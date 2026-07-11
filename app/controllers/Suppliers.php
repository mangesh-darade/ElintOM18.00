<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
        }
        $this->lang->load('suppliers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');

        $this->lang->load('settings', $this->Settings->user_language);
        $this->load->model('settings_model');
        $this->load->model('products_model');
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';

        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '2048';
    }

    function index($action = NULL)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $this->data['country'] = $this->site->getCountry();
        $this->data['settings'] = $this->site->get_setting();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('suppliers')));
        $meta = array('page_title' => lang('suppliers'), 'bc' => $bc);
        $this->page_construct('suppliers/index', $meta, $this->data);
    }

    function getSuppliers()
    {
        $this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select("id, company, name, email, phone, city, country, vat_no,gstn_no")
            ->from("companies")
            ->where('group_name', 'supplier')
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_supplier") . "' href='" . site_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a class=\"tip\" title='" . $this->lang->line("list_users") . "' href='" . site_url('suppliers/users/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-users\"></i></a> <a class=\"tip\" title='" . $this->lang->line("add_user") . "' href='" . site_url('suppliers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus-circle\"></i></a> <a href='javascript:void(0);' onclick='return delSupplier();' class='tip po' title='<b>" . $this->lang->line("delete_supplier") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    function view($id = NULL)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        if (!$this->data['supplier']) {
            $this->session->set_flashdata('error', lang('supplier_not_found'));
            redirect(site_url('suppliers'));
        }
        $this->load->view($this->theme.'suppliers/view',$this->data);
    }

    function add()
    {
        $this->sma->checkPermissions(false, true);
	    $this->form_validation->set_rules('name', lang("supplier_name"), 'required');
        //$this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');
        //$this->form_validation->set_rules('phone', lang("phone"), 'is_unique[companies.phone]');
        /*if($this->input->post('country') == 'India'){            
            $this->form_validation->set_rules('state', lang("state"), 'required');            
        } */
        
      //  $this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');
        $postal_required = false;
        $country_list = $this->site->getCountry();
        $settings = $this->site->get_setting();
        if (!empty($country_list)) {
            foreach ($country_list as $c) {
                if (strcasecmp($c->name, $settings->country_name) === 0) {
                    if (!empty($c->postal_code) && $c->postal_code != 0) {
                        $postal_required = true;
                    }
                    break;
                }
            }
        }
        if ($postal_required) {
            $this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');
        }
        if ($this->form_validation->run('biller/add') == true) {
         $company = !empty($this->input->post('company')) ? $this->input->post('company'):'-';
           
            if ($this->input->post('country') == 'other' && $this->input->post('add_country') != '') {
                $this->site->addCountry(['name' => $this->input->post('add_country')]);
                $country = $this->input->post('add_country');
            } else {
                $country = ($this->input->post('country') != 'other') ? $this->input->post('country') : NULL;
            }

            if (($this->input->post('state') == 'other' || $this->input->post('state') == '') && ($this->input->post('statecode') != '' && $this->input->post('statename') != '' )) {
                $countryid = $this->site->getCountryId($country);
                $state = $this->input->post('statename');
                $state_code = $this->input->post('statecode');
                $statedata = [
                    'country_id' => $countryid,
                    'code' => $state_code,
                    'name' => $state,
                ];
                $this->site->addstate($statedata);
            } else {
                if ($this->input->post('state') == 'other') {
                    $state = NULL;
                    $state_code = NULL;
                } else {
                    $state = $this->input->post('state');
                    $state_code = $this->site->getStateCodeFromName($state);
                }
            }


            $data = array('name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '4',
                'group_name' => 'supplier',
                'company' => $company,
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                
                'gstn_no' => $this->input->post('gstn_no'),
                'city' => $this->input->post('city'),
                'state' =>$state,// $this->input->post('state'),
                'state_code'=>$state_code, // $this->site->getStateCodeFromName($this->input->post('state')),
                
                'postal_code' => $this->input->post('postal_code') ? $this->input->post('postal_code') : "0000",
                'country' => $country, //$this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'location_id' => $this->input->post('location_id'),
            );

            // If not Owner/Admin, force location_id to user's warehouse
            if (!$this->Owner && !$this->Admin) {
                $wid = $this->session->userdata('warehouse_id');
                if (!empty($wid) && $wid !== '0') {
                    $data['location_id'] = $wid;
                }
            }
        } elseif ($this->input->post('add_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', $this->lang->line("supplier_added"));
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
            redirect('suppliers');
        } 
        
        else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            // $this->data['biller'] = $this->companies_model->getCompanyByID($this->Settings->default_biller);
            $this->data['states'] = $this->site->getAllStates();
            $this->data['country'] = $this->site->getCountry();
            $this->data['settings'] = $this->site->get_setting();
            $cfields = $this->site->getCustomeFieldsLabel('supplier') ;
            $this->data['custome_fields'] = $cfields['supplier'];
            $this->data['warehouses'] = $this->site->getWarehousesByLocationType('Vendor');
            $user = $this->site->getUser();
            if ($this->Owner || $this->Admin) {
                $biller_details = $this->companies_model->getCompanyByID($this->Settings->default_biller);
            } else {
                $warehouse_ids = explode(',', $user->warehouse_id);
                $first_warehouse_id = trim($warehouse_ids[0]);
                $warehouseArr = $this->site->getWarehouseByID($first_warehouse_id);
                // var_dump($warehouseArr);exit;
                $warehouse = is_array($warehouseArr) ? reset($warehouseArr) : false;
                $biller_details = ($warehouse && !empty($warehouse->primary_biller_id))
                    ? $this->site->getCompanyByID($warehouse->primary_biller_id)
                    : $this->site->getCompanyByID($this->Settings->default_biller);
            }

            // var_dump("biller_details",$biller_details);exit;
            $this->data['biller'] = $biller_details;
            $country = (isset($_POST['country']) ? $_POST['country'] : $biller_details->country);
            $this->data['states'] = $this->site->getstates($country) ? $this->site->getstates($country) : array();
            
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    function getWarehousesByLocationType() { 

        $warehouses = $this->site->getWarehousesByLocationType('Vendor');

        if ($warehouses) {
            echo json_encode([
                'status' => 'success',
                'warehouses' => $warehouses
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No warehouses found'
            ]);
        }
    }

    function add_warehouse() {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("code"), 'trim|is_unique[warehouses.code]|required');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('city', lang("city"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');
        $this->form_validation->set_rules('postal_code', lang("postal_code"), 'required');
        // $this->form_validation->set_rules('address', lang("address"), 'required');
        $this->form_validation->set_rules('userfile', lang("map_image"), 'xss_clean');

        if ($this->form_validation->run() == TRUE) {
            if (!empty($_FILES['userfile']['size']) && $_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = 'assets/mdata/' . $this->Customer_assets . '/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = '2000';
                $config['max_height'] = '2000';
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    echo json_encode([
                        'status'  => 'failure',
                        'message' => 'Error uplodaing image'
                    ]);
                    // redirect("system_settings/warehouses");
                }
    
                $map = $this->upload->file_name;
    
                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'assets/mdata/' . $this->Customer_assets . '/uploads/' . $map;
                $config['new_image'] = 'assets/mdata/' . $this->Customer_assets . '/uploads/thumbs/' . $map;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 76;
                $config['height'] = 76;
    
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
    
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            } else {
                $map = NULL;
            }

            $stateInfo = explode('~', $this->input->post('state'));
            $name = $this->input->post('name');
            $city = $this->input->post('city');
            $country = $this->input->post('country');
            $postal_code = $this->input->post('postal_code');

            $address = !empty($this->input->post('address')) ? $this->input->post('address') : "$name, $city $stateInfo[1] $country $postal_code ";
    
            $retail_production = $this->input->post('retail_production') == 'yes' ? 1 : 0;
            $batch_production = $this->input->post('batch_production') == 'yes' ? 1 : 0;
            $other_billers = $this->input->post('other_biller');
            // $other_billers = implode(',', $other_billers);
            if (is_array($other_billers)) {
                $other_billers = implode(',', $other_billers);
            } else {
                $other_billers = $other_billers;
            }

            $data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'state' => $stateInfo[0],
                'state_code' => $stateInfo[1],
                'address' => $address,
                'price_group_id' => $this->input->post('price_group'),
                'location_type' => $this->input->post('location_type'),
                'owned_by' => $this->input->post('owned_by'),
                'map' => $map,
                'retail_production' => $retail_production,
                'batch_production' => $batch_production,
                'contact_person_name' => $this->input->post('contact_person_name'),
                'address_line1' => $this->input->post('address_line1'),
                'address_line2' => $this->input->post('address_line2'),
                'primary_biller_id' => $this->input->post('primary_biller'),
                'other_biller_id' => $other_billers,
                'bill_prefix' => $this->input->post('bill_prefix'),
            ];
         
        } elseif ($this->input->post('add_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
            echo json_encode([
                'status'  => 'failure',
                'message' => 'Error adding warehouse.'
            ]);
            // redirect("system_settings/warehouses");
        }

        if ($this->form_validation->run() == TRUE && $this->settings_model->addWarehouse($data)) {
            $DataLog = array(
                'action_type' => 'Add',
                'product_id' => '',
                'quantity' => '',
                'action_reff_id' => $this->db->insert_id(),
                'action_affected_data' => json_encode($data),
                'action_comment' => 'Add warehouses',
            );

            $this->sma->setUserActionLog($DataLog);
            $this->session->set_flashdata('message', lang("warehouse_added"));
            // redirect("suppliers/add");
            // redirect("suppliers/add");
            echo json_encode([
                'status'  => 'success',
                'message' => 'Warehouse added successfully'
            ]);
           
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['states'] = $this->site->getAllStates();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['location_types'] = $this->settings_model->getAllLocationTypes();
            $this->data['owned_by'] = $this->settings_model->getAllOwned_by();
            $this->data['billers_data'] = $this->settings_model->getBillers();
            $this->data['warehouse'] = (object) array('state' => '', 'postal_code' => '');
            $this->data['location_type'] = (object) array('location_type' => '');
            $this->load->view($this->theme . 'suppliers/add_warehouse_from_supplier', $this->data);
        }
    }

    function edit($id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if (!$company_details) {
            $this->session->set_flashdata('error', lang('supplier_not_found'));
            redirect(site_url('suppliers'));
        }
        //if ($this->input->post('email') != $company_details->email) {
           // $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[companies.email]');
       // }
       /* if($this->input->post('country') == 'India'){            
            $this->form_validation->set_rules('state', lang("state"), 'required');            
        }*/ 
        
       // $this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');
        
        if ($this->input->post('phone') != $company_details->phone) {
            $this->form_validation->set_rules('phone', lang("phone"), 'is_unique[companies.phone]');
        }
        $postal_required = false;
        $country_list = $this->site->getCountry();
        $settings = $this->site->get_setting();
        if (!empty($country_list)) {
            foreach ($country_list as $c) {
                if (strcasecmp($c->name, $settings->country_name) === 0) {
                    if (!empty($c->postal_code) && $c->postal_code != 0) {
                        $postal_required = true;
                    }
                    break;
                }
            }
        }
        if ($postal_required) {
            $this->form_validation->set_rules('postal_code', lang("Pincode"), 'required');
        }

        if ($this->form_validation->run('biller/add') == true) {
            $state = $this->input->post('state');
            if ($state !== null && $state !== '' && strpos((string) $state, '~') !== false) {
                $p = explode('~', $state);
                $state = $p[0];
                $state_code = $p[1];
            } else {
                $state_code = $this->site->getStateCodeFromName($state);
            }
            // Derive GST state code from selected state (for India GST)
            $gst_state_code = $this->site->getGstStateCodeFromName($state);
            
            $data = array('name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '4',
                'group_name' => 'supplier',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                
                'gstn_no' => $this->input->post('gstn_no'),
                'city' => $this->input->post('city'),
                'state' => $state,
                'state_code' => $state_code,
                'gst_state_code' => $gst_state_code,
                'postal_code' => $this->input->post('postal_code') ? $this->input->post('postal_code') : "0000",
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'location_id' => $this->input->post('location_id'),
            );
        } elseif ($this->input->post('edit_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("supplier_updated"));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
        } 
        
        else {
            $this->data['supplier'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            // $this->data['states'] = $this->site->getAllStates();
            $cfields = $this->site->getCustomeFieldsLabel('supplier') ;
            $this->data['custome_fields'] = $cfields['supplier'];
            $this->data['settings'] = $this->site->get_setting();
            $this->data['warehouses'] = $this->site->getWarehousesByLocationType('Vendor');
            $this->data['countries'] = $this->site->getCountry();
            $country = (isset($_POST['country']) ? $_POST['country'] : $company_details->country);
            $this->data['states'] = $this->site->getstates($country) ? $this->site->getstates($country) : array();
            $this->load->view($this->theme . 'suppliers/edit', $this->data);
        }
    }

    function users($company_id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        if (!$this->data['company']) {
            $this->session->set_flashdata('error', lang('supplier_not_found'));
            redirect(site_url('suppliers'));
        }
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'suppliers/users', $this->data);

    }

    function add_user($company_id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

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
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->lang->line("user_added"));
            redirect("suppliers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'suppliers/add_user', $this->data);
        }
    }

    function import_csv()
    {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
            }

            if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

                $this->load->library('upload');

                $config['upload_path'] = 'assets/mdata/'.$this->Customer_assets.'/uploads/csv/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = '2000';
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("suppliers");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen("assets/mdata/$this->Customer_assets/uploads/csv/" . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv) {
                    //if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                        //$this->session->set_flashdata('error', $this->lang->line("check_supplier_email") . " (" . $csv['email'] . "). " . $this->lang->line("supplier_already_exist") . " (" . $this->lang->line("line_no") . " " . $rw . ")");
                        //redirect("suppliers");
                    //}
                    $rw++;
                }
                foreach ($final as $record) {
                    $record['group_id'] = 4;
                    $record['group_name'] = 'supplier';
                    $data[] = $record;
                }
                //$this->sma->print_arrays($data);
            }

        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', $this->lang->line("suppliers_added"));
                redirect('suppliers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }

    function delete($id = NULL)
    {
        $this->sma->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->storeDeletedData('companies', 'id', $id);
        if ($this->companies_model->deleteSupplier($id)) {
            echo $this->lang->line("supplier_deleted");
        } else {
            $this->sma->deleteTableDataById('companies', $id);
            $this->session->set_flashdata('warning', lang('supplier_x_deleted_have_purchases'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }

    function getSupplier($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        if (!$row) {
            $this->sma->send_json(array());
            return;
        }
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->company)));
    }
    function getSupplierName($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        if (!$row) {
            $this->sma->send_json(array());
            return;
        }
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->name.'('.$row->company.')')));
    }

    function supplier_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        $this->sma->storeDeletedData('companies', 'id', $id);
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $this->sma->deleteTableDataById('companies', $id);
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("suppliers_deleted"));
                    }
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000') )));

                    $this->excel->getActiveSheet()->getStyle("A1:Q1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:Q1');

                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Suppliers');
                    $this->excel->getActiveSheet()->setTitle(lang('Suppliers'));

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
                     $this->excel->getActiveSheet()->SetCellValue('L2', 'Supplier Custom Field 1');
                    $this->excel->getActiveSheet()->SetCellValue('M2', 'Supplier Custom Field 2');
                    $this->excel->getActiveSheet()->SetCellValue('N2', 'Supplier Custom Field 3');
                    $this->excel->getActiveSheet()->SetCellValue('O2', 'Supplier Custom Field 4');
                    $this->excel->getActiveSheet()->SetCellValue('P2', 'Supplier Custom Field 5');
                    $this->excel->getActiveSheet()->SetCellValue('Q2', 'Supplier Custom Field 6');

                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        if (!$customer) {
                            continue;
                        }
                         $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ' '.$customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, ' '.$customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->vat_no);
                         $this->excel->getActiveSheet()->SetCellValue('K' . $row, ' '.$customer->gstn_no);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, ' '.$customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, ' '.$customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, ' '.$customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, ' '.$customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, ' '.$customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, ' '.$customer->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
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

                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
        }
    }

}