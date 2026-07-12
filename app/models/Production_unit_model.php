<?php
class Production_Unit_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->created_by      = $this->session->userdata('user_id');
       
    }
    // get location data
    public function getLocations() {

        $this->db->where_in('location_type', array('3', '4')); //location_type != Production Unit and HO, means show only Retail Outlet and Stockist
        $q = $this->db->get('warehouses');
            if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    //get order details
    public function getProcurementRefrenceNo($locationName, $Filter = Null) {
        
        $this->db->select('poi.id as itemId, poi.procurement_order_ref_no,poi.order_creation_date,poi.location_name,poi.location_code, poi.status,  poi.note, poi.planned_delivery_datetime');
        $this->db->from('procurement_orders poi');
        // $this->db->order_by("poi.id", "desc");
        $this->db->where('poi.status !=', 'Received');
        if (!$this->Owner || !$this->Admin) {
            if ($created_by) {
                $this->db->where('po.created_by', $created_by); 
            }
        }
        if ($locationName) {
            $this->db->where('poi.location_name', $locationName);
        }
        if ($Filter == 'Oldest') {
            $this->db->order_by('poi.order_creation_date', 'asc');
        } elseif($Filter == 'Newest') {
            $this->db->order_by('poi.order_creation_date', 'desc');
        }else{
            $this->db->order_by("poi.id", "asc");
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
              
                $data[] = $row;

            }
            return $data;
        }
        return FALSE;

    }
    //  get order items details
    public function getProcurementdetails($procurmentRefNo = null,$status= null,$itemId= null, $location_id) {
        
        // Use warehouses_products for stock and derive open_order_quantity from procurement items
        $this->db->select("
            poi.id as itemId,
            poi.procurement_orders_id,
            poi.product_id,
            poi.product_name,
            poi.order_quantity,
            poi.allot_quantity,
            poi.product_code,
            poi.item_status,
            poi.production_unit_name,
            po.procurement_order_ref_no,
            po.location_name,
            po.status,
            wp.quantity as stock_quantity,
            (
                SELECT COALESCE(SUM(poi2.order_quantity - COALESCE(poi2.allot_quantity, 0)), 0)
                FROM sma_procurement_order_items poi2
                WHERE poi2.product_id = poi.product_id
                  AND poi2.production_unit_id = poi.production_unit_id
                  AND poi2.item_status IN ('Open','Locked','Committed','partially_completed')
            ) AS open_order_quantity,
            po.note,
            poi.production_unit_id
        ", false);
        $this->db->from('procurement_order_items poi');
        // $this->db->join('procurement_orders po', 'po.procurement_order_ref_no = poi.procurement_orders_id', 'left');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id', 'left');
        $this->db->join('products ', 'products.id = poi.product_id', 'left');
        // Aggregate warehouses_products to one row per (product_id, warehouse_id) to avoid duplicates
        $this->db->join('(
            SELECT product_id, warehouse_id, SUM(quantity) AS quantity
            FROM sma_warehouses_products
            GROUP BY product_id, warehouse_id
        ) wp', 'wp.product_id = poi.product_id AND wp.warehouse_id = poi.production_unit_id', 'left');
        
        // for showing latest order items data initially
        $procurement_orders_id = $procurmentRefNo[0];
        if ($procurement_orders_id) {
            $this->db->where('poi.procurement_orders_id', $procurement_orders_id);
        }
        if ($itemId) {
            $this->db->where('poi.id', $itemId);
        }
        if ($procurmentRefNo) {
            $this->db->where_in('poi.procurement_orders_id',  $procurmentRefNo);
        }
        if ($status) {
            $this->db->where('poi.item_status', $status);
        }
        if ($location_id) {
            $this->db->where('poi.production_unit_id', $location_id);
        }
        if (!$this->Owner || !$this->Admin) {
            if ($created_by) {
                $this->db->where('po.created_by', $created_by);

            }
        }
        $this->db->order_by("po.id", "desc");

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
   #===========================================   PROCURMENT ORDER SCREEN  ============================================
    public function getProductCategoriesList() {

        // $query = $this->db->query("SELECT parent.id AS parent_id, parent.name AS parent_name, sub.id AS subcategory_id, sub.name AS subcategory_name 
        // FROM sma_categories AS parent 
        // LEFT JOIN sma_categories AS sub ON parent.id = sub.parent_id");

        $query = $this->db->query("SELECT parent.id AS parent_id, parent.name AS parent_name, sub.id AS subcategory_id, sub.name AS subcategory_name 
                FROM sma_categories AS parent 
                LEFT JOIN sma_categories AS sub ON parent.id = sub.parent_id 
                WHERE parent.flag_visible = 1");


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
            usort($category['subcategories'], function($a, $b) {
            return strcmp($a['subcategory_name'], $b['subcategory_name']);
            });
            }
            unset($category); // unset reference

            // Sort categories by category_name
            usort($categories, function($a, $b) {
            return strcmp($a['category_name'], $b['category_name']);
            });

            return $categories;
            } else {
            return FALSE;
            }

        // echo json_encode($categories);


    }
    ////////////////////////////////   getProductByCategories //////////////////////////////////////////
    public function getProductByCategories($categoryID = null, $subcategory_id = null, $search = null, $warehouse_id = null) {
    
        // Initialize data array
        $data = array();
        $warehouse_id = (int) $warehouse_id;

        $query = "
            SELECT 
                products.*, 
                categories.name AS categoryName,  
                units.name AS unitName, 
                tax_rates.name AS tax_rate, 
                COALESCE(pp_sub.price, products.price) AS c_price
            FROM 
                sma_products AS products
            LEFT JOIN 
                sma_tax_rates AS tax_rates ON tax_rates.id = products.tax_rate
            JOIN 
                sma_categories AS categories ON categories.id = products.category_id
            LEFT JOIN 
                sma_warehouses AS w ON w.id = {$warehouse_id}
            LEFT JOIN (
                SELECT 
                    product_id, price_group_id, price
                FROM 
                    sma_product_prices
            ) AS pp_sub 
                ON pp_sub.product_id = products.id 
                AND (
                    pp_sub.price_group_id = w.price_group_id 
                    OR w.price_group_id IS NULL
                )
            JOIN 
                sma_units AS units ON units.id = products.unit
            WHERE 
                products.flag_visible = 1
                AND products.type != 'raw'
        ";

            // Optional filters
            if (!empty($categoryID)) {
                $query .= " AND products.category_id = " . (int)$categoryID;
            }
            if (!empty($subcategory_id)) {
                $query .= " AND products.subcategory_id = " . (int)$subcategory_id;
            }
            if (!empty($search)) {
                $search = $this->db->escape_str($search);
                $query .= " AND (products.name LIKE '%$search%' OR products.code LIKE '%$search%' OR products.article_code LIKE '%$search%')";
            }

            // Final order clause
            $query .= " GROUP BY products.id ORDER BY products.name ASC";

            $q = $this->db->query($query);
            // return $result->result();
       
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        
        return FALSE;
    } 
    public function PlaceProcurementOrder($order, $Items){
       
        if ($this->db->insert('procurement_orders', $order)) {
            $orderId['procurement_orders_id'] = $this->db->insert_id();
            // Merge duplicate products (same product_id + production_unit_id) into single row with summed quantities
            $merged_items = array();
            foreach ($Items as $item) {
                $key = (isset($item['product_id']) ? $item['product_id'] : '') . '_' . (isset($item['production_unit_id']) ? $item['production_unit_id'] : '');
                if (isset($merged_items[$key])) {
                    $merged_items[$key]['order_quantity'] += isset($item['order_quantity']) ? (float) $item['order_quantity'] : 0;
                    $merged_items[$key]['subtotal'] = (float) $merged_items[$key]['subtotal'] + (isset($item['subtotal']) ? (float) $item['subtotal'] : 0);
                    $merged_items[$key]['tax'] = (float) $merged_items[$key]['tax'] + (isset($item['tax']) ? (float) $item['tax'] : 0);
                    $merged_items[$key]['item_tax'] = (float) $merged_items[$key]['item_tax'] + (isset($item['item_tax']) ? (float) $item['item_tax'] : 0);
                    $merged_items[$key]['net_price'] = (float) $merged_items[$key]['net_price'] + (isset($item['net_price']) ? (float) $item['net_price'] : 0);
                } else {
                    $merged_items[$key] = $item;
                }
            }
            $merged_array = array();
            foreach ($merged_items as $item) {
                $merged_array[] = array_merge($item, $orderId);
            }
            if ($this->db->insert_batch('procurement_order_items', $merged_array)) {
                return true ;
            }
        }
        return false;
    }
    public function getProcurmentOrders($status, $order_dispatch){

      
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
        $this->db->select('
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
            $status_conditions = array('Locked', 'Completed', 'Received','Rejected','partially_completed','Dispatched');
            $this->db->where_in('po.status', $status_conditions);
        }
        if ($status == 'partially') {
            // $Item_status = array('pending','partially_completed');
            // $this->db->where_in('poi.item_status', $Item_status);
            // Include all valid order statuses that could have partial items
            $order_status_conditions = array('Locked', 'Dispatched', 'partially_completed', 'Completed');
            $this->db->where_in('po.status', $order_status_conditions);
            
            // Show orders that have unfulfilled quantities regardless of item status
            // This ensures dispatched orders with remaining quantities stay visible
            $this->db->where('(poi.allot_quantity < poi.order_quantity OR poi.allot_quantity IS NULL OR poi.allot_quantity = 0)', null, false);
        }
        if ($status == 'Open') {
            $this->db->where('po.status', 'Open'); 
        }
        if ($status == 'Received') {
            if($order_dispatch == '1'){
                // $Item_status = array('Completed','Dispatched','Received','partially_completed');
                $Item_status = array('Dispatched','partially_completed');

            }else{
                $Item_status = array('Completed','partially_completed');
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
    public function getOrdersByItems($order_id,$status ){
      
        $this->db->select('poi.id as order_id,poi.unit_price as unit_price, poi.received_quantity as received_quantity,po.id as itemsId,  poi.allot_quantity as allot_quantity,   po.note as order_note,poi.product_name,poi.order_quantity,poi.subtotal,poi.item_status,units.name as unitName,cat.name as categoryName,po.procurement_order_ref_no,po.created_by as placedBy,po.order_creation_date as placedOn,po.actual_delivery_date as receivedOn,po.status as orderStatus,products.name as product_name,products.tax_method as tax_method,tax_rates.name as tax_rate,poi.order_quantity as quantity, poi.adjustment_price');        
        $this->db->from('procurement_order_items poi');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id','left');
        $this->db->join('units', 'units.id = poi.product_unit_id');
        $this->db->join('products', 'products.id = poi.product_id');
        // $this->db->join('tax_rates', 'tax_rates.id = products.tax_rate');
        $this->db->join('tax_rates', 'tax_rates.id = poi.tax_rate_id', 'left');

        $this->db->join('categories cat', 'cat.id = products.category_id');
        // if ($order_id) {
            $this->db->where('poi.procurement_orders_id', $order_id); 
        if ($status == 'partial_order_item') {
            // $Item_status = array('pending','partially_completed');
            // $this->db->where_in('poi.item_status', $Item_status);
            // Show all items with unfulfilled quantities regardless of status
            $this->db->where('(poi.allot_quantity < poi.order_quantity OR poi.allot_quantity IS NULL OR poi.allot_quantity = 0)', null, false);
        }
        // if ($status == 'Received_orders') {
        //     $this->db->where('po.actual_delivery_date', '0000-00-00 00:00:00');
        // }
            if (!$this->Owner || !$this->Admin) {
                if ($this->created_by) {
                    $this->db->where('po.created_by', $this->created_by);
                }
            }
        // }
        // $this->db->order_by("poi.procurement_orders_id", "DESC");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;    
    }
    public function getOrderByItems($order_id,$product_id){
      
        $this->db->select('poi.id as order_id,poi.unit_price as unit_price, poi.received_quantity as received_quantity,po.id as itemsId, po.location_id as location_id, poi.allot_quantity as allot_quantity,   po.note as order_note,poi.product_name,poi.order_quantity,poi.subtotal,poi.item_status,units.name as unitName,cat.name as categoryName,po.procurement_order_ref_no,po.created_by as placedBy,po.order_creation_date as placedOn,po.actual_delivery_date as receivedOn,po.status as orderStatus,products.name as product_name,products.tax_method as tax_method,tax_rates.name as tax_rate,poi.order_quantity as quantity');        
        $this->db->from('procurement_order_items poi');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id','left');
        $this->db->join('units', 'units.id = poi.product_unit_id');
        $this->db->join('products', 'products.id = poi.product_id');
        $this->db->join('tax_rates', 'tax_rates.id = products.tax_rate');
        $this->db->join('categories cat', 'cat.id = products.category_id');
        // if ($order_id) {
            $this->db->where('poi.procurement_orders_id', $order_id); 
            $this->db->where('poi.product_id', $product_id); 
            if (!$this->Owner || !$this->Admin) {
                if ($this->created_by) {
                    $this->db->where('po.created_by', $this->created_by);
                }
            }
        // }
        // $this->db->order_by("poi.procurement_orders_id", "DESC");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                // $data = $row->order_id;
                $data = $row;
            }
            return $data;
        }
        return FALSE;    
    }
  ///////////////////////////// UPDATE ORDER /////////////////////////////////////////////////////
    public function updateProcurementOrder($id, $order, $Items, $selectedHorizonatlTabValue) {
      
        if ($selectedHorizonatlTabValue === 'received_order') {
          
            $this->db->where('id', $id);
            if ($this->db->update('procurement_orders', $order)) {
            }
            $merged_array = array();
            $data = array(
                'procurement_orders_id' => $id,
            );
            foreach ($Items as $item) {
                // Merge each $item with $data
                $merged_array[] = array_merge($item, $data);
                $received_quantity = $item['received_quantity'];
                $product_id       = $item['product_id'];
                $location_id      = $order['location_id'];

                // $WarehouseProductsData = $this->site->getWarehouseProducts($product_id, $location_id); //get warehouse product data
                // $WarehouseProductsQuantity = 0;

                // if (!empty($WarehouseProductsData)) {
                //     foreach ($WarehouseProductsData as $WarehouseProducts) {
                //         $WarehouseProductsQuantity = $WarehouseProducts->quantity;
                //     }
                //     $WarehouseProductsStock = $received_quantity + $WarehouseProductsQuantity;

                //     $this->db->where('product_id', $product_id);
                //     $this->db->where('warehouse_id', $location_id);
                //     $this->db->update('sma_warehouses_products', array('quantity' => $WarehouseProductsStock));
                // } else {

                //     $this->db->insert('sma_warehouses_products', array(
                //         'product_id' => $product_id,
                //         'warehouse_id' => $location_id,
                //         'quantity' => $received_quantity
                //     ));
                // }

                // // Update the quantity in the products table
                // $this->db->set('quantity', 'quantity + ' . (int)$received_quantity, FALSE);
                // $this->db->where('id', $product_id);
                // $this->db->update('sma_products');
            }

            try {
                $this->db->update_batch('procurement_order_items', $merged_array, 'id');
                return TRUE;
            } catch (Exception $e) {
                // Handle exceptions or errors
                log_message('error', 'Error updating batch: ' . $e->getMessage());
                return FALSE;
            }
        }elseif ($selectedHorizonatlTabValue === 'reject') {
          
            $this->db->where('id', $id);
            if ($this->db->update('procurement_orders', $order)) {
            }
            $merged_array = array();
            $data = array(
                'procurement_orders_id' => $id,
            );
            foreach ($Items as $item) {
                // Merge each $item with $data
                $merged_array[] = array_merge($item, $data);
                $received_quantity = $item['received_quantity'];
                $product_id       = $item['product_id'];
                $location_id      = $order['location_id'];

                $WarehouseProductsData = $this->site->getWarehouseProducts($product_id, $location_id); //get warehouse product data
                foreach ($WarehouseProductsData as $WarehouseProducts) { 
                    $WarehouseProductsQuantity = $WarehouseProducts->quantity;
                }
                $WarehouseProductsStock = $received_quantity + $WarehouseProductsQuantity;
               
                // $this->db->where('product_id', $product_id);
                // $this->db->where('warehouse_id', $location_id);
                // $this->db->update('sma_warehouses_products', array('quantity' => $WarehouseProductsStock)); // stock update against location and product
            }

            try {
                $this->db->update_batch('procurement_order_items', $merged_array, 'id');
                return TRUE;
            } catch (Exception $e) {
                // Handle exceptions or errors
                log_message('error', 'Error updating batch: ' . $e->getMessage());
                return FALSE;
            }
        }else{
          
            if ($this->db->update('procurement_orders', $order,array('id' => $id))) {
                $this->db->delete('procurement_order_items', array('procurement_orders_id' => $id));
            }
            $merged_array = array();
            $data = array(
                'procurement_orders_id' => $id,
            );
            foreach ($Items as $item) {
                $merged_array[] = array_merge($item, $data);
            }  
               
            if ($this->db->insert_batch('procurement_order_items', $merged_array)) {
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;

    }

    public function getOrder($location_code) {
        
        $this->db->select('procurement_order_ref_no');
        if($location_code){
            $this->db->like('procurement_order_ref_no', $location_code, 'after'); 
        }
        $this->db->order_by('procurement_orders.id', 'DESC'); // Order by procurement_orders.id in descending order
        $q = $this->db->get('procurement_orders'); 
        
        if ($q->num_rows() > 0) {
            return $q->row(); 
        }
        return FALSE;
        
    }
    public function getProductionUnitDetailsForProduct($product_id) {
        // Use warehouses_products; prefer warehouses whose location_type is Production Unit
        $this->db->select('wp.product_id, wp.warehouse_id as location_id, wp.quantity as stock_quantity');
        $this->db->from('sma_warehouses_products wp');
        $this->db->join('sma_warehouses w', 'w.id = wp.warehouse_id', 'left');
        $this->db->join('sma_location_type lt', 'lt.id = w.location_type', 'left');
        $this->db->where('wp.product_id', $product_id);
        $this->db->where('lt.type', 'Production Unit');
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getUnmappedProducts($product_ids) {
        $this->db->select('id, name');
        $this->db->from('products');
        $this->db->where_in('id', $product_ids);
        // Products that do not have a warehouses_products row for any Workstation-type warehouse
        $this->db->where("id NOT IN (
            SELECT DISTINCT wp.product_id
            FROM " . $this->db->dbprefix('warehouses_products') . " wp
            JOIN " . $this->db->dbprefix('warehouses') . " w ON w.id = wp.warehouse_id
            JOIN " . $this->db->dbprefix('location_type') . " lt ON lt.id = w.location_type
            WHERE lt.type = 'Production Unit'
        )", NULL, FALSE);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }
    public function getUnmappedProductsByNames($product_names) {
        $this->db->select('id, name');
        $this->db->from('products');
        $this->db->where_in('name', $product_names);
        $this->db->where("id NOT IN (
            SELECT DISTINCT wp.product_id
            FROM " . $this->db->dbprefix('warehouses_products') . " wp
            JOIN " . $this->db->dbprefix('warehouses') . " w ON w.id = wp.warehouse_id
            JOIN " . $this->db->dbprefix('location_type') . " lt ON lt.id = w.location_type
            WHERE lt.type = 'Production Unit'
        )", NULL, FALSE);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }
    public function getWarehouseByID($id) {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
      return FALSE;
    }

    public function getProductDetailsByName($name = Null, $product_id = Null) {    
        $product_id = ($product_id !== null && $product_id !== '') ? (int) trim((string) $product_id, '"') : 0;

        $this->db->select('products.*, units.name as unit_name'); 
        $this->db->join('units', 'products.unit = units.id', 'left');
        if ($product_id > 0) {
            $this->db->where('sma_products.id', $product_id);
        } elseif ($name !== null && trim((string) $name) !== '') {
            $name_trimmed = trim((string) $name);
            // Try name first (TRIM handles extra spaces in DB), then code as fallback
            $this->db->group_start();
            $this->db->where('TRIM(sma_products.name)', $name_trimmed);
            $this->db->or_where('TRIM(sma_products.code)', $name_trimmed);
            $this->db->group_end();
        } else {
            return FALSE;
        }
        $q = $this->db->get('products', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getUnitById($id) {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
   #=========================================== END  PROCURMENT ORDER SCREEN  ============================================

    // update item stock and allot quantity
    public function updateOrderItemStock($quantity, $itemId, $itemData, $procurmentDetails,$checkbox, $Complete_unit_order, $datetime,  $status) {
  
        $order_data =  $this->getProcurementdetails($procurmentId = null, null, $itemId);
        foreach($order_data as $data){
           
            $product_id            = $data->product_id;
            $stock_quantity        = $data->stock_quantity;
            $procurement_orders_id = $data->procurement_orders_id;
        }
        $new_stock_quantity = $stock_quantity - $quantity;  
        $old_stock_quantity = $stock_quantity + $quantity;  //increase stock 

        // update allot quantity and item status if order is locked and click on checkbox
        if (!empty($quantity)) {
            if($checkbox == 0){ 
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => '0', 'item_status' => 'Locked'), array('id' => $itemId));
            }else{
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => $quantity, 'item_status' => 'Committed'), array('id' => $itemId));
            }
        }
        // update order and order item status if order is locked and also update status after status is Committed
        if($procurmentDetails){
            foreach($procurmentDetails as $value){
                $procurement_orders_id = $value->procurement_orders_id;
                $itemId                = $value->itemId;
                $item_status           = $value->item_status;
                $allot_quantity        = $value->allot_quantity;
                $order_quantity        = $value->order_quantity;

                // If item status is 'open', update status to 'locked'
                if($status == 'Locked'){
                    $this->db->update('sma_procurement_order_items', array('item_status' => 'Locked'), array('id' => $itemId));
                    $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurement_orders_id));
                }
                if ($Complete_unit_order == 'true') {
                    // If item status is 'Committed', update item status to 'completed'(for complete order)
                    if($allot_quantity == $order_quantity){
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'Completed'), array('id' => $itemId));
                        // $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                    
                    }elseif($allot_quantity < $order_quantity){
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'partially_completed'), array('id' => $itemId));
                        // $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                    }
                    else{
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'Pending'), array('id' => $itemId));
                    }
                    // check all kitchen items status then update order status
                    // if($item_status == 'Completed' || $item_status == 'partially_completed'){
                    //     $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                    // }else{
                    //     $this->db->update('sma_procurement_orders', array('status' => 'partially_completed'), array('id' => $procurement_orders_id));
                    // }
                }
                 // check all kitchen items status then update order status
                $this->db->where('procurement_orders_id', $procurement_orders_id); // Determine overall order status based on item status
                $item_query = $this->db->get('sma_procurement_order_items');

                $complete_order = true;
                foreach ($item_query->result() as $item) {
                    if ($item->item_status != 'Completed' && $item->item_status != 'partially_completed') {
                        $complete_order = false;
                        break; 
                    }
                    if($item->item_status == 'Open'){
                        $open_order = true;
                        break;
                    }
                }
                // Update order status based on item status
                if ($complete_order) {
                    $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                }else {
                    if($item->item_status != 'Open'){
                        $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurement_orders_id));
                    }
                    // $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurement_orders_id));
                }
            }
            return $procurmentDetails;
        }

        // for bulk action update allot quantity and item_status after order locked and click on checkbox
        foreach ($itemData as $item) {
            $itemId = $item['itemId'];
            $quantity = $item['quantity'];
            
            // Update stock quantity for the item in bulk
            $order =  $this->getProcurementdetails($procurmentRefNo = null,$status= null,$itemId);
            foreach($order as $data){
           
                $product_id            = $data->product_id;
                $stock_quantity        = $data->stock_quantity;
                $procurement_orders_id = $data->procurement_orders_id;
            }
            if($checkbox == 0){
                $this->db->where('id', $itemId);
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => '0', 'item_status' => 'Locked')); //update status if uncheck allot qty checkbox(for bulk)
            }else{
                $this->db->where('id', $itemId);
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => $quantity, 'item_status' => 'Committed'));//update status if check allot qty checkbox(for bulk)
            }

        }
        //update planned_delivery_datetime after set delivery time for orders
        if(!empty($datetime)){
            $this->db->update('sma_procurement_orders', array('planned_delivery_datetime' =>  $datetime['planned_delivery_datetime']), array('id' =>  $datetime['id'])); // for orders
            $this->db->update('sma_procurement_order_items', array('planned_delivery_datetime' =>  $datetime['planned_delivery_datetime']), array('procurement_orders_id' =>  $datetime['id'])); // for orders
            
        }     
    }

    #=========================================== Production Manager Dashboard Screen  ============================================
    
    // Production Manager Dashboard
    // public function getproductDetails() {
    //     $this->db->from('products');
    //     return $products = $this->db->get()->result();
    // }
    // public function getproductDetailsByLocation($locationName) {

    //     $this->db->select('products.*, productionunit_products.location_id, warehouses.name as location_name');
    //     $this->db->from('products');
    //     $this->db->join('productionunit_products', 'productionunit_products.product_id = products.id', 'left');
    //     $this->db->join('warehouses', 'warehouses.id = productionunit_products.location_id', 'left');
    //     $this->db->where('warehouses.name', $locationName);
    //     return $products = $this->db->get()->result();
    // }
    /** Manager dashboard stock: variant → wpv sum, else → wp sum (uses getProductStock). */
    public function getStockQtyForLocation($product_id, $warehouse_id, $product_type = null, $products_table_qty = null)
    {
        $stock = $this->getProductStock($product_id, $warehouse_id);
        $qty = $stock ? (float) $stock->stock_quantity : 0;
        if ($qty <= 0 && $product_type === 'Intermediate' && $products_table_qty !== null) {
            $qty = (float) $products_table_qty;
        }
        return $qty;
    }

    public function getProductDetailsByLocation($locationName) {
    
        // Sum order_quantity only for items assigned to THIS production unit (w.id = production_unit_id)
        $this->db->distinct();
        $this->db->select('p.*, w.id as warehouse_id, w.name as location_name, COALESCE(poi.total_order_quantity, 0) as order_quantity');
        $this->db->from('products p');
        $this->db->join('sma_warehouses_products pu', 'pu.product_id = p.id', 'left');
        $this->db->join('warehouses w', 'w.id = pu.warehouse_id', 'left');
        // Restrict to warehouses whose location type is either Workstation or Production Unit
        $this->db->join('location_type lt', 'lt.id = w.location_type', 'left');
        $this->db->join(
            '(SELECT product_id, production_unit_id, SUM(deduped_qty) as total_order_quantity FROM (
                SELECT procurement_orders_id, product_id, production_unit_id, MAX(order_quantity) as deduped_qty
                FROM sma_procurement_order_items
                WHERE item_status IN ("Committed", "Locked")
                GROUP BY procurement_orders_id, product_id, production_unit_id
            ) deduped GROUP BY product_id, production_unit_id) poi',
            'poi.product_id = p.id AND poi.production_unit_id = w.id',
            'left'
        );
       
        $this->db->where('w.name', $locationName);
        // Only include locations tagged as Workstation or Production Unit
        $this->db->where_in('lt.type', array('Workstation', 'Production Unit'));
        $this->db->where_in('p.type', array('standard', 'Intermediate'));
        $this->db->where('p.flag_visible', 1);
        $this->db->group_by('p.id, w.id');
    
        // Order by total_order_quantity in descending order
        $this->db->order_by('COALESCE(poi.total_order_quantity, 0) DESC');

        $products = $this->db->get()->result();
        foreach ($products as $product) {
            $product->stock_quantity = $this->getStockQtyForLocation(
                $product->id,
                isset($product->warehouse_id) ? $product->warehouse_id : null,
                isset($product->type) ? $product->type : null,
                isset($product->quantity) ? $product->quantity : null
            );
        }
        return $products;
    }
    public function getProductWiseOrderdetails($product_id, $production_unit_id = null) {
        // Deduplicate: show only first row per (order, product, production_unit) to avoid double-counting
        // when same product was added twice by mistake. Use aggregated wp for stock.
        $sub_where = "poi2.item_status IN ('Locked','Committed') AND po.status = 'Locked'";
        if ($product_id) {
            $sub_where .= " AND poi2.product_id = " . $this->db->escape($product_id);
        }
        if ($production_unit_id) {
            $sub_where .= " AND poi2.production_unit_id = " . $this->db->escape($production_unit_id);
        }
        $sub_sql = "SELECT MIN(poi2.id) as first_id FROM sma_procurement_order_items poi2
            INNER JOIN sma_procurement_orders po ON po.id = poi2.procurement_orders_id
            WHERE $sub_where
            GROUP BY poi2.procurement_orders_id, poi2.product_id, poi2.production_unit_id";
        
        $this->db->select("
            poi.id as itemId,
            poi.procurement_orders_id,
            poi.product_id,
            poi.product_name,
            poi.order_quantity,
            poi.allot_quantity,
            poi.item_status,
            poi.production_unit_name,
            po.procurement_order_ref_no,
            po.location_name,
            po.order_creation_date,  
            poi.production_unit_id,
            products.type as product_type,
            products.quantity as product_table_qty,
            (
                SELECT COALESCE(SUM(poi2.order_quantity - COALESCE(poi2.allot_quantity, 0)), 0)
                FROM sma_procurement_order_items poi2
                WHERE poi2.product_id = poi.product_id
                  AND poi2.production_unit_id = poi.production_unit_id
                  AND poi2.item_status IN ('Open','Locked','Committed','partially_completed')
            ) AS open_order_quantity,
            po.note
        ", false);
        $this->db->from('procurement_order_items poi');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id', 'left');
        $this->db->join('products ', 'products.id = poi.product_id', 'left');
        $this->db->where("poi.id IN ($sub_sql)", null, false);
        $this->db->where_in('poi.item_status', array('Locked', 'Committed'));
        $this->db->where('po.status', 'Locked');
        
        if ($product_id) {
            $this->db->where('poi.product_id', $product_id);
        }
        if ($production_unit_id) {
            $this->db->where('poi.production_unit_id', $production_unit_id);
        }
        $this->db->order_by("po.id", "desc");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->stock_quantity = $this->getStockQtyForLocation(
                    $row->product_id,
                    $row->production_unit_id,
                    isset($row->product_type) ? $row->product_type : null,
                    isset($row->product_table_qty) ? $row->product_table_qty : null
                );
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductStock($product_id, $warehouse_id = null) {
        $product_id = (int) $product_id;
        $location_id = 0;
        $ids = array();

        if ($warehouse_id !== null && $warehouse_id !== '') {
            $ids = is_array($warehouse_id) ? $warehouse_id : explode(',', $warehouse_id);
            $ids = array_map('trim', $ids);
            $ids = array_filter($ids, 'is_numeric');
            $ids = array_values($ids);
            if (!empty($ids)) {
                $location_id = (int) $ids[0];
            }
        }

        $variants = $this->get_product_variants($product_id);
        $has_variants = !empty($variants);

        // Variant product: stock from warehouses_products_variants
        if ($has_variants) {
            $this->db->select('product_id, ' . $location_id . ' as location_id, CAST(COALESCE(SUM(quantity), 0) AS UNSIGNED) as stock_quantity', false);
            $this->db->from('sma_warehouses_products_variants');
            $this->db->where('product_id', $product_id);
            if (!empty($ids)) {
                $this->db->where_in('warehouse_id', $ids);
            }
            $this->db->group_by('product_id');
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }

        // Non-variant product OR fallback
        $this->db->select('product_id, ' . $location_id . ' as location_id, CAST(COALESCE(SUM(quantity), 0) AS UNSIGNED) as stock_quantity', false);
        $this->db->from('sma_warehouses_products');
        $this->db->where('product_id', $product_id);
        if (!empty($ids)) {
            $this->db->where_in('warehouse_id', $ids);
        }
        $this->db->group_by('product_id');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        // No row in either table: return zero stock object
        $data = new stdClass();
        $data->product_id = $product_id;
        $data->location_id = $location_id;
        $data->stock_quantity = 0;
        return $data;
    }

    public function productExistsInWarehouse($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('sma_warehouses_products', array(
            'product_id'   => $product_id,
            'warehouse_id' => $warehouse_id,
        ), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

     public function getProductStockinProductionDashboard($product_id) {
       
        // Aggregate stock across warehouses for this product
        $q = $this->db->select('CAST(SUM(quantity) AS UNSIGNED) as stock_quantity')
                      ->from('sma_warehouses_products')
                      ->where('product_id', $product_id)
                      ->get()
                      ->row_array();
        if ($q && isset($q['stock_quantity'])) {
            return (float) $q['stock_quantity'];
        }
        return FALSE;
    }
    public function getProductBatches($product_id) {
        $this->db->select('product_batches.*, units.name as unit_name, products.name as product_name');
        
        $this->db->from('product_batches');
        $this->db->join('products', 'product_batches.product_id = products.id', 'left'); 
        
        // Product base unit
        $this->db->join('units', 'products.unit = units.id', 'left');

        $this->db->where('product_batches.product_id', $product_id);

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result(); 
        }

        return FALSE;
    }
    public function getBatchDetailsForYieldCalculation($product_id)
    {
        $sql = "
            SELECT 
                pb.min_batch_qty,
                pb.sales_units,
                bu.name AS batch_unit_name
            FROM sma_produnit_bill_of_materials bom

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

            LEFT JOIN sma_units bu 
                ON pb.batch_uom = bu.id

            WHERE bom.product_id = ?
            AND bom.version_no = (
                SELECT MAX(CAST(version_no AS UNSIGNED))
                FROM sma_produnit_bill_of_materials
                WHERE product_id = ? AND is_active = 1
            )
            AND bom.is_active = 1

            LIMIT 1
        ";

        $query = $this->db->query($sql, [
            (int)$product_id,
            (int)$product_id
        ]);

        if ($query->num_rows() > 0) {
            return $query->row(); // returns single object
        }

        return false;
    }
    public function getLatestProductBatches($product_id) {

        $this->db->select('product_batches.*, units.name as unit_name'); 
        $this->db->from('product_batches');
        $this->db->join('products', 'product_batches.product_id = products.id', 'left'); 
        $this->db->join('units', 'products.unit = units.id', 'left');
        $this->db->where('product_batches.product_id', $product_id);
        $this->db->order_by('product_batches.created_at', 'desc'); 
        $this->db->limit(5); 
    
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result(); 
        }
        return FALSE;
    }
    //Insert batches
    public function addProductBatches($data){

        $product_id = $data['product_id'];
        $quantity   = $data['quantity'];

        if ($this->db->insert('product_batches', $data)) {
            return true ;
        }
        return false;
    }

      public function addProductBatchesinProductionDashboard($data){

        $product_id = $data['product_id'];
        $quantity   = $data['quantity'];
         
        if ($this->db->insert('product_batches', $data)) {
            return true ;
        }
        return false;
    }

    // get last batch number for create next batch number(automatically)
    public function getLastBatchNumber($product_id) {
        
        $this->db->select('batch_no');
        $this->db->from('product_batches'); 
        $this->db->where('product_id', $product_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }

    public function resetProductStock($product_id, $warehouse_id = null){
        // Determine existing warehouse quantity for this product (if warehouse provided)
        $existing_qty = 0;
        if ($warehouse_id) {

            $warehouse_data        = $this->site->getWarehouseBy_ID($warehouse_id); //get warehouse data
            $location_id           = $this->Production_Unit_Model_New->get_location_type_name($warehouse_data); // check warehouse is workstation or not
            if($location_id){ // if warehouse is worktation then fetch users primary location for update qty
                $user_id       = $this->session->userdata('user_id');
                $user_data     = $this->site->getUser($user_id);
                $user_warehouses_raw = (string) $user_data->warehouse_id;
                $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
                $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
                $warehouse_id   = $primary_location_id;
            }
            $Warehouse_products = $this->site->getWarehouseProductQuantity($warehouse_id, $product_id);
            if ($Warehouse_products) {
                $existing_qty = (float) $Warehouse_products->quantity;
            }

            // Set warehouse product quantity to 0 for this warehouse
            $this->db->where('warehouse_id', $warehouse_id);
            $this->db->where('product_id', $product_id);
            $this->db->update('sma_warehouses_products', array('quantity' => 0));

            // Set purchase_items quantity and balance to 0 for this product in this warehouse
            $this->db->where('warehouse_id', $warehouse_id);
            $this->db->where('product_id', $product_id);
            $this->db->update('purchase_items', array('quantity' => 0, 'quantity_balance' => 0));

            // Subtract the previous warehouse quantity from master product quantity
            if ($existing_qty > 0) {
                $this->db->set('quantity', "(quantity - " . (float)$existing_qty . ")", FALSE);
                $this->db->where('id', $product_id);
                $this->db->update('sma_products');
            }
        }

        return true;
    }
    public function getOrderRefrenceNoById($order_id){
        $this->db->select('*');
        $this->db->from('procurement_orders');
        $this->db->where('id', $order_id);
        $this->db->order_by('id', 'DESC'); 
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }

     public function getWarehousesWithProductsData(){        
        $query = $this->db->query("SELECT id, name FROM sma_warehouses")->result_array();
        $products_query = $this->db->query("SELECT id, name FROM sma_products")->result_array();        
        
        $productsDetailsQuery = $this->db->query("
                        SELECT 
                            p.id AS product_id,
                            p.name,
                            wp.warehouse_id as location_id,
                            wp.quantity as stock_quantity,
                            IFNULL(po.total_order_quantity, 0) AS total_order_quantity
                        FROM 
                            sma_products p
                        LEFT JOIN 
                            sma_warehouses_products wp ON p.id = wp.product_id
                        LEFT JOIN 
                            (SELECT product_id, SUM(order_quantity) AS total_order_quantity
                            FROM sma_procurement_order_items
                            GROUP BY product_id) po 
                            ON p.id = po.product_id
                ")->result_array();            
                
        $completeData =  array('warehouses' => $query, 'productsDetails' => $productsDetailsQuery);                
        return $completeData;
    }
    
    #=========================================== End Production Manager Dashboard Screen  ============================================
    #========================================================Ordering History  ============================================
    
    public function get_attachment($OrderRefrenceNo) {
        $this->db->select('attachment');
        $this->db->from('orderdispatchdetails');
        $this->db->where('procurement_order_ref_no', $OrderRefrenceNo);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               
                $data = $row->attachment;
            }
            return $data;
        }
        return FALSE;
    } 

    public function get_products($searchTerm) {

        $this->db->like('products.name', $searchTerm,'both');
        $this->db->or_like('products.code', $searchTerm, 'both');
        $this->db->select('products.name as name, products.id')
                ->from('products')
                ->group_by('products.id');

        $query = $this->db->get();
        $results = array();
        
        foreach ($query->result() as $row) {
            // Get non-color variants for this product
            $variants = $this->get_product_variants($row->id);
            $variant_names = array();
            
            foreach ($variants as $variant) {
                $variant_names[] = $variant->name;
            }
            
            // Format: "Product Name (Variant1, Variant2, Variant3)"
            $row->varient_name = !empty($variant_names) ? implode(', ', $variant_names) : '';
            $results[] = $row;
        }

        return $results;
    }

    public function get_product_variants($product_id) {
        $this->db->where('product_id', $product_id);
        $this->db->where('group_id !=', 2); // Exclude Color variants (group_id = 2)
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('product_variants');
        
        return $query->result();
    }
    public function getProductIdByName($name) {
        $this->db->where('name', $name);
        $query = $this->db->get('products');
        $result = $query->row();
        return $result;

    }
    public function add_product_to_productionunit($data) {
        // Map products to production units using warehouses_products (warehouse_id instead of location_id)
        $mapped = [];
        foreach ($data as $row) {
            if (isset($row['product_id'], $row['location_id'])) {
                $mapped[] = [
                    'product_id'   => $row['product_id'],
                    'warehouse_id' => $row['location_id'],
                    'quantity'     => isset($row['stock_quantity']) ? $row['stock_quantity'] : 0,
                ];
            }
        }
        if (!empty($mapped)) {
            return $this->db->insert_batch('sma_warehouses_products', $mapped);
        }
        return false;
    }  

    public function CourierDetails($data, $procurement_order_id) {

        // return $this->db->insert('sma_orderdispatchdetails', $data);

        $result = $this->db->insert('sma_orderdispatchdetails', $data);
        if ($result) {
            $this->db->where('id', $procurement_order_id);
            $this->db->update('sma_procurement_orders', ['status' => 'Dispatched']);
            
            $this->db->where('procurement_orders_id', $procurement_order_id);
            $this->db->update('sma_procurement_order_items', ['item_status' => 'Dispatched']); 
    
            return $this->db->affected_rows();
        }
        return false; 
    }
    public function getProcurementOrderData($procurement_order_ref_no) {
        $q = $this->db->get_where('procurement_orders', array('procurement_order_ref_no' => $procurement_order_ref_no), 1);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getExistingProducts() {
        // Existing production-unit mapped products now come from warehouses_products (workstation warehouses)
        $sql = "SELECT DISTINCT wp.*
                FROM " . $this->db->dbprefix('warehouses_products') . " wp
                JOIN " . $this->db->dbprefix('warehouses') . " w ON w.id = wp.warehouse_id
                JOIN " . $this->db->dbprefix('location_type') . " lt ON lt.id = w.location_type
                WHERE lt.type = 'Workstation'";
        return $this->db->query($sql)->result();
    }
        
    ///////////////////////////// GET ALL KOT /////////////////////////////////////////////////////
    public function all_kot($warehouse_id)
    {
        $query = "SELECT
            p.name AS Product,
            COALESCE(poi.total_order_quantity, 0) AS `Order Quantity`,
            COALESCE(pup.quantity, 0) AS `Stock Quantity`,
            CASE
                WHEN COALESCE(pup.quantity, 0) >= COALESCE(poi.total_order_quantity, 0) THEN 0
                WHEN COALESCE(pup.quantity, 0) = 0 THEN COALESCE(poi.total_order_quantity, 0)
                WHEN COALESCE(pup.quantity, 0) < COALESCE(poi.total_order_quantity, 0) THEN COALESCE(poi.total_order_quantity, 0) - COALESCE(pup.quantity, 0)
                ELSE 0
            END AS `Built Quantity`,
            u.code AS Unit,
            COALESCE(GROUP_CONCAT(DISTINCT po_locations.location_name ORDER BY po_locations.location_name SEPARATOR ', '), '') AS `Outlets Requesting`,
            '' AS `Packing Instructions`,
            '' AS `Special Instruction`
        FROM
            sma_products p
        LEFT JOIN
            sma_warehouses_products pup
            ON pup.product_id = p.id AND pup.warehouse_id IN ({$warehouse_id})
        LEFT JOIN (
            SELECT
                product_id,
                SUM(order_quantity) AS total_order_quantity
            FROM
                sma_procurement_order_items
            WHERE
                item_status IN ('Committed', 'Locked')
            GROUP BY
                product_id
        ) AS poi ON poi.product_id = p.id
        LEFT JOIN
            sma_units u ON u.id = p.unit
        LEFT JOIN (
            SELECT
                sps.product_id,
                spo.location_name
            FROM
                sma_procurement_orders spo
            JOIN
                sma_procurement_order_items sps ON spo.id = sps.procurement_orders_id
            WHERE
                spo.status = 'Locked'
                AND spo.location_id IN ({$warehouse_id})
            GROUP BY
                sps.product_id, spo.location_name
        ) AS po_locations ON po_locations.product_id = p.id
        WHERE
            pup.product_id IS NOT NULL
        GROUP BY
            p.id, p.name, p.code, pup.stock_quantity, poi.total_order_quantity, u.code
        ORDER BY
            COALESCE(poi.total_order_quantity, 0) DESC, p.name ASC;";
        $result = $this->db->query($query);
        return $result ? $result->result_array() : array();
    }

    public function kot_by_order($orderId, $toggle)
    {
        if($toggle)
        {
            $query = "SELECT 
            p.name AS Product,
            poi.order_quantity AS `Order Quantity`,
            CASE
                WHEN pup.quantity > poi.order_quantity THEN 0
                WHEN pup.quantity = 0 THEN poi.order_quantity
                WHEN pup.quantity < poi.order_quantity THEN poi.order_quantity - pup.quantity
                ELSE 0
            END AS `Built Quantity`,
            pup.quantity AS `Stock Quantity`,
            u.code AS Unit,
            po.location_name AS `Outlets Requesting`,
            po.procurement_order_ref_no AS `procurement_order_ref_no`,
            po.note AS `Packing Instructions`,
            po.note AS `Special Instruction`
            FROM sma_procurement_orders po
            JOIN sma_procurement_order_items poi ON po.id = poi.procurement_orders_id
            JOIN sma_products p ON p.id = poi.product_id
            JOIN sma_units u ON u.id = p.unit
            JOIN sma_warehouses_products pup 
            ON pup.product_id = poi.product_id
            WHERE po.id = ?
            ORDER BY p.name;";
            $result = $this->db->query($query, [$orderId]);
        }
        else{
            $warehouse_id = $this->session->userdata('warehouse_id');
                $query = "SELECT 
                    p.name AS Product,
                    poi.order_quantity AS `Order Quantity`,
                    CASE
                        WHEN pup.quantity > poi.order_quantity THEN 0
                        WHEN pup.quantity = 0 THEN poi.order_quantity
                        WHEN pup.quantity < poi.order_quantity THEN poi.order_quantity - pup.quantity
                        ELSE 0
                    END AS `Built Quantity`,
                    pup.quantity AS `Stock Quantity`,
                    u.code AS Unit,
                    po.location_name AS `Outlets Requesting`,
                    po.procurement_order_ref_no AS `procurement_order_ref_no`,
                    po.note AS `Packing Instructions`,
                    po.note AS `Special Instruction`
                FROM sma_procurement_orders po
                JOIN sma_procurement_order_items poi ON po.id = poi.procurement_orders_id
                JOIN sma_products p ON p.id = poi.product_id
                JOIN sma_units u ON u.id = p.unit
                JOIN sma_warehouses_products pup ON pup.product_id = poi.product_id AND pup.warehouse_id = ?
                WHERE po.id = ?
                ORDER BY p.name";
            
                $result = $this->db->query($query, [$warehouse_id, $orderId]);
        }
        return $result ? $result->result_array() : array();
    }

    public function get_warehouse_name($warehouse_id)
    {
        $this->db->select('name');
        $this->db->from('sma_warehouses');
        $this->db->where('id', $warehouse_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->name;
        }
        return 'Unknown';
    }
    ///////////////////////////// update transfer after received order from outlet/////////////////////////////////////////
    public function get_transfer($procurement_order_ref_no) {
        $query = $this->db->get_where('sma_transfers', array('procurement_order_ref_no' => $procurement_order_ref_no));
        return $query->row(); 
    }
    public function get_transfer_items($transfer_id) {
        $q = $this->db->get_where('sma_transfer_items', array('transfer_id' => $transfer_id));
        if ($q->num_rows() > 0) {
            return $q->result(); 
        }
        return false; 
    }
    public function updateTransfer($id, $data , $items) {
        //  error_reporting(E_ALL);
        // ini_set('display_errors', 1);
        $status = $data['status'];
        if ($this->db->update('transfers', $data, ['id' => $id])) {            
            $this->db->delete('transfer_items', ['transfer_id' => $id]);
             
            foreach ($items as $item) {
                $item = (array) $item;
                $item['transfer_id'] = $id;
                               
                if ($status !== 'completed') {
                    $titem = $item;
                    $titem['item_status'] = $status;
                    unset($titem['hsn_code']);
                    $this->db->insert('transfer_items', $titem);
                }
                
                if($status == 'sent'){
                    $qty = $item['unit_quantity'];
                                           
                    $pclause = ['product_id' => $item['product_id'], 'warehouse_id' => $data['from_warehouse_id'], 'option_id' => $item['option_id'], 'quantity_balance >' => 0 ];
                    $piw1 = $this->getPurchasedItems($pclause);
                    if($piw1) { 
                        foreach ($piw1 as $key => $pi) {
                            
                            if($pi->quantity_balance < $qty) {
                                $quantity_balance = 0;
                                $qty = $qty - $pi->quantity_balance;
                            } else {
                                $quantity_balance = $pi->quantity_balance - $qty;
                                $qty = 0;
                            }   

                            if($this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id])){
                                
                                $tclause = ['transfer_id'=> $item['transfer_id'], 'product_id' => $item['product_id'], 'warehouse_id' => $data['to_warehouse_id'], 'option_id' => $item['option_id'], 'status'=>'!received' ];
                                $tiw2   = $this->getPurchasedItems($tclause);
                                
                                if($tiw2){ 
                                    $trpi = (array)$tiw2[0];                                   
                                    
                                } else {                    
                                   // $trpi = (array)$pi;                               
                                                                   
                                    $trpi['transfer_id']      = $item['transfer_id'];
                                    $trpi['batch_number']     = !empty($item['batch_number']) ? $item['batch_number'] : NULL;
                                    $trpi['product_id']       = $pi->product_id;
                                    $trpi['product_code']     = $pi->product_code;
                                    $trpi['product_name']     = $pi->product_name;
                                    $trpi['option_id']        = $pi->option_id;
                                    $trpi['quantity']         = $item['quantity'];
                                    $trpi['unit_quantity']    = $item['unit_quantity'];
                                    $trpi['quantity_balance']     = 0;
                                    $trpi['quantity_received']    = 0;                                    
                                    $trpi['status']           = 'pending';
                                    $trpi['warehouse_id']     = $data['to_warehouse_id'];
                                    $trpi['date']             = date('Y-m-d');
                                    $trpi['unit_cost']        = $item['unit_cost'];
                                    $trpi['real_unit_cost']   = $item['real_unit_cost'];
                                    $trpi['net_unit_cost']    = $item['net_unit_cost'];
                                    $trpi['tax_rate_id']      = $item['tax_rate_id'];
                                    $trpi['tax']              = $item['tax'];
                                    $trpi['item_tax']         = $item['item_tax'];
                                    $trpi['subtotal']         = $item['subtotal'];
                                    $trpi['expiry']           = $item['expiry'];
                                    $trpi['hsn_code']         = $item['hsn_code'];
                                    
                                    if($item['item_tax']) {
                                        $gst_rate = substr($item['tax'], 0, 4 );
                                        $gst = (float)$item['item_tax'] / 2;
                                        
                                        $trpi['gst_rate'] = ((float)$gst_rate / 2);
                                        $trpi['cgst'] = $gst;
                                        $trpi['sgst'] = $gst;
                                        $trpi['igst'] = 0;
                                        
                                        //Set IGST Conditions
                                        if($data['from_warehouse_state_code'] != '' && $data['to_warehouse_state_code']!=''){                                        
                                            if($data['from_warehouse_state_code'] != $data['to_warehouse_state_code']){

                                                $trpi['gst_rate'] = $gst_rate;
                                                $trpi['cgst'] = 0;
                                                $trpi['sgst'] = 0;
                                                $trpi['igst'] = $item['item_tax'];
                                            }                                        
                                        } 
                                    }
                                    
                                    $this->db->insert('purchase_items' , $trpi);
                                }
                            } 
                            if($qty == 0) { break; }    
                             
                        }//end foreach
                        
                    }                  
                
                   $this->site->syncProductQty($item['product_id'], $data['from_warehouse_id']);
                   if($item['option_id']){
                        $this->site->syncVariantQty($item['option_id'], $data['from_warehouse_id'], $item['product_id']);
                   }
                }//End status == sent                
                elseif($status == 'completed'){
                    
                    $clause2 = ['transfer_id' => $id ,'product_id' => $item['product_id'], 'warehouse_id' => $data['to_warehouse_id'], 'option_id' => $item['option_id'], 'batch_number' => $item['batch_number'], 'status' => '!received' ];
                    $piw2 = $this->getPurchasedItems($clause2);
                    
                    if($piw2) {
                        
                        $quantity_balance   = $piw2->quantity_balance + $item['unit_quantity'];
                        $quantity           = $item['request_quantity'];
                        $quantity_received  = $piw2->quantity_received + $item['unit_quantity'];
                        $status = $quantity == $quantity_received ? 'received' : 'partial';                        
                        $update = [
                                'quantity'          => $quantity,
                                'quantity_balance'  => $quantity_balance,
                                'quantity_received' => $quantity_received, 
                                'status'            => $status, 
                            ];
                        
                        $this->db->update('purchase_items', $update, ['id' => $piw2->id]);
                    } else {
                        $trdata['transfer_id']      = $item['transfer_id'];
                        $trdata['product_id']       = $item['product_id'];
                        $trdata['product_code']     = $item['product_code'];
                        $trdata['product_name']     = $item['product_name'];
                        $trdata['option_id']        = ($item['option_id'] ? $item['option_id'] : 0);
                        $trdata['batch_number']     = (!empty($item['batch_number']) ? $item['batch_number'] : NULL);
                        $trdata['warehouse_id']     = $data['to_warehouse_id'];
                        $trdata['net_unit_cost']    = $item['net_unit_cost'];
                        $trdata['unit_cost']        = $item['unit_cost'];
                        $trdata['real_unit_cost']   = $item['real_unit_cost'];
                        $trdata['product_unit_id']  = $item['product_unit_id'];
                        $trdata['product_unit_code']= $item['product_unit_code'];                        
                        $trdata['item_tax']         = $item['item_tax'];
                        $trdata['tax_rate_id']      = $item['tax_rate_id'];
                        $trdata['tax']              = $item['tax'];
                        $trdata['subtotal']         = $item['subtotal'];
                        $trdata['unit_quantity']    = $item['unit_quantity'];                        
                        $trdata['quantity']         = $item['quantity'];                         
                        $trdata['quantity_balance'] = $item['quantity'];
                        $trdata['quantity_received']= $item['quantity'];
                        $trdata['status']           = 'received';
                        $trdata['date']             = date('Y-m-d');
                        $trdata['hsn_code']         =  $item['hsn_code'];
                        
                        if($item['item_tax']) {
                            $gst_rate = substr($item['tax'], 0, 4 );
                            $gst = (float)$item['item_tax'] / 2;

                            $trdata['gst_rate'] = ((float)$gst_rate / 2);
                            $trdata['cgst'] = $gst;
                            $trdata['sgst'] = $gst;
                            $trdata['igst'] = 0;
                            //Set IGST Conditions
                            if($data['from_warehouse_state_code']!='' && $data['to_warehouse_state_code']!=''){                                        
                                if($data['from_warehouse_state_code'] != $data['to_warehouse_state_code']){

                                    $trdata['gst_rate'] = $gst_rate;
                                    $trdata['cgst'] = 0;
                                    $trdata['sgst'] = 0;
                                    $trdata['igst'] = $item['item_tax'];
                                }                                        
                            }
                        }
                        
                        $this->db->insert('purchase_items', $trdata);                        
                    }
                    
                    $this->site->syncProductQty($item['product_id'], $data['to_warehouse_id']);
                    if($item['option_id']){
                        $this->site->syncVariantQty($item['option_id'], $data['to_warehouse_id'], $item['product_id']);
                    }
                } //End Status == complited                  
            }
           
            $this->db->update('transfers', ['status' => $data['status']], array('id' => $id));
            return true;
        }
        return false;
    }
    public function getPurchasedItems($where_clause) {

        $product_storage_type = $where_clause['product_id'] ? $this->site->getProductStorageType($where_clause['product_id']) : 'packed';

        $orderby = ($this->Settings->accounting_method == 1) ? 'desc' : 'asc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $this->db->order_by('quantity_balance', 'DESC');

        if ($where_clause['option_id'] && $product_storage_type == 'packed') {
            $this->db->where('option_id', $where_clause['option_id']);
        }
        unset($where_clause['option_id']);

        if ($this->Settings->product_batch_setting > 0 && $where_clause['batch_number']) {
            $this->db->where('batch_number', $where_clause['batch_number']);
        }
        unset($where_clause['batch_number']);

        if ($where_clause['status']) {
            if($where_clause['status'] == '!received'){
                $this->db->where('status !=', 'received');
            } else {
                $this->db->where('status', $where_clause['status']);
            }
            unset($where_clause['status']);
        } else {
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        }
        $this->db->where($where_clause);

        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
           foreach (($q->result()) as $row) {
                
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    ///////////////////////////// update transfer after received order from outlet END/////////////////////////////////////////
    
    // while mapping products : add data in  sma_warehouses_products table
    public function add_product_to_warehouse($war_data) {
        return $this->db->insert_batch('sma_warehouses_products', $war_data);
    }
    // update quantity againt warehouse in warehouse product table
    public function updateWarehouseProductQty($data){

        $product_id     = $data['product_id'];
        $quantity       = $data['quantity'];
        $warehouse_id   = $data['location_id'];
        // Use global helper to get last batch number (and id) for this product
        $lastBatch = $this->site->getLastBatchNumber($product_id);
        $batch_id = $lastBatch ? $lastBatch->id : null;

        // If there is an existing row for this product+warehouse with qty = 0 and no batch_id,
        // and that warehouse is of type Workstation or Production Unit, reuse that row
        // instead of inserting a new one.
        $existing = $this->db->select('wp.id')
            ->from('sma_warehouses_products wp')
            ->join('sma_warehouses w', 'w.id = wp.warehouse_id', 'left')
            ->join('sma_location_type lt', 'lt.id = w.location_type', 'left')
            ->where('wp.product_id', $product_id)
            ->where('wp.warehouse_id', $warehouse_id)
            ->where('wp.quantity', 0)
            ->where('wp.batch_id IS NULL', null, false)
            ->where_in('lt.type', array('Workstation', 'Production Unit'))
            ->limit(1)
            ->get()
            ->row();

        $insert_data = array(
            'product_id'   => $product_id,
            'warehouse_id' => $warehouse_id,
            'quantity'     => $quantity,
        );
        if ($batch_id) {
            $insert_data['batch_id'] = $batch_id;
        }
        if ($existing) {
            // Update the placeholder row
            $this->db->update('sma_warehouses_products', $insert_data, array('id' => $existing->id));
        } else {
            // No placeholder row -> insert a new record
            $this->db->insert('sma_warehouses_products', $insert_data);
        }
        return true;
    }

    public function getRawMaterials($product_id, $location_id) {

        // COALESCE(batches.expiry_date, pur_items.expiry) AS expiry_date,
        // COALESCE(batches.batch_no, pur_items.batch_number) AS batch_no
        $sql = "
            SELECT DISTINCT
                bom.version_no,
                bom.notes,
                bom_items.bom_id,
                bom_items.material_id,
                bom_items.bom_ref,
                bom_items.quantity_required,
                p.name AS raw_material,
                bom.product_id AS product_id,
                u.name AS unit_name,
                pb.is_batch_only,
                pb.min_batch_qty,
                bu.name AS batch_unit_name,
                bom_items.is_alternative,
                bom_items.primary_product_id,
                pb.sales_units,
                pi.expiry AS expiry_date,
                pi.batch_number AS batch_no,
                CASE 
                    WHEN IFNULL(pi.batch_available_qty, 0) > 0 THEN pi.batch_available_qty
                    ELSE 0
                END AS available_qty
            FROM sma_produnit_bill_of_material_items AS bom_items
            JOIN sma_produnit_bill_of_materials AS bom 
                ON bom_items.bom_id = bom.id
            LEFT JOIN sma_products p 
                ON bom_items.material_id = p.id
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
            LEFT JOIN sma_units u 
                ON bom_items.uom = u.id
            LEFT JOIN sma_units bu 
                ON pb.batch_uom = bu.id
            LEFT JOIN (
                SELECT 
                    pi.product_id,
                    pi.warehouse_id,
                    pi.batch_number,
                    pi.expiry,
                    SUM(pi.quantity_balance) AS batch_available_qty
                FROM sma_purchase_items pi
                WHERE pi.warehouse_id = ?
                GROUP BY 
                    pi.product_id,
                    pi.warehouse_id,
                    pi.batch_number,
                    pi.expiry
            ) AS pi 
                ON pi.product_id = bom_items.material_id
                AND pi.warehouse_id = ?
            WHERE bom.product_id = ?
            AND bom.version_no = (
                SELECT MAX(CAST(version_no AS UNSIGNED))
                FROM sma_produnit_bill_of_materials
                WHERE product_id = ? AND is_active = 1
            )
            AND bom.is_active = 1
            ORDER BY 
            COALESCE(bom_items.primary_product_id, bom_items.material_id),
            CASE 
                WHEN bom_items.is_alternative = 1 THEN 1 
                ELSE 0 
            END,
            bom_items.material_id,
            pi.batch_number,
            pi.expiry;
        ";
    
        $query = $this->db->query($sql, [
            (int)$location_id,
            (int)$location_id,
            (int)$product_id,
            (int)$product_id
        ]);
        // return $query->num_rows() > 0 ? $query->result() : false;
        $result = $query->result();

        /* ==============================
        FILTER LOGIC 
        ============================== */

        // Group rows by material_id
        $grouped = [];
        foreach ($result as $row) {
            $grouped[$row->material_id][] = $row;
        }

        $filteredResult = [];

        foreach ($grouped as $material_id => $rows) {

            // Get rows where available_qty > 0
            $positiveQtyRows = array_filter($rows, function ($r) {
                return floatval($r->available_qty) > 0;
            });

            if (!empty($positiveQtyRows)) {
                // If any positive qty exists → keep only those
                $filteredResult = array_merge($filteredResult, $positiveQtyRows);
            } else {
                // If all qty <= 0 → keep only ONE record (first one)
                $filteredResult[] = $rows[0];
            }
        }

        // Re-index array
        $filteredResult = array_values($filteredResult);

        return !empty($filteredResult) ? $filteredResult : false;

    }

    public function getRawMaterialForProduct($product_id, $material_id)
    {
        return $this->db
            ->select('b.version_no, i.*, p.name AS material_name')
            ->from('sma_produnit_bill_of_material_items i')
            ->join('sma_produnit_bill_of_materials b', 'b.id = i.bom_id')
            ->join('sma_products p', 'p.id = i.material_id', 'left')
            ->where('i.material_id', $material_id)
            ->where('b.product_id', $product_id)
            ->where('b.is_active', 1)
            ->order_by('LENGTH(b.version_no)', 'DESC', false)
            ->order_by('b.version_no', 'DESC', false)
            ->limit(1)
            ->get()
            ->row();
    }
    
    // update raw material stock quantity
    // public function updateRawMaterialQty($raw_material_data) {
    //         echo "<pre>";
    //         print_r($raw_material_data);
    //     if (!empty($raw_material_data)) {

    //         $product_id = $raw_material_data['product_id'];
    //         $location_id = $raw_material_data['location_id'];
    //         $raw_material_quantity   = $raw_material_data['quantity'];

    //         $where_clause = array('product_id' => $raw_material_data['product_id'], 'warehouse_id' => $raw_material_data['location_id']);

    //         // $purchase_items = $this->site->getPurchasedItems($product_id, $location_id);
    //         $purchase_item = $this->site->getPurchasedItem($where_clause);
    //         $Warehouse_products = $this->site->getWarehouseProductQuantity($location_id, $product_id); // get product stock details by product id

    //         // update qty in purchase_items table
    //         if ($purchase_item) {
    //             $quantity_balance =  $purchase_item->quantity_balance - $raw_material_quantity;
    //             $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
    //         } else {

    //             $pr = $this->site->getProductByID($product_id);
    //             $item = array(
    //                 'product_id' => $product_id,
    //                 'product_code' => $pr->code,
    //                 'product_name' => $pr->name,
    //                 'net_unit_cost' => $pr->cost,
    //                 'unit_cost' => $pr->cost,
    //                 'real_unit_cost' => $pr->cost,
    //                 'quantity' => $raw_material_quantity,
    //                 'option_id' => 0,
    //                 'quantity_balance' => $raw_material_quantity,
    //                 'item_tax' => 0,
    //                 'tax_rate_id' => 1,
    //                 'tax' => 0,
    //                 'tax_method' => $pr->tax_method,
    //                 'subtotal' => ($pr->cost * $raw_material_quantity),
    //                 'warehouse_id' => $location_id,
    //                 'date' => date('Y-m-d'),
    //                 'status' => 'received',
    //                 'expiry' =>  null,
    //                 'batch_number' => null,
    //                 'product_unit_id' => $pr->purchase_unit,
    //                 'hsn_code' => $pr->hsn_code,
    //                 'unit_quantity' => $raw_material_quantity ? $raw_material_quantity : 1
    //             );
    //             $this->db->insert('purchase_items', $item);
    //         }
    //         // update qty in sma_warehouses_products table
    //         if($Warehouse_products){
    //             $existing_stock   = $Warehouse_products->quantity;
    //             $new_stock        = $existing_stock - $raw_material_quantity; 
            
    //             $this->db->where('product_id', $product_id);
    //             $this->db->where('warehouse_id', $location_id);
    //             $this->db->update('sma_warehouses_products', array('quantity' => $new_stock)); 
    //         }
    //         $this->site->syncProductQtyForPU($product_id,  $location_id);  // update qty of raw material in products table
    //     }
    // }
    public function updateRawMaterialQty($raw_material_data) {
        if (!empty($raw_material_data)) {
    
            // Handle composite product_id (e.g. "1924|B2|" or "1919||")
            $composite   = explode('|', $raw_material_data['product_id']);
            $product_id = isset($composite[0]) ? (int)$composite[0] : (int)$raw_material_data['product_id'];
            $batch_no   = $raw_material_data['batch_no'] ? $raw_material_data['batch_no'] : null;
            $expiry     = $raw_material_data['expiry'] ? $raw_material_data['expiry'] : null;
            $location_id = $raw_material_data['location_id'];
            $raw_material_quantity = $raw_material_data['quantity'];
    
            // --- Fetch existing purchase item ---
            $this->db->where('product_id', $product_id);
            $this->db->where('warehouse_id', $location_id);
    
            if ($batch_no) {
                $this->db->where('batch_number', $batch_no);
            } else {
                // Handle NULL, empty string, or no batch
                $this->db->where("(batch_number IS NULL OR batch_number = '')", NULL, FALSE);
            }
    
            if ($expiry) {
                $this->db->where('expiry', $expiry);
            } else {
                // Handle NULL, empty string, or legacy '0000-00-00'
                $this->db->where("(expiry IS NULL OR expiry = '' OR expiry = '0000-00-00')", NULL, FALSE);
            }                                  
    
            $purchase_item = $this->db->get('purchase_items')->row();
    
            // --- Warehouse stock record ---
            $batch_id = null;
            if ($batch_no) {
                $batch_row = $this->db->get_where('sma_product_batches', ['product_id' => $product_id, 'batch_no' => $batch_no], 1)->row();
                if ($batch_row) {
                    $batch_id = $batch_row->id;
                }
            }

            if ($batch_id) {
                $Warehouse_products = $this->db->get_where('sma_warehouses_products', [
                    'product_id'   => $product_id,
                    'warehouse_id' => $location_id,
                    'batch_id'     => $batch_id
                ], 1)->row();
            } else {
                $Warehouse_products = $this->db->get_where('sma_warehouses_products', [
                    'product_id'   => $product_id,
                    'warehouse_id' => $location_id,
                    'batch_id IS NULL' => null
                ], 1)->row();
            }

            if (!$Warehouse_products) {
                $Warehouse_products = $this->site->getWarehouseProductQuantity($location_id, $product_id);
            }
    
            // --- Update purchase_items table ---
            if ($purchase_item) {
                $quantity_balance = $purchase_item->quantity_balance - $raw_material_quantity;
                $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $purchase_item->id]);
            } else {
                // Try permission-aware lookup first
                $pr = $this->site->getProductByID($product_id);

                // Fallback if lookup failed or returned an empty row (MySQL aggregate behavior without GROUP BY)
                if (!$pr || empty($pr->id)) {
                    $pr = $this->db->get_where('products', ['id' => $product_id], 1)->row();
                }

                if ($pr && !empty($pr->id)) {
                    $item = [
                        'product_id'       => $product_id,
                        'product_code'     => $pr->code,
                        'product_name'     => $pr->name,
                        'net_unit_cost'    => $pr->cost,
                        'unit_cost'        => $pr->cost,
                        'real_unit_cost'   => $pr->cost,
                        'quantity'         => $raw_material_quantity,
                        'option_id'        => 0,
                        'quantity_balance' => $raw_material_quantity,
                        'item_tax'         => 0,
                        'tax_rate_id'      => 1,
                        'tax'              => 0,
                        'tax_method'       => $pr->tax_method,
                        'subtotal'         => ($pr->cost * $raw_material_quantity),
                        'warehouse_id'     => $location_id,
                        'date'             => date('Y-m-d'),
                        'status'           => 'received',
                        'expiry'           => $expiry,
                        'batch_number'     => $batch_no,
                        'product_unit_id'  => $pr->purchase_unit,
                        'hsn_code'         => $pr->hsn_code,
                        'unit_quantity'    => $raw_material_quantity ?: 1
                    ];
                    $this->db->insert('purchase_items', $item);
                }
            }
    
            // --- Update warehouse quantity ---
            if ($Warehouse_products) {
                $existing_stock = $Warehouse_products->quantity;
                $new_stock = $existing_stock - $raw_material_quantity;
    
                $this->db->where('id', $Warehouse_products->id);
                $this->db->update('sma_warehouses_products', ['quantity' => $new_stock]);
            }
    
            // --- Sync master product quantity ---
            $this->site->syncProductQtyForPU($product_id, $location_id);
        }
    }
    
    
    // Create sales when we dispatch order from production unit
    public function addSale($data = [], $items = [], $payments = [], $si_return = [], $extrasPara = []) {
        
        $this->load->model('orders_model');
        if($data['sale_status'] !='returned'){
           $cost = $this->site->costing($items);       
         }  
        $sale_action = $extrasPara['sale_action'] ? $extrasPara['sale_action'] : null;
        $order_id = $extrasPara['order_id'] ? $extrasPara['order_id'] : null;
        $syncQuantity = $extrasPara['sale_action'];
        if ($sale_action == 'chalan') {
            $sma_sales = 'orders';
            $sma_sales_items = 'order_items';
            $sma_sales_items_tax = 'orders_items_tax';
            $saleRefKey = 'ordr';
            $ReturnSaleRefKey = 're_ordr';
            $data['sale_as_chalan'] = 1;
        } else {
            $sma_sales = 'sales';
            $sma_sales_items = 'sale_items';
            $sma_sales_items_tax = 'sales_items_tax';
            $saleRefKey = 'so';
            $ReturnSaleRefKey = 're';
        }

        if ($this->db->insert($sma_sales, $data)) {

            $sale_id = $this->db->insert_id();
            $todaydate = $data['date']?date('Y-m-d',strtotime($data['date'])) :date('Y-m-d');
            //Get formated Invoice No
            // $invoice_no = $this->sma->invoice_format($sale_id,date());   
            ////////////////////////////// Multiple Biller Invoice No //////////////////////////////
            $biller_invoice_prefix = $this->site->getPrefixByBillerFromLocation($data['warehouse_id'],$data['biller_id']);
            if ($biller_invoice_prefix) {
                $invoice_no = ($sale_action == 'chalan') ? $sale_id : $this->sma->biller_invoice_format($sale_id, $todaydate,$biller_invoice_prefix);
                $invoice_no = $biller_invoice_prefix .'/'. $invoice_no;
            }else{
                $invoice_no = ($sale_action == 'chalan') ? $sale_id : $this->sma->invoice_format($sale_id, $todaydate);
            }
            ////////////////////////////// Multiple Biller Invoice No End //////////////////////////////            //Update formated invoice no
            $this->db->where(['id' => $sale_id])->update($sma_sales, ['invoice_no' => $invoice_no]);

            if ($order_id) {
                //Update sale_invoice_no after convert order into sales. 
                $this->db->where(['id' => $order_id])->update('orders', ['sale_invoice_no' => $invoice_no]);
            }
            // End Invoice No

            if ($this->site->getReference($saleRefKey) == $data['reference_no']) {
                $this->site->updateReference($saleRefKey);
            }
            if (isset($data['return_sale_ref']) &&  $this->site->getReference($ReturnSaleRefKey) == $data['return_sale_ref']) {
                $this->site->updateReference($ReturnSaleRefKey);
            }
            $Setting = $this->Settings;

            foreach ($items as $item) {
                //------------------ End ----------------//
                $item['sale_id'] = $sale_id;
                $this->db->insert($sma_sales_items, $item);
                $sale_item_id = $this->db->insert_id();

                $DatalogArr = array('item' => $item, 'sale_item_id' => $sale_item_id);
                $DataLog = array(
                    'action_type' => 'Sale Products',
                    'product_id' => $item['product_id'],
                    'option_id' => $item['option_id'],
                    'batch_number' => $item['batch_number'] ? $item['batch_number'] : NULL,
                    'quantity' => $item['quantity'],
                    'action_reff_id' => "sma_sales.id:$sale_id",
                    'action_affected_data' => json_encode($DatalogArr),
                    'action_comment' => 'Add Sale'
                );
                $this->sma->setUserActionLog($DataLog);

                $_taxSaleID =  $sale_id;
                $_tax_type = ($sale_action == 'chalan' ? 'o' : NULL);
                $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID , $_tax_type);

                if ($data['sale_status'] == 'completed') {

                    $item_costs = $this->site->item_costing($item);

                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) {
                            if ($sale_action == 'chalan') {
                                $item_cost['order_item_id'] = $sale_item_id;
                                $item_cost['order_id'] = $sale_id;
                            } else {
                                $item_cost['sale_item_id'] = $sale_item_id;
                                $item_cost['sale_id'] = $sale_id;
                            }
                            if (!isset($item_cost['pi_overselling'])) {
                                unset($item_cost['unit_quantity']);
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                if (is_array($ic)):
                                    if ($sale_action == 'chalan') {
                                        $ic['order_item_id'] = $sale_item_id;
                                        $ic['order_id'] = $sale_id;
                                    } else {
                                        $ic['sale_item_id'] = $sale_item_id;
                                        $ic['sale_id'] = $sale_id;
                                    }

                                    if (!isset($ic['pi_overselling'])) {
                                        unset($ic['unit_quantity']);
                                        $this->db->insert('costing', $ic);
                                    }
                                endif;
                            }
                        }
                    }
                }
            }
            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    
                    $purchase_item_id = null;
                   
                    if ($this->Settings->overselling == 0) {
                        $costing_cause = ['sale_id'=>$return_item['sale_id'], 'product_id'=>$return_item['product_id'], 'sale_item_id'=>$return_item['id']];
                        $costingItem = $this->site->getProductCostings($costing_cause);                       
                        $purchase_item_id = $costingItem ? $costingItem->purchase_item_id : null;
                    }
                        
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            if ($sale_action == 'chalan') {
                                $this->orders_model->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                                $this->orders_model->updatePurchaseItem($purchase_item_id, ($return_item['quantity'] * $combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                            } else {
                                $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                                $this->updatePurchaseItem($purchase_item_id, ($return_item['quantity'] * $combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                            }
                            /* if($sale_action == 'sale') {
                              $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                              $this->updatePurchaseItem(NULL,($return_item['quantity']*$combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                              } */
                        }
                    } elseif ($product->type == 'Bundle') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            if ($sale_action == 'chalan') {
                                $this->orders_model->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                                $this->orders_model->updatePurchaseItem($purchase_item_id, ($return_item['quantity'] * $combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                            } else {
                                $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                                $this->updatePurchaseItem($purchase_item_id, ($return_item['quantity'] * $combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                            }
                            /* if($sale_action == 'sale') {
                              $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                              $this->updatePurchaseItem(NULL,($return_item['quantity']*$combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                              } */
                        }
                    }else {
                        
                        if ($sale_action == 'chalan') {
                            $this->orders_model->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                            $this->orders_model->updatePurchaseItem($purchase_item_id, $return_item['quantity'], $return_item['id']);
                        } else {
                            $this->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                            $this->updatePurchaseItem($purchase_item_id, $return_item['quantity'], $return_item['id'],$return_item['product_id'], $return_item['warehouse_id'],$return_item['option_id']);
                        }
                        /* if($sale_action == 'sale') {
                          $this->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                          $this->updatePurchaseItem(NULL, $return_item['quantity'], $return_item['id']);
                          } */
                    }
                }
                $this->db->update($sma_sales, array('return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'], 'return_sale_total' => $data['grand_total'], 'return_id' => $sale_id), array('id' => $data['sale_id']));
            }
            if ($sale_action == 'sale_return') {
                if (($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid') && !empty($payments)) {
                    if (empty($payments['reference_no'])) {
                        $payments['reference_no'] = $this->site->getReference('pay');
                    }

                    if ($sale_action == 'chalan') {
                        $payments['order_id'] = $sale_id;
                    } else {
                        $payments['sale_id'] = $sale_id;
                    }

                    if ($payments['paid_by'] == 'gift_card') {
                        $this->db->update('gift_cards', array('balance' => $payments['gc_balance']), array('card_no' => $payments['cc_no']));
                        unset($payments['gc_balance']);
                        $this->db->insert('payments', $payments);
                    } elseif ($payments['paid_by'] == 'credit_note') {
                        $this->db->update('credit_note', array('balance' => $payments['gc_balance']), array('card_no' => $payments['cc_no']));
                        unset($payments['gc_balance']);
                        $this->db->insert('payments', $payments);
                    } else {
                        if ($payments['paid_by'] == 'deposit') {
                            $customer = $this->site->getCompanyByID($data['customer_id']);
                            $this->db->update('companies', array('deposit_amount' => $payments['cc_holder']), array('id' => $data['customer_id']));
                            //$this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payments['amount'])), array('id' => $customer->id));
                        }
                        $this->db->insert('payments', $payments);
                    }
                    if ($this->site->getReference('pay') == $payments['reference_no']) {
                        $this->site->updateReference('pay');
                    }
                    //$this->site->syncSalePayments($sale_id);
                    $this->site->syncSaleActionPayments($sale_id, $sale_action);
                }
            } else {
                /*                 * *
                 * Multiple payment logic
                 * * */
                $msg = array();
                if (($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid') && !empty($payments)) {
                    //print_r($payments);
                    $paid = 0;
                    foreach ($payments as $payment) {
                        if (!empty($payment) && isset($payment['amount']) && $payment['amount'] != 0) {
                            if (empty($payment['reference_no'])) {
                                $payment['reference_no'] = $this->site->getReference('pay');
                            }

                            if ($sale_action == 'chalan') {
                                $payment['order_id'] = $sale_id;
                            } else {
                                $payment['sale_id'] = $sale_id;
                            }
                            if ($payment['paid_by'] == 'gift_card') {
                                $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                                unset($payment['gc_balance']);
                            } elseif ($payment['paid_by'] == 'credit_note') {

                                $this->db->update('credit_note', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                                unset($payment['gc_balance']);
                            } elseif ($payment['paid_by'] == 'deposit') {
                                $customer = $this->site->getCompanyByID($data['customer_id']);
                                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount - $payment['amount'])), array('id' => $customer->id));
                            }

                            $this->db->insert('payments', $payment);
                            if ($this->site->getReference('pay') == $payment['reference_no']) {
                                $this->site->updateReference('pay');
                            }
                            $paid += $payment['amount'];
                        }
                    }
                    $this->site->syncSaleActionPayments($sale_id, $sale_action);
                }

                /*                 * *
                 *  End Multiple payment logic
                 * * */
            }
            if ($this->Settings->synch_reward_points) {
                $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            }

            return $sale_id;
        }

        return false;
    }

      /**
     * generate_variant_po
     */
    public function getwarehousesById($id) {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
            if ($q->num_rows() > 0) {
                $data = $q->row();
                return $data;
            }
    }
    
    /**
     * Get Material Availability by Product ID and Warehouse ID
     * Returns the quantity of a product in a specific warehouse
     * 
     * @param int $product_id - Product/Material ID
     * @param int $warehouse_id - Warehouse ID
     * @return object|false - Returns warehouse_product row or FALSE
     */
    public function getMaterialWarehouseQuantity($product_id, $warehouse_id) {
        $this->db->select('quantity');
        $this->db->from('sma_warehouses_products');
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        
        return FALSE;
    }
    
    /**
     * Get Total Material Availability across All Warehouses
     * Returns the sum of quantities for a product across all warehouses
     * 
     * @param int $product_id - Product/Material ID
     * @return float - Total quantity across all warehouses
     */
    public function getTotalMaterialQuantity($product_id) {
        $this->db->select('COALESCE(SUM(quantity), 0) as total_quantity', FALSE);
        $this->db->from('sma_warehouses_products');
        $this->db->where('product_id', $product_id);
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
            $result = $q->row();
            return floatval($result->total_quantity);
        }
        
        return 0.0;
    }
    
    /**
     * Get Material Availability Data for Multiple Products
     * Returns availability data for multiple materials across warehouses
     * 
     * @param array $material_ids - Array of product/material IDs
     * @param int|null $supplier_warehouse_id - Supplier's warehouse ID (optional)
     * @param int|null $delivery_warehouse_id - Delivery warehouse ID (optional)
     * @return array - Array of availability data indexed by material_id
     */
    public function getMaterialsAvailability($material_ids, $supplier_warehouse_id = null, $delivery_warehouse_id = null) {
        $result = array();
        
        if (empty($material_ids) || !is_array($material_ids)) {
            return $result;
        }
        
        foreach ($material_ids as $material_id) {
            $material_id = (int)$material_id;
            if ($material_id <= 0) continue;
            
            // Get total available quantity across all warehouses
            $total_available = $this->getTotalMaterialQuantity($material_id);
            
            // Get supplier warehouse quantity
            $supplier_qty = 0;
            if ($supplier_warehouse_id) {
                $supplier_data = $this->getMaterialWarehouseQuantity($material_id, $supplier_warehouse_id);
                $supplier_qty = $supplier_data ? floatval($supplier_data->quantity) : 0;
            }
            
            // Get delivery warehouse quantity
            $delivery_qty = 0;
            if ($delivery_warehouse_id) {
                $delivery_data = $this->getMaterialWarehouseQuantity($material_id, $delivery_warehouse_id);
                $delivery_qty = $delivery_data ? floatval($delivery_data->quantity) : 0;
            }
            
            $result[$material_id] = array(
                'total_available' => $total_available,
                'supplier_qty' => $supplier_qty,
                'delivery_qty' => $delivery_qty
            );
        }
        
        return $result;
    }

      // Workstation helpers (do not modify existing functions)
    // Return all warehouses whose location_type is tagged as Workstation (for admin/owner)
    public function getWorkstationList()
    {
        return $this->db->select('w.id, w.name')
            ->from('warehouses w')
            ->join('location_type lt', 'lt.id = w.location_type', 'left')
            ->where('lt.type', 'Workstation')
            ->order_by('w.name', 'ASC')
            ->get()
            ->result();
    }

    // Return a single workstation by warehouse id (used to restrict non-admin users)
    public function getWorkstationListById($warehouseId)
    {
        return $this->db->select('w.id, w.name')
            ->from('warehouses w')
            ->join('location_type lt', 'lt.id = w.location_type', 'left')
            ->where('lt.type', 'Workstation')
            ->where('w.id', (int)$warehouseId)
            ->order_by('w.name', 'ASC')
            ->get()
            ->result();
    }

    // Return products for a specific workstation (warehouse) with order totals and stock
    public function getProductsByWorkstation($warehouseId , $primary_location_id = null)
    {
        $warehouseId = (int) $warehouseId;
     
        $this->db->distinct();
        $this->db->select('p.*, COALESCE(wp.quantity, 0) as stock_quantity, w.name as location_name, COALESCE(poi.total_order_quantity, 0) as order_quantity');
        $this->db->from('products p');
        // Restrict products by latest active BOM for the selected workstation
        $this->db->join('sma_produnit_bill_of_materials bom', 'bom.product_id = p.id', 'inner');
        // Stock now comes directly from warehouse products. Apply warehouse filter in JOIN to keep products even if no stock row.
        if ($primary_location_id) {
            $this->db->join('sma_warehouses_products wp', 'wp.product_id = p.id AND wp.warehouse_id = ' . (int) $primary_location_id, 'left');
        } else {
            $this->db->join('sma_warehouses_products wp', 'wp.product_id = p.id', 'left');
        }
        $this->db->join('warehouses w', 'w.id = bom.workstation_id', 'left');
        $this->db->join(
            '(SELECT product_id, production_unit_id, SUM(order_quantity) as total_order_quantity FROM sma_procurement_order_items WHERE item_status IN ("Committed", "Locked") GROUP BY product_id, production_unit_id) poi',
            'poi.product_id = p.id AND poi.production_unit_id = ' . $warehouseId,
            'left'
        );

        $this->db->where('bom.workstation_id', $warehouseId);
        $this->db->where('bom.is_active', 1);
        $this->db->where('p.type !=', 'raw');
        $this->db->where('p.flag_visible', 1);
        // Use latest BOM version per product for this workstation
        $this->db->where('bom.version_no = (SELECT MAX(CAST(version_no AS UNSIGNED)) FROM sma_produnit_bill_of_materials WHERE product_id = p.id AND workstation_id = ' . $warehouseId . ' AND is_active = 1)', null, false);
        $this->db->group_by('p.id');
        $this->db->order_by('COALESCE(poi.total_order_quantity, 0) DESC');

        return $this->db->get()->result();
    }

    ///////////////////////////// Kitchen User update warehouse product quantity /////////////////////////////
    public function KitchenUserupdateWarehouseProductQty($data){
       
        $product_id     = $data['product_id'];
        $quantity       = $data['quantity'];
        $warehouse_id   = $data['location_id'];
        
        $productstockdata = $this->site->getWarehouseProductQuantity($warehouse_id, $product_id); // get product stock details by product id
        $existing_stock   = $productstockdata->quantity;
        $new_stock        = $existing_stock + $quantity; 
       if (!empty($productstockdata)) {
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $this->db->update('sma_warehouses_products', array('quantity' => $new_stock)); 
        return true;
       } else {
        $this->db->insert('sma_warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity));
        return true;
       }    
    }
    public function getKitchenUserProductStock($product_id, $warehouse_id) {
        $this->db->select('quantity as stock_quantity');
        $this->db->from('sma_warehouses_products');
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        } else {
            return [];
        }
        return FALSE;
    }
    public function GetPrimaryUserWarehouse($user_id) {
        $this->db->select('warehouse_id');
        $this->db->from('users');
        $this->db->where('warehouse_id', $user_id);
        $q = $this->db->get();
        return $q->row();
    }
    ///////////////////////////// Kitchen User update warehouse product quantity END /////////////////////////////

    // Create Transfer when we dispatch order/ complete order from Working orders screen from production unit
    public function addTransfer($data = [], $items = []) {
        
        $status = $data['status'];
        $productsids = array();
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }
            $new_items=$items;
            foreach ($new_items as $n_item) {
                $n_item['transfer_id'] = $transfer_id;
                $n_item['item_status'] = $status;
                $n_item['created_by'] = $data['created_by'];
                $this->db->insert('transfer_item_new', $n_item);
            }
            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    $item['quantity_balance']  = $item['quantity_balance']  ? $item['quantity_balance']  : $item['quantity'];
                    $item['quantity_received'] = $item['quantity_received'] ? $item['quantity_received'] : $item['quantity'];
                    
                    if((float)$item['item_tax'] > 0){ 
                        $gst = (float)$item['item_tax'];
                        $tax = substr($item['tax'],0,4);
                                              
                        $item['gst_rate'] = (float)$tax / 2;
                        $item['cgst'] =  $item['sgst'] = ((float)$gst / 2);
                        $item['igst'] = 0;
                        //Check IGST Conditions   
                        if($data['to_warehouse_state_code']!='' && $data['from_warehouse_state_code']!=''){
                            if($data['to_warehouse_state_code'] != $data['from_warehouse_state_code']){                            
                               
                                $item['gst_rate'] = $tax;
                                $item['cgst'] =  $item['sgst'] = 0;
                                $item['igst'] = $gst;
                            }
                        } 
                    }
                    
                    $this->db->insert('purchase_items', $item);
                } else {
                    $item['item_status'] = $status;
                    $this->db->insert('transfer_items', $item);
                }

                // if ($status == 'sent' || $status == 'completed') {
                //     $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['quantity'], $item['option_id'], $item['batch_number']);

                //    $productsids [] = $item['product_id'];    
                // }
            }


            /*if(!empty($productsids)){
                // Urbanpiper Stock Manage 
                if($this->Settings->pos_type == 'restaurant'){
                    $this->load->model("Urban_piper_model","UPM");
                    $this->UPM->Product_out_of_stock($productsids,  $data['from_warehouse_id']);
                    $this->UPM->Product_out_of_stock($productsids,  $data['to_warehouse_id']);
               }
            }*/

            return true;
        }
        return false;
    }
    public function fetchSuspendedBillsWithItems($params = array()){

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');

        $this->db->select('sb.id, sb.date, sb.table_id, t.name AS display_invoice_no, sb.suspend_note, sb.order_type, sb.customer, sb.reference_no, sb.created_by, u.first_name, u.last_name');
        $this->db->from($this->db->dbprefix('suspended_bills') . ' sb');
        $this->db->join($this->db->dbprefix('restaurant_tables') . ' t', 't.id = sb.table_id', 'left');
        $this->db->join($this->db->dbprefix('users') . ' u', 'u.id = sb.created_by', 'left');
        $this->db->where("LOWER(sb.order_type) = 'dine in'", null, false);
        // Exclude completed orders from KDS
        // Note: Suspended bills don't have sale_status, so we check suspend_note
        $this->db->where("(sb.suspend_note IS NULL OR sb.suspend_note NOT LIKE 'Completed from KDS%')", null, false);

        if (!empty($params['warehouse_id'])) {
            $this->db->where('sb.warehouse_id', $params['warehouse_id']);
        }
        if (!empty($params['from_date'])) {
            $this->db->where('sb.date >=', $params['from_date']);
        }
        if (!empty($params['to_date'])) {
            $this->db->where('sb.date <=', $params['to_date']);
        }

        // FIFO: oldest first by timestamp
        $this->db->order_by('sb.date', 'asc');
        // $this->db->where('sb.date >=', $todayStart);
        // $this->db->where('sb.date <=', $todayEnd);

        $orders = $this->db->get()->result();
        $orderIds = array();
        foreach ($orders as $order) {
            $order->time = date('H:i', strtotime($order->date));

            $orderIds[] = $order->id;
        }

        $items = array();
        if (!empty($orderIds)) {
            $this->db->select('sbi.id, sbi.suspend_id, sbi.quantity, sbi.isdelivered, sbi.product_name, sbi.note AS comment, pv.name AS variant_name');
            $this->db->from($this->db->dbprefix('suspended_items') . ' sbi');
            $this->db->join($this->db->dbprefix('product_variants') . ' pv', 'pv.id = sbi.option_id', 'left');
            $this->db->where_in('sbi.suspend_id', $orderIds);
            $this->db->order_by('sbi.id', 'asc');

            $rows = $this->db->get()->result();
            foreach ($rows as $row) {
                $items[$row->suspend_id][] = $row;
            }
        }

        return array(
            'orders' => $orders,
            'items' => $items,
        );
    }


    public function fetchSalesOrdersWithItems($params = array()){


        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');

        /* ------------------ FETCH ORDERS ------------------ */
        $this->db->select("
            s.id,
            s.date,
            s.order_type,

            CASE
                WHEN s.up_channel IS NOT NULL AND s.up_channel != ''
                    THEN CONCAT(s.invoice_no, ' - ', s.up_channel)
                ELSE s.invoice_no
            END AS display_invoice_no,

            s.invoice_no,
            s.reference_no,
            s.customer,
            s.pos,
            s.eshop_sale,
            s.up_sales,
            s.up_channel,
            s.up_order_id,
            s.created_by,
            s.seller,
            u.first_name,
            u.last_name
        ", false);

        $this->db->from($this->db->dbprefix('sales') . ' s');
        $this->db->join($this->db->dbprefix('users') . ' u', 'u.id = s.created_by', 'left');

        // ---- KDS relevant orders only ----
        $this->db->group_start();

            // Webshop Take Away / Pickup
            $this->db->group_start();
                $this->db->where('s.eshop_sale', 1);
                $this->db->where("LOWER(s.order_type) IN ('take away','takeaway','pickup','pick up', 'Door Delivery')", null, false);
            $this->db->group_end();

            // Aggregator orders
            $this->db->or_group_start();
                $this->db->where('s.up_sales', 1);
            $this->db->group_end();

            // POS Take Away / Delivery
            $this->db->or_group_start();
                $this->db->where('s.pos', 1);
                $this->db->where("LOWER(s.order_type) IN ('take away','takeaway','delivery', 'Door Delivery')", null, false);
                $this->db->where('s.eshop_sale', 0);
                $this->db->where('s.up_sales', 0);
            $this->db->group_end();

        $this->db->group_end();

        // Exclude completed orders from KDS
        // $this->db->where("(s.sale_status IS NULL OR s.sale_status != 'completed')", null, false);

        // Optional filters
        if (!empty($params['warehouse_id'])) {
            $this->db->where('s.warehouse_id', $params['warehouse_id']);
        }
        if (!empty($params['from_date'])) {
            $this->db->where('s.date >=', $params['from_date']);
        }
        if (!empty($params['to_date'])) {
            $this->db->where('s.date <=', $params['to_date']);
        }

        // FIFO for KDS
        $this->db->order_by('s.date', 'asc');
        // $this->db->where('s.date >=', $todayStart);
        // $this->db->where('s.date <=', $todayEnd);


        $orders = $this->db->get()->result();

        /* ------------------ COLLECT ORDER IDS ------------------ */
        $orderIds = [];
        foreach ($orders as $order) {

            // Optional UI helpers
            // $order->staff_name = trim($order->first_name . ' ' . $order->last_name);
            $order->staff_name = $order->seller;
            $order->time = date('H:i', strtotime($order->date));

            $orderIds[] = $order->id;
        }

        /* ------------------ FETCH ITEMS ------------------ */
        $items = [];

        if (!empty($orderIds)) {
            $this->db->select('
                si.id,
                si.sale_id,
                si.quantity,
                si.product_name,
                si.note AS comment,
                pv.name AS variant_name
            ');
            $this->db->from($this->db->dbprefix('sale_items') . ' si');
            $this->db->join($this->db->dbprefix('product_variants') . ' pv', 'pv.id = si.option_id', 'left');
            $this->db->where_in('si.sale_id', $orderIds);
            $this->db->order_by('si.id', 'asc');

            $rows = $this->db->get()->result();

            foreach ($rows as $row) {
                $items[$row->sale_id][] = $row;
            }
        }

        /* ------------------ FINAL RESPONSE ------------------ */
        return [
            'orders' => $orders,
            'items'  => $items
        ];
       
    }

    // fetch raw materials for variant product (manager dashboard variant RM popup)
    public function get_variant_raw_materials($product_id, $option_id, $location_id)
    {
        $product_id  = (int) $product_id;
        $option_id   = (int) $option_id;
        $location_id = (int) $location_id;

        if ($product_id <= 0 || $option_id <= 0 || $location_id <= 0) {
            return false;
        }

        $sql = "
            SELECT DISTINCT
                bom.version_no,
                bom.notes,
                bom_items.bom_id,
                bom_items.material_id,
                bom_items.bom_ref,
                bom_items.quantity_required,
                bom_items.option_id AS variant_option_id,
                p.name AS raw_material,
                bom.product_id AS product_id,
                u.name AS unit_name,
                pb.is_batch_only,
                pb.min_batch_qty,
                bu.name AS batch_unit_name,
                bom_items.is_alternative,
                bom_items.primary_product_id,
                pb.sales_units,
                pi.expiry AS expiry_date,
                pi.batch_number AS batch_no,
                CASE
                    WHEN IFNULL(pi.batch_available_qty, 0) > 0 THEN pi.batch_available_qty
                    ELSE 0
                END AS available_qty
            FROM sma_produnit_bill_of_material_items AS bom_items
            JOIN sma_produnit_bill_of_materials AS bom
                ON bom_items.bom_id = bom.id
            LEFT JOIN sma_products p
                ON bom_items.material_id = p.id
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
            LEFT JOIN sma_units u
                ON bom_items.uom = u.id
            LEFT JOIN sma_units bu
                ON pb.batch_uom = bu.id
            LEFT JOIN (
                SELECT
                    pi.product_id,
                    pi.warehouse_id,
                    pi.batch_number,
                    pi.expiry,
                    SUM(pi.quantity_balance) AS batch_available_qty
                FROM sma_purchase_items pi
                WHERE pi.warehouse_id = ?
                GROUP BY
                    pi.product_id,
                    pi.warehouse_id,
                    pi.batch_number,
                    pi.expiry
            ) AS pi
                ON pi.product_id = bom_items.material_id
                AND pi.warehouse_id = ?
            WHERE bom.product_id = ?
            AND bom.version_no = (
                SELECT MAX(CAST(version_no AS UNSIGNED))
                FROM sma_produnit_bill_of_materials
                WHERE product_id = ? AND is_active = 1
            )
            AND bom.is_active = 1
            AND (
                bom_items.option_id IS NULL
                OR bom_items.option_id = 0
                OR bom_items.option_id = ?
            )
            ORDER BY
                COALESCE(bom_items.primary_product_id, bom_items.material_id),
                CASE WHEN bom_items.is_alternative = 1 THEN 1 ELSE 0 END,
                bom_items.material_id,
                pi.batch_number,
                pi.expiry
        ";

        $query = $this->db->query($sql, [
            $location_id,
            $location_id,
            $product_id,
            $product_id,
            $option_id,
        ]);

        if (!$query || $query->num_rows() === 0) {
            return false;
        }

        $result = $query->result();

        // Per material: variant BOM line overrides generic; skip if recipe qty is 0 for this variant
        $recipe_qty_by_material = array();
        foreach ($result as $row) {
            $material_id = (int) $row->material_id;
            $variant_opt   = $row->variant_option_id === null ? 0 : (int) $row->variant_option_id;
            $qty           = (float) $row->quantity_required;

            if ($variant_opt === $option_id) {
                $recipe_qty_by_material[$material_id] = $qty;
            }
        }
        foreach ($result as $row) {
            $material_id = (int) $row->material_id;
            if (isset($recipe_qty_by_material[$material_id])) {
                continue;
            }
            $variant_opt = $row->variant_option_id === null ? 0 : (int) $row->variant_option_id;
            if ($variant_opt === 0) {
                $recipe_qty_by_material[$material_id] = (float) $row->quantity_required;
            }
        }

        $allowed_material_ids = array();
        foreach ($recipe_qty_by_material as $material_id => $qty) {
            if ($qty > 0) {
                $allowed_material_ids[$material_id] = true;
            }
        }

        $result = array_values(array_filter($result, function ($row) use ($allowed_material_ids) {
            return isset($allowed_material_ids[(int) $row->material_id]);
        }));

        if (empty($result)) {
            return false;
        }

        $grouped = [];
        foreach ($result as $row) {
            $grouped[$row->material_id][] = $row;
        }

        $filteredResult = [];
        foreach ($grouped as $rows) {
            $positiveQtyRows = array_filter($rows, function ($r) {
                return floatval($r->available_qty) > 0;
            });
            if (!empty($positiveQtyRows)) {
                $filteredResult = array_merge($filteredResult, $positiveQtyRows);
            } else {
                $filteredResult[] = $rows[0];
            }
        }

        return !empty($filteredResult) ? array_values($filteredResult) : false;
    }
    public function get_variant_raw_material_for_product($product_id, $material_id, $option_id)
    {
        $product_id  = (int) $product_id;
        $material_id = (int) $material_id;
        $option_id   = (int) $option_id;

        $this->db
            ->select('b.version_no, i.*, p.name AS material_name')
            ->from('sma_produnit_bill_of_material_items i')
            ->join('sma_produnit_bill_of_materials b', 'b.id = i.bom_id')
            ->join('sma_products p', 'p.id = i.material_id', 'left')
            ->where('i.material_id', $material_id)
            ->where('b.product_id', $product_id)
            ->where('b.is_active', 1)
            ->group_start()
                ->where('i.option_id IS NULL', null, false)
                ->or_where('i.option_id', 0)
                ->or_where('i.option_id', $option_id)
            ->group_end()
            ->order_by('LENGTH(b.version_no)', 'DESC', false)
            ->order_by('b.version_no', 'DESC')
            ->limit(1);

        return $this->db->get()->row();
    }
    public function addVariantProductBatch($data){

        if ($this->db->insert('product_batches', $data)) {
            return true ;
        }
        return false;
    }
    public function saveVariantRmConsumption($pu_batch_id,$product_id,$option_id,$location_id,$batch_quantity,$ingredient_entries,$wastage_map) {
        $processed_keys = [];
        if (!empty($ingredient_entries)) {
            foreach ($ingredient_entries as $entry) {
                $parts = explode('|', $entry);
                $material_id = isset($parts[0]) ? (int) $parts[0] : 0;
                $selected_rm_batch_no = isset($parts[1]) ? trim($parts[1]) : '';
                $selected_rm_expiry_date = isset($parts[2]) ? trim($parts[2]) : '';
                $required_quantity = isset($parts[3]) ? trim($parts[3]) : '';
                if (!$material_id) {
                    continue;
                }

                $ingredient = $this->get_variant_raw_material_for_product($product_id, $material_id, $option_id);
                if (!$ingredient) {
                    continue;
                }

                if ($required_quantity !== null && $required_quantity !== '') {
                    $consumed_qty = $required_quantity;
                } else {
                    $consumed_qty = $batch_quantity * $ingredient->quantity_required;
                }

                $rm_batch_row = null;
                if ($selected_rm_batch_no !== '') {
                    $this->db->where('product_id', $material_id);
                    $this->db->where('batch_no', $selected_rm_batch_no);
                    $this->db->limit(1);
                    $rm_batch_row = $this->db->get('sma_product_batches')->row();
                }

                $wastage_key = $material_id . ($selected_rm_batch_no ? '|' . $selected_rm_batch_no : '');
                $wastage = isset($wastage_map[$wastage_key]) ? $wastage_map[$wastage_key] : 0;

                $raw_material_data = [
                    'product_id'  => $material_id,
                    'quantity'    => $consumed_qty + $wastage,
                    'location_id' => $location_id,
                    'batch_no'    => $rm_batch_row ? $rm_batch_row->batch_no : null,
                    'expiry'      => $selected_rm_expiry_date ? $selected_rm_expiry_date : null,
                ];
                $this->updateRawMaterialQty($raw_material_data);

                $this->db->insert('sma_rm_consumption', [
                    'pu_batch' => $pu_batch_id,
                    'rm_batch' => $rm_batch_row ? $rm_batch_row->id : null,
                    'rm_name'  => $ingredient->material_name,
                    'consumed' => $consumed_qty,
                    'wastage'  => $wastage,
                ]);

                $processed_keys[$wastage_key] = true;
            }
        }
        if (!empty($wastage_map)) {
            foreach ($wastage_map as $map_key => $map_wastage) {
                if (isset($processed_keys[$map_key])) {
                    continue;
                }
                $parts = explode('|', $map_key);
                $material_id = isset($parts[0]) ? (int) $parts[0] : 0;
                $selected_rm_batch_no = isset($parts[1]) ? trim($parts[1]) : '';
                if (!$material_id) {
                    continue;
                }

                $rm_batch_row = null;
                if ($selected_rm_batch_no !== '') {
                    $this->db->where('product_id', $material_id);
                    $this->db->where('batch_no', $selected_rm_batch_no);
                    $this->db->limit(1);
                    $rm_batch_row = $this->db->get('sma_product_batches')->row();
                }

                $product_row = $this->db->select('name')->where('id', $material_id)->get('sma_products')->row();
                $material_name = $product_row ? $product_row->name : '';

                $this->updateRawMaterialQty([
                    'product_id'  => $material_id,
                    'quantity'    => $map_wastage,
                    'location_id' => $location_id,
                    'batch_no'    => $rm_batch_row ? $rm_batch_row->batch_no : null,
                ]);

                $this->db->insert('sma_rm_consumption', [
                    'pu_batch' => $pu_batch_id,
                    'rm_batch' => $rm_batch_row ? $rm_batch_row->id : null,
                    'rm_name'  => $material_name,
                    'consumed' => 0,
                    'wastage'  => $map_wastage,
                ]);
            }
        }
        return true;
    }
    /**
     * Variant finished goods: same structure as updateWarehouseProductQty, keyed by option_id.
     * $data: product_id, location_id, quantity (sales qty), option_id.
     */
    public function updateWarehouseProductVariantQty($data)
    {
        $product_id   = $data['product_id'];
        $add_quantity = (float) $data['quantity'];
        $warehouse_id = $data['location_id'];
        $option_id    = isset($data['option_id']) ? (int) $data['option_id'] : 0;

        if (empty($product_id) || $option_id <= 0 || $add_quantity <= 0) {
            return false;
        }

        $lastBatch = $this->site->getLastBatchNumber($product_id);
        $batch_id = $lastBatch ? $lastBatch->id : null;

        // 1) Reuse qty=0 placeholder (same as non-variant updateWarehouseProductQty)
        $placeholder = $this->db->select('wpv.id, wpv.quantity')
            ->from('sma_warehouses_products_variants wpv')
            ->join('sma_warehouses w', 'w.id = wpv.warehouse_id', 'left')
            ->join('sma_location_type lt', 'lt.id = w.location_type', 'left')
            ->where('wpv.product_id', $product_id)
            ->where('wpv.warehouse_id', $warehouse_id)
            ->where('wpv.option_id', $option_id)
            ->where('wpv.quantity', 0)
            ->where('wpv.batch_id IS NULL', null, false)
            ->where_in('lt.type', array('Workstation', 'Production Unit'))
            ->limit(1)
            ->get()
            ->row();

        if ($placeholder) {
            $update = array(
                'product_id'   => $product_id,
                'warehouse_id' => $warehouse_id,
                'option_id'    => $option_id,
                'quantity'     => $add_quantity,
            );
            if ($batch_id) {
                $update['batch_id'] = $batch_id;
            }
            $this->db->update('sma_warehouses_products_variants', $update, array('id' => $placeholder->id));
            return true;
        }

        // 2) Existing stock for this variant → add sales qty (multi batch / second variant row)
        $stock_row = $this->db->select('wpv.id, wpv.quantity')
            ->from('sma_warehouses_products_variants wpv')
            ->where('wpv.product_id', $product_id)
            ->where('wpv.warehouse_id', $warehouse_id)
            ->where('wpv.option_id', $option_id)
            ->order_by('wpv.id', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        if ($stock_row) {
            $this->db->update('sma_warehouses_products_variants',array('quantity' => (float) $stock_row->quantity + $add_quantity),array('id' => $stock_row->id));
            return true;
        }

        // 3) No row yet → insert
        $insert_data = array(
            'product_id'   => $product_id,
            'warehouse_id' => $warehouse_id,
            'option_id'    => $option_id,
            'quantity'     => $add_quantity,
        );
        if ($batch_id) {
            $insert_data['batch_id'] = $batch_id;
        }
        $this->db->insert('sma_warehouses_products_variants', $insert_data);
        return true;
    }

    /**
     * Variant Add Batch stock sync — mirrors non-variant 3-step flow (lines 972–974 in Production_Unit).
     */
    public function syncVariantStockForProductionUnit($product_id, $location_id, $batch_row)
    {
        $option_id = isset($batch_row['option_id']) ? (int) $batch_row['option_id'] : 0;
        $batch_no = isset($batch_row['batch_no']) ? $batch_row['batch_no'] : null;
        $sale_qty = isset($batch_row['quantity']) ? $batch_row['quantity'] : 0;

        if (empty($product_id) || $option_id <= 0) {
            return false;
        }
        // Same order as non-variant: purchase_items → warehouse variant row → then parent product sum
        $this->site->syncVariantPurchaseItemsForProductionUnit(
            $product_id,
            $location_id,
            $sale_qty,
            $batch_no,
            $option_id
        );
        $this->updateWarehouseProductVariantQty($batch_row);
        $this->site->syncVariantProductQtyForPU($product_id, $location_id);

        return true;
    }
    
}
?>