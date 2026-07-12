<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .entity-mapping-container {
        font-family: 'Inter', sans-serif;
        color: #2c3e50;
        background: #f8fafc;
        padding: 15px 5px 30px;
    }

    /* ── Header Back Navigation ── */
    .cms-back-header {
        background: #ffffff;
        padding: 24px 30px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .cms-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f1f5f9;
        color: #105C6A;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
    }
    .cms-back-btn:hover {
        background: #105C6A;
        color: #ffffff;
        transform: translateX(-3px);
    }
    .cms-page-title-wrap {
        flex-grow: 1;
    }
    .cms-page-title {
        margin: 10px 0 4px;
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .cms-page-subtitle {
        margin: 0;
        font-size: 14px;
        color: #64748b;
    }

    /* ── Action Buttons ── */
    .btn-add-mapping {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff !important;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 20px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2);
        transition: all 0.2s;
        text-decoration: none !important;
    }
    .btn-add-mapping:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.3);
    }

    /* ── Content Card Box ── */
    .ws-card-box {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 24px;
    }

    /* ── Styled Modern Tables ── */
    .ws-modern-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .ws-modern-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    .ws-modern-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
        color: #334155;
        vertical-align: middle;
    }
    .ws-modern-table tr:last-child td {
        border-bottom: none;
    }
    .ws-modern-table tr:hover td {
        background-color: #f8fafc;
    }

    /* ── Type Badges ── */
    .type-badge-product {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.15);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .type-badge-category {
        background: rgba(124, 58, 237, 0.1);
        color: #7c3aed;
        border: 1px solid rgba(124, 58, 237, 0.15);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Total Tags Badges ── */
    .total-tags-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        color: #b45309;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 12.5px;
        border: 1px solid rgba(217, 119, 6, 0.15);
    }

    /* ── Action Buttons ── */
    .btn-action-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.15);
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none !important;
        transition: all 0.2s;
    }
    .btn-action-edit:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        transform: scale(1.05);
    }

    .btn-action-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.15);
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none !important;
        transition: all 0.2s;
        margin-left: 6px;
    }
    .btn-action-delete:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: scale(1.05);
    }

</style>

<div class="entity-mapping-container">

    <!-- ── Header ── -->
    <div class="cms-back-header">
        <div class="cms-page-title-wrap">
            <a href="<?= site_url('cms_admin_panel'); ?>" class="cms-back-btn">
                <i class="fa fa-arrow-left"></i> Back to CMS Admin Panel
            </a>
            <h1 class="cms-page-title"><i class="fa fa-tags"></i> Entity Tag Mapping</h1>
            <p class="cms-page-subtitle">Map descriptive tags to products or categories to drive optimized smart filters.</p>
        </div>
        <div>
            <a class="btn-add-mapping" href="<?= site_url('entity_mapping/add'); ?>">
                <i class="fa fa-plus"></i> Add Mapping
            </a>
        </div>
    </div>

    <!-- ── Main Card Box ── -->
    <div class="ws-card-box">
        <div class="table-responsive">
            <table id="entityMappingTable" class="ws-modern-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 150px;">Type</th>
                        <th>Entity Name</th>
                        <th style="width: 140px; text-align: center;">Total Tags</th>
                        <th style="width: 200px;">Created Date</th>
                        <th style="text-align: center; width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)) { foreach ($rows as $row) { ?>
                        <?php
                        $entityName = '-';
                        $code = isset($row['entity_code']) ? strtolower((string) $row['entity_code']) : '';
                        if ($code === 'product' && !empty($row['product_name'])) {
                            $entityName = $row['product_name'];
                        } elseif ($code === 'category' && !empty($row['category_name'])) {
                            $entityName = $row['category_name'];
                        }
                        $entityType = isset($row['entity_name']) ? (string) $row['entity_name'] : '';
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: #64748b;"><?= (int) $row['id']; ?></td>
                            <td>
                                <?php if (strtolower($entityType) === 'product') { ?>
                                    <span class="type-badge-product">Product</span>
                                <?php } else { ?>
                                    <span class="type-badge-category"><?= htmlspecialchars($entityType, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </td>
                            <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($entityName, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="text-align: center;">
                                <span class="total-tags-pill">
                                    <i class="fa fa-tag" style="font-size: 11px; margin-right: 4px; opacity:0.85;"></i>
                                    <?= (int) $row['total_tags']; ?>
                                </span>
                            </td>
                            <td style="font-size: 13.5px; color: #64748b;"><?= htmlspecialchars((string) $row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="<?= site_url('entity_mapping/edit/' . (int) $row['entity_master_id'] . '/' . (int) $row['entity_id']); ?>" class="btn-action-edit" title="Edit Mapping">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?= site_url('entity_mapping/delete/' . (int) $row['entity_master_id'] . '/' . (int) $row['entity_id']); ?>"
                                   onclick="return confirm('Are you sure you want to delete this mapping group?');" class="btn-action-delete" title="Delete Mapping">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        if ($.fn && $.fn.DataTable) {
            $('#entityMappingTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "info": true,
                "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>'
            });
        }
    });
</script>
