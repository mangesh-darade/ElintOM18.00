<?php defined('BASEPATH') OR exit('No direct script access allowed');
$rows = isset($rows) && is_array($rows) ? $rows : array();
$schema_ready = !empty($schema_ready);
?>

<div class="cms-blogs-index">
    <div class="cms-panel-header" style="margin-bottom:16px;">
        <div class="cms-panel-header-text">
            <h4 class="cms-section-title"><i class="fa fa-newspaper-o"></i> Blog Posts</h4>
            <p class="cms-panel-desc">Articles shown in webshop <strong>Blog Grid</strong> sections. Click a row to edit; storefront links open at <code>/webshop/blog_details/{slug}</code>.</p>
        </div>
        <a href="<?= site_url('cms_admin/blogs/add'); ?>" class="btn btn-primary cms-btn-toolbar">
            <i class="fa fa-plus"></i> Add Blog Post
        </a>
    </div>

    <?php if (!$schema_ready) { ?>
    <div class="alert alert-warning">
        <strong>Blog table not ready.</strong> Run <code>database/cms_blogs_seed.sql</code> or save a post to auto-create <code>sma_cms_blogs</code>.
    </div>
    <?php } ?>

    <?php
    $this->load->view($this->theme . 'cms_admin/blogs/_categories_panel', array(
        'categories'  => isset($categories) ? $categories : array(),
        'return_url'  => site_url('cms_admin/blogs'),
        'collapsible' => true,
    ));
    ?>

    <div class="ws-card-box">
        <div class="cms-table-scroll">
            <table id="cmsBlogsTable" class="ws-modern-table cms-datatable" data-cms-list="blogs">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th>Title</th>
                        <th style="width:140px;">Category</th>
                        <th style="width:200px;">Slug</th>
                        <th style="width:100px;text-align:center;">Status</th>
                        <th style="width:80px;text-align:center;">Active</th>
                        <th style="width:170px;">Updated</th>
                        <th style="width:120px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)) {
                        $blog_sr = 0;
                        foreach ($rows as $row) {
                            $blog_sr++;
                            $title = (string) $row['title'];
                            $sort_title = function_exists('mb_strtolower') ? mb_strtolower($title) : strtolower($title);
                            $status = strtolower((string) $row['status']);
                            ?>
                    <tr>
                        <td class="cms-col-id"><?= (int) $row['id']; ?></td>
                        <td class="cms-col-srno text-center"><?= (int) $blog_sr; ?></td>
                        <td data-order="<?= htmlspecialchars($sort_title, ENT_QUOTES, 'UTF-8'); ?>" style="font-weight:600;color:#0f172a;">
                            <a href="<?= site_url('cms_admin/blogs/edit/' . (int) $row['id']); ?>"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php if (!empty($row['subtitle'])) { ?>
                            <div style="font-size:12px;color:#64748b;font-weight:400;"><?= htmlspecialchars((string) $row['subtitle'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php } ?>
                        </td>
                        <td style="font-size:13px;color:#475569;">
                            <?= !empty($row['category_name']) ? htmlspecialchars((string) $row['category_name'], ENT_QUOTES, 'UTF-8') : '—'; ?>
                        </td>
                        <td style="font-size:13px;color:#475569;"><code><?= htmlspecialchars((string) $row['slug'], ENT_QUOTES, 'UTF-8'); ?></code></td>
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
                            <a href="<?= site_url('cms_admin/blogs/edit/' . (int) $row['id']); ?>" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                            <?= form_open('cms_admin/blogs/delete/' . (int) $row['id'], array('style' => 'display:inline-block;', 'onsubmit' => "return confirm('Delete this blog post?');")); ?>
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
        Add a <strong>Blog Grid</strong> section on any CMS page (Pages → Edit → Add Section) to display published posts as product-style cards.
    </p>
</div>
