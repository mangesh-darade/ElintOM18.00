<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$alertqty  = ($alert_qty) ? '?alert_qty=' . $alert_qty : '';
$alertqty1 = ($alert_qty) ? '/' . $alert_qty : '';
$warehouseIds = is_numeric($warehouse_id) ? '/' . $warehouse_id : '';
$param_sale = $warehouseIds . $alertqty;
?>

<style type="text/css" media="screen">
    /* Base tweaks from desktop inventory */
    #PRData td:nth-child(7) {
        text-align: right;
    }
    #PRData td:nth-child(9) {
        text-align: center;
    }
    .remove_fav i.fa.fa-heart {
        color: #bd1919;
    }
    #PRData .fa-heart,
    #fav_products .fa-heart {
        color: #bd1919;
        margin-right: 2%;
    }
    .make_fav .remove_fav_link { display: none; }
    .remove_fav .add_fav_link { display: none; }
    #s2id_autogen1 { width: 75px; }
    #PRData_filter {
        text-align: end !important;
    }
    thead th:nth-child(8) .yadcf-filter-wrapper input {
        text-align: center;
    }
    tbody td:nth-child(8) {
        text-align: center;
    }

    .text-center {
        text-align: center;
    }

    /* ===== Mobile-specific layout: show Name, Stock Quantity, Unit ===== */
    .inventory-mobile .box {
        border-radius: 0;
        box-shadow: none;
    }

    .inventory-mobile .box-header {
        padding: 10px 12px;
    }

    .inventory-mobile #PRData {
        font-size: 13px;
    }

    /* Checkbox column width */
    .inventory-mobile #PRData th:nth-child(1),
    .inventory-mobile #PRData td:nth-child(1) {
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
         3: Product Name
         6: Sale Unit
         9: Stock Quantity
       Hide:
         2: Code
         4: Brand
         5: Category
         7: Rack
         8: Storage Type
         10: extra/favourite
         11: Actions (already visually hidden)
    */
    @media (max-width: 768px) {
        .inventory-mobile #PRData th:nth-child(2),
        .inventory-mobile #PRData td:nth-child(2),
        .inventory-mobile #PRData th:nth-child(4),
        .inventory-mobile #PRData td:nth-child(4),
        .inventory-mobile #PRData th:nth-child(5),
        .inventory-mobile #PRData td:nth-child(5),
        .inventory-mobile #PRData th:nth-child(8),
        .inventory-mobile #PRData td:nth-child(8),
        .inventory-mobile #PRData th:nth-child(9),
        .inventory-mobile #PRData td:nth-child(9),
        .inventory-mobile #PRData th:nth-child(10),
        .inventory-mobile #PRData td:nth-child(10),
        .inventory-mobile #PRData th:nth-child(11),
        .inventory-mobile #PRData td:nth-child(11) {
            display: none;
        }

        .inventory-mobile #PRData th,
        .inventory-mobile #PRData td {
            white-space: nowrap;
        }

        .inventory-mobile #PRData th:nth-child(3),
        .inventory-mobile #PRData td:nth-child(3) {
            max-width: 220px;
            white-space: normal;
        }
    }
    /* ===== DataTables top bar: Show entries + Search down by down ===== */
    .inventory-mobile .dataTables_wrapper > .row:first-child {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    .inventory-mobile .dataTables_wrapper > .row:first-child > div {
        width: 100% !important;
        float: none !important;
        padding: 0 19px;
    }
    .inventory-mobile .dataTables_length,
    .inventory-mobile .dataTables_filter {
        float: none;
        display: block;
        width: 100%;
        margin: 5px 0;
    }
    .inventory-mobile .inv-dt-topbar {
        display: block;
        padding: 6px 8px;
        background: #fff;
        border-bottom: 1px solid #ddd;
    }
    .inventory-mobile .inv-dt-topbar .dataTables_length,
    .inventory-mobile .inv-dt-topbar .dataTables_filter {
        width: 100%;
    }
    .inventory-mobile .inv-dt-topbar .dataTables_filter input {
        width: 100%;
        box-sizing: border-box;
    }
    /* Put length select & label inline */
    .inventory-mobile .dataTables_length label {
        /* display: none; */
        align-items: center;
        gap: 19px;
        white-space: nowrap;
        margin: 0;
        font-weight: normal;
    }
    /* Search label + input inline */
    .inventory-mobile .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        width: 100%;
    }
    .inventory-mobile .dataTables_filter input[type="search"] {
        flex: 1;
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    /* Hide the default DT top/bottom info line */
    .inventory-mobile .dataTables_info { padding-top: 4px; font-size: 12px; }

    /* ===== box-header: title + icon inline ===== */
    .inventory-mobile .box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
    }
    .inventory-mobile .box-header h2 {
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 15px;
    }
    .inventory-mobile .box-header .box-icon {
        flex-shrink: 0;
    }
    .box .box-header h2 i {
        margin: -10px 20px -10px -12px;
    }

</style>

<script type="text/javascript">
    var oTable;

    function productNameLink(data, type, row) {
        if (type === 'display' || type === 'type') {
            var productId = row[0];
            var productName = data || '';
            return '<a href="<?= site_url('products/view') ?>/' + productId + '">' + productName + '</a>';
        }
        return data;
    }

    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, 500, 1000, 2000, 5000, -1], [10, 25, 50, 100, 500, 1000, 2000, 5000, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('Production_Unit/getProducts') ?>?warehouse_id=<?= $warehouse_id ?><?= $alertqty ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
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
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                var favClass = 'make_fav';
                if (aData[9] == 1) {
                    var nameCell = $("td:eq(2)", nRow);
                    var currentHtml = nameCell.html();
                    if (currentHtml.indexOf('fa-heart') === -1) {
                        nameCell.html('<i class="fa fa-heart"></i>' + currentHtml);
                    }
                    favClass = 'remove_fav';
                }
                nRow.className = "product_link " + favClass;

                $("td:eq(2) a", nRow).on('click', function (e) {
                    e.stopPropagation();
                });

                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                null,
                {"mRender": productNameLink},
                null, null,
                {"sClass": "text-center", "bSortable": false, "bSearchable": false, "mRender": function(data, type, row) { return row[8] ? row[8] : ''; }},
                {"bSortable": false, "bSearchable": false, "mRender": function(data, type, row) { return row[5] ? row[5] : ''; }},
                <?php
                if (!$warehouse_id || !$Settings->racks) {
                    echo '{"bVisible": false},';
                } else {
                    echo '{"bSortable": true},';
                }
                ?>
                {"bSearchable": true, "mData": 7},
                {"bVisible": false, "mData": 9},
                {"bVisible": false, "bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?= lang('Code'); ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('Name'); ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('Brand'); ?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?= lang('Category'); ?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?= lang('Stock Quantity'); ?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?= lang('Unit'); ?>]", filter_type: "text", data: []},
            <?php
            if ($warehouse_id && $Settings->racks) {
                echo '{column_number : 7, filter_default_label: "[' . lang('Rack') . ']", filter_type: "text", data: [] },';
            }
            ?>
            {column_number: 8, filter_default_label: "[<?= lang('Storate_Type'); ?>]", filter_type: "text", data: []}
        ], "footer");
    });

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

<?php
if ($Owner || $GP['bulk_actions']) {
    echo form_open('Production_Unit/product_actions' . $warehouseIds, 'id="action-form"');
}
?>

<div class="box inventory-mobile">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i>
            <?= lang('products') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('All_Locations')) . ')'; ?>
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
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("Locations") ?>"></i>
                        </a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li>
                                <a href="<?= site_url('Production_Unit/inventory') ?>">
                                    <i class="fa fa-building-o"></i> <?= lang('All_Locations') ?>
                                </a>
                            </li>
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

    <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

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
                            <th><?= lang("Name") ?></th>
                            <th><?= lang("brand") ?></th>
                            <th><?= lang("category") ?></th>
                            <th class="text-center"><?= lang("Stock Quantity") ?></th>
                            <th><?= lang("unit") ?></th>
                            <th><?= lang("Rack") ?></th>
                            <th><?= lang("Storage Type") ?></th>
                            <th></th>
                            <th style="display:none; min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty">
                                <?= lang('loading_data_from_server'); ?>
                            </td>
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

