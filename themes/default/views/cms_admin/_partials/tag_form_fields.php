<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * Tag tabs + inputs (shared by pages edit, entity tags, AJAX partial).
 *
 * @var array $tags_by_category
 * @var array $existing_values  tag_id => value
 * @var string $scope_form      page|entity
 * @var string $scope_code
 * @var string $scope_label
 * @var string $head_tag_import_url
 */
$tags_by_category = isset($tags_by_category) ? $tags_by_category : array();
$existing_values  = isset($existing_values) ? $existing_values : array();
$scope_form       = isset($scope_form) ? (string) $scope_form : 'page';
$scope_code       = isset($scope_code) ? (string) $scope_code : '';
$scope_label      = isset($scope_label) ? (string) $scope_label : '';
$head_tag_import_url = isset($head_tag_import_url) ? (string) $head_tag_import_url : '';

$this->load->helper('cms_tags');
$tag_count = 0;
foreach ($tags_by_category as $items) {
    $tag_count += is_array($items) ? count($items) : 0;
}

$is_entity_scope = ($scope_form === 'entity');
$seo_expanded = false;

if ($is_entity_scope) {
    if ($scope_label === '' && $scope_code !== '') {
        $scope_label = cms_tags_form_scope_label($scope_form, $scope_code);
    }
    $seo_title = ($scope_label !== '') ? $scope_label : 'SEO & Schema Fields';
    $scope_hint = '';
    if ($scope_code !== '') {
        $scope_hint = cms_tags_form_scope_hint($scope_form, $tag_count, $scope_code);
    }
    ?>
    <div class="cms-collapsible-panel<?= $seo_expanded ? ' is-expanded' : ''; ?>" id="cmsEntitySeoCollapsible">
        <button type="button" class="cms-collapsible-header" aria-expanded="<?= $seo_expanded ? 'true' : 'false'; ?>" aria-controls="cmsEntitySeoCollapsibleBody">
            <span class="cms-collapsible-chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
            <span class="cms-collapsible-header-text">
                <i class="fa fa-filter"></i>
                <span id="cmsTagScopeBannerTitle"><?= htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></span>
            </span>
            <?php if ($tag_count > 0) { ?>
                <span class="cms-collapsible-count badge"><?= (int) $tag_count; ?> field<?= $tag_count === 1 ? '' : 's'; ?></span>
            <?php } ?>
        </button>
        <div class="cms-collapsible-body" id="cmsEntitySeoCollapsibleBody">
            <?php if ($scope_hint !== '') { ?>
                <p class="cms-collapsible-hint text-muted"><?= htmlspecialchars($scope_hint, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } ?>
    <?php
} else {
    $this->load->view($this->theme . 'cms_admin/_partials/tag_scope_banner', array(
        'scope_form'  => $scope_form,
        'scope_code'  => $scope_code,
        'scope_label' => $scope_label,
        'tag_count'   => $tag_count,
    ));
}

if ($scope_code === '' && $scope_form === 'entity') {
    ?>
    <div class="alert alert-info cms-tag-scope-empty">
        <i class="fa fa-hand-pointer-o"></i> Select <strong>Type</strong> above to load the correct SEO fields for that entity.
    </div>
    <?php
} elseif (empty($tags_by_category)) {
    ?>
    <div class="alert alert-warning cms-tag-scope-empty">
        <i class="fa fa-info-circle"></i> No tags are enabled for this form yet. Use <strong>Import from head script</strong> below to auto-create tags, or enable fields in <strong>Tag Master</strong>.
    </div>
    <?php
} else {
    ?>
    <ul class="nav nav-tabs cms-tag-tabs" role="tablist">
        <?php $i = 0; foreach ($tags_by_category as $cat => $items) { ?>
            <li role="presentation" class="<?= $i === 0 ? 'active' : ''; ?>">
                <a href="#tab_cat_<?= $i; ?>" role="tab" data-toggle="tab">
                    <?= htmlspecialchars(trim((string) $cat), ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </li>
        <?php $i++; } ?>
    </ul>
    <div class="tab-content cms-tag-tab-content">
        <?php $i = 0; foreach ($tags_by_category as $cat => $items) { ?>
            <div role="tabpanel" class="tab-pane <?= $i === 0 ? 'active' : ''; ?>" id="tab_cat_<?= $i; ?>">
                <div class="row">
                    <?php foreach ($items as $tag) { ?>
                        <?php
                        $tag_id = (int) $tag['id'];
                        $display_tag_name = cms_tag_display_name($tag['tag_name']);
                        $tag_page_type = isset($tag['page_type']) ? trim((string) $tag['page_type']) : 'all';
                        if ($tag_page_type === '') {
                            $tag_page_type = 'all';
                        }
                        $tag_field_value = isset($existing_values[$tag_id]) ? $existing_values[$tag_id] : '';
                        $is_schema_tag = !empty($tag['tag_type']) && $tag['tag_type'] === 'schema';
                        $field_col_class = $is_schema_tag ? 'col-md-12' : 'col-md-6';
                        $input_class = 'entity-tag-input cms-tag-value-input';
                        if ($is_schema_tag) {
                            $input_class .= ' skip cms-tag-value-field';
                        }
                        ?>
                        <div class="<?= $field_col_class; ?>">
                            <div class="form-group">
                                <label for="tag_value_<?= $tag_id; ?>">
                                    <?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ($scope_form === 'entity' || $scope_form === 'page') { ?>
                                        <span class="cms-tag-page-type-badge" title="Tag Master page_type"><?= htmlspecialchars($tag_page_type, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>
                                </label>
                                <?php if ($is_schema_tag) { ?>
                                    <textarea
                                        class="form-control <?= htmlspecialchars($input_class, ENT_QUOTES, 'UTF-8'); ?>"
                                        id="tag_value_<?= $tag_id; ?>"
                                        name="tag_values[<?= $tag_id; ?>]"
                                        rows="8"
                                        placeholder="Enter JSON-LD or value for <?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    ><?= htmlspecialchars($tag_field_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <?php } else { ?>
                                    <input
                                        type="text"
                                        class="form-control <?= htmlspecialchars($input_class, ENT_QUOTES, 'UTF-8'); ?>"
                                        id="tag_value_<?= $tag_id; ?>"
                                        name="tag_values[<?= $tag_id; ?>]"
                                        value="<?= htmlspecialchars($tag_field_value, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Enter value for <?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php $i++; } ?>
    </div>
    <?php
}

if ($head_tag_import_url !== '') {
    $this->load->view($this->theme . 'cms_admin/_partials/head_tag_import_panel', array(
        'head_tag_import_url' => $head_tag_import_url,
    ));
}

if ($is_entity_scope) {
    ?>
        </div>
    </div>
    <?php
}
