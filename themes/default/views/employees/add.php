<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 

<style>

    .modal.fade {
        -webkit-transition: opacity .3s linear, top .3s ease-out;
        -moz-transition: opacity .3s linear, top .3s ease-out;
        -ms-transition: opacity .3s linear, top .3s ease-out;
        -o-transition: opacity .3s linear, top .3s ease-out;
        transition: opacity .3s linear, top .3s ease-out;
        top: -25%;
    }

    .modal-header .btnGrp{
        position: absolute;
        top:8px;
        right: 10px;
    } 

    .pac-container {
        z-index: 10000 !important;
    }
    .pac-card {
        margin: 10px 10px 0 0;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        background-color: #fff;
        font-family: Roboto;
    }

    #pac-container {
        padding-bottom: 12px;
        margin-right: 12px;
    }

    .pac-controls {
        display: inline-block;
        padding: 5px 11px;
    }

    .pac-controls label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
    }

    #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
    }

    #pac-input:focus {
        border-color: #4d90fe;
    }


    .shownot {
        display: none;
    }

</style>
<div class="container" style="margin-top:150px;">				
    <!--<div class="mymodal" id="modal-1" role="dailog">-->
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('Add Employee'); ?></h4>
                <p><?= lang('enter_info'); ?></p>
            </div>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add_employee_form');
            echo form_open_multipart("employees/add", $attrib);
            ?>
            <div class="modal-body">             
                <div class="row">
                    <div class="col-md-6">                     
                        <div class="form-group person">
                                <?= lang("Employee Group Type", "Employee Type") ?>
                            <select class="form-control" name="employee_Type" required="required">                             
                                <?php foreach ($employeeType as $emptype) { ?>
                                    <option value="<?= $emptype->id . '~' . $emptype->name ?>"><?= $emptype->description ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <?= lang("warehouse", "warehouse") ?>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse[]', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" multiple class="form-control select" style="width:100%;" ');
                            ?>
                        </div>
                        <div class="form-group person ">
                            <?= lang("name", "name"); ?> 
                            <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"  required="required"'); ?>
                            <span id="error_name" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>                    
                        <div class="form-group">
                            <?= lang("email_address", "email_address"); ?>
                            <input type="text" name="email" class="form-control"  id="email_address" data-bv-emailaddress
                                   data-bv-onerror="onFieldError" data-bv-onsuccess="onFieldSuccess" data-bv-onstatus="onFieldStatus" />
                        </div>
                        <div class="form-group">
                            <?= lang("phone", "phone"); ?>
                            <input type="tel" name="phone" class="form-control" data-bv-phone="true" data-bv-phone-country="US" required="required" id="phone" maxlength="10" />
                        </div>
                        <div class="form-group">
                            <?= lang("Date_OF_Birth", "DOB"); ?>
                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip date" id="date" autocomplete="off" '); ?>
                        </div>
                        <div class="form-group">
                            <?= lang("address", "address"); ?>
                            <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang("city", "city"); ?>
                            <?php echo form_input('city', '', 'class="form-control" id="city"  onkeypress="return onlyAlphabetsc(event,this);" '); ?>
                            <span id="error_city" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                        <div class="form-group">
                            <?= lang("country", "country"); ?>
                            <?php
                            $ct[""] = "";
                            foreach ($country as $country_value) {
                                $ct[$country_value->name] = $country_value->name;
                            }
                            $ct['other'] = 'Other';
                            echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : ''), 'id="country" data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                            ?>

                            <input type="text" name="add_country" placeholder="Country " id="addnewcountry" class="form-control" style="display:none; margin-top: 10px;"/>
                            <span id="error" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                        <div class="form-group">
                            <?= lang("state", "state"); ?>
                            <?php
                            $st[""] = "";
                            foreach ($states as $state) {
                                $st[$state->name] = $state->name;
                            }
                            echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : ''), 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select" style="width:100%; height:30px;"');
                            ?>
                            <input type="text" name="statecode" placeholder="State Code " id="statecode" class="form-control" style="display:none; margin-top: 10px;"/>
                            <input type="text" name="statename" placeholder="State Name " id="statename" class="form-control" style="display:none; margin-top: 10px;"/>

                        </div>

                        <div class="form-group">
                            <?= lang("Pan_card", "Pan_card"); ?>
                            <?php echo form_input('pan_card', '', 'class="form-control" id="pan_card" maxlength="10"'); ?>
                            <small class="text-danger" id="errpancard"></small>
                        </div>
                    </div>
                    <div class="col-md-6">                 
                        <div class="form-group">
                            <?= lang("postal_code", "postal_code"); ?>
                            <?php echo form_input('postal_code', '', 'class="form-control"  maxlength="6" id="postal_code" onkeypress="return IsNumeric2(event,this)" type="text"  '); ?>
                            <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                        <div class="form-group">
                            <?= lang("Photo", "Photo") ?>
                            <input id="photos" type="file" data-browse-label="<?= lang('browse'); ?>" name="photos" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>
                        <div class="form-group">
                            <?= lang("Attach_Identity_Proof", "Attach_identity_proof") ?>
                            <input id="attch_document" type="file" data-browse-label="<?= lang('browse'); ?>" name="attch_document" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>

                        <!-- CF1 is reserved for Employee Id Proof file name -->    
                        <!-- <div class="form-group">                         
                            < ?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ssf1') : lang('ssf1', 'ssf1')) ?>                                
                            < ?php echo form_input('cf1', '', 'class="form-control" id="cf1" ' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); ?> 
                        </div>-->
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ssf2') : lang('ssf2', 'ssf2')) ?> 
                            <?php echo form_input('cf2', '', 'class="form-control" id="cf2" ' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : '')); ?>

                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'ssf3') : lang('ssf3', 'ssf3')) ?>
                            <?php echo form_input('cf3', '', 'class="form-control" id="cf3" ' . ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); ?>
                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'ssf4') : lang('ssf4', 'ssf4')) ?>
                            <?php echo form_input('cf4', '', 'class="form-control" id="cf4"' . ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); ?>

                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'ssf5') : lang('ssf5', 'ssf5')) ?>
                            <?php echo form_input('cf5', '', 'class="form-control" id="cf5"' . ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); ?>

                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'ssf6') : lang('ssf6', 'ssf6')) ?>
                            <?php echo form_input('cf6', '', 'class="form-control" id="cf6"' . ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            <?php echo form_submit('add_employee', lang('add_employee'), 'class="btn btn-primary"'); ?>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
    <!--</div>-->
</div>

<script type="text/javascript">
    $(document).ready(function (e) {
        $('#add_employee_form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });
    });

    var specialKeys = new Array();
    specialKeys.push(8); //Backspace
    function IsNumeric(e, t) {
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
        document.getElementById("error").style.display = ret ? "none" : "inline";
        return ret;
    }
    function IsNumeric2(e, t) {
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
        document.getElementById("error1").style.display = ret ? "none" : "inline";
        return ret;
    }
    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("error_name").style.display = ret ? "none" : "inline";
        return ret;
    }
    function onlyAlphabets(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("errora2").style.display = ret ? "none" : "inline";
        return ret;
    }
    function onlyAlphabetsc(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("error_city").style.display = ret ? "none" : "inline";
        return ret;
    }

    $('#pan_card').change(function () {
        $('#errpancard').html(" ");
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        var pan_card = $(this).val();
        if (patt.test(pan_card)) {
            $('#errpancard').html(" ");
            var url = '<?= site_url("employees/getPancardNo/") ?>?pan_card=' + pan_card;
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                url: url,
                async: true,
                success: function (result) {
                    console.log(result);
                    if (result != null) {
                        $('#errpancard').html("\"<strong>" + pan_card + " </strong>\" this  Pancard No already Exist.")
                        $('#pan_card').val(" ");
                    }
                }
            });
        } else {
            if (pan_card != '') {
                $('#errpancard').html("\"<strong>" + pan_card + "</strong>\" this no. invalid, Please enter valid pancard no.");
                $(this).val(" ");
            }
        }
    });

    $('#country').change(function (event) {
        if ($(this).val() == 'other') {
            $('#addnewcountry').show();
            $('#state').html('<option value="other">Other</option>');
            $('#statecode').show();
            $('#statename').show();
        } else {
            $('#addnewcountry').hide();

            getstate($(this).val());
        }

    });

    $('#state').change(function (event) {
        if ($(this).val() == 'other' || $(this).val() == '') {
            $('#statecode').show();
            $('#statename').show();
        } else {
            $('#statecode').hide();
            $('#statename').hide();

        }
    });

    // Filter warehouses based on selected employee type
    $('select[name="employee_Type"]').on('change', function() {
        var employeeTypeValue = $(this).val();
        var employeeTypeName = employeeTypeValue.split('~')[1]; // Get the employee type name
        
        if (employeeTypeName) {
            $.ajax({
                url: "<?= site_url('employees/get_warehouses_by_employee_type'); ?>",
                type: "POST",
                data: {
                    employee_type: employeeTypeName,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                dataType: "json",
                success: function(response) {
                    const warehouseSelect = $('#warehouse');
                    
                    if (response.status == 'success') {
                        // Get currently selected warehouses
                        var selectedWarehouses = warehouseSelect.val() || [];
                        
                        // Clear existing options
                        warehouseSelect.empty();
                        
                        // Add new options
                        if (response.warehouses && response.warehouses.length > 0) {
                            response.warehouses.forEach(function(warehouse) {
                                const option = $('<option>', {
                                    value: warehouse.id,
                                    text: warehouse.name
                                });
                                
                                // Re-select if previously selected and still available
                                if (selectedWarehouses.indexOf(warehouse.id) !== -1) {
                                    option.prop('selected', true);
                                }
                                
                                warehouseSelect.append(option);
                            });
                        } else {
                            warehouseSelect.append($('<option>', {
                                value: '',
                                text: 'No Warehouses Available'
                            }));
                        }
                        
                        // Refresh the select2 plugin if used
                        if (warehouseSelect.hasClass('select2-hidden-accessible')) {
                            warehouseSelect.select2('destroy').select2();
                        } else if (warehouseSelect.data('chosen')) {
                            warehouseSelect.trigger('chosen:updated');
                        }
                    } else {
                        console.error('Error loading warehouses:', response.message);
                    }
                },
                error: function() {
                    console.error('AJAX error while fetching warehouses');
                }
            });
        }
    });

</script> 
<?= $modal_js ?>