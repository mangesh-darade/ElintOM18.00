<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cfg = isset($config) && is_array($config) ? $config : array();
$mode = isset($cfg['footer_mode']) ? strtolower(trim((string) $cfg['footer_mode'])) : 'custom';
$profile = 'default';
if (isset($cfg['storefront_profile']) && function_exists('cms_storefront_normalize_profile_slug')) {
    $profile = cms_storefront_normalize_profile_slug($cfg['storefront_profile']);
    if ($profile === '') {
        $profile = 'default';
    }
} elseif (isset($cfg['storefront_profile'])) {
    $profile = trim((string) $cfg['storefront_profile']);
}

$title = '';
foreach (array('title', 'heading') as $k) {
    if (!empty($cfg[$k]) && trim((string) $cfg[$k]) !== '') {
        $title = trim((string) $cfg[$k]);
        break;
    }
}

$uploads_base = '';
$CI =& get_instance();
if (isset($CI->Customer_assets) && $CI->Customer_assets !== '') {
    $uploads_base = base_url('assets/mdata/' . $CI->Customer_assets . '/uploads/');
}

if ($mode === 'storefront_profile' && function_exists('cms_render_storefront_footer_profile_html')) {
    $strip = cms_render_storefront_footer_profile_html($profile, $uploads_base);
    if ($strip !== '') {
        echo '<div class="cms-page-footer-block cms-page-footer-block--profile">';
        if ($title !== '') {
            echo '<div class="cms-page-footer-block__title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        echo $strip;
        echo '</div>';
        return;
    }
}

$content = isset($cfg['content']) ? (string) $cfg['content'] : '';
if ($title === '' && trim($content) === '') {
    return;
}
?>
<div class="cms-page-footer-block cms-page-footer-block--custom">
    <?php if ($title !== '') { ?>
    <div class="cms-page-footer-block__title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php } ?>
    <?php if (trim($content) !== '') { ?>
    <div class="cms-page-footer-block__body"><?= $content; ?></div>
    <?php } ?>
</div>
