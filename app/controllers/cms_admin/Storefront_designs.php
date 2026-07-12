<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * CMS hub: header & footer layout designs (storefront profiles).
 */
class Storefront_designs extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('storefront');
        $this->load->helper('cms_layout');
    }

    public function index()
    {
        redirect('cms_admin/layout_builder');
    }

    public function index_legacy()
    {
        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => 'Layout designs'),
        );
        $meta = array('page_title' => 'Header & Footer layouts', 'bc' => $bc);

        $this->data['schema_ready'] = $this->cms_storefront_model->schema_ready();
        $this->data['header_profiles'] = $this->cms_storefront_model->get_header_design_profiles();
        $this->data['footer_profiles'] = $this->cms_storefront_model->get_footer_design_profiles();
        $this->data['cms_list_datatable'] = false;
        $this->cms_page_construct('cms_admin/storefront_designs/index', $meta, $this->data);
    }
}
