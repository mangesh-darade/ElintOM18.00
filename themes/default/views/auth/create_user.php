<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
     .select2-container-multi{height: auto !important;}
     .has-error .select2-selection {
        border: 1px solid #a94442 !important;
        box-shadow: 0 0 5px rgba(169,68,66,.5);
    }

    .has-error .select2-selection--multiple {
        border-color: #a94442 !important;
    }
</style> 
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('create_user'); ?></h2>
    </div>
<p class="introtext"><?php echo lang('create_user'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                

                <?php $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                echo form_open("auth/create_user", $attrib);
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-5">
                            <div class="form-group">
                                <?php echo lang('first_name', 'first_name'); ?>
                                <div class="controls">
                                    <?php
                                    $first_name = isset($_POST['first_name'])?$_POST['first_name']:'';
                                    echo form_input('first_name', $first_name, 'class="form-control" id="first_name" onkeypress="return onlyAlphabets1(event,this);" type="text" required="required" '); ?><!--pattern=".{3,10}"-->
                                    <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('last_name', 'last_name'); ?>
                                <div class="controls">
                                    <?php 
                                    $last_name = isset($_POST['last_name'])?$_POST['last_name']:'';
                                    echo form_input('last_name', $last_name, 'class="form-control" id="last_name" onkeypress="return onlyAlphabets1(event,this);" type="text" required="required"'); ?>
                                   <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?= lang('gender', 'gender'); ?>
                                <?php
                                $ge[''] = array('male' => lang('male'), 'female' => lang('female'));
                                echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="tip form-control" id="gender" data-placeholder="' . lang("select") . ' ' . lang("gender") . '" required="required"');
                                ?>
                            </div>

                            <div class="form-group">
                                <?php echo lang('company', 'company_name'); ?>
                                <div class="controls">
                                    <?php 
                                     $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : (isset($Settings->site_name) ? $Settings->site_name : '');
                                    echo form_input('company_name', $company_name, 'class="form-control" id="company_name" required="required"');
                                    echo form_hidden('company', $company_name, 'id="company"');
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('phone', 'phone'); ?>
                                <div class="controls">
                                    <?php 
                                    $phone = isset($_POST['phone'])?$_POST['phone']:'';
                                    echo form_input('phone', $phone, 'class="form-control" data-bv-phone="true" data-bv-phone-country="US" maxlength="10"  id="phone" required="required"onkeypress="return IsNumeric(event)" ondrop="return 
                                         false" onpaste="return false"'); ?>
                                       <span id="error" style="color:#a94442; display: none;font-size:11px;">Please Enter numbers only</span>

                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('email', 'email'); ?>
                                <div class="controls">
                                    <?php 
                                     $email = isset($_POST['email']) ? $_POST['email'] : $this->session->userdata('email');
                                    ?>
                                    <input type="email" value="<?php echo $email;?>" id="email" name="email" class="form-control"
                                           required="required"/>
                                    <?php /* echo form_input('email', '', 'class="form-control" id="email" required="required"'); */ ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="date_of_joining">Date of Joining</label>
                                <div class="controls">
                                    <?php $date_of_joining = isset($_POST['date_of_joining']) ? $_POST['date_of_joining'] : date('Y-m-d'); ?>
                                    <input type="date" id="date_of_joining" name="date_of_joining" class="form-control" value="<?= $date_of_joining; ?>" required="required"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('username', 'username'); ?>
                                <div class="controls">
                                    <?php $username = isset($_POST['username']) ? $_POST['username'] : ''; ?>
                                    <input type="text" id="username" name="username" class="form-control"
                                           value="<?= $username; ?>" required="required" pattern=".{4,20}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('password', 'password'); ?>
                                <div class="controls">
                                    <div class="input-group">
                                        <?php echo form_password('password', '', 'class="form-control tip" id="password" required="required" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[\W]){1,})(?!.*\s).{8,}$"  data-bv-regexp-message="'.lang('pasword_hint').'"'); ?>
                                        <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    <span class="help-block"><?= lang('pasword_hint') ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('confirm_password', 'confirm_password'); ?>
                                <div class="controls">
                                    <div class="input-group">
                                        <?php echo form_password('confirm_password', '', 'class="form-control" id="confirm_password" required="required" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                        <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-5 col-md-offset-1">

                            <div class="form-group">
                                <?= lang('status', 'status'); ?>
                                <?php
                                $opt = array(1 => lang('active'), 0 => lang('inactive'));
                                echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" required="required" class="form-control select" style="width:100%;"');
                                ?>
                            </div>
                            
                            <div class="form-group">
                                <?= lang("group", "group"); ?>
                                <?php
                                foreach ($groups as $group) {
                                    if ($group['name'] != 'customer' && $group['name'] != 'supplier') {
                                        $gp[$group['id']] = $group['name'];
                                    }
                                }
                                echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : ''), 'id="group" required="required" class="form-control select" style="width:100%;"');
                                ?>
                            </div>

                            <div class="clearfix"></div>
                            <div class="no">
                                <div class="form-group"  id="warehouse_group">
                                    <?= lang("location*", "location*"); ?>
                                    <?php
                                   // $wh[''] = lang('select').' '.lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse[]', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" multiple class="form-control select" style="width:100%;" ');
                                    ?>
                                    <span id="warehouse_error" style="color:#a94442;font-size:11px; display:none;">Please select at least one location</span>
                                </div>
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = lang('select').' '.lang('biller');
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'id="biller" class="form-control select" style="width:100%;"');
                                    ?>
                                    <input type="hidden" name="biller" id="biller_id">
                                </div>
                                <div class="form-group">
                                    <?= lang('offline_mobile_app_access', 'offline_mobile_app_access'); ?>
                                    <?php
                                    $opt_mbaccess = array(1 => lang('active'), 0 => lang('inactive'));
                                    echo form_dropdown('offline_mobile_app_access', $opt_mbaccess, (isset($_POST['offline_mobile_app_access']) ? $_POST['offline_mobile_app_access'] : ''), 'id="offline_mobile_app_access" class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?= lang('offline_windows_app_access', 'offline_windows_app_access'); ?>
                                    <?php
                                    //$opt_winacces = array(1 => lang('active'), 0 => lang('inactive'));
                                    $opt_winacces = array(1 => lang('active'));
                                    echo form_dropdown('offline_windows_app_access', $opt_winacces, (isset($_POST['offline_windows_app_access']) ? $_POST['offline_windows_app_access'] : ''), 'id="offline_windows_app_access" class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?= lang("view_right", "view_right"); ?>
                                    <?php
                                    $vropts = array(1 => lang('all_records'), 0 => lang('own_records'));
                                    echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : 1), 'id="view_right" class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?= lang("edit_right", "edit_right"); ?>
                                    <?php
                                    $opts = array(1 => lang('yes'), 0 => lang('no'));
                                    echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : 0), 'id="edit_right" class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?= lang("allow_discount", "allow_discount"); ?>
                                    <?= form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : 0), 'id="allow_discount" class="form-control select" style="width:100%;"'); ?>
                                </div>
                 
                            </div>
                              <?php if($pos_type == 'restaurant'){ ?>
                                <div class="form-group">
                                    <label for="restaurantTables"> Tables</label>
                                    <select class="form-control" name="table_assign[]" multiple="true">
                                            <?php foreach($restaurantTables as $tables){ ?>
                                                <option value="<?= $tables->id ?>"><?= $tables->name ?></option>
                                            <?php } ?>
                                    </select>    
                                </div>    
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="checkbox" for="notify">
                                        <input type="checkbox" name="notify" value="1" id="notify" checked="checked"/>
                                        <?= lang('notify_user_by_email') ?>
                                    </label>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row" id="create-user-face-register" style="margin-top:10px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Register Face (Optional)</label>
                            <video class="face-register-video" width="320" height="240" autoplay muted playsinline style="display:block;background:#000;border-radius:4px;"></video>
                            <canvas class="face-register-canvas" width="320" height="240" style="display:none;"></canvas>
                            <div style="margin-top:8px;">
                                <button type="button" class="btn btn-info start-face-camera">Start Face Camera</button>
                                <button type="button" class="btn btn-primary capture-face-descriptor">Capture Face</button>
                            </div>
                            <div style="margin-top:8px;">
                                <img class="face-register-preview img-thumbnail" src="" alt="Captured face preview" style="display:none;max-width:160px;">
                            </div>
                            <small class="text-muted face-register-status" style="display:block;margin-top:6px;"></small>
                            <input type="hidden" name="face_descriptor" class="face-register-descriptor" value="">
                            <input type="hidden" name="face_image_data" class="face-register-image-data" value="">
                        </div>
                    </div>
                </div>

                <p><?php echo form_submit('add_user', lang('add_user'), 'class="btn btn-primary"'); ?></p>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
       // $('#username').disableAutoFill();
       // $('#password').disableAutoFill();
    
        $('.no').slideUp();
        $('#group').change(function (event) {
            var group = $(this).val();
            if (group == 1 || group == 2) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
    });
    var specialKeys = new Array();
	specialKeys.push(8); //Backspace
function IsNumeric(e) {
            var keyCode = e.which ? e.which : e.keyCode
            var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
            document.getElementById("error").style.display = ret ? "none" : "inline";
                            return ret;
}

function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        //document.getElementById("error2").style.display = ret ? "none" : "inline";
	return ret;	
} 

$('#phone').on('keyup change', function () {
    var phone = $(this).val();
    $('#username').val(phone);
});

$('#company_name').on('keyup change', function () {
    $('#company').val($(this).val());
});
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= $assets; ?>js/auth_face_register.js"></script>
<script>
$(document).ready(function() {
    var warehouseId = $('#warehouse').val();
   
    if (warehouseId) {
        getbillerbyWarehoueseid(warehouseId);
    }
    $('#warehouse').on('change', function() {
        var warehouseId = $(this).val();
        getbillerbyWarehoueseid(warehouseId);
    });
    $('#biller').on('change', function() {
        var biller_name = $(this).val();
        $("#biller_id").val(biller_name);
    });

    function getbillerbyWarehoueseid(warehouseId) {
        if (warehouseId) {
            $.ajax({
                url: "<?= site_url('sales/get_biller_details'); ?>",
                type: "GET",
                data: {
                    warehouse_id: warehouseId[0] // Assuming warehouseId is an array, take the first element
                },
                dataType: "json",
                success: function(response) {
                    const slbiller = $('#biller');
                    if (response.success) {
                        slbiller.empty(); // Clear existing options
                        response.billers.forEach(function(biller, index) {
                            const option = $('<option>', {
                                value: biller.id,
                                text: biller.name
                            });

                            slbiller.append(option);
                        });
                        response.primer_biller ? slbiller.val(response.primer_biller).trigger(
                            'change') : slbiller.val(response.billers[0].id).trigger('change');

                    } else {
                        // alert(response.message);
                        // slbiller.empty(); // Clear existing options if no billers found
                        // slbiller.append($('<option>', {
                        //     value: '',
                        //     text: 'No Billers Available'
                        // }));
                    }
                },
                error: function() {
                    // alert('Error fetching warehouse data');
                }
            });
        }
    }
 //////////////////////////// Generate Variant PO ////////////////////////////

    // Filter warehouses based on selected group
    $('#group').on('change', function() {
        var groupId = $(this).val();
        
        if (groupId) {
            $.ajax({
                url: "<?= site_url('auth/get_warehouses_by_group'); ?>",
                type: "POST",
                data: {
                    group_id: groupId,
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
                                text: 'No Locations Available'
                            }));
                        }
                        
                        // Refresh the select2 or chosen plugin if used
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

});

function togglePasswordVisibility(element) {
    var field = element.parentElement.querySelector('input');
    var icon = element.querySelector('i');
    if (field && (field.type === "password" || field.type === "text")) {
        if (field.type === "password") {
            field.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}
</script>
