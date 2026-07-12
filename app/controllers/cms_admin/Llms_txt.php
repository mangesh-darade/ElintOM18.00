<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * @deprecated Use cms_admin/site_settings/llms
 */
class Llms_txt extends Cms_admin_base
{
    public function index()
    {
        $this->cms_redirect('site_settings/llms');
    }
}
