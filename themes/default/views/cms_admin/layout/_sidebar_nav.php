<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cms_section = isset($cms_section) ? $cms_section : 'dashboard';
$ss_method = ($cms_section === 'site_settings') ? strtolower((string) get_instance()->router->fetch_method()) : '';
$ss_open = ($cms_section === 'site_settings');
?>
<p class="cms-nav-label">Storefront</p>
<ul class="cms-nav cms-nav-main">
    <li><a href="<?= site_url('cms_admin/dashboard'); ?>" class="<?= $cms_section === 'dashboard' ? 'active' : '' ?>"><i class="fa fa-th-large"></i> Dashboard</a></li>
    <li><a href="<?= site_url('cms_admin/catalog'); ?>" class="<?= $cms_section === 'catalog' ? 'active' : '' ?>"><i class="fa fa-cubes"></i> Products</a></li>
    <li><a href="<?= site_url('cms_admin/pages'); ?>" class="<?= $cms_section === 'pages' ? 'active' : '' ?>"><i class="fa fa-file-text-o"></i> CMS Pages</a></li>
    <li><a href="<?= site_url('cms_admin/blogs'); ?>" class="<?= $cms_section === 'blogs' ? 'active' : '' ?>"><i class="fa fa-newspaper-o"></i> Blog Posts</a></li>
    <li><a href="<?= site_url('cms_admin/testimonials'); ?>" class="<?= $cms_section === 'testimonials' ? 'active' : '' ?>"><i class="fa fa-quote-left"></i> Testimonials</a></li>
    <li><a href="<?= site_url('cms_admin/layout_builder'); ?>" class="<?= in_array($cms_section, array('layout_builder', 'storefront_designs', 'header_designs', 'footer_designs'), true) ? 'active' : '' ?>"><i class="fa fa-object-group"></i> Header & Footer</a></li>
    <li><a href="<?= site_url('cms_admin/media'); ?>" class="<?= $cms_section === 'media' ? 'active' : '' ?>"><i class="fa fa-picture-o"></i> Media</a></li>
    <li><a href="<?= site_url('cms_admin/entity_tags'); ?>" class="<?= $cms_section === 'entity_tags' ? 'active' : '' ?>"><i class="fa fa-tags"></i> Entity Pages</a></li>
    <li><a href="<?= site_url('cms_admin/tags_master'); ?>" class="<?= $cms_section === 'tags_master' ? 'active' : '' ?>"><i class="fa fa-database"></i> Tag Master</a></li>
    <li class="cms-nav-group<?= $ss_open ? ' is-open' : '' ?>">
        <button type="button" class="cms-nav-group-toggle" aria-expanded="<?= $ss_open ? 'true' : 'false' ?>">
            <i class="fa fa-cog" aria-hidden="true"></i>
            <span class="cms-nav-group-text">Site Settings</span>
            <i class="fa fa-chevron-down cms-nav-group-chevron" aria-hidden="true"></i>
        </button>
        <ul class="cms-nav cms-nav-sub">
            <li><a href="<?= site_url('cms_admin/site_settings/robots'); ?>" class="cms-nav-sub-link<?= $ss_method === 'robots' ? ' active' : '' ?>"><i class="fa fa-file-code-o"></i> Robots</a></li>
            <li><a href="<?= site_url('cms_admin/site_settings/sitemap'); ?>" class="cms-nav-sub-link<?= $ss_method === 'sitemap' ? ' active' : '' ?>"><i class="fa fa-sitemap"></i> Sitemap.xml</a></li>
            <li><a href="<?= site_url('cms_admin/site_settings/llms'); ?>" class="cms-nav-sub-link<?= $ss_method === 'llms' ? ' active' : '' ?>"><i class="fa fa-file-text-o"></i> LLMS</a></li>
        </ul>
    </li>
    <li><a href="<?= site_url('cms_admin/prices'); ?>" class="<?= $cms_section === 'prices' ? 'active' : '' ?>"><i class="fa fa-inr"></i> Prices</a></li>
    <li><a href="<?= site_url('cms_admin/form_templates'); ?>" class="<?= $cms_section === 'form_templates' ? 'active' : '' ?>"><i class="fa fa-list-alt"></i> Form Templates</a></li>
    <li><a href="<?= site_url('cms_admin/leads'); ?>" class="<?= $cms_section === 'leads' ? 'active' : '' ?>"><i class="fa fa-users"></i> Leads</a></li>
    <li><a href="<?= site_url('cms_admin/newsletter_subscribers'); ?>" class="<?= $cms_section === 'newsletter_subscribers' ? 'active' : '' ?>" title="Newsletter Subscribers"><i class="fa fa-envelope-o"></i> Newsletter</a></li>
    <li><a href="<?= site_url('cms_admin/guide'); ?>" class="<?= $cms_section === 'guide' ? 'active' : '' ?>"><i class="fa fa-book"></i> Guide</a></li>
</ul>
