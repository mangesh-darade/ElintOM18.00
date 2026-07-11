<!DOCTYPE html>
<html>

<head>
    <title>Create Order Schedule</title>
    <!-- Bootstrap CSS CDN -->

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
    <div class="container mt-5">

        <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
        <?php echo form_open('Omnichannel/add_schedule'); ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category*</label>
                <select class="form-control" name="category_id" id="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
    <label for="product_id" class="form-label">Products*</label>
    <select multiple class="form-control" name="product_id[]" id="product_id">
        <option value="All*">All*</option>
    </select>
</div>

        </div>

    </div>
    <div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="order_lead_time" class="form-label">Order Lead Time*</label>
            <input type="number" class="form-control" name="order_lead_time" id="order_lead_time">
        </div>
        <div class="col-md-6">
            <label for="start_time" class="form-label">Start Time*</label>
            <input type="text" class="form-control" name="start_time" id="start_time">
        </div>
    </div>
    </div>
    <div class="container mt-5">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="end_time" class="form-label">End Time*</label>
                <input type="text" class="form-control" name="end_time" id="end_time">
            </div>
        <div class="col-md-6">
            <label for="weekday" class="form-label">Weekday*</label>
            <select class="form-control" name="weekday[]" id="weekday" multiple>
                <option value="All*">All*</option>
                <option value="Mon">Mon</option>
                <option value="Tue">Tue</option>
                <option value="Wed">Wed</option>
                <option value="Thu">Thu</option>
                <option value="Fri">Fri</option>
                <option value="Sat">Sat</option>
                <option value="Sun">Sun</option>
            </select>
        </div>


    </div>
    </div>
    <div class="container mt-5">
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
    </div>
    </form>

    <!-- <?php echo form_close(); ?> -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Initialize Select2 on products dropdown
$(document).ready(function() {
    $('#product_id').select2({
        placeholder: "Select Products",
        allowClear: true,
        width: '100%' // ensure it fits the container
    });
    $('#weekday').select2({
        placeholder: "Select Day",
        allowClear: true,
        width: '100%' // ensure it fits the container
    });
});

// AJAX logic for dynamic product loading
$('#category_id').on('change', function() {
    var categoryId = $(this).val();
    $('#product_id').empty().trigger('change'); // clear existing options

    if (categoryId) {
        $.ajax({
            url: '<?= base_url("Omnichannel/get_products_by_category") ?>',
            type: 'GET',
            data: {
                category_id: categoryId
            },
            success: function(response) {
                let products = JSON.parse(response);

                // Add "All*" as the first option
                $('#product_id').append(new Option("All*", "All*", false, false));

                // Append other product options
                products.forEach(function(product) {
                    var newOption = new Option(product.name, product.id, false, false);
                    $('#product_id').append(newOption);
                });

                $('#product_id').trigger('change'); // trigger change event if needed
            },
            error: function() {
                alert('Could not load products.');
            }
        });
    }
});
</script>


</html>