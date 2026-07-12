<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>



<div class="cms-header-designs-page">

    <div class="cms-back-header">

        <a href="<?= site_url('cms_admin/footer_designs'); ?>" class="cms-back-btn"><i class="fa fa-arrow-left"></i> Footer designs</a>

        <div class="cms-page-title-wrap">

            <h2 class="cms-page-title"><i class="fa fa-th-large"></i> New footer design</h2>

            <p class="cms-page-subtitle">Step 1 of 3: design ID + name. Next you add tagline, links, copyright.</p>

        </div>

    </div>



    <?php $this->load->view($this->theme . 'cms_admin/_partials/design_workflow_guide', array(

        'design_type' => 'footer',

        'context' => 'index',

    )); ?>



    <div class="ws-card-box cms-header-design-form-card">

        <?php echo form_open('cms_admin/footer_designs/create', array('class' => 'cms-header-design-create-form')); ?>

        <div class="row">

            <div class="col-md-4">

                <div class="form-group">

                    <label for="profile_slug">Design ID (slug) <span class="text-danger">*</span></label>

                    <input type="text" name="profile_slug" id="profile_slug" class="form-control" required

                           maxlength="32" pattern="[a-zA-Z0-9_-]+" placeholder="e.g. home, main"

                           autocomplete="off">

                    <p class="help-block">Simple ID only — not <code>landing__footer_tagline</code> (that is for content items).</p>

                </div>

            </div>

            <div class="col-md-6">

                <div class="form-group">

                    <label for="profile_label">Display name</label>

                    <input type="text" name="profile_label" id="profile_label" class="form-control"

                           placeholder="e.g. Main site footer">

                </div>

            </div>

        </div>

        <div class="form-group">

            <label class="cms-storefront-checkbox">

                <input type="checkbox" name="seed_starter" value="1" checked>

                Add starter content (tagline, link, copyright) — recommended

            </label>

        </div>

        <button type="submit" name="save_footer_design" value="1" class="btn btn-primary btn-lg">Create footer design</button>

        <?= form_close(); ?>

    </div>

</div>

