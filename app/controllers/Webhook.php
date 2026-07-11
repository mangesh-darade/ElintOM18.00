<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('file'); 
        $this->load->model('companies_model');
        $this->Settings = $this->site->get_setting();
        $this->lang->load('customers', $this->Settings->user_language);

    }
    public function cheerio_add_customer() {

        $companyData = $this->companies_model->getCompanyByID($this->Settings->default_biller);
        $customerGroupDetails = $this->get_customer_group_by_id($this->Settings->customer_group);
        $json = file_get_contents('php://input');
   		$data = json_decode($json, true);

        $companyData = [
        'group_id' => '3',
        'group_name' => 'customer',
        'customer_group_id' => $this->Settings->customer_group, 
        'customer_group_name' => $customerGroupDetails->name,
        'state' => $companyData->state,
        'country' => $companyData->country, 
        'state_code' => $companyData->state_code,
        'gst_state_code' => $companyData->gst_state_code,
        'name' => $data['name'],
        'phone' => preg_replace('/^91/', '', $data['mobile']) // Remove '91' only if it is at the start
        ];
        $MobileNo =  preg_replace('/^91/', '', $data['mobile']);
      // $MobileNo =  '7744010738';
       $mobiledetails =  $this->checkMobileno($MobileNo);
      
       if (empty($mobiledetails)) {
          $this->db->insert('companies', $companyData);
       }
         exit;
    }
    public function checkMobileno($mobileno) {
        $result = $this->site->checkMobilenoForCheerio($mobileno);
       if ($result) {
        return $result;
       }else {
        return false;
       }
    }
    public function get_customer_group_by_id($id) {
        $this->db->where('id', $id);
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
}
?>
