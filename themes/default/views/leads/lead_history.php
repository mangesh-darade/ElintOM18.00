<link rel="stylesheet" href="<?= $assets ?>styles/style.css" type="text/css" />
<link rel="stylesheet" href="<?= $assets ?>styles/radactor.css" type="text/css" />
<!-- Redactor CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/redactor/redactor.css">
<!-- Redactor JS -->
<script src="https://cdn.jsdelivr.net/npm/redactor/redactor.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<style>
.custom-button {
    background-color: #337ab7;
    color: white;
    padding: 7px 19px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    float: right;
}

.custom-button:hover {
    background-color: #286090;
}

table#DepData td:not(:last-child) ol,
table#DepData td:not(:last-child) ul {
    list-style: none !important;
    padding-left: 0 !important;
    margin-left: 0 !important;
}

table#DepData td:last-child ol,
table#DepData td:last-child ul {
    list-style-position: inside;
    padding-left: 15px;
}

#DepData {
    table-layout: fixed;
    word-wrap: break-word;
}

#DepData th,
#DepData td {
    white-space: normal;
    overflow-wrap: break-word;
    word-wrap: break-word;
    max-width: 300px;
}

#DepData th:nth-child(1),
#DepData td:nth-child(1),
#DepData th:nth-child(2),
#DepData td:nth-child(2) {
    width: 150px;
    white-space: nowrap;
}

#DepData th:nth-child(3),
#DepData td:nth-child(3) {
    width: auto;
}
#DepData th:nth-child(1) { width: 6.4px!important;}
#DepData th:nth-child(2) { width: 12.4px!important; }
#DepData th:nth-child(3) { width: 36px!important; } /* Or a larger percentage */

.form-group{
    display: flex; 
    flex-direction: column;
}
.table-responsive{
    display: flex; 
    flex-direction: column;
}
.modal-dialog {
    width: 80%; 
    max-width: 1200px; 
}
.scrollable-table {
    max-height: 90vh;
    overflow-y: auto;
    height:90vh;
}


.dataTables_length select {
    font-size: 16px;          
    padding: 8px;            
    height: auto;             
    width: auto;             
    border-radius: 5px;       
    background-color: #f5f5f5; 
}

html {
    overflow-y: auto!important;
    height: 100%;
}
/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-dialog {
        width: 90%; 
    }

    .modal-body {
        padding: 10px; 
    }

    .form-group, .table-responsive {
        flex-direction: column; 
    }
}

/* For very small screens like mobile phones */
@media (max-width: 480px) {
    .modal-dialog {
        width: 95%; 
    }

    .custom-button {
        width: 100%; 
        padding: 10px; 
    }

    .table-responsive {
        overflow-x: auto;
    }

    #DepData th, #DepData td {
        font-size: 12px; 
    }
}

</style>
<style>
    .scrollable-tbody {
        display: block;
        max-height: 500px; /* Adjust height as needed */
        overflow-y: auto;
    }
    .table-header {
        display: block;
        max-height: 1px; /* Adjust height as needed */
        overflow-y: auto;
    }

    .scrollable-tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .scrollable-tbody td {
        word-wrap: break-word;
    }

    table.scrollable-table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-header,
    .table-footer {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
</style>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
                <?php echo lang('Leads') . " (" . $leadDetails->full_name . ")"; ?></h4>
        </div>

        <div class="modal-body">
        <p class="no-print"><strong><?= lang('Lead_History'); ?></strong></p>


            <div class="row">
    <!-- Left Side: Comment Form -->
    <div class="col-md-4">
        <div class="form-group">
            <label for="comment">Add Comment:</label><br>
            <textarea id="comment" name="comment" class="pa form-control kb-text payment_note"
                placeholder="Write your comment here..."></textarea><br><br>
            <button id="addCommentBtn" class="custom-button">Add</button>
        </div>
    </div>
    <div class="alerts-con"></div>


    <!-- Right Side: Comment Table -->
    <div class="col-md-8">
        <div class="table-responsive">
            <table id="DepData"  cellpadding="0" cellspacing="0" border ="0"
                class="table table-bordered table-condensed table-hover table-striped">
                <thead class="table-header scrollable-head">
                    <tr class="primary">
                        <th ><?= lang("Created_at"); ?></th>
                        <th ><?= lang("Created_by"); ?></th>
                        <th ><?= lang("Comments"); ?></th>
                    </tr>   
                </thead>
                <tbody class="scrollable-tbody">
                    <tr>
                        <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


        </div>
    </div>

    <?= $modal_js ?>
    <script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
        commentsHistory();


        // Function to load the comment history
        function commentsHistory() {
            var oTable = $('#DepData').dataTable({
                "aaSorting": [
                    [1, "ASC"]
                ],
                "aLengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "<?= lang('all') ?>"]
                ],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true,
                'bServerSide': true,
                'sAjaxSource': '<?= site_url('Leads/getHistory/'.$lead_id) ?>',
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
                "aoColumns": [null, null, null]
            });
        }

        // Adding a comment
        $('#addCommentBtn').click(function() {
            var comment = $('#comment').val(); // Get the comment text from the textarea
            if (comment == '') {
                alert('Please enter a comment.');
                return false;
            }

            // Send comment to the server via AJAX
            $.ajax({
                url: "<?php echo base_url('Leads/saveHistory/' . $lead_id); ?>",
                type: "GET",
                data: {
                    comment: comment
                },
                success: function(response) {
                    if (response == 'success') {
                        // Show a success notification using Toastr
                        toastr.success('Comment added successfully!',
                        'Success'); // Success notification
                        $('#comment').val(''); // Clear the textarea
                        refreshCommentsGrid();
                    } else {
                        // Show an error notification using Toastr
                        toastr.error('Failed to add comment.',
                        'Error'); // Error notification
                    }
                },
                error: function() {
                    alert('An error occurred.');
                }
            });
        });

        function refreshCommentsGrid() {
            var oTable = $('#DepData').DataTable(); // Get the existing DataTable instance
            oTable
                .fnClearTable();
            oTable.fnDraw(); // Re-draw the table with the updated data
        }

        // Redactor editor initialization
        $('#comment').redactor({
            minHeight: 200,
            placeholder: "Write your comment here...",
            toolbarFixed: true,
            buttons: ['bold', 'italic', 'underline', 'unorderedlist', 'orderedlist', 'link'],
        });
    });
    </script>