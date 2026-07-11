<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 

<style>


   .modal.fade {
    -webkit-transition: opacity .3s linear, top .3s ease-out;
    -moz-transition: opacity .3s linear, top .3s ease-out;
    -ms-transition: opacity .3s linear, top .3s ease-out;
    -o-transition: opacity .3s linear, top .3s ease-out;
    transition: opacity .3s linear, top .3s ease-out;
    top: -5%;
}

.modal-header .btnGrp{
      position: absolute;
      top:8px;
      right: 10px;
    } 
  
  </style>
<div class="container" >				
<div class="mymodal" id="modal-1" role="dailog">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_supplier'); ?></h4>
        </div>
         <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id' => 'add-suppliers-form');
        echo form_open_multipart("suppliers/edit/" . $supplier->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang("type", "type"); ?> 
                    <?php // $types = array('company' => lang('company'), 'person' => lang('person'));  echo form_dropdown('type', $types, $supplier->type, 'class="form-control select" id="type" required="required"'); ?>
                </div> -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group company">
                            <?= lang("company", "company"); ?>
                            <?php echo form_input('company', $supplier->company, 'autocomplete="off" class="form-control tip" id="company" required="required"'); ?>
                        </div>
                        <div class="form-group person">
                            <?= lang("name", "name"); ?>
                            <?php echo form_input('name', $supplier->name, 'autocomplete="off" class="form-control tip" id="name" required="required" data-bv-notempty="true" onkeypress="return onlyAlphabets(event,this);" type="text" ondrop="return false;" onpaste="return false;"'); ?>  
                            <span id="error1" style="color:#a94442;font-size:11px; display: none">please enter alphabets only</span>                
                        </div>
                        <div class="row">
                        <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("vat_no", "vat_no"); ?>
                            <?php echo form_input('vat_no', $supplier->vat_no, 'autocomplete="off" class="form-control" id="vat_no"'); ?>
                        </div>
                        </div>
                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                    <?= lang("gstn_no", "gstn_no"); ?>
                                <?php echo form_input('gstn_no', $supplier->gstn_no, 'autocomplete="off" class="form-control" id="gstn_no"  onchange="return validateGstin();"'); ?>
                                    </div>
                                </div> -->
                                <?php
                                $show_tax_field = false;
                                $tax_label = '';
                                if (!empty($settings->country_name) && !empty($countries)) {
                                    foreach ($countries as $c) {
                                        if (strcasecmp($c->name, $settings->country_name) === 0 && !empty($c->TaxNumberLabelText)) {
                                            $tax_label = $c->TaxNumberLabelText;
                                            $show_tax_field = true;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <?php if ($show_tax_field): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gstn_no"><?= $tax_label; ?></label>
                                            <?php echo form_input('gstn_no', $supplier->gstn_no, 'autocomplete="off" class="form-control" id="gstn_no"  onchange="return validateGstinss();"'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php // echo form_input('contact_person', $supplier->contact_person, 'autocomplete="off" class="form-control" id="contact_person" required="required"'); ?>
                </div> -->
                    
                    <div class="row">
                        <div class="col-md-6">
                        <div class="form-group">
                           <?= lang("email_address", "email_address"); ?>
                         <input type="text" name="email" class="form-control" 
                               value="<?= $supplier->email ?>"  autocomplete="off" id="email_address" data-bv-emailaddress
        data-bv-onerror="onFieldError" data-bv-onsuccess="onFieldSuccess" data-bv-onstatus="onFieldStatus"/>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="form-group">
                        <?= lang("phone", "phone"); ?> 
                        <input type="tel" name="phone" class="form-control" id="phone"  value="<?= $supplier->phone ?>" required/>
                        <span id="phone_error" style="color:#a94442; display:none; font-size:11px;"></span>
                    </div>
                        </div>
                    </div>
                    
                   
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', $supplier->address, 'autocomplete="off" class="form-control" id="address"'); ?>
                    </div>
                    <?php if ($Owner || $Admin) { ?>
                        <div class="form-group">
                            <?= lang('Location', 'location_id'); ?>
                            <?php
                                $loc = array('' => '');
                                if (!empty($warehouses)) {
                                    foreach ($warehouses as $wh) {
                                        $loc[$wh->id] = $wh->name . ' (' . $wh->code . ')';
                                    }
                                }
                                // Check if location_id already has a value - if yes, make it readonly
                                $hasLocation = !empty($supplier->location_id) && $supplier->location_id > 0;
                                $attributes = 'id="location_id" data-placeholder="' . lang("select") . ' ' . lang("location") . '" class="form-control input-tip select" style="width:100%; height:30px;';
                                if ($hasLocation) {
                                    $attributes .= ' opacity:0.65; pointer-events:none;" disabled="disabled"';
                                } else {
                                    $attributes .= '"';
                                }
                                echo form_dropdown('location_id', $loc, (isset($_POST['location_id']) ? $_POST['location_id'] : $supplier->location_id), $attributes);
                                // Add hidden field to preserve value when disabled
                                if ($hasLocation) {
                                    echo '<input type="hidden" name="location_id" value="' . $supplier->location_id . '" />';
                                    echo '<small class="text-muted" style="display:block; margin-top:5px;"><i class="fa fa-lock"></i> Location cannot be changed once assigned</small>';
                                }
                            ?>
                        </div>
                    <?php } else { ?>
                        <input type="hidden" name="location_id" value="<?= $this->session->userdata('warehouse_id'); ?>" />
                    <?php } ?>
                    
                    <div class="row">
                      <!-- <div class="col-md-6">
                     <div class="form-group">
                        <?= lang("country", "country"); ?>
                         <?php echo form_input('country', $supplier->country, 'autocomplete="off" class="form-control" id="country" onkeypress="return onlyAlphabets3(event,this);" type="text" ondrop="return false;" onpaste="return false;"'); ?>
                        <span id="error4" style="color:#a94442;font-size:11px; display: none">please enter alphabets only</span>
                    </div>
                     </div> -->
                    <div class="col-sm-6">
                          <div class="form-group">
                              <?= lang("country", "country"); ?>
                              <select name="country" id="country" required
                                class="form-control input-tip select"
                                data-placeholder="<?= lang('select').' '.lang('country'); ?>"
                                style="width:100%;">
                                <option value=""></option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= $country->name ?>"
                                        <?= ($supplier->country == $country->name) ? 'selected' : '' ?>>
                                        <?= $country->name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                          </div>
                      </div>
                    
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("state", "state"); ?>
                            <?php
                                $selected_state = str_replace('State ', '', $supplier->state);
                                $selected_state = explode('~', $selected_state)[0];
                                $st = [];
                                foreach ($states as $state) {
                                    $st[$state->name] = $state->name;
                                }
                                echo form_dropdown('state',  $st, isset($_POST['state']) ? $_POST['state'] : $selected_state,  'id="state" required = "required" class="form-control input-tip select" style="width:100%; height:30px;"' );
                            ?>
                            <!-- <br> <br>
                            <?php echo $this->sma->dbSavedValue($st,$supplier->state);?> -->
                        </div>
                    </div> 
                    </div>
                     <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                           <?= lang("city", "city"); ?>
                        <?php echo form_input('city', $supplier->city, 'autocomplete="off" class="form-control" id="city"  onkeypress="return onlyAlphabets1(event,this);" type="text" '); ?>
                        <span id="error2" style="color:#a94442;font-size:11px; display: none">please enter alphabets only</span>
                    </div>
                    </div>
                    <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("postal_code", "postal_code"); ?>
                                        <?php echo form_input('postal_code', $supplier->postal_code, 'autocomplete="off" class="form-control" id="postal_code" maxlength="6"  onkeypress="return IsNumeric1(event)" ondrop="return false"'); ?>
                                        <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please
                                            enter numbers only</span>
                                    </div>
                                </div> -->
                                <?php
                                if (!empty($settings->country_name) && !empty($countries)) {
                                    foreach ($countries as $c) {
                                        if (strcasecmp($c->name, $settings->country_name) === 0) {
                                            $postal_code_value = $c->postal_code;
                                            break;
                                        }
                                    }
                                }
                                if (!empty($postal_code_value) && $postal_code_value != 0): ?>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?= lang("Postal_Code *", "postal_code"); ?></label>
                                            <?php echo form_input('postal_code', $supplier->postal_code, 'autocomplete="off" class="form-control" id="postal_code" maxlength="6"  onkeypress="return IsNumeric1(event)" ondrop="return false"'); ?>
                                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'scf1') : lang('scf1', 'scf1')) ?>
                                <?php echo form_input('cf1', $supplier->cf1, 'autocomplete="off" class="form-control" id="cf1" '. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); ?>
                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'scf2') : lang('scf2', 'scf2')) ?>
                                <?php echo form_input('cf2', $supplier->cf2, 'autocomplete="off" class="form-control" id="cf2" '. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : '')); ?>

                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'scf3') : lang('scf3', 'scf3')) ?>
                                <?php echo form_input('cf3', $supplier->cf3, 'autocomplete="off" class="form-control" id="cf3" '. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); ?>
                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'scf4') : lang('scf4', 'scf4')) ?>
                                <?php echo form_input('cf4', $supplier->cf4, 'autocomplete="off" class="form-control" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); ?>

                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'scf5') : lang('scf5', 'scf5')) ?>
                                <?php echo form_input('cf5', $supplier->cf5, 'autocomplete="off" class="form-control" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); ?>

                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'scf6') : lang('scf6', 'scf6')) ?>
                                <?php echo form_input('cf6', $supplier->cf6, 'autocomplete="off" class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); ?>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <?php echo form_submit('edit_supplier', lang('edit_supplier'), 'class="btn btn-primary"'); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<!--</div>-->
</div>
<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function (e) {
        $('#add-suppliers-form').bootstrapValidator({
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
    function onlyAlphabets(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        document.getElementById("error1").style.display = ret ? "none" : "inline";
	return ret;
    }
    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        document.getElementById("error2").style.display = ret ? "none" : "inline";
	return ret;
    }
    function onlyAlphabets2(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        document.getElementById("error3").style.display = ret ? "none" : "inline";
	return ret;
    }
    function onlyAlphabets3(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        document.getElementById("error4").style.display = ret ? "none" : "inline";
	return ret;
    }
    function IsNumeric(e) {
	var keyCode = e.which ? e.which : e.keyCode
	var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
	document.getElementById("error").style.display = ret ? "none" : "inline";
	return ret;
    }
    function IsNumeric1(e) {
        var keyCode = e.which ? e.which : e.keyCode
	var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
	document.getElementById("errorp").style.display = ret ? "none" : "inline";
	return ret;
   }  
    
</script>
<script>
    $('#country').change(function (event) {
        if($(this).val()=='other'){
            $('#addnewcountry').show();
            $('#state').html('<option value="other">Other</option>');
            $('#statecode').show();
          $('#statename').show();
        } else {
            $('#addnewcountry').hide();
            getstate($(this).val());
        }
         
    });
    $('#state').change(function(event){
     if($(this).val()=='other' ||$(this).val()=='' ){
          $('#statecode').show();
          $('#statename').show();
     }else {
        $('#statecode').hide();
        $('#statename').hide();
     }
    });
    $('#location_id').select2('readonly', true); 
    $('#location_id').prop('disabled', false); 
    $('#s2id_location_id').css({
        'pointer-events': 'none',
        'background-color': '#eee'
    });
 
</script>
<script>
     var taxLabel = <?= json_encode($tax_label); ?>;

        function validateGstinss() {
            var gstInput = document.getElementById("gstn_no");
            var value = gstInput.value;

            if (value.length !== 15) {
                alert(taxLabel + " must be 15 characters long.");
                gstInput.value = '';
                gstInput.select();
                gstInput.focus();
                return false;
            }
            return true;
        }
   (function(){

    const phoneInput = document.getElementById('phone');
    const countrySelect = document.getElementById('country');
    const errorMsg = document.getElementById('phone_error');

    if (!phoneInput || !countrySelect) return;

    const countryData = <?php
        echo json_encode(array_map(function($c) {
            return [
                'name' => $c->name,
                'phone_digits' => (!empty($c->phone_digits) && is_numeric($c->phone_digits))
                                    ? (int)$c->phone_digits
                                    : 10
            ];
        }, $countries));
    ?>;

    function getRequiredLength(countryName) {
       const found = countryData.find(c => c.name === countryName);
        if (!found || !found.phone_digits) {
            return 10; // default if empty/null
        }
        const digits = parseInt(found.phone_digits);
        return isNaN(digits) ? 10 : digits; 
    }

    function validatePhone() {

        const phone = phoneInput.value.trim();
        const requiredLength = getRequiredLength(countrySelect.value);
        const isValidDigits = /^[1-9][0-9]*$/.test(phone);

        if (phone === '') {
            // errorMsg.textContent = 'Phone number is required.';
            errorMsg.style.display = 'none';
            return false;
        }

        if (!isValidDigits) {
            errorMsg.textContent =
                'Phone number must contain only digits and should not start with 0.';
            errorMsg.style.display = 'block';
            return false;
        }

        if (phone.length !== requiredLength) {
            errorMsg.textContent =
                'Phone number must be ' + requiredLength + ' digits.';
            errorMsg.style.display = 'block';
            return false;
        }

        errorMsg.style.display = 'none';
        return true;
    }


    phoneInput.addEventListener('input', validatePhone);

    phoneInput.addEventListener('keypress', function(e){
        if (!/\d/.test(e.key)) {
            e.preventDefault();
            return;
        }

        const requiredLength = getRequiredLength(countrySelect.value);
        if (phoneInput.value.length >= requiredLength) {
            e.preventDefault();
        }
    });

    $('#country').on('change', function () {
        validatePhone();
    });

    // ✅ SUBMIT HANDLER MOVED INSIDE
    $('#add-suppliers-form').on('submit', function (e) {

        if (!validatePhone()) {
            e.preventDefault();
            phoneInput.focus();
            return false;
        }

    });

})();
</script>
