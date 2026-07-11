
<?php
class Variant_Order_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->created_by      = $this->session->userdata('user_id');
       
    }
    
    public function get_product_variants($product_id){
        $this->db->select('pv.*, u.name AS unit');
        $this->db->from('product_variants pv');
        $this->db->join('sma_products p', 'p.id = pv.product_id', 'left');
        $this->db->join('sma_units u', 'u.id = p.unit', 'left');
        $this->db->where('pv.group_id', '1');
        $this->db->where('pv.product_id', $product_id);
        
        $query = $this->db->get();
        return $query->result();
    }
    public function getRawMaterials($product_id, $location_id, $job_work_id = null){
        if ($this->Owner || $this->Admin) {
            $location_id = $this->Settings->default_warehouse;
        }

        // Base SQL shared for both cases
        $baseSql = "
            SELECT DISTINCT
                bom.version_no,
                bom.notes,
                bom_items.bom_id,
                bom_items.material_id,
                bom_items.bom_ref,
                bom_items.quantity_required,
                bom_items.option_id,
                p.name AS raw_material,
                p.id AS raw_material_id,
                bom.product_id AS product_id,
                u.name AS unit_name,
                pb.is_batch_only,
                pb.min_batch_qty,
                bom_items.is_alternative,
                pb.sales_units,
                pi.expiry AS expiry_date,
                pi.batch_number AS batch_no,
                IFNULL(pi.batch_available_qty, 0) AS available_qty,
                w_all.name AS warehouse_name,              -- ✅ from all warehouses
                w_all.id AS warehouse_id,                  -- ✅ include warehouse ID
                IFNULL(wp_all.quantity, 0) AS warehouse_qty -- ✅ stock for each warehouse
            FROM sma_produnit_bill_of_material_items AS bom_items
            JOIN sma_produnit_bill_of_materials AS bom 
                ON bom_items.bom_id = bom.id
            LEFT JOIN sma_products p 
                ON bom_items.material_id = p.id
            LEFT JOIN sma_units u 
                ON bom_items.uom = u.id
            LEFT JOIN (
                SELECT pb1.*
                FROM sma_produnit_products_in_batches pb1
                INNER JOIN (
                    SELECT product_id, MAX(id) AS max_id
                    FROM sma_produnit_products_in_batches
                    GROUP BY product_id
                ) pb2 
                    ON pb1.product_id = pb2.product_id 
                    AND pb1.id = pb2.max_id
            ) AS pb 
                ON pb.product_id = bom.product_id
            LEFT JOIN (
                SELECT 
                    pi.product_id,
                    pi.warehouse_id,
                    pi.batch_number,
                    pi.expiry,
                    SUM(pi.quantity_balance) AS batch_available_qty
                FROM sma_purchase_items pi
                WHERE pi.warehouse_id = ?                 -- ✅ current location filter
                GROUP BY 
                    pi.product_id,
                    pi.warehouse_id,
                    pi.batch_number,
                    pi.expiry
            ) AS pi 
                ON pi.product_id = bom_items.material_id
            /* ✅ Join all warehouses (no filter) to show name and qty for all */
            LEFT JOIN sma_warehouses_products wp_all 
                ON wp_all.product_id = p.id
            LEFT JOIN sma_warehouses w_all 
                ON w_all.id = wp_all.warehouse_id
            LEFT JOIN sma_location_type lt 
                ON lt.id = w_all.location_type
            WHERE bom.product_id = ?
        ";

        $params = [
            (int)$location_id,
            (int)$product_id,
        ];

        if ($job_work_id) {
            // Restrict by job_works when provided
            $baseSql .= " AND bom.job_works = ? ";
            $params[] = (int)$job_work_id;

            $baseSql .= " AND (lt.type IS NULL OR lt.type != 'vendor')
            AND bom.version_no = (
                SELECT MAX(CAST(version_no AS UNSIGNED))
                FROM sma_produnit_bill_of_materials
                WHERE product_id = ? AND job_works = ? AND is_active = 1
            )
            AND bom.is_active = 1
            ORDER BY w_all.name, bom_items.material_id, pi.batch_number, pi.expiry;";

            $params[] = (int)$product_id;
            $params[] = (int)$job_work_id;
        } else {
            // Original logic: latest active BOM by product only
            $baseSql .= " AND (lt.type IS NULL OR lt.type != 'vendor')
            AND bom.version_no = (
                SELECT MAX(CAST(version_no AS UNSIGNED))
                FROM sma_produnit_bill_of_materials
                WHERE product_id = ? AND is_active = 1
            )
            AND bom.is_active = 1
            ORDER BY w_all.name, bom_items.material_id, pi.batch_number, pi.expiry;";

            $params[] = (int)$product_id;
        }

        $query = $this->db->query($baseSql, $params);

        return $query->num_rows() > 0 ? $query->result() : false;
    }
    public function getAllCategories() {
        $this->db->where('flag_visible', 1)->group_start()->where('parent_id', NULL)->or_where('parent_id', 0)->group_end()->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_other_category($product_id)
{
    return $this->db->select('p.other_categories, c.name as primary_category')
        ->from('products p')
        ->join('categories c', 'c.id = p.category_id', 'left')
        ->where('p.id', $product_id)
        ->get()
        ->row();
}

    public function get_category_names_by_ids($ids) {
        if (empty($ids)) {
            return [];
        }
        // Make sure all values are integers
        $ids = array_map('intval', $ids);

        // Create placeholders: ?, ?, ?, ...
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Build the query
        $q = "SELECT name FROM sma_categories WHERE id IN ($placeholders)";
        $query = $this->db->query($q, $ids);
        return $query->result_array();
    }
    public function getAllBrands() {
        $q = $this->db->get("brands");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    /**
     * Get Job Work processes linked to a product via sma_product_dcc_stages.
     * Mirrors Products_model::getDccStagesByMainProductId but returns a simple
     * list of {id, name} for use in dropdowns.
     */
    public function get_job_works_by_product($product_id)
    {
        $product_id = (int) $product_id;
        if ($product_id <= 0) {
            return [];
        }

        // First try to find stages directly on this product_id
        $this->db
            ->select('d.job_work AS id, jw.items AS name')
            ->from('sma_product_dcc_stages d')
            ->join('products p', 'p.id = d.product_id', 'inner')
            ->join('sma_standard_job_works jw', 'jw.id = d.job_work', 'left')
            ->where('d.product_id', $product_id)
            ->order_by('d.id', 'asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }

        // Fallback: if this product has a mainproduct_id, use that
        $row = $this->db->select('mainproduct_id')
                        ->from('sma_products')
                        ->where('id', $product_id)
                        ->limit(1)
                        ->get()
                        ->row();
        if ($row && (int)$row->mainproduct_id > 0) {
            $main_id = (int)$row->mainproduct_id;

            $this->db
                ->select('d.job_work AS id, jw.items AS name')
                ->from('sma_product_dcc_stages d')
                ->join('products p', 'p.id = d.product_id', 'inner')
                ->join('sma_standard_job_works jw', 'jw.id = d.job_work', 'left')
                ->where('d.product_id', $main_id)
                ->order_by('d.id', 'asc');

            $q2 = $this->db->get();
            return $q2->num_rows() > 0 ? $q2->result() : [];
        }

        return [];
    }

    /**
     * check_recipe_variants
     * Check if recipe has variants and return appropriate variant data
     */
    public function check_recipe_variants($product_id) {
        // Get BOM items to check if recipe has variants
        $this->db->select('bom_items.option_id, p.mainproduct_id');
        $this->db->from('sma_produnit_bill_of_material_items bom_items');
        $this->db->join('sma_produnit_bill_of_materials bom', 'bom_items.bom_id = bom.id');
        $this->db->join('sma_products p', 'p.id = bom.product_id', 'left');
        $this->db->where('bom.product_id', $product_id);
        $this->db->where('bom.is_active', 1);
        $this->db->where('bom.version_no = (
            SELECT MAX(CAST(version_no AS UNSIGNED))
            FROM sma_produnit_bill_of_materials
            WHERE product_id = ' . (int)$product_id . ' AND is_active = 1
        )');
        $query = $this->db->get();
        
        $result = [
            'has_variants' => false,
            'variants' => [],
            'mainproduct_id' => null
        ];
        
        if ($query->num_rows() > 0) {
            $bom_items = $query->result();
            
            // Check if any BOM item has option_id (recipe variants)
            $has_recipe_variants = false;
            foreach ($bom_items as $item) {
                if (!empty($item->option_id)) {
                    $has_recipe_variants = true;
                    break;
                }
            }
            
            $result['has_variants'] = $has_recipe_variants;
            
            if (!empty($bom_items[0]->mainproduct_id)) {
                $result['mainproduct_id'] = $bom_items[0]->mainproduct_id;
            }
            
            // Get product variants
            $variants = $this->get_product_variants($product_id);
            
            // If recipe has variants but product has no variants, get variants from mainproduct_id
            if ($has_recipe_variants && empty($variants) && !empty($result['mainproduct_id'])) {
                $variants = $this->get_product_variants($result['mainproduct_id']);
                
                // For Job Works products, ensure variants maintain original product_id
                // The original product_id is the one passed to check_recipe_variants function
                foreach ($variants as &$variant) {
                    if ($result['mainproduct_id'] && $variant->product_id == $result['mainproduct_id']) {
                        $variant->product_id = $product_id; // Use the original Job Works product ID
                    }
                }
            }
            
            $result['variants'] = $variants;
        }
        
        return $result;
    }

    /**
     * Build dummy line items for Generate Variant PO when a Job Work (process) is selected on RM Calculator.
     * Child rows in products use: main_name + '_' + job_work_suffix + '_' + variant_option (e.g. Pink Shalu_Ironed_L)
     * with mainproduct_id pointing at the main sellable product.
     *
     * @param int   $job_work_id
     * @param array $variants_payload Decoded variants from payload (id, name, product_id, required_quantity)
     * @return array|null List of [ 'product_id' => int, 'variant_id' => int, 'qty' => float ], or null to fall back to legacy (main product + product_variants id)
     * When job work has no suffix (empty in DB), returns null so Order items use the selected main product + variants, not child/raw SKUs.
     */
    public function resolve_jobwork_variant_po_dummy_items($job_work_id, $variants_payload)
    {
        $job_work_id = (int) $job_work_id;
        if (!is_array($variants_payload)) {
            return null;
        }
        if ($job_work_id <= 0 || empty($variants_payload)) {
            return null;
        }

        $jw = $this->db->where('id', $job_work_id)->get('standard_job_works')->row();
        if (!$jw) {
            return null;
        }

        $suffix = isset($jw->suffix) ? trim((string) $jw->suffix) : '';
        if ($suffix === '') {
            return null;
        }

        $ctx_id = 0;
        foreach ($variants_payload as $v) {
            if (!empty($v['product_id']) && (int) $v['product_id'] > 0) {
                $ctx_id = (int) $v['product_id'];
                break;
            }
        }
        if ($ctx_id <= 0) {
            return null;
        }

        $ctx = $this->db->select('id, mainproduct_id, name')
            ->from('products')
            ->where('id', $ctx_id)
            ->limit(1)
            ->get()
            ->row();

        if (!$ctx) {
            return null;
        }

        if (!empty($ctx->mainproduct_id) && (int) $ctx->mainproduct_id > 0) {
            $main_id = (int) $ctx->mainproduct_id;
            $main_row = $this->db->select('name')
                ->from('products')
                ->where('id', $main_id)
                ->limit(1)
                ->get()
                ->row();
            $main_name = $main_row && !empty($main_row->name) ? trim($main_row->name) : '';
        } else {
            $main_id = $ctx_id;
            $main_name = trim((string) $ctx->name);
        }

        if ($main_id <= 0 || $main_name === '') {
            return null;
        }

        $tbl = $this->db->dbprefix('products');
        $items = array();

        foreach ($variants_payload as $v) {
            $qty = isset($v['required_quantity']) ? (float) $v['required_quantity'] : 0;
            if ($qty <= 0) {
                continue;
            }

            $vid = isset($v['id']) ? $v['id'] : null;
            $vname = isset($v['name']) ? trim((string) $v['name']) : '';

            $is_simple = ($vid === 'simple_variant' || $vname === '' || strcasecmp($vname, 'Default Variant') === 0);

            if ($is_simple) {
                $expected = $main_name . '_' . $suffix;
            } else {
                $expected = $main_name . '_' . $suffix . '_' . $vname;
            }

            $child_id = $this->_find_jobwork_child_product_id($tbl, $main_id, $expected);
            if ($child_id > 0) {
                $items[] = array(
                    'product_id' => $child_id,
                    'variant_id' => 0,
                    'qty'        => $qty,
                );
                continue;
            }

            $pid = isset($v['product_id']) ? (int) $v['product_id'] : 0;
            $opt_id = (is_numeric($vid)) ? (int) $vid : 0;
            if ($pid > 0) {
                $items[] = array(
                    'product_id' => $pid,
                    'variant_id' => $opt_id,
                    'qty'        => $qty,
                );
            }
        }

        return !empty($items) ? $items : null;
    }

    /**
     * When the selected job work has no suffix in standard_job_works, RM Calculator "Order items" must use
     * the main sellable product + product_variants option rows — not semi-finished child SKUs sent from
     * the simple-quantity view (raw material product ids).
     *
     * Also remaps ingredient quantities_by_variant keys (e.g. simple_0 → real option id) for Generate Variant PO.
     *
     * @param int         $job_work_id
     * @param array       $variants_payload  Decoded variants JSON from RM Calculator
     * @param array|null  $ingredients_payload Decoded ingredients JSON or null
     * @return array      keys: changed (bool), variants (array), ingredients (array|null)
     */
    public function normalize_rm_variant_po_for_empty_jobwork_suffix($job_work_id, $variants_payload, $ingredients_payload = null)
    {
        $job_work_id = (int) $job_work_id;
        $result = array(
            'changed'     => false,
            'variants'    => $variants_payload,
            'ingredients' => $ingredients_payload,
        );

        if (!is_array($variants_payload)) {
            return $result;
        }

        if ($job_work_id <= 0 || empty($variants_payload)) {
            return $result;
        }

        $jw = $this->db->where('id', $job_work_id)->get('standard_job_works')->row();
        if (!$jw) {
            return $result;
        }

        $suffix = isset($jw->suffix) ? trim((string) $jw->suffix) : '';
        if ($suffix !== '') {
            return $result;
        }

        $id_map = array();
        $out_variants = array();
        $any_normalized = false;

        foreach ($variants_payload as $v) {
            if (!is_array($v)) {
                $out_variants[] = $v;
                continue;
            }
            $pid = isset($v['product_id']) ? (int) $v['product_id'] : 0;
            if ($pid <= 0) {
                $out_variants[] = $v;
                continue;
            }
            $ctx = $this->db->select('id, mainproduct_id, name')
                ->from('products')
                ->where('id', $pid)
                ->limit(1)
                ->get()
                ->row();

            if (!$ctx) {
                $out_variants[] = $v;
                continue;
            }
            $main_id = (!empty($ctx->mainproduct_id) && (int) $ctx->mainproduct_id > 0) ? (int) $ctx->mainproduct_id : $pid;
            $old_id_key = isset($v['id']) ? (string) $v['id'] : '';
            $vname = isset($v['name']) ? trim((string) $v['name']) : '';
            $vid = isset($v['id']) ? $v['id'] : null;
            $option_id = 0;
            $option_name = $vname;

            if (is_numeric($vid) && (int) $vid > 0) {
                $opt_id = (int) $vid;
                $pv = $this->db->where('id', $opt_id)->limit(1)->get('product_variants')->row();
                if ($pv) {
                    $pvpid = (int) $pv->product_id;
                    if ($pvpid === $main_id) {
                        $option_id = $opt_id;
                        $option_name = trim((string) $pv->name);
                    } else {
                        $vn = trim((string) $pv->name);
                        $pv_main = $this->db->where('product_id', $main_id)->where('name', $vn)->limit(1)->get('product_variants')->row();
                        if (!$pv_main && $vn !== '') {
                            $pv_main = $this->db->where('product_id', $main_id)
                                ->where('LOWER(name)', strtolower($vn), false)
                                ->limit(1)
                                ->get('product_variants')
                                ->row();
                        }
                        if ($pv_main) {
                            $option_id = (int) $pv_main->id;
                            $option_name = trim((string) $pv_main->name);
                        }
                    }
                }
            }

            if ($option_id <= 0 && $vname !== '') {
                $pv_main = $this->db->where('product_id', $main_id)->where('name', $vname)->limit(1)->get('product_variants')->row();
                if (!$pv_main) {
                    $pv_main = $this->db->where('product_id', $main_id)
                        ->where('LOWER(name)', strtolower($vname), false)
                        ->limit(1)
                        ->get('product_variants')
                        ->row();
                }
                if ($pv_main) {
                    $option_id = (int) $pv_main->id;
                    $option_name = trim((string) $pv_main->name);
                }
            }

            if ($option_id <= 0 && $main_id !== $pid) {
                $main_row = $this->db->select('name')->from('products')->where('id', $main_id)->limit(1)->get()->row();
                $main_name = $main_row && !empty($main_row->name) ? trim((string) $main_row->name) : '';
                $child_name = trim((string) $ctx->name);
                if ($main_name !== '' && $child_name !== '' && strpos($child_name, $main_name) === 0) {
                    $rest = substr($child_name, strlen($main_name));
                    $rest = ltrim($rest, '_');
                    if ($rest !== '') {
                        $segments = explode('_', $rest);
                        $tail = trim(end($segments));
                        if ($tail !== '') {
                            $pv_main = $this->db->where('product_id', $main_id)->where('name', $tail)->limit(1)->get('product_variants')->row();
                            if (!$pv_main) {
                                $pv_main = $this->db->where('product_id', $main_id)
                                    ->where('LOWER(name)', strtolower($tail), false)
                                    ->limit(1)
                                    ->get('product_variants')
                                    ->row();
                            }
                            if ($pv_main) {
                                $option_id = (int) $pv_main->id;
                                $option_name = trim((string) $pv_main->name);
                            }
                        }
                    }
                }
            }

            $qty = isset($v['required_quantity']) ? (float) $v['required_quantity'] : 0;

            if ($option_id <= 0) {
                $out_variants[] = $v;
                continue;
            }

            $new_line = array(
                'id'                 => $option_id,
                'name'               => $option_name !== '' ? $option_name : $vname,
                'product_id'         => $main_id,
                'required_quantity'  => $qty,
            );
            $out_variants[] = $new_line;
            $any_normalized = true;

            if ($old_id_key !== '' && $old_id_key !== (string) $option_id) {
                $id_map[$old_id_key] = $option_id;
            }
        }

        if (!$any_normalized) {
            return $result;
        }

        $result['changed'] = true;
        $result['variants'] = $out_variants;

        if (is_array($ingredients_payload)) {
            foreach ($ingredients_payload as $key => $ing) {
                if (!is_array($ing)) {
                    continue;
                }
                if (empty($ing['quantities_by_variant']) || !is_array($ing['quantities_by_variant'])) {
                    continue;
                }
                $new_qbv = array();
                foreach ($ing['quantities_by_variant'] as $qk => $qty) {
                    $qks = (string) $qk;
                    if (isset($id_map[$qks])) {
                        $nk = (string) $id_map[$qks];
                        if (!isset($new_qbv[$nk])) {
                            $new_qbv[$nk] = 0;
                        }
                        $new_qbv[$nk] += (float) $qty;
                    } else {
                        if (!isset($new_qbv[$qks])) {
                            $new_qbv[$qks] = 0;
                        }
                        $new_qbv[$qks] += (float) $qty;
                    }
                }
                $ingredients_payload[$key]['quantities_by_variant'] = $new_qbv;
            }
            $result['ingredients'] = $ingredients_payload;
        }

        return $result;
    }

    /**
     * @param string $products_table Prefixed table name e.g. sma_products
     */
    private function _find_jobwork_child_product_id($products_table, $main_id, $expected_name)
    {
        $main_id = (int) $main_id;
        if ($main_id <= 0 || $expected_name === '') {
            return 0;
        }

        $q = $this->db->query(
            'SELECT id FROM ' . $products_table . ' WHERE mainproduct_id = ? AND LOWER(TRIM(name)) = LOWER(?) LIMIT 1',
            array($main_id, $expected_name)
        );
        if ($q && $q->num_rows() > 0) {
            return (int) $q->row()->id;
        }

        $q2 = $this->db->query(
            'SELECT id FROM ' . $products_table . ' WHERE mainproduct_id = ? AND name = ? LIMIT 1',
            array($main_id, $expected_name)
        );
        if ($q2 && $q2->num_rows() > 0) {
            return (int) $q2->row()->id;
        }

        // Data often uses inconsistent spacing vs code-built "Main_Suffix_Variant"
        // (e.g. DB "Sleeves full _Stiched_L" vs expected "Sleeves full_Stiched_L", or hyphen in DCC vs underscore in products).
        $normExpected = $this->_normalize_jobwork_product_name_for_match($expected_name);
        if ($normExpected !== '') {
            $q3 = $this->db->select('id, name')
                ->from('products')
                ->where('mainproduct_id', $main_id)
                ->get();
            if ($q3 && $q3->num_rows() > 0) {
                foreach ($q3->result() as $row) {
                    if ($this->_normalize_jobwork_product_name_for_match($row->name) === $normExpected) {
                        return (int) $row->id;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Compare child SKU names ignoring space/hyphen/underscore run differences
     * (mainproduct_id children only — safe for disambiguation).
     */
    private function _normalize_jobwork_product_name_for_match($name)
    {
        $s = strtolower(trim((string) $name));
        $s = str_replace('-', '_', $s);
        $s = preg_replace('/\s+/', '_', $s);
        $s = preg_replace('/_+/', '_', $s);
        return $s;
    }
}
?>