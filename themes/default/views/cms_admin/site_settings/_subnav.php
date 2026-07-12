<?php defined('BASEPATH') OR exit('No direct script access allowed');
$tab = isset($site_settings_tab) ? (string) $site_settings_tab : 'robots';
$tabs = array(
    'robots'  => array('label' => 'Robots', 'icon' => 'fa-android', 'url' => site_url('cms_admin/site_settings/robots')),
    'sitemap' => array('label' => 'Sitemap.xml', 'icon' => 'fa-sitemap', 'url' => site_url('cms_admin/site_settings/sitemap')),
    'llms'    => array('label' => 'LLMS', 'icon' => 'fa-file-text-o', 'url' => site_url('cms_admin/site_settings/llms')),
);
?>
<nav class="cms-site-settings-subnav" aria-label="Site settings sections">
    <?php foreach ($tabs as $key => $item) { ?>
    <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
       class="cms-site-settings-subnav__link<?= $tab === $key ? ' is-active' : ''; ?>">
        <i class="fa <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
    </a>
    <?php } ?>
</nav>
