<?php defined('BASEPATH') OR exit('No direct script access allowed');
$h = isset($header_config) && is_array($header_config) ? $header_config : array();
$f = isset($footer_config) && is_array($footer_config) ? $footer_config : array();
$tab = isset($active_tab) ? $active_tab : 'header';
$upload_base = isset($upload_base) ? $upload_base : '';
$logo_src = '';
if (!empty($h['logo_image']) && function_exists('cms_layout_builder_uploads_preview_url')) {
    $logo_src = cms_layout_builder_uploads_preview_url($h['logo_image'], $upload_base);
}
$favicon_src = '';
if (!empty($h['favicon_image']) && function_exists('cms_layout_builder_uploads_preview_url')) {
    $favicon_src = cms_layout_builder_uploads_preview_url($h['favicon_image'], $upload_base);
}
$f_logo_src = '';
if (!empty($f['logo_image']) && function_exists('cms_layout_builder_uploads_preview_url')) {
    $f_logo_src = cms_layout_builder_uploads_preview_url($f['logo_image'], $upload_base);
}
$preview_url = isset($preview_url) ? $preview_url : '';
$profile_slug = isset($profile_slug) ? $profile_slug : 'site';
$header_profile_slug = isset($header_profile_slug) ? (string) $header_profile_slug : $profile_slug;
$footer_profile_slug = isset($footer_profile_slug) ? (string) $footer_profile_slug : $profile_slug;
$default_profile_slug = isset($default_profile_slug) ? (string) $default_profile_slug : 'site';
$bootstrap_profile_slug = isset($bootstrap_profile_slug) ? (string) $bootstrap_profile_slug : 'site';
$layout_profiles = isset($layout_profiles) && is_array($layout_profiles) ? $layout_profiles : array();
$profile_label = $profile_slug;
foreach ($layout_profiles as $lp) {
    if (isset($lp['slug']) && $lp['slug'] === $profile_slug) {
        $profile_label = isset($lp['label']) ? $lp['label'] : $profile_slug;
        break;
    }
}
$is_default_profile = ($profile_slug === $default_profile_slug);
$is_bootstrap_profile = ($profile_slug === $bootstrap_profile_slug);
$tab_base = isset($page_url_base) ? $page_url_base : site_url('cms_admin/layout_builder');
$profile_q = 'header_profile=' . rawurlencode($header_profile_slug) . '&footer_profile=' . rawurlencode($footer_profile_slug);
$header_tab_href = $tab_base . '?' . $profile_q;
$footer_tab_href = $tab_base . '?tab=footer&amp;' . $profile_q;
$layout_tab_label = ($tab === 'footer') ? 'Footer layout' : 'Header layout';
$footer_link_pages_json = isset($footer_link_pages) && is_array($footer_link_pages)
    ? json_encode($footer_link_pages)
    : '[]';
