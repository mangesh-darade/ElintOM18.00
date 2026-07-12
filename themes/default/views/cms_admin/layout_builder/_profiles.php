<?php defined('BASEPATH') OR exit('No direct script access allowed');
require __DIR__ . DIRECTORY_SEPARATOR . '_init.php';
$other_profile_slug = ($tab === 'footer') ? $header_profile_slug : $footer_profile_slug;
$other_profile_label = ($tab === 'footer') ? 'Header layout' : 'Footer layout';
?>

    <div class="cms-layout-builder__profiles">
        <div class="cms-layout-builder__profiles-main">
            <div class="cms-layout-builder__profiles-picker">
                <label class="cms-layout-builder__profiles-label" for="cmsLbProfileSelect"><?= htmlspecialchars($layout_tab_label, ENT_QUOTES, 'UTF-8'); ?></label>
                <select id="cmsLbProfileSelect" class="form-control input-sm cms-layout-builder__profile-select">
                    <?php foreach ($layout_profiles as $lp) {
                        $ps = isset($lp['slug']) ? (string) $lp['slug'] : '';
                        $pl = isset($lp['label']) ? (string) $lp['label'] : $ps;
                        $is_def = !empty($lp['is_default']);
                        if ($ps === '') {
                            continue;
                        }
                        $opt_label = $pl . ' (' . $ps . ')';
                        if ($is_def) {
                            $opt_label .= ' — Default';
                        }
                        ?>
                        <option value="<?= htmlspecialchars($ps, ENT_QUOTES, 'UTF-8'); ?>"<?= $ps === $profile_slug ? ' selected' : ''; ?><?= $is_def ? ' data-is-default="1"' : ''; ?>>
                            <?= htmlspecialchars($opt_label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="cms-layout-builder__profiles-actions">
                <button type="button" class="btn btn-default btn-sm" id="cmsLbNewLayoutBtn" title="New layout"><i class="fa fa-plus"></i> New</button>
                <button type="button" class="btn btn-default btn-sm" id="cmsLbDupLayoutBtn" title="Duplicate current"><i class="fa fa-copy"></i></button>
                <?php if (!$is_default_profile) { ?>
                <?= form_open('cms_admin/layout_builder/set_default_profile', array('class' => 'cms-layout-builder__set-default-form')); ?>
                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-info btn-sm" title="Use this layout when CMS pages choose Default Store <?= $tab === 'footer' ? 'Footer' : 'Header'; ?>">
                    <i class="fa fa-star"></i> Set as default
                </button>
                <?= form_close(); ?>
                <?php } ?>
                <?php if (!$is_bootstrap_profile) { ?>
                <a class="btn btn-danger btn-sm" href="<?= site_url('cms_admin/layout_builder/delete_profile/' . rawurlencode($profile_slug)); ?>"
                   onclick="return confirm('Delete this layout and its saved header/footer?');" title="Delete layout"><i class="fa fa-trash-o"></i></a>
                <?php } ?>
            </div>
        </div>
        <div class="cms-layout-builder__profiles-meta">
            <span class="cms-layout-builder__profile-id">
                <span class="cms-layout-builder__meta-label">Editing</span>
                <code><?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?></code>
                <?php if ($is_default_profile) { ?>
                <span class="label label-primary">Default <?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php } ?>
                <?php if ($is_bootstrap_profile) { ?>
                <span class="label label-default">Built-in</span>
                <?php } ?>
            </span>
            <?php if ($other_profile_slug !== $profile_slug) { ?>
            <span class="cms-layout-builder__other-profile">
                <span class="cms-layout-builder__meta-label"><?= htmlspecialchars($other_profile_label, ENT_QUOTES, 'UTF-8'); ?></span>
                <code><?= htmlspecialchars($other_profile_slug, ENT_QUOTES, 'UTF-8'); ?></code>
            </span>
            <?php } ?>
        </div>
    </div>

    <div id="cmsLbNewLayoutPanel" class="cms-layout-builder__new-panel is-collapsed" hidden aria-hidden="true">
        <?= form_open('cms_admin/layout_builder/create_profile', array('class' => 'cms-layout-builder__new-form')); ?>
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="cms-layout-builder__new-panel-grid">
            <div class="form-group">
                <label for="cmsLbNewProfileSlug">New layout ID</label>
                <input type="text" id="cmsLbNewProfileSlug" name="profile_slug" class="form-control input-sm" placeholder="e.g. landing" maxlength="32" required pattern="[a-zA-Z0-9][a-zA-Z0-9_-]*">
            </div>
            <div class="form-group">
                <label for="cmsLbNewProfileLabel">Display name</label>
                <input type="text" id="cmsLbNewProfileLabel" name="profile_label" class="form-control input-sm" placeholder="e.g. Landing page">
            </div>
            <label class="checkbox-inline cms-layout-builder__new-copy">
                <input type="checkbox" name="copy_from_current" value="1"> Copy current <?= htmlspecialchars($tab === 'footer' ? 'footer' : 'header', ENT_QUOTES, 'UTF-8'); ?> layout
            </label>
            <div class="cms-layout-builder__new-panel-btns">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Create</button>
                <button type="button" class="btn btn-default btn-sm cms-lb-cancel-new">Cancel</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>

    <div id="cmsLbDupLayoutPanel" class="cms-layout-builder__new-panel is-collapsed" hidden aria-hidden="true">
        <?= form_open('cms_admin/layout_builder/duplicate_profile'); ?>
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="cms-layout-builder__new-panel-grid">
            <div class="form-group">
                <label for="cmsLbDupProfileSlug">New ID</label>
                <input type="text" id="cmsLbDupProfileSlug" name="duplicate_slug" class="form-control input-sm" placeholder="e.g. landing-copy" required>
            </div>
            <div class="form-group">
                <label for="cmsLbDupProfileLabel">Name</label>
                <input type="text" id="cmsLbDupProfileLabel" name="duplicate_label" class="form-control input-sm" value="<?= htmlspecialchars($profile_label, ENT_QUOTES, 'UTF-8'); ?> copy">
            </div>
            <div class="cms-layout-builder__new-panel-btns">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-copy"></i> Duplicate</button>
                <button type="button" class="btn btn-default btn-sm cms-lb-cancel-dup">Cancel</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
