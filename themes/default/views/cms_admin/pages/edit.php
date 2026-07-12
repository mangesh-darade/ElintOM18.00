<?php defined('BASEPATH') OR exit('No direct script access allowed');
$page_status = isset($page_data['status']) ? (string) $page_data['status'] : 'draft';
$page_status_label = $page_status === 'published' ? 'Published' : 'Draft';
$page_status_class = $page_status === 'published' ? 'ws-status-published' : 'ws-status-draft';
$just_unpublished = !empty($_GET['unpublished']) || $this->session->flashdata('cms_page_unpublished');
// Save Changes and page fields only appear after Unpublish.
$show_save_changes = $just_unpublished;
$show_page_fields = $just_unpublished;
$fields_required_attr = $show_page_fields ? ' required' : '';
?>
<?php
$this->load->helper('cms_media');
$media_base_path = base_url('assets/mdata/' . (isset($Customer_assets) ? $Customer_assets : 'localhost') . '/uploads/webshop/cms_pages/');
$banner_preview_url = !empty($page_data['banner_image'])
    ? cms_media_public_url($page_data['banner_image'], isset($Customer_assets) ? $Customer_assets : 'localhost')
    : '';
$banner_stored_path = !empty($page_data['banner_image'])
    ? cms_media_normalize_stored_path($page_data['banner_image'])
    : '';
$page_edit_url = site_url('cms_admin/pages/edit/' . (int) $page_data['id']);
$header_design_profiles = isset($header_design_profiles) && is_array($header_design_profiles) ? $header_design_profiles : array();
$footer_design_profiles = isset($footer_design_profiles) && is_array($footer_design_profiles) ? $footer_design_profiles : array();
$cms_page_section_count = !empty($page_sections) && is_array($page_sections) ? count($page_sections) : 0;
?>
<div class="box cms-page-edit">
    <div class="box-content">
        <div class="row cms-edit-wrap cms-page-edit-primary">
            <div class="col-lg-12">
                <div class="cms-page-toolbar-card">
                    <div class="cms-page-toolbar-row">
                        <div class="cms-page-toolbar-meta">
                            <span class="cms-page-status-label">Page status</span>
                            <span id="cmsPageStatusBadge" class="ws-status-badge <?= htmlspecialchars($page_status_class, ENT_QUOTES, 'UTF-8'); ?>" data-status="<?= htmlspecialchars($page_status, ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="ws-status-dot" aria-hidden="true"></span>
                                <?= htmlspecialchars($page_status_label, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <div id="cmsPublishControls" class="cms-page-toolbar-actions">
                            <?= form_open($page_edit_url, array('class' => 'cms-inline-form', 'id' => 'form_cms_publish_page')); ?>
                            <input type="hidden" name="header_design" id="publish_header_design" value="">
                            <input type="hidden" name="footer_design" id="publish_footer_design" value="">
                            <button type="submit" name="publish_page" value="1" class="btn btn-success cms-btn-toolbar"<?= $page_status === 'published' ? ' disabled' : ''; ?>>
                                <i class="fa fa-check-circle"></i> Publish
                            </button>
                            <?= form_close(); ?>
                            <?= form_open($page_edit_url, array('class' => 'cms-inline-form')); ?>
                            <button type="submit" name="unpublish_page" value="1" class="btn btn-warning cms-btn-toolbar"<?= $page_status === 'draft' ? ' disabled' : ''; ?>>
                                <i class="fa fa-ban"></i> Unpublish
                            </button>
                            <?= form_close(); ?>
                            <div id="cmsSaveChangesActions" class="cms-toolbar-save-group<?= $show_save_changes ? '' : ' is-hidden'; ?>">
                                <button type="button" id="btnCmsSaveChanges" class="btn btn-primary cms-btn-toolbar">
                                    <i class="fa fa-save"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="cms-page-edit-hint" id="cmsPageEditHint"<?= $show_page_fields ? ' style="display:none;"' : ''; ?>>
                        <?php if ($page_status === 'draft') { ?>
                            Page details are locked while the page is in draft. Click <strong>Publish</strong>, then <strong>Unpublish</strong> to edit name, URL, and banner.
                        <?php } else { ?>
                            Click <strong>Unpublish</strong> to edit page name, URL, and banner.
                        <?php } ?>
                    </p>
                </div>

                <?php
                $header_design_profiles = isset($header_design_profiles) && is_array($header_design_profiles) ? $header_design_profiles : array();
                $page_header_design_slug = isset($page_header_design_slug) ? (string) $page_header_design_slug : '';
                $footer_design_profiles = isset($footer_design_profiles) && is_array($footer_design_profiles) ? $footer_design_profiles : array();
                $page_footer_design_slug = isset($page_footer_design_slug) ? (string) $page_footer_design_slug : '';
                $default_header_profile_slug = isset($default_header_profile_slug) ? (string) $default_header_profile_slug : 'site';
                $default_footer_profile_slug = isset($default_footer_profile_slug) ? (string) $default_footer_profile_slug : 'site';
                $page_header_uses_default = function_exists('cms_page_design_uses_store_default_profile')
                    ? cms_page_design_uses_store_default_profile($page_header_design_slug, 'header')
                    : ($page_header_design_slug === '' || $page_header_design_slug === 'default' || $page_header_design_slug === 'site');
                $page_footer_uses_default = function_exists('cms_page_design_uses_store_default_profile')
                    ? cms_page_design_uses_store_default_profile($page_footer_design_slug, 'footer')
                    : ($page_footer_design_slug === '' || $page_footer_design_slug === 'default' || $page_footer_design_slug === 'site');
                $header_default_option_label = function_exists('cms_page_design_default_option_label')
                    ? cms_page_design_default_option_label('header', $header_design_profiles)
                    : ('— Default Store Header (' . $default_header_profile_slug . ') —');
                $footer_default_option_label = function_exists('cms_page_design_default_option_label')
                    ? cms_page_design_default_option_label('footer', $footer_design_profiles)
                    : ('— Default Store Footer (' . $default_footer_profile_slug . ') —');
                ?>
                <div class="cms-page-chrome-card cms-page-details-card cms-pe-collapsible" id="cmsPeSectionChrome" data-pe-section="chrome">
                    <div class="cms-pe-collapsible__bar">
                        <button type="button" class="cms-pe-collapsible__toggle" aria-expanded="false" aria-controls="cmsPeSectionChromeBody">
                            <span class="cms-pe-collapsible__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                            <span class="cms-pe-collapsible__heading">
                                <span class="cms-pe-collapsible__title"><i class="fa fa-paint-brush"></i> Page header &amp; footer design</span>
                                <span class="cms-pe-collapsible__desc">Assign storefront header and footer layouts to this page</span>
                            </span>
                        </button>
                    </div>
                    <div class="cms-pe-collapsible__body" id="cmsPeSectionChromeBody">
                    <p class="cms-field-hint cms-pe-collapsible__intro">
                        Assigns a <a href="<?= site_url('cms_admin/layout_builder'); ?>">Storefront layout</a> to this page.
                        Stored in <code>page_section_mapping.section_contain</code> (Header/Footer section JSON), not on <code>pages</code>.
                        Works while the page is <strong>Published</strong>. Current selections are also saved when you click <strong>Publish</strong> or <strong>Save Changes</strong> (after unpublish).
                    </p>
                    <?= form_open($page_edit_url, array('class' => 'cms-page-chrome-form', 'id' => 'form_cms_page_chrome')); ?>
                    <input type="hidden" name="save_page_chrome_designs" value="1">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="page_header_design">Page header design <span class="text-muted">(optional)</span></label>
                                <select name="header_design" id="page_header_design" class="form-control cms-input">
                                    <option value="default"<?= $page_header_uses_default ? ' selected' : ''; ?>><?= htmlspecialchars($header_default_option_label, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php foreach ($header_design_profiles as $hd) {
                                        $slug = isset($hd['slug']) ? (string) $hd['slug'] : '';
                                        if (function_exists('cms_page_design_profile_skip_in_list')) {
                                            if (cms_page_design_profile_skip_in_list($slug, 'header')) {
                                                continue;
                                            }
                                        } elseif ($slug === '' || $slug === 'default' || $slug === 'site' || $slug === $default_header_profile_slug) {
                                            continue;
                                        }
                                        $lbl = isset($hd['label']) ? (string) $hd['label'] : $slug;
                                        ?>
                                        <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"<?= (!$page_header_uses_default && $page_header_design_slug === $slug) ? ' selected' : ''; ?>>
                                            <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <?php if ($page_status === 'draft') { ?>
                                <div class="alert alert-warning cms-field-hint" style="margin-top:8px;margin-bottom:0;">
                                    <strong>Webshop uses this only after Publish.</strong>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="page_footer_design">Page footer design <span class="text-muted">(optional)</span></label>
                                <select name="footer_design" id="page_footer_design" class="form-control cms-input">
                                    <option value="default"<?= $page_footer_uses_default ? ' selected' : ''; ?>><?= htmlspecialchars($footer_default_option_label, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php foreach ($footer_design_profiles as $fd) {
                                        $slug = isset($fd['slug']) ? (string) $fd['slug'] : '';
                                        if (function_exists('cms_page_design_profile_skip_in_list')) {
                                            if (cms_page_design_profile_skip_in_list($slug, 'footer')) {
                                                continue;
                                            }
                                        } elseif ($slug === '' || $slug === 'default' || $slug === 'site' || $slug === $default_footer_profile_slug) {
                                            continue;
                                        }
                                        $lbl = isset($fd['label']) ? (string) $fd['label'] : $slug;
                                        ?>
                                        <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"<?= (!$page_footer_uses_default && $page_footer_design_slug === $slug) ? ' selected' : ''; ?>>
                                            <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary cms-btn-toolbar">
                                <i class="fa fa-save"></i> Save header &amp; footer design
                            </button>
                            <?php if ($page_header_design_slug !== '' || $page_footer_design_slug !== '' || $page_header_uses_default || $page_footer_uses_default) { ?>
                            <span class="text-muted" style="margin-left:12px;">
                                Current:
                                <?php if ($page_header_design_slug !== '' || $page_header_uses_default) { ?>
                                    header <code><?= htmlspecialchars($page_header_uses_default ? ('default → ' . $default_header_profile_slug) : $page_header_design_slug, ENT_QUOTES, 'UTF-8'); ?></code>
                                <?php } ?>
                                <?php if ($page_footer_design_slug !== '' || $page_footer_uses_default) { ?>
                                    footer <code><?= htmlspecialchars($page_footer_uses_default ? ('default → ' . $default_footer_profile_slug) : $page_footer_design_slug, ENT_QUOTES, 'UTF-8'); ?></code>
                                <?php } ?>
                            </span>
                            <?php } ?>
                        </div>
                    </div>
                    <?= form_close(); ?>
                    </div>
                </div>

                <?php
                $attrib = array('role' => 'form', 'id' => 'form_cms_page_edit', 'class' => 'cms-page-edit-form', 'novalidate' => 'novalidate');
                echo form_open_multipart($page_edit_url, $attrib);
                ?>
                <input type="hidden" name="update_cms_page" value="1">
                <input type="hidden" name="cms_edit_mode" id="cms_edit_mode" value="<?= $just_unpublished ? '1' : '0'; ?>">
                <input type="hidden" name="header_design" id="save_changes_header_design" value="">
                <input type="hidden" name="footer_design" id="save_changes_footer_design" value="">
                <button type="submit" id="cmsSaveChangesSubmit" class="cms-sr-only-submit" tabindex="-1" aria-hidden="true">Save</button>
                <div id="cmsPageFields" class="cms-page-details-card<?= $show_page_fields ? '' : ' is-hidden'; ?>">
                    <h3 class="cms-panel-subtitle"><i class="fa fa-file-text-o"></i> Page details</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="page_name">Page name <span class="cms-required">*</span></label>
                                <input type="text" name="page_name" id="page_name" class="form-control cms-input"
                                       placeholder="e.g. About Us"
                                       value="<?= htmlspecialchars($page_data['page_name'], ENT_QUOTES, 'UTF-8'); ?>"<?= $fields_required_attr; ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="url">URL path <span class="cms-required">*</span></label>
                                <div class="cms-input-prefix-wrap">
                                    <span class="cms-input-prefix">/</span>
                                    <input type="text" name="url" id="url" class="form-control cms-input cms-input-with-prefix"
                                           placeholder="about-us"
                                           value="<?= htmlspecialchars(ltrim((string) $page_data['url'], '/'), ENT_QUOTES, 'UTF-8'); ?>"<?= $fields_required_attr; ?>>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group cms-form-group">
                                <label for="parent_page_id">Parent page (optional)</label>
                                <?php $selected_parent_page_id = isset($page_data['parent_page_id']) ? (int) $page_data['parent_page_id'] : 0; ?>
                                <select name="parent_page_id" id="parent_page_id" class="form-control cms-input">
                                    <option value="0">None (top-level page)</option>
                                    <?php if (!empty($parent_page_options)) { ?>
                                        <?php foreach ($parent_page_options as $parent_page) { ?>
                                            <option value="<?= (int) $parent_page['id']; ?>"<?= $selected_parent_page_id === (int) $parent_page['id'] ? ' selected' : ''; ?>>
                                                <?= htmlspecialchars((string) $parent_page['page_name'] . ' (' . (string) $parent_page['url'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <p class="cms-field-hint">Set this page as subpage for dropdown menu in webshop API.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="submenu_order">Submenu order</label>
                                <input type="number" min="0" name="submenu_order" id="submenu_order" class="form-control cms-input"
                                       value="<?= isset($page_data['submenu_order']) ? (int) $page_data['submenu_order'] : 0; ?>" placeholder="0">
                                <p class="cms-field-hint">Lower value shows first in subpage dropdown.</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group cms-form-group">
                                <label for="banner_image">Page banner image</label>
                                <div class="cms-file-upload">
                                    <input type="file" name="banner_image" id="banner_image" class="cms-file-upload-input" accept=".jpg,.jpeg,.png,.gif">
                                    <label for="banner_image" class="cms-file-upload-trigger">
                                        <i class="fa fa-cloud-upload"></i>
                                        <span class="cms-file-upload-label">Choose image</span>
                                        <span class="cms-file-upload-name" id="bannerFileName">No file chosen</span>
                                    </label>
                                </div>
                                <p class="cms-field-hint">JPG, JPEG, PNG, or GIF — maximum 2 MB. Or choose from the Media Library.</p>
                                <input type="hidden" name="banner_media_path" id="bannerMediaPath" value="<?= htmlspecialchars($banner_stored_path, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="cms-media-pick-row">
                                    <button type="button" class="btn btn-default btn-sm cms-media-pick-btn"
                                            data-media-preset="page_banners"
                                            data-media-target="#bannerMediaPath"
                                            data-media-preview="#bannerMediaPreviewImg">
                                        <i class="fa fa-picture-o"></i> Choose from library
                                    </button>
                                    <a href="<?= site_url('cms_admin/media?preset=page_banners'); ?>" class="btn btn-link btn-sm">Open Media Library</a>
                                </div>
                                <?php if ($banner_preview_url !== '') { ?>
                                    <div class="cms-banner-preview cms-media-pick-preview" id="bannerMediaPreview">
                                        <img src="<?= htmlspecialchars($banner_preview_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Current banner" class="cms-banner-preview-img" id="bannerMediaPreviewImg">
                                        <div class="cms-banner-preview-meta">
                                            <span class="cms-banner-preview-label">Current banner</span>
                                            <?php
                                            $remove_banner_url = site_url('cms_admin/pages/remove_media/' . (int) $page_data['id'] . '/banner');
                                            if ($just_unpublished) {
                                                $remove_banner_url .= (strpos($remove_banner_url, '?') === false ? '?' : '&') . 'unpublished=1';
                                            }
                                            ?>
                                            <a href="<?= htmlspecialchars($remove_banner_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Remove banner image?');">
                                                <i class="fa fa-trash"></i> Remove
                                            </a>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="cms-banner-preview cms-media-pick-preview" id="bannerMediaPreview" style="display:none;">
                                        <img src="" alt="Selected banner" class="cms-banner-preview-img" id="bannerMediaPreviewImg">
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <div class="cms-page-details-card cms-pe-collapsible" id="cmsPeSectionAdd" data-pe-section="add-section">
                    <div class="cms-pe-collapsible__bar">
                        <button type="button" class="cms-pe-collapsible__toggle" aria-expanded="false" aria-controls="cmsPeSectionAddBody">
                            <span class="cms-pe-collapsible__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                            <span class="cms-pe-collapsible__heading">
                                <span class="cms-pe-collapsible__title"><i class="fa fa-puzzle-piece"></i> Add dynamic section</span>
                                <span class="cms-pe-collapsible__desc">Choose a section template and add it to this page</span>
                            </span>
                        </button>
                        <div class="cms-pe-collapsible__actions">
                            <button type="submit" form="form_add_cms_section" class="btn btn-success cms-btn-toolbar">
                                <i class="fa fa-plus"></i> Add section
                            </button>
                        </div>
                    </div>
                    <div class="cms-pe-collapsible__body" id="cmsPeSectionAddBody">
                <?php
                $attrib = array('role' => 'form', 'id' => 'form_add_cms_section');
                echo form_open('cms_admin/pages/add_section/' . (int) $page_data['id'], $attrib);
                ?>
                <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="section_id">Section</label>
                        <select name="section_id" id="section_id" class="form-control" required>
                            <option value="">Select Section</option>
                            <?php if (!empty($section_masters)) { ?>
                                <?php foreach ($section_masters as $master) {
                                    $master_section_type = strtolower(trim((string) $master['section_type']));
                                    if (in_array($master_section_type, array('contact_us_form', 'contact_form'), true)) {
                                        continue;
                                    }
                                    ?>
                                    <option
                                        value="<?= (int) $master['id']; ?>"
                                        data-section-name="<?= htmlspecialchars(strtolower((string) $master['section_name']), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-section-type="<?= htmlspecialchars($master_section_type, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        <?= htmlspecialchars($master['section_name'] . ' (' . $master['section_type'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sort_order">Order <span class="text-muted">(optional)</span></label>
                        <input type="number" min="1" name="sort_order" id="sort_order" class="form-control" placeholder="Auto-assigned if empty">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="section_heading">Section title</label>
                        <input type="text" name="section_heading" id="section_heading" class="form-control" placeholder="Section title (optional)">
                    </div>
                </div>
                <div class="col-md-12" id="sectionLayoutModeWrap" style="display:none;">
                    <div class="alert alert-info cms-section-layout-hint" style="margin-bottom:12px;">
                        <strong>Header / footer on this page.</strong>
                        Design (logo, colors, cart) → <a href="<?= site_url('cms_admin/layout_builder'); ?>"><strong>Storefront layout</strong></a> — create multiple layouts there, then pick one below.
                        <strong>Header menu links</strong> → <a href="<?= site_url('cms_admin/pages'); ?>">CMS Pages</a>: <strong>Show In Header</strong> + <strong>Published</strong>.
                        <strong>Footer menu columns</strong> → turn <strong>Show In Footer</strong> on here to list the page in <a href="<?= site_url('cms_admin/layout_builder?tab=footer'); ?>">Layout Builder → Footer</a> (position, alignment, colors). Custom columns are in <em>Footer menu sections</em>.
                        Add a <strong>Header</strong> or <strong>Footer</strong> section → <em>Storefront profile</em> → choose layout (e.g. <code>site</code>, <code>landing</code>).
                    </div>
                    <div id="sectionHeaderModeFields" style="display:none;">
                        <div class="form-group">
                            <label for="header_mode">Page header source</label>
                            <select name="header_mode" id="header_mode" class="form-control">
                                <option value="custom">Custom HTML (this page only)</option>
                                <option value="storefront_profile" selected>Storefront header profile</option>
                            </select>
                        </div>
                    </div>
                    <div id="sectionFooterModeFields" style="display:none;">
                        <div class="form-group">
                            <label for="footer_mode">Page footer block source</label>
                            <select name="footer_mode" id="footer_mode" class="form-control">
                                <option value="custom">Custom HTML (this page only)</option>
                                <option value="storefront_profile" selected>Storefront footer profile</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="sectionProfileField" style="display:none;">
                        <label for="storefront_profile">Profile</label>
                        <select name="storefront_profile" id="storefront_profile" class="form-control">
                            <?php
                            $add_hdr_profiles = !empty($header_design_profiles) ? $header_design_profiles : (isset($storefront_header_profiles) ? $storefront_header_profiles : array());
                            $add_ftr_profiles = !empty($footer_design_profiles) ? $footer_design_profiles : (isset($storefront_footer_profiles) ? $storefront_footer_profiles : array());
                            ?>
                            <option value="default"><?= htmlspecialchars(function_exists('cms_page_design_default_option_label') ? cms_page_design_default_option_label('header', $add_hdr_profiles) : ('Default Store Header (' . $default_header_profile_slug . ')'), ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php foreach ($add_hdr_profiles as $hp) {
                                $slug = isset($hp['slug']) ? (string) $hp['slug'] : '';
                                if (function_exists('cms_page_design_profile_skip_in_list')) {
                                    if (cms_page_design_profile_skip_in_list($slug, 'header')) {
                                        continue;
                                    }
                                } elseif ($slug === '' || $slug === 'default' || $slug === 'site' || $slug === $default_header_profile_slug) {
                                    continue;
                                }
                                $lbl = isset($hp['label']) ? (string) $hp['label'] : $slug;
                                ?>
                                <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                        <?php if (empty($storefront_schema_ready)) { ?>
                        <p class="help-block text-warning">Storefront table missing — import <code>sma_cms_webshop_header_footer</code> to use profiles.</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-12" id="sectionContainWrap" style="display:none;">
                    <div class="form-group cms-html-code-editor" data-cms-html-editor>
                        <div class="cms-html-code-editor__toolbar">
                            <span class="cms-html-code-editor__label">Section content (HTML)</span>
                            <div class="cms-html-code-editor__tabs" role="tablist">
                                <button type="button" class="cms-html-code-editor__tab is-active" data-mode="code" role="tab" aria-selected="true">Code</button>
                                <button type="button" class="cms-html-code-editor__tab" data-mode="preview" role="tab" aria-selected="false">Preview</button>
                            </div>
                        </div>
                        <div class="cms-html-code-editor__pane cms-html-code-editor__pane--code is-active" data-pane="code">
                            <textarea name="page_text" id="page_text" rows="12" class="form-control skip cms-section-html-textarea cms-html-code-textarea" placeholder="Paste HTML, CSS (&lt;style&gt;), and scripts for this section"></textarea>
                        </div>
                        <div class="cms-html-code-editor__pane cms-html-code-editor__pane--preview" data-pane="preview" hidden>
                            <div class="cms-html-code-editor__preview" id="page_text_preview"></div>
                        </div>
                        <p class="help-block text-muted" id="sectionContainHelp">Raw HTML editor — preserves &lt;style&gt; tags and inline CSS. Used when source is Custom HTML.</p>
                    </div>
                </div>
                <div class="col-md-12" id="sectionCatalogHint" style="display:none;">
                    <div class="alert alert-info" style="margin-top:0;">
                        <strong>Catalog section.</strong> Product/category data is loaded from the e-shop API on the storefront — no HTML body is required.
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group"></div>
                </div>
                </div>
                <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->load->view($this->theme . 'cms_admin/pages/_page_faqs_panel', array(
            'page_data'              => $page_data,
            'page_faqs'              => isset($page_faqs) ? $page_faqs : array(),
            'page_faqs_schema_ready' => !empty($page_faqs_schema_ready),
        )); ?>

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <div class="cms-page-details-card cms-pe-collapsible" id="cmsPeSectionList" data-pe-section="sections">
                    <div class="cms-pe-collapsible__bar">
                        <button type="button" class="cms-pe-collapsible__toggle" aria-expanded="false" aria-controls="cmsPeSectionListBody">
                            <span class="cms-pe-collapsible__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                            <span class="cms-pe-collapsible__heading">
                                <span class="cms-pe-collapsible__title">
                                    <i class="fa fa-list"></i> Current page sections
                                    <?php if ($cms_page_section_count > 0) { ?>
                                    <span class="cms-pe-collapsible__badge"><?= (int) $cms_page_section_count; ?></span>
                                    <?php } ?>
                                </span>
                                <span class="cms-pe-collapsible__desc">
                                    Drag rows to reorder — sort order saves automatically
                                    <span id="cmsSortSaving" class="cms-sort-saving"><i class="fa fa-spinner fa-spin"></i> Saving…</span>
                                    <span id="cmsSortStatus" class="cms-sort-status"></span>
                                </span>
                            </span>
                        </button>
                    </div>
                    <div class="cms-pe-collapsible__body" id="cmsPeSectionListBody">
                <div class="cms-table-wrap">
                <table class="table table-bordered table-striped cms-table ws-modern-table">
                    <thead>
                        <tr>
                            <th>Sort order</th>
                            <th>Section</th>
                            <th>Active</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cmsSectionsSortableBody" data-page-id="<?= (int) $page_data['id']; ?>">
                        <?php if (!empty($page_sections)) { ?>
                            <?php foreach ($page_sections as $index => $section) {
                                $raw_enabled = isset($section['is_enabled']) ? $section['is_enabled'] : 0;
                                $section_enabled = in_array(strtolower(trim((string) $raw_enabled)), array('1', 'true', 'yes', 'on'), true)
                                    || $raw_enabled === 1
                                    || $raw_enabled === true;
                                ?>
                                <tr data-mapping-id="<?= (int) $section['id']; ?>" data-section-enabled="<?= $section_enabled ? '1' : '0'; ?>">
                                    <td>
                                        <span class="cms-drag-handle" title="Drag to reorder"><i class="fa fa-bars"></i></span>
                                        <span class="cms-sort-order"><?= (int) $section['sort_order']; ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($section['section_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="cms-section-status-cell">
                                        <div class="cms-section-toggle-wrap"
                                             data-mapping-id="<?= (int) $section['id']; ?>"
                                             data-toggle-url="<?= htmlspecialchars(site_url('cms_admin/pages/toggle_section/' . (int) $page_data['id'] . '/' . (int) $section['id']), ENT_QUOTES, 'UTF-8'); ?>">
                                            <label class="cms-toggle-switch <?= $section_enabled ? 'is-on' : 'is-off'; ?>"
                                                   title="<?= $section_enabled ? 'Active on webshop — click to hide' : 'Inactive — click to show on webshop'; ?>">
                                                <input type="checkbox"
                                                       class="skip cms-section-enabled-toggle"
                                                       <?= $section_enabled ? 'checked' : ''; ?>
                                                       aria-label="Toggle section active on webshop">
                                                <span class="cms-toggle-slider" aria-hidden="true"></span>
                                            </label>
                                            <span class="cms-section-status-label<?= $section_enabled ? ' is-active' : ' is-inactive'; ?>">
                                                <?= $section_enabled ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning cms-btn-icon" title="Edit Section" data-toggle="modal" data-target="#editSectionModal_<?= (int) $section['id']; ?>">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <?= form_open('cms_admin/pages/delete_section/' . (int) $page_data['id'] . '/' . (int) $section['id'], array('style' => 'display:inline-block;', 'onsubmit' => "return confirm('Delete this section from page?');")); ?>
                                        <button type="submit" class="btn btn-xs btn-danger cms-btn-icon" title="Delete Section">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                        <?= form_close(); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">No sections mapped for this page.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                </div>

                <?php
                $hdr_profiles = !empty($header_design_profiles) && is_array($header_design_profiles)
                    ? $header_design_profiles
                    : (isset($storefront_header_profiles) && is_array($storefront_header_profiles)
                        ? $storefront_header_profiles
                        : array(array('slug' => 'default', 'label' => 'Default')));
                $ftr_profiles = !empty($footer_design_profiles) && is_array($footer_design_profiles)
                    ? $footer_design_profiles
                    : (isset($storefront_footer_profiles) && is_array($storefront_footer_profiles)
                        ? $storefront_footer_profiles
                        : array(array('slug' => 'default', 'label' => 'Default')));
                ?>
                <?php if (!empty($page_sections)) { ?>
                    <?php foreach ($page_sections as $section) { ?>
                        <?php
                        $section_config = json_decode((string) $section['section_contain'], true);
                        $section_type_edit = isset($section['section_type']) ? strtolower(trim((string) $section['section_type'])) : '';
                        $section_is_catalog = in_array($section_type_edit, array('product_grid', 'product_carousel', 'category_grid', 'category_carousel'), true);
                        $section_is_cms_api = in_array($section_type_edit, array('blog_grid', 'testimonials_grid'), true);
                        $section_is_page_faq = in_array($section_type_edit, array('page_faq', 'faq_accordion', 'faq'), true);
                        $section_content = '';
                        $section_heading = '';
                        $section_header_mode = 'custom';
                        $section_footer_mode = 'custom';
                        $section_profile = 'default';
                        if (is_array($section_config)) {
                            $section_content = isset($section_config['content']) ? (string) $section_config['content'] : '';
                            if (isset($section_config['title']) && trim((string) $section_config['title']) !== '') {
                                $section_heading = (string) $section_config['title'];
                            } elseif (isset($section_config['heading']) && trim((string) $section_config['heading']) !== '') {
                                $section_heading = (string) $section_config['heading'];
                            }
                            if (isset($section_config['header_mode'])) {
                                $section_header_mode = (string) $section_config['header_mode'];
                            }
                            if (isset($section_config['footer_mode'])) {
                                $section_footer_mode = (string) $section_config['footer_mode'];
                            }
                            if (isset($section_config['storefront_profile']) && trim((string) $section_config['storefront_profile']) !== '') {
                                $section_profile = (string) $section_config['storefront_profile'];
                            }
                        }
                        $section_is_header = ($section_type_edit === 'header');
                        $section_is_footer = ($section_type_edit === 'footer');
                        $section_is_contact_form = in_array($section_type_edit, array('contact_us_form', 'contact_form'), true);
                        if ($section_is_contact_form && is_array($section_config)) {
                            if ($section_heading === '' && isset($section_config['title'])) {
                                $section_heading = (string) $section_config['title'];
                            }
                        }
                        ?>
                        <div class="modal fade cms-section-edit-modal" id="editSectionModal_<?= (int) $section['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editSectionModalTitle_<?= (int) $section['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog cms-section-edit-dialog">
                                <div class="modal-content">
                                    <?= form_open('cms_admin/pages/update_section/' . (int) $page_data['id'] . '/' . (int) $section['id'], array(
                                        'role'            => 'form',
                                        'class'           => 'cms-section-edit-form',
                                        'id'              => 'editSectionForm_' . (int) $section['id'],
                                        'data-section-type' => $section_type_edit,
                                    )); ?>
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="editSectionModalTitle_<?= (int) $section['id']; ?>">Edit section: <?= htmlspecialchars($section['section_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row cms-section-meta-row">
                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                <div class="form-group">
                                                    <label>Sort order</label>
                                                    <input type="number" name="sort_order" min="1" class="form-control" value="<?= (int) $section['sort_order']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <div class="form-group">
                                                    <label>Section title</label>
                                                    <input type="text" name="section_heading" class="form-control" value="<?= htmlspecialchars($section_heading, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Section title (optional)">
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($section_is_header || $section_is_footer) { ?>
                                        <div class="row cms-section-layout-row">
                                            <div class="col-xs-12">
                                                <?php if ($section_is_header) { ?>
                                                <div class="form-group">
                                                    <label>Page header source</label>
                                                    <select name="header_mode" class="form-control cms-section-header-mode">
                                                        <option value="custom"<?= $section_header_mode !== 'storefront_profile' ? ' selected' : ''; ?>>Custom HTML</option>
                                                        <option value="storefront_profile"<?= $section_header_mode === 'storefront_profile' ? ' selected' : ''; ?>>Storefront header profile</option>
                                                    </select>
                                                </div>
                                                <?php } ?>
                                                <?php if ($section_is_footer) { ?>
                                                <div class="form-group">
                                                    <label>Page footer block source</label>
                                                    <select name="footer_mode" class="form-control cms-section-footer-mode">
                                                        <option value="custom"<?= $section_footer_mode !== 'storefront_profile' ? ' selected' : ''; ?>>Custom HTML</option>
                                                        <option value="storefront_profile"<?= $section_footer_mode === 'storefront_profile' ? ' selected' : ''; ?>>Storefront footer profile</option>
                                                    </select>
                                                </div>
                                                <?php } ?>
                                                <div class="form-group cms-section-profile-field">
                                                    <label>Profile</label>
                                                    <select name="storefront_profile" class="form-control">
                                                        <?php
                                                        $profile_opts = $section_is_footer ? $ftr_profiles : $hdr_profiles;
                                                        $section_chrome = $section_is_footer ? 'footer' : 'header';
                                                        $section_default_slug = $section_is_footer ? $default_footer_profile_slug : $default_header_profile_slug;
                                                        $section_uses_default = function_exists('cms_page_design_uses_store_default_profile')
                                                            ? cms_page_design_uses_store_default_profile($section_profile, $section_chrome)
                                                            : ($section_profile === 'default' || $section_profile === '' || $section_profile === 'site');
                                                        $section_default_label = function_exists('cms_page_design_default_option_label')
                                                            ? cms_page_design_default_option_label($section_chrome, $profile_opts)
                                                            : ('Default Store Profile (' . $section_default_slug . ')');
                                                        ?>
                                                        <option value="default"<?= $section_uses_default ? ' selected' : ''; ?>><?= htmlspecialchars($section_default_label, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php
                                                        foreach ($profile_opts as $hp) {
                                                            $slug = isset($hp['slug']) ? (string) $hp['slug'] : '';
                                                            if (function_exists('cms_page_design_profile_skip_in_list')) {
                                                                if (cms_page_design_profile_skip_in_list($slug, $section_chrome)) {
                                                                    continue;
                                                                }
                                                            } elseif ($slug === '' || $slug === 'default' || $slug === 'site' || $slug === $section_default_slug) {
                                                                continue;
                                                            }
                                                            $lbl = isset($hp['label']) ? (string) $hp['label'] : $slug;
                                                            ?>
                                                            <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"<?= (!$section_uses_default && $section_profile === $slug) ? ' selected' : ''; ?>><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="row cms-section-editor-row">
                                            <div class="col-xs-12">
                                                <?php if ($section_is_contact_form) { ?>
                                                <div class="alert alert-info" style="margin-bottom:0;">
                                                    <strong>Contact form section.</strong>
                                                    Fields, styling, and submission settings are managed in
                                                    <a href="<?= site_url('cms_admin/form_templates'); ?>">CMS Admin &rarr; Form Templates</a>.
                                                    You can update the section title above. Ensure this section is <strong>Active</strong> in the list.
                                                    <?php
                                                    $section_form_key = is_array($section_config) && isset($section_config['form_key'])
                                                        ? trim((string) $section_config['form_key'])
                                                        : '';
                                                    if ($section_form_key !== '') { ?>
                                                    <br><span class="text-muted">Template key: <code><?= htmlspecialchars($section_form_key, ENT_QUOTES, 'UTF-8'); ?></code></span>
                                                    <?php } ?>
                                                </div>
                                                <?php } elseif ($section_is_catalog) { ?>
                                                <div class="alert alert-info" style="margin-bottom:0;">
                                                    <strong>Catalog section.</strong>
                                                    Products and categories load automatically from your e-shop catalog on the webshop.
                                                    Use the optional header above for a section title. Ensure products are enabled in
                                                    <strong>CMS Admin → Catalog</strong> and this section is <strong>Active</strong> in the list.
                                                    <?php if ($section_type_edit === 'product_grid' || $section_type_edit === 'product_carousel') { ?>
                                                    <br><span class="text-muted">Default: up to 12 products (stored as <code>limit</code> in section JSON).</span>
                                                    <?php } ?>
                                                </div>
                                                <?php } elseif ($section_is_page_faq) { ?>
                                                <div class="alert alert-info" style="margin-bottom:0;">
                                                    <strong>Page FAQ section.</strong>
                                                    Questions and answers are managed in the <strong>Page FAQs</strong> panel on this edit screen.
                                                    Use the section title above for the heading on the webshop. Click
                                                    <a href="#cmsPeSectionFaqs" class="cms-jump-page-faqs">Page FAQs</a> to add or edit entries, then click <strong>Save FAQ</strong>.
                                                    Ensure this section is <strong>Active</strong> in the list.
                                                </div>
                                                <?php } elseif ($section_is_cms_api) { ?>
                                                <div class="alert alert-info" style="margin-bottom:0;">
                                                    <strong>Dynamic CMS section.</strong>
                                                    Content loads from ElintOm API on the storefront. Use the section title above for the heading.
                                                    <?php if ($section_type_edit === 'testimonials_grid') { ?>
                                                    Manage entries in <strong>CMS Admin → Testimonials</strong> (status Published + Active).
                                                    <?php } elseif ($section_type_edit === 'blog_grid') { ?>
                                                    Manage posts in <strong>CMS Admin → Blog Posts</strong>.
                                                    <?php } ?>
                                                </div>
                                                <?php } else { ?>
                                                <div class="form-group cms-section-html-field cms-html-code-editor" data-cms-html-editor>
                                                    <div class="cms-html-code-editor__toolbar">
                                                        <label class="cms-html-code-editor__label" for="page_text_<?= (int) $section['id']; ?>">Section content (HTML)</label>
                                                        <div class="cms-html-code-editor__tabs" role="tablist">
                                                            <button type="button" class="cms-html-code-editor__tab is-active" data-mode="code" role="tab" aria-selected="true">Code</button>
                                                            <button type="button" class="cms-html-code-editor__tab" data-mode="preview" role="tab" aria-selected="false">Preview</button>
                                                        </div>
                                                    </div>
                                                    <div class="cms-html-code-editor__pane cms-html-code-editor__pane--code is-active" data-pane="code">
                                                        <textarea id="page_text_<?= (int) $section['id']; ?>" name="page_text" class="form-control skip cms-section-html-textarea cms-html-code-textarea" rows="12" placeholder="Paste HTML, CSS (&lt;style&gt;), and scripts for this section"><?= htmlspecialchars($section_content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    </div>
                                                    <div class="cms-html-code-editor__pane cms-html-code-editor__pane--preview" data-pane="preview" hidden>
                                                        <div class="cms-html-code-editor__preview" id="page_text_preview_<?= (int) $section['id']; ?>"></div>
                                                    </div>
                                                    <p class="help-block text-muted cms-html-code-editor__hint">Code mode preserves &lt;style&gt; tags and inline CSS (not stripped like the visual editor).</p>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary cms-section-save-btn" form="editSectionForm_<?= (int) $section['id']; ?>">Save section</button>
                                    </div>
                                    <?= form_close(); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $this->load->helper('cms_tags');
        $page_scope_type = isset($page_scope_type)
            ? cms_sanitize_page_type((string) $page_scope_type)
            : cms_sanitize_page_type(isset($page_data['page_type']) ? (string) $page_data['page_type'] : 'static');
        $tags_by_category = cms_group_tags_by_category(!empty($tags_master) ? $tags_master : array());
        $cms_page_tag_field_count = 0;
        foreach ($tags_by_category as $items) {
            $cms_page_tag_field_count += is_array($items) ? count($items) : 0;
        }
        $page_tag_values = array();
        if (!empty($page_tags)) {
            foreach ($page_tags as $existing_tag) {
                $tag_id = (int) $existing_tag['tag_id'];
                if (!isset($page_tag_values[$tag_id])) {
                    $page_tag_values[$tag_id] = $existing_tag['value'];
                }
            }
        }
        ?>

        <div class="row cms-edit-wrap" id="cmsPageTagSection" data-page-id="<?= (int) $page_data['id']; ?>" data-page-type="<?= htmlspecialchars($page_scope_type, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="col-lg-12">
                <div class="cms-page-details-card cms-pe-collapsible" id="cmsPeSectionTags" data-pe-section="tags">
                    <div class="cms-pe-collapsible__bar">
                        <button type="button" class="cms-pe-collapsible__toggle" aria-expanded="false" aria-controls="cmsPeSectionTagsBody">
                            <span class="cms-pe-collapsible__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                            <span class="cms-pe-collapsible__heading">
                                <span class="cms-pe-collapsible__title">
                                    <i class="fa fa-tags"></i> Tag values by category
                                    <?php if ($cms_page_tag_field_count > 0) { ?>
                                    <span class="cms-pe-collapsible__badge"><?= (int) $cms_page_tag_field_count; ?></span>
                                    <?php } ?>
                                </span>
                                <span class="cms-pe-collapsible__desc">Page-level SEO, Open Graph, GEO, and schema</span>
                            </span>
                        </button>
                        <div class="cms-pe-collapsible__actions">
                            <button type="submit" form="form_save_cms_tags" name="save_cms_tags" value="1" class="btn btn-info cms-btn-toolbar">
                                <i class="fa fa-save"></i> Save tag values
                            </button>
                        </div>
                    </div>
                    <div class="cms-pe-collapsible__body" id="cmsPeSectionTagsBody">
                <?php
                $attrib = array('role' => 'form', 'id' => 'form_save_cms_tags');
                echo form_open('cms_admin/pages/edit/' . (int) $page_data['id'], $attrib);
                ?>
                <p class="cms-panel-desc cms-pe-collapsible__intro">Per-item product/category/blog schema is under <strong>Entity Tag Mapping</strong>. <a href="<?= site_url('cms_admin/tags_master'); ?>">Tag Master</a> controls which fields appear.</p>

                <div id="cmsPageTagFieldsWrap">
                <?php $this->load->view($this->theme . 'cms_admin/_partials/tag_form_fields', array(
                    'tags_by_category'    => $tags_by_category,
                    'existing_values'     => $page_tag_values,
                    'scope_form'          => 'page',
                    'scope_code'          => $page_scope_type,
                    'scope_label'         => cms_tags_form_scope_label('page', $page_scope_type),
                    'head_tag_import_url' => site_url('cms_admin/pages/ajax_import_head_tags/' . (int) $page_data['id']),
                )); ?>
                </div>

                <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function ($) {
        var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
        var csrfName = cmsCfg.csrfName || "<?= $this->security->get_csrf_token_name(); ?>";
        var csrfHash = cmsCfg.csrfHash || "<?= $this->security->get_csrf_hash(); ?>";
        var cmsHeaderProfiles = <?= json_encode(!empty($header_design_profiles) ? $header_design_profiles : (isset($storefront_header_profiles) ? $storefront_header_profiles : array(array('slug' => 'default', 'label' => 'Default')))); ?>;
        var cmsFooterProfiles = <?= json_encode(!empty($footer_design_profiles) ? $footer_design_profiles : (isset($storefront_footer_profiles) ? $storefront_footer_profiles : array(array('slug' => 'default', 'label' => 'Default')))); ?>;
        var cmsDefaultHeaderProfileSlug = <?= json_encode(isset($default_header_profile_slug) ? $default_header_profile_slug : 'site'); ?>;
        var cmsDefaultFooterProfileSlug = <?= json_encode(isset($default_footer_profile_slug) ? $default_footer_profile_slug : 'site'); ?>;
        var cmsBootstrapProfileSlug = 'site';

        function profileUsesStoreDefault(slug, defaultSlug) {
            slug = String(slug || '').toLowerCase();
            defaultSlug = String(defaultSlug || 'site').toLowerCase();
            if (slug === '' || slug === 'default') {
                return true;
            }
            if (slug === defaultSlug) {
                return true;
            }
            if (slug === 'site' && (defaultSlug === 'site' || defaultSlug === cmsBootstrapProfileSlug)) {
                return true;
            }
            return false;
        }

        function profileSkipInList(slug, defaultSlug) {
            slug = String(slug || '').toLowerCase();
            defaultSlug = String(defaultSlug || 'site').toLowerCase();
            if (slug === '' || slug === 'default') {
                return true;
            }
            if (slug === defaultSlug) {
                return true;
            }
            if (slug === 'site' && (defaultSlug === 'site' || defaultSlug === cmsBootstrapProfileSlug)) {
                return true;
            }
            return false;
        }

        function defaultProfileOptionLabel(profiles, defaultSlug, kind) {
            var name = defaultSlug;
            (profiles || []).forEach(function (p) {
                if (String(p.slug || '').toLowerCase() === String(defaultSlug).toLowerCase() && p.label) {
                    name = p.label;
                }
            });
            if (name && String(name).toLowerCase() !== String(defaultSlug).toLowerCase()) {
                return '— Default Store ' + kind + ' (' + name + ' / ' + defaultSlug + ') —';
            }
            return '— Default Store ' + kind + ' (' + defaultSlug + ') —';
        }

        function repopulateStorefrontProfileSelect($select, profiles, keepValue, defaultSlug, kind) {
            if (!$select || !$select.length) {
                return;
            }
            defaultSlug = defaultSlug || cmsDefaultHeaderProfileSlug;
            kind = kind || 'Header';
            var current = keepValue || $select.val() || 'default';
            var usesDefault = profileUsesStoreDefault(current, defaultSlug);
            $select.empty();
            $select.append(
                $('<option></option>').val('default').text(defaultProfileOptionLabel(profiles, defaultSlug, kind))
            );
            if (usesDefault) {
                $select.val('default');
            }
            (profiles || []).forEach(function (p) {
                var slug = String(p.slug || '').toLowerCase();
                if (profileSkipInList(slug, defaultSlug)) {
                    return;
                }
                var lbl = p.label || slug;
                var $opt = $('<option></option>').val(slug).text(lbl);
                if (!usesDefault && slug === String(current).toLowerCase()) {
                    $opt.prop('selected', true);
                }
                $select.append($opt);
            });
        }

        function getCsrfToken() {
            var tokenFromInput = $('input[name="' + csrfName + '"]').first().val();
            return tokenFromInput || csrfHash;
        }

        function syncCsrfHash(nextHash) {
            if (!nextHash) {
                return;
            }
            csrfHash = nextHash;
            if (window.CmsAdmin && window.CmsAdmin.config) {
                window.CmsAdmin.config.csrfHash = nextHash;
            }
            $('input[name="' + csrfName + '"]').val(nextHash);
        }

        function syncSectionLayoutModeUi() {
            var sectionType = String($('#section_id option:selected').data('section-type') || '').toLowerCase();
            var isHeader = sectionType === 'header';
            var isFooter = sectionType === 'footer';
            var isLayout = isHeader || isFooter;

            $('#sectionLayoutModeWrap').toggle(isLayout);
            $('#sectionHeaderModeFields').toggle(isHeader);
            $('#sectionFooterModeFields').toggle(isFooter);
            $('#sectionProfileField').toggle(isLayout);

            var mode = 'custom';
            if (isHeader) {
                mode = String($('#header_mode').val() || 'custom');
            } else if (isFooter) {
                mode = String($('#footer_mode').val() || 'custom');
            }
            var useProfile = mode === 'storefront_profile';
            $('#sectionContainWrap').toggle(!useProfile && (isLayout || sectionType === 'html_block'));
            if (useProfile) {
                $('#page_text').val('');
            }
            if (isLayout) {
                var profiles = isFooter ? cmsFooterProfiles : cmsHeaderProfiles;
                var defaultSlug = isFooter ? cmsDefaultFooterProfileSlug : cmsDefaultHeaderProfileSlug;
                var kind = isFooter ? 'Footer' : 'Header';
                repopulateStorefrontProfileSelect($('#storefront_profile'), profiles, null, defaultSlug, kind);
            }
        }

        function toggleSectionContainField() {
            var $selected = $('#section_id option:selected');
            var sectionName = String($selected.data('section-name') || '').toLowerCase();
            var sectionType = String($selected.data('section-type') || '').toLowerCase();
            var typesWithContent = ['html_block', 'header', 'footer'];
            var namesWithContent = ['html component', 'header', 'footer'];
            var allowContent = typesWithContent.indexOf(sectionType) !== -1
                || namesWithContent.indexOf(sectionName) !== -1;

            var catalogTypes = ['product_grid', 'product_carousel', 'category_grid', 'category_carousel'];
            var cmsApiTypes = ['blog_grid', 'testimonials_grid'];
            var faqTypes = ['page_faq', 'faq_accordion', 'faq'];
            var isCatalog = catalogTypes.indexOf(sectionType) !== -1;
            var isCmsApi = cmsApiTypes.indexOf(sectionType) !== -1;
            var isPageFaq = faqTypes.indexOf(sectionType) !== -1 || sectionName === 'faq';

            if (!isPageFaq && window.CmsPageFaqs && typeof window.CmsPageFaqs.hidePanel === 'function') {
                window.CmsPageFaqs.hidePanel();
            }

            if (isPageFaq) {
                $('#sectionLayoutModeWrap').hide();
                $('#sectionContainWrap').hide();
                $('#sectionCatalogHint').show().html(
                    '<div class="alert alert-info" style="margin-top:0;">'
                    + '<strong>Page FAQ section.</strong> '
                    + 'Add the section with <strong>Add section</strong>, then manage questions in the '
                    + '<a href="#cmsPeSectionFaqs" class="cms-jump-page-faqs">Page FAQs</a> panel below '
                    + '(Category, Question, Answer) and click <strong>Save FAQ</strong>.'
                    + '</div>'
                );
                $('#page_text').val('');
                if (window.CmsPageFaqs && typeof window.CmsPageFaqs.expandPanel === 'function') {
                    window.CmsPageFaqs.expandPanel(true);
                }
            } else if (isCatalog || isCmsApi) {
                $('#sectionLayoutModeWrap').hide();
                $('#sectionContainWrap').hide();
                $('#sectionCatalogHint').show();
                if (isCmsApi) {
                    var cmsHint = 'CMS section — data loads from ElintOm on the storefront. Set an optional section title above; no HTML body is required.';
                    if (sectionType === 'testimonials_grid') {
                        cmsHint += ' Manage entries under <strong>CMS Admin → Testimonials</strong> (publish + active).';
                    } else if (sectionType === 'blog_grid') {
                        cmsHint += ' Manage posts under <strong>CMS Admin → Blog Posts</strong>.';
                    }
                    $('#sectionCatalogHint').html('<div class="alert alert-info" style="margin-top:0;"><strong>Dynamic CMS section.</strong> ' + cmsHint + '</div>');
                } else {
                    $('#sectionCatalogHint').html('<div class="alert alert-info" style="margin-top:0;"><strong>Catalog section.</strong> Product/category data is loaded from the e-shop API on the storefront — no HTML body is required.</div>');
                }
                $('#page_text').val('');
            } else if (allowContent) {
                $('#sectionCatalogHint').hide();
                if (sectionType === 'html_block') {
                    $('#sectionLayoutModeWrap').hide();
                    $('#sectionContainWrap').show();
                } else {
                    syncSectionLayoutModeUi();
                }
            } else {
                $('#sectionLayoutModeWrap').hide();
                $('#sectionContainWrap').hide();
                $('#sectionCatalogHint').hide();
                $('#page_text').val('');
            }
        }

        $('#section_id').on('change', toggleSectionContainField);
        $('#header_mode, #footer_mode').on('change', syncSectionLayoutModeUi);
        $(document).on('click', '.cms-jump-page-faqs', function (e) {
            e.preventDefault();
            if (window.CmsPageFaqs && typeof window.CmsPageFaqs.expandPanel === 'function') {
                window.CmsPageFaqs.expandPanel(true);
            }
        });
        toggleSectionContainField();

        var _sortSaveTimer = null;
        var _sortInFlight = false;
        var _sortPending = false;

        function saveSectionOrder() {
            // Collect current DOM order
            var orders = {};
            $('#cmsSectionsSortableBody tr[data-mapping-id]').each(function (index) {
                var mappingId = parseInt($(this).data('mapping-id'), 10);
                var sortOrder = index + 1;
                orders[mappingId] = sortOrder;
                $(this).find('.cms-sort-order').text(sortOrder);
            });

            // If a request is already in-flight, mark pending and skip (will retry after)
            if (_sortInFlight) {
                _sortPending = true;
                return;
            }

            _sortInFlight = true;
            $('#cmsSortSaving').show();
            $('#cmsSortStatus').hide().removeClass('ok err').text('');

            // Send orders as a JSON string so CI receives it reliably regardless of
            // XSS-filter settings (the controller already handles is_string → json_decode).
            var payload = { orders: JSON.stringify(orders) };
            payload[csrfName] = getCsrfToken();

            $.ajax({
                url: "<?= site_url('cms_admin/pages/reorder_sections/' . (int) $page_data['id']); ?>",
                type: 'POST',
                dataType: 'json',
                data: payload
            }).done(function (res) {
                // Always refresh CSRF token from the response
                syncCsrfHash(res && res.csrf_hash ? res.csrf_hash : null);
                if (res && res.status === 'success') {
                    $('#cmsSortStatus').text('✓ Order saved').addClass('ok').show();
                    setTimeout(function () { $('#cmsSortStatus').fadeOut(); }, 2500);
                } else {
                    var msg = (res && res.message) ? res.message : 'Failed to save order';
                    $('#cmsSortStatus').text('✗ ' + msg).addClass('err').show();
                }
            }).fail(function (xhr) {
                var errMsg = 'Save failed';
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json && json.message) { errMsg = json.message; }
                } catch (e) { /* ignore parse errors */ }
                $('#cmsSortStatus').text('✗ ' + errMsg + ' (HTTP ' + xhr.status + ')').addClass('err').show();
            }).always(function () {
                $('#cmsSortSaving').hide();
                _sortInFlight = false;
                // If another drag happened while this request was in-flight, save now
                if (_sortPending) {
                    _sortPending = false;
                    saveSectionOrder();
                }
            });
        }

        function debouncedSaveSectionOrder() {
            clearTimeout(_sortSaveTimer);
            _sortSaveTimer = setTimeout(saveSectionOrder, 300);
        }

        if ($.fn.sortable) {
            $('#cmsSectionsSortableBody').sortable({
                axis: 'y',
                handle: '.cms-drag-handle',
                helper: function (e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function () {
                    debouncedSaveSectionOrder();
                }
            });
        } else {
            // Fallback drag-drop when jQuery UI sortable is not loaded.
            var draggedRow = null;
            $('#cmsSectionsSortableBody tr[data-mapping-id]').attr('draggable', true);

            $('#cmsSectionsSortableBody').on('dragstart', 'tr[data-mapping-id]', function (e) {
                draggedRow = this;
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', $(this).data('mapping-id'));
            });

            $('#cmsSectionsSortableBody').on('dragover', 'tr[data-mapping-id]', function (e) {
                e.preventDefault();
                $(this).addClass('cms-drag-over');
                e.originalEvent.dataTransfer.dropEffect = 'move';
            });

            $('#cmsSectionsSortableBody').on('dragleave', 'tr[data-mapping-id]', function () {
                $(this).removeClass('cms-drag-over');
            });

            $('#cmsSectionsSortableBody').on('drop', 'tr[data-mapping-id]', function (e) {
                e.preventDefault();
                $('#cmsSectionsSortableBody tr').removeClass('cms-drag-over');
                if (!draggedRow || draggedRow === this) {
                    return;
                }

                var $target = $(this);
                var targetIndex = $target.index();
                var draggedIndex = $(draggedRow).index();

                if (draggedIndex < targetIndex) {
                    $target.after(draggedRow);
                } else {
                    $target.before(draggedRow);
                }

                debouncedSaveSectionOrder();
            });

            $('#cmsSectionsSortableBody').on('dragend', 'tr[data-mapping-id]', function () {
                $('#cmsSectionsSortableBody tr').removeClass('cms-drag-over');
                draggedRow = null;
            });
        }

        function sanitizeUrlSegment(value) {
            var safe = String(value || '')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9/_-]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/\/+/g, '/')
                .replace(/^\/+|\/+$/g, '')
                .replace(/(^-|-$)/g, '');

            return safe;
        }

        $('#page_name').on('input', function () {
            var $url = $('#url');
            if ($url.data('manually-edited') === true) {
                return;
            }
            $url.val(sanitizeUrlSegment($(this).val()));
        });

        $('#url').on('input', function () {
            $(this).data('manually-edited', true);
            $(this).val(sanitizeUrlSegment($(this).val()));
        });

        var pageId = <?= (int) $page_data['id']; ?>;

        $('#banner_image').on('change', function () {
            var name = (this.files && this.files[0]) ? this.files[0].name : 'No file chosen';
            $('#bannerFileName').text(name);
        });

        function toggleSubmenuOrderField() {
            var hasParent = parseInt($('#parent_page_id').val(), 10) > 0;
            $('#submenu_order').prop('disabled', !hasParent);
        }
        $('#parent_page_id').on('change', toggleSubmenuOrderField);
        toggleSubmenuOrderField();

        function setPageEditMode(active) {
            var on = !!active;
            $('#cms_edit_mode').val(on ? '1' : '0');
            $('#cmsSaveChangesActions').toggleClass('is-hidden', !on);
            $('#cmsPageFields').toggleClass('is-hidden', !on);
            $('#cmsPageEditHint').toggle(!on);
            $('#page_name, #url').prop('required', on);
        }

        function isUnpublishedEditUrl() {
            return window.location.search.indexOf('unpublished=1') !== -1;
        }

        if (isUnpublishedEditUrl()) {
            setPageEditMode(true);
        }

        function refreshPageToolbar(status) {
            var $publish = $('#cmsPublishControls button[name="publish_page"]');
            var $unpublish = $('#cmsPublishControls button[name="unpublish_page"]');
            var $badge = $('#cmsPageStatusBadge');
            var labelHtml = '<span class="ws-status-dot" aria-hidden="true"></span>';

            if (status === 'published') {
                $publish.prop('disabled', true);
                $unpublish.prop('disabled', false);
                $badge.removeClass('ws-status-draft').addClass('ws-status-published').html(labelHtml + 'Published').attr('data-status', 'published');
                setPageEditMode(false);
            } else {
                $publish.prop('disabled', false);
                $unpublish.prop('disabled', true);
                $badge.removeClass('ws-status-published').addClass('ws-status-draft').html(labelHtml + 'Draft').attr('data-status', 'draft');
                setPageEditMode(isUnpublishedEditUrl() || $('#cms_edit_mode').val() === '1');
            }
        }

        refreshPageToolbar($('#cmsPageStatusBadge').data('status'));

        function syncChromeDesignsToHiddenFields() {
            var headerVal = $('#page_header_design').val() || '';
            var footerVal = $('#page_footer_design').val() || '';
            $('#publish_header_design, #save_changes_header_design').val(headerVal);
            $('#publish_footer_design, #save_changes_footer_design').val(footerVal);
        }

        $('#form_cms_publish_page').on('submit', function () {
            syncChromeDesignsToHiddenFields();
        });

        $('#btnCmsSaveChanges').on('click', function () {
            if ($('#cmsPageFields').hasClass('is-hidden')) {
                alert('Unpublish the page first to edit page details.');
                return;
            }
            $('#form_cms_page_edit').trigger('submit');
        });

        $('#cmsSectionsSortableBody').on('mousedown click', '.cms-section-status-cell, .cms-section-toggle-wrap', function (e) {
            e.stopPropagation();
        });

        $('#form_cms_page_edit').on('submit', function (e) {
            if ($('#cmsPageFields').hasClass('is-hidden')) {
                e.preventDefault();
                return false;
            }

            if ($('#cms_edit_mode').val() !== '1') {
                e.preventDefault();
                alert('Unpublish the page first to edit page details.');
                return false;
            }

            var $name = $('#page_name');
            var $url = $('#url');

            var name = $.trim($name.val());
            var urlSegment = sanitizeUrlSegment($url.val());

            if (name === '' || urlSegment === '') {
                e.preventDefault();
                alert('Please fill page name and a valid URL path.');
                return false;
            }

            $url.val(urlSegment);
            syncChromeDesignsToHiddenFields();

            var allowedExt = /\.(jpg|jpeg|png|gif)$/i;
            var maxBytes = 2 * 1024 * 1024;
            var fileInputs = ['#banner_image'];
            for (var i = 0; i < fileInputs.length; i++) {
                var fileInput = $(fileInputs[i])[0];
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    continue;
                }
                var file = fileInput.files[0];
                if (!allowedExt.test(file.name)) {
                    e.preventDefault();
                    alert('Only JPG, JPEG, PNG, and GIF files are allowed.');
                    return false;
                }
                if (file.size > maxBytes) {
                    e.preventDefault();
                    alert('Image size must be 2 MB or less.');
                    return false;
                }
            }
        });

    })(jQuery);
</script>
