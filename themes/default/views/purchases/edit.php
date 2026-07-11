<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, po_edit = true, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?= $default_currency->code ?>', shipping = 0,
            product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
            tax_rates = <?php echo json_encode($tax_rates); ?>, poitems = {},
            audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
            audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(window).bind("load", function () {
<?= ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; ?>
    });
    $(document).ready(function () {
<?= ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; ?>
        $('#postatus').change(function () {
            var st = $(this).val();
            if (st == 'received' || st == 'partial') {
                $(".rec_con").show();
                $(".rquantity").attr('disabled', true);                
            } else {
                $(".rec_con").hide();
                $(".rquantity").attr('disabled', false);
            }
        });
<?php if ($inv) { ?>
            localStorage.setItem('podate', '<?= date($dateFormats['php_ldate'], strtotime($inv->date)) ?>');
            localStorage.setItem('posupplier', '<?= $inv->supplier_id ?>');
            localStorage.setItem('poref', '<?= $inv->reference_no ?>');
            localStorage.setItem('powarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('postatus', '<?= $inv->status ?>');
            localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)); ?>');
            localStorage.setItem('podiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('potax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('poshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('popayment_term', '<?= $inv->payment_term ?>');
            if (parseFloat(localStorage.getItem('potax2')) >= 1 || localStorage.getItem('podiscount').length >= 1 || parseFloat(localStorage.getItem('poshipping')) >= 1) {
                localStorage.setItem('poextras', '1');
            }
            localStorage.setItem('poitems', JSON.stringify(<?= $inv_items; ?>));
<?php } ?>

<?php if ($Owner || $Admin) { ?>
            $(document).on('change', '#podate', function (e) {
                localStorage.setItem('podate', $(this).val());
            });
            if (podate = localStorage.getItem('podate')) {
                $('#podate').val(podate);
            }
<?php } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            source: '<?= site_url('purchases/suggestions'); ?>',
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
                if (ui.item.options) {
                    product_option_model_call(ui.item);
                    $(this).val('');
                    return true;
                }
                if (ui.item.id !== 0) {
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(document).on('click', '#addItemManually', function (e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null, product = {
                type: 'standard',
                code: $('#mcode').val(),
                name: $('#mname').val(),
                tax_rate: $('#mtax').val(),
                tax_method: $('#mtax_method').val(),
                category_id: $('#mcategory').val(),
                unit: $('#munit').val(),
                cost: $('#mcost').val(),
                price: $('#mprice').val()
            };

            $.ajax({
                type: "get", async: false,
                url: site.base_url + "products/addByAjax",
                data: {token: "<?= $csrf; ?>", product: product},
                dataType: "json",
                success: function (data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });
        $(window).bind('beforeunload', function (e) {
            $.get('<?= site_url('welcome/set_data/remove_pols/1'); ?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_pruchase').click(function () {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });

    });


</script>
<style>
.btn-prni span {
    display: table-cell;
    height: 45px;
    line-height: 15px;
    vertical-align: middle;
    text-transform: uppercase;
    width: 10.5%;
    min-width: 94px;
    overflow: hidden;
    color: #000;
}

/* Set the max height and enable scrolling for the modal body */
.modal-body {

    max-height: 70vh;
    /* Adjust as needed */
    overflow-y: auto;
}

.modal-body table td {
    text-align: center;
}

.quickchange::-webkit-inner-spin-button,
.quickchange::-webkit-outer-spin-button {
    -webkit-appearance: none !important;
    margin: 0 !important;
}

.quickchange {
    -moz-appearance: textfield !important;
}

.width-setting {
    text-align: right;
    /* width: 60%; */
    border-radius: 0.3rem !important;
}

.w-50 {
    width: 50%;
}

.w50-center {
    display: flex;
    justify-content: center;
    width: 100%;
}

.table-border {
    border: 1px solid #ccc;
}

.disp-flx {
    display: flex;
    align-items: center;
    height: 6.5rem;
    justify-content: end;
}

.custom-setting {
    padding: 0.5rem;
    margin: 0.7rem;
    text-align: end;
    width: 60%;
}

.text-right {
    text-align: end;
}

.text-rightimp {
    text-align: end;
}

.cust-marginset {
    margin-top: 0.7rem;
}

.w25-center {
    width: 15%;
}

.w25-right {
    width: 20%;
}

.w-25 {
    width: 20%;
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_purchase'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form');
                echo form_open_multipart("purchases/edit/" . $inv->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['purchases-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "podate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($purchase->date)), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "poref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $purchase->reference_no), 'class="form-control input-tip" id="poref" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("warehouse", "powarehouse"); ?>
                                <?php
                                
                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                // $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    if ($Owner || $Admin) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $purchase->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("status", "postatus"); ?>
                                <?php
                                switch($purchase->status){
                                    case 'pending':
                                        $post = ['pending' => lang('pending'), 'received' => lang('received'), 'partial' => lang('partial'),  'ordered' => lang('ordered')];
                                        break;
                                    case 'ordered':
                                        $post = ['ordered' => lang('ordered'), 'received' => lang('received'), 'partial' => lang('partial'), 'pending' => lang('pending')];
                                        break;
                                    case 'partial':
                                        $post = ['received' => lang('received'), 'partial' => lang('partial')];
                                        break;
                                    case 'received':
                                        $post = ['received' => lang('received')];
                                        break;                                    
                                }//end switch                                
                                
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : $purchase->status), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" style="width:100%;" ');
                                ?>
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
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("supplier", "posupplier"); ?>
                                            <div class="input-group">
                                                <input type="hidden" name="supplier" value="" id="posupplier"  class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                            <input type="hidden" name="previous_url" value="<?php echo $_SERVER["HTTP_REFERER"]; ?>" id="previous_url" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                        <a href="products/quick_add_product" id="addManually1" class="external" data-toggle="modal" data-target="#myModal" tabindex="-1" >
                                                        <i class="fa fa-plus-circle fa-lg iconcolor" aria-hidden="true"></i>
                                                    </a></div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label>

                                <div class="controls table-controls">
                                    <table id="poTable"
                                           class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                                <th class="col-md-1"><?= lang("Variant"); ?></th>
                                                <?php
                                                if ($Settings->product_expiry) {
                                                    echo '<th class="col-md-1">' . $this->lang->line("expiry_date") . '</th>';
                                                }
                                                ?>
                                                <?php if($Settings->product_batch_setting > 0) { ?>
                                                <th class="col-md-1"><?= lang("batch_number"); ?></th>
                                                <?php } ?>
                                                <th class="col-md-1"><?= lang("unit_cost"); ?></th>
                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <th class="col-md-1 rec_con"><?= lang("received"); ?></th>
                                                <th class="col-md-1 "><?= lang("Net_Cost"); ?></th>
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
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" id="extras" value=""/>
                                <label  class="padding05"><?= lang('more_options') ?></label><!--for="extras"-->
                            </div>
                            <div class="row" id="extras-con" style="display: none;">
                                <?php if ($Settings->tax1) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('order_tax', 'potax2') ?>
                                            <?php
                                            $tx[""] = "";
                                            foreach ($tax_rates as $tax) {
                                                if($tax->is_substitutable == 0) {
                                                    $tx[$tax->id] = $tax->name;
                                                }
                                            }
                                            echo form_dropdown('order_tax', $tx, "", 'id="potax2" class="form-control input-tip select" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($Settings->purchase_order_discount == '1') { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                             <?= lang("discount_label", "podiscount"); ?> (Apply to each item)
                                            <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("shipping", "poshipping"); ?>
                                        <?php echo form_input('shipping', '', 'class="form-control input-tip" id="poshipping"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("payment_term", "popayment_term"); ?>
                                        <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group">
                                <?= lang("note", "ponote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                            <!-- GRN Section -->
                            <?php if (!empty($grn_data)) { ?>
                                <div class="row" style="margin-top:30px;">
                                    
                                    <div class="col-md-6">
                                        <h3 class="blue"><i class="fa fa-check-circle"></i> <?= lang("Goods_Received_Notes"); ?></h3>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:center; width:40px;"><?= lang("no"); ?></th>
                                                        <th style="width:120px;"><?= lang("date"); ?></th>
                                                        <th><?= lang("PO_Number"); ?></th>
                                                        <th style="width:120px; text-align:center;"><?= lang("Received_QTY"); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $grn_count = 1;
                                                    foreach ($grn_data as $grn): 
                                                    ?>
                                                    <tr>
                                                        <td style="text-align:center;"><?= $grn_count; ?></td>
                                                        <td><?= $grn->date; ?></td>
                                                        <td><?= $grn->po_number; ?></td>
                                                        <td style="text-align:center;">
                                                            <?= $this->sma->formatQuantity($grn->received_quantity); ?>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                    $grn_count++;
                                                    endforeach; 
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('edit_pruchase', $this->lang->line("submit"), 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                            <!--<td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>-->   
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
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
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

                    <div class="form-group">
                        <label class="col-sm-4 control-label"> <?= lang("tax_method", "mtax_method") ?></label>
                        <div class="col-sm-8">
                            <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, '', 'id="tax_method" class="form-control pos-input-tip" style="width:100%"');
                            ?>
                        </div>   

                    </div>  
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php } ?>

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
                    <?php if ((int) $Settings->product_batch_setting) { ?>
                        <div class="form-group">
                            <label for="pbatch_number" class="col-sm-4 control-label"><?= lang('batch_number') ?></label>
                            <div class="col-sm-8" id="batchNo_div" ></div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?= lang('calculate_unit_cost'); ?></div>
                        <div class="panel-body">

                            <div class="form-group">
                                <label for="pcost" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_cost'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="punit_cost" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_cost" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                    <input type="hidden" id="item_id" value=""/>
                    <input type="hidden" id="storage_type" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = "";
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>

                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group all">
                                <?= lang("tax_method", "mtax_method") ?>
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    
    
    setTimeout(function(){
        var st = $('#postatus').val(); 
 	if (st == 'received' || st == 'partial') {         
            $(".rquantity").attr('disabled', 'disabled');

        } else {        
            $(".rquantity").attr('disabled', false);
        }
    }, 500);
    
        
    
});

</script>
<!-- Modal Variant -->

<div class="modal  modalvarient" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="modalClose('modalvarient')" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button id="submitBtn" class="btn btn-primary">Submit</button>
                <button type="button" onclick="modalClose('modalvarient')" class="btn btn-default"
                    data-toggle="modal">Close</button>
                <!--button type="button" class="btn btn-primary" onclick="addProductToVarientProduct('modalvarient')">Save changes</button -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- End Modal Variant -->

<script>
/** Modal Variant **/
function product_option_model_call(product) {
     var ColorOption='';
		$.each(product.options_color, function (index, element) {
			ColorOption = element.name;
		});
    var product_options =
        '<table class="table table-striped table-border"><thead><tr><th>Variant Name</th><th>Quantity</th><th>Net Unit Cost</th><th>Subtotal (INR)</th></tr></thead><tbody>';

    // product.options.sort(function(a, b) {
    //     function parseNumericParts(value) {
    //         return value.split('/').map(part => parseFloat(part) || 0);
    //     }

    //     function compareNumericArrays(arr1, arr2) {
    //         const length = Math.min(arr1.length, arr2.length);
    //         for (let i = 0; i < length; i++) {
    //             if (arr1[i] < arr2[i]) return -1;
    //             if (arr1[i] > arr2[i]) return 1;
    //         }
    //         return arr1.length - arr2.length;
    //     }
    //     if (a.name && b.name) {
    //         const aParts = parseNumericParts(a.name);
    //         const bParts = parseNumericParts(b.name);

    //             return compareNumericArrays(aParts, bParts);
    //         } else if (a.name || b.name) {
    //             return a.name ? -1 : 1;
    //         } else {
    //             return (a.value || 0) - (b.value || 0);
    //         }
    //     });
        $.each(product.options, function(index, element) {
           
        var cost = element.cost;
        var unitcost =  getUnitCost();
        var formattedCost = parseFloat(cost).toFixed(2);  
        var netCost =  CalculateCost(product, unitcost,product);
        var variantRow;
        if (element.name.toLowerCase() == 'note') {
            variantRow = '<tr><td colspan="2" class="text-center">' +
                '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' + element.name +
                '\')">' +
                '<i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i> Note</button>' +
                '</td></tr>';
        } else {
            variantRow = '<tr>' +
                '<td class="w-25">' + element.name + '</td>' +
                '<td class="w25-center"><input type="number" min="0" value="0" ' +'class="form-control input-sm quantity-input quantity_input width-setting" data-variant-id="' + element.id + '" data-netcost="' + cost + '" data-initial-value="0" ></td>' +
                '<td class="w25-right net-cost">' + '<input type="number" value="' + (formattedCost) + '" ' + 'class="form-control input-sm net-cost-input net_cost_input width-setting" ' + 'data-variant-id="' + element.id + '" ' + 'data-netcost="' + cost + '" ' + 'data-initial-value="0">' +'</td>' +
                // '<td class="w25-right"><input type="text" min="0" value="' + formatMoney() + ' " ' +'class="form-control input-sm subtotal subtotal width-setting" id= "subtotals"> </td>' +
                '<td class="w25-right"><input type="text" min="0" value="' + formatMoney() + '" class="form-control input-sm subtotal subtotal width-setting" data-variant-id="' + element.id + '" id="subtotals"></td>' +
                '</tr>';
        }
        product_options += variantRow;
    });

    product_options += '</tbody></table>';
    product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.row
        .id + "' >";
    product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.row.code +
        "' >";
    var modalTitle = 'Name: ' + product.row.name;
    if (ColorOption) {
       modalTitle += '<br>Color: ' + ColorOption;
    }
    // Update modal content
    $('.modalvarient').find('.modal-title').html(modalTitle);
    $('.modalvarient').find('.modal-body').empty();
    $('.modalvarient').find('.modal-body').append(product_options);
    $('.modalvarient').show();

    return true;
}

function getUnitCost(rowNo) {
    return $('#unit_price_' + rowNo).val();
}
function CalculateCost(product, optionCost,product) {
    var productCost = product.row.cost;
    productCost = productCost == null ? 0 : productCost;
    optionCost = optionCost == null ? 0 : optionCost;
    var netCost = (parseFloat(optionCost) + parseFloat(productCost));
    return netCost;

}
$(document).on('change', '.quantity_input', function() {
    var quantity = parseFloat($(this).val());
    var variantId = $(this).data('variant-id');
    // var netCosts = parseFloat($(this).data('netcost'));
    var netCosts = parseFloat($(this).closest('tr').find('.net-cost-input').val()); 
    var netCost = quantity * netCosts;  
    $(this).closest('tr').find('.subtotal').val(formatMoney(netCost));
    $(this).closest('tr').find('.net-cost').val(formatMoney(netCosts));
});

$(document).on('change', '.net-cost-input', function() {
    var quantity = parseFloat($(this).closest('tr').find('.quantity-input').val()); 
    var netCost = parseFloat($(this).val());
    var variantId = $(this).data('variant-id'); 
    var subtotal = quantity * netCost;
    $(this).closest('tr').find('.subtotal').val(formatMoney(subtotal));
  
});

$(document).on('change', '.subtotal', function() {
    var row = $(this).closest('tr');  
    var quantity = parseFloat(row.find('.quantity-input').val());  
    var subtotalValue = $(this).val(); 
    var subtotal = parseFloat(subtotalValue.replace(/[^0-9]/g, ''));  
    var netCost = parseFloat(subtotal) / (quantity);
    $(this).closest('tr').find('.net-cost-input').val(netCost.toFixed(2));
    $(this).closest('tr').find('.subtotal').val(formatMoney(subtotal.toFixed(2)));
});
$('#submitBtn').on('click', function() {

$('.quantity-input').each(function(index) {
    setTimeout(function() {
    var $this = $(this);
    var initialValue = $this.data('initial-value');
    var currentValue = $this.val();
    var netCost = $this.closest('tr').find('.net-cost').text();
    var updatedCost = netCost.replace(/[^0-9.-]+/g, "");
    var inputValue = $this.closest('tr').find('.subtotal').val(); 

    var subtotal = inputValue.replace(/[^0-9.-]+/g, "");
    var  unitcost = parseFloat(subtotal)/parseFloat(currentValue);
        unitcost = formatDecimal(unitcost);
    if (currentValue !== '0') {
        var variantId = $this.data('variant-id');
        var quantity = currentValue;
        addProductToVarientProduct(variantId, quantity,unitcost);
        $this.data('initial-value', currentValue);
    }
}.bind(this), index * 100);
});
$('.modalvarient').hide();
});

function addProductToVarientProduct(option_id, option_name,unitcost) {
    //console.log(option_name);
    var Product_color = $('#Product_color').val();
    $('.color-form-group').removeClass('has-error')
    if (Product_color == '0') {
        $('.color-form-group').addClass('has-error')
        return false;
    }
    var note = '';
    if (option_name.toLowerCase() == 'note') {

        note = prompt("Please enter your note");
        if (note == null) {
            return false;
        }
    }
    var quantityInput = $('.modalvarient').find(`input[data-variant-id="${option_id}"]`);
    var quantity = quantityInput.length > 0 ? quantityInput.val() : 1; // Default to 1 if not found
    var itemId = $(".modalvarient").find('.product_item_id').attr("value")
    //var option_id = $(".modalvarient").find('.option_id').val();
    var term = $(".modalvarient").find('.product_term').val() + "<?php echo $this->Settings->barcode_separator; ?>" +
        option_id + "<?php echo $this->Settings->barcode_separator; ?>" + Product_color;

    wh = $('#powarehouse').val(),
        cu = $('#posupplier').val();
    $.ajax({
        type: "get",
        url: "<?= site_url('purchases/suggestions') ?>",
        data: {
            term: term,
            option_id: option_id,
            Product_color: Product_color,
            warehouse_id: wh,
            customer_id: cu,
            option_note: note,  
            quantity: quantity,
            subtotal: unitcost,
        },
        dataType: "json",
        success: function(data) {

            if (data !== null) {

                add_purchase_item(data[0]);
                $('.modalvarient').hide();
            } else {
                bootbox.alert('<?= lang('no_match_found') ?>');
                $('.modalvarient').hide();
            }
        }
    });
}

function modalClose(modalClass) {
    $('.' + modalClass).hide();
}
/** End modal Variant **/
jQuery('.cmdprint').on('click', function() {
    jQuery('#submit_type').val('print');
});

$('#print_barcode').click(function() {

    $('#print_barcode').text('<?= lang('loading'); ?>').attr('disabled', true);
    document.getElementById('add-purchase-form').submit();
});
</script>
