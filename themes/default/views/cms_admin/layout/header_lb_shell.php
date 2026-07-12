<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cms_section = isset($cms_section) ? $cms_section : 'layout_builder';
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
<body class="cms-admin-body" data-cms-section="<?= htmlspecialchars($cms_section, ENT_QUOTES, 'UTF-8') ?>">
<div class="cms-shell">
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
