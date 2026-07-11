<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_pharma = isset($Settings->pos_type) && $Settings->pos_type == 'pharma' ? true : false;

?>
<script type="text/javascript">
    var pos_settings = <?= json_encode($pos_settings); ?>, Pos_Settings = pos_settings;

var count = 1,
    an = 1,
    product_variant = 0,
    DT = <?= $Settings->default_tax_rate ?>,
    product_tax = 0,
    invoice_tax = 0,
    product_discount = 0,
    order_discount = 0,
    total_discount = 0,
    total = 0;
    var allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>;
    var allow_edit_price = <?= ($Owner || $Admin || (isset($GP['edit_price']) && $GP['edit_price'])) ? 1 : 0; ?>;
    var tax_rates = <?php echo json_encode($tax_rates); ?>;
//var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
//var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
$(document).ready(function() {
    localStorage.clear();
    if (localStorage.getItem('remove_slls')) {
        if (localStorage.getItem('slitems')) {
            localStorage.removeItem('slitems');
        }
        if (localStorage.getItem('sldiscount')) {
            localStorage.removeItem('sldiscount');
        }
        if (localStorage.getItem('sltax2')) {
            localStorage.removeItem('sltax2');
        }
        if (localStorage.getItem('slref')) {
            localStorage.removeItem('slref');
        }
        if (localStorage.getItem('slshipping')) {
            localStorage.removeItem('slshipping');
        }
        if (localStorage.getItem('slwarehouse')) {
            localStorage.removeItem('slwarehouse');
        }
        if (localStorage.getItem('slnote')) {
            localStorage.removeItem('slnote');
        }
        if (localStorage.getItem('slinnote')) {
            localStorage.removeItem('slinnote');
        }
        if (localStorage.getItem('slcustomer')) {
            localStorage.removeItem('slcustomer');
        }
        if (localStorage.getItem('slbiller')) {
            localStorage.removeItem('slbiller');
        }
        if (localStorage.getItem('slcurrency')) {
            localStorage.removeItem('slcurrency');
        }
        if (localStorage.getItem('sldate')) {
            localStorage.removeItem('sldate');
        }
        if (localStorage.getItem('slsale_status')) {
            localStorage.removeItem('slsale_status');
        }
        if (localStorage.getItem('slchallan_status')) {
            localStorage.removeItem('slchallan_status');
        }
        if (localStorage.getItem('slpayment_status')) {
            localStorage.removeItem('slpayment_status');
        }
        if (localStorage.getItem('paid_by')) {
            localStorage.removeItem('paid_by');
        }
        if (localStorage.getItem('amount_1')) {
            localStorage.removeItem('amount_1');
        }
        if (localStorage.getItem('paid_by_1')) {
            localStorage.removeItem('paid_by_1');
        }
        if (localStorage.getItem('pcc_holder_1')) {
            localStorage.removeItem('pcc_holder_1');
        }
        if (localStorage.getItem('pcc_type_1')) {
            localStorage.removeItem('pcc_type_1');
        }
        if (localStorage.getItem('pcc_month_1')) {
            localStorage.removeItem('pcc_month_1');
        }
        if (localStorage.getItem('pcc_year_1')) {
            localStorage.removeItem('pcc_year_1');
        }
        if (localStorage.getItem('pcc_no_1')) {
            localStorage.removeItem('pcc_no_1');
        }
        if (localStorage.getItem('cheque_no_1')) {
            localStorage.removeItem('cheque_no_1');
        }
        if (localStorage.getItem('payment_note_1')) {
            localStorage.removeItem('payment_note_1');
        }
        if (localStorage.getItem('slpayment_term')) {
            localStorage.removeItem('slpayment_term');
        }
        localStorage.removeItem('remove_slls');
    }
    <?php if ($quote_id) { ?>
    // localStorage.setItem('sldate', '<?= $this->sma->hrld($quote->date) ?>');
    localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
    localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
    localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
    localStorage.setItem('slnote',
        '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($quote->note)); ?>');
    localStorage.setItem('sldiscount', '<?= $quote->order_discount_id ?>');
    localStorage.setItem('sltax2', '<?= $quote->order_tax_id ?>');
    localStorage.setItem('slshipping', '<?= $quote->shipping ?>');
    localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));
    <?php } ?>
    <?php if ($this->input->get('customer')) { ?>
    if (!localStorage.getItem('slitems')) {
        localStorage.setItem('slcustomer', <?= $this->input->get('customer'); ?>);
    }
    <?php } ?>
    <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
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
    if (!localStorage.getItem('slref')) {
        localStorage.setItem('slref', '<?= $slnumber ?>');
    }

    // New Customer Add 
    if (!localStorage.getItem('slcustomer')) {
        var quick_custome_nm = $('#quick_custome_nm').val();
        //alert(quick_custome_nm);
        if (quick_custome_nm.length != '') {
            localStorage.setItem('slcustomer', quick_custome_nm);
        } else {
            localStorage.setItem('slcustomer', <?= $pos_settings->default_customer; ?>);
        }
    } else {

        var quick_custome_nm = $('#quick_custome_nm').val();
        if (quick_custome_nm.length != '') {
            localStorage.setItem('slcustomer', quick_custome_nm);
        }

    }
    // End new customer add

    if (!localStorage.getItem('sltax2')) {
        localStorage.setItem('sltax2', <?= $Settings->default_tax_rate2; ?>);
    }
    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function(e) {
        $('#add_item').focus();
    });
    $("#add_item").autocomplete({
        source: function(request, response) {
            if (!$('#slcustomer').val()) {
                $('#add_item').val('').removeClass('ui-autocomplete-loading');
                bootbox.alert('<?= lang('select_above'); ?>');
                $('#add_item').focus();
                return false;
            }
            var Sale_flag = 1;    // set flag for checking which screen is called for suggestion function in controller
            if (request.term.length >= 3) {
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        Sale_flag: Sale_flag,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val(),
                        sale_action: $('#sale_action').val(),
                        challan_status: $('#slchallan_status').val()
                    },
                    success: function(data) {
                        if (data === null) {
                            bootbox.alert("Product not found.");
                            $('#add_item').val('');
                            $('#add_item').removeClass('ui-autocomplete-loading');
                            $('#modal-loading').hide();
                            return false;
                        }
                        if (data.status == 'raw_error') {
                            bootbox.alert(data.msg);
                            $('#add_item').val('');
                            $('#add_item').removeClass('ui-autocomplete-loading');
                            return;
                        }
                        response(data);
                    }
                });
            }
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
                $(this).removeClass('ui-autocomplete-loading');
                $(this).val('');
            } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                // set alert msg for products which is out of stock
                if (ui.content[0].Product_type === 'Bundle' && ui.content[0].is_out_of_stock === 1) {
                    var sale_action = $('#sale_action').val();
                    if (sale_action != 'chalan' && sale_action != 'challan') {
                        var outOfStockProducts = ui.content.map(function(item) {
                            return item.message;
                        }).join(', ');
                        bootbox.alert(outOfStockProducts, function() {
                            $('#add_item').focus();
                            $(this).removeClass('ui-autocomplete-loading');
                        });
                    }
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                }
                $(this).val('');
            }
        },
        select: function(event, ui) {
        event.preventDefault();

    if (ui.item.row.type === 'Bundle' && ui.item.is_out_of_stock === 1) {
        var sale_action = $('#sale_action').val();
        if (sale_action != 'chalan' && sale_action != 'challan') {
            bootbox.alert(ui.item.message, function() {
                $('#add_item').focus();
                $('#add_item').removeClass('ui-autocomplete-loading');
            });
            $(this).val('');
            return false;
        }
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
            var sale_action = $('#sale_action').val();
            var challan_status = $('#slchallan_status').val();
            if (sale_action != 'chalan' && sale_action != 'challan') {
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
                            bootbox.alert("The following products are out of stock: (" + outOfStockItems
                                .join(', ') + ")",
                                function() {
                                    $('#add_item').focus();
                                    $('#add_item').removeClass('ui-autocomplete-loading');
                                });
                            $(this).val('');
                            return false;
                        }
                    }
                }
            }
        <?php } ?>

        var sale_action = $('#sale_action').val();
        if (sale_action == 'chalan' && ui.item.tax_method_type !== 'category') {
            var default_tax_id = $('#tax_percentage').val();
            var selected_tax = null;
            if (default_tax_id) {
                // User picked a specific tax from dropdown — apply it
                $.each(tax_rates, function() {
                    if (this.id == default_tax_id) {
                        selected_tax = this;
                        return false;
                    }
                });
            } else {
                // Dropdown is empty — default to 0% tax
                $.each(tax_rates, function() {
                    if (parseFloat(this.rate) == 0) {
                        selected_tax = this;
                        return false;
                    }
                });
            }

            if (selected_tax) {
                ui.item.tax_rate = selected_tax;
                ui.item.row.tax_rate = selected_tax.id;
            }
        }

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

    // ── Tax Percentage: apply to existing grid rows on change ──
    $(document).on('change', '#tax_percentage', function () {
        if ($('#sale_action').val() != 'chalan') return;
        var selected_tax_id = $(this).val();

        // Find the chosen tax object from tax_rates array
        var selected_tax = null;
        if (selected_tax_id) {
            $.each(tax_rates, function () {
                if (this.id == selected_tax_id) {
                    selected_tax = this;
                    return false; // break
                }
            });
        } else {
            // Dropdown is empty — default to 0% tax
            $.each(tax_rates, function() {
                if (parseFloat(this.rate) == 0) {
                    selected_tax = this;
                    return false;
                }
            });
        }

        if (!selected_tax) return;

        // Update every item in slitems that does NOT have a category-based tax
        if (localStorage.getItem('slitems')) {
            var slitems = JSON.parse(localStorage.getItem('slitems'));
            $.each(slitems, function (key, item) {
                if (item.tax_method_type !== 'category') {
                    slitems[key].tax_rate      = selected_tax;
                    slitems[key].row.tax_rate  = selected_tax.id;
                }
            });
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems(); // re-render the table so Product Tax column updates
        }
    });

    $(document).on('change', '.gift_card_no', function() {
        $('.final-btn').prop('disabled', true);
        var getid = $(this).attr('id').split("_");
        var p_id = getid[3];
        $('#gc_details_' + p_id).html('');
        var cn = $(this).val() ? $(this).val() : '';
        var paid_amount = $('#amount_' + p_id).val();
        if (cn != '') {
            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "sales/validate_gift_card/" + cn,
                dataType: "json",
                success: function(data) {
                    if (data === false) {
                        $('#gift_card_no_' + p_id).parent('.form-group').addClass(
                            'has-error');
                        bootbox.alert('<?= lang('incorrect_gift_card') ?>');
                    } else if (data.customer_id !== null && data.customer_id !== $(
                            '#slcustomer').val()) {
                        $('#gift_card_no_' + p_id).parent('.form-group').addClass(
                            'has-error');
                        bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');

                    } else if (parseFloat(paid_amount) > parseFloat(data.balance)) {
                        $('#gift_card_no_' + p_id).parent('.form-group').addClass(
                            'has-error');
                        $('#gift_card_no_' + p_id).val('');
                        bootbox.alert(
                            '<?= lang("Unable to process payment: low card balance") ?>'
                        );
                    } else {
                        $('.final-btn').prop('disabled', false);
                        $('#gc_details_' + p_id).html('<small>Card No: ' + data.card_no +
                            '<br>Value: ' + data.value + ' - Balance: ' + data.balance +
                            '</small>');
                        $('#gift_card_no_' + p_id).parent('.form-group').removeClass(
                            'has-error');
                    }
                }
            });
        }
    });
    $(document).on('change', '.credit_card_no', function() {
        $('.final-btn').prop('disabled', true);
        var getid = $(this).attr('id').split("_");
        var p_id = getid[3];
        $('#credit_details_' + p_id).html('');
        var cn = $(this).val() ? $(this).val() : '';
        var paid_amount = $('#amount_' + p_id).val();
        if (cn != '') {
            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "sales/validate_credit_note/" + cn,
                dataType: "json",
                success: function(data) {
                    if (data === false) {
                        $('#credit_card_no_' + p_id).parent('.form-group').addClass(
                            'has-error');
                        bootbox.alert('<?= lang('incorrect_gift_card') ?>');
                    } else if (data.customer_id !== null && data.customer_id !== $(
                            '#slcustomer').val()) {
                        $('#credit_card_no_' + p_id).parent('.form-group').addClass(
                            'has-error');
                        bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');

                    } else if (parseFloat(paid_amount) > parseFloat(data.balance)) {
                        $('#credit_card_no_' + p_id).parent('.form-group').addClass(
                            'has-error');
                        $('#credit_card_no_' + p_id).val('');
                        bootbox.alert(
                            '<?= lang("Unable to process payment: low card balance") ?>'
                        );
                    } else {
                        $('.final-btn').prop('disabled', false);
                        $('#credit_details_' + p_id).html('<small>Card No: ' + data
                            .card_no + '<br>Value: ' + data.value + ' - Balance: ' +
                            data.balance + '</small>');
                        $('#credit_card_no_' + p_id).parent('.form-group').removeClass(
                            'has-error');
                    }
                }
            });
        }
    });
});
</script>
<script>
var pa = 1,
    grand_total = 0;
$(document).on('click', '.addButton', function() {
    if (pa == 3) {
        bootbox.alert('<?= lang('max_reached') ?>');
        document.getElementById("more_payment_block").style.display = "none";
        return false;
    }

    grand_total = formatDecimal(parseFloat(((total + invoice_tax) - order_discount) + shipping));
    var total_amt = 0,
        roundig_amt = 0;
    for (var i = 1; i <= pa; i++) {
        total_amt = parseFloat(total_amt) + parseFloat($('#amount_' + i).val());
    }

    pa++;
    $('#paid_by_1, #pcc_type_1').select2('destroy');
    var phtml = $('#payments').html(),
        update_html = phtml.replace(/_1/g, '_' + pa);
    pi = 'amount_' + pa;


    $('#multi-payment').append(
        '<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' +
        update_html);
    //                                        $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({minimumResultsForSearch: 7});

    roundig_amt = roundNumberNEW(parseFloat(grand_total) - parseFloat(total_amt), Number(pos_settings
        .rounding));

    $('#amount_' + pa).val(formatDecimal(roundig_amt));

    document.getElementById("pcheque_" + pa).style.display = "none";
    document.getElementById("g_transaction_id_" + pa).style.display = "none";
    document.getElementById("gc_" + pa).style.display = "none";
    $('#gc_details_' + pa).html('');
    document.getElementById("cd_" + pa).style.display = "none";
    $('#credit_details_' + pa).html('');
    if (pa >= 3) {
        document.getElementById("more_payment_block").style.display = "none";
    }

});

$(document).on('click', '.close-payment', function() {
    $(this).next().remove();
    $(this).remove();
    pa--;
    document.getElementById("more_payment_block").style.display = "block";
});
</script>
<style>

/* Remove increment/decrement arrows - Chrome, Safari, Edge */
.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button,
.net-cost-input::-webkit-outer-spin-button,
.net-cost-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Remove increment/decrement arrows - Firefox */
.quantity-input,
.net-cost-input {
    -moz-appearance: textfield;
}

/* Quantity center aligned */
.quantity-input {
    text-align: center !important;
}

/* Net Price right aligned */
.net-cost-input {
    text-align: right !important;
}
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
                class="fa-fw fa fa-plus"></i><?= lang($sale_action == 'chalan' ? 'Add Sale Challan' : 'add_sale'); ?>
        </h2>
    </div>
    <p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-sale-form');
                // Prevent form submission if sale_action is ambiguous
$current_action = $this->input->get('sale_action') ? $this->input->get('sale_action') : 'sales';
echo form_open_multipart("sales/add" . ($current_action != 'sales' ? '?sale_action=' . $current_action : ''), $attrib);
                if ($quote_id || $order_id) {
                    echo form_hidden(['quote_id' => $quote_id, 'order_id' => $order_id]);
                }
                // Preserve sale_action during form submission
                if ($sale_action) {
                    echo form_hidden('sale_action', $sale_action);
                }
                echo form_hidden(['syncQuantity' => $syncQuantity]);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("reference_no", "slref"); ?>
                                    <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
                                </div>
                            </div>
                            <?php //if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {  ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?= lang("warehouse", "slwarehouse"); ?>
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
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
                                        <?php if ($Owner || $Admin || $this->session->userdata('biller_id')) { ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?= lang("biller", "slbiller"); ?>
                                                <?php
                                        $bl[""] = "";
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
                            
                        </div>
                        <?php if ($Settings->challan_transporter_details) { ?>
                                <div class="panel panel-info" id="challan_transporter_details_panel" style="<?= ($sale_action == 'chalan') ? '' : 'display:none;' ?>">
                                    <div class="panel-heading"><?= lang('challan_transporter_details'); ?></div>
                                    <div class="panel-body">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("transporter_mode", "transporter_mode"); ?>
                                                <?php echo form_input('transporter_mode', (isset($_POST['transporter_mode']) ? $_POST['transporter_mode'] : ""), 'class="form-control" id="transporter_mode"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("LR_No", "LR_No"); ?>
                                                <?php echo form_input('LR_No', (isset($_POST['LR_No']) ? $_POST['LR_No'] : ""), 'class="form-control" id="LR_No"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("total_parcels", "total_parcels"); ?>
                                                <?php echo form_input('total_parcels', (isset($_POST['total_parcels']) ? $_POST['total_parcels'] : ""), 'class="form-control" id="total_parcels"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("place_of_supply", "place_of_supply"); ?>
                                                <?php echo form_input('place_of_supply', (isset($_POST['place_of_supply']) ? $_POST['place_of_supply'] : ""), 'class="form-control" id="place_of_supply"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("way_bill_no", "way_bill_no"); ?>
                                                <?php echo form_input('way_bill_no', (isset($_POST['way_bill_no']) ? $_POST['way_bill_no'] : ""), 'class="form-control" id="way_bill_no"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("packing_no", "packing_no"); ?>
                                                <?php echo form_input('packing_no', (isset($_POST['packing_no']) ? $_POST['packing_no'] : ""), 'class="form-control" id="packing_no"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#sale_action').change(function() {
            var newAction = $(this).val();
            var currentAction = '<?= $sale_action; ?>';
            
            // Prevent switching if it would cause data collision
            if (currentAction && newAction !== currentAction) {
                var confirmMsg = 'Warning: Changing from ' + (currentAction == 'chalan' ? 'Challan' : 'Sale') + ' to ' + (newAction == 'chalan' ? 'Challan' : 'Sale') + ' will clear all form data. Continue?';
                if (!confirm(confirmMsg)) {
                    $(this).val(currentAction);
                    return false;
                }
                // Clear form data if confirmed
                localStorage.clear();
                $('#add-sale-form')[0].reset();
            }
            
            if (newAction == 'chalan') {
                $('#challan_transporter_details_panel').slideDown();
                $('#tax_percentage_container').slideDown();
            } else {
                $('#challan_transporter_details_panel').slideUp();
                $('#tax_percentage_container').slideUp();
            }
        });
    });
</script>
                        <div class="clearfix"></div>
                        <?php if ($is_pharma): ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="patient_name">Patient Name </label>
                                    <input type="text" name="patient_name" id="patient_name"
                                        class="form-control input-tip required">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="doctor_name">Doctor Name </label>
                                    <input type="text" name="doctor_name" id="doctor_name"
                                        class="form-control input-tip required">
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <?php endif; ?>
                        <div class="row">
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
                                                    echo form_input('customer', (isset($_SESSION['quick_customername']) ? $_SESSION['quick_customername'] : (isset($_POST['customer']) ? $_POST['customer'] : $default_customer_name)), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                    ?>
                                        <input type="hidden" name="quick_custome_nm" id="quick_custome_nm"
                                            value="<?php echo (isset($_SESSION['quick_customerid']) ? $_SESSION['quick_customerid'] : $default_customer_id); ?>">

                                        <div class="input-group-addon no-print"
                                            style="padding: 2px 8px; border-left: 0;">
                                            <a href="#" id="toogle-customer-read-attr" class="external edit-customers">
                                                <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div>
                                        <div class="input-group-addon no-print"
                                            style="padding: 2px 7px; border-left: 0;">
                                            <a href="#" id="view-customer" class="external" data-toggle="modal"
                                                data-target="#myModal">
                                                <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div>
                                        <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                            <a href="<?= site_url('customers/add/quick'); ?>" id="add-customer"
                                                class="external" data-toggle="modal" data-target="#myModal">
                                                <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                                        <?php if ($saleAction) { ?>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <?= lang("Sale Action", "slsale_action"); ?>
                                                <?php
                                                    $sstyp['sales'] = lang('As Sales');
                                                    if ($this->data['Owner'] || $this->data['GP']['sales-add_challans']) {
                                                        $sstyp['chalan'] = lang('As Chalan');
                                                    }
                                                    if ($sale_action == 'chalan') {
                                                        unset($sstyp['sales']);
                                                    }
                                                    echo form_dropdown('sale_action', $sstyp, $sale_action, 'class="form-control input-tip" required="required" id="sale_action"');
                                                    ?>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                        <input type="hidden" name="sale_action" id="sale_action"
                                            value="<?= $sale_action ? $sale_action : 'sales'; ?>" />
                                        <?php } ?>
                                        <?php if($pos_settings->display_seller == 2 || $pos_settings->display_seller == 1) { ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <?= lang("Sales_Person*", "Sales_Person*"); ?>
                                                    <select name="sales_person" id="sales_person" class="form-control " onchange="return getSellerDetails(this.value);">
                                                        <option value="0">Select Sales Person</option>
                                                        <?php  foreach($salesperson_details as $key_salesperson){ ?>
                                                        <option value="<?php echo $key_salesperson['id'].'-'.$key_salesperson['name']; ?>"><?php echo $key_salesperson['name']; ?></option>
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
                                                    if (!$_otl_order_mode) {
                                                        echo '<option value="" selected="selected">Select Type</option>';
                                                    }
                                                    foreach ($_order_types as $_ot) {
                                                        if (empty($_ot->type)) {
                                                            continue;
                                                        }
                                                        $t = $_ot->type;
                                                        echo '<option value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($t) . "</option>\n";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                        <input type="hidden" name="order_type" value="" />
                                        <?php } ?>

                                        <div class="col-md-3" id="tax_percentage_container" style="<?= ($sale_action != 'chalan' && $sale_action != 'challan') ? 'display: none;' : ''; ?>">
                                            <div class="form-group">
                                                <?= lang("Tax_Percentage", "Tax_Percentage"); ?>
                                                <select name="tax_percentage" id="tax_percentage" class="form-control select">
                                                    <option value="">-- Select Tax --</option>
                                                    <?php foreach($tax_rates as $tax){ ?>
                                                        <option value="<?= $tax->id; ?>"><?= $tax->name; ?> (<?= (float)$tax->rate; ?>%)</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" id="sticker">
                                <div class="well well-sm">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <div class="input-group wide-tip">
                                            <div class="input-group-addon"
                                                style="padding-left: 10px; padding-right: 10px;">
                                                <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                            </div>
                                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                            <?php if($Settings->barcode_scan_camera){ ?>
                                            <div class="input-group-addon"
                                                style="padding-left: 10px; padding-right: 10px;">
                                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    id="scancamerabtn" data-target="#scan_barcode_camera"> <i
                                                        class="fa fa-camera"></i> Scan </button>
                                            </div>
                                            <?php } ?>
                                            <?php if ($Owner || $Admin || $GP['products-add']) { ?>

                                            <div class="input-group-addon"
                                                style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually" class="tip"
                                                    title="<?= lang('add_product_manually') ?>">
                                                    <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                            <?php } if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
                                            <div class="input-group-addon"
                                                style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="sellGiftCard" class="tip"
                                                    title="<?= lang('sell_gift_card') ?>">
                                                    <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                                                    <th class="col-md-1">Item<br />Cart Qt. / Stock Qt.</th>
                                                    <?php } ?>
                                                    <?php
                                                        if ($Settings->product_serial) {
                                                            echo '<th class="col-md1">' . lang("serial_no") . '</th>';
                                                        }
                                                    ?>
                                                    <?php if ($Settings->product_batch_setting > 0) { ?>
                                                    <th style="width:10%;"><?= lang("Batch_Number"); ?></th>
                                                    <?php } ?>
                                                    <?php if ($Settings->product_expiry > 0) { ?>
                                                    <th class="col-md-1"><?= lang("expiry_date") ?></th>
                                                    <?php } ?>
                                                    <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                    <?php if ($Settings->product_weight == 1) { ?>
                                                    <th class="col-md-1"><?= lang("Weight") ?></th>
                                                    <?php } ?>
                                                    <th class="col-md-1"><?= lang("Unit Price") ?></th>
                                                    <?php
                                                    if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                        echo '<th class="col-md-1">' . lang("discount") . '</th>';
                                                    }
                                                    ?>
                                                    <th class="col-md-1"><?= lang("Net Price"); ?> </th>
                                                    <?php
                                                    if ($Settings->tax1) {
                                                        echo '<th class="col-md-1">' . lang("product_tax") . '</th>';
                                                    }
                                                    ?>
                                                    <th>
                                                        <?= lang("subtotal"); ?>
                                                        (<span class="currency"><?= $default_currency->code ?></span>)
                                                    </th>
                                                    <th style="width: 30px !important; text-align: center;">
                                                        <i class="fa fa-trash-o"
                                                            style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
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

                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                if ($Settings->sales_order_discount == '1') {
                                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_discount", "sldiscount"); ?>
                                    <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
                                </div>
                            </div>
                            <?php }
                            } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("shipping", "slshipping"); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("document", "document") ?>
                                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>"
                                        name="document" data-show-upload="false" data-show-preview="false"
                                        class="form-control file">
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?php 
                                        $status_field_name = $sale_action == 'chalan' ? "slchallan_status" : "slsale_status";
                                        $status_input_name = $sale_action == 'chalan' ? "challan_status" : "sale_status";
                                        echo lang($sale_action == 'chalan' ? "challan_status" : "sale_status", $status_field_name);
                                    ?>
                                    <?php 
                                    if ($sale_action == 'chalan') {
                                        // Enhanced status options for challans
                                        $sst = array('pending' => lang('pending'));
                                        // Only add 'completed' option if user has permission
                                        if ($this->session->userdata('group_id') == 1 || $this->session->userdata('group_id') == 2 || (isset($this->GP['challancompleted_status']) && $this->GP['challancompleted_status'] == 1)) {
                                            $sst['completed'] = lang('completed');
                                        }
                                    } else {
                                        // Simple status options for sales
                                        $sst = array('completed' => lang('completed'), 'pending' => lang('pending'));
                                    }
                                    echo form_dropdown($status_input_name, $sst, '', 'class="form-control input-tip" required="required" id="'.$status_field_name.'"');
                                    ?>

                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?= lang("payment_term", "slpayment_term"); ?>
                                    <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); ?>

                                </div>
                            </div>
                            <?php 
                            if ($sale_action == 'chalan') {
                                // For challans, check challan-showPayments permission
                                if ($Owner || $Admin || $GP['challan-add_payment']) { ?>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <?= lang("payment_status", "slpayment_status"); ?>
                                            <?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
                                            echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"');
                                            ?>
                                        </div>
                                    </div>
                                <?php } else {
                                    echo form_hidden('payment_status', 'pending');
                                }
                            } else {
                                // For normal sales, check sales-payments permission
                                if ($Owner || $Admin || $GP['sales-payments']) { ?>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <?= lang("payment_status", "slpayment_status"); ?>
                                            <?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
                                            echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"');
                                            ?>
                                        </div>
                                    </div>
                                <?php
                                } else {
                                    echo form_hidden('payment_status', 'pending');
                                }
                            }
                            ?>

                        </div>
                        <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no[]', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid[]" type="text" id="amount_1"
                                                            class="pa form-control kb-pad amount"
                                                            onkeypress="return isNumberKey(event)"
                                                            required="required" />
                                                        <span id="error"
                                                            style="color:#a94442; display: none;font-size:11px;">please
                                                            enter numbers only</span>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <?= lang("paying_by", "paid_by_1"); ?>
                                                    <select name="paid_by[]" id="paid_by_1"
                                                        class="form-control paid_by">
                                                        <?= $this->sma->paid_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="payment">
                                                    <div class="pcheque_1" id="pcheque_1" style="display:none;">
                                                        <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                                            <input name="cheque_no[]" type="text" id="cheque_no_1"
                                                                class="form-control cheque_no" />
                                                        </div>
                                                    </div>
                                                    <div class="gc_1" id="gc_1" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no[]" type="text" id="gift_card_no_1"
                                                            class="pa form-control kb-pad gift_card_no" />

                                                        <div id="gc_details_1"></div>
                                                    </div>
                                                    <div class="cd_1" id="cd_1" style="display:none;">
                                                        <label for="credit_card_no_1">Credit Note</label>
                                                        <input name="credit_card_no[]" type="text" id="credit_card_no_1"
                                                            class="pa form-control kb-pad credit_card_no"
                                                            autocomplete="off">
                                                        <div id="credit_details_1"></div>
                                                    </div>
                                                    <div class="g_transaction_id_1" id="g_transaction_id_1"
                                                        style="display: none;">
                                                        <?= lang("transaction_id", "transaction_id"); ?>
                                                        <input name="transaction_id[]" type="text" id="transaction_id_1"
                                                            class="transaction_id form-control kb-pad" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no[]" type="text" id="pcc_no_1"
                                                            class="form-control" placeholder="<?= lang('cc_no') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_holder[]" type="text" id="pcc_holder_1"
                                                            class="form-control"
                                                            placeholder="<?= lang('cc_holder') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type[]" id="pcc_type_1"
                                                            class="form-control pcc_type"
                                                            placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                            <option value="MasterCard"><?= lang("MasterCard"); ?>
                                                            </option>
                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                                        </select>
                                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month[]" type="text" id="pcc_month_1"
                                                            class="form-control" placeholder="<?= lang('month') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year[]" type="text" id="pcc_year_1"
                                                            class="form-control" placeholder="<?= lang('year') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv[]" type="text" id="pcc_cvv2_1"
                                                            class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <div id="multi-payment" style="display:none"> </div>
                        <div id="more_payment_block" style="display:none">
                            <button type="button" class="btn btn-primary col-md-12 addButton"><i class="fa fa-plus"></i>
                                <?= lang('Add_More_Payments') ?></button>
                        </div>
                        <div class="form-group">
                            <?= lang('payment_note', 'payment_note_1'); ?>
                            <textarea name="payment_note" id="payment_note_1"
                                class="pa form-control kb-text payment_note"></textarea>
                        </div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="row" id="bt">
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

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" name="submit_type" id="submit_type" value="">
                                <div class="fprom-group">
                                    <?php echo form_submit('add_sale', lang("submit"), 'id="add_sale" class="btn btn-primary final-btn" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                    <button type="button" class="btn btn-info cmdprint final-btn" name="cmdprint"
                                        style="padding: 6px 15px; margin:15px 0;" id="print_invoice">Submit &
                                        Print</button>
                                    <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <!-- <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td> -->
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
                            class="fa fa-2x">&times;</i></span><span
                        class="sr-only"><?= lang('close'); ?></span></button>
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
                    <?php if ((int) $Settings->product_batch_setting) { ?>
                    <div class="form-group">
                        <label for="pbatch_number" class="col-sm-4 control-label"><?= lang('batch_number') ?></label>
                        <div class="col-sm-8" id="batchNo_div"></div>
                    </div>
                    <?php } ?>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                    <div class="form-group">
                        <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pdiscount">
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
                            class="fa fa-2x">&times;</i></span><span
                        class="sr-only"><?= lang('close'); ?></span></button>
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                    <div class="form-group">
                        <label for="mdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mdiscount">
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
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                id="genNo"><i class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname" />

                <div class="form-group">
                    <?= lang("value", "gcvalue"); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("price", "gcprice"); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("customer", "gccustomer"); ?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("expiry_date", "gcexpiry"); ?>
                    <?php echo form_input('gcexpiry', $this->sma->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('#print_invoice').attr('disabled', true);
    $('#gccustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function(term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function(data, page) {
                if (data.results != null) {
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        }
    });
    $('#genNo').click(function() {
        var no = generateCardNo();
        $(this).parent().parent('.input-group').children('input').val(no);
        return false;
    });
});

jQuery('.cmdprint').on('click', function() {
    jQuery('#submit_type').val('print');
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
            var val = $(this).val(); // "id~name" OR ""
            if (!val) {
                alert('Please Select Item Wise Sales Person.');
                valid = false;
                return false; // break loop
            }
        });
        if (!valid) {
            return false;
        }
    }
    <?php if (isset($pos_settings->sale_source_order_type_mode) && (int) $pos_settings->sale_source_order_type_mode === 2) { ?>
    if ($('#ordertype').length && !$('#ordertype').val()) {
        bootbox.alert(<?= json_encode(lang('sale_source_order_type_required')) ?>);
        return false;
    }
    <?php } ?>
    return true;
}

$('#add_sale').click(function() {
    var paid_by = $('.paid_by').val();
    var credit_card_no = $('.credit_card_no').val();
    if (paid_by == 'credit_note') {
        if (credit_card_no.length == '') {
            $('.credit_card_no').parent('.form-group').addClass('has-error');
            bootbox.alert('Credit Note required.');
            return false;
        }
    }

    var gift_card_no = $('.gift_card_no').val();
    if (paid_by == 'gift_card') {
        if (gift_card_no.length == '') {
            $('.gift_card_no').parent('.form-group').addClass('has-error');
            bootbox.alert('<?= lang('required_gift_card') ?>');
            return false;
        }
    }
    if (!validateSubmit()) {
        return false;
    }

    var sale_action = $('#sale_action').val();
    var challan_status = $('#slchallan_status').val();
    if (sale_action == 'chalan' && challan_status == 'completed' && site.settings.overselling == 0) {
        if (window.overselling_items && window.overselling_items.length > 0) {
            var unique_items = Array.from(new Set(window.overselling_items));
            bootbox.alert("The following products are out of stock: (" + unique_items.join(', ') + ")");
            return false;
        }
    }
});
$('#print_invoice').click(function() {
    var paid_by = $('.paid_by').val();
    var credit_card_no = $('.credit_card_no').val();
    if (paid_by == 'credit_note') {
        if (credit_card_no.length == '') {
            $('.credit_card_no').parent('.form-group').addClass('has-error');
            bootbox.alert('Credit Note required.');
            return false;
        }
    }
    var gift_card_no = $('.gift_card_no').val();
    if (paid_by == 'gift_card') {
        if (gift_card_no.length == '') {
            $('.gift_card_no').parent('.form-group').addClass('has-error');
            bootbox.alert('<?= lang('required_gift_card') ?>');
            return false;
        }
    }
    if (!validateSubmit()) {
        return false;
    }

    var sale_action = $('#sale_action').val();
    var challan_status = $('#slchallan_status').val();
    if (sale_action == 'chalan' && challan_status == 'completed' && site.settings.overselling == 0) {
        if (window.overselling_items && window.overselling_items.length > 0) {
            var unique_items = Array.from(new Set(window.overselling_items));
            bootbox.alert("The following products are out of stock: (" + unique_items.join(', ') + ")");
            return false;
        }
    }
    $('#print_invoice').text('<?= lang('loading'); ?>').attr('disabled', true);
    document.getElementById('add-sale-form').submit();
});

/****/
function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        document.getElementById("error").style.display = "inline";
        return false;
    }
    document.getElementById("error").style.display = "none";
    return true;
}
/****/



<?php 
        if($Settings->send_sales_excel){
            if($_SESSION['Send_Excel']==1){ ?>
$.ajax({
    type: 'ajax',
    method: 'get',
    url: '<?= base_url()."sales/export_excel"."/".$_SESSION['sale_id']; ?>',
    //async:false,
    success: function(res) {
        console.log(res);
        console.log('success');
    },
    error: function() {
        console.log('errror');
    }
});
<?php } 
        }
   ?>
</script>

<!--  Barcode Scan using system camera -->
<!-- Modal -->
<div class="modal fade" id="scan_barcode_camera" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Scan Barcode</h5>

            </div>
            <div class="modal-body" style="height: 72%;">

                <main class="wrapper" style="padding-top:2em">

                    <section class="container" id="demo-content">




                        <div>
                            <video id="video" width="100%" height="90%" style="border: 1px solid gray"></video>
                        </div>

                        <div id="sourceSelectPanel" style="display:none">
                            <label for="sourceSelect">Change video source:</label>
                            <select id="sourceSelect" style="max-width:400px; ">
                            </select>
                        </div>

                        <!--              <label>Result:</label>
                              <pre><code id="result"></code></pre>-->
                    </section>

                </main>
                <!-- <div id="barcodeScanner">
                        <span id='loading-status' style='font-size:x-large'>Loading Library...</span>
                    </div>

                    <div class="cameralist" style="display:none">
                        <label for="videoSource">Video source: </label>
                        <select id="videoSource"></select>
                    </div>

                        <div id="videoview">
                            <div class="dce-video-container" id="videoContainer"></div>
                            <canvas id="overlay"></canvas>
                        </div> -->
            </div>
            <div class="modal-footer">
                <button type="button" id="closecamera" class="btn btn-danger" data-dismiss="modal">Close</button>
                <!--<button type="button" class="btn btn-primary">Save changes</button>-->
            </div>
        </div>
    </div>
</div>

<script src="<?= $assets ?>js/barcodezxing/index.js"></script>
<script src="<?= $assets ?>js/barcodezxing/script.js"></script>

<script>
window.addEventListener('load', function() {
    let selectedDeviceId;
    var hints = new Map();
    hints.set(ZXing.DecodeHintType.ASSUME_GS1, true)
    hints.set(ZXing.DecodeHintType.TRY_HARDER, true)
    const codeReader = new ZXing.BrowserMultiFormatReader(hints)
    console.log('ZXing code reader initialized')
    codeReader.getVideoInputDevices()
        .then((videoInputDevices) => {
            const sourceSelect = document.getElementById('sourceSelect')
            selectedDeviceId = videoInputDevices[0].deviceId
            if (videoInputDevices.length >= 1) {
                videoInputDevices.forEach((element) => {
                    const sourceOption = document.createElement('option')
                    sourceOption.text = element.label
                    sourceOption.value = element.deviceId
                    sourceSelect.appendChild(sourceOption)
                })

                sourceSelect.onchange = () => {
                    selectedDeviceId = sourceSelect.value;
                };

                const sourceSelectPanel = document.getElementById('sourceSelectPanel')
                sourceSelectPanel.style.display = 'block'
            }

            //document.getElementById('startButton').addEventListener('click', () => {
            document.getElementById('scancamerabtn').addEventListener('click', () => {
                codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                    if (result) {
                        console.log(result.getText())

                        $('#add_item').val(result.getText());
                        $('#add_item').autocomplete('search', $('#add_item').val());
                        $('#closecamera').trigger('click');
                        $('#scan_barcode_camera').modal('hide');
                        setTimeout(function() {

                            $('#scancamerabtn').trigger('click');
                        }, 1000);


                        //  document.getElementById('result').textContent = result.text
                    }
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error(err)
                        document.getElementById('result').textContent = err
                    }
                })
                console.log(`Started continous decode from camera with id ${selectedDeviceId}`)
            })


            // document.getElementById('resetButton').addEventListener('click', () => {
            document.getElementById('closecamera').addEventListener('click', () => {
                codeReader.reset()
                document.getElementById('result').textContent = '';
                console.log('Reset.')
            })



        })
        .catch((err) => {
            console.error(err)
        })
})
</script>
<!--  <script src="https://cdn.jsdelivr.net/npm/dynamsoft-javascript-barcode@9.0.0/dist/dbr.js"></script>-->

<!-- <script type="text/javascript" src="<?= $assets ?>js/barcode/overlay.js"></script> -->
<script>
/* // Make sure to set the key before you call any other APIs under Dynamsoft.DBR
        // You can register for a free 30-day trial here: https://www.dynamsoft.com/customer/license/trialLicense?product=dbr&deploymenttype=browser.
        Dynamsoft.DBR.BarcodeReader.license = "DLS2eyJoYW5kc2hha2VDb2RlIjoiMjAwMDAxLTE2NDk4Mjk3OTI2MzUiLCJvcmdhbml6YXRpb25JRCI6IjIwMDAwMSIsInNlc3Npb25QYXNzd29yZCI6IndTcGR6Vm05WDJrcEQ5YUoifQ==";
        var videoSelect = document.querySelector('#videoSource');
        var cameraInfo = {};
        var scanner = null;
        initOverlay(document.getElementById('overlay'));
        async function openCamera() {
            clearOverlay();
            let deviceId = videoSelect.value;
            if (scanner) {
                await scanner.setCurrentCamera(cameraInfo[deviceId]);
            }
        }

       async function closeCamera() {
            clearOverlay();
//            let deviceId = videoSelect.value;
//            if (scanner) {
//                await scanner.stop();
//            }
        }
        videoSelect.onchange = openCamera;
    
       $('#scancamerabtn').click(function(){
           Dynamsoft.DBR.BarcodeScanner.loadWasm();
           initBarcodeScanner();
       });


        $('#closecamera').click(function(){
           closeCamera();
        });
        
        

//        window.onload = async function () {
//            try {
//                await Dynamsoft.DBR.BarcodeScanner.loadWasm();
////                await initBarcodeScanner();
//            } catch (ex) {
//                alert(ex.message);
//                throw ex;
//            }
//        };

        function updateResolution() {
            if (scanner) {
                let resolution = scanner.getResolution();
                updateOverlay(resolution[0], resolution[1]);
            }
        }
        
        function listCameras(deviceInfos) {
            for (var i = 0; i < deviceInfos.length; ++i) {
                var deviceInfo = deviceInfos[i];
                var option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label;
                cameraInfo[deviceInfo.deviceId] = deviceInfo;
                videoSelect.appendChild(option);
            }
        }

        async function initBarcodeScanner() {
            scanner = await Dynamsoft.DBR.BarcodeScanner.createInstance();
            await scanner.updateRuntimeSettings("speed");
            await scanner.setUIElement(document.getElementById('videoContainer'));

            let cameras = await scanner.getAllCameras();
            listCameras(cameras);
            await openCamera();
            scanner.onFrameRead = results => {
                clearOverlay();

                let txts = [];
                try {
                    let localization;
                    if (results.length > 0) {
                        for (var i = 0; i < results.length; ++i) {
                            txts.push(results[i].barcodeText);
                            localization = results[i].localizationResult;
//                            drawOverlay(localization, results[i].barcodeText);
                        }
                        getBarcodeValue(txts.join(', '));
//                        alert(txts.join(', '));
//                         document.getElementById('result').innerHTML = txts.join(', ');
                    }
                    else {
//                        document.getElementById('result').innerHTML = "No barcode found";
                    }

                } catch (e) {
                    alert(e);
                }
            };
            scanner.onUnduplicatedRead = (txt, result) => { };
            document.getElementById('loading-status').hidden = true;
            scanner.onPlayed = function() {
                updateResolution();
            }
            await scanner.show();
            
        }
        
        
        function getBarcodeValue(pass){
            console.log("Barcode : "+ pass);
             $('#add_item').val(pass);
             $('#add_item').autocomplete('search', $('#add_item').val());
             $('#closecamera').trigger('click');
              $('#scan_barcode_camera').modal('hide');
              setTimeout(function(){
                 
                     $('#scancamerabtn').trigger('click');
                }, 700);
        }
        
        $('#closecamera').click(function(){
             $('#scan_barcode_camera').modal('hide');
        }) */
</script>

<!-- End Barcode Scan Using Camera -->

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
var Sale_flag;    // set flag for checking which screen is called for suggestion function in controller
function addcomboProducts(bundle_data, option_name = null, unitcost = null) {
    if (!Array.isArray(bundle_data.combo_items) || bundle_data.combo_items.length === 0) {
        return;
    }
    var Sale_flag = 1;    // set flag for checking which screen is called for suggestion function in controller
    bundle_data.combo_items.forEach(function(item) {
        var productid = item.product_id;
        code = item.code; // product code
        bundle_option_id = item.option_id; // product option id(for variant case)
        // var  product_Id = option_name;
        var Product_color = $('#Product_color').val();
        $('.color-form-group').removeClass('has-error')
        if (Product_color == '0') {
            $('.color-form-group').addClass('has-error')
            return false;
        }
        // var itemId = $(".modalvarient").find('.product_item_id').attr("value")
        //var option_id = $(".modalvarient").find('.option_id').val();
        var term = $("#add_item").val() + "<?php echo $this->Settings->barcode_separator; ?>" +
            bundle_data + "<?php echo $this->Settings->barcode_separator; ?>" + Product_color;
        wh = $('#slwarehouse').val(),
            $.ajax({
                type: "get",
                url: "<?= site_url('sales/suggestions') ?>",
                data: {
                    term: term,
                    bundel_item_code: code,
                    bundle_option_id: bundle_option_id,
                    Product_color: Product_color,
                    warehouse_id: wh,
                    product_Id: productid,
                    Sale_flag : Sale_flag,
                    sale_action: $('#sale_action').val(),
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
    });
}
/** Modal Variant **/
function product_option_model_call(product) {
    var ColorOption='';
		$.each(product.options_color, function (index, element) {
			ColorOption = element.name;
		});
       
    var product_options =
        '<table class="table table-striped table-border"><thead><tr><th>Variant Name</th><th>Quantity</th><th>Net Price</th><th>Subtotal (INR)</th></tr></thead><tbody>';
    // if (Array.isArray(product.options)) {
    //     product.options.sort(function(a, b) {

    //         function parseNumericParts(value) {
    //             return value.split('/').map(part => parseFloat(part) || 0);
    //         }

    //         function compareNumericArrays(arr1, arr2) {
    //             const length = Math.min(arr1.length, arr2.length);
    //             for (let i = 0; i < length; i++) {
    //                 if (arr1[i] < arr2[i]) return -1;
    //                 if (arr1[i] > arr2[i]) return 1;
    //             }
    //             return arr1.length - arr2.length;
    //         }
    //         if (a.name && b.name) {
    //             const aParts = parseNumericParts(a.name);
    //             const bParts = parseNumericParts(b.name);

    //             return compareNumericArrays(aParts, bParts);
    //         } else if (a.name || b.name) {
    //             return a.name ? -1 : 1;
    //         } else {
    //             return (a.value || 0) - (b.value || 0);
    //         }
    //     });
    // }

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
                // '<td class="w25-right net-cost">' + formatMoney() + '</td>' + //existing
                '<td class="w25-right net-cost">' + '<input type="number" value="' + (formattedCost) + '" ' +
                'class="form-control input-sm net-cost-input net_cost_input width-setting" ' +
                'data-variant-id="' + element.id + '" ' + 'data-netcost="' + cost + '" ' +
                'data-initial-value="0">' + '</td>' +
                '<td class="w25-right">' +
                '<input type="hidden" id="subtotal">' +
                '<input type="text" min="0" value="' + formatMoney(0) + '" ' +
                'class="form-control input-sm subtotal subtotal width-setting" ' +
                'data-variant-id="' + element.id + '" id="subtotals">' +
                '</td>'
            // '<td class="w25-right"><input type="text" min="0" value="' + formatMoney() + ' " ' +'class="form-control subtotal  width-setting" id= "subtotals"> </td>' + // existing
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
    // var netCosts = parseFloat($(this).data('netcost'));
    var netCosts = parseFloat($(this).closest('tr').find('.net-cost-input').val());
    var netCost = quantity * netCosts;
    $(this).closest('tr').find('#subtotal').val(parseFloat(netCost));
    $(this).closest('tr').find('.subtotal').val(formatMoney(netCost));
    $(this).closest('tr').find('.net-cost').val(formatMoney(netCosts));
});

$(document).on('change', '.net-cost-input', function() {
    var quantity = parseFloat($(this).closest('tr').find('.quantity-input').val());
    var netCost = parseFloat($(this).val());
    var variantId = $(this).data('variant-id');
    var subtotal = quantity * netCost;
    $(this).closest('tr').find('.subtotal').val(formatMoney(subtotal));
    $(this).closest('tr').find('#subtotal').val(parseFloat(subtotal));

});

$(document).on('change', '.subtotal', function() {
    var row = $(this).closest('tr');
    var quantity = parseFloat(row.find('.quantity-input').val());
    var subtotalValue = $(this).val();
    var subtotal = parseFloat(subtotalValue.replace(/[^0-9]/g, ''));
    var netCost = parseFloat(subtotal) / (quantity);
    $(this).closest('tr').find('.net-cost-input').val(netCost.toFixed(2));
    $(this).closest('tr').find('.subtotal').val(formatMoney(subtotal.toFixed(2)));
    $(this).closest('tr').find('#subtotal').val(parseFloat(subtotal.toFixed(2)));

});
$('#submitBtn').on('click', function() {

    $('.quantity-input').each(function(index) {
        setTimeout(function() {
            var $this = $(this);
            var $row = $this.closest('tr');
            var variantId = $this.data('variant-id');
            var initialValue = $this.data('initial-value');
            var currentValue = $this.val();
            var netCost = $this.closest('tr').find('.net-cost').text();
            var updatedCost = netCost.replace("Rs.", "").replace(/,/g, "");
            var subtotal = $this.closest('tr').find('#subtotal').val();

            // var subtotal = inputValue.replace("Rs.", "").replace(/,/g, "");
            // var subtotal = inputValue.replace(/[^\d.]/g, '');
            // let subtotal = parseFloat(inputValue.substring(3));
            var unitcost = parseFloat(subtotal) / parseFloat(currentValue);
            unitcost = formatDecimal(unitcost);
            if (currentValue !== '0') {
                // var variantId = $this.data('variant-id');
                var quantity = currentValue;
                addProductToVarientProduct(variantId, quantity, unitcost);
                $this.data('initial-value', currentValue);
            }
        }.bind(this), index * 100); // Delay each iteration by 100ms * index
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
                sale_action: $('#sale_action').val(),
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
    var warehouseId = $('#slwarehouse').val();
    if (warehouseId) {
        getbillerbyWarehoueseid(warehouseId);
    }
    $('#slwarehouse').on('change', function() {
        var warehouseId = $(this).val();
        getbillerbyWarehoueseid(warehouseId);
    });
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
                        response.primer_biller ? slbiller.val(response.primer_biller).trigger(
                            'change') : slbiller.val(response.billers[0].id).trigger('change');

                    } else {
                        alert(response.message);
                        slbiller.empty(); // Clear existing options if no billers found
                        slbiller.append($('<option>', {
                            value: '',
                            text: 'No Billers Available'
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