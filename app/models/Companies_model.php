<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Companies_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getAllBillerCompanies() {
        $q = $this->db->get_where('companies', array('group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllCustomerCompanies() {
        $q = $this->db->order_by('name', 'ASC')->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSMSCustomerList() {
        $q = $this->db->select('id,name,phone')->order_by('name', 'ASC')->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllSupplierCompanies() {
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllCustomerGroups() {
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyUsers($company_id) {
        $q = $this->db->get_where('users', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id) {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCompanyByEmail($email) {
        $q = $this->db->get_where('companies', array('email' => $email), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    private function ensureInternalCustomerColumn() {
        static $checked = false;
        if ($checked) {
            return;
        }
        if (!$this->db->field_exists('is_internal_customer', 'companies')) {
            $this->load->dbforge();
            $this->dbforge->add_column('companies', array(
                'is_internal_customer' => array('type' => 'VARCHAR', 'constraint' => 3, 'default' => 'no'),
            ));
        }
        $checked = true;
    }

    public function addCompany($data = array(), $synch_customer_data = false) {
        $this->ensureInternalCustomerColumn();
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
            if ($data['group_id'] == 3 && $synch_customer_data):
                $coustmer = $this->getCompanyByID($cid);
                $this->load->library('sma');
                $this->sma->SyncCustomerData($coustmer);
            endif;
            // Only set quick_customerid session for actual customers, not suppliers
            if ($data['group_id'] == 3) {
                $_SESSION["quick_customerid"] = $cid;
            }
            return $cid;
        }
        return false;
    }

    public function updateCompany($id, $data = array(), $synch_customer_data = false) {
        $this->ensureInternalCustomerColumn();
        $this->db->where('id', $id);
        if (!isset($data['is_synced'])):
            $data['is_synced'] = 0;
        endif;
        if ($this->db->update('companies', $data)) {
            if ($data['group_id'] == 3 && $data['is_synced'] != 1 && $synch_customer_data):
                $coustmer = $this->getCompanyByID($id);
                $this->load->library('sma');
                $this->sma->SyncCustomerData($coustmer);
            endif;
            return true;
        }
        return false;
    }

    public function addCompanies($data = array()) {
        if (!empty($data)) {
            foreach ($data as $itesms) {
                $this->db->insert('companies', $itesms);
                $customerId = $this->db->insert_id();
                $deposit_amount = isset($itesms['deposit_amount']) ? $itesms['deposit_amount'] : '';
                if ($deposit_amount !== '' && $deposit_amount !== null && is_numeric($deposit_amount) && (float) $deposit_amount > 0) {
                    $amount = (float) $deposit_amount;
                    $this->db->update('companies', array('deposit_amount' => $amount), array('id' => $customerId));
                    $deposit = [
                        'date' => date('Y-m-d H:i:s'),
                        'company_id' => $customerId,
                        'amount' => $amount,
                        'created_by' => $this->session->userdata('user_id'),
                    ];
                    $this->db->insert('deposits', $deposit);
                }
            }
            return true;
        }

        /* if ($this->db->insert_batch('companies', $data)) {
          return true;
          } */
        return false;
    }

    public function deleteCustomer($id) {
        if ($this->getCustomerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'customer')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteSupplier($id) {
        if ($this->getSupplierPurchases($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'supplier')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteBiller($id) {
        if ($this->getBillerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'biller'))) {
            return true;
        }
        return FALSE;
    }

    public function getBillerSuggestions($term, $limit = 10) {
        $this->db->select("id, company as text");
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'biller'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSuggestions($term, $limit = 10) {
        //$this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);
        //$this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $this->db->select("id, (CASE WHEN company IS NULL THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR IF(name LIKE '%" . $term . "%',name LIKE '%" . $term . "%',Replace(coalesce(name,''), ' ','') LIKE '%" . str_replace(" ", "", $term) . "%'  ) OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%' OR cf1 LIKE '%" . $term . "%'  OR cf2 LIKE '%" . $term . "%') ");

        // Enhanced filtering: ensure both group_name and group_id match customer criteria
        $this->db->where("(group_name = 'customer' AND group_id = 3)");
        
        $q = $this->db->get('companies', $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getSupplierSuggestions($term, $limit = 10) {
        //$this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);
        //$this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $this->db->select("id, (CASE WHEN company IS NULL THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR IF(name LIKE '%" . $term . "%',name LIKE '%" . $term . "%',Replace(coalesce(name,''), ' ','') LIKE '%" . str_replace(" ", "", $term) . "%'  ) OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");

        $q = $this->db->get_where('companies', array('group_name' => 'supplier'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSales($id) {
        $this->db->where('customer_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getBillerSales($id) {
        $this->db->where('biller_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getSupplierPurchases($id) {
        $this->db->where('supplier_id', $id)->from('purchases');
        return $this->db->count_all_results();
    }

    public function addDeposit($data, $cdata) {
        if ($this->db->insert('deposits', $data)) {
            if ($deposit_id = $this->db->insert_id()) {
                $this->db->update('companies', $cdata, array('id' => $data['company_id']));
                if ($this->db->affected_rows()) {
                    return $deposit_id;
                }
            }
        }
        return false;
    }

    public function updateDeposit($id, $data, $cdata) {
        if ($this->db->update('deposits', $data, array('id' => $id)) &&
                $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
            return true;
        }
        return false;
    }

    public function getDepositByID($id) {
        $q = $this->db->get_where('deposits', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDeposit($id) {
        $deposit = $this->getDepositByID($id);
        $company = $this->getCompanyByID($deposit->company_id);
        $cdata = array(
            'deposit_amount' => ($company->deposit_amount - $deposit->amount)
        );
        if ($this->db->update('companies', $cdata, array('id' => $deposit->company_id)) &&
                $this->db->delete('deposits', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getAllPriceGroups() {
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyAddresses($company_id) {
        $q = $this->db->get_where('addresses', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCustomerAddress($company_id) {
        $this->db->select('location_name');
        $this->db->where('company_id', (int) $company_id);
        $this->db->order_by('id', 'ASC');
        $row = $this->db->get('addresses', 1)->row();
        if ($row && isset($row->location_name) && $row->location_name !== null && $row->location_name !== '') {
            return $row->location_name;
        }
        return '';
    }

    public function syncCustomersAddress($company_id, $location_id) {
        $company_id = (int) $company_id;
        if ($location_id === '' || $location_id === null || $location_id === false) {
            $location_id = NULL;
        } 

        $customer = $this->getCompanyByID($company_id);
        if (!$customer) {
            return false;
        }

        $address_data = array(
            'location_name' => $location_id,
            'company_name' => !empty($customer->company) ? $customer->company : '-',
            'address_name' => !empty($customer->name) ? $customer->name : '',
            'line1' => !empty($customer->address) ? $customer->address : '-',
            'city' => !empty($customer->city) ? $customer->city : '-',
            'state' => !empty($customer->state) ? $customer->state : '-',
            'state_code' => !empty($customer->state_code) ? $customer->state_code : '',
            'country' => !empty($customer->country) ? $customer->country : '-',
            'phone' => !empty($customer->phone) ? $customer->phone : '-',
            'email_id' => !empty($customer->email) ? $customer->email : '',
            'postal_code' => !empty($customer->postal_code) ? $customer->postal_code : '',
        );

        $q = $this->db->get_where('addresses', array('company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            $address_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('company_id', $company_id);
            if ($this->db->update('addresses', $address_data)) {
                return true;
            }
            return false;
        }

        $data = array_merge($address_data, array(
            'company_id' => $company_id,
            'is_default' => 1,
        ));

        return $this->addAddress($data);
    }

    public function addAddress($data) {
        if ($this->db->insert('addresses', $data)) {
            return true;
        }
        return false;
    }

    public function updateAddress($id, $data) {
        if ($this->db->update('addresses', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteAddress($id) {
        if ($this->db->delete('addresses', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getAddressByID($id) {
        $q = $this->db->get_where('addresses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addApiNotify($data) {
        if ($this->db->insert('apinotify', $data)) {
            $aid = $this->db->insert_id();
            return $aid;
        }
        return false;
    }

    public function getCompanyCustomer($arr) {
        if (is_array($arr)):
            $q = $this->db->get_where('companies', $arr, 1);
            //    echo $this->db->last_query(); 
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        endif;

        return FALSE;
    }

    public function getAuthCustomer($param = NULL) {
        $this->db->select('id,name,phone,email');
        $loginid = isset($param['loginid']) && !empty($param['loginid']) ? $param['loginid'] : NULL;
        $pass = isset($param['pass']) && !empty($param['pass']) ? $param['pass'] : NULL;
        $pass_type = isset($param['pass_type']) && !empty($param['pass_type']) ? $param['pass_type'] : 'password';

        switch ($pass_type) {
            case 'pass_key':
                $where = "pass_key='$pass'";
                break;

            default:
                $where = "password='$pass' AND (email='$loginid' OR phone='$loginid' )";
                break;
        }
        $this->db->where($where);
        $this->db->limit(1, 0);
        $q = $this->db->get('companies');
        // $this->db->last_query(); 
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function addEshopPasswordToken($data = array()) {
        $this->db->delete('eshop_password_token', array('user_id' => $data['user_id']));
        if ($this->db->insert('eshop_password_token', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    public function validateEshopPasswordToken($param = array()) {

        $user_id = isset($param['user_id']) && !empty($param['user_id']) ? $param['user_id'] : NULL;
        $token = isset($param['token']) && !empty($param['token']) ? $param['token'] : NULL;
        $dt = date("Y-m-d H:i:s");
        if (empty($user_id) || empty($token)) :
            return false;
        endif;
        $this->db->where('user_id  =', $user_id);
        $this->db->where('token  =', $token);
        $this->db->where('status  =', 1);
        $this->db->where('token_end >=', $dt);
        $this->db->where('token_start<=', $dt);
        $q = $this->db->get('eshop_password_token');
        // echo $this->db->last_query(); 
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res;
        }
        return FALSE;
        return FALSE;
    }

    public function get_eshop_user($id = null, $fields = null) {
        $user_id = isset($id) && !empty((int) $id) ? $id : NULL;
        $fields = isset($fields) && !empty($fields) ? $fields : '*';
        if (empty((int) $user_id)):
            return false;
        endif;
        $this->db->select('*');
        $where = "user_id='$user_id'  ";
        $this->db->where($where);
        $this->db->limit(1, 0);
        $q = $this->db->get('eshop_user_details');
        $this->db->last_query();
        if ($q->num_rows() > 0) {
            $res = $q->result_array();
            $comp = $this->getCompanyByID($user_id);

            if (is_object($comp) && $fields == '*'):
                $res[0]['email'] = $comp->email;
                $res[0]['phone'] = $comp->phone;
                $res[0]['name'] = $comp->name;
            endif;
            return $res;
        }
        return FALSE;
    }

    public function set_billing_shiiping_info($id = null, $param = null) {
        $user_id = isset($id) && !empty((int) $id) ? $id : NULL;
        $res = $this->get_eshop_user($user_id);
        $act = isset($res[0]['id']) && !empty((int) $res[0]['id']) ? 'edit' : 'add';
        $param = isset($param) && is_array($param) ? $param : NULL;

        if (empty($param) || empty($user_id)):
            return false;
        endif;
        switch ($act) {
            case 'edit':
                $this->db->where('user_id', $user_id);
                if ($this->db->update('eshop_user_details', $param)) {
                    return true;
                }
                return false;
                break;

            case 'add':

                $param['user_id'] = $user_id;
                if ($this->db->insert('eshop_user_details', $param)) {
                    $cid = $this->db->insert_id();
                    return $cid;
                }
                return false;
                break;
        }
        return false;
    }

    //array('$user_photo'=>,'user_photo_path'=>$user_photo_path);

    public function set_photo($id, $param) {
        $user_id = isset($id) && !empty((int) $id) ? $id : NULL;
        if (empty($user_id)):
            return false;
        endif;
        $this->db->where('user_id', $user_id);
        if ($this->db->update('eshop_user_details', $param)) {
            return $param['user_photo_path'] . $param['user_photo'];
        }
        return false;
    }

    public function duplicateUser($fieldVal, $field, $userID = null) {
        if (!empty($fieldVal) && !empty($field)):
            $this->db->where($field, $fieldVal);
        endif;
        if (!empty($userID)):
            $this->db->where(" id != '" . $userID . "' ");
        endif;

        $this->db->limit(1, 0);
        $q = $this->db->get('companies');
        return $q->num_rows();
    }

    public function nonSyncCustmerCount() {
        $this->db->select("id");
        $q = $this->db->get_where('view_non_sync_custmer', array());

        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return false;
    }

    public function nonSyncCustmer($limit = 10) {
        $this->db->select("id");
        if (!empty($limit)):
            $this->db->limit($limit, 0);
        endif;
        $q = $this->db->get_where('view_non_sync_custmer', array());
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function checkApiNotify($arr) {
        if (is_array($arr)):
            $q = $this->db->get_where('apinotify', $arr, 1);
            if ($q->num_rows() > 0) {
                return true;
            }
        endif;

        return FALSE;
    }

    public function getBillerByID($id) {
        $q = $this->db->select('id,name,company,vat_no,address,city,state,state_code,postal_code,country,phone,email,invoice_footer,gstn_no')->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getOfflineDefaultBiller() {
        $q = $this->db->query('SELECT c.* FROM `sma_companies` c INNER JOIN `sma_settings` s ON s.offlinepos_biller = c.id');

        if ($q->num_rows() > 0) {

            return $q->row();
        }
        return FALSE;
    }

    /**
     * This method using get gift Card column to Customer list
     * @return type
     */
    public function getGiftCard($cust_id = null) {
        $array = array('customer_id' => $cust_id, 'balance >' => '0', 'expiry >=' => date('Y-m-d'));
        $get = $this->db->select('balance as giftbalance')
                        ->where($array)
                        ->order_by('balance', DESC)
                        ->limit(1)
                        ->get('sma_gift_cards')->row();

        return $get->giftbalance;
    }

    /** End get payment option * */

    /**
     * 
     * @param type $customerId
     * @return type
     */
    public function getDepositandGift($customerId) {
        $deposit = $this->db->select('deposit_amount')->where(['id' => $customerId])->get('companies')->row();


        $giftcard = $this->getGiftCardAmt($customerId);

        $reponse = [
            'deposit' => ($deposit->deposit_amount) ? round($deposit->deposit_amount, 2) : '',
            'giftcardqty' => $giftcard->giftqty,
            'giftcardAmt' => ($giftcard->giftbalance) ? $this->sma->formatMoney($giftcard->giftbalance) : '',
        ];
        return $reponse;
    }

    /**
     * 
     * @param type $cust_id
     * @return type
     */
    public function getGiftCardAmt($cust_id = null) {
        $array = array('customer_id' => $cust_id, 'balance >' => '0', 'expiry >=' => date('Y-m-d'));
        $get = $this->db->select('sum(balance)as giftbalance ,count(id) as giftqty')
                        ->where($array)
                        ->order_by('balance', DESC)
                        ->get('sma_gift_cards')->row();

        return $get;
    }

    /**
     * Get Employee Type List
     * @return type
     */
    public function getEmployeeTypes() {
        return $this->db->where('is_employee', 1)->get('groups')->result();
    }

    /**
     * Suplier Privatekey Notification
     */

    /**
     * Count New Notification
     * @return type
     */
    public function count_new_purchase() {
        $q = $this->db->select('id')->where(['notification_supplier !=' => NULL])->get('companies')->result();

        $data['num'] = count($q);

        foreach ($q as $items) {
            $data['suplier_id'][] = $items->id;
        }
        return $data;
    }

    /**
     * Removed Notification alert
     * @param type $status
     * @return type
     */
    public function set_notification_order_status($ids) {

        $getData = $this->db->select('id,notification_supplier')
                        ->where(['notification_supplier !=' => NULL])->where_in('id', $ids)
                        ->get('companies')->result();


        foreach ($getData as $items) {
            $getnItemsData = unserialize($items->notification_supplier);

            $data = [
                'privatekey' => $getnItemsData['privatekey'],
                'customer_url' => $getnItemsData['customer_url'],
                'notification_supplier' => NULL,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->where(['id' => $items->id])->update('companies', $data);
        }
    }

    /**
     * End Suplier Privatekey Notification
     */

    /**
     * 
     * @param type $customerID
     * @return type
     */
    public function getOPCLDeposit($customerID, $date = NULL) {
        $date = ($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
        $getData = $this->db->where(['customer_id' => $customerID, 'DATE(date)' => $date])
                        ->get('sma_customer_wallet_transactions')->row();

        return $getData;
    }

    /**
     * Get Total Reacharge Amount
     */
    public function getTotalReacharge($date, $customer_id) {
        $result = $this->db->select('COALESCE(sum(amount), 0) as totalAmt')->where(['Date(date)' => $date, 'company_id' => $customer_id])->get('sma_deposits')->row();


        return ($this->db->affected_rows() ? $result->totalAmt : '0');
    }

    /**
     * Get used Deposit
     */
    public function getUseddeposit($date, $customer_id) {

        $result = $this->db->select('COALESCE(sum(sma_payments.amount), 0) as totalAmt')
                        ->join('sma_sales', 'sma_sales.id = sma_payments.sale_id', 'inner')
                        ->where(['Date(sma_payments.date)' => $date, 'sma_sales.customer_id' => $customer_id, 'sma_payments.paid_by' => 'deposit'])->get('sma_payments')->row();
        return ($this->db->affected_rows() ? $result->totalAmt : '0');
    }

    public function set_customer_wallet_log(array $logData) {

        if (is_array($logData)) {

            $this->db->insert("customer_wallet_transactions", $logData);

            return $this->db->affected_rows();
        }

        return false;
    }
  
    public function customerDeposit($id) {
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
    
	public function importBulkDeposit($data = array(),$user)
	 {

        if (!empty($data)) {
            $i=0;
            foreach ($data as $itesms) {
                               
                $quary = "select sma_companies.id,deposit_amount from sma_companies where phone=".$itesms['phone'];
                $res= $this->db->query($quary);
                $id = $res->result()[0]->id;
                $deposit = $res->result()[0]->deposit_amount;
                if(!$deposit)
                {
                    $deposit = 0;
                }
                $su_cash = $data[$i]['super_cash'];
                $de_type = $data[$i]['paid_by'];
                $new_dep = $deposit + $data[$i]['deposit_amount'] + $su_cash;
                $amount = $data[$i]['deposit_amount'] + $su_cash;
                if( $su_cash==null){
                    $su_cash=0;
                }

                if($this->db->update('companies', array('deposit_amount' => $new_dep), array('id' => $id)))
                {
                  
                    $deposit_arr = [
                       // "date" => "CURRENT_TIMESTAMP",
                        "company_id" => $id,
                        "amount"=>$amount,
                        "paid_by"=>$de_type,
                        "note" => "services",
                        "created_by" => $user,
                        "updated_by" => 0,
                        "updated_at" =>NULL,
                        "super_cash" => $su_cash,
                        "services" => "0",
                        
                    ];
                    $this->db->insert('deposits', $deposit_arr);
                    $newId = $this->db->insert_id();


                    if($res)
                    {
                    $str = "'".'{"table_name":"sma_deposits","where":{"id": '.$newId.'}}'."'";
                     $sqlnew = "INSERT INTO `sma_customer_wallet_transactions`(`id`, `customer_id`, `date`, `descriptions`, `amount`, `cr_dr`, `transaction_details`, `opening_balance`, `closing_balance`, `updated_at`, `created_by`) VALUES (NULL,$id,CURRENT_TIMESTAMP,'Add Amount',$amount,'CR',$str,$deposit,$new_dep,CURRENT_TIMESTAMP,$user)";
                     $this->db->query($sqlnew);
                    }
                }
                else
                {
                    return false;
                }

                $i++;
            }
            // exit;
            return true;

        }
	
    }
    
    ///////////////////Customer Family Relation///////////////////
    public function getCustomerDetails($phone) {
        $q = $this->db->get_where('companies', array('phone' => $phone));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data = $row;
            }
            $data->location_id = $this->getCustomerAddress($data->id);
            return $data;
        }
        return FALSE;
    }
    public function getCustomerSuggestionsforproductionunit($term, $limit = 10, $only_synced = false) {
        $this->db->select("id, (CASE WHEN company IS NULL THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' 
            OR IF(name LIKE '%" . $term . "%', name LIKE '%" . $term . "%', Replace(coalesce(name,''), ' ','') LIKE '%" . str_replace(" ", "", $term) . "%'  )
            OR company LIKE '%" . $term . "%' 
            OR email LIKE '%" . $term . "%' 
            OR phone LIKE '%" . $term . "%' 
            OR cf1 LIKE '%" . $term . "%'  
            OR cf2 LIKE '%" . $term . "%') ");
        $this->db->where('group_name', 'customer');
        if ($only_synced) {
            $this->db->where('synced_data', 1);
        }
        $q = $this->db->get('companies', $limit);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }
    public function customerPhoneExists($phone) {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return false;
        }
        $q = $this->db->select('id')->where('phone', $phone)->limit(1)->get('companies');
        return $q->num_rows() > 0;
    }

    /**
     * Auto customer number: prefix + 3-digit warehouse id + 4 sequential digits (10 digits total).
     * Sequence is per warehouse (0001, 0002, 0003, ...).
     *
     * @param int|null $warehouse_id
     * @return string|false
     */
    public function generateUniqueAutoCustomerPhone($warehouse_id = null) {
        $warehouse_id = (int) $warehouse_id;
        if ($warehouse_id <= 0) {
            $warehouse_id = 1;
        }
        $warehouse_part = str_pad((string) ($warehouse_id % 1000), 3, '0', STR_PAD_LEFT);
        $prefix = '999' . $warehouse_part;

        $next_seq = $this->getNextAutoCustomerPhoneSequence($prefix);
        if ($next_seq > 9999) {
            return false;
        }

        for ($seq = $next_seq; $seq <= 9999; $seq++) {
            $phone = $prefix . sprintf('%04d', $seq);
            if (strlen($phone) !== 10) {
                continue;
            }
            if (!$this->customerPhoneExists($phone)) {
                return $phone;
            }
        }

        return false;
    }

    /**
     * Next 4-digit sequence for auto phones sharing the same 6-digit prefix.
     *
     * @param string $prefix
     * @return int
     */
    private function getNextAutoCustomerPhoneSequence($prefix) {
        $prefix = preg_replace('/\D+/', '', (string) $prefix);
        if (strlen($prefix) !== 6) {
            return 1;
        }

        $this->db->select('phone');
        $this->db->from('companies');
        $this->db->like('phone', $prefix, 'after');
        $this->db->where('CHAR_LENGTH(phone)', 10, false);
        $q = $this->db->get();

        $max_seq = 0;
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $digits = preg_replace('/\D+/', '', (string) $row->phone);
                if (strlen($digits) !== 10 || strpos($digits, $prefix) !== 0) {
                    continue;
                }
                $seq = (int) substr($digits, -4);
                if ($seq > $max_seq) {
                    $max_seq = $seq;
                }
            }
        }

        return $max_seq + 1;
    }
    public function customerPhoneExistsForOtherCustomer($phone, $exclude_id) {
        $phone = trim((string) $phone);
        if ($phone === '' || (int) $exclude_id <= 0) {
            return false;
        }
        $q = $this->db->select('id')
            ->where('phone', $phone)
            ->where('group_name', 'customer')
            ->where('id !=', (int) $exclude_id)
            ->limit(1)
            ->get('companies');
        return $q->num_rows() > 0;
    }
    /**
     * Same as generateUniqueAutoCustomerPhone but also skips numbers reserved in the current import batch.
     *
     * @param int|null $warehouse_id
     * @param array $exclude_phones
     * @return string|false
     */
    public function generateUniqueAutoCustomerPhoneExcluding($warehouse_id = null, $exclude_phones = array()) {
        $warehouse_id = (int) $warehouse_id;
        if ($warehouse_id <= 0) {
            $warehouse_id = 1;
        }
        $warehouse_part = str_pad((string) ($warehouse_id % 1000), 3, '0', STR_PAD_LEFT);
        $prefix = '999' . $warehouse_part;

        $exclude_set = array();
        foreach ((array) $exclude_phones as $p) {
            $digits = preg_replace('/\D+/', '', trim((string) $p));
            if ($digits !== '') {
                $exclude_set[$digits] = true;
            }
        }

        $next_seq = $this->getNextAutoCustomerPhoneSequence($prefix);
        foreach (array_keys($exclude_set) as $digits) {
            if (strlen($digits) !== 10 || strpos($digits, $prefix) !== 0) {
                continue;
            }
            $seq = (int) substr($digits, -4);
            if ($seq >= $next_seq) {
                $next_seq = $seq + 1;
            }
        }

        if ($next_seq > 9999) {
            return false;
        }

        for ($seq = $next_seq; $seq <= 9999; $seq++) {
            $phone = $prefix . sprintf('%04d', $seq);
            if (strlen($phone) !== 10) {
                continue;
            }
            if (!$this->customerPhoneExists($phone) && !isset($exclude_set[$phone])) {
                return $phone;
            }
        }

        return false;
    }

}


