<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mobile-optimized controller for Procurement Orders.
 * Reuses the same backend logic as Production_Unit via the same model.
 * Does not modify any existing Production_Unit logic.
 */
class Production_Unit_Mobile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->model('production_unit_model');
    }

    /**
     * Mobile procurement orders page: three tabs (Current, Open, History).
     * Uses same data as desktop for consistency.
     */
    public function procurementOrders() {
        $this->sma->checkPermissions('Procurement_Orders');
        $user_id     = $this->session->userdata('user_id');
        $user_data   = $this->site->getUser($user_id);
        $location_id = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses();
        } else {
            $location_data = $this->site->getWarehouseByIDs($location_id);
        }
        if (!$location_data || !is_array($location_data)) {
            $location_data = array();
        }
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['outletName'] = $location_data;

        $bc   = array(array('link' => base_url(), 'page' => lang('Procurement_Orders')), array('link' => '#', 'page' => lang('Place_Order')));
        $meta = array('page_title' => lang('Place_Order') . ' (Mobile)', 'bc' => $bc);

        $this->page_construct('mobile_view/production_unit/procurement_orders_mobile', $meta, $this->data);
    }

    /**
     * Mobile inventory view.
     * Reuses same data as Production_Unit::inventory but loads inventory_mobile view.
     */
    public function inventory($warehouse_id = NULL) {
        $this->sma->checkPermissions('inventory', NULL, 'production_unit');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['alert_qty'] = $this->uri->segment(4);
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses']   = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id')) : NULL;
            $this->data['warehouse_id'] = ($warehouse_id) ? $warehouse_id : $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id'));
        }

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Inventory')));
        $meta = array('page_title' => lang('products') . ' (Mobile)', 'bc' => $bc);
        $this->page_construct('mobile_view/production_unit/inventory_mobile', $meta, $this->data);
    }

    /**
     * Same as Production_Unit::getProcurementOrderList.
     * Returns order list for status: Open, partially, previous_order (History).
     */
    public function getProcurementOrderList() {
        $status = $this->input->get('orderStatus');
        if (!$status) {
            echo json_encode(array('error' => 'Please Select Status'));
            return;
        }
        $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '1' : '0';
        $getOrderData   = $this->production_unit_model->getProcurmentOrders($status, $order_dispatch);
        $pr             = array(array('getOrderData' => $getOrderData));
        echo json_encode($pr);
    }

    /**
     * Same as Production_Unit::getProcurementOrderItemsListByOrderStatus.
     * Returns order items for a given order_id and status.
     */
    public function getProcurementOrderItemsListByOrderStatus() {
        $status   = $this->input->get('orderStatus');
        $order_id = $this->input->get('order_id');
        if (!$order_id) {
            echo json_encode(array('error' => 'Please Select Status'));
            return;
        }
        $item_data = $this->production_unit_model->getOrdersByItems($order_id, $status);
        $pr        = array(array('item_data' => $item_data));
        echo json_encode($pr);
    }
}

