<?php defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = isset($page_title) ? $page_title : 'CMS Admin';
$assets = isset($assets) ? $assets : base_url('themes/default/assets/');
$cms_assets = isset($cms_assets) ? $cms_assets : base_url('themes/default/assets/cms_admin/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> · CMS Admin</title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <style>
    body.cms-admin-body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;font-size:14px;line-height:1.55;color:#111827;background:#f8fafc;-webkit-font-smoothing:antialiased}
    body.cms-admin-body #loading{display:none!important}
    .cms-shell{min-height:100vh}
    .cms-main{margin-left:260px;display:flex;flex-direction:column;min-height:100vh;background:#f8fafc}
    .cms-topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 28px;min-height:60px;display:flex;align-items:center;justify-content:space-between;gap:16px}
    .cms-topbar-left,.cms-topbar-right{display:flex;align-items:center;gap:14px;min-width:0}
    .cms-content{flex:1;padding:24px 28px 32px}
    .cms-sidebar{position:fixed;top:0;left:0;bottom:0;width:260px;z-index:1000}
    .cms-layout-builder__sub{color:#64748b;font-size:14px;margin:6px 0 0;max-width:560px;line-height:1.5}
    .cms-page-title{margin:0;font-size:1.35rem;font-weight:700;color:#0f172a}
    .cms-layout-builder__top{display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
    .cms-layout-builder__tabs{display:flex;gap:8px}
    .cms-layout-builder__tab{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;border:1px solid #e2e8f0;color:#475569;text-decoration:none;font-size:13px;font-weight:600}
    .cms-layout-builder__tab.is-active{background:#0f172a;color:#fff;border-color:#0f172a}
    .cms-layout-builder__loading{color:#64748b;font-size:14px;padding:24px 0}
    </style>
    <link href="<?= $assets ?>styles/helpers/font-awesome.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="<?= $assets ?>styles/helpers/font-awesome.min.css" rel="stylesheet"></noscript>
    <link href="<?= cms_admin_asset_url('css/layout.css', $cms_assets) ?>" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="<?= cms_admin_asset_url('css/layout.css', $cms_assets) ?>" rel="stylesheet"></noscript>
    <link href="<?= cms_admin_asset_url('css/modules.css', $cms_assets) ?>" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="<?= cms_admin_asset_url('css/modules.css', $cms_assets) ?>" rel="stylesheet"></noscript>
    <link href="<?= cms_admin_asset_url('css/components.css', $cms_assets) ?>" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="<?= cms_admin_asset_url('css/components.css', $cms_assets) ?>" rel="stylesheet"></noscript>
</head>
