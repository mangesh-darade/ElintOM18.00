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

.modal-header .btnGrp {
    position: absolute;
    top: 18px;
    right: 10px;
}
</style>
<div class="container">
    <!--<div class="mymodal" id="modal-1" role="dailog">-->
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
            </div>
            <?php
            $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
            echo form_open_multipart("customers/add_quick", $attrib);
            ?>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
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
                            <label class="control-label"
                                for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
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
                        <div class="form-group person">
                            <?= lang("name", "name"); ?>
                            <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" type="text" type="text" id="text1" ondrop="return false;" onpaste="return false;"'); ?>
                            <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets
                                only</span>
                        </div>
                        <div class="form-group">
                            <?= lang("address", "address"); ?>
                            <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                        </div>

                        <div class="form-group">
                            <?= lang("email_address", "email_address"); ?>
                            <input type="text" name="email" class="form-control" id="email_address" data-bv-emailaddress
                                data-bv-onerror="onFieldError" data-bv-onsuccess="onFieldSuccess"
                                data-bv-onstatus="onFieldStatus" />
                        </div>

                        <div class="form-group">
                            <?= lang("phone", "phone"); ?>
                            <input type="tel" name="phone" class="form-control" required="required" id="phone"
                                data-bv-phone="true" data-bv-phone-country="US"
                                onkeyup="checkmobileno('customer', $(this).val(), 'error', 'phone')"
                                onkeypress="return IsNumeric(event, this)" maxlength="10" type="text" id="text1"
                                ondrop="return false" onpaste="return false">
                            <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers
                                only</span>
                        </div>

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
                    <div class="col-sm-6">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("vat_no", "vat_no"); ?>
                                    <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("gstn_no", "gstn_no"); ?> (GST Number)
                                    <?php echo form_input('gstn_no', '', 'class="form-control" id="gstn_no" minlength="15" maxlength="15"  onchange="return validateGstin();"'); ?>
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
                                    echo form_dropdown('country', $ct, (isset($_POST['country']) ? $_POST['country'] : $biller->country), 'id="country"   data-placeholder="' . lang("select") . ' ' . lang("country") . '"  class="form-control input-tip select" style="width:100%;height:30px;"');
 //data-bv-notempty="true"
                                    ?>
                                </div>
                                <div class="form-group" id="div_country_name">
                                    <?= lang("Country Name", "country"); ?>
                                    <input type="text" name="add_country" placeholder="Country Name"
                                        value="<?= $biller->country ?>" id="add_country" readonly="readonly"
                                        class="form-control" />
                                    <span id="errora2" style="color:#a94442;font-size:10px; display: none">please enter
                                        alphabets only</span>
                                </div>
                                <div class="form-group">
                                    <?= lang("Pan_Number", "pan_card"); ?>
                                    <?php echo form_input('pan_card', (isset($_POST['pan_card']) ? $_POST['pan_card'] : ''), 'class="form-control" id="pan_card" maxlength="10"'); ?>
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
                                    echo form_dropdown('state', $st, (isset($_POST['state']) ? $_POST['state'] : $biller->state . '~' . $biller->state_code), 'id="state" data-placeholder="' . lang("select") . ' ' . lang("state") . '" class="form-control input-tip select"    style="width:100%;height:30px;"');
                                    ?>
                                </div>
                                <div class="form-group" id="div_statename">
                                    <?= lang("State Name", "state"); ?>
                                    <input type="text" name="statename" placeholder="State Name"
                                        value="<?= $biller->state ?>" id="statename" class="form-control"
                                        required="required" readonly="readonly" />
                                    <small class="text-danger" id="errstatename"></small>
                                </div>
                                <div class="form-group" id="div_statecode">
                                    <?= lang("State Code", "state"); ?>
                                    <input type="text" name="state_code" placeholder="State Code"
                                        value="<?= $biller->state_code ?>" id="state_code" class="form-control"
                                        readonly="readonly" />
                                    <small class="text-danger" id="errstate_code"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("City_Name", "city"); ?>
                                    <?php echo form_input('city', '', 'class="form-control" id="city" onkeypress="return onlyAlphabets1(event,this);"  type="text"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("postal_code", "postal_code"); ?>
                                    <?php echo form_input('postal_code', (isset($_POST['postal_code']) ? $_POST['postal_code'] : ''), 'class="form-control" id="postal_code"    maxlength="6"  onkeypress="return IsNumeric2(event,this)" type="text" id="text1" ondrop="return false" onpast="return false"'); ?>
                                    <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter
                                        numbers only</span>
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
                                        <?php echo form_input('dob', (isset($_POST['dob']) ? $_POST['dob'] : ""), 'class="form-control input-tip date" id="dob" '); ?>
                                    </div </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("Anniversary Date", "anniversary"); ?>
                                        <?php echo form_input('anniversary', (isset($_POST['anniversary']) ? $_POST['anniversary'] : ""), 'class="form-control input-tip date" id="anniversary"'); ?>
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
$(document).ready(function(e) {
    $('#add-customer-form').bootstrapValidator({
        feedbackIcons: {
            valid: 'fa fa-check',
            invalid: 'fa fa-times',
            validating: 'fa fa-refresh'
        },
        excluded: [':disabled']
    });
    $('select.select').select2({
        minimumResultsForSearch: 7
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

    $('.form-control').attr('autocomplete', 'off');
});

$("#email_address").focusout(function() {
    var email = $("#email_address").val();

    if (email != '') {
        $.ajax({
            type: "get",
            url: '<?php echo base_url(); ?>customers/getEmail',
            data: {
                emailid: email
            },
            success: function(response) {
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


$('#pan_card').change(function() {
    $('#errpancard').html(" ");
    var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
    var pan_card = $(this).val();
    if (patt.test(pan_card)) {
        $('#errpancard').html("");
    } else {
        if (pan_card != '') {
            $('#errpancard').html("\"<strong>" + pan_card +
                "</strong>\" this no. invalid, Please enter valid pancard no.");
            $(this).val("");
        }
    }
});

$('#add_customer').click(function() {
    $('#errpancard').html("");
    if ($('#pan_card').val() == '') {
        return true;
    } else {
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        var pan_card = $('#pan_card').val();
        if (patt.test(pan_card)) {
            return true;
        } else {
            $('#errpancard').html("\"<strong>" + pan_card +
                "</strong>\" this no. invalid, Please enter valid pancard no.");
            $('#pan_card').val("");
            return false;
        }
    }

    if ($('#state').val() == 'other' || $('#statename').val() == '') {
        $('#errstatename').html("<strong>State Name</strong> is required.");
        return false;
    }

    return false;
});


$('#country').change(function(event) {

    if ($(this).val() == 'other') {
        $('#add_country').attr('readonly', false);
        $('#add_country').val('');
        $('#state').html('<option value="other" selected="selected" >Other</option>');
        $('#state_code').attr('readonly', false);
        $('#statename').attr('readonly', false);
        setTimeout(function() {
            $("#state").select2("val", "other");
            $('#state_code').val('');
            $('#statename').val('');
        }, 100);

    } else {
        $('#add_country').attr('readonly', true);
        $('#add_country').val($(this).val());
        // $('#state').html('<option value="">--Select State--</option>');
        get_state($(this).val());

        setTimeout(function() {
            $("#state").select2("val", "");
            $('#state_code').val('');
            $('#statename').val('');
        }, 100);

    }
});

$('#state').change(function(event) {
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
        data: {
            'country': country
        },
        success: function(response) {
            if (response.status == 'success') {
                $('#state').html(response.data);
            } else {
                $('#state').html(response.data);
            }
        }
    });
}


/**
 * Check Mobile no register or not 
 * @param {type} groupname
 * @param {type} mobileno
 * @param {type} errorshow
 * @param {type} thisid
 * @returns {undefined}
 */
function checkmobileno(groupname, mobileno, errorshow, thisid) {

    if (mobileno.toString().length == 10) {

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

                    $('#' + thisid).val('');
                    $('#' + thisid).focus();
                    alert('Phone no already exists');
                }

            }
        });
    }

}


$(document).ready(function() {
    /*if(localStorage.getItem('quickCusName')){
        $('#company').val(localStorage.getItem('quickCusName'));
    }*/

    if (localStorage.getItem('quickCusMobile')) {
        $('#phone').val(localStorage.getItem('quickCusMobile'));
    }

    //  localStorage.removeItem("quickCusName");
    localStorage.removeItem("quickCusMobile");
});


$("#moreoption").on("click", function() {

    if ($(this).prop('checked')) {
        $('#moreoption_block').show();
    } else {
        $('#moreoption_block').hide();
    }
});
</script>