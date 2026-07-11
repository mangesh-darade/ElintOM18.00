<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller {

    /** @var bool Whether the phone saved on the current add-customer request was system-generated */
    private $customer_phone_is_system_generated = false;

    public function __construct() {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        }
        $this->lang->load('customers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');

        $this->load->model('pos_model');
        $this->pos_settings = $this->pos_model->getSetting();
    }

    public function index($action = NULL) {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $this->data['country'] = $this->site->getCountry();
        $this->data['settings'] = $this->site->get_setting();
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customers')));
        $meta = array('page_title' => lang('customers'), 'bc' => $bc);
        $this->page_construct('customers/index', $meta, $this->data);
    }

    public function getCustomers()
    {
        $this->sma->checkPermissions('index');
        $this->load->library('datatables');

        // Get custom field labels for customer
        // $cfields = $this->site->getCustomeFieldsLabel('customer');

        // Base columns
        $phone_column = 'phone';
        if ($this->db->field_exists('is_system_generated', 'companies')) {
            $phone_column = "IF(IFNULL(is_system_generated, 0) = 1, '', phone) AS phone";
        }
        $select_columns = [
            'id', 'company', 'name', 'email', $phone_column,
            'price_group_name', 'customer_group_name', 'gstn_no',
            'deposit_amount', 'award_points'
        ];

        // List grid only has cf1/cf2 columns; extra configured fields are for add/edit forms
        $select_columns[] = 'cf1';
        $select_columns[] = 'cf2';

        $this->datatables->select(implode(',', $select_columns));

        // Add computed columns
        $this->datatables->add_column('opening_deposit_balance', '');
        $this->datatables->add_column('closing_deposit_balance', '');
        $this->datatables->add_column('GiftCard', '');

        $this->datatables->from("companies")
            ->where('group_name', 'customer')
            ->add_column("Actions",
                "<div class=\"text-center\">
                    <a class=\"tip\" title='" . lang("list_deposits") . "' href='" . site_url('customers/deposits/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-money\"></i></a>
                    <a class=\"tip\" title='" . lang("Deposits_History") . "' href='" . site_url('customers/depositsHistory/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-history\"></i></a>
                    <a class=\"tip\" title='" . lang("add_deposit") . "' href='" . site_url('customers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus\"></i></a>
                    <a class=\"tip\" title='" . lang("list_addresses") . "' href='" . site_url('customers/addresses/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-location-arrow\"></i></a>
                    <a class=\"tip\" title='" . lang("edit_customer") . "' href='" . site_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>
                    <a href='#' class='tip po' title='<b>" . lang("delete_customer") . "</b>'
                        data-content=\"<p>" . lang('r_u_sure') . "</p>
                        <a class='btn btn-danger po-delete' href='" . site_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a>
                        <button class='btn po-close'>" . lang('no') . "</button>\"
                        rel='popover'>
                        <i class=\"fa fa-trash-o\"></i></a>
                </div>", "id");

        echo $this->datatables->generate();
    }

    public function view($id = NULL) {
        $this->sma->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyByID($id);
        if (!$this->data['customer']) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            redirect(site_url('customers'));
        }
        $cfields = $this->site->getCustomeFieldsLabel('customer');
        $this->data['custome_fields'] = $cfields['customer'];
        $this->load->view($this->theme . 'customers/view', $this->data);
    }

    public function add_quick() {

        $auto_customer_number = $this->isAutoCustomerNumberEnabled();
        if (!$this->applyAutoCustomerPhoneForAdd($auto_customer_number)) {
            return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
        }

        // $this->form_validation->set_rules('cf1', lang("PAN_Card"), 'is_unique[companies.cf1]');
        if (!$auto_customer_number) {
            $this->form_validation->set_rules('phone', lang("phone"), 'required|callback_phone_by_country');
        }
        $this->form_validation->set_rules('email', lang("email_address"), 'valid_email|is_unique[companies.email]');
        //$this->form_validation->set_rules('add_country', lang("Country Name"), 'trim|required');            
        //$this->form_validation->set_rules('state', lang("state"), 'trim|required');            
        // $this->form_validation->set_rules('state_code', lang("State Code"), 'trim|required');            
        //$this->form_validation->set_rules('statename', lang("State Name"), 'trim|required');
        //$this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');        

        $synch_customer_data = ($this->Settings->synch_customers) ? true : false;
        $country_select = trim((string) $this->input->post('country', true));
        $country_name = trim((string) $this->input->post('add_country', true));
        $country_for_postal = !empty($country_name) ? $country_name : $country_select;
        if (!empty($country_select) && $country_select !== 'other') {
            $country_for_postal = $country_select;
        }
        // if ($this->isPostalCodeRequiredByCountry($country_for_postal)) {
        //     $this->form_validation->set_rules('postal_code', lang('postal_code'), 'trim|required|exact_length[6]|numeric');
        // }

        if ($this->runCustomerAddFormValidation($auto_customer_number) == true) {

            $company = !empty($this->input->post('company')) ? $this->input->post('company') : '-';

            $country = $this->input->post('add_country');
            $state_name = $this->input->post('state');
            $state_parts = explode('~', $state_name);
            $state_name = $state_parts[0];
            $state_code = $this->input->post('state_code');
            $_SESSION["quick_customername"] = $this->input->post('name');
            $_SESSION["quick_customerphone"] = $this->input->post('phone');
            if ($this->input->post('country') == 'other' && $country_select != '') {

                $this->db->insert('country_master', ['name' => $country_select]);
                $country_id = $this->db->insert_id();
                $statedata = [
                    'country_id' => $country_id,
                    'code' => $state_code,
                    'name' => $state_name,
                ];
                $this->site->addstate($statedata);
            } else if (($this->input->post('state') == 'other' || $this->input->post('state') == '') && ($state_code != '' && $state_name != '' )) {

                $country_id = $this->site->getCountryId($country_select);
                $statedata = [
                    'country_id' => $country_id,
                    'code' => $state_code,
                    'name' => $state_name,
                ];
                $this->site->addstate($statedata);
            }

            $customer_group_id = $this->input->post('customer_group');
            $cg = $this->site->getCustomerGroupByID($customer_group_id);
            $customer_group_name = ($cg && isset($cg->name)) ? $cg->name : '';

            $price_group_id = $this->input->post('price_group') ? $this->input->post('price_group') : NULL;
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $price_group_name = ($this->input->post('price_group') && $pg && isset($pg->name)) ? $pg->name : NULL;

            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $customer_group_id,
                'customer_group_name' => $customer_group_name,
                'price_group_id' => $price_group_id,
                'price_group_name' => $price_group_name,
                'is_internal_customer' => $this->input->post('is_internal_customer') ? $this->input->post('is_internal_customer') : 'no',
                'company' => $company,
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'gstn_no' => $this->input->post('gstn_no'),
                'city' => $this->input->post('city'),
                'state' => $state_name,
                'state_code' => $state_code,
                'postal_code' => $this->input->post('postal_code'),
                'country' => $country_select,
                'phone' => $this->input->post('phone'),
                'pan_card' => $this->input->post('pan_card'),
                'dob' => $this->sma->fsd($this->input->post('dob')),
                'anniversary' => $this->sma->fsd($this->input->post('anniversary')),
                'dob_father' => $this->sma->fsd($this->input->post('dob_father')),
                'dob_mother' => $this->sma->fsd($this->input->post('dob_mother')),
                'dob_child1' => $this->sma->fsd($this->input->post('dob_child1')),
                'dob_child2' => $this->sma->fsd($this->input->post('dob_child2')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
            ];
            $this->appendIsSystemGeneratedToCustomerData($data, $auto_customer_number);
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data, $synch_customer_data)) {
            $this->syncCustomerAddressesAfterCompanySave($cid, $data);
            $_SESSION["quick_customerid"] = $cid;
            $this->session->set_flashdata('message', lang("customer_added"));
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;

            // Storing session data
            

            $redirect_url = trim((string) ($this->input->post('redirect_url') ?: $this->input->get('redirect_url')));
            if ($redirect_url !== '') {
                return redirect($redirect_url);
            }

            return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $customer_groups = $this->companies_model->getAllCustomerGroups();
            $this->data['customer_groups'] = $customer_groups ? $customer_groups : array();
            $this->data['states'] = $this->site->getAllStates();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->data['country'] = $this->site->getCountry();
            $this->data['settings'] = $this->site->get_setting();
            $cfields = $this->site->getCustomeFieldsLabel('customer');
            $this->data['custome_fields'] = $cfields['customer'];
            $user = $this->site->getUser();
            if ($this->Owner || $this->Admin) {
                $source_module = strtolower(trim((string) $this->input->get('source_module', true)));
                if ($source_module === '') {
                    $source_module = strtolower(trim((string) $this->input->post('source_module', true)));
                }
                if ($source_module === 'pos') {
                    $biller_details = $this->companies_model->getCompanyByID($this->pos_settings->default_biller);
                } else {
                    $biller_details = $this->companies_model->getCompanyByID($this->Settings->default_biller);
                }
            } else {
                $warehouse_ids = explode(',', $user->warehouse_id);
                $first_warehouse_id = trim($warehouse_ids[0]);
                $warehouseArr = $this->site->getWarehouseByID($first_warehouse_id);
                $warehouse = is_array($warehouseArr) ? reset($warehouseArr) : false;
                $biller_details = ($warehouse && !empty($warehouse->primary_biller_id))
                    ? $this->site->getCompanyByID($warehouse->primary_biller_id)
                    : $this->site->getCompanyByID($this->Settings->default_biller);
            }
            $this->data['biller'] = $biller_details;
            $country = (isset($_POST['country']) ? $_POST['country'] : $biller_details->country);
            $this->data['states'] = $this->site->getstates($country) ? $this->site->getstates($country) : array();
            $this->assignAutoCustomerNumberViewData($auto_customer_number);

            $this->load->view($this->theme . 'customers/add_quick', $this->data);
        }
    }

    public function add() {

        $this->sma->checkPermissions(false, true);

        $auto_customer_number = $this->isAutoCustomerNumberEnabled();
        if (!$this->applyAutoCustomerPhoneForAdd($auto_customer_number)) {
            return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
        }

        // $this->form_validation->set_rules('cf1', lang("PAN_Card"), 'is_unique[companies.cf1]');
        if (!$auto_customer_number) {
            $this->form_validation->set_rules('phone', lang("phone"), 'required|callback_phone_by_country');
        }
        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[companies.email]');
        //$this->form_validation->set_rules('add_country', lang("Country Name"), 'trim|required');            
        //$this->form_validation->set_rules('state', lang("state"), 'trim|required');            
        //$this->form_validation->set_rules('state_code', lang("State Code"), 'trim|required');            
        //$this->form_validation->set_rules('statename', lang("State Name"), 'trim|required');
        //$this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');        

        $synch_customer_data = ($this->Settings->synch_customers) ? true : false;
        $country_select = trim((string) $this->input->post('country', true));
        $country_name = trim((string) $this->input->post('add_country', true));
        $country_for_postal = !empty($country_name) ? $country_name : $country_select;
        if (!empty($country_select) && $country_select !== 'other') {
            $country_for_postal = $country_select;
        }
        // if ($this->isPostalCodeRequiredByCountry($country_for_postal)) {
        //     $this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');
        // }
        if ($this->runCustomerAddFormValidation($auto_customer_number) == true) {

            $company = !empty($this->input->post('company')) ? $this->input->post('company') : '-';

            $country = $this->input->post('add_country');
            $state_name = $this->input->post('state');
            $state_parts = explode('~', $state_name);
            $state_name = $state_parts[0];
            $state_code = $this->input->post('state_code');
            $company_city = $this->input->post('city');
            $company_postal = $this->input->post('postal_code') ? $this->input->post('postal_code') : '000000';

            $company_address = $this->input->post('address');
            $loc = $this->companyLocationFromPostedAddresses('000000');
            if ($loc !== null) {
                $state_name = $loc['state'];
                $state_code = $loc['state_code'];
                $country_select = $loc['country'];
                $country = $loc['country'];
                $company_city = $loc['city'];
                $company_postal = $loc['postal_code'];
                $company_address = $loc['address'];
            } elseif ($this->input->post('country') == 'other' && $country_select != '') {

                $this->db->insert('country_master', ['name' => $country_select]);
                $country_id = $this->db->insert_id();
                $statedata = [
                    'country_id' => $country_id,
                    'code' => $state_code,
                    'name' => $state_name,
                ];
                $this->site->addstate($statedata);
            } else if (($this->input->post('state') == 'other' || $this->input->post('state') == '') && ($state_code != '' && $state_name != '' )) {

                $country_id = $this->site->getCountryId($country_select);
                $statedata = [
                    'country_id' => $country_id,
                    'code' => $state_code,
                    'name' => $state_name,
                ];
                $this->site->addstate($statedata);
            }

            $customer_group_id = $this->input->post('customer_group');
            $cg = $this->site->getCustomerGroupByID($customer_group_id);
            $customer_group_name = ($cg && isset($cg->name)) ? $cg->name : '';

            $price_group_id = $this->input->post('price_group') ? $this->input->post('price_group') : NULL;
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $price_group_name = ($this->input->post('price_group') && $pg && isset($pg->name)) ? $pg->name : NULL;

            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $customer_group_id,
                'customer_group_name' => $customer_group_name,
                'price_group_id' => $price_group_id,
                'price_group_name' => $price_group_name,
                'company' => $company,
                'address' => $company_address,
                'vat_no' => $this->input->post('vat_no'),
                'gstn_no' => $this->input->post('gstn_no'),
                'city' => $company_city,
                'state' => $state_name,
                'state_code' => $state_code,
                'postal_code' => $company_postal,
                'country' => $country_select,
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'award_points' => $this->input->post('award_points'),
                'dob' => $this->sma->fsd($this->input->post('dob')),
                'anniversary' => $this->sma->fsd($this->input->post('anniversary')),
                'dob_father' => $this->sma->fsd($this->input->post('dob_father')),
                'dob_mother' => $this->sma->fsd($this->input->post('dob_mother')),
                'dob_child1' => $this->sma->fsd($this->input->post('dob_child1')),
                'dob_child2' => $this->sma->fsd($this->input->post('dob_child2')),
                'pan_card' => $this->input->post('pan_card'),
                'is_internal_customer' => $this->input->post('is_internal_customer') ? $this->input->post('is_internal_customer') : 'no',
                'source' => $this->input->post('sale_source'),
            ];


            if ($this->Settings->synced_data_sales) {
                $data['synced_data'] = $this->input->post('synced_data');
                $data['customer_url'] = $this->input->post('customer_url');
                $data['privatekey'] = $this->input->post('privatekey');
            }
            $this->appendIsSystemGeneratedToCustomerData($data, $auto_customer_number);
            $address_validation = $this->validateCustomerAddressRows($this->postedAddressRows());
            if ($address_validation !== true) {
                $this->session->set_flashdata('error', $address_validation);
                return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
            }
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data, $synch_customer_data)) {
            $this->syncCustomerAddressesAfterCompanySave($cid, $data);

            if ($this->Settings->synced_data_sales && $data['privatekey']) {

                $biller_details = $this->site->getCompanyByID($this->pos_settings->default_biller);
                $_SESSION['Send_customer'] = [
                    'status' => '1',
                    'suppliername' => $biller_details->name,
                    'supplierKey' => $this->Settings->api_privatekey,
                    'send_customer_url' => $this->input->post('customer_url') . '/api4/setSupplierKey',
                    'supplierURL' => base_url(),
                    'pivatekey' => $data['privatekey']
                ];
            }


            $this->session->set_flashdata('message', lang("customer_added"));
            $redirect_url = trim((string) $this->input->post('redirect_url', true));
            $redirect_path = preg_replace('#^index\.php/#i', '', trim(parse_url($redirect_url, PHP_URL_PATH), '/'));
            if ($redirect_url && preg_match('#(^|/)(pos|sales/add)(/|$)#i', $redirect_path)) {
                return redirect($redirect_url);
            }
            return redirect('Customers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $customer_groups = $this->companies_model->getAllCustomerGroups();
            $this->data['customer_groups'] = $customer_groups ? $customer_groups : array();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->data['country'] = $this->site->getCountry();
            $this->data['settings'] = $this->site->get_setting();
            // $this->data['biller'] = $this->companies_model->getCompanyByID($this->Settings->default_biller);
            $user = $this->site->getUser();
            $source_module = strtolower(trim((string) $this->input->get('source_module', true)));
            if ($source_module === '') {
                $source_module = strtolower(trim((string) $this->input->post('source_module', true)));
            }
            $requested_biller_id = (int) $this->input->get('biller_id', true);
            if ($requested_biller_id <= 0) {
                $requested_biller_id = (int) $this->input->post('biller_id', true);
            }
            if ($source_module === 'sales' && $requested_biller_id > 0) {
                $biller_details = $this->site->getCompanyByID($requested_biller_id);
            } elseif ($this->Owner || $this->Admin) {
                $biller_details = $this->companies_model->getCompanyByID($this->Settings->default_biller);
            } else {
                $warehouse_ids = explode(',', $user->warehouse_id);
                $first_warehouse_id = trim($warehouse_ids[0]);
                $warehouseArr = $this->site->getWarehouseByID($first_warehouse_id);
                $warehouse = is_array($warehouseArr) ? reset($warehouseArr) : false;
                $biller_details = ($warehouse && !empty($warehouse->primary_biller_id))
                    ? $this->site->getCompanyByID($warehouse->primary_biller_id)
                    : $this->site->getCompanyByID($this->Settings->default_biller);
            }
            $this->data['biller'] = $biller_details;
            $country = (isset($_POST['country']) ? $_POST['country'] : $biller_details->country);
            $this->data['states'] = $this->site->getstates($country) ? $this->site->getstates($country) : array();
            $cfields = $this->site->getCustomeFieldsLabel('customer');
            $this->data['custome_fields'] = $cfields['customer'];
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->assignAutoCustomerNumberViewData($auto_customer_number);
            if ($this->input->is_ajax_request()) {
                $this->data['type'] = $this->input->get('type') ?: $this->input->post('type');
                $this->load->view($this->theme . 'customers/add', $this->data);
            } else {
                $this->load->view($this->theme . 'customers/add', $this->data);
            }
        }
    }

    public function generate_auto_customer_phone() {
        if (!$this->isAutoCustomerNumberEnabled()) {
            $this->sma->send_json(['status' => false, 'message' => 'Auto Customer Number is disabled.']);
            return;
        }
        $warehouse_id = $this->getWarehouseIdForAutoCustomerNumber();
        $phone = $this->companies_model->generateUniqueAutoCustomerPhone($warehouse_id);
        if (!$phone) {
            $this->sma->send_json(['status' => false, 'message' => 'Unable to generate a unique customer phone number. Please try again.']);
            return;
        }
        $this->sma->send_json([
            'status' => true,
            'phone' => $phone,
            'warehouse_id' => $warehouse_id,
        ]);
    }

    public function phone_by_country($phone)
    {
        $digits_only = preg_replace('/\D+/', '', (string) $phone);
        $len = strlen($digits_only);

        if ($this->isAutoCustomerNumberEnabled()) {
            $is_generated = strtoupper(trim((string) $this->input->post('is_system_generated', true))) === 'TRUE';
            if ($is_generated) {
                if ($len < 1 || $len > 10) {
                    $this->form_validation->set_message(
                        'phone_by_country',
                        'Phone number must be up to 10 digits only.'
                    );
                    return false;
                }
                return true;
            }
        }

        $biller_phone = $this->getBillerPhoneValidationMeta();
        $country = $biller_phone['country'];
        $required_digits = $biller_phone['phone_digits'];
        if ($required_digits !== null && $len !== $required_digits) {
            $label = $country !== '' ? $country : lang('phone');
            $this->form_validation->set_message(
                'phone_by_country',
                'For ' . $label . ', phone number must be exactly ' . $required_digits . ' digits.'
            );
            return false;
        }

        return true;
    }

    /** Biller country + phone_digits for customer add/edit phone validation (non auto-generated). */
    private function getBillerPhoneValidationMeta()
    {
        $biller = $this->getBillerForCustomerForm();
        $country = $biller && !empty($biller->country)
            ? $this->normalizeCountryName($biller->country)
            : '';
        $digits = $country !== '' ? $this->getPhoneDigitsByCountry($country) : null;
        if ($digits === null) {
            $digits = 10;
        }
        return array(
            'country' => $country,
            'phone_digits' => (int) $digits,
        );
    }

    private function getBillerForCustomerForm()
    {
        $source_module = strtolower(trim((string) $this->input->post('source_module', true)));
        if ($source_module === '') {
            $source_module = strtolower(trim((string) $this->input->get('source_module', true)));
        }
        $requested_biller_id = (int) $this->input->post('biller_id', true);
        if ($requested_biller_id <= 0) {
            $requested_biller_id = (int) $this->input->get('biller_id', true);
        }
        if ($source_module === 'pos') {
            return $this->companies_model->getCompanyByID($this->pos_settings->default_biller);
        }
        if ($source_module === 'sales' && $requested_biller_id > 0) {
            $requested_biller = $this->site->getCompanyByID($requested_biller_id);
            if (!empty($requested_biller)) {
                return $requested_biller;
            }
        }

        if ($this->Owner || $this->Admin) {
            return $this->companies_model->getCompanyByID($this->Settings->default_biller);
        }
        $user = $this->site->getUser();
        if (!empty($user->warehouse_id)) {
            $warehouse_ids = explode(',', $user->warehouse_id);
            $first_warehouse_id = trim($warehouse_ids[0]);
            if ($first_warehouse_id !== '') {
                $warehouseArr = $this->site->getWarehouseByID($first_warehouse_id);
                $warehouse = reset($warehouseArr);
                if ($warehouse && !empty($warehouse->primary_biller_id)) {
                    return $this->site->getCompanyByID($warehouse->primary_biller_id);
                }
            }
        }
        return $this->companies_model->getCompanyByID($this->Settings->default_biller);
    }

    public function getPhoneDigitsByCountry($country_name = null)
    {
        if (empty($country_name)) {
            return null;
        }

        $country_name = $this->normalizeCountryName($country_name);
        $aliases = [
            'UAE' => 'United Arab Emirates',
            'United Arab Emirates' => 'UAE',
            'INDIA' => 'India',
        ];

        $country = $this->db->select('phone_digits')
            ->where('name', $country_name)
            ->limit(1)
            ->get('country_master')
            ->row();

        if (empty($country) && isset($aliases[$country_name])) {
            $country = $this->db->select('phone_digits')
                ->where('name', $aliases[$country_name])
                ->limit(1)
                ->get('country_master')
                ->row();
        }

        if (empty($country)) {
            $country = $this->db->select('phone_digits')
                ->where('code', $country_name)
                ->limit(1)
                ->get('country_master')
                ->row();
        }

        if (!empty($country) && isset($country->phone_digits) && (int) $country->phone_digits > 0) {
            return (int) $country->phone_digits;
        }

        return null;
    }

    public function normalizeCountryName($country_name = '')
    {
        $country_name = trim(preg_replace('/\s+/', ' ', (string) $country_name));
        if ($country_name === '') {
            return '';
        }
        if (preg_match('/^(.+)\s+\1$/i', $country_name, $m)) {
            return trim($m[1]);
        }
        return $country_name;
    }

    public function getCountryPhoneDigits()
    {
        $country = trim((string) $this->input->get('country', true));
        $required_digits = $this->getPhoneDigitsByCountry($country);
        $this->sma->send_json([
            'status' => !empty($required_digits),
            'country' => $country,
            'phone_digits' => !empty($required_digits) ? (int) $required_digits : null,
        ]);
    }

    public function isPhoneValidByCountry($phone, $country)
    {
        $digits_only = preg_replace('/\D+/', '', (string) $phone);
        $len = strlen($digits_only);
        $required_digits = $this->getPhoneDigitsByCountry($country);
        if ($required_digits === null) {
            return true;
        }
        return $len === $required_digits;
    }

    public function edit($id = NULL) {

        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $company_details = $this->companies_model->getCompanyByID($id);
        if (!$company_details) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            redirect(site_url('customers'));
        }
        $original_row = $this->db->select('cf1')->where('id', $id)->get('sma_companies')->row();
        $original_value = $original_row ? $original_row->cf1 : '';
        if ($this->input->post('cf1') != $original_value) {
            $this->form_validation->set_rules('cf1', lang("PAN_Card"), 'is_unique[companies.cf1]');
        }
        if ($this->input->post('phone') != $company_details->phone) {
            $this->form_validation->set_rules('phone', lang("phone"), 'required|callback_phone_by_country|is_unique[companies.phone]');
        }
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('email', lang("email_address"), 'valid_email|is_unique[companies.email]');
        }

        /* $this->form_validation->set_rules('add_country', lang("Country Name"), 'trim|required');            
          $this->form_validation->set_rules('state', lang("state"), 'trim|required');
          $this->form_validation->set_rules('state_code', lang("State Code"), 'trim|required');
          $this->form_validation->set_rules('statename', lang("State Name"), 'trim|required');
          $this->form_validation->set_rules('postal_code', lang("Pincode"), 'required'); */

        if ($this->form_validation->run('customer/add') == true) {

            $company = !empty($this->input->post('company')) ? $this->input->post('company') : '-';
            $country = $this->input->post('add_country');
            $state_name = $this->input->post('statename');
            $state_code = $this->input->post('state_code');
            $company_city = $this->input->post('city');
            $company_postal = $this->input->post('postal_code') ? $this->input->post('postal_code') : '';

            $company_address = $this->input->post('address');
            $loc = $this->companyLocationFromPostedAddresses('');
            if ($loc !== null) {
                $state_name = $loc['state'];
                $state_code = $loc['state_code'];
                $country = $loc['country'];
                $company_city = $loc['city'];
                $company_postal = $loc['postal_code'];
                $company_address = $loc['address'];
            }

            $address_validation = $this->validateCustomerAddressRows($this->postedAddressRows());
            if ($address_validation !== true) {
                $this->session->set_flashdata('error', $address_validation);
                return redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('customers'));
            }

            if ($this->input->post('country') == 'other' && $country != '') {

                $this->db->insert('country_master', ['name' => $country]);
                $country_id = $this->db->insert_id();
                $statedata = [
                    'country_id' => $country_id,
                    'code' => $state_code,
                    'name' => $state_name,
                ];
                $this->site->addstate($statedata);
            } else if (($this->input->post('state') == 'other' || $this->input->post('state') == '') && ($state_code != '' && $state_name != '' )) {

                $country_id = $this->site->getCountryId($country);
                $statedata = [
                    'country_id' => $country_id,
                    'code' => $state_code,
                    'name' => $state_name,
                ];
                $this->site->addstate($statedata);
            }

            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $e_password = $this->input->post('eshop_pass');
            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => ($cg && isset($cg->name)) ? $cg->name : '',
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => ($this->input->post('price_group') && $pg && isset($pg->name)) ? $pg->name : NULL,
                'company' => $company,
                'address' => $company_address,
                'vat_no' => $this->input->post('vat_no'),
                'gstn_no' => $this->input->post('gstn_no'),
                'city' => $company_city,
                'state' => $state_name,
                'state_code' => $state_code,
                'postal_code' => $company_postal !== '' ? $company_postal : ($this->hasValidCustomerAddressRows($this->postedAddressRows()) ? '' : "0000"),
                'country' => $country,
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'award_points' => $this->input->post('award_points'),
                'dob' => $this->sma->fsd($this->input->post('dob')),
                'anniversary' => $this->sma->fsd($this->input->post('anniversary')),
                'dob_father' => $this->sma->fsd($this->input->post('dob_father')),
                'dob_mother' => $this->sma->fsd($this->input->post('dob_mother')),
                'dob_child1' => $this->sma->fsd($this->input->post('dob_child1')),
                'dob_child2' => $this->sma->fsd($this->input->post('dob_child2')),
                'pan_card' => $this->input->post('pan_card'),
                'is_internal_customer' => $this->input->post('is_internal_customer') ? $this->input->post('is_internal_customer') : 'no',
            ];
            if (!empty($e_password)):
                $data['password'] = md5($e_password);
            endif;

            if ($this->Settings->synced_data_sales) {
                $data['synced_data'] = $this->input->post('synced_data');
                $data['customer_url'] = $this->input->post('customer_url');
                $data['privatekey'] = $this->input->post('privatekey');
            } else {
                $data['synced_data'] = NULL;
                $data['customer_url'] = NULL;
                $data['privatekey'] = NULL;
            }
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data, $this->Settings->synch_customers)) {
            $this->syncCustomerAddressesAfterCompanySave($id, $data);

            if ($this->Settings->synced_data_sales && $data['privatekey']) {
                if ($company_details->privatekey != $data['privatekey']) {
                    $biller_details = $this->site->getCompanyByID($this->pos_settings->default_biller);
                    $_SESSION['Send_customer'] = [
                        'status' => '1',
                        'suppliername' => $biller_details->name,
                        'supplierKey' => $this->Settings->api_privatekey,
                        'send_customer_url' => $this->input->post('customer_url') . '/api4/setSupplierKey',
                        'supplierURL' => base_url(),
                        'pivatekey' => $data['privatekey']
                    ];
                }
            }

            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        } else {
            $this->data['phone'] = $company_details->phone;
            $this->data['relations'] = $this->pos_model->get_relations();
            $this->data['events'] = $this->pos_model->get_events();
            $this->data['customer'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $customer_groups = $this->companies_model->getAllCustomerGroups();
            $this->data['customer_groups'] = $customer_groups ? $customer_groups : array();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $country = (isset($_POST['country']) ? $_POST['country'] : $company_details->country);
            $this->data['states'] = $this->site->getstates($country) ? $this->site->getstates($country) : array();
            $this->data['country'] = $this->site->getCountry();

            $cfields = $this->site->getCustomeFieldsLabel('customer');
            $this->data['custome_fields'] = $cfields['customer'];
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['location_id'] = $this->companies_model->getCustomerAddress($id);
            $user = $this->site->getUser();
            if ($this->Owner || $this->Admin) {
                $biller_details = $this->companies_model->getCompanyByID($this->Settings->default_biller);
            } else {
                $warehouse_ids = explode(',', $user->warehouse_id);
                $first_warehouse_id = trim($warehouse_ids[0]);
                $warehouseArr = $this->site->getWarehouseByID($first_warehouse_id);
                $warehouse = is_array($warehouseArr) ? reset($warehouseArr) : false;
                $biller_details = ($warehouse && !empty($warehouse->primary_biller_id))
                    ? $this->site->getCompanyByID($warehouse->primary_biller_id)
                    : $this->site->getCompanyByID($this->Settings->default_biller);
            }
            $this->data['biller'] = $biller_details;
            $this->data['customer_addresses'] = $this->companies_model->getCompanyAddresses($id);
            if (!$this->data['customer_addresses']) {
                $this->data['customer_addresses'] = array();
            }
            $biller_country = $biller_details->country;
            $this->data['selected_country_name'] = isset($_POST['country']) ? $_POST['country'] : $biller_country;
            $this->data['biller_state_value'] = (isset($_POST['state']) ? $_POST['state'] : $biller_details->state . '~' . $biller_details->state_code);
            $biller_state_parts = explode('~', $this->data['biller_state_value']);
            $this->data['biller_state_name'] = isset($biller_state_parts[0]) ? trim($biller_state_parts[0]) : '';
            $this->data['biller_phone_meta'] = $this->getBillerPhoneValidationMeta();

            // $this->load->view($this->theme . 'customers/edit', $this->data);
            $this->load->view($this->theme . 'pos/CRM', $this->data);
        }
    }

    public function users($company_id = NULL) {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'customers/users', $this->data);
    }

    function add_user($company_id = NULL) {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active = $this->input->post('status');
            $notify = $this->input->post('notify');
            list($username, $domain) = explode("@", $this->input->post('email'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'company_id' => $company->id,
                'company' => $company->company,
                'group_id' => 3
            );
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang("user_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    public function import_csv() {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang("upload_file"), 'xss_clean');
        $csv_shipping_by_record = array();

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
            }

            if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

                $this->load->library('upload');

                $csv_upload_dir = FCPATH . 'assets/mdata/' . $this->Customer_assets . '/uploads/csv/';
                if (!is_dir($csv_upload_dir)) {
                    @mkdir($csv_upload_dir, 0777, true);
                }
                $config['upload_path'] = $csv_upload_dir;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = '2000';
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($csv_upload_dir . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $headerIdx = 0;
                for ($hi = 0; $hi < count($arrResult) && $hi < 5; $hi++) {
                    if (!empty($arrResult[$hi][0])) {
                        $firstCol = preg_replace('/^\xEF\xBB\xBF/', '', trim($arrResult[$hi][0]));
                        if (strcasecmp($firstCol, 'Company') === 0) {
                            $headerIdx = $hi;
                            break;
                        }
                    }
                }
                $csvHeaders = $arrResult[$headerIdx];
                if (!empty($csvHeaders[0])) {
                    $csvHeaders[0] = preg_replace('/^\xEF\xBB\xBF/', '', trim($csvHeaders[0]));
                }
                $csvDataRows = array_slice($arrResult, $headerIdx + 1);
                $csvNewFormat = (isset($csvHeaders[4]) && stripos(trim($csvHeaders[4]), 'gst') !== false)
                    || (isset($csvHeaders[6], $csvHeaders[12])
                        && stripos(trim($csvHeaders[6]), 'address') !== false
                        && stripos(trim($csvHeaders[12]), 'address') !== false);

                $deposit_col = 24;
                if ($csvNewFormat && !empty($csvHeaders)) {
                    foreach ($csvHeaders as $ci => $hdr) {
                        if (strcasecmp(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $hdr)), 'deposit') === 0) {
                            $deposit_col = (int) $ci;
                            break;
                        }
                    }
                }
                $final = array();
                if ($csvNewFormat) {
                    foreach ($csvDataRows as $value) {
                        if (!is_array($value)) {
                            continue;
                        }
                        $value = array_pad($value, max(26, count($csvHeaders)), '');
                        $allEmpty = true;
                        foreach ($value as $cell) {
                            if (trim((string) $cell) !== '') {
                                $allEmpty = false;
                                break;
                            }
                        }
                        if ($allEmpty) {
                            continue;
                        }
                        $final[] = array(
                            'company' => trim($value[0]),
                            'name' => trim($value[1]),
                            'email' => trim($value[2]),
                            'phone' => trim($value[3]),
                            'gstn_no' => trim($value[4]),
                            'vat_no' => trim($value[5]),
                            'address' => trim($value[6]),
                            'city' => trim($value[7]),
                            'state' => trim($value[8]),
                            'state_code' => trim($value[9]),
                            'postal_code' => trim($value[10]),
                            'country' => trim($value[11]),
                            'cf1' => trim($value[18]),
                            'cf2' => trim($value[19]),
                            'cf3' => trim($value[20]),
                            'cf4' => trim($value[21]),
                            'cf5' => trim($value[22]),
                            'cf6' => trim($value[23]),
                            'deposit_amount' => trim((string) (isset($value[$deposit_col]) ? $value[$deposit_col] : '')),
                            'csv_shipping' => array(
                                'line1' => trim($value[12]),
                                'city' => trim($value[13]),
                                'state' => trim($value[14]),
                                'state_code' => trim($value[15]),
                                'postal_code' => trim($value[16]),
                                'country' => trim($value[17]),
                            ),
                        );
                    }
                } else {
                    $keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'state_code', 'postal_code', 'country', 'gstn_no', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'deposit_amount');
                    foreach ($csvDataRows as $value) {
                        if (!is_array($value)) {
                            continue;
                        }
                        $value = array_pad($value, count($keys), '');
                        $final[] = array_combine($keys, array_slice($value, 0, count($keys)));
                    }
                }
                $used_phones_in_import = array();
                foreach ($final as $fk => $row) {
                    $phone_raw = trim((string) (isset($row['phone']) ? $row['phone'] : ''));
                    if ($phone_raw !== '') {
                        $phone_digits = preg_replace('/\D+/', '', $phone_raw);
                        if ($phone_digits === '' || !preg_match('/^\d{1,10}$/', $phone_digits)) {
                            $this->session->set_flashdata('error', 'Phone number must be numeric.');
                            redirect('customers');
                        }
                        if (isset($used_phones_in_import[$phone_digits])) {
                            $this->session->set_flashdata('error', 'Duplicate phone number in import file.');
                            redirect('customers');
                        }
                        $this->db->select('id')->from('companies')->where('phone', $phone_digits)->where('group_name', 'customer')->limit(1);
                        if ($this->db->get()->num_rows() > 0) {
                            $this->session->set_flashdata('error', 'Phone number already exists.');
                            redirect('customers');
                        }
                        $used_phones_in_import[$phone_digits] = true;
                        $final[$fk]['phone'] = $phone_digits;
                    }
                    $postal = trim((string) (isset($row['postal_code']) ? $row['postal_code'] : ''));
                    if ($postal !== '' && !preg_match('/^\d+$/', $postal)) {
                        $this->session->set_flashdata('error', 'Postal code must contain numbers only.');
                        redirect('customers');
                    }
                    $gstn = trim((string) (isset($row['gstn_no']) ? $row['gstn_no'] : ''));
                    if ($gstn === '-' || $gstn === '---' || strcasecmp($gstn, 'na') === 0 || strcasecmp($gstn, 'n/a') === 0) {
                        $gstn = '';
                    }
                    if ($gstn !== '' && !preg_match('/^\d{15}$/', $gstn)) {
                        $this->session->set_flashdata('error', 'GST No must be exactly 15 digits.');
                        redirect('customers');
                    }
                    $final[$fk]['gstn_no'] = $gstn;
                    $vat = trim((string) (isset($row['vat_no']) ? $row['vat_no'] : ''));
                    if ($vat === '-' || $vat === '---' || strcasecmp($vat, 'na') === 0 || strcasecmp($vat, 'n/a') === 0) {
                        $vat = '';
                    }
                    if ($vat !== '' && !preg_match('/^\d{15}$/', $vat)) {
                        $this->session->set_flashdata('error', 'Vat No must be exactly 15 digits.');
                        redirect('customers');
                    }
                    $final[$fk]['vat_no'] = $vat;
                    $dep = isset($row['deposit_amount']) ? trim((string) $row['deposit_amount']) : '';
                    if ($dep !== '' && !is_numeric($dep)) {
                        $this->session->set_flashdata('error', 'Deposit must contain numbers only.');
                        redirect('customers');
                    }
                    $final[$fk]['deposit_amount'] = ($dep !== '' && is_numeric($dep)) ? (float) $dep : '';
                }
                $default_biller = $this->companies_model->getCompanyByID($this->Settings->default_biller);
                if ($default_biller) {
                    foreach ($final as $fk => $row) {
                        if (trim((string) $row['country']) === '' && !empty($default_biller->country)) {
                            $final[$fk]['country'] = $default_biller->country;
                        }
                        if (trim((string) $row['state']) === '' && !empty($default_biller->state)) {
                            $final[$fk]['state'] = $default_biller->state;
                        }
                        if (trim((string) $row['state_code']) === '' && !empty($default_biller->state_code)) {
                            $final[$fk]['state_code'] = $default_biller->state_code;
                        }
                    }
                }
                if (!$this->isAutoCustomerNumberEnabled()) {
                    $rw = 2;
                    foreach ($final as $csv) {
                        //if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                        // $this->session->set_flashdata('error', lang("check_customer_email") . " (" . $csv['email'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                        // redirect("customers");
                        //  }
                        if ($csv['name'] == '' || $csv['phone'] == '' || $csv['state'] == '' || $csv['state_code'] == '') {
                            $this->session->set_flashdata('error', "Please required Name, Phone No, State, State Code");
                            redirect("customers");
                        }

                        $rw++;
                    }
                    foreach ($final as $record) {
                        if ($record['name'] != '' && $record['phone'] != '' && $record['state'] != '' && $record['state_code'] != '') {

                            $record['group_id'] = 3;
                            $record['group_name'] = 'customer';
                            $record['customer_group_id'] = 1;
                            $record['customer_group_name'] = 'General';
                            if (isset($record['csv_shipping'])) {
                                $csv_shipping_by_record[] = $record['csv_shipping'];
                                unset($record['csv_shipping']);
                            } else {
                                $csv_shipping_by_record[] = null;
                            }
                            $data[] = $record;
                        }
                    }
                } else {
                    foreach ($final as $key => $csv_row) {
                        if ($csv_row['name'] == '' || $csv_row['state'] == '' || $csv_row['state_code'] == '') {
                            $this->session->set_flashdata('error', "Please required Name, State, State Code (Phone is optional when Auto Customer Number is enabled)");
                            redirect("customers");
                        }

                        $phone_result = $this->resolveCsvImportCustomerPhone($csv_row['phone'], $used_phones_in_import, null);
                        if (!empty($phone_result['error'])) {
                            $this->session->set_flashdata('error', $phone_result['error']);
                            redirect("customers");
                        }

                        $final[$key]['phone'] = $phone_result['phone'];
                        if ($this->db->field_exists('is_system_generated', 'companies')) {
                            $this->ensureCompaniesIsSystemGeneratedColumn();
                            $final[$key]['is_system_generated'] = !empty($phone_result['is_system_generated']) ? TRUE : FALSE;
                        }
                    }
                    foreach ($final as $record) {
                        if ($record['name'] == '' || $record['state'] == '' || $record['state_code'] == '') {
                            continue;
                        }

                        $record['group_id'] = 3;
                        $record['group_name'] = 'customer';
                        $record['customer_group_id'] = 1;
                        $record['customer_group_name'] = 'General';
                        if (isset($record['csv_shipping'])) {
                            $csv_shipping_by_record[] = $record['csv_shipping'];
                            unset($record['csv_shipping']);
                        } else {
                            $csv_shipping_by_record[] = null;
                        }
                        $data[] = $record;
                    }
                }
                //$this->sma->print_arrays($data);
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                foreach ($data as $idx => $record) {
                    if (!is_array($record)) {
                        continue;
                    }
                    $phone = isset($record['phone']) ? trim((string) $record['phone']) : '';
                    $name = isset($record['name']) ? trim((string) $record['name']) : '';
                    if ($phone === '' && $name === '') {
                        continue;
                    }
                    $this->db->select('id')->from('companies')->where('group_name', 'customer');
                    if ($phone !== '') {
                        $this->db->where('phone', $phone);
                    }
                    if ($name !== '') {
                        $this->db->where('name', $name);
                    }
                    $company_row = $this->db->order_by('id', 'DESC')->limit(1)->get()->row();
                    if (!$company_row) {
                        continue;
                    }
                    $company_id = (int) $company_row->id;
                    $this->saveDefaultBillingAddressFromCompany($company_id, $record);
                    if (!empty($csv_shipping_by_record[$idx]) && is_array($csv_shipping_by_record[$idx])) {
                        $ship = $csv_shipping_by_record[$idx];
                        $has_shipping_data = false;
                        foreach (array('line1', 'city', 'state', 'state_code', 'postal_code', 'country') as $ship_field) {
                            if (trim((string) (isset($ship[$ship_field]) ? $ship[$ship_field] : '')) !== '') {
                                $has_shipping_data = true;
                                break;
                            }
                        }
                        if ($has_shipping_data) {
                            $ship_row = array(
                                'id' => '',
                                'type' => 'Shipping',
                                'address_name' => $name !== '' ? $name : '-',
                                'line1' => trim((string) $ship['line1']),
                                'line2' => '',
                                'country' => trim((string) $ship['country']),
                                'state' => trim((string) $ship['state']),
                                'state_code' => trim((string) $ship['state_code']),
                                'city' => trim((string) $ship['city']),
                                'postal_code' => trim((string) $ship['postal_code']),
                            );
                            if ($this->isValidCustomerAddressRow($ship_row)) {
                                $this->saveCustomerAddressesFromArray($company_id, array($ship_row), array(), $record);
                            }
                        }
                    }
                }
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }

    public function delete($id = NULL) {
        $this->sma->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->session->set_flashdata('error', lang('customer_x_deleted'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
        $this->sma->storeDeletedData('companies', 'id', $id);
        if ($this->companies_model->deleteCustomer($id)) {
            echo lang("customer_deleted");
        } else {
            $this->sma->deleteTableDataById('companies', $id);
            $this->session->set_flashdata('warning', lang('customer_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    // public function suggestions($term = NULL, $limit = NULL) {
    //     // $this->sma->checkPermissions('index');
    //     if ($this->input->get('term')) {
    //         $term = $this->input->get('term', TRUE);
    //     }
    //     if (strlen($term) < 1) {
    //         return FALSE;
    //     }
    //     $limit = $this->input->get('limit', TRUE);
    //     $rows['results'] = $this->companies_model->getCustomerSuggestions($term, $limit);
    //     $this->sma->send_json($rows);
    // }
    public function suggestions($term = NULL, $limit = NULL) {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $user = $this->site->getUser();
        $location_type_id = null;
        $is_production_unit = false;
        if (!empty($user->warehouse_id)) {
            $warehouse_ids = explode(',', $user->warehouse_id);
            $first_warehouse_id = trim($warehouse_ids[0]);
            // Fetch warehouse
            $warehouse = $this->db->get_where('sma_warehouses', ['id' => $first_warehouse_id])->row();
            if (!empty($warehouse) && isset($warehouse->location_type)) {
                $location_type_id = $warehouse->location_type;
                // Fetch location type
                $location_type = $this->db->get_where('sma_location_type', ['id' => $location_type_id])->row();
                if (!empty($location_type) && isset($location_type->type)) {
                    $is_production_unit = ($location_type->type === 'Production Unit');
                }
            }
        }
        $limit = $this->input->get('limit', TRUE);
        if ($is_production_unit) {
            $rows['results'] = $this->companies_model->getCustomerSuggestionsforproductionunit($term, $limit, $is_production_unit);
        } else {
            $rows['results'] = $this->companies_model->getCustomerSuggestions($term, $limit);
        }
        $this->sma->send_json($rows);
    }

    public function getCustomer($id = NULL) {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        if (!$row) {
            $this->sma->send_json(array());
            return;
        }
        if($row->name == 'Walk in Customer'){
            $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->company != '-' ? $row->name : $row->name), 'company_name' => $row->name)));
        }else{
            $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->company != '-' ? $row->name : $row->name), 'company_name' => $row->company)));
        }
    }

    /**
     * 
     * @param type $id
     */
    public function getCustomereshop($id = NULL) {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        if (!$row) {
            $this->sma->send_json(array());
            return;
        }
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->name, 'company_name' => $row->name)));
    }

    public function get_customer_details($id = NULL) {
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json($row ? $row : array());
    }

    public function get_award_points($id = NULL) {
        $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        if (!$row) {
            $this->sma->send_json(array('ca_points' => 0));
            return;
        }
        $this->sma->send_json(array('ca_points' => $row->award_points));
    }

    public function customer_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_deposit') {

                $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,), 'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000'))));
        $this->excel->getActiveSheet()->SetCellValue('A1', 'Customer name');
        $this->excel->getActiveSheet()->SetCellValue('B1', 'Phone No ');
        $this->excel->getActiveSheet()->SetCellValue('C1', 'Customer Group');
        $this->excel->getActiveSheet()->SetCellValue('D1', 'Member Card No');
        $this->excel->getActiveSheet()->SetCellValue('E1', 'Flat No');
        $this->excel->getActiveSheet()->SetCellValue('F1', 'Amount ');
        $this->excel->getActiveSheet()->SetCellValue('G1', 'Supercash ');
        $this->excel->getActiveSheet()->SetCellValue('H1', 'Payment Mode');
        // $this->excel->getActiveSheet()->SetCellValue('I1', 'Deposit Type');

        $row = 2;
        foreach ($_POST['val'] as $id) {
            $customer = $this->companies_model->customerDeposit($id);

            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->name);
            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->sma->customerPhoneForDisplay($customer));
            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->customer_group_name);
            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->cf1);
            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->cf2);
            $this->excel->getActiveSheet()->SetCellValue('F' . $row);
            // $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->deposit_amount);
            $this->excel->getActiveSheet()->SetCellValue('G' . $row);
            $this->excel->getActiveSheet()->SetCellValue('H' . $row);
            // $this->excel->getActiveSheet()->SetCellValue('I' . $row,"services");
            $row++;
        }

        // $this->excel->getActiveSheet()->protectCells('B1:B'.$row);
        // $this->excel->getActiveSheet()->protectCells('A1:B1', 'PHP');
        // $this->excel->getActiveSheet()->getProtection()->setSheet(true); //->protectCells('A1:B1', 'PHP');
        
        // $filename = 'sample_customers_bulk_deposit' . date('Y_m_d_H_i_s');
        $filename = 'sample_customers_bulk_deposit';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        return $objWriter->save('php://output');
    }
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        $this->sma->storeDeletedData('companies', 'id', $id);
                        if (!$this->companies_model->deleteCustomer($id)) {
                            $this->sma->deleteTableDataById('companies', $id);
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang("customers_deleted"));
                    }
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);

                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,), 'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000'))));

                    $this->excel->getActiveSheet()->getStyle("A1:R1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:R1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Customers');


                    $this->excel->getActiveSheet()->setTitle(lang('customers'));

                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('state'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('postal_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I2', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('J2', lang('vat_no'));
                    $this->excel->getActiveSheet()->SetCellValue('K2', lang('GST No'));
                    $this->excel->getActiveSheet()->SetCellValue('L2', lang('deposit_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('M2', lang('Price Group'));
                    $this->excel->getActiveSheet()->SetCellValue('N2', lang('Customer Group'));
                    $this->excel->getActiveSheet()->SetCellValue('O2', lang('ccf1'));
                    $this->excel->getActiveSheet()->SetCellValue('P2', lang('ccf2'));
                    $this->excel->getActiveSheet()->SetCellValue('Q2', lang('ccf3'));
                    $this->excel->getActiveSheet()->SetCellValue('R2', lang('ccf4'));
                    $this->excel->getActiveSheet()->SetCellValue('S2', lang('ccf5'));
                    $this->excel->getActiveSheet()->SetCellValue('T2', lang('ccf6'));

                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->sma->customerPhoneForDisplay($customer));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->gstn_no);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $customer->deposit_amount);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->price_group_name);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->customer_group_name);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $customer->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                    PHP_EOL . ' as appropriate for your directory structure');
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

                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_selected"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        }
    }

    public function deposits($company_id = NULL) {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        if (!$this->data['company']) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            redirect(site_url('customers'));
        }
        $this->load->view($this->theme . 'customers/deposits', $this->data);
    }

    public function get_deposits($company_id = NULL) {
        $this->sma->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
                ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
                ->from("deposits")
                ->join('users', 'users.id=deposits.created_by', 'left')
                ->where($this->db->dbprefix('deposits') . '.company_id', $company_id)
                ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . site_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
                ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function add_deposit($company_id = NULL) {

        $this->sma->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);
        if (!$company) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            redirect(site_url('customers'));
        }

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
                // If seconds not provided, use current seconds
                if (strlen($date) == 16) { // Format: YYYY-MM-DD HH:MM (16 chars)
                    $date .= ':' . date('s');
                }
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $note = ($this->input->post('services-check')) ? 'services' : $this->input->post('note');

            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'super_cash' => $this->input->post('super_price'),
                'services' => $this->input->post('services-check'),
                'note' => $note,
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
            );

            // Updated total deposite
            $cdata = array(
                'deposit_amount' => ($company->deposit_amount + $this->input->post('amount'))
            );

            $depositLog = [
                "customer_id" => $company->id,
                "date" => $date,
                "descriptions" => "Add Amount",
                "amount" => $this->input->post('amount'),
                "cr_dr" => 'CR',
                "opening_balance" => ((bool) $company->deposit_amount ? $company->deposit_amount : 0),
                "closing_balance" => ((float) $company->deposit_amount + (float) $this->input->post('amount')),
                "created_by" => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            //redirect('customers');
            return redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        }

        if ($this->form_validation->run() == true && $deposit_id = $this->companies_model->addDeposit($data, $cdata)) {

            $depositLog["transaction_details"] = json_encode(["table_name" => "sma_deposits", "where" => ["id" => $deposit_id]]);

            $this->companies_model->set_customer_wallet_log($depositLog);

            $this->session->set_flashdata('message', lang("deposit_added"));

            $OpeningBalance = ((bool) $company->deposit_amount ? $company->deposit_amount : 0);

            //$company = $this->companies_model->getCompanyByID($company_id);
            //$log = $this->companies_model->getOPCLDeposit($company->id, date('Y-m-d', strtotime($date)));

            $_SESSION['Print_Deposite_Receipt'] = [
                'status' => '1',
                'customer_Details' => $company,
                'last_deposit' => $data,
                'openingBalance' => $OpeningBalance,
            ];

            return redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
        }
    }

    public function edit_deposit($id = NULL) {
        $this->sma->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
                // If seconds not provided, use current seconds
                if (strlen($date) == 16) { // Format: YYYY-MM-DD HH:MM (16 chars)
                    $date .= ':' . date('s');
                }
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
            );

            $cdata = array(
                'deposit_amount' => (($company->deposit_amount - $deposit->amount) + $this->input->post('amount'))
            );
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $cdata)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }

    public function delete_deposit($id) {
        $this->sma->checkPermissions(NULL, TRUE);

        if ($this->companies_model->deleteDeposit($id)) {
            echo lang("deposit_deleted");
        }
    }

    public function deposit_note($id = null) {
        $this->sma->checkPermissions('deposits', true);
        $deposit = $this->companies_model->getDepositByID($id);
        $this->data['customer'] = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }

    public function addresses($company_id = NULL) {
        $this->sma->checkPermissions('index', true);
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'customers/addresses', $this->data);
    }

    public function add_address($company_id = NULL) {
        $this->sma->checkPermissions('add', true);
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('address_name', lang("address_name"), 'required');
        $this->form_validation->set_rules('line1', lang("line1"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');

        if ($this->form_validation->run() == true) {
            $state_raw = $this->input->post('state');
            $state_parts = explode('~', $state_raw, 2);
            $state_name = isset($state_parts[0]) ? trim($state_parts[0]) : '';
            $state_code = isset($state_parts[1]) ? trim($state_parts[1]) : '';

            $data = array(
                'type' => $this->input->post('type'),
                'address_name' => $this->input->post('address_name'),
                'line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state' => $state_name,
                'state_code' => $state_code,
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone') ? $this->input->post('phone') : $company->phone,
                'company_id' => $company->id,
            );
        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $saved_id = (int) $this->db->insert_id();
            if ($data['type'] === 'Billing') {
                $sync_company = $this->shouldSyncCompanyOnAddressSave($company, false, $data, $saved_id);
                if ($sync_company) {
                    $this->syncCompanyLocationFromAddressRow($company->id, $data, '');
                }
            }
            $this->session->set_flashdata('message', lang("address_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['countries'] = $this->site->getCountry();
            $this->data['states'] = $this->site->getstates($company->country);
            $this->load->view($this->theme . 'customers/add_address', $this->data);
        }
    }

    public function edit_address($id = NULL) {
        $this->sma->checkPermissions('edit', true);
        $address = $this->companies_model->getAddressByID($id);
        $company = $this->companies_model->getCompanyByID($address->company_id);

        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('address_name', lang("address_name"), 'required');
        $this->form_validation->set_rules('line1', lang("line1"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');

        if ($this->form_validation->run() == true) {
            $state_raw = $this->input->post('state');
            $state_parts = explode('~', $state_raw, 2);
            $state_name = isset($state_parts[0]) ? trim($state_parts[0]) : '';
            $state_code = isset($state_parts[1]) ? trim($state_parts[1]) : '';

            $data = array(
                'type' => $this->input->post('type'),
                'address_name' => $this->input->post('address_name'),
                'line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state' => $state_name,
                'state_code' => $state_code,
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone') ? $this->input->post('phone') : ($address->phone ? $address->phone : $company->phone),
                'updated_at' => date('Y-m-d H:i:s'),
            );
        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $sync_company = $this->shouldSyncCompanyOnAddressSave($company, $address, $data, $id);
            if ($sync_company) {
                $this->syncCompanyLocationFromAddressRow($company->id, $data, '');
            }
            $this->session->set_flashdata('message', lang("address_updated"));
            redirect("customers");
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['address'] = $address;
            $this->data['company'] = $company;
            $this->data['countries'] = $this->site->getCountry();
            $this->data['states'] = $this->site->getstates($address->country);
            $this->load->view($this->theme . 'customers/edit_address', $this->data);
        }
    }

    public function delete_address($id) {
        $this->sma->checkPermissions('delete', TRUE);

        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang("address_deleted"));
            redirect("customers");
        }
    }

    public function getEmail() {
        $emailid = $this->input->get('emailid');
        $row = $this->companies_model->getCompanyByEmail($emailid);
        if (empty($row)) {
            echo 1;
        } else {
            echo 0;
        }
    }

    /** @return json for Customer 12-21-2019 */
    public function getGiftBalance() {
        $id = $this->input->get('id');
        $retrun_option = $this->companies_model->getGiftCard($id);
        $depositBalance = $this->companies_model->getOPCLDeposit($id);
        $response = ['giftcard' => $retrun_option,
            'opening_balance' => ($depositBalance && isset($depositBalance->opening_balance)) ? $depositBalance->opening_balance : 0,
            'closing_balance' => ($depositBalance && isset($depositBalance->closing_balance)) ? $depositBalance->closing_balance : 0,
        ];
        echo json_encode($response);
    }

    /**
     * Check mobile no register or not
     */
    public function checkMobileno() {
        $groupname = $_GET['groupname'];
        $mobileno = $_GET['mobileno'];
        $result = $this->site->checkMobileno($groupname, $mobileno);
        if ($result) {
            $response['status'] = "success";
            $response['id'] = $result->id;
            $response['name'] = $result->name;
            $response['phone'] = $result->phone;
        } else {
            $response['status'] = "error";
        }
        echo json_encode($response);
    }

    /**
     * Get State List Country Vise
     */
    public function getstates() {
        $country = $_GET['country'];
        $statedata = $this->site->getstates($country);

        if ($statedata) {
            $output = '<option value="">--Select State--</option>';
            foreach ($statedata as $statevalue) {
                $output .= '<option value="' . $statevalue->name . '~' . $statevalue->code . '">' . $statevalue->name . ' (' . $statevalue->code . ')</option>';
            }
            $output .= '<option value="other">Other</option>';
            $response['status'] = "success";
            $response['data'] = $output;
            echo json_encode($response);
        } else {
            $output = '<option value="">--Select State--</option>';
            $output .= '<option value="other">Other</option>';
            $response['status'] = "error";
            $response['data'] = $output;
            echo json_encode($response);
        }
    }

    /**
     * 
     * @param type $customerId
     */
    public function getdeposit($customerId) {
        $result = $this->companies_model->getDepositandGift($customerId);
        echo json_encode($result);
    }

    /**
     * Suplier Privatekey Notification
     */
    public function supplier_key() {

        $result = $this->companies_model->count_new_purchase();
        if (is_array($result)) {
            echo json_encode($result);
        } else {
            echo json_encode(['num' => 0]);
        }
    }

    public function supplier_key_accept() {
        $status = $this->input->post('status') ? $this->input->post('status') : $this->input->get('status');
        echo $this->companies_model->set_notification_order_status($status);
    }

    /**
     * End Suplier Privatekey Notification
     */

    /**
     *  Get Depostis History
     * @param type $company_id
     *
     */
    public function depositsHistory($company_id = NULL) {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'customers/deposits_history', $this->data);
    }

    /**
     * 
     * @param type $company_id
     */
    function get_deposits_history($company_id = NULL) {
        $this->sma->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
                ->select("payments.id, payments.date, sales.invoice_no, payments.reference_no, payments.amount, payments.cc_holder as balance ", false)
                ->from("payments")
                ->join('sales', 'sales.id=payments.sale_id', 'Inner')
                ->where($this->db->dbprefix('payments') . '.paid_by', 'deposit')
                ->where($this->db->dbprefix('sales') . '.customer_id', $company_id);

        echo $this->datatables->generate();
    }

    /**
     * End Deposit History
     */

    /**
     * Reachage Amount
     */
    public function getDepositreacharge() {

        $date = $this->sma->fld(trim($this->input->get('date')));

        $customerId = $_GET['customer_id'];

        $result = $this->companies_model->getTotalReacharge($date, $customerId);
        $useddeposit = $this->companies_model->getUseddeposit($date, $customerId);
        $response = [
            'amount' => $result,
            'useddeposit' => $useddeposit,
        ];
        echo json_encode($response);
    }

      //controller for bulk deposit

      public function getBulkDeposit() {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('deposit_file', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
            }

            if (isset($_FILES["deposit_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

                $this->load->library('upload');
                $config['upload_path'] = 'assets/mdata/'.$this->Customer_assets.'/uploads/csv/';
                $config['allowed_types'] = 'xls';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('deposit_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
                }

                $this->load->library('excel');
                $File = $_FILES['deposit_file']['tmp_name'];
                $inputFileType = PHPExcel_IOFactory::identify($File);
                $reader = PHPExcel_IOFactory::createReader($inputFileType);
                $reader->setReadDataOnly(true);
                $path = $File;
                $excel = $reader->load($path);

                $sheet = $excel->getActiveSheet()->toArray(null, true, true, true);
                $arrayCount = count($sheet);
                $arrResult = array();
                for ($i = 2; $i <= $arrayCount; $i++) {
                    $arrResult[] = $sheet[$i];
                }

                $keys = array('name', 'phone', 'customer_group_name', 'cf1','cf2','deposit_amount','super_cash','paid_by');

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
               
                $rw = 3;
                foreach ($final as $csv) {
                                       
                    if ($csv['name'] == '' || $csv['phone'] == '' ||  $csv['deposit_amount'] == '' ||  $csv['paid_by'] == '' ) {
                        $this->session->set_flashdata('error', "Please required Name, Phone No, deposit_amount, payment type");
                        redirect("customers");
                    }

                    $rw++;
                }
                $data= $final;

                $user = $this->session->userdata('user_id');

            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->importBulkDeposit($data,$user)) {
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/bulk_deposit', $this->data);
        }
    }

            /////////////////CRM functionality//////////////////

    // public function save_customer() {
    //     $this->load->library('form_validation');
    //         $formData = $this->input->post('formData');
    //         $id = isset($formData['id']) ? (int) $formData['id'] : 0;
    //         if (!empty($id)) {
    //             // Keep price group fields aligned using group name.
    //             $selected_group_name = isset($formData['price_group_name']) ? trim($formData['price_group_name']) : '';
    //             if ($selected_group_name !== '') {
    //                 $pg = $this->db->select('id,name')->where('name', $selected_group_name)->get('price_groups')->row();
    //                 if ($pg) {
    //                     $formData['price_group_id'] = (int) $pg->id;
    //                     $formData['price_group_name'] = $pg->name;
    //                 }
    //             }

    //             $location_id = isset($formData['location_id']) ? $formData['location_id'] : null;
    //             unset($formData['location_id']);

    //             $this->db->where('id', $id);
    //             if ($this->db->update('companies', $formData)) {
    //                 $this->companies_model->syncCustomersAddress($id, $location_id);
    //                 echo json_encode(['status' => true, 'message' => 'Customer details updated successfully!']);
    //             } else {
    //                 echo json_encode(['status' => false, 'message' => 'Failed to update customer details.']);
    //             }
    //         }
    // }
    public function save_customer() {
        $this->load->library('form_validation');
            $formData = $this->input->post('formData');
            // Insert into the database
            $id = $formData['id'];
            if (!empty($id)) {
                $posted_country = '';
                if (isset($formData['selected_country']) && $formData['selected_country'] !== '') {
                    $posted_country = $formData['selected_country'];
                } elseif (isset($formData['country']) && $formData['country'] !== '') {
                    $posted_country = $formData['country'];
                } else {
                    $posted_country = (string) $this->input->post('country', true);
                }
                $country = $this->normalizeCountryName($posted_country);
                $source_module = '';
                if (isset($formData['source_module'])) {
                    $source_module = strtolower(trim((string) $formData['source_module']));
                }
                if ($source_module === '') {
                    $source_module = strtolower(trim((string) $this->input->post('source_module', true)));
                }
                $phone = isset($formData['phone']) ? $formData['phone'] : '';
                $digits_only = preg_replace('/\D+/', '', (string) $phone);
                $phone_len = strlen($digits_only);
                if ($source_module === 'pos') {
                    $effective_biller = $this->companies_model->getCompanyByID($this->pos_settings->default_biller);
                } else {
                   
                    // List-customer edit and other non-POS save_customer flows use Settings default biller.
                    $effective_biller = $this->companies_model->getCompanyByID($this->Settings->default_biller);
                }
                $effective_country = !empty($effective_biller->country) ? $this->normalizeCountryName($effective_biller->country) : '';
                $effective_digits = $effective_country !== '' ? $this->getPhoneDigitsByCountry($effective_country) : null;
                if ($effective_digits === null) {
                    $effective_digits = 10;
                }
                $biller_phone_meta = array(
                    'country' => $effective_country,
                    'phone_digits' => (int) $effective_digits,
                );
                $required_digits = (int) $biller_phone_meta['phone_digits'];
                if ($required_digits <= 0) {
                    $required_digits = isset($formData['phone_digits']) ? (int) $formData['phone_digits'] : 0;
                }
                if ($required_digits <= 0) {
                    $required_digits = (int) $this->getPhoneDigitsByCountry($country);
                }
                if ($required_digits > 0 && $phone_len !== $required_digits) {
                    $msg = 'Phone number is invalid.';
                    if (!empty($biller_phone_meta['country'])) {
                        $msg = 'For ' . $biller_phone_meta['country'] . ', phone number must be exactly ' . $required_digits . ' digits.';
                    } elseif (!empty($country)) {
                        $msg = 'For ' . $country . ', phone number must be exactly ' . $required_digits . ' digits.';
                    }
                    echo json_encode(['status' => false, 'message' => $msg]);
                    return;
                }

                $phone_trimmed = trim((string) $phone);
                if ($phone_trimmed !== '' && $this->companies_model->customerPhoneExistsForOtherCustomer($phone_trimmed, $id)) {
                    echo json_encode(['status' => false, 'message' => lang('Customer_Phone_Already_Exists')]);
                    return;
                }

                $customer_addresses = isset($formData['customer_addresses']) && is_array($formData['customer_addresses'])
                    ? $formData['customer_addresses'] : array();
                $customer_addr_deleted = isset($formData['customer_addr_deleted']) && is_array($formData['customer_addr_deleted'])
                    ? $formData['customer_addr_deleted'] : array();

                $address_validation = $this->validateCustomerAddressRows($customer_addresses);
                if ($address_validation !== true) {
                    echo json_encode(['status' => false, 'message' => $address_validation]);
                    return;
                }

                $has_address_rows = $this->hasValidCustomerAddressRows($customer_addresses);
                $loc = $this->companyLocationFromAddressRows($customer_addresses, '');
                if ($loc !== null) {
                    $formData['address'] = $loc['address'];
                    $formData['city'] = $loc['city'];
                    $formData['state'] = $loc['state'];
                    $formData['state_code'] = $loc['state_code'];
                    $formData['country'] = $loc['country'];
                    $formData['postal_code'] = $loc['postal_code'];
                } else {
                    $formData['country'] = $country;
                }
                if (isset($formData['price_group_id']) && $formData['price_group_id'] !== '' && $formData['price_group_id'] !== null) {
                    $pg = $this->site->getPriceGroupByID((int) $formData['price_group_id']);
                    if ($pg) {
                        $formData['price_group_id'] = (int) $pg->id;
                        $formData['price_group_name'] = $pg->name;
                    }
                } else {
                    $formData['price_group_id'] = NULL;
                    $formData['price_group_name'] = NULL;
                }
                if (isset($formData['customer_group_id']) && $formData['customer_group_id'] !== '' && $formData['customer_group_id'] !== null) {
                    $cg = $this->site->getCustomerGroupByID((int) $formData['customer_group_id']);
                    if ($cg) {
                        $formData['customer_group_id'] = (int) $cg->id;
                        $formData['customer_group_name'] = $cg->name;
                    }
                } else {
                    $cg = $this->site->getCustomerGroupByID(1);
                    if ($cg) {
                        $formData['customer_group_id'] = (int) $cg->id;
                        $formData['customer_group_name'] = $cg->name;
                    } else {
                        $formData['customer_group_id'] = 1;
                        $formData['customer_group_name'] = 'General';
                    }
                }
                if ($this->db->field_exists('is_internal_customer', 'companies')) {
                    $internal_flag = isset($formData['is_internal_customer']) ? strtolower(trim((string) $formData['is_internal_customer'])) : 'no';
                    $formData['is_internal_customer'] = ($internal_flag === 'yes') ? 'yes' : 'no';
                } else {
                    unset($formData['is_internal_customer']);
                }
                unset($formData['id'], $formData['phone_digits'], $formData['selected_country'], $formData['location_id'], $formData['is_system_generated'], $formData['customer_addresses'], $formData['customer_addr_deleted'], $formData['source_module'], $formData['biller_id']);
                if ($this->db->field_exists('is_system_generated', 'companies')) {
                    $existing = $this->companies_model->getCompanyByID($id);
                    $old_phone_digits = $existing ? preg_replace('/\D+/', '', (string) $existing->phone) : '';
                    $new_phone_digits = preg_replace('/\D+/', '', (string) $phone);
                    if ($old_phone_digits !== $new_phone_digits) {
                        $formData['is_system_generated'] = FALSE;
                    }
                }
                $this->db->where('id', $id);
                if ($this->db->update('companies', $formData)) {
                    $company_row = $this->companies_model->getCompanyByID($id);
                    $company_data = $this->companyDataArrayFromCompanyRow($company_row);
                    if ($has_address_rows || !empty($customer_addr_deleted)) {
                        $this->saveCustomerAddressesFromArray($id, $customer_addresses, $customer_addr_deleted, $company_data);
                    } else {
                        $this->saveDefaultBillingAddressFromCompany($id, $company_data);
                    }
                    echo json_encode(['status' => true, 'message' => 'Customer details updated successfully!']);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Failed to update customer details.']);
                }
            }
    }
    public function getCustomerDetails($phone = NULL, $limit = NULL) {
        // $this->sma->checkPermissions('index');
        $term = '';
        if ($phone == 0 || $this->input->post('phone')) {
            $term = $this->input->post('phone');
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows = $this->companies_model->getCustomerDetails($term, $limit);
        if ($rows) {
            $rows->addresses = $this->formatCompanyAddressesForJson($rows->id);
        }
        $this->sma->send_json($rows);
    }
    public function getstatesCrm() {
        $country = $_GET['country'];
        $statedata = $this->site->getstatesCRM($country);

        if ($statedata) {
            $output = '<option value="">--Select State--</option>';
            foreach ($statedata as $statevalue) {
                $name = isset($statevalue->name) ? trim((string) $statevalue->name) : '';
                $code = isset($statevalue->code) ? trim((string) $statevalue->code) : '';
                $output .= '<option value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" data-code="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
            }
            $output .= '<option value="other">Other</option>';
            $response['status'] = "success";
            $response['data'] = $output;
            echo json_encode($response);
        } else {
            $output = '<option value="">--Select State--</option>';
            $output .= '<option value="other">Other</option>';
            $response['status'] = "error";
            $response['data'] = $output;
            echo json_encode($response);
        }
    }
    /////////////////////////////////// Whats app integration /////////////////////////////////////
    public function send_customer_reminder_on_whatsapp() {

        $customerId   = $this->input->get('id');
        $phone   = $this->input->get('phone');
        $data = [
            'id'            => $this->input->get('id'),
            'total_amount'  => $this->input->get('total_amount'),
            'paid'          => $this->input->get('paid'),
            'balance'       => $this->input->get('balance'),
            'used_amount'   => $this->input->get('used_amount'),
            'customer_name' => $this->input->get('customer_name'),
            'phone'         => $this->input->get('phone'),
            'site_name'         => $this->Settings->site_name
        ];
        // Simulate sending WhatsApp message
        $messageSent = $this->sma->send_customer_reminder_on_whatsapp($phone, $data);
        if ($messageSent) {
            echo json_encode(['status' => 'success', 'message' => 'WhatsApp message sent']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send WhatsApp message']);
        }
    }
     public function isPostalCodeRequiredByCountry($country_name = null)
    {
        if (empty($country_name) || $country_name === 'other') {
            return false;
        }
        $country_name = $this->normalizeCountryName($country_name);
        $aliases = [
            'UAE' => 'United Arab Emirates',
            'United Arab Emirates' => 'UAE',
            'INDIA' => 'India',
        ];

        $country = $this->db->select('postal_code')
            ->where('name', $country_name)
            ->limit(1)
            ->get('country_master')
            ->row();

        if (empty($country) && isset($aliases[$country_name])) {
            $country = $this->db->select('postal_code')
                ->where('name', $aliases[$country_name])
                ->limit(1)
                ->get('country_master')
                ->row();
        }

        if (empty($country)) {
            $country = $this->db->select('postal_code')
                ->where('code', $country_name)
                ->limit(1)
                ->get('country_master')
                ->row();
        }

        return (!empty($country) && isset($country->postal_code) && (int) $country->postal_code === 1);
    }

    private function runCustomerAddFormValidation($auto_customer_number) {
        if ($auto_customer_number) {
            return $this->form_validation->run('customer/add_auto');
        }
        return $this->form_validation->run('customer/add');
    }

    private function isAutoCustomerNumberEnabled() {
        if (!$this->db->field_exists('auto_customer_number', 'settings')) {
            return false;
        }
        return isset($this->Settings->auto_customer_number) && (int) $this->Settings->auto_customer_number === 1;
    }

    private function getWarehouseIdForAutoCustomerNumber() {
        if ($this->Owner || $this->Admin) {
            return (int) $this->Settings->default_warehouse;
        }
        $user = $this->site->getUser();
        if (!empty($user->warehouse_id)) {
            $warehouse_ids = explode(',', $user->warehouse_id);
            $first_warehouse_id = trim($warehouse_ids[0]);
            if ($first_warehouse_id !== '') {
                return (int) $first_warehouse_id;
            }
        }
        return (int) $this->Settings->default_warehouse;
    }

    private function applyAutoCustomerPhoneForAdd($auto_customer_number) {
        $this->customer_phone_is_system_generated = false;
        if (!$auto_customer_number) {
            return true;
        }
        $posted_phone = preg_replace('/\D+/', '', trim((string) $this->input->post('phone', true)));
        if ($posted_phone !== '') {
            if (!preg_match('/^\d{1,10}$/', $posted_phone)) {
                $this->session->set_flashdata('error', 'Phone number must be numeric and up to 10 digits.');
                return false;
            }
            $_POST['phone'] = $posted_phone;
            $this->customer_phone_is_system_generated = $this->postedPhoneIsSystemGenerated($posted_phone);
            return true;
        }
        $phone = $this->companies_model->generateUniqueAutoCustomerPhone($this->getWarehouseIdForAutoCustomerNumber());
        if (!$phone) {
            $this->session->set_flashdata('error', 'Unable to generate a unique customer phone number. Please try again.');
            return false;
        }
        $_POST['phone'] = $phone;
        $this->customer_phone_is_system_generated = true;
        return true;
    }

    private function postedPhoneIsSystemGenerated($posted_phone) {
        $flag = strtoupper(trim((string) $this->input->post('is_system_generated', true)));
        if ($flag !== 'TRUE') {
            return false;
        }
        $original = preg_replace('/\D+/', '', trim((string) $this->input->post('auto_phone_original', true)));
        if ($original === '') {
            return true;
        }
        return $posted_phone === $original;
    }

    private function ensureCompaniesIsSystemGeneratedColumn() {
        if ($this->db->field_exists('is_system_generated', 'companies')) {
            return;
        }
        $this->load->dbforge();
        $this->dbforge->add_column('companies', array(
            'is_system_generated' => array(
                'type' => 'BOOLEAN',
                'null' => TRUE,
                'default' => NULL,
            ),
        ));
    }

    private function appendIsSystemGeneratedToCustomerData(&$data, $auto_customer_number) {
        if (!$this->db->field_exists('is_system_generated', 'companies')) {
            $this->ensureCompaniesIsSystemGeneratedColumn();
        }
        if (!$this->db->field_exists('is_system_generated', 'companies')) {
            return;
        }
        $is_generated = $auto_customer_number && $this->customer_phone_is_system_generated;
        $data['is_system_generated'] = $is_generated ? TRUE : FALSE;
    }

    private function assignAutoCustomerNumberViewData($auto_customer_number) {
        $this->data['auto_customer_number'] = $auto_customer_number ? 1 : 0;
        if (!$auto_customer_number) {
            $this->data['auto_customer_phone'] = '';
            return;
        }
        $posted_phone = trim((string) $this->input->post('phone', true));
        if ($posted_phone !== '') {
            $this->data['auto_customer_phone'] = $posted_phone;
            return;
        }
        $phone = $this->companies_model->generateUniqueAutoCustomerPhone($this->getWarehouseIdForAutoCustomerNumber());
        $this->data['auto_customer_phone'] = $phone ? $phone : '';
    }

    /**
     * Resolve customer phone for CSV import when Auto Customer Number is enabled.
     *
     * @param string $csv_phone
     * @param array $used_phones_in_import
     * @param int|null $line_no
     * @return array{phone?:string,is_system_generated?:bool,error?:string}
     */
    private function resolveCsvImportCustomerPhone($csv_phone, array &$used_phones_in_import, $line_no = null) {
        $phone = preg_replace('/\D+/', '', trim((string) $csv_phone));
        $line_suffix = $line_no ? ' (' . lang('line_no') . ' ' . $line_no . ')' : '';

        if ($phone !== '') {
            if (!preg_match('/^\d{1,10}$/', $phone)) {
                return array('error' => 'Phone number must be numeric and up to 10 digits.' . $line_suffix);
            }
            $used_phones_in_import[$phone] = true;
            return array(
                'phone' => $phone,
                'is_system_generated' => false,
            );
        }

        $generated = $this->companies_model->generateUniqueAutoCustomerPhoneExcluding(
            $this->getWarehouseIdForAutoCustomerNumber(),
            array_keys($used_phones_in_import)
        );
        if (!$generated) {
            return array('error' => 'Unable to generate a unique customer phone number. Please try again.' . $line_suffix);
        }
        $used_phones_in_import[$generated] = true;
        return array(
            'phone' => $generated,
            'is_system_generated' => true,
        );
    }

    private function postedAddressRows() {
        $lines = $this->input->post('customer_addr_line');
        if (!is_array($lines)) {
            return array();
        }
        $post = function ($key, $i) {
            $v = $this->input->post($key);
            return (is_array($v) && isset($v[$i])) ? trim((string) $v[$i]) : '';
        };
        $rows = array();
        foreach ($lines as $i => $line1) {
            $state_parts = explode('~', $post('customer_addr_state', $i), 2);
            $state_code = $post('customer_addr_state_code', $i);
            if ($state_code === '' && isset($state_parts[1])) {
                $state_code = trim($state_parts[1]);
            }
            $rows[] = array(
                'type' => $post('customer_addr_type', $i),
                'address_name' => $post('customer_addr_address_name', $i),
                'line1' => trim((string) $line1),
                'line2' => $post('customer_addr_line2', $i),
                'country' => $post('customer_addr_country', $i),
                'state' => trim($state_parts[0]),
                'state_code' => $state_code,
                'city' => $post('customer_addr_city', $i),
                'postal_code' => $post('customer_addr_postal_code', $i),
            );
        }
        return $rows;
    }

    private function formatCompanyAddressesForJson($company_id) {
        $addresses = $this->companies_model->getCompanyAddresses($company_id);
        if (!$addresses || !is_array($addresses)) {
            return array();
        }
        $formatted = array();
        foreach ($addresses as $addr) {
            $db_type = isset($addr->type) ? trim((string) $addr->type) : '';
            $addr_type = 'Billing';
            if (strcasecmp($db_type, 'Shipping') === 0) {
                $addr_type = 'Shipping';
            } elseif (strcasecmp($db_type, 'Site') === 0) {
                $addr_type = 'Site';
            }
            $addr_city = isset($addr->city) ? trim((string) $addr->city) : '';
            if ($addr_city === '-') {
                $addr_city = '';
            }
            $addr_name = isset($addr->address_name) ? trim((string) $addr->address_name) : '';
            if ($addr_name === '-' || $addr_name === '') {
                $addr_name = '';
            }
            $formatted[] = array(
                'id' => (int) $addr->id,
                'type' => $addr_type,
                'address_name' => $addr_name,
                'line1' => isset($addr->line1) ? (string) $addr->line1 : '',
                'country' => isset($addr->country) ? (string) $addr->country : '',
                'state' => isset($addr->state) ? (string) $addr->state : '',
                'state_code' => isset($addr->state_code) ? trim((string) $addr->state_code) : '',
                'city' => $addr_city,
                'postal_code' => isset($addr->postal_code) ? trim((string) $addr->postal_code) : '',
                'is_default' => isset($addr->is_default) ? (int) $addr->is_default : 0,
            );
        }
        return $formatted;
    }

    private function normalizeAddressRowArray(array $row) {
        $state_raw = isset($row['state']) ? trim((string) $row['state']) : '';
        $state_parts = explode('~', $state_raw, 2);
        $state_code = isset($row['state_code']) ? trim((string) $row['state_code']) : '';
        if ($state_code === '' && isset($state_parts[1])) {
            $state_code = trim($state_parts[1]);
        }
        return array(
            'id' => isset($row['id']) ? trim((string) $row['id']) : '',
            'type' => isset($row['type']) ? trim((string) $row['type']) : '',
            'address_name' => isset($row['address_name']) ? trim((string) $row['address_name']) : '',
            'line1' => isset($row['line1']) ? trim((string) $row['line1']) : '',
            'line2' => isset($row['line2']) ? trim((string) $row['line2']) : '',
            'country' => isset($row['country']) ? trim((string) $row['country']) : '',
            'state' => trim($state_parts[0]),
            'state_code' => $state_code,
            'city' => isset($row['city']) ? trim((string) $row['city']) : '',
            'postal_code' => isset($row['postal_code']) ? trim((string) $row['postal_code']) : '',
        );
    }

    private function isValidCustomerAddressRow(array $row) {
        $row = $this->normalizeAddressRowArray($row);
        return in_array($row['type'], array('Shipping', 'Billing', 'Site'), true)
            && $row['address_name'] !== ''
            && $row['line1'] !== ''
            && $row['state'] !== '';
    }

    private function customerAddressRowIsEmpty(array $row) {
        $row = $this->normalizeAddressRowArray($row);
        return $row['line1'] === '' && $row['line2'] === '' && $row['address_name'] === '' && $row['type'] === ''
            && $row['country'] === '' && $row['state'] === '' && $row['city'] === '' && $row['postal_code'] === '';
    }

    private function validateCustomerAddressRows(array $rows) {
        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $row = $this->normalizeAddressRowArray($row);
            if ($this->customerAddressRowIsEmpty($row)) {
                continue;
            }
            if ($this->isValidCustomerAddressRow($row)) {
                continue;
            }
            $n = $i + 1;
            if (!in_array($row['type'], array('Shipping', 'Billing', 'Site'), true)) {
                return 'Please select address Type (Shipping, Billing or Site) for address row ' . $n . '.';
            }
            if ($row['address_name'] === '') {
                return 'Please enter Address Name for address row ' . $n . '.';
            }
            if ($row['line1'] === '') {
                return 'Please enter Address 1 for address row ' . $n . '.';
            }
            if ($row['state'] === '') {
                return 'Please select State for address row ' . $n . '.';
            }
        }
        return true;
    }

    private function hasValidCustomerAddressRows(array $rows) {
        foreach ($rows as $row) {
            if (is_array($row) && $this->isValidCustomerAddressRow($row)) {
                return true;
            }
        }
        return false;
    }

    private function getFirstBillingAddressRow(array $rows) {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row = $this->normalizeAddressRowArray($row);
            if ($row['type'] === 'Billing' && $this->isValidCustomerAddressRow($row)) {
                return $row;
            }
        }
        return null;
    }

    private function companyLocationFromAddressRow(array $row, $default_postal = '000000') {
        $row = $this->normalizeAddressRowArray($row);
        return array(
            'address' => $row['line1'],
            'city' => $row['city'],
            'state' => $row['state'],
            'state_code' => $row['state_code'],
            'country' => $row['country'],
            'postal_code' => $row['postal_code'] !== '' ? $row['postal_code'] : $default_postal,
        );
    }

    /** null = no usable Billing location from form; array = companies location from first valid Billing. */
    private function companyLocationFromAddressRows(array $rows, $default_postal = '000000') {
        if (!$this->hasValidCustomerAddressRows($rows)) {
            return null;
        }
        $billing = $this->getFirstBillingAddressRow($rows);
        if (!$billing) {
            // Do not blank companies location when payload has only Shipping/invalid Billing rows.
            return null;
        }
        return $this->companyLocationFromAddressRow($billing, $default_postal);
    }

    private function companyLocationFromPostedAddresses($default_postal = '000000') {
        return $this->companyLocationFromAddressRows($this->postedAddressRows(), $default_postal);
    }

    private function normalizeCompanyFieldValue($val) {
        $val = trim((string) $val);
        return ($val === '-' || $val === '') ? '' : $val;
    }

    private function addressRecordToRowArray($addr) {
        if (!$addr) {
            return array();
        }
        $type = 'Billing';
        if (isset($addr->type) && strcasecmp(trim((string) $addr->type), 'Shipping') === 0) {
            $type = 'Shipping';
            } elseif (isset($addr->type) && strcasecmp(trim((string) $addr->type), 'Site') === 0) {
            $type = 'Site';
        }
        $city = isset($addr->city) ? trim((string) $addr->city) : '';
        if ($city === '-') {
            $city = '';
        }
        return array(
            'id' => isset($addr->id) ? (string) $addr->id : '',
            'type' => $type,
            'address_name' => isset($addr->address_name) ? trim((string) $addr->address_name) : '',
            'line1' => isset($addr->line1) ? trim((string) $addr->line1) : '',
            'line2' => isset($addr->line2) ? trim((string) $addr->line2) : '',
            'country' => isset($addr->country) ? trim((string) $addr->country) : '',
            'state' => isset($addr->state) ? trim((string) $addr->state) : '',
            'state_code' => isset($addr->state_code) ? trim((string) $addr->state_code) : '',
            'city' => $city,
            'postal_code' => isset($addr->postal_code) ? trim((string) $addr->postal_code) : '',
        );
    }

    private function mergeAddressRowWithExisting(array $row, $existing_addr) {
        $row = $this->normalizeAddressRowArray($row);
        if (!$existing_addr) {
            return $row;
        }
        $existing = $this->normalizeAddressRowArray($this->addressRecordToRowArray($existing_addr));
        foreach (array('address_name', 'line1', 'line2', 'country', 'state', 'state_code', 'city', 'postal_code') as $key) {
            if ($row[$key] === '' && $existing[$key] !== '') {
                $row[$key] = $existing[$key];
            }
        }
        if ($row['type'] === '' && $existing['type'] !== '') {
            $row['type'] = $existing['type'];
        }
        return $row;
    }

    private function companyLocationMatchesAddressRecord($company_row, $addr) {
        if (!$company_row || !$addr) {
            return false;
        }
        $loc = $this->companyLocationFromAddressRow($this->addressRecordToRowArray($addr), '');
        $pairs = array(
            array($this->normalizeCompanyFieldValue($company_row->address), $this->normalizeCompanyFieldValue($loc['address'])),
            array($this->normalizeCompanyFieldValue($company_row->city), $this->normalizeCompanyFieldValue($loc['city'])),
            array($this->normalizeCompanyFieldValue($company_row->state), $this->normalizeCompanyFieldValue($loc['state'])),
            array($this->normalizeCompanyFieldValue($company_row->state_code), $this->normalizeCompanyFieldValue($loc['state_code'])),
            array($this->normalizeCompanyFieldValue($company_row->country), $this->normalizeCompanyFieldValue($loc['country'])),
            array($this->normalizeCompanyFieldValue($company_row->postal_code), $this->normalizeCompanyFieldValue($loc['postal_code'])),
        );
        foreach ($pairs as $pair) {
            if ($pair[0] !== $pair[1]) {
                return false;
            }
        }
        return true;
    }

    private function addressTypeFromRecord($addr) {
        $type = isset($addr->type) ? trim((string) $addr->type) : 'Billing';
        if (strcasecmp($type, 'Shipping') === 0) {
            return 'Shipping';
        }
        if (strcasecmp($type, 'Site') === 0) {
            return 'Site';
        }
        return 'Billing';
    }

    private function getFirstAddressRecordByType($company_id, $type, $exclude_id = 0) {
        $company_id = (int) $company_id;
        $exclude_id = (int) $exclude_id;
        $type = trim((string) $type);
        $this->db->from('addresses');
        $this->db->where('company_id', $company_id);
        if ($this->db->field_exists('type', 'addresses')) {
            $this->db->where('type', $type);
        } elseif (strcasecmp($type, 'Billing') !== 0) {
            // Legacy schemas without type column only support Billing semantics.
            return null;
        }
        if ($exclude_id > 0) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->order_by('is_default', 'DESC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->row();
    }

    private function getFirstBillingAddressRecord($company_id, $exclude_id = 0) {
        return $this->getFirstAddressRecordByType($company_id, 'Billing', $exclude_id);
    }

    private function addressTypeHasDefault($company_id, $type) {
        if (!$this->db->field_exists('type', 'addresses')) {
            return false;
        }
        $this->db->from('addresses');
        $this->db->where('company_id', (int) $company_id);
        $this->db->where('type', trim((string) $type));
        $this->db->where('is_default', 1);
        return $this->db->count_all_results() > 0;
    }

    /**
     * After deleting a default address of a given type, promote the next one of the same type.
     * Preference: first address with id > $after_id, otherwise the oldest (lowest id).
     */
    private function promoteNextDefaultAddressForTypeAfterDelete($company_id, $type, $after_id) {
        if (!$this->db->field_exists('type', 'addresses')) {
            // Legacy schemas handled elsewhere (single Billing semantics).
            return;
        }
        $company_id = (int) $company_id;
        $after_id   = (int) $after_id;

        // If some other default already exists for this type, keep it.
        if ($this->addressTypeHasDefault($company_id, $type)) {
            return;
        }

        $this->db->from('addresses');
        $this->db->where('company_id', $company_id);
        $this->db->where('type', trim((string) $type));
        if ($after_id > 0) {
            $this->db->where('id >', $after_id);
        }
        $this->db->order_by('id', 'ASC');
        $next = $this->db->get()->row();

        if (!$next && $after_id > 0) {
            // No address after deleted id; fall back to the first address of this type.
            $this->db->from('addresses');
            $this->db->where('company_id', $company_id);
            $this->db->where('type', trim((string) $type));
            $this->db->order_by('id', 'ASC');
            $next = $this->db->get()->row();
        }

        if ($next) {
            $this->db->where('id', (int) $next->id)->update('addresses', array('is_default' => 1));
        }
    }

    private function addressRecordIsFirstBilling($company_id, $address_id) {
        $address_id = (int) $address_id;
        if ($address_id <= 0) {
            return false;
        }
        $first = $this->getFirstBillingAddressRecord($company_id);
        return $first && (int) $first->id === $address_id;
    }

    private function syncCompanyLocationFromAddressRow($company_id, array $row, $default_postal = '') {
        $row = $this->normalizeAddressRowArray($row);
        // Never push partial/invalid rows into companies location fields.
        if ($row['type'] !== 'Billing' || !$this->isValidCustomerAddressRow($row)) {
            return;
        }
        $loc = $this->companyLocationFromAddressRow($row, $default_postal);
        $this->db->where('id', (int) $company_id);
        $this->db->update('companies', $loc);
    }

    private function syncCompanyLocationFromFirstBillingAddress($company_id, $default_postal = '') {
        $first = $this->getFirstBillingAddressRecord($company_id);
        if (!$first) {
            $this->db->where('id', (int) $company_id);
            $this->db->update('companies', array(
                'address' => '',
                'city' => '',
                'state' => '',
                'state_code' => '',
                'country' => '',
                'postal_code' => '',
            ));
            return;
        }
        $this->syncCompanyLocationFromAddressRow($company_id, $this->addressRecordToRowArray($first), $default_postal);
    }

    private function shouldSyncCompanyOnAddressSave($company_row, $existing_addr, array $row, $saved_id) {
        $row = $this->normalizeAddressRowArray($row);
        if ($row['type'] !== 'Billing') {
            return false;
        }
        if ($existing_addr) {
            if ($this->companyLocationMatchesAddressRecord($company_row, $existing_addr)) {
                return true;
            }
            return $this->addressRecordIsFirstBilling((int) $company_row->id, (int) $existing_addr->id);
        }
        return $this->addressRecordIsFirstBilling((int) $company_row->id, (int) $saved_id);
    }

    private function shouldSyncCompanyOnAddressDelete($company_row, $existing_addr) {
        if (!$existing_addr) {
            return false;
        }
        $type = isset($existing_addr->type) ? trim((string) $existing_addr->type) : 'Billing';
        if (strcasecmp($type, 'Billing') !== 0) {
            return false;
        }
        if ($this->companyLocationMatchesAddressRecord($company_row, $existing_addr)) {
            return true;
        }
        return $this->addressRecordIsFirstBilling((int) $company_row->id, (int) $existing_addr->id);
    }

    private function companyDataArrayFromCompanyRow($company_row) {
        if (!$company_row) {
            return array();
        }
        return array(
            'name' => isset($company_row->name) ? $company_row->name : '',
            'company' => isset($company_row->company) ? $company_row->company : '-',
            'phone' => isset($company_row->phone) ? $company_row->phone : '',
            'email' => isset($company_row->email) ? $company_row->email : '',
            'address' => isset($company_row->address) ? $company_row->address : '',
            'city' => isset($company_row->city) ? $company_row->city : '',
            'state' => isset($company_row->state) ? $company_row->state : '',
            'state_code' => isset($company_row->state_code) ? $company_row->state_code : '',
            'country' => isset($company_row->country) ? $company_row->country : '',
            'postal_code' => isset($company_row->postal_code) ? $company_row->postal_code : '',
        );
    }

    /** Build a Billing row for sma_addresses from companies table fields. */
    private function billingAddressRowFromCompanyData(array $company_data) {
        $name = trim((string) (isset($company_data['name']) ? $company_data['name'] : ''));
        $line1 = trim((string) (isset($company_data['address']) ? $company_data['address'] : ''));
        if ($line1 === '' || $line1 === '-') {
            $line1 = '-';
        }
        $state = trim((string) (isset($company_data['state']) ? $company_data['state'] : ''));
        if ($state === '-' || $state === '') {
            $state = '';
        }
        $city = trim((string) (isset($company_data['city']) ? $company_data['city'] : ''));
        if ($city === '-') {
            $city = '';
        }
        $country = trim((string) (isset($company_data['country']) ? $company_data['country'] : ''));
        if ($country === '-') {
            $country = '';
        }
        return array(
            'id' => '',
            'type' => 'Billing',
            'address_name' => $name !== '' ? $name : '-',
            'line1' => $line1,
            'line2' => '',
            'country' => $country,
            'state' => $state,
            'state_code' => trim((string) (isset($company_data['state_code']) ? $company_data['state_code'] : '')),
            'city' => $city,
            'postal_code' => trim((string) (isset($company_data['postal_code']) ? $company_data['postal_code'] : '')),
        );
    }

    /** When no address grid rows: insert or update default Billing from companies data. */
    private function saveDefaultBillingAddressFromCompany($company_id, array $company_data) {
        $row = $this->billingAddressRowFromCompanyData($company_data);
        if (!$this->isValidCustomerAddressRow($row)) {
            return;
        }
        $existing = $this->getFirstBillingAddressRecord($company_id);
        if ($existing) {
            $row['id'] = (string) $existing->id;
        }
        $this->saveCustomerAddressesFromArray((int) $company_id, array($row), array(), $company_data);
    }

    private function syncCustomerAddressesAfterCompanySave($company_id, array $company_data) {
        if ($this->hasValidCustomerAddressRows($this->postedAddressRows())) {
            $this->savePostedCustomerAddresses($company_id, $company_data);
            return;
        }
        $this->saveDefaultBillingAddressFromCompany($company_id, $company_data);
    }

    private function savePostedCustomerAddresses($company_id, $company_data = array()) {
        $rows = array();
        $ids = $this->input->post('customer_addr_id');
        foreach ($this->postedAddressRows() as $i => $row) {
            $addr_id = '';
            if (is_array($ids) && isset($ids[$i])) {
                $addr_id = trim((string) $ids[$i]);
            }
            $row['id'] = $addr_id;
            $rows[] = $row;
        }
        $deleted = $this->input->post('customer_addr_deleted');
        if (!is_array($deleted)) {
            $deleted = array();
        }
        $this->saveCustomerAddressesFromArray($company_id, $rows, $deleted, $company_data);
    }

    private function buildCustomerAddressRecord($company_id, array $row, array $company_data, $is_default) {
        $row = $this->normalizeAddressRowArray($row);
        $company_name = !empty($company_data['company']) ? $company_data['company'] : '-';
        $address_name = $row['address_name'] !== ''
            ? $row['address_name']
            : (!empty($company_data['name']) ? $company_data['name'] : '-');
        $phone = !empty($company_data['phone']) ? $company_data['phone'] : '-';
        $email = !empty($company_data['email']) ? $company_data['email'] : '';
        $addr = array(
            'company_id' => (int) $company_id,
            'company_name' => $company_name,
            'address_name' => $address_name,
            'line1' => $row['line1'],
            'line2' => $row['line2'] !== '' ? $row['line2'] : null,
            'city' => $row['city'] !== '' ? $row['city'] : '-',
            'postal_code' => $row['postal_code'] !== '' ? $row['postal_code'] : '',
            'state' => $row['state'] !== '' ? $row['state'] : '-',
            'country' => $row['country'] !== '' ? $row['country'] : '-',
            'phone' => $phone,
            'email_id' => $email,
            'state_code' => $row['state_code'],
            'is_default' => $is_default ? 1 : 0,
        );
        if ($this->db->field_exists('type', 'addresses')) {
            $addr['type'] = $row['type'];
        }
        return $addr;
    }

    private function saveCustomerAddressesFromArray($company_id, array $rows, array $deleted_ids, $company_data = array()) {
        $company_id = (int) $company_id;
        $now = date('Y-m-d H:i:s');

        // Track which types lost their default due to deletion so we can promote a new one.
        $types_needing_promote = array();

        foreach ($deleted_ids as $deleted_id) {
            $deleted_id = (int) $deleted_id;
            if ($deleted_id <= 0) {
                continue;
            }
            $existing_addr = $this->companies_model->getAddressByID($deleted_id);
            if (!$existing_addr || (int) $existing_addr->company_id !== $company_id) {
                continue;
            }
            if ((int) $existing_addr->is_default === 1) {
                $types_needing_promote[$this->addressTypeFromRecord($existing_addr)] = true;
            }
            $this->companies_model->deleteAddress($deleted_id);
        }

        $valid_rows = array();
        foreach ($rows as $row) {
            if (!is_array($row) || !$this->isValidCustomerAddressRow($row)) {
                continue;
            }
            $valid_rows[] = $this->normalizeAddressRowArray($row);
        }

        // First Billing, first Shipping and first Site from the posted rows become defaults.
        $billing_default_assigned = false;
        $shipping_default_assigned = false;
        $site_default_assigned = false;

        foreach ($valid_rows as $row) {
            $is_default = false;
            if ($row['type'] === 'Billing') {
                if (!$billing_default_assigned) {
                    $is_default = true;
                    $billing_default_assigned = true;
                }
            } elseif ($row['type'] === 'Shipping') {
                if (!$shipping_default_assigned) {
                    $is_default = true;
                    $shipping_default_assigned = true;
                }
                } elseif ($row['type'] === 'Site') {
                if (!$site_default_assigned) {
                    $is_default = true;
                    $site_default_assigned = true;
                }
            }

            $addr = $this->buildCustomerAddressRecord($company_id, $row, $company_data, $is_default);
            $addr_id = (int) $row['id'];
            if ($addr_id > 0) {
                if ($this->db->field_exists('updated_at', 'addresses')) {
                    $addr['updated_at'] = $now;
                }
                $this->companies_model->updateAddress($addr_id, $addr);
            } else {
                if ($this->db->field_exists('created_at', 'addresses')) {
                    $addr['created_at'] = $now;
                }
                if ($this->db->field_exists('updated_at', 'addresses')) {
                    $addr['updated_at'] = $now;
                }
                $this->companies_model->addAddress($addr);
            }
        }

        // After deletions and saves, ensure each affected type still has a default.
        if ($this->db->field_exists('type', 'addresses')) {
            foreach (array_keys($types_needing_promote) as $type) {
                $this->promoteNextDefaultAddressForTypeAfterDelete($company_id, $type, 0);
            }
        }
    }
    public function save_customer_address() {
        $company_id = (int) $this->input->post('company_id');
        $row = $this->input->post('address');
        if ($company_id <= 0 || !is_array($row)) {
            $this->sma->send_json(['status' => false, 'message' => 'Invalid request.']);
            return;
        }

        $company_row = $this->companies_model->getCompanyByID($company_id);
        if (!$company_row) {
            $this->sma->send_json(['status' => false, 'message' => 'Customer not found.']);
            return;
        }

        $validation = $this->validateCustomerAddressRows([$row]);
        if ($validation !== true) {
            $this->sma->send_json(['status' => false, 'message' => $validation]);
            return;
        }

        $row = $this->normalizeAddressRowArray($row);
        $addr_id = (int) $row['id'];
        $existing_addr = false;
        if ($addr_id > 0) {
            $existing_addr = $this->companies_model->getAddressByID($addr_id);
            if (!$existing_addr || (int) $existing_addr->company_id !== $company_id) {
                $this->sma->send_json(['status' => false, 'message' => 'Address not found.']);
                return;
            }
            $row = $this->mergeAddressRowWithExisting($row, $existing_addr);
        }

        $company_data = [
            'name' => $company_row->name,
            'company' => $company_row->company,
            'phone' => $company_row->phone,
            'email' => $company_row->email,
        ];

        $is_default = 0;
        if ($addr_id > 0 && $existing_addr) {
            $is_default = (int) $existing_addr->is_default;
        } elseif ($row['type'] === 'Billing' && !$this->addressTypeHasDefault($company_id, 'Billing')) {
            $is_default = 1;
        } elseif ($row['type'] === 'Shipping' && !$this->addressTypeHasDefault($company_id, 'Shipping')) {
            $is_default = 1;
            } elseif ($row['type'] === 'Site' && !$this->addressTypeHasDefault($company_id, 'Site')) {
            $is_default = 1;
        }

        $sync_company = $this->shouldSyncCompanyOnAddressSave($company_row, $existing_addr, $row, $addr_id);

        $addr = $this->buildCustomerAddressRecord($company_id, $row, $company_data, ($is_default === 1));
        $now = date('Y-m-d H:i:s');

        if ($addr_id > 0) {
            if ($this->db->field_exists('updated_at', 'addresses')) {
                $addr['updated_at'] = $now;
            }
            if (!$this->companies_model->updateAddress($addr_id, $addr)) {
                $this->sma->send_json(['status' => false, 'message' => 'Failed to update address.']);
                return;
            }
            $saved_id = $addr_id;
            $message = 'Address updated successfully.';
        } else {
            if ($this->db->field_exists('created_at', 'addresses')) {
                $addr['created_at'] = $now;
            }
            if ($this->db->field_exists('updated_at', 'addresses')) {
                $addr['updated_at'] = $now;
            }
            if (!$this->companies_model->addAddress($addr)) {
                $this->sma->send_json(['status' => false, 'message' => 'Failed to add address.']);
                return;
            }
            $saved_id = (int) $this->db->insert_id();
            $message = 'Address added successfully.';
            if ($row['type'] === 'Billing') {
                $sync_company = $this->shouldSyncCompanyOnAddressSave($company_row, false, $row, $saved_id);
            }
        }

        if ($sync_company) {
            // Sync companies from persisted Billing record to avoid blank writes
            // when posted payload is partial/legacy formatted.
            $saved_addr = $this->companies_model->getAddressByID($saved_id);
            if ($saved_addr && (int) $saved_addr->company_id === $company_id) {
                $this->syncCompanyLocationFromAddressRow($company_id, $this->addressRecordToRowArray($saved_addr), '');
            } else {
                $this->syncCompanyLocationFromAddressRow($company_id, $row, '');
            }
        }

        $this->sma->send_json([
            'status' => true,
            'message' => $message,
            'address_id' => $saved_id,
        ]);
    }

    public function delete_customer_address() {
        $company_id = (int) $this->input->post('company_id');
        $address_id = (int) $this->input->post('address_id');
        if ($company_id <= 0 || $address_id <= 0) {
            $this->sma->send_json(['status' => false, 'message' => 'Invalid request.']);
            return;
        }

        $company_row = $this->companies_model->getCompanyByID($company_id);
        if (!$company_row) {
            $this->sma->send_json(['status' => false, 'message' => 'Customer not found.']);
            return;
        }

        $existing_addr = $this->companies_model->getAddressByID($address_id);
        if (!$existing_addr || (int) $existing_addr->company_id !== $company_id) {
            $this->sma->send_json(['status' => false, 'message' => 'Address not found.']);
            return;
        }

        $sync_company = $this->shouldSyncCompanyOnAddressDelete($company_row, $existing_addr);

        if ($this->companies_model->deleteAddress($address_id)) {
            if ((int) $existing_addr->is_default === 1) {
                $this->promoteNextDefaultAddressForTypeAfterDelete($company_id, $this->addressTypeFromRecord($existing_addr), $address_id);
            }
            if ($sync_company) {
                $this->syncCompanyLocationFromFirstBillingAddress($company_id, '');
            }
            $this->sma->send_json(['status' => true, 'message' => 'Address deleted successfully.']);
            return;
        }

        $this->sma->send_json(['status' => false, 'message' => 'Failed to delete address.']);
    }

    public function set_default_customer_address() {
        $company_id = (int) $this->input->post('company_id');
        $address_id = (int) $this->input->post('address_id');
        if ($company_id <= 0 || $address_id <= 0) {
            $this->sma->send_json(['status' => false, 'message' => 'Invalid request.']);
            return;
        }

        $company_row = $this->companies_model->getCompanyByID($company_id);
        if (!$company_row) {
            $this->sma->send_json(['status' => false, 'message' => 'Customer not found.']);
            return;
        }

        $existing_addr = $this->companies_model->getAddressByID($address_id);
        if (!$existing_addr || (int) $existing_addr->company_id !== $company_id) {
            $this->sma->send_json(['status' => false, 'message' => 'Address not found.']);
            return;
        }

        if ((int) $existing_addr->is_default === 1) {
            $this->sma->send_json(['status' => true, 'message' => 'Address is already default.']);
            return;
        }

        $addr_type = $this->addressTypeFromRecord($existing_addr);

        $this->db->where('company_id', $company_id);
        if ($this->db->field_exists('type', 'addresses')) {
            $this->db->where('type', $addr_type);
        }
        $this->db->update('addresses', array('is_default' => 0));

        $this->db->where('id', $address_id);
        $this->db->where('company_id', $company_id);
        if (!$this->db->update('addresses', array('is_default' => 1))) {
            $this->sma->send_json(['status' => false, 'message' => 'Failed to set default address.']);
            return;
        }

        if (strcasecmp($addr_type, 'Billing') === 0) {
            $saved_addr = $this->companies_model->getAddressByID($address_id);
            if ($saved_addr) {
                $this->syncCompanyLocationFromAddressRow($company_id, $this->addressRecordToRowArray($saved_addr), '');
            }
        }

        $this->sma->send_json([
            'status' => true,
            'message' => 'Default address updated successfully.',
            'address_id' => $address_id,
            'type' => $addr_type,
        ]);
    }
}
    
 

        
