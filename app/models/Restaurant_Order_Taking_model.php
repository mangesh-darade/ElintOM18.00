<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Restaurant Order Taking Model
 * 
 * Purpose: Handles all database operations for restaurant order taking system
 * Usage: Loaded by Restaurant_Order_Taking controller for data operations
 */
class Restaurant_Order_Taking_model extends CI_Model
{
    /**
     * Constructor - Initialize the model
     * 
     * Purpose: Set up model configuration and database connection
     * Usage: Automatically called when model is loaded
     */
    public function __construct()
    {
        parent::__construct();
    }

    // ==================== SECTION MANAGEMENT ====================

    // Get all active restaurant sections for navigation
    public function get_sections()
    {
        $this->db->select('*');
        $this->db->from('sma_res_sections');
        $this->db->where('is_active', 1);
        $this->db->order_by('name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    // Get specific section details by ID
    public function get_section($section_id)
    {
        $this->db->select('*');
        $this->db->from('sma_res_sections');
        $this->db->where('id', $section_id);
        $this->db->where('is_active', 1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    // ==================== ALLERGY MANAGEMENT ====================

    // Create or find allergy in master list, returns ID
    public function ensure_allergy_exists($name)
    {
        $name = trim((string)$name);
        if ($name === '') { return false; }
        // Try to find existing by name (most MySQL collations are case-insensitive)
        $q = $this->db->select('id')
            ->from('sma_res_common_allergies')
            ->where('name', $name)
            ->limit(1)
            ->get();
        if ($q->num_rows()) {
            return (int)$q->row()->id;
        }
        // Build insert payload with only existing columns
        $data = ['name' => $name];
        if ($this->db->field_exists('is_active', 'sma_res_common_allergies')) {
            $data['is_active'] = 1;
        }
        if ($this->db->field_exists('created_at', 'sma_res_common_allergies')) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('updated_at', 'sma_res_common_allergies')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->insert('sma_res_common_allergies', $data)) {
            return (int)$this->db->insert_id();
        }
        return false;
    }

    // ==================== SUBSECTION MANAGEMENT ====================

    // Get all subsections for a specific section
    public function get_subsections($section_id)
    {
        $this->db->select('*');
        $this->db->from('sma_res_subsections');
        $this->db->where('section_id', $section_id);
        // Some databases don't have an is_active column for subsections
        if ($this->db->field_exists('is_active', 'sma_res_subsections')) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    // ==================== TABLE MANAGEMENT ====================

    // Get tables with section, subsection, status, and active order details
    public function get_tables($section_id = null, $subsection_id = null)
    {
        $this->db->select('t.*, s.name as section_name, ss.name as subsection_name, ts.value as status_name, ts.color as status_color,
            o.id as order_id, o.guest_count, o.status as order_status, o.created_at as order_time');
        $this->db->from('sma_res_tables t');
        $this->db->join('sma_res_sections s', 't.section_id = s.id', 'left');
        $this->db->join('sma_res_subsections ss', 't.subsection_id = ss.id', 'left');
        $this->db->join('sma_res_table_status ts', 't.status_id = ts.id', 'left');
        $this->db->join('sma_res_orders o', 't.id = o.res_tables_id AND o.status NOT IN ("Completed", "Cancelled")', 'left');
        $this->db->where('t.is_active', 1);
        
        if ($section_id) {
            $this->db->where('t.section_id', $section_id);
        }
        
        if ($subsection_id) {
            $this->db->where('t.subsection_id', $subsection_id);
        }
        
        $this->db->order_by('t.name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $tables = $query->result();
            
            // Enhance table data with additional order information
            foreach ($tables as $table) {
                if ($table->order_id) {
                    // Get order items count for additional status info
                    $items_count = $this->db->select('COUNT(*) as count')
                        ->from('sma_res_orders_items')
                        ->where('res_orders_id', $table->order_id)
                        ->get()
                        ->row();
                    
                    $table->items_count = $items_count ? (int)$items_count->count : 0;
                    $table->has_order = true;
                } else {
                    $table->items_count = 0;
                    $table->has_order = false;
                }
            }
            
            return $tables;
        }
        return [];
    }

    // Get only occupied tables for order management dropdown
    public function get_all_occupied_tables()
    {
        $occupied_id = $this->get_table_status_id_by_value('Occupied') ?: 2;
        $order_placed_id = $this->get_table_status_id_by_value('Order Placed');
        if (!$order_placed_id) { $order_placed_id = $this->get_table_status_id_by_value('Placed'); }
        if (!$order_placed_id) { $order_placed_id = $this->get_table_status_id_by_value('Kitchen'); }
        if (!$order_placed_id) { $order_placed_id = 7; } // Fallback configured by user

        $this->db->select('t.*, s.name as section_name, ss.name as subsection_name, ts.value as status_name, ts.color as status_color');
        $this->db->from('sma_res_tables t');
        $this->db->join('sma_res_sections s', 't.section_id = s.id', 'left');
        $this->db->join('sma_res_subsections ss', 't.subsection_id = ss.id', 'left');
        $this->db->join('sma_res_table_status ts', 't.status_id = ts.id', 'left');
        $this->db->where('t.is_active', 1);
        $this->db->where_in('t.status_id', array_unique([$occupied_id, $order_placed_id]));
        
        $this->db->order_by('t.name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_table($table_id)
    {
        $this->db->select('t.*, s.name as section_name, ss.name as subsection_name, ts.value as status_name');
        $this->db->from('sma_res_tables t');
        $this->db->join('sma_res_sections s', 't.section_id = s.id', 'left');
        $this->db->join('sma_res_subsections ss', 't.subsection_id = ss.id', 'left');
        $this->db->join('sma_res_table_status ts', 't.status_id = ts.id', 'left');
        $this->db->where('t.id', $table_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    public function update_table_status($table_id, $status_id)
    {
        $data = ['status_id' => $status_id, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->where('id', $table_id);
        return $this->db->update('sma_res_tables', $data);
    }

    // Update reservation fields if present in schema. Safe for DBs without these columns.
    public function update_table_reservation($table_id, $status_id, $reserved_until = null, $reserved_by = null, $reserved_note = null, $clear_reserved_fields = false)
    {
        $data = ['status_id' => $status_id, 'updated_at' => date('Y-m-d H:i:s')];

        if ($this->db->field_exists('reserved_until', 'sma_res_tables')) {
            if ($clear_reserved_fields) {
                $data['reserved_until'] = null;
            } elseif ($reserved_until !== null) {
                $data['reserved_until'] = $reserved_until;
            }
        }

        if ($this->db->field_exists('reserved_by', 'sma_res_tables')) {
            if ($clear_reserved_fields) {
                $data['reserved_by'] = null;
            } elseif ($reserved_by !== null) {
                $data['reserved_by'] = $reserved_by;
            }
        }

        if ($this->db->field_exists('reserved_note', 'sma_res_tables')) {
            if ($clear_reserved_fields) {
                $data['reserved_note'] = null;
            } elseif ($reserved_note !== null) {
                $data['reserved_note'] = $reserved_note;
            }
        }

        $this->db->where('id', $table_id);
        return $this->db->update('sma_res_tables', $data);
    }

    // Order Management
    public function create_order($data)
    {
        // Validate input to avoid DB errors and header warnings during tests
        if (empty($data) || !is_array($data)) {
            return false;
        }
        if ($this->db->insert('sma_res_orders', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_order($order_id)
    {
        $this->db->select('o.*, t.name as table_name');
        $this->db->from('sma_res_orders o');
        $this->db->join('sma_res_tables t', 'o.res_tables_id = t.id', 'left');
        $this->db->where('o.id', $order_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    public function get_order_minimal($order_id)
    {
        return $this->db->select('id, res_tables_id, status, payment_status')
            ->from('sma_res_orders')
            ->where('id', $order_id)
            ->get()->row();
    }

    // ==================== ORDER MANAGEMENT ====================

    /**
     * Get Active Order - Retrieve currently active order for a table
     */
    public function get_active_order($table_id)
    {
        $this->db->select('*');
        $this->db->from('sma_res_orders');
        $this->db->where(['res_tables_id' => $table_id, 'status' => 'Active']);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    /**
     * Get Open Order For Table - Find any open order for a table
     */
    public function get_open_order_for_table($table_id)
    {
        // Consider any non-completed/non-cancelled order as open
        $this->db->select('*');
        $this->db->from('sma_res_orders');
        $this->db->where('res_tables_id', $table_id);
        $this->db->where_not_in('status', ['Completed', 'Cancelled']);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $q = $this->db->get();
        return $q->num_rows() ? $q->row() : null;
    }

    /**
     * Update Order - Update order details
     
     */
    public function update_order($order_id, $data)
    {
        if (empty($order_id) || empty($data) || !is_array($data)) {
            return false;
        }
        $this->db->where('id', $order_id);
        $this->db->update('sma_res_orders', $data);
        return $this->db->affected_rows();
    }

    // ==================== GUEST MANAGEMENT ====================

    /**
     * Create Guest - Add a new guest to an order
     */
    public function create_guest($order_id)
    {
        $data = ['res_orders_id' => $order_id, 'created_at' => date('Y-m-d H:i:s')];
        if ($this->db->insert('sma_res_orders_guests', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    // Get all guests for an order
    public function get_order_guests($order_id)
    {
        $this->db->select('*');
        $this->db->from('sma_res_orders_guests');
        $this->db->where('res_orders_id', $order_id);
        $this->db->order_by('id');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    // Count items for a specific guest
    public function count_guest_items($guest_id)
    {
        return (int)$this->db->select('COUNT(*) AS cnt')
            ->from('sma_res_orders_items')
            ->where('res_orders_guests_id', $guest_id)
            ->get()->row()->cnt;
    }

    // Delete guest only if they have no items
    public function delete_guest_if_no_items($guest_id)
    {
        if ($this->count_guest_items($guest_id) > 0) {
            return false;
        }
        return $this->db->delete('sma_res_orders_guests', ['id' => $guest_id]);
    }

    // Get last guest that has no items assigned
    public function get_last_guest_without_items($order_id)
    {
        $this->db->select('g.*');
        $this->db->from('sma_res_orders_guests g');
        $this->db->where('g.res_orders_id', $order_id);
        $this->db->order_by('g.id', 'DESC');
        $guests = $this->db->get()->result();
        foreach ($guests as $g) {
            if ($this->count_guest_items($g->id) == 0) {
                return $g;
            }
        }
        return null;
    }

    // Menu Management
    public function get_menu_categories()
    {
        // Return only categories that have products with active restaurant details
        $this->db->select('c.id, c.name, COUNT(DISTINCT p.id) AS product_count');
        $this->db->from('sma_categories c');
        $this->db->join('sma_products p', 'p.category_id = c.id AND p.flag_visible = 1', 'inner');
        $this->db->join('sma_res_product_details pd', 'p.id = pd.product_id AND pd.is_active = 1', 'inner');
        $this->db->group_by('c.id');
        $this->db->order_by('c.name');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_menu_items($filters = [])
    {
        $this->db->select('p.*, COALESCE(pd.price, p.price) AS price, pd.meal_type_id AS meal_type_id, mt.name as meal_type_name, c.name as category_name');
        $this->db->from('sma_products p');
        $this->db->join('sma_res_product_details pd', 'p.id = pd.product_id', 'left');
        $this->db->join('sma_res_meal_type mt', 'pd.meal_type_id = mt.id', 'left');
        $this->db->join('sma_categories c', 'p.category_id = c.id', 'left');
        // Use flag_visible for product visibility (common in this codebase) and ensure product detail is active
        $this->db->where('pd.is_active', 1);
        $this->db->where('p.flag_visible', 1);

        // Category filter
        if (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', $filters['category_id']);
        }

        // Subcategory filter (if provided)
        if (!empty($filters['subcategory_id'])) {
            $this->db->where('p.subcategory_id', $filters['subcategory_id']);
        }

        // Meal type filter: supports CSV of IDs or a single ID/name, using pd.meal_type_id
        if (!empty($filters['meal_type'])) {
            $mealType = $filters['meal_type'];
            $ids = [];

            // Extract IDs
            if (is_numeric($mealType)) {
                $ids[] = (int)$mealType;
            } elseif (is_string($mealType) && strpos($mealType, ',') !== false) {
                 $ids = array_filter(array_map('trim', explode(',', $mealType)));
            } elseif (is_string($mealType) && is_numeric($mealType)) {
                 $ids[] = (int)$mealType;
            }

            if (!empty($ids)) {
                 $hasVeg = in_array(1, $ids) || in_array('1', $ids);
                 $hasNonVeg = in_array(2, $ids) || in_array('2', $ids);

                 if ($hasVeg && $hasNonVeg) {
                     // Show all - No filter applied
                 } elseif ($hasVeg) {
                     $this->db->where('pd.meal_type_id', 1);
                 } elseif ($hasNonVeg) {
                     // Show everything NOT Veg (including other IDs or NULL)
                     $this->db->group_start();
                         $this->db->where('pd.meal_type_id !=', 1);
                         $this->db->or_where('pd.meal_type_id IS NULL');
                     $this->db->group_end();
                 } else {
                     $this->db->where_in('pd.meal_type_id', $ids);
                 }
            } else {
                // Fallback: match by meal type name
                $this->db->where('mt.name', $mealType);
            }
        }

        // Search filter: match product name or code
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('p.name', $filters['search'])
                ->or_like('p.code', $filters['search'])
            ->group_end();
        }

        $this->db->order_by('p.name');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_menu_items_grouped($filters = [])
    {
        $this->db->select('c.id AS category_id, c.name AS category_name, p.id, p.name, p.code, p.image, COALESCE(pd.price, p.price) AS price, pd.meal_type_id AS meal_type_id, mt.name AS meal_type_name');
        $this->db->from('sma_products p');
        $this->db->join('sma_categories c', 'p.category_id = c.id', 'left');
        $this->db->join('sma_res_product_details pd', 'p.id = pd.product_id', 'left');
        $this->db->join('sma_res_meal_type mt', 'pd.meal_type_id = mt.id', 'left');
        $this->db->where('p.flag_visible', 1);
        $this->db->where('pd.is_active', 1);

        // Category filter (was missing; needed when user clicks a category chip)
        if (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', $filters['category_id']);
        }

        // Subcategory filter
        if (!empty($filters['subcategory_id'])) {
            $this->db->where('p.subcategory_id', $filters['subcategory_id']);
        }

        // Meal type filter: IDs (CSV) or name, using pd.meal_type_id
        if (!empty($filters['meal_type'])) {
            $mealType = $filters['meal_type'];
            $ids = [];

            if (is_numeric($mealType)) {
                $ids[] = (int)$mealType;
            } elseif (is_string($mealType) && strpos($mealType, ',') !== false) {
                 $ids = array_filter(array_map('trim', explode(',', $mealType)));
            } elseif (is_string($mealType) && is_numeric($mealType)) {
                 $ids[] = (int)$mealType;
            }

            if (!empty($ids)) {
                 $hasVeg = in_array(1, $ids) || in_array('1', $ids);
                 $hasNonVeg = in_array(2, $ids) || in_array('2', $ids);

                 if ($hasVeg && $hasNonVeg) {
                     // Show all
                 } elseif ($hasVeg) {
                     $this->db->where('pd.meal_type_id', 1);
                 } elseif ($hasNonVeg) {
                     $this->db->group_start();
                         $this->db->where('pd.meal_type_id !=', 1);
                         $this->db->or_where('pd.meal_type_id IS NULL');
                     $this->db->group_end();
                 } else {
                     $this->db->where_in('pd.meal_type_id', $ids);
                 }
            } else {
                $this->db->where('mt.name', $mealType);
            }
        }

        // Search
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('p.name', $filters['search'])
                ->or_like('p.code', $filters['search'])
            ->group_end();
        }

        $this->db->order_by('c.name ASC, p.name ASC');
        $query = $this->db->get();

        $grouped = [];
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                if (!$row->category_id) { continue; }
                if (!isset($grouped[$row->category_id])) {
                    $grouped[$row->category_id] = [
                        'category_id' => $row->category_id,
                        'category_name' => $row->category_name,
                        'items' => []
                    ];
                }
                $grouped[$row->category_id]['items'][] = $row;
            }
        }
        return array_values($grouped);
    }

    public function get_product_details($product_id)
    {
        $this->db->select('pd.*, mt.name as meal_type_name');
        $this->db->from('sma_res_product_details pd');
        $this->db->join('sma_res_meal_type mt', 'pd.meal_type_id = mt.id', 'left');
        $this->db->where('pd.product_id', $product_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    public function get_product_basic_details($product_id)
    {
        // Fallback to products table when restaurant-specific detail row is absent
        $this->db->select('id as product_id, name, code, price');
        $this->db->from('sma_products');
        $this->db->where('id', $product_id);
        $this->db->where('flag_visible', 1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    // Master Data
    public function get_meal_types()
    {
        $this->db->select('*');
        $this->db->from('sma_res_meal_type');
        $this->db->where('is_active', 1);
        $this->db->order_by('name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_allergies()
    {
        $this->db->select('*');
        $this->db->from('sma_res_common_allergies');
        // Some databases store boolean flags as strings like 'Y', 'Yes', or '1'. Include those.
        $this->db->group_start()
            ->where('is_active', 1)
            ->or_where('is_active', '1')
            ->or_where('is_active', 'Y')
            ->or_where('is_active', 'y')
            ->or_where('is_active', 'Yes')
            ->or_where('is_active', 'yes')
        ->group_end();
        $this->db->order_by('name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_meat_wellness()
    {
        $this->db->select('*');
        $this->db->from('sma_res_meat_wellness');
        $this->db->where('is_active', 1);
        $this->db->order_by('type');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_add_ons()
    {
        $this->db->select('*');
        $this->db->from('sma_res_add_ons');
        $this->db->where('is_active', 1);
        $this->db->order_by('name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_toppings()
    {
        $this->db->select('*');
        $this->db->from('sma_res_toppings');
        $this->db->where('is_active', 1);
        $this->db->order_by('name');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    public function get_product_add_ons($product_id)
    {
        // Mapping via sma_res_product_add_ons has been deprecated.
        // Return global active add-ons from master table.
        return $this->get_add_ons();
    }

    public function get_product_toppings($product_id)
    {
        // Mapping via sma_res_product_toppings has been deprecated.
        // Return global active toppings from master table.
        return $this->get_toppings();
    }

    public function sum_add_on_prices($add_on_ids)
    {
        if (empty($add_on_ids)) { return 0.0; }
        if (!is_array($add_on_ids)) { $add_on_ids = [$add_on_ids]; }
        $this->db->select('COALESCE(SUM(price),0) AS total');
        $this->db->from('sma_res_add_ons');
        $this->db->where_in('id', $add_on_ids);
        $this->db->where('is_active', 1);
        $q = $this->db->get();
        return $q->num_rows() ? (float)$q->row()->total : 0.0;
    }

    public function sum_topping_prices($topping_ids)
    {
        if (empty($topping_ids)) { return 0.0; }
        if (!is_array($topping_ids)) { $topping_ids = [$topping_ids]; }
        $this->db->select('COALESCE(SUM(price),0) AS total');
        $this->db->from('sma_res_toppings');
        $this->db->where_in('id', $topping_ids);
        $this->db->where('is_active', 1);
        $q = $this->db->get();
        return $q->num_rows() ? (float)$q->row()->total : 0.0;
    }

    // Order Items Management
    public function add_order_item($data)
    {
        if ($this->db->insert('sma_res_orders_items', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_order_items($order_id)
    {
        $this->db->select("oi.*, p.name as product_name, p.image, g.id as guest_number, mt.name as meal_type_name, mw.type as meat_wellness_type,
            (
                SELECT COUNT(*) FROM sma_res_orders_guests g2
                WHERE g2.res_orders_id = g.res_orders_id AND g2.id <= g.id
            ) AS guest_display_number");
        $this->db->from('sma_res_orders_items oi');
        $this->db->join('sma_products p', 'oi.sma_product_id = p.id', 'left');
        $this->db->join('sma_res_orders_guests g', 'oi.res_orders_guests_id = g.id', 'left');
        $this->db->join('sma_res_meal_type mt', 'oi.meal_type_id = mt.id', 'left');
        $this->db->join('sma_res_meat_wellness mw', 'oi.sma_res_meat_wellness_id = mw.id', 'left');
        $this->db->where('oi.res_orders_id', $order_id);
        $this->db->order_by('oi.created_at');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return [];
    }

    /**
     * Summarise monetary totals for a restaurant order.
     *
     * @param int $order_id
     * @return array{subtotal:float,sgst:float,cgst:float,grand_total:float,item_count:int,quantity:float}
     */
    public function get_order_totals($order_id)
    {
        $totals = $this->db->select('COALESCE(SUM(amount), 0) AS subtotal, COALESCE(SUM(quantity), 0) AS quantity, COUNT(*) AS item_count')
            ->from('sma_res_orders_items')
            ->where('res_orders_id', $order_id)
            ->get()
            ->row();

        $subtotal = $totals ? (float) $totals->subtotal : 0.0;
        $quantity = $totals ? (float) $totals->quantity : 0.0;
        $item_count = $totals ? (int) $totals->item_count : 0;

        $order = $this->get_order($order_id);
        $sgst = 0.0;
        $cgst = 0.0;

        if ($order) {
            if (isset($order->sgst_value) && $order->sgst_value !== null) {
                $sgst = (float) $order->sgst_value;
            } elseif (isset($order->sgst_percent) && $order->sgst_percent !== null) {
                $sgst = $subtotal * ((float) $order->sgst_percent / 100);
            }

            if (isset($order->cgst_value) && $order->cgst_value !== null) {
                $cgst = (float) $order->cgst_value;
            } elseif (isset($order->cgst_percent) && $order->cgst_percent !== null) {
                $cgst = $subtotal * ((float) $order->cgst_percent / 100);
            }
        }

        $grand_total = $subtotal + $sgst + $cgst;

        return [
            'subtotal'    => round($subtotal, 2),
            'sgst'        => round($sgst, 2),
            'cgst'        => round($cgst, 2),
            'grand_total' => round($grand_total, 2),
            'item_count'  => $item_count,
            'quantity'    => round($quantity, 2),
        ];
    }

    public function update_order_item($item_id, $data)
    {
        $this->db->where('id', $item_id);
        return $this->db->update('sma_res_orders_items', $data);
    }

    public function delete_order_item($item_id)
    {
        $this->db->where('id', $item_id);
        return $this->db->delete('sma_res_orders_items');
    }

    public function update_order_items_status($order_id, $status)
    {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->where('res_orders_id', $order_id);
        return $this->db->update('sma_res_orders_items', $data);
    }

    // Table Status Master
    public function get_table_statuses()
    {
        // Minimal fields: id and value (display label)
        $this->db->select('id, value, color');
        $this->db->from('sma_res_table_status');
        $this->db->order_by('id');
        $q = $this->db->get();
        return $q->num_rows() ? $q->result() : [];
    }

    /**
     * Get status ID by human-readable value (case-insensitive)
     */
    public function get_table_status_id_by_value($value)
    {
        if ($value === null || $value === '') { return null; }
        $value_lc = strtolower(trim((string)$value));
        $rows = $this->get_table_statuses();
        foreach ($rows as $r) {
            if (strtolower((string)$r->value) === $value_lc) { return (int)$r->id; }
        }
        return null;
    }

    /**
     * Update table status by value (e.g., 'Available', 'Occupied', ...)
     */
    public function set_table_status_by_value($table_id, $value)
    {
        $id = is_numeric($value) ? (int)$value : $this->get_table_status_id_by_value($value);
        if (!$id) { return false; }
        return $this->update_table_status($table_id, $id);
    }

    /**
     * Reserve a table
     * @param int $table_id
     * @param string $reserved_until
     * @param string $reserved_by
     * @param string $reserved_note
     * @return bool
     */
    public function reserve_table($table_id, $reserved_until, $reserved_by, $reserved_note = '')
    {
        $reserved_status_id = $this->get_table_status_id_by_value('Reserved');
        if (!$reserved_status_id) {
            log_message('error', 'Reserved status not found in sma_res_table_status table.');
            return false;
        }

        return $this->update_table_reservation($table_id, $reserved_status_id, $reserved_until, $reserved_by, $reserved_note);
    }
    
    /**
     * Unreserve a table
     * @param int $table_id
     * @return bool
     */
    public function unreserve_table($table_id)
    {
        $available_status_id = $this->get_table_status_id_by_value('Available');
        if (!$available_status_id) {
            log_message('error', 'Available status not found in sma_res_table_status table.');
            return false;
        }

        return $this->update_table_reservation($table_id, $available_status_id, null, null, null, true);
    }
    // ===============================
// Get items grouped by guest
// ===============================
public function get_order_items_grouped_by_guest($order_id)
{
    $items = $this->db
        ->select('
            oi.*,
            p.name AS product_name,
            g.id AS guest_id
        ')
        ->from('sma_res_orders_items oi')
        ->join('sma_products p', 'p.id = oi.sma_product_id', 'left')
        ->join('sma_res_orders_guests g', 'g.id = oi.res_orders_guests_id', 'left')
        ->where('oi.res_orders_id', $order_id)
        ->order_by('g.id', 'ASC')
        ->order_by('oi.id', 'ASC')
        ->get()
        ->result();

    $grouped = [];
   
    foreach ($items as $item) {
        $grouped[$item->guest_id][] = $item;
    }

    return $grouped;
}

    // ===============================
    // Get Order By ID for KOT functionality
    // ===============================
    public function get_order_by_id($order_id)
    {
        // As per requirements: "Fetch from OrderTransaction table"
        if ($this->db->table_exists('OrderTransaction')) {
            $query = $this->db->get_where('OrderTransaction', ['id' => $order_id]);
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
        }
        
        // Fallback to CodeIgniter's sma_res_orders using table_id alias
        $this->db->select('*, res_tables_id as table_id');
        $query = $this->db->get_where('sma_res_orders', ['id' => $order_id]);
        if ($query && $query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

}
