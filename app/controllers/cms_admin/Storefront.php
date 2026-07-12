<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Storefront extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('storefront');
        $this->load->helper('storefront');
    }

    /**
     * @param string $field_key
     * @param string $value
     * @return string|false Normalized value, or false when GA field is invalid.
     */
    private function prepare_storefront_value($field_key, $value)
    {
        return storefront_sanitize_field_value($field_key, (string) $value);
    }

    public function index() {
        $rows = $this->cms_storefront_model->get_all_rows();

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => 'Storefront header & footer'),
        );
        $meta = array('page_title' => 'Storefront header & footer', 'bc' => $bc);

        $this->data['identity_rows'] = $rows;
        $this->data['header_footer_schema_ready'] = $this->cms_storefront_model->schema_ready();
        $this->cms_page_construct('cms_admin/storefront/index', $meta, $this->data);
    }

    public function add() {
        if ($this->input->post('save_identity_row')) {
            if (!$this->cms_storefront_model->schema_ready()) {
                $this->session->set_flashdata('error', 'Database tables are missing. Ensure <code>sma_cms_webshop_header_footer</code> exists, then retry.');
                redirect('cms_admin/storefront/add');
            }

            $section_type = strtolower(trim((string) $this->input->post('section_type')));
            $field_key    = storefront_normalize_field_key($this->input->post('field_key'));
            $label        = trim((string) $this->input->post('label'));
            $value        = (string) $this->input->post('value');
            $icons        = trim((string) $this->input->post('icons'));
            $sort_order   = (int) $this->input->post('sort_order');
            $is_active    = $this->input->post('is_active') ? 1 : 0;

            if ($section_type !== 'header' && $section_type !== 'footer') {
                $this->session->set_flashdata('error', 'Choose a valid section (Header or Footer).');
                redirect('cms_admin/storefront/add');
            }
            if ($field_key === '') {
                $this->session->set_flashdata('error', 'Field key is required (max 64 characters).');
                redirect('cms_admin/storefront/add');
            }
            if ($this->cms_storefront_model->field_key_exists($section_type, $field_key)) {
                $this->session->set_flashdata('error', 'This section and field key already exists.');
                redirect('cms_admin/storefront/add');
            }

            $stored_value = $this->prepare_storefront_value($field_key, $value);
            if ($stored_value === false) {
                $this->session->set_flashdata('error', 'Invalid Google Analytics ID. Use a value like ' . storefront_ga_example_measurement_id() . ' or paste the full gtag snippet.');
                redirect('cms_admin/storefront/add');
            }
            if (!empty($_FILES['media_file']['name'])) {
                $up = $this->do_upload('media_file', '', true);
                if ($up['status'] === 'success') {
                    $stored_value = 'webshop/' . $up['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($up['error']));
                    redirect('cms_admin/storefront/add');
                }
            }

            $label = storefront_resolve_storefront_label($label, $stored_value, $field_key);

            $insert = array(
                'section_type' => $section_type,
                'field_key'    => $field_key,
                'label'        => $label,
                'value'        => $stored_value,
                'icons'        => $icons === '' ? null : $icons,
                'sort_order'   => $sort_order,
                'is_active'    => $is_active,
            );

            if ($this->cms_storefront_model->insert_row($insert)) {
                $this->session->set_flashdata('message', $this->storefront_saved_flash_message($stored_value));
                redirect('cms_admin/storefront');
            }
            $this->session->set_flashdata('error', 'Could not save row.');
            redirect('cms_admin/storefront/add');
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('cms_admin/storefront'), 'page' => 'Storefront header & footer'),
            array('link' => '#', 'page' => 'Add row'),
        );
        $meta = array('page_title' => 'Add storefront row', 'bc' => $bc);
        $this->assign_storefront_form_view_data();
        $this->cms_page_construct('cms_admin/storefront/form', $meta, $this->data);
    }

    public function edit($id = null) {
        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid row.');
            redirect('cms_admin/storefront');
        }

        $row = $this->cms_storefront_model->get_by_id($id);
        if (!$row) {
            $this->session->set_flashdata('error', 'Row not found.');
            redirect('cms_admin/storefront');
        }

        if ($this->input->post('save_identity_row')) {
            if (!$this->cms_storefront_model->schema_ready()) {
                $this->session->set_flashdata('error', 'Database tables are missing. Ensure <code>sma_cms_webshop_header_footer</code> exists, then retry.');
                redirect('cms_admin/storefront/edit/' . $id);
            }

            $section_type = strtolower(trim((string) $this->input->post('section_type')));
            $field_key    = storefront_normalize_field_key(isset($row['field_key']) ? $row['field_key'] : '');
            $label        = trim((string) $this->input->post('label'));
            $value        = (string) $this->input->post('value');
            $icons        = trim((string) $this->input->post('icons'));
            $sort_order   = (int) $this->input->post('sort_order');
            $is_active    = $this->input->post('is_active') ? 1 : 0;

            if ($section_type !== 'header' && $section_type !== 'footer') {
                $this->session->set_flashdata('error', 'Choose a valid section (Header or Footer).');
                redirect('cms_admin/storefront/edit/' . $id);
            }
            if ($field_key === '') {
                $this->session->set_flashdata('error', 'Field key is required (max 64 characters).');
                redirect('cms_admin/storefront/edit/' . $id);
            }
            if ($this->cms_storefront_model->field_key_exists($section_type, $field_key, $id)) {
                $this->session->set_flashdata('error', 'This section and field key already exists.');
                redirect('cms_admin/storefront/edit/' . $id);
            }

            $stored_value = $this->prepare_storefront_value($field_key, $value);
            if ($stored_value === false) {
                $this->session->set_flashdata('error', 'Invalid Google Analytics ID. Use a value like ' . storefront_ga_example_measurement_id() . ' or paste the full gtag snippet.');
                redirect('cms_admin/storefront/edit/' . $id);
            }
            if (!empty($_FILES['media_file']['name'])) {
                $up = $this->do_upload('media_file', '', true);
                if ($up['status'] === 'success') {
                    $stored_value = 'webshop/' . $up['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($up['error']));
                    redirect('cms_admin/storefront/edit/' . $id);
                }
            }

            $label = storefront_resolve_storefront_label($label, $stored_value, $field_key);

            $update = array(
                'section_type' => $section_type,
                'field_key'    => $field_key,
                'label'        => $label,
                'value'        => $stored_value,
                'icons'        => $icons === '' ? null : $icons,
                'sort_order'   => $sort_order,
                'is_active'    => $is_active,
            );

            if ($this->cms_storefront_model->update_row($id, $update)) {
                $this->session->set_flashdata('message', $this->storefront_saved_flash_message($stored_value));
                redirect('cms_admin/storefront');
            }
            $this->session->set_flashdata('error', 'Could not update row.');
            redirect('cms_admin/storefront/edit/' . $id);
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('cms_admin/storefront'), 'page' => 'Storefront header & footer'),
            array('link' => '#', 'page' => 'Edit row'),
        );
        $meta = array('page_title' => 'Edit storefront row', 'bc' => $bc);
        $this->data['identity_row'] = $row;
        $this->assign_storefront_form_view_data();
        $this->cms_page_construct('cms_admin/storefront/form', $meta, $this->data);
    }

    /**
     * @param string $stored_value
     * @return string
     */
    private function storefront_saved_flash_message($stored_value)
    {
        $msg = 'Storefront content saved.';
        if (storefront_normalize_ga_measurement_id($stored_value) !== '') {
            $msg .= ' Google Analytics will load in the webshop header.';
        }
        return $msg;
    }

    private function assign_storefront_form_view_data()
    {
        if (!isset($this->data['identity_row'])) {
            $this->data['identity_row'] = null;
        }
        $this->data['header_footer_schema_ready'] = $this->cms_storefront_model->schema_ready();
        $this->data['cms_enhancements'] = true;
    }

    public function delete($id = null) {
        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid row.');
            redirect('cms_admin/storefront');
        }
        if ($this->cms_storefront_model->delete_row($id)) {
            $this->session->set_flashdata('message', 'Row deleted.');
        } else {
            $this->session->set_flashdata('error', 'Could not delete row.');
        }
        redirect('cms_admin/storefront');
    }

    public function toggle_active($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            $this->output->set_status_header(400)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Invalid row.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $row = $this->cms_storefront_model->get_by_id($id);
        if (!$row) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Row not found.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $is_active = $this->input->post('is_active') ? 1 : 0;
        if (!$this->cms_storefront_model->set_active($id, $is_active)) {
            $this->output->set_status_header(500)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Failed to update status.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'success',
                'is_active' => $is_active,
                'csrf_hash' => $this->security->get_csrf_hash(),
            )));
    }
}
