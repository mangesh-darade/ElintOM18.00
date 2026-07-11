<?php
class Production_Unit_Model_New extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->created_by      = $this->session->userdata('user_id');
       
    }
    // get location data
    public function getLocations() {

        $this->db->where_in('location_type', array('3', '4')); //location_type != Production Unit and HO, means show only Retail Outlet and Stockist
        $this->db->order_by('name', 'ASC');
        $q = $this->db->get('sma_warehouses');
            if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    // get location id for filter 
    public function getLocationID($locationName) {
       
        $q = $this->db->get_where('sma_warehouses', ['name' => $locationName], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
        return FALSE;
    }
    // get order id wise filter 

    public function getProcurmentOrderById($procurmentOrderId) {
       
        $q = $this->db->get_where('sma_procurement_orders', ['id' => $procurmentOrderId], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
        return FALSE;
    }

    // All Orders for Logged in Kitchen <= Production Unit
    public function getAllOrdersForLoggedInProductionUnit($user_id, $outletId, $sort_order_by_time) {
     
        // Get user data to access warehouse_id
        $user = $this->db->get_where('sma_users', ['id' => $user_id])->row();
        
        // Get Workstation location IDs from user's warehouse_id
        $workstation_location_ids = array();
        if ($user && !empty($user->warehouse_id)) {
            $location_ids = explode(',', $user->warehouse_id);
            foreach ($location_ids as $location_id) {
                $location_id = trim($location_id);
                if (!empty($location_id)) {
                    // Get location data
                    $location_data = $this->db->get_where('sma_warehouses', ['id' => $location_id])->row();
                    if ($location_data && !empty($location_data->location_type)) {
                        // Check if location type is "Workstation"
                        $location_type_result = $this->db->get_where('sma_location_type', [
                            'id' => $location_data->location_type, 
                            'type' => 'Workstation'
                        ])->row();
                        if ($location_type_result) {
                            // Add to Workstation location IDs array
                            $workstation_location_ids[] = $location_id;
                        }
                    }
                }
            }
        }
    
        $this->db->distinct();
        $this->db->select('sma_procurement_orders.*');
        $this->db->from('sma_procurement_orders');
        $this->db->join('sma_procurement_order_items', 'sma_procurement_orders.id = sma_procurement_order_items.procurement_orders_id', 'left');
        // Join with users table - check if production_unit_id exists in user's comma-separated warehouse_id using FIND_IN_SET
        $this->db->join('sma_users', 'FIND_IN_SET(sma_procurement_order_items.production_unit_id, sma_users.warehouse_id) > 0', 'left');
        $this->db->where('sma_users.id', $user_id);

        // filter orders by outlet
        if($outletId){
            $this->db->where('sma_procurement_orders.location_id', $outletId);
        }
        if($sort_order_by_time){
            if ($sort_order_by_time == 'Newest') {
                $this->db->order_by('sma_procurement_orders.order_creation_date', 'desc');
            }else{
                $this->db->order_by("sma_procurement_orders.order_creation_date", 'asc');
            }
        }else{
            $this->db->order_by("sma_procurement_orders.id", 'asc');
        }
        

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    // completed Orders for Logged in Kitchen <= Production Unit
    public function getCompleteOrdersForLoggedInProductionUnit($user_id, $outletId, $sort_order_by_time, $order_dispatch) {

         // Get user data to access warehouse_id
        $user = $this->db->get_where('sma_users', ['id' => $user_id])->row();
        
        // Get Workstation location IDs from user's warehouse_id
        $workstation_location_ids = array();
        if ($user && !empty($user->warehouse_id)) {
            $location_ids = explode(',', $user->warehouse_id);
            foreach ($location_ids as $location_id) {
                $location_id = trim($location_id);
                if (!empty($location_id)) {
                    // Get location data
                    $location_data = $this->db->get_where('sma_warehouses', ['id' => $location_id])->row();
                    if ($location_data && !empty($location_data->location_type)) {
                        // Check if location type is "Workstation"
                        $location_type_result = $this->db->get_where('sma_location_type', [
                            'id' => $location_data->location_type, 
                            'type' => 'Workstation'
                        ])->row();
                        if ($location_type_result) {
                            // Add to Workstation location IDs array
                            $workstation_location_ids[] = $location_id;
                        }
                    }
                }
            }
        }

        $this->db->distinct();
        $this->db->select('sma_procurement_orders.*');
        $this->db->from('sma_procurement_orders');
        $this->db->join('sma_procurement_order_items', 'sma_procurement_orders.id = sma_procurement_order_items.procurement_orders_id', 'left');
        // $this->db->join('sma_users', 'sma_users.warehouse_id = sma_procurement_order_items.production_unit_id', 'left');
        // Join with users table - check if production_unit_id exists in user's comma-separated warehouse_id using FIND_IN_SET
        $this->db->join('sma_users', 'FIND_IN_SET(sma_procurement_order_items.production_unit_id, sma_users.warehouse_id) > 0', 'left');
        $this->db->where('sma_users.id', $user_id);

        // $this->db->where('sma_procurement_orders.status', 'Completed');

        // filter orders by outlet
        if($outletId){
            $this->db->where('sma_procurement_orders.location_id', $outletId);
        }
        if($order_dispatch == '1'){
            $this->db->where('sma_procurement_orders.status', 'Dispatched');
        }else{
            $this->db->where('sma_procurement_orders.status', 'Completed');
        }
        if($sort_order_by_time){
            if ($sort_order_by_time == 'Newest') {
                $this->db->order_by('sma_procurement_orders.order_creation_date', 'desc');
            }else{
                $this->db->order_by("sma_procurement_orders.order_creation_date", 'asc');
            }
        }else{
            $this->db->order_by("sma_procurement_orders.id", 'asc');
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    // get order items against order

    public function getOrderItemsForSelectedOrder($procurementOrderId, $orderItemId, $location_name) {


        // Use warehouses_products for stock (sum across all batch rows) and derive open_order_quantity from procurement items
        $this->db->select("
            poi.id as itemId,
            poi.procurement_orders_id,
            poi.product_id,
            poi.product_name,
            poi.order_quantity,
            poi.allot_quantity,
            poi.received_quantity,
            poi.product_code,
            poi.item_status,
            poi.production_unit_name,
            po.procurement_order_ref_no,
            po.location_name,
            po.location_code,
            po.status as order_status,
            CAST(COALESCE(wp.quantity, 0) AS UNSIGNED) as stock_quantity,
            (
                SELECT COALESCE(SUM(poi2.order_quantity - COALESCE(poi2.allot_quantity, 0)), 0)
                FROM sma_procurement_order_items poi2
                WHERE poi2.product_id = poi.product_id
                  AND poi2.production_unit_id = poi.production_unit_id
                  AND poi2.item_status IN ('Open','Locked','Committed','partially_completed')
            ) AS open_order_quantity,
            po.note,
            poi.production_unit_id,
            po.order_creation_date,
            poi.unit_price,
            poi.tax_rate_id,
            poi.item_tax,
            poi.net_unit_cost,
            poi.unit_cost,
            poi.alloted_by
        ", false);
        $this->db->from('sma_procurement_order_items as poi');
        $this->db->join('sma_procurement_orders as po', 'poi.procurement_orders_id = po.id', 'left');
        $this->db->join('products ', 'products.id = poi.product_id', 'left');
        // Aggregate warehouses_products so one row per (product_id, warehouse_id) with SUM(quantity) - avoids duplicates from multiple batch rows
        $this->db->join('(
            SELECT product_id, warehouse_id, SUM(quantity) AS quantity
            FROM sma_warehouses_products
            GROUP BY product_id, warehouse_id
        ) wp', 'wp.product_id = poi.product_id AND wp.warehouse_id = poi.production_unit_id', 'left');
    
        if($procurementOrderId){
            $this->db->where('poi.procurement_orders_id', $procurementOrderId);
        }
        if($orderItemId){
            $this->db->where('poi.id', $orderItemId);
        }
        // filter : sort location name wise order items
        if($location_name){
            $this->db->where('po.location_name', $location_name);
        }
       
        //$this->db->order_by("po.id", "desc");

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                // $row->user_location_id = $location_id; // Add the location_id to each row object
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    //update order details
    public function updateOrder($procurmentOrderId, $Locked, $isChecked, $allotQuantityInput, $orderItemId, $bulkAllotCheck, $itemData, $completeOrderFlag, $user_id,$deliveryDateTime) {

        if (!empty($deliveryDateTime)) {
            $this->db->update('sma_procurement_orders', array('planned_delivery_datetime' => $deliveryDateTime), array('id' => $procurmentOrderId));
       exit;
        }
        $order_items =  $this->getOrderItemsForSelectedOrder($procurmentOrderId); // get order id wise order items

        // when click on lock button, update status to 'locked'
        if($Locked == 'true'){
            $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurmentOrderId));
            $this->db->update('sma_procurement_order_items', array('item_status' => 'Locked'), array('procurement_orders_id' => $procurmentOrderId));
        }
        // update order item status, allot qty and stock qty
        if($isChecked){
            if ($isChecked === "Checked") {

                $data = array(
                    'allot_quantity' => $allotQuantityInput,
                    'item_status'    => 'Committed',
                    'alloted_by'     => $user_id

                );
                $this->db->where('id', $orderItemId);
                $this->db->update('sma_procurement_order_items', $data);
            }else{
                
                $data = array(
                    'allot_quantity' => 0,
                    'item_status'    => 'Locked',
                    'alloted_by'     => $user_id

                );
                $this->db->where('id', $orderItemId);
                $this->db->update('sma_procurement_order_items', $data);
            }
            // $this->updateProductStock($allotQuantityInput, $orderItemId, $isChecked);
            // update quantity against warehouse in warehouse products table
            $this->syncWarehouseProductStockForProductionUnit($procurmentOrderId, $allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData);
            // update quantity balance against warehouse and products in purchase items table  when click on check box in grid
            $this->updatePurchaseItemsForProductionUnit($procurmentOrderId, $allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData);
            
        }
        // update order item status, allot qty and stock qty for bulk allot
        if($bulkAllotCheck){
          
            if(!empty($itemData)){

                foreach ($itemData as $item) {
                 
                    $order_item_id  = $item['orderItemId'];
                    $allot_quantity_input = $item['allotQuantity'];

                    if($bulkAllotCheck === "allChecked"){

                        $data = array(
                            'allot_quantity' => $allot_quantity_input,
                            'alloted_by'     => $user_id,
                            'item_status'    => 'Committed'
                        );
                        $this->db->where('id', $order_item_id);
                        $this->db->update('sma_procurement_order_items', $data);
    
                    }else {
                        $data = array(
                            'allot_quantity' => '0',
                            'alloted_by'     => $user_id,
                            'item_status' => 'Locked'
                        );
                        
                        $this->db->where('id', $order_item_id);
                        $this->db->update('sma_procurement_order_items', $data);
                    }
                }
                // $this->updateProductStock($allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData);
                 // update quantity against warehouse in warehouse products table
                $this->syncWarehouseProductStockForProductionUnit($procurmentOrderId, $allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData);
                 // update quantity balance against warehouse and products in purchase items table  when click on check box in grid
                $this->updatePurchaseItemsForProductionUnit($procurmentOrderId, $allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData);
            }
        }
        // complete order functionality
        if($completeOrderFlag){

            $order_items =  $this->getOrderItemsForSelectedOrder($procurmentOrderId); // get order item id wise order items data
            $itemStatus = array('Completed', 'partially_completed', 'Pending');  // Array to store item statuses
            $CheckCompleteOrder = true;
            foreach($order_items as $data){
            
                $orderItemId           = $data->itemId;
                $item_status           = $data->item_status;
                $allot_quantity        = (float) $data->allot_quantity;
                $order_quantity        = (float) $data->order_quantity;

                if($item_status === 'Committed')
                {
                    if($allot_quantity == $order_quantity){
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'Completed'), array('id' => $orderItemId));
                        $item_status  = 'Completed';
                    }elseif($allot_quantity < $order_quantity && $allot_quantity != 0){
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'partially_completed'), array('id' => $orderItemId));
                        $item_status  = 'partially_completed';
                    }
                    else{
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'Pending'), array('id' => $orderItemId));
                        $item_status  = 'Pending';
                    }
                }
                // After updating, check if the status is valid
                if (!in_array($item_status, $itemStatus)) {
                    $CheckCompleteOrder = false;
                }
            }
            // Update order status if all items are completed
            if ($CheckCompleteOrder) {
                $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurmentOrderId));
            }
        }
    }
    // fetch sales data by procurement order reference number
    public function getSalesDataByRefNum($reference_number) {
       
        $q = $this->db->get_where('sma_sales', ['order_no' => $reference_number], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
        return FALSE;
    }
    /**
     * Adjust stock in warehouses_products by batch (FIFO).
     * Allot: reduce qty from batch 1, then batch 2, etc.
     * Unallot: add back in reverse order (newest first), using purchase_items quantity as ceiling per batch.
     */
    public function updateWarehouseProductStockFIFO($warehouse_id, $product_id, $quantity, $reverse = false) {
        $qty = (float) $quantity;
        if ($qty <= 0) return;

        $where = array('product_id' => $product_id, 'warehouse_id' => $warehouse_id);

        if ($reverse) {
            // Unallot: use purchase_items distribution (reverse order, quantity ceiling per row)
            $this->db->select('id, quantity, quantity_balance, batch_number');
            $this->db->from('purchase_items');
            $this->db->where('product_id', $product_id);
            $this->db->where('warehouse_id', $warehouse_id);
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
            $this->db->order_by('date', 'desc');
            $this->db->order_by('id', 'desc');
            $pis = $this->db->get()->result();
            if (!$pis) return;

            $remaining = $qty;
            foreach ($pis as $pi) {
                if ($remaining <= 0) break;
                $room = (float) $pi->quantity - (float) $pi->quantity_balance;
                if ($room <= 0) continue;
                $add = min($remaining, $room);
                $remaining -= $add;
                // Find warehouses_products row by batch (batch_id = product_batches.id, batch_no = pi.batch_number)
                $this->db->select('wp.id, wp.quantity');
                $this->db->from('warehouses_products wp');
                $this->db->join('product_batches pb', 'wp.batch_id = pb.id AND pb.product_id = ' . (int) $product_id, 'inner');
                $this->db->where('wp.product_id', $product_id);
                $this->db->where('wp.warehouse_id', $warehouse_id);
                if ($pi->batch_number === null || $pi->batch_number === '') {
                    $this->db->group_start()->where('pb.batch_no', null)->or_where('pb.batch_no', '')->group_end();
                } else {
                    $this->db->where('pb.batch_no', $pi->batch_number);
                }
                $wp = $this->db->limit(1)->get()->row();
                if ($wp) {
                    $this->db->update('warehouses_products',
                        array('quantity' => (float) $wp->quantity + $add),
                        array('id' => $wp->id));
                } else {
                    // No batch match: add to oldest wp row
                    $wp = $this->db->select('id, quantity')->from('warehouses_products')
                        ->where($where)
                        ->order_by('COALESCE(batch_id, 999999)', 'ASC', false)
                        ->order_by('id', 'ASC')
                        ->limit(1)->get()->row();
                    if ($wp) {
                        $this->db->update('warehouses_products',
                            array('quantity' => (float) $wp->quantity + $add),
                            array('id' => $wp->id));
                    }
                }
            }
            return;
        }

        // Allot: take from rows in batch order until qty is done
        $rows = $this->db->select('id, quantity')
            ->from('warehouses_products')
            ->where($where)
            ->where('quantity >', 0)
            ->order_by('COALESCE(batch_id, 999999)', 'ASC', false)
            ->order_by('id', 'ASC')
            ->get()->result();

        $remaining = $qty;
        foreach ($rows as $row) {
            if ($remaining <= 0) break;
            $take = min($remaining, (float) $row->quantity);
            $this->db->update('warehouses_products',
                array('quantity' => (float) $row->quantity - $take),
                array('id' => $row->id));
            $remaining -= $take;
        }
    }

    // update quantity against warehouse in warehouse products table when click on check box in grid
    public function syncWarehouseProductStockForProductionUnit($procurmentOrderId, $allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData) {

        // update stock for specific order item
        if($isChecked){

            $order_items =  $this->getOrderItemsForSelectedOrder($procurmentOrderId, $orderItemId);
            $product_id = $warehouse_id = null;
            if ($order_items) {
                foreach($order_items as $data){
                    $product_id            = $data->product_id;
                    $warehouse_id          = $data->production_unit_id;
                    $warehouse_data        = $this->site->getWarehouseBy_ID($warehouse_id);
                    $location_id           = $this->Production_Unit_Model_New->get_location_type_name($warehouse_data);
                    if($location_id){
                        $user_id       = $this->session->userdata('user_id');
                        $user_data     = $this->site->getUser($user_id);
                        $user_warehouses_raw = (string) $user_data->warehouse_id;
                        $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
                        $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
                        $warehouse_id   = $primary_location_id;
                    }else{
                        $warehouse_id          = $data->production_unit_id;
                    }
                }
            }
            if ($product_id && $warehouse_id) {
                $qty = (float) $allotQuantityInput;
                if($isChecked === "Checked"){
                    $this->updateWarehouseProductStockFIFO($warehouse_id, $product_id, $qty, false);
                }else{
                    $this->updateWarehouseProductStockFIFO($warehouse_id, $product_id, $qty, true);
                }
            }
        }
        // update stock for bulk order items
        if($bulkAllotCheck){
            if(!empty($itemData)){
                foreach ($itemData as $item) {

                    $order_item_id  = $item['orderItemId'];
                    $allot_quantity_input = (float) $item['allotQuantity'];

                    $order_items =  $this->getOrderItemsForSelectedOrder($procurmentOrderId, $order_item_id);
                    $product_id = $warehouse_id = null;
                    if ($order_items) {
                        foreach($order_items as $data){
                            $product_id            = $data->product_id;
                            $warehouse_id          = $data->production_unit_id;
                            $warehouse_data        = $this->site->getWarehouseBy_ID($warehouse_id);
                            $location_id           = $this->Production_Unit_Model_New->get_location_type_name($warehouse_data);
                            if($location_id){
                                $user_id       = $this->session->userdata('user_id');
                                $user_data     = $this->site->getUser($user_id);
                                $user_warehouses_raw = (string) $user_data->warehouse_id;
                                $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
                                $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
                                $warehouse_id   = $primary_location_id;
                            }else{
                                $warehouse_id          = $data->production_unit_id;
                            }
                        }
                    }
                    if ($product_id && $warehouse_id) {
                        if($bulkAllotCheck === "allChecked"){
                            $this->updateWarehouseProductStockFIFO($warehouse_id, $product_id, $allot_quantity_input, false);
                        }else{
                            $this->updateWarehouseProductStockFIFO($warehouse_id, $product_id, $allot_quantity_input, true);
                        }
                    }
                }
            }
        }
    }
    // update quantity_balance in purchase_items table when click on check box in grid (FIFO: row1 then row2)
    public function updatePurchaseItemsForProductionUnit($procurmentOrderId, $allotQuantityInput, $orderItemId, $isChecked, $bulkAllotCheck, $itemData) {

        // update quantity_balance for specific order item
        if($isChecked){

            $order_items = $this->getOrderItemsForSelectedOrder($procurmentOrderId, $orderItemId);
            $product_id = $warehouse_id = null;
            if ($order_items) {
                foreach($order_items as $data){
                    $product_id            = $data->product_id;
                    $warehouse_id          = $data->production_unit_id;
                    $warehouse_data        = $this->site->getWarehouseBy_ID($warehouse_id);
                    $location_id           = $this->get_location_type_name($warehouse_data);
                    if($location_id){
                        $user_id       = $this->session->userdata('user_id');
                        $user_data     = $this->site->getUser($user_id);
                        $user_warehouses_raw = (string) $user_data->warehouse_id;
                        $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
                        $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
                        $warehouse_id   = $primary_location_id;
                    }else{
                        $warehouse_id = $data->production_unit_id;
                    }
                }
            }
            if ($product_id && $warehouse_id) {
                $this->updatePurchaseItemsFIFO($product_id, $warehouse_id, (float) $allotQuantityInput, ($isChecked === 'Checked'));
            }
        }
        // update quantity_balance for bulk order items
        if($bulkAllotCheck){
            if(!empty($itemData)){
                foreach ($itemData as $item) {

                    $order_item_id       = $item['orderItemId'];
                    $allot_quantity_input = (float) $item['allotQuantity'];

                    $order_items = $this->getOrderItemsForSelectedOrder($procurmentOrderId, $order_item_id);
                    $product_id = $warehouse_id = null;
                    if ($order_items) {
                        foreach($order_items as $data){
                            $product_id            = $data->product_id;
                            $warehouse_id          = $data->production_unit_id;
                            $warehouse_data        = $this->site->getWarehouseBy_ID($warehouse_id);
                            $location_id           = $this->get_location_type_name($warehouse_data);
                            if($location_id){
                                $user_id       = $this->session->userdata('user_id');
                                $user_data     = $this->site->getUser($user_id);
                                $user_warehouses_raw = (string) $user_data->warehouse_id;
                                $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
                                $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
                                $warehouse_id   = $primary_location_id;
                            }else{
                                $warehouse_id = $data->production_unit_id;
                            }
                        }
                    }
                    if ($product_id && $warehouse_id) {
                        $this->updatePurchaseItemsFIFO($product_id, $warehouse_id, $allot_quantity_input, ($bulkAllotCheck === 'allChecked'));
                    }
                }
            }
        }
    }

    // FIFO: allot/Unallot: add back in reverse order (newest first), max quantity per row.
    public function updatePurchaseItemsFIFO($product_id, $warehouse_id, $qty, $isAllot) {
        if ($qty <= 0) return;

        if ($isAllot) {
            $this->db->select('id, quantity_balance');
            $this->db->from('purchase_items');
            $this->db->where('product_id', $product_id);
            $this->db->where('warehouse_id', $warehouse_id);
            $this->db->where('quantity_balance >', 0);
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
            $this->db->order_by('date', 'asc');
            $this->db->order_by('id', 'asc');
            $purchase_items = $this->db->get()->result();
            if (!$purchase_items) return;

            $remaining = $qty;
            foreach ($purchase_items as $pi) {
                if ($remaining <= 0) break;
                $bal = (float) $pi->quantity_balance;
                if ($bal <= 0) continue;
                $take = min($remaining, $bal);
                $new_bal = $bal - $take;
                $this->db->update('purchase_items', array('quantity_balance' => $new_bal), array('id' => $pi->id));
                $remaining -= $take;
            }
        } else {
            // Unallot: add in reverse order (newest first), each row max (quantity - quantity_balance)
            $this->db->select('id, quantity, quantity_balance');
            $this->db->from('purchase_items');
            $this->db->where('product_id', $product_id);
            $this->db->where('warehouse_id', $warehouse_id);
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
            $this->db->order_by('date', 'desc');
            $this->db->order_by('id', 'desc');
            $purchase_items = $this->db->get()->result();
            if (!$purchase_items) return;

            $remaining = $qty;
            foreach ($purchase_items as $pi) {
                if ($remaining <= 0) break;
                $room = (float) $pi->quantity - (float) $pi->quantity_balance;
                if ($room <= 0) continue;
                $add = min($remaining, $room);
                $new_bal = (float) $pi->quantity_balance + $add;
                $this->db->update('purchase_items', array('quantity_balance' => $new_bal), array('id' => $pi->id));
                $remaining -= $add;
            }
        }
        $this->site->syncProductQtyForPU($product_id, $warehouse_id);
    }
    public function get_location_type_name($location_data) {
        if (!$location_data || !is_object($location_data)) return '';
        $location_type = $location_data->location_type;
        $location_id = $location_data->id;
       
        // Check if location type is "Workstation"
        $result = $this->db->select('*')->get_where('sma_location_type', ['id' => $location_type, 'type' => 'Workstation'])->row();
        if($result) {
            // If location type is Workstation, return only the location_id
            return $location_id;
        }
        // Otherwise return blank/empty
        return '';
    }
}
?>