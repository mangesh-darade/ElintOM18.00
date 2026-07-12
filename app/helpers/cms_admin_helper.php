<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('cms_admin_assets_base')) {
  /**
   * Base URL for CMS Admin static assets (themes/default/assets/cms_admin/).
   *
   * @return string
   */
  function cms_admin_assets_base()
  {
    return base_url('themes/default/assets/cms_admin/');
  }
}

if (!function_exists('cms_admin_asset_file_version')) {
  /**
   * File modification time for cache-busting (updates automatically when the file changes).
   *
   * @param string $relative_path Path under themes/default/assets/cms_admin/
   * @return string
   */
  function cms_admin_asset_file_version($relative_path)
  {
    $relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
    if ($relative_path === '') {
      return '';
    }
    $fs = FCPATH . 'themes' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'assets'
      . DIRECTORY_SEPARATOR . 'cms_admin' . DIRECTORY_SEPARATOR
      . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
    if (is_file($fs)) {
      return (string) filemtime($fs);
    }
    return '';
  }
}

if (!function_exists('cms_admin_asset_url')) {
  /**
   * Public URL for a CMS admin CSS/JS asset with automatic filemtime cache-busting.
   *
   * @param string      $relative_path e.g. css/layout.css or js/app.js
   * @param string|null $assets_base   Optional base URL override
   * @return string
   */
  function cms_admin_asset_url($relative_path, $assets_base = null)
  {
    $relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
    if ($relative_path === '') {
      return '';
    }
    $base = ($assets_base !== null && trim((string) $assets_base) !== '')
      ? rtrim(str_replace('\\', '/', (string) $assets_base), '/') . '/'
      : rtrim(cms_admin_assets_base(), '/') . '/';
    $url = $base . $relative_path;
    $ver = cms_admin_asset_file_version($relative_path);
    if ($ver !== '') {
      $url .= '?v=' . rawurlencode($ver);
    }
    return $url;
  }
}

if (!function_exists('contact_form_country_list')) {
  require_once APPPATH . 'helpers/contact_form_country_helper.php';
}
