<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales extends MY_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->lang->load('sales', $this->Settings->user_language);
        $this->load->helper('text');
        $this->load->helper('download_helper');
        $this->load->library('form_validation');
        $this->load->model('sales_model');
        $this->load->model('orders_model');
        $this->load->model('products_model');  
        $this->load->model('reports_model');
        $this->load->model('pos_model');
        $this->load->model('challan_model');
        
        $this->digital_upload_path = 'files/'.$this->Customer_assets;
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : null;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->data['pos_settings']->pos_theme = json_decode($this->pos_settings->pos_theme);

        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null) {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id')) : NULL;
            $this->data['warehouse_id'] = $warehouse_id == null ? $this->session->userdata('warehouse_id') : $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id'));
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('sales/index', $meta, $this->data);
    }

    public function getSales($warehouse_id = null) {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link1 = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link1 . '</li>
            <li>' . $detail_link . '</li>
            <li class="link_$2 link_duplicate_$2">' . $duplicate_link . '</li>
            <li class="link_$2 link_payment_$3">' . $payments_link . '</li>
            <li class="link_$2 link_add_payment_$2 link_add_payment_$3" >' . $add_payment_link . '</li>
            <li class="link_$2 link_add_delivery_$2" >' . $add_delivery_link . '</li>
            <li class="link_edit_$2">' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li class="link_$2 link_return_$2">' . $return_link . '</li>
            <li class="link_$2 link_delete_$2">' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        $arrWr = [];
        if ($warehouse_id) {

            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, invoice_no, biller, customer, sale_status, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales');

            $arrWr = explode(',', $warehouse_id);

            $this->datatables->where_in('warehouse_id', $arrWr);
        } else {
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, invoice_no, biller, customer, sale_status, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales');
        }
        $this->datatables->where('pos =', 0); //->or_where('sale_status =', 'returned');
        $this->datatables->where('eshop_sale =', 0); //  skip eshop_sale
        $this->datatables->where('offline_sale =', 0); //  skip offline_sale
        $this->datatables->where('up_sales =', 0); //  skip offline_sale
        $this->datatables->where('sale_status !=', 'deleted'); //  skip offline_sale

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        
        
        $this->datatables->add_column("Actions", $action, "id,sale_status,payment_status");

        echo $this->datatables->generate();
    }

    public function getWarehouseByUserId() {
        $user_value = $this->input->get('user_value') ? $this->input->get('user_value') : NULL;
        $user = $this->site->getUser($user_value);
        $Explode = explode(',', $user->warehouse_id);
        $ArrWarehouse = array();
        foreach ($Explode as $key) {
            $ResultWarehouse = $this->site->getWarehouseByID($key);
            $ArrWarehouse[] = array(
                $key => $ResultWarehouse->name,
            );
        }
        echo json_encode($ArrWarehouse);
    }

    public function all_sale_lists() {
        $this->sma->checkPermissions('index', null);

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id')) : NULL;

            $this->data['warehouse_id'] = $warehouse_id == null ? $this->session->userdata('warehouse_id') : $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id'));
        }

        $WarehouseView = 0;
        if ($this->session->userdata('group_id') == 1)
            $WarehouseView = 1;
        elseif ($this->session->userdata('group_id') == 2)
            $WarehouseView = 1;

        if ($WarehouseView == 0) {
            $this->data['user_id'] = $user_id = $this->session->userdata('user_id');

            $user = $this->site->getUser($user_id);

            $this->data['billers'][] = $this->site->getCompanyByID($user->biller_id);
            $this->data['users'] = $this->reports_model->getStaffById($user_id);
        } else {

            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['users'] = $this->reports_model->getStaff();
        }

        // exit;


        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('sales/all_sale_listing', $meta, $this->data);
    }

    public function all_sale_lists_filter($pdf = NULL, $xls = NULL) {
        // $this->sma->checkPermissions('index');
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;
        $TypeOfModeSale = $this->input->get('TypeOfModeSale') ? $this->input->get('TypeOfModeSale') : NULL;
        $sale_status = $this->input->get('sale_status') ? $this->input->get('sale_status') : NULL;
        if ($sale_status && !is_array($sale_status)) {
            $sale_status = array($sale_status);
        }


        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && $user == NULL && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($this->session->userdata('warehouse_id')) {
            $warehouse_user = $this->session->userdata('warehouse_id');
            //echo $warehouse_user;
        }
        if ($pdf || $xls) {
            $this->load->library('datatables');
            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, sale_status, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales')->join($si, 'FSI.sale_id=sales.id', 'left');
            if ($TypeOfModeSale) {
                if ($TypeOfModeSale == 'Sale') {
                    $this->datatables->where('sales.eshop_sale =', 0);
                    $this->datatables->where('sales.offline_sale =', 0);
                    $this->datatables->where('sales.pos =', 0);
                    $this->datatables->where('sales.up_sales =', 0);
                }
                if ($TypeOfModeSale == 'EShop')
                    $this->datatables->where('sales.eshop_sale =', 1);
                if ($TypeOfModeSale == 'OfflineSale')
                    $this->datatables->where('sales.offline_sale =', 1);
                if ($TypeOfModeSale == 'POSSale')
                    $this->datatables->where('sales.pos =', 1);
                if ($TypeOfModeSale == 'UrbanPipperSale')
                    $this->datatables->where('sales.up_sales =', 1);
            }
            if ($this->session->userdata('view_right') == '0') {
                if ($user) {
                    $this->datatables->where('sales.created_by', $user);
                }
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }

            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            /* 23-7 */
            if ($warehouse) {
                $getwarehouse = str_replace("_", ",", $warehouse);
                $this->datatables->where('sales.warehouse_id IN (' . $getwarehouse . ')');
            } else {

                if (!$this->Owner && !$this->Admin) {
                    $arrWr = [];
                    $arr = explode(',', $warehouse_user);
                    foreach ($arr as $warehouse_id) {
                        $arrWr[] = $warehouse_id;
                    }
                    //$impwr = implode("','",$arrWr);
                    $this->db->where_in('sales.warehouse_id', $arr);
                }
            }
            /* 23-7 */
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where('DATE(' . $this->db->dbprefix('sales') . '.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($sale_status) {
                if (is_array($sale_status)) {
                    $this->datatables->where_in('sales.sale_status', $sale_status);
                } else {
                    $this->datatables->where('sales.sale_status', $sale_status);
                }
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Sale_Status'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->sale_status);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->payment_status));
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row . ":H" . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $filename = 'sales_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {

                    $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' . PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(TRUE);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
            $this->load->library('datatables');
            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, invoice_no, biller, customer, sale_status, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, attachment, return_id, if(pos=1, 'POS', if(offline_sale=1, 'Offline', if(eshop_sale=1, 'Eshop', if(up_sales=1, 'up_sales', 'Sale')))) as sale_type")
                    ->from('sales')->join($si, 'FSI.sale_id=sales.id', 'left');
            if ($TypeOfModeSale) {
                if ($TypeOfModeSale == 'Sale') {
                    $this->datatables->where('sales.eshop_sale =', 0);
                    $this->datatables->where('sales.offline_sale =', 0);
                    $this->datatables->where('sales.pos =', 0);
                    $this->datatables->where('sales.up_sales =', 0);
                }
                if ($TypeOfModeSale == 'EShop')
                    $this->datatables->where('sales.eshop_sale =', 1);
                if ($TypeOfModeSale == 'OfflineSale')
                    $this->datatables->where('sales.offline_sale =', 1);
                if ($TypeOfModeSale == 'POSSale')
                    $this->datatables->where('sales.pos =', 1);
                if ($TypeOfModeSale == 'UrbanPipperSale')
                    $this->datatables->where('sales.up_sales =', 1);
            }

            if (!$this->Owner && !$this->Admin && $user && !$this->session->userdata('view_right')) {

                $this->datatables->where('sales.created_by', $user);
            } else if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }

            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }

            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            /* if($warehouse)
              {
              $getwarehouse = str_replace("_",",", $warehouse);
              $this->datatables->where('sales.warehouse_id IN ('.$getwarehouse.')');
              } */

            /* 23-7 */
            if ($warehouse) {
                $getwarehouse = str_replace("_", ",", $warehouse);
                $this->datatables->where('sales.warehouse_id IN (' . $getwarehouse . ')');
            } else {
                if (!$this->Owner && !$this->Admin) {
                    $arrWr = [];
                    $arr = explode(',', $warehouse_user);
                    foreach ($arr as $warehouse_id) {
                        $arrWr[] = $warehouse_id;
                    }
                    //$impwr = implode("','",$arrWr);
                    $this->db->where_in('sales.warehouse_id', $arr);
                }
            }

            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where('DATE(' . $this->db->dbprefix('sales') . '.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($sale_status) {
                if (is_array($sale_status)) {
                    $this->datatables->where_in('sales.sale_status', $sale_status);
                } else {
                    $this->datatables->where('sales.sale_status', $sale_status);
                }
            }

            $detail_link1 = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
            $detail_link2 = anchor('sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-target="#myModal"');
            $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
            $duplicate_link = anchor('sales/add?sale_id=$1&sale_type=all_sale', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
            $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
            $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
            $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
            $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
            $edit_link = anchor('sales/edit/$1/all_sale', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
            $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
            $return_link = anchor('sales/return_sale/$1/all_sale', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
            $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
                    . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1/all_sale') . "'>"
                    . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                    . lang('delete_sale') . "</a>";
            $action = '<div class="text-center"><div class="btn-group text-left">'
                    . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                    . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                    <li>' . $detail_link1 . '</li>
                    <li class="SaleDetailModel SaleDetailModel_$3">' . $detail_link2 . '</li>
                    <li>' . $detail_link . '</li>
                    <li class="duplicate_$3">' . $duplicate_link . '</li>
                    <li class="view_payments_$3">' . $payments_link . '</li>
                    <li class="add_payment_$3">' . $add_payment_link . '</li>
                    <li class="add_delivery_$3">' . $add_delivery_link . '</li>
                    <li class="edit_$3">' . $edit_link . '</li>
                    <li class="download_$3">' . $pdf_link . '</li>
                    <li class="email_$3">' . $email_link . '</li>
                    <li class="return_$3">' . $return_link . '</li>
                    <li class="delete_$3">' . $delete_link . '</li>
            </ul>
        </div></div>';

            $this->datatables->add_column("Actions", $action, "id,sale_status,sale_type");
            echo $this->datatables->generate();
        }
    }

    public function modal_view($id = null) {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $exp_product = explode('_', $id);
        if (isset($exp_product[1])) {
            $this->data['products_id'] = $exp_product[1];
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view && !empty($inv->return_id)):
            $inv->rows_tax = $this->sales_model->getAllTaxItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($id, $inv->return_id);
        $this->data['salestax'] = $this->sales_model->getSalesItemsTaxes($id); ///my code
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        foreach ($this->data['rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        //echo '<pre>';
        //$this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;

        $return_sales = $inv->return_id ? $this->sales_model->getAllReturnInvoiceByID($id) : NULL;
        //print_r($return_sales);
        //echo '<br>';
        $product_discount = 0;
        $product_tax = 0;
        $total = 0;
        $grand_total = 0;
        $order_discount = 0;
        $order_tax = 0;
        $paid = 0;
        $rounding = 0;
        if (!empty($return_sales)) {
            foreach ($return_sales as $Keys => $Vals) {
                $product_discount = $product_discount + $Vals['product_discount'];
                $product_tax = $product_tax + $Vals['product_tax'];
                $total = $total + $Vals['total'];
                $rounding = $rounding + $Vals['rounding'];
                $grand_total = $grand_total + $Vals['grand_total'];
                $order_discount = $order_discount + $Vals['order_discount'];
                $order_tax = $order_tax + $Vals['order_tax'];
                $paid = $paid + $Vals['paid'];
                //echo '<br/>';
            }
            $this->data['return_sale'] = (object) array(
                        'product_discount' => $product_discount,
                        'product_tax' => $product_tax,
                        'total' => $total,
                        'rounding' => $rounding,
                        'grand_total' => $grand_total,
                        'order_tax' => $order_tax,
                        'order_discount' => $order_discount,
                        'paid' => $paid,
            );
        }
        //print_r($this->data['return_sale']); exit;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllReturnInvoiceItems($id) : NULL;
        foreach ($this->data['return_rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        $Settings = $this->site->get_setting();
        if (isset($Settings->pos_type) && $Settings->pos_type == 'pharma') {
            $this->load->view($this->theme . 'sales/modal_view_pharma', $this->data);
        } elseif (isset($this->data['products_id'])) {

            $this->load->view($this->theme . 'sales/modal_view_products', $this->data);
        } else {
            $this->load->view($this->theme . 'sales/modal_view', $this->data);
        }
        
        // Prepare print data for Android devices - AFTER view is loaded
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->load->model('pos_model');
        
        $print = array();
        $print['print_option'] = $this->data['default_printer'];
        $print['rows'] = $this->data['rows'];
        $print['biller'] = $this->data['biller'];
        $print['customer'] = $this->data['customer'];
        $print['payments'] = $this->data['payments'];
        $print['pos'] = $this->pos_model->getSetting();
        unset($print['pos']->pos_theme);
        $print['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $print['return_sale'] = !empty($this->data['return_sale']) ? $this->data['return_sale'] : null;
        $print['return_rows'] = $this->data['return_rows'];
        $print['inv'] = $inv;
        $print['warehouse'] = $this->data['warehouse'];
        $print['sid'] = $id;
        $print['modal'] = true;
        $print['page_title'] = 'Invoice';
        $print['taxItems'] = $this->data['taxItems'];
        $print['salestax'] = $this->data['salestax'];
        
        // Set product images
        if (!empty($print['rows'])) {
            foreach ($print['rows'] as $key => $row) {
                $product = $this->pos_model->getProductByID($row->product_id, $select = 'image');
                $print['rows'][$key]->image = $product->image;
            }
        }
        
        $print['brcode'] = $this->sma->save_barcode($inv->reference_no, 'code128', 66, false);
        $print['qrcode'] = $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2);
        $arr = explode("'", $print['brcode']);
        $print['brcode'] = $arr[1];
        $qrr = explode("'", $print['qrcode']);
        $print['qrcode'] = $qrr[1];
        
        foreach ($print['rows'] as $key => $row) {
            foreach ($row as $key2 => $value) {
                if ($key2 == 'quantity') {
                    $print['rows'][$key]->quantity = round($value, 2);
                }
                if ($key2 == 'unit_quantity') {
                    $print['rows'][$key]->quantity = round($value, 2);
                }
                if ($key2 == 'product_id') {
                    $product = $this->pos_model->getProductByID($value, $select = 'image');
                    $print['rows'][$key]->cf1 = $product->image;
                }
            }
        }
        
        // Send print data to Android handler
        if ($id != $_SESSION['print'] && (isset($_SESSION['print_type']) && $_SESSION['print_type'] == null)) {
            $row_taxes_print = $inv->rows_tax;
            unset($inv->rows_tax);
            $row_taxes_print_arr = array();
            if (count($row_taxes_print)) {
                foreach ($row_taxes_print as $_key => $_data) {
                    foreach ($_data as $_key1 => $value1) {
                        $row_taxes_print_arr[] = $value1;
                    }
                }
            }
            $inv->rows_tax = $row_taxes_print_arr;
            ?>
        <script>
        window.MyHandler.setPrintRequest('<?php echo json_encode($print); ?>');
        </script>
        <?php
            unset($print);
        }
        $_SESSION['print'] = $id;
    }

    public function view($id = null) {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        if ($inv->eshop_sale == 1):
            $this->load->model('eshop_model');
            $this->data['eshop_order'] = $this->eshop_model->getOrderDetails(array('sale_id' => $inv->id));
        endif;

        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);

        $this->data['created_by'] = $this->site->getUser($inv->created_by);

        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        foreach ($this->data['rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        //$this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
        // $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
        $return_sales = $inv->return_id ? $this->sales_model->getAllReturnInvoiceByID($id) : NULL;
        //print_r($return_sales);
        //echo '<br>';
        $product_discount = 0;
        $product_tax = 0;
        $total = 0;
        $grand_total = 0;
        $order_discount = 0;
        $order_tax = 0;
        $paid = 0;
        if (!empty($return_sales)) {
            foreach ($return_sales as $Keys => $Vals) {
                $product_discount = $product_discount + $Vals['product_discount'];
                $product_tax = $product_tax + $Vals['product_tax'];
                $total = $total + $Vals['total'];
                $grand_total = $grand_total + $Vals['grand_total'];
                $order_discount = $order_discount + $Vals['order_discount'];
                $order_tax = $order_tax + $Vals['order_tax'];
                $paid = $paid + $Vals['paid'];
                //echo '<br/>';
            }
            $this->data['return_sale'] = (object) array(
                        'product_discount' => $product_discount,
                        'product_tax' => $product_tax,
                        'total' => $total,
                        'grand_total' => $grand_total,
                        'order_tax' => $order_tax,
                        'product_discount' => $product_discount,
            );
        }
        //print_r($this->data['return_sale']); exit;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllReturnInvoiceItems($id) : NULL;
        foreach ($this->data['return_rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }


        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->sales_model->getAllTaxItems($id, $inv->return_id);
        endif;
        //$this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($id, $inv->return_id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $Settings = $this->site->get_setting();
        if (isset($Settings->pos_type) && $Settings->pos_type == 'pharma') {
            $this->page_construct('sales/view-sales-pharma', $meta, $this->data);
        } else {
            $this->page_construct('sales/view', $meta, $this->data);
        }
    }

    public function pdf($id = null, $view = null, $save_bufffer = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->sales_model->getAllTaxItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($id, $inv->return_id);

        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        foreach ($this->data['rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        //$this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
        //$this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
        $return_sales = $inv->return_id ? $this->sales_model->getAllReturnInvoiceByID($id) : NULL;
        //print_r($return_sales);
        //echo '<br>';
        $product_discount = 0;
        $product_tax = 0;
        $total = 0;
        $grand_total = 0;
        $order_discount = 0;
        $order_tax = 0;
        $paid = 0;
        if (!empty($return_sales)) {
            foreach ($return_sales as $Keys => $Vals) {
                $product_discount = $product_discount + $Vals['product_discount'];
                $product_tax = $product_tax + $Vals['product_tax'];
                $total = $total + $Vals['total'];
                $grand_total = $grand_total + $Vals['grand_total'];
                $order_discount = $order_discount + $Vals['order_discount'];
                $order_tax = $order_tax + $Vals['order_tax'];
                $paid = $paid + $Vals['paid'];
                //echo '<br/>';
            }
            $this->data['return_sale'] = (object) array(
                        'product_discount' => $product_discount,
                        'product_tax' => $product_tax,
                        'total' => $total,
                        'grand_total' => $grand_total,
                        'order_tax' => $order_tax,
                        'product_discount' => $product_discount,
            );
        }
        //print_r($this->data['return_sale']); exit;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllReturnInvoiceItems($id) : NULL;
        foreach ($this->data['return_rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        if ($inv->eshop_sale) {
            $this->data['shipping_details'] = $this->pos_model->getShipingDetails($inv->order_no);
        }

        $receipt_addresses = $this->sales_model->getInvoiceReceiptAddresses($inv, $this->data['customer']);
        if ($receipt_addresses) {
            $this->data = array_merge($this->data, $receipt_addresses);
        }

        $name = lang("sale") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        //$html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        $html = $this->load->view($this->theme . 'sales/pdf_reciept', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }


        if ($view) {
            // $this->load->view($this->theme . 'sales/pdf', $this->data);
            $this->load->view($this->theme . 'sales/pdf_reciept', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer); //, $this->data['biller']->invoice_footer
        } else {
            $this->sma->generate_pdf($html, $name, false);
        } /* echo */
    }

    public function combine_pdf($sales_id) {
        $this->sma->checkPermissions('pdf');

        foreach ($sales_id as $id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            foreach ($this->data['rows'] as $row) {
                if (!empty($row->shade_id)) {
                    $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                    $row->shade_name= $colors->name;
                }
            }
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
            $html_data = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
                'content' => $html_data,
                'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("sales") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }

    public function combine_challan_pdf($challan_ids) {
        $this->sma->checkPermissions('pdf');

        foreach ($challan_ids as $id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->challan_model->getChallanByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->challan_model->getChallanPayments($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->challan_model->getAllChallanItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->challan_model->getChallanByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->challan_model->getAllChallanItems($inv->return_id) : NULL;

            $receipt_addresses = $this->challan_model->getChallanReceiptAddresses($inv, $this->data['customer']);
            if ($receipt_addresses) {
                $this->data = array_merge($this->data, $receipt_addresses);
            }

            $html_data = $this->load->view($this->theme . 'sales/pdf_challan', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }
            $html[] = array('content' => $html_data, 'footer' => $this->data['biller']->invoice_footer);
        }

        $name = lang("Challans") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }

    public function combine_invoice_pdf($sales_id) {
        $this->sma->checkPermissions('pdf');

        foreach ($sales_id as $id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->sma->checkPermissions('index');

            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            if ($inv->eshop_sale == 1):
                $this->load->model('eshop_model');
                $this->data['eshop_order'] = $this->eshop_model->getOrderDetails(array('sale_id' => $inv->id));

            endif;

            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);

            $this->data['created_by'] = $this->site->getUser($inv->created_by);

            $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            foreach ($this->data['rows'] as $row) {
                if (!empty($row->shade_id)) {
                    $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                    $row->shade_name= $colors->name;
                }
            }
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;


            $_PID = $this->Settings->default_printer;
            $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
            if ($this->data['default_printer']->tax_classification_view):
                $inv->rows_tax = $this->sales_model->getAllTaxItems($id, $inv->return_id);
            endif;
            $this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($id, $inv->return_id);

            $receipt_addresses = $this->sales_model->getInvoiceReceiptAddresses($inv, $this->data['customer']);
            if ($receipt_addresses) {
                $this->data = array_merge($this->data, $receipt_addresses);
            }

            $html_data = $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
                'content' => $html_data,
                'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("sales") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }

    public function email($id = null) {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        //$this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $paypal = $this->sales_model->getPaypalSettings();
            $skrill = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == "1" && $inv->grand_total != "0.00") {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . site_url('sales/view/' . $inv->id) . '&cancel_return=' . site_url('sales/view/' . $inv->id) . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            }
            if ($skrill->active == "1" && $inv->grand_total != "0.00") {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('sales/view/' . $inv->id) . '&cancel_url=' . site_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div>
    </div>';
            $message = $message . $btn_code;

            $attachment = $this->pdf($id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->session->set_flashdata('message', lang("email_sent_msg"));
            redirect($_SERVER["HTTP_REFERER"]);
            // redirect("sales");
        } else {

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/views/email_templates/sale.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $sale_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    public function add($quote_id = null) {

        $this->sma->checkPermissions();

        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : NULL;
        $chalan_id = $this->input->get('chalan_id') ? $this->input->get('chalan_id') : NULL;
        $order_id = $this->input->get('order_id') ? $this->input->get('order_id') : NULL;
        $sale_type = $this->input->get('sale_type') ? $this->input->get('sale_type') : NULL;
        
        // Determine sale_action early for validation rules
        $sale_action = 'sales'; // default
        
        if ($chalan_id) {
            $sale_action = 'chalan';
        } elseif ($this->input->get('sale_action')) {
            $sale_action = $this->input->get('sale_action');
        }

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $field_name = ($sale_action == 'chalan') ? 'challan_status' : 'sale_status';
        $field_label = ($sale_action == 'chalan') ? "challan_status" : "sale_status";
        $this->form_validation->set_rules($field_name, $field_label, 'required');
        $this->form_validation->set_rules('sale_action', lang("sale_action"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        $Settings = $this->site->get_setting();
        if (isset($Settings->pos_type) && $Settings->pos_type == 'pharma') {
            // $this->form_validation->set_rules('patient_name',  'Patient Name', 'trim|required');
            // $this->form_validation->set_rules('doctor_name', 'Doctor Name' , 'trim|required');
        }
         
        if ($this->form_validation->run() == true) {

            // Override with POST data if available (for form submissions)
        if ($this->input->post('sale_action')) {
            $sale_action = $this->input->post('sale_action');
        }
            $_ssot_mode = isset($this->pos_settings->sale_source_order_type_mode) ? (int) $this->pos_settings->sale_source_order_type_mode : 1;
            if ($_ssot_mode === 2 && trim((string) $this->input->post('order_type')) === '') {
                $this->session->set_flashdata('error', lang('sale_source_order_type_required'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales/add');
            }
            $refKey = $sale_action == 'chalan' ? 'ordr' : 'so';

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference($refKey);

            if ($this->Owner || $this->Admin || $this->GP['sales-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
                if (strlen($date) == 16) {
                    $date .= ':' . date('s');
                }
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id       = $this->input->post('warehouse');
            $customer_id        = $this->input->post('customer');
            $biller_id          = $this->input->post('biller');
            $total_items        = $this->input->post('total_items');
            $sale_status        = ($sale_action == 'chalan') ? $this->input->post('challan_status') : $this->input->post('sale_status');
            $payment_status     = $this->input->post('payment_status');
            $payment_term       = $this->input->post('payment_term');
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note               = $this->sma->clear_tags($this->input->post('note'));
            $staff_note         = $this->sma->clear_tags($this->input->post('staff_note'));
            $quote_id           = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            $syncQuantity       = $this->input->post('syncQuantity');
            $order_id           = $this->input->post('order_id') ? $this->input->post('order_id') : null;
            $sale_type_input    = $this->input->post('sale_type') ? $this->input->post('sale_type') : '';

            // check billing address state code from address table if not then check customer state code from company table
            $customer_state_code = '';
            $selected_billing_address_id = $this->input->post('billing_address_id') ? (int) $this->input->post('billing_address_id') : 0;
            if ($selected_billing_address_id > 0) {
                $selected_billing_address = $this->db->get_where('addresses', array(
                    'id' => $selected_billing_address_id,
                    'company_id' => $customer_id,
                ), 1)->row();
                if (!empty($selected_billing_address) && !empty($selected_billing_address->state_code)) {
                    $customer_state_code = trim($selected_billing_address->state_code);
                }
            }
            if ($customer_state_code === '') {
                $default_billing_address = $this->db->get_where('addresses', array(
                    'company_id' => $customer_id,
                    'type' => 'Billing',
                    'is_default' => 1,
                ), 1)->row();
                if (!empty($default_billing_address) && !empty($default_billing_address->state_code)) {
                    $customer_state_code = trim($default_billing_address->state_code);
                } else {
                    $customer_state_code = !empty($customer_details->state_code) ? trim($customer_details->state_code) : '';
                }
            }

            if ((!empty($customer_state_code) && !empty($biller_details->state_code)) && $customer_state_code != trim($biller_details->state_code)) {
                $interStateTax = true;
            } else {
                $interStateTax = false;
            }

            $total              = 0;
            $product_tax        = 0;
            $order_tax          = 0;
            $product_discount   = 0;
            $order_discount     = 0;
            $percentage         = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            $sale_cgst = $sale_sgst = $sale_igst = 0;
            $customer_pu       = $this->site->getCompanyByID($customer_id);
            $warehouse_id_pu = $customer_pu->customer_url;
            $warehouses_pu = $this->site->getWarehouseByID($warehouse_id_pu);
            if (!empty($warehouses_pu)) {
                foreach($warehouses_pu as $warehouse_pu){
                 if($biller_id == $warehouse_pu->primary_biller_id && $warehouse_pu->location_type == 1){
                    $this->session->set_flashdata('error', 'Submitted sale could not be added as source and destination locations have the same biller. You can perform this transaction through Transfers.');
                    redirect($_SERVER['HTTP_REFERER']);
                     }
                }
                
            }
            //  Sales Person
            $SalesPersonDetails = (isset($_POST['sales_person'])?$_POST['sales_person']:NULL);
            if($SalesPersonDetails){
                $ExplodeSalesPerson = explode('-', $SalesPersonDetails);
                $SellerId = $ExplodeSalesPerson[0];
                $SellerName = $ExplodeSalesPerson[1];
            }
            // End Sales Person

            for ($r = 0; $r < $i; $r++) {
                if ($_POST['product_type'][$r] == 'manual') {
                    $productfiled = [
                        'code' => $_POST['product_code'][$r],
                        'name' => $_POST['product_name'][$r],
                        'cost' => $_POST['unit_price'][$r],
                        'price' => $_POST['real_unit_price'][$r],
                        'mrp' => $_POST['mrp'][$r],
                        'type' => 'standard',
                        'tax_rate' => $_POST['product_tax'][$r]
                    ];
                    $item_id = $this->sales_model->addproductManual($productfiled);
                    $item_type = 'standard';
                } else {
                    $item_id = $_POST['product_id'][$r];
                    $item_type = $_POST['product_type'][$r];
                }

                $hsn_code = $_POST['hsn_code'][$r];
                $hsn_code = ($hsn_code == 'null') ? '' : $hsn_code;

                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : 0;
                $real_unit_price    = $_POST['real_unit_price'][$r];
                $unit_price         = $item_unit_price = $_POST['unit_price'][$r];
                $net_price          = $_POST['net_price'][$r];
                $item_quantity      = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_packing_size  = isset($_POST['packing_size'][$r]) ? $_POST['packing_size'][$r] : 0;
                $item_unit          = $_POST['product_unit'][$r];
                // $item_unit_quantity = $_POST['product_base_quantity'][$r];
                $item_mrp           = $_POST['mrp'][$r];
                $item_expiry        = $_POST['cf1'][$r];
                $item_weight        = $_POST['item_weight'][$r];
                $tax_method = $_POST['tax_method'][$r];
                 $category_id = $_POST['cat_id'][$r]; ////////////// Multiple Category////////////////
                //$item_unit_quantity = $item_quantity;
                //$item_batchno = $_POST['cf2'][$r];
                if($_POST['item_storage_type'][$r] != "loose")
                {
                    $item_unit_quantity = $item_quantity;   
                }
                else{
                    $item_unit_quantity = $_POST['product_base_quantity'][$r];

                }
                $batch_number = isset($_POST['batch_number'][$r]) ? $_POST['batch_number'][$r] : null;
                if ($batch_number) {
                    $batch = explode("~", $batch_number);
                    $item_batchno = $batch[1];
                } else {
                    $item_batchno = NULL;
                }
                // product wise sales person
                $salesperson = isset($_POST['product_sales_person'][$r]) ? $_POST['product_sales_person'][$r] : null;
                if($salesperson){
                    $expsalesperson = explode('~', $salesperson);
                }
                // End Product wise sales person

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $item_mrp = !empty($item_mrp) ? $item_mrp : $product_details->mrp;
                    $item_mrp = $this->sma->formatDecimal($item_mrp);

                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            //Note : unitprice is product and variant price. Real unit price is actual product price. if we taken realunitprice then grandtotal and discount calculate wrong becuase real unit price not included variant price. so now taken unit_price.(28-03-2020)
                            $pr_discount = $this->sma->formatDecimal(( ( (Float) $unit_price * (Float) $pds[0] ) / 100), 4);
                            //$pr_discount = $this->sma->formatDecimal(( ( (Float) $real_unit_price * (Float) $pds[0] ) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount, 4);
                        }
                    }
                    $unit_discount = $pr_discount;
                    $item_unit_price_less_discount = ($unit_price - $unit_discount);
                    //$item_unit_price_less_discount = $this->sma->formatDecimal($unit_price - $unit_discount); //17/05/19

                    $item_net_price = $item_unit_price_less_discount;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity);
                    //Sum Of products discounts
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = '';
                    $unit_surcharge = 0;
                    $item_surcharge = NULL;
                   
                    $invoice_net_unit_price = 0;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        $tax = $tax_details->rate . "%";
                        if ($tax_details->rate != 0) {
                            if ($tax_details->type == 1) {
                                //Exclusive tax method calculation
                                if ($product_details && $tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / 100, 4);

                                    $net_unit_price = $item_unit_price_less_discount;
                                    $unit_price = $item_unit_price_less_discount + $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    //Inclusive tax method calculation.
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);

                                    $item_net_price = $item_unit_price_less_discount - $item_tax;

                                    $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $unit_price = $item_unit_price_less_discount;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }
                            } elseif ($tax_details->type == 2) {

                                if ($product_details && $tax_method  == 1) {
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / 100, 4);

                                    $net_unit_price = $item_unit_price_less_discount;
                                    $unit_price = $item_unit_price_less_discount + $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);

                                    $item_net_price = $item_unit_price_less_discount - $item_tax;

                                    $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $unit_price = $item_unit_price_less_discount;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }
                            }//end else.
                        } else {

                            $net_unit_price = $item_unit_price_less_discount;
                            $unit_price = $item_unit_price_less_discount;
                            $invoice_unit_price = $item_unit_price_less_discount;
                            $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                        }

                        $item_tax = $item_tax ? $item_tax : 0;
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity, 4);

                        $unit_tax = $item_tax;
                    } else {
                        $net_unit_price = $item_unit_price_less_discount;
                        $unit_price = $item_unit_price_less_discount;

                        $invoice_unit_price = $item_unit_price_less_discount;
                        $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                    }//end else

                    if (isset($tax_details)) {
                        $unit_surcharge_val = $this->sma->calcSaleItemUnitSurcharge($tax_details, $unit_tax);
                        if ($unit_surcharge_val !== NULL && (float) $unit_surcharge_val != 0) {
                            $unit_surcharge = $this->sma->formatDecimal($unit_surcharge_val, 6);
                            $item_surcharge = $this->sma->formatDecimal(($unit_surcharge * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 6);

                            if ($product_details && $tax_method == 1) {
                                // Exclusive
                                $unit_price += $unit_surcharge;
                                $invoice_net_unit_price += $unit_surcharge;
                            } else {
                                // Inclusive
                                $net_unit_price -= $unit_surcharge;
                                $item_net_price = isset($item_net_price) ? ($item_net_price - $unit_surcharge) : $net_unit_price;
                                $invoice_unit_price -= $unit_surcharge;
                            }
                        }
                    }

                    if ($interStateTax) {
                        $item_gst = $tax_details->rate;
                        $item_cgst = 0;
                        $item_sgst = 0;
                        $item_igst = $pr_item_tax;
                    } else {
                        $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                        $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_igst = 0;
                    }

                    $product_tax += $pr_item_tax;
                    if ($item_surcharge !== NULL) {
                        $product_tax += (float) $item_surcharge;
                    }
                    $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $mrp                            = $item_mrp;
                    $invoice_unit_price             = $this->sma->formatDecimal($invoice_unit_price, 4);
                    $invoice_net_unit_price         = $this->sma->formatDecimal($invoice_net_unit_price, 4);
                    $invoice_total_net_unit_price   = $this->sma->formatDecimal(($invoice_net_unit_price * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);
                    $net_unit_price                 = $this->sma->formatDecimal($net_unit_price, 4);
                    $unit_price                     = $this->sma->formatDecimal($unit_price, 4);
                    $net_price                      = $this->sma->formatDecimal(($mrp * $item_quantity), 4);
                    
                    // Apply packing_size multiplier to subtotal if packing_size > 0
                    if ($item_packing_size > 0) {
                        $subtotal = $this->sma->formatDecimal((($unit_price * $item_packing_size) * $item_quantity), 4);
                    } else {
                        $subtotal = $this->sma->formatDecimal(($unit_price * $item_quantity), 4);
                    }
                    
                    $product_option_color           = isset($_POST['product_option_color'][$r]) ? $_POST['product_option_color'][$r] : null;
                   
                    $sale_item_discount = $this->sma->getSaleItemDiscountForStorage(null, $item_discount, $item_mrp, $real_unit_price);

                    $products[] = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                          'category_id'      => $category_id, ////////////// Multiple Category////////////////
                        'article_code'      => $product_details->article_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $unit_price,
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => ($unit ? $unit->code : NULL),
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'surcharge'         => $item_surcharge,
                        'item_weight'       => $item_weight,
                        'tax_rate_id'       => $pr_tax,
                        'tax'               => $tax,
                        // 'discount'          => $item_discount,
                        'discount'          => $sale_item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $subtotal,
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                        'mrp'               => $item_mrp,
                        'hsn_code'          => $hsn_code,
                        'delivery_status'   => 'pending',
                        'pending_quantity'  => $item_quantity,
                        'delivered_quantity'=> 0,
                        'tax_method'        => $tax_method,
                        'unit_discount'     => $unit_discount,
                        'unit_tax'          => $unit_tax,
                        'invoice_unit_price'=> $invoice_unit_price,
                        'net_price'         => $net_price,
                        'invoice_net_unit_price'        => $invoice_net_unit_price,
                        'invoice_total_net_unit_price'  => $invoice_total_net_unit_price,
                        'gst_rate'          => $item_gst,
                        'cgst'              => $item_cgst,
                        'sgst'              => $item_sgst,
                        'igst'              => $item_igst,
                        'cf1'               => $item_expiry,
                        'cf1_name'          => 'Exp. Date',
                        'batch_number'      => $batch_number,
                        'shade_id'          => $product_option_color,
                        'seller_id'         => ($salesperson)?$expsalesperson[0] : (isset($SellerId) ? $SellerId : NULL),  
                        'seller'            => ($salesperson)? $expsalesperson[1] : (isset($SellerName) ? $SellerName : NULL),
                        'packing_size'      => $item_packing_size,

                    ];

                    $sale_cgst += $item_cgst;
                    $sale_sgst += $item_sgst;
                    $sale_igst += $item_igst;

                    // Apply packing_size multiplier if packing_size > 0
                    // NOTE: Do NOT include $pr_item_tax here. Tax is accumulated separately
                    // in $product_tax and added to grand_total via $total_tax.
                    // Including it here causes double-counting of tax in grand_total.
                    if ($item_packing_size > 0) {
                        $total += $this->sma->formatDecimal(($item_net_price * $item_packing_size * $item_quantity), 4);
                    } else {
                        // $total += $this->sma->formatDecimal(($unit_price * $item_quantity), 4);
                        $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4); //17/05/19
                    }

                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {                
                $sale_items = $products;
                unset($products);
                foreach ($sale_items as $key => $item) {
                    ksort($item);
                    $products[] = $item;
                }
            }

            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                /* $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                  $ods = explode("%", $order_discount_id);
                  $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                  } else {
                  $order_discount = $this->sma->formatDecimal($order_discount_id);
                  } */
            } else {
                $order_discount_id = null;
            }
            // $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
            $total_discount = $this->sma->formatDecimal($product_discount);


            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    } elseif ($order_tax_details->type == 1) {
                        //$order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping)), 4);
            $rounding = '';

            if ($this->pos_settings->rounding > 0) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = ($round_total - $grand_total);
            }
            $data = ['date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'seller_id' => (isset($SellerId)?$SellerId :NULL),
                'seller' => (isset($SellerName)?$SellerName :NULL),
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'rounding' => $rounding,
                'due_date' => $due_date,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'cgst' => $sale_cgst,
                'sgst' => $sale_sgst,
                'igst' => $sale_igst,
                'transporter_mode' => $this->input->post('transporter_mode'),
                'LR_No' => $this->input->post('LR_No'),
                'total_parcels' => $this->input->post('total_parcels'),
                'place_of_supply' => $this->input->post('place_of_supply'),
                'order_type' => $this->input->post('order_type'),
            ];
            $data['shipping_address_id'] = $this->input->post('shipping_address_id') ? $this->input->post('shipping_address_id') : NULL;
            $data['billing_address_id'] = $this->input->post('billing_address_id') ? $this->input->post('billing_address_id') : NULL;
            if ($sale_action == 'chalan') {
                $data['way_bill_no'] = $this->input->post('way_bill_no');
                $data['packing_no'] = $this->input->post('packing_no');
            }
            if ($payment_status == 'partial' || $payment_status == 'paid') {
                /* if ($this->input->post('paid_by') == 'deposit') {
                  if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                  $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                  redirect($_SERVER["HTTP_REFERER"]);
                  }
                  }
                  if ($this->input->post('paid_by') == 'gift_card') {
                  $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                  $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                  $gc_balance = $gc->balance - $amount_paying;
                  $payment = array(
                  'date' => $date,
                  'reference_no' => $this->input->post('payment_reference_no'),
                  'amount' => $this->sma->formatDecimal($amount_paying),
                  'paid_by' => $this->input->post('paid_by'),
                  'cheque_no' => $this->input->post('cheque_no'),
                  'cc_no' => $this->input->post('gift_card_no'),
                  'cc_holder' => $this->input->post('pcc_holder'),
                  'cc_month' => $this->input->post('pcc_month'),
                  'cc_year' => $this->input->post('pcc_year'),
                  'cc_type' => $this->input->post('pcc_type'),
                  'created_by' => $this->session->userdata('user_id'),
                  'note' => $this->input->post('payment_note'),
                  'transaction_id' => $this->input->post('transaction_id'),
                  'type' => 'received',
                  'gc_balance' => $gc_balance,
                  );
                  } else {
                  $payment = array(
                  'date' => $date,
                  'reference_no' => $this->input->post('payment_reference_no'),
                  'amount' => $this->sma->formatDecimal($this->input->post('amount-paid')),
                  'paid_by' => $this->input->post('paid_by'),
                  'cheque_no' => $this->input->post('cheque_no'),
                  'cc_no' => $this->input->post('pcc_no'),
                  'cc_holder' => $this->input->post('pcc_holder'),
                  'cc_month' => $this->input->post('pcc_month'),
                  'cc_year' => $this->input->post('pcc_year'),
                  'cc_type' => $this->input->post('pcc_type'),
                  'created_by' => $this->session->userdata('user_id'),
                  'note' => $this->input->post('payment_note'),
                  'transaction_id' => $this->input->post('transaction_id'),
                  'type' => 'received',
                  );
                  } */
                /**
                 * End Single Payment Logic 
                 * */
               
                $p = isset($_POST['amount-paid']) ? sizeof($_POST['amount-paid']) : 0;
                $paid = 0;
                //print_r($_POST['paid_by']);
                for ($r = 0; $r < $p; $r++) {
                    //echo $_POST['paid_by'][$r];
                    if (isset($_POST['amount-paid'][$r]) && !empty($_POST['amount-paid'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->sma->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount-paid'][$r] - $_POST['balance_amount'][$r] : $_POST['amount-paid'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } elseif ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($_POST['gift_card_no'][$r]);
                            $amount_paying = $_POST['amount-paid'][$r] >= $gc->balance ? $gc->balance : $_POST['amount-paid'][$r];
                            $gc_balance = $gc->balance - $amount_paying;
                            $payment[] = array(
                                'date' => $date,
                                'reference_no' => $_POST['payment_reference_no'][$r],
                                'amount' => $amount,
                                'paid_by' => $_POST['paid_by'][$r],
                                'cheque_no' => $_POST['cheque_no'][$r],
                                'cc_no' => $_POST['gift_card_no'][$r],
                                'cc_holder' => $_POST['cc_holder'][$r],
                                'cc_month' => $_POST['cc_month'][$r],
                                'cc_year' => $_POST['cc_year'][$r],
                                'cc_type' => $_POST['cc_type'][$r],
                                //'cc_cvv2' => $_POST['pcc_ccv'][$r],
                                'created_by' => $this->session->userdata('user_id'),
                                'type' => 'received',
                                'note' => $_POST['payment_note'][$r],
                                'pos_paid' => $_POST['amount'][$r],
                                'pos_balance' => $_POST['balance_amount'][$r],
                                'gc_balance' => $gc_balance,
                            );
                        } elseif ($_POST['paid_by'][$r] == 'credit_note') {
                            $gc = $this->site->getCreditNoteByNO($_POST['credit_card_no'][$r]);
                            $amount_paying = $_POST['amount-paid'][$r] >= $gc->balance ? $gc->balance : $_POST['amount-paid'][$r];
                            $gc_balance = $gc->balance - $amount_paying;
                            $payment[] = array(
                                'date' => $date,
                                'reference_no' => $_POST['payment_reference_no'][$r],
                                'amount' => $amount,
                                'paid_by' => $_POST['paid_by'][$r],
                                'cheque_no' => $_POST['cheque_no'][$r],
                                'cc_no' => $_POST['credit_card_no'][$r],
                                'cc_holder' => $_POST['cc_holder'][$r],
                                'cc_month' => $_POST['cc_month'][$r],
                                'cc_year' => $_POST['cc_year'][$r],
                                'cc_type' => $_POST['cc_type'][$r],
                                //'cc_cvv2' => $_POST['pcc_ccv'][$r],
                                'created_by' => $this->session->userdata('user_id'),
                                'type' => 'received',
                                'note' => $_POST['payment_note'][$r],
                                'pos_paid' => $_POST['amount'][$r],
                                'pos_balance' => $_POST['balance_amount'][$r],
                                'gc_balance' => $gc_balance,
                            );
                        } else {
                            $payment[] = array(
                                'date' => $date,
                                'reference_no' => $_POST['payment_reference_no'][$r],
                                'amount' => $amount,
                                'paid_by' => $_POST['paid_by'][$r],
                                'cheque_no' => $_POST['cheque_no'][$r],
                                'cc_no' => $_POST['cc_no'][$r],
                                'cc_holder' => $_POST['cc_holder'][$r],
                                'cc_month' => $_POST['cc_month'][$r],
                                'cc_year' => $_POST['cc_year'][$r],
                                'cc_type' => $_POST['cc_type'][$r],
                                //'cc_cvv2' => $_POST['pcc_ccv'][$r],
                                'created_by' => $this->session->userdata('user_id'),
                                'type' => 'received',
                                'note' => $_POST['payment_note'][$r],
                                'pos_paid' => $_POST['amount'][$r],
                                'pos_balance' => $_POST['balance_amount'][$r],
                                'transaction_id' => isset($_POST['transaction_id'][$r]) ? $_POST['transaction_id'][$r] : '',
                            );
                        }
                    }
                }


                /**
                 * End New Payment Logic 
                 * */
            } else {
                $payment = array();
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products, $payment);
        }

        if (isset($Settings->pos_type) && $Settings->pos_type == 'pharma') {
            $patient_name = $this->input->post('patient_name');
            $patient_name = !empty($patient_name) ? $patient_name : '-';
            if ($patient_name):
                $data['cf1'] = $patient_name;
            endif;

            $doctor_name = $this->input->post('doctor_name');
            $doctor_name = !empty($doctor_name) ? $doctor_name : '-';
            if ($doctor_name):
                $data['cf2'] = $doctor_name;
            endif;
        }
        $sale_action = (isset($sale_action) && $sale_action != '') ? $sale_action : 'sales';
        $syncQuantity = (isset($syncQuantity) && $syncQuantity != '') ? $syncQuantity : 'sales';
        $extrasPara = array('sale_action' => $sale_action, 'syncQuantity' => $syncQuantity, 'order_id' => $order_id);
                       
        if ($this->form_validation->run() == true && $sale_id = $this->sales_model->addSale($data, $products, $payment, array(), $extrasPara)) {
            $customer       = $this->site->getCompanyByID($customer_id);
            $warehouse_id = $customer->customer_url;
            $warehouses = $this->site->getWarehouseByID($warehouse_id);
            if (!empty($warehouses) && isset($warehouses[$warehouse_id])) {
                $warehouse = $warehouses[$warehouse_id];
                if($biller_id !== $warehouse->primary_biller_id && $warehouse->location_type != 1){
                    $this->sma->CreatePurchase($sale_id, $warehouse_id, true);
                }else{
                    $this->session->set_flashdata('error', 'Submitted sale could not be added as source and destination locations have the same biller. You can perform this transaction through Transfers.');
                }
                
            }
            
            if($this->Settings->send_sales_excel){            
                $_SESSION['Send_Excel'] = 1;
                $_SESSION['sale_id'] = $sale_id;
            } 


            if($this->Settings->synced_data_sales){
                if($customer_details->synced_data && $customer_details->customer_url){
                // $salesItems =  $this->sales_model->getSalesItems($sale_id);
                 $salesDetails = $this->sales_model->getInvoiceByID($sale_id);
                    $_SESSION['Send_Notification'] = [
                         'status'=> '1',
                        'send_notification_sale_id' =>$sale_id,
                        'invoice_no' => $salesDetails->invoice_no,
                        'reference_no' => $reference,
                        'biller'=> $biller_details->name,  
                        'biller_id'=> $biller_id,
                        'items' =>'' ,//serialize($salesItems),
                        'send_request_url'   => base_url(),
                        'send_customer_url'  => $customer_details->customer_url.'/api4/salesNotification',
                        'pivatekey' => $customer_details->privatekey
                    ];
               }
            }

            $this->session->set_userdata('remove_slls', 1);
            unset($_SESSION['quick_customerid']);
            if ($quote_id) {
                $this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
            }
            $success_msg = ($sale_action == 'chalan') ? lang("Challan successfully added") : lang("sale_added");
            if ($customer_id != 1) {
                if ($this->Settings->whatsapp_service == '1' && ($this->pos_settings->invoice_auto_sms == '2' || $this->pos_settings->invoice_auto_sms == '3')) {
                    $customer_phone = $this->db->select('phone, is_system_generated')->where('id', $customer_id)->get('sma_companies')->row();
                    if (!empty($customer_phone) && !empty($customer_phone->phone)) {
                        $sms_code = md5('Reciept' . $reference . $sale_id);
                        $phone_system_generated = $this->sma->shouldHideCustomerPhone($customer_phone);
                        $formatted_phone = $this->format_phone_with_country_code($customer_phone->phone);
                        if ($sale_action == 'chalan') {
                            $wa_result = $this->sma->send_challan_whatsapp_message($sms_code, $formatted_phone, $phone_system_generated);
                        } else {
                            $wa_result = $this->sma->send_whatsapp_message($sms_code, $formatted_phone, $phone_system_generated);
                        }
                        if (is_array($wa_result) && !empty($wa_result['blocked'])) {
                            $success_msg .= '<br>' . $wa_result['msg'];
                        }
                    }
                }
            }
            $this->session->set_flashdata('message', $success_msg);
            if ($this->input->post('submit_type') == 'print') {
                /* ------ For checking Print/notPrint Button updated by SW 21/01/2017 --------------- */
                $print = $this->input->post('submit_type') == '' ? $this->input->post('submit_type') : 'print';
                $_SESSION['print_type'] = $print;
                $_SESSION['Sales'] = "Sales";
                /* ------ End For checking Print/notPrint Button updated by SW 21/01/2017 --------------- */
                if ($sale_action == 'chalan') {
                    $_SESSION['Sales'] = "Sales/challans";
                    redirect("sales/challan_view/" . $sale_id);
                } else {
                    redirect("pos/view/" . $sale_id);
                }
            } else {
                $inv = $this->sales_model->getInvoiceByID($sale_id);
                // $sale_type_input.' '.$inv->eshop_sale.' '.$inv->offline_sale.' '.$inv->pos;
                if ($sale_type_input != '') {
                    redirect('sales/all_sale_lists');
                } else {
                    if ($sale_action == 'chalan') {
                        redirect('sales/challans');
                    } elseif ($inv->eshop_sale == 1) {
                        redirect('eshop_sales/sales');
                    } elseif ($inv->offline_sale == 1) {
                        redirect('offline/sales');
                    } elseif ($inv->pos == 1) {
                        redirect('pos/sales');
                    } elseif ($inv->up_sales == 1) {
                        redirect('pos/up_sales');
                    } else {
                        redirect('sales');
                    }
                }
            }
        }
        else {

            $this->data['syncQuantity'] = 1;
            $this->data['saleAction'] = true;
            $this->data['formaction'] = isset($action) ? $action : '';

            if ($quote_id || $sale_id || $order_id || $chalan_id) {
                if ($chalan_id) {
                    $this->load->model('challan_model');
                    $this->data['quote'] = $this->challan_model->getChallanByID($chalan_id);
                    $items = $this->challan_model->getAllChallanItems($chalan_id);
                    $this->data['syncQuantity'] = 0;
                    $this->data['saleAction'] = false;
                    $this->data['order_id'] = $chalan_id;
                    $this->data['quote_id'] = $chalan_id;
                } elseif ($order_id) {
                    $this->data['quote'] = $this->orders_model->getOrderByID($order_id);
                    $items = $this->orders_model->getAllOrderItems($order_id);
                    $this->data['saleAction'] = false;
                    $this->data['order_id'] = $order_id;
                    $this->data['quote_id'] = $order_id;
                } elseif ($quote_id) {
                    $this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
                    $items = $this->sales_model->getAllQuoteItems($quote_id);
                    $this->data['saleAction'] = false;
                    $this->data['quote_id'] = $quote_id;
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
                    $items = $this->sales_model->getAllInvoiceItems($sale_id);
                    $this->data['quote_id'] = $sale_id;
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }

                    $unitData = $this->sales_model->getUnitById($row->unit);
                    $row->unit_lable = $unitData->name;
                    $row->id = $item->product_id;
                    $row->code = $item->product_code;
                    $row->name = $item->product_name;
                    $row->type = $item->product_type;
                    $row->qty = $item->quantity;
                    $row->base_quantity = $item->quantity;
                    $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                    $row->unit = $item->product_unit_id;
                    $row->qty = $item->unit_quantity;
                    $row->discount = $item->discount ? $item->discount : '0';
                    $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                    $row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate = $item->tax_rate_id;
                    $row->serial = '';
                    $row->option = $item->option_id;
                    // $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    $options = $this->sales_model->getProductOptionsByAttr($row->id, $item->warehouse_id, 1);
                    $options_color = $this->sales_model->getProductoptioncolor($item->shade_id);
                        if ($options_color) {
                            $opt_color = current($options_color);
                            if (!empty($opt_color)) {
                            $option_color_id = $opt_color->id;
                            $option_color_name = $opt_color->name;
                            $row->option_color_name = $option_color_name;
                        }
                        } else {
                            $opt_color = json_decode('{}');
                        }
                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if ($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }
                    if($this->Settings->product_batch_setting) {
                    
                        $productbatches = $this->products_model->getProductVariantsBatch($row->id,$warehouse_id);
                        
                        if($productbatches){
                            
                            $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);
    
                            if ($pis) {
                                $row->quantity_total = $option_quantity = 0;
                                foreach ($pis as $pi) {
                                    $row->quantity_total += $pi->quantity_balance;
                                    if($options !== false && $option_id == $pi->option_id){
                                        $option_quantity += $pi->quantity_balance;
                                        if($pi->batch_number && isset($productbatches[$pi->option_id])){
                                            
                                            foreach($productbatches[$pi->option_id] as $batch_id=>$optBatch){
                                                if($optBatch->batch_no == $pi->batch_number){
    
                                                    $productbatches[$pi->option_id][$batch_id]->quantity += $pi->quantity_balance;  
                                                }
                                            }                                                      
                                        }  
                                    } else {
                                        //Loose products batches quantity.
                                        if($pi->batch_number && isset($productbatches[0])){
                                            foreach($productbatches[0] as $batch_id=>$optBatch){
    
                                                if($optBatch->batch_no == $pi->batch_number){
    
                                                    $productbatches[0][$batch_id]->quantity += $pi->quantity_balance;  
                                                }
                                            }                                                      
                                        }  
                                    }//end else
                                }//end foreach
                            }//end if $pis
                            
                        }//end if $productbatches
                        $batch_option = ($option_id && $row->storage_type == 'packed') ? $option_id : 0;
                        $batch = $productbatches[$batch_option];
    
                        if ($batch) {
                            $firstKey = current($batch);
                            $batchoption = $batch;
    
                            $firstKey = $firstKey->id;
                            $row->batch          = $batchoption[$firstKey]->id;
                            $row->batch_number   = $batchoption[$firstKey]->batch_no;
                            $row->batch_quantity = $batchoption[$firstKey]->quantity;
                             
                            $row->unit_price     = $batchoption[$firstKey]->price ? $batchoption[$firstKey]->price : $row->unit_price;
                            $row->expiry         = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';
    
                        } else {
                            $batchoption = false;
                            $row->batch = false;
                            $row->batch_number = '';
                            $row->batch_quantity = 0;
                            
                        }
                    }
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    if ($row->type == 'Bundle') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'image' => $row->image, 'label' => $row->name . " (" . $row->code . ")",
                        'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id ? $quote_id : $sale_id;
            $this->data['order_id'] = $order_id ? $order_id : '';
            $this->data['sale_type'] = (isset($sale_type_input) && $sale_type_input) ? $sale_type_input : $sale_type;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            
            // Get user's assigned warehouses and filter sales persons accordingly
            try {
                $user = $this->site->getUser();
                $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
                if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                    $this->data['salesperson_details'] = $this->site->getSalesPersonsByWarehouse($user_warehouses);
                } else {
                    // Fallback to original method if new method doesn't exist
                    $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
                }
            } catch (Exception $e) {
                // Fallback to original method if there's any error
                $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
            }
            
            $this->data['Pos_settings'] = $this->site->get_pos_setting();
            //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
            $this->data['slnumber'] = ''; //$this->site->getReference('so');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');

            $this->data['sale_action'] = $this->input->get('sale_action') ? $this->input->get('sale_action') : 'sales';
            $user = $this->site->getUser();
            // Prioritize Warehouse Customer from User > Session > Settings > POS Default
            $default_customer_id = $this->pos_settings->default_customer;
            $target_warehouse_id = isset($warehouseId) ? $warehouseId : null; 
                if (empty($target_warehouse_id)) {
                    if (!empty($user->warehouse_id)) {
                        $uwh = explode(',', $user->warehouse_id);
                        $target_warehouse_id = trim($uwh[0]);
                    } elseif ($this->session->userdata('warehouse_id')) {
                        $swh = explode(',', $this->session->userdata('warehouse_id'));
                        $target_warehouse_id = trim($swh[0]);
                    } elseif (isset($this->Settings->default_warehouse)) {
                        $target_warehouse_id = $this->Settings->default_warehouse;
                    }
                }
                if ($target_warehouse_id) {
                     $warehouseArr = $this->site->getWarehouseByID($target_warehouse_id);
                     if ($warehouseArr) {
                         $warehouse = is_array($warehouseArr) ? reset($warehouseArr) : $warehouseArr;
                         if (is_object($warehouse) && !empty($warehouse->customer_id)) {
                             $default_customer_id = $warehouse->customer_id;
                         }
                     }
                }
            $customer_details = $this->site->getCompanyByID($default_customer_id);
            $this->data['customer'] = $customer_details; // Ensure view gets this object
            $default_customer_name = ($customer_details->name != '' && $customer_details->name != '-')? $customer_details->name: '';
            $this->data['default_customer_id']   = $default_customer_id;
            $this->data['default_customer_name'] = $default_customer_name;

            // shipping and billing address
            $sale_addresses = $this->site->getCustomerBillingShippingAddresses($default_customer_id);
            $default_shipping = $this->site->getDefaultCustomerAddress($sale_addresses['shipping']);
            $default_billing = $this->site->getDefaultCustomerAddress($sale_addresses['billing']);
            $this->data['default_shipping_address_id'] = $default_shipping['id'];
            $this->data['default_shipping_address_text'] = $default_shipping['label'];
            $this->data['default_billing_address_id'] = $default_billing['id'];
            $this->data['default_billing_address_text'] = $default_billing['label'];
            $this->data['order_types'] = $this->site->getOrderTypes();
            if ($this->data['sale_action'] == 'chalan') {
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('Add Sale Challan')));
                $meta = array('page_title' => lang('Add Sale Challan'), 'bc' => $bc);
            } else {
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale')));
                $meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
            }
                     
            $this->page_construct('sales/add', $meta, $this->data);
        }
    }

    public function edit($id = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $saleType = '';
        if ($this->uri->segment(4))
            $saleType = $this->uri->segment(4);
        $inv = $this->sales_model->getInvoiceByID($id);

        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('delivery_status', lang("delivery_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {

            $_ssot_mode = isset($this->pos_settings->sale_source_order_type_mode) ? (int) $this->pos_settings->sale_source_order_type_mode : 1;
            if ($_ssot_mode === 2 && trim((string) $this->input->post('order_type')) === '') {
                $this->session->set_flashdata('error', lang('sale_source_order_type_required'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
            }

            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['sales-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id       = $this->input->post('warehouse');
            $saleTypeInput      = $this->input->post('saleType');
            $customer_id        = $this->input->post('customer');
            $biller_id          = $this->input->post('biller');
            $total_items        = $this->input->post('total_items');
            $sale_status        = $this->input->post('sale_status');
            $payment_status     = $this->input->post('payment_status');
            $delivery_status    = $this->input->post('delivery_status');
            $payment_term       = $this->input->post('payment_term');
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note               = $this->sma->clear_tags($this->input->post('note'));
            $staff_note         = $this->sma->clear_tags($this->input->post('staff_note'));

            // check billing address state code from address table if not then check customer state code from company table
            $customer_state_code = '';
            $selected_billing_address_id = $this->input->post('billing_address_id') ? (int) $this->input->post('billing_address_id') : 0;
           
            if ($selected_billing_address_id > 0) {
                $selected_billing_address = $this->db->get_where('addresses', array(
                    'id' => $selected_billing_address_id,
                    'company_id' => $customer_id,
                ), 1)->row();
                if (!empty($selected_billing_address) && !empty($selected_billing_address->state_code)) {
                    $customer_state_code = trim($selected_billing_address->state_code);
                }
            }
            if ($customer_state_code === '') {
                $default_billing_address = $this->db->get_where('addresses', array(
                    'company_id' => $customer_id,
                    'type' => 'Billing',
                    'is_default' => 1,
                ), 1)->row();
                if (!empty($default_billing_address) && !empty($default_billing_address->state_code)) {
                    $customer_state_code = trim($default_billing_address->state_code);
                } else {
                    $customer_state_code = !empty($customer_details->state_code) ? trim($customer_details->state_code) : '';
                }
            }
            
            if ((!empty($customer_state_code) && !empty($biller_details->state_code)) && $customer_state_code != trim($biller_details->state_code)) {
                $interStateTax = true;
            } else {
                $interStateTax = false;
            }

            // Sales Person
            $SalesPersonDetails =  (isset($_POST['sales_person'])?$_POST['sales_person'] : NULL);
            if($SalesPersonDetails){
                $ExplodeSalesPerson = explode('-', $SalesPersonDetails);
                $SellerId = $ExplodeSalesPerson[0];
                $SellerName = $ExplodeSalesPerson[1];
            }
	        // End Sales Person
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $sale_cgst = $sale_sgst = $sale_igst = 0;


            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                
                $item_id    = $_POST['product_id'][$r];
                $item_type  = $_POST['product_type'][$r];
                $item_code  = $_POST['product_code'][$r];

                $hsn_code           = $_POST['hsn_code'][$r];
                $hsn_code           = ($hsn_code == 'null') ? '' : $hsn_code;

                $item_name          = $_POST['product_name'][$r];
                $batch_number       = isset($_POST['batch_number'][$r]) ? $_POST['batch_number'][$r] : null;
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : 0;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_quantity      = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_packing_size  = isset($_POST['packing_size'][$r]) ? $_POST['packing_size'][$r] : 0;
                $item_unit          = $_POST['product_unit'][$r];
                $item_unit_quantity = $_POST['product_base_quantity'][$r];
                $item_mrp           = $_POST['mrp'][$r];
                $item_cf1           = $_POST['cf1'][$r];               
                $item_weight        = $_POST['item_weight'][$r];
                $tax_method = $_POST['tax_method'][$r];
                 $category_id =  $_POST['cat_id'][$r]; /////////////// multiple category //////////////////////

                 $product_option_color = isset($_POST['product_option_color'][$r]) ? $_POST['product_option_color'][$r] : null;

                // product wise sales person
                $salesperson = isset($_POST['product_sales_person'][$r]) ? $_POST['product_sales_person'][$r] : null;
                if($salesperson){
                    $expsalesperson = explode('~', $salesperson);
                }
                // End Product wise sales person
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $item_mrp = !empty($item_mrp) ? $item_mrp : $product_details->mrp;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }
                    $unit_discount = $pr_discount;
                    $item_unit_price_less_discount = $this->sma->formatDecimal($unit_price - $unit_discount, 6);
                    $item_net_price = $net_unit_price = $item_unit_price_less_discount;

                    $pr_item_discount  = $this->sma->formatDecimal($pr_discount * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    $unit_surcharge = 0;
                    $item_surcharge = NULL;
                    $net_unit_price         = $item_unit_price_less_discount;
                    $unit_price             = $item_unit_price_less_discount;
                    $invoice_unit_price     = $item_unit_price_less_discount;
                    $invoice_net_unit_price = ($item_unit_price_less_discount + $unit_discount);

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                      
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";

                                $net_unit_price = $item_unit_price_less_discount;
                                $unit_price = $item_unit_price_less_discount + $item_tax;

                                $invoice_unit_price = $item_unit_price_less_discount;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;

                                $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                $unit_price = $item_unit_price_less_discount;

                                $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                            }

                            $unit_tax = $item_tax;
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";

                                $net_unit_price = $item_unit_price_less_discount;
                                $unit_price = $item_unit_price_less_discount + $item_tax;

                                $invoice_unit_price = $item_unit_price_less_discount;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;

                                $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                $unit_price = $item_unit_price_less_discount;

                                $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                            }

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity, 4);
                        $unit_tax = $item_tax;
                    }

                    if (isset($tax_details)) {
                        $unit_surcharge_val = $this->sma->calcSaleItemUnitSurcharge($tax_details, $unit_tax);
                        if ($unit_surcharge_val !== NULL && (float) $unit_surcharge_val != 0) {
                            $unit_surcharge = $this->sma->formatDecimal($unit_surcharge_val, 6);
                            $item_surcharge = $this->sma->formatDecimal(($unit_surcharge * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 6);

                            if ($product_details && $tax_method == 1) {
                                // Exclusive
                                $unit_price += $unit_surcharge;
                                $invoice_net_unit_price += $unit_surcharge;
                            } else {
                                // Inclusive
                                $net_unit_price -= $unit_surcharge;
                                $item_net_price = isset($item_net_price) ? ($item_net_price - $unit_surcharge) : $net_unit_price;
                                $invoice_unit_price -= $unit_surcharge;
                            }
                        }
                    }

                    if($interStateTax) {
                        $item_gst = $tax_details->rate;
                        $item_cgst = 0;
                        $item_sgst = 0;
                        $item_igst = $pr_item_tax;
                    } else {
                        $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                        $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_igst = 0;
                    }


                    $invoice_unit_price             = $this->sma->formatDecimal($invoice_unit_price, 4);
                    $invoice_net_unit_price         = $this->sma->formatDecimal($invoice_net_unit_price, 4);
                    $invoice_total_net_unit_price   = $this->sma->formatDecimal(($invoice_net_unit_price * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);
                    $product_tax += $pr_item_tax;
                    if ($item_surcharge !== NULL) {
                        $product_tax += (float) $item_surcharge;
                    }
                    
                    // Apply packing_size multiplier to subtotal if packing_size > 0
                    if ($item_packing_size > 0) {
                        $subtotal = $this->sma->formatDecimal((($unit_price * $item_packing_size) * $item_quantity), 4);
                    } else {
                        $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);
                        if ($item_surcharge !== NULL) {
                            $subtotal += $item_surcharge;
                        }
                    }
                    
                    $unit                           = $this->site->getUnitByID($item_unit);
                    $net_price                      = $this->sma->formatDecimal(($item_mrp * $item_quantity), 4);
                    $sale_item_discount = $this->sma->getSaleItemDiscountForStorage(null, $item_discount, $item_mrp, $real_unit_price);

                    $products[] = array(
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                         'category_id'       => $category_id,  ////// multiple category //////////
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'batch_number'      => $batch_number,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $unit_price,
                        'surcharge'         => $item_surcharge,
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $pr_tax,
                        'tax'               => $tax,
                        // 'discount'          => $item_discount,
                        'discount'          => $sale_item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                        'mrp'               => $item_mrp,
                        'hsn_code'          => $hsn_code,
                        'cf1'               => $item_cf1,                         
                        'cf1_name'          => 'Exp. Date',                         
                        'net_price'         => $net_price,
                        'tax_method'        => $tax_method,
                        'unit_discount'     => $unit_discount,
                        'unit_tax'          => $unit_tax,
                        'item_weight'       => $item_weight,
                        'gst_rate'          => $item_gst,
                        'cgst'              => $item_cgst,
                        'sgst'              => $item_sgst,
                        'igst'              => $item_igst,
                        'invoice_unit_price'            => $invoice_unit_price,
                        'invoice_net_unit_price'        => $invoice_net_unit_price,
                        'invoice_total_net_unit_price'  => $invoice_total_net_unit_price,
                        'shade_id'          => $product_option_color,
                        'seller_id'         => ($salesperson)?$expsalesperson[0]:NULL,
                        'seller'            => ($salesperson)?$expsalesperson[1] :NULL,
                        'packing_size'      => $item_packing_size,
                    );
                    $sale_cgst += $item_cgst;
                    $sale_sgst += $item_sgst;
                    $sale_igst += $item_igst;

                    // Apply packing_size multiplier to total if packing_size > 0
                    // NOTE: Do NOT include $pr_item_tax here. Tax is accumulated separately
                    // in $product_tax and added to grand_total via $total_tax.
                    // Including it here causes double-counting of tax in grand_total.
                    if ($item_packing_size > 0) {
                        $total += $this->sma->formatDecimal(($item_net_price * $item_packing_size * $item_quantity), 4);
                    } else {
                        $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                    }
                }
            }
            if (empty($products)) {

    $this->form_validation->set_rules('product', lang("order_items"), 'required');

} else {

    // Same flow as add sale

    $sale_items = $products;

    unset($products);

    foreach ($sale_items as $key => $item) {

        // Sort internal array keys only
        ksort($item);

        $products[] = $item;
    }
}
            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                /* $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                  $ods = explode("%", $order_discount_id);
                  $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                  } else {
                  $order_discount = $this->sma->formatDecimal($order_discount_id);
                  } */
            } else {
                $order_discount_id = null;
            }
            //$total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
            $total_discount = $this->sma->formatDecimal($product_discount);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        // $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax ) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping)), 4);

            /* 12-6-2019 */
            $rounding = '';

            if ($this->pos_settings->rounding > 0) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = ($round_total - $grand_total);
            }
           
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'shipping_address_id' => $this->input->post('shipping_address_id') ? $this->input->post('shipping_address_id') : NULL,
                'billing_address_id' => $this->input->post('billing_address_id') ? $this->input->post('billing_address_id') : NULL,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'seller_id' => isset($SellerId)?$SellerId : NULL,
                'seller' => isset($SellerName)?$SellerName : NULL,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'cgst' => $sale_cgst,
                'sgst' => $sale_sgst,
                'igst' => $sale_igst,
                'sale_status' => $sale_status,
                'delivery_status' => $delivery_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'rounding' => $rounding,
                'due_date' => $due_date,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'transporter_mode' => $this->input->post('transporter_mode'),
                'LR_No' => $this->input->post('LR_No'),
                'total_parcels' => $this->input->post('total_parcels'),
                'place_of_supply' => $this->input->post('place_of_supply'),
                'order_type' => $this->input->post('order_type'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products)) {

            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("sale_updated"));
            //echo $inv->eshop_sale.' '.$inv->offline_sale.' '.$inv->pos; exit;
            if ($saleTypeInput != '') {
                redirect('sales/all_sale_lists');
            } else {
                if ($this->input->post('redirects') == 'reports/sales') {
                    redirect('reports/sales');
                }elseif( $this->input->post('redirects')=='reports_new/sales_gst_reportnew'){
                     redirect('reports_new/sales_gst_reportnew');
                } elseif ($inv->eshop_sale == 1) {
                    redirect('eshop_sales/sales');
                } elseif ($inv->offline_sale == 1) {
                    redirect('offline/sales');
                } elseif ($inv->pos == 1) {
                    redirect('pos/sales');
                } elseif ($inv->up_sales == 1) {
                    redirect('pos/up_sales');
                } else {
                    redirect('sales');
                }
            }
        }         
        else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $ResEshopOrder = $this->orders_model->getEshopOrderByInvoice($this->data['inv']->order_no);
            $this->data['eshop_order'] = $this->orders_model->getOrderDetails($ResEshopOrder->id);
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("sale_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            krsort($inv_items);
            $pos_settings = $this->site->get_pos_setting();
            $item_order = isset($pos_settings->item_order) ? (int)$pos_settings->item_order : 0;
            $order_multiplier = ($item_order == 0) ? -1 : 1;
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {

                $row = $this->site->getProductByID($item->product_id);
                
                // Get user's assigned warehouses and filter sales persons accordingly
                try {
                    $user = $this->site->getUser();
                    $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
                    if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                        $salesperson_details = $this->site->getSalesPersonsByWarehouse($user_warehouses);
                    } else {
                        // Fallback to original method if new method doesn't exist
                        $salesperson_details = $this->site->getCompanyDetailsByGroupID(5);
                    }
                } catch (Exception $e) {
                    // Fallback to original method if there's any error
                    $salesperson_details = $this->site->getCompanyDetailsByGroupID(5);
                }

                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    unset($row->alert_quantity, $row->brand, $row->comments_count, $row->divisionid, $row->file, $row->food_type_id, $row->in_eshop, $row->is_featured, $row->purchase_unit, $row->ratings_avarage, $row->ratings_count, $row->supplier3price, $row->track_quantity, $row->up_items, $row->up_price, $row->updated_at );
                }
              
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {                        
                        $row->quantity += $pi->quantity_balance;
                    }
                }

                //$unitData = $this->sales_model->getUnitById($row->unit);
                $unitData = $this->sales_model->getUnitById($item->product_unit_id);
                $row->unit_lable        = $unitData->name;
                $row->id                = $item->product_id;
                $row->code              = $item->product_code;
                $row->name              = $item->product_name;
                $row->type              = $item->product_type;
                $row->base_quantity     = $item->unit_quantity;
                $row->unit_quantity     = $item->unit_quantity;
                $row->old_qty           = $item->unit_quantity;
                $category_data          = $this->pos_model->getCategoryIdByName($row->category_id);
                $row->category_name     = $category_data ? $category_data->name : '';
                $row->base_unit         = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price   = $row->price;
                $row->unit              = $item->product_unit_id;
                $row->qty               = $item->quantity;              
                $row->discount          = $item->discount ? $item->discount : '0';
                //$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                $row->unit_price        = ($row->tax_method ) ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price   = $item->real_unit_price;
                $row->packing_size      = $item->packing_size;
                $row->tax_rate          = $item->tax_rate_id;
                $row->serial            = $item->serial_no;
                $row->option            = $item->option_id;
                $row->delivery_status   = $item->delivery_status;
                $row->delivered_qty     = $item->delivered_quantity;
                $row->pending_qty       = $item->pending_quantity;
                $row->net_unit_price    = $item->net_unit_price;
                $row->cf1               = $item->cf1;
                $row->cf2               = $item->cf2;
                $row->batch_number      = $item->batch_number;
                $row->unit_weight       = $row->weight;
                $row->tax_method        = $item->tax_method;
                $row->invoice_unit_price = $item->invoice_unit_price;
                $row->edit = 'edit';
                $row->option_color      = $item->shade_id;
                $row->seller_id         = $item->seller_id;
                
                $options = FALSE;
                $option_id              = $item->option_id;
                
                if($this->Settings->attributes == 1 && ($row->storage_type == 'packed' || ($row->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1 ) ) ){
                    //$options = $this->sales_model->getProductVariants($row->id);
                    $options = $this->sales_model->getProductOptionsByAttr($row->id, $item->warehouse_id, 1);
                    
                    $opt = json_decode('{}');
                    
                    if($options && $option_id) { 
                        $opt = $options[$option_id];                            
                                              
                        $row->unit_quantity = $opt->unit_quantity ? $opt->unit_quantity : 1;
                        $row->unit_weight   = $opt->unit_weight ? $opt->unit_weight : ($row->weight ? $row->weight * $row->unit_quantity : '' );
                    }  
                } else {
                    $option_id = 0;
                    $option_quantity = 0;
                }   
                $options_color = $this->sales_model->getProductoptioncolor($item->shade_id);  
                       
                
                $row->option  = $option_id;                   
                $row->base_quantity = $row->unit_quantity * $row->qty;
                if ($options_color) {
                    $opt_color = current($options_color);
                    if (!empty($opt_color)) {
                    $option_color_id = $opt_color->id;
                    $option_color_name = $opt_color->name;
                    $row->option_color_name = $option_color_name;
                }
                } else {
                    $opt_color = json_decode('{}');
                }
                
                if ($options && $opt->product_id == $row->id ) {
                    
                    foreach ($options as $option) {
                        if($row->storage_type == "packed") {
                            $option_quantity = 0;
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $option->id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }                            
                            $option->quantity = $option_quantity;
                           
                        } else {
                            //Loose products Variants Quantity Calculate
                           // $option->quantity = number_format($row->quantity / $option->unit_quantity , 2);
                            $option->quantity = number_format($row->quantity , 3);
                        }
                        
//                        if ((!$this->Settings->overselling && $option->quantity) || $this->Settings->overselling){
                            $product_options[$option->id] = $option; 
//                        }
                    }
                    
                    $row->quantity = $product_options[$option_id]->quantity;
                    foreach ($options as $option) {
                        $option->price = $item->real_unit_price;
                    }
                } else {
                    $product_options = FALSE;
                }
                
                 
                /**
                 * Batch Config
                 **/
                $batchoption = $productbatches = $batchs = FALSE;
                
                if($this->Settings->product_batch_setting) {
                    
                    $productbatches = $this->products_model->getProductVariantsBatch($row->id,$warehouse_id);
                    
                    if($productbatches){
                        
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);

                        if ($pis) {
                            $row->quantity_total = $option_quantity = 0;
                            foreach ($pis as $pi) {
                                $row->quantity_total += $pi->quantity_balance;
                                if($options !== false && $option_id == $pi->option_id){
                                    $option_quantity += $pi->quantity_balance;
                                    if($pi->batch_number && isset($productbatches[$pi->option_id])){
                                        
                                        foreach($productbatches[$pi->option_id] as $batch_id=>$optBatch){
                                            if($optBatch->batch_no == $pi->batch_number){

                                                $productbatches[$pi->option_id][$batch_id]->quantity += $pi->quantity_balance;  
                                            }
                                        }                                                      
                                    }  
                                } else {
                                    //Loose products batches quantity.
                                    if($pi->batch_number && isset($productbatches[0])){
                                        foreach($productbatches[0] as $batch_id=>$optBatch){

                                            if($optBatch->batch_no == $pi->batch_number){

                                                $productbatches[0][$batch_id]->quantity += $pi->quantity_balance;  
                                            }
                                        }                                                      
                                    }  
                                }//end else
                            }//end foreach
                        }//end if $pis
                        
                    }//end if $productbatches
                    
                    $batch_option = ($options !== false) ? $option_id : 0;

                    $batch = isset($productbatches[$batch_option]) ? $productbatches[$batch_option] : false;

                    if ($batch) {
                        $firstKey = key($batch);
                        foreach ($batch as $batch_id => $optBatch) {
                            if ($optBatch->batch_no == $item->batch_number) {
                                $firstKey = $batch_id;
                                break;
                            }
                        }
                        $batchoption = $batch;
                        $row->batch          = $batchoption[$firstKey]->id;
                        // $row->batch_number   = $batchoption[$firstKey]->batch_no;
                        $row->batch_number      = !empty($item->batch_number)? $item->batch_number: $batchoption[$firstKey]->batch_no;
                        $row->batch_quantity    = $batchoption[$firstKey]->quantity;
                         
                       // $row->unit_price     = $batchoption[$firstKey]->price ? $batchoption[$firstKey]->price : $row->unit_price;
                        $row->expiry         = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';

                    } else {
                        $batchoption = false;
                        $row->batch = false;
                        $row->batch_number = '';
                        $row->batch_quantity = 0;   
                        $row->batch_quantity = 0;   
                        // foreach ($productbatches as $optionBatches) {
                        //     if (!empty($optionBatches)) {
                        //         $firstBatch = reset($optionBatches); 
                        //         $row->batch_number = $firstBatch->batch_no; 
                        //         break;
                        //     }
                        // }                     
                                           
                    }
                }
                /**
                 * End Batch Config
                 */
                    

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                if ($row->type == 'Bundle') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                // category leve tax 
                $categoryTax = $this->sales_model->getCategoryTax((($row->subcategory_id)?$row->subcategory_id :$row->category_id));
                // if($categoryTax){
                //     if($categoryTax->fix_tax_rate=='fix tax'){
                //         $gettax =  $this->site->getTaxRateByID($categoryTax->tax_rate);
                //         $fixtaxrate = $gettax->id.'~'.$gettax->rate;
                //         $varaibletax = False;
                //     } else {
                //         $varaibletax = $this->sales_model->getVariableTax((($row->subcategory_id)?$row->subcategory_id :$row->category_id));
                //         $fixtaxrate = False;
                //     }
                // }
                if ($categoryTax) {
                    if (strtolower($categoryTax->fix_tax_rate) == 'fix tax') {
                        if(!empty($row->tax_rate)){  //checking product level tax
                            $gettax = $this->site->getTaxRateByID($row->tax_rate);
                            $fixtaxrate = $gettax->id . '~' . $gettax->rate;
                            $varaibletax = False;
                        }else{
                            $gettax = $this->site->getTaxRateByID($categoryTax->tax_rate);  //checking fixed tax
                            $fixtaxrate = $gettax->id . '~' . $gettax->rate;
                            $varaibletax = False;
                            $tax_rate   = $this->site->getTaxRateByID($gettax->id);
                            $row->tax_rate = $gettax->id;
                        }
                    
                    } else {
                        if(!empty($row->tax_rate)){  //checking product level tax
                            $gettax = $this->site->getTaxRateByID($row->tax_rate);
                            $fixtaxrate = $gettax->id . '~' . $gettax->rate;
                            $varaibletax = False;
                        }else{
                            // checking varible tax
                            $varaibletax = $this->sales_model->getVariableTax((($row->subcategory_id) ? $row->subcategory_id : $row->category_id));
                            $fixtaxrate = False;
                            $taxratevalue = $varaibletax[1]['taxratevalue'];
                            $parts = explode('~', $taxratevalue);
                            $gettax->id = $parts[0]; 
                            $tax_rate   = $this->site->getTaxRateByID($gettax->id);
                            $row->tax_rate = $gettax->id;
                        }
                    }
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                
                $row_id  = $row->id;
                $row_id  = $row->id . $row->option;
                $row_id .= ($row->batch) ? $row->batch : '';
                $ri = $this->Settings->item_addition ? $row_id : $c;
                
               

                $pr[$ri] = array('id' => $c, 'item_id' => $row_id, 'image' => $row->image, 'label' => $row->name . " (" . $row->code . ")",  
                    'fixtax'=> $fixtaxrate,'category_tax'=> $varaibletax,'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'cf1' => $row->cf1, 'cf2' => $row->cf2, 'units' => $units, 'options' => $options, 'batchs' => $batchoption, 'product_batches' => $productbatches, 'options_color' => $options_color, 'salesperson_details' => $salesperson_details, 'order' => $order_multiplier * (int) $item->id, 'category' => $row->category_id, 'sub_category' => $row->subcategory_id, 'divisionid' => $row->divisionid, 'brand' => $row->brand);
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
          
            $this->data['eshop_sale'] = $inv->eshop_sale;
            $this->data['id'] = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['sale_type'] = $saleTypeInput ? $saleTypeInput : $saleType;
            
            // Get user's assigned warehouses and filter sales persons accordingly
            try {
                $user = $this->site->getUser();
                $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
                if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                    $this->data['salesperson_details'] = $this->site->getSalesPersonsByWarehouse($user_warehouses);
                } else {
                    // Fallback to original method if new method doesn't exist
                    $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
                }
            } catch (Exception $e) {
                // Fallback to original method if there's any error
                $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
            }
            
            $this->data['pos_settings'] = $this->data['Pos_settings'] = $this->site->get_pos_setting();
            $this->data['order_types'] = $this->site->getOrderTypes();
            $this->data['sale_action'] = $this->uri->segment(2);

            $sale_inv = $this->data['inv'];
            $shipping_addr = $this->site->getCustomerAddressById($sale_inv->shipping_address_id);
            $billing_addr = $this->site->getCustomerAddressById($sale_inv->billing_address_id);
            $this->data['default_shipping_address_id'] = $shipping_addr['id'];
            $this->data['default_shipping_address_text'] = $shipping_addr['label'];
            $this->data['default_billing_address_id'] = $billing_addr['id'];
            $this->data['default_billing_address_text'] = $billing_addr['label'];

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('edit_sale')));
            $meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);

            $this->page_construct('sales/edit', $meta, $this->data);
        }
    }

    public function return_sale($id = null) {
        
        $this->sma->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $saleType = '';
        if ($this->uri->segment(4))
            $saleType = $this->uri->segment(4);

        $sale = $this->sales_model->getInvoiceByID($id);
        
          if($this->Owner || $this->Admin) {

          }else{
            
            
                $date1=date_create(date('Y-m-d',strtotime($sale->date)));
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
               $diff->format("%a"); 
                if($diff->format("%a") >= $this->GP['sales-return_invoice_days']){
                   $this->session->set_flashdata('warning', lang('access_denied'));
                   redirect($_SERVER["HTTP_REFERER"]);
               }
         }
        

        //echo $this->Settings->sale_multiple_return_edit; exit;
        if ($sale->return_id) {
            if ($this->Settings->sale_multiple_return_edit == 0) {
                $this->session->set_flashdata('error', lang("sale_already_returned"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $ReturnTotalItems = 0;
                $Return_sale = $this->sales_model->getAllReturnInvoiceByID($id);
                foreach ($Return_sale as $keys => $vals) {
                    $ReturnTotalItems = $ReturnTotalItems + $vals['total_items'];
                }
                $CalReturnTotalItems = $sale->total_items + $ReturnTotalItems;
                if ($CalReturnTotalItems == 0) {
                    $this->session->set_flashdata('error', lang("sale_already_returned"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
        }

        $customer_details = $this->site->getCompanyByID($sale->customer_id);
        $biller_details   = $this->site->getCompanyByID($sale->biller_id);

        if ((!empty($customer_details->state_code) && !empty($biller_details->state_code)) && $customer_details->state_code != $biller_details->state_code) {
            $interStateTax = true;
        } else {
            $interStateTax = false;
        }

        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {
            $saleTypeInput = $this->input->post('saleType');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            // Ensure we always have a valid date, especially for deposit payments where date field might be disabled
            if ($this->Owner || $this->Admin) {
                $submitted_date = trim($this->input->post('date'));
                $hidden_date = trim($this->input->post('date_hidden'));
                
                // Use hidden date field as fallback if main date field is empty
                if (empty($submitted_date) && !empty($hidden_date)) {
                    $submitted_date = $hidden_date;
                }
                
                if (!empty($submitted_date)) {
                    $date = $this->sma->fld($submitted_date);
                    if (substr_count($date, ':') == 1) {
                        $date .= ':' . date('s');
                    }
                } else {
                    // Fallback to current date/time if submitted date is empty (common with deposit payments)
                    $date = date('Y-m-d H:i:s');
                }
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->sma->clear_tags($this->input->post('note'));

            // Invoice-level Sales Person for return sale (display_seller 1 or 2)
            $SellerId = $sale->seller_id;
            $SellerName = $sale->seller;
            if (isset($this->pos_settings->display_seller) && in_array((int)$this->pos_settings->display_seller, [1, 2], true)) {
                $SalesPersonDetails = $this->input->post('sales_person') ? $this->input->post('sales_person') : null;
                if ($SalesPersonDetails) {
                    $ExplodeSalesPerson = explode('-', $SalesPersonDetails);
                    if (!empty($ExplodeSalesPerson[0])) {
                        $SellerId = $ExplodeSalesPerson[0];
                        $SellerName = isset($ExplodeSalesPerson[1]) ? $ExplodeSalesPerson[1] : '';
                    }
                }
            }

            $total              = 0;
            $product_tax        = 0;
            $order_tax          = 0;
            $product_discount   = 0;
            $order_discount     = 0;
            $percentage         = '%';
            $sale_cgst = $sale_sgst = $sale_igst = 0;
            $syncQuantity = $this->input->post('syncQuantity');
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            
            for ($r = 0; $r < $i; $r++) {
                if ($_POST['quantity'][$r] > 0) {
                    
                    $item_id        = $_POST['product_id'][$r];
                    $item_type      = $_POST['product_type'][$r];
                    $item_code      = $_POST['product_code'][$r];
                    $item_name      = $_POST['product_name'][$r];
                    $batch_number   = $_POST['batch_number'][$r];
                    $sale_item_id   = $_POST['sale_item_id'][$r];
                    $item_option    = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                    $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);

                    //$unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                    $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                    $item_quantity      = (0 - $_POST['quantity'][$r]);
                    $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_unit          = $_POST['product_unit'][$r];
                    $item_unit_quantity = (0 - $_POST['product_base_quantity'][$r]);
                    $item_mrp           = $_POST['mrp'][$r];
                    $item_expity        = $_POST['cf1'][$r];
                    $item_weight        = (0 - $_POST['item_weight'][$r]);
                    $category_id        = $_POST['cat_id'][$r]; ////////////// Multiple Category//////////////
                    $product_option_color = $_POST['product_option_color'][$r];
                    $item_packing_size  = isset($_POST['packing_size'][$r]) ? $_POST['packing_size'][$r] : 0;
                    
                    // product wise sales person
                    $salesperson = isset($_POST['product_sales_person'][$r]) ? $_POST['product_sales_person'][$r] : null;
                    $expsalesperson = array(); // Initialize to prevent undefined variable
                    if($salesperson){
                        $expsalesperson = explode('~', $salesperson);
                    }
                    // End Product wise sales person
                    
                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                        // $unit_price = $real_unit_price;
                        $item_mrp = !empty($item_mrp) ? $item_mrp : $product_details->mrp;
                        $item_mrp = $this->sma->formatDecimal($item_mrp);
                        $pr_discount = 0;
                        $unit_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount, 4);
                            }
                        }
                        $unit_discount = $pr_discount;
                        $item_unit_price_less_discount = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                        $unit_price = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                        $item_net_price = $unit_price;
                        $pr_item_discount = $this->sma->formatDecimal($pr_discount * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity, 4);
                        $product_discount += $pr_item_discount;
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $unit_tax = 0;
                        $item_tax = 0;
                        $tax = "";
                        $tax_method = '';
                        $net_unit_price = $item_unit_price_less_discount;
                        $unit_price = $item_unit_price_less_discount;
                        $invoice_unit_price = $item_unit_price_less_discount;
                        $invoice_net_unit_price = ($item_unit_price_less_discount + $unit_discount);
                        $unit_surcharge = 0;
                        $item_surcharge = NULL;

                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $tax_method = $product_details->tax_method;
                            $pr_tax = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }
                            } elseif ($tax_details->type == 2) {

                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;
                            }
                            $unit_tax = $item_tax;

                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);

                            if (isset($tax_details)) {
                                $unit_surcharge_val = $this->sma->calcSaleItemUnitSurcharge($tax_details, $unit_tax);
                                if ($unit_surcharge_val !== NULL && (float) $unit_surcharge_val != 0) {
                                    $unit_surcharge = $this->sma->formatDecimal($unit_surcharge_val, 6);
                                    $item_surcharge = $this->sma->formatDecimal(($unit_surcharge * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 6);

                                    if ($product_details && $tax_method == 1) {
                                        // Exclusive
                                        $unit_price += $unit_surcharge;
                                        $invoice_net_unit_price += $unit_surcharge;
                                    } else {
                                        // Inclusive
                                        $net_unit_price -= $unit_surcharge;
                                        $item_net_price = isset($item_net_price) ? ($item_net_price - $unit_surcharge) : $net_unit_price;
                                        $invoice_unit_price -= $unit_surcharge;
                                    }
                                }
                            }
                        }

                        if ($interStateTax) {
                            $item_gst = $tax_details->rate;
                            $item_cgst = 0;
                            $item_sgst = 0;
                            $item_igst = $pr_item_tax;
                        } else {
                            $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                            $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                            $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                            $item_igst = 0;
                        }

                        $product_tax += $pr_item_tax;
                        if ($item_surcharge !== NULL) {
                            $product_tax += (float) $item_surcharge;
                        }
                        $subtotal = $this->sma->formatDecimal((($item_net_price * $item_quantity) + $pr_item_tax), 4);
                        if ($item_surcharge !== NULL) {
                            $subtotal += $item_surcharge;
                        }
                        
                        if ($item_packing_size > 0) {
                            $subtotal = $this->sma->formatDecimal((($unit_price * $item_packing_size) * $item_quantity), 4);
                            $net_unit_price = $this->sma->formatDecimal(($real_unit_price * $item_packing_size) - $pr_discount, 4);
                        }
                        
                        $unit = $this->site->getUnitByID($item_unit);

                        $unit_discount = 0 - $this->sma->formatDecimal($unit_discount, 4);
                        $unit_tax = 0 - $this->sma->formatDecimal($unit_tax, 4);
                        $invoice_unit_price = $this->sma->formatDecimal($invoice_unit_price, 4);
                        $invoice_net_unit_price = $this->sma->formatDecimal($invoice_net_unit_price, 4);
                        $invoice_total_net_unit_price = $this->sma->formatDecimal(($invoice_net_unit_price * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);
                        $net_price = $this->sma->formatDecimal(($item_mrp * $item_quantity), 4);


                        $products[] = [
                            'product_id'        => $item_id,
                            'product_code'      => $item_code,
                             'category_id'      => $category_id, ////////////// Multiple Category//////////////
                            'product_name'      => $item_name,
                            'product_type'      => $item_type,
                            'packing_size'      => $item_packing_size,
                            'article_code'      => $product_details->article_code,
                            'hsn_code'          => $product_details->hsn_code,
                            'option_id'         => $item_option,
                            'batch_number'      => $batch_number,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax + $unit_surcharge),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity'     => $item_unit_quantity,
                            'warehouse_id'      => $sale->warehouse_id,
                            'item_tax'          => $pr_item_tax,
                            'surcharge'         => $item_surcharge,
                            'tax_rate_id'       => $pr_tax,
                            'tax'               => $tax,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'serial_no'         => $item_serial,
                            'real_unit_price'   => $real_unit_price,
                            'sale_item_id'      => $sale_item_id,
                            'mrp'               => $item_mrp,
                            'tax_method'        => $tax_method,
                            'unit_discount'     => $unit_discount,
                            'unit_tax'          => $unit_tax,
                            'invoice_unit_price'            => $invoice_unit_price,
                            'invoice_net_unit_price'        => $invoice_net_unit_price,
                            'invoice_total_net_unit_price'  => $invoice_total_net_unit_price,
                            'net_price'         => $net_price,
                            'gst_rate'          => $item_gst,
                            'cgst'              => $item_cgst,
                            'sgst'              => $item_sgst,
                            'igst'              => $item_igst,
                            'cf1'               => $item_expity,
                            'cf1_name'          => 'Exp. Date',
                            'item_weight'       => $item_weight,
                            'shade_id'          => $product_option_color,
                            'seller_id'         => (trim($salesperson) !== '')?$expsalesperson[0] : (isset($SellerId) ? $SellerId : NULL),  
                            'seller'            => (trim($salesperson) !== '')?$expsalesperson[1] : (isset($SellerName) ? $SellerName : NULL),
                        ];
                        

                        $si_return[] = [
                            'id'            => $sale_item_id,
                            'sale_id'       => $id,
                            'packing_size'  => $item_packing_size,
                            'product_id'    => $item_id,
                            'option_id'     => $item_option,
                            'batch_number'  => $batch_number,
                            'quantity'      => (0 - $item_unit_quantity),
                            'warehouse_id'  => $sale->warehouse_id,
                            'seller_id'     => ($salesperson)?$expsalesperson[0] : (isset($SellerId) ? $SellerId : NULL),  
                            'seller'        => ($salesperson)? $expsalesperson[1] : (isset($SellerName) ? $SellerName : NULL),
                        ];

                        $sale_cgst += $item_cgst;
                        $sale_sgst += $item_sgst;
                        $sale_igst += $item_igst;

                        if ($item_packing_size > 0) {
                            $total += $this->sma->formatDecimal(($item_net_price * $item_packing_size * $item_quantity), 4);
                        } else {
                            $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                        }
                    }
                }
            }
            if (empty($products)) {

    $this->form_validation->set_rules('product', lang("order_items"), 'required');

} else {

    // Same flow as add sale

    $sale_items = $products;

    unset($products);

    foreach ($sale_items as $key => $item) {

        // Sort internal array keys only
        ksort($item);

        $products[] = $item;
    }
}
          
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                /* $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                  $ods = explode("%", $order_discount_id);
                  $order_discount = '-' . $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                  } else {
                  $order_discount = '-' . $this->sma->formatDecimal($order_discount_id, 4);
                  } */
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal($product_tax + $order_tax, 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);

            $rounding = '';

            if ($this->pos_settings->rounding > 0) {
                $abs_grand_total = abs($grand_total);
                $round_total = $this->sma->roundNumber($abs_grand_total, $this->pos_settings->rounding);
                $round_total = ($grand_total < 0) ? -1 * $round_total : $round_total;
                $rounding = ($round_total - $grand_total);
            }

            $data = [
                'date'              => $date,
                'sale_id'           => $id,
                'reference_no'      => $sale->reference_no,
                'seller_id'         => $SellerId,
                'seller'            => $SellerName,
                'customer_id'       => $sale->customer_id,
                'customer'          => $sale->customer,
                'biller_id'         => $sale->biller_id,
                'biller'            => $sale->biller,
                'warehouse_id'      => $sale->warehouse_id,
                'pos'               => $sale->pos,
                'eshop_sale'        => $sale->eshop_sale,
                'offline_sale'      => $sale->offline_sale,
                'offlinepos_sale_reff'      => $sale->offlinepos_sale_reff,
                'offline_reference_no'      => $sale->offline_reference_no,
                'offline_payment_id'        => $sale->offline_payment_id,
                'offline_transaction_type'  => $sale->offline_transaction_type,
                'note'                  => $note,
                'total'                 => $total,
                'product_discount'      => $product_discount,
                'order_discount_id'     => $order_discount_id,
                'order_discount'        => $order_discount,
                'total_discount'        => $total_discount,
                'product_tax'           => $product_tax,
                'order_tax_id'          => $order_tax_id,
                'order_tax'             => $order_tax,
                'total_tax'             => $total_tax,
                'surcharge'             => $this->sma->formatDecimal($return_surcharge),
                'grand_total'           => $grand_total,
                'created_by'            => $this->session->userdata('user_id'),
                'return_sale_ref'       => $reference,
                'rounding'              => $rounding,
                'sale_status'           => 'returned',
                'payment_status'        => $sale->payment_status == 'paid' ? 'due' : 'pending',
                'total_items'           => (0 - ($this->input->post('total_items'))),
                'cgst'                  => $sale_cgst,
                'sgst'                  => $sale_sgst,
                'igst'                  => $sale_igst,
            ];

            
            
            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
                $pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
                /* 9-11-2019 Add paid amount to giftcard and Deposit */
                $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                $amount = $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0;

                $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no')); //Gift Card Balance
                $gc_balance = $gc->balance; // + $amount; //Add Amount To gift card balance 
                $cd = $this->site->getCreditNoteByNO($this->input->post('credit_card_no')); //Gift Card Balance
                $cd_balance = $cd->balance + $amount; //Add Amount To gift card balance 
                $desposit = $this->site->customerDepositAmt($sale->customer_id); //Deposit balance
                $deposit_balance = $desposit + $amount; //Add Amount To Deposit balance 

                $pos_paid = $this->input->post('pospaid') ? $this->input->post('pospaid') : 0;
                $pos_balance = $this->input->post('posbalance') ? $this->input->post('posbalance') : $this->input->post('posbalance');
                /* end */

                if ($this->input->post('paid_by') == 'deposit') {

                    $payment = array(
                        'date' => $date,
                        'reference_no' => $pay_ref,
                        'amount' => (0 - $this->input->post('amount-paid')),
                        'paid_by' => $this->input->post('paid_by'),
                        'cheque_no' => $this->input->post('cheque_no'),
                        'cc_no' => $cc_no,
                        'cc_holder' => $deposit_balance,
                        'cc_month' => $this->input->post('pcc_month'),
                        'cc_year' => $this->input->post('pcc_year'),
                        'cc_type' => $this->input->post('pcc_type'),
                        'created_by' => $this->session->userdata('user_id'),
                        'pos_paid' => $pos_paid,
                        'pos_balance' => $pos_balance,
                        'type' => 'returned',
                    );
                } else if ($this->input->post('paid_by') == 'gift_card') {
                    $cc_no = $this->input->post('gift_card_no');
                    $payment = array(
                        'date' => $date,
                        'reference_no' => $pay_ref,
                        'amount' => (0 - $this->input->post('amount-paid')),
                        'paid_by' => $this->input->post('paid_by'),
                        'cheque_no' => $this->input->post('cheque_no'),
                        'cc_no' => $cc_no,
                        'cc_holder' => $gc_balance,
                        'cc_month' => $this->input->post('pcc_month'),
                        'cc_year' => $this->input->post('pcc_year'),
                        'cc_type' => $this->input->post('pcc_type'),
                        'created_by' => $this->session->userdata('user_id'),
                        'pos_paid' => $pos_paid,
                        'pos_balance' => $pos_balance,
                        'type' => 'returned',
                        'gc_balance' => $gc_balance,
                    );
                } else if ($this->input->post('paid_by') == 'credit_note') {
                    $cc_no = $this->input->post('credit_card_no');
                    $payment = array(
                        'date' => $date,
                        'reference_no' => $pay_ref,
                        'amount' => (0 - $this->input->post('amount-paid')),
                        'paid_by' => $this->input->post('paid_by'),
                        'cheque_no' => $this->input->post('cheque_no'),
                        'cc_no' => $cc_no,
                        'cc_holder' => $cd_balance,
                        'cc_month' => $this->input->post('pcc_month'),
                        'cc_year' => $this->input->post('pcc_year'),
                        'cc_type' => $this->input->post('pcc_type'),
                        'created_by' => $this->session->userdata('user_id'),
                        'pos_paid' => $pos_paid,
                        'pos_balance' => $pos_balance,
                        'type' => 'returned',
                        'gc_balance' => $cd_balance,
                    );
                } else {
                    $cc_no = $this->input->post('pcc_no');
                    $payment = array(
                        'date' => $date,
                        'reference_no' => $pay_ref,
                        'amount' => (0 - $this->input->post('amount-paid')),
                        'paid_by' => $this->input->post('paid_by'),
                        'cheque_no' => $this->input->post('cheque_no'),
                        'cc_no' => $cc_no,
                        'cc_holder' => $this->input->post('pcc_holder'),
                        'cc_month' => $this->input->post('pcc_month'),
                        'cc_year' => $this->input->post('pcc_year'),
                        'cc_type' => $this->input->post('pcc_type'),
                        'created_by' => $this->session->userdata('user_id'),
                        'pos_paid' => $pos_paid,
                        'pos_balance' => $pos_balance,
                        'type' => 'returned',
                    );
                }


                $data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
            } else {
                $payment = array();
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products, $si_return, $payment);
        }
        
        $extrasPara = array('sale_action' => 'sale_return', 'syncQuantity' => $syncQuantity, 'order_id' => $id);

        if ($this->form_validation->run() == true && $return_sale_id = $this->sales_model->addSale($data, $products, $payment, $si_return, $extrasPara)) {
            $this->session->set_flashdata('message', lang("return_sale_added"));
            if ($this->input->post('paid_by') == 'credit_note') {
                /**
                 * Gift Card
                 **/
                $card_no = mt_rand(1000, 9999);
                $expiry_Date = date("Y-m-d", strtotime("+3 month"));
                $gift_amount = $payment['amount'];
                $giftcard_field = array(
                    'date' => date('Y-m-d H:i:s'),
                    'card_no' => $this->input->post('credit_card_no'),
                    'value' => abs($gift_amount),
                    'customer_id' => $data['customer_id'],
                    'customer' => $data['customer'],
                    'balance' => abs($gift_amount),
                    'expiry' => $expiry_Date,
                    'created_by' => $this->session->userdata('user_id'),
                );
                $gift_id = $this->sales_model->CreateCreditNote($giftcard_field);
                if ($gift_id) {
                    $this->session->set_flashdata('giftcard_id', $gift_id);
                }
                /**
                 * End Gift Card
                 **/
            }else if($this->input->post('paid_by') == 'deposit'){
                
                $refund_amount = $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0;
                $depositFilds = array(
                    'date' => date('Y-m-d H:i:s'),
                    'company_id' => $data['customer_id'],
                    'amount'        => abs($refund_amount),
                    'paid_by'       => $this->input->post('paid_by_deposit'),
                    'note'          => 'Sale Return',
                    'created_by' => $this->session->userdata('user_id'),
                );
                              
                if ($deposit_id = $this->sales_model->addDeposit($depositFilds)) {
                    $this->load->model('companies_model');
                    
                    if (!isset($desposit)) {
                        $desposit = $this->site->customerDepositAmt($data['customer_id']);
                    }
                    if (!isset($deposit_balance)) {
                        $deposit_balance = $desposit + abs($refund_amount);
                    }
                    
                    $depositLog = [
                        "customer_id" => $data['customer_id'],
                        "date" => $depositFilds['date'],
                        "descriptions" => "Sale Return - Deposit Reversal for Sale ID: " . $return_sale_id,
                        "amount" => $depositFilds['amount'],
                        "cr_dr" => 'CR',
                        "opening_balance" => ((bool) $desposit ? $desposit : 0),
                        "closing_balance" => $deposit_balance,
                        "created_by" => $this->session->userdata('user_id'),
                        "transaction_details" => json_encode(["table_name" => "sma_deposits", "where" => ["id" => $deposit_id]])
                    ];
                    
                    $this->companies_model->set_customer_wallet_log($depositLog);
                }
            }
            


            /* ------------------------- Revert reward Point on  return---------------------------- */
            $sale = $this->sales_model->getInvoiceByID($id);
            $company = $this->site->getCompanyByID($sale->customer_id);

            $points = floor(($sale->grand_total / $this->Settings->each_spent) * $this->Settings->ca_point);
            //  echo  "Points =  $points <br>";
            $_points = floor((($sale->grand_total + $sale->return_sale_total) / $this->Settings->each_spent) * $this->Settings->ca_point);
            $return_point = 0;
            //  echo  "Points after return = $_points<br>";
            if ($points > $_points && $_points != 0):
                $total_points = $company->award_points - ($points - $_points);
                $this->db->update('companies', array('award_points' => $total_points), array('id' => $sale->customer_id));
                $return_point = ($points - $_points) * (-1);
            elseif ($_points == 0) :
                $total_points = $company->award_points - ($points);
                $return_point = $points * (-1);
                $this->db->update('companies', array('award_points' => $total_points), array('id' => $sale->customer_id));
            endif;

            $ci = get_instance();

            $order_pt = floor(($this->data['inv']->grand_total / $Settings->each_spent) * $Settings->ca_point);
            // $data =array();
            // $data['customer_id'] =  $company->phone ; 
            // $data['merchant_id'] =  $ci->config->item('merchant_phone');  
            // $data['points']      =  $return_point  ; 
            // $data['order_id']    =  $sale->id ; 
            // $data['remark']      =  'Order ID '.$sale_id.' point achived'. $return_point  ;
            // $url = 'http://simplypos.co.in/api/v1/customer/merchant/transaction/reward';
            // $res = $this->post_to_url($url, $data) ;
            /* ------------------------- Revert reward Point on  return---------------------------- */
            $inv = $this->sales_model->getInvoiceByID($id);
            if ($saleTypeInput != '') {
                redirect('sales/all_sale_lists');
            } else {
                if ($inv->eshop_sale == 1) {
                    redirect('eshop_sales/sales');
                } elseif ($inv->offline_sale == 1) {
                    redirect('offline/sales');
                } elseif ($inv->pos == 1) {
                    redirect('pos/sales');
                } elseif ($inv->up_sales == 1) {
                    redirect('pos/up_sales');
                } else {
                    redirect('sales');
                }
            }

            // redirect("sales");
            //redirect($_SERVER["HTTP_REFERER"]);
        } 
        
        else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $sale;

            if ($this->data['inv']->sale_status == 'returned') {
                $this->session->set_flashdata('error', lang("sale_already_returned"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->data['inv']->sale_status != 'completed') {
                $this->session->set_flashdata('error', lang("sale_status_x_competed"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', lang("sale_x_return_older_than_x_days"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }

            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            $payment = $this->sales_model->getPaymentsSale($id);

            krsort($inv_items);
            $pos_settings = $this->site->get_pos_setting();
            $item_order = isset($pos_settings->item_order) ? (int)$pos_settings->item_order : 0;
            $order_multiplier = ($item_order == 0) ? -1 : 1;
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $ReturnQty = 0;
                $ReturnSaleRow = $this->sales_model->getAllReturnInvoiceItemByItemID($item->id);
                if (!empty($ReturnSaleRow)) {
                    foreach ($ReturnSaleRow as $keys => $val) {
                        $ReturnQty = $ReturnQty + $val['quantity'];
                    }
                }
                                
                $UnitQty = $item->quantity + $ReturnQty;
                if ($UnitQty != 0) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                        $row->tax_method = 0;
                        $row->quantity = 0;
                    } else {
                        unset($row->cost, $row->weight, $row->article_code, $row->alert_quantity,  $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        unset($row->supplier3price, $row->is_featured, $row->divisionid, $row->food_type_id, $row->updated_at, $row->ratings_avarage, $row->ratings_count, $row->comments_count, $row->in_eshop, $row->is_active );
                    }
                    $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->sale_item_id      = $item->id;
                    $row->id                = $item->product_id;                    
                    $row->code              = $item->product_code;
                    $row->name              = $item->product_name;
                    $row->type              = $item->product_type;
                    $row->warehouse         = $item->warehouse_id;
                    $row->batch_number      = $item->batch_number;
                    $row->base_quantity     = 0; //$item->unit_quantity;
                    $row->base_unit         = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price   = $row->price ? $row->price : $item->unit_price;
                    $row->unit              = $item->product_unit_id;
                    $row->qty               = $item->quantity;
                    $row->oqty              = $item->quantity + $ReturnQty;
                    $row->discount          = $item->discount ? $item->discount : '0';
                    $qty_multiplier         = ($item->packing_size > 0 ? $item->packing_size : 1) * $item->quantity;
                    $row->price             = $this->sma->formatDecimal($item->net_unit_price + ($item->item_discount / $qty_multiplier));
                    $unit_price             = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $qty_multiplier) + $this->sma->formatDecimal($item->item_tax / $qty_multiplier) : $item->unit_price + ($item->item_discount / $qty_multiplier);
                    if($row->tax_method == 1){
                        $unit_price             = $this->sma->formatDecimal($item->unit_price  - ($item->item_tax / $qty_multiplier) - ($item->surcharge / $qty_multiplier)) + ($item->item_discount / $qty_multiplier);
                    }
                    $row->unit_price        = $unit_price;
                    $row->net_unit_price    = $this->sma->formatDecimal($unit_price - ($item->item_tax / $qty_multiplier) - ($item->surcharge / $qty_multiplier) - ($item->item_discount / $qty_multiplier));
                    if($row->tax_method == 1){
                        $row->net_unit_price    = $this->sma->formatDecimal($unit_price - ($item->item_discount / $qty_multiplier));
                    }
                    $row->real_unit_price   = ((int)$item->real_unit_price > 0) ? $item->real_unit_price : $row->net_unit_price;
                    $row->tax_rate          = $item->tax_rate_id;
                    $row->serial            = $item->serial_no;
                    $row->option            = $item->option_id;
                    $row->rounding          = $item->rounding;
                    $row->cf1               = $item->cf1;                  
                    $row->unit_weight       = ($item->item_weight / $item->quantity);
                    $row->unit_quantity     = $item->unit_quantity;
                    $row->item_tax          = $item->item_tax;
                    $row->packing_size      = $item->packing_size;

                    $row->option_color      = $item->shade_id;
                    $row->seller_id         = $item->seller_id;
                    $row->seller            = $item->seller;
                    if($row->storage_type == 'loose'){
                        $options = $this->sales_model->getLooseProductOptionsByAttr($row->id, $item->warehouse_id, 1);
                    }else{
                        $options = $this->sales_model->getProductOptionsByAttr($row->id, $item->warehouse_id, 1);
                    }
                    $options_color = $this->sales_model->getProductoptioncolor($item->shade_id);
                    if ($options_color) {
                        $opt_color = current($options_color);
                        if (!empty($opt_color)) {
                            $option_color_id = $opt_color->id;
                            $option_color_name = $opt_color->name;
                            $row->option_color_name = $option_color_name;
                        }
                    } else {
                        $opt_color = json_decode('{}');
                    }
                    $units = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    
                    $row_id = $row->id . $row->option;
                    
                    $ri = $this->Settings->item_addition ? $row_id : $c;
                                      
                    $pr[$ri] = array('id' => $c, 'item_id' => $row_id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options, 'options_color' => $options_color, 'order' => $order_multiplier * (int) $item->id);
                    $c++;
                }
            }
            
            $this->data['sale_type'] = $saleTypeInput ? $saleTypeInput : $saleType;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['payment_ref'] = '';
            $this->data['reference'] = ''; // $this->site->getReference('re');
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['payment'] = $payment->paid_by;
            $this->data['cc_no'] = $payment->cc_no;
            
            // Load sales persons for product-wise sales person functionality
            $this->data['salesperson_details'] = $this->site->getAllSalesPersons();
            // POS settings (both keys used in different views)
            $this->data['pos_settings'] = $this->data['Pos_settings'] = $this->site->get_pos_setting();
            
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('return_sale')));
            $meta = array('page_title' => lang('return_sale'), 'bc' => $bc);
            $this->page_construct('sales/return_sale', $meta, $this->data);
        }
    }

    public function delete($id = null) {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->sales_model->getInvoiceByID($id);
        
       
        if ($inv->sale_status == 'returned') {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            $this->sma->md();
        }
        if ($inv->sale_status == 'completed') {
            $this->session->set_flashdata('error', "This action can not be performed for sale completed record");
            $this->sma->md();
        }
        
        $this->sma->storeDeletedData('sales', 'id', $id);
        if ($this->sales_model->deleteSale($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("sale_deleted");
                die();
            }
            $this->session->set_flashdata('message', lang('sale_deleted'));
            redirect('welcome');
        }
    }

    public function delete_return($id = null) {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("return_sale_deleted");
                die();
            }
            $this->session->set_flashdata('message', lang('return_sale_deleted'));
            redirect('welcome');
        }
    }

  public function sale_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sma->storeDeletedData('sales', 'id', $id);
                        $this->sales_model->deleteSale($id);
                    }
                    $this->session->set_flashdata('message', lang("sales_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'combine_invoice') {

                    $html = $this->combine_invoice_pdf($_POST['val']);
                 // Code for Json file
                }elseif ($this->input->post('form_action') == 'export_to_json') {
                    // Code for Json file

                    $indian_all_states  = array (
                        'AP' => "37",//'Andhra Pradesh',
                        'AR' => "12",//'Arunachal Pradesh',
                        'AS' => "18",//'Assam',
                        'BR' => "10",//'Bihar',
                        'CT' => "22",//'Chhattisgarh',
                        'GA' => "30",//'Goa',
                        'GJ' => "24",//'Gujarat',
                        'HR' => "9",//'Haryana',
                        'HP' => "2",//'Himachal Pradesh',
                        'JK' => "1", //'Jammu & Kashmir',
                        'JH' => "20",//'Jharkhand',
                        'KA' => "29",//'Karnataka',
                        'KL' => "32",//'Kerala',
                        'MP' => "23",//'Madhya Pradesh',
                        'MH' => "27",//"27",
                        'MN' => "14",//'Manipur',
                        'ML' => "17",//'Meghalaya',
                        'MZ' => "15",//'Mizoram',
                        'NL' => "13",//'Nagaland',
                        'OR' => "21",//'Odisha',
                        'PB' => "3",//'Punjab',
                        'RJ' => "8",//'Rajasthan',
                        'SK' => "11",//'Sikkim',
                        'TN' => "33",//'Tamil Nadu',
                        'TR' => "16",//'Tripura',
                        'UK' => "5",//'Uttarakhand',
                        'UP' => "9",//'Uttar Pradesh',
                        'WB' => "19",//'West Bengal',
                        'AN' => "35",//'Andaman & Nicobar',
                        'CH' => "4",//'Chandigarh',
                        'DN' => "26",//'Dadra and Nagar Haveli',
                        'DD' => "25",//'Daman & Diu',
                        'DL' => "7",//'Delhi',
                        'LD' => "31",//'Lakshadweep',
                        'PY' => "34",//'Puducherry',
                    );
                    

                    $TranDtls[] =array();
                    $DocDtls[] =array();
                    $SellerDtls[] =array();
                    $BuyerDtls[] =array();
                    $ValDtls[] =array();
                    $ItemList[] =array();
                    
                    $itr=0;

                    $total_inv = sizeof($_POST['val']);
                    $fp = fopen('./Json_data.json', 'w');
                    fwrite($fp,"[");
                    $flag=0;
                   
                    foreach ($_POST['val'] as $id) {
                        
                        $sales = $this->sales_model->getAllInvoiceItems1($id);
                        $Buyer = $this->sales_model->getBuyer($sales[0]->customer_id);
                        $Seller = $this->sales_model->getSeller($sales[0]->biller_id);
                       
                        $totalItmes = $this->sales_model->getItemsByInv($id);
                       

                        $TranDtls=array(
                            "TaxSch"=>"GST",
                            "SupTyp"=>"B2B",
                            "IgstOnIntra"=>"N",
                            "RegRev"=>null,
                            "EcmGstin"=>null
                        );

                        $str=substr($sales[0]->s_date,0,10);
                        $a = explode("-",$str);
                        
                        $a = array_reverse($a);
                        // echo "<br>";
                        $date = implode("/", $a);

                        if($sales[0]->sale_status== "returned")
                        {
                            $sale_status = "DBN";
                        }
                        else{
                            $sale_status = "INV";
                        }
                        $DocDtls = array(
                            "Typ"=>$sale_status,
                            "No"=>$sales[0]->invoice_no,
                            "Dt"=>$date        // substr($sales[0]->s_date,0,10)
                            );
                           
                        //ALTER TABLE `sma_state_master` ADD code_number varchar(11);

                        $pin1=(int)$Seller->postal_code;

                        foreach($indian_all_states as $key => $value)
                        {
                            if($key == $Seller->state_code )
                            {
                                $Stcd1 = $value;
                            }
                        }
                        $SellerDtls=array(
                            "Gstin"=>$Seller->gstn_no,
                            "LglNm"=>$Seller->cf6,
                            // "TrdNm"=>$Seller->company,  // Remaining
                            "Addr1"=>$Seller->address,
                            "Addr2"=>null,
                            "Loc"=>$Seller->city,
                            "Pin"=>$pin1,
                            "Stcd"=>$Stcd1,
                            "Ph"=>null,
                            "Em"=>null
                        );
                       
                        foreach($indian_all_states as $key => $value)
                        {
                            if($key == $Buyer->state_code )
                            {
                                $Stcd2 = $value;
                                // exit;
                            }
                        }
                        $pin2=(int)$Buyer->postal_code;
                        $BuyerDtls=array(
                            "Gstin"=>$Buyer->gstn_no,
                            "LglNm"=>$Buyer->name,
                            // "TrdNm"=> null, // Remaining
                            "Addr1"=>$Buyer->address,
                            "Addr2"=>null,
                            "Loc"=>$Buyer->city,
                            "Pin"=>$pin2,
                            "Stcd"=>$Stcd2,
                            "Pos" =>$Stcd2,
                            "Ph"=>null,
                            "Em"=>null
                        );
                       
                        $ValDtls1=round($sales[0]->s_total,2);
                        $ValDtls2=round($sales[0]->s_cgst,2);
                        $ValDtls3=round($sales[0]->s_sgst,2);
                        $ValDtls4=round($sales[0]->s_igst,2);
                        $ValDtls5=round($sales[0]->s_grand_total,2);

                        $ValDtls=array(
                            "AssVal"=> abs($ValDtls1),
                            "IgstVal"=>abs($ValDtls4),
                            "CgstVal"=> abs($ValDtls2),
                            "SgstVal"=> abs($ValDtls3),
                            "TotInvVal"=>abs($ValDtls5)

                        );
                       
                        $itm=0;
                        foreach($totalItmes as $item)
                        {
                            $Unit = $this->sales_model->getProductUnit($item->product_id);
                           
                            $itm1 = $itm + 1;
                            $SlNo = (string)$itm1;

                            if($item->cgst == 0 || $item->cgst == 0)
                            {
                                $GstRt=round($item->gst_rate,2);
                            }
                            else
                            {
                                $GstRt=(round($item->gst_rate,2)*2);
                            }
                            $Qty=round($item->quantity,2);
                            $UnitPrice=round($item->net_unit_price,2);
                            if($item->tax_method == 0){
                                $item_netunit =  ($item->real_unit_price* $Qty) ;
                                $TotalAmt =((($item_netunit - $item->item_discount))); 
                                $TotalAmount = ($TotalAmt * 100);
                                $TotalTax = (100 + $item->tax);
                                // $taxAmount = (($TotalAmt * $sale->tax)/ $TotalTax);                  
                                $AssAmt = round($TotalAmount/$TotalTax,2);            
                            }else{
                                $AssAmt=round((($item->real_unit_price * $item->quantity) - $item->item_discount),2);
                            }
                          
                            // $GstRt=round($item->gst_rate,2);
                            $CgstAmt=round($item->cgst,2);
                            $SgstAmt=round($item->sgst,2);
                            $IgstAmt=round($item->igst,2);
                         
                            $ItemList[$itm]=array(
                                "SlNo"=> "$SlNo",  
                                "Prdnm"=> "$item->product_name", 
                                "PrdDesc"=> "$item->note $item->serial_no",
                                "IsServc"=> "N",
                                "HsnCd"=> $item->hsn_code,
                                "Qty"=> abs($Qty),
                                "Unit"=>strtoupper($Unit),    
                                "UnitPrice"=> abs($item->real_unit_price), 
                                // "TotAmt"=>abs(round(($item->net_unit_price * $item->quantity),2)),
                                "TotAmt"=>abs($item->real_unit_price * $Qty), 
                                "Discount"=> abs($item->item_discount), 
                                "AssAmt"=> abs($AssAmt),
                                "GstRt"=> abs($GstRt),
                                "IgstAmt"=> abs($IgstAmt),
                                "CgstAmt"=> abs($CgstAmt),
                                "SgstAmt"=> abs($SgstAmt),
                                "TotItemVal"=>abs(round(($AssAmt + $item->item_tax),2))
                            );   
                          
                            $itm++;
                        }
                   
                    $response['Version'] = "1.1";
                    $response['TranDtls'] = $TranDtls;
                    $response['DocDtls'] = $DocDtls;
                    $response['SellerDtls'] = $SellerDtls;
                    $response['BuyerDtls'] = $BuyerDtls;
                    $response['ValDtls'] = $ValDtls;
                    $response['ItemList'] = $ItemList;
                    if($flag == 0)
                    {
                        $fp = fopen('./Json_data.json', 'a');
                        fwrite($fp, json_encode($response,JSON_PRETTY_PRINT));
                    }
                    else
                    {
                        $fp = fopen('./Json_data.json', 'a');
                    fwrite($fp, ",".json_encode($response,JSON_PRETTY_PRINT));
                    }
                    
                    $itr++;
                    $flag = 1;  
                    } // end foreach
                        
                    $fp = fopen('./Json_data.json', 'a');
                    fwrite($fp,"]");
                    $file = 'Json_data.json';
                   
                    json_download($file);
                 // Code for Json file    
                } elseif ( $this->input->post('form_action') == 'export_invoice_to_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,), 'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000'))));

                    $this->excel->getActiveSheet()->getStyle("A1:Q1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:Q1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Sales');
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));


                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('Co_Name'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('Reference No'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('Invoice_No'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('Invoice_Date'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('Barcode'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('Category'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('Product'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('Article Code')); //Style_Code
                    $this->excel->getActiveSheet()->SetCellValue('I2', lang('Variant'));
                    $this->excel->getActiveSheet()->SetCellValue('J2', lang('Unit'));
                    $this->excel->getActiveSheet()->SetCellValue('K2', lang('Brand'));
                    $this->excel->getActiveSheet()->SetCellValue('L2', lang('Quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('M2', lang('MRP'));
                    $this->excel->getActiveSheet()->SetCellValue('N2', lang('WSP'));
                    $this->excel->getActiveSheet()->SetCellValue('O2', lang('Consignee_Name'));
                    $this->excel->getActiveSheet()->SetCellValue('P2', lang('Consignee_City'));
                    $this->excel->getActiveSheet()->SetCellValue('Q2', lang('HSN_Code'));



                    $row = 3;
                    $company = $this->sales_model->getCompanies();
                    foreach ($_POST['val'] as $id) {
                        $saleId = $this->sales_model->getInvoiceByID($id);
                        $delivery = $this->sales_model->getDeliveryBySaleID($id);
                        $sales = $this->sales_model->getAllInvoiceItems($id);
                        $customer_details = $this->site->getCompanyByID($saleId->customer_id);

                        foreach ($sales as $sale) {
                            $options_color = $this->site->getProductOptionsByShapeId($sale->option_id, $sale->product_id);
                            $scategory = $this->sales_model->getCategoryByProductId($sale->product_id);
                            $subcategory = $this->sales_model->getSubCategories($scategory->cid, $scategory->pid);
                            $brand = $this->sales_model->getBrandByProductId($sale->product_id);

                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $saleId->biller); //Biller_Name
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $saleId->reference_no); //PO_NO
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $saleId->id); //Invoice_No
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $saleId->date); //INVOICE_DATE
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->product_code); //barcode
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $scategory->Catname);
                            $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale->product_name);
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sale->article_code); // style code

                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sale->variant); //SIZE


                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $sale->product_unit_code);

                            $this->excel->getActiveSheet()->SetCellValue('K' . $row, $brand->brandname);
                            $this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->sma->formatQuantity($sale->quantity));
                            $this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->sma->formatMoney($sale->mrp));
                            $this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->sma->formatMoney($sale->unit_price));
                            $this->excel->getActiveSheet()->SetCellValue('O' . $row, $saleId->customer);
                          
                            $this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer_details->city);

                           
                            $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $sale->hsn_code);
                            $row++;
                        }
                        //$row++;
                    }
                    // Center align Quantity column (L)
                    $lastRow = $row - 1;

                    /* TEXT columns → LEFT */
                    $this->excel->getActiveSheet()->getStyle("A3:K{$lastRow}")
                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                    $this->excel->getActiveSheet()->getStyle("O3:Q{$lastRow}")
                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                    /* QUANTITY → CENTER (already correct, keep it) */
                    $this->excel->getActiveSheet()->getStyle("L3:L{$lastRow}")
                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    /* MONEY columns → RIGHT */
                    $this->excel->getActiveSheet()->getStyle("M3:N{$lastRow}")
                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_items_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
                    if ( $this->input->post('form_action') == 'export_invoice_to_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }elseif($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf' ){
                   $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $titleStyle = array(
                        'font' => array(
                            'bold' => true,
                            'size' => 16,
                            'color' => array('rgb' => 'CC0000')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        )
                    );

                    $this->excel->getActiveSheet()->getStyle("A1:I1")->applyFromArray($titleStyle);
                    $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);


                    $this->excel->getActiveSheet()->mergeCells('A1:I1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Sales');
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));

                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('invoice_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('payment_status'));
                    $this->excel->getActiveSheet()->SetCellValue('I2', lang('Delivery Status'));

                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_model->getInvoiceByID($id);
                        $delivery = $this->sales_model->getDeliveryBySaleID($id);

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->id);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->customer);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sale->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($sale->paid));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($sale->payment_status));
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($sale->delivery_status) . ' ' . lang($delivery->status));
                        $row++;
                    }
                   
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }  
                
            } else {
                $this->session->set_flashdata('error', lang("no_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function challan_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete', true);
                    foreach ($_POST['val'] as $id) {
                        $this->sma->storeDeletedData('delivery_challan', 'id', $id);
                        $this->challan_model->deleteChallan($id);
                    }
                    $this->session->set_flashdata('message', lang("challans_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_challan_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Challans'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('payment_status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $challan = $this->challan_model->getChallanByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($challan->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $challan->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $challan->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $challan->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatMoney($challan->grand_total));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatMoney($challan->paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatMoney($challan->grand_total - $challan->paid));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($challan->payment_status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'challans_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_challan_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

 
    public function deliveries() {
        $this->sma->checkPermissions();

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->page_construct('sales/deliveries', $meta, $this->data);
    }

    public function getDeliveries() {
        $this->sma->checkPermissions('deliveries');

        $detail_link = anchor('sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $pdf_link = anchor('sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_delivery/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
        // ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
        $this->datatables
                ->select("deliveries.id as id, date, do_reference_no, invoice_no, customer, customer_phone, address,city, state,pincode,delivered_by,delivered_person_phone , status, delivery_type ,  attachment ")
                ->from('deliveries');
        if ($this->session->userdata('view_right') == '0') {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }

        $this->db->group_by('deliveries.id');
        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

    public function pdf_delivery($id = null, $view = null, $save_bufffer = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);

        $this->data['delivery'] = $deli;
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user'] = $this->site->getUser($deli->created_by);

        $name = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_delivery', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function view_delivery($id = null) {
        $this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        if (!$sale) {
            $this->session->set_flashdata('error', lang('sale_not_found'));
            $this->sma->md();
        }
        $this->data['sale'] = $sale;
        $this->data['delivery'] = $deli;
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user'] = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang("delivery_order");

        $this->load->view($this->theme . 'sales/view_delivery', $this->data);
    }

    public function add_delivery($id = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        $this->data['inv_items'] = $this->sales_model->getAllInvoiceItems($id);
        foreach ($this->data['inv_items'] as $items) {
            if (!empty($items->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($items->shade_id);
                $items->shade_name= $colors->name;
            }
        }

        if ($sale->sale_status != 'completed') {
            $this->session->set_flashdata('error', lang('status_is_x_completed'));
            $this->sma->md();
        }

        if ($delivery = $this->sales_model->getDeliveryBySaleID($id)) {
            $this->edit_delivery($delivery->id);
        } else {

            $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');

            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                    if (strlen($date) == 16) {
                        $date .= ':' . date('s');
                    }
                } else {
                    $date = date('Y-m-d H:i:s');
                }

                $exp_delBy = explode("~", $this->input->post('delivered_by'));
                $dlDetails = array(
                    'date' => $date,
                    'sale_id' => $this->input->post('sale_id'),
                    'do_reference_no' => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer' => $this->input->post('customer'),
                    'address' => $this->input->post('address'),
                    'status' => $this->input->post('status'),
                    'delivered_by' => $exp_delBy[0],
                    'received_by' => $this->input->post('received_by'),
                    'note' => $this->sma->clear_tags($this->input->post('note')),
                    'created_by' => $this->session->userdata('user_id'),
                    'invoice_no' => $this->input->post('invoice_no'),
                    'customer_phone' => $this->input->post('customer_phone'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'pincode' => $this->input->post('pincode'),
                    'delivered_person_phone' => $this->input->post('delivered_person_phone'),
                );

                /////////////////////////////////Partial Delivery Code Start//////////////////////////////////////////
                $quantity = $this->input->post('quantity');
                $delivered = $this->input->post('delivered_quantity');
                $saleDeliveryStatus = 'overall';

                if ($this->input->post('delivery_status') == 'partial') {

                    foreach ($quantity as $itm_id => $qty) {
                        $pending_qty = $qty - $delivered[$itm_id];
                        $status = ($delivered[$itm_id]) ? (($pending_qty) ? 'partial' : 'delivered') : 'pending';
                        $updateItemsDelivery[$itm_id] = array(
                            'pending_quantity' => $pending_qty,
                            'delivered_quantity' => $delivered[$itm_id],
                            'delivery_status' => $status,
                        );

                        if ($saleDeliveryStatus == 'overall') {
                            $saleDeliveryStatus = ($pending_qty) ? 'partial' : 'overall';
                        }
                    }//end foreach.
                } else if ($this->input->post('delivery_status') == 'overall') {
                    foreach ($quantity as $itm_id => $qty) {
                        $updateItemsDelivery[$itm_id] = array(
                            'pending_quantity' => 0,
                            'delivered_quantity' => $qty,
                            'delivery_status' => 'delivered',
                        );
                    }//end foreach.
                    $saleDeliveryStatus = 'overall';
                }//end else.
                ///////////////////////////////////////////Partial Delivery Code End//////////////////////////////////////////////////////// 

                $dlDetails['delivery_type'] = $saleDeliveryStatus;

                if ($_FILES['document']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['attachment'] = $photo;
                }
            } elseif ($this->input->post('add_delivery')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->form_validation->run() == true && $this->sales_model->addDelivery($dlDetails)) {

                //Manage/Update Partial delivery status
                $this->sales_model->updateSalesDeliveryStatus($this->input->post('sale_id'), $updateItemsDelivery, $saleDeliveryStatus);

                $this->session->set_flashdata('message', lang("delivery_added"));
                // redirect("sales/deliveries");
                redirect($_SERVER["HTTP_REFERER"]);
            } else {

                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
                $this->data['delivery_person'] = $this->sales_model->getDelivaryPerson();
                $this->data['inv'] = $sale;

                $this->data['do_reference_no'] = ''; //$this->site->getReference('do');
                if ($sale->eshop_sale == 1) {
                    $this->load->model('eshop_model');
                    $billing_details = $this->eshop_model->getOrderDetails(array('sale_id' => $sale->id));
                    $this->data['shipping_addr'] = 'Name:' . $billing_details[0]['shipping_name'] .
                            '   Address:' . $billing_details[0]['shipping_addr'] .
                            '   Email:' . $billing_details[0]['shipping_email'] .
                            '   Phone:' . $billing_details[0]['shipping_phone'];
                }
                $this->data['modal_js'] = $this->site->modal_js();

                $this->load->view($this->theme . 'sales/add_delivery', $this->data);
            }
        }
    }

    public function edit_delivery($id = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }


        $this->form_validation->set_rules('do_reference_no', lang("do_reference_no"), 'required');
        $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('address', lang("address"), 'required');

        if ($this->form_validation->run() == true) {
            $exp_delBy = explode("~", $this->input->post('delivered_by'));
            $dlDetails = array(
                'sale_id' => $this->input->post('sale_id'),
                'do_reference_no' => $this->input->post('do_reference_no'),
                'sale_reference_no' => $this->input->post('sale_reference_no'),
                'customer' => $this->input->post('customer'),
                'address' => $this->input->post('address'),
                'status' => $this->input->post('status'),
                'delivered_by' => $exp_delBy[0],
                'received_by' => $this->input->post('received_by'),
                'note' => $this->sma->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'invoice_no' => $this->input->post('invoice_no'),
                'customer_phone' => $this->input->post('customer_phone'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'pincode' => $this->input->post('pincode'),
                'delivered_person_phone' => $this->input->post('delivered_person_phone'),
            );

            /////////////////////////////////Partial Delivery Code Start//////////////////////////////////////////
            $quantity = $this->input->post('quantity');
            $delivered = $this->input->post('delivered_quantity');
            $saleDeliveryStatus = 'overall';

            if ($this->input->post('delivery_status') == 'partial') {

                foreach ($quantity as $itm_id => $qty) {
                    $pending_qty = $qty - $delivered[$itm_id];
                    $status = ($delivered[$itm_id]) ? (($pending_qty) ? 'partial' : 'delivered') : 'pending';
                    $updateItemsDelivery[$itm_id] = array(
                        'pending_quantity' => $pending_qty,
                        'delivered_quantity' => $delivered[$itm_id],
                        'delivery_status' => $status,
                    );

                    if ($saleDeliveryStatus == 'overall') {
                        $saleDeliveryStatus = ($pending_qty == 0) ? 'overall' : 'partial';
                    }
                }//end foreach.
            } else if ($this->input->post('delivery_status') == 'overall') {
                foreach ($quantity as $itm_id => $qty) {
                    $updateItemsDelivery[$itm_id] = array(
                        'pending_quantity' => 0,
                        'delivered_quantity' => $qty,
                        'delivery_status' => 'delivered',
                    );
                }//end foreach.
                $saleDeliveryStatus = 'overall';
            } else {
                $saleDeliveryStatus = 'pending';
            }//end else.
            ///////////////////////////////////////////Partial Delivery Code End//////////////////////////////////////////////////////// 

            $dlDetails['delivery_type'] = $saleDeliveryStatus;

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
                $dlDetails['date'] = $date;
            }
        } elseif ($this->input->post('edit_delivery')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails)) {

            //Manage/Update Partial delivery status
            $this->sales_model->updateSalesDeliveryStatus($this->input->post('sale_id'), $updateItemsDelivery, $saleDeliveryStatus);

            $this->session->set_flashdata('message', lang("delivery_updated"));
            redirect("sales/deliveries");
        } else {
            $delivery = $this->sales_model->getDeliveryByID($id);
            $this->data['sale'] = $this->sales_model->getInvoiceByID($delivery->sale_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['delivery'] = $delivery;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['delivery_person'] = $this->sales_model->getDelivaryPerson();

            $this->data['inv_items'] = $this->sales_model->getAllInvoiceItems($delivery->sale_id);
            $this->load->view($this->theme . 'sales/edit_delivery', $this->data);
        }
    }

    public function delete_delivery($id = null) {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteDelivery($id)) {
            echo lang("delivery_deleted");
        }
    }

    public function delivery_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang("deliveries_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('delivered'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('pending'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->sales_model->getDeliveryByID($id);
                        $items = $this->sales_model->getDeliveryItemBySaleID($delivery->sale_id);
                        $items->delivered = ($items->delivered && $delivery->delivery_type != '') ? $items->delivered : $items->quantity;

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->sale_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, strip_tags($delivery->address));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($delivery->status));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($delivery->delivery_type));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, number_format($items->quantity, 0));
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, number_format($items->delivered, 0));
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, number_format(($items->quantity - $items->delivered), 0));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_delivery_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
 
    public function payments($id = null) {
        $this->sma->checkPermissions(false, true);
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id);
        $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function payment_note($id = null) {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->sales_model->getPaymentByID($id);
        if ($payment->sale_id) {
            $inv = $this->sales_model->getInvoiceByID($payment->sale_id);
        } elseif ($payment->challan_id) {
            $this->load->model('challan_model');
            $inv = $this->challan_model->getChallanByID($payment->challan_id);
        }
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }

    public function add_payment($id = null) {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang("sale_already_paid"));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'transaction_id' => $this->input->post('transaction_id'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->sma->md();
            }
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    public function edit_payment($id = null) {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
        $payment_sale = $this->sales_model->getPaymentByID($id);

        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                if ($this->input->post('sale_id')) {
                    $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                } elseif ($this->input->post('challan_id')) {
                    $this->load->model('challan_model');
                    $sale = $this->challan_model->getChallanByID($this->input->post('challan_id'));
                }
                $customer_id = $sale->customer_id;
                $amount = $this->input->post('amount-paid') - $payment->amount;
                if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }
            $payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id') ? $this->input->post('sale_id') : NULL,
                'challan_id' => $this->input->post('challan_id') ? $this->input->post('challan_id') : NULL,
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'transaction_id' => $this->input->post('transaction_id'),    
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
            if ($payment_sale->sale_id == 0) {
                $payment['order_id'] = $payment_sale->order_id;
            }
            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            //redirect("sales");
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }

    public function delete_payment($id = null) {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    //  public function suggestions() {
        
    //     $quantity = $this->input->get('quantity', true);
    //     $subtotal = $this->input->get('subtotal', true);
    //     if ($this->input->get('category_id')) {
    //         $category_id = $this->input->get('category_id', true);
    //     }
    //     $term = $this->input->get('term', true);
    //     $bundel_item_code = $this->input->get('bundel_item_code', true); // This product code of bundle items
    //     $bundle_option_id = $this->input->get('bundle_option_id', true); // This is variant id of bundle variant items
    //     $product_Id = $this->input->get('product_Id', true);
     
    //     $warehouse_id = $this->input->get('warehouse_id', true);
    //     $customer_id = $this->input->get('customer_id', true);
    //     $option_note = $this->input->get('option_note', true);
    //     $variant_popup = $this->input->get('variant_popup', true) ? $this->input->get('variant_popup', true) : null;
    //     $Settings = $this->site->get_setting();

    //     // set flag for checking which screen is called for suggestion function in controller
    //     $Sale_flag = $this->input->get('Sale_flag', true); 

    //     if($this->Settings->pos_type == 'restaurant'){
    //       if ($this->input->get('table_id')) {  
    //          $table_id = $this->input->get('table_id', true); 
    //       }
    //     }

    //     if ($this->input->get('batch_no')) {
    //         $batch_no = $this->input->get('batch_no');
    //     }

    //     if (strlen($term) < 3 || !$term) {        
    //         die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
    //     }

    //     $qty_value = explode("-", $term); //Using Barcode - Qty
    //     $product_qty = isset($qty_value[1]) ? $qty_value[1] : 1;

    //     $exp = $qty_value[0] ? explode("_", $qty_value[0]) : ''; 

    //     $analyzed   = $this->sma->analyze_term($qty_value[0]);
    //     $sr         = $analyzed['term'];        
    //     $option_id  = $analyzed['option_id'] ? $analyzed['option_id'] : 0;
    //     $option_color_id  = $analyzed['option_color_id'] ? $analyzed['option_color_id'] : 0;
     
    //     $warehouse      = $warehouse_id ? $this->site->getWarehousesID($warehouse_id) : false;
    //     $customer       = $this->site->getCompanyByID($customer_id);
    //     $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

    //     $saleData = $this->sales_model->getPreviousSaleNo();
    //     $parts = explode("/", $saleData->reference_no);
    //     $right_section = end($parts);
    //     $RefNo = $this->sma->getReturnSaleReferenceNo($right_section);
    //     if(isset($table_id)){
    //        $table_details = $this->sales_model->getTableDetails($table_id); 
    //     }
    //     if ($bundel_item_code) {
    //         $sr = $bundel_item_code;
    //         $option_id = $bundle_option_id; // This is variant id of bundle variant items
    //     }
    //     if (!$this->Owner || !$this->Admin) {
    //         $rows = $this->sales_model->getProductNames($sr, $warehouse_id, 50, 1, $product_Id,$Settings->overselling);
    //     } else {
    //         $rows = $this->sales_model->getProductNames($sr, $warehouse_id, null, null, $product_Id,$Settings->overselling);
    //     }
    //     //$rows->item_note = $item_note;
    //     $wrong_prd_msg = FALSE;
    //     $no_prd_found = FALSE;
    //     //$rows->item_note = $item_note;
    //     if($rows[0]->type != 'combo')
    //     {
    //         if(!$Settings->overselling)
    //     {
    //         if(!$rows)
    //         {
    //              //$this->sma->send_json(array(array('id' => 0, 'label' => lang('wrong_product'), 'value' => $term)));
    //             $wrong_prd_msg = TRUE;
    //         }
    //         else{
    //             $rows = array_filter($rows, function ($row) {
    //             return isset($row->wp_quantity) && (float)$row->wp_quantity > 0;
    //         });
    
    //             if(!$rows)
    //             {
    //                 //$this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
    //                 $no_prd_found = TRUE;
    //             }
    //         }
    //     }
    //     }


       
    //     if ($rows) {
    //         $c = str_replace(".", "", microtime(true));
    //         $r = 0;
    //         foreach ($rows as $row)  {
    //             $product_current_stock = $this->site->getWarehouseStock($warehouse_id, $row->id);
    //             $row->current_stock = $product_current_stock->quantity;
    //              if (($this->pos_settings->active_repeat_customer_discount) && ($this->pos_settings->auto_apply_repeat_customer_discount) && ($customer_id != 1)) {
                   
    //                 $getDiscount = $this->sales_model->getRepeatSalesCheck($customer_id, $row->code, $row->repeat_sale_validity);
                  
    //             if ($getDiscount) {
    //                     $discountP = $getDiscount['discountP'];
    //                     $discountAmt = $getDiscount['discountAmt'];
    //                 }else{
    //                       $discountP = 0;
    //                        $discountAmt = 0;
    //                  }
    //             }

    //             unset($row->cost,$row->details, $row->product_details, $row->barcode_symbology, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
    //             unset($row->alert_quantity, $row->article_code,  $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6,  $row->file, $row->food_type_id, $row->in_eshop, $row->is_featured, $row->purchase_unit, $row->ratings_avarage, $row->ratings_count, $row->supplier3price, $row->track_quantity, $row->updated_at, $row->comments_count );
               
    //             $row->category_id =  $category_id ? $category_id : $row->category_id; // Set category id if passed from pos setting
    //             $category_data = $this->pos_model->getCategoryIdByName($row->category_id); 
    //             $row->category_name =  $category_data->name;
    //             /** Changes according to pos setting Use Product Price Field* */
    //             if (isset($this->pos_settings->use_product_price) && $this->pos_settings->use_product_price == 'mrp') {                    
    //                 $row->price = $row->mrp ? $row->mrp : $row->price;
    //             }                 
            

    //             if(isset($table_details)){
    //               if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $table_details->price_group_id)) {
    //                 $row->price = $pr_group_price->price;
    //               }
    //             }
                
    //             $productbatches = false;  
    //             $option = isset($exp[1]) ? $exp[1] : false; // Using Barcode Scan time 
    //             $row->return_ref_no = $RefNo;   
    //             $unitData = $this->sales_model->getUnitById($row->unit);
    //             $row->unit_lable        = $unitData->name;
    //             $row->quantity_total    = $row->quantity;
    //             $row->item_tax_method   = $row->tax_method;
    //             $row->base_quantity     = $product_qty ? (float)$product_qty : 1;
    //             $row->qty               = $product_qty ? (float)$product_qty : 1;
    //             $row->qty = ($quantity) ? $quantity :  $product_qty;
    //             $subtotal = $this->sma->formatDecimal($subtotal);
    //             $row->unit_quantity     = 1;
    //             if ($discountP) {

    //                 $row->discount = (($discountP) ? $discountP . '%' : (($customer_group->apply_as_discount) ? $customer_group->percent . '%' : '0'));
    //             } else {
    //                $row->discount          = ($customer_group->apply_as_discount)?$customer_group->percent.'%':'0'; 
    //                $customer_group_discount = 0;
    //                $row->customer_group_discount = ($customer_group->percent > 0) ? '1' : '0'; // flag for customer discount apply
    //             }      
    //             $row->discount_on_mrp =  ($row->discount_on_mrp == 'Null' || $row->discount_on_mrp == '') ? "0%" : $row->discount_on_mrp ;

    //             $row->warehouse         = $warehouse_id;                               
    //             // $row->unit_price        = $row->price;                
    //             $row->unit_price        = $row->mrp;  
    //             if($this->Settings->theme == 'newpos' || $this->Settings->theme == 'default'){
    //                 $row->unit_price        = $row->price;  
    //             }          
    //             $row->base_unit_price   = $row->price;
    //             $row->unit_weight       = $row->weight;
    //             $row->option            = 0;
                
    //             $row->sale_loose_products_with_variants   = $this->Settings->sale_loose_products_with_variants;
                
    //             $pis = $this->site->getPurchasedItems($row->id, $warehouse_id); 
    //             $pw_quantity = 0; 
    //             if($pis) {                    
    //                 foreach ($pis as $pi) {
    //                     $pw_quantity += $pi->quantity_balance;
    //                 }                   
    //             }
                
    //             $row->quantity = $pw_quantity;
    //             $option_id = $option_id ? $option_id : false;
    //             // $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariants($row->id, $variant_popup) : false;                
    //             $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariantsWithoutcolor($row->id, $variant_popup, 1) : false;                
    //             // $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariants($row->id) : false;                
    //             // $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductoptionData($row->id) : false;                
    //             $opt = false;
                                
    //             if( $options !== false ) {
                    
    //                 // if($option_id !== false && !empty($options[$option_id])){
    //                 //     $opt = $options[$option_id];
    //                 //     if($opt->product_id !== $row->id){
    //                 //         $option_id = false; 
    //                 //         $opt = false;
    //                 //     }
    //                 // } else {
  
    //                 //     $option_id = false;                        
    //                 // }                    
                  
    //                 // if($option_id === false) {  
                     
    //                 //     if($row->primary_variant && !empty($options[$row->primary_variant])) {
    //                 //         $opt = $options[$row->primary_variant];
    //                 //         $option_id = $row->primary_variant;                            
    //                 //     } else {
    //                 //         $opt = current($options);                            
    //                 //         if($opt->product_id == $row->id){
    //                 //             $option_id = $opt->id; //Set primary varients
    //                 //         }
    //                 //     }
    //                 // }

    //                 if ($options) {
    //                     $option_id = $option_id ? $option_id : ($row->primary_variant ? $row->primary_variant : 0);
                      
    //                     // $opt = ($option_id)? $options[$option_id] : current($options); 
    //                     $opt = ($option_id) ? $options[$option_id] : current($options); 
    //                     // $row->mrp = $opt->price;
    //                     $row->mrp = $opt->mrp;


    //                     if (!$option_id) {
    //                         $option_id = $opt->id;
    //                     }
    //                     $row->option = $option_id;
    //                     $variant_current_stock = $this->site->getWarehouseStock($warehouse_id, $row->id, $option_id);
    //                     $row->current_stock = $variant_current_stock->quantity;
    //                 }
    //                 else{
    //                     $row->mrp = $row->mrp;
    //                 } 

    //                 if($opt !== false) {
    //                     $row->unit_price        = $row->price + $opt->price; 
    //                     $row->base_unit_price   = $row->unit_price;                                                 
    //                     $row->unit_quantity     = $opt->unit_quantity ? $opt->unit_quantity : 1;
    //                     $row->unit_weight       = $opt->unit_weight;
    //                     $row->option            = $option_id;
    //                     // calculate unit_price after discount on mrp apply(only for variant with product)
    //                     if(isset($opt->variant_discount_on_mrp)){
                           
    //                         // $percentage = '%';
    //                         // $discount = $opt->variant_discount_on_mrp;
    //                         // $dpos = strpos($discount, $percentage);
    //                         // if ($dpos !== false) {
    //                         //     $pds = explode("%", $discount);
    //                         //     $pr_var_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($row->unit_price)) * (Float) ($pds[0])) / 100), 6);
    //                         // } else {
    //                         //     $pr_var_discount = $this->sma->formatDecimal($discount, 6);
    //                         // }
    //                         // $row->unit_price = $row->unit_price - $pr_var_discount;
    //                         // // $row->pr_var_discount =  $pr_var_discount;
    //                         // $row->discount_on_mrp =  $pr_var_discount;
    //                         $row->discount_on_mrp =  $opt->variant_discount_on_mrp;

    //                     }   
    //                 }//end if
                    
    //                 $product_options = false;
                
    //                 if ($opt->product_id == $row->id ) {
    //                     foreach ($options as $option) {
    //                         if($row->storage_type == "loose") {                           
    //                             $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);                                                      
    //                         } else {
    //                             $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $option->id);                           
    //                         }

    //                         $option_quantity = 0;
    //                         if($pis) {
    //                             foreach ($pis as $pi) {
    //                                 $option_quantity += $pi->quantity_balance;
    //                             }
    //                         }
    //                         if($row->storage_type == "loose") { 
    //                             //Loose products Variants Quantity Calculate
    //                             $option->quantity = number_format($option_quantity / $option->unit_quantity , 2);
    //                         } else {
    //                             $option->quantity = $option_quantity;  
    //                         } 
                            
    //                         if((!$this->Settings->overselling && $option->quantity > 0) || $this->Settings->overselling ) {
    //                             // $product_options[$option->id] = $option; 
    //                             $product_options[] = $option; 
    //                         }
                           
    //                     }//end foreach
    //                     unset($options);
 
    //                     $row->quantity = $product_options[$row->option]->quantity;
    //                     $options = $product_options;
    //                 }//end if
    //                 foreach ($options as $option) {
    //                     if ($subtotal == '') {
    //                         $option->price =$option->price;
    //                     }else{
    //                         $option->price = $subtotal;
    //                     }
    //                     $option_color_id = '';
    //                     $options_color = $this->pos_model->getProductOptionscolor($row->id, $warehouse_id, 2);
    //                     if ($options_color) {
    //                         $opt_color = current($options_color);
    //                         if (!empty($opt_color)) {
    //                             $option_color_id = $opt_color->id;
    //                              $option_color_name = $opt_color->name;
    //                         }
    //                     } else {
    //                         $opt_color = json_decode('{}');
    //                     }
    //                     $row->option_color = $option_color_id;
    //                     $row->option_color_name = $option_color_name;
    //                 }
    //             }//end if
                 
    //             if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
    //                 $message = lang('no_match_found');
    //                 // echo null;
    //                 // die();
    //             }
          
                       
    //             $row->mrp = $row->mrp ? $row->mrp : $row->unit_price;
    //             if( $row->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants != 1 ){
    //                 $options         = false;
    //                 $option_quantity = 0;
    //                 $row->option     = 0;
    //             } //end if               
                
    //             /**
    //              * Batch Config
    //              **/
    //             if($this->Settings->product_batch_setting) {
                    
    //                 $productbatches = $this->products_model->getProductVariantsBatch($row->id);
                    
    //                 if($productbatches){
                        
    //                     $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);

    //                     if ($pis) {
    //                         $row->quantity_total = $option_quantity = 0;
    //                         foreach ($pis as $pi) {
    //                             $row->quantity_total += $pi->quantity_balance;
    //                             if($options !== false && $option_id == $pi->option_id){
    //                                 $option_quantity += $pi->quantity_balance;
    //                                 if($pi->batch_number && isset($productbatches[$pi->option_id])){
                                        
    //                                     foreach($productbatches[$pi->option_id] as $batch_id=>$optBatch){
    //                                         if($optBatch->batch_no == $pi->batch_number){

    //                                             $productbatches[$pi->option_id][$batch_id]->quantity += $pi->quantity_balance;  
    //                                         }
    //                                     }                                                      
    //                                 }  
    //                             } else {
    //                                 //Loose products batches quantity.
    //                                 if($pi->batch_number && isset($productbatches[0])){
    //                                     foreach($productbatches[0] as $batch_id=>$optBatch){

    //                                         if($optBatch->batch_no == $pi->batch_number){

    //                                             $productbatches[0][$batch_id]->quantity += $pi->quantity_balance;  
    //                                         }
    //                                     }                                                      
    //                                 }  
    //                             }//end else
    //                         }//end foreach
    //                     }//end if $pis
                        
    //                 }//end if $productbatches
    //                 $batch_option = ($option_id && $row->storage_type == 'packed') ? $option_id : 0;
    //                 $batch = $productbatches[$batch_option];

    //                 if ($batch) {
    //                     $firstKey = current($batch);
    //                     $batchoption = $batch;

    //                     $firstKey = $firstKey->id;
    //                     $row->batch          = $batchoption[$firstKey]->id;
    //                     $row->batch_number   = $batchoption[$firstKey]->batch_no;
    //                     $row->batch_quantity = $batchoption[$firstKey]->quantity;
                         
    //                     $row->unit_price     = $batchoption[$firstKey]->price ? $batchoption[$firstKey]->price : $row->unit_price;
    //                     $row->expiry         = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';

    //                 } else {
    //                     $batchoption = false;
    //                     $row->batch = false;
    //                     $row->batch_number = '';
    //                     $row->batch_quantity = 0;
                        
    //                 }
    //             }
    //             /**
    //              * End Batch Config
    //              */        
                
    //             $row->org_price = $row->unit_price;
             
    //             if ($row->promotion) {
    //                 // $today = strtotime(date('Y-m-d'));
    //                 // $row->unit_price = (strtotime($row->start_date) <= $today && strtotime($row->end_date) >$today ) ? $row->unit_price : $row->promo_price;
    //                 $today = date('Y-m-d');
    //                 $start_date = date('Y-m-d', strtotime($row->start_date)); // Format start date
    //                 $end_date = date('Y-m-d', strtotime($row->end_date)); // Format end date
    //                 $row->unit_price = ($start_date <= $today && $end_date >= $today) ? $row->promo_price : $row->unit_price;
    //             } elseif ($warehouse->price_group_id) {                 
    //                 if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
    //                     // $row->unit_price = $pr_group_price->price;
    //                     if(($this->Settings->theme == "theme_three" || $this->Settings->theme == "theme_four") && $Sale_flag != '1'){ //for Apex and Anchor theme on pos screen 
    //                         $row->mrp = $pr_group_price->price;
    //                         $row->unit_price = $pr_group_price->price;
    //                     }else{
    //                         $row->unit_price = $pr_group_price->price; //for default and theme 2 on pos screen and add/edit sale screen
    //                         $row->price = $pr_group_price->price; 
    //                     }
                       
    //                 }
    //             } 
    //             if ($customer->price_group_id) {
    //                 if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
    //                     // $row->unit_price = $pr_group_price->price;
    //                     if(($this->Settings->theme == "theme_three" || $this->Settings->theme == "theme_four") && $Sale_flag != '1'){ //for Apex and Anchor theme on pos screen 
    //                         $row->mrp = $pr_group_price->price;
    //                         $row->unit_price = $pr_group_price->price;
    //                     }else{
    //                         $row->unit_price = $pr_group_price->price; //for default and theme 2 on pos screen and add/edit sale screen
    //                         $row->price = $pr_group_price->price; 
                            
    //                     }
    //                 }
    //             } 
    //             if ($row->unit_price == 0){
    //                 $row->unit_price = $row->org_price;
    //             }
              
    //             if($customer_group->apply_as_discount){
    //                 $row->unit_price        = $row->unit_price;
    //             }else{
    //                 if (isset($discountP) || isset($discountAmt)) {
    //                     if ($discountP) {
    //                         $row->unit_price = $row->unit_price - (($row->unit_price * $discountP) / 100);
    //                     }

    //                     if ($discountAmt) {
    //                         $row->unit_price = $row->unit_price - $discountAmt;
    //                     }
    //                 } else {
    //                    $row->unit_price        = $row->unit_price - (($row->unit_price * $customer_group->percent) / 100);
    //                 }
    //             }
    //                // 
                     
    //                $row->real_unit_price   = $row->price ? $row->price : $row->unit_price;
    //                 $row->base_quantity     = $row->unit_quantity ? ($row->unit_quantity * $row->qty) : $row->qty;
    //                 $row->unit_quantity     = $row->unit_quantity ? $row->unit_quantity : 1;
    //                 $row->base_unit         = $row->unit;
    //                 $row->warehouse_price_group_id         = $warehouse->price_group_id;

    //                 $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
    //                 // $combo_items = false;
                    
    //             if ($row->type == 'combo') {
    //                 $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
    //             }
    //             if ($row->type == 'Bundle') {
    //                 $combo_items = $this->sales_model->getProductBundelItems($row->id, $warehouse_id);
    //                 $combo_items_data = $this->sales_model->getProductComboItemsdata($row->id);

    //                 $product_names = [];
    //                 $missing_items = [];  
    //                 $is_out_of_stock = 0;
                    
    //                 foreach ($combo_items_data as $item) {
    //                     $found = false;
    //                     $option_data = $this->sales_model->getProductOptionByID($item->option_id);
                      
    //                     foreach ($combo_items as $combo_item) {
    //                         if ($item->id == $combo_item->id && $combo_item->quantity > 0) {
    //                             $found = true;
    //                             break;  
    //                         }
    //                     }
    //                     if (!$found) {
    //                         // $missing_items[] = $item->name;
    //                         $missing_items[] = $item->name . ' (' . $option_data->name . ')';

    //                     }
    //                 }
    //                 if (!empty($missing_items)) {
    //                     $message = implode(', ', $missing_items) . ' ' . lang('is_out_of_stock');
    //                     // $is_out_of_stock = 1;
    //                     $is_out_of_stock = isset($this->Settings->overselling) && $this->Settings->overselling == 1 ? 0 : 1;
    //                     $combo_items = isset($this->Settings->overselling) && $this->Settings->overselling == 1 ? $combo_items_data : $combo_items;
    //                 } 
    //             }
                
    //             if ($bundel_item_code) {  
    //                 $bundelItems = $this->sales_model->getcombodata($bundel_item_code, $warehouse_id, $row->product_id);
    //                 $row->qty = $bundelItems->quantity; 
    //                 $row->base_quantity = $bundelItems->quantity; 
    //             }

    //             $units      = $this->site->getUnitsByBUID($row->base_unit);
    //             $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);
                
    //             if ($this->Settings->theme == "theme_three" || $this->Settings->theme == "theme_four" || $this->Settings->theme == "theme_five" || $Sale_flag == '1') {
    //                 // category leve tax
    //                 $itemCategory = ($row->subcategory_id) ? $row->subcategory_id : $row->category_id;
    //                 $categoryTax = FALSE;
    //                 if ($itemCategory) {
    //                     $categoryTax = $this->sales_model->getCategoryTax($itemCategory);
    //                 }
    //                 if ($categoryTax) {
    //                     if (strtolower($categoryTax->fix_tax_rate) == 'fix tax') {
    //                         if(!empty($row->tax_rate)){  //checking product level tax
    //                             $gettax = $this->site->getTaxRateByID($row->tax_rate);
    //                             $fixtaxrate = $gettax->id . '~' . $gettax->rate;
    //                             $varaibletax = False;
    //                         }else{
    //                             $gettax = $this->site->getTaxRateByID($categoryTax->tax_rate);  //checking fixed tax
    //                             $fixtaxrate = $gettax->id . '~' . $gettax->rate;
    //                             $varaibletax = False;
    //                             $tax_rate   = $this->site->getTaxRateByID($gettax->id);
    //                             $row->tax_rate = $gettax->id;
    //                         }
                        
    //                     } else {
    //                         if(!empty($row->tax_rate)){  //checking product level tax
    //                             $gettax = $this->site->getTaxRateByID($row->tax_rate);
    //                             $fixtaxrate = $gettax->id . '~' . $gettax->rate;
    //                             $varaibletax = False;
    //                         }else{
    //                             // checking varible tax
    //                             $varaibletax = $this->sales_model->getVariableTax((($row->subcategory_id) ? $row->subcategory_id : $row->category_id));
    //                             $fixtaxrate = False;
    //                             $taxratevalue = $varaibletax[1]['taxratevalue'];
    //                             $parts = explode('~', $taxratevalue);
    //                             $gettax->id = $parts[0]; 
    //                             $tax_rate   = $this->site->getTaxRateByID($gettax->id);
    //                             $row->tax_rate = $gettax->id;
    //                         }
    //                     }
    //                 }
    //             }
    //             foreach ($options as $opt) {
    //                 if (isset($opt->group_id) && $opt->group_id == 2) {
    //                     $row->product_option_color = $opt->id;
    //                 }
    //             }
    //             unset($row->org_price, $row->weight );
                
    //           //  $row_id  = $row->id .( ($row->option)?$row->option :'');
    //              $row_id = $row->id . $row->option;
    //             $row_id .= ($row->batch) ? $row->batch : '';
    //             // $size = (!empty($options)) ? $options[0]->name . '-' : '';
    //             $color = (!empty($options_color)) ? $options_color[0]->name : '';
    //             $label = $row->name . ' ' . $color . " (" . $row->code . ")";

    //             $ri = $this->Settings->item_addition == 1 ? $row_id : ($c + $r);
    //             $pr[] = ['id' => ($ri), 'item_id' => $row_id, 'otp' => $opt ,'image' => $row->image, 'label' => $label, 'categoryTaxData' => $categoryTax,'category' => $row->category_id, 'sub_category' => $row->subcategory_id, 'divisionid' =>  $row->divisionid,'brand' => $row->brand,
    //                 'fixtax' => $fixtaxrate, 'category_tax' => $varaibletax, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batchs' => $batchoption, 'product_batches' => $productbatches, 'note' => ($option_note) ? $option_note : "", 'message' => $message, 'is_out_of_stock' => $is_out_of_stock, 'Product_type' => $row->type, 'options_color' => $options_color];
    //             $r++; 
             
    //             unset($opt, $product_options, $options, $batchoption, $productbatches);
                
    //         }
    //         $this->sma->send_json($pr);
    //     } else {
    //         if($wrong_prd_msg)
    //         {
    //             $this->sma->send_json(array(array('id' => 0, 'label' => lang('wrong_product'), 'value' => $term)));
    //         }
    //         else if($no_prd_found){
                
    //             $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
    //         }
    //     }
    // }
    public function suggestions() {
        
        $quantity = $this->input->get('quantity', true);
        $subtotal = $this->input->get('subtotal', true);
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id', true);
        }
        $term = $this->input->get('term', true);
        $bundel_item_code = $this->input->get('bundel_item_code', true); // This product code of bundle items
        $bundle_option_id = $this->input->get('bundle_option_id', true); // This is variant id of bundle variant items
        $product_Id = $this->input->get('product_Id', true);
     
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
        $option_note = $this->input->get('option_note', true);
        $variant_popup = $this->input->get('variant_popup', true) ? $this->input->get('variant_popup', true) : null;
        $pos = $this->input->get('pos', true);
        $Settings = $this->site->get_setting();
        $Pos_settings = $this->site->get_pos_setting();
        
        // Get user's assigned warehouses and filter sales persons accordingly
        try {
            $user = $this->site->getUser();
            $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
            if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                $salesperson_details = $this->site->getSalesPersonsByWarehouse($user_warehouses);
            } else {
                // Fallback to original method if new method doesn't exist
                $salesperson_details = $this->site->getCompanyDetailsByGroupID(5);
            }
        } catch (Exception $e) {
            // Fallback to original method if there's any error
            $salesperson_details = $this->site->getCompanyDetailsByGroupID(5);
        }


        // set flag for checking which screen is called for suggestion function in controller
        $Sale_flag = $this->input->get('Sale_flag', true); 
        $sale_action = $this->input->get('sale_action', true);
        $challan_status = $this->input->get('challan_status', true);

        if($this->Settings->pos_type == 'restaurant'){
          if ($this->input->get('table_id')) {  
             $table_id = $this->input->get('table_id', true); 
          }
        }

        if ($this->input->get('batch_no')) {
            $batch_no = $this->input->get('batch_no');
        }

        if (strlen($term) < 3 || !$term) {        
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $qty_value = explode("-", $term); //Using Barcode - Qty
        $product_qty = isset($qty_value[1]) ? $qty_value[1] : 1;

        $exp = $qty_value[0] ? explode("_", $qty_value[0]) : ''; 

        $analyzed   = $this->sma->analyze_term($qty_value[0]);
        $sr         = $analyzed['term'];        
        $option_id  = $analyzed['option_id'] ? $analyzed['option_id'] : 0;
        $option_color_id  = $analyzed['option_color_id'] ? $analyzed['option_color_id'] : 0;
     
        $warehouse      = $warehouse_id ? $this->site->getWarehousesID($warehouse_id) : false;
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

        $saleData = $this->sales_model->getPreviousSaleNo();
        $parts = explode("/", $saleData->reference_no);
        $right_section = end($parts);
        $RefNo = $this->sma->getReturnSaleReferenceNo($right_section);
        if(isset($table_id)){
           $table_details = $this->sales_model->getTableDetails($table_id); 
        }
        if ($bundel_item_code) {
            $sr = $bundel_item_code;
            $option_id = $bundle_option_id; // This is variant id of bundle variant items
        }
        $sale_action = strtolower(trim($sale_action));
        $overselling = ($sale_action == 'chalan' || $sale_action == 'challan') ? 1 : $this->Settings->overselling;
        if (!$this->Owner || !$this->Admin) {
            $rows = $this->sales_model->getProductNames($sr, $warehouse_id, 50, 1, $product_Id, $overselling);
        } else {
            $rows = $this->sales_model->getProductNames($sr, $warehouse_id, null, null, $product_Id, $overselling);
        }
        //$rows->item_note = $item_note;
        $wrong_prd_msg = FALSE;
        $no_prd_found = FALSE;
        //$rows->item_note = $item_note;
        if (!empty($rows) && $rows[0]->type != 'combo') {
            if (!$overselling) {
            if(!$rows)
            {
                 //$this->sma->send_json(array(array('id' => 0, 'label' => lang('wrong_product'), 'value' => $term)));
                $wrong_prd_msg = TRUE;
            }
            else{
                /*
                if (!($sale_action == 'chalan' || $sale_action == 'challan')) {
                    $rows = array_filter($rows, function ($row) {
                        return isset($row->wp_quantity) && (float)$row->wp_quantity > 0;
                    });
                }
                */
    
                if(!$rows)
                {
                    //$this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
                    $no_prd_found = TRUE;
                }
            }
            }
        }


       
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row)  {
                $is_out_of_stock = 0;
                $message = '';
                $product_current_stock = $this->site->getWarehouseStock($warehouse_id, $row->id);
                $row->current_stock = $product_current_stock->quantity;
                 if (($this->pos_settings->active_repeat_customer_discount) && ($this->pos_settings->auto_apply_repeat_customer_discount) && ($customer_id != 1)) {
                   
                    $getDiscount = $this->sales_model->getRepeatSalesCheck($customer_id, $row->code, $row->repeat_sale_validity);
                  
                if ($getDiscount) {
                        $discountP = $getDiscount['discountP'];
                        $discountAmt = $getDiscount['discountAmt'];
                    }else{
                          $discountP = 0;
                           $discountAmt = 0;
                     }
                }

                $article_code = isset($row->article_code) ? $row->article_code : null;
                unset($row->cost,$row->details, $row->product_details, $row->barcode_symbology, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                unset($row->alert_quantity, $row->article_code,  $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6,  $row->file, $row->food_type_id, $row->in_eshop, $row->is_featured, $row->purchase_unit, $row->ratings_avarage, $row->ratings_count, $row->supplier3price, $row->track_quantity, $row->updated_at, $row->comments_count );
               
                $row->category_id =  $category_id ? $category_id : $row->category_id; // Set category id if passed from pos setting
                $category_data = $this->pos_model->getCategoryIdByName($row->category_id); 
                $row->category_name =  $category_data->name;
                /** Changes according to pos setting Use Product Price Field* */
                if (isset($this->pos_settings->use_product_price) && $this->pos_settings->use_product_price == 'mrp') {                    
                    $row->price = $row->mrp ? $row->mrp : $row->price;
                }                 
            

                if(isset($table_details)){
                  if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $table_details->price_group_id)) {
                    $row->price = $pr_group_price->price;
                  }
                }
                
                $productbatches = false;  
                $option = isset($exp[1]) ? $exp[1] : false; // Using Barcode Scan time 
                $row->return_ref_no = $RefNo;   
                $unitData = $this->sales_model->getUnitById($row->unit);
                $row->unit_lable        = $unitData->name;
                $row->quantity_total    = $row->quantity;
                $row->item_tax_method   = $row->tax_method;
                $row->base_quantity     = $product_qty ? (float)$product_qty : 1;
                $row->qty               = $product_qty ? (float)$product_qty : 1;
                $row->qty = ($quantity) ? $quantity :  $product_qty;
                $subtotal = $this->sma->formatDecimal($subtotal);
                $row->unit_quantity     = 1;
                if ($discountP) {

                    $row->discount = (($discountP) ? $discountP . '%' : (($customer_group->apply_as_discount) ? $customer_group->percent . '%' : '0'));
                } else {
                   $row->discount          = ($customer_group->apply_as_discount)?$customer_group->percent.'%':'0'; 
                   $customer_group_discount = 0;
                   $row->customer_group_discount = ($customer_group->percent > 0) ? '1' : '0'; // flag for customer discount apply
                }      
                $row->discount_on_mrp =  ($row->discount_on_mrp == 'Null' || $row->discount_on_mrp == '') ? "0%" : $row->discount_on_mrp ;

                $row->warehouse         = $warehouse_id;                               
                // $row->unit_price        = $row->price;                
                $row->unit_price        = $row->mrp;  
                if($this->Settings->theme == 'newpos' || $this->Settings->theme == 'default'){
                    $row->unit_price        = $row->price;  
                }          
                $row->base_unit_price   = $row->price;
                $row->unit_weight       = $row->weight;
                $row->option            = 0;
                
                $row->sale_loose_products_with_variants   = $this->Settings->sale_loose_products_with_variants;
                
                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id); 
                $pw_quantity = 0; 
                if($pis) {                    
                    foreach ($pis as $pi) {
                        $pw_quantity += $pi->quantity_balance;
                    }                   
                }
                
                $row->quantity = $pw_quantity;
                $option_id = $option_id ? $option_id : false;
                // $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariants($row->id, $variant_popup) : false;                
                $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariantsWithoutcolor($row->id, $variant_popup, 1) : false;      
                $Option_stock = $this->sales_model->getWarehouse($row->id, $warehouse_id);                
                if ($options && $Option_stock) {
                    // Create a mapping of option_id to index for quick lookup
                    $option_index_map = array();
                    foreach ($options as $index => $opt) {
                        $option_index_map[$opt->id] = $index;
                    }
                    // Add stock data while preserving order
                    foreach ($options as $index => $opt) {
                        $options[$index]->stock = isset($Option_stock[$opt->id]) ? $Option_stock[$opt->id] : 0;
                    }
                }
          
                // $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariants($row->id) : false;                
                // $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductoptionData($row->id) : false;                
                $opt = false;
                                
                if( $options !== false ) {
                    
                    // if($option_id !== false && !empty($options[$option_id])){
                    //     $opt = $options[$option_id];
                    //     if($opt->product_id !== $row->id){
                    //         $option_id = false; 
                    //         $opt = false;
                    //     }
                    // } else {
  
                    //     $option_id = false;                        
                    // }                    
                  
                    // if($option_id === false) {  
                     
                    //     if($row->primary_variant && !empty($options[$row->primary_variant])) {
                    //         $opt = $options[$row->primary_variant];
                    //         $option_id = $row->primary_variant;                            
                    //     } else {
                    //         $opt = current($options);                            
                    //         if($opt->product_id == $row->id){
                    //             $option_id = $opt->id; //Set primary varients
                    //         }
                    if ($options) {
                        $option_id = $option_id ? $option_id : ($row->primary_variant ? $row->primary_variant : 0);
                      
                        // Find the option by ID in the array
                        $opt = null;
                        if ($option_id) {
                            foreach ($options as $option) {
                                if ($option->id == $option_id) {
                                    $opt = $option;
                                    break;
                                }
                            }
                        }
                        if (!$opt && isset($options[0])) {
                            $opt = $options[0]; // Get first option if no specific one found
                        }
                        
                        $row->mrp = $opt ? $opt->mrp : null;


                        if (!$option_id) {
                            $option_id = $opt->id;
                        }
                        $row->option = $option_id;
                        $variant_current_stock = $this->site->getWarehouseStock($warehouse_id, $row->id, $option_id);
                        $row->current_stock = $variant_current_stock->quantity;
                    }
                    else{
                        $row->mrp = $row->mrp;
                    } 

                    if($opt !== false) {
                        $row->unit_price        = $row->price + $opt->price; 
                        $row->base_unit_price   = $row->unit_price;                                                 
                        $row->unit_quantity     = $opt->unit_quantity ? $opt->unit_quantity : 1;
                        $row->unit_weight       = $opt->unit_weight;
                        $row->option            = $option_id;
                        // calculate unit_price after discount on mrp apply(only for variant with product)
                        if(isset($opt->variant_discount_on_mrp)){
                           
                            // $percentage = '%';
                            // $discount = $opt->variant_discount_on_mrp;
                            // $dpos = strpos($discount, $percentage);
                            // if ($dpos !== false) {
                            //     $pds = explode("%", $discount);
                            //     $pr_var_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($row->unit_price)) * (Float) ($pds[0])) / 100), 6);
                            // } else {
                            //     $pr_var_discount = $this->sma->formatDecimal($discount, 6);
                            // }
                            // $row->unit_price = $row->unit_price - $pr_var_discount;
                            // // $row->pr_var_discount =  $pr_var_discount;
                            // $row->discount_on_mrp =  $pr_var_discount;
                            $row->discount_on_mrp =  $opt->variant_discount_on_mrp;

                        }   
                    }//end if
                    
                    $product_options = false;
                
                    if ($opt->product_id == $row->id ) {
                        foreach ($options as $option) {
                            if($row->storage_type == "loose") {                           
                                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);                                                      
                            } else {
                                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $option->id);                           
                            }

                            $option_quantity = 0;
                            if($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if($row->storage_type == "loose") { 
                                //Loose products Variants Quantity Calculate
                                $option->quantity = number_format($option_quantity / $option->unit_quantity , 2);
                            } else {
                                $option->quantity = $option_quantity;  
                            } 
                            
                            if ((!$overselling && $option->quantity > 0) || $overselling) {
                                // $product_options[$option->id] = $option; 
                                $product_options[] = $option; 
                            }
                           
                        }//end foreach
                        unset($options);
 
                        // Find the option with matching ID
                        $selected_option = null;
                        foreach ($product_options as $po) {
                            if ($po->id == $row->option) {
                                $selected_option = $po;
                                break;
                            }
                        }
                        $row->quantity = $selected_option ? $selected_option->quantity : $row->current_stock;
                        $options = $product_options;
                    }//end if
                    foreach ($options as $option) {
                        if ($subtotal == '') {
                            $option->price =$option->price;
                        }else{
                            $option->price = $subtotal;
                        }
                        if ($option_color_id == 0) {
                            $option_color_id = '';
                            $options_color = $this->pos_model->getProductOptionscolor($row->id, $warehouse_id, 2);
                            if ($options_color) {
                                $opt_color = current($options_color);
                                if (!empty($opt_color)) {
                                    $option_color_id = $opt_color->id;
                                    $option_color_name = $opt_color->name;
                                }
                            } else {
                                $opt_color = json_decode('{}');
                            }
                        } else {
                            $opt_color = $this->sales_model->getProductOptionByID($option_color_id);
                            if ($opt_color) {
                                $option_color_name = $opt_color->name;
                            }
                        }
                        $row->option_color = $option_color_id;
                        $row->option_color_name = $option_color_name;
                    }
                }//end if
                 
                if ($row->type == 'standard' && (!$overselling && $row->quantity < 1)) {
                    $message = lang('no_match_found');
                    // echo null;
                    // die();
                }
          
                       
                $row->mrp = $row->mrp ? $row->mrp : $row->unit_price;
                if( $row->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants != 1 ){
                    $options         = false;
                    $option_quantity = 0;
                    $row->option     = 0;
                } //end if               
                
                /**
                 * Batch Config
                 **/
                if($this->Settings->product_batch_setting) {
                    if($batch_no){
                        $batches = $this->products_model->getProductBatchById($batch_no);
                        if ($batches && count($batches) > 0) {
                            $batch = $batches[0]; 
                            $row->batch             = $batch->id;
                            $row->batch_number      = $batch->batch_no;
                            $row->option            = $batch->option_id;
                            $row->batch_quantity    = $batch->quantity;
                            $row->unit_price        = (!empty($batch->price) && $batch->price != 0) ? $batch->price : $row->price;
                            $row->expiry            = (!empty($batch->expiry_date) && $batch->expiry_date !== '0000-00-00')? $batch->expiry_date : '';
                            $row->mrp               = (!empty($batch->mrp) && $batch->mrp != 0) ? $batch->mrp : $row->mrp;
                            $row->base_unit_price   = $batch->price ? $batch->price : $row->unit_price;
                            if(!empty($options)){
                                foreach ($options as $option) {
                                    // $option->mrp            = $batch->mrp ? $batch->mrp : $option->mrp;
                                    $option->mrp               = (!empty($batch->mrp) && $batch->mrp != 0) ? $batch->mrp : $option->mrp;
                                    $option->unit_price        = (!empty($batch->price) && $batch->price != 0) ? $batch->price : $option->price;
                                    $option->base_unit_price   = $batch->price ? $batch->price : $option->unit_price;
                                }
                            }
                        } else {
                            $row->batch = false;
                            $row->batch_number = '';
                            $row->batch_quantity = 0;
                        }
                    }else{
                        $productbatches = $this->products_model->getProductVariantsBatch($row->id);
                        if($productbatches){
                            
                            $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);

                            if ($pis) {
                                $row->quantity_total = $option_quantity = 0;
                                foreach ($pis as $pi) {
                                    $row->quantity_total += $pi->quantity_balance;
                                    if($options !== false && $option_id == $pi->option_id){
                                        $option_quantity += $pi->quantity_balance;
                                        if($pi->batch_number && isset($productbatches[$pi->option_id])){
                                            
                                            foreach($productbatches[$pi->option_id] as $batch_id=>$optBatch){
                                                if($optBatch->batch_no == $pi->batch_number){
                                                    $productbatches[$pi->option_id][$batch_id]->quantity += $pi->quantity_balance;  
                                                }
                                            }                                                      
                                        }  
                                    } else {
                                        //Loose products batches quantity.
                                        if($pi->batch_number && isset($productbatches[0])){
                                            foreach($productbatches[0] as $batch_id=>$optBatch){
                                                if($optBatch->batch_no == $pi->batch_number){
                                                    $productbatches[0][$batch_id]->quantity += $pi->quantity_balance;  
                                                }
                                            }                                                      
                                        }  
                                    }//end else
                                }//end foreach
                            }//end if $pis
                            
                        }//end if $productbatches
                        if($pos == '1'){
                            $batch_option = ($option_id && $row->storage_type == 'packed') ? $option_id : 0;
                            $batch = $productbatches[$batch_option];
                            $all_batches = [];
                            foreach ($productbatches as $option_batches) {
                                foreach ($option_batches as $batch_id => $bt) {
                                    $all_batches[$batch_id] = $bt;
                                }
                            }
                            $batch = $all_batches;
                        }else{
                            $batch_option = ($option_id && $row->storage_type == 'packed') ? $option_id : 0;
                            $batch = isset($productbatches[$batch_option]) ? $productbatches[$batch_option] : [];
                        }
                        if (!empty($batch)) {
                            $firstKey = current($batch);
                            $batchoption = $batch;
                            $firstKey = $firstKey->id;
                            $row->batch          = $batchoption[$firstKey]->id;
                            $row->batch_number   = $batchoption[$firstKey]->batch_no;
                            $row->batch_quantity = $batchoption[$firstKey]->quantity;
                            $row->unit_price     = $batchoption[$firstKey]->price ? $batchoption[$firstKey]->price : $row->unit_price;
                            $row->expiry         = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';
                        } else {
                            $batchoption = false;
                            $row->batch = false;
                            $row->batch_number = '';
                            $row->batch_quantity = 0;
                        }
                    }
                    
                }
                /**
                 * End Batch Config
                 */        
                
                $row->org_price = $row->unit_price;
             
                if ($row->promotion) {
                    // $today = strtotime(date('Y-m-d'));
                    // $row->unit_price = (strtotime($row->start_date) <= $today && strtotime($row->end_date) >$today ) ? $row->unit_price : $row->promo_price;
                    $today = date('Y-m-d');
                    $start_date = date('Y-m-d', strtotime($row->start_date)); // Format start date
                    $end_date = date('Y-m-d', strtotime($row->end_date)); // Format end date
                    $row->unit_price = ($start_date <= $today && $end_date >= $today) ? $row->promo_price : $row->unit_price;
                } elseif ($warehouse->price_group_id) {                 
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        // $row->unit_price = $pr_group_price->price;
                        if(($this->Settings->theme == "theme_three" || $this->Settings->theme == "theme_four" || $this->Settings->theme == "theme_six" || $this->Settings->theme == "theme_seven") && $Sale_flag != '1'){ //for Apex and Anchor theme on pos screen 
                            if ($this->Settings->discount_on_mrp == 1) {
                                $row->mrp = $pr_group_price->price;
                                $row->unit_price = $pr_group_price->price;
                            } else {
                                $row->price = $pr_group_price->price;
                                $row->base_unit_price = $pr_group_price->price;
                            }
                        }else{
                            $row->unit_price = $pr_group_price->price; //for default and theme 2 on pos screen and add/edit sale screen
                            $row->price = $pr_group_price->price; 
                        }
                       
                    }
                } 
                if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->unit_price = $pr_group_price->price;
                        if(($this->Settings->theme == "theme_three" || $this->Settings->theme == "theme_four" || $this->Settings->theme == "theme_six" || $this->Settings->theme == "theme_seven") && $Sale_flag != '1'){ //for Apex and Anchor theme on pos screen 
                            if ($this->Settings->discount_on_mrp == 1) {
                                $row->mrp = $pr_group_price->price;
                            } else {
                                $row->price = $pr_group_price->price;
                                $row->unit_price = $pr_group_price->price;
                                $row->base_unit_price = $pr_group_price->price;
                            }
                        }else{
                            $row->unit_price = $pr_group_price->price; //for default and theme 2 on pos screen and add/edit sale screen
                            $row->price = $pr_group_price->price; 
                            
                        }
                    }
                }

                if ($this->Settings->discount_on_mrp == 0 && (!(float) $row->mrp || (float) $row->mrp == 0)) {
                    $row->mrp = $row->org_price;
                }

                if ($row->unit_price == 0){
                    $row->unit_price = $row->org_price;
                }
              
                if($customer_group->apply_as_discount){
                    $row->unit_price        = $row->unit_price;
                }else{
                    if (isset($discountP) || isset($discountAmt)) {
                        if ($discountP) {
                            $row->unit_price = $row->unit_price - (($row->unit_price * $discountP) / 100);
                        }

                        if ($discountAmt) {
                            $row->unit_price = $row->unit_price - $discountAmt;
                        }
                    } else {
                       $row->unit_price        = $row->unit_price - (($row->unit_price * $customer_group->percent) / 100);
                    }
                }
                   // 
                     
                   $row->real_unit_price   = $row->price ? $row->price : $row->unit_price;
                    $row->base_quantity     = $row->unit_quantity ? ($row->unit_quantity * $row->qty) : $row->qty;
                    $row->unit_quantity     = $row->unit_quantity ? $row->unit_quantity : 1;
                    $row->base_unit         = $row->unit;
                    $row->warehouse_price_group_id         = $warehouse->price_group_id;

                    $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                    // $combo_items = false;
                    
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                if ($row->type == 'Bundle') {
                    $combo_items = $this->sales_model->getProductBundelItems($row->id, $warehouse_id);
                    $combo_items_data = $this->sales_model->getProductComboItemsdata($row->id);

                    $product_names = [];
                    $missing_items = [];  
                    $is_out_of_stock = 0;
                    
                    foreach ($combo_items_data as $item) {
                        $found = false;
                        $option_data = $this->sales_model->getProductOptionByID($item->option_id);
                      
                        foreach ($combo_items as $combo_item) {
                            if ($item->id == $combo_item->id && $combo_item->quantity > 0) {
                                $found = true;
                                break;  
                            }
                        }
                        if (!$found) {
                            // $missing_items[] = $item->name;
                            $missing_items[] = $item->name . ' (' . $option_data->name . ')';

                        }
                    }
                    if (!empty($missing_items)) {
                        $message = implode(', ', $missing_items) . ' ' . lang('is_out_of_stock');
                        // $is_out_of_stock = 1;
                        $is_out_of_stock = isset($overselling) && $overselling == 1 ? 0 : 1;
                        $combo_items = isset($overselling) && $overselling == 1 ? $combo_items_data : $combo_items;
                    } 
                }
                
                if ($bundel_item_code) {  
                    $bundelItems = $this->sales_model->getcombodata($bundel_item_code, $warehouse_id, $row->product_id);
                    $row->qty = $bundelItems->quantity; 
                    $row->base_quantity = $bundelItems->quantity; 
                }

                $units      = $this->site->getUnitsByBUID($row->base_unit);
                $fixtaxrate = false;
                $varaibletax = false;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                
                $tax_method_type = 'product';
                if ($this->Settings->theme == "theme_three" || $this->Settings->theme == "theme_four" || $this->Settings->theme == "theme_five" || $this->Settings->theme == "theme_six" || $this->Settings->theme == "theme_seven" || $Sale_flag == '1') {
                    // category leve tax
                    $itemCategory = ($row->subcategory_id) ? $row->subcategory_id : $row->category_id;
                    $categoryTax = FALSE;
                    if ($itemCategory) {
                        $categoryTax = $this->sales_model->getCategoryTax($itemCategory);
                    }
                    if ($categoryTax) {
                        if (strtolower($categoryTax->fix_tax_rate) == 'fix tax') {
                            if(!empty($row->tax_rate)){  //checking product level tax
                                $gettax = $this->site->getTaxRateByID($row->tax_rate);
                                $fixtaxrate = $gettax->id . '~' . $gettax->rate;
                                $varaibletax = False;
                                $tax_method_type = 'product';
                            }else{
                                $gettax = $this->site->getTaxRateByID($categoryTax->tax_rate);  //checking fixed tax
                                $fixtaxrate = $gettax->id . '~' . $gettax->rate;
                                $varaibletax = False;
                                $tax_rate   = $this->site->getTaxRateByID($gettax->id);
                                $row->tax_rate = $gettax->id;
                                $tax_method_type = 'category';
                            }
                        
                        } else {
                            if(!empty($row->tax_rate)){  //checking product level tax
                                $gettax = $this->site->getTaxRateByID($row->tax_rate);
                                $fixtaxrate = $gettax->id . '~' . $gettax->rate;
                                $varaibletax = False;
                                $tax_method_type = 'product';
                            }else{
                                // checking varible tax
                                $varaibletax = $this->sales_model->getVariableTax((($row->subcategory_id) ? $row->subcategory_id : $row->category_id));
                                $fixtaxrate = False;
                                $taxratevalue = $varaibletax[1]['taxratevalue'];
                                $parts = explode('~', $taxratevalue);
                                $gettax->id = $parts[0]; 
                                $tax_rate   = $this->site->getTaxRateByID($gettax->id);
                                $row->tax_rate = $gettax->id;
                                $tax_method_type = 'category';
                            }
                        }
                    }
                }
                // Internal customer: zero tax after category/product tax (user can still manually set tax in POS)
                $this->site->applyInternalCustomerZeroTax($customer, $row, $tax_rate, $fixtaxrate, $varaibletax);
                foreach ($options as $opt) {
                    if (isset($opt->group_id) && $opt->group_id == 2) {
                        $row->product_option_color = $opt->id;
                    }
                }
                unset($row->org_price, $row->weight );
                
              //  $row_id  = $row->id .( ($row->option)?$row->option :'');
                 $row_id = $row->id . $row->option;
                $row_id .= ($row->batch) ? $row->batch : '';
                // $size = (!empty($options)) ? $options[0]->name . '-' : '';
                $color = (!empty($options_color)) ? $options_color[0]->name : '';
                $label = $row->name . ' ' . $color . " (" . $row->code . ")";

                $ri = $this->Settings->item_addition == 1 ? $row_id : ($c + $r);
                $pr[] = ['id' => ($ri), 'item_id' => $row_id, 'otp' => $opt ,'image' => $row->image, 'label' => $label, 'categoryTaxData' => $categoryTax,'category' => $row->category_id, 'sub_category' => $row->subcategory_id, 'divisionid' =>  $row->divisionid,'brand' => $row->brand,
                    'fixtax' => $fixtaxrate, 'category_tax' => $varaibletax, 'tax_method_type' => $tax_method_type, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batchs' => $batchoption, 'product_batches' => $productbatches, 'note' => ($option_note) ? $option_note : "", 'message' => $message, 'is_out_of_stock' => $is_out_of_stock, 'Product_type' => $row->type, 'options_color' => $options_color, 'article_code' => $article_code, 'salesperson_details' => $salesperson_details, 'Pos_settings' => $Pos_settings];
                $r++; 
             
                unset($opt, $product_options, $options, $batchoption, $productbatches);
                
            }
            $this->sma->send_json($pr);
        } else {
           if (!$rows) {
        $raw_check = $this->sales_model->isRawProduct($sr);

        if ($raw_check) {
            $this->sma->send_json([
                'status' => 'raw_error',
                'msg'    => 'Product not found.'
            ]);
        }
    }

            if($wrong_prd_msg)
            {
                $this->sma->send_json(array(array('id' => 0, 'label' => lang('wrong_product'), 'value' => $term)));
            }
            else {
                $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
            }
        }
    }

    /* **********************************************
     *  Suggestion in QR Code
     * ********************************************* */

    public function suggestions_qr() {
        $Settings = $this->site->get_setting();

        $termstring = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
        $option_note = $this->input->get('option_note', true);

        if($this->Settings->pos_type == 'restaurant'){
          if ($this->input->get('table_id')) {  
             $table_id = $this->input->get('table_id', true); 
             
          }
        }

        if (strlen($termstring) < 1 || !$termstring) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

//             $termdata =  explode(",", $termstring);
        $termdata = explode(",", str_replace(" ", "", $termstring));


        foreach ($termdata as $term) {

            if ($term != '') {

                $qty_value = explode($Settings->barcode_separator_weight, $term); //Using Barcode - Qty
                $product_qty = isset($qty_value[1]) ? $qty_value[1] : 1;

                $exp = $qty_value[0] ? explode("_", $qty_value[0]) : ''; // Using Barcode

                $analyzed = $this->sma->analyze_term($qty_value[0]);
                $sr = $analyzed['term'];


                $option_id = $analyzed['option_id'];
                $option_color_id = $analyzed['option_color_id'];

                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                $customer = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

                 if(isset($table_id)){
                     $table_details = $this->sales_model->getTableDetails($table_id); 
                 }
     
                if ((!$this->Owner || !$this->Admin)):
                    $rows = $this->sales_model->getQRScanProductNames($sr, $warehouse_id, 50, 1);
                else:
                    $rows = $this->sales_model->getQRScanProductNames($sr, $warehouse_id);
                endif;
//                echo '<pre>';
//                print_r($rows);
//                exit;
////                $rows->item_note = $item_note;
                if ($rows) {
                    
                    $r = 0;
                    foreach ($rows as $row) {
                          if (($this->pos_settings->active_repeat_customer_discount) && ($this->pos_settings->auto_apply_repeat_customer_discount) && ($customer_id != 1)) {
                            $getDiscount = $this->sales_model->getRepeatSalesCheck($customer_id, $row->code, $row->repeat_sale_validity);
                            if ($getDiscount) {
                                $discountP = $getDiscount['discountP'];
                                $discountAmt = $getDiscount['discountAmt'];
                            }
                        }
$c = str_replace(".", "", microtime(true));
                        unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        unset($row->alert_quantity, $row->article_code,  $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->cost,  $row->file, $row->food_type_id, $row->in_eshop, $row->is_featured, $row->purchase_unit, $row->ratings_avarage, $row->ratings_count, $row->supplier3price, $row->track_quantity, $row->updated_at, $row->comments_count);

                        $option = $options = $productbatches = false;
                        $option = isset($exp[1]) ? $exp[1] : false; // Using Barcode Scan time 
                         
                       if(isset($table_details)){
                           if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $table_details->price_group_id)) {
                             $row->price = $pr_group_price->price;
                            }
                        }

                        $unitData = $this->sales_model->getUnitById($row->unit);
                        $row->unit_lable = $unitData->name;
                        $row->quantity_total = $row->quantity;
                        $row->base_quantity = 1;
                        $row->item_tax_method = $row->tax_method;
                        $row->qty = (float) $product_qty;
                       if (isset($discountP)) {
                            $row->discount = (($discountP) ? $discountP . '%' : (($customer_group->apply_as_discount) ? $customer_group->percent . '%' : '0'));
                        } else {
                         $row->discount = ($customer_group->apply_as_discount)?$customer_group->percent.'%':'0'; 
                        }
                        $row->warehouse = $warehouse_id;

                        $row->unit_price = $row->price;
                        $row->base_unit_price = $row->price;

                        $options = $this->Settings->attributes == 1 ? $this->sales_model->getProductVariants($row->id) : false;
                        if ($row->storage_type == 'packed' || ($row->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1 )) {

                            //$options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                            $opt = json_decode('{}');
                            $opt->price = 0;
                            if ($options) {
                                if (!$option_id) {
                                    $copt = current($options);
                                    if ($copt->product_id == $row->id) {
                                        $option_id = ($row->primary_variant) ? $row->primary_variant : $copt->id; //Set primary varients                                
                                    }
                                }
                                $opt = $options[$option_id];
                                $row->unit_price = $row->price + $opt->price;
                                $row->unit_quantity = $opt->unit_quantity ? $opt->unit_quantity : 1;
                                $row->option = $option_id;
                            } else {
                                $row->option = 0;
                                $option_id = 0;
                            }
                        } else {
                            if ($row->storage_type == 'loose' && $options) {
                                if (!$row->primary_variant) {
                                    $copt = current($options);
                                    if ($copt->product_id == $row->id) {
                                        $option_id = ($row->primary_variant) ? $row->primary_variant : $copt->id; //Set primary varients                                
                                    }
                                } else {
                                    $option_id = $row->primary_variant;
                                }
                                $opt = $options[$option_id];

                                $row->unit_price = $row->price + $opt->price;
                                $row->base_unit_price = $row->price + $opt->price;
                                $row->price = $row->price > 0 ? $row->price : $row->base_unit_price;
                                $row->unit_quantity = $opt->unit_quantity ? $opt->unit_quantity : 1;
                                $row->weight = $opt->unit_quantity ? $opt->unit_quantity : 1;

                                $options = false;
                            }
                            $option_id = 0;
                            $option_quantity = 0;
                            $row->option = 0;
                        }
                        $row->mrp = $row->mrp ? $row->mrp : $row->unit_price;

                        if ($options && $opt->product_id == $row->id) {

                            foreach ($options as $option) {
                                if ($row->storage_type == "packed") {
                                    $option_quantity = 0;
                                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $option->id);
                                    if ($pis) {
                                        foreach ($pis as $pi) {
                                            $option_quantity += $pi->quantity_balance;
                                        }
                                    }
                                    $option->quantity = $option_quantity;
                                } else {
                                    //Loose products Variants Quantity Calculate
                                    $option->quantity = number_format($row->quantity_total / $option->unit_quantity, 2);
                                }

                                //                        if ((!$this->Settings->overselling && $option->quantity) || $this->Settings->overselling){
                                $product_options[$option->id] = $option;
                                //                        }
                            }

                            $row->quantity = $product_options[$option_id]->quantity;
                        } else {
                            $product_options = FALSE;
                            $row->option = 0;
                            $option_id = 0;
                        }

                        /**
                         * Batch Config
                         * */

                         $row->quantity = (float) $row->quantity;

                        if ($this->Settings->product_batch_setting) {

                            $productbatches = $this->products_model->getProductVariantsBatch($row->id,$warehouse_id);

                            if ($productbatches) {

                                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);

                                if ($pis) {
                                    $row->quantity_total = $option_quantity = 0;
                                    foreach ($pis as $pi) {
                                        $row->quantity_total += $pi->quantity_balance;
                                        if ($options !== false && $option_id == $pi->option_id) {
                                            $option_quantity += $pi->quantity_balance;
                                            if ($pi->batch_number && isset($productbatches[$pi->option_id])) {

                                                foreach ($productbatches[$pi->option_id] as $batch_id => $optBatch) {
                                                    if ($optBatch->batch_no == $pi->batch_number) {

                                                        $productbatches[$pi->option_id][$batch_id]->quantity += $pi->quantity_balance;
                                                    }
                                                }
                                            }
                                        } else {
                                            //Loose products batches quantity.
                                            if ($pi->batch_number && isset($productbatches[0])) {
                                                foreach ($productbatches[0] as $batch_id => $optBatch) {

                                                    if ($optBatch->batch_no == $pi->batch_number) {

                                                        $productbatches[0][$batch_id]->quantity += $pi->quantity_balance;
                                                    }
                                                }
                                            }
                                        }//end else
                                    }//end foreach
                                }//end if $pis
                            }//end if $productbatches

                            $batch_option = ($options) ? $option_id : 0;

                            $batch = isset($productbatches[$batch_option]) ? $productbatches[$batch_option] : false;

                            if ($batch) {
                                $firstKey = current($batch);
                                $batchoption = $batch;
                                $row->batch = $batchoption[$firstKey]->id;
                                $row->batch_number = $batchoption[$firstKey]->batch_no;
                                $row->batch_quantity = $batchoption[$firstKey]->quantity;

                                $row->unit_price = $batchoption[$firstKey]->price ? $batchoption[$firstKey]->price : $row->unit_price;
                                $row->expiry = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';
                            } else {
                                $batchoption = false;
                                $row->batch = false;
                                $row->batch_number = '';
                                $row->batch_quantity = 0;
                            }
                        }
                        /**
                         * End Batch Config
                         */
                        $row->org_price = $row->unit_price;

                        if ($row->promotion) {
                            $today = strtotime(date('Y-m-d'));
                            $row->unit_price = (strtotime($row->start_date) <= $today && strtotime($row->end_date) > $today ) ? $row->promo_price : $row->unit_price;
                        } elseif ($customer->price_group_id) {
                            if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                                $row->unit_price = $pr_group_price->price;
                            }
                        } elseif ($warehouse && isset($warehouse->price_group_id)) {
                            if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                                $row->unit_price = $pr_group_price->price;
                            }
                        }
                        if ($row->unit_price == 0) {
                            $row->unit_price = $row->org_price;
                        }

                        if($customer_group->apply_as_discount){
                           $row->unit_price = $row->unit_price; 
                        }else{
                            if (isset($discountP) || isset($discountAmt)) {
                                if ($discountP) {
                                    $row->unit_price = $row->unit_price - (($row->unit_price * $discountP) / 100);
                                }

                                if ($discountAmt) {
                                    $row->unit_price = $row->unit_price - $discountAmt;
                                }
                            } else {
                               $row->unit_price = $row->unit_price - (($row->unit_price * $customer_group->percent) / 100);

                            }
                        }
                        //
                        
                        $row->real_unit_price = $row->unit_price;
                        $row->base_quantity = (float) $product_qty;
                        $row->unit_quantity = $row->unit_quantity ? $row->unit_quantity : 1;
                        $row->base_unit = $row->unit;

                        $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                        }
                        if ($row->type == 'Bundle') {
                            $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $this->site->applyInternalCustomerZeroTax($customer, $row, $tax_rate);

                        unset($row->org_price);

                        $row_id = $row->id . $row->option;
                        $row_id .= ($row->batch) ? $row->batch : '';

                        $ri = $this->Settings->item_addition == 1 ? $row_id : ($c + $r);

                        $pr[] = ['id' => ($c + $r + $ri), 'item_id' => $row_id, 'image' => $row->image, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'sub_category' => $row->subcategory_id,'divisionid' =>  $row->divisionid,'brand' => $row->brand,
                            'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batchs' => $batchoption, 'product_batches' => $productbatches, 'note' => ($option_note) ? $option_note : ""];
                        $r++;
                        //unset($row);
                    }
                } else {
                    $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
                }
            }
        }

        if (!empty($pr)) {
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
      
//        header('Content-Type: application/json');
//        echo json_encode($pr);
//        $this->sma->send_json($pr);

        exit;
    }

    /*     * **********************************************
     *  End Suggestion in QR Code
     * ********************************************** */




    public function gift_cards() {
        $this->sma->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('gift_cards')));
        $meta = array('page_title' => lang('gift_cards'), 'bc' => $bc);
        $this->page_construct('sales/gift_cards', $meta, $this->data);
    }

    public function getGiftCards() {

        $this->load->library('datatables');
        $this->datatables
                ->select($this->db->dbprefix('gift_cards') . ".id as id, card_no, value, balance, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by, CONCAT(sma_companies.name,' (',sma_companies.company,')' ), expiry", false)
                ->join('users', 'users.id=gift_cards.created_by', 'left')
                ->join('sma_companies', 'sma_companies.id = sma_gift_cards.customer_id', 'left')
                ->from("gift_cards")
                ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('sales/view_gift_card/$1') . "' class='tip' title='" . lang("view_gift_card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('sales/topup_gift_card/$1') . "' class='tip' title='" . lang("topup_gift_card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . site_url('sales/history_gift_card/$1') . "' class='tip' title='" . lang("History_Gift_Card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-history\"></i></a> <a href='" . site_url('sales/edit_gift_card/$1') . "' class='tip' title='" . lang("edit_gift_card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_gift_card") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function view_gift_card($id = null) {
        $this->data['page_title'] = lang('gift_card');
        $gift_card = $this->site->getGiftCardByID($id);
        $this->data['gift_card'] = $this->site->getGiftCardByID($id);
        $this->data['customer'] = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups'] = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'sales/view_gift_card', $this->data);
    }

    /* 21-11-2019 Show the History Gift Card */

    public function history_gift_card($id = null) {
        $this->data['page_title'] = lang('gift_card');
        $gift_card = $this->site->getGiftCardByID($id);
        $this->data['historygiftcard'] = $this->sales_model->getGiftHistoryByID($gift_card->customer_id, $gift_card->card_no);
        $this->load->view($this->theme . 'sales/giftcard_history', $this->data);
    }

    public function topup_gift_card($card_id) {
        $this->sma->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = array('card_id' => $card_id,
                'amount' => $this->input->post('amount'),
                'date' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id'),
            );
            $card_data['balance'] = ($this->input->post('amount') + $card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->sma->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang("topup_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['card'] = $card;
            $this->data['page_title'] = lang("topup_gift_card");
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function validate_gift_card($no) {
        //$this->sma->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->sma->send_json($gc);
                } else {
                    $this->sma->send_json(false);
                }
            } else {
                $this->sma->send_json($gc);
            }
        } else {
            $this->sma->send_json(false);
        }
    }

    public function add_gift_card() {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang("value"), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            if ($customer == '-' || empty($customer)) :
                $customer = $customer_details->name;
            endif;
            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => $this->input->post('value'),
                'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                'created_by' => $this->session->userdata('user_id'),
            );
            $sa_data = array();
            $ca_data = array();
            if ($this->input->post('staff_points')) {
                $sa_points = $this->input->post('sa_points');
                $user = $this->site->getUser($this->input->post('user'));
                if ($user->award_points < $sa_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/gift_cards");
                }
                $sa_data = array('user' => $user->id, 'points' => ($user->award_points - $sa_points));
            } elseif ($customer_details && $this->input->post('use_points')) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/gift_cards");
                }
                $ca_data = array('customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points));
            }
            // $this->sma->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang("gift_card_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['users'] = $this->sales_model->getStaff();
            $this->data['page_title'] = lang("new_gift_card");
            $this->load->view($this->theme . 'sales/add_gift_card', $this->data);
        }
    }

    public function edit_gift_card($id = null) {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
        $gc_details = $this->site->getGiftCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang("value"), 'required');
        //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card = $this->site->getGiftCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->company : null;
            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
                'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
            );
        } elseif ($this->input->post('edit_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateGiftCard($id, $data)) {
            $this->session->set_flashdata('message', lang("gift_card_updated"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getGiftCardByID($id);
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_gift_card', $this->data);
        }
    }

    public function sell_gift_card() {
        $this->sma->checkPermissions('gift_cards', true);
        $error = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang("value") . " " . lang("is_required");
        }
        if (empty($gcData[1])) {
            $error = lang("card_no") . " " . lang("is_required");
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer = $customer_details ? $customer_details->company : null;
        $data = array('card_no' => $gcData[0],
            'value' => $gcData[1],
            'customer_id' => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer' => $customer,
            'balance' => $gcData[1],
            'expiry' => (!empty($gcData[3])) ? $this->sma->fsd($gcData[3]) : null,
            'created_by' => $this->session->userdata('user_id'),
        );

        if (!$error) {
            if ($this->sales_model->addGiftCard($data)) {
                $this->sma->send_json(array('result' => 'success', 'message' => lang("gift_card_added")));
            }
        } else {
            $this->sma->send_json(array('result' => 'failed', 'message' => $error));
        }
    }

    public function delete_gift_card($id = null) {
        $this->sma->checkPermissions();

        if ($this->sales_model->deleteGiftCard($id)) {
            echo lang("gift_card_deleted");
        }
    }

    public function gift_card_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteGiftCard($id);
                    }
                    $this->session->set_flashdata('message', lang("gift_cards_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,), 'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000'))));

                    $this->excel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:E1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Gift Cards');
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));

                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('value'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('Balance'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('Expiry'));

                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getGiftCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, ' ' . $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, '' . $sc->value);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, '' . $sc->balance);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->expiry);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $this->excel->getDefaultStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $filename = 'gift_cards_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_gift_card_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function get_award_points($id = null) {
        $this->sma->checkPermissions('index');

        $row = $this->site->getUser($id);
        $this->sma->send_json(array('sa_points' => $row->award_points));
    }

    public function sale_by_csv() {
        $this->sma->checkPermissions('index', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));

            $customer_state_code = '';
            $selected_billing_address_id = $this->input->post('billing_address_id') ? (int) $this->input->post('billing_address_id') : 0;
            if ($selected_billing_address_id > 0) {
                $selected_billing_address = $this->db->get_where('addresses', array(
                    'id' => $selected_billing_address_id,
                    'company_id' => $customer_id,
                ), 1)->row();
                if (!empty($selected_billing_address) && !empty($selected_billing_address->state_code)) {
                    $customer_state_code = trim($selected_billing_address->state_code);
                }
            }
            if ($customer_state_code === '') {
                $default_billing_address = $this->db->get_where('addresses', array(
                    'company_id' => $customer_id,
                    'type' => 'Billing',
                    'is_default' => 1,
                ), 1)->row();
                if (!empty($default_billing_address) && !empty($default_billing_address->state_code)) {
                    $customer_state_code = trim($default_billing_address->state_code);
                } else {
                    $customer_state_code = !empty($customer_details->state_code) ? trim($customer_details->state_code) : '';
                }
            }

            if ((!empty($customer_state_code) && !empty($biller_details->state_code)) && $customer_state_code != trim($biller_details->state_code)) {
                $interStateTax = true;
            } else {
                $interStateTax = false;
            }
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $sale_cgst = $sale_sgst = $sale_igst = 0;
            if (isset($_FILES["userfile"])) {

                /* $this->load->library('upload');

                  $config['upload_path'] = $this->digital_upload_path;
                  $config['allowed_types'] = 'csv';
                  $config['max_size'] = $this->allowed_file_size;
                  $config['overwrite'] = true;

                  $this->upload->initialize($config);

                  if (!$this->upload->do_upload()) {
                  $error = $this->upload->display_errors();
                  $this->session->set_flashdata('error', $error);
                  redirect("sales/sale_by_csv");
                  }

                  $csv = $this->upload->file_name;
                  $data['attachment'] = $csv;

                  $arrResult = array();
                  $handle = fopen($this->digital_upload_path . $csv, "r");
                  if ($handle) {
                  while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                  $arrResult[] = $row;
                  }
                  fclose($handle);
                  }
                  $titles = array_shift($arrResult); */

                $this->load->library('excel');
                $File = $_FILES['userfile']['tmp_name'];
                $inputFileType = PHPExcel_IOFactory::identify($File);
                $reader = PHPExcel_IOFactory::createReader($inputFileType);
                //$reader= PHPExcel_IOFactory::createReader('Excel2007');
                $reader->setReadDataOnly(true);
                $path = $File; //"./uploads/upload.xlsx";
                $excel = $reader->load($path);

                $sheet = $excel->getActiveSheet()->toArray(null, true, true, true);
                //print_r($sheet);
                $arrayCount = count($sheet);
                $arrResult = array();
                for ($i = 2; $i <= $arrayCount; $i++) {
                    $arrResult[] = $sheet[$i];
                    // echo $sheet[$i]["A"].$sheet[$i]["B"].$sheet[$i]["C"].$sheet[$i]["D"].$sheet[$i]["E"];
                }

                if ($this->Settings->product_batch_setting) {
                    $keys = array('code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial','batch_number');
                } else {
                    $keys = array('code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial');
                }
                if ($this->Settings->packing_size_column == 1) {
                    $keys[] = 'packing_size';
                }

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {

                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {

                        if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {

                            if ($csv_pr['variant']) {
                                $item_option = $this->sales_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_id = $product_details->id;
                            $item_type = $product_details->type;
                            $item_code = $product_details->code;
                            $item_name = $product_details->name;
                            $item_net_price = $this->sma->formatDecimal($csv_pr['net_unit_price']);
                            $item_quantity = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_serial = $csv_pr['serial'];
                            $batch_number = isset($csv_pr['batch_number']) ? $csv_pr['batch_number'] : null;
                            $item_packing_size = isset($csv_pr['packing_size']) ? (float)$csv_pr['packing_size'] : 0;

                            


                            // Negative value validations
                            if ($item_net_price < 0) {
                                $this->session->set_flashdata('error', "Line {$rw}: Net Unit Price cannot be negative.");
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                            if ($item_quantity <= 0) {
                                $this->session->set_flashdata('error', "Line {$rw}: Quantity must be greater than zero.");
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                            if ($item_tax_rate !== '' && $item_tax_rate < 0) {
                                $this->session->set_flashdata('error', "Line {$rw}: Tax Rate cannot be negative.");
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                            if ($item_discount !== '' && $item_discount < 0) {
                                $this->session->set_flashdata('error', "Line {$rw}: Discount cannot be negative.");
                                redirect($_SERVER["HTTP_REFERER"]);
                            }

                            // Batch number validation based on batch setting
                            $batch_setting = $this->site->get_setting()->product_batch_setting;
                            $variant_text = !empty($csv_pr['variant']) ? ' (' . $csv_pr['variant'] . ')' : '';
                            
                            // If batch is enabled (setting 1 or 2) and batch number is empty, show error
                            if (($batch_setting == 1 || $batch_setting == 2) && empty($batch_number)) {
                                $this->session->set_flashdata('error', "Line {$rw}: Batch number is required for product '{$product_details->name}'" . $variant_text);
                                redirect($_SERVER["HTTP_REFERER"]);
                                return;
                            }
                            
                            // If batch is disabled (setting 0) and batch number is provided, show error
                            if ($batch_setting == 0 && !empty($batch_number)) {
                                $this->session->set_flashdata('error', "Line {$rw}: Batch number is not allowed as batch setting is disabled for product '{$product_details->name}'" . $variant_text);
                                redirect($_SERVER["HTTP_REFERER"]);
                                return;
                            }
                            
                            // Check if batch number exists for this specific product and variant if batch is enabled and provided
                            if (($batch_setting == 1 || $batch_setting == 2) && !empty($batch_number)) {
                                // Get batch with product_id and batch number check
                                $this->db->where('product_id', $product_details->id);
                                $this->db->where('batch_no', $batch_number);
                                if (isset($item_option->id) && !empty($item_option->id)) {
                                    $this->db->where('option_id', $item_option->id);
                                }
                                $batch = $this->db->get('product_batches')->row();

                                if (!$batch) {
                                    $this->session->set_flashdata('error', "Line {$rw}: Batch number '{$batch_number}' not found for product '{$product_details->name}'" . $variant_text);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                    return;
                                }
                            }
                            
                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details = $this->sales_model->getProductByCode($item_code);
                                $real_unit_price = $item_net_price;

                                if (isset($item_discount)) {
                                    $discount = $item_discount;
                                    $dpos = strpos($discount, $percentage);
                                    if ($dpos !== false) {
                                        $pds = explode("%", $discount);
                                        $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_price)) * (Float) ($pds[0])) / 100), 4);
                                    } else {
                                        $pr_discount = $this->sma->formatDecimal($discount);
                                    }
                                } else {
                                    $pr_discount = 0;
                                }
                                $item_net_price = $this->sma->formatDecimal(($item_net_price - $pr_discount), 4);
                                $pr_item_discount = $this->sma->formatDecimal(($pr_discount * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                $tax_details = null;
                                $pr_tax = 0;
                                $item_tax = 0;
                                $pr_item_tax = 0;
                                $tax = '';
                                $unit_tax = 0;
                                $item_gst = $item_cgst = $item_sgst = $item_igst = 0;

                                if (isset($item_tax_rate) && $item_tax_rate != '' && $item_tax_rate != 0) {
                                    $tax_details = $this->sales_model->getTaxRateByName($item_tax_rate);
                                    if (!$tax_details) {
                                        $this->session->set_flashdata('error', lang("tax_not_found") . " ( " . $item_tax_rate . " ). " . lang("line_no") . " " . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                    $pr_tax = $tax_details->id;
                                } elseif ($product_details->tax_rate) {
                                    $pr_tax = $product_details->tax_rate;
                                    $tax_details = $this->site->getTaxRateByID($pr_tax);
                                }

                                if ($tax_details) {
                                    $tax_method = isset($product_details->tax_method) ? $product_details->tax_method : 1;
                                    if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                        if ($tax_method == 1) {
                                            $item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / 100, 4);
                                        } else {
                                            $item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                            $item_net_price = $this->sma->formatDecimal($item_net_price - $item_tax, 4);
                                        }
                                        $tax = $tax_details->rate . "%";
                                    } elseif ($tax_details->type == 2) {
                                        if ($tax_method == 1) {
                                            $item_tax = $this->sma->formatDecimal($tax_details->rate, 4);
                                        } else {
                                            $item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                            $item_net_price = $this->sma->formatDecimal($item_net_price - $item_tax, 4);
                                        }
                                        $tax = $tax_details->rate;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);
                                    $unit_tax = $item_tax;

                                    if ($interStateTax) {
                                        $item_gst = $tax_details->rate;
                                        $item_igst = $pr_item_tax;
                                    } else {
                                        $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                                        $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                                        $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                                    }
                                }

                                $product_tax += $pr_item_tax;
                                if ($item_packing_size > 0) {
                                     $subtotal = $this->sma->formatDecimal((($this->sma->formatDecimal(($item_net_price + $item_tax), 4) * $item_packing_size) * $item_quantity), 4);
                                     $item_net_price = $this->sma->formatDecimal(($real_unit_price * $item_packing_size) - $pr_discount, 4);
                                 } else {
                                     $subtotal = $this->sma->formatDecimal((($item_net_price * $item_quantity) + $pr_item_tax), 4);
                                 }
                                $unit = $this->site->getUnitByID($product_details->unit);

                                $products[] = array(
                                    'product_id' => $product_details->id,
                                    'product_code' => $item_code,
                                    'product_name' => $item_name,
                                    'product_type' => $item_type,
                                    'option_id' => $item_option->id,
                                    'net_unit_price' => $item_net_price,
                                    'quantity' => $item_quantity,
                                    'product_unit_id' => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity' => $item_quantity,
                                    'warehouse_id' => $warehouse_id,
                                    'item_tax' => $pr_item_tax,
                                    'batch_number' => !empty($batch_number) ? $batch_number : NULL,
                                    'packing_size' => $item_packing_size,
                                    'tax_rate_id' => $pr_tax,
                                    'tax' => $tax,
                                    'unit_tax' => $unit_tax,
                                    'unit_discount' => $unit_discount,
                                    'discount' => $item_discount,
                                    'item_discount' => $pr_item_discount,
                                    'subtotal' => $subtotal,
                                    'serial_no' => $item_serial,
                                    'unit_price' => $this->sma->formatDecimal(($item_net_price + $item_tax), 4),
                                    'real_unit_price' => $real_unit_price,
                                    'invoice_unit_price' => $item_net_price,
                                    'invoice_net_unit_price' => $this->sma->formatDecimal(($item_net_price + $unit_discount), 4),
                                    'mrp' => $product_details->mrp,
                                    'hsn_code' => $product_details->hsn_code, 
                                    'gst_rate' => $item_gst,
                                    'cgst' => $item_cgst,
                                    'sgst' => $item_sgst,
                                    'igst' => $item_igst,
                                    'batch_number' =>$batch_number,
                                    'category_id' => $product_details->category_id,  // <-- ADD THIS LINE
                                    
                                );

                                $sale_cgst += $item_cgst;
                                $sale_sgst += $item_sgst;
                                $sale_igst += $item_igst;

                                if ($item_packing_size > 0) {
                                     $total += $this->sma->formatDecimal(($item_net_price * $item_packing_size * $item_quantity), 4);
                                 } else {
                                     $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                                 }
                            }
                        } else {
                            $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }
                }
            }

            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'cgst' => $sale_cgst,
                'sgst' => $sale_sgst,
                'igst' => $sale_igst,
                'shipping_address_id' => $this->input->post('shipping_address_id') ? $this->input->post('shipping_address_id') : NULL,
                'billing_address_id' => $this->input->post('billing_address_id') ? $this->input->post('billing_address_id') : NULL,
            );

            if ($payment_status == 'paid') {

                $payment = array(
                    'date' => $date,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount' => $grand_total,
                    'paid_by' => 'cash',
                    'cheque_no' => '',
                    'cc_no' => '',
                    'cc_holder' => '',
                    'cc_month' => '',
                    'cc_year' => '',
                    'cc_type' => '',
                    'created_by' => $this->session->userdata('user_id'),
                    'note' => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type' => 'received',
                );
            } else {
                $payment = array();
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data, $products, $payment);
        }

        $extrasPara = array('sale_action' => $sale_action, 'syncQuantity' => 1, 'order_id' => $order_id);

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, array(), $extrasPara)) {
            //if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', $this->lang->line("sale_added"));
            redirect("sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['slnumber'] = $this->site->getReference('so');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale_by_csv')));
            $meta = array('page_title' => lang('add_sale_by_csv'), 'bc' => $bc);
            $this->page_construct('sales/sale_by_csv', $meta, $this->data);
        }
    }

    public function update_status($id) {

        $this->form_validation->set_rules('status', lang("sale_status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/update_status', $this->data);
        }
    }

    public function eshop_sales($warehouse_id = null) {
        $this->load->model('eshop_model');
        $this->eshop_model->set_eshop_order_status(2);
        if ($_GET['status'])
            $this->data['status'] = $_GET['status'];
        $this->sma->checkPermissions();
        $resDecline = $this->sales_model->getEshopDeclineOrder();
        if (is_array($resDecline)) {
            foreach ($resDecline as $resDeclineID):
                try {
                    $this->sma->storeDeletedData('sales', 'id', $resDeclineID);
                    $this->sales_model->deleteSale($resDeclineID);
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            endforeach;
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('sales/eshop', $meta, $this->data);
    }

    public function getEshopSales($warehouse_id = null) {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales')
                    ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales');
        }
        $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');
        $this->datatables->where('eshop_sale  =', 1); // ->where('sale_status !=', 'returned');
        if ($_GET['status'] != '')
            $this->datatables->where('payment_status', $_GET['status']);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function offline_sales($warehouse_id = null) {

        $this->sma->checkPermissions();


        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('offline sales'), 'bc' => $bc);
        $this->page_construct('sales/offline', $meta, $this->data);
    }

    public function getOfflineSales($warehouse_id = null) {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales')
                    ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
                    ->from('sales');
        }
        // $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');
        $this->datatables->where('offline_sale  =', 1); // ->where('sale_status !=', 'returned');

        /*  if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
          $this->datatables->where('created_by', $this->session->userdata('user_id'));
          } elseif ($this->Customer) {
          $this->datatables->where('customer_id', $this->session->userdata('user_id'));
          } */
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function post_to_url($url, $data) {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }
        rtrim($fields, '&');
        $post = curl_init();
        curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($post, CURLOPT_POST, count($data));
        curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($post);
        curl_close($post);
        return $result;
    }

    public function modal_view_challan($id = null) {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->load->model('challan_model');
        $inv = $this->challan_model->getChallanByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view && !empty($inv->return_id)):
            $inv->rows_tax = $this->challan_model->getAllTaxChallanItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->challan_model->getAllTaxItemsGroup($id, $inv->return_id);

        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $orderItems = $this->challan_model->getAllChallanItems($id);
        foreach ($orderItems as $key => $row) {
            unset($row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->cf1_name, $row->cf2_name, $row->cf3_name, $row->cf4_name, $row->cf5_name, $row->cf6_name, $row->note);
        
            $rows[] = $row;
        }
         $this->data['rows'] = $rows;
         foreach ($this->data['rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        //$this->data['return_sale'] = $inv->return_id ? $this->orders_model->getOrderByID($inv->return_id) : NULL;
        $return_sales = $inv->return_id ? $this->challan_model->getAllReturnChallanByID($id) : NULL;
        //print_r($return_sales);
        //echo '<br>';
        $product_discount = 0;
        $product_tax = 0;
        $total = 0;
        $grand_total = 0;
        $order_discount = 0;
        $order_tax = 0;
        $paid = 0;
        $rounding = 0;
        if (!empty($return_sales)) {
            foreach ($return_sales as $Keys => $Vals) {
                $product_discount = $product_discount + $Vals['product_discount'];
                $product_tax = $product_tax + $Vals['product_tax'];
                $total = $total + $Vals['total'];
                $rounding = $rounding + $Vals['rounding'];
                $grand_total = $grand_total + $Vals['grand_total'];
                $order_discount = $order_discount + $Vals['order_discount'];
                $order_tax = $order_tax + $Vals['order_tax'];
                $paid = $paid + $Vals['paid'];
                //echo '<br/>';
            }
            $this->data['return_sale'] = (object) array(
                        'product_discount' => $product_discount,
                        'product_tax' => $product_tax,
                        'total' => $total,
                        'rounding' => $rounding,
                        'grand_total' => $grand_total,
                        'order_tax' => $order_tax,
                        'order_discount' => $order_discount,
                        'paid' => $paid,
            );
        }
        $this->data['return_rows'] = $inv->return_id ? $this->challan_model->getAllReturnChallanItems($id) : NULL;
        foreach ($this->data['return_rows'] as $row) {
            if (!empty($row->shade_id)) {
                $colors = $this->sales_model->getProductOptionByID($row->shade_id);
                $row->shade_name= $colors->name;
            }
        }
        $this->data['payments'] = $this->challan_model->getPaymentsForChallan($id);

        $receipt_addresses = $this->challan_model->getChallanReceiptAddresses($inv, $this->data['customer']);
        if ($receipt_addresses) {
            $this->data = array_merge($this->data, $receipt_addresses);
        }

        $this->load->view($this->theme . 'sales/modal_view_challan', $this->data);
        
        // Prepare print data for Android devices - AFTER view is loaded
        //$this->data['payments'] = $this->challan_model->getPaymentsForChallan($id);
        $this->load->model('pos_model');
        
        $print = array();
        $print['print_option'] = $this->data['default_printer'];
        $print['rows'] = $this->data['rows'];
        $print['biller'] = $this->data['biller'];
        $print['customer'] = $this->data['customer'];
        $print['payments'] = $this->data['payments'];
        $print['pos'] = $this->pos_model->getSetting();
        unset($print['pos']->pos_theme);
        $print['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $print['return_sale'] = !empty($this->data['return_sale']) ? $this->data['return_sale'] : null;
        $print['return_rows'] = $this->data['return_rows'];
        $print['inv'] = $inv;
        $print['warehouse'] = $this->data['warehouse'];
        $print['sid'] = $id;
        $print['modal'] = true;
        $print['page_title'] = 'Challan';
        $print['taxItems'] = $this->data['taxItems'];
        
        // Set product images
        if (!empty($print['rows'])) {
            foreach ($print['rows'] as $key => $row) {
                $product = $this->pos_model->getProductByID($row->product_id, $select = 'image');
                $print['rows'][$key]->image = $product->image;
            }
        }
        
        $print['brcode'] = $this->sma->save_barcode($inv->reference_no, 'code128', 66, false);
        $print['qrcode'] = $this->sma->qrcode('link', urlencode(site_url('sales/challan_view/' . $inv->id)), 2);
        $arr = explode("'", $print['brcode']);
        $print['brcode'] = $arr[1];
        $qrr = explode("'", $print['qrcode']);
        $print['qrcode'] = $qrr[1];
        
        foreach ($print['rows'] as $key => $row) {
            foreach ($row as $key2 => $value) {
                if ($key2 == 'quantity') {
                    $print['rows'][$key]->quantity = round($value, 2);
                }
                if ($key2 == 'unit_quantity') {
                    $print['rows'][$key]->quantity = round($value, 2);
                }
                if ($key2 == 'product_id') {
                    $product = $this->pos_model->getProductByID($value, $select = 'image');
                    $print['rows'][$key]->cf1 = $product->image;
                }
            }
        }
        
        // Send print data to Android handler
        if ($id != $_SESSION['print'] && (isset($_SESSION['print_type']) && $_SESSION['print_type'] == null)) {
            $row_taxes_print = $inv->rows_tax;
            unset($inv->rows_tax);
            $row_taxes_print_arr = array();
            if (count($row_taxes_print)) {
                foreach ($row_taxes_print as $_key => $_data) {
                    foreach ($_data as $_key1 => $value1) {
                        $row_taxes_print_arr[] = $value1;
                    }
                }
            }
            $inv->rows_tax = $row_taxes_print_arr;
            ?>
<script>
window.MyHandler.setPrintRequest('<?php echo json_encode($print); ?>');
</script>
<?php
            unset($print);
        }
        $_SESSION['print'] = $id;
    }

    public function challans($warehouse_id = null) {

        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id')) : NULL;
            $this->data['warehouse_id'] = $warehouse_id == null ? $this->session->userdata('warehouse_id') : $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id'));
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('challan')));
        $meta = array('page_title' => lang('Challans'), 'bc' => $bc);

        $this->page_construct('sales/challans', $meta, $this->data);
    }

    public function getChallans($warehouse_id = null) {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link1 = anchor('sales/challan_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link = anchor('sales/challan_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('challan_details'));
        //$duplicate_link = anchor('sales/add?chalan_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_sale'));
        $duplicate_link = anchor('sales/add_sale_from_chalan?challan_id=$1&syncQuantity=0&sale_action=chalan', '<i class="fa fa-plus-circle"></i> ' . lang('create_sale'), 'class="create-sale-link"');
        $payments_link = anchor('sales/paymentschallan/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_challan_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/emailchallan/$1', '<i class="fa fa-envelope"></i> ' . lang('Email Challan'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit_challan/$1', '<i class="fa fa-edit"></i> ' . lang('Edit_Challan'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf_challan/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_challan/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('Return Challan'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("Delete Challan") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_challan/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('Delete Challan') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link1 . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li class="link_$2">' . $add_payment_link . '</li>
            <li class="link_$2">' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li class="link_$2">' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';


        $this->load->library('datatables');
        $arrWr = [];
        if ($warehouse_id) {

            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, packing_no, challan_no, invoice_no, biller, customer, challan_status, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, attachment")
                    ->from('delivery_challan');

            $arrWr = explode(',', $warehouse_id);

            $this->datatables->where_in('warehouse_id', $arrWr);
        } else {
            $this->datatables
                    ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, packing_no, challan_no, invoice_no, biller, customer, challan_status, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, attachment")
                    ->from('delivery_challan');
        }

        $this->datatables->where('sale_as_chalan =', 1);


        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id,sale_status");

        echo $this->datatables->generate();
    }


    public function pdf_challan($id = null, $view = null, $save_bufffer = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->load->model('challan_model');
        $inv = $this->challan_model->getChallanByID($id);
        if (!$inv) {
            $this->session->set_flashdata('error', lang("Challan Not found"));
            redirect("sales/challans");
        }
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->challan_model->getAllTaxChallanItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->challan_model->getAllTaxItemsGroup($id, $inv->return_id);

        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->challan_model->getChallanPayments($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->challan_model->getAllChallanItems($id);

        $return_sales = $inv->return_id ? $this->challan_model->getAllReturnChallanByID($id) : NULL;
        $product_discount = 0; $product_tax = 0; $total = 0; $grand_total = 0; $order_discount = 0; $order_tax = 0; $paid = 0;
        if (!empty($return_sales)) {
            foreach ($return_sales as $Keys => $Vals) {
                $product_discount = $product_discount + $Vals['product_discount'];
                $product_tax = $product_tax + $Vals['product_tax'];
                $total = $total + $Vals['total'];
                $grand_total = $grand_total + $Vals['grand_total'];
                $order_discount = $order_discount + $Vals['order_discount'];
                $order_tax = $order_tax + $Vals['order_tax'];
                $paid = $paid + $Vals['paid'];
            }
            $this->data['return_sale'] = (object) array(
                'product_discount' => $product_discount,
                'product_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'order_tax' => $order_tax,
                'order_discount' => $order_discount,
                'paid' => $paid,
            );
        }
        $this->data['return_rows'] = $inv->return_id ? $this->challan_model->getAllReturnChallanItems($id) : NULL;

        $receipt_addresses = $this->challan_model->getChallanReceiptAddresses($inv, $this->data['customer']);
        if ($receipt_addresses) {
            $this->data = array_merge($this->data, $receipt_addresses);
        }

        $name = lang("challan") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf_challan', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_challan', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name, false);
        }
    }


    public function challan_view($Id = null, $modal = null) {
        $this->load->model('orders_model');
        $this->load->model('pos_model');

        $this->data['myclass'] = $ci = & get_instance();
        $this->data['pos_settingss'] = $this->site->get_pos_setting();
        // $this->sma->checkPermissions('sales');
        if ($this->input->get('id')) {
            $Id = $this->input->get('id');
        }

        $Settings = $this->Settings = $this->site->get_setting();

        $_PID = $this->Settings->default_printer;

        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);

        $this->load->helper('text');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $this->load->model('challan_model');
        $inv = $this->challan_model->getChallanByID($Id);
        
        if (!$inv) {
            $this->session->set_flashdata('error', lang("Challan Not found"));
            redirect("sales/challans");
        }

        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->challan_model->getAllTaxChallanItems($Id, $inv->return_id);
        endif;

        //$isGstSale = $this->site->isGstSale($Id);
        $inv->GstSale = $inv->product_tax == 0.0000 ? 0 : 1;

        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $print = array();
        $print['print_option'] = $this->site->defaultPrinterOption($_PID);
        $print['rows'] = $this->data['rows'] = $this->challan_model->getAllChallanItems($Id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $print['biller'] = $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $print['customer'] = $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $print['payments'] = $this->data['payments'] = $this->challan_model->getPaymentsForChallan($Id);
        $print['pos'] = $this->data['pos'] = $this->pos_model->getSetting();
        unset($print['pos']->pos_theme);
        $print['barcode'] = $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        //$print['return_sale'] = $this->data['return_sale'] = $inv->return_id ? $this->orders_model->getOrderByID($inv->return_id) : null;
        $return_sales = $inv->return_id ? $this->orders_model->getAllReturnOrderByID($Id) : NULL;
        //print_r($return_sales);
        //echo '<br>';
        $product_discount = 0;
        $product_tax = 0;
        $total = 0;
        $grand_total = 0;
        $order_discount = 0;
        $order_tax = 0;
        $paid = 0;
        $rounding = 0;
        $ArrReturnId = array();
        $ReturnIds = '';
        if (!empty($return_sales)) {
            foreach ($return_sales as $Keys => $Vals) {
                $product_discount = $product_discount + $Vals['product_discount'];
                $product_tax = $product_tax + $Vals['product_tax'];
                $total = $total + $Vals['total'];
                $rounding = $rounding + $Vals['rounding'];
                $grand_total = $grand_total + $Vals['grand_total'];
                $order_discount = $order_discount + $Vals['order_discount'];
                $order_tax = $order_tax + $Vals['order_tax'];
                $paid = $paid + $Vals['paid'];
                $ArrReturnId[] = $Vals['id'];
                //echo '<br/>';
            }
            $print['return_sale'] = $this->data['return_sale'] = (object) array(
                        'product_discount' => $product_discount,
                        'product_tax' => $product_tax,
                        'total' => $total,
                        'rounding' => $rounding,
                        'grand_total' => $grand_total,
                        'order_tax' => $order_tax,
                        'order_discount' => $order_discount,
                        'paid' => $paid,
            );
            $ReturnIds = "'" . implode("','", $ArrReturnId) . "'";
        }
        $print['return_rows'] = $this->data['return_rows'] = $inv->return_id ? $this->orders_model->getAllReturnOrderItems($Id) : NULL;
        //$print['return_rows'] = $this->data['return_rows'] = $inv->return_id ? $this->orders_model->getAllOrderItems($inv->return_id) : null;
        if (!empty($ArrReturnId)) {
            $print['return_payments'] = $this->data['return_payments'] = $this->data['return_sale'] ? $this->orders_model->getOrderPayments1($ReturnIds) : null;
        }
        //$print['return_payments'] = $this->data['return_payments'] = $this->data['return_sale'] ? $this->orders_model->getOrderPayments($this->data['return_sale']->id) : null;
        $print['inv'] = $this->data['inv'] = $inv;
        $print['sid'] = $this->data['sid'] = $Id;
        $print['modal'] = $this->data['modal'] = $modal;
        $print['page_title'] = $this->data['page_title'] = $this->lang->line("invoice");
        $print['taxItems'] = $this->data['taxItems'] = $this->orders_model->getAllTaxItemsGroup($inv->id, $inv->return_id);

        //Set Sale items image

        if (!empty($print['rows'])) {
            foreach ($print['rows'] as $key => $row) {
                $product = $this->pos_model->getProductByID($row->product_id, $select = 'image');
                $print['rows'][$key]->image = $product->image;
            }
        }

        $print['pos_type'] = $Settings->pos_type;

        $this->data['inv']->invoice_product_image = $Settings->invoice_product_image;

        $this->data['sms_limit'] = $this->sma->BalanceSMS();

        $this->data['show_kot'] = false;
        if (isset($Settings->pos_type) && $Settings->pos_type == 'restaurant'):
            $this->data['show_kot'] = true;
        endif;
        //////////////////////////////////// Whats app Challan receipt////////////////////
        $pos_settings_challan = $this->data['pos_settingss'];
        if (isset($pos_settings_challan->auto_print_receipt) && $pos_settings_challan->auto_print_receipt == 1) {
            $this->data['auto_print_receipt'] = true;
        }

        $default_country_name = isset($Settings->country_name) ? $Settings->country_name : '';
        $country_code = '';
        $phone_digits = 10;
        if (!empty($default_country_name)) {
            $country_info = $this->db->select('code, phone_digits')
                ->where('name', $default_country_name)
                ->get('country_master')
                ->row();
            if ($country_info) {
                $country_code = $country_info->code;
                $phone_digits = !empty($country_info->phone_digits) ? (int) $country_info->phone_digits : 10;
            }
        }
        $this->data['country_code'] = $country_code;
        $this->data['phone_digits'] = $phone_digits;

        $sales_sess = $this->session->userdata('Sales');
        if (!empty($sales_sess) && stripos($sales_sess, 'challans') !== false) {
            $this->data['print_redirect_url'] = site_url('sales/challans');
        } elseif (!empty($sales_sess)) {
            $this->data['print_redirect_url'] = site_url('sales');
        } else {
            $this->data['print_redirect_url'] = site_url('sales/challans');
        }

        $receipt_addresses = $this->challan_model->getChallanReceiptAddresses($inv, $this->data['customer']);
        if ($receipt_addresses) {
            $this->data = array_merge($this->data, $receipt_addresses);
            $print = array_merge($print, $receipt_addresses);
        }

        $this->load->view($this->theme . 'sales/view_challan', $this->data);

        $print['brcode'] = $this->sma->save_barcode($inv->reference_no, 'code128', 66, false);
        $print['qrcode'] = $this->sma->qrcode('link', urlencode(site_url('sales/challan_view/' . $inv->id)), 2);
        $arr = explode("'", $print['brcode']);
        $print['brcode'] = $arr[1];
        $qrr = explode("'", $print['qrcode']);
        $print['qrcode'] = $qrr[1];
        //echo $print['rows'][0]->net_unit_price;
        foreach ($print['rows'] as $key => $row) {
            //Set Sale items image.
            foreach ($row as $key2 => $value) {
                if ($key2 == 'quantity') {
                    $print['rows'][$key]->quantity = round($value, 2);
                }
                if ($key2 == 'unit_quantity') {
                    $print['rows'][$key]->quantity = round($value, 2);
                }
                if ($key2 == 'product_id') {
                    $product = $this->pos_model->getProductByID($value, $select = 'image');
                    $print['rows'][$key]->cf1 = $product->image;
                }
            }
        }

        if ($Id != $_SESSION['print'] && (isset($_SESSION['print_type']) && $_SESSION['print_type'] == null)) {
            $row_taxes_print = $inv->rows_tax;
            unset($inv->rows_tax);
            $row_taxes_print_arr = array();
            if (count($row_taxes_print)) {
                foreach ($row_taxes_print as $_key => $_data) {
                    foreach ($_data as $_key1 => $value1) {
                        $row_taxes_print_arr[] = $value1;
                    }
                }
            }
            $inv->rows_tax = $row_taxes_print_arr;
            ?>
            <script>
                window.MyHandler.setPrintRequest('<?php echo json_encode($print); ?>');
            </script>
            <?php
            unset($print);
        }
        $_SESSION['print'] = $Id;
    }

    public function email_challan_receipt($challan_id = null) {
        $this->sma->checkPermissions('index');

        if ($this->input->post('id')) {
            $challan_id = $this->input->post('id');
        }
        if (!$challan_id) {
            die('No challan selected.');
        }

        $to = $this->input->post('email');
        $this->load->model('challan_model');
        $this->load->model('pos_model');

        $inv = $this->challan_model->getChallanByID($challan_id);
        if (!$inv) {
            $this->sma->send_json(array('msg' => 'Invalid challan.'));
        }

        $biller = $this->pos_model->getCompanyByID($inv->biller_id);
        $customer = $this->pos_model->getCompanyByID($inv->customer_id);

        if (!$to && !empty($customer) && !empty($customer->email)) {
            $to = $customer->email;
        }
        if (!$to) {
            $this->sma->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }

        $attachment = $this->pdf_challan($challan_id, null, 'S');
        $receipt = 'Please find challan attachment';
        $subject_company = (!empty($biller) && !empty($biller->company)) ? $biller->company : $this->Settings->site_name;

        if ($this->sma->send_email($to, 'Challan from ' . $subject_company, $receipt, null, null, $attachment)) {
            if (!empty($attachment) && file_exists($attachment)) {
                @unlink($attachment);
            }
            $this->sma->send_json(array('msg' => $this->lang->line("email_sent")));
        } else {
            log_message('error', 'Email Challan Failed - Challan ID: ' . $challan_id . ', To: ' . $to);
            $this->sma->send_json(array('msg' => $this->lang->line("email_failed")));
        }
    }

    public function view_order($id = null) {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->orders_model->getOrderByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);

        $this->data['created_by'] = $this->site->getUser($inv->created_by);

        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->orders_model->getAllOrderItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->orders_model->getOrderByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->orders_model->getAllOrderItems($inv->return_id) : NULL;


        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->orders_model->getAllTaxOrderItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->orders_model->getAllTaxItemsGroup($id, $inv->return_id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $Settings = $this->site->get_setting();
        if (isset($Settings->pos_type) && $Settings->pos_type == 'pharma') {
            $this->page_construct('orders/view-sales-pharma', $meta, $this->data);
        } else {
            $this->page_construct('orders/view', $meta, $this->data);
        }
    }

    public function order_as_pdf($id = null, $view = null, $save_bufffer = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->orders_model->getOrderByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->orders_model->getAllTaxOrderItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->orders_model->getAllTaxItemsGroup($id, $inv->return_id);

        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->orders_model->getPaymentsForOrder($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->orders_model->getAllOrderItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->orders_model->getOrderByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->orders_model->getAllOrderItems($inv->return_id) : NULL;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $name = lang("sale") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'orders/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }


        if ($view) {
            $this->load->view($this->theme . 'orders/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer); //, $this->data['biller']->invoice_footer
        } else {
            $this->sma->generate_pdf($html, $name, false); //, $this->data['biller']->invoice_footer
        } /* echo */
    }

    public function delete_challan($id = null) {
        $this->sma->checkPermissions('delete', true, 'challan');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->load->model('challan_model');
        $inv = $this->challan_model->getChallanByID($id);

        if($inv->invoice_no){
            $this->session->set_flashdata('error', lang("Can't delete because a sale invoice has already been created against the challan."));
            $this->sma->md();
        }
        
        if ($inv->invoice_no) {
            $sale = $this->sales_model->getSaleByInvoiceNo($inv->invoice_no);
            $sale_id = $sale->id;
            $syncQuantity = ($sale->id) ? 0 : 1;
        } else {
            $syncQuantity = 1;
            $sale_id = null;
        }

        if ($inv->challan_status == 'returned') {
            $this->session->set_flashdata('error', lang('challan_x_action') ? lang('challan_x_action') : 'Cannot return  because a sale invoice has already been created against the challan');
            $this->sma->md();
        }

        $this->load->model('challan_model');
        if ($this->challan_model->deleteChallan($id, $syncQuantity, $sale_id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("challan_deleted") ? lang("challan_deleted") : 'Challan successfully deleted';
                die();
            }
            $this->session->set_flashdata('message', lang("challan_deleted") ? lang("challan_deleted") : 'Challan successfully deleted');
            redirect('sales/challans');
        }
    }

    public function add_sale_from_chalan($challan_id = null, $syncQuantity = 0, $sale_action = 'sale') {
        $this->sma->checkPermissions('add_sale', true, 'challan');
        //$this->load->helper('security');

        if ($this->input->get('order_id')) {
            $challan_id = $this->input->get('order_id');
        } elseif ($this->input->get('challan_id')) {
            $challan_id = $this->input->get('challan_id');
        }
        
        if ($this->input->get('syncQuantity')) {
            $syncQuantity = $this->input->get('syncQuantity');
        }
        if ($this->input->get('sale_action')) {
            $sale_action = $this->input->get('sale_action');
        }

        if (!$challan_id) {
            $this->session->set_flashdata('error', lang("id_not_found"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($challan_id) {

            $reference = $this->site->getReference('so');

            $date = date('Y-m-d H:i:s');

            $this->load->model('challan_model');
            $challan = $this->challan_model->getChallanByID($challan_id);

            if ($challan->challan_status == 'returned') {
                $this->session->set_flashdata('error', 'Cannot return  because a sale invoice has already been created against the challan');

                redirect('sales/challans');
            }

            if (!empty($challan->invoice_no)) {
                $this->session->set_flashdata('error', 'sale invoice has already been created against the challan');

                redirect('sales/challans');
            }

            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $challan->customer_id,
                'customer' => $challan->customer,
                'biller_id' => $challan->biller_id,
                'biller' => $challan->biller,
                'seller_id' => $challan->seller_id,
                'seller' => $challan->seller,
                'warehouse_id' => $challan->warehouse_id,
                'note' => $challan->note,
                'staff_note' => $challan->staff_note,
                'total' => $challan->total,
                'product_discount' => $challan->product_discount,
                'order_discount_id' => $challan->order_discount_id,
                'order_discount' => $challan->order_discount,
                'total_discount' => $challan->total_discount,
                'product_tax' => $challan->product_tax,
                'order_tax_id' => $challan->order_tax_id,
                'order_tax' => $challan->order_tax,
                'total_tax' => $challan->total_tax,
                'shipping' => $challan->shipping,
                'grand_total' => $challan->grand_total,
                'order_type' => $challan->order_type,
                'source' => $challan->source ? $challan->source : 'challan',
                'total_items' => $challan->total_items,
                'sale_status' => $challan->sale_status ? $challan->sale_status : $challan->challan_status,
                'payment_status' => $challan->payment_status,
                'payment_term' => $challan->payment_term,
                'due_date' => $challan->due_date,
                'created_by' => $this->session->userdata('user_id'),
                'paid' => $challan->paid,
                'cgst' => $challan->cgst,
                'sgst' => $challan->sgst,
                'igst' => $challan->igst,
                'order_no' => $challan->invoice_no,
            );

            $challanItems = $this->challan_model->getAllChallanItems($challan_id);
            if ($challanItems) {
                foreach ($challanItems as $key => $item) {

                    $products[] = array(
                        'product_id' => $item->product_id,
                        'product_code' => $item->product_code,
                        'article_code' => $item->article_code,
                        'product_name' => $item->product_name,
                        'product_type' => $item->product_type,
                        'option_id' => $item->option_id,
                        'net_unit_price' => $item->net_unit_price,
                        'unit_discount' => $item->unit_discount,
                        'unit_tax' => $item->unit_tax,
                        'invoice_unit_price' => $item->invoice_unit_price,
                        'invoice_net_unit_price' => $item->invoice_net_unit_price,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'net_price' => $item->net_price,
                        'invoice_total_net_unit_price' => $item->invoice_total_net_unit_price,
                        'warehouse_id' => $item->warehouse_id,
                        'item_tax' => $item->item_tax,
                        'tax_method' => $item->tax_method,
                        'tax_rate_id' => $item->tax_rate_id,
                        'tax' => $item->tax,
                        'discount' => $item->discount,
                        'item_discount' => $item->item_discount,
                        'subtotal' => $item->subtotal,
                        'serial_no' => $item->serial_no,
                        'real_unit_price' => $item->real_unit_price,
                        'product_unit_id' => $item->product_unit_id,
                        'product_unit_code' => $item->product_unit_code,
                        'unit_quantity' => $item->unit_quantity,
                        'cf1' => $item->cf1,
                        'cf2' => $item->cf2,
                        'cf1_name' => $item->cf1_name,
                        'cf2_name' => $item->cf2_name,
                        'mrp' => $item->mrp,
                        'hsn_code' => $item->hsn_code,
                        'note' => $item->note,
                        'delivery_status' => $item->delivery_status,
                        'pending_quantity' => $item->pending_quantity,
                        'delivered_quantity' => $item->delivered_quantity,
                        'gst_rate' => $item->gst_rate,
                        'cgst' => $item->cgst,
                        'sgst' => $item->sgst,
                        'igst' => $item->igst,
                    );
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("Challan Items Not found"));

                redirect("sales/challans");
            }
            $extrasPara = array('sale_action' => $sale_action, 'syncQuantity' => $syncQuantity, 'challan_id' => $challan_id);


            if ($sale_id = $this->sales_model->addSaleFromChallan($data, $products, $extrasPara)) {

                if ($challan->return_id) {
                    if ($this->add_sale_return_from_chalan_return($challan->return_id, $sale_id, $syncQuantity)) {
                        $this->session->set_flashdata('message', $this->lang->line("Delivery Challan and return items added to sales successfully"));
                        redirect("sales");
                    }
                } else {
                    $this->session->set_flashdata('message', $this->lang->line("Delivery Challan added to sale successfully"));
                    redirect("sales");
                }
            }
        }
    }

    public function add_sale_return_from_chalan_return($challan_return_id, $sale_id, $syncQuantity = 0) {

        $this->sma->checkPermissions('index', true);

        if ($challan_return_id) {

            $this->load->model('challan_model');
            $challanReturn = $this->challan_model->getChallanByID($challan_return_id);

            $sales = $this->sales_model->getInvoiceByID($sale_id);

            if (!empty($sales->return_id)) {
                $this->session->set_flashdata('message', 'Sale already returned');
                if ($syncQuantity) {
                    redirect('sales');
                }
            }

            $return_sale_ref = $this->site->getReference('re');

            $date = date('Y-m-d H:i:s');

            $data = array('date' => $date,
                'sale_id' => $sales->id,
                'invoice_no' => $sales->invoice_no,
                'reference_no' => $sales->reference_no,
                'return_sale_ref' => $return_sale_ref,
                'customer_id' => $challanReturn->customer_id,
                'customer' => $challanReturn->customer,
                'biller_id' => $challanReturn->biller_id,
                'biller' => $challanReturn->biller,
                'seller_id' => $challanReturn->seller_id,
                'seller' => $challanReturn->seller,
                'warehouse_id' => $challanReturn->warehouse_id,
                'note' => $challanReturn->note,
                'staff_note' => $challanReturn->staff_note,
                'total' => $challanReturn->total,
                'product_discount' => $challanReturn->product_discount,
                'order_discount_id' => $challanReturn->order_discount_id,
                'order_discount' => $challanReturn->order_discount,
                'total_discount' => $challanReturn->total_discount,
                'product_tax' => $challanReturn->product_tax,
                'order_tax_id' => $challanReturn->order_tax_id,
                'order_tax' => $challanReturn->order_tax,
                'total_tax' => $challanReturn->total_tax,
                'shipping' => $challanReturn->shipping,
                'grand_total' => $challanReturn->grand_total,
                'total_items' => $challanReturn->total_items,
                'sale_status' => $challanReturn->sale_status,
                'payment_status' => $challanReturn->payment_status,
                'payment_term' => $challanReturn->payment_term,
                'due_date' => $challanReturn->due_date,
                'created_by' => $challanReturn->created_by,
                'paid' => $challanReturn->paid,
                'cgst' => $challanReturn->cgst,
                'sgst' => $challanReturn->sgst,
                'igst' => $challanReturn->igst,
            );

            $salesItems = $this->sales_model->getSalesItemBySaleID($sale_id);

            if (is_array($salesItems)) {
                foreach ($salesItems as $item_id => $sitems) {
                    $salesProductsItems[$sitems->product_id] = $item_id;
                }
            }

            $challanItems = $this->challan_model->getAllChallanItems($challan_return_id);

            if ($challanItems) {
                foreach ($challanItems as $key => $item) {

                    $products[] = array(
                        'sale_item_id' => $salesProductsItems[$item->product_id],
                        'product_id' => $item->product_id,
                        'product_code' => $item->product_code,
                        'article_code' => $item->article_code,
                        'product_name' => $item->product_name,
                        'product_type' => $item->product_type,
                        'option_id' => $item->option_id,
                        'net_unit_price' => $item->net_unit_price,
                        'unit_discount' => $item->unit_discount,
                        'unit_tax' => $item->unit_tax,
                        'invoice_unit_price' => $item->invoice_unit_price,
                        'invoice_net_unit_price' => $item->invoice_net_unit_price,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'net_price' => $item->net_price,
                        'invoice_total_net_unit_price' => $item->invoice_total_net_unit_price,
                        'warehouse_id' => $item->warehouse_id,
                        'item_tax' => $item->item_tax,
                        'tax_method' => $item->tax_method,
                        'tax_rate_id' => $item->tax_rate_id,
                        'tax' => $item->tax,
                        'discount' => $item->discount,
                        'item_discount' => $item->item_discount,
                        'subtotal' => $item->subtotal,
                        'serial_no' => $item->serial_no,
                        'real_unit_price' => $item->real_unit_price,
                        'product_unit_id' => $item->product_unit_id,
                        'product_unit_code' => $item->product_unit_code,
                        'unit_quantity' => $item->unit_quantity,
                        'cf1' => $item->cf1,
                        'cf2' => $item->cf2,
                        'cf1_name' => $item->cf1_name,
                        'cf2_name' => $item->cf2_name,
                        'mrp' => $item->mrp,
                        'hsn_code' => $item->hsn_code,
                        'note' => $item->note,
                        'delivery_status' => $item->delivery_status,
                        'pending_quantity' => $item->pending_quantity,
                        'delivered_quantity' => $item->delivered_quantity,
                        'gst_rate' => $item->gst_rate,
                        'cgst' => $item->cgst,
                        'sgst' => $item->sgst,
                        'igst' => $item->igst,
                    );
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("Challan Return Items Not found"));

                redirect("sales");
            }
            $extrasPara = array('sale_action' => 'sale_return', 'syncQuantity' => $syncQuantity, 'sale_id' => $sale_id, 'challan_id' => $challan_return_id);

            if ($sale_return_id = $this->sales_model->addSaleReturnFromChallanReturn($data, $products, $extrasPara)) {
                return $sale_return_id;
            }
        }
    }

    public function barcode($text = null, $bcs = 'code128', $height = 50) {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    /*     * *************************************************************************
     * Sales Challans 
     * ************************************************************************* */

    /**
     * 
     * This method working for edit challan
     * @param type $id
     * 
     */
    public function edit_challan($id = null) {
        $this->sma->checkPermissions('edit', null, 'challan');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $saleType = '';
        if ($this->uri->segment(4))
            $saleType = $this->uri->segment(4);
        $this->load->model('challan_model');
        $inv = $this->challan_model->getChallanByID($id);

        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref || !empty($inv->invoice_no)) {
            $msg = !empty($inv->invoice_no) ? 'Sale already created for this challan. Editing is not allowed.' : lang('sale_x_action');
            $this->session->set_flashdata('error', $msg);
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }


        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('challan_status', lang("challan_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {
            
            $_ssot_mode = isset($this->pos_settings->sale_source_order_type_mode) ? (int) $this->pos_settings->sale_source_order_type_mode : 1;
            if ($_ssot_mode === 2 && trim((string) $this->input->post('order_type')) === '') {
                $this->session->set_flashdata('error', lang('sale_source_order_type_required'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales/add');
            }
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['sales-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $saleTypeInput = $this->input->post('saleType');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $challan_status = $this->input->post('challan_status');
            $payment_status = $this->input->post('payment_status');
            $delivery_status = $this->input->post('delivery_status');
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));

            // check billing address state code from address table if not then check customer state code from company table
            $customer_state_code = '';
            $selected_billing_address_id = $this->input->post('billing_address_id') ? (int) $this->input->post('billing_address_id') : 0;
           
            if ($selected_billing_address_id > 0) {
                $selected_billing_address = $this->db->get_where('addresses', array(
                    'id' => $selected_billing_address_id,
                    'company_id' => $customer_id,
                ), 1)->row();
                if (!empty($selected_billing_address) && !empty($selected_billing_address->state_code)) {
                    $customer_state_code = trim($selected_billing_address->state_code);
                }
            }
            if ($customer_state_code === '') {
                $default_billing_address = $this->db->get_where('addresses', array(
                    'company_id' => $customer_id,
                    'type' => 'Billing',
                    'is_default' => 1,
                ), 1)->row();
                if (!empty($default_billing_address) && !empty($default_billing_address->state_code)) {
                    $customer_state_code = trim($default_billing_address->state_code);
                } else {
                    $customer_state_code = !empty($customer_details->state_code) ? trim($customer_details->state_code) : '';
                }
            }
            
            if ((!empty($customer_state_code) && !empty($biller_details->state_code)) && $customer_state_code != trim($biller_details->state_code)) {
                $interStateTax = true;
            } else {
                $interStateTax = false;
            }


            // Sales Person
            $SalesPersonDetails = (isset($_POST['sales_person'])?$_POST['sales_person']:NULL);
            if($SalesPersonDetails){
                $ExplodeSalesPerson = explode('-', $SalesPersonDetails);
                $SellerId = $ExplodeSalesPerson[0];
                $SellerName = $ExplodeSalesPerson[1];
            } else {
                $SellerId = NULL;
                $SellerName = NULL;
            }
            // End Sales Person

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $sale_cgst = $sale_sgst = $sale_igst = 0;

            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];

                $hsn_code = $_POST['hsn_code'][$r];
                $hsn_code = ($hsn_code == 'null') ? '' : $hsn_code;

                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_packing_size  = isset($_POST['packing_size'][$r]) ? $_POST['packing_size'][$r] : 0;
                $item_mrp = $_POST['mrp'][$r];
                $item_cf1 = $_POST['cf1'][$r];
                $item_cf2 = $_POST['cf2'][$r];



                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $item_mrp = !empty($item_mrp) ? $item_mrp : $product_details->mrp;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }
                    $unit_discount = $pr_discount;
                    $item_unit_price_less_discount = $this->sma->formatDecimal($unit_price - $unit_discount, 6);
                    $item_net_price = $net_unit_price = $item_unit_price_less_discount;


                    /* $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                      $item_net_price = $unit_price; */

                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $unit_surcharge = 0;
                    $item_surcharge = NULL;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    $net_unit_price = $item_unit_price_less_discount;
                    $unit_price = $item_unit_price_less_discount;
                    $invoice_unit_price = $item_unit_price_less_discount;
                    $invoice_net_unit_price = ($item_unit_price_less_discount + $unit_discount);

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_method = $product_details->tax_method;
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";

                                $net_unit_price = $item_unit_price_less_discount;
                                $unit_price = $item_unit_price_less_discount + $item_tax;

                                $invoice_unit_price = $item_unit_price_less_discount;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;

                                $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                $unit_price = $item_unit_price_less_discount;

                                $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                            }

                            $unit_tax = $item_tax;
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";

                                $net_unit_price = $item_unit_price_less_discount;
                                $unit_price = $item_unit_price_less_discount + $item_tax;

                                $invoice_unit_price = $item_unit_price_less_discount;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;

                                $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                $unit_price = $item_unit_price_less_discount;

                                $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                            }

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity, 4);
                        $unit_tax = $item_tax;
                    }
                    
                    if (isset($tax_details)) {
                        $unit_surcharge_val = $this->sma->calcSaleItemUnitSurcharge($tax_details, $unit_tax);
                        if ($unit_surcharge_val !== NULL && (float) $unit_surcharge_val != 0) {
                            $unit_surcharge = $this->sma->formatDecimal($unit_surcharge_val, 6);
                            $item_surcharge = $this->sma->formatDecimal(($unit_surcharge * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity), 6);

                            if ($product_details && $tax_method == 1) {
                                // Exclusive
                                $unit_price += $unit_surcharge;
                                $invoice_net_unit_price += $unit_surcharge;
                            } else {
                                // Inclusive
                                $net_unit_price -= $unit_surcharge;
                                $item_net_price = isset($item_net_price) ? ($item_net_price - $unit_surcharge) : $net_unit_price;
                                $invoice_unit_price -= $unit_surcharge;
                            }
                        }
                    }

                    if($interStateTax) {
                        $item_gst = $tax_details->rate;
                        $item_cgst = 0;
                        $item_sgst = 0;
                        $item_igst = $pr_item_tax;
                    } else {
                        $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                        $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_igst = 0;
                    }

                    $invoice_unit_price = $this->sma->formatDecimal($invoice_unit_price, 4);
                    $invoice_net_unit_price = $this->sma->formatDecimal($invoice_net_unit_price, 4);
                    $invoice_total_net_unit_price = $this->sma->formatDecimal(($invoice_net_unit_price * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_quantity), 4);
                    $product_tax += $pr_item_tax;
                    if ($item_surcharge !== NULL) {
                        $product_tax += (float) $item_surcharge;
                    }
                    if ($item_packing_size > 0) {
                        $subtotal = $this->sma->formatDecimal((($unit_price * $item_packing_size) * $item_unit_quantity), 4);
                        $net_unit_price = $this->sma->formatDecimal(($real_unit_price * $item_packing_size) - $pr_discount, 4);
                    } else {
                        $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        if ($item_surcharge !== NULL) {
                            $subtotal += $item_surcharge;
                        }
                    }
                    $unit = $this->site->getUnitByID($item_unit);
                    $net_price = $this->sma->formatDecimal(($item_mrp * $item_quantity), 4);
                    $batch_number       = isset($_POST['batch_number'][$r]) ? $_POST['batch_number'][$r] : null;
                    $sale_item_discount = $this->sma->getSaleItemDiscountForStorage(null, $item_discount, $item_mrp, $real_unit_price);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $unit_price,
                        'surcharge' => $item_surcharge,
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        // 'discount' => $item_discount,
                        'discount' => $sale_item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'mrp' => $item_mrp,
                        'hsn_code' => $hsn_code,
                        'cf1' => $item_cf1,
                        'cf2' => $item_cf2,
                        'cf1_name' => 'Exp. Date',
                        'cf2_name' => 'Batch No.',
                        'net_price' => $net_price,
                        'tax_method' => $tax_method,
                        'unit_discount' => $unit_discount,
                        'unit_tax' => $unit_tax,
                        'gst_rate'  => $item_gst,
                        'cgst'       => $item_cgst,
                        'sgst'       => $item_sgst,
                        'igst'        => $item_igst,
                        'invoice_unit_price' => $invoice_unit_price,
                        'invoice_net_unit_price' => $invoice_net_unit_price,
                        'invoice_total_net_unit_price' => $invoice_total_net_unit_price,
                        'batch_number'      => $batch_number,
                        'packing_size'      => $item_packing_size,
                    );

                    $sale_cgst += $item_cgst;
                    $sale_sgst += $item_sgst;
                    $sale_igst += $item_igst;

                    if ($item_packing_size > 0) {
                        $total += $this->sma->formatDecimal(($item_net_price * $item_packing_size * $item_unit_quantity), 4);
                    } else {
                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    }
                }
            }
            if (empty($products)) {

    $this->form_validation->set_rules('product', lang("order_items"), 'required');

} else {

    // Same flow as add sale

    $sale_items = $products;

    unset($products);

    foreach ($sale_items as $key => $item) {

        // Sort internal array keys only
        ksort($item);

        $products[] = $item;
    }
}
            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                /* $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                  $ods = explode("%", $order_discount_id);
                  $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                  } else {
                  $order_discount = $this->sma->formatDecimal($order_discount_id);
                  } */
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);

            /* 12-6-2019 */
            $rounding = '';

            if ($this->pos_settings->rounding > 0) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = ($round_total - $grand_total);
            }
            /*             * **** */
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'shipping_address_id' => $this->input->post('shipping_address_id') ? $this->input->post('shipping_address_id') : NULL,
                'billing_address_id' => $this->input->post('billing_address_id') ? $this->input->post('billing_address_id') : NULL,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'seller_id' => $SellerId,
                'seller' => $SellerName,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'cgst' => $sale_cgst,
                'sgst' => $sale_sgst,
                'igst' => $sale_igst,
                'challan_status' => $challan_status,
                'sale_status' => $challan_status,
                'delivery_status' => $delivery_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'rounding' => $rounding,
                'due_date' => $due_date,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'transporter_mode' => $this->input->post('transporter_mode'),
                'LR_No' => $this->input->post('LR_No'),
                'total_parcels' => $this->input->post('total_parcels'),
                'place_of_supply' => $this->input->post('place_of_supply'),
                'way_bill_no' => $this->input->post('way_bill_no'),
                'packing_no' => $this->input->post('packing_no'),
                'order_type' => $this->input->post('order_type'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->challan_model->updateChallan($id, $data, $products)) {

            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("sale_updated"));
            redirect('sales/challans');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->challan_model->getChallanByID($id);
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("sale_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->challan_model->getAllChallanItems($id);

            try {
                $user = $this->site->getUser();
                $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
                if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                    $salesperson_details = $this->site->getSalesPersonsByWarehouse($user_warehouses);
                } else {
                    $salesperson_details = $this->site->getCompanyDetailsByGroupID(5);
                }
            } catch (Exception $e) {
                $salesperson_details = $this->site->getCompanyDetailsByGroupID(5);
            }

            krsort($inv_items);
            $pos_settings = $this->site->get_pos_setting();
            $item_order = isset($pos_settings->item_order) ? (int)$pos_settings->item_order : 0;
            $order_multiplier = ($item_order == 0) ? -1 : 1;
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {

                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $row->quantity = 0; // Reset before summing warehouse stock from purchase records
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }

                $unitData = $this->sales_model->getUnitById($row->unit);
                $row->unit_lable = $unitData->name;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $category_data = $this->pos_model->getCategoryIdByName($row->category_id);
                $row->category_name = $category_data ? $category_data->name : '';
                if ($this->data['inv']->challan_status == 'completed') {
                    $row->quantity += $item->quantity;
                }
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                $row->unit_price = ($row->tax_method ) ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->packing_size = $item->packing_size;
                $row->tax_rate = $item->tax_rate_id;
                $row->serial = $item->serial_no;
                $row->option = $item->option_id;
                $row->delivery_status = $item->delivery_status;
                $row->delivered_qty = $item->delivered_quantity;
                $row->pending_qty = $item->pending_quantity;
                $row->net_unit_price = $item->net_unit_price;
                $row->option_color      = $item->shade_id;
                $row->seller_id         = $item->seller_id;
                $row->batch_number      = $item->batch_number;
                // $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                // $options = $this->sales_model->getProductOptionsByAttr($row->id, $item->warehouse_id, 1);
                $options_color = $this->sales_model->getProductoptioncolor($item->shade_id);
                $option_id = $item->option_id;
                $row->edit = 'edit';

                $options = FALSE;
                if ($this->Settings->attributes == 1 && ($row->storage_type == 'packed' || ($row->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1))) {
                    $options = $this->sales_model->getProductOptionsByAttr($row->id, $item->warehouse_id, 1);
                    if (!$options) {
                        $options = $this->sales_model->getProductVariantsWithoutcolor($row->id, null, 1);
                    }
                }

                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($this->data['inv']->challan_status == 'completed') {
                            $option_quantity += $item->quantity;
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                        $option->price = $item->real_unit_price;
                        $row->price = 0;
                    }
                }
                if ($options_color) {
                    $opt_color = current($options_color);
                    if (!empty($opt_color)) {
                    $option_color_id = $opt_color->id;
                    $option_color_name = $opt_color->name;
                    $row->option_color_name = $option_color_name;
                }
                } else {
                    $opt_color = json_decode('{}');
                }
                
                if($this->Settings->product_batch_setting) {
                    $productbatches = $this->products_model->getProductVariantsBatch($row->id,$warehouse_id);
                    if($productbatches){
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id);
                        if ($pis) {
                            $row->quantity_total = $option_quantity = 0;
                            foreach ($pis as $pi) {
                                $row->quantity_total += $pi->quantity_balance;
                                if($options !== false && $option_id == $pi->option_id){
                                    $option_quantity += $pi->quantity_balance;
                                    if($pi->batch_number && isset($productbatches[$pi->option_id])){
                                        foreach($productbatches[$pi->option_id] as $batch_id=>$optBatch){
                                            if($optBatch->batch_no == $pi->batch_number){
                                                $productbatches[$pi->option_id][$batch_id]->quantity += $pi->quantity_balance;  
                                            }
                                        }                                                      
                                    }  
                                } else {
                                    if($pi->batch_number && isset($productbatches[0])){
                                        foreach($productbatches[0] as $batch_id=>$optBatch){
                                            if($optBatch->batch_no == $pi->batch_number){
                                                $productbatches[0][$batch_id]->quantity += $pi->quantity_balance;  
                                            }
                                        }                                                      
                                    }  
                                }//end else
                            }//end foreach
                        }//end if $pis
                        
                    }//end if $productbatches
                    $batch_option = ($options !== false) ? $option_id : 0;
                    $batch = isset($productbatches[$batch_option]) ? $productbatches[$batch_option] : false;
                    if ($batch) {
                        $firstKey = key($batch);
                        foreach ($batch as $batch_id => $optBatch) {
                            if ($optBatch->batch_no == $item->batch_number) {
                                $firstKey = $batch_id;
                                break;
                            }
                        }
                        $batchoption = $batch;
                        $row->batch          = $batchoption[$firstKey]->id;
                        // $row->batch_number   = $batchoption[$firstKey]->batch_no;
                         $row->batch_number      = !empty($item->batch_number)? $item->batch_number: $batchoption[$firstKey]->batch_no;
                        $row->batch_quantity = $batchoption[$firstKey]->quantity;
                        $row->expiry         = ($batchoption[$firstKey]->expiry != '' && $batchoption[$firstKey]->expiry !== '0000-00-00') ? $batchoption[$firstKey]->expiry : '';
                    } else {
                        $batchoption = false;
                        $row->batch = false;
                        $row->batch_number = '';
                        $row->batch_quantity = 0;   
                    }
                }
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                if ($row->type == 'Bundle') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                // category leve tax 
                $categoryTax = $this->sales_model->getCategoryTax((($row->subcategory_id)?$row->subcategory_id :$row->category_id));
                if($categoryTax){
                    if($categoryTax->fix_tax_rate=='fix tax'){
                        $gettax =  $this->site->getTaxRateByID($categoryTax->tax_rate);
                        $fixtaxrate = $gettax->id.'~'.$gettax->rate;
                        $varaibletax = False;
                    } else {
                        $varaibletax = $this->sales_model->getVariableTax((($row->subcategory_id)?$row->subcategory_id :$row->category_id));
                        $fixtaxrate = False;
                    }
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                
                $row_id = $row->id . ($option_id ? $option_id : '');
                // $row_id .= ($row->batch) ? $row->batch : ''; // If batch is needed for unique id
                $ri = $this->Settings->item_addition ? $row_id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row_id, 'image' => $row->image, 'label' => $row->name . " (" . $row->code . ")",
                    'fixtax'=> $fixtaxrate,'category_tax'=> $varaibletax,'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'cf1' => $row->cf1, 'cf2' => $row->cf2, 'units' => $units,'batchs' => $batchoption, 'options' => $options,'product_batches' => $productbatches, 'options_color' => $options_color, 'salesperson_details' => $salesperson_details, 'order' => $order_multiplier * (int) $item->id, 'category' => $row->category_id, 'sub_category' => $row->subcategory_id, 'divisionid' => $row->divisionid, 'brand' => $row->brand);
                $c++;
            }



            $this->data['inv_items'] = json_encode($pr);
            $this->data['eshop_sale'] = $inv->eshop_sale;
            $this->data['id'] = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['sale_type'] = $saleTypeInput ? $saleTypeInput : $saleType;

            try {
                $user = $this->site->getUser();
                $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
                if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                    $this->data['salesperson_details'] = $this->site->getSalesPersonsByWarehouse($user_warehouses);
                } else {
                    $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
                }
            } catch (Exception $e) {
                $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
            }

            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $shipping_addr = $this->site->getCustomerAddressById($inv->shipping_address_id);
            $billing_addr = $this->site->getCustomerAddressById($inv->billing_address_id);
            $this->data['default_shipping_address_id'] = $shipping_addr['id'];
            $this->data['default_shipping_address_text'] = $shipping_addr['label'];
            $this->data['default_billing_address_id'] = $billing_addr['id'];
            $this->data['default_billing_address_text'] = $billing_addr['label'];
            $this->data['pos_settings'] = $this->data['Pos_settings'] = $this->site->get_pos_setting();
            $this->data['order_types'] = $this->site->getOrderTypes();
            $this->data['sale_action'] = 'chalan';
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('Edit Challan')));
            $meta = array('page_title' => lang('Edit Challan'), 'bc' => $bc);

            $this->page_construct('sales/edit_challan', $meta, $this->data);
        }
    }

    public function return_challan($id = null) {
        $this->sma->checkPermissions('return', null, 'challan');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $challan = $this->challan_model->getChallanByID($id);
        if (!$challan) {
            $this->session->set_flashdata('error', lang("Challan Not found"));
            redirect("sales/challans");
        }
        
        if ($challan->return_id) {
            $this->session->set_flashdata('error', lang("challan_already_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if (!empty($challan->invoice_no)) {
            $this->session->set_flashdata('error', 'Challan cannot be returned because a Sale has already been created for it.');
            redirect('sales/challans');
        }

        $customer_details = $this->site->getCompanyByID($challan->customer_id);
        $biller_details = $this->site->getCompanyByID($challan->biller_id);

        if ((!empty($customer_details->state_code) && !empty($biller_details->state_code)) && $customer_details->state_code != $biller_details->state_code) {
            $interStateTax = true;
        } else {
            $interStateTax = false;
        }

        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re_ordr');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $sale_cgst = $sale_sgst = $sale_igst = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                if ($_POST['quantity'][$r] > 0) {
                    $item_id = $_POST['product_id'][$r];
                    $item_type = $_POST['product_type'][$r];
                    $item_code = $_POST['product_code'][$r];
                    $item_name = $_POST['product_name'][$r];
                    $sale_item_id = $_POST['sale_item_id'][$r];
                    $batch_number = isset($_POST['batch_number'][$r]) ? $_POST['batch_number'][$r] : null;
                    $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                    $product_option_color = $_POST['product_option_color'][$r];
                    $item_packing_size  = isset($_POST['packing_size'][$r]) ? $_POST['packing_size'][$r] : 0;
                    $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                    $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                    $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                    $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                    $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_unit = $_POST['product_unit'][$r];
                    $item_quantity = (0 - $_POST['product_base_quantity'][$r]);
                    $category_id = $_POST['cat_id'][$r]; // Multiple Category
                    $item_mrp = $_POST['mrp'][$r];
                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details = $this->site->getProductByCode($item_code);
                        $item_mrp = !empty($item_mrp) ? $item_mrp : $product_details->mrp;
                        $item_mrp = $this->sma->formatDecimal($item_mrp);
                        $pr_discount = 0;
                        $unit_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount, 4);
                            }
                        }
                        $unit_discount = $pr_discount;
                        $item_unit_price_less_discount = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                        $unit_price = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                        $item_net_price = $unit_price;
                        $pr_item_discount = $this->sma->formatDecimal($pr_discount * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity, 4);
                        $product_discount += $pr_item_discount;
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $unit_tax = 0;
                        $item_tax = 0;
                        $tax = "";
                        $tax_method = '';
                        $net_unit_price = $item_unit_price_less_discount;
                        $unit_price = $item_unit_price_less_discount;
                        $invoice_unit_price = $item_unit_price_less_discount;
                        $invoice_net_unit_price = ($item_unit_price_less_discount + $unit_discount);
                        $unit_surcharge = 0;
                        $item_surcharge = NULL;

                        $pr_tax = (isset($item_tax_rate) && $item_tax_rate != 0) ? $item_tax_rate : $product_details->tax_rate;
                        if ($pr_tax && $pr_tax != 0) {
                            $tax_method = $product_details->tax_method;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }
                            } elseif ($tax_details->type == 2) {
                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;
                                $unit_tax = $item_tax;
                                $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                            }
                            $unit_tax = $item_tax;
                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity), 4);

                            if (isset($tax_details)) {
                                $unit_surcharge_val = $this->sma->calcSaleItemUnitSurcharge($tax_details, $unit_tax);
                                if ($unit_surcharge_val !== NULL && (float) $unit_surcharge_val != 0) {
                                    $unit_surcharge = $this->sma->formatDecimal($unit_surcharge_val, 6);
                                    $item_surcharge = $this->sma->formatDecimal(($unit_surcharge * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity), 6);

                                    if ($product_details && $tax_method == 1) {
                                        // Exclusive
                                        $unit_price += $unit_surcharge;
                                        $invoice_net_unit_price += $unit_surcharge;
                                    } else {
                                        // Inclusive
                                        $net_unit_price -= $unit_surcharge;
                                        $item_net_price = isset($item_net_price) ? ($item_net_price - $unit_surcharge) : $net_unit_price;
                                        $invoice_unit_price -= $unit_surcharge;
                                    }
                                }
                            }
                        }

                        if ($interStateTax) {
                            $item_gst = $tax_details->rate;
                            $item_cgst = 0;
                            $item_sgst = 0;
                            $item_igst = $pr_item_tax;
                        } else {
                            $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                            $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                            $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                            $item_igst = 0;
                        }

                        $product_tax += $pr_item_tax;
                        if ($item_surcharge !== NULL) {
                            $product_tax += (float) $item_surcharge;
                        }
                        $sale_cgst += $item_cgst;
                        $sale_sgst += $item_sgst;
                        $sale_igst += $item_igst;
                        $subtotal = $this->sma->formatDecimal((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
                        if ($item_surcharge !== NULL) {
                            $subtotal += $item_surcharge;
                        }
                        
                        if ($item_packing_size > 0) {
                            $subtotal = $this->sma->formatDecimal((($unit_price * $item_packing_size) * $item_unit_quantity), 4);
                            $net_unit_price = $this->sma->formatDecimal(($real_unit_price * $item_packing_size) - $pr_discount, 4);
                        }
                        
                        $unit = $this->site->getUnitByID($item_unit);

                        $unit_discount = 0 - $this->sma->formatDecimal($unit_discount, 4);
                        $unit_tax = 0 - $this->sma->formatDecimal($unit_tax, 4);
                        $invoice_unit_price = $this->sma->formatDecimal($invoice_unit_price, 4);
                        $invoice_net_unit_price = $this->sma->formatDecimal($invoice_net_unit_price, 4);
                        $invoice_total_net_unit_price = $this->sma->formatDecimal(($invoice_net_unit_price * ($item_packing_size > 0 ? $item_packing_size : 1) * $item_unit_quantity), 4);
                        $net_price = $this->sma->formatDecimal(($item_mrp * $item_unit_quantity), 4);


                        $products[] = array(
                            'product_id' => $item_id,
                            'product_code' => $item_code,
                            'product_name' => $item_name,
                            'product_type' => $item_type,
                            'packing_size' => $item_packing_size,
                            'article_code' => $product_details->article_code,
                            'hsn_code' => $product_details->hsn_code,
                            'option_id' => $item_option,
                            'batch_number' => $batch_number,
                            'shade_id' => $product_option_color,
                            'net_unit_price' => $item_net_price,
                            'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax + $unit_surcharge),
                            'quantity' => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity' => $item_unit_quantity,
                            'warehouse_id' => $challan->warehouse_id,
                            'item_tax' => $pr_item_tax,
                            'surcharge' => $item_surcharge,
                            'tax_rate_id' => $pr_tax,
                            'tax' => $tax,
                            'discount' => $item_discount,
                            'item_discount' => $pr_item_discount,
                            'subtotal' => $this->sma->formatDecimal($subtotal),
                            'serial_no' => $item_serial,
                            'real_unit_price' => $real_unit_price,
                            'challan_item_id' => $sale_item_id,
                            'mrp' => $item_mrp,
                            'tax_method' => $tax_method,
                            'unit_discount' => $unit_discount,
                            'unit_tax' => $unit_tax,
                            'invoice_unit_price' => $invoice_unit_price,
                            'invoice_net_unit_price' => $invoice_net_unit_price,
                            'invoice_total_net_unit_price' => $invoice_total_net_unit_price,
                            'net_price' => $net_price,
                            'category_id' => $category_id,
                            'gst_rate' => $item_gst,
                            'cgst' => $item_cgst,
                            'sgst' => $item_sgst,
                            'igst' => $item_igst,
                        );

                        $si_return[] = array(
                            'id' => $sale_item_id,
                            'challan_id' => $id,
                            'packing_size' => $item_packing_size,
                            'product_id' => $item_id,
                            'option_id' => $item_option,
                            'batch_number' => $batch_number,
                            'quantity' => (0 - $item_quantity),
                            'warehouse_id' => $challan->warehouse_id,
                        );

                        if ($item_packing_size > 0) {
                            $total += $this->sma->formatDecimal(($item_net_price * $item_packing_size * $item_unit_quantity), 4);
                        } else {
                            $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                        }
                    }
                }
            }
            
           
            if (empty($products)) {

    $this->form_validation->set_rules('product', lang("order_items"), 'required');

} else {

    // Same flow as add sale

    $sale_items = $products;

    unset($products);

    foreach ($sale_items as $key => $item) {

        // Sort internal array keys only
        ksort($item);

        $products[] = $item;
    }
}

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = '-' . $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = '-' . $this->sma->formatDecimal($order_discount_id, 4);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = '-' . $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal($product_tax + $order_tax, 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);
            
            // Apply rounding to grand_total (PHP equivalent of roundNumberNEW)
            if (!empty($this->pos_settings)) {
                $rounding = $this->pos_settings->rounding;
                if ($rounding > 0) {
                    $abs_grand_total = abs($grand_total);
                    switch ($rounding) {
                        case 1:
                            $round_total = round($abs_grand_total * 20) / 20;
                            break;
                        case 2:
                            $round_total = round($abs_grand_total * 2) / 2;
                            break;
                        case 3:
                            $round_total = round($abs_grand_total);
                            break;
                        case 4:
                            $round_total = ceil($abs_grand_total);
                            break;
                        default:
                            $round_total = $abs_grand_total;
                            break;
                    }
                    $grand_total = $this->sma->formatDecimal(($grand_total < 0 ? -1 * $round_total : $round_total));
                }
            }
            
            $data = array('date' => $date,
                'challan_id' => $id,
                'packing_no' => $challan->packing_no,
                'reference_no' => $challan->reference_no,
                'seller_id' => $challan->seller_id,
                'seller' => $challan->seller,
                'customer_id' => $challan->customer_id,
                'customer' => $challan->customer,
                'biller_id' => $challan->biller_id,
                'biller' => $challan->biller,
                'warehouse_id' => $challan->warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->sma->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_challan_ref' => $reference,
                'challan_status' => 'returned',
                'sale_status' => 'returned',
                'payment_status' => 'pending',
                'total_items' => (0 - ($this->input->post('total_items'))),
                'cgst' => $sale_cgst,
                'sgst' => $sale_sgst,
                'igst' => $sale_igst,
            );
            
            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
                $pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
                $payment = array(
                    'date' => $date,
                    'reference_no' => $pay_ref,
                    'amount' => (0 - $this->input->post('amount-paid')),
                    'paid_by' => $this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => ($this->input->post('gift_card_no') != '') ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'returned',
                );
                $data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
            } else {
                $payment = array();
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            
            // For addChallan compatibility
            $extrasPara = array('sale_action' => 'return', 'syncQuantity' => 1, 'challan_id' => $id);
            
            if ($this->form_validation->run() == true && $this->challan_model->addChallan($data, $products, $payment, $si_return, $extrasPara)) {
                $this->session->set_flashdata('message', lang("Return challan add"));
                
                if ($this->input->post('paid_by') == 'gift_card') {
                    $expiry_Date = date("Y-m-d", strtotime("+3 month"));
                    $gift_amount = $payment['amount'];
                    $giftcard_field = array(
                        'date' => date('Y-m-d H:i:s'),
                        'card_no' => $this->input->post('gift_card_no'),
                        'value' => abs($gift_amount),
                        'customer_id' => $data['customer_id'],
                        'customer' => $data['customer'],
                        'balance' => abs($gift_amount),
                        'expiry' => $expiry_Date,
                        'created_by' => $this->session->userdata('user_id'),
                    );
                    $gift_id = $this->sales_model->CreateGiftCard($giftcard_field);
                    if ($gift_id) { $this->session->set_flashdata('giftcard_id', $gift_id); }
                }

                // Revert reward points logic
                $company = $this->site->getCompanyByID($challan->customer_id);
                $points = floor(($challan->grand_total / $this->Settings->each_spent) * $this->Settings->ca_point);
                $_points = floor((($challan->grand_total + $challan->return_sale_total) / $this->Settings->each_spent) * $this->Settings->ca_point);
                if ($points > $_points && $_points != 0) {
                    $total_points = $company->award_points - ($points - $_points);
                    $this->db->update('companies', array('award_points' => $total_points), array('id' => $challan->customer_id));
                } elseif ($_points == 0) {
                    $total_points = $company->award_points - ($points);
                    $this->db->update('companies', array('award_points' => $total_points), array('id' => $challan->customer_id));
                }

                redirect('sales/challans');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($challan->date <= date('Y-m-d', strtotime('-3 months'))) {
                $this->session->set_flashdata('error', lang("sale_x_edited_older_than_3_months"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $inv_items = $this->challan_model->getAllChallanItems($id);
            krsort($inv_items);
            $pos_settings = $this->site->get_pos_setting();
            $item_order = isset($pos_settings->item_order) ? (int)$pos_settings->item_order : 0;
            $order_multiplier = ($item_order == 0) ? -1 : 1;
            
            $c = rand(100000, 9999999); $pr = array();
            foreach ($inv_items as $key => $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}'); $row->tax_method = 0; $row->quantity = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) { foreach ($pis as $pi) { $row->quantity += $pi->quantity_balance; } }
                
                $row->id = $item->product_id;
                $row->sale_item_id = $item->id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = 0;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->unit_quantity;
                $qty_multiplier = ($item->packing_size > 0 ? $item->packing_size : 1) * $item->quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $qty_multiplier));
                $row->unit_price = ($item->tax_method == 0) ? $this->sma->formatDecimal($item->unit_price + ($item->item_discount / $qty_multiplier)) : $this->sma->formatDecimal($item->net_unit_price + ($item->item_discount / $qty_multiplier));
                $row->net_unit_price = $this->sma->formatDecimal($item->net_unit_price);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->serial = $item->serial_no;
                $row->option = $item->option_id;
                $row->option_color = $item->shade_id;
                $row->category_id = $item->category_id;
                $row->mrp = $item->mrp;
                $row->item_tax = $item->item_tax;
                $row->seller_id = $item->seller_id;
                $row->seller = $item->seller;
                $row->packing_size = $item->packing_size;
                
                $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true, SIZE);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                
                $row_id = $row->id . ($item->option_id ? $item->option_id : '');
                $ri = $this->Settings->item_addition ? $row_id : $c;
                
                $options_color = $this->sales_model->getProductOptionsByAttr($row->id, $item->warehouse_id, COLOR);
                $pr[$ri.$key] = array('id' => $ri.$key, 'item_id' => $row_id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options, 'options_color' => $options_color, 'order' => $order_multiplier * (int) $item->id);
                $c++;
            }
            $this->data['inv'] = $challan;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['payment_ref'] = '';
            $this->data['reference'] = $this->site->getReference('re_ordr');
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['payments'] = $this->challan_model->getChallanPayments($id);
            
            // Load sales persons for sales person functionality
            try {
                $user = $this->site->getUser();
                $user_warehouses = $user->warehouse_id ? explode(',', $user->warehouse_id) : array();
                if (method_exists($this->site, 'getSalesPersonsByWarehouse')) {
                    $this->data['salesperson_details'] = $this->site->getSalesPersonsByWarehouse($user_warehouses);
                } else {
                    // Fallback to original method if new method doesn't exist
                    $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
                }
            } catch (Exception $e) {
                // Fallback to original method if there's any error
                $this->data['salesperson_details'] = $this->site->getCompanyDetailsByGroupID(5);
            }
            
            $this->data['pos_settings'] = $this->data['Pos_settings'] = $this->site->get_pos_setting();
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => site_url('sales/challans'), 'page' => lang('challans')), array('link' => '#', 'page' => lang('return_challan')));
            $meta = array('page_title' => lang('return_challan'), 'bc' => $bc);
            $this->page_construct('sales/return_challan', $meta, $this->data);
        }
    }

    /**
     * Show callan Payments 
     * 
     * @param type $id
     */
    public function paymentschallan($id = null) {
        $this->sma->checkPermissions('payments', true, 'challan');
        $this->data['payments'] = $this->challan_model->getChallanPayments($id);
        $this->data['inv'] = $this->challan_model->getChallanByID($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    /**
     * This method using add challan payment
     * 
     * @param type $id
     */
    public function add_challan_payment($id = null) {
        $this->sma->checkPermissions('add_payment', true, 'challan');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getChallanByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang("sale_already_paid"));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->sales_model->getChallanByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'challan_id' => $this->input->post('challan_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'transaction_id' => $this->input->post('transaction_id'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id, 'chalan')) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->sma->md();
            }
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    /**
     * Challan Email
     * @param type $id
     */
    public function emailchallan($id = null) {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getChallanByID($id);
        $this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $paypal = $this->sales_model->getPaypalSettings();
            $skrill = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == "1" && $inv->grand_total != "0.00") {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . site_url('sales/view/' . $inv->id) . '&cancel_return=' . site_url('sales/view/' . $inv->id) . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            }
            if ($skrill->active == "1" && $inv->grand_total != "0.00") {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('sales/view/' . $inv->id) . '&cancel_url=' . site_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div>
    </div>';
            $message = $message . $btn_code;

            $attachment = $this->challanpdf($id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->session->set_flashdata('message', lang("email_sent_msg"));
            // redirect("sales");
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/views/email_templates/sale.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('Order') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $sale_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    /**
     *  Challan PDF
     * @param type $id
     * @param type $view
     * @param type $save_bufffer
     * @return type
     */
    public function challanpdf($id = null, $view = null, $save_bufffer = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->challan_model->getChallanByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);
        if ($this->data['default_printer']->tax_classification_view):
            $inv->rows_tax = $this->challan_model->getAllTaxItems($id, $inv->return_id);
        endif;
        $this->data['taxItems'] = $this->challan_model->getAllTaxItemsGroup($id, $inv->return_id);

        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->challan_model->getChallanPayments($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
       
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->challan_model->getAllChallanItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->challan_model->getChallanByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->challan_model->getAllChallanItems($inv->return_id) : NULL;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $receipt_addresses = $this->challan_model->getChallanReceiptAddresses($inv, $this->data['customer']);
        if ($receipt_addresses) {
            $this->data = array_merge($this->data, $receipt_addresses);
        }

        $name = lang("Challan") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf_challan', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }


        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_challan', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer); //, $this->data['biller']->invoice_footer
        } else {
            $this->sma->generate_pdf($html, $name, false); //, $this->data['biller']->invoice_footer
        } /* echo */
    }

    /*     * *************************************************************************
     * End Sales Challans
     * ************************************************************************* */
    /*     * *02-09-2020** */
    /* ------------------------------------ Gift Cards ---------------------------------- */

    public function credit_note() {
        $this->sma->checkPermissions();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('Credit_Note')));
        $meta = array('page_title' => lang('Credit_Note'), 'bc' => $bc);
        $this->page_construct('sales/credit_note', $meta, $this->data);
    }

    public function getCreditNote() {

        $this->load->library('datatables');
        $this->datatables
                ->select($this->db->dbprefix('credit_note') . ".id as id, card_no, value, balance, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by, IF(sma_companies.company='-' OR sma_companies.company=' ',sma_companies.name ,sma_companies.company), expiry", false)
                ->join('users', 'users.id=credit_note.created_by', 'left')
                ->join('sma_companies', 'sma_companies.id=credit_note.customer_id', 'left')
                ->from("credit_note")
                ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('sales/view_credit_note/$1') . "' class='tip' title='" . lang("View_Credit_Note") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('sales/topup_credit_note/$1') . "' class='tip' title='" . lang("Topup_Credit_Note") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . site_url('sales/history_credit_note/$1') . "' class='tip' title='" . lang("History_Credit_Note") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-history\"></i></a> <a href='" . site_url('sales/edit_credit_note/$1') . "' class='tip' title='" . lang("Edit_Credit_Note") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("Delete_Credit_Note") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_credit_note/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function view_credit_note($id = null) {
        $this->data['page_title'] = lang('Credit_Note');
        $gift_card = $this->site->getCreditNoteByID($id);
        $this->data['gift_card'] = $this->site->getCreditNoteByID($id);
        $this->data['customer'] = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups'] = $this->sales_model->getAllCreditNoteTopups($id);
        $this->load->view($this->theme . 'sales/view_credit_note', $this->data);
    }

    /* 21-11-2019 Show the History Gift Card */

    public function history_credit_note($id = null) {
        $this->data['page_title'] = lang('Credit_Note');
        $gift_card = $this->site->getCreditNoteByID($id);
        $this->data['historycreditnote'] = $this->sales_model->getCreditNoteHistoryByID($gift_card->customer_id, $gift_card->card_no);
        $this->load->view($this->theme . 'sales/creditnote_history', $this->data);
    }

    /*     * */

    public function topup_credit_note($card_id) {
        //$this->sma->checkPermissions('add_gift_card', true);
        $card = $this->site->getCreditNoteByID($card_id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = array('card_id' => $card_id,
                'amount' => $this->input->post('amount'),
                'date' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id'),
            );
            $card_data['balance'] = ($this->input->post('amount') + $card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->sma->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/credit_note");
        }

        if ($this->form_validation->run() == true && $this->sales_model->topupCreditNote($data, $card_data)) {
            $this->session->set_flashdata('message', lang("topup_added"));
            redirect("sales/credit_note");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['card'] = $card;
            $this->data['page_title'] = lang("Topup_Credit_Note");
            $this->load->view($this->theme . 'sales/topup_credit_note', $this->data);
        }
    }

    public function add_credit_note() {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|is_unique[credit_note.card_no]|required');
        $this->form_validation->set_rules('value', lang("value"), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            if ($customer == '-' || empty($customer)) :
                $customer = $customer_details->name;
            endif;
            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => $this->input->post('value'),
                'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                'created_by' => $this->session->userdata('user_id'),
            );
            $sa_data = array();
            $ca_data = array();
            if ($this->input->post('staff_points')) {
                $sa_points = $this->input->post('sa_points');
                $user = $this->site->getUser($this->input->post('user'));
                if ($user->award_points < $sa_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/credit_note");
                }
                $sa_data = array('user' => $user->id, 'points' => ($user->award_points - $sa_points));
            } elseif ($customer_details && $this->input->post('use_points')) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/credit_note");
                }
                $ca_data = array('customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points));
            }
            // $this->sma->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_credit_note')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/credit_note");
        }

        if ($this->form_validation->run() == true && $this->sales_model->addCreditNote($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang("Credit_note_added"));
            redirect("sales/credit_note");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['users'] = $this->sales_model->getStaff();
            $this->data['page_title'] = lang("New_Credit_Card");
            $this->load->view($this->theme . 'sales/add_credit_note', $this->data);
        }
    }

    public function edit_credit_note($id = null) {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
        $gc_details = $this->site->getCreditNoteByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'is_unique[credit_note.card_no]');
        }
        $this->form_validation->set_rules('value', lang("value"), 'required');
        //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card = $this->site->getCreditNoteByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->name : null;
            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
                'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
            );
        } elseif ($this->input->post('edit_credit_note')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/credit_note");
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateCreditNote($id, $data)) {
            $this->session->set_flashdata('message', lang("Credit_Note_updated"));
            redirect("sales/credit_note");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getCreditNoteByID($id);
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_credit_note', $this->data);
        }
    }

    public function sell_credit_note() {
        //$this->sma->checkPermissions('gift_cards', true);
        $error = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang("value") . " " . lang("is_required");
        }
        if (empty($gcData[1])) {
            $error = lang("card_no") . " " . lang("is_required");
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer = $customer_details ? $customer_details->company : null;
        $data = array('card_no' => $gcData[0],
            'value' => $gcData[1],
            'customer_id' => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer' => $customer,
            'balance' => $gcData[1],
            'expiry' => (!empty($gcData[3])) ? $this->sma->fsd($gcData[3]) : null,
            'created_by' => $this->session->userdata('user_id'),
        );

        if (!$error) {
            if ($this->sales_model->addCreditNote($data)) {
                $this->sma->send_json(array('result' => 'success', 'message' => lang("Credit_Note_added")));
            }
        } else {
            $this->sma->send_json(array('result' => 'failed', 'message' => $error));
        }
    }

    public function delete_credit_note($id = null) {
        $this->sma->checkPermissions();

        if ($this->sales_model->deleteCreditNote($id)) {
            echo lang("Credit_Note_deleted");
        }
    }

    public function credit_note_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    //$this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteCreditNote($id);
                    }
                    $this->session->set_flashdata('message', lang("Credit_Note_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,), 'font' => array('name' => 'Arial', 'color' => array('rgb' => 'FF0000')), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE, 'color' => array('rgb' => 'FF0000'))));

                    $this->excel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($style);
                    $this->excel->getActiveSheet()->mergeCells('A1:E1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Gift Cards');
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));

                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('value'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('Balance'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('Expiry'));

                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getCreditNoteByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, ' ' . $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, '' . $sc->value);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, '' . $sc->balance);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->expiry);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $this->excel->getDefaultStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $filename = 'credit_note_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_credit_note_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------------- */

    public function validate_credit_note($no) {
        //$this->sma->checkPermissions();
        if ($gc = $this->site->getCreditNoteByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->sma->send_json($gc);
                } else {
                    $this->sma->send_json(false);
                }
            } else {
                $this->sma->send_json($gc);
            }
        } else {
            $this->sma->send_json(false);
        }
    }

    /*     * *02-09-2020** */

    /**
     * 
     * @param type $no
     */
    public function checkCreaditNo($no) {
        $result = $this->sales_model->checkCreaditNo($no);
        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Card No allready create, Please enter another card no',
            ];
        } else {
            $response = [
                'status' => false,
            ];
        }
        $this->sma->send_json($response);
    }



        /**
     * Send Purchase Excel For Customer
     */
      public function export_excel() {
        $_SESSION['Send_Excel'] = 0;
        $_SESSION['sale_id'] = '';
        $id = $this->uri->segment(3);
        $inv = $this->pos_model->getInvoiceByID($id);
        $sale_item = $this->sales_model->getAllInvoiceItems($id);
        $customer_id = $inv->customer_id;
        $customer = $this->pos_model->getCompanyByID($customer_id);
        $to = $customer->email;
        if ($this->input->get('email'))
            $to = $this->input->get('email');

        
//        echo  '<pre>';
//        print_r($sale_item);
//        exit;
        
        if ($to != '') {
            $this->load->library('excel');
            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle(lang('sales'));
            $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
            $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
            $this->excel->getActiveSheet()->SetCellValue('C1', lang('style'));
            $this->excel->getActiveSheet()->SetCellValue('D1', lang('net_unit_cost'));
            $this->excel->getActiveSheet()->SetCellValue('E1', lang('quantity'));
            $this->excel->getActiveSheet()->SetCellValue('F1', lang('variant'));
            $this->excel->getActiveSheet()->SetCellValue('G1', lang('Tax_Percent'));
            $this->excel->getActiveSheet()->SetCellValue('H1', lang('Tax_Type'));
            $this->excel->getActiveSheet()->SetCellValue('I1', lang('discount'));
            $this->excel->getActiveSheet()->SetCellValue('J1', lang('expiry'));
            $this->excel->getActiveSheet()->SetCellValue('K1', lang('category_code'));
            $this->excel->getActiveSheet()->SetCellValue('L1', lang('subcategory_code'));
            $this->excel->getActiveSheet()->SetCellValue('M1', lang('brand'));
            $this->excel->getActiveSheet()->SetCellValue('N1', lang('unit'));
            $this->excel->getActiveSheet()->SetCellValue('O1', lang('price'));
            $this->excel->getActiveSheet()->SetCellValue('P1', lang('MRP_Price'));
            $this->excel->getActiveSheet()->SetCellValue('Q1', lang('Alert_Quantity'));
            $this->excel->getActiveSheet()->SetCellValue('R1', lang('hsn_code'));
            $this->excel->getActiveSheet()->SetCellValue('S1', lang('warehouse'));

            $row = 2;


            foreach ($sale_item as $item) {
            
                $options_color = ''; //$this->sales_model->getProductOptionsByShapeId($item->shade_id, $item->product_id, COLOR);

                $tax_rate = $this->pos_model->getTaxRateByID($item->tax_rate_id);
               
                $product_type =  ''; //$this->sales_model->getProduct_typeByID($item->type_code);

                $product_details = $this->sales_model->getProductByCode($item->product_code);
              
                $categoey_code = $this->sales_model->getCategoryCode($product_details->category_id);
               
                $subcategory_code = $this->sales_model->getCategoryCode($product_details->subcategory_id);
              
                $brand_code = $this->sales_model->getProductBrand($product_details->brand);
                $unit_code = $this->sales_model->getUnitById($product_details->unit);

               
                //echo $product_type->product_type_name;
                //print_r($product_type);
                $this->excel->getActiveSheet()->setCellValueExplicit('A' . $row, $item->product_code, PHPExcel_Cell_DataType::TYPE_STRING);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product_details->name);
                $this->excel->getActiveSheet()->setCellValueExplicit('C' . $row, $item->article_code, PHPExcel_Cell_DataType::TYPE_STRING);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $item->real_unit_price);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $item->quantity);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $item->variant);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $tax_rate->name);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $item->tax_method);		
                $this->excel->getActiveSheet()->setCellValueExplicit('I' . $row, $item->discount, PHPExcel_Cell_DataType::TYPE_STRING);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, '');

                $this->excel->getActiveSheet()->SetCellValue('K' . $row, ($categoey_code) ? $categoey_code->code . '|' . $categoey_code->name : '');
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, ($subcategory_code) ? $subcategory_code->code . '|' . $subcategory_code->code : '');
                $this->excel->getActiveSheet()->SetCellValue('M' . $row, ($brand_code) ? $brand_code->code . '|' . $brand_code->name : '');
                $this->excel->getActiveSheet()->SetCellValue('N' . $row, ($unit_code) ? $unit_code->code : '');
                $this->excel->getActiveSheet()->SetCellValue('O' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $item->mrp);
                $this->excel->getActiveSheet()->SetCellValue('Q' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('R' . $row, $item->hsn_code);
                $this->excel->getActiveSheet()->SetCellValue('S' . $row, '');

                $row++;
            }

          
            $filename = 'sales_' . date('Y_m_d_H_i_s');

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="assets/' . $filename . '.xls"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save(str_replace(__FILE__, 'assets/' . $filename . '.xls', __FILE__));
            $attachment = 'assets/' . $filename . '.xls';
            $attachment1 = $this->pdf($id, null, 'S');
            $multi_attach = array($attachment, $attachment1);
            $subject = 'Purchase Excel';
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            );
            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/views/email_templates/sale.html');
            }

            $message = $this->parser->parse_string($sale_temp, $parse_data);

            if ($this->sma->send_email($to, $subject, $message, null, null, $multi_attach)) {
                $this->sma->send_json(array('msg' => $this->lang->line("email_sent")));
            } else {
                $this->sma->send_json(array('msg' => $this->lang->line("email_failed")));
            }
            unlink($attachment);
        }
    }

    ///////////////////////////////////////// Multiple Biller invoice No//////////////////////////////////////
    public function get_biller_details() {
        $warehouse_id = $this->input->get('warehouse_id');
        if ($warehouse_id) {
            $biller_ids = $this->site->getBillerByWarehouseId($warehouse_id); 
            $billers = $this->site->getBillerByIds($biller_ids);
            $primer_biller = $this->site->getPrimaryBillerById($warehouse_id);

            if ($billers) {
                $options = [];
                foreach ($billers as $biller) {
                    $options[] = [
                        'id' => $biller->id,
                        'name' => $biller->name
                    ];
                }
                echo json_encode([
                    'success' => true,
                    'billers' => $options,
                    'primer_biller' => $primer_biller
                ]);
            } else {
                $all_billers = $this->site->getAllBillers('biller');
                echo json_encode([
                    'success' => false,
                    'all_billers' => $all_billers,
                    'message' => 'No billers found in that location.'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid warehouse ID']);
        }
    }

 
    private function format_phone_with_country_code($phone) {
        if (empty($phone)) {
            return $phone;
        }

        $cleaned_phone = preg_replace('/[^0-9]/', '', $phone);
        $default_country_name = isset($this->Settings->country_name) ? $this->Settings->country_name : '';

        if (!empty($default_country_name)) {
            $country_info = $this->db->select('code')
                ->where('name', $default_country_name)
                ->get('country_master')
                ->row();

            if ($country_info && !empty($country_info->code)) {
                $clean_country_code = str_replace('+', '', $country_info->code);
                if (!empty($clean_country_code) && strpos($cleaned_phone, $clean_country_code) !== 0) {
                    return $clean_country_code . $cleaned_phone;
                }
            }
        }

        return $cleaned_phone;
    }
    public function get_customer_addresses() {
        $company_id = $this->input->get('company_id');
        if ($company_id) {
            $addresses = $this->site->getCustomerBillingShippingAddresses($company_id);
            echo json_encode(array(
                'success' => true,
                'shipping' => !empty($addresses['shipping']) ? $addresses['shipping'] : array(),
                'billing' => !empty($addresses['billing']) ? $addresses['billing'] : array(),
            ));
        } else {
            echo json_encode(array('success' => false, 'shipping' => array(), 'billing' => array()));
        }
    }

    /**
     * HTML fragment for shared customer address modal (lazy-loaded on sale/challan + buttons).
     */
    public function address_add_modal() {
        $this->load->view($this->theme . 'customers/add_address_modal');
    }









}

//end class
