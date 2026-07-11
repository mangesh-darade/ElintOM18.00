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
    
    /* ===== Mobile-specific layout: similar to inventory_mobile ===== */
    .products-mobile .box {
        border-radius: 0;
        box-shadow: none;
    }

    .products-mobile .box-header {
        padding: 10px 12px;
    }

    .products-mobile #PRData {
        font-size: 13px;
    }

    /* Checkbox column width */
    .products-mobile #PRData th:nth-child(1),
    .products-mobile #PRData td:nth-child(1) {
        width: 28px;
        min-width: 28px;
        max-width: 28px;
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;
    }

    /* Hide non-required data columns on small screens:
       Keep:
         1: checkbox (selection)
         3: Code
         5: Product Name
         10: Stock Quantity
         11: Sale Unit
       Hide:
         2: Image
         4: Article Code
         6: Brand
         7: Category
         8: Cost
         9: Price
         12: Rack
         13: Storage Type
         14: Actions (already visually hidden)
    */
    @media (max-width: 768px) {
        .products-mobile #PRData th:nth-child(2),
        .products-mobile #PRData td:nth-child(2),
        .products-mobile #PRData th:nth-child(4),
        .products-mobile #PRData td:nth-child(4),
        .products-mobile #PRData th:nth-child(6),
        .products-mobile #PRData td:nth-child(6),
        .products-mobile #PRData th:nth-child(7),
        .products-mobile #PRData td:nth-child(7),
        .products-mobile #PRData th:nth-child(8),
        .products-mobile #PRData td:nth-child(8),
        .products-mobile #PRData th:nth-child(9),
        .products-mobile #PRData td:nth-child(9),
        .products-mobile #PRData th:nth-child(12),
        .products-mobile #PRData td:nth-child(12),
        .products-mobile #PRData th:nth-child(13),
        .products-mobile #PRData td:nth-child(13),
        .products-mobile #PRData th:nth-child(14),
        .products-mobile #PRData td:nth-child(14),
        .products-mobile #PRData th:nth-child(15),
        .products-mobile #PRData td:nth-child(15) {
            display: none;
        }

        .products-mobile #PRData th,
        .products-mobile #PRData td {
            white-space: nowrap;
        }

        .products-mobile #PRData th:nth-child(5),
        .products-mobile #PRData td:nth-child(5) {
            max-width: 200px;
            white-space: normal;
        }
    }
    
    /* ===== DataTables top bar: Show entries + Search down by down ===== */
    .products-mobile .dataTables_wrapper > .row:first-child {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    .products-mobile .dataTables_wrapper > .row:first-child > div {
        width: 100% !important;
        float: none !important;
        padding: 0 19px;
    }
    .products-mobile .dataTables_length,
    .products-mobile .dataTables_filter {
        float: none;
        display: block;
        width: 100%;
        margin: 5px 0;
    }
    .products-mobile .dataTables_filter input[type="search"] {
        width: 100%;
        box-sizing: border-box;
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .products-mobile .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        margin: 0;
        font-weight: normal;
    }
    .products-mobile .box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
    }
    .products-mobile .box-header h2 {
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
    }
    .dataTables_filter input[type=text]{
        background-color: #FFFFFF;
        background-image: none;
        border: 1px solid #CCCCCC;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
        color: #555555;
        font-size: 14px;
        padding: 6px 12px;
        transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
        vertical-align: middle;
        margin-right: 31px;
    }
    .box .box-header h2 i{
    border-right: 1px solid #dbdee0;
    padding: 12px 0px;
    height: 40px;
    width: 40px;
    display: inline-block;
    text-align: center;
    margin: -10px 20px -10px -13px;
    font-size: 16px;
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
    $(document).ready(function () {

    oTable = $('#PRData').dataTable({
    "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100,500, 1000, 2000, 5000,- 1], [10, 25, 50, 100,500, 1000, 2000, 5000, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProducts' . $param_sale) ?>',
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
            if (aData[13] == 1){

            productname = $("td:eq(4)", nRow).text()
                    $("td:eq(4)", nRow).html(productname + '<i class=\"fa fa-star\"></i>');
            favClass = 'remove_fav'
            } else{
            $("td:eq(4)", nRow).text(aData[4]);
            }


            nRow.className = "product_link " + favClass;
            //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
            return nRow;
            },
            "aoColumns": [
            {"bSortable": false, "mRender": checkbox}, {"bSortable": false, "mRender": img_hl}, null,null, null, null, null, <?php
if ($Owner || $Admin) {
    echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},';
} else {
    if ($this->session->userdata('show_cost')) {
        echo '{"mRender": currencyFormat},';
    } if ($this->session->userdata('show_price')) {
        echo '{"mRender": currencyFormat},';
    }
}
?> {"mRender": formatQuantity, "bSearchable":false}, null, <?php
if (!$warehouse_id || !$Settings->racks) {
    echo '{"bVisible": false},';
} else {
    echo '{"bSortable": true},';
}
?> {"bSearchable":true}, {"bVisible": false}, {"bSortable": false}
            ]
    }).fnSetFilteringDelay().dtFilter([
    {column_number: 2, filter_default_label: "[<?= lang('code'); ?>]", filter_type: "text", data: []},
//    {column_number: 3, filter_default_label: "[Artical Code]", filter_type: "text", data: []},
    {column_number: 4, filter_default_label: "[<?= lang('name'); ?>]", filter_type: "text", data: []},
    {column_number: 5, filter_default_label: "[<?= lang('brand'); ?>]", filter_type: "text", data: []},
    {column_number: 6, filter_default_label: "[<?= lang('category'); ?>]", filter_type: "text", data: []},
<?php
$col = 6;
if ($Owner || $Admin) {
    echo '{column_number : 7, filter_default_label: "[' . lang('cost') . ']", filter_type: "text", data: [] },';
    echo '{column_number : 8, filter_default_label: "[' . lang('price') . ']", filter_type: "text", data: [] },';
    $col += 2;
} else {
    if ($this->session->userdata('show_cost')) {
        $col++;
        echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('cost') . ']", filter_type: "text", data: [] },';
    }
    if ($this->session->userdata('show_price')) {
        $col++;
        echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('price') . ']", filter_type: "text, data: []" },';
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
<?php
$col++;
if ($warehouse_id && $Settings->racks) {
    echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('rack') . ']", filter_type: "text", data: [] },';
}
?>
    {column_number: <?php
$col++;
echo $col;
?>, filter_default_label: "[<?= lang('storate_type'); ?>]", filter_type: "text", data: []},
    ], "footer");
    });
</script>
<?php
if ($Owner || $GP['bulk_actions']) {
    echo form_open('products/product_actions' . $warehouseIds, 'id="action-form"');
}
?>
<div class="box products-mobile">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?> (Mobile)
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
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

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
                                <th><?= lang("name") ?></th>
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
                                <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
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
