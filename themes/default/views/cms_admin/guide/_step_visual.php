<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @var string|array<string,mixed> $step
 * @var int                        $step_num
 * @var string                     $fallback_image
 * @var callable                   $guide_asset
 * @var callable                   $guide_image_exists
 */
$this->load->helper('cms_guide');
$norm = cms_guide_normalize_step($step, isset($fallback_image) ? $fallback_image : '');
$img = $norm['image'];
$has_img = $img !== '' && $guide_image_exists($img);
$hl = !empty($norm['highlight']) && is_array($norm['highlight']) ? $norm['highlight'] : null;
$step_type = !empty($norm['type']) ? (string) $norm['type'] : 'normal';
$card_class = 'cms-guide-step-card';
if ($step_type === 'difficulty') {
    $card_class .= ' cms-guide-step-card--difficulty';
} elseif ($step_type === 'journey') {
    $card_class .= ' cms-guide-step-card--journey';
}
?>
<div class="<?= $card_class; ?>">
    <div class="cms-guide-step-card__head">
        <span class="cms-guide-step-num" aria-hidden="true"><?= (int) $step_num; ?></span>
        <div class="cms-guide-step-text"><?= $norm['text']; ?></div>
    </div>
    <?php if ($has_img) { ?>
    <figure class="cms-guide-step-visual">
        <div class="cms-guide-step-visual__frame">
            <img src="<?= $guide_asset($img); ?>" alt="Step <?= (int) $step_num; ?> screenshot" loading="lazy">
            <?php if ($hl) {
                $hl = cms_guide_prepare_highlight($hl);
                $top = isset($hl['top']) ? (float) $hl['top'] : 0;
                $left = isset($hl['left']) ? (float) $hl['left'] : 0;
                $width = isset($hl['width']) ? (float) $hl['width'] : 10;
                $height = isset($hl['height']) ? (float) $hl['height'] : 5;
                $label = isset($hl['label']) ? (string) $hl['label'] : '';
                $spot_classes = 'cms-guide-spotlight';
                if ($top < 10) {
                    $spot_classes .= ' cms-guide-spotlight--label-below';
                }
                if (!empty($hl['compact'])) {
                    $spot_classes .= ' cms-guide-spotlight--compact';
                }
                ?>
            <div class="<?= $spot_classes; ?>" style="top:<?= $top; ?>%;left:<?= $left; ?>%;width:<?= $width; ?>%;height:<?= $height; ?>%;">
                <?php if ($label !== '') { ?>
                <span class="cms-guide-spotlight__label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <figcaption class="cms-guide-step-visual__caption">
            <i class="fa fa-hand-pointer-o"></i>
            <?= $hl && !empty($hl['label'])
                ? 'Look for the highlighted area: <strong>' . htmlspecialchars($hl['label'], ENT_QUOTES, 'UTF-8') . '</strong>'
                : 'This is what you should see on your screen for this step.'; ?>
        </figcaption>
    </figure>
    <?php } ?>
</div>
