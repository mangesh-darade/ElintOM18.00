<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$scope_form = isset($scope_form) ? (string) $scope_form : 'page';
$scope_code = isset($scope_code) ? (string) $scope_code : '';
$scope_label = isset($scope_label) ? (string) $scope_label : '';
$scope_hint = isset($scope_hint) ? (string) $scope_hint : '';
$tag_count = isset($tag_count) ? (int) $tag_count : 0;
if ($scope_label === '' && $scope_code !== '') {
    $this->load->helper('cms_tags');
    $scope_label = cms_tags_form_scope_label($scope_form, $scope_code);
}
if ($scope_hint === '') {
    $this->load->helper('cms_tags');
    $scope_hint = cms_tags_form_scope_hint($scope_form, $tag_count, $scope_code);
}
?>
<div class="cms-tag-scope-banner" id="cmsTagScopeBanner" data-scope-form="<?= htmlspecialchars($scope_form, ENT_QUOTES, 'UTF-8'); ?>" data-scope-code="<?= htmlspecialchars($scope_code, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="cms-tag-scope-banner__icon" aria-hidden="true"><i class="fa fa-filter"></i></div>
    <div class="cms-tag-scope-banner__body">
        <strong class="cms-tag-scope-banner__title"><?= htmlspecialchars($scope_label, ENT_QUOTES, 'UTF-8'); ?></strong>
        <p class="cms-tag-scope-banner__hint"><?= htmlspecialchars($scope_hint, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <?php if ($tag_count > 0) { ?>
        <span class="cms-tag-scope-banner__count badge"><?= (int) $tag_count; ?> field<?= $tag_count === 1 ? '' : 's'; ?></span>
    <?php } ?>
</div>
