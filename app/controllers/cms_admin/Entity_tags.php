<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Entity_tags extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('entity_tags');
        $this->load_cms_model('entity_faqs');
        $this->load->helper('cms_tags');
    }

    public function index()
    {
        $this->data['rows'] = $this->cms_entity_tags_model->get_mapping_list();
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'Entity Tag Mapping'),
        );
        $meta = array('page_title' => 'Entity Tag Mapping', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/entity_tags/list', $meta, $this->data);
    }

    public function add()
    {
        $this->data['entity_types'] = $this->cms_entity_tags_model->get_entity_types();
        $entity_master_id_ctx = (int) $this->input->post('entity_master_id');
        $entity_id_ctx = (int) $this->input->post('entity_id');
        $entity_code_ctx = $entity_master_id_ctx > 0
            ? $this->cms_entity_tags_model->get_entity_code_by_id($entity_master_id_ctx)
            : '';
        $this->data['entity_scope_code'] = $entity_code_ctx;
        $this->data['tags_master'] = $entity_code_ctx !== ''
            ? $this->cms_entity_tags_model->get_tags_master_for_entity($entity_code_ctx)
            : array();
        $this->data['entity_items'] = $entity_master_id_ctx > 0
            ? $this->cms_entity_tags_model->get_entities_by_type($entity_master_id_ctx)
            : array();
        $this->data['entity_faqs_schema_ready'] = $this->cms_entity_faqs_model->ensure_table();
        $this->data['entity_faqs'] = $this->resolve_entity_faqs_for_form(
            $entity_master_id_ctx,
            $entity_id_ctx,
            (array) $this->input->post('entity_faqs')
        );

        if ($this->input->post('save_mapping')) {
            $this->form_validation->set_rules('entity_master_id', 'Type', 'required|integer');
            $this->form_validation->set_rules('entity_id', 'Entity', 'required|integer');
            $entity_code_save = $this->cms_entity_tags_model->get_entity_code_by_id(
                (int) $this->input->post('entity_master_id')
            );
            $tags_for_save = $entity_code_save !== ''
                ? $this->cms_entity_tags_model->get_tags_master_for_entity($entity_code_save)
                : array();
            $tag_values = (array) $this->input->post('tag_values');
            $tag_rows = $this->build_tag_rows($tag_values, $tags_for_save);
            $faq_rows = (array) $this->input->post('entity_faqs');
            if (empty($tag_rows) && !$this->has_faq_questions($faq_rows)) {
                $this->form_validation->set_rules('tag_values[]', 'Tags or FAQs', 'required');
            }

            if ($this->form_validation->run() === true) {
                $entity_master_id = (int) $this->input->post('entity_master_id');
                $entity_id = (int) $this->input->post('entity_id');
                $mapping_saved = true;
                if (!empty($tag_rows)) {
                    $mapping_saved = $this->cms_entity_tags_model->save_mapping(
                        $entity_master_id,
                        $entity_id,
                        $tag_rows
                    );
                }
                $faq_result = $this->cms_entity_faqs_model->saveFaqsForEntity(
                    $entity_master_id,
                    $entity_id,
                    $faq_rows
                );
                if ($mapping_saved && !empty($faq_result['ok'])) {
                    $message = 'Entity mapping saved successfully.';
                    if ((int) $faq_result['saved'] > 0) {
                        $message .= ' ' . $faq_result['message'];
                    }
                    $this->session->set_flashdata('message', $message);
                    $this->cms_redirect('entity_tags/edit/' . $entity_master_id . '/' . $entity_id);
                }
                $this->session->set_flashdata('error', 'Failed to save entity mapping.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => $this->cms_url('entity_tags'), 'page' => 'Entity Tag Mapping'),
            array('link' => '#', 'page' => 'Add'),
        );
        $this->data['cms_enhancements'] = true;
        $meta = array('page_title' => 'Add Entity Mapping', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/entity_tags/add', $meta, $this->data);
    }

    public function edit($entity_master_id = null, $entity_id = null)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id <= 0 || $entity_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid mapping selection.');
            $this->cms_redirect('entity_tags');
        }

        $details = $this->cms_entity_tags_model->get_mapping_details($entity_master_id, $entity_id);
        if (!$details) {
            $entity_code = $this->cms_entity_tags_model->get_entity_code_by_id($entity_master_id);
            $existing_faqs = $this->cms_entity_faqs_model->getFaqsByEntity($entity_master_id, $entity_id);
            if ($entity_code === '' || empty($existing_faqs)) {
                $this->session->set_flashdata('error', 'Mapping not found.');
                $this->cms_redirect('entity_tags');
            }
            $details = array(
                'entity_master_id' => $entity_master_id,
                'entity_id'        => $entity_id,
                'entity_code'      => $entity_code,
                'rows'             => array(),
            );
        }

        $this->data['entity_types'] = $this->cms_entity_tags_model->get_entity_types();
        $entity_code = isset($details['entity_code']) ? strtolower(trim((string) $details['entity_code'])) : '';
        $this->data['entity_scope_code'] = $entity_code;
        $this->data['tags_master'] = $entity_code !== ''
            ? $this->cms_entity_tags_model->get_tags_master_for_entity($entity_code)
            : array();
        $this->data['mapping'] = $details;
        $this->data['entity_items'] = $this->cms_entity_tags_model->get_entities_by_type($entity_master_id);

        $this->data['entity_faqs_schema_ready'] = $this->cms_entity_faqs_model->ensure_table();
        $this->data['entity_faqs'] = $this->cms_entity_faqs_model->getFaqsByEntity($entity_master_id, $entity_id);
        if ($this->input->post('update_mapping')) {
            $posted_faqs = (array) $this->input->post('entity_faqs');
            if (!empty($posted_faqs)) {
                $this->data['entity_faqs'] = $posted_faqs;
            }
        }

        if ($this->input->post('update_mapping')) {
            $this->form_validation->set_rules('entity_master_id', 'Type', 'required|integer');
            $this->form_validation->set_rules('entity_id', 'Entity', 'required|integer');
            $new_entity_code = $this->cms_entity_tags_model->get_entity_code_by_id(
                (int) $this->input->post('entity_master_id')
            );
            $tags_for_save = $new_entity_code !== ''
                ? $this->cms_entity_tags_model->get_tags_master_for_entity($new_entity_code)
                : array();
            $tag_values = (array) $this->input->post('tag_values');
            $tag_rows = $this->build_tag_rows($tag_values, $tags_for_save);
            $faq_rows = (array) $this->input->post('entity_faqs');
            if (empty($tag_rows) && !$this->has_faq_questions($faq_rows)) {
                $this->form_validation->set_rules('tag_values[]', 'Tags or FAQs', 'required');
            }

            if ($this->form_validation->run() === true) {
                $new_entity_master_id = (int) $this->input->post('entity_master_id');
                $new_entity_id = (int) $this->input->post('entity_id');
                $mapping_saved = true;
                if (!empty($tag_rows)) {
                    $mapping_saved = $this->cms_entity_tags_model->update_mapping(
                        $entity_master_id,
                        $entity_id,
                        $new_entity_master_id,
                        $new_entity_id,
                        $tag_rows
                    );
                }
                $faq_result = $this->cms_entity_faqs_model->saveFaqsForEntity(
                    $new_entity_master_id,
                    $new_entity_id,
                    $faq_rows
                );
                if (
                    $mapping_saved
                    && !empty($faq_result['ok'])
                    && ($new_entity_master_id !== $entity_master_id || $new_entity_id !== $entity_id)
                ) {
                    $this->cms_entity_faqs_model->deleteFaqsForEntity($entity_master_id, $entity_id);
                }
                if ($mapping_saved && !empty($faq_result['ok'])) {
                    $message = 'Entity mapping updated successfully.';
                    if ((int) $faq_result['saved'] > 0) {
                        $message .= ' ' . $faq_result['message'];
                    }
                    $this->session->set_flashdata('message', $message);
                    $this->cms_redirect('entity_tags/edit/' . $new_entity_master_id . '/' . $new_entity_id);
                }
                $this->session->set_flashdata('error', 'Failed to update entity mapping.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => $this->cms_url('entity_tags'), 'page' => 'Entity Tag Mapping'),
            array('link' => '#', 'page' => 'Edit'),
        );
        $this->data['cms_enhancements'] = true;
        $meta = array('page_title' => 'Edit Entity Mapping', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/entity_tags/edit', $meta, $this->data);
    }

    public function delete($entity_master_id = null, $entity_id = null)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id <= 0 || $entity_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid mapping selection.');
            $this->cms_redirect('entity_tags');
        }
        if ($this->cms_entity_tags_model->delete_mapping($entity_master_id, $entity_id)) {
            $this->cms_entity_faqs_model->deleteFaqsForEntity($entity_master_id, $entity_id);
            $this->session->set_flashdata('message', 'Entity mapping deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete entity mapping.');
        }
        $this->cms_redirect('entity_tags');
    }

    public function entities_by_type()
    {
        $entity_master_id = (int) $this->input->get('entity_master_id');
        $items = $this->cms_entity_tags_model->get_entities_by_type($entity_master_id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($items));
    }

    /**
     * AJAX: reload FAQ rows when entity Type / Entity changes.
     */
    public function faqs_form_partial()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $entity_master_id = (int) $this->input->get('entity_master_id');
        $entity_id = (int) $this->input->get('entity_id');
        $entity_faqs = array();
        if ($entity_master_id > 0 && $entity_id > 0) {
            $entity_faqs = $this->cms_entity_faqs_model->getFaqsByEntity($entity_master_id, $entity_id);
        }

        $html = $this->load->view(
            $this->theme . 'cms_admin/entity_tags/_entity_faqs_fields',
            array(
                'entity_faqs'              => $entity_faqs,
                'entity_faqs_schema_ready' => $this->cms_entity_faqs_model->ensure_table(),
            ),
            true
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'html'      => $html,
                'faq_count' => count($entity_faqs),
                'csrf_hash' => $this->security->get_csrf_hash(),
            )));
    }

    /**
     * AJAX: reload tag fields when entity Type changes (add screen).
     */
    public function tags_form_partial()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $entity_master_id = (int) $this->input->get('entity_master_id');
        $entity_id = (int) $this->input->get('entity_id');
        $entity_code = $this->cms_entity_tags_model->get_entity_code_by_id($entity_master_id);

        $existing_values = array();
        if ($entity_master_id > 0 && $entity_id > 0) {
            $details = $this->cms_entity_tags_model->get_mapping_details($entity_master_id, $entity_id);
            if (is_array($details) && !empty($details['rows'])) {
                foreach ($details['rows'] as $row) {
                    $existing_values[(int) $row['tag_id']] = (string) $row['value'];
                }
            }
        }

        $tags = $entity_code !== ''
            ? $this->cms_entity_tags_model->get_tags_master_for_entity($entity_code)
            : array();

        $import_url = site_url('cms_admin/entity_tags/ajax_import_head_tags');
        if ($entity_master_id > 0 && $entity_id > 0) {
            $import_url = site_url(
                'cms_admin/entity_tags/ajax_import_head_tags/' . $entity_master_id . '/' . $entity_id
            );
        }

        $html = $this->load->view(
            $this->theme . 'cms_admin/_partials/tag_form_fields',
            array(
                'tags_by_category'    => cms_group_tags_by_category($tags),
                'existing_values'     => $existing_values,
                'scope_form'          => 'entity',
                'scope_code'          => $entity_code,
                'scope_label'         => cms_tags_form_scope_label('entity', $entity_code),
                'head_tag_import_url' => $import_url,
            ),
            true
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'       => 'success',
                'html'         => $html,
                'entity_code'  => $entity_code,
                'tag_count'    => count($tags),
                'csrf_hash'    => $this->security->get_csrf_hash(),
            )));
    }

    /**
     * Parse pasted head HTML/scripts, auto-create tags_master rows, save entity tag values.
     *
     * @param int|null $entity_master_id
     * @param int|null $entity_id
     */
    public function ajax_import_head_tags($entity_master_id = null, $entity_id = null)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output->set_content_type('application/json');
        $entity_master_id = (int) ($entity_master_id ?: $this->input->post('entity_master_id'));
        $entity_id = (int) ($entity_id ?: $this->input->post('entity_id'));

        if ($entity_master_id <= 0 || $entity_id <= 0) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Select Type and Entity before importing head scripts.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $entities = $this->cms_entity_tags_model->get_entities_by_type($entity_master_id);
        $entity_ok = false;
        foreach ($entities as $item) {
            if ((int) $item['id'] === $entity_id) {
                $entity_ok = true;
                break;
            }
        }
        if (!$entity_ok) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid entity selection.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $this->load->helper('cms_head_tag_import');
        $this->load_cms_model('pages');

        $head_markup = cms_read_head_markup_from_post($this);
        if ($head_markup === '') {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Paste head HTML or script markup first.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $parsed = cms_parse_head_markup_for_tags($head_markup);
        if (empty($parsed)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'No recognizable SEO tags found in pasted content. Ensure markup includes tags like <title>, <meta>, <link rel="canonical">, or JSON-LD scripts.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $entity_code = $this->cms_entity_tags_model->get_entity_code_by_id($entity_master_id);
        $import_context = cms_head_import_context('entity', $entity_code);
        $created_tags = array();
        $failed_tags = array();
        $applied = array();
        $saved = 0;

        foreach ($parsed as $tag_name => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $definition = cms_tag_master_definition($tag_name);
            if (!is_array($definition) || empty($definition['tag_name'])) {
                continue;
            }
            $ensure = $this->cms_pages_model->ensureTagMaster($definition, $import_context);
            if (empty($ensure['id'])) {
                $failed_tags[] = $tag_name . (!empty($ensure['error']) ? ' (' . $ensure['error'] . ')' : '');
                continue;
            }

            if (!empty($ensure['created'])) {
                $created_tags[] = array(
                    'tag_name' => (string) $ensure['tag_name'],
                    'category' => (string) $ensure['category'],
                    'tag_id'   => (int) $ensure['id'],
                );
            }

            if ($this->cms_entity_tags_model->upsertEntityTagValue(
                $entity_master_id,
                $entity_id,
                (int) $ensure['id'],
                $tag_name,
                $value
            )) {
                $saved++;
            }
            $applied[(int) $ensure['id']] = $value;
        }

        if (empty($applied)) {
            $fail_msg = 'Parsed tags could not be saved.';
            if (!empty($failed_tags)) {
                $fail_msg .= ' Failed to create/find: ' . implode(', ', $failed_tags) . '.';
            }
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => $fail_msg,
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $message = $saved . ' tag value(s) imported.';
        if (!empty($created_tags)) {
            $names = array();
            foreach ($created_tags as $row) {
                $names[] = $row['tag_name'] . ' (' . $row['category'] . ')';
            }
            $message .= ' Auto-created missing Tag Master entries: ' . implode(', ', $names) . '.';
        }
        if (!empty($failed_tags)) {
            $message .= ' Skipped: ' . implode(', ', $failed_tags) . '.';
        }

        echo json_encode(array(
            'status'       => 'success',
            'message'      => $message,
            'created_tags' => $created_tags,
            'failed_tags'  => $failed_tags,
            'applied'      => $applied,
            'reload'       => true,
            'csrf_hash'    => $this->security->get_csrf_hash(),
        ));
    }

    private function has_faq_questions(array $faq_rows)
    {
        foreach ($faq_rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (trim((string) (isset($row['question']) ? $row['question'] : '')) !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int   $entity_master_id
     * @param int   $entity_id
     * @param array $posted_faqs
     * @return array
     */
    private function resolve_entity_faqs_for_form($entity_master_id, $entity_id, array $posted_faqs)
    {
        if (!empty($posted_faqs)) {
            return $posted_faqs;
        }
        if ($entity_master_id > 0 && $entity_id > 0) {
            return $this->cms_entity_faqs_model->getFaqsByEntity($entity_master_id, $entity_id);
        }
        return array();
    }

    private function build_tag_rows(array $tag_values, array $tags_master)
    {
        $master_by_id = array();
        foreach ($tags_master as $tag) {
            $master_by_id[(int) $tag['id']] = $tag;
        }

        $rows = array();
        foreach ($tag_values as $tag_id => $value) {
            $tag_id = (int) $tag_id;
            $value = trim((string) $value);
            if ($tag_id <= 0 || $value === '' || !isset($master_by_id[$tag_id])) {
                continue;
            }
            $rows[] = array(
                'tag_id' => $tag_id,
                'property_name' => (string) $master_by_id[$tag_id]['tag_name'],
                'value' => $value,
            );
        }
        return $rows;
    }
}
