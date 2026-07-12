<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        // Load necessary libraries and models
        $this->load->library(['form_validation', 'datatables', 'upload']);
        $this->load->database();
        $this->load->model('orders_model');
        $this->load->model('sales_model');
        $this->load->model('eshop_model');
        $this->load->helper('sms');
        $this->load->library('sma');
        $this->load->model('site');
        $this->load->model('Whatsapp_model');
        $this->load->model('webshop_model');
        $this->load->helper('webshop_helper');
        $this->load->helper('url');
        $this->load->helper('file'); 
        $this->load->helper('text');
        $this->load->library('form_validation');
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $this->data['cart_items'] = $_SESSION['cart'];
            $this->data['cart_data'] = $this->webshop_model->get_cart_data();
        }
         $this->theme = $this->Settings->theme.'/views/';
      
        // Define paths for file uploads
        $this->digital_upload_path = 'files/' . $this->Customer_assets;
        $this->upload_path = 'assets/mdata/' . $this->Customer_assets . '/uploads/production_unit';
        $this->thumbs_path = 'assets/mdata/' . $this->Customer_assets . '/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';

        // Load settings
        $this->data['Settings'] = $this->Settings;
        $this->api_key = $this->Settings->whatsapp_api_key;
        $this->data['logo'] = true;
        $this->pos_settings = $this->site->get_pos_setting();
        $this->data['pos_settings'] = $this->pos_settings;
    }

    public function send_whatsapp_message()
    {
        // Get the receipt code from the URL
        $code = $this->input->get('code');
        $phone = $this->input->get('phone');
        $phone_system_generated = $this->input->get('phone_system_generated') === '1'|| $this->input->get('phone_system_generated') === 'true';
        $result = $this->sma->send_whatsapp_message($code, $phone, $phone_system_generated);
        if (is_array($result) && !empty($result['blocked'])) {
            echo json_encode(array('msg' => $result['msg']));
            return;
        }
        return $result;
    }

    /**
     * Find a file in uploads directory and subfolders (same logic as File_manager)
     */
    private function findFileInUploads($filename) {
        $filename = basename($filename);
        $upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/';
        $root_file = $upload_path . $filename;
        if (file_exists($root_file)) {
            return $root_file;
        }
        if (is_dir($upload_path)) {
            $dir = new DirectoryIterator($upload_path);
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                    $subfolder = $fileinfo->getFilename();
                    if (substr($subfolder, 0, 1) === '.' || $subfolder === 'thumbs') {
                        continue;
                    }
                    $subfolder_file = $upload_path . $subfolder . '/' . $filename;
                    if (file_exists($subfolder_file)) {
                        return $subfolder_file;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Create never-expiring short link for a file (compatible with File_manager::s resolver)
     */
    private function createShortLink($filename) {
        $payload = array('f' => basename($filename));
        $payload_b64 = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $secret = isset($this->config->config['encryption_key']) ? $this->config->config['encryption_key'] : 'sma-secret';
        $sig = hash_hmac('sha256', $payload_b64, $secret);
        $token = $payload_b64 . '.' . $sig;
        return site_url('file_manager/s/' . $token);
    }

    /**
     * API: Create WhatsApp share link for a file (returns JSON with wa.me URL)
     * POST: phone, file, message (optional)
     */
    public function share_file() {
        if (!$this->loggedIn) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Not logged in')));
            return;
        }

        $phone = $this->input->post('phone');
        $filename = $this->input->post('file');
        $message = $this->input->post('message');

        if (!$phone || !$filename) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Phone and file are required')));
            return;
        }

        // Validate file exists
        $file_path = $this->findFileInUploads($filename);
        if ($file_path === false || !file_exists($file_path)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'File not found')));
            return;
        }

        // Generate short link
        $shortUrl = $this->createShortLink($filename);

        // Clean phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 8) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Invalid phone')));
            return;
        }

        if (!$message) {
            $message = 'Check out this file: ' . basename($filename) . "\n" . $shortUrl;
        } else {
            $message .= "\n" . $shortUrl;
        }

        $wa = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'success','whatsapp_url'=>$wa,'url'=>$shortUrl)));
    }

    /**
     * API: Send WhatsApp message via Cheerio provider with short link to file
     * POST: phone, file, message (optional)
     */
    public function send_file() {
        if (!$this->loggedIn) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Not logged in')));
            return;
        }

        $phone = $this->input->post('phone');
        $filename = $this->input->post('file');
        $message = $this->input->post('message');

        if (!$phone || !$filename) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Phone and file are required')));
            return;
        }

        // Validate file exists
        $file_path = $this->findFileInUploads($filename);
        if ($file_path === false || !file_exists($file_path)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'File not found')));
            return;
        }

        // Generate short link and compose message (ensure only ONE URL)
        $shortUrl = $this->createShortLink($filename);
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $containsUrl = $message && preg_match('#https?://#i', $message);
        if (!$message) {
            $message = 'Check out this file: ' . basename($filename);
        }
        if (!$containsUrl) {
            $message .= "\n" . $shortUrl;
        }

        // POS file share: direct Cheerio text (not order template flow)
        $this->load->helper('cheerio_whatsapp');
        $resp = cheerio_whatsapp_send_direct_text($phone, $message);
        $this->output->set_content_type('application/json')->set_output(json_encode($resp));
    }

    /**
     * API: Send SMS with short link to a file using the same backend as receipt SMS
     * POST: phone, file, message(optional)
     */
    public function send_sms_file() {
        if (!$this->loggedIn) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Not logged in')));
            return;
        }
        $phone = $this->input->post('phone');
        $filename = $this->input->post('file');
        $message = $this->input->post('message');
        if (!$phone || !$filename) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'Phone and file are required')));
            return;
        }
        $file_path = $this->findFileInUploads($filename);
        if ($file_path === false || !file_exists($file_path)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'File not found')));
            return;
        }
        $shortUrl = $this->createShortLink($filename);
        // Ensure only one URL in SMS body
        $containsUrl = $message && preg_match('#https?://#i', $message);
        if (!$message) { $message = 'Please check file: '; }
        if (!$containsUrl) { $message .= $shortUrl; }
        // Use the same SMS backend as receipts via SMA helper
        if (method_exists($this->sma, 'SendSMS')) {
            $res = $this->sma->SendSMS($phone, $message, 'SALE_INVOICE');
            if (!empty($res)) {
                $Obj = json_decode($res);
                if (isset($Obj) && isset($Obj->Status) && $Obj->Status == 'Success') {
                    $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'success')));
                    return;
                }
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=> isset($Obj->Description)?$Obj->Description:'Failed to send SMS')));
                return;
            }
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'SMS send returned empty response')));
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status'=>'error','message'=>'SMS backend not available')));
        }
    }
    public function pdf_eshop_order($id = null, $view = null, $save_bufffer = null) {
        $this->load->model('orders_model');
       $this->load->model('eshop_model');
       if ($this->input->get('id')) {
           $code = $this->input->get('id');
       }
       $res = $this->eshop_model->validateRecieptEshopOrder($id);
       $_PID = $this->Settings->default_printer;
       $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
       $inv = $this->orders_model->getOrderByID($id);
       if (!$inv) {
           echo 'Order not found';
           return;
       }
       if ($this->data['default_printer']->tax_classification_view):
           $inv->rows_tax = $this->orders_model->getAllTaxOrderItems($id, $inv->return_id);
       endif;
       $this->data['taxItems'] = $this->orders_model->getAllTaxItemsGroup($id, $inv->return_id);
       $this->default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
       $this->data['default_currency'] = $this->default_currency;
       $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
       $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
       $this->data['payments'] = $this->orders_model->getPaymentsForOrder($id);
       $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
       $this->data['billerDetails'] = $this->orders_model->getOrderDetails($id);
       $this->data['user'] = $this->site->getUser($inv->created_by);
       $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
       $this->data['inv'] = $inv;
       $this->data['rows'] = $this->orders_model->getAllOrderItems($id);
       $this->data['return_sale'] = $inv->return_id ? $this->orders_model->getOrderByID($inv->return_id) : NULL;
       $this->data['return_rows'] = $inv->return_id ? $this->orders_model->getAllOrderItems($inv->return_id) : NULL;
       $name = lang("sale") . "_" . str_replace('/', '_', $inv->reference_no) . '_' . time() . ".pdf";
       $Settings = $this->Settings; //$this->site->get_setting();
      
       $html = $this->load->view($this->theme . 'orders/pdf_eshop_order', $this->data, true);

       if (!$this->Settings->barcode_img) {
           $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
       }

       $file_path = $this->sma->generate_pdf($html, $name, 'S', $this->data['biller']->invoice_footer);
       if (!empty($file_path)) {
           $file_path1 = FCPATH . $file_path;
           if (file_exists($file_path1)) :
               $_url = base_url($file_path);
               $this->sma->md($_url);
               exit;
           endif;
       }
    }
    public function send_otp_by_whatsapp() {
        $phone = '917744010738';
        $template_name = 'Darade_order_received';
        $otp = '123456'; // Example OTP, you can generate this dynamically
        // $response = $this->Whatsapp_model->send_order_whatsapp_message($phone,$order_id);
        $response = $this->Whatsapp_model->send_otp_by_whatsapp($phone,$otp);
        $data = is_string($response) ? json_decode($response, true) : $response;
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }
    //////////////////////////// Whats app Challan receipt////////////////////
    public function send_challan_whatsapp_message()
    {
        $code = $this->input->get('code');
        $phone = $this->input->get('phone');
        $phone_system_generated = $this->input->get('phone_system_generated') === '1'|| $this->input->get('phone_system_generated') === 'true';
        $result = $this->sma->send_challan_whatsapp_message($code, $phone, $phone_system_generated);
        if (is_array($result) && !empty($result['blocked'])) {
            echo json_encode(array('msg' => $result['msg']));
            return;
        }
        return $result;
    }

}
?>
