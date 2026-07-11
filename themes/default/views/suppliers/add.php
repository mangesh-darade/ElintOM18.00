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

    .add_show_location_div{
        display : flex;
        justify-content : space-between;
        align-items : end;
    }

    .add_show_location_div :first-child{
        width : 90%;
    }

    .add_show_location_div div:nth-child(2) {
        width : 10%;
        height : 32px;
        display: flex;
        justify-content: center;
        align-items: center;
        border : 1px solid #ccc;
        background-color : #ccc;
        cursor : pointer;
    }

    #open-location-modal{
        width : 100% ;
        height : 100%; 
        display: flex;
        justify-content: center;
        align-items: center;        
    }

    #s2id_location_id a {
        width : 100%;
        margin : 0%;
    }

    #s2id_location_id{
        height : 32px !important;
    }

    .select2-choice{
        height : 32px !important;
    }

    #s2id_location_id .select2-arrow {
    display: none !important;
}
    /* Stacking helpers for Bootstrap 3 modals */
    .modal { z-index: 1050; }
    .modal-backdrop { z-index: 1040; }

    /* ensure later backdrops sit between stacked modals */
    .modal-backdrop.in + .modal-backdrop.in { z-index: 1051 !important; }

    #addWarehouseModal{
        top : 0%;
    }

    #location_id.select {
    }

    .iconcolor{
        color : #428bca;
        height : 100%;
        width : 100%;
        display: flex;
        justify-content : center;
        align-items : center;
    }
  
  </style>
<div class="container add-suplr-modal" style="margin-top:150px;">				
<div class="mymodal" id="modal-1" role="dailog">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-supp" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_supplier'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id' => 'add-suppliers-form');
        echo form_open_multipart("suppliers/add", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang("type", "type"); ?>
                    <?php $types = array('company' => lang('company'), 'person' => lang('person'));
            echo form_dropdown('type', $types, '', 'class="form-control select" id="type" data-bv-notempty="true" required="required"'); ?>
                </div> -->

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group company">
                                <?= lang("company", "company"); ?>
                                <?php echo form_input('company', '', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                            </div>
                            <div class="form-group person">
                                <?= lang("name", "name"); ?>  
                                <?php echo form_input('name', '', 'class="form-control tip" id="name" required="required" data-bv-notempty="true" onkeypress="return onlyAlphabets(event,this);" type="text" ondrop="return false;" onpaste="return false;"'); ?>
                                <span id="error1" style="color:#a94442;font-size:11px; display: none">please enter alphabets only</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("vat_no", "vat_no"); ?>
                                    <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                                </div>
                                </div>
                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("gstn_no", "gstn_no"); ?>
                                        <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no"  onchange="return validateGstin();"'); ?>
                                    </div>
                                </div> -->
                                <?php
                                $show_tax_field = false;
                                $tax_label = '';
                                if (!empty($settings->country_name) && !empty($country)) {
                                    foreach ($country as $c) {
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
                                            <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no"  onchange="return validateGstinss();"'); ?>
                                        </div>  
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php echo form_input('contact_person', '', 'class="form-control" id="contact_person" data-bv-notempty="true"'); ?>
                </div>-->
               
                    <div class="row">
                        <div class="col-md-6">
                        <div class="form-gromup">
                           <?= lang("email_address", "email_address"); ?>
                           <input type="text" name="email" class="form-control" id="email_address" data-bv-emailaddress
        data-bv-onerror="onFieldError" data-bv-onsuccess="onFieldSuccess" data-bv-onstatus="onFieldStatus"/>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="form-group">
                        <?= lang("phone", "phone"); ?> 
                        <input type="text" name="phone" class="form-control" id="phone" required/>
                        <span id="phone_error" style="color:#a94442; display:none; font-size:11px;"></span>
                    </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?> 
                        <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                    </div>
                    <?php if (($Owner || $Admin) && $Settings->display_job_work) { ?>
                        <div class="form-group add_show_location_div">
                            <div>
                                <?= lang('Location', 'location_id'); ?>
                                <?php
                                    $loc = array('' => '');
                                    if (!empty($warehouses)) {
                                        foreach ($warehouses as $wh) {
                                            $loc[$wh->id] = $wh->name . ' (' . $wh->code . ')';
                                        }
                                    }
                                    echo form_dropdown('location_id', $loc, (isset($_POST['location_id']) ? $_POST['location_id'] : ''), 'id="location_id" readonly="readonly" data-placeholder="' . lang("select") . ' ' . lang("location") . '" class="form-control input-tip select" style="width:100%; height:30px;"');
                                ?>
                            </div>
                            <div>
                                <!-- <a href="<?php echo site_url('suppliers/add_warehouse'); ?>" id="open-location-modal" data-toggle="modal" data-target="#addWarehouseModal"> -->
                                <div id="open-location-modal">
                                    <i class="fa fa-plus-circle fa-lg iconcolor" aria-hidden="true"></i>
                                </div>
                                <!-- </a> -->
                            </div>
                        </div>
                    <?php } else { ?>
                        <input type="hidden" name="location_id" value="<?= $this->session->userdata('warehouse_id'); ?>" />
                    <?php } ?>
                    <div class="row">
                    <div class="col-md-6">
                     <div class="form-group">
                        <?= lang("country", "country"); ?>
                         <?php
				$ct[""] = "";
				foreach ($country as $country_value) {
				 	$ct[$country_value->name] = $country_value->name;
				}
                                $ct['other'] ='Other';
				echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $biller->country), 'id="country" required="required" data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
			?>
                        
                        <!-- <input type="text" name="add_country" placeholder="Country " id="addnewcountry" class="form-control" style="display:none; margin-top: 10px;"/> -->
                        <input type="text" name="add_country" placeholder="Country " id="addnewcountry" class="form-control" style="display:none; margin-top: 10px;" value="<?=$biller->country?>"/>
                        <span id="error" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                    </div>
                     </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("state", "state"); ?>
                         <?php
				$st[""] = "";
				foreach ($states as $state) {
					$st[$state->name] = $state->name. " (".$state->code.")";
				}
				echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : $biller->state), 'id="state" required="required" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select" style="width:100%; height:30px;"');
			?>
                       <input type="text" name="statecode" placeholder="State Code " id="statecode" class="form-control" style="display:none; margin-top: 10px;"/>
                        <input type="text" name="statename" placeholder="State Name " id="statename" class="form-control" style="display:none; margin-top: 10px;"/>

                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("city", "city"); ?>
                                        <?php echo form_input('city', '', 'class="form-control" id="city" onkeypress="return onlyAlphabets1(event,this);" type="text" ondrop="return false;" onpaste="return false;"'); ?>
                                        <span id="error2" style="color:#a94442;font-size:11px; display: none">please
                                            enter alphabets only</span>
                                    </div>
                                </div>


                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("postal_code", "postal_code"); ?>
                                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" onkeypress="return IsNumeric1(event)" ondrop="return false"'); ?>

                                        <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please
                                            enter numbers only</span>
                                    </div>
                                </div> -->
                                <?php
                                    if (!empty($settings->country_name) && !empty($country)) {
                                        foreach ($country as $c) {
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
                                                <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" maxlength="6" onkeypress="return IsNumeric2(event,this)" style ="margin-top: -12px !important;" type="text"'); ?>
                                                <span id="errorn1" style="color:#a94442; display: none; font-size:11px;">please enter numbers only</span>
                                            </div>
                                        </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'scf1') : lang('scf1', 'scf1')) ?>
                                <?php echo form_input('cf1', '', 'class="form-control" id="cf1" '. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); ?>
                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'scf2') : lang('scf2', 'scf2')) ?>
                                <?php echo form_input('cf2', '', 'class="form-control" id="cf2" '. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : '')); ?>

                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'scf3') : lang('scf3', 'scf3')) ?>
                                <?php echo form_input('cf3', '', 'class="form-control" id="cf3" '. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); ?>
                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'scf4') : lang('scf4', 'scf4')) ?>
                                <?php echo form_input('cf4', '', 'class="form-control" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); ?>

                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'scf5') : lang('scf5', 'scf5')) ?>
                                <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); ?>

                            </div>
                            <div class="form-group">
                                <?php  echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'scf6') : lang('scf6', 'scf6')) ?>
                                <?php echo form_input('cf6', '', 'class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); ?>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <?php echo form_submit('add_supplier', lang('add_supplier'), 'class="btn btn-primary"'); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<input type="hidden" data-is-warehouse-added="false" id="is_warehouse_added" value="false"/>
</div>
<!-- <?php include_once('add_warehouse_from_supplier.php'); ?> -->
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
    $(document).on('click', '#open-location-modal', function(e) {
        e.preventDefault();

        $.ajax({
            url: "<?= site_url('suppliers/add_warehouse'); ?>",
            type: "GET",
            dataType: "html",
            success: function(response) {
                try {
                    $('#addWarehouseModal').remove();

                    var $resp = $(response);
                    var $modal = $resp.filter('#addWarehouseModal');
                    if ($modal.length === 0) {
                        $modal = $resp.find('#addWarehouseModal');
                    }

                    if ($modal.length === 0) {
                        console.error('Modal markup #addWarehouseModal not found in response');
                        return;
                    }

                    $('body').append($modal);

                    $resp.filter('script').each(function() {
                        var code = this.text || this.textContent || this.innerHTML || '';
                        if (code && code.trim()) {
                            $.globalEval(code);
                        }
                    });

                    // if ($.fn.select2) {
                    //     $modal.find('select.select').select2({ width: '100%' });
                    // }

                    var $sup = $('.add-suplr-modal');
                    var supCountryText = $sup.find('select#country_new option:selected').text() || '';
                    var supStateText = ($sup.find('select#state_new option:selected').text() || '').replace(/\s*\(.+\)$/, '');
                    var supCity = $sup.find('#city_new').val() || '';
                    var supPostal = $sup.find('#postal_code_new').val() || '';
                    var supAddress = $sup.find('#address_new').val() || '';

                    if (supCountryText) { $modal.find('input#country_new').val(supCountryText); }

                    if (supCity) { $modal.find('input#city_new').val(supCity).trigger('input'); }

                    if (supPostal) { $modal.find('input#postal_code_new').val(supPostal).trigger('input'); } 

                    if (supAddress) {
                        var $al1 = $modal.find('input#address_line1_new');
                        if ($al1.length) { $al1.val(supAddress); }
                        var $addrTa = $modal.find('textarea#address_new');
                        if ($addrTa.length) { $addrTa.val(supAddress); }
                    }

                    if (supStateText) {
                        var $stateSel = $modal.find('select#state_new');
                        if ($stateSel.length) {
                            var matchVal = null;
                            $stateSel.find('option').each(function() {
                                if ($.trim($(this).text()) === $.trim(supStateText)) {
                                    matchVal = $(this).val();
                                    return false;
                                }
                            });
                            if (matchVal !== null) {
                                $stateSel.val(matchVal).trigger('change');
                            }
                        }
                    }

                    $modal.modal('show');
                } catch (ex) {
                    console.error('Failed to render Add Warehouse modal:', ex);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load Add Warehouse modal:', status, error);
                console.debug('Response:', xhr && xhr.responseText);
            }
        }); 
    });
</script>

<script>
    $(document).on('hidden.bs.modal', '#addWarehouseModal', function () {
        if ($('.modal.in:visible, .modal.show:visible').length) {
            $('body').addClass('modal-open');
        }
    });

    $(document).on('shown.bs.modal', '#addWarehouseModal', function () {
        $('body').addClass('modal-open');
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
//////////////////// Biller Phone Validation /////////////////////
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
        }, $country));
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
        // phoneInput.value = '';
        // errorMsg.style.display = 'none';
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
