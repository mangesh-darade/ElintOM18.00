<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');
$v = '';
if($this->input->post('warehouse'))
{
    $v .= "&warehouse=" . $this->input->post('warehouse');
}else{
    $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'': "&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if($this->input->post('stock_alert'))
{
    $v .= "&stock_alert=" . $this->input->post('stock_alert');
}
if($this->input->post('supplier'))
{
    $v .= "&supplier=" . $this->input->post('supplier');
}

?>
<style>
    /* Bigger checkboxes in Quantity Alerts table */
#PQData input[type="checkbox"] {
    transform: scale(2);   /* increase size */
    -webkit-transform: scale(2);
    margin: 0;
    cursor: pointer;
}
.box .box-content p.introtext {
    margin: -20px -20px 20px -10px !important;
}
</style>
<script>
/**
 * ============================================================================
 * UPDATE CREATE PURCHASE ORDER BUTTON STATE
 * ============================================================================
 * 
 * PURPOSE:
 * Dynamically enables/disables the "Create Purchase Order" button based on
 * user selections. Button is only enabled when both conditions are met.
 * 
 * BUSINESS RULES:
 * 1. At least one product must be selected (checkbox checked)
 * 2. A supplier must be selected from dropdown
 * 
 * Both conditions must be TRUE for button to be enabled.
 * 
 * CALLED FROM:
 * - Checkbox change events (when user selects/deselects products)
 * - Supplier dropdown change event
 * - DataTables draw callback (after table loads/refreshes)
 * ============================================================================
 */
function updateCreatePOButton() {
    try {
        // Check condition 1: Are there any checked checkboxes?
        // Business Logic: Count all checked checkboxes with class 'multi-select'
        var hasSelectedItems = $('.multi-select:checked').length > 0;
        
        // Check condition 2: Is supplier selected?
        // Business Logic: Supplier dropdown must have a value (not empty or null)
        var supplierSelected = $('#supplier').val() != '' && $('#supplier').val() != null;
        
        // Button should be enabled only if BOTH conditions are true
        // Business Rule: Both product selection AND supplier selection are mandatory
        var shouldEnable = hasSelectedItems && supplierSelected;
        
        console.log('Update button - Selected items:', hasSelectedItems, 'Supplier:', supplierSelected, 'Enable:', shouldEnable);
        
        // Update button state and visual appearance
        var $btn = $('#create_po_btn');
        if ($btn.length) {
            // Set disabled property based on conditions
            $btn.prop('disabled', !shouldEnable);
            
            if (shouldEnable) {
                // ENABLED STATE: Full opacity, pointer cursor, clickable
                $btn.removeClass('disabled').css('opacity', '1').css('cursor', 'pointer');
            } else {
                // DISABLED STATE: Reduced opacity, not-allowed cursor, not clickable
                $btn.addClass('disabled').css('opacity', '0.6').css('cursor', 'not-allowed');
            }
        }
    } catch(e) {
        console.error('Error in updateCreatePOButton:', e);
    }
}

$(document).ready(function() {

    var oTable = $('#PQData').dataTable({

        "aaSorting": [
            [1, "desc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "<?= lang('all') ?>"]
        ],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= site_url('reports/getQuantityAlerts/?v=1' . $v) ?>',
        'fnServerData': function(sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
        },
        "aoColumns": [
            { "bSortable": false, "bSearchable": false, "mRender": checkbox }, // 0 checkbox
            { "bSortable": false, "bSearchable": false, "mRender": img_hl }, // 1 image
            null,                                       // 2 product_code
            null,                                       // 3 category_name
            null,                                       // 4 product_name
            { "bSortable": false },                   // 5 variant_name
            null,                                       // 6 supplier_name
            { "bSortable": false, "mRender": formatQuantity }, // 7 alert_quantity
            { "mRender": formatQuantity },          // 8 stock_quantity
            { "bVisible": false }                  // 9 variant_id (hidden)
        ],

        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {

            var idxAlert = 7;  // Updated: alert_quantity is now at index 7 (was 6)
            var idxStock = 8;  // Updated: stock_quantity is now at index 8 (was 7)

            // Visual indicators based on percentage of Reorder_QTY (alert_quantity):
            // - Red   : quantity <= alert_quantity (at or below reorder level)
            // - Yellow: alert_quantity < quantity <= alert_quantity * (1 + ALERT_PERCENT/100)
            // - Gray  : quantity > that upper threshold
         // Get the percentage from "Stock Alert Variance(%)" input
            var expiryInput    = document.getElementById('alert_quantity');
            var quantity       = parseFloat(aData[idxStock]) || 0;
            var alert_quantity = parseFloat(aData[idxAlert]) || 0;
            var variancePct    = expiryInput ? parseFloat(expiryInput.value) || 0 : 0; // Stock Alert Variance(%)

            // Compute upper threshold based on percentage variance.
            // Example: alert_quantity = 100, variancePct = 10  => upperLimit = 110
            var upperLimit = alert_quantity;
            if (alert_quantity > 0 && variancePct > 0) {
                upperLimit = alert_quantity * (1 + (variancePct / 100));
            }
        // IMPORTANT: reset flags on every draw
            $(nRow).removeClass('low-stock medium-stock');

            if (quantity <= alert_quantity) {
                $(nRow)
                    .addClass('low-stock')
                    .css('background-image', 'linear-gradient(white, #ff5454)');
            }
            else if (quantity <= upperLimit) {
                $(nRow)
                    .addClass('medium-stock')
                    .css('background-image', 'linear-gradient(white, #f3ff54)');
            }
            else {
                $(nRow).css('background-image', 'linear-gradient(white, #cccccc)');
            }
            $(nRow).find('td').eq(0).css('background-color', 'white');

        },
        "fnDrawCallback": function(oSettings) {
            // Update button state after table is drawn/redrawn
            setTimeout(function() {
                try {
                    var supplierSelected = $('#supplier').val();

                // Reset all checkboxes first
                $('#select_all').prop('checked', false);
                $('.multi-select').prop('checked', false);

                if (supplierSelected) {

                    // Select ONLY red + yellow rows
                    $('#PQData tbody tr.low-stock, #PQData tbody tr.medium-stock')
                        .find('.multi-select')
                        .prop('checked', true);

                    // If ALL visible rows are selected → check select_all
                    var total = $('.multi-select').length;
                    var checked = $('.multi-select:checked').length;

                    $('#select_all').prop('checked', total > 0 && total === checked);
                }

                    updateCreatePOButton();
                } catch(e) {
                    console.error('Error updating button in fnDrawCallback:', e);
                }
            }, 100);
        },
        "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var reordertotal = 0, stocktotal = 0;
                for (var i = 0; i < aaData.length; i++) {
                    reordertotal += parseFloat(aaData[aiDisplay[i]][7]);
                    stocktotal += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = formatQuantity(reordertotal);
                nCells[8].innerHTML = formatQuantity(stocktotal);
        }
    }).fnSetFilteringDelay().dtFilter([
        {
            column_number: 2,
            filter_default_label: "[<?=lang('product_code');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 3,
            filter_default_label: "[<?=lang('Category_Name');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 4,
            filter_default_label: "[<?=lang('product_name');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 5,
            filter_default_label: "[<?=lang('variant_name');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 6,
            filter_default_label: "[<?=lang('supplier');?>]",
            filter_type: "text",
            data: []
        },
        // {
        //     column_number: 7,
        //     filter_default_label: "[<?=lang('Reorder_QTY');?>]",
        //     filter_type: "text",
        //     data: []
        // },
        // {
        //     column_number: 8,
        //     filter_default_label: "[<?=lang('Stock_QTY');?>]",
        //     filter_type: "text",
        //     data: []
        // },
    ], "footer");
});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-calendar-o"></i><?= lang('Product_Stock_Alerts') . ' (' . ($warehouse_id ? (isset($warehouse[$warehouse_id]->name)?$warehouse[$warehouse_id]->name:lang('all_warehouses')) : lang('all_warehouses')) . ')'; ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <?php //if (!empty($warehouses)) { ?>
                <li class="dropdown">
                    <!-- <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-building-o tip" data-placement="left"
                            title="<?= lang("warehouses") ?>"></i>
                    </a> -->
                    <!-- <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel"> -->
                        <!-- <li>
                            <a href="<?= site_url('reports/quantity_alerts') ?>">
                                <i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?>
                            </a>
                        </li> -->
                        <!-- <li class="divider"></li> -->
                        <?php
                            // $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            // foreach ($warehouses as $warehouse) {
                            //     if($Owner || $Admin   ){
                            //         echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/quantity_alerts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            //     }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                            //         echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/quantity_alerts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';

                            //     }
                                 
                            // }
                            ?>
                    </ul>
                </li>
                <?php //} ?>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <!-- <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li> -->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div id="form" style="margin-bottom: 25px; margin-right: 14px !important; margin-left: 29px !important;">
                <?php echo form_open("reports/quantity_alerts","id='searchproduct'"); ?>
                <div class="row">
                    <div class="col-sm-4" style ="padding-left: 1px !important;">
                        <div class="form-group">
                            <label class="control-label" for="warehouse"><?= lang("Location"); ?></label>
                            <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select') . ' ' . lang('Location');
                                foreach($warehouses as $warehouse)
                                {
                                	if($Owner || $Admin ){
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }else if(in_array($warehouse->id,$permisions_werehouse)){
                                           $wh[$warehouse->id] = $warehouse->name;
                                        }    
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                        </div>
                    </div>
                    <!-- <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang('Stock Alert ', 'stock_alert'); ?>
                            <?php $arr1 = array('0'=>'Select Stock Alert','1'=>'Below Stock Quantity','2'=>'Above Stock Quantity','3'=>'Stock less than reorder quantity');

                           
                            //  form_dropdown('stock_alert', $arr1, $pos->pos_screen_products, 'class="form-control" id="stock_alert" style="width:100%;"');
                            echo form_dropdown('stock_alert', $arr1, (isset($_POST['stock_alert']) ? $_POST['stock_alert'] : ""), 'class="form-control" id="stock_alert" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("stock_alert") . '"');
                            ?>
                        </div>
                    </div> -->
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label" for="supplier"><?= lang("supplier"); ?></label>
                            <?php
                                $supp[""] = lang('select') . ' ' . lang('supplier');
                                if(isset($suppliers) && !empty($suppliers)) {
                                    foreach($suppliers as $supplier) {
                                        $supp[$supplier->id] = $supplier->name;
                                    }
                                }
                                echo form_dropdown('supplier', $supp, (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"');
                                ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label" for="alert_quantity">
                                    <?= lang("Stock Alert Variance(%)"); ?>
                            </label>
                            <div class="alert-qty-wrapper">
                                <input type="number" min="0" max="100" step="0.1" name="alert_quantity" id="alert_quantity" class="form-control alert_quantity" style="background-color: #f7f338; " value="<?= isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : 0 ?>" onblur="fixMinValue(this)" required>
                            </div>
                                <small id="alert_quantity_error"
                                class="text-danger"
                                style="display:none;">
                                <?= lang("Stock Alert Variance is required.") ?>
                            </small>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>

                            <a href="<?= site_url('reports/quantity_alerts') ?>" type="reset" id="report_reset"
                                class="btn btn-warning input-xs">Reset </a>
                            
                            <button type="button" id="create_po_btn" class="btn btn-success input-xs" disabled style="opacity: 0.6; cursor: not-allowed;">
                                <i class="fa fa-shopping-cart"></i> Generate PO
                            </button>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="PQData" cellpadding="0" cellspacing="0" border="0"
                        class="table table-bordered table-condensed table-hover  dfTable reports-table">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input type="checkbox" id="select_all" class="checkbox" /></th>
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?php echo $this->lang->line("image"); ?></th>
                                <th><?php echo $this->lang->line("product_code"); ?></th>
                                <th><?php echo $this->lang->line("Category_Name"); ?></th>
                                <th><?php echo $this->lang->line("product_name"); ?></th>
                                <th><?php echo $this->lang->line("Variant _Name"); ?></th>
                                <th><?php echo $this->lang->line("supplier"); ?></th>
                                <th><?php echo $this->lang->line("Reorder_QTY"); ?></th>
                                <th><?php echo $this->lang->line("Stock_QTY"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>

                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;"></th>
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?php echo $this->lang->line("image"); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
     // Always hide form initially
    $('#form').show();

    // If supplier was submitted → auto open form on page load
    <?php if ($this->input->post('supplier')): ?>
        $("#form").slideDown();
    <?php endif; ?>

    $('.toggle_down').click(function () {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function () {
        $("#form").slideUp();
        return false;
    });

    // $('#pdf').click(function(event) {
    //     event.preventDefault();
    //     window.location.href =
    //         "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/pdf')?>";
    //     return false;
    // });
    // $('#xls').click(function(event) {
    //     event.preventDefault();
    //     window.location.href =
    //         "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/xls')?>";
    //     return false;
    // });
    // $('#image').click(function(event) {
    //     event.preventDefault();
    //     window.location.href =
    //         "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/0/img')?>";
    //     /* html2canvas($('.box'), {
    //          onrendered: function (canvas) {
    //              var img = canvas.toDataURL()
    //              window.open(img);
    //          }
    //      });*/
    //     return false;
    // });
    /**
     * ============================================================================
     * COLLECT SELECTED ITEMS FOR EXPORT
     * ============================================================================
     * Collects product_id and variant_id from checked checkboxes
     * Returns array of {product_id, variant_id} objects
     * ============================================================================
     */
    function getSelectedItemsForExport() {
        var selectedItems = [];
        
        $('.multi-select:checked').each(function() {
            var productId = $(this).val();
            var row = $(this).closest('tr');
            var variantId = 0;
            
            try {
                var table = $('#PQData').dataTable();
                var rowIndex = table.fnGetPosition(row[0]);
                
                if (rowIndex !== null && rowIndex >= 0) {
                    var rowData = table.fnGetData(rowIndex);
                    
                    // Get variant_id from column 9 (hidden column)
                    if (rowData && rowData.length > 9) {
                        variantId = parseInt(rowData[9]) || 0;
                    } else if (rowData && rowData.length > 8) {
                        variantId = parseInt(rowData[8]) || 0;
                    }
                }
            } catch(e) {
                console.error('Error getting variant_id:', e);
                variantId = 0;
            }
            
            if (productId) {
                selectedItems.push({
                    product_id: parseInt(productId),
                    variant_id: parseInt(variantId) || 0
                });
            }
        });
        
        return selectedItems;
    }

    $('#pdf').click(function(event) {
        event.preventDefault();
        
        // Collect selected items
        var selectedItems = getSelectedItemsForExport();
        
        if (selectedItems.length === 0) {
            bootbox.alert('<?= lang('please_select_items') ?>');
            return false;
        }
        
        // Build URL with selected items as JSON parameter
        var baseUrl = "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/pdf')?>";
        var params = {
            selected_items: JSON.stringify(selectedItems),
            warehouse: $('#warehouse').val() || '',
            stock_alert: $('#stock_alert').val() || '',
            supplier: $('#supplier').val() || ''
        };
        
        var queryString = $.param(params);
        window.location.href = baseUrl + '?' + queryString;
        return false;
    });
    
    $('#xls').click(function(event) {
        event.preventDefault();
        
        // Collect selected items
        var selectedItems = getSelectedItemsForExport();
        
        if (selectedItems.length === 0) {
            bootbox.alert('<?= lang('please_select_items') ?>');
            return false;
        }
        
        // Build URL with selected items as JSON parameter
        var baseUrl = "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/xls')?>";
        var params = {
            selected_items: JSON.stringify(selectedItems),
            warehouse: $('#warehouse').val() || '',
            stock_alert: $('#stock_alert').val() || '',
            supplier: $('#supplier').val() || ''
        };
        
        var queryString = $.param(params);
        window.location.href = baseUrl + '?' + queryString;
        return false;
    });

    // Select all checkbox functionality
    $(document).on('click', '#select_all', function() {
        var isChecked = $(this).is(':checked');
        $('.multi-select').prop('checked', isChecked);
        updateCreatePOButton();
    });

    // Update select all checkbox when individual checkboxes change
    $(document).on('change', '.multi-select', function() {
        var totalCheckboxes = $('.multi-select').length;
        var checkedCheckboxes = $('.multi-select:checked').length;
        $('#select_all').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateCreatePOButton();
    });

    // Update button when supplier changes (use event delegation for dynamically loaded elements)
    $(document).on('change', '#supplier', function() {
        console.log('Supplier changed:', $(this).val());
        updateCreatePOButton();
    });

    /**
     * ============================================================================
     * PURCHASE ORDER CREATION FUNCTIONALITY
     * ============================================================================
     * 
     * BUSINESS LOGIC FLOW:
     * 1. User selects products from quantity alerts table (checkboxes)
     * 2. User selects a supplier from dropdown
     * 3. User clicks "Create Purchase Order" button
     * 4. System collects selected products with their variant IDs
     * 5. System sends AJAX request to backend to get product details
     * 6. Backend processes products and returns formatted purchase order items
     * 7. System stores items in localStorage and redirects to purchase order form
     * 8. Purchase order form loads items from localStorage automatically
     * 
     * KEY BUSINESS RULES:
     * - Products WITH variants: Only the selected variant is added (e.g., "T-Shirt - S")
     * - Products WITHOUT variants: Product is added as single item
     * - Supplier must be selected before creating PO (required for GST/IGST calculations)
     * - Each selected row represents one product/variant combination
     * ============================================================================
     */
    
    // Create Purchase Order button click handler
    $(document).on('click', '#create_po_btn', function(e) {
        e.preventDefault();
        
        // ========================================================================
        // STEP 1: VALIDATE SUPPLIER SELECTION
        // ========================================================================
        // Business Rule: Supplier is mandatory for purchase orders
        // Reason: Required for GST/IGST tax calculations and supplier information
        var supplierId = $('#supplier').val();
        if (!supplierId) {
            bootbox.alert('<?= lang('please_select_supplier') ?>');
            return false;
        }

        // ========================================================================
        // STEP 2: COLLECT SELECTED PRODUCTS FROM TABLE
        // ========================================================================
        // Business Logic: Iterate through all checked checkboxes in the table
        // Each checkbox contains product_id as its value
        // We need to extract variant_id from the DataTables row data
        var selectedItems = [];
        
        $('.multi-select:checked').each(function() {
            // Get product_id from checkbox value (set by checkbox() function in core.js)
            var productId = $(this).val();
            var row = $(this).closest('tr');
            var variantId = 0; // Default to 0 for products without variants
            
            // ====================================================================
            // STEP 2a: EXTRACT VARIANT ID FROM DATATABLES ROW DATA
            // ====================================================================
            // Business Logic: 
            // - Products with variants have variant_id in column 9 (hidden)
            // - Products without variants have variant_id = 0
            // - We need variant_id to add the correct variant to purchase order
            // 
            // Column order in DataTables:
            // 0=checkbox, 1=image, 2=code, 3=category, 4=product_name, 
            // 5=variant_name, 6=supplier_name, 7=alert_qty, 8=stock_qty, 9=variant_id (hidden)
            try {
                var table = $('#PQData').dataTable();
                // Get the row index in DataTables internal structure
                var rowIndex = table.fnGetPosition(row[0]);
                
                if (rowIndex !== null && rowIndex >= 0) {
                    // Get all column data for this row
                    var rowData = table.fnGetData(rowIndex);
                    qty = parseInt(rowData[7]) || 0;
                    console.log('Full row data:', rowData);
                    
                    // Primary method: Get variant_id from column 9 (updated from 8)
                    if (rowData && rowData.length > 9) {
                        variantId = parseInt(rowData[9]) || 0;
                        console.log('Row data - Product ID:', productId, 'Variant ID:', variantId, 'Row data length:', rowData.length);
                    } 
                    // Fallback method: Try column 8 if column 9 doesn't exist (backward compatibility)
                    else if (rowData && rowData.length > 8) {
                        variantId = parseInt(rowData[8]) || 0;
                        console.log('Using fallback column 8 - Variant ID:', variantId);
                    }
                } else {
                    // Alternative method: Try to get variant_id from row attributes or DOM
                    // This is a fallback if DataTables API doesn't work
                    var $row = $(row);
                    var variantIdAttr = $row.find('td').eq(9).text() || $row.data('variant-id') || 0;
                    if (variantIdAttr) {
                        variantId = parseInt(variantIdAttr) || 0;
                        console.log('Got variant ID from row attribute:', variantId);
                    }
                }
            } catch(e) {
                console.error('Error getting variant_id:', e);
                variantId = 0; // Default to 0 if extraction fails
            }
            
            // ====================================================================
            // STEP 2b: BUILD ITEM OBJECT FOR BACKEND
            // ====================================================================
            // Business Logic: Create object with product_id and variant_id
            // Backend will use this to fetch product details and create PO items
            if (productId) {
                selectedItems.push({
                    product_id: parseInt(productId),    // Required: Product identifier
                    variant_id: parseInt(variantId) || 0, // Required: Variant identifier (0 = no variant)
                    qty: parseInt(qty) || 0, 
                });
                console.log('Added item - Product ID:', productId, 'Variant ID:', variantId);
            }
        });

        // ========================================================================
        // STEP 3: VALIDATE SELECTIONS
        // ========================================================================
        // Business Rule: At least one product must be selected
        if (selectedItems.length === 0) {
            bootbox.alert('<?= lang('please_select_items') ?>');
            return false;
        }

        console.log('Selected items:', selectedItems);

        // ========================================================================
        // STEP 4: PREPARE UI FOR PROCESSING
        // ========================================================================
        // Business Logic: Disable button and show loading state
        // Prevents multiple clicks and provides user feedback
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        // ========================================================================
        // STEP 5: SEND AJAX REQUEST TO BACKEND
        // ========================================================================
        // Business Logic: 
        // - Send selected items and supplier_id to backend
        // - Backend will fetch product details, variants, costs, taxes, etc.
        // - Backend returns formatted purchase order items ready for PO form
        $.ajax({
            url: '<?= site_url('reports/getSelectedProductsData') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                items: JSON.stringify(selectedItems),  // Array of {product_id, variant_id}
                supplier_id: supplierId,                // Selected supplier ID
                <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>' // CSRF protection
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                
                // ================================================================
                // STEP 6: PROCESS SUCCESS RESPONSE
                // ================================================================
                // Business Logic: Backend returns formatted PO items
                // Store in localStorage so purchase order form can load them
                if (response && response.status === 'success') {
                    // Store purchase order items in localStorage
                    // Key: 'poitems' - Used by purchases/add.php to load items
                    localStorage.setItem('poitems', JSON.stringify(response.items));
                    
                    // Store supplier ID in localStorage
                    // Key: 'posupplier' - Used by purchases/add.php to pre-fill supplier
                    localStorage.setItem('posupplier', supplierId);
                    
                    // ============================================================
                    // STEP 7: REDIRECT TO PURCHASE ORDER FORM
                    // ============================================================
                    // Business Logic: Redirect to purchase order add page
                    // The form will automatically load items from localStorage
                    // Supplier will be pre-filled from URL parameter and localStorage
                    window.location.href = '<?= site_url('purchases/add') ?>?supplier=' + supplierId;
                } else {
                    // Handle error response from backend
                    var errorMsg = (response && response.message) ? response.message : (response && response.error) ? response.error : '<?= lang('error_occurred') ?>';
                    bootbox.alert(errorMsg);
                    $btn.prop('disabled', false).html('<i class="fa fa-shopping-cart"></i> Generate PO');
                }
            },
            error: function(xhr, status, error) {
                // ================================================================
                // STEP 8: HANDLE AJAX ERRORS
                // ================================================================
                // Business Logic: Show error message and restore button state
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                
                var errorMsg = '<?= lang('error_occurred') ?>';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {
                    errorMsg = xhr.responseText || error || '<?= lang('error_occurred') ?>';
                }
                bootbox.alert(errorMsg);
                $btn.prop('disabled', false).html('<i class="fa fa-shopping-cart"></i> Generate PO');
            }
        });
        
        return false;
    });

    // Initialize button state - check multiple times as DataTables loads data
    setTimeout(function() {
        updateCreatePOButton();
    }, 500);
    
    setTimeout(function() {
        updateCreatePOButton();
    }, 1000);
    
    setTimeout(function() {
        updateCreatePOButton();
    }, 2000);
});
</script>
<script>
function fixMinValue(el) {
    var value = parseFloat(el.value);
    var $error = $('#alert_quantity_error');

    if (el.value.trim() === '' || isNaN(value) || value < 0) {
        $error.show();
        $(el).addClass('has-error');
        return false;
    } else {
        $error.hide();
        $(el).removeClass('has-error');
    }
}
// $('#searchproduct').on('submit', function (e) {
//     var value = parseFloat($('#alert_quantity').val());

//     if (isNaN(value) || value < 1) {
//         e.preventDefault();
//         $('#alert_quantity_error').show();
//         $('#alert_quantity').addClass('has-error').focus();
//         return false;
//     }

//     $('#alert_quantity_error').hide();
// });

</script>
