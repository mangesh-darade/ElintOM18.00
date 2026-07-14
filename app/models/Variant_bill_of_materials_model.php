<?php
class Variant_bill_of_materials_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->created_by = $this->session->userdata('user_id');
    }
    public function getProductCategoriesList()
    {

        $query = $this->db->query("SELECT parent.id AS parent_id, parent.name AS parent_name, sub.id AS subcategory_id, sub.name AS subcategory_name FROM sma_categories AS parent LEFT JOIN sma_categories AS sub ON parent.id = sub.parent_id AND sub.flag_visible = 1 WHERE parent.flag_visible = 1");

        if ($query->num_rows() > 0) {
            $categories = array();
            foreach ($query->result_array() as $row) {
                $parent_id = $row['parent_id'];
                $subcategory_id = $row['subcategory_id'];

                if (!isset($categories[$parent_id])) {
                    $categories[$parent_id] = array(
                        'category_id' => $parent_id,
                        'category_name' => $row['parent_name'],
                        'subcategories' => array()
                    );
                }
                if ($subcategory_id !== null) {
                    $categories[$parent_id]['subcategories'][] = array(
                        'subcategory_id' => $subcategory_id,
                        'subcategory_name' => $row['subcategory_name']
                    );
                }
            }

            // Sort categories and subcategories alphabetically
            foreach ($categories as &$category) {
                usort($category['subcategories'], function ($a, $b) {
                    return strcmp($a['subcategory_name'], $b['subcategory_name']);
                });
            }
            unset($category); // unset reference

            // Sort categories by category_name
            usort($categories, function ($a, $b) {
                return strcmp($a['category_name'], $b['category_name']);
            });

            return $categories;
        } else {
            return FALSE;
        }

        // echo json_encode($categories);


    }
    ////////////////////////////////   getProductByCategories //////////////////////////////////////////
    // public function getProductByCategories($categoryID = null, $subcategory_id = null)
    // {

    //     // Initialize data array
    //     $data = array();

    //     $query = "
    //         SELECT 
    //             products.*, 
    //             categories.name AS categoryName,  
    //             units.name AS unitName, 
    //             tax_rates.name AS tax_rate, 
    //             COALESCE(pp_sub.price, products.price) AS c_price
    //         FROM 
    //             sma_products AS products
    //         JOIN 
    //             sma_tax_rates AS tax_rates ON tax_rates.id = products.tax_rate
    //         JOIN 
    //             sma_categories AS categories ON categories.id = products.category_id
    //         LEFT JOIN (
    //             SELECT * FROM sma_productionunit_products
    //         ) AS pup_sub ON pup_sub.product_id = products.id
    //         LEFT JOIN 
    //             sma_warehouses AS w ON w.id = pup_sub.location_id
    //         LEFT JOIN (
    //             SELECT 
    //                 product_id, price_group_id, price
    //             FROM 
    //                 sma_product_prices
    //         ) AS pp_sub 
    //             ON pp_sub.product_id = products.id 
    //             AND (
    //             pp_sub.price_group_id = w.price_group_id 
    //             OR w.price_group_id IS NULL
    //         )
    //         JOIN 
    //             sma_units AS units ON units.id = products.unit
    //         WHERE 
    //             products.flag_visible = 1
    //             AND products.type NOT IN ('raw')
    //     ";

    //     // Optional filters
    //     if (!empty($categoryID)) {
    //         $query .= " AND products.category_id = " . (int) $categoryID;
    //     }
    //     if (!empty($subcategory_id)) {
    //         $query .= " AND products.subcategory_id = " . (int) $subcategory_id;
    //     }

    //     // Final order clause
    //     $query .= " ORDER BY products.name ASC";

    //     $q = $this->db->query($query);
    //     // return $result->result();

    //     if ($q->num_rows() > 0) {
    //         foreach ($q->result() as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }

    //     return FALSE;
    // }
    public function getProductByCategories($categoryID = null, $subcategory_id = null)
    {
        $data = array();

        $query = "
            SELECT 
                p.*, 
                c.name AS categoryName,  
                u.name AS unitName, 
                t.name AS tax_rate, 
                COALESCE(pp.price, p.price) AS c_price
            FROM sma_products AS p
            LEFT JOIN sma_tax_rates AS t ON t.id = p.tax_rate
            LEFT JOIN sma_categories AS c ON c.id = p.category_id
            LEFT JOIN sma_units AS u ON u.id = p.unit
            LEFT JOIN sma_warehouses_products AS pup ON pup.product_id = p.id
            LEFT JOIN sma_warehouses AS w ON w.id = pup.warehouse_id
            LEFT JOIN sma_product_prices AS pp 
                ON pp.product_id = p.id 
                AND (pp.price_group_id = w.price_group_id OR w.price_group_id IS NULL)
            WHERE 
                p.type IN ('standard', 'Intermediate')
        ";

        if (!empty($categoryID)) {
            // Check if category matches main category_id OR exists in other_categories field
            $query .= " AND (p.category_id = " . (int)$categoryID . " OR FIND_IN_SET(" . (int)$categoryID . ", p.other_categories) > 0)";
        }
        if (!empty($subcategory_id)) {
            // Check if subcategory matches subcategory_id OR exists in other_categories field
            $query .= " AND (p.subcategory_id = " . (int)$subcategory_id . " OR FIND_IN_SET(" . (int)$subcategory_id . ", p.other_categories) > 0)";
        }

        $query .= " GROUP BY p.id ORDER BY p.name ASC";

        $q = $this->db->query($query);

        if ($q->num_rows() > 0) {
            return $q->result();
        }

        return false;
    }
    public function getWarehouseByID($id)
    {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
        return FALSE;
    }
    public function getUnitById($id)
    {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    // fetch raw materials for ingrediants functionality
    public function getRawMaterials($product_id, $location_id)
    {

        $this->db->select('bom.version_no, bom.notes,bom_items.bom_id,bom_items.material_id,bom_items.bom_ref,bom_items.quantity_required, sma_products.name as raw_material, bom.product_id as product_id, IFNULL(wp.quantity, 0) as available_qty, sma_units.name as unit_name, pb.is_batch_only, pb.min_batch_qty');
        $this->db->from('sma_produnit_bill_of_material_items AS bom_items');
        $this->db->join('(SELECT * FROM sma_produnit_bill_of_materials WHERE product_id = ' . (int) $product_id . ' ORDER BY version_no DESC LIMIT 1) AS bom', 'bom_items.bom_id = bom.id', 'left');
        // $this->db->join('sma_produnit_bill_of_materials AS bom', 'bom_items.bom_id = bom.id', 'left');
        $this->db->join('sma_products', 'bom_items.material_id = sma_products.id', 'left');
        $this->db->join('sma_units', 'sma_products.unit = sma_units.id', 'left');
        $this->db->join('sma_warehouses_products AS wp', 'wp.product_id = sma_products.id AND wp.warehouse_id = ' . (int) $location_id, 'left');
        $this->db->join('sma_produnit_products_in_batches AS pb', 'pb.product_id = bom.product_id', 'left');
        $this->db->where('bom.product_id', $product_id);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {

                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    //////////////////////////////////////////////// Raw Material Functionality  ////////////////////////////////////////////////
    public function getRawProducts()
    {
        $this->db->where_in('type', array('raw', 'Intermediate'));
        $this->db->where('flag_visible', 1);
        $q = $this->db->get('sma_products');

        if ($q->num_rows() > 0) {
            $data = array();
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return false;
    }
    public function getBomDetailsByProductId($product_id, $job_work_id = null)
    {
        if ($product_id) {
            // Step 1: Get all BOMs for the product (optionally filtered by process / job_works)
            $this->db->select('b.*, p.name as name, p.code as code, pb.is_batch_only, pb.min_batch_qty, pb.batch_uom, pb.sales_units');
            $this->db->from('sma_produnit_bill_of_materials b');
            $this->db->join('sma_products p', 'p.id = b.product_id', 'left');
            $this->db->join('sma_produnit_products_in_batches pb', 'pb.product_id = p.id', 'left');
            $this->db->where('b.product_id', $product_id);
            if ($job_work_id !== null && $job_work_id !== '' && (int) $job_work_id > 0) {
                $this->db->where('b.job_works', (int) $job_work_id);
            }
            $this->db->order_by('CAST(version_no AS UNSIGNED)', 'DESC'); // Optional: keep BOMs ordered by version
            $this->db->group_by('b.id');

            $q = $this->db->get();

            if ($q && $q->num_rows() > 0) {
                $boms = $q->result();
                foreach ($boms as &$bom) {
                    // Step 2: For each BOM, get its items
                    $this->db->select('i.*, m.name as name, m.code as code');
                    $this->db->from('produnit_bill_of_material_items i');
                    $this->db->join('sma_products m', 'm.id = i.material_id', 'left');
                    $this->db->where('i.bom_id', $bom->id);
                    $items_q = $this->db->get();

                    if ($items_q && $items_q->num_rows() > 0) {
                        $items = $items_q->result();
                        $unique_items = array();

                        // Deduplicate by material_id
                        foreach ($items as $item) {
                            if (!isset($unique_items[$item->material_id])) {
                                $unique_items[$item->material_id] = $item;
                            } else {
                                // Optional: If you want to sum quantities or do something else with duplicates,
                                // you can add that logic here.
                                // Example:
                                // $unique_items[$item->material_id]->quantity += $item->quantity;
                            }
                        }

                        $bom->items = array_values($unique_items);
                    } else {
                        $bom->items = array();
                    }
                }

                return $boms; // Return array of BOMs with unique items
            }

            // When filtering by process, an empty result means no BOMs for that process (do not return dummy header).
            if ($job_work_id !== null && $job_work_id !== '' && (int) $job_work_id > 0) {
                return array();
            }

            // Step 3: No BOMs found — return product info only
            $this->db->select('id as product_id, name as name');
            $this->db->from('sma_products');
            $this->db->where('id', $product_id);
            $p = $this->db->get();

            if ($p && $p->num_rows() > 0) {
                $product = $p->row();

                // Build dummy BOM-like object with no BOM data
                $emptyBom = new stdClass();
                $emptyBom->id = null;
                $emptyBom->product_id = $product->product_id;
                $emptyBom->name = $product->name;
                $emptyBom->version_no = null;
                $emptyBom->notes = null;
                $emptyBom->is_active = null;
                $emptyBom->created_at = null;
                $emptyBom->items = array();

                return [$emptyBom]; // Still return as an array
            }
        }

        return false;
}


    public function addProddUnitBom($data)
    {
        if ($this->db->insert('sma_produnit_bill_of_materials', $data)) {
            return $this->db->insert_id(); // return bom_id
        }
        return false;
    }
    public function addProddUnitBomItems($data)
    {
        return $this->db->insert('sma_produnit_bill_of_material_items', $data);
    }
    public function getProcurmentOrders($status, $order_dispatch)
    {


        // // $this->db->select('po.procurement_order_ref_no as orderNo, po.id as orderId, po.status as orderStatus, po.created_by as placedBy, po.order_creation_date as placedOn, po.actual_delivery_date as receivedOn,poi.product_name as product_Name, COUNT(poi.id) as itemCount');
        // $this->db->select('po.procurement_order_ref_no as orderNo, po.id as orderId, po.status as orderStatus, po.created_by as placedBy, po.order_creation_date as placedOn, po.actual_delivery_date as receivedOn,createdByUsers.first_name as placedBy,  COUNT(DISTINCT poi.id) as itemCount');
        // $this->db->from('procurement_orders po');
        // $this->db->join('procurement_order_items poi', 'po.id = poi.procurement_orders_id', 'left');
        // $this->db->join('units', 'units.id = poi.product_unit_id');
        // $this->db->join('products', 'products.id = poi.product_id');
        // $this->db->join('categories cat', 'cat.id = products.category_id');
        // // $this->db->join('users', 'users.warehouse_id = po.location_id');
        // $this->db->join('users createdByUsers', 'createdByUsers.id = po.created_by');
        // $this->db->join('users', 'users.warehouse_id = po.location_id', 'left');
        $this->db->select(
            '
            po.procurement_order_ref_no as orderNo, 
            po.id as orderId, 
            po.status as orderStatus, 
            po.created_by as createdById, 
            od.courier as courier, 
            od.tracking_number as tracking_number, 
            od.attachment as attachment,  
            po.order_creation_date as placedOn, 
            po.actual_delivery_date as receivedOn, 
            createdByUsers.first_name as createdByFirstName, 
            COUNT(DISTINCT poi.id) as itemCount'
        );
        $this->db->from('procurement_orders po');
        $this->db->join('procurement_order_items poi', 'po.id = poi.procurement_orders_id', 'left');
        $this->db->join('units', 'units.id = poi.product_unit_id', 'left');
        $this->db->join('products', 'products.id = poi.product_id', 'left');
        $this->db->join('categories cat', 'cat.id = products.category_id', 'left');
        $this->db->join('users createdByUsers', 'createdByUsers.id = po.created_by', 'left');
        $this->db->join('users warehouseUsers', 'warehouseUsers.warehouse_id = po.location_id', 'left');
        $this->db->join('orderdispatchdetails od', 'od.procurement_order_ref_no = po.procurement_order_ref_no', 'left');
        $this->db->group_by('po.id');
        // $this->db->group_by('poi.id');

        if ($status == 'previous_order') {
            $status_conditions = array('Locked', 'Completed', 'Received', 'Rejected', 'partially_completed', 'Dispatched');
            $this->db->where_in('po.status', $status_conditions);
        }
        if ($status == 'partially') {
            $Item_status = array('pending', 'partially_completed');
            $this->db->where_in('poi.item_status', $Item_status);
        }
        if ($status == 'Open') {
            $this->db->where('po.status', 'Open');
        }
        if ($status == 'Received') {
            if ($order_dispatch == '1') {
                // $Item_status = array('Completed','Dispatched','Received','partially_completed');
                $Item_status = array('Dispatched', 'partially_completed');
            } else {
                $Item_status = array('Completed', 'partially_completed');
            }
            // $Item_status = array('partially_completed','Completed');
            $this->db->where_in('po.status', $Item_status);
            $this->db->where_in('poi.item_status', $Item_status);
            // $this->db->where('po.actual_delivery_date', '0000-00-00 00:00:00');
        }
        if (!$this->Owner || !$this->Admin) {
            if ($this->created_by) {
                $this->db->where('po.created_by', $this->created_by);
            }
        }
        $this->db->order_by("po.id", "DESC");

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_bom_version_no($product_id)
    {
        $this->db->select('MAX(CAST(version_no AS UNSIGNED)) AS max_version');
        $this->db->from('sma_produnit_bill_of_materials');
        $this->db->where('product_id', $product_id);
        // $this->db->order_by('version_no', 'DESC');
        // $this->db->limit(1);

        $query = $this->db->get();

        // if ($query->num_rows() > 0) {
        // return $query->row()->version_no;
        // }
        // return 0; // Default version number if no BOM exists
        $row = $query->row();
        return $row && $row->max_version ? (int)$row->max_version : 0;
    }
    public function addproducts_in_batches($data)
    {
        if ($this->db->insert('sma_produnit_products_in_batches', $data)) {
            return $this->db->insert_id(); // return bom_id
        }
        return false;
    }
    public function getBomMaterialDetails($product_id)
    {
        $this->db->select('b.*, p.name as name, p.code as product_code');
        $this->db->from('sma_produnit_bill_of_materials b');
        $this->db->join('sma_products p', 'p.id = b.product_id', 'left');
        $this->db->where('b.product_id', $product_id);
        $this->db->order_by('b.version_no', 'ASC'); // Get the latest version
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; // No BOM found for this product
    }
    public function getBomMaterialItems($bom_id, $location_id = null)
    {
        $this->db->select('
        bom_items.bom_id,
        bom_items.material_id,
        bom_items.bom_ref,
        bom_items.quantity_required,
        bom_items.uom AS uom_id,
        bom_items.option_id AS variant_option_id,
        sma_product_variants.name AS variant_name,
        sma_products.name AS raw_material,
        bom_items.wastage_percent AS wastage_percent,
        bom_items.is_alternative AS is_alternative,
        bom_uom.name AS unit_name,
        sma_products.code AS product_code,
        sma_products.id AS product_id,
        pb.is_batch_only AS is_batch_only,
        pb.min_batch_qty AS min_batch_qty,
        pb.batch_uom AS batch_uom,
        pb.sales_units AS sales_units,
        sma_produnit_bill_of_materials.notes AS notes,
        sma_produnit_bill_of_materials.is_active AS is_active,
        sma_produnit_bill_of_materials.created_at AS created_at,
        sma_produnit_bill_of_materials.version_no AS version_no,
        jw.items AS process_name
    ');
        $this->db->from('sma_produnit_bill_of_material_items AS bom_items');
        $this->db->join('sma_products', 'bom_items.material_id = sma_products.id', 'left');
        $this->db->join('sma_produnit_bill_of_materials', 'sma_produnit_bill_of_materials.id = bom_items.bom_id', 'left');
        $this->db->join('sma_standard_job_works jw', 'jw.id = sma_produnit_bill_of_materials.job_works', 'left');
        $this->db->join('sma_units AS bom_uom', 'bom_items.uom = bom_uom.id', 'left');
        $this->db->join('sma_product_variants', 'sma_product_variants.id = bom_items.option_id', 'left');
        // Optional: join batch info if applicable - use DISTINCT to avoid duplicates
        $this->db->join('(SELECT DISTINCT bom_id, is_batch_only, min_batch_qty, batch_uom, sales_units FROM sma_produnit_products_in_batches) AS pb', 'pb.bom_id = bom_items.bom_id', 'left');

        $this->db->where('bom_items.bom_id', (int) $bom_id);
        $this->db->group_by('bom_items.id'); // Group by BOM item ID to prevent duplicates

        $query = $this->db->get();
        if (!$query) {
            return array();
        }
        return $query->result();
    }

    public function getRawMaterialVariants($raw_material_id)
    {
        // First check if this raw material has a mainproduct_id
        $this->db->select('mainproduct_id');
        $this->db->from('sma_products');
        $this->db->where('id', $raw_material_id);
        $this->db->limit(1);
        $product_query = $this->db->get();
        
        $product_id_to_use = $raw_material_id; // Default to the raw material itself
        
        if ($product_query->num_rows() > 0) {
            $product_row = $product_query->row();
            // If mainproduct_id exists and is not null or 0, use it instead
            if ($product_row->mainproduct_id && $product_row->mainproduct_id != 0) {
                $product_id_to_use = $product_row->mainproduct_id;
            }
        }
        
        // Now get variants for the determined product (either original or the raw material itself)
        $this->db->select('id, name');
        $this->db->from('sma_product_variants');
        $this->db->where('product_id', $product_id_to_use);
        $this->db->where('group_id !=', 2); // Exclude Color variants (group_id = 2)
        $this->db->order_by('id', 'ASC');
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        // If no variants found in database, return empty array
        return array();
    }

    public function getProductVariants($product_id)
    {
        $this->db->select('id, name');
        $this->db->from('sma_product_variants');
        $this->db->where('product_id', $product_id);
        $this->db->where('group_id !=', 2); // Exclude Color variants (group_id = 2)
        $this->db->order_by('id', 'ASC');
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        // If no variants found in database, return empty array
        return array();
    }
    
    // Get all data for vandors stock report's Grid
    public function getVendorStockList($warehouse = NULL, $supplier = NULL, $product = NULL, $start = 0, $limit = 25, $search = NULL, $order_col = NULL, $order_dir = 'asc')
    {
        $this->db->select("\n            c.id AS supplier_id,\n            p.id AS product_id,\n            c.name AS supplier_name,\n            p.name AS product_name,\n            u.name AS unit_name,\n            w.name AS warehouse_name,\n            COALESCE(wp.quantity, 0) AS quantity,\n            COALESCE(vrs.agg_total_req_qty, 0) AS pending_required,\n            (COALESCE(vrs.agg_total_req_qty, 0) - COALESCE(vrs.agg_total_supplied, 0)) AS to_supply\n        ", FALSE);

        $this->db->from('sma_companies c');
        $this->db->join('sma_warehouses w', 'w.id = c.location_id', 'left');
        $this->db->join('sma_location_type lt', 'lt.id = w.location_type', 'left');
        $this->db->join('sma_warehouses_products wp', 'wp.warehouse_id = c.location_id', 'left');
        $this->db->join('sma_products p', 'p.id = wp.product_id AND p.type = "raw"', 'left');
        $this->db->join('sma_units u', 'u.id = p.unit', 'left');
        // Aggregated vendor RM stock to avoid duplication per warehouse
        $this->db->join('(
            SELECT supplier_id, rm_product_id,
                   SUM(total_req_qty) AS agg_total_req_qty,
                   SUM(total_supplied) AS agg_total_supplied
            FROM sma_vendor_rm_stock
            GROUP BY supplier_id, rm_product_id
        ) vrs', 'vrs.supplier_id = c.id AND vrs.rm_product_id = p.id', 'left', FALSE);

        // Filters
        if (!empty($warehouse)) {
            $list = str_replace("_", ",", $warehouse);
            $this->db->where("c.location_id IN ($list)");
        }
        if (!empty($supplier)) {
            $this->db->where("c.id", $supplier);
        }
        if (!empty($product)) {
            if (is_array($product)) {
                $this->db->where_in("p.id", $product);
            } else {
                $this->db->where("p.id", $product);
            }
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.name', $search);
            $this->db->or_like('p.name', $search);
            $this->db->or_like('w.name', $search);
            $this->db->group_end();
        }

        $this->db->where("p.type", "raw");
        // Only vendor-location warehouses
        $this->db->where('lt.type', 'Vendor');

        // Grouping for aggregates from vendor_rm_stock
        $this->db->group_by(array('c.id', 'p.id', 'w.id'));

        // Apply ordering based on column index
        // 0: supplier_name, 1: product_name, 2: unit_name, 3: warehouse_name, 4: quantity
        $dir = strtolower($order_dir) === 'desc' ? 'DESC' : 'ASC';
        switch ($order_col) {
            case 0: $this->db->order_by('c.name', $dir); break;
            case 1: $this->db->order_by('p.name', $dir); break;
            case 2: $this->db->order_by('u.name', $dir); break;
            case 3: $this->db->order_by('w.name', $dir); break;
            case 4: $this->db->order_by('wp.quantity', $dir); break;
            default:
                $this->db->order_by('c.name', 'ASC');
                $this->db->order_by('p.name', 'ASC');
                $this->db->order_by('u.name', 'ASC');
                $this->db->order_by('w.name', 'ASC');
        }

        if ($limit && (int)$limit > 0) {
            $this->db->limit((int)$limit, (int)$start);
        }

        return $this->db->get()->result();
    }

    // Count grouped rows for datatables
    public function countVendorStock($warehouse = NULL, $supplier = NULL, $product = NULL, $search = NULL)
    {
        $sql = "
            SELECT COUNT(*) AS cnt FROM (
                SELECT 1
                FROM sma_companies c
                LEFT JOIN sma_warehouses w ON w.id = c.location_id
                LEFT JOIN sma_location_type lt ON lt.id = w.location_type
                LEFT JOIN sma_warehouses_products wp ON wp.warehouse_id = c.location_id
                LEFT JOIN sma_products p ON p.id = wp.product_id AND p.type = 'raw'
                WHERE 1 = 1 AND lt.type = 'Vendor'
            ";

        $bind = array();

        if (!empty($warehouse)) {
            $list = str_replace("_", ",", $warehouse);
            $sql .= " AND c.location_id IN ($list)";
        }
        if (!empty($supplier)) {
            $sql .= " AND c.id = ?";
            $bind[] = $supplier;
        }
        if (!empty($product)) {
            $sql .= " AND p.id = ?";
            $bind[] = $product;
        }
        if (!empty($search)) {
            $sql .= " AND (c.name LIKE ? OR p.name LIKE ? OR w.name LIKE ?)";
            $s = "%$search%";
            $bind[] = $s; $bind[] = $s; $bind[] = $s;
        }

        $sql .= " GROUP BY c.id, p.id, w.id ) X";

        $q = $this->db->query($sql, $bind);
        $r = $q->row();
        return $r ? $r->cnt : 0;
    }

    // Get all data for vandors stock report's Excel/PDF export
    public function getVendorStockExport($warehouse = NULL, $supplier = NULL, $product = NULL)
    {
        $this->db->select("
            c.name AS supplier_name,
            p.name AS product_name,
            u.name AS unit_name,
            w.name AS warehouse_name,
            COALESCE(wp.quantity, 0) AS quantity,
            COALESCE(vrs.agg_total_req_qty, 0) AS pending_required,
            GREATEST(COALESCE(vrs.agg_total_req_qty, 0) - COALESCE(vrs.agg_total_supplied, 0), 0) AS to_supply
        ", FALSE);

        $this->db->from('sma_companies c');
        $this->db->join('sma_warehouses w', 'w.id = c.location_id', 'left');
        $this->db->join('sma_location_type lt', 'lt.id = w.location_type', 'left');
        $this->db->join('sma_warehouses_products wp', 'wp.warehouse_id = c.location_id', 'left');
        $this->db->join('sma_products p', 'p.id = wp.product_id AND p.type = "raw"', 'left');
        $this->db->join('sma_units u', 'u.id = p.unit', 'left');

        // FIXED SUBQUERY
        $subquery = "(SELECT supplier_id, rm_product_id,
                            SUM(total_req_qty) AS agg_total_req_qty,
                            SUM(total_supplied) AS agg_total_supplied
                    FROM sma_vendor_rm_stock
                    GROUP BY supplier_id, rm_product_id) vrs";

        $this->db->join($subquery, "vrs.supplier_id = c.id AND vrs.rm_product_id = p.id", 'left', FALSE);

        if (!empty($warehouse)) {
            $list = str_replace("_", ",", $warehouse);
            $this->db->where("c.location_id IN ($list)");
        }
        if (!empty($supplier)) {
            $this->db->where("c.id", $supplier);
        }
        if (!empty($product)) {
            $this->db->where("p.id", $product);
        }

        $this->db->where("p.type", "raw");
        $this->db->where('lt.type', 'Vendor');
        $this->db->group_by(array('c.id', 'p.id', 'w.id'));
        $this->db->order_by("c.name, p.name, w.name");

        return $this->db->get()->result();
    }

    public function get_all_suppliers()
    {
        return $this->db
            ->select('id, name')
            ->from('sma_companies')
            ->where('group_name', 'supplier')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }
    public function get_raw_products()
    {
        return $this->db
            ->select('id, name')
            ->from('sma_products')
            ->where('type', 'raw')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }
    
    // Per-PO breakdown for a vendor and raw material
    // public function getVendorRMDetails($supplier_id, $product_id)
    // {
    //     $this->db->select("\n            po_reference_no,\n            SUM(total_req_qty) AS total_req_qty,\n            SUM(total_supplied) AS total_supplied,\n            SUM(COALESCE(total_consumed_till_date, 0)) AS total_consumed_till_date\n        ");
    //     $this->db->from('sma_vendor_rm_stock');
    //     $this->db->where('supplier_id', (int)$supplier_id);
    //     $this->db->where('rm_product_id', (int)$product_id);
    //     $this->db->group_by('po_reference_no');

    //     $rows = $this->db->get()->result();
    //     // compute derived fields on php side for consistency
    //     foreach ($rows as $r) {
    //         $locked = (float)$r->total_supplied - (float)$r->total_consumed_till_date;
    //         $need   = (float)$r->total_req_qty - (float)$r->total_supplied;
    //         $r->locked_stock = $locked;
    //         $r->need_to_supply = $need;
    //     }
    //     return $rows;
    // }
    public function getVendorRMDetails($supplier_id, $product_id)
    {
        $this->db->select("
            vrs.po_reference_no,
            SUM(vrs.total_req_qty) AS total_req_qty,
            SUM(vrs.total_supplied) AS total_supplied,
            SUM(COALESCE(vrs.total_consumed_till_date, 0)) AS total_consumed_till_date,
            u.name AS unit_name
        ", FALSE);

        $this->db->from('sma_vendor_rm_stock vrs');

        // JOIN product to get unit
        $this->db->join('sma_products p', 'p.id = vrs.rm_product_id', 'left');
        $this->db->join('sma_units u', 'u.id = p.unit', 'left');

        $this->db->where('vrs.supplier_id', (int)$supplier_id);
        $this->db->where('vrs.rm_product_id', (int)$product_id);

        $this->db->group_by('vrs.po_reference_no, u.name');

        $rows = $this->db->get()->result();

        // compute derived fields on php side for consistency
        foreach ($rows as $r) {
            $locked = (float)$r->total_supplied - (float)$r->total_consumed_till_date;
            $need   = (float)$r->total_req_qty - (float)$r->total_supplied;

            $r->locked_stock   = $locked;
            $r->need_to_supply = $need;
        }

        return $rows;
    }

    /**
     * Last underscore segment of material product name (used as variant label tail).
     */
    public function get_variant_label_tail_from_material_name($material_id)
    {
        $material_id = (int) $material_id;
        if (!$material_id) {
            return '';
        }
        $row = $this->db->select('name')->from('sma_products')->where('id', $material_id)->limit(1)->get()->row();
        if (!$row || $row->name === '') {
            return '';
        }
        $name = $row->name;
        $pos  = strrpos($name, '_');
        if ($pos === false) {
            return '';
        }
        $tail = trim(substr($name, $pos + 1));
        return $tail !== '' ? $tail : '';
    }

    protected function _bom_variant_rows_for_product($product_id)
    {
        $product_id = (int) $product_id;
        if ($product_id <= 0) {
            return array();
        }
        $this->db->select('id, name');
        $this->db->from('sma_product_variants');
        $this->db->where('product_id', $product_id);
        $this->db->order_by('id', 'ASC');
        $q = $this->db->get();
        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    protected function _bom_match_variant_label($label, $rows)
    {
        $label = trim(strtolower($label));
        if ($label === '' || $label === 'n/a') {
            return 0;
        }
        foreach ($rows as $r) {
            $n = isset($r['name']) ? trim(strtolower($r['name'])) : '';
            if ($n !== '' && $label === $n) {
                return (int) $r['id'];
            }
        }
        foreach ($rows as $r) {
            $n = isset($r['name']) ? trim(strtolower($r['name'])) : '';
            if ($n === '') {
                continue;
            }
            if (strpos($label, $n) !== false || strpos($n, $label) !== false) {
                return (int) $r['id'];
            }
        }
        if (ctype_digit($label)) {
            $want = (int) $label;
            foreach ($rows as $r) {
                if ((int) $r['id'] === $want) {
                    return $want;
                }
            }
        }
        return 0;
    }

    /**
     * Resolve option_id when UI sent 0 but we have a textual variant label (Input/DCC flow).
     */
    public function resolve_bom_line_option_id_for_save($header_product_id, $material_id, $variant_label, $mainproduct_id = null)
    {
        $header_product_id = (int) $header_product_id;
        $material_id       = (int) $material_id;
        $variant_label     = trim((string) $variant_label);
        if ($header_product_id <= 0 || $material_id <= 0 || $variant_label === '') {
            return 0;
        }

        // Start with header product variants
        $merged = $this->_bom_variant_rows_for_product($header_product_id);

        // Optionally also merge mainproduct variants if different
        if ($mainproduct_id && (int) $mainproduct_id > 0 && (int) $mainproduct_id !== $header_product_id) {
            $extra = $this->_bom_variant_rows_for_product((int) $mainproduct_id);
            $seen  = array();
            foreach ($merged as $r) {
                $seen[(int) $r['id']] = true;
            }
            foreach ($extra as $r) {
                $id = (int) $r['id'];
                if (!isset($seen[$id])) {
                    $merged[]     = $r;
                    $seen[$id] = true;
                }
            }
        }

        $hit = $this->_bom_match_variant_label($variant_label, $merged);
        if ($hit > 0) {
            return $hit;
        }

        // Fallback: try variants defined directly on the material product
        return $this->_bom_match_variant_label($variant_label, $this->_bom_variant_rows_for_product($material_id));
    }
}