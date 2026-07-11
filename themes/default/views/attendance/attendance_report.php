<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#attendanceReportFilterForm').show();
        $('.attendance-report-toggle-up').click(function (e) {
            e.preventDefault();
            $('#attendanceReportFilterForm').slideUp();
        });
        $('.attendance-report-toggle-down').click(function (e) {
            e.preventDefault();
            $('#attendanceReportFilterForm').slideDown();
        });
    });
</script>
<?php
$queryFilters = array(
    'user_id' => isset($attendance_filter_user_id) && !empty($attendance_filter_user_id) ? (int) $attendance_filter_user_id : '',
    'start_date' => isset($attendance_filter_start_date) ? $attendance_filter_start_date : '',
    'end_date' => isset($attendance_filter_end_date) ? $attendance_filter_end_date : ''
);
$baseReportUrl = site_url('attendance/report');
$excelUrl = $baseReportUrl . '?' . http_build_query(array_merge($queryFilters, array('export' => 'excel')));
$pdfUrl = $baseReportUrl . '?' . http_build_query(array_merge($queryFilters, array('export' => 'pdf')));
$showUserFilter = empty($attendance_scope_user_id);
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text-o"></i>Attendance Report</h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="attendance-report-toggle-up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="attendance-report-toggle-down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= $excelUrl; ?>">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $pdfUrl; ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext">Use filters to generate attendance report and export.</p>
    <div class="box-content">
        <div id="attendanceReportFilterForm">
            <form method="get" action="<?= site_url('attendance/report'); ?>">
                <div class="row">
                    <?php if ($showUserFilter) { ?>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="report_user_id">User Name</label>
                            <select name="user_id" id="report_user_id" class="form-control select" style="width:100%">
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
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="report_start_date">Start Date</label>
                            <input type="date" name="start_date" id="report_start_date" class="form-control" value="<?= html_escape(isset($attendance_filter_start_date) ? $attendance_filter_start_date : '') ?>">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="report_end_date">End Date</label>
                            <input type="date" name="end_date" id="report_end_date" class="form-control" value="<?= html_escape(isset($attendance_filter_end_date) ? $attendance_filter_end_date : '') ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="<?= site_url('attendance/report'); ?>" class="btn btn-success">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Date</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Landmark</th>
                        <th>Derived Location</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendance_rows)) { ?>
                        <?php foreach ($attendance_rows as $row) { ?>
                            <?php
                            $displayName = attendance_display_name_for_row($row, (int) $this->session->userdata('user_id'), isset($logged_in_display_name) ? $logged_in_display_name : '');
                            ?>
                            <tr>
                                <td><?= html_escape($displayName); ?></td>
                                <td><?= html_escape($row->created_at); ?></td>
                                <td><?= $row->latitude !== null ? html_escape((string) $row->latitude) : 'None'; ?></td>
                                <td><?= $row->longitude !== null ? html_escape((string) $row->longitude) : 'None'; ?></td>
                                <td><?= str_replace(' | ', '<br>', html_escape($row->landmark)); ?></td>
                                <td><?= $row->derived_location !== null && trim((string) $row->derived_location) !== '' ? html_escape($row->derived_location) : 'None'; ?></td>
                                <td><?= html_escape($row->check_in); ?></td>
                                <td><?= html_escape($row->check_out); ?></td>
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
</div>
