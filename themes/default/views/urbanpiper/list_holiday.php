<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#HolidayData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('Omnichannel/get_holidays') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, null, null, null,null, {"bSortable": false}]
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?= lang('day');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('date');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('start_time');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?= lang('end_time');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?= lang('special_hours');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?= lang('site_banner_text');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('Omnichannel/holiday_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">

        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('Holidays'); ?></h2>


        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= site_url('Omnichannel/add_holiday'); ?>"><i class="fa fa-plus-circle"></i> <?= lang("Add_holiday"); ?></a></li>
                        <li>
                            <a href="<?= site_url('Omnichannel/import_holiday') ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-file-excel-o"></i> <?= lang('Import Holiday') ?>
                            </a>
                        </li>
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li><a href="#" id="pdf" data-action="export_pdf"><i  class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#" class="bpo" title="<b><?= $this->lang->line("delete_holiday") ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                               data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?= $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

                <p class="introtext"><?= lang('list_results'); ?> </p>

                <div class="table-responsive">
                    <table id="HolidayData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>                            
                            <th><?= lang("Day"); ?></th>
                            <th><?= lang("Date"); ?></th>
                            <th><?= lang("Start_Time"); ?></th>
                            <th><?= lang("End_Time"); ?></th>
                            <th><?= lang("Special_Hours"); ?></th>
                            <th><?= lang("Site Banner Text"); ?></th>
                            <th style="width:85px;"><?= lang("Actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th style="width:85px;" class="text-center"><?= lang("actions"); ?></th>
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
<script>
  $(document).ready(function() {
    setTimeout(function() {
      $('.alert-success').fadeOut('slow');
    }, 3000);
  });
</script>
