<?php defined('BASEPATH') OR exit('No direct script access allowed');



$is_edit = !empty($identity_row) && !empty($identity_row['id']);

$action = $is_edit

    ? 'cms_admin/storefront/edit/' . (int) $identity_row['id']

    : 'cms_admin/storefront/add';

$sec_val = $is_edit ? (isset($identity_row['section_type']) ? strtolower(trim((string) $identity_row['section_type'])) : 'header') : 'header';

if ($sec_val !== 'header' && $sec_val !== 'footer') {

    $sec_val = 'header';

}

$fk_val = $is_edit ? (isset($identity_row['field_key']) ? trim((string) $identity_row['field_key']) : '') : '';

$lb_val = $is_edit ? (isset($identity_row['label']) ? trim((string) $identity_row['label']) : '') : '';

$v_val = $is_edit ? (isset($identity_row['value']) ? $identity_row['value'] : '') : '';

$i_val = $is_edit ? (isset($identity_row['icons']) ? $identity_row['icons'] : '') : '';

$so_val = $is_edit ? (isset($identity_row['sort_order']) ? (int) $identity_row['sort_order'] : 0) : 100;

$ia_val = $is_edit ? (!empty($identity_row['is_active'])) : true;

$upload_base = base_url('assets/mdata/' . (isset($Customer_assets) ? $Customer_assets : 'localhost') . '/uploads/');

$show_preview = $is_edit && $v_val !== '' && preg_match('/\\.(jpe?g|png|gif|webp)$/i', $v_val) && strpos($v_val, 'webshop/') === 0;

$schema_ok = !empty($header_footer_schema_ready);

?>

<div class="box cms-storefront-form-page">
    <div class="box-content">
        <div class="cms-storefront-form-shell">
            <div class="cms-storefront-form-header">
                <h2><?= $is_edit ? 'Edit Storefront Row' : 'Add Storefront Row'; ?></h2>
                <p>Configure storefront content with clean structure for header and footer API merge.</p>
            </div>

            <?php if (!$schema_ok) { ?>
            <div class="alert alert-danger cms-storefront-alert">
                <strong>Table missing.</strong> Ensure <code>sma_cms_webshop_header_footer</code> exists in the database before saving.
            </div>
            <?php } ?>

            <?php
            $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'form_storefront_identity');
            echo form_open_multipart($action, $attrib);
            ?>

            <div class="cms-storefront-actionbar">
                <div class="cms-storefront-actionbar-left">
                    <button type="submit" name="save_identity_row" value="1" class="btn btn-primary" <?= $schema_ok ? '' : 'disabled'; ?>>
                        <?= $is_edit ? 'Save changes' : 'Add row'; ?>
                    </button>
                    <a href="<?= site_url('cms_admin/storefront'); ?>" class="btn btn-default">Back to list</a>
                </div>
                <div class="cms-storefront-actionbar-right">
                    <span class="cms-storefront-status-pill"><?= $schema_ok ? 'Schema ready' : 'Schema missing'; ?></span>
                </div>
            </div>

            <div class="cms-storefront-card">
                <h4 class="cms-storefront-card-title">Basic Details</h4>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="section_type">Section <span class="text-danger">*</span></label>
                            <select name="section_type" id="section_type" class="form-control" required <?= $schema_ok ? '' : 'disabled'; ?>>
                                <option value="header"<?= $sec_val === 'header' ? ' selected' : ''; ?>>Header</option>
                                <option value="footer"<?= $sec_val === 'footer' ? ' selected' : ''; ?>>Footer</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="field_key">Field key <span class="text-danger">*</span></label>
                            <?php if ($is_edit) { ?>
                            <p class="form-control-static cms-storefront-readonly"><code><?= htmlspecialchars($fk_val, ENT_QUOTES, 'UTF-8'); ?></code></p>
                            <p class="help-block text-muted">Field key cannot be changed. Delete and add a new row to rename.</p>
                            <?php } else { ?>
                            <input type="text" name="field_key" id="field_key" class="form-control" required
                                   value="<?= htmlspecialchars($fk_val, ENT_QUOTES, 'UTF-8'); ?>"
                                   maxlength="64"
                                   placeholder="Any name you choose"
                                   autocomplete="off"
                                   <?= $schema_ok ? '' : 'disabled'; ?>>
                            <p class="help-block text-muted">Any text up to 64 characters. Unique per section. For multi-design headers use <code>profile__item</code> (e.g. <code>landing__logo_image</code>) or a row with field key <code>header_profile</code> and value = profile slug.</p>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="label">Label</label>
                            <input type="text" name="label" id="label" class="form-control"
                                   value="<?= htmlspecialchars($lb_val, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="Optional — auto: Google Analytics code"
                                   <?= $schema_ok ? '' : 'disabled'; ?>>
                            <p class="help-block text-muted">Leave blank for GA4/GTM rows; label defaults to <strong>Google Analytics code</strong> and gtag still loads in the webshop header.</p>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="sort_order">Sort order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control"
                                   value="<?= (int) $so_val; ?>"
                                   <?= $schema_ok ? '' : 'disabled'; ?>>
                            <p class="help-block text-muted">Lower appears first in lists / API merge order.</p>
                        </div>
                    </div>

                    <div class="col-md-5 col-sm-8">
                        <div class="form-group">
                            <label for="icons">Icon class (optional)</label>
                            <input type="text" name="icons" id="icons" class="form-control"
                                   value="<?= htmlspecialchars($i_val, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="e.g. facebook-f"
                                   <?= $schema_ok ? '' : 'disabled'; ?>>
                            <p class="help-block text-muted">Font Awesome fragment where the theme expects it.</p>
                        </div>
                    </div>

                    <div class="col-md-7 col-sm-12">
                        <div class="cms-storefront-checkbox-wrap">
                            <label class="cms-storefront-checkbox">
                                <input type="checkbox" name="is_active" value="1" <?= $ia_val ? 'checked' : ''; ?> <?= $schema_ok ? '' : 'disabled'; ?>>
                                <span>Active (inactive rows are hidden from the storefront API merge)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cms-storefront-card">
                <h4 class="cms-storefront-card-title">Content Value</h4>
                <div class="form-group">
                    <label for="value">Value</label>
                    <textarea name="value" id="value" class="form-control" rows="4" placeholder="Text, URL, GA4 id (G-…), or path after upload" <?= $schema_ok ? '' : 'disabled'; ?>><?= htmlspecialchars($v_val, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="help-block text-muted">For GA4/GTM use section <strong>header</strong>: paste your full tracking snippet here, or a measurement id like <code><?= htmlspecialchars(storefront_ga_example_measurement_id(), ENT_QUOTES, 'UTF-8'); ?></code>.</p>
                </div>
            </div>

            <div class="cms-storefront-card">
                <h4 class="cms-storefront-card-title">Media Upload</h4>
                <div class="form-group">
                    <label for="media_file">Replace with file (optional)</label>
                    <div class="cms-storefront-upload-box">
                        <input type="file" name="media_file" id="media_file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp" <?= $schema_ok ? '' : 'disabled'; ?>>
                        <p class="help-block text-muted">Allowed: JPG, JPEG, PNG, GIF, WEBP | Max size: 2 MB.</p>
                    </div>
                </div>

                <?php if ($show_preview) { ?>
                <div class="cms-storefront-upload-preview">
                    <p class="text-muted">Current file preview</p>
                    <img src="<?= htmlspecialchars($upload_base . $v_val, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="img-thumbnail cms-storefront-preview-img">
                </div>
                <?php } ?>
            </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>
