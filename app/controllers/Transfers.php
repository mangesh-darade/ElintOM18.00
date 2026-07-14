<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transfers extends MY_Controller
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
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }
        $this->lang->load('transfers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('transfers_model');
        $this->load->model('products_model');
        $this->load->model('settings_model');
        $this->digital_upload_path = 'files/'.$this->Customer_assets;
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types   = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size    = '1024';
        $this->data['logo'] = true;
    }

    function index()
    {
        $this->sma->checkPermissions();

        $this->data['fromWarehouses']  = $this->transfers_model->getFromWarehouses();
        $this->data['toWarehouses']    = $this->transfers_model->getToWarehouses();
        // $this->data['product']      = $this->transfers_model->product();
        $this->data['category']        = $this->transfers_model->getCategory();
        $this->data['brands']          = $this->transfers_model->getBrand();
        $this->data['transferStatus']  = $this->transfers_model->transferStatus();
        $this->data['Users']           = $this->transfers_model->getUsers();
        $this->data['refNo']           = $this->transfers_model->getRefNo();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('transfers')));
        $meta = array('page_title' => lang('transfers'), 'bc' => $bc);
        $this->page_construct('transfers/index', $meta, $this->data);
    }

    function getTransfers()
    {
        $this->sma->checkPermissions('index');

        $fromWarehouse = $this->input->get('fromWarehouse') ? $this->input->get('fromWarehouse') : NULL;
        $toWarehouse = $this->input->get('toWarehouse') ? $this->input->get('toWarehouse') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);

        }
        $detail_link = anchor('transfers/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $pdf_link = anchor('transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . lang("delete_transfer") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . site_url('transfers/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_transfer') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select("id, date, transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, total, total_tax, grand_total, status, attachment")
            ->from('transfers')
            ->edit_column("fname", "$1 ($2)", "fname, fcode")
            ->edit_column("tname", "$1 ($2)", "tname, tcode");
        if($fromWarehouse)
        {
            $this->datatables->where('transfers.from_warehouse_id', $fromWarehouse);
        }
        if($toWarehouse)
        {
            $this->datatables->where('transfers.to_warehouse_id', $toWarehouse);
        }
        if($start_date)
        {
            $this->datatables->where('DATE(' . $this->db->dbprefix('transfers') . '.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        if($this->session->userdata('view_right')){
          if($this->input->get('warehouse')){
             $getwarehouse = str_replace("_",",",$this->input->get('warehouse')); 
             $this->datatables->where('to_warehouse_id IN ('.$getwarehouse.')');
             $this->datatables->or_where('from_warehouse_id IN ('.$getwarehouse.')');
          }
       }

        $this->datatables->add_column("Actions", $action, "id")
            ->unset_column('fcode')
            ->unset_column('tcode');
        echo $this->datatables->generate();
    }

    function add()
    {
        $this->sma->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {

            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            if ($this->Owner || $this->Admin ||  $this->GP['transfers-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $to_warehouse = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $shipping = $this->input->post('shipping');
            $status = $this->input->post('status');
            $fromWarehouseDetails = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_details = $fromWarehouseDetails[$from_warehouse];
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            $from_warehouse_state_code = $from_warehouse_details->state_code;
            
            $toWarehouseDetails = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_details = $toWarehouseDetails[$to_warehouse];
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;
            $to_warehouse_state_code = $to_warehouse_details->state_code;
           
            $total = 0;
            $product_tax = 0;

            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $product_tax_id = isset($_POST['product_tax_id'][$r]) ? $_POST['product_tax_id'][$r] : NULL;
                $item_expiry = isset($_POST['expiry'][$r]) ? $this->sma->fsd($_POST['expiry'][$r]) : NULL;
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_quantity = $item_unit_quantity;
                $product_option_color = isset($_POST['product_option_color'][$r]) ? $_POST['product_option_color'][$r] : null;
                $item_batch_number = (isset($_POST['batch_number'][$r]) && $_POST['batch_number'][$r] != '') ? $_POST['batch_number'][$r] : NULL;


                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->transfers_model->getProductByCode($item_code);
                   
                    // if (!$this->Settings->overselling) {
                    $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option);

                    if ($warehouse_quantity->quantity < $item_quantity) {
                        $this->session->set_flashdata('error', lang("no_match_found") . " (" . lang('product_name') . " <strong>" . $product_details->name . "</strong> " . lang('product_code') . " <strong>" . $product_details->code . "</strong>)");
                        redirect("transfers/add");
                    }
                   // }
                    
                    if ( $item_tax_rate && $product_tax_id > 1 ) {
                        $pr_tax = $product_tax_id;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        } elseif ($tax_details->rate == 0) {
                                   $item_tax = 0;
                                   $tax = $tax_details->rate . "%";
                                   if ($tax_details->type == 2)
                                      $tax = $tax_details->rate; 
                        }
                        
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = ($product_details && $product_details->tax_method == 1) ? $this->sma->formatDecimal($unit_cost) : $this->sma->formatDecimal($unit_cost-$item_tax, 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = [
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $to_warehouse,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'shade_id'          => $product_option_color,
                        'batch_number'      => $item_batch_number,
                    ];

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            $data = array('transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'from_warehouse_state_code' => $from_warehouse_state_code,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
                'to_warehouse_state_code' => $to_warehouse_state_code,
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("transfer_added"));
            redirect("transfers");
            
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['rnumber'] = ''; //$this->site->getReference('to');
            $this->data['UsersDatas']  =  $this->Owner; // Admin Login

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('add_transfer')));
            $meta = array('page_title' => lang('transfer_quantity'), 'bc' => $bc);
            $this->page_construct('transfers/add', $meta, $this->data);
        }
    }

    public function edit($id = NULL)
    {
        $this->sma->checkPermissions();
        $this->data['page_mode'] = 'edit';
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($id);
        if (!$transfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers');
        }
        $last_status = $transfer->status;
        
        if($this->session->userdata('view_right')=='0'){ 
          if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($transfer->created_by);
          }
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
           
            $transfer_no = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin ||  $this->GP['transfers-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $to_warehouse   = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note           = $this->sma->clear_tags($this->input->post('note'));
            $shipping       = $this->input->post('shipping');
            $status         = $this->input->post('status');
            
            $fromWarehouseDetails   = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_details     = $fromWarehouseDetails[$from_warehouse];           
            $from_warehouse_code        = $from_warehouse_details->code;
            $from_warehouse_name        = $from_warehouse_details->name;
            $from_warehouse_state_code  = $from_warehouse_details->state_code;
            
            $toWarehouseDetails     = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_details       = $toWarehouseDetails[$to_warehouse];
            $to_warehouse_code          = $to_warehouse_details->code;
            $to_warehouse_name          = $to_warehouse_details->name;
            $to_warehouse_state_code    = $to_warehouse_details->state_code;

            $total = 0;
            $product_tax = 0;
            
            if($last_status==$status){
                $error = 'Status has been not changed';
                $this->session->set_flashdata('error', $error);
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
            }                   
            
            if($last_status != 'completed'){
                
                $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
                
                for ($r = 0; $r < $i; $r++) {
                    $item_code          = $_POST['product_code'][$r];
                    $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                    $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                    $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                    $product_tax_id     = isset($_POST['product_tax_id'][$r]) ? $_POST['product_tax_id'][$r] : NULL;
                    $item_expiry        = isset($_POST['expiry'][$r]) ? $this->sma->fsd($_POST['expiry'][$r]) : NULL;
                    $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                    $item_unit          = $_POST['product_unit'][$r];
                    $product_option_color        = isset($_POST['product_option_color'][$r]) && $_POST['product_option_color'][$r] != 'false' && $_POST['product_option_color'][$r] != 'undefined' && $_POST['product_option_color'][$r] != 'null' ? $_POST['product_option_color'][$r] : NULL;
                    $item_quantity          = $_POST['product_base_quantity'][$r];
                    $item_unit_quantity     = $_POST['quantity'][$r];
                    $item_batch_number = (isset($_POST['batch_number'][$r]) && $_POST['batch_number'][$r] != '') ? $_POST['batch_number'][$r] : NULL;
                    
//                    $item_request_quantity  = isset($_POST['request_quantity'][$r]) ? $_POST['request_quantity'][$r] : NULL;
//                    $sent_quantity          = $_POST['sent_quantity'][$r];
                   // $quantity_received      = $_POST['quantity_received'][$r];
                    $quantity_balance       = $status == 'completed' ? $item_unit_quantity : 0;
                    $ordered_quantity       = $_POST['ordered_quantity'][$r];
                                        
                   /* if( $item_request_quantity ){
                    //Request Transfer Module
                        if( $quantity_balance < $item_unit_quantity && $status != 'completed'){
                            $error = 'Product quantity not more than balance quantity';
                            $this->session->set_flashdata('error', $error);
                            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                        } else {
                            switch ($status) {
                                case 'partial':
                                    $sent_quantity      = $sent_quantity + $item_unit_quantity;
                                    $quantity_balance   = $item_request_quantity - $sent_quantity;
                                    
                                    break;
                                
                                case 'partial_completed':
                                    $quantity_received  = $quantity_received + $item_unit_quantity;
                                    $sent_quantity      = $sent_quantity + $item_unit_quantity;
                                    $quantity_balance   = $item_request_quantity - $sent_quantity;
                                    break;
                                
                                case 'sent_balance':
                                    $sent_quantity      = $sent_quantity + $item_unit_quantity;
                                    $quantity_balance   = $item_request_quantity - $sent_quantity;
                                    
                                    break;
                                
                                case 'sent':
                                    $sent_quantity      = $item_request_quantity;
                                    $quantity_balance   = 0;

                                    break;
                                
                                case 'completed':
                                    $sent_quantity      = $item_request_quantity;
                                    $quantity_balance   = 0;
                                    $quantity_received  = $item_request_quantity;
                                    
                                    break;
                                
                                default:
                                    break;
                            }//end switch
                        }//else                        
                    } */
                                
                    if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                        
                        $product_details = $this->transfers_model->getProductByCode($item_code);

                        if (isset($product_tax_id) && $product_tax_id != 0) {
                            $pr_tax = $product_tax_id;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                }
                            } elseif ($tax_details->type == 2) {

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;

                            } elseif ($tax_details->rate == 0) {
                               $item_tax = 0;
                               $tax = $tax_details->rate . "%";
                               if ($tax_details->type == 2)
                                  $tax = $tax_details->rate; 
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);                                   
                        } else {
                            $pr_tax = 0;
                            $pr_item_tax = 0;                                   
                            $tax = "";
                        }

                        $item_net_cost  = ($product_details && $product_details->tax_method == 1) ? $this->sma->formatDecimal($unit_cost) : $this->sma->formatDecimal($unit_cost-$item_tax, 4);
                        $product_tax    += $pr_item_tax;
                        
                        $subtotal       = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);                        
                        $unit           = $this->site->getUnitByID($item_unit);
                                         
                        $products[$r] = [
                            'product_id'        => $product_details->id,
                            'product_code'      => $item_code,
                            'product_name'      => $product_details->name,
                            'hsn_code'          => $product_details->hsn_code,
                            'option_id'         => $item_option,
                            'net_unit_cost'     => $item_net_cost,
                            'unit_cost'         => $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                            'quantity'          => $item_unit_quantity,
                            'product_unit_id'   => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity'     => $item_unit_quantity,
                            'quantity_balance'  => $quantity_balance,                            
                            'warehouse_id'      => $to_warehouse,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $pr_tax,
                            'tax'               => $tax,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'expiry'            => $item_expiry,
                            'real_unit_cost'    => $real_unit_cost,
                            'date'              => date('Y-m-d', strtotime($date)), 
                            'shade_id'          => $product_option_color,  
                            'batch_number'      => $item_batch_number,                        
                        ];

                        $total  += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    }
                }
                                
                if (empty($products)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } else {
                    krsort($products);
                }
            }
            
            $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            
            $data = [
                'transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'from_warehouse_state_code' => $from_warehouse_state_code,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
                'to_warehouse_state_code' => $to_warehouse_state_code,
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                //'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping
            ];
  
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if($this->form_validation->run() == true && $last_status =='completed') {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
            if($this->transfers_model->updateStatus($id, $status, $note)){
                $this->session->set_flashdata('message', lang('status_updated'));
                redirect("transfers");
            }            
        } else if ($this->form_validation->run() == true && $this->transfers_model->updateTransfer($id, $data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("transfer_updated"));
            redirect("transfers");
        } 
        else {
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['transfer'] = $this->transfers_model->getTransferByID($id);
            if (!$this->data['transfer']) {
                $this->session->set_flashdata('error', lang('no_transfer_selected'));
                redirect('transfers');
            }
			
            $transfer_items = $this->transfers_model->getAllTransferItems($id, $this->data['transfer']->status);
                      
            $transfer_completed_items = $this->transfers_model->getTransferCompletedItems($id) ?: array();
            
            krsort($transfer_items);
            $pr = array();
            $c = rand(100000, 9999999);
            foreach ($transfer_items as $item) {               
                
                $row = $this->site->getProductByID($item->product_id);
                //$get_qty = $this->db->select('quantity')->where(['product_id'=>$item->product_id,'warehouse_id'=>$this->data['transfer']->from_warehouse_id])->get('sma_warehouses_products')->row();
                
                if(isset($transfer_completed_items[$item->product_id])) {
                    $option_id = $item->option_id ? $item->option_id : 0;
                    $item_completed = $transfer_completed_items[$item->product_id][$option_id];
                    $row->quantity_received = $item_completed->quantity_received;
                } else {
                    $row->quantity_received = 0;
                }
                
                if (!$row) {
                    $row = json_decode('{}');
                } else {
                    unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                //$row->quantity = $get_qty->quantity;
//                $row->warehousestock =$get_qty->quantity; 
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = ($item->request_quantity) ? $item->request_quantity - $item->sent_quantity : $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                /*if($this->data['transfer']->status=='partial' || $this->data['transfer']->status=='request'){
                        $row->qty = ($item->request_quantity)?$item->request_quantity - $item->sent_quantity :$item->unit_quantity;
                }else{
                        $row->qty = ($item->request_quantity)?$item->sent_quantity  :$item->unit_quantity;
                }*/
                if($this->data['transfer']->status=='completed'){
                    $row->qty = ($item->request_quantity) ? $item->sent_quantity  : $item->unit_quantity;
                } elseif($this->data['transfer']->status=='sent' || $this->data['transfer']->status=='sent_balance'){
                    $row->qty = ($item->request_quantity) ? $item->sent_quantity  : $item->unit_quantity;
                } else {
                    $row->qty = ($item->request_quantity) ? $item->request_quantity - $item->sent_quantity : $item->unit_quantity; 
                }
				
               
                $row->item_status      = $item->item_status ? $item->item_status : $this->data['transfer']->status;
                $row->quantity_balance = $item->quantity_balance;
                $row->ordered_quantity = $item->quantity; //($item->request_quantity)?$item->request_quantity:
//                $row->quantity += $item->quantity_balance;
                $row->request_quantity = $item->request_quantity ? $item->request_quantity : 0;
                $row->sent_quantity    = $item->sent_quantity ? $item->sent_quantity : 0;
                $row->cost = $item->net_unit_cost;
                if($item->quantity==0)
                    $row->unit_cost = $item->net_unit_cost+(0);
                else
                    $row->unit_cost = $item->net_unit_cost+($item->item_tax/$item->quantity);
                    $row->real_unit_cost = $item->real_unit_cost;
                    $row->tax_rate = $item->tax_rate_id;
                    $row->option = $item->option_id;
                    $row->option_color = $item->shade_id;

                /*show stock quantity with variant option 19-09-2019*/
               /* if($row->option){
                    $get_qty = $this->db->select('quantity')->where(['product_id'=>$row->id,'warehouse_id'=>$this->data['transfer']->from_warehouse_id ,'option_id'=>$row->option])->get('sma_warehouses_products_variants')->row();
                } else {
                    $get_qty = $this->db->select('quantity')->where(['product_id'=>$item->product_id,'warehouse_id'=>$this->data['transfer']->from_warehouse_id])->get('sma_warehouses_products')->row();
                }
                $row->quantity = $get_qty->quantity;
                  */             
                $options = $this->transfers_model->getProductOptionsByGroupId($row->id, $this->data['transfer']->from_warehouse_id, FALSE, 1);
				$options_color = $this->transfers_model->getProductVariantColor($row->id, 2);
                if ($options_color) {
                    foreach ($options_color as $color) {
                        if (!empty($color)) {
                        $row->option_color_name = $color->name;
                    }
                }
                } else {
                    $opt_color = json_decode('{}');
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $this->data['transfer']->from_warehouse_id, $item->option_id);
                $row->quantity = 0;
                if($pis) {
                    foreach ($pis as $pi) {
                         $row->quantity += $pi->quantity_balance;
                    }
                }
                
                $row->stockwarehouse2 = $this->getstockwarehousedata($this->data['transfer']->to_warehouse_id,$row->id, ($item->option_id)?$item->option_id:NULL);
               
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                        if($pis){
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        $option_quantity += $item->quantity;
                        if($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                // id.option concatenation collides (e.g. id 9 + opt 10 == id 91 + opt 0) and merges lines in JSON → wrong single row
                $line_pk = isset($item->id) ? (int) $item->id : $c;
                $row_id = $row->id . $row->option;
                if ($this->Settings->item_addition) {
                    $ri = $row->id . '_' . (int) $row->option . '_' . $line_pk;
                    $item_id_for_js = $ri;
                } else {
                    $ri = $c;
                    $item_id_for_js = $row_id;
                }

                $pr[$ri] = array('id' => $c, 'item_id' => $item_id_for_js, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => ($options ? $options : []), 'options_color' => ($options_color ? $options_color : []));
                $c++;
            }
			
			//print_r($pr); exit;
            $this->data['transfer_items'] = json_encode($pr, JSON_UNESCAPED_UNICODE);
            if ($this->data['transfer_items'] === false) {
                $this->data['transfer_items'] = '{}';
                log_message('error', 'transfers/edit: json_encode(transfer items) failed for transfer id ' . (int) $id);
            }
            $this->data['id'] = $id;
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['UsersDatas']  =  $this->Owner; // Admin Login

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('edit_transfer')));
            $meta = array('page_title' => lang('edit_transfer_quantity'), 'bc' => $bc);
            $this->page_construct('transfers/edit', $meta, $this->data);
        }
    }                                
    
    function transfer_by_csv()
    {
        //$this->sma->checkPermissions('csv');
        $this->sma->checkPermissions('index',TRUE);
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        $this->data['batch_setting'] = $batch_setting = $this->site->get_setting()->product_batch_setting;

        if ($this->form_validation->run()) {

            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $to_warehouse = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $shipping = $this->input->post('shipping');
            $status = $this->input->post('status');
            
            $fromWarehouseDetails = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_details = $fromWarehouseDetails[$from_warehouse];
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            
            $toWarehouseDetails = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_details = $toWarehouseDetails[$to_warehouse];
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;

            $total = 0;
            $product_tax = 0;
            

            if (isset($_FILES["userfile"])) {

               /* $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;

                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("transfers/transfer_bt_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);*/
                $this->load->library('excel');
		$File  = $_FILES['userfile']['tmp_name'];
                $inputFileType = PHPExcel_IOFactory::identify($File);
		$reader= PHPExcel_IOFactory::createReader($inputFileType);
		//$reader= PHPExcel_IOFactory::createReader('Excel2007');
		$reader->setReadDataOnly(true);
		$path= $File;//"./uploads/upload.xlsx";
		$excel=$reader->load($path);

		$sheet = $excel->getActiveSheet()->toArray(null,true,true,true);
				//print_r($sheet);
		$arrayCount = count($sheet);
		$arrResult = array();
		for($i=2;$i<=$arrayCount;$i++)
		{    
		       $arrResult[]=$sheet[$i];
		       // echo $sheet[$i]["A"].$sheet[$i]["B"].$sheet[$i]["C"].$sheet[$i]["D"].$sheet[$i]["E"];
		}
                $final = array();

                foreach ($arrResult as $value) {

                    $final[] = array(
                        'product'      => isset($value['A']) ? $value['A'] : '',
                        'net_cost'     => isset($value['B']) ? $value['B'] : '',
                        'quantity'     => isset($value['C']) ? $value['C'] : '',
                        'variant'      => isset($value['D']) ? $value['D'] : '',
                        'expiry'       => isset($value['E']) ? $value['E'] : '',
                        'batch_number' => isset($value['F']) ? $value['F'] : '',
                    );
                }

                $rw = 2;
                foreach ($final as $csv_pr) {

                    $item_code = $csv_pr['product'];
                    $item_net_cost = $csv_pr['net_cost'];
                    $item_quantity = $csv_pr['quantity'];
                    $variant = isset($csv_pr['variant']) ? trim($csv_pr['variant']) : NULL;
                    $item_expiry = NULL;

                    if (!empty($csv_pr['expiry'])) {

                        $expiry_value = trim($csv_pr['expiry']);

                        // Excel numeric date
                        if (is_numeric($expiry_value)) {

                            $unix_date = ($expiry_value - 25569) * 86400;
                            $item_expiry = gmdate('Y-m-d', $unix_date);

                        } else {

                            $timestamp = strtotime($expiry_value);

                            if ($timestamp !== false) {
                                $item_expiry = date('Y-m-d', $timestamp);
                            }
                        }
                    }
                    $item_batch_number = !empty($csv_pr['batch_number']) ? $csv_pr['batch_number'] : NULL;

                    

                    // Get product details for batch validation
                    $product_details = $this->transfers_model->getProductByCode($item_code);
                    $item_option = $this->transfers_model->getProductVariantByName($variant, $product_details->id);
                    
                    // Batch number validation based on batch setting
                    
                    // If batch number is provided
                    if (!empty($csv_pr['batch_number'])) {
                        // If batch is disabled (setting 0) and batch number is provided, show error
                        if ($batch_setting == 0) {
                            $this->session->set_flashdata('error', "Line {$rw}: Batch number is not allowed as batch setting is disabled for product '{$product_details->name}'" . 
                                ($variant ? " (Variant: {$variant})" : ""));
                            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                        }
                        
                        // For batch settings 1 or 2, check if batch exists
                        // $batch = $this->db
                        //     ->where('product_id', $product_details->id)
                        //     ->where('batch_no', $csv_pr['batch_number'])
                        //     ->where('option_id', $variant ? $item_option->id : 0)
                        //     ->get('product_batches')
                        //     ->row();
                        
                        // Validate/resolve product and variant before this (and handle not found cases).
                        $batch_no = trim((string) $csv_pr['batch_number']);
                        
                        $this->db->where('product_id', (int)$product_details->id)
                        ->where('batch_no', $batch_no);
                        // For option_id, match your DB convention:
                        if (is_float($csv_pr['batch_number']) || is_int($csv_pr['batch_number'])) {
                            $batch_no = (string) (int) $csv_pr['batch_number'];
                        } else {
                            $batch_no = trim((string) $csv_pr['batch_number']);
                        }
                        $batch = $this->db->get('product_batches')->row();
                        if (!$batch) {
                            $this->session->set_flashdata('error', "Line {$rw}: Batch number '{$csv_pr['batch_number']}' not found for product '{$product_details->name}'" . 
                                ($variant ? " (Variant: {$variant})" : ""));
                            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                        }
                    } 
                    // If batch is enabled (setting 1 or 2) and batch number is empty, show error
                    elseif (($batch_setting == 1 || $batch_setting == 2) && empty($item_batch_number)) {
                        $this->session->set_flashdata('error', "Line {$rw}: Batch number is required for product '{$product_details->name}'" . 
                            ($variant ? " (Variant: {$variant})" : ""));
                        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                    }

                    // --- Negative Value Validations ---
                    if ($item_net_cost < 0) {
                        $this->session->set_flashdata('error', "Line {$rw}: Net Cost cannot be negative for product '{$item_code}'.");
                        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                    }
                    if ($item_quantity < 0) {
                        $this->session->set_flashdata('error', "Line {$rw}: Quantity must be greater than zero for product '{$item_code}'.");
                        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                    }

                    // --- Expiry Date Validation ---
                    if (!empty($item_expiry)) {
                        $today = date('Y-m-d');
                        if ($item_expiry < $today) {
                            $this->session->set_flashdata('error', "Line {$rw}: Invalid expiry date for product '{$item_code}'. Expiry date cannot be earlier than today.");
                            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                        }
                    }

                    if (isset($item_code) && isset($item_net_cost) && isset($item_quantity)) {
                        if (!($product_details = $this->transfers_model->getProductByCode($item_code))) {
                            $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $csv_pr['product'] . " ). " . lang("line_no") . " " . $rw);
                            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                        }
                        if ($variant) {
                            $item_option = $this->transfers_model->getProductVariantByName($variant, $product_details->id);
                            if (!$item_option) {
                                $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $csv_pr['product'] . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                            }
                        } else {
                            $item_option = json_decode('{}');
                            $item_option->id = NULL;
                        }

                        if (!$this->Settings->overselling) {
                            $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option->id);
                            if ($warehouse_quantity->quantity < $item_quantity) {
                                $this->session->set_flashdata('error', lang("no_match_found") . " (" . lang('product_name') . " <strong>" . $product_details->name . "</strong> " . lang('product_code') . " <strong>" . $product_details->code . "</strong>) " . lang("line_no") . " " . $rw);
                                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                            }
                        }

                        
                        

                        if (isset($product_details->tax_rate)) {
                            $pr_tax = $product_details->tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
			    // New Method update 8/04/19
			    if ($tax_details->type == 1 && $tax_details->rate != 0) {
                               if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($item_net_cost) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($item_net_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $item_net_cost = $this->sma->formatDecimal($item_net_cost - $item_tax, 4);
                                    $tax = $tax_details->rate . "%";

                                }
                            } elseif ($tax_details->type == 2) {

                               if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal($tax_details->rate, 4);
                               } else {
                                    $item_tax = $this->sma->formatDecimal((($item_net_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $item_net_cost = $this->sma->formatDecimal($item_net_cost - $item_tax, 4);
                               }
                               $tax = $tax_details->rate;

                           }
                            $product_tax = $this->sma->formatDecimal($item_tax * $item_quantity, 4);
			    // End 8/04/19

                            /*if ($tax_details->type == 1 && $tax_details->rate != 0) {  //old method
                                $item_tax = ((($item_quantity * $item_net_cost) * $tax_details->rate) / 100);
                                $product_tax += $item_tax;
                            } else {
                                $item_tax = $tax_details->rate;
                                $product_tax += $item_tax;
                            }

                            if ($tax_details->type == 1)
                                $tax = $tax_details->rate . "%";
                            else
                                $tax = $tax_details->rate;*/
                        } else {
                            $pr_tax = 0;
                            $item_tax = 0;
                            $tax = "";
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_quantity, 4);
                        $subtotal = (($item_net_cost * $item_quantity) + $pr_item_tax);

                        $item_unit = $product_details->unit;
                        $unit = $this->site->getUnitByID($item_unit);
                        $product_unit_code = $unit ? $unit->code : '';

                        $products[] = array(
                            'product_id' => $product_details->id,
                            'product_code' => $item_code,
                            'product_name' => $product_details->name,
                            'option_id' => isset($item_option->id) ? $item_option->id : null,
                            'net_unit_cost' => $item_net_cost,
                            'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                            'quantity' => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $product_unit_code,
                            'quantity_balance' => $item_quantity,
                            'unit_quantity' => $item_quantity,
                            'warehouse_id' => $to_warehouse,
                            'item_tax' => $pr_item_tax,
                            'tax_rate_id' => $pr_tax,
                            'tax' => $tax,
                            'expiry' => $item_expiry,
                            'batch_number' => $item_batch_number,
                            'subtotal' => $subtotal,
                            'real_unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                            'date' => date('Y-m-d', strtotime($date))
                        );

                        $total += $item_net_cost * $item_quantity;
                    }
                    $rw++;
                }
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_item"));
            } else {
                krsort($products);
            }
            $grand_total = $total + $shipping + $product_tax;
            $data = array('transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'from_warehouse_state_code' => $from_warehouse_state_code,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
                'to_warehouse_state_code' => $to_warehouse_state_code,
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);

        }

        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("transfer_added"));
            redirect("transfers");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['rnumber'] = $this->site->getReference('to');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('transfer_by_csv')));
            $meta = array('page_title' => lang('add_transfer_by_csv'), 'bc' => $bc);
            $this->page_construct('transfers/transfer_by_csv', $meta, $this->data);
        }
    }

    function view($transfer_id = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        if (!$transfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers');
        }
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer->created_by, true);
        }
        $this->data['rows'] = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        foreach ($this->data['rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->transfers_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors ? $colors->name : '';
            }
        }
        $fromWarehouse = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $toWarehouse = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['from_warehouse'] = $fromWarehouse[$transfer->from_warehouse_id];
        $this->data['to_warehouse'] = $toWarehouse[$transfer->to_warehouse_id];
        $this->data['transfer'] = $transfer;
        $this->data['tid'] = $transfer_id;
        $this->data['created_by'] = $this->site->getUser($transfer->created_by);
        // $this->load->view($this->theme . 'transfers/view', $this->data);
         // Screen: Transfers Print View (view_print)
        // Used by: Generate Variant PO quick print flow (single-tab chaining)
        if ($this->input->get('print') == '1') {
            $this->load->view($this->theme . 'transfers/view_print', $this->data);
        } else {
            $this->load->view($this->theme . 'transfers/view', $this->data);
        }
    }

    function pdf($transfer_id = NULL, $view = NULL, $save_bufffer = NULL)
    {
        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        if (!$transfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers');
        }
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer->created_by);
        }
        $this->data['rows'] = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        foreach ($this->data['rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->transfers_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors ? $colors->name : '';
            }
        }
        $fromWarehouse = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $toWarehouse = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['from_warehouse'] = $fromWarehouse[$transfer->from_warehouse_id];
        $this->data['to_warehouse'] = $toWarehouse[$transfer->to_warehouse_id];
        $this->data['transfer'] = $transfer;
        $this->data['tid'] = $transfer_id;
        $this->data['created_by'] = $this->site->getUser($transfer->created_by);
        $name = lang("transfer") . "_" . str_replace('/', '_', $transfer->transfer_no) . ".pdf";
        $html = $this->load->view($this->theme . 'transfers/pdf', $this->data, TRUE);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'transfers/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }

    }

    function combine_pdf($transfers_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($transfers_id as $transfer_id) {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $transfer = $this->transfers_model->getTransferByID($transfer_id);
            if (!$transfer) {
                continue;
            }
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($transfer->created_by);
            }
            $this->data['rows'] = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
            foreach ($this->data['rows'] as $row) {
                if (!empty($row->shade_id)) {
                    $colors = $this->transfers_model->getProductOptionByID($row->shade_id);
                    $row->shade_name= $colors ? $colors->name : '';
                }
            }
            $fromWarehouse  = $this->site->getWarehouseByID($transfer->from_warehouse_id);
            $toWarehouse = $this->site->getWarehouseByID($transfer->to_warehouse_id);
            $this->data['from_warehouse'] = $fromWarehouse[$transfer->from_warehouse_id];
            $this->data['to_warehouse'] = $toWarehouse[$transfer->to_warehouse_id];
            $this->data['transfer'] = $transfer;
            $this->data['tid'] = $transfer_id;
            $this->data['created_by'] = $this->site->getUser($transfer->created_by);

            $html[] = array(
                'content' => $this->load->view($this->theme . 'transfers/pdf', $this->data, TRUE),
                'footer' => '',
            );
        }

        $name = lang("transfers") . ".pdf";
        $this->sma->generate_pdf($html, $name);

    }

    function email($transfer_id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        if (!$transfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers');
        }
        //$this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($transfer->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = NULL;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = NULL;
            }

            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $transfer->transfer_no,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            //$name = lang("transfer") . "_" . str_replace('/', '_', $transfer->transfer_no) . ".pdf";
            //$file_content = $this->pdf($transfer_id, NULL, 'S');
            //$attachment = array('file' => $file_content, 'name' => $name, 'mime' => 'application/pdf');
            $attachment = $this->pdf($transfer_id, NULL, 'S'); //delete_files($attachment);
            
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }


        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, NULL, NULL, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            //$this->session->set_flashdata('message', lang("email_sent"));
            //redirect("transfers");
            $this->session->set_flashdata('message', lang("email_sent_msg"));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/transfer.html')) {
                $transfer_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/transfer.html');
            } else {
                $transfer_temp = file_get_contents('./themes/default/views/email_templates/transfer.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('transfer_order').' (' . $transfer->transfer_no . ') '.lang('from').' ' . $transfer->from_warehouse_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $transfer_temp),
            );
            $towarehouse = $this->site->getWarehouseByID($transfer->to_warehouse_id);
            $this->data['warehouse'] = $towarehouse[$transfer->to_warehouse_id];

            $this->data['id'] = $transfer_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'transfers/email', $this->data);

        }
    }

    function delete($id = NULL)
    {
        $this->sma->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        
        $otransfer = $this->transfers_model->getTransferByID($id);
        if (!$otransfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers');
        }
        if($otransfer->status == 'completed'){
            $error = '<p class="text-danger">Failed: Completed transfer cannot delete.</p>';
            if($this->input->is_ajax_request()) {
                echo $error; die();
            }
            
            $this->session->set_flashdata('error', $error);
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        } else {
            if ($this->transfers_model->deleteTransfer($id, $otransfer)) {
                if($this->input->is_ajax_request()) {
                    echo lang("transfer_deleted"); die();
                }
                $this->session->set_flashdata('message', lang('transfer_deleted'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
            }
        }
    }

    function suggestions__old()
    {
        $this->sma->checkPermissions('index', TRUE);
        $term = $this->input->get('term', TRUE);
        $warehouse_id = $this->input->get('warehouse_id', TRUE);
        $warehouse2   = $this->input->get('warehouse_2', TRUE);

        if (strlen($term) < 3 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
    
        $exp = explode("_", $term); // Using Barcode

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->transfers_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
               // $option = FALSE;
                $option = ($exp[1])?$exp[1]:false; // Using Barcode Scan time
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->qty = 1;
                $row->discount = '0';
                $row->expiry = '';
                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $options = $this->transfers_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->transfers_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = 0;
                }
                $row->option = $option_id;
               /* $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if($pis){
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                } */
                
               /* $get_qty = $this->db->select('quantity')->where(['product_id'=>$row->id,'warehouse_id'=>$warehouse_id])->get('sma_warehouses_products')->row();*/

                if ($option_id){
                    $get_qty = $this->db->select('quantity')->where(['product_id'=>$row->id,'warehouse_id'=>$warehouse_id ,'option_id'=>$row->option])->get('sma_warehouses_products_variants')->row();

                } else {
                    $get_qty = $this->db->select('quantity')->where(['product_id'=>$row->id,'warehouse_id'=>$warehouse_id])->get('sma_warehouses_products')->row();
                }

                $row->quantity = $get_qty->quantity;
                $row->stockwarehouse2 = $this->getstockwarehousedata($warehouse2,$row->id, $option_id );
                if ($options) {                    
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                        $option_quantity = 0;
                        if($pis){                            
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        $option->quantity = $option_quantity;
                    }
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->real_unit_cost = $row->cost;
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
 
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    
    function suggestions() {

        $this->sma->checkPermissions('index', TRUE);
        $quantity = $this->input->get('quantity', true);
        $subtotal = $this->input->get('subtotal', true);
        $this->load->model('products_model');

        $term = $this->input->get('term', TRUE);
        $warehouse_id = $this->input->get('warehouse_id', TRUE);
        $warehouse2 = $this->input->get('warehouse_2', TRUE);
        $supplier_id = $this->input->get('supplier_id', TRUE);

        if (strlen($term) < 3 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $exp = explode("_", $term); // Using Barcode

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = (isset($analyzed['option_id']) && $analyzed['option_id']) ? $analyzed['option_id'] : 0;
        $option_color_id = (isset($analyzed['option_color_id']) && $analyzed['option_color_id']) ? $analyzed['option_color_id'] : 0;
        $rows = $this->transfers_model->getProductNames($sr, $warehouse_id);

        if ($rows) {
            $pr = array();
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {

                $option = (isset($exp[1]) && $exp[1]) ? $exp[1] : false; // Using Barcode Scan time
                $row->quantity = $row->quantity ? $row->quantity : 0;
                $row->item_tax_method = $row->tax_method;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->qty = 1;
                $row->discount = '0';
                $row->expiry = '';
                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                
                $opt = null;
                
                // $options = $this->products_model->getProductOptions($row->id);//existing function
                // $options = $this->products_model->getProductVariant($row->id); // change for variant popup functionality
                $options = $this->products_model->getProductOptionswithbatchAndWarehous($row->id, $warehouse_id, 1);
                if ($options) {
                    $option_id = $option_id ? $option_id : ($row->primary_variant ? $row->primary_variant : 0);
                    $opt = ($option_id) ? $options[$option_id] : reset($options); 
                    if ($opt && !$option_id) {
                        $option_id = $opt->id;
                    }
                    if($opt && $opt->cost > 0) {
                        $row->cost = $opt->cost;
                    }
                    if ($opt) {
                    $row->qty    = $opt->unit_quantity ? $opt->unit_quantity : 1;
                    $row->option = $option_id;
                    }
                } 
                
                if ($row->storage_type == 'loose' || !$options ) {
                    $options = FALSE;
                    $option_id = 0; 
                    $row->option = 0;
                }


                if ($options != FALSE) {
                    foreach ($options as $option) {
                        $option_quantity = 0;
                        $option_quantity_2 = 0;
                        $pis  = $this->site->getPurchasedItems($row->id, $warehouse_id, $option->id);
                        $pis2 = $this->site->getPurchasedItems($row->id, $warehouse2, $option->id);
                        
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += ($option->id == $pi->option_id) ? $pi->quantity_balance : 0;
                            }
                        }
                        if ($pis2) {
                            foreach ($pis2 as $pi2) {
                                $option_quantity_2 += ($option->id == $pi2->option_id) ? $pi2->quantity_balance : 0;
                            }
                        }
                        
                        if ($option_quantity) {
                            $option->org_quantity = $option_quantity;
                            $option->quantity = $option_quantity;
                        } else {
                           // unset($options[$option->id]);
                        }
                        
                        if (isset($options[$option->id])) {
                            $option->org_quantity2 = $option_quantity_2;
                            $option->quantity2 = $option_quantity_2;
                        }
                        
                        if($option_id == $option->id){
                            $row->quantity = $option_quantity;
                            $row->cost =   ($option->cost) ? $option->cost : $row->cost;
                            $row->stockwarehouse2 = $option_quantity_2;
                        }
                    }
                    foreach ($options as $option) {
                        if ($subtotal == '') {
                            $option->cost =$option->cost;
                        }else{
                            $option->cost = $subtotal;
                        }
                    }
                } else {
                    $item_quantity = 0;
                    $item_quantity_2 = 0;
                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id );
                    $pis2 = $this->site->getPurchasedItems($row->id, $warehouse2 );

                    if ($pis) {
                        foreach ($pis as $pi) {
                            $item_quantity += $pi->quantity_balance;
                        }
                    }
                    if ($pis2) {
                        foreach ($pis2 as $pi2) {
                            $item_quantity_2 += $pi2->quantity_balance;
                        }
                    }                    
                    $row->quantity = $item_quantity;                     
                    $row->stockwarehouse2 = $item_quantity_2;                    
                }

                /**
                 * Batch Config
                 * */
                $batchoption = $productbatches = false;
                $row->batch = false;
                $row->batch_number = '';
                $row->batch_quantity = $row->quantity;
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_unit_cost = $row->cost;
                $row->expiry = '';

                if ($this->Settings->product_batch_setting) {

                    $this->load->model('products_model');
                    $batch_option = ($option_id && $row->storage_type == 'packed') ? $option_id : 0;

                    $productbatches = $this->products_model->getProductVariantsBatch($row->id, $warehouse_id);

                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            if ($pi->batch_number && is_array($productbatches) && isset($productbatches[$pi->option_id]) && is_array($productbatches[$pi->option_id])) {
                                foreach ($productbatches[$pi->option_id] as $pioption => $piobatches) {
                                    if ($pi->batch_number == $piobatches->batch_no) {
                                        $productbatches[$pi->option_id][$piobatches->id]->quantity += $pi->quantity_balance;
                                    }
                                }
                            }
                        }
                    }

                    $batch = (is_array($productbatches) && isset($productbatches[$batch_option])) ? $productbatches[$batch_option] : null;

                    if ($batch) {
                        $firstKey = key($batch);
                        $batchoption = $batch;
                        $row->batch = $batchoption[$firstKey]->id;
                        $row->batch_number = $batchoption[$firstKey]->batch_no;
                        $row->batch_quantity = $batchoption[$firstKey]->quantity;
                        $row->cost = $batchoption[$firstKey]->cost;
                        $row->real_unit_cost = $batchoption[$firstKey]->cost;
                        $row->base_unit_cost = $batchoption[$firstKey]->cost;
                        $row->expiry = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';
                    }
                }
                /**
                 * End Batch Configs
                 * */
                if ($options) {
                    $opt = ($option_id && $r == 0 ) ? $this->transfers_model->getProductOptionByID($option_id) : reset($options);
                    if ($opt && (!$option_id || $r > 0)) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;                    
                    $option_id = false;
                }
                $options_color = $this->products_model->getProductOptionswithbatchAndWarehous($row->id, $warehouse_id, 2);
                if ($options_color) {
				    $opt_color = $option_color_id && $r == 0 ? $this->transfers_model->getProductOptionByID($option_color_id) : (is_array($options_color) ? reset($options_color) : false);
					if ($opt_color && (!$option_color_id || $r > 0)) {
						$option_color_id = $opt_color->id;
                        $row->option_color = $option_color_id;
                        $row->option_color_name = $opt_color->name;
                    }
                } else {
                    $opt_color = json_decode('{}');
                }
                
                $row->option = $option_id ? $option_id : 0;
                $row->supplier_part_no = '';
                if ($opt && $opt->cost != 0) {
                    $row->cost = $opt->cost;
                    $row->base_unit_cost = $row->cost;
                    $row->real_unit_cost = $row->cost;
                }
                $row->real_unit_cost = $row->cost;
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $row->qty = ($quantity) ? $quantity :  $row->qty ;
                $subtotal = $this->sma->formatDecimal($subtotal);
                $row_id = $row->id . $row->option;
                if ($row->batch) {
                    $row_id = $row_id . $row->batch;
                }
                $first_option = is_array($options_color) ? reset($options_color) : false;
                $color = (!empty($first_option) && isset($first_option->name)) ? $first_option->name : '';
                $label = $row->name . ' ' . $color . " (" . $row->code . ")";
                $ri = $this->Settings->item_addition ? $row_id : ($c + $r);

                $pr[] = ['id' => ($c + $r), 'item_id' => $row_id, 'option_id' => $option_id, 'batch_id' => $row->batch, 'label' => $label,
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => (is_array($options) ? array_values($options) : $options), 'option_batches' => $productbatches, 'batchs' => $batchoption, 'options_color' => $options_color ];
                $r++;
            }
            
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    
    function transfer_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    foreach ($_POST['val'] as $id) {
                        $this->transfers_model->deleteTransfer($id);
                    }
                    $this->session->set_flashdata('message', lang("transfers_deleted"));
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000') )));

                    $this->excel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:H1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Transfers');

                    $this->excel->getActiveSheet()->setTitle(lang('transfers'));
                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('from_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('to_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('Taxable amount'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('Total GST tax'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('status'));

                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $tansfer = $this->transfers_model->getTransferByID($id);
                        if (!$tansfer) {
                            continue;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($tansfer->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tansfer->transfer_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tansfer->from_warehouse_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $tansfer->to_warehouse_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $tansfer->total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $tansfer->total_tax);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $tansfer->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $tansfer->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'transfers_' . date('Y_m_d_H_i_s');
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

                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
            } else {
                $this->session->set_flashdata('error', lang("no_transfer_selected"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }
    }

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }

        if ($this->form_validation->run() == true && $this->transfers_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        } else {

            $this->data['inv'] = $this->transfers_model->getTransferByID($id);
            if (!$this->data['inv']) {
                $this->session->set_flashdata('error', lang('no_transfer_selected'));
                redirect('transfers');
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'transfers/update_status', $this->data);

        }
    }
    
     /*--------------------------------- Product List --------------------------------------*/
    public function getTransferProduct($warehouse_id = NULL, $werehouse_id_2= NULL){
       $rows = $this->transfers_model->getTransferProductList($warehouse_id,$werehouse_id_2 );
        echo json_encode($rows);
    }
    /*--------------------------------- Product List --------------------------------------*/
    
    public function getstockwarehouse(){
       $get_data =$this->input->get();
         // $this->db->select('quantity as werehouse_2_quantity')
         //         ->where(['product_id'=>$get_data['product'],'warehouse_id'=>$get_data['warehouse2']]);       
         //        $sql = $this->db->get('warehouses_products')->row();
         
        if($get_data['vartient']){
            $this->db->select('quantity as werehouse_quantity')
            ->where(['product_id'=>$get_data['product'],'warehouse_id'=>$get_data['warehouse'],'option_id'=>$get_data['vartient']]);       
            $sql = $this->db->get('warehouses_products_variants')->row();
        } else {
            $this->db->select('quantity as werehouse_quantity')
                  ->where(['product_id'=>$get_data['product'],'warehouse_id'=>$get_data['warehouse']]);       
                $sql = $this->db->get('warehouses_products')->row();
        }
        echo json_encode($sql->werehouse_quantity);
    }
    
    public function getstockwarehousedata($warehouse, $productid, $option){
        
       
         if($option != NULL){
            $this->db->select('quantity as werehouse_2_quantity')
            ->where(['product_id'=>$productid,'warehouse_id'=>$warehouse,'option_id'=>$option]);       
            $sql = $this->db->get('warehouses_products_variants')->row();
        }else{
            $this->db->select('quantity as werehouse_2_quantity')
                  ->where(['product_id'=>$productid,'warehouse_id'=>$warehouse]);       
                $sql = $this->db->get('warehouses_products')->row();
        }
        
//        $this->db->last_query();
//     
//        echo json_encode($sql->werehouse_2_quantity);
        
        return ($sql && isset($sql->werehouse_2_quantity)) ? $sql->werehouse_2_quantity : 0;
    }
     
    public function getQuantity(){
        
        $warehouse_id = array();
        $vartient = $this->input->get('vartient', TRUE);
        $warehouse_id[0] = $this->input->get('from_warehouse', TRUE);
        $warehouse_id[1] = $this->input->get('to_warehouse', TRUE);
       
        $rows = $this->transfers_model->getVariantQuantity($vartient,$warehouse_id);
      
        echo json_encode($rows);
   
    }
    
    // Start Request
    
    public function add_request()
    {        
        $this->sma->checkPermissions();
        
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
                        
            $transfer_request_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('treq');
            
            if ($this->Owner || $this->Admin ||  $this->GP['transfers-add_request']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            
            $to_warehouse   = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
            $note           = $this->sma->clear_tags($this->input->post('note'));
            $shipping       = $this->input->post('shipping');
            $shipping = $shipping == '' ? 0 : (float)$shipping;
            
            $status = 'pending'; // $this->input->post('status');
            
            $fromWarehouseDetails   = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_details     = $fromWarehouseDetails[$from_warehouse];
            $from_warehouse_code        = $from_warehouse_details->code;
            $from_warehouse_name        = $from_warehouse_details->name;
            $from_warehouse_state_code  = $from_warehouse_details->state_code;
            
            $toWarehouseDetails     = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_details       = $toWarehouseDetails[$to_warehouse];
            $to_warehouse_code          = $to_warehouse_details->code;
            $to_warehouse_name          = $to_warehouse_details->name;
            $to_warehouse_state_code    = $to_warehouse_details->state_code;
 
            $total = 0;
            $product_tax = 0;

            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            
            for ($r = 0; $r < $i; $r++) {
                
                $item_code          = $_POST['product_code'][$r];
                $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_expiry        = isset($_POST['expiry'][$r]) ? $this->sma->fsd($_POST['expiry'][$r]) : NULL;
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->transfers_model->getProductByCode($item_code);
                    
                    $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option);
//                    if ($warehouse_quantity->quantity < $item_unit_quantity) {
//                        $this->session->set_flashdata('error', "Request stock should be less than or equal to stock. (" . lang('product_name') . " <strong>" . $product_details->name . "</strong> " . lang('product_code') . " <strong>" . $product_details->code . "</strong>)");
//                        redirect("transfers/request");
//                    }
                    
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $item_tax=0;
                        $tax = "";
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
							
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            }
                        } elseif ($tax_details->type == 2) {
							
                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = ($product_details && $product_details->tax_method == 1) ? $this->sma->formatDecimal($unit_cost) : $this->sma->formatDecimal($unit_cost-$item_tax, 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'request_quantity'  => $item_unit_quantity,
                        'sent_quantity'     => 0,
                        'quantity_balance'  => $item_unit_quantity,
                        'tax_rate_id'       => $pr_tax,
                        'tax'               => $tax,
                        'item_tax'          => $pr_item_tax,                        
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                        'subtotal'          => $this->sma->formatDecimal($subtotal),                        
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'warehouse_id'      => $to_warehouse,
                        'real_unit_cost'    => $real_unit_cost,
                        'item_status'       => $status,
                        'date'              => date('Y-m-d', strtotime($date))
                    ];

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
			//echo $total.' ff '.$shipping.' gg '.$product_tax;
            $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            $data = [   
                        'transfer_request_no'       => $transfer_request_no,
                        'date'                      => $date,
                        'from_warehouse_id'         => $from_warehouse,
                        'from_warehouse_code'       => $from_warehouse_code,
                        'from_warehouse_name'       => $from_warehouse_name,
                        'from_warehouse_state_code' => $from_warehouse_state_code,
                        'to_warehouse_id'           => $to_warehouse,
                        'to_warehouse_code'         => $to_warehouse_code,
                        'to_warehouse_name'         => $to_warehouse_name,
                        'to_warehouse_state_code'   => $to_warehouse_state_code,
                        'note'                      => $note,
                        'total_tax'                 => $product_tax,
                        'total'                     => $total,
                        'grand_total'               => $grand_total,
                        'created_by'                => $this->session->userdata('user_id'),
                        'status'                    => $status,
                        'shipping'                  => $shipping
                    ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
		 
        if ($this->form_validation->run() == true && $this->transfers_model->addRequest($data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("New Transfer Request Added Successfully."));
            redirect("transfers/request");
            
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['rnumber'] =  ''; //$this->site->getReference('treq');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('transfers/request'), 'page' => lang('Transfers_Request')), array('link' => '#', 'page' => lang('Add Request')));
            $meta = array('page_title' => lang('Transfer_Request'), 'bc' => $bc);
                       
            $this->page_construct('transfers/add_request', $meta, $this->data);
        }
    }
       
    public function edit_request($id = NULL)
    {
        $this->sma->checkPermissions();
        $this->data['page_mode'] = 'edit';
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        
        $transfer_request = $this->transfers_model->getTransferRequestByID($id);
        if (!$transfer_request) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers/request');
        }
        $last_status = $transfer_request->status;
        
        if($this->session->userdata('view_right')=='0'){ 
            if (!$this->session->userdata('edit_right')) {
              $this->sma->view_rights($transfer_request->created_by);
            }
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');              
        $this->form_validation->set_rules('to_warehouse', lang("warehouse") . ' (' . lang("to") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang("warehouse") . ' (' . lang("from") . ')', 'required|is_natural_no_zero');
                
        if ($this->form_validation->run()) {
            
            $transfer_request_no = $this->input->post('reference_no');
            $transfer_request_id = $id;
            
            if ($this->Owner || $this->Admin ) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');                
            }
            $today = date('Y-m-d H:i:s');
            
            $note           = $this->sma->clear_tags($this->input->post('note'));
            $shipping       = $this->input->post('shipping');
            $status         = $this->input->post('status');
                         
            $to_warehouse   = $this->input->post('to_warehouse');
            $from_warehouse = $this->input->post('from_warehouse');
                        
            $fromWarehouseDetails       = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_details     = $fromWarehouseDetails[$from_warehouse];           
            $from_warehouse_code        = $from_warehouse_details->code;
            $from_warehouse_name        = $from_warehouse_details->name;
            $from_warehouse_state_code  = $from_warehouse_details->state_code;
            
            $toWarehouseDetails         = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_details       = $toWarehouseDetails[$to_warehouse];
            $to_warehouse_code          = $to_warehouse_details->code;
            $to_warehouse_name          = $to_warehouse_details->name;
            $to_warehouse_state_code    = $to_warehouse_details->state_code;

            $total = 0; $total2 = 0;
            $product_tax  = 0;
            $product_tax2 = 0;
            
            if($status != 'partial' && $last_status == $status){
                $error = 'Status has been not changed';
                $this->session->set_flashdata('error', $error);
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
            }
            
            if($status == 'closed'){
                $this->db->update('transfer_request',['status'=>$status], ['id'=>$id]);
                $msg = 'Request has been updated successfully.';
                $this->session->set_flashdata('message', $msg);
                redirect('transfers/request'); 
            }
            
            if($status == 'partial' || $status == 'sent'){                
                $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;                
                for ($r = 0; $r < $i; $r++) {                                
                    if($_POST['quantity'][$r] > $_POST['quantity_balance'][$r]){
                        $error = 'Product ['.$_POST['product_name'][$r].'] quantity should not greater than balance quantity.';
                        $this->session->set_flashdata('error', $error);
                        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                    }
                }
            }
            
            if($last_status != 'completed' && $last_status != 'closed' ){
                
                $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
                $request['status'] = 'completed'; 
                                
                for ($r = 0; $r < $i; $r++) {
                    
                    $item_code          = $_POST['product_code'][$r];
                    $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                    $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                    $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                    $item_expiry        = isset($_POST['expiry'][$r]) ? $this->sma->fsd($_POST['expiry'][$r]) : NULL;
                    $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                    $item_unit          = $_POST['product_unit'][$r];
                    
                    $item_quantity          = $_POST['product_base_quantity'][$r];
                    $item_unit_quantity     = $_POST['quantity'][$r];
                    
                    $item_request_quantity  = $_POST['request_quantity'][$r];
                    $sent_quantity          = $_POST['sent_quantity'][$r] ? $_POST['sent_quantity'][$r] : 0;
                    $quantity_received      = $_POST['quantity_received'][$r];
                    $quantity_balance       = $_POST['quantity_balance'][$r];
                    $ordered_quantity       = $_POST['ordered_quantity'][$r];
                     
                    if($quantity_balance == 0 && $item_request_quantity == $sent_quantity){                        
                        continue;
                    }
                    
                    if (isset($_POST['product_code'][$r]) && isset($real_unit_cost) && isset($unit_cost) && ($item_unit_quantity > 0 || $status == 'edit_pending_request')) {
                        
                        $sent_quantity      = ($status == 'edit_pending_request') ? 0 : ($sent_quantity + $item_unit_quantity);
                        $quantity_balance   = ($status == 'edit_pending_request') ? $item_request_quantity : ($item_request_quantity - $sent_quantity);
                        $item_unit_quantity = ($status == 'edit_pending_request') ? $item_request_quantity : $item_unit_quantity;
                                
                        $product_details = $this->transfers_model->getProductByCode($item_code);
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $pr_item_tax2  =0;
                        $tax = "";
                                                
                        if ((isset($item_tax_rate) && $item_tax_rate != 0) || $product_details->tax_rate > 1) {
                            $pr_tax = $item_tax_rate ? $item_tax_rate : $product_details->tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                                if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                    }
                                } elseif ($tax_details->type == 2) {

                                    $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                    $tax = $tax_details->rate;

                                } elseif ($tax_details->rate == 0) {
                                   $item_tax = 0;
                                   $tax = $tax_details->rate . "%";
                                   if ($tax_details->type == 2)
                                      $tax = $tax_details->rate; 
                                }
                                $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                                $pr_item_tax2 = $this->sma->formatDecimal($item_tax * $sent_quantity, 4);
                        }

                        $item_net_cost   = ($product_details && $product_details->tax_method == 1) ? $this->sma->formatDecimal($unit_cost) : $this->sma->formatDecimal($unit_cost-$item_tax, 4);
                        $product_tax    += $pr_item_tax;
                        $product_tax2   += $pr_item_tax2; 
                        $subtotal        = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                        $subtotal2       = $this->sma->formatDecimal((($item_net_cost * $sent_quantity) + $pr_item_tax2), 4);
                        $unit            = $this->site->getUnitByID($item_unit);
                                         
                        $transfer_items[$r] =  [
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $product_details->name,
                                    'option_id'         => $item_option,
                                    'net_unit_cost'     => $item_net_cost,
                                    'unit_cost'         => $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $item_unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_unit_quantity,                             
                                    'warehouse_id'      => $to_warehouse,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $pr_tax,
                                    'tax'               => $tax,
                                    'subtotal'          => $this->sma->formatDecimal($subtotal),
                                    'expiry'            => $item_expiry,
                                    'real_unit_cost'    => $real_unit_cost,
                                    'date'              => date('Y-m-d', strtotime($date)),
                                    'item_status'       => 'sent',
                                ];
                        
                        $request_items[$r] = $transfer_items[$r];
                        
                        if($status == 'edit_pending_request'){
                            $request_items[$r]['request_quantity']  = $item_request_quantity;
                            $request_items[$r]['sent_quantity']     = $sent_quantity; 
                            $request_items[$r]['item_status']       = 'pending';       
                        } else {                        
                            $request_items[$r]['item_tax']          = $pr_item_tax2;
                            $request_items[$r]['subtotal']          = $subtotal2;
                            $request_items[$r]['quantity']          = $sent_quantity;
                            $request_items[$r]['unit_quantity']     = $sent_quantity;                        
                            $request_items[$r]['quantity_balance']  = $quantity_balance;                             
                            $request_items[$r]['request_quantity']  = $item_request_quantity;
                            $request_items[$r]['sent_quantity']     = $sent_quantity; 
                            $request_items[$r]['item_status']       = $quantity_balance > 0 ? 'partial' : 'completed';                         
                      
                            if($request['status'] == 'completed' && $quantity_balance > 0){
                                $request['status'] = 'partial';
                            }
                        }
                        
                        $total  += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                        $total2 += $this->sma->formatDecimal(($item_net_cost * $sent_quantity), 4);                        
                    } 
                }
               
                if (empty($transfer_items)) {
                    $this->form_validation->set_rules('product', 'Transfer Items Is Required', 'required');
                } else {
                    krsort($transfer_items);
                }
            }
            
            $grand_total  = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            $grand_total2 = $this->sma->formatDecimal(($total2 + $shipping + $product_tax2), 4);
                        
            $request['total']       = $total2;
            $request['total_tax']   = $product_tax2;            
            $request['grand_total'] = $grand_total2;
            
            $request['shipping']    = $shipping;
            
            $transfer_no = $this->site->getReference('to');
            
            $transfer = [
                'transfer_no'               => $transfer_no,
                'date'                      => $today,
                'from_warehouse_id'         => $from_warehouse,
                'from_warehouse_code'       => $from_warehouse_code,
                'from_warehouse_name'       => $from_warehouse_name,
                'from_warehouse_state_code' => $from_warehouse_state_code,
                'to_warehouse_id'           => $to_warehouse,
                'to_warehouse_code'         => $to_warehouse_code,
                'to_warehouse_name'         => $to_warehouse_name,
                'to_warehouse_state_code'   => $to_warehouse_state_code,
                'note'                      => $note,
                'total_tax'                 => $product_tax,
                'total'                     => $total,
                'grand_total'               => $grand_total,
                'created_by'                => $this->session->userdata('user_id'),
                'status'                    => 'sent',
                'shipping'                  => $shipping,
                'transfer_request_no'       => $transfer_request_no,
                'transfer_request_id'       => $transfer_request_id,
            ];
            
            if($status == 'edit_pending_request'){
                
                $request = $transfer;
                $request['status'] = 'pending';
                unset($request['transfer_no'], $request['created_by'], $request['transfer_request_no'], $request['transfer_request_id']);
                unset($transfer);
                unset($transfer_items);
            }
  
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true ) {
            
           
            $is_jw_rm = in_array($this->router->fetch_method(), array('jw_edit_transfer', 'jw_edit_request'), TRUE);
            if($status == 'edit_pending_request' && $this->transfers_model->updateTransferRequest($id, $request, $request_items, NULL, NULL)){
                $this->session->set_userdata('remove_tols', 1);
                $this->session->set_flashdata('message', lang("Request Updated"));
                redirect($is_jw_rm ? "transfers/jw_edit_transfer/" . $id : "transfers/request"); 
                 
            } else if($this->transfers_model->updateTransferRequest($id, $request, $request_items, $transfer, $transfer_items)) {
                 
                $this->session->set_userdata('remove_tols', 1);
                $this->session->set_flashdata('message', lang("transfer_added"));
                redirect("transfers");
            }
            
        } 
        else {
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['transfer_request'] = $transfer_request = $this->transfers_model->getTransferRequestByID($id);
            if (!$transfer_request) {
                $this->session->set_flashdata('error', lang('no_transfer_selected'));
                redirect('transfers/request');
            }
			
            $transfer_request_items = $this->transfers_model->getAllTransferRequestItems($id) ?: array(); 
            
            krsort($transfer_request_items);
            $pr = array();
            $c = rand(100000, 9999999);
            foreach ($transfer_request_items as $item) {               
                
                $row = $this->site->getProductByID($item->product_id);
                
                if (!$row) {
                    $row = json_decode('{}');
                } else {
                    unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
               
                $row->base_quantity = ($item->request_quantity) ? $item->request_quantity - $item->sent_quantity : $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->expiry = '';
               
                if($transfer_request->status=='completed' || $transfer_request->status=='sent'){
                    $row->qty = ($item->request_quantity) ? $item->sent_quantity  : $item->unit_quantity;
                } elseif($this->data['transfer_request']->status=='partial'){
                    $row->qty = ($item->request_quantity) ? $item->sent_quantity  : $item->unit_quantity;
                } else {
                    $row->qty = ($item->request_quantity) ? $item->request_quantity - $item->sent_quantity : $item->unit_quantity; 
                }	
               
                $row->transfer_request_id = $item->transfer_request_id;
                $row->item_status      = $item->item_status ? $item->item_status : $transfer_request->status;
                $row->quantity_balance = $item->quantity_balance;
                $row->ordered_quantity = $item->quantity;
                $row->request_quantity = $item->request_quantity;
                $row->sent_quantity    = $item->sent_quantity;
                $row->cost             = $item->unit_cost;
                if($item->quantity==0) {
                    $row->unit_cost = $item->net_unit_cost+(0);
                } else {
                    $row->unit_cost = $item->net_unit_cost+($item->item_tax/$item->quantity);
                }
                $row->real_unit_cost    = $item->real_unit_cost;
                $row->tax_rate          = $item->tax_rate_id;
                $row->option            = 0;
                $row->batch_number      = $item->batch_number ? $item->batch_number : FALSE;
                $options = FALSE;
                $row->quantity = 0;
                
                if($row->storage_type == 'packed') {              
                    //$options = $this->transfers_model->getProductOptions($row->id, $transfer_request->from_warehouse_id, FALSE);
                    $options = $this->products_model->getProductOptions($row->id);
                } 
                
                if ($options) {
                    foreach ($options as $option) {
                        $option_quantity = 0;
                        $option_quantity_2 = 0;                         
                        $pis  = $this->site->getPurchasedItems($row->id, $transfer_request->from_warehouse_id, $option->id);
                        $pis2 = $this->site->getPurchasedItems($row->id, $transfer_request->to_warehouse_id, $option->id);
                        
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += ($option->id == $pi->option_id) ? $pi->quantity_balance : 0;
                            }
                        }
                        if ($pis2) {
                            foreach ($pis2 as $pi2) {
                                $option_quantity_2 += ($option->id == $pi2->option_id) ? $pi2->quantity_balance : 0;
                            }
                        }
                        
                        if ($option_quantity > 0) {
                            $option->org_quantity = $option_quantity;
                            $option->quantity = $option_quantity;
                        } else {
                            unset($options[$option->id]);
                        }
                        
                        if (isset($options[$option->id])) {
                            $option->org_quantity2 = $option_quantity_2;
                            $option->quantity2 = $option_quantity_2;
                        }
                        
                        if($item->option_id == $option->id){                            
                            if($option->cost) {
                                $row->cost = $option->cost;
                            }
                            $row->quantity = $option_quantity;
                            $row->stockwarehouse2 = $option_quantity_2;
                            $row->option = $option->id;
                        }
                    }
                } else {
                    $row->option = 0;
                    $item_quantity = 0;
                    $item_quantity_2 = 0;
                     
                    $pis  = $this->site->getPurchasedItems($row->id, $transfer_request->from_warehouse_id );
                    $pis2 = $this->site->getPurchasedItems($row->id, $transfer_request->to_warehouse_id );
                    
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $item_quantity += $pi->quantity_balance;
                        }
                    }
                    if ($pis2) {
                        foreach ($pis2 as $pi2) {
                            $item_quantity_2 += $pi2->quantity_balance;
                        }
                    }                    
                    $row->quantity = $item_quantity;                     
                    $row->stockwarehouse2 = $item_quantity_2;                    
                }

                /**
                 * Batch Config
                 * */
                $batchoption = $productbatches = false;
                $row->batch = false;
                $row->batch_number = '';
                $row->batch_quantity = $row->quantity;
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_unit_cost = $row->cost;
                $row->expiry = '';

                if ($this->Settings->product_batch_setting) {
                    
                    $batch_option = ($option_id && $row->storage_type == 'packed') ? $option_id : 0;

                    $productbatches = $this->products_model->getProductVariantsBatch($row->id);

                    $pis = $this->site->getPurchasedItems($row->id, $transfer_request->from_warehouse_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            if ($pi->batch_number && is_array($productbatches[$pi->option_id])) {
                                foreach ($productbatches[$pi->option_id] as $pioption => $piobatches) {
                                    if ($pi->batch_number == $piobatches->batch_no) {
                                        $productbatches[$pi->option_id][$piobatches->id]->quantity += $pi->quantity_balance;
                                    }
                                }
                            }
                        }
                    }

                    $batch = $productbatches[$batch_option];

                    if ($batch) {
                        $firstKey = key($batch);
                        $batchoption = $batch;
                        $row->batch = $batchoption[$firstKey]->id;
                        $row->batch_number = $batchoption[$firstKey]->batch_no;
                        $row->batch_quantity = $batchoption[$firstKey]->quantity;
                        $row->cost = $batchoption[$firstKey]->cost;
                        $row->real_unit_cost = $batchoption[$firstKey]->cost;
                        $row->base_unit_cost = $batchoption[$firstKey]->cost;
                        $row->expiry = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';
                    }
                }
                /**
                 * End Batch Configs
                 * */
                $row->real_unit_cost = $row->cost;                
                
                $units      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);
                                 
                $row_id = $row->id . $row->option;
                
                $ri = $this->Settings->item_addition ? $row_id : $c;
                
                $pr[$ri] = ['id' => $c, 'item_id' => $row_id, 'option_id'=> $row->option, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options ];
                $c++;
            }
	                  
                                
            $this->data['transfer_request_items'] = json_encode($pr);
            $this->data['id']           = $id;
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();

            $is_jw_rm = in_array($this->router->fetch_method(), array('jw_edit_transfer', 'jw_edit_request'), TRUE);
            $bc = array(
                array('link' => base_url(), 'page' => lang('home')),
                array('link' => site_url($is_jw_rm ? 'transfers/transfer_rm' : 'transfers/request'), 'page' => $is_jw_rm ? 'Transfer RM' : lang('Transfer Requests')),
                array('link' => '#', 'page' => lang('edit'))
            );
            $meta = array('page_title' => lang('edit_transfer_request'), 'bc' => $bc);
            $view_file = $is_jw_rm ? 'transfers/jw_edit_transfer' : 'transfers/edit_request';
            $this->page_construct($view_file, $meta, $this->data);
        }
    }

    public function jw_edit_transfer($id = NULL)
    {
        $this->edit_request($id);
    }
       
    public function request() {
                                
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Product Requests')));
        $meta = array('page_title' => lang('Products Requests'), 'bc' => $bc);
        $this->page_construct('transfers/request', $meta, $this->data);
    }

    public function getTransfersRequests()
    {
        $this->sma->checkPermissions('request');

        $detail_link = anchor('transfers/view_request/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-target="#myModal"');
       // $email_link = anchor('transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('transfers/edit_request/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
       // $pdf_link = anchor('transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
       // $print_barcode = anchor('products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . lang("delete_product_request") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . site_url('transfers/delete_request/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete_Request') . "</a>";
        $cancel_link = "<a href='#' class='tip po' title='<b>" . lang("cancel_product_request") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger ' id='a__$1' href='" . site_url('transfers/calcel_request/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-remove\"></i> "
            . lang('Cancel_Request') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>         
            <li>' . $cancel_link . '</li>         
            <li>' . $delete_link . '</li>         
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select("id, date, transfer_request_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, total, total_tax, grand_total, status, attachment")
            ->from('transfer_request')
            ->edit_column("fname", "$1 ($2)", "fname, fcode")
            ->edit_column("tname", "$1 ($2)", "tname, tcode");

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        if($this->session->userdata('view_right')){
          if($this->input->get('warehouse')){
             $getwarehouse = str_replace("_",",",$this->input->get('warehouse')); 
             $this->datatables->where('to_warehouse_id IN ('.$getwarehouse.')');
             $this->datatables->or_where('from_warehouse_id IN ('.$getwarehouse.')');
          }
       }

        $this->datatables->add_column("Actions", $action, "id")
            ->unset_column('fcode')
            ->unset_column('tcode');
        echo $this->datatables->generate();
    }

    public function view_request($request_id = NULL)
    {
        $this->sma->checkPermissions('request', TRUE);

        if ($this->input->get('id')) {
            $request_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer_request = $this->transfers_model->getTransferRequestByID($request_id);
        if (!$transfer_request) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers/request');
        }
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer_request->created_by, true);
        }
        $this->data['rows'] = $this->transfers_model->getAllTransferRequestItems($request_id);
        $fromWarehouse = $this->site->getWarehouseByID($transfer_request->from_warehouse_id);
        $toWarehouse = $this->site->getWarehouseByID($transfer_request->to_warehouse_id);
        $this->data['from_warehouse'] = $fromWarehouse[$transfer_request->from_warehouse_id];
        $this->data['to_warehouse'] = $toWarehouse[$transfer_request->to_warehouse_id];
        $this->data['transfer_request'] = $transfer_request;
        $this->data['trid'] = $request_id;
        $this->data['created_by'] = $this->site->getUser($transfer_request->created_by);
        $this->load->view($this->theme . 'transfers/view_request', $this->data);
    }
        
    public function calcel_request($request_id=null) {
        
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $request_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $request = $this->transfers_model->getTransferRequestByID($request_id);
        if (!$request) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers/request');
        }
        
        if($request->status == 'pending'){
            
            $this->db->update('transfer_request',['status'=>'cancelled'], ['id' => $request_id]);
            $msg = 'Request has been cancelled successfully.';
            if($this->input->is_ajax_request()) {
                echo $msg; die();
            }
            $this->session->set_flashdata('message', $msg);
            redirect('transfers/request'); 
            
        } else {
            
            $error = '<p class="text-danger">Request that have already been '.$request->status.' cannot be cancelled.</p>';
            if($this->input->is_ajax_request()) {
                echo $error; die();
            }
            
            $this->session->set_flashdata('error', $error);
            redirect('transfers/request'); 
        }
        
        
    }
    
    public function delete_request($id = NULL)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        
        $otransfer = $this->transfers_model->getTransferRequestByID($id);
        if (!$otransfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers/request');
        }
        if($otransfer->status != 'pending' && $otransfer->status != 'cancelled'){
            $error = '<p class="text-danger">Request that have already been '.$otransfer->status.' cannot be deleted.</p>';            
            if($this->input->is_ajax_request()) {
                echo $error; die();
            }
            
            $this->session->set_flashdata('error', $error);
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        } else {
            if ($this->transfers_model->deleteTransferRequest($id)) {
                if($this->input->is_ajax_request()) {
                    echo lang("transfer_deleted"); die();
                }
                $this->session->set_flashdata('message', lang('transfer_deleted'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
            }
        }
    }
    
    
    // End Request
    //Transfer Report
    public function load_transfer_report_data($pdf = NULL, $xls = NULL){
        
        $user = $this->input->get('created_by') ? $this->input->get('created_by') : NULL;
        //echo "<br>";
        $fromWarehouse = $this->input->get('fromWarehouse') ? $this->input->get('fromWarehouse') : NULL;
        //echo "<br>";
        $toWarehouse = $this->input->get('toWarehouse') ? $this->input->get('toWarehouse') : NULL;
        //echo "<br>";
        $reference_no = $this->input->get('refno') ? $this->input->get('refno') : NULL;
        //echo "<br>";
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        //echo "<br>";
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        //echo "<br>";
        $status = $this->input->get('status') ? $this->input->get('status') : NULL;
        //echo "<br>";
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        //echo "<br>";
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        //echo "<br>";
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
           // echo "<br>";
            $end_date = $this->sma->fld($end_date);
            //echo "<br>";
        }
        $this->load->library('datatables');

        // Build an aggregate subquery so GROUP_CONCAT columns can be searched
        // safely via their aliases (code/products/variant) without putting
        // GROUP_CONCAT directly into the main WHERE clause (which causes
        // MySQL error 1111: Invalid use of group function).
        $itemsAgg = "(SELECT
                sma_transfers.id AS transfer_id,
                GROUP_CONCAT(COALESCE(p_items.product_code, t_items.product_code) SEPARATOR '\\n ') AS code,
                GROUP_CONCAT(COALESCE(p_items.product_name, t_items.product_name) SEPARATOR '\\n ') AS products,
                GROUP_CONCAT(COALESCE(pv1.name, pv2.name) SEPARATOR '\\n ') AS variant
            FROM sma_transfers
            LEFT JOIN sma_transfer_item_new t_items
                ON sma_transfers.id = t_items.transfer_id
                AND sma_transfers.status <> 'completed'
            LEFT JOIN sma_purchase_items p_items
                ON sma_transfers.id = p_items.transfer_id
                AND sma_transfers.status = 'completed'
            LEFT JOIN sma_product_variants pv2
                ON t_items.option_id = pv2.id
            LEFT JOIN sma_product_variants pv1
                ON p_items.option_id = pv1.id
            GROUP BY sma_transfers.id
        ) items_agg";

        $this->datatables->select(
            "sma_transfers.id as id,
             CAST(sma_transfers.date AS DATE) as date,
             CAST(sma_transfers.date AS TIME) as time,
             transfer_no,
             from_warehouse_name,
             to_warehouse_name,
             grand_total,
             sma_transfers.status as status,
             items_agg.code,
             items_agg.products,
             items_agg.variant,
             sma_users.username as user",
            FALSE
        )
        ->from('transfers')
        ->join('transfer_item_new t_items', 'sma_transfers.id = t_items.transfer_id AND sma_transfers.status <> "completed"', 'left')
        ->join('purchase_items p_items', 'sma_transfers.id = p_items.transfer_id AND sma_transfers.status = "completed"', 'left')
        ->join('products', '(sma_products.id = t_items.product_id OR sma_products.id = p_items.product_id)', 'left')
        ->join('users sma_users', 'sma_transfers.created_by = sma_users.id', 'left')
        ->join($itemsAgg, 'items_agg.transfer_id = sma_transfers.id', 'left', FALSE);

       if($reference_no)
       {
            $this->datatables->where('sma_transfers.id', $reference_no);
       }
       if($user)
       {
            $this->datatables->where('sma_transfers.created_by', $user);
       }
       if($fromWarehouse)
       {
            $this->datatables->where('sma_transfers.from_warehouse_id', $fromWarehouse);
       }
       if($toWarehouse)
       {
            $this->datatables->where('sma_transfers.to_warehouse_id', $toWarehouse);
       }
       if($status)
       {
            $this->datatables->where('sma_transfers.status', $status);
       }
        if($product) {
            $this->datatables->where('(t_items.product_id = ' . (int)$product . ' OR p_items.product_id = ' . (int)$product . ')');
        }
        
        if($brand) {
            $this->datatables->join('brands', 'sma_products.brand=sma_brands.id', 'left');
            $this->datatables->where('brands.id', $brand);
        }
        
        if($category) {
            $this->datatables->join('categories', 'sma_products.category_id=sma_categories.id', 'left');
            $this->datatables->where('sma_categories.id', $category);
        }    
        if($start_date)
        {
            $this->datatables->where('DATE(' . $this->db->dbprefix('transfers') . '.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->group_by('sma_transfers.id');
        echo $this->datatables->generate();
    }
    function transfer_actions1()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000') )));
                    //Exel heading.
                    $this->excel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:K1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Transfers Report');
                    $this->excel->getActiveSheet()->setTitle(lang('transfers '));
                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('from_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('to_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('Created By'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('Code'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('Product'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('Variant'));
                    $this->excel->getActiveSheet()->SetCellValue('I2', lang('Quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('J2', lang('Unit Cost'));
                    $this->excel->getActiveSheet()->SetCellValue('K2', lang('Tax'));
                    $this->excel->getActiveSheet()->SetCellValue('L2', lang('Subtotal'));
                    $this->excel->getActiveSheet()->SetCellValue('M2', lang('Total'));
                    $this->excel->getActiveSheet()->SetCellValue('N2', lang('Status'));
                    $this->excel->getActiveSheet()->SetCellValue('O2', lang('User'));
                    //database name for excel content.
                    $row = 3;
                    
                    // Initialize footer variables
                    $footer_qty = 0;
                    $footer_unit_cost = 0;
                    $footer_tax = 0;
                    $footer_subtotal = 0;
                    $footer_total = 0;
                    
                    foreach ($_POST['val'] as $id) {

                        //call model
                        $tansfer = $this->transfers_model->getTransferByID($id);
                        if (!$tansfer) {
                            continue;
                        }
                        
                        // Get products based on status like getAllTransferReportItems
                        if ($tansfer->status == 'completed') {
                            $this->db->select('purchase_items.*, product_variants.name as variant, products.unit')
                                    ->from('purchase_items')
                                    ->join('products', 'products.id=purchase_items.product_id', 'left')
                                    ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
                                    ->group_by('purchase_items.id')
                                    ->where('transfer_id', $id);
                        } else {
                            $this->db->select('transfer_item_new.*, product_variants.name as variant, products.unit')
                                    ->from('transfer_item_new')
                                    ->join('products', 'products.id=transfer_item_new.product_id', 'left')
                                    ->join('product_variants', 'product_variants.id=transfer_item_new.option_id', 'left')
                                    ->group_by('transfer_item_new.id')
                                    ->where('transfer_id', $id);
                        }
                        $q = $this->db->get();
                        $products = $q->result();
                       
                        $k=0;
                        $product_name=array();
                        $quantity = array();
                        $unit_cost = array();
                        $tax = array();
                        $subtotal = array();
                        $total = array();
                        $product_code = array();
                        $product_variant = array();

                        foreach ($products as $product) {
                            
                            $product_name[$k] = $product->product_name;
                            $quantity[$k] = $product->quantity;
                            $unit_cost[$k] = $product->unit_cost;
                            $tax[$k] = ($product->item_tax/$product->quantity);
                            $subtotal[$k] = ($tax[$k] + $product->unit_cost);
                            $total[$k] = $product->subtotal;
                            $product_code[$k] = isset($product->product_code) ? $product->product_code : '';
                            $product_variant[$k] = isset($product->variant) ? $product->variant : '';

                            $k++;                        
                        }
                        $size = sizeof($product_name);

                        $user = $this->transfers_model->getUserByID($id);
                        $this->excel->getActiveSheet()->mergeCells("A".$row.":A".($row+($size-1)));
                        $this->excel->getActiveSheet()->SetCellValue('A'.$row, $this->sma->hrld($tansfer->date));

                        $this->excel->getActiveSheet()->mergeCells("B".$row.":B".($row+($size-1)));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tansfer->transfer_no);

                        $this->excel->getActiveSheet()->mergeCells("C".$row.":C".($row+($size-1)));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tansfer->from_warehouse_name);

                        $this->excel->getActiveSheet()->mergeCells("D".$row.":D".($row+($size-1)));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $tansfer->to_warehouse_name);

                        $this->excel->getActiveSheet()->mergeCells("E".$row.":E".($row+($size-1)));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user[0]->username);

                        for($i=0;$i<$size;$i++)
                        {
                            $this->excel->getActiveSheet()->SetCellValue('F' . ($row+$i), $product_code[$i]);
                            $this->excel->getActiveSheet()->getStyle('F' . ($row+$i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $this->excel->getActiveSheet()->SetCellValue('G' . ($row+$i), $product_name[$i]);
                            $this->excel->getActiveSheet()->SetCellValue('H' . ($row+$i), $product_variant[$i]);
                            $qty = (float)$quantity[$i];
                            $dec = $this->Settings->qty_decimals;
                            $format = '#,##0' . ($dec > 0 ? '.' . str_repeat('0', $dec) : '');

                            $this->excel->getActiveSheet()->setCellValueExplicit(
                                'I'.($row+$i),
                                $qty,
                                PHPExcel_Cell_DataType::TYPE_NUMERIC
                            );

                            $this->excel->getActiveSheet()
                                ->getStyle('I'.($row+$i))
                                ->getNumberFormat()
                                ->setFormatCode($format);

                            $this->excel->getActiveSheet()
                                ->getStyle('I'.($row+$i))
                                ->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $this->excel->getActiveSheet()->SetCellValue('J' . ($row+$i), $this->sma->formatMoney($unit_cost[$i]));
                            $this->excel->getActiveSheet()->getStyle('J' . ($row+$i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);                                
                            $this->excel->getActiveSheet()->SetCellValue('K' . ($row+$i), $this->sma->formatMoney($tax[$i]));
                            $this->excel->getActiveSheet()
                                ->getStyle('K' . ($row + $i))
                                ->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $this->excel->getActiveSheet()->SetCellValue('L' . ($row+$i), $this->sma->formatMoney($subtotal[$i]));
                            $this->excel->getActiveSheet()->getStyle('L' . ($row+$i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $this->excel->getActiveSheet()->SetCellValue('M' . ($row+$i), $this->sma->formatMoney($total[$i]));
                            $this->excel->getActiveSheet()->getStyle('M' . ($row+$i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $this->excel->getActiveSheet()->SetCellValue('N' . ($row+$i), $tansfer->status);
                            $this->excel->getActiveSheet()->SetCellValue('O' . ($row+$i), $user[0]->username);
                            
                            $footer_qty += $quantity[$i];
                            $footer_unit_cost += $unit_cost[$i];
                            $footer_tax += $tax[$i];          // total item tax
                            $footer_subtotal += $subtotal[$i];
                            $footer_total += $total[$i];
                        }
                        $row = $row+($size);
                    }
                    // Footer row - immediately after table data
                    $footer_row = $row;

                    $this->excel->getActiveSheet()->SetCellValue('F' . $footer_row, "TOTAL");
                    // Write RAW numeric value (not formatted string)
                    $footerQty = (float) $footer_qty;

                    // Excel: store numeric value
                    $this->excel->getActiveSheet()->setCellValueExplicit(
                        'I' . $footer_row,
                        $footerQty,
                        PHPExcel_Cell_DataType::TYPE_NUMERIC
                    );

                    // Excel number formatting using system qty decimals
                    $dec = $this->Settings->qty_decimals;
                    $format = '#,##0' . ($dec > 0 ? '.' . str_repeat('0', $dec) : '');

                    $this->excel->getActiveSheet()
                        ->getStyle('I' . $footer_row)
                        ->getNumberFormat()
                        ->setFormatCode($format);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $footer_row, $this->sma->formatMoney($footer_unit_cost));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $footer_row, $this->sma->formatMoney($footer_tax));
                    $this->excel->getActiveSheet()->SetCellValue('L' . $footer_row, $this->sma->formatMoney($footer_subtotal));
                    $this->excel->getActiveSheet()->SetCellValue('M' . $footer_row, $this->sma->formatMoney($footer_total));
                    $this->excel->getActiveSheet()->mergeCells('N' . $footer_row . ':O' . $footer_row);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $footer_row, '');
                    $this->excel->getActiveSheet()
                    ->getStyle('L' . $footer_row)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $this->excel->getActiveSheet()
                    ->getStyle('M' . $footer_row)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    
                $this->excel->getActiveSheet()
                ->getStyle('J' . $footer_row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $this->excel->getActiveSheet()
                ->getStyle('K' . $footer_row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                $this->excel->getActiveSheet()
                ->getStyle('I' . $footer_row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                
                    // Make footer bold
                    $this->excel->getActiveSheet()->getStyle("F{$footer_row}:O{$footer_row}")
                        ->getFont()->setBold(true);
                    
                    // Add borders to all data including headers & footer
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000')
                            )
                        )
                    );
                    $this->excel->getActiveSheet()->getStyle("A2:O{$footer_row}")->applyFromArray($styleArray);
                    
                    // Make headers bold and add background color
                    $headerStyle = array(
                        'font'  => array(
                            'bold'  => true,
                            'color' => array('rgb' => 'FFFFFF'),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '4472C4')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    );
                    $this->excel->getActiveSheet()->getStyle("A2:O2")->applyFromArray($headerStyle);

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'transfers_' . date('Y_m_d_H_i_s');
                   
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
                }
            } else {
                $this->session->set_flashdata('error', lang("no_transfer_selected"));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
        }
    }
    // End Request
    function view_report($transfer_id = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        if (!$transfer) {
            $this->session->set_flashdata('error', lang('no_transfer_selected'));
            redirect('transfers');
        }
        
        if (!$this->session->userdata('view_right')) {

            $this->sma->view_rights($transfer->created_by, true);
        }
        $this->data['rows'] = $this->transfers_model->getAllTransferReportItems($transfer_id, $transfer->status);
        $fromWarehouse = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $toWarehouse = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['from_warehouse'] = $fromWarehouse[$transfer->from_warehouse_id];
        $this->data['to_warehouse'] = $toWarehouse[$transfer->to_warehouse_id];
        $this->data['transfer'] = $transfer;
        $this->data['tid'] = $transfer_id;
        $this->data['created_by'] = $this->site->getUser($transfer->created_by);
        $this->load->view($this->theme . 'reports/transfer_report_model', $this->data);
    }
     //////////////////////////////////////// Job Work Transfer Request /////////////////////////////////////////
    /**
     * Transfer RM screen
     * Renders the listing view that shows only transfer requests linked to purchases.
     * The datatable data is provided by getTransfersRequestsRM().
     */
    public function transfer_rm() {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'Transfer RM')
        );
        $meta = array('page_title' => 'Transfer RM', 'bc' => $bc);
        $this->page_construct('transfers/transfer_rm', $meta, $this->data);
    }
    /**
     * Datatable source for Transfer RM
     * Returns transfer_request rows where purchase_id is set (not null and not zero).
     */
    public function getTransfersRequestsRM()
    {
        $this->sma->checkPermissions('request');

        $detail_link = anchor('transfers/view_request/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('transfers/jw_edit_transfer/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . lang("delete_product_request") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . site_url('transfers/delete_request/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete_Request') . "</a>";
        $cancel_link = "<a href='#' class='tip po' title='<b>" . lang("cancel_product_request") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger ' id='a__$1' href='" . site_url('transfers/calcel_request/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-remove\"></i> "
            . lang('Cancel_Request') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>         
            <li>' . $cancel_link . '</li>         
            <li>' . $delete_link . '</li>         
        </ul>
     </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select("transfer_request.id AS rid, transfer_request.date, purchases.reference_no as pro_ref, transfer_request.from_warehouse_name as fname, transfer_request.from_warehouse_code as fcode, companies.name as supplier_name, companies.company as supplier_company, transfer_request.status, transfer_request.attachment")
            ->from('transfer_request')
            ->join('purchases', 'purchases.id = transfer_request.purchase_id', 'left')
            ->join('companies', 'companies.id = purchases.supplier_id', 'left')
            ->join('transfer_request_items as tri', 'tri.transfer_request_id = transfer_request.id', 'left')
            ->join('products as p', 'p.id = tri.product_id', 'left')
            ->edit_column("fname", "$1 ($2)", "fname, fcode")
            ->edit_column("supplier_name", "$1 ($2)", "supplier_name, supplier_company");

        // RM filter: only show requests linked to a purchase
        $this->datatables->where(' transfer_request.purchase_id IS NOT NULL');
        $this->datatables->where('transfer_request.purchase_id !=', 0);
        $this->datatables->where('p.type =', 'raw');

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('transfer_request.created_by', $this->session->userdata('user_id'));
        }
        if($this->session->userdata('view_right')){
          if($this->input->get('warehouse')){
             $getwarehouse = str_replace("_",",",$this->input->get('warehouse')); 
             $this->datatables->where('transfer_request.to_warehouse_id IN ('.$getwarehouse.')');
             $this->datatables->or_where('transfer_request.from_warehouse_id IN ('.$getwarehouse.')');
          }
       }

        $this->datatables->add_column("Actions", $action, "rid")
            ->unset_column('fcode')
            ->unset_column('supplier_company');
         echo $this->datatables->generate();
    }
    //////////////////////////////////////// End Job Work Transfer Request /////////////////////////////////////////
}
