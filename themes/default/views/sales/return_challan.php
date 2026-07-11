<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, surcharge = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>,
        Pos_settings = <?= json_encode($pos_settings); ?>;

    $(document).ready(function () {
        <?php if ($inv) { ?>
        localStorage.setItem('rechref', '<?= $reference ?>');
        localStorage.setItem('rechnote', '<?= $this->sma->decode_html($inv->note); ?>');
        localStorage.setItem('rechitems', JSON.stringify(<?= $inv_items; ?>));
        localStorage.setItem('rechdiscount', '<?= $inv->order_discount_id ?>');
        localStorage.setItem('rechallantax2', '<?= $inv->order_tax_id ?>');
        localStorage.setItem('return_challan_surcharge', '0');
        localStorage.setItem('recpayment_status', '<?= $inv->payment_status ?>');
        localStorage.setItem('recpaid_amount', '<?= $inv->paid ?>');
        <?php if (!empty($payments)) { ?>
        localStorage.setItem('recpaid_by', '<?= end($payments)->paid_by ?>');
        <?php } else { ?>
        localStorage.setItem('recpaid_by', 'cash');
        <?php } ?>
        <?php } ?>
            
        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('rechdate')) {
            $("#rechdate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#rechdate', function (e) {
            localStorage.setItem('rechdate', $(this).val());
            $('#date_hidden').val($(this).val());
        });
        if (rechdate = localStorage.getItem('rechdate')) {
            $('#rechdate').val(rechdate);
            $('#date_hidden').val(rechdate);
        }
        <?php } ?>
        if (rechref = localStorage.getItem('rechref')) {
            $('#rechref').val(rechref);
        }
        if (rechdiscount = localStorage.getItem('rechdiscount')) {
            $('#rechdiscount').val(rechdiscount);
        }
        if (rechallantax2 = localStorage.getItem('rechallantax2')) {
            $('#rechallantax2').val(rechallantax2);
        }
        if (return_challan_surcharge = localStorage.getItem('return_challan_surcharge')) {
            $('#return_challan_surcharge').val(return_challan_surcharge);
        }
        if (localStorage.getItem('rechitems')) {
            loadItems();
        }
        
        // Set payment method to original value
        if (originalPaidBy = localStorage.getItem('recpaid_by')) {
            $('#paid_by_1').val(originalPaidBy);
            $('#paid_by_1').trigger('change');
        }

        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
            }
        });

        var old_row_qty;
        $(document).on("focus", '.rquantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.rquantity', function () {
            var row = $(this).closest('tr');
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            if (!is_numeric(new_qty) || (new_qty > rechitems[item_id].row.oqty)) {
                $(this).val(old_row_qty);
                bootbox.alert('<?= lang('unexpected_value'); ?>');
                return false;
            }
            rechitems[item_id].row.base_quantity = new_qty;
            if(rechitems[item_id].row.unit != rechitems[item_id].row.base_unit) {
                $.each(rechitems[item_id].units, function(){
                    if (this.id == rechitems[item_id].row.unit) {
                        rechitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            rechitems[item_id].row.qty = new_qty;
            localStorage.setItem('rechitems', JSON.stringify(rechitems));
            loadItems();
        });

        var old_surcharge;
        $(document).on("focus", '#return_challan_surcharge', function () {
            old_surcharge = $(this).val() ? $(this).val() : '0';
        }).on("change", '#return_challan_surcharge', function () {
            var new_surcharge = $(this).val() ? $(this).val() : '0';
            if (!is_valid_discount(new_surcharge)) {
                $(this).val(new_surcharge);
                bootbox.alert('<?= lang('unexpected_value'); ?>');
                return;
            }
            localStorage.setItem('return_challan_surcharge', JSON.stringify(new_surcharge));
            loadItems();
        });

        $(document).on('click', '.redel', function () {
            var row = $(this).closest('tr');
            var item_id = row.attr('data-item-id');
            delete rechitems[item_id];
            row.remove();
            localStorage.setItem('rechitems', JSON.stringify(rechitems));
            loadItems();
        });

        // Sales person dropdown change handler
        $(document).on('change', '.prsalesperson', function(){
            var row = $(this).closest('tr');
            item_id = row.attr('data-item-id');
            var prosp = $(this).val();
            var expsp = prosp.split('~');
            rechitems[item_id].row.seller_id = expsp[0];
            localStorage.setItem('rechitems', JSON.stringify(rechitems));
        });
    });

    function loadItems() {
        if (localStorage.getItem('rechitems')) {
            total = 0; count = 1; an = 1; product_tax = 0; invoice_tax = 0; product_discount = 0; order_discount = 0; total_discount = 0; surcharge = 0;
            $("#rechTable tbody").empty();
            rechitems = JSON.parse(localStorage.getItem('rechitems'));
            $.each(rechitems, function (id, item) {
                var item_id = id;
                var item_type = item.row.type, product_id = item.row.id, sale_item_id = item.row.sale_item_id, item_price = item.row.price, item_qty = item.row.qty, item_oqty = item.row.oqty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
                var item_option_color = item.row.option_color;
                var item_option_color_name = item.row.option_color_name;
                var net_unit_price = item.row.net_unit_price;
                var unit_price = item.row.unit_price;
                var product_unit = item.row.unit;
                var product_unit = item.row.unit;
                var packing_size = item.row.packing_size ? item.row.packing_size : 0;

                /* if (item.options !== false) {
                    $.each(item.options, function () {
                        if (this.id == item_option && this.price != 0 && this.price != '' && this.price != null) {
                            item_price = formatDecimal(parseFloat(unit_price) + (parseFloat(this.price)), 4);
                            unit_price = item_price;
                        }
                    });
                } */

                var ds = item_ds ? item_ds : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 4);
                    } else {
                        item_discount = formatDecimal(ds);
                    }
                } else {
                    item_discount = parseFloat(ds);
                }
                product_discount += formatDecimal(((packing_size > 0 ? item_discount * packing_size : item_discount) * item_qty), 4);

                unit_price = formatDecimal(unit_price - item_discount);
                var pr_tax = item.tax_rate;
                var pr_tax_val = 0, pr_tax_rate = 0;
                if (site.settings.tax1 == 1) {
                    if (pr_tax !== false) {
                        if (pr_tax.type == 1) {
                            if (item_tax_method == '0') {
                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                                pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                            } else {
                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / 100, 4);
                                pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                            }
                        } else if (pr_tax.type == 2) {
                            pr_tax_val = parseFloat(pr_tax.rate);
                            pr_tax_rate = pr_tax.rate;
                        }
                        
                        var pr_tax_surcharge_val = 0;
                        var pr_tax_final_val = parseFloat(pr_tax_val) || 0;
                        var pr_tax_rate_obj = getPrTaxRateObj(pr_tax, null);
                        var pr_tax_surcharge = null;
                        if (pr_tax_rate_obj) {
                            pr_tax_surcharge = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, item_qty);
                            var pr_tax_surcharge_unit = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, 1);
                            if (pr_tax_surcharge_unit.has_pr_tax_surcharge) {
                                pr_tax_surcharge_val = parseFloat(pr_tax_surcharge_unit.pr_tax_surcharge_val) || 0;
                                pr_tax_final_val = parseFloat(pr_tax_surcharge_unit.pr_tax_final_val) || pr_tax_final_val;
                            }
                        }
                        product_tax += (packing_size > 0 ? pr_tax_final_val * packing_size : pr_tax_final_val) * item_qty;
                    }
                }
                item_price = item_tax_method == 0 ? formatDecimal((unit_price - pr_tax_val - pr_tax_surcharge_val), 4) : formatDecimal(unit_price);
                unit_price = formatDecimal((unit_price + item_discount), 4);
                
                var sel_opt = '';
                var unit_qty = 1;
                $.each(item.options, function () {
                    if (this.id == item_option) {
                        sel_opt = this.name;
                        unit_qty = this.unit_quantity;
                    }
                });

                var base_quantity;
                if(item.row.storage_type == 'loose'){
                    base_quantity = formatDecimal((parseFloat(item_qty) * parseFloat(unit_qty)),3);
                }else{
                    base_quantity = formatDecimal((parseFloat(item_qty)),3);
                }

                var row_no = (new Date).getTime();
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
                tr_html = '<td><input name="sale_item_id[]" type="hidden" class="rsiid" value="' + (item.row.sale_item_id ? item.row.sale_item_id : '') + '"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><span class="sname" id="name_' + row_no + '">' + item_name + ' (' + item_code + ')' + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item_option_color_name ? ' (' + item_option_color_name + ')' : '') + '</span></td>';
                if (site.settings.product_serial == 1) {
                    tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="' + (item_serial ? item_serial : '') + '"></td>';
                }
                if (site.settings.product_batch_setting > 0) {
                    tr_html += '<td><input class="form-control rbtach_no" name="batch_number[]" type="text" value="' + (item.row.batch_number ? item.row.batch_number : '') + '" id="batch_number_' + row_no + '"></td>';
                }
                <?php if(isset($pos_settings->display_seller) && ($pos_settings->display_seller == 3 || $pos_settings->display_seller == 4)) { ?>
                if(Pos_settings.display_seller =='4' || Pos_settings.display_seller =='3'){
                    tr_html += '<td>';
                    tr_html += '<select name="product_sales_person[]" class="form-control prsalesperson" style="width:110px;">';
                    tr_html += '<option value="">Sales Person</option>';
                    <?php if(isset($salesperson_details) && is_array($salesperson_details)) { ?>
                        var salesperson = <?php echo json_encode($salesperson_details); ?>;
                        if (Array.isArray(salesperson)) {
                            for (var i = 0; i < salesperson.length; i++) {
                                var selected = (item.row.seller_id == salesperson[i].id) ? ' selected' : '';
                                tr_html += '<option value="' + salesperson[i].id + '~' + salesperson[i].name + '"' + selected + '>';
                                tr_html += salesperson[i].name;
                                tr_html += '</option>';
                            }
                        }
                    <?php } ?>
                    tr_html += '</select>';
                    tr_html += '</td>';
                }
                <?php } ?>
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item.row.real_unit_price) + '</span></td>';
                <?php if ($Settings->packing_size_column == 1) { ?>
                tr_html += '<td class="text-center"><input name="packing_size[]" type="hidden" class="rpackingsize" value="' + packing_size + '"><span>' + packing_size + '</span></td>';
                <?php } else { ?>
                tr_html += '<input name="packing_size[]" type="hidden" class="rpackingsize" value="' + packing_size + '">';
                <?php } ?>
                tr_html += '<td class="text-center"><span>' + formatDecimal(item_oqty) + '</span></td>';
                tr_html += '<td><input class="form-control text-center rquantity" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
                tr_html += '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" id="base_quantity_' + row_no + '" value="' + base_quantity + '"></td>';
                if (packing_size > 0) {
                    net_unit_price = (parseFloat(item.row.real_unit_price) * parseFloat(packing_size)) - parseFloat(item_discount * packing_size);
                }
                tr_html += '<td class="text-right">' + formatMoney(net_unit_price * item_qty) + '</td>';
                
                // Add hidden fields for required logic
                tr_html += '<input name="product_option[]" type="hidden" value="' + (item_option ? item_option : '') + '">';
                tr_html += '<input name="cat_id[]" type="hidden" value="' + (item.row.category_id ? item.row.category_id : '') + '">';
                tr_html += '<input name="product_option_color[]" type="hidden" value="' + (item.row.option_color ? item.row.option_color : '') + '">';
                tr_html += '<input name="mrp[]" type="hidden" value="' + (item.row.mrp ? item.row.mrp : '') + '">';
                tr_html += '<input name="cf1[]" type="hidden" value="' + (item.row.cf1 ? item.row.cf1 : '') + '">';
                tr_html += '<input name="item_weight[]" type="hidden" value="0">';

                if (site.settings.product_discount == 1) {
                    tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - ((packing_size > 0 ? item_discount * packing_size : item_discount) * item_qty)) + '</span></td>';
                }
                 if (site.settings.tax1 == 1) {
                    tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + (item.row.tax_rate ? item.row.tax_rate : '') + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatPrTaxSurchargeDisplay((packing_size > 0 ? pr_tax_val * packing_size : pr_tax_val) * item_qty, pr_tax_surcharge) + '</span></td>';
                }
                var item_subtotal = 0;
                if (packing_size > 0) {
                    item_subtotal = ((parseFloat(item_price) + parseFloat(pr_tax_final_val)) * parseFloat(packing_size) * parseFloat(item_qty));
                } else {
                    item_subtotal = ((parseFloat(item_price) + parseFloat(pr_tax_final_val)) * parseFloat(item_qty));
                }
                tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(item_subtotal) + '</span></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip pointer redel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#rechTable tbody");
                total += parseFloat(item_subtotal);
                count += parseFloat(item_qty);
                an++;
            });

            var gtotal = total;
            if (return_challan_surcharge = localStorage.getItem('return_challan_surcharge')) {
                var rs = return_challan_surcharge.replace(/"/g, '');
                if (rs.indexOf("%") !== -1) {
                    var prs = rs.split('%');
                    var percentage = parseFloat(prs[0]);
                    surcharge = parseFloat((gtotal * percentage) / 100);
                } else {
                    surcharge = parseFloat(rs);
                }
            }
            gtotal += surcharge;

            $('#total').text(formatMoney(total));
            $('#titems').text((an - 1) + ' (' + (parseFloat(count) - 1) + ')');
            $('#total_items').val((parseFloat(count) - 1));
            $('#trs').text(formatMoney(surcharge));
            if (site.settings.tax1) { $('#ttax1').text(formatMoney(product_tax)); }
            $('#gtotal').text(formatMoney(gtotal));
            if (count > 1) {
                var paymentStatus = localStorage.getItem('recpayment_status');
                var paidAmount = parseFloat(localStorage.getItem('recpaid_amount'));
                if (paymentStatus == 'paid') {
                    var roundedAmount = roundNumberNEW(gtotal, Number(Pos_settings.rounding));
                    $('#amount_1').val(formatDecimal(roundedAmount));
                } else if (paymentStatus == 'partial') {
                    var roundedAmount = roundNumberNEW(paidAmount, Number(Pos_settings.rounding));
                    $('#amount_1').val(formatDecimal(roundedAmount));
                } else {
                    $('#amount_1').val('');
                }
            }
        }
    }

    // Sales person validation function
    function validateReturnChallan() {
    if (Pos_settings.display_seller != '3') return true;

    var valid = true;
    $('#rechTable tbody tr[data-item-id]').each(function () {
        var item_id = $(this).attr('data-item-id');
        var dropdownVal = $(this).find('.prsalesperson').val();
        var storedSeller = (rechitems && rechitems[item_id] && rechitems[item_id].row)
                           ? rechitems[item_id].row.seller_id
                           : null;

        // Treat 0, "0", null, undefined, "" all as "not set"
        var hasDropdown = dropdownVal && dropdownVal !== '';
        var hasStored   = storedSeller && storedSeller !== '' && storedSeller != '0' && storedSeller !== 0;

        if (!hasDropdown && !hasStored) {
            alert('Please Select Item Wise Sales Person.');
            valid = false;
            return false;
        }
    });
    return valid;
}
    
    // Add form submission validation
    $(document).on('submit', 'form.edit-rech-form', function(e) {
        if (!validateReturnChallan()) {
            e.preventDefault();
            return false;
        }
        
        // Round the amount field before submission
        var amountField = $('#amount_1');
        if (amountField.val() && amountField.val() !== '') {
            var currentAmount = parseFloat(amountField.val());
            var roundedAmount = roundNumberNEW(currentAmount, Number(Pos_settings.rounding));
            amountField.val(formatDecimal(roundedAmount));
        }
    });

    function getPrTaxRateObj(pr_tax, tax_rate_id) {
        var pr_tax_rate_obj = null;
        var searchId = tax_rate_id;
        if (pr_tax && pr_tax.id) {
            searchId = pr_tax.id;
        }
        if (searchId) {
            $.each(tax_rates, function () {
                if (this.id == searchId) {
                    pr_tax_rate_obj = this;
                    return false;
                }
            });
        }
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
        var surcharge_val = pr_tax_surcharge.pr_tax_surcharge_val;
        var final_val = pr_tax_surcharge.pr_tax_final_val;
        if (Math.abs(pr_tax_val) > Math.abs(pr_tax_surcharge.pr_tax_val) + 0.01) {
            surcharge_val = pr_tax_surcharge.pr_tax_surcharge_line;
            final_val = pr_tax_surcharge.pr_tax_final_line;
        }
        return formatMoney(pr_tax_val) + ' + ' + formatMoney(surcharge_val) + ' = ' + formatMoney(final_val);
    }

    function setPrTaxDisplay(selector, pr_tax_val, pr_tax_rate_obj) {
        var pr_tax_surcharge = calcPrTaxSurcharge(pr_tax_rate_obj, pr_tax_val, 1);
        $(selector).text(formatPrTaxSurchargeDisplay(pr_tax_val, pr_tax_surcharge));
    }
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-minus-circle"></i><?= lang('return_challan'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-rech-form');
                echo form_open_multipart("sales/return_challan/" . $inv->id, $attrib);
                echo form_hidden(['syncQuantity'=>'1']);
                echo form_hidden('date_hidden', '', 'id="date_hidden"');
                ?>

                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "rechdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="rechdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "rechref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="rechref"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("return_surcharge", "return_challan_surcharge"); ?>
                                <?php echo form_input('return_surcharge', (isset($_POST['return_surcharge']) ? $_POST['return_surcharge'] : ''), 'class="form-control input-tip" id="return_challan_surcharge" required="required"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("challan_items"); ?></label>
                                <div class="controls table-controls">
                                    <table id="rechTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                            <?php if ($Settings->product_serial) { echo '<th class="col-md-2">' . lang("serial_no") . '</th>'; } ?>
                                            <?php if ($Settings->product_batch_setting > 0) { echo '<th class="col-md-1">' . lang("Batch_Number") . '</th>'; } ?>
                                            <?php if(isset($pos_settings->display_seller) && ($pos_settings->display_seller == 3 || $pos_settings->display_seller == 4)) { ?>
                                            <th class="col-md-1"><?= lang("Sales_Person"); ?></th>
                                            <?php } ?>
                                            <th class="col-md-1"><?= lang("Unit Price"); ?></th>
                                            <?php if ($Settings->packing_size_column == 1) { ?>
                                                <th class="col-md-1">Packing Size</th>
                                                <?php } ?>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
                                            <th class="col-md-1"><?= lang("return_quantity"); ?></th>
                                            <th class="col-md-1"><?= lang("net_price"); ?></th>
                                            <?php if ($Settings->product_discount) { echo '<th class="col-md-1">' . lang("discount") . '</th>'; } ?>
                                            <?php if ($Settings->tax1) { echo '<th class="col-md-1">' . lang("product_tax") . '</th>'; } ?>
                                            <th><?= lang("subtotal"); ?> (<span class="currency"><?= $default_currency->code ?></span>)</th>
                                            <th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                                <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                                    <tr class="warning">
                                        <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                                        <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                                        <?php if ($Settings->tax1) { ?>
                                        <td><?= lang('product_tax') ?> <span class="totals_val pull-right" id="ttax1">0.00</span></td>
                                        <?php } ?>
                                        <td><?= lang('surcharges') ?> <span class="totals_val pull-right" id="trs">0.00</span></td>
                                        <td><?= lang('return_amount') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div style="height:15px; clear: both;"></div>
                        <div class="col-md-12">
                            <?php
                            if ($inv->payment_status == 'paid') {
                                echo '<div class="alert alert-success">' . lang('payment_status') . ': <strong>' . $inv->payment_status . '</strong> & ' . lang('paid_amount') . ' <strong>' . $this->sma->formatMoney($inv->paid) . '</strong></div>';
                            } else {
                                echo '<div class="alert alert-warning">' . lang('payment_status_not_paid') . ' ' . lang('payment_status') . ': <strong>' . $inv->payment_status . '</strong> & ' . lang('paid_amount') . ' <strong>' . $this->sma->formatMoney($inv->paid) . '</strong></div>';
                            }
                            ?>
                        </div>
                        
                        <div id="payments">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1"
                                                               class="pa form-control kb-pad amount"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <?= lang("paying_by", "paid_by_1"); ?>
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <?= $this->sma->paid_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no" type="text" id="pcc_no_1"
                                                               class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_holder" type="text" id="pcc_holder_1"
                                                               class="form-control"
                                                               placeholder="<?= lang('cc_holder') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type" id="pcc_type_1"
                                                                class="form-control pcc_type"
                                                                placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                            <option
                                                                value="MasterCard"><?= lang("MasterCard"); ?></option>
                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month" type="text" id="pcc_month_1"
                                                               class="form-control" placeholder="<?= lang('month') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year" type="text" id="pcc_year_1"
                                                               class="form-control" placeholder="<?= lang('year') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1"
                                                               class="form-control" placeholder="<?= lang('cvv2') ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pcheque_1" style="display:none;">
                                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                                <input name="cheque_no" type="text" id="cheque_no_1"
                                                       class="form-control cheque_no"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
                        <input type="hidden" name="order_tax" value="" id="rechallantax2" required="required"/>
                        <input type="hidden" name="discount" value="" id="rechdiscount" required="required"/>
                        
                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?= lang("return_note", "rechnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="rechnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="fprom-group"><?php echo form_submit('add_return', lang("submit"), 'id="add_return" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
