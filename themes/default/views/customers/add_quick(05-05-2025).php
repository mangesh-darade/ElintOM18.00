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

#toggle-more-details {
    float: right;
    margin-right: 3.5rem;
    background: transparent;
    color: #333;
    margin-bottom: 1rem;
    outline: none;
    padding: 0.4rem 1rem;
    outline: none;
    text-decoration: underline;
}
</style>
<!--<div class="container" >-->
<div class="mymodal" id="modal-1" role="dialog">
    <div class="modal-dialog modal-lg add_quick">
        <div class="modal-content">
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
            echo form_open_multipart("customers/add/quick", $attrib ); ?>

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
                            <input type="tel" name="phone" class="form-control" required="required" id="phone"
                                data-bv-phone="true" data-bv-phone-country="US" onkeyup="checkmobileno('customer', $(this).val(), 'error', 'phone')" maxlength="10" required="required"
                                onkeypress="return IsNumeric(event,this)" ondrop="return false" onpaste="return false">
                            <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers
                                only</span>
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
                <!-- Additional Fields Section -->
                <div id="more-details" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label"
                                    for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                                <?php
                                foreach ($customer_groups as $customer_group) {
                                    $optval = $customer_group->id .'~'.$customer_group->name;
                                    $cgs[$optval] = $customer_group->name;
                                    $select_cgs = ($Settings->customer_group == $customer_group->id) ? $optval : null;
                                }
                                echo form_dropdown('customer_group', $cgs, $select_cgs, 'id="customer_group" data-placeholder="' . lang("customer_group") . '" class="form-control input-tip select" style="width:100%;height:30px; "  ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label"
                                    for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                                <?php
                                $pgs[''] = lang('select').' '.lang('price_group');
                                foreach ($price_groups as $price_group) {
                                    $pgoptval = $price_group->id .'~'.$price_group->name;
                                    $pgs[$pgoptval] = $price_group->name;
                                    $select_pg = ($Settings->price_group == $price_group->id) ? $pgoptval : null;
                                }
                                echo form_dropdown('price_group', $pgs, $select_pg, 'id="price_group" data-placeholder="' . lang("price_group") . '" class="form-control input-tip select" style="width:100%;height:30px; "  ');
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("gstn_no", "gstn_no"); ?>
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
                                <input type="text" name="email" class="form-control" id="email_address" />
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("country", "country"); ?>
                                <?php echo form_input('country', 'India', 'class="form-control" id="country"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("state", "state"); ?>
                                <?php
                                $st[""] = "";
                                foreach ($states as $state) {
                                    $st_otp = $state->name . '~' . $state->code;
                                    $st[$st_otp] = $state->name.' ('.$state->code.')';
                                    $select_st = (isset($_POST['state']) ? $_POST['state'] : '')==$state->name ? $st_otp : '';
                                }
                                echo form_dropdown('state', $st, $select_st, 'id="state" data-placeholder="' . lang("select") . '" class="form-control input-tip select" style="width:100%;height:30px;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("State Code", "State Code"); ?>
                                <select class="form-control" id="gst_state_code" name="gst_state_code">
                                    <option value="">-- Select State Code -- </option>
                                    <?php foreach($states as $gst_state_code){ 
                                        $selected = $Settings->state_code == $gst_state_code->gst_state_code ? 'selected' : '';?>
                                    <option value="<?= $gst_state_code->gst_state_code ?>" <?= $selected; ?>>
                                        <?= $gst_state_code->name ?> (<?= $gst_state_code->gst_state_code ?>)</option>
                                    <?php } ?>
                                </select>
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
                                <?= lang("postal_code", "postal_code"); ?>
                                <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" onkeypress="return IsNumeric2(event,this)" ondrop="return false" onpaste="return false"'); ?>
                                <span id="error1" style="color:#a94442; display: none;font-size:11px;">please enter
                                    numbers only</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                    </div>
                    <input type="checkbox" name="moreoption" id="moreoption"> <label for="moreoption">More
                        Option</label>
                    <div id="moreoption_block" style="display:none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("DOB", "dob"); ?>
                                    <?php echo form_input('dob', (isset($_POST['dob']) ? $_POST['dob'] : ""), 'class="form-control input-tip date" id="dob" '); ?>
                                </div>
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
                <!-- Button to toggle more details -->
                <div class="row" id="more-details-button-container">
                    <div class="col-md-12">
                        <button type="button" id="toggle-more-details" class="btn btn-secondary"
                            style="float: right;">More Details <i class="fa fa-chevron-down"></i></button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?= $modal_js ?>

<script>
document.getElementById('toggle-more-details').addEventListener('click', function() {
    var moreDetails = document.getElementById('more-details');
    if (moreDetails.style.display === 'none') {
        moreDetails.style.display = 'block';
        this.querySelector('i').classList.remove('fa-chevron-down');
        this.querySelector('i').classList.add('fa-chevron-up');
    } else {
        moreDetails.style.display = 'none';
        this.querySelector('i').classList.remove('fa-chevron-up');
        this.querySelector('i').classList.add('fa-chevron-down');
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
});
$('#add_customer').click(function() {
    $('#errpancard').html(" ");
    if ($('#pancard').val() == '') {
        return true;
    } else {
        var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
        var pan_card = $('#pancard').val();
        if (patt.test(pan_card)) {
            return true;
        } else {
            $('#errpancard').html("\"<strong>" + pan_card +
                " </strong>\" this no. invalid, Please enter valid pancard no.");
            $('#pancard').val(" ");
            return false;
        }
    }
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
$('#pancard').change(function() {
    $('#errpancard').html(" ");
    var patt = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
    var pan_card = $(this).val();
    if (patt.test(pan_card)) {
        $('#errpancard').html(" ");
    } else {
        $('#errpancard').html("\"<strong>" + pan_card +
            " </strong>\" this no. invalid, Please enter valid pancard no.");
        $(this).val(" ");
    }
});
$("#moreoption").on("click", function() {

    if ($(this).prop('checked')) {
        $('#moreoption_block').show();
    } else {
        $('#moreoption_block').hide();
    }
});
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
</script>