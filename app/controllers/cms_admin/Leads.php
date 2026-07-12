<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * CMS Admin shell around the existing ElintOM Leads module (themes/default/views/leads/).
 */
class Leads extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $lang = isset($this->Settings->user_language) ? $this->Settings->user_language : $this->Settings->language;
        $this->lang->load('employees_lang', $lang);
        $this->load->model('Leads_model');
    }

    public function index($action = null)
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $this->data['statuses'] = $this->db->order_by('sequence', 'ASC')->get('sma_leads_status')->result();
        $converted_row = $this->db->select('id')->from('sma_leads_status')->where('LOWER(name)', 'converted')->get()->row();
        $this->data['converted_status_id'] = $converted_row ? (int) $converted_row->id : null;
        $this->data['assign_users'] = $this->Leads_model->getGroupByName('manager');

        if (!empty($this->Owner) || !empty($this->Admin)) {
            $this->data['GP'] = (object) array('bulk_actions' => 1);
        }

        $meta = array('page_title' => lang('All leads'));
        $this->data['cms_erp_embed'] = true;
        $this->cms_page_construct('cms_admin/leads/index', $meta, $this->data);
    }
}
