<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Site Settings — robots.txt, sitemap.xml, llms.txt (preview + LLMS edit).
 */
class Site_settings extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cms_admin/Cms_admin_site_settings_model', 'cms_site_settings_model');
        $this->load->helper('cms_site_settings');
        $this->data['cms_enhancements'] = true;
    }

    public function index()
    {
        $this->cms_redirect('site_settings/robots');
    }

    public function robots()
    {
        if ($this->input->post('save_robots_txt')) {
            $content = (string) $this->input->post('robots_txt_content');
            $ok = $this->cms_site_settings_model->save_robots($content);
            $this->session->set_flashdata(
                $ok ? 'message' : 'error',
                $ok ? 'Robots.txt saved. Live file updated on your storefront.' : 'Could not save robots.txt.'
            );
            $this->cms_redirect('site_settings/robots');
        }

        $this->data['site_settings_tab'] = 'robots';
        $this->data['robots'] = $this->cms_site_settings_model->get_robots_for_admin();
        $this->data['robots_default_template'] = cms_site_settings_robots_default_template();
        $meta = array('page_title' => 'Robots.txt');
        $this->cms_page_construct('cms_admin/site_settings/robots', $meta, $this->data);
    }

    public function sitemap()
    {
        $variant = trim((string) $this->input->get('variant'));
        if ($variant === '') {
            $variant = 'index';
        }
        $this->data['site_settings_tab'] = 'sitemap';
        $this->data['preview'] = $this->cms_site_settings_model->get_sitemap_preview($variant);
        $meta = array('page_title' => 'Sitemap.xml');
        $this->cms_page_construct('cms_admin/site_settings/sitemap', $meta, $this->data);
    }

    public function llms()
    {
        $this->load->model('cms_admin/Cms_admin_llms_txt_model', 'cms_llms_txt_model');
        $this->load->helper('cms_llms_txt');

        if ($this->input->post('save_llms_txt')) {
            $content = (string) $this->input->post('llms_txt_custom');
            $enabled = (bool) $this->input->post('llms_txt_include_custom');
            $ok = $this->cms_llms_txt_model->save($content, $enabled);
            if ($ok) {
                $msg = $enabled
                    ? 'Custom markdown saved and included on live llms.txt.'
                    : 'Custom markdown saved (draft only). Check “Include custom block” and save again to publish it on live llms.txt.';
            } else {
                $msg = 'Could not save llms.txt settings.';
            }
            $this->session->set_flashdata($ok ? 'message' : 'error', $msg);
            $this->cms_redirect('site_settings/llms');
        }

        $settings = $this->cms_llms_txt_model->get_for_admin();
        $site_name = isset($this->Settings->site_name) ? (string) $this->Settings->site_name : 'Store';

        $stored = isset($settings['content']) ? $settings['content'] : '';
        $prepared = cms_llms_txt_prepare_stored_content(
            $stored,
            $site_name,
            $settings['storefront_url']
        );
        if ($prepared !== $stored) {
            $this->cms_llms_txt_model->save($prepared, $settings['enabled']);
            $settings['content'] = $prepared;
            $this->session->set_flashdata(
                'message',
                'Custom markdown was corrected (removed invalid SQL/developer text and fixed storefront URLs).'
            );
        }

        $autoPreview = isset($settings['auto_generated_preview']) ? (string) $settings['auto_generated_preview'] : '';
        $computedPreview = cms_llms_txt_build_admin_live_preview(
            $autoPreview,
            isset($settings['content']) ? (string) $settings['content'] : '',
            !empty($settings['enabled'])
        );
        $livePreview = cms_llms_txt_fetch_storefront_preview($settings['storefront_url']);
        if ($livePreview === '' || !cms_llms_txt_is_valid_preview($livePreview)) {
            $livePreview = $computedPreview;
        }

        $this->data['site_settings_tab'] = 'llms';
        $this->data['llms'] = $settings;
        $this->data['llms_live_preview'] = $livePreview;
        $this->data['llms_live_url'] = rtrim($settings['storefront_url'], '/') . '/llms.txt';
        $this->data['llms_default_template'] = cms_llms_txt_default_template($site_name, $settings['storefront_url']);

        $meta = array('page_title' => 'LLMS.txt');
        $this->cms_page_construct('cms_admin/site_settings/llms', $meta, $this->data);
    }
}
