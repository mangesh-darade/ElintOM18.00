<?php defined('BASEPATH') OR exit('No direct script access allowed');
$sec = isset($guide_section) && is_array($guide_section) ? $guide_section : array();
$cms_assets = isset($cms_assets) ? $cms_assets : base_url('themes/default/assets/cms_admin/');
$guide_asset = function ($file) use ($cms_assets) {
    return cms_admin_asset_url('guide/' . ltrim((string) $file, '/'), $cms_assets);
};
$guide_image_exists = function ($file) {
    $path = FCPATH . 'themes/default/assets/cms_admin/guide/' . ltrim((string) $file, '/');
    return is_file($path);
};
$card_title = !empty($sec['card_title']) ? $sec['card_title'] : (isset($sec['title']) ? $sec['title'] : 'Module guide');
$module_url = !empty($sec['url']) ? $sec['url'] : '';
$guide_nav = isset($guide_nav) && is_array($guide_nav) ? $guide_nav : array('prev' => null, 'next' => null, 'index' => 0, 'total' => 0);
$nav_prev = !empty($guide_nav['prev']) ? $guide_nav['prev'] : null;
$nav_next = !empty($guide_nav['next']) ? $guide_nav['next'] : null;
$nav_index = isset($guide_nav['index']) ? (int) $guide_nav['index'] : 0;
$nav_total = isset($guide_nav['total']) ? (int) $guide_nav['total'] : 0;
$this->load->helper('cms_guide');
$module_step_count = cms_guide_count_steps($sec);
?>

<link rel="stylesheet" href="<?= cms_admin_asset_url('css/guide.css', $cms_assets); ?>">

<div class="cms-guide cms-guide--module">
    <nav class="cms-guide-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= site_url('cms_admin/dashboard'); ?>">Dashboard</a>
        <span class="cms-guide-breadcrumb__sep">/</span>
        <a href="<?= site_url('cms_admin/guide'); ?>">User Guide</a>
        <span class="cms-guide-breadcrumb__sep">/</span>
        <span><?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8'); ?></span>
    </nav>

    <header class="cms-guide-section-head cms-guide-section-head--module">
        <div>
            <a href="<?= site_url('cms_admin/guide'); ?>" class="cms-guide-back"><i class="fa fa-arrow-left"></i> All modules</a>
            <h1 class="cms-guide-title cms-guide-title--module">
                <i class="fa <?= htmlspecialchars(isset($sec['icon']) ? $sec['icon'] : 'fa-book', ENT_QUOTES, 'UTF-8'); ?>"></i>
                <?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8'); ?>
            </h1>
            <?php if ($nav_index > 0 && $nav_total > 0) { ?>
            <p class="cms-guide-module-position">Module <?= $nav_index; ?> of <?= $nav_total; ?> · <?= (int) $module_step_count; ?> visual steps</p>
            <?php } elseif ($module_step_count > 0) { ?>
            <p class="cms-guide-module-position"><?= (int) $module_step_count; ?> visual steps with screenshots below</p>
            <?php } ?>
        </div>
        <?php if ($module_url !== '') { ?>
        <a class="btn btn-primary cms-guide-open-live" href="<?= htmlspecialchars($module_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
            <i class="fa fa-external-link"></i> Open live module
        </a>
        <?php } ?>
    </header>

    <article class="cms-guide-section cms-guide-section--single">
        <?php $this->load->view($this->theme . 'cms_admin/guide/_module_body', array(
            'sec'                => $sec,
            'guide_asset'        => $guide_asset,
            'guide_image_exists' => $guide_image_exists,
        )); ?>
    </article>

    <?php if ($nav_prev !== null || $nav_next !== null) { ?>
    <nav class="cms-guide-module-nav cms-guide-module-nav--page" aria-label="Module navigation">
        <?php if ($nav_prev !== null) {
            $prev_title = !empty($nav_prev['card_title']) ? $nav_prev['card_title'] : $nav_prev['title'];
            ?>
        <a href="<?= site_url('cms_admin/guide/module/' . $nav_prev['id']); ?>" class="cms-guide-module-nav__link cms-guide-module-nav__link--prev">
            <span class="cms-guide-module-nav__dir"><i class="fa fa-arrow-left"></i> Previous module</span>
            <span class="cms-guide-module-nav__title"><?= htmlspecialchars($prev_title, ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <?php } else { ?>
        <span class="cms-guide-module-nav__spacer"></span>
        <?php } ?>
        <?php if ($nav_next !== null) {
            $next_title = !empty($nav_next['card_title']) ? $nav_next['card_title'] : $nav_next['title'];
            ?>
        <a href="<?= site_url('cms_admin/guide/module/' . $nav_next['id']); ?>" class="cms-guide-module-nav__link cms-guide-module-nav__link--next">
            <span class="cms-guide-module-nav__dir">Next module <i class="fa fa-arrow-right"></i></span>
            <span class="cms-guide-module-nav__title"><?= htmlspecialchars($next_title, ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>

    <footer class="cms-guide-footer cms-guide-footer--module">
        <a href="<?= site_url('cms_admin/guide'); ?>" class="btn btn-default"><i class="fa fa-th-large"></i> All modules</a>
        <?php if ($module_url !== '') { ?>
        <a href="<?= htmlspecialchars($module_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open live module</a>
        <?php } ?>
    </footer>
</div>
