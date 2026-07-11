<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    //var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
        
        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
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
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        <?php } ?>
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }

    });
</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale'); ?></h2>
    </div>
<p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("sales/sale_by_csv", $attrib);

                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="<?= ($Owner || $Admin) ? 'col-md-3' : 'col-md-4'; ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
                            </div>
                        </div>
                        <?php if (!$Settings->restrict_user || $Owner || $Admin) { ?>
                            <div class="<?= ($Owner || $Admin) ? 'col-md-3' : 'col-md-4'; ?>">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="<?= ($Owner || $Admin) ? 'col-md-3' : 'col-md-4'; ?>">
                                <div class="form-group">
                                    <?= lang("warehouse", "slwarehouse"); ?>
                                    <?php
                                    $wh[''] = '';
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'slbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                            $warehouse_input = array(
                                'type' => 'hidden',
                                'name' => 'warehouse',
                                'id' => 'slwarehouse',
                                'value' => $this->session->userdata('warehouse_id'),
                            );
                            echo form_input($warehouse_input);
                        } ?>
                        </div>

                        <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 5px;"><a
                                                        href="<?= site_url('customers/add?redirect_url=' . urlencode(current_url())); ?>" id="add-customer"
                                                        class="external" data-toggle="modal" data-target="#myModal"><i
                                                            class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("Billing Address", "sl_billing_address"); ?>
                                            <input type="hidden" name="billing_address_id" id="billing_address_id" value="" />
                                            <div class="input-group">
                                                <input type="text" class="form-control input-tip" id="sl_billing_address" readonly="readonly" style="width:100%;" value="" data-tip="" />
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="edit-billing-address" class="external">
                                                        <i class="fa fa-pencil" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="#" id="add-billing-address" class="external">
                                                        <i class="fa fa-plus-circle" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("Shipping Address", "sl_shipping_address"); ?>
                                            <input type="hidden" name="shipping_address_id" id="shipping_address_id" value="" />
                                            <div class="input-group">
                                                <input type="text" class="form-control input-tip" id="sl_shipping_address" readonly="readonly" style="width:100%;" value="" data-tip="" />
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="edit-shipping-address" class="external">
                                                        <i class="fa fa-pencil" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="#" id="add-shipping-address" class="external">
                                                        <i class="fa fa-plus-circle" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        </div>

                                    <div class="modal fade" id="customerAddressModal" tabindex="-1" role="dialog" aria-labelledby="customerAddressModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                                                    <h4 class="modal-title" id="customerAddressModalLabel">Customer Addresses</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <style type="text/css">
                                                        .addr-box-row {
                                                            display: grid;
                                                            grid-template-columns: repeat(2, minmax(0, 1fr));
                                                            gap: 8px;
                                                            max-height: 180px;
                                                            overflow-y: auto;
                                                            margin-left: 0;
                                                            margin-right: 0;
                                                        }
                                                        .addr-box-row::before,
                                                        .addr-box-row::after { display: none; }
                                                        .addr-box-row > [class*="col-"] {
                                                            float: none;
                                                            width: auto;
                                                            max-width: none;
                                                            padding: 0;
                                                            margin-bottom: 0;
                                                        }
                                                        .addr-box-row > .col-sm-12:only-child { grid-column: 1 / -1; }
                                                        @media (max-width: 767px) {
                                                            .addr-box-row { grid-template-columns: 1fr; }
                                                        }
                                                        .addr-box {background:#5bc0de; color:#fff; padding:8px 8px; border-radius:2px; min-height:68px; text-align:left; position:relative; height:100%; box-sizing:border-box;}
                                                        .addr-box .addr-radio {margin-right:6px; vertical-align:middle;}
                                                        .addr-box .addr-title {font-weight:700; margin-bottom:2px; font-size:12px; opacity:0.95; display:inline-block;}
                                                        .addr-box .addr-text {font-size:12px; line-height:1.25; word-break:break-word; margin-top:4px;}
                                                        .addr-empty {padding:8px; color:#777;}
                                                    </style>
                                                    <div class="row">
                                                        <div class="col-sm-12" id="customerShippingAddressSection">
                                                            <h4 style="margin-top:0;">Shipping Address</h4>
                                                            <div id="customerShippingAddressList" class="row addr-box-row"></div>
                                                        </div>
                                                        <div class="col-sm-12" id="customerBillingAddressSection">
                                                            <h4 style="margin-top:0;">Billing Address</h4>
                                                            <div id="customerBillingAddressList" class="row addr-box-row"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                        <div class="col-md-12">
                            <div class="clearfix"></div>
                            <div class="well well-sm">
                                <?php 
                                $sample_file = ($Settings->product_batch_setting == 0)
                                    ? (($Settings->packing_size_column == 1) 
                                        ? 'sample_sale_product_old_with_packing.xlsx' 
                                        : 'sample_sale_product_old.xlsx')
                                    : (($Settings->packing_size_column == 1) 
                                        ? 'sample_sale_products_new_with_packing.xlsx' 
                                        : 'sample_sale_products_new.xlsx');
                                ?>

<a href="<?php echo $this->config->base_url(); ?>assets/mdata/<?php echo $Customer_assets; ?>/csv/<?php echo $sample_file; ?>"
   class="btn btn-primary pull-right">
    <i class="fa fa-download"></i> Download Sample File
</a>
                                <span class="text-warning"><?php echo $this->lang->line("csv1"); ?></span><br>
                                <?php echo $this->lang->line("csv2"); ?> <span
                                    class="text-info">( <?= lang("product_code") . ', ' . lang("net_unit_price") . ', ' . lang("quantity") . ', ' . lang("product_variant") . ', ' . lang("tax_rate_name") . ', ' . lang("discount") . ', ' . lang("serial_no"); ?> )</span> <?php echo $this->lang->line("csv3"); ?><br>
                                <strong><?= sprintf(lang('x_col_required'), 3); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("csv_file", "csv_file") ?>
                                <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" required="required"
                                       data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "sltax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("order_discount", "sldiscount"); ?>
                                <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("shipping", "slshipping"); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("sale_status", "slsale_status"); ?>
                                <?php $sst = array('completed' => lang('completed'), 'pending' => lang('pending'));
                                echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_term", "slpayment_term"); ?>
                                <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_status", "slpayment_status"); ?>
                                <?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'paid' => lang('paid'));
                                echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>

                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no" required="required"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1"
                                                               class="pa form-control kb-pad amount"/>
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
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
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_sale', $this->lang->line("submit"), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var $customer = $('#slcustomer');
    $customer.change(function (e) {
        localStorage.setItem('slcustomer', $(this).val());
        if (typeof autoPopulateCustomerAddresses === 'function') {
            autoPopulateCustomerAddresses();
        }
    });
    if (slcustomer = localStorage.getItem('slcustomer')) {
        $customer.val(slcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customers/getCustomer/" + $(element).val(),
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
        if (count > 1) {
            $customer.select2("readonly", true);
            $customer.val(slcustomer);
            $('#slwarehouse').select2("readonly", true);
            //$('#slcustomer_id').val(slcustomer);
        }
    } else {
        nsCustomer();
    }

// Order level shipping and discount localStorage 
if (sldiscount = localStorage.getItem('sldiscount')) {
    $('#sldiscount').val(sldiscount);
}
$('#sltax2').change(function (e) {
    localStorage.setItem('sltax2', $(this).val());
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


var old_payment_term;
$('#slpayment_term').focus(function () {
    old_payment_term = $(this).val();
}).change(function (e) {
    var new_payment_term = $(this).val() ? parseFloat($(this).val()) : 0;
    if (!is_numeric($(this).val())) {
        $(this).val(old_payment_term);
        bootbox.alert('Unexpected value provided!');
        return;
    } else {
        localStorage.setItem('slpayment_term', new_payment_term);
        $('#slpayment_term').val(new_payment_term);
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
        $(this).val(old_shipping);
        bootbox.alert('Unexpected value provided!');
        return;
    } else {
        shipping = $(this).val() ? parseFloat($(this).val()) : '0';
    }
    localStorage.setItem('slshipping', shipping);
    var gtotal = ((total + product_tax + invoice_tax) - total_discount) + shipping;
    $('#gtotal').text(formatMoney(gtotal));
});
if (slshipping = localStorage.getItem('slshipping')) {
    shipping = parseFloat(slshipping);
    $('#slshipping').val(shipping);
} else {
    shipping = 0;
}

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

        // prevent default action usln enter
$('body').bind('keypress', function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        return false;
    }
});

// Order tax calcuation 
if (site.settings.tax2 != 0) {
    $('#sltax2').change(function () {
        localStorage.setItem('sltax2', $(this).val());
        loadItems();
        return;
    });
}

// Order discount calcuation 
var old_sldiscount;
$('#sldiscount').focus(function () {
    old_sldiscount = $(this).val();
}).change(function () {
    var new_discount = $(this).val() ? $(this).val() : '0';
    if (is_valid_discount(new_discount)) {
        localStorage.removeItem('sldiscount');
        localStorage.setItem('sldiscount', new_discount);
        loadItems();
        return;
    } else {
        $(this).val(old_sldiscount);
        bootbox.alert('Unexpected value provided!');
        return;
    }

});

//$(document).on('change', '#slnote', function (e) {
        // $('#slnote').redactor('destroy');
        // $('#slnote').redactor({
        //     buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        //     formattingTags: ['p', 'pre', 'h3', 'h4'],
        //     minHeight: 100,
        //     changeCallback: function (e) {
        //         var v = this.get();
        //         localStorage.setItem('slnote', v);
        //     }
        // });
        // if (slnote = localStorage.getItem('slnote')) {
        //     $('#slnote').redactor('set', slnote);
        // }
        // $('#slinnote').redactor('destroy');
        // $('#slinnote').redactor({
        //     buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        //     formattingTags: ['p', 'pre', 'h3', 'h4'],
        //     minHeight: 100,
        //     changeCallback: function (e) {
        //         var v = this.get();
        //         localStorage.setItem('slinnote', v);
        //     }
        // });
        // if (slinnote = localStorage.getItem('slinnote')) {
        //     $('#slinnote').redactor('set', slinnote);
        // }

    });

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
</script>
<?php $this->load->view($this->theme . 'sales/sales_customer_address'); ?>
<!-- Customer Address Functionality  -->
<script>
    function buildAddressLabelFromFormData(data) {
        var bits = [];
        if (data && data.address_name) { bits.push(data.address_name); }
        if (data && data.line1) { bits.push(data.line1); }
        if (data && data.city) { bits.push(data.city); }
        if (data && data.state) { bits.push(data.state); }
        if (data && data.country) { bits.push(data.country); }
        if (data && data.postal_code) { bits.push(data.postal_code); }
        return bits.join(', ');
    }

    function pickDefaultAddress(items) {
        if (!items || !items.length) {
            return null;
        }
        for (var i = 0; i < items.length; i++) {
            if (items[i].is_default == 1 || items[i].is_default == '1') {
                return items[i];
            }
        }
        return items[0];
    }

    function setAddressFields(type, address) {
        if (!address) {
            return;
        }
        if (type == 'shipping') {
            $('#sl_shipping_address').val(address.label || '');
            $('#shipping_address_id').val(address.id || '');
        } else if (type == 'billing') {
            $('#sl_billing_address').val(address.label || '');
            $('#billing_address_id').val(address.id || '');
        }
    }

    function getCustomerAddresses(customer_id) {
        $('#sl_shipping_address').val('');
        $('#sl_billing_address').val('');
        $('#shipping_address_id').val('');
        $('#billing_address_id').val('');
        if (customer_id) {
            $.ajax({
                url: "<?= site_url('sales/get_customer_addresses'); ?>",
                type: "GET",
                data: { company_id: customer_id },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        setAddressFields('shipping', pickDefaultAddress(response.shipping));
                        setAddressFields('billing', pickDefaultAddress(response.billing));
                    }
                }
            });
        }
    }

    function autoPopulateCustomerAddresses() {
        var customer_id = getSelectedCustomerIdForAddress();
        if (!customer_id) {
            return;
        }
        getCustomerAddresses(customer_id);
    }

    function getSelectedCustomerIdForAddress() {
        var cid = localStorage.getItem('slcustomer');
        if (!cid) {
            cid = $('#slcustomer').val();
        }
        return cid;
    }

    function renderAddressBoxes(containerSelector, items, titlePrefix, selectedId) {
        var $c = $(containerSelector);
        $c.empty();
        if (!items || !items.length) {
            $c.html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
            return;
        }
        var hasSelected = false;
        $.each(items, function(i, a) {
            var t = (titlePrefix || 'Address') + ' ' + (i + 1);
            var groupName = (titlePrefix && titlePrefix.toLowerCase() === 'shipping') ? 'shipping_address_choice' : 'billing_address_choice';
            var checked = '';
            if (selectedId && String(a.id) === String(selectedId)) {
                checked = ' checked';
                hasSelected = true;
            } else if (!selectedId && !hasSelected && (a.is_default == 1 || a.is_default == '1')) {
                checked = ' checked';
                hasSelected = true;
            }
            var html = '<div class="col-xs-12 col-sm-6">' +
                '<div class="addr-box">' +
                '<label style="margin:0; cursor:pointer; display:block;">' +
                '<input type="radio" class="addr-radio" name="' + groupName + '" value="' + (a.id || '') + '"' + checked + '/>' +
                '<span class="addr-title">' + t + '</span>' +
                '</label>' +
                '<div class="addr-text">' + (a.label || '') + '</div>' +
                '</div></div>';
            $c.append(html);
        });
        if (!hasSelected && items.length) {
            $c.find('input.addr-radio:first').prop('checked', true);
        }
    }

    function openCustomerAddressPopup(address_type) {
        var customer_id = getSelectedCustomerIdForAddress();
        $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Loading...</div></div>');
        $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Loading...</div></div>');
        if (address_type == 'shipping') {
            $('#customerAddressModalLabel').text(typeof formatAddressModalTitle === 'function' ? formatAddressModalTitle('Shipping Addresses') : 'Shipping Addresses');
            $('#customerShippingAddressSection').show();
            $('#customerBillingAddressSection').hide();
        } else if (address_type == 'billing') {
            $('#customerAddressModalLabel').text(typeof formatAddressModalTitle === 'function' ? formatAddressModalTitle('Billing Addresses') : 'Billing Addresses');
            $('#customerShippingAddressSection').hide();
            $('#customerBillingAddressSection').show();
        } else {
            $('#customerAddressModalLabel').text(typeof formatAddressModalTitle === 'function' ? formatAddressModalTitle('Customer Addresses') : 'Customer Addresses');
            $('#customerShippingAddressSection').show();
            $('#customerBillingAddressSection').show();
        }
        $('#customerAddressModal').modal('show');
        if (!customer_id) {
            if (address_type == 'shipping') {
                $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Please select customer.</div></div>');
            } else if (address_type == 'billing') {
                $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Please select customer.</div></div>');
            } else {
                $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Please select customer.</div></div>');
                $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Please select customer.</div></div>');
            }
            return;
        }
        $.ajax({
            url: "<?= site_url('sales/get_customer_addresses'); ?>",
            type: "GET",
            data: { company_id: customer_id },
            dataType: "json",
            success: function(response) {
                if (response && response.success) {
                    if (address_type == 'shipping') {
                        renderAddressBoxes('#customerShippingAddressList', response.shipping, 'Shipping', $('#shipping_address_id').val());
                    } else if (address_type == 'billing') {
                        renderAddressBoxes('#customerBillingAddressList', response.billing, 'Billing', $('#billing_address_id').val());
                    } else {
                        renderAddressBoxes('#customerShippingAddressList', response.shipping, 'Shipping', $('#shipping_address_id').val());
                        renderAddressBoxes('#customerBillingAddressList', response.billing, 'Billing', $('#billing_address_id').val());
                    }
                } else {
                    $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
                    $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
                }
            },
            error: function() {
                $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Failed to load.</div></div>');
                $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Failed to load.</div></div>');
            }
        });
    }

    $(document).ready(function() {
        $(document).on('click', '#edit-shipping-address', function(e) {
            e.preventDefault();
            openCustomerAddressPopup('shipping');
        });
        $(document).on('click', '#edit-billing-address', function(e) {
            e.preventDefault();
            openCustomerAddressPopup('billing');
        });
        $(document).on('click', '#add-shipping-address', function(e) {
            e.preventDefault();
            openAddCustomerAddressModalForType('Shipping');
        });
        $(document).on('click', '#add-billing-address', function(e) {
            e.preventDefault();
            openAddCustomerAddressModalForType('Billing');
        });
        $(document).on('change', 'input[name="shipping_address_choice"]', function() {
            setAddressFields('shipping', {
                id: $(this).val(),
                label: $(this).closest('.addr-box').find('.addr-text').text()
            });
        });
        $(document).on('change', 'input[name="billing_address_choice"]', function() {
            setAddressFields('billing', {
                id: $(this).val(),
                label: $(this).closest('.addr-box').find('.addr-text').text()
            });
        });

        autoPopulateCustomerAddresses();
        setTimeout(autoPopulateCustomerAddresses, 350);
        $(document).on('change', '#slcustomer', function() {
            autoPopulateCustomerAddresses();
        });
    });
</script>
<!-- Customer Address Functionality END -->
