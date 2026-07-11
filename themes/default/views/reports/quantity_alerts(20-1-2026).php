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
?>
<script>
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
            { "bSortable": false, "bSearchable": false, "mRender": img_hl }, // 0 image
            null,                                       // 1 product_code
            null,                                       // 2 category_name
            null,                                       // 3 product_name
            { "bSortable": false },                   // 4 variant_name
            <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) { ?>
                { "bSortable": false, "bSearchable": false }, // 5 batch_numbers (aggregate)
                { "bSortable": false, "mRender": formatQuantity }, // 6 alert_quantity
                { "mRender": formatQuantity }          // 7 stock_quantity
            <?php } else { ?>
                { "bSortable": false, "mRender": formatQuantity }, // 5 alert_quantity
                { "mRender": formatQuantity }          // 6 stock_quantity
            <?php } ?>
        ],

        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {

            var hasBatch = <?php echo ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) ? 'true' : 'false'; ?>;
            var idxAlert = hasBatch ? 6 : 5;
            var idxStock = hasBatch ? 7 : 6;

            var quantity = parseFloat(aData[idxStock]);
            var alert_quanity = parseFloat(aData[idxAlert]);

            console.log('quantity');
            if (quantity <= 0) {
                $(nRow).css('background-image', 'linear-gradient(white, #ff5454)'); //red
                // $(nRow).css('background-color', '#ff5454');
            } 
            if (quantity > 0 && quantity <= alert_quanity) {
                $(nRow).css('background-image', 'linear-gradient(white, #f3ff54)'); //yellow
                // $(nRow).css('background-color', '#ff5454');
            }
            if (quantity > alert_quanity) {
                $(nRow).css('background-image', 'linear-gradient(white, #cccccc)'); // white
                // $(nRow).css('background-color', '#ff5454');
            }
            $(nRow).find('td').eq(0).css('background-color', 'white');

        }
    }).fnSetFilteringDelay().dtFilter([
        {
            column_number: 1,
            filter_default_label: "[<?=lang('product_code');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 2,
            filter_default_label: "[<?=lang('Category_Name');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 3,
            filter_default_label: "[<?=lang('product_name');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) { echo 6; } else { echo 5; } ?>,
            filter_default_label: "[<?=lang('alert_quantity');?>]",
            filter_type: "text",
            data: []
        },
    ], "footer");
});
</script>
<style>
/* .table-hover > tbody > tr:hover > td, .table-hover > tbody > tr:hover > th{
        background:#fff;
        color:#333;
    }        */
</style>

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
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div id="form" style="margin-bottom: 25px;">
                <?php echo form_open("reports/quantity_alerts","id='searchproduct'"); ?>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                            <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select') . ' ' . lang('warehouse');
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
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang('Stock Alert ', 'stock_alert'); ?>
                            <?php $arr1 = array('0'=>'Select Stock Alert','1'=>'Below Stock Quantity','2'=>'Above Stock Quantity','3'=>'Stock less than reorder quantity');

                           
                            //  form_dropdown('stock_alert', $arr1, $pos->pos_screen_products, 'class="form-control" id="stock_alert" style="width:100%;"');
                            echo form_dropdown('stock_alert', $arr1, (isset($_POST['stock_alert']) ? $_POST['stock_alert'] : ""), 'class="form-control" id="stock_alert" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("stock_alert") . '"');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="controls">
                        <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>

                        <a href="<?= site_url('reports/quantity_alerts') ?>" type="reset" id="report_reset"
                            class="btn btn-warning input-xs">Reset </a>
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
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?php echo $this->lang->line("image"); ?></th>
                                <th><?php echo $this->lang->line("product_code"); ?></th>
                                <th><?php echo $this->lang->line("Category_Name"); ?></th>
                                <th><?php echo $this->lang->line("product_name"); ?></th>
                                <th><?php echo $this->lang->line("Variant _Name"); ?></th>
                                <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) { ?>
                                    <th><?php echo $this->lang->line("Batch No."); ?></th>
                                <?php } ?>
                                <th><?php echo $this->lang->line("Reorder_Quantity"); ?></th>
                                <th><?php echo $this->lang->line("Stock_Quantity"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>

                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?php echo $this->lang->line("image"); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) { ?>
                                    <th></th>
                                <?php } ?>
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
    $('#form').hide();
    $('.toggle_down').click(function() {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function() {
        $("#form").slideUp();
        return false;
    });

    $('#pdf').click(function(event) {
        event.preventDefault();
        window.location.href =
            "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/pdf')?>";
        return false;
    });
    $('#xls').click(function(event) {
        event.preventDefault();
        window.location.href =
            "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/xls')?>";
        return false;
    });
    $('#image').click(function(event) {
        event.preventDefault();
        window.location.href =
            "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/0/img')?>";
        /* html2canvas($('.box'), {
             onrendered: function (canvas) {
                 var img = canvas.toDataURL()
                 window.open(img);
             }
         });*/
        return false;
    });
});
</script>