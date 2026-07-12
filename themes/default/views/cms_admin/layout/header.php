<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cms_section = isset($cms_section) ? $cms_section : 'dashboard';
$page_title = isset($page_title) ? $page_title : 'CMS Admin';
$cms_assets = isset($cms_assets) ? $cms_assets : base_url('themes/default/assets/cms_admin/');
$cms_list_datatable = !empty($cms_list_datatable);
$cms_erp_embed = !empty($cms_erp_embed);
$cms_layout_builder = !empty($cms_layout_builder);
$cms_breadcrumbs = isset($cms_breadcrumbs) && is_array($cms_breadcrumbs) ? $cms_breadcrumbs : array();
$username = (string) $this->session->userdata('username');
$initials = '';
if ($username !== '') {
    $parts = preg_split('/\s+/', trim($username));
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
if ($initials === '') {
    $initials = 'A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> · CMS Admin</title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>
    <style>.cms-admin-body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}</style>
    <?php if ($cms_layout_builder) { ?>
    <style>.cms-layout-builder__sub{color:#64748b;font-size:14px;margin:6px 0 0;max-width:560px;line-height:1.5}.cms-page-title{margin:0;font-size:1.35rem;font-weight:700;color:#0f172a}.cms-layout-builder__top{display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:flex-start;margin-bottom:16px}.cms-layout-builder__tabs{display:flex;gap:8px}.cms-layout-builder__tab{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;border:1px solid #e2e8f0;color:#475569;text-decoration:none;font-size:13px;font-weight:600}.cms-layout-builder__tab.is-active{background:#0f172a;color:#fff;border-color:#0f172a}</style>
    <?php } ?>
    <?php if (!$cms_layout_builder) { ?>
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet"/>
    <?php } ?>
    <link href="<?= $assets ?>styles/helpers/font-awesome.min.css" rel="stylesheet"/>
    <link href="<?= cms_admin_asset_url('css/layout.css', $cms_assets) ?>" rel="stylesheet"/>
    <?php if ($cms_layout_builder) { ?>
    <link href="<?= cms_admin_asset_url('css/modules.css', $cms_assets) ?>" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="<?= cms_admin_asset_url('css/modules.css', $cms_assets) ?>" rel="stylesheet"></noscript>
    <?php } else { ?>
    <link href="<?= cms_admin_asset_url('css/modules.css', $cms_assets) ?>" rel="stylesheet"/>
    <?php } ?>
    <?php if ($cms_layout_builder) { ?>
    <link href="<?= cms_admin_asset_url('css/components.css', $cms_assets) ?>" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="<?= cms_admin_asset_url('css/components.css', $cms_assets) ?>" rel="stylesheet"></noscript>
    <?php } else { ?>
    <link href="<?= cms_admin_asset_url('css/components.css', $cms_assets) ?>" rel="stylesheet"/>
    <?php } ?>
    <?php if (!empty($cms_list_datatable) || !empty($simple_datatable)) { ?>
    <link href="<?= $assets ?>bs-assets/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
    <?php } ?>
    <?php if (!empty($cms_enhancements)) { ?>
    <link href="<?= cms_admin_asset_url('css/cms-enhancements.css', $cms_assets) ?>" rel="stylesheet"/>
    <?php } ?>
    <?php if ($cms_erp_embed) { ?>
    <link href="<?= $assets ?>bs-assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?= cms_admin_asset_url('css/erp-embed.css', $cms_assets) ?>" rel="stylesheet"/>
    <?php } ?>
    <?php if (!$cms_layout_builder) { ?>
    <script src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <?php } ?>
</head>
<body class="cms-admin-body" data-cms-section="<?= htmlspecialchars($cms_section, ENT_QUOTES, 'UTF-8') ?>">
<div class="cms-shell">
    <aside class="cms-sidebar" id="cmsSidebar" aria-label="CMS navigation">
        <div class="cms-sidebar-brand">
            <a href="<?= site_url('cms_admin/dashboard'); ?>">
                <span class="cms-brand-mark"><i class="fa fa-shopping-cart"></i></span>
                <span class="cms-brand-text">
                    <span class="title">CMS Admin</span>
                    <span class="sub"><?= htmlspecialchars($Settings->site_name, ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            </a>
        </div>
        <div class="cms-sidebar-scroll">
            <?php $this->load->view($this->theme . 'cms_admin/layout/_sidebar_nav', get_defined_vars()); ?>
        </div>
        <div class="cms-sidebar-bottom">
            <p class="cms-nav-label cms-nav-label-system">System</p>
            <ul class="cms-nav cms-nav-system">
                <li><a href="<?= site_url(); ?>" class="cms-nav-erp"><i class="fa fa-arrow-left"></i> ElintOM</a></li>
                <li><a href="<?= site_url('logout'); ?>" class="cms-nav-erp"><i class="fa fa-sign-out"></i> <?= lang('logout'); ?></a></li>
            </ul>
            <div class="cms-sidebar-footer">ElintOM</div>
        </div>
    </aside>
    <div class="cms-sidebar-backdrop" id="cmsSidebarBackdrop" aria-hidden="true"></div>
    <main class="cms-main">
        <header class="cms-topbar">
            <div class="cms-topbar-left">
                <button type="button" class="cms-menu-toggle" id="cmsMenuToggle" aria-label="Open menu"><i class="fa fa-bars"></i></button>
                <div class="cms-topbar-title-block">
                    <?php if (!empty($cms_breadcrumbs)) { ?>
                    <ol class="cms-breadcrumb" aria-label="Breadcrumb">
                        <?php foreach ($cms_breadcrumbs as $crumb) {
                            $label = isset($crumb['label']) ? $crumb['label'] : '';
                            $url = isset($crumb['url']) ? $crumb['url'] : null;
                            ?>
                        <li>
                            <?php if ($url) { ?>
                            <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
                            <?php } else { ?>
                            <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php } ?>
                        </li>
                        <?php } ?>
                    </ol>
                    <?php } ?>
                </div>
            </div>
            <div class="cms-topbar-right">
                <div class="cms-topbar-search" role="search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="search" id="cmsNavSearch" placeholder="Search menu…" autocomplete="off" aria-label="Search navigation">
                </div>
                <a href="<?= site_url('cms_admin/dashboard'); ?>" class="cms-icon-btn" title="Notifications" aria-label="Notifications">
                    <i class="fa fa-bell-o"></i>
                    <span class="cms-notif-dot" aria-hidden="true"></span>
                </a>
                <a href="<?= site_url(); ?>" class="cms-btn-ghost hidden-xs"><i class="fa fa-home"></i> ElintOM</a>
                <div class="cms-user-chip">
                    <span class="cms-user-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                    <span><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <a href="<?= site_url('logout'); ?>" class="cms-icon-btn" title="<?= lang('logout'); ?>" aria-label="<?= lang('logout'); ?>"><i class="fa fa-sign-out"></i></a>
            </div>
        </header>
        <div class="cms-content">
            <div class="cms-flash">
                <?php if (!empty($message)) { ?>
                    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= $message ?></div>
                <?php } ?>
                <?php if (!empty($error)) { ?>
                    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= $error ?></div>
                <?php } ?>
                <?php if (!empty($warning)) { ?>
                    <div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= $warning ?></div>
                <?php } ?>
            </div>
