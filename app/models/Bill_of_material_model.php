<?php
class Bill_of_material_model extends CI_Model
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
    public function getProductByCategories($categoryID = null, $subcategory_id = null)
    {
        $data = [];

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
                p.flag_visible = 1
                AND p.type NOT IN ('raw')
        ";

        if (!empty($categoryID)) {
            $query .= " AND (p.category_id = " . (int)$categoryID . " OR FIND_IN_SET(" . (int)$categoryID . ", p.other_categories) > 0)";
        }
        if (!empty($subcategory_id)) {
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
        $this->db->where('type', 'raw');
        $this->db->where('flag_visible', 1);
        $q = $this->db->get('sma_products');

        if ($q->num_rows() > 0) {
            $data = [];
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return false;
    }
    public function getBomDetailsByProductId($product_id)
    {
        if ($product_id) {
            // Step 1: Get all BOMs for the product
            $this->db->select('b.*, p.name as name, p.code as code, pb.is_batch_only, pb.min_batch_qty, pb.batch_uom, pb.sales_units');
            $this->db->from('sma_produnit_bill_of_materials b');
            $this->db->join('sma_products p', 'p.id = b.product_id', 'left');
            $this->db->join('sma_produnit_products_in_batches pb', 'pb.product_id = p.id', 'left');
            $this->db->where('b.product_id', $product_id);
            $this->db->order_by('CAST(version_no AS UNSIGNED)', 'DESC'); // Optional: keep BOMs ordered by version
            $this->db->group_by('b.id');

            $q = $this->db->get();

            if ($q->num_rows() > 0) {
                $boms = $q->result();
                foreach ($boms as &$bom) {
                    // Step 2: For each BOM, get its items
                    $this->db->select('i.*, m.name as name, m.code as code');
                    $this->db->from('produnit_bill_of_material_items i');
                    $this->db->join('sma_products m', 'm.id = i.material_id', 'left');
                    $this->db->where('i.bom_id', $bom->id);
                    $items_q = $this->db->get();

                    if ($items_q->num_rows() > 0) {
                        $items = $items_q->result();
                        $unique_items = [];

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
                        $bom->items = [];
                    }
                }

                return $boms; // Return array of BOMs with unique items
            }

            // Step 3: No BOMs found — return product info only
            $this->db->select('id as product_id, name as name');
            $this->db->from('sma_products');
            $this->db->where('id', $product_id);
            $p = $this->db->get();

            if ($p->num_rows() > 0) {
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
                $emptyBom->items = [];

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
    $sql = "
        SELECT 
            bom_items.bom_id,
            bom_items.material_id,
            bom_items.bom_ref,
            bom_items.quantity_required,
            bom_items.uom AS uom_id,
            sma_products.name AS raw_material,
            bom_items.wastage_percent AS wastage_percent,
            bom_items.primary_product_id AS primary_product_id,
            alternative_product.name AS alternative_material_name,
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
            bom_items.is_alternative,
            bom_items.primary_product_id
        FROM sma_produnit_bill_of_material_items AS bom_items
        LEFT JOIN sma_products 
            ON bom_items.material_id = sma_products.id
        LEFT JOIN sma_products AS alternative_product
            ON bom_items.primary_product_id = alternative_product.id
        LEFT JOIN sma_produnit_bill_of_materials
            ON sma_produnit_bill_of_materials.id = bom_items.bom_id
        LEFT JOIN sma_units AS bom_uom
            ON bom_items.uom = bom_uom.id
        LEFT JOIN sma_produnit_products_in_batches AS pb
            ON pb.bom_id = bom_items.bom_id
        WHERE bom_items.bom_id = ".(int)$bom_id."
        
       ORDER BY 
            COALESCE(bom_items.primary_product_id, bom_items.material_id),
            CASE 
                WHEN bom_items.is_alternative = 1 THEN 1 
                ELSE 0 
            END,
            bom_items.material_id

    ";
    return $this->db->query($sql)->result_array();
}

}