<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Dashboard extends Cms_admin_base
{
    public function index()
    {
        $this->load_cms_model('dashboard');
        $this->data['dashboard_stats'] = $this->cms_dashboard_model->get_stats();
        $this->data['stat_cards'] = $this->cms_dashboard_model->get_stat_cards();

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'CMS Admin Panel'),
        );
        $meta = array('page_title' => 'Dashboard', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/dashboard', $meta, $this->data);
    }
}
