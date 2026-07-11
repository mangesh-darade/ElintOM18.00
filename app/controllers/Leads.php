<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Leads extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        // if (!$this->Owner) {
        //     $this->session->set_flashdata('warning', lang('access_denied'));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }
        $this->lang->load('employees_lang', $this->Settings->user_language);
        $this->load->library('form_validation');         
        $this->load->model('Leads_model');
	    $this->digital_upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/people/';
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/people/';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/people/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    function index($action = NULL)
    {
        // $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('All leads')]];
        $meta =  ['page_title' => lang('All leads'), 'bc' => $bc];
        $this->page_construct('leads/index', $meta, $this->data);
    }

    function get_leads()
    {
        // $this->sma->checkPermissions('index');
        $this->load->library('datatables');
        $this->datatables
        ->select("sma_leads.id,sma_leads.full_name,sma_leads.mobile,sma_leads.city,sma_leads.product_sel_1,sma_leads.business, sma_leads.source,sma_leads.created_at,sma_users.first_name") // Concatenate address fields
        ->from("sma_leads") 
        ->join('sma_users', 'sma_users.id = sma_leads.created_by', 'left')  // Ensure correct join with leads table
        ->where('is_delete', '0')  // Filter by the lead id
        ->edit_column('sma_leads.full_name', '<a href="'.site_url('leads/edit/$1').'">$2</a>', 'sma_leads.id, sma_leads.full_name')
        // ->add_column("Actions", "<div class=\"text-center\">
        //                             <a class=\"tip\" title='Lead_History' href='" . site_url('leads/lead_history/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-tasks\"></i></a>
        //                              <a class=\"tip\" title='Add_Deals' href='" . site_url('leads/add_deals/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus\"></i></a>
        //                             <a class=\"tip\" title='Deals' href='" . site_url('leads/listDeals/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-tags\"></i></a>
        //                             <a class=\"tip\" title='Edit' href='" . site_url('leads/edit/$1') . "'><i class=\"fa fa-edit\"></i></a>
        //                             <a href='#' class='tip po' title='<b>Delete</b>' data-content=\"<p>Are you sure?</p><a class='btn btn-danger po-delete' href='" . site_url('leads/delete/$1') . "'>Yes</a><button class='btn po-close'>No</button>\">
        //                                 <i class=\"fa fa-trash-o\"></i>
        //                             </a>
        //                         </div>", "id"); // Actions column for Edit and Delete
        ->add_column("Actions", "<div class=\"text-center\">
                                    <a class=\"tip\" title='Lead_History' href='" . site_url('leads/lead_history/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-tasks\"></i></a>
                                    <a class=\"tip\" title='Deals' href='" . site_url('leads/listDeals/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-tags\"></i></a>
                                     <a href='#' class='tip po' title='<b>Delete</b>' data-content=\"<p>Are you sure?</p><a class='btn btn-danger po-delete' href='" . site_url('leads/delete/$1') . "'>Yes</a><button class='btn po-close'>No</button>\">
                                        <i class=\"fa fa-trash-o\"></i>
                                    </a>
                                </div>", "sma_leads.id"); // Actions column for Edit and Delete
    echo $this->datatables->generate(); // This generates the JSON for DataTables

    }

    function add()
    {
        // $this->sma->checkPermissions();
                 
        //$this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');
        $this->form_validation->set_rules('phone', $this->lang->line("phone"), 'required');

        if ($this->form_validation->run('Leads/add') == true) {
            $exp_emptype = explode("~",$this->input->post('leads_type'));
            $leadId = $exp_emptype[0];
            $LeadType = $exp_emptype[1];
           
            $data = [
                    'full_name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'mobile' => $this->input->post('phone'),                 
                    'city' => $this->input->post('city'),
                    'country' => $this->input->post('country'),
                    'postal_code' => $this->input->post('postal_code'),
                    'state' => $this->input->post('state'),
                    'address_line_2' => $this->input->post('address_line_2'),
                    'address_line_1' => $this->input->post('address_line_1'),
                    'business'=>$this->input->post('business'),   
                    'brands'=>$this->input->post('brands'),   
                    'comments'=>$this->input->post('comments'),
                    'source'=>$this->input->post('source'), 
                    'created_by'=>$this->session->userdata('user_id'),
                    'district'=>$this->input->post('district'),
                    'product_sel_1'=>$this->input->post('product_sel_1'), 
                    'product_sel_2'=>$this->input->post('product_sel_2'), 
                    'product_sel_3'=>$this->input->post('product_sel_3'),
                    'form_memo'=>$this->input->post('form_memo'), 
                    'campaign'=>$this->input->post('campaign'),
                    'form'=>$this->input->post('form'), 

                    'type' => $LeadType,                
                ];
            
        } elseif ($this->input->post('Add_Leads')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Leads/index');
        }

        if ($this->form_validation->run() == true && $this->Leads_model->addLeads($data)) {
            $this->session->set_flashdata('message', $this->lang->line("Lead_Added_Successfully"));
            redirect('Leads/index');
        } else {

            $this->data['leads_type'] =  $this->Leads_model->getLeadTypes();
            $this->data['modal_js'] = $this->site->modal_js();
            $cfields = $this->site->getCustomeFieldsLabel('employee') ;
            $this->data['custome_fields'] = $cfields['employee'];
            $bc = [['link' => base_url(), 'page' => lang('home')],  ['link' => base_url('Leads/index'), 'page' => lang('All Leads')], ['link' => '#', 'page' => lang('Add lead')]];
            $meta =  ['page_title' => lang('Add lead'), 'bc' => $bc];
            $this->page_construct('leads/add', $meta, $this->data);
        }
    }  
    function edit($id = NULL)
    {
        // $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        } 
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        $this->form_validation->set_rules('phone', lang("phone"), 'trim|required');		
       
         if ( $this->form_validation->run('Leads/edit') == true) {
             
              $exp_emptype = explode("~",$this->input->post('leads_type'));
              $leadId = $exp_emptype[0];
              $LeadType = $exp_emptype[1];
            
                $data = [
                        'full_name' => $this->input->post('name'),
                        'email' => $this->input->post('email'),
                        'mobile' => $this->input->post('phone'),                 
                        'city' => $this->input->post('city'),
                        'address' => $this->input->post('address'),                 
                        // 'source' => $this->input->post('source'),/
                        'type' => $LeadType,  
                        'country' => $this->input->post('country'),
                        'postal_code' => $this->input->post('postal_code'),
                        'state' => $this->input->post('state'),
                        'address_line_2' => $this->input->post('address_line_2'),
                        'address_line_1' => $this->input->post('address_line_1'),
                        'business'=>$this->input->post('business'),   
                        'brands'=>$this->input->post('brands'),   
                        'comments'=>$this->input->post('comments'),
                        'source'=>$this->input->post('source'),
                        'created_by'=>$this->session->userdata('user_id'),
                        'district'=>$this->input->post('district'),
                        'product_sel_1'=>$this->input->post('product_sel_1'), 
                        'product_sel_2'=>$this->input->post('product_sel_2'), 
                        'product_sel_3'=>$this->input->post('product_sel_3'),
                        'form_memo'=>$this->input->post('form_memo'), 
                        'campaign'=>$this->input->post('campaign'),
                        'form'=>$this->input->post('form'),

                    ];
             
           
           } elseif ($this->input->post('edit_employee')){
               $this->session->set_flashdata('error', validation_errors());
               redirect($_SERVER["HTTP_REFERER"]);
           }
    
         if ($this->form_validation->run() == true && $this->Leads_model->updateLeads($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("Leads_Updated"));
            redirect('Leads/index');            
         } else {  
            $this->data['leads'] = $this->Leads_model->getLeadsByID($id);
            $this->data['leads_type'] =  $this->Leads_model->getLeadTypes();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = [
                ['link' => base_url(), 'page' => lang('home')],
                ['link' => base_url('Leads/index'), 'page' => lang('All Leads')],
                ['link' => '#', 'page' => lang('Edit lead')]
            ];
            $meta =  ['page_title' => lang('Edit lead'), 'bc' => $bc];
            $this->page_construct('leads/edit', $meta, $this->data);
         }
    }


    function delete($id = NULL)
    {
        // $this->sma->checkPermissions(NULL, TRUE);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->Leads_model->deleteLeads($id)) {
            echo $this->lang->line("Leads_deleted");
        } else {             
            $this->session->set_flashdata('warning', lang('employee_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
       
    }
    public function lead_history($id = NULL) {

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['deals'] = $this->Leads_model->getCommentsHistory($id);
        $this->data['leadDetails'] = $this->Leads_model->getLeadsByID($id);
        $this->data['lead_id'] = $id;
        $this->load->view($this->theme . 'leads/lead_history', $this->data);
    }
    public function getHistory($id = NULL) {
        $this->load->library('datatables');
        $this->datatables
            ->select("leads_history.id,leads_history.created_at,leads_history.created_by,leads_history.comments")
            ->from("leads_history")
            ->join('leads', 'leads.id = leads_history.lead_id', 'left')  // Ensure correct join with leads table
            ->where('leads_history.lead_id', $id)
            // ->add_column("Actions", "<div class=\"text-center\">
            //                             <a class=\"tip\" title='" . lang("Edit_Deals") . "' href='" . site_url('Leads/edit_deals/$1') . "' data-toggle='modal' data-target='#myModal2'>
            //                                 <i class=\"fa fa-edit\"></i>
            //                             </a> 
            //                             <a href='#' class='tip po' title='<b>" . lang("Delete_Deals") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p>
            //                                 <a class='btn btn-danger po-delete' href='" . site_url('Leads/delete_deals/$1') . "'>" . lang('i_m_sure') . "</a>
            //                                 <button class='btn po-close'>" . lang('no') . "</button>\" rel='popover'>
            //                                 <i class=\"fa fa-trash-o\"></i>
            //                             </a>
                                    //   </div>", "leads_history.id")
            ->unset_column('leads_history.id');  // Unset the 'id' column to prevent it from being displayed in the DataTable
    
        echo $this->datatables->generate();
    }
    public function saveHistory($id) {
        $comment = $this->input->get('comment');  // Get the comment sent from AJAX
        if ($comment) {
            $data = [
                'lead_id' => $id,
                'comments' => $comment 
            ];
            $response = $this->db->insert('leads_history', $data);
            if ($response) {
                echo 'success';  
            } else {
                echo 'failure';  
            }
        } else {
            echo 'failure';  
        }
    }
    
    
    
/////////////////////////////////////// Deals/////////////////////////////////////////////
    public function listDeals($id = NULL) {
        // $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['deals'] = $this->Leads_model->getDealsByID($id);
        $this->data['lead_id'] = $id;
        $this->load->view($this->theme . 'leads/list_deals', $this->data);
    }
    public function getDeals($id = NULL) {
      
        $this->load->library('datatables');
        $this->datatables
            ->select("deals.id as id, categories.name as categoryName, products.name as productName, deals.quantity, deals.RateExpected, deals.RateFinalized, deals.Status, deals.ProcessStage, deals.created_at, deals.Description", false)
            ->from("deals")
            ->join('categories', 'categories.id = deals.CategoryId', 'left')  // Ensure correct join with categories
            ->join('products', 'products.id = deals.ProductId', 'left')  // Correct join condition for products table
            ->where($this->db->dbprefix('deals') . '.Leadid', $id)  // Filter by the lead id
            ->where($this->db->dbprefix('deals') . '.is_delete', '0')  // Filter by the lead id
            ->add_column("Actions", "<div class=\"text-center\">
                                        <a class=\"tip\" title='" . lang("Edit_Deals") . "' href='" . site_url('Leads/edit_deals/$1') . "' data-toggle='modal' data-target='#myModal2'>
                                            <i class=\"fa fa-edit\"></i>
                                        </a> 
                                        <a href='#' class='tip po' title='<b>" . lang("Delete_Deals") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p>
                                            <a class='btn btn-danger po-delete' href='" . site_url('Leads/delete_deals/$1') . "'>" . lang('i_m_sure') . "</a>
                                            <button class='btn po-close'>" . lang('no') . "</button>\" rel='popover'>
                                            <i class=\"fa fa-trash-o\"></i>
                                        </a>
                                      </div>", "id")
            ->unset_column('id');  // Unset the 'id' column to prevent it from being displayed in the DataTable
        echo $this->datatables->generate();
    }
    public function edit_deals($id = NULL) {

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deals = $this->Leads_model->getDealsNameByID($id);
       
        // $company = $this->companies_model->getCompanyByID($deposit->company_id);

        $this->form_validation->set_rules('expected_rate', lang("expected_rate"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $category = explode("~",$this->input->post('category'));
            $products = explode("~",$this->input->post('Products'));
            $productId = $products[0];
            $productName = $products[1];

            $categoryId = $category[0];
            $categoryName = $category[1];
            // if ($this->Owner || $this->Admin) {
            //     $date = $this->sma->fld(trim($this->input->post('date')));
            // } else {
            //     $date = $deals->date;
            // }
            $data = array(
                'id'            => $this->input->post('id'),
                'CategoryId'      => $categoryId ,
                'ProductId'       => $productId,
                'Quantity'      => $this->input->post('quantity'),
                'RateExpected' => $this->input->post('expected_rate'),
                'RateFinalized'=> $this->input->post('finalized_rate'),
                'Status'        => $this->input->post('status'),
                'ProcessStage' => $this->input->post('process_stage'),
                'Description'   => $this->input->post('description')
            );

        } elseif ($this->input->post('edit_deals')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->Leads_model->updateDeals($id, $data)) {
            $this->session->set_flashdata('message', lang("Deals_updated"));
            redirect("leads");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['categories'] =$this->Leads_model->get_all_categories();
            $this->data['products'] =$this->Leads_model->get_all_products();
            $this->data['deals'] = $deals;
            $this->load->view($this->theme . 'leads/edit_deals', $this->data);
        }
    }
    public function add_deals($id = NULL) {

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deals = $this->Leads_model->getDealsNameByID($id);

        // $company = $this->companies_model->getCompanyByID($deposit->company_id);

        $this->form_validation->set_rules('expected_rate', lang("expected_rate"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $category = explode("~",$this->input->post('category'));
            $products = explode("~",$this->input->post('Products'));
            $productId = $products[0];
            $productName = $products[1];

            $categoryId = $category[0];
            $categoryName = $category[1];
            // if ($this->Owner || $this->Admin) {
            //     $date = $this->sma->fld(trim($this->input->post('date')));
            // } else {
            //     $date = $deals->date;
            // }
            $data = array(
                'Leadid'            => $this->input->post('id'),
                'CategoryId'      => $categoryId ,
                'ProductId'       => $productId,
                'Quantity'      => $this->input->post('quantity'),
                'RateExpected' => $this->input->post('expected_rate'),
                'RateFinalized'=> $this->input->post('finalized_rate'),
                'Status'        => $this->input->post('status'),
                'ProcessStage' => $this->input->post('process_stage'),
                'Description'   => $this->input->post('description')
            );

        } elseif ($this->input->post('add_deals')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('leads');
        }
   
        if ($this->form_validation->run() == true && $this->Leads_model->addDeals($data)) {

            $this->session->set_flashdata('message', lang("Add Deals Successfully"));
            redirect('leads');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['categories'] =$this->Leads_model->get_all_categories();
            $this->data['products'] =$this->Leads_model->get_all_products();
            $this->data['leadDetails'] = $this->Leads_model->getLeadsByID($id); 
            $this->data['deals'] = $deals;
            $this->data['lead_id'] = $id;
            $this->load->view($this->theme . 'leads/add_deals', $this->data);
        }
    }

    public function delete_deals($id) {
        // $this->sma->checkPermissions(NULL, TRUE);
        if ($this->Leads_model->delete_deals($id)) {
            echo lang("Deals_Deleted");
        }
    }


}
