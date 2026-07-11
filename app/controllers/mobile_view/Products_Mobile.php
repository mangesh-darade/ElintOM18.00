<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_Mobile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->lang->load('products', $this->Settings->user_language);
        $this->load->model('products_model');
    }

    public function index($warehouse_id = NULL) {
        $this->sma->checkPermissions('index', NULL, 'products');
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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('products') . ' (Mobile)', 'bc' => $bc);
        $this->page_construct('mobile_view/products/index_mobile', $meta, $this->data);
    }
}
