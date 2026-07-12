<?php defined('BASEPATH') OR exit('No direct script access allowed');
require __DIR__ . DIRECTORY_SEPARATOR . '_init.php';
?>

<div class="cms-layout-builder" id="cmsLayoutBuilder" data-tab="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>"
     data-preview-url="<?= htmlspecialchars($preview_url, ENT_QUOTES, 'UTF-8'); ?>"
     data-page-url="<?= htmlspecialchars($tab_base, ENT_QUOTES, 'UTF-8'); ?>"
     data-profile="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-header-profile="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-footer-profile="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-cms-footer-pages="<?= htmlspecialchars($footer_link_pages_json, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="cms-layout-builder__top">
        <div>
            <h2 class="cms-page-title"><i class="fa fa-object-group"></i> Header & Footer</h2>
            <p class="cms-layout-builder__sub">Header and footer layouts are edited separately (each tab remembers its own profile). Assign layouts on <strong>Edit CMS Page → Page header/footer design</strong>.<?= $tab === 'footer' ? ' Drag footer elements to reorder; CMS pages with <strong>Show In Footer</strong> appear in the list automatically.' : ' Header menu links come from <a href="' . site_url('cms_admin/pages') . '">CMS Pages</a> (<strong>Show In Header</strong>).' ?></p>
        </div>
        <div class="cms-layout-builder__tabs">
            <a class="cms-layout-builder__tab<?= $tab === 'header' ? ' is-active' : ''; ?>" href="<?= htmlspecialchars($header_tab_href, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-object-group"></i> Header</a>
            <a class="cms-layout-builder__tab<?= $tab === 'footer' ? ' is-active' : ''; ?>" href="<?= htmlspecialchars($footer_tab_href, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-th-large"></i> Footer</a>
        </div>
    </div>

    <div class="cms-layout-builder__profiles">
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
        <button type="button" class="btn btn-default btn-sm" id="cmsLbNewLayoutBtn" title="New layout"><i class="fa fa-plus"></i> New</button>
        <button type="button" class="btn btn-default btn-sm" id="cmsLbDupLayoutBtn" title="Duplicate current"><i class="fa fa-copy"></i></button>
        <?php if (!$is_default_profile) { ?>
        <?= form_open('cms_admin/layout_builder/set_default_profile', array('class' => 'cms-layout-builder__set-default-form', 'style' => 'display:inline-block;')); ?>
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
        <span class="cms-layout-builder__profile-id">ID: <code><?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?></code><?= $is_default_profile ? ' <span class="label label-primary">Default ' . htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') . '</span>' : ''; ?><?= $is_bootstrap_profile ? ' <span class="label label-default">Built-in</span>' : ''; ?></span>
        <?php if ($tab === 'header' && $footer_profile_slug !== $header_profile_slug) { ?>
        <span class="text-muted cms-layout-builder__other-profile">Footer layout: <code><?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?></code></span>
        <?php } elseif ($tab === 'footer' && $footer_profile_slug !== $header_profile_slug) { ?>
        <span class="text-muted cms-layout-builder__other-profile">Header layout: <code><?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?></code></span>
        <?php } ?>
    </div>

    <div id="cmsLbNewLayoutPanel" class="cms-layout-builder__new-panel" hidden>
        <?= form_open('cms_admin/layout_builder/create_profile', array('class' => 'cms-layout-builder__new-form')); ?>
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-inline">
            <div class="form-group">
                <label>New layout ID</label>
                <input type="text" name="profile_slug" class="form-control input-sm" placeholder="e.g. landing" maxlength="32" required pattern="[a-zA-Z0-9][a-zA-Z0-9_-]*">
            </div>
            <div class="form-group">
                <label>Display name</label>
                <input type="text" name="profile_label" class="form-control input-sm" placeholder="e.g. Landing page">
            </div>
            <label class="checkbox-inline">
                <input type="checkbox" name="copy_from_current" value="1"> Copy current <?= htmlspecialchars($tab === 'footer' ? 'footer' : 'header', ENT_QUOTES, 'UTF-8'); ?> layout
            </label>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Create</button>
            <button type="button" class="btn btn-default btn-sm cms-lb-cancel-new">Cancel</button>
        </div>
        <?= form_close(); ?>
    </div>

    <div id="cmsLbDupLayoutPanel" class="cms-layout-builder__new-panel" hidden>
        <?= form_open('cms_admin/layout_builder/duplicate_profile'); ?>
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-inline">
            <div class="form-group">
                <label>New ID</label>
                <input type="text" name="duplicate_slug" class="form-control input-sm" placeholder="e.g. landing-copy" required>
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="duplicate_label" class="form-control input-sm" value="<?= htmlspecialchars($profile_label, ENT_QUOTES, 'UTF-8'); ?> copy">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-copy"></i> Duplicate</button>
            <button type="button" class="btn btn-default btn-sm cms-lb-cancel-dup">Cancel</button>
        </div>
        <?= form_close(); ?>
    </div>
