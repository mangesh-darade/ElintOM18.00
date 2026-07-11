$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    var $customer = $('#slcustomer');
    $customer.change(function (e) {
        var customer_id = $(this).val();
        localStorage.setItem('slcustomer', customer_id);
        if (customer_id && /^\d+$/.test(String(customer_id))) {
            $('#quick_custome_nm').val(customer_id);
        }
        if (typeof getCustomerAddresses === 'function') {
            getCustomerAddresses(customer_id);
        }
        //$('#slcustomer_id').val($(this).val());
    });
    if (slcustomer = localStorage.getItem('slcustomer')) {
        $customer.val(slcustomer);
        if ($('#quick_custome_nm').length && /^\d+$/.test(String(slcustomer))) {
            $('#quick_custome_nm').val(slcustomer);
        }
        $customer.select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        $('#companyName').html(data[0].company_name);
                        callback(data[0]);                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        nsCustomer();
    }

// Order level shipping and discount localStorage
    if (sldiscount = localStorage.getItem('sldiscount')) {
        $('#sldiscount').val(sldiscount);
    }
    $('#sltax2').change(function (e) {
        localStorage.setItem('sltax2', $(this).val());
        $('#sltax2').val($(this).val());
    });
    if (sltax2 = localStorage.getItem('sltax2')) {
        $('#sltax2').select2("val", sltax2);
    }
    $('#slsale_status').change(function (e) {
        localStorage.setItem('slsale_status', $(this).val());
    });
    if (slsale_status = localStorage.getItem('slsale_status')) {
        $('#slsale_status').select2("val", slsale_status);
    }
    $('#slchallan_status').change(function (e) {
        localStorage.setItem('slchallan_status', $(this).val());
        loadItems();
    });
    if (slchallan_status = localStorage.getItem('slchallan_status')) {
        $('#slchallan_status').select2("val", slchallan_status);
    }
    $('#slpayment_status').change(function (e) {
        var ps = $(this).val();
        localStorage.setItem('slpayment_status', ps);
        if (ps == 'partial' || ps == 'paid') {
                       if (ps == 'paid') {
                var Amt1 = formatDecimal(parseFloat(((total + invoice_tax) - order_discount) + shipping));
				var round_total = roundNumberNEW(Amt1, Number(pos_settings.rounding));
                var rounding = formatDecimal(0 - (Amt1 - round_total));
                $('#amount_1').val(round_total);
				$('#Round_Off').html('(' + rounding + ')');
            }
            $('#payments').slideDown();
            $('#multi-payment').slideDown();
            $('#more_payment_block').slideDown();
            document.getElementById("more_payment_block").style.display = "block";
            $('#pcc_no_1').focus();
        } else {
            $('#payments').slideUp();
            $('#multi-payment').slideUp();
            $('#more_payment_block').slideUp();
            document.getElementById("more_payment_block").style.display = "none";
        }
    });
    if (slpayment_status = localStorage.getItem('slpayment_status')) {
        $('#slpayment_status').select2("val", slpayment_status);
        var ps = slpayment_status;
        if (ps == 'partial' || ps == 'paid') {
            $('#payments').slideDown();
            $('#multi-payment').slideDown();
            $('#more_payment_block').slideDown();
            document.getElementById("more_payment_block").style.display = "block";
            $('#pcc_no_1').focus();
        } else {
            $('#payments').slideUp();
            $('#multi-payment').slideUp();
            $('#more_payment_block').slideUp();
            document.getElementById("more_payment_block").style.display = "none";
        }
    }

    $(document).on('change', '.paid_by', function () {
        $('.final-btn').prop('disabled', false);
		var getid = $(this).attr('id').split("_");
        var p_id = getid[2];
		
        var p_val = $(this).val();
        localStorage.setItem('paid_by', p_val);
        $('#rpaidby').val(p_val);
        $('.g_transaction_id_' + p_id).show();
		$('.gc_' + p_id).hide();
		$('.cd_' + p_id).hide();
        if (p_val == 'cash') {
            $('.g_transaction_id_' + p_id).hide();
            $('.pcheque_' + p_id).hide();
            $('.pcc_' + p_id).hide();
            $('.pcash_' + p_id).show();
            $('#payment_note_1').focus();
        }
        if (p_val == 'other') {
           $('.pcheque_' + p_id).hide();
            $('.pcc_' + p_id).hide();
            $('.pcash_' + p_id).show();
            $('#payment_note_1').focus();
        } else if (p_val == 'CC') {
           $('.pcheque_' + p_id).hide();
            $('.pcash_' + p_id).hide();
            //$('.pcc_1').show();
            $('#pcc_no_' + p_id).focus();
        } else if (p_val == 'Cheque') {
            $('.g_transaction_id_' + p_id).hide();
			$('.gc_' + p_id).hide();
			$('.cd_' + p_id).hide();
            $('.pcc_' + p_id).hide();
            $('.pcash_' + p_id).hide();
            $('.pcheque_' + p_id).show();
            $('#cheque_no_' + p_id).focus();
        } else {
            $('.pcheque_' + p_id).hide();
            $('.pcc_' + p_id).hide();
            $('.pcash_' + p_id).hide();
        }
        if (p_val == 'deposit') {
            $('.g_transaction_id_' + p_id).hide();
        }
        if (p_val == 'gift_card') {
            $('.final-btn').prop('disabled', true);
            $('.g_transaction_id_' + p_id).hide();
            $('.gc_' + p_id).show();
            $('.ngc').hide();
            $('#gift_card_no_' + p_id).focus();
        } else {
            $('.ngc').show();
            $('.gc_' + p_id).hide();
            $('#gc_details_' + p_id).html('');
        }
		 if (p_val == 'credit_note') {
            $('.final-btn').prop('disabled', true);
            $('.g_transaction_id_' + p_id).hide();
            $('.cd_' + p_id).show();
            $('.ngc').hide();
            $('#credit_card_no_' + p_id).focus();
        } else {
            $('.ngc').show();
            $('.cd_' + p_id).hide();
            $('#credit_details_' + p_id).html('');
        }
    });

    if (paid_by = localStorage.getItem('paid_by')) {
        $('.final-btn').prop('disabled', false);
        var p_val = paid_by;
        $('.paid_by').select2("val", paid_by);
        $('#rpaidby').val(p_val);
        $('.g_transaction_id_1').show();
        if (p_val == 'cash') {
            $('.g_transaction_id_1').hide();
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').show();
            $('#payment_note_1').focus();
        }
        if (p_val == 'other') {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').show();
            $('#payment_note_1').focus();
        } else if (p_val == 'CC') {
            $('.pcheque_1').hide();
            $('.pcash_1').hide();
            //$('.pcc_1').show();
            $('#pcc_no_1').focus();
        } else if (p_val == 'Cheque') {
            $('.g_transaction_id_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').hide();
            $('.pcheque_1').show();
            $('#cheque_no_1').focus();
        } else {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').hide();
        }
        if (p_val == 'deposit') {
            $('.g_transaction_id_1').hide();
        }
        if (p_val == 'gift_card') {
            $('.final-btn').prop('disabled', true);
            $('.g_transaction_id_1').hide();
            $('.gc_1').show();
            $('.ngc').hide();
            $('#gift_card_no_1').focus();
        } else {
            $('.ngc').show();
            $('.gc_1').hide();
            $('#gc_details_1').html('');
        }
		if (p_val == 'credit_note') {
            $('.final-btn').prop('disabled', true);
            $('.g_transaction_id_1').hide();
            $('.cd_1').show();
            $('.ngc').hide();
            $('#credit_card_no_1').focus();
        } else {
            $('.ngc').show();
            $('.cd_1').hide();
            $('#credit_details_1').html('');
        }
    } else {
        $('.paid_by').trigger('change');
    }

    /*if (gift_card_no = localStorage.getItem('gift_card_no')) {
     $('#gift_card_no').val(gift_card_no);
     }*/
     $('.gift_card_no').change(function (e) {
        localStorage.setItem('gift_card_no', $(this).val());
    });
    $('.credit_card_no').change(function (e) {
        localStorage.setItem('credit_card_no', $(this).val());
    });

    if (amount_1 = localStorage.getItem('amount_1')) {
        $('#amount_1').val(amount_1);
    }
    $('#amount_1').change(function (e) {
        localStorage.setItem('amount_1', $(this).val());
    });

    if (paid_by_1 = localStorage.getItem('paid_by_1')) {
        $('#paid_by_1').val(paid_by_1);
    }
    $('#paid_by_1').change(function (e) {
        localStorage.setItem('paid_by_1', $(this).val());
    });

    if (pcc_holder_1 = localStorage.getItem('pcc_holder_1')) {
        $('#pcc_holder_1').val(pcc_holder_1);
    }
    $('#pcc_holder_1').change(function (e) {
        localStorage.setItem('pcc_holder_1', $(this).val());
    });

    if (pcc_type_1 = localStorage.getItem('pcc_type_1')) {
        $('#pcc_type_1').select2("val", pcc_type_1);
    }
    $('#pcc_type_1').change(function (e) {
        localStorage.setItem('pcc_type_1', $(this).val());
    });

    if (pcc_month_1 = localStorage.getItem('pcc_month_1')) {
        $('#pcc_month_1').val(pcc_month_1);
    }
    $('#pcc_month_1').change(function (e) {
        localStorage.setItem('pcc_month_1', $(this).val());
    });

    if (pcc_year_1 = localStorage.getItem('pcc_year_1')) {
        $('#pcc_year_1').val(pcc_year_1);
    }
    $('#pcc_year_1').change(function (e) {
        localStorage.setItem('pcc_year_1', $(this).val());
    });

    if (pcc_no_1 = localStorage.getItem('pcc_no_1')) {
        $('#pcc_no_1').val(pcc_no_1);
    }
    $('#pcc_no_1').change(function (e) {
        var pcc_no = $(this).val();
        localStorage.setItem('pcc_no_1', pcc_no);
        var CardType = null;
        var ccn1 = pcc_no.charAt(0);
        if (ccn1 == 4)
            CardType = 'Visa';
        else if (ccn1 == 5)
            CardType = 'MasterCard';
        else if (ccn1 == 3)
            CardType = 'Amex';
        else if (ccn1 == 6)
            CardType = 'Discover';
        else
            CardType = 'Visa';

        $('#pcc_type_1').select2("val", CardType);
    });

    if (cheque_no_1 = localStorage.getItem('cheque_no_1')) {
        $('#cheque_no_1').val(cheque_no_1);
    }
    $('#cheque_no_1').change(function (e) {
        localStorage.setItem('cheque_no_1', $(this).val());
    });

    if (payment_note_1 = localStorage.getItem('payment_note_1')) {
        $('#payment_note_1').redactor('set', payment_note_1);
    }
    $('#payment_note_1').redactor('destroy');
    $('#payment_note_1').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('payment_note_1', v);
        }
    });

    var old_payment_term;
    $('#slpayment_term').focus(function () {
        old_payment_term = $(this).val();
    }).change(function (e) {
        var new_payment_term = $(this).val() ? parseFloat($(this).val()) : 0;
        if ($(this).val() != '') {
            if (!is_numeric($(this).val())) {
                $(this).val(old_payment_term);
                bootbox.alert(lang.unexpected_value);
                return;
            } else {
                localStorage.setItem('slpayment_term', new_payment_term);
                $('#slpayment_term').val(new_payment_term);
            }
        } else {
            localStorage.setItem('slpayment_term', '');
            $('#slpayment_term').val('');
        }

    });
    if (slpayment_term = localStorage.getItem('slpayment_term')) {
        $('#slpayment_term').val(slpayment_term);
    }

    var old_shipping;
    $('#slshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if (!is_numeric($(this).val())) {
            //$(this).val(0);
           shipping = $(this).val() ? parseFloat($(this).val()) : '0'; 
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('slshipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (slshipping = localStorage.getItem('slshipping')) {
        shipping = parseFloat(slshipping);
        $('#slshipping').val(shipping);
    } else {
        shipping = 0;
    }
    $('#add_sale,  #print_invoice').attr('disabled', true); //#edit_sale,
    $(document).on('change', '.rserial', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        slitems[item_id].row.serial = $(this).val();
        localStorage.setItem('slitems', JSON.stringify(slitems));
    });

// If there is any item in localStorage
    if (localStorage.getItem('slitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('slitems')) {
                    localStorage.removeItem('slitems');
                }
                if (localStorage.getItem('sldiscount')) {
                    localStorage.removeItem('sldiscount');
                }
                if (localStorage.getItem('sltax2')) {
                    localStorage.removeItem('sltax2');
                }
                if (localStorage.getItem('slshipping')) {
                    localStorage.removeItem('slshipping');
                }
                if (localStorage.getItem('slref')) {
                    localStorage.removeItem('slref');
                }
                if (localStorage.getItem('slwarehouse')) {
                    localStorage.removeItem('slwarehouse');
                }
                if (localStorage.getItem('slnote')) {
                    localStorage.removeItem('slnote');
                }
                if (localStorage.getItem('slinnote')) {
                    localStorage.removeItem('slinnote');
                }
                if (localStorage.getItem('slcustomer')) {
                    localStorage.removeItem('slcustomer');
                }
                if (localStorage.getItem('slcurrency')) {
                    localStorage.removeItem('slcurrency');
                }
                if (localStorage.getItem('sldate')) {
                    localStorage.removeItem('sldate');
                }
                if (localStorage.getItem('slstatus')) {
                    localStorage.removeItem('slstatus');
                }
                if (localStorage.getItem('slbiller')) {
                    localStorage.removeItem('slbiller');
                }
                if (localStorage.getItem('gift_card_no')) {
                    localStorage.removeItem('gift_card_no');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

// save and load the fields in and/or from localStorage

    $('#slref').change(function (e) {
        localStorage.setItem('slref', $(this).val());
    });
    if (slref = localStorage.getItem('slref')) {
        $('#slref').val(slref);
    }

    $('#slwarehouse').change(function (e) {
        localStorage.setItem('slwarehouse', $(this).val());
    });
    if (slwarehouse = localStorage.getItem('slwarehouse')) {
        $('#slwarehouse').select2("val", slwarehouse);
    }

    $('#slnote').redactor('destroy');
    $('#slnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slnote', v);
        }
    });
    if (slnote = localStorage.getItem('slnote')) {
        $('#slnote').redactor('set', slnote);
    }
    $('#slinnote').redactor('destroy');
    $('#slinnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slinnote', v);
        }
    });
    if (slinnote = localStorage.getItem('slinnote')) {
        $('#slinnote').redactor('set', slinnote);
    }

    // prevent default action usln enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Order tax calculation
    if (site.settings.tax2 != 0) {
        $('#sltax2').change(function () {
            localStorage.setItem('sltax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calculation
    var old_sldiscount;
    $('#sldiscount').focus(function () {
        old_sldiscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
           /* localStorage.removeItem('sldiscount');
            localStorage.setItem('sldiscount', new_discount);
            loadItems();*/
            updateDoubleDiscount();
            return;
        } else {
            $(this).val(old_sldiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }

    });

    function updateDoubleDiscount() {
        var sldiscount = $('#sldiscount').val();
        setOrderAsItemsDiscount(sldiscount);
        loadItems();
    }

    function setOrderAsItemsDiscount(new_discount) {
       var ds = new_discount ? new_discount : null;
       if (is_valid_discount(ds)) {
                $('#posdiscount').val(ds);
                localStorage.removeItem('posdiscount');
                localStorage.setItem('posdiscount', ds);
                // $('#sldiscount').val('');
                localStorage.removeItem('sldiscount');
                localStorage.setItem('sldiscount', '');
                //loadItems();
        }

        return;
    }

    /* ----------------------
     * Delete Row Method
     * ---------------------- */
    $(document).on('click', '.sldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete slitems[item_id];
        row.remove();
        if (slitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems();
            return;
        }
    });


    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
    $(document).on('click', '.edit', function () {
        
        var row    = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id    = row.attr('data-item-id');
        item       = slitems[item_id];
        var tax_method = item.row.tax_method; 
      
        item = slitems[item_id];
         
        if (parseInt(site.settings.product_batch_setting) > 0) {
            var batch_number = row.children().children('.rbtach_no').val();
        }
        
        var qty = row.children().children('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            // unit_price = formatDecimal(row.children().children('.ruprice').val()), // existing
            unit_price = formatDecimal(row.find('input[name="unit_price[]"]').val()),
             product_option_color = row.children().children('.roption_color').val(),

            discount = row.children().children('.rdiscount').val();
        var cf1 = row.children().children('.cf1').val();
        var cf2 = row.children().children('.cf2').val();
        var manualedit = (item.row.manualedit) ? item.row.manualedit : '';
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == product_option && this.price != 0 && this.price != '' && this.price != null) {
                    // unit_price = parseFloat(item.row.price) + parseFloat(this.price);// existing
                    if(manualedit == '1'){
                        unit_price = parseFloat(item.row.price);
                    }else{
                        unit_price = parseFloat(item.row.price) + parseFloat(this.price);
                    }
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        // if (site.settings.theme == 'default' || site.settings.theme == 'newpos') { // product level tax
        //     if (site.settings.tax1) {
        //         $('#ptax').select2('val', item.row.tax_rate);
        //         $('#old_tax').val(item.row.tax_rate);
        //         var item_discount = 0, ds = discount ? discount : '0';
        //         if (ds.indexOf("%") !== -1) {
        //             var pds = ds.split("%");
        //             if (!isNaN(pds[0])) {
        //                 item_discount = formatDecimal(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
        //             } else {
        //                 item_discount = parseFloat(ds);
        //             }
        //         } else {
        //             item_discount = parseFloat(ds);
        //         }
        //         net_price -= item_discount;
        //         var pr_tax = item.row.tax_rate, pr_tax_val = 0;
        //         if (pr_tax !== null && pr_tax != 0) {
        //             $.each(tax_rates, function () {
        //                 if (this.id == pr_tax) {
        //                     if (this.type == 1) {
        //                         if (slitems[item_id].row.tax_method == 0) {
        //                             pr_tax_val = formatDecimal((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
        //                             pr_tax_rate = formatDecimal(this.rate) + '%';
        //                             net_price -= pr_tax_val;
        //                         } else {
        //                             pr_tax_val = formatDecimal((((net_price) * parseFloat(this.rate)) / 100), 4);
        //                             pr_tax_rate = formatDecimal(this.rate) + '%';
        //                         }
        //                     } else if (this.type == 2) {
        //                         pr_tax_val = parseFloat(this.rate);
        //                         pr_tax_rate = this.rate;

        //                     }
        //                 }
        //             });
        //         }
        //     }
        // }
        if (item.tax_method_type === undefined) {
             item.tax_method_type = (item.category_tax || (item.categoryTaxData && item.categoryTaxData.fix_tax_rate == 'fix tax' && !item.row.tax_rate)) ? 'category' : 'product';
        }
        $('#ptax').select2('val', item.row.tax_rate);
        if (site.settings.tax1) {
            if (item.fixtax && item.tax_method_type == 'category') {
                var taxvalue = item.fixtax;
                var exptax = taxvalue.split("~");
                $('#ptax').select2('val', exptax[0]);
            } else {
                    if (item.category_tax) {
                        $.each(item.category_tax, function (k, categorytax) {
                            var uptocheck = categorytax.upto;
                            if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                if (uptocheck) {
                                    if (categorytax.price >= net_price && unit_price <= uptocheck) {
                                        var taxvalue = categorytax.taxratevalue;
                                        var exptax = taxvalue.split("~");
                                        $('#ptax').select2('val', exptax[0]);
                                    } else {
                                        var taxvalue = categorytax.taxratevalue;
                                        var exptax = taxvalue.split("~");
                                        $('#ptax').select2('val', exptax[0]);
                                    }
                                } else {
                                    var taxvalue = categorytax.taxratevalue;
                                    var exptax = taxvalue.split("~");
                                    $('#ptax').select2('val', exptax[0]);
                                }

                            } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                if (uptocheck) {
                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                        var taxvalue = categorytax.taxratevalue;
                                        var exptax = taxvalue.split("~");
                                        $('#ptax').select2('val', exptax[0]);
                                    } else {
                                        var taxvalue = categorytax.taxratevalue;
                                        var exptax = taxvalue.split("~");
                                        $('#ptax').select2('val', exptax[0]);
                                    }
                                } else {
                                    var taxvalue = categorytax.taxratevalue;
                                    var exptax = taxvalue.split("~");
                                    $('#ptax').select2('val', exptax[0]);
                                }

                            }
                        });
                    }
                }
                $('#old_tax').val(item.row.tax_rate);
                var item_discount = 0, ds = discount ? discount : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        item_discount = formatDecimal(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
                    } else {
                        item_discount = parseFloat(ds);
                    }
                } else {
                    item_discount = parseFloat(ds);
                }
                // unit_price -= item_discount;
                net_price -= item_discount;

                var pr_tax = item.row.tax_rate, pr_tax_val = 0;
                if (pr_tax !== null && pr_tax != 0) {
                    $.each(tax_rates, function () {
                        if (this.id == pr_tax) {
                            if (this.type == 1) {
                                if (slitems[item_id].row.tax_method == 0) {
                                    if (item.fixtax && item.tax_method_type != 'product') {
                                        var taxvalue = item.fixtax;
                                        var exptax = taxvalue.split("~");

                                        pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1]))), 4);
                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                        net_price -= pr_tax_val;
                                    } else if (item.category_tax && item.tax_method_type != 'product') {
                                        if (item.category_tax) {
                                            $.each(item.category_tax, function (k, categorytax) {
                                                var uptocheck = categorytax.upto;
                                                if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                                    if (uptocheck) {
                                                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((net_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                        } else if (unit_price <= categorytax.price) {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                        }
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                    }

                                                } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {

                                                    if (uptocheck) {
                                                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                        } else {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                        }
                                                    } else if (unit_price >= categorytax.price) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                    }
                                                }
                                            });
                                        }
                                    } else {
                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                                        pr_tax_rate = formatDecimal(this.rate) + '%';
                                        net_price -= pr_tax_val;
                                    }
                                } else {
                                    if (item.fixtax && item.tax_method_type != 'product') {
                                        var taxvalue = item.fixtax;
                                        var exptax = taxvalue.split("~");

                                        pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / 100), 4);
                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                    } else if (item.category_tax && item.tax_method_type != 'product') {
                                        if (item.category_tax) {
                                            $.each(item.category_tax, function (k, categorytax) {
                                                var uptocheck = categorytax.upto;
                                                if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {

                                                    if (uptocheck) {
                                                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price);
                                                        } else {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price);
                                                        }
                                                    // } else if (unit_price >= categorytax.price) {
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price);
                                                        

                                                    }


                                                } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                                    if (uptocheck) {
                                                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price);
                                                        } else {
                                                            var taxvalue = categorytax.taxratevalue;
                                                            var exptax = taxvalue.split("~");
                                                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                            pr_tax = exptax[0];
                                                            net_price = parseFloat(unit_price);
                                                        }
                                                    // } else if (unit_price >= categorytax.price) {
                                                    } else  {

                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price);
                                                    }
                                                }
                                            });
                                        }
                                    } else {
                                        pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / 100), 4);
                                        pr_tax_rate = formatDecimal(this.rate) + '%';
                                    }
                                }

                            } else if (this.type == 2) {
                                if (item.fixtax && item.tax_method_type != 'product') {
                                    var taxvalue = item.fixtax;
                                    var exptax = taxvalue.split("~");

                                    pr_tax_val = parseFloat(exptax[1]);
                                    pr_tax_rate = exptax[0];
                                } else if (item.category_tax && item.tax_method_type != 'product') {
                                    if (item.category_tax) {
                                        $.each(item.category_tax, function (k, categorytax) {
                                            var uptocheck = categorytax.upto;
                                            if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                                if (uptocheck) {
                                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = parseFloat(exptax[1])
                                                        pr_tax_rate = exptax[1];
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price);
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = parseFloat(exptax[1])
                                                        pr_tax_rate = exptax[1];
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price);
                                                    }
                                                }
                                                else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = parseFloat(exptax[1])
                                                    pr_tax_rate = exptax[1];
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price);
                                                }
                                            } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                                if (uptocheck) {
                                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = parseFloat(exptax[1])
                                                        pr_tax_rate = exptax[1];
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price);
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = parseFloat(exptax[1])
                                                        pr_tax_rate = exptax[1];
                                                        pr_tax = exptax[0];
                                                        net_price = parseFloat(unit_price);
                                                    }
                                                }
                                                else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = parseFloat(exptax[1])
                                                    pr_tax_rate = exptax[1];
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price);
                                                }
                                            }
                                        });
                                    }
                                } else {
                                    pr_tax_val = parseFloat(this.rate);
                                    pr_tax_rate = this.rate;
                                }

                            }
                        }
                    });
                }
            }
        // }    
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.options !== false) {
            var o = 1;
            if( (item.row.storage_type == 'loose' && site.settings.sale_loose_products_with_variants == 1) || item.row.storage_type == 'packed' ) {
            
                opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
                $.each(item.options, function () {
                    if (o == 1) {
                        product_variant = item.row.option ? item.row.option : this.id;                       
                    }
                    $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                    o++;
                });
            } else {                
                product_variant = item.row.option;                
                opt = '<p class="form-control" >'+item.options[product_variant].name+'</p>';
            }
        } else {
            product_variant = 0;
        }
        var opt_color = '<p style="margin: 12px 0 0 0;">n/a</p>';
        // if (item.options_color !== false) {
        //     var o = 1;
        //     opt_color = $("<select id=\"poption_color\" name=\"poption_color\" class=\"form-control select\" />");
        //     $.each(item.options_color, function () {
        //         if (o == 1) {
        //             if (product_option_color == '') {
        //                 product_variant_color = this.id;
        //             } else {
        //                 product_variant_color = product_option_color;
        //             }
        //         }
        //         if (product_option_color == this.id) {
        //             $("<option />", {value: this.id, text: this.name, selected: true}).appendTo(opt_color);
        //         } else {
        //             $("<option />", {value: this.id, text: this.name}).appendTo(opt_color);
        //         }
        //         o++;
        //     });
        // }
        
        
        if (parseInt(site.settings.product_batch_setting) > 0) {
            var edtbatch = '<p style="margin: 12px 0 0 0;"><input class="form-control" name="pbatch_number" type="text" id="pbatch_number"></p>';
            if (parseInt(site.settings.product_batch_setting) == 1) {
                if (item.batchs) {
                    var b = 1;
                    edtbatch = $('<select id="pbatch_number" name="pbatch_number" class="form-control" />');
                    $.each(item.batchs, function () {
                        $('<option data-batchid="' + this.id + '" data-price="' + this.price + '" data-mrp="'+this.mrp+'" data-quantity="'+this.quantity+'" value="' + this.batch_no + '" >' + this.batch_no + '</option>').appendTo(edtbatch);
                        b++;
                    });
                }
            } else if (parseInt(site.settings.product_batch_setting) == 2) {
                if (item.batchs) {
                    var batchIds = [];
                    edtbatch = '<input list="batches" class="form-control" name="pbatch_number" id="pbatch_number"><datalist id="batches">';
                    $.each(item.batchs, function () {
                        edtbatch += '<option data-batchid="' + this.id + '" data-price="' + this.price + '" data-mrp="'+this.mrp+'" data-quantity="'+this.quantity+'" value="' + this.batch_no + '" >' + this.batch_no + '</option>';
                        batchno = this.batch_no;
                        batchid = this.id;
                        batchIds[batchno] = batchid;
                    });
                    edtbatch += '</datalist>';
                   // slitems[item_id].product_batches = batchIds;
                }
            }
        }

        if (parseInt(site.settings.product_batch_setting) > 0) {
            $('#batchNo_div').html(edtbatch);
            $('#pbatch_number').select2('val', item.row.batch_number);
        }
        
        
        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if (this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected: true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

         
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#cf1').val(cf1);
        $('#cf2').val(cf2);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        var pr_tax_rate_obj = getPrTaxRateObj(null, pr_tax);
        var pr_tax_surcharge_val = 0;
        var pr_tax_final_val = parseFloat(pr_tax_val) || 0;
        if (pr_tax_rate_obj) {
            var pr_tax_surcharge = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, 1);
            if (pr_tax_surcharge.has_pr_tax_surcharge) {
                pr_tax_surcharge_val = parseFloat(pr_tax_surcharge.pr_tax_surcharge_val) || 0;
                pr_tax_final_val = parseFloat(pr_tax_surcharge.pr_tax_final_val) || pr_tax_final_val;
            }
        }
        $('#punit_price').val(formatDecimal(parseFloat(unit_price) + pr_tax_final_val));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        if (slitems[item_id].row.tax_method == 0) {
            net_price = parseFloat(unit_price) - parseFloat(pr_tax_val) - pr_tax_surcharge_val;
        } else {
            net_price = parseFloat(unit_price);
        }
        $('#net_price').text(formatMoney(net_price));
        $('#tax_method').val(tax_method).trigger('change');
        if (item.tax_method_type === undefined) {
            item.tax_method_type = (item.category_tax || (item.categoryTaxData && item.categoryTaxData.fix_tax_rate == 'fix tax' && !item.row.tax_rate)) ? 'category' : 'product';
        }
        if (item.tax_method_type == 'product') {
            $('#ptax').select2("readonly", false);
        } else {
            $('#ptax').select2("readonly", true);
        }

        setPrTaxDisplay('#pro_tax', pr_tax_val, pr_tax_rate_obj);
        $('#storage_type').val(item.row.storage_type);
        $('#prModal').appendTo("body").modal('show');

    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pprice, #ptax, #pdiscount , #tax_method', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = slitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var tax_method = $('#tax_method').val(); 
        var pr_tax = $('#ptax').val(), item_tax_method = tax_method;
        // var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        // if (site.settings.theme == 'default' || site.settings.theme == 'newpos') { // product level tax
        //     if (pr_tax !== null && pr_tax != 0) {
        //         $.each(tax_rates, function () {
        //             if (this.id == pr_tax) {
        //                 if (this.type == 1) {

        //                     if (item_tax_method == 0) {
        //                         pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
        //                         pr_tax_rate = formatDecimal(this.rate) + '%';
        //                         unit_price -= pr_tax_val;
        //                     } else {
        //                         pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / 100), 4);
        //                         pr_tax_rate = formatDecimal(this.rate) + '%';
        //                     }

        //                 } else if (this.type == 2) {

        //                     pr_tax_val = parseFloat(this.rate);
        //                     pr_tax_rate = this.rate;

        //                 }
        //             }
        //         });
        //     }
        // }
        // if (site.settings.theme == 'theme_four' || site.settings.theme == 'theme_three') { // category leve tax
            if (item.tax_method_type === undefined) {
                item.tax_method_type = (item.category_tax || (item.categoryTaxData && item.categoryTaxData.fix_tax_rate == 'fix tax' && !item.row.tax_rate)) ? 'category' : 'product';
            }
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {

                            if (item_tax_method == 0) {

                                if (item.fixtax && item.tax_method_type != 'product') {
                                    var taxvalue = item.fixtax;
                                    var exptax = taxvalue.split("~");

                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1]))), 4);
                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                    pr_tax = exptax[0];
                                    net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                } else if (item.category_tax && item.tax_method_type != 'product') {
                                    $.each(item.category_tax, function (k, categorytax) {
                                        var uptocheck = categorytax.upto;
                                        if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                } else if (unit_price <= categorytax.price) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                            }

                                        } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                                }
                                            } else if (unit_price >= categorytax.price) {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                            }
                                        }
                                    });
                                } else {
                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                                    pr_tax_rate = formatDecimal(this.rate) + '%';
                                    net_price = parseFloat(unit_price) - parseFloat(pr_tax_val);
                                }
                            } else {
                                if (item.fixtax && item.tax_method_type != 'product') {
                                    var taxvalue = item.fixtax;
                                    var exptax = taxvalue.split("~");
                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / 100), 4);
                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                    pr_tax = exptax[0];
                                    net_price = parseFloat(unit_price);
                                } else if (item.category_tax && item.tax_method_type != 'product') {
                                    $.each(item.category_tax, function (k, categorytax) {
                                        var uptocheck = categorytax.upto;
                                        if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {

                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price);
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price);
                                                }
                                            // } else if (unit_price >= categorytax.price) {
                                            } else {

                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price);
                                            }


                                        } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price);
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    pr_tax = exptax[0];
                                                    net_price = parseFloat(unit_price);
                                                }
                                            // } else if (unit_price >= categorytax.price) {
                                            } else {

                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100), 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price);
                                            }
                                        }
                                    });
                                } else {
                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / 100), 4);
                                    pr_tax_rate = formatDecimal(this.rate) + '%';
                                    net_price = parseFloat(unit_price);
                                }

                            }

                        } else if (this.type == 2) {

                            if (item.fixtax && item.tax_method_type != 'product') {
                                var taxvalue = item.fixtax;
                                var exptax = taxvalue.split("~");

                                pr_tax_val = parseFloat(exptax[1]);
                                pr_tax_rate = exptax[1];
                                pr_tax = exptax[0];
                                net_price = parseFloat(unit_price);
                            } else if (item.category_tax && item.tax_method_type != 'product') {
                                $.each(item.category_tax, function (k, categorytax) {
                                    var uptocheck = categorytax.upto;
                                    if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                        if (uptocheck) {
                                            if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = parseFloat(exptax[1])
                                                pr_tax_rate = exptax[1];
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price);
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = parseFloat(exptax[1])
                                                pr_tax_rate = exptax[1];
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price);
                                            }
                                        }
                                        else {
                                            var taxvalue = categorytax.taxratevalue;
                                            var exptax = taxvalue.split("~");
                                            pr_tax_val = parseFloat(exptax[1])
                                            pr_tax_rate = exptax[1];
                                            pr_tax = exptax[0];
                                            net_price = parseFloat(unit_price);
                                        }
                                    } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                        if (uptocheck) {
                                            if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = parseFloat(exptax[1])
                                                pr_tax_rate = exptax[1];
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price);
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = parseFloat(exptax[1])
                                                pr_tax_rate = exptax[1];
                                                pr_tax = exptax[0];
                                                net_price = parseFloat(unit_price);
                                            }
                                        }
                                        else {
                                            var taxvalue = categorytax.taxratevalue;
                                            var exptax = taxvalue.split("~");
                                            pr_tax_val = parseFloat(exptax[1])
                                            pr_tax_rate = exptax[1];
                                            pr_tax = exptax[0];
                                            net_price = parseFloat(unit_price);
                                        }
                                    }
                                });
                            } else {
                                pr_tax_val = parseFloat(this.rate);
                                pr_tax_rate = this.rate;
                                net_price = parseFloat(unit_price);
                            }

                        }
                    }
                });
            }
        // }
        $('#ptax').select2('val', pr_tax);
        var pr_tax_rate_obj = getPrTaxRateObj(null, pr_tax);
        var pr_tax_surcharge_val = 0;
        if (pr_tax_rate_obj) {
            var pr_tax_surcharge = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, 1);
            if (pr_tax_surcharge.has_pr_tax_surcharge) {
                pr_tax_surcharge_val = parseFloat(pr_tax_surcharge.pr_tax_surcharge_val) || 0;
            }
        }
        if (item_tax_method == 0) {
            net_price = parseFloat(unit_price) - parseFloat(pr_tax_val) - pr_tax_surcharge_val;
        } else {
            net_price = parseFloat(unit_price);
        }
        // $('#net_price').text(formatMoney(unit_price));
        $('#net_price').text(formatMoney(net_price));
        setPrTaxDisplay('#pro_tax', pr_tax_val, pr_tax_rate_obj);
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), nameunit = $('#punit option:selected').text(), unit = $('#punit').val(), base_quantity = $('#pquantity').val(), aprice = 0;
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
        if (unit != slitems[item_id].row.base_unit) {
            $.each(item.units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                    $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)), 4)).change();
                }
            });
        } else {
            $('#pprice').val(formatDecimal(item.row.base_unit_price + aprice)).change();
        }
        slitems[item_id].row.unit_lable = nameunit;
        localStorage.setItem('slitems', JSON.stringify(slitems));
    });
    
    $(document).on('change', '.rbtach_no', function () {
        /* var item_id = $(this).closest('tr').attr('data-item-id');
         slitems[item_id].row.batch_number = $(this).val();
         localStorage.setItem('slitems', JSON.stringify(slitems));*/

        var item_id     = $(this).closest('tr').attr('data-item-id');
        var batch       = $(this).val();
        var batch_id    = $(this).find(':selected').attr('data-batchid');

        batch_id = batch_id ? batch_id : (slitems[item_id].product_batches[batch] ? slitems[item_id].product_batches[batch] : false);
        var select_batch = 'change'
        slitems[item_id].row.select_batch = select_batch;
        slitems[item_id].row.batch_number = batch;

        if (batch_id) {
            slitems[item_id].row.batch = batch_id;

            var batchvalue = slitems[item_id].batchs[batch_id];
            
        //    if(batchvalue['cost']) {
        //        slitems[item_id].row.cost = batchvalue['cost'];
        //        slitems[item_id].row.real_unit_cost = batchvalue['cost'];
        //        slitems[item_id].row.base_unit_cost = batchvalue['cost'];
        //    }
           if(batchvalue['price']) {
               slitems[item_id].row.real_unit_price = batchvalue['price'];            
               slitems[item_id].row.base_unit_price = batchvalue['price'];
           }
            slitems[item_id].row.batch_quantity = batchvalue['quantity'];
            
            if(batchvalue['mrp'])
            slitems[item_id].row.mrp = batchvalue['mrp'];
        
            slitems[item_id].row.expiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
        }

        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });
    
    $(document).on('change', '#pbatch_number', function () {
        if (parseInt(site.settings.product_batch_setting) > 0) {
            onBatchChanged();
        }
    });

    /* ----------------------- *
     * Product option change   *
     * ----------------------- */
    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), 
        opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        var unit = $('#punit').val(), 
        base_quantity = parseFloat($('#pquantity').val()), 
        base_unit_price = item.row.base_unit_price;
        
        if (unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function () {
                if (this.id == unit) {
                    base_unit_price = formatDecimal((parseFloat(item.row.base_unit_price) * (unitToBaseQty(1, this))), 4)
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(item.row.price) + (parseFloat(this.price))).trigger('change');
                }
            });
        } else {
            $('#pprice').val(parseFloat(base_unit_price)).trigger('change');
        }
        
        if (parseInt(site.settings.product_batch_setting) > 0) {
            onVariantChanged();
        }
    });
    
    /* -----------------------
     * Edit Row Method
     ----------------------- */
    $(document).on('click', '#editItem', function () {
       // var row = $('#' + $('#row_id').val());
       // var item_id = row.attr('data-item-id');
        var item_id     = $('#item_id').val(); 
        var new_pr_tax  = $('#ptax').val(), new_pr_tax_rate = false;
        var tax_method  = $('#tax_method').val(); 
        var manualedit = (item.row.manualedit) ? item.row.manualedit : ''; // 05-09-19

        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        var unit_price = price;
        
        if (item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    
                    price = unit_price - parseFloat(this.price); // existing
                    if (manualedit == 1) { // Manual Price Edit
                        price = unit_price;
                    }else{
                        price = unit_price - parseFloat(this.price);
                    }           
                    slitems[item_id].row.option         = opt;
                    slitems[item_id].row.quantity       = this.quantity;
                    slitems[item_id].row.unit_quantity  = this.unit_quantity;                    
                    slitems[item_id].row.unit_weight    = this.unit_weight;                    
                }                
            });            
        }        
        
        if (parseInt(site.settings.product_batch_setting) > 0) {
            var batch       = $('#pbatch_number').val();
            var batch_id    = $('#pbatch_number').find(':selected').attr('data-batchid');
            var bprice      = $('#pbatch_number').find(':selected').attr('data-price');
            var mrp         = $('#pbatch_number').find(':selected').attr('data-mrp');
            var quantity    = $('#pbatch_number').find(':selected').attr('data-quantity');

            slitems[item_id].row.batch_number   = batch;
            slitems[item_id].row.batch          = batch_id;
            slitems[item_id].row.batch_quantity = quantity;
        }        
        
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {            
            
            var ods = $('#pdiscount').val();
            
            if (ods.indexOf("%") !== -1) {
                var pds = ods.split("%");
                if (isNaN(pds[0])) {
                    bootbox.alert('1'+lang.unexpected_value);
                    return false;
                }
            } else if (!is_numeric(ods)) {
                bootbox.alert('2'+lang.unexpected_value);
                return false;
            } else if(is_numeric($('#pdiscount').val()) && parseFloat($('#pdiscount').val()) > parseFloat(unit_price)) {
                bootbox.alert('3'+lang.unexpected_value);
                return false;
            }
             
        }
       
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
         
        slitems[item_id].row.fup             = 1,
        slitems[item_id].row.qty             = parseFloat($('#pquantity').val()),
        slitems[item_id].row.base_quantity   = base_quantity,
        slitems[item_id].row.unit_price      = unit_price; 
        slitems[item_id].row.price           = price;
        slitems[item_id].row.real_unit_price = unit_price,        
        slitems[item_id].row.base_unit_price = unit_price,        
        slitems[item_id].row.unit            = unit,
        slitems[item_id].row.tax_rate        = new_pr_tax,
        slitems[item_id].tax_rate            = new_pr_tax_rate,
        slitems[item_id].tax_method_type     = slitems[item_id].tax_method_type || 'product',
        slitems[item_id].row.discount        = $('#pdiscount').val() ? $('#pdiscount').val() : '',         
        slitems[item_id].row.serial          = $('#pserial').val();
        slitems[item_id].row.cf1             = $('#cf1').val();
        slitems[item_id].row.cf2             = $('#cf2').val();
        slitems[item_id].row.tax_method      = tax_method;

        /*localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#prModal').modal('hide');
        loadItems();
        return;*/

        var Item = slitems[item_id];
        delete slitems[item_id];
        resetCartItems();
        localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#prModal').modal('hide');
        add_invoice_item(Item);
        loadItems();
        return;
    });
   
    /* ------------------------------
     * Sell Gift Card modal
     ------------------------------- */
    $(document).on('click', '#sellGiftCard', function (e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2("readonly", true);
                $('#slwarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#gcModal').appendTo("body").modal('show');
        return false;
    });

    $(document).on('click', '#addGiftCard', function (e) {
        var mid = (new Date).getTime(),
                gccode = $('#gccard_no').val(),
                gcname = $('#gcname').val(),
                gcvalue = $('#gcvalue').val(),
                gccustomer = $('#gccustomer').val(),
                gcexpiry = $('#gcexpiry').val() ? $('#gcexpiry').val() : '',
                gcprice = parseFloat($('#gcprice').val());
        if (gccode == '' || gcvalue == '' || gcprice == '' || gcvalue == 0 || gcprice == 0) {
            $('#gcerror').text('Please fill the required fields');
            $('.gcerror-con').show();
            return false;
        }

        var gc_data = new Array();
        gc_data[0] = gccode;
        gc_data[1] = gcvalue;
        gc_data[2] = gccustomer;
        gc_data[3] = gcexpiry;
        //if (typeof slitems === "undefined") {
        //    var slitems = {};
        //}

        $.ajax({
            type: 'get',
            url: site.base_url + 'sales/sell_gift_card',
            dataType: "json",
            data: {gcdata: gc_data},
            success: function (data) {
                if (data.result === 'success') {
                    slitems[mid] = {"id": mid, "item_id": mid, "label": gcname + ' (' + gccode + ')', "row": {"id": mid, "code": gccode, "name": gcname, "quantity": 1, "price": gcprice, "real_unit_price": gcprice, "tax_rate": 0, "qty": 1, "type": "manual", "discount": "0", "serial": "", "option": ""}, "tax_rate": false, "options": false};
                    localStorage.setItem('slitems', JSON.stringify(slitems));
                    loadItems();
                    $('#gcModal').modal('hide');
                    $('#gccard_no').val('');
                    $('#gcvalue').val('');
                    $('#gcexpiry').val('');
                    $('#gcprice').val('');
                } else {
                    $('#gcerror').text(data.message);
                    $('.gcerror-con').show();
                }
            }
        });
        return false;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2("readonly", true);
                $('#slwarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

    $(document).on('click', '#addItemManually', function (e) {
        var mid = (new Date).getTime(),
                mcode = $('#mcode').val(),
                mname = $('#mname').val(),
                mtax = parseInt($('#mtax').val()),
                mqty = parseFloat($('#mquantity').val()),
                mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
                unit_price = parseFloat($('#mprice').val()),
                mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            slitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "mrp":unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option": ""}, "tax_rate": mtax_rate, 'units': false, "options": false};
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(), item_tax_method = 0;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });

    /* --------------------------
     * Edit Row Quantity Method
     --------------------------- */
    //  $(document).on("change",".rbtach_no", function(){
    //     var row = $(this).closest('tr');
    //     var row_id = row.attr('id');
    //     $('#row_id').val(row_id);
    //     item_id = row.attr('data-item-id');        
         
    //     var batch_number    = $(this).val();
    //     var batch_id        = $(this).find(':selected').attr('data-batchid');
    
    //     slitems[item_id].row.batch = batch_id;
    //     slitems[item_id].row.batch_number = batch_number;
                 
    //     localStorage.setItem('slitems', JSON.stringify(slitems));

    //     loadItems();
    // })
    
    $(document).on("change",".rexpiry", function(){
         var row = $(this).closest('tr');
        var row_id = row.attr('id');
        $('#row_id').val(row_id);
        item_id = row.attr('data-item-id');
        
        item = slitems[item_id];
        var expiry = $(this).val();
        
        
        slitems[item_id].row.cf1	 = expiry;
        localStorage.setItem('slitems', JSON.stringify(slitems));

        loadItems();
    })
    

    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
        slitems[item_id].row.base_quantity = new_qty;
        slitems[item_id].row.unit_quantity = new_qty;
        if (slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function () {
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                   slitems[item_id].row.unit_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        slitems[item_id].row.qty = new_qty;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document).on("focus", '.rprice', function () {
        old_price = $(this).val();
    }).on("change", '.rprice', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
        slitems[item_id].row.price = new_price;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
        $('#slcustomer').select2('readonly', false);
        //$('#slwarehouse').select2('readonly', false);
        return false;
    });


});
/* -----------------------
 * Misc Actions
 ----------------------- */



function onVariantChanged() {    
    
    var item_id  = $('#item_id').val();
    var poption = $('#poption').val();    
   
    var batch_id = slitems[item_id].row.batch;
    var batch_number = slitems[item_id].row.batch_number;
    var selected = '';
    var b = 0;
    var first_batch_number = '';
    var batchIds = [];
    var btc = '<input type="text" class="form-control" name="pbatch_number" id="pbatch_number" value="' + batch_number + '">';
    // alert(slitems[item_id].product_batches[poption]);
    if (slitems[item_id].product_batches !== false && slitems[item_id].product_batches[poption]) {
        var optionBatched = slitems[item_id].product_batches[poption];

        if (parseInt(site.settings.product_batch_setting) == 1) {
            btc = '<select id="pbatch_number" name="pbatch_number" class="form-control" >';

            $.each(optionBatched, function () {
                b++;
                batch_number = (batch_id == this.id) ? this.batch_no : '';
                btc += '<option data-batchid="' + this.id + '" value="' + this.batch_no + '" ' + selected + ' >' + this.batch_no + '</option>';
                if (b == 1) {
                    first_batch_number = this.batch_no;
                }
            });
            btc += '</select>';

        } else if (parseInt(site.settings.product_batch_setting) == 2) {

            btc = '<input list="batches" class="form-control" name="pbatch_number" id="pbatch_number" value="' + batch_number + '"><datalist id="batches">';
            $.each(optionBatched, function () {
                b++;
                batch_number = batch_number ? batch_number : ((batch_id == this.id) ? this.batch_no : '');
                btc += '<option data-batchid="' + this.id + '" value="' + this.batch_no + '" >';
                if (b == 1) {
                    first_batch_number = this.batch_no;
                }
                batchno = this.batch_no;
                batchid = this.id;
                batchIds[batchno] = batchid;

            });
            btc += '</datalist>';
            slitems[item_id].product_batches = batchIds;
        }
    }
    $('#batchNo_div').html(btc);
    $('#pbatch_number').select2('val', batch_number ? batch_number : first_batch_number);

    slitems[item_id].batchs = optionBatched ? optionBatched : false;
    localStorage.setItem('slitems', JSON.stringify(slitems));

    onBatchChanged();

}


function onBatchChanged() {
    
    var item_id     = $('#item_id').val();
    var batch       = $('#pbatch_number').val();
    var batch_id    = $('#pbatch_number').find(':selected').attr('data-batchid');

    batch_id = batch_id ? batch_id : (slitems[item_id].product_batches[batch] ? slitems[item_id].product_batches[batch] : false)

    if (batch_id && slitems[item_id].batchs[batch_id]) {

        var batchvalue = slitems[item_id].batchs[batch_id];

        var real_unit_price = batchvalue['price'];
        var pexpiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
        $('#pcost').val(batchvalue['price']);
        $('#pexpiry').val(pexpiry);
        var tax_rates = slitems[item_id].tax_rate;
        var net_price = real_unit_price;
        var tax_method = $('#tax_method').val();
        if (site.settings.tax1) {

            var item_discount = 0, ds = $('#pdiscount').val();
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat(((real_unit_price) * parseFloat(pds[0])) / 100);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = $('#ptax').val();
            var pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {

                if (tax_method == 0) {
                    pr_tax_val = formatDecimal((((real_unit_price - item_discount) * parseFloat(tax_rates)) / (100 + parseFloat(tax_rates))), 4);
                    // pr_tax_rate = formatDecimal(tax_rates) + '%';
                    net_price -= pr_tax_val;
                } else {
                    pr_tax_val = formatDecimal((((real_unit_price - item_discount) * parseFloat(tax_rates)) / 100), 4);
                    //  pr_tax_rate = formatDecimal(tax_rates) + '%';
                }                   
            }
        }

        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#ptax').val(pr_tax).trigger('change');
        $('#psubtotal').val('');

    }
}


function resetCartItems(){
      localStorage.removeItem('slitems');
    }
    

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#slcustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}

function getVariant_Detail(VarientId, ItemId) {
        
    slitems = JSON.parse(localStorage.getItem('slitems'));   
    
    //ItemId = ItemId + VarientId;
    
    //var item_id = (site.settings.item_addition == 1) ? slitems[ItemId].item_id  +''+VarientId : slitems[ItemId].id +''+VarientId;
     var item_id = (site.settings.item_addition == 1) ? (slitems[ItemId].row.id + VarientId) : slitems[ItemId].id ;
     var opt = slitems[ItemId].options[VarientId]; 
     
    slitems[item_id] = slitems[ItemId];
    slitems[item_id].item_id             = (slitems[ItemId].row.id + VarientId);
    slitems[item_id].row.option          = VarientId;
    slitems[item_id].row.quantity        = opt.quantity;
    slitems[item_id].row.unit_quantity   = opt.unit_quantity;
    slitems[item_id].row.unit_weight     = opt.unit_weight;
    var unit_price                       = parseFloat(slitems[ItemId].row.price) + parseFloat(opt.price);
    var unit_cost                        = parseFloat(slitems[ItemId].row.cost)  + parseFloat(opt.cost);
    slitems[item_id].row.unit_cost       = unit_cost;
    slitems[item_id].row.unit_price      = unit_price;
    slitems[item_id].row.base_unit_price = unit_price;
    slitems[item_id].row.real_unit_price = unit_price;
    
    
    
    if (slitems[ItemId].row.storage_type == 'packed' && slitems[ItemId].product_batches !== false && slitems[ItemId].product_batches[VarientId]) {
        
        option_batchs = slitems[ItemId].product_batches[VarientId];
        slitems[item_id].batchs = option_batchs;
        var i=0;
        $.each(option_batchs, function (index, obj) {
            i++;
            if(i==1) {
                slitems[item_id].row.batch = option_batchs[index].id;
                slitems[item_id].row.batch_number = option_batchs[index].batch_no;
                slitems[item_id].row.batch_quantity = option_batchs[index].quantity;
            }
        });
    }
    
    if(item_id != ItemId) {
        delete slitems[ItemId];
    }
    
    localStorage.setItem('slitems', JSON.stringify(slitems));

    loadItems();
}

//localStorage.clear();
function loadItems() {
    var can_edit_price = (typeof allow_edit_price !== 'undefined') ? allow_edit_price : 1;
    window.overselling_items = [];
    if (localStorage.getItem('slitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        total_netprice = 0;
        item_cart_qty = [];
        
        $("#slTable tbody").empty();
        
        slitems = JSON.parse(localStorage.getItem('slitems'));
        
        sortedItems = slitems;
        
        console.log('-----------sortedItems---------');
        console.log(sortedItems);
        
        sortedItems = (site.settings.item_addition == 1) 
        ? _.sortBy(slitems, function (o) {  return [parseInt(o.order)];  }) 
        : slitems;
        if (slitems) {
            var order_changed = false;
            var order_index = 1;
            $.each(slitems, function (k, v) {
                if (v && !v.order) {
                    v.order = new Date().getTime() + order_index;
                    order_index++;
                    order_changed = true;
                }
            });
            if (order_changed) {
                localStorage.setItem('slitems', JSON.stringify(slitems));
            }
        }

        if (typeof pos_settings === 'undefined') {
            window.pos_settings = (typeof Pos_Settings !== 'undefined') ? Pos_Settings : {};
        }

        if (pos_settings && pos_settings.item_order !== undefined) {
            var item_order = parseInt(pos_settings.item_order);
            if (item_order == 1) { // category
                sortedItems = _.sortBy(slitems, function (o) {
                    var categoryId = o.category ? parseInt(o.category) : ((o.row && o.row.category_id) ? parseInt(o.row.category_id) : 0);
                    var order = o.order ? parseInt(o.order) : new Date().getTime();
                    return [categoryId, order];
                });
            } else if (item_order == 2) { // oldest to newest
                sortedItems = _.sortBy(slitems, function (o) {
                    var order = o.order ? parseInt(o.order) : new Date().getTime();
                    return order;
                });
            } else { // newest to oldest (item_order == 0)
                sortedItems = _.sortBy(slitems, function (o) {
                    var order = o.order ? parseInt(o.order) : new Date().getTime();
                    return -order;
                });
            }
        } else {
            sortedItems = _.sortBy(slitems, function (o) {
                var order = o.order ? parseInt(o.order) : new Date().getTime();
                return -order;
            });
        }
        
        $('#add_sale, #edit_sale, #print_invoice').attr('disabled', false);

        var cart_item_unit_count = 0;
        var total_item_weight = 0;
        var total_packing_size = 0;
        
        $.each(sortedItems, function () {
            cart_item_unit_count += parseFloat(this.row.qty);
        });
        
        $.each(sortedItems, function () {
            
            var item = this;
            var packing_size = (item.row.packing_size) ? parseFloat(item.row.packing_size) : 0;
            
            var Buprice = (parseInt(item.row.base_unit_price)) ? item.row.base_unit_price : item.row.real_unit_price;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.unit_price, item_option_color = item.row.option_color,item_option_color_name = item.row.option_color_name, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_option = item.row.option, item_code = item.row.code, item_serial = ((item.row.serial === null || item.row.serial === "" || item.row.serial == "undefined") ? '' : item.row.serial), item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
//            var item_delivered_qty  = item.row.delivered_qty;
//            var item_pending_qty    = item.row.pending_qty;
            var product_unit = item.row.unit;
            var item_ds = item.row.discount, item_discount = 0;
            var item_weight = 0;
            
            var sale_action = $('#sale_action').val();
            var challan_status = $('#slchallan_status').val();
            var old_qty = (sale_action == 'edit') ? item.row.old_qty : 0;
            var manualedit = (item.row.manualedit) ? item.row.manualedit : ''; // for manual price edit on row
            
            // Category Tax
            var category_tax = item.category_tax;
            var fixtax = item.fixtax;
            var salesperson = item.salesperson_details;
            if (item.tax_method_type === undefined) {
                 item.tax_method_type = (item.category_tax || (item.categoryTaxData && item.categoryTaxData.fix_tax_rate == 'fix tax' && !item.row.tax_rate)) ? 'category' : 'product';
            }
          
            // site.settings.product_batch_required
            if(item.row.storage_type == 'loose' ){
                item_cart_qty[item.row.id] = parseFloat(item_cart_qty[item.row.id]) > 0 ? (item_cart_qty[item.row.id] + (item.row.qty * item.row.unit_quantity)) : (item.row.qty * item.row.unit_quantity);
            } else {
                item_cart_qty[item.item_id] = parseFloat(item_cart_qty[item.item_id]) > 0 ? (item_cart_qty[item.item_id] + item.row.qty) : item.row.qty;
            }
            if(item.row.storage_type == 'loose'){
                var base_quantity = formatDecimal((parseFloat(item.row.base_quantity) * parseFloat(item.row.qty)),3);
            }else{
                var base_quantity = formatDecimal((parseFloat(item.row.qty)),3);
            }
            //   var base_quantity = formatDecimal( parseFloat(item.row.unit_quantity),3);        
            /*if (sale_action == 'add') {*/
            // var unit_price = item.row.real_unit_price ? item.row.real_unit_price : item.row.unit_price;
            /*} else {
                var unit_price = (item_tax_method == 0) ? item.row.real_unit_price : item.row.net_unit_price;
            }*/

            // for variant popup functionality
            var unit_price = 0;
            if (item.options && item.options.length > 0 && item.row.edit !== 'edit') {
                var optionId = item.row.option;
                var OptionData = item.options.find(option => option.id === optionId);
                if (OptionData) {
                    unit_price = OptionData.price;
                    item.row.real_unit_price = OptionData.price;
                } else {
                    unit_price = item.row.real_unit_price;
                }
            } else {
                // unit_price = item.row.real_unit_price;
                unit_price = item.row.real_unit_price ? item.row.real_unit_price : item.row.unit_price;
            }
                        
            var mrp                     = item.row.mrp;
            var hsn_code                = item.row.hsn_code;
            var hidden_base_quantity    = base_quantity;
            
            if (item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_price = formatDecimal((parseFloat(Buprice) * (unitToBaseQty(1, this))), 4);
                    }
                });
            }
          var invoice_unit_price  = item.row.invoice_unit_price;
          var net_unit_price  = item.row.net_unit_price;  
            if (item.options !== false) {                               
                $.each(item.options, function () {
                    
                    var this_options = this;
                                        
                    //If Select multiple options
                    if (jQuery.type(item.row.option) == 'string') {
                        var optionArr = item.row.option.split(",");
                        $.each(optionArr, function (k, opt) {

                            if (this_options.id == opt) {
                                if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                                    if (manualedit == 1 || item.row.edit === 'edit') { // Manual Price Edit
                                        item_price = formatDecimal(parseFloat(unit_price));
                                        unit_price = item_price;
                                        item_aqty = this_options.quantity;
                                    }else{
                                        item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.price), 6);
                                        unit_price = item_price;
                                        item_aqty = this_options.quantity;
                                    }
                                }
                                if (k) {
                                    sel_opt = sel_opt + ',' + this_options.name;
                                } else {
                                    sel_opt = this_options.name;
                                }
                            }
                        });
                    } 
                    else {
                        if (this_options.id == item.row.option) {
                            if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                                if (manualedit == 1 || item.row.edit === 'edit') { // Manual Price Edit
                                    item_price = formatDecimal(parseFloat(unit_price));
                                    unit_price = item_price;
                                    item_aqty = this_options.quantity;
                                }else{
                                    item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.price)), 6);
                                    unit_price = item_price;
                                    item_aqty  = this_options.quantity;
                                }
                            }
                            sel_opt = this_options.name;                            
                        }
                    }
                });
            }
            
            //Apply Batch settings
            if (parseInt(site.settings.product_batch_setting) > 0 && (item.row.batch != false && item.row.batch_number != false)) {
                
                var batch_id = item.row.batch;
if(batch_id){
                if(item.batchs[batch_id].batch_no== item.row.batch_number || item.batchs[batch_id].option_id == item.row.option){
                    if(item.row.select_batch == 'change'){
                        unit_price = parseFloat(item.batchs[batch_id].price) > 0 ? item.batchs[batch_id].price : unit_price;
                    }else{
                        unit_price = unit_price ;
                    }
                }
                }
                //Condition for manage batch stocks
                if(parseInt(site.settings.product_batch_required) == 2 || (parseInt(site.settings.product_batch_required) == 1  && item.row.storage_type == 'packed' )){
                    
                    item.row.quantity = item.batchs[batch_id].quantity;
                }
            }
          
          
            if(item_ds != '' && item_ds != 0) {
                var ds = item_ds ? item_ds : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");                    
                    if (!isNaN(pds[0])) {                        
                        item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);                        
                    } else {                        
                        item_discount = formatDecimal(ds);
                    }
                } else {                    
                    item_discount = formatDecimal(ds);
                } 
                product_discount += parseFloat((packing_size > 0 ? item_discount * packing_size : item_discount) * item_qty); 
            }
 
             /** New Loginc Order Discount **/
            if(site.settings.sales_order_discount == 1) {                
                var posdiscount = localStorage.getItem('posdiscount');
               
                if ( posdiscount != '' && posdiscount) {
                    //Order Level Discount Calculations               
                    var ods = posdiscount;
                    if (ods.indexOf("%") !== -1) {
                        var pds = ods.split("%");
                        if (!isNaN(pds[0])) {
                            item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
                            item_ds = ods;
                        } else {
                            item_discount = formatDecimal(parseFloat(ods), 6);
                            item_ds = item_discount;
                        }
                    } else {
                        //If Discount in amount then divided equal in each items unit equally.
                        item_discount = formatDecimal((parseFloat(ods) / cart_item_unit_count), 6);
                        item_ds = item_discount;
                    }
                    
                    //Set Order Discount Value null.
                    product_discount += parseFloat((packing_size > 0 ? item_discount * packing_size : item_discount) * item_qty);
                } 
            }
                /** New Login Order Discount ***/
                    
            unit_price = formatDecimal(unit_price - item_discount);
            var cf1 = item.row.cf1 ? item.row.cf1 : '';
            var cf2 = item.row.cf2 ? item.row.cf2 : '';
            var cf3 = item.row.cf3;
            var cf4 = item.row.cf4;
            var cf5 = item.row.cf5;
            var cf6 = item.row.cf6;
                       

            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0, tax_rate = 0, pr_tax_surcharge_val = 0, pr_tax_final_val = 0;
            // if (site.settings.theme == 'default' || site.settings.theme == 'newpos') { // product level tax
            //     if (site.settings.tax1 == 1) {
            //         if (pr_tax !== false) {
            //             if (pr_tax.type == 1) {

            //                 if (item_tax_method == '0') {
            //                     pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
            //                     pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
            //                 } else {
            //                     pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
            //                     pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
            //                 }

            //             } else if (pr_tax.type == 2) {

            //                 pr_tax_val = parseFloat(pr_tax.rate);
            //                 pr_tax_rate = pr_tax.rate;

            //             }
            //             product_tax += pr_tax_val * item_qty;
            //         }
            //     }
            // }
            // if (site.settings.theme == 'theme_four' || site.settings.theme == 'theme_three') { // category leve tax
                if (site.settings.tax1 == 1) {
                    if (pr_tax !== false) {
                        if (pr_tax.type == 1) {
                            if (item_tax_method == '0') {
                                var is_manual_tax = false;
                                if (fixtax) {
                                    var exptax = fixtax.split("~");
                                    if (item.row.tax_rate && item.row.tax_rate != exptax[0]) {
                                        is_manual_tax = true;
                                    }
                                }
                                if (fixtax && item.tax_method_type != 'product' && !is_manual_tax) {
                                    var exptax = fixtax.split("~");
                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1]))), 4);
                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                    tax_rate = exptax[0];
                                } else if (category_tax && item.tax_method_type != 'product' && !is_manual_tax) {
                                    if (category_tax) {
                                        $.each(category_tax, function (k, categorytax) {
                                            var uptocheck = categorytax.upto;
                                            if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                                if (uptocheck) {
                                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    }
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }
                                            } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                                if (uptocheck) {
                                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    }
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }

                                            }
                                        });
                                    }

                                } else {
                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
                                    pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                                    tax_rate = pr_tax.id;
                                }
                            } else {
                                var is_manual_tax = false;
                                if (fixtax) {
                                    var exptax = fixtax.split("~");
                                    if (item.row.tax_rate && item.row.tax_rate != exptax[0]) {
                                        is_manual_tax = true;
                                    }
                                }
                                if (fixtax && item.tax_method_type != 'product'  && !is_manual_tax) {
                                    var exptax = fixtax.split("~");
                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / 100), 4);
                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                    tax_rate = exptax[0];
                                } else if (category_tax && item.tax_method_type != 'product' && !is_manual_tax) {
                                    if (category_tax) {
                                        $.each(category_tax, function (k, categorytax) {
                                            var uptocheck = categorytax.upto;
                                            if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                                if (uptocheck) {
                                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    }
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }

                                            } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                                if (uptocheck) {
                                                    if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    } else {
                                                        var taxvalue = categorytax.taxratevalue;
                                                        var exptax = taxvalue.split("~");
                                                        pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                        pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                        tax_rate = exptax[0];
                                                    }
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }

                                            }
                                        });
                                    }

                                } else {
                                    pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
                                    pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                                    tax_rate = pr_tax.id;
                                }
                            }

                        } else if (pr_tax.type == 2) {
                            var is_manual_tax = false;
                            if (fixtax) {
                                var exptax = fixtax.split("~");
                                if (item.row.tax_rate && item.row.tax_rate != exptax[0]) {
                                    is_manual_tax = true;
                                }
                            }
                            if (fixtax && item.tax_method_type != 'product' && !is_manual_tax) {
                                var exptax = fixtax.split("~");
                                pr_tax_val = parseFloat(exptax[1]);
                                pr_tax_rate = exptax[1];
                                tax_rate = exptax[0];
                            } else if (category_tax && item.tax_method_type != 'product' && !is_manual_tax) {
                                if (category_tax) {
                                    $.each(category_tax, function (k, categorytax) {
                                        var uptocheck = categorytax.upto;
                                        if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(exptax[1]);
                                                    pr_tax_rate = formatDecimal(exptax[1]);
                                                    tax_rate = exptax[0];
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(exptax[1]);
                                                    pr_tax_rate = formatDecimal(exptax[1]);
                                                    tax_rate = exptax[0];
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(exptax[1]);
                                                pr_tax_rate = formatDecimal(exptax[1]);
                                                tax_rate = exptax[0];
                                            }

                                        } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {

                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(exptax[1]);
                                                    pr_tax_rate = formatDecimal(exptax[1]);
                                                    tax_rate = exptax[0];
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(exptax[1]);
                                                    pr_tax_rate = formatDecimal(exptax[1]);
                                                    tax_rate = exptax[0];
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(exptax[1]);
                                                pr_tax_rate = formatDecimal(exptax[1]);
                                                tax_rate = exptax[0];
                                            }

                                        }
                                    });
                                }

                            } else {
                                pr_tax_val = parseFloat(pr_tax.rate);
                                pr_tax_rate = pr_tax.rate;
                                tax_rate = pr_tax.id;
                            }
                        }
                        pr_tax_final_val = parseFloat(pr_tax_val) || 0;
                        var pr_tax_rate_obj = getPrTaxRateObj(pr_tax, null);
                        if (pr_tax_rate_obj) {
                            var pr_tax_surcharge = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, 1);
                            if (pr_tax_surcharge.has_pr_tax_surcharge) {
                                pr_tax_surcharge_val = parseFloat(pr_tax_surcharge.pr_tax_surcharge_val) || 0;
                                pr_tax_final_val = parseFloat(pr_tax_surcharge.pr_tax_final_val) || pr_tax_final_val;
                            }
                        }
                        product_tax += (packing_size > 0 ? pr_tax_final_val * packing_size : pr_tax_final_val) * item_qty;
                    }
                }
            // }    
            item_price = (item_tax_method == 0) ? formatDecimal(unit_price - pr_tax_val - pr_tax_surcharge_val, 4) : formatDecimal(unit_price);
             
            var show_unit_price = item_price;
            var show_net_price = formatMoney(parseFloat(item.row.base_unit_price) * parseFloat(base_quantity));
            
            var row_no = (new Date).getTime();

            /*03-10-2019*/
            // Store item.row.real_unit_price for packing_size calculation
            var original_unit_price = item.row.real_unit_price ? item.row.real_unit_price : item.row.unit_price;
            unit_price = formatDecimal(unit_price + item_discount, 4);
            mrp = formatDecimal(mrp, 4);
            var opt = 'N/A <input id="poption_' + row_no + '" name="product_option[]" type="hidden" class="roption" value="0">';
             
            if (item.options) {                
                if( (item.row.storage_type == 'loose' && site.settings.sale_loose_products_with_variants == 1) || item.row.storage_type == 'packed' ) {
                                 
                    opt = '<select id="poption_' + row_no + '" name="product_option[]" class="form-control select roption" onchange="return getVariant_Detail(this.value, ' + item_id + ');" >';
                    $.each(item.options, function () {
                        if (this.id == item_option) {
                           sel_opt = this.name; 
                           hidden_base_quantity = this.unit_quantity;
                           opt += '<option value="'+this.id+'" data-unitqty="'+this.unit_quantity+'" selected="selected" >'+this.name+'</option >';
                        } else {
                           opt += '<option value="'+this.id+'" data-unitqty="'+this.unit_quantity+'"  >'+this.name+'</option >';
                        }
                    });
                    opt += '</select>';
                } else if(item_option) {
                    opt = item.options[item_option].name + '<input name="product_option[]" type="hidden" class="roption" value="'+item_option+'">'; 
                } else {
                    opt = '<input name="product_option[]" type="hidden" class="roption" value="'+item_option+'">';
                }                 
            }
            var sel_opt = '';
            $.each(item.options, function () {
                if (this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var sel_opt_color = '';
            if (item.options_color) {
                $.each(item.options_color, function () {
                    if (this.id == item_option_color) {
                        sel_opt_color = this.name;
                    }
                });
            }
           
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td>';
            if (site.settings.sales_image == 1) {
                var subdomain = new URL(window.location.href).hostname.split('.')[0];
                tr_html += '<img src="assets/mdata/'+ subdomain + '/uploads/thumbs/' + item.image + '" alt="' + item.image + '" style="width:30px; height:30px;" /> ';
            }
            
            item_weight = (item.row.unit_weight) ? (parseFloat(item_qty) * parseFloat(item.row.unit_weight)) : '';
             
            tr_html += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">';
            tr_html += '<input name="tax_method[]" type="hidden" class="rid" value="' + item.row.tax_method  + '">';
            tr_html += '<input name="hsn_code[]" type="hidden" class="rid" value="' + hsn_code + '">';
            tr_html += '<input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '">';
            tr_html += '<input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '">';
            tr_html += '<input name="product_name[]" type="hidden" class="rname" value="' + item_name + '">';
            tr_html += '<input name="item_weight[]" type="hidden" class="rweight" value="' + item_weight + '">';
            tr_html += '<input name="cat_id[]" type="hidden"  value="' + item.row.category_id + '">'; ////////////////////// Muliple category////////////////////
           tr_html += '<input name="product_option[]" type="hidden" class="roption" value="' + item_option + '">';
           var category_name_bracket = '';
           if (pos_settings && parseInt(pos_settings.item_order) == 1 && item.row.category_name) {
               category_name_bracket = ' (' + item.row.category_name + ')';
           }
           tr_html += '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + category_name_bracket + (sel_opt != '' ? ' (' + sel_opt + ')' : '') +  (item_option_color_name ? ' (' + item_option_color_name + ')' : '') + '</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
           //tr_html += '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + '</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            // tr_html += '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + '</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
                        
            // tr_html += '<td>' + opt + '</td>';
            tr_html += '<input name="product_option_color[]" type="hidden" class="roption_color" value="' + item_option_color + '">';
            
            if(Pos_Settings.display_seller =='4' || Pos_Settings.display_seller =='3'){
                tr_html += '<td>';
                tr_html += '<select name="product_sales_person[]" class="form-control prsalesperson">';
                tr_html += '<option value="">Sales Person</option>';
                if (Array.isArray(salesperson)) {
                    for (var i = 0; i < salesperson.length; i++) {
                        var selected = (item.row.seller_id == salesperson[i].id) ? ' selected' : '';
                        tr_html += '<option value="' + salesperson[i].id + '~' + salesperson[i].name + '"' + selected + '>';
                        tr_html += salesperson[i].name;
                        tr_html += '</option>';
                    }
                }
                tr_html += '</select>';
                tr_html += '</td>';
            }
            if (parseInt(site.settings.overselling) == 0) {
                
                if(item.row.storage_type == 'loose' ){  
                    var iqty = (sale_action == 'edit') ? (parseFloat(item_aqty) + parseFloat(item_cart_qty[item.row.id])) : item_aqty ;
                    tr_html += '<td>'+ formatDecimal(item_cart_qty[item.row.id],2) + '/' + formatDecimal(iqty,2) + '</td>';
                } else {
                    var iqty = (sale_action == 'edit') ? (parseFloat(item_aqty) + parseFloat(item_cart_qty[item.item_id])) : item_aqty ;
                    tr_html += '<td>'+ formatDecimal(item_cart_qty[item.item_id],2) + '/' + formatDecimal(iqty,2) +' '+ item.row.unit_lable +  '</td>';
                }
            }
        
            if (site.settings.product_serial == 1) {
                //alert(item_serial);
                var item_serial_val = '';
                if(item_serial != '' && item_serial != 'undefined'  && item_serial != null) { item_serial_val=item_serial; }
                       
                tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="' + item_serial_val+ '"></td>';
            }
                      
            /***************************************************
             * site.settings.product_batch_required (0:Optional | 1:Required For Packed Products | 2:Required For All Products  )
             * site.settings.product_batch_setting  (0:Hide/Disabled Batches | 1:Select Batch From List | 2:Add Batch While Transaction)
             ***************************************************/
            // item.row.storage_type

            if (parseInt(site.settings.product_batch_setting) > 0) {
                var td_batch = '<td>';
                var batch_required = '';
                if (parseInt(site.settings.product_batch_required) == 2 || (parseInt(site.settings.product_batch_required) == 1 && item.row.storage_type == 'packed')) {
                    batch_required = ' required="required" ';
                }

                if (item.batchs) {
                    // if (parseInt(site.settings.product_batch_setting) == 1) {
                    //     td_batch += '<select class="form-control rbtach_no" name="batch_number[]" ' + batch_required + '  data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
                    //     td_batch += '<option value="" disabled selected>Select a number</option>';
                    //     $.each(item.batchs, function (index, value) {
                    //     // alert(value.batch_no)

                    //         td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>';
                    //     });
                    //     td_batch += '</select>';
                    // }
                    if (parseInt(site.settings.product_batch_setting) == 1 || parseInt(site.settings.product_batch_setting) == 2) {
                        td_batch += '<select class="form-control rbtach_no" name="batch_number[]" ' + batch_required + '  data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
                        td_batch += '<option value="" disabled>Select a number</option>'; // Keep this as the default option
                        
                        var selectedBatchNumber = item.row.batch_number; // Get the pre-selected batch number
                        
                        $.each(item.batchs, function (index, value) {
                            var selected = (value.batch_no === selectedBatchNumber) ? 'selected' : ''; // Check if it's the pre-selected value
                            td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '" ' + selected + '>' + value.batch_no + '</option>';
                        });
                        td_batch += '</select>';
                    }
                    // if (parseInt(site.settings.product_batch_setting) == 2) {
                       
                    //     batchIds = [];
                    //     var batch = item.row.batch_number;
                    //     if(item.row.select_batch == 'change'){
                    //         item.row.batch_number = item.row.batch_number;
                    //     }else{
                    //         item.row.batch_number = '';
                    //     }
                    //     if (item.row.edit == "edit") {
                    //         item.row.batch_number = batch;
                    //     }
                    //     td_batch += '<input list="batches_' + row_no + '" type="text" ' + batch_required + '  class="form-control rbtach_no" name="batch_number[]" id="batch_number_' + row_no + '" value="' + item.row.batch_number + '" ><datalist id="batches_' + row_no + '">';
                    //     $.each(item.batchs, function (index, value) {
                    //         td_batch += '<option data-batchid="' + value.id + '"  value="' + value.batch_no + '" >';
                    //         batchno = value.batch_no;
                    //         batchid = value.id;
                    //         batchIds[batchno] = batchid;
                    //     });
                    //     td_batch += '</datalist>';
                    //     slitems[item_id].product_batches = batchIds; 
                    // }
                } else {
                    var item_batch_number = item.row && item.row.batch_number ? item.row.batch_number : ''; // Ensure item.row exists and has batch_number
                    td_batch += '<input class="form-control rbtach_no" ' + batch_required + ' name="batch_number[]" type="text" value="' + item_batch_number + '" data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';

                }
                td_batch += '</td>';
            }

            tr_html += td_batch;
             
        if (site.settings.product_expiry == 1) {
            tr_html += '<td><input name="cf1[]" type="text" placeholder="MM/YYYY" class="form-control rexpiry cf1" value="' + cf1 + '"> </td>';
        }
                
        tr_html += '<td class="text-center"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">';
        tr_html += '<input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '">';
        tr_html += '<input name="old_qty[]" type="hidden" class="rold_qty" value="' + old_qty + '">';
        tr_html += '<input name="item_storage_type[]" type="hidden" class="rstorage_type" value="' + item.row.storage_type + '">';
        var qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(item_aqty,0) : 1000;
        if(item.row.type == 'combo') {
            var cmax = 1000, cimax = '';
            $.each(combo_items, function () {                         
                cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
                cmax = (cimax > cmax) ? cmax : cimax;                       
            });                    
            qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax,0) : 1000;
        }//end if.
        if(item.row.type == 'Bundle') {
            var cmax = 1000, cimax = '';
            $.each(combo_items, function () {                         
                cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
                cmax = (cimax > cmax) ? cmax : cimax;                       
            });                    
            qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax,0) : 1000;
        }//end if.
                
        if(item.row.storage_type == 'packed' ) {
            var qotp = '', selected = '';            
            for(var q = 1; q <= (qmax?qmax:1); q++){
                selected = '';
                if(formatDecimal(item_qty,0)==q){
                    selected = ' selected="selected" ';
                }
                qotp += '<option '+selected+'>'+q+'</option>';
            }//end for
              tr_html += '<input class="form-control text-center rquantity" name="quantity[]" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" type="number" value="' + formatDecimal(item_qty,3) + '" min="1" max="'+qmax+'" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
            //tr_html += '<select class="form-control text-center rquantity" name="quantity[]" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" >'+qotp+'</select>';
        } else {
            tr_html += '<input class="form-control text-center rquantity" name="quantity[]" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" type="number" value="' + formatDecimal(item_qty,3) + '" min="1" max="'+qmax+'" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
        }
       // tr_html += item.row.unit_lable + '</td>';
        tr_html += '</td>';
        
        // Packing Size column
        var packing_size = (item.row.packing_size) ? item.row.packing_size : 0;
        total_packing_size += parseFloat(packing_size);
        if (site.settings.packing_size_column == 1) {
            tr_html += '<td class="text-center"><input class="form-control text-center rpacking_size quickchange" name="packing_size[]" type="number" value="' + formatDecimal(packing_size, 2) + '" min="0" step="0.01" data-id="' + row_no + '" data-item="' + item_id + '" id="packing_size_' + row_no + '" placeholder="0"></td>';
        } else {
            tr_html += '<input type="hidden" name="packing_size[]" value="' + formatDecimal(packing_size, 2) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="packing_size_' + row_no + '" class="rpacking_size">';
        }
        
        if (site.settings.product_weight == 1) {
            tr_html += '<td class="text-right">'+ formatDecimal(parseFloat(item_weight),3) +' Kg</td>';
            total_item_weight += item_weight;
        }
        
            //tr_html += '<td class="delivery_items"><input class="form-control text-center rdelivered_quantity" tabindex="" name="delivered_quantity[]" type="number" value="' + formatDecimal(item_delivered_qty) + '" min="0" max="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="delivered_quantity_' + row_no + '" onchange="validate_qty(this);" onClick="this.select();"></td>';
            //tr_html += '<td class="delivery_items"><input class="form-control text-center rpending_quantity" tabindex="" name="pending_quantity[]" type="number" value="' + formatDecimal(item_pending_qty) + '" min="0" max="' + formatDecimal(item_qty) + '"  data-id="' + row_no + '" data-item="' + item_id + '" id="pending_quantity_' + row_no + '" onchange="validate_qty(this);" onClick="this.select();"></td>';
            
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + original_unit_price + '"><input class="form-control input-sm text-right quickchange" name="unit_price[]" type="text" id="unit_price_' + row_no + '" value="' + formatDecimal(unit_price) + '"' + (can_edit_price == 1 ? '' : ' readonly') + '>';
            tr_html += '<input class="form-control input-sm text-right rmrp" name="mrp[]" type="hidden" id="mrp_' + row_no + '" value="' + mrp + '"><span class="text-right smrp" id="smrp_' + row_no + '"></span></td>';
                      
            if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount || (typeof invoice_has_discounts !== 'undefined' && invoice_has_discounts == 1)) {
                // tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">'  + (parseFloat(item_ds) != '' ? '(' + item_ds + ')' : '') + '</br>' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>'; // existing
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount quickchange"  name="product_discount[]" type="text" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">'  + (parseFloat(item_ds) != '' ? '(' + item_ds + ')' : '') + '</br>' + formatMoney(0 - ((packing_size > 0 ? item_discount * packing_size : item_discount) * item_qty)) + '</span></td>';
            }
            
            tr_html += '<td class="text-right">' + formatMoney(show_unit_price * (packing_size > 0 ? packing_size : 1) * item_qty) + ' </td>';

            total_netprice += parseFloat(show_unit_price) * parseFloat(packing_size > 0 ? packing_size : 1) * parseFloat(item_qty);//item_price

            if (site.settings.tax1 == 1) {
                // tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (parseFloat(pr_tax_rate) != '' ? '(' + pr_tax_rate + ')' : '') + '</br>' + formatMoney(pr_tax_val * item_qty) + '</span></td>'; // existing
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + tax_rate + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (parseFloat(pr_tax_rate) != 0 ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney((packing_size > 0 ? pr_tax_final_val * packing_size : pr_tax_final_val) * item_qty) + '</span></td>';

            }
            // Apply packing_size multiplier if packing_size > 0
            var item_subtotal = 0;
            if (packing_size > 0) {
                item_subtotal = ((parseFloat(item_price) + parseFloat(pr_tax_final_val)) * parseFloat(packing_size) * parseFloat(item_qty));
            } else {
                item_subtotal = ((parseFloat(item_price) + parseFloat(pr_tax_final_val)) * parseFloat(item_qty));
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(item_subtotal) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer sldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i>  <input name="cf3[]" type="hidden" class="rid cf3" value="' + cf3 + '"> <input name="cf4[]" type="hidden" class="rid cf4" value="' + cf4 + '"> <input name="cf5[]" type="hidden" class="rid cf5" value="' + cf5 + '"> <input name="cf6[]" type="hidden" class="rid cf6" value="' + cf6 + '"></td>';

            newTr.html(tr_html);
            newTr.appendTo("#slTable tbody");
            // Apply packing_size multiplier to total if packing_size > 0
            if (packing_size > 0) {
                total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_final_val)) * parseFloat(packing_size) * parseFloat(item_qty)), 4);
            } else {
                total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_final_val)) * parseFloat(item_qty)), 4);
            }
            count += parseFloat(item_qty);
            an++;
            
            
            if( ( sale_action == 'edit' && base_quantity > old_qty ) || (sale_action != 'edit') ) {
                
                if (item_type == 'standard' && item.options !== false) {
                    $.each(item.options, function () {
                        if ( this.id == item_option && (base_quantity > this.quantity || item_cart_qty[item.item_id] > this.quantity ) ) {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling == 0) {
                                if (sale_action != 'chalan') {
                                    $('#add_sale').attr('disabled', true); // , #edit_sale
                                    $('#print_invoice').attr('disabled', true);
                                }
                                window.overselling_items.push(item.row.name);
                            }
                        }
                    });
                } else if (item_type == 'standard' && (base_quantity > item_aqty || item_cart_qty[item.item_id] > item_aqty) ) {
                    $('#row_' + row_no).addClass('danger');
                    if (site.settings.overselling != 1) {
                        if (sale_action != 'chalan') {
                            $('#add_sale').attr('disabled', true); //, #edit_sale
                            $('#print_invoice').attr('disabled', true);
                        }
                        window.overselling_items.push(item.row.name);
                    }
                } else if (item_type == 'combo') {

                        if (combo_items === false) {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling != 1) {
                                if (sale_action != 'chalan') {
                                    $('#add_sale').attr('disabled', true); // , #edit_sale
                                    $('#print_invoice').attr('disabled', true);
                                }
                                window.overselling_items.push(item.row.name);
                            }
                        } else {
                            $.each(combo_items, function () {
                                if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                                    $('#row_' + row_no).addClass('danger');
                                    if (site.settings.overselling != 1) {
                                        if (sale_action != 'chalan') {
                                            $('#add_sale').attr('disabled', true); //, #edit_sale
                                            $('#print_invoice').attr('disabled', true);
                                        }
                                        window.overselling_items.push(item.row.name);
                                    }
                                }
                            });
                        }
                }else if (item_type == 'Bundle') {

                    if (combo_items === false) {
                        $('#row_' + row_no).addClass('danger');
                        if (site.settings.overselling != 1) {
                            if (sale_action != 'chalan') {
                                $('#add_sale').attr('disabled', true); // , #edit_sale
                                $('#print_invoice').attr('disabled', true);
                            }
                            window.overselling_items.push(item.row.name);
                        }
                    } else {
                        $.each(combo_items, function () {
                            if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                                $('#row_' + row_no).addClass('danger');
                                if (site.settings.overselling != 1) {
                                    if (sale_action != 'chalan') {
                                        $('#add_sale').attr('disabled', true); //, #edit_sale
                                        $('#print_invoice').attr('disabled', true);
                                    }
                                    window.overselling_items.push(item.row.name);
                                }
                            }
                        });
                    }
            }

            }//end if action
            
        });

        var col = 1;
        if (parseInt(site.settings.overselling) == 0) { col++; } //Condition for Item Stocks Coloumn
        if (site.settings.product_serial == 1) {
            col++;
        }
        if(Pos_Settings.display_seller =='4' || Pos_Settings.display_seller =='3'){
            col++;
        }

        if (parseInt(site.settings.product_batch_setting) > 0) {
            col++;
        }
        if (site.settings.product_expiry == 1) {
            col++;
        }
        // if (site.settings.packing_size_column == 1) {
        //     col++; // Packing Size column
        // }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="' + col + '">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';

        if (site.settings.packing_size_column == 1) {
            tfoot += '<th class="text-center">' + formatNumber(parseFloat(total_packing_size)) + '</th>'; // Packing Size column
        }

        if (site.settings.product_weight == 1) {
           tfoot += '<th class="text-right">'+formatDecimal(total_item_weight,3)+' Kg</th>';
        }

         tfoot += '<th class="text-right"></th>';

        if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">' + formatMoney(product_discount) + '</th>';
        }
        
        tfoot += '<th class="text-right">' + formatMoney(total_netprice) + '</th>';
        
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        tfoot += '<th class="text-right">' + formatMoney(total) + '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#slTable tfoot').html(tfoot);

        //Order level discount calculations
       /* if (sldiscount = localStorage.getItem('sldiscount')) {
            var ds = sldiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((((total) * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }
            //total_discount += parseFloat(order_discount);
        } */

        //Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (sltax2 = localStorage.getItem('sltax2')) {
                $.each(tax_rates, function () {
                    if (this.id == sltax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        } else if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }

        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        //$('#tds').text('('+formatMoney(product_discount)+'+'+formatMoney(order_discount)+')'+formatMoney(total_discount));
        $('#tds').text(formatMoney(total_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        if (count > 1) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        }
        set_page_focus();
        // show_hide_delevey_options($('#sldelivery_status').val());
    }
}

function validate_qty(Obj) {

    if (parseInt(Obj.value) > parseInt(Obj.max)) {
        Obj.value = Obj.max
    }
    if (parseInt(Obj.value) < 0) {
        Obj.value = 0
    }
}
/* -----------------------------
 * Add Sale Order Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_invoice_item(item) {

    if (count == 1) {
        slitems = {};
        if ($('#slwarehouse').val() && $('#slcustomer').val()) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    
    if (slitems[item_id]) {
        slitems[item_id].row.qty = parseFloat(slitems[item_id].row.qty) +  parseFloat(item.row.qty);
    } else {
        slitems[item_id] = item;
    }
    slitems[item_id].order = new Date().getTime();
    localStorage.setItem('slitems', JSON.stringify(slitems));
    loadItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}
$(document).on('change', '.prsalesperson', function(){
    var row = $(this).closest('tr');
    var row_id = row.attr('id');
    $('#row_id').val(row_id);
    item_id = row.attr('data-item-id');
    
    item = slitems[item_id];
    var prosp = $(this).val();
    var expsp = prosp.split('~');
    
    slitems[item_id].row.seller_id	 = expsp[0];
    localStorage.setItem('slitems', JSON.stringify(slitems));

    loadItems();
})
$(document).on('change', '.rpacking_size', function () {
    var val = parseFloat($(this).val());
    if (val < 0) {
        var item_id = $(this).attr('data-item');
        var old_packing_size = (slitems[item_id] && slitems[item_id].row && slitems[item_id].row.packing_size) ? slitems[item_id].row.packing_size : 0;
        bootbox.alert("Packing Size cannot be negative!");
        $(this).val(old_packing_size);
        $(this).trigger('change');
        return false;
    }
});

$(document).on('change', '.quickchange', function () {
    
    var row = $(this).closest('tr');
    var row_id = row.attr('id');
    $('#row_id').val(row_id);
    item_id = row.attr('data-item-id');

    item = slitems[item_id];
    var rowno = row_id.replace("row_", "");
    var real_unit_price = item.row.real_unit_price;
    var unit_price = $('#unit_price_' + rowno).val() ? parseFloat($('#unit_price_' + rowno).val()) : slitems[item_id].row.real_unit_price;
    
    var discount = $('#discount_' + rowno).val() ? $('#discount_' + rowno).val() : '0';
    if (discount.indexOf("%") !== -1) {
        var pds = discount.split("%");
        if (!isNaN(pds[0])) {
            item_discount = formatDecimal((((unit_price) * parseFloat(pds[0])) / 100), 4);
        } else {
            item_discount = formatDecimal(discount);
        }
    } else {
        item_discount = formatDecimal(discount);
    }
    // var net_unit_price = unit_price - item_discount;
    var net_unit_price = unit_price;

    $('#price_' + rowno).val(net_unit_price);

    if (net_unit_price >= 1000) {
        tax_rate = 16;
    } else {
        tax_rate = 15;
    }
    slitems[item_id].row.price = net_unit_price;
    //slitems[item_id].row.tax_method    = 0;         
    // slitems[item_id].row.tax_rate = tax_rate;
    slitems[item_id].row.real_unit_price = unit_price;
    slitems[item_id].row.base_unit_price = unit_price;
    slitems[item_id].row.discount = discount;
    if ($('#packing_size_' + rowno).length) {
        slitems[item_id].row.packing_size = $('#packing_size_' + rowno).val() ? parseFloat($('#packing_size_' + rowno).val()) : 0;
    }
    slitems[item_id].row.manualedit = 1; // Note :  Manual Price Edit 
    localStorage.setItem('slitems', JSON.stringify(slitems));
    loadItems();

});
// show customer name in address modal title
function getCustomerDisplayNameForAddress() {
    var name = '';
    var $customer = $('#slcustomer');
    if ($customer.length) {
        try {
            var data = $customer.select2('data');
            if (data) {
                if ($.isArray(data) && data.length) {
                    name = data[0].text || '';
                } else if (data.text) {
                    name = data.text;
                }
            }
        } catch (e) {}
        if (!name) {
            var val = $.trim($customer.val());
            if (val && isNaN(val)) {
                name = val;
            }
        }
    }
    if (!name) {
        var companyText = $.trim($('#companyName').text());
        if (companyText && companyText !== '-') {
            name = companyText;
        }
    }
    return name;
}
// show customer name in address modal title
function formatAddressModalTitle(baseTitle) {
    var customerName = getCustomerDisplayNameForAddress();
    return customerName ? (baseTitle + ' (' + customerName + ')') : baseTitle;
}

$(document).ready(function () {
    if (!$('#slcustomer').length) {
        return;
    }

    function getSaleScreenCustomerId() {
        var cid = $.trim(String($('#quick_custome_nm').val() || ''));
        if (!/^\d+$/.test(cid)) {
            var $customer = $('#slcustomer');
            try {
                if ($customer.data('select2')) {
                    var s2data = $customer.select2('data');
                    if (s2data && s2data.id) {
                        cid = $.trim(String(s2data.id));
                    }
                }
            } catch (e) {}
            if (!/^\d+$/.test(cid)) {
                cid = $.trim(String($customer.val() || ''));
            }
        }
        if (!/^\d+$/.test(cid)) {
            cid = $.trim(String(localStorage.getItem('slcustomer') || ''));
        }
        return (/^\d+$/.test(cid)) ? cid : '';
    }

    function loadSaleScreenModal(url) {
        var $modal = $('#myModal');
        $modal.removeData('bs.modal');
        $modal.empty();
        $('#modal-loading').show();
        $modal.load(url, function (response, status, xhr) {
            $('#modal-loading').hide();
            if (status === 'error' || (xhr && xhr.status >= 400)) {
                $modal.html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">' + lang.unexpected_value + ' (HTTP ' + (xhr ? xhr.status : '') + ')</div></div></div></div>');
            }
            $modal.modal('show');
        });
    }

    function openSaleCustomerModal(remotePath) {
        var customerId = getSaleScreenCustomerId();
        if (!customerId) {
            bootbox.alert(lang.select_above);
            return;
        }
        loadSaleScreenModal(remotePath + customerId);
    }

    $('#view-customer').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        openSaleCustomerModal(site.base_url + 'customers/view/');
        return false;
    });

    $('.edit-customers').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        openSaleCustomerModal(site.base_url + 'customers/edit/');
        return false;
    });

    $('#add-customer').off('click').on('click', function (e) {
        var href = $.trim(String($(this).attr('href') || ''));
        if (!href || href === '#') {
            return false;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        loadSaleScreenModal(href);
        return false;
    });
});

function getPrTaxRateObj(pr_tax, tax_rate_id) {
    if (pr_tax && typeof pr_tax === 'object' && pr_tax.id) {
        return pr_tax;
    }
    var pr_tax_id = tax_rate_id || pr_tax;
    if (!pr_tax_id || typeof tax_rates === 'undefined') {
        return null;
    }
    var pr_tax_rate_obj = null;
    $.each(tax_rates, function () {
        if (this.id == pr_tax_id) {
            pr_tax_rate_obj = this;
            return false;
        }
    });
    return pr_tax_rate_obj;
}

function calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, item_qty) {
    item_qty = parseFloat(item_qty) || 1;
    pr_tax_val = parseFloat(pr_tax_val) || 0;
    var pr_tax_line = formatDecimal(pr_tax_val * item_qty, 6);
    var pr_tax_surcharge_val = 0;
    var pr_tax_surcharge_line = 0;

    if (pr_tax_rate_obj && pr_tax_rate_obj.surcharge_rate !== null && pr_tax_rate_obj.surcharge_rate !== '' && pr_tax_rate_obj.surcharge_rate !== undefined && parseFloat(pr_tax_rate_obj.surcharge_rate) > 0) {
        var surcharge_rate = parseFloat(pr_tax_rate_obj.surcharge_rate);
        var surcharge_type = String(pr_tax_rate_obj.surcharge_type);
        if (surcharge_type == '1') {
            pr_tax_surcharge_val = formatDecimal((pr_tax_val * surcharge_rate) / 100, 6);
            pr_tax_surcharge_line = formatDecimal((pr_tax_line * surcharge_rate) / 100, 6);
        } else if (surcharge_type == '2') {
            pr_tax_surcharge_val = formatDecimal(surcharge_rate, 6);
            pr_tax_surcharge_line = formatDecimal(surcharge_rate * item_qty, 6);
        }
    }

    return {
        pr_tax_val: pr_tax_val,
        pr_tax_surcharge_val: parseFloat(pr_tax_surcharge_val),
        pr_tax_surcharge_line: parseFloat(pr_tax_surcharge_line),
        pr_tax_final_val: formatDecimal(pr_tax_val + parseFloat(pr_tax_surcharge_val), 6),
        pr_tax_final_line: formatDecimal(parseFloat(pr_tax_line) + parseFloat(pr_tax_surcharge_line), 6),
        has_pr_tax_surcharge: parseFloat(pr_tax_surcharge_val) > 0
    };
}

function formatPrTaxSurchargeDisplay(pr_tax_val, pr_tax_surcharge) {
    if (!pr_tax_surcharge || !pr_tax_surcharge.has_pr_tax_surcharge) {
        return formatMoney(pr_tax_val);
    }
    return formatMoney(pr_tax_val) + ' + ' + formatMoney(pr_tax_surcharge.pr_tax_surcharge_val) + ' = ' + formatMoney(pr_tax_surcharge.pr_tax_final_val);
}

function setPrTaxDisplay(selector, pr_tax_val, pr_tax_rate_obj) {
    var pr_tax_surcharge = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, 1);
    $(selector).data('base-tax', pr_tax_val);
    $(selector).text(formatPrTaxSurchargeDisplay(pr_tax_val, pr_tax_surcharge));
}

function getPrTaxBaseVal(selector) {
    var pr_tax_base = $(selector).data('base-tax');
    if (pr_tax_base !== undefined && pr_tax_base !== null && pr_tax_base !== '') {
        return parseFloat(pr_tax_base) || 0;
    }
    return parseFloat($(selector).text().trim().replace(/,/g, '')) || 0;
}
 