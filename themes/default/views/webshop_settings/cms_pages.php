<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="cms-pages-container">

    <!-- ── Header ── -->
    <div class="cms-back-header">
        <div class="cms-page-title-wrap">
            <a href="<?= site_url('cms_admin_panel'); ?>" class="cms-back-btn">
                <i class="fa fa-arrow-left"></i> Back to CMS Admin Panel
            </a>
            <h1 class="cms-page-title"><i class="fa fa-file-text-o"></i> CMS Page List</h1>
            <p class="cms-page-subtitle">Publish static pages, privacy policies, terms, and custom banner promotions.</p>
        </div>
        <div>
            <a class="btn-add-cms" href="<?= site_url('webshop_settings/add_cms_page'); ?>">
                <i class="fa fa-plus"></i> Add New Page
            </a>
        </div>
    </div>

    <!-- ── Main Card Box ── -->
    <div class="ws-card-box">
        <div class="table-responsive">
            <table class="ws-modern-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Page Name</th>
                        <th>URL Code</th>
                        <th style="text-align: center; width: 140px;">Banner</th>
                        <th style="text-align: center; width: 110px;">Logo</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 170px;">Last Updated</th>
                        <th style="text-align: center; width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $media_base_path = base_url('assets/mdata/' . (isset($Customer_assets) ? $Customer_assets : 'localhost') . '/uploads/webshop/cms_pages/'); ?>
                    <?php if (!empty($cms_pages)) { ?>
                        <?php foreach ($cms_pages as $page) { ?>
                            <tr>
                                <td style="font-weight: 600; color: #64748b;"><?= (int) $page['id']; ?></td>
                                <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($page['page_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="font-family: monospace; color: #2563eb; font-size: 13px; font-weight: 500;">
                                    <?= htmlspecialchars($page['url'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($page['banner_image'])) { ?>
                                        <img src="<?= htmlspecialchars($media_base_path . $page['banner_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Banner" class="ws-media-thumbnail">
                                    <?php } else { ?>
                                        <span class="text-muted" style="font-size: 20px;">-</span>
                                    <?php } ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($page['logo_image'])) { ?>
                                        <img src="<?= htmlspecialchars($media_base_path . $page['logo_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" class="ws-media-thumbnail">
                                    <?php } else { ?>
                                        <span class="text-muted" style="font-size: 20px;">-</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($page['status'] === 'published') { ?>
                                        <span class="ws-status-badge ws-status-published">
                                            <i class="fa fa-circle" style="font-size: 8px;"></i> Published
                                        </span>
                                    <?php } else { ?>
                                        <span class="ws-status-badge ws-status-draft">
                                            <i class="fa fa-circle-o" style="font-size: 8px;"></i> Draft
                                        </span>
                                    <?php } ?>
                                </td>
                                <td style="font-size: 13px; color: #64748b;"><?= htmlspecialchars($page['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a class="btn-action-edit" href="<?= site_url('webshop_settings/edit_cms_page/' . (int) $page['id']); ?>">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a class="btn-action-delete" href="<?= site_url('webshop_settings/delete_cms_page/' . (int) $page['id']); ?>" onclick="return confirm('Are you sure you want to delete this CMS page and its section blocks?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #64748b; padding: 40px;">
                                <i class="fa fa-folder-open-o" style="font-size: 32px; display: block; margin-bottom: 12px; color: #94a3b8;"></i>
                                No pages created yet. Click the "Add New Page" button to get started.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
