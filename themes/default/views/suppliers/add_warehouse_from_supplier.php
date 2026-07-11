<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    #addWarehouseModal {
        display: none;
    }
</style>
<div id="addWarehouseModal" class="modal fade awfs" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close_addWarehouseModal" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_vendor_location'); ?></h4>
            </div>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
            echo form_open_multipart("suppliers/add_warehouse", $attrib); ?>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="code"><?php echo $this->lang->line("Code*"); ?></label>
                            <?php echo form_input('code', '', 'class="form-control" id="code_new" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="name"><?php echo $this->lang->line("Name*"); ?></label>
                            <input type="text" name="name" id="name_new" class="form-control" required pattern="^[A-Za-z\s]+$"
                                title="Enter text only">
                            <small id="name-error-new" class="text-danger" style="display: none;">Enter letters only</small>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="country"><?php echo $this->lang->line("Country"); ?></label>
                            <?php echo form_input('country', 'India', 'class="form-control" id="country_new" '); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="city"><?php echo $this->lang->line("City*"); ?></label>
                            <input type="text" name="city" id="city_new" class="form-control" required pattern="^[A-Za-z\s]+$"
                                title="Enter text only">
                            <small id="city-error-new" class="text-danger" style="display: none;">Enter letters only</small>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="state"><?php echo $this->lang->line("State*"); ?></label>
                            <?php
                            $state_list[''] = lang('select') . ' ' . lang('state');
                            foreach ($states as $state) {
                                $state_list[$state->id . '~' . $state->code] = $state->name;
                            }
                            echo form_dropdown('state', $state_list, $warehouse->state, 'class="form-control tip select" required="required" id="state_new" style="width:100%;"');
                            ?>
                        </div>


                        <div class="form-group">
                            <label class="control-label" for="owned_by"><?php echo $this->lang->line("Owned_By"); ?></label>
                            <?php
                            $name[''] = lang('select') . ' ' . lang('Select Owned By');
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
                            <?php echo form_input('address_line1', '', 'class="form-control" id="address_line1_new" '); ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label"
                                for="contact_person_name"><?php echo $this->lang->line("Contact Person*"); ?></label>
                            <?php echo form_input('contact_person_name', '', 'class="form-control" id="contact_person_name_new" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"
                                for="primary_biller"><?php echo $this->lang->line("Primary_Biller*"); ?></label>
                            <?php
                            $biller_name[''] = lang('select') . ' ' . lang('Select Primary_Biller');
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
                            $pgs[''] = lang('select') . ' ' . lang('price_group');
                            foreach ($price_groups as $price_group) {
                                $pgs[$price_group->id] = $price_group->name;
                            }
                            echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control tip select" id="price_group_new" style="width:100%;"');
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="phone"><?php echo $this->lang->line("Phone*"); ?></label>
                            <input type="text" name="phone" id="phone_new" class="form-control" minlength="2" maxlength="10"
                                pattern="^[0-9]+$" required title="Enter numbers only">
                            <small id="phone-error-new" class="text-danger" style="display: none;">Enter numbers only</small>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="email"><?php echo $this->lang->line("Email"); ?></label>
                            <?php echo form_input('email', '', 'class="form-control" id="email_new"'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="postal_code">Pincode*</label>
                            <input type="text" name="postal_code" id="postal_code_new"
                                value="<?php echo htmlspecialchars($warehouse->postal_code); ?>" class="form-control"
                                required pattern="^[0-9]{6}$" maxlength="6" title="Enter a 6-digit pincode">
                            <small id="postal-error-new" class="text-danger" style="display: none;">Enter a valid pincode</small>
                        </div>
                        <div class="form-group">
                            <?= lang("Location_Map", "image") ?>
                            <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile"
                                data-show-upload="false" data-show-preview="false" class="form-control file">
                        </div>



                        <div class="form-group" style="display:none;">
                            <label class="control-label" for="location_type">
                                <?= $this->lang->line("Location_Type"); ?>
                            </label>
                            <?php
                            // Build dropdown array
                            $type = [];

                            foreach ($location_types as $loc) {
                                // Only add Vendor
                                if ($loc->type == "Vendor") {
                                    $type[$loc->id] = $loc->type;
                                }
                            }

                            // Get selected value properly
                            $selected = isset($location_type->location_type) ? $location_type->location_type : '';

                            echo form_dropdown(
                                'location_type',
                                $type,
                                $selected,
                                'class="form-control tip select" id="location_type" style="width:100%; "'
                            );
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
                            <?php echo form_input('address_line2', '', 'class="form-control" id="address_line2_new" '); ?>
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
                        <?php echo form_textarea('address', '', 'class="form-control" id="address_new"'); ?>
                    </div>
                </div>

                <div class="modal-footer">
                    <?php echo form_submit('add_warehouse', lang('Add_Location'), 'class="btn btn-primary"'); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script>
    $(document).ready(function() {
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

    });
    $(document).on('input', '#bill_prefix', function() {
        this.value = this.value.replace(/[^a-zA-Z]/g, '');
    });
    $(document).ready(function() {
        $('#name_new').on('input', function() {
            var value = $(this).val();
            var isValid = /^[A-Za-z\s]*$/.test(value);

            if (!isValid) {
                $(this).css('border-color', 'red');
                $('#name-error-new').show();
            } else {
                $(this).css('border-color', '');
                $('#name-error-new').hide();
            }
        });
    });
    $(document).ready(function() {
        $('#city_new').on('input', function() {
            var value = $(this).val();
            var isValid = /^[A-Za-z\s]*$/.test(value);

            if (!isValid) {
                $(this).css('border-color', 'red');
                $('#city-error-new').show();
            } else {
                $(this).css('border-color', '');
                $('#city-error-new').hide();
            }
        });
    });
    $(document).ready(function() {
        $('#phone_new').on('input', function() {
            var value = $(this).val();
            var isValid = /^[0-9]*$/.test(value);

            if (!isValid) {
                $(this).css('border-color', 'red');
                $('#phone-error-new').show();
            } else {
                $(this).css('border-color', '');
                $('#phone-error-new').hide();
            }
        });
    });
    $(document).ready(function() {
        $('#postal_code_new').on('input', function() {
            var value = $(this).val();
            var isValid = /^[0-9]{0,6}$/.test(value); // Allows 0-6 digits only

            if (!isValid || value.length > 6) {
                $(this).css('border-color', 'red');
                $('#postal-error-new').show();
            } else {
                $(this).css('border-color', '');
                $('#postal-error-new').hide();
            }
        });
    });

    $(document).ready(function() {
        $('#addWarehouseModal form').on('submit', function(event) {
            event.preventDefault();

            var form = this;
            var fd = new FormData(form);

            var otherBillerVals = $('#other_biller').val() || [];
            fd.delete('other_biller[]');
            otherBillerVals.forEach(function(v) {
                fd.append('other_biller[]', v);
            });

            var stateVal = $('#state_new').val() || '';
            fd.set('state', stateVal);

            if ($('#bill_prefix').length) {
                fd.set('bill_prefix', $('#bill_prefix').val() || '');
            }

            $.ajax({
                url: "<?= site_url('suppliers/add_warehouse'); ?>",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                dataType: "html",
                success: function(response) {
                    try {
                        // console.log('response.statusresponse.status', response.status);
                        if (JSON.parse(response).status == "success") {
                            alert('Warehouse added successfully');
                            $('#is_warehouse_added').val('true');
                            $('#is_warehouse_added').attr('data-is-warehouse-added', 'true');

                            $.ajax({
                                url: "<?= site_url('suppliers/getWarehousesByLocationType'); ?>",
                                type: "GET",
                                dataType: "json",
                                success: function(response) {
                                    try {
                                        if (response.status === "success" && Array.isArray(response.warehouses)) {
                                            var $sel = $('#location_id');
                                            var prevVal = $sel.val();
                                            $sel.empty();
                                            $sel.append($('<option>', {
                                                value: '',
                                                text: ''
                                            }));
                                            response.warehouses.forEach(function(wh) {
                                                var txt = (wh.name || '') + ' (' + (wh.code || '') + ')';
                                                $sel.append($('<option>', {
                                                    value: wh.id,
                                                    text: txt
                                                }));
                                            });

                                            if (prevVal && $sel.find('option[value="' + prevVal + '"]').length) {
                                                $sel.val(prevVal);
                                            } else {
                                                if (response.warehouses.length > 0) {
                                                    var last = response.warehouses[response.warehouses.length - 1];
                                                    if (last && last.id) {
                                                        $sel.val(String(last.id));
                                                    }
                                                }
                                            }
                                            if ($.fn.select2 && $sel.hasClass('select')) {
                                                $sel.trigger('change.select2');
                                            } else {
                                                $sel.trigger('change');
                                            }
                                        }
                                    } catch (ex) {
                                        console.error('Failed to render Add Warehouse modal:', ex);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Failed to load Add Warehouse modal:', status, error);
                                    console.debug('Response:', xhr && xhr.responseText);
                                }
                            });

                            $('.close_addWarehouseModal').click();

                        } else {
                            alert("Something went wrong. Please try again later");
                            $('#is_warehouse_added').val('false');
                            $('#is_warehouse_added').attr('data-is-warehouse-added', 'false');
                        }
                    } catch (ex) {
                        console.error('Failed handling Add Warehouse response:', ex);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to submit Add Warehouse:', status, error);
                    console.debug('Response:', xhr && xhr.responseText);
                    alert("Something went wrong. Please try again later");
                    $('#is_warehouse_added').val('false');
                    $('#is_warehouse_added').attr('data-is-warehouse-added', 'false');
                }
            });
        });
    });
</script>