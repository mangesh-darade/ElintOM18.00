<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cfg = isset($config) && is_array($config) ? $config : array();
$title = '';
foreach (array('title', 'heading') as $k) {
    if (!empty($cfg[$k]) && trim((string) $cfg[$k]) !== '') {
        $title = trim((string) $cfg[$k]);
        break;
    }
}
$content = isset($cfg['content']) ? (string) $cfg['content'] : '';
if ($title === '' && trim($content) === '') {
    return;
}
?>
<div class="cms-html-block">
    <?php if ($title !== '') { ?>
    <h2 class="cms-html-block__title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php } ?>
    <?php if (trim($content) !== '') { ?>
    <div class="cms-html-block__body"><?= $content; ?></div>
    <?php } ?>
</div>
