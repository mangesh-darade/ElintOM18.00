<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cfg = isset($config) && is_array($config) ? $config : array();
$content = isset($cfg['content']) ? trim((string) $cfg['content']) : '';
$title = '';
foreach (array('title', 'heading') as $k) {
    if (!empty($cfg[$k]) && trim((string) $cfg[$k]) !== '') {
        $title = trim((string) $cfg[$k]);
        break;
    }
}
if ($content === '' && $title === '') {
    return;
}
?>
<div class="cms-banner-block">
    <?php if ($title !== '') { ?>
    <div class="cms-banner-block__title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php } ?>
    <?php if ($content !== '') { ?>
    <div class="cms-banner-block__body"><?= $content; ?></div>
    <?php } ?>
</div>
