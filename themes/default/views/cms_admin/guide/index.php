<?php defined('BASEPATH') OR exit('No direct script access allowed');
$sections = isset($guide_sections) && is_array($guide_sections) ? $guide_sections : array();
$cms_assets = isset($cms_assets) ? $cms_assets : base_url('themes/default/assets/cms_admin/');
$guide_asset = function ($file) use ($cms_assets) {
    return cms_admin_asset_url('guide/' . ltrim((string) $file, '/'), $cms_assets);
};
$this->load->helper('cms_guide');
$total_steps = 0;
foreach ($sections as $sec) {
    $total_steps += cms_guide_count_steps($sec);
}
?>

<link rel="stylesheet" href="<?= cms_admin_asset_url('css/guide.css', $cms_assets); ?>">

<div class="cms-guide cms-guide--home">
    <header class="cms-guide-hero cms-guide-hero--home">
        <h1 class="cms-guide-title"><i class="fa fa-book"></i> CMS Admin User Guide</h1>
        <p class="cms-guide-lead">
            <?= count($sections); ?> modules · <?= (int) $total_steps; ?> steps with screenshots.
            Click a card below — each module opens numbered steps with highlighted buttons to follow on your screen.
            New here? Start with <strong>Login &amp; access</strong>, then <strong>Dashboard</strong>.
        </p>
    </header>

    <div class="cms-guide-home-toolbar">
        <h2 class="cms-guide-grid-heading">All modules</h2>
        <p class="cms-guide-home-toolbar__hint"><i class="fa fa-hand-pointer-o"></i> Click a card · use Previous / Next at the bottom to move through all modules in order</p>
    </div>

    <div class="cms-guide-module-grid cms-guide-module-grid--5">
        <?php foreach ($sections as $i => $sec) {
            $num = $i + 1;
            $card_title = !empty($sec['card_title']) ? $sec['card_title'] : $sec['title'];
            $summary = !empty($sec['summary']) ? $sec['summary'] : '';
            $thumb = !empty($sec['image']) ? $sec['image'] : '';
            $step_count = cms_guide_count_steps($sec);
            $guide_url = site_url('cms_admin/guide/module/' . $sec['id']);
            ?>
        <a href="<?= htmlspecialchars($guide_url, ENT_QUOTES, 'UTF-8'); ?>" class="cms-guide-module-card" title="<?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="cms-guide-module-card__badge"><?= $num; ?></span>
            <?php if ($thumb !== '') { ?>
            <span class="cms-guide-module-card__thumb">
                <img src="<?= $guide_asset($thumb); ?>" alt="" loading="lazy">
            </span>
            <?php } else { ?>
            <span class="cms-guide-module-card__icon"><i class="fa <?= htmlspecialchars($sec['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
            <?php } ?>
            <span class="cms-guide-module-card__body">
                <span class="cms-guide-module-card__title"><?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if ($summary !== '') { ?>
                <span class="cms-guide-module-card__summary"><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php } ?>
                <span class="cms-guide-module-card__meta">
                    <i class="fa fa-picture-o"></i> <?= (int) $step_count; ?> steps
                </span>
                <span class="cms-guide-module-card__cta">Open guide <i class="fa fa-arrow-right"></i></span>
            </span>
        </a>
        <?php } ?>
    </div>

    <div class="cms-guide-sidebar-map">
        <img src="<?= $guide_asset('sidebar-menu.png'); ?>" alt="CMS Admin sidebar menu map" loading="lazy">
        <p class="cms-guide-caption">Module cards match the left sidebar menu in CMS Admin</p>
    </div>

    <footer class="cms-guide-footer">
        <p><a href="<?= site_url('cms_admin/dashboard'); ?>"><i class="fa fa-home"></i> Return to Dashboard</a></p>
    </footer>
</div>
