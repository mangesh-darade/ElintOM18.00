<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Pages extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('pages');
        $this->load->helper('cms_layout');
        $this->load->helper('cms_tags');
    }

    /**
     * Redirect back to CMS page edit with optional publish/edit-mode query flags.
     *
     * @param int   $page_id
     * @param array $options saved (bool), edit_mode (bool) — shows Save + page fields
     */
    protected function cms_page_edit_redirect($page_id, array $options = array())
    {
        $page_id = (int) $page_id;
        $params = array();

        if (!empty($options['saved'])) {
            $params['saved'] = '1';
            $this->session->set_flashdata('cms_page_saved', 1);
            $this->cms_bust_storefront_cms_cache();
        }
        if (!empty($options['edit_mode'])) {
            $params['unpublished'] = '1';
            $this->session->set_flashdata('cms_page_unpublished', 1);
            if (empty($params['saved'])) {
                $params['saved'] = '1';
            }
        }

        $query = empty($params) ? '' : '?' . http_build_query($params);
        redirect('cms_admin/pages/edit/' . $page_id . $query);
    }

    public function index() {
        $this->cms_pages_model->ensureNavOrderColumn();
        $this->cms_pages_model->ensureNavVisibilityColumns();
        $this->cms_pages_model->backfillNavOrdersIfNeeded();
        $cms_pages = $this->cms_pages_model->getAdminPages();

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => lang('CMS Pages'))
        );
        $meta = array('page_title' => lang('CMS Pages'), 'bc' => $bc);

        $this->data['cms_pages'] = $cms_pages;
        $this->cms_page_construct('cms_admin/pages/index', $meta, $this->data);
    }
    public function add() {
        if ($this->input->post('create_cms_page')) {
            $name = trim((string) $this->input->post('page_name'));
            $url = '/' . ltrim(trim((string) $this->input->post('url')), '/');
            $parent_page_id = (int) $this->input->post('parent_page_id');
            $submenu_order = (int) $this->input->post('submenu_order');
            if ($submenu_order < 0) {
                $submenu_order = 0;
            }
            if ($name === '' || $url === '/') {
                $this->session->set_flashdata('error', 'Please provide valid page details.');
                redirect('cms_admin/pages/add');
            }

            if (!$this->cms_pages_model->ensurePageMediaColumns()) {
                $this->session->set_flashdata('error', 'Failed to prepare CMS media columns in pages table.');
                redirect('cms_admin/pages/add');
            }

            $this->cms_pages_model->ensureNavOrderColumn();
            $this->cms_pages_model->ensureParentPageColumn();
            $this->cms_pages_model->ensureSubmenuOrderColumn();
            if ($parent_page_id > 0 && !$this->cms_pages_model->getPageById($parent_page_id)) {
                $this->session->set_flashdata('error', 'Selected parent page not found.');
                redirect('cms_admin/pages/add');
            }
            $insert_data = array(
                'page_name' => $name,
                'url'       => $url,
                'status'    => 'draft',
            );
            $this->cms_pages_model->ensurePageTypeColumn();
            if ($this->cms_pages_model->hasPageColumn('page_type')) {
                $insert_data['page_type'] = 'static';
            }
            if ($this->cms_pages_model->hasPageColumn('parent_page_id')) {
                $insert_data['parent_page_id'] = $parent_page_id > 0 ? $parent_page_id : null;
            }
            if ($this->cms_pages_model->hasPageColumn('submenu_order')) {
                $insert_data['submenu_order'] = $parent_page_id > 0 ? $submenu_order : 0;
            }
            if ($this->cms_pages_model->hasPageColumn('nav_order')) {
                $insert_data['nav_order'] = $this->cms_pages_model->getNextNavOrder();
            }

            if ($this->cms_pages_model->hasPageColumn('banner_image')) {
                if (!empty($_FILES['banner_image']['name'])) {
                    $banner_upload = $this->do_upload('banner_image', 'cms_pages');
                    if ($banner_upload['status'] === 'success') {
                        $insert_data['banner_image'] = $banner_upload['upload_data']['file_name'];
                    } else {
                        $this->session->set_flashdata('error', strip_tags($banner_upload['error']));
                        redirect('cms_admin/pages/add');
                    }
                } else {
                    $this->load->helper('cms_media');
                    $banner_media_path = cms_media_normalize_stored_path($this->input->post('banner_media_path'));
                    if ($banner_media_path !== '') {
                        $insert_data['banner_image'] = $banner_media_path;
                    }
                }
            }
            $new_id = $this->cms_pages_model->addPage($insert_data);
            if ($new_id) {
                $this->session->set_flashdata('message', 'CMS page created successfully.');
                $this->cms_bust_storefront_cms_cache();
                redirect('cms_admin/pages/edit/' . (int) $new_id . '?saved=1');
            }
            $this->session->set_flashdata('error', 'Failed to create CMS page.');
            redirect('cms_admin/pages/add');
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('cms_admin/pages'), 'page' => lang('CMS Pages')),
            array('link' => '#', 'page' => lang('Add CMS Page'))
        );
        $meta = array('page_title' => lang('Add CMS Page'), 'bc' => $bc);
        $this->data['parent_page_options'] = $this->cms_pages_model->getParentPageOptions();
        $this->cms_page_construct('cms_admin/pages/add', $meta, $this->data);
    }

    public function delete($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('cms_admin/pages');
        }
        $page_data = $this->cms_pages_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('cms_admin/pages');
        }

        $this->cms_pages_model->deletePageSectionsByPageId($page_id);
        if ($this->cms_pages_model->deletePageById($page_id)) {
            $this->session->set_flashdata('message', 'CMS page deleted successfully.');
            $this->cms_bust_storefront_cms_cache();
        } else {
            $this->session->set_flashdata('error', 'Failed to delete CMS page.');
        }
        redirect('cms_admin/pages');
    }

    public function remove_media($page_id = null, $media_type = '') {
        $page_id = (int) $page_id;
        $media_type = strtolower(trim((string) $media_type));
        if ($page_id <= 0 || $media_type !== 'banner') {
            $this->session->set_flashdata('error', 'Invalid media remove request.');
            redirect('cms_admin/pages');
        }
        $page_data = $this->cms_pages_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('cms_admin/pages');
        }
        $column = 'banner_image';
        if (!$this->cms_pages_model->hasPageColumn($column)) {
            $this->session->set_flashdata('error', 'Media column not available.');
            redirect('cms_admin/pages/edit/' . $page_id);
        }
        if ($this->cms_pages_model->updatePageById($page_id, array($column => null))) {
            $this->session->set_flashdata('message', ucfirst($media_type) . ' removed successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to remove ' . $media_type . '.');
        }
        $preserve_edit = (int) $this->input->get('unpublished') === 1;
        $this->cms_page_edit_redirect($page_id, array(
            'saved'     => true,
            'edit_mode' => $preserve_edit,
        ));
    }
    public function edit($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('cms_admin/pages');
        }

        if ($this->input->post('save_cms_tags')) {
            $this->load->helper('cms_tags');
            $tag_values = $this->input->post('tag_values', false);
            if (!is_array($tag_values)) {
                $tag_values = array();
            }

            $page_type = $this->cms_pages_model->getPageTypeForPage($page_id);
            $tags_master = $this->cms_pages_model->getTagsMasterForPage($page_type);
            $master_by_id = cms_tags_master_index_by_id($tags_master);

            $saved = 0;
            foreach ($tag_values as $tag_id => $value) {
                $tag_id = (int) $tag_id;
                $value = trim((string) $value);
                if ($tag_id <= 0 || $value === '' || !isset($master_by_id[$tag_id])) {
                    continue;
                }

                $property_name = $master_by_id[$tag_id]['tag_name'];
                if ($this->cms_pages_model->upsertPageTagValue($page_id, $tag_id, $property_name, $value)) {
                    $saved++;
                }
            }

            if ($saved > 0) {
                $this->session->set_flashdata('message', 'Tag values saved successfully.');
            } else {
                $this->session->set_flashdata('warning', 'No tag values were saved.');
            }
            if ($saved > 0) {
                $this->cms_bust_storefront_cms_cache();
            }
            redirect('cms_admin/pages/edit/' . $page_id . '?saved=1');
        }

        if ($this->input->post('save_cms_page_faqs')) {
            $this->load_cms_model('pages_faqs');
            $raw_faqs = $this->input->post('page_faqs', false);
            if (!is_array($raw_faqs)) {
                $raw_faqs = array();
            }
            $result = $this->cms_pages_faqs_model->saveFaqsForPage($page_id, $raw_faqs);
            if (!empty($result['ok'])) {
                $this->session->set_flashdata('message', isset($result['message']) ? $result['message'] : 'FAQs saved.');
            } else {
                $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Failed to save FAQs.');
            }
            $this->cms_page_edit_redirect($page_id, array('saved' => true));
        }

        if ($this->input->post('save_page_chrome_designs')) {
            $message = $this->savePageChromeDesignsFromPost($page_id);
            if ($message !== '') {
                $this->session->set_flashdata('message', $message);
            }
            $this->cms_page_edit_redirect($page_id, array('saved' => true));
        }

        if ($this->input->post('publish_page') || $this->input->post('unpublish_page')) {
            $page_data = $this->cms_pages_model->getPageById($page_id);
            if (!$page_data) {
                $this->session->set_flashdata('error', 'CMS page not found.');
                redirect('cms_admin/pages');
            }

            $is_unpublish = (bool) $this->input->post('unpublish_page');
            $new_status = $is_unpublish ? 'draft' : 'published';
            $updated = $this->cms_pages_model->updatePageById($page_id, array('status' => $new_status));
            $message = '';
            if ($updated) {
                $label = $new_status === 'published' ? 'published' : 'unpublished (draft)';
                $message = 'Page ' . $label . ' successfully.';
                if (!$is_unpublish) {
                    $chrome_msg = $this->savePageChromeDesignsFromPost($page_id);
                    if ($chrome_msg !== '') {
                        $message .= ' ' . $chrome_msg;
                    }
                }
                $this->session->set_flashdata('message', $message);
            } else {
                $this->session->set_flashdata('error', 'Failed to update page status.');
            }
            $this->cms_page_edit_redirect($page_id, array(
                'saved'     => true,
                'edit_mode' => ($updated && $is_unpublish),
            ));
        }

        if ($this->input->post('update_cms_page')) {
            $in_edit_mode = in_array($this->input->post('cms_edit_mode'), array('1', 1, true), true);
            if (!$in_edit_mode) {
                $chrome_msg = $this->savePageChromeDesignsFromPost($page_id);
                if ($chrome_msg !== '') {
                    $this->session->set_flashdata('message', $chrome_msg);
                } else {
                    $this->session->set_flashdata('warning', 'Unpublish the page first to edit page name, URL, and banner. Use "Save header & footer design" for layout assignment.');
                }
                $this->cms_page_edit_redirect($page_id, array('saved' => true));
            }

            $name = trim($this->input->post('page_name'));
            $url_segment = trim($this->input->post('url'));
            $url = ($url_segment === '') ? '/' : '/' . ltrim($url_segment, '/');
            $parent_page_id = (int) $this->input->post('parent_page_id');
            $submenu_order = (int) $this->input->post('submenu_order');
            if ($submenu_order < 0) {
                $submenu_order = 0;
            }

            if (!$this->cms_pages_model->ensurePageMediaColumns()) {
                $this->session->set_flashdata('error', 'Failed to prepare CMS media columns in pages table.');
                $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
            }

            if ($name === '' || $url === '') {
                $chrome_msg = $this->savePageChromeDesignsFromPost($page_id);
                $this->session->set_flashdata('error', 'Please provide valid page details.' . ($chrome_msg !== '' ? ' ' . $chrome_msg : ''));
                $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
            }

            $update_data = array(
                'page_name' => $name,
                'url'       => $url,
            );
            $this->cms_pages_model->ensureParentPageColumn();
            $this->cms_pages_model->ensureSubmenuOrderColumn();
            if ($this->cms_pages_model->hasPageColumn('parent_page_id')) {
                if ($parent_page_id > 0) {
                    if ($parent_page_id === $page_id || !$this->cms_pages_model->getPageById($parent_page_id)) {
                        $this->session->set_flashdata('error', 'Please select a valid parent page.');
                        $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
                    }
                    $update_data['parent_page_id'] = $parent_page_id;
                } else {
                    $update_data['parent_page_id'] = null;
                }
            }
            if ($this->cms_pages_model->hasPageColumn('submenu_order')) {
                $update_data['submenu_order'] = $parent_page_id > 0 ? $submenu_order : 0;
            }

            if ($this->cms_pages_model->hasPageColumn('banner_image')) {
                if (!empty($_FILES['banner_image']['name'])) {
                    $banner_upload = $this->do_upload('banner_image', 'cms_pages');
                    if ($banner_upload['status'] === 'success') {
                        $update_data['banner_image'] = $banner_upload['upload_data']['file_name'];
                    } else {
                        $this->session->set_flashdata('error', strip_tags($banner_upload['error']));
                        $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
                    }
                } else {
                    $this->load->helper('cms_media');
                    $banner_media_path = cms_media_normalize_stored_path($this->input->post('banner_media_path'));
                    if ($banner_media_path !== '') {
                        $update_data['banner_image'] = $banner_media_path;
                    }
                }
            }

            if ($this->cms_pages_model->updatePageById($page_id, $update_data)) {
                $message = 'CMS page updated successfully.';
                $header_msg = $this->applyPageHeaderDesignFromPost($page_id);
                if ($header_msg !== '') {
                    $message .= ' ' . $header_msg;
                }
                $footer_msg = $this->applyPageFooterDesignFromPost($page_id);
                if ($footer_msg !== '') {
                    $message .= ' ' . $footer_msg;
                }
                $this->session->set_flashdata('message', $message);
                $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
            }

            $header_msg = $this->applyPageHeaderDesignFromPost($page_id);
            $footer_msg = $this->applyPageFooterDesignFromPost($page_id);
            if ($header_msg !== '' || $footer_msg !== '') {
                $this->session->set_flashdata('message', trim($header_msg . ' ' . $footer_msg));
                $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
            }

            $this->session->set_flashdata('error', 'Failed to update CMS page.');
            $this->cms_page_edit_redirect($page_id, array('saved' => true, 'edit_mode' => $in_edit_mode));
        }

        $page_data = $this->cms_pages_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('cms_admin/pages');
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('cms_admin/pages'), 'page' => lang('CMS Pages')),
            array('link' => '#', 'page' => lang('Edit CMS Page'))
        );
        $meta = array('page_title' => lang('Edit CMS Page'), 'bc' => $bc);

        $this->data['page_data'] = $page_data;
        $this->cms_pages_model->ensureSectionEnabledColumn();
        // Ensure section master rows exist before reading the dropdown source.
        $this->cms_pages_model->ensureAllSectionMasters();
        $this->load->helper('cms_layout');
        $this->load_cms_model('storefront');
        $this->data['header_design_profiles'] = $this->cms_storefront_model->get_layout_builder_profiles_for_section('header');
        $this->data['footer_design_profiles'] = $this->cms_storefront_model->get_layout_builder_profiles_for_section('footer');
        $this->data['storefront_header_profiles'] = $this->data['header_design_profiles'];
        $this->data['storefront_footer_profiles'] = $this->data['footer_design_profiles'];
        $this->data['default_header_profile_slug'] = $this->cms_storefront_model->layout_builder_default_profile('header');
        $this->data['default_footer_profile_slug'] = $this->cms_storefront_model->layout_builder_default_profile('footer');
        $this->data['page_header_design_slug'] = $this->getPageAssignedHeaderDesignSlug($page_id);
        $this->data['page_footer_design_slug'] = $this->getPageAssignedFooterDesignSlug($page_id);
        $this->data['storefront_schema_ready'] = $this->cms_storefront_model->schema_ready();
        $this->data['section_masters'] = $this->cms_pages_model->getSectionMasters();
        $this->data['page_sections'] = $this->cms_pages_model->getAdminPageSections($page_id);
        $this->load->helper('cms_tags');
        $this->cms_pages_model->ensurePageTypeColumn();
        $page_scope_type = cms_sanitize_page_type(
            isset($page_data['page_type']) ? (string) $page_data['page_type'] : 'static'
        );
        $this->data['page_scope_type'] = $page_scope_type;
        $this->data['tags_master'] = $this->cms_pages_model->getTagsMasterForPage($page_scope_type);
        $this->data['page_tags'] = $this->cms_pages_model->getPageTagMappings($page_id);
        $this->data['parent_page_options'] = $this->cms_pages_model->getParentPageOptions($page_id);
        $this->load_cms_model('pages_faqs');
        $this->data['page_faqs_schema_ready'] = $this->cms_pages_faqs_model->ensure_table();
        $this->data['page_faqs'] = $this->data['page_faqs_schema_ready']
            ? $this->cms_pages_faqs_model->getFaqsByPageId($page_id)
            : array();
        $this->data['Customer_assets'] = isset($this->Customer_assets) ? $this->Customer_assets : 'localhost';
        $this->data['header_design_profiles'] = is_array($this->data['header_design_profiles'])
            ? $this->data['header_design_profiles'] : array();
        $this->data['footer_design_profiles'] = is_array($this->data['footer_design_profiles'])
            ? $this->data['footer_design_profiles'] : array();
        $this->cms_page_construct('cms_admin/pages/edit', $meta, $this->data);
    }

    public function add_section($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('cms_admin/pages');
        }

        $page_data = $this->cms_pages_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('cms_admin/pages');
        }

        $this->cms_pages_model->ensureAllSectionMasters();

        $section_id = (int) $this->input->post('section_id');
        $sort_order = (int) $this->input->post('sort_order');
        $is_enabled = 1;
        $section_heading = trim((string) $this->input->post('section_heading'));
        $page_text = trim(cms_read_html_field_from_post('page_text', $this));

        if ($section_id <= 0) {
            $this->session->set_flashdata('error', 'Section is required.');
            redirect('cms_admin/pages/edit/' . $page_id);
        }

        if ($sort_order <= 0) {
            $sort_order = $this->cms_pages_model->getNextPageSectionSortOrder($page_id);
        } elseif ($this->cms_pages_model->isPageSectionSortOrderExists($page_id, $sort_order)) {
            $sort_order = $this->cms_pages_model->getNextPageSectionSortOrder($page_id);
        }
        $master = $this->cms_pages_model->getSectionMasterById($section_id);
        $section_type = is_array($master) && !empty($master['section_type'])
            ? strtolower(trim((string) $master['section_type']))
            : '';
        $placement = $this->resolveSectionPlacementFlags($section_type, $section_heading);
        $mode_config = $this->readSectionModeConfigFromPost($section_type);
        if (!$this->validateStorefrontProfileModeConfig($section_type, $mode_config)) {
            redirect('cms_admin/pages/edit/' . $page_id);
        }
        $config_data = $this->buildSectionContainConfig($section_type, $section_heading, $page_text, array(), $mode_config);

        $section_contain = json_encode($config_data);

        $insert_data = array(
            'page_id'     => $page_id,
            'section_id'  => $section_id,
            'sort_order'  => $sort_order,
            'is_enabled'  => $is_enabled,
            'section_contain' => $section_contain,
        );
        $insert_data = array_merge($insert_data, $this->buildSectionVisibilityColumns(
            $placement['header'],
            $placement['footer'],
            $placement['banner'],
            $placement['logo']
        ));

        if ($this->cms_pages_model->addPageSection($insert_data)) {
            $this->session->set_flashdata('message', 'Dynamic section added successfully.');
            $this->cms_bust_storefront_cms_cache();
            redirect('cms_admin/pages/edit/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to add section. Sort order may already exist.');
        redirect('cms_admin/pages/edit/' . $page_id);
    }

    public function update_section($page_id = null, $mapping_id = null) {
        $page_id = (int) $page_id;
        $mapping_id = (int) $mapping_id;
        if ($page_id <= 0 || $mapping_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid page section.');
            redirect('cms_admin/pages');
        }

        $sort_order = (int) $this->input->post('sort_order');
        $section_heading = trim((string) $this->input->post('section_heading'));
        $page_text = trim(cms_read_html_field_from_post('page_text', $this));

        $existing_section = $this->cms_pages_model->getPageSectionById($mapping_id, $page_id);
        if (!$existing_section) {
            $this->session->set_flashdata('error', 'Page section not found.');
            redirect('cms_admin/pages/edit/' . $page_id);
        }
        $existing_config = array();
        $existing_raw = '';
        if (!empty($existing_section['section_contain'])) {
            $existing_raw = (string) $existing_section['section_contain'];
        } elseif (!empty($existing_section['config_json'])) {
            $existing_raw = (string) $existing_section['config_json'];
        }
        if ($existing_raw !== '') {
            $decoded = json_decode($existing_raw, true);
            if (is_array($decoded)) {
                $existing_config = $decoded;
            }
        }
        $master = !empty($existing_section['section_id'])
            ? $this->cms_pages_model->getSectionMasterById((int) $existing_section['section_id'])
            : null;
        $section_type = is_array($master) && !empty($master['section_type'])
            ? strtolower(trim((string) $master['section_type']))
            : '';
        $is_enabled = (int) $existing_section['is_enabled'] === 1 ? 1 : 0;

        if ($sort_order <= 0) {
            $sort_order = (int) $existing_section['sort_order'];
        }

        if ($this->cms_pages_model->isPageSectionSortOrderExists($page_id, $sort_order, $mapping_id)) {
            $sort_order = $this->cms_pages_model->getNextPageSectionSortOrder($page_id);
        }

        $placement = $this->resolveSectionPlacementFlags($section_type, $section_heading);
        $mode_config = $this->readSectionModeConfigFromPost($section_type);
        if (!$this->validateStorefrontProfileModeConfig($section_type, $mode_config)) {
            redirect('cms_admin/pages/edit/' . $page_id);
        }
        $config_data = $this->buildSectionContainConfig($section_type, $section_heading, $page_text, $existing_config, $mode_config);

        $update_data = array(
            'sort_order' => $sort_order,
            'is_enabled' => $is_enabled,
            'section_contain' => json_encode($config_data),
        );
        $update_data = array_merge($update_data, $this->buildSectionVisibilityColumns(
            $placement['header'],
            $placement['footer'],
            $placement['banner'],
            $placement['logo']
        ));

        if ($this->cms_pages_model->updatePageSectionById($mapping_id, $page_id, $update_data)) {
            $this->session->set_flashdata('message', 'Section updated successfully.');
            $this->cms_bust_storefront_cms_cache();
            redirect('cms_admin/pages/edit/' . $page_id . '?edit_section=' . $mapping_id);
        }

        $this->session->set_flashdata('error', 'Failed to update section.');
        redirect('cms_admin/pages/edit/' . $page_id . '?edit_section=' . $mapping_id);
    }

    public function delete_section($page_id = null, $mapping_id = null) {
        $page_id = (int) $page_id;
        $mapping_id = (int) $mapping_id;
        if ($page_id <= 0 || $mapping_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid page section.');
            redirect('cms_admin/pages');
        }

        if ($this->cms_pages_model->deletePageSectionById($mapping_id, $page_id)) {
            $this->session->set_flashdata('message', 'Section deleted successfully.');
            $this->cms_bust_storefront_cms_cache();
            redirect('cms_admin/pages/edit/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to delete section.');
        redirect('cms_admin/pages/edit/' . $page_id);
    }

    public function toggle_section($page_id = null, $mapping_id = null) {
        $page_id = (int) $page_id;
        $mapping_id = (int) $mapping_id;
        $is_ajax = $this->input->is_ajax_request();

        if ($page_id <= 0 || $mapping_id <= 0) {
            if ($is_ajax) {
                $this->output->set_content_type('application/json');
                echo json_encode(array(
                    'status'    => 'fail',
                    'message'   => 'Invalid section.',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ));
                return;
            }
            $this->session->set_flashdata('error', 'Invalid section.');
            redirect('cms_admin/pages');
        }

        if (!$this->cms_pages_model->getPageById($page_id)) {
            if ($is_ajax) {
                $this->output->set_content_type('application/json');
                echo json_encode(array(
                    'status'    => 'fail',
                    'message'   => 'CMS page not found.',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ));
                return;
            }
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('cms_admin/pages');
        }

        // Hidden field toggle_active (0/1) — avoids duplicate is_enabled POST keys and iCheck issues.
        $raw_enabled = $this->input->post('toggle_active', false);
        if ($raw_enabled === null || $raw_enabled === '') {
            $raw_enabled = $this->input->post('is_enabled', false);
        }
        if (is_array($raw_enabled)) {
            $raw_enabled = in_array('1', $raw_enabled, true) || in_array(1, $raw_enabled, true) ? '1' : '0';
        }
        $is_enabled = ($raw_enabled === '1' || $raw_enabled === 1 || $raw_enabled === true) ? 1 : 0;
        $updated = $this->cms_pages_model->setPageSectionEnabled($mapping_id, $page_id, $is_enabled);

        if ($is_ajax) {
            $this->output->set_content_type('application/json');
            if ($updated) {
                $this->cms_bust_storefront_cms_cache();
            }
            echo json_encode(array(
                'status'         => $updated ? 'success' : 'fail',
                'message'        => $updated
                    ? ($is_enabled ? 'Section is active on webshop.' : 'Section hidden from webshop.')
                    : 'Failed to update section status.',
                'is_enabled'     => $is_enabled,
                'webshop_active' => (bool) $is_enabled,
                'csrf_hash'      => $this->security->get_csrf_hash(),
            ));
            return;
        }

        if ($updated) {
            $this->session->set_flashdata('message', $is_enabled
                ? 'Section is now active on the webshop.'
                : 'Section is now hidden from the webshop.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update section status.');
        }
        $this->cms_page_edit_redirect($page_id, array('saved' => true));
    }

    public function toggle_nav_visibility($page_id = null) {
        $this->output->set_content_type('application/json');

        $page_id = (int) $page_id;
        $field = strtolower(trim((string) $this->input->post('field', false)));
        $allowed = array('show_in_header', 'show_in_footer');

        if ($page_id <= 0 || !in_array($field, $allowed, true)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid page or visibility field.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $this->cms_pages_model->ensureNavVisibilityColumns();

        $raw_enabled = $this->input->post('toggle_active', false);
        if ($raw_enabled === null || $raw_enabled === '') {
            $raw_enabled = $this->input->post('is_enabled', false);
        }
        if (is_array($raw_enabled)) {
            $raw_enabled = in_array('1', $raw_enabled, true) || in_array(1, $raw_enabled, true) ? '1' : '0';
        }
        $is_enabled = ($raw_enabled === '1' || $raw_enabled === 1 || $raw_enabled === true) ? 1 : 0;

        $updated = $this->cms_pages_model->setPageNavVisibility($page_id, $field, $is_enabled);
        $labels = array(
            'show_in_header' => 'header menu',
            'show_in_footer' => 'footer menu',
        );

        if ($updated) {
            $this->cms_bust_storefront_cms_cache();
        }

        echo json_encode(array(
            'status'     => $updated ? 'success' : 'fail',
            'message'    => $updated
                ? ($is_enabled
                    ? 'Page will appear in the ' . $labels[$field] . '.'
                    : 'Page removed from the ' . $labels[$field] . '.')
                : 'Failed to update visibility.',
            'field'      => $field,
            'is_enabled' => $is_enabled,
            'csrf_hash'  => $this->security->get_csrf_hash(),
        ));
    }

    public function reorder_nav() {
        $this->output->set_content_type('application/json');

        $raw_orders = $this->input->post('orders');
        if (is_string($raw_orders) && $raw_orders !== '') {
            $decoded = json_decode($raw_orders, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw_orders = $decoded;
            }
        }

        if (!is_array($raw_orders) || empty($raw_orders)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'No menu order data received.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $page_orders = array();
        foreach ($raw_orders as $page_id => $nav_order) {
            $page_id = (int) $page_id;
            $nav_order = (int) $nav_order;
            if ($page_id > 0 && $nav_order > 0) {
                $page_orders[$page_id] = $nav_order;
            }
        }

        if (empty($page_orders)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid menu order data.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $this->cms_pages_model->ensureNavOrderColumn();
        $updated = $this->cms_pages_model->updatePageNavOrders($page_orders);
        if ($updated) {
            $this->cms_bust_storefront_cms_cache();
        }
        echo json_encode(array(
            'status'    => $updated ? 'success' : 'fail',
            'message'   => $updated ? 'Webshop menu order saved.' : 'Failed to save menu order.',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ));
    }

    public function reorder_sections($page_id = null) {
        $this->output->set_content_type('application/json');

        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid page.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $raw_orders = $this->input->post('orders');

        // The JS sends orders as JSON.stringify({}), so it arrives as a JSON string.
        // Gracefully handle both a plain string and an already-decoded array.
        if (is_string($raw_orders) && $raw_orders !== '') {
            $decoded = json_decode($raw_orders, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw_orders = $decoded;
            }
        }

        if (!is_array($raw_orders) || empty($raw_orders)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'No sort order data received.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $mapping_orders = array();
        foreach ($raw_orders as $mapping_id => $sort_order) {
            $mapping_id = (int) $mapping_id;
            $sort_order = (int) $sort_order;
            if ($mapping_id > 0 && $sort_order > 0) {
                $mapping_orders[$mapping_id] = $sort_order;
            }
        }

        if (empty($mapping_orders)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid sort order data (all entries filtered).',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $updated = $this->cms_pages_model->updatePageSectionSortOrders($page_id, $mapping_orders);
        if ($updated) {
            $this->cms_bust_storefront_cms_cache();
        }
        echo json_encode(array(
            'status'    => $updated ? 'success' : 'fail',
            'message'   => $updated ? 'Sort order updated.' : 'Failed to update sort order in database.',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ));
    }

    public function add_tag($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('cms_admin/pages');
        }

        $page_data = $this->cms_pages_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('cms_admin/pages');
        }

        $tag_id = (int) $this->input->post('tag_id');
        $property_name = trim((string) $this->input->post('property_name'));
        $value = trim((string) $this->input->post('value'));

        if ($tag_id <= 0 || $property_name === '' || $value === '') {
            $this->session->set_flashdata('error', 'Tag, property name and value are required.');
            redirect('cms_admin/pages/edit/' . $page_id);
        }

        if ($this->cms_pages_model->upsertPageTagValue($page_id, $tag_id, $property_name, $value)) {
            $this->session->set_flashdata('message', 'Tag value saved successfully.');
            $this->cms_bust_storefront_cms_cache();
            redirect('cms_admin/pages/edit/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to save tag value.');
        redirect('cms_admin/pages/edit/' . $page_id);
    }

    /**
     * Build section_contain JSON per section type (webshopapi Webshop_section_engine contract).
     *
     * @param string $section_type
     * @param string $section_heading
     * @param string $page_text
     * @param array  $existing_config
     * @return array
     */
    /**
     * @param string $section_type
     * @return array{header_mode?:string,footer_mode?:string,storefront_profile?:string}
     */
    /**
     * @param string $section_type
     * @param array  $mode_config
     * @return bool
     */
    protected function validateStorefrontProfileModeConfig($section_type, array $mode_config)
    {
        $this->load->helper('cms_layout');
        $section_type = strtolower(trim((string) $section_type));
        $profile = isset($mode_config['storefront_profile']) ? (string) $mode_config['storefront_profile'] : '';

        if ($section_type === 'header' && isset($mode_config['header_mode']) && $mode_config['header_mode'] === 'storefront_profile') {
            if (!cms_storefront_profile_exists('header', $profile)) {
                $this->session->set_flashdata('error', 'Header design "' . $profile . '" was not found. Create it under Header designs first.');
                return false;
            }
        }
        if ($section_type === 'footer' && isset($mode_config['footer_mode']) && $mode_config['footer_mode'] === 'storefront_profile') {
            if (!cms_storefront_profile_exists('footer', $profile)) {
                $this->session->set_flashdata('error', 'Footer design "' . $profile . '" was not found. Create it under Footer designs first.');
                return false;
            }
        }
        return true;
    }

    protected function readSectionModeConfigFromPost($section_type)
    {
        $section_type = strtolower(trim((string) $section_type));
        $out = array();
        if ($section_type === 'header') {
            $mode = strtolower(trim((string) $this->input->post('header_mode')));
            $out['header_mode'] = ($mode === 'storefront_profile') ? 'storefront_profile' : 'custom';
            $out['storefront_profile'] = cms_storefront_normalize_profile_slug($this->input->post('storefront_profile'));
            if ($out['storefront_profile'] === '') {
                $out['storefront_profile'] = cms_layout_builder_default_profile_slug('header');
            }
        } elseif ($section_type === 'footer') {
            $mode = strtolower(trim((string) $this->input->post('footer_mode')));
            $out['footer_mode'] = ($mode === 'storefront_profile') ? 'storefront_profile' : 'custom';
            $out['storefront_profile'] = cms_storefront_normalize_profile_slug($this->input->post('storefront_profile'));
            if ($out['storefront_profile'] === '') {
                $out['storefront_profile'] = cms_layout_builder_default_profile_slug('footer');
            }
        }
        return $out;
    }

    /**
     * @param string $section_type
     * @param string $section_heading
     * @return array{header:string,footer:string,banner:string,logo:string}
     */
    protected function resolveSectionPlacementFlags($section_type, $section_heading = '')
    {
        $section_type = strtolower(trim((string) $section_type));
        $yes = 'yes';
        $no = 'no';

        switch ($section_type) {
            case 'header':
                return array('header' => $yes, 'footer' => $no, 'banner' => $no, 'logo' => $no);
            case 'footer':
                return array('header' => $no, 'footer' => $yes, 'banner' => $no, 'logo' => $no);
            case 'banner':
            case 'hero_banner':
                return array('header' => $no, 'footer' => $no, 'banner' => $yes, 'logo' => $no);
            case 'logo':
                return array('header' => $no, 'footer' => $no, 'banner' => $no, 'logo' => $yes);
            default:
                return array(
                    'header' => trim((string) $section_heading) !== '' ? $yes : $no,
                    'footer' => $no,
                    'banner' => $no,
                    'logo'   => $no,
                );
        }
    }

    protected function buildSectionContainConfig($section_type, $section_heading, $page_text, array $existing_config = array(), array $mode_config = array())
    {
        $section_type = strtolower(trim((string) $section_type));
        $section_heading = trim((string) $section_heading);
        $page_text = trim((string) $page_text);

        $html_types = array('html_block', 'header', 'footer', 'banner', 'hero_banner');
        if (in_array($section_type, $html_types, true)) {
            $placement = $this->resolveSectionPlacementFlags($section_type, $section_heading);
            $config = array(
                'show_header' => $placement['header'],
                'show_footer' => $placement['footer'],
                'show_banner' => $placement['banner'],
                'show_logo'   => $placement['logo'],
                'content'     => $page_text,
            );
            if ($section_type === 'header') {
                $config['header_mode'] = isset($mode_config['header_mode']) ? $mode_config['header_mode'] : 'custom';
                if (isset($mode_config['storefront_profile'])) {
                    $config['storefront_profile'] = $mode_config['storefront_profile'];
                } elseif (isset($existing_config['storefront_profile'])) {
                    $config['storefront_profile'] = $existing_config['storefront_profile'];
                } else {
                    $config['storefront_profile'] = 'default';
                }
                if ($config['header_mode'] === 'storefront_profile') {
                    $config['content'] = $page_text !== ''
                        ? $page_text
                        : (isset($existing_config['content']) ? (string) $existing_config['content'] : '');
                }
            }
            if ($section_type === 'footer') {
                $config['footer_mode'] = isset($mode_config['footer_mode']) ? $mode_config['footer_mode'] : 'custom';
                if (isset($mode_config['storefront_profile'])) {
                    $config['storefront_profile'] = $mode_config['storefront_profile'];
                } elseif (isset($existing_config['storefront_profile'])) {
                    $config['storefront_profile'] = $existing_config['storefront_profile'];
                } else {
                    $config['storefront_profile'] = 'default';
                }
                if ($config['footer_mode'] === 'storefront_profile') {
                    $config['content'] = $page_text !== ''
                        ? $page_text
                        : (isset($existing_config['content']) ? (string) $existing_config['content'] : '');
                }
            }
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            }
            return $config;
        }

        if (in_array($section_type, array('product_grid', 'product_carousel'), true)) {
            $config = is_array($existing_config) ? $existing_config : array();
            unset($config['content'], $config['show_header'], $config['show_footer'], $config['show_banner'], $config['show_logo']);
            if (!isset($config['limit']) || (int) $config['limit'] < 1) {
                $config['limit'] = 12;
            }
            if (!isset($config['products_per_page']) || (int) $config['products_per_page'] < 1) {
                $config['products_per_page'] = (int) $config['limit'];
            }
            if (!isset($config['columns_desktop']) || (int) $config['columns_desktop'] < 1) {
                $config['columns_desktop'] = 4;
            }
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            } else {
                unset($config['title'], $config['heading']);
            }
            return $config;
        }

        if (in_array($section_type, array('category_grid', 'category_carousel'), true)) {
            $config = is_array($existing_config) ? $existing_config : array();
            unset($config['content']);
            if (!isset($config['limit'])) {
                $config['limit'] = 0;
            }
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            }
            return $config;
        }

        if (in_array($section_type, array('contact_us_form', 'contact_form'), true)) {
            $config = is_array($existing_config) ? $existing_config : array();
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            }
            return $config;
        }

        if ($section_type === 'blog_grid') {
            $config = is_array($existing_config) ? $existing_config : array();
            unset($config['content']);
            if (!isset($config['limit']) || (int) $config['limit'] < 1) {
                $config['limit'] = 12;
            }
            if (!isset($config['posts_per_page']) || (int) $config['posts_per_page'] < 1) {
                $config['posts_per_page'] = (int) $config['limit'];
            }
            if (!isset($config['columns_desktop']) || (int) $config['columns_desktop'] < 1) {
                $config['columns_desktop'] = 3;
            }
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            }
            return $config;
        }

        if ($section_type === 'testimonials_grid') {
            $config = is_array($existing_config) ? $existing_config : array();
            unset($config['content']);
            if (!isset($config['limit']) || (int) $config['limit'] < 1) {
                $config['limit'] = 6;
            }
            if (!isset($config['columns_desktop']) || (int) $config['columns_desktop'] < 1) {
                $config['columns_desktop'] = 3;
            }
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            } elseif (!isset($config['title'])) {
                $config['title'] = 'What Our Clients Say';
            }
            return $config;
        }

        if (in_array($section_type, array('page_faq', 'faq_accordion', 'faq'), true)) {
            $config = is_array($existing_config) ? $existing_config : array();
            unset($config['content']);
            if ($section_heading !== '') {
                $config['title'] = $section_heading;
                $config['heading'] = $section_heading;
            } elseif (!isset($config['title'])) {
                $config['title'] = 'Frequently Asked Questions';
            }
            return $config;
        }

        $config = is_array($existing_config) ? $existing_config : array();
        $config['content'] = $page_text;
        if ($section_heading !== '') {
            $config['title'] = $section_heading;
            $config['heading'] = $section_heading;
        }
        return $config;
    }

    /**
     * Assign page header design when header_design is posted (Add section form).
     *
     * @param int $page_id
     * @return string Extra flash message text, or empty string
     */
    /**
     * Save page header/footer layout assignments (no unpublish required).
     *
     * @param int $page_id
     * @return string Flash message text
     */
    protected function savePageChromeDesignsFromPost($page_id)
    {
        $parts = array();
        $header_msg = $this->applyPageHeaderDesignFromPost($page_id);
        if ($header_msg !== '') {
            $parts[] = $header_msg;
        }
        $footer_msg = $this->applyPageFooterDesignFromPost($page_id);
        if ($footer_msg !== '') {
            $parts[] = $footer_msg;
        }
        return trim(implode(' ', $parts));
    }

    protected function applyPageHeaderDesignFromPost($page_id)
    {
        $this->load->helper('cms_layout');
        if ($this->input->post('header_design') === false) {
            return '';
        }
        $profile = cms_storefront_normalize_profile_slug($this->input->post('header_design'));
        if ($profile === '') {
            $result = $this->cms_pages_model->clearPageHeaderDesignAssignment((int) $page_id);
            if (!empty($result['ok'])) {
                return isset($result['message']) ? (string) $result['message'] : 'Page header design cleared.';
            }
            return '';
        }

        $this->load_cms_model('storefront');
        $this->cms_storefront_model->ensure_layout_builder_profile_ready($profile);

        $result = $this->cms_pages_model->assignStorefrontProfileSection((int) $page_id, 'header', $profile);
        if (!empty($result['ok'])) {
            if (!cms_layout_builder_profile_has_config($profile)) {
                $this->session->set_flashdata('warning',
                    'Header design "' . htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') . '" is assigned, but has no saved layout. '
                    . 'Go to Storefront layout → select this profile → configure and save it first.'
                );
            }
            return isset($result['message']) ? (string) $result['message'] : 'Page header design updated.';
        }

        $this->session->set_flashdata('warning', isset($result['message']) ? $result['message'] : 'Section was added but header design could not be assigned.');
        return '';
    }

    /**
     * Header design slug currently assigned on this page (from Header section JSON).
     *
     * @param int $page_id
     * @return string
     */
    protected function getPageAssignedHeaderDesignSlug($page_id)
    {
        return $this->cms_pages_model->getPageStorefrontProfileSlug((int) $page_id, 'header');
    }

    /**
     * Assign page footer design when footer_design is posted.
     *
     * @param int $page_id
     * @return string
     */
    protected function applyPageFooterDesignFromPost($page_id)
    {
        $this->load->helper('cms_layout');
        if ($this->input->post('footer_design') === false) {
            return '';
        }
        $profile = cms_storefront_normalize_profile_slug($this->input->post('footer_design'));
        if ($profile === '') {
            $result = $this->cms_pages_model->clearPageFooterDesignAssignment((int) $page_id);
            if (!empty($result['ok'])) {
                return isset($result['message']) ? (string) $result['message'] : 'Page footer design cleared.';
            }
            return '';
        }

        $this->load_cms_model('storefront');
        $this->cms_storefront_model->ensure_layout_builder_profile_ready($profile);

        $result = $this->cms_pages_model->assignStorefrontProfileSection((int) $page_id, 'footer', $profile);
        if (!empty($result['ok'])) {
            return isset($result['message']) ? (string) $result['message'] : 'Page footer design updated.';
        }

        $this->session->set_flashdata('warning', isset($result['message']) ? $result['message'] : 'Section was added but footer design could not be assigned.');
        return '';
    }

    /**
     * Footer design slug currently assigned on this page.
     *
     * @param int $page_id
     * @return string
     */
    protected function getPageAssignedFooterDesignSlug($page_id)
    {
        return $this->cms_pages_model->getPageStorefrontProfileSlug((int) $page_id, 'footer');
    }

    /**
     * AJAX: reload tag fields after head-script import (new tags may appear in Tag Master).
     *
     * @param int|null $page_id
     */
    public function tags_form_partial($page_id = null)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->helper('cms_tags');
        $page_id = (int) $page_id;

        $requested_type = $this->input->get('page_type');
        if ($requested_type !== null && trim((string) $requested_type) !== '') {
            $page_type = cms_sanitize_page_type($requested_type);
        } elseif ($page_id > 0) {
            $page_type = $this->cms_pages_model->getPageTypeForPage($page_id);
        } else {
            $page_type = 'static';
        }

        $existing_values = array();
        if ($page_id > 0) {
            foreach ($this->cms_pages_model->getPageTagMappings($page_id) as $row) {
                $existing_values[(int) $row['tag_id']] = (string) $row['value'];
            }
        }

        $tags = $this->cms_pages_model->getTagsMasterForPage($page_type);
        $html = $this->load->view(
            $this->theme . 'cms_admin/_partials/tag_form_fields',
            array(
                'tags_by_category'    => cms_group_tags_by_category($tags),
                'existing_values'     => $existing_values,
                'scope_form'          => 'page',
                'scope_code'          => $page_type,
                'scope_label'         => cms_tags_form_scope_label('page', $page_type),
                'head_tag_import_url' => $page_id > 0
                    ? site_url('cms_admin/pages/ajax_import_head_tags/' . $page_id)
                    : '',
            ),
            true
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'html'      => $html,
                'tag_count' => count($tags),
                'csrf_hash' => $this->security->get_csrf_hash(),
            )));
    }

    /**
     * Parse pasted head HTML/scripts, auto-create missing tags_master rows, save & return field map.
     *
     * @param int|null $page_id
     */
    public function ajax_import_head_tags($page_id = null)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output->set_content_type('application/json');
        $page_id = (int) $page_id;

        if ($page_id <= 0 || !$this->cms_pages_model->getPageById($page_id)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid CMS page.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $this->load->helper('cms_head_tag_import');
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

        $import_context = cms_head_import_context('page');
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

            if ($this->cms_pages_model->upsertPageTagValue($page_id, (int) $ensure['id'], $tag_name, $value)) {
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
            'reload'       => !empty($created_tags),
            'csrf_hash'    => $this->security->get_csrf_hash(),
        ));
    }

}
