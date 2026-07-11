<?php defined('BASEPATH') OR exit('No direct script access allowed');
 $lat = ($biller->lat)==0?'':$biller->lat;
 $lng = ($biller->lng)==0?'':$biller->lng;
 
 $loc ='';
 if(!empty($lat) && !empty($lng)):
 	 $loc = $lat.','.$lng;
 endif;
 
 $lat1 = ($biller->lat)==0?'23.2599':$biller->lat;
 $lng1 = ($biller->lng)==0?'77.4126':$biller->lng;
 
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
</style>
<div class="container" style="margin-top:150px;">				
<!--<div class="mymodal" id="modal-1" role="dailog">-->
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_biller'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add_biller-form');
        echo form_open_multipart("billers/edit/" . $biller->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("logo", "biller_logo"); ?>
                        <?php
                        $biller_logos[''] = '';
                        foreach ($logos as $key => $value) {
                            $biller_logos[$value] = $value;
                        }
                        echo form_dropdown('logo', $biller_logos, $biller->logo, 'class="form-control select" id="biller_logo"'); ?>
                    </div>
                    <div id="logo-con" class="text-center"><img
                            src="<?= base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo) ?>" alt=""></div>
                </div>

                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang("company", "company"); ?> 
                        <?php echo form_input('company', $biller->company, 'class="form-control tip" id="company"  required="required"'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>  
                        <?php echo form_input('name', $biller->name, 'class="form-control tip" id="name"  data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" type="text" required="required"'); ?>
                         <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', $biller->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
                      <!-- <div class="form-group">
                        <?= lang("gstn_no", "gstn_no"); ?>
                        <?php echo form_input('gstn_no', $biller->gstn_no, 'class="form-control" id="gstn_no"  onchange="return validateGstin();"'); ?>
                  
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
                            <label for="gstn_no"><?= $tax_label; ?></label>
                            <?php echo form_input('gstn_no', $biller->gstn_no, 'class="form-control" id="gstn_no"  onchange="return validateGstinss();"'); ?>
                        <?php endif; ?>
                    <div class="form-group">
                        <?= lang("Pan_Number", "pan_card"); ?>
                        <?php echo form_input('pan_card', (isset($_POST['pan_card']) ? $_POST['pan_card'] : $biller->pan_card), 'class="form-control" id="pan_card" maxlength="10" style="text-transform:uppercase"'); ?>
                        <span id="errpancard" style="color:#a94442; display:none; font-size:11px;"></span>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php //echo form_input('contact_person', $biller->contact_person, 'class="form-control" id="contact_person"'); ?>
                </div> -->
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                       <input type="text" name="email" class="form-control" id="email_address"value="<?= $biller->email ?>" />
                        <span id="email_error" style="color:#a94442; display:none; font-size:11px;"></span>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?> 
                        <input type="tel" name="phone" class="form-control" id="phone" value="<?= $biller->phone ?>" required/>
                        <span id="phone_error" style="color:#a94442; display:none; font-size:11px;"></span>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', $biller->address, 'class="form-control" id="address"'); ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("city", "city"); ?>
                                <?php echo form_input('city', $biller->city, 'class="form-control" onkeypress="return onlyAlphabets1(event,this);" type="text" id="city"'); ?> 
                                <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                            </div>
                        </div>
                        <!-- <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("postal_code", "postal_code"); ?>
                                <?php echo form_input('postal_code', $biller->postal_code, 'class="form-control" id="postal_code" maxlength="6"  onkeypress="return IsNumeric2(event,this)" '); ?>
                                 <span id="errorn1" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
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
                                    <?php echo form_input('postal_code', $biller->postal_code, 'class="form-control" id="postal_code" maxlength="6"  onkeypress="return IsNumeric2(event,this)" style ="margin-top: -12px !important;" '); ?>
                                    <!-- <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" maxlength="6" onkeypress="return IsNumeric2(event,this)" style ="margin-top: -12px !important;" type="text"'); ?> -->
                                    <span id="errorn1" style="color:#a94442; display: none; font-size:11px;">please enter numbers only</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                              <?= lang("country", "country"); ?>
                                 <?php
                                        $ct[""] = "";
                                        foreach ($countries as $country_value) {
                                                $ct[$country_value->name] = $country_value->name;
                                        }
                                        $ct['other'] ='Other';
                                        echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $biller->country), 'id="country" required = "required" data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                                 ?>
                                 <input type="text" name="add_country" placeholder="Country " id="addnewcountry" class="form-control" style="display:none; margin-top: 10px;"/>
                          </div>
                      </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("state", "state"); ?>
                                 <?php
                                  $selected_state = str_replace('State ', '', $biller->state);
                                  $selected_state = explode('~', $selected_state)[0];
                                  $st = [];
                                  $st[''] = '';
                                  foreach ($states as $state) {
                                      $st[$state->name] = $state->name;
                                  }
                                  echo form_dropdown('state',  $st, isset($_POST['state']) ? $_POST['state'] : $selected_state,  'id="state" required="required" class="form-control input-tip select" style="width:100%; height:30px;"' );
                                  ?>
                                <input type="text" name="statecode" placeholder="State Code " id="statecode" class="form-control" style="display:none; margin-top: 10px;"/>
                                <input type="text" name="statename" placeholder="State Name " id="statename" class="form-control" style="display:none; margin-top: 10px;"/>

                                <br>
                                <?php echo $this->sma->dbSavedValue($st,$biller->state);?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">                    
                    <div class="form-group">
                        <?= lang("bcf1", "cf1"); ?>
                        <?php echo form_input('cf1', $biller->cf1, 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("Barcode Address", "Barcode Address"); ?>
                        <?php echo form_input('cf2', $biller->cf2, 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("bcf3", "cf3"); ?>
                        <?php echo form_input('cf3', $biller->cf3, 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("bcf4", "cf4"); ?>
                        <?php echo form_input('cf4', $biller->cf4, 'class="form-control" id="cf4"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("bcf5", "cf5"); ?>
                        <?php echo form_input('cf5', $biller->cf5, 'class="form-control" id="cf5"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("bcf6", "cf6"); ?>
                        <?php echo form_input('cf6', $biller->cf6, 'class="form-control" id="cf6"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("invoice_footer", "invoice_footer"); ?>
                        <?php echo form_textarea('invoice_footer', $biller->invoice_footer, 'class="form-control skip" id="invoice_footer" style="height:100px;"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("declaration", "invoice_declaration"); ?>
                        <?php echo form_textarea('invoice_declaration', $biller->invoice_declaration, 'class="form-control skip" id="invoice_declaration" style="height:100px;"'); ?>
                    </div>
                </div>
<!--                <div class="row">
                    <div class="col-md-12">
                        <hr/>
                       <div class="form-group">
                             <input id="pac-input" class="form-control" type="text" placeholder="Search Box">
                           <div id="map" style="height:200px;"></div>
                       </div>
                       <div class="form-group">
                            <div class="col-md-12">
                               <?php echo form_input('location_map',  $loc, 'class="form-control" id="location_map" placeholder="Location On Map" readonly '); ?>
                             </div>
                       </div>
                   </div>
                </div>-->
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_biller', lang('edit_biller'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<!--</div>-->
</div>
<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function (e) {
        $('#add_biller-form').bootstrapValidator({
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
</script>

<script>

      // In the following example, markers appear when the user clicks on the map.
      // The markers are stored in an array.
      // The user can then click an option to hide, show or delete the markers.
      var map;
      var markers = [];

      function initMap() {
      
        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: <?php echo  $lat1?>, lng: <?php echo  $lng1?>},
          zoom: 12,
          mapTypeId: 'terrain'
        });

        // This event listener will call addMarker() when the map is clicked.
        map.addListener('click', function(event) {
          addMarker(event.latLng);
        });
         
  // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

  searchBox.addListener('places_changed', function() {
 
    var places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    // Clear out the old markers.
    markers.forEach(function(marker) {
      marker.setMap(null);
    });
    markers = [];
 
    // For each place, get the icon, name and location.
    var bounds = new google.maps.LatLngBounds();
    places.forEach(function(place) {
      if (!place.geometry) {
        console.log("Returned place contains no geometry");
        return;
      }
        addMarker(place.geometry.location) ;

      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });
    map.fitBounds(bounds);
  });
 
         
	  <?php 
	   if(!empty($lat) && !empty($lng)):
	   ?>
        // Adds a marker at the center of the map.
		  var haightAshbury = {lat: <?php echo $lat;?>, lng: <?php echo $lng?>};
          addMarker(haightAshbury);
	  <?php  endif;
	  ?>

      }

      // Adds a marker to the map and push to the array.
      function addMarker(location) {
		console.log(location);  
		clearMarkers();
                markers = [];
		if(typeof location.lat === 'function') { 
	            $('#location_map').val(location.lat()+','+location.lng());		
		}
		
		var marker = new google.maps.Marker({
          position: location,
          map: map
        });
        markers.push(marker);
      }

      // Sets the map on all markers in the array.
      function setMapOnAll(map) {
        for (var i = 0; i < markers.length; i++) {
          markers[i].setMap(map);
        }
      }

      // Removes the markers from the map, but keeps them in the array.
      function clearMarkers() {
        setMapOnAll(null);
      }
 
      // Deletes all markers in the array by removing references to them.
      function deleteMarkers() {
        clearMarkers();
        markers = [];
      }
	 
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
    </script>
<script>
(function () {
    var panInput = document.getElementById('pan_card');
    var errorMsg = document.getElementById('errpancard');
    if (!panInput || !errorMsg) return;

    var panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;

    function validatePan() {
        panInput.value = panInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
        var pan = panInput.value.trim();

        if (pan === '') {
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
            return;
        }

        if (pan.length !== 10) {
            errorMsg.textContent = 'PAN must be exactly 10 characters.';
            errorMsg.style.display = 'block';
        } else if (!panRegex.test(pan)) {
            errorMsg.textContent = 'Enter a valid PAN (example: ABCDE1234F)';
            errorMsg.style.display = 'block';
        } else {
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
    }

    panInput.addEventListener('input', validatePan);
    panInput.addEventListener('blur', validatePan);

    setTimeout(function () {
        validatePan();
    }, 300);
})();
</script>
 <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTg4kjqcDeHxyJs4MnwL38FWi4n5lwMBQ&libraries=places&callback=initMap"
    async defer></script>

<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $('#biller_logo').change(function (event) {
            var biller_logo = $(this).val();
            $('#logo-con').html('<img src="<?=base_url('assets/mdata/'.$Customer_assets.'/uploads/logos')?>/' + biller_logo + '" alt="">');
        });
    });

    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        document.getElementById("error2").style.display = ret ? "none" : "inline";
	return ret;	
    } 
    

    function onlyAlphabets(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret= (charCode == 32 || (charCode>=97 && charCode<=122)|| (charCode>=65 && charCode<=90));
        document.getElementById("error").style.display = ret ? "none" : "inline";
	return ret;	
    }

    var specialKeys = new Array();
    specialKeys.push(8); //Backspace
    function IsNumeric2(e,t) {
	var keyCode = e.which ? e.which : e.keyCode
	var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
	document.getElementById("errorn1").style.display = ret ? "none" : "inline";
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
 
</script> 
<script>
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

            // ✅ REQUIRED CHECK FIRST
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
        $('#add_biller-form').on('submit', function (e) {

            if (!validatePhone()) {
                e.preventDefault();
                phoneInput.focus();
                return false;
            }

        });

    })();
</script>
<script>

(function(){

    const emailInput = document.getElementById('email_address');
    const errorMsg = document.getElementById('email_error');

    if (!emailInput) return;

    // Remove bootstrap validation attributes
    emailInput.removeAttribute('data-bv-emailaddress');
    emailInput.removeAttribute('data-bv-emailaddress-message');

    function validateEmail() {

        const email = emailInput.value.trim();

        if (email === '') {
            errorMsg.style.display = 'none';
            return;
        }

        // Strong email regex
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailPattern.test(email)) {
            errorMsg.textContent =
                'Enter a valid email (example: name@gmail.com)';
            errorMsg.style.display = 'block';
        } else {
            errorMsg.style.display = 'none';
        }
    }

    emailInput.addEventListener('input', validateEmail);
    emailInput.addEventListener('blur', validateEmail);

    // Validate prefilled value (Edit mode)
    setTimeout(function(){
        validateEmail();
    }, 300);

})();
</script>

<!-- Removed redundant modal_js call -->

