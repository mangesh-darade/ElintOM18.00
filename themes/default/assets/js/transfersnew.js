page_mode = $('#page_mode').val();
permission_owner = $('#permission_owner').val();
permission_admin = $('#permission_admin').val();
sent_edit_transfer = $('#sent_edit_transfer').val();
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
// Order level shipping and discoutn localStorage 
    $('#tostatus').change(function (e) {
        localStorage.setItem('tostatus', $(this).val());
        var Tostatus = $(this).val();

        if (Tostatus == 'request') {
            $('.request_quantity').attr("readonly", false);
            $('.main_quantity').attr("readonly", true);
        } else {
            $('.request_quantity').attr("readonly", true);
            $('.main_quantity').attr("readonly", false);
        }
        if (sent_edit_transfer == 1 && Tostatus == 'completed') {
            $('.rquantity').attr("readonly", true);
            $('#add_item').attr("readonly", true);
        }
        if (Tostatus == 'partial_completed') {
            changeStatus();
        }
        if (Tostatus == 'sent_balance') {
            changeStatus();
        }
        if (page_mode == 'edit') {
            //	$('.rquantity').attr("readonly", true);
            /*if(Tostatus == 'partial') {
             if(ReadonlyData!=1){
             $('.rquantity').attr("readonly", false);
             }else{
             $('.rquantity').attr("readonly", false);
             }
             }*/
            $('.rqty_zero').attr("readonly", true);
        }

        if (site.settings.overselling != 1) {
            onChangeStatus();
        }
        
    });



    function onChangeStatus() {

        var Tostatus = $('#tostatus').val();

        $.each(sortedItems, function () {
            var item = this;

            var base_quantity = item.row.base_quantity;
            var quantity = item.row.quantity;
            var item_oqty = item.row.ordered_quantity;
            var item_bqty = item.row.quantity_balance;
            var item_aqty = item.row.quantity;
            var item_option = item.row.option;

            var itemId = item.item_id;
            var row_no = $('#row_no_' + itemId).val();

            if ((item.status != 'sent' && item.status != 'completed')) {
                if (item.options !== false) {
                    $.each(item.options, function () {
                        if (this.id == item_option && parseFloat(base_quantity) > parseFloat(this.quantity)) {

                            $('#row_' + row_no).addClass('danger a');

                            if (Tostatus == 'request') {
                                $('#edit_transfer').attr('disabled', false);
                            } else {
                                $('#edit_transfer').attr('disabled', true);
                            }

                            if (Tostatus == 'completed') {
                                var aaqty = parseFloat(quantity) + parseFloat(item_oqty);

                                if (parseFloat(base_quantity) > parseFloat(aaqty)) {
                                    $('#edit_transfer').attr('disabled', true);
                                }
                            }
                        }
                    });
                } else if (parseFloat(base_quantity) > parseFloat(item_aqty)) {
                    $('#row_' + row_no).addClass('danger');
                    if (Tostatus == 'request') {
                        $('#edit_transfer').attr('disabled', false);
                    } else {
                        $('#edit_transfer').attr('disabled', true);
                    }
                    //$('#add_transfer').attr('disabled', true);
                    if (Tostatus == 'completed') {
                        var aaqty = parseFloat(item_aqty) + parseFloat(item_bqty);

                        if (parseFloat(base_quantity) > parseFloat(aaqty)) {
                            $('#edit_transfer').attr('disabled', true);
                        }
                    }
                }
            }

        });
    }


    function changeStatus() {
        var Tostatus = localStorage.getItem('tostatus');
        if (Tostatus == 'partial_completed') {
            $.each(toitems, function (k, v) {
                var new_qty = toitems[k].row.sent_quantity;
                toitems[k].row.base_quantity = new_qty;
                /*if(toitems[k].row.unit != toitems[k].row.base_unit) {
                 $.each(toitems[k].units, function(){
                 if (this.id == toitems[k].row.unit) {
                 toitems[k].row.base_quantity = unitToBaseQty(new_qty, this);
                 }
                 });
                 }*/
                toitems[k].row.qty = new_qty;
            });
        }
        if (Tostatus == 'sent_balance') {
            $.each(toitems, function (k, v) {
                var new_qty = parseFloat(toitems[k].row.request_quantity) - parseFloat(toitems[k].row.sent_quantity);
                toitems[k].row.base_quantity = new_qty;
                toitems[k].row.qty = new_qty;
            });
        }
        //console.log(JSON.stringify(toitems));
        localStorage.setItem('toitems', JSON.stringify(toitems));
        loadItems();
    }
    
    if (tostatus = localStorage.getItem('tostatus')) {
        $('#tostatus').select2("val", tostatus);

        if (tostatus == 'completed') {
            $('#tostatus').select2("readonly", true);
            if (page_mode == 'edit') {
                //alert(permission_owner)
                $('#from_warehouse').select2("readonly", true);
                $('#to_warehouse').select2("readonly", true);
                $('#display_product').select2("readonly", true);
                //$('#add_item').attr("readonly", true);
                $('.rexpiry').attr("readonly", true);
                //$('.rquantity').attr("readonly", true);
                $('.tointer').hide();
            }
        }
    }
    if (page_mode == 'edit') {
        $('#from_warehouse').select2("readonly", true);
        $('#to_warehouse').select2("readonly", true);
        if (ReadonlyData != 1) {
            //alert(permission_owner)
            $('#from_warehouse').select2("readonly", true);
            $('#to_warehouse').select2("readonly", true);
            $('#display_product').select2("readonly", true);
            //$('#add_item').attr("readonly", true);
            $('.rexpiry').attr("readonly", true);
            //$('.rquantity').attr("readonly", true);
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
        loadItems();
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
         
        var new_qty = parseFloat($(this).val());
        var item_id = row.attr('data-item-id');

        toitems[item_id].row.base_quantity = new_qty;

        if (toitems[item_id].row.unit != toitems[item_id].row.base_unit) {
            $.each(toitems[item_id].units, function () {
                if (this.id == toitems[item_id].row.unit) {
                    toitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }

        toitems[item_id].row.qty = new_qty;
        var Tostatus = $('#tostatus').val();
        if (Tostatus == 'request') {
            toitems[item_id].row.request_quantity = new_qty;
        }

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
    //item_id = row.attr('data-item-id');
    item_id = $(this).attr('data-item');
    alert('row_id: ' + row_id);
    alert('item_id: ' + item_id);

    item = toitems[item_id];
    var qty = row.children().children('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            cost = row.children().children('.rucost').val();

    $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');

    if (site.settings.tax1) {
        var tax = item.tax_rate != 0 ? item.tax_rate.name + ' (' + item.tax_rate.rate + ')' : 'N/A';
        $('#ptax').text(tax);
        $('#old_tax').val($('#sproduct_tax_' + row_id).text());
    }

    if (parseInt(site.settings.product_batch_setting) > 0) {
        var edtbatch = '<p style="margin: 12px 0 0 0;"><input class="form-control" name="pbatch_number" type="text" ></p>';

        if (item.batchs) {
            var b = 1;
            edtbatch = $('<select id="pbatch_number" name="pbatch_number" class="form-control" />');
            $.each(item.batchs, function () {
                $('<option data-batchid="' + this.id + '" data-cost="' + this.cost + '" value="' + this.batch_no + '" >' + this.batch_no + '</option>').appendTo(edtbatch);
                b++;
            });
        }

        $('#batchNo_div').html(edtbatch);
        $('#pbatch_number').select2('val', item.row.batch_number);
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
    $('#storage_type').val(item.row.storage_type);

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
    var vartient = $('#poption').val();

    var from_warehouse = (localStorage.getItem('from_warehouse') == null) ? $('#from_warehouse').val() : localStorage.getItem('from_warehouse');
    var to_warehouse = (localStorage.getItem('to_warehouse') == null) ? $('#to_warehouse').val() : localStorage.getItem('to_warehouse');
    var base_path = window.location.pathname;
    var geturl_path = base_path.split("/");
    var url_pass = window.location.origin + '/' + geturl_path[1] + '/getQuantity';

    onVariantChanged();

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
    });

});

$(document).on('change', '.rbtach_no', function () {

    var item_id = $(this).closest('tr').attr('data-item-id');
    var batch = $(this).val();
    var batch_id = $(this).find(':selected').attr('data-batchid');

    var item = toitems[item_id];

    batch_id = batch_id ? batch_id : (item.batchsData[batch] ? item.batchsData[batch] : false)

    if (batch_id) {
        var itemId = item.item_id + item.row.option + batch_id;

        toitems[itemId] = item;
        delete toitems[item_id];
    } else {
        itemId = item_id;
        toitems[itemId].row.batch = false;
        toitems[itemId].batch_id = false;
        toitems[itemId].batch_quantity = false;
        toitems[itemId].id = itemId;
    }

    toitems[itemId].row.batch_number = batch;

    if (batch_id) {
        toitems[itemId].row.batch = batch_id;
        toitems[itemId].batch_id = batch_id;
        toitems[itemId].id = itemId;

        var batchvalue = item.batchs[batch_id];

        toitems[itemId].row.batch_quantity = batchvalue['quantity'];
        toitems[itemId].row.cost = batchvalue['cost'];
        toitems[itemId].row.real_unit_cost = batchvalue['cost'];
        toitems[itemId].row.base_unit_cost = batchvalue['cost'];
        toitems[itemId].row.expiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
    }

    localStorage.setItem('toitems', JSON.stringify(toitems));
    loadItems();
});



/* -----------------------
 * Edit Row Method 
 ----------------------- */
$(document).on('click', '#editItem', function () {

    //var row = $('#' + $('#row_id').val());
    // var itemId = row.attr('data-item-id');
    var itemId = $('#item_id').val();

    if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
        $(this).val(old_row_qty);
        bootbox.alert(lang.unexpected_value);
        return;
    }

    var Item = toitems[itemId];
    delete(toitems[itemId]);

    var item_id = Item.item_id;

    if (Item.options !== false) {
        var option_id = $('#poption').val();
        item_id = item_id + '' + option_id;
    }

    if (parseInt(site.settings.product_batch_setting) > 0) {
        var batch_number = $('#pbatch_number').val();
        var batch_id = $('#pbatch_number').find(':selected').attr('data-batchid');
        if (batch_id) {
            item_id = item_id + '' + batch_id;
        } else {
            item_id = item_id + '' + batch_number;
        }
    }


    toitems[item_id] = Item;
    toitems[item_id].id = item_id;

    var unit = $('#punit').val();
    var base_quantity = parseFloat($('#pquantity').val());
    if (unit != Item.row.base_unit) {
        $.each(Item.units, function () {
            if (this.id == unit) {
                base_quantity = unitToBaseQty($('#pquantity').val(), this);
            }
        });
    }

    if (parseInt(site.settings.product_batch_setting) > 0) {

        toitems[item_id].row.batch_number = batch_number ? batch_number : '';
        toitems[item_id].batch_id = batch_id ? batch_id : false;
        toitems[item_id].row.batch = batch_id ? batch_id : false;

        if (batch_id) {
            toitems[item_id].batchs = Item.option_batches[option_id];
            toitems[item_id].row.batch_quantity = Item.option_batches[option_id][batch_id].quantity;
        }
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
                toitems[item_id].row.option = $('#poption').val();
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
                toitems[item_id].row.option = $('#poption').val();
    }

    localStorage.setItem('toitems', JSON.stringify(toitems));
    $('#prModal').modal('hide');

    loadItems();
    return;
});

$(document).on('change', '#pbatch_number', function () {

    onBatchChanged();

});

function onChangeStatus() {

    var Tostatus = $('#tostatus').val();

    $.each(sortedItems, function () {
        var item = this;

        var base_quantity = item.row.base_quantity;
        var quantity = item.row.quantity;
        var item_oqty = item.row.ordered_quantity;
        var item_bqty = item.row.quantity_balance;
        var item_aqty = item.row.quantity;
        var item_option = item.row.option;

        var itemId = item.item_id;
        var row_no = $('#row_no_' + itemId).val();

        if ((item.status != 'sent' && item.status != 'completed')) {
            if (item.options !== false) {
                $.each(item.options, function () {
                    if (this.id == item_option && parseFloat(base_quantity) > parseFloat(this.quantity)) {

                        $('#row_' + row_no).addClass('danger a');

                        if (Tostatus == 'request') {
                            $('#edit_transfer').attr('disabled', false);
                        } else {
                            $('#edit_transfer').attr('disabled', true);
                        }

                        if (Tostatus == 'completed') {
                            var aaqty = parseFloat(quantity) + parseFloat(item_oqty);

                            if (parseFloat(base_quantity) > parseFloat(aaqty)) {
                                $('#edit_transfer').attr('disabled', true);
                            }
                        }
                    }
                });
            } else if (parseFloat(base_quantity) > parseFloat(item_aqty)) {
                $('#row_' + row_no).addClass('danger');
                if (Tostatus == 'request') {
                    $('#edit_transfer').attr('disabled', false);
                } else {
                    $('#edit_transfer').attr('disabled', true);
                }
                //$('#add_transfer').attr('disabled', true);
                if (Tostatus == 'completed') {
                    var aaqty = parseFloat(item_aqty) + parseFloat(item_bqty);

                    if (parseFloat(base_quantity) > parseFloat(aaqty)) {
                        $('#edit_transfer').attr('disabled', true);
                    }
                }
            }
        }

    });
}


function changeStatus() {
    var Tostatus = localStorage.getItem('tostatus');
    if (Tostatus == 'partial_completed') {
        $.each(toitems, function (k, v) {
            var new_qty = toitems[k].row.sent_quantity;
            toitems[k].row.base_quantity = new_qty;
            /*if(toitems[k].row.unit != toitems[k].row.base_unit) {
             $.each(toitems[k].units, function(){
             if (this.id == toitems[k].row.unit) {
             toitems[k].row.base_quantity = unitToBaseQty(new_qty, this);
             }
             });
             }*/
            toitems[k].row.qty = new_qty;
        });
    }
    if (Tostatus == 'sent_balance') {
        $.each(toitems, function (k, v) {
            var new_qty = parseFloat(toitems[k].row.request_quantity) - parseFloat(toitems[k].row.sent_quantity);
            toitems[k].row.base_quantity = new_qty;
            toitems[k].row.qty = new_qty;
        });
    }
    //console.log(JSON.stringify(toitems));
    localStorage.setItem('toitems', JSON.stringify(toitems));
    loadItems();
}


function onVariantChanged() {
    var item_id = $('#item_id').val();
    var poption = $('#poption').val();
    var setBatchCost = false;
    var rowoption = toitems[item_id].options[poption];

    //Apply option cost
    if (rowoption.cost) {

        $('#pprice').val(rowoption.cost);
    }

    if (parseInt(site.settings.product_batch_setting) > 0) {

        var selected = '';
        var b = 0;
        var first_batch_number = '';

        var optionBatched = false;

        var old_batch_id = toitems[item_id].row.batch;
        var batch_number = toitems[item_id].row.batch_number;

        if (toitems[item_id].option_batches !== false && toitems[item_id].option_batches[poption]) {

            optionBatched = toitems[item_id].option_batches[poption];

            var btc = '<select id="pbatch_number" name="pbatch_number" class="form-control" >';

            $.each(optionBatched, function () {
                b++;
                batch_number = (old_batch_id == this.id) ? this.batch_no : '';
                btc += '<option data-batchid="' + this.id + '" value="' + this.batch_no + '" ' + selected + ' >' + this.batch_no + '</option>';
                if (b == 1) {
                    first_batch_number = this.batch_no;
                }
            });
            btc += '</select>';
            setBatchCost = true;
        } else {

            var btc = '<input type="text" class="form-control" name="pbatch_number" value="' + batch_number + '">';
        }

        $('#batchNo_div').html(btc);
        $('#pbatch_number').select2('val', batch_number ? batch_number : first_batch_number);

    }

    if (setBatchCost) {
        onBatchChanged();
    }
}

function onBatchChanged() {

    var item_id = $('#item_id').val();
    var option_id = 0;
    if (toitems[item_id].options !== false) {
        var option_id = $('#poption').val();
    }
    var batch_number = $('#pbatch_number').val();

    var batch_id = $('#pbatch_number').find(':selected').attr('data-batchid');

    //batch_id = batch_id ? batch_id : (toitems[item_id].batchsData[batch] ? toitems[item_id].batchsData[batch] : false)

    if (batch_id && toitems[item_id].option_batches[option_id]) {

        var batch = toitems[item_id].option_batches[option_id][batch_id];

        if (batch.cost) {
            $('#pprice').val(batch.cost);
        }
    }
}

function getVariant_Detail(VarientId, ItemId) {

    toitems[ItemId].row.option = VarientId;

    if (toitems[ItemId].option_batches[VarientId] && toitems[ItemId].row.storage_type == 'packed') {
        toitems[ItemId].batchs = toitems[ItemId].option_batches[VarientId];
    }

    localStorage.setItem('toitems', JSON.stringify(toitems));

    loadItems();
}


/* -----------------------
 * Misc Actions
 ----------------------- */

function loadItems() {

    var warehouse2 = (localStorage.getItem('to_warehouse') == null) ? $('#to_warehouse').val() : localStorage.getItem('to_warehouse');
    var Tostatus = $('#tostatus').val();
    if (localStorage.getItem('toitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        $("#toTable tbody").empty();
        $('#add_transfer, #edit_transfer').attr('disabled', false);
        toitems = JSON.parse(localStorage.getItem('toitems'));
        //sortedItems = (site.settings.item_addition == 1) ? _.sortBy(toitems, function(o){return [parseInt(o.order)];}) :   toitems;
        sortedItems = _.sortBy(toitems, function (o) {
            return [parseInt(o.order)];
        });
        var order_no = new Date().getTime();
        var isRequestPrd = false;

        console.log('-----------sortedItems-----------');
        console.log(sortedItems);

        $.each(sortedItems, function () {
            var item = this;

            //var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            var item_id = item.item_id;
            if (item.option_id != '') {
                item_id = item.item_id + item.option_id;
            }
            if (item.row.batch_number && item.row.batch != false) {
                item_id = item_id + item.row.batch;
            }
            item.order = item.order ? item.order : order_no++;
            var from_warehouse = localStorage.getItem('from_warehouse'), check = false;
            var product_id = item.row.id, item_type = item.row.type, item_cost = item.row.cost, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_oqty = item.row.ordered_quantity, item_expiry = item.row.expiry, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");

            var unit_cost = item.row.real_unit_cost;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var quantity = item.row.quantity;
            // var getstock_2= item.row.getstock_2;
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;

            // Get Stock 2 Warehouse
            var getstock_2 = '0';
            getstock_2 = item.row.stockwarehouse2;
             
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
            var sel_opt = '';
            $.each(item.options, function () {
                if (this.id == item_option) {
                    sel_opt = this.name;
                }
            });

            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + ' each_tr" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td>';
            tr_html += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">' +
                    '<input name="row_no[]" id="row_no_' + item_id + '" type="hidden" class="rrow_no" value="' + row_no + '">' +
                    '<input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '">' +
                    '<input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '">' +
                    '<input name="product_name[]" type="hidden" class="rname" value="' + item_name + '">' +
                    '<input type="hidden" id="PrItemId_' + row_no + '" value="' + item.item_id + '">' +
                    '<input name="product_option[]" type="hidden" class="roption" id="ItemOption_' + row_no + '" value="' + item_option + '">' +
                    '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + '</span> <i class="pull-right fa fa-edit tip tointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';


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
                    
                    td_batch += '<select class="form-control rbtach_no" name="batch_number[]" ' + batch_required + '  data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
                    $.each(item.batchs, function (index, value) {
                        
                        if(item.transfer_status == 'sent' || item.transfer_status == 'sent_balance' || item.transfer_status == 'partial' || item.transfer_status == 'sent_partial' || item.transfer_status == 'completed'){
                            if(item.batch_number == value.batch_no) {
                                td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '" ' + (value.id == item.row.batch ? 'Selected="Selected"' : '') + ' >' + value.batch_no + '</option>';
                            }
                        } else {
                            td_batch += '<option data-batchid="' + value.id + '" value="' + value.batch_no + '" ' + (value.id == item.row.batch ? 'Selected="Selected"' : '') + ' >' + value.batch_no + '</option>';
                        }
                    });
                    td_batch += '</select>';

                } else {
                    var item_batch_number = (item.batch_number) ? item.batch_number : '';
                    td_batch += '<input class="form-control " ' + batch_required + ' name="batch_number[]" type="text" value="' + item_batch_number + '" placeholder="Batch Number" data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';

                }
                td_batch += '</td>';

                tr_html += td_batch;
            }

            tr_html += '<td class="text-right">' + formatDecimal(quantity) + '</td>';
            tr_html += '<td  class="text-right stock_2_' + row_no + '">' + formatDecimal(getstock_2) + '</td>';

            if (parseInt(site.settings.product_expiry) == 1) {
                tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
            }

            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + formatDecimal(item_cost) + '"><input class="rucost" name="unit_cost[]" type="hidden" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right scost" id="scost_' + row_no + '">' + formatMoney(item_cost) + '</span></td>';

            if (item.row.request_quantity != null || item.row.request_quantity != 0) {
                isRequestPrd = true;
            }


            var requestTextQty = '';
            if (status == 'request' || status == 'partial' || status == 'partial_completed' || status == 'sent_balance') {
                if (item.row.request_quantity == item.row.sent_quantity) {
                    requestTextQty = '<input type="hidden" name="request_quantity[]"  value="' + formatDecimal(item.row.request_quantity) + '" />';
                    $('.extracloumn').hide();
                } else {
                    var OrgSentQty = '';
                    if (status == 'sent_balance') {
                        OrgSentQty = '(' + formatDecimal(item.row.PrQtyBallance) + ')';
                    }
                    tr_html += '<td class="text-right"><input type="text" name="request_quantity[]" ' + ((Tostatus != "request") ? "readonly" : "") + ' class="form-control rquantity request_quantity" value="' + formatDecimal(item.row.request_quantity) + '" /></td>';
                    tr_html += '<td class="text-right"> ' + formatDecimal(item.row.sent_quantity) + OrgSentQty + '</td>';
                }
            } else {
                if (status == 'sent') {
                    if (item.row.request_quantity != 0) {
                        if (item.row.request_quantity != null) {
                            tr_html += '<td class="text-right"><input type="text" name="request_quantity[]" ' + ((Tostatus != "request") ? "readonly" : "") + ' class="form-control rquantity request_quantity" value="' + formatDecimal(item.row.request_quantity) + '" /></td>';
                            tr_html += '<td class="text-right"> ' + formatDecimal(item.row.sent_quantity) + '</td>';
                            $('.extracloumn').show();
                        } else {
                            $('.extracloumn').hide();
                            requestTextQty = '<input type="hidden" name="request_quantity[]"  value="' + formatDecimal(item.row.request_quantity) + '" />';
                        }

                    } else {
                        $('.extracloumn').hide();
                        requestTextQty = '<input type="hidden" name="request_quantity[]"  value="' + formatDecimal(item.row.request_quantity) + '" />';
                    }

                } else {
                    requestTextQty = '<input type="hidden" name="request_quantity[]"  value="' + formatDecimal(item.row.request_quantity) + '" />';
                }
            }

            // tr_html += td_request_quantity + '' + td_request_quantity;

            // End  2/04/19
            var rqty = '';
            //console.log(item.row.request_quantity+' '+item.row.sent_quantity);
            if (item_qty == 0)
                rqty = 'rqty_zero';
            tr_html += '<td> ' + requestTextQty + '<input type="hidden" name="sent_quantity[]" value="' + formatDecimal(item.row.sent_quantity) + '"/>' +
                    '<input name="quantity_balance[]" type="hidden" class="rbqty" value="' + formatDecimal(item_bqty, 4) + '">' +
                    '<input name="ordered_quantity[]" type="hidden" class="roqty" value="' + formatDecimal(item_oqty, 4) + '">' +
                    '<input ' + ((Tostatus == "request") ? "readonly" : "") + ' class="form-control text-center rquantity main_quantity ' + rqty + '" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">' +
                    '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">' +
                    '<input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';

            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }

            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_cost) - item_discount + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip todel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#toTable");
            total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;

            if (item.status != 'sent' && item.status != 'completed') {

                if (item.options !== false) {
                    $.each(item.options, function () {
                        if (this.id == item_option && parseFloat(base_quantity) > parseFloat(this.quantity)) {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling != 1) {
                            $('#add_transfer, #edit_transfer').attr('disabled', true);
                            }
                            if (Tostatus == 'completed') {
                                var aaqty = parseFloat(quantity) + parseFloat(item_oqty);

                                if (parseFloat(base_quantity) > parseFloat(aaqty)) {
                                    if (site.settings.overselling != 1) {
                                    $('#edit_transfer').attr('disabled', true);
                                }
                            }
                        }
                        }
                    });
                } else if (parseFloat(base_quantity) > parseFloat(item_aqty)) {
                    $('#row_' + row_no).addClass('danger');

                    if (site.settings.overselling != 1) {
                    $('#add_transfer, #edit_transfer').attr('disabled', true);
                    }
                    if (Tostatus == 'completed') {
                        var aaqty = parseFloat(item_aqty) + parseFloat(item_bqty);

                        if (parseFloat(base_quantity) > parseFloat(aaqty)) {
                            if (site.settings.overselling != 1) {
                            $('#edit_transfer').attr('disabled', true);
                        }
                    }
                }
                }
            } else {
                $('#edit_transfer').attr('disabled', false);
            }

        });

        var col = 4;
        if (site.settings.product_expiry == 1) {
            col++;
        }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="' + col + '">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        tfoot += '<th class="text-right">' + formatMoney(total) + '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
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
                //alert(permission_owner)
                $('.rexpiry').attr("readonly", true);
                //$('.rquantity').attr("readonly", true);
                $('.tointer').hide();
            }
        }
        if (page_mode == 'edit') {
            //$('.rquantity').attr("readonly", true);
            if (ReadonlyData != 1) {
                //alert(permission_owner)
                $('.rexpiry').attr("readonly", true);
                //$('.rquantity').attr("readonly", true);
                $('.tointer').hide();
            }
        }
        if (tostatus == 'partial') {
            if (page_mode == 'edit') {
                if (ReadonlyData != 1) {
                    //$('.rquantity').attr("readonly", false);
                }
            }
        }
        var ttstatus = $('#tostatus').val();
        if (ttstatus == 'partial') {
            if (page_mode == 'edit') {
                //console.log(ReadonlyData);
                if (ReadonlyData == 1) {
                    //$('.rquantity').attr("readonly", false);
                }
            }
        }
        //$('.rqty_zero').attr("readonly", true);

        if (sent_edit_transfer == 1) {
            $('.rquantity').attr("readonly", true);
            $('#add_item').attr("readonly", true);
        }
        if (ttstatus == 'partial_completed') {
            $('.rquantity').attr("readonly", true);
            $('#add_item').attr("readonly", true);
        }

        if (Tostatus == 'request') {
            $('#edit_transfer').attr('disabled', false);
        }
    }

    localStorage.setItem('toitems', JSON.stringify(toitems));
}

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_transfer_item(item) {
    if (item.row.quantity < 1) {
        bootbox.alert('The product is out of stock and cannot be added to transfer');
    }
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

    //var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    var item_id = item.item_id;
    if (item.option_id != '') {
        item_id = item.item_id + item.option_id;
    }
    if (item.row.batch_number != '' && item.row.batch !== false) {
        item_id = item_id + item.row.batch;
    }

    if (toitems[item_id]) {
        toitems[item_id].row.qty = parseFloat(toitems[item_id].row.qty) + 1;
        var bsqty = parseFloat(toitems[item_id].row.base_quantity) + 1;
        toitems[item_id].row.base_quantity = unitToBaseQty(bsqty, this);
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