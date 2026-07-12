<?php defined('BASEPATH') OR exit('No direct script access allowed');
$rows = isset($rows) && is_array($rows) ? $rows : array();
$schema_ready = !empty($schema_ready);
?>

<div class="cms-testimonials-index">
    <div class="cms-panel-header" style="margin-bottom:16px;">
        <div class="cms-panel-header-text">
            <h4 class="cms-section-title"><i class="fa fa-quote-left"></i> Testimonials</h4>
            <p class="cms-panel-desc">Client quotes shown in webshop <strong>Testimonials Grid</strong> sections. Each row has person name, photo, and comments.</p>
        </div>
        <a href="<?= site_url('cms_admin/testimonials/add'); ?>" class="btn btn-primary cms-btn-toolbar">
            <i class="fa fa-plus"></i> Add Testimonial
        </a>
    </div>

    <?php if (!$schema_ready) { ?>
    <div class="alert alert-warning">
        <strong>Testimonials table not ready.</strong> Run <code>database/cms_testimonials_seed.sql</code> or save a row to auto-create <code>sma_cms_testimonials</code>.
    </div>
    <?php } ?>

    <div class="ws-card-box">
        <div class="cms-table-scroll">
            <table id="cmsTestimonialsTable" class="ws-modern-table cms-datatable" data-cms-list="testimonials">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th>Person Name</th>
                        <th>Comments</th>
                        <th style="width:100px;text-align:center;">Status</th>
                        <th style="width:80px;text-align:center;">Active</th>
                        <th style="width:170px;">Updated</th>
                        <th style="width:120px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)) {
                        $sr = 0;
                        foreach ($rows as $row) {
                            $sr++;
                            $name = (string) $row['person_name'];
                            $sort_name = function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name);
                            $status = strtolower((string) $row['status']);
                            $comment = (string) $row['comments'];
                            $commentShort = strlen($comment) > 80 ? substr($comment, 0, 77) . '…' : $comment;
                            ?>
                    <tr>
                        <td class="cms-col-id"><?= (int) $row['id']; ?></td>
                        <td class="cms-col-srno text-center"><?= (int) $sr; ?></td>
                        <td data-order="<?= htmlspecialchars($sort_name, ENT_QUOTES, 'UTF-8'); ?>" style="font-weight:600;color:#0f172a;">
                            <a href="<?= site_url('cms_admin/testimonials/edit/' . (int) $row['id']); ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></a>
                        </td>
                        <td style="font-size:13px;color:#475569;"><?= htmlspecialchars($commentShort, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="text-align:center;">
                            <?php if ($status === 'published') { ?>
                            <span class="label label-success">Published</span>
                            <?php } else { ?>
                            <span class="label label-default">Draft</span>
                            <?php } ?>
                        </td>
                        <td style="text-align:center;"><?= !empty($row['is_active']) ? '<span class="label label-info">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
                        <td style="font-size:13px;color:#64748b;"><?= !empty($row['updated_at']) ? htmlspecialchars((string) $row['updated_at'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                        <td style="text-align:center;white-space:nowrap;" class="cms-col-actions">
                            <a href="<?= site_url('cms_admin/testimonials/edit/' . (int) $row['id']); ?>" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                            <?= form_open('cms_admin/testimonials/delete/' . (int) $row['id'], array('style' => 'display:inline-block;', 'onsubmit' => "return confirm('Delete this testimonial?');")); ?>
                            <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash-o"></i></button>
                            <?= form_close(); ?>
                        </td>
                    </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted" style="margin-top:14px;font-size:13px;">
        Add a <strong>Testimonials Grid</strong> section on any CMS page (Pages → Edit → Add Section) to display published testimonials.
    </p>
</div>
