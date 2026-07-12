<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for the standalone CMS Admin Panel MVC module.
 */
class Cms_admin_base extends MY_Controller
{
    /** @var string URI prefix for all CMS admin routes (cms_admin/...) */
    protected $cms_uri = 'cms_admin';

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Settings->active_webshop) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        // CMS admin views live under themes/default/views/cms_admin/ (not the POS skin name in sma_settings.theme).
        $this->theme = 'default/views/';
        $this->data['assets'] = base_url() . 'themes/default/assets/';

        $this->load->library('form_validation');
        $this->load->helper('cms_admin');

        $this->load->model('cms_admin/Cms_admin_schema_model', 'cms_schema_model');
        $this->cms_schema_model->ensure_all();

        $this->m = 'cms_admin';
        $this->data['m'] = 'cms_admin';
    }

    /**
     * Release the PHP session lock so parallel AJAX (layout workspace) does not block the shell.
     */
    protected function cms_release_session_lock()
    {
        if (function_exists('session_write_close')) {
            @session_write_close();
        }
    }

    /**
     * Render CMS admin pages with standalone layout (no ERP header/sidebar/footer).
     */
    protected function cms_page_construct($page, $meta = array(), $data = array())
    {
        $meta = $this->cms_prepare_page_meta($meta, $data);

        if (!empty($meta['cms_layout_builder'])) {
            $this->cms_layout_builder_construct($meta, $data);
            return;
        }

        $this->load->view($this->theme . 'cms_admin/layout/header', $meta);
        $this->load->view($this->theme . $page, $data);
        $this->load->view($this->theme . 'cms_admin/layout/footer', $meta);
    }

    protected function cms_prepare_page_meta($meta, $data)
    {
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');
        $meta['Settings'] = isset($data['Settings']) ? $data['Settings'] : $this->Settings;
        $meta['assets'] = isset($data['assets']) ? $data['assets'] : $this->data['assets'];
        $meta['dateFormats'] = isset($data['dateFormats']) ? $data['dateFormats'] : $this->dateFormats;
        $meta['cms_section'] = $this->cms_active_section();
        $meta['cms_list_datatable'] = $this->cms_uses_list_datatable() || !empty($data['cms_list_datatable']);
        $meta['simple_datatable'] = $meta['cms_list_datatable']
            || !empty($data['simple_datatable'])
            || !empty($data['cms_enhancements']);
        $meta['cms_enhancements'] = !empty($data['cms_enhancements']);
        $meta['cms_erp_embed'] = !empty($data['cms_erp_embed']);
        foreach (array(
            'cms_layout_builder',
            'cms_header_designs',
            'cms_footer_designs',
            'cms_header_designs_editor',
            'cms_footer_designs_editor',
        ) as $cms_view_flag) {
            if (!empty($data[$cms_view_flag])) {
                $meta[$cms_view_flag] = true;
            }
        }
        $meta['cms_assets'] = $this->cms_assets_base();
        $meta['Customer_assets'] = $this->Customer_assets;
        if (!isset($meta['page_title'])) {
            $meta['page_title'] = 'CMS Admin';
        }
        $meta['cms_breadcrumbs'] = $this->cms_breadcrumbs($meta['page_title']);

        return $meta;
    }

    /**
     * Fast layout builder shell: subtitle paints before heavy editor HTML (loaded via AJAX).
     */
    protected function cms_layout_builder_construct($meta, $data)
    {
        $html = $this->load->view($this->theme . 'cms_admin/layout/header_lb_head', $meta, true)
            . $this->load->view($this->theme . 'cms_admin/layout/header_lb_shell_min', $meta, true)
            . $this->load->view($this->theme . 'cms_admin/layout_builder/_lcp', $data, true)
            . $this->load->view($this->theme . 'cms_admin/layout/footer_lb', $meta, true);
        $this->output->set_output($html);
    }

    /**
     * Echo a view immediately and flush so the browser can paint before PHP finishes.
     */
    protected function cms_stream_view($view, $vars = array())
    {
        echo $this->load->view($this->theme . $view, $vars, true);
        $this->cms_admin_stream_flush();
    }

    protected function cms_admin_stream_flush()
    {
        if (!headers_sent()) {
            header('X-Accel-Buffering: no');
        }
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        @flush();
    }

    /**
     * List index screens that use DataTables (pages, storefront, entity tags).
     */
    protected function cms_uses_list_datatable()
    {
        if (strtolower($this->router->fetch_method()) !== 'index') {
            return false;
        }
        return in_array($this->cms_active_section(), array('pages', 'blogs', 'testimonials', 'storefront', 'entity_tags', 'entity_faqs', 'tags_master', 'form_templates', 'newsletter_subscribers', 'media'), true);
    }

    protected function cms_active_section()
    {
        $class = strtolower($this->router->fetch_class());
        $map = array(
            'dashboard'       => 'dashboard',
            'catalog'         => 'catalog',
            'pages'           => 'pages',
            'blogs'           => 'blogs',
            'testimonials'    => 'testimonials',
            'storefront'         => 'storefront',
            'layout_builder'     => 'layout_builder',
            'storefront_designs' => 'layout_builder',
            'header_designs'     => 'layout_builder',
            'footer_designs'     => 'layout_builder',
            'entity_tags'     => 'entity_tags',
            'entity_faqs'     => 'entity_faqs',
            'tags_master'     => 'tags_master',
            'site_settings'   => 'site_settings',
            'llms_txt'        => 'site_settings',
            'prices'          => 'prices',
            'form_templates'         => 'form_templates',
            'leads'                  => 'leads',
            'newsletter_subscribers' => 'newsletter_subscribers',
            'media'                  => 'media',
            'guide'                  => 'guide',
        );
        return isset($map[$class]) ? $map[$class] : 'dashboard';
    }

    /**
     * Load a CMS admin model from app/models/cms_admin/.
     *
     * @param string $short_name e.g. pages, catalog, storefront, entity_tags, prices
     */
    protected function load_cms_model($short_name)
    {
        $map = array(
            'pages'        => 'Cms_admin_pages_model',
            'catalog'      => 'Cms_admin_catalog_model',
            'storefront'          => 'Cms_admin_storefront_model',
            'entity_tags'  => 'Cms_admin_entity_tags_model',
            'entity_faqs'  => 'Cms_admin_entity_faqs_model',
            'tags_master'  => 'Cms_admin_tags_master_model',
            'llms_txt'     => 'Cms_admin_llms_txt_model',
            'prices'       => 'Cms_admin_prices_model',
            'dashboard'      => 'Cms_admin_dashboard_model',
            'form_templates'         => 'Cms_admin_form_templates_model',
            'newsletter_subscribers' => 'Cms_admin_newsletter_subscribers_model',
            'media'                  => 'Cms_admin_media_model',
            'pages_faqs'             => 'Cms_admin_pages_faqs_model',
        );
        if (!isset($map[$short_name])) {
            show_error('Unknown CMS admin model: ' . $short_name);
        }
        $property = 'cms_' . $short_name . '_model';
        if (!isset($this->$property)) {
            $this->load->model('cms_admin/' . $map[$short_name], $property);
        }
    }

    /**
     * @param string $path Path without leading cms_admin/ (e.g. "pages/edit/5")
     */
    protected function cms_redirect($path = '')
    {
        $path = ltrim((string) $path, '/');
        redirect($path === '' ? $this->cms_uri : $this->cms_uri . '/' . $path);
    }

    /**
     * Ping the storefront so CMS page/nav disk caches are cleared after admin saves.
     */
    protected function cms_bust_storefront_cms_cache()
    {
        if (!function_exists('cms_webshop_refresh_storefront_cms_cache')) {
            $this->load->helper('cms_layout');
        }
        if (function_exists('cms_webshop_refresh_storefront_cms_cache')) {
            cms_webshop_refresh_storefront_cms_cache();
        }
    }

    protected function cms_url($path = '')
    {
        $path = ltrim((string) $path, '/');
        return site_url($path === '' ? $this->cms_uri : $this->cms_uri . '/' . $path);
    }

    /**
     * Base URL for CMS Admin static assets (themes/default/assets/cms_admin/).
     */
    protected function cms_assets_base()
    {
        return base_url('themes/default/assets/cms_admin/');
    }

    /**
     * @param string $path Path under cms_admin assets, e.g. "css/layout.css"
     */
    protected function cms_breadcrumbs($page_title)
    {
        $section = $this->cms_active_section();
        $sections = array(
            'dashboard'   => array('label' => 'Dashboard', 'path' => 'dashboard'),
            'catalog'     => array('label' => 'Products', 'path' => 'catalog'),
            'pages'       => array('label' => 'CMS Pages', 'path' => 'pages'),
            'blogs'       => array('label' => 'Blog Posts', 'path' => 'blogs'),
            'testimonials' => array('label' => 'Testimonials', 'path' => 'testimonials'),
            'storefront'         => array('label' => 'Header & Footer', 'path' => 'storefront'),
            'entity_tags' => array('label' => 'Entity Pages', 'path' => 'entity_tags'),
            'entity_faqs' => array('label' => 'Entity FAQs', 'path' => 'entity_faqs'),
            'tags_master' => array('label' => 'Tag Master', 'path' => 'tags_master'),
            'site_settings' => array('label' => 'Site Settings', 'path' => 'site_settings/robots'),
            'prices'         => array('label' => 'Prices', 'path' => 'prices'),
            'form_templates'         => array('label' => 'Form Templates', 'path' => 'form_templates'),
            'leads'                  => array('label' => 'Leads', 'path' => 'leads'),
            'newsletter_subscribers' => array('label' => 'Newsletter Subscribers', 'path' => 'newsletter_subscribers'),
            'media'                  => array('label' => 'Media Library', 'path' => 'media'),
            'guide'                  => array('label' => 'User Guide', 'path' => 'guide'),
        );

        if ($section === 'dashboard') {
            return array(array('label' => 'Dashboard', 'url' => null));
        }

        $crumbs = array(
            array('label' => 'Dashboard', 'url' => site_url('cms_admin/dashboard')),
        );

        if (isset($sections[$section])) {
            $crumbs[] = array(
                'label' => $sections[$section]['label'],
                'url'   => site_url('cms_admin/' . $sections[$section]['path']),
            );
        }

        $section_label = isset($sections[$section]) ? $sections[$section]['label'] : '';
        if ($page_title && $page_title !== $section_label) {
            $crumbs[] = array('label' => $page_title, 'url' => null);
        }

        return $crumbs;
    }

    protected function cms_asset($path)
    {
        return cms_admin_asset_url($path, $this->cms_assets_base());
    }

    public function do_upload($field_name, $folder = '', $relax_dimensions = false)
    {
        $relative_upload_dir = "assets/mdata/$this->Customer_assets/uploads/webshop/" . ($folder ? $folder . '/' : '');
        $absolute_upload_dir = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative_upload_dir);
        if (!is_dir($absolute_upload_dir)) {
            @mkdir($absolute_upload_dir, 0777, true);
        }

        $config = array(
            'upload_path'   => $absolute_upload_dir,
            'allowed_types' => 'gif|jpg|png|jpeg|pdf|doc|docx',
            'overwrite'     => TRUE,
            'max_size'      => '2048000',
            'max_height'    => '768',
            'max_width'     => '1024',
        );

        if ($folder === 'cms_pages' || $relax_dimensions) {
            unset($config['max_height'], $config['max_width']);
        }

        $this->load->library('upload', $config);
        if ($this->upload->do_upload($field_name)) {
            return array('status' => 'success', 'upload_data' => $this->upload->data());
        }

        return array('status' => 'fail', 'error' => $this->upload->display_errors());
    }

    protected function normalizeYesNo($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true) ? 'yes' : 'no';
    }

    protected function buildSectionVisibilityColumns($show_header, $show_footer, $show_banner, $show_logo)
    {
        $data = array();

        $this->load_cms_model('pages');
        $m = $this->cms_pages_model;
        if ($m->hasPageSectionColumn('header')) {
            $data['header'] = $show_header;
        }
        if ($m->hasPageSectionColumn('footer')) {
            $data['footer'] = $show_footer;
        }
        if ($m->hasPageSectionColumn('banner')) {
            $data['banner'] = $show_banner;
        }
        if ($m->hasPageSectionColumn('logo')) {
            $data['logo'] = $show_logo;
        }
        if ($m->hasPageSectionColumn('show_header')) {
            $data['show_header'] = $show_header;
        }
        if ($m->hasPageSectionColumn('show_footer')) {
            $data['show_footer'] = $show_footer;
        }
        if ($m->hasPageSectionColumn('show_banner')) {
            $data['show_banner'] = $show_banner;
        }
        if ($m->hasPageSectionColumn('show_logo')) {
            $data['show_logo'] = $show_logo;
        }

        return $data;
    }
}
