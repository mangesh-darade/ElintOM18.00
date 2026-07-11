<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 

<style>
    .modal.fade {
        -webkit-transition: opacity .3s linear, top .3s ease-out;
        -moz-transition: opacity .3s linear, top .3s ease-out;
        -ms-transition: opacity .3s linear, top .3s ease-out;
        -o-transition: opacity .3s linear, top .3s ease-out;
        transition: opacity .3s linear, top .3s ease-out;
        top: -3%;
    }

    .modal-header .btnGrp{
        position: absolute;
        top:18px;
        right: 10px;
    } 
    .form-control {
        height: 31px;
    }
    /* Keep customer modal grid aligned even when POS theme overrides .row/.col globally. */
    #add-customer-form .row {
        margin-left: -15px !important;
        margin-right: -15px !important;
    }
    #add-customer-form [class*="col-"] {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    #customerAddressesTable input.form-control,
    #customerAddressesTable select.form-control {
        height: 28px;
        padding: 2px 6px;
        font-size: 12px;
        min-width: 0;
    }
    .customer-addr-table-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }
    .customer-addr-table-header h4 {
        margin: 0;
    }
    .customer-addr-table-header .btn-add-addr-row {
        padding: 2px 8px;
        line-height: 1.2;
    }
    #customerAddressesTable .col-addr-type { width: 8%; }
    #customerAddressesTable .col-addr-name { width: 14%; }
    #customerAddressesTable .col-addr-line { width: 18%; }
    #customerAddressesTable .col-addr-country { width: 10%; }
    #customerAddressesTable .col-addr-state { width: 16%; }
    #customerAddressesTable .col-addr-city { width: 14%; }
    #customerAddressesTable .col-addr-postal { width: 9%; }
    #customerAddressesTable .col-addr-actions { width: 8%; }
    #customerAddressesTable .customer-addr-actions {
        white-space: nowrap;
        text-align: center;
    }
    #customerAddressesTable .customer-addr-actions .btn {
        margin: 0;
    }
    #customerAddressesTable td.has-error .form-control {
        border-color: #a94442;
    }
    #customer_addr_form_error {
        font-size: 12px;
        line-height: 1.3;
    }
    .customer-addr-table-wrap {
        width: 100%;
        margin-top: 8px;
    }
    .customer-addr-table-wrap .table-controls,
    #customerAddressesTable {
        width: 100% !important;
        max-width: 100%;
    }
    tr.customer-addr-row.customer-addr-row-locked td {
        vertical-align: middle !important;
    }
    tr.customer-addr-row.customer-addr-row-locked .customer-addr-display {
        display: block;
        padding: 4px 2px;
        font-size: 12px;
        line-height: 1.35;
        word-break: break-word;
    }
    /* Add Customer in #myModal: scroll body only; header + Add button always visible */
    #myModal.modal.in {
        overflow-y: auto !important;
    }
    #myModal > .modal-dialog {
        margin: 10px auto;
    }
    #myModal .modal-dialog.modal-lg,
    .sma-add-customer-modal .modal-dialog.modal-lg {
        width: 96%;
        max-width: 1100px;
    }
    #myModal .modal-dialog.modal-lg .modal-content,
    .sma-add-customer-modal .modal-content {
        max-height: calc(100vh - 20px);
        display: flex;
        flex-direction: column;
    }
    #myModal .modal-dialog.modal-lg .modal-header,
    .sma-add-customer-modal .modal-header {
        flex-shrink: 0;
    }
    #myModal #add-customer-form,
    .sma-add-customer-modal #add-customer-form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        margin-bottom: 0;
        overflow: hidden;
    }
    #myModal #add-customer-form > .modal-body,
    .sma-add-customer-modal #add-customer-form > .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        max-height: none;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    #myModal #add-customer-form > .modal-footer,
    .sma-add-customer-modal #add-customer-form > .modal-footer {
        flex-shrink: 0;
        display: block;
        visibility: visible;
        background: #fff;
        border-top: 1px solid #e5e5e5;
        padding: 12px 15px;
        text-align: right;
    }
    #myModal #add-customer-form > .modal-footer #add_customer,
    .sma-add-customer-modal #add-customer-form > .modal-footer #add_customer {
        display: inline-block;
        min-width: 120px;
    }
</style>
<div class="container sma-add-customer-modal">				
    <!--<div class="mymodal" id="modal-1" role="dailog">-->
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
            </div>
            <?php
            $country_phone_meta = [];
            foreach ($country as $c) {
                $dig = 10;
                if (!empty($c->phone_digits) && is_numeric($c->phone_digits)) {
                    $dig = (int)$c->phone_digits;
                } elseif (stripos($c->name, 'United Arab Emirates') !== false || strcasecmp($c->name, 'UAE') === 0) {
                    $dig = 9;
                }
                $country_phone_meta[] = [
                    'name' => $c->name,
                    'phone_digits' => $dig,
                    'tax_label' => !empty($c->TaxNumberLabelText) ? $c->TaxNumberLabelText : 'GSTIN (GST #)',
                    'postal_required' => (!empty($c->postal_code) && (int)$c->postal_code === 1) ? 1 : 0,
                ];
            }
            $auto_customer_number_on = !empty($auto_customer_number);
            $auto_phone_value = !empty($auto_customer_phone) ? $auto_customer_phone : '';
            $biller_country_for_phone = !empty($biller->country) ? trim((string) $biller->country) : '';
            $biller_phone_digits = 10;
            if ($biller_country_for_phone !== '') {
                foreach ($country as $c_row) {
                    if (strcasecmp(trim((string) $c_row->name), $biller_country_for_phone) === 0) {
                        if (!empty($c_row->phone_digits) && is_numeric($c_row->phone_digits)) {
                            $biller_phone_digits = (int) $c_row->phone_digits;
                        } elseif (stripos($c_row->name, 'United Arab Emirates') !== false || strcasecmp($c_row->name, 'UAE') === 0) {
                            $biller_phone_digits = 9;
                        }
                        break;
                    }
                }
            }
            $attrib = [
                'role' => 'form',
                'id' => 'add-customer-form',
                'class' => 'sma-add-customer-form',
            ];
            if ($auto_customer_number_on) {
                $attrib['data-auto-customer-number'] = '1';
                $attrib['data-biller-country'] = htmlspecialchars($biller_country_for_phone, ENT_QUOTES, 'UTF-8');
                $attrib['data-biller-phone-digits'] = (string) (int) $biller_phone_digits;
            } else {
                $attrib['data-biller-country'] = htmlspecialchars($biller_country_for_phone, ENT_QUOTES, 'UTF-8');
                $attrib['data-biller-phone-digits'] = (string) (int) $biller_phone_digits;
            }
            echo form_open_multipart("customers/add", $attrib);
            $redirect_url = $this->input->post('redirect_url') ?: $this->input->get('redirect_url');
            $source_module = $this->input->post('source_module') ?: $this->input->get('source_module');
            $requested_biller_id = $this->input->post('biller_id') ?: $this->input->get('biller_id');
            $selected_country_name = isset($_POST['country']) ? $_POST['country'] : $biller->country;
            $biller_state_value = (isset($_POST['state']) ? $_POST['state'] : $biller->state . '~' . $biller->state_code);
            $biller_state_parts = explode('~', $biller_state_value);
            $biller_state_name = isset($biller_state_parts[0]) ? trim($biller_state_parts[0]) : '';
            $addr_country_options = '';
            foreach ($country as $country_value) {
                $addr_country_options .= '<option value="' . htmlspecialchars($country_value->name, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($country_value->name, ENT_QUOTES, 'UTF-8') . '</option>';
            }
            $addr_country_options .= '<option value="other">Other</option>';
            $initial_tax_label = 'GSTIN (GST #)';
            if (!empty($country) && !empty($selected_country_name)) {
                foreach ($country as $country_row) {
                    if (isset($country_row->name) && trim((string)$country_row->name) === trim((string)$selected_country_name)) {
                        if (!empty($country_row->TaxNumberLabelText)) {
                            $initial_tax_label = $country_row->TaxNumberLabelText;
                        }
                        break;
                    }
                }
            }
            ?>
            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars((string)$redirect_url, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (!empty($source_module)): ?>
            <input type="hidden" name="source_module" value="<?= htmlspecialchars((string)$source_module, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <?php if (!empty($requested_biller_id)): ?>
            <input type="hidden" name="biller_id" value="<?= (int)$requested_biller_id; ?>">
            <?php endif; ?>
            <textarea id="add-customer-country-meta-json" readonly tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;overflow:hidden;"><?= htmlspecialchars(json_encode($country_phone_meta), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group person">
                            <?= lang("Name*", "Name"); ?>
                            <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" type="text" type="text" id="text1" ondrop="return false;" onpaste="return false;"'); ?>
                            <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("phone", "phone"); ?>
                            <?php if ($auto_customer_number_on): ?>
                            <input type="hidden" name="auto_phone_original" id="auto_phone_original" value="<?= htmlspecialchars($auto_phone_value, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="is_system_generated" id="is_system_generated" value="TRUE">
                            <input type="tel" name="phone" class="form-control" id="phone" required="required"
                                value="<?= htmlspecialchars($auto_phone_value, ENT_QUOTES, 'UTF-8'); ?>"
                                maxlength="10" autocomplete="off"
                                onkeypress="return IsNumeric(event,this)" ondrop="return false" onpaste="return false">
                            <?php else: ?>
                            <input type="tel" name="phone" class="form-control" id="phone" required="required"
                                onkeypress="return IsNumeric(event,this)" ondrop="return false" onpaste="return false">
                            <?php endif; ?>
                            <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                            <span id="phone_error" style="color:#a94442; display: none;font-size:11px; margin-top:4px;"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                            <?php
                            foreach ($customer_groups as $customer_group) {
                                $cgs[$customer_group->id] = $customer_group->name;
                            }
                            echo form_dropdown('customer_group', $cgs, $Settings->customer_group, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                            <?php
                            $pgs[''] = lang('select') . ' ' . lang('price_group');
                            foreach ($price_groups as $price_group) {
                                $pgs[$price_group->id] = $price_group->name;
                            }
                            echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control select" id="price_group" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="is_internal_customer"><?php echo lang('Is_Internal_Customer'); ?></label>
                            <?php
                            $internal_options = array('no' => 'No', 'yes' => 'Yes');
                            echo form_dropdown('is_internal_customer', $internal_options, 'no', 'class="form-control select" id="is_internal_customer" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                </div>        
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group company">
                            <?= lang("company", "company"); ?> 
                        <?php echo form_input('company', '', 'class="form-control tip" id="company"'); ?>

                        </div>
                        <!-- <div class="form-group">
                            <?= lang("address", "address"); ?>
                            <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                        </div> -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("vat_no", "vat_no"); ?>
                                    <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("gstn_no", "gstn_no"); ?> (GST Number)
                                        <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no" minlength="15" maxlength="15"  onchange="return validateGstin();"'); ?>
                                </div>
                            </div> -->
                            <div class="col-md-6 gstin_div">
                                <div class="form-group">
                                    <label for="gstn_no" id="gstn_label"><?= htmlspecialchars((string)$initial_tax_label, ENT_QUOTES, 'UTF-8'); ?></label>
                                    <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no" onchange="return validateGstinno();"'); ?>
                                </div>
                            </div>
                        </div>                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("email_address", "email_address"); ?>
                                    <input type="email" name="email" class="form-control" id="email_address" title="<?= htmlspecialchars(lang('email_address'), ENT_QUOTES, 'UTF-8'); ?>" />
                                    <span id="email_error" style="color:#a94442; display: none;font-size:11px;">Please enter a valid email address</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Pan_Number", "pan_card"); ?>
                                    <?php echo form_input('pan_card', (isset($_POST['pan_card']) ? $_POST['pan_card'] : ''), 'class="form-control" id="pan_card" maxlength="10"'); ?>
                                    <small class="text-danger" id="errpancard"></small>
                                </div>
                            </div>    
                        </div>
                        <?php
                        $cf_required = function ($label) {
                            return (strpos((string) $label, '*') !== false) ? ' required="required" ' : '';
                        };
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="sale_source"><?= lang("Sale_source"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = array(0 => lang('no'), 1 => lang('yes'));
                                        echo form_dropdown('sale_source', $opt, '', 'class="form-control tip noedit" id="sale_source" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'ccf4') : lang('ccf4', 'ccf4')); ?>
                                    <?php
                                    if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {
                                        echo form_dropdown('cf4', (json_decode($custome_fields->cf4_input_options, TRUE)), '', 'class="form-control tip" id="cf4"' . $cf_required($custome_fields->cf4));
                                    } else {
                                        echo form_input('cf4', '', 'class="form-control" id="cf4"' . $cf_required($custome_fields->cf4));
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ccf1') : lang('Members Card No', 'ccf1')); ?>
                                    <?php
                                    if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {
                                        echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)), '', 'class="form-control tip" id="cf1"' . $cf_required($custome_fields->cf1));
                                    } else {
                                        echo form_input('cf1', '', 'class="form-control" id="cf1"' . $cf_required($custome_fields->cf1));
                                    }
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'ccf5') : lang('ccf5', 'ccf5')); ?>
                                    <?php
                                    if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {
                                        echo form_dropdown('cf5', (json_decode($custome_fields->cf5_input_options, TRUE)), '', 'class="form-control tip" id="cf5"' . $cf_required($custome_fields->cf5));
                                    } else {
                                        echo form_input('cf5', '', 'class="form-control" id="cf5"' . $cf_required($custome_fields->cf5));
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="country" id="country" value="<?= htmlspecialchars((string) $selected_country_name, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="add_country" id="add_country" value="<?= htmlspecialchars((string) $selected_country_name, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="state" id="state" value="<?= htmlspecialchars((string) $biller_state_value, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="state_code" id="state_code" value="<?= htmlspecialchars((string) $biller->state_code, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="statename" id="statename" value="<?= htmlspecialchars((string) $biller_state_name, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="city" id="city" value="<?= htmlspecialchars((string) (isset($_POST['city']) ? $_POST['city'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <div id="postal_code_group" class="hidden">
                            <input type="hidden" name="postal_code" id="postal_code" value="<?= htmlspecialchars((string) (isset($_POST['postal_code']) ? $_POST['postal_code'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <span id="postal_code_error" style="color:#a94442; display:none; font-size:11px;">Please enter valid postal code</span>
                        </div>
                        <span id="error1" style="display:none;"></span>
                        <textarea id="customer-addr-country-options-html" readonly tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;overflow:hidden;"><?= $addr_country_options; ?></textarea>

                        <div class="form-group hidden">
                            <?= lang('award_points', 'award_points'); ?>
                            <?= form_input('award_points', 0, 'class="form-control tip" id="award_points"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("DOB", "dob"); ?>
                                    <?php echo form_input('dob', (isset($_POST['dob']) ? $_POST['dob'] : ""), 'class="form-control input-tip dob" id="dob" '); ?>
                                <span id="dob_error" style="color:#a94442; display: none; font-size:11px;">Date of Birth cannot be in the future</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Anniversary Date", "anniversary"); ?>
                                    <?php echo form_input('anniversary', (isset($_POST['anniversary']) ? $_POST['anniversary'] : ""), 'class="form-control input-tip anniversary" id="anniversary"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Older Child's Birthday", "dob_child1"); ?>
<?php echo form_input('dob_child1', (isset($_POST['dob_child1']) ? $_POST['dob_child1'] : ""), 'class="form-control input-tip date" id="dob_child1"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Younger Child's Birthday", "dob_child2"); ?>
<?php echo form_input('dob_child2', (isset($_POST['dob_child2']) ? $_POST['dob_child2'] : ""), 'class="form-control input-tip date" id="dob_child2"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Fathers Birthday", "dob_father"); ?>
<?php echo form_input('dob_father', (isset($_POST['dob_father']) ? $_POST['dob_father'] : ""), 'class="form-control input-tip date" id="dob_father" '); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Mothers Birthday", "dob_mother"); ?>
<?php echo form_input('dob_mother', (isset($_POST['dob_mother']) ? $_POST['dob_mother'] : ""), 'class="form-control input-tip date" id="dob_mother" '); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ccf2') : lang('ccf2', 'ccf2')); ?>
                                <?php
                                if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {
                                    echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)), '', 'class="form-control tip" id="cf2"' . $cf_required($custome_fields->cf2));
                                } else {
                                    echo form_input('cf2', '', 'class="form-control" id="cf2"' . $cf_required($custome_fields->cf2));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'ccf3') : lang('ccf3', 'ccf3')); ?>
                                <?php
                                if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {
                                    echo form_dropdown('cf3', (json_decode($custome_fields->cf3_input_options, TRUE)), '', 'class="form-control tip" id="cf3"' . $cf_required($custome_fields->cf3));
                                } else {
                                    echo form_input('cf3', '', 'class="form-control" id="cf3"' . $cf_required($custome_fields->cf3));
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'ccf6') : lang('ccf6', 'ccf6')) ?>
                        <?php                        
                        if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {                            
                            echo form_dropdown('cf6', (json_decode($custome_fields->cf6_input_options, TRUE)) , '', 'class="form-control tip" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf6', '', 'class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); 
                        }   
                        ?>
                    </div>
                    <?php /*
                    <div class="form-group">
                        <label class="control-label" for="location_id"><?php echo lang('warehouse'); ?></label>
                        <?php
                        $loc = array('' => lang('select') . ' ' . lang('warehouse'));
                        if (!empty($warehouses)) {
                            foreach ($warehouses as $wh) {
                                $loc[$wh->id] = $wh->name;
                            }
                        }
                        echo form_dropdown('location_id', $loc, set_value('location_id', ''), 'class="form-control select" id="location_id" style="width:100%;"');
                        ?>
                    </div>
                    */ ?>

                </div>
            </div>

            <div class="row customer-addr-table-wrap">
                <div class="col-md-12">
                    <div class="control-group table-group">
                        <div class="controls table-controls">
                            <div class="customer-addr-table-header">
                                <h4><b><?= lang('Addresses'); ?></b></h4>
                                <button type="button" class="btn btn-success btn-xs btn-add-addr-row" id="btnAddCustomerAddressRow" title="<?= lang('add_address'); ?>">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <div id="customer_addr_form_error" class="text-danger" style="display:none; margin-bottom:6px; font-size:12px;"></div>
                            <table id="customerAddressesTable" class="table items table-striped table-bordered table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th class="col-addr-type"><?= lang('type'); ?></th>
                                        <th class="col-addr-name">Address Name</th>
                                        <th class="col-addr-line"><?= lang('line1'); ?></th>
                                        <th class="col-addr-country"><?= lang('country'); ?></th>
                                        <th class="col-addr-state"><?= lang('state'); ?></th>
                                        <th class="col-addr-city"><?= lang('city'); ?></th>
                                        <th class="col-addr-postal"><?= lang('postal_code'); ?></th>
                                        <th class="col-addr-actions text-center"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

          <?php if($Settings->synced_data_sales){ ?>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="customer_url"> Customer URL </label>
                        <input type="text" name="customer_url" id="customer_url" class="form-control" placeholder="Customer URL" />
                    </div>
                    <div class="col-sm-6">
                        <label for="synced_data"> Synch Data </label>
                        <?php
                            $syncedSalesData[0] = 'No';
                            $syncedSalesData[1] = 'Yes';
                            echo form_dropdown('synced_data', $syncedSalesData, '0', 'class="form-control tip "  id="synced_data" style="width:100%;"');
                       ?>
                     
                    </div>

                     <div class="col-sm-6">
                        <label for="privatekey"> Private Key</label>
                        <input type="text" name="privatekey" id="privatekey" class="form-control" placeholder="Private Key" />
                    </div>
                </div>
            <?php } ?>
        </div>
            <div class="modal-footer">
                <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary" id="add_customer"'); ?>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<!--</div>-->

<?php $this->load->view($this->theme . 'customers/add_address_modal'); ?>

<?= $modal_js ?>
<script type="text/javascript" src="<?= base_url('themes/default/assets/js/customer_add_address_modal.js?v=20260630_2'); ?>"></script>

<script type="text/javascript">
    /** Parse DOB text the same way as the datepicker (site.dateFormats.js_sdate). Avoids new Date("dd/mm/yyyy") US-parse bugs. */
    function parseCustomerDobValue(str) {
        if (!str || !String(str).trim()) {
            return null;
        }
        var fmt = (typeof site !== 'undefined' && site.dateFormats && site.dateFormats.js_sdate)
            ? String(site.dateFormats.js_sdate).toLowerCase() : 'dd/mm/yyyy';
        var parts = String(str).trim().split(/[^0-9]+/).filter(function (p) { return p.length; });
        if (parts.length < 3) {
            return null;
        }
        var tokens = fmt.split(/[^a-z]+/i).filter(function (t) { return t.length; });
        if (tokens.length < 3) {
            tokens = ['dd', 'mm', 'yyyy'];
        }
        var d, m, y, i, tok;
        for (i = 0; i < 3; i++) {
            tok = tokens[i].toLowerCase();
            if (tok.indexOf('d') === 0) {
                d = parseInt(parts[i], 10);
            } else if (tok.indexOf('m') === 0) {
                m = parseInt(parts[i], 10) - 1;
            } else if (tok.indexOf('y') === 0) {
                y = parseInt(parts[i], 10);
            }
        }
        if (d == null || m == null || y == null || isNaN(d) || isNaN(m) || isNaN(y)) {
            return null;
        }
        var dt = new Date(y, m, d);
        if (dt.getFullYear() !== y || dt.getMonth() !== m || dt.getDate() !== d) {
            return null;
        }
        return dt;
    }

    var smaCustomerAddrCountryOptionsHtml = '';
    var smaCustomerAddrRowIndex = 0;
    var smaCustomerAddrDefaultCountry = <?= json_encode((string) $selected_country_name); ?>;
    var smaCustomerAddrDefaultState = <?= json_encode((string) $biller_state_value); ?>;

    function smaGetCustomerAddrCountryOptionsHtml() {
        if (!smaCustomerAddrCountryOptionsHtml) {
            smaCustomerAddrCountryOptionsHtml = String($('#customer-addr-country-options-html').val() || $('#customer-addr-country-options-html').text() || '').trim();
        }
        return smaCustomerAddrCountryOptionsHtml;
    }

    function smaParseAddrStateValue(stateVal) {
        return window.SmaCustomerAddAddressModal
            ? window.SmaCustomerAddAddressModal.parseAddrStateValue(stateVal)
            : { name: '', code: '' };
    }

    function smaEscapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function customerFormHasAddressState() {
        if ($('#customerAddressesTable tbody tr.customer-addr-row').length) {
            return $.trim($('#customerAddressesTable tbody tr.customer-addr-row').first().find('input[name="customer_addr_state[]"]').val() || '') !== '';
        }
        return $.trim($('#statename').val() || '') !== '';
    }

    function smaGetAddrStateDisplayText(stateVal) {
        var parsed = smaParseAddrStateValue(stateVal);
        if (parsed.name && parsed.code) {
            return parsed.name + ' (' + parsed.code + ')';
        }
        return parsed.name || String(stateVal || '').trim();
    }

    function smaBuildLockedCustomerAddressRowHtml(rowIndex, data) {
        data = data || {};
        var typeVal = data.type || 'Billing';
        var addressNameVal = data.address_name || '';
        var lineVal = data.line1 || '';
        var countryVal = data.country || '';
        var stateVal = data.state || '';
        var stateCodeVal = data.state_code || '';
        var cityVal = data.city || '';
        var postalVal = data.postal_code || '';
        return '<tr class="customer-addr-row customer-addr-row-locked" data-row-index="' + rowIndex + '">' +
            '<td><input type="hidden" name="customer_addr_type[]" value="' + smaEscapeHtml(typeVal) + '" /><span class="customer-addr-display">' + smaEscapeHtml(typeVal) + '</span></td>' +
            '<td><input type="hidden" name="customer_addr_address_name[]" value="' + smaEscapeHtml(addressNameVal) + '" /><span class="customer-addr-display">' + smaEscapeHtml(addressNameVal) + '</span></td>' +
            '<td><input type="hidden" name="customer_addr_line[]" value="' + smaEscapeHtml(lineVal) + '" /><span class="customer-addr-display">' + smaEscapeHtml(lineVal) + '</span></td>' +
            '<td><input type="hidden" name="customer_addr_country[]" value="' + smaEscapeHtml(countryVal) + '" /><span class="customer-addr-display">' + smaEscapeHtml(countryVal) + '</span></td>' +
            '<td><input type="hidden" name="customer_addr_state[]" value="' + smaEscapeHtml(stateVal) + '" />' +
            '<input type="hidden" name="customer_addr_state_code[]" value="' + smaEscapeHtml(stateCodeVal) + '" />' +
            '<span class="customer-addr-display">' + smaEscapeHtml(smaGetAddrStateDisplayText(stateVal)) + '</span></td>' +
            '<td><input type="hidden" name="customer_addr_city[]" value="' + smaEscapeHtml(cityVal) + '" /><span class="customer-addr-display">' + smaEscapeHtml(cityVal) + '</span></td>' +
            '<td><input type="hidden" name="customer_addr_postal_code[]" value="' + smaEscapeHtml(postalVal) + '" /><span class="customer-addr-display">' + smaEscapeHtml(postalVal) + '</span></td>' +
            '<td class="customer-addr-actions">' +
            '<button type="button" class="btn btn-danger btn-xs btn-customer-addr-delete" title="<?= lang('delete'); ?>"><i class="fa fa-trash-o"></i></button>' +
            '</td>' +
            '</tr>';
    }

    function smaAppendLockedCustomerAddressRow(data) {
        var rowIndex = smaCustomerAddrRowIndex++;
        $('#customerAddressesTable tbody').append(smaBuildLockedCustomerAddressRowHtml(rowIndex, data));
        smaClearCustomerAddressTableErrors();
    }

    var smaAddCustomerAddressModal;

    function smaValidateAddressFields(data, rowNum) {
        if (smaAddCustomerAddressModal) {
            return smaAddCustomerAddressModal.validateFields(data, rowNum);
        }
        return null;
    }

    function smaClearCustomerAddressTableErrors() {
        $('#customer_addr_form_error').hide().text('');
        $('#customerAddressesTable tbody tr.customer-addr-row td').removeClass('has-error');
    }

    function smaReadAddressRowFromTable($row) {
        return {
            type: $.trim($row.find('input[name="customer_addr_type[]"]').val() || ''),
            address_name: $.trim($row.find('input[name="customer_addr_address_name[]"]').val() || ''),
            line1: $.trim($row.find('input[name="customer_addr_line[]"]').val() || ''),
            state: $.trim($row.find('input[name="customer_addr_state[]"]').val() || '')
        };
    }

    function smaValidateCustomerAddressRowsClient() {
        smaClearCustomerAddressTableErrors();
        var firstErrorMsg = '';

        $('#customerAddressesTable tbody tr.customer-addr-row').each(function (i) {
            if (firstErrorMsg) {
                return;
            }
            var data = smaReadAddressRowFromTable($(this));
            if (!data.type && !data.address_name && !data.line1 && !data.state) {
                return;
            }
            var err = smaValidateAddressFields(data, i + 1);
            if (err) {
                firstErrorMsg = err.msg;
            }
        });

        if (firstErrorMsg) {
            $('#customer_addr_form_error').text(firstErrorMsg).show();
            return false;
        }
        return true;
    }

    function smaDestroyAddCustomerBootstrapValidator() {
        var $form = $('#add-customer-form');
        if (!$form.length) {
            return;
        }
        var bv = $form.data('bootstrapValidator');
        if (bv && typeof bv.destroy === 'function') {
            try {
                bv.destroy();
            } catch (ignoreBvDestroy) {}
        }
    }

    function smaValidateAddCustomerRequiredFields() {
        var $form = $('#add-customer-form');
        if (!$form.length) {
            return false;
        }

        var name = $.trim($('#name').val() || '');
        if (!name) {
            $('#name').focus();
            return false;
        }

        var $phone = $form.find('#phone');
        if ($phone.length && $phone.prop('required') && !$phone.is(':disabled')) {
            var phoneVal = $.trim(String($phone.val() || ''));
            if (!phoneVal) {
                var $phoneErr = $form.find('#phone_error');
                if ($phoneErr.length) {
                    $phoneErr.text('Phone number is required.').show();
                }
                $phone.focus();
                return false;
            }
        }

        var invalid = null;
        $form.find('input[required], select[required], textarea[required]').each(function () {
            var $el = $(this);
            if (!$el.is(':visible') || $el.is(':disabled')) {
                return;
            }
            var id = $el.attr('id') || '';
            if (id === 'phone' || id === 'email_address') {
                return;
            }
            var val = $el.is('select') ? $el.val() : $.trim(String($el.val() || ''));
            if (val === '' || val === null) {
                invalid = $el;
                return false;
            }
        });

        if (invalid) {
            invalid.focus();
            if (invalid.hasClass('select') && invalid.data('select2')) {
                invalid.select2('open');
            }
            return false;
        }

        return true;
    }

    function smaRunAddCustomerClientValidation() {
        $('#errpancard').html('');
        syncAddCustomerButtonState();
        if (isPosDeliveryCustomerFlow() && $('#add_customer').prop('disabled')) {
            return false;
        }

        if (!smaValidateCustomerAddressRowsClient()) {
            return false;
        }

        var email = $('#email_address').val();
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (email !== '' && !emailPattern.test(email)) {
            $('#email_error').show();
            $('#email_address').focus();
            return false;
        }
        $('#email_error').hide();

        if (!validatePostalCode()) {
            $('#postal_code').focus();
            return false;
        }

        var dob = $('#dob').val();
        if (dob !== '') {
            var selectedDate = parseCustomerDobValue(dob);
            if (selectedDate) {
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);
                if (selectedDate > today) {
                    $('#dob_error').show();
                    $('#dob').focus();
                    return false;
                }
            }
            $('#dob_error').hide();
        }

        var panCard = $.trim($('#pan_card').val() || '');
        if (panCard === '') {
            if (!customerFormHasAddressState() && ($('#state').val() === 'other' || $.trim($('#statename').val() || '') === '')) {
                $('#errstatename').html('<strong>State Name</strong> is required.');
                return false;
            }
            $('#errstatename').html('');
            return true;
        }

        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        if (patt.test(panCard)) {
            if (!customerFormHasAddressState() && ($('#state').val() === 'other' || $.trim($('#statename').val() || '') === '')) {
                $('#errstatename').html('<strong>State Name</strong> is required.');
                return false;
            }
            $('#errstatename').html('');
            return true;
        }

        $('#errpancard').html('"<strong>' + smaEscapeHtml(panCard) + '</strong>" this no. invalid, Please enter valid pancard no.');
        $('#pan_card').val('').focus();
        return false;
    }

    function smaBindAddCustomerSubmitHandlers() {
        if (window.__smaAddCustomerSubmitBound) {
            return;
        }
        window.__smaAddCustomerSubmitBound = true;

        $(document).on('submit.smaAddCust', '#add-customer-form', function (e) {
            if (!smaRunAddCustomerClientValidation()) {
                e.preventDefault();
                return false;
            }
            if (!smaValidateAddCustomerRequiredFields()) {
                e.preventDefault();
                return false;
            }
        });
    }

    $(document).ready(function (e) {

        smaAddCustomerAddressModal = new SmaCustomerAddAddressModal({
            instanceKey: 'addCustomer',
            getStatesUrl: '<?= base_url(); ?>customers/getstates',
            countryOptionsSelector: '#customer-addr-country-options-html',
            defaultCountry: smaCustomerAddrDefaultCountry,
            defaultState: smaCustomerAddrDefaultState,
            onSave: function (data) {
                smaAppendLockedCustomerAddressRow(data);
            }
        });

        $(document).on('click', '.btn-customer-addr-delete', function (e) {
            e.preventDefault();
            var $row = $(this).closest('tr.customer-addr-row');
            if (window.confirm(<?= json_encode(lang('r_u_sure')); ?>)) {
                $row.remove();
                smaClearCustomerAddressTableErrors();
            }
        });

        smaBindAddCustomerSubmitHandlers();
        smaDestroyAddCustomerBootstrapValidator();
        setTimeout(smaDestroyAddCustomerBootstrapValidator, 0);
        $(document).off('shown.bs.modal.smaAddCust', '#myModal').on('shown.bs.modal.smaAddCust', '#myModal', function () {
            if ($('#add-customer-form').length) {
                smaDestroyAddCustomerBootstrapValidator();
            }
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            if (!id) {
                return;
            }
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                var $label = $("label[for='" + id + "']");
                if ($label.length && $label.text().indexOf('*') === -1) {
                    $label.append(' *');
                }
            }
        });

        $('.form-control').attr('autocomplete', 'off');
    });

    $('#email_address').on('input blur', function () {
        var email = $(this).val();
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (email === '') {
            $('#email_error').hide();
            return true;
        }
        if (emailPattern.test(email)) {
            $('#email_error').hide();
            return true;
        }
        $('#email_error').show();
        return false;
    });

    $("#email_address").focusout(function () {
        var email = $("#email_address").val();
        if (email === '' || $('#email_error').is(':visible')) {
            return;
        }
        $.ajax({
            type: "get",
            url: '<?php echo base_url(); ?>customers/getEmail',
            data: {emailid: email},
            success: function (response) {
                if (response == 0) {
                    alert('Email already exists');
                    $("#email_address").val('');
                    $("#email_address").focus();
                }
            },
        });
    });

    var specialKeys = new Array();
    specialKeys.push(8); //Backspace
    function IsNumeric(e, t) {
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
        document.getElementById("error").style.display = ret ? "none" : "inline";
        return ret;
    }

    function IsNumeric2(e, t) {
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
        document.getElementById("error1").style.display = ret ? "none" : "inline";
        return ret;
    }

    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("error2").style.display = ret ? "none" : "inline";
        return ret;
    }

    function onlyAlphabets(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("errora2").style.display = ret ? "none" : "inline";
        return ret;
    }

    function showEditCustomerModal(customerId) {
        var $modal = $('#myModal');
        if (!$modal.length) {
            return;
        }
        $modal.empty();
        $modal.append($.parseHTML(
            '<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading customer details...</div></div></div>',
            document,
            true
        ));
        $modal.modal('show');
        $.ajax({
            url: "<?= site_url('customers/edit'); ?>/" + customerId,
            type: 'GET',
            cache: false,
            success: function(response) {
                $modal.empty();
                $modal.append($.parseHTML(response, document, true));
                $modal.modal('show');
            },
            error: function(xhr, status) {
                console.error('Failed to load edit customer form:', status, xhr ? xhr.status : '');
                $modal.empty();
                $modal.append($.parseHTML(
                    '<div class="modal-dialog"><div class="modal-content"><div class="modal-body text-danger">Failed to load customer details. Please try again.</div></div></div>',
                    document,
                    true
                ));
                $modal.modal('show');
            }
        });
    }

    (function() {
        const addForm = document.getElementById('add-customer-form');
        const phoneInput = document.getElementById('phone');
        const errorMsg = document.getElementById('phone_error');
        const isAutoCustomerNumber = addForm && addForm.getAttribute('data-auto-customer-number') === '1';

        if (!phoneInput || !addForm) return;

        if (isAutoCustomerNumber) {
            phoneInput.setAttribute('maxlength', '10');
        }

        const countryField = document.getElementById('country');
        if (!countryField) return;

        const countryData = <?php
            echo json_encode(array_map(function($c) {
                return [
                    'name' => $c->name,
                    'phone_digits' => (!empty($c->phone_digits) && is_numeric($c->phone_digits))
                                        ? (int)$c->phone_digits
                                        : 10
                ];
            }, $country));
        ?>;

        function getRequiredLength(countryName) {
            var billerDigits = parseInt(addForm.getAttribute('data-biller-phone-digits'), 10);
            if (!isNaN(billerDigits) && billerDigits > 0) {
                return billerDigits;
            }
            const found = countryData.find(function (c) { return c.name === countryName; });
            if (!found || !found.phone_digits) {
                return 10;
            }
            return found.phone_digits;
        }

        function isUnchangedAutoPhone() {
            if (!isAutoCustomerNumber) {
                return false;
            }
            var original = String(document.getElementById('auto_phone_original').value || '').replace(/\D+/g, '');
            var current = String(phoneInput.value || '').replace(/\D+/g, '');
            return original !== '' && current === original;
        }

        function syncSystemGeneratedFlag() {
            if (!isAutoCustomerNumber) {
                return;
            }
            var original = String(document.getElementById('auto_phone_original').value || '').replace(/\D+/g, '');
            var current = String(phoneInput.value || '').replace(/\D+/g, '');
            var flagEl = document.getElementById('is_system_generated');
            if (flagEl) {
                flagEl.value = (original !== '' && current === original) ? 'TRUE' : 'FALSE';
            }
        }

        function validatePhone() {
            if (isUnchangedAutoPhone()) {
                if (errorMsg) {
                    errorMsg.style.display = 'none';
                }
                return true;
            }

            const phone = phoneInput.value.trim();
            const countryName = $('#country').val();
            const requiredLength = getRequiredLength(countryName);
            const isValidDigits = /^[1-9][0-9]*$/.test(phone);

            if (phone === '') {
                if (errorMsg) {
                    errorMsg.style.display = 'none';
                }
                return true;
            }

            if (!isValidDigits) {
                errorMsg.textContent = 'Phone number must contain only digits and should not start with 0.';
                errorMsg.style.display = 'block';
                return false;
            }

            if (phone.length !== requiredLength) {
                errorMsg.textContent = 'Phone number must be ' + requiredLength + ' digits.';
                errorMsg.style.display = 'block';
                return false;
            }

            errorMsg.style.display = 'none';
            return true;
        }

        function maybeCheckDuplicatePhone() {
            if (isUnchangedAutoPhone()) {
                return;
            }
            const countryName = $('#country').val();
            const requiredLength = getRequiredLength(countryName);
            const phone = String(phoneInput.value || '').replace(/\D+/g, '');
            if (phone.length === requiredLength) {
                checkmobileno('customer', phone, 'error', 'phone');
            }
        }

        $(phoneInput).on('input.smaAddCustPhone', function() {
            syncSystemGeneratedFlag();
            validatePhone();
            maybeCheckDuplicatePhone();
            setTimeout(syncAddCustomerButtonState, 0);
        });

        $(phoneInput).on('keypress.smaAddCustPhone', function(e){
            const countryName = $('#country').val();
            const requiredLength = getRequiredLength(countryName);
            if (this.value.length >= requiredLength) {
                e.preventDefault();
            }
        });

        $('#country').on('change.smaAddCustPhone', function () {
            syncSystemGeneratedFlag();
            validatePhone();
            maybeCheckDuplicatePhone();
        });

        $('#add-customer-form').on('submit.smaAddCustPhone', function (e) {
            syncSystemGeneratedFlag();
            if (!validatePhone()) {
                e.preventDefault();
                phoneInput.focus();
                return false;
            }
        });

        addForm.setAttribute('data-phone-dup-inline', '1');
        syncSystemGeneratedFlag();
    })();

    function checkmobileno(groupname, mobileno, errorshow, thisid) {
        var $phone = $('#myModal #add-customer-form #' + thisid);
        if (!$phone.length) {
            $phone = $('#add-customer-form #' + thisid);
        }
        if (!$phone.length) {
            $phone = $('#' + thisid);
        }
        var phone = $.trim(String(mobileno || ''));
        if (!phone) {
            return;
        }
        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'get',
            url: '<?= base_url() ?>customers/checkMobileno',
            data: {
                groupname: groupname,
                mobileno: phone
            },
            success: function(response) {
                if (response && response.status == 'success') {
                    $phone.val('').focus();
                    alert('Phone no already exists');

                    // POS delivery flow: keep customer selected and open edit form.
                    if ($('#poscustomer').length) {
                        $('#poscustomer').val(response.id).trigger('change');
                    }
                    if ($('#custname').length) {
                        $('#custname').val(response.id);
                    }
                    if ($('#customer_name').length) {
                        $('#customer_name').val((response.name || '') + '(' + (response.phone || '') + ')');
                    }
                    if (typeof localStorage !== 'undefined') {
                        localStorage.setItem('poscustomer', response.id);
                        localStorage.setItem('poscustomername', response.name || '');
                    }
                    if (response.id) {
                        showEditCustomerModal(response.id);
                    }
                }
            }
        });
    }
    window.checkmobileno = checkmobileno;

    function validateGstinno(el) {
        var input = el && el.nodeName && (el.nodeName === 'INPUT' || el.nodeName === 'TEXTAREA') ? el : null;
        if (!input) {
            input = document.querySelector('#add-customer-form #gstn_no') || document.getElementById('gstn_no');
        }
        if (!input) {
            return true;
        }
        var len = String(input.value || '').trim().length;
        if (len === 0) {
            input.value = '';
            return true;
        }
        if (len !== 15) {
            var taxLabel = String($('#gstn_label').text() || 'GSTN').replace(/\*/g, '').trim();
            alert(taxLabel + ' length must be 15 character long ');
            input.value = '';
            input.focus();
            return false;
        }
        return true;
    }

    $('#pan_card').change(function () {
        $('#errpancard').html(" ");
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        var pan_card = $(this).val();
        if (patt.test(pan_card)) {
            $('#errpancard').html("");
        } else {
            if (pan_card != '') {
                $('#errpancard').html("\"<strong>" + pan_card + "</strong>\" this no. invalid, Please enter valid pancard no.");
                $(this).val("");
            }
        }
    });

    function validatePostalCode() {
        var $postal = $('#postal_code');
        if (!$postal.length || $postal.is(':disabled') || !$('#postal_code_group').is(':visible')) {
            $('#postal_code_error').hide();
            return true;
        }
        var postalCode = $.trim(String($postal.val() || ''));
        if (postalCode !== '' && postalCode.length < 6) {
            $('#postal_code_error').show();
            return false;
        }
        $('#postal_code_error').hide();
        return true;
    }

    function isPosDeliveryCustomerFlow() {
        var redirectUrl = $.trim(String($('input[name="redirect_url"]').val() || ''));
        var hasPosRedirect = /(^|\/)pos(\/|$)/i.test(redirectUrl) || /index\.php\/pos(\/|$)/i.test(redirectUrl);
        var hasDeliveryType = /[?&]type=delivery(?:&|$)/i.test(window.location.search || '');
        return hasPosRedirect || hasDeliveryType;
    }

    function syncAddCustomerButtonState() {
        var $btn = $('#add_customer');
        if (!$btn.length) {
            return;
        }
        if (!isPosDeliveryCustomerFlow()) {
            $btn.prop('disabled', false);
            return;
        }
        var disable = false;
        var phoneVal = $.trim(String($('#phone').val() || ''));
        if (!phoneVal) {
            disable = true;
        }
        if ($('#phone_error').is(':visible')) {
            disable = true;
        }
        if ($('#email_error').is(':visible')) {
            disable = true;
        }
        if ($('#dob_error').is(':visible')) {
            disable = true;
        }
        if ($('#postal_code_group').is(':visible') && $('#postal_code_error').is(':visible')) {
            disable = true;
        }
        $btn.prop('disabled', disable);
    }

    $('#postal_code').on('input blur', function () {
        validatePostalCode();
        syncAddCustomerButtonState();
    });

    $('#add-customer-form').on('input change blur', '#phone, #email_address, #dob, #postal_code, #country', function () {
        setTimeout(syncAddCustomerButtonState, 0);
    });


    /* Country/state list, add_country sync, phone length & GST label: core.js (works when this form is loaded into #myModal via AJAX). */

    $(document).on('focus', '.anniversary', function() {
        if (!$(this).data('datetimepicker')) {
            $(this).datetimepicker({
                format: site.dateFormats.js_sdate,
                fontAwesome: true,
                language: 'sma',
                todayBtn: 1,
                autoclose: 1,
                minView: 2,
                endDate: false
            });
            $(this).datetimepicker('show');
        }
    });

        $(document).on('focus', '#dob', function() {
        if (!$(this).data('datetimepicker')) {
            $(this).datetimepicker({
                format: site.dateFormats.js_sdate,
                fontAwesome: true,
                language: 'sma',
                todayBtn: 1,
                autoclose: 1,
                minView: 2,
                endDate: new Date()
            });
            $(this).datetimepicker('show');
        }
    });

    $('#dob').on('change blur', function() {
        var dob = $(this).val();
        if (dob === '') {
            $('#dob_error').hide();
            return true;
        }

        var selectedDate = parseCustomerDobValue(dob);
        if (!selectedDate) {
            $('#dob_error').hide();
            return true;
        }
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);

        if (selectedDate > today) {
            $('#dob_error').show();
            $(this).val('');
            return false;
        }
        $('#dob_error').hide();
        return true;
    });

    setTimeout(syncAddCustomerButtonState, 100);

</script>
