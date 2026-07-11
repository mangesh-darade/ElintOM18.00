<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$warehouseIds = is_numeric($warehouse_id) ? '/' . $warehouse_id : '/0';
?>
<style>
    .order_ready td .order_created_link{
        display:none;
    }
    .invoice_created td .invoice_created_link{
        display:none;
    }
</style>
<script>
    $(document).ready(function () {
        var oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('orders/get_order_items' . $warehouseIds) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
               
               return nRow;
            }, 
            "aoColumns": [ null, null, {"mRender": formatQuantity, "bSearchable":false,"bSortable": false}, {"mRender": formatQuantity, "bSearchable":false,"bSortable": false}, {"mRender": formatQuantity, "bSearchable":false,"bSortable": false}],
            
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?= lang('Item Name'); ?>]", filter_type: "text", data: []},            
            {column_number: 1, filter_default_label: "[<?= lang('Varient Name'); ?>]", filter_type: "text", data: []},            
            {column_number: 2, filter_default_label: "[<?= lang('Unit Total'); ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('Weight (KG)'); ?>]", filter_type: "text", data: []},           
            {column_number: 4, filter_default_label: "[<?= lang('In Stock'); ?>]", filter_type: "text", data: []},           
        ], "footer");

    });

</script>

<?php
if ($Owner || $GP['bulk_actions']) {
    echo form_open('orders/get_order_items' . $warehouseIds, 'id="action-form"');
}
?> 
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-heart"></i><?= 'Order Items (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">                        
                        <li>
                            <a href="<?= base_url("orders/get_order_items".$warehouseIds."/0/1")?>" >
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url("orders/get_order_items".$warehouseIds."/1/0")?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php if (!empty($warehouses)) {     ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url( 'orders/order_items') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                if ($Owner || $Admin) {
                                    echo '<li><a href="' . site_url('orders/order_items/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                    echo '<li><a href="' . site_url('orders/order_items/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Item(s) Name</th>
                                <th>Variant Name</th>
                                <th>Total Units </th>                                
                                <th>Total Weight (KG)</th>
                                <th>Item In Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
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
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>    
