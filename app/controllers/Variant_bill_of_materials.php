<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Variant_bill_of_materials extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('Variant_bill_of_materials_model');
        $this->load->model('Production_Unit_Model_New');
        $this->load->model('Products_model');
        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->database(); // Database library
        $this->load->library('datatables');
        $this->load->library('upload');
        $this->digital_upload_path = 'files/' . $this->Customer_assets;
        $this->upload_path = 'assets/mdata/' . $this->Customer_assets . '/uploads/production_unit';
        $this->thumbs_path = 'assets/mdata/' . $this->Customer_assets . '/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
        $this->data['Settings'] = $this->Settings;
    }

    function index($warehouse_id = NULL){
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses();
        } else {
            $location_data = $this->site->getWarehouseByIDs($location_id); //get
        }
        $locationName = '';
        foreach ($location_data as $location) {
            $locationName = $location->name;
        }

        $this->data['units'] = $this->site->getAllBaseUnits();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['categories'] = $this->Variant_bill_of_materials_model->getProductCategoriesList();
        $this->data['outletName'] = $location_data;

        $bc = array(array('link' => base_url(), 'page' => lang('Job_Works')), array('link' => '#', 'page' => lang('Variant_bill_of_materials')));
        $meta = array('page_title' => lang('variant_bill_of_materials'), 'bc' => $bc);

        $this->page_construct('job_works/variant_bill_of_materials', $meta, $this->data);
    }
    public function GetProductsbyCategoriesID(){
        $categoriesId = $this->input->get('categoriesId');
        $subcategoryId = $this->input->get('subcategoryId');
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        $location = $this->Variant_bill_of_materials_model->getWarehouseByID($location_id);

        $productsByCategories = '';
        if ($categoriesId || $subcategoryId) {
            $productsByCategories = $this->Variant_bill_of_materials_model->getProductByCategories($categoriesId, $subcategoryId);
        }
        if ($productsByCategories) {
            $i = 0;
            foreach ($productsByCategories as $row) {

                $unitData = $this->Variant_bill_of_materials_model->getUnitById($row->unit);
                $row->unit_name = $unitData->name;
                $row->unit_price = $row->price;
                $row->org_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit_weight = $row->weight;
                $row->quantity = 1;

                if (($location->price_group_id)) {
                    // if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $location->price_group_id)) {
                    $row->unit_price = $row->c_price;
                    // }
                }
                if ($row->unit_price == 0) {
                    $row->unit_price = $row->org_price;
                }
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
            }
            $pr[] = array('productsByCat' => $productsByCategories, 'row' => $row, 'tax_rate' => $tax_rate);
        } else {
            $pr[] = array('productsByCat' => $productsByCategories);
        }
        echo json_encode($pr);
        return;
    }
    public function getProcurementOrderList(){

        // Example PHP controller endpoint
        $status = $this->input->get('orderStatus');
        $order_id = $this->input->get('order_id');

        if ($status) {
            $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '1' : '0';
            $getOrderData = $this->Variant_bill_of_materials_model->getProcurmentOrders($status, $order_dispatch);
            $pr[] = array('getOrderData' => $getOrderData);
        } else {
            $pr = "Please Select Status";
        }
        echo json_encode($pr);
        return;
    }
    public function GetRawProducts(){
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        $location = $this->Variant_bill_of_materials_model->getWarehouseByID($location_id);

        $Rawproducts = '';
        // if ($categoriesId || $subcategoryId) {
        $Rawproducts = $this->Variant_bill_of_materials_model->getRawProducts();
        // }
        if ($Rawproducts) {
            $i = 0;
            foreach ($Rawproducts as $row) {
                $unitData = $this->Variant_bill_of_materials_model->getUnitById($row->unit);
                $row->unit_name = $unitData->name;
                $row->unit_price = $row->price;
                $row->org_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit_weight = $row->weight;
                $row->quantity = 1;
                if (($location->price_group_id)) {
                    $row->unit_price = $row->c_price;
                }
                if ($row->unit_price == 0) {
                    $row->unit_price = $row->org_price;
                }
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
            }
            $pr[] = array('Rawproducts' => $Rawproducts, 'row' => $row, 'tax_rate' => $tax_rate);
        } else {
            $pr[] = array('Rawproducts' => $Rawproducts);
        }
        echo json_encode($pr);
        return;
    }
    public function addProddUnitBomData(){
        $postData = json_decode($this->input->post('data'), true);

        if (!$postData || empty($postData['items'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid or empty data.'));
            return;
        }

        $productId   = $postData['productId'];
        $jobWorkId   = !empty($postData['job_work_id']) ? (int) $postData['job_work_id'] : null;
        $product_name = isset($postData['product_name']) ? $postData['product_name'] : '';
        $min_batch_qty = isset($postData['batchData'][0]['min_batch_qty']) ? $postData['batchData'][0]['min_batch_qty'] : '';
        $sale_unit = isset($postData['batchData'][0]['sale_unit']) && isset($postData['batchData'][0]['sale_unit_uom_id']) ?
        $postData['batchData'][0]['sale_unit'] . ' ' . $postData['batchData'][0]['sale_unit_uom_id'] : '';

        $version = $this->Variant_bill_of_materials_model->get_bom_version_no($productId);

        // Prepare BOM header data
        $bomData = array(
            'product_id' => $productId,
            'version_no' => $version + 1,
            'job_works'  => $jobWorkId,
            'is_active' => !empty($postData['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'notes' => isset($postData['notes']) ? $postData['notes'] : ''
        );

        $bom_id = $this->Variant_bill_of_materials_model->addProddUnitBom($bomData);

        if (!$bom_id) {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to save BOM header.'));
            return;
        }
  
        // Insert Batch Data (if any)
        if (!empty($postData['batchData'])) {
            $puItem = array();
            foreach ($postData['batchData'] as $item) {
                $puItem = array(
                    'bom_id' => $bom_id, // Optional: if batch is tied to BOM
                    'product_id' => $productId,
                    'min_batch_qty' => isset($item['min_batch_qty']) ? $item['min_batch_qty'] : 0,
                    'batch_uom' => isset($item['uom_id']) ? $item['uom_id'] : null,
                    'sales_units' => (isset($item['sale_unit']) ? $item['sale_unit'] : '') . ' ' .
                        (isset($item['sale_unit_uom_id']) ? $item['sale_unit_uom_id'] : ''),
                    'is_batch_only' => isset($item['isBatchOnly']) ? $item['isBatchOnly'] : 0
                );

            }
                $this->Variant_bill_of_materials_model->addproducts_in_batches($puItem);

        }

        // Insert BOM Item Details
        $headerProductId = (int) $productId;
        foreach ($postData['items'] as $item) {
            $rawPid = isset($item['rawProductId']) ? (int) $item['rawProductId'] : 0;
            $vRaw   = isset($item['variant_option_id']) ? $item['variant_option_id'] : null;
            $variant_option_id = ($vRaw === '' || $vRaw === null) ? 0 : (int) $vRaw;

            $variant_label = isset($item['variant_label']) ? trim((string) $item['variant_label']) : '';
            if ($variant_label === '' || strcasecmp($variant_label, 'N/A') === 0) {
                $variant_label = $this->Variant_bill_of_materials_model->get_variant_label_tail_from_material_name($rawPid);
            }

            // Try to resolve mainproduct_id for the material
            $mainproduct_id = null;
            if ($rawPid > 0) {
                $this->db->select('mainproduct_id');
                $this->db->from('sma_products');
                $this->db->where('id', $rawPid);
                $this->db->limit(1);
                $mrow = $this->db->get()->row();
                if ($mrow && !empty($mrow->mainproduct_id)) {
                    $mainproduct_id = (int) $mrow->mainproduct_id;
                }
            }

            // If UI sent 0 but we have a textual label, resolve option_id using header+material variants
            if ($variant_option_id === 0 && $variant_label !== '' && strcasecmp($variant_label, 'N/A') !== 0) {
                $resolved = (int) $this->Variant_bill_of_materials_model->resolve_bom_line_option_id_for_save(
                    $headerProductId,
                    $rawPid,
                    $variant_label,
                    $mainproduct_id
                );
                if ($resolved > 0) {
                    $variant_option_id = $resolved;
                }
            }

            $itemData = array(
                'bom_id'            => $bom_id,
                'material_id'       => $rawPid,
                'bom_ref'           => isset($item['bom_ref']) ? $item['bom_ref'] : null,
                'quantity_required' => isset($item['quantity']) ? $item['quantity'] : 0,
                'uom'               => isset($item['unitId']) ? $item['unitId'] : null,
                'wastage_percent'   => isset($item['wastage']) ? $item['wastage'] : 0,
                'is_alternative'    => isset($item['is_alternative']) ? $item['is_alternative'] : 0,
                'option_id'         => $variant_option_id > 0 ? $variant_option_id : null
            );

            $this->Variant_bill_of_materials_model->addProddUnitBomItems($itemData);
        }
            echo json_encode(array(
            'status' => 'success',
            'message' => 'BOM saved successfully',
            'bomData' => array(
            'id' => $bom_id,
            'product_id' => $productId,
            'version_no' => $version + 1,
            'is_active' => $bomData['is_active'],
            'created_at' => date('Y-m-d H:i:s'), 
            'name' => $product_name,
            'min_batch_qty' => $min_batch_qty,
            'sales_units' => $sale_unit,
        )
        ));

        // echo json_encode(array('status' => 'success', 'message' => 'BOM saved successfully'));
    }
    public function GetBomDetailsByProductId(){
        $productId = $this->input->get('productId');
        $jobWorkRaw = $this->input->get('job_work_id');
        $jobWorkId = ($jobWorkRaw !== null && $jobWorkRaw !== '') ? (int) $jobWorkRaw : null;
        if ($jobWorkId !== null && $jobWorkId <= 0) {
            $jobWorkId = null;
        }
        $bomDetails = $this->Variant_bill_of_materials_model->getBomDetailsByProductId($productId, $jobWorkId);
        if ($bomDetails) {
            $pr = array('bomDetails' => $bomDetails);
        } else {
            $pr = array('bomDetails' => array());
        }
        echo json_encode($pr);
        return;
    }
    public function getBomMaterialDetails(){
        $productId = $this->input->get('productId');
        $bomDetails = $this->Variant_bill_of_materials_model->getBomMaterialDetails($productId);
        if ($bomDetails) {
            $pr = array('bomMaterialsDetails' => $bomDetails);
        } else {
            $pr = array('bomMaterialsDetails' => array());
        }
        echo json_encode($pr);
        return;
    }
    public function getBomMaterialItems(){
        $bom_id = $this->input->post('bom_id');

        if (!$bom_id) {
            echo json_encode(array('status' => 'error', 'message' => 'BOM ID is required.'));
            return;
        }
        $bomItems = $this->Variant_bill_of_materials_model->getBomMaterialItems($bom_id);
        if ($bomItems) {
            $pr = array('bomItems' => $bomItems);
        } else {
            $pr = array('bomItems' => array());
        }
        echo json_encode($pr);
        return;
    }
    public function getLatestBomVersion(){

        $productId = $this->input->get('productId');
        if (!$productId) {
            echo json_encode(array('version' => 1)); // default fallback
            return;
        }

        $version = $this->Variant_bill_of_materials_model->get_bom_version_no($productId);
        // Return the NEXT version number to be used when creating a new BOM
        $next_version = ($version && (int)$version > 0) ? ((int)$version + 1) : 1;
        echo json_encode(array('version' => $next_version));
    }

    public function getProductVariants(){
        $productId = $this->input->get('productId');
        
        if (!$productId) {
            echo json_encode(array('variants' => array()));
            return;
        }

        $variants = $this->Variant_bill_of_materials_model->getProductVariants($productId);
        echo json_encode(array('variants' => $variants));
        return;
    }

    public function getRawMaterialVariants(){
        $rawMaterialId = $this->input->get('rawMaterialId');
        
        if (!$rawMaterialId) {
            echo json_encode(array('variants' => array()));
            return;
        }

        $variants = $this->Variant_bill_of_materials_model->getRawMaterialVariants($rawMaterialId);
        echo json_encode(array('variants' => $variants));
        return;
    }
    

    // Load Vendors stock report with filters
    public function vendor_stock($warehouse_id = NULL)
    {
        // $this->sma->checkPermissions('products');

        $this->data['suppliers'] = $this->Variant_bill_of_materials_model->get_all_suppliers();
        $this->data['raw_products'] = $this->Variant_bill_of_materials_model->get_raw_products();
        $this->data['warehouses'] = $this->site->getAllVendorWarehouses();

        // $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('reports'), 'page' => lang('reports')),
            array('link' => '#', 'page' => lang('Vendors_Stock_Report'))
        );
        $meta = array('page_title' => lang('Vendors_Stock_Report'), 'bc' => $bc);
        $this->page_construct('reports/vendor_stock', $meta, $this->data);
    }

    public function getVendorStockReport($pdf = NULL, $xls = NULL, $img = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        // filters
        $warehouse = $this->input->get_post('warehouse') ?: NULL;
        $supplier  = $this->input->get_post('supplier') ?: NULL;
        $product   = $this->input->get_post('product') ?: NULL;

        /* ---------------------- EXPORT SECTION ---------------------- */

        if ($pdf || $xls || $img) {

            $rows = $this->Variant_bill_of_materials_model
                            ->getVendorStockExport($warehouse, $supplier, $product);
        
            if (empty($rows)) {
                $this->session->set_flashdata('error', lang('nothing_found'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('Variant_bill_of_materials/vendor_stock'));
            }
        
            // Excel/PDF export
            $this->load->library('excel');
            $sheet = $this->excel->setActiveSheetIndex(0);
            $sheet->setTitle(lang('vendors_stock_report'));
        
            /* ------------------ TITLE ----------------------- */
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'Vendor Stock Report');
        
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FF0000');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
            /* ------------------ HEADER ----------------------- */
            $sheet->setCellValue('A2', 'Vendor');
            $sheet->setCellValue('B2', 'Raw Material');
            $sheet->setCellValue('C2', 'Location');
            $sheet->setCellValue('D2', 'Unit');
            $sheet->setCellValue('E2', 'Stock');
            $sheet->setCellValue('F2', 'Req. For Pending Orders');
            $sheet->setCellValue('G2', 'To Be Supplied');
        
            $headerStyle = array(
                'font' => array('bold' => true),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
            );
            $sheet->getStyle('A2:G2')->applyFromArray($headerStyle);
        
            /* ------------------ DATA ROWS ----------------------- */
            $r = 3;
            $total_stock = 0;
            $total_pending = 0;
            $total_to_supply = 0;

            foreach ($rows as $row) {
                $sheet->setCellValue("A{$r}", $row->supplier_name);
                $sheet->setCellValue("B{$r}", $row->product_name);
                $sheet->setCellValue("C{$r}", $row->warehouse_name);
                $sheet->setCellValue("D{$r}", $row->unit_name);
                // Stock, Pending, To Supply
                $sheet->setCellValue("E{$r}", $this->sma->formatQuantity($row->quantity));
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue("F{$r}", $this->sma->formatQuantity($row->pending_required));
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue("G{$r}", $this->sma->formatQuantity($row->to_supply));
                $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                // Border
                $sheet->getStyle("A{$r}:G{$r}")->applyFromArray(array(
                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                ));

                $total_stock   += (float)$row->quantity;
                $total_pending += (float)$row->pending_required;
                $total_to_supply += (float)$row->to_supply;
                $r++;
            }

            /* ------------------ TOTAL FOOTER ----------------------- */
            $sheet->setCellValue("D{$r}", "TOTAL");
            $sheet->setCellValue("E{$r}", $this->sma->formatQuantity($total_stock));
            $sheet->setCellValue("F{$r}", $this->sma->formatQuantity($total_pending));
            $sheet->setCellValue("G{$r}", $this->sma->formatQuantity($total_to_supply));

            $sheet->getStyle("D{$r}:G{$r}")->applyFromArray(array(
                'font' => array('bold' => true),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
            ));
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            /* ------------------ COLUMN WIDTHS ----------------------- */
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(35);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(35);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(18);

            $filename = 'vendors_stock_report';

            // Optional detail section if a specific vendor + RM was selected by the UI
            $detail_supplier = $this->input->get_post('detail_supplier');
            $detail_product  = $this->input->get_post('detail_product');
            if (!empty($detail_supplier) && !empty($detail_product)) {
                $detail_rows = $this->Variant_bill_of_materials_model->getVendorRMDetails($detail_supplier, $detail_product);
                if (!empty($detail_rows)) {
                    $r = $r + 2; // gap
                    // Title
                    $sheet->mergeCells("A{$r}:G{$r}");
                    $sheet->setCellValue("A{$r}", 'PO Breakdown');
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                    $r++;

                    // Header
                    $sheet->setCellValue("A{$r}", 'PO#');
                    $sheet->setCellValue("B{$r}", 'Unit');
                    $sheet->setCellValue("C{$r}", 'Total Req Qty');
                    $sheet->setCellValue("D{$r}", 'Total Supplied');
                    $sheet->setCellValue("E{$r}", 'Consumed Till Date');
                    $sheet->setCellValue("F{$r}", 'Locked Stock');
                    $sheet->setCellValue("G{$r}", 'Need To Be Supplied');
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($headerStyle);
                    $r++;

                    $t_req = $t_sup = $t_cons = $t_lock = $t_need = 0;

                    foreach ($detail_rows as $dr) {

                        $sheet->setCellValue("A{$r}", $dr->po_reference_no);
                        $sheet->setCellValue("B{$r}", $dr->unit_name); // NEW
                        $sheet->setCellValue("C{$r}", $this->sma->formatQuantity($dr->total_req_qty));
                        $sheet->setCellValue("D{$r}", $this->sma->formatQuantity($dr->total_supplied));
                        $sheet->setCellValue("E{$r}", $this->sma->formatQuantity($dr->total_consumed_till_date));
                        $sheet->setCellValue("F{$r}", $this->sma->formatQuantity($dr->locked_stock));
                        $sheet->setCellValue("G{$r}", $this->sma->formatQuantity($dr->need_to_supply));

                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray(array(
                            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                        ));

                        // Left align PO# and Unit
                        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        // Center align all quantity columns
                        $sheet->getStyle("C{$r}:G{$r}")
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // Totals
                        $t_req  += (float)$dr->total_req_qty;
                        $t_sup  += (float)$dr->total_supplied;
                        $t_cons += (float)$dr->total_consumed_till_date;
                        $t_lock += (float)$dr->locked_stock;
                        $t_need += (float)$dr->need_to_supply;

                        $r++;
                    }

                    // Total row
                    $sheet->setCellValue("A{$r}", 'TOTAL');
                    $sheet->setCellValue("B{$r}", ''); // No unit in total row
                    $sheet->setCellValue("C{$r}", $this->sma->formatQuantity($t_req));
                    $sheet->setCellValue("D{$r}", $this->sma->formatQuantity($t_sup));
                    $sheet->setCellValue("E{$r}", $this->sma->formatQuantity($t_cons));
                    $sheet->setCellValue("F{$r}", $this->sma->formatQuantity($t_lock));
                    $sheet->setCellValue("G{$r}", $this->sma->formatQuantity($t_need));

                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray(array(
                        'font' => array('bold' => true),
                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                    ));

                    // TOTAL row alignment
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                    $sheet->getStyle("C{$r}:G{$r}")
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }
            }

            /* ------------------ PDF EXPORT ----------------------- */
            if ($pdf) {
                $this->excel->getActiveSheet()->getPageSetup()
                            ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

                require_once(APPPATH."third_party/MPDF/mpdf.php");
                PHPExcel_Settings::setPdfRenderer(
                    PHPExcel_Settings::PDF_RENDERER_MPDF,
                    APPPATH.'third_party/MPDF'
                );

                header('Content-Type: application/pdf');
                header("Content-Disposition: attachment;filename=\"{$filename}.pdf\"");
                $writer = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                $writer->save('php://output');
                exit;
            }

            /* ------------------ XLS EXPORT ----------------------- */
            if ($xls) {
                header('Content-Type: application/vnd.ms-excel');
                header("Content-Disposition: attachment;filename=\"{$filename}.xls\"");
                $writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $writer->save('php://output');
                exit;
            }
        }

        /* ---------------------- DATATABLE SECTION ---------------------- */

        // Support both DT 1.10 (draw/start/length/search[value]) and legacy 1.9 (sEcho/iDisplayStart/iDisplayLength/sSearch)
        $start  = $this->input->post('start');
        if ($start === NULL) { $start = $this->input->post('iDisplayStart'); }
        $start = ($start !== NULL) ? (int)$start : 0;

        $length = $this->input->post('length');
        if ($length === NULL) { $length = $this->input->post('iDisplayLength'); }
        $length = ($length !== NULL) ? (int)$length : 10;

        $draw = $this->input->post('draw');
        if ($draw === NULL) { $draw = $this->input->post('sEcho'); }
        $draw = ($draw !== NULL) ? (int)$draw : 1;

        // Search term
        $search = NULL;
        $searchArr = $this->input->post('search');
        if (is_array($searchArr) && isset($searchArr['value'])) {
            $search = $searchArr['value'];
        } else {
            $legacySearch = $this->input->post('sSearch');
            if ($legacySearch !== NULL) { $search = $legacySearch; }
        }

        // Ordering (support both new and legacy)
        $order_col = NULL; $order_dir = 'asc';
        $order = $this->input->post('order');
        if (is_array($order) && isset($order[0]['column'])) {
            $order_col = (int)$order[0]['column'];
            if (!empty($order[0]['dir'])) { $order_dir = $order[0]['dir']; }
        } else {
            // legacy
            $legacySortCol = $this->input->post('iSortCol_0');
            $legacySortDir = $this->input->post('sSortDir_0');
            if ($legacySortCol !== NULL) { $order_col = (int)$legacySortCol; }
            if ($legacySortDir !== NULL) { $order_dir = $legacySortDir; }
        }

        // fetch model data
        // total before search
        $totalAll = $this->Variant_bill_of_materials_model
                            ->countVendorStock($warehouse, $supplier, $product, NULL);
        // total after search
        $totalFiltered = $this->Variant_bill_of_materials_model
                            ->countVendorStock($warehouse, $supplier, $product, $search);

        $rows = $this->Variant_bill_of_materials_model
                        ->getVendorStockList($warehouse, $supplier, $product, $start, $length, $search, $order_col, $order_dir);
        $row_count = count($rows);
        // Prepare rows
        $data = array();
        foreach ($rows as $r) {
            $product_link = '<a href="#" class="rm-details" data-supplier="' . $r->supplier_id . '" data-product="' . $r->product_id . '" data-supplier-name="' . htmlspecialchars($r->supplier_name, ENT_QUOTES, 'UTF-8') . '" data-product-name="' . htmlspecialchars($r->product_name, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($r->product_name, ENT_QUOTES, 'UTF-8') . '</a>';
            $data[] = array(
                $r->supplier_name,
                $product_link,
                $r->warehouse_name,
                $r->unit_name,
                $this->sma->formatQuantity($r->quantity),
                $this->sma->formatQuantity($r->pending_required),
                $this->sma->formatQuantity($r->to_supply)
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalAll,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        
            "sEcho" => $draw,
            "iTotalRecords" => $totalAll,
            "iTotalDisplayRecords" => $totalFiltered,
            "aaData" => $data
        );
        

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
        exit;
    }

    public function getIntermediateProducts()
    {
        // Only return processes (job works) that are configured for the given main product in sma_product_dcc_stages.
        $mainProductId = (int) $this->input->get('mainProductId');

        $intermediateProducts = array();

        if ($mainProductId > 0) {
            $this->db->select('jw.id, jw.items, jw.suffix');
            $this->db->from('sma_product_dcc_stages d');
            $this->db->join('sma_standard_job_works jw', 'jw.id = d.job_work', 'inner');
            $this->db->where('d.product_id', $mainProductId);
            $this->db->group_by('jw.id, jw.items, jw.suffix');
            $this->db->order_by('jw.items', 'ASC');
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $name   = isset($row->items) ? $row->items : '';
                    $suffix = !empty($row->suffix) ? $row->suffix : 'items';
                    $intermediateProducts[] = array(
                        'id'     => (int) $row->id,
                        'name'   => $name,
                        'suffix' => $suffix
                    );
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('intermediateProducts' => $intermediateProducts));
    }

    /**
     * Given main product + selected process (job work), return suffix products created for that process.
     * Used by JS to auto-add Input/DCC rows for the recipe.
     */
    public function checkProductWithSuffix()
    {
        $main_id    = (int) $this->input->get('main_product_id');
        $process_id = (int) $this->input->get('process_id');
        if (!$main_id || !$process_id) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'status'          => 'error',
                'message'         => 'Missing main_product_id or process_id',
                'suffix_products' => array()
            ));
            return;
        }

        // 1) Get the configured DCC stage for this main product + process (job work).
        //    Gate: auto-add should run only when there is a DCC stage for this main product + job_work.
        $this->db->select('d.input, d.output, jw.suffix as job_work_suffix');
        $this->db->from('sma_product_dcc_stages d');
        $this->db->join('sma_standard_job_works jw', 'jw.id = d.job_work', 'left');
        $this->db->where('d.product_id', $main_id);
        $this->db->where('d.job_work', $process_id);
        $this->db->limit(1);
        $stage = $this->db->get()->row();
        // If there is no stage for this product_id + process, do nothing (normal flow, no auto-add).
        if (!$stage) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'status'          => 'success',
                'suffix'          => '',
                'suffix_products' => array()
            ));
            return;
        }

        // 2) Suffixes only from Input (lines "Label - Suffix"). Empty Input => no auto-add (do not use job_work_suffix).
        //    Use the LAST hyphen so names like "White T-shirt-Stiched" yield suffix "Stiched", not "shirt-Stiched".
        $suffixCandidates = array();
        if (isset($stage->input) && trim($stage->input) !== '') {
            $rawInput = trim($stage->input);
            $tokens = preg_split('/[\r\n,]+/', $rawInput);
            if (is_array($tokens)) {
                foreach ($tokens as $tok) {
                    $tok = trim($tok);
                    if ($tok === '') {
                        continue;
                    }
                    $dashPos = strrpos($tok, '-');
                    if ($dashPos === false) {
                        continue;
                    }
                    $suf = trim(substr($tok, $dashPos + 1));
                    if ($suf !== '') {
                        $suffixCandidates[] = $suf;
                    }
                }
            }
        }
        $suffixCandidates = array_values(array_unique($suffixCandidates));
        // Common spelling mismatch between DCC Input and SKU names (e.g. Stitched vs Stiched)
        $aliasExtra = array();
        foreach ($suffixCandidates as $c) {
            if (strcasecmp($c, 'Stitched') === 0) {
                $aliasExtra[] = 'Stiched';
            }
            if (strcasecmp($c, 'Stiched') === 0) {
                $aliasExtra[] = 'Stitched';
            }
        }
        if (!empty($aliasExtra)) {
            $suffixCandidates = array_values(array_unique(array_merge($suffixCandidates, $aliasExtra)));
        }

        if (empty($suffixCandidates)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'status'          => 'success',
                'suffix'          => '',
                'suffix_products' => array()
            ));
            return;
        }

        // 3) Load all child products for this main product (these are the *_Stiched_* / *_Ironed_* variants)
        $this->db->select('id, name, code');
        $this->db->from('sma_products');
        $this->db->where('mainproduct_id', $main_id);
        $this->db->where('flag_visible', 1);
        $all_children = $this->db->get()->result();

        // 4) Match each child to the first suffix candidate that appears in its name (case-insensitive path segment)
        $children = array(); // each: array('product' => $p, 'suffix' => $matchedSuffix)
        foreach ($all_children as $p) {
            $name = (string) $p->name;
            if ($name === '') {
                continue;
            }
            $matched = '';
            foreach ($suffixCandidates as $cand) {
                $cand = (string) $cand;
                if ($cand === '') {
                    continue;
                }
                $needle = '_' . $cand;
                $nlen = strlen($needle);
                $endsWith = ($nlen > 0 && strlen($name) >= $nlen && strcasecmp(substr($name, -$nlen), $needle) === 0);
                if (stripos($name, $needle . '_') !== false || $endsWith) {
                    $matched = $cand;
                    break;
                }
            }
            if ($matched !== '') {
                $children[] = array('product' => $p, 'suffix' => $matched);
            }
        }

        // Primary suffix for response (first candidate that actually matched any child, else first candidate)
        $suffixToken = !empty($suffixCandidates) ? $suffixCandidates[0] : '';
        foreach ($children as $ch) {
            $suffixToken = $ch['suffix'];
            break;
        }

        // 5) For each selected child, try to resolve its variant against main product variants
        $this->db->select('id, name');
        $this->db->from('sma_product_variants');
        $this->db->where('product_id', $main_id);
        $this->db->where('group_id !=', 2); // skip colour group
        $this->db->order_by('id', 'ASC');
        $vrows = $this->db->get()->result_array();

        $suffix_products = array();
        foreach ($children as $ch) {
            $p = $ch['product'];
            $childSuffix = isset($ch['suffix']) ? (string) $ch['suffix'] : $suffixToken;
            $tail = '';
            $name = (string) $p->name;
            $marker = '_' . $childSuffix . '_';
            $pos2 = stripos($name, $marker);
            if ($pos2 !== false) {
                $tail = substr($name, $pos2 + strlen($marker));
            } else {
                // last underscore segment as fallback
                $parts = explode('_', $name);
                if (count($parts) > 1) {
                    $tail = end($parts);
                }
            }
            $tail = trim($tail);

            $matchedId   = 0;
            $matchedName = '';
            if ($tail !== '') {
                $tl = strtolower($tail);
                foreach ($vrows as $v) {
                    $vn = isset($v['name']) ? strtolower(trim($v['name'])) : '';
                    if ($vn !== '' && ($vn === $tl || strpos($tl, $vn) !== false || strpos($vn, $tl) !== false)) {
                        $matchedId   = (int) $v['id'];
                        $matchedName = $v['name'];
                        break;
                    }
                }
                if ($matchedId === 0 && ctype_digit($tail)) {
                    $want = (int) $tail;
                    foreach ($vrows as $v) {
                        if ((int) $v['id'] === $want) {
                            $matchedId   = $want;
                            $matchedName = $v['name'];
                            break;
                        }
                    }
                }
            }

            $suffix_products[] = array(
                'id'                 => (int) $p->id,
                'name'               => $p->name,
                'code'               => isset($p->code) ? $p->code : '',
                'matched_variant_id' => $matchedId,
                'matched_variant_name' => $matchedName
            );
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'status'          => 'success',
            'suffix'          => $suffixToken,
            'suffix_products' => $suffix_products
        ));
    }
    // AJAX endpoint: breakdown by PO for selected vendor + raw material
    public function getVendorRMStockDetails()
    {
        $supplier_id = (int)$this->input->get('supplier_id');
        $product_id  = (int)$this->input->get('product_id');
        if (!$supplier_id || !$product_id) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Missing parameters')));
            return;
        }
        $rows = $this->Variant_bill_of_materials_model->getVendorRMDetails($supplier_id, $product_id);
        $tot = array('total_req_qty' => 0, 'total_supplied' => 0, 'total_consumed_till_date' => 0, 'locked_stock' => 0, 'need_to_supply' => 0);
        foreach ($rows as $r) {
            $tot['total_req_qty'] += (float)$r->total_req_qty;
            $tot['total_supplied'] += (float)$r->total_supplied;
            $tot['total_consumed_till_date'] += (float)$r->total_consumed_till_date;
            $tot['locked_stock'] += (float)$r->locked_stock;
            $tot['need_to_supply'] += (float)$r->need_to_supply;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'success', 'rows' => $rows, 'totals' => $tot)));
    }
}
