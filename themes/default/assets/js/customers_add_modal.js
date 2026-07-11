function smaIsAutoCustomerNumberForm($form) {
    if (!$form || !$form.length) {
        return false;
    }
    return String($form.attr('data-auto-customer-number') || $form.data('autoCustomerNumber') || '') === '1';
}

function smaSyncAutoCustomerSystemGeneratedFlag($form) {
    if (!smaIsAutoCustomerNumberForm($form)) {
        return;
    }
    var $phone = $form.find('#phone');
    var $flag = $form.find('#is_system_generated');
    var $original = $form.find('#auto_phone_original');
    if (!$phone.length || !$flag.length) {
        return;
    }
    var current = String($phone.val() || '').replace(/\D+/g, '');
    var original = String($original.val() || '').replace(/\D+/g, '');
    $flag.val(original !== '' && current === original ? 'TRUE' : 'FALSE');
}

function smaIsUnchangedAutoCustomerPhone($form) {
    if (!smaIsAutoCustomerNumberForm($form)) {
        return false;
    }
    var current = String($form.find('#phone').val() || '').replace(/\D+/g, '');
    var original = String($form.find('#auto_phone_original').val() || '').replace(/\D+/g, '');
    return original !== '' && current === original;
}

function smaMaybeCheckCustomerPhoneDuplicate($form) {
    if (!$form || !$form.length || smaIsUnchangedAutoCustomerPhone($form)) {
        return;
    }
    var cd = smaParseAddCustomerCountryMeta($form);
    if (!cd) {
        return;
    }
    var phone = String($form.find('#phone').val() || '').replace(/\D+/g, '');
    var requiredLength = smaGetPhoneValidationDigits($form, cd);
    if (phone.length !== requiredLength) {
        return;
    }
    if (typeof checkmobileno === 'function') {
        checkmobileno('customer', phone, 'error', 'phone');
        return;
    }
    var pending = $form.data('smaDupPhonePending');
    if (pending === phone) {
        return;
    }
    $form.data('smaDupPhonePending', phone);
    var base = (typeof site !== 'undefined' && site.base_url) ? String(site.base_url) : '';
    if (base && base.slice(-1) !== '/') {
        base += '/';
    }
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: base + 'customers/checkMobileno',
        data: { groupname: 'customer', mobileno: phone },
        success: function (response) {
            $form.removeData('smaDupPhonePending');
            if (!response || response.status !== 'success') {
                return;
            }
            $form.find('#phone').val('').focus();
            if ($form.find('#is_system_generated').length) {
                $form.find('#is_system_generated').val('FALSE');
            }
            alert('Phone no already exists');
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
            if (response.id && typeof showEditCustomerModal === 'function') {
                showEditCustomerModal(response.id);
            }
        },
        error: function () {
            $form.removeData('smaDupPhonePending');
        }
    });
}

function smaValidateAutoCustomerPhoneDigits($form) {
    var $phone = $form.find('#phone');
    var $phoneError = $form.find('#phone_error');
    if (!$phone.length) {
        return true;
    }
    var phone = String($phone.val() || '').replace(/\D+/g, '');
    $phone.val(phone);
    if (!phone) {
        if ($phoneError.length) {
            $phoneError.text('Phone number is required.').show();
        }
        return false;
    }
    if (!/^\d{1,10}$/.test(phone)) {
        if ($phoneError.length) {
            $phoneError.text('Phone number must be up to 10 digits only.').show();
        }
        return false;
    }
    if ($phoneError.length) {
        $phoneError.hide();
    }
    return true;
}

function smaBindAutoCustomerSystemGeneratedFlag($form) {
    if (!smaIsAutoCustomerNumberForm($form)) {
        return;
    }
    $form.find('#phone').off('.smaSysGen').on('input change.smaSysGen', function () {
        var digits = String($(this).val() || '').replace(/\D+/g, '').substring(0, 10);
        if (String($(this).val() || '') !== digits) {
            $(this).val(digits);
        }
        smaValidateAutoCustomerPhoneDigits($form);
        smaSyncAutoCustomerSystemGeneratedFlag($form);
        smaMaybeCheckCustomerPhoneDuplicate($form);
    });
    $form.find('#phone').on('keypress.smaSysGen', function (e) {
        var keyCode = e.which ? e.which : e.keyCode;
        var isDigit = keyCode >= 48 && keyCode <= 57;
        var isControl = [8, 9, 13].indexOf(keyCode) !== -1;
        if (!isDigit && !isControl) {
            e.preventDefault();
            return;
        }
        if (isDigit && String(this.value || '').replace(/\D+/g, '').length >= 10) {
            e.preventDefault();
        }
    });
    smaValidateAutoCustomerPhoneDigits($form);
    smaSyncAutoCustomerSystemGeneratedFlag($form);
}

/**
 * Add customer (#add-customer-form): phone length + GST label by country.
 * Bootstrap runs $modal.load(remote) asynchronously; shown.bs.modal often fires before
 * the form exists, and <script> in the response is stripped. Delegated handlers + polling fix this.
 * Loaded after core.js from footer.php.
 */
function smaAddCustomerSetState(state, $form) {
    var $stateCode = $form.find('#state_code');
    var $stateName = $form.find('#statename');
    if (state == 'other' || state === '') {
        $stateCode.attr('readonly', false).val('');
        $stateName.attr('readonly', false).val('');
    } else {
        var raw = String(state);
        var myArr = raw.split('~');
        var stateName = myArr[0] != null ? String(myArr[0]).trim() : '';
        var stateCode = myArr[1] != null ? String(myArr[1]).trim() : '';

        // Some getstates responses use option value as name/id and keep code in option text.
        if (stateCode === '') {
            var txt = String($form.find('#state option:selected').text() || '').trim();
            var m = txt.match(/^(.*)\(([^)]+)\)\s*$/);
            if (m) {
                stateName = String(m[1] || '').trim();
                stateCode = String(m[2] || '').trim();
            } else if (stateName === '' || /^\d+$/.test(stateName)) {
                stateName = txt;
            }
        }

        $stateCode.val(stateCode).attr('readonly', true);
        $stateName.val(stateName).attr('readonly', true);
    }
}

function smaAddCustomerGetStates(country, $form, preserveCurrentState) {
    var $state = $form.find('#state');
    preserveCurrentState = !!preserveCurrentState;
    var currentState = $state.val();
    var base = (typeof site !== 'undefined' && site.base_url) ? String(site.base_url) : '';
    if (base && base.slice(-1) !== '/') {
        base += '/';
    }
    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'get',
        url: base + 'customers/getstates',
        data: { country: country },
        success: function (response) {
            if (response.status == 'success') {
                $state.html(response.data);
            } else {
                $state.html(response.data);
            }
            if (preserveCurrentState && currentState) {
                $state.val(currentState).trigger('change.select2');
            } else {
                try {
                    $state.select2('val', '');
                } catch (e1) {
                    $state.val('').trigger('change');
                }
            }
        }
    });
}

/** Parse country phone / tax JSON from hidden textarea (survives jQuery .load into #myModal; scripts do not). */
function smaParseAddCustomerCountryMeta($form) {
    var $t = $form.find('#add-customer-country-meta-json');
    if (!$t.length) {
        return null;
    }
    var raw = String($t.val() || $t.text() || '').replace(/^\uFEFF/, '').trim();
    if (!raw) {
        return null;
    }
    try {
        return JSON.parse(raw);
    } catch (e) {
        return null;
    }
}

/**
 * Reliable country name for validation after Select2: native .val() can lag or stay empty
 * on submit; select2('val') and #add_country (synced in change handler) match the real choice.
 */
function smaGetAddCustomerCountryName($form) {
    return smaGetSelectedCountryFromControl($form);
}

/** Phone length validation uses biller country when not auto-generated customer number. */
function smaGetPhoneValidationCountryName($form) {
    if (smaIsAutoCustomerNumberForm($form)) {
        return smaGetAddCustomerCountryName($form);
    }
    var billerCountry = String($form.attr('data-biller-country') || $form.find('#biller_phone_country').val() || '').trim();
    if (billerCountry) {
        return billerCountry;
    }
    return smaGetAddCustomerCountryName($form);
}

function smaGetPhoneValidationDigits($form, countryData) {
    var billerDigits = parseInt($form.attr('data-biller-phone-digits') || $form.find('#biller_phone_digits').val(), 10);
    if (!isNaN(billerDigits) && billerDigits > 0) {
        return billerDigits;
    }
    countryData = countryData || smaParseAddCustomerCountryMeta($form);
    var countryName = smaGetPhoneValidationCountryName($form);
    return smaGetRequiredPhoneDigits(countryData, countryName);
}

function smaGetSelectedCountryFromControl($form) {
    var $sel = $form.find('#country');
    if (!$sel.length) {
        return '';
    }
    var v = '';
    try {
        if ($sel.data('select2')) {
            v = $sel.select2('val');
        }
    } catch (e) {}
    if (v == null || v === '') {
        v = $sel.val();
    }
    return String(v || '').trim();
}

function smaNormalizeCountryToken(countryName) {
    var v = String(countryName || '').replace(/\s+/g, ' ').trim();
    var dup = v.match(/^(.+)\s+\1$/i);
    if (dup && dup[1]) {
        v = String(dup[1]).trim();
    }
    return v.toLowerCase().replace(/[^a-z0-9]/g, '');
}

function smaFindCountryMeta(countryData, countryName) {
    if (!countryData || !countryName) {
        return null;
    }
    var wantRaw = String(countryName).trim();
    if (!wantRaw) {
        return null;
    }
    var wantToken = smaNormalizeCountryToken(wantRaw);
    var aliases = {
        uae: 'unitedarabemirates',
        unitedarabemirates: 'uae'
    };
    var i;
    for (i = 0; i < countryData.length; i++) {
        if (String(countryData[i].name).trim() === wantRaw) {
            return countryData[i];
        }
    }
    for (i = 0; i < countryData.length; i++) {
        if (smaNormalizeCountryToken(countryData[i].name) === wantToken) {
            return countryData[i];
        }
    }
    if (aliases[wantToken]) {
        for (i = 0; i < countryData.length; i++) {
            if (smaNormalizeCountryToken(countryData[i].name) === aliases[wantToken]) {
                return countryData[i];
            }
        }
    }
    return null;
}

function smaGetRequiredPhoneDigits(countryData, countryName) {
    if (!countryData || !countryName) {
        return 10;
    }
    var found = smaFindCountryMeta(countryData, countryName);
    if (!found || found.phone_digits == null) {
        return 10;
    }
    var d = parseInt(found.phone_digits, 10);
    return isNaN(d) ? 10 : d;
}

function smaIsPostalCodeRequired(countryData, countryName) {
    var found = smaFindCountryMeta(countryData, countryName);
    if (!found) {
        return false;
    }
    return parseInt(found.postal_required, 10) === 1;
}

function smaSyncPostalCodeField($form, countryData, countryNameOverride) {
    if (!countryData) {
        return;
    }
    var countryName =
        countryNameOverride !== undefined && countryNameOverride !== null
            ? countryNameOverride
            : smaGetAddCustomerCountryName($form);
    var required = smaIsPostalCodeRequired(countryData, countryName);
    var $postal = $form.find('#postal_code');
    var $postalGroup = $form.find('#postal_code_group');
    if (!$postal.length || !$postalGroup.length) {
        return;
    }
    if (required) {
        $postalGroup.show();
        $postal.prop('disabled', false);
        $postal.prop('required', true);
    } else {
        $postal.val('');
        $postal.prop('required', false);
        $postal.prop('disabled', true);
        $postalGroup.hide();
        var $bv = $form.data('bootstrapValidator');
        if ($bv && typeof $bv.resetField === 'function' && $bv.options && $bv.options.fields && $bv.options.fields.postal_code) {
            try {
                $bv.resetField('postal_code', true);
            } catch (e) {}
        }
    }
}

function smaUpdateAddCustomerTaxLabel($form, countryData, countryNameOverride) {
    if (!countryData) {
        return;
    }
    var countryName =
        countryNameOverride !== undefined && countryNameOverride !== null
            ? countryNameOverride
            : smaGetAddCustomerCountryName($form);
    var found = smaFindCountryMeta(countryData, countryName);
    var text = found && found.tax_label ? found.tax_label : 'GSTIN (GST #)';
    var $lbl = $form.find('#gstn_label');
    if ($lbl.length) {
        $lbl.text(text);
    }
}

function smaSyncAddCustomerPhoneMaxlength($form, countryData, countryNameOverride) {
    var phoneInput = $form.find('#phone')[0];
    if (!phoneInput) {
        return;
    }
    if (smaIsAutoCustomerNumberForm($form)) {
        phoneInput.setAttribute('maxlength', '10');
        $form.data('smaPhoneDigits', 10);
        return;
    }
    var len = smaGetPhoneValidationDigits($form, countryData || smaParseAddCustomerCountryMeta($form));
    phoneInput.setAttribute('maxlength', String(len));
    if (phoneInput.value.length > len) {
        phoneInput.value = phoneInput.value.substring(0, len);
    }
    $form.data('smaPhoneDigits', len);
}

function smaValidateAddCustomerPhone($form, countryData) {
    if (smaIsAutoCustomerNumberForm($form)) {
        return true;
    }
    var phoneInput = $form.find('#phone')[0];
    var errorMsg = $form.find('#phone_error')[0];
    if (!phoneInput || !errorMsg) {
        return true;
    }
    var countryName = smaGetPhoneValidationCountryName($form);
    var phone = phoneInput.value.trim();
    var requiredLength = parseInt($form.data('smaPhoneDigits'), 10);
    if (isNaN(requiredLength) || requiredLength <= 0) {
        requiredLength = smaGetPhoneValidationDigits($form, countryData);
    }
    var isValidDigits = /^[1-9][0-9]*$/.test(phone);

    if (phone === '') {
        errorMsg.style.display = 'none';
        return true;
    }
    if (!isValidDigits) {
        errorMsg.textContent = 'Phone number must contain only digits and should not start with 0.';
        errorMsg.style.display = 'block';
        return false;
    }
    if (phone.length !== requiredLength) {
        var countryHint = countryName ? (' for ' + countryName) : '';
        errorMsg.textContent = 'Phone number must be exactly ' + requiredLength + ' digits' + countryHint + '.';
        errorMsg.style.display = 'block';
        return false;
    }
    errorMsg.style.display = 'none';
    return true;
}

function smaInitSelect2OnAddCustomerForm($form) {
    $form.find('select.select').each(function () {
        var $s = $(this);
        if ($s.data('select2')) {
            return;
        }
        try {
            $s.select2({ minimumResultsForSearch: 7 });
        } catch (e) {}
    });
}

/** Phone + tax for a known country name (Select2 3 often needs this after close, not only on change). */
function smaApplyAddCustomerPhoneAndTax($form, countryData, countryName) {
    if (countryData && countryName !== undefined && countryName !== null) {
        smaSyncAddCustomerPhoneMaxlength($form, countryData, countryName);
        if (!smaIsAutoCustomerNumberForm($form)) {
            smaValidateAddCustomerPhone($form, countryData);
        }
        smaUpdateAddCustomerTaxLabel($form, countryData, countryName);
        smaSyncPostalCodeField($form, countryData, countryName);
    } else if (smaIsAutoCustomerNumberForm($form)) {
        smaSyncAddCustomerPhoneMaxlength($form, countryData, countryName);
    }
}

function smaRunAddCustomerCountrySideEffects($form, selectedCountry) {
    var cd = smaParseAddCustomerCountryMeta($form);
    if (cd) {
        smaApplyAddCustomerPhoneAndTax($form, cd, selectedCountry);
    }

    var $state = $form.find('#state');
    $state.prop('disabled', false).trigger('change');

    if (selectedCountry == 'other') {
        $form.find('#add_country').attr('readonly', false).val('');
        $state.html('<option value="other" selected="selected" >Other</option>');
        $form.find('#state_code').attr('readonly', false);
        $form.find('#statename').attr('readonly', false);
        setTimeout(function () {
            try {
                $state.select2('val', 'other');
            } catch (e1) {
                try {
                    $state.val('other').trigger('change');
                } catch (e2) {}
            }
            $form.find('#state_code').val('');
            $form.find('#statename').val('');
        }, 100);
    } else {
        $form.find('#add_country').attr('readonly', true).val(selectedCountry);
        smaAddCustomerGetStates(selectedCountry, $form, false);
        setTimeout(function () {
            if (!$state.val()) {
                try {
                    $state.select2('val', '');
                } catch (e3) {
                    try {
                        $state.val('').trigger('change');
                    } catch (e4) {}
                }
                $form.find('#state_code').val('');
                $form.find('#statename').val('');
            }
        }, 100);
    }
}

/**
 * Direct bind on #country / #state after Select2 init — delegated document handlers miss
 * Select2 3 timing; select2-close runs when the value is final (instant phone error on country switch).
 */
function smaBindAddCustomerFormHandlers($form) {
    var $country = $form.find('#country');
    var $state = $form.find('#state');

    $country.off('.smaAddCust');
    $state.off('change.smaAddCust');

    $country.on('change.smaAddCust', function () {
        var selectedCountry = $(this).val();
        smaRunAddCustomerCountrySideEffects($form, selectedCountry);
        setTimeout(function () {
            var cd = smaParseAddCustomerCountryMeta($form);
            if (!cd) {
                return;
            }
            smaApplyAddCustomerPhoneAndTax($form, cd, smaGetAddCustomerCountryName($form));
        }, 50);
    });

    $country.on('select2-close.smaAddCust', function () {
        var cd = smaParseAddCustomerCountryMeta($form);
        if (!cd) {
            return;
        }
        smaApplyAddCustomerPhoneAndTax($form, cd, smaGetAddCustomerCountryName($form));
    });

    $state.on('change.smaAddCust', function () {
        smaAddCustomerSetState($(this).val(), $form);
    });
}

/**
 * Old Bootstrap: #myModal.load(url) is async; shown.bs.modal fires before HTML exists.
 * Poll until #add-customer-form is present, then init Select2 + labels + phone UI.
 */
function smaWhenAddCustomerFormReady($modal, callback, attempt) {
    attempt = attempt || 0;
    var $form = $modal.find('#add-customer-form');
    if ($form.length) {
        callback($form);
        return;
    }
    if (attempt < 50) {
        setTimeout(function () {
            smaWhenAddCustomerFormReady($modal, callback, attempt + 1);
        }, 50);
    }
}

$(document).ready(function () {
    function smaFallbackSyncTaxLabel($form) {
        var cd = smaParseAddCustomerCountryMeta($form);
        if (!cd) {
            return;
        }
        smaUpdateAddCustomerTaxLabel($form, cd, smaGetAddCustomerCountryName($form));
    }

    $(document).on('input', '#add-customer-form #phone', function () {
        var $form = $(this).closest('#add-customer-form');
        if ($form.attr('data-phone-dup-inline') === '1') {
            return;
        }
        if (smaIsAutoCustomerNumberForm($form)) {
            smaValidateAutoCustomerPhoneDigits($form);
            smaSyncAutoCustomerSystemGeneratedFlag($form);
            smaMaybeCheckCustomerPhoneDuplicate($form);
            return;
        }
        var cd = smaParseAddCustomerCountryMeta($form);
        if (!cd) {
            return;
        }
        smaValidateAddCustomerPhone($form, cd);
        var requiredLength = smaGetPhoneValidationDigits($form, cd);
        $form.data('smaPhoneDigits', requiredLength);
        smaMaybeCheckCustomerPhoneDuplicate($form);
    });

    /* Match add_quick.php: only cap length; digit filter is onkeypress="IsNumeric" on the input where used. */
    $(document).on('keypress', '#add-customer-form #phone', function (e) {
        var $form = $(this).closest('#add-customer-form');
        if (smaIsAutoCustomerNumberForm($form)) {
            var keyCode = e.which ? e.which : e.keyCode;
            var isDigit = keyCode >= 48 && keyCode <= 57;
            var isControl = [8, 9, 13].indexOf(keyCode) !== -1;
            if (!isDigit && !isControl) {
                e.preventDefault();
            } else if (isDigit && String(this.value || '').replace(/\D+/g, '').length >= 10) {
                e.preventDefault();
            }
            return;
        }
        var cd = smaParseAddCustomerCountryMeta($form);
        if (!cd) {
            return;
        }
        var requiredLength = smaGetPhoneValidationDigits($form, cd);
        if (this.value.length >= requiredLength) {
            e.preventDefault();
        }
    });

    // Fallback for pages where direct Select2 binding timing misses country change.
    $(document).on('change', '#add-customer-form #country', function () {
        var $form = $(this).closest('#add-customer-form');
        var selectedCountry = smaGetSelectedCountryFromControl($form) || String($(this).val() || '');
        if (selectedCountry) {
            smaRunAddCustomerCountrySideEffects($form, selectedCountry);
        }
        smaFallbackSyncTaxLabel($form);
    });

    $(document).on('select2-close', '#add-customer-form #country', function () {
        var $form = $(this).closest('#add-customer-form');
        var selectedCountry = smaGetSelectedCountryFromControl($form) || String($(this).val() || '');
        if (selectedCountry) {
            smaRunAddCustomerCountrySideEffects($form, selectedCountry);
        }
        smaFallbackSyncTaxLabel($form);
    });

    // Fallback: always update state name/code when state selection changes.
    $(document).on('change', '#add-customer-form #state', function () {
        var $form = $(this).closest('#add-customer-form');
        smaAddCustomerSetState($(this).val(), $form);
    });

    $(document).on('submit', '#add-customer-form', function (e) {
        var $form = $(this);
        if (smaIsAutoCustomerNumberForm($form)) {
            smaSyncAutoCustomerSystemGeneratedFlag($form);
            if (!smaValidateAutoCustomerPhoneDigits($form)) {
                e.preventDefault();
                $form.find('#phone').focus();
                return false;
            }
            return true;
        }
        var selectedCountry = smaGetSelectedCountryFromControl($form);
        if (selectedCountry && selectedCountry !== 'other') {
            $form.find('#add_country').val(selectedCountry);
        }
        var cd = smaParseAddCustomerCountryMeta($form);
        if (!cd) {
            return;
        }
        var phoneVal = String($form.find('#phone').val() || '').trim();
        if (phoneVal === '') {
            e.preventDefault();
            var em = $form.find('#phone_error');
            em.text('Phone number is required.').show();
            var ph0 = $form.find('#phone')[0];
            if (ph0) {
                ph0.focus();
            }
            return false;
        }
        if (!smaValidateAddCustomerPhone($form, cd)) {
            e.preventDefault();
            var ph = $form.find('#phone')[0];
            if (ph) {
                ph.focus();
            }
            return false;
        }
        return true;
    });

    function smaBootstrapAddCustomerForm($form) {
        smaInitSelect2OnAddCustomerForm($form);
        smaBindAddCustomerFormHandlers($form);
        if (smaIsAutoCustomerNumberForm($form)) {
            smaSyncAddCustomerPhoneMaxlength($form, null, null);
            smaBindAutoCustomerSystemGeneratedFlag($form);
        }
        var selectedCountry = smaGetSelectedCountryFromControl($form);
        if (selectedCountry && selectedCountry !== 'other') {
            $form.find('#add_country').attr('readonly', true).val(selectedCountry);
        }
        var cd = smaParseAddCustomerCountryMeta($form);
        if (cd) {
            var phoneCountry = smaIsAutoCustomerNumberForm($form)
                ? smaGetAddCustomerCountryName($form)
                : smaGetPhoneValidationCountryName($form);
            smaApplyAddCustomerPhoneAndTax($form, cd, phoneCountry);
        } else if (smaIsAutoCustomerNumberForm($form)) {
            smaSyncAddCustomerPhoneMaxlength($form, null, null);
        }
    }

    $('#myModal').on('shown.bs.modal', function () {
        var $m = $(this);
        smaWhenAddCustomerFormReady($m, function ($form) {
            smaBootstrapAddCustomerForm($form);
        });
    });

    var $addFormStandalone = $('#add-customer-form');
    if ($addFormStandalone.length && $addFormStandalone.closest('#myModal').length === 0) {
        smaBootstrapAddCustomerForm($addFormStandalone);
        // Extra initial pass after old plugins finish first paint/Select2 value sync.
        setTimeout(function () {
            smaFallbackSyncTaxLabel($addFormStandalone);
        }, 150);
    }
});
