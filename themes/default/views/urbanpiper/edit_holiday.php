<!DOCTYPE html>
<html>
<div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('Edit_Holiday'); ?></h2>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</div>
<body>
    <div class="container mt-5">

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
        <?php echo form_open('Omnichannel/edit_holiday/' . $holiday->id,['id' => 'editHolidayForm']); ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="day" class="form-label">Day</label>
                <input list="days" class="form-control" name="day" id="day" value="<?= set_value('day', $holiday->day) ?>">
                <datalist id="days">
                    <option value="Mon">
                    <option value="Tue">
                    <option value="Wed">
                    <option value="Thu">
                    <option value="Fri">
                    <option value="Sat">
                    <option value="Sun">
                </datalist>
            </div>

            <div class="col-md-6">
                <label for="date" class="form-label">Date</label>
                <input type="text" class="form-control" name="date" id="date" value="<?= set_value('date', $holiday->date) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="text" class="form-control" name="start_time" id="start_time" value="<?= set_value('start_time', $holiday->start_time) ?>">
            </div>
            <div class="col-md-6">
                <label for="end_time" class="form-label">End Time</label>
                <input type="text" class="form-control" name="end_time" id="end_time" value="<?= set_value('end_time', $holiday->end_time) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="special_hours" class="form-label">Special Hours?</label>
                <select name="special_hours" class="form-control" id="special_hours">
                    <option value="">Select</option>
                    <option value="Y" <?= set_select('special_hours', 'Y', $holiday->special_hours == 'Y') ?>>Yes</option>
                    <option value="N" <?= set_select('special_hours', 'N', $holiday->special_hours == 'N') ?>>No</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="site_banner_text" class="form-label">Site Banner Text</label>
                <textarea name="site_banner_text" class="form-control" id="site_banner_text" rows="3"><?= set_value('site_banner_text', $holiday->site_banner_text) ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Schedule</button>
        <?php echo form_close(); ?>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->

    <script>
$(document).ready(function () {
    flatpickr("#date", {
        dateFormat: "Y-m-d",
        allowInput: true
    });

    flatpickr("#start_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true
    });

    flatpickr("#end_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true
    });

    $('#editHolidayForm').on('submit', function(e) {
        var date = $('#date').val();
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();

        if (!date) {
            alert('Please select a date.');
            e.preventDefault();
            return false;
        }

        if (!startTime) {
            alert('Please select a start time.');
            e.preventDefault();
            return false;
        }

        if (!endTime) {
            alert('Please select an end time.');
            e.preventDefault();
            return false;
        }

        var startDateTime = new Date(date + ' ' + startTime);
        var endDateTime = new Date(date + ' ' + endTime);

        if (startDateTime >= endDateTime) {
            alert('End time must be later than start time.');
            e.preventDefault();
            return false;
        }
    });
});
</script>


</body>
</html>
