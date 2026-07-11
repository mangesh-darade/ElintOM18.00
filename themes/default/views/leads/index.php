<style>
    thead th:nth-child(8) .yadcf-filter-wrapper input
    {
    text-align:center;
    
    }

    tbody td:nth-child(8) 
    {
        text-align:center;
    }
   

</style>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#LeadData').dataTable({
            "aaSorting": [[7, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('Leads/get_leads') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bVisible": false, 
                // "mRender": checkbox
            }, null, 
            null, 
            null, 
            null, 
            null, 
            null,
            null,
            null, {"bSortable": false}]
        }).dtFilter([
         
            // {column_number: 1, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},          
            // {column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            // {column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            // {column_number: 4, filter_default_label: "[<?=lang('email');?>]", filter_type: "text", data: []},
            // {column_number: 5, filter_default_label: "[<?=lang('city');?>]", filter_type: "text", data: []},            
            // {column_number: 6, filter_default_label: "[<?=lang('pan_number');?>]", filter_type: "text", data: []},
             
        ], "footer");
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('leads/salesshaff_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('All Leads'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= site_url('Leads/add'); ?>"  id="add"><i class="fa fa-plus-circle"></i> <?= lang("Add"); ?></a></li>
                        <!-- <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li><a href="#" id="pdf" data-action="export_pdf"><i  class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a></li> -->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?> </p>

                <div class="table-responsive">
                    <table id="LeadData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="text-align: center;">
                                <input class="checkbox checkth col-md-1" type="checkbox" name="check"/>
                            </th>                           
                            <th class="col-lg-3"><?= lang("Full_Name"); ?></th>
                            <th><?= lang("Mobile"); ?></th>
                            <!-- <th><?= lang("Email"); ?></th> -->
                            <th class="col-lg-1"><?= lang("City"); ?></th>
                            <th class="col-lg-2"><?= lang("Product Sel 1"); ?></th>
                            <!-- <th><?= lang("Type"); ?></th>
                            <th><?= lang("Address"); ?></th> -->
                            <th class="col-lg-2"><?= lang("Business Name"); ?></th>
                            <th class="col-lg-1"><?= lang("Source"); ?></th>
                            <th class="col-lg-2"><?= lang("Created At"); ?></th>
                            <th class="col-lg-1"><?= lang("Created By"); ?></th>

                            <th style="width:5px;"><?= lang("actions"); ?></th>
                            

                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                        <!-- <th style="text-align: center;">
                        <input class="checkbox checkft" type="checkbox" name="check"/>
                        </th> -->

                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th> 
                            <th></th>                            
                            <th></th>                            
                            <th></th>                            
                            <th></th>                            
                            <th></th>                            
                            <th class="text-center"><?= lang("actions"); ?></th>
                            <!-- <th></th>
                            <th></th> -->

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
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>
	

