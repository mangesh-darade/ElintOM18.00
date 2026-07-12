<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-content">
        <div class="cms-back-header">
            <a href="<?= site_url('cms_admin_panel'); ?>" class="cms-back-btn">
                <i class="fa fa-arrow-left"></i> Back to CMS Admin Panel
            </a>
            <h1 class="cms-page-title"><i class="fa fa-plus-square-o"></i> Create CMS Page</h1>
            <p class="cms-page-subtitle">Configure the page URL path and branding media values.</p>
        </div>

        <div class="row cms-edit-wrap">
            <div class="col-lg-12">
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'form_cms_page_add');
                echo form_open_multipart('webshop_settings/add_cms_page', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-12" style="margin-bottom: 20px; display: flex; gap: 12px;">
                        <button type="submit" name="create_cms_page" value="1" class="btn btn-primary">
                            <i class="fa fa-plus-circle"></i> Create CMS Page
                        </button>
                        <a href="<?= site_url('webshop_settings/cms_pages'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="page_name">Page Name</label>
                            <input type="text" name="page_name" id="page_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="url">URL</label>
                            <input type="text" name="url" id="url" class="form-control" placeholder="/about-us" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="banner_image">Page Banner Image</label>
                            <input type="file" name="banner_image" id="banner_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                            <p class="help-block text-muted">Allowed: JPG, JPEG, PNG, GIF | Max size: 2 MB</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="logo_image">Page Logo Image</label>
                            <input type="file" name="logo_image" id="logo_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                            <p class="help-block text-muted">Allowed: JPG, JPEG, PNG, GIF | Max size: 2 MB</p>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
