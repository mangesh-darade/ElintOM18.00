<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CheckStock extends MY_Controller {

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
        $this->load->model('CheckStock_model');
        $this->load->model('products_model');
        $this->load->library('form_validation');
    }

    public function index() {
        if (!$this->Owner && !$this->GP['products-stock_check']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if($this->session->userdata('group_id')!='1'){
            $this->data['warehouse'] =   $this->CheckStock_model->getUserWarehouse();
        }else{    
           $this->data['warehouse'] = $this->site->getAllWarehouses();
        }
        $this->data['category'] = $this->site->getAllCategories();
        $bc = array(array('link' => base_url(), 'page' => lang('Stock Check')), array('link' => '#', 'page' => lang('Stock Check ')));
        $meta = array('page_title' => lang('Check Stock'), 'bc' => $bc);
        $this->page_construct('checkStock/index', $meta, $this->data);
    }

    public function getStockReport() {
        $warehouse = $_POST['warehouse'];
        $product = array_count_values($_POST['scanproduct']);
        $notProcducts = '';
        if ($_POST['allcategory'] == 'true') {
            $response = $this->CheckStock_model->getStock($warehouse, $product);
        } else {
            $categorys = $_POST['category'];
            $response = $this->CheckStock_model->getCategoryProduct($warehouse, $product, $categorys);
        }
        $result = '<div > <div id="note" class="alert alert-danger"></div> <button type="button" class="btn btn-primary pull-right " id="btnExport" onclick="exportTableToExcel();"> <i class="fa fa-file-excel-o" aria-hidden="true"></i>
            Export </button>';
         if ($warehouse) {
            $result .= ' <button type="button" onclick="bulk_adjustment()" class="btn btn-success pull-right"> <i class="fa fa-adjust" aria-hidden="true"></i> Bulk Adjustment</button>';
         }
        $result .= '<button type="button" class="btn btn-primary pull-right " id="printbtn" onclick="print_list()" style="margin-bottom: 1em;"><i class="fa fa-print"></i> Print</button></div><div id="showproductlist"><h3 style="text-align:center; font-size:20px; font-weight:bold;">  Stock Check</h3><table id="product_table" class="table table-bordered producttable"><thead>';
        $result.='<tr >';
        if ($warehouse) {
            $result .='<th class="hidecheckbox"><input class="checkbox checkft" onclick="multiplecheck()" type="checkbox" id="selectall" name="check"/></th>';
        }
        $result .='<th style="text-align: left;">Sr. No. </th>';
        if ($warehouse) {
            $result.='<th style="text-align: left;">Warehouse</th>';
        }
        $result.='<th style="text-align: left;">Product Code</th>';
        $result.='<th style="text-align: left;" >Product Name</th>';
        $result.='<th style="text-align: left;" >Style Code</th>';
        $result.='<th style="text-align: left;" >Size</th>';
        $result.='<th style="text-align: left;" >Color</th>';
        if (isset($categorys)) {
            $result.='<th style="text-align: left;" >Category</th>';
        }
        $result.='<th style="text-align: left;" >Scan Product</th>';
        $result.='<th style="text-align: left;">System Stock</th>';
        $result.='<th style="text-align: left;">Difference Qty</th>';
        $result.='<th style="text-align: left;">Add Adjustment</th>';
        $result.='<th style="text-align: left;"><i class="fa fa-times"></i></th>';
        $result.='</tr></thead>';
        $result.='<tbody>';
        foreach ($response as $key => $response_value) {
            $difference = 0;
            $qyt = 0;
            $variant = 0;
            $type = "'addition'";
            $difference = $response_value['product_scan'] - $response_value['Stock'];
            if ($difference < 0) {
                $type = "'subtraction'";
            } else {
                $type = "'addition'";
            }
            $qyt = "'" . abs($difference) . "'";
            $variant = "'" . $response_value['variant_id'] . "'";
            $pass = $response_value['product_id']."~".$type.'~'.$qyt.'~'.$variant.'~'.$warehouse;
            $linecount = ($key + 1);         
            $result.='<tr id="id_'.$key.'">';
            if ($warehouse) {
                 if(isset($response_value['hidebutton']) && $response_value['hidebutton'] =='hide'){
                      $result.='<td></td>';
                  }else {
                       if($response_value['product_name'] =='-- Product dose not belongs to the system --'){
                          $result.='<td></td>';
                          $notProcducts.= 'Product Code: '.$response_value['product_barcode'].' Line No : '.$linecount.'<br/> ';
                      } else{
                      $result .='<td class="hidecheckbox"><input class="check_add_adjustment" name="addAdjustment" value="'.str_replace("'","",$pass).'" type="checkbox"></td>';
                      }
                  }
             }
            $result .='<td>' . ($key + 1) . '</td>';
            if ($warehouse) {
                $result .='<td>' . $response_value['warehouse'] . '</td>';
            }
            $result .='<td>' . $response_value['product_barcode'] . '</td>';
            $result .='<td>' . $response_value['product_name'] . '</td>';
            $result .='<td>' . $response_value['style_code'] . '</td>';
            $result .='<td>' . $response_value['size'] . '</td>';
            $result .='<td>' . $response_value['color'] . '</td>';
            if (isset($categorys)) {
                $result .='<td>' . $response_value['category_name'] . '</td>';
            }
            $result .='<td>' . $response_value['product_scan'] . '</td>';
            $result .='<td>' . $this->sma->formatQuantity($response_value['Stock']) . '</td>';
           if( $response_value['product_name'] =='-- Product dose not belongs to the system --'){
                $result.='<td> </td>';
                $result.='<td> </td>';
            }else{
                 $result.='<td>' . $difference . '</td>';
                  if(isset($response_value['hidebutton']) && $response_value['hidebutton'] =='hide'){
                     $result.='<td> </td>';
                 }else{
                     $result.='<td class="text-center"><button onclick="add_adjustment(' . $response_value['product_id'] . ',' . $type . ',' . $qyt . ',' . $variant . ',' . $warehouse . ')"' . (($warehouse) ? '' : 'disabled') . ' type="button" class="btn btn-xs btn-primary"> Add </button></td>';
                 }
            }
           /* $result.='<td>' . $difference . '</td>';
            $result.='<td class="text-center"><button onclick="add_adjustment(' . $response_value['product_id'] . ',' . $type . ',' . $qyt . ',' . $variant . ',' . $warehouse . ')"' . (($warehouse) ? '' : 'disabled') . ' type="button" class="btn btn-xs btn-primary"> Add </button></td>';*/
             $result.='<td><button style="cursor: pointer;" class="removerow btn btn-xs btn-danger" data-item="id_'.$key.'" onclick="removerow('.$key.')"><i class="fa fa-times"></i></button></td>';
            $result.='</tr>';
        }
        $result.='</tbody></table></div>';
        $result.='<div id="notData">'.$notProcducts .'</div>';
        echo json_encode($result);
    }

    public function add_adjustment() {
       // $this->sma->checkPermissions('adjustments', TRUE);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        if ($this->form_validation->run() == TRUE) {
            $date = date('Y-m-d H:i:s');
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;
                if (!$this->Settings->overselling && $type == 'subtraction') {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $response = [
                                    'status' => FALSE,
                                    'messages' => lang('warehouse_option_qty_is_less_than_damage'),
                                ];
                            }
                        } else {
                            $response = [
                                'status' => FALSE,
                                'messages' => lang('warehouse_option_qty_is_less_than_damage'),
                            ];
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $response = [
                                'status' => FALSE,
                                'messages' => lang('warehouse_qty_is_less_than_damage'),
                            ];
                        }
                    } else {
                        $response = [
                            'status' => FALSE,
                            'messages' => lang('warehouse_qty_is_less_than_damage'),
                        ];
                    }
                }
                $products[] = array('product_id' => $product_id, 'type' => $type, 'quantity' => $quantity, 'warehouse_id' => $warehouse_id, 'option_id' => $variant,);
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }
            $data = array('date' => $date, 'reference_no' => $reference_no, 'warehouse_id' => $warehouse_id, 'note' => $note, 'created_by' => $this->session->userdata('user_id'), 'count_id' => $this->input->post('count_id') ? $this->input->post('count_id') : NULL,);
        }
        if ($this->form_validation->run() == TRUE && $this->products_model->addAdjustment($data, $products)) {
            $response = [
                'status' => TRUE,
                'messages' => 'Stock adjustment has been successfuly',
            ];
        }
        echo json_encode($response);
    }

}
