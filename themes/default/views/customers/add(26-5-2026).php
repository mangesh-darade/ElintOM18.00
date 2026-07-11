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
        top:18px;
        right: 10px;
    } 
    .form-control {
        height: 31px;
    }
    /* Keep customer modal grid aligned even when POS theme overrides .row/.col globally. */
    #add-customer-form .row {
        margin-left: -15px !important;
        margin-right: -15px !important;
    }
    #add-customer-form [class*="col-"] {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
</style>
<div class="container" >				
    <!--<div class="mymodal" id="modal-1" role="dailog">-->
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
            </div>
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
            $attrib = [
                'data-toggle' => 'validator',
                'role' => 'form',
                'id' => 'add-customer-form',
            ];
            if ($auto_customer_number_on) {
                $attrib['data-auto-customer-number'] = '1';
            }
            echo form_open_multipart("customers/add", $attrib);
            $redirect_url = $this->input->post('redirect_url') ?: $this->input->get('redirect_url');
            $selected_country_name = isset($_POST['country']) ? $_POST['country'] : $biller->country;
            $initial_tax_label = 'GSTIN (GST #)';
            if (!empty($country) && !empty($selected_country_name)) {
                foreach ($country as $country_row) {
                    if (isset($country_row->name) && trim((string)$country_row->name) === trim((string)$selected_country_name)) {
                        if (!empty($country_row->TaxNumberLabelText)) {
                            $initial_tax_label = $country_row->TaxNumberLabelText;
                        }
                        break;
                    }
                }
            }
            ?>
            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars((string)$redirect_url, ENT_QUOTES, 'UTF-8'); ?>">
            <textarea id="add-customer-country-meta-json" readonly tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;overflow:hidden;"><?= htmlspecialchars(json_encode($country_phone_meta), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group person">
                            <?= lang("Name*", "Name"); ?>
                            <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" type="text" type="text" id="text1" ondrop="return false;" onpaste="return false;"'); ?>
                            <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                    </div>
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
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                            <?php
                            foreach ($customer_groups as $customer_group) {
                                $cgs[$customer_group->id] = $customer_group->name;
                            }
                            echo form_dropdown('customer_group', $cgs, $Settings->customer_group, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                            <?php
                            $pgs[''] = lang('select') . ' ' . lang('price_group');
                            foreach ($price_groups as $price_group) {
                                $pgs[$price_group->id] = $price_group->name;
                            }
                            echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control select" id="price_group" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group company">
                            <?= lang("company", "company"); ?> 
                        <?php echo form_input('company', '', 'class="form-control tip" id="company"'); ?>

                        </div>
                        <div class="form-group">
                            <?= lang("address", "address"); ?>
                            <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
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
                                    <?= lang("gstn_no", "gstn_no"); ?> (GST Number)
                                        <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no" minlength="15" maxlength="15"  onchange="return validateGstin();"'); ?>
                                </div>
                            </div> -->
                            <div class="col-md-6 gstin_div">
                                <div class="form-group">
                                    <label for="gstn_no" id="gstn_label"><?= htmlspecialchars((string)$initial_tax_label, ENT_QUOTES, 'UTF-8'); ?></label>
                                    <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no" onchange="return validateGstinno();"'); ?>
                                </div>
                            </div>
                        </div>                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("email_address", "email_address"); ?>
                                    <input type="email" name="email" class="form-control" id="email_address" title="<?= htmlspecialchars(lang('email_address'), ENT_QUOTES, 'UTF-8'); ?>" />
                                    <span id="email_error" style="color:#a94442; display: none;font-size:11px;">Please enter a valid email address</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Pan_Number", "pan_card"); ?>
                                    <?php echo form_input('pan_card', (isset($_POST['pan_card']) ? $_POST['pan_card'] : ''), 'class="form-control" id="pan_card" maxlength="10"'); ?>
                                    <small class="text-danger" id="errpancard"></small>
                                </div>
                            </div>    
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("City_Name", "city"); ?>
                                    <?php echo form_input('city', '', 'class="form-control" id="city" onkeypress="return onlyAlphabets1(event,this);"   type="text"'); ?>
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
                                    // echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $biller->country), 'id="country"  data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                                    // echo form_dropdown('country', $ct, '', 'id="country"  data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                                    echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $biller->country), 'id="country"  data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
                                    ?>                                    
                                </div>
                                <!-- <div class="form-group" id="div_country_name">
                                     <?= lang("Country Name", "country"); ?>
                                    <input type="text" name="add_country" placeholder="Country Name"  value="<?=$biller->country?>" id="add_country" readonly="readonly" class="form-control" />
                                    <span id="errora2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                                </div> -->
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("state", "state"); ?>
                                    <?php
                                    $st[''] = '--Select State--';
                                    foreach ($states as $state) {                                         
                                        $st[$state->name .'~'. $state->code] = $state->name . " (".$state->code.")";
                                    }
                                    $st['other'] = 'Other';
                                    // echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : $biller->state.'~'.$biller->state_code), 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select"    style="width:100%;height:30px;"');
                                    // echo form_dropdown('state', $st, '', 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select"    style="width:100%;height:30px;"');
                                    echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : $biller->state.'~'.$biller->state_code), 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select"    style="width:100%;height:30px;"');
                                    ?>                                    
                                </div>
                                <!-- <div class="form-group" id="div_statename" >
                                    <?= lang("State Name", "state"); ?>
                                    <input type="text" name="statename" placeholder="State Name" value="<?=$biller->state?>" id="statename" class="form-control"  readonly="readonly" />
                                    <small class="text-danger" id="errstatename"></small>
                                </div> -->
                            </div>                      
                        </div>
                        <div class="row">
                            <div class="col-md-6">  
                                <div class="form-group" id="div_statecode" >
                                    <?= lang("State Code", "state"); ?>
                                    <input type="text" name="state_code" placeholder="State Code" value="<?=$biller->state_code?>" id="state_code" class="form-control" readonly="readonly" />                                    
                                    <small class="text-danger" id="errstate_code"></small>
                                </div>
                            </div> 
                            <div class="col-md-6">
                                <div class="form-group" id="postal_code_group">
                                    <?= lang("postal_code", "postal_code"); ?>
                                    <?php echo form_input('postal_code', (isset($_POST['postal_code']) ? $_POST['postal_code'] : ''), 'class="form-control" id="postal_code" maxlength="6"  onkeypress="return IsNumeric2(event,this)" type="text" id="text1" ondrop="return false" onpast="return false"'); ?>
                                    <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                                    <span id="postal_code_error" style="color:#a94442; display:none; font-size:11px;">Please enter valid postal code</span>
                                </div>                                
                            </div>  
                        </div>
                        
                        <div class="form-group hidden">
                            <?= lang('award_points', 'award_points'); ?>
                            <?= form_input('award_points', 0, 'class="form-control tip" id="award_points"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("DOB", "dob"); ?>
                                    <?php echo form_input('dob', (isset($_POST['dob']) ? $_POST['dob'] : ""), 'class="form-control input-tip dob" id="dob" '); ?>
                                <span id="dob_error" style="color:#a94442; display: none; font-size:11px;">Date of Birth cannot be in the future</span>
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
                    
                    <div class="form-group">                         
                        <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ccf1') : lang('Members Card No', 'ccf1')) ?>                                
                        <?php                        
                        if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {                            
                            echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)) , '', 'class="form-control tip" id="cf1"'. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf1', '', 'class="form-control" id="cf1" '. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); 
                        }
                        ?>                        
                    </div>
                    <div class="form-group">
                        <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ccf2') : lang('ccf2', 'ccf2')) ?> 
                       <?php                        
                        if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {                            
                            echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)) , '', 'class="form-control tip" id="cf2"'. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf2', '', 'class="form-control" id="cf2" '. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                        }  ?>

                    </div>
                    <div class="form-group">
                        <?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'ccf3') : lang('ccf3', 'ccf3')) ?>
                        <?php                        
                        if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {                            
                            echo form_dropdown('cf3', (json_decode($custome_fields->cf3_input_options, TRUE)) , '', 'class="form-control tip" id="cf3"'. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf3', '', 'class="form-control" id="cf3" '. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); 
                        }    
                        ?>
                    </div>
                    <div class="form-group">
                        <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'ccf4') : lang('ccf4', 'ccf4')) ?>
                        <?php                        
                        if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {                            
                            echo form_dropdown('cf4', (json_decode($custome_fields->cf4_input_options, TRUE)) , '', 'class="form-control tip" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf4', '', 'class="form-control" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); 
                        }   
                        ?>
                    </div>
                    <div class="form-group">
                        <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'ccf5') : lang('ccf5', 'ccf5')) ?>
                        <?php                        
                        if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {                            
                            echo form_dropdown('cf5', (json_decode($custome_fields->cf5_input_options, TRUE)) , '', 'class="form-control tip" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf5', '', 'class="form-control" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); 
                        }    
                        ?>
                    </div>
                    <!-- <div class="form-group">
                        <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'ccf6') : lang('ccf6', 'ccf6')) ?>
                        <?php                        
                        if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {                            
                            echo form_dropdown('cf6', (json_decode($custome_fields->cf6_input_options, TRUE)) , '', 'class="form-control tip" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                        } else {
                            echo form_input('cf6', '', 'class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); 
                        }   
                        ?>
                    </div> -->
                    <?php /*
                    <div class="form-group">
                        <label class="control-label" for="location_id"><?php echo lang('warehouse'); ?></label>
                        <?php
                        $loc = array('' => lang('select') . ' ' . lang('warehouse'));
                        if (!empty($warehouses)) {
                            foreach ($warehouses as $wh) {
                                $loc[$wh->id] = $wh->name;
                            }
                        }
                        echo form_dropdown('location_id', $loc, set_value('location_id', ''), 'class="form-control select" id="location_id" style="width:100%;"');
                        ?>
                    </div>
                    */ ?>

                </div>
            </div>


          <?php if($Settings->synced_data_sales){ ?>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="customer_url"> Customer URL </label>
                        <input type="text" name="customer_url" id="customer_url" class="form-control" placeholder="Customer URL" />
                    </div>
                    <div class="col-sm-6">
                        <label for="synced_data"> Synch Data </label>
                        <?php
                            $syncedSalesData[0] = 'No';
                            $syncedSalesData[1] = 'Yes';
                            echo form_dropdown('synced_data', $syncedSalesData, '0', 'class="form-control tip "  id="synced_data" style="width:100%;"');
                       ?>
                     
                    </div>

                     <div class="col-sm-6">
                        <label for="privatekey"> Private Key</label>
                        <input type="text" name="privatekey" id="privatekey" class="form-control" placeholder="Private Key" />
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="modal-footer">
        <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary" id="add_customer"'); ?>
        </div>
    </div>
<?php echo form_close(); ?>
</div>
<!--</div>-->
</div>
<?= $modal_js ?>

<script type="text/javascript">
    /** Parse DOB text the same way as the datepicker (site.dateFormats.js_sdate). Avoids new Date("dd/mm/yyyy") US-parse bugs. */
    function parseCustomerDobValue(str) {
        if (!str || !String(str).trim()) {
            return null;
        }
        var fmt = (typeof site !== 'undefined' && site.dateFormats && site.dateFormats.js_sdate)
            ? String(site.dateFormats.js_sdate).toLowerCase() : 'dd/mm/yyyy';
        var parts = String(str).trim().split(/[^0-9]+/).filter(function (p) { return p.length; });
        if (parts.length < 3) {
            return null;
        }
        var tokens = fmt.split(/[^a-z]+/i).filter(function (t) { return t.length; });
        if (tokens.length < 3) {
            tokens = ['dd', 'mm', 'yyyy'];
        }
        var d, m, y, i, tok;
        for (i = 0; i < 3; i++) {
            tok = tokens[i].toLowerCase();
            if (tok.indexOf('d') === 0) {
                d = parseInt(parts[i], 10);
            } else if (tok.indexOf('m') === 0) {
                m = parseInt(parts[i], 10) - 1;
            } else if (tok.indexOf('y') === 0) {
                y = parseInt(parts[i], 10);
            }
        }
        if (d == null || m == null || y == null || isNaN(d) || isNaN(m) || isNaN(y)) {
            return null;
        }
        var dt = new Date(y, m, d);
        if (dt.getFullYear() !== y || dt.getMonth() !== m || dt.getDate() !== d) {
            return null;
        }
        return dt;
    }

    $(document).ready(function (e) {

        let countryValue = $('#country').val();
        if (!countryValue)  $('#state').prop('disabled', true).trigger('change');

        // Keep biller preselected state visible on first open.
        // Trigger country change only when no state is currently selected.
        if (countryValue && countryValue !== 'other' && !$('#state').val()) {
            $('#country').trigger('change');
        }

        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            },
            excluded: [':disabled', '#email_address', '#phone']
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

    $('#email_address').on('input blur', function () {
        var email = $(this).val();
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (email === '') {
            $('#email_error').hide();
            return true;
        }
        if (emailPattern.test(email)) {
            $('#email_error').hide();
            return true;
        }
        $('#email_error').show();
        return false;
    });

    $("#email_address").focusout(function () {
        var email = $("#email_address").val();
        if (email === '' || $('#email_error').is(':visible')) {
            return;
        }
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

    function showEditCustomerModal(customerId) {
        var $modal = $('#myModal');
        if (!$modal.length) {
            return;
        }
        $modal.empty();
        $modal.append($.parseHTML(
            '<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading customer details...</div></div></div>',
            document,
            true
        ));
        $modal.modal('show');
        $.ajax({
            url: "<?= site_url('customers/edit'); ?>/" + customerId,
            type: 'GET',
            cache: false,
            success: function(response) {
                $modal.empty();
                $modal.append($.parseHTML(response, document, true));
                $modal.modal('show');
            },
            error: function(xhr, status) {
                console.error('Failed to load edit customer form:', status, xhr ? xhr.status : '');
                $modal.empty();
                $modal.append($.parseHTML(
                    '<div class="modal-dialog"><div class="modal-content"><div class="modal-body text-danger">Failed to load customer details. Please try again.</div></div></div>',
                    document,
                    true
                ));
                $modal.modal('show');
            }
        });
    }

    function checkmobileno(groupname, mobileno, errorshow, thisid) {
        var phone = $.trim(String(mobileno || ''));
        if (!phone) {
            return;
        }
        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'get',
            url: '<?= base_url() ?>customers/checkMobileno',
            data: {
                groupname: groupname,
                mobileno: phone
            },
            success: function(response) {
                if (response && response.status == 'success') {
                    $('#' + thisid).val('').focus();
                    alert('Phone no already exists');

                    // POS delivery flow: keep customer selected and open edit form.
                    if ($('#poscustomer').length) {
                        $('#poscustomer').val(response.id).trigger('change');
                    }
                    if ($('#custname').length) {
                        $('#custname').val(response.id);
                    }
                    if ($('#customer_name').length) {
                        $('#customer_name').val((response.name || '') + '(' + (response.phone || '') + ')');
                    }
                    if (typeof localStorage !== 'undefined') {
                        localStorage.setItem('poscustomer', response.id);
                        localStorage.setItem('poscustomername', response.name || '');
                    }
                    if (response.id) {
                        showEditCustomerModal(response.id);
                    }
                }
            }
        });
    }

    function validateGstinno(el) {
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

    function validatePostalCode() {
        var $postal = $('#postal_code');
        if (!$postal.length || $postal.is(':disabled') || !$('#postal_code_group').is(':visible')) {
            $('#postal_code_error').hide();
            return true;
        }
        var postalCode = $.trim(String($postal.val() || ''));
        if (postalCode !== '' && postalCode.length < 6) {
            $('#postal_code_error').show();
            return false;
        }
        $('#postal_code_error').hide();
        return true;
    }

    function isPosDeliveryCustomerFlow() {
        var redirectUrl = $.trim(String($('input[name="redirect_url"]').val() || ''));
        var hasPosRedirect = /(^|\/)pos(\/|$)/i.test(redirectUrl) || /index\.php\/pos(\/|$)/i.test(redirectUrl);
        var hasDeliveryType = /[?&]type=delivery(?:&|$)/i.test(window.location.search || '');
        return hasPosRedirect || hasDeliveryType;
    }

    function syncAddCustomerButtonState() {
        var $btn = $('#add_customer');
        if (!$btn.length) {
            return;
        }
        if (!isPosDeliveryCustomerFlow()) {
            $btn.prop('disabled', false);
            return;
        }
        var disable = false;
        var phoneVal = $.trim(String($('#phone').val() || ''));
        if (!phoneVal) {
            disable = true;
        }
        if ($('#phone_error').is(':visible')) {
            disable = true;
        }
        if ($('#email_error').is(':visible')) {
            disable = true;
        }
        if ($('#dob_error').is(':visible')) {
            disable = true;
        }
        if ($('#postal_code_group').is(':visible') && $('#postal_code_error').is(':visible')) {
            disable = true;
        }
        $btn.prop('disabled', disable);
    }

    $('#postal_code').on('input blur', function () {
        validatePostalCode();
        syncAddCustomerButtonState();
    });

    $('#add-customer-form').on('input change blur', '#phone, #email_address, #dob, #postal_code, #country', function () {
        setTimeout(syncAddCustomerButtonState, 0);
    });


    $('#add_customer').click(function () {
        $('#errpancard').html("");
        syncAddCustomerButtonState();
        if (isPosDeliveryCustomerFlow() && $(this).prop('disabled')) {
            return false;
        }

        var email = $('#email_address').val();
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (email !== '' && !emailPattern.test(email)) {
            $('#email_error').show();
            $('#email_address').focus();
            return false;
        }
        $('#email_error').hide();

        if (!validatePostalCode()) {
            $('#postal_code').focus();
            return false;
        }

        var dob = $('#dob').val();
        if (dob !== '') {
            var selectedDate = parseCustomerDobValue(dob);
            if (selectedDate) {
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);
                if (selectedDate > today) {
                    $('#dob_error').show();
                    $('#dob').focus();
                    return false;
                }
            }
            $('#dob_error').hide();
        }

        if ($('#pan_card').val() == '') {
            if ($('#state').val() == 'other' || $('#statename').val() == '') {
                $('#errstatename').html("<strong>State Name</strong> is required.");
                return false;
            }
            $('#errstatename').html("");
            return true;
        }
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        var pan_card = $('#pan_card').val();
        if (patt.test(pan_card)) {
            if ($('#state').val() == 'other' || $('#statename').val() == '') {
                $('#errstatename').html("<strong>State Name</strong> is required.");
                return false;
            }
            $('#errstatename').html("");
            return true;
        }
        $('#errpancard').html("\"<strong>" + pan_card + "</strong>\" this no. invalid, Please enter valid pancard no.");
        $('#pan_card').val("");
        return false;
    });

    /* Country/state list, add_country sync, phone length & GST label: core.js (works when this form is loaded into #myModal via AJAX). */

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

        var selectedDate = parseCustomerDobValue(dob);
        if (!selectedDate) {
            $('#dob_error').hide();
            return true;
        }
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);

        if (selectedDate > today) {
            $('#dob_error').show();
            $(this).val('');
            return false;
        }
        $('#dob_error').hide();
        return true;
    });

    setTimeout(syncAddCustomerButtonState, 100);

</script>
