<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: right;
    }
    #PRData td:nth-child(9) {
        text-align: center;
    }
    .remove_fav i.fa.fa-heart {
        color: #bd1919;
    }
    #PRData .fa-heart, #fav_products .fa-heart{
        color: #bd1919;
        margin-right: 2%;
    }
    .make_fav  .remove_fav_link{ display:none;}
    .remove_fav .add_fav_link{ display:none;}
    #s2id_autogen1{ width: 75px;}

    #PRData_filter{
        text-align:end!important;
    }
    thead th:nth-child(8) .yadcf-filter-wrapper input {
    text-align: center;
    }

    tbody td:nth-child(8) {
        text-align: center;
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
     * This replaces the "View Details" button in the action column
     * When user clicks on product name, it navigates directly to products/view/{id}
     * instead of opening a modal dialog
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
            "aLengthMenu": [[10, 25, 50, 100, 500, 1000, 2000, 5000, -1], [10, 25, 50, 100, 500, 1000, 2000, 5000, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('Production_Unit/getProducts') ?>?warehouse_id=<?= $warehouse_id ?><?= $alertqty ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                favClass = 'make_fav';
                if (aData[9] == 1) {
                    // Preserve the hyperlink when adding favorite star
                    var nameCell = $("td:eq(2)", nRow);
                    var currentHtml = nameCell.html();
                    // Check if star already exists to avoid duplicates
                    if (currentHtml.indexOf('fa-star') === -1) {
                        // nameCell.html(currentHtml + ' <i class="fa fa-star"></i>');
                        nameCell.html('<i class="fa fa-heart"></i>' + currentHtml);
                    }
                    favClass = 'remove_fav';                            
                }

                // if (aData[9] == 1) {
                //     productname = $("td:eq(2)", nRow).text()
                //     $("td:eq(2)", nRow).html('<i class=\"fa fa-heart\"></i> ' + productname);
                //     favClass = 'remove_fav'
                //     } else {
                //         $("td:eq(2)", nRow).text(aData[2]);

                nRow.className = "product_link " + favClass;
                
                /**
                 * Prevent row click modal when clicking on product name link
                 * The row has a click handler that opens modal_view, but we want the name link
                 * to navigate directly to products/view/{id} instead of opening the modal
                 * stopPropagation prevents the click event from bubbling up to the row handler
                 * Note: Name column is at index 2 (td:eq(2)) for inventory screen
                 */
                $("td:eq(2) a", nRow).on('click', function(e) {
                    e.stopPropagation(); // Prevent row click handler from firing
                });
                
                return nRow;
            },
            "aoColumns": [
    {"bSortable": false, "mRender": checkbox}, null, 
    {"mRender": productNameLink}, // Product name column - rendered as hyperlink via productNameLink function
    null, null, null, null,
    <?php
    if (!$warehouse_id || !$Settings->racks) {
        echo '{"bVisible": false},';
    } else {
        echo '{"bSortable": true},';
    }
    ?> 
    {"bSearchable": true}, {"bVisible": false}, {"bVisible": false, "bSortable": false} // Actions column - hidden from table
]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?= lang('Code'); ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('Product Name'); ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('Brand'); ?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?= lang('Category'); ?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?= lang('Sale Unit'); ?>]", filter_type: "text", data: []},
            <?php
            if ($warehouse_id && $Settings->racks) {
                echo '{column_number : 6, filter_default_label: "[' . lang('Rack') . ']", filter_type: "text", data: [] },';
            }
            ?>
            {column_number: 7, filter_default_label: "[<?= lang('Storate_Type'); ?>]", filter_type: "text", data: []}, // Changed from 8 to 9
            {column_number: 8, filter_default_label: "[<?= lang('Stock Quantity'); ?>]", filter_type: "text", data: []}, // Changed from 5 to 7

        ], "footer");
    });

    // Hide the modal view of product details on clicking anywhere on the screen
    $(document).on('mouseenter', '#PRData tbody td', function () {
        $(this).css('cursor', 'default');
    });
    $(document).on('click', '#PRData tbody td', function (e) {
        if ($(e.target).is('input[type="checkbox"]')) {
            return true;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
    });
    $(document).ready(function () {
    $('#myModal').remove();

});
</script>
<style>
    .text-center {
    text-align: center;
}
</style>

<?php
if ($Owner || $GP['bulk_actions']) {
    echo form_open('Production_Unit/product_actions' . $warehouseIds, 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('All_Locations')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
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
                        <?php if (!$warehouse_id) { ?>
                            <li>
                                <a href="<?= site_url('products/update_price') ?>" data-toggle="modal" data-target="#myModal">
                                    <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <!-- for inventory :- print barcod and label  not required  -->
                        <!-- <li>
                            <a href="#" id="labelProducts" data-action="labels">
                                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                            </a>
                        </li> -->
                        <li>
                            <a href="#" id="sync_quantity" data-action="sync_quantity">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
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
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("Locations") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('Production_Unit/inventory') ?>"><i class="fa fa-building-o"></i> <?= lang('All_Locations') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                if ($Owner || $Admin) {
                                    echo '<li><a href="' . site_url('Production_Unit/inventory/' . $warehouse->id . $alertqty1) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                    echo '<li><a href="' . site_url('Production_Unit/inventory/' . $warehouse->id . $alertqty1) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('list_results'); ?></p>     
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:10px; width: 10px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang("code") ?></th>
                                <th><?= lang("Product Name") ?></th>
                                <th><?= lang("brand") ?></th>
                                <th><?= lang("category") ?></th>
                                <th>Sale <?= lang("unit") ?></th>
                                <th><?= lang("Rack") ?></th>
                                <th><?= lang("Storage Type") ?></th>
                                <!-- <th><?= lang("Stock Quantity") ?></th> -->
                                <th class="text-center"><?= lang("Stock Quantity") ?></th>
                                 <th></th>
                                <th style="display:none; min-width:65px; text-align:center;"><?= lang("actions") ?></th> <!-- Actions column - hidden from table -->
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
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th> 
                                <th></th>
                                <th style="display:none; width:65px; text-align:center;"><?= lang("actions") ?></th> <!-- Actions column - hidden from table -->
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
