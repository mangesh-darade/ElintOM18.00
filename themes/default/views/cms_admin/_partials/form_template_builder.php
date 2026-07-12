<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Full form template builder: fields + design + live preview.
 *
 * @var string $builder_id
 * @var array  $builder_config  title, subtitle, button_text, source, fields, style
 * @var array  $style_presets
 */
$builder_id = isset($builder_id) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $builder_id) : 'formTemplateBuilder';
$config = isset($builder_config) && is_array($builder_config) ? $builder_config : array();
$style_presets = isset($style_presets) && is_array($style_presets) ? $style_presets : array();

$ft_title = isset($config['title']) ? (string) $config['title'] : '';
$cf_subtitle = isset($config['subtitle']) ? (string) $config['subtitle'] : '';
$cf_button = isset($config['button_text']) && trim((string) $config['button_text']) !== ''
    ? (string) $config['button_text'] : 'Send Message';
$cf_source = isset($config['source']) && trim((string) $config['source']) !== ''
    ? (string) $config['source'] : 'webshop_contact_form';
$cf_fields = isset($config['fields']) && is_array($config['fields']) ? $config['fields'] : array();
$cf_style = isset($config['style']) && is_array($config['style']) ? $config['style'] : array();

$cf_fields_json = json_encode($cf_fields, JSON_UNESCAPED_UNICODE);
if ($cf_fields_json === false) {
    $cf_fields_json = '[]';
}
$cf_style_json = json_encode($cf_style, JSON_UNESCAPED_UNICODE);
if ($cf_style_json === false) {
    $cf_style_json = '{}';
}
$preset_key_current = isset($cf_style['preset_key']) ? trim((string) $cf_style['preset_key']) : 'default';
if ($preset_key_current === '') {
    $preset_key_current = 'default';
}
$preset_swatch_keys = array('container_bg', 'title_color', 'button_bg');
$presets_json = json_encode($style_presets, JSON_UNESCAPED_UNICODE);
if ($presets_json === false) {
    $presets_json = '{}';
}
$phone_countries = isset($contact_form_phone_countries) && is_array($contact_form_phone_countries)
    ? $contact_form_phone_countries
    : array();
if (empty($phone_countries)) {
    $CI =& get_instance();
    $CI->load->helper('contact_form_phone');
    $phone_countries = contact_form_phone_countries();
}
$phone_countries_json = json_encode($phone_countries, JSON_UNESCAPED_UNICODE);
if ($phone_countries_json === false) {
    $phone_countries_json = '[]';
}
$form_countries = isset($contact_form_countries) && is_array($contact_form_countries)
    ? $contact_form_countries
    : array();
if (empty($form_countries)) {
    $CI =& get_instance();
    $CI->load->helper(array('contact_form_phone', 'contact_form_country'));
    $form_countries = function_exists('contact_form_country_list') ? contact_form_country_list() : array();
}
$form_countries_json = json_encode($form_countries, JSON_UNESCAPED_UNICODE);
if ($form_countries_json === false) {
    $form_countries_json = '[]';
}
?>
<script>window.CF_PHONE_COUNTRIES = <?= $phone_countries_json ?>;window.CF_COUNTRIES = <?= $form_countries_json ?>;</script>
<div class="cms-form-template-builder" id="<?= htmlspecialchars($builder_id, ENT_QUOTES, 'UTF-8'); ?>"
     data-form-template-builder="1"
     data-style-presets="<?= htmlspecialchars($presets_json, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="cms-ft-layout">
        <aside class="cms-ft-preview-panel">
            <div class="cms-ft-preview-head">
                <div class="cms-ft-preview-head__title">
                    <strong>Live preview</strong>
                    <span class="text-muted cms-ft-preview-design-label">Updates as you edit</span>
                </div>
                <div class="cms-ft-preview-modes">
                    <button type="button" class="cms-ft-preview-mode is-active" data-preview-mode="desktop" title="Desktop preview">
                        <i class="fa fa-desktop"></i> Desktop
                    </button>
                    <button type="button" class="cms-ft-preview-mode" data-preview-mode="tablet" title="Tablet preview">
                        <i class="fa fa-tablet"></i> Tablet
                    </button>
                    <button type="button" class="cms-ft-preview-mode" data-preview-mode="mobile" title="Mobile preview">
                        <i class="fa fa-mobile"></i> Mobile
                    </button>
                </div>
            </div>
            <div class="cms-ft-preview-stage">
                <div class="cms-ft-preview-viewport is-desktop" data-preview-viewport="1">
                    <div class="cms-ft-preview-frame">
                        <style class="cms-ft-preview-style"></style>
                        <div class="cms-ft-preview-layout contact-us-layout contact-us-layout--full">
                            <div class="contact-us-layout__aside cms-ft-preview-aside" aria-hidden="true">
                                <div class="cms-ft-preview-aside__mock">
                                    <span class="cms-ft-preview-aside__label">Page content</span>
                                    <p class="cms-ft-preview-aside__text">Headline, body copy, or hero image from a CMS HTML block on the same page.</p>
                                </div>
                            </div>
                            <div class="contact-us-layout__form">
                                <div class="cms-ft-preview-html contact-us-component">
                                    <h3 class="contact-us-title cms-ft-preview-title"></h3>
                                    <p class="contact-us-subtitle cms-ft-preview-subtitle"></p>
                                    <div class="contact-us-form">
                                        <div class="contact-us-grid">
                                            <div class="cms-ft-preview-fields"></div>
                                        </div>
                                        <div class="contact-us-captcha-wrap" style="margin-bottom:16px;">
                                            <div style="display:flex;align-items:center;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;max-width:302px;">
                                                <input type="checkbox" disabled style="width:20px;height:20px;margin:0 12px 0 0;cursor:default;">
                                                <span style="font-size:13.5px;color:#334155;font-weight:500;flex:1;">I am not a robot</span>
                                                <div style="text-align:center;margin-left:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="#4285F4" style="opacity:0.8;"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                                                    <span style="font-size:8px;color:#64748b;display:block;line-height:1;margin-top:2px;">reCAPTCHA</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="contact-us-submit-wrap">
                                            <button type="button" class="contact-us-submit cms-ft-preview-btn">
                                                <span class="cms-ft-preview-btn-text"></span>
                                                <span class="contact-us-submit-chevron" aria-hidden="true">›</span>
                                            </button>
                                        </div>
                                        <div class="contact-us-security cms-ft-preview-security">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Your information is secure and confidential.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="cms-ft-editor">
            <section class="cms-ft-fields-section" id="<?= $builder_id; ?>_fields_section">
                <div class="cms-ft-section-head">
                    <h4 class="cms-ft-section-title"><i class="fa fa-list-ul"></i> Form fields</h4>
                    <p class="cms-ft-section-desc text-muted">Configure the inputs visitors fill out. Map fields to lead data (name, phone, email, message) where possible.</p>
                </div>

                <div class="row cms-ft-meta-row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Form title</label>
                            <input type="text" class="form-control cms-ft-title" name="form_template_title" value="<?= htmlspecialchars($ft_title, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contact Us">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Form subtitle</label>
                            <input type="text" class="form-control cms-cf-subtitle" value="<?= htmlspecialchars($cf_subtitle, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Optional subtitle">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Button text</label>
                            <input type="text" class="form-control cms-cf-button-text" value="<?= htmlspecialchars($cf_button, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label>Lead source tag</label>
                            <input type="text" class="form-control cms-cf-source" name="contact_form_source" value="<?= htmlspecialchars($cf_source, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>

                <div class="cms-contact-form-builder" id="<?= $builder_id; ?>_fields" data-contact-form-builder="1">
                    <input type="hidden" name="contact_form_subtitle" class="cms-cf-subtitle-hidden" value="<?= htmlspecialchars($cf_subtitle, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="contact_form_button_text" class="cms-cf-button-hidden" value="<?= htmlspecialchars($cf_button, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="contact_form_fields_json" class="cms-cf-fields-json" value="<?= htmlspecialchars($cf_fields_json, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="cms-cf-fields-head">
                        <strong>Field list</strong>
                        <span class="text-muted cms-cf-fields-hint">Each row is one input on the storefront form.</span>
                        <button type="button" class="btn btn-default btn-xs cms-cf-add-field"><i class="fa fa-plus"></i> Add field</button>
                    </div>
                    <div class="table-responsive cms-cf-table-wrap">
                        <table class="table table-bordered table-condensed cms-cf-fields-table">
                            <thead>
                                <tr>
                                    <th class="cms-cf-col-name">Name</th>
                                    <th class="cms-cf-col-label">Label</th>
                                    <th class="cms-cf-col-type">Type</th>
                                    <th class="cms-cf-col-req">Req.</th>
                                    <th class="cms-cf-col-map">Map to</th>
                                    <th class="cms-cf-col-extra">Options / placeholder</th>
                                    <th class="cms-cf-col-actions"></th>
                                </tr>
                            </thead>
                            <tbody class="cms-cf-fields-body">
                                <?php
                                $this->load->view($this->theme . 'cms_admin/_partials/contact_form_fields_tbody', array(
                                    'cf_fields' => $cf_fields,
                                ));
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="cms-ft-design-section">
                <div class="cms-ft-section-head">
                    <h4 class="cms-ft-section-title"><i class="fa fa-paint-brush"></i> Design &amp; CSS</h4>
                    <p class="cms-ft-section-desc text-muted">Colors, layout, and optional custom CSS for the form card.</p>
                </div>

                <div class="cms-ft-design-panel" id="<?= $builder_id; ?>_tab_design">
                    <input type="hidden" name="form_template_style_json" class="cms-ft-style-json" value="<?= htmlspecialchars($cf_style_json, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" class="cms-ft-preset-key" value="<?= htmlspecialchars($preset_key_current, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row cms-ft-style-controls" style="margin-top:12px;">
                        <div class="col-md-12">
                            <span class="cms-ft-preset-gallery__label">Design preset</span>
                            <div class="cms-ft-preset-gallery">
                                <?php foreach ($style_presets as $preset_key => $preset) {
                                    $preset_style = isset($preset['style']) && is_array($preset['style']) ? $preset['style'] : array();
                                    $is_active = ($preset_key === $preset_key_current);
                                    ?>
                                <button type="button" class="cms-ft-preset-card<?= $is_active ? ' is-active' : ''; ?>" data-preset-key="<?= htmlspecialchars($preset_key, ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="cms-ft-preset-card__swatch">
                                        <?php foreach ($preset_swatch_keys as $sk) {
                                            $color = isset($preset_style[$sk]) ? (string) $preset_style[$sk] : '#e2e8f0';
                                            ?>
                                        <span style="background:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>"></span>
                                        <?php } ?>
                                    </span>
                                    <span class="cms-ft-preset-card__name"><?= htmlspecialchars(isset($preset['label']) ? $preset['label'] : $preset_key, ENT_QUOTES, 'UTF-8'); ?></span>
                                </button>
                                <?php } ?>
                                <button type="button" class="cms-ft-preset-card<?= ($preset_key_current === 'custom') ? ' is-active' : ''; ?>" data-preset-key="custom">
                                    <span class="cms-ft-preset-card__swatch cms-ft-preset-card__swatch--custom"><i class="fa fa-sliders"></i></span>
                                    <span class="cms-ft-preset-card__name">Custom</span>
                                    <span class="cms-ft-preset-card__hint">Manual edits</span>
                                </button>
                            </div>
                        </div>

                        <?php
                        $style_fields = array(
                            array('key' => 'container_bg', 'label' => 'Container background', 'type' => 'color'),
                            array('key' => 'container_border', 'label' => 'Container border', 'type' => 'color'),
                            array('key' => 'title_color', 'label' => 'Title color', 'type' => 'color'),
                            array('key' => 'subtitle_color', 'label' => 'Subtitle color', 'type' => 'color'),
                            array('key' => 'label_color', 'label' => 'Label color', 'type' => 'color'),
                            array('key' => 'input_border', 'label' => 'Input border', 'type' => 'color'),
                            array('key' => 'input_focus', 'label' => 'Input focus', 'type' => 'color'),
                            array('key' => 'button_bg', 'label' => 'Button background', 'type' => 'color'),
                            array('key' => 'button_hover', 'label' => 'Button hover', 'type' => 'color'),
                            array('key' => 'button_text_color', 'label' => 'Button text', 'type' => 'color'),
                            array('key' => 'container_radius', 'label' => 'Container radius (px)', 'type' => 'number'),
                            array('key' => 'container_padding', 'label' => 'Container padding (px)', 'type' => 'number'),
                            array('key' => 'title_size', 'label' => 'Title size (px)', 'type' => 'number'),
                            array('key' => 'input_radius', 'label' => 'Input radius (px)', 'type' => 'number'),
                        );
                        foreach ($style_fields as $sf) {
                            $val = isset($cf_style[$sf['key']]) ? (string) $cf_style[$sf['key']] : '';
                            ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group cms-ft-style-field" data-style-key="<?= htmlspecialchars($sf['key'], ENT_QUOTES, 'UTF-8'); ?>">
                                <label><?= htmlspecialchars($sf['label'], ENT_QUOTES, 'UTF-8'); ?></label>
                                <?php if ($sf['type'] === 'color') { ?>
                                <input type="color" class="form-control cms-ft-style-input" data-style-key="<?= htmlspecialchars($sf['key'], ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($val !== '' ? $val : '#000000', ENT_QUOTES, 'UTF-8'); ?>">
                                <?php } else { ?>
                                <input type="number" class="form-control cms-ft-style-input" data-style-key="<?= htmlspecialchars($sf['key'], ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>" min="0" step="1">
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group cms-ft-style-field" data-style-key="grid_columns">
                                <label>Grid columns <span class="text-muted">(fields inside card)</span></label>
                                <select class="form-control cms-ft-style-input cms-native-select" data-style-key="grid_columns">
                                    <?php
                                    $cols = isset($cf_style['grid_columns']) ? (string) $cf_style['grid_columns'] : '1';
                                    for ($c = 1; $c <= 4; $c++) {
                                        ?>
                                    <option value="<?= $c; ?>"<?= ((string) $c === $cols) ? ' selected' : ''; ?>><?= $c; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group cms-ft-style-field" data-style-key="card_position">
                                <label>Card position <span class="text-muted">(on page)</span></label>
                                <select class="form-control cms-ft-style-input cms-native-select" data-style-key="card_position">
                                    <?php
                                    $card_pos = isset($cf_style['card_position']) ? (string) $cf_style['card_position'] : 'full';
                                    $card_positions = array(
                                        'full'  => 'Full width (centered)',
                                        'left'  => 'Left column',
                                        'right' => 'Right column',
                                    );
                                    foreach ($card_positions as $pos_key => $pos_label) {
                                        ?>
                                    <option value="<?= htmlspecialchars($pos_key, ENT_QUOTES, 'UTF-8'); ?>"<?= ($pos_key === $card_pos) ? ' selected' : ''; ?>><?= htmlspecialchars($pos_label, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Custom CSS <span class="text-muted">(scoped to .contact-us-component)</span></label>
                                <textarea class="form-control cms-ft-custom-css" rows="6" placeholder=".contact-us-title { letter-spacing: -0.02em; }"><?= htmlspecialchars(isset($cf_style['custom_css']) ? (string) $cf_style['custom_css'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
