<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('addresses') . " (" . $company->name . ")"; ?>
            </h4>
        </div>

        <style>
            .modal-header {
                background-color: #428bca;
                border-bottom: 1px solid #357ebd;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }

            .modal-title {
                color: #ffffff !important;
                font-weight: 700;
            }

            .modal-header .close {
                color: #ffffff !important;
                opacity: 0.8;
                text-shadow: none;
            }

            .modal-header .close:hover {
                color: #ffffff !important;
                opacity: 1;
            }

            .address-sections-wrapper {
                display: flex;
                flex-direction: column;
                gap: 12px;
                padding: 10px 0;
            }

            .address-section {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            .address-section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                background-color: #fafafa;
                cursor: pointer;
                user-select: none;
                transition: background-color 0.2s ease;
                border-bottom: 1px solid transparent;
                margin-bottom: 0;
            }

            .address-section.expanded .address-section-header {
                border-bottom-color: #e2e8f0;
            }

            .address-section-header:hover {
                background-color: #f4f4f5;
            }

            .address-header-title {
                font-size: 14px;
                font-weight: 700;
                color: #0284c7; /* Sky blue */
                display: flex;
                align-items: center;
                gap: 8px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .address-section-header i.fa:first-child {
                font-size: 15px;
                color: #0284c7;
                width: 18px;
                text-align: center;
            }

            .address-chevron-icon {
                font-size: 14px;
                color: #64748b;
                transition: transform 0.25s ease;
            }

            .address-section.expanded .address-chevron-icon {
                transform: rotate(180deg);
            }

            .address-section-content {
                padding: 16px 20px;
            }

            .address-cards-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 16px;
            }

            .address-card {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                padding: 16px;
                position: relative;
                transition: all 0.2s ease;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 140px;
            }

            .address-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #cbd5e1;
            }

            .address-card.is-default {
                border: 2px solid #0284c7;
                background: #f8fafc;
            }

            .address-card-header {
                display: flex;
                flex-direction: column;
                gap: 4px;
                margin-bottom: 12px;
            }

            .address-card-title {
                font-weight: 600;
                font-size: 14px;
                color: #1f2937;
                padding-right: 48px;
                word-break: break-word;
            }

            .address-badge-default {
                align-self: flex-start;
                background-color: #0284c7;
                color: white;
                font-size: 9px;
                font-weight: 700;
                padding: 2px 8px;
                border-radius: 9999px;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .address-card-details {
                font-size: 13px;
                color: #4b5563;
                line-height: 1.5;
                margin-bottom: 12px;
                word-break: break-word;
            }

            .address-card-phone {
                font-size: 12px;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: auto;
            }

            .address-card-actions {
                position: absolute;
                top: 12px;
                right: 12px;
                display: flex;
                gap: 8px;
            }

            .address-card-btn {
                color: #9ca3af;
                transition: color 0.15s ease;
                font-size: 15px;
            }

            .address-card-btn:hover {
                color: #4b5563;
            }

            .address-card-btn.delete:hover {
                color: #ef4444;
            }

            .address-empty-state {
                font-size: 13px;
                color: #9ca3af;
                font-style: italic;
                padding: 8px 0;
            }
        </style>

        <div class="modal-body">
            <?php
            $billing_addresses = array();
            $shipping_addresses = array();
            $site_addresses = array();

            if (!empty($addresses)) {
                foreach ($addresses as $address) {
                    $db_type = isset($address->type) ? trim((string) $address->type) : '';
                    if (strcasecmp($db_type, 'Shipping') === 0) {
                        $shipping_addresses[] = $address;
                    } elseif (strcasecmp($db_type, 'Site') === 0) {
                        $site_addresses[] = $address;
                    } else {
                        $billing_addresses[] = $address;
                    }
                }
            }
            ?>

            <div class="address-sections-wrapper">
                <!-- Billing Addresses (Expanded by default) -->
                <div class="address-section expanded">
                    <div class="address-section-header billing">
                        <span class="address-header-title">
                            <i class="fa fa-file-text-o"></i> Billing Addresses
                        </span>
                        <i class="fa fa-chevron-down address-chevron-icon"></i>
                    </div>
                    <div class="address-section-content" style="display: block;">
                        <?php if (!empty($billing_addresses)): ?>
                            <div class="address-cards-grid">
                                <?php foreach ($billing_addresses as $address): ?>
                                    <div class="address-card <?= $address->is_default ? 'is-default' : ''; ?>">
                                        <div class="address-card-actions">
                                            <a href="<?= site_url('customers/edit_address/' . $address->id); ?>"
                                                class="tip address-card-btn" title="<?= lang("edit_address"); ?>"
                                                data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                            <a href="#" class="tip po address-card-btn delete"
                                                title="<?= $this->lang->line("delete_address"); ?>"
                                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure'); ?></p><a class='btn btn-danger' href='<?= site_url('customers/delete_address/' . $address->id); ?>'><?= lang('yes'); ?></a> <button class='btn po-close'><?= lang('no'); ?></button></div>"
                                                rel="popover"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                        <div class="address-card-header">
                                            <div class="address-card-title">
                                                <?= htmlspecialchars($address->address_name ? $address->address_name : $company->name, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if ($address->is_default): ?>
                                                <span class="address-badge-default">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="address-card-details">
                                            <?= htmlspecialchars($address->line1, ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php if ($address->line2): ?>
                                                <?= htmlspecialchars($address->line2, ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($address->city, ENT_QUOTES, 'UTF-8') . ($address->postal_code ? ' - ' . htmlspecialchars($address->postal_code, ENT_QUOTES, 'UTF-8') : ''); ?><br>
                                            <?= htmlspecialchars($address->state, ENT_QUOTES, 'UTF-8') . ($address->country ? ', ' . htmlspecialchars($address->country, ENT_QUOTES, 'UTF-8') : ''); ?>
                                        </div>
                                        <?php if ($address->phone): ?>
                                            <div class="address-card-phone">
                                                <i class="fa fa-phone"></i>
                                                <?= htmlspecialchars($address->phone, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="address-empty-state">No billing addresses defined.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shipping Addresses (Collapsed by default) -->
                <div class="address-section">
                    <div class="address-section-header shipping">
                        <span class="address-header-title">
                            <i class="fa fa-truck"></i> Shipping Addresses
                        </span>
                        <i class="fa fa-chevron-down address-chevron-icon"></i>
                    </div>
                    <div class="address-section-content" style="display: none;">
                        <?php if (!empty($shipping_addresses)): ?>
                            <div class="address-cards-grid">
                                <?php foreach ($shipping_addresses as $address): ?>
                                    <div class="address-card <?= $address->is_default ? 'is-default' : ''; ?>">
                                        <div class="address-card-actions">
                                            <a href="<?= site_url('customers/edit_address/' . $address->id); ?>"
                                                class="tip address-card-btn" title="<?= lang("edit_address"); ?>"
                                                data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                            <a href="#" class="tip po address-card-btn delete"
                                                title="<?= $this->lang->line("delete_address"); ?>"
                                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure'); ?></p><a class='btn btn-danger' href='<?= site_url('customers/delete_address/' . $address->id); ?>'><?= lang('yes'); ?></a> <button class='btn po-close'><?= lang('no'); ?></button></div>"
                                                rel="popover"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                        <div class="address-card-header">
                                            <div class="address-card-title">
                                                <?= htmlspecialchars($address->address_name ? $address->address_name : $company->name, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if ($address->is_default): ?>
                                                <span class="address-badge-default">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="address-card-details">
                                            <?= htmlspecialchars($address->line1, ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php if ($address->line2): ?>
                                                <?= htmlspecialchars($address->line2, ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($address->city, ENT_QUOTES, 'UTF-8') . ($address->postal_code ? ' - ' . htmlspecialchars($address->postal_code, ENT_QUOTES, 'UTF-8') : ''); ?><br>
                                            <?= htmlspecialchars($address->state, ENT_QUOTES, 'UTF-8') . ($address->country ? ', ' . htmlspecialchars($address->country, ENT_QUOTES, 'UTF-8') : ''); ?>
                                        </div>
                                        <?php if ($address->phone): ?>
                                            <div class="address-card-phone">
                                                <i class="fa fa-phone"></i>
                                                <?= htmlspecialchars($address->phone, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="address-empty-state">No shipping addresses defined.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Site Addresses (Collapsed by default) -->
                <div class="address-section">
                    <div class="address-section-header site">
                        <span class="address-header-title">
                            <i class="fa fa-map-marker"></i> Site Addresses
                        </span>
                        <i class="fa fa-chevron-down address-chevron-icon"></i>
                    </div>
                    <div class="address-section-content" style="display: none;">
                        <?php if (!empty($site_addresses)): ?>
                            <div class="address-cards-grid">
                                <?php foreach ($site_addresses as $address): ?>
                                    <div class="address-card <?= $address->is_default ? 'is-default' : ''; ?>">
                                        <div class="address-card-actions">
                                            <a href="<?= site_url('customers/edit_address/' . $address->id); ?>"
                                                class="tip address-card-btn" title="<?= lang("edit_address"); ?>"
                                                data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                            <a href="#" class="tip po address-card-btn delete"
                                                title="<?= $this->lang->line("delete_address"); ?>"
                                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure'); ?></p><a class='btn btn-danger' href='<?= site_url('customers/delete_address/' . $address->id); ?>'><?= lang('yes'); ?></a> <button class='btn po-close'><?= lang('no'); ?></button></div>"
                                                rel="popover"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                        <div class="address-card-header">
                                            <div class="address-card-title">
                                                <?= htmlspecialchars($address->address_name ? $address->address_name : $company->name, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if ($address->is_default): ?>
                                                <span class="address-badge-default">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="address-card-details">
                                            <?= htmlspecialchars($address->line1, ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php if ($address->line2): ?>
                                                <?= htmlspecialchars($address->line2, ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($address->city, ENT_QUOTES, 'UTF-8') . ($address->postal_code ? ' - ' . htmlspecialchars($address->postal_code, ENT_QUOTES, 'UTF-8') : ''); ?><br>
                                            <?= htmlspecialchars($address->state, ENT_QUOTES, 'UTF-8') . ($address->country ? ', ' . htmlspecialchars($address->country, ENT_QUOTES, 'UTF-8') : ''); ?>
                                        </div>
                                        <?php if ($address->phone): ?>
                                            <div class="address-card-phone">
                                                <i class="fa fa-phone"></i>
                                                <?= htmlspecialchars($address->phone, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="address-empty-state">No site addresses defined.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <a href="<?= site_url('customers/add_address/' . $company->id); ?>" class="btn btn-primary pull-left"
                data-toggle='modal' data-target='#myModal2'><?= lang('add_address'); ?></a>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
        </div>
    </div>
    <script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.tip').tooltip();

            // Match modal-header background and border color with the primary button
            var $btnPrimary = $('.modal-footer .btn-primary');
            if ($btnPrimary.length) {
                var btnBg = $btnPrimary.css('background-color');
                var btnBorder = $btnPrimary.css('border-top-color') || $btnPrimary.css('border-color');
                if (btnBg) {
                    $('.modal-header').css({
                        'background-color': btnBg,
                        'border-bottom-color': btnBorder
                    });
                }
            }

            $('.address-section-header').on('click', function (e) {
                var $header = $(this);
                var $section = $header.closest('.address-section');
                var $content = $section.find('.address-section-content');

                if ($section.hasClass('expanded')) {
                    $content.slideUp(250, function () {
                        $section.removeClass('expanded');
                    });
                } else {
                    $section.addClass('expanded');
                    $content.hide().slideDown(250);
                }
            });
        });
    </script>
</div>