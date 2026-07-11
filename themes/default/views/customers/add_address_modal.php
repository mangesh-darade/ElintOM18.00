<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reusable Add/Edit customer address popup.
 * Include from any view: $this->load->view($this->theme . 'customers/add_address_modal', $data);
 *
 * Optional $data:
 *   show_edit_id (bool) — hidden id field for CRM edit mode
 *   modal_title (string) — default lang('add_address')
 */
if (defined('SMA_CUSTOMER_ADDRESS_MODAL_RENDERED')) {
    return;
}
define('SMA_CUSTOMER_ADDRESS_MODAL_RENDERED', true);

$show_edit_id = !empty($show_edit_id);
$modal_title = isset($modal_title) ? $modal_title : lang('add_address');
?>
<style>
    #addCustomerAddressModal {
        z-index: 10600 !important;
    }
    .select2-drop, .select2-dropdown, .select2-drop-mask {
        z-index: 10650 !important;
    }

    #addCustomerAddressModal .modal-dialog {
        max-width: 440px;
        width: 92%;
        margin: 30px auto;
    }
    #addCustomerAddressModal .modal-header {
        padding: 10px 12px;
        background-color: #f0f9ff;
        border-bottom: 1px solid #bae6fd;
    }
    #addCustomerAddressModal .modal-body {
        padding: 12px 14px;
    }
    #addCustomerAddressModal .modal-footer {
        padding: 8px 12px;
        border-top: 1px solid #e5e7eb;
    }
    #addCustomerAddressModal .modal-title {
        font-size: 15px;
        color: #0369a1;
        font-weight: 600;
    }
    #addCustomerAddressModal .form-group {
        margin-bottom: 8px;
    }
    #addCustomerAddressModal .form-control {
        height: 32px;
        /* padding: 4px 8px; */
        font-size: 13px;
    }
    #addCustomerAddressModal .form-control:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
    #add_customer_addr_modal_error {
        font-size: 12px;
        margin-bottom: 8px;
    }
    #btnSaveCustomerAddressModal {
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #fff !important;
    }
    #btnSaveCustomerAddressModal:hover,
    #btnSaveCustomerAddressModal:focus,
    #btnSaveCustomerAddressModal:active {
        background-color: #0369a1 !important;
        border-color: #0369a1 !important;
    }
</style>

<div class="modal fade" id="addCustomerAddressModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerAddressModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" id="btnCloseCustomerAddressModal" aria-label="<?= lang('close'); ?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addCustomerAddressModalLabel"><?= htmlspecialchars((string) $modal_title, ENT_QUOTES, 'UTF-8'); ?></h4>
            </div>
            <div class="modal-body">
                <div id="add_customer_addr_modal_error" class="text-danger" style="display:none;"></div>
                <?php if ($show_edit_id): ?>
                    <input type="hidden" id="add_addr_modal_id" value="" />
                <?php endif; ?>
                <div class="form-group">
                    <label for="add_addr_modal_type"><?= lang('type'); ?></label>
                    <select id="add_addr_modal_type" class="form-control">
                        <option value=""><?= lang('select'); ?></option>
                        <option value="Shipping">Shipping</option>
                        <option value="Site">Site</option>
                        <option value="Billing" selected="selected">Billing</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_addr_modal_address_name">Address Name</label>
                    <input type="text" id="add_addr_modal_address_name" class="form-control" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="add_addr_modal_line1"><?= lang('Address Line1'); ?></label>
                    <input type="text" id="add_addr_modal_line1" class="form-control" autocomplete="street-address" />
                </div>
                <div class="form-group">
                    <label for="add_addr_modal_country"><?= lang('country'); ?></label>
                    <select id="add_addr_modal_country" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label for="add_addr_modal_state"><?= lang('state'); ?></label>
                    <select id="add_addr_modal_state" class="form-control">
                        <option value="">--Select State--</option>
                    </select>
                    <input type="hidden" id="add_addr_modal_state_code" value="" />
                </div>
                <div class="form-group">
                    <label for="add_addr_modal_city"><?= lang('city'); ?></label>
                    <input type="text" id="add_addr_modal_city" class="form-control" />
                </div>
                <div class="form-group">
                    <label for="add_addr_modal_postal"><?= lang('postal_code'); ?></label>
                    <input type="text" id="add_addr_modal_postal" class="form-control" maxlength="6" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm" id="btnCancelCustomerAddressModal"><?= lang('Cancel'); ?></button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveCustomerAddressModal"><?= lang('Save'); ?></button>
            </div>
        </div>
    </div>
</div>
