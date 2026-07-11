<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
#DepData td:nth-child(3),
#DepData th:nth-child(3),
#DepData td:nth-child(4),
#DepData th:nth-child(4),
#DepData td:nth-child(5),
#DepData th:nth-child(5) {
    text-align: right;
    /* Align text to the right in the 3rd, 4th, and 5th columns */
}

#DepData th:nth-child(1),
#DepData td:nth-child(1) {
    width: 15% !important;
}

/* Category */
#DepData th:nth-child(2),
#DepData td:nth-child(2) {
    width: 15% !important;
}

/* Product */
#DepData th:nth-child(3),
#DepData td:nth-child(3) {
    width: 6% !important;
}

/* Quantity */
#DepData th:nth-child(4),
#DepData td:nth-child(4) {
    width: 6% !important;
}

/* Expected Rate */
#DepData th:nth-child(5),
#DepData td:nth-child(5) {
    width: 6% !important;
}

/* Finalized Rate */
#DepData th:nth-child(6),
#DepData td:nth-child(6) {
    width: 7% !important;
}

/* Status */
#DepData th:nth-child(7),
#DepData td:nth-child(7) {
    width: 10% !important;
}

/* Process Stage */
#DepData th:nth-child(8),
#DepData td:nth-child(8) {
    width: 10% !important;
}

/* Created At */
#DepData th:nth-child(9),
#DepData td:nth-child(9) {
    width: 18% !important;
}

/* Description */
#DepData th:nth-child(10),
#DepData td:nth-child(10) {
    width: 5% !important;
}

/* Actions */


#DepData {
    width: 100%;
    table-layout: auto;
}

#DepData th,
#DepData td {
    padding: 8px;
    white-space: nowrap;
}

.modal-lg {
    max-width: 90%;
    width: 90%;
}

.modal-content {
    width: 100%;
}

@media print {
    #DepData {
        width: 100%;
        table-layout: fixed;
        /* Ensures the table is laid out in a more predictable manner */
    }

    #DepData th,
    #DepData td {
        padding: 8px;
        /* Slightly increase padding for better spacing */
        white-space: normal;
        /* Allow wrapping of text inside cells */
        overflow-wrap: break-word;
        /* Ensure that long words break properly */
    }

    #DepData td:nth-child(3),
    #DepData th:nth-child(3),
    #DepData td:nth-child(4),
    #DepData th:nth-child(4),
    #DepData td:nth-child(5),
    #DepData th:nth-child(5) {
        text-align: right;
    }

    #DepData th:nth-child(1),
    #DepData td:nth-child(1) {
        width: 17% !important;
        /* Category */
    }

    #DepData th:nth-child(2),
    #DepData td:nth-child(2) {
        width: 15% !important;
        /* Product */
    }

    #DepData th:nth-child(3),
    #DepData td:nth-child(3) {
        width: 7% !important;
        /* Quantity */
    }

    #DepData th:nth-child(4),
    #DepData td:nth-child(4) {
        width: 7% !important;
        /* Expected Rate */
    }

    #DepData th:nth-child(5),
    #DepData td:nth-child(5) {
        width: 7% !important;
        /* Finalized Rate */
    }

    #DepData th:nth-child(6),
    #DepData td:nth-child(6) {
        width: 6% !important;
        /* Status */
    }

    #DepData th:nth-child(7),
    #DepData td:nth-child(7) {
        width: 10% !important;
        /* Process Stage */
    }

    #DepData th:nth-child(8),
    #DepData td:nth-child(8) {
        width: 11% !important;
        /* Created At */
    }

    #DepData th:nth-child(9),
    #DepData td:nth-child(9) {
        width: 20% !important;
        /* Description */
    }

    #DepData th:nth-child(10),
    #DepData td:nth-child(10) {
        width: 8% !important;
        /* Actions */
    }

    /* Adjust modal size for better fit */
    .modal-lg {
        max-width: 100%;
        width: 100%;
    }

    .modal-content {
        width: 100%;
    }
}
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>

            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;"
                onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel">
                <?= lang('Deals') . " (" . $deals->name . ")"; ?>
            </h4>
            <a class="btn btn-xs btn-primary pull-right open-modal"
                href="<?= base_url('leads/add_deals/' . $lead_id); ?>" style="margin-right: 20px; margin-top: -19px;">
                <i class="fa fa-plus"></i> <?= lang('Add Deal'); ?>
            </a>

        </div>

        <div class="modal-body">
            <p class="no-print"><?= lang('deposits_subheading'); ?></p>
            <div class="alerts-con"></div>

            <div class="table-responsive">
                <table id="DepData" cellpadding="0" cellspacing="0" border="0"
                    class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                        <tr class="primary">
                            <th class="col-xs-3"><?= lang("Category"); ?></th>
                            <th class="col-xs-2"><?= lang("Product"); ?></th>
                            <th class="col-xs-3"><?= lang("Quantity"); ?></th>
                            <th class="col-xs-3"><?= lang("Expected"); ?></th>
                            <th class="col-xs-3"><?= lang("Finalized"); ?></th>
                            <th class="col-xs-3"><?= lang("status"); ?></th>
                            <th class="col-xs-3"><?= lang("Process Stage"); ?></th>
                            <th class="col-xs-3"><?= lang("created_at"); ?></th>
                            <th class="col-xs-3"><?= lang("Description"); ?></th>
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <th>Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>


                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <?= $modal_js ?>
    <script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
        var oTable = $('#DepData').dataTable({
            "aaSorting": [
                [1, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('Leads/getDeals/'.$deals->Leadid) ?>',
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
            "columnDefs": [{
                "targets": [4], // Targeting the 3rd column (index starts from 0)
                "className": "text-right" // Add a class that aligns the text to the right
            }],
            "aoColumns": [null, null, {
                    "mRender": formatQuantity,
                    "bSearchable": false
                }, <?php echo '{"mRender": currencyFormat}'?>,
                <?php echo '{"mRender": currencyFormat}'?>, null, null, null, null, {
                    "bSortable": false
                }
            ],
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var quantity = 0;
                // expectedRate = 0,
                // finalizeRate = 0;
                for (var i = 0; i < aaData.length; i++) {
                    quantity += parseFloat(aaData[aiDisplay[i]][2]);
                    // expectedRate += parseFloat(aaData[aiDisplay[i]][3]);
                    // finalizeRate += parseFloat(aaData[aiDisplay[i]][4]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = formatQuantity(parseFloat(quantity));
                // nCells[3].innerHTML = currencyFormat(parseFloat(expectedRate));
                // nCells[4].innerHTML = currencyFormat(parseFloat(finalizeRate));
            }
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?=lang('Products_Name');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?=lang('Rate');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?=lang('Rate');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?=lang('Status');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 6,
                filter_default_label: "[<?=lang('Process_Stage');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 7,
                filter_default_label: "[<?=lang('Created_at');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 8,
                filter_default_label: "[<?=lang('Description');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 9,
                filter_default_label: "[<?=lang('Actions');?>]",
                filter_type: "text",
                data: []
            },
        ], "footer");
        $('div.dataTables_length select').addClass('form-control');
        $('div.dataTables_length select').addClass('select2');
        $('div.dataTables_filter input').attr('placeholder', 'product');
        $('select.select2').select2({
            minimumResultsForSearch: 7
        });
    });
    </script>
    <script>
    $(document).on('click', '.open-modal', function(e) {
        e.preventDefault(); // Prevent default link behavior
        var url = $(this).attr('href'); // Get the URL to load

        $('#myModal').modal('show'); // Show the modal first
        $('#myModal .modal-content').html(
            '<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div>'
            ); // Optional loader

        // Load the URL content into modal content area
        $.get(url, function(data) {
            $('#myModal .modal-content').html(data);
        });
    });
    </script>