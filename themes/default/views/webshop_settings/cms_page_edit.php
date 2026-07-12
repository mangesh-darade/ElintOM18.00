<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-content">
        <div class="cms-back-header">
            <a href="<?= site_url('cms_admin_panel'); ?>" class="cms-back-btn">
                <i class="fa fa-arrow-left"></i> Back to CMS Admin Panel
            </a>
            <h1 class="cms-page-title"><i class="fa fa-file-text"></i> Edit CMS Page</h1>
            <p class="cms-page-subtitle">Update page name, sections layout, dynamic categories, and search tag parameters.</p>
        </div>

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'form_cms_page_edit');
                echo form_open_multipart('webshop_settings/edit_cms_page/' . (int) $page_data['id'], $attrib);
                $media_base_path = base_url('assets/mdata/' . $Customer_assets . '/uploads/cms_pages/');
                ?>
                <div class="col-md-12">
                    <div class="cms-action-top" style="margin-bottom:20px;">
                        <button type="submit" name="update_cms_page" value="1" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                        <a href="<?= site_url('webshop_settings/cms_pages'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="page_name">Page Name</label>
                        <input type="text" name="page_name" id="page_name" class="form-control"
                               value="<?= htmlspecialchars($page_data['page_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="text" name="url" id="url" class="form-control"
                               value="<?= htmlspecialchars($page_data['url'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="draft" <?= $page_data['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?= $page_data['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-muted" style="margin: 0 0 8px;">
                                Allowed formats: JPG, JPEG, PNG, GIF | Max size: 2 MB
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_image">Page Banner Image</label>
                                <input type="file" name="banner_image" id="banner_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                                <p class="help-block text-muted" style="margin-bottom:0;">Max size: 2 MB</p>
                                <?php if (!empty($page_data['banner_image'])) { ?>
                                    <div style="margin-top:8px;">
                                        <img src="<?= htmlspecialchars($media_base_path . $page_data['banner_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Banner" class="img-thumbnail" style="max-width:160px; max-height:80px;">
                                        <a href="<?= site_url('webshop_settings/remove_cms_page_media/' . (int) $page_data['id'] . '/banner'); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Remove banner image?');">
                                            <i class="fa fa-trash"></i> Remove Banner
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="logo_image">Page Logo Image</label>
                                <input type="file" name="logo_image" id="logo_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                                <p class="help-block text-muted" style="margin-bottom:0;">Max size: 2 MB</p>
                                <?php if (!empty($page_data['logo_image'])) { ?>
                                    <div style="margin-top:8px;">
                                        <img src="<?= htmlspecialchars($media_base_path . $page_data['logo_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" class="img-thumbnail" style="max-width:120px; max-height:70px;">
                                        <a href="<?= site_url('webshop_settings/remove_cms_page_media/' . (int) $page_data['id'] . '/logo'); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Remove logo image?');">
                                            <i class="fa fa-trash"></i> Remove Logo
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

        <hr>

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <?php
                $attrib = array('role' => 'form', 'id' => 'form_add_cms_section');
                echo form_open('webshop_settings/add_cms_page_section/' . (int) $page_data['id'], $attrib);
                ?>
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col-sm-6">
                        <h4 class="cms-section-title" style="margin-bottom: 0;">Add Dynamic Section</h4>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="submit" class="btn btn-success">Add Dynamic Section</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="section_id">Section</label>
                        <select name="section_id" id="section_id" class="form-control" required>
                            <option value="">Select Section</option>
                            <?php if (!empty($section_masters)) { ?>
                                <?php foreach ($section_masters as $master) { ?>
                                    <option
                                        value="<?= (int) $master['id']; ?>"
                                        data-section-name="<?= htmlspecialchars(strtolower((string) $master['section_name']), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-section-type="<?= htmlspecialchars(strtolower((string) $master['section_type']), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        <?= htmlspecialchars($master['section_name'] . ' (' . $master['section_type'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" min="1" name="sort_order" id="sort_order" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="is_enabled">Status</label>
                        <select name="is_enabled" id="is_enabled" class="form-control">
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="section_heading">Header (Optional)</label>
                        <input type="text" name="section_heading" id="section_heading" class="form-control" placeholder="Enter section heading">
                    </div>
                </div>
                <div class="col-md-12" id="sectionContainWrap" style="display:none;">
                    <div class="form-group">
                        <label for="page_text">
                            Section Contain
                            <a href="javascript:void(0)" id="btn_section_preview" title="Preview Section Contain" style="color: #1e88e5; margin-left: 6px;">
                                <i class="fa fa-eye"></i>
                            </a>
                        </label>
                        <textarea name="page_text" id="page_text" rows="12" class="form-control skip cms-section-html-textarea cms-html-code-textarea" placeholder="Paste HTML, CSS (&lt;style&gt;), and scripts for this section"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group"></div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <h4 class="cms-section-title">Current Page Sections</h4>
                <p class="text-muted">
                    Drag rows using the handle to change position. Sort order saves automatically.
                    <span id="cmsSortSaving" class="cms-sort-saving"><i class="fa fa-spinner fa-spin"></i> Saving...</span>
                    <span id="cmsSortStatus" class="cms-sort-status"></span>
                </p>
                <table class="table table-bordered table-striped cms-table">
                    <thead>
                        <tr>
                            <th>Sort order</th>
                            <th>Section</th>
                            <th>Type</th>
                            <th>Dynamic</th>
                            <th>Status</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cmsSectionsSortableBody">
                        <?php if (!empty($page_sections)) { ?>
                            <?php foreach ($page_sections as $index => $section) { ?>
                                <?php
                                $section_config = json_decode((string) $section['section_contain'], true);
                                $section_content = '';
                                $section_show_header = 0;
                                $section_heading = '';
                                $section_show_footer = 0;
                                $section_show_banner = 0;
                                $section_show_logo = 0;
                                if (is_array($section_config)) {
                                    $section_content = isset($section_config['content']) ? (string) $section_config['content'] : '';
                                    if (isset($section_config['title']) && trim((string) $section_config['title']) !== '') {
                                        $section_heading = (string) $section_config['title'];
                                    } elseif (isset($section_config['heading']) && trim((string) $section_config['heading']) !== '') {
                                        $section_heading = (string) $section_config['heading'];
                                    }
                                    $section_show_header = isset($section_config['show_header']) && in_array(strtolower((string) $section_config['show_header']), array('1', 'true', 'yes', 'on'), true) ? 1 : 0;
                                    $section_show_footer = isset($section_config['show_footer']) && in_array(strtolower((string) $section_config['show_footer']), array('1', 'true', 'yes', 'on'), true) ? 1 : 0;
                                    $section_show_banner = isset($section_config['show_banner']) && in_array(strtolower((string) $section_config['show_banner']), array('1', 'true', 'yes', 'on'), true) ? 1 : 0;
                                    $section_show_logo = isset($section_config['show_logo']) && in_array(strtolower((string) $section_config['show_logo']), array('1', 'true', 'yes', 'on'), true) ? 1 : 0;
                                }
                                ?>
                                <tr data-mapping-id="<?= (int) $section['id']; ?>">
                                    <td>
                                        <span class="cms-drag-handle" title="Drag to reorder"><i class="fa fa-bars"></i></span>
                                        <span class="cms-sort-order"><?= (int) $section['sort_order']; ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($section['section_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($section['section_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= (int) $section['is_dynamic'] === 1 ? 'Yes' : 'No'; ?></td>
                                    <td><?= (int) $section['is_enabled'] === 1 ? 'Enabled' : 'Disabled'; ?></td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-xs btn-info btn-section-eye cms-btn-icon"
                                                title="Preview Section Contain"
                                                data-mapping-id="<?= (int) $section['id']; ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-warning cms-btn-icon" title="Edit Section" data-toggle="modal" data-target="#editSectionModal_<?= (int) $section['id']; ?>">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <?= form_open('webshop_settings/delete_cms_page_section/' . (int) $page_data['id'] . '/' . (int) $section['id'], array('style' => 'display:inline-block;', 'onsubmit' => "return confirm('Delete this section from page?');")); ?>
                                        <button type="submit" class="btn btn-xs btn-danger cms-btn-icon" title="Delete Section">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                        <?= form_close(); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No sections mapped for this page.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <?php if (!empty($page_sections)) { ?>
                    <?php foreach ($page_sections as $section) { ?>
                        <?php
                        $section_config = json_decode((string) $section['section_contain'], true);
                        $section_content = '';
                        $section_heading = '';
                        if (is_array($section_config)) {
                            $section_content = isset($section_config['content']) ? (string) $section_config['content'] : '';
                            if (isset($section_config['title']) && trim((string) $section_config['title']) !== '') {
                                $section_heading = (string) $section_config['title'];
                            } elseif (isset($section_config['heading']) && trim((string) $section_config['heading']) !== '') {
                                $section_heading = (string) $section_config['heading'];
                            }
                        }
                        ?>
                        <script type="application/json" id="cmsSectionJson_<?= (int) $section['id']; ?>"><?= json_encode(array('content' => $section_content), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
                        <div class="modal fade cms-section-edit-modal" id="editSectionModal_<?= (int) $section['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editSectionModalTitle_<?= (int) $section['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog cms-section-edit-dialog">
                                <div class="modal-content">
                                    <?= form_open('webshop_settings/update_cms_page_section/' . (int) $page_data['id'] . '/' . (int) $section['id'], array('role' => 'form', 'class' => 'cms-section-edit-form')); ?>
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="editSectionModalTitle_<?= (int) $section['id']; ?>">Edit section: <?= htmlspecialchars($section['section_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row cms-section-meta-row">
                                            <div class="col-xs-12 col-sm-2 col-md-2">
                                                <div class="form-group">
                                                    <label>Sort order</label>
                                                    <input type="number" name="sort_order" min="1" class="form-control" value="<?= (int) $section['sort_order']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select name="is_enabled" class="form-control">
                                                        <option value="1" <?= (int) $section['is_enabled'] === 1 ? 'selected' : ''; ?>>Enabled</option>
                                                        <option value="0" <?= (int) $section['is_enabled'] === 0 ? 'selected' : ''; ?>>Disabled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-7 col-md-7">
                                                <div class="form-group">
                                                    <label>Header (optional)</label>
                                                    <input type="text" name="section_heading" class="form-control" value="<?= htmlspecialchars($section_heading, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Section heading (optional)">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row cms-section-editor-row">
                                            <div class="col-xs-12">
                                                <div class="form-group cms-section-html-field">
                                                    <label for="page_text_<?= (int) $section['id']; ?>">Section content (HTML)</label>
                                                    <textarea id="page_text_<?= (int) $section['id']; ?>" name="page_text" class="form-control skip cms-section-html-textarea cms-html-code-textarea" rows="12" placeholder="Paste HTML, CSS (&lt;style&gt;), and scripts for this section"><?= htmlspecialchars($section_content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save section</button>
                                    </div>
                                    <?= form_close(); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <hr>

        <?php
        $tags_by_category = array();
        if (!empty($tags_master)) {
            foreach ($tags_master as $master_tag) {
                $category = !empty($master_tag['category']) ? $master_tag['category'] : 'General';
                if (!isset($tags_by_category[$category])) {
                    $tags_by_category[$category] = array();
                }
                $tags_by_category[$category][] = $master_tag;
            }
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

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <?php
                $attrib = array('role' => 'form', 'id' => 'form_save_cms_tags');
                echo form_open('webshop_settings/edit_cms_page/' . (int) $page_data['id'], $attrib);
                ?>
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col-sm-6">
                        <h4 class="cms-section-title" style="margin-bottom: 0;">Tag Values by Category</h4>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="submit" name="save_cms_tags" value="1" class="btn btn-info">Save Tag Values</button>
                    </div>
                </div>
                <ul class="nav nav-tabs" role="tablist">
                    <?php $cat_index = 0; ?>
                    <?php foreach ($tags_by_category as $category_name => $category_tags) { ?>
                        <li role="presentation" class="<?= $cat_index === 0 ? 'active' : ''; ?>">
                            <a href="#tag_cat_<?= $cat_index; ?>" aria-controls="tag_cat_<?= $cat_index; ?>" role="tab" data-toggle="tab">
                                <?= htmlspecialchars(trim((string) $category_name), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                        <?php $cat_index++; ?>
                    <?php } ?>
                </ul>

                <div class="tab-content" style="padding: 15px; border: 1px solid #ddd; border-top: 0;">
                    <?php $cat_index = 0; ?>
                    <?php foreach ($tags_by_category as $category_name => $category_tags) { ?>
                        <div role="tabpanel" class="tab-pane <?= $cat_index === 0 ? 'active' : ''; ?>" id="tag_cat_<?= $cat_index; ?>">
                            <div class="row">
                                <?php foreach ($category_tags as $master_tag) { ?>
                                    <?php $tag_id = (int) $master_tag['id']; ?>
                                    <?php
                                    $display_tag_name = preg_replace('/\s+/', ' ', trim(str_replace('_', ' ', (string) $master_tag['tag_name'])));
                                    ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tag_value_<?= $tag_id; ?>">
                                                <?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tag_value_<?= $tag_id; ?>"
                                                name="tag_values[<?= $tag_id; ?>]"
                                                value="<?= isset($page_tag_values[$tag_id]) ? htmlspecialchars($page_tag_values[$tag_id], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                                placeholder="Enter value for <?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php $cat_index++; ?>
                    <?php } ?>
                </div>

                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sectionContainPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Section Contain Preview</h4>
            </div>
            <div class="modal-body">
                <div id="sectionContainPreviewBody" style="min-height: 120px; border: 1px solid #ddd; padding: 10px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cmsPageMediaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="cmsMediaTitle">Preview</h4>
            </div>
            <div class="modal-body text-center">
                <img id="cmsMediaImage" src="" alt="CMS Media" style="max-width: 100%; max-height: 500px;">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function ($) {
        var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
        var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

        function getCsrfToken() {
            var tokenFromInput = $('input[name="' + csrfName + '"]').first().val();
            return tokenFromInput || csrfHash;
        }

        function toggleSectionContainField() {
            var $selected = $('#section_id option:selected');
            var sectionName = String($selected.data('section-name') || '').toLowerCase();
            var sectionType = String($selected.data('section-type') || '').toLowerCase();
            // Header/Footer also accept body HTML (rendered as the CMS strip body / copyright).
            var typesWithContent = ['html_block', 'header', 'footer'];
            var namesWithContent = ['html component', 'header', 'footer'];
            var allowContent = typesWithContent.indexOf(sectionType) !== -1
                || namesWithContent.indexOf(sectionName) !== -1;

            if (allowContent) {
                $('#sectionContainWrap').show();
            } else {
                $('#sectionContainWrap').hide();
                $('#page_text').val('');
            }
        }

        $('#section_id').on('change', toggleSectionContainField);
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
                url: "<?= site_url('webshop_settings/reorder_cms_page_sections/' . (int) $page_data['id']); ?>",
                type: 'POST',
                dataType: 'json',
                data: payload
            }).done(function (res) {
                // Always refresh CSRF token from the response
                if (res && res.csrf_hash) {
                    csrfHash = res.csrf_hash;
                    $('input[name="' + csrfName + '"]').val(csrfHash);
                }
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

        $('#btn_section_preview').on('click', function () {
            var value = $('#page_text').val() || '';
            $('#sectionContainPreviewBody').html(value !== '' ? value : '<em>No section content to preview.</em>');
            $('#sectionContainPreviewModal').modal('show');
        });

        $('.open-media-modal').on('click', function () {
            var title = $(this).data('title') || 'Preview';
            var image = $(this).data('image') || '';
            $('#cmsMediaTitle').text(title);
            $('#cmsMediaImage').attr('src', image);
            $('#cmsPageMediaModal').modal('show');
        });

        $('.btn-section-eye').on('click', function () {
            var value = '';
            var mappingId = $(this).data('mapping-id');
            if (mappingId) {
                var $script = $('#cmsSectionJson_' + mappingId);
                if ($script.length) {
                    try {
                        var parsed = JSON.parse($script.text());
                        value = parsed && parsed.content ? String(parsed.content) : '';
                    } catch (err) {
                        value = '';
                    }
                }
            }
            $('#sectionContainPreviewBody').html(value !== '' ? value : '<em>No section content to preview.</em>');
            $('#sectionContainPreviewModal').modal('show');
        });

        function sanitizeUrlPath(value) {
            var safe = String(value || '')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9/_-]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/\/+/g, '/')
                .replace(/(^-|-$)/g, '');

            if (safe !== '' && safe.charAt(0) !== '/') {
                safe = '/' + safe;
            }

            return safe === '' ? '/' : safe;
        }

        $('#page_name').on('input', function () {
            var $url = $('#url');
            if ($url.data('manually-edited') === true) {
                return;
            }
            $url.val(sanitizeUrlPath($(this).val()));
        });

        $('#url').on('input', function () {
            $(this).data('manually-edited', true);
        });

        $('#form_cms_page_edit').on('submit', function (e) {
            var $name = $('#page_name');
            var $url = $('#url');

            var name = $.trim($name.val());
            var url = sanitizeUrlPath($url.val());
            $url.val(url);

            if (name === '' || url === '/') {
                e.preventDefault();
                alert('Please fill Page Name and a valid URL path.');
                return false;
            }

            var allowedExt = /\.(jpg|jpeg|png|gif)$/i;
            var maxBytes = 2 * 1024 * 1024;
            var fileInputs = ['#banner_image', '#logo_image'];
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
<script src="<?= base_url('themes/default/assets/cms_admin/js/pages-section-editor.js'); ?>"></script>
