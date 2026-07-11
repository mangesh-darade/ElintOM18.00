<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .address-modal-container.modal-dialog {
        max-width: 440px;
        width: 92%;
        margin: 30px auto;
    }

    .address-modal-container .modal-header {
        padding: 10px 12px;
        background-color: #f0f9ff !important;
        border-bottom: 1px solid #bae6fd !important;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .address-modal-container .modal-header .close {
        color: #0369a1 !important;
        opacity: 0.8;
        text-shadow: none;
        margin-top: 2px;
    }

    .address-modal-container .modal-header .close:hover {
        opacity: 1;
    }

    .address-modal-container .modal-title {
        font-size: 15px;
        color: #0369a1 !important;
        font-weight: 600;
    }

    .address-modal-container .modal-body {
        padding: 12px 14px;
    }

    .address-modal-container .modal-footer {
        padding: 8px 12px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .address-modal-container .form-group {
        margin-bottom: 8px;
    }

    .address-modal-container .form-group label {
        font-weight: 600;
        font-size: 13px;
        color: #374151;
        margin-bottom: 4px;
    }

    .address-modal-container .form-control {
        height: 32px;
        font-size: 13px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
    }

    .address-modal-container .form-control:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    .address-modal-container .btn-save-address {
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #fff !important;
        padding: 5px 12px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 4px;
    }

    .address-modal-container .btn-save-address:hover,
    .address-modal-container .btn-save-address:focus {
        background-color: #0369a1 !important;
        border-color: #0369a1 !important;
    }

    .address-modal-container .btn-cancel-address {
        background-color: #fff !important;
        border: 1px solid #d1d5db !important;
        color: #374151 !important;
        padding: 5px 12px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 4px;
    }

    .address-modal-container .btn-cancel-address:hover {
        background-color: #f9fafb !important;
        border-color: #cbd5e1 !important;
    }
    .address-modal-container .select2-container .select2-choice {
        height: 32px;
        line-height: 32px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
        background-image: none;
        background-color: #fff;
    }
    .address-modal-container .select2-container .select2-choice .select2-arrow {
        border-left: none;
        background: transparent;
    }
    .address-modal-container .select2-container .select2-choice .select2-arrow b {
        background-position: 0 3px;
    }
    .address-select2-dropdown .select2-results {
        max-height: 150px !important;
    }
</style>

<div class="modal-dialog address-modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                    class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_address'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'edit-address-form');
        echo form_open("customers/edit_address/" . $address->id, $attrib); ?>
        <div class="modal-body">
            <div class="form-group">
                <label for="addr-type"><?= lang('type'); ?></label>
                <select id="addr-type" name="type" class="form-control select2" style="width:100%;" required="required">
                    <option value="Billing" <?= ($address->type == 'Billing') ? 'selected="selected"' : ''; ?>>Billing
                    </option>
                    <option value="Shipping" <?= ($address->type == 'Shipping') ? 'selected="selected"' : ''; ?>>Shipping
                    </option>
                    <option value="Site" <?= ($address->type == 'Site') ? 'selected="selected"' : ''; ?>>Site</option>
                </select>
            </div>
            <div class="form-group">
                <label for="addr-address_name">Address Name</label>
                <input type="text" id="addr-address_name" name="address_name"
                    value="<?= htmlspecialchars($address->address_name ? $address->address_name : $company->name, ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control" required="required" autocomplete="off" />
            </div>
            <div class="form-group">
                <label for="addr-line1"><?= lang('Address Line1'); ?></label>
                <input type="text" id="addr-line1" name="line1"
                    value="<?= htmlspecialchars($address->line1, ENT_QUOTES, 'UTF-8'); ?>" class="form-control"
                    required="required" autocomplete="street-address" />
            </div>
            <div class="form-group">
                <label for="addr-country"><?= lang('country'); ?></label>
                <select id="addr-country" name="country" class="form-control select2" style="width:100%;">
                    <option value="">Select Country</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?= $c->name; ?>" <?= (strcasecmp($c->name, $address->country) === 0) ? 'selected="selected"' : ''; ?>><?= $c->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="addr-state"><?= lang('state'); ?></label>
                <select id="addr-state" name="state" class="form-control select2" style="width:100%;"
                    required="required">
                    <option value="">--Select State--</option>
                    <?php if (!empty($states)): ?>
                        <?php foreach ($states as $s): ?>
                            <?php
                            $state_val = $s->name . '~' . $s->code;
                            $is_selected = (strcasecmp($s->name, $address->state) === 0 || strcasecmp($s->code, $address->state_code) === 0);
                            ?>
                            <option value="<?= $state_val; ?>" <?= $is_selected ? 'selected="selected"' : ''; ?>><?= $s->name; ?>
                                (<?= $s->code; ?>)</option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <option value="other" <?= (strcasecmp($address->state, 'other') === 0) ? 'selected="selected"' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="addr-city"><?= lang('city'); ?></label>
                <input type="text" id="addr-city" name="city"
                    value="<?= htmlspecialchars($address->city, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
            </div>
            <div class="form-group">
                <label for="addr-postal_code"><?= lang('postal_code'); ?></label>
                <input type="text" id="addr-postal_code" name="postal_code"
                    value="<?= htmlspecialchars($address->postal_code, ENT_QUOTES, 'UTF-8'); ?>" class="form-control"
                    maxlength="6" />
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel-address" data-dismiss="modal">Cancel</button>
            <button type="submit" name="edit_address" class="btn btn-save-address">Save</button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        function refreshSelect2($el) {
            if (typeof $.fn.select2 === 'function') {
                try {
                    $el.select2('destroy');
                } catch (e) { }
                $el.select2({
                    minimumResultsForSearch: 7,
                    width: '100%',
                    dropdownCssClass: 'address-select2-dropdown'
                });
            }
        }

        refreshSelect2($('.address-modal-container select.select2'));

        $('#addr-country').change(function () {
            var country = $(this).val();
            var $state = $('#addr-state');
            if (!country || country === 'other') {
                $state.html('<option value="other">Other</option>');
                refreshSelect2($state);
                return;
            }
            $state.html('<option value="">--Select State--</option>');
            refreshSelect2($state);

            var statesUrl = '';
            if (typeof site !== 'undefined' && site.base_url) {
                statesUrl = site.base_url + (site.base_url.slice(-1) === '/' ? '' : '/') + 'customers/getstates';
            } else {
                statesUrl = '<?= site_url('customers/getstates'); ?>';
            }

            $.ajax({
                url: statesUrl,
                data: { country: country },
                dataType: 'json',
                success: function (data) {
                    if (data.status === 'success') {
                        $state.html(data.data);
                    } else {
                        $state.html('<option value="">--Select State--</option><option value="other">Other</option>');
                    }
                    refreshSelect2($state);
                },
                error: function () {
                    $state.html('<option value="">--Select State--</option><option value="other">Other</option>');
                    refreshSelect2($state);
                }
            });
        });

        // Trigger change to load states only if a country is selected but no state is selected yet
        if ($('#addr-country').val() && !$('#addr-state').val()) {
            $('#addr-country').trigger('change');
        }
    });
</script>
