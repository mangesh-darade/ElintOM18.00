<?php defined('BASEPATH') OR exit('No direct script access allowed');
require __DIR__ . DIRECTORY_SEPARATOR . '_init.php';
$tab_base = isset($page_url_base) ? $page_url_base : site_url('cms_admin/layout_builder');
$profile_q = 'header_profile=' . rawurlencode($header_profile_slug) . '&footer_profile=' . rawurlencode($footer_profile_slug);
$header_tab_href = $tab_base . '?' . $profile_q;
$footer_tab_href = $tab_base . '?tab=footer&amp;' . $profile_q;
$workspace_q = 'tab=' . rawurlencode($tab) . '&amp;' . $profile_q;
$workspace_url = site_url('cms_admin/layout_builder/workspace') . '?' . $workspace_q;
?>

<div class="cms-layout-builder" id="cmsLayoutBuilder" data-tab="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>"
     data-preview-url="<?= htmlspecialchars($preview_url, ENT_QUOTES, 'UTF-8'); ?>"
     data-page-url="<?= htmlspecialchars($tab_base, ENT_QUOTES, 'UTF-8'); ?>"
     data-profile="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-header-profile="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-footer-profile="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-workspace-url="<?= htmlspecialchars($workspace_url, ENT_QUOTES, 'UTF-8'); ?>"
     data-cms-footer-pages="[]">

    <div class="cms-layout-builder__top">
        <div>
            <h2 class="cms-page-title">Header &amp; Footer</h2>
            <p class="cms-layout-builder__sub">Header and footer layouts are edited separately (each tab remembers its own profile). Assign layouts on <strong>Edit CMS Page → Page header/footer design</strong>.<?= $tab === 'footer' ? ' Drag footer elements to reorder; CMS pages with <strong>Show In Footer</strong> appear in the list automatically.' : ' Header menu links come from <a href="' . site_url('cms_admin/pages') . '">CMS Pages</a> (<strong>Show In Header</strong>).' ?></p>
        </div>
        <div class="cms-layout-builder__tabs">
            <a class="cms-layout-builder__tab<?= $tab === 'header' ? ' is-active' : ''; ?>" href="<?= htmlspecialchars($header_tab_href, ENT_QUOTES, 'UTF-8'); ?>">Header</a>
            <a class="cms-layout-builder__tab<?= $tab === 'footer' ? ' is-active' : ''; ?>" href="<?= htmlspecialchars($footer_tab_href, ENT_QUOTES, 'UTF-8'); ?>">Footer</a>
        </div>
    </div>

    <div id="cmsLbAsyncHost" class="cms-layout-builder__async-host" aria-busy="true" aria-live="polite">
        <p class="cms-layout-builder__loading">Loading layout editor…</p>
    </div>
</div>
