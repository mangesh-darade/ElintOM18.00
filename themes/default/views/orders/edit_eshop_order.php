<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$formaction = $this->router->fetch_method();
?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
            product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
            tax_rates = <?php echo json_encode($tax_rates); ?>;
    pos_settings = <?= json_encode($pos_settings); ?>;
    //var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
<?php if ($inv) { ?>
            localStorage.setItem('sldate', '<?= $this->sma->hrld($inv->date) ?>');
            localStorage.setItem('slcustomer', '<?= $inv->customer_id ?>');
            localStorage.setItem('slbiller', '<?= $inv->biller_id ?>');
            localStorage.setItem('slref', '<?= $inv->reference_no ?>');
            localStorage.setItem('slwarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('slsale_status', '<?= $inv->sale_status ?>');
            localStorage.setItem('slpayment_status', '<?= $inv->payment_status ?>');
            localStorage.setItem('slpayment_term', '<?= $inv->payment_term ?>');
            localStorage.setItem('slnote', "<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)); ?>");
            localStorage.setItem('slinnote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->staff_note)); ?>');
            localStorage.setItem('sldiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('sltax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('slshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('slitems', JSON.stringify(<?= $inv_items; ?>));
<?php } ?>

<?php if ($Owner || $Admin) { ?>
            $(document).on('change', '#sldate', function (e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (sldate = localStorage.getItem('sldate')) {
                $('#sldate').val(sldate);
            }
<?php } ?>
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(window).bind('beforeunload', function (e) {
            localStorage.setItem('remove_slls', true);
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_sale').click(function () {
            $(window).unbind('beforeunload');
            $('form.edit-so-form').submit();
        });

//        $('#sldelivery_status').on('change', function(){
//            
//           show_hide_delevey_options(this.value)
//             
//        });

    });



    function show_hide_delevey_options(status) {

        switch (status) {
            case 'pending':
                $('.delivery_items').hide();
                break;
            case 'partial':
                $('.delivery_items').show();
                break;
            case 'delivered':
                $('.delivery_items').hide();
                break;
        }
    }
</script>
<input type="hidden" name="sale_action" id="sale_action" value="<?php echo $sale_action; ?>">
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= ($formaction == 'edit') ? lang('edit_sale') : 'Edit Eshop Order'; ?></h2>
        <h2 class="blue" ><p style="font-weight:bold; margin-left:250px;"><?= lang("Invoice Number"); ?> : <?= lang($inv->id); ?></p></h2>
    </div>
    <p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if ($formaction == 'edit_eshop_order')
                    $ModuleAct = 'orders';
                else
                    $ModuleAct = 'sales';
                ?>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-so-form');
                echo form_open_multipart($ModuleAct . "/" . $formaction . "/" . $inv->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                                <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                            <?= lang("date", "sldate"); ?>
                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($inv->date)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                                <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                        <?= lang("reference_no", "slref"); ?>
                        <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref" required="required"'); ?>
                            </div>
                        </div>
                                <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'slbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        }
                        ?>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                            <?php //if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {  ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("warehouse", "slwarehouse"); ?>
                                            <?php
                                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                            //$wh[''] = '';
                                            foreach ($warehouses as $warehouse) {
                                                if ($Owner || $Admin) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                    <?php /* } else {
                                      $warehouse_input = array(
                                      'type' => 'hidden',
                                      'name' => 'warehouse',
                                      'id' => 'slwarehouse',
                                      'value' => $this->session->userdata('warehouse_id'),
                                      );
                                      echo form_input($warehouse_input);
                                      } */ ?>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                                <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
<?php
echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("delivery_status", "sldelivery_status"); ?>
                                            <?php
                                            $Ds = '';
                                            if ($eshop_order[0]['shipping_method_name'] == 'Pickup From Store ') {
                                                $sst = array('not_applicable' => lang('Not Applicable'));
                                            } else {
                                                if ($inv->sale_status == 'delivered') {
                                                    $sst = array( 'pending' => lang('pending'), 'delivered' => lang('delivered')); //, 'partial' => lang('partial')
                                                } else {
                                                    $sst = array( 'pending' => lang('pending')); //, 'partial' => lang('partial')
                                                }
                                                if ($inv->sale_status != 'pending')
                                                    $Ds = $inv->delivery_status;
                                            }
                                            echo form_dropdown('delivery_status', $sst, $Ds, 'class="form-control input-tip" required="required" id="sldelivery_status"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
<?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually">
                                                    <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
<?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="slTable"  class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-4"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                                <?php
                                                if ($Settings->product_serial) {
                                                    //echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
                                                }
                                                ?>
                                                <th class="col-md-1"><?= lang("Unit Price") ?></th>

                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <th class="col-md-1"><?= lang("Weight"); ?></th>
                                                <th class="col-md-1"><?= lang("Net Price"); ?> </th>
    <!--                                            <th class="col-md-1 delivery_items"><?= lang("delivered"); ?></th>
                                                <th class="col-md-1 delivery_items"><?= lang("pending"); ?></th>-->
                                                <?php
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount') || $inv->product_discount)) {
                                                    echo '<th class="col-md-1">' . lang("discount") . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                    echo '<th class="col-md-1">' . lang("product_tax") . '</th>';
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
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                                <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4" style="display:none;">
                                <div class="form-group">
                                    <?= lang("order_tax", "sltax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang("select") . ' ' . lang("order_tax") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php
                        if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->order_discount_id) {
                            if ($Settings->sales_order_discount == '1') {
                                ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                <?= lang("order_discount", "sldiscount"); ?>
                                <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount" ' . (($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"')); ?>
                                    </div>
                                </div>
    <?php
    }
}
?>

                        <div class="col-md-4">
                            <div class="form-group">
<?= lang("shipping", "slshipping"); ?>
<?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("Sale_Status", "slsale_status"); ?>
                                <?php
                                if ($eshop_sale == 1) {
                                    if ($inv->sale_status == 'order_ready') {
                                        $sst = array('pending' => lang('pending'), 'order_ready' => lang('Order_ready'), 'completed' => lang('completed'), 'processing' => lang('processing'), 'cancelled' => lang('cancelled'));
                                    } else {
                                        $sst = array('' => lang('select'), 'pending' => lang('pending'), 'completed' => lang('completed'), 'processing' => lang('processing'), 'cancelled' => lang('cancelled'));
                                    }
                                } else {
                                    if ($inv->sale_status == 'order_ready') {
                                        $sst = array('pending' => lang('pending'), 'order_ready' => lang('Order_ready'), 'completed' => lang('completed'));
                                    } else {
                                        $sst = array('pending' => lang('pending'), 'completed' => lang('completed'));
                                    }
                                }
                                echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4" style="display:none;">
                            <div class="form-group">
<?= lang("payment_term", "slpayment_term"); ?>
<?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); ?>

                            </div>
                        </div>
                        <div class="col-md-4" style="display:none;">
                            <div class="form-group">
<?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
<?= lang("sale_note", "slnote"); ?>
<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
<?= lang("staff_note", "slinnote"); ?>
<?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>


                            </div>

                        </div>
                        <div class="row">
                                    <?php if ($Owner || $Admin || $GP['sales-payments']) { ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("payment_status", "slpayment_status"); ?>
    <?php
    $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
    echo form_dropdown('payment_status', $pst, $inv->payment_status, 'class="form-control input-tip" required="required" id="slpayment_status"');
    ?>

                                    </div>
                                </div>
                                <?php
                            } else {
                                echo form_hidden('payment_status', $inv->payment_status);
                            }
                            ?>
                            <input type="hidden" name="paid_amount" id="paid_amount" value="<?php echo $inv->paid; ?>">
                            <div class="clearfix"></div>
                        </div>
                        <!--<div id="payments" style="display: none;">
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
                                                    <div class="form-group ngc">
<?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1"
                                                               class="pa form-control kb-pad amount" onkeypress="return isNumberKey(event)" required="required"/>
                                                        <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                                                    </div>
                                                    <div class=" gc" style="display: none;">
<?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
                                                    </div>
                                                    <div class=" g_transaction_id" style="display: none;">
<?= lang("transaction_id", "transaction_id"); ?>
                                                        <input name="transaction_id" type="text" id="transaction_id" class="transaction_id form-control kb-pad"/>
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
                                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />--
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
                                        <div class="form-group">
<?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1"
                                                      class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>-->
                               <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', $payments[0]->reference_no, 'class="form-control tip" 
 '.($payments[0]->reference_no?'readonly' :'').' id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input type="text" name="amount-paid"  <?= ($payments[0]->amount?'readonly' :'')?>   value="<?= round($payments[0]->amount, 2) ?>"  name="amount-paid" class="pa form-control kb-pad amount" onkeypress="return isNumberKey(event)" required="required" id="amount_paid"/>
                                                        <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                                                    </div>


                                                    <div class=" gc" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($payments[0]->transaction_id) { ?>
                                                <div class="col-sm-4">
                                                    <div class=" g_transaction_id" >
                                                        <?= lang("transaction_id", "transaction_id"); ?>
                                                        <input name="transaction_id"  <?= ($payments[0]->transaction_id?'readonly' :'')?> 
 type="text"  value="<?= $payments[0]->transaction_id ?>" id="transaction_id" class="transaction_id form-control kb-pad"/>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="clearfix"></div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <?= lang("paying_by", "paid_by_1"); ?>

                                                     <!-- <input name="paid_by" id="paid_by_1" class="form-control paid_by" type="text"  value="<?= $payments[0]->paid_by ?>" />-->
                                                    <?php if ($payments[0]->paid_by) { ?>
                                                    <input name="paid_by" <?= ($payments[0]->paid_by?'readonly' :'')?> id="paid_by_1" class="form-control paid_by" type="text"  value="<?= $payments[0]->paid_by ?>" />
                                                     <?php } else { ?>
                                                        <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                            <?= $this->sma->paid_opts(); ?>
                                                        </select>    
                                                    <?php } ?>

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
                                        <div class="form-group">
                                            <?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1"
                                                      class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('edit_sale', lang("submit"), 'id="edit_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->total_discount) { ?>
                                <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
<?php } ?>
<?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
<?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

<?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                            <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
<?php } ?>
<?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
<?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
<?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
<?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cf1" class="col-sm-4 control-label"><?= 'Expiry Date' ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="cf1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cf2" class="col-sm-4 control-label"><?= 'Batch No.' ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="cf2">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                            <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
<?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
<?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="mserial" class="col-sm-4 control-label"><?= lang('product_serial') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mserial">
                            </div>
                        </div>
                            <?php } ?>
<?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="mdiscount" class="col-sm-4 control-label">
    <?= lang('product_discount') ?>
                            </label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
<?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $('#slsale_status').change(function () {
        if ($(this).val() == 'completed') {
            bootbox.alert('Marking order as completed, System will create invoice now');
        }
    });
</script>
