<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, surcharge = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>,
        Pos_settings = <?= json_encode($pos_settings); ?>;

    $(document).ready(function () {
        <?php if ($inv) { ?>
        //localStorage.setItem('redate', '<?= $this->sma->hrld($inv->date) ?>');
        localStorage.setItem('reref', '<?= $reference ?>');
        localStorage.setItem('renote', '<?= $this->sma->decode_html($inv->note); ?>');
        localStorage.setItem('reitems', JSON.stringify(<?= $inv_items; ?>));
        localStorage.setItem('rediscount', '<?= $inv->order_discount_id ?>');
        localStorage.setItem('retax2', '<?= $inv->order_tax_id ?>');
        localStorage.setItem('return_surcharge', '0');
        <?php } ?>
            
        // Check initial payment method and disable date if it's deposit
        var initialPaymentMethod = $('#paid_by_1').val();
        if (initialPaymentMethod == 'deposit') {
            // Store current date value before disabling
            var currentDateValue = $('#redate').val();
            if (!currentDateValue) {
                $('#redate').val(moment().format('YYYY-MM-DD HH:mm:ss'));
            }
            $('#redate').prop('disabled', true);
        }
            
        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('redate')) {
            $("#redate").datetimepicker({
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
        $(document).on('change', '#redate', function (e) {
            localStorage.setItem('redate', $(this).val());
            // Sync hidden field to ensure date is always submitted
            $('#date_hidden').val($(this).val());
        });
        if (redate = localStorage.getItem('redate')) {
            $('#redate').val(redate);
            $('#date_hidden').val(redate);
        }
        <?php } ?>
        if (reref = localStorage.getItem('reref')) {
            $('#reref').val(reref);
        }
        if (rediscount = localStorage.getItem('rediscount')) {
            $('#rediscount').val(rediscount);
        }
        if (retax2 = localStorage.getItem('retax2')) {
            $('#retax2').val(retax2);
        }
        if (return_surcharge = localStorage.getItem('return_surcharge')) {
            $('#return_surcharge').val(return_surcharge);
        }
        /*$(window).bind('beforeunload', function (e) {
         //localStorage.setItem('remove_resl', true);
         if (count > 1) {
         var message = "You will loss data!";
         return message;
         }
         });
         $('#add_return').click(function () {
         $(window).unbind('beforeunload');
         $('form.edit-resl-form').submit();
         });*/
        if (localStorage.getItem('reitems')) {
            loadItems();
        }

        $(document).on('change','.credit_card_no',function(){
            var cardNo = $(this).val();
            var getId = $(this).attr('id');
            $.ajax({
                type:'ajax',
                dataType:'json',
                method:'get',
                url:'<?= base_url("sales/checkCreaditNo/") ?>'+cardNo,
                async:false,
                success:function(result){
                    if(result.status==true){
                      bootbox.alert(result.message);
                      $('#'+getId).val('');
                      $('#'+getId).focus();
                    }
                },errors:function(){
                    console.log('error');
                }
                        
            });
        });
        
        
       
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            //localStorage.setItem('paid_by', p_val);
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                //$('#amount_1').focus();
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
            if (p_val == 'gift_card') {
                $('.gc').show();
                $('.ngc').hide();
                $('#gift_card_no').focus();
            } else {
                $('.ngc').show();
                $('.gc').hide();
                $('#gc_details').html('');
            }
            if (p_val == 'credit_note') {  
                $('.cd_1').show();
                $('.ngc').hide();
                $('#credit_card_no_1').focus();
            } else {
                $('.ngc').show();
                $('.cd_1').hide();
                $('#credit_details_1').html('');
            }

            if(p_val == 'deposit'){
                 $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').hide();
                 $('.deposit_by_return').show();
              
                $('#deposit_paid_by_1').focus();                
            }

            if (p_val == 'deposit') {
                // Store current date value before disabling
                var currentDateValue = $('#redate').val();
                if (!currentDateValue) {
                    var newDate = moment().format('YYYY-MM-DD HH:mm:ss');
                    $('#redate').val(newDate);
                    $('#date_hidden').val(newDate);
                } else {
                    $('#date_hidden').val(currentDateValue);
                }
                // Update localStorage with the date value
                localStorage.setItem('redate', $('#redate').val());
                $('#redate').prop('disabled', true);
            } else {
                $('#redate').prop('disabled', false);
                // Restore date from localStorage if available
                var storedDate = localStorage.getItem('redate');
                if (storedDate) {
                    $('#redate').val(storedDate);
                    $('#date_hidden').val(storedDate);
                }
            }

  
        });
        /* ------------------------------
         * Sell Gift Card modal
         ------------------------------- */

        $(document).on('click', '#sellGiftCard', function (e) {
            $('#gcvalue').val($('#amount_1').val());
            $('#gccard_no').val(generateCardNo());
            $('#gcModal').appendTo("body").modal('show');
            return false;
        });
        $('#gccustomer').val(<?=$inv->customer_id?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?= site_url('customers/getCustomer') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
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

        $(document).on('click', '#noCus', function (e) {
            e.preventDefault();
            $('#gccustomer').select2('val', '');
            return false;
        });

        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
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
            if (typeof slitems === "undefined") {
                var slitems = {};
            }

            $.ajax({
                type: 'get',
                url: site.base_url + 'sales/sell_gift_card',
                dataType: "json",
                data: {gcdata: gc_data},
                success: function (data) {
                    if (data.result === 'success') {
                        $('#gift_card_no').val(gccode);
                        $('#gc_details').text('<?=lang('gift_card_added')?>');
                        $('#gcModal').modal('hide');
                    } else {
                        $('#gcerror').text(data.message);
                        $('.gcerror-con').show();
                    }
                }
            });
            return false;
        });
        var old_row_qty;
        $(document).on("focus", '.rquantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.rquantity', function () {
            var row = $(this).closest('tr');
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            if (!is_numeric(new_qty) || (new_qty > reitems[item_id].row.oqty)) {
                $(this).val(old_row_qty);
                bootbox.alert('<?= lang('unexpected_value'); ?>');
                return false;
            }
            if(new_qty > reitems[item_id].row.oqty) {
                bootbox.alert('<?= lang('unexpected_value'); ?>');
                $(this).val(old_row_qty);
                return false;
            }
            reitems[item_id].row.base_quantity = new_qty;
            if(reitems[item_id].row.unit != reitems[item_id].row.base_unit) {
                $.each(reitems[item_id].units, function(){
                    if (this.id == reitems[item_id].row.unit) {
                        reitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            reitems[item_id].row.qty = new_qty;
            // reitems[item_id].row.new_qty = new_qty;
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
        });
        var old_surcharge;
        $(document).on("focus", '#return_surcharge', function () {
            old_surcharge = $(this).val() ? $(this).val() : '0';
        }).on("change", '#return_surcharge', function () {
            var new_surcharge = $(this).val() ? $(this).val() : '0';
            if (!is_valid_discount(new_surcharge)) {
                $(this).val(new_surcharge);
                bootbox.alert('<?= lang('unexpected_value'); ?>');
                return;
            }
            localStorage.setItem('return_surcharge', JSON.stringify(new_surcharge));
            loadItems();
        });
        $(document).on('click', '.redel', function () {
            var row = $(this).closest('tr');
            var item_id = row.attr('data-item-id');
            delete reitems[item_id];
            row.remove();
            if(reitems.hasOwnProperty(item_id)) { } else {
                localStorage.setItem('reitems', JSON.stringify(reitems));
                loadItems();
                return;
            }
        });
    });
    
    // Sales person dropdown change handler
    $(document).on('change', '.prsalesperson', function(){
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        $('#row_id').val(row_id);
        item_id = row.attr('data-item-id');
        
        item = reitems[item_id];
        var prosp = $(this).val();
        var expsp = prosp.split('~');
        
        reitems[item_id].row.seller_id = expsp[0];
        localStorage.setItem('reitems', JSON.stringify(reitems));

        // Don't reload items, just update the data
        // loadItems();
    });
    
    //localStorage.clear();
    function loadItems() {
        
        if (localStorage.getItem('reitems')) {
            total = 0;
            count = 1;
            an = 1;
            product_tax = 0;
            invoice_tax = 0;
            product_discount = 0;
            order_discount = 0;
            total_discount = 0;
            surcharge = 0;
            rounding  = 0;
            var item_weight = 0, return_item_weight = 0;
            
            $("#reTable tbody").empty();
            reitems = JSON.parse(localStorage.getItem('reitems'));
            
            console.log('-------------reitems-----------');
            console.log(reitems);
            
            $.each(reitems, function () {
                var item = this;
                var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;

                var item_type = item.row.type, product_id = item.row.id, combo_items = item.combo_items, sale_item_id = item.row.sale_item_id, item_option = item.row.option, item_option_color = item.row.option_color,item_option_color_name = item.row.option_color_name, item_price = item.row.price, item_qty = item.row.qty, item_oqty = item.row.oqty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
                var unit_price = item.row.unit_price;
                 rounding = item.row.rounding;
                  var cf1 = item.row.cf1;
                  var new_qty = item.row.new_qty;
                var product_unit = item.row.unit; 
                var packing_size = item.row.packing_size ? item.row.packing_size : 0;
                // base_quantity = item.row.base_quantity;
                if(product_unit != item.row.base_unit) {
                    $.each(item.units, function(){
                        if (this.id == product_unit) {
                            base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        }
                    });
                }
                if(item.options !== false) {
                           /* $.each(item.options, function () {
                                if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                                    item_price = unit_price + (parseFloat(this.price));
                                    unit_price = item_price;
                                }
                            });*/
              $.each(item.options, function () {
                    var this_options = this;
                      
                    //If Select multiple options
                    if (jQuery.type(item.row.option) == 'string') {
                        
                        var optionArr = item.row.option.split(",");
                        $.each(optionArr, function (k, opt) {
                            
                            if (this_options.id == opt) {     
                       
                                if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {            //console.log(this_options.id +'=='+ opt+'/'+unit_price+' '+this_options.price);             
//                                    item_price = formatDecimal(parseFloat(unit_price) + parseFloat(this_options.price),6);
//                                    unit_price = item_price;
// 				    console.log(unit_price);
                                }
                                if (k) {
                                    sel_opt = sel_opt + ',' + this_options.name;
                                } else {
                                    sel_opt = this_options.name;
                                }
                            }
                        });
                    } else {                       
                        if (this_options.id == item.row.option) {
                            if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                                item_price = formatDecimal(parseFloat(unit_price) + (parseFloat(this_options.price)),6);
                                unit_price = item_price;
                            }
                            sel_opt = this_options.name;
                        }
                    }
                });
			}
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

                unit_price = formatDecimal(unit_price-item_discount);
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
                item_price = item_tax_method == 0 ? formatDecimal((unit_price-pr_tax_val-pr_tax_surcharge_val), 4) : formatDecimal(unit_price);
                unit_price = formatDecimal((unit_price+item_discount), 4);
                var net_unit_price = item.row.net_unit_price; 
                var sel_opt = '';
                var unit_qty = 1;
                $.each(item.options, function () {
                    if(this.id == item_option) {
                        sel_opt = this.name;
                        unit_qty = this.unit_quantity;
                    }
                });
                if(item.row.storage_type == 'loose'){
                    var base_quantity = formatDecimal((parseFloat(item.row.qty) * parseFloat(unit_qty)),3);
                }else{
                    var base_quantity = formatDecimal((parseFloat(item.row.qty)),3);
                }
                var sel_opt_color = '';
                $.each(item.options_color, function () {
                    if(this.id == item_option_color) {
                        sel_opt_color = this.name;
                    }
                });
               
                
                //'+(item_option != 0 ? ' - '+item.option.name : '')+'
                var row_no = (new Date).getTime();
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
                tr_html = '<td><input name="sale_item_id[]" type="hidden" class="rsiid" value="' + sale_item_id + '"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><span class="sname" id="name_' + row_no + '">' + item_name + ' (' + item_code + ')'+(sel_opt != '' ? ' ('+sel_opt+')' : '')+(item_option_color_name ? ' (' + item_option_color_name + ')' : '') +'</span></td>';
                if (site.settings.product_serial == 1) {
                    tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="' + item_serial + '"></td>';
                }
                //tr_html +='<td class="text-right">'+formatMoney(item.row.mrp)+'</td>';
                
                if (parseInt(site.settings.product_batch_setting) > 0) {
                    tr_html += '<td><input class="form-control rbtach_no" name="batch_number[]" type="text" value="' + item.row.batch_number + '" data-id="' + row_no + '" data-item="' + item_id + '" id="batch_number_' + row_no + '">';
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
                if (site.settings.product_expiry == 1) {
                    tr_html += '<td><input  style="width:100px;" name="cf1[]" type="date" placeholder="MM/YYYY" class="form-control rexpiry cf1" value="' + cf1 + '"> </td>';
                }                
	
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(unit_price) + '</span></td>';
                
                <?php if ($Settings->packing_size_column == 1) { ?>
                tr_html += '<td class="text-center"><input name="packing_size[]" type="hidden" class="rpackingsize" value="' + packing_size + '"><span>' + packing_size + '</span></td>';
                <?php } else { ?>
                tr_html += '<input name="packing_size[]" type="hidden" class="rpackingsize" value="' + packing_size + '">';
                <?php } ?>
                tr_html += '<td class="text-center"><span>' + formatDecimal(item_oqty) + '</span>'; 
                
                item_weight         = (item.row.unit_weight) ? (parseFloat(item_oqty) * parseFloat(item.row.unit_weight)) : '';
                return_item_weight  = (item.row.unit_weight) ? (parseFloat(item_qty) * parseFloat(item.row.unit_weight)) : '';
                
                tr_html += '<input name="item_weight[]" type="hidden" class="rweight" value="' + formatDecimal(return_item_weight,3) + '"></td>';
                if (site.settings.product_weight == 1) {
                    tr_html += '<td class="text-center"><span>' + formatDecimal(item_weight,3) + ' Kg</span></td>';  
                }
                
                // tr_html += '<td><input class="form-control text-center rquantity" name="quantity[]" type="text" value="' + formatDecimal(base_quantity) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
                tr_html += '<td><input class="form-control text-center rquantity" name="quantity[]" type="text" value="' + formatDecimal(parseFloat(item_qty)) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
                tr_html += '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">';
                // tr_html += '<input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + (parseFloat(item_qty) * parseFloat(unit_qty))  + '">';
                tr_html += '<input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + (parseFloat(base_quantity))  + '">';
                tr_html += '<input name="return_unit_quantity[]" type="hidden" class="runit_quantity" value="' + (parseFloat(unit_qty) * parseFloat(item_qty)) + '"></td>';
                /////////// Multiple Category //////////////
                tr_html += '<input name="cat_id[]" type="hidden"  value="' + item.row.category_id + '"></td>';  
                tr_html += '<input name="product_option_color[]" type="hidden" class="roption_color"  value="' + item_option_color + '"></td>';
               
                if (packing_size > 0) {
                    net_unit_price = (parseFloat(item.row.real_unit_price) * parseFloat(packing_size)) - parseFloat(item_discount * packing_size);
                }
                tr_html +='<td class="text-right">'+formatMoney(parseFloat(net_unit_price) * parseFloat(item_qty))+'</td>'; 
                if (site.settings.product_discount == 1) {
                    tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - ((packing_size > 0 ? item_discount * packing_size : item_discount) * item_qty)) + '</span></td>';
                }
                if (site.settings.tax1 == 1) {
                    tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatPrTaxSurchargeDisplay((packing_size > 0 ? pr_tax_val * packing_size : pr_tax_val) * item_qty, pr_tax_surcharge) + '</span></td>';
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
                newTr.prependTo("#reTable");
                total += parseFloat(item_subtotal);
                count += parseFloat(item_qty);
                an++;

            });
            // Order level discount calculations
           /* if (rediscount = localStorage.getItem('rediscount')) {
                var ds = rediscount;
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        order_discount = parseFloat(((total ) * parseFloat(pds[0])) / 100);//+ product_tax
                    } else {
                        order_discount = parseFloat(ds);
                    }
                } else {
                    order_discount = parseFloat(ds);
                }

            }*/

            // Order level tax calculations
            if (site.settings.tax2 != 0) {
                if (retax2 = localStorage.getItem('retax2')) {
                    $.each(tax_rates, function () {
                        if (this.id == retax2) {
                            if (this.type == 2) {
                                invoice_tax = parseFloat(this.rate);
                            }
                            if (this.type == 1) {
                                invoice_tax = parseFloat(((total - order_discount) * this.rate) / 100);// + product_tax
                            }
                        }
                    });
                }
            }
            total_discount = parseFloat(order_discount + product_discount);

            // Totals calculations after item addition
            var gtotal = parseFloat(((total + invoice_tax) - order_discount));

            if (return_surcharge = localStorage.getItem('return_surcharge')) {
                var rs = return_surcharge.replace(/"/g, '');
                if (rs.indexOf("%") !== -1) {
                    var prs = rs.split('%');
                    var percentage = parseFloat(prs[0]);
                    if (!isNaN(prs[0])) {
                        surcharge = parseFloat((gtotal * percentage) / 100);
                    } else {
                        surcharge = parseFloat(rs);
                    }
                } else {
                    surcharge = parseFloat(rs);
                }
            }

            gtotal -= surcharge;
           
            //add Rounding for sale in Amount 9-11-2019
            if(rounding != 0.0000 || rounding!=0){
                gtotal = gtotal + parseFloat(rounding);
             }
           
            $('#total').text(formatMoney(total));
            $('#titems').text((an - 1) + ' (' + (parseFloat(count) - 1) + ')');
            $('#total_items').val((parseFloat(count) - 1));
            $('#trs').text(formatMoney(surcharge));
            if (site.settings.tax1) {
                $('#ttax1').text(formatMoney(product_tax));
            }
            if (site.settings.tax2 != 0) {
                $('#ttax2').text(formatMoney(invoice_tax));
            }
            $('#gtotal').text(formatMoney(gtotal));

             
            <?php if($inv->payment_status == 'paid') { echo "$('#amount_1').val(formatDecimal(gtotal));"; } ?>
            if (an > site.settings.bc_fix && site.settings.bc_fix != 0) {
                $("html, body").animate({scrollTop: $('#reTable').offset().top - 150}, 500);
                $(window).scrollTop($(window).scrollTop() + 1);
            }
            if (count > 1) {
                $('#add_item').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
            }
            //audio_success.play();
        }
    }
    
    // Sales person validation function
    function validateReturnSale() {
        var displaySeller = <?php echo (int)$pos_settings->display_seller; ?>;
        
        // Item-level sales person validation (mandatory when display_seller is 3)
        if (displaySeller === 3) {
            var valid = true;
            $('.prsalesperson').each(function () {
                var val = $(this).val(); // "id~name" OR ""
                if (!val) {
                    alert('Please Select Item Wise Sales Person.');
                    valid = false;
                    return false; // break the loop
                }
            });
            if (!valid) {
                return false;
            }
        }
        
        return true;
    }
    
    // getSellerDetails function for invoice-level sales person
    function getSellerDetails(val) {
        // This function handles sales person selection at invoice level
        console.log('Invoice sales person selected:', val);
    }
    
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

    $(document).on('submit', 'form.edit-resl-form', function(e) {
        if (!validateReturnSale()) {
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
</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-minus-circle"></i><?= lang('return_sale'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-resl-form');
                echo form_open_multipart("sales/return_sale/" . $inv->id, $attrib);
                 echo form_hidden(['syncQuantity'=>'1']);
                 // Hidden field to ensure date is always submitted, even when visible date field is disabled
                 echo form_hidden('date_hidden', '', 'id="date_hidden"');
                ?>

                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "redate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="redate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "reref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="reref"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("return_surcharge", "return_surcharge"); ?>
                                <?php echo form_input('return_surcharge', (isset($_POST['return_surcharge']) ? $_POST['return_surcharge'] : ''), 'class="form-control input-tip" id="return_surcharge" required="required"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <?php if($Pos_settings->display_seller == 1 || $Pos_settings->display_seller == 2) { ?>
    <div class="col-md-4">
        <div class="form-group">
            <?= lang("Sales_Person", "Sales_Person"); ?>
            <select name="sales_person" id="sales_person" class="form-control" onchange="getSellerDetails(this.value);">
                <option value="0">Select Sales Person</option>
                <?php if(!empty($salesperson_details)) {
    foreach($salesperson_details as $sp){ ?>
        <option value="<?= $sp->id . '-' . $sp->name; ?>" <?= (isset($inv->seller_id) && $inv->seller_id == $sp->id) ? 'selected="selected"' : ''; ?>>
            <?= $sp->name; ?>
        </option>
<?php } } ?>
            </select>
        </div>
    </div>
<?php } ?>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label> (<?= lang('return_tip'); ?>)

                                <div class="controls table-controls">
                                    <table id="reTable"
                                           class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                           <?php
                                            if ($Settings->product_serial) {
                                                echo '<th class="col-md-1">' . $this->lang->line("serial_no") . '</th>';
                                            }
                                            ?>
                                            <!-- <th class="col-md-1"><?= lang("MRP"); ?></th>-->
                                            <?php if ($Settings->product_batch_setting > 0) { ?>
                                            <th class="col-md-1"><?= lang("Batch_Number"); ?></th>
                                            <?php } ?>
                                            <?php if(isset($pos_settings->display_seller) && ($pos_settings->display_seller == 3 || $pos_settings->display_seller == 4)) { ?>
                                            <th class="col-md-1"><?= lang("Sales_Person"); ?></th>
                                            <?php } ?>
                                            <?php if ($Settings->product_expiry > 0) { ?>
                                            <th class="col-md-1"><?= lang("expiry_date")?></th>
                                            <?php } ?>
                                             <th class="col-md-1"><?= lang("Unit Price"); ?></th>
                                             <?php if ($Settings->packing_size_column == 1) { ?>
                                                <th class="col-md-1">Packing Size</th>
                                                <?php } ?>
                                             <th class="col-md-1"><?= lang("quantity"); ?></th>
                                            <?php if ($Settings->product_weight == 1) { ?>
                                             <th class="col-md-1"><?= lang("Weight"); ?></th>
                                            <?php } ?> 
                                            <th class="col-md-1"><?= lang("return_quantity"); ?></th>
                                            <th class="col-md-1"><?= lang("Net Price"); ?></th>
                                            
                                            
                                            <?php
                                            if ($Settings->product_discount) {
                                                echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th><?= lang("subtotal"); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                            </th>
                                            <th style="width: 30px !important; text-align: center;"><i
                                                    class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                                <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                                    <tr class="warning">
                                        <td>
                                            <?= lang('items') ?>
                                            <span class="totals_val pull-right" id="titems">0</span>
                                        </td>
                                        <td>
                                            <?= lang('total') ?>
                                            <span class="totals_val pull-right" id="total">0.00</span>
                                        </td>
                                        <?php if ($Settings->tax1) { ?>
                                        <td>
                                            <?= lang('product_tax') ?>
                                            <span class="totals_val pull-right" id="ttax1">0.00</span>
                                        </td>
                                        <?php } ?>
                                        <td>
                                            <?= lang('surcharges') ?>
                                            <span class="totals_val pull-right" id="trs">0.00</span>
                                        </td>
                                        <?php if ($Settings->tax2) { ?>
                                        <td>
                                            <?= lang('order_tax') ?>
                                            <span class="totals_val pull-right" id="ttax2">0.00</span>
                                        </td>
                                        <?php } ?>
                                        <td>
                                            <?= lang('return_amount') ?>
                                            <span class="totals_val pull-right" id="gtotal">0.00</span>
                                        </td>
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
                        <?php //if (($Owner || $Admin || $GP['sales-payments']) && ($inv->payment_status == 'paid' || $inv->payment_status == 'partial')) { ?>
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
                                                        <?= $this->sma->paid_opts($payment); ?>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                          <div  class="row deposit_by_return" style="display:none" >
                                            <div class="form-group col-sm-4">
                                                    <?= lang("Deposit to Paid By", "deposit_paid_by_1"); ?>
                                                    <select name="paid_by_deposit" id="deposit_paid_by_1" class="form-control ">
                                                        <?= $this->sma->paid_opts($payment); ?>
                                                    </select>
                                            </div>
                                        </div>
                                         

                                        <div class="cd_1" id="cd_1" style="display:none;">
					     <label for="credit_card_no_1">Credit Note</label>
					     <input name="credit_card_no" type="text" id="credit_card_no_1" value="<?= mt_rand(1000,9999) ?>" class="pa form-control kb-pad credit_card_no" autocomplete="off">
					     <div id="credit_details_1"></div>  
					</div>
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
                                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
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
                                        <div class="gc" style="<?php if($payment!='gift_card') echo 'display: none;'; ?>">
                                            <div class="form-group">
                                                <?= lang("gift_card_no", "gift_card_no"); ?>
                                                <div class="input-group">

                                                    <input name="gift_card_no" type="text" id="gift_card_no"
                                                           class="pa form-control kb-pad" value="<?php if($payment=='gift_card') echo $cc_no; ?>"/>

                                                    <div class="input-group-addon"
                                                         style="padding-left: 10px; padding-right: 10px; height:25px;">
                                                        <a href="#" id="sellGiftCard" class="tip"
                                                           title="<?= lang('sell_gift_card') ?>"><i
                                                                class="fa fa-credit-card"></i></a></div>
                                                </div>
                                            </div>
                                            <div id="gc_details"></div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <?php // } ?>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
                        <input type="hidden" name="order_tax" value="" id="retax2" required="required"/>
                        <input type="hidden" name="discount" value="" id="rediscount" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?= lang("return_note", "renote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_return', $this->lang->line("submit"), 'id="add_return" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?></div>
                        </div>
                    </div>
                </div>


                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?= lang("card_no", "gccard_no"); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no" onClick="this.select();"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                                                                                           id="genNo"><i
                                    class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname"/>

                <div class="form-group">
                    <?= lang("value", "gcvalue"); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("customer", "gccustomer"); ?>
                    <div class="input-group">
                        <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                                                                                           id="noCus"
                                                                                                           class="tip"
                                                                                                           title="<?= lang('unselect_customer') ?>"><i
                                    class="fa fa-times"></i></a></div>
                    </div>
                </div>
                <div class="form-group">
                    <?= lang("expiry_date", "gcexpiry"); ?>
                    <?php echo form_input('gcexpiry', '', 'class="form-control date" id="cgexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>
