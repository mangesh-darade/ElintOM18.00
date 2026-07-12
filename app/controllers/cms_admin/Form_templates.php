<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Standalone form template builder (fields + design/CSS).
 */
class Form_templates extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('form_templates');
    }

    public function index()
    {
        $rows = $this->cms_form_templates_model->list_templates($this->cms_form_templates_model->theme_slug($this->Settings));
        foreach ($rows as $i => $row) {
            $cfg = isset($row['config']) && is_array($row['config']) ? $row['config'] : array();
            $rows[$i]['design_info'] = $this->cms_form_templates_model->design_info_from_config($cfg);
            $rows[$i]['embed_links'] = $this->cms_form_templates_model->build_embed_links($row);
        }
        $this->data['rows'] = $rows;
        $this->data['schema_ready'] = $this->cms_form_templates_model->ensure_table();

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'Form Templates'),
        );
        $meta = array('page_title' => 'Form Templates', 'bc' => $bc);
        $this->data['cms_list_datatable'] = true;
        $this->cms_page_construct('cms_admin/form_templates/index', $meta, $this->data);
    }

    public function add()
    {
        $this->edit_form(0);
    }

    public function edit($id = null)
    {
        $this->edit_form((int) $id);
    }

    /**
     * @param int $id
     */
    protected function edit_form($id)
    {
        $id = (int) $id;
        $is_new = $id <= 0;
        $template = $is_new ? null : $this->cms_form_templates_model->get_template($id);

        if (!$is_new && !$template) {
            $this->session->set_flashdata('error', 'Form template not found.');
            $this->cms_redirect('form_templates');
        }

        $repopulate_from_post = false;
        if ($this->input->post('save_form_template')) {
            $result = $this->save_from_post($id);
            if (!empty($result['ok'])) {
                $this->session->set_flashdata('message', isset($result['message']) ? $result['message'] : 'Saved.');
                $redirect_id = isset($result['id']) ? (int) $result['id'] : $id;
                $this->cms_redirect('form_templates/edit/' . $redirect_id);
            }
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Save failed.');
            $repopulate_from_post = true;
        }

        if ($repopulate_from_post) {
            $this->data['template'] = $this->template_from_post($id);
        } elseif ($is_new) {
            $defaults = $this->cms_form_templates_model->default_config();
            $this->data['template'] = array(
                'id'         => 0,
                'form_key'   => '',
                'form_name'  => '',
                'page_url'   => '',
                'is_active'  => true,
                'config'     => $defaults,
            );
        } else {
            $this->data['template'] = $template;
        }

        $this->data['schema_ready'] = $this->cms_form_templates_model->ensure_table();
        $this->data['style_presets'] = $this->cms_form_templates_model->style_presets();
        $this->data['is_new'] = $is_new;
        $this->load->helper(array('contact_form_phone', 'contact_form_country'));
        $this->data['contact_form_phone_countries'] = contact_form_phone_countries();
        $this->data['contact_form_countries'] = contact_form_country_list();

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => $this->cms_url('form_templates'), 'page' => 'Form Templates'),
            array('link' => '#', 'page' => $is_new ? 'Add' : 'Edit'),
        );
        $meta = array('page_title' => $is_new ? 'Add Form Template' : 'Edit Form Template', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/form_templates/edit', $meta, $this->data);
    }

    public function duplicate($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid template.');
            $this->cms_redirect('form_templates');
        }
        $result = $this->cms_form_templates_model->duplicate_template($id);
        if (!empty($result['ok']) && !empty($result['id'])) {
            $this->session->set_flashdata('message', isset($result['message']) ? $result['message'] : 'Template duplicated.');
            $this->cms_redirect('form_templates/edit/' . (int) $result['id']);
        }
        $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Duplicate failed.');
        $this->cms_redirect('form_templates');
    }

    public function delete($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid template.');
            $this->cms_redirect('form_templates');
        }
        $result = $this->cms_form_templates_model->delete_template($id);
        if (!empty($result['ok'])) {
            $this->session->set_flashdata('message', isset($result['message']) ? $result['message'] : 'Deleted.');
        } else {
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Delete failed.');
        }
        $this->cms_redirect('form_templates');
    }

    /**
     * JSON embed / share links for a template (index modal).
     *
     * @param int $id
     */
    public function ajax_embed_links($id = null)
    {
        $this->cms_release_session_lock();
        $id = (int) $id;
        $template = $this->cms_form_templates_model->get_template($id);
        if (!$template) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'message' => 'Template not found')));
            return;
        }

        $links = $this->cms_form_templates_model->build_embed_links($template);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'ok'    => true,
                'id'    => (int) $template['id'],
                'links' => $links,
            ), JSON_UNESCAPED_UNICODE));
    }

    /**
     * Decode the builder JSON from POST into a config array.
     *
     * @return array<string,mixed>
     */
    protected function config_from_post()
    {
        $fields = json_decode((string) $this->input->post('contact_form_fields_json', false), true);
        $style = json_decode((string) $this->input->post('form_template_style_json', false), true);

        return $this->cms_form_templates_model->build_config(array(
            'title'                => $this->input->post('form_template_title'),
            'subtitle'             => $this->input->post('contact_form_subtitle'),
            'button_text'          => $this->input->post('contact_form_button_text'),
            'source'               => $this->input->post('contact_form_source'),
            'fields'               => is_array($fields) ? $fields : array(),
            'style'                => is_array($style) ? $style : array(),
            'recaptcha_site_key'   => $this->input->post('recaptcha_site_key'),
            'recaptcha_secret_key' => $this->input->post('recaptcha_secret_key'),
        ));
    }

    /**
     * Rebuild template view data from POST after a failed save.
     *
     * @param int $id
     * @return array<string,mixed>
     */
    protected function template_from_post($id)
    {
        return array(
            'id'         => (int) $id,
            'form_key'   => (string) $this->input->post('form_key'),
            'form_name'  => (string) $this->input->post('form_name'),
            'page_url'   => (string) $this->input->post('page_url'),
            'is_active'  => $this->input->post('is_active') ? true : false,
            'config'     => $this->config_from_post(),
        );
    }

    /**
     * @param int $id
     * @return array{ok:bool,message?:string,id?:int}
     */
    protected function save_from_post($id)
    {
        return $this->cms_form_templates_model->save_template($id, array(
            'theme_slug' => $this->cms_form_templates_model->theme_slug($this->Settings),
            'form_key'   => $this->input->post('form_key'),
            'form_name'  => $this->input->post('form_name'),
            'page_url'   => $this->input->post('page_url'),
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
            'config'     => $this->config_from_post(),
        ));
    }
}
