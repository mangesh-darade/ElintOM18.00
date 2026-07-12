<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Entity_faqs extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('entity_faqs');
        $this->load_cms_model('entity_tags');
    }

    public function index()
    {
        $rows = $this->cms_entity_faqs_model->get_faq_list();
        foreach ($rows as $i => $row) {
            $rows[$i]['entity_label'] = $this->cms_entity_faqs_model->entity_label_from_list_row($row);
        }
        $this->data['rows'] = $rows;
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'Entity FAQs'),
        );
        $meta = array('page_title' => 'Entity FAQs', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/entity_faqs/list', $meta, $this->data);
    }

    public function add()
    {
        $this->data['entity_types'] = $this->cms_entity_tags_model->get_entity_types();
        $entity_master_id_ctx = (int) $this->input->post('entity_master_id');
        $entity_id_ctx = (int) $this->input->post('entity_id');
        $this->data['entity_faqs'] = array();
        $this->data['entity_items'] = $entity_master_id_ctx > 0
            ? $this->cms_entity_tags_model->get_entities_by_type($entity_master_id_ctx)
            : array();

        if ($entity_master_id_ctx > 0 && $entity_id_ctx > 0) {
            $this->data['entity_faqs'] = $this->cms_entity_faqs_model->getFaqsByEntity(
                $entity_master_id_ctx,
                $entity_id_ctx
            );
        }

        if ($this->input->post('save_entity_faqs')) {
            $this->form_validation->set_rules('entity_master_id', 'Entity Type', 'required|integer');
            $this->form_validation->set_rules('entity_id', 'Entity Id', 'required|integer');

            if ($this->form_validation->run() === true) {
                $entity_master_id = (int) $this->input->post('entity_master_id');
                $entity_id = (int) $this->input->post('entity_id');
                $faq_rows = (array) $this->input->post('entity_faqs');
                $result = $this->cms_entity_faqs_model->saveFaqsForEntity($entity_master_id, $entity_id, $faq_rows);

                if (!empty($result['ok'])) {
                    $this->session->set_flashdata('message', $result['message']);
                    $this->cms_redirect('entity_faqs/edit/' . $entity_master_id . '/' . $entity_id);
                }
                $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Failed to save FAQs.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => $this->cms_url('entity_faqs'), 'page' => 'Entity FAQs'),
            array('link' => '#', 'page' => 'Add'),
        );
        $this->data['entity_faqs_schema_ready'] = $this->cms_entity_faqs_model->ensure_table();
        $this->data['cms_enhancements'] = true;
        $meta = array('page_title' => 'Add Entity FAQs', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/entity_faqs/add', $meta, $this->data);
    }

    public function edit($entity_master_id = null, $entity_id = null)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id <= 0 || $entity_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid entity selection.');
            $this->cms_redirect('entity_faqs');
        }

        $entity_code = $this->cms_entity_tags_model->get_entity_code_by_id($entity_master_id);
        if ($entity_code === '') {
            $this->session->set_flashdata('error', 'Entity type not found.');
            $this->cms_redirect('entity_faqs');
        }

        $this->data['entity_types'] = $this->cms_entity_tags_model->get_entity_types();
        $this->data['entity_items'] = $this->cms_entity_tags_model->get_entities_by_type($entity_master_id);
        $this->data['mapping'] = array(
            'entity_master_id' => $entity_master_id,
            'entity_id'        => $entity_id,
            'entity_code'      => $entity_code,
        );
        $this->data['entity_faqs'] = $this->cms_entity_faqs_model->getFaqsByEntity($entity_master_id, $entity_id);

        if ($this->input->post('update_entity_faqs')) {
            $this->form_validation->set_rules('entity_master_id', 'Entity Type', 'required|integer');
            $this->form_validation->set_rules('entity_id', 'Entity Id', 'required|integer');

            if ($this->form_validation->run() === true) {
                $new_entity_master_id = (int) $this->input->post('entity_master_id');
                $new_entity_id = (int) $this->input->post('entity_id');
                $faq_rows = (array) $this->input->post('entity_faqs');
                $result = $this->cms_entity_faqs_model->saveFaqsForEntity(
                    $new_entity_master_id,
                    $new_entity_id,
                    $faq_rows
                );

                if (!empty($result['ok'])) {
                    if ($new_entity_master_id !== $entity_master_id || $new_entity_id !== $entity_id) {
                        $this->cms_entity_faqs_model->deleteFaqsForEntity($entity_master_id, $entity_id);
                    }
                    $this->session->set_flashdata('message', $result['message']);
                    $this->cms_redirect('entity_faqs/edit/' . $new_entity_master_id . '/' . $new_entity_id);
                }
                $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Failed to save FAQs.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => $this->cms_url('entity_faqs'), 'page' => 'Entity FAQs'),
            array('link' => '#', 'page' => 'Edit'),
        );
        $this->data['entity_faqs_schema_ready'] = $this->cms_entity_faqs_model->ensure_table();
        $this->data['cms_enhancements'] = true;
        $meta = array('page_title' => 'Edit Entity FAQs', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/entity_faqs/edit', $meta, $this->data);
    }

    public function delete($entity_master_id = null, $entity_id = null)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id <= 0 || $entity_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid entity selection.');
            $this->cms_redirect('entity_faqs');
        }

        if ($this->cms_entity_faqs_model->deleteFaqsForEntity($entity_master_id, $entity_id)) {
            $this->session->set_flashdata('message', 'Entity FAQs deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete entity FAQs.');
        }
        $this->cms_redirect('entity_faqs');
    }
}
