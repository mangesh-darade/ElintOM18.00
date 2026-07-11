<!DOCTYPE html>
<html>

<head>
    <title>Edit Schedule</title>
    <!-- Bootstrap CSS CDN -->

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('Edit_Schedule'); ?></h2>
    </div>

<body>
    <div class="container mt-5">

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
        <?php echo form_open('Omnichannel/edit_schedule/' . $schedule->id); ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-control" name="category_id" id="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id']; ?>" <?= $schedule->category_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="product_id" class="form-label">Products</label>
                <select multiple class="form-control" name="product_id[]" id="product_id">
                <?php foreach ($products as $product): ?>
    <option value="<?= $product->product_id; ?>" selected><?= $product->product_name; ?></option>
<?php endforeach; ?>

                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="order_lead_time" class="form-label">Order Lead Time</label>
                <input type="number" class="form-control" name="order_lead_time" id="order_lead_time"
                       value="<?= $schedule->order_lead_time ?>">
            </div>
            <div class="col-md-6">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="text" class="form-control" name="start_time" id="start_time"
                       value="<?= $schedule->start_time ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="end_time" class="form-label">End Time</label>
                <input type="text" class="form-control" name="end_time" id="end_time"
                       value="<?= $schedule->end_time ?>">
            </div>
            <div class="col-md-6">
                <label for="weekday" class="form-label">Weekday</label>
                <select class="form-control" name="weekdays[]" id="weekday" multiple>
                    <?php
                    $days = ['All', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $selected_days = set_value('weekdays[]') ? (array)set_value('weekdays[]') : $weekdays;
foreach ($days as $day):
    $selected = in_array($day, $selected_days) ? 'selected' : '';
    echo "<option value='$day' $selected>$day</option>";
endforeach;
                    // foreach ($days as $day):
                    //     $selected = in_array($day, $weekdays) ? 'selected' : '';
                    //     echo "<option value='$day' $selected>$day</option>";
                    // endforeach;
                    ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <!-- Scripts -->
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#product_id').select2({
                placeholder: "Select Products",
                allowClear: true,
                width: '100%'
            });
            $('#weekday').select2({
                placeholder: "Select Weekdays",
                allowClear: true,
                width: '100%'
            });

            
        });
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
                    $('#product_id').append(new Option("All*", "all", false, false));

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
</body>

</html>
