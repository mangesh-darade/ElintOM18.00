<style>
.editsty {
    background-color: transparent !important;
    color: #007bff !important;
    border: 1px solid #007bff !important;
    padding: 4px 29px !important;
    font-size: 13px !important;
    border-radius: 3px !important;
    margin-top: -1rem;
    margin-left: -1em;
}

.editsty:hover {
    background-color: #007bff !important;
    color: white !important;
}

.panel {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
}

.panel-heading {
    font-weight: bold;
    font-size: 16px;
    border-left: 4px solid #007bff;
    padding: 10px;
    background-color: #fff;
    border-radius: 8px 8px 0 0;
}

.panel-default>.panel-heading {
    color: #333;
    background-color: #f5f5f5;
    border-color: #009DFF;
}

/* .table {
        width: 100%;
        margin-bottom: 0;
    }

    .table td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .table td:first-child {
        font-weight: bold;
        width: 35%;
    } */

.profileSection .table input,
.profileSection .table select {
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 6px;
}
span#select2-chosen-6,
span#select2-chosen-7,
span#select2-chosen-8,
span#select2-chosen-9 {
    text-align: left;
}

</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<?php
$biller_phone_country = '';
$biller_phone_digits = 10;
if (!empty($biller_phone_meta) && is_array($biller_phone_meta)) {
    $biller_phone_country = !empty($biller_phone_meta['country']) ? (string) $biller_phone_meta['country'] : '';
    $biller_phone_digits = !empty($biller_phone_meta['phone_digits']) ? (int) $biller_phone_meta['phone_digits'] : 10;
} elseif (!empty($biller) && !empty($biller->country)) {
    $biller_phone_country = trim((string) $biller->country);
    if (!empty($country)) {
        foreach ($country as $c_row) {
            if (strcasecmp(trim((string) $c_row->name), $biller_phone_country) === 0) {
                if (!empty($c_row->phone_digits) && is_numeric($c_row->phone_digits)) {
                    $biller_phone_digits = (int) $c_row->phone_digits;
                } elseif (stripos($c_row->name, 'United Arab Emirates') !== false || strcasecmp($c_row->name, 'UAE') === 0) {
                    $biller_phone_digits = 9;
                }
                break;
            }
        }
    }
}
?>
<div class="container edit-customer-details"
     data-biller-country="<?= htmlspecialchars($biller_phone_country, ENT_QUOTES, 'UTF-8'); ?>"
     data-biller-phone-digits="<?= (int) $biller_phone_digits; ?>">
    <!-- Edit Button -->
    <div class="modal-header">
        <button type="button" id="add_button" class="btn btn-primary add_button editsty">Edit</button>
    </div>

    <div class="row">
        <!-- left Column: Personal Information -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Basic Information</strong></div>
                <div class="panel-body">
                    <table class="table">
                        <input type="hidden" id="customer_id" class="customer_id" name="customer_id">

                        <tr>
                            <td><strong>Customer Name*</strong></td>
                            <td>
                                <?php echo form_input('name', '', 'class="form-control disebledForm tip cust_name" id="cust_name" autocomplete="name" onkeypress="return onlyAlphabets1(event,this);" '); ?>
                                <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter
                                    alphabets only</span>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Phone *</strong></td>
                            <td>
                                <input type="tel" name="phone" class="form-control disebledForm tip cust_phone" required="required"
                                       id="cust_phone" autocomplete="tel" onkeypress="return IsNumeric(event, this)">
                                <span id="error" class ="error" style="color:#a94442; display: none;font-size:11px;">please enter
                                    numbers only</span>
                                    <span class="phone-error"
                                        style="color:#a94442; display:none; font-size:11px; margin-top:4px; display:block;">
                                    </span>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Email</strong></td>
                            <td>
                                <input type="email" name="email" class="form-control disebledForm cust_email" id="cust_email" autocomplete="email"
                                       pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
                                       title="Enter a valid email like name@example.com">
                                <span id="cust_email-errorss" class="cust_email-errorss"
                                      style="color:#a94442; font-size:12px; text-align:left; display:none; margin-top: 4px;">Please enter a valid email address</span>
                                <span id="cust_email-exists" class="cust_email-exists"
                                      style="color:#a94442; font-size:12px; display:none; margin-top:4px;">This email already exists</span>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>DOB</strong></td>
                            <td>
                                <?php echo form_input('dob', '', 'type="text" class="form-control disebledForm input-tip dob" id="dob" autocomplete="bday" placeholder="YYYY-MM-DD" pattern="(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])" maxlength="10" title="Enter a valid date in YYYY-MM-DD format (Year must be between 1900-2099)" required'); ?>
                                <span class="dob-future-error" style="color:#a94442;font-size:12px;display:none;margin-top:4px;">Future date is not allowed for Date of Birth</span>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Anniversary</strong></td>
                            <td>
                                <?php echo form_input('anniversary', '', 'type="text" class="form-control disebledForm input-tip anniversary" id="anniversary" autocomplete="off" placeholder="YYYY-MM-DD" pattern="(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])" maxlength="10" title="Enter a valid date in YYYY-MM-DD format (Year must be between 1900-2099)" required'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong><?php echo htmlspecialchars($this->site->getCustomFieldLabelText($custome_fields, 1, 'ccf1'), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td>
                                <?php
                                if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {
                                    echo form_dropdown('cf1', json_decode($custome_fields->cf1_input_options, TRUE), $customer->cf1, 'class="form-control tip cf1" id="cf1"' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf1', $customer->cf1, 'class="form-control disebledForm cf1" id="cf1"' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                }
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong><?php echo htmlspecialchars($this->site->getCustomFieldLabelText($custome_fields, 2, 'ccf2'), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td>
                                <?php
                                if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {
                                    echo form_dropdown('cf2', json_decode($custome_fields->cf2_input_options, TRUE), $customer->cf2, 'class="form-control tip cf2" id="cf2"' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf2', $customer->cf2, 'class="form-control disebledForm cf2" id="cf2"' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                }
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong><?php echo htmlspecialchars($this->site->getCustomFieldLabelText($custome_fields, 3, 'ccf3'), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td>
                                <?php
                                if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {
                                    echo form_dropdown('cf3', json_decode($custome_fields->cf3_input_options, TRUE), $customer->cf3, 'class="form-control tip cf3" id="cf3"' . ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf3', $customer->cf3, 'class="form-control disebledForm cf3" id="cf3"' . ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                }
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong><?php echo htmlspecialchars($this->site->getCustomFieldLabelText($custome_fields, 4, 'ccf4'), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td>
                                <?php
                                if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {
                                    echo form_dropdown('cf4', json_decode($custome_fields->cf4_input_options, TRUE), $customer->cf4, 'class="form-control tip cf4" id="cf4"' . ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf4', $customer->cf4, 'class="form-control disebledForm cf4" id="cf4"' . ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                }
                                ?>
                            </td>
                        </tr>

                        <?php /* Address, Country, State, State Code, Postal Code — managed in Addresses tab */ ?>
                        <tr class="crm-profile-location-field" style="display:none;">
                            <td><strong>Address</strong></td>
                            <td>
                                <?php echo form_input('address', '', 'class="form-control disebledForm address" id="address" autocomplete="street-address"'); ?>
                            </td>
                        </tr>

                        <tr class="crm-profile-location-field" style="display:none;">
                            <td><strong>Country</strong></td>
                            <td>
                                <select name="country" id="country_name" class="form-control country_name disebledForm " autocomplete="country">
                                    <option value="">Select Country</option>
                                    <?php foreach ($country as $country_val) { ?>
                                        <option value="<?= $country_val->name ?>" data-phone-digits="<?= (int) $country_val->phone_digits ?>" data-haspostalcode="<?= !empty($country_val->postal_code) ? (int) $country_val->postal_code : 0 ?>" data-taxlabel="<?= !empty($country_val->TaxNumberLabelText) ? htmlspecialchars($country_val->TaxNumberLabelText, ENT_QUOTES, 'UTF-8') : '' ?>">
                                            <?= $country_val->name ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>

                        <tr class="crm-profile-location-field" style="display:none;">
                            <td><strong>State</strong></td>
                            <td>
                                <script>
                                    var states = <?= json_encode($states) ?>;
                                </script>

                                <!-- crm-state-native: excluded from POS global Select2 so .val() saves correctly to DB -->
                                <select name="state" class="form-control state_id crm-state-native disebledForm" id="state_id">
                                    <option value="">Select state</option>
                                    <?php foreach ($states as $state) { ?>
                                        <option value="<?= $state->name ?>">
                                            <?= $state->name . '~' . $state->code ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>

                        <tr class="crm-profile-location-field" style="display:none;">
                            <td><strong>State Code</strong></td>
                            <td>
                                <input type="text" name="state_code" value="<?= $biller->state_code ?>" id="state_code"
                                       class="form-control state_code disebledForm" readonly>
                            </td>
                        </tr>

                        <tr class="crm-profile-location-field" style="display:none;">
                            <td><strong>City</strong></td>
                            <td>
                                <input type="text" name="city" id="city_name" class="form-control city_name disebledForm" autocomplete="address-level2" pattern="^[A-Za-z .-]{2,50}$" title="Enter a valid city name (letters only)">
                                <span class="city-errorss"
                                    style="color:#a94442; font-size:12px; display:none; margin-top:4px;">
                                    Please enter a valid city name
                                </span>
                            </td>
                        </tr>

                        <tr class="zipcode_tr crm-profile-location-field" style="display:none;">
                            <td><strong>Postal Code</strong></td>
                            <td>
                                <input type="text" name="postal_code" id="postal_code"
                                       class="form-control disebledForm postal_code" pattern="[0-9]{6}" autocomplete="postal-code">
                                <span id="postal_code-errorss" class="postal_code-errorss"
                                      style="color:#a94442; font-size:12px; text-align:left; display:none; margin-top:4px;">
                                    Please enter a valid Postal Code number
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- Right Column: Details -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Others Details</strong></div>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <td><strong>Price Group</strong></td>
                            <td>
                                <select name="price_group_id" class="form-control price_group disebledForm" id="price_group">
                                    <option></option>
                                    <?php foreach ($price_groups as $group) { ?>
                                    <option value="<?= $group->id ?>"
                                        <?= ($group->id == $customer->price_group_id) ? 'selected' : '' ?>>
                                        <?= $group->name ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <!-- <?php echo form_input('price_group', '', 'class="form-control disebledForm tip" id="price_group"'); ?> -->
                            </td>
                        <tr>
                            <td><strong>Company Name</strong></td>
                            <td><?php echo form_input('company', '', 'class="form-control company disebledForm tip" id="company"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Members Card No</strong></td>
                            <td>
                                <?php echo form_input('ccf1', '', 'class="form-control ccf1 disebledForm tip" id="ccf1"'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Customer Group </strong></td>
                            <td>

                            <select name="customer_group_id" class="form-control customer_groupId disebledForm" id="customer_groupId">
                                <option value="">Select Price Group</option>
                                <?php foreach ($customer_groups as $group) { ?>
                                    <option value="<?= $group->id ?>">
                                        <?= $group->name ?>
                                    </option>
                                <?php } ?>
                            </select>
                           

                                <!-- <?php echo form_input('customer_group', '', 'class="form-control disebledForm tip" id="customer_group"'); ?> -->
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('Is_Internal_Customer') ? lang('Is_Internal_Customer') : 'Internal Customer'; ?></strong></td>
                            <td>
                                <select name="is_internal_customer" class="form-control is_internal_customer disebledForm" id="is_internal_customer">
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="vat">
                            <td><strong>VAT/TIN #</strong></td>
                            <td>
                                <?php echo form_input('vat_no', '', 'class="form-control vat_no disebledForm tip" id="vat_no" pattern="[0-9A-Za-z]{8,15}"'); ?>
                                <div id="vat_no-errorss" class ="vat_no-errorss" style="color:#a94442; font-size:12px; text-align:left; display: none; margin-top: 5px;">
                                    Please enter a valid VAT/TIN number (8-15 alphanumeric characters)
                                </div>
                            </td>
                        </tr>
                        <?php
                        $show_tax_field = false;
                        $tax_label = '';
                        $active_settings = isset($settings) ? $settings : (isset($Settings) ? $Settings : null);
                        if (!empty($active_settings->country_name) && !empty($country)) {
                            foreach ($country as $c) {
                                if (strcasecmp($c->name, $active_settings->country_name) === 0 && !empty($c->TaxNumberLabelText)) {
                                    $tax_label = $c->TaxNumberLabelText;
                                    $show_tax_field = true;
                                    break;
                                }
                            }
                        }
                        ?>
                        <?php if ($show_tax_field): ?>
                        <tr class="gstn" data-default-tax-label="<?= htmlspecialchars($tax_label, ENT_QUOTES, 'UTF-8') ?>">
                            <td><strong class="gstn_label_text"><?= $tax_label; ?></strong></td>
                            <td>
                                <?php echo form_input('gstn_no', '', 'class="form-control gstn_no disebledForm tip" id="gstn_no" pattern="[0-9A-Z]{15}"'); ?>
                                <div id="gstn_no-errorss" class ="gstn_no-errorss" style="color:#a94442; font-size:12px; text-align:left; display: none; margin-top: 5px;">
                                    Please enter a valid <span class="gstn_label_error_text"><?= $tax_label; ?></span> number
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>



                        <tr>
                            <td><strong>PAN #</strong></td>
                            <td>
                                <?php echo form_input('pan_no', '', 'class="form-control pan_no disebledForm tip" id="pan_no" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" minlength="10" maxlength="10"'); ?>
                                <span class="pan_no-errorss"
                                    style="color:#a94442; font-size:12px; display:none; margin-top:4px;">
                                    Please enter a valid PAN number
                                </span>
                            </td>
                        </tr>
                        <tr class="award-point">
                            <td><strong><?= lang('Award_points'); ?></strong></td>
                            <td>
                                <?php echo form_input('award_points','', 'class="form-control award_points tip disebledForm" id="award_points"readonly'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong><?php echo htmlspecialchars($this->site->getCustomFieldLabelText($custome_fields, 5, 'ccf5'), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td>
                                <?php
                                if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {
                                    echo form_dropdown('cf5', json_decode($custome_fields->cf5_input_options, TRUE), $customer->cf5, 'class="form-control tip cf5" id="cf5"' . ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf5', $customer->cf5, 'class="form-control disebledForm cf5" id="cf5"' . ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                }
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong><?php echo htmlspecialchars($this->site->getCustomFieldLabelText($custome_fields, 6, 'ccf6'), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td>
                                <?php
                                if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {
                                    echo form_dropdown('cf6', json_decode($custome_fields->cf6_input_options, TRUE), $customer->cf6, 'class="form-control tip cf6" id="cf6"' . ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf6', $customer->cf6, 'class="form-control disebledForm cf6" id="cf6"' . ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                }
                                ?>
                            </td>
                        </tr>
                        <!-- <tr>
                            <td><strong>Older Child's Birthday</strong></td>
                            <td><?php echo form_input('dob_child1', '', 'class="form-control disebledForm input-tip date" id="dob_child1"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Younger Child's Birthday</strong></td>
                            <td><?php echo form_input('dob_child2', '', 'class="form-control disebledForm input-tip date" id="dob_child2"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Father's Birthday</strong></td>
                            <td><?php echo form_input('dob_father', '', 'class="form-control disebledForm input-tip date" id="dob_father"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Mother's Birthday</strong></td>
                            <td><?php echo form_input('dob_mother', '', 'class="form-control disebledForm input-tip date" id="dob_mother"'); ?>
                            </td>
                        </tr> -->
                        <tr class="hidden-rows">
                            <td><strong>Private Key</strong></td>
                            <td>---</td>
                        </tr>
                        <tr class="hidden-rows">
                            <td><strong>Reset E-shop Password</strong></td>
                            <td>
                                <input type="password" name="eshop_password" class="form-control disebledForm"
                                    placeholder="Leave blank if you don't want to reset">
                            </td>
                        </tr>
                        <tr class="hidden-rows">
                            <td><strong>Sync Data</strong></td>
                            <td>
                                <select name="sync_data" class="form-control disebledForm">
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function normalizeCountryDisplayName(name) {
    name = (name || '').replace(/\s+/g, ' ').trim();
    if (!name) return '';
    const parts = name.split(' ');
    if (parts.length % 2 === 0) {
        const half = parts.length / 2;
        const left = parts.slice(0, half).join(' ');
        const right = parts.slice(half).join(' ');
        if (left.toLowerCase() === right.toLowerCase()) {
            return left;
        }
    }
    return name;
}

function fetchPhoneDigitsForCrm(countryName, $container) {
    if (!countryName) {
        $container.find('.country_name').data('required-digits', 0);
        return;
    }
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "<?= base_url('customers/getCountryPhoneDigits') ?>",
        data: { country: countryName },
        success: function (res) {
            // Ignore stale responses from previously selected countries.
            if (($container.find('.country_name').val() || '') !== countryName) {
                return;
            }
            $container.find('.country_name').data('required-digits', (res && res.phone_digits) ? parseInt(res.phone_digits, 10) : 0);
            $container.find('.cust_phone').trigger('input');
        },
        error: function () {
            if (($container.find('.country_name').val() || '') !== countryName) {
                return;
            }
            $container.find('.country_name').data('required-digits', parseInt($container.find('.country_name option:selected').data('phone-digits'), 10) || 0);
            $container.find('.cust_phone').trigger('input');
        }
    });
}

$(document).ready(function() {
    $('.hidden-rows').hide();
});
</script>
<script>
$(document).on('input', function () {
    validateFields(); // Call validation function on input
});

$('#paymentModal').on('shown.bs.modal', function () {
    validateFields(); // Validate when the modal opens
});

function validateFields() {
    let isValid = true;
    let country = $('.country_name').val();

    // Specify the field selectors and their corresponding error message selectors
    const fields = [
        { field: '.postal_code', error: '.postal_code-errorss' },
        { field: '.cust_email', error: '.cust_email-errorss' },
        { field: '.vat_no', error: '.vat_no-errorss' },
        { field: '.gstn_no', error: '.gstn_no-errorss' },
    ];

    fields.forEach(function (cfg) {
        const $field = $(cfg.field);
        const $errorSpan = $(cfg.error);

        if ($field.length && !$field[0].checkValidity()) {
            $errorSpan.show();
            isValid = false;
        } else {
            $errorSpan.hide();
        }
    });

    // Explicit PAN validation for better cross-browser reliability
    const $panField = $('.pan_no');
    const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/; // 5 letters, 4 digits, 1 letter
    if ($panField.length) {
        $panField.each(function() {
            const $this = $(this);
            const $panError = $this.closest('td').find('.pan_no-errorss');
            const panVal = $this.val().trim();
            if (panVal.length === 0) {
                $panError.hide();
            } else if (panVal.length < 10) {
                // Don't show error until full length is entered
                $panError.hide();
            } else if (!panRegex.test(panVal)) {
                $panError.show();
                isValid = false;
            } else {
                $panError.hide();
            }
        });
    }

    // Enable/disable the save button based on form validity
    $('.add_button').prop('disabled', !isValid);
}


// function getStates(countryId) {
//         $.ajax({
//             type: 'GET',
//             dataType: 'json',
//             url: '<?= base_url('customers/getstates') ?>', 
//             data: { 'country': countryId },
//             success: function(response) {
//                 // console.log('response');
//                 // console.log(response);
//                 // console.log('response');

//                 if (response.status == 'success') {
//                     var stateOptions = '<option value="">Select State</option>';
//                     $.each(response.data, function(index, state) {
//                         stateOptions += `<option value="${state.id}">${state.name}</option>`;
//                     });
//                     $("#state").html(stateOptions);
//                 } else {
//                     $("#state").html('<option value="">Select State</option>');
//                 }
//             }
//         });
//     }
    /**
     * State uses native <select class="crm-state-native"> (not Select2) so options
     * can be replaced with .html() and the selected value posts correctly on save.
     */
    function getStates(countryId, $container, selectedState) {
        $container = $container && $container.length ? $container : $('.edit-customer-details').first();
        var $sel = $container.find('select.state_id');
        if (!$sel.length) {
            return;
        }

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '<?= base_url('customers/getstatesCrm') ?>',
            data: {'country': countryId},
            success: function (response) {
                $sel.html(response.data || '');
                var targetState = $.trim(String(selectedState || $container.find('.country_name').data('selected-state') || ''));
                var matchedState = '';

                if (targetState !== '') {
                    $sel.val(targetState);
                    if (($sel.val() || '') !== targetState) {
                        $sel.find('option').each(function () {
                            var optionValue = $.trim(String($(this).val() || ''));
                            if (matchedState === '' && optionValue.toLowerCase() === targetState.toLowerCase()) {
                                matchedState = optionValue;
                            }
                        });
                        $sel.val(matchedState);
                    }
                } else {
                    $sel.val('');
                }
                $sel.trigger('change');
                $container.find('.country_name').removeData('selected-state');

                if (response.status !== 'success') {
                    console.error('Error fetching states for selected country');
                }
            },
            error: function () {
                console.error('getStates AJAX failed');
            }
        });
    }

        function syncPostalCodeVisibility($container, $countrySelect) {
            var hasPostalCode = String($countrySelect.find('option:selected').data('haspostalcode') || '0');
            var $zipRow = $container.find('.zipcode_tr');
            var $postal = $container.find('.postal_code');
            if (!$postal.length || !$zipRow.length) return;
            // Postal code is managed in Addresses tab; never show on profile.
            if ($zipRow.hasClass('crm-profile-location-field')) {
                $zipRow.hide();
                $postal.prop('required', false);
                $postal.prop('disabled', true);
                return;
            }
            var isEditMode = $.trim(String($container.find('.add_button').text() || '')) === 'Save';

            if (hasPostalCode === '1') {
                // Country requires postal code: show and make mandatory.
                $zipRow.show();
                // Keep disabled until user clicks Edit.
                $postal.prop('disabled', !isEditMode);
                $postal.prop('required', true);
            } else {
                // Country does not use postal code: hide and make optional.
                $zipRow.hide();
                $postal.val('');
                $postal.prop('required', false);
                $postal.prop('disabled', true);
            }
        }

        function updateTaxLabelByCountry($container, $countrySelect) {
            var $gstnRow = $container.find('.gstn');
            if (!$gstnRow.length) return;
            var defaultLabel = String($gstnRow.data('default-tax-label') || '').trim();
            var selectedLabel = String($countrySelect.find('option:selected').data('taxlabel') || '').trim();
            var effectiveLabel = selectedLabel !== '' ? selectedLabel : defaultLabel;
            if (effectiveLabel === '') return;
            $container.find('.gstn_label_text').text(effectiveLabel);
            $container.find('.gstn_label_error_text').text(effectiveLabel);
        }

        $('.country_name').on('change', function() {
            var $container = $(this).closest('.edit-customer-details');
            var countryId = $(this).val();
            $container.data('selected-country', countryId);
            syncPostalCodeVisibility($container, $(this));
            updateTaxLabelByCountry($container, $(this));
            
            if(countryId == 'India') {
                $('.vat').hide();
                $('.gstn').show();
            }else if(countryId == "UAE"){
                $('.vat').show();
                $('.gstn').hide();
            }
            var selectedState = $(this).data('selected-state') || '';
            getStates(countryId, $container, selectedState);
        });

        // Apply same rule on first load based on current selected country.
        $('.country_name').each(function () {
            var $container = $(this).closest('.edit-customer-details');
            syncPostalCodeVisibility($container, $(this));
            updateTaxLabelByCountry($container, $(this));
        });


    $('.state_id').on('change', function() {
        var selectedValue = $(this).find('option:selected').text(); 
        var selectedState = selectedValue;
        set_state(selectedState);
        
    });
    // });
    function set_state(state) {
        if (state == 'other' || state == '') {

            $('.state_code').attr('readonly', false);
            $('.state_id').attr('readonly', false);

            $('.state_code').val('');
            // $('.state_id').val('');
        } else {
            let str = state;
            const myArr = str.split('~');

            $('.state_code').val(myArr[1]);
            $('.state_code').attr('readonly', true);
            // $('#state').attr('readonly', true);
        }
    }
    $('.pan_no').on('input', function () {
        $(this).val($(this).val().toUpperCase());
    });
    $(document).on('input', '.gstn_no', function() {
    $(this).val($(this).val().toUpperCase()); // Converts input to uppercase
});

</script>
<script>
    $(".cust_email").focusout(function () {
        var $this = $(this);
        var email = $this.val().trim();

        if (email === '') { // empty allowed; hide errors and skip
            $(".cust_email-errorss").hide();
            $(".cust_email-exists").hide();
            return;
        }

        // Validate format before AJAX uniqueness check
        var emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
        if (!emailPattern.test(email)) {
            $(".cust_email-errorss").show();
            $(".cust_email-exists").hide();
            $(".add_button").prop('disabled', true);
            return;
        } else {
            $(".cust_email-errorss").hide();
        }

        $.ajax({
            type: "get",
            url: '<?php echo base_url(); ?>customers/getEmail',
            data: {emailid: email},
            success: function (response) {
                if (response == 0) {
                    $(".cust_email-exists").show();
                    $(".add_button").prop('disabled', true);
                } else {
                    $(".cust_email-exists").hide();
                    validateFields(); // re-evaluate overall validity
                }
            },
        });
    });

    (function initPriceGroupSelect2() {
        if (typeof $ !== 'undefined' && $.fn.select2 && $('#price_group').length) {
            $('#price_group').select2({
                placeholder: 'Select Price Group',
                allowClear: true
            });
        } else {
            setTimeout(initPriceGroupSelect2, 50);
        }
    })();

</script>
<script>
    $('.city_name').on('input blur', function () {
    let value = $(this).val();

    // Allow only letters, space, dot, hyphen
    value = value.replace(/[^A-Za-z .-]/g, '');

    // Prevent multiple spaces
    value = value.replace(/\s+/g, ' ');

    // Remove leading spaces
    value = value.trimStart();

    $(this).val(value);
});
$('.pan_no').on('input blur', function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
    const panVal = this.value.trim();
    const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;
    const $panError = $(this).closest('td').find('.pan_no-errorss');

    if (panVal.length !== 10) {
        $panError.text("PAN must be exactly 10 characters").show();
        $('.add_button').prop('disabled', true);
    } else if (!panRegex.test(panVal)) {
        $panError.text("Invalid PAN format").show();
        $('.add_button').prop('disabled', true);
    } else {
        $panError.hide();
        validateFields(); // recheck all validations
    }
});
$('.gstn_no').on('input blur', function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 15);
    const gstn_no = this.value.trim();
    const gstRegex = /^[0-9A-Z]{15}$/;
    const $gstError = $(this).closest('td').find('.gstn_no-errorss');

    if (gstn_no.length !== 15) {
        $gstError.text("GSTIN must be exactly 15 characters").show();
        $('.add_button').prop('disabled', true);
    } else if (!gstRegex.test(gstn_no)) {
        $gstError.text("Invalid GSTIN format").show();
        $('.add_button').prop('disabled', true);
    } else {
        $gstError.hide();
        validateFields(); // recheck all validations
    }
});

$(".cust_name").on("input blur", function () {
    let value = $(this).val();

    // Allow alphabets and spaces only
    let cleaned = value.replace(/[^A-Za-z ]/g, '');

    // Prevent multiple spaces
    cleaned = cleaned.replace(/\s+/g, ' ');

    // Remove space at start
    cleaned = cleaned.trimStart();

    $(this).val(cleaned);
});
$('.cust_phone').on('input blur', function () {
    let $container = $(this).closest('.edit-customer-details');
    let country = String($container.attr('data-biller-country') || '').trim();
    let requiredDigits = parseInt($container.attr('data-biller-phone-digits'), 10) || 10;
    let val = $(this).val();
    let $error = $(this).closest('td').find('.phone-error');

    // Allow only digits
    val = val.replace(/\D/g, '');

    if (requiredDigits > 0) {
        if (val.length !== requiredDigits) {
            $error.text("Phone number must be exactly " + requiredDigits + " digits for " + country).show();
            $(".add_button").prop('disabled', true);
        } else if (val.startsWith('0')) {
            $error.text("Phone number cannot start with 0").show();
            $(".add_button").prop('disabled', true);
            val = val.replace(/^0+/, '');
        } else {
            $error.hide();
            $(".add_button").prop('disabled', false);
        }
    }

    // Prevent starting with 0
    if (val.startsWith('0')) {
        $("#error").text("Phone number cannot start with 0").show();
        val = val.replace(/^0+/, "");  // remove leading zeros
    } else {
        $("#error").hide();
    }

    $(this).val(val);
});


/* -----------------------------------
   ALLOW ONLY DIGITS, +, SPACE
----------------------------------- */
function IsNumeric(evt) {
    var charCode = evt.which ? evt.which : evt.keyCode;

    // Allow digits only
    if (charCode >= 48 && charCode <= 57) return true;

    return false;
}

$(document).ready(function () {

    function normalizeDobDisplayValue(value) {
        value = String(value || '').trim();
        if (!value) {
            return '';
        }
        // 1899-12-31 00:00(:00) -> 31/12/1899
        var ymd = value.match(/(\d{4})-(\d{2})-(\d{2})/);
        if (ymd) {
            return ymd[3] + '/' + ymd[2] + '/' + ymd[1];
        }
        // Keep already formatted DD/MM/YYYY (strip any trailing time)
        var dmy = value.match(/(\d{2})\/(\d{2})\/(\d{4})/);
        if (dmy) {
            return dmy[1] + '/' + dmy[2] + '/' + dmy[3];
        }
        return value;
    }

    function forceDobDisplayFormat() {
        $('.dob').each(function () {
            var formatted = normalizeDobDisplayValue($(this).val());
            if (formatted !== $(this).val()) {
                $(this).val(formatted);
            }
        });
    }

    function dobStringToLocalDate(str) {
        str = (str || '').trim();
        if (!str) {
            return null;
        }
        if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
            var p = str.split('-');
            return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
        }
        if (str.indexOf('/') !== -1) {
            var parts = str.split('/');
            if (parts.length === 3) {
                // POS / site format is usually DD/MM/YYYY (matches retrieveFormData in edit_customer_details.js)
                var d = parseInt(parts[0], 10);
                var m = parseInt(parts[1], 10) - 1;
                var y = parseInt(parts[2], 10);
                var dt = new Date(y, m, d);
                return isNaN(dt.getTime()) ? null : dt;
            }
        }
        var parsed = new Date(str);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function isDobInFuture(localDate) {
        if (!localDate || isNaN(localDate.getTime())) {
            return false;
        }
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var cmp = new Date(localDate.getFullYear(), localDate.getMonth(), localDate.getDate());
        return cmp > today;
    }

    function setDobPickerEndToday() {
        var $dob = $('.dob');
        if (!$dob.length || !$.fn.datetimepicker) {
            return;
        }
        try {
            if ($dob.data('datetimepicker')) {
                $dob.datetimepicker('setEndDate', new Date());
            }
        } catch (e) { /* ignore if plugin API differs */ }
    }

    function validateDOB() {
        forceDobDisplayFormat();
        var $dob = $('.dob');
        var dobRaw = $dob.val().trim();
        var $err = $('.dob-future-error');

        if (!dobRaw) {
            $err.hide();
            return;
        }

        var local = dobStringToLocalDate(dobRaw);
        if (!local) {
            $err.hide();
            return;
        }

        if (isDobInFuture(local)) {
            toastr.error('Future date is not allowed for Date of Birth');
            $err.show();
            $dob.val('');
        } else {
            $err.hide();
        }
    }

    // DOB should be date-only like Anniversary (no time selection).
    $(document).on('focus', '.dob', function() {
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
        } else {
            setDobPickerEndToday();
        }
    });
    $(document).on('shown.bs.modal', '.modal', function () {
        if ($(this).find('.dob').length) {
            setDobPickerEndToday();
        }
    });
    setTimeout(setDobPickerEndToday, 0);
    setTimeout(forceDobDisplayFormat, 0);
    setTimeout(forceDobDisplayFormat, 400);
    setTimeout(forceDobDisplayFormat, 1200);

    // Validate DOB on input, change, and focusout
    $('.dob').on('change input focusout changeDate dp.change', function () {
        forceDobDisplayFormat();
        validateDOB();
    });

    // Custom initialization for Anniversary field to allow future dates
    $(document).on('focus', '.anniversary', function() {
        if (!$(this).data('datetimepicker')) {
            $(this).datetimepicker({
                format: site.dateFormats.js_sdate,
                fontAwesome: true,
                language: 'sma',
                todayBtn: 1,
                autoclose: 1,
                minView: 2,
                endDate: false // Expressly allow future dates
            });
            $(this).datetimepicker('show');
        }
    });

});
</script>

