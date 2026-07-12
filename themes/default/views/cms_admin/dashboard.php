<?php defined('BASEPATH') OR exit('No direct script access allowed');
$dashboard_stats = isset($dashboard_stats) ? $dashboard_stats : array();
$stat_cards = isset($stat_cards) ? $stat_cards : array();
$modules_count = isset($dashboard_stats['modules_count']) ? (int) $dashboard_stats['modules_count'] : 5;
$products_online = isset($dashboard_stats['products_in_eshop']) ? (int) $dashboard_stats['products_in_eshop'] : 0;

$quick_modules = array(
    array(
        'url' => 'cms_admin/catalog',
        'icon' => 'fa-cubes',
        'title' => 'Manage products',
        'desc' => 'Choose which categories and products appear on your webshop and control e-shop visibility.',
    ),
    array(
        'url' => 'cms_admin/pages',
        'icon' => 'fa-file-text-o',
        'title' => 'CMS pages',
        'desc' => 'Create privacy policies, about pages, help content, and promotional landing pages.',
    ),
    array(
        'url' => 'cms_admin/layout_builder',
        'icon' => 'fa-object-group',
        'title' => 'Header & footer',
        'desc' => 'Logos, navigation links, footer text, social profiles, and contact details.',
    ),
    array(
        'url' => 'cms_admin/media',
        'icon' => 'fa-picture-o',
        'title' => 'Media library',
        'desc' => 'Upload and reuse logos, banners, hero images, and content assets across the webshop.',
    ),
    array(
        'url' => 'cms_admin/entity_tags',
        'icon' => 'fa-tags',
        'title' => 'Entity tags',
        'desc' => 'Map tags to products and categories for better filters and search on your store.',
    ),
    array(
        'url' => 'cms_admin/form_templates',
        'icon' => 'fa-wpforms',
        'title' => 'Form templates',
        'desc' => 'Build reusable contact forms with multiple fields, custom CSS, and design presets.',
    ),
    array(
        'url' => 'cms_admin/leads',
        'icon' => 'fa-address-book',
        'title' => 'Leads',
        'desc' => 'Full leads list with status, assign, convert, and webshop form submissions.',
    ),
    array(
        'url' => 'cms_admin/newsletter_subscribers',
        'icon' => 'fa-envelope-o',
        'title' => 'Newsletter subscribers',
        'desc' => 'See who signed up via the footer newsletter email field.',
    ),
    array(
        'url' => 'cms_admin/guide',
        'icon' => 'fa-book',
        'title' => 'User guide',
        'desc' => 'Step-by-step instructions with screenshots for every CMS module.',
    ),
);
?>

<div class="cms-dashboard">
    <section class="cms-welcome" aria-labelledby="cms-welcome-title">
        <div class="cms-welcome-inner">
            <div>
                <h2 id="cms-welcome-title">Welcome to your Admin Panel</h2>
                <p>Select a module below to manage your webshop. New here? Open the <a href="<?= site_url('cms_admin/guide'); ?>">User guide</a> for step-by-step help.</p>
            </div>
            <span class="cms-welcome-badge">
                <span class="cms-status-dot" aria-hidden="true"></span>
                <?= (int) $modules_count ?> modules · <?= number_format($products_online) ?> products online
            </span>
        </div>
    </section>

    <?php if (!empty($stat_cards)) { ?>
    <div class="cms-stats" role="list">
        <?php foreach ($stat_cards as $card) {
            $icon = isset($card['icon']) ? $card['icon'] : 'fa-bar-chart';
            ?>
        <a href="<?= site_url($card['url']); ?>" class="cms-stat cms-stat-link" role="listitem">
            <span class="cms-stat-icon"><i class="fa <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
            <div class="cms-stat-body">
                <div class="cms-stat-value"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="cms-stat-label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($card['hint'])) { ?>
                <div class="cms-stat-hint"><?= htmlspecialchars($card['hint'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php } ?>
            </div>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <h2 class="cms-section-heading">Quick access</h2>
    <div class="cms-module-grid">
        <?php foreach ($quick_modules as $mod) { ?>
        <a href="<?= site_url($mod['url']); ?>" class="cms-module-card">
            <div class="cms-module-icon"><i class="fa <?= htmlspecialchars($mod['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></div>
            <h3><?= htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($mod['desc'], ENT_QUOTES, 'UTF-8') ?></p>
            <span class="cms-module-cta">Open module <i class="fa fa-arrow-right"></i></span>
        </a>
        <?php } ?>
    </div>
</div>
