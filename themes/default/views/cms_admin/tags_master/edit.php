<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$tag = isset($tag) && is_array($tag) ? $tag : array();
$scope_options = isset($scope_options) && is_array($scope_options) ? $scope_options : array();
$tag_id = (int) $tag['id'];
$show_page = !empty($tag['show_on_page']);
$show_entity = !empty($tag['show_on_entity']);
$page_type = isset($tag['page_type']) ? (string) $tag['page_type'] : 'all';
$this->load->helper('cms_tags');
?>

<div class="box">
    <div class="box-content">
        <div class="cms-panel-header" style="margin-bottom:20px;">
            <div class="cms-panel-header-text">
                <h4 class="cms-section-title"><i class="fa fa-tag"></i> <?= htmlspecialchars(cms_tag_display_name($tag['tag_name']), ENT_QUOTES, 'UTF-8'); ?></h4>
                <p class="cms-panel-desc">Internal name: <code><?= htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?></code></p>
            </div>
            <a href="<?= site_url('cms_admin/tags_master'); ?>" class="btn btn-default cms-btn-toolbar"><i class="fa fa-arrow-left"></i> Back to list</a>
        </div>

        <?= form_open('cms_admin/tags_master/edit/' . $tag_id); ?>
        <input type="hidden" name="save_tag_master" value="1">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Category (tab label)</label>
                    <input type="text" name="category" class="form-control" value="<?= htmlspecialchars((string) $tag['category'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tag type</label>
                    <select name="tag_type" class="form-control">
                        <?php foreach (array('meta', 'link', 'og', 'schema', 'twitter') as $tt) { ?>
                            <option value="<?= $tt; ?>"<?= (string) $tag['tag_type'] === $tt ? ' selected' : ''; ?>><?= strtoupper($tt); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Scope (page_type)</label>
                    <select name="page_type" class="form-control">
                        <?php foreach ($scope_options as $scope_val => $scope_label) { ?>
                            <option value="<?= htmlspecialchars($scope_val, ENT_QUOTES, 'UTF-8'); ?>"<?= $page_type === $scope_val ? ' selected' : ''; ?>>
                                <?= htmlspecialchars($scope_label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <p class="help-block">Example: <code>product</code> scope + Entity ON = product schema on each product entity only.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="show_on_page" value="1"<?= $show_page ? ' checked' : ''; ?>>
                        Show on <strong>CMS Pages</strong> tag form
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="show_on_entity" value="1"<?= $show_entity ? ' checked' : ''; ?>>
                        Show on <strong>Entity Tag Mapping</strong> form
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save tag</button>
        <?= form_close(); ?>
    </div>
</div>
