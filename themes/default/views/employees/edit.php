<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
?>

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
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('Edit Employee'); ?></h4>
            </div>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'autocomplete' => 'off');
            echo form_open_multipart("employees/edit/" .$employee->id, $attrib);
            ?>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>                 
                <div class="row">
                    <div class="col-md-6">                         
                        <div class="form-group person">
                                <?= lang("Employee Type *", "Employee Type") ?>
                            <select class="form-control" name="employee_Type" required="true">                                 
                                <?php foreach ($employeeType as $emptype) { ?>
                                    <option value="<?= $emptype->id . '~' . $emptype->name ?>" <?= ($emptype->id ==$employee->group_id ? 'Selected' : '') ?>><?= $emptype->description ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <?= lang("warehouse", "warehouse") ?>
                            <?php
                            // Get warehouse data from employee record using location_id
                            $warehouse_id_value = $employee->location_id;
                            $selected_warehouses = !empty($warehouse_id_value) ? explode(',', $warehouse_id_value) : [];
                            
                            // Build warehouse options array
                            $wh = [];
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            
                            // Ensure selected warehouses are strings for proper comparison
                            $selected_warehouses = array_map('strval', $selected_warehouses);
                            
                            echo form_dropdown('warehouse[]', $wh, $selected_warehouses, 'id="warehouse" multiple class="form-control select" style="width:100%;" ');
                            ?>
                        </div>
                        <div class="form-group person">
                            <?= lang("name", "name"); ?> 
                            <?php echo form_input('name',$employee->name, 'class="form-control tip" id="name"   required="required"'); ?>
                            <span id="error_name" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>                         
                        <div class="form-group">
                            <?= lang("email_address", "email_address"); ?>
                            <input type="text" name="email" class="form-control"  id="email_address" value="<?=$employee->email ?>"/>
                        </div>
                        <div class="form-group">
                            <?= lang("phone", "phone"); ?>
                            <input type="tel" name="phone" class="form-control"  id="phone" required="required" value="<?=$employee->phone ?>"/>
                        </div>
                        <div class="form-group">
                            <?= lang("Date_OF_Birth", "DOB"); ?>
                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : (($employee->dob == NULL || $employee->dob == "0000-00-00") ? '' : date('d/m/Y', strtotime($employee->dob)))), 'class="form-control input-tip date" id="date" '); ?> 
                        </div>
                        <div class="form-group">
                            <?= lang("address", "address"); ?>
                            <?php echo form_input('address',$employee->address, 'class="form-control" id="address"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang("city", "city"); ?>
                            <?php echo form_input('city',$employee->city, 'class="form-control" id="city" onkeypress="return onlyAlphabetsc(event,this);" '); ?>
                            <span id="error_city" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                        <div class="form-group">
                            <?= lang("state", "state"); ?>
                            <?php
                            $st[""] = "";
                            foreach ($states as $state) {
                                $st[$state->name] = $state->name;
                            }
                            echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] :$employee->state), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("state") . 'class="form-control input-tip select" style="width:100%; height:30px;"');
                            ?>
                            <br>
                            <?php echo $this->sma->dbSavedValue($st,$employee->state); ?>

                        </div>
                        <div class="form-group">
                            <?= lang("country", "country"); ?>
                            <?php echo form_input('country',$employee->country, 'class="form-control" id="country" onkeypress="return onlyAlphabetsct(event,this);" '); ?>
                            <span id="errora2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                        <div class="form-group">
                            <?= lang("Pan_card", "Pan_card"); ?>
                            <?php echo form_input('pan_card',$employee->pan_card, 'class="form-control" id="pan_card" maxlength="10"'); ?>
                            <input name="old_pancard" disabled="true"  id="old_pancard"  value="<?php echo$employee->pan_card ?>" type="hidden" >
                            <small class="text-danger" id="errpancard"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("postal_code", "postal_code"); ?>
                            <?php echo form_input('postal_code',$employee->postal_code, 'class="form-control"  maxlength="6" id="postal_code" onkeypress="return IsNumeric2(event,this)" type="text" '); ?>
                            <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>

                        <div class="form-group">
                            <?= lang("photos", "photos") ?>
                            <input id="photos" type="file" data-browse-label="<?= lang('browse'); ?>" name="photos" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>

                        <div class="form-group">
                            <?= lang("Attach_Identity_Proof", "Attach_identity_proof") ?>
                            <input id="attch_document" type="file" data-browse-label="<?= lang('browse'); ?>" name="attch_document" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>

<!--                        <div class="form-group">                         
                            < ?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ssf1') : lang('ssf1', 'ssf1')) ?>                                
                            < ?php echo form_input('cf1',$employee->cf1, 'class="form-control" id="cf1" ' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); ?> 
                        </div>-->
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ssf2') : lang('ssf2', 'ssf2')) ?> 
                            <?php echo form_input('cf2',$employee->cf2, 'class="form-control" id="cf2" ' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : '')); ?>

                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'ssf3') : lang('ssf3', 'ssf3')) ?>
                            <?php echo form_input('cf3',$employee->cf3, 'class="form-control" id="cf3" ' . ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); ?>
                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'ssf4') : lang('ssf4', 'ssf4')) ?>
                            <?php echo form_input('cf4',$employee->cf4, 'class="form-control" id="cf4"' . ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); ?>

                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'ssf5') : lang('ssf5', 'ssf5')) ?>
                            <?php echo form_input('cf5',$employee->cf5, 'class="form-control" id="cf5"' . ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); ?>

                        </div>
                        <div class="form-group">
                            <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'ssf6') : lang('ssf6', 'ssf6')) ?>
                            <?php echo form_input('cf6',$employee->cf6, 'class="form-control" id="cf6"' . ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php echo form_submit('edit_employee', lang('edit_employee'), 'class="btn btn-primary"'); ?>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
    <!--</div>-->
</div>
<?= $modal_js ?>

<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $('select.select').select2({minimumResultsForSearch: 7});
        
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
                            // Get currently selected warehouses before clearing
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
                                    if (selectedWarehouses.indexOf(warehouse.id.toString()) !== -1) {
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
        
        // Don't trigger change on page load - let the PHP-generated selection remain
        // Only trigger when user actually changes the employee type
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
    function onlyAlphabetsct(e, t) {
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
        var pan_card = $(this).val();//         ?date='+paydate+'&option='+payoption+
        var old_pancard = $('#old_pancard').val();
        var custid = $('#customer_id').val();
        console.log(custid);
        if (patt.test(pan_card)) {
            $('#errpancard').html(" ");
            if (pan_card != old_pancard) {
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
            }

        } else {
            if (pan_card != '') {
                $('#errpancard').html("\"<strong>" + pan_card + " </strong>\" this no. invalid, Please enter valid pancard no.")
                $(this).val(' ');
            }
        }
    });


</script>
 

