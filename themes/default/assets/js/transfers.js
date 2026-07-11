page_mode = $('#page_mode').val();
permission_owner = $('#permission_owner').val();
permission_admin = $('#permission_admin').val();
ReadonlyData = 0;
if (permission_admin == 1)
    ReadonlyData = 1;
if (permission_owner == 1)
    ReadonlyData = 1;
$(document).ready(function () {

    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    tostatus = localStorage.getItem('tostatus');
    if (tostatus) {
        $('#tostatus').select2("val", tostatus);
        if (tostatus == 'completed') {
            $('#tostatus').select2("readonly", true);
            if (page_mode == 'edit') {
                //alert(permission_owner)
                $('#from_warehouse').select2("readonly", true);
                $('#to_warehouse').select2("readonly", true);
                $('#display_product').select2("readonly", true);
                $('#add_item').attr("readonly", true);
                $('.rexpiry').attr("readonly", true);
                $('.rquantity').attr("readonly", true);
                $('.tointer').hide();
            }
        }
    }
// Order level shipping and discoutn localStorage 
   
    $('#tostatus').change(function (e) {
        localStorage.setItem('tostatus', $(this).val());
        var Tostatus = $(this).val();
        if (page_mode == 'edit') {
            $('.rquantity').attr("readonly", true);
            if (Tostatus == 'partial') {
                if (ReadonlyData != 1) {
                    $('.rquantity').attr("readonly", false);
                } else {
                    $('.rquantity').attr("readonly", false);
                }
            } else if (Tostatus == 'partial_completed') {
                $('.rquantity').attr("readonly", false);
            } else if(Tostatus == 'completed' && tostatus == 'sent'){
                $('#edit_transfer').attr('disabled', false);
                $('tr.danger').removeClass('danger');                 
            }
            $('.rqty_zero').attr("readonly", true);
        
        }
    });
    
    if (page_mode == 'edit') {
        if (ReadonlyData != 1) {
            //alert(permission_owner)
            $('#from_warehouse').select2("readonly", true);
            $('#to_warehouse').select2("readonly", true);
            $('#display_product').select2("readonly", true);
            $('#add_item').attr("readonly", true);
            $('.rexpiry').attr("readonly", true);
            $('.rquantity').attr("readonly", true);
            $('.tointer').hide();
        }
    }
    var old_shipping;
    $('#toshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        /*if (!is_numeric($(this).val())) {
         $(this).val(old_shipping);
         bootbox.alert(lang.unexpected_value);
         return;
         } else {
         shipping = $(this).val() ? parseFloat($(this).val()) : '0';
         }
         localStorage.setItem('toshipping', shipping);*/
        if ($(this).val() != '') {
            if (!is_numeric($(this).val())) {
                $(this).val(old_shipping);
                bootbox.alert(lang.unexpected_value);
                return;
            } else {
                shipping = $(this).val() ? parseFloat($(this).val()) : '0';
            }
            localStorage.setItem('toshipping', shipping);
        } else {

            var shipping = 0;
            localStorage.removeItem('toshipping');
        }

        var gtotal;
        var display_product = $('#display_product').val();
        if (display_product == 'warehouse_product') {
            total1 = parseFloat($('#total_warProduct').val());
            gtotal = total1 + shipping;
            $('#total').text(formatMoney(total1));
        }

        if (display_product == 'search_product') {
            gtotal = total + shipping;
            $('#total').text(formatMoney(total));
        }

        //var gtotal = total  + shipping;
        $('#gtotal').text(formatMoney(gtotal));

        $('#tship').text(formatMoney(shipping));
        $('#tship_In').val(shipping);
    });
    if (toshipping = localStorage.getItem('toshipping')) {
        shipping = parseFloat(toshipping);
        $('#toshipping').val(shipping);
    }
//localStorage.clear();
// If there is any item in localStorage
    if (localStorage.getItem('toitems')) {
        // Edit transfer: inline script in view seeds toitems + toitems_transfer_id; loading here can run first and paint the PREVIOUS transfer's cart
        if ($('#pageAction').val() === 'edit_transfer') {
            var _tid = $('#transfer_server_id').val();
            if (_tid && localStorage.getItem('toitems_transfer_id') === String(_tid)) {
                loadItems();
            }
        } else {
            loadItems();
        }
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('toitems')) {
                    localStorage.removeItem('toitems');
                }
                if (localStorage.getItem('toshipping')) {
                    localStorage.removeItem('toshipping');
                }
                if (localStorage.getItem('toref')) {
                    localStorage.removeItem('toref');
                }
                if (localStorage.getItem('to_warehouse')) {
                    localStorage.removeItem('to_warehouse');
                }
                if (localStorage.getItem('tonote')) {
                    localStorage.removeItem('tonote');
                }
                if (localStorage.getItem('from_warehouse')) {
                    localStorage.removeItem('from_warehouse');
                }
                if (localStorage.getItem('todate')) {
                    localStorage.removeItem('todate');
                }
                if (localStorage.getItem('tostatus')) {
                    localStorage.removeItem('tostatus');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

// save and load the fields in and/or from localStorage

    $('#toref').change(function (e) {
        localStorage.setItem('toref', $(this).val());
    });
    if (toref = localStorage.getItem('toref')) {
        $('#toref').val(toref);
    }
    $('#to_warehouse').change(function (e) {
        localStorage.setItem('to_warehouse', $(this).val());
    });
    if (to_warehouse = localStorage.getItem('to_warehouse')) {
        $('#to_warehouse').select2("val", to_warehouse);
    }
    $('#from_warehouse').change(function (e) {
        localStorage.setItem('from_warehouse', $(this).val());
    });
    if (from_warehouse = localStorage.getItem('from_warehouse')) {
        $('#from_warehouse').select2("val", from_warehouse);
        if (count > 1) {
            //$('#from_warehouse').select2("readonly", true);
        }
    }

    //$(document).on('change', '#tonote', function (e) {
    $('#tonote').redactor('destroy');
    $('#tonote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('tonote', v);
        }
    });
    if (tonote = localStorage.getItem('tonote')) {
        $('#tonote').redactor('set', tonote);
    }

    $(document).on('change', '.rexpiry', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        toitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('toitems', JSON.stringify(toitems));
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


    /* ---------------------- 
     * Delete Row Method 
     * ---------------------- */

    $(document).on('click', '.todel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete toitems[item_id];
        row.remove();
        if (toitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('toitems', JSON.stringify(toitems));
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
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
        toitems[item_id].row.base_quantity = new_qty;
        if (toitems[item_id].row.unit != toitems[item_id].row.base_unit) {
            $.each(toitems[item_id].units, function () {
                if (this.id == toitems[item_id].row.unit) {
                    toitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        toitems[item_id].row.qty = new_qty;
        localStorage.setItem('toitems', JSON.stringify(toitems));
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
        toitems[item_id].row.cost = new_cost;
        localStorage.setItem('toitems', JSON.stringify(toitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
        $('#from_warehouse').select2('readonly', false);
        return false;
    });


});

/* -----------------------
 * Edit Row Modal Hanlder 
 ----------------------- */
$(document).on('click', '.edit', function () {
    $('#prModal').appendTo("body").modal('show');
    if ($('#poption').select2('val') != '') {
        $('#poption').select2('val', product_variant);
        product_variant = 0;
    }

    var row = $(this).closest('tr');
    var row_id = row.attr('id');
    item_id = row.attr('data-item-id');
    item = toitems[item_id];
    var qty = row.children().children('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            cost = row.children().children('.rucost').val();
            product_option_color = row.children().children('.roption_color').val(),
    $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
    if (site.settings.tax1) {
        var tax = item.tax_rate != 0 ? item.tax_rate.name + ' (' + item.tax_rate.rate + ')' : 'N/A';
        $('#ptax').text(tax);
        $('#old_tax').val($('#sproduct_tax_' + row_id).text());
    }

    var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
    if (item.options !== false) {
        var o = 1;
        opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
        $.each(item.options, function () {
            if (o == 1) {
                if (product_option == '') {
                    product_variant = this.id;
                } else {
                    product_variant = product_option;
                }
            }
            $("<option />", {value: this.id, text: this.name}).appendTo(opt);
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
            if (product_option_color == this.id) {
                $("<option />", {value: this.id, text: this.name, selected: true}).appendTo(opt_color);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(opt_color);
            }
            o++;
        });
    }
    $('#poptions-div').html(opt);
    $('#punits-div').html(uopt);
    //$('select.select').select2({minimumResultsForSearch: 7});
    $('#pquantity').val(qty);
    $('#old_qty').val(qty);
    $('#pprice').val(cost);
    //$('#poption').select2('val', item.row.option);
    $('#poption').val(item.row.option);
    $('#old_price').val(cost);
    $('#row_id').val(row_id);
    $('#item_id').val(item_id);
    $('#pserial').val(row.children().children('.rserial').val());
    $('#pproduct_tax').select2('val', row.children().children('.rproduct_tax').val());
    $('#pdiscount').val(row.children().children('.rdiscount').val());


});

/*$('#prModal').on('shown.bs.modal', function (e) {
 if($('#poption').select2('val') != '') {
 $('#poption').select2('val', product_variant);
 product_variant = 0;
 }
 });*/

$(document).on('change', '#punit', function () {
    var row = $('#' + $('#row_id').val());
    var item_id = row.attr('data-item-id');
    var item = toitems[item_id];
    if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
        $(this).val(old_row_qty);
        bootbox.alert(lang.unexpected_value);
        return;
    }
    var unit = $('#punit').val();
    if (unit != toitems[item_id].row.base_unit) {
        $.each(item.units, function () {
            if (this.id == unit) {
                $('#pprice').val(formatDecimal((parseFloat(item.row.base_unit_cost) * (unitToBaseQty(1, this))), 4)).change();
            }
        });
    } else {
        $('#pprice').val(formatDecimal(item.row.base_unit_cost)).change();
    }
});

/*7-09-2019*/
$(document).on('change', '#poption', function () {
    var qtyw1 = 0;
    var qtyw2 = 0;
    
    var option_id = $('#poption').val();    
    var Items = toitems[item_id];
    
    toitems[item_id].row.fup = 1;
    toitems[item_id].option_id           = option_id;
    toitems[item_id].row.option          = option_id;
    toitems[item_id].row.quantity        = qtyw1 = Items.options[option_id].quantity;
    toitems[item_id].row.stockwarehouse2 = qtyw2 = Items.options[option_id].quantity2;
    
    // var pprice = Items.options[option_id].cost;
    var pprice = Items.options[option_id].cost ? Items.options[option_id].cost : Items.row.cost;
    
    $('#pprice').val(pprice);
    $('#warh1qty').val(qtyw1);
    $('#warh2qty').val(qtyw2);
        
    /*
    var vartient = $('#poption').val();

    var from_warehouse = (localStorage.getItem('from_warehouse') == null) ? $('#from_warehouse').val() : localStorage.getItem('from_warehouse');
    var to_warehouse = (localStorage.getItem('to_warehouse') == null) ? $('#to_warehouse').val() : localStorage.getItem('to_warehouse');
    var base_path = window.location.pathname;
    var geturl_path = base_path.split("/");
    var url_pass = window.location.origin + '/' + geturl_path[1] + '/getQuantity';

    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'Get',
        data: {'from_warehouse': from_warehouse, 'to_warehouse': to_warehouse, 'vartient': vartient},
        url: url_pass,
        async: false,
        success: function (data) {
            if (data[0]) {
                qtyw1 = parseFloat(data[0]['quantity']);
            }

            if (data[1]) {
                qtyw2 = parseFloat(data[1]['quantity']);
            }
            $('#warh1qty').val(qtyw1);
            $('#warh2qty').val(qtyw2);
        }
    });*/
});


/* -----------------------
 * Edit Row Method 
 ----------------------- */
$(document).on('click', '#editItem', function () {
    var row = $('#' + $('#row_id').val());
    var item_id = row.attr('data-item-id');
    if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
        $(this).val(old_row_qty);
        bootbox.alert(lang.unexpected_value);
        return;
    }
    var unit = $('#punit').val();
    var base_quantity = parseFloat($('#pquantity').val());
    if (unit != toitems[item_id].row.base_unit) {
        $.each(toitems[item_id].units, function () {
            if (this.id == unit) {
                base_quantity = unitToBaseQty($('#pquantity').val(), this);
            }
        });
    }

    if ($('#warh1qty').val() == '' && $('#warh2qty').val() == '') {
        toitems[item_id].row.fup = 1,
                toitems[item_id].row.qty = parseFloat($('#pquantity').val()),
                toitems[item_id].row.base_quantity = parseFloat(base_quantity),
                toitems[item_id].row.unit = unit,
                toitems[item_id].row.real_unit_cost = parseFloat($('#pprice').val()),
                toitems[item_id].row.cost = parseFloat($('#pprice').val()),
                // toitems[item_id].row.tax_rate = new_pr_tax_rate,
                toitems[item_id].row.discount = $('#pdiscount').val(),
                toitems[item_id].row.option = $('#poption').val(),
                localStorage.setItem('toitems', JSON.stringify(toitems));
    } else {
        toitems[item_id].row.fup = 1,
                toitems[item_id].row.quantity = parseFloat($('#warh1qty').val()),
                toitems[item_id].row.getstock_2 = parseFloat($('#warh2qty').val()),
                toitems[item_id].row.qty = parseFloat($('#pquantity').val()),
                toitems[item_id].row.base_quantity = parseFloat(base_quantity),
                toitems[item_id].row.unit = unit,
                toitems[item_id].row.real_unit_cost = parseFloat($('#pprice').val()),
                toitems[item_id].row.cost = parseFloat($('#pprice').val()),
                // toitems[item_id].row.tax_rate = new_pr_tax_rate,
                toitems[item_id].row.discount = $('#pdiscount').val(),
                toitems[item_id].row.option = $('#poption').val(),
                localStorage.setItem('toitems', JSON.stringify(toitems));
    }

    $('#prModal').modal('hide');

    loadItems();
    return;
});

/* -----------------------
 * Misc Actions
 ----------------------- */

function loadItems() {
    var warehouse2 = (localStorage.getItem('to_warehouse') == null) ? $('#to_warehouse').val() : localStorage.getItem('to_warehouse');
    var pageAction = $('#pageAction').val();
    if (localStorage.getItem('toitems')) {
        total = 0;
        count = 1;
        an = 1;
        adminsData = 0;
        product_tax = 0;
        $("#toTable tbody").empty();
        
        $('#add_transfer, #edit_transfer').attr('disabled', false);
        toitems = JSON.parse(localStorage.getItem('toitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(toitems, function (o) {
            return [parseInt(o.order)];
        }) : toitems;
        
        var order_no = new Date().getTime();
        
        console.log('=======sortedItems=========');
        console.log(sortedItems);
        
        $.each(sortedItems, function () {
            
            var product_removes = product_remove; // Remove button condition
            var adminDatas = adminData; // Remove button condition
            var item = this;
            
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;
            var from_warehouse = localStorage.getItem('from_warehouse'), check = false;
            var product_id = item.row.id, item_type = item.row.type, item_cost = item.row.cost, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_oqty = item.row.ordered_quantity, item_expiry = item.row.expiry, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option,item_option_color = item.row.option_color,item_option_color_name = item.row.option_color_name, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");

            // var unit_cost = item.row.real_unit_cost;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            //From Warehouse Stock Quantity
            var quantity = item.row.quantity;
            // var getstock_2= item.row.getstock_2;
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;

            // Get Stock 2 Warehouse
            var getstock_2 = '0';
            getstock_2 = item.row.stockwarehouse2;
            // End Get Second Warehouse Stock
            var unit_cost = 0;
            // for variant popup functionality
            if (item.options && item.options.length > 0) {
                console.log(item.oprions);
                var optionId = item.row.option;
                var OptionData = item.options.find(option => option.id == optionId);
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
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
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
            $.each($.isArray(item.options) ? item.options : [], function () {
                if (this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var sel_opt_color = '';
            $.each($.isArray(item.options_color) ? item.options_color : [], function () {
                if (this.id == item_option_color) {
                    sel_opt_color = this.name;
                }
            });
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + ' each_tr" data-item-id="' + item_id + '"></tr>');
            
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">'+
                        '<input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '">'+
                        '<input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '">'+
                        '<input name="product_name[]" type="hidden" class="rname" value="' + item_name + '">'+
                        '<input type="hidden" id="PrItemId_' + row_no + '" value="' + item.item_id + '">'+
                        '<input name="product_option[]" type="hidden" class="roption" id="ItemOption_' + row_no + '" value="' + item_option + '">'+
                        '<input name="product_option_color[]" type="hidden" class="roption_color" value="' + item_option_color + '">'+
                        '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') +  (item_option_color_name ? ' (' + item_option_color_name + ')' : '') + '</span> <i class="pull-right fa fa-edit tip tointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i>'+
                    '</td>';
            
            tr_html += '<td class="text-right">' + formatDecimal(quantity) + '</td>';
            tr_html += '<td  class="text-right stock_2_' + row_no + '">' + formatDecimal(getstock_2) + '</td>';
            
            if (site.settings.product_expiry == 1) {
                tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
            }
            if (parseInt(site.settings.product_batch_setting) > 0) {
                var td_batch = '<td>';
                var batch_required = '';
                if (parseInt(site.settings.product_batch_required) == 2 || (parseInt(site.settings.product_batch_required) == 1 && item.row.storage_type == 'packed')) {
                    batch_required = ' required="required" ';
                }
                if (item.batchs) {
                    if (parseInt(site.settings.product_batch_setting) == 1 || parseInt(site.settings.product_batch_setting) == 2) {
                        // td_batch += '<select class="form-control rbtach_no" name="batch_number[]" ' + batch_required + '  data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
                        // td_batch += '<option value="" selected disabled>Select a number</option>';
                        // $.each(item.batchs, function (index, value) {
                        //     td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>';
                        // });
                        // td_batch += '</select>';
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
                    //     if(item.row.select_batch == 'change'){
                    //         item.row.batch_number = item.row.batch_number;
                    //     }else{
                    //         item.row.batch_number = '';
                    //     }
                    //     td_batch += '<input list="batches_' + row_no + '" type="text" ' + batch_required + '  class="form-control rbtach_no" name="batch_number[]" id="batch_number_' + row_no + '" value="' + item.row.batch_number + '" ><datalist id="batches_' + row_no + '">';
                    //     $.each(item.batchs, function (index, value) {
                    //         td_batch += '<option data-batchid="' + value.id + '"  value="' + value.batch_no + '" >';
                    //         batchno = value.batch_no;
                    //         batchid = value.id;
                    //         batchIds[batchno] = batchid;
                    //     });
                    //     td_batch += '</datalist>';
                    //     toitems[item_id].batchsData = batchIds;
                    // }
                } else {
                    var item_batch_number = item.row && item.row.batch_number ? item.row.batch_number : '';
                    td_batch += '<input class="form-control rbtach_no" ' + batch_required + ' name="batch_number[]" type="text" value="' + item_batch_number + '" data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';

                }
                td_batch += '</td>';
            }
            tr_html += td_batch;
            // tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + formatDecimal(item_cost) + '"><input class="rucost" name="unit_cost[]" type="hidden" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right scost" id="scost_' + row_no + '">' + formatMoney(item_cost) + '</span></td>';
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '"><input class="form-control text-rightimp rucost quickchange" name="unit_cost[]" id="unit_price_' + row_no + '"  type="number" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right scost" style="display:none" id="scost_' + row_no + '">' + formatMoney(show_item_cost) + '</span></td>';

//            if (item.row.request_quantity) {
//                tr_html += '<td class="text-right">' + formatDecimal(item.row.request_quantity) + '</td>';
//                tr_html += '<td class="text-right">' + formatDecimal(item.row.sent_quantity) + '</td>';
//                tr_html += '<td class="text-right">' + formatDecimal(item_bqty) + '</td>';
//            }
            
            var rqty = '';
            if (item_qty == 0) { rqty = 'rqty_zero'; }
            
//            '<input type="hidden" name="request_quantity[]"  value="' + formatDecimal(item.row.request_quantity) + '" />'+
//            '<input type="hidden" name="sent_quantity[]" value="' + formatDecimal(item.row.sent_quantity) + '"/>'+
//            '<input type="hidden" name="quantity_balance[]" class="rbqty" value="' + formatDecimal(item_bqty, 4) + '">'+
//            '<input type="hidden" name="quantity_received[]" class="coqty" value="' + formatDecimal(item.row.quantity_received, 4) + '">'+
            
            tr_html += '<td><input type="number" name="quantity[]" class="form-control text-center rquantity ' + rqty + '" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" min="1" steps="1" max="'+item.row.request_quantity+'" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">'+
                    '<input type="hidden" name="ordered_quantity[]" class="roqty" value="' + formatDecimal(item_oqty, 4) + '">'+
                    '<input type="hidden" name="product_unit[]" class="runit" value="' + product_unit + '">'+
                    '<input type="hidden" name="product_base_quantity[]" class="rbase_quantity" value="' + base_quantity + '">'+
                '</td>';

            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right">'+
                            '<input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax_rate + '">'+
                            '<input class="form-control input-sm text-right rproduct_tax_id" name="product_tax_id[]" type="hidden" id="product_tax_id_' + row_no + '" value="' + pr_tax.id + '">'+
                            '<span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span>'+
                        '</td>';
            }

            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_cost) - item_discount + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            // Remove button condition
            if (product_removes == true ||adminData) {
                tr_html += '<td class="text-center"><i class="fa fa-times tip todel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            }
            newTr.html(tr_html);
            newTr.prependTo("#toTable");
            total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            product_remove = product_removes;
            adminData = adminDatas;
            
            an++;
            
            var checkStocks = false;
            if( $('#pageAction').val() == 'add_transfer' ) {
                checkStocks = true;
            }
            else if( $('#tostatus').val() !== "completed" && $('#tostatus').val() !== "sent") {
                checkStocks = true;
            }
            if (item_qty <= 0) {
                $('#row_' + row_no).addClass('danger');
                 $('#add_transfer, #edit_transfer').attr('disabled', true); 
            }
            
            if( checkStocks !== false ) {
                if (item.options !== false && item.options[item.option_id]) {
                    $.each(item.options, function () {
                        if (this.id == item_option && base_quantity > this.quantity) {                         
                            $('#row_' + row_no).addClass('danger a');
                            $('#add_transfer, #edit_transfer').attr('disabled', true);
                        }
                    });
                } else if (base_quantity > item_aqty) {

                    $('#row_' + row_no).addClass('danger b');
                    $('#add_transfer, #edit_transfer').attr('disabled', true); 
                }
            }

        });

        var col = 4;
        if (site.settings.product_expiry == 1) {
            col++;
        }
        if (parseInt(site.settings.product_batch_setting) > 0) {
            col++;
       }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="' + col + '">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        tfoot += '<th class="text-right">' + formatMoney(total) + '</th>';
        if (product_remove == 1 || adminData) {
            // tfoot += '<th class="text-center">No Trash</th>'; 
            tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>';
        } 
        tfoot +='</tr>';
        $('#toTable tfoot').html(tfoot);

        // Totals calculations after item addition
        var shipping = ($('#toshipping').val() != '') ? parseFloat($('#toshipping').val()) : 0;
        var gtotal = total + shipping;
        $('#tship').text(formatMoney(shipping));
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + (parseFloat(count) - 1) + ')');
        if (site.settings.tax1) {
            $('#ttax1').text(formatMoney(product_tax));
        }
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
        if (tostatus == 'completed') {
            $('#tostatus').select2("readonly", true);
            if (page_mode == 'edit') {               
                $('.rexpiry').attr("readonly", true);
                $('.rquantity').attr("readonly", true);
                $('.tointer').hide();
            }
        }
        if (page_mode == 'edit') {
            $('.rquantity').attr("readonly", true);
            if (ReadonlyData != 1) {                
                $('.rexpiry').attr("readonly", true);
                $('.rquantity').attr("readonly", true);
                $('.tointer').hide();
            }
        }
       
        var ttstatus = $('#tostatus').val();
        if (ttstatus == 'partial') {
            if (page_mode == 'edit') {                
                if (ReadonlyData == 1) {
                    $('.rquantity').attr("readonly", false);
                }
            }
        }
         
         
        //$('.rqty_zero').attr("readonly", true);
    }
}
$(document).on('change', '.rbtach_no', function () {
    var item_id = $(this).closest('tr').attr('data-item-id');
    var batch = $(this).val();
    var batch_id = $(this).find(':selected').attr('data-batchid');
    batch_id = batch_id ? batch_id : (toitems[item_id].batchsData[batch] ? toitems[item_id].batchsData[batch] : false);
    var select_batch = 'change'
    toitems[item_id].row.batch_number = batch;
    toitems[item_id].row.select_batch = select_batch;
    if (batch_id) {
        toitems[item_id].row.batch = batch_id;
        var batchvalue = toitems[item_id].batchs[batch_id];
        toitems[item_id].row.cost = batchvalue['cost'];
        toitems[item_id].row.real_unit_cost = batchvalue['cost'];
        toitems[item_id].row.base_unit_cost = batchvalue['cost'];
        toitems[item_id].row.expiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
    }
    localStorage.setItem('toitems', JSON.stringify(toitems));
    loadItems();
});

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_transfer_item(item) {

    if (count == 1) {
        toitems = {};
        if ($('#from_warehouse').val()) {
            //  $('#from_warehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    
    if (toitems[item_id]) {
        toitems[item_id].row.qty = parseFloat(toitems[item_id].row.qty) + 1;
    } else {
        toitems[item_id] = item;
    }
    toitems[item_id].order = new Date().getTime();
    localStorage.setItem('toitems', JSON.stringify(toitems));
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