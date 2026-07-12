<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Tag Master — configure which SEO tags appear on CMS Pages vs Entity forms.
 */
class Tags_master extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('tags_master');
        $this->load->helper('cms_tags');
    }

    public function index()
    {
        $this->data['tags'] = $this->cms_tags_master_model->get_all_for_admin();
        $this->data['scope_options'] = cms_tags_scope_options();
        $this->data['cms_list_datatable'] = true;

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'Tag Master'),
        );
        $meta = array('page_title' => 'Tag Master', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/tags_master/index', $meta, $this->data);
    }

    public function edit($id = null)
    {
        $id = (int) $id;
        $tag = $this->cms_tags_master_model->get_by_id($id);
        if (!$tag) {
            $this->session->set_flashdata('error', 'Tag not found.');
            $this->cms_redirect('tags_master');
        }

        if ($this->input->post('save_tag_master')) {
            $ok = $this->cms_tags_master_model->update_tag($id, array(
                'category'         => $this->input->post('category'),
                'tag_type'         => $this->input->post('tag_type'),
                'page_type'        => $this->input->post('page_type'),
                'show_on_page'     => $this->input->post('show_on_page'),
                'show_on_entity'   => $this->input->post('show_on_entity'),
            ));
            $this->session->set_flashdata($ok ? 'message' : 'error', $ok ? 'Tag updated.' : 'Could not update tag.');
            redirect('cms_admin/tags_master/edit/' . $id);
        }

        $this->data['tag'] = $tag;
        $this->data['scope_options'] = cms_tags_scope_options();

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => $this->cms_url('tags_master'), 'page' => 'Tag Master'),
            array('link' => '#', 'page' => 'Edit'),
        );
        $meta = array('page_title' => 'Edit Tag', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/tags_master/edit', $meta, $this->data);
    }

    /**
     * AJAX: quick save from list toggles / scope dropdown.
     */
    public function ajax_update()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output->set_content_type('application/json');
        $id = (int) $this->input->post('id');
        if ($id <= 0) {
            echo json_encode(array('status' => 'fail', 'message' => 'Invalid tag.', 'csrf_hash' => $this->security->get_csrf_hash()));
            return;
        }

        $payload = array('id' => $id);
        if ($this->input->post('page_type') !== null) {
            $payload['page_type'] = $this->input->post('page_type');
        }
        if ($this->input->post('show_on_page') !== null) {
            $payload['show_on_page'] = $this->input->post('show_on_page');
        }
        if ($this->input->post('show_on_entity') !== null) {
            $payload['show_on_entity'] = $this->input->post('show_on_entity');
        }

        $ok = $this->cms_tags_master_model->update_tag($id, $payload);
        echo json_encode(array(
            'status'    => $ok ? 'success' : 'fail',
            'message'   => $ok ? 'Saved.' : 'Update failed.',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ));
    }
}
