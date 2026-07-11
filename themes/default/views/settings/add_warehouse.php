<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$warehouse_country_phone_meta = [];
if (isset($country) && !empty($country)) {
    foreach ($country as $c) {
        $digits = 10;
        if (!empty($c->phone_digits) && is_numeric($c->phone_digits)) {
            $digits = (int) $c->phone_digits;
        }
        if ($digits <= 0) {
            $digits = 10;
        }
        $warehouse_country_phone_meta[] = [
            'name' => $c->name,
            'phone_digits' => $digits,
        ];
    }
}
?>
<style>
.ui-autocomplete {
    max-height: 200px;   /* height of dropdown */
    overflow-y: auto;    /* vertical scrollbar */
    overflow-x: hidden;
    z-index: 9999 !important;  /* above modal */
}
/* Optional: better item spacing */
.ui-menu-item {
    padding: 6px 10px;
    cursor: pointer;
}
.ui-autocomplete {
    scrollbar-width: thin;
}
#warehouse_map {
    width: 100%;
    height: 260px;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
}

</style>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_location'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-warehouse-form');
        echo form_open_multipart("system_settings/add_warehouse", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label" for="code"><?php echo $this->lang->line("Code*"); ?></label>
                        <?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="name"><?php echo $this->lang->line("Name*"); ?></label>
                        <input type="text" name="name" id="name" class="form-control" required pattern="^[A-Za-z\s]+$"
                            title="Enter text only">
                        <small id="name-error" class="text-danger" style="display: none;">Enter letters only</small>
                    </div>

                    <div class="form-group">
                        <?= lang("country", "country"); ?>
                            <?php
                                $ct[""] = "";
                                foreach ($country as $country_value) {
                                    $ct[$country_value->name] = $country_value->name;
                                }
                                $ct['other'] = 'Other';
                                $selected_country = isset($_POST['country']) ? $_POST['country'] : 'India';
                                echo form_dropdown('country', $ct, $selected_country, 'id="country"  data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                            ?>                                    
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="city"><?php echo $this->lang->line("City*"); ?></label>
                        <input type="text" name="city" id="city" class="form-control" required pattern="^[A-Za-z\s]+$"
                            title="Enter text only">
                        <small id="city-error" class="text-danger" style="display: none;">Enter letters only</small>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="state"><?php echo $this->lang->line("State*"); ?></label>
                        <?php
                        $state_list[''] = lang('select') . ' ' . lang('state');
                        foreach ($states as $state) {
                            $state_list[$state->name .'~'.$state->code] = $state->name . ' (' . $state->code . ')';
                        }
                        $selected_state = isset($_POST['state']) ? $_POST['state'] : '';
                        echo form_dropdown('state', $state_list, $selected_state, 'class="form-control tip select" required="required" id="state" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="state_code">State Code</label>
                        <input type="text" name="state_code" placeholder="State Code" value="" id="state_code" class="form-control" readonly="readonly" />
                    </div>


                    <div class="form-group">
                        <label class="control-label" for="owned_by"><?php echo $this->lang->line("Owned_By"); ?></label>
                        <?php
                            $name[''] = lang('select').' '.lang('Select Owned By');
                            foreach ($owned_by as $owned_by) {
                                $name[$owned_by->id] = $owned_by->name;
                            }
                             echo form_dropdown('owned_by', $name, $owned_by->owned_by, 'class="form-control tip select" id="owned_by" style="width:100%;"');
                        ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="batch_production">Batch Production*</label>
                        <select name="batch_production" class="form-control" id="batch_production" required>
                            <option value="">Select an option</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="address_line1"><?php echo $this->lang->line("Address_Line1"); ?></label>
                        <?php echo form_input('address_line1', '', 'class="form-control" id="address_line1" '); ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                            for="contact_person_name"><?php echo $this->lang->line("Contact Person*"); ?></label>
                        <?php echo form_input('contact_person_name', '', 'class="form-control" id="contact_person_name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="primary_biller"><?php echo $this->lang->line("Primary_Biller*"); ?></label>
                        <?php
                             $biller_name[''] = lang('select').' '.lang('Select Primary_Biller');
                            foreach ($billers_data as $biller_data) {
                                $biller_name[$biller_data['id']] = $biller_data['name'];
                                // print_r($biller_name);
                                // exit;
                            }
                             echo form_dropdown('primary_biller', $biller_name, $biller_data['name'], 'class="form-control tip select" id="primary_biller" required="required" style="width:100%;"');
                        ?>
                    </div>

                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label"
                            for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                        <?php
                            $pgs[''] = lang('select').' '.lang('price_group');
                            foreach ($price_groups as $price_group) {
                                $pgs[$price_group->id] = $price_group->name;
                            }
                             echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control tip select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="phone"><?php echo $this->lang->line("Phone*"); ?></label>
                        <input type="text" name="phone" id="phone" class="form-control" minlength="2" maxlength="10"
                            pattern="^[0-9]+$" required title="Enter numbers only">
                        <small id="phone-error" class="text-danger" style="display: none;"></small>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="email"><?php echo $this->lang->line("Email"); ?></label>
                        <input type="text" name="email" class="form-control" id="email" title="<?= htmlspecialchars(lang('email_address'), ENT_QUOTES, 'UTF-8'); ?>" />
                        <small id="email-error" class="text-danger" style="display: none;">Please enter a valid email address</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="postal_code">Pincode*</label>
                        <input type="text" name="postal_code" id="postal_code"
                            value="<?php echo htmlspecialchars($warehouse->postal_code); ?>" class="form-control"
                            required pattern="^[0-9]{6}$" maxlength="6" title="Enter a 6-digit pincode">
                        <small id="postal-error" class="text-danger" style="display: none;">Enter a valid pincode</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="location_radius_m">Radius (meters)</label>
                        <input type="number" name="location_radius_m" id="location_radius_m" class="form-control" min="10" step="1" value="100" title="Geofence for attendance check-in/out">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="latitude">Latitude</label>
                        <?php echo form_input('latitude', '', 'class="form-control" id="latitude" placeholder="e.g. 22.7196"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="longitude">Longitude</label>
                        <div style="display:flex; gap:8px;">
                            <?php echo form_input('longitude', '', 'class="form-control" id="longitude" placeholder="e.g. 75.8577"'); ?>
                            <button type="button" class="btn btn-default btn-sm" id="fetch_geo_location">Auto</button>
                        </div>
                        <p id="warehouse_geo_live_display" class="small" style="margin-top:8px; margin-bottom:0; display:none;" aria-live="polite">
                            <span class="text-success"><i class="fa fa-map-marker"></i> Live GPS:</span>
                            <strong id="warehouse_geo_live_latlng"></strong>
                            <span id="warehouse_geo_live_meta" class="text-muted"></span>
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Pick Location On Map</label>
                        <div style="display:flex; gap:8px; margin-bottom:8px;">
                            <input type="text" id="location_search" class="form-control" placeholder="Search landmark, address, area, or city">
                            <button type="button" class="btn btn-default btn-sm" id="search_location_btn">Search</button>
                            <button type="button" class="btn btn-info btn-sm" id="current_location_btn">Current</button>
                        </div>
                        <div id="warehouse_map"></div>
                        <small class="text-muted">Type a place name or address and Search. <strong>Current</strong> / <strong>Auto</strong> uses GPS and fills this field with the nearest address. You can edit the text or click the map to fine-tune.</small>
                    </div>
                    <div class="form-group">
                        <?= lang("Location_Map", "image") ?>
                        <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile"
                            data-show-upload="false" data-show-preview="false" class="form-control file">
                    </div>

                    <!-- <div class="form-group">
                        <label class="control-label" for="location_type">Location Type</label>
                        <select name="location_type" class="form-control" id="location_type" required>
                            <option value="">Select Location Type</option>
                            <?php foreach ($location_types as $location_type): ?>
                            <option value="<?php echo $location_type->id; ?>"><?php echo $location_type->type; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div> -->

                    <div class="form-group">
                        <label class="control-label"
                            for="location_type"><?php echo $this->lang->line("Location_Type"); ?></label>
                        <?php
                            $type[''] = lang('select').' '.lang('Select Location Type');
                            foreach ($location_types as $location_type) {
                                $type[$location_type->id] = $location_type->type;
                            }
                             echo form_dropdown('location_type', $type, $location_type->location_type, 'class="form-control tip select" id="location_type" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="workstations"><?php echo $this->lang->line("Workstations"); ?></label>
                        <?php
                            $warehouse_list = array();
                            if (isset($warehouses_list) && !empty($warehouses_list)) {
                                foreach ($warehouses_list as $warehouse_item) {
                                    $warehouse_list[$warehouse_item->id] = $warehouse_item->name;
                                }
                            }
                            echo form_dropdown('workstations[]', $warehouse_list, '', 'class="form-control tip select" id="workstations" multiple="multiple" style="width:100%;"');
                        ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="retail_production">Retail Production*</label>
                        <select name="retail_production" class="form-control" id="retail_production" required>
                            <option value="">Select an option</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="address_line2"><?php echo $this->lang->line("Address_Line2"); ?></label>
                        <?php echo form_input('address_line2', '', 'class="form-control" id="address_line2" '); ?>
                    </div>
                    <div class="form-group">
                        <div class="form-group">
                        <label class="control-label">Customer</label>
                        <input type="text" id="search_customer" class="form-control" placeholder="Search Customer By Name">
                        <input type="hidden" name="customer_id" id="customer_id">
                    </div>
                    <?php if ($Settings->use_invoice_number_prefix) { ?>
                    <div class="form-group">
                        <label class="control-label" for="bill_prefix">
                            <?php echo $this->lang->line("Bill_Prefix"); ?>
                        </label>
                        <?php echo form_input('bill_prefix', '', 'class="form-control" pattern="[A-Za-z]+" title="Letters only" id="bill_prefix" '); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="other_biller"><?php echo $this->lang->line("Other_Biller"); ?></label> (Select a
                        primary
                        biller first.)
                        <?php
                             $biller_name[''] = '';
                            foreach ($billers_data as $biller_data) {
                                $biller_name[$biller_data['id']] = $biller_data['name'];
                            }
                            echo form_dropdown('other_biller[]', $biller_name, [], 'class="form-control tip select" id="other_biller" multiple="multiple" style="width:100%;"');
                        ?>
                    </div>
                    <?php }  ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 form-group">
                    <label class="control-label" for="address"><?php echo $this->lang->line("Address"); ?></label>
                    <?php echo form_textarea('address', '', 'class="form-control" id="address"'); ?>
                </div>
            </div>

            <div class="modal-footer">
                <?php echo form_submit('add_warehouse', lang('Add_Location'), 'class="btn btn-primary"'); ?>
            </div>
        </div>
        <textarea id="warehouse-country-meta-json" readonly tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;overflow:hidden;"><?= htmlspecialchars(json_encode($warehouse_country_phone_meta), ENT_QUOTES, 'UTF-8'); ?></textarea>
        <?php echo form_close(); ?>
    </div>
    <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
    <?= $modal_js ?>
    <script>
    $(document).ready(function() {
        function parseCountryPhoneMeta() {
            var raw = String($('#warehouse-country-meta-json').val() || '').replace(/^\uFEFF/, '').trim();
            if (!raw) {
                return null;
            }
            try {
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        }

        function normalizeToken(value) {
            return String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        }

        function getPhoneDigitsByCountry(countryName, meta) {
            if (!meta || !countryName) {
                return 10;
            }
            var wanted = String(countryName).trim();
            var i;
            for (i = 0; i < meta.length; i++) {
                if (String(meta[i].name).trim() === wanted) {
                    return parseInt(meta[i].phone_digits, 10) || 10;
                }
            }
            var wantedToken = normalizeToken(wanted);
            for (i = 0; i < meta.length; i++) {
                if (normalizeToken(meta[i].name) === wantedToken) {
                    return parseInt(meta[i].phone_digits, 10) || 10;
                }
            }
            return 10;
        }

        function validateWarehousePhone(showRequiredError) {
            var meta = parseCountryPhoneMeta();
            var selectedCountry = String($('#country').val() || '').trim();
            var requiredDigits = getPhoneDigitsByCountry(selectedCountry, meta);
            var phoneValue = String($('#phone').val() || '').trim();
            var isDigits = /^[1-9][0-9]*$/.test(phoneValue);
            $('#phone').attr('maxlength', String(requiredDigits));

            if (phoneValue === '') {
                if (showRequiredError) {
                    $('#phone-error').text('Phone number is required.').show();
                } else {
                    $('#phone-error').hide();
                }
                return false;
            }
            if (!isDigits) {
                $('#phone-error').text('Phone number must contain only digits and should not start with 0.').show();
                return false;
            }
            if (phoneValue.length !== requiredDigits) {
                $('#phone-error').text('Phone number must be ' + requiredDigits + ' digits.').show();
                return false;
            }
            $('#phone-error').hide();
            return true;
        }

        function syncWarehousePhoneByCountry() {
            var meta = parseCountryPhoneMeta();
            var selectedCountry = String($('#country').val() || '').trim();
            var requiredDigits = getPhoneDigitsByCountry(selectedCountry, meta);
            var phoneValue = String($('#phone').val() || '');
            $('#phone').attr('maxlength', String(requiredDigits));
            if (phoneValue.length > requiredDigits) {
                $('#phone').val(phoneValue.substring(0, requiredDigits));
            }
            validateWarehousePhone(false);
        }

        function validateWarehouseEmail(showRequiredError) {
            var email = String($('#email').val() || '').trim();
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (email === '') {
                if (showRequiredError) {
                    $('#email-error').hide();
                } else {
                    $('#email-error').hide();
                }
                return true;
            }
            if (emailPattern.test(email)) {
                $('#email-error').hide();
                return true;
            }
            $('#email-error').show();
            return false;
        }

        const allOptions = $('#other_biller option').clone();
        $('#other_biller').prop('disabled', true);
        $('#primary_biller').on('change', function() {
            const selected = $(this).val();
            if (selected === '') {
                $('#other_biller').prop('disabled', true);
                $('#other_biller').select2({
                    placeholder: "Select other biller",
                    allowClear: true
                });
                return;
            }
            $('#other_biller').html(allOptions); // Reset all options
            $('#other_biller').prop('disabled', false);
            // Remove the selected value from Other Biller
            $('#other_biller option').each(function() {
                if ($(this).val() === selected && selected !== '') {
                    $(this).remove();
                }
            });
        });
        $('#other_biller').select2({
            placeholder: "Select other_biller"
        });

        // Initialize Workstations multi-select
        $('#workstations').select2({
            placeholder: "Select Workstations",
            allowClear: true
        });

        function setStateFromSelection(state) {
            if (state === 'other' || state === '') {
                $('#state_code').attr('readonly', false).val('');
            } else {
                var raw = String(state);
                var parts = raw.split('~');
                var stateCode = parts[1] != null ? String(parts[1]).trim() : '';
                if (stateCode === '') {
                    var txt = String($('#state option:selected').text() || '').trim();
                    var match = txt.match(/^(.*)\(([^)]+)\)\s*$/);
                    stateCode = match ? String(match[2] || '').trim() : '';
                }
                $('#state_code').val(stateCode).attr('readonly', true);
            }
        }

        function getStates(country) {
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                method: 'get',
                url: '<?= base_url('customers/getstates') ?>',
                data: { country: country },
                success: function(response) {
                    $('#state').html(response.data);
                }
            });
        }

        $('#country').on('change', function() {
            var selectedCountry = $(this).val();
            if (selectedCountry === 'other') {
                $('#state').html('<option value="other" selected="selected">Other</option>');
                setTimeout(function() {
                    $('#state').val('other').trigger('change');
                    $('#state_code').val('').attr('readonly', false);
                }, 100);
            } else {
                getStates(selectedCountry);
                setTimeout(function() {
                    $('#state').val('').trigger('change');
                    $('#state_code').val('').attr('readonly', false);
                }, 100);
            }
        });

        $('#state').on('change', function() {
            setStateFromSelection($(this).val());
        });
        $('#country').on('change', function() {
            syncWarehousePhoneByCountry();
        });
        $('#phone').on('input', function() {
            validateWarehousePhone(false);
        });
        $('#email').on('input blur', function() {
            validateWarehouseEmail(false);
        });
        $('#add-warehouse-form').on('submit', function(e) {
            if (!validateWarehousePhone(true)) {
                e.preventDefault();
                $('#phone').focus();
                return false;
            }
            if (!validateWarehouseEmail(true)) {
                e.preventDefault();
                $('#email').focus();
                return false;
            }
        });

        setTimeout(function() {
            var initialCountry = $('#country').val();
            if (initialCountry && initialCountry !== 'other') {
                getStates(initialCountry);
            }
            setStateFromSelection($('#state').val());
            syncWarehousePhoneByCountry();
        }, 100);

    });
    $(document).on('input', '#bill_prefix', function() {
        this.value = this.value.replace(/[^a-zA-Z]/g, '');
    });
    $(document).ready(function() {
        $('#name').on('input', function() {
            var value = $(this).val();
            var isValid = /^[A-Za-z\s]*$/.test(value);

            if (!isValid) {
                $(this).css('border-color', 'red');
                $('#name-error').show();
            } else {
                $(this).css('border-color', '');
                $('#name-error').hide();
            }
        });
    });
    $(document).ready(function() {
        $('#city').on('input', function() {
            var value = $(this).val();
            var isValid = /^[A-Za-z\s]*$/.test(value);

            if (!isValid) {
                $(this).css('border-color', 'red');
                $('#city-error').show();
            } else {
                $(this).css('border-color', '');
                $('#city-error').hide();
            }
        });
    });
    $(document).ready(function() {
        $('#phone').on('input', function() {
            var value = $(this).val();
            var isValid = /^[0-9]*$/.test(value);
            if (!isValid) {
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });
    });
    $(document).ready(function() {
    $('#postal_code').on('input', function() {
        var value = $(this).val();
        var isValid = /^[0-9]{0,6}$/.test(value); // Allows 0-6 digits only

        if (!isValid || value.length > 6) {
            $(this).css('border-color', 'red');
            $('#postal-error').show();
        } else {
            $(this).css('border-color', '');
            $('#postal-error').hide();
        }
    });
});

    function initializeWarehouseMapPicker() {
        function initMap() {
            if (typeof L === 'undefined' || !$('#warehouse_map').length) {
                return;
            }

            var defaultLat = parseFloat($('#latitude').val()) || 22.7196;
            var defaultLng = parseFloat($('#longitude').val()) || 75.8577;
            var map = L.map('warehouse_map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            function invalidateMapAfterLayout() {
                setTimeout(function () {
                    map.invalidateSize();
                    var plat = parseFloat($('#latitude').val());
                    var plng = parseFloat($('#longitude').val());
                    if (!isNaN(plat) && !isNaN(plng)) {
                        map.setView([plat, plng], map.getZoom());
                    }
                }, 150);
            }

            $('#myModal').off('shown.bs.modal.warehouseLeaflet').on('shown.bs.modal.warehouseLeaflet', function () {
                if (!$('#warehouse_map').length) {
                    return;
                }
                invalidateMapAfterLayout();
            });

            function setLatLng(lat, lng, moveMap, zoomOverride) {
                lat = parseFloat(lat);
                lng = parseFloat(lng);
                if (isNaN(lat) || isNaN(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) {
                    return;
                }
                $('#latitude').val(lat.toFixed(7));
                $('#longitude').val(lng.toFixed(7));
                marker.setLatLng([lat, lng]);
                if (moveMap) {
                    var z = (zoomOverride != null && !isNaN(zoomOverride)) ? zoomOverride : map.getZoom();
                    map.setView([lat, lng], z);
                    invalidateMapAfterLayout();
                }
            }

            function showWarehouseLiveGeo(lat, lng, position, sourceLabel) {
                var $d = $('#warehouse_geo_live_display');
                var $t = $('#warehouse_geo_live_latlng');
                var $m = $('#warehouse_geo_live_meta');
                if (!$d.length || !$t.length) {
                    return;
                }
                lat = parseFloat(lat);
                lng = parseFloat(lng);
                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }
                $t.text(lat.toFixed(7) + ', ' + lng.toFixed(7));
                var metaParts = [];
                if (sourceLabel) {
                    metaParts.push(sourceLabel);
                }
                if (position && position.coords) {
                    var acc = position.coords.accuracy;
                    if (typeof acc === 'number' && !isNaN(acc) && acc > 0) {
                        metaParts.push('±' + Math.round(acc) + ' m');
                    }
                }
                $m.text(metaParts.length ? ' (' + metaParts.join(' · ') + ')' : '');
                $d.show();
            }

            var reverseGeocodeDebounceTimer = null;

            function reverseGeocodeFillSearch(lat, lng) {
                var la = parseFloat(lat);
                var lo = parseFloat(lng);
                if (isNaN(la) || isNaN(lo)) {
                    return;
                }
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/reverse',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        format: 'jsonv2',
                        lat: la,
                        lon: lo,
                        zoom: 18,
                        addressdetails: 1
                    },
                    success: function (data) {
                        if (data && data.display_name && $('#location_search').length) {
                            $('#location_search').val(data.display_name);
                        }
                    }
                });
            }

            function reverseGeocodeFillSearchDebounced(lat, lng) {
                if (reverseGeocodeDebounceTimer) {
                    clearTimeout(reverseGeocodeDebounceTimer);
                }
                reverseGeocodeDebounceTimer = setTimeout(function () {
                    reverseGeocodeFillSearch(lat, lng);
                    reverseGeocodeDebounceTimer = null;
                }, 450);
            }

            function warehouseGetRefinedPosition(done, err) {
                if (!navigator.geolocation) {
                    err({ message: 'Geolocation is not supported by this browser.' });
                    return;
                }
                var best = null;
                var watchId = null;
                var finished = false;
                var fallbackTimer = null;

                function cleanupWatch() {
                    if (watchId !== null) {
                        try {
                            navigator.geolocation.clearWatch(watchId);
                        } catch (e) {
                            // ignore
                        }
                        watchId = null;
                    }
                }

                function finish(pos) {
                    if (finished) {
                        return;
                    }
                    finished = true;
                    cleanupWatch();
                    if (fallbackTimer) {
                        clearTimeout(fallbackTimer);
                    }
                    done(pos);
                }

                function fail(e) {
                    if (finished) {
                        return;
                    }
                    if (best) {
                        finish(best);
                    } else {
                        finished = true;
                        cleanupWatch();
                        if (fallbackTimer) {
                            clearTimeout(fallbackTimer);
                        }
                        err(e);
                    }
                }

                try {
                    watchId = navigator.geolocation.watchPosition(
                        function (pos) {
                            if (!best || pos.coords.accuracy < best.coords.accuracy) {
                                best = pos;
                            }
                            if (pos.coords.accuracy > 0 && pos.coords.accuracy <= 35) {
                                finish(pos);
                            }
                        },
                        fail,
                        { enableHighAccuracy: true, maximumAge: 0 }
                    );
                } catch (e) {
                    navigator.geolocation.getCurrentPosition(finish, fail, {
                        enableHighAccuracy: true,
                        timeout: 25000,
                        maximumAge: 0
                    });
                    return;
                }

                fallbackTimer = setTimeout(function () {
                    if (finished) {
                        return;
                    }
                    if (best) {
                        finish(best);
                    } else {
                        cleanupWatch();
                        navigator.geolocation.getCurrentPosition(finish, fail, {
                            enableHighAccuracy: true,
                            timeout: 25000,
                            maximumAge: 0
                        });
                    }
                }, 9000);
            }

            function applyWarehouseGpsPosition(position, sourceLabel) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                setLatLng(lat, lng, true, 15);
                showWarehouseLiveGeo(lat, lng, position, sourceLabel);
                reverseGeocodeFillSearch(lat, lng);
            }

            var mapSearchResults = [];
            var nominatimLimit = 10;

            function nominatimSearchParams(query) {
                return {
                    q: query,
                    format: 'json',
                    addressdetails: 1,
                    limit: nominatimLimit,
                    dedupe: 1
                };
            }

            function fetchLocationSuggestions(query, callback) {
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    method: 'GET',
                    dataType: 'json',
                    data: nominatimSearchParams(query),
                    success: function(result) {
                        mapSearchResults = result || [];
                        if (typeof callback === 'function') {
                            callback(mapSearchResults);
                        }
                    },
                    error: function() {
                        mapSearchResults = [];
                        if (typeof callback === 'function') {
                            callback([]);
                        }
                    }
                });
            }

            function searchAndSetLocation() {
                var query = $.trim($('#location_search').val());
                if (!query) {
                    alert('Please enter a location to search.');
                    return;
                }
                var $searchBtn = $('#search_location_btn');
                $searchBtn.prop('disabled', true).text('Searching...');
                fetchLocationSuggestions(query, function(result) {
                    if (result && result.length > 0) {
                            var lat = parseFloat(result[0].lat);
                            var lng = parseFloat(result[0].lon);
                            setLatLng(lat, lng, true, 14);
                    } else {
                        alert('Location not found. Try a more specific search.');
                    }
                    $searchBtn.prop('disabled', false).text('Search');
                });
            }

            function setCurrentLocation() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }
                var $btn = $('#current_location_btn');
                $btn.prop('disabled', true).text('Locating...');
                warehouseGetRefinedPosition(
                    function (position) {
                        applyWarehouseGpsPosition(position, 'Current');
                        $btn.prop('disabled', false).text('Current');
                    },
                    function (error) {
                        alert('Unable to fetch current location: ' + (error && error.message ? error.message : 'unknown'));
                        $btn.prop('disabled', false).text('Current');
                    }
                );
            }

            map.on('click', function(e) {
                setLatLng(e.latlng.lat, e.latlng.lng, false);
                reverseGeocodeFillSearchDebounced(e.latlng.lat, e.latlng.lng);
            });

            marker.on('dragend', function(e) {
                var pos = e.target.getLatLng();
                setLatLng(pos.lat, pos.lng, false);
                reverseGeocodeFillSearchDebounced(pos.lat, pos.lng);
            });

            $('#latitude, #longitude').off('change.warehouseMap').on('change.warehouseMap', function() {
                var lat = parseFloat($('#latitude').val());
                var lng = parseFloat($('#longitude').val());
                if (!isNaN(lat) && !isNaN(lng)) {
                    setLatLng(lat, lng, true);
                    reverseGeocodeFillSearchDebounced(lat, lng);
                }
            });

            $('#fetch_geo_location').off('click.warehouseMap').on('click.warehouseMap', function() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }
                var $btn = $(this);
                $btn.prop('disabled', true).text('Auto...');
                warehouseGetRefinedPosition(
                    function (position) {
                        applyWarehouseGpsPosition(position, 'Auto');
                        $btn.prop('disabled', false).text('Auto');
                    },
                    function (error) {
                        alert('Unable to fetch location: ' + (error && error.message ? error.message : 'unknown'));
                        $btn.prop('disabled', false).text('Auto');
                    }
                );
            });

            $('#search_location_btn').off('click.warehouseMap').on('click.warehouseMap', searchAndSetLocation);
            $('#current_location_btn').off('click.warehouseMap').on('click.warehouseMap', setCurrentLocation);
            $('#location_search').off('keypress.warehouseMap').on('keypress.warehouseMap', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    searchAndSetLocation();
                }
            });

            if ($.ui && $.ui.autocomplete) {
                var $locInput = $('#location_search');
                try {
                    if ($locInput.data('ui-autocomplete')) {
                        $locInput.autocomplete('destroy');
                    }
                } catch (ignore) {}
                var suggestTimer = null;
                $locInput.autocomplete({
                    minLength: 2,
                    delay: 400,
                    appendTo: $locInput.closest('.modal-content').length ? $locInput.closest('.modal-content') : '.modal-content',
                    source: function(request, response) {
                        if (suggestTimer) {
                            clearTimeout(suggestTimer);
                        }
                        suggestTimer = setTimeout(function() {
                            fetchLocationSuggestions(request.term, function(result) {
                                var suggestions = $.map(result, function(item) {
                                    return {
                                        label: item.display_name,
                                        value: item.display_name,
                                        lat: item.lat,
                                        lon: item.lon
                                    };
                                });
                                response(suggestions);
                            });
                        }, 300);
                    },
                    select: function(event, ui) {
                        $locInput.val(ui.item.value);
                        setLatLng(ui.item.lat, ui.item.lon, true, 14);
                        return false;
                    }
                });
            }

            setTimeout(function () {
                map.invalidateSize();
            }, 400);
        }

        if (typeof L !== 'undefined') {
            initMap();
            return;
        }

        if (!document.getElementById('leaflet-css')) {
            var leafletCss = document.createElement('link');
            leafletCss.id = 'leaflet-css';
            leafletCss.rel = 'stylesheet';
            leafletCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(leafletCss);
        }

        if (!document.getElementById('leaflet-js')) {
            var leafletJs = document.createElement('script');
            leafletJs.id = 'leaflet-js';
            leafletJs.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            leafletJs.onload = initMap;
            document.body.appendChild(leafletJs);
        } else {
            setTimeout(initMap, 150);
        }
    }

    initializeWarehouseMapPicker();
    </script>
    <script>
        $(document).ready(function () {
            $("#search_customer").autocomplete({
                minLength: 2,
                appendTo: ".modal-content",
                source: function (request, response) {
                    $.ajax({
                        url: "<?= site_url('system_settings/search_customers'); ?>",
                        dataType: "json",
                        data: { term: request.term },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                select: function (event, ui) {
                    $('#customer_id').val(ui.item.id);     
                    $('#search_customer').val(ui.item.label); 
                    return false;
                }
            });
        });
    </script>