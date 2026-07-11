<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('deposits') . " (" . $company->name . ")"; ?></h4>
        </div>

        <div class="modal-body">
            <p class="no-print"><?= lang('deposits_subheading'); ?></p>
            <div class="alerts-con"></div>

            <div class="table-responsive">
                <table id="DepData" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                    <tr class="primary">
                        <th></th>
                        <th class="col-xs-3"><?= lang("date"); ?></th>
                        <th class="col-xs-2"><?= lang("Invoice No"); ?></th>
                        <th class="col-xs-3"><?= lang("Payment Ref "); ?></th>
                        <th class="col-xs-3"><?= lang("Amount"); ?></th>
                        <th class="col-xs-3"><?= lang("Balance"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?= $modal_js ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.tip').tooltip();
            var oTable = $('#DepData').dataTable({
                "aaSorting": [[1, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('customers/get_deposits_history/'.$company->id) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"bVisible": false},{"mRender": fld},null, null,{"mRender": currencyFormat},{"mRender": currencyFormat}]
            });
            $('div.dataTables_length select').addClass('form-control');
            $('div.dataTables_length select').addClass('select2');
            $('div.dataTables_filter input').attr('placeholder', 'Date (yyyy-mm-dd)');
            $('select.select2').select2({minimumResultsForSearch: 7});
        });
    </script>

