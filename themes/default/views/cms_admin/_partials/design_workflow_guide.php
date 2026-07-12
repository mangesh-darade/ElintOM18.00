<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User-friendly steps for Header / Footer designs.
 * @var string $design_type header|footer
 * @var string $profile_slug optional, for step 3
 * @var int    $item_count optional
 * @var string $context index|edit
 */
$design_type = isset($design_type) ? (string) $design_type : 'header';
$is_footer = ($design_type === 'footer');
$label = $is_footer ? 'footer' : 'header';
$label_cap = $is_footer ? 'Footer' : 'Header';
$designs_url = site_url('cms_admin/' . ($is_footer ? 'footer' : 'header') . '_designs');
$pages_url = site_url('cms_admin/pages');
$context = isset($context) ? (string) $context : 'index';
$item_count = isset($item_count) ? (int) $item_count : 0;
$profile_slug = isset($profile_slug) ? (string) $profile_slug : '';
$step1_done = ($context !== 'index');
$step2_done = ($item_count > 0);
$step3_hint = $profile_slug !== '' ? $profile_slug : 'your-design-slug';
?>
<div class="cms-design-guide" role="region" aria-label="<?= $label_cap; ?> design guide">
    <h3 class="cms-design-guide__title"><i class="fa fa-lightbulb-o"></i> How to use <?= $label_cap; ?> designs (3 steps)</h3>
    <ol class="cms-design-guide__steps">
        <li class="cms-design-guide__step<?= $step1_done ? ' is-done' : ' is-current'; ?>">
            <span class="cms-design-guide__num">1</span>
            <div class="cms-design-guide__body">
                <strong>Create a design</strong>
                <p>Give it a name you will recognize (e.g. “Home page header”). The <em>slug</em> is the technical ID used on pages — use simple words: <code>home</code>, <code>landing</code>.</p>
                <?php if ($context === 'index') { ?>
                <a class="btn btn-primary btn-xs" href="<?= site_url('cms_admin/' . ($is_footer ? 'footer' : 'header') . '_designs/create'); ?>">Start: New <?= $label; ?> design</a>
                <?php } ?>
            </div>
        </li>
        <li class="cms-design-guide__step<?= $step2_done ? ' is-done' : ($step1_done ? ' is-current' : ''); ?>">
            <span class="cms-design-guide__num">2</span>
            <div class="cms-design-guide__body">
                <strong>Add pieces to the <?= $label; ?></strong>
                <p>Each piece is one thing visitors see: logo, phone number, promo text, link, etc. Fill the form on the left, click <strong>Add item</strong>, and watch the preview on the right update.</p>
                <?php if ($context === 'edit' && $item_count === 0) { ?>
                <p class="cms-design-guide__tip"><i class="fa fa-arrow-left"></i> Use a quick preset below “Add content item”, or pick <strong>Logo</strong> / <strong>Phone</strong>.</p>
                <?php } elseif ($step2_done) { ?>
                <p class="text-success" style="margin:0;font-size:12px;"><i class="fa fa-check"></i> <?= (int) $item_count; ?> piece<?= $item_count === 1 ? '' : 's'; ?> added.</p>
                <?php } ?>
            </div>
        </li>
        <li class="cms-design-guide__step<?= ($step2_done && $context === 'edit') ? ' is-current' : ''; ?>">
            <span class="cms-design-guide__num">3</span>
            <div class="cms-design-guide__body">
                <strong>Attach design to a CMS page</strong>
                <?php if ($context === 'edit') { ?>
                <p>Scroll the left panel to <strong>Step 3: Put this <?= $label; ?> on a page</strong> → pick a page → click <strong>Assign to selected page</strong>.</p>
                <?php } else { ?>
                <p>Open your design → use <strong>Step 3</strong> in the left panel, or go to <a href="<?= $pages_url; ?>"><strong>CMS Pages</strong></a> and set <strong>Storefront <?= $label; ?> profile</strong> to <code><?= htmlspecialchars($step3_hint, ENT_QUOTES, 'UTF-8'); ?></code>.</p>
                <?php } ?>
                <p class="cms-design-guide__note">The webshop will then show this <?= $label; ?> on that page.</p>
            </div>
        </li>
    </ol>
</div>
