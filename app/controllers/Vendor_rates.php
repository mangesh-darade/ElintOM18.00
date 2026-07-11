<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor_rates extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Vendor_rates_model');
    }

    /**
     * Vendor Rates Grid (Editable Row)
     */
    public function index()
    {
        $this->sma->checkPermissions('index');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['rates'] = $this->Vendor_rates_model->get_all_rates();
        $this->data['vendors'] = $this->Vendor_rates_model->get_vendor_suppliers();
        $this->data['job_works'] = $this->Vendor_rates_model->get_job_works();
        $this->data['products'] = $this->Vendor_rates_model->get_products();

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('vendor_rates'), 'page' => 'Vendor Rates')
        );
        $meta = array('page_title' => 'Vendor Rates', 'bc' => $bc);

        $this->page_construct('job_works/vendor_rates', $meta, $this->data);
    }

    /**
     * AJAX: variants by product
     * POST: product_id
     */
    public function get_variants_by_product()
    {
        $this->sma->checkPermissions('index');
        if ($this->input->method(TRUE) !== 'POST') {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Invalid request method'));
        }
        $product_id = (int) $this->input->post('product_id');
        if ($product_id <= 0) {
            $this->sma->send_json(array('status' => TRUE, 'variants' => array()));
        }
        $variants = $this->Vendor_rates_model->get_variants_by_product($product_id);
        $this->sma->send_json(array('status' => TRUE, 'variants' => $variants));
    }

    /**
     * AJAX: create/upsert vendor rate
     * POST: vendor, job_work, product_id, variant_id, rate_per_item
     */
    public function create_rate()
    {
        $this->sma->checkPermissions('index');
        if ($this->input->method(TRUE) !== 'POST') {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Invalid request method'));
        }

        $vendor_id = (int) $this->input->post('vendor');
        $job_work_id = (int) $this->input->post('job_work');
        $product_id = (int) $this->input->post('product_id');
        $variant_id = (int) $this->input->post('variant_id'); // 0 allowed = NA
        $rateRaw = trim((string) $this->input->post('rate_per_item'));

        if ($vendor_id <= 0 || $job_work_id <= 0 || $product_id <= 0) {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Please select Vendor, Job Work and Product.'));
        }

        if ($rateRaw === '' || !preg_match('/^\d+(\.\d+)?$/', $rateRaw)) {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Invalid rate. Numbers only.'));
        }
        $rate = (float) $rateRaw;
        if ($rate < 0) {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Rate must be >= 0'));
        }

        $result = $this->Vendor_rates_model->upsert_vendor_rate($vendor_id, $job_work_id, $product_id, $variant_id, $rate);
        if (!empty($result['status'])) {
            $this->sma->send_json(array('status' => TRUE, 'message' => $result['message'], 'id' => $result['id']));
        }
        $this->sma->send_json(array('status' => FALSE, 'message' => $result['message']));
    }

    /**
     * AJAX: update rate_per_item
     * POST: id, rate
     */
    public function update_rate()
    {
        $this->sma->checkPermissions('index');

        if ($this->input->method(TRUE) !== 'POST') {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Invalid request method'));
        }

        $id = (int) $this->input->post('id');
        $rateRaw = trim((string) $this->input->post('rate'));

        if ($id <= 0) {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Missing or invalid id'));
        }

        // Numeric only validation (supports decimals). Example: 10, 10.5, 0, 0.00
        if ($rateRaw === '' || !preg_match('/^\d+(\.\d+)?$/', $rateRaw)) {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Invalid rate value'));
        }

        $rate = (float) $rateRaw;
        if ($rate < 0) {
            $this->sma->send_json(array('status' => FALSE, 'message' => 'Rate must be >= 0'));
        }

        $ok = $this->Vendor_rates_model->update_rate($id, $rate);
        if ($ok) {
            // Keep display clean without trailing zeros.
            $rateDisplay = rtrim(rtrim(number_format($rate, 10, '.', ''), '0'), '.');
            $this->sma->send_json(array('status' => TRUE, 'message' => 'Rate updated', 'rate' => $rateDisplay));
        }

        $this->sma->send_json(array('status' => FALSE, 'message' => 'Rate not updated. Please try again.'));
    }
}

