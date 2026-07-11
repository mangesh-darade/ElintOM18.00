<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    tbody td:nth-child(6){
        text-align:right;
    }
    tbody td:nth-child(3){
        text-align:left;
    }
</style>
<?php
$show_batch = ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2);
?>

<script>
    $(document).ready(function () {
        // Prevent "html5_data attribute" popup from dtFilter
        const oldAlert = window.alert;
        window.alert = function(msg) {
            if (msg && msg.indexOf('html5_data') !== -1) {
                console.warn('dtFilter skipped popup:', msg);
                return;
            }
            oldAlert(msg);
        };

        var oTable = $('#PQData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getProductCosting' . ($warehouse_id ? '/' . str_replace(",","_",$warehouse_id) : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                null, // product_code
                null, // product_name
                <?php if ($show_batch) { ?> 
                    null, // batch_no
                    {"mRender": formatQuantity, "bSearchable": false}, // purchase qty
                    {"mRender": formatQuantity, "bSearchable": false}, // balance qty
                    {"mRender": currencyFormat, "bSearchable": false}  // avg cost
                <?php } else { ?>
                    {"mRender": formatQuantity, "bSearchable": false}, // purchase qty
                    {"mRender": formatQuantity, "bSearchable": false}, // balance qty
                    {"mRender": currencyFormat, "bSearchable": false}  // avg cost
                <?php } ?>
            ],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text"},
            {column_number: 1, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text"},
            <?php if ($show_batch) { ?>
                {column_number: 2, filter_default_label: "[<?=lang('batch_no');?>]", filter_type: "text"},
                {column_number: 3, filter_default_label: "[<?=lang('purchase_quantity');?>]", filter_type: "text"},
                {column_number: 4, filter_default_label: "[<?=lang('balance_quantity');?>]", filter_type: "text"},
                {column_number: 5, filter_default_label: "[<?=lang('average_cost');?>]", filter_type: "text"}
            <?php } else { ?>
                {column_number: 2, filter_default_label: "[<?=lang('purchase_quantity');?>]", filter_type: "text"},
                {column_number: 3, filter_default_label: "[<?=lang('balance_quantity');?>]", filter_type: "text"},
                {column_number: 4, filter_default_label: "[<?=lang('average_cost');?>]", filter_type: "text"}
            <?php } ?>
        ], "footer");


    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-calendar-o"></i><?= lang('Products_Costing') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <?php //if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i>
                        </a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li>
                                <a href="<?= site_url('reports/products_costing') ?>">
                                    <i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <?php
                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                if($Owner || $Admin   ){
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/products_costing/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/products_costing/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';

                                }                                 
                            }
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
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('list_results'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                
                <div class="table-responsive">
                    <table id="PQData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped dfTable reports-table">
                        <thead>
                            <tr class="active">
                                <th><?php echo $this->lang->line("product_code"); ?></th>
                                <th><?php echo $this->lang->line("product_name"); ?></th>
                                <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) { ?>
                                    <th><?= lang('batch_no'); ?></th>
                                <?php } ?>
                                <th><?php echo $this->lang->line("Purchase quantity"); ?></th>
                                <th><?php echo $this->lang->line("Balance quantity"); ?></th>
                                <th><?php echo $this->lang->line("Average_cost"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th> <!-- product_code -->
                                <th></th> <!-- product_name -->
                                <?php if ($show_batch) { ?>
                                    <th></th> <!-- batch_no -->
                                    <th></th> <!-- purchase_qty -->
                                    <th></th> <!-- balance_qty -->
                                    <th></th> <!-- avg_cost -->
                                <?php } else { ?>
                                    <th></th> <!-- purchase_qty -->
                                    <th></th> <!-- balance_qty -->
                                    <th></th> <!-- avg_cost -->
                                <?php } ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>-->
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductCosting/'.($warehouse_id ? $warehouse_id : '0').'/pdf')?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductCosting/'.($warehouse_id ? $warehouse_id : '0').'/0/xls')?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductCosting/'.($warehouse_id ? $warehouse_id : '0').'/0/0/img')?>";
            return false;
        });
    });
</script>
