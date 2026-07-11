<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Sale/challan screens: reuse shared #addCustomerAddressModal without duplicating it on the page
 * (avoids ID clashes when Add Customer loads customers/add inside #myModal).
 */
$addr_country_rows = $this->db->select('name')->order_by('name', 'ASC')->get('country_master')->result_array();
$addr_default_country = isset($Settings->country_name) ? (string) $Settings->country_name : '';
$addr_country_options = '<option value="">Select Country</option>';
if (!empty($addr_country_rows)) {
    foreach ($addr_country_rows as $c_row) {
        $c_name = isset($c_row['name']) ? trim((string) $c_row['name']) : '';
        if ($c_name === '') {
            continue;
        }
        $addr_country_options .= '<option value="' . htmlspecialchars($c_name, ENT_QUOTES, 'UTF-8') . '"' . (($c_name === $addr_default_country) ? ' selected="selected"' : '') . '>' . htmlspecialchars($c_name, ENT_QUOTES, 'UTF-8') . '</option>';
    }
}
?>
<style>
    #addCustomerAddressModal .modal-dialog {
        max-width: 440px;
        width: 92%;
        margin: 30px auto;
    }
    #addCustomerAddressModal .modal-header {
        padding: 10px 12px;
    }
    #addCustomerAddressModal .modal-body {
        padding: 12px 14px;
    }
    #addCustomerAddressModal .modal-footer {
        padding: 8px 12px;
    }
    #addCustomerAddressModal .modal-title {
        font-size: 15px;
    }
    #addCustomerAddressModal .form-group {
        margin-bottom: 8px;
    }
    #addCustomerAddressModal .form-control {
        height: 32px;
        /* padding: 4px 8px; */
        font-size: 13px;
    }
    #add_customer_addr_modal_error {
        font-size: 12px;
        margin-bottom: 8px;
    }
    #sl_billing_address,
    #sl_shipping_address {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        cursor: default;
    }
    #customer-addr-country-options-html {
        display: none !important;
    }
</style>
<input type="hidden" id="customer-addr-country-options-html" value="<?= htmlspecialchars($addr_country_options, ENT_QUOTES, 'UTF-8'); ?>" />
<script type="text/javascript" src="<?= base_url('themes/default/assets/js/customer_add_address_modal.js?v=20260630_2'); ?>"></script>
<script>
(function($) {
    var smaSalesAddressModal = null;
    var smaSalesAddressModalLoading = false;
    var smaSalesAddrDefaultCountry = <?= json_encode($addr_default_country); ?>;

    window.syncSaleAddressFieldTooltip = function($input) {
        if (!$input || !$input.length) {
            return;
        }
        var text = $.trim($input.val() || '');
        $input.attr('data-tip', text);
        if ($input.data('bs.tooltip')) {
            $input.tooltip('destroy');
        }
        if (text) {
            $input.tooltip({
                placement: 'top',
                html: true,
                trigger: 'hover focus',
                container: 'body',
                title: function() {
                    return $(this).attr('data-tip');
                }
            });
        }
    };

    window.syncSaleAddressTooltips = function() {
        syncSaleAddressFieldTooltip($('#sl_billing_address'));
        syncSaleAddressFieldTooltip($('#sl_shipping_address'));
    };

    window.salesNormalizeAddressType = function(addressType) {
        var lower = String(addressType || '').toLowerCase();
        if (lower === 'shipping') {
            return 'Shipping';
        }
        if (lower === 'site') {
            return 'Site';
        }
        return 'Billing';
    };

    window.salesUnlockPageScroll = function() {
        var hasVisibleModal = false;
        $('.modal').each(function() {
            var $m = $(this);
            if ($m.hasClass('in') && $m.is(':visible')) {
                hasVisibleModal = true;
                return false;
            }
        });
        if (hasVisibleModal) {
            return;
        }
        $('.modal.in').removeClass('in');
        $('body').removeClass('modal-open');
        if (document.body) {
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
        if (document.documentElement) {
            document.documentElement.style.removeProperty('overflow');
        }
        $('.modal-backdrop').remove();
    };

    window.salesCleanupModalBackdrops = function() {
        salesUnlockPageScroll();
    };

    window.salesRemoveOwnedAddressModal = function() {
        var $owned = salesGetOwnedModal();
        smaSalesAddressModal = null;
        if (!$owned.length) {
            salesCleanupModalBackdrops();
            return;
        }
        if ($owned.hasClass('in')) {
            $owned.one('hidden.bs.modal.salesOwnedRemove', function() {
                $(this).remove();
                salesCleanupModalBackdrops();
            });
            $owned.modal('hide');
        } else {
            $owned.remove();
            salesCleanupModalBackdrops();
        }
    };

    function salesLockAddressType(type) {
        var $type = $('#addCustomerAddressModal[data-sales-owned="1"] #add_addr_modal_type');
        if (!$type.length) {
            $type = $('body > #addCustomerAddressModal[data-sales-owned="1"] #add_addr_modal_type');
        }
        if (!$type.length) {
            return;
        }
        $type.val(type);
        if ($type.is('select')) {
            $type.prop('disabled', true).hide();
            var $display = $('#add_addr_modal_type_display');
            if (!$display.length) {
                $display = $('<input type="text" id="add_addr_modal_type_display" class="form-control" readonly="readonly" />');
                $type.after($display);
            }
            $display.val(type).show();
        }
    }

    function salesGetOwnedModal() {
        return $('body > #addCustomerAddressModal[data-sales-owned="1"]');
    }

    function salesScrubDuplicateAddressModals() {
        $('#myModal').find('#addCustomerAddressModal').remove();
        $('body > #addCustomerAddressModal').not('[data-sales-owned="1"]').remove();
    }

    function salesInitAddressModalInstance() {
        var $owned = salesGetOwnedModal();
        if (!$owned.length || typeof SmaCustomerAddAddressModal === 'undefined') {
            return null;
        }
        if (!smaSalesAddressModal) {
            smaSalesAddressModal = new SmaCustomerAddAddressModal({
                instanceKey: 'salesAddress',
                modalSelector: 'body > #addCustomerAddressModal[data-sales-owned="1"]',
                getStatesUrl: <?= json_encode(base_url('customers/getstates')); ?>,
                countryOptionsSelector: '#customer-addr-country-options-html',
                defaultCountry: smaSalesAddrDefaultCountry,
                onSave: function() {
                    /* handled by sales-owned save click (async) */
                }
            });
        } else {
            smaSalesAddressModal.$modal = $owned;
        }
        return smaSalesAddressModal;
    }

    window.salesEnsureAddressModalLoaded = function(callback) {
        if (salesGetOwnedModal().length) {
            salesScrubDuplicateAddressModals();
            callback();
            return;
        }
        if (smaSalesAddressModalLoading) {
            $(document).one('salesAddressModalLoaded', callback);
            return;
        }
        smaSalesAddressModalLoading = true;
        $.get(<?= json_encode(site_url('sales/address_add_modal')); ?>, function(html) {
            salesScrubDuplicateAddressModals();
            var $wrap = $('<div>').html(html);
            var $modal = $wrap.find('#addCustomerAddressModal').first();
            if (!$modal.length) {
                $modal = $wrap.filter('#addCustomerAddressModal').first();
            }
            if ($modal.length) {
                $wrap.find('style').appendTo('head');
                $modal.find('.modal-dialog').removeClass('modal-sm');
                $modal.attr('data-sales-owned', '1').appendTo('body');
            }
            smaSalesAddressModalLoading = false;
            $(document).trigger('salesAddressModalLoaded');
            callback();
        }).fail(function() {
            smaSalesAddressModalLoading = false;
            bootbox.alert('Failed to load address form.');
        });
    };

    window.openAddCustomerAddressModalForType = function(addressType) {
        if (typeof getSelectedCustomerIdForAddress !== 'function') {
            return;
        }
        var customer_id = getSelectedCustomerIdForAddress();
        if (!customer_id) {
            bootbox.alert('Please select customer.');
            return;
        }
        var normalizedType = salesNormalizeAddressType(addressType);
        salesEnsureAddressModalLoaded(function() {
            salesScrubDuplicateAddressModals();
            var modal = salesInitAddressModalInstance();
            if (!modal) {
                bootbox.alert('Address form is not available.');
                return;
            }
            SmaCustomerAddAddressModal._active = modal;
            modal.open({ type: normalizedType }, <?= json_encode(lang('add_address')); ?>);
            modal.$modal.one('shown.bs.modal', function() {
                salesLockAddressType(normalizedType);
            });
            salesLockAddressType(normalizedType);
        });
    };

    function salesFinishCloseOwnedAddressModal() {
        var $owned = salesGetOwnedModal();
        if ($owned.length) {
            $owned.remove();
        }
        if (typeof SmaCustomerAddAddressModal !== 'undefined' &&
            SmaCustomerAddAddressModal._active &&
            SmaCustomerAddAddressModal._active.cfg &&
            SmaCustomerAddAddressModal._active.cfg.instanceKey === 'salesAddress') {
            SmaCustomerAddAddressModal._active = null;
        }
        smaSalesAddressModal = null;
        salesUnlockPageScroll();
        setTimeout(salesUnlockPageScroll, 50);
        setTimeout(salesUnlockPageScroll, 300);
    }

    $(document).on('hidden.bs.modal.salesOwnedAddr', 'body > #addCustomerAddressModal[data-sales-owned="1"]', function() {
        salesFinishCloseOwnedAddressModal();
    });

    $(document).on('click.salesOwnedAddrSave', '#btnSaveCustomerAddressModal', function(e) {
        var $owned = salesGetOwnedModal();
        if (!$owned.length || !$owned.hasClass('in') || !smaSalesAddressModal || SmaCustomerAddAddressModal._active !== smaSalesAddressModal) {
            return;
        }
        e.preventDefault();
        e.stopImmediatePropagation();

        if (typeof getSelectedCustomerIdForAddress !== 'function') {
            return;
        }
        var customer_id = getSelectedCustomerIdForAddress();
        if (!customer_id) {
            bootbox.alert('Please select customer.');
            return;
        }

        var data = smaSalesAddressModal.readFields();
        var err = smaSalesAddressModal.validateFields(data);
        var $err = $('#add_customer_addr_modal_error');
        $err.hide().text('');
        if (err) {
            $err.text(err.msg).show();
            return;
        }

        $.ajax({
            url: <?= json_encode(site_url('customers/save_customer_address')); ?>,
            type: 'POST',
            dataType: 'json',
            data: { company_id: customer_id, address: data },
            success: function(resp) {
                if (!(resp && resp.status && resp.address_id)) {
                    bootbox.alert((resp && resp.message) ? resp.message : 'Failed to save address.');
                    return;
                }
                var lowerType = String(data.type || '').toLowerCase();
                var normalizedType = (lowerType === 'shipping') ? 'shipping' : ((lowerType === 'site') ? 'site' : 'billing');
                if (typeof setAddressFields === 'function' && typeof buildAddressLabelFromFormData === 'function') {
                    setAddressFields(normalizedType, {
                        id: resp.address_id,
                        label: buildAddressLabelFromFormData(data)
                    });
                }
                var $owned = salesGetOwnedModal();
                if ($owned.length) {
                    $owned.one('hidden.bs.modal.salesAfterSave', salesFinishCloseOwnedAddressModal);
                    smaSalesAddressModal.close();
                } else {
                    salesFinishCloseOwnedAddressModal();
                }
                setTimeout(salesUnlockPageScroll, 500);
            },
            error: function() {
                bootbox.alert('Failed to save address.');
            }
        });
    });

    $(document).on('click.salesOwnedAddrCancel', '#btnCancelCustomerAddressModal, #btnCloseCustomerAddressModal', function(e) {
        var $owned = salesGetOwnedModal();
        if (!$owned.length || !$owned.hasClass('in') || !smaSalesAddressModal || SmaCustomerAddAddressModal._active !== smaSalesAddressModal) {
            return;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        $owned.one('hidden.bs.modal.salesAfterCancel', salesFinishCloseOwnedAddressModal);
        smaSalesAddressModal.close(e);
    });

    $(document).on('click', '#add-customer', function() {
        salesRemoveOwnedAddressModal();
    });
    $(document).on('show.bs.modal', '#myModal', function() {
        salesRemoveOwnedAddressModal();
    });

    $(document).ready(function() {
        if (typeof window.setAddressFields === 'function' && !window.setAddressFields._saleAddressTooltip) {
            var origSetAddressFields = window.setAddressFields;
            window.setAddressFields = function(type, address) {
                origSetAddressFields(type, address);
                if (type === 'shipping') {
                    syncSaleAddressFieldTooltip($('#sl_shipping_address'));
                } else if (type === 'billing') {
                    syncSaleAddressFieldTooltip($('#sl_billing_address'));
                }
                salesUnlockPageScroll();
            };
            window.setAddressFields._saleAddressTooltip = true;
        }

        if (typeof window.getCustomerAddresses === 'function' && !window.getCustomerAddresses._saleAddressTooltip) {
            var origGetCustomerAddresses = window.getCustomerAddresses;
            window.getCustomerAddresses = function(customer_id) {
                origGetCustomerAddresses(customer_id);
                syncSaleAddressTooltips();
            };
            window.getCustomerAddresses._saleAddressTooltip = true;
        }

        syncSaleAddressTooltips();
        $(window).on('load', syncSaleAddressTooltips);
    });
})(jQuery);
</script>
