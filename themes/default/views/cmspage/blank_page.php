<?php defined('BASEPATH') OR exit('No direct script access allowed');
$show_header = !empty($show_header);
$show_footer = !empty($show_footer);
$header_html = isset($header_sections_html) ? $header_sections_html : '';
$footer_html = isset($footer_sections_html) ? $footer_sections_html : '';
$banner_html = isset($banner_sections_html) ? $banner_sections_html : '';
$logo_html = isset($logo_sections_html) ? $logo_sections_html : '';
$content_html = isset($page_content) ? $page_content : '';
$page_title = isset($page_title) ? (string) $page_title : 'CMS Page';
$meta_tags = isset($meta_tags) ? $meta_tags : '';
$layout_css = base_url('themes/default/assets/cms_storefront_layout.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <?= $meta_tags; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($layout_css, ENT_QUOTES, 'UTF-8'); ?>?v=2">
    <style>
        body.cms-blank-page { margin: 0; font-family: Inter, Segoe UI, sans-serif; color: #0f172a; background: #f8fafc; }
        .cms-blank-page__main { max-width: 1200px; margin: 0 auto; padding: 24px 16px 48px; }
        .cms-blank-page__title { margin: 0 0 16px; font-size: 28px; font-weight: 700; }
    </style>
</head>
<body class="cms-blank-page">
    <?php if ($show_header && $header_html !== '') { echo $header_html; } ?>
    <?php if ($banner_html !== '') { echo '<div class="cms-blank-page__banner">' . $banner_html . '</div>'; } ?>
    <?php if ($logo_html !== '') { echo '<div class="cms-blank-page__logo">' . $logo_html . '</div>'; } ?>
    <main class="cms-blank-page__main">
        <h1 class="cms-blank-page__title"><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="cms-blank-page__content"><?= $content_html; ?></div>
    </main>
    <?php if ($show_footer && $footer_html !== '') { echo $footer_html; } ?>
</body>
</html>
