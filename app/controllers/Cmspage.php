<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cmspage extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('cms_model');
        $this->load->model('webshop_model');
        $this->load->library('cms_renderer');
        $this->load->library('cms_seo');
        $this->load->helper('webshop_helper');

        $this->data['assets'] = base_url('themes/default/assets/webshop/');
        $this->data['uploads'] = base_url('assets/mdata/' . $this->Customer_assets . '/uploads/');
        $this->data['thumbs'] = base_url('assets/mdata/' . $this->Customer_assets . '/uploads/thumbs/');
        $this->data['webshop_settings'] = $this->webshop_model->get_webshop_settings();
    }

    /**
     * Catch-all render method for CMS pages.
     *
     * @param string $url_path
     */
    public function index($url_path = '')
    {
        // Normalize URL (always leading slash, no trailing slashes)
        $url = '/' . trim($url_path, '/');

        // For empty route, keep root path '/'
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

        // 4) Nothing found → 404
        if (!$page_data) {
            show_404();
        }

        // Prepare context data for placeholder replacement in SEO
        $context = [
            'page_title'  => $page_data['page']['page_name'],
            'base_url'    => base_url(),
            'site_name'   => isset($this->Settings->site_name) ? $this->Settings->site_name : 'ElintOm',
            'current_url' => current_url(),
        ];

        // 1. Generate SEO / Meta Tags
        $meta_html = $this->cms_seo->generate_meta_tags($page_data['meta'], $context);

        $this->load->helper('cms_layout');
        $layout_data = cms_build_page_layout_sections($page_data['sections'], $this->cms_renderer);
      
        // 3. Assemble Final Output
        $this->data['meta_tags']   = $meta_html;
        $this->data['page_content'] = $layout_data['body_html'];
        $this->data['page_title']   = $page_data['page']['page_name'];
        $this->data['header_sections_html'] = $layout_data['header_html'];
        $this->data['footer_sections_html'] = $layout_data['footer_html'];
        $this->data['banner_sections_html'] = $layout_data['banner_html'];
        $this->data['logo_sections_html'] = $layout_data['logo_html'];
        $this->data['show_header'] = $layout_data['show_header'];
        $this->data['show_footer'] = $layout_data['show_footer'];
        $this->data['page_banner_image_url'] = $this->buildCmsImageUrl(isset($page_data['page']['banner_image']) ? $page_data['page']['banner_image'] : '');
        $this->data['page_logo_image_url'] = $this->buildCmsImageUrl(isset($page_data['page']['logo_image']) ? $page_data['page']['logo_image'] : '');
        $this->data['header_data'] = $this->cms_model->getHeaderData(array());

        // Load dedicated CMS blank page view.
        $this->load->view($this->theme . 'cmspage/blank_page', $this->data);
    }

    private function buildCmsImageUrl($file_name) {
        $file_name = trim((string) $file_name);
        if ($file_name === '') {
            return '';
        }
        $this->load->helper('cms_media');
        return cms_media_public_url($file_name, $this->Customer_assets);
    }

    private function isSectionFlagEnabled($section, $config, $flag_name) {
        $flag_name = (string) $flag_name;
        if (isset($section[$flag_name])) {
            return $this->normalizeFlag($section[$flag_name]);
        }

        if (isset($config[$flag_name])) {
            return $this->normalizeFlag($config[$flag_name]);
        }

        return false;
    }

    private function normalizeFlag($value) {
        $value = strtolower(trim((string) $value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true);
    }

}