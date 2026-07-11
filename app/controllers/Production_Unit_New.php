<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Production_Unit_New extends MY_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->model('Production_Unit_Model_New');
        $this->load->model('sales_model');
        $this->load->database(); // Database library
        $this->load->library('datatables');
		$this->data['Settings'] = $this->Settings;
    }
    public function working_orders() {

        $this->sma->checkPermissions('Order_Dispatch');
        $locationNames = $this->Production_Unit_Model_New->getLocations();    
        if ($this->input->is_ajax_request()) {
            echo json_encode($locationNames ?: array());
            return;
        }
        $this->data['locationNames'] = is_array($locationNames) ? $locationNames : ($locationNames ?: array());
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('Working_Orders')));
        $meta = array('page_title' => lang('Production_Unit'), 'bc' => $bc);
        $this->page_construct('production_unit/order_dispatch_New', $meta, $this->data);
    }

    // All Orders for Logged in Kitchen <= Production Unit
    public function getAllOrdersForLoggedInProductionUnit(){

        $user_id             = $this->session->userdata('user_id');
        $user_data           = $this->site->getUser($user_id); //get user information
        $user_location_data  = $this->site->getWarehouseBy_ID($user_data->warehouse_id); //get user information
       
        $location_name       = $this->input->get('locationName');
        $sort_order_by_time  = $this->input->get('sortOrderByTime');

        $outlets   = $this->Production_Unit_Model_New->getLocationID($location_name);    
        $outletId  = ($outlets && isset($outlets->id)) ? $outlets->id : null;
        $AllOrders = $this->Production_Unit_Model_New->getAllOrdersForLoggedInProductionUnit($user_id, $outletId, $sort_order_by_time);    
        // Add Production unit name  to each order (for show PU name in header)
        foreach ($AllOrders as $Order) {
            $Order->ProductionUnitName = $user_location_data->name;
        }
        
        $Orders[]  = ['AllOrders' => $AllOrders];
        echo json_encode($Orders);
        return;
    }

    // Get order items for orders
    public function getOrderItemsForSelectedOrder(){

      
        $user_id            = $this->session->userdata('user_id');
        $user_data          = $this->site->getUser($user_id); //get user information
        $location_id        = $user_data->warehouse_id;
        $pds = explode(",", $location_id);
        $location_id = '';
        foreach ($pds as $pd) {
            $location_data = $this->site->getWarehouseBy_ID($pd);
            $location_id = $this->Production_Unit_Model_New->get_location_type_name($location_data);
        }
        $location_id = $location_id ? $location_id : $pds[0];
        $procurmentOrderId  = $this->input->get('procurmentOrderId'); //get order Id
        $location_name      = $this->input->get('locationName');

        if($procurmentOrderId){
            // click on specific order, show that order wise order items
            $order_items = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurmentOrderId); //get order items
            foreach ($order_items as $item) {
                $location_name = $item->location_name; // Accessing the location name
                $userAddress = $this->Production_Unit_Model_New->getLocationID($location_name); 
                $addressLine1 = $userAddress->address_line1; 
                $addressLine2 = $userAddress->address_line2; 
                if (empty($addressLine1) && empty($addressLine2)) {
                    $userAddress = ''; 
                } else {
                    $userAddress = trim("$addressLine1, $addressLine2"); // Concatenate address_line1 and address_line2
                }
            }
        }else{
            // get all orders data
            $AllOrders = $this->Production_Unit_Model_New->getAllOrdersForLoggedInProductionUnit($user_id);    
           
            $filteredOrders = array_filter($AllOrders, function($order) {
                return in_array($order->status, ['Open', 'Locked']);
            });
            $filteredOrders = array_values($filteredOrders);
            $order_id = $filteredOrders[0]->id;

            // show by default first order items data in grid
            $order_items = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($order_id, null, $location_name); //get order items
            
            if($procurmentOrderId =''){
                $order_items = []; 
            }
        }

        // Add location_id to each order_item (for toggle switch)
        foreach ($order_items as $item) {
            $item->user_location_id = $location_id;
            $order_status = $item->order_status;
        }
        $OrderItems[] = ['order_items' => $order_items,'order_status' => $order_status,'userAddress' => $userAddress];
        echo json_encode($OrderItems);
        return;
    }

    // update order data
    public function updateOrder()
    {
        $user_id            = $this->session->userdata('user_id');
        $user_data          = $this->site->getUser($user_id); //get user information
        $location_id        = $user_data->warehouse_id;

        $pds = explode(",", $location_id);
        $location_id = '';
        foreach ($pds as $pd) {
            $location_data = $this->site->getWarehouseBy_ID($pd);
            $location_id = $this->Production_Unit_Model_New->get_location_type_name($location_data);
        }
        $location_id = $location_id ? $location_id : $pds[0];


        $procurmentOrderId  = $this->input->get('procurmentOrderId'); //get order Id      
        $Locked             = $this->input->get('Locked'); //flag for update status when click on lock button
        $orderItemId        = $this->input->get('orderItemId'); //get order item id
        $isChecked          = $this->input->get('checkbox') ;//flag for update status, allot qty adn stock qty when click on checkbox
        $allotQuantityInput = $this->input->get('allotQuantityInput');
        $bulkAllotCheck     = $this->input->get('bulkAllotCheck'); // flag for bulk allot 
        $itemData           = $this->input->get('itemData'); // order item id and allot qty for bulk
        $completeOrderFlag  = $this->input->get('completeOrderFlag'); // flag for complete order button
        $deliveryDateTime   = $this->input->get('deliveryDateTime');
        
        $this->Production_Unit_Model_New->updateOrder($procurmentOrderId, $Locked, $isChecked, $allotQuantityInput, $orderItemId, $bulkAllotCheck, $itemData, $completeOrderFlag, $user_id,$deliveryDateTime); //update status after click on locked button and also click on complete order button

        // get update order and order item status to show on view
        $order_items = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurmentOrderId); //get order items
        
        // Create sales when we dispatch order from production unit
        $procurement_orders    = $this->Production_Unit_Model_New->getProcurmentOrderById($procurmentOrderId); 
        $procurement_order_items    = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurmentOrderId); 

        if ($procurement_orders && $procurement_orders->status == 'Completed' && $this->Settings->set_order_dispatch == '0') {
           
            // for checking biller_id for production unit login
            $user_id                    = $this->session->userdata('user_id');
            $user_data                  = $this->site->getUser($user_id); 
            $warehouse_id               = $user_data->warehouse_id;
            $warehouse                  = $this->site->getWarehouseBy_ID($warehouse_id);
            $production_unit_biller_id  = $warehouse->primary_biller_id;
    
            // run only if comma exists(check one of the location is workstation or not => always priority to primary location)
            if(strpos($warehouse_id, ',') !== false) {
                $user_warehouses_raw = (string) $user_data->warehouse_id;
                $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
                $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
                // $warehouse_id   = $primary_location_id;
                $warehouse                  = $this->site->getWarehouseBy_ID($primary_location_id);
                $production_unit_biller_id  = $warehouse->primary_biller_id;
            }else{
                $warehouse_id = $user_data->warehouse_id;
                $warehouse                  = $this->site->getWarehouseBy_ID($warehouse_id);
                $production_unit_biller_id  = $warehouse->primary_biller_id;
            }

            // for checking biller_id for outlet login
            $warehouse                  = $this->site->getWarehouseBy_ID($procurement_orders->location_id);
            $outlet_biller_id           = $warehouse->primary_biller_id;

            // old logic with checking only biller_id
            // if($production_unit_biller_id !== $outlet_biller_id){
            //     $this->sma->CreateSales($procurement_orders, $procurement_order_items); // Create sales when we complete order from production unit
            // }
            // if($production_unit_biller_id == $outlet_biller_id){
            //     $this->sma->CreateTransfer($procurement_orders->id); // Create Transfer when we complete order from production unit
            // }
            
            // Outlet biller details
            $outlet_biller_details      = $this->site->getCompanyByID($outlet_biller_id);
            $outlet_biller_gstno        = $outlet_biller_details->gstn_no;
            $outletGST                  = trim($outlet_biller_gstno);
            $outletBiller               = $outlet_biller_details->company;

            // Production unit biller details
            $PU_biller_details          = $this->site->getCompanyByID($production_unit_biller_id);
            $PU_biller_gstno            = $PU_biller_details->gstn_no;
            $puGST                      = trim($PU_biller_gstno);
            $puBiller                   = $PU_biller_details->company;

            // Normalize for safe comparison
            $outletGST  = strtoupper($outletGST);
            $puGST      = strtoupper($puGST);
            $outletBiller = strtolower($outletBiller);
            $puBiller     = strtolower($puBiller);
           
            // GSTN has FIRST priority
            if (!empty($puGST) && !empty($outletGST)) {

                // Case 1: GSTNs are different → SALE
                if ($puGST !== $outletGST) {
                    $this->sma->CreateSales($procurement_orders, $procurement_order_items);
                } 
                // Case 2: GSTNs are same → TRANSFER
                else {
                    $this->sma->CreateTransfer($procurement_orders->id);
                }

            } 
            //  GSTN missing (one or both) → fallback to company comparison
            else {
                // Company names different → SALE
                if (!empty($puBiller) && !empty($outletBiller) && $puBiller !== $outletBiller) {
                    $this->sma->CreateSales($procurement_orders, $procurement_order_items);
                } 
                // Company names same OR missing → TRANSFER
                else {
                    $this->sma->CreateTransfer($procurement_orders->id);
                }
            }
        }

        foreach($order_items as $value){
            $order_status = $value->order_status;
            $value->user_location_id = $location_id;

        }
        $updatedOrderDetails[] = ['order_items' => $order_items,'order_status' => $order_status];
        echo json_encode($updatedOrderDetails);
        return;
        
    }    
    // get completed orders
    public function getCompleteOrdersForLoggedInProductionUnit(){

        $user_id             = $this->session->userdata('user_id');
        $user_data           = $this->site->getUser($user_id); //get user information
        $user_location_data  = $this->site->getWarehouseBy_ID($user_data->warehouse_id); //get user information
       
        $location_name       = $this->input->get('locationName');
        $sort_order_by_time  = $this->input->get('sortOrderByTime');

        $outlets   = $this->Production_Unit_Model_New->getLocationID($location_name);    
        $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '0' : '1';

        $AllOrders = $this->Production_Unit_Model_New->getCompleteOrdersForLoggedInProductionUnit($user_id, $outlets->id, $sort_order_by_time, $order_dispatch);    
        // Add Production unit name  to each order (for show PU name in header)
        foreach ($AllOrders as $Order) {
            $Order->ProductionUnitName = $user_location_data->name;
        }
        
        $Orders[]  = ['AllOrders' => $AllOrders];
        echo json_encode($Orders);
        return;
    }
}
?>