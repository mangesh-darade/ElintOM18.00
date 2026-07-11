<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script>
    $(document).ready(function () {
        var oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('products/getBatcheslist' )?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                null,
                null,
                null,
                {
                    "mRender": function(data, type, row) {
                        return currencyFormat(data); // Apply currency format for the Cost column
                    }, "sClass": "text-right"},
                {"mRender": function(data, type, row) {
                        return currencyFormat(data); //Apply currency format for the Price column
                    }, "sClass": "text-right"},
                {"mRender": function(data, type, row) {
                        return currencyFormat(data); // Apply currency format for the Mrp column
                    }, "sClass": "text-right"},
                {"bSortable": false}
            ],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('Product');?> ", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Variants');?> ", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Batch No');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('cost');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('price');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('mrp');?>]", filter_type: "text", data: []},
           ], "footer");
        });
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
           
<style>
.text-right {
    text-align: right;
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?=lang('Products Batches') ?></h2>
        <?php if($Owner || $GP['products-add_batch']) { ?> 
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?=site_url('products/add_batch')?>" data-toggle="modal" data-target="#myModal" style="margin-right: 10px;">
                        <i class="icon fa fa-plus tip" data-placement="left" title="<?=lang("Add Batch")?>"></i><?=lang('Add New Batch')?> 
                    </a>
                </li>
            </ul>
        </div>
        <?php } ?>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("Product"); ?></th>
                            <th><?= lang("Variants"); ?></th>
                            <th><?= lang("Batch No."); ?></th>
                            <th class="text-right"><?= lang("cost"); ?></th>
                            <th class="text-right"><?= lang("price"); ?></th>
                            <th class="text-right"><?= lang("mrp"); ?></th>
                            <th width="200px" style="width: 200px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th>
                            <th class="text-right"></th>
                            <th class="text-right"></th>
                            <th class="text-right"></th>
                            <th style="width: 200px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?=form_close()?>

<script>
function confirm_delete(batchNo) {
    if (!confirm('Are you really sure you want to delete batch ' + batchNo + ' ?')) {
        return false;
    }
    
    return true;
}


</script>
