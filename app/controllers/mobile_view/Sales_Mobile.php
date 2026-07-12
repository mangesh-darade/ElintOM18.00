<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Mobile-optimized controller for Sales.
 * Reuses same backend logic as Sales via same model.
 * Does not modify any existing Sales logic.
 */
class Sales_Mobile extends MY_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('welcome'));
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

    /**
     * Mobile sales listing page.
     * Reuses same data as Sales::all_sale_lists but loads all_sale_listing_mobile view.
     */
    public function all_sale_lists() {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $this->input->post('warehouse') ? $this->input->post('warehouse') : NULL;
            $this->data['warehouse'] = $this->data['warehouse_id'] ? $this->site->getWarehouseByID($this->data['warehouse_id']) : NULL;
        } else {
            $this->data['warehouses'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id')) : NULL;
            $this->data['warehouse_id'] = $this->input->post('warehouse') ? $this->input->post('warehouse') : $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->data['warehouse_id'] ? $this->site->getWarehouseByID($this->data['warehouse_id']) : $this->site->getWarehouseByIDs($this->session->userdata('warehouse_id'));
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('sales') . ' (Mobile)', 'bc' => $bc);
        $this->page_construct('mobile_view/sales/all_sale_listing_mobile', $meta, $this->data);
    }

    /**
     * Same as Sales::all_sale_lists_filter.
     * Returns sales list for DataTables with mobile-optimized columns.
     */
    public function all_sale_lists_filter($pdf = NULL, $xls = NULL) {
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;
        $sale_status = $this->input->get('sale_status') ? $this->input->get('sale_status') : NULL;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : NULL;
        $sale_type = $this->input->get('sale_type') ? $this->input->get('sale_type') : NULL;
        $grand_total = $this->input->get('grand_total') ? $this->input->get('grand_total') : NULL;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        $created_by = $this->input->get('created_by') ? $this->input->get('created_by') : NULL;
        $created_by = $this->input->get('created_by') ? $this->input->get('created_by') : NULL;
        $grand_total1 = $this->input->get('grand_total1') ? $this->input->get('grand_total1') : NULL;
        $grand_total2 = $this->input->get('grand_total2') ? $this->input->get('grand_total2') : NULL;
        $paid_total1 = $this->input->get('paid_total1') ? $this->input->get('paid_total1') : NULL;
        $paid_total2 = $this->input->get('paid_total2') ? $this->input->get('paid_total2') : NULL;
        $due_total1 = $this->input->get('due_total1') ? $this->input->get('due_total1') : NULL;
        $due_total2 = $this->input->get('due_total2') ? $this->input->get('due_total2') : NULL;
        $total_items1 = $this->input->get('total_items1') ? $this->input->get('total_items1') : NULL;
        $total_items2 = $this->input->get('total_items2') ? $this->input->get('total_items2') : NULL;
        $TypeOfModeSale = $this->input->get('TypeOfModeSale') ? $this->input->get('TypeOfModeSale') : NULL;

        if ($pdf || $xls) {
            $this->db
                ->select("date, reference_no, invoice_no, biller, customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id, if(pos=1, 'POS', if(offline_sale=1, 'Offline', if(eshop_sale=1, 'Eshop', if(up_sales=1, 'up_sales', 'Sale')))) as type")
                ->from('sales');

            if ($warehouse) {
                $this->db->where('warehouse_id', $warehouse);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
                $this->db->join('sale_items', 'sale_items.sale_id=sales.id');
            }
            if ($biller) {
                $this->db->where('biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('customer_id', $customer);
            }
            if ($reference_no) {
                $this->db->like('reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales') . '.date >=', $start_date);
            }
            if ($end_date) {
                $this->db->where($this->db->dbprefix('sales') . '.date <=', $end_date);
            }
            if ($sale_status) {
                $this->db->where('sale_status', $sale_status);
            }
            if ($payment_status) {
                $this->db->where('payment_status', $payment_status);
            }
            if ($grand_total1) {
                $this->db->where('grand_total >=', $grand_total1);
            }
            if ($grand_total2) {
                $this->db->where('grand_total <=', $grand_total2);
            }
            if ($paid_total1) {
                $this->db->where('paid >=', $paid_total1);
            }
            if ($paid_total2) {
                $this->db->where('paid <=', $paid_total2);
            }
            if ($due_total1) {
                $this->db->where('(grand_total-paid) >=', $due_total1);
            }
            if ($due_total2) {
                $this->db->where('(grand_total-paid) <=', $due_total2);
            }
            if ($total_items1) {
                $this->db->where('total_items >=', $total_items1);
            }
            if ($total_items2) {
                $this->db->where('total_items <=', $total_items2);
            }
            if ($sale_type) {
                $this->db->where('sale_type', $sale_type);
            }
            if ($TypeOfModeSale) {
                $this->db->where('TypeOfModeSale', $TypeOfModeSale);
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
                $this->excel->getActiveSheet()->setTitle(lang('sales'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('sale_status'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->sale_status);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->payment_status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $filename = 'sales_' . date('Y_m_d_H_i_s');
                if ($pdf) {
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
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
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('welcome'));
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

        $this->load->library('datatables');
        $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, " . $this->db->dbprefix('sales') . ".date as date, " . $this->db->dbprefix('sales') . ".reference_no as reference_no, " . $this->db->dbprefix('sales') . ".invoice_no as invoice_no, " . $this->db->dbprefix('sales') . ".biller as biller, " . $this->db->dbprefix('sales') . ".customer as customer, " . $this->db->dbprefix('sales') . ".sale_status as sale_status, " . $this->db->dbprefix('sales') . ".grand_total as grand_total, " . $this->db->dbprefix('sales') . ".paid as paid, (grand_total-paid) as balance, " . $this->db->dbprefix('sales') . ".payment_status as payment_status, " . $this->db->dbprefix('sales') . ".attachment as attachment, " . $this->db->dbprefix('sales') . ".return_id as return_id, if(pos=1, 'POS', if(offline_sale=1, 'Offline', if(eshop_sale=1, 'Eshop', if(up_sales=1, 'up_sales', 'Sale')))) as type", FALSE)
                ->from('sales');

        if ($user) {
            $this->datatables->where('sales.created_by', $user);
        }
        if ($product) {
            $this->datatables->join('sale_items', 'sale_items.sale_id=sales.id', 'left');
            $this->datatables->where('sale_items.product_id', $product);
        }
        if ($serial) {
            $this->datatables->join('sale_items', 'sale_items.sale_id=sales.id', 'left');
            $this->datatables->like('sale_items.serial_no', $serial, 'both');
        }
        if ($biller) {
            $this->datatables->where('sales.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('sales.customer_id', $customer);
        }
        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->like('sales.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date >=', $start_date);
        }
        if ($end_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date <=', $end_date);
        }
        if ($sale_status) {
            $this->datatables->where('sales.sale_status', $sale_status);
        }
        if ($payment_status) {
            $this->datatables->where('sales.payment_status', $payment_status);
        }
        if ($grand_total1) {
            $this->datatables->where('sales.grand_total >=', $grand_total1);
        }
        if ($grand_total2) {
            $this->datatables->where('sales.grand_total <=', $grand_total2);
        }
        if ($paid_total1) {
            $this->datatables->where('sales.paid >=', $paid_total1);
        }
        if ($paid_total2) {
            $this->datatables->where('sales.paid <=', $paid_total2);
        }
        if ($due_total1) {
            $this->datatables->where('(grand_total-paid) >=', $due_total1);
        }
        if ($due_total2) {
            $this->datatables->where('(grand_total-paid) <=', $due_total2);
        }
        if ($total_items1) {
            $this->datatables->where('sales.total_items >=', $total_items1);
        }
        if ($total_items2) {
            $this->datatables->where('sales.total_items <=', $total_items2);
        }
        if ($sale_type) {
            $this->datatables->where('sales.sale_type', $sale_type);
        }
        if ($TypeOfModeSale) {
            $this->datatables->where('sales.TypeOfModeSale', $TypeOfModeSale);
        }

        echo $this->datatables->generate();
    }
}
