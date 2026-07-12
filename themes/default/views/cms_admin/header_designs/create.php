<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="cms-header-designs-page">
    <div class="cms-back-header">
        <a href="<?= site_url('cms_admin/header_designs'); ?>" class="cms-back-btn"><i class="fa fa-arrow-left"></i> Header designs</a>
        <div class="cms-page-title-wrap">
            <h2 class="cms-page-title"><i class="fa fa-object-group"></i> New header design</h2>
            <p class="cms-page-subtitle">Step 1 of 3: pick a short ID (slug) and a friendly name. Next screen you add logo, phone, etc.</p>
        </div>
    </div>

    <?php $this->load->view($this->theme . 'cms_admin/_partials/design_workflow_guide', array(
        'design_type' => 'header',
        'context' => 'index',
    )); ?>

    <div class="ws-card-box cms-header-design-form-card">
        <?php echo form_open('cms_admin/header_designs/create', array('class' => 'cms-header-design-create-form')); ?>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="profile_slug">Design ID (slug) <span class="text-danger">*</span></label>
                    <input type="text" name="profile_slug" id="profile_slug" class="form-control" required
                           maxlength="32" pattern="[a-zA-Z0-9_-]+" placeholder="e.g. home, landing"
                           autocomplete="off">
                    <p class="help-block">You will select this ID on CMS Pages. Use simple words: <code>home</code>, <code>shop</code>.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="profile_label">Display name</label>
                    <input type="text" name="profile_label" id="profile_label" class="form-control"
                           placeholder="e.g. Landing page header">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="cms-storefront-checkbox">
                <input type="checkbox" name="seed_starter" value="1" checked>
                Add starter content (promo, phone, home link) — recommended
            </label>
        </div>
        <button type="submit" name="save_header_design" value="1" class="btn btn-primary btn-lg">Create header design</button>
        <?= form_close(); ?>
    </div>
</div>
