<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="cms-pages-container">

    <div class="cms-page-toolbar">
        <a class="btn-add-cms" href="<?= site_url('cms_admin/pages/add'); ?>">
            <i class="fa fa-plus"></i> Add New Page
        </a>
        <span id="cmsNavSortSaving" class="cms-sort-saving"><i class="fa fa-spinner fa-spin"></i> Saving…</span>
        <span id="cmsNavSortStatus" class="cms-sort-status"></span>
    </div>

    <div class="ws-card-box">
        <div class="cms-table-scroll">
            <table id="cmsPagesTable" class="ws-modern-table cms-datatable" data-cms-list="pages">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th style="width:44px;" aria-label="Drag to reorder"></th>
                        <th class="cms-col-name">Page Name</th>
                        <th style="width: 220px;">Menu Type</th>
                        <th>URL Code</th>
                        <th style="text-align: center; width: 140px;">Banner</th>
                        <th style="text-align: center; width: 110px;">Show In Header</th>
                        <th style="text-align: center; width: 110px;">Show In Footer</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 170px;">Last Updated</th>
                        <th class="cms-col-actions cms-actions-cell" style="width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="cmsPagesSortableBody">
                    <?php $media_base_path = base_url('assets/mdata/' . (isset($Customer_assets) ? $Customer_assets : 'localhost') . '/uploads/webshop/cms_pages/'); ?>
                    <?php
                    $page_name_by_id = array();
                    if (!empty($cms_pages)) {
                        foreach ($cms_pages as $page_item) {
                            $page_name_by_id[(int) $page_item['id']] = isset($page_item['page_name']) ? (string) $page_item['page_name'] : '';
                        }
                    }
                    ?>
                    <?php if (!empty($cms_pages)) { ?>
                        <?php $cms_page_sr = 0; foreach ($cms_pages as $page) {
                            $cms_page_sr++;
                            $page_name = isset($page['page_name']) ? (string) $page['page_name'] : '';
                            $sort_name = function_exists('mb_strtolower') ? mb_strtolower($page_name) : strtolower($page_name);
                            $parent_page_id = isset($page['parent_page_id']) ? (int) $page['parent_page_id'] : 0;
                            $is_submenu = $parent_page_id > 0;
                            $parent_page_name = $is_submenu && isset($page_name_by_id[$parent_page_id])
                                ? (string) $page_name_by_id[$parent_page_id]
                                : '';
                            $show_in_header = isset($page['show_in_header']) && in_array(strtolower(trim((string) $page['show_in_header'])), array('1', 'true', 'yes', 'on'), true);
                            $show_in_footer = isset($page['show_in_footer']) && in_array(strtolower(trim((string) $page['show_in_footer'])), array('1', 'true', 'yes', 'on'), true);
                            $toggle_url = site_url('cms_admin/pages/toggle_nav_visibility/' . (int) $page['id']);
                            ?>
                            <tr data-page-id="<?= (int) $page['id']; ?>">
                                <td class="cms-col-id"><?= (int) $page['id']; ?></td>
                                <td class="cms-col-srno text-center"><?= (int) $cms_page_sr; ?></td>
                                <td class="cms-drag-cell text-center">
                                    <span class="cms-drag-handle" title="Drag to reorder menu" aria-hidden="true"><i class="fa fa-bars"></i></span>
                                </td>
                                <td class="cms-col-name" data-order="<?= htmlspecialchars($sort_name, ENT_QUOTES, 'UTF-8'); ?>" style="font-weight: 600; color: #0f172a;">
                                    <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td style="font-size: 12px;">
                                    <?php if ($is_submenu) { ?>
                                        <span class="ws-status-badge ws-status-draft" style="margin-bottom: 4px;">
                                            <i class="fa fa-level-down" style="font-size: 10px;"></i> Submenu
                                        </span><br>
                                        <span class="text-muted">
                                            Parent: <?= htmlspecialchars($parent_page_name !== '' ? $parent_page_name : ('#' . $parent_page_id), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php } else { ?>
                                        <span class="ws-status-badge ws-status-published">
                                            <i class="fa fa-list" style="font-size: 10px;"></i> Main page
                                        </span>
                                    <?php } ?>
                                </td>
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
                                <td class="text-center cms-page-nav-toggle-cell">
                                    <div class="cms-page-nav-toggle-wrap"
                                         data-field="show_in_header"
                                         data-toggle-url="<?= htmlspecialchars($toggle_url, ENT_QUOTES, 'UTF-8'); ?>">
                                        <label class="cms-toggle-switch <?= $show_in_header ? 'is-on' : 'is-off'; ?>"
                                               title="<?= $show_in_header ? 'Shown in header menu — click to hide' : 'Hidden from header menu — click to show'; ?>">
                                            <input type="checkbox"
                                                   class="skip cms-page-nav-toggle"
                                                   <?= $show_in_header ? 'checked' : ''; ?>
                                                   aria-label="Show in header menu">
                                            <span class="cms-toggle-slider" aria-hidden="true"></span>
                                        </label>
                                    </div>
                                </td>
                                <td class="text-center cms-page-nav-toggle-cell">
                                    <div class="cms-page-nav-toggle-wrap"
                                         data-field="show_in_footer"
                                         data-toggle-url="<?= htmlspecialchars($toggle_url, ENT_QUOTES, 'UTF-8'); ?>">
                                        <label class="cms-toggle-switch <?= $show_in_footer ? 'is-on' : 'is-off'; ?>"
                                               title="<?= $show_in_footer ? 'Shown in footer menu — click to hide' : 'Hidden from footer menu — click to show'; ?>">
                                            <input type="checkbox"
                                                   class="skip cms-page-nav-toggle"
                                                   <?= $show_in_footer ? 'checked' : ''; ?>
                                                   aria-label="Show in footer menu">
                                            <span class="cms-toggle-slider" aria-hidden="true"></span>
                                        </label>
                                    </div>
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
                                <td class="cms-col-actions cms-actions-cell">
                                    <div class="cms-action-group">
                                        <a class="btn-action-edit" href="<?= site_url('cms_admin/pages/edit/' . (int) $page['id']); ?>" title="Edit" aria-label="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a class="btn-action-delete" href="<?= site_url('cms_admin/pages/delete/' . (int) $page['id']); ?>" title="Delete" aria-label="Delete" onclick="return confirm('Are you sure you want to delete this CMS page and its section blocks?');">
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
