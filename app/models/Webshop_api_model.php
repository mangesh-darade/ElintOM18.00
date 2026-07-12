<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webshop_api_model — all DB queries backing the Webshop_api controller.
 *
 * Operates on ElintOm's sma_* tables.
 * Called exclusively by Webshop_api.php controller.
 */
class Webshop_api_model extends CI_Model {

    /** @var Webshop_model */
    public $webshop_model;

    /** @var Webshop_settings_model */
    public $webshop_settings_model;

    /** @var Site */
    public $site;

    /** @var Eshop_model */
    public $eshop_model;

    /** @var Whatsapp_model */
    public $Whatsapp_model;

    public function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->load->model('webshop_model');
        $this->webshop_model = $CI->webshop_model;
        $this->load->helper('order_pricing');
    }

    /* ================================================================
     * STORE SETTINGS
     * ================================================================ */

    public function get_settings() {
        static $request_cache = null;
        if ($request_cache !== null) {
            return $request_cache;
        }

        $cache_file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'webshop_api_getsettings.cache.php';
        $cache_ttl  = 60;
        if (is_file($cache_file) && (time() - (int) @filemtime($cache_file)) < $cache_ttl) {
            $cached = @include $cache_file;
            if (is_array($cached) && isset($cached['status'])) {
                $request_cache = $cached;
                return $request_cache;
            }
        }

        try {
            $ws  = $this->webshop_model->get_webshop_settings();
            $gw  = $this->webshop_model->get_payment_gatways();

            // POS + eshop settings (use unprefixed names; dbprefix adds sma_)
            // Never chain ->row_array() on get(): if the query fails, get() returns false and PHP fatals (HTTP 500 + empty body).
            $qPos = $this->db->get('settings');
            $posSet = ($qPos && is_object($qPos)) ? $qPos->row_array() : [];
            $qConf = $this->db->get('pos_settings');
            $posConf = ($qConf && is_object($qConf)) ? $qConf->row_array() : [];

            $this->load->model('webshop_settings_model');
            $this->webshop_settings_model = get_instance()->webshop_settings_model;
            $websiteRows = $this->webshop_settings_model->merge_website_setting_for_api(array());
            $websiteSections = $this->webshop_settings_model->get_website_setting_sections_for_api();
            $headerBuilder = $this->get_header_builder_config_for_api();
            $headerBuildersByProfile = $this->get_header_builder_configs_by_profile_for_api();
            $footerBuilder = $this->get_footer_builder_config_for_api();
            $footerBuildersByProfile = $this->get_footer_builder_configs_by_profile_for_api();
            $this->load->helper('cms_layout');
            $headerDefaultProfile = function_exists('cms_layout_builder_default_profile_slug')
                ? cms_layout_builder_default_profile_slug('header')
                : 'site';
            $footerDefaultProfile = function_exists('cms_layout_builder_default_profile_slug')
                ? cms_layout_builder_default_profile_slug('footer')
                : 'site';

            $request_cache = [
                'status'                    => 'SUCCESS',
                'webshop_settings'          => $ws  ? (array) $ws  : [],
                'payment_gateways'          => $gw  ? (array) $gw  : [],
                'pos_settings'              => $posSet  ?: [],
                'pos_config'                => $posConf ?: [],
                'website_setting'            => $websiteRows,
                'website_setting_sections'   => $websiteSections,
                'header_builder_config'      => $headerBuilder,
                'header_builder_configs_by_profile' => $headerBuildersByProfile,
                'footer_builder_config'      => $footerBuilder,
                'footer_builder_configs_by_profile' => $footerBuildersByProfile,
                'header_layout_default_profile' => $headerDefaultProfile,
                'footer_layout_default_profile' => $footerDefaultProfile,
                'storefront_logo'            => $this->webshop_settings_model->get_storefront_header_logo_status(),
            ];
            if (is_writable(dirname($cache_file))) {
                @file_put_contents(
                    $cache_file,
                    '<?php return ' . var_export($request_cache, true) . ';' . PHP_EOL,
                    LOCK_EX
                );
            }
            return $request_cache;
        } catch (\Throwable $e) {
            log_message('error', 'Webshop_api_model::get_settings — ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            // Serve last good cache when live assembly fails (PHP 8.x / schema drift) so storefront is not blank.
            if (is_file($cache_file)) {
                $stale = @include $cache_file;
                if (is_array($stale) && isset($stale['status']) && strtoupper((string) $stale['status']) === 'SUCCESS') {
                    $request_cache = $stale;
                    return $request_cache;
                }
            }
            return [
                'status'    => 'ERROR',
                'error_code'=> 500,
                'msg'       => 'Could not assemble settings (see ElintOm application logs).',
            ];
        }
    }

    /**
     * Layout builder JSON for webshop theme (promo, phone, colors).
     *
     * @return array<string,mixed>
     */
    public function get_header_builder_config_for_api() {
        $this->load->model('webshop_settings_model');
        if (!$this->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');
        if (!function_exists('cms_layout_builder_load_json_config')) {
            return array();
        }
        $cfg = cms_layout_builder_load_json_config('header', cms_layout_builder_default_profile_slug('header'));
        if (!is_array($cfg)) {
            return array();
        }
        return function_exists('cms_sanitize_header_builder_config')
            ? cms_sanitize_header_builder_config($cfg)
            : $cfg;
    }

    /**
     * Layout-builder header JSON for every profile (site, test2, …) for webshop themes.
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_header_builder_configs_by_profile_for_api() {
        $this->load->helper('cms_layout');
        if (!function_exists('cms_get_header_builder_configs_by_profile_for_api')) {
            return array();
        }
        return cms_get_header_builder_configs_by_profile_for_api();
    }

    /**
     * Layout builder JSON for default site footer profile.
     *
     * @return array<string,mixed>
     */
    public function get_footer_builder_config_for_api() {
        $this->load->model('webshop_settings_model');
        if (!$this->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');
        if (!function_exists('cms_layout_builder_load_json_config')) {
            return array();
        }
        $cfg = cms_layout_builder_load_json_config('footer', cms_layout_builder_default_profile_slug('footer'));
        if (!is_array($cfg)) {
            return array();
        }
        return function_exists('cms_sanitize_footer_builder_config')
            ? cms_sanitize_footer_builder_config($cfg)
            : $cfg;
    }

    /**
     * Layout-builder footer JSON for every profile (site, test2, …) for webshop themes.
     *
     * @return array<string,array<string,mixed>>
     */
    public function get_footer_builder_configs_by_profile_for_api() {
        $this->load->helper('cms_layout');
        if (!function_exists('cms_get_footer_builder_configs_by_profile_for_api')) {
            return array();
        }
        return cms_get_footer_builder_configs_by_profile_for_api();
    }

    /* ================================================================
     * CATALOGUE
     * ================================================================ */

    public function get_categories() {
        $categories = $this->webshop_model->get_categories();
        return [
            'status'     => 'SUCCESS',
            'categories' => $categories ?: ['main' => []],
        ];
    }

    public function get_sliders() {
        $sliders = $this->webshop_model->get_sliders();
        return [
            'status'  => 'SUCCESS',
            'sliders' => $sliders ?: [],
        ];
    }

    public function get_products_list(array $params = []) {
        $by     = isset($params['by'])       ? $params['by']       : null;
        $byid   = isset($params['byid'])     ? $params['byid']     : null;
        $hash   = isset($params['use_hash']) ? (bool) $params['use_hash'] : false;
        $limit  = isset($params['limit'])    ? (int)  $params['limit']    : 0;
        $page   = isset($params['page'])     ? max(1, (int) $params['page']) : 1;

        $result = $this->webshop_model->get_products_list($by, $byid, $hash, $limit, $page);
        if ($result) {
            return ['status' => 'SUCCESS', 'result' => $result];
        }
        return ['status' => 'ERROR', 'msg' => 'No products found'];
    }

    public function get_product_by_hash($hash) {
        $product = $this->webshop_model->get_product_by_hash($hash);
        if ($product) {
            return ['status' => 'SUCCESS', 'product' => $product];
        }
        return ['status' => 'ERROR', 'msg' => 'Product not found'];
    }

    public function get_entity_tags($entity_code, $entity_id) {
        $entity_code = strtolower(trim((string) $entity_code));
        $entity_id = (int) $entity_id;
        if ($entity_code === '' || $entity_id <= 0) {
            return ['status' => 'ERROR', 'msg' => 'Invalid entity selection'];
        }

        if (
            !$this->db->table_exists('sma_cms_entities_master') ||
            !$this->db->table_exists('sma_cms_entity_tag_mapping') ||
            !$this->db->table_exists('sma_cms_tags_master')
        ) {
            return ['status' => 'SUCCESS', 'entity_code' => $entity_code, 'entity_id' => $entity_id, 'rows' => []];
        }

        $entity_master = $this->db
            ->select('id, entity_code, entity_name')
            ->from('sma_cms_entities_master')
            ->where('entity_code', $entity_code)
            ->get()
            ->row_array();
        if ((!is_array($entity_master) || empty($entity_master['id']))
            && $this->db->field_exists('is_active', 'sma_cms_entities_master')) {
            $entity_master = $this->db
                ->select('id, entity_code, entity_name')
                ->from('sma_cms_entities_master')
                ->where('entity_code', $entity_code)
                ->group_start()
                ->where('is_active', 1)
                ->or_where('is_active IS NULL', null, false)
                ->group_end()
                ->get()
                ->row_array();
        }

        if (empty($entity_master) || empty($entity_master['id'])) {
            return ['status' => 'SUCCESS', 'entity_code' => $entity_code, 'entity_id' => $entity_id, 'rows' => []];
        }

        $rows = $this->db
            ->select('etm.tag_id, etm.property_name, etm.value, tm.tag_name, tm.category')
            ->from('sma_cms_entity_tag_mapping etm')
            ->join('sma_cms_tags_master tm', 'tm.id = etm.tag_id', 'left')
            ->where('etm.entity_master_id', (int) $entity_master['id'])
            ->where('etm.entity_id', $entity_id)
            ->order_by('tm.category', 'ASC')
            ->order_by('tm.tag_name', 'ASC')
            ->get()
            ->result_array();

        return [
            'status' => 'SUCCESS',
            'entity_code' => $entity_code,
            'entity_id' => $entity_id,
            'entity_master_id' => (int) $entity_master['id'],
            'rows' => $rows ?: [],
        ];
    }

    public function search_products($keyword, $category_id = null) {
        $items = $this->webshop_model->search_category_products($keyword, $category_id);
        $other = $this->webshop_model->search_other_products($keyword, $category_id);
        return [
            'status' => 'SUCCESS',
            'items'  => $items ?: [],
            'other'  => $other ?: [],
        ];
    }

    /* ================================================================
     * GEO
     * ================================================================ */

    public function get_states() {
        $states = $this->webshop_model->get_state();
        return ['status' => 'SUCCESS', 'states' => $states ?: []];
    }

    public function get_countries() {
        $countries = $this->webshop_model->getCountry();
        return ['status' => 'SUCCESS', 'countries' => $countries ?: []];
    }

    /* ================================================================
     * CUSTOMER
     * ================================================================ */

    public function get_customer(array $filter) {
        $customer = $this->webshop_model->get_customer($filter);
        if ($customer) {
            unset($customer['password'], $customer['pass_key']);
            return ['status' => 'SUCCESS', 'customer' => $customer];
        }
        return ['status' => 'NOT_FOUND', 'msg' => 'Customer not found'];
    }

    public function create_customer(array $d) {
        $data = [
            'group_id'            => '3',
            'group_name'          => 'customer',
            'customer_group_id'   => '1',
            'customer_group_name' => 'General',
            'price_group_id'      => '2',
            'price_group_name'    => 'Standered',
            'name'                => isset($d['name'])        ? $d['name']        : '',
            'phone'               => isset($d['phone'])       ? $d['phone']       : '',
            'email'               => isset($d['email'])       ? $d['email']       : null,
            'company'             => isset($d['company'])     ? $d['company']     : null,
            'address'             => isset($d['address'])     ? $d['address']     : null,
            'city'                => isset($d['city'])        ? $d['city']        : null,
            'state'               => isset($d['state'])       ? $d['state']       : null,
            'state_code'          => isset($d['state_code'])  ? $d['state_code']  : null,
            'postal_code'         => isset($d['postal_code']) ? $d['postal_code'] : null,
            'country'             => isset($d['country'])     ? $d['country']     : null,
            'password'            => !empty($d['password'])   ? md5($d['password']) : null,
        ];

        $customer = $this->webshop_model->add_customer($data);
        if ($customer) {
            unset($customer['password'], $customer['pass_key']);
            return ['status' => 'SUCCESS', 'customer' => $customer];
        }
        return ['status' => 'ERROR', 'msg' => 'Failed to create customer'];
    }

    public function login_customer($login, $password) {
        // Query companies table directly (CI3 dbprefix 'sma_' is added automatically → sma_companies).
        $pw = md5($password);
        $login = trim((string) $login);
        $q = $this->db
            ->group_start()
                ->where('phone', $login)
                ->or_where('email', $login)
            ->group_end()
            ->where('password', $pw)
            ->where('group_id', 3)
            ->get('companies');

        if ($q && $q->num_rows() > 0) {
            $customer = $q->row_array();
            unset($customer['password'], $customer['pass_key']);
            return ['status' => 'SUCCESS', 'customer' => $customer];
        }
        return ['status' => 'ERROR', 'msg' => 'Invalid credentials'];
    }

    /**
     * Staff login for WebshopAPI Admin (sma_users + groups). Used when storefront has no local DB.
     *
     * @param string $login          Email, username, or phone
     * @param string $password_plain Plain password
     * @return array                  SUCCESS + user payload, or ERROR
     */
    public function login_admin($login, $password_plain) {
        $this->load->model('site');

        $login = trim((string) $login);
        if ($login === '' || $password_plain === null || $password_plain === '') {
            return ['status' => 'ERROR', 'msg' => 'login and password required'];
        }

        $this->db->group_start()
            ->where('email', $login)
            ->or_where('username', $login)
            ->or_where('phone', $login)
            ->group_end();
        $q = $this->db->get('users');
        if (!$q || $q->num_rows() === 0) {
            return ['status' => 'ERROR', 'msg' => 'Invalid credentials'];
        }

        $user = $q->row();
        $authenticated = false;
        if (isset($user->password)) {
            if (function_exists('password_verify') && password_verify($password_plain, $user->password)) {
                $authenticated = true;
            } elseif ($user->password === md5($password_plain)) {
                $authenticated = true;
            }
        }
        if (!$authenticated) {
            return ['status' => 'ERROR', 'msg' => 'Invalid credentials'];
        }

        $group = $this->site->getUserGroup($user->id);
        if (!$group || !isset($group->name)) {
            return ['status' => 'ERROR', 'msg' => 'Access denied'];
        }
        $gname = strtolower((string) $group->name);
        if ($gname !== 'admin' && $gname !== 'owner') {
            return ['status' => 'ERROR', 'msg' => 'Access denied: administrative privileges required'];
        }

        return [
            'status' => 'SUCCESS',
            'user'   => [
                'id'         => (int) $user->id,
                'user_id'    => (int) $user->id,
                'email'      => isset($user->email) ? (string) $user->email : '',
                'username'   => isset($user->username) ? (string) $user->username : '',
                'group_id'   => isset($user->group_id) ? (int) $user->group_id : 0,
                'group_name' => $gname,
            ],
        ];
    }

    public function register_check($phone, $email) {
        $dupPhone = $phone ? $this->webshop_model->get_customer(['phone' => $phone]) : false;
        $dupEmail = $email ? $this->webshop_model->get_customer(['email' => $email]) : false;
        return [
            'status'    => 'SUCCESS',
            'dup_phone' => (bool) $dupPhone,
            'dup_email' => (bool) $dupEmail,
        ];
    }

    /* ================================================================
     * ADDRESSES
     * ================================================================ */

    public function get_addresses($customer_id, $address_id = null) {
        $addresses = $this->webshop_model->get_customer_address($customer_id, $address_id);
        return ['status' => 'SUCCESS', 'addresses' => $addresses ?: []];
    }

    public function add_address(array $d) {
        // Only include columns that actually exist in the 'addresses' table.
        // 'address_type' is NOT a DB column — omitting it prevents an "unknown column" error.
        $row = [
            'company_id'   => isset($d['customer_id'])  ? (int) $d['customer_id'] : 0,
            'address_name' => isset($d['address_name']) ? $d['address_name'] : '',
            'company_name' => isset($d['company_name']) ? $d['company_name'] : '',
            'line1'        => isset($d['line1'])        ? $d['line1']        : '',
            'line2'        => isset($d['line2'])        ? $d['line2']        : '',
            'city'         => isset($d['city'])         ? $d['city']         : '',
            'postal_code'  => isset($d['postal_code'])  ? $d['postal_code']  : '',
            'state'        => isset($d['state'])        ? $d['state']        : '',
            'state_code'   => isset($d['state_code'])   ? $d['state_code']   : '',
            'country'      => isset($d['country'])      ? $d['country']      : '',
            'phone'        => isset($d['phone'])        ? $d['phone']        : '',
            'email_id'     => isset($d['email'])        ? $d['email']        : (isset($d['email_id']) ? $d['email_id'] : ''),
        ];

        $id = $this->webshop_model->add_address($row);
        if ($id) {
            return ['status' => 'SUCCESS', 'address_id' => $id];
        }
        return ['status' => 'ERROR', 'msg' => 'Failed to save address'];
    }

    public function update_address(array $d) {
        $aid = isset($d['address_id']) ? (int) $d['address_id'] : 0;
        $cid = isset($d['customer_id']) ? (int) $d['customer_id'] : (isset($d['company_id']) ? (int) $d['company_id'] : 0);
        if ($aid < 1 || $cid < 1) {
            return ['status' => 'ERROR', 'msg' => 'address_id and customer_id required'];
        }
        $existing = $this->webshop_model->get_address_by_id($aid);
        if (!$existing || (int) (isset($existing['company_id']) ? $existing['company_id'] : 0) !== $cid) {
            return ['status' => 'ERROR', 'msg' => 'Address not found'];
        }
        $row = [
            'address_name' => isset($d['address_name']) ? $d['address_name'] : '',
            'company_name' => isset($d['company_name']) ? $d['company_name'] : '',
            'line1'        => isset($d['line1']) ? $d['line1'] : '',
            'line2'        => isset($d['line2']) ? $d['line2'] : '',
            'city'         => isset($d['city']) ? $d['city'] : '',
            'postal_code'  => isset($d['postal_code']) ? $d['postal_code'] : '',
            'state'        => isset($d['state']) ? $d['state'] : '',
            'state_code'   => isset($d['state_code']) ? $d['state_code'] : '',
            'country'      => isset($d['country']) ? $d['country'] : '',
            'phone'        => isset($d['phone']) ? $d['phone'] : '',
            'email_id'     => isset($d['email']) ? $d['email'] : (isset($d['email_id']) ? $d['email_id'] : ''),
        ];
        $ok = $this->webshop_model->update_customer_address($row, $aid);
        return $ok ? ['status' => 'SUCCESS'] : ['status' => 'ERROR', 'msg' => 'Update failed'];
    }

    public function delete_address_for_customer($customer_id, $address_id) {
        $cid = (int) $customer_id;
        $aid = (int) $address_id;
        if ($cid < 1 || $aid < 1) {
            return ['status' => 'ERROR', 'msg' => 'Invalid id'];
        }
        $existing = $this->webshop_model->get_address_by_id($aid);
        if (!$existing || (int) (isset($existing['company_id']) ? $existing['company_id'] : 0) !== $cid) {
            return ['status' => 'ERROR', 'msg' => 'Address not found'];
        }
        $ok = $this->webshop_model->delete_address($aid);
        return $ok ? ['status' => 'SUCCESS'] : ['status' => 'ERROR', 'msg' => 'Delete failed'];
    }

    public function set_address_default_for_customer($customer_id, $address_id) {
        $cid = (int) $customer_id;
        $aid = (int) $address_id;
        if ($cid < 1 || $aid < 1) {
            return ['status' => 'ERROR', 'msg' => 'Invalid id'];
        }
        $existing = $this->webshop_model->get_address_by_id($aid);
        if (!$existing || (int) (isset($existing['company_id']) ? $existing['company_id'] : 0) !== $cid) {
            return ['status' => 'ERROR', 'msg' => 'Address not found'];
        }
        $ok = $this->webshop_model->set_address_default($cid, $aid);
        return $ok ? ['status' => 'SUCCESS'] : ['status' => 'ERROR', 'msg' => 'Could not set default'];
    }

    /* ================================================================
     * ORDERS
     * ================================================================ */

    /**
     * Create a webshop sale (order) in ElintOm.
     *
     * Expected keys in $d:
     *   reference_no, customer_id, sale_date, grand_total, shipping,
     *   discount, coupon_code, coupon_discount, payment_method,
     *   payment_status (paid|unpaid), sale_status (ordered|pending…),
     *   note, biller_id, warehouse_id,
     *   address (assoc array — shipped to),
     *   items[] each with: product_id, product_code, product_name,
     *     product_unit, unit_price, variant_price, option_id, quantity,
     *     tax_rate, tax_method, item_discount, item_tax
     */
    public function create_order(array $d) {
        $CI =& get_instance();
        if (isset($CI) && is_object($CI) && method_exists($CI, 'load')) {
            $CI->load->helper('order_pricing');
        }

        $items = !empty($d['items']) ? $d['items'] : [];
        if (empty($items)) {
            return ['status' => 'ERROR', 'msg' => 'No items in order'];
        }

        // Build order row — pass all supplied fields through so the webshopapi's
        // full $order array (date, reference_no, customer_id, grand_total …)
        // lands directly in the 'orders' table columns.
        $order_row = $d;
        unset($order_row['items']); // items are stored separately in order_items

        // Ensure required columns have sensible defaults.
        if (empty($order_row['date']) && !empty($order_row['sale_date'])) {
            $order_row['date'] = $order_row['sale_date'];
        }
        if (empty($order_row['date'])) {
            $order_row['date'] = date('Y-m-d H:i:s');
        }
        unset($order_row['sale_date']); // not a column in 'orders'

        // POS E-shop list (orders/eshop_order) and footer alert (eshop/new_eshop_orders) require these flags.
        $order_row['eshop_sale'] = 1;
        if (!isset($order_row['eshop_order_alert_status']) || $order_row['eshop_order_alert_status'] === '') {
            $order_row['eshop_order_alert_status'] = 0;
        }

        if (function_exists('order_db_filter_row')) {
            $order_row = order_db_filter_row($this->db, 'orders', $order_row);
        }
        $order_row['eshop_sale'] = 1;
        if (!array_key_exists('eshop_order_alert_status', $order_row)) {
            $order_row['eshop_order_alert_status'] = 0;
        }

        if (!$this->db->insert('orders', $order_row)) {
            $db_err = $this->db->error();
            $detail = is_array($db_err) && !empty($db_err['message'])
                ? (string) $db_err['message']
                : 'unknown database error';
            log_message('error', 'Webshop_api_model::create_order orders insert failed: ' . $detail);
            return ['status' => 'ERROR', 'msg' => 'DB insert failed (orders): ' . $detail];
        }
        $order_id = $this->db->insert_id();

        if (!$order_id) {
            return ['status' => 'ERROR', 'msg' => 'DB insert failed (orders): no insert id'];
        }

        // Eshop order grid (orders/get_eshop_order) displays `invoice_no` as "Order no"; legacy Webshop_model::add_order
        // always assigns it after insert. API-created rows must do the same or the admin list shows null.
        if (empty($order_row['invoice_no'])) {
            $this->load->library('sma');
            $invoice_no = $this->sma->invoice_format($order_id, date('Y-m-d'));
            $this->db->where('id', $order_id)->update('orders', ['invoice_no' => $invoice_no]);
        }

        $line_sum = 0;
        $lines_inserted = 0;
        foreach ($items as $item) {
            $raw = is_array($item) ? $item : (array) $item;
            if (function_exists('order_items_prepare_row_for_insert')) {
                $item = order_items_prepare_row_for_insert($this->db, $raw, $order_id);
            } elseif (function_exists('order_pricing_normalize_item_row')) {
                $item = order_pricing_normalize_item_row($raw);
                $item['sale_id'] = (int) $order_id;
            } elseif (function_exists('order_pick_order_item_line_for_db')) {
                $item = order_pick_order_item_line_for_db($raw);
                $item['sale_id'] = (int) $order_id;
            } else {
                $item = $raw;
                $item['sale_id'] = (int) $order_id;
                unset($item['variant_id'], $item['variant_price'], $item['product_option_id']);
            }
            if ($item === null || empty($item['product_id'])) {
                log_message('error', 'Webshop_api_model::create_order skipped line — missing product_id');
                continue;
            }
            if (function_exists('order_pick_order_item_line_for_db')) {
                $item = order_pick_order_item_line_for_db($item);
            } else {
                foreach (array('variant_id', 'variant_price', 'product_option_id') as $drop) {
                    unset($item[$drop]);
                }
            }
            if (function_exists('order_db_filter_row')) {
                $item = order_db_filter_row($this->db, 'order_items', $item);
            }
            if (array_key_exists('variant_price', $item) || array_key_exists('variant_id', $item)) {
                log_message('error', 'Webshop_api_model::create_order blocked variant_* keys for order ' . $order_id);
                unset($item['variant_id'], $item['variant_price'], $item['product_option_id']);
            }
            if (!$this->db->insert('order_items', $item)) {
                $db_err = $this->db->error();
                $detail = is_array($db_err) && !empty($db_err['message'])
                    ? (string) $db_err['message']
                    : 'unknown database error';
                log_message('error', 'Webshop_api_model::create_order order_items insert failed: ' . $detail
                    . ' keys=' . implode(',', array_keys($item)));
                $this->db->where('id', $order_id)->delete('orders');
                $this->db->where('sale_id', $order_id)->delete('order_items');
                return ['status' => 'ERROR', 'msg' => 'DB insert failed (order_items): ' . $detail];
            }
            $lines_inserted++;
            $line_sum += isset($item['subtotal']) ? (float) $item['subtotal'] : 0;
        }

        if ($lines_inserted < 1) {
            log_message('error', 'Webshop_api_model::create_order — no order_items rows inserted for order ' . $order_id);
            $this->db->where('id', $order_id)->delete('orders');
            return ['status' => 'ERROR', 'msg' => 'No order lines could be saved. Check product_id and line pricing.'];
        }

        $patch = array('total_items' => $lines_inserted);
        if ($line_sum > 0) {
            if (empty($order_row['total']) || (float) $order_row['total'] <= 0) {
                $patch['total'] = $line_sum;
            }
            if (empty($order_row['grand_total']) || (float) $order_row['grand_total'] <= 0) {
                $shipping = isset($order_row['shipping']) ? (float) $order_row['shipping'] : 0;
                $tax = isset($order_row['total_tax']) ? (float) $order_row['total_tax'] : 0;
                if (isset($order_row['product_tax']) && (float) $order_row['product_tax'] > 0) {
                    $tax = (float) $order_row['product_tax'];
                }
                $patch['grand_total'] = $line_sum + $shipping + $tax;
            }
        }
        if ($patch !== array()) {
            if (function_exists('order_db_filter_row')) {
                $patch = order_db_filter_row($this->db, 'orders', $patch);
            }
            $this->db->where('id', $order_id)->update('orders', $patch);
        }

        // Ensure API-created rows appear in E-shop order grid and trigger admin "new order" polling.
        $this->db->where('id', $order_id)->update('orders', array(
            'eshop_sale'               => 1,
            'eshop_order_alert_status' => 0,
        ));

        return [
            'status'       => 'SUCCESS',
            'sale_id'      => $order_id,
            'order_id'     => $order_id,
            'reference_no' => isset($d['reference_no']) ? $d['reference_no'] : '',
        ];
    }

    public function get_gateway_credentials() {
        // ── Step 1: Config file fallback (base defaults) ────────────────
        $CI = get_instance();
        $CI->config->load('payment_gateways', true);
        $cfg     = $CI->config->item('payment_gateways');
        if (!is_array($cfg)) { $cfg = array(); }
        $testMode = !empty($cfg['TestMode']);

        // ── Step 2: Read from sma_payment_gateways DB table ─────────────
        // Table columns: id, pgateway_title, pgateway_testing (JSON),
        //                pgateway_production (JSON), is_active, updated_at
        $dbCreds = array(); // keyed by normalised gateway slug
        $titleMap = array(
            'ccavenue'  => array('ccavenue', 'cc avenue', 'ccav'),
            'razorpay'  => array('razorpay', 'razor pay'),
            'paytm'     => array('paytm', 'pay tm'),
            'instamojo' => array('instamojo', 'insta mojo'),
            'stripe'    => array('stripe'),
            'paypal_pro'=> array('paypal', 'paypal pro'),
        );
        if ($this->db->table_exists('sma_payment_gateways')) {
            $rows = $this->db->get('sma_payment_gateways')->result_array();
            foreach ($rows as $row) {
                $title = strtolower(trim((string) (isset($row['pgateway_title']) ? $row['pgateway_title'] : '')));
                // Match title to a slug.
                $slug = null;
                foreach ($titleMap as $s => $aliases) {
                    foreach ($aliases as $alias) {
                        if (strpos($title, $alias) !== false) { $slug = $s; break 2; }
                    }
                }
                if (!$slug) { continue; }

                // Pick testing vs production JSON column.
                $jsonCol = $testMode ? 'pgateway_testing' : 'pgateway_production';
                $json    = isset($row[$jsonCol]) && $row[$jsonCol] !== '' ? $row[$jsonCol] : null;
                // Fallback: if production is null/empty, use testing.
                if ($json === null && !$testMode && isset($row['pgateway_testing'])) {
                    $json = $row['pgateway_testing'];
                }
                $creds = $json ? @json_decode($json, true) : array();
                if (!is_array($creds)) { $creds = array(); }

                // is_active: treat NULL as active (backwards compatible).
                $isActive = !isset($row['is_active']) || $row['is_active'] === null || (int)$row['is_active'] === 1;
                $creds['_db_active'] = $isActive ? 1 : 0;
                $dbCreds[$slug] = $creds;
            }
        }

        // ── Step 3: pos_settings flags ───────────────────────────────────
        $flags = array('paytm'=>0,'razorpay'=>0,'ccavenue'=>0,'instamojo'=>0,'stripe'=>0,'paypal_pro'=>0);
        $q = $this->db->select(implode(',', array_keys($flags)))->get('pos_settings');
        if ($q && $q->num_rows() > 0) {
            $row = (array) $q->row();
            foreach ($flags as $k => $v) {
                if (isset($row[$k])) { $flags[$k] = (int) $row[$k]; }
            }
        }

        // ── Helper: pick cred from DB first, then config file ────────────
        $cred = function($slug, $key, $cfgKey = null) use ($dbCreds, $cfg) {
            if (isset($dbCreds[$slug][$key]) && $dbCreds[$slug][$key] !== '') {
                return $dbCreds[$slug][$key];
            }
            $ck = $cfgKey ?: $key;
            return isset($cfg[$slug][$ck]) ? $cfg[$slug][$ck] : '';
        };

        // Gateway is enabled when: pos_settings flag=1 OR DB row exists and is active.
        $isEnabled = function($slug) use ($flags, $dbCreds) {
            if ($flags[$slug]) { return 1; }
            if (isset($dbCreds[$slug]) && !empty($dbCreds[$slug]['_db_active'])) { return 1; }
            return 0;
        };

        // ── Step 4: Build output ─────────────────────────────────────────
        $gateways = array();

        $gateways['ccavenue'] = array(
            'enabled'     => $isEnabled('ccavenue'),
            'MERCHANT_ID' => $cred('ccavenue', 'MERCHANT_ID'),
            'ACCESS_CODE' => $cred('ccavenue', 'ACCESS_CODE'),
            'API_KEY'     => $cred('ccavenue', 'API_KEY'),
            'API_URL'     => $cred('ccavenue', 'API_URL'),
        );
        $gateways['paytm'] = array(
            'enabled'          => $isEnabled('paytm'),
            'MERCHANT_KEY'     => $cred('paytm', 'PAYTM_MERCHANT_KEY', 'MERCHANT_KEY'),
            'MERCHANT_MID'     => $cred('paytm', 'PAYTM_MERCHANT_MID', 'MERCHANT_MID'),
            'MERCHANT_WEBSITE' => $cred('paytm', 'PAYTM_MERCHANT_WEBSITE', 'MERCHANT_WEBSITE'),
            'ENVIRONMENT'      => $cred('paytm', 'PAYTM_ENVIRONMENT', 'ENVIRONMENT'),
            'TXN_URL'          => $cred('paytm', 'PAYTM_TXN_URL', 'TXN_URL'),
        );
        $gateways['razorpay'] = array(
            'enabled'    => $isEnabled('razorpay'),
            'KEY_ID'     => $cred('razorpay', 'KEY_ID'),
            'KEY_SECRET' => $cred('razorpay', 'KEY_SECRET'),
            'API_URL'    => $cred('razorpay', 'API_URL'),
        );
        $gateways['instamojo'] = array(
            'enabled'    => $isEnabled('instamojo'),
            'API_KEY'    => $cred('instamojo', 'API_KEY'),
            'AUTH_TOKEN' => $cred('instamojo', 'AUTH_TOKEN'),
            'API_URL'    => $cred('instamojo', 'API_URL'),
        );
        $gateways['stripe'] = array(
            'enabled'         => $isEnabled('stripe'),
            'SECRET_KEY'      => $cred('stripe', 'SECRET_KEY', 'stripe_secret_key'),
            'PUBLISHABLE_KEY' => $cred('stripe', 'PUBLISHABLE_KEY', 'stripe_publishable_key'),
        );

        return array('status' => 'SUCCESS', 'gateways' => $gateways);
    }

    /**
     * Load one e-shop order by numeric id or by reference_no (e.g. ES-20260513-ABC123).
     *
     * @param int         $order_id     Primary key on sma_orders when known (>0).
     * @param string|null $reference_no When $order_id is 0, lookup by this reference (eshop_sale=1).
     */
    public function get_order($order_id, $reference_no = null) {
        $order_id = (int) $order_id;
        $reference_no = $reference_no !== null ? trim((string) $reference_no) : '';

        if ($order_id > 0) {
            $order = $this->db->where('id', $order_id)->get('orders')->row_array();
        } elseif ($reference_no !== '') {
            $order = $this->db->where('reference_no', $reference_no)->where('eshop_sale', 1)->get('orders')->row_array();
            if ($order && isset($order['id'])) {
                $order_id = (int) $order['id'];
            }
        } else {
            return ['status' => 'ERROR', 'msg' => 'Order not found'];
        }

        if (!$order || empty($order['id'])) {
            return ['status' => 'ERROR', 'msg' => 'Order not found'];
        }

        $sale_id = (int) $order['id'];
        $items = $this->db->where('sale_id', $sale_id)->get('order_items')->result_array();
        $items = $items ?: array();
        foreach ($items as $idx => $item) {
            $items[$idx] = order_pricing_normalize_item_row($item);
        }
        $totals = order_pricing_apply_order_totals($order, $items);
        return ['status' => 'SUCCESS', 'order' => $totals['order'], 'items' => $totals['items']];
    }

    /**
     * Guest tracking link from WhatsApp/email: /track_order/{md5(orders.id)}.
     * No customer login required; token is the MD5 of the numeric order id.
     */
    public function get_order_by_track_hash($hash) {
        $hash = strtolower(trim((string) $hash));
        if (!preg_match('/^[a-f0-9]{32}$/', $hash)) {
            return ['status' => 'ERROR', 'msg' => 'Invalid tracking link'];
        }

        $order = $this->db->query(
            'SELECT * FROM ' . $this->db->dbprefix . 'orders WHERE eshop_sale = 1 AND MD5(id) = ? LIMIT 1',
            array($hash)
        )->row_array();

        if (!$order || empty($order['id'])) {
            return ['status' => 'ERROR', 'msg' => 'Order not found'];
        }

        return $this->get_order((int) $order['id'], null);
    }

    /**
     * Record a successful CCAvenue payment against an order (same DB logic as Webshop_model::CcavenuePayAfterSale).
     * Used by the DB-less WebshopAPI storefront so ElintOm stays the source of truth.
     *
     * @param array $response_data Decrypted CCAvenue response map (order_id, amount, tracking_id, currency, …)
     */
    public function record_ccavenue_payment(array $response_data) {
        $this->load->model('webshop_model');
        $order_id = $this->webshop_model->CcavenuePayAfterSale($response_data);
        if ($order_id) {
            return ['status' => 'SUCCESS', 'order_id' => (int) $order_id];
        }
        return ['status' => 'ERROR', 'msg' => 'Could not record payment for this order'];
    }

    /**
     * Mark a webshop order as Cancelled (called when the buyer aborts at the
     * payment gateway, or when the gateway declines payment).
     *
     * Only orders flagged as eshop_sale=1 and still in a pre-paid state
     * (payment_status in due/pending/Failed) are eligible — guards against the
     * cancel endpoint being used to wipe completed sales.
     *
     * @param int         $order_id      Primary key (>0) when known.
     * @param string|null $reference_no  Reference (e.g. ES-YYYYMMDD-XXXXXX) when $order_id is 0.
     * @param string|null $reason        Optional buyer/gateway reason; persisted to staff_note.
     * @return array                     {status: SUCCESS|ERROR, ...}
     */
    public function cancel_order($order_id, $reference_no = null, $reason = null) {
        $order_id = (int) $order_id;
        $reference_no = $reference_no !== null ? trim((string) $reference_no) : '';

        if ($order_id > 0) {
            $row = $this->db->where('id', $order_id)->get('orders')->row_array();
        } elseif ($reference_no !== '') {
            $row = $this->db->where('reference_no', $reference_no)->where('eshop_sale', 1)->get('orders')->row_array();
        } else {
            return ['status' => 'ERROR', 'msg' => 'order_id or reference_no required'];
        }

        if (empty($row) || !is_array($row)) {
            return ['status' => 'ERROR', 'msg' => 'Order not found'];
        }

        // Only allow cancelling webshop-originated orders.
        if (empty($row['eshop_sale'])) {
            return ['status' => 'ERROR', 'msg' => 'Order is not a webshop sale'];
        }

        // Block cancelling a paid sale by mistake — only pre-payment states are eligible.
        $pay_status = isset($row['payment_status']) ? strtolower((string) $row['payment_status']) : '';
        $allowed_pay_states = array('due', 'pending', 'failed', '');
        if (!in_array($pay_status, $allowed_pay_states, true)) {
            return [
                'status' => 'ERROR',
                'msg'    => 'Cannot cancel: order already paid or partially paid',
            ];
        }

        // Idempotent: already Cancelled → treat as success so the storefront
        // doesn't loop on stale links / retries.
        if (isset($row['sale_status']) && strtolower((string) $row['sale_status']) === 'cancelled') {
            return ['status' => 'SUCCESS', 'order_id' => (int) $row['id'], 'already_cancelled' => true];
        }

        $update = array(
            'sale_status'    => 'Cancelled',
            'payment_status' => 'Failed',
        );
        if ($reason !== null && $reason !== '') {
            // Append to existing staff_note rather than overwrite so the admin can still
            // see whatever the original cart/checkout flow stored.
            $existing = isset($row['staff_note']) ? trim((string) $row['staff_note']) : '';
            $stamp = '[' . date('Y-m-d H:i:s') . '] Payment cancelled: ' . substr($reason, 0, 240);
            $update['staff_note'] = $existing !== '' ? ($existing . "\n" . $stamp) : $stamp;
        }

        $this->db->where('id', (int) $row['id'])->update('orders', $update);

        if ($this->db->affected_rows() < 1) {
            return ['status' => 'ERROR', 'msg' => 'Order cancel failed (no rows updated)'];
        }
        return ['status' => 'SUCCESS', 'order_id' => (int) $row['id']];
    }

    public function get_customer_sales($customer_id, $sale_status = '') {
        $cid = (int) $customer_id;
        if ($cid < 1) {
            return ['status' => 'ERROR', 'msg' => 'customer_id required'];
        }

        // Webshop checkout (create_order / addorder) inserts into `orders` with eshop_sale=1.
        // Legacy eshop_model::getCustomerSales() reads `sales`, so API-mode storefronts saw no rows.
        $this->db->from('orders');
        $this->db->where('customer_id', $cid);
        $this->db->where('eshop_sale', 1);
        if ($sale_status !== null && $sale_status !== '') {
            $this->db->where('sale_status !=', $sale_status);
        }
        $this->db->order_by('date', 'desc');
        $q = $this->db->get();
        $from_orders = ($q && $q->num_rows() > 0) ? $q->result() : [];

        $res = $from_orders;
        if (!$res) {
            $this->load->model('eshop_model');
            $legacy = $this->eshop_model->getCustomerSales([
                'user_id'     => $cid,
                'sale_status' => $sale_status,
                'sale_type'   => 'eshop_sale',
            ]);
            $res = $legacy ? $legacy : [];
        }

        foreach ($res as &$sale) {
            unset($sale->note, $sale->staff_note, $sale->created_by,
                $sale->updated_by, $sale->pos, $sale->offline_sale);
        }
        unset($sale);
        return ['status' => 'SUCCESS', 'sales_count' => count($res), 'sales' => $res];
    }

    /* ================================================================
     * COUPONS
     * ================================================================ */

    public function apply_coupon($code, $cart_total = 0) {
        $today  = date('Y-m-d');
        $coupon = $this->db
            ->where('coupon_code', $code)
            ->where('is_active', 1)
            ->where('status !=', 'expired')
            ->where('(expiry_date IS NULL OR expiry_date >= "' . $today . '")')
            ->get('sma_discount_coupons')
            ->row_array();

        if (!$coupon) {
            return ['status' => 'ERROR', 'msg' => 'Coupon not found or expired'];
        }
        $max_coupons = isset($coupon['max_coupons']) ? (int) $coupon['max_coupons'] : 0;
        $used_coupons = isset($coupon['used_coupons']) ? (int) $coupon['used_coupons'] : 0;
        if ($max_coupons > 0 && $used_coupons >= $max_coupons) {
            return ['status' => 'ERROR', 'msg' => 'Coupon usage limit reached'];
        }

        // System settings UI stores minimum_cart_amount, discount_rate ("10%" or flat),
        // maximum_discount_amount. Legacy rows may use min_order, discount_type, discount, max_discount.
        $min_order = 0.0;
        if (isset($coupon['minimum_cart_amount']) && $coupon['minimum_cart_amount'] !== '' && $coupon['minimum_cart_amount'] !== null) {
            $min_order = (float) $coupon['minimum_cart_amount'];
        } elseif (!empty($coupon['min_order'])) {
            $min_order = (float) $coupon['min_order'];
        }
        if ($min_order > 0.0 && $cart_total < $min_order) {
            return ['status' => 'ERROR', 'msg' => 'Cart total below minimum order for this coupon'];
        }

        $rate_raw = '';
        if (isset($coupon['discount_rate']) && $coupon['discount_rate'] !== '' && $coupon['discount_rate'] !== null) {
            $rate_raw = trim((string) $coupon['discount_rate']);
        }
        if ($rate_raw === '' && isset($coupon['discount'])) {
            $rate_raw = trim((string) $coupon['discount']);
        }

        $is_percentage = false;
        $rate_value = 0.0;
        if ($rate_raw !== '') {
            if (stripos($rate_raw, '%') !== false) {
                $is_percentage = true;
                $rate_value = (float) str_replace(['%', ' '], '', $rate_raw);
            } elseif (!empty($coupon['discount_type']) && strtolower((string) $coupon['discount_type']) === 'percentage') {
                $is_percentage = true;
                $rate_value = (float) $rate_raw;
            } else {
                $is_percentage = false;
                $rate_value = (float) $rate_raw;
            }
        } elseif (!empty($coupon['discount_type']) && (string) $coupon['discount_type'] === 'percentage' && isset($coupon['discount'])) {
            $is_percentage = true;
            $rate_value = (float) $coupon['discount'];
        } elseif (isset($coupon['discount'])) {
            $is_percentage = false;
            $rate_value = (float) $coupon['discount'];
        }

        $discount = $is_percentage
            ? round(($cart_total * $rate_value) / 100, 2)
            : round($rate_value, 2);

        $max_discount = 0.0;
        if (isset($coupon['maximum_discount_amount']) && $coupon['maximum_discount_amount'] !== '' && $coupon['maximum_discount_amount'] !== null) {
            $max_discount = (float) $coupon['maximum_discount_amount'];
        } elseif (!empty($coupon['max_discount'])) {
            $max_discount = (float) $coupon['max_discount'];
        }
        if ($max_discount > 0.0 && $discount > $max_discount) {
            $discount = $max_discount;
        }

        $coupon['calculated_discount'] = $discount;
        $coupon['discount_type'] = $is_percentage ? 'percentage' : 'fixed';
        $coupon['discount'] = $rate_value;
        $coupon['min_order'] = $min_order;
        $coupon['max_discount'] = $max_discount;

        return ['status' => 'SUCCESS', 'coupon' => $coupon];
    }

    /* ================================================================
     * WISHLIST
     * ================================================================ */

    public function get_wishlist($user_id) {
        return [
            'status'   => 'SUCCESS',
            'wishlist' => $this->webshop_model->get_wishlist($user_id) ?: [],
            'count'    => $this->webshop_model->get_wishlist_count($user_id),
        ];
    }

    public function add_wishlist($user_id, $product_id, $option_id = null) {
        // The legacy Webshop_model::add_to_wishlist($data) expects a single
        // associative array AND never sets `date` — under STRICT_ALL_TABLES +
        // NO_ZERO_DATE the insert blew up (`INSERT INTO sma_eshop_wishlist (16)
        // VALUES ('')`). The API storefront does not run the legacy POS so we
        // implement the write here directly and keep the contract clean.
        $user_id    = (int) $user_id;
        $product_id = (int) $product_id;
        $option_id  = (int) $option_id;
        if ($user_id <= 0 || $product_id <= 0) {
            return ['status' => 'ERROR', 'msg' => 'user_id and product_id required'];
        }

        // Idempotent: clicking the heart again shouldn't error out.
        // Legacy rows may have option_id NULL while new writes use 0 — treat as equivalent.
        $exists = $this->db->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->where('IFNULL(option_id, 0) = ' . $option_id, null, false)
            ->get('eshop_wishlist');
        if ($exists && $exists->num_rows() > 0) {
            return ['status' => 'SUCCESS'];
        }

        $ok = $this->db->insert('eshop_wishlist', [
            'user_id'    => $user_id,
            'product_id' => $product_id,
            'option_id'  => $option_id,
            'date'       => date('Y-m-d H:i:s'),
        ]);
        if (!$ok) {
            $err = $this->db->error();
            log_message('error', 'Webshop_api_model::add_wishlist insert failed: '
                . (isset($err['message']) ? $err['message'] : 'unknown'));
            return ['status' => 'ERROR', 'msg' => 'Wishlist insert failed'];
        }
        return ['status' => 'SUCCESS'];
    }

    public function remove_wishlist($user_id, $product_id, $option_id = null) {
        $user_id    = (int) $user_id;
        $product_id = (int) $product_id;
        $option_id  = (int) $option_id;
        if ($user_id <= 0 || $product_id <= 0) {
            return ['status' => 'ERROR', 'msg' => 'user_id and product_id required'];
        }

        $this->db->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->where('IFNULL(option_id, 0) = ' . $option_id, null, false)
            ->delete('eshop_wishlist');

        // Idempotent: removing a row that isn't there is still a success.
        return ['status' => 'SUCCESS'];
    }

    /* ================================================================
     * COMPANY / BILLER
     * ================================================================ */

    public function get_company($id) {
        if (!$id) {
            return ['status' => 'ERROR', 'msg' => 'biller_id required'];
        }
        $q = $this->db->where('id', $id)->get('companies');
        if ($q->num_rows() > 0) {
            return ['status' => 'SUCCESS', 'company' => $q->row_array()];
        }
        return ['status' => 'ERROR', 'msg' => 'Company not found'];
    }

    /* ================================================================
     * PRODUCT REVIEWS (persist on ElintOm DB — sma_webshop_products_reviews)
     * ================================================================ */

    public function submit_product_review($product_id, $rating, $review, $customer_name, $customer_id, array $context) {
        $product_id = (int) $product_id;
        $rating = (int) $rating;
        $review = trim((string) $review);
        $customer_name = trim((string) $customer_name);
        if ($customer_name === '') {
            $customer_name = 'Customer';
        }
        if ($product_id <= 0 || $rating < 1 || $rating > 5 || $review === '') {
            return ['status' => 'ERROR', 'msg' => 'product_id, rating (1–5), and review are required'];
        }
        $cid = ($customer_id !== null && (int) $customer_id > 0) ? (int) $customer_id : null;
        $ok = $this->webshop_model->insert_webshop_product_review($product_id, $rating, $review, $customer_name, $cid, $context);
        if ($ok) {
            return ['status' => 'SUCCESS', 'message' => 'Review saved'];
        }
        $err = isset($this->webshop_model->last_review_insert_error)
            ? trim((string) $this->webshop_model->last_review_insert_error)
            : '';
        return ['status' => 'ERROR', 'msg' => $err !== '' ? $err : 'Could not save review'];
    }

    public function get_product_reviews_list($product_id, $limit = 200) {
        $product_id = (int) $product_id;
        $limit = (int) $limit;
        if ($product_id <= 0) {
            return ['status' => 'ERROR', 'msg' => 'product_id required'];
        }
        $this->webshop_model->ensure_webshop_product_reviews_table();
        $items = $this->webshop_model->get_webshop_product_reviews_for_product_id($product_id, $limit > 0 ? $limit : 200);
        return ['status' => 'SUCCESS', 'items' => $items ?: []];
    }

    public function get_product_rating($product_id) {
        if (!$this->db->table_exists('webshop_products_reviews')) {
            return ['status' => 'SUCCESS', 'average' => 0, 'count' => 0];
        }
        $q = $this->db
            ->select('AVG(reviews_rattings) as average, COUNT(id) as count')
            ->where('product_id', (int) $product_id)
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->get('webshop_products_reviews');
        $row = $q->row();
        return [
            'status'  => 'SUCCESS',
            'average' => $row ? (float) $row->average : 0,
            'count'   => $row ? (int) $row->count : 0
        ];
    }

    /* ================================================================
     * CONTACT / LEADS
     * ================================================================ */

    /**
     * Ensure sma_cms_webshop_contact_forms exists (theme/page form presets for webshop).
     *
     * @return bool
     */
    public function ensure_webshop_contact_forms_table()
    {
        if ($this->db->table_exists('sma_cms_webshop_contact_forms')) {
            return true;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `sma_cms_webshop_contact_forms` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `theme_slug` VARCHAR(64) NOT NULL,
            `form_key` VARCHAR(128) NOT NULL DEFAULT 'default',
            `page_url` VARCHAR(255) NULL DEFAULT NULL,
            `form_name` VARCHAR(255) NULL DEFAULT NULL,
            `config_json` MEDIUMTEXT NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_theme_form_key` (`theme_slug`, `form_key`),
            KEY `idx_theme_page` (`theme_slug`, `page_url`, `is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return (bool) $this->db->query($sql);
    }

    /**
     * Load contact form preset from DB for a theme + page.
     *
     * @param string $theme_slug
     * @param string $page_url
     * @param string $form_key
     * @return array{status:string,msg?:string,form?:array<string,mixed>}
     */
    public function get_contact_form_preset($theme_slug, $page_url = '', $form_key = '')
    {
        $this->ensure_webshop_contact_forms_table();
        if (!$this->db->table_exists('sma_cms_webshop_contact_forms')) {
            return array('status' => 'ERROR', 'msg' => 'Contact forms table not found');
        }

        $theme = strtolower(trim(preg_replace('/[^a-z0-9_.-]/', '', (string) $theme_slug)));
        if ($theme === '') {
            return array('status' => 'ERROR', 'msg' => 'theme_slug is required');
        }

        $page_url = $this->normalize_contact_form_page_url($page_url);
        $form_key = strtolower(trim(preg_replace('/[^a-z0-9_.-]/', '', (string) $form_key)));

        $row = null;
        if ($page_url !== '') {
            $row = $this->fetch_contact_form_row('sma_cms_webshop_contact_forms', $theme, array('page_url' => $page_url));
            if (!$row) {
                $slugKey = ltrim($page_url, '/');
                if ($slugKey !== '') {
                    $row = $this->fetch_contact_form_row('sma_cms_webshop_contact_forms', $theme, array('form_key' => $slugKey));
                }
            }
        }
        if (!$row && $form_key !== '') {
            $row = $this->fetch_contact_form_row('sma_cms_webshop_contact_forms', $theme, array('form_key' => $form_key));
        }
        if (!$row) {
            $row = $this->fetch_contact_form_row('sma_cms_webshop_contact_forms', $theme, array('form_key' => 'default'));
        }
        if (!$row && $form_key !== '') {
            $row = $this->fetch_contact_form_row_by_key_only('sma_cms_webshop_contact_forms', $form_key);
        }

        if (!$row) {
            return array('status' => 'ERROR', 'msg' => 'Contact form preset not found');
        }

        $form = $this->decode_contact_form_config_row($row);
        if (empty($form['fields']) || !is_array($form['fields'])) {
            return array('status' => 'ERROR', 'msg' => 'Contact form preset has no fields');
        }

        return array(
            'status' => 'SUCCESS',
            'form'   => $form,
        );
    }

    /**
     * @param string $page_url
     * @return string
     */
    protected function normalize_contact_form_page_url($page_url)
    {
        $page_url = trim((string) $page_url);
        if ($page_url === '') {
            return '';
        }
        $page_url = '/' . ltrim($page_url, '/');
        return $page_url === '//' ? '/' : $page_url;
    }

    /**
     * @param string $table
     * @param string $theme
     * @param array<string,string> $match
     * @return array|null
     */
    protected function fetch_contact_form_row($table, $theme, array $match)
    {
        $this->db->from($table);
        $this->db->where('theme_slug', $theme);
        $this->db->where('is_active', 1);
        if (isset($match['page_url'])) {
            $this->db->where('page_url', $match['page_url']);
        }
        if (isset($match['form_key'])) {
            $this->db->where('form_key', $match['form_key']);
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return null;
        }
        return $q->row_array();
    }

    /**
     * Resolve an active template by form_key when theme_slug does not match storefront view folder.
     *
     * @param string $table
     * @param string $form_key
     * @return array|null
     */
    protected function fetch_contact_form_row_by_key_only($table, $form_key)
    {
        $form_key = strtolower(trim(preg_replace('/[^a-z0-9_.-]/', '', (string) $form_key)));
        if ($form_key === '') {
            return null;
        }
        $this->db->from($table);
        $this->db->where('form_key', $form_key);
        $this->db->where('is_active', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return null;
        }
        return $q->row_array();
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    protected function decode_contact_form_config_row(array $row)
    {
        $raw = isset($row['config_json']) ? (string) $row['config_json'] : '';
        $cfg = json_decode($raw, true);
        if (!is_array($cfg)) {
            return array();
        }
        $fields = isset($cfg['fields']) && is_array($cfg['fields']) ? $cfg['fields'] : array();
        $form = array(
            'id'          => isset($row['id']) ? (int) $row['id'] : 0,
            'theme_slug'  => isset($row['theme_slug']) ? (string) $row['theme_slug'] : '',
            'form_key'    => isset($row['form_key']) ? (string) $row['form_key'] : '',
            'page_url'    => isset($row['page_url']) ? (string) $row['page_url'] : '',
            'form_name'   => isset($row['form_name']) ? (string) $row['form_name'] : '',
            'title'       => isset($cfg['title']) ? (string) $cfg['title'] : 'Contact Us',
            'subtitle'    => isset($cfg['subtitle']) ? (string) $cfg['subtitle'] : '',
            'button_text' => isset($cfg['button_text']) && trim((string) $cfg['button_text']) !== ''
                ? (string) $cfg['button_text']
                : 'Send Message',
            'source'      => isset($cfg['source']) && trim((string) $cfg['source']) !== ''
                ? (string) $cfg['source']
                : 'webshop_contact_form',
            'fields'      => $fields,
        );
        if (isset($cfg['style']) && is_array($cfg['style'])) {
            $form['style'] = $cfg['style'];
        }
        return $form;
    }

    public function submit_contact_lead(array $data) {
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        if ($name === '') {
            $name = isset($data['full_name']) ? trim((string) $data['full_name']) : '';
        }
        $phone = isset($data['phone']) ? trim((string) $data['phone']) : '';
        if ($phone === '') {
            $phone = isset($data['mobile']) ? trim((string) $data['mobile']) : '';
        }
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $message = isset($data['message']) ? trim((string) $data['message']) : '';
        if ($message === '') {
            $message = isset($data['comments']) ? trim((string) $data['comments']) : '';
        }
        $source = isset($data['source']) ? trim((string) $data['source']) : '';

        $responseJson = '';
        if (isset($data['response_json'])) {
            if (is_array($data['response_json'])) {
                $encoded = json_encode($data['response_json'], JSON_UNESCAPED_UNICODE);
                $responseJson = $encoded !== false ? $encoded : '';
            } else {
                $responseJson = trim((string) $data['response_json']);
            }
            if ($responseJson !== '') {
                $decoded = json_decode($responseJson, true);
                if (!is_array($decoded)) {
                    return ['status' => 'ERROR', 'msg' => 'response_json must be valid JSON'];
                }
                $reencoded = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                $responseJson = $reencoded !== false ? $reencoded : $responseJson;
            }
        }

        $table = $this->db->table_exists('sma_leads') ? 'sma_leads' : ($this->db->table_exists('leads') ? 'leads' : '');
        if ($table === '') {
            return ['status' => 'ERROR', 'msg' => 'Leads table not found'];
        }

        $this->ensure_leads_response_json_capacity($table);

        $statusId = '';
        if (isset($data['status_id']) && $data['status_id'] !== '' && $data['status_id'] !== null) {
            $statusId = (int) $data['status_id'];
        } elseif (isset($data['status']) && trim((string) $data['status']) !== '') {
            $resolved = $this->resolve_leads_status_id_by_name((string) $data['status']);
            if ($resolved !== null) {
                $statusId = $resolved;
            }
        }
        if ($statusId === '' || $statusId === 0) {
            $resolved = $this->resolve_leads_status_id_by_name('New');
            if ($resolved !== null) {
                $statusId = $resolved;
            }
        }

        $row = array(
            'full_name'  => $name,
            'mobile'     => $phone,
            'email'      => $email,
            'comments'   => $message,
            'source'     => $source !== '' ? $source : 'Webshop Contact Form',
            'created_by' => 1,
            'status_id'  => $statusId,
            'type'       => 'General Lead',
            'created_at' => date('Y-m-d H:i:s'),
        );
        if ($responseJson !== '') {
            $row['response_json'] = $responseJson;
        }

        try {
            $allowed = array();
            $meta = $this->db->field_data($table);
            if (is_array($meta)) {
                foreach ($meta as $m) {
                    if (isset($m->name)) {
                        $allowed[(string) $m->name] = true;
                    }
                }
            }
            if (!empty($allowed)) {
                $row = array_intersect_key($row, $allowed);
            }

            $oldDbDebug = isset($this->db->db_debug) ? $this->db->db_debug : false;
            $this->db->db_debug = false;
            $leadId = 0;

            // Upsert by contact number: update existing lead if same number exists.
            $phoneColumn = isset($allowed['mobile']) ? 'mobile' : (isset($allowed['phone']) ? 'phone' : '');
            if ($phoneColumn !== '' && $phone !== '') {
                $existing = $this->db
                    ->select('id')
                    ->from($table)
                    ->where($phoneColumn, $phone)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row_array();
                if (is_array($existing) && !empty($existing['id'])) {
                    $leadId = (int) $existing['id'];
                }
            }

            $ok = false;
            $wasUpdate = ($leadId > 0);
            if ($leadId > 0) {
                $updateRow = $row;
                if (isset($allowed['updated_at'])) {
                    $updateRow['updated_at'] = date('Y-m-d H:i:s');
                }
                if (isset($allowed['created_at'])) {
                    unset($updateRow['created_at']);
                }
                if (isset($updateRow['status_id'])) {
                    unset($updateRow['status_id']);
                }
                if (!empty($updateRow)) {
                    $ok = (bool) $this->db->where('id', $leadId)->update($table, $updateRow);
                }
            } else {
                $ok = (bool) $this->db->insert($table, $row);
                if ($ok) {
                    $leadId = (int) $this->db->insert_id();
                }
            }

            $dbErr = method_exists($this->db, 'error') ? $this->db->error() : array();
            $this->db->db_debug = $oldDbDebug;
            if (!$ok) {
                $msg = 'Could not save lead.';
                if (is_array($dbErr) && !empty($dbErr['message'])) {
                    $msg = (string) $dbErr['message'];
                }
                return ['status' => 'ERROR', 'msg' => $msg];
            }
            $historyTable = $this->db->table_exists('sma_leads_history')
                ? 'sma_leads_history'
                : ($this->db->table_exists('leads_history') ? 'leads_history' : '');
            if ($historyTable !== '' && $message !== '') {
                $historyRow = array(
                    'lead_id'    => $leadId,
                    'comments'   => $message,
                    'created_by' => isset($row['created_by']) ? $row['created_by'] : '',
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $historyAllowed = array();
                $historyMeta = $this->db->field_data($historyTable);
                if (is_array($historyMeta)) {
                    foreach ($historyMeta as $m) {
                        if (isset($m->name)) {
                            $historyAllowed[(string) $m->name] = true;
                        }
                    }
                }
                if (!empty($historyAllowed)) {
                    $historyRow = array_intersect_key($historyRow, $historyAllowed);
                }
                if (!empty($historyRow)) {
                    $this->db->insert($historyTable, $historyRow);
                }
            }

            // Notify marketing group members (never blocks lead saving).
            $marketingNotified = false;
            try {
                $marketingNotified = $this->notify_marketing_group_lead($leadId, $row, $responseJson, $wasUpdate);
            } catch (\Throwable $e) {
                log_message('error', 'Webshop_api_model::submit_contact_lead marketing email failed — ' . $e->getMessage());
            }

            return [
                'status'             => 'SUCCESS',
                'msg'                => 'Lead saved successfully.',
                'lead_id'            => $leadId,
                'lead_action'        => $wasUpdate ? 'updated' : 'created',
                'marketing_notified' => $marketingNotified,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Webshop_api_model::submit_contact_lead — ' . $e->getMessage());
            return ['status' => 'ERROR', 'msg' => 'Could not save lead.'];
        }
    }

    /**
     * Email all active members of the marketing user group when a webshop
     * form lead is created or updated.
     *
     * @param int    $lead_id
     * @param array  $lead_row     Data saved into the leads table.
     * @param string $response_json Raw form response JSON (may be '').
     * @param bool   $was_update   TRUE when an existing lead was modified.
     * @return bool  TRUE when at least one email was sent.
     */
    public function notify_marketing_group_lead($lead_id, array $lead_row, $response_json = '', $was_update = false)
    {
        $recipients = $this->get_marketing_group_emails();
        if (empty($recipients)) {
            log_message('debug', 'notify_marketing_group_lead: no active marketing group users with email, skipping.');
            return false;
        }

        $settings = $this->get_email_settings();
        $site_name = ($settings && isset($settings->site_name) && trim((string) $settings->site_name) !== '')
            ? trim((string) $settings->site_name)
            : 'Webshop';

        $name = isset($lead_row['full_name']) ? trim((string) $lead_row['full_name']) : '';
        $source = isset($lead_row['source']) ? trim((string) $lead_row['source']) : '';

        $subject = ($was_update ? 'Lead Updated' : 'New Lead Received')
            . ' — ' . ($name !== '' ? $name : 'Unknown')
            . ($source !== '' ? ' (' . $source . ')' : '')
            . ' | ' . $site_name;

        $message = $this->build_lead_notification_html($lead_id, $lead_row, $response_json, $was_update, $site_name);

        return $this->send_notification_email($recipients, $subject, $message, $settings);
    }

    /**
     * Active users belonging to the marketing group ("marketing" / "markeing").
     *
     * @return array<int,string> email addresses
     */
    private function get_marketing_group_emails()
    {
        if (!$this->db->table_exists('sma_users') || !$this->db->table_exists('sma_groups')) {
            return array();
        }
        $rows = $this->db->query(
            "SELECT u.email
             FROM sma_users u
             JOIN sma_groups g ON g.id = u.group_id
             WHERE u.active = 1
               AND u.email IS NOT NULL AND u.email != ''
               AND (LOWER(g.name) LIKE '%market%' OR LOWER(g.name) LIKE '%markeing%')"
        )->result_array();

        $emails = array();
        foreach ($rows as $r) {
            $email = strtolower(trim((string) $r['email']));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $emails, true)) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    /**
     * HTML body for the marketing lead notification email.
     *
     * @param int    $lead_id
     * @param array  $lead_row
     * @param string $response_json
     * @param bool   $was_update
     * @param string $site_name
     * @return string
     */
    private function build_lead_notification_html($lead_id, array $lead_row, $response_json, $was_update, $site_name)
    {
        $esc = function ($v) {
            return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        };

        $headline = $was_update ? 'Lead Updated' : 'New Lead Received';
        $intro = $was_update
            ? 'An existing lead has just been updated through a webshop form. The latest details are below.'
            : 'A new lead has just come in through a webshop form. The details are below.';

        $details = array(
            'Lead ID'   => '#' . (int) $lead_id,
            'Full Name' => isset($lead_row['full_name']) ? $lead_row['full_name'] : '',
            'Phone'     => isset($lead_row['mobile']) ? $lead_row['mobile'] : '',
            'Email'     => isset($lead_row['email']) ? $lead_row['email'] : '',
            'Message'   => isset($lead_row['comments']) ? $lead_row['comments'] : '',
            'Source'    => isset($lead_row['source']) ? $lead_row['source'] : '',
            'Type'      => isset($lead_row['type']) ? $lead_row['type'] : '',
            'Received'  => date('d M Y, h:i A'),
        );

        $rowsHtml = '';
        $i = 0;
        foreach ($details as $label => $value) {
            if (trim((string) $value) === '') {
                continue;
            }
            $bg = (++$i % 2 === 1) ? '#f8fafc' : '#ffffff';
            $rowsHtml .= '<tr style="background:' . $bg . ';">'
                . '<td style="padding:10px 14px;font-weight:bold;width:160px;border-bottom:1px solid #eef2f7;">' . $esc($label) . '</td>'
                . '<td style="padding:10px 14px;border-bottom:1px solid #eef2f7;">' . nl2br($esc($value)) . '</td>'
                . '</tr>';
        }

        // All submitted form fields (from response_json built by the webshop form).
        $fieldsHtml = '';
        if (trim((string) $response_json) !== '') {
            $decoded = json_decode((string) $response_json, true);
            if (is_array($decoded) && !empty($decoded['fields']) && is_array($decoded['fields'])) {
                $j = 0;
                foreach ($decoded['fields'] as $field) {
                    if (!is_array($field)) {
                        continue;
                    }
                    $label = isset($field['label']) && trim((string) $field['label']) !== ''
                        ? (string) $field['label']
                        : (isset($field['name']) ? (string) $field['name'] : '');
                    $value = isset($field['display_value']) && trim((string) $field['display_value']) !== ''
                        ? (string) $field['display_value']
                        : (isset($field['value']) ? (string) $field['value'] : '');
                    if ($label === '' || trim($value) === '') {
                        continue;
                    }
                    $bg = (++$j % 2 === 1) ? '#f8fafc' : '#ffffff';
                    $fieldsHtml .= '<tr style="background:' . $bg . ';">'
                        . '<td style="padding:10px 14px;font-weight:bold;width:160px;border-bottom:1px solid #eef2f7;">' . $esc($label) . '</td>'
                        . '<td style="padding:10px 14px;border-bottom:1px solid #eef2f7;">' . nl2br($esc($value)) . '</td>'
                        . '</tr>';
                }
            }
        }

        $leads_link = function_exists('base_url') ? base_url('Leads') : '';

        return '<!DOCTYPE html><html><body style="font-family:Arial,Helvetica,sans-serif;color:#333;margin:0;padding:0;background:#f1f5f9;">'
            . '<div style="max-width:620px;margin:30px auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">'
            . '<div style="background:#2c3e50;padding:22px 30px;text-align:center;">'
            . '<h2 style="color:#fff;margin:0;font-size:20px;">' . $esc($headline) . '</h2>'
            . '<p style="color:#cbd5e1;margin:6px 0 0;font-size:13px;">' . $esc($site_name) . ' — Lead Notification</p>'
            . '</div>'
            . '<div style="padding:26px 30px;">'
            . '<p style="margin:0 0 16px;font-size:14px;line-height:1.6;">Hello Marketing Team,</p>'
            . '<p style="margin:0 0 18px;font-size:14px;line-height:1.6;">' . $esc($intro) . '</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid #eef2f7;">'
            . $rowsHtml
            . '</table>'
            . ($fieldsHtml !== ''
                ? '<h4 style="color:#2c3e50;margin:22px 0 8px;font-size:14px;">Form Responses</h4>'
                  . '<table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid #eef2f7;">' . $fieldsHtml . '</table>'
                : '')
            . ($leads_link !== ''
                ? '<p style="margin:22px 0 0;"><a href="' . $esc($leads_link) . '" '
                  . 'style="display:inline-block;background:#2c3e50;color:#fff;text-decoration:none;padding:10px 22px;border-radius:5px;font-size:13px;">View Lead in CRM</a></p>'
                : '')
            . '<p style="margin:22px 0 0;font-size:12px;color:#94a3b8;line-height:1.5;">Please follow up with this lead as soon as possible. '
            . 'This is an automated notification — do not reply to this email.</p>'
            . '</div>'
            . '<div style="background:#f8fafc;padding:12px 30px;font-size:11px;color:#94a3b8;text-align:center;border-top:1px solid #eef2f7;">'
            . '&copy; ' . date('Y') . ' ' . $esc($site_name)
            . '</div>'
            . '</div></body></html>';
    }

    /**
     * Email settings row (prefers the controller-loaded Settings object).
     *
     * @return object|null
     */
    private function get_email_settings()
    {
        $CI =& get_instance();
        if (isset($CI->Settings) && is_object($CI->Settings)) {
            return $CI->Settings;
        }
        if ($this->db->table_exists('sma_settings')) {
            return $this->db->get('sma_settings')->row();
        }
        return null;
    }

    /**
     * Send an HTML email via the CI email library using sma_settings SMTP config.
     *
     * @param array<int,string> $recipients
     * @param string $subject
     * @param string $message
     * @param object|null $settings
     * @return bool
     */
    private function send_notification_email(array $recipients, $subject, $message, $settings = null)
    {
        if (empty($recipients)) {
            return false;
        }

        $CI =& get_instance();
        $s = $settings !== null ? $settings : $this->get_email_settings();

        $protocol   = ($s && isset($s->protocol) && $s->protocol !== '') ? $s->protocol : 'mail';
        $from_email = ($s && isset($s->default_email) && $s->default_email !== '') ? $s->default_email : 'no-reply@localhost';
        $from_name  = ($s && isset($s->site_name)) ? $s->site_name : 'Store';

        $cfg = array(
            'useragent' => 'ElintOm CRM',
            'protocol'  => $protocol,
            'mailtype'  => 'html',
            'crlf'      => "\r\n",
            'newline'   => "\r\n",
        );

        if ($protocol === 'sendmail') {
            $cfg['mailpath'] = ($s && isset($s->mailpath)) ? $s->mailpath : '/usr/sbin/sendmail';
        } elseif ($protocol === 'smtp') {
            $cfg['smtp_host'] = ($s && isset($s->smtp_host)) ? $s->smtp_host : '';
            $cfg['smtp_user'] = ($s && isset($s->smtp_user)) ? $s->smtp_user : '';
            if ($s && !empty($s->smtp_pass)) {
                try {
                    $CI->load->library('encrypt');
                    $cfg['smtp_pass'] = $CI->encrypt->decode($s->smtp_pass);
                } catch (\Throwable $ex) {
                    $cfg['smtp_pass'] = $s->smtp_pass;
                }
            }
            $cfg['smtp_port'] = ($s && isset($s->smtp_port)) ? (int) $s->smtp_port : 25;
            if ($s && !empty($s->smtp_crypto)) {
                $cfg['smtp_crypto'] = $s->smtp_crypto;
            } elseif ($cfg['smtp_port'] == 465) {
                $cfg['smtp_crypto'] = 'ssl';
            } elseif ($cfg['smtp_port'] == 587) {
                $cfg['smtp_crypto'] = 'tls';
            }
        }

        try {
            $CI->load->library('email');
            $CI->email->initialize($cfg);
            $CI->email->set_newline("\r\n");
            $CI->email->clear(true);
            $CI->email->from($from_email, $from_name);
            $CI->email->to($recipients);
            $CI->email->subject($subject);
            $CI->email->message($message);
            $sent = (bool) $CI->email->send(false);
            if (!$sent) {
                log_message('error', 'send_notification_email: send() returned false | ' . $CI->email->print_debugger(array('headers')));
            }
            return $sent;
        } catch (\Throwable $e) {
            log_message('error', 'send_notification_email exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure sma_cms_newsletter_subscriber exists.
     *
     * @return bool
     */
    public function ensure_newsletter_subscriber_table()
    {
        if ($this->db->table_exists('sma_cms_newsletter_subscriber')) {
            return true;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `sma_cms_newsletter_subscriber` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(255) NOT NULL,
            `source` VARCHAR(128) NOT NULL DEFAULT 'footer_newsletter',
            `status` VARCHAR(32) NOT NULL DEFAULT 'active',
            `ip_address` VARCHAR(45) NULL DEFAULT NULL,
            `user_agent` VARCHAR(512) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_newsletter_email` (`email`),
            KEY `idx_newsletter_status` (`status`),
            KEY `idx_newsletter_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return (bool) $this->db->query($sql);
    }

    /**
     * Save footer / webshop newsletter signup.
     *
     * @param array $data email, source (optional), ip_address, user_agent
     * @return array{status:string,msg?:string,subscriber_id?:int,already_subscribed?:bool}
     */
    public function submit_newsletter_subscriber(array $data)
    {
        $email = isset($data['email']) ? strtolower(trim((string) $data['email'])) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array('status' => 'ERROR', 'msg' => 'Please enter a valid email address.');
        }

        if (!$this->ensure_newsletter_subscriber_table()) {
            return array('status' => 'ERROR', 'msg' => 'Newsletter table could not be created.');
        }

        $source = isset($data['source']) ? trim((string) $data['source']) : '';
        if ($source === '') {
            $source = 'footer_newsletter';
        }
        $source = substr($source, 0, 128);

        $ip = isset($data['ip_address']) ? trim((string) $data['ip_address']) : '';
        if ($ip === '' && isset($_SERVER['REMOTE_ADDR'])) {
            $ip = trim((string) $_SERVER['REMOTE_ADDR']);
        }
        $ip = substr($ip, 0, 45);

        $ua = isset($data['user_agent']) ? trim((string) $data['user_agent']) : '';
        if ($ua === '' && isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = trim((string) $_SERVER['HTTP_USER_AGENT']);
        }
        $ua = substr($ua, 0, 512);

        $now = date('Y-m-d H:i:s');

        $existing = $this->db
            ->select('id, status')
            ->from('sma_cms_newsletter_subscriber')
            ->where('email', $email)
            ->limit(1)
            ->get()
            ->row_array();

        if (is_array($existing) && !empty($existing['id'])) {
            $update = array(
                'status'     => 'active',
                'source'     => $source,
                'updated_at' => $now,
            );
            if ($ip !== '') {
                $update['ip_address'] = $ip;
            }
            if ($ua !== '') {
                $update['user_agent'] = $ua;
            }
            $this->db->where('id', (int) $existing['id'])->update('sma_cms_newsletter_subscriber', $update);
            return array(
                'status'              => 'SUCCESS',
                'msg'                 => 'You are already subscribed. Thank you!',
                'subscriber_id'       => (int) $existing['id'],
                'already_subscribed'  => true,
            );
        }

        $row = array(
            'email'      => $email,
            'source'     => $source,
            'status'     => 'active',
            'ip_address' => $ip !== '' ? $ip : null,
            'user_agent' => $ua !== '' ? $ua : null,
            'created_at' => $now,
            'updated_at' => $now,
        );

        $allowed = array();
        $meta = $this->db->field_data('sma_cms_newsletter_subscriber');
        if (is_array($meta)) {
            foreach ($meta as $m) {
                if (isset($m->name)) {
                    $allowed[(string) $m->name] = true;
                }
            }
        }
        if (!empty($allowed)) {
            $row = array_intersect_key($row, $allowed);
        }

        $oldDbDebug = isset($this->db->db_debug) ? $this->db->db_debug : false;
        $this->db->db_debug = false;
        $ok = (bool) $this->db->insert('sma_cms_newsletter_subscriber', $row);
        $dbErr = method_exists($this->db, 'error') ? $this->db->error() : array();
        $this->db->db_debug = $oldDbDebug;

        if (!$ok) {
            $msg = 'Could not save subscription.';
            if (is_array($dbErr) && !empty($dbErr['message'])) {
                $msg = (string) $dbErr['message'];
            }
            return array('status' => 'ERROR', 'msg' => $msg);
        }

        return array(
            'status'        => 'SUCCESS',
            'msg'           => 'Thank you for subscribing!',
            'subscriber_id' => (int) $this->db->insert_id(),
        );
    }

    /**
     * Ensure sma_leads.response_json can store full CMS contact form payloads.
     *
     * @param string $table
     * @return void
     */
    /**
     * Resolve sma_leads_status.id by status name (case-insensitive).
     *
     * @param string $name
     * @return int|null
     */
    protected function resolve_leads_status_id_by_name($name)
    {
        $name = strtolower(trim((string) $name));
        if ($name === '') {
            return null;
        }

        static $cache = array();
        if (isset($cache[$name])) {
            return $cache[$name];
        }

        $statusTable = $this->db->table_exists('sma_leads_status')
            ? 'sma_leads_status'
            : ($this->db->table_exists('leads_status') ? 'leads_status' : '');
        if ($statusTable === '') {
            $cache[$name] = null;
            return null;
        }

        $row = $this->db
            ->select('id')
            ->from($statusTable)
            ->where('LOWER(name) =', $name)
            ->limit(1)
            ->get()
            ->row();
        $cache[$name] = $row ? (int) $row->id : null;
        return $cache[$name];
    }

    protected function ensure_leads_response_json_capacity($table)
    {
        static $checked = array();
        if (isset($checked[$table])) {
            return;
        }
        $checked[$table] = true;

        if (!$this->db->field_exists('response_json', $table)) {
            $this->db->query('ALTER TABLE `' . $table . '` ADD `response_json` MEDIUMTEXT NULL');
            return;
        }

        $q = $this->db->query('SHOW COLUMNS FROM `' . $table . '` LIKE \'response_json\'');
        if (!$q || $q->num_rows() === 0) {
            return;
        }
        $col = $q->row_array();
        $type = isset($col['Type']) ? strtolower((string) $col['Type']) : '';
        if (strpos($type, 'varchar') !== false || (strpos($type, 'char') !== false && strpos($type, 'text') === false)) {
            $this->db->query('ALTER TABLE `' . $table . '` MODIFY `response_json` MEDIUMTEXT NULL');
        }
    }

    /* ================================================================
     * PRIVATE HELPERS  (customer auth used by login_customer)
     * ================================================================ */

    /**
     * Authenticate customer by phone/email + plain-text password (md5 hashed).
     * Proxied through webshop_model if that method exists, otherwise inline.
     */
    public function login_customer_direct($login, $password_plain) {
        $pw = md5($password_plain);
        $q = $this->db
            ->group_start()
                ->where('phone', $login)
                ->or_where('email', $login)
            ->group_end()
            ->where('password', $pw)
            ->where('group_id', 3)
            ->get('companies');

        if ($q->num_rows() > 0) {
            return $q->row_array();
        }
        return false;
    }

    /**
     * Deliver a forgot-password OTP to the customer through every available
     * channel (WhatsApp → SMS → Email). Storefront generates and stores the
     * OTP itself; this method only sends. Returns per-channel delivery flags
     * so the caller can show "OTP sent via WhatsApp & SMS" feedback.
     *
     * @param string $phone Mobile number (digits, country code optional)
     * @param string $otp   6-digit OTP string (kept opaque — caller decides format)
     * @return array        ['status' => SUCCESS|ERROR, 'delivered' => [...], 'msg' => ...]
     */
    public function send_password_otp($phone, $otp) {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);
        $otp   = trim((string) $otp);
        if ($phone === '' || $otp === '') {
            return ['status' => 'ERROR', 'msg' => 'phone and otp required'];
        }

        // Customer must exist before we send anything — prevents abusing the API
        // as an SMS/WhatsApp gateway against arbitrary numbers. Defensive against
        // unexpected DB issues — return a clean ERROR instead of a 500.
        try {
            $customer = $this->webshop_model->get_customer(['phone' => $phone]);
        } catch (Exception $e) {
            log_message('error', 'send_password_otp: get_customer failed: ' . $e->getMessage());
            return ['status' => 'ERROR', 'msg' => 'Account lookup failed. Please try again.'];
        }
        if (!$customer) {
            return ['status' => 'ERROR', 'msg' => 'No account found for this mobile number.'];
        }

        $this->load->library('sma');

        $delivered = ['whatsapp' => false, 'sms' => false, 'email' => false];
        $errors    = [];
        $CI = function_exists('get_instance') ? get_instance() : null;
        $settings = ($CI && isset($CI->Settings) && is_object($CI->Settings)) ? $CI->Settings : null;

        // 1) WhatsApp (direct text via Cheerio).
        try {
            $this->load->helper('cheerio_whatsapp');
            $this->load->model('Whatsapp_model');
            $wa = $this->Whatsapp_model->send_otp_by_whatsapp($phone, $otp);
            if (cheerio_whatsapp_delivery_ok($wa)) {
                $delivered['whatsapp'] = true;
            } else {
                $errors['whatsapp'] = isset($wa['message']) ? $wa['message'] : 'WhatsApp send failed';
            }
        } catch (Exception $e) {
            $errors['whatsapp'] = $e->getMessage();
        }

        // 2) SMS (msg91 via existing ESHOP_SIGNUP_OTP DLT template).
        try {
            $brand = ($settings && !empty($settings->sms_brand_name))
                ? (string) $settings->sms_brand_name
                : (($settings && !empty($settings->site_name)) ? (string) $settings->site_name : 'Webshop');
            $sms_phone = ltrim($phone, '+');
            $msg = 'OTP for password reset is ' . $otp . ' for single use ' . $brand . '.';
            $sms_res = $this->sma->SendSMS($sms_phone, $msg, 'ESHOP_SIGNUP_OTP');
            $sms_decoded = is_string($sms_res) ? json_decode($sms_res, true) : $sms_res;
            if (is_array($sms_decoded) && isset($sms_decoded['type']) && strtolower((string) $sms_decoded['type']) === 'success') {
                $delivered['sms'] = true;
            } elseif (is_string($sms_res) && stripos($sms_res, 'success') !== false) {
                $delivered['sms'] = true;
            } else {
                $errors['sms'] = is_array($sms_decoded) && isset($sms_decoded['message']) ? $sms_decoded['message'] : 'SMS send failed';
            }
        } catch (Exception $e) {
            $errors['sms'] = $e->getMessage();
        }

        // 3) Email (best-effort — only if customer has a valid email).
        $email = '';
        if (is_array($customer) && !empty($customer['email'])) {
            $email = trim((string) $customer['email']);
        } elseif (is_object($customer) && !empty($customer->email)) {
            $email = trim((string) $customer->email);
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $brand = ($settings && !empty($settings->site_name)) ? (string) $settings->site_name : 'Webshop';
                $subject = 'Your password reset OTP - ' . $brand;
                $body = '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.6;color:#1f2937">'
                      . '<p>Hi,</p>'
                      . '<p>Your one-time password (OTP) to reset your account password is:</p>'
                      . '<p style="font-size:24px;font-weight:700;letter-spacing:4px;color:#0F4C81;margin:18px 0">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</p>'
                      . '<p>This OTP is valid for 10 minutes and can be used only once. Please do not share it with anyone.</p>'
                      . '<p>If you did not request this, you can safely ignore this email.</p>'
                      . '<p style="margin-top:24px">Regards,<br>' . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '</p>'
                      . '</div>';
                if ($this->sma->send_email($email, $subject, $body)) {
                    $delivered['email'] = true;
                }
            } catch (Exception $e) {
                $errors['email'] = $e->getMessage();
            }
        }

        $anyDelivered = $delivered['whatsapp'] || $delivered['sms'] || $delivered['email'];
        if (!$anyDelivered) {
            // Log channel-level errors for debugging without exposing them to the
            // user (they may contain internal gateway URLs, account ids, etc.).
            foreach ($errors as $channel => $why) {
                log_message('error', 'send_password_otp: ' . $channel . ' delivery failed: ' . $why);
            }
            return [
                'status' => 'ERROR',
                'msg' => 'OTP delivery failed on all channels. Please try again or contact support.',
                'delivered' => $delivered,
            ];
        }

        return [
            'status' => 'SUCCESS',
            'msg' => 'OTP sent.',
            'delivered' => $delivered,
        ];
    }

    /**
     * Update the customer's password (md5 hashed to match login_customer).
     * Caller (storefront) is responsible for verifying the OTP first.
     */
    public function reset_customer_password($phone, $new_password_plain) {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);
        $pw    = (string) $new_password_plain;
        if ($phone === '' || $pw === '') {
            return ['status' => 'ERROR', 'msg' => 'phone and new password required'];
        }
        if (strlen($pw) < 6) {
            return ['status' => 'ERROR', 'msg' => 'Password must be at least 6 characters.'];
        }

        // Only update group_id=3 (customer) rows — never staff/users.
        try {
            $this->db->where('phone', $phone)->where('group_id', 3);
            $updated = $this->db->update('companies', ['password' => md5($pw)]);
        } catch (Exception $e) {
            log_message('error', 'reset_customer_password: db update failed: ' . $e->getMessage());
            return ['status' => 'ERROR', 'msg' => 'Database error. Please try again.'];
        }

        if ($updated && $this->db->affected_rows() > 0) {
            return ['status' => 'SUCCESS', 'msg' => 'Password updated.'];
        }
        return ['status' => 'ERROR', 'msg' => 'No matching customer or password unchanged.'];
    }

    /**
     * Webshop WhatsApp — HTTP action notifywebshoporderwhatsapp (called from webshopapi).
     * Key: sma_settings.whatsapp_api_key. Phone: cheerio_whatsapp_resolve_order_phone_digits().
     * Checkout flag 'true' → direct text then elintom_webshop_order template via Whatsapp_model.
     *
     * @param int    $sale_id  orders.id
     * @param string $flag     'true' | YES | NO | Ready
     * @return array whatsapp_sent, msg
     */
    public function notify_webshop_order_whatsapp($sale_id, $flag = 'true') {
        $sale_id = (int) $sale_id;
        if ($sale_id < 1) {
            return ['status' => 'ERROR', 'msg' => 'order_id required'];
        }

        $this->load->helper('cheerio_whatsapp');
        $key = cheerio_whatsapp_api_key();
        if ($key === '') {
            log_message('info', 'notify_webshop_order_whatsapp: whatsapp_api_key empty; skip order ' . $sale_id);
            return ['status' => 'SUCCESS', 'msg' => 'WhatsApp not configured', 'whatsapp_sent' => false];
        }

        $order = $this->db->where('id', $sale_id)->get('orders')->row_array();
        if (!$order || empty($order['id'])) {
            return ['status' => 'ERROR', 'msg' => 'Order not found'];
        }

        $phone_digits = cheerio_whatsapp_resolve_order_phone_digits($order);
        if ($phone_digits === '') {
            log_message('info', 'notify_webshop_order_whatsapp: no phone for order ' . $sale_id);
            return ['status' => 'SUCCESS', 'msg' => 'No customer phone on order', 'whatsapp_sent' => false];
        }

        $this->load->model('Whatsapp_model');
        $this->Whatsapp_model->set_api_key($key);

        if ($flag === 'true' || $flag === true) {
            $res = $this->Whatsapp_model->send_webshop_order_placed_whatsapp($phone_digits, $sale_id, true);
        } else {
            $res = $this->Whatsapp_model->send_order_whatsapp_message($phone_digits, $sale_id, $flag);
        }
        $sent = cheerio_whatsapp_delivery_ok($res);

        if (!$sent) {
            cheerio_whatsapp_log_send_failure('notify_webshop_order_whatsapp: Cheerio failed for order ' . $sale_id, $res);
        }
        // Do not set eshop_order_alert_status here — footer "new order" alert counts status 0 only.
        // Staff dismiss via eshop/new_eshop_orders_alert (notify_close in footer.php).

        return [
            'status' => 'SUCCESS',
            'whatsapp_sent' => $sent,
            'msg' => cheerio_whatsapp_result_message($sent, $res),
        ];
    }

    /**
     * Base URL of the customer-facing webshop for email links.
     * Set application/config/elintom_api.php → webshop_storefront_base_url when the shop is not on this host.
     *
     * @param object $CI
     * @return string No trailing slash
     */
    private function resolve_webshop_storefront_base_url_for_email($CI)
    {
        $CI->load->helper('cheerio_whatsapp');
        return cheerio_whatsapp_storefront_base_url();
    }

    /**
     * Storefront hostname for mdata/uploads (matches webshop browser host).
     * Prefers shop_host/http_host sent by WebshopAPI on API calls.
     *
     * @param object $CI
     * @return string Safe folder name (no port)
     */
    private function resolve_shop_host_for_email($CI)
    {
        foreach (array('shop_host', 'http_host') as $key) {
            $raw = trim((string) $CI->input->post($key));
            if ($raw === '') {
                continue;
            }
            $hostOnly = strtolower(explode(':', $raw, 2)[0]);
            $hostOnly = preg_replace('/[^a-zA-Z0-9_.-]/', '', $hostOnly);
            if ($hostOnly !== '') {
                return $hostOnly;
            }
        }

        return '';
    }

    /**
     * Absolute site base for /assets/mdata/… URLs in outbound email (must be publicly reachable).
     * Priority: elintom_api webshop_email_asset_base_url → HTTP_HOST + path from base_url() → base_url().
     *
     * @param object $CI
     * @return string No trailing slash
     */
    private function resolve_webshop_email_public_asset_base_url($CI)
    {
        static $config_loaded = false;
        if (!$config_loaded) {
            $CI->load->config('elintom_api', false, true);
            $config_loaded = true;
        }
        $override = $CI->config->item('webshop_email_asset_base_url', 'elintom_api');
        if (is_string($override)) {
            $o = rtrim(trim($override), '/');
            if ($o !== '') {
                return $o;
            }
        }

        $cfgUrl = rtrim((string) $CI->config->item('base_url'), '/');
        if ($cfgUrl !== '' && $this->resolve_shop_host_for_email($CI) !== '') {
            return $cfgUrl;
        }

        $host = trim((string) $CI->input->server('HTTP_HOST'));
        if ($host !== '' && !is_cli()) {
            $cfgUrl = rtrim((string) $CI->config->item('base_url'), '/');
            $p = @parse_url($cfgUrl);
            $scheme = 'http';
            if (!empty($p['scheme'])) {
                $scheme = strtolower((string) $p['scheme']);
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $xf = strtolower(trim((string) $_SERVER['HTTP_X_FORWARDED_PROTO']));
                if ($xf === 'https' || $xf === 'http') {
                    $scheme = $xf;
                }
            } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                $scheme = 'https';
            }
            $path = '';
            if (!empty($p['path'])) {
                $path = rtrim((string) $p['path'], '/');
            }

            return rtrim($scheme . '://' . $host . $path, '/');
        }

        return rtrim((string) $CI->config->item('base_url'), '/');
    }

    /**
     * mdata folder name under assets/mdata/{folder}/uploads — matches MY_Controller / storefront.
     *
     * @param object $CI
     * @return string
     */
    private function resolve_customer_assets_folder_for_email($CI)
    {
        $from_api = $this->resolve_shop_host_for_email($CI);
        if ($from_api !== '') {
            return $from_api;
        }
        if (isset($CI->Customer_assets) && trim((string) $CI->Customer_assets) !== '') {
            return trim((string) $CI->Customer_assets);
        }
        if (isset($CI->Customer_url) && trim((string) $CI->Customer_url) !== '') {
            return trim((string) $CI->Customer_url);
        }
        $host = trim((string) $CI->input->server('HTTP_HOST'));
        if ($host !== '') {
            $hostOnly = strtolower(explode(':', $host, 2)[0]);
            $hostOnly = preg_replace('/[^a-zA-Z0-9_.-]/', '', $hostOnly);

            return $hostOnly !== '' ? $hostOnly : 'localhost';
        }

        return 'localhost';
    }

    /**
     * Header logo for order email — same source as webshop header: merged
     * sma_website_setting + sma_cms_webshop_header_footer (Storefront identity logo_image).
     *
     * @param object $CI
     * @param string $site_name_for_alt
     * @return string HTML snippet or empty string
     */
    private function resolve_webshop_order_email_logo_block($CI, $site_name_for_alt)
    {
        $this->load->model('webshop_settings_model');
        $legacy = array();
        $wq = $this->db->get('website_setting');
        if ($wq && $wq->num_rows() > 0) {
            $legacy = $wq->result();
        }
        $merged = $this->webshop_settings_model->merge_website_setting_for_api($legacy);
        $path = '';
        foreach ($merged as $obj) {
            $row = is_object($obj) ? $obj : (object) (array) $obj;
            $f = isset($row->fields) ? strtolower(trim((string) $row->fields)) : '';
            if ($f !== 'logo_image' || !isset($row->value) || trim((string) $row->value) === '') {
                continue;
            }
            $val = trim((string) $row->value);
            $sec = isset($row->section_type) ? strtolower(trim((string) $row->section_type)) : '';
            if ($sec === 'header') {
                $path = $val;
                break;
            }
            if ($path === '') {
                $path = $val;
            }
        }
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            $abs = $path;
        } else {
            $base = $this->resolve_webshop_email_public_asset_base_url($CI);
            $tail = ltrim(str_replace('\\', '/', $path), '/');
            if (stripos($tail, 'assets/') === 0) {
                $abs = $base . '/' . $tail;
            } else {
                $mdata = $this->resolve_customer_assets_folder_for_email($CI);
                $abs = $base . '/assets/mdata/' . rawurlencode($mdata) . '/uploads/' . $tail;
            }
        }
        $alt = htmlspecialchars($site_name_for_alt, ENT_QUOTES, 'UTF-8');
        $src = htmlspecialchars($abs, ENT_QUOTES, 'UTF-8');

        return '<div style="text-align:center;width:100%;margin:0 0 12px 0;">'
            . '<span style="display:inline-block;background:#fff;border-radius:6px;padding:8px 14px;line-height:0;">'
            . '<img src="' . $src . '" alt="' . $alt . '" style="max-height:52px;max-width:260px;height:auto;width:auto;display:block;margin:0 auto;border:0;">'
            . '</span></div>';
    }

    /**
     * Footer credit line for webshop order emails (matches storefront footer).
     *
     * @return string
     */
    private function webshop_order_email_powered_by_footer_html()
    {
        return '<span style="font-size:12px;line-height:1.5;color:#888;">Powered by '
            . '<a href="https://elintom.io/" style="color:#666;text-decoration:underline;font-weight:600;" '
            . 'target="_blank" rel="noopener noreferrer">ElintOm</a></span>';
    }

    /**
     * Send a post-order confirmation email for a webshop sale.
     *
     * Reads the sale from sma_orders, resolves the customer email from
     * sma_addresses (billing) → sma_companies fallback, builds HTML
     * (theme file under themes/&lt;theme&gt;/views/email_templates/ if present),
     * then dispatches via CI Email (SMTP / mail / sendmail from settings).
     *
     * @param int $sale_id  sma_orders.id
     * @return array        { status: SUCCESS|ERROR, email_sent: bool, ... }
     */
    public function notify_webshop_order_email($sale_id) {
        $sale_id = (int) $sale_id;
        if ($sale_id < 1) {
            return ['status' => 'ERROR', 'msg' => 'order_id required'];
        }

        $CI = get_instance();
        $fmtMoney = function ($amount) use ($CI) {
            if ($amount === '' || $amount === null) {
                return '';
            }
            if (!is_numeric($amount)) {
                return (string) $amount;
            }
            if (isset($CI->sma) && is_object($CI->sma) && method_exists($CI->sma, 'formatMoney')) {
                return $CI->sma->formatMoney((float) $amount);
            }
            return number_format((float) $amount, 2, '.', ',');
        };

        // ── 1. Load the order ───────────────────────────────────────────────
        $order = $this->db->where('id', $sale_id)->get('orders')->row_array();
        if (!$order || empty($order['id'])) {
            return ['status' => 'ERROR', 'msg' => 'Order not found'];
        }

        $payment_status = isset($order['payment_status']) ? trim((string) $order['payment_status']) : '';
        $payment_method = isset($order['payment_method']) ? trim((string) $order['payment_method']) : '';
        $sale_status = isset($order['sale_status']) ? trim((string) $order['sale_status']) : '';
        $invoice_no = isset($order['invoice_no']) ? trim((string) $order['invoice_no']) : '';
        $order_total = isset($order['total']) ? $order['total'] : '';
        $shipping_fee = isset($order['shipping']) ? $order['shipping'] : '';
        $amount_paid = isset($order['paid']) ? $order['paid'] : '';
        $grand_total_raw = isset($order['grand_total']) ? $order['grand_total'] : '';

        $payment_status_label = $payment_status !== ''
            ? ucwords(str_replace(array('_', '-'), ' ', $payment_status))
            : '—';
        $payment_method_label = $payment_method !== '' ? $payment_method : '—';
        $sale_status_label = $sale_status !== ''
            ? ucwords(str_replace(array('_', '-'), ' ', $sale_status))
            : '—';

        // ── 2. Resolve customer email + display name ─────────────────────────
        $to_email = '';
        $to_name = '';
        $company_display = '';

        $addr_id = !empty($order['billing_address_id']) ? (int) $order['billing_address_id']
            : (!empty($order['shipping_address_id']) ? (int) $order['shipping_address_id'] : 0);

        if ($addr_id > 0) {
            $addr = $this->db->select('email_id, phone, company_name, address_name')
                ->where('id', $addr_id)
                ->get('addresses')
                ->row();
            if ($addr) {
                if (isset($addr->email_id) && trim((string) $addr->email_id) !== '') {
                    $to_email = trim((string) $addr->email_id);
                } elseif (isset($addr->email) && trim((string) $addr->email) !== '') {
                    $to_email = trim((string) $addr->email);
                }
                if (isset($addr->address_name) && trim((string) $addr->address_name) !== '') {
                    $to_name = trim((string) $addr->address_name);
                }
                if ($to_name === '' && isset($addr->company_name)) {
                    $to_name = trim((string) $addr->company_name);
                }
                if (isset($addr->company_name)) {
                    $company_display = trim((string) $addr->company_name);
                }
            }
        }

        if (!empty($order['customer_id'])) {
            $cust = $this->db->select('email, name, company')
                ->where('id', (int) $order['customer_id'])
                ->get('companies')
                ->row();
            if ($cust) {
                if ($to_email === '' && isset($cust->email)) {
                    $to_email = trim((string) $cust->email);
                }
                if ($to_name === '' && isset($cust->name) && trim((string) $cust->name) !== '') {
                    $to_name = trim((string) $cust->name);
                }
                if ($company_display === '' && isset($cust->company) && trim((string) $cust->company) !== '') {
                    $company_display = trim((string) $cust->company);
                }
            }
        }

        if ($to_email === '') {
            log_message('info', 'notify_webshop_order_email: no email for order ' . $sale_id . '; skip.');
            return ['status' => 'SUCCESS', 'msg' => 'No customer email on order', 'email_sent' => false];
        }

        if ($to_name === '') {
            $to_name = $company_display !== '' ? $company_display : 'Customer';
        }

        // ── 3. Build order items summary (product name + optional variant) ───
        $items = array();
        if ($this->db->table_exists('order_items')) {
            $this->db->from('order_items oi');
            $this->db->select('oi.*', false);
            if ($this->db->table_exists('products')) {
                $this->db->select('p.name AS product_display_name, p.code AS product_display_code', false);
                $this->db->join('products p', 'p.id = oi.product_id', 'left');
            }
            if ($this->db->table_exists('product_variants')) {
                $this->db->select('pv.name AS variant_option_name', false);
                $this->db->join('product_variants pv', 'pv.id = oi.option_id', 'left');
            }
            $this->db->where('oi.sale_id', $sale_id);
            $iq = $this->db->get();
            $items = ($iq && $iq->num_rows() > 0) ? $iq->result_array() : array();
        }

        $items_html = '';
        $line_sum = 0;
        foreach ($items as $idx => $it) {
            $pname = '';
            foreach (array('product_name', 'name', 'product_display_name') as $nk) {
                if (!empty($it[$nk])) {
                    $pname = (string) $it[$nk];
                    break;
                }
            }
            if ($pname === '' && !empty($it['product_code'])) {
                $pname = (string) $it['product_code'];
            }
            if ($pname === '' && !empty($it['product_display_code'])) {
                $pname = (string) $it['product_display_code'];
            }
            if ($pname === '') {
                $pname = 'Product #' . (int) (isset($it['product_id']) ? $it['product_id'] : 0);
            }
            if (!empty($it['variant_option_name'])) {
                $pname .= ' — ' . (string) $it['variant_option_name'];
            }
            $qty = isset($it['quantity']) ? $it['quantity'] : '';
            $lineAmt = '';
            $it = order_pricing_normalize_item_row($it);
            $items[$idx] = $it;
            $line_sum += isset($it['subtotal']) ? (float) $it['subtotal'] : 0;
            if (isset($it['subtotal']) && $it['subtotal'] !== '' && is_numeric($it['subtotal']) && (float) $it['subtotal'] > 0) {
                $lineAmt = $fmtMoney($it['subtotal']);
            } elseif (isset($it['net_price']) && is_numeric($it['net_price']) && (float) $it['net_price'] > 0) {
                $lineAmt = $fmtMoney($it['net_price']);
            } elseif (isset($it['unit_price'], $it['quantity']) && is_numeric($it['unit_price']) && (float) $it['unit_price'] > 0) {
                $lineAmt = $fmtMoney((float) $it['unit_price'] * (float) $it['quantity']);
            } elseif (isset($it['mrp'], $it['quantity']) && (float) $it['mrp'] > 0) {
                $lineAmt = $fmtMoney((float) $it['mrp'] * (float) $it['quantity']);
            } elseif (isset($it['unit_price'])) {
                $lineAmt = $fmtMoney($it['unit_price']);
            }
            $sku = '';
            if (isset($it['product_code']) && trim((string) $it['product_code']) !== '') {
                $sku = (string) $it['product_code'];
            } elseif (!empty($it['product_display_code'])) {
                $sku = (string) $it['product_display_code'];
            }
            $name_cell = htmlspecialchars($pname, ENT_QUOTES, 'UTF-8');
            if ($sku !== '') {
                $name_cell .= '<br><span style="font-size:12px;color:#666;">SKU: ' . htmlspecialchars($sku, ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $items_html .= '<tr>'
                . '<td style="padding:6px 8px;border-bottom:1px solid #eee;">' . $name_cell . '</td>'
                . '<td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center;">' . htmlspecialchars((string) $qty, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:right;">' . htmlspecialchars($lineAmt, ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>';
        }

        if ($line_sum > 0) {
            if ($order_total === '' || !is_numeric($order_total) || (float) $order_total <= 0) {
                $order_total = $line_sum;
            }
            if ($grand_total_raw === '' || !is_numeric($grand_total_raw) || (float) $grand_total_raw <= 0) {
                $tax_part = isset($order['product_tax']) && is_numeric($order['product_tax'])
                    ? (float) $order['product_tax']
                    : (is_numeric($order_tax) ? (float) $order_tax : 0);
                $grand_total_raw = $line_sum + (is_numeric($shipping_fee) ? (float) $shipping_fee : 0) + $tax_part;
            }
        }

        // ── 4. Load template or fall back to inline HTML ────────────────────
        $theme = isset($CI->Settings->theme) ? $CI->Settings->theme : 'default';
        $tpl_candidates = array(
            FCPATH . 'themes/' . $theme . '/views/email_templates/webshop_order_confirm.html',
            FCPATH . 'themes/' . $theme . '/views/email_templates/sale.html',
            FCPATH . 'themes/default/views/email_templates/webshop_order_confirm.html',
            FCPATH . 'themes/default/views/email_templates/sale.html',
        );
        $tpl_path = null;
        foreach ($tpl_candidates as $cand) {
            if (is_file($cand)) {
                $tpl_path = $cand;
                break;
            }
        }

        $site_name = isset($CI->Settings->site_name) ? $CI->Settings->site_name : 'Store';
        $storefront_base = $this->resolve_webshop_storefront_base_url_for_email($CI);
        $webshop_app = rtrim($storefront_base, '/') . '/webshop';
        $order_tracking_url = rtrim($webshop_app, '/') . '/track_order/' . md5((string) $sale_id);
        $order_tracking_url_esc = htmlspecialchars($order_tracking_url, ENT_QUOTES, 'UTF-8');
        $track_order_line = '<p style="margin:16px 0 0;">'
            . '<a href="' . $order_tracking_url_esc . '" '
            . 'style="display:inline-block;background:#2c3e50;color:#ffffff;text-decoration:none;'
            . 'font-size:14px;font-weight:600;padding:12px 22px;border-radius:4px;line-height:1.4;" '
            . 'target="_blank" rel="noopener noreferrer">Track your order</a></p>';
        $logo_block = $this->resolve_webshop_order_email_logo_block($CI, $site_name);
        $site_name_esc = htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8');
        $site_name_header = $logo_block !== ''
            ? ''
            : '<h1 style="margin:0;font-size:22px;color:#fff;">' . $site_name_esc . '</h1>';
        $powered_by_footer = $this->webshop_order_email_powered_by_footer_html();
        $ref_no = isset($order['reference_no']) ? (string) $order['reference_no'] : ('#' . $sale_id);
        $grand_total = ($grand_total_raw !== '' && is_numeric($grand_total_raw)) ? $grand_total_raw : (isset($order['grand_total']) ? $order['grand_total'] : '');
        $currency = isset($CI->Settings->default_currency) ? (string) $CI->Settings->default_currency : '';
        $order_date = isset($order['date']) ? date('d M Y', strtotime($order['date'])) : date('d M Y');
        $grand_total_fmt = trim($fmtMoney($grand_total) . ($currency !== '' ? ' ' . $currency : ''));

        $parser_data = array(
            'contact_person' => $to_name,
            'customer_name' => $to_name,
            'company' => $company_display !== '' ? $company_display : $site_name,
            'reference_number' => $ref_no,
            'order_no' => $ref_no,
            'invoice_no' => $invoice_no !== '' ? $invoice_no : $ref_no,
            'grand_total' => $grand_total_fmt,
            'site_name' => $site_name,
            'site_link' => $webshop_app,
            'client_link' => $webshop_app,
            'order_tracking_url' => $order_tracking_url,
            'track_order_line' => $track_order_line,
            'logo_block' => $logo_block,
            'order_date' => $order_date,
            'payment_status' => $payment_status_label,
            'payment_method' => $payment_method_label,
            'sale_status' => $sale_status_label,
            'order_total' => $fmtMoney($order_total),
            'shipping' => $fmtMoney($shipping_fee),
            'amount_paid' => $fmtMoney($amount_paid),
            'items_table' => $items_html,
            'logo' => '',
            'site_name_header' => $site_name_header,
            'powered_by_footer' => $powered_by_footer,
            'year' => date('Y'),
        );

        if ($tpl_path !== null) {
            $CI->load->library('parser');
            $tpl_raw = file_get_contents($tpl_path);
            $message = $CI->parser->parse_string($tpl_raw, $parser_data);
        } else {
            $inv_show = $invoice_no !== '' ? htmlspecialchars($invoice_no, ENT_QUOTES, 'UTF-8') : htmlspecialchars($ref_no, ENT_QUOTES, 'UTF-8');
            $message = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;color:#333;margin:0;padding:0;">'
                . '<div style="max-width:600px;margin:30px auto;border:1px solid #ddd;border-radius:6px;overflow:hidden;">'
                . '<div style="background:#2c3e50;padding:24px 32px;text-align:center;">'
                . $logo_block
                . $site_name_header
                . '<p style="color:#fff;margin:8px 0 0;font-size:14px;opacity:.95;">Order confirmation</p>'
                . '</div>'
                . '<div style="padding:28px 32px;">'
                . '<h3 style="color:#2c3e50;">Order Confirmed ✓</h3>'
                . '<p>Dear ' . htmlspecialchars($to_name, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p>Thank you for your order. We have received it and it is being processed.</p>'
                . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                . '<tr style="background:#f5f5f5;">'
                . '<td style="padding:8px;font-weight:bold;">Order reference</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($ref_no, ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>'
                . '<tr><td style="padding:8px;font-weight:bold;">Order / invoice no.</td>'
                . '<td style="padding:8px;">' . $inv_show . '</td></tr>'
                . '<tr style="background:#f5f5f5;"><td style="padding:8px;font-weight:bold;">Date</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($order_date, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:8px;font-weight:bold;">Payment method</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($payment_method_label, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr style="background:#f5f5f5;"><td style="padding:8px;font-weight:bold;">Payment status</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($payment_status_label, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:8px;font-weight:bold;">Order status</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($sale_status_label, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr style="background:#f5f5f5;"><td style="padding:8px;font-weight:bold;">Items total</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($fmtMoney($order_total), ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:8px;font-weight:bold;">Shipping</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($fmtMoney($shipping_fee), ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr style="background:#f5f5f5;"><td style="padding:8px;font-weight:bold;">Amount paid</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($fmtMoney($amount_paid), ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:8px;font-weight:bold;">Grand total</td>'
                . '<td style="padding:8px;">' . htmlspecialchars($grand_total_fmt, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '</table>'
                . ($items_html !== '' ? '<h4 style="color:#2c3e50;margin-top:24px;">Items ordered</h4>'
                  . '<table style="width:100%;border-collapse:collapse;">'
                  . '<thead><tr style="background:#2c3e50;color:#fff;">'
                  . '<th style="padding:8px;text-align:left;">Product</th>'
                  . '<th style="padding:8px;text-align:center;">Qty</th>'
                  . '<th style="padding:8px;text-align:right;">Line total</th>'
                  . '</tr></thead><tbody>' . $items_html . '</tbody></table>' : '<p style="margin-top:16px;color:#666;">No line items were returned for this order in the database.</p>')
                . '<div style="margin-top:24px;">' . $track_order_line . '</div>'
                . '<div style="margin-top:20px;border-top:1px solid #eee;padding-top:20px;">'
                . '<p style="margin:0 0 12px;font-size:13px;color:#666;">Best regards,<br><strong>'
                . htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
                . '<tr><td align="right" style="padding:8px 0 0;font-size:12px;line-height:1.5;color:#888;">'
                . $powered_by_footer
                . '</td></tr></table></div>'
                . '</div>'
                . '<div style="background:#f9f9f9;padding:12px 32px;font-size:11px;color:#999;text-align:center;border-top:1px solid #eee;">'
                . '&copy; ' . date('Y') . ' ' . htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8')
                . '</div>'
                . '</div>'
                . '</body></html>';
        }

        // ── 5. Send via CI email library (direct — avoids Sma constructor side-effects) ─
        $sent = false;
        try {
            // Read SMTP/email settings from sma_settings (already in $CI->Settings from __construct)
            $s = $CI->Settings; // stdClass from site->get_setting()

            $protocol   = (isset($s->protocol)     && $s->protocol !== '') ? $s->protocol : 'mail';
            $from_email = (isset($s->default_email) && $s->default_email !== '') ? $s->default_email : 'no-reply@localhost';
            $from_name  = isset($s->site_name) ? $s->site_name : 'Store';

            $cfg = array(
                'useragent' => 'ElintOm Webshop',
                'protocol'  => $protocol,
                'mailtype'  => 'html',
                'crlf'      => "\r\n",
                'newline'   => "\r\n",
            );

            if ($protocol === 'sendmail') {
                $cfg['mailpath'] = isset($s->mailpath) ? $s->mailpath : '/usr/sbin/sendmail';
            } elseif ($protocol === 'smtp') {
                $cfg['smtp_host'] = isset($s->smtp_host) ? $s->smtp_host : '';
                $cfg['smtp_user'] = isset($s->smtp_user) ? $s->smtp_user : '';
                // Decrypt password the same way Sma does (CI3 encrypt library)
                if (!empty($s->smtp_pass)) {
                    try {
                        $CI->load->library('encrypt');
                        $cfg['smtp_pass'] = $CI->encrypt->decode($s->smtp_pass);
                    } catch (\Throwable $ex) {
                        $cfg['smtp_pass'] = $s->smtp_pass; // fallback: use as-is
                    }
                }
                $cfg['smtp_port'] = isset($s->smtp_port) ? (int) $s->smtp_port : 25;
                if (!empty($s->smtp_crypto)) {
                    $cfg['smtp_crypto'] = $s->smtp_crypto;
                } elseif ($cfg['smtp_port'] == 465) {
                    $cfg['smtp_crypto'] = 'ssl';
                } elseif ($cfg['smtp_port'] == 587) {
                    $cfg['smtp_crypto'] = 'tls';
                }
            }

            $CI->load->library('email');
            $CI->email->initialize($cfg);
            $CI->email->set_newline("\r\n");
            $CI->email->clear(true);
            $CI->email->from($from_email, $from_name);
            $CI->email->to($to_email);
            $CI->email->subject('Order Confirmation — ' . $ref_no . ' | ' . $site_name);
            $CI->email->message($message);
            $sent = (bool) $CI->email->send(false);

            if (!$sent) {
                log_message('error', 'notify_webshop_order_email: CI email send() returned false for order '
                    . $sale_id . ' | debugger: ' . $CI->email->print_debugger());
            }
        } catch (\Throwable $e) {
            log_message('error', 'notify_webshop_order_email: exception for order '
                . $sale_id . ': ' . $e->getMessage());
            return ['status' => 'ERROR', 'msg' => 'Email dispatch exception: ' . $e->getMessage(), 'email_sent' => false];
        }

        return [
            'status'     => 'SUCCESS',
            'email_sent' => $sent,
            'to'         => $to_email,
            'msg'        => $sent ? 'Order confirmation email sent.' : 'Email send failed (check SMTP settings in ElintOm → System Settings → Email).',
        ];
    }

}
