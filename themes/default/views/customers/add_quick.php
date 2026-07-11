<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
#myModal {
    display: block;
    overflow: scroll;
}

// body{overflow: hidden !important;}
.modal.fade {
    -webkit-transition: opacity .3s linear, top .3s ease-out;
    -moz-transition: opacity .3s linear, top .3s ease-out;
    -ms-transition: opacity .3s linear, top .3s ease-out;
    -o-transition: opacity .3s linear, top .3s ease-out;
    transition: opacity .3s linear, top .3s ease-out;
    top: -3%;
}

a.select2-choice {
    border-radius: 0rem !important;
}

.modal-header .btnGrp {
    position: absolute;
    top: 18px;
    right: 10px;
}

.form-group {
    margin-bottom: 10px;
}

.d-flx {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-marginright {
    margin-right: 4rem;
    padding: 0.3rem 1rem;
    box-shadow: rgba(255, 255, 255, 0.5) 2px 2px 2px 0px inset, rgba(0, 0, 0, 0.1) 7px 7px 20px 0px, rgba(0, 0, 0, 0.1) 4px 4px 5px 0px;
    outline: none;
}

.modal-header .close {
    position: relative;
    bottom: 1.5rem;
}

/* Add Quick Customer: keep Customer Group, Price Group, Is Internal Customer on one row */
    #add-customer-form .row-customer-top {
        display: flex;
        flex-wrap: nowrap;
        margin-left: -15px;
        margin-right: -15px;
    }
    #add-customer-form .row-customer-top .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding-left: 15px;
        padding-right: 15px;
    }
    #add-customer-form .row-customer-top .col-md-3 {
        flex: 0 0 25%;
        max-width: 25%;
        padding-left: 15px;
        padding-right: 15px;
    }
    @media (max-width: 768px) {
        #add-customer-form .row-customer-top {
            flex-wrap: wrap;
        }
        #add-customer-form .row-customer-top .col-md-6,
        #add-customer-form .row-customer-top .col-md-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>
<!--<div class="container" >-->
<div class="mymodal" id="modal-1" role="dialog">
    <div class="modal-dialog modal-lg add_quick">
        <div class="modal-content">
            <?php
            $country_phone_meta = [];
            foreach ($country as $c) {
                $dig = 10;
                if (!empty($c->phone_digits) && is_numeric($c->phone_digits)) {
                    $dig = (int)$c->phone_digits;
                } elseif (stripos($c->name, 'United Arab Emirates') !== false || strcasecmp($c->name, 'UAE') === 0) {
                    $dig = 9;
                }
                $country_phone_meta[] = [
                    'name' => $c->name,
                    'phone_digits' => $dig,
                    'tax_label' => !empty($c->TaxNumberLabelText) ? $c->TaxNumberLabelText : 'GSTIN (GST #)',
                    'postal_required' => (!empty($c->postal_code) && (int)$c->postal_code === 1) ? 1 : 0,
                ];
            }
            $auto_customer_number_on = !empty($auto_customer_number);
            $auto_phone_value = !empty($auto_customer_phone) ? $auto_customer_phone : '';
            $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
            if ($auto_customer_number_on) {
                $attrib['data-auto-customer-number'] = '1';
            }
            echo form_open_multipart("customers/add_quick", $attrib );
            $redirect_url = $this->input->get('redirect_url') ?: $this->input->post('redirect_url');
            $source_module = $this->input->get('source_module') ?: $this->input->post('source_module');
            ?>
            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars((string)$redirect_url, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($source_module !== '' && $source_module !== null): ?>
            <input type="hidden" name="source_module" value="<?= htmlspecialchars((string)$source_module, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <textarea id="add-customer-country-meta-json" readonly tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;overflow:hidden;"><?= htmlspecialchars(json_encode($country_phone_meta), ENT_QUOTES, 'UTF-8'); ?></textarea>

            <div class="modal-header">
                <div class="d-flx">
                    <h4 class="modal-title" id="myModalLabel">Quick <?php echo lang('add_customer'); ?></h4>
                    <!-- <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary custom-marginright"'); ?> -->
                    <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary custom-marginright" id="add_customer"'); ?>
                </div>
                <div class="">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                            class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("phone", "phone"); ?>
                            <?php if ($auto_customer_number_on): ?>
                            <input type="hidden" name="auto_phone_original" id="auto_phone_original" value="<?= htmlspecialchars($auto_phone_value, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="is_system_generated" id="is_system_generated" value="TRUE">
                            <input type="tel" name="phone" class="form-control" id="phone" required="required"
                                value="<?= htmlspecialchars($auto_phone_value, ENT_QUOTES, 'UTF-8'); ?>"
                                maxlength="10" autocomplete="off"
                                onkeypress="return IsNumeric(event,this)" ondrop="return false" onpaste="return false">
                            <?php else: ?>
                            <input type="tel" name="phone" class="form-control" id="phone" required="required"
                                onkeypress="return IsNumeric(event,this)" ondrop="return false" onpaste="return false">
                            <?php endif; ?>
                            <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                            <span id="phone_error" style="color:#a94442; display: none;font-size:11px; margin-top:4px;"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group person">
                            <?= lang("name", "name"); ?>
                            <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" ondrop="return false;" onpaste="return false;"'); ?>
                            <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets
                                only</span>
                        </div>
                    </div>
                </div>
                <div class="row row-customer-top">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                            <?php
                            $cgs = array();
                            $select_cgs = null;
                            foreach ($customer_groups as $customer_group) {
                                $optval = $customer_group->id .'~'.$customer_group->name;
                                $cgs[$optval] = $customer_group->name;
                                if ($Settings->customer_group == $customer_group->id) {
                                    $select_cgs = $optval;
                                }
                            }
                            echo form_dropdown('customer_group', $cgs, $select_cgs, 'id="customer_group" data-placeholder="' . lang("customer_group") . '" class="form-control input-tip select" style="width:100%;height:30px;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label"
                                for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                            <?php
                            $pgs = array('' => lang('select').' '.lang('price_group'));
                            $select_pg = null;
                            foreach ($price_groups as $price_group) {
                                $pgoptval = $price_group->id .'~'.$price_group->name;
                                $pgs[$pgoptval] = $price_group->name;
                                if ($Settings->price_group == $price_group->id) {
                                    $select_pg = $pgoptval;
                                }
                            }
                            echo form_dropdown('price_group', $pgs, $select_pg, 'id="price_group" data-placeholder="' . lang("price_group") . '" class="form-control input-tip select" style="width:100%;height:30px;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="is_internal_customer"><?php echo lang('Is_Internal_Customer'); ?></label>
                            <?php
                            $internal_options = array('no' => 'No', 'yes' => 'Yes');
                            echo form_dropdown('is_internal_customer', $internal_options, 'no', 'class="form-control select" id="is_internal_customer" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                </div>
                <!-- Additional Fields Section -->
                <div id="more-details">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group company">
                                <?= lang("company", "company"); ?>
                                <?php echo form_input('company', '', 'class="form-control tip" id="company"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gstn_no" id="gstn_label"><?= lang("gstn_no", "gstn_no"); ?></label>
                                <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no" onchange="return validateGstin();"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Pan Card", "Pan Card"); ?>
                                <input type="text" name="pan_card" id="pancard" class="form-control" />
                                <small class="text-danger" id="errpancard"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("email_address", "email_address"); ?>
                                <input type="email" name="email" class="form-control" id="email_address" title="Please enter a valid email address" />
                                <span id="email_error" style="color:#a94442; display: none;font-size:11px;">Please enter a valid email address</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("address", "address"); ?>
                                <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                            </div>
                        </div>
                        <!-- <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("country", "country"); ?>
                                <?php echo form_input('country', 'India', 'class="form-control" id="country"'); ?>
                            </div>
                        </div> -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("country", "country"); ?>
                                <?php
                                $ct[""] = "";
                                foreach ($country as $country_value) {
                                    $ct[$country_value->name] = $country_value->name;
                                }
                                $ct['other'] = 'Other';
                                echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $biller->country), 'id="country"  data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                                ?>      
                            </div>
                        </div>
                        <!-- <div class="col-md-3" style="width:23.33% !important;">
                            <div class="form-group" id="div_country_name">
                                    <?= lang("Country Name", "country"); ?>
                                <input type="text" name="add_country" placeholder="Country Name"  value="<?=$biller->country?>" id="add_country" readonly="readonly" class="form-control" />
                                <span id="errora2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                            </div>
                        </div> -->

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("state", "state"); ?>
                                <?php
                                $st[''] = '--Select State--';
                                foreach ($states as $state) {                                         
                                    $st[$state->name .'~'. $state->code] = $state->name . " (".$state->code.")";
                                }
                                $st['other'] = 'Other';
                                echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : $biller->state.'~'.$biller->state_code), 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select"    style="width:100%;height:30px;"');
                                ?>                                    
                            </div>
                        </div>
                        <!-- <div class="col-md-3" style="width:23.33% !important;">
                            <div class="form-group" id="div_statename" >
                                <?= lang("State Name", "state"); ?>
                                <input type="text" name="statename" placeholder="State Name" value="<?=$biller->state?>" id="statename" class="form-control"  readonly="readonly" />
                                <small class="text-danger" id="errstatename"></small>
                            </div>
                        </div> -->
                        <div class="col-md-6">
                            <div class="form-group" id="div_statecode" >
                                <?= lang("State Code", "state"); ?>
                                <input type="text" name="state_code" placeholder="State Code" value="<?=$biller->state_code?>" id="state_code" class="form-control state_code" readonly="readonly" />                                    
                                <small class="text-danger" id="errstate_code"></small>
                            </div>
                        </div>  
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("city", "city"); ?>
                                <?php echo form_input('city', '', 'class="form-control" id="city"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label" for="sale_source"><?= lang("Sale_source"); ?></label>
                                <div class="controls">
                                    <?php
                                        $opt = array(0 => lang('no'),1 => lang('yes'));
                                        echo form_dropdown('sale_source', $opt, '', 'class="form-control tip noedit" id="sale_source" style="width:100%;"');
                                        ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ccf1') : lang('Members Card No', 'ccf1')) ?>
                                <?php
                            if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {
                                echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)), '', 'class="form-control tip" id="cf1"' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                            } else {
                                echo form_input('cf1', '', 'class="form-control" id="cf1" ' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                            }
                            ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ccf2') : lang('ccf2', 'ccf2')) ?>
                                <?php
                            if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {
                                echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)), '', 'class="form-control tip" id="cf2"' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                            } else {
                                echo form_input('cf2', '', 'class="form-control" id="cf2" ' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                            }
                            ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="postal_code_group">
                                <?= lang("postal_code", "postal_code"); ?>
                                <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" maxlength="6" onkeypress="return IsNumeric2(event,this)" ondrop="return false" onpaste="return false"'); ?>
                                <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter
                                    numbers only</span>
                                <span id="postal_code_error" style="color:#a94442; display:none; font-size:11px;">Please enter valid postal code</span>
                            </div>
                        </div>
                    </div>
                    <input type="checkbox" name="moreoption" id="moreoption"> <label for="moreoption">More
                        Option</label>
                    <div id="moreoption_block" style="display:none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("DOB", "dob"); ?>
                                    <?php echo form_input('dob', (isset($_POST['dob']) ? $_POST['dob'] : ""), 'class="form-control input-tip dob" id="dob" '); ?>
                                    <span id="dob_error" style="color:#a94442; display: none;font-size:11px;">Date of birth cannot be in the future</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Anniversary Date", "anniversary"); ?>
                                    <?php echo form_input('anniversary', (isset($_POST['anniversary']) ? $_POST['anniversary'] : ""), 'class="form-control input-tip anniversary" id="anniversary"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Older Child's Birthday", "dob_child1"); ?>
                                    <?php echo form_input('dob_child1', (isset($_POST['dob_child1']) ? $_POST['dob_child1'] : ""), 'class="form-control input-tip date" id="dob_child1"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Younger Child's Birthday", "dob_child2"); ?>
                                    <?php echo form_input('dob_child2', (isset($_POST['dob_child2']) ? $_POST['dob_child2'] : ""), 'class="form-control input-tip date" id="dob_child2"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Fathers Birthday", "dob_father"); ?>
                                    <?php echo form_input('dob_father', (isset($_POST['dob_father']) ? $_POST['dob_father'] : ""), 'class="form-control input-tip date" id="dob_father" '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Mothers Birthday", "dob_mother"); ?>
                                    <?php echo form_input('dob_mother', (isset($_POST['dob_mother']) ? $_POST['dob_mother'] : ""), 'class="form-control input-tip date" id="dob_mother" '); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?= $modal_js ?>

<script>
    $(document).on('focus', '.anniversary', function() {
        if (!$(this).data('datetimepicker')) {
            $(this).datetimepicker({
                format: site.dateFormats.js_sdate,
                fontAwesome: true,
                language: 'sma',
                todayBtn: 1,
                autoclose: 1,
                minView: 2,
                endDate: false
            });
            $(this).datetimepicker('show');
        }
    });

</script>
<script type="text/javascript">
$(document).ready(function(e) {
    $('.bootbox-alert').modal('hide');
    $('.close').click(function() {});
    $('#add-customer-form').bootstrapValidator({
        feedbackIcons: {
            valid: 'fa fa-check',
            invalid: 'fa fa-times',
            validating: 'fa fa-refresh'
        },
        excluded: [':disabled', '#email_address', '#phone']
    });
    $('select.select').select2({
        minimumResultsForSearch: 7
    });
    // Bind state change event after select2 initialization
    $('#state').on('change', function (event) {
        set_state($(this).val());
    });
    fields = $('.modal-content').find('.form-control');
    $.each(fields, function() {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');
        var iid = '#' + id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function() {
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
            });
        }
    });
    $('#add_customer').click(function() {
        $('#errpancard').empty();

        if ($('#add-customer-form').attr('data-auto-customer-number') === '1') {
            var manualPhone = String($('#phone').val() || '').replace(/\D+/g, '');
            $('#phone').val(manualPhone);
            if (!manualPhone) {
                $('#phone_error').text('Phone number is required.').show();
                $('#phone').focus();
                return false;
            }
            if (manualPhone.length > 10) {
                $('#phone_error').text('Phone number must be up to 10 digits only.').show();
                $('#phone').focus();
                return false;
            }
            $('#phone_error').hide();
        }

        if (!validatePostalCode()) {
            $('#add-customer-form').find('#postal_code').focus();
            return false;
        }
        
        // Email validation before submission
        var email = $('#email_address').val();
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (email !== '' && !emailPattern.test(email)) {
            $('#email_error').show();
            $('#email_address').focus();
            return false;
        } else {
            $('#email_error').hide();
        }
        
        // DOB validation before submission
        var dob = $('#dob').val();
        if (dob !== '') {
            var selectedDate = parseUiDate(dob);
            if (!selectedDate) {
                selectedDate = new Date(dob);
            }
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate > today) {
                $('#dob_error').show();
                $('#dob').focus();
                return false;
            } else {
                $('#dob_error').hide();
            }
        }
        
        var panRaw = $.trim(String($('#pancard').val() || ''));
        if (panRaw === '') {
            $('#pancard').val('');
            return true;
        }
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        if (patt.test(panRaw)) {
            return true;
        }
        $('#errpancard').html("\"<strong>" + panRaw + "</strong>\" this no. invalid, Please enter valid pancard no.");
        $('#pancard').val('');
        return false;
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
        var value = String((t && t.value) ? t.value : '');
        var isDigit = (keyCode >= 48 && keyCode <= 57);
        var ret = (isDigit || specialKeys.indexOf(keyCode) != -1);
        if (isDigit && value.length >= 6) {
            ret = false;
        }
        document.getElementById("error1").style.display = ret ? "none" : "inline";
        return ret;
    }
    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("error2").style.display = ret ? "none" : "inline";
        return ret;
    }
    $('#pancard').on('input', function() {
        var pan_card = $.trim(String($(this).val() || ''));
        if (pan_card === '') {
            $(this).val('');
            $('#errpancard').empty();
        }
    });
    $('#pancard').on('change blur', function() {
        var pan_card = $.trim(String($(this).val() || ''));
        if (pan_card === '') {
            $(this).val('');
            $('#errpancard').empty();
            return;
        }
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        if (patt.test(pan_card)) {
            $('#errpancard').empty();
        } else {
            $('#errpancard').html("\"<strong>" + pan_card + "</strong>\" this no. invalid, Please enter valid pancard no.");
        }
    });
    
    // Email validation
    $('#email_address').on('input blur', function() {
        var email = $(this).val();
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (email === '') {
            $('#email_error').hide();
            return true;
        }
        
        if (emailPattern.test(email)) {
            $('#email_error').hide();
            return true;
        } else {
            $('#email_error').show();
            return false;
        }
    });

    function validatePostalCode() {
        var $form = $('#add-customer-form');
        var $postal = $form.find('#postal_code');
        var $postalGroup = $form.find('#postal_code_group');
        var $postalError = $form.find('#postal_code_error');
        if (!$postal.length || $postal.is(':disabled') || !$postalGroup.is(':visible')) {
            $postalError.hide();
            return true;
        }
        var postalCode = $.trim(String($postal.val() || ''));
        if (postalCode !== '' && postalCode.length < 6) {
            $postalError.show();
            return false;
        }
        $postalError.hide();
        return true;
    }

    function parseUiDate(dateStr) {
        var raw = $.trim(String(dateStr || ''));
        if (!raw) {
            return null;
        }
        var parts = raw.match(/\d+/g);
        if (!parts || parts.length < 3) {
            return null;
        }
        var format = ((site && site.dateFormats && site.dateFormats.js_sdate) ? site.dateFormats.js_sdate : 'dd/mm/yyyy').toLowerCase();
        var tokenOrder = [
            { key: 'd', idx: format.indexOf('d') },
            { key: 'm', idx: format.indexOf('m') },
            { key: 'y', idx: format.indexOf('y') }
        ].sort(function(a, b) {
            if (a.idx === -1 && b.idx === -1) return 0;
            if (a.idx === -1) return 1;
            if (b.idx === -1) return -1;
            return a.idx - b.idx;
        });
        if (tokenOrder[0].idx === -1 || tokenOrder[1].idx === -1 || tokenOrder[2].idx === -1) {
            tokenOrder = [{ key: 'd' }, { key: 'm' }, { key: 'y' }];
        }
        var mapped = { d: null, m: null, y: null };
        for (var i = 0; i < 3; i++) {
            mapped[tokenOrder[i].key] = parseInt(parts[i], 10);
        }
        var day = mapped.d, month = mapped.m, year = mapped.y;
        if (!day || !month || !year) {
            return null;
        }
        if (year < 100) {
            year += 2000;
        }
        var parsed = new Date(year, month - 1, day);
        if (parsed.getFullYear() !== year || parsed.getMonth() !== (month - 1) || parsed.getDate() !== day) {
            return null;
        }
        return parsed;
    }

    $('#add-customer-form').on('input blur', '#postal_code', function() {
        validatePostalCode();
    });
    
    $(document).on('focus', '#dob', function() {
        if (!$(this).data('datetimepicker')) {
            $(this).datetimepicker({
                format: site.dateFormats.js_sdate,
                fontAwesome: true,
                language: 'sma',
                todayBtn: 1,
                autoclose: 1,
                minView: 2,
                endDate: new Date()
            });
            $(this).datetimepicker('show');
        }
    });
    
    $('#dob').on('change blur', function() {
        var dob = $(this).val();
        if (dob === '') {
            $('#dob_error').hide();
            return true;
        }
        
        var selectedDate = parseUiDate(dob);
        if (!selectedDate) {
            selectedDate = new Date(dob);
        }
        var today = new Date();
        today.setHours(0, 0, 0, 0); // Set time to start of day for accurate comparison
        
        if (selectedDate > today) {
            $('#dob_error').show();
            $(this).val(''); // Clear the invalid date
            return false;
        } else {
            $('#dob_error').hide();
            return true;
        }
    });
    $("#moreoption").on("click", function() {

        if ($(this).prop('checked')) {
            $('#moreoption_block').show();
        } else {
            $('#moreoption_block').hide();
        }
    });
    function getCountryMetaData() {
        var raw = $.trim(String($('#add-customer-country-meta-json').val() || ''));
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function getSelectedCountryName() {
        var v = '';
        try {
            if ($('#country').data('select2')) {
                v = $('#country').select2('val');
            }
        } catch (e) {}
        if (!v) {
            v = $('#country').val();
        }
        return $.trim(String(v || ''));
    }

    function normalizeCountryToken(countryName) {
        var v = $.trim(String(countryName || '').replace(/\s+/g, ' '));
        var dup = v.match(/^(.+)\s+\1$/i);
        if (dup && dup[1]) {
            v = $.trim(String(dup[1]));
        }
        return v.toLowerCase().replace(/[^a-z0-9]/g, '');
    }

    function findCountryMeta(countryData, countryName) {
        if (!countryData || !countryName) return null;
        var wantRaw = $.trim(String(countryName || ''));
        if (!wantRaw) return null;
        var wantToken = normalizeCountryToken(wantRaw);
        var aliases = { uae: 'unitedarabemirates', unitedarabemirates: 'uae' };
        var i;
        for (i = 0; i < countryData.length; i++) {
            if ($.trim(String(countryData[i].name || '')) === wantRaw) return countryData[i];
        }
        for (i = 0; i < countryData.length; i++) {
            if (normalizeCountryToken(countryData[i].name) === wantToken) return countryData[i];
        }
        if (aliases[wantToken]) {
            for (i = 0; i < countryData.length; i++) {
                if (normalizeCountryToken(countryData[i].name) === aliases[wantToken]) return countryData[i];
            }
        }
        return null;
    }

    function syncPostalCodeField(countryNameOverride) {
        var countryData = getCountryMetaData();
        if (!countryData) return;
        var countryName = (countryNameOverride !== undefined && countryNameOverride !== null) ? countryNameOverride : getSelectedCountryName();
        var found = findCountryMeta(countryData, countryName);
        var required = !!(found && parseInt(found.postal_required, 10) === 1);
        var $postal = $('#postal_code');
        var $postalGroup = $('#postal_code_group');
        if (!$postal.length || !$postalGroup.length) return;
        if (required) {
            $postalGroup.show();
            $postal.prop('disabled', false);
            $postal.prop('required', true);
        } else {
            $postal.val('');
            $postal.prop('required', false);
            $postal.prop('disabled', true);
            $postalGroup.hide();
            var bv = $('#add-customer-form').data('bootstrapValidator');
            if (bv && typeof bv.resetField === 'function') {
                bv.resetField('postal_code', true);
            }
        }
    }

    $('#country').change(function (event) {
    
        if ($(this).val() == 'other') {
            $('#add_country').attr('readonly', false);
            $('#add_country').val('');
            $('#state').html('<option value="other" selected="selected" >Other</option>');
            $('.state_code').attr('readonly', false);
            $('#statename').attr('readonly', false);
            setTimeout(function(){
                $("#state").select2("val", "other");                
                $('.state_code').val('');
                $('#statename').val('');
                syncPostalCodeField('other');
            }, 100);
            
        } else {
            $('#add_country').attr('readonly', true);
            $('#add_country').val($(this).val());
        // $('#state').html('<option value="">--Select State--</option>');
            get_state($(this).val());
            
            setTimeout(function(){
                $("#state").select2("val", "");                
                $('.state_code').val('');
                $('#statename').val('');
                syncPostalCodeField(getSelectedCountryName());
            }, 100);
            
        }
    });

    // Initial country-wise postal code requirement sync on modal load.
    syncPostalCodeField(getSelectedCountryName());

});

function set_state(state){
    
    if (state == 'other' || state == '') { 
        $('.state_code').attr('readonly', false);
        $('#statename').attr('readonly', false);
        
        $('.state_code').val('');
        $('#statename').val('');
    } else { 
        let str = state;
        const myArr = str.split('~');
        $('.state_code').val(myArr[1]);
        $('#statename').val(myArr[0]);
        
        $('.state_code').attr('readonly', true);
        $('#statename').attr('readonly', true);
    }
}
   
function get_state(country) {       
    var currentState = $('#state').val();
    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'get',
        url: '<?= base_url('customers/getstates') ?>',
        data: { 'country' : country },
        success: function (response) {              
            if (response.status == 'success') {
                $('#state').html(response.data);                      
            } else {
                $('#state').html(response.data);                     
            }
            if (currentState) {
                $('#state').val(currentState).trigger('change.select2');
            }
        }
    });
}
function validateGstin(el) {
    var input = el && el.nodeName && (el.nodeName === 'INPUT' || el.nodeName === 'TEXTAREA') ? el : null;
    if (!input) {
        input = document.querySelector('#add-customer-form #gstn_no') || document.getElementById('gstn_no');
    }
    if (!input) {
        return true;
    }
    var len = String(input.value || '').trim().length;
    if (len === 0) {
        input.value = '';
        return true;
    }
    if (len !== 15) {
        var taxLabel = String($('#gstn_label').text() || 'GSTN').replace(/\*/g, '').trim();
        alert(taxLabel + ' length must be 15 character long ');
        input.value = '';
        input.focus();
        return false;
    }
    return true;
}
(function() {
    const addForm = document.getElementById('add-customer-form');
    const phoneInput = document.getElementById('phone');
    const errorMsg = document.getElementById('phone_error');
    const isAutoCustomerNumber = addForm && addForm.getAttribute('data-auto-customer-number') === '1';

    if (!phoneInput) return;

    if (isAutoCustomerNumber) {
        phoneInput.setAttribute('maxlength', '10');
    }

    const countrySelect = document.getElementById('country');
    if (!countrySelect) return;

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
            return 10;
        }
        return found.phone_digits;
    }

    function isUnchangedAutoPhone() {
        if (!isAutoCustomerNumber) {
            return false;
        }
        var original = String(document.getElementById('auto_phone_original').value || '').replace(/\D+/g, '');
        var current = String(phoneInput.value || '').replace(/\D+/g, '');
        return original !== '' && current === original;
    }

    function syncSystemGeneratedFlag() {
        if (!isAutoCustomerNumber) {
            return;
        }
        var original = String(document.getElementById('auto_phone_original').value || '').replace(/\D+/g, '');
        var current = String(phoneInput.value || '').replace(/\D+/g, '');
        var flagEl = document.getElementById('is_system_generated');
        if (flagEl) {
            flagEl.value = (original !== '' && current === original) ? 'TRUE' : 'FALSE';
        }
    }

    function validatePhone() {
        if (isUnchangedAutoPhone()) {
            errorMsg.style.display = 'none';
            return true;
        }

        const phone = phoneInput.value.trim();
        const countryName = $('#country').val();
        const requiredLength = getRequiredLength(countryName);
        const isValidDigits = /^[1-9][0-9]*$/.test(phone);

        if (phone === '') {
            errorMsg.style.display = 'none';
            return true;
        }

        if (!isValidDigits) {
            errorMsg.textContent = 'Phone number must contain only digits and should not start with 0.';
            errorMsg.style.display = 'block';
            return false;
        }

        if (phone.length !== requiredLength) {
            errorMsg.textContent = 'Phone number must be ' + requiredLength + ' digits.';
            errorMsg.style.display = 'block';
            return false;
        }

        errorMsg.style.display = 'none';
        return true;
    }

    function maybeCheckDuplicatePhone() {
        if (isUnchangedAutoPhone()) {
            return;
        }
        const countryName = $('#country').val();
        const requiredLength = getRequiredLength(countryName);
        const phone = String(phoneInput.value || '').replace(/\D+/g, '');
        if (phone.length === requiredLength) {
            checkmobileno('customer', phone, 'error', 'phone');
        }
    }

    $(phoneInput).on('input', function() {
        syncSystemGeneratedFlag();
        validatePhone();
        maybeCheckDuplicatePhone();
    });

    $(phoneInput).on('keypress', function(e){
        const countryName = $('#country').val();
        const requiredLength = getRequiredLength(countryName);
        if (this.value.length >= requiredLength) {
            e.preventDefault();
        }
    });

    $('#country').on('change', function () {
        syncSystemGeneratedFlag();
        validatePhone();
        maybeCheckDuplicatePhone();
    });

    $('#add-customer-form').on('submit', function (e) {
        syncSystemGeneratedFlag();
        if (!validatePhone()) {
            e.preventDefault();
            phoneInput.focus();
            return false;
        }
    });

    syncSystemGeneratedFlag();
})();

function checkmobileno(groupname, mobileno, errorshow, thisid) {
    var $phone = $('#myModal #add-customer-form #' + thisid);
    if (!$phone.length) {
        $phone = $('#add-customer-form #' + thisid);
    }
    if (!$phone.length) {
        $phone = $('#' + thisid);
    }

    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'get',
        url: '<?= base_url() ?>customers/checkMobileno',
        data: {
            'groupname': groupname,
            'mobileno': mobileno
        },
        success: function(response) {
            if (response.status == 'success') {
                $phone.val('');
                $phone.focus();
                alert('Phone no already exists');
            }
        }
    });
}

(function() {
    const countryData = <?php
        $cdata = [];
        foreach ($country as $c) {
            $cdata[] = [
                'name' => $c->name,
                'tax_label' => !empty($c->TaxNumberLabelText) ? $c->TaxNumberLabelText : 'GSTIN (GST #)'
            ];
        }
        echo json_encode($cdata);
    ?>;

    function updateTaxLabel() {
        const countryName = $('#country').val();
        const found = countryData.find(c => c.name === countryName);
        if (found) {
            $('#gstn_label').text(found.tax_label);
        } else {
            $('#gstn_label').text('GSTIN (GST #)');
        }
    }

    $(document).ready(function() {
        $('#country').on('change', updateTaxLabel);
        // Initial set based on current selection
        setTimeout(updateTaxLabel, 200); 
    });
})();

</script>