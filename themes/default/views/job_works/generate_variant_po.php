<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
// Clear storage when returning via browser back/forward (robust handlers)
// Redirect to RM_Calculator on page refresh
(function () {
try {
    // Check if page is being refreshed (reload navigation)
    var navEntries = (window.performance && window.performance.getEntriesByType) ? window.performance.getEntriesByType('navigation') : [];
    var navType = (navEntries && navEntries[0]) ? navEntries[0].type : '';
    var legacyIsReload = window.performance && window.performance.navigation && window.performance.navigation.type === 1; // deprecated fallback
    
    if (navType === 'reload' || legacyIsReload) {
        // Page is being refreshed - redirect to RM_Calculator
        return; // Stop further execution
    }
    
    if (sessionStorage.getItem('__vpo_should_clear') === '1') {
        // Clear only Variant PO specific keys
        var vpoKeysEarly = ['variantpoitems', 'vpo_supplied_quantities', 'vpo_note', 'vpo_raw_materials_data',
            'podiscount', 'potax2', 'poshipping', 'poref', 'powarehouse', 'ponote',
            'posupplier', 'pocurrency', 'poextras', 'podate', 'postatus', 'popayment_term'];
        for (var i = 0; i < vpoKeysEarly.length; i++) {
            try { localStorage.removeItem(vpoKeysEarly[i]); } catch (e1) {}
        }
        try { sessionStorage.removeItem('__vpo_should_clear'); } catch (e2) {}
    }
} catch (e) {}

function clearVpoDataOnly() {
    var vpoKeys = ['variantpoitems', 'vpo_supplied_quantities', 'vpo_note', 'vpo_raw_materials_data',
        'podiscount', 'potax2', 'poshipping', 'poref', 'powarehouse', 'ponote',
        'posupplier', 'pocurrency', 'poextras', 'podate', 'postatus', 'popayment_term'];
    for (var i = 0; i < vpoKeys.length; i++) {
        try { localStorage.removeItem(vpoKeys[i]); } catch (e) {}
    }
}

function isBackForwardNavigation(event) {
    try {
        var navEntries = (window.performance && window.performance.getEntriesByType) ? window.performance.getEntriesByType('navigation') : [];
        var type = (navEntries && navEntries[0]) ? navEntries[0].type : '';
        var legacyIsBack = window.performance && window.performance.navigation && window.performance.navigation.type === 2; // deprecated fallback
        return (event && event.persisted) || type === 'back_forward' || legacyIsBack;
    } catch (e) {
        return false;
    }
}

window.addEventListener('pageshow', function (event) {
    if (isBackForwardNavigation(event)) {
        clearVpoDataOnly();
    }
});

window.addEventListener('popstate', function () {
    clearVpoDataOnly();
});

window.addEventListener('pagehide', function () {
    try { sessionStorage.setItem('__vpo_should_clear', '1'); } catch (e) {}
});
})();
<?php if (!empty($variant_po_js)) { echo $variant_po_js; } ?>

<?php if ($this->session->userdata('remove_pols')) { ?>
        if (localStorage.getItem('variantpoitems')) {
            localStorage.removeItem('variantpoitems');
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
        if (localStorage.getItem('vpo_supplied_quantities')) {
            localStorage.removeItem('vpo_supplied_quantities');
        }
        if (localStorage.getItem('vpo_note')) {
            localStorage.removeItem('vpo_note');
        }
        if (localStorage.getItem('vpo_raw_materials_data')) {
            localStorage.removeItem('vpo_raw_materials_data');
        }
    <?php
    $this->sma->unset_data('remove_pols');
}
?>
<?php if ($quote_id) { ?>
        localStorage.setItem('powarehouse', '<?= $quote->warehouse_id ?>');
        localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($quote->note)); ?>');
        localStorage.setItem('podiscount', '<?= $quote->order_discount_id ?>');
        localStorage.setItem('potax2', '<?= $quote->order_tax_id ?>');
        localStorage.setItem('poshipping', '<?= $quote->shipping ?>');
    <?php if ($quote->supplier_id) { ?>
            localStorage.setItem('posupplier', '<?= $quote->supplier_id ?>');
    <?php } ?>
        localStorage.setItem('variantpoitems', JSON.stringify(<?= $quote_items; ?>));
<?php } ?>

    /**
     * LocalStorage Data Structure for Purchase Order:
     * - variantpoitems: JSON string containing all PO items with product details, quantities, prices
     * - posupplier: Selected supplier ID
     * - powarehouse: Selected warehouse ID
     * - podate: Purchase order date
     * - postatus: PO status (received/pending/ordered)
     * - podiscount: Order discount
     * - potax2: Order tax ID
     * - poshipping: Shipping cost
     * - poref: Reference number
     * - ponote: Purchase order note
     * - pocurrency: Currency code
     * - poextras: Extra options
     * - popayment_term: Payment terms
     * - vpo_raw_materials_data: JSON object with raw materials table structure {ingredients, variants, note}
     * - vpo_supplied_quantities: JSON object with supplied quantities for raw materials {rowIndex: quantity}
     * - vpo_note: Variant PO note for raw materials
     */
    var count = 1, an = 1, po_edit = false, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?= $default_currency->code ?>', shipping = 0,
            product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
            tax_rates = <?php echo json_encode($tax_rates); ?>, variantpoitems = {},
            audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
            audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
<?php if ($this->input->get('supplier')) { ?>
            if (!localStorage.getItem('variantpoitems')) {
                localStorage.setItem('posupplier', <?= $this->input->get('supplier'); ?>);
            }
<?php } ?>
<?php if ($Owner || $Admin|| $GP['purchases-date']) { ?>
            if (!localStorage.getItem('podate')) {
                $("#podate").datetimepicker({
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
            $(document).on('change', '#podate', function (e) {
                localStorage.setItem('podate', $(this).val());
            });
            if (podate = localStorage.getItem('podate')) {
                $('#podate').val(podate);
            }
<?php } ?>
        if (!localStorage.getItem('potax2')) {
            localStorage.setItem('potax2', <?= $Settings->default_tax_rate2; ?>);
            setTimeout(function () {
                $('#extras').iCheck('check');
            }, 1000);
        }
        
        
        ItemnTotals();
        $("#add_item").autocomplete({
            // source: '<?= site_url('purchases/suggestions'); ?>',
            source: function (request, response) {
                if (request.term.length >= 3) {
                    $.ajax({
                        type: 'get',
                        url: '<?= site_url('purchases/suggestions'); ?>',
                        dataType: "json",
                        data: {
                            term: request.term.trim(),
                            // warehouse_id: $("#poswarehouse").val(),
                            supplier_id: $("#posupplier").val()

                            
                        },
                        success: function(data) {
                    var exp = request.term.split("_"); // Using bacrcode Scanning
                    if (exp[1]) { //Using bacrcode Scanning
                        if (data[0].id !== 0) {
                            add_purchase_item(data[0]);
                            response('');
                            document.getElementById('add_item').value = '';
                        } else {

                            bootbox.alert('<?= lang('no_match_found') ?>', function() {
                                $('#add_item').focus();
                            });
                            $('#add_item').removeClass('ui-autocomplete-loading');
                            $('#add_item').val('');

                        }
                    } else {
                        response(data);
                    }
                }
                    });
                }
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                var inputValue = $(this).val();
                   inputval = inputValue.includes('-');
                     var formatPattern = /^\d{3}-\d+(\.\d+)?$/; // Regular expression for productcode-quantity format
                     var productEntries = inputValue.split(','); // Split by comma to get individual product entries
                  
                     var validEntries = [];

                     if (inputValue.includes('-')) {
                            // Validate each product entry
                            productEntries.forEach(function(entry) {
                                entry = entry.trim(); // Remove any extra spaces
                                if (formatPattern.test(entry)) {
                                    validEntries.push(entry);
                                }
                            });
                            if (validEntries.length > 1) {
                                var items = ui.content;
                                if (items.length > 0) {
                                    items.forEach(function(item) {
                                        ui.item = item;
                                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                                        $(this).autocomplete('close');
                                    }.bind(this)); // Use .bind(this) to maintain the correct 'this' context inside the loop
                                } else {
                                    // If no valid item found, show no match alert
                                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                                        $('#add_item').focus();
                                    });
                                    $(this).val('');
                                }
                            }
     
                        }
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
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
                    //audio_error.play();
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
                    //audio_error.play();
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
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

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

/* VPO full-width layout (additive — does not change existing rules above) */
#add-purchase-form {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
#add-purchase-form .control-group.table-group,
#add-purchase-form .table-controls {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
#add-purchase-form #poTable {
    width: 100% !important;
    max-width: 100%;
}
#add-purchase-form #bottom-total {
    max-width: 100%;
    box-sizing: border-box;
}
#add-purchase-form #bottom-total table.totals {
    width: 100%;
    table-layout: fixed;
    margin-bottom: 0;
}
#add-purchase-form #vendor-stock-partial-container,
#add-purchase-form #vpo-dynamic-container {
    max-width: 100%;
    box-sizing: border-box;
}
@media (max-width: 991px) {
  #add-purchase-form .table-controls {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i>Generate Variant PO</h2>
    </div>
    <p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">


                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form','id' => 'add-purchase-form');
                echo form_open_multipart("purchases/generate_variant_po", $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <?php if ($Owner || $Admin || $GP['purchases-date']) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("date", "podate"); ?>
                                        <!-- <?php //echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="podate" required="required"'); ?> -->
                                        <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip " id="podate" readonly required="required"'); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("reference_no", "poref"); ?>
                                    <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ponumber), 'class="form-control input-tip" readonly id="poref"'); ?>
                                </div>
                            </div>
                            <?php //if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {  ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("Delivery Location", "powarehouse"); ?>
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
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
                                    <?= lang("status", "postatus"); ?>
                                    <?php
                                    $post = array('received' => lang('received'), 'pending' => lang('pending'), 'ordered' => lang('ordered'));
                                    echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : 'ordered'), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" disabled style="width:100%;" ');
                                    echo form_hidden('status', (isset($_POST['status']) ? $_POST['status'] : 'ordered'));
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

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("Vendor", "posupplier"); ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?><div class="input-group"><?php } ?>
                                        <input type="hidden" name="supplier" value="" id="posupplier" required="required"
                                               class="form-control" style="width:100%;"
                                               placeholder="<?= lang("select") . ' ' . "Vendor" ?>">
                                        <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                               class="form-control">
                                               <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                                <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                    <i class="fa fa-2x fa-user" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                <a href="<?= site_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                    <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?></div><?php } ?>
                                </div>
                            </div>

                            


                            <div class="col-md-12" id="sticker">
                               
                            </div>

                            <div class="col-md-12">
                                <div class="control-group table-group">
                                    <label class="table-label"><?= lang("Order_items *"); ?></label>

                                    <div class="controls table-controls">
                                        <table id="poTable"
                                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                            <thead>
                                                <tr>
                                                    <th><?= lang("product_name") . " (" . $this->lang->line("Barcode") . ")"; ?></th>
                                                    <th class="col-md-1"><?= lang("Variants"); ?></th>
                                                    <th class="col-md-1"><?= lang("unit_cost"); ?></th>
                                                    <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                    <th class="col-md-1"><?= lang("Net_Cost"); ?></th>
                                                    <?php
                                                    if ($Settings->tax1) {
                                                        echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
                                                    }
                                                    ?>
                                                    <th><?= lang("subtotal"); ?> (<span
                                                            class="currency"><?= $default_currency->code ?></span>)
                                                    </th>
                                                    <!-- <th style="width: 30px !important; text-align: center;"><i
                                                            class="fa fa-trash-o"
                                                            style="opacity:0.5; filter:alpha(opacity=50);"></i></th> -->
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
                                    <label  class="padding05" for="extras"><?= lang('more_options') ?></label><!--for="extras"-->
                                </div>
                                <div class="row" id="extras-con" style="display: none;">
                                    <?php if ($Settings->tax1) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('order_tax', 'potax2') ?>
                                                <?php
                                                $tr[""] = "";
                                                foreach ($tax_rates as $tax) {
                                                    $tr[$tax->id] = $tax->name;
                                                }
                                                echo form_dropdown('order_tax', $tr, "", 'id="potax2" class="form-control input-tip select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Settings->purchase_order_discount == '1') { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("discount_label", "podiscount"); ?>
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
                                <!-- <div class="form-group">
                                    <?= lang("note", "ponote"); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div> -->

                            </div>
                            
                            <?php if (!empty($vpo_ingredients)) { ?>
                            <script type="text/javascript">
                            // Save raw materials data to localStorage
                            (function(){
                                var rawMaterialsData = {
                                    ingredients: <?= json_encode($vpo_ingredients) ?>,
                                    variants: <?= json_encode($vpo_variants) ?>,
                                    note: <?= json_encode(isset($vpo_note) ? $vpo_note : '') ?>
                                };
                                localStorage.setItem('vpo_raw_materials_data', JSON.stringify(rawMaterialsData));
                            })();
                            </script>
                            <?php } ?>
                            
                            <!-- Container for dynamically rendered raw materials table -->
                            <div id="vpo-dynamic-container" style ="margin-left: 1%;"></div>
                          
                            <div class="col-md-12">
                            <input type="hidden" name="submit_type" id="submit_type" value="">
                                <div class="from-group">
                                    <?php //echo form_submit('add_pruchase', $this->lang->line("submit"), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                    <?php echo form_submit('add_pruchase', lang("Generate PO"), 'id="add_pruchase" class="btn btn-primary final-btn" style="padding: 6px 15px; margin:15px 0;" disabled'); ?>
                                    <!-- <button type="button" class="btn btn-info cmdprint final-btn" name="cmdprint"
                                        style="padding: 6px 15px; margin:15px 0;" id="print_barcode">Submit & Print
                                        Barcode</button> -->
                                    <!-- <button type="button" class="btn btn-info cmdprint final-btn" name="cmdprint"
                                        style="padding: 6px 15px; margin:15px 0;" id="add_pruchase">Submit & Print
                                        </button>
                                    <button type="button" class="btn btn-danger"
                                        id="reset"><?= lang('reset') ?></button> -->
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

                <div id="vendor-stock-partial-container">
                    <?php 
                    if (!empty($vendor_stock_details)) {
                        $this->load->view($this->theme . 'job_works/vendor_stock_partial', ['vendor_stock_details' => $vendor_stock_details]); 
                    }
                    ?>
                </div>

                <script type="text/javascript">
                (function(){
                    var __dummyLoaded = false;
                    function tryLoad() {
                        if (__dummyLoaded) return;
                        // Wait until dummyItems is defined
                        if (!window.__dummyItems || !Array.isArray(window.__dummyItems) || window.__dummyItems.length === 0) {
                            return setTimeout(tryLoad, 200);
                        }
                        // Previously required supplier selection before loading items.
                        // Now we allow auto-loading without supplier selection.
                        __dummyLoaded = true;
                        var reqs = [];
                        var __resetQtyOnReload = !!window.__isVpoReload; // set from top refresh detector
                        window.__dummyItems.forEach(function(it){
                            var qtyFetch = it.qty || 1; // for API
                            reqs.push($.getJSON('<?= site_url('purchases/item_details'); ?>', {
                                product_id: it.product_id || 0,
                                variant_id: it.variant_id || 0,
                                tax_id: it.tax_id || '',
                                qty: qtyFetch,
                                supplier_id: localStorage.getItem('posupplier') || 0
                            }).then(function(resp){
                                if (resp && resp.status === 'success' && resp.item) {
                                    var p = resp.item;
                                    var row = p.row || {};
                                    // Fill required fields to avoid undefined
                                    var initQty = __resetQtyOnReload ? 0 : qtyFetch;
                                    row.base_quantity = initQty;
                                    row.qty = initQty;
                                    row.base_unit = row.unit;
                                    row.unit_lable = (p.units && p.units.length) ? p.units[0].name : '';
                                    row.discount = '0';
                                    // Provide a minimal options array if product is packed, so variant name shows
                                    var optionsArr = false;
                                    if (row.storage_type === 'packed' && row.option) {
                                        optionsArr = [{ id: row.option, name: (p.option_name || 'Variant') }];
                                    }
                                    var item_id = '' + row.id + (row.option || 0);
                                    var label = p.name + ' (' + p.code + ')';
                                    var pr = {
                                        id: Date.now(),
                                        item_id: item_id,
                                        image: p.image_url || '',
                                        label: label,
                                        row: row,
                                        tax_rate: p.tax_rate || null,
                                        units: p.units || [],
                                        options: optionsArr,
                                        option_batches: false,
                                        batchs: false,
                                        primary_variant: 0,
                                        options_color: []
                                    };
                                    // De-duplicate: scan existing variantpoitems for same product and variant
                                    var existingStr = localStorage.getItem('variantpoitems');
                                    var exists = false;
                                    if (existingStr) {
                                        try {
                                            var existingObj = JSON.parse(existingStr) || {};
                                            $.each(existingObj, function(k, v){
                                                if (v && v.row && v.row.id == row.id && (v.row.option || 0) == (row.option || 0)) {
                                                    exists = true; return false;
                                                }
                                            });
                                        } catch(e) { exists = false; }
                                    }
                                    if (!exists) { add_purchase_item(pr); }
                                }
                            }));
                        });
                        $.when.apply($, reqs).then(function(){});
                    }
                    $(document).ready(function(){
                        tryLoad();
                        
                        // Validate supplier and toggle Generate PO button
                        function validateSupplierAndToggleButton() {
                            var supplierId = $('#posupplier').val();
                            
                            if (!supplierId || supplierId.trim() === '') {
                                $('#add_pruchase').prop('disabled', true).addClass('disabled');
                                return;
                            }
                            
                            checkSupplierLocation(supplierId, function(hasLocation, supplier) {
                                if (hasLocation) {
                                    $('#add_pruchase').prop('disabled', false).removeClass('disabled');
                                } else {
                                    $('#add_pruchase').prop('disabled', true).addClass('disabled');
                                    var supplierName = supplier ? (supplier.name || supplier.company || 'Unknown') : 'Unknown';
                                    alert('⚠️ Vendor Location Required\n\nThe selected vendor "' + supplierName + '" does not have a location/address set.\n\nPlease add a location to this vendor before generating a Purchase Order.');
                                }
                            });
                        }
                        
                        // Validate on page load
                        setTimeout(validateSupplierAndToggleButton, 500);
                        
                        // Validate when supplier changes
                        $('#posupplier').on('change', function(){ 
                            __dummyLoaded = false; 
                            tryLoad(); 
                            validateSupplierAndToggleButton();
                        });
                        
                        $('.pcalculate').on('change', function(){
        
        calculateCost();
        
    });
    // Make supplier readonly/non-editable on this page
    try { 
        // Disable the select2 dropdown to make it non-clickable
        $('#posupplier').prop('disabled', true);
        
        // Make the select2 container visually disabled but keep form value submittable
        var $select2Container = $('#posupplier').next('.select2-container');
        $select2Container.find('.select2-selection').css({
            'background-color': '#e9ecef',
            'cursor': 'not-allowed',
            'pointer-events': 'none',
            'opacity': '0.65'
        });
        
        // Re-enable just before form submit so value is posted
        $('#add-purchase-form').on('submit', function() {
            $('#posupplier').prop('disabled', false);
        });
    } catch (e) {}
    
});
                })();
                </script>

                <script type="text/javascript">
                // Check if supplier has location via AJAX
                function checkSupplierLocation(supplierId, callback) {
                    if (!supplierId) {
                        callback(false, null);
                        return;
                    }
                    
                    $.ajax({
                        type: 'GET',
                        url: '<?= site_url('Production_Unit/getCompanyByID'); ?>/' + supplierId,
                        dataType: 'json',
                        success: function(response) {
                            if (response.error) {
                                // Backend validation failed - no location
                                callback(false, response.supplier || null);
                            } else {
                                // Success - supplier has location
                                callback(true, response.supplier || null);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Supplier validation error:', error);
                            callback(false, null);
                        }
                    });
                }
                
                // Apply standard formatting using core.js helpers
                $(function(){
                    // Function to rebuild raw materials table from localStorage
                    function rebuildRawMaterialsTable() {
                        // Check if table already exists
                        if ($('.vpo-table').length > 0) {
                            return; // Table already rendered by PHP
                        }
                        
                        var savedData = localStorage.getItem('vpo_raw_materials_data');
                        if (!savedData) {
                            return; // No saved data
                        }
                        
                        try {
                            var data = JSON.parse(savedData);
                            if (!data.ingredients || !data.variants) {
                                return;
                            }
                            
                            console.log('Rebuilding table with data:', data);
                            console.log('Ingredients:', data.ingredients);
                            
                            // Build the table HTML
                            var html = '<style>' +
                                '.vpo-table-title { font-weight:600; margin: 15px 0 8px; }' +
                                '.vpo-table-wrap { max-width: 100%; overflow-x: auto; }' +
                                '.vpo-table { width: auto; min-width: 600px; table-layout: fixed; border-collapse: collapse; }' +
                                '.vpo-table th, .vpo-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }' +
                                '.vpo-table th { background-color: #007bff; color: #fff; }' +
                                '.vpo-sticky { position: sticky; left: 0; z-index: 2; }' +
                                '.vpo-material { font-weight: bold; text-align: left; }' +
                                '.vpo-supplied.error { border-color: #dc3545 !important; background-color: #fff5f5; }' +
                                '.vpo-error-msg { font-size: 11px; margin-top: 3px; font-weight: 500; }' +
                                '.vpo-supplied::-webkit-outer-spin-button, .vpo-supplied::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }' +
                                '.vpo-supplied[type=number] { -moz-appearance: textfield; }' +
                                '.vpo-transfer-qty::-webkit-outer-spin-button, .vpo-transfer-qty::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }' +
                                '.vpo-transfer-qty[type=number] { -moz-appearance: textfield; }' +
                                '</style>';
                            
                            html += '<div class="vpo-table-title" >Raw Materials Supplied</div>';
                            html += '<div class="vpo-table-wrap"><table class="vpo-table"><thead><tr>';
                            html += '<th class="vpo-sticky" style="width: 16%; background-color: #40b960 !important;">Raw Material</th>';
                            html += '<th style="width:90px;">Total Req</th>';
                            html += '<th style="width:100px; background-color:rgb(105, 145, 189);">Vendor Stock</th>';
                            html += '<th style="width:100px;">Additional Transfer</th>';
                            html += '<th style="width:100px;">Our Total Stock</th>';
                            html += '<th style="width:120px;">Our Locations</th>';
                            html += '<th style="width:80px;">Stock</th>';
                            html += '<th style="width:100px; background-color:rgb(105, 145, 189);">Transfer Qty</th>';
                            html += '<th style="width:100px; background-color:rgb(105, 145, 189);">Total Supplied</th>';
                            html += '</tr></thead><tbody>';
                            
                            // Add ingredient rows
                            $.each(data.ingredients, function(rm_i, rm){
                                var materialId = rm.raw_material_id || rm.product_id || 0;
                                
                                // Display raw material with unit: e.g., "Threads (Meter)"
                                var materialName = rm.raw_material || '-';
                                // Check both unit_name and unit for compatibility
                                var unitName = rm.unit_name || rm.unit;
                                
                                // Debug: log if unit is missing
                                if (!unitName || unitName === '') {
                                    console.log('Missing unit for material:', rm.raw_material, 'Data:', rm);
                                }
                                
                                if (unitName && unitName !== '') {
                                    materialName += ' (' + unitName + ')';
                                }
                                
                                // Calculate total required quantity
                                // Logic: Sum of (base_qty_from_BOM × order_qty_for_each_selected_variant)
                                // Uses quantities_by_variant if present to avoid index mismatches.
                                var total_req = 0.0;
                                if (data.variants && data.variants.length > 0) {
                                    data.variants.forEach(function(v, idx) {
                                        var baseQty = 0.0;
                                        if (rm.quantities_by_variant && typeof rm.quantities_by_variant[v.id] !== 'undefined') {
                                            baseQty = parseFloat(rm.quantities_by_variant[v.id]) || 0.0;
                                        } else if (rm.quantities && rm.quantities.length > idx) {
                                            baseQty = parseFloat(rm.quantities[idx]) || 0.0;
                                        }
                                        var orderQty = v.required_quantity ? parseFloat(v.required_quantity) : 1.0;
                                        if (isNaN(orderQty)) { orderQty = 0.0; }
                                        var calculated = Math.round(baseQty * orderQty * 100) / 100;
                                        total_req += calculated;
                                    });
                                }
                                
                                // Main summary row
                                html += '<tr class="material-main-row" data-material-id="' + materialId + '">';
                                html += '<td class="vpo-sticky vpo-material" rowspan="1"style="background-color: #40b960 !important;" data-material-id="' + materialId + '">' + materialName + '</td>';
                                html += '<td rowspan="1"><span class="js-format-qty vpo-total-req" data-material-id="' + materialId + '">' + total_req + '</span>';
                                html += '<input type="hidden" name="vpo_request[' + rm_i + ']" value="' + total_req + '" /></td>';
                                html += '<input type="hidden" name="vpo_material_unit[' + rm_i + ']" value="' + (unitName || '') + '" /></td>';
                                html += '<td rowspan="1"><span class="js-format-qty vpo-supplier-qty" data-material-id="' + materialId + '">-</span></td>';
                                
                                // Additional Transfer Calculation Cell
                                html += '<td rowspan="1"><span class="js-format-qty vpo-additional-calc" data-material-id="' + materialId + '">0.00</span></td>';
                                
                                html += '<td rowspan="1"><span class="js-format-qty vpo-our-total-stock" data-material-id="' + materialId + '">-</span></td>';
                                html += '<td colspan="3" class="vpo-locations-placeholder" data-material-id="' + materialId + '" style="text-align:left; padding:5px;">Loading locations...</td>';
                                
                                html += '<td rowspan="1">';
                                html += '<span class="vpo-total-supplied" data-material-id="' + materialId + '">0</span>';
                                html += '<input type="hidden" name="vpo_supplied[' + rm_i + ']" class="vpo-supplied-hidden" value="0" data-material-id="' + materialId + '" />';
                                html += '<input type="hidden" name="vpo_material[' + rm_i + ']" value="' + (rm.raw_material || '-') + '" />';
                                html += '<input type="hidden" name="vpo_material_id[' + rm_i + ']" value="' + materialId + '" />';
                                html += '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody></table></div>';
                            
                            // Add note section
                            var default_note = 'Please find attached raw materials details supplied along with this PO. There is a seperate delivery challan attached with this PO.';
                            var bom_prefix = '<strong>Bill Of Materials Note:</strong> ';
                            var note_content = data.note ? (bom_prefix + data.note + '\n\n' + default_note) : default_note;
                            
                            html += '<div class="form-group" style="margin-top:12px;">';
                            html += '<label style="font-weight:bold;">Note</label>';
                            html += '<textarea class="form-control" rows="2" id="vpo_note" name="vpo_note">' + note_content + '</textarea>';
                            html += '</div>';
                            
                            // Insert into container
                            $('#vpo-dynamic-container').html(html);
                            
                            // Trigger calculation
                            if (typeof window.updateAdditionalTransferCalculations === 'function') {
                                window.updateAdditionalTransferCalculations();
                            }
                            
                        } catch(e) {
                            console.log('Error rebuilding raw materials table:', e);
                        }
                    }
                    
                    
                    // Load VPO Note from localStorage
                    function loadVPONote() {
                        var savedNote = localStorage.getItem('vpo_note');
                        if (savedNote) {
                            $('#vpo_note').val(savedNote);
                        }
                    }
                    
                    // Save VPO Note to localStorage
                    function saveVPONote() {
                        var note = $('#vpo_note').val();
                        if (note) {
                            localStorage.setItem('vpo_note', note);
                        }
                    }
                    
                    // Calculation for Additional Transfer Column
                    window.updateAdditionalTransferCalculations = function() {
                        var supplierId = $('#posupplier').val();
                        // console.log('Calculating Additional Transfer. SupplierID:', supplierId);

                        $('.vpo-additional-calc').each(function() {
                            var $el = $(this);
                            var mid = $el.data('material-id');
                            var totalReq = parseFloat($('.vpo-total-req[data-material-id="' + mid + '"]').text().replace(/,/g, '') || 0);
                            var toSupply = 0;

                            if (supplierId && window.vendorStockData && Array.isArray(window.vendorStockData)) {
                                for (var i = 0; i < window.vendorStockData.length; i++) {
                                    // Robust comparison
                                    var dataSupId = window.vendorStockData[i].supplier_id;
                                    var dataProdId = window.vendorStockData[i].product_id;
                                    
                                    if (dataSupId == supplierId && dataProdId == mid) {
                                        toSupply = parseFloat(window.vendorStockData[i].to_supply);
                                        // console.log('Match found for RM:', mid, 'ToSupply:', toSupply);
                                        break; 
                                    }
                                }
                            }
                            
                            if (isNaN(toSupply)) toSupply = 0;
                            var val = totalReq + toSupply;
                            $el.text(formatDecimal(val));
                        });
                    };
                    
                    // Provide requested helper: localstorage.clearall()
                    // Clears only Variant PO related keys from localStorage
                    (function(){
                        window.localstorage = window.localstorage || {};
                        window.localstorage.clearall = function(){
                            var vpoKeys = ['variantpoitems', 'vpo_supplied_quantities', 'vpo_note', 'vpo_raw_materials_data', 
                                          'podiscount', 'potax2', 'poshipping', 'poref', 'powarehouse', 'ponote', 
                                          'posupplier', 'pocurrency', 'poextras', 'podate', 'postatus', 'popayment_term'];
                            vpoKeys.forEach(function(key){
                                try { localStorage.removeItem(key); } catch(e) {}
                            });
                        };
                    })();
                    
                    // Rebuild table if needed (must be called before loading data)
                    rebuildRawMaterialsTable();
                    
                    // NEW: Reload Vendor Stock Table if missing (on refresh)
                    if ($('#vendorStockTable').length === 0) {
                        var savedData = localStorage.getItem('vpo_raw_materials_data');
                        if (savedData) {
                            try {
                                var data = JSON.parse(savedData);
                                var rm_ids = [];
                                
                                if (data.ingredients) {
                                    $.each(data.ingredients, function(i, rm){
                                        var mid = rm.raw_material_id || rm.product_id;
                                        if (mid) rm_ids.push(mid);
                                    });
                                }
                                
                                if (rm_ids.length > 0) {
                                    // Read saved supplier id from localStorage (key used by posupplier dropdown)
                                    var savedSupplierId = localStorage.getItem('posupplier') || $('#posupplier').val() || '';
                                    
                                    $('#vendor-stock-partial-container').html('<div class="box" style="margin-top:15px; padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i> Loading Vendor Stock Details...</div>');
                                    
                                    $.ajax({
                                        type: 'POST',
                                        url: '<?= site_url('purchases/get_vendor_stock_table'); ?>',
                                        data: {
                                            rm_ids: rm_ids,
                                            supplier_id: savedSupplierId,
                                            <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                                        },
                                        success: function(html) {
                                            $('#vendor-stock-partial-container').html(html);
                                        }
                                    });
                                }
                            } catch(e) { console.error(e); }
                        }
                    }
                    
                    // Function to load our (non-vendor) locations for all materials
                    function loadVendorLocationData() {
                        $('.vpo-locations-placeholder').each(function() {
                            var $placeholder = $(this);
                            var materialId = $placeholder.data('material-id');
                            var $mainRow = $placeholder.closest('tr');
                            var variantColsCount = $mainRow.find('td').length - 8; // Total cols minus fixed cols
                            
                            if (!materialId || materialId == 0) {
                                $placeholder.html('<em style="color:#999;">No material ID</em>');
                                return;
                            }
                            
                            // Fetch location stock data for our locations (non-vendor)
                            $.ajax({
                                type: 'GET',
                                url: '<?= site_url('Production_Unit/getStockByOurLocations'); ?>',
                                data: { product_id: materialId },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.error) {
                                        $placeholder.html('<em style="color:#dc3545;">' + (response.message || 'Error loading') + '</em>');
                                        return;
                                    }
                                    
                                    var locations = response.locations || [];
                                    var totalStock = response.total_stock || 0;
                                    
                                    // Update Our Total Stock column
                                    $('.vpo-our-total-stock[data-material-id="' + materialId + '"]').text(formatDecimal(totalStock));
                                    
                                    if (locations.length === 0) {
                                        $placeholder.html('<em style="color:#999;">No locations found</em>');
                                        return;
                                    }
                                    
                                    // Update rowspan for main row cells
                                    var rowspan = locations.length;
                                    $mainRow.find('td[rowspan]').attr('rowspan', rowspan);
                                    
                                    // Build location rows
                                    var locationRowsHTML = '';
                                    $.each(locations, function(idx, loc) {
                                        if (idx === 0) {
                                            // First location - replace placeholder
                                            $placeholder.replaceWith(
                                                '<td>' + loc.name + '</td>' +
                                                '<td><span class="js-format-qty">' + formatDecimal(loc.quantity) + '</span></td>' +
                                                '<td><input type="number" step="1" min="0" max="' + loc.quantity + '" ' +
                                                'class="form-control input-sm vpo-transfer-qty" ' +
                                                'name="vpo_transfer_qty[' + materialId + '][' + loc.id + ']" ' +
                                                'data-material-id="' + materialId + '" ' +
                                                'data-location-id="' + loc.id + '" value="0" ' +
                                                'style="width:100px;text-align:center;margin-top:-5px;margin-bottom:-2px;-webkit-appearance:none;-moz-appearance:textfield;" ' +
                                                'onwheel="return false;" />' +
                                                '<input type="hidden" name="vpo_transfer_location[' + materialId + '][' + idx + ']" value="' + loc.id + '" />' +
                                                '</td>'
                                            );
                                        } else {
                                            // Additional locations - insert as new rows
                                            locationRowsHTML += '<tr class="material-location-row" data-material-id="' + materialId + '">';
                                            locationRowsHTML += '<td>' + loc.name + '</td>';
                                            locationRowsHTML += '<td><span class="js-format-qty">' + formatDecimal(loc.quantity) + '</span></td>';
                                            locationRowsHTML += '<td><input type="number" step="1" min="0" max="' + loc.quantity + '" ';
                                            locationRowsHTML += 'class="form-control input-sm vpo-transfer-qty" ';
                                            locationRowsHTML += 'name="vpo_transfer_qty[' + materialId + '][' + loc.id + ']" ';
                                            locationRowsHTML += 'data-material-id="' + materialId + '" ';
                                            locationRowsHTML += 'data-location-id="' + loc.id + '" value="0" ';
                                            locationRowsHTML += 'style="width:100px;text-align:center;margin-top:-5px;margin-bottom:-2px;-webkit-appearance:none;-moz-appearance:textfield;" ';
                                            locationRowsHTML += 'onwheel="return false;" />';
                                            locationRowsHTML += '<input type="hidden" name="vpo_transfer_location[' + materialId + '][' + idx + ']" value="' + loc.id + '" />';
                                            locationRowsHTML += '</td>';
                                            locationRowsHTML += '</tr>';
                                        }
                                    });
                                    
                                    // Insert additional location rows after main row
                                    if (locationRowsHTML) {
                                        $mainRow.after(locationRowsHTML);
                                    }
                                    
                                    // Format quantity displays
                                    $mainRow.nextAll('.material-location-row[data-material-id="' + materialId + '"]').find('.js-format-qty').each(function(){
                                        var raw = ($(this).text()||'').toString().replace(/,/g,'');
                                        var n = parseFloat(raw);
                                        if (!isNaN(n)) {
                                            $(this).text(formatDecimal(n));
                                        }
                                    });
                                    
                                    // Bind change event to calculate total supplied
                                    bindTransferQtyChange();
                                },
                                error: function() {
                                    $placeholder.html('<em style="color:#dc3545;">Failed to load</em>');
                                }
                            });
                        });
                    }
                    
                    // Bind change event for transfer quantity inputs
                    function bindTransferQtyChange() {
                        $('.vpo-transfer-qty').off('input change').on('input change', function() {
                            var $input = $(this);
                            var materialId = $input.data('material-id');
                            var maxQty = parseFloat($input.attr('max')) || 0;
                            var enteredQty = parseFloat($input.val()) || 0;
                            
                            // Validate: Transfer quantity cannot exceed available stock
                            if (enteredQty > maxQty) {
                                $input.css('border-color', '#dc3545'); // Red border
                                $input.val(maxQty); // Auto-correct to max available stock
                                
                                // Show alert
                                alert('Transfer quantity cannot exceed available stock (' + formatDecimal(maxQty) + ') at this location.');
                                
                                enteredQty = maxQty;
                            } else if (enteredQty < 0) {
                                $input.val(0);
                                enteredQty = 0;
                            } else {
                                $input.css('border-color', ''); // Reset border color
                            }
                            
                            // Sum all transfer quantities for this material
                            var total = 0;
                            $('.vpo-transfer-qty[data-material-id="' + materialId + '"]').each(function() {
                                var val = parseFloat($(this).val()) || 0;
                                total += val;
                            });
                            
                            // Update total supplied display and hidden input
                            $('.vpo-total-supplied[data-material-id="' + materialId + '"]').text(formatDecimal(total));
                            $('.vpo-supplied-hidden[data-material-id="' + materialId + '"]').val(total);
                        });
                    }
                    
                    // Helper function for decimal formatting
                    function formatDecimal(num) {
                        if (isNaN(num) || num === null || num === undefined) return '0.00';
                        return parseFloat(num).toFixed(2);
                    }
                    
                    // Function to fetch and populate availability quantities
                    function loadAvailabilityData() {
                        var supplierId = $('#posupplier').val();
                        var deliveryWarehouseId = $('#powarehouse').val();
                        
                        // Collect all material IDs
                        var materialIds = [];
                        $('.vpo-supplier-qty').each(function(){
                            var materialId = $(this).data('material-id');
                            if (materialId && materialId > 0) {
                                materialIds.push(materialId);
                            }
                        });
                        
                        if (materialIds.length === 0) return;
                        
                        // Fetch availability data via AJAX
                        $.ajax({
                            type: 'POST',
                            url: '<?= site_url('purchases/get_material_availability'); ?>',
                            data: {
                                <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>',
                                material_ids: materialIds,
                                supplier_id: supplierId,
                                delivery_warehouse_id: deliveryWarehouseId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response && response.success) {
                                    // Update Vendor Stock column for each material
                                    $.each(response.data, function(materialId, data){
                                        var supplierQty = parseFloat(data.supplier_qty || 0);
                                        
                                        // Update Vendor Stock display column
                                        $('.vpo-supplier-qty[data-material-id="' + materialId + '"]').text(formatDecimal(supplierQty));
                                    });
                                }
                            },
                            error: function() {
                                // On error, show 0 instead of -
                                $('.vpo-supplier-qty').each(function(){
                                    if ($(this).text() === '-') {
                                        $(this).text('0.00');
                                    }
                                });
                            }
                        });
                    }
                    
                    // Format Request Qty text using formatDecimal (no wrapper div)
                    setTimeout(function(){
                        $('.js-format-qty').each(function(){
                            var raw = ($(this).text()||'').toString().replace(/,/g,'');
                            var n = parseFloat(raw);
                            if (isNaN(n)) n = 0;
                            $(this).text(formatDecimal(n));
                        });
                        
                        // Load our location data first (non-vendor warehouses)
                        loadVendorLocationData();
                        
                        // Load availability data immediately after formatting
                        loadAvailabilityData();
                    }, 100);
                    
                    // Load saved VPO Note only (not supplied quantities - those are auto-calculated)
                    setTimeout(function(){
                        loadVPONote();
                    }, 200);
                    
                    // Reload availability and location data when supplier or warehouse changes
                    $('#posupplier, #powarehouse').on('change', function(){
                        loadVendorLocationData(); // Loads our (non-vendor) locations
                        loadAvailabilityData();
                        if (typeof window.updateAdditionalTransferCalculations === 'function') {
                            window.updateAdditionalTransferCalculations();
                        }
                        // Reload Vendor Stock Details for newly selected supplier
                        reloadVendorStockTable();
                    });
                    
                    // Reload Vendor Stock Details table for selected supplier
                    function reloadVendorStockTable() {
                        var supplierId = $('#posupplier').val();
                        if (!supplierId) {
                            // No supplier selected — clear vendor stock section
                            $('#vendor-stock-partial-container').html('');
                            return;
                        }
                        
                        // Collect material IDs from the raw materials table
                        var rm_ids = [];
                        $('.vpo-total-req').each(function() {
                            var mid = $(this).data('material-id');
                            if (mid && mid > 0) rm_ids.push(mid);
                        });
                        
                        if (rm_ids.length === 0) return;
                        
                        $('#vendor-stock-partial-container').html('<div class="box" style="margin-top:15px; padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i> Loading Vendor Stock Details...</div>');
                        
                        $.ajax({
                            type: 'POST',
                            url: '<?= site_url('purchases/get_vendor_stock_table'); ?>',
                            data: {
                                rm_ids: rm_ids,
                                supplier_id: supplierId,
                                <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                            },
                            success: function(html) {
                                $('#vendor-stock-partial-container').html(html);
                            },
                            error: function() {
                                $('#vendor-stock-partial-container').html('<div class="alert alert-danger">Failed to load vendor stock details.</div>');
                            }
                        });
                    }
                    
                    // Validate supplied quantities and toggle button
                    function checkAllSuppliedQtyAndToggleButton() {
                        var hasError = false;
                        
                        $('.vpo-supplied').each(function(){
                            var suppliedQty = parseFloat(($(this).val() || '').toString().replace(/,/g, ''));
                            
                            if (isNaN(suppliedQty)) suppliedQty = 0;
                            
                            // Only check for negative values
                            if (suppliedQty < 0) {
                                hasError = true;
                                return false; // Break loop
                            }
                        });
                        
                        if (hasError) {
                            $('#add_pruchase').prop('disabled', true).addClass('disabled');
                            return;
                        }
                        
                        // Check supplier location if quantities are valid
                        var supplierId = $('#posupplier').val();
                        if (!supplierId || supplierId.trim() === '') {
                            $('#add_pruchase').prop('disabled', true).addClass('disabled');
                            return;
                        }
                        
                        checkSupplierLocation(supplierId, function(hasLocation) {
                            $('#add_pruchase').prop('disabled', !hasLocation).toggleClass('disabled', !hasLocation);
                        });
                    }
                    
                    // Validation function for Supplied Qty
                    function validateSuppliedQty($input) {
                        var raw = ($input.val()||'').toString().replace(/,/g,'');
                        var suppliedQty = parseFloat(raw);
                        var $errorMsg = $input.closest('td').find('.vpo-error-msg');
                        
                        // Clear previous error states
                        $input.removeClass('error warning');
                        $errorMsg.hide().text('');
                        
                        // Check if value is valid number
                        if (isNaN(suppliedQty)) {
                            suppliedQty = 0;
                        }
                        
                        // Validation checks - only check for negative values
                        if (suppliedQty < 0) {
                            $input.addClass('error');
                            $errorMsg.text('Supplied Qty cannot be negative').show();
                            return false;
                        }
                        
                        return true;
                    }
                    
                    
                    // Format Supplied Qty input values to site qty decimals (initial)
                    setTimeout(function(){
                        $('.vpo-supplied').each(function(){
                            var raw = ($(this).val()||'').toString().replace(/,/g,'');
                            var n = parseFloat(raw);
                            if (isNaN(n)) n = 0;
                            $(this).val(formatDecimal(n));
                        });
                    }, 250);
                    
                    // Use event delegation for dynamically added elements
                    $(document).on('input change', '.vpo-supplied', function(){
                        validateSuppliedQty($(this));
                        checkAllSuppliedQtyAndToggleButton();
                    });
                    
                    $(document).on('blur', '.vpo-supplied', function(){
                        var raw = ($(this).val()||'').toString().replace(/,/g,'');
                        var n = parseFloat(raw);
                        if (isNaN(n)) n = 0;
                        $(this).val(formatDecimal(n));
                        validateSuppliedQty($(this));
                        checkAllSuppliedQtyAndToggleButton();
                    });
                    
                    // Save VPO Note when it changes (using event delegation)
                    $(document).on('change blur', '#vpo_note', function(){
                        saveVPONote();
                    });
                    
                    // Initial check on page load
                    setTimeout(function(){
                        checkAllSuppliedQtyAndToggleButton();
                    }, 600);
                    
                    // Validate all fields before form submission
                    $('#add-purchase-form').on('submit', function(e){
                        var hasError = false;
                        var transferError = false;
                        
                        // Validate transfer quantities don't exceed stock
                        $('.vpo-transfer-qty').each(function(){
                            var $input = $(this);
                            var maxQty = parseFloat($input.attr('max')) || 0;
                            var enteredQty = parseFloat($input.val()) || 0;
                            
                            if (enteredQty > maxQty) {
                                $input.css('border-color', '#dc3545');
                                transferError = true;
                            }
                        });
                        
                        if (transferError) {
                            bootbox.alert('Transfer quantity cannot exceed available stock at any location. Please check the highlighted fields.');
                            e.preventDefault();
                            return false;
                        }
                        
                        $('.vpo-supplied').each(function(){
                            if (!validateSuppliedQty($(this))) {
                                hasError = true;
                            }
                        });
                        
                        if (hasError) {
                            bootbox.alert('Please correct the errors in Supplied Qty fields before submitting.');
                            e.preventDefault();
                            return false;
                        }
                        
                        // Clear all localStorage on successful form submission
                        var vpoKeys = ['variantpoitems', 'vpo_supplied_quantities', 'vpo_note', 'vpo_raw_materials_data', 
                                      'podiscount', 'potax2', 'poshipping', 'poref', 'powarehouse', 'ponote', 
                                      'posupplier', 'pocurrency', 'poextras', 'podate', 'postatus', 'popayment_term'];
                        vpoKeys.forEach(function(key) {
                            localStorage.removeItem(key);
                        });
                    });
                });
                </script>

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
                                $tx[""] = "";
                                foreach ($tax_rates as $tax) {
                                    if($tax->is_substitutable == 0) { 
                                        $tx[$tax->id] = $tax->name ;
                                    }
                                }
                                echo form_dropdown('ptax', $tx, "", 'id="ptax" class="form-control pos-input-tip pcalculate" style="width:100%;"');
                                ?>
                            </div>
                        </div>                    
                        <div class="form-group">
                            <label class="col-sm-4 control-label"> <?= lang("tax_method", "mtax_method") ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, '', 'id="tax_method" class="form-control pos-input-tip pcalculate" style="width:100%"');
                                ?>
                            </div> 
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control pcalculate" id="pquantity">
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
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control pcalculate" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control pcalculate" id="pcost">
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
        var formattedCost = parseFloat(cost).toFixed(2);  
        var unitcost =  getUnitCost();
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
    var updatedCost = netCost.replace("Rs.", "").replace(/,/g, "");
    var inputValue = $this.closest('tr').find('.subtotal').val(); 

    var subtotal = inputValue.replace("Rs.", "").replace(/,/g, "");
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
<script>
//   window.__dummyItems = [
   
//     { product_id: 140, variant_id: 488, tax_id: '', qty: 6 }
//   ];
</script>
<script>
$(document).ready(function() {
    $('#add_pruchase').on('click', function(e) {
    localStorage.clear();
})
});
</script>
<script type="text/javascript">
// moved to top of file: robust back/forward clear logic
</script>
<script type="text/javascript">
/* VPO full-width totals bar — additive only; ItemnTotals() unchanged */
$(function () {
    function vpoFullWidthBottomTotal() {
        var $bt = $('#bottom-total');
        var $area = $('#add-purchase-form').closest('.box-content');
        if (!$bt.length || !$area.length) {
            return;
        }
        var w = $area.innerWidth();
        var left = $area.offset().left;
        if ($bt.css('position') === 'fixed') {
            $bt.css({ width: w, left: left, right: 'auto', maxWidth: w });
        } else {
            $bt.css({ width: '100%', left: '', right: '', maxWidth: '100%' });
        }
    }
    $(window).on('scroll.vpoFullWidth resize.vpoFullWidth', vpoFullWidthBottomTotal);
    vpoFullWidthBottomTotal();
});
</script>