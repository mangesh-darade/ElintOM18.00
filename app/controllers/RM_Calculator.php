<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Variant_Order controller
 *
 * Business logic (why):
 * - Provides a production-oriented UI for selecting a Product, its Variants, and calculating
 *   required raw materials before creating a Purchase Order (Variant PO).
 * - Keeps this step separate from the generic Purchase Add screen to avoid coupling UX and rules.
 * - Stages the user's selection (variants, ingredients, note) and then redirects to Purchases
 *   to finalize a proper PO with costs, taxes, Vendor, etc.
 */
class RM_Calculator extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        // Load common dependencies needed across actions in this controller.
        $this->load->library('form_validation');
        $this->load->model('Variant_Order_Model');
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

    /**
     * index
     * Shows the Variant Order screen where the user picks Category → Product → Variants
     * and reviews the derived raw materials required.
     */
    function index($warehouse_id = NULL)
    {
        $this->data['categories'] = $this->Variant_Order_Model->getAllCategories();
        $this->data['brands'] = $this->Variant_Order_Model->getAllBrands();
        
       $this->data['suppliers'] = $this->getLocationSuppliers();

        $bc = array(array('link' => base_url(), 'page' => lang('Procurement_Orders')), array('link' => '#', 'page' => lang('RM_Calculator')));
        $meta = array('page_title' => lang('RM_Calculator'), 'bc' => $bc);

        $this->page_construct('job_works/variant_order', $meta, $this->data);
    }
    /**
     * get_products_by_category
     * Fetch products for a given category (AJAX). Keeps UI responsive.
     */
    public function get_products_by_category(){
        $category_id = $this->input->post('category_id');
        $brand_id = $this->input->post('brand_id');
      
        if($category_id){
            $this->db->from('products');
            $this->db->where('flag_visible', 1);
            // Show only products of type "standard"
            $this->db->where('type', 'standard');
            $this->db->group_start()
                     ->where('category_id', $category_id)
                     ->or_where("FIND_IN_SET(" . $this->db->escape_str($category_id) . ", other_categories) !=", 0)
                     ->group_end();
            $this->db->order_by('name', 'ASC');
        }else{
            $this->db->from('products');
            $this->db->where('flag_visible', 1);
            // Show only products of type "standard"
            $this->db->where('type', 'standard');
            $this->db->group_start()
                     ->where('brand', $brand_id)
                     ->group_end();
            $this->db->order_by('name', 'ASC');
        }
        $products = $this->db->get()->result();
        echo json_encode($products);
    }
    /**
     * get_variants_by_product
     * Fetch variants for a selected product (AJAX) and return a fresh CSRF for subsequent calls.
     */
    public function get_variants_by_product(){
        $product_id = $this->input->post('product_id');
        $job_work_id = (int) $this->input->post('job_work_id');
        $csrf_hash = $this->security->get_csrf_hash();
        
        // Example: fetch variant rows for that product
        $query = $this->db->where('product_id', $product_id)->get('product_variants');
        $variants = $query->result_array(); // associative array for dynamic keys

        echo json_encode([
            'variants' => $variants,
            'csrfHash' => $csrf_hash
        ]);
    }
    /**
     * getProductWiseAllDetails
     * Business logic (why):
     * - Central endpoint that provides both: product variants and aggregated raw materials required
     *   for the selected product at the user's location. This powers the matrix UI.
     */
    public function getProductWiseAllDetails() {

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); 
        $location_id   = $user_data->warehouse_id;
        $location_data = $this->site->getWarehouseByIDs($location_id); 

        $product_id  = $this->input->post('product_id');
        $job_work_id = (int) $this->input->post('job_work_id');
        // Check recipe variants and get appropriate variants
        $recipe_variants_info = $this->Variant_Order_Model->check_recipe_variants($product_id);
        
        // Fetch product variants and raw materials for the given product and location
        if ($recipe_variants_info['has_variants'] && !empty($recipe_variants_info['variants'])) {
            // Use variants from recipe_variants_info (could be from mainproduct_id)
            $variants = $recipe_variants_info['variants'];
        } else {
            // Use regular product variants
            $variants = $this->Variant_Order_Model->get_product_variants($product_id);
        }
        
        $jw_for_bom = ($job_work_id > 0 ? $job_work_id : null);
        $Ingredients          = $this->Variant_Order_Model->getRawMaterials($product_id, $location_id, $jw_for_bom); // fetch raw materials against product & job work if provided
        $Other_Category       = $this->Variant_Order_Model->get_other_category($product_id);
        $Primary_category = explode(',', $Other_Category->primary_category);
        $ids_array = explode(',', $Other_Category->other_categories);
        $other_categories = $this->Variant_Order_Model->get_category_names_by_ids($ids_array);
        $cat_names = array_map(function($cat) {
            return trim($cat['name']);
        }, $other_categories);
        // Remove empty category names
        $cat_names = array_filter($cat_names, function($name) {
            return !empty($name);
        });

        $other_categories_names = implode(', ', $cat_names);
        // Note: The response includes variants and Ingredients (raw materials). Other keys can be filled
        // by the model later if needed by UX. Keeping payload compact for performance.
        $pr = [
            'variants' => $variants,
            'OrderDetails' => $OrderDetails, 
            'productStock' => $productStock, 
            'productBatches' => $productBatches, 
            'latestProductBatches' => $latestProductBatches, 
            'productDetails' => $productDetails, 
            'lastBatch' => $lastBatch, 
            'locationCode' => $locationCode, 
            'Ingredients' => $Ingredients, 
            'other_categories_names' => $other_categories_names, 
            'Primary_category' => $Primary_category,
            'recipe_has_variants' => $recipe_variants_info['has_variants'],
            'product_has_variants' => !empty($variants),
            'mainproduct_id' => $recipe_variants_info['mainproduct_id']
        ];
        echo json_encode($pr);
        return;
        
    }

    /**
     * prepare_variant_po
     * Business logic (why):
     * - Stage the user's selection (variants, grouped raw materials, note) in the session to avoid
     *   long/complex query strings or temp tables.
     * - Redirect to Purchases/generate_vriant_po where a proper PO is created with Vendor & taxes.
     * - Secured as AJAX-only to prevent CSRF tricks via GET; view JS includes CSRF token in POST.
     */
    public function generate_variant_po() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        $payload = $this->input->post(NULL, true);

        // Try to enrich payload with latest active BOM note for the selected product
        try {
            $note_from_bom = '';
            if (!empty($payload['variants'])) {
                $variants_arr = json_decode($payload['variants'], true);
                if (is_array($variants_arr) && !empty($variants_arr)) {
                    // Derive product_id from first variant entry (product_id is present on pv rows)
                    $product_id = 0;
                    foreach ($variants_arr as $v) {
                        if (isset($v['product_id']) && (int)$v['product_id'] > 0) { $product_id = (int)$v['product_id']; break; }
                    }
                    if ($product_id > 0) {
                        $q = $this->db
                            ->select('notes')
                            ->from('sma_produnit_bill_of_materials')
                            ->where('product_id', $product_id)
                            ->where('is_active', 1)
                            ->order_by('CAST(version_no AS UNSIGNED)', 'DESC')
                            ->limit(1)
                            ->get();
                        if ($q && $q->num_rows() > 0) {
                            $row = $q->row();
                            $note_from_bom = is_string($row->notes) ? trim($row->notes) : '';
                        }
                    }
                }
            }
            // If frontend didn't send a note, use BOM note
            if (empty($payload['note']) && $note_from_bom !== '') {
                $payload['note'] = $note_from_bom;
            }
        } catch (Exception $e) {
            // fail silent: keep existing behavior (PHP 5.6: use Exception, not Throwable)
        }
        
        // Enrich ingredients with unit information from products table
        try {
            if (!empty($payload['ingredients'])) {
                $ingredients_arr = json_decode($payload['ingredients'], true);
                if (is_array($ingredients_arr) && !empty($ingredients_arr)) {
                    foreach ($ingredients_arr as $key => $ingredient) {
                        // If unit_name already exists from frontend, keep it
                        if (!empty($ingredient['unit_name'])) {
                            // Already has unit_name from frontend, ensure 'unit' is also set
                            if (empty($ingredient['unit'])) {
                                $ingredients_arr[$key]['unit'] = $ingredient['unit_name'];
                            }
                            continue; // Skip database lookup
                        }
                        
                        // Get product/raw material ID - check multiple possible field names
                        $material_id = 0;
                        if (isset($ingredient['raw_material_id']) && (int)$ingredient['raw_material_id'] > 0) {
                            $material_id = (int)$ingredient['raw_material_id'];
                        } elseif (isset($ingredient['product_id']) && (int)$ingredient['product_id'] > 0) {
                            $material_id = (int)$ingredient['product_id'];
                        } elseif (isset($ingredient['id']) && (int)$ingredient['id'] > 0) {
                            $material_id = (int)$ingredient['id'];
                        }
                        
                        if ($material_id > 0) {
                            // Fetch unit from products table
                            $product_query = $this->db
                                ->select('unit')
                                ->from('sma_products')
                                ->where('id', $material_id)
                                ->limit(1)
                                ->get();
                            
                            if ($product_query && $product_query->num_rows() > 0) {
                                $product_row = $product_query->row();
                                $unit_value = trim($product_row->unit);
                                
                                // Only set if unit is not empty
                                if (!empty($unit_value)) {
                                    $ingredients_arr[$key]['unit'] = $unit_value;
                                    $ingredients_arr[$key]['unit_name'] = $unit_value;
                                }
                            }
                        }
                    }
                    // Update payload with enriched ingredients
                    $payload['ingredients'] = json_encode($ingredients_arr);
                }
            }
        } catch (Exception $e) {
            // Log error for debugging (PHP 5.6: use Exception, not Throwable)
            log_message('error', 'Error enriching ingredients with unit: ' . $e->getMessage());
        }

        // Job work with empty suffix: order lines must be main product + variant options (not child SKUs from simple RM rows).
        try {
            $jw_norm_id = isset($payload['job_work_id']) ? (int) $payload['job_work_id'] : 0;
            if ($jw_norm_id > 0 && !empty($payload['variants'])) {
                $vars_norm = json_decode($payload['variants'], true);
                $ings_norm = !empty($payload['ingredients']) ? json_decode($payload['ingredients'], true) : null;
                if (is_array($vars_norm) && !empty($vars_norm)) {
                    $norm = $this->Variant_Order_Model->normalize_rm_variant_po_for_empty_jobwork_suffix(
                        $jw_norm_id,
                        $vars_norm,
                        is_array($ings_norm) ? $ings_norm : null
                    );
                    if (!empty($norm['changed'])) {
                        $payload['variants'] = json_encode($norm['variants']);
                        if ($norm['ingredients'] !== null) {
                            $payload['ingredients'] = json_encode($norm['ingredients']);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            log_message('error', 'RM Calculator variant PO normalization: ' . $e->getMessage());
        }
       
        // Store supplier_id separately if provided
        if (!empty($payload['supplier_id'])) {
            $this->session->set_userdata('variant_po_supplier_id', $payload['supplier_id']);
        }
        
        // Store the user's selection in the session for later use
        $this->session->set_userdata('variant_po_payload', $payload);
        echo json_encode(array(
            'status' => 'ok',
            'redirect' => site_url('purchases/generate_variant_po')
        ));
    }
    ///////////////////// RM Calculator Helper Functions /////////////////////
    public function getLocationSuppliers(){
        $this->db->select('c.id, c.name as name, c.location_id');
        $this->db->from('sma_companies c');
        $this->db->join('sma_warehouses w', 'w.id = c.location_id', 'left');
        $this->db->join('sma_location_type lt', 'lt.id = w.location_type', 'left');
        $this->db->where('c.group_name', 'supplier');
        $this->db->where('c.location_id IS NOT NULL', NULL, FALSE);
        $this->db->where('c.location_id !=', 0);
        $this->db->where('(w.location_type IS NOT NULL AND w.location_type != 0)', NULL, FALSE);
        $this->db->where('lt.id IS NOT NULL', NULL, FALSE); // ensure valid location type
        $this->db->order_by('c.name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }
    public function get_supplier_stock(){
        $supplier_id = $this->input->post('supplier_id');
        $csrf = [
            'csrfName' => $this->security->get_csrf_token_name(),
            'csrfHash' => $this->security->get_csrf_hash()
        ];

        if (!$supplier_id) {
            echo json_encode(array_merge($csrf, ['status' => 'error', 'message' => 'Missing Vendor ID']));
            return;
        }

        // Get the supplier's assigned location_id (warehouse_id)
        $supplier = $this->db->get_where('sma_companies', ['id' => $supplier_id])->row();

        if (!$supplier || !$supplier->location_id) {
            echo json_encode(array_merge($csrf, ['status' => 'error', 'message' => 'Vendor has no location assigned']));
            return;
        }

        $warehouse_id = $supplier->location_id;

        // Get all product stock for that warehouse (raw materials)
        $this->db->select('wp.product_id as raw_material_id, p.name as raw_material, wp.quantity, w.name as warehouse_name');
        $this->db->from('sma_warehouses_products wp');
        $this->db->join('sma_products p', 'p.id = wp.product_id', 'left');
        $this->db->join('sma_warehouses w', 'w.id = wp.warehouse_id', 'left');
        $this->db->where('wp.warehouse_id', $warehouse_id);
        $query = $this->db->get();
        $data = $query->result();

        echo json_encode(array_merge($csrf, [
            'status' => 'ok',
            'data' => $data
        ]));
    }

    /**
     * get_job_works_by_product
     * Fetch Job Work / process list for a selected product based on
     * sma_product_dcc_stages → sma_standard_job_works mapping.
     */
    public function get_job_works_by_product()
    {
        $product_id = (int) $this->input->post('product_id');
        $csrf = [
            'csrfName' => $this->security->get_csrf_token_name(),
            'csrfHash' => $this->security->get_csrf_hash()
        ];

        if ($product_id <= 0) {
            echo json_encode(array_merge($csrf, [
                'status' => 'error',
                'message' => 'Missing or invalid product ID',
                'data' => []
            ]));
            return;
        }

        $job_works = $this->Variant_Order_Model->get_job_works_by_product($product_id);

        echo json_encode(array_merge($csrf, [
            'status' => 'ok',
            'data' => $job_works
        ]));
    }

}

?>
