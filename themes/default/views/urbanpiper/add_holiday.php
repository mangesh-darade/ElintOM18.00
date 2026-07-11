<!DOCTYPE html>
<html>

<head>
    <title>Create Order Schedule</title>

</head>

<body>
    <div class="container mt-5">

        <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
        <?php echo form_open('Omnichannel/add_holiday'); ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="day" class="form-label">Day</label>
                <input list="days" class="form-control" name="day" id="day">
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
                <input type="text" class="form-control" name="date" id="date">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="text" class="form-control" name="start_time" id="start_time">
            </div>
            <div class="col-md-6">
                <label for="end_time" class="form-label">End Time</label>
                <input type="text" class="form-control" name="end_time" id="end_time">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="special_hours" class="form-label">Special Hours?</label>
                <select name="special_hours" class="form-control" id="special_hours">
                    <option value="">Select</option>
                    <option value="Y">Yes</option>
                    <option value="N">No</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="site_banner_text" class="form-label">Site Banner Text</label>
                <textarea name="site_banner_text" class="form-control" id="site_banner_text" rows="3"></textarea>
            </div>
        </div>



        <button type="submit" class="btn btn-primary">Save</button>
        <?php echo form_close(); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    flatpickr("#date", {
        dateFormat: "Y-m-d"
    });

    flatpickr("#start_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
    });

    flatpickr("#end_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
    });
    </script>

</body>

</html>