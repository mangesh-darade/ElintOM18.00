<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Transfer RM view
// This page mirrors transfers/request but loads data from getTransfersRequestsRM,
// which filters to only those transfer requests that are linked to a purchase (purchase_id present).
?>
<?php $user_warehouse = $this->session->userdata('warehouse_id');
      $v= ($user_warehouse=='0' ||$user_warehouse==NULL)?'':"?warehouse=" . str_replace(",", "_",$user_warehouse);
?>
<script>
    $(document).ready(function () {
        var oTable = $('#TOData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            // Load only purchase-linked transfer requests (RM)
            'sAjaxSource': '<?= site_url('transfers/getTransfersRequestsRM').$v  ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                {"mRender": fld},
                null,
                null,
                null,
                {"mRender": row_status},
                {"bSortable": false, "mRender": attachment},
                {"bSortable": false}
            ],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var ntdCells = nRow.getElementsByTagName('td');
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "request_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                // Footer callback removed as amount columns are removed
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('purchase_ref_no'); ?>]", filter_type: "text", data: []},
            {
                column_number: 3,
                filter_default_label: "[<?= lang('location'); ?> (<?= lang('from'); ?>)]",
                filter_type: "text", data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('vendor'); ?>]",
                filter_type: "text", data: []
            },
            {column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('transfers/request_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i>Transfer RM</h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <?php if ($Admin || $Owner || $GP['transfers-add_request']) { ?>
                        <li>
                            <a href="<?= site_url('transfers/add_request') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_product_request') ?>
                            </a>
                        </li>
                    <?php } ?>
                 </ul>
             </li>
            </ul>
        </div>
    </div>
    <!--<p class="introtext"><?= lang('list_results'); ?></p>-->
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("Date"); ?></th>
                            <th><?= lang('Purchase_Ref_No'); ?></th>
                            <th><?= lang('Location'); ?> (<?= lang('From'); ?>)</th>
                            <th><?= lang('Vendor'); ?></th>
                            <th><?= lang("Status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                           <th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
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
