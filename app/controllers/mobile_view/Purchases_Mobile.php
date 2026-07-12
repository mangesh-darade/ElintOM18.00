<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchases_Mobile extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('welcome'));
        }
        $this->lang->load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('purchases_model');
        $this->load->model('products_model');
        $this->load->model('production_unit_model');
        $this->load->model('pos_model');
        $this->load->model('transfers_model');
    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions('index', TRUE, 'purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id == null ? $this->session->userdata('warehouse_id') : $warehouse_id;
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchases')));
        $meta = array('page_title' => lang('purchases') . ' (Mobile)', 'bc' => $bc);
        $this->page_construct('mobile_view/purchases/index_mobile', $meta, $this->data);
    }
}
