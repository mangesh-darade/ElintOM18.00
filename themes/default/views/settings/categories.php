<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    $('#CategoryTable').dataTable({
        "aaSorting": [
            [3, "asc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "<?= lang('all') ?>"]
        ],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= site_url('system_settings/getCategories') ?>',
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
        "aoColumns": [{
                "bSortable": false,
                "mRender": function(data, type, row) {
                    return '<input type="checkbox" data-id="' + row[0] +
                        '" class="category-checkbox custom-checkbox"  />';
                }
            },
            {
                "bSortable": false,
                "mRender": img_hl
            },
            null, null, null, null, {
                "mRender": function(data, type, row) {
                    var value = data !== null && data !== undefined ? data : '';
                    return '<input type="number" min="0" max="999" value="' + value +
                        '" class="form-control rank-input text-right" style="width: 70px;" />';
                },

            },
            {
                "bSortable": false,
                "mRender": function(data, type, row) {
                    const isChecked = (data === '1');
                    return `<div style="text-align: center;"><input type="checkbox" ${isChecked ? 'checked' : ''} class="category-checkbox custom-checkbox" /></div>`;
                }
            },
            {
                "bSortable": false
            }
        ],
    });

});
</script>
<script>
$(document).ready(function() {
    $('#submitButton').on('click', function() {
        var dataToSubmit = [];
        $('#CategoryTable tbody tr').each(function() {
            var row = $(this);
            var rowId = row.find('input[type="checkbox"]').data('id');
            var rank = row.find('.rank-input').val();
            var visible = row.find('.category-checkbox').is(':checked') ? 1 : 0;

            dataToSubmit.push({
                id: rowId,
                rank: rank,
                visible: visible
            });
            console.log("dataToSubmit");
            console.log(dataToSubmit);
        });
        $.ajax({
            type: 'POST',
            url: '<?= site_url('system_settings/saveCategories') ?>', // Your controller method
            data: {
                categories: dataToSubmit,
                "<?= $this->security->get_csrf_token_name() ?>": "<?= $this->security->get_csrf_hash() ?>"
            },
            success: function(response) {
                // location.reload();
            },
            error: function(xhr, status, error) {
            }
        });
    });

});
</script>
<style>
#submitButton {
    margin-top: 0.4rem;
    margin-right: 2rem;
    padding: 4px 1rem;
    width: auto !important;
}
.custom-checkbox {
    width: 23px;
    height: 23px;
    cursor: pointer;
}
.box1 {
    text-align: end;
}
</style>
<?= form_open('system_settings/category_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('categories'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <button id="submitButton" class="btn btn-primary" data-action="submitButton">Submit</button>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo site_url('system_settings/add_category'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_category') ?>
                            </a>
                        </li>
                        <input type="hidden" id="selected-id" placeholder="Selected ID will appear here" name="Checkbox[]">
                        <li>
                            <a href="<?php echo site_url('system_settings/import_categories'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('import_categories') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_categories') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('list_results'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                
                <div class="table-responsive">
                    <table id="CategoryTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?= lang("image"); ?>
                                </th>
                                <th><?= lang("category_code"); ?></th>
                                <th><?= lang("category_name"); ?></th>
                                <th><?= lang("parent_category"); ?></th>
                                <th><?= lang("Tax Rate"); ?></th>
                                <th><?= lang("Rank"); ?></th>
                                <th><?= lang("Visible"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="dataTables_empty">
                                    <?= lang('loading_data_from_server') ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
        $('#submitButton').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
    // Add an event listener for the checkbox change event
    document.addEventListener('change', function(event) {
    if (event.target && event.target.classList.contains('category-checkbox')) {
        var checkbox = event.target;
        var dataId = checkbox.getAttribute('data-id');
        var inputField = document.getElementById('selected-id');
        var currentValue = inputField.value;

        if (checkbox.checked) {
            if (currentValue) {
                inputField.value += ',' + dataId;
            } else {
                inputField.value = dataId;
            }
        } else {
            var ids = currentValue.split(',');
            var index = ids.indexOf(dataId);

            if (index > -1) {
                ids.splice(index, 1);
                inputField.value = ids.join(',');
            }
        }
    }
});


</script>

