
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Todays_Special extends MY_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation'); 
        $this->load->model('Production_Unit_Model_New');
        $this->load->model('Todaysoffer_model');
        $this->load->model('Products_model');
        $this->load->model('Sales_model');
        $this->load->model('Purchases_model');
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
         $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses(); 
        }else {
            $location_data = $this->site->getWarehouseByIDs($location_id); //get
        }
        $locationName  = '';
        foreach ($location_data as $location) {
           $locationName = $location->name;
        }
            $this->data['error']       = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories']  = $this->Todaysoffer_model->getProductCategoriesList();     
            $this->data['outletName']  = $location_data;
            // Pass currency symbol to JavaScript
            $this->data['currency'] = $this->Settings->default_currency;
            // Get the currency symbol from settings
            $this->data['currency_symbol'] = $this->sma->formatDecimal(0, 0);
            $this->data['currency_symbol'] = trim(str_replace('0', '', $this->data['currency_symbol']));
            // Ensure we have a valid currency symbol
            if (empty($this->data['currency_symbol'])) {
                $this->data['currency_symbol'] = 'Rs.'; // Default fallback
            }

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang("Today's Special")));
            $meta = array('page_title' => lang("Today's Special"), 'bc' => $bc);

            $this->page_construct('products/map_todays_offer', $meta, $this->data);   
    }
        
    public function GetProductsbyCategoriesID() {   
        $this->load->model('Todaysoffer_model');
        $categoriesId    = $this->input->get('categoriesId');
        $subcategoryId   = $this->input->get('subcategoryId');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        $location        =  $this->Todaysoffer_model->getWarehouseByID($location_id);
        $productsByCategories = '';
        
        if ($categoriesId || $subcategoryId) {
            $productsByCategories  = $this->Todaysoffer_model->getProductByCategories($categoriesId,$subcategoryId);    
        }
      
        if ($productsByCategories) {
            $i=0;
            foreach ($productsByCategories as $row) {
                
                $unitData = $this->Todaysoffer_model->getUnitById($row->unit);
                $row->unit_name         = $unitData->name;
                $row->unit_price        = $row->price;  
                $row->org_price         = $row->price;
                $row->base_unit_price   = $row->price;
                $row->unit_weight       = $row->weight;
                $row->quantity          = 1;
                $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);

            }
            $pr[] = ['productsByCat' => $productsByCategories,  'row' => $row, 'tax_rate' => $tax_rate];
        }else {
            $pr[] = ['productsByCat' => $productsByCategories];

        }
        echo json_encode($pr);
        return;
    }


    

public function PlaceProcurementOrder() {
    $user_id     = $this->session->userdata('user_id');
    $user_data   = $this->site->getUser($user_id);
    $location_id = $user_data->warehouse_id;

    $post = $this->input->post();
    $orders = isset($post['orders']) ? $post['orders'] : [];

    if (!empty($orders)) {
        $title = isset($orders[0]['title']) ? trim($orders[0]['title']) : '';

        if (empty($title)) {
            echo json_encode([
                'status' => 'error',
                'message' => '⚠️ Title is empty. Please enter a title before saving.'
            ]);
            return;
        }

        $requested_delivery_date = isset($orders[0]['requested_delivery_date']) ? $orders[0]['requested_delivery_date'] : '';

        $existingTitle = $this->Todaysoffer_model->getTitleForDate($requested_delivery_date);

            if ($existingTitle && $title !== $existingTitle) {
                echo json_encode([
                    'status' => 'error',
                    'message' => '⚠️ A title for this date already exists: ' . $existingTitle
                ]);
                return;
            }

        $Items = [];
        foreach ($orders as $order) {
            $productName = isset($order['product']) ? trim($order['product']) : '';
            $category_id = isset($order['category_id']) ? trim($order['category_id']) : '';
            $price = isset($order['price']) ? trim($order['price']) : '0';

            $price = preg_replace('/[^\d.]/', '', $price);
            $price = ltrim($price, '.');
            $price = ($price !== '') ? floatval($price) : 0;

            $productDetails = $this->Todaysoffer_model->getProductDetailsByName($productName);
            if (!$productDetails) {
                continue;
            }

            $Items[] = [
                'product_id'  => $productDetails->id,
                'category_id' => $category_id,
                'title'       => $title,
                'price'       => $price,
                'date'        => $requested_delivery_date
            ];
        }

        if (!empty($Items)) {
            $result = $this->Todaysoffer_model->PlaceProcurementOrder($Items);
            if ($result['added'] === 0 && $result['updated'] === 0 && empty($result['duplicates'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => '❌ Nothing to add or update.'
                ]);
                return;
            }


            echo json_encode($result);
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No valid items to insert.'
            ]);
            return;
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No orders received.'
        ]);
        return;
    }
}

    public function getProcurementOrderList() {    
        $status          = $this->input->get('orderStatus');
        // $order_id        = $this->input->get('order_id');

        if ($status) {
            $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '1' : '0';
            $getOrderData  = $this->Todaysoffer_model->getProcurmentOrders($status, $order_dispatch); 
            $pr = $getOrderData;

        }else {
            $pr = "Please Select Status";
        }
        echo json_encode($pr);
        return;
    }



public function deleteProcurementItem() {
    $id = $this->input->post('id');
    $product_id = $this->input->post('product_id');

    try {
        if (!$id && !$product_id) {
            // No valid identifier provided
            throw new Exception('No record identifier provided for deletion.');
        }

        // First, check if the record exists (only if id is provided)
        if ($id) {
            $exists = $this->db->where('id', $id)->where('deleted', 0)->get('sma_today_special_items')->row();
            if (!$exists) {
                throw new Exception("Record with id {$id} not found or already deleted.");
            }
            // Delete by id only - safest option
            $this->db->where('id', $id);
        } elseif ($product_id) {
            // If no id, fallback to product_id - be cautious with this
            $exists = $this->db->where('product_id', $product_id)->where('deleted', 0)->get('sma_today_special_items')->row();
            if (!$exists) {
                throw new Exception("Record with product_id {$product_id} not found or already deleted.");
            }
            $this->db->where('product_id', $product_id);
        }

        // Soft delete: mark record as deleted
        $this->db->update('sma_today_special_items', [
            'deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        $affected = $this->db->affected_rows();

        if ($affected > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Item soft deleted successfully'
            ]);
        } else {
            throw new Exception('No records matched the deletion criteria.');
        }

    } catch (Exception $e) {
        // Log error with query info for debug
        log_message('error', 'Soft Delete failed: ' . $e->getMessage() . " | Last query: " . $this->db->last_query());

        echo json_encode([
            'status' => 'error',
            'message' => 'Deletion failed',
            'debug' => [
                'error' => $e->getMessage(),
                'input_id' => $id,
                'input_product_id' => $product_id,
                'last_query' => $this->db->last_query()
            ]
        ]);
    }
}

public function checkProductInCart() {
    $product_id = $this->input->post('product_id');
    
    $this->db->where('product_id', $product_id);
    $query = $this->db->get('sma_today_special_items');
    
    echo json_encode(['exists' => $query->num_rows() > 0]);
    exit;
}
    

}
?>