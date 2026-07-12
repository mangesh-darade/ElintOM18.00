<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="entity-mapping-container">

    <div class="cms-page-toolbar">
        <a class="btn-add-mapping" href="<?= site_url('cms_admin/entity_faqs/add'); ?>">
            <i class="fa fa-plus"></i> Add Entity FAQs
        </a>
    </div>

    <div class="ws-card-box">
        <div class="cms-table-scroll">
            <table id="entityFaqsTable" class="ws-modern-table cms-datatable" data-cms-list="entity_faqs">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th style="width: 150px;">Entity Type</th>
                        <th class="cms-col-name">Entity Name</th>
                        <th style="width: 140px; text-align: center;">Total FAQs</th>
                        <th style="width: 200px;">Created Date</th>
                        <th class="cms-col-actions cms-actions-cell" style="width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <?php
                        $entityName = isset($row['entity_label']) ? (string) $row['entity_label'] : '-';
                        $entityType = isset($row['entity_name']) ? (string) $row['entity_name'] : '';
                        $sort_name = function_exists('mb_strtolower') ? mb_strtolower((string) $entityName) : strtolower((string) $entityName);
                        ?>
                        <tr>
                            <td class="cms-col-id"><?= (int) $row['id']; ?></td>
                            <td class="cms-col-srno"></td>
                            <td>
                                <?php if (strtolower($entityType) === 'product') { ?>
                                    <span class="type-badge-product">Product</span>
                                <?php } else { ?>
                                    <span class="type-badge-category"><?= htmlspecialchars($entityType, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </td>
                            <td class="cms-col-name" data-order="<?= htmlspecialchars($sort_name, ENT_QUOTES, 'UTF-8'); ?>" style="font-weight: 600; color: #0f172a;">
                                <?= htmlspecialchars($entityName, ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="total-tags-pill">
                                    <i class="fa fa-question-circle" style="font-size: 11px; margin-right: 4px; opacity:0.85;"></i>
                                    <?= (int) $row['total_faqs']; ?>
                                </span>
                            </td>
                            <td style="font-size: 13.5px; color: #64748b;"><?= htmlspecialchars((string) $row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="cms-col-actions cms-actions-cell">
                                <div class="cms-action-group">
                                    <a href="<?= site_url('cms_admin/entity_faqs/edit/' . (int) $row['entity_master_id'] . '/' . (int) $row['entity_id']); ?>" class="btn-action-edit" title="Edit" aria-label="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="<?= site_url('cms_admin/entity_faqs/delete/' . (int) $row['entity_master_id'] . '/' . (int) $row['entity_id']); ?>"
                                       onclick="return confirm('Delete all FAQs for this entity?');" class="btn-action-delete" title="Delete" aria-label="Delete">
                                        <i class="fa fa-trash-o"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
