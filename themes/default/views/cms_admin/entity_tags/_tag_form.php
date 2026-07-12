<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$tags_by_category = isset($tags_by_category) ? $tags_by_category : array();
$existing_values  = isset($existing_values) ? $existing_values : array();
$scope_code       = isset($scope_code) ? (string) $scope_code : '';
$scope_label      = isset($scope_label) ? (string) $scope_label : '';
$head_tag_import_url = isset($head_tag_import_url) ? (string) $head_tag_import_url : '';
?>
<div class="row entity-wrap cms-entity-tag-form-root" id="cmsEntityTagFormRoot">
    <div class="col-lg-12">
        <?php $this->load->view($this->theme . 'cms_admin/_partials/tag_form_fields', array(
            'tags_by_category'     => $tags_by_category,
            'existing_values'      => $existing_values,
            'scope_form'           => 'entity',
            'scope_code'           => $scope_code,
            'scope_label'          => $scope_label,
            'head_tag_import_url'  => $head_tag_import_url,
        )); ?>
    </div>
</div>
