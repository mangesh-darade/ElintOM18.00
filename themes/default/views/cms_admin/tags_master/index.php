<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$tags = isset($tags) && is_array($tags) ? $tags : array();
$scope_options = isset($scope_options) && is_array($scope_options) ? $scope_options : array();
$this->load->helper('cms_tags');
?>

<div class="cms-page-intro">
    <h2><i class="fa fa-database"></i> Tag Master</h2>
    <p>Control which SEO fields appear on <strong>CMS Pages</strong> vs <strong>Entity Tag Mapping</strong>. Changes apply immediately after save.</p>
</div>

<div class="cms-tag-master-legend ws-card-box" style="padding:14px 18px;margin-bottom:16px;">
    <div class="row">
        <div class="col-md-4"><strong><i class="fa fa-file-text-o text-primary"></i> CMS Pages</strong> — title, meta, OG, homepage schema</div>
        <div class="col-md-4"><strong><i class="fa fa-cube text-success"></i> Entity forms</strong> — product / category / blog JSON-LD per item</div>
        <div class="col-md-4"><strong><i class="fa fa-filter text-muted"></i> Scope</strong> — <code>all</code> = every matching type; <code>home</code>, <code>product</code>, etc. = specific</div>
    </div>
</div>

<div class="ws-card-box">
    <div class="cms-table-scroll">
        <table id="tagsMasterTable" class="ws-modern-table cms-datatable cms-tag-master-table" data-cms-list="tags_master">
            <thead>
                <tr>
                    <th class="cms-col-id">ID</th>
                    <th>Tag name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th style="min-width:160px;">Scope</th>
                    <th style="text-align:center;width:100px;">CMS Pages</th>
                    <th style="text-align:center;width:100px;">Entity</th>
                    <th class="cms-col-actions" style="width:70px;">Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $tag) {
                    $tag_id = (int) $tag['id'];
                    $show_page = !empty($tag['show_on_page']);
                    $show_entity = !empty($tag['show_on_entity']);
                    $page_type = isset($tag['page_type']) ? (string) $tag['page_type'] : 'all';
                    ?>
                    <tr data-tag-id="<?= $tag_id; ?>">
                        <td class="cms-col-id"><?= $tag_id; ?></td>
                        <td style="font-weight:600;color:#0f172a;">
                            <?= htmlspecialchars(cms_tag_display_name($tag['tag_name']), ENT_QUOTES, 'UTF-8'); ?>
                            <br><small class="text-muted"><code><?= htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?></code></small>
                        </td>
                        <td><?= htmlspecialchars((string) $tag['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="label label-default"><?= htmlspecialchars((string) $tag['tag_type'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td>
                            <select class="form-control input-sm cms-tag-master-scope" data-tag-id="<?= $tag_id; ?>" aria-label="Scope">
                                <?php foreach ($scope_options as $scope_val => $scope_label) { ?>
                                    <option value="<?= htmlspecialchars($scope_val, ENT_QUOTES, 'UTF-8'); ?>"<?= $page_type === $scope_val ? ' selected' : ''; ?>>
                                        <?= htmlspecialchars($scope_label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <label class="cms-tag-master-toggle" title="Show on CMS Pages edit">
                                <input type="checkbox" class="cms-tag-master-show-page skip" data-tag-id="<?= $tag_id; ?>" value="1"<?= $show_page ? ' checked' : ''; ?>>
                                <span class="cms-tag-master-toggle-ui"></span>
                            </label>
                        </td>
                        <td style="text-align:center;">
                            <label class="cms-tag-master-toggle" title="Show on Entity Tag Mapping">
                                <input type="checkbox" class="cms-tag-master-show-entity skip" data-tag-id="<?= $tag_id; ?>" value="1"<?= $show_entity ? ' checked' : ''; ?>>
                                <span class="cms-tag-master-toggle-ui"></span>
                            </label>
                        </td>
                        <td class="cms-col-actions">
                            <a href="<?= site_url('cms_admin/tags_master/edit/' . $tag_id); ?>" class="btn-action-edit" title="Edit details"><i class="fa fa-pencil"></i></a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<p id="cmsTagMasterSaveStatus" class="cms-tag-master-save-status" aria-live="polite"></p>
