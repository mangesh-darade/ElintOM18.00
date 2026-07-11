<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Variant_Order extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('Bill_of_material_model');
        $this->load->model('Variant_Order');
        $this->load->model('Production_Unit_Model_New');
        $this->load->model('Products_model');
        $this->load->model('purchases_model');
        $this->load->database(); // Database library
        $this->load->library('datatables');
        $this->load->library('upload');
        $this->digital_upload_path = 'files/' . $this->Customer_assets;
        $this->upload_path = 'assets/mdata/' . $this->Customer_assets . '/uploads/production_unit';
        $this->thumbs_path = 'assets/mdata/' . $this->Customer_assets . '/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
        $this->data['Settings'] = $this->Settings;
    }




    

    function index($warehouse_id = NULL)
    {
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses();
        } else {
            $location_data = $this->site->getWarehouseByIDs($location_id); //get
        }
        $locationName = '';
        foreach ($location_data as $location) {
            $locationName = $location->name;
        }

        $this->data['units'] = $this->site->getAllBaseUnits();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['categories'] = $this->Bill_of_material_model->getProductCategoriesList();
        $this->data['outletName'] = $location_data;

        $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('Variant_Order')));
        $meta = array('page_title' => lang('Variant_Order'), 'bc' => $bc);

        $this->page_construct('production_unit/variant_order', $meta, $this->data);
    }
   
}
