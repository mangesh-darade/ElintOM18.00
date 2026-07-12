<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Dedicated CMS screen for multiple footer designs (storefront profiles).
 */
class Footer_designs extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('storefront');
        $this->load->helper(array('storefront', 'cms_layout'));
    }

    public function index()
    {
        redirect('cms_admin/layout_builder?tab=footer');
    }

    public function index_legacy()
    {
        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => 'Footer designs'),
        );
        $meta = array('page_title' => 'Footer designs', 'bc' => $bc);

        $this->data['profiles'] = $this->cms_storefront_model->get_footer_design_profiles();
        $this->data['schema_ready'] = $this->cms_storefront_model->schema_ready();
        $this->data['cms_list_datatable'] = false;
        $this->data['cms_footer_designs'] = true;
        $this->data['upload_base'] = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');
        $this->cms_page_construct('cms_admin/footer_designs/index', $meta, $this->data);
    }

    public function create()
    {
        redirect('cms_admin/layout_builder?tab=footer');
    }

    public function edit($profile_slug = null)
    {
        redirect('cms_admin/layout_builder?tab=footer');
    }

    public function create_legacy()
    {
        if (!$this->cms_storefront_model->schema_ready()) {
            $this->session->set_flashdata('error', 'Ensure table <code>sma_cms_webshop_header_footer</code> exists first.');
            redirect('cms_admin/footer_designs');
        }

        if ($this->input->post('save_footer_design')) {
            $slug = cms_storefront_normalize_profile_slug($this->input->post('profile_slug'));
            $label = trim((string) $this->input->post('profile_label'));

            $slug_err = cms_storefront_validate_profile_slug($slug, 'footer', false);
            if ($slug_err !== '') {
                $this->session->set_flashdata('error', $slug_err);
                redirect('cms_admin/footer_designs/create');
            }
            if ($this->cms_storefront_model->footer_profile_slug_in_use($slug)) {
                $this->session->set_flashdata('error', 'This profile slug already exists.');
                redirect('cms_admin/footer_designs/create');
            }
            if (!$this->cms_storefront_model->ensure_footer_profile_marker($slug, $label)) {
                $this->session->set_flashdata('error', 'Could not create footer design.');
                redirect('cms_admin/footer_designs/create');
            }

            // Auto-register default layout builder config so the profile works immediately
            if (!$this->cms_storefront_model->get_builder_config_row('footer', 'footer_builder_config', $slug)) {
                $this->cms_storefront_model->save_footer_builder_config(cms_footer_builder_default_config(), $slug);
            }

            if ($this->input->post('seed_starter')) {
                $this->cms_storefront_model->seed_footer_starter_items($slug);
            }

            $this->session->set_flashdata('message', 'Footer design created. Customize content and assign to a CMS page.');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($slug));
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('cms_admin/footer_designs'), 'page' => 'Footer designs'),
            array('link' => '#', 'page' => 'New design'),
        );
        $meta = array('page_title' => 'New footer design', 'bc' => $bc);
        $this->data['schema_ready'] = true;
        $this->data['cms_enhancements'] = true;
        $this->cms_page_construct('cms_admin/footer_designs/create', $meta, $this->data);
    }

    public function edit_legacy($profile_slug = null)
    {
        if (!$this->cms_storefront_model->schema_ready()) {
            $this->session->set_flashdata('error', 'Ensure table <code>sma_cms_webshop_header_footer</code> exists first.');
            redirect('cms_admin/footer_designs');
        }

        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        if ($profile === '') {
            $this->session->set_flashdata('error', 'Invalid footer design.');
            redirect('cms_admin/footer_designs');
        }

        if ($this->input->post('save_profile_label')) {
            $label = trim((string) $this->input->post('profile_label'));
            $marker = $this->cms_storefront_model->get_footer_profile_marker($profile);
            if ($marker && !empty($marker['id'])) {
                $this->cms_storefront_model->update_row((int) $marker['id'], array('label' => $label));
                $this->session->set_flashdata('message', 'Design name updated.');
            } else {
                $this->cms_storefront_model->ensure_footer_profile_marker($profile, $label);
                $this->session->set_flashdata('message', 'Design registered.');
            }
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
        }

        if ($this->input->post('save_footer_item')) {
            $this->save_footer_item($profile);
            return;
        }

        if ($this->input->post('duplicate_footer_design')) {
            $this->duplicate_footer_design($profile);
            return;
        }

        $marker = $this->cms_storefront_model->get_footer_profile_marker($profile);
        $profile_label = $profile;
        if ($marker && !empty($marker['label'])) {
            $profile_label = (string) $marker['label'];
        } elseif ($profile !== 'default') {
            $profile_label = ucfirst(str_replace(array('-', '_'), ' ', $profile));
        } else {
            $profile_label = 'Default';
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('cms_admin/footer_designs'), 'page' => 'Footer designs'),
            array('link' => '#', 'page' => $profile_label),
        );
        $meta = array('page_title' => 'Edit footer design: ' . $profile_label, 'bc' => $bc);

        $upload_base = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');
        $this->data['profile_slug'] = $profile;
        $this->data['profile_label'] = $profile_label;
        $this->data['items'] = $this->cms_storefront_model->get_footer_design_items($profile);
        $this->data['has_marker'] = (bool) $marker;
        $this->data['cms_enhancements'] = true;
        $this->data['upload_base'] = $upload_base;
        $this->data['preview_url'] = site_url('cms_admin/footer_designs/preview/' . rawurlencode($profile));
        $this->data['preview_strip_html'] = cms_footer_design_preview_strip_html($profile, $upload_base);
        $this->data['cms_footer_designs_editor'] = true;
        $this->data['cms_footer_designs'] = true;
        $this->load_cms_model('pages');
        $this->cms_pages_model->ensureHeaderFooterSectionMasters();
        $this->data['cms_pages_for_assign'] = $this->cms_pages_model->getAdminPages();
        $this->data['pages_using_design'] = $this->cms_pages_model->getPagesUsingStorefrontProfile('footer', $profile);
        $this->cms_page_construct('cms_admin/footer_designs/edit', $meta, $this->data);
    }

    /**
     * Step 3: attach this footer design to a CMS page (one click).
     *
     * @param string|null $profile_slug
     */
    public function assign_to_page($profile_slug = null)
    {
        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        if ($profile === '') {
            $this->session->set_flashdata('error', 'Invalid footer design.');
            redirect('cms_admin/footer_designs');
        }

        $page_id = (int) $this->input->post('page_id');
        $this->load_cms_model('pages');
        $result = $this->cms_pages_model->assignStorefrontProfileSection($page_id, 'footer', $profile);

        if (!empty($result['ok'])) {
            $this->session->set_flashdata('message', $result['message'] . ' You can review sections on the page editor.');
            redirect('cms_admin/pages/edit/' . $page_id);
        }

        $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Could not assign design.');
        redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
    }

    public function preview($profile_slug = null)
    {
        if (!$this->cms_storefront_model->schema_ready()) {
            show_404();
        }

        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        if ($profile === '') {
            show_404();
        }

        $marker = $this->cms_storefront_model->get_footer_profile_marker($profile);
        $label = $profile;
        if ($marker && !empty($marker['label'])) {
            $label = (string) $marker['label'];
        } elseif ($profile === 'default') {
            $label = 'Default';
        }

        $upload_base = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');
        $compact = $this->input->get('compact') === '1';

        $this->output
            ->set_content_type('text/html; charset=UTF-8')
            ->set_output(cms_footer_design_preview_document($profile, $label, $upload_base, $compact));
    }

    public function preview_data($profile_slug = null)
    {
        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        if ($profile === '' || !$this->cms_storefront_model->schema_ready()) {
            $this->output->set_status_header(400)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Invalid design.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $upload_base = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');
        $items = $this->cms_storefront_model->get_footer_design_items($profile);

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'      => 'success',
                'strip_html'  => cms_footer_design_preview_strip_html($profile, $upload_base),
                'item_count'  => count($items),
                'preview_url' => site_url('cms_admin/footer_designs/preview/' . rawurlencode($profile)),
                'csrf_hash'   => $this->security->get_csrf_hash(),
            )));
    }

    private function duplicate_footer_design($from_profile)
    {
        $new_slug = cms_storefront_normalize_profile_slug($this->input->post('duplicate_slug'));
        $new_label = trim((string) $this->input->post('duplicate_label'));

        if ($new_slug === '') {
            $this->session->set_flashdata('error', 'New design slug is required.');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($from_profile));
        }
        if ($this->cms_storefront_model->footer_profile_slug_in_use($new_slug)) {
            $this->session->set_flashdata('error', 'That slug is already in use.');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($from_profile));
        }
        if (!$this->cms_storefront_model->duplicate_footer_profile($from_profile, $new_slug, $new_label)) {
            $this->session->set_flashdata('error', 'Could not duplicate design.');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($from_profile));
        }

        $this->session->set_flashdata('message', 'Design duplicated. You are now editing the copy.');
        redirect('cms_admin/footer_designs/edit/' . rawurlencode($new_slug));
    }

    private function save_footer_item($profile)
    {
        $item_id = (int) $this->input->post('item_id');
        $item_key = trim((string) $this->input->post('item_key'));
        $label = trim((string) $this->input->post('label'));
        $value = (string) $this->input->post('value');
        $icons = trim((string) $this->input->post('icons'));
        $sort_order = (int) $this->input->post('sort_order');
        $is_active = $this->input->post('is_active') ? 1 : 0;

        if ($item_id <= 0 && $item_key === '') {
            $this->session->set_flashdata('error', 'Item key is required for new rows (e.g. footer_tagline, footer_copyright).');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
        }

        $field_key = '';
        if ($item_id > 0) {
            $existing = $this->cms_storefront_model->get_by_id($item_id);
            if (!$existing || strtolower((string) $existing['section_type']) !== 'footer') {
                $this->session->set_flashdata('error', 'Item not found.');
                redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
            }
            $field_key = isset($existing['field_key']) ? (string) $existing['field_key'] : '';
        } else {
            $field_key = $this->cms_storefront_model->build_footer_profile_field_key($profile, $item_key);
            if ($field_key === '') {
                $this->session->set_flashdata('error', 'Invalid item key.');
                redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
            }
            if ($this->cms_storefront_model->field_key_exists('footer', $field_key)) {
                $this->session->set_flashdata('error', 'This item key already exists for footer.');
                redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
            }
        }

        $stored_value = storefront_sanitize_field_value($field_key, $value);
        if ($stored_value === false) {
            $this->session->set_flashdata('error', 'Invalid tracking snippet in value.');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
        }

        if (!empty($_FILES['media_file']['name'])) {
            $up = $this->do_upload('media_file', '', true);
            if ($up['status'] === 'success') {
                $stored_value = 'webshop/' . $up['upload_data']['file_name'];
            } else {
                $this->session->set_flashdata('error', strip_tags($up['error']));
                redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
            }
        }

        $label = storefront_resolve_storefront_label($label, $stored_value, $field_key);
        $this->cms_storefront_model->ensure_footer_profile_marker($profile);

        $payload = array(
            'section_type' => 'footer',
            'field_key'    => $field_key,
            'label'        => $label,
            'value'        => $stored_value,
            'icons'        => $icons === '' ? null : $icons,
            'sort_order'   => $sort_order > 0 ? $sort_order : 100,
            'is_active'    => $is_active,
        );

        if ($item_id > 0) {
            unset($payload['field_key'], $payload['section_type']);
            $ok = $this->cms_storefront_model->update_row($item_id, $payload);
            $this->session->set_flashdata($ok ? 'message' : 'error', $ok ? 'Item updated.' : 'Could not update item.');
        } else {
            $ok = $this->cms_storefront_model->insert_row($payload);
            $this->session->set_flashdata($ok ? 'message' : 'error', $ok ? 'Item added.' : 'Could not add item.');
        }

        redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
    }

    public function delete_item($profile_slug = null, $id = null)
    {
        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        $id = (int) $id;
        if ($profile === '' || $id <= 0) {
            $this->session->set_flashdata('error', 'Invalid request.');
            redirect('cms_admin/footer_designs');
        }
        $row = $this->cms_storefront_model->get_by_id($id);
        if (!$row || !$this->cms_storefront_model->row_belongs_to_profile('footer', $profile, isset($row['field_key']) ? $row['field_key'] : '')) {
            $this->session->set_flashdata('error', 'Item does not belong to this design.');
            redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
        }
        if ($this->cms_storefront_model->delete_row($id)) {
            $this->session->set_flashdata('message', 'Item removed.');
        } else {
            $this->session->set_flashdata('error', 'Could not delete item.');
        }
        redirect('cms_admin/footer_designs/edit/' . rawurlencode($profile));
    }

    public function delete_profile($profile_slug = null)
    {
        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        if ($profile === '' || $profile === 'default') {
            $this->session->set_flashdata('error', 'Cannot delete the default profile.');
            redirect('cms_admin/footer_designs');
        }
        $this->load_cms_model('pages');
        $in_use = $this->cms_pages_model->getPagesUsingStorefrontProfile('footer', $profile);
        if (!empty($in_use)) {
            $this->session->set_flashdata('error', 'This design is assigned to ' . count($in_use) . ' CMS page(s). Remove it from those pages first.');
            redirect('cms_admin/footer_designs');
        }
        if ($this->cms_storefront_model->delete_footer_profile($profile)) {
            $this->session->set_flashdata('message', 'Footer design deleted.');
        } else {
            $this->session->set_flashdata('error', 'Could not delete design.');
        }
        redirect('cms_admin/footer_designs');
    }

    public function toggle_item_active($profile_slug = null, $id = null)
    {
        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        $id = (int) $id;
        if ($profile === '' || $id <= 0) {
            $this->output->set_status_header(400)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Invalid item.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $row = $this->cms_storefront_model->get_by_id($id);
        if (!$row) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Not found.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $is_active = $this->input->post('is_active') ? 1 : 0;
        if (!$this->cms_storefront_model->set_active($id, $is_active)) {
            $this->output->set_status_header(500)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Update failed.', 'csrf_hash' => $this->security->get_csrf_hash())));
            return;
        }

        $upload_base = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'      => 'success',
                'is_active'   => $is_active,
                'strip_html'  => cms_footer_design_preview_strip_html($profile, $upload_base),
                'csrf_hash'   => $this->security->get_csrf_hash(),
            )));
    }
}
