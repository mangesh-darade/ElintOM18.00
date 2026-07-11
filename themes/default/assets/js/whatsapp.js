$(document).ready(function () {
    try {
        // Check if the button exists
        if ($('#send_whatsapp_app').length) {
            $('#send_whatsapp_app').click(function () {
                try {
                    if (typeof customerPhoneSystemGenerated !== 'undefined' && customerPhoneSystemGenerated) {
                        alert('WhatsApp message not sent: customer phone number is system generated.');
                        return;
                    }

                    // Validate customerPhone
                    if (typeof customerPhone !== 'undefined' && customerPhone) {
                        if (whatsapp_service == '1') {
                            var customer_Phone = askCustomerPhone(customerPhone);
                            if (customer_Phone === null || customer_Phone === false) {
                                return;
                            }

                            // Prepare phone number with country code
                            var finalPhone = customer_Phone;
                            if (typeof countryCode !== 'undefined' && countryCode && countryCode.trim() !== '') {
                                // Remove + if present and add country code
                                var cleanCountryCode = countryCode.replace(/^\+/, '');
                                // Remove country code from phone if already present
                                if (customer_Phone.startsWith(cleanCountryCode)) {
                                    finalPhone = customer_Phone;
                                } else {
                                    finalPhone = cleanCountryCode + customer_Phone;
                                }
                            }

                            // Check site.base_url
                            if (typeof site !== 'undefined' && site.base_url) {
                                var endpoint = "Whatsapp/send_whatsapp_message";
                                var pageReceiptType = (typeof window.receiptType !== 'undefined') ? window.receiptType : '';
                                var isChallanPath = /\/sales\/challan_view\/?/i.test(window.location.pathname || '');
                                if (pageReceiptType === 'challan' || isChallanPath) {
                                    endpoint = "Whatsapp/send_challan_whatsapp_message";
                                }
                                $.ajax({
                                    url: site.base_url + endpoint,
                                    type: 'GET',
                                    data: {
                                        phone: finalPhone,
                                        code: typeof receiptCode !== 'undefined' ? receiptCode : '',
                                        phone_system_generated: (typeof customerPhoneSystemGenerated !== 'undefined' && customerPhoneSystemGenerated) ? 1 : 0
                                    },
                                    dataType: 'json',
                                    success: function (response) {
                                        if (response && response.msg) {
                                            alert(response.msg);
                                        } else {
                                            alert("WhatsApp message sent successfully!");
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        console.error('AJAX Error:', status, error);
                                        alert('Failed to send WhatsApp message.');
                                    }
                                });
                            } else {
                                console.error('Error: site.base_url is not defined.');
                            }
                        } else {
                            alert("WhatsApp message are currently disabled. Please contact the administrator to enable this feature.");
                            return;
                        }

                    } else if (typeof whatsapp_service !== 'undefined' && whatsapp_service == '1') {
                        alert('WhatsApp message not sent: customer phone number is system generated.');
                    } else {
                        console.error('Error: customerPhone is not defined or empty.');
                    }
                } catch (clickHandlerError) {
                    console.error('Click Handler Error:', clickHandlerError);
                }
            });

        } else {
            console.warn('send_whatsapp_app button not found on this page.');
        }
    } catch (mainError) {
        console.error('Main WhatsApp script error:', mainError);
    }
    function askCustomerPhone(customerPhone) {
        try {
            // Get phone digits from global variable, default to 10 if not set
            var requiredDigits = typeof phoneDigits !== 'undefined' ? phoneDigits : 10;
            var countryCodeText = typeof countryCode !== 'undefined' && countryCode && countryCode.trim() !== '' 
                ? ' (Country Code: ' + countryCode + ', ' + requiredDigits + ' digits)' 
                : ' (' + requiredDigits + ' digits)';
            
            var customer_Phone = prompt("Please confirm or change the phone number" + countryCodeText + ":", customerPhone);

            // User pressed Cancel or left input empty
            if (customer_Phone === null || customer_Phone.trim() === "") {
                alert("Phone number is required.");
                return null;
            }

            // Remove all non-digit characters
            let cleanedPhone = customer_Phone.replace(/\D/g, '');
            
            // Remove country code if present at the beginning
            if (typeof countryCode !== 'undefined' && countryCode && countryCode.trim() !== '') {
                var cleanCountryCode = countryCode.replace(/^\+/, '').replace(/\D/g, '');
                if (cleanedPhone.startsWith(cleanCountryCode)) {
                    cleanedPhone = cleanedPhone.substring(cleanCountryCode.length);
                }
            }

            // Check if input contains the required number of digits
            if (cleanedPhone.length !== requiredDigits) {
                alert("Please enter a valid phone number with exactly " + requiredDigits + " digits.");
                return null;
            }

            // Extra check: make sure input was numeric (after cleaning)
            var digitPattern = new RegExp('^\\d{' + requiredDigits + '}$');
            if (!digitPattern.test(cleanedPhone)) {
                alert("Phone number must contain only numeric digits.");
                return null;
            }

            return cleanedPhone;
        } catch (error) {
            console.error('Error validating phone number:', error);
            alert("An unexpected error occurred while validating the phone number.");
            return null;
        }
    }


});
