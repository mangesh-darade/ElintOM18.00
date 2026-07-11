<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 

<style>
    .modal.fade {
        -webkit-transition: opacity .3s linear, top .3s ease-out;
        -moz-transition: opacity .3s linear, top .3s ease-out;
        -ms-transition: opacity .3s linear, top .3s ease-out;
        -o-transition: opacity .3s linear, top .3s ease-out;
        transition: opacity .3s linear, top .3s ease-out;
        top: -3%;
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
                    <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_customer'); ?></h4>
                </div>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("customers/edit/" . $customer->id, $attrib);
                ?>
                <div class="modal-body">
                    <p><?= lang('enter_info'); ?></p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                                <?php
                                foreach ($customer_groups as $customer_group) {
                                    $cgs[$customer_group->id] = $customer_group->name;
                                }
                                echo form_dropdown('customer_group', $cgs, $customer->customer_group_id, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                                <?php
                                $pgs[''] = lang('select') . ' ' . lang('price_group');
                                foreach ($price_groups as $price_group) {
                                    $pgs[$price_group->id] = $price_group->name;
                                }
                                echo form_dropdown('price_group', $pgs, $customer->price_group_id, 'class="form-control select" id="price_group" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="is_internal_customer"><?php echo lang('Is_Internal_Customer'); ?></label>
                                <?php
                                $internal_options = array('no' => 'No', 'yes' => 'Yes');
                                $selected_internal = isset($customer->is_internal_customer) ? $customer->is_internal_customer : 'no';
                                echo form_dropdown('is_internal_customer', $internal_options, $selected_internal, 'class="form-control select" id="is_internal_customer" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group company">
                                <?= lang("company", "company"); ?> 
                                <?php echo form_input('company', $customer->company, ($customer->id==1?'readonly ' : ''). ' class="form-control tip" id="company"'); ?>
                            </div>
                            <div class="form-group person">
                                <?= lang("name", "name"); ?> *
                                <?php echo form_input('name', $customer->name,  ($customer->id==1?'readonly ' : ''). ' class="form-control tip" id="name" required="required"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang("address", "address"); ?>
                                <?php echo form_input('address', $customer->address, 'class="form-control" id="address"'); ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("vat_no", "vat_no"); ?>
                                        <?php echo form_input('vat_no', $customer->vat_no, 'class="form-control" id="vat_no"'); ?>
                                    </div>
                                </div>
                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("gstn_no", "gstn_no"); ?>
                                        <?php echo form_input('gstn_no', $customer->gstn_no, 'class="form-control" id="gstn_no"  onchange="return validateGstin();"'); ?>
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
                                                <?php echo form_input('gstn_no', $customer->gstn_no, 'class="form-control" id="gstn_no"  onchange="return validateGstin();"'); ?>
                                            </div>  
                                        </div>
                                <?php endif; ?>
                            </div>                        
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("email_address", "email_address"); ?>
                                        <input type="text" name="email" value="<?= isset($customer->email) ? $customer->email : " "; ?>" class="form-control" id="email_address" data-bv-emailaddress
                                               data-bv-onerror="onFieldError" data-bv-onsuccess="onFieldSuccess" data-bv-onstatus="onFieldStatus"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("phone", "phone"); ?> *
                                        <input type="tel" name="phone" class="form-control" <?=($customer->id==1?'readonly ' : '') ?>  id="phone" data-bv-phone="true" data-bv-phone-country="US"  onkeypress="return IsNumeric(event, this)" maxlength="10" required="required"  type="text" value="<?= $customer->phone ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("city", "city"); ?> 
                                        <?php echo form_input('city', $customer->city, 'class="form-control" id="city" onkeypress="return onlyAlphabets1(event,this);"  type="text" '); ?>
                                        <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label" for="sale_source"><?= lang("Sale_source"); ?></label>
                                        <div class="controls">
                                            <?php
                                            $opt = array(0 => lang('no'),1 => lang('yes'));
                                            echo form_dropdown('sale_source', $opt, $customer->source, 'class="form-control tip noedit" id="sale_source" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("postal_code", "postal_code"); ?>
                                        <?php echo form_input('postal_code', (isset($_POST['postal_code']) ? $_POST['postal_code'] : $customer->postal_code), 'class="form-control" id="postal_code"   maxlength="6"  onkeypress="return IsNumeric2(event,this)" type="text" id="text1" ondrop="return false" onpast="return false"'); ?>
                                        <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                                    </div>                                
                                </div>     -->
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
                                                <?php echo form_input('postal_code', $customer->postal_code, 'class="form-control" id="postal_code" maxlength="6" data-bv-stringlength="true" data-bv-stringlength-min="6" data-bv-stringlength-max="6" data-bv-digits="true" onkeypress="return IsNumeric2(event,this)" style ="margin-top: -12px !important;" type="text" ondrop="return false" onpast="return false"'); ?>
                                                <span id="error1" style="color:#a94442; display: none; font-size:11px;">please enter numbers only</span>
                                            </div>
                                        </div>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("country", "country"); ?>
                                        <?php
                                        $ct[""] = "";
                                        foreach ($country as $country_value) {
                                            $ct[$country_value->name] = $country_value->name;
                                        }
                                        $ct['other'] = 'Other';
                                        echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $customer->country), 'id="country"  data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                                        ?>                                    
                                    </div>
                                    <div class="form-group" id="div_country_name">
                                        <?= lang("Country Name", "country"); ?>
                                        <input type="text" name="add_country" placeholder="Country Name"  value="<?= $customer->country ?>" id="add_country" readonly="readonly" class="form-control" />
                                        <span id="errora2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                                    </div>
                                    <div class="form-group">
                                        <?= lang("Pan_Number", "pan_card"); ?>
                                        <?php echo form_input('pan_card', (isset($_POST['pan_card']) ? $_POST['pan_card'] : $customer->pan_card), 'class="form-control" id="pan_card" maxlength="10"'); ?>
                                        <small class="text-danger" id="errpancard"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("state", "state"); ?>
                                        <?php
                                        $st[''] = '--Select State--';
                                        foreach ($states as $state) {
                                            $st[$state->name . '~' . $state->code] = $state->name . " (" . $state->code . ")";
                                        }
                                        $st['other'] = 'Other';
                                        echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : $customer->state . '~' . $customer->state_code), 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select"   style="width:100%;height:30px;"');
                                        ?>                                    
                                    </div>
                                    <div class="form-group" id="div_statename" >
                                        <?= lang("State Name", "state"); ?>
                                        <input type="text" name="statename" placeholder="State Name" value="<?= $customer->state ?>" id="statename" class="form-control"  readonly="readonly" />
                                        <small class="text-danger" id="errstatename"></small>
                                    </div>
                                    <div class="form-group" id="div_statecode" >
                                        <?= lang("State Code", "state"); ?>
                                        <input type="text" name="state_code" placeholder="State Code" value="<?= $customer->state_code ?>" id="state_code" class="form-control"  readonly="readonly" />                                    
                                        <small class="text-danger" id="errstate_code"></small>
                                    </div>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <?= lang('award_points', 'award_points'); ?>
                                <?= form_input('award_points', set_value('award_points', $customer->award_points), 'class="form-control tip" id="award_points"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("DOB", "dob"); ?>
                                        <?php echo form_input('dob', (isset($_POST['dob']) ? $_POST['dob'] : $this->sma->hrsd($customer->dob)), 'class="form-control input-tip date" id="dob"  '); ?>
                                    </div
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Anniversary Date", "anniversary"); ?>
                                    <?php echo form_input('anniversary', (isset($_POST['anniversary']) ? $_POST['anniversary'] : $this->sma->hrsd($customer->anniversary)), 'class="form-control input-tip date" id="anniversary" '); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Older Child's Birthday", "dob_child1"); ?>
                                    <?php echo form_input('dob_child1', (isset($_POST['dob_child1']) ? $_POST['dob_child1'] : $this->sma->hrsd($customer->dob_child1)), 'class="form-control input-tip date" id="dob_child1"  '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Younger Child's Birthday", "dob_child2"); ?>
                                    <?php echo form_input('dob_child2', (isset($_POST['dob_child2']) ? $_POST['dob_child2'] : $this->sma->hrsd($customer->dob_child2)), 'class="form-control input-tip date" id="dob_child2"  '); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Fathers Birthday", "dob_father"); ?>
                                    <?php echo form_input('dob_father', (isset($_POST['dob_father']) ? $_POST['dob_father'] : $this->sma->hrsd($customer->dob_father)), 'class="form-control input-tip date" id="dob_father"  '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Mothers Birthday", "dob_mother"); ?>
                                    <?php echo form_input('dob_mother', (isset($_POST['dob_mother']) ? $_POST['dob_mother'] : $this->sma->hrsd($customer->dob_mother)), 'class="form-control input-tip date" id="dob_mother"  "'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">                         
                                    <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ccf1') : lang('Members Card No', 'ccf1')) ?>                                
                                    <?php
                                    if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {
                                        echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)), $customer->cf1, 'class="form-control tip" id="cf1"' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                    } else {
                                        echo form_input('cf1', $customer->cf1, 'class="form-control" id="cf1" ' . ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                    }
                                    ?>                        
                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ccf2') : lang('ccf2', 'ccf2')) ?> 
                                    <?php
                                    if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {
                                        echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)), $customer->cf2, 'class="form-control tip" id="cf2"' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                    } else {
                                        echo form_input('cf2', $customer->cf2, 'class="form-control" id="cf2" ' . ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                    }
                                    ?>

                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'ccf3') : lang('ccf3', 'ccf3')) ?>
                                    <?php
                                    if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {
                                        echo form_dropdown('cf3', (json_decode($custome_fields->cf3_input_options, TRUE)), $customer->cf3, 'class="form-control tip" id="cf3"' . ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                    } else {
                                        echo form_input('cf3', $customer->cf3, 'class="form-control" id="cf3" ' . ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                    }
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'ccf4') : lang('ccf4', 'ccf4')) ?>
                                    <?php
                                    if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {
                                        echo form_dropdown('cf4', (json_decode($custome_fields->cf4_input_options, TRUE)), $customer->cf4, 'class="form-control tip" id="cf4"' . ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                    } else {
                                        echo form_input('cf4', $customer->cf4, 'class="form-control" id="cf4"' . ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                    }
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'ccf5') : lang('ccf5', 'ccf5')) ?>
                                    <?php
                                    if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {
                                        echo form_dropdown('cf5', (json_decode($custome_fields->cf5_input_options, TRUE)), $customer->cf5, 'class="form-control tip" id="cf5"' . ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                    } else {
                                        echo form_input('cf5', $customer->cf5, 'class="form-control" id="cf5"' . ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                    }
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'ccf6') : lang('ccf6', 'ccf6')) ?>
                                    <?php
                                    if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {
                                        echo form_dropdown('cf6', (json_decode($custome_fields->cf6_input_options, TRUE)), $customer->cf6, 'class="form-control tip" id="cf6"' . ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                    } else {
                                        echo form_input('cf6', $customer->cf6, 'class="form-control" id="cf6"' . ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                    }
                                    ?>
                                </div>

                                <div class="form-group">
                                    <?= lang("Reset E-Shop Password", "eshop_pass"); ?>
                                    <input type="text" id="eshop_pass"  name="eshop_pass" class="form-control" placeholder="leave blank if you don’t want to change it">
                                </div>
                            </div>
                        </div>
                    </div>
   
                    <div class="container">
                         <?php if($Settings->synced_data_sales){ 
                             
                             ?>
                        
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label for="customer_url"> Customer URL </label>
                                        <input type="text" name="customer_url" value="<?= $customer->customer_url ?>" id="customer_url" class="form-control" placeholder="Customer URL" />
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="synced_data"> Synch Data </label>
                                        <?php
                                            $syncedSalesData[0] = 'No';
                                            $syncedSalesData[1] = 'Yes';
                                            echo form_dropdown('synced_data', $syncedSalesData, $customer->synced_data, 'class="form-control tip "  id="synced_data" style="width:100%;"');
                                       ?>

                                    </div>

                                      <div class="col-sm-6">
                                        <label for="privatekey"> Private Key</label>
                                        <input type="text" name="privatekey"  value="<?= $customer->privatekey ?>" id="privatekey" class="form-control" placeholder="Private Key" />
                                    </div>
                                </div>
                      <?php } ?>
                    </div>
                </div>
                <div class="modal-footer">
            <?php echo form_submit('edit_customer', lang('edit_customer'), 'class="btn btn-primary"'); ?>
                </div>
            </div>
<?php echo form_close(); ?>
        </div>
        <!--</div>-->
    </div>
<?= $modal_js ?>

    <script type="text/javascript">
        $(document).ready(function (e) {
            $('#add-customer-form').bootstrapValidator({
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
            $('.form-control').attr('autocomplete', 'off');
        });

        $("#email_address").focusout(function () {
            var email = $("#email_address").val();

            if (email != '') {
                $.ajax({
                    type: "get",
                    url: '<?php echo base_url(); ?>customers/getEmail',
                    data: {emailid: email},
                    success: function (response) {
                        if (response == 0) {
                            alert('Email already exists');
                            $("#email_address").val('');
                            $("#email_address").focus();
                        }

                    },
                });
            }

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
            document.getElementById("error2").style.display = ret ? "none" : "inline";
            return ret;
        }

        function onlyAlphabets(e, t) {
            var charCode = e.which ? e.which : e.keyCode
            var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
            document.getElementById("errora2").style.display = ret ? "none" : "inline";
            return ret;
        }


        $('#pan_card').change(function () {
            $('#errpancard').html(" ");
            var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
            var pan_card = $(this).val();
            if (patt.test(pan_card)) {
                $('#errpancard').html("");
            } else {
                if (pan_card != '') {
                    $('#errpancard').html("\"<strong>" + pan_card + "</strong>\" this no. invalid, Please enter valid pancard no.");
                    $(this).val("");
                }
            }
        });
 
        

        $('#country').change(function (event) {

            if ($(this).val() == 'other') {
                $('#add_country').attr('readonly', false);
                $('#add_country').val('');
                $('#state').html('<option value="other" selected="selected" >Other</option>');
                $('#state_code').attr('readonly', false);
                $('#statename').attr('readonly', false);
                setTimeout(function () {
                    $("#state").select2("val", "other");
                    $('#state_code').val('');
                    $('#statename').val('');
                }, 100);

            } else {
                $('#add_country').attr('readonly', true);
                $('#add_country').val($(this).val());
                // $('#state').html('<option value="">--Select State--</option>');
                get_state($(this).val());

                setTimeout(function () {
                    $("#state").select2("val", "");
                    $('#state_code').val('');
                    $('#statename').val('');
                }, 100);

            }
        });

        $('#state').change(function (event) {
            set_state($(this).val());
        });

        function set_state(state) {

            if (state == 'other' || state == '') {
                $('#state_code').attr('readonly', false);
                $('#statename').attr('readonly', false);

                $('#state_code').val('');
                $('#statename').val('');
            } else {
                let str = state;
                const myArr = str.split('~');
                $('#state_code').val(myArr[1]);
                $('#statename').val(myArr[0]);

                $('#state_code').attr('readonly', true);
                $('#statename').attr('readonly', true);
            }
        }

        function get_state(country) {
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                method: 'get',
                url: '<?= base_url('customers/getstates') ?>',
                data: {'country': country},
                success: function (response) {
                    if (response.status == 'success') {
                        $('#state').html(response.data);
                    } else {
                        $('#state').html(response.data);
                    }
                }
            });
        }

        setTimeout(function () {             
            get_state($('#country').val());
        }, 100);
        
        
    </script> 
    <script>
        var taxLabel = <?= json_encode($tax_label); ?>;

        function validateGstin() {
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

