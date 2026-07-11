<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .bom-header-info { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ddd; }
    .bom-header-info .row { margin-bottom: 10px; }
    .bom-header-info label { font-weight: bold; margin-bottom: 5px; display: block; }
    .req-badge { display: flex; align-items: center; gap: 5px; }
    .req-badge input[type="number"] { width: 90px !important; height: 30px !important; padding: 4px 8px !important; font-size: 13px !important; line-height: 1; border: 1px solid #ccc; border-radius: 3px; }
    .req-badge span { font-weight: bold; color: #555; }
</style>
<script type="text/javascript">
var count = 1,
    an = 1,
    product_variant = 0,
    DT = <?= $Settings->default_tax_rate ?>,
    allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
    product_tax = 0,
    invoice_tax = 0,
    total_discount = 0,
    total = 0,
    net_price = 0,
    shipping = 0,
    tax_rates = <?php echo json_encode($tax_rates); ?>;
var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
$(document).ready(function() {
    <?php if($this->input->get('customer')) { ?>
    if (!localStorage.getItem('quitems')) {
        localStorage.setItem('qucustomer', <?=$this->input->get('customer');?>);
    }
    <?php } ?>
    <?php if ($Owner || $Admin || $GP['quotes-date'] ) { ?>
    if (!localStorage.getItem('qudate')) {
        $("#qudate").datetimepicker({
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
    $(document).on('change', '#qudate', function(e) {
        localStorage.setItem('qudate', $(this).val());
    });
    if (qudate = localStorage.getItem('qudate')) {
        $('#qudate').val(qudate);
    }
    <?php } ?>
    $(document).on('change', '#qubiller', function(e) {
        localStorage.setItem('qubiller', $(this).val());
    });
    if (qubiller = localStorage.getItem('qubiller')) {
        $('#qubiller').val(qubiller);
    }

    // New Customer Add 
    if (!localStorage.getItem('qucustomer')) {
        var quick_custome_nm = $('#quick_custome_nm').val();
        //alert(quick_custome_nm);
        if (quick_custome_nm.length != '') {
            localStorage.setItem('qucustomer', quick_custome_nm);
        } else {
            localStorage.setItem('qucustomer', <?=$pos_settings->default_customer;?>);
        }
    } else {

        var quick_custome_nm = $('#quick_custome_nm').val();
        if (quick_custome_nm.length != '') {
            localStorage.setItem('qucustomer', quick_custome_nm);
        }

    }
    // End new customer add        

    if (!localStorage.getItem('qutax2')) {
        localStorage.setItem('qutax2', <?=$Settings->default_tax_rate2;?>);
    }
    ItemnTotals();
    $("#add_item").autocomplete({
        source: function(request, response) {
            if (!$('#qucustomer').val()) {
                $('#add_item').val('').removeClass('ui-autocomplete-loading');
                bootbox.alert('<?=lang('select_above');?>');
                //response('');
                $('#add_item').focus();
                return false;
            }
            $.ajax({
                type: 'get',
                url: '<?= site_url('quotes/suggestions'); ?>',
                dataType: "json",
                data: {
                    term: request.term,
                    warehouse_id: $("#quwarehouse").val(),
                    customer_id: $("#qucustomer").val()
                },
                success: function(data) {
                    // RAW ERROR HANDLING
                    if (data.status == 'raw_error') {
                        bootbox.alert(data.msg);
                        $('#add_item').val('');
                        $('#add_item').removeClass('ui-autocomplete-loading');
                        return;
                    }
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
        // select: function(event, ui) {
        //     event.preventDefault();
        //     if (ui.item.id !== 0) {
        //         var row = add_invoice_item(ui.item);
        //         if (row)
        //             $(this).val('');
        //     } else {
        //         bootbox.alert('<?= lang('no_match_found') ?>');
        //     }
        // }


        select: function (event, ui) {
                var selectedItem = $.extend(true, {}, ui.item);

                if (selectedItem.has_bom) {
                    bootbox.dialog({
                        message: "Do you wish to add main Product or Bill of Materials?",
                        title: "Bill of Materials",
                        buttons: {
                            main: {
                                label: "Main Product",
                                className: "btn-primary",
                                callback: function() {
                                    setTimeout(function() {
                                        if (selectedItem.options && Object.keys(selectedItem.options).length > 0) {
                                            product_option_model_call(selectedItem);
                                        } else {
                                            add_invoice_item(selectedItem);
                                        }
                                    }, 400);
                                }
                            },
                            bom: {
                                label: "Bill of Materials",
                                className: "btn-success",
                                callback: function() {
                                    showBOMModal(selectedItem);
                                }
                            }
                        }
                    });
                    $(this).val('');
                    return true;
                }

                if (selectedItem.options && Object.keys(selectedItem.options).length > 0) {
                    product_option_model_call(selectedItem);
                    $(this).val('');
                    return true;
                }
                if (selectedItem.id !== 0) {
                    var row = add_invoice_item(selectedItem);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
    });
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
        var variantPrice = element.price || product.row.price || 0;
        var formattedPrice = parseFloat(variantPrice).toFixed(2);
        var variantRow;
        if (element.name.toLowerCase() == 'note') {
            variantRow = '<tr><td colspan="2" class="text-center">' +
                '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' + element.name +
                '\')">' +
                '<i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i> Note</button>' +
                '</td></tr>';
        } else {
            var variantRow = '<tr>' +
            '<td class="w-25">' + element.name + '</td>' +
            '<td class="w25-center"><input type="number" min="0" value="0" ' +
            'class="form-control input-sm quantity-input quantity_input width-setting" ' +
            'data-variant-id="' + element.id + '" data-netcost="' + variantPrice + '"></td>' +
            '<td class="w25-right net-cost">' + formatMoney(variantPrice) + '</td>' +
            '<td class="w25-right subtotal">' + formatMoney(0) + '</td>' +
            '</tr>';
        }
        product_options += variantRow;
        //console.log(product_options)
    });
    //console.log(variantRow);
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
$(document).on('change', '.quantity-input', function() {
    var row = $(this).closest('tr');
    var quantity = parseFloat($(this).val()) || 0;
    var price = parseFloat($(this).data('netcost')) || 0;
    var subtotal = quantity * price;
    
    row.find('.net-cost').text(formatMoney(price));
    row.find('.subtotal').text(formatMoney(subtotal));
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
    var $row = $this.closest('tr');
    var variantId = $this.data('variant-id');
    var initialValue = $this.data('initial-value');
    var currentValue = $this.val();
    var netCost = $this.closest('tr').find('.net-cost').text();
    var updatedCost = netCost.replace("Rs.", "").replace(/,/g, "");
    var inputValue = $this.closest('tr').find('.subtotal').val(); 
    var subtotal = inputValue.replace("Rs.", "").replace(/,/g, "");

    
    var  unitcost = parseFloat(subtotal)/parseFloat(currentValue);
        unitcost = formatDecimal(unitcost);
    if (currentValue !== '0') {
        // var variantId = $this.data('variant-id');
        var quantity = currentValue;
        addProductToVarientProduct(variantId, quantity,unitcost);
        $this.data('initial-value', currentValue);
    }
}.bind(this), index * 100);  
});
$('.modalvarient').hide();
});

function addProductToVarientProduct(option_id, option_name,unitcost) {
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
    // var variantPrice = parseFloat($quantityInput.data('netcost')); // Make sure this selector works!
    // var subtotal = variantPrice * qty; // compute subtotal
    var itemId = $(".modalvarient").find('.product_item_id').attr("value")
    var term = $(".modalvarient").find('.product_term').val() + "<?php echo $this->Settings->barcode_separator; ?>" +
        option_id + "<?php echo $this->Settings->barcode_separator; ?>" + Product_color;

    wh = $('#wp_id').val(),
        cu = $('#posupplier').val();
    $.ajax({
        type: "get",
        url: "<?= site_url('quotes/suggestions') ?>",
        data: {
            term: term,
            option_id: option_id,
            Product_color: Product_color,
            warehouse_id: wh,
            customer_id: cu,
            option_note: note,  
            quantity: quantity,
            //subtotal: subtotal
        },
        dataType: "json",
        success: function(data) {
    if (data !== null) {
        
        var product = data[0];

        // Fetch variant data from modal DOM
        var $quantityInput = $('.modalvarient').find(`input[data-variant-id="${option_id}"]`);
        var variantPrice = parseFloat($quantityInput.data('netcost'));
        var mrp = parseFloat($quantityInput.data('mrp'));
        var qty = parseFloat($quantityInput.val());

        if (!product.row) product.row = {};
        // Set selected size; set color only if a valid color id is present
        product.row.option = option_id;
        if (typeof Product_color !== 'undefined' && Product_color !== null && Product_color !== '' && Product_color !== '0') {
            product.row.option_color = Product_color;
        }
        // Ensure MRP is populated for display and submission
        if (isNaN(mrp) || !mrp || mrp <= 0) {
            var basePrice = (!isNaN(parseFloat(product.row.price)) && parseFloat(product.row.price) > 0)
                ? parseFloat(product.row.price)
                : variantPrice;
            product.row.mrp = basePrice;
        } else {
            product.row.mrp = mrp;
        }
        // Do not override real_unit_price or unit_price here to avoid zeros; keep values from suggestions
        // Ensure quantity is set on the row object
        if (qty && !isNaN(qty)) {
            product.row.qty = qty;
        }

        console.log("Adding variant", {
    unit_price: product.row.unit_price,
    quantity: product.row.quantity
});

        add_invoice_item(product);
        $('.modalvarient').hide();
    } else {
        bootbox.alert('<?= lang('no_match_found') ?>');
        $('.modalvarient').hide();
    }
}
    });
}
});
var current_bom_items = [];
function showBOMModal(item) {
    $('#bomModalLabel').text('Bill of Materials: ' + item.row.name);
    $('#bom_build_qty').val(1);
    $('#bom_product_id').val(item.row.id);
    var wh = $('#quwarehouse').val();
    
    $.ajax({
        type: 'get',
        url: '<?= site_url('quotes/get_bom_materials'); ?>',
        dataType: "json",
        data: { product_id: item.row.id, warehouse_id: wh },
        success: function(data) {
            current_bom_items = data;
            if (current_bom_items.length > 0) {
                var first = current_bom_items[0];
                $('#bom_base_unit').text(first.batch_unit_name || '');
                $('#bom_yield_unit').text(first.batch_unit_name || '');
                $('#bom_wastage_unit').text(first.batch_unit_name || '');
                $('#bom_sales_unit').text(first.sales_units ? first.sales_units.split(' ')[1] : '');
                
                // Initialize yield and sales based on min_batch_qty and sales_units
                var buildQty = 1;
                $('#bom_build_qty').val(buildQty);
                calculateBOMHeader(buildQty, first);
            }
            renderBOMGrid(1);
            $('#bomModal').modal('show');
        }
    });
}

function calculateBOMHeader(buildQty, bomData) {
    var minBatchQty = parseFloat(bomData.min_batch_qty) || 1;
    var salesUnitsStr = bomData.sales_units || "1";
    var salesUnits = parseFloat(salesUnitsStr.split(" ")[0]) || 1;

    var yieldQty = buildQty;
    var salesQty = (yieldQty * salesUnits) / minBatchQty;
    salesQty = Math.floor(salesQty);

    $('#bom_yield_qty').val(formatDecimal(yieldQty));
    $('#bom_sales_qty').val(formatDecimal(salesQty));
    $('#bom_wastage').val(0);
}

function renderBOMGrid(build_qty) {
    var html = '';
    var total_subtotal = 0;
    $.each(current_bom_items, function(index, item) {
        var min_batch = parseFloat(item.min_batch_qty) || 1;
        var req_qty = (parseFloat(item.quantity_required) / min_batch) * build_qty;
        var net_price = parseFloat(item.net_price) || 0;
        var subtotal = req_qty * net_price;
        total_subtotal += subtotal;
        var unit_name = item.unit_name || '';
        html += '<tr>' +
                '<td class="text-center">' + (index + 1) + '</td>' +
                '<td>' + item.raw_material + ' (' + item.product_code + ')</td>' +
                '<td>' +
                    '<div class="req-badge">' +
                        '<input type="number" step="any" min="0" class="required-qty-input" value="' + formatDecimal(req_qty) + '" data-index="' + index + '">' +
                        '<span>' + unit_name + '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="text-right">' + formatMoney(net_price) + '</td>' +
                '<td class="text-right">' + formatMoney(subtotal) + '</td>' +
                '</tr>';
    });
    $('#bomGridBody').html(html);
}

$(document).on('input', '#bom_build_qty', function() {
    var val = $(this).val();
    if (val !== '' && parseFloat(val) < 0) {
        val = Math.abs(parseFloat(val));
        $(this).val(val);
    }
    var buildQty = parseFloat(val) || 0;
    if (current_bom_items.length > 0) {
        calculateBOMHeader(buildQty, current_bom_items[0]);
    }
    renderBOMGrid(buildQty);
});

$(document).on('keydown', '#bom_build_qty', function(e) {
    if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
        e.preventDefault();
    }
});

$(document).on('input', '.required-qty-input', function() {
    var val = $(this).val();
    if (val !== '' && parseFloat(val) < 0) {
        val = Math.abs(parseFloat(val));
        $(this).val(val);
    }
    var index = $(this).data('index');
    var newQty = parseFloat(val) || 0;
    var netPrice = parseFloat(current_bom_items[index].net_price) || 0;
    var newSubtotal = newQty * netPrice;
    $(this).closest('tr').find('td:last').text(formatMoney(newSubtotal));
});

$(document).on('keydown', '.required-qty-input', function(e) {
    if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
        e.preventDefault();
    }
});

$(document).on('click', '#bomSubmitBtn', function() {
    $('.required-qty-input').each(function() {
        var index = $(this).data('index');
        var qty = parseFloat($(this).val()) || 0;
        if (qty > 0) {
            var item = current_bom_items[index];
            if (item && item.details) {
                var newItem = {
                    id: item.material_id,
                    item_id: item.material_id,
                    label: item.raw_material + ' (' + item.product_code + ')',
                    row: {
                        id: item.material_id,
                        code: item.product_code,
                        name: item.raw_material,
                        qty: qty,
                        quantity: qty,
                        base_quantity: qty,
                        price: item.net_price,
                        unit_price: item.net_price,
                        real_unit_price: item.net_price,
                        base_unit_price: item.net_price,
                        tax_rate: item.tax_rate,
                        tax_method: item.tax_method,
                        discount: '0',
                        unit: item.details.unit,
                        unit_lable: item.unit_name,
                        base_unit: item.details.unit,
                        type: item.details.type,
                        image: item.details.image,
                        hsn_code: item.details.hsn_code
                    },
                    tax_rate: item.tax_rate_details,
                    units: item.units,
                    options: false
                };
                add_invoice_item(newItem);
            }
        }
    });
    $('#bomModal').modal('hide');
});

$('#bomModal').on('shown.bs.modal', function () {
    $('#bom_build_qty').focus();
});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_quote'); ?></h2>
    </div>
    <p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">


                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("quotes/add", $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <?php if ($Owner || $Admin || $GP['quotes-date']) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("date", "qudate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="qudate" required="required"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("reference_no", "quref"); ?>
                                    <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $qunumber), 'class="form-control input-tip" id="quref"'); ?>
                                </div>
                            </div>
                            <?php //if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("warehouse", "quwarehouse"); ?>
                                    <?php
                                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
		                                    //$wh[''] = '';
		                                    
		                                    foreach ($warehouses as $warehouse) {
		                                        if($Owner || $Admin  ){
		                                            $wh[$warehouse->id] = $warehouse->name;
		                                        }elseif (in_array($warehouse->id,$permisions_werehouse)) {
		                                            $wh[$warehouse->id] = $warehouse->name;
		                                        }
		                                    }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="quwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                                ?>
                                </div>
                            </div>
                            <?php /*} else {
                                        $warehouse_input = array(
                                            'type' => 'hidden',
                                            'name' => 'warehouse',
                                            'id' => 'quwarehouse',
                                           'value' => $this->session->userdata('warehouse_id'),
                                        );
                                        echo form_input($warehouse_input);
                                    }*/ ?>
                             <?php if ($Owner || $Admin || $this->session->userdata('biller_id')) { ?>
                             <div class="col-md-3">
                                 <div class="form-group">
                                     <?= lang("biller", "qubiller"); ?>
                                     <?php
                                                     $bl[""] = "";
                                                     foreach ($billers as $biller) {
                                                         $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                                     }
                                                     echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="qubiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                     ?>
                                 </div>
                             </div>
                             <?php } else {
                                             $biller_input = array(
                                                 'type' => 'hidden',
                                                 'name' => 'biller',
                                                 'id' => 'qubiller',
                                                 'value' => $this->session->userdata('biller_id'),
                                             );
                                             echo form_input($biller_input);
                                         } ?>


                            <?php if ($Settings->tax2) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("order_tax", "qutax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="qutax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <?php } ?>

                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("discount", "qudiscount"); ?>
                                    <?php echo form_input('discount', '', 'class="form-control input-tip" id="qudiscount"'); ?>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("shipping", "qushipping"); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="qushipping"'); ?>

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

                            <div class="col-md-12">
                                <div class="panel panel-warning">
                                    <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?>
                                    </div>
                                    <div class="panel-body" style="padding: 5px;">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("status", "qustatus"); ?>
                                                <?php $st = array('pending' => lang('pending'), 'sent' => lang('sent'));
                                echo form_dropdown('status', $st, '', 'class="form-control input-tip" id="qustatus"'); ?>

                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("supplier", "qusupplier"); ?>
                                                <input type="hidden" name="supplier" value="" id="qusupplier"
                                                    class="form-control" style="width:100%;"
                                                    placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                                <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("customer", "qucustomer",'id="lable_red"'); ?>
                                                <div class="input-group" id="customer_red">
                                                    <?php
                                                echo form_input('customer', (isset($_SESSION['quick_customername']) ? $_SESSION['quick_customername'] : (isset($_POST['customer']) ? $_POST['customer'] : $default_customer_name)), 'id="qucustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"onkeypress="return onclick(event,this);" type="text" ondrop="return false;" onpaste="return false;"'); ?>
                                                    <input type="hidden" name="quick_custome_nm" id="quick_custome_nm"
                                                        value="<?php echo (isset($_SESSION['quick_customerid']) ? $_SESSION['quick_customerid'] : $default_customer_id); ?>">
                                                    <div class="input-group-addon no-print"
                                                        style="padding: 2px 8px; border-left: 0;">
                                                        <a href="#" id="toogle-customer" class="external edit-customers"
                                                            data-target="#myModal" data-toggle="modal">
                                                            <i class="fa fa-pencil" id="addIcon"
                                                                style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                    <div class="input-group-addon no-print"
                                                        style="padding: 2px 7px; border-left: 0;">
                                                        <a href="#" id="view-customer" class="external"
                                                            data-toggle="modal" data-target="#myModal">
                                                            <i class="fa fa-eye" id="addIcon"
                                                                style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                    <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?= site_url('customers/add?redirect_url=' . urlencode(current_url())); ?>" id="add-customer"
                                                            data-base-href="<?= site_url('customers/add'); ?>"
                                                            data-redirect-url="<?= htmlspecialchars((string) current_url(), ENT_QUOTES, 'UTF-8'); ?>"
                                                            class="external" data-toggle="modal" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon"
                                                                style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                                <span id="error"
                                                    style="color:#a94442;font-size:11px; display: none">Please
                                                    enter/select a value</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>


                            <div class="col-md-12" id="sticker">
                                <div class="well well-sm">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <div class="input-group wide-tip">
                                            <div class="input-group-addon"
                                                style="padding-left: 10px; padding-right: 10px;">
                                                <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                            </div>
                                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                            <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon"
                                                style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually" class="tip"
                                                    title="<?= lang('add_product_manually') ?>"><i
                                                        class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="control-group table-group">
                                    <label class="table-label"><?= lang("order_items"); ?> *</label>

                                    <div class="controls table-controls">
                                        <table id="quTable"
                                            class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                            <thead>
                                                <tr>
                                                    <th class="col-md-4">
                                                        <?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?>
                                                    </th>

                                                    <th class="col-md-1"><?= lang("mrp"); ?></th>
                                                    <th class="col-md-1"><?= lang("unit_price"); ?></th>
                                                    <th class="col-md-1"><?= lang("quantity"); ?> (Unit)</th>
                                                    <th class="col-md-1"><?= lang("Net Price "); ?></th>
                                                    <?php
                                            if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                                    <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-2">' . $this->lang->line("product_tax") . '</th>';
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

                            <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                        </div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group" style="max-width:100%;">
                                            <?= lang("note", "qunote"); ?>
                                            <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qunote" style="margin-top: 10px; height: 100px;"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="fprom-group">
                                    <?php echo form_submit('add_quote', $this->lang->line("submit"), 'id="add_quote" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span>
                            </td>
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
</div>

<style>
.buildset {
    padding: 10px 20px;
    background: #f9f9f9;
    border-bottom: 1px solid #ddd;
    display: flex;
    align-items: center;
    gap: 20px;
}
.qty-badge {
    background-color: #d20c06;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap; 
    font-size: 12px;
}
.qty-text {
    color: #333;
}
.req-badge {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    font-weight: 500;
    color: #333;
    font-size: medium;
}
.req-badge span:first-child { 
    margin-right: 5px;
}
.required-qty-input {
    width: 80px;
    text-align: center;
    margin-right: 5px;
    border-radius: 3px;
    border: 1px solid #ccc;
}
#bomModal .modal-header {
    padding: 15px 20px;
    background: #f7f7f7;
    border-bottom: 1px solid #ddd;
}
#bomModal .modal-title {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 18px;
}
.static-head {
    background: #fff;
    border-bottom: 1px solid #ddd;
}
.buildset {
    padding: 15px 20px;
    display: flex;
    gap: 30px;
    align-items: center;
    flex-wrap: wrap;
}
.batch-like-input {
    width: 100px;
    padding: 5px;
    border: 1px solid #050505ff;
    border-radius: 4px;
}
.unit-text {
    font-weight: bold;
    margin-left: 5px;
}
#bomModal {
    z-index: 10001 !important;
}
body.modal-open a#main-menu-act {
    display: none !important;
}
</style>



        </div>
    </div>
</div>


<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                            <th style="width:25%;"><?= lang('Unit Cost'); ?></th>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bomModal" tabindex="-1" role="dialog" aria-labelledby="bomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="static-head">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="bomModalLabel">Bill of Materials</h4>
                </div>
                <div class="buildset">
                    <!-- Build Qty -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="font-weight: bold; font-size: 16px; border: 2px solid #000; border-radius: 10px; padding: 5px 15px;">
                            <span>Build Qty:-</span>
                            <input type="number" id="bom_build_qty" value="1" min="1" step="any" style="width: 80px; border: 1px solid #ccc; outline: none; font-weight: bold; font-size: 18px; text-align: center; background: #fff; border-radius: 4px;">
                            <span id="bom_base_unit" class="unit-text"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bom_product_id">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Raw Material</th>
                                <th style="width:150px;">Qty Required</th>
                                <th style="width:120px;">Net Price</th>
                                <th style="width:120px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="bomGridBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="bomSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                    <div class="form-group ">
                        <label for="munit" class="col-sm-4 control-label"><?= lang('product_unit', 'unit'); ?> *</label>
                        <div class="col-sm-8">
                            <?php
                        $pu[''] = lang('select') . ' ' . lang('unit');
                        foreach ($base_units as $bu) {
                            $pu[$bu->id] = $bu->name;
                        }
                        echo form_dropdown('munit', $pu, "", 'id="munit" class="form-control input-tip select"   style="width:100%;"');
                        ?>
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
<script>
    function modalClose(modalClass) {
    $('.' + modalClass).hide();
}
$(document).ready(function() {
    $("#add_item").click(function() {
        var cust_field = $("#qucustomer").val();
        if (cust_field == "") {

            $("#customer_red").css({
                "border": "1.5px solid #a94442"
            });

            $("#lable_red").css({
                "color": "#a94442"
            });
            document.getElementById("error").style.display = cust_field ? "none" : "inline";
            return false;
        } else {

            $("#lable_red").css({
                "color": "#2b542c"
            });
            $("#customer_red").css({
                "border": "1.5px solid #2b542c"
            });

            document.getElementById("error").style.display = cust_field ? "none" : "none";
            return true;
        }
    });
});
</script>
<script>
$(document).ready(function() {
    function syncQuoteAddCustomerModalLink(billerId) {
        var $link = $('#add-customer');
        if (!$link.length) {
            return;
        }
        var baseHref = $link.attr('data-base-href') || "<?= site_url('customers/add'); ?>";
        var redirectUrl = $link.attr('data-redirect-url') || "<?= htmlspecialchars((string) current_url(), ENT_QUOTES, 'UTF-8'); ?>";
        var href = baseHref + '?redirect_url=' + encodeURIComponent(redirectUrl) + '&source_module=sales';
        if (billerId) {
            href += '&biller_id=' + encodeURIComponent(String(billerId));
        }
        $link.attr('href', href);
    }

    var warehouseId = $('#quwarehouse').val();
    if (warehouseId) {
        getbillerbyWarehoueseid(warehouseId);
    }
    syncQuoteAddCustomerModalLink($('#qubiller').val());
    $('#quwarehouse').on('change', function() {
        var warehouseId = $(this).val();
        getbillerbyWarehoueseid(warehouseId);
    });
    $('#qubiller').on('change', function() {
        var biller_name = $(this).val();
        syncQuoteAddCustomerModalLink(biller_name);
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
                    const slbiller = $('#qubiller');
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