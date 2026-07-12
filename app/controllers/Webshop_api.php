<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webshop_api — dedicated REST endpoint for the WebshopAPI storefront.
 *
 * URL:  POST http://localhost/ElintOm/webshop_api/action
 * Auth: POST field  privatekey = sma_settings.api_privatekey
 *       POST field  action     = <action name>
 *
 * All actions are prefixed with nothing (the controller itself is the
 * namespace), so callers POST to /webshop_api/action with e.g.
 *   action = getsettings | getcategories | getproductbyhash | createorder …
 *
 * Response: JSON  { status: "SUCCESS"|"ERROR", … }
 *
 * Extends CI_Controller (not MY_Controller) so API requests skip the full POS
 * bootstrap — session/views/login branches can yield a fatal or empty body with echo+exit.
 */
class Webshop_api extends CI_Controller {

    /** @var Site */
    public $site;

    /** @var Webshop_api_model */
    public $webshop_api_model;

    /** @var Webshop_model|null Set when Webshop_api_model loads webshop_model */
    public $webshop_model;

    /** @var Cms_model */
    public $cms_model;

    /** @var Cms_blogs_model */
    public $cms_blogs_model;

    /** @var Cms_blog_categories_model */
    public $cms_blog_categories_model;

    /** @var Cms_testimonials_model */
    public $cms_testimonials_model;

    /** @var Cms_pages_faqs_model|null */
    public $cms_pages_faqs_model;

    /** @var Cms_entity_faqs_model|null */
    public $cms_entity_faqs_model;

    /** @var Cms_renderer */
    public $cms_renderer;

    /** @var Cms_seo */
    public $cms_seo;

    /** @var Webshop_settings_model|null Loaded lazily by Webshop_api_model */
    public $webshop_settings_model;

    /** @var Eshop_model|null Loaded lazily by Webshop_api_model */
    public $eshop_model;

    /** @var Whatsapp_model|null Loaded lazily by Webshop_api_model */
    public $Whatsapp_model;

    /** Loaded once in __construct from sma_settings.api_privatekey */
    private $private_key = '';

    /** @var object|false Loaded from DB for auth checks */
    public $Settings;

    public function __construct() {
        parent::__construct();

        $this->load->model('site');
        $this->Settings = $this->site->get_setting();
        if (!$this->Settings) {
            $this->_json(['status' => 'ERROR', 'error_code' => 500,
                'msg' => 'Could not load store settings from the database.']);
        }

        $this->load->helper('order_pricing');
        $this->load->model('webshop_api_model');
        $this->load->model('cms_model');
        $this->load->model('Cms_blogs_model', 'cms_blogs_model');
        $this->load->model('Cms_blog_categories_model', 'cms_blog_categories_model');
        $this->load->model('cms_testimonials_model');
        if (is_file(APPPATH . 'models/Cms_pages_faqs_model.php')) {
            $this->load->model('cms_pages_faqs_model');
        }
        if (is_file(APPPATH . 'models/Cms_entity_faqs_model.php')) {
            $this->load->model('cms_entity_faqs_model');
        }
        $this->load->library('cms_renderer');
        $this->load->library('cms_seo');
        // Verify API access is enabled and version >= 3
        $posRaw = isset($this->Settings->pos_version) ? $this->Settings->pos_version : '';
        $posVersion = is_string($posRaw) ? json_decode($posRaw) : null;
        $ver = 0.0;
      
        // Validate private key
        $this->private_key = isset($this->Settings->api_privatekey)
            ? (string) $this->Settings->api_privatekey : '';

        if ($this->private_key === '') {
            $this->_json(['status' => 'ERROR', 'error_code' => 100,
                'msg' => 'API private key not configured in ElintOm settings.']);
        }

        $posted_key = $this->input->post('privatekey');
        if ($this->private_key !== $posted_key) {
            $this->_json(['status' => 'ERROR', 'error_code' => 102,
                'msg' => 'Private key mismatch.', 'private_key_msg' => 'mismatch']);
        }
    }

    /** Default entry — dispatch on POST[action] */
    public function index() {
        $action = $this->input->post('action');

        switch ($action) {

            /* ── STORE ─────────────────────────────────────────────── */
            case 'getsettings':
                $this->_json($this->webshop_api_model->get_settings());
                break;

            case 'getcmspage':
                // URL path for the CMS page, e.g. "/about-us" or "about-us"
                $url_path = $this->input->post('url');
                if ($url_path === null) {
                    $this->_json([
                        'status' => 'ERROR',
                        'msg'    => 'url parameter is required for getcmspage',
                    ]);
                }

                // Normalize URL like Cmspage::index
                $url = '/' . trim($url_path, '/');
                if ($url === '//') {
                    $url = '/';
                }

                // 1) Try requested URL
                $page_data = $this->cms_model->getPageData($url);

                // 2) Fallback for direct /cmspage access
                if (!$page_data && $url !== '/cmspage') {
                    $page_data = $this->cms_model->getPageData('/cmspage');
                }

                // 3) Final fallback to home page
                if (!$page_data && $url !== '/') {
                    $page_data = $this->cms_model->getPageData('/');
                }

                // 4) Nothing found → JSON 404-style error
                if (!$page_data) {
                    $this->_json([
                        'status' => 'ERROR',
                        'msg'    => 'CMS page not found',
                        'url'    => $url,
                    ]);
                }

                // Prepare context data for SEO/meta generation
                $context = [
                    'page_title'  => $page_data['page']['page_name'],
                    'base_url'    => base_url(),
                    'site_name'   => isset($this->Settings->site_name) ? $this->Settings->site_name : 'ElintOm',
                    'current_url' => current_url(),
                ];

                // Generate SEO / Meta Tags HTML
                $meta_html = $this->cms_seo->generate_meta_tags($page_data['meta'], $context);

                $this->load->helper('cms_layout');
                $layout_data = cms_build_page_layout_sections($page_data['sections'], $this->cms_renderer);
                $page_banner_image_url = $this->buildCmsImageUrl(isset($page_data['page']['banner_image']) ? $page_data['page']['banner_image'] : '');
                $page_logo_image_url = $this->buildCmsImageUrl(isset($page_data['page']['logo_image']) ? $page_data['page']['logo_image'] : '');
                $page_header_design_profile = cms_layout_resolve_page_chrome_profile_slug(
                    isset($page_data['sections']) && is_array($page_data['sections']) ? $page_data['sections'] : array(),
                    'header'
                );
                $page_footer_design_profile = cms_layout_resolve_page_chrome_profile_slug(
                    isset($page_data['sections']) && is_array($page_data['sections']) ? $page_data['sections'] : array(),
                    'footer'
                );

                // Return both raw data and rendered HTML
                $this->_json([
                    'status'        => 'SUCCESS',
                    'page'          => $page_data['page'],
                    'sections'      => $page_data['sections'],
                    'meta_tags_raw' => $page_data['meta'],
                    'meta_tags_html'=> $meta_html,
                    'content_html'  => $layout_data['body_html'],
                    'header_html'   => $layout_data['header_html'],
                    'footer_html'   => $layout_data['footer_html'],
                    'banner_html'   => $layout_data['banner_html'],
                    'logo_html'     => $layout_data['logo_html'],
                    'show_header'   => $layout_data['show_header'],
                    'show_footer'   => $layout_data['show_footer'],
                    'page_header_design_profile' => $page_header_design_profile,
                    'page_footer_design_profile' => $page_footer_design_profile,
                    'page_banner_image_url' => $page_banner_image_url,
                    'page_logo_image_url'   => $page_logo_image_url,
                ]);
                break;

            case 'getcmspages':
                // placement: header | footer | (empty) all published pages for nav
                $placement = strtolower(trim((string) $this->input->post('placement')));
                if (!in_array($placement, array('header', 'footer'), true)) {
                    $placement = null;
                }
                $payload = $this->cms_model->getPublishedNavPayload($placement, array('static', 'category'));
                $this->_json([
                    'status'     => 'SUCCESS',
                    'pages'      => $payload['pages'],
                    'menu_tree'  => $payload['menu_tree'],
                    'placement'  => $placement === null ? 'all' : $placement,
                ]);
                break;

            case 'getblogposts':
                $page = max(1, (int) $this->input->post('page'));
                $perPage = (int) $this->input->post('per_page');
                if ($perPage < 1) {
                    $perPage = (int) $this->input->post('limit');
                }
                if ($perPage < 1) {
                    $perPage = 12;
                }
                $categoryId = (int) $this->input->post('category_id');
                if ($categoryId < 1) {
                    $categoryId = (int) $this->input->post('blog_category_id');
                }
                $list = $this->cms_blogs_model->list_published($page, $perPage, $categoryId);
                $this->_json([
                    'status'      => 'SUCCESS',
                    'posts'       => $list['items'],
                    'items'       => $list['items'],
                    'total_items' => $list['total_items'],
                    'page'        => $list['page'],
                    'per_page'    => $list['per_page'],
                    'total_pages' => $list['total_pages'],
                ]);
                break;

            case 'getblogpost':
                $id = (int) $this->input->post('id');
                $slug = trim((string) $this->input->post('slug'));
                if ($slug === '') {
                    $slug = trim((string) $this->input->post('url'));
                }
                $detail = $this->cms_blogs_model->get_published($id, $slug);
                if (!$detail) {
                    $this->_json([
                        'status' => 'ERROR',
                        'msg'    => 'Blog post not found',
                    ]);
                }
                $this->_json(array_merge(array('status' => 'SUCCESS'), $detail));
                break;

            case 'getblogcategories':
                $items = $this->cms_blog_categories_model->list_all(true);
                $this->_json([
                    'status' => 'SUCCESS',
                    'items'  => $items,
                    'categories' => $items,
                ]);
                break;

            case 'gettestimonials':
                $page = max(1, (int) $this->input->post('page'));
                $perPage = (int) $this->input->post('per_page');
                if ($perPage < 1) {
                    $perPage = (int) $this->input->post('limit');
                }
                if ($perPage < 1) {
                    $perPage = 12;
                }
                $list = $this->cms_testimonials_model->list_published($page, $perPage);
                $this->_json([
                    'status'      => 'SUCCESS',
                    'items'       => $list['items'],
                    'testimonials' => $list['items'],
                    'total_items' => $list['total_items'],
                    'page'        => $list['page'],
                    'per_page'    => $list['per_page'],
                    'total_pages' => $list['total_pages'],
                ]);
                break;

            case 'getpagefaqs':
                $page_id = (int) $this->input->post('page_id');
                if ($page_id < 1) {
                    $this->_json([
                        'status' => 'ERROR',
                        'msg'    => 'page_id parameter is required for getpagefaqs',
                    ]);
                }
                if (!$this->cms_pages_faqs_model) {
                    $this->_json([
                        'status'      => 'SUCCESS',
                        'page_id'     => $page_id,
                        'items'       => array(),
                        'faqs'        => array(),
                        'total_items' => 0,
                    ]);
                }
                $items = $this->cms_pages_faqs_model->list_by_page_id($page_id);
                $this->_json([
                    'status' => 'SUCCESS',
                    'page_id' => $page_id,
                    'items'  => $items,
                    'faqs'   => $items,
                    'total_items' => count($items),
                ]);
                break;

            case 'getnextref':
                $this->_json([
                    'status'    => 'SUCCESS',
                    'reference' => $this->site->getNextReference('eshop'),
                ]);
                break;

            /* ── CATALOGUE ──────────────────────────────────────────── */
            case 'getcategories':
                $this->_json($this->webshop_api_model->get_categories());
                break;

            case 'getsliders':
                $this->_json($this->webshop_api_model->get_sliders());
                break;

            case 'getproductslist':
                $this->_json($this->webshop_api_model->get_products_list([
                    'by'       => $this->input->post('by'),
                    'byid'     => $this->input->post('byid'),
                    'use_hash' => (bool) $this->input->post('use_hash'),
                    'limit'    => (int)  $this->input->post('limit'),
                    'page'     => max(1, (int) $this->input->post('page')),
                ]));
                break;

            case 'getproductbyhash':
                $hash = $this->input->post('product_hash');
                if (!$hash) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'product_hash required']);
                }
                $this->_json($this->webshop_api_model->get_product_by_hash($hash));
                break;
            case 'getentityfaqs':
                $entity_code = strtolower(trim((string) $this->input->post('entity_code')));
                $entity_id = (int) $this->input->post('entity_id');
                if ($entity_code === '' || $entity_id < 1) {
                    $this->_json([
                        'status' => 'ERROR',
                        'msg'    => 'entity_code and entity_id are required for getentityfaqs',
                    ]);
                }
                if (!$this->cms_entity_faqs_model) {
                    $this->_json([
                        'status'       => 'SUCCESS',
                        'entity_code'  => $entity_code,
                        'entity_id'    => $entity_id,
                        'items'        => array(),
                        'faqs'         => array(),
                        'total_items'  => 0,
                    ]);
                }
                $entity_master_id = $this->cms_entity_faqs_model->resolve_entity_master_id($entity_code);
                if ($entity_master_id < 1) {
                    $this->_json([
                        'status'       => 'SUCCESS',
                        'entity_code'  => $entity_code,
                        'entity_id'    => $entity_id,
                        'items'        => array(),
                        'faqs'         => array(),
                        'total_items'  => 0,
                    ]);
                }
                $items = $this->cms_entity_faqs_model->list_for_entity_code($entity_code, $entity_id);
                $this->_json([
                    'status'            => 'SUCCESS',
                    'entity_code'       => $entity_code,
                    'entity_id'         => $entity_id,
                    'entity_master_id'  => $entity_master_id,
                    'items'             => $items,
                    'faqs'              => $items,
                    'total_items'       => count($items),
                ]);
                break;

            case 'getentitytags':
                $entity_code = $this->input->post('entity_code');
                $entity_id = (int) $this->input->post('entity_id');
                if (!$entity_code || $entity_id <= 0) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'entity_code and entity_id are required']);
                }
                $this->_json($this->webshop_api_model->get_entity_tags($entity_code, $entity_id));
                break;

            case 'searchproducts':
                $this->_json($this->webshop_api_model->search_products(
                    $this->input->post('keyword'),
                    $this->input->post('category_id')
                ));
                break;

            /* ── GEO ────────────────────────────────────────────────── */
            case 'getstates':
                $this->_json($this->webshop_api_model->get_states());
                break;

            case 'getcountries':
                $this->_json($this->webshop_api_model->get_countries());
                break;

            /* ── CUSTOMER ───────────────────────────────────────────── */
            case 'getcustomer':
                $filter = array_filter([
                    'id'    => $this->input->post('id'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                ]);
                if (empty($filter)) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'id, phone or email required']);
                }
                $this->_json($this->webshop_api_model->get_customer($filter));
                break;

            case 'createcustomer':
                if (!$this->input->post('name') || !$this->input->post('phone')) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'name and phone are required']);
                }
                $this->_json($this->webshop_api_model->create_customer([
                    'name'        => $this->input->post('name'),
                    'phone'       => $this->input->post('phone'),
                    'email'       => $this->input->post('email'),
                    'company'     => $this->input->post('company'),
                    'address'     => $this->input->post('address'),
                    'city'        => $this->input->post('city'),
                    'state'       => $this->input->post('state'),
                    'state_code'  => $this->input->post('state_code'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country'     => $this->input->post('country'),
                    'password'    => $this->input->post('password'),
                ]));
                break;

            case 'logincheck':
                if (!$this->input->post('login') || !$this->input->post('password')) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'login and password required']);
                }
                $this->_json($this->webshop_api_model->login_customer(
                    $this->input->post('login'),
                    $this->input->post('password')
                ));
                break;

            case 'adminlogincheck':
                if (!$this->input->post('login') || !$this->input->post('password')) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'login and password required']);
                }
                $this->_json($this->webshop_api_model->login_admin(
                    $this->input->post('login'),
                    $this->input->post('password')
                ));
                break;

            case 'registercheck':
                $this->_json($this->webshop_api_model->register_check(
                    $this->input->post('phone'),
                    $this->input->post('email')
                ));
                break;

            /* ── PASSWORD RESET ─────────────────────────────────────────
             * The storefront generates the OTP and stores it in its own
             * session; this endpoint only delivers it through every
             * channel available to the customer (WhatsApp/SMS/Email).
             */
            case 'passwordotpsend':
                $phone = $this->input->post('phone');
                $otp   = $this->input->post('otp');
                if (!$phone || !$otp) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'phone and otp required']);
                }
                // Forgot-password OTP: WhatsApp (direct text) + SMS + email on ElintOm
                $this->_json($this->webshop_api_model->send_password_otp($phone, $otp));
                break;

            // Post-checkout / status WhatsApp (storefront_base_url in POST from webshopapi)
            case 'notifywebshoporderwhatsapp':
                $oid = (int) $this->input->post('order_id');
                $flag = trim((string) $this->input->post('flag'));
                if ($flag === '') {
                    $flag = 'true';
                }
                if ($oid < 1) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'order_id required']);
                }
                $this->_json($this->webshop_api_model->notify_webshop_order_whatsapp($oid, $flag));
                break;

            case 'notifywebshoporderemail':
                $oid = (int) $this->input->post('order_id');
                if ($oid < 1) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'order_id required']);
                }
                $this->_json($this->webshop_api_model->notify_webshop_order_email($oid));
                break;

            case 'customerresetpassword':
                $phone = $this->input->post('phone');
                $new_password = $this->input->post('new_password');
                if (!$phone || !$new_password) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'phone and new_password required']);
                }
                $this->_json($this->webshop_api_model->reset_customer_password($phone, $new_password));
                break;

            /* ── ADDRESSES ──────────────────────────────────────────── */
            case 'getaddresses':
                $customer_id = (int) $this->input->post('customer_id');
                if (!$customer_id) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'customer_id required']);
                }
                $this->_json($this->webshop_api_model->get_addresses(
                    $customer_id,
                    $this->input->post('address_id')
                ));
                break;

            case 'addaddress':
                $this->_json($this->webshop_api_model->add_address([
                    // Accept both 'customer_id' and 'company_id' — the webshopapi
                    // builds address arrays with 'company_id' (matching local DB column).
                    'customer_id'  => (int) ($this->input->post('customer_id') ?: $this->input->post('company_id')),
                    'address_name' => $this->input->post('address_name'),
                    'company_name' => $this->input->post('company_name'),
                    'line1'        => $this->input->post('line1'),
                    'line2'        => $this->input->post('line2'),
                    'city'         => $this->input->post('city'),
                    'postal_code'  => $this->input->post('postal_code'),
                    'state'        => $this->input->post('state'),
                    'state_code'   => $this->input->post('state_code'),
                    'country'      => $this->input->post('country'),
                    'phone'        => $this->input->post('phone'),
                    // Accept 'email_id' (webshopapi local DB key) as well as 'email'.
                    'email'        => $this->input->post('email') ?: $this->input->post('email_id'),
                    'address_type' => $this->input->post('address_type') ?: 'billing',
                ]));
                break;

            case 'updateaddress':
                $this->_json($this->webshop_api_model->update_address([
                    'address_id'   => (int) $this->input->post('address_id'),
                    'customer_id'  => (int) ($this->input->post('customer_id') ?: $this->input->post('company_id')),
                    'address_name' => $this->input->post('address_name'),
                    'company_name' => $this->input->post('company_name'),
                    'line1'        => $this->input->post('line1'),
                    'line2'        => $this->input->post('line2'),
                    'city'         => $this->input->post('city'),
                    'postal_code'  => $this->input->post('postal_code'),
                    'state'        => $this->input->post('state'),
                    'state_code'   => $this->input->post('state_code'),
                    'country'      => $this->input->post('country'),
                    'phone'        => $this->input->post('phone'),
                    'email'        => $this->input->post('email') ?: $this->input->post('email_id'),
                ]));
                break;

            case 'deleteaddress':
                $this->_json($this->webshop_api_model->delete_address_for_customer(
                    (int) ($this->input->post('customer_id') ?: $this->input->post('company_id')),
                    (int) $this->input->post('address_id')
                ));
                break;

            case 'setaddressdefault':
                $this->_json($this->webshop_api_model->set_address_default_for_customer(
                    (int) ($this->input->post('customer_id') ?: $this->input->post('company_id')),
                    (int) $this->input->post('address_id')
                ));
                break;

            /* ── ORDERS ─────────────────────────────────────────────── */
            case 'addorder':
                $raw_order = $this->input->post('order');
                $raw_items = $this->input->post('items');
                if (!$raw_order || !$raw_items) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'order and items required']);
                }
                $order = json_decode($raw_order, true);
                $items = json_decode($raw_items, true);
                if (!$order || !$items) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'Invalid order or items JSON']);
                }
                $this->load->helper('order_pricing');
                $clean_items = array();
                foreach ($items as $line) {
                    $row = is_array($line) ? $line : (array) $line;
                    if (function_exists('order_pricing_normalize_item_row')) {
                        $row = order_pricing_normalize_item_row($row);
                    }
                    if (function_exists('order_pick_order_item_line_for_db')) {
                        $row = order_pick_order_item_line_for_db($row);
                    } else {
                        foreach (array('variant_id', 'variant_price', 'product_option_id') as $drop) {
                            unset($row[$drop]);
                        }
                    }
                    if (!empty($row['product_id'])) {
                        $clean_items[] = $row;
                    }
                }
                if ($clean_items === array()) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'No valid order lines in items JSON']);
                }
                
                $d = $order;
                $d['items'] = $clean_items;
                $res = $this->webshop_api_model->create_order($d);
                
                if (isset($res['status']) && $res['status'] === 'SUCCESS') {
                    $this->_json(['status' => 'SUCCESS', 'order_id' => $res['sale_id']]);
                } else {
                    $this->_json(['status' => 'ERROR', 'msg' => isset($res['msg']) ? $res['msg'] : 'Database insert failed']);
                }
                break;

            case 'getgatewaycredentials':
                $this->_json($this->webshop_api_model->get_gateway_credentials());
                break;

            case 'getorder':
                $oid = (int) $this->input->post('order_id');
                $ref = trim((string) $this->input->post('reference_no'));
                if ($oid < 1 && $ref === '') {
                    $this->_json(['status' => 'ERROR', 'msg' => 'order_id or reference_no required']);
                }
                $this->_json($this->webshop_api_model->get_order($oid, $ref !== '' ? $ref : null));
                break;

            // Guest track link from WhatsApp (md5 of orders.id) — no customer session required.
            case 'getorderbytrackhash':
                $hash = trim((string) $this->input->post('track_hash'));
                $this->_json($this->webshop_api_model->get_order_by_track_hash($hash));
                break;

            case 'recordccavenuepayment':
                $raw = $this->input->post('response_json');
                $response_data = is_string($raw) ? json_decode($raw, true) : null;
                if (!$response_data || !is_array($response_data)) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'response_json must be a JSON object']);
                }
                $this->_json($this->webshop_api_model->record_ccavenue_payment($response_data));
                break;

            case 'cancelorder':
                /*
                 * Mark a webshop order Cancelled when the buyer aborts at the payment gateway
                 * (or the gateway declines). Keeps an audit trail rather than hard-deleting
                 * the sale, but the admin order grid can filter Cancelled rows out.
                 */
                $oid = (int) $this->input->post('order_id');
                $ref = trim((string) $this->input->post('reference_no'));
                $reason = trim((string) $this->input->post('reason'));
                if ($oid < 1 && $ref === '') {
                    $this->_json(['status' => 'ERROR', 'msg' => 'order_id or reference_no required']);
                }
                $this->_json($this->webshop_api_model->cancel_order(
                    $oid,
                    $ref !== '' ? $ref : null,
                    $reason !== '' ? $reason : null
                ));
                break;

            case 'getcustomersales':
                $customer_id = (int) $this->input->post('customer_id');
                $sale_status = $this->input->post('sale_status');
                if (!$customer_id) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'customer_id required']);
                }
                $this->_json($this->webshop_api_model->get_customer_sales($customer_id, $sale_status));
                break;

            /* ── COUPONS ────────────────────────────────────────────── */
            case 'applycoupon':
                $code = $this->input->post('coupon_code');
                if (!$code) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'coupon_code required']);
                }
                $this->_json($this->webshop_api_model->apply_coupon(
                    $code,
                    (float) $this->input->post('cart_total')
                ));
                break;

            /* ── WISHLIST ───────────────────────────────────────────── */
            case 'getwishlist':
                $this->_json($this->webshop_api_model->get_wishlist(
                    (int) $this->input->post('user_id')
                ));
                break;

            case 'addwishlist':
                $this->_json($this->webshop_api_model->add_wishlist(
                    (int) $this->input->post('user_id'),
                    (int) $this->input->post('product_id'),
                    $this->input->post('option_id')
                ));
                break;

            case 'removewishlist':
                $this->_json($this->webshop_api_model->remove_wishlist(
                    (int) $this->input->post('user_id'),
                    (int) $this->input->post('product_id'),
                    $this->input->post('option_id')
                ));
                break;

            /* ── COMPANY ────────────────────────────────────────────── */
            case 'getcompany':
                $biller_id = (int) $this->input->post('biller_id');
                $this->_json($this->webshop_api_model->get_company($biller_id));
                break;

            case 'submitproductreview':
                $ctx = [
                    'product_name'    => $this->input->post('product_name'),
                    'variant_id'      => $this->input->post('variant_id') !== '' && $this->input->post('variant_id') !== null
                        ? (int) $this->input->post('variant_id') : 0,
                    'variant_name'    => $this->input->post('variant_name'),
                    'reviews_title'   => $this->input->post('review_title'),
                    'customer_images' => '',
                ];
                $cid_raw = $this->input->post('customer_id');
                $customer_id = ($cid_raw !== '' && $cid_raw !== null) ? (int) $cid_raw : null;
                $this->_json($this->webshop_api_model->submit_product_review(
                    (int) $this->input->post('product_id'),
                    (int) $this->input->post('rating'),
                    (string) $this->input->post('review'),
                    (string) ($this->input->post('customer_name') ?: 'Customer'),
                    $customer_id,
                    $ctx
                ));
                break;

            case 'getproductreviews':
                $pid = (int) $this->input->post('product_id');
                $lim = (int) $this->input->post('limit');
                if ($pid <= 0) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'product_id required']);
                }
                $this->_json($this->webshop_api_model->get_product_reviews_list($pid, $lim > 0 ? $lim : 200));
                break;

            case 'getproductrating':
                $pid = (int) $this->input->post('product_id');
                if ($pid <= 0) {
                    $this->_json(['status' => 'ERROR', 'msg' => 'product_id required']);
                }
                $this->_json($this->webshop_api_model->get_product_rating($pid));
                break;

            /* ── CONTACT / LEADS ───────────────────────────────────── */
            case 'getcontactformpreset':
                $this->_json($this->webshop_api_model->get_contact_form_preset(
                    (string) $this->input->post('theme_slug'),
                    (string) $this->input->post('page_url'),
                    (string) $this->input->post('form_key')
                ));
                break;

            case 'submitcontactlead':
                try {
                    $this->_json($this->webshop_api_model->submit_contact_lead(array(
                        'name'          => $this->input->post('name'),
                        'full_name'     => $this->input->post('full_name'),
                        'phone'         => $this->input->post('phone'),
                        'mobile'        => $this->input->post('mobile'),
                        'email'         => $this->input->post('email'),
                        'message'       => $this->input->post('message'),
                        'comments'      => $this->input->post('comments'),
                        'source'        => $this->input->post('source'),
                        'status'        => $this->input->post('status'),
                        'status_id'     => $this->input->post('status_id'),
                        'response_json' => $this->input->post('response_json', false),
                    )));
                } catch (\Throwable $e) {
                    log_message('error', 'Webshop_api::submitcontactlead fatal: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
                    $this->_json(array(
                        'status' => 'ERROR',
                        'msg'    => 'submitcontactlead failed: ' . $e->getMessage(),
                    ));
                }
                break;

            case 'submitnewslettersubscriber':
                try {
                    $this->_json($this->webshop_api_model->submit_newsletter_subscriber(array(
                        'email'       => $this->input->post('email'),
                        'source'      => $this->input->post('source'),
                        'ip_address'  => $this->input->post('ip_address'),
                        'user_agent'  => $this->input->post('user_agent'),
                    )));
                } catch (\Throwable $e) {
                    log_message('error', 'Webshop_api::submitnewslettersubscriber fatal: ' . $e->getMessage());
                    $this->_json(array(
                        'status' => 'ERROR',
                        'msg'    => 'submitnewslettersubscriber failed: ' . $e->getMessage(),
                    ));
                }
                break;

            default:
                $this->_json(['status' => 'ERROR', 'error_code' => 103, 'msg' => 'Unknown action: ' . $action]);
        }
    }

    /** Encode $data as JSON and exit. */
    private function _json($data) {
        header('Content-Type: application/json; charset=utf-8');
        $flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        $json = json_encode($data, $flags);
        if ($json === false) {
            $json = json_encode(array(
                'status' => 'ERROR',
                'error_code' => 500,
                'msg' => 'JSON encode failed: ' . json_last_error_msg(),
            ), $flags);
            if ($json === false) {
                $json = '{"status":"ERROR","msg":"JSON encode failed"}';
            }
        }
        echo $json;
        exit;
    }

    private function buildCmsImageUrl($file_name) {
        $file_name = trim((string) $file_name);
        if ($file_name === '') {
            return '';
        }
        if (function_exists('cms_media_public_url')) {
            $this->load->helper('cms_media');
            $customer_assets = isset($this->Settings->customer_assets) && $this->Settings->customer_assets !== ''
                ? $this->Settings->customer_assets
                : (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost');
            return cms_media_public_url($file_name, $customer_assets);
        }
        $customer_assets = isset($this->Settings->customer_assets) && $this->Settings->customer_assets !== ''
            ? $this->Settings->customer_assets
            : 'localhost';
        return base_url('assets/mdata/' . $customer_assets . '/uploads/webshop/cms_pages/' . $file_name);
    }

    private function normalizeFlag($value) {
        $value = strtolower(trim((string) $value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true);
    }

    private function isSectionFlagEnabled($section, $config, $flag_name) {
        if (isset($section[$flag_name])) {
            return $this->normalizeFlag($section[$flag_name]);
        }
        if (isset($config[$flag_name])) {
            return $this->normalizeFlag($config[$flag_name]);
        }
        return false;
    }

}
