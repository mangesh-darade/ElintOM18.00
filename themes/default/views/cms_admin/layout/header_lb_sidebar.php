<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cms_section = isset($cms_section) ? $cms_section : 'layout_builder';
$site_name = (isset($Settings) && is_object($Settings) && isset($Settings->site_name))
    ? (string) $Settings->site_name
    : 'CMS Admin';
?>
    <aside class="cms-sidebar" id="cmsSidebar" aria-label="CMS navigation">
        <div class="cms-sidebar-brand">
            <a href="<?= site_url('cms_admin/dashboard'); ?>">
                <span class="cms-brand-mark"><i class="fa fa-shopping-cart"></i></span>
                <span class="cms-brand-text">
                    <span class="title">CMS Admin</span>
                    <span class="sub"><?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></span>
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
