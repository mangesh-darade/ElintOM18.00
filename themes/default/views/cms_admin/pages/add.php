<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box cms-page-add">
    <div class="box-content">
        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'form_cms_page_add', 'class' => 'cms-page-add-form');
                echo form_open_multipart('cms_admin/pages/add', $attrib);
                ?>
                <div class="cms-panel-header">
                    <div class="cms-panel-header-text">
                        <h4 class="cms-section-title"><i class="fa fa-plus-circle"></i> New CMS page</h4>
                        <p class="cms-panel-desc">Create a draft page, then publish and add sections from the edit screen. SEO tag import (paste head scripts) is available on the edit page under <strong>Tag values by category</strong>.</p>
                    </div>
                    <button type="submit" name="create_cms_page" value="1" class="btn btn-primary cms-btn-toolbar">
                        <i class="fa fa-plus-circle"></i> Create CMS Page
                    </button>
                </div>

                <div class="cms-page-details-card cms-page-add-fields">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="page_name">Page name <span class="cms-required">*</span></label>
                                <input type="text" name="page_name" id="page_name" class="form-control cms-input" placeholder="e.g. About Us" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="url">URL path <span class="cms-required">*</span></label>
                                <div class="cms-input-prefix-wrap">
                                    <span class="cms-input-prefix">/</span>
                                    <input type="text" name="url" id="url" class="form-control cms-input cms-input-with-prefix" placeholder="about-us" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group cms-form-group">
                                <label for="parent_page_id">Parent page (optional)</label>
                                <select name="parent_page_id" id="parent_page_id" class="form-control cms-input">
                                    <option value="0">None (top-level page)</option>
                                    <?php if (!empty($parent_page_options)) { ?>
                                        <?php foreach ($parent_page_options as $parent_page) { ?>
                                            <option value="<?= (int) $parent_page['id']; ?>">
                                                <?= htmlspecialchars((string) $parent_page['page_name'] . ' (' . (string) $parent_page['url'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <p class="cms-field-hint">Subpages are shown as dropdown items under the selected parent in webshop menu API.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group cms-form-group">
                                <label for="submenu_order">Submenu order</label>
                                <input type="number" min="0" name="submenu_order" id="submenu_order" class="form-control cms-input" value="0" placeholder="0">
                                <p class="cms-field-hint">Used only when Parent page is selected. Lower value shows first in dropdown.</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p class="cms-page-edit-hint cms-page-add-hint">
                                New pages are saved as <strong>Draft</strong>. Use <strong>Publish</strong> on the edit screen after saving changes.
                            </p>
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
                                <p class="cms-field-hint">JPG, JPEG, PNG, or GIF — maximum 2 MB</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function ($) {
    'use strict';

    function sanitizeUrlPath(value) {
        var safe = String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9/_-]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/\/+/g, '/')
            .replace(/(^-|-$)/g, '');

        if (safe !== '' && safe.charAt(0) === '/') {
            safe = safe.substring(1);
        }

        return safe;
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
        $(this).val(sanitizeUrlPath($(this).val()));
    });

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

    $('#form_cms_page_add').on('submit', function (e) {
        var $url = $('#url');
        var url = sanitizeUrlPath($url.val());
        $url.val(url === '' ? '' : '/' + url);

        if (url === '' || url === '/') {
            e.preventDefault();
            alert('Please enter a valid URL path.');
            return false;
        }

        var allowedExt = /\.(jpg|jpeg|png|gif)$/i;
        var maxBytes = 2 * 1024 * 1024;
        var fileInput = $('#banner_image')[0];
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
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
