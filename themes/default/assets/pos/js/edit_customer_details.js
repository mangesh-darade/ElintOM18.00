function crmHasSelect2($el) {
    return $el && $el.length && (!!$el.data('select2') || $el.hasClass('select2-offscreen') || $el.siblings('.select2-container').length > 0);
}

function crmSetSelectEnabled($select, enabled) {
    if (!$select || !$select.length) {
        return;
    }
    $select.prop('disabled', !enabled);
    if (crmHasSelect2($select)) {
        try {
            $select.select2(enabled ? 'enable' : 'disable');
        } catch (e) {}
    }
}

function crmSetProfileFieldsEnabled($container, enabled) {
    $container = $container && $container.length ? $container : $();
    if (!$container.length) {
        return;
    }
    $container.find('.disebledForm').each(function () {
        var $el = $(this);
        if ($el.is('select')) {
            crmSetSelectEnabled($el, enabled);
        } else {
            $el.prop('disabled', !enabled);
        }
    });
}

function crmReadPriceGroupSelection($container) {
    var $pg = $container.find('select.price_group').first();
    if (!$pg.length) {
        return { id: '', name: '' };
    }
    var pgId = '';
    if (crmHasSelect2($pg)) {
        try {
            pgId = $pg.select2('val');
        } catch (e1) {}
    }
    if (pgId == null || pgId === undefined || pgId === '') {
        pgId = $pg.val();
    }
    pgId = pgId == null ? '' : String(pgId);
    var pgName = '';
    if (pgId !== '') {
        pgName = String($pg.find('option').filter(function () {
            return String($(this).attr('value')) === pgId;
        }).first().text() || '').trim();
    }
    return { id: pgId, name: pgName };
}

function crmApplyPriceGroupSelection($container, groupId) {
    var $pg = $container.find('select.price_group').first();
    if (!$pg.length) {
        return;
    }
    groupId = groupId == null ? '' : String(groupId);
    $pg.val(groupId);
    if (crmHasSelect2($pg)) {
        try {
            $pg.select2('val', groupId || '');
        } catch (e1) {
            $pg.trigger('change');
        }
    } else {
        $pg.trigger('change');
    }
}

function crmReadCustomerGroupSelection($container) {
    var $cg = $container.find('select.customer_groupId').first();
    if (!$cg.length) {
        return { id: '', name: '' };
    }
    var cgId = '';
    if (crmHasSelect2($cg)) {
        try {
            cgId = $cg.select2('val');
        } catch (e1) {}
    }
    if (cgId == null || cgId === undefined || cgId === '') {
        cgId = $cg.val();
    }
    cgId = cgId == null ? '' : String(cgId);
    var cgName = '';
    if (cgId !== '') {
        cgName = String($cg.find('option').filter(function () {
            return String($(this).attr('value')) === cgId;
        }).first().text() || '').trim();
    }
    return { id: cgId, name: cgName };
}

function crmApplyCustomerGroupSelection($container, groupId) {
    var $cg = $container.find('select.customer_groupId').first();
    if (!$cg.length) {
        return;
    }
    groupId = groupId == null ? '' : String(groupId);
    $cg.val(groupId);
    if (crmHasSelect2($cg)) {
        try {
            $cg.select2('val', groupId || '');
        } catch (e1) {
            $cg.trigger('change');
        }
    } else {
        $cg.trigger('change');
    }
}

function crmSyncMembersCardAlias($container) {
    var $c = $container && $container.length ? $container : $('.edit-customer-details').first();
    var $cf1 = $c.find('.cf1').first();
    var $ccf1 = $c.find('.ccf1').first();
    if (!$cf1.length || !$ccf1.length) {
        return;
    }

    var cf1Val = ($cf1.val() || '').trim();
    var ccf1Val = ($ccf1.val() || '').trim();

    if (ccf1Val !== '' && ccf1Val !== cf1Val) {
        $cf1.val($ccf1.val());
        return;
    }
    if (ccf1Val === '' && cf1Val !== '') {
        $ccf1.val($cf1.val());
    }
}


$(document).ready(function () {

    // $('#add_button').on('click', function () {
    //     var formData = {
    //         name: $('#customers_name').val(),
    //         priceGroup: $('#price_group').val(),
    //         companyName: $('#company').val(),
    //         membersCardNo: $('#ccf1').val(),
    //         customerGroup: $('#customer_group').val(),
    //         vatNo: $('#vat_no').val(),
    //         gstnNo: $('#gstn_no').val(),
    //         panNo: $('#pan_no').val(),
    //         stateCode: $('#state_code').val(),
    //         dobChild1: $('#dob_child1').val(),
    //         dobChild2: $('#dob_child2').val(),
    //         dobFather: $('#dob_father').val(),
    //         dobMother: $('#dob_mother').val(),
    //         eshopPassword: $("input[name='eshop_password']").val(),
    //         syncData: $("select[name='sync_data']").val(),
    //         phone: $('#phone').val(),
    //         email: $('#email').val(),
    //         address: $('#address').val(),
    //         country: $('#add_country').val(),
    //         stateName: $('#statename').val(),
    //         city: $('#city').val(),
    //         postalCode: $('#postal_code').val()
    //     };
    //     localStorage.setItem('formData', JSON.stringify(formData));
    //     toastr.success('Customer Details Save Successfully');
    //     setTimeout(function() {
    //         toastr.clear();  
    //     }, 3000);
    // });
    $('.edit-customer-details').each(function () {
        crmSetProfileFieldsEnabled($(this), false);
        var digits = parseInt($(this).attr('data-biller-phone-digits'), 10) || 10;
        $(this).find('.cust_phone').attr('maxlength', digits);
    });
    function resolveRequiredPhoneDigits($container) {
        $container = $container && $container.length ? $container : $('.edit-customer-details').first();
        var billerDigits = parseInt($container.attr('data-biller-phone-digits'), 10);
        if (!isNaN(billerDigits) && billerDigits > 0) {
            return billerDigits;
        }
        return 10;
    }

    function resolvePhoneValidationCountryName($container) {
        $container = $container && $container.length ? $container : $('.edit-customer-details').first();
        var billerCountry = String($container.attr('data-biller-country') || '').replace(/\s+/g, ' ').trim();
        if (billerCountry) {
            return billerCountry;
        }
        return resolveSelectedCountryName($container);
    }

    function resolveSelectedCountryName($container) {
        $container = $container && $container.length ? $container : $('.edit-customer-details').first();
        var $country = $container.find('.country_name');
        if (!$country.length) {
            return '';
        }

        // Prefer the latest explicit user selection captured on change.
        var countryName = String($container.data('selected-country') || '').replace(/\s+/g, ' ').trim();
        if (countryName) {
            return countryName;
        }

        // Then value from the active select element.
        countryName = String($country.val() || '').replace(/\s+/g, ' ').trim();
        if (countryName) {
            return countryName;
        }

        // Fallback to selected option text if value is unexpectedly empty.
        countryName = String($country.find('option:selected').text() || '').replace(/\s+/g, ' ').trim();
        if (countryName && countryName.toLowerCase() !== 'select country') {
            return countryName;
        }

        // Last fallback: keep latest user-selected country in container data.
        return String($container.data('selected-country') || '').replace(/\s+/g, ' ').trim();
    }
    // Delegated: CRM is injected via AJAX on sales/quotes (button not in DOM at first ready).
    $(document).off('click.crmCustomerSave', '#myModal .add_button, .mymodal .add_button, #paymentModal .add_button').on('click.crmCustomerSave', '#myModal .add_button, .mymodal .add_button, #paymentModal .add_button', function () {
        var buttonText = $.trim($(this).text());
        if (buttonText === 'Edit') {
            $(this).text('Save');

            crmSetProfileFieldsEnabled($(this).closest('.edit-customer-details'), true);
        } else if (buttonText === 'Save') {
            var $c = $(this).closest('.edit-customer-details');
            var pgSelection = crmReadPriceGroupSelection($c);
            var cgSelection = crmReadCustomerGroupSelection($c);

            // Validate phone number before saving
            var phone = $c.find('.cust_phone').val();
            var country = resolvePhoneValidationCountryName($c);
            var requiredDigits = resolveRequiredPhoneDigits($c);
            var isValidPhone = true;
            var phoneErrorMessage = '';
            var cleanPhone = (phone || '').replace(/\D/g, '');

            if (requiredDigits > 0 && cleanPhone.length !== requiredDigits) {
                isValidPhone = false;
                phoneErrorMessage = 'Phone number must be exactly ' + requiredDigits + ' digits for ' + country;
            }

            if (!isValidPhone) {
                if (typeof toastr !== 'undefined') {
                    toastr.error(phoneErrorMessage);
                } else {
                    bootbox.alert(phoneErrorMessage);
                }
                return false; // Stop form submission
            }

            // Postal code is managed in Addresses tab (profile location fields hidden).

            $(this).text('Edit');

            // Collect all form data
            function formatDate(inputDate) {
                if (!inputDate) return ''; // Return empty if no value

                var parts = inputDate.split('/'); // Split by "/"
                if (parts.length === 3) {
                    return parts[2] + '-' + parts[1] + '-' + parts[0]; // Rearrange to YYYY-MM-DD
                }
                return inputDate; // Return original if format is incorrect
            }
            var $stateSel = $c.find('select.state_id');
            var stateVal = '';
            if ($stateSel.length && $stateSel[0].options && $stateSel[0].selectedIndex >= 0) {
                stateVal = $stateSel[0].options[$stateSel[0].selectedIndex].value;
            } else {
                stateVal = $stateSel.val() || '';
            }
            var selectedCountryName = resolveSelectedCountryName($c);
            var useAddressTable = profileUsesAddressTable();
            var isPosSource = (String(window.location.pathname || '').toLowerCase().indexOf('/pos') !== -1)
                || $('#paymentModal').length > 0;
            crmSyncMembersCardAlias($c);
            var membersCardValue = ($c.find('.ccf1').val() || '').trim();
            var formData = {
                id: $c.find('.customer_id').val(),
                source_module: isPosSource ? 'pos' : 'customers',
                biller_id: isPosSource ? ($('#posbiller').val() || '').toString() : '',
                name: $c.find('.cust_name').val(),
                phone: $c.find('.cust_phone').val(),
                email: $c.find('.cust_email').val(),
                selected_country: selectedCountryName,
                phone_digits: resolveRequiredPhoneDigits($c),
                price_group_id: pgSelection.id,
                price_group_name: pgSelection.name,
                company: $c.find('.company').val(),
                customer_group_id: cgSelection.id,
                customer_group_name: cgSelection.name,
                is_internal_customer: ($c.find('.is_internal_customer').val() === 'yes') ? 'yes' : 'no',
                vat_no: $c.find('.vat_no').val(),
                gstn_no: $c.find('.gstn_no').val(),
                pan_card: $c.find('.pan_no').val(),
                password: $("input[name='eshop_password']").val(),
                is_synced: $("select[name='sync_data']").val(),
                dob: formatDate($c.find('.dob').val()),
                anniversary: formatDate($c.find('.anniversary').val()),
                award_points: $c.find('.award_points').val(),
                cf1: membersCardValue !== '' ? membersCardValue : $c.find('.cf1').val(),
                cf2: $c.find('.cf2').val(),
                cf3: $c.find('.cf3').val(),
                cf4: $c.find('.cf4').val(),
                cf5: $c.find('.cf5').val(),
                cf6: $c.find('.cf6').val(),
                location_id: $c.find('select[name="location_id"]').val(),
                customer_addresses: collectCustomerAddresses(),
                customer_addr_deleted: getDeletedCustomerAddressIds(),
            };
            localStorage.setItem('formData', JSON.stringify(formData));
            updateData(formData, $c);
            crmSetProfileFieldsEnabled($c, false);
            if (typeof toastr !== 'undefined') {
                setTimeout(function () {
                    toastr.clear();
                }, 3000);
            }
        }
    });

    $(document).off('click.crmCustomerSubmit', '#myModal .final-submit-btn, .mymodal .final-submit-btn, #paymentModal .final-submit-btn').on('click.crmCustomerSubmit', '#myModal .final-submit-btn, .mymodal .final-submit-btn, #paymentModal .final-submit-btn', function (e) {
        e.preventDefault();
        var formData = {};
        try {
            formData = JSON.parse(localStorage.getItem('formData')) || {};
        } catch (err) {
            formData = {};
        }
        if (!formData || typeof formData !== 'object') {
            formData = {};
        }
        // Always submit latest address cards (do not rely on stale localStorage snapshot).
        formData.customer_addresses = collectCustomerAddresses();
        formData.customer_addr_deleted = getDeletedCustomerAddressIds();
        if (!formData.id) {
            formData.id = $('.edit-customer-details .customer_id').first().val() || '';
        }
        $('#profile_details').val(JSON.stringify(formData));
        $('#submitForm').submit();
    });

});

$('#payment').click(function () {
    $('#showDivButton1').trigger('click');
    var getname = $('#customer_name').val();
    var phoneNumber = (getname).match(/\((\d+)\)/)[1];
    $.ajax({
        url: site.base_url + "customers/getCustomerDetails",
        type: "POST",
        data: { 'phone': phoneNumber },
        dataType: "json",
        success: function (response) {
            retrieveFormData(response);
        },
        error: function () {
            alert("Error occurred while fetching data!");
        }
    });
});

function retrieveFormData(formData) {
    var cust_grp_id = 0;

    if (formData && formData.id) {
        customer_id = formData.id;
        $('.customer_id').val(formData.id);
    }

    // Load addresses immediately from getCustomerDetails (do not wait for getCompanyDetails).
    syncCrmAddresses(parseServerAddressList(formData));

    function formatDobForDisplay(raw) {
        raw = String(raw || '').trim();
        if (!raw || raw === '0000-00-00' || raw === '0000-00-00 00:00:00') {
            return '';
        }
        var ymd = raw.match(/(\d{4})-(\d{2})-(\d{2})/);
        if (ymd) {
            return ymd[3] + '/' + ymd[2] + '/' + ymd[1];
        }
        var dmy = raw.match(/(\d{2})\/(\d{2})\/(\d{4})/);
        if (dmy) {
            return dmy[1] + '/' + dmy[2] + '/' + dmy[3];
        }
        return raw;
    }

    function sanitizeDobField() {
        $('.dob').each(function () {
            var v = $(this).val();
            var f = formatDobForDisplay(v);
            if (f !== v) {
                $(this).val(f);
            }
        });
    }
    // fetch customer group
    $.ajax({
        url: site.base_url + "pos/getCompanyDetails",
        type: "POST",
        data: { customer_id: formData.id },
        dataType: "json",
        success: function (response) {

            cust_grp_id = response.customer_group_id;
            $(".customer_groupId").val(response.customer_group_id);
            if (typeof $.fn.select2 !== 'undefined' && $(".customer_groupId").data('select2')) {
                try {
                    $(".customer_groupId").select2('val', response.customer_group_id || '');
                } catch (e) {}
            }
            $(".customer_groupId").change();
            var internalCustomer = 'no';
            if (formData && formData.is_internal_customer) {
                internalCustomer = String(formData.is_internal_customer).toLowerCase() === 'yes' ? 'yes' : 'no';
            } else if (response && response.is_internal_customer) {
                internalCustomer = String(response.is_internal_customer).toLowerCase() === 'yes' ? 'yes' : 'no';
            }
            $('.is_internal_customer').val(internalCustomer).change();
            if (formData) {
                var formattedCountry = '';
                if (response.country) {
                    formattedCountry = response.country.charAt(0).toUpperCase() + response.country.slice(1).toLowerCase();
                }
                var formattedState = '';
                if (response.state) {
                    formattedState = response.state.charAt(0).toUpperCase() + response.state.slice(1).toLowerCase();
                }
                let stateVal = response.state ? (response.state == "other" ? "" : response.state) : "";                
                customer_id = formData.id || ''; // Assign to global variable
                $('.customer_id').val(customer_id);
                // $('.country_name').val(response.country || '').trigger('change'); // Default to empty string if not defined
                // $('.country_name').val(formattedCountry).trigger('change');
                $('.country_name').val(response.country ? response.country : '');
                $('.country_name').data('selected-state', stateVal);
                $('.country_name').trigger('change');
                // $('.state_id').val(formData.state ? formData.state : '').trigger('change');
                $('.state_code').val(formData.state_code || ''); // 'state_code' in the response
                $('.city_name').val(formData.city || ''); // 'city' in the response
                // If customer has no postal code saved, show default placeholder-like value.
                $('.postal_code').val(formData.postal_code || '000000'); // 'postal_code' in the response



                function formatDate(dateStr) {
                    if (!dateStr) return '';
                    dateStr = String(dateStr).trim();
                    if (dateStr === '' || dateStr === '0000-00-00' || dateStr === '0000-00-00 00:00:00') {
                        return '';
                    }

                    // Match yyyy-mm-dd even if datetime exists: "1899-12-31 00:00"
                    var ymd = dateStr.match(/(\d{4})-(\d{2})-(\d{2})/);
                    if (ymd) {
                        return ymd[3] + '/' + ymd[2] + '/' + ymd[1];
                    }

                    // Match dd/mm/yyyy even if time suffix exists: "31/12/1899 00:00"
                    var dmy = dateStr.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                    if (dmy) {
                        return dmy[1] + '/' + dmy[2] + '/' + dmy[3];
                    }

                    return '';
                }

                var dob_date = formatDobForDisplay(formData.dob);
                $('.dob').val(dob_date);
                $('.anniversary').val(formatDate(formData.anniversary || ''));
                sanitizeDobField();
                // Some POS scripts can assign values after AJAX bind; keep DOB clean briefly.
                let dobSanitizeCount = 0;
                const dobSanitizeTimer = setInterval(function () {
                    sanitizeDobField();
                    dobSanitizeCount++;
                    if (dobSanitizeCount >= 15) { // ~4.5 sec
                        clearInterval(dobSanitizeTimer);
                    }
                }, 300);

                $('#dob_child1').val(formData.dob_child1 || ''); // 'dob_child1' in the response
                $('#dob_child2').val(formData.dob_child2 || ''); // 'dob_child2' in the response
                $('#dob_father').val(formData.dob_father || ''); // 'dob_father' in the response
                $('#dob_mother').val(formData.dob_mother || ''); // 'dob_mother' in the response
                $('.cf1').val(formData.cf1 || '');
                $('.ccf1').val(formData.cf1 || '');
                $('.cf2').val(formData.cf2 || '');
                $('.cf3').val(formData.cf3 || '');
                $('.cf4').val(formData.cf4 || '');
                $('.cf5').val(formData.cf5 || '');
                $('.cf6').val(formData.cf6 || '');
                $('.edit-customer-details select[name="location_id"]').val(formData.location_id || '').trigger('change');
                // $('#country').val(formData.country || '');        
                $('.address').val(formData.address || '');
                $('.edit-customer-details').each(function () {
                    crmApplyPriceGroupSelection($(this), formData.price_group_id || response.price_group_id || '');
                });
                $('#award_points').val(formData.award_points || '');
                $('#group_name').val(formData.group_name || '');
                $('.vat_no').val(formData.vat_no || '');
                $('.pan_no').val(formData.pan_card || '');
                // Add additional fields as needed
                $('.company').val(formData.company || ''); // 'company' in the response
                $('.cust_phone').val(formData.phone || ''); // 'phone' in the response
                $('.cust_email').val(formData.email || ''); // 'email' in the response
                $('.cust_name').val(formData.name || ''); // 'name' in the response
                $('.gstn_no').val(formData.gstn_no || ''); // 'gstn_no' in the response
                $('#logo').val(formData.logo || ''); // 'logo' in the response (if needed)

                if ($('.country_name').find('option:selected').data('haspostalcode') == '1') {
                    $('.zipcode_tr').show();
                    // $('.postal_code').val('');
                } else {
                    $('.zipcode_tr').hide();
                    $('.postal_code').val('000000');
                }

                let countryVal = $('.country_name').val();
                if (countryVal == 'India') {
                    $('.vat').hide();
                    $('.gstn').show();
                } else if (countryVal == "UAE") {
                    $('.vat').show();
                    $('.gstn').hide();
                }
            } else {
                console.log('No form data found');
            }
        },
        error: function () {
            alert("Error occurred while fetching data!");
        }
    });




}
function updateData(formData, $container) {

    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'post',
        url: site.base_url + "customers/save_customer",
        data: {
            'formData': formData
        },
        success: function (response) {
            // console.log(response);
            // exit;
            if (response.status) {
                if ($container && $container.length) {
                    crmApplyPriceGroupSelection($container, formData.price_group_id || '');
                    crmApplyCustomerGroupSelection($container, formData.customer_group_id || '');
                }
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    bootbox.alert(response.message);
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(response.message);
                } else {
                    bootbox.alert(response.message);
                }
            }
        },
        error: function () {
            if (typeof toastr !== 'undefined') {
                toastr.error("Something went wrong!");
            } else {
                bootbox.alert("Something went wrong!");
            }
        }
    });
}

///////// This function create for list customer screen edit action 
function customer_details(phone) {
    var phoneNumber = phone;
    $.ajax({
        url: site.base_url + "customers/getCustomerDetails",
        type: "POST",
        data: { 'phone': phoneNumber },
        dataType: "json",
        success: function (response) {
            console.log(response);
            retrieveFormData(response);
        },
        error: function () {
            alert("Error occurred while fetching data!");
        }
    });
}
$('#').click(function () {
    $('#showDivButton1').trigger('click');

    $(document).off('input.crmMembersCardAlias', '.edit-customer-details .ccf1').on('input.crmMembersCardAlias', '.edit-customer-details .ccf1', function () {
        var $c = $(this).closest('.edit-customer-details');
        $c.find('.cf1').val($(this).val());
    });

    $(document).off('input.crmCf1Alias', '.edit-customer-details .cf1').on('input.crmCf1Alias', '.edit-customer-details .cf1', function () {
        var $c = $(this).closest('.edit-customer-details');
        $c.find('.ccf1').val($(this).val());
    });
});

function getDeletedCustomerAddressIds() {
    if (!Array.isArray(window.crmDeletedAddressIds)) {
        window.crmDeletedAddressIds = [];
    }
    return window.crmDeletedAddressIds.slice(0);
}

function addDeletedCustomerAddressId(id) {
    id = parseInt(id, 10) || 0;
    if (id <= 0) {
        return;
    }
    if (!Array.isArray(window.crmDeletedAddressIds)) {
        window.crmDeletedAddressIds = [];
    }
    if (window.crmDeletedAddressIds.indexOf(id) === -1) {
        window.crmDeletedAddressIds.push(id);
    }
    $('.js-customer-address-deleted').val(JSON.stringify(window.crmDeletedAddressIds));
}

function resetDeletedCustomerAddressIds() {
    window.crmDeletedAddressIds = [];
    $('.js-customer-address-deleted').val('[]');
}

function getCrmAddressModal() {
    if (typeof window.SmaCustomerAddAddressModal === 'undefined') {
        return null;
    }
    var base = (typeof site !== 'undefined' && site.base_url) ? String(site.base_url) : '';
    if (base && base.slice(-1) !== '/') {
        base += '/';
    }
    if (!window.smaCrmAddressModal) {
        window.smaCrmAddressModal = new window.SmaCustomerAddAddressModal({
            instanceKey: 'crm',
            getStatesUrl: base + 'customers/getstates',
            // countryOptionsSelector: '.js-address-country-template',
            countryOptionsSelector: '#customer-addr-country-options-html',
            onSave: function (data) {
                persistCrmAddressFromModal(data);
            }
        });
    } else {
        window.smaCrmAddressModal.prepareModalElement();
    }
    return window.smaCrmAddressModal;
}

function getActiveCrmAddressBook() {
    var $book = $('.addressSection:visible .crm-address-book, #addressSection:visible .crm-address-book').first();
    if (!$book.length) {
        $book = $('.mymodal#modal-1:visible .crm-address-book, .mymodal.in .crm-address-book').first();
    }
    if (!$book.length) {
        $book = $('.crm-address-book').filter(function () {
            var $section = $(this).closest('.addressSection, #addressSection, .mymodal');
            return !$section.length || $section.is(':visible');
        }).first();
    }
    if (!$book.length) {
        $book = $('.crm-address-book').first();
    }
    return $book;
}

function normalizeAddressList(raw) {
    if (!raw) {
        return [];
    }
    if (Array.isArray(raw)) {
        return raw;
    }
    if (typeof raw === 'string') {
        try {
            raw = JSON.parse(raw);
        } catch (e1) {
            return [];
        }
        return normalizeAddressList(raw);
    }
    if (typeof raw === 'object') {
        return Object.keys(raw).map(function (key) {
            return raw[key];
        }).filter(function (item) {
            return item && typeof item === 'object';
        });
    }
    return [];
}

function extractPosCustomerPhone() {
    var getname = $('#customer_name').val();
    if (!getname) {
        return '';
    }
    var match = String(getname).match(/\((\d+)\)/);
    return match ? match[1] : '';
}

function refreshCrmAddressesTab() {
    if (typeof initCrmAddressBook === 'function') {
        initCrmAddressBook();
    }
    if (window.crmAddresses && window.crmAddresses.length) {
        if (typeof renderAddressCards === 'function') {
            renderAddressCards();
        }
        if (typeof getCrmAddressModal === 'function') {
            getCrmAddressModal();
        }
        return;
    }
    var phone = extractPosCustomerPhone();
    if (phone && typeof customer_details === 'function') {
        customer_details(phone);
        return;
    }
    if (typeof renderAddressCards === 'function') {
        renderAddressCards();
    }
    if (typeof getCrmAddressModal === 'function') {
        getCrmAddressModal();
    }
}

function parseServerAddressList(formData) {
    var serverAddresses = [];
    if (!formData) {
        return serverAddresses;
    }
    normalizeAddressList(formData.addresses).forEach(function (addr) {
        var addrType = normalizeCrmAddressType(addr.type);
        var addrCity = String(addr.city || '').trim();
        if (addrCity === '-') {
            addrCity = '';
        }
        serverAddresses.push({
            id: parseInt(addr.id, 10) || 0,
            type: addrType,
            address_name: String(addr.address_name || '').trim(),
            line1: String(addr.line1 || ''),
            country: String(addr.country || ''),
            state: String(addr.state || ''),
            state_code: String(addr.state_code || ''),
            city: addrCity,
            postal_code: String(addr.postal_code || '').trim(),
            is_default: parseInt(addr.is_default, 10) || 0
        });
    });
    return serverAddresses;
}

function syncCrmAddresses(serverAddresses) {
    serverAddresses = Array.isArray(serverAddresses) ? serverAddresses : [];
    window.crmAddresses = serverAddresses;
    var json = JSON.stringify(serverAddresses);
    $('.crm-address-book').attr('data-addresses', json);
    if (typeof renderAddressCards === 'function') {
        renderAddressCards();
    }
}

function initCrmAddressBook() {
    var customerId = $.trim(String($('.customer_id').first().val() || ''));
    if (window.crmAddresses && window.crmAddresses.length && customerId) {
        syncCrmAddresses(window.crmAddresses);
        return;
    }
    var $book = getActiveCrmAddressBook();
    if (!$book.length) {
        window.crmAddresses = [];
        return;
    }
    try {
        window.crmAddresses = JSON.parse($book.attr('data-addresses') || '[]');
    } catch (e) {
        window.crmAddresses = [];
    }
    if (!Array.isArray(window.crmAddresses)) {
        window.crmAddresses = [];
    }
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function normalizeCrmAddressType(type) {
   var lower = String(type || '').toLowerCase();
    if (lower === 'shipping') {
        return 'Shipping';
    }
    if (lower === 'site') {
        return 'Site';
    }
    return 'Billing';
}

function isCrmAddressDefault(addr) {
    if (!addr) {
        return false;
    }
    var flag = addr.is_default;
    return flag === 1 || flag === '1' || flag === true;
}

function buildAddressCardHtml(addr) {
    addr = addr || {};
    var id = parseInt(addr.id, 10) || 0;
    var type = normalizeCrmAddressType(addr.type);
    var isDefault = isCrmAddressDefault(addr);
    var addressName = $.trim(String(addr.address_name || ''));
    var line1 = $.trim(String(addr.line1 || ''));
    var country = $.trim(String(addr.country || ''));
    var state = $.trim(String(addr.state || ''));
    var city = $.trim(String(addr.city || ''));
    var postal = $.trim(String(addr.postal_code || ''));
    var cityLine = city;
    if (postal) {
        cityLine = cityLine ? (cityLine + ' - ' + postal) : postal;
    }
    var defaultBadge = isDefault
        ? '  <div class="crm-address-card-default-badge">Default</div>'
        : '';
    var setDefaultBtn = !isDefault && id > 0
        ? '  <div class="crm-address-card-footer">' +
          '    <button type="button" class="btn btn-default btn-xs js-set-default-address">Set to default</button>' +
          '  </div>'
        : '';
    return '' +
        '<div class="crm-address-card js-address-card' + (isDefault ? ' crm-address-card-default' : '') + '" data-id="' + id + '" data-type="' + escapeHtml(type) + '">' +
        '  <div class="crm-address-card-actions">' +
        '    <button type="button" class="btn btn-xs btn-default js-edit-address-card" title="Edit"><i class="fa fa-edit"></i></button>' +
        '    <button type="button" class="btn btn-xs btn-danger js-delete-address-card" title="Delete"><i class="fa fa-trash"></i></button>' +
        '  </div>' +
        defaultBadge +
        (addressName ? '  <div class="crm-address-card-name">' + escapeHtml(addressName) + '</div>' : '') +
        '  <div class="crm-address-card-line">' + escapeHtml(line1 || '—') + '</div>' +
        '  <div class="crm-address-card-meta">' +
        (country ? '<span><strong>Country:</strong> ' + escapeHtml(country) + '</span>' : '') +
        (state ? '<span><strong>State:</strong> ' + escapeHtml(state) + '</span>' : '') +
        (cityLine ? '<span><strong>City:</strong> ' + escapeHtml(cityLine) + '</span>' : '') +
        '  </div>' +
        setDefaultBtn +
        '</div>';
}

function sortCrmAddressesWithDefaultFirst(list) {
    return (list || []).slice().sort(function (a, b) {
        var aDef = isCrmAddressDefault(a) ? 1 : 0;
        var bDef = isCrmAddressDefault(b) ? 1 : 0;
        if (aDef !== bDef) {
            return bDef - aDef;
        }
        return (parseInt(a.id, 10) || 0) - (parseInt(b.id, 10) || 0);
    });
}

function renderAddressCards() {
    var billing = [];
    var shipping = [];
    var site = [];
    (window.crmAddresses || []).forEach(function (addr) {
        var type = normalizeCrmAddressType(addr.type);
        if (type === 'Shipping') {
            shipping.push(addr);
        } else if (type === 'Site') {
            site.push(addr);
        } else {
            billing.push(addr);
        }
    });
    billing = sortCrmAddressesWithDefaultFirst(billing);
    shipping = sortCrmAddressesWithDefaultFirst(shipping);
    site = sortCrmAddressesWithDefaultFirst(site);


    var billingHtml = billing.length
        ? billing.map(buildAddressCardHtml).join('')
        : '<div class="crm-addr-empty">No billing addresses</div>';
    var shippingHtml = shipping.length
        ? shipping.map(buildAddressCardHtml).join('')
        : '<div class="crm-addr-empty">No shipping addresses</div>';
        var siteHtml = site.length
        ? site.map(buildAddressCardHtml).join('')
        : '<div class="crm-addr-empty">No site addresses</div>';
    var json = JSON.stringify(window.crmAddresses || []);

    $('.crm-address-book').each(function () {
        var $book = $(this);
        $book.attr('data-addresses', json);
        var $billing = $book.find('.js-addr-cards-billing');
        var $shipping = $book.find('.js-addr-cards-shipping');
        var $site = $book.find('.js-addr-cards-site');
        if ($billing.length) {
            $billing.html(billingHtml);
        }
        if ($shipping.length) {
            $shipping.html(shippingHtml);
        }
        if ($site.length) {
            $site.html(siteHtml);
        }
    });
}

function getAddressFromCard($card) {
    var id = parseInt($card.attr('data-id'), 10) || 0;
    var found = null;
    (window.crmAddresses || []).some(function (addr) {
        if ((parseInt(addr.id, 10) || 0) === id) {
            found = addr;
            return true;
        }
        return false;
    });
    return found;
}

function collectAddressPayloadFromModal() {
    var modal = getCrmAddressModal();
    if (!modal) {
        return {};
    }
    var payload = modal.readFields();
    payload.type = normalizeCrmAddressType(payload.type);
    payload.line2 = '';
    return payload;
}

function isCrmCustomerModalOpen() {
    return $('#myModal').hasClass('in')
        || $('#myModal2').hasClass('in')
        || $('.mymodal#modal-1').is(':visible')
        || $('.modal.in').not('.bootbox').length > 0;
}

function restoreCrmParentModalState() {
    if (!isCrmCustomerModalOpen()) {
        return;
    }
    $('body').addClass('modal-open');
    var $parent = $('#myModal.in, #myModal2.in').first();
    if (!$parent.length) {
        $parent = $('.modal.in').not('.bootbox').first();
    }
    if ($parent.length) {
        $parent.css('display', 'block');
    }
    if ($('.modal-backdrop').length === 0) {
        $('<div class="modal-backdrop fade in"></div>').appendTo(document.body);
    }
}

function closeAddressModal() {
    var modal = getCrmAddressModal();
    if (modal) {
        modal.close();
    }
}

function initCrmAddressModalStack() {
    if (window.__crmAddressModalStackInit) {
        return;
    }
    window.__crmAddressModalStackInit = true;
    var $m = $('#addCustomerAddressModal');
    if (!$m.length) {
        return;
    }
    $m.on('hidden.bs.modal.crmAddrStack', function () {
        var $backdrops = $('.modal-backdrop');
        if ($backdrops.length > 1) {
            $backdrops.last().remove();
        }
        restoreCrmParentModalState();
    });
}

function initCrmBootboxStackFix() {
    if (window.__crmBootboxStackFixInit) {
        return;
    }
    window.__crmBootboxStackFixInit = true;

    $(document).on('hidden.bs.modal.crmBootboxStack', '.bootbox.modal', function () {
        var $backdrops = $('.modal-backdrop');
        if ($backdrops.length > 1) {
            $backdrops.last().remove();
        }
        restoreCrmParentModalState();
    });
}

function openAddressModal(mode, addr) {
    var modal = getCrmAddressModal();
    if (!modal) {
        return;
    }
    initCrmAddressModalStack();
    addr = addr || { type: 'Billing' };
    addr.type = normalizeCrmAddressType(addr.type || 'Billing');
    if (mode === 'edit' && addr.id) {
        addr.id = String(parseInt(addr.id, 10) || '');
    } else {
        addr.id = '';
    }
    modal.open(addr, mode === 'edit' ? 'Edit Address' : 'Add Address');
}

function persistCrmAddressFromModal(payload) {
    var companyId = $.trim(String($('.customer_id').first().val() || ''));
    if (!companyId) {
        toastr.error('Customer not found. Please reload customer details.');
        return;
    }
    payload = payload || collectAddressPayloadFromModal();
    payload.type = normalizeCrmAddressType(payload.type);
    var $btn = $('#btnSaveCustomerAddressModal');
    if ($btn.data('busy')) {
        return;
    }
    $btn.data('busy', true);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: site.base_url + 'customers/save_customer_address',
        data: {
            company_id: companyId,
            address: payload
        },
        success: function (response) {
            if (response && response.status) {
                var savedId = parseInt(response.address_id, 10) || parseInt(payload.id, 10) || 0;
                var list = window.crmAddresses || [];
                var idx = -1;
                var prevDefault = 0;
                list.forEach(function (item, i) {
                    if ((parseInt(item.id, 10) || 0) === savedId) {
                        idx = i;
                        prevDefault = isCrmAddressDefault(item) ? 1 : 0;
                    }
                });
                var entry = {
                    id: savedId,
                    type: payload.type,
                    address_name: payload.address_name,
                    line1: payload.line1,
                    country: payload.country,
                    state: payload.state,
                    state_code: payload.state_code || '',
                    city: payload.city,
                    postal_code: payload.postal_code,
                    is_default: prevDefault
                };
                if (idx < 0) {
                    var typeHasDefault = list.some(function (item) {
                        return normalizeCrmAddressType(item.type) === payload.type && isCrmAddressDefault(item);
                    });
                    if (!typeHasDefault) {
                        entry.is_default = 1;
                    }
                }
                if (idx >= 0) {
                    list[idx] = entry;
                } else {
                    list.push(entry);
                }
                window.crmAddresses = list;
                renderAddressCards();
                toastr.success(response.message || 'Address saved successfully.');
            } else {
                toastr.error((response && response.message) ? response.message : 'Failed to save address.');
            }
        },
        error: function () {
            toastr.error('Failed to save address.');
        },
        complete: function () {
            $btn.data('busy', false);
        }
    });
}

function confirmDeleteAddress(callback) {
    var message = 'Are you sure you want to delete this address?';
    if (typeof bootbox !== 'undefined' && typeof bootbox.confirm === 'function') {
        bootbox.confirm({
            title: 'Delete Address',
            message: message,
            buttons: {
                confirm: {
                    label: 'Delete',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Cancel',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (result) {
                    callback();
                }
            }
        });
        return;
    }
    if (window.confirm(message)) {
        callback();
    }
}

function confirmSetDefaultAddress(callback) {
    var message = 'Set this address as default?';
    if (typeof bootbox !== 'undefined' && typeof bootbox.confirm === 'function') {
        bootbox.confirm({
            title: 'Set default address',
            message: message,
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-primary'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (result) {
                    callback();
                }
            }
        });
        return;
    }
    if (window.confirm(message)) {
        callback();
    }
}

function applyDefaultAddressInList(addressId, addrType) {
    addressId = parseInt(addressId, 10) || 0;
    addrType = normalizeCrmAddressType(addrType);
    window.crmAddresses = (window.crmAddresses || []).map(function (addr) {
        var copy = $.extend({}, addr);
        if (normalizeCrmAddressType(copy.type) !== addrType) {
            return copy;
        }
        copy.is_default = (parseInt(copy.id, 10) || 0) === addressId ? 1 : 0;
        return copy;
    });
}

function setDefaultAddressCard(addressId) {
    var companyId = $.trim(String($('.customer_id').first().val() || ''));
    addressId = parseInt(addressId, 10) || 0;
    if (!companyId || addressId <= 0) {
        return;
    }
    var addr = null;
    (window.crmAddresses || []).some(function (item) {
        if ((parseInt(item.id, 10) || 0) === addressId) {
            addr = item;
            return true;
        }
        return false;
    });
    if (!addr) {
        toastr.error('Address not found.');
        return;
    }
    if (isCrmAddressDefault(addr)) {
        return;
    }
    confirmSetDefaultAddress(function () {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: site.base_url + 'customers/set_default_customer_address',
            data: {
                company_id: companyId,
                address_id: addressId
            },
            success: function (response) {
                if (response && response.status) {
                    applyDefaultAddressInList(addressId, addr.type);
                    renderAddressCards();
                    toastr.success(response.message || 'Default address updated successfully.');
                } else {
                    toastr.error((response && response.message) ? response.message : 'Failed to set default address.');
                }
            },
            error: function () {
                toastr.error('Failed to set default address.');
            }
        });
    });
}

function deleteAddressCard(addressId) {
    var companyId = $.trim(String($('.customer_id').first().val() || ''));
    addressId = parseInt(addressId, 10) || 0;
    if (!companyId || addressId <= 0) {
        return;
    }
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: site.base_url + 'customers/delete_customer_address',
        data: {
            company_id: companyId,
            address_id: addressId
        },
        success: function (response) {
            if (response && response.status) {
                var deletedAddr = null;
                (window.crmAddresses || []).some(function (addr) {
                    if ((parseInt(addr.id, 10) || 0) === addressId) {
                        deletedAddr = addr;
                        return true;
                    }
                    return false;
                });
                var deletedType = deletedAddr ? normalizeCrmAddressType(deletedAddr.type) : '';
                var wasDefault = deletedAddr && isCrmAddressDefault(deletedAddr);
                window.crmAddresses = (window.crmAddresses || []).filter(function (addr) {
                    return (parseInt(addr.id, 10) || 0) !== addressId;
                });
                if (wasDefault && deletedType) {
                    var promoted = false;
                    (window.crmAddresses || []).forEach(function (addr) {
                        addr.is_default = 0;
                        if (!promoted && normalizeCrmAddressType(addr.type) === deletedType) {
                            addr.is_default = 1;
                            promoted = true;
                        }
                    });
                }
                renderAddressCards();
                toastr.success(response.message || 'Address deleted successfully.');
            } else {
                toastr.error((response && response.message) ? response.message : 'Failed to delete address.');
            }
        },
        error: function () {
            toastr.error('Failed to delete address.');
        }
    });
}

function isValidAddressRow(row) {
    return (row.type === 'Shipping' || row.type === 'Billing' || row.type === 'Site')
        && row.address_name !== ''
        && row.line1 !== ''
        && row.state !== '';
}

function collectCustomerAddresses() {
    var rows = [];
    (window.crmAddresses || []).forEach(function (addr) {
        var row = {
            id: String(addr.id || ''),
            type: normalizeCrmAddressType(addr.type),
            address_name: $.trim(String(addr.address_name || '')),
            line1: $.trim(String(addr.line1 || '')),
            line2: '',
            country: $.trim(String(addr.country || '')),
            state: $.trim(String(addr.state || '')),
            state_code: '',
            city: $.trim(String(addr.city || '')),
            postal_code: $.trim(String(addr.postal_code || ''))
        };
        if (isValidAddressRow(row)) {
            rows.push(row);
        }
    });
    return rows;
}

function profileUsesAddressTable() {
    return collectCustomerAddresses().length > 0;
}

function bindCustomerAddressCardEvents() {
    if (window.__crmAddressCardEventsBound) {
        return;
    }
    window.__crmAddressCardEventsBound = true;

    $(document).on('click.crmAddr', '.js-add-address-btn', function (e) {
        e.preventDefault();
        openAddressModal('add', { type: 'Billing' });
    });

    $(document).on('click.crmAddr', '.js-edit-address-card', function (e) {
        e.preventDefault();
        var $card = $(this).closest('.js-address-card');
        var addr = getAddressFromCard($card);
        if (!addr) {
            toastr.error('Address not found.');
            return;
        }
        openAddressModal('edit', addr);
    });
    $(document).on('click.crmAddr', '#paymentModal .js-address-card', function (e) {
        if ($(e.target).closest('.crm-address-card-actions, .crm-address-card-footer, button, a').length) {
            return;
        }
        e.preventDefault();
        var $card = $(this);
        var addr = getAddressFromCard($card);
        if (!addr) {
            toastr.error('Address not found.');
            return;
        }
        openAddressModal('edit', addr);
    });

    $(document).on('click.crmAddr', '.js-delete-address-card', function (e) {
        e.preventDefault();
        var addressId = parseInt($(this).closest('.js-address-card').attr('data-id'), 10) || 0;
        if (addressId <= 0) {
            return;
        }
        confirmDeleteAddress(function () {
            deleteAddressCard(addressId);
        });
    });

    $(document).on('click.crmAddr', '.js-set-default-address', function (e) {
        e.preventDefault();
        var addressId = parseInt($(this).closest('.js-address-card').attr('data-id'), 10) || 0;
        if (addressId <= 0) {
            return;
        }
        setDefaultAddressCard(addressId);
    });

    $(document).on('click.crmAddr', '.showDivButton5', function () {
        setTimeout(function () {
            initCrmAddressBook();
            renderAddressCards();
            if (typeof getCrmAddressModal === 'function') {
                getCrmAddressModal();
            }
        }, 0);
    });
}

$(document).ready(function () {
    resetDeletedCustomerAddressIds();
    bindCustomerAddressCardEvents();
    if ($('#addCustomerAddressModal').length) {
        getCrmAddressModal();
    }
    initCrmAddressModalStack();
    initCrmBootboxStackFix();
    initCrmAddressBook();
    renderAddressCards();
});