<?php defined('BASEPATH') OR exit('No direct script access allowed');
  $formaction = $this->router->fetch_method();
 ?>
<script type="text/javascript">
var pos_settings = <?= json_encode($pos_settings); ?>, Pos_Settings = pos_settings;

var count = 1,
    an = 1,
    product_variant = 0,
    DT = <?= $Settings->default_tax_rate ?>,
    product_tax = 0,
    invoice_tax = 0,
    total_discount = 0,
    total = 0,
    allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
    tax_rates = <?php echo json_encode($tax_rates); ?>;
//var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
//var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');


$(document).ready(function() {
    <?php if ($inv) { ?>
    localStorage.setItem('sldate', '<?= $this->sma->hrld($inv->date) ?>');
    localStorage.setItem('slcustomer', '<?= $inv->customer_id ?>');
    localStorage.setItem('slbiller', '<?= $inv->biller_id ?>');
    localStorage.setItem('slref', '<?= $inv->reference_no ?>');
    localStorage.setItem('slwarehouse', '<?= $inv->warehouse_id ?>');
    localStorage.setItem('slsale_status', '<?= $inv->sale_status ?>');
    localStorage.setItem('slpayment_status', '<?= $inv->payment_status ?>');
    localStorage.setItem('slpayment_term', '<?= $inv->payment_term ?>');
    localStorage.setItem('slnote',
        '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)); ?>');
    localStorage.setItem('slinnote',
        '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->staff_note)); ?>');
    localStorage.setItem('sldiscount', '<?= ($inv->order_discount_id) ? $inv->order_discount_id : NULL ?>');
    localStorage.setItem('posdiscount', '<?= ($inv->order_discount_id)? $inv->order_discount_id : NULL ?>');
    localStorage.setItem('sltax2', '<?= $inv->order_tax_id ?>');
    localStorage.setItem('slshipping', '<?= $inv->shipping ?>');
    localStorage.setItem('slitems', JSON.stringify(<?= $inv_items; ?>));
    <?php } ?>

    <?php if ($Owner || $Admin) { ?>
    $(document).on('change', '#sldate', function(e) {
        localStorage.setItem('sldate', $(this).val());
    });
    if (sldate = localStorage.getItem('sldate')) {
        $('#sldate').val(sldate);
    }
    <?php } ?>
    $(document).on('change', '#slbiller', function(e) {
        localStorage.setItem('slbiller', $(this).val());
    });
    if (slbiller = localStorage.getItem('slbiller')) {
        $('#slbiller').val(slbiller);
    }
    ItemnTotals();
    $("#add_item").autocomplete({
        source: function(request, response) {
            if (!$('#slcustomer').val()) {
                $('#add_item').val('').removeClass('ui-autocomplete-loading');
                bootbox.alert('<?=lang('select_above');?>');
                $('#add_item').focus();
                return false;
            }
            var Sale_flag =
            1; // set flag for checking which screen is called for suggestion function in controller
            $.ajax({
                type: 'get',
                url: '<?= site_url('sales/suggestions'); ?>',
                dataType: "json",
                data: {
                    term: request.term,
                    Sale_flag: Sale_flag,
                    warehouse_id: $("#slwarehouse").val(),
                    customer_id: $("#slcustomer").val()
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function(event, ui) {
            if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                bootbox.alert('<?= lang('no_match_found') ?>', function() {
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
                bootbox.alert('<?= lang('no_match_found') ?>', function() {
                    $('#add_item').focus();
                });
                $(this).removeClass('ui-autocomplete-loading');
                $(this).val('');
            }
        },
        select: function(event, ui) {
        event.preventDefault();

    if (ui.item.row.type === 'Bundle' && ui.item.is_out_of_stock === 1) {
        bootbox.alert(ui.item.message, function() {
            $('#add_item').focus();
            $('#add_item').removeClass('ui-autocomplete-loading');
        });
        $(this).val('');
        return false;
    }

    if (ui.item.row.type === 'Bundle') {
        addcomboProducts(ui.item, ui.item.row.id);
        $(this).val('');
        return true;
    }

    if (ui.item.options) {
        product_option_model_call(ui.item);
        $(this).val('');
        return true;
    }

    if (ui.item.id !== 0) {
        <?php if ($this->Settings->overselling == 0) { ?>
            if (ui.item.row.type === 'combo') {
                var combo_items = ui.item.combo_items;
                if (Array.isArray(combo_items)) {
                    var outOfStockItems = [];

                    for (var i = 0; i < combo_items.length; i++) {
                        var citem = combo_items[i];
                        var qty = parseFloat(citem.quantity);

                        if (qty <= 0) {
                            outOfStockItems.push(citem.name);
                        }
                    }

                    if (outOfStockItems.length > 0) {
                        bootbox.alert("The following products are out of stock: (" + outOfStockItems.join(', ') + ")", function() {
                            $('#add_item').focus();
                            $('#add_item').removeClass('ui-autocomplete-loading');
                        });
                        $(this).val('');
                        return false;
                    }
                }
            }
        <?php } ?>

        var row = add_invoice_item(ui.item);
        if (row) {
            $(this).val('');
            $('#print_invoice').attr('disabled', false);
        }

    } else {
        bootbox.alert('<?= lang('no_match_found') ?>', function() {
            $('#add_item').focus();
            $('#add_item').removeClass('ui-autocomplete-loading');
        });
    }
}
    });

    $(window).bind('beforeunload', function(e) {
        localStorage.setItem('remove_slls', true);
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
    $('#reset').click(function(e) {
        $(window).unbind('beforeunload');
    });
    $('#edit_sale').click(function(e) {

        if (!validateSubmit()) {
            e.preventDefault();
            return false;
        }
        $(window).unbind('beforeunload');
    });
    $('form.edit-so-form').on('submit', function(e) {
        if (!validateSubmit()) {
            e.preventDefault();
            return false;
        }
        $(window).unbind('beforeunload');
    });
    function validateSubmit() {

        var displaySeller = <?php echo (int)$pos_settings->display_seller; ?>;

        // Invoice-level sales person
        if (displaySeller === 2) {
            var salesPerson = document.getElementById('sales_person') ? document.getElementById('sales_person').value: '0';
            if (salesPerson === '0' || salesPerson === '') {
                alert('Please Select Sales Person.');
                return false;
            }
        }

        // Item-level sales person
        if (displaySeller === 3) {
            var valid = true;
            $('.prsalesperson').each(function () {
                if (!$(this).val()) {
                    alert('Please Select Item Wise Sales Person.');
                    valid = false;
                    return false; // break
                }
            });
            if (!valid) return false;
        }
        <?php if (isset($pos_settings->sale_source_order_type_mode) && (int) $pos_settings->sale_source_order_type_mode === 2) { ?>
        if ($('#ordertype').length && !$('#ordertype').val()) {
            bootbox.alert(<?= json_encode(lang('sale_source_order_type_required')) ?>);
            return false;
        }
        <?php } ?>
        return true;
    }

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
        <h2 class="blue"><i
                class="fa-fw fa fa-plus"></i><?= ($formaction=='edit')? lang('edit_sale') : 'Edit Challan'; ?></h2>
        <h2 class="blue">
            <p style="font-weight:bold; margin-left:250px;"><?= lang("Invoice Number");?> : <?= lang($inv->id ); ?></p>
        </h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if($formaction=='edit_eshop_order') $ModuleAct='orders'; else $ModuleAct='sales'; ?>
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-so-form');
                echo form_open_multipart($ModuleAct."/".$formaction."/" . $inv->id, $attrib);
                    $lasturl = explode("/", $_SERVER['HTTP_REFERER']);
                    $last_segment = sizeof($lasturl)-1;
                ?>
                <input type="hidden" name="redirects"
                    value="<?= $lasturl[$last_segment-1].'/'.$lasturl[$last_segment] ?>" />
                <input type="hidden" name="sale_action" id="sale_action" value="<?php echo $sale_action; ?>">

                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("date", "sldate"); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($inv->date)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref" required="required"'); ?>
                            </div>
                        </div>
                        <?php //if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("warehouse", "slwarehouse"); ?>
                                <?php
                                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                                //$wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    if($Owner || $Admin ){
                                                    	$wh[$warehouse->id] = $warehouse->name;
                                                     }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                                                        $wh[$warehouse->id] = $warehouse->name;
                                                    }   	
                                                    	
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                                ?>
                            </div>
                        </div>
                        <?php /*} else {
                                        $warehouse_input = array(
                                            'type' => 'hidden',
                                            'name' => 'warehouse',
                                            'id' => 'slwarehouse',
                                            'value' => $this->session->userdata('warehouse_id'),
                                        );
                                        echo form_input($warehouse_input);
                                    }*/ ?>
                        <?php if ($Owner || $Admin || $this->session->userdata('biller_id')) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("biller", "slbiller"); ?>
                                <?php
                                    $bl[""] = "";
                                    if (!empty($billers)) {
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                    }
                                    if (!$Owner && !$Admin && !empty($inv->biller_id) && !isset($bl[$inv->biller_id])) {
                                        $sale_biller = $this->site->getCompanyByID($inv->biller_id);
                                        if ($sale_biller) {
                                            $bl[$sale_biller->id] = $sale_biller->company != '-' ? $sale_biller->company : $sale_biller->name;
                                        }
                                    }
                                    echo form_dropdown('billers', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" disabled data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
                                        } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("sale_status", "slsale_status"); ?>
                                <?php 
                                if($eshop_sale==1){
                                    switch ($inv->sale_status) {
                                        case "pending":
                                            $sst = ['pending' => lang('pending'), 'accepted'=> lang('Accepted'), 'cancelled'=> lang('cancelled')];
                                            break;
                                        case "cancelled":
                                            $sst = ['cancelled'=> lang('cancelled')];
                                            break;
                                        case "accepted":
                                            $sst = ['accepted'=> lang('Accepted'), 'cancelled'=> lang('cancelled'), 'processing'=> lang('processing'), 'order_ready' => lang('Order_ready')];
                                            break;
                                        case "processing":
                                            $sst = ['processing'=> lang('processing'), 'cancelled'=> lang('cancelled'), 'order_ready' => lang('Order_ready')];
                                            break;
                                        case "order_ready":
                                            $sst = ['order_ready' => lang('Order_ready'), 'completed' => lang('completed'), 'cancelled'=> lang('cancelled') ];
                                            break;
                                        case "completed":
                                            $sst = ['completed' => lang('completed')];
                                            break;
                                        default:
                                            $sst = [$inv->sale_status => lang($inv->sale_status)];
                                            break;
                                    }//end switch                                    
                                } else {
                                    if($inv->sale_status=='order_ready'){
                                        $sst = [ 'order_ready' => lang('Order_ready'), 'completed' => lang('completed')];
                                    } else if($inv->sale_status=='completed'){  
                                        $sst = [ 'completed' => lang('completed') ];
                                    } elseif($inv->sale_status=='pending') {
                                        $sst = ['pending' => lang('pending'), 'order_ready' => lang('Order_ready'), 'completed' => lang('completed') ];
                                    } else {
                                        $sst = [$inv->sale_status => lang($inv->sale_status)];
                                    }
                                }
                                echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"');
                                ?>
                            </div>
                        </div>
                        <input type="hidden" name="biller" id="biller_id" value="<?= (int) $inv->biller_id ?>">
                        <?php if($pos_settings->display_seller == 2 || $pos_settings->display_seller == 1) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                        <?= lang("Sales_Person*", "Sales_Person*"); ?>
                                    <select name="sales_person" id="sales_person" class="form-control " onchange="return getSellerDetails(this.value);">
                                        <option value="0">Select Sales Person</option>
                                        <?php  foreach($salesperson_details as $key_salesperson){ 
                                        $Value1 = $key_salesperson['id'].'-'.$key_salesperson['name'];
                                        $DbValue1 = $inv->seller_id.'-'.$inv->seller;
                                        ?>
                                        
                                        <option value="<?php echo $key_salesperson['id'].'-'.$key_salesperson['name']; ?>" <?php if($Value1==$DbValue1) echo 'selected'; ?>><?php echo $key_salesperson['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                        <?php
                        $_ssot_mode = isset($pos_settings->sale_source_order_type_mode) ? (int) $pos_settings->sale_source_order_type_mode : 1;
                        if ($_ssot_mode !== 0) {
                            $_otl_pt = isset($Settings->pos_type) ? strtolower(trim((string) $Settings->pos_type)) : '';
                            $_otl_order_mode = in_array($_otl_pt, array('restaurant', 'cafe', 'bakery'), true);
                        ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?php
                                echo lang($_otl_order_mode ? 'Order Type' : 'Sale Source', 'ordertype');
                                ?>
                                <select id="ordertype" name="order_type" class="form-control order_type">
                                    <?php
                                    $_order_types = !empty($order_types) ? $order_types : array();
                                    $stored = (string) $inv->order_type;
                                    $placeholder = $_otl_order_mode ? 'Select Type' : 'Select Source';
                                    echo '<option value=""' . ($stored === '' ? ' selected="selected"' : '') . '>' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . "</option>\n";
                                    foreach ($_order_types as $_ot) {
                                        if (empty($_ot->type)) {
                                            continue;
                                        }
                                        $t = $_ot->type;
                                        $sel = ($stored === $t);
                                        echo '<option value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '"' . ($sel ? ' selected' : '') . '>' . htmlspecialchars($t) . "</option>\n";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php } else { ?>
                        <input type="hidden" name="order_type" value="<?php echo htmlspecialchars((string) $inv->order_type, ENT_QUOTES, 'UTF-8'); ?>" />
                        <?php } ?>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?>
                                </div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                    echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon"
                                                    style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <?= lang("Company Name", "Company Name"); ?>

                                            <span class="form-control" readonly id="companyName"> </span>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang("Billing Address", "sl_billing_address"); ?>
                                            <input type="hidden" name="billing_address_id" id="billing_address_id" value="<?= isset($default_billing_address_id) ? $default_billing_address_id : ''; ?>" />
                                            <div class="input-group">
                                                <input type="text" class="form-control input-tip" id="sl_billing_address" readonly="readonly" style="width:100%;" value="<?= isset($default_billing_address_text) ? htmlspecialchars($default_billing_address_text, ENT_QUOTES, 'UTF-8') : ''; ?>" data-tip="<?= isset($default_billing_address_text) ? htmlspecialchars($default_billing_address_text, ENT_QUOTES, 'UTF-8') : ''; ?>" />
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="edit-billing-address" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="#" id="add-billing-address" class="external">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang("Shipping Address", "sl_shipping_address"); ?>
                                            <input type="hidden" name="shipping_address_id" id="shipping_address_id" value="<?= isset($default_shipping_address_id) ? $default_shipping_address_id : ''; ?>" />
                                            <div class="input-group">
                                                <input type="text" class="form-control input-tip" id="sl_shipping_address" readonly="readonly" style="width:100%;" value="<?= isset($default_shipping_address_text) ? htmlspecialchars($default_shipping_address_text, ENT_QUOTES, 'UTF-8') : ''; ?>" data-tip="<?= isset($default_shipping_address_text) ? htmlspecialchars($default_shipping_address_text, ENT_QUOTES, 'UTF-8') : ''; ?>" />
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="edit-shipping-address" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="#" id="add-shipping-address" class="external">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
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
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                        </div>
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
                                    <table id="slTable"
                                        class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?>
                                                </th>
                                                <!-- <th class="col-md-1">Variant</th> -->
                                                <?php if($pos_settings->display_seller == 3 || $pos_settings->display_seller == 4) { ?>
                                                    <th class="col-md-2"><?= lang("Sales_Person") ?> </th>
                                                <?php } ?>
                                                <?php if($this->Settings->overselling == 0) { ?>
                                                <th class="col-md-1">Item Stocks</th>
                                                <?php } ?>
                                                <?php
                                            if ($Settings->product_serial) {
                                                echo '<th class="col-md-1">' . lang("serial_no") . '</th>';
                                            }
                                            ?>
                                                <?php if ($Settings->product_batch_setting > 0) { ?>
                                                <th class="col-md-1"><?= lang("Batch_Number"); ?></th>
                                                <?php } ?>
                                                <?php if ($Settings->product_expiry > 0) { ?>
                                                <th class="col-md-1"><?= lang("expiry_date")?></th>
                                                <?php } ?>
                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <?php if ($Settings->packing_size_column == 1) { ?>
                                                <th class="col-md-1">Packing Size</th>
                                                <?php } ?>
                                                <?php if ($Settings->product_weight == 1) { ?>
                                                <th class="col-md-1"><?= lang("Weight") ?></th>
                                                <?php } ?>
                                                <th class="col-md-1"><?= lang("Unit Price") ?></th>
                                                <?php
                                            if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount') || $inv->product_discount)) {
                                                echo '<th class="col-md-1">' . lang("discount") . '</th>';
                                            }
                                            ?>
                                                <th class="col-md-1"><?= lang("Net Price"); ?> </th>
                                                <!--                                            <th class="col-md-1 delivery_items"><?= lang("delivered"); ?></th>
                                            <th class="col-md-1 delivery_items"><?= lang("pending"); ?></th>-->

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
                        <div class="col-md-4">
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

                        <?php if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->order_discount_id) { 
                          if ($Settings->sales_order_discount == '1') { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("order_discount", "sldiscount"); ?>
                                <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount" '.(($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"')); ?>
                            </div>
                        </div>
                        <?php  } } ?>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("shipping", "slshipping"); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("payment_term", "slpayment_term"); ?>
                                <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); ?>

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>"
                                    name="document" data-show-upload="false" data-show-preview="false"
                                    class="form-control file">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("Delivery_Status", "sldelivery_status"); ?>
                                <?php if($inv->eshop_sale==1){
                                            if($eshop_order[0]['shipping_method_name']=='Pickup From Store '){
                                                    $sst = array('not_applicable' => lang('Not Applicable'));
                                            }else{
                                                    $sst = array('not_applicable' => lang('Not Applicable'),'pending' => lang('pending'), 'in_progress' => lang('In_Progress'),'delivered' => lang('delivered'));
                                            }
                                        }else{
                                                $sst = array('not_applicable' => lang('Not Applicable'),'pending' => lang('pending'), 'in_progress' => lang('In_Progress'), 'delivered' => lang('delivered'));
                                        }
                                echo form_dropdown('delivery_status', $sst, $inv->delivery_status, 'class="form-control input-tip" required="required" id="sldelivery_status"');
                                ?>
                            </div>
                        </div>
                        <?= form_hidden('payment_status', $inv->payment_status); ?>
                        <div class="clearfix"></div>
                        <div id="multi-payment" style="display:none"> </div>
                        <div id="more_payment_block" style="display:none"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <?php if ($Settings->challan_transporter_details) { ?>
                                <div class="panel panel-info" style="<?= ($sale_action == 'chalan') ? '' : 'display:none;' ?>">
                                    <div class="panel-heading"><?= lang('challan_transporter_details'); ?></div>
                                    <div class="panel-body">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("transporter_mode", "transporter_mode"); ?>
                                                <?php echo form_input('transporter_mode', (isset($_POST['transporter_mode']) ? $_POST['transporter_mode'] : $inv->transporter_mode), 'class="form-control" id="transporter_mode"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("LR_No", "LR_No"); ?>
                                                <?php echo form_input('LR_No', (isset($_POST['LR_No']) ? $_POST['LR_No'] : $inv->LR_No), 'class="form-control" id="LR_No"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("total_parcels", "total_parcels"); ?>
                                                <?php echo form_input('total_parcels', (isset($_POST['total_parcels']) ? $_POST['total_parcels'] : $inv->total_parcels), 'class="form-control" id="total_parcels"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("place_of_supply", "place_of_supply"); ?>
                                                <?php echo form_input('place_of_supply', (isset($_POST['place_of_supply']) ? $_POST['place_of_supply'] : $inv->place_of_supply), 'class="form-control" id="place_of_supply"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("way_bill_no", "way_bill_no"); ?>
                                                <?php echo form_input('way_bill_no', (isset($_POST['way_bill_no']) ? $_POST['way_bill_no'] : $inv->way_bill_no), 'class="form-control" id="way_bill_no"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("packing_no", "packing_no"); ?>
                                                <?php echo form_input('packing_no', (isset($_POST['packing_no']) ? $_POST['packing_no'] : $inv->packing_no), 'class="form-control" id="packing_no"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>

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
                            <div class="fprom-group">
                                <?php echo form_submit('edit_sale', lang("submit"), 'id="edit_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                            <!--<td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td> -->
                            <?php } ?>
                            <?php if ($Settings->tax2) { ?>
                            <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span>
                            </td>
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
                        <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pdiscount"
                                <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice"
                                <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <?php if ((int) $Settings->product_expiry) { ?>
                    <div class="form-group">
                        <label for="cf1" class="col-sm-4 control-label"><?= 'Expiry Date' ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="cf1">
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ((int) $Settings->product_batch_setting) { ?>
                    <div class="form-group">
                        <label for="pbatch_number" class="col-sm-4 control-label"><?= lang('batch_number') ?></label>
                        <div class="col-sm-8" id="batchNo_div"></div>
                    </div>
                    <?php } ?>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_price" value="" />
                    <input type="hidden" id="row_id" value="" />
                    <input type="hidden" id="item_id" value="" />
                    <input type="hidden" id="storage_type" value="" />
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
                            <input type="text" class="form-control" id="mdiscount"
                                <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
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
$(document).ready(function() {
    <?php if($Settings->modify_qty_add_products !='1'){ ?>
    setTimeout(function() {
        if ($('#slsale_status').val() == 'completed') {
            $('.roption option').attr('disabled', 'disabled');
            $('.roption option:selected').attr('disabled', false);

            $('.rquantity').attr('readonly', true);
            //$('#editItem').attr('disabled', true);
            //            $('#edit_sale').attr('disabled', true);
            $('#add_item').attr('disabled', true);
            $('#addItemManually').attr('disabled', true);
        }

    }, 1000);
    <?php } ?>
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
var Sale_flag; // set flag for checking which screen is called for suggestion function in controller
/** Modal Variant **/
function product_option_model_call(product) {
    var ColorOption='';
		$.each(product.options_color, function (index, element) {
			ColorOption = element.name;
		});
    var product_options =
        '<table class="table table-striped table-border"><thead><tr><th>Variant Name</th><th>Quantity</th><th>Net Price</th><th>Subtotal (INR)</th></tr></thead><tbody>';

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

        var cost = element.price;
        var formattedCost = parseFloat(cost).toFixed(2);
        var unitcost = getUnitCost();
        var netCost = CalculateCost(product, unitcost, product);
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
                '<td class="w25-center"><input type="number" min="0" value="0" ' +
                'class="form-control input-sm quantity-input quantity_input width-setting" data-variant-id="' +
                element.id + '" data-netcost="' + cost + '" data-initial-value="0" ></td>' +
                // '<td class="w25-right net-cost">' + formatMoney() + '</td>' +
                '<td class="w25-right net-cost">' + '<input type="number" value="' + (formattedCost) + '" ' +
                'class="form-control input-sm net-cost-input net_cost_input width-setting" ' +
                'data-variant-id="' + element.id + '" ' + 'data-netcost="' + cost + '" ' +
                'data-initial-value="0">' + '</td>' +
                '<td class="w25-right"><input type="text" min="0" value="' + formatMoney() + ' " ' +
                'class="form-control subtotal  width-setting" id= "subtotals"> </td>' +
                // '<td class="w25-right net-cost">' + formatMoney() + '</td>' +
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

        <?php if (isset($pos_settings->sale_source_order_type_mode) && (int) $pos_settings->sale_source_order_type_mode === 2) { ?>
        if ($('#ordertype').length && !$('#ordertype').val()) {
            bootbox.alert(<?= json_encode(lang('sale_source_order_type_required')) ?>);
            return false;
        }
        <?php } ?>
        return true;
}

function getUnitCost(rowNo) {
    return $('#unit_price_' + rowNo).val();
}

function CalculateCost(product, optionCost, product) {
    var productCost = product.row.cost;
    productCost = productCost == null ? 0 : productCost;
    optionCost = optionCost == null ? 0 : optionCost;
    var netCost = (parseFloat(optionCost) + parseFloat(productCost));
    return netCost;

}
$(document).on('change', '.quantity_input', function() {
    var quantity = parseFloat($(this).val());
    var variantId = $(this).data('variant-id');
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
            var unitcost = parseFloat(subtotal) / parseFloat(currentValue);
            unitcost = formatDecimal(unitcost);

            if (currentValue !== '0') {
                var variantId = $this.data('variant-id');
                var quantity = currentValue;
                addProductToVarientProduct(variantId, quantity, unitcost);
                $this.data('initial-value', currentValue);
            }
        }.bind(this), index * 100);
    });
    $('.modalvarient').hide();
});

function addProductToVarientProduct(option_id, option_name, unitcost) {
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
    var Sale_flag = 1; // set flag for checking which screen is called for suggestion function in controller
    var quantityInput = $('.modalvarient').find(`input[data-variant-id="${option_id}"]`);
    var quantity = quantityInput.length > 0 ? quantityInput.val() : 1; // Default to 1 if not found
    var itemId = $(".modalvarient").find('.product_item_id').attr("value")
    //var option_id = $(".modalvarient").find('.option_id').val();
    var term = $(".modalvarient").find('.product_term').val() + "<?php echo $this->Settings->barcode_separator; ?>" +
        option_id + "<?php echo $this->Settings->barcode_separator; ?>" + Product_color;

    wh = $('#slwarehouse').val(),
        // cu = $('#posupplier').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('sales/suggestions') ?>",
            data: {
                term: term,
                option_id: option_id,
                Product_color: Product_color,
                warehouse_id: wh,
                // customer_id: cu,
                option_note: note,
                quantity: quantity,
                subtotal: unitcost,
                Sale_flag: Sale_flag,
            },
            dataType: "json",
            success: function(data) {

                if (data !== null) {
                    add_invoice_item(data[0]);
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
<!-- Multiple Biller Functionality  -->
<script>
$(document).ready(function() {
    <?php if (!$Owner && !$Admin) { ?>
    var warehouseId = $('#slwarehouse').val();
    if (warehouseId) {
        getbillerbyWarehoueseid(warehouseId);
    }
    $('#slwarehouse').on('change', function() {
        var warehouseId = $(this).val();
        getbillerbyWarehoueseid(warehouseId);
    });
    <?php } ?>
    var qubiller = $('#slbiller').val();
    if (qubiller) {
        $("#biller_id").val(qubiller);
    } else {
        $("#biller_id").val('<?= (int) $inv->biller_id ?>');
    }
    $('#slbiller').on('change', function() {
        var biller_name = $(this).val();
        $("#biller_id").val(biller_name);
    });

    function getbillerbyWarehoueseid(warehouseId) {
        if (warehouseId) {
            $.ajax({
                url: "<?= site_url('sales/get_biller_details'); ?>",
                type: "GET",
                data: {
                    warehouse_id: warehouseId
                },
                dataType: "json",
                success: function(response) {
                    const slbiller = $('#slbiller');
                    if (response.success) {
                        slbiller.empty(); // Clear existing options
                        response.billers.forEach(function(biller, index) {
                            const option = $('<option>', {
                                value: biller.id,
                                text: biller.name
                            });

                            slbiller.append(option);
                        });
                        slbiller.val('<?= (int) $inv->biller_id ?>').trigger('change');
                        $("#biller_id").val('<?= (int) $inv->biller_id ?>');

                    } else {
                        alert(response.message);
                        slbiller.empty(); // Clear existing options if no billers found
                        slbiller.append($('<option>', {
                            value: '',
                            text: 'No Biller Available'
                        }));
                    }
                },
                error: function() {
                    alert('Error fetching warehouse data');
                }
            });
        }
    }

});
</script>
<!-- Multiple Biller Functionality END -->
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
                data: {
                    company_id: customer_id
                },
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

    function getSelectedCustomerIdForAddress() {
        var cid = $('#slcustomer').val();
        if (!cid) {
            cid = localStorage.getItem('slcustomer');
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
            $('#customerAddressModalLabel').text(formatAddressModalTitle('Shipping Addresses'));
            $('#customerShippingAddressSection').show();
            $('#customerBillingAddressSection').hide();
        } else if (address_type == 'billing') {
            $('#customerAddressModalLabel').text(formatAddressModalTitle('Billing Addresses'));
            $('#customerShippingAddressSection').hide();
            $('#customerBillingAddressSection').show();
        } else {
            $('#customerAddressModalLabel').text(formatAddressModalTitle('Customer Addresses'));
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
                    if (address_type == 'shipping') {
                        $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
                    } else if (address_type == 'billing') {
                        $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
                    } else {
                        $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
                        $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">No address found.</div></div>');
                    }
                }
            },
            error: function() {
                if (address_type == 'shipping') {
                    $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Failed to load.</div></div>');
                } else if (address_type == 'billing') {
                    $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Failed to load.</div></div>');
                } else {
                    $('#customerShippingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Failed to load.</div></div>');
                    $('#customerBillingAddressList').html('<div class="col-sm-12"><div class="addr-empty">Failed to load.</div></div>');
                }
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
    });
</script>
<!-- Customer Address Functionality END -->