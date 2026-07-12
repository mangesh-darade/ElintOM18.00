<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Newsletter signups from footer and other webshop forms.
 */
class Newsletter_subscribers extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('newsletter_subscribers');
    }

    public function index()
    {
        $this->data['rows'] = $this->cms_newsletter_subscribers_model->list_subscribers();
        $this->data['schema_ready'] = $this->cms_newsletter_subscribers_model->ensure_table();

        $meta = array('page_title' => 'Newsletter Subscribers');
        $this->data['cms_list_datatable'] = true;
        $this->cms_page_construct('cms_admin/newsletter_subscribers/index', $meta, $this->data);
    }
}
