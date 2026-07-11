<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $isModal = !empty($is_modal); ?>
<?php
$phoneText = ($attendance->phone !== null && trim((string) $attendance->phone) !== '') ? $attendance->phone : 'None';
$checkOutText = ($attendance->check_out !== null && trim((string) $attendance->check_out) !== '') ? $attendance->check_out : 'None';
$latitudeText = ($attendance->latitude !== null && trim((string) $attendance->latitude) !== '') ? $attendance->latitude : 'None';
$longitudeText = ($attendance->longitude !== null && trim((string) $attendance->longitude) !== '') ? $attendance->longitude : 'None';
$landmarkText = ($attendance->landmark !== null && trim((string) $attendance->landmark) !== '') ? $attendance->landmark : 'None';
$derivedLocationText = ($attendance->derived_location !== null && trim((string) $attendance->derived_location) !== '') ? $attendance->derived_location : 'None';
$sessionUid = (int) $this->session->userdata('user_id');
$attendanceDisplayName = attendance_display_name_for_row($attendance, $sessionUid, isset($logged_in_display_name) ? $logged_in_display_name : '');
?>
<style>
    .attendance-details-table th {
        width: 170px;
        white-space: nowrap;
        background: #f7f7f7;
    }
    .attendance-details-table td {
        word-break: break-word;
    }
    @media (max-width: 767px) {
        .attendance-details-table th,
        .attendance-details-table td {
            display: block;
            width: 100% !important;
            white-space: normal;
        }
        .attendance-details-table tr {
            border-top: 1px solid #ddd;
        }
        .attendance-details-table th {
            border-bottom: none !important;
            padding-bottom: 4px !important;
        }
        .attendance-details-table td {
            border-top: none !important;
            padding-top: 0 !important;
            padding-bottom: 10px !important;
        }
    }
</style>
<?php if ($isModal) { ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title"><i class="fa fa-info-circle"></i> Attendance Details</h4>
        </div>
        <div class="modal-body">
<?php } else { ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i>Attendance Details</h2>
    </div>
    <div class="box-content">
<?php } ?>
        <table class="table table-bordered attendance-details-table">
            <tr><th>Name</th><td><?= html_escape($attendanceDisplayName); ?></td></tr>
            <tr><th>Phone</th><td><?= html_escape($phoneText); ?></td></tr>
            <tr><th>Check In</th><td><?= html_escape($attendance->check_in); ?></td></tr>
            <tr><th>Check Out</th><td><?= html_escape($checkOutText); ?></td></tr>
            <tr><th>Latitude</th><td><?= html_escape($latitudeText); ?></td></tr>
            <tr><th>Longitude</th><td><?= html_escape($longitudeText); ?></td></tr>
            <tr><th>Landmark</th><td><?= $landmarkText === 'None' ? 'None' : str_replace(' | ', '<br>', html_escape($landmarkText)); ?></td></tr>
            <tr><th>Derived Location</th><td><?= html_escape($derivedLocationText); ?></td></tr>
            <tr><th>Created At</th><td><?= html_escape($attendance->created_at); ?></td></tr>
        </table>
<?php if ($isModal) { ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
<?php } else { ?>
        <a href="<?= site_url('attendance'); ?>" class="btn btn-default">Back</a>
    </div>
</div>
<?php } ?>

