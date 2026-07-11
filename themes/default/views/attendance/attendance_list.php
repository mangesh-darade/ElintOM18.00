<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#attendanceFilterForm').show();
        $('.attendance-toggle-up').click(function (e) {
            e.preventDefault();
            $('#attendanceFilterForm').slideUp();
        });
        $('.attendance-toggle-down').click(function (e) {
            e.preventDefault();
            $('#attendanceFilterForm').slideDown();
        });

        $(document).on('click', '.attendance-row-delete', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var url = $btn.data('url');
            var csrfName = $btn.data('csrfName');
            var csrfHash = $btn.data('csrfHash');
            var message = $btn.data('confirm') || 'Are you sure?';
            attendanceConfirmDelete(url, csrfName, csrfHash, message);
        });
    });

    function attendanceConfirmDelete(url, csrfName, csrfHash, message) {
        var text = (typeof message === 'string' && message) ? message : 'Are you sure?';
        if (!window.confirm(text)) {
            return false;
        }

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = csrfName;
        csrf.value = csrfHash;
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
        return false;
    }
</script>
<style type="text/css">
.attendance-list-table th.attendance-col-date,
.attendance-list-table td.attendance-col-date {
    min-width: 118px;
    width: 1%;
    white-space: nowrap;
    vertical-align: middle;
}
</style>
<?php
$queryFilters = array(
    'user_id' => isset($attendance_filter_user_id) && !empty($attendance_filter_user_id) ? (int) $attendance_filter_user_id : '',
    'attendance_date' => isset($attendance_filter_date) ? $attendance_filter_date : ''
);
$baseAttendanceUrl = site_url('attendance');
$excelUrl = $baseAttendanceUrl . '?' . http_build_query(array_merge($queryFilters, array('export' => 'excel')));
$pdfUrl = $baseAttendanceUrl . '?' . http_build_query(array_merge($queryFilters, array('export' => 'pdf')));
$showUserFilter = empty($attendance_scope_user_id);
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar-check-o"></i>Attendance List</h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="attendance-toggle-up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="attendance-toggle-down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if (!empty($can_index_attendance)) { ?>
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
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext">Use filters to view attendance and download as Excel/PDF.</p>
    <div class="box-content">
        <div id="attendanceFilterForm">
            <form method="get" action="<?= site_url('attendance'); ?>">
                <div class="row">
                    <?php if ($showUserFilter) { ?>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="attendance_user_id">User Name</label>
                            <select name="user_id" id="attendance_user_id" class="form-control select" style="width:100%">
                                <option value="">All</option>
                                <?php if (!empty($attendance_filter_users)) { foreach ($attendance_filter_users as $user) { ?>
                                    <?php
                                    $name = trim((string) $user->first_name . ' ' . (string) $user->last_name);
                                    if ($name === '') {
                                        $name = (string) $user->phone;
                                    }
                                    ?>
                                    <option value="<?= (int) $user->id; ?>" <?= ((int) $attendance_filter_user_id === (int) $user->id) ? 'selected' : ''; ?>>
                                        <?= html_escape($name); ?>
                                    </option>
                                <?php } } ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="attendance_date">Date</label>
                            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?= html_escape(isset($attendance_filter_date) ? $attendance_filter_date : ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="<?= site_url('attendance'); ?>" class="btn btn-success">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <?php if (!empty($can_index_attendance)) { ?>
        <?= form_open('attendance/list_actions', 'id="action-form"'); ?>
    <?php } ?>
    <div class="box-content">
        <div class="table-responsive">
            <table class="table table-bordered table-striped attendance-list-table">
                <thead>
                    <tr>
                        <th style="min-width:30px; width: 30px; text-align:center;">
                            <input class="checkbox checkth" type="checkbox" name="check"/>
                        </th>
                        <th>Name</th>
                        <th class="attendance-col-date">Date</th>
                        <th>Landmark</th>
                        <th>Derived Location</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendance_rows)) { ?>
                        <?php
                        $session_uid = (int) $this->session->userdata('user_id');
                        $view_right = $this->session->userdata('view_right');
                        $edit_right = $this->session->userdata('edit_right');
                        $view_all = isset($attendance_has_view_all)
                            ? (bool) $attendance_has_view_all
                            : attendance_sma_view_all_records($Owner, $Admin, $view_right);
                        $edit_any = isset($attendance_has_edit_any)
                            ? (bool) $attendance_has_edit_any
                            : attendance_sma_edit_any_record($Owner, $Admin, $edit_right);
                        $perm_index = !empty($can_index_attendance);
                        $perm_edit = !empty($can_edit_attendance);
                        $perm_delete = !empty($can_delete_attendance);
                        ?>
                        <?php foreach ($attendance_rows as $row) { ?>
                            <?php
                            $row_uid = (int) $row->user_id;
                            $is_own_row = ($row_uid === $session_uid);
                            $checkInTs = !empty($row->check_in) ? strtotime($row->check_in) : false;
                            $checkOutTs = !empty($row->check_out) ? strtotime($row->check_out) : false;
                            $attendanceDate = $checkInTs ? date('Y-m-d', $checkInTs) : '';
                            $checkInTime = $checkInTs ? date('H:i:s', $checkInTs) : '';
                            $checkOutTime = $checkOutTs ? date('H:i:s', $checkOutTs) : '';
                            // View details: index permission + (all-records view right OR edit-any right OR own row)
                            $may_view_row = $view_all || $edit_any || $is_own_row;
                            $show_view = $perm_index && $may_view_row;
                            // Edit/delete others: module permission + profile edit_right (1); own row: module permission only
                            $may_mutate_row = $edit_any || $is_own_row;
                            $show_edit = $perm_edit && $may_mutate_row;
                            $show_delete = $perm_delete && $may_mutate_row;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <input class="checkbox multi-select" type="checkbox" name="val[]" value="<?= (int) $row->id; ?>"/>
                                </td>
                                <td><?= html_escape(attendance_display_name_for_row($row, $session_uid, isset($logged_in_display_name) ? $logged_in_display_name : '')); ?></td>
                                <td class="attendance-col-date"><?= $attendanceDate !== '' ? html_escape($attendanceDate) : '-'; ?></td>
                                <td><?= str_replace(' | ', '<br>', html_escape($row->landmark)); ?></td>
                                <td><?= $row->derived_location !== null && trim((string) $row->derived_location) !== '' ? html_escape($row->derived_location) : 'None'; ?></td>
                                <td><?= $checkInTime !== '' ? html_escape($checkInTime) : '-'; ?></td>
                                <td><?= $checkOutTime !== '' ? html_escape($checkOutTime) : '-'; ?></td>
                                <td class="text-center" style="width: 100px;">
                                    <?php if ($show_view || $show_edit || $show_delete) { ?>
                                    <div class="btn-group">
                                        <?php if ($show_view) { ?>
                                        <a href="<?= site_url('attendance/details/' . (int) $row->id) . '?modal=1'; ?>" class="btn btn-xs btn-info tip" title="<?= lang('view') ?>" data-toggle="modal" data-target="#myModal"><i class="fa fa-eye"></i></a>
                                        <?php } ?>
                                        <!-- <?php if ($show_edit) { ?>
                                        <a href="<?= site_url('attendance/edit/' . (int) $row->id); ?>" class="btn btn-xs btn-warning tip" title="<?= lang('edit') ?>"><i class="fa fa-pencil"></i></a>
                                        <?php } ?> -->
                                        <?php if ($show_delete) { ?>
                                        <button type="button"
                                                class="btn btn-xs btn-danger tip attendance-row-delete"
                                                title="<?= lang('delete') ?>"
                                                data-url="<?= html_escape(site_url('attendance/delete/' . (int) $row->id)); ?>"
                                                data-csrf-name="<?= html_escape($this->security->get_csrf_token_name()); ?>"
                                                data-csrf-hash="<?= html_escape($this->security->get_csrf_hash()); ?>"
                                                data-confirm="<?= html_escape(lang('r_u_sure')); ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <?php } ?>
                                    </div>
                                    <?php } else { ?>
                                    <span class="text-muted">&mdash;</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8" class="text-center">No attendance found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($can_index_attendance)) { ?>
        <div style="display:none;">
            <input type="hidden" name="form_action" value="" id="form_action"/>
            <?= form_submit('performAction', 'performAction', 'id="action-form-submit"'); ?>
        </div>
        <?= form_close(); ?>
    <?php } ?>
</div>

