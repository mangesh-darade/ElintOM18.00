<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Legacy entry URL — forwards to the standalone CMS Admin module.
 */
class Cms_admin_panel extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        redirect('cms_admin/dashboard');
    }

    public function index()
    {
        redirect('cms_admin/dashboard');
    }
}
