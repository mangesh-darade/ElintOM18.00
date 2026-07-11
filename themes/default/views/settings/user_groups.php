<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    #groupSettingsScreensModal .group-screen-toggle {
        transform: scale(1.6);
        -webkit-transform: scale(1.6);
        width: 16px;
        height: 16px;
        margin: 6px 0;
        cursor: pointer;
        vertical-align: middle;
    }
</style>
<script>
    $(document).ready(function () {
        var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

        $('#GPData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "oTableTools": {
                "sSwfPath": "assets/media/swf/copy_csv_xls_pdf.swf",
                "aButtons": ["csv", {"sExtends": "pdf", "sPdfOrientation": "landscape", "sPdfMessage": ""}, "print"]
            },
            "aoColumns": [{"bSortable": false}, null, null, null, {"bSortable": false}
            ]

        });

        $(document).on('click', '.open-group-settings-screens', function (e) {
            e.preventDefault();
            var groupId = $(this).data('group-id');
            var groupName = $(this).data('group-name');
            $('#group-settings-group-id').val(groupId);
            $('#group-settings-group-name').text(groupName);
            $('#group-settings-screens-body').html('<tr><td colspan="2" class="text-center">Loading...</td></tr>');
            $('#groupSettingsScreensModal').modal('show');

            $.ajax({
                url: '<?= site_url('system_settings/group_settings_screens'); ?>/' + groupId,
                type: 'GET',
                dataType: 'json',
                success: function (resp) {
                    if (!resp || resp.status !== 'success') {
                        $('#group-settings-screens-body').html('<tr><td colspan="2" class="text-center text-danger">Unable to load screens.</td></tr>');
                        return;
                    }
                    var rows = '';
                    if (!resp.screens || !resp.screens.length) {
                        rows = '<tr><td colspan="2" class="text-center">No screens found.</td></tr>';
                    } else {
                        $.each(resp.screens, function (_, item) {
                            var checked = parseInt(item.active, 10) === 1 ? 'checked' : '';
                            rows += '<tr>' +
                                '<td>' + item.screen_name + '</td>' +
                                '<td class="text-center">' +
                                '<input type="checkbox" class="group-screen-toggle" data-column="' + item.column + '" ' + checked + '>' +
                                '</td>' +
                                '</tr>';
                        });
                    }
                    $('#group-settings-screens-body').html(rows);
                },
                error: function () {
                    $('#group-settings-screens-body').html('<tr><td colspan="2" class="text-center text-danger">Unable to load screens.</td></tr>');
                }
            });
        });

        $(document).on('change', '.group-screen-toggle', function () {
            var $checkbox = $(this);
            var groupId = $('#group-settings-group-id').val();
            var active = $checkbox.is(':checked') ? 1 : 0;
            var previousState = active ? false : true;
            var payload = {
                group_id: groupId,
                column: $checkbox.data('column'),
                active: active
            };
            payload[csrfName] = csrfHash;

            $checkbox.prop('disabled', true);

            $.ajax({
                url: '<?= site_url('system_settings/update_group_settings_screen'); ?>',
                type: 'POST',
                dataType: 'json',
                data: payload,
                success: function (resp) {
                    if (resp && resp.csrf_hash) {
                        csrfHash = resp.csrf_hash;
                    }
                    if (!resp || resp.status !== 'success') {
                        $checkbox.prop('checked', previousState);
                        alert((resp && resp.message) ? resp.message : 'Unable to update.');
                    }
                },
                error: function (xhr) {
                    $checkbox.prop('checked', previousState);
                    alert(xhr && xhr.status === 403 ? 'Security token mismatch. Please refresh page.' : 'Unable to update.');
                },
                complete: function () {
                    $checkbox.prop('disabled', false);
                }
            });
        });
    });
</script>
<?php if ($Owner) {
    echo form_open('system_settings/user_group_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('groups'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"
                                                                                  data-placement="left"
                                                                                  title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= site_url('system_settings/create_group'); ?>" data-toggle="modal"
                               data-target="#myModal"><i class="fa fa-plus"></i> <?= lang('add_group') ?></a></li>
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
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_groups") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_groups') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                
                <div class="table-responsive">
                    <table id="GPData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?php echo $this->lang->line("group_id"); ?></th>
                            <th><?php echo $this->lang->line("group_name"); ?></th>
                            <th><?php echo $this->lang->line("group_description"); ?></th>
                            <th style="min-width:95px; width:95px;"><?php echo $this->lang->line("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($groups as $group) {
                            ?>
                            <tr>
                                <td>
                                    <div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]"
                                                   value="<?= $group->id ?>"/></div>
                                </td>
                                <td><?php echo $group->id; ?></td>
                                <td><?php echo $group->name; ?></td>
                                <td><?php echo $group->description; ?></td>
                                <td style="text-align:center; white-space:nowrap;">
                                    <div class="text-center">
                                    <?php echo '<a class="tip" title="' . $this->lang->line("change_permissions") . '" href="' . site_url('system_settings/permissions/' . $group->id) . '"><i class="fa fa-tasks"></i></a> <a href="#" class="tip open-group-settings-screens" title="Settings Screens" data-group-id="' . $group->id . '" data-group-name="' . htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-cog"></i></a> <a class="tip" title="' . $this->lang->line("edit_group") . '" data-toggle="modal" data-target="#myModal" href="' . site_url('system_settings/edit_group/' . $group->id) . '"><i class="fa fa-edit"></i></a> <a href="#" class="tip po" title="' . $this->lang->line("delete_group") . '" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger\' href=\'' . site_url('system_settings/delete_group/' . $group->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"><i class="fa fa-trash-o"></i></a>'; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>

<div class="modal fade" id="groupSettingsScreensModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Group Settings Screens - <span id="group-settings-group-name"></span></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="group-settings-group-id" value="">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Screen</th>
                                <th style="width:140px;" class="text-center">Active</th>
                            </tr>
                        </thead>
                        <tbody id="group-settings-screens-body">
                            <tr>
                                <td colspan="2" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>