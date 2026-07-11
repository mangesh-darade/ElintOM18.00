<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: right;
    }
    .remove_fav i.fa.fa-star {
        color: #bd1919;
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
</style>
<?php
$alertqty = ($alert_qty) ? '?alert_qty=' . $alert_qty : '';
$alertqty1 = ($alert_qty) ? '/' . $alert_qty : '';
$warehouseIds = is_numeric($warehouse_id) ? '/' . $warehouse_id : '';
$param_sale = $warehouseIds . $alertqty;
?>
<script>
    var oTable;
    $(document).ready(function () {

    oTable = $('#PRData').dataTable({
    "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100,500, 1000, 2000, 5000,- 1], [10, 25, 50, 100,500, 1000, 2000, 5000, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getRawMaterials' . $param_sale) ?>',
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
            // Note: is_featured has been removed from the query
            $("td:eq(2)", nRow).text(aData[2]);


            nRow.className = "product_link " + favClass;
            //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                nRow.className = "product_link " + favClass;

                // ===== Background color conditions based on alert quantity =====
                // alert_quantity is the second-to-last item (before Actions which is added last by add_column)
                var alert_quantity = 0;
                if (aData.length >= 2) {
                    alert_quantity = parseFloat(aData[aData.length - 2]) || 0;
                }
                // Quantity is typically at index 8 when both cost and price are visible
                // Index may shift to 6 or 7 if cost/price are unset
                var quantity = 0;
                if (aData[8] !== undefined && !isNaN(parseFloat(aData[8]))) {
                    quantity = parseFloat(aData[8]);
                } else if (aData[7] !== undefined && !isNaN(parseFloat(aData[7]))) {
                    quantity = parseFloat(aData[7]);
                } else if (aData[6] !== undefined && !isNaN(parseFloat(aData[6]))) {
                    quantity = parseFloat(aData[6]);
                }
                
                // Apply color coding based on quantity vs alert quantity
                if (quantity <= 0) {
                    // Red: Out of stock
                    $(nRow).css('background-image', 'linear-gradient(white, #ff5454)');
                } else if (alert_quantity > 0 && quantity <= alert_quantity) {
                    // Yellow: Stock is at or below alert level
                    $(nRow).css('background-image', 'linear-gradient(white, #f3ff54)');
                } else {
                    // Grey/White: Stock is above alert level (or no alert set)
                    $(nRow).css('background-image', 'linear-gradient(white, #cccccc)');
                }
                // Keep checkbox column background white for visibility
                $(nRow).find('td').eq(0).css('background-color', 'white');
                // ==========================================
            return nRow;
            },
            "aoColumns": [
            // checkbox, image, name, brand, code, article code, cost, price, qty, unit, storage_type, expiry, alert_quantity(hidden), actions
            {"bSortable": false, "mRender": checkbox},
            {"bSortable": false, "mRender": img_hl},
            null,  // name
            null,  // brand
            null,  // code
            null,  // article_code
            <?php
            if ($Owner || $Admin) {
                echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},';
            } else {
                if ($this->session->userdata('show_cost')) {
                    echo '{"mRender": currencyFormat},';
                }
                if ($this->session->userdata('show_price')) {
                    echo '{"mRender": currencyFormat},';
                }
            }
            ?>
            {"mRender": formatQuantity, "bSearchable": false},  // quantity
            null,  // unit
            null,  // storage_type
            null,  // expiry
            {"bVisible": false},  // alert_quantity - hidden, used for row coloring only
            {"bSortable": false}  // actions
            ]
    }).fnSetFilteringDelay().dtFilter([
    {column_number: 2, filter_default_label: "[<?= lang('name'); ?>]", filter_type: "text", data: []},
    {column_number: 3, filter_default_label: "[<?= lang('brand'); ?>]", filter_type: "text", data: []},
    {column_number: 4, filter_default_label: "[<?= lang('code'); ?>]", filter_type: "text", data: []},
    {column_number: 5, filter_default_label: "[Artical Code]", filter_type: "text", data: []},
<?php
$col = 5;
if ($Owner || $Admin) {
    echo '{column_number : 6, filter_default_label: "[' . lang('cost') . ']", filter_type: "text", data: [] },';
    echo '{column_number : 7, filter_default_label: "[' . lang('price') . ']", filter_type: "text", data: [] },';
    $col += 2;
} else {
    if ($this->session->userdata('show_cost')) {
        $col++;
        echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('cost') . ']", filter_type: "text", data: [] },';
    }
    if ($this->session->userdata('show_price')) {
        $col++;
        echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('price') . ']", filter_type: "text", data: [] },';
    }
}
?>
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('quantity'); ?>]", filter_type: "text", data: []},
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('unit'); ?>]", filter_type: "text", data: []},
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('storage_type'); ?>]", filter_type: "text", data: []},


    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('expiry'); ?>]", filter_type: "text", data: []},

    ], "footer");
    });
</script>
<?php
if ($Owner || $GP['bulk_actions']) {
    echo form_open('products/product_actions' . $warehouseIds, 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
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
                                <i class="fa fa-star"></i> <?= lang('add_favourite') ?>
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
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                if ($Owner || $Admin) {
                                    echo '<li><a href="' . site_url('products/index/' . $warehouse->id . $alertqty1) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                    echo '<li><a href="' . site_url('products/' . $warehouse->id . $alertqty1) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                    <table id="PRData" class="table table-bordered table-condensed table-hover">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                                <th><?= lang("name") ?></th>
                                <th style="min-width:20px; width: 30px; text-align: center;"><?= lang("brand") ?></th>
                                <th><?= lang("code") ?></th>
                                <th>Article Code</th>
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

                                <th><?= lang("Storage Type") ?></th>
                                <th><?= lang("Expiry Date") ?></th>
                                <th></th>
                                <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="14" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                                <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
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
