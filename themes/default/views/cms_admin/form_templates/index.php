<?php defined('BASEPATH') OR exit('No direct script access allowed');
$rows = isset($rows) && is_array($rows) ? $rows : array();
$schema_ready = !empty($schema_ready);
?>

<div class="cms-form-templates-index">
    <div class="cms-page-toolbar">
        <a class="btn-add-cms" href="<?= site_url('cms_admin/form_templates/add'); ?>">
            <i class="fa fa-plus"></i> New Form Template
        </a>
    </div>

    <?php if (!$schema_ready) { ?>
    <div class="alert alert-warning">
        <strong>Database table missing.</strong> Saving will attempt to create <code>sma_cms_webshop_contact_forms</code> automatically on first save.
    </div>
    <?php } ?>

    <div class="ws-card-box">
        <div class="cms-table-scroll">
            <table id="formTemplatesTable" class="ws-modern-table cms-datatable" data-cms-list="form_templates">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th>Template name</th>
                        <th>Form key</th>
                        <th>Fields</th>
                        <th>Design</th>
                        <th>Page URL</th>
                        <th style="width:90px;text-align:center;">Active</th>
                        <th style="width:160px;">Updated</th>
                        <th class="cms-col-actions cms-actions-cell" style="width:170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)) {
                        foreach ($rows as $row) {
                            $cfg = isset($row['config']) && is_array($row['config']) ? $row['config'] : array();
                            $field_count = isset($cfg['fields']) && is_array($cfg['fields']) ? count($cfg['fields']) : 0;
                            $design = isset($row['design_info']) && is_array($row['design_info']) ? $row['design_info'] : array();
                            $design_label = isset($design['label']) ? (string) $design['label'] : '—';
                            $design_swatches = isset($design['swatches']) && is_array($design['swatches']) ? $design['swatches'] : array();
                            ?>
                    <tr>
                        <td class="cms-col-id"><?= (int) $row['id']; ?></td>
                        <td class="cms-col-srno"></td>
                        <td style="font-weight:600;color:#0f172a;">
                            <?= htmlspecialchars((string) $row['form_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td><code><?= htmlspecialchars((string) $row['form_key'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                        <td><?= (int) $field_count; ?></td>
                        <td>
                            <span class="cms-ft-design-pill" title="<?= htmlspecialchars($design_label, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php foreach ($design_swatches as $swatch) { ?>
                                <span class="cms-ft-design-swatch" style="background:<?= htmlspecialchars((string) $swatch, ENT_QUOTES, 'UTF-8'); ?>"></span>
                                <?php } ?>
                                <?= htmlspecialchars($design_label, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td><?= $row['page_url'] !== '' ? htmlspecialchars((string) $row['page_url'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                        <td style="text-align:center;">
                            <?php if (!empty($row['is_active'])) { ?>
                            <span class="label label-success">Yes</span>
                            <?php } else { ?>
                            <span class="label label-default">No</span>
                            <?php } ?>
                        </td>
                        <td style="font-size:13px;color:#64748b;"><?= htmlspecialchars((string) $row['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="cms-col-actions cms-actions-cell">
                            <div class="cms-action-group">
                                <button type="button" class="btn-action-edit cms-ft-embed-open" title="Embed links" aria-label="Embed links"
                                        data-template-id="<?= (int) $row['id']; ?>"
                                        data-template-name="<?= htmlspecialchars((string) $row['form_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa fa-link"></i>
                                </button>
                                <a href="<?= site_url('cms_admin/form_templates/edit/' . (int) $row['id']); ?>" class="btn-action-edit" title="Edit" aria-label="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?= site_url('cms_admin/form_templates/duplicate/' . (int) $row['id']); ?>" class="btn-action-edit" title="Duplicate" aria-label="Duplicate">
                                    <i class="fa fa-copy"></i>
                                </a>
                                <a href="<?= site_url('cms_admin/form_templates/delete/' . (int) $row['id']); ?>"
                                   onclick="return confirm('Delete this form template?');" class="btn-action-delete" title="Delete" aria-label="Delete">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted" style="margin-top:14px;font-size:13px;">
        Use <strong>Embed</strong> (<i class="fa fa-link"></i>) to copy a direct link or iframe code for any page.
        Use <strong>form key</strong> when adding a Contact Form section on CMS Pages, or assign a <strong>page URL</strong> to auto-load this template on matching storefront pages.
    </p>
</div>
