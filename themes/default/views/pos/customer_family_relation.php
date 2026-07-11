<style>
div#s2id_relation {
    width: 115px;
}
.sortable.sortable {
  cursor: pointer;
}

.sortable.sortable.asc::after {
  content: " \2191"; /* Up arrow */
}

.sortable.sortable.desc::after {
  content: " \2193"; /* Down arrow */
}
div#relation-error{
    position: absolute;
    margin-top: 54px;
    margin-left: 0px;
}
div#name-error {
    position: absolute;
    margin-top: 54px;
    margin-left: 23rem;
}
div#event_type-error{
    position: absolute;
    margin-top: 55px;
    margin-left: 53rem;
}
div.date-error {
    position: absolute;
    margin-top: 57px;
    margin-left: 80rem;
}
.Occasion{
    width:100px;
}
</style>
<script>

</script>
<div class="form-container customer_family_relation">
    <label for="relation">Relation:</label>
    <select id="relation" class="relation" name="relation">
        <option value="">Select</option>
        <?php foreach ($relations as $relation): ?>
        <option value="<?php echo $relation->id; ?>"><?php echo $relation->name; ?></option>
        <?php endforeach; ?>
    </select>
    <div class="error-message" id="relation-error" style="color: red; display: none;">This field is required.</div>
    
    <input type="hidden" id="customer_id" class="customer_id" name="customer_name_family">
    <input type="hidden" id="customer_details_id" class="customer_details_id" name="customerDetails">

    <label for="name">Name:</label>
    <input type="text" id="personName" class="personName" name="name" pattern="^[A-Za-z]+(?:\s[A-Za-z]+)*$" required>
    <div class="error-message" id="name-error" style="color: red; display: none;">This field is required.</div>

    <label for="event_type">Occasion:</label>
    <select class="Occasion event_type" name="event_type" id="event_type">
        <option value="">Select</option>
        <?php foreach ($events as $event): ?>
        <option value="<?php echo $event->id; ?>"><?php echo $event->name; ?></option>
        <?php endforeach; ?>
    </select>
    <div class="error-message" id="event_type-error" style="color: red; display: none;">This field is required.</div>

    <label for="date">Date:</label>
    <input type="text" id="family_relation_date" class="event-date" style = "width: 165px !important;" name="date" autocomplete="off" required>
    <div class="error-message date-error" id="date-error" style="color: red; display: none;">This field is required.</div>

    <button type="button" class="add-button submitBtn" style="display: none;" id="submitBtn">Add</button>
    <button type="button" class="add-button editButton" style="display: none;" id="editButton">Save</button>
</div>
<div class="table-container" style="margin-top: 15px;">
    <h4>Relations & Occasions</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Relation</th>
                <th>Name</th>
                <th>Occasion</th>
                <th>Date</th>
                <th>Actions</th> <!-- Actions column -->
            </tr>
        </thead>
        <tbody id="gridContent" class="gridContent">
            <!-- Dynamically added grid content will appear here -->
        </tbody>
    </table>
</div>
</div>
<script>
document.querySelectorAll(".tab-nav button").forEach(button => {
    button.addEventListener("click", function() {
        document.querySelectorAll(".tab-nav button").forEach(btn => btn.classList.remove("active"));
        this.classList.add("active");
    });
});
document.querySelectorAll(".personName").forEach(function (input) {
  input.addEventListener("input", function () {
    this.value = this.value.replace(/[^A-Za-z ]/g, '');
  });
});

</script>
<script>
$(document).ready(function () {

    // Setup today's date in ISO for comparisons
    const todayISO = new Date().toISOString().split('T')[0];
    const todayDate = new Date(todayISO);

    // Helper: parse various common formats into ISO yyyy-mm-dd
    function toISODateString(input) {
        if (!input) return '';
        // Already ISO yyyy-mm-dd
        if (/^\d{4}-\d{2}-\d{2}$/.test(input)) return input;
        // dd/mm/yyyy
        let m = input.match(/^([0-3]?\d)\/(0?\d|1[0-2])\/(\d{4})$/);
        if (m) {
            const [ , d, mo, y ] = m;
            const dd = String(d).padStart(2, '0');
            const mm = String(mo).padStart(2, '0');
            return `${y}-${mm}-${dd}`;
        }
        // mm/dd/yyyy
        m = input.match(/^(0?\d|1[0-2])\/([0-3]?\d)\/(\d{4})$/);
        if (m) {
            const [ , mo, d, y ] = m;
            const dd = String(d).padStart(2, '0');
            const mm = String(mo).padStart(2, '0');
            return `${y}-${mm}-${dd}`;
        }
        // dd-mm-yyyy
        m = input.match(/^([0-3]?\d)-(0?\d|1[0-2])-(\d{4})$/);
        if (m) {
            const [ , d, mo, y ] = m;
            const dd = String(d).padStart(2, '0');
            const mm = String(mo).padStart(2, '0');
            return `${y}-${mm}-${dd}`;
        }
        // Fallback: Date parse
        const dt = new Date(input);
        if (!isNaN(dt)) {
            const iso = new Date(dt.getTime() - dt.getTimezoneOffset()*60000).toISOString().split('T')[0];
            return iso;
        }
        return '';
    }

    function validateAndNormalizeDateByClass(inputClass, errorClass) {
        $(document).on('change blur', '.' + inputClass, function () {
            const $inp = $(this);
            const error = $inp.closest('.form-container').find('.' + errorClass);
            const raw = $inp.val();
            if (!raw) { error.hide(); return; }

            const iso = toISODateString(raw);
            if (!iso) {
                error.text("Invalid date format.").show();
                $inp.val('');
                return;
            }

            // Check if occasion is Anniversary
            const $form = $inp.closest('.customer_family_relation');
            const occasionText = $form.find('.event_type option:selected').text().toLowerCase();
            const isAnniversary = occasionText.includes('anniversary');

            // Compare using Date objects
            const picked = new Date(iso);
            if (!isAnniversary && picked > todayDate) {
                error.text("Future date is not allowed for this occasion.").show();
                $inp.val('');
                return;
            }

            // Normalize the value to ISO so backend and JS agree
            $inp.val(iso);
            error.hide();
        });
    }

    validateAndNormalizeDateByClass('event-date', 'date-error');

    function applyOccasionDateLimit($input) {
        const $form = $input.closest('.customer_family_relation');
        const occasionText = $form.find('.event_type option:selected').text().toLowerCase();
        const isAnniversary = occasionText.includes('anniversary');
        if (isAnniversary) {
            // Anniversary allows future dates: remove any max date cap.
            $input.datetimepicker('setEndDate', false);
        } else {
            // BirthDate/others cannot go beyond today.
            $input.datetimepicker('setEndDate', new Date());
        }
    }

    function initEventDatePicker($input) {
        const $form = $input.closest('.customer_family_relation');
        const occasionText = $form.find('.event_type option:selected').text().toLowerCase();
        const isAnniversary = occasionText.includes('anniversary');
        $input.datetimepicker('remove');
        const pickerConfig = {
            format: site.dateFormats.js_sdate,
            fontAwesome: true,
            language: 'sma',
            todayBtn: 1,
            autoclose: 1,
            minView: 2
        };
        if (!isAnniversary) {
            pickerConfig.endDate = new Date();
        }
        $input.datetimepicker(pickerConfig);
        applyOccasionDateLimit($input);
    }

    // Dynamic Picker Initialization
    $(document).on('focus', '.event-date', function() {
        const $input = $(this);
        initEventDatePicker($input);
        $input.datetimepicker('show');
    });

    // Clear date and re-init picker if occasion changes
    $(document).on('change', '.customer_family_relation .event_type', function() {
        const $form = $(this).closest('.customer_family_relation');
        const $dateInput = $form.find('.event-date');
        $dateInput.val('');
        $dateInput.datetimepicker('remove');
        $form.find('#date-error').hide();
    });

});

</script>

