$(document).ready(function () {
    if (!localStorage.getItem('qaref')) {
        localStorage.setItem('qaref', '');
    }

    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    //localStorage.clear();
    // If there is any item in localStorage
    if (localStorage.getItem('qaitems')) {
        loadItems(); 
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('slitems')) {
                    localStorage.removeItem('qaitems');
                }
                if (localStorage.getItem('qaitems')) {
                    localStorage.removeItem('qaitems');
                }
                if (localStorage.getItem('qaref')) {
                    localStorage.removeItem('qaref');
                }
                if (localStorage.getItem('qawarehouse')) {
                    localStorage.removeItem('qawarehouse');
                }
                if (localStorage.getItem('qanote')) {
                    localStorage.removeItem('qanote');
                }
                if (localStorage.getItem('qadate')) {
                    localStorage.removeItem('qadate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#qaref').change(function (e) {
        localStorage.setItem('qaref', $(this).val());
    });
    if (qaref = localStorage.getItem('qaref')) {
        $('#qaref').val(qaref);
    }
    $('#qawarehouse').change(function (e) {
        localStorage.setItem('qawarehouse', $(this).val());
    });
    if (qawarehouse = localStorage.getItem('qawarehouse')) {
        $('#qawarehouse').select2("val", qawarehouse);
    }

    //$(document).on('change', '#qanote', function (e) {
    $('#qanote').redactor('destroy');
    $('#qanote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('qanote', v);
        }
    });
    if (qanote = localStorage.getItem('qanote')) {
        $('#qanote').redactor('set', qanote);
    }

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

    $(document).on('click', '.qadel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete qaitems[item_id];
        row.remove();
        if (qaitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('qaitems', JSON.stringify(qaitems));
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */

    $(document).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
        var stockqty = $('#ShowQty_' + item_id).text();
        qaitems[item_id].row.product_qty = stockqty;
        qaitems[item_id].row.qty = new_qty;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
        loadItems();
    });

    $(document).on("change", '.rtype', function () {
        var row = $(this).closest('tr');
        var new_type = $(this).val(),
            item_id = row.attr('data-item-id');
        qaitems[item_id].row.type = new_type;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    $(document).on("change", '.rvariant', function () {
        var row = $(this).closest('tr');
        var new_opt = $(this).val(),
            item_id = row.attr('data-item-id');
        qaitems[item_id].row.option = new_opt;


        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    $(document).on('change', '.rbtach_no', function () {

        var item_id = $(this).closest('tr').attr('data-item-id');
        if ($(this).attr('type') == 'text' || $(this).prop('tagName').toLowerCase() == 'input') {
            var batch = $(this).val();

            var batch_id = qaitems[item_id].batchsData && qaitems[item_id].batchsData[batch] ? qaitems[item_id].batchsData[batch] : false;
            
            qaitems[item_id].row.batch_number = batch;
            var select_batch = 'change';
            qaitems[item_id].row.select_batch = select_batch;
            
            if (batch_id && qaitems[item_id].batchs[batch_id]) {
                qaitems[item_id].row.batch = batch_id;
                var batchvalue = qaitems[item_id].batchs[batch_id];
                qaitems[item_id].row.cost = batchvalue['cost'];
                qaitems[item_id].row.real_unit_cost = batchvalue['cost'];
                qaitems[item_id].row.base_unit_cost = batchvalue['cost'];
                qaitems[item_id].row.expiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
                qaitems[item_id].row.batch_stocks = batchvalue['stocks'];
            }
        } else {
            var batch = $(this).val();

            var batch_id = $(this).find(':selected').attr('data-batchid');

            batch_id = batch_id ? batch_id : (qaitems[item_id].batchsData && qaitems[item_id].batchsData[batch] ? qaitems[item_id].batchsData[batch] : false);
            var select_batch = 'change'
            qaitems[item_id].row.select_batch = select_batch;
            qaitems[item_id].row.batch_number = batch;

            if (batch_id && qaitems[item_id].batchs[batch_id]) {
                qaitems[item_id].row.batch = batch_id;

                var batchvalue = qaitems[item_id].batchs[batch_id];

                qaitems[item_id].row.cost = batchvalue['cost'];
                qaitems[item_id].row.real_unit_cost = batchvalue['cost'];
                qaitems[item_id].row.base_unit_cost = batchvalue['cost'];
                qaitems[item_id].row.expiry = batchvalue['expiry'] !== '' ? batchvalue['expiry'] : '';
                qaitems[item_id].row.batch_stocks = batchvalue['stocks'];
            }
        }
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
        loadItems();
    });


});



function onVariantChange(option_id, item_id) {

    qaitems[item_id].row.option = option_id;

    var opt_warehouse_qty = '';
    var selectedOption = null;

    // Iterate over the options object
    $.each(qaitems[item_id].options, function (key, option) {
        if (option.id === option_id) {
            selectedOption = option;
        }
    });
    if (selectedOption) {
        opt_warehouse_qty = selectedOption.warehouse_quant;

        // Update the item stock based on the selected option's quantity
        qaitems[item_id].row.item_stock = opt_warehouse_qty;
        qaitems[item_id].row.quantity = opt_warehouse_qty;
    }
    // qaitems[item_id].row.item_stock = qaitems[item_id].options[option_id].quantity;
    // qaitems[item_id].row.quantity   = qaitems[item_id].options[option_id].quantity;
    qaitems[item_id].row.cost = selectedOption.cost;
    qaitems[item_id].row.real_unit_cost = selectedOption.cost;
    qaitems[item_id].row.price = selectedOption.price;
    if (parseInt(site.settings.product_batch_setting) > 0 && qaitems[item_id].option_batches != false) {

        if (qaitems[item_id].option_batches[option_id]) {
            qaitems[item_id].batchs = qaitems[item_id].option_batches[option_id];

            $.each(qaitems[item_id].option_batches[option_id], function () {

                qaitems[item_id].row.batch = this.id;
                qaitems[item_id].row.batch_number = this.batch_no;
                qaitems[item_id].row.batch_stocks = this.stocks;
                return false;
            });
        } else {
            qaitems[item_id].batchs = false;
            qaitems[item_id].row.batch = false;
            qaitems[item_id].row.batch_number = false;
            qaitems[item_id].row.batch_stocks = 0;
        }
    }

    localStorage.setItem('qaitems', JSON.stringify(qaitems));

    loadItems();
}

/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {

    if (localStorage.getItem('qaitems')) {
        count = 1;
        an = 1;
        $("#qaTable tbody").empty();
        console.log("--- loadItems running (v2) ---");
        qaitems = JSON.parse(localStorage.getItem('qaitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(qaitems, function (o) {
            return [parseInt(o.order)];
        }) : qaitems;

        console.log('-------------sortedItems------------------');
        console.log(sortedItems);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            qaitems[item_id] = item;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_qty = item.row.qty, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_option_color = item.row.option_color, item_option_color_name = item.row.option_color_name, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var type = item.row.type ? item.row.type : '';
            // var batch_stock = item.row.batch_stocks;
            // var quantity = item.row.item_stock;
            var row_no = (new Date).getTime();
            var opt_warehouse_qty = '';
            var opt = $('<select id="poption_' + row_no + '" name="variant[]" class="form-control select " onchange="return onVariantChange(this.value, ' + item_id + ' );" />');
            if (item.options !== false && item.row.storage_type == 'packed') {
                $.each(item.options, function () {
                    if (item.row.option == this.id) {
                        $("<option />", { value: this.id, text: this.name, selected: 'selected' }).appendTo(opt);
                        opt_warehouse_qty = this.warehouse_quant;
                    }
                    else
                        $("<option />", { value: this.id, text: this.name }).appendTo(opt);
                });
            } else {
                $("<option />", { value: 0, text: 'n/a' }).appendTo(opt);
                opt = opt.hide();
                opt_warehouse_qty = item.row.item_stock;
            }

            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + '</span>';
            tr_html = '<td><span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (item_option_color_name ? ' (' + item_option_color_name + ')' : '') + '</span>';
            tr_html += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">';
            tr_html += '<input name="product_code[]" type="hidden" value="' + item.row.code + '">';
            tr_html += '<input name="product_name[]" type="hidden" value="' + item.row.name + '">';
            tr_html += '<input name="storage_type[]" id="storage_type_' + item_id + '" type="hidden" value="' + item.row.storage_type + '">';
            tr_html += '<input name="price[]" id="price_' + item_id + '" type="hidden" value="' + formatDecimal(item.row.price) + '">';
            tr_html += '<input name="cost[]" id="cost_' + item_id + '" type="hidden" value="' + formatDecimal(item.row.cost) + '">';
            tr_html += '<input name="real_unit_cost[]" type="hidden" value="' + formatDecimal(item.row.real_unit_cost) + '">';
            tr_html += '<input name="expiry[]" id="expiry_' + item_id + '" type="hidden" value="' + item.row.expiry + '">';
            tr_html += '<input name="tax_rate_id[]" type="hidden" value="' + (item.row.tax_rate_id != null && item.row.tax_rate_id !== '' ? item.row.tax_rate_id : '') + '">';
            tr_html += '<input name="tax_method[]" type="hidden" value="' + (item.row.tax_method != null && item.row.tax_method !== '' ? item.row.tax_method : '') + '">';
            tr_html += '<input name="product_type[]" type="hidden" value="' + item.row.product_type + '">';
            tr_html += '<input name="unit[]" type="hidden" value="' + item.row.unit + '">';
            tr_html += '<input name="hsn_code[]" type="hidden" value="' + item.row.hsn_code + '">';
            tr_html += '<input name="product_option_color[]" type="hidden" class = "roption_color" value="' + item_option_color + '">';
            tr_html += '<input name="mrp[]" id="mrp_' + item_id + '" type="hidden" value="' + formatDecimal(item.row.mrp) + '">';

            tr_html += '</td>';
            tr_html += '<td>' + (opt.get(0).outerHTML) + '</td>';

            if (site.settings.product_serial == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="' + item_serial + '"></td>';
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
                        if (item.row.select_batch == 'change') {
                            item.row.batch_number = item.row.batch_number;
                        } else {
                            item.row.batch_number = item.row.batch_number;
                        }
                        td_batch += '<input list="batches_' + row_no + '" type="text" ' + batch_required + '  class="form-control rbtach_no" name="batch_number[]" id="batch_number_' + row_no + '" value="' + (item.row.batch_number ? item.row.batch_number : '') + '" ><datalist id="batches_' + row_no + '">';
                        $.each(item.batchs, function (index, value) {
                            td_batch += '<option data-batchid="' + value.id + '"  value="' + value.batch_no + '" >';
                            batchno = value.batch_no;
                            batchid = value.id;
                            batchIds[batchno] = batchid;
                        });
                        td_batch += '</datalist>';
                        qaitems[item_id].batchsData = batchIds;
                    }
                } else {
                    var item_batch_number = (item_batch_number) ? item_batch_number : (item.row.batch_number ? item.row.batch_number : '');
                    td_batch += '<input class="form-control rbtach_no" ' + batch_required + ' name="batch_number[]" type="text" value="' + item_batch_number + '" data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';

                }
                td_batch += '</td>';

                td_batch += '<td class="text-right" id="ShowBatchQty_' + item_id + '">' + formatDecimal(item.row.batch_stocks) + '<input name="batch_qty[]" id="batch_qty_' + item_id + '" type="hidden" value="' + formatDecimal(item.row.batch_stocks) + '"></td>';
            }

            tr_html += td_batch;
            tr_html += '<td class="text-right" id="ShowOptionQty_' + item_id + '">' + formatDecimal(opt_warehouse_qty) + '<input name="item_qty[]" id="item_qty_' + item_id + '" type="hidden" value="' + formatDecimal(opt_warehouse_qty) + '"></td>';
            tr_html += '<td>' + (item.row.unit || '') + '</td>';
            tr_html += '<td>' + formatDecimal(item.row.cost) + '</td>';

            if ((Allow_Stock_Addition == 1 && Allow_Stock_Subtraction == 1) || (admin || owner)) {
                tr_html += '<td><select name="type[]" class="form-control select rtype" style="width:100%;"><option value="subtraction"' + (type == 'subtraction' ? ' selected' : '') + '>' + type_opt.subtraction + '</option><option value="addition"' + (type == 'addition' ? ' selected' : '') + '>' + type_opt.addition + '</option></select></td>';
            }
            else if (Allow_Stock_Addition == 1) {
                tr_html += '<td><select name="type[]" class="form-control select rtype" style="width:100%;"></option><option value="addition"' + (type == 'addition' ? ' selected' : '') + '>' + type_opt.addition + '</option></select></td>';

            }
            else if (Allow_Stock_Subtraction == 1) {
                tr_html += '<td><select name="type[]" class="form-control select rtype" style="width:100%;"><option value="subtraction"' + (type == 'subtraction' ? ' selected' : '') + '>' + type_opt.subtraction + '</option></select></td>';

            }



            tr_html += '<td><input class="form-control text-center rquantity" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" type="text" value="' + formatDecimal(item_qty > 0 ? item_qty : 1) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';


            tr_html += '<td class="text-center"><i class="fa fa-times tip qadel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#qaTable tbody");
            count += parseFloat(item_qty);
            an++;

        });

        var col = 8; // Base: Product Name, Variant, Item Stock, Unit, Unit Cost, Unit Quantity, Remove, +1 for Type
        if (site.settings.product_serial == 1) {
            col += 1; // Serial No
        }
        if (parseInt(site.settings.product_batch_setting) > 0) {
            col += 2; // Batches + Batch Stock
        }
        if (Allow_Stock_Addition == 0 && Allow_Stock_Subtraction == 0) {
            col -= 1; // Remove Type column

        }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="' + (col - 2) + '">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#qaTable tfoot').html(tfoot);
        $('select.select').select2({ minimumResultsForSearch: 7 });
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({ scrollTop: $('#sticker').offset().top }, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_adjustment_item(item) {

    if (count == 1) {
        qaitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;

    if (qaitems[item_id]) {
        qaitems[item_id].row.qty = parseFloat(qaitems[item_id].row.qty) + 1;
    } else {
        qaitems[item_id] = item;
    }
    qaitems[item_id].order = new Date().getTime();
    localStorage.setItem('qaitems', JSON.stringify(qaitems));
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

function update_qaitems_for_warehouse(warehouse_id) {
    if (localStorage.getItem('qaitems')) {
        var items = JSON.parse(localStorage.getItem('qaitems'));
        var updated = false;
        var new_items = {};
        
        $.each(items, function(item_key, item) {
            var item_id = item.item_id ? item.item_id : item.id;
            $.ajax({
                type: 'get',
                url: site.base_url + 'products/qa_suggestions',
                dataType: "json",
                async: false,
                data: {
                    term: item.row.code,
                    warehouse_id: warehouse_id,
                },
                success: function (data) {
                    if (data && data.length > 0) {
                        var new_item = data[0]; 
                        
                        // Preserve user-entered values
                        var old_qty = item.row.qty;
                        var old_type = item.row.type;
                        var old_batch_number = item.row.batch_number;
                        
                        // Replace item with new item
                        new_items[item_id] = new_item;
                        new_items[item_id].row.qty = old_qty;
                        if (old_type) new_items[item_id].row.type = old_type;
                        
                        // Check if old batch number still exists
                        var batch_still_exists = false;
                        if (new_item.batchs) {
                            $.each(new_item.batchs, function(idx, b) {
                                if (b.batch_no === old_batch_number) {
                                    batch_still_exists = true;
                                    items[item_id].row.batch_stocks = b.stocks;
                                    items[item_id].row.batch = b.id;
                                }
                            });
                        }
                        
                        if (!batch_still_exists) {
                            new_items[item_id].row.batch_number = '';
                            new_items[item_id].row.batch_stocks = 0;
                            new_items[item_id].row.batch = '';
                        } else {
                            new_items[item_id].row.batch_number = old_batch_number;
                        }
                        
                        // Preserve order if exists
                        if (item.order) new_items[item_id].order = item.order;
                        else new_items[item_id].order = new Date().getTime();
                        
                        updated = true;
                    }
                }
            });
        });
        
        if (updated) {
            qaitems = new_items;
            localStorage.setItem('qaitems', JSON.stringify(new_items));
            loadItems();
        }
    }
}