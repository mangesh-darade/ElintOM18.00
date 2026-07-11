<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bill_of_material extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('Bill_of_material_model');
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




    

    function index($warehouse_id = NULL)
    {
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses();
        } else {
            $location_data = $this->site->getWarehouseByIDs($location_id); //get
        }
        if (!$location_data || !is_array($location_data)) {
            $location_data = array();
        }
        $locationName = '';
        foreach ($location_data as $location) {
            $locationName = $location->name;
        }

        $this->data['units'] = $this->site->getAllBaseUnits();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['categories'] = $this->Bill_of_material_model->getProductCategoriesList();
        $this->data['outletName'] = $location_data;

        $bc = array(array('link' => base_url(), 'page' => lang('Production_Unit')), array('link' => '#', 'page' => lang('Bill_Of_materials')));
        $meta = array('page_title' => lang('Bill_Of_materials'), 'bc' => $bc);

        $this->page_construct('production_unit/bill_of_material', $meta, $this->data);
    }
    public function GetProductsbyCategoriesID()
    {

        $categoriesId = $this->input->get('categoriesId');
        $subcategoryId = $this->input->get('subcategoryId');
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        $location = $this->Bill_of_material_model->getWarehouseByID($location_id);

        $productsByCategories = '';
        if ($categoriesId || $subcategoryId) {
            $productsByCategories = $this->Bill_of_material_model->getProductByCategories($categoriesId, $subcategoryId);
        }
        if ($productsByCategories) {
            $i = 0;
            foreach ($productsByCategories as $row) {

                $unitData = $this->Bill_of_material_model->getUnitById($row->unit);
                $row->unit_name = $unitData->name;
                $row->unit_price = $row->price;
                $row->org_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit_weight = $row->weight;
                $row->quantity = 1;

                if ($location && ($location->price_group_id)) {
                    // if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $location->price_group_id)) {
                    $row->unit_price = $row->c_price;
                    // }
                }
                if ($row->unit_price == 0) {
                    $row->unit_price = $row->org_price;
                }
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
            }
            $pr[] = ['productsByCat' => $productsByCategories, 'row' => $row, 'tax_rate' => $tax_rate];
        } else {
            $pr[] = ['productsByCat' => $productsByCategories];
        }
        echo json_encode($pr);
        return;
    }
    public function getProcurementOrderList()
    {

        // Example PHP controller endpoint
        $status = $this->input->get('orderStatus');
        $order_id = $this->input->get('order_id');

        if ($status) {
            $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '1' : '0';
            $getOrderData = $this->Bill_of_material_model->getProcurmentOrders($status, $order_dispatch);
            $pr[] = ['getOrderData' => $getOrderData];
        } else {
            $pr = "Please Select Status";
        }
        echo json_encode($pr);
        return;
    }
    public function GetRawProducts()
    {
        $user_id = $this->session->userdata('user_id');
        $user_data = $this->site->getUser($user_id); //get user information
        $location_id = $user_data->warehouse_id;
        $location = $this->Bill_of_material_model->getWarehouseByID($location_id);

        $Rawproducts = '';
        // if ($categoriesId || $subcategoryId) {
        $Rawproducts = $this->Bill_of_material_model->getRawProducts();
        // }
        if ($Rawproducts) {
            $i = 0;
            foreach ($Rawproducts as $row) {
                $unitData = $this->Bill_of_material_model->getUnitById($row->unit);
                $row->unit_name = $unitData->name;
                $row->unit_price = $row->price;
                $row->org_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit_weight = $row->weight;
                $row->quantity = 1;
                if ($location && ($location->price_group_id)) {
                    $row->unit_price = $row->c_price;
                }
                if ($row->unit_price == 0) {
                    $row->unit_price = $row->org_price;
                }
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
            }
            $pr[] = ['Rawproducts' => $Rawproducts, 'row' => $row, 'tax_rate' => $tax_rate];
        } else {
            $pr[] = ['Rawproducts' => $Rawproducts];
        }
        echo json_encode($pr);
        return;
    }
    public function addProddUnitBomData()
    {
        $postData = json_decode($this->input->post('data'), true);

        if (!$postData || empty($postData['items'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or empty data.']);
            return;
        }

        $productId = $postData['productId'];
        $product_name = isset($postData['product_name']) ? $postData['product_name'] : '';
        $min_batch_qty = isset($postData['batchData'][0]['min_batch_qty']) ? $postData['batchData'][0]['min_batch_qty'] : '';
        $sale_unit = isset($postData['batchData'][0]['sale_unit']) && isset($postData['batchData'][0]['sale_unit_uom_id']) ?
        $postData['batchData'][0]['sale_unit'] . ' ' . $postData['batchData'][0]['sale_unit_uom_id'] : '';

        $version = $this->Bill_of_material_model->get_bom_version_no($productId);

        // Prepare BOM header data
        $bomData = [
            'product_id' => $productId,
            'version_no' => $version + 1,
            'is_active' => !empty($postData['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'notes' => isset($postData['notes']) ? $postData['notes'] : ''
        ];

        $bom_id = $this->Bill_of_material_model->addProddUnitBom($bomData);

        if (!$bom_id) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save BOM header.']);
            return;
        }
  
        // Insert Batch Data (if any)
        if (!empty($postData['batchData'])) {
            $puItem= [];
            foreach ($postData['batchData'] as $item) {
                $puItem = [
                    'bom_id' => $bom_id, // Optional: if batch is tied to BOM
                    'product_id' => $productId,
                    'min_batch_qty' => isset($item['min_batch_qty']) ? $item['min_batch_qty'] : 0,
                    'batch_uom' => isset($item['uom_id']) ? $item['uom_id'] : null,
                    'sales_units' => (isset($item['sale_unit']) ? $item['sale_unit'] : '') . ' ' .
                        (isset($item['sale_unit_uom_id']) ? $item['sale_unit_uom_id'] : ''),
                    'is_batch_only' => isset($item['isBatchOnly']) ? $item['isBatchOnly'] : 0
                ];

            }
                $this->Bill_of_material_model->addproducts_in_batches($puItem);

        }

        // Insert BOM Item Details
        $altIds = array();
        foreach ($postData['items'] as $it) {
            if (isset($it['primary_product_id']) && !empty($it['primary_product_id'])) {
                $altIds[(int)$it['rawProductId']] = true;
                $altIds[(int)$it['primary_product_id']] = true;
            }
        }

        foreach ($postData['items'] as $item) {
            $itemData = [
                'bom_id' => $bom_id,
                'material_id' => $item['rawProductId'],
                'bom_ref' => isset($item['bom_ref']) ? $item['bom_ref'] : null,
                'quantity_required' => isset($item['quantity']) ? $item['quantity'] : 0,
                'uom' => isset($item['unitId']) ? $item['unitId'] : null,
                'wastage_percent' => isset($item['wastage']) ? $item['wastage'] : 0,
                'is_alternative' => isset($altIds[(int)$item['rawProductId']]) ? 1 : 0,
                'primary_product_id' => isset($item['primary_product_id']) && !empty($item['primary_product_id']) ? $item['primary_product_id'] : null
            ];

            $this->Bill_of_material_model->addProddUnitBomItems($itemData);
        }
            echo json_encode([
            'status' => 'success',
            'message' => 'BOM saved successfully',
            'bomData' => [
            'id' => $bom_id,
            'product_id' => $productId,
            'version_no' => $version + 1,
            'is_active' => $bomData['is_active'],
            'created_at' => date('Y-m-d H:i:s'), 
            'name' => $product_name,
            'min_batch_qty' => $min_batch_qty,
            'sales_units' => $sale_unit,
        ]
    ]);

        // echo json_encode(['status' => 'success', 'message' => 'BOM saved successfully']);
    }

    public function GetBomDetailsByProductId()
    {
        $productId = $this->input->get('productId');
        $bomDetails = $this->Bill_of_material_model->getBomDetailsByProductId($productId);
        if ($bomDetails) {
            $pr = ['bomDetails' => $bomDetails];
        } else {
            $pr = ['bomDetails' => []];
        }
        echo json_encode($pr);
        return;
    }
    public function getBomMaterialDetails()
    {
        $productId = $this->input->get('productId');
        $bomDetails = $this->Bill_of_material_model->getBomMaterialDetails($productId);
        if ($bomDetails) {
            $pr = ['bomMaterialsDetails' => $bomDetails];
        } else {
            $pr = ['bomMaterialsDetails' => []];
        }
        echo json_encode($pr);
        return;
    }
    public function getBomMaterialItems()
    {
        $bom_id = $this->input->post('bom_id');

        if (!$bom_id) {
            echo json_encode(['status' => 'error', 'message' => 'BOM ID is required.']);
            return;
        }
        $bomItems = $this->Bill_of_material_model->getBomMaterialItems($bom_id);
        if ($bomItems) {
            $pr = ['bomItems' => $bomItems];
        } else {
            $pr = ['bomItems' => []];
        }
        echo json_encode($pr);
        return;
    }
    public function getLatestBomVersion()
{
    $productId = $this->input->get('productId');
    if (!$productId) {
        echo json_encode(['version' => 1]); // default fallback
        return;
    }

    $version = $this->Bill_of_material_model->get_bom_version_no($productId);
    // Return the NEXT version number to be used when creating a new BOM
    $next_version = ($version && (int)$version > 0) ? ((int)$version + 1) : 1;
    echo json_encode(['version' => $next_version]);
}

}
