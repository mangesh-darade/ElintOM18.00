<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: left;
    }
    .remove_fav i.fa.fa-heart {
        color: #bd1919;
        margin-right : 2%;
    }
    <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
        #PRData td:nth-child(9) {
            text-align: right;
        }
    <?php } if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
        #PRData td:nth-child(8) {
            text-align: right;
        }
    <?php } ?>
    .make_fav  .remove_fav_link{ display:none;}
    .remove_fav .add_fav_link{ display:none;}
    #s2id_autogen1{ width: 75px;}
    <?php if ($Settings->pos_type == 'restaurant') { ?>
        /* Hide Article Code column for restaurant POS */
        #PRData th:nth-child(4),
        #PRData td:nth-child(4) {
            display: none !important;
        }
        /* Adjust name column width for restaurant POS */
        #PRData th:nth-child(5),
        #PRData td:nth-child(5) {
            width: 150px !important;
            min-width: 150px !important;
        }
        /* Adjust code column width for restaurant POS */
        #PRData th:nth-child(3),
        #PRData td:nth-child(3) {
            width: 60px !important;
            min-width: 60px !important;
        }
    <?php } ?>

    #fav_products i{
        color : #bd1919;
    }
</style>
<?php
$alertqty = ($alert_qty) ? '?alert_qty=' . $alert_qty : '';
$alertqty1 = ($alert_qty) ? '/' . $alert_qty : '';
$warehouseIds = is_numeric($warehouse_id) ? '/' . $warehouse_id : '';
$param_sale = $warehouseIds . $alertqty;
?>
<script>
    var oTable;
    
    /**
     * Function to render product name as clickable hyperlink
     * When user clicks on product name, it navigates to products/view/{id}
     * 
     * @param {string} data - The product name text
     * @param {string} type - The type of rendering (display, type, filter, etc.)
     * @param {array} row - The full row data array
     * @returns {string} HTML anchor tag with product name as link text
     */
    function productNameLink(data, type, row) {
        if (type === 'display' || type === 'type') {
            var productId = row[0]; // productid is in first column (index 0)
            var productName = data || '';
            // Return anchor tag linking to product view page
            return '<a href="<?= site_url('products/view') ?>/' + productId + '">' + productName + '</a>';
        }
        return data;
    }
    
    $(document).ready(function () {

    oTable = $('#PRData').dataTable({
    "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100,500, 1000, 2000, 5000,- 1], [10, 25, 50, 100,500, 1000, 2000, 5000, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProducts' . $param_sale) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
            // aoData.push({
            // "name": "<?= $this->security->get_csrf_token_name() ?>",
            //         "value": "<?= $this->security->get_csrf_hash() ?>"
            // });
            aoData.push(
                { name: "category", value: $('#category').val() },
                { name: "brand", value: $('#brand').val() },
                {
                    name: "<?= $this->security->get_csrf_token_name() ?>",
                    value: "<?= $this->security->get_csrf_hash() ?>"
                }
            );
            $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
            var oSettings = oTable.fnSettings();
            nRow.id = aData[0];
            favClass = 'make_fav';
            if (aData[aData.length-2] == 1){
                // Get the current content (which is already a link from mRender) and add star icon
                productname = $("td:eq(4)", nRow).html();
                $("td:eq(4)", nRow).html('<i class=\"fa fa-heart\"></i>' + productname);
                favClass = 'remove_fav'
            }


            nRow.className = "product_link " + favClass;
            //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
            return nRow;
            },
            "aoColumns": [
            {"bSortable": false, "mRender": checkbox}, {"bSortable": false, "mRender": img_hl}, null,null, {"mRender": productNameLink}, null, null, <?php
            $q_col = 7;
if ($Owner || $Admin) {
    echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},';
    $q_col = 9;
} else {
    if ($this->session->userdata('show_cost')) {
        echo '{"mRender": currencyFormat},';
        $q_col++;
    } if ($this->session->userdata('show_price')) {
        echo '{"mRender": currencyFormat},';
        $q_col++;
    }
}
?> {"mRender": formatQuantity, "bSearchable":false}, null, <?php
if (!$warehouse_id || !$Settings->racks) {
    echo '{"bVisible": false},';
} else {
    echo '{"bSortable": true},';
}
?> {"bSearchable":true}, {"bVisible": false}, {"bVisible": false, "bSortable": false}
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total_qty = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total_qty += parseFloat(aaData[aiDisplay[i]][<?= $q_col; ?>]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[<?= $q_col; ?>].innerHTML = formatQuantity(total_qty);
            }
    }).fnSetFilteringDelay().dtFilter([
    {column_number: 2, filter_default_label: "[<?= lang('code'); ?>]", filter_type: "text", data: []},
//    {column_number: 3, filter_default_label: "[Artical Code]", filter_type: "text", data: []},
    {column_number: 4, filter_default_label: "[<?= lang('Products_name'); ?>]", filter_type: "text", data: []},
    {column_number: 5, filter_default_label: "[<?= lang('brand'); ?>]", filter_type: "text", data: []},
    {column_number: 6, filter_default_label: "[<?= lang('category'); ?>]", filter_type: "text", data: []},
<?php
$col = 6;
if ($Owner || $Admin) {
    echo '{column_number : 7, filter_default_label: "[' . lang('Avg cost') . ']", filter_type: "text", data: [] },';
    echo '{column_number : 8, filter_default_label: "[' . lang('price') . ']", filter_type: "text", data: [] },';
    $col += 2;
} else {
    if ($this->session->userdata('show_cost')) {
        $col++;
        echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('Cost') . ']", filter_type: "text", data: [] },';
    }
    if ($this->session->userdata('show_price')) {
        $col++;
        echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('Price') . ']", filter_type: "text, data: []" },';
    }
}
?>
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('Quantity'); ?>]", filter_type: "text", data: []},
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('Sale Unit'); ?>]", filter_type: "text", data: []},
<?php
$col++;
if ($warehouse_id && $Settings->racks) {
    echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('rack') . ']", filter_type: "text", data: [] },';
}
?>
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('Storage Type'); ?>]", filter_type: "text", data: []},
    ], "footer");
    $('#filter_submit').on('click', function () {
        var warehouseId = $('#warehouse_select').val();
        var category = $('#category').val();
        var brand = $('#brand').val();
        
        // Build the URL based on warehouse selection
        var baseUrl = '<?= site_url('products') ?>';
        var alertQty = '<?= $alertqty1 ?>';
        
        if (warehouseId) {
            if ('<?= $Owner || $Admin ?>') {
                baseUrl = '<?= site_url('products/index') ?>/' + warehouseId + alertQty;
            } else {
                baseUrl = '<?= site_url('products') ?>/' + warehouseId + alertQty;
            }
        } else {
            baseUrl = baseUrl + alertQty;
        }
        
        // Add query parameters for category and brand
        var params = [];
        if (category) {
            params.push('category=' + category);
        }
        if (brand) {
            params.push('brand=' + brand);
        }
        
        if (params.length > 0) {
            baseUrl += (baseUrl.indexOf('?') === -1 ? '?' : '&') + params.join('&');
        }
        
        // Navigate to the warehouse URL with filters
        window.location.href = baseUrl;
    });
    });

    // Hide the modal view of product details on clicking anywhere on the screen
    $(document).on('mouseenter', '#PRData tbody td', function () {
        $(this).css('cursor', 'default');
    });
    $(document).on('click', '#PRData tbody td', function (e) {
        if ($(e.target).closest('a').length) {
        return true;
    }
        if ($(e.target).is('input[type="checkbox"]')) {
            return true;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
    });
//     $(document).ready(function () {
//     $('#myModal').remove();
// });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle tip" data-toggle="dropdown" title="<?= lang('excel') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                    <ul class="dropdown-menu pull-right">
                        
                        <!-- Product (existing logic) -->
                        <li>
                            <a href="#" class="export-product-excel">
                                <i class="fa fa-cube"></i> Export Product
                            </a>
                        </li>

                        <!-- Variant (new logic) -->
                        <li>
                            <a href="#" id="export_variant_excel" data-action="export_variant_excel">
                                <i class="fa fa-cubes"></i> Export Variant
                            </a>
                        </li>

                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle tip" data-toggle="dropdown" title="<?= lang('pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                    <ul class="dropdown-menu pull-right">
                        
                        <!-- Product (existing logic) -->
                        <li>
                            <a href="#" class="export-product-pdf">
                                <i class="fa fa-cube"></i> Export Product
                            </a>
                        </li>

                        <!-- Variant (new logic) -->
                        <li>
                            <a href="#" id="export_variant_pdf" data-action="export_variant_pdf">
                                <i class="fa fa-cubes"></i> Export Variant
                            </a>
                        </li>

                    </ul>
                </li>
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
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('products/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
                            </a>
                        </li>
                        <li>
                            <a class='fav' id="fav_products" data-action="fav_products" href="#">
                                <i class="fa fa-heart"></i> <?= lang('add_favourite') ?>
                            </a>
                        </li>
                        <?php if ($Owner || $Admin || $GP['products-edit']) { ?>
                            <li>
                                <a href="<?= site_url('products/update_price') ?>" data-toggle="modal" data-target="#myModal">
                                    <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="#" id="labelProducts" data-action="labels">
                                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="sync_quantity" data-action="sync_quantity">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity') ?>
                            </a>
                        </li>
                        <!-- added seperate icon for export product and export variant in excel and pdf  -->
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <!-- <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?> -->
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <!-- <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?> -->
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_products") ?></b>"
                               data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                               data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php // header warehouse dropdown removed; warehouse selection is now handled by the filter form dropdown ?>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('list_results'); ?></p>     
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">

                    <?php echo form_open("products/index"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select') . ' ' . lang('category');
                                foreach($categories as $category)
                                {
                                    $cat[$category->id] = $category->name;
                                }
                                $selectedCategory = isset($_POST['category']) ? $_POST['category'] : (isset($_GET['category']) ? $_GET['category'] : '');
                                echo form_dropdown('category', $cat, $selectedCategory, 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("brand", "brand") ?>
                                <?php
                                $bt[''] = lang('select') . ' ' . lang('brand');
                                foreach($brands as $brand)
                                {
                                    $bt[$brand->id] = $brand->name;
                                }
                                $selectedBrand = isset($_POST['brand']) ? $_POST['brand'] : (isset($_GET['brand']) ? $_GET['brand'] : '');
                                echo form_dropdown('brand', $bt, $selectedBrand, 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("warehouses", "warehouse_select") ?>
                                <select id="warehouse_select" name="warehouse" class="form-control select" style="width:100%">
                                    <option value=""><?= lang('all_warehouses') ?></option>
                                    <?php
                                    if (!empty($warehouses)) {
                                        $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                        foreach ($warehouses as $warehouse) {
                                            if ($Owner || $Admin || in_array($warehouse->id, $permisions_werehouse)) {
                                                $selected = (!empty($warehouse_id) && is_numeric($warehouse_id) && (int)$warehouse_id === (int)$warehouse->id) ? ' selected="selected"' : '';
                                                echo '<option value="' . $warehouse->id . '"' . $selected . '>' . $warehouse->name . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <button type="button" id="filter_submit" class="btn btn-primary"><?= lang('submit') ?></button>
                            <!-- <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> -->
                            <!--<input type="button" id="report_reset" data-value="<?= base_url('reports/products'); ?>"
                                   name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                                <a href="<?= site_url('products/index') ?>" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <?php
                if ($Owner || $GP['bulk_actions']) {
                    echo form_open('products/product_actions' . $warehouseIds, 'id="action-form"');
                }
                ?>
                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                                <th><?= lang("code") ?></th>
                                <th>Article Code</th>
                                <th><?= lang("Products_name") ?></th>
                                <th><?= lang("brand") ?></th>
                                <th><?= lang("category") ?></th>
                                <?php
                                if ($Owner || $Admin) {
                                    echo '<th>' . lang("Avg cost") . '</th>';
                                    echo '<th>' . lang("price") . '</th>';
                                } else {
                                    if ($this->session->userdata('show_cost')) {
                                        echo '<th>' . lang("cost") . '</th>';
                                    }
                                    if ($this->session->userdata('show_price')) {
                                        echo '<th>' . lang("price") . '</th>';
                                    }
                                }
                                ?>
                                <th><?= lang("quantity") ?></th>
                                <th>Sale <?= lang("unit") ?></th>
                                <th><?= lang("rack") ?></th>
                                <th><?= lang("Storage Type") ?></th> <th></th>
                                <th style="display:none; min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <?php
                                if ($Owner || $Admin) {
                                    echo '<th></th>';
                                    echo '<th></th>';
                                } else {
                                    if ($this->session->userdata('show_cost')) {
                                        echo '<th></th>';
                                    }
                                    if ($this->session->userdata('show_price')) {
                                        echo '<th></th>';
                                    }
                                }
                                ?>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th> 
                                <th></th>
                                <th style="display:none; width:65px; text-align:center;"><?= lang("actions") ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>

<script>
$(document).ready(function () {

    // ✅ Export Product - Excel (uses existing button)
    $(document).on('click', '.export-product-excel', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var excelBtn = $('#excel');
        if (excelBtn.length) {
            excelBtn.trigger('click');
        } else {
            console.warn('Excel button not found');
        }
    });

    // ✅ Export Product - PDF (uses existing button)
    $(document).on('click', '.export-product-pdf', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var pdfBtn = $('#pdf');
        if (pdfBtn.length) {
            pdfBtn.trigger('click');
        } else {
            console.warn('PDF button not found');
        }
    });

    // ✅ Export Variant - Excel
    $(document).on('click', '#export_variant_excel', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });

    // ✅ Export Variant - PDF
    $(document).on('click', '#export_variant_pdf', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });

});
</script>
