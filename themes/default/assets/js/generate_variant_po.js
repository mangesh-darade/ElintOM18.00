/*
 * Generate Variant PO screen script
 *
 * Business logic (why):
 * - This is the standard Purchase Order add/edit logic, reused by the Variant PO flow.
 * - Variant_Order stages selected variants/quantities in session and injects a lightweight
 *   JS payload window.__dummyItems when we land on this screen.
 * - We intentionally wait for the user to select a Supplier before auto-adding staged items
 *   into the grid to keep supplier-driven pricing/taxes consistent.
 * - Most screen state is persisted in localStorage to preserve user input across refresh.
 */
function validateSubmitButton() {
    var hasSupplier = $('#posupplier').val() && $('#posupplier').val() !== '' && $('#posupplier').val() !== '0';
    var hasItems = Object.keys(variantpoitems).length > 0;
    
    if (hasSupplier && hasItems) {
        $('#add_pruchase').prop('disabled', false);
        $('#print_barcode').prop('disabled', false);
    } else {
        $('#add_pruchase').prop('disabled', true);
        $('#print_barcode').prop('disabled', true);
    }
}

$(document).ready(function () {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    
    // Initial validation check
    validateSubmitButton();

// Order level shipping and discoutn localStorage
    if (podiscount = localStorage.getItem('podiscount')) {
        $('#podiscount').val(podiscount);
    }
    $('#potax2').change(function (e) {
        localStorage.setItem('potax2', $(this).val());
    });
    if (potax2 = localStorage.getItem('potax2')) {
        $('#potax2').select2("val", potax2);
    }
    $('#postatus').change(function (e) {
        localStorage.setItem('postatus', $(this).val());
    });
    if (postatus = localStorage.getItem('postatus')) {
        $('#postatus').select2("val", postatus);
    }
    var old_shipping;
    $('#poshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if ($(this).val() != '') {
            if (!is_numeric($(this).val())) {
                $(this).val(old_shipping);
                bootbox.alert(lang.unexpected_value);
                return;
            } else {
                shipping = $(this).val() ? parseFloat($(this).val()) : '0';
            }
        } else {
            shipping = 0;
            localStorage.removeItem('toshipping');
        }

        localStorage.setItem('poshipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (poshipping = localStorage.getItem('poshipping')) {
        shipping = parseFloat(poshipping);
        $('#poshipping').val(shipping);
        $('#tship').text(formatMoney(shipping));
    }

    $('#popayment_term').change(function (e) {
        localStorage.setItem('popayment_term', $(this).val());
    });
    if (popayment_term = localStorage.getItem('popayment_term')) {
        $('#popayment_term').val(popayment_term);
    }

// If there is any item in localStorage
    // If there are any items stored (or added by the Variant PO loader), render them
    if (localStorage.getItem('variantpoitems')) {
        loadItems();
    } else {
        // If no items, ensure button is disabled
        validateSubmitButton();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.removeItem('variantpoitems')) {
                    variantpoitems = {};
                    localStorage.removeItem('variantpoitems');
                    count = 1;
                    an = 1;
                    po_edit = false;
                    loadItems();
                    validateSubmitButton();
                }
                if (localStorage.getItem('podiscount')) {
                    localStorage.removeItem('podiscount');
                }
                if (localStorage.getItem('potax2')) {
                    localStorage.removeItem('potax2');
                }
                if (localStorage.getItem('poshipping')) {
                    localStorage.removeItem('poshipping');
                }
                if (localStorage.getItem('poref')) {
                    localStorage.removeItem('poref');
                }
                if (localStorage.getItem('powarehouse')) {
                    localStorage.removeItem('powarehouse');
                }
                if (localStorage.getItem('ponote')) {
                    localStorage.removeItem('ponote');
                }
                if (localStorage.getItem('posupplier')) {
                    localStorage.removeItem('posupplier');
                }
                if (localStorage.getItem('pocurrency')) {
                    localStorage.removeItem('pocurrency');
                }
                if (localStorage.getItem('poextras')) {
                    localStorage.removeItem('poextras');
                }
                if (localStorage.getItem('podate')) {
                    localStorage.removeItem('podate');
                }
                if (localStorage.getItem('postatus')) {
                    localStorage.removeItem('postatus');
                }
                if (localStorage.getItem('popayment_term')) {
                    localStorage.removeItem('popayment_term');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

// save and load the fields in and/or from localStorage
    var $supplier = $('#posupplier'), $currency = $('#pocurrency');

    // Purchase reference is synced to localStorage
    $('#poref').change(function (e) {
        localStorage.setItem('poref', $(this).val());
    });
    if (poref = localStorage.getItem('poref')) {
        $('#poref').val(poref);
    }
    $('#powarehouse').change(function (e) {
        localStorage.setItem('powarehouse', $(this).val());
    });
    if (powarehouse = localStorage.getItem('powarehouse')) {
        $('#powarehouse').select2("val", powarehouse);
    }

    // Use Redactor for rich-text PO note; also used by Variant PO to prefill default notes
    $('#ponote').redactor('destroy');
    $('#ponote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('ponote', v);
        }
    });
    if (ponote = localStorage.getItem('ponote')) {
        $('#ponote').redactor('set', ponote);
    }
    $supplier.change(function (e) {
        validateSubmitButton();
        localStorage.setItem('posupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if (posupplier = localStorage.getItem('posupplier')) {
        $supplier.val(posupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "suppliers/getSupplierName/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
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
        
        // Make supplier field readonly after initialization
        setTimeout(function() {
            $('#posupplier').select2('enable', false);
            $('#posupplier').next('.select2-container').css({
                'pointer-events': 'none',
                'opacity': '0.65'
            });
        }, 100);

    } else {
        nsSupplier();
    }

    /*$('.rexpiry').change(function (e) {
     var item_id = $(this).closest('tr').attr('data-item-id');
     variantpoitems[item_id].row.expiry = $(this).val();
     localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
     });*/
    if (localStorage.getItem('poextras')) {
        $('#extras').iCheck('check');
        $('#extras-con').show();
    }
    $('#extras').on('ifChecked', function () {
        localStorage.setItem('poextras', 1);
        $('#extras-con').slideDown();
    });
    $('#extras').on('ifUnchecked', function () {
        localStorage.removeItem("poextras");
        $('#extras-con').slideUp();
    });
    $(document).on('change', '.rexpiry', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        variantpoitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
    });
    $(document).on('change', '.rbtach_no', function () {
        /* var item_id = $(this).closest('tr').attr('data-item-id');
         variantpoitems[item_id].row.batch_number = $(this).val();
         localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));*/

        var item_id = $(this).closest('tr').attr('data-item-id');
        var batch = $(this).val();
        var batch_id = $(this).find(':selected').attr('data-batchid');

        batch_id = batch_id ? batch_id : (variantpoitems[item_id].batchsData[batch] ? variantpoitems[item_id].batchsData[batch] : false);
        var select_batch = 'change'
        variantpoitems[item_id].row.batch_number = batch;
        variantpoitems[item_id].row.select_batch = select_batch;


        if (batch_id) {
            variantpoitems[item_id].row.batch = batch_id;

            var batchvalue = variantpoitems[item_id].batchs[batch_id];

            variantpoitems[item_id].row.cost = batchvalue['cost'];
            variantpoitems[item_id].row.real_unit_cost = batchvalue['cost'];
            variantpoitems[item_id].row.base_unit_cost = batchvalue['cost'];
            variantpoitems[item_id].row.expiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
        }

        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
        loadItems();
    });

    $(document).on('change', '#pbatch_number', function () {
        if (parseInt(site.settings.product_batch_setting) > 0) {
            onBatchChanged();
        }
    });

    $(document).on('change', '#poption', function () {
       
        onVariantChanged();
         
    });

// prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

// Order tax calcuation
    if (site.settings.tax2 != 0) {
        $('#potax2').change(function () {
            localStorage.setItem('potax2', $(this).val());
            loadItems();
            return;
        });
    }

// Order discount calcuation
    var old_podiscount;
    $('#podiscount').focus(function () {
        old_podiscount = $(this).val();
    }).change(function () {
        if (is_valid_discount($(this).val())) {
            localStorage.removeItem('podiscount');
            localStorage.setItem('podiscount', $(this).val());
            loadItems();
            return;
        } else {
            localStorage.removeItem('podiscount');
            loadItems();
            //$(this).val(old_podiscount);
            // bootbox.alert(lang.unexpected_value);
            return;
        }

    });


    /* ----------------------
     * Delete Row Method
     * ---------------------- */

    $(document).on('click', '.podel', function () {

        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete variantpoitems[item_id];
        row.remove();
        if (variantpoitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
            loadItems();
            return;
        }
    });

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
    $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = variantpoitems[item_id];

        if (parseInt(site.settings.product_batch_setting) > 0) {
            var batch_number = row.children().children('.rbtach_no').val();
        }
        var qty = row.children().children('.rquantity').val(),
                product_option = row.children().children('.roption').val(),
                unit_cost = formatDecimal(row.children().children('.rucost').val()),
                discount = row.children().children('.rdiscount').val();
                product_option_color = row.children().children('.roption_color').val(),

        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        var real_unit_cost = item.row.real_unit_cost;
        var net_cost = real_unit_cost;
        var tax_method = item.row.tax_method;
        if (site.settings.tax1) {
            // $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat(((real_unit_cost) * parseFloat(pds[0])) / 100);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_cost -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {

                            if (variantpoitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((((real_unit_cost - item_discount) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_cost -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((((real_unit_cost - item_discount) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }

        if (parseInt(site.settings.product_batch_setting) > 0) {
            var edtbatch = '<p style="margin: 12px 0 0 0;"><input class="form-control" name="pbatch_number" type="text" id="pbatch_number"></p>';
            if (parseInt(site.settings.product_batch_setting) == 1) {
                if (item.batchs) {
                    var b = 1;
                    edtbatch = $('<select id="pbatch_number" name="pbatch_number" class="form-control" />');
                    $.each(item.batchs, function () {
                        $('<option data-batchid="' + this.id + '" data-cost="' + this.cost + '" value="' + this.batch_no + '" >' + this.batch_no + '</option>').appendTo(edtbatch);
                        b++;
                    });
                }
            } else if (parseInt(site.settings.product_batch_setting) == 2) {
                if (item.batchs) {
                    edtbatch = '<input list="batches" class="form-control" name="pbatch_number" id="pbatch_number"><datalist id="batches">';
                    $.each(item.batchs, function () {
                        edtbatch += '<option data-batchid="' + this.id + '" data-cost="' + this.cost + '" value="' + this.batch_no + '" >' + this.batch_no + '</option>';
                        batchno = this.batch_no;
                        batchid = this.id;
                        batchIds[batchno] = batchid;
                    });
                    edtbatch += '</datalist>';
                    variantpoitems[item_id].batchsData = batchIds;
                }
            }
         
            $('#batchNo_div').html(edtbatch);
            $('#pbatch_number').select2('val', item.row.batch_number);
        }


        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.row.storage_type == 'packed') {
            if (item.options !== false) {
                var o = 1;
                opt = $('<select id="poption" name="poption" class="form-control select" />');
                $.each(item.options, function () {
                    if (o == 1) {
//                        if (product_option == '') {
//                            product_variant = this.id;
//                        } else {
//                            product_variant = product_option;
//                        }
                    }
                    $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                    o++;
                });
            }
        }
         var opt_color = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.options_color !== false) {
            var o = 1;
            opt_color = $("<select id=\"poption_color\" name=\"poption_color\" class=\"form-control select\" />");
            $.each(item.options_color, function () {
                console.log(product_option_color + ' ' + this.id);
                if (o == 1) {
                    if (product_option_color == '') {
                        product_variant_color = this.id;
                    } else {
                        product_variant_color = product_option_color;
                    }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt_color);
                o++;
            });
        }
        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if (this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected: true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });


        $('#prModal').appendTo("body").modal('show');

        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pcost').val(unit_cost);
        $('#punit_cost').val(formatDecimal(parseFloat(unit_cost) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_cost').val(unit_cost);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pexpiry').val(row.children().children('.rexpiry').val());
        $('#pbatch_number').val(batch_number);
        $('#pdiscount').val(discount);
        $('#net_cost').text(formatMoney(net_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#tax_method').val(tax_method).trigger('change');
        $('#ptax').val(pr_tax).trigger('change');
        $('#storage_type').val(item.row.storage_type);
        //   $('#ptax').val(pr_tax_rate);
        $('#psubtotal').val('');
        calculateCost();

    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pcost, #ptax, #tax_method, #pdiscount', function () {

        var pr_tax = $('#ptax').val();

        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_cost = parseFloat($('#pcost').val());
        var item = variantpoitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_cost -= item_discount;
        var tax_method = $('#tax_method').val();
        var /*pr_tax = $('#ptax').val(),*/ item_tax_method = tax_method;  //item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((((unit_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_cost -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((((unit_cost) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_cost').text(formatMoney(unit_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = variantpoitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        if (unit != variantpoitems[item_id].row.base_unit) {
            $.each(item.units, function () {
                if (this.id == unit) {
                    $('#pcost').val(formatDecimal((parseFloat(item.row.base_unit_cost) * (unitToBaseQty(1, this))), 4)).change();
                }
            });
        } else {
            $('#pcost').val(formatDecimal(item.row.base_unit_cost)).change();
        }
    });

    $(document).on('click', '#calculate_unit_price', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = variantpoitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var subtotal = parseFloat($('#psubtotal').val()),
                qty = parseFloat($('#pquantity').val());
        $('#pcost').val(formatDecimal((subtotal / qty), 4)).change();
        return false;
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
    $(document).on('click', '#editItem', function () {

        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = {};
        var tax_method = $('#tax_method').val();
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }

        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }

        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != variantpoitems[item_id].row.base_unit) {
            $.each(variantpoitems[item_id].units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }

        if (parseInt(site.settings.product_batch_setting) > 0) {
            var batch = $('#pbatch_number').val();
            var batch_id = $('#pbatch_number').find(':selected').attr('data-batchid');
            var cost = $('#pbatch_number').find(':selected').attr('data-cost');

            variantpoitems[item_id].row.batch_number = batch;
            variantpoitems[item_id].row.batch = batch_id;
            variantpoitems[item_id].row.cost = cost;
        }

        variantpoitems[item_id].row.fup = 1,
        variantpoitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        variantpoitems[item_id].row.base_quantity = parseFloat(base_quantity),
        variantpoitems[item_id].row.unit = unit,
        variantpoitems[item_id].row.unit_lable = document.getElementById('punit').selectedOptions[0].text,
        variantpoitems[item_id].row.real_unit_cost = parseFloat($('#pcost').val()),
        variantpoitems[item_id].row.tax_rate = new_pr_tax,
        variantpoitems[item_id].tax_rate = new_pr_tax_rate,
        variantpoitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '0',
        variantpoitems[item_id].row.option = $('#poption').val(),
        variantpoitems[item_id].row.expiry = $('#pexpiry').val() ? $('#pexpiry').val() : '';

        variantpoitems[item_id].row.tax_method = tax_method;
        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var batch_number = row.find('.rbtach_no').val();
        
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }

        // Update batch number in variantpoitems before reloading items
        if (variantpoitems[item_id]) {
            // Get the batch number from the input field if it exists
            var current_batch = row.find('.rbtach_no').val();
            if (current_batch !== undefined) {
                variantpoitems[item_id].row.batch_number = current_batch;
            }
        }

        var new_qty = parseFloat($(this).val());
        variantpoitems[item_id].row.base_quantity = new_qty;
        
        if (variantpoitems[item_id].row.unit != variantpoitems[item_id].row.base_unit) {
            $.each(variantpoitems[item_id].units, function () {
                if (this.id == variantpoitems[item_id].row.unit) {
                    variantpoitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        
        variantpoitems[item_id].row.qty = new_qty;
        variantpoitems[item_id].row.received = new_qty;

        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
        loadItems();
    });

    var old_received;
    $(document).on("focus", '.received', function () {
        old_received = $(this).val();
    }).on("change", '.received', function () {
        var row = $(this).closest('tr');
        new_received = $(this).val() ? $(this).val() : 0;
        if (!is_numeric(new_received)) {
            $(this).val(old_received);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_received = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
        if (new_received > variantpoitems[item_id].row.qty) {
            $(this).val(old_received);
            bootbox.alert(lang.unexpected_value);
            return;
        }

//        unit = formatDecimal(row.children().children('.runit').val()),
//        $.each(variantpoitems[item_id].units, function(){
//            
//            if (this.id == unit) {
//                 
//                qty_received = formatDecimal(unitToBaseQty(new_received, this), 4);
//            }
//        });


        variantpoitems[item_id].row.unit_received = new_received;
        variantpoitems[item_id].row.received = new_received;
//        variantpoitems[item_id].row.received = qty_received;
        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Cost Method
     -------------------------- */
    var old_cost;
    $(document).on("focus", '.rcost', function () {
        old_cost = $(this).val();
    }).on("change", '.rcost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_cost = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
        variantpoitems[item_id].row.cost = new_cost;
        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
        $('#posupplier').select2('readonly', false);
        return false;
    });

    if (po_edit) {
        $('#posupplier').select2("readonly", false);
    }

    $('.pcalculate').on('change', function(){
        
        calculateCost();
        
    });

});
/* -----------------------
 * Misc Actions
 ----------------------- */


function onVariantChanged() {
    var item_id = $('#item_id').val();
    var poption = $('#poption').val();
        
    var selected = '';    
       
    var itemOptions = variantpoitems[item_id].options
    
    if (parseInt(site.settings.product_batch_setting) > 0) {
                
        var batch_id = variantpoitems[item_id].row.batch;
        var batch_number = variantpoitems[item_id].row.batch_number;
        
        var b = 0;
        var first_batch_number = '';
        var batchIds = [];
        var btc = '<input type="text" class="form-control" name="pbatch_number" id="pbatch_number" value="' + batch_number + '">';
    
        if (variantpoitems[item_id].option_batches !== false && variantpoitems[item_id].option_batches[poption]) {
            var optionBatched = variantpoitems[item_id].option_batches[poption];
        
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
                variantpoitems[item_id].batchsData = batchIds;
            }
        }
    
        $('#batchNo_div').html(btc);
        $('#pbatch_number').select2('val', batch_number ? batch_number : first_batch_number);

        variantpoitems[item_id].batchs = optionBatched ? optionBatched : false;
                 
    } else {
        
        $.each(itemOptions, function () {
            if (poption == this.id) {
                var opt_cost = this.cost;
                $('#pcost').val(opt_cost);
            } 
        });
        
    }
    
    calculateCost();
    
    localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
}

function calculateCost(){
    
    var item_id         = $('#item_id').val();
    var real_unit_cost  = $('#pcost').val();
    var ptax            = $('#ptax').val();     
    var pquantity = parseFloat($('#pquantity').val()) ? $('#pquantity').val() : 1;  
   
    var net_cost = real_unit_cost;       
    var item_discount = 0; 
    var ds = $('#pdiscount').val();
    if (ds.indexOf("%") !== -1) {
        var pds = ds.split("%");
        if (!isNaN(pds[0])) {
            item_discount = parseFloat(((real_unit_cost) * parseFloat(pds[0])) / 100);
        } else {
            item_discount = parseFloat(ds);
        }
    } else {
        item_discount = parseFloat(ds);
    }
     
    var unit_cost = parseFloat(real_unit_cost) - parseFloat(item_discount);
         
    var pr_tax_val = 0, pr_tax_rate = 0, unit_tax = 0;
    if (site.settings.tax1 == 1) {
        
        var tax_rate   = variantpoitems[item_id].tax_rate;
        var tax_method = $('#tax_method').val(); // 0:Inclusive | 1:Exclusive
    
        $.each(tax_rates, function () {
            if (this.id == ptax) {
                pr_tax_rate = this.rate;
                pr_tax_type = this.type;                 
            }
        });
        
        if (pr_tax_rate) {
            if (pr_tax_type == 1) {
                if (tax_method == '0') {
                    pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax_rate)) / (100 + parseFloat(pr_tax_rate)), 4);
                    pr_tax_rate = formatDecimal(pr_tax_rate) + '%';
                } else {
                    pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax_rate)) / 100, 4);
                    pr_tax_rate = formatDecimal(pr_tax_rate) + '%';
                }

            } else if (pr_tax_type == 2) {

                pr_tax_val = parseFloat(pr_tax_rate);
                pr_tax_rate = pr_tax_rate;
            }            
        }
    }
    
    var item_tax = pr_tax_val * pquantity;
    
    net_cost = tax_method == 0 ? formatDecimal(unit_cost - item_tax, 4) : formatDecimal(unit_cost);     
    net_cost = Math.abs(net_cost);
        
    $('#net_cost').html(formatMoney(net_cost));
    $('#pro_tax').html(formatMoney(item_tax));
    
    var subtotal =  parseFloat(pquantity) * parseFloat(net_cost);
    $('#psubtotal').val(formatMoney(subtotal));
}
 
function onBatchChanged() {

    var item_id     = $('#item_id').val();
    var batch       = $('#pbatch_number').val();
    var batch_id    = $('#pbatch_number').find(':selected').attr('data-batchid'); 

    batch_id = batch_id ? batch_id : (variantpoitems[item_id].batchsData[batch] ? variantpoitems[item_id].batchsData[batch] : false)

    if (batch_id && variantpoitems[item_id].batchs[batch_id]) {

        var batchvalue = variantpoitems[item_id].batchs[batch_id];

        var batch_cost = batchvalue['cost'];
        var pexpiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
        $('#pcost').val(batch_cost);
        $('#pexpiry').val(pexpiry);
        
        calculateCost();        
    }
}
 
function getVariant_Detail(VarientId, ItemId) {

    variantpoitems[ItemId].row.option = VarientId;

    if (variantpoitems[ItemId].option_batches[VarientId] && variantpoitems[ItemId].row.storage_type == 'packed') {
        variantpoitems[ItemId].batchs = variantpoitems[ItemId].option_batches[VarientId];
    }

    localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));

    loadItems();
}

// helper function for supplier if no localStorage value
function nsSupplier() {
    $('#posupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
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
    
    // Make supplier readonly after initialization (when no localStorage)
    setTimeout(function() {
        if ($('#posupplier').val()) {
            $('#posupplier').select2('enable', false);
            $('#posupplier').next('.select2-container').css({
                'pointer-events': 'none',
                'opacity': '0.65'
            });
        }
    }, 100);
}

// Function to update batch number when changed
function updateBatchNumber(input) {
    var row = $(input).closest('tr');
    var item_id = row.attr('data-item-id');
    var batch_number = $(input).val();
    
    if (variantpoitems[item_id]) {
        variantpoitems[item_id].row.batch_number = batch_number;
        localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
    }
}

function loadItems() {

    if (localStorage.getItem('variantpoitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        total_netcost = 0;
        total_received_qty = 0;
        
        $("#poTable tbody").empty();
        purcahsestatus = ($('#postatus').val() == 'ordered' ? 'display:none' : 'display:block');

        variantpoitems = JSON.parse(localStorage.getItem('variantpoitems'));

        // Clean up null or invalid items
        $.each(variantpoitems, function(key, item) {
            if (!item || !item.row) {
                delete variantpoitems[key];
            }
        });

        ItemsCount = Object.keys(variantpoitems).length;
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(variantpoitems, function (o) {
            return [parseInt(o.order)];
        }) : variantpoitems;
        var cart_item_unit_count = 0;
        var footerUnitLabel = '';
        $.each(sortedItems, function () {
            if (this && this.row && this.row.qty) {
                cart_item_unit_count += parseFloat(this.row.qty);
                if (!footerUnitLabel && this.row.unit_lable) {
                    footerUnitLabel = this.row.unit_lable;
                }
            }
        });
        var order_no = new Date().getTime();
        
        
            
        $.each(sortedItems, function () {

            var item = this;
            if (!item || !item.row) {
                return true; // Skip this item
            }
            
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;

            var unit_lable = item.row.unit_lable;
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_cost = item.row.cost, item_oqty = item.row.oqty, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_option_color = item.row.option_color,item_option_color_name = item.row.option_color_name, item_expiry = item.row.expiry, item_batch_number = item.row.batch_number, item_tax_method = item.row.tax_method, item_option = (typeof item.row.option !== 'undefined') ? item.row.option : 0, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var qty_received = (item.row.received >= 0) ? item.row.received : item.row.qty;
            var item_supplier_part_no = item.row.supplier_part_no ? item.row.supplier_part_no : '';
            if (item.row.new_entry == 1) {
                item_bqty = item_qty;
                item_oqty = item_qty;
            }
            // var unit_cost = item.row.real_unit_cost;
            var product_unit = item.row.unit;
            var base_quantity = item.row.base_quantity;
            var supplier = localStorage.getItem('posupplier'), belong = false;
            var hsn_code = item.row.hsn_code;
            var item_ds = item.row.discount, item_discount = 0;
            var unit_cost = 0;
            // for variant popup functionality
            if (item.options && item.options.length > 0) {
                var optionId = item.row.option;
                var OptionData = item.options.find(option => option.id === optionId);
                if (OptionData) {
                    // unit_cost = OptionData.cost;
                    // item.row.real_unit_cost = OptionData.cost;
                    unit_cost = item.row.real_unit_cost;
                    item.row.real_unit_cost = item.row.real_unit_cost;
                } else {
                    unit_cost = item.row.real_unit_cost;
                }
            } else {
                unit_cost = item.row.real_unit_cost;
            }
            
            /** New Login Order Discount ***/
            if(item_ds != '' && item_ds) {
                var ds = item_ds ? item_ds : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        item_discount = formatDecimal((parseFloat(((unit_cost) * parseFloat(pds[0])) / 100)), 6);
                    } else {
                        item_discount = formatDecimal(ds);
                    }
                } else {
                    item_discount = formatDecimal(ds);
                }
                product_discount += parseFloat(item_discount * item_qty);  
            }
            
            /** New Logic Order Discount **/
            if(site.settings.purchase_order_discount == 1) {

                var posdiscount = localStorage.getItem('podiscount');
                 //Order Common Discount Calculations     
                if (posdiscount != '' && posdiscount != null) {

                    var ods = posdiscount;
                    if (ods.indexOf("%") !== -1) {
                        var pds = ods.split("%");
                        if (!isNaN(pds[0])) {
                            item_discount = formatDecimal((parseFloat(((unit_cost) * parseFloat(pds[0])) / 100)), 6);
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
                   
                    product_discount += parseFloat(item_discount * item_qty); 
                    //Set Order Discount Value null.
                }
            }//end if site.settings.purchase_order_discount

            /*
            if (supplier == item.row.supplier1) {
                belong = true;
            } else
            if (supplier == item.row.supplier2) {
                belong = true;
            } else
            if (supplier == item.row.supplier3) {
                belong = true;
            } else
            if (supplier == item.row.supplier4) {
                belong = true;
            } else
            if (supplier == item.row.supplier5) {
                belong = true;
            }
            */
            var unit_qty_received = qty_received;
            if (item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_qty_received = item.row.unit_received ? item.row.unit_received : formatDecimal(baseToUnitQty(qty_received, this), 4);
                        unit_cost = formatDecimal((parseFloat(item.row.base_unit_cost) * (unitToBaseQty(1, this))), 4);
                    }
                });
            }

            
            unit_cost  = formatDecimal(unit_cost - item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax !== null && pr_tax) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }

                    } else if (pr_tax.type == 2) {

                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;

                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_cost = item_tax_method == 0 ? formatDecimal(unit_cost - pr_tax_val, 4) : formatDecimal(unit_cost);
            unit_cost = formatDecimal(unit_cost + item_discount, 4);
            var show_item_cost = formatDecimal(item.row.real_unit_cost, 4);
            var sel_opt = '';
            if (item.options !== false && item.row.storage_type == 'packed') {
                var opt = $('<select id="poption_' + row_no + '" name="variant[]" class="form-control select roption" onchange="return getVariant_Detail(this.value, ' + item_id + ');" />');
            
                $.each(item.options, function () {
                    if (item.row.option == this.id) {
                        sel_opt = this.name;
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt);
                    } else {
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                    }
                });
            }
            else {
//                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt);
//                opt = opt.hide();
                sel_opt = 'N/A';
            }

            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            td_item_name = '<td>';
            td_item_name += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">';
            td_item_name += '<input name="hsn_code[]" type="hidden" class="rid" value="' + hsn_code + '">';
            td_item_name += '<input name="product[]" type="hidden" class="rcode" value="' + item_code + '">';
            td_item_name += '<input name="product_name[]" type="hidden" class="rname" value="' + item_name + '">';
            td_item_name += '<input name="product_option[]" type="hidden" class="roption" value="' + item_option + '">';
            td_item_name += '<input name="part_no[]" type="hidden" class="rpart_no" value="' + item_supplier_part_no + '">';
            td_item_name += '<input name="product_option_color[]" type="hidden" class="roption_color" value="' + item_option_color + '">';

            if (site.settings.purchase_image == 1) {
                var subdomain = new URL(window.location.href).hostname.split('.')[0];
                td_item_name += '<img src="assets/mdata/'+ subdomain + '/uploads/thumbs/' + item.row.image + '" alt="' + item.row.image + '" style="width:30px; height:30px;" /> ';
            }
            td_item_name += '<span class="sname" id="name_' + row_no + '">' + item_name;
            td_item_name += '<br/>(Barcode: ' + item_code + ')';
            if (item_option_color_name) {
                td_item_name += '<br/>(Color: ' + item_option_color_name + ')';
            }
            /* if (item.row.storage_type != 'loose') {
             td_item_name += (sel_opt != '') ? (' - ' + item_code + ' (' + sel_opt + ')') : ' (' + item_code + ')';
             } else {
             td_item_name += ' (' + item_code + ')';
             }*/
            td_item_name += ' <span class="label label-default">' + item_supplier_part_no + '</span></span>';
            // Edit code desibled
            // td_item_name += '<i class="pull-right fa fa-edit tip edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';

            tr_html = td_item_name;
            // tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';

            tr_html += '<td>' + sel_opt + '</td>';
            // Expiry column intentionally hidden from list view

//            console.log('==========item===========');
//            console.log(item);

            /***************************************************
             * site.settings.product_batch_required (0:Optional | 1:Required For Packed Products | 2:Required For All Products  )
             * site.settings.product_batch_setting  (0:Hide/Disabled Batches | 1:Select Batch From List | 2:Add Batch While Transaction)
             ***************************************************/
            // item.row.storage_type

            if (false && parseInt(site.settings.product_batch_setting) > 0) {
                var td_batch = '<td>';
                var batch_required = '';
                if (parseInt(site.settings.product_batch_required) == 2 || (parseInt(site.settings.product_batch_required) == 1 && item.row.storage_type == 'packed')) {
                    batch_required = ' required="required" ';
                }

                if (item.batchs) {
                    // if (parseInt(site.settings.product_batch_setting) == 1) {
                    //     td_batch += '<select class="form-control rbtach_no" name="batch_number[]" ' + batch_required + '  data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
                    //     $.each(item.batchs, function (index, value) {
                    //         td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '" ' + (value.id == item.row.batch ? 'Selected="Selected"' : '') + ' >' + value.batch_no + '</option>';
                    //     });
                    //     td_batch += '</select>';
                    // }
                    if (parseInt(site.settings.product_batch_setting) == 1) {
                        td_batch += '<select class="form-control rbtach_no" name="batch_number[]" ' + batch_required + '  data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
                        td_batch += '<option value="" disabled>Select a number</option>'; // Keep this as the default option
                        
                        var selectedBatchNumber = item.row.batch_number; // Get the pre-selected batch number
                        
                        $.each(item.batchs, function (index, value) {
                            var selected = (value.batch_no === selectedBatchNumber) ? 'selected' : ''; // Check if it's the pre-selected value
                            td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '" ' + selected + '>' + value.batch_no + '</option>';
                        });
                        td_batch += '</select>';
                    }

                    if (parseInt(site.settings.product_batch_setting) == 2) {
                        batchIds = [];
                        if(item.row.select_batch == 'change'){
                            item.row.batch_number = item.row.batch_number;
                        }else{
                            item.row.batch_number = item.row.batch_number;
                        }
                        td_batch += '<input list="batches_' + row_no + '" type="text" ' + batch_required + '  class="form-control rbtach_no" name="batch_number[]" id="batch_number_' + row_no + '" value="' + item.row.batch_number + '" ><datalist id="batches_' + row_no + '">';
                        $.each(item.batchs, function (index, value) {
                            td_batch += '<option data-batchid="' + value.id + '"  value="' + value.batch_no + '" >';
                            batchno = value.batch_no;
                            batchid = value.id;
                            batchIds[batchno] = batchid;
                        });
                        td_batch += '</datalist>';
                        variantpoitems[item_id].batchsData = batchIds;
                    }
                } else {
                    var item_batch_number = item.row && item.row.batch_number ? item.row.batch_number : '';
                    td_batch += '<input class="form-control rbtach_no" ' + batch_required + ' name="batch_number[]" type="text" value="' + item_batch_number + '" data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';

                }
                td_batch += '</td>';
            }
            


            // Cost column: show formatted cost and submit hidden values the server expects
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '"><input class="form-control text-rightimp rucost quickchange" name="unit_cost[]" id="unit_price_' + row_no + '"  type="number" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right scost" style="display:none" id="scost_' + row_no + '">' + formatMoney(show_item_cost) + '</span></td>';
            // tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '"><input class="rucost" name="unit_cost[]" type="hidden" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right scost" id="scost_' + row_no + '">' + formatMoney(show_item_cost) + '</span></td>'; // existing
            // Quantity column: show read-only quantity but submit hidden fields backend uses
            tr_html += '<td class="text-center">'
                + '<input name="quantity_balance[]" type="hidden" class="rbqty" value="' + item_bqty + '">'
                + '<input name="quantity[]" type="hidden" value="' + formatDecimal(item_qty) + '">'
                + '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">'
                + '<input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '">'
                + formatDecimal(item_qty) + ' ' + unit_lable
                + '</td>';
            if (po_edit) {
                total_received_qty += parseFloat(unit_qty_received);
                tr_html += '<td class="rec_con" style="' + purcahsestatus + '"><input name="ordered_quantity[]" type="hidden" class="oqty" value="' + item_oqty + '"><input class="form-control text-center received" name="received[]" type="number" min="1" max="' + item_oqty + '" step="1" value="' + (parseFloat(unit_qty_received) ? formatDecimal(unit_qty_received): 0) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="received_' + row_no + '" onClick="this.select();">' + unit_lable + '<input name="received_base_quantity[]" type="hidden" class="rrbase_quantity" value="' + qty_received + '"></td>';
            }
            var net_cost = show_item_cost * item_qty;
            total_netcost += net_cost;
            tr_html += '<td class="text-right">' + formatMoney(net_cost) + '</td>';
            if (false && site.settings.product_discount == 1) {
                // tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + (item_ds != '' ? '(' + item_ds + ')' : '') + '<br/>' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>'; // existing
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount quickchange text-right"  name="product_discount[]" type="text" id="discount_' + row_no + '" value="' + (item_ds ? item_ds : 0) + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }
            if (site.settings.tax1 == 1) {
                var tax_id = (pr_tax && pr_tax.id) ? pr_tax.id : 1;
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + tax_id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + '<br/>' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-right"><input type="hidden" name="tax_method[]" id="tax_method' + row_no + '" value="' + item_tax_method + '"/> <span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            // tr_html += '<td class="text-center"><i class="fa fa-times tip podel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#poTable");
            total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            total_discount += (item_discount * item_qty);
            an++;
            if (!belong)
                $('#row_' + row_no).addClass('warning');
        });

        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="' + col + '">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + (footerUnitLabel ? (' ' + footerUnitLabel) : '') + '</th>';
        if (po_edit) {
            tfoot += '<th class="rec_con" style="' + purcahsestatus + '">'+total_received_qty + (footerUnitLabel ? (' ' + footerUnitLabel) : '') + '</th>';
        }
        tfoot += '<th class="">' + formatMoney(total_netcost) + '</th>';
        if (false && site.settings.product_discount == 1) {
            tfoot += '<th class="text-right">' + formatMoney(0-total_discount) + '</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        // tfoot += '<th class="text-right">' + formatMoney(total) + '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        tfoot += '<th class="text-right">' + formatMoney(total) + '</th></tr>';
        $('#poTable tfoot').html(tfoot);

        // Order level discount calculations
        /* if (podiscount = localStorage.getItem('podiscount')) {
         var ds = podiscount;
         if (ds.indexOf("%") !== -1) {
         var pds = ds.split("%");
         if (!isNaN(pds[0])) {
         order_discount = formatDecimal(((total * parseFloat(pds[0])) / 100), 4);
         } else {
         order_discount = formatDecimal(ds);
         }
         } else {
         order_discount = formatDecimal(ds);
         }
         } */

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (potax2 = localStorage.getItem('potax2')) {
                $.each(tax_rates, function () {
                    if (this.id == potax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + (parseFloat(count) - 1) + ')');
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax1) {
            $('#ttax1').text(formatMoney(product_tax));
        }
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            var $st = $('#sticker');
            if ($st.length) {
                $("html, body").animate({scrollTop: $st.offset().top}, 500);
                $(window).scrollTop($(window).scrollTop() + 1);
            }
        }
        set_page_focus();
        // Keep hidden field in sync for server-side validation and enable buttons
        $('#total_items').val(ItemsCount);
        validateSubmitButton();
    }
    else {
        // No items present: reset totals/UI and hidden field so validation is consistent
        $("#poTable tbody").empty();
        $('#poTable tfoot').empty();
        $('#total').text(formatMoney(0));
        $('#titems').text('0 (0)');
        $('#ttax1').text && $('#ttax1').text(formatMoney(0));
        $('#ttax2').text && $('#ttax2').text(formatMoney(0));
        $('#tship').text(formatMoney(0));
        $('#gtotal').text(formatMoney(0));
        $('#total_items').val(0);
        validateSubmitButton();
    }
}

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_purchase_item(item) {
    // if (count == 1) {
    //     variantpoitems = {};
    //     if ($('#posupplier').val()) {
    //         $('#posupplier').select2("readonly", false);
    //     } else {
    //         bootbox.alert(lang.select_above);
    //         item = null;
    //         return;
    //     }
    // }
    if (item == null || !item.row)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    
    if (variantpoitems[item_id]) {
        variantpoitems[item_id].row.qty = (parseFloat(variantpoitems[item_id].row.qty) + parseFloat(item.row.qty));
        variantpoitems[item_id].row.base_quantity = (parseFloat(variantpoitems[item_id].row.base_quantity) +  parseFloat(item.row.base_quantity));
    } else {
        variantpoitems[item_id] = item;
    }

    variantpoitems[item_id].order = new Date().getTime();
    localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
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
$(document).on('change', '.quickchange', function () {
    var row = $(this).closest('tr');
    var row_id = row.attr('id');
    $('#row_id').val(row_id);
    item_id = row.attr('data-item-id');

    item = variantpoitems[item_id];
    var rowno = row_id.replace("row_", "");
    var real_unit_cost = item.row.real_unit_cost;

    var unit_price = $('#unit_price_' + rowno).val() ? parseFloat($('#unit_price_' + rowno).val()) : variantpoitems[item_id].row.real_unit_cost;
    var discountRaw = $('#discount_' + rowno).val();
    var discount = (discountRaw == null) ? '0' : String(discountRaw).trim();
    if (discount === '') discount = '0';
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
    var net_unit_price = unit_price - item_discount;
    $('#price_' + rowno).val(net_unit_price);

    // if (net_unit_price >= 1000) {
    //     tax_rate = 16;
    // } else {
    //     tax_rate = 15;
    // }

    variantpoitems[item_id].row.cost = net_unit_price;
    //variantpoitems[item_id].row.tax_method = 0;         
    // variantpoitems[item_id].row.tax_rate = tax_rate;  
    variantpoitems[item_id].row.real_unit_cost = unit_price;  
    variantpoitems[item_id].row.discount = discount;
    localStorage.setItem('variantpoitems', JSON.stringify(variantpoitems));
    loadItems();

});

 