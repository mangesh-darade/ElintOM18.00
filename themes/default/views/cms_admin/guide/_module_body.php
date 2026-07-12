<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Renders one guide module (visual steps with screenshots + highlights).
 *
 * @var array    $sec
 * @var callable $guide_asset
 * @var callable $guide_image_exists
 */
$sec = isset($sec) ? $sec : array();
if (empty($sec)) {
    return;
}

$this->load->helper('cms_guide');

$img = isset($sec['image']) ? $sec['image'] : '';
$hasImg = $img !== '' && $guide_image_exists($img);
$hasVisualSteps = cms_guide_section_has_step_images($sec);
$fallback_image = $img;
$global_step = isset($step_offset) ? (int) $step_offset : 0;
?>

<?php if (!empty($sec['intro'])) { ?>
<div class="cms-guide-intro alert alert-success">
    <strong><i class="fa fa-user"></i> For new users</strong>
    <p><?= $sec['intro']; ?></p>
</div>
<?php } ?>

<?php if (!empty($sec['purpose'])) { ?>
<p class="cms-guide-purpose"><strong>What this module does:</strong> <?= $sec['purpose']; ?></p>
<?php } ?>

<?php if ($hasImg && !$hasVisualSteps) { ?>
<figure class="cms-guide-figure cms-guide-figure--hero">
    <img src="<?= $guide_asset($img); ?>" alt="<?= htmlspecialchars(isset($sec['card_title']) ? $sec['card_title'] : $sec['title'], ENT_QUOTES, 'UTF-8'); ?> screenshot" loading="eager">
    <figcaption>This is what the screen looks like — follow the steps below in the same order.</figcaption>
</figure>
<?php } elseif ($img !== '' && !$hasVisualSteps) { ?>
<div class="cms-guide-placeholder">
    <i class="fa fa-camera"></i>
    <p>Screenshot coming soon for this module.</p>
</div>
<?php } ?>

<?php if (!empty($sec['workflows']) && is_array($sec['workflows'])) { ?>
<h3 class="cms-guide-steps-title"><i class="fa fa-list-ol"></i> Follow these steps</h3>
<p class="cms-guide-visual-hint">Each step shows a screenshot with a <span class="cms-guide-visual-hint__mark">highlighted</span> area.
<span class="cms-guide-legend cms-guide-legend--journey">Green cards</span> = first-time path.
<span class="cms-guide-legend cms-guide-legend--difficulty">Orange cards</span> = common difficulty + fix.</p>
<?php
foreach ($sec['workflows'] as $wf) {
    ?>
<div class="cms-guide-workflow cms-guide-workflow--visual">
    <h4 class="cms-guide-workflow-title"><?= isset($wf['title']) ? $wf['title'] : 'Steps'; ?></h4>
    <?php if (!empty($wf['steps']) && is_array($wf['steps'])) { ?>
    <div class="cms-guide-step-list">
        <?php foreach ($wf['steps'] as $step) {
            $global_step++;
            $this->load->view($this->theme . 'cms_admin/guide/_step_visual', array(
                'step'               => $step,
                'step_num'           => $global_step,
                'fallback_image'     => $fallback_image,
                'guide_asset'        => $guide_asset,
                'guide_image_exists' => $guide_image_exists,
            ));
        } ?>
    </div>
    <?php } ?>
</div>
<?php } ?>
<?php } elseif (!empty($sec['steps']) && is_array($sec['steps'])) { ?>
<h3 class="cms-guide-steps-title"><i class="fa fa-list-ol"></i> Follow these steps</h3>
<?php if ($hasVisualSteps) { ?>
<p class="cms-guide-visual-hint">Each step shows a screenshot with a <span class="cms-guide-visual-hint__mark">highlighted</span> area.
<span class="cms-guide-legend cms-guide-legend--journey">Green cards</span> = first-time path.
<span class="cms-guide-legend cms-guide-legend--difficulty">Orange cards</span> = common difficulty + fix.</p>
<div class="cms-guide-step-list">
    <?php foreach ($sec['steps'] as $step) {
        $global_step++;
        $this->load->view($this->theme . 'cms_admin/guide/_step_visual', array(
            'step'               => $step,
            'step_num'           => $global_step,
            'fallback_image'     => $fallback_image,
            'guide_asset'        => $guide_asset,
            'guide_image_exists' => $guide_image_exists,
        ));
    } ?>
</div>
<?php } else { ?>
<ol class="cms-guide-steps">
    <?php foreach ($sec['steps'] as $step) { ?>
    <li><?= is_array($step) && isset($step['text']) ? $step['text'] : $step; ?></li>
    <?php } ?>
</ol>
<?php } ?>
<?php } ?>

<?php if (!empty($sec['tips']) && is_array($sec['tips'])) { ?>
<div class="cms-guide-tips">
    <strong><i class="fa fa-lightbulb-o"></i> Helpful tips</strong>
    <ul>
        <?php foreach ($sec['tips'] as $tip) { ?>
        <li><?= $tip; ?></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
