<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Production_Unit extends MY_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('production_unit_model');
        $this->load->model('Production_Unit_Model_New');
        $this->load->model('Products_model');
        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->database(); // Database library
        $this->load->library('datatables');
        $this->load->library('upload');
        $this->digital_upload_path = 'files/'.$this->Customer_assets;
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/production_unit';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
        $this->data['Settings'] = $this->Settings;

    }

    function index($warehouse_id = NULL) {
        // $this->sma->checkPermissions();

        $this->data['locationNames'] = $this->production_unit_model->getLocations();      
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('Working_Orders')));
        $meta = array('page_title' => lang('Production_Unit'), 'bc' => $bc);
        $this->page_construct('production_unit/order_dispatch', $meta, $this->data);
    }
    
    public function getProcurementRefrenceNo($warehouse_id = NULL) {
        
        $locationName       = $_GET['location']; 
        $status             = $_GET['status'];
        $procurmentRefNo    = $_GET['procurmentRefNo'];
        $itemId             = $_GET['itemId']; 
        $quantity           = $_GET['quantity'];
        $itemData           = $_GET['itemData'];
        $checkbox           = $_GET['checkbox'];
        $Filter             = $_GET['Filter'];
        $Complete_unit_order = $_GET['Complete_unit_order'];
        $deliveryDateTime   = $_GET['deliveryDateTime'];
        $AllItems           = $_GET['AllItems']; // View full order 
       
        $user_id       = $this->session->userdata('user_id');
        $location_data = $this->site->getUser($user_id);
        $location_id   = ($AllItems == false) ? $location_data->warehouse_id : '';

        //update planned_delivery_datetime after set delivery time for orders
        if($deliveryDateTime){
            $datetime = [
                'id' => $procurmentRefNo,
                'planned_delivery_datetime' => $deliveryDateTime
            ];
            $this->production_unit_model->updateOrderItemStock($quantity, $itemId, $itemData, $procurmentDetails, $checkbox, $Complete_unit_order, $datetime); //update status after click on locked button and also click on complete order button
        } 
        if($checkbox == 1 || $checkbox == 0){
            $this->production_unit_model->updateOrderItemStock($quantity, $itemId, $itemData, Null, $checkbox); // update status, allot qty and stock after check and uncheck on checkbox
        }
        $refrenceNo = $this->production_unit_model->getProcurementRefrenceNo($locationName, $Filter);
        $procurmentId = [ ];
        foreach ($refrenceNo as  $value) {
            $procurmentId[] = $value->itemId;
        } 
        
        $procurmentsDetail = ''; 
        if ($status || $procurmentRefNo) {

            // Use warehouses_products as the single source of stock, and derive open_order_quantity from procurement items
            $this->db->select("
                poi.id as itemId,
                poi.procurement_orders_id,
                poi.product_id,
                poi.product_name, 
                poi.product_code,
                poi.order_quantity,
                poi.allot_quantity,
                po.procurement_order_ref_no,
                po.location_name,
                po.note,
                po.status as order_status,
                poi.item_status,
                wp.quantity as stock_quantity,
                (
                    SELECT COALESCE(SUM(poi2.order_quantity - COALESCE(poi2.allot_quantity, 0)), 0)
                    FROM sma_procurement_order_items poi2
                    WHERE poi2.product_id = poi.product_id
                      AND poi2.production_unit_id = poi.production_unit_id
                      AND poi2.item_status IN ('Open','Locked','Committed','partially_completed')
                ) AS open_order_quantity,
                poi.production_unit_id,
                poi.production_unit_name
            ", false);
            $this->db->from('sma_procurement_order_items poi');
            $this->db->join('products ', 'products.id = poi.product_id', 'left');
            // Aggregate warehouses_products to one row per (product_id, warehouse_id) to avoid duplicates
            $this->db->join('(
                SELECT product_id, warehouse_id, SUM(quantity) AS quantity
                FROM sma_warehouses_products
                GROUP BY product_id, warehouse_id
            ) wp', 'wp.product_id = poi.product_id AND wp.warehouse_id = poi.production_unit_id', 'left');
            $this->db->join('sma_procurement_orders po', 'po.id = poi.procurement_orders_id');
            // if ($status) {
            //     $this->db->where('poi.item_status', $status);
            // }
            if ($status == 'Completed') {
                // Condition for Completed Items tab 
                $this->db->where('poi.order_quantity = poi.allot_quantity', null, false);
            } elseif ($status == 'Partially Completed') {
                // Condition for Partially Completed Items tab 
                $this->db->where('poi.order_quantity > poi.allot_quantity AND poi.allot_quantity > 0');
            } elseif ($status == 'Pending') {
                // Condition for Pending Items tab 
                $this->db->where('(poi.allot_quantity = 0 OR poi.allot_quantity IS NULL)');
            }
            if ($procurmentRefNo) {
                $this->db->where('poi.procurement_orders_id', $procurmentRefNo);
            }
            if($location_id) {
                $this->db->where('poi.production_unit_id', $location_id); // show prodution_unit wise order items data
            }
            $this->db->order_by("poi.id", "DESC");
            $procurmentsDetail = $this->db->get()->result();
            $procurmentDetails = $procurmentsDetail;
            // $this->production_unit_model->updateOrderItemStock($quantity = null, $itemId = null, $itemData = null, $procurmentDetails, null, $Complete_unit_order); //update status after click on locked button and also click on complete order button
            $this->production_unit_model->updateOrderItemStock($quantity = null, $itemId = null, $itemData = null, $procurmentDetails, null, $Complete_unit_order, null, $status); //update status after click on locked button and also click on complete order button

        } else {
            $procurmentDetails = $this->production_unit_model->getProcurementdetails($procurmentId, $status = null, null, $location_id);
            foreach ($procurmentDetails as $item) {
                if ($item->item_status == "completed") {
                    $item->open_order_quantity = 0;
                }
            }
        }        
        $pr[] = ['refrenceNo' => $refrenceNo, 'procurmentDetails' => $procurmentDetails, 'location_data' => $location_data];
        echo json_encode($pr);
        return;

    }
    //Procurment Orders 
    public function procurementOrders() {
        $this->sma->checkPermissions('Procurement_Orders');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses(); 
        } else {
            $location_data = $this->site->getWarehouseByIDs($location_id);
        }
        // Both getAllWarehouses and getWarehouseByIDs can return FALSE when no data
        if (!$location_data || !is_array($location_data)) {
            $location_data = array();
        }
        $locationName  = '';
        foreach ($location_data as $location) {
            $locationName = is_object($location) ? $location->name : '';
            break;
        }
            $this->data['error']       = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories']  = $this->production_unit_model->getProductCategoriesList();     
            $this->data['outletName']  = $location_data;

            $bc   = array(array('link' => base_url(), 'page' => lang('Procurement_Orders')), array('link' => '#', 'page' => lang('Place_Order')));
            $meta = array('page_title' => lang('Place_Order'), 'bc' => $bc);

            $this->page_construct('production_unit/procurement_order', $meta, $this->data);      
    }
    
    public function GetProductsbyCategoriesID() {   

        $categoriesId    = $this->input->get('categoriesId');
        $subcategoryId   = $this->input->get('subcategoryId');
        $search          = $this->input->get('search');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        $location        =  $this->production_unit_model->getWarehouseByID($location_id);
        
        $productsByCategories = '';
        if ($categoriesId || $subcategoryId || $search) {
            // Pass current outlet warehouse id so query can use its price_group_id
            $productsByCategories  = $this->production_unit_model->getProductByCategories($categoriesId,$subcategoryId, $search, $location_id);    
        }
        if ($productsByCategories) {
            $i=0;
            foreach ($productsByCategories as $row) {
                
                $unitData = $this->production_unit_model->getUnitById($row->unit);
                $row->unit_name      = $unitData->name;
                $row->unit_price        = $row->price;  
                $row->org_price         = $row->price;
                $row->base_unit_price   = $row->price;
                $row->unit_weight       = $row->weight;
                $row->quantity          = 1;

                if (($location->price_group_id)) {                 
                    // if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $location->price_group_id)) {
                        $row->unit_price = $row->c_price;
                    // }
                }
                if ($row->unit_price == 0){
                    $row->unit_price = $row->org_price;
                }
                $tax_rate_id = $this->site->CalculateCategoryLevelTaxRate($row->id, $row->unit_price);
                if ($tax_rate_id && ($tax_details = $this->site->getTaxRateByID($tax_rate_id))) {
                    $row->tax_rate     = (float) $tax_details->rate; 
                    $row->tax_rate_id  = (int) $tax_rate_id;        
                } else {
                    $row->tax_rate    = 0;
                    $row->tax_rate_id = null;
                }
                $tax_rate = $row->tax_rate;
            }
            $pr[] = ['productsByCat' => $productsByCategories,  'row' => $row, 'tax_rate' => $tax_rate];
        }else {
            $pr[] = ['productsByCat' => $productsByCategories];

        }
        echo json_encode($pr);
        return;
    }
      
    public function PlaceProcurementOrder(){

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        if ($orders = $this->input->get('orders')) {
            // Debugging: Output orders received
           
            if (!empty($orders)) {
                $Items = array();
                $i = 0;
                $rows = "";
                $note = isset($orders[0]['note']) ? $orders[0]['note'] : '';
                $outletNames = isset($orders[0]['outletNames']) ? $orders[0]['outletNames'] : $locationName;
                $outletData = $this->Production_Unit_Model_New->getLocationID($outletNames);
                
                $requested_delivery_date = isset($orders[0]['requested_delivery_date']) ? $orders[0]['requested_delivery_date'] : '';
                $cgst = $sgst = $igst = 0;
                
                // Determine if it's interstate for GST calculation
                $interStateTax = !empty($location_data->state_code) ? true : false;
        
                foreach ($orders as $order) {
                    // Fetch product details
                    $rows = $this->production_unit_model->getProductDetailsByName($order['product']);
                    // Fetch production unit details for the product
                    $product_location = $this->production_unit_model->getProductionUnitDetailsForProduct($rows->id);
                    // Get warehouse details by location ID
                    $location  = $this->site->getWarehouseBy_ID($product_location->location_id);
        
                    // Process order details
                    $subtotal  = str_replace(['Rs.', ','], '', $order['sub_total']);
                    $tax       = str_replace(['Rs.', ','], '', $order['tax']);
                    $net_price = str_replace(['Rs.', ','], '', $order['net_price']);
        
                    // Calculate product price
                    $order_quantity = $order['0'];
                    $product_price = $this->sma->formatDecimal(($subtotal / $order_quantity), 2);
                    $item_net_price = $product_price;
        
                    $tax_rate_id = $this->site->CalculateCategoryLevelTaxRate($rows->id, $product_price);
                    $rows->tax_rate = $tax_rate_id;
                 
                    // Calculate tax for the product
                    $calculated = $this->site->calculateTax($rows, $product_price, $order_quantity, $interStateTax);
                    $product_tax += $calculated['pr_item_tax'];
    
                    // Prepare item data
                    $Items[$i] = array(
                        'product_id'           => $rows->id,
                        'product_code'         => $rows->code,
                        'product_name'         => $order['product'],
                        'order_quantity'       => $order['0'],
                        'item_status'          => 'Open',
                        'unit_quantity'        => $rows->quantity,
                        'unit_price'           => $product_price,
                        'unit_cost'            => $rows->cost,
                        'product_unit_id'      => $rows->unit,
                        'product_unit_code'    => $order['unit'],
                        'tax_rate_id'          => $rows->tax_rate,
                        'tax'                  => $calculated['tax'],
                        'item_tax'             => $calculated['pr_item_tax'],
                        'net_unit_cost'        => $rows->cost,
                        'net_price'            => $net_price,
                        'subtotal'             => $subtotal,
                        'production_unit_id'   => $location->id,
                        'production_unit_name' => $location->name,
                        'production_unit_code' => $location->code
                    );
        
                    // Accumulate tax values
                    $cgst += $calculated['item_cgst'];
                    $sgst += $calculated['item_sgst'];
                    $igst += $calculated['item_igst'];
                    // Calculate total cost
                    $total += $this->sma->formatDecimal(($product_price * $order_quantity), 2);
                    $i++;
                }
        
                // Format tax and calculate grand total
                $total_tax = $this->sma->formatDecimal(($product_tax), 2);
                $shipping_amount = $this->sma->formatDecimal('');
                $grand_total = $this->sma->formatDecimal(($total  + $shipping_amount), 2);
        
                // // Determine procurement order reference number
                // $porder = $this->production_unit_model->getOrder($outletNames);
                // $outlate_name = substr($porder->procurement_order_ref_no, 0, strpos($porder->procurement_order_ref_no, '/'));
                // $refrence_No = substr($porder->procurement_order_ref_no, strrpos($porder->procurement_order_ref_no, '/') + 1);
        
                // if ($outletNames === $outlate_name) {
                //     $numeric_part = intval($refrence_No);
                //     $new_numeric_part = $numeric_part + 1;
                //     $incremented_number = sprintf('%04d', $new_numeric_part); // Format as 4-digit number
                // } else {
                //     $incremented_number = '0001';
                // }
                // $order_no = $outletNames . '/' . $incremented_number;
                
                // Determine procurement order reference number outlet-wise.
                $order_no = $this->generateProcurementOrderReferenceNo($outletData, $outletNames);
        
                // Prepare data for insertion
                $orderItems = count($Items);
                $data = array(
                    'procurement_order_ref_no' => $order_no,
                    'location_code'            => $outletData->code,
                    // 'location_id'              => $location_id,
                    'location_id'              => $outletData->id,

                    'location_name'            => $outletNames,
                    'location_state_code' => 'MH',
                    'created_by'               => $user_id,
                    'requested_delivery_date'  => $requested_delivery_date,
                    'note'                     => $note,
                    'status'                   => 'Open',
                    'total'                    => $orderItems,
                    'total_tax'                => $total_tax,
                    'shipping_amount'          => '',
                    'grand_total'              => $grand_total
                );
        
                // Insert data into procurement orders table
                if ($this->production_unit_model->PlaceProcurementOrder($data, $Items)) {
                    $this->load->library('session');
                    $affected_rows = $this->db->affected_rows();
                    $this->session->set_userdata('affected_rows_count', $affected_rows);
                    $response = array('status' => 'success', 'message' => 'Order Placed Successfully.');
                } else {
                    $response = array('status' => 'error', 'message' => 'Failed to insert data.');
                }
                echo json_encode($response);
                return;
            } else {
                // If no items found in orders
                $response = array('status' => 'error', 'message' => 'Item Data Not Found.');
                echo json_encode($response);
            }
        } else {
            // If no orders received
            $this->session->set_flashdata('error', validation_errors());
            redirect("production_Unit/procurementOrders");
        }
    
    }

    public function generateProcurementOrderReferenceNo($outletData, $outletNames) {
        $incremented_number = '0001';
        $lastOutletOrder = null;

        if (!empty($outletData) && !empty($outletData->id)) {
            $lastOutletOrder = $this->db->select('procurement_order_ref_no')
                ->from('sma_procurement_orders')
                ->where('location_id', (int) $outletData->id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
        }

        if (!empty($lastOutletOrder) && !empty($lastOutletOrder->procurement_order_ref_no)) {
            $parts = explode('/', $lastOutletOrder->procurement_order_ref_no);
            $lastCounter = end($parts);
            if (is_numeric($lastCounter)) {
                $incremented_number = sprintf('%04d', ((int) $lastCounter) + 1);
            }
        }

        return $outletNames . '/' . $incremented_number;
    }

    public function checkProductsKitchenMapping() {
        $product_ids = $this->input->get('product_ids');
        $product_names = $this->input->get('product_names');
        
        if (!empty($product_ids)) {
            $unmapped = $this->production_unit_model->getUnmappedProducts($product_ids);
        } elseif (!empty($product_names)) {
            $unmapped = $this->production_unit_model->getUnmappedProductsByNames($product_names);
        } else {
            echo json_encode([]);
            return;
        }
        
        echo json_encode($unmapped);
    }
   
    public function getProcurementOrderList() {     

        // Example PHP controller endpoint
        $status          = $this->input->get('orderStatus');
        $order_id        = $this->input->get('order_id');

        if ($status) {
            $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '1' : '0';
            $getOrderData  = $this->production_unit_model->getProcurmentOrders($status, $order_dispatch); 
            $pr[] = ['getOrderData' => $getOrderData];

        }else {
            $pr = "Please Select Status";
        }
        echo json_encode($pr);
        return;
    }
     
    public function getProcurementOrderItemsListByOrderStatus() {     

        $status          = $this->input->get('orderStatus');
        $order_id        = $this->input->get('order_id');

        if ($order_id) {
            $item_data     = $this->production_unit_model->getOrdersByItems($order_id,$status );
            $pr[] = ['item_data' => $item_data];
        }else {
            $pr = "Please Select Status";
        }
        echo json_encode($pr);
        return;
    }

    public function updateProcurementOrder(){
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        $location_data = $this->site->getWarehouseByIDs($location_id); //get
        $currentDateTime = date('Y-m-d H:i:s');
        $order_id = '';
        $locationName = '';
        foreach ($location_data as $location) {
        $locationName = $location->name;
        }
    
        if ($orders = $this->input->get('orders')) {
          
            if (!empty($orders)) {
                $Items = array();
                $i = 0;
                $rows = "";
                $status = "";
                $note                    = isset($orders[0]['note']) ? $orders[0]['note'] : '';
                $requested_delivery_date = isset($orders[0]['requested_delivery_date']) ? $orders[0]['requested_delivery_date'] : '';
                $outletNames = isset($orders[0]['outletNames']) ? $orders[0]['outletNames'] : $locationName;
                $cgst = $sgst = $igst = 0;
                $order_id = $orders[0]['order_id'];
                $interStateTax = !empty($location_data->state_code) ? true : false;  //for gst calculation
                $selectedHorizonatlTabValue = '';
                $received_qty = '';
                foreach ($orders as $order) { 
                   
                    $received_qty = $order['received_qty'];
                    $rows             = $this->production_unit_model->getProductDetailsByName($order['product']);
                    $orderItemDetails = $this->production_unit_model->getOrderByItems($order_id,$rows->id);
                    $product_location = $this->production_unit_model->getProductionUnitDetailsForProduct($rows->id);  //productionunit_products data
                    $location         = $this->site->getWarehouseBy_ID($product_location->location_id); 
                    $subtotal         = str_replace(['Rs.', ','], '', $order['sub_total']);                
                    $tax              = str_replace(['Rs.', ','], '', $order['tax']);                
                    $net_price        = str_replace(['Rs.', ','], '', $order['net_price']);           
                    $adjustment_price        = str_replace(['Rs.', ','], '', $order['adjustments']);           
                    // $subtotal         = $this->sma->formatDecimal(($order['sub_total']),2);
                    $order_quantity   = $order['0'];
                    $product_price    = $this->sma->formatDecimal(($subtotal/$order_quantity),2);
                    $item_net_price   = $product_price;

                    $tax_rate_id = $this->site->CalculateCategoryLevelTaxRate($rows->id, $product_price);
                    $rows->tax_rate = $tax_rate_id;

                    $calculated       = $this->site->calculateTax($rows, $product_price, $order_quantity, $interStateTax); // Tax calculation
                    $product_tax += $calculated['pr_item_tax'];
                    $selectedHorizonatlTabValue = $order['selectedHorizonatlTabValue'];
                
                    if ($selectedHorizonatlTabValue == 'reject') {
                        $selectedHorizonatlTabValue = ($selectedHorizonatlTabValue === 'received_order') ? $order['selectedHorizonatlTabValue'] : 'reject';
                    }else {
                        $selectedHorizonatlTabValue = ($selectedHorizonatlTabValue === 'received_order') ? $order['selectedHorizonatlTabValue'] : 'update_order';
                    }
                  
                    $Items[$i]   = array(
                        'product_id'            => $rows->id,
                        'product_code'          => $rows->code,
                        'product_name'          => $order['product'],
                        // 'batch_number'       => '',
                        // 'manufacturing_date' => '',
                        // 'expiry_date'        => '',
                        'order_quantity'        => $order['0'],
                        // 'received_quantity'  => '',
                        'item_status'           =>'Open',
                        'unit_quantity'         => $rows->quantity,
                        'unit_price'            => $product_price,
                        'unit_cost'             => $rows->cost,
                        'product_unit_id'       => $rows->unit,
                        'product_unit_code'     => $order['unit'],
                        'tax_rate_id'           => $rows->tax_rate,
                        'tax'                   => $calculated['tax'],
                        'item_tax'              => $calculated['pr_item_tax'],
                        'net_unit_cost'         => $rows->cost,
                        'net_price'             => $net_price,
                        'subtotal'              => $calculated['subtotal'],
                        'production_unit_id'    => $location->id,
                        'production_unit_name'  => $location->name,
                        'production_unit_code'  => $location->code    
                    );  
                    if ($selectedHorizonatlTabValue === 'received_order') { 
                        $Items[$i]   = array(
                            'id'                      => $orderItemDetails->order_id,           
                            'received_quantity'       => $received_qty,
                            'received_by'             => $user_id ,          
                            'actual_delivery_date'    => $currentDateTime ,
                            'item_status'             =>'Received', 
                            'product_id'              => $rows->id,
                            'adjustment_price'        => $adjustment_price

                        );
                    }
                    if ($selectedHorizonatlTabValue === 'reject') { 
                        $Items[$i]   = array(
                            'id'                      => $orderItemDetails->order_id,           
                            'received_quantity'       => $received_qty,
                            'received_by'             => $user_id ,          
                            'actual_delivery_date'    => $currentDateTime ,
                            'item_status'             =>'Rejected',
                            'product_id'              => $rows->id,
                            'adjustment_price'        => $adjustment_price
                        );
                    }
                    $cgst += $calculated['item_cgst'];
                    $sgst += $calculated['item_sgst'];
                    $igst += $calculated['item_igst']; 
                    $igst += $calculated['item_igst'];
                    $adjustment_price += $this->sma->formatDecimal(($adjustment_price),2);
                    $total+= $this->sma->formatDecimal(($product_price * $order_quantity), 2); 
                    $i++;  
                
                }  
                
                $adjustment_price       = $adjustment_price;
                $total_tax       = $this->sma->formatDecimal(($product_tax),2);
                $shipping_amount = $this->sma->formatDecimal('');
                $grand_total     = $this->sma->formatDecimal(($total  + $shipping_amount),2);

                // procurement_order_ref_no logic
                $porder = $this->production_unit_model->getOrder($location_data->code); 
                $getOrderRefrenceNoById = $this->production_unit_model->getOrderRefrenceNoById($order_id); 
                $outlate_name = substr($porder->procurement_order_ref_no, 0, strpos($porder->procurement_order_ref_no, '/'));
                $refrence_No = substr($porder->procurement_order_ref_no, strrpos($porder->procurement_order_ref_no, '/') + 1);
                
                if ($locationName === $outlate_name) {
                    $numeric_part = intval($refrence_No);
                    $new_numeric_part = $numeric_part + 1;
                    $incremented_number = sprintf('%04d', $new_numeric_part); // Format as 4-digit number
                } else {
                    $incremented_number = '0001';
                }
                $order_no = $locationName . '/' . $incremented_number;
                // procurement_order_ref_no logic end
                $orderItems      = count($Items); 
                $outletData = $this->Production_Unit_Model_New->getLocationID($outletNames);

                $data = array(
                    'procurement_order_ref_no' => $getOrderRefrenceNoById->procurement_order_ref_no,
                    'location_code'            => $outletData->code,
                    // 'location_id'              => $location_id,
                    'location_id'              => $outletData->id,
                    'location_name'            => $outlate_name,
                    'location_state_code'      => 'MH',
                    'created_by'               => $user_id,
                    'requested_delivery_date'  => $requested_delivery_date,
                    'note'                     => $note,
                    'status'                   => 'Open',
                    'total'                    => $orderItems,
                    'total_tax'                => $total_tax,
                    'shipping_amount'          => '',
                    'grand_total'              => $grand_total
                    // 'actual_order_amount'      => '',
                    // 'actual_delivery_date'     => '',
                );
           
                if ($selectedHorizonatlTabValue === 'received_order') { 
                    $data   = array(
                        // 'received_quantity'       => $received_qty,
                        'status'                   => 'Received',
                        'received_by'             =>   $user_id ,          
                        'location_id'             => $orderItemDetails->location_id,
                        'actual_delivery_date'    =>   $currentDateTime,
                        'adjustment_price'        => $adjustment_price          
                    );
                }
                if ($selectedHorizonatlTabValue === 'reject') { 
                    $data   = array(
                        // 'received_quantity'       => $received_qty,
                        'status'                   => 'Rejected',
                        'received_by'             =>   $user_id ,          
                        'location_id'             => $orderItemDetails->location_id,
                        'actual_delivery_date'    =>   $currentDateTime, 
                        'adjustment_price'        => $adjustment_price         
                    );
                }
                if ($selectedHorizonatlTabValue === 'received_order') { 
                    // fetch orders and order items data
                    $procurement_orders         = $this->production_unit_model->getProcurementOrderData($getOrderRefrenceNoById->procurement_order_ref_no); 
                    $procurement_order_id       = $procurement_orders->id;
                    $procurement_order_items    = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurement_order_id); 

                    // Enrich orders with product_id for UpdateTransfer matching 
                    for ($i = 0; $i < count($orders); $i++) {
                        $pr = $this->production_unit_model->getProductDetailsByName(isset($orders[$i]['product']) ? $orders[$i]['product'] : '');
                        if ($pr) {
                            $orders[$i]['product_id'] = $pr->id;
                        }
                    }

                    // for checking biller_id for outlet unit login
                    $warehouse_for_outlet_login  = $this->site->getWarehouseBy_ID($procurement_orders->location_id);
                    $outlet_biller_id            = $warehouse_for_outlet_login->primary_biller_id;
                    
                    // for checking biller_id for production unit login
                    $alloted_by = $procurement_order_items[0]->alloted_by; // Get the user ID of the user responsible for producing this product
                    $user_data     = $this->site->getUser($alloted_by);  
                    $warehouse_id   = $user_data->warehouse_id;

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

                    // // for checking biller_id for production unit login
                    // $warehouse_for_PU           = $this->site->getWarehouseBy_ID($product_location->location_id);
                    // $production_unit_biller_id  = $warehouse_for_PU->primary_biller_id;
                    
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

                        // Case 1: GSTNs are different → PURCHASE
                        if ($puGST !== $outletGST) {
                            // fetch sale data by procurement order reference number
                            $sales_data    = $this->Production_Unit_Model_New->getSalesDataByRefNum($getOrderRefrenceNoById->procurement_order_ref_no); 
                            $this->CreatePurchase($sales_data->id, $procurement_orders->location_id, $Items); 
                        } 
                        // Case 2: GSTNs are same → TRANSFER
                        else {
                            // fetch reference wise transfer data
                            $transfer_data = $this->production_unit_model->get_transfer($procurement_orders->procurement_order_ref_no);
                            $transfer_Items = $this->production_unit_model->get_transfer_items($transfer_data->id);
                            $this->sma->UpdateTransfer($transfer_data, $transfer_Items, $orders, $procurement_orders); 
                        }

                    } 
                    //  GSTN missing (one or both) → fallback to company comparison
                    else {

                        // Company names different → PURCHASE
                        if (!empty($puBiller) && !empty($outletBiller) && $puBiller !== $outletBiller) {
                            // fetch sale data by procurement order reference number
                            $sales_data    = $this->Production_Unit_Model_New->getSalesDataByRefNum($getOrderRefrenceNoById->procurement_order_ref_no); 
                            $this->CreatePurchase($sales_data->id, $procurement_orders->location_id, $Items); 
                        } 
                        // Company names same OR missing → TRANSFER
                        else {
                            // fetch reference wise transfer data
                            $transfer_data = $this->production_unit_model->get_transfer($procurement_orders->procurement_order_ref_no);
                            $transfer_Items = $this->production_unit_model->get_transfer_items($transfer_data->id);
                            $this->sma->UpdateTransfer($transfer_data, $transfer_Items, $orders, $procurement_orders); 
                        }
                    }
                    // old logic with checking only biller_id
                    // if($production_unit_biller_id !== $outlet_biller_id){
                    //     // fetch sale data by procurement order reference number
                    //     $sales_data    = $this->Production_Unit_Model_New->getSalesDataByRefNum($getOrderRefrenceNoById->procurement_order_ref_no); 
                    //     $this->CreatePurchase($sales_data->id, $procurement_orders->location_id, $Items); // Create sales when we dispatch order from production unit
                    // }
                    // if($production_unit_biller_id == $outlet_biller_id){
                    //     // fetch reference wise transfer data
                    //     $transfer_data = $this->production_unit_model->get_transfer($procurement_orders->procurement_order_ref_no);
                    //     $transfer_Items = $this->production_unit_model->get_transfer_items($transfer_data->id);
                    //     $this->sma->UpdateTransfer($transfer_data, $transfer_Items, $orders); // update transfer when we dispatch order from production unit
                    // }
                }

                if ($this->production_unit_model->updateProcurementOrder($order_id ,$data , $Items, $selectedHorizonatlTabValue)) {
                        $response = array('status' => 'success', 'message' => 'Data inserted successfully.');
                }
                else {
                    $response = array('status' => 'error', 'message' => 'Failed to insert data.');
                }
                echo json_encode($response);
                return;
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect("production_Unit/procurementOrders");
                
            }
        }
        else {
            // Send error response if 'orders' parameter is missing
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories']  = $this->production_unit_model->getProdCategories();     
            $this->data['user_name']  = $user_data->first_name;
            $bc = array(array('link' => base_url(), 'page' => lang('Procurement_Orders')), array('link' => '#', 'page' => lang('Place_Order')));
            $meta = array('page_title' => lang('Production_Unit'), 'bc' => $bc);
            $this->page_construct('production_unit/procurement_order', $meta, $this->data);
        }
    }
    #=========================================== Production Manager Dashboard Screen  ============================================
   
    // Production Manager Dashboard
    public function manager_dashboard() {
        $this->sma->checkPermissions('production_manager_dashboard');
        $user_id               = $this->session->userdata('user_id');   
        $user_data             = $this->site->getUser($user_id); 
        $location_id           = $user_data->warehouse_id;
        $location_data         = $this->site->getWarehouseByIDs($location_id); 
        $productionUnitName    = $this->input->get('productionUnitName');
        
        $default_location      = reset($location_data); // Get the first location
        $default_location_name = $default_location->name; 
       
        $productionUnit  = '';
        foreach ($location_data as $location) {
            $productionUnit[] = $location->name;
            $productionUnitId = $location->id;

        }
      
        if($product_id = $this->input->get('productId')){

            if(!empty($product_id)){
                // Production Dashboard :reset stock for this product in current user's warehouse
                $this->production_unit_model->resetProductStock($product_id, $location_id);

                // After resetting, return updated products for the selected production unit (AJAX caller expects JSON)
                if ($productionUnitName) {
                    $products = $this->production_unit_model->getproductDetailsByLocation($productionUnitName);
                } else {
                    $products = $this->production_unit_model->getproductDetailsByLocation($default_location->name);
                }
                echo json_encode($products);
                return;
            }else {
                $this->session->set_flashdata('error', validation_errors());
                redirect("production_Unit/manager_dashboard");
            }

        }else{

            // Fetch products based on the default location or selected production unit
            if ($productionUnitName) {
                $products = $this->production_unit_model->getproductDetailsByLocation($productionUnitName);
            } else {
                $products = $this->production_unit_model->getproductDetailsByLocation($default_location->name);
            }
            if ($this->input->is_ajax_request()) {
                echo json_encode($products);
                return;
            }
            $this->data['products'] = $products;
            // $this->data['products']  = $this->production_unit_model->getproductDetails();  
            $this->data['productionUnitName']  = $productionUnit; 
            $this->data['productionUnitId']  = $productionUnitId; 
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('Production Unit')), array('link' => '#', 'page' => lang('Production Dashboard')));
            $meta = array('page_title' => lang('Production Dashboard'), 'bc' => $bc);
            $this->page_construct('production_unit/manager_dashboard', $meta, $this->data);
        }
    }
    public function getProductWiseAllDetails() {

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); 
        $location_id   = $user_data->warehouse_id;
        $location_data = $this->site->getWarehouseByIDs($location_id); 

        $locationCode  = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }

        $product_id           = $this->input->get('productId');
        $manufacturingDate    = $this->input->get('manufacturingDate'); // manufacturingDate of product
        $productionUnitName   = $this->input->get('productionUnitName');
        $production_unit_id   = null;
        if ($productionUnitName) {
            $wh = $this->Production_Unit_Model_New->getLocationID($productionUnitName);
            $production_unit_id = $wh ? $wh->id : null;
        }

        $OrderDetails         = $this->production_unit_model->getProductWiseOrderdetails($product_id, $production_unit_id);
        // Sum stock for this product in selected production unit (or user's warehouse)
        $stock_warehouse_id = $production_unit_id ? $production_unit_id : $location_id;
        $productStock         = $this->production_unit_model->getProductStock($product_id, $stock_warehouse_id);
        $productBatches       = $this->production_unit_model->getProductBatches($product_id); // For All Batches
        $yield_calculation_data = $this->production_unit_model->getBatchDetailsForYieldCalculation($product_id);
        $latestProductBatches = $this->production_unit_model->getLatestProductBatches($product_id); // For latest 5 Batches
        $productDetails       = $this->production_unit_model->getProductDetailsByName($name = Null, $product_id);
        $lastBatch            = $this->production_unit_model->getLastBatchNumber($product_id); // Get the last batch number for the specific location and product
        $Ingredients          = $this->production_unit_model->getRawMaterials($product_id, $location_id); // fetch raw materials against product
        $productVariants      = $this->production_unit_model->get_product_variants($product_id);

        $product_shelf_life = isset($productDetails->shelf_life) ? $productDetails->shelf_life : '';
        // Calculate product expiry date
        if(empty($product_shelf_life)){
            $expiryDate = '';
        }else{
            $expiryDate = date('Y-m-d', strtotime($manufacturingDate  . ' + ' . $productDetails->shelf_life . ' days'));
        }
        
        $productDetails->expiryDate = $expiryDate; 
        $productBatches->expiryDate = $expiryDate; 

        $pr[] = ['OrderDetails' => $OrderDetails, 'productStock' => $productStock, 'productBatches' => $productBatches, 'latestProductBatches' => $latestProductBatches, 'productDetails' => $productDetails, 'lastBatch' => $lastBatch, 'locationCode' => $locationCode, 'Ingredients' => $Ingredients, 'yieldData' => $yield_calculation_data, 'productVariants' => $productVariants];
        echo json_encode($pr);
        return;
        
    }
    /////////////////// rmConsumptionSubmit ////////////////////////
    public function addProductWiseBatches()
    {
        $product_id        = $this->input->get('productId');
        $batchQuantity     = $this->input->get('batchQuantity');
        $manufacturingDate = $this->input->get('manufacturingDate');
        $ingredients_param = $this->input->get('ingredients');
        $rawWastageMap = $this->input->get('wastageMap');
        $actual_yield_quantity = $this->input->get('actual_yield_quantity'); // Add actual yield quantity (G)
        $actual_sale_quantity = $this->input->get('actual_sale_quantity'); // Add actual sale quantity (H)
        $total_wastage        = $this->input->get('total_wastage'); // Add total wastage (W) quantity
        $production_loss = $this->input->get('production_loss'); // W1
        $rounding_loss = $this->input->get('rounding_loss'); // W2
        $packaging_loss = $this->input->get('packaging_loss'); // W3
        $wastage_unit         = $this->input->get('wastage_unit'); // Add wastage unit
        $wastageMap = [];
        if (!empty($rawWastageMap)) {
            $decoded = json_decode($rawWastageMap, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $wastageMap = $decoded;
            }
        }

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id);

        // $location_id   = $user_data->warehouse_id;
        // $location_data = $this->site->getWarehouseByIDs($location_id);
        // $locationCode = '';
        // foreach ($location_data as $location) {
        //     $locationCode = $location->code;
        // }

        $user_warehouses_raw = (string) $user_data->warehouse_id;
        $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
        $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
      
        $location_id   = $primary_location_id;
        $location_data = $this->site->getWarehouseByIDs($location_id);

        $locationCode = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }

        // Generate batch number
        $lastBatch = $this->production_unit_model->getLastBatchNumber($product_id);
        $newBatchNumber = $lastBatch
            ? str_pad(intval(substr($lastBatch->batch_no, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : '001';
        $batch_no = $locationCode . '/' . $product_id . '/' . $newBatchNumber;

        if (!empty($batchQuantity)) {
            $productDetails = $this->production_unit_model->getProductDetailsByName(null, $product_id);
            $product_shelf_life = $productDetails->shelf_life;
            $expiryDate = date('Y-m-d', strtotime($manufacturingDate . ' + ' . $product_shelf_life . ' days'));

            $data = [
                'batch_no'        => $batch_no,
                'product_id'      => $product_id,
                'created_at'      => $manufacturingDate,
                'quantity'        => $actual_sale_quantity,
                'expiry_date'     => $expiryDate,
                'cost'            => $productDetails->cost,
                'mrp'             => $productDetails->mrp,
                'price'           => $productDetails->price,
                'location_id'     => $location_id,
                'batch_quantity'  => $batchQuantity,
                'yield_qty'       => $actual_yield_quantity
            ];

            if ($this->production_unit_model->addProductBatches($data)) {

                $ingredient_entries = explode(',', $ingredients_param);
                $pu_batch_id = $this->db->insert_id();

                // Save main product wastage to sma_product_wastage table using new structure
                if (!empty($total_wastage) && $total_wastage > 0) {
                    $wastage_data = [
                        'BatchId' => $batch_no,
                        'batch_build_quantity' => $batchQuantity,
                        'actual_yield_quantity' => $actual_yield_quantity,
                        'actual_sale_quantity' => $actual_sale_quantity,
                        'production_loss' => $production_loss, // W1 - Production Loss (E - G)
                        'rounding_loss' => $rounding_loss, // W2 - Rounding Loss (I - INT(I))
                        'packaging_loss' => $packaging_loss, // W3 - Packaging Loss ((H_original - H_changed) * Z)
                        'total_wastage' => $total_wastage, // W - Total Wastage (W1 + W2 + W3)
                        'unit' => $wastage_unit,
                        'product_id' => $product_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $user_id
                    ];
                    
                    $this->db->insert('sma_product_wastage', $wastage_data);
                }

                // Update stock sync
                $this->site->syncPurchaseItemsForProductionUnit($data['product_id'], $data['location_id'], $data['quantity'], $data['batch_no']);
                $this->site->syncProductQtyForPU($data['product_id'], $data['location_id']);
                $this->production_unit_model->updateWarehouseProductQty($data);

                // ---------- BEGIN: original ingredient_entries loop (unchanged logic) ----------
                // We add $processed_keys tracking to mark keys already handled here.
                $processed_keys = [];

                if (!empty($ingredient_entries)) {
                    foreach ($ingredient_entries as $entry) {
                        // Format: material_id|batch_no|expiry_date (batch_no may be empty)
                        $parts = explode('|', $entry);
                        $material_id = isset($parts[0]) ? (int)$parts[0] : null;
                        $selected_rm_batch_no = isset($parts[1]) ? trim($parts[1]) : null;
                        $selected_rm_expiry_date = isset($parts[2]) ? trim($parts[2]) : null;
                        $requiredQuantity = isset($parts[3]) ? trim($parts[3]) : null;
                        if (!$material_id) continue; // material_id is mandatory

                        // ✅ Get BOM ingredient (for quantity_required)
                        $ingredient = $this->production_unit_model->getRawMaterialForProduct($data['product_id'], $material_id);
                        if (!$ingredient) continue;

                        // ✅ Calculate consumed qty
                        if($requiredQuantity !== null && $requiredQuantity != '') {
                            $consumed_qty = $requiredQuantity;
                        } else {
                            $consumed_qty = $batchQuantity * $ingredient->quantity_required;
                        }

                        $rmBatchRow = null;
                        if (!empty($selected_rm_batch_no)) {
                            // Try to get matching batch for this material
                            $this->db->where('product_id', $material_id);
                            $this->db->where('batch_no', $selected_rm_batch_no);
                            $this->db->limit(1);
                            $rmBatchRow = $this->db->get('sma_product_batches')->row();
                        }

                        // ✅ Deduct from that batch (if batch exists), else update directly by product
                        $wastage_key = $material_id . ($selected_rm_batch_no ? '|' . $selected_rm_batch_no : '');
                        $wastage = isset($wastageMap[$wastage_key]) ? $wastageMap[$wastage_key] : 0;

                        $raw_material_data = [
                            'product_id'  => $material_id,
                            'quantity'    => $consumed_qty + $wastage,
                            'location_id' => $location_id,
                            'batch_no'    => $rmBatchRow ? $rmBatchRow->batch_no : null,
                            'expiry' => $selected_rm_expiry_date ? $selected_rm_expiry_date : null
                        ];
                        $this->production_unit_model->updateRawMaterialQty($raw_material_data);


                        // ✅ Prepare RM Consumption insert
                        $rm_ins = [
                            'pu_batch' => $pu_batch_id,
                            'rm_batch' => $rmBatchRow ? $rmBatchRow->id : null,
                            'rm_name'  => $ingredient->material_name,
                            'consumed' => $consumed_qty,
                            'wastage'  => $wastage
                        ];

                        $this->db->insert('sma_rm_consumption', $rm_ins);

                        // mark processed so we don't insert duplicate later
                        $processed_keys[$wastage_key] = true;
                    }
                }
                // ---------- END: original ingredient_entries loop ----------

                // ---------- BEGIN: insert remaining keys from wastageMap (those not in processed_keys) ----------
                if (!empty($wastageMap)) {
                    foreach ($wastageMap as $map_key => $map_wastage) {
                        // if already processed in the ingredient_entries loop, skip
                        if (isset($processed_keys[$map_key])) {
                            continue;
                        }

                        // Parse key: material_id|batch_no OR material_id
                        $parts = explode('|', $map_key);
                        $material_id = isset($parts[0]) ? (int)$parts[0] : null;
                        $selected_rm_batch_no = isset($parts[1]) ? trim($parts[1]) : null;

                        if (!$material_id) continue;

                        // For these entries consumed = 0 (since not in ingredient_entries)
                        $consumed_qty = 0;
                        $productRow = $this->db
                            ->select('name')
                            ->where('id', $material_id)
                            ->get('sma_products')
                            ->row();

                        $material_name = $productRow ? $productRow->name : '';

                        // Fetch batch row (if exists) to store rm_batch / rm_name
                        $rmBatchRow = null;
                        if (!empty($selected_rm_batch_no)) {
                            $this->db->where('product_id', $material_id);
                            $this->db->where('batch_no', $selected_rm_batch_no);
                            $this->db->limit(1);
                            $rmBatchRow = $this->db->get('sma_product_batches')->row();
                        }

                        // ✅ Also update stock for wastage-only entries
                        $raw_material_data = [
                            'product_id'  => $material_id,
                            'quantity'    => $map_wastage,
                            'location_id' => $location_id,
                            'batch_no'    => $rmBatchRow ? $rmBatchRow->batch_no : null,
                        ];
                        $this->production_unit_model->updateRawMaterialQty($raw_material_data);

                        // Insert into sma_rm_consumption with consumed = 0 and wastage = map value
                        $rm_ins = [
                            'pu_batch' => $pu_batch_id,
                            'rm_batch' => $rmBatchRow ? $rmBatchRow->id : null,
                            'rm_name'  => $material_name,
                            'consumed' => $consumed_qty,
                            'wastage'  => $map_wastage
                        ];

                        $this->db->insert('sma_rm_consumption', $rm_ins);
                    }
                }
                // ---------- END: insert remaining wastageMap keys ----------

                // Return updated product batch & stock info
                $productBatches       = $this->production_unit_model->getProductBatches($product_id);
                $latestProductBatches = $this->production_unit_model->getLatestProductBatches($product_id);
                $productStock         = $this->production_unit_model->getProductStock($product_id);

                echo json_encode([
                    'productBatches'       => $productBatches,
                    'latestProductBatches' => $latestProductBatches,
                    'productStock'         => $productStock
                ]);
                return;
            }

        } else {
            $this->session->set_flashdata('error', 'Batch quantity is required.');
            redirect("Production_Unit/manager_dashboard");
        }
    }

    public function addProductWiseBatchesinProductionDashboard() {
        
        $product_id     = $this->input->get('productId');  
        $product_id = (int)trim($product_id, '"');

        $batchQuantity  = $this->input->get('batchQuantity');
        $manufacturingDate  = $this->input->get('manufacturingDate');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id);                
        $location_id   = $user_data->warehouse_id;        
        $location_data = $this->site->getWarehouseByIDs($location_id);        
        
        $locationCode  = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }

        # Location Wise Batch Number 
        // Get the last batch number for the specific location and product
        $lastBatch = $this->production_unit_model->getLastBatchNumber($product_id);
        
        // Prepare the new batch number
        if ($lastBatch) {
            $lastBatchNumber = intval(substr($lastBatch->batch_no, -3));
            $newBatchNumber  = str_pad($lastBatchNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newBatchNumber = '001';
        }
        $batch_no = $locationCode . '/' . $product_id . '/' . $newBatchNumber;        
        // echo json_encode($batch_no);
        // return;
        # Location Wise Batch Number end

        if(!empty($batchQuantity)){
          
            $productDetails = $this->production_unit_model->getProductDetailsByName($name = Null, $product_id);
            $product_shelf_life = $productDetails->shelf_life;
            
            // Calculate product expiry date
            $expiryDate = date('Y-m-d', strtotime($manufacturingDate  . ' + ' . $product_shelf_life . ' days'));
            // $batch_no = $this->createLocationWiseBatchNumber($product_id);
            
            $data = array(
                'batch_no'        => $batch_no,
                'product_id'      => $product_id,
                'created_at'      => $manufacturingDate,
                'quantity'        => $batchQuantity,
                'expiry_date'     => $expiryDate,
                'cost'            => $productDetails->cost,
                'mrp'             => $productDetails->mrp,
                'price'           => $productDetails->price,
                'location_id'     => $location_id
            );
            
            if ($this->production_unit_model->addProductBatchesinProductionDashboard($data)) {
                
                $productBatches       = $this->production_unit_model->getProductBatches($product_id); // For All Batches                
                $latestProductBatches = $this->production_unit_model->getLatestProductBatches($product_id); // For latest 5 Batches                
                $productStock         = $this->production_unit_model->getProductStockinProductionDashboard($product_id); // for show updated stock after add batch
                
                $productBatchesData = ['productBatches' => $productBatches, 'latestProductBatches' => $latestProductBatches, 'productStock' => $productStock];
                echo json_encode($productBatchesData);
                return;
            }
        }else{
            $this->session->set_flashdata('error', validation_errors());
            redirect("Production_Unit/manager_dashboard");
        }
    }

    // Production Dashboard :create new batch number
    public function createLocationWiseBatchNumber($product_id) {

        $product_id  = $this->input->get('productId');
        $user_id     = $this->session->userdata('user_id');   
        $user_data   = $this->site->getUser($user_id); 
        $location_id = $user_data->warehouse_id;

        $location_data = $this->site->getWarehouseByIDs($location_id); 
        $locationCode  = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }
    
        // Get the last batch number for the specific location and product
        $lastBatch = $this->production_unit_model->getLastBatchNumber($product_id);
    
        // Prepare the new batch number
        if ($lastBatch) {
            $lastBatchNumber = intval(substr($lastBatch->batch_no, -3));
            $newBatchNumber  = str_pad($lastBatchNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newBatchNumber = '001';
        }
    
        $batch_no = $locationCode . '/' . $product_id . '/' . $newBatchNumber;
        echo json_encode(['batch_no' => $batch_no]);
        return $batch_no;
    }

    public function updateDashboardOrderItemDetails() {

        $orderItemId          = $this->input->get('orderItemId');  // get order item id 
        $isChecked            = $this->input->get('isChecked');   // flag for allot qty checkbox click 
        $allotQuantityInput   = $this->input->get('allotQuantityInput');
        $product_id           = $this->input->get('productId');
        $user_id              = $this->session->userdata('user_id');   

        // $this->production_unit_model->updateDashboardOrderItems($orderItemId, $allotQuantityInput, $isChecked); 
        $this->Production_Unit_Model_New->updateOrder(Null, Null, $isChecked, $allotQuantityInput, $orderItemId, Null, Null, Null, $user_id); //update status after click on locked button and also click on complete order button
        
        // get update order and order item details to show on view
        $OrderDetails         = $this->production_unit_model->getProductWiseOrderdetails($product_id);
       
        $updatedOrderDetails[] = ['OrderDetails' => $OrderDetails];
        echo json_encode($updatedOrderDetails);
        return;
    }

    #=========================================== End Production Manager Dashboard Screen  ============================================
    public function receive_delivery() {
        $this->sma->checkPermissions('receive_delivery', NULL, 'production_unit');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        $location_data = $this->site->getWarehouseByIDs($location_id); //get

        $locationName  = '';
        foreach ($location_data as $location) {
           $locationName = $location->name;
        }
            $this->data['error']       = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories']  = $this->production_unit_model->getProductCategoriesList();     
            $this->data['outletName']  = $location_data;

            $bc   = array(array('link' => base_url(), 'page' => lang('Procurement_orders')), array('link' => '#', 'page' => lang('Receive_Delivery')));
            $meta = array('page_title' => lang('Receive_Delivery'), 'bc' => $bc);

            $this->page_construct('production_unit/receive_delivery', $meta, $this->data);      
    }
    public function ordering_history() {
        $this->sma->checkPermissions('ordering_history', NULL, 'production_unit');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        $location_data = $this->site->getWarehouseByIDs($location_id); //get

        $locationName  = '';
        foreach ($location_data as $location) {
           $locationName = $location->name;
        }
            $this->data['error']       = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories']  = $this->production_unit_model->getProductCategoriesList();     
            $this->data['outletName']  = $location_data;

            $bc   = array(array('link' => base_url(), 'page' => lang('Procurement_orders')), array('link' => '#', 'page' => lang('Ordering_History')));
            $meta = array('page_title' => lang('Ordering_History'), 'bc' => $bc);

            $this->page_construct('production_unit/ordering_history', $meta, $this->data);    

    }	

    function add_product($id = NULL) {
        $this->sma->checkPermissions('add_product', NULL, 'production_unit');

        $this->load->helper('security'); 
        $productIds = $this->input->post('productIds');
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); // Get user information
        $location_id = $user_data->warehouse_id;
        $pds = explode(",", $location_id);
        $location_id = '';
        foreach ($pds as $pd) {
            $location_data = $this->site->getWarehouseBy_ID($pd);
            $location_id = $this->Production_Unit_Model_New->get_location_type_name($location_data);
        }
        $warehouse_id = $location_id ? $location_id : $pds[0];
      
        if (!empty($productIds)) {
            $data = array();
            $existingProducts = array();
            $productsAdded = false; // Flag to track if products were added
            $productsExist = false; // Flag to track if products already exist
    
            foreach ($productIds as $productId) {
    
                // Check for this product + this warehouse only
                $check = $this->production_unit_model->productExistsInWarehouse($productId, $warehouse_id);

                if ($check && $check->product_id == $productId && $check->warehouse_id == $warehouse_id) {
                    $product = $this->site->getProductByID($productId);
                    if ($product) {
                        $existingProducts[] = $product->name; 
                    }
                    $productsExist = true;

                } else {
                    $data[] = array(
                        'product_id' => $productId,
                        'location_id' => $warehouse_id,
                        'stock_quantity' => 0, 
                        'open_order_quantity' => 0, 
                        'batch_production' => 0, 
                        'retail_production' => 0, 
                        'rack' => 0, 
                        'manufacturing_cost' => 0.00, 
                    );
                    $war_data[] = array(
                        'product_id' => $productId,
                        'warehouse_id' => $warehouse_id,
                        'quantity' => 0, 
                    );
                }
            }
            if (!empty($data)) {
                $result = $this->production_unit_model->add_product_to_productionunit($data);
                if ($result) {
                    // Products are now mapped by inserting into sma_warehouses_products inside add_product_to_productionunit()
                    $productsAdded = true; // Set the flag for products added
                }
            }
            $message = '';
            if ($productsAdded) {
                $message .= 'Products added successfully. ';
            }
            if ($productsExist) {
                $message .= 'The following products already exist: ' . implode(', ', $existingProducts);
            }
            if ($message) {
                $this->session->set_flashdata('message', $message);
            }
            $this->page_construct('production_unit/add_product', $meta, $this->data);
        } else {
            $this->page_construct('production_unit/add_product', $meta, $this->data);
        }
    }

    // public function suggestions() {

    //     $searchTerm = $this->input->get('term');
    //     $productName = $this->input->get('name');
    //     $product_id = $this->input->get('product_id');
    //     $variants = 0;
    //     $productId = 0;
    //     $data = 0;
    //     $existingProducts = $this->production_unit_model->getExistingProducts();
    //     if ($product_id) {
    //         $variants = $this->production_unit_model->get_product_variants($product_id);
    //         // echo json_encode($variants);
    //     }
    
    //     if ($productName) {
    //         $productId = $this->production_unit_model->getProductIdByName($productName);
    //         // echo json_encode($productId);
    //     }
    
    //     if ($searchTerm) {
    //         $data = $this->production_unit_model->get_products($searchTerm);
    //         // echo json_encode($data);
    //     }

    // }

    public function suggestions() {
        $searchTerm = $this->input->get('term');
        $productName = $this->input->get('name');
        $product_id = $this->input->get('product_id');
        
        $suggestedProducts = [];
        $existingProducts = $this->production_unit_model->getExistingProducts();
    
        if ($product_id) {
            $variants = $this->production_unit_model->get_product_variants($product_id);
            $suggestedProducts = array_merge($suggestedProducts, $variants);
        }
        if ($productName) {
            $productId = $this->production_unit_model->getProductIdByName($productName);
            if ($productId) {
                $suggestedProducts[] = (object) ['id' => $productId]; // Ensure it has the correct structure
            }
        }
        if ($searchTerm) {
            $data = $this->production_unit_model->get_products($searchTerm);
            $suggestedProducts = array_merge($suggestedProducts, $data);
        }
        foreach ($suggestedProducts as $product) {
            $present = false; 
            foreach ($existingProducts as $c_product) {
                if ($product->id == $c_product->product_id) {
                    $present = true; 
                    break; 
                }
            }
            if (!$present) {
                $filteredSuggestions[] = $product; 
            }
        }
        echo json_encode(array_values($filteredSuggestions)); 
    }
    

////////////////////////////////////////// Order Dispatch ///////////////////////////////////////////
    public function Ready_To_Dispatch() {
        $this->sma->checkPermissions('ready_to_dispatch', NULL, 'production_unit');
        $locationNames = $this->Production_Unit_Model_New->getLocations();    
        if ($this->input->is_ajax_request()) {
            echo json_encode($locationNames);
            return;
        }
        $this->data['locationNames'] = $locationNames;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('Ready_To_Dispatch')));
        $meta = array('page_title' => lang('Production_Unit'), 'bc' => $bc);
        $this->page_construct('production_unit/order_dispatch', $meta, $this->data);
    }   

    public function insert_data() {

        $procurement_order_ref_no = trim((string)$this->input->post('refNumber')) ?: trim((string)$this->input->post('procurement_order_ref_no'));

        $courier_val = $this->input->post('Courier');
        $courier_options = [
            '0' => 'DTDC',
            '1' => 'FedEx',
            '2' => 'DHL',
            '3' => 'Aramex'
        ];
        $courier_text = isset($courier_options[$courier_val]) ? $courier_options[$courier_val] : '';
        $tracking_number = $this->input->post('Tracking_Number');
        $procurement_orders = $this->production_unit_model->getProcurementOrderData($procurement_order_ref_no);
        $procurement_order_id = $procurement_orders->id;
        $procurement_order_items    = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurement_order_id); 
        
        // for checking biller_id for production unit login
        $user_id                    = $this->session->userdata('user_id');
        $user_data                  = $this->site->getUser($user_id); 
        $warehouse_id               = $user_data->warehouse_id;
        
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

        $picked_up_by = $this->input->post('Picked_Up_by');
        $notes = $this->input->post('Notes');

        // Initialize the data array
        $data = [
            'procurement_order_ref_no' => $procurement_order_ref_no,
            'courier' => $courier_text,
            'tracking_number' => $tracking_number,
            'picked_up_by' => $picked_up_by,
            'notes' => $notes,
        ];

        // --- File Upload Handling ---
        // if (!empty($_FILES['attachment']['name'])) {
        //     $this->load->library('upload');

        //     // Define a valid upload path and create the directory if it doesn't exist
        //     $upload_path = FCPATH . 'assets/uploads/dispatch_attachments/';
        //     if (!is_dir($upload_path)) {
        //         mkdir($upload_path, 0777, true);
        //         file_put_contents($upload_path . 'index.html', '<html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        //     }
            
        //     $config = [
        //         'upload_path'   => $upload_path,
        //         'allowed_types' => 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt|rtf|bmp|csv',
        //         'max_size'      => '8000', // 8MB
        //         'overwrite'     => false,
        //         'encrypt_name'  => true
        //     ];
        //     $this->upload->initialize($config);

        //     if ($this->upload->do_upload('attachment')) {
        //         $upload_data = $this->upload->data();
        //         // Save the relative path for database and URL access
        //         $data['attachment'] = $upload_data['file_name']; // Get the file name
        //     } else {
        //         // If upload fails, show error and redirect back
        //         $error = $this->upload->display_errors();
        //         $this->session->set_flashdata('error', $error);
        //         $this->session->keep_flashdata('procurement_order_ref_no'); // Keep ref number for the form
        //         // redirect($this->agent->referrer()); // Or $_SERVER["HTTP_REFERER"]
        //         // redirect('production_unit/Ready_To_Dispatch',$data);
        //         redirect('Production_Unit/Ready_To_Dispatch');

        //         return; // Exit after redirect
        //     }
        // }


        // Insert data into the database
        // $this->load->model('Production_Unit_Model');
        $inserted = $this->production_unit_model->CourierDetails($data, $procurement_order_id);
        if ($inserted) {
            // if($production_unit_biller_id !== $outlet_biller_id){
            //     $this->sma->CreateSales($procurement_orders, $procurement_order_items); // Create sales when we dispatch order from production unit
            //     $this->session->set_flashdata('message', 'Order dispatched and sale created successfully.');
            // }
            // if($production_unit_biller_id == $outlet_biller_id){
            //     $this->sma->CreateTransfer($procurement_order_id);// Create transfer when we dispatch order from production unit
            //     $this->session->set_flashdata('message', 'Order dispatched  and transfer created successfully.');
            // }
            // $this->session->set_flashdata('message', 'Order dispatched successfully.');
            
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
        } else {
            $this->session->set_flashdata('error', 'Failed to dispatch order.');
        }

        redirect('Production_Unit/Ready_To_Dispatch');
    }

    // All Orders for Logged in Kitchen <= Production Unit
    public function getAllOrdersForLoggedInProductionUnit(){

        $user_id             = $this->session->userdata('user_id');
        $user_data           = $this->site->getUser($user_id); //get user information
        $user_location_data  = $this->site->getWarehouseBy_ID($user_data->warehouse_id); //get user information
    
        $location_name       = $this->input->get('locationName');
        $sort_order_by_time  = $this->input->get('sortOrderByTime');

        $outlets   = $this->Production_Unit_Model_New->getLocationID($location_name);    
    
        $AllOrders = $this->Production_Unit_Model_New->getAllOrdersForLoggedInProductionUnit($user_id, $outlets->id, $sort_order_by_time);    
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
        $procurmentOrderId  = $this->input->get('procurmentOrderId'); //get order Id
        $location_name      = $this->input->get('locationName');

        if($procurmentOrderId){
            // click on specific order, show that order wise order items
            $order_items = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurmentOrderId); //get order items
        
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
        $OrderItems[] = ['order_items' => $order_items,'order_status' => $order_status];
    
        echo json_encode($OrderItems);
        return;
    }

    // update order data
    public function updateOrder()
    {
        $user_id            = $this->session->userdata('user_id');
        $user_data          = $this->site->getUser($user_id); //get user information
        $location_id        = $user_data->warehouse_id;

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
        foreach($order_items as $value){
            $order_status = $value->order_status;
            $value->user_location_id = $location_id;

        }
        $updatedOrderDetails[] = ['order_items' => $order_items,'order_status' => $order_status];
        echo json_encode($updatedOrderDetails);
        return;
        
    }
    // Inventory

    function inventory($warehouse_id = NULL) {
        $this->sma->checkPermissions('inventory', NULL, 'production_unit');
        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['alert_qty'] = $this->uri->segment(4);
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id')) : NULL;
            $this->data['warehouse_id'] = ($warehouse_id) ? $warehouse_id : $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id'));
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Inventory')));
        $meta = array('page_title' => lang('products'), 'bc' => $bc);
        $this->page_construct('production_unit/inventory', $meta, $this->data);
    }

    function getProducts($warehouse_id = NULL) {
        // $this->sma->checkPermissions('index', TRUE);

        // Get warehouse_id from GET parameter first, then URI segment
        if (!$warehouse_id) {
            $warehouse_id = $this->input->get('warehouse_id');
        }
        if (!$warehouse_id) {
            $warehouse_id = $this->uri->segment(4);
        }

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        // Handle alert_qty parameter
        $alert_qty = $this->input->get('alert_qty');

        // Convert warehouse_id to array for proper handling (like in standard Products controller)
        if ($warehouse_id) {
            $warehouse_id = explode(',', $warehouse_id);
            $warehouse_id = array_filter(array_map('trim', $warehouse_id)); // removes empty values
            $warehouse_id = array_map('intval', $warehouse_id);
        }

        $detail_link = anchor('products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . site_url('products/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> " . lang('delete_product') . "</a>";
        $single_barcode = anchor('products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));

        $set_fav_link = "<a  id='a__$1' href='" . site_url('products/favourite/') . "?product_id=$1'><i class=\"fa fa-star\"></i> " . lang('add_favourite') . "</a>";
        $unset_fav_link = "<a  id='a__$1' href='" . site_url('products/Refavourite/') . "?product_id=$1'><i class=\"fa fa-star\"></i> " . lang('remove_favourite') . "</a>";

        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">' . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">' . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . site_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . site_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . site_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> ' . lang('set_rack') . '</a></li>';
        }

        if ($this->Settings->product_batch_setting > 0) {
            $action_add_batches = '<li><a href="' . site_url('products/add_batch?p=$1') . '"  data-toggle="modal" data-target="#myModal"><i class="fa fa-list"></i>' . lang('Manage Batches') . '<img src="' . site_url('themes/default/assets/images/new.gif') . '" height="20px" alt="new"></a></li>';
        }

        $action .= '<li><a href="' . site_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> ' . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
                        <li class="add_fav_link">' . $set_fav_link . '</li><li  class="remove_fav_link">' . $unset_fav_link . '</li>
                        ' . $action_add_batches . '    
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');

        if ($warehouse_id) {
            //{$this->db->dbprefix('products')}.article_code as article_code ,
            $this->datatables->select("sma_products.id as productid,  "
            . "{$this->db->dbprefix('products')}.code as code,"
            . "{$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand,"
            . "{$this->db->dbprefix('categories')}.name as cname,"
            . "{$this->db->dbprefix('units')}.name as unit, wp.rack as rack, {$this->db->dbprefix('products')}.storage_type, "
            . "FORMAT(COALESCE(wp.quantity, 0), 2) as quantity, is_featured", FALSE)
            ->from('products');

            if ($this->Settings->display_all_products) {
                // Using the same pattern as standard Products controller
                $warehouse_list = implode(',', $warehouse_id);
                $this->datatables->join("( SELECT product_id, quantity, rack, warehouse_id from
                    {$this->db->dbprefix('warehouses_products')}
                    WHERE warehouse_id IN (" . $warehouse_list . ") ) wp",
                    'products.id=wp.product_id', 'left');
                if ($this->Owner && $this->Admin) {
                    $this->datatables->where('wp.warehouse_id is not null');
                }
            } else {
                // Using where_in for multiple warehouses like the standard controller
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where_in('wp.warehouse_id', $warehouse_id)
                    ->where('wp.quantity !=', 0);
            }

            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.sale_unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left');

            if ($this->input->get('alert_qty')) {
                $this->datatables->where('wp.quantity <= products.alert_quantity');
            }
            $this->datatables->where('products.pos_combo_product', NULL);
            $this->datatables->group_by("sma_products.id, {$this->db->dbprefix('products')}.code, {$this->db->dbprefix('products')}.name, {$this->db->dbprefix('brands')}.name, {$this->db->dbprefix('categories')}.name, {$this->db->dbprefix('units')}.name, wp.rack, {$this->db->dbprefix('products')}.storage_type, is_featured");
        
        } else {

            //echo $this->input->post('aqty');
            //{$this->db->dbprefix('products')}.article_code as article_code , 
            $this->datatables->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit, '' as rack, {$this->db->dbprefix('products')}.storage_type, FORMAT(COALESCE(quantity, 0),2) as quantity, {$this->db->dbprefix('products')}.is_featured", FALSE)
                    ->from('products')
                    ->join('categories', 'products.category_id=categories.id', 'left')
                    ->join('units', 'products.sale_unit=units.id', 'left')
                    ->join('brands', 'products.brand=brands.id', 'left');
            if ($this->input->get('alert_qty')) { // update by SW on 8-08-2019
                $this->datatables->where('products.quantity <= products.alert_quantity');
            }
            $this->datatables->where('products.pos_combo_product', NULL);
            $this->datatables->group_by("products.id");
        }

        $this->datatables->add_column("Actions", $action, "productid, image, code, name");

        echo $this->datatables->generate();
    }
    public function download_attachment($id){
        $id =   urldecode($id);
        // $this->load->model('Production_Unit_Model');
        
        $Order = $this->production_unit_model->getOrderRefrenceNoById($id);
        if (!$Order) {
            $this->session->set_flashdata('error', 'Invalid Order.');
             return redirect($_SERVER['HTTP_REFERER']);
        }

        $OrderRefrenceNo = $Order->procurement_order_ref_no;
        $filename = $this->production_unit_model->get_attachment($OrderRefrenceNo);

        if ($filename) {
            // Correct path to match your upload function
            $file_path = FCPATH . 'assets/uploads/dispatch_attachments/' . $filename;

            if (file_exists($file_path)) {
                $this->load->helper('download');
                // Correct way to force download a local file
                force_download($file_path, NULL);
            } else {
                $this->session->set_flashdata('error', 'Attachment file not found on server.');
                return redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', 'No attachment found for this order.');
            return redirect($_SERVER['HTTP_REFERER']);
        }
        
    }
    function product_actions($wh = NULL) {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ((!$this->Owner || !$this->Admin) && !$wh) {
            $user = $this->site->getUser();
            $wh = $user->warehouse_id;
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == TRUE) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {

                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(NULL, NULL, NULL, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'fav_products') {
                    if ($this->Products_model->productsMarkFavourite($_POST['val'])) {
                        $this->session->set_flashdata('message', $this->lang->line("Product Mark as Favourite"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line("Please try again"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sma->storeDeletedData('products', 'id', $id);
                        $this->Products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'labels') {

                    foreach ($_POST['val'] as $id) {
                        $row = $this->Products_model->getProductByID_Production_unit_printBarcode($id, $wh);
                        $selected_variants = FALSE;
                        if ($variants = $this->Products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->wp_quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : FALSE;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->page_construct('products/print_barcodes', $meta, $this->data);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,), 'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000'))));

                    $this->excel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:F1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Products');
                    $this->excel->getActiveSheet()->setTitle('Products');

                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('sale') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('quantity'));
                    // $this->excel->getActiveSheet()->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $row = 3;
                    $total_quantity = 0;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->Products_model->getProductDetail($id);
                        $brand = $this->site->getBrandByID($product->brand);
                        if ($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach ($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        } else {
                            $base_unit = '';
                            $sale_unit = '';
                            $purchase_unit = '';
                        }
                        $variants = $this->Products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if ($wh_qty = $this->Products_model->getProductQuantity_by_warehouse($id, $wh)) {
                                $quantity = $wh_qty['total_quantity']; 
                            } else {
                                $quantity = 0;
                            }
                        }
                        $styleArray = [
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            ],
                        ],
                    ];
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, ($brand ? $brand->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale_unit);
                        $this->excel->getActiveSheet()->getStyle("F" . $row)->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $quantity);
                        $this->excel->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $total_quantity += $quantity;

                        $row++;
                    }
                    $styleArray = [
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            ],
                        ],
                    ];
                    
                    $this->excel->getActiveSheet()->getStyle("F" . $row)->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total_quantity);
                        // Apply border to the cell
                        $styleArray = [
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'], // Black color
                                ],
                            ],
                        ];

                    $this->excel->getActiveSheet()->getStyle('F' . $row)->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    
                    // $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    // $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    // $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    // $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    // $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(40);
                    // $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
                    // $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Inventory_' . date('Y_m_d_H_i_s');
                    
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' . PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
   
    public function kot()
    {
        $warehouse_id = $this->input->get('productionUnits');
        if(!$warehouse_id)
        {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        $this->data['location_name'] = $this->production_unit_model->get_warehouse_name($warehouse_id);
        $this->data['all_kot'] = $this->production_unit_model->all_kot($warehouse_id);
        $this->format_kot_quantities($this->data['all_kot']);
        $this->data['is_kot_by_order'] = false; // Add this line
        $this->data['is_procurement_order_ref_no'] = false; // Add this line
        $this->load->view($this->theme . 'production_unit/kot', $this->data);
    }

    private function format_kot_quantities(&$all_kot) {
        if (empty($all_kot)) return;
        foreach ($all_kot as &$row) {
            if (isset($row['Order Quantity'])) $row['Order Quantity'] = $this->sma->formatQuantity((float)$row['Order Quantity']);
            if (isset($row['Stock Quantity'])) $row['Stock Quantity'] = $this->sma->formatQuantity((float)$row['Stock Quantity']);
            if (isset($row['Built Quantity'])) $row['Built Quantity'] = $this->sma->formatQuantity((float)abs($row['Built Quantity']));
        }
    }

    public function kot_by_order($orderId)
    {
        $toggle = $this->input->get('isToggleOn');
        $this->data['all_kot'] = $this->production_unit_model->kot_by_order($orderId, $toggle);
        $this->format_kot_quantities($this->data['all_kot']);
        $this->data['location_name'] = !empty($this->data['all_kot'][0]['Outlets Requesting']) ? $this->data['all_kot'][0]['Outlets Requesting'] : '';
        $full_ref_no = !empty($this->data['all_kot'][0]['procurement_order_ref_no']) ? $this->data['all_kot'][0]['procurement_order_ref_no'] : '';
        $parts = explode('/', $full_ref_no);
        $this->data['procurement_order_ref_no'] = isset($parts[1]) ? $parts[1] : '';        
        $this->data['is_kot_by_order'] = true; // Add this line
        $this->data['is_procurement_order_ref_no'] = true; // Add this line
        $this->load->view($this->theme . 'production_unit/kot', $this->data);
    }


    // public function production_dashboard() { 

    //     // $this->sma->checkPermissions();
    //     $user_id               = $this->session->userdata('user_id');   
    //     $user_data             = $this->site->getUser($user_id); 
    //     $location_id           = $user_data->warehouse_id;
    //     $location_data         = $this->site->getWarehouseByIDs($location_id); 
    //     $productionUnitName    = $this->input->get('productionUnitName');
        
    //     $default_location      = reset($location_data); // Get the first location
    //     $default_location_name = $default_location->name;
              
    //     $productionUnit  = '';
    //     foreach ($location_data as $location) {
    //         $productionUnit[] = $location->name;
    //     }
      
    //     if($product_id = $this->input->get('productId')){

    //         if(!empty($product_id)){
    //             // Production Dashboard :reset stock
    //             $this->production_unit_model->resetProductStock($product_id);
    //         }else {
    //             $this->session->set_flashdata('error', validation_errors());
    //             redirect("production_Unit/manager_dashboard");
    //         }

    //     }
    //     else{
    //         // Fetch products based on the default location or selected production unit
    //         if ($productionUnitName) {
    //             $products = $this->production_unit_model->getproductDetailsByLocation($productionUnitName);
    //         } else {
    //             $products = $this->production_unit_model->getproductDetailsByLocation($default_location->name);
    //         }
    //         if ($this->input->is_ajax_request()) {
    //             echo json_encode($products);
    //             return;
    //         }
    //         $this->data['products'] = $products;
    //         // $this->data['products']  = $this->production_unit_model->getproductDetails();  
    //         $this->data['productionUnitName']  = $productionUnit; 
    //         $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    //         $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('production_dashboard2')));
    //         $meta = array('page_title' => lang('production_dashboard'), 'bc' => $bc);
    //         $this->page_construct('production_unit/production_dashboard', $meta, $this->data);
    //     }
    // }


    public function production_dashboard() {  

        $this->sma->checkPermissions('Production_Dashboard');
        $user_id               = $this->session->userdata('user_id');   
        $user_data             = $this->site->getUser($user_id); 
        $location_id           = $user_data->warehouse_id;
        $location_data         = $this->site->getWarehouseByIDs($location_id); 
        $productionUnitName    = $this->input->get('productionUnitName');       
       
        $default_location      = reset($location_data); // Get the first location
        $default_location_name = $default_location->name;

        $warehousesWithProductsDetails =  $this->production_unit_model->getWarehousesWithProductsData();     

        $productionUnit  = '';
        foreach ($location_data as $location) {
            $productionUnit[] = $location->name;
        }
      
        if($product_id = $this->input->get('productId')){

            if(!empty($product_id)){
                // Production Dashboard :reset stock
                $this->production_unit_model->resetProductStock($product_id);
            }else {
                $this->session->set_flashdata('error', validation_errors());
                redirect("production_Unit/manager_dashboard");
            }            

            // -------------------xxxxxxxxxxxxxxxxxxxxxxxxxx--------------------------------            
            if ($this->input->is_ajax_request()) {
                echo json_encode($warehousesWithProductsDetails);   
                return;             
            }                                      
            // -----------------xxxxxxxxxxxxxxxxxxxxxxxxxxxx---------------------------------

        }
        else{
            // Fetch products based on the default location or selected production unit
            if ($productionUnitName) {
                $products = $this->production_unit_model->getproductDetailsByLocation($productionUnitName);
            } else {
                $products = $this->production_unit_model->getproductDetailsByLocation($default_location->name);
            }            
            if ($this->input->is_ajax_request()) {
                echo json_encode($products);
                return;
            }
                
            // xxxxxxxxxxxxxxxxxxxxxxxxxxx-------------------commented------------------------      
            if ($this->input->is_ajax_request()) {
                echo json_encode($warehousesWithProductsDetails);   
                return;             
            }                  
            // -----------------xxxxxxxxxxxxxxxxxxxxxxxxxxx---------------------------------
            
            $this->data['products'] = $products;
            $this->data['warehousesWithProductsDetails'] = $warehousesWithProductsDetails;  //  xxxxxxxxxxxxxxx
            $this->data['productionUnitName']  = $productionUnit; 
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('Production Unit')), array('link' => '#', 'page' => lang('Floor Dashboard')));
            $meta = array('page_title' => lang('Floor Dashboard'), 'bc' => $bc);
            $this->page_construct('production_unit/production_dashboard', $meta, $this->data);
        }          
    }
    
        // Create purchase when we received order from outlet 
    public function CreatePurchase($sale_id, $location_id, $order_data) {
        // $this->sma->checkPermissions();

        if ($sale_id) {
            $inv = $this->sales_model->getInvoiceByID($sale_id);
            $inv_items = $this->sales_model->getAllInvoiceItems($sale_id);
           
            $reference =  $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                // $date = $this->sma->fld(trim($this->input->post('date')));
                $date = date('Y-m-d H:i:s');

            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id       = $location_id;
            
            $supplier_id       = $inv->biller_id;
            $status = 'received';
            $shipping =  0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            //$supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $supplier = !empty($supplier_details->name) ? $supplier_details->name : $supplier_details->company;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            
            // Check if biller name exists in companies table as supplier
            $existing_supplier = $this->site->getSupplierByCompanyame($supplier_details->company);

            if ($existing_supplier) {
                $supplier_id = $existing_supplier->id;
                $supplier_details = $existing_supplier;
            } else {
                $supplier_data = [
                    'name'        => $supplier_details->name,
                    'email'       => $supplier_details->email,
                    'group_id'    => 4,
                    'group_name'  => 'supplier',
                    'company'     => $supplier_details->company,
                    'address'     => $supplier_details->address,
                    'vat_no'      => $supplier_details->vat_no,
                    'gstn_no'     => $supplier_details->gstn_no,
                    'city'        => $supplier_details->city,
                    'state'       => $supplier_details->state,
                    'state_code'  => $supplier_details->state_code,
                    'postal_code' => $supplier_details->postal_code,
                    'country'     => $supplier_details->country,
                    'phone'       => $supplier_details->phone,
                    'cf1'         => $supplier_details->cf1,
                    'cf2'         => $supplier_details->cf2,
                    'cf3'         => $supplier_details->cf3,
                    'cf4'         => $supplier_details->cf4,
                    'cf5'         => $supplier_details->cf5,
                    'cf6'         => $supplier_details->cf6,
                    'location_id' => $supplier_details->location_id,
                ];

                $this->db->insert('companies', $supplier_data);
                $supplier_id = $this->db->insert_id();
                $supplier_details = (object) $supplier_data;
                $supplier_details->id = $supplier_id;
            }

            $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $supplier_id = $supplier_details->id;

            /*Set GST Type Logic*/
            $supplier_state_code = $supplier_details->state_code != '' ? $supplier_details->state_code : NULL;
            // Get biller state code instead of warehouse state code
            $billers_id = $this->site->getBillerByWarehouseId($warehouse_id);
            $billers_state_code = $this->sma->getstatecode($billers_id);
            $GSTType = 'GST';
            if($supplier_state_code != NULL && $billers_state_code != NULL){
                $GSTType = ($supplier_state_code == $billers_state_code) ? 'GST' : 'IGST';
            }

            // $warehouse = $this->site->getWarehouseByID($warehouse_id);            
            // if($warehouse->state_code != ''){
            //     $billers_id = $this->pos_settings->default_biller;
            //     $billers_state_code = $this->sma->getstatecode($billers_id);
            // }    
             
            // $purchase_state_code = $warehouse->state_code != '' ? $warehouse->state_code : ($billers_state_code != '' ? $billers_state_code : NULL);
            // $GSTType = 'GST';
            // if($supplier_state_code != NULL && $purchase_state_code != NULL){
            //     $GSTType = ($supplier_state_code == $purchase_state_code) ? 'GST' : 'IGST';
            // }
            
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = sizeof($_POST['product']);
            $total_cgst = $total_sgst = $total_igst = 0;
            foreach ($inv_items as $item) {
                 
                foreach ($order_data as $order_item) {
              
                    if ($order_item['product_id'] == $item->product_id) {
                        // Set received_quantity to the quantity in inv_items
                        $item->quantity = $order_item['received_quantity'];
                        $item->unit_quantity = $order_item['received_quantity'];
                    }
                }
                $item_code = $item->product_code;
                
                if($item_code != '') {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    $product_id = $product_details->id;
                }
                
                $item_option = 0; $item_batch_number = NULL; $batchData = NULL; 
                
                if($product_details->storage_type == 'packed') {
                    $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : 0; 
                }
                
                if($this->Settings->product_batch_setting !== 0) {
                    $row_batch_number = (isset($_POST['batch_number'][$r]) && $_POST['batch_number'][$r]!='') ? $_POST['batch_number'][$r] : NULL;
                    if ($row_batch_number) { 
                        $batch = explode('~', $row_batch_number);
                        $item_batch_number = (count($batch)==2) ? $batch[1] : $batch[0];
                        $batchData = $this->site->getProductBatchData($item_batch_number, $product_id, $item_option);                    
                    }
                }
                
                $hsn_code           = (isset($_POST['hsn_code'][$r]) && $_POST['hsn_code'][$r] != '') ? $hsn_code : $product_details->hsn_code;
                $item_net_cost      = $item->net_unit_price;
                $unit_cost          = $item->net_unit_price;
                $real_unit_cost     = $item->real_unit_price;
                $item_unit_quantity = $item->quantity;
                
                $item_tax_rate      = isset($item->tax_rate_id) ? $item->tax_rate_id : 0;
                $item_discount      = isset($item->discount) ? $item->discount : 0;
                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;

                if($batchData == NULL && $item_batch_number != NULL ){
                    $batchData = array(
                        'product_id'=>$product_id, 
                        'option_id'=>$item_option, 
                        'batch_no'=>$item_batch_number,
                        'cost' => $unit_cost,
                        'price' => '',
                        'mrp' => '',
                        'expiry_date' => $item_expiry ,
                    );
                    $this->site->addBatchInfo($batchData);
                }

                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $item->product_unit_id;
                // $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_quantity      = $item_unit_quantity;
                $item_tax_method    =  $item->tax_method; 

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost          = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost      = $unit_cost;
                    $pr_item_discount   = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount   += $pr_item_discount;
                    $pr_tax             = 0;
                    $pr_item_tax        = 0;
                    $item_tax           = 0;
                    $tax                = "";
                    $cgst = $sgst = $igst = $gst_rate = 0;
                    
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            $taxmethod = ($item_tax_method == '') ? $product_details->tax_method : $item_tax_method;
                            if ($product_details && $taxmethod == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $taxmethod == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }

                    $product_tax    += $pr_item_tax;
                    $subtotal       = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit           = $this->site->getUnitByID($item_unit);
                    $item_option    = $item_option ? $item_option : 0;
                    
                    $quantity_received = ($status == 'received') ? $item_quantity : 0;
                                        
                    if($pr_item_tax) {
                        if($GSTType == 'IGST'){
                            $igst = $pr_item_tax;
                            $gst_rate = $tax_details->rate;
                        } else {
                            $cgst = $sgst = ($pr_item_tax / 2);
                            $gst_rate = ($tax_details->rate / 2);
                        }
                    }
            
                    $products[] = array(
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $quantity_received,
                        'quantity_received' => $quantity_received,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $pr_tax,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'batch_number'      => $item_batch_number,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                        'hsn_code'          => $hsn_code,
                        'tax_method'        => $item_tax_method,
                        'gst_rate'          => $gst_rate,
                        'cgst'              => $cgst,
                        'sgst'              => $sgst,
                        'igst'              => $igst,
                        'mrp'              => $item->mrp,

                    );

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    
                    $total_cgst += $cgst;
                    $total_sgst += $sgst;
                    $total_igst += $igst;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                  $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                  } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                  }  
            } else {
                $order_discount_id = null;
            }
            //$total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
            $total_discount = $this->sma->formatDecimal($product_discount);
            
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        // $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax ) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping)), 4);
            $rounding = '';
            if ($this->pos_settings->rounding > 0) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = ($round_total - $grand_total);
            }
                        
            $data = [
                'reference_no'      => $inv->reference_no,
                'date'              => $date,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $order_tax_id,
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => $status,
                'created_by'        => $this->session->userdata('user_id'),
                'payment_term'      => $payment_term,
                'rounding'          => $rounding,
                'due_date'          => $due_date,
                'cgst'              => $total_cgst,
                'sgst'              => $total_sgst,
                'igst'              => $total_igst,
            ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        $this->purchases_model->addPurchase($data, $products);
        return;
    }
    ///////////// Generate Variant PO /////////////
     // Lightweight JSON endpoint to fetch company by ID for client-side validations
     public function getCompanyByID($id = null)
     {
         if (!$id) {
             $this->sma->send_json(['error' => true, 'message' => 'Supplier ID is required']);
             return;
         }
         
         $row = $this->site->getCompanyByID($id);
         
         if (!$row) {
             $this->sma->send_json(['error' => true, 'message' => 'Supplier not found']);
             return;
         }
         
         // Check if address/location is set
         $hasAddress = !empty($row->address) && trim($row->address) !== '';
         $hasLocationId = !empty($row->location_id) && $row->location_id !== null;
         $hasLocation = $hasAddress || $hasLocationId;
         
         if (!$hasLocation) {
             $this->sma->send_json([
                 'error' => true, 
                 'message' => 'Supplier location/address is not set',
                 'supplier' => $row,
                 'hasAddress' => $hasAddress,
                 'hasLocationId' => $hasLocationId
             ]);
             return;
         }
         
        // Return success with supplier data
        $this->sma->send_json([
            'error' => false,
            'supplier' => $row,
            'hasLocation' => true
        ]);
    }

    /**
     * Get stock across all Vendor locations for a given product/raw material
     * Returns location-wise stock breakdown
     */
    public function getStockByVendorLocations() {
        $product_id = $this->input->get('product_id') ? (int)$this->input->get('product_id') : 0;
        
        if (!$product_id) {
            $this->sma->send_json(['error' => true, 'message' => 'Product ID required']);
            return;
        }

        // Get all Vendor type locations
        $vendor_locations = $this->site->getWarehousesByLocationType('Vendor');
        
        if (!$vendor_locations) {
            $this->sma->send_json(['error' => false, 'locations' => [], 'total_stock' => 0]);
            return;
        }

        $locations_data = [];
        $total_stock = 0;

        foreach ($vendor_locations as $location) {
            // Get stock for this product at this location
            $this->db->select('quantity');
            $this->db->where('warehouse_id', $location->id);
            $this->db->where('product_id', $product_id);
            $stock_query = $this->db->get('warehouses_products');
            
            $quantity = 0;
            if ($stock_query->num_rows() > 0) {
                $quantity = (float)$stock_query->row()->quantity;
            }

            $locations_data[] = [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'quantity' => $quantity
            ];

            $total_stock += $quantity;
        }

        $this->sma->send_json([
            'error' => false,
            'locations' => $locations_data,
            'total_stock' => $total_stock
        ]);
    }

    /**
     * Get stock across all non-Vendor locations (our own warehouses/outlets)
     * Returns location-wise stock breakdown
     */
    public function getStockByOurLocations() {
        $product_id = $this->input->get('product_id') ? (int)$this->input->get('product_id') : 0;
        
        if (!$product_id) {
            $this->sma->send_json(['error' => true, 'message' => 'Product ID required']);
            return;
        }

        // Get all non-Vendor locations (our own warehouses)
        // Get vendor location type ID first
        $vendor_type = $this->db->select('id')->where('type', 'Vendor')->get('sma_location_type')->row();
        $vendor_type_id = $vendor_type ? $vendor_type->id : 0;

        // Get all warehouses that are NOT vendor type
        $this->db->select('id, name, code, location_type');
        $this->db->from('warehouses');
        $this->db->where('is_deleted', 0);
        $this->db->where('is_disabled', 0);
        $this->db->where('is_active', 1);

        if ($vendor_type_id) {
            $this->db->where('location_type !=', $vendor_type_id);
        }
        $locations_query = $this->db->get();
        
        if ($locations_query->num_rows() === 0) {
            $this->sma->send_json(['error' => false, 'locations' => [], 'total_stock' => 0]);
            return;
        }

        $our_locations = $locations_query->result();
        $locations_data = [];
        $total_stock = 0;

        foreach ($our_locations as $location) {
            // Get stock for this product at this location
            $this->db->select('quantity');
            $this->db->where('warehouse_id', $location->id);
            $this->db->where('product_id', $product_id);
            $stock_query = $this->db->get('warehouses_products');
            
            $quantity = 0;
            if ($stock_query->num_rows() > 0) {
                $quantity = (float)$stock_query->row()->quantity;
            }

            // Only include locations with stock greater than 0
            if ($quantity > 0) {
                $locations_data[] = [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'quantity' => $quantity
                ];

                $total_stock += $quantity;
            }
        }

        $this->sma->send_json([
            'error' => false,
            'locations' => $locations_data,
            'total_stock' => $total_stock
        ]);
    }
      // Kitchen User Dashboard (clone of manager_dashboard)
      public function kitchen_user_dashboard() {

        $user_id               = $this->session->userdata('user_id');   
        $user_data             = $this->site->getUser($user_id); 

        // Warehouses can be comma-separated; first is primary, rest are secondary
        $user_warehouses_raw   = (string)$user_data->warehouse_id;
        $user_warehouses_arr   = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
        $primary_location_id   = $user_warehouses_arr ? (int)$user_warehouses_arr[0] : null;
        $secondary_location_ids = $user_warehouses_arr ? array_slice($user_warehouses_arr, 1) : [];

        // Determine which warehouse the user is currently logged into (fallback to primary)
        $current_location_id   = (int)$this->session->userdata('warehouse_id');
        if (!$current_location_id) {
            $current_location_id = $primary_location_id;
        }

        // Build workstation list based on login context:
        // - If logged into primary: show only secondary locations.
        // - If logged into secondary: show only that specific location.
        // - Fallback: use current location when nothing else is available.
        $workstations = [];
        if ($current_location_id && $primary_location_id && $current_location_id === $primary_location_id && !empty($secondary_location_ids)) {
            $workstations = $this->site->getWarehouseByIDs(implode(',', $secondary_location_ids));
        } elseif ($current_location_id) {
            $workstations = $this->site->getWarehouseByIDs((string)$current_location_id);
        }
        // Normalize to indexed array for the view
        if (is_array($workstations)) {
            $workstations = array_values($workstations);
        }

        $location_data         = $this->site->getWarehouseByIDs($user_warehouses_raw); 
        $productionUnitName    = $this->input->get('productionUnitName');
        $workstation_id        = $this->input->get('workstation_id');
        
        $default_location      = reset($location_data); // Get the first location
        $default_location_name = $default_location ? $default_location->name : ''; 
       
        $productionUnit  = '';
        foreach ($location_data as $location) {
            $productionUnit[] = $location->name;
            $productionUnitId = $location->id;

        }
      
        if($product_id = $this->input->get('productId')){

            if(!empty($product_id)){
                // Production Dashboard :reset stock
                $this->production_unit_model->resetProductStock($product_id);
            }else {
                $this->session->set_flashdata('error', validation_errors());
                redirect("Production_Unit/kitchen_user_dashboard");
            }

        }else{
            // Choose the first available workstation as selected if none provided
            $selected_ws  = $workstation_id ?: ($workstations ? $workstations[0]->id : null);
         
            if ($selected_ws) {
                // Preferred path: products scoped by selected workstation
                $products = $this->production_unit_model->getProductsByWorkstation($selected_ws,$primary_location_id);
            } else {
                // Fallback to previous behavior if no workstation found
                // if ($productionUnitName) {
                //     $products = $this->production_unit_model->getproductDetailsByLocation($productionUnitName);
                // } else {
                //     $products = $this->production_unit_model->getproductDetailsByLocation($default_location->name);
                // }
            }

            if ($this->input->is_ajax_request()) {
                echo json_encode($products);
                return;
            }
            $this->data['workstations'] = $workstations;
            $this->data['selected_workstation_id'] = $selected_ws;
            $this->data['products'] = $products;
            $this->data['productionUnitName']  = $productionUnit; 
            $this->data['productionUnitId']  = $productionUnitId; 
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('Production Unit')), array('link' => '#', 'page' => 'Kitchen User Dashboard'));
            $meta = array('page_title' => 'Kitchen User Dashboard', 'bc' => $bc);
            $this->page_construct('production_unit/kitchen_user_dashboard', $meta, $this->data);
        }
    }
    // New: fetch products by workstation for Kitchen User Dashboard
    public function getKitchenProductsByWorkstation()
    {
        $workstation_id = $this->input->get('workstation_id');
        if (!$workstation_id) {
            echo json_encode([]);
            return;
        }
        // Always scope by the workstation passed from UI
        $products = $this->production_unit_model->getProductsByWorkstation((int)$workstation_id);
        echo json_encode($products ?: []);
    }
    public function getProductWiseAllDetailsForKitchenUser() {

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); 
        $location_id   = $user_data->warehouse_id;
        $location_data = $this->site->getWarehouseByIDs($location_id); 

        $locationCode  = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }

        $product_id           = $this->input->get('productId');
        $manufacturingDate    = $this->input->get('manufacturingDate'); // manufacturingDate of product

        $OrderDetails         = $this->production_unit_model->getProductWiseOrderdetails($product_id);
        $productStock         = $this->production_unit_model->getKitchenUserProductStock($product_id, $location_id);
     
        $productBatches       = $this->production_unit_model->getProductBatches($product_id); // For All Batches
        $latestProductBatches = $this->production_unit_model->getLatestProductBatches($product_id); // For latest 5 Batches
        $productDetails       = $this->production_unit_model->getProductDetailsByName($name = Null, $product_id);
        $lastBatch            = $this->production_unit_model->getLastBatchNumber($product_id); // Get the last batch number for the specific location and product
        $Ingredients          = $this->production_unit_model->getRawMaterials($product_id, $location_id); // fetch raw materials against product

        $product_shelf_life = isset($productDetails->shelf_life) ? $productDetails->shelf_life : '';
        // Calculate product expiry date
        if(empty($product_shelf_life)){
            $expiryDate = '';
        }else{
            $expiryDate = date('Y-m-d', strtotime($manufacturingDate  . ' + ' . $productDetails->shelf_life . ' days'));
        }
        
        $productDetails->expiryDate = $expiryDate; 
        $productBatches->expiryDate = $expiryDate; 

        $pr[] = ['OrderDetails' => $OrderDetails, 'productStock' => $productStock, 'productBatches' => $productBatches, 'latestProductBatches' => $latestProductBatches, 'productDetails' => $productDetails, 'lastBatch' => $lastBatch, 'locationCode' => $locationCode, 'Ingredients' => $Ingredients];
        echo json_encode($pr);
        return;
        
    }
    public function KitchenUseraddProductWiseBatches()
    {
        $product_id        = $this->input->get('productId');
        $batchQuantity     = $this->input->get('batchQuantity');
        $manufacturingDate = $this->input->get('manufacturingDate');
        $ingredients_param = $this->input->get('ingredients');
        $rawWastageMap = $this->input->get('wastageMap');
        $wastageMap = [];
        if (!empty($rawWastageMap)) {
            $decoded = json_decode($rawWastageMap, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $wastageMap = $decoded;
            }
        }

        $user_id     = $this->session->userdata('user_id');
        $user_data   = $this->site->getUser($user_id);

        // Users can have multiple warehouses (comma-separated). The first is primary.
        $user_warehouses_raw = (string) $user_data->warehouse_id;
        $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
        $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
      
        $location_id   = $primary_location_id;
        $location_data = $this->site->getWarehouseByIDs($location_id);
      
        $locationCode = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }   

        // Generate batch number
        $lastBatch = $this->production_unit_model->getLastBatchNumber($product_id);
        $newBatchNumber = $lastBatch
            ? str_pad(intval(substr($lastBatch->batch_no, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : '001';
        $batch_no = $locationCode . '/' . $product_id . '/' . $newBatchNumber;

        if (!empty($batchQuantity)) {
            $productDetails = $this->production_unit_model->getProductDetailsByName(null, $product_id);
            $product_shelf_life = $productDetails->shelf_life;
            $expiryDate = date('Y-m-d', strtotime($manufacturingDate . ' + ' . $product_shelf_life . ' days'));

            $data = [
                'batch_no'        => $batch_no,
                'product_id'      => $product_id,
                'created_at'      => $manufacturingDate,
                'quantity'        => $batchQuantity,
                'expiry_date'     => $expiryDate,
                'cost'            => $productDetails->cost,
                'mrp'             => $productDetails->mrp,
                'price'           => $productDetails->price,
                'location_id'     => $location_id,
                'batch_quantity'  => $batchQuantity
            ];

            if ($this->production_unit_model->addProductBatches($data)) {

                $ingredient_entries = explode(',', $ingredients_param);
                $pu_batch_id = $this->db->insert_id();

                // Update stock sync
                $this->site->syncPurchaseItemsForProductionUnit($data['product_id'], $data['location_id'], $data['quantity'], $data['batch_no']);
                $this->site->syncProductQtyForPU($data['product_id'], $data['location_id']);
                $this->production_unit_model->KitchenUserupdateWarehouseProductQty($data);

                // ---------- BEGIN: original ingredient_entries loop (unchanged logic) ----------
                // We add $processed_keys tracking to mark keys already handled here.
                $processed_keys = [];

                if (!empty($ingredient_entries)) {
                    foreach ($ingredient_entries as $entry) {
                        // Format: material_id|batch_no|expiry_date (batch_no may be empty)
                        $parts = explode('|', $entry);
                        $material_id = isset($parts[0]) ? (int)$parts[0] : null;
                        $selected_rm_batch_no = isset($parts[1]) ? trim($parts[1]) : null;
                        $selected_rm_expiry_date = isset($parts[2]) ? trim($parts[2]) : null;
                        if (!$material_id) continue; // material_id is mandatory

                        // ✅ Get BOM ingredient (for quantity_required)
                        $ingredient = $this->production_unit_model->getRawMaterialForProduct($data['product_id'], $material_id);
                        if (!$ingredient) continue;

                        // ✅ Calculate consumed qty
                        $consumed_qty = $batchQuantity * $ingredient->quantity_required;

                        $rmBatchRow = null;
                        if (!empty($selected_rm_batch_no)) {
                            // Try to get matching batch for this material
                            $this->db->where('product_id', $material_id);
                            $this->db->where('batch_no', $selected_rm_batch_no);
                            $this->db->limit(1);
                            $rmBatchRow = $this->db->get('sma_product_batches')->row();
                        }

                        // ✅ Deduct from that batch (if batch exists), else update directly by product
                        $raw_material_data = [
                            'product_id'  => $material_id,
                            'quantity'    => $consumed_qty,
                            'location_id' => $location_id,
                            'batch_no'    => $rmBatchRow ? $rmBatchRow->batch_no : null,
                            'expiry' => $selected_rm_expiry_date ? $selected_rm_expiry_date : null
                        ];
                        $this->production_unit_model->updateRawMaterialQty($raw_material_data);

                        $wastage_key = $material_id . ($selected_rm_batch_no ? '|' . $selected_rm_batch_no : '');
                        $wastage = isset($wastageMap[$wastage_key]) ? $wastageMap[$wastage_key] : 0;


                        // ✅ Prepare RM Consumption insert
                        $rm_ins = [
                            'pu_batch' => $pu_batch_id,
                            'rm_batch' => $rmBatchRow ? $rmBatchRow->id : null,
                            'rm_name'  => $rmBatchRow ? $rmBatchRow->batch_no : '',
                            'consumed' => $consumed_qty,
                            'wastage'  => $wastage
                        ];

                        $this->db->insert('sma_rm_consumption', $rm_ins);

                        // mark processed so we don't insert duplicate later
                        $processed_keys[$wastage_key] = true;
                    }
                }
                // ---------- END: original ingredient_entries loop ----------

                // ---------- BEGIN: insert remaining keys from wastageMap (those not in processed_keys) ----------
                if (!empty($wastageMap)) {
                    foreach ($wastageMap as $map_key => $map_wastage) {
                        // if already processed in the ingredient_entries loop, skip
                        if (isset($processed_keys[$map_key])) {
                            continue;
                        }

                        // Parse key: material_id|batch_no OR material_id
                        $parts = explode('|', $map_key);
                        $material_id = isset($parts[0]) ? (int)$parts[0] : null;
                        $selected_rm_batch_no = isset($parts[1]) ? trim($parts[1]) : null;

                        if (!$material_id) continue;

                        // For these entries consumed = 0 (since not in ingredient_entries)
                        $consumed_qty = 0;

                        // Fetch batch row (if exists) to store rm_batch / rm_name
                        $rmBatchRow = null;
                        if (!empty($selected_rm_batch_no)) {
                            $this->db->where('product_id', $material_id);
                            $this->db->where('batch_no', $selected_rm_batch_no);
                            $this->db->limit(1);
                            $rmBatchRow = $this->db->get('sma_product_batches')->row();
                        }

                        // Insert into sma_rm_consumption with consumed = 0 and wastage = map value
                        $rm_ins = [
                            'pu_batch' => $pu_batch_id,
                            'rm_batch' => $rmBatchRow ? $rmBatchRow->id : null,
                            'rm_name'  => $rmBatchRow ? $rmBatchRow->batch_no : '',
                            'consumed' => $consumed_qty,
                            'wastage'  => $map_wastage
                        ];

                        $this->db->insert('sma_rm_consumption', $rm_ins);
                    }
                }
                // ---------- END: insert remaining wastageMap keys ----------

                // Return updated product batch & stock info
                $productBatches       = $this->production_unit_model->getProductBatches($product_id);
                $latestProductBatches = $this->production_unit_model->getLatestProductBatches($product_id);
                $productStock         = $this->production_unit_model->getProductStock($product_id);

                echo json_encode([
                    'productBatches'       => $productBatches,
                    'latestProductBatches' => $latestProductBatches,
                    'productStock'         => $productStock
                ]);
                return;
            }

        } else {
            $this->session->set_flashdata('error', 'Batch quantity is required.');
            redirect("Production_Unit/manager_dashboard");
        }
    }

    // public function kds(){
    //     $source = strtolower((string) $this->input->get('source'));
    //     $kdsSource = ($source === 'suspended') ? 'suspended' : 'sales';

    //     if ($kdsSource === 'suspended') {
    //         $payload = $this->production_unit_model->fetchSuspendedBillsWithItems();
    //     } else {
    //         $payload = $this->production_unit_model->fetchSalesOrdersWithItems();
    //     }

    //     // If request is AJAX return JSON
    //     if ($this->input->is_ajax_request()) {
    //         die("akshu");
    //         echo json_encode($payload);
    //         return;
    //     }

    //     $this->data['kds_source'] = $kdsSource;
    //     $this->data['kds_orders'] = isset($payload['orders']) ? $payload['orders'] : array();
    //     $this->data['kds_items']  = isset($payload['items']) ? $payload['items'] : array();

    //     $this->load->view($this->theme . 'production_unit/kds', $this->data);
    // }

    public function kds(){
        $this->load->view($this->theme . 'production_unit/kds', $this->data);
        
        // $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        // $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('kds')));
        // $meta = array('page_title' => lang('kds'), 'bc' => $bc);
        // $this->page_construct('production_unit/kds', $meta, $this->data);
    }

    public function kds_D(){
        $this->load->view($this->theme . 'production_unit/kds_D', $this->data);
    }
    // public function get_kds_data(){

    //     $salesData     = $this->production_unit_model->fetchSalesOrdersWithItems();
    //     $suspendedData = $this->production_unit_model->fetchSuspendedBillsWithItems();

    //     $orders = array_merge(
    //         $salesData['orders'] ?? [],
    //         $suspendedData['orders'] ?? []
    //     );

    //     $items = $salesData['items'] ?? [];
    //     foreach (($suspendedData['items'] ?? []) as $orderId => $orderItems) {
    //         $items[$orderId] = $orderItems;
    //     }

    //     usort($orders, function ($a, $b) {
    //         return strtotime($a->date) <=> strtotime($b->date);
    //     });

    //     return $this->output
    //         ->set_content_type('application/json')
    //         ->set_output(json_encode([
    //             'orders' => $orders,
    //             'items'  => $items
    //         ]));
    // }

    public function get_kds_data(){

        // Fetch data from both sources
        $salesData     = $this->production_unit_model->fetchSalesOrdersWithItems();
        $suspendedData = $this->production_unit_model->fetchSuspendedBillsWithItems();

        /* ---------------- SAFE ORDERS ---------------- */
      
        $salesOrders = [];
        if (isset($salesData['orders']) && is_array($salesData['orders'])) {
            $salesOrders = $salesData['orders'];
        }

        $suspendedOrders = [];
        if (isset($suspendedData['orders']) && is_array($suspendedData['orders'])) {
            $suspendedOrders = $suspendedData['orders'];
        }

        // Add order type to each order
        foreach ($salesOrders as &$order) {
            $order->order_source = 'sale';
        }
        foreach ($suspendedOrders as &$order) {
            $order->order_source = 'suspended';
        }

        // Merge orders safely
        $orders = array_merge($salesOrders, $suspendedOrders);

        // /* ---------------- SAFE ITEMS ---------------- */

        $items = [];
        if (isset($salesData['items']) && is_array($salesData['items'])) {
            $items = $salesData['items'];
        }

        if (isset($suspendedData['items']) && is_array($suspendedData['items'])) {
            foreach ($suspendedData['items'] as $orderId => $orderItems) {
                $items[$orderId] = $orderItems;
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'orders' => $orders,
                'items'  => $items
            ]));
    }

    public function complete_kds_order() {
        $order_id = $this->input->post('order_id');
        $order_type = $this->input->post('order_type'); // 'sale' or 'suspended'
        
        if (!$order_id) {
            $this->sma->send_json([
                'error' => true,
                'message' => 'Order ID is required'
            ]);
            return;
        }
        
        $this->load->model('sales_model');
        
        try {
            if ($order_type === 'suspended') {
                // Handle suspended bill - mark as completed
                // Note: Suspended bills don't have sale_status, they're reactivated as sales
                // For KDS purposes, we can add a note or flag
                $this->db->update('suspended_bills', 
                    array('suspend_note' => 'Completed from KDS - ' . date('Y-m-d H:i:s')), 
                    array('id' => $order_id)
                );
            } else {
                // Handle sale order - update sale status to completed
                if ($this->sales_model->updateStatus($order_id, 'completed', 'Completed from KDS')) {
                    $this->sma->send_json([
                        'error' => false,
                        'message' => 'Order completed successfully',
                        'csrf_token' => $this->security->get_csrf_hash()
                    ]);
                    return;
                } else {
                    $this->sma->send_json([
                        'error' => true,
                        'message' => 'Failed to update order status'
                    ]);
                    return;
                }
            }
            
            $this->sma->send_json([
                'error' => false,
                'message' => 'Order completed successfully',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            
        } catch (Exception $e) {
            $this->sma->send_json([
                'error' => true,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    // Variant product: fetch RM list for selected variant (manager dashboard)
    public function getVariantBatchRmConsumptionData() {

        $product_id           = $this->input->get('productId');
        $option_id            = $this->input->get('optionId');
        $productionUnitName   = $this->input->get('productionUnitName');

        if (empty($product_id) || empty($option_id)) {
            echo json_encode(array('success' => false, 'message' => 'Product and variant are required.'));
            return;
        }

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id);
        $location_id   = $user_data->warehouse_id;
        $production_unit_id = null;

        if ($productionUnitName) {
            $wh = $this->Production_Unit_Model_New->getLocationID($productionUnitName);
            $production_unit_id = $wh ? $wh->id : null;
        }

        $stock_warehouse_id = $production_unit_id ? $production_unit_id : $location_id;
        $Ingredients        = $this->production_unit_model->get_variant_raw_materials($product_id, $option_id, $stock_warehouse_id);
        $variant            = $this->site->getVerientById($option_id);
        $productDetails     = $this->production_unit_model->getProductDetailsByName($name = Null, $product_id);
        $yieldData          = $this->production_unit_model->getBatchDetailsForYieldCalculation($product_id);

        $pr = array(
            'success'           => true,
            'Ingredients'       => $Ingredients ? $Ingredients : array(),
            'variant_name'      => $variant ? $variant->name : '',
            'product_name'      => $productDetails ? $productDetails->name : '',
            'product_unit_name' => $productDetails ? $productDetails->unit_name : '',
            'yieldData'         => $yieldData,
        );
        echo json_encode($pr);
        return;
    }

    // Variant product: save batch + RM consumption (manager dashboard)
    public function saveVariantBatchRmConsumption() {

        $product_id            = $this->input->get('productId');
        $option_id             = $this->input->get('optionId');
        $batchQuantity         = $this->input->get('batchQuantity');
        $manufacturingDate     = $this->input->get('manufacturingDate');
        $ingredients_param     = $this->input->get('ingredients');
        $rawWastageMap         = $this->input->get('wastageMap');
        $actual_yield_quantity = $this->input->get('actual_yield_quantity');
        $actual_sale_quantity  = $this->input->get('actual_sale_quantity');
        $total_wastage         = $this->input->get('total_wastage');
        $production_loss       = $this->input->get('production_loss');
        $rounding_loss         = $this->input->get('rounding_loss');
        $packaging_loss        = $this->input->get('packaging_loss');
        $wastage_unit          = $this->input->get('wastage_unit');

        $wastageMap = [];
        if (!empty($rawWastageMap)) {
            $decoded = json_decode($rawWastageMap, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $wastageMap = $decoded;
            }
        }

        if (empty($product_id) || empty($option_id) || empty($batchQuantity)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid batch or variant.'));
            return;
        }

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id);
        $user_warehouses_raw = (string) $user_data->warehouse_id;
        $user_warehouses_arr = array_values(array_filter(array_map('trim', explode(',', $user_warehouses_raw))));
        $primary_location_id = $user_warehouses_arr ? (int) $user_warehouses_arr[0] : (int) $user_data->warehouse_id;
        $location_id   = $primary_location_id;
        $location_data = $this->site->getWarehouseByIDs($location_id);

        $locationCode = '';
        foreach ($location_data as $location) {
            $locationCode = $location->code;
        }

        $lastBatch = $this->production_unit_model->getLastBatchNumber($product_id);
        $newBatchNumber = $lastBatch
            ? str_pad(intval(substr($lastBatch->batch_no, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : '001';
        $batch_no = $locationCode . '/' . $product_id . '/' . $newBatchNumber;

        $productDetails     = $this->production_unit_model->getProductDetailsByName(null, $product_id);
        $product_shelf_life = $productDetails->shelf_life;
        $expiryDate         = date('Y-m-d', strtotime($manufacturingDate . ' + ' . $product_shelf_life . ' days'));

        $batch_row = [
            'batch_no'       => $batch_no,
            'product_id'     => $product_id,
            'created_at'     => $manufacturingDate,
            'quantity'       => $actual_sale_quantity,
            'expiry_date'    => $expiryDate,
            'cost'           => $productDetails->cost,
            'mrp'            => $productDetails->mrp,
            'price'          => $productDetails->price,
            'location_id'    => $location_id,
            'batch_quantity' => $batchQuantity,
            'yield_qty'      => $actual_yield_quantity,
            'option_id'      => $option_id,
        ];

        $variant = $this->site->getVerientById($option_id);
        if ($variant) {
            if (isset($variant->cost) && $variant->cost !== null && $variant->cost !== '') {
                $batch_row['cost'] = $variant->cost;
            }
            if (isset($variant->price) && $variant->price !== null && $variant->price !== '') {
                $batch_row['price'] = $variant->price;
            }
            if (isset($variant->mrp) && $variant->mrp !== null && $variant->mrp !== '') {
                $batch_row['mrp'] = $variant->mrp;
            }
        }

        if (!$this->production_unit_model->addVariantProductBatch($batch_row)) {
            echo json_encode(array('success' => false, 'message' => 'Could not save batch.'));
            return;
        }

        $pu_batch_id = $this->db->insert_id();

        if (!empty($total_wastage) && $total_wastage > 0) {
            $this->db->insert('sma_product_wastage', [
                'BatchId'               => $batch_no,
                'batch_build_quantity'  => $batchQuantity,
                'actual_yield_quantity' => $actual_yield_quantity,
                'actual_sale_quantity'  => $actual_sale_quantity,
                'production_loss'       => $production_loss,
                'rounding_loss'         => $rounding_loss,
                'packaging_loss'        => $packaging_loss,
                'total_wastage'         => $total_wastage,
                'unit'                  => $wastage_unit,
                'product_id'            => $product_id,
                'created_at'            => date('Y-m-d H:i:s'),
                'created_by'            => $user_id,
            ]);
        }

        // Variant stock sync (same 3-step pattern as non-variant add batch, per option_id + product sum)
        $this->production_unit_model->syncVariantStockForProductionUnit($product_id,$location_id,$batch_row);

        $ingredient_entries = !empty($ingredients_param) ? explode(',', $ingredients_param) : [];
        $this->production_unit_model->saveVariantRmConsumption($pu_batch_id,$product_id,$option_id,$location_id,$batchQuantity,$ingredient_entries,$wastageMap);

        $productBatches       = $this->production_unit_model->getProductBatches($product_id);
        $latestProductBatches = $this->production_unit_model->getLatestProductBatches($product_id);
        $productStock         = $this->production_unit_model->getProductStock($product_id, $location_id);

        echo json_encode(array(
            'success'              => true,
            'productBatches'       => $productBatches,
            'latestProductBatches' => $latestProductBatches,
            'productStock'         => $productStock,
        ));
        return;
    }


}
?>
