<?php defined('BASEPATH') OR exit('No direct script access allowed');

$upload_base = base_url('assets/mdata/' . (isset($Customer_assets) ? $Customer_assets : 'localhost') . '/uploads/');
$schema_ok = !empty($header_footer_schema_ready);
$webshop_url = site_url('webshop');
?>

<div class="storefront-container storefront-list-page">

    <div class="cms-page-toolbar storefront-toolbar">
        <a class="cms-btn-primary btn-add-row" href="<?= site_url('cms_admin/storefront/add'); ?>">
            <i class="fa fa-plus"></i> Add New Row
        </a>
    </div>

    <?php if (!$schema_ok) { ?>
        <div class="alert-database-notice">
            <i class="fa fa-exclamation-triangle"></i>
            <div>
                <strong>Database Setup Required:</strong> To enable all custom features, please import the <code>sma_cms_webshop_header_footer</code> file in your database schema, then refresh this page.
            </div>
        </div>
    <?php } ?>

    <div class="ws-card-box storefront-table-card">
        <div class="cms-table-scroll cms-table-scroll--wide">
            <table id="cmsStorefrontTable" class="ws-modern-table cms-datatable" data-cms-list="storefront">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th style="width: 90px;">Section</th>
                        <th class="cms-col-name" style="width: 150px;">Label</th>
                        <th class="cms-col-value" style="width: 190px;">Value</th>
                        <th style="width: 56px;">Icon</th>
                        <th style="width: 48px;">Sort</th>
                        <th style="width: 70px;">Active</th>
                        <th class="cms-col-actions cms-actions-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($identity_rows)) { ?>
                        <?php foreach ($identity_rows as $row) {
                            $sec = isset($row['section_type']) ? (string) $row['section_type'] : '';
                            $fk  = isset($row['field_key']) ? (string) $row['field_key'] : '';
                            $lbl = isset($row['label']) ? (string) $row['label'] : '';
                            $val = isset($row['value']) ? (string) $row['value'] : '';
                            $sort = isset($row['sort_order']) ? (int) $row['sort_order'] : 0;
                            $active = !empty($row['is_active']);
                            $is_image_path = (bool) preg_match('/\\.(jpe?g|png|gif|webp)$/i', $val);
                            $is_uploaded = $is_image_path && strpos($val, 'webshop/') === 0;
                            $preview_url = $is_uploaded ? $upload_base . $val : '';
                            $short = function_exists('mb_substr') ? mb_substr($val, 0, 80) : substr($val, 0, 80);
                            if (strlen($val) > 80) {
                                $short .= '…';
                            }
                            $sort_lbl = function_exists('mb_strtolower') ? mb_strtolower($lbl) : strtolower($lbl);
                            $preview_href = '';
                            if ($preview_url !== '') {
                                $preview_href = $preview_url;
                            } elseif (preg_match('#^https?://#i', $val)) {
                                $preview_href = $val;
                            } elseif ($fk === 'home_url' || $fk === 'store_url') {
                                $preview_href = $webshop_url;
                            }
                            ?>
                            <tr>
                                <td class="cms-col-id"><?= (int) $row['id']; ?></td>
                                <td class="cms-col-srno"></td>
                                <td>
                                    <?php if ($sec === 'header') { ?>
                                        <span class="section-badge-header">Header</span>
                                    <?php } else { ?>
                                        <span class="section-badge-footer">Footer</span>
                                    <?php } ?>
                                </td>
                                <td class="cms-col-name" data-order="<?= htmlspecialchars($sort_lbl, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="cms-cell-truncate" title="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if ($is_uploaded && $preview_url !== '') { ?>
                                        <div class="storefront-value-media">
                                            <img src="<?= htmlspecialchars($preview_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="ws-media-thumbnail">
                                            <span class="cms-cell-truncate-inner"><?= htmlspecialchars($short, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    <?php } else { ?>
                                        <span class="cms-cell-truncate-inner"><?= htmlspecialchars($short, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['icons'])) { ?>
                                        <span class="ws-code-badge ws-code-badge--icon" title="<?= htmlspecialchars($row['icons'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-<?= htmlspecialchars($row['icons'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                                        </span>
                                    <?php } else { ?>
                                        <span class="text-muted">—</span>
                                    <?php } ?>
                                </td>
                                <td class="storefront-sort-cell"><?= (int) $sort; ?></td>
                                <td class="cms-section-status-cell">
                                    <div class="cms-section-toggle-wrap storefront-active-toggle-wrap"
                                         data-row-id="<?= (int) $row['id']; ?>"
                                         data-toggle-url="<?= htmlspecialchars(site_url('cms_admin/storefront/toggle_active/' . (int) $row['id']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <label class="cms-toggle-switch <?= $active ? 'is-on' : 'is-off'; ?>">
                                            <input type="checkbox"
                                                   class="skip storefront-active-toggle"
                                                   <?= $active ? 'checked' : ''; ?>
                                                   aria-label="Toggle storefront row active">
                                            <span class="cms-toggle-slider" aria-hidden="true"></span>
                                        </label>
                                        <span class="cms-section-status-label<?= $active ? ' is-active' : ' is-inactive'; ?>">
                                            <?= $active ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="cms-col-actions cms-actions-cell">
                                    <div class="cms-action-group">
                                        <?php if ($preview_href !== '') { ?>
                                        <a class="btn-action-preview" href="<?= htmlspecialchars($preview_href, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" title="Preview" aria-label="Preview">
                                            <i class="fa fa-external-link"></i>
                                        </a>
                                        <?php } ?>
                                        <a class="btn-action-edit" href="<?= site_url('cms_admin/storefront/edit/' . (int) $row['id']); ?>" title="Edit" aria-label="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a class="btn-action-delete" href="<?= site_url('cms_admin/storefront/delete/' . (int) $row['id']); ?>" title="Delete" aria-label="Delete" onclick="return confirm('Delete this row?');">
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

<script type="text/javascript">
(function ($) {
    'use strict';

    var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    var csrfName = cmsCfg.csrfName || '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = cmsCfg.csrfHash || '<?= $this->security->get_csrf_hash(); ?>';

    function getCsrfToken() {
        if (!csrfName) {
            return null;
        }
        var tokenFromInput = $('input[name="' + csrfName + '"]').first().val();
        return tokenFromInput || csrfHash;
    }

    function syncCsrfHash(nextHash) {
        if (!nextHash) {
            return;
        }
        csrfHash = nextHash;
        if (window.CmsAdmin && window.CmsAdmin.config) {
            window.CmsAdmin.config.csrfHash = nextHash;
        }
        $('input[name="' + csrfName + '"]').val(nextHash);
    }

    $(document).on('change', '.storefront-active-toggle', function () {
        var $input = $(this);
        var $wrap = $input.closest('.storefront-active-toggle-wrap');
        var toggleUrl = $wrap.data('toggle-url');
        var isActive = $input.is(':checked') ? 1 : 0;
        var previous = !isActive;

        if (!toggleUrl) {
            return;
        }

        $wrap.addClass('is-saving');
        var payload = { is_active: isActive };
        var token = getCsrfToken();
        if (csrfName && token) {
            payload[csrfName] = token;
        }

        $.ajax({
            url: toggleUrl,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            syncCsrfHash(res && res.csrf_hash ? res.csrf_hash : null);
            if (!res || res.status !== 'success') {
                $input.prop('checked', previous === 1);
                if (res && res.message) {
                    window.alert(res.message);
                } else {
                    window.alert('Could not update status. Please try again.');
                }
                return;
            }
            isActive = parseInt(res.is_active, 10) === 1 ? 1 : 0;
            $input.prop('checked', isActive === 1);
            var $switch = $wrap.find('.cms-toggle-switch');
            var $label = $wrap.find('.cms-section-status-label');
            $switch.toggleClass('is-on', isActive === 1).toggleClass('is-off', isActive !== 1);
            $label.toggleClass('is-active', isActive === 1).toggleClass('is-inactive', isActive !== 1)
                .text(isActive === 1 ? 'Active' : 'Inactive');
        }).fail(function (xhr) {
            $input.prop('checked', previous === 1);
            if (xhr && xhr.status === 403) {
                window.alert('Security token expired. Refresh page and try again.');
            } else {
                window.alert('Network/server error while updating status.');
            }
        }).always(function () {
            $wrap.removeClass('is-saving');
        });
    });
})(jQuery);
</script>
