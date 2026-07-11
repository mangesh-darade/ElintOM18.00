<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Restaurant_Order_Taking extends MY_Controller
{
    public $data;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Restaurant_Order_Taking_model');
        // Needed to create POS sales during finalization
        $this->load->model('Sales_model', 'sales_model');
        // Needed to create suspended KOT entries for KDS
        $this->load->model('Pos_model', 'pos_model');
        $this->load->model('Orders_model', 'orders_model');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->data['assets'] = base_url("themes/default/assets/restaurant/");
        
        // Check if user is logged in
        if (!$this->loggedIn) {
            redirect('auth');
        }
    }

    /**
     * Index - Main entry point for Restaurant Order Taking system
     */
    public function index()
    {
        $this->data['page_title'] = 'Restaurant Order Taking';
        $this->data['sections'] = $this->Restaurant_Order_Taking_model->get_sections();
        $this->load->view($this->theme . 'restaurant/index', $this->data);
    }

    /**
     * Tables - Display tables for a specific restaurant section
     */
    public function tables($section_id = null, $subsection_id = null)
    {
        // Direct access to table screen for a specific section (and optional subsection)
        if ($section_id) {
            $this->data['page_title'] = 'Restaurant Tables';
            $this->data['section_id'] = $section_id;
            $this->data['subsection_id'] = $subsection_id;
            $this->data['section'] = $this->Restaurant_Order_Taking_model->get_section($section_id);
            $this->data['sections'] = $this->Restaurant_Order_Taking_model->get_sections();
            $this->data['subsections'] = $this->Restaurant_Order_Taking_model->get_subsections($section_id);
            $this->data['tables'] = $this->Restaurant_Order_Taking_model->get_tables($section_id, $subsection_id);
            $this->load->view($this->theme . 'restaurant/tables', $this->data);
        } else {
            redirect('Restaurant_Order_Taking');
        }
    }

    /**
     * Orders - Main order management screen for restaurant orders
     * 
     * Purpose: Display and manage restaurant orders for a specific table or all tables
     * Usage: Accessed via URL: restaurant_order_taking/orders/{table_id}
     */
    public function orders($table_id = null)
    {
        $this->data['page_title'] = 'Restaurant Orders';
        $this->data['sections'] = $this->Restaurant_Order_Taking_model->get_sections();
        $this->data['table_id'] = $table_id;
        $this->data['tables'] = $this->Restaurant_Order_Taking_model->get_all_occupied_tables();
        
        if ($table_id) {
            $table = $this->Restaurant_Order_Taking_model->get_table($table_id);
            if ($table) {
                $this->data['table'] = $table;
                
                // Ensure current table is in the dropdown list (even if not occupied yet)
                $table_in_list = false;
                foreach($this->data['tables'] as $t) {
                    if($t->id == $table->id) {
                        $table_in_list = true;
                        break;
                    }
                }
                if (!$table_in_list) {
                    $this->data['tables'][] = $table;
                    // Sort by name to keep it tidy
                    usort($this->data['tables'], function($a, $b) {
                        return strcasecmp($a->name, $b->name);
                    });
                }

                // Pass section_id and subsection_id to allow "Tables" tab to link back correctly
                $this->data['section_id'] = $table->section_id;
                $this->data['subsection_id'] = $table->subsection_id;
            }
            
            // Check for any open order instead of just 'Active'
            $order = $this->Restaurant_Order_Taking_model->get_open_order_for_table($table_id);
            if ($order) {
                $this->data['order'] = $order;
                $this->data['guests'] = $this->Restaurant_Order_Taking_model->get_order_guests($order->id);
                $this->data['order_items'] = $this->Restaurant_Order_Taking_model->get_order_items($order->id);
                $this->data['totals'] = $this->Restaurant_Order_Taking_model->get_order_totals($order->id);
            }
        }
        $this->data['view_mode'] = $this->input->get('view');
        $this->load->view($this->theme . 'restaurant/orders', $this->data);
    }

    /**
     * Load Tables - AJAX endpoint to load tables for a specific section
     */
    public function load_tables($section_id = null)
    {
        if ($this->input->is_ajax_request()) {
            $section_id = $section_id ?: $this->input->post('section_id');
            $subsection_id = $this->input->post('subsection_id');
            $tables = $this->Restaurant_Order_Taking_model->get_tables($section_id, $subsection_id);
            echo json_encode(['status' => 'success', 'tables' => $tables]);
        } else {
            show_404();
        }
    }

    /**
     * Load Subsections - AJAX endpoint to load subsections for a specific section
     */
    public function load_subsections($section_id = null)
    {
        if ($this->input->is_ajax_request()) {
            $section_id = $section_id ?: $this->input->post('section_id');
            $subsections = $this->Restaurant_Order_Taking_model->get_subsections($section_id);
            echo json_encode(['status' => 'success', 'subsections' => $subsections]);
        } else {
            show_404();
        }
    }

    /**
     * Unreserve Table - AJAX endpoint to free up a table reservation
     */
    public function unreserve_table()
    {
        $this->output->set_content_type('application/json');
        
        if (!$this->input->is_ajax_request()) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]));
        }

        // Get and validate input
        $table_id = (int)$this->input->post('table_id');
        
        // Validate required fields
        if (empty($table_id)) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Table ID is required'
            ]));
        }

        // Check if table exists
        $table = $this->Restaurant_Order_Taking_model->get_table($table_id);
        if (!$table) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Table not found'
            ]));
        }

        // Unreserve the table
        $result = $this->Restaurant_Order_Taking_model->unreserve_table($table_id);

        if ($result) {
            return $this->output->set_output(json_encode([
                'status' => 'success',
                'message' => 'Table reservation removed successfully',
                'table' => $this->Restaurant_Order_Taking_model->get_table($table_id)
            ]));
        } else {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Failed to remove table reservation. Please try again.'
            ]));
        }
    }
    
    /**
     * Reserve Table - AJAX endpoint to reserve a table for future use
     */
    public function reserve_table()
    {
        $this->output->set_content_type('application/json');
        
        if (!$this->input->is_ajax_request()) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]));
        }

        // Get and validate input
        $table_id = (int)$this->input->post('table_id');
        $reserved_by = trim((string)$this->input->post('reserved_by'));
        $reserved_until = trim((string)$this->input->post('reserved_until'));
        $reserved_note = trim((string)$this->input->post('reserved_note'));

        // Validate required fields
        if (empty($table_id) || empty($reserved_by) || empty($reserved_until)) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Table ID, reserved by, and reserved until are required'
            ]));
        }

        // Validate datetime format
        $reserved_until_ts = strtotime($reserved_until);
        if ($reserved_until_ts === false) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid reservation end time format. Please use YYYY-MM-DD HH:MM:SS'
            ]));
        }
        $reserved_until = date('Y-m-d H:i:s', $reserved_until_ts);

        // Check if table exists and is available
        $table = $this->Restaurant_Order_Taking_model->get_table($table_id);
        if (!$table) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Table not found'
            ]));
        }

        // Determine the correct status ID for a reserved table
        $reserved_status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Reserved');
        if (empty($reserved_status_id)) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Reserved status is not configured. Please contact the administrator.'
            ]));
        }

        // Check if table is already reserved
        if (isset($table->status_id) && (int)$table->status_id === (int)$reserved_status_id && !empty($table->reserved_until)) {
            $reserved_until_time = strtotime($table->reserved_until);
            if ($reserved_until_time > time()) {
                return $this->output->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Table is already reserved until ' . date('M j, Y g:i A', $reserved_until_time)
                ]));
            }
        }

        // Reserve the table and persist reservation metadata
        $result = $this->Restaurant_Order_Taking_model->update_table_reservation(
            $table_id,
            $reserved_status_id,
            $reserved_until,
            $reserved_by,
            $reserved_note,
            false
        );

        if ($result) {
            return $this->output->set_output(json_encode([
                'status' => 'success',
                'message' => 'Table reserved successfully',
                'table' => $this->Restaurant_Order_Taking_model->get_table($table_id)
            ]));
        } else {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Failed to reserve table. Please try again.'
            ]));
        }
    }


    

    /**
     * Open Table - AJAX endpoint to open a table for taking orders
     */
    public function open_table($table_id = null)
    {
        if ($this->input->is_ajax_request()) {
            $table_id = $table_id ?: $this->input->post('table_id');
            $table = $this->Restaurant_Order_Taking_model->get_table($table_id);
            $existing_order = $this->Restaurant_Order_Taking_model->get_active_order($table_id);
            
            echo json_encode([
                'status' => 'success', 
                'table' => $table,
                'existing_order' => $existing_order
            ]);
        } else {
            show_404();
        }
    }

    /**
     * Get Order Status - Lightweight endpoint for polling order status
     */
    public function get_order_status()
    {
        // Allow GET or POST; do not force AJAX since browsers may hit this URL directly
        $order_id = (int)($this->input->get('order_id') ?: $this->input->post('order_id'));
        $table_id = (int)($this->input->get('table_id') ?: $this->input->post('table_id'));

        if (!$order_id && $table_id) {
            // Resolve open order by table
            $order = $this->Restaurant_Order_Taking_model->get_open_order_for_table($table_id);
            $order_id = $order ? (int)$order->id : 0;
        }

        if (!$order_id) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'order_id or table_id required']));
            return;
        }

        $order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
        if (!$order) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Order not found']));
            return;
        }

        // Aggregate item status counts
        $q = $this->db->select('status, COUNT(*) AS cnt')
            ->from('sma_res_orders_items')
            ->where('res_orders_id', $order_id)
            ->group_by('status')
            ->get();
        $item_counts = [];
        $total_items = 0;
        foreach ($q->result() as $row) {
            $item_counts[$row->status ?: 'Unknown'] = (int)$row->cnt;
            $total_items += (int)$row->cnt;
        }

        $payload = [
            'status' => 'success',
            'order' => [
                'id' => (int)$order_id,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ],
            'items' => [
                'total' => $total_items,
                'by_status' => $item_counts,
            ],
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    /**
     * Create Order - AJAX endpoint to create a new restaurant order
     */
    public function create_order()
    {
        if ($this->input->is_ajax_request()) {
            $table_id = $this->input->post('table_id');
            $guest_count = $this->input->post('guest_count');
            
            $order_data = [
                'res_tables_id' => $table_id,
                'guest_count' => $guest_count,
                'status' => 'Active',
                'payment_status' => 'Pending',
                'order_type' => 'Dine-In',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $order_id = $this->Restaurant_Order_Taking_model->create_order($order_data);
            
            if ($order_id) {
                // Create guest entries
                for ($i = 1; $i <= $guest_count; $i++) {
                    $this->Restaurant_Order_Taking_model->create_guest($order_id);
                }
                
                // Update table status to occupied
                $this->Restaurant_Order_Taking_model->update_table_status($table_id, 2); // 2 = Occupied
                
                echo json_encode(['status' => 'success', 'order_id' => $order_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create order']);
            }
        } else {
            show_404();
        }
    }

    /**
     * Order Screen - Main interface for adding items to restaurant orders
     */
    public function order_screen($order_id = null)
    {
        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        log_message('debug', 'order_screen called with order_id: ' . $order_id);
        
        $order_id = $order_id ?: $this->input->post('order_id');
        
        log_message('debug', 'Processed order_id: ' . $order_id);
        
        if (!$order_id) {
            log_message('error', 'No order_id provided');
            show_404();
        }
        
        // Log database connection status
        log_message('debug', 'Database host: ' . $this->db->hostname);
        
        // Get order data
        $this->data['order'] = $this->Restaurant_Order_Taking_model->get_order($order_id);
        log_message('debug', 'Order data: ' . print_r($this->data['order'], true));
        
        if (!$this->data['order']) {
            log_message('error', 'Order not found with id: ' . $order_id);
            show_404();
        }
        
        // Get related data
        $this->data['guests'] = $this->Restaurant_Order_Taking_model->get_order_guests($order_id);
        log_message('debug', 'Guests count: ' . count($this->data['guests']));
        
        $this->data['menu_categories'] = $this->Restaurant_Order_Taking_model->get_menu_categories();
        log_message('debug', 'Menu categories count: ' . count($this->data['menu_categories']));
        
        $this->data['meal_types'] = $this->Restaurant_Order_Taking_model->get_meal_types();
        log_message('debug', 'Meal types count: ' . count($this->data['meal_types']));
        
        $this->data['allergies'] = $this->Restaurant_Order_Taking_model->get_allergies();
        log_message('debug', 'Allergies count: ' . count($this->data['allergies']));
        $this->data['meat_wellness'] = $this->Restaurant_Order_Taking_model->get_meat_wellness();
        $order_items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        // Enrich order items with resolved allergy/add-on/topping names for display
        $allergy_map = [];
        foreach ($this->data['allergies'] as $a) { $allergy_map[$a->id] = $a->name; }
        $add_on_map = [];
        foreach ($this->Restaurant_Order_Taking_model->get_add_ons() as $a) { $add_on_map[$a->id] = $a->name; }
        $topping_map = [];
        foreach ($this->Restaurant_Order_Taking_model->get_toppings() as $t) { $topping_map[$t->id] = $t->name; }
        foreach ($order_items as $it) {
            $it->allergy_names = [];
            if (!empty($it->sma_res_common_allergies_list)) {
                $ids = array_filter(array_map('trim', explode(',', $it->sma_res_common_allergies_list)));
                foreach ($ids as $id) { if (isset($allergy_map[$id])) { $it->allergy_names[] = $allergy_map[$id]; } }
            }
            $it->add_on_names = [];
            if (!empty($it->on_add_on_id)) {
                $ids = array_filter(array_map('trim', explode(',', $it->on_add_on_id)));
                foreach ($ids as $id) { if (isset($add_on_map[$id])) { $it->add_on_names[] = $add_on_map[$id]; } }
            }
            $it->topping_names = [];
            if (!empty($it->on_toppings_id)) {
                $ids = array_filter(array_map('trim', explode(',', $it->on_toppings_id)));
                foreach ($ids as $id) { if (isset($topping_map[$id])) { $it->topping_names[] = $topping_map[$id]; } }
            }
        }
        $this->data['order_items'] = $order_items;
        $this->data['items_by_guest'] =$this->Restaurant_Order_Taking_model->get_order_items_grouped_by_guest($order_id);
        $this->load->view($this->theme . 'restaurant/order_screen', $this->data);
    }

    // Dedicated Kitchen View for managing a specific order by order_id or table_id
    public function kitchen_view($order_id = null)
    {
        // Accept order_id via param or query; allow table_id to resolve active order
        $order_id = (int)($order_id ?: $this->input->get('order_id'));
        $table_id = (int)$this->input->get('table_id');

        if (!$order_id && $table_id) {
            $open_order = $this->Restaurant_Order_Taking_model->get_open_order_for_table($table_id);
            if ($open_order) { $order_id = (int)$open_order->id; }
        }

        if (!$order_id) { show_404(); }

        $order = $this->Restaurant_Order_Taking_model->get_order($order_id);
        if (!$order) { show_404(); }

        // Fetch items and resolve display names similar to kot()
        $items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        $allergies = $this->Restaurant_Order_Taking_model->get_allergies();
        $allergy_map = [];
        foreach ($allergies as $a) { $allergy_map[$a->id] = $a->name; }
        $add_on_map = [];
        foreach ($this->Restaurant_Order_Taking_model->get_add_ons() as $a) { $add_on_map[$a->id] = $a->name; }
        $topping_map = [];
        foreach ($this->Restaurant_Order_Taking_model->get_toppings() as $t) { $topping_map[$t->id] = $t->name; }

        foreach ($items as $it) {
            $it->allergy_names = [];
            if (!empty($it->sma_res_common_allergies_list)) {
                $ids = array_filter(array_map('trim', explode(',', $it->sma_res_common_allergies_list)));
                foreach ($ids as $id) { if (isset($allergy_map[$id])) { $it->allergy_names[] = $allergy_map[$id]; } }
            }
            $it->add_on_names = [];
            if (!empty($it->on_add_on_id)) {
                $ids = array_filter(array_map('trim', explode(',', $it->on_add_on_id)));
                foreach ($ids as $id) { if (isset($add_on_map[$id])) { $it->add_on_names[] = $add_on_map[$id]; } }
            }
            $it->topping_names = [];
            if (!empty($it->on_toppings_id)) {
                $ids = array_filter(array_map('trim', explode(',', $it->on_toppings_id)));
                foreach ($ids as $id) { if (isset($topping_map[$id])) { $it->topping_names[] = $topping_map[$id]; } }
            }
        }

        $data = [
            'order' => $order,
            'items' => $items,
        ];

        $this->load->view($this->theme . 'restaurant/kitchen_view', $data);
    }

    // Render Kitchen Order Ticket for a given order
    public function kot($order_id = null)
    {
        $order_id = (int)($order_id ?: $this->input->get('order_id'));
        if (!$order_id) { show_404(); }

        $order = $this->Restaurant_Order_Taking_model->get_order($order_id);
        if (!$order) { show_404(); }

        // Fetch items and resolve display names
        $items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        $allergies = $this->Restaurant_Order_Taking_model->get_allergies();
        $allergy_map = [];
        foreach ($allergies as $a) { $allergy_map[$a->id] = $a->name; }
        $add_on_map = [];
        foreach ($this->Restaurant_Order_Taking_model->get_add_ons() as $a) { $add_on_map[$a->id] = $a->name; }
        $topping_map = [];
        foreach ($this->Restaurant_Order_Taking_model->get_toppings() as $t) { $topping_map[$t->id] = $t->name; }

        foreach ($items as $it) {
            // Preset allergy names by ID
            $it->allergy_names = [];
            if (!empty($it->sma_res_common_allergies_list)) {
                $ids = array_filter(array_map('trim', explode(',', $it->sma_res_common_allergies_list)));
                foreach ($ids as $id) { if (isset($allergy_map[$id])) { $it->allergy_names[] = $allergy_map[$id]; } }
            }
            // Add-ons
            $it->add_on_names = [];
            if (!empty($it->on_add_on_id)) {
                $ids = array_filter(array_map('trim', explode(',', $it->on_add_on_id)));
                foreach ($ids as $id) { if (isset($add_on_map[$id])) { $it->add_on_names[] = $add_on_map[$id]; } }
            }
            // Toppings
            $it->topping_names = [];
            if (!empty($it->on_toppings_id)) {
                $ids = array_filter(array_map('trim', explode(',', $it->on_toppings_id)));
                foreach ($ids as $id) { if (isset($topping_map[$id])) { $it->topping_names[] = $topping_map[$id]; } }
            }
            // Optional: format a brief instructions line if needed later
            $it->instructions_line = isset($it->special_instructions) ? trim((string)$it->special_instructions) : '';
        }

        $data = [
            'order' => $order,
            'items' => $items,
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        $this->load->view($this->theme . 'restaurant/kot', $data);
    }

    // Render a printable order bill (pre-finalization) for the given order
    public function order_bill($order_id = null)
    {
        $order_id = (int)($order_id ?: $this->input->get('order_id'));
        if (!$order_id) { show_404(); }

        $order = $this->Restaurant_Order_Taking_model->get_order($order_id);
        if (!$order) { show_404(); }

        $items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        $guests = $this->Restaurant_Order_Taking_model->get_order_guests($order_id);
        $totals = $this->Restaurant_Order_Taking_model->get_order_totals($order_id);

        $this->data['order'] = $order;
        $this->data['items'] = $items;
        $this->data['guests'] = $guests;
        $this->data['totals'] = $totals;
        $this->data['generated_at'] = date('Y-m-d H:i:s');

        $this->load->view($this->theme . 'restaurant/order_bill', $this->data);
    }

    // Create suspended bill/items (KOT) for KDS from a restaurant order
    public function push_to_kds()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $order_id = (int)$this->input->post('order_id');
        if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }

        $order = $this->Restaurant_Order_Taking_model->get_order($order_id);
        if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }

        $items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        if (empty($items)) { echo json_encode(['status' => 'error', 'message' => 'No items in order']); return; }

        // Defaults from POS settings
        $date = date('Y-m-d H:i:s');
        $warehouse_id = isset($this->pos_settings->default_warehouse) ? (int)$this->pos_settings->default_warehouse : 1;
        $customer_id = isset($this->pos_settings->default_customer) ? (int)$this->pos_settings->default_customer : 1;
        $biller_id = isset($this->pos_settings->default_biller) ? (int)$this->pos_settings->default_biller : 1;

        $customer_details = $this->site->getCompanyByID($customer_id);
        $customer = $customer_details ? ($customer_details->company != '-' ? $customer_details->company : $customer_details->name) : 'Walk-in Customer';

        // Build suspended bill data
        $grand_total = 0; $total_items = 0;
        foreach ($items as $it) { $grand_total += (float)$it->amount; $total_items += 1; }

        $suspend_data = [
            'total_items' => $total_items,
            'biller_id' => $biller_id,
            'customer_id' => $customer_id,
            'warehouse_id' => $warehouse_id,
            'customer' => $customer,
            'date' => $date,
            'suspend_note' => isset($order->table_name) ? $order->table_name : ('Table #' . (int)$order->res_tables_id),
            'table_id' => isset($order->res_tables_id) ? (int)$order->res_tables_id : null,
            'grand_total' => $this->sma->formatDecimal($grand_total),
            'order_tax_id' => null,
            'order_discount_id' => null,
        ];

        // Build suspended items array
        $suspended_items = [];
        foreach ($items as $it) {
            $prod = $this->site->getProductByID($it->sma_product_id);
            if (!$prod) { continue; }
            $note_bits = [];
            if (!empty($it->spice_level)) { $note_bits[] = 'Spice: ' . $it->spice_level; }
            if (!empty($it->meat_wellness_type)) { $note_bits[] = 'Meat: ' . $it->meat_wellness_type; }
            if (!empty($it->special_instructions)) { $note_bits[] = trim($it->special_instructions); }
            $line = [
                'product_id' => (int)$prod->id,
                'product_name' => $prod->name,
                'option_id' => 0,
                'quantity' => (float)$it->quantity,
                'note' => implode(' | ', $note_bits),
                'isdelivered' => 0,
            ];
            $suspended_items[] = $line;
        }

        if (empty($suspended_items)) { echo json_encode(['status' => 'error', 'message' => 'No valid items to push']); return; }

        // Insert suspended bill + items (creates/updates KOT)
        $ok = $this->pos_model->suspendSale($suspend_data, $suspended_items, null);
        if ($ok) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create KOT']);
        }
    }

    // Increase guest: add one guest record and update order guest_count
    public function increase_guest()
    {
        if ($this->input->is_ajax_request()) {
            $order_id = (int)$this->input->post('order_id');
            if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }
            $guest_id = $this->Restaurant_Order_Taking_model->create_guest($order_id);
            if ($guest_id) {
                // increment guest count on order
                $order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
                $this->Restaurant_Order_Taking_model->update_order($order_id, ['guest_count' => ((int)$order->guest_count)+1]);
                echo json_encode(['status' => 'success', 'guest_id' => $guest_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add guest']);
            }
        } else { show_404(); }
    }

    // Decrease guest: remove latest guest without items; prevent deletion if items exist
    public function decrease_guest()
    {
        if ($this->input->is_ajax_request()) {
            $order_id = (int)$this->input->post('order_id');
            if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }
            $guest = $this->Restaurant_Order_Taking_model->get_last_guest_without_items($order_id);
            if (!$guest) { echo json_encode(['status' => 'error', 'message' => 'All guests have items; cannot delete']); return; }
            if ($this->Restaurant_Order_Taking_model->delete_guest_if_no_items($guest->id)) {
                // decrement guest count on order
                $order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
                $new_count = max(0, ((int)$order->guest_count)-1);
                $this->Restaurant_Order_Taking_model->update_order($order_id, ['guest_count' => $new_count]);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Unable to delete guest']);
            }
        } else { show_404(); }
    }

    public function get_menu_items()
    {
        if ($this->input->is_ajax_request()) {
            $category_id = $this->input->post('category_id');
            $subcategory_id = $this->input->post('subcategory_id');
            $meal_type = $this->input->post('meal_type');
            $search = $this->input->post('search');
            $group_by_category = $this->input->post('group_by_category');
            
            $filters = [];
            if (!empty($category_id)) { $filters['category_id'] = $category_id; }
            if (!empty($subcategory_id)) { $filters['subcategory_id'] = $subcategory_id; }
            if (!empty($meal_type)) { $filters['meal_type'] = $meal_type; }
            if (!empty($search)) { $filters['search'] = $search; }
            
            if (!empty($group_by_category) && (int)$group_by_category === 1) {
                $data = $this->Restaurant_Order_Taking_model->get_menu_items_grouped($filters);
                echo json_encode(['status' => 'success', 'grouped' => true, 'data' => $data]);
            } else {
                $items = $this->Restaurant_Order_Taking_model->get_menu_items($filters);
                echo json_encode(['status' => 'success', 'items' => $items]);
            }
        } else {
            show_404();
        }
    }

    public function get_product_customizations()
    {
        if ($this->input->is_ajax_request()) {
            $product_id = (int) $this->input->post('product_id');
            if (!$product_id) { echo json_encode(['status' => 'error', 'message' => 'product_id required']); return; }
            // Fetch product-specific mappings first
            $add_ons = $this->Restaurant_Order_Taking_model->get_product_add_ons($product_id);
            $toppings = $this->Restaurant_Order_Taking_model->get_product_toppings($product_id);
            // Fallback: if no mappings exist for this product, return all active master entries
            if (empty($add_ons)) {
                $add_ons = $this->Restaurant_Order_Taking_model->get_add_ons();
            }
            if (empty($toppings)) {
                $toppings = $this->Restaurant_Order_Taking_model->get_toppings();
            }
            // Common allergies from master (shown as horizontal chip grid in UI)
            $allergies = $this->Restaurant_Order_Taking_model->get_allergies();
            // Meat wellness master
            $meat_wellness = $this->Restaurant_Order_Taking_model->get_meat_wellness();
            
            $data = [
                'add_ons' => $add_ons,
                'toppings' => $toppings,
                'allergies' => $allergies,
                'meat_wellness' => $meat_wellness, // Added this
                'product_id' => $product_id,
                'Settings' => $this->Settings,
            ];

            // Load view into variable
            $html = $this->load->view($this->theme . 'restaurant/modal_product_customizations', $data, true);

            echo json_encode([
                'status' => 'success',
                'html' => $html,
            ]);
        } else {
            show_404();
        }
    }

    public function get_customizations()
    {
        if ($this->input->is_ajax_request()) {
            $product_id = (int) $this->input->post('product_id');
            if (!$product_id) { 
                echo json_encode(['status' => 'error', 'message' => 'product_id required']); 
                return; 
            }
            
            // Fetch product-specific mappings first
            $add_ons = $this->Restaurant_Order_Taking_model->get_product_add_ons($product_id);
            $toppings = $this->Restaurant_Order_Taking_model->get_product_toppings($product_id);
            
            // Fallback: if no mappings exist for this product, return all active master entries
            if (empty($add_ons)) {
                $add_ons = $this->Restaurant_Order_Taking_model->get_add_ons();
            }
            if (empty($toppings)) {
                $toppings = $this->Restaurant_Order_Taking_model->get_toppings();
            }
            
            // Common allergies from master
            $allergies = $this->Restaurant_Order_Taking_model->get_allergies();
            // Meat wellness master
            $meat_wellness = $this->Restaurant_Order_Taking_model->get_meat_wellness();
            
            $data = [
                'add_ons' => $add_ons,
                'toppings' => $toppings,
                'allergies' => $allergies,
                'meat_wellness' => $meat_wellness,
                'product_id' => $product_id,
                'Settings' => $this->Settings,
            ];

            // Load view into variable
            $html = $this->load->view($this->theme . 'restaurant/modal_product_customizations', $data, true);

            echo json_encode([
                'status' => 'success',
                'html' => $html,
            ]);
        } else {
            show_404();
        }
    }

    // Return product details for a given product_id
    public function get_product_details()
    {
        $this->output->set_content_type('application/json');

        if (!$this->input->is_ajax_request()) {
            return $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]));
        }

        $product_id = (int) $this->input->post('product_id');
        if (!$product_id) {
            $resp = [
                'status' => 'error',
                'message' => 'product_id required'
            ];
            // Return fresh CSRF token for next requests
            $resp[$this->security->get_csrf_token_name()] = $this->security->get_csrf_hash();
            return $this->output->set_output(json_encode($resp));
        }

        // Try rich details first, then basic details as fallback
        $product = $this->Restaurant_Order_Taking_model->get_product_details($product_id);
        if (!$product) {
            $product = $this->Restaurant_Order_Taking_model->get_product_basic_details($product_id);
        }

        if ($product) {
            $resp = [
                'status' => 'success',
                'data' => $product,
            ];
        } else {
            $resp = [
                'status' => 'error',
                'message' => 'Product not found'
            ];
        }

        // Always include CSRF for subsequent POSTs
        $resp[$this->security->get_csrf_token_name()] = $this->security->get_csrf_hash();
        return $this->output->set_output(json_encode($resp));
    }

    // Expose table statuses master for dynamic UI mapping
    public function table_statuses()
    {
        // Allow GET or AJAX for flexibility
        $statuses = $this->Restaurant_Order_Taking_model->get_table_statuses();
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $statuses]));
    }


    // Add a new allergy name to master (or return existing) and respond with its ID
    public function add_allergy()
    {
        // Accept normal POST too (some setups may not set X-Requested-With)
        $name = trim((string)$this->input->post('name'));
        $resp = [];
        if ($name === '') {
            $resp = ['status' => 'error', 'message' => 'Allergy name required'];
        } else {
            $id = $this->Restaurant_Order_Taking_model->ensure_allergy_exists($name);
            if ($id) {
                $resp = ['status' => 'success', 'id' => (int)$id, 'name' => $name];
            } else {
                $resp = ['status' => 'error', 'message' => 'Failed to save allergy'];
            }
        }
        // Always return fresh CSRF token for subsequent POSTs
        $resp[$this->security->get_csrf_token_name()] = $this->security->get_csrf_hash();
        return $this->output->set_content_type('application/json')->set_output(json_encode($resp));
    }

    // Add an item to order (AJAX)
    public function add_item()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $order_id = $this->input->post('order_id');
        $guest_id = $this->input->post('guest_id');
        $product_id = $this->input->post('product_id');
        // Accept decimal quantity per Settings->qty_decimals
        $quantity = $this->sma->formatDecimal($this->input->post('quantity'), isset($this->Settings->qty_decimals) ? (int)$this->Settings->qty_decimals : null);
        $spice_level = $this->input->post('spice_level');
        $meat_wellness = $this->input->post('meat_wellness');
        $allergies = $this->input->post('allergies'); // array or CSV
        $custom_allergies = $this->input->post('custom_allergies'); // pipe-delimited or array
        $add_ons = $this->input->post('add_ons'); // array or CSV
        $toppings = $this->input->post('toppings'); // array or CSV
        $onion_flag = $this->input->post('onion_flag');
        $garlic_flag = $this->input->post('garlic_flag');
        $special_instructions = $this->input->post('special_instructions');

        if (is_string($allergies) && strlen($allergies)) { $allergies = explode(',', $allergies); }
        if (!is_array($allergies)) { $allergies = []; }
        if (is_string($add_ons) && strlen($add_ons)) { $add_ons = explode(',', $add_ons); }
        if (!is_array($add_ons)) { $add_ons = []; }
        if (is_string($toppings) && strlen($toppings)) { $toppings = explode(',', $toppings); }
        if (!is_array($toppings)) { $toppings = []; }

        // Get product price
        $product = $this->Restaurant_Order_Taking_model->get_product_details($product_id);
        if (!$product) { $product = $this->Restaurant_Order_Taking_model->get_product_basic_details($product_id); }
        if (!$product) { echo json_encode(['status' => 'error', 'message' => 'Invalid product']); return; }

        $add_on_total = $this->Restaurant_Order_Taking_model->sum_add_on_prices($add_ons);
        $topping_total = $this->Restaurant_Order_Taking_model->sum_topping_prices($toppings);
        $base_price = isset($product->price) ? (float)$product->price : 0.0;
        $unit_price = $base_price + (float)$add_on_total + (float)$topping_total;
        // Allow fractional quantities; ensure positive
        if ($quantity <= 0) { echo json_encode(['status' => 'error', 'message' => 'Invalid quantity']); return; }
        $line_amount = $unit_price * $quantity;

        $item_data = [
            'sma_product_id' => $product_id,
            'res_orders_id' => $order_id,
            'res_orders_guests_id' => $guest_id,
            'quantity' => $quantity,
            'mrp' => $unit_price,
            'price' => $unit_price,
            'discount' => 0,
            'amount' => $line_amount,
            'spice_level' => $spice_level,
            'sma_res_meat_wellness_id' => $meat_wellness,
            'sma_res_common_allergies_list' => $allergies ? implode(',', $allergies) : null,
            'on_add_on_id' => !empty($add_ons) ? implode(',', (array)$add_ons) : null,
            'on_toppings_id' => !empty($toppings) ? implode(',', (array)$toppings) : null,
            'onion_flag' => $onion_flag,
            'garlic_flag' => $garlic_flag,
            'meal_type_id' => isset($product->meal_type_id) ? $product->meal_type_id : null,
            'status' => 'Pending',
            'created_by' => $this->session->userdata('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($special_instructions)) {
            $item_data['special_instructions'] = trim($special_instructions);
        }

        // Persist custom allergies if a safe column exists
        if (!empty($custom_allergies)) {
            if ($this->db->field_exists('custom_allergies_text', 'sma_res_orders_items')) {
                $item_data['custom_allergies_text'] = is_array($custom_allergies)
                    ? implode('|', $custom_allergies)
                    : trim($custom_allergies);
            } elseif ($this->db->field_exists('special_instructions', 'sma_res_orders_items')) {
                $appendText = is_array($custom_allergies)
                    ? implode(', ', $custom_allergies)
                    : trim($custom_allergies);
                $bracketed = '[Allergies: ' . $appendText . ']';
                if (empty($item_data['special_instructions'])) {
                    $item_data['special_instructions'] = $bracketed;
                } else {
                    $item_data['special_instructions'] = trim($item_data['special_instructions'] . ' ' . $bracketed);
                }
            }
        }

        $item_id = $this->Restaurant_Order_Taking_model->add_order_item($item_data);

        if ($item_id) {
            echo json_encode(['status' => 'success', 'item_id' => $item_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add item']);
        }
    }

    // API to mark order complete and free table in one action (for post-payment webhook or manual)
    public function complete_and_free()
    {
        if ($this->input->is_ajax_request()) {
            $order_id = (int)$this->input->post('order_id');
            if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }
            $order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
            if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }

            // Mark order complete/paid
            $this->Restaurant_Order_Taking_model->update_order($order_id, ['status' => 'Completed', 'payment_status' => 'Paid']);
            // Free table
            if (!empty($order->res_tables_id)) {
                $this->Restaurant_Order_Taking_model->set_table_status_by_value($order->res_tables_id, 'Available');
            }
            echo json_encode(['status' => 'success']);
        } else {
            show_404();
        }
    }

    /**
     * Finalize Order - AJAX endpoint to convert restaurant order to POS sale
     * 
     * Purpose: Convert a completed restaurant order into a POS sale and free the table
     * Usage: Called via AJAX when user clicks "Finalize Order" button
     * 
     * Parameters:
     * - $_POST['order_id']: ID of the restaurant order to finalize
     * 
     * Process:
     * 1. Validates order exists and has items
     * 2. Creates POS sale with all order items
     * 3. Handles taxes and payments
     * 4. Updates order status to 'Completed'
     * 5. Frees the table (sets status to 'Available')
     * 6. Generates invoice for printing
     * 
     * Returns:
     * - JSON response with invoice URL on success
     * - Error message on failure
     * 
     * Security: Only accepts AJAX requests
     * 
     * Used by: Frontend JavaScript for order finalization
     */
    // Convert restaurant order into a POS sale and free the table
    public function finalize_order()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $order_id = (int)$this->input->post('order_id');
        if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }

        $order = $this->Restaurant_Order_Taking_model->get_order($order_id);
        if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }

        // Fetch order items
        $items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        if (empty($items)) { echo json_encode(['status' => 'error', 'message' => 'No items in order']); return; }

        // Defaults from settings
        $date = date('Y-m-d H:i:s');
        $warehouse_id = isset($this->pos_settings->default_warehouse) ? (int)$this->pos_settings->default_warehouse : 1;
        $customer_id = isset($this->pos_settings->default_customer) ? (int)$this->pos_settings->default_customer : 1;
        $biller_id = isset($this->pos_settings->default_biller) ? (int)$this->pos_settings->default_biller : 1;

        $customer_details = $this->site->getCompanyByID($customer_id);
        $customer = $customer_details ? ($customer_details->company != '-' ? $customer_details->company : $customer_details->name) : 'Walk-in Customer';
        $biller_details = $this->site->getCompanyByID($biller_id);
        $biller = $biller_details ? ($biller_details->company != '-' ? $biller_details->company : $biller_details->name) : 'Default Biller';

        $reference = $this->site->getReference('so');

        // Build sale items array similar to POS controller
        $sale_products = [];
        $total = 0; $product_tax = 0; $product_discount = 0;
        foreach ($items as $it) {
            $prod = $this->site->getProductByID($it->sma_product_id);
            if (!$prod) { continue; }

            $item_quantity = (float)$it->quantity;
            $real_unit_price = (float)$it->price; // unit price incl. add-ons/toppings (from order)
            $unit_price = $real_unit_price; // assuming tax-exclusive pricing, no per-item discount
            $item_net_price = $this->sma->formatDecimal($unit_price);
            $item_tax_rate_id = null; // if needed, map later; currently not captured on order items
            $pr_item_tax = 0; $tax = '';
            $pr_item_discount = 0; $discount = null;

            $subtotal = $this->sma->formatDecimal(($item_net_price * $item_quantity) + $pr_item_tax);

            $note_bits = [];
            if (!empty($it->sma_res_common_allergies_list)) { $note_bits[] = 'Allergies IDs: ' . $it->sma_res_common_allergies_list; }
            if (!empty($it->on_add_on_id)) { $note_bits[] = 'Add-ons: ' . $it->on_add_on_id; }
            if (!empty($it->on_toppings_id)) { $note_bits[] = 'Toppings: ' . $it->on_toppings_id; }
            if (!empty($it->spice_level)) { $note_bits[] = 'Spice: ' . $it->spice_level; }
            if (!empty($it->meat_wellness_type)) { $note_bits[] = 'Meat: ' . $it->meat_wellness_type; }
            if (!empty($it->special_instructions)) { $note_bits[] = trim($it->special_instructions); }
            $item_note = implode(' | ', $note_bits);

            $unit = $this->site->getUnitByID(isset($prod->unit) ? $prod->unit : null);
            $sale_products[] = array(
                'product_id' => $prod->id,
                'product_code' => $prod->code,
                'product_name' => $prod->name,
                'product_type' => $prod->type,
                'option_id' => null,
                'net_unit_price' => $item_net_price,
                'unit_price' => $this->sma->formatDecimal($item_net_price + $pr_item_tax),
                'quantity' => $item_quantity,
                'product_unit_id' => $unit ? $unit->id : (isset($prod->unit) ? $prod->unit : null),
                'product_unit_code' => $unit ? $unit->code : null,
                'unit_quantity' => $item_quantity,
                'warehouse_id' => $warehouse_id,
                'item_tax' => $pr_item_tax,
                'tax_rate_id' => $item_tax_rate_id,
                'tax' => $tax,
                'discount' => $discount,
                'item_discount' => $pr_item_discount,
                'subtotal' => $subtotal,
                'serial_no' => '',
                'real_unit_price' => $real_unit_price,
                'mrp' => $real_unit_price,
                'hsn_code' => '',
                'note' => $item_note,
            );

            $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
            $product_tax += $pr_item_tax;
            $product_discount += $pr_item_discount;
        }

        if (empty($sale_products)) { echo json_encode(['status' => 'error', 'message' => 'No valid sale items']); return; }

        // Totals and sale data
        $order_discount = 0; $order_tax = 0; $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
        $total_tax = $this->sma->formatDecimal($product_tax + $order_tax, 4);
        $shipping = 0;
        $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping - $order_discount), 4);
        $rounding = 0;
        if (!empty($this->pos_settings->rounding) && $this->pos_settings->rounding > 0) {
            $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
            $rounding = ($round_total - $grand_total);
            $grand_total = $round_total;
        }

        $sale_data = array(
            'date' => $date,
            'reference_no' => $reference,
            'customer_id' => $customer_id,
            'customer' => $customer,
            'biller_id' => $biller_id,
            'biller' => $biller,
            'warehouse_id' => $warehouse_id,
            'note' => 'Restaurant Order #' . $order_id,
            'staff_note' => null,
            'total' => $total,
            'product_discount' => $product_discount,
            'order_discount_id' => null,
            'order_discount' => $order_discount,
            'total_discount' => $total_discount,
            'product_tax' => $product_tax,
            'order_tax_id' => null,
            'order_tax' => $order_tax,
            'total_tax' => $total_tax,
            'shipping' => $this->sma->formatDecimal($shipping),
            'grand_total' => $grand_total,
            'total_items' => count($sale_products),
            'sale_status' => 'completed',
            'payment_status' => 'paid',
            'payment_term' => 0,
            'rounding' => $rounding,
            'pos' => 1,
            'paid' => $grand_total,
            'created_by' => $this->session->userdata('user_id'),
        );

        // Single payment entry (cash) for full amount
        $payments = array();
        $payments[] = array(
            'date' => $date,
            'amount' => $grand_total,
            'paid_by' => 'cash',
            'created_by' => $this->session->userdata('user_id'),
            'type' => 'received',
            'note' => 'Restaurant finalization',
            'pos_paid' => $grand_total,
            'pos_balance' => 0,
        );

        $extras = array(
            'sale_action' => 'sale',
            'order_id' => null,
            'syncQuantity' => 1,
        );

        $sale_id = $this->sales_model->addSale($sale_data, $sale_products, $payments, [], $extras);
        if ($sale_id) {
            // Update restaurant order and free table
            $totals = $this->Restaurant_Order_Taking_model->get_order_totals($order_id);
            $update_payload = [
                'status' => 'Completed',
                'payment_status' => 'Paid',
                'subtotal' => isset($totals['subtotal']) ? $totals['subtotal'] : null,
                'sgst_value' => isset($totals['sgst']) ? $totals['sgst'] : null,
                'cgst_value' => isset($totals['cgst']) ? $totals['cgst'] : null,
                'granttotal' => isset($totals['grand_total']) ? $totals['grand_total'] : null,
            ];
            $this->Restaurant_Order_Taking_model->update_order($order_id, $update_payload);
            if (!empty($order->res_tables_id)) {
                $this->Restaurant_Order_Taking_model->set_table_status_by_value($order->res_tables_id, 'Available');
            }

            // Use restaurant-branded invoice wrapper to auto-print and redirect back
            $invoice_url = site_url('Restaurant_Order_Taking/invoice/' . $sale_id);
            echo json_encode(['status' => 'success', 'sale_id' => $sale_id, 'invoice_url' => $invoice_url]);
            return;
        }

        echo json_encode(['status' => 'error', 'message' => 'Failed to create sale']);
    }
    /**
     * Finalize Screen - Display order finalization interface
     */
    public function finalize_screen($order_id)
    {
        $order = $this->Restaurant_Order_Taking_model->get_order($order_id);
        if (!$order) show_404();

        // Update order status to 'Finalizing' and free table when finalize screen is accessed
        $this->Restaurant_Order_Taking_model->update_order($order_id, ['status' => 'Finalizing']);
        if (isset($order->res_tables_id) && !empty($order->res_tables_id)) {
            $this->Restaurant_Order_Taking_model->set_table_status_by_value($order->res_tables_id, 'Free');
        }

        $items = $this->Restaurant_Order_Taking_model->get_order_items($order_id);
        $guests = $this->Restaurant_Order_Taking_model->get_order_guests($order_id);
        $totals = $this->Restaurant_Order_Taking_model->get_order_totals($order_id);
        
        // Get only the current table
        $table = $this->Restaurant_Order_Taking_model->get_table($order->res_tables_id);

        $data['order'] = $order;
        $data['items'] = $items;
        $data['guests'] = $guests;
        $data['totals'] = $totals;
        $data['table'] = $table;

        $this->load->view($this->theme .'restaurant/finalize_screen', $data);
    }
    public function barcode($text = null, $bcs = 'code128', $height = 50) {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }
    // Restaurant-branded invoice view with auto-print behaviour
    public function invoice($sale_id = null, $modal = null)
    {
        // Keep route simple; if id passed via query, honor it
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        if (!$sale_id) {
            show_404();
        }

        $this->load->helper('text');

        $this->data['myclass'] = $ci = &get_instance();
        $this->data['pos_settingss'] = $this->data['pos_settings'];
        $this->data['go_back'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('Restaurant_Order_Taking');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['Customer_assets'] = $this->Customer_assets;

        $_PID = $this->Settings->default_printer;
        $this->data['default_printer'] = $this->site->defaultPrinterOption($_PID);

        $cfields = $this->site->getCustomeFieldsLabel('customer');
        $this->data['custome_fields'] = isset($cfields['customer']) ? $cfields['customer'] : array();

        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$inv) {
            show_404();
        }

        if (!empty($this->data['default_printer']->tax_classification_view)) {
            $inv->rows_tax = $this->sales_model->getAllTaxItems($sale_id, $inv->return_id);
        }

        $isGstSale = $this->site->isGstSale($sale_id);
        $inv->GstSale = !empty($isGstSale) ? 1 : 0;

        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }

        $rows = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller = $this->pos_model->getCompanyByID($inv->biller_id);
        $customer = $this->pos_model->getCompanyByID($inv->customer_id);
        $payments = $this->pos_model->getInvoicePayments($sale_id);
        $pos_settings = $this->pos_model->getSetting();
        unset($pos_settings->pos_theme);
        
        $barcode = $this->barcode($inv->reference_no, 'code128', 30);
        $brcode = $this->sma->save_barcode($inv->reference_no, 'code128', 66, false);
        $qrcode = $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2);

        $return_sales = $inv->return_id ? $this->pos_model->getAllReturnInvoiceByID($sale_id) : null;
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
            foreach ($return_sales as $Vals) {
                $product_discount += $Vals['product_discount'];
                $product_tax += $Vals['product_tax'];
                $total += $Vals['total'];
                $rounding += $Vals['rounding'];
                $grand_total += $Vals['grand_total'];
                $order_discount += $Vals['order_discount'];
                $order_tax += $Vals['order_tax'];
                $paid += $Vals['paid'];
                $ArrReturnId[] = $Vals['id'];
            }
            $return_sale = (object) array(
                'product_discount' => $product_discount,
                'product_tax'      => $product_tax,
                'total'            => $total,
                'rounding'         => $rounding,
                'grand_total'      => $grand_total,
                'order_discount'   => $order_discount,
                'order_tax'        => $order_tax,
                'paid'             => $paid,
            );
            $ReturnIds = "'" . implode("','", $ArrReturnId) . "'";
        } else {
            $return_sale = null;
        }

        $return_rows = $inv->return_id ? $this->pos_model->getAllReturnInvoiceItems($sale_id) : null;
        if (!empty($ArrReturnId)) {
            $return_payments = $return_sale ? $this->pos_model->getInvoicePayments1($ReturnIds) : null;
        } else {
            $return_payments = null;
        }

        if ($inv->order_no) {
            $shipping_details = $this->pos_model->getShipingDetails($inv->order_no);
        } elseif ($inv->shipping_address_id || $inv->billing_address_id) {
            $shipping_details_raw = $this->orders_model->getShipingAdress($inv->shipping_address_id);
            $billing_details_raw = $this->orders_model->getShipingAdress($inv->billing_address_id);
            $shipping_details = (object) array(
                'shipping_name'  => isset($shipping_details_raw->address_name) ? $shipping_details_raw->address_name : '',
                'shipping_phone' => isset($shipping_details_raw->phone) ? $shipping_details_raw->phone : '',
                'shipping_email' => isset($shipping_details_raw->email_id) ? $shipping_details_raw->email_id : '',
                'shipping_addr'  => isset($shipping_details_raw) ? trim($shipping_details_raw->line1 . ' ' . $shipping_details_raw->line2 . ' ' . $shipping_details_raw->city . ' ' . $shipping_details_raw->state . ' ' . $shipping_details_raw->country . ' ' . $shipping_details_raw->postal_code) : '',
                'billing_name'   => isset($billing_details_raw->address_name) ? $billing_details_raw->address_name : '',
                'billing_phone'  => isset($billing_details_raw->phone) ? $billing_details_raw->phone : '',
                'billing_email'  => isset($billing_details_raw->email_id) ? $billing_details_raw->email_id : '',
                'billing_addr'   => isset($billing_details_raw) ? trim($billing_details_raw->line1 . ' ' . $billing_details_raw->line2 . ' ' . $billing_details_raw->city . ' ' . $billing_details_raw->state . ' ' . $billing_details_raw->country . ' ' . $billing_details_raw->postal_code) : '',
            );
        } else {
            $shipping_details = null;
        }

        $dueAmount = $this->pos_model->partialAmount($inv->customer_id);
        $dueAmounts = null;
        if (!empty($dueAmount)) {
            foreach ($dueAmount as $totalDue) {
                $dueAmounts = $totalDue;
            }
        }

        if (!empty($rows)) {
            foreach ($rows as $key => $row) {
                if (isset($row->product_id)) {
                    $product = $this->pos_model->getProductByID($row->product_id, 'image');
                    $rows[$key]->image = $product ? $product->image : null;
                    $rows[$key]->cf1 = $product ? $product->image : null;
                }
                if (isset($row->quantity)) {
                    $rows[$key]->quantity = round($row->quantity, 2);
                }
                if (isset($row->unit_quantity)) {
                    $rows[$key]->quantity = round($row->unit_quantity, 2);
                }
            }
        }

        if (!empty($return_rows)) {
            foreach ($return_rows as $key => $row) {
                if (isset($row->product_id)) {
                    $product = $this->pos_model->getProductByID($row->product_id, 'image');
                    $return_rows[$key]->cf1 = $product ? $product->image : null;
                }
            }
        }

        if ((isset($_SESSION['print']) && $sale_id != $_SESSION['print']) && (isset($_SESSION['print_type']) && $_SESSION['print_type'] == null)) {
            $row_taxes_print = isset($inv->rows_tax) ? $inv->rows_tax : array();
            unset($inv->rows_tax);
            $row_taxes_print_arr = array();
            if (!empty($row_taxes_print)) {
                foreach ($row_taxes_print as $_data) {
                    foreach ($_data as $value1) {
                        $row_taxes_print_arr[] = $value1;
                    }
                }
            }
            $inv->rows_tax = $row_taxes_print_arr;
        }
        $_SESSION['print'] = $sale_id;

        $this->data['rows'] = $rows;
        $this->data['biller'] = $biller;
        $this->data['customer'] = $customer;
        $this->data['payments'] = $payments;
        $this->data['pos'] = $pos_settings;
        $this->data['barcode'] = $barcode;
        $arr = is_string($brcode) ? explode("'", $brcode) : array('', '');
        $qrr = is_string($qrcode) ? explode("'", $qrcode) : array('', '');
        $this->data['brcode'] = isset($arr[1]) ? $arr[1] : '';
        $this->data['qrcode'] = isset($qrr[1]) ? $qrr[1] : '';
        $this->data['return_sale'] = $return_sale;
        $this->data['return_rows'] = $return_rows;
        $this->data['return_payments'] = $return_payments;
        $this->data['inv'] = $inv;
        $this->data['shipping_details'] = $shipping_details;
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['page_title'] = $this->lang->line('invoice');
        $this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($inv->id, $inv->return_id);
        $this->data['salestax'] = $this->sales_model->getSalesItemsTaxes($sale_id);
        $this->data['dueAmount'] = $dueAmount;
        $this->data['dueAmounts'] = $dueAmounts;
        $this->data['sms_limit'] = $this->sma->BalanceSMS();
        $this->data['show_kot'] = (isset($this->Settings->pos_type) && $this->Settings->pos_type == 'restaurant');
        $this->data['back_url'] = site_url('Restaurant_Order_Taking');

        $this->load->view($this->theme . 'restaurant/pos_invoice', $this->data);
    }

    // Update order item quantity and line amount
    public function update_item()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $item_id = (int)$this->input->post('item_id');
        if (!$item_id) { echo json_encode(['status' => 'error', 'message' => 'item_id required']); return; }

        // Accept decimal quantity per Settings->qty_decimals
        $quantity = $this->sma->formatDecimal($this->input->post('quantity'), isset($this->Settings->qty_decimals) ? (int)$this->Settings->qty_decimals : null);
        if ($quantity <= 0) { echo json_encode(['status' => 'error', 'message' => 'Invalid quantity']); return; }

        // Optional editable fields
        $spice_level = $this->input->post('spice_level');
        $meat_wellness = $this->input->post('meat_wellness');
        $allergies = $this->input->post('allergies'); // array or CSV
        $custom_allergies = $this->input->post('custom_allergies'); // pipe-delimited or array
        $add_ons = $this->input->post('add_ons'); // array or CSV
        $toppings = $this->input->post('toppings'); // array or CSV
        $onion_flag = $this->input->post('onion_flag');
        $garlic_flag = $this->input->post('garlic_flag');
        $special_instructions = $this->input->post('special_instructions');

        if (is_string($allergies) && strlen($allergies)) { $allergies = explode(',', $allergies); }
        if (!is_array($allergies)) { $allergies = []; }
        if (is_string($add_ons) && strlen($add_ons)) { $add_ons = explode(',', $add_ons); }
        if (!is_array($add_ons)) { $add_ons = []; }
        if (is_string($toppings) && strlen($toppings)) { $toppings = explode(',', $toppings); }
        if (!is_array($toppings)) { $toppings = []; }

        // Fetch current item to get product_id for price recalculation
        $qi = $this->db->select('sma_product_id')->from('sma_res_orders_items')->where('id', $item_id)->get();
        if (!$qi->num_rows()) { echo json_encode(['status' => 'error', 'message' => 'Item not found']); return; }
        $product_id = (int)$qi->row()->sma_product_id;

        // Recalculate unit price including add-ons/toppings
        $product = $this->Restaurant_Order_Taking_model->get_product_details($product_id);
        if (!$product) { $product = $this->Restaurant_Order_Taking_model->get_product_basic_details($product_id); }
        $base_price = isset($product->price) ? (float)$product->price : 0.0;
        $add_on_total = $this->Restaurant_Order_Taking_model->sum_add_on_prices($add_ons);
        $topping_total = $this->Restaurant_Order_Taking_model->sum_topping_prices($toppings);
        $unit_price = $base_price + (float)$add_on_total + (float)$topping_total;
        $amount = $unit_price * $quantity;

        $data = [
            'quantity' => $quantity,
            'price' => $unit_price,
            'mrp' => $unit_price,
            'amount' => $amount,
            'spice_level' => $spice_level,
            'sma_res_meat_wellness_id' => $meat_wellness,
            'sma_res_common_allergies_list' => $allergies ? implode(',', $allergies) : null,
            'on_add_on_id' => !empty($add_ons) ? implode(',', (array)$add_ons) : null,
            'on_toppings_id' => !empty($toppings) ? implode(',', (array)$toppings) : null,
            'onion_flag' => $onion_flag,
            'garlic_flag' => $garlic_flag,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if (!empty($special_instructions)) { $data['special_instructions'] = trim($special_instructions); }
        // Persist custom allergies into dedicated column if available; else append to instructions
        if (!empty($custom_allergies)) {
            if ($this->db->field_exists('custom_allergies_text', 'sma_res_orders_items')) {
                $data['custom_allergies_text'] = is_array($custom_allergies) ? implode('|', $custom_allergies) : trim($custom_allergies);
            } elseif ($this->db->field_exists('special_instructions', 'sma_res_orders_items')) {
                $appendText = is_array($custom_allergies) ? implode(', ', $custom_allergies) : trim($custom_allergies);
                $br = '[Allergies: ' . $appendText . ']';
                $data['special_instructions'] = isset($data['special_instructions']) && $data['special_instructions']
                    ? trim($data['special_instructions'] . ' ' . $br)
                    : $br;
            }
        }

        $ok = $this->Restaurant_Order_Taking_model->update_order_item($item_id, $data);
        if ($ok) { echo json_encode(['status' => 'success']); }
        else { echo json_encode(['status' => 'error', 'message' => 'Failed to update item']); }
    }

    // Delete an order item
    public function delete_item()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $item_id = (int)$this->input->post('item_id');
        if (!$item_id) { echo json_encode(['status' => 'error', 'message' => 'item_id required']); return; }

        $ok = $this->Restaurant_Order_Taking_model->delete_order_item($item_id);
        if ($ok) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete item']);
        }
    }

    // Close table: mark order completed and free the table
    public function close_table()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $table_id = (int)$this->input->post('table_id');
        $order_id = (int)$this->input->post('order_id');
        if (!$table_id || !$order_id) {
            echo json_encode(['status' => 'error', 'message' => 'table_id and order_id required']);
            return;
        }

        // Mark order completed (payment status unchanged)
        $this->Restaurant_Order_Taking_model->update_order($order_id, [
            'status' => 'Completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        // Free table
        $this->Restaurant_Order_Taking_model->set_table_status_by_value($table_id, 'Available');

        echo json_encode(['status' => 'success']);
    }

    // Generic endpoint to set table status by id or value
    public function set_table_status()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $table_id = (int)$this->input->post('table_id');
        $status = $this->input->post('status'); // id or value
        if (!$table_id || $status === null || $status === '') {
            echo json_encode(['status' => 'error', 'message' => 'table_id and status required']);
            return;
        }

        $ok = $this->Restaurant_Order_Taking_model->set_table_status_by_value($table_id, $status);
        if ($ok) { echo json_encode(['status' => 'success']); }
        else { echo json_encode(['status' => 'error', 'message' => 'Failed to update status']); }
    }

    // Convenience: mark table Occupied (e.g., when opening a table or creating an order)
    public function mark_occupied()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $table_id = (int)$this->input->post('table_id');
        if (!$table_id) { echo json_encode(['status' => 'error', 'message' => 'table_id required']); return; }
        $ok = $this->Restaurant_Order_Taking_model->set_table_status_by_value($table_id, 'Occupied');
        echo json_encode($ok ? ['status' => 'success'] : ['status' => 'error', 'message' => 'Failed to update status']);
    }

    // Convenience: mark table Free/Available (without touching order)
    public function mark_free()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $table_id = (int)$this->input->post('table_id');
        if (!$table_id) { echo json_encode(['status' => 'error', 'message' => 'table_id required']); return; }
        $ok = $this->Restaurant_Order_Taking_model->set_table_status_by_value($table_id, 'Available');
        echo json_encode($ok ? ['status' => 'success'] : ['status' => 'error', 'message' => 'Failed to update status']);
    }

    // Link order event to table: Order Placed
    public function mark_order_placed()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $order_id = (int)$this->input->post('order_id');
        if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }
        $order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
        if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }
        if (!empty($order->res_tables_id)) {
            $this->Restaurant_Order_Taking_model->set_table_status_by_value($order->res_tables_id, 'Order Placed');
        }
        $this->Restaurant_Order_Taking_model->update_order($order_id, ['status' => 'Placed']);
        echo json_encode(['status' => 'success']);
    }

    // Link order event to table: Ready
    public function mark_ready()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $order_id = (int)$this->input->post('order_id');
        if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }
        
        $order = $this->Restaurant_Order_Taking_model->get_order_by_id($order_id);
        if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }
        
        $table_id = isset($order->table_id) ? $order->table_id : (isset($order->res_tables_id) ? $order->res_tables_id : null);
        
        if (!empty($table_id)) {
            $status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Ready');
            if (!$status_id) { $status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Order Ready'); }
            if (!$status_id) { $status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Prepared'); }
            
            if ($status_id) {
                $this->Restaurant_Order_Taking_model->update_table_status($table_id, $status_id);
            } else {
                // Fallback (User commonly overrides these manually for missing statuses)
                $this->Restaurant_Order_Taking_model->update_table_status($table_id, 8);
            }
        }
        $this->Restaurant_Order_Taking_model->update_order($order_id, ['status' => 'Ready']);
        echo json_encode(['status' => 'success']);
    }

    // Link order event to table: Served
    public function mark_served()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $order_id = (int)$this->input->post('order_id');
        if (!$order_id) { echo json_encode(['status' => 'error', 'message' => 'order_id required']); return; }
        $order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
        if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }
        if (!empty($order->res_tables_id)) {
            $this->Restaurant_Order_Taking_model->set_table_status_by_value($order->res_tables_id, 'Served');
        }
        $this->Restaurant_Order_Taking_model->update_order($order_id, ['status' => 'Served']);
        echo json_encode(['status' => 'success']);
    }

    public function update_kot_status()
    {
        if ($this->input->is_ajax_request()) {
            $order_id = $this->input->post('order_id');
            if ($order_id) {
                $order = $this->Restaurant_Order_Taking_model->get_order_by_id($order_id);
                if ($order && isset($order->table_id)) {
                    // Update table status to Order Placed (Pink)
                    $status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Order Placed');
                    if (!$status_id) { $status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Placed'); }
                    if (!$status_id) { $status_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Kitchen'); }
                    
                    if ($status_id) {
                        $this->Restaurant_Order_Taking_model->update_table_status($order->table_id, $status_id);
                    } else {
                        // Fallback to Occupied if 'Order Placed' status is missing
                        $this->Restaurant_Order_Taking_model->update_table_status($order->table_id, 7);
                    }
                    
                    // Update order status so it matches
                    $this->Restaurant_Order_Taking_model->update_order($order_id, ['status' => 'Order Placed']);
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Order or table not found']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        }
    }

    public function table_section($table_id = null, $auto_ready_order = null)
    {
        $this->data['page_title'] = 'Restaurant Tables';
        $this->data['sections'] = $this->Restaurant_Order_Taking_model->get_sections();
        
        if (!$auto_ready_order) {
            $auto_ready_order = $this->input->get('auto_ready_order');
        }
        if ($auto_ready_order) {
            $this->data['auto_ready_order'] = $auto_ready_order;
        }

        // Pass optional updated table id for HIGHLIGHT bonus (if view chooses to use it)
        $updated_table = $table_id ? $table_id : $this->input->get('updated_table');
        if ($updated_table) {
            $this->data['updated_table'] = $updated_table;
            
            // Get table details to select the correct section in the view
            $table = $this->Restaurant_Order_Taking_model->get_table($updated_table);
            if ($table) {
                $this->data['section_id'] = $table->section_id;
                $this->data['subsection_id'] = $table->subsection_id;
                if (!empty($table->section_id)) {
                    $this->data['section'] = $this->Restaurant_Order_Taking_model->get_section($table->section_id);
                }
            }
        }

        // Load all tables with their current status
        $this->data['tables'] = $this->Restaurant_Order_Taking_model->get_tables(
            isset($this->data['section_id']) ? $this->data['section_id'] : null,
            isset($this->data['subsection_id']) ? $this->data['subsection_id'] : null
        );
        $this->load->view($this->theme . 'restaurant/tables', $this->data);
    }

    public function table($table_id = null, $auto_ready_order = null)
    {
        // User requested URL format /table/47 for the bonus feature
        return $this->table_section($table_id, $auto_ready_order);
    }

    public function debug_statuses()
    {
        $statuses = $this->Restaurant_Order_Taking_model->get_table_statuses();
        echo json_encode($statuses);
    }
    
    public function free_table()
    {
        $table_id = $this->input->post('table_id');
        if ($table_id) {
            $available_id = $this->Restaurant_Order_Taking_model->get_table_status_id_by_value('Available');
            
            // 1. Reset Table Status to Available and Clear any Reservation Fields
            $this->Restaurant_Order_Taking_model->update_table_reservation($table_id, $available_id, null, null, null, true);
            
            // 2. Clear Table's Active Order (if any)
            $open_order = $this->Restaurant_Order_Taking_model->get_open_order_for_table($table_id);
            if ($open_order) {
                $this->Restaurant_Order_Taking_model->update_order($open_order->id, [
                    'status' => 'Completed'
                ]);
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Table ID required']);
        exit;
    }
}
