<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $isModal = !empty($is_modal); ?>
<style>
    .attendance-capture-wrap {
        margin-top: 10px;
    }
    .attendance-panel {
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        background: #fff;
        padding: 14px;
        width: 100%;
        max-width: 760px;
    }
    .attendance-form-row {
        margin-bottom: 12px;
    }
    .attendance-form-row .form-group {
        margin-bottom: 0;
    }
    .attendance-form-row label {
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
    }
    .attendance-actions {
        margin-top: 12px;
        display: flex;
        align-items: stretch;
        gap: 10px;
        flex-wrap: nowrap;
    }
    .attendance-actions .btn {
        margin-right: 0;
        margin-bottom: 0;
        flex: 1 1 50%;
        min-height: 40px;
        font-weight: 600;
    }
    .attendance-form-note {
        font-size: 12px;
        color: #666;
        margin-bottom: 12px;
    }
    @media (max-width: 767px) {
        .attendance-panel {
            padding: 10px;
        }
        .attendance-actions {
            gap: 8px;
            flex-wrap: wrap;
        }
        .attendance-actions .btn {
            width: 100%;
            flex: 1 1 100%;
            min-height: 42px;
        }
    }
</style>
<?php if ($isModal) { ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Attendance</h4>
        </div>
        <?php echo form_open('attendance/update/' . (int) $attendance->id, array('class' => 'form-horizontal')); ?>
        <input type="hidden" name="modal" value="1">
        <div class="modal-body attendance-capture-wrap">
            <div class="attendance-panel">
<?php } else { ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-pencil"></i>Edit Attendance</h2>
    </div>
    <div class="box-content attendance-capture-wrap">
        <?php echo form_open('attendance/update/' . (int) $attendance->id, array('class' => 'form-horizontal')); ?>
        <input type="hidden" name="modal" value="0">
        <div class="attendance-panel">
<?php } ?>
        <div class="attendance-form-note">Update attendance details below.</div>
        <div class="row attendance-form-row">
            <div class="col-xs-12 col-sm-6 form-group">
                <label for="checkInField">Check In</label>
                <input id="checkInField" type="datetime-local" name="check_in" class="form-control" value="<?= !empty($attendance->check_in) ? html_escape(date('Y-m-d\TH:i', strtotime($attendance->check_in))) : ''; ?>" required>
            </div>
            <div class="col-xs-12 col-sm-6 form-group">
                <label for="checkOutField">Check Out</label>
                <input id="checkOutField" type="datetime-local" name="check_out" class="form-control" value="<?= !empty($attendance->check_out) ? html_escape(date('Y-m-d\TH:i', strtotime($attendance->check_out))) : ''; ?>">
            </div>
        </div>
        <div class="row attendance-form-row">
            <div class="col-xs-12 col-sm-6 form-group">
                <label for="latitudeField">Latitude</label>
                <input id="latitudeField" type="number" step="any" name="latitude" class="form-control" value="<?= html_escape($attendance->latitude); ?>">
            </div>
            <div class="col-xs-12 col-sm-6 form-group">
                <label for="longitudeField">Longitude</label>
                <input id="longitudeField" type="number" step="any" name="longitude" class="form-control" value="<?= html_escape($attendance->longitude); ?>">
            </div>
        </div>
        <div class="row attendance-form-row">
            <div class="col-xs-12 form-group">
                <label for="landmarkField">Landmark</label>
                <textarea id="landmarkField" name="landmark" class="form-control" rows="2"><?= html_escape($attendance->landmark); ?></textarea>
            </div>
        </div>
        <div class="row attendance-form-row">
            <div class="col-xs-12 form-group">
                <label for="derivedLocationField">Derived Location</label>
                <textarea id="derivedLocationField" name="derived_location" class="form-control" rows="2"><?= html_escape($attendance->derived_location); ?></textarea>
            </div>
        </div>
<?php if ($isModal) { ?>
            </div>
        </div>
        <div class="modal-footer">
            <div class="attendance-actions">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Attendance</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php } else { ?>
        <div class="attendance-actions">
            <a href="<?= site_url('attendance'); ?>" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
        </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php } ?>

