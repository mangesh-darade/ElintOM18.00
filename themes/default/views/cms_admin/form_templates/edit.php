<?php defined('BASEPATH') OR exit('No direct script access allowed');
$template = isset($template) && is_array($template) ? $template : array();
$is_new = !empty($is_new);
$config = isset($template['config']) && is_array($template['config']) ? $template['config'] : array();
$style_presets = isset($style_presets) && is_array($style_presets) ? $style_presets : array();
?>

<div class="cms-form-templates-edit">
    <div class="cms-back-header">
        <a href="<?= site_url('cms_admin/form_templates'); ?>" class="cms-back-btn">
            <i class="fa fa-arrow-left"></i> Back to templates
        </a>
        <div class="cms-page-title-wrap">
            <h1 class="cms-page-title">
                <i class="fa fa-wpforms"></i>
                <?= $is_new ? 'New Form Template' : 'Edit Form Template'; ?>
            </h1>
            <p class="cms-page-subtitle">Build reusable forms with multiple fields and custom design/CSS.</p>
        </div>
    </div>

    <?= form_open('', array('id' => 'formTemplateSaveForm', 'class' => 'cms-ft-save-form')); ?>
    <input type="hidden" name="save_form_template" value="1">

    <div class="ws-card-box" style="margin-bottom:20px;">
        <h4 class="cms-section-title" style="margin-top:0;"><i class="fa fa-cog"></i> Template settings</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Template name <span class="text-danger">*</span></label>
                    <input type="text" name="form_name" class="form-control" required
                           value="<?= htmlspecialchars((string) (isset($template['form_name']) ? $template['form_name'] : ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Contact Us — Main">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Form key <span class="text-danger">*</span></label>
                    <input type="text" name="form_key" class="form-control" required pattern="[a-z0-9._-]+"
                           value="<?= htmlspecialchars((string) (isset($template['form_key']) ? $template['form_key'] : ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="contact_main" autocapitalize="off" spellcheck="false"
                           title="Lowercase letters, numbers, dots, underscores, hyphens only"
                           <?= $is_new ? '' : 'readonly'; ?>>
                    <p class="help-block">Unique ID for this theme (lowercase). Used when linking from CMS page sections.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Auto-load page URL <span class="text-muted">(optional)</span></label>
                    <input type="text" name="page_url" class="form-control"
                           value="<?= htmlspecialchars((string) (isset($template['page_url']) ? $template['page_url'] : ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="/contact">
                </div>
            </div>
            <div class="col-md-6" style="margin-top:15px;">
                <div class="form-group">
                    <label>Google reCAPTCHA Site Key <span class="text-muted">(optional)</span></label>
                    <input type="text" name="recaptcha_site_key" class="form-control"
                           value="<?= htmlspecialchars((string) (isset($config['recaptcha_site_key']) ? $config['recaptcha_site_key'] : ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g. 6LctZ8UqAAAAAP_...">
                    <p class="help-block">If empty, fallback "I am not a robot" checkbox will be used.</p>
                </div>
            </div>
            <div class="col-md-6" style="margin-top:15px;">
                <div class="form-group">
                    <label>Google reCAPTCHA Secret Key <span class="text-muted">(optional)</span></label>
                    <input type="text" name="recaptcha_secret_key" class="form-control"
                           value="<?= htmlspecialchars((string) (isset($config['recaptcha_secret_key']) ? $config['recaptcha_secret_key'] : ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g. 6LctZ8UqAAAAAJ_...">
                    <p class="help-block">Secret verification key paired with the site key.</p>
                </div>
            </div>
            <div class="col-md-12" style="margin-top:10px;">
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_active" value="1" <?= !isset($template['is_active']) || !empty($template['is_active']) ? 'checked' : ''; ?>>
                    Active (available on storefront)
                </label>
            </div>
        </div>
    </div>

    <div class="ws-card-box">
        <h4 class="cms-section-title" style="margin-top:0;"><i class="fa fa-wpforms"></i> Form builder</h4>
        <p class="text-muted" style="margin:-8px 0 16px;font-size:13px;">Add fields, preview the layout, and customize design before saving.</p>
        <?php $this->load->view($this->theme . 'cms_admin/_partials/form_template_builder', array(
            'builder_id'      => 'formTemplateBuilder',
            'builder_config'  => $config,
            'style_presets'   => $style_presets,
            'contact_form_phone_countries' => isset($contact_form_phone_countries) ? $contact_form_phone_countries : array(),
            'contact_form_countries' => isset($contact_form_countries) ? $contact_form_countries : array(),
        ));
        ?>
    </div>

    <div class="cms-ft-save-bar">
        <button type="submit" class="btn btn-primary btn-lg" id="formTemplateSaveBtn" form="formTemplateSaveForm">
            <i class="fa fa-save"></i> Save Form Template
        </button>
        <a href="<?= site_url('cms_admin/form_templates'); ?>" class="btn btn-default btn-lg">Cancel</a>
    </div>
    <?= form_close(); ?>
</div>
