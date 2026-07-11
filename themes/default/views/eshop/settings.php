<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <style>
        .select2-drop select2-drop-multi{width: 211px!important;}
        .select2-container{width: 100%!important;}
        h1.upload-image {            
            text-align: center!important;
        }

        h1.upload-image i {
            font-size: 50px !important;            
            cursor: pointer!important;           
        }
    </style>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Eshop_Settings'); ?></h2>
    </div>
    <div class="box-content">
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'name' => "eshop_settings", id => "eshop_settings");
        echo form_open_multipart("eshop_admin/settings", $attrib, ['action' => 'save_settings'])
        ?>
        <div class="row">
            <p class="introtext"><?php echo lang('enter_info'); ?></p>   
            <fieldset class="scheduler-border">
                <legend class="scheduler-border">Media & Images</legend>
                <div class="row">
                    <?php
                    $check_1 = $check_2 = $check_3 = '';
                    if (!empty($eshop_settings->default_banner)) {
                        $default_banner = json_decode($eshop_settings->default_banner, true);
                        if (is_array($default_banner)) {
                            foreach ($default_banner as $key => $value) {
                                $check_key = 'check_' . $key;
                                $$check_key = ' checked="checked" ';
                            }
                        }
                    }
                    for ($d = 1; $d <= 3; $d++) {
                        ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php
                                $checkkey = 'check_' . $d;
                                if (@getimagesize($eshop_upload . "banner_$d.jpg")) {

                                    echo '<label><i class="fa fa-image text-success"></i> Default Banner Image ' . $d . ' </label>';

                                    echo img(array('src' => $eshop_upload . "banner_$d.jpg", 'class' => 'img img-responsive img-rounded', 'alt' => "banner_$d.jpg"));
                                    ?>        
                                    <label class="col-sm-12" style="margin-top:10px; padding: 0;"><input type="checkbox"  name="default_banner[<?= $d ?>]" value="<?= "banner_$d.jpg" ?>" <?= $$checkkey ?> /> Show in banner</label>    
                                    <?php
                                }
                                ?>

                            </div>                   
                        </div>
                    <?php } ?>
                </div>
                <hr/>
                <div class="row">
                    <?php
                    for ($b = 1; $b <= 3; $b++) {
                        ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php
                                $bl = "banner_image_" . $b;
                                if (!empty($eshop_settings->$bl) && @getimagesize($eshop_settings->$bl)) {

                                    echo '<label><i class="fa fa-image text-success"></i> Custom Banner Image ' . $b . ' <span><a class="text-danger" href="' . base_url('eshop_admin/deleteimage/' . $bl) . '"><i class="fa fa-remove"></i> Delete</a></span></label>';

                                    echo img(array('src' => $eshop_settings->$bl, 'class' => 'img img-responsive img-rounded', 'alt' => $eshop_settings->$bl));
                                } else {

                                    echo '<label><i class="fa fa-image text-success"></i> Custom Banner Image ' . $b . '<br/><small class="text-primary">(Minimum image size: 1600 x 500 pixcel)</small></label>';

                                    echo '<h1 class="upload-image"><label for="' . $bl . '"><i class="fa fa-cloud-upload"></i><br/><small id="' . $bl . '_selectedfile">Upload Image</small></label></h1>';
                                    echo form_upload("banner_image[$b]", (isset($_POST[$bl]) && !empty($_POST[$bl]) ? $_POST[$bl] : ($eshop_settings ? $eshop_settings->$bl : '')), 'class="form-control cloud_upload" style="display:none;" id="' . $bl . '"');
                                }
                                ?>
                                <span id="html_msg"></span>
                            </div>                   
                        </div>
                    <?php } ?>
                </div>
                <hr/>
                <div class="row">
                    <?php
                    for ($h = 1; $h <= 3; $h++) {
                        ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php
                                $hm = "homepage_image_" . $h;
                                $hmtx = "homepage_image_text_" . $h;
                                $txtmaxlength = ['1' => 30, '2' => 50, '3' => 40];
                                if (!empty($eshop_settings->$hm) && @getimagesize($eshop_settings->$hm)) {

                                    echo '<label><i class="fa fa-image text-danger"></i> Homepage Image ' . $h . ' <span><a class="text-danger" href="' . base_url('eshop_admin/deleteimage/' . $hm) . '"><i class="fa fa-remove"></i> Delete</a></span></label>';

                                    echo img(array('src' => $eshop_settings->$hm, 'class' => 'img img-responsive img-rounded hmp-img', 'alt' => $eshop_settings->$hm, 'style' => 'max-height:200px; width:100%;'));
                                } else {

                                    echo '<label><i class="fa fa-image text-danger"></i> Homepage Image ' . $h . '<br/><small class="text-primary">(Minimum image size: 350 x 230 pixcel)</small></label>';

                                    echo '<h1 class="upload-image"><label for="' . $hm . '"><i class="fa fa-cloud-upload"></i><br/><small id="' . $hm . '_selectedfile">Upload Image</small></label></h1>';
                                    echo form_upload("homepage_image[$h]", (isset($_POST[$hm]) && !empty($_POST[$hm]) ? $_POST[$hm] : ($eshop_settings ? $eshop_settings->$hm : '')), 'class="form-control cloud_upload" style="display:none;" id="' . $hm . '"');
                                }
                                ?>
                            </div>                   
                        </div>
                    <?php } ?>
                </div>
                <hr>
                <div class="row">                    

                    <div class="col-md-4">
                        <div class="form-group all" >                            
                            <?php
                            if (!empty($eshop_settings->hot_offers_banner) && @getimagesize($eshop_settings->hot_offers_banner)) {
                                echo '<label><i class="fa fa-image text-warning"></i> Hot Offers Banner  <span><a class="text-danger" href="' . base_url('eshop_admin/deleteimage/hot_offers_banner') . '"><i class="fa fa-remove"></i> Delete</a></span></label>';

                                echo img(array('src' => $eshop_settings->hot_offers_banner, 'class' => 'img img-responsive img-rounded', 'alt' => $eshop_settings->hot_offers_banner));
                            } else {

                                echo '<label><i class="fa fa-image text-warning"></i> Hot Offers Banner  </label>';
                                echo '<h1 class="upload-image"><label for="hot_offers_banner"><i class="fa fa-cloud-upload"></i><br/><small id="hot_offers_banner_selectedfile">Upload Image</small></label></h1>';
                                echo form_upload('hot_offers_banner', (isset($_POST['hot_offers_banner']) && !empty($_POST['hot_offers_banner']) ? $_POST['hot_offers_banner'] : ($eshop_settings ? $eshop_settings->hot_offers_banner : '')), 'class="form-control cloud_upload" style="display:none;" id="hot_offers_banner"');
                            }
                            ?>                            
                        </div>                        

                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">                            
                            <?php
                            if (!empty($eshop_settings->eshop_logo) && @getimagesize($eshop_settings->eshop_logo)) {
                                echo '<label><i class="fa fa-image text-warning"></i> Eshop Logo <span><a class="text-danger" href="' . base_url('eshop_admin/deleteimage/eshop_logo') . '"><i class="fa fa-remove"></i> Delete</a></span></label>';

                                echo '<div>' . img(array('src' => $eshop_settings->eshop_logo, 'class' => 'img img-responsive img-rounded', 'style' => "margin:auto; padding:50px;", 'alt' => $eshop_settings->eshop_logo, 'style' => 'height:100px;')) . '</div>';
                            } else {

                                echo '<label><i class="fa fa-image text-warning"></i> Eshop Logo <br/><small class="text-primary">(Maximum image size: 100 x 100 pixcel)</small></label>';
                                echo '<h1 class="upload-image"><label for="eshop_logo"><i class="fa fa-cloud-upload"></i><br/><small id="eshop_logo_selectedfile">Upload Logo</small></label></h1>';
                                echo form_upload('eshop_logo', (isset($_POST['eshop_logo']) && !empty($_POST['eshop_logo']) ? $_POST['eshop_logo'] : ($eshop_settings ? $eshop_settings->eshop_logo : '')), 'class="form-control cloud_upload" style="display:none;" id="eshop_logo"');
                            }
                            ?>                            
                        </div>                   
                    </div>
                </div>

            </fieldset>
            <fieldset class="scheduler-border">
                <legend class="scheduler-border">General</legend>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-shopping-bag text-info"></i> Eshop Name<br/><small class="text-primary">Name will display on eshop logo</small></label>
                            <?= form_input('shop_name', (isset($_POST['shop_name']) && !empty($_POST['shop_name']) ? $_POST['shop_name'] : ($eshop_settings ? $eshop_settings->shop_name : '')), 'class="form-control" id="shop_name" maxlength="25"'); ?>
                            <span id="html_msg"></span>
                        </div>                   
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-phone-square text-info"></i> Phone  <br/><small class="text-primary">(Can enter multiple numbers separated by comma)</small></label>
                            <?= form_input('shop_phone', (isset($_POST['shop_phone']) && !empty($_POST['shop_phone']) ? $_POST['shop_phone'] : ($eshop_settings ? $eshop_settings->shop_phone : '')), 'class="form-control" id="shop_phone" maxlength="35"'); ?>
                            <span id="html_msg"></span>
                        </div>                   
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-mail-forward text-info"></i> Email<br/><small class="text-primary">Enter email if want to display on eshop customers</small></label>
                            <?= form_input('shop_email', (isset($_POST['shop_email']) && !empty($_POST['shop_email']) ? $_POST['shop_email'] : ($eshop_settings ? $eshop_settings->shop_email : '')), 'class="form-control" id="shop_email" maxlength="40"'); ?>
                        </div>                   
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-list text-danger"></i> Show/Hide Top Products</label>
                            <?php
                            $display_top_products = 'selected_' . $eshop_settings->display_top_products;
                            $$display_top_products = ' selected="selected" ';
                            ?>
                            <select name="display_top_products" class="form-control">
                                <option value="0" <?= $selected_0 ?> >Hide Top Homepage Products</option> 
                                <option value="4" <?= $selected_4 ?> >4 Products</option> 
                                <option value="8" <?= $selected_8 ?> >8 Products</option> 
                                <option value="12" <?= $selected_12 ?> >12 Products</option>
                                <option value="20" <?= $selected_20 ?> >20 Products</option> 
                            </select>
                        </div> 
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-list text-danger"></i> Show/Hide Homepage Hot Offers</label>
                            <?php
                            $offercheck = 'offffer_' . $eshop_settings->display_hot_offers;
                            $$offercheck = ' checked="checked" ';
                            ?>
                            <select name="display_hot_offers" class="form-control">
                                <option value="0" <?= $offffer_1 ?> >Show Offers</option> 
                                <option value="1" <?= $offffer_0 ?> >Hide Offers</option>
                            </select> 
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-list text-danger"></i> Active Multiple Outlets / Locations</label>
                            <?php
                            $active_location = 'active_location_' . $eshop_settings->active_multi_outlets;
                            $$active_location = ' selected="selected" ';
                            ?>
                            <select name="active_multi_outlets" class="form-control">
                                <option value="0" <?= $active_location_0 ?> >No</option> 
                                <option value="1" <?= $active_location_1 ?> >Yes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-lock text-danger"></i> User Login Action</label>
                            <?php
                            $user_login_action = $eshop_settings->user_login_action;
                            $$user_login_action = ' selected="selected" ';
                            ?>
                            <select name="user_login_action" class="form-control" disabled="disabled" >
                                <option value="login_before_checkout" <?= $login_on_checkout ?> >Login Before Checkout</option> 
                                <option value="login_before_access_catalogue" <?= $login_before_access_catalogue ?> >Login Before Items Add To Cart</option>
                                <option value="login_before_user_landing" <?= $login_on_landing ?> >Login Before User Landing Page</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-dashboard text-danger"></i> User Landing Page</label>
                            <?php
                            $user_landing_page = $eshop_settings->user_landing_page;
                            $$user_landing_page = ' selected="selected" ';
                            ?>
                            <select name="user_landing_page" class="form-control">
                                <option value="home_page" <?= $home_page ?> >Default Home Page</option> 
                                <option value="select_delivery_option" <?= $select_delivery_option ?> >Choose Shipping Method Page</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label><i class="fa fa-remove text-danger"></i> Order Cancel</label>
                            <?php
                            $order_cancel_duration = 'h_'.$eshop_settings->order_cancel_duration;
                            $$order_cancel_duration = ' selected="selected" ';
                            ?>
                            <select name="order_cancel_duration" class="form-control">
                                <option value="">Can Not Cancel</option> 
                                <option value="24" <?= $h_24 ?> >Within 1 Day (24 Hours)</option> 
                                <option value="48" <?= $h_48 ?> >Within 2 Day (48 Hours)</option> 
                                <option value="72" <?= $h_72 ?> >Within 3 Day (72 Hours)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="scheduler-border">
                <legend class="scheduler-border">Social Media</legend>                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group all">
                            <label><i class="fa fa-facebook-official text-primary"></i> Facebook Page Link</label>
                            <?= form_input('facebook_link', (isset($_POST['facebook_link']) && !empty($_POST['facebook_link']) ? $_POST['facebook_link'] : ($eshop_settings ? $eshop_settings->facebook_link : '')), 'class="form-control" id="facebook_link" maxlength="100"'); ?>
                            <span id="html_msg"></span>
                        </div>                   
                    </div>
                    <div class="col-md-6">
                        <div class="form-group all">
                            <label><i class="fa fa-google-plus-official text-danger"></i> Google Profile Link</label>
                            <?= form_input('google_link', (isset($_POST['google_link']) && !empty($_POST['google_link']) ? $_POST['google_link'] : ($eshop_settings ? $eshop_settings->google_link : '')), 'class="form-control" id="google_link" maxlength="100"'); ?>
                            <span id="html_msg"></span>
                        </div>                   
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group all">
                            <label><i class="fa fa-twitter-square text-warning"></i> Twitter Profile Link</label>
                            <?= form_input('twitter_link', (isset($_POST['twitter_link']) && !empty($_POST['twitter_link']) ? $_POST['twitter_link'] : ($eshop_settings ? $eshop_settings->twitter_link : '')), 'class="form-control" id="twitter_link" maxlength="100"'); ?>
                            <span id="html_msg"></span>
                        </div>                   
                    </div>
                    <div class="col-md-6">
                        <div class="form-group all">
                            <label><i class="fa fa-instagram "></i> Instagram Link</label>
                            <?= form_input('instagram_link', (isset($_POST['instagram_link']) && !empty($_POST['instagram_link']) ? $_POST['instagram_link'] : ($eshop_settings ? $eshop_settings->instagram_link : '')), 'class="form-control" id="instagram_link" maxlength="100"'); ?>
                            <span id="html_msg"></span>
                        </div>                   
                    </div>
                </div>
            </fieldset>
            <!-- Payment Option -->
            <fieldset class="scheduler-border">
                <legend class="scheduler-border">Payment Methods</legend>
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group all">

                            <label> <input type="checkbox" name="cash_on_delivery" value="1" <?= $eshop_settings->cash_on_delivery ? 'checked' : '' ?> /> Cash on Delivery (COD)</label>

                        </div>                   
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label> <input type="checkbox" name="qr_upi_payment" value="1" <?= $eshop_settings->qr_upi_payment ? 'checked' : '' ?> />  QR Code & UPI</label>
                        </div>                   
                    </div>
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label> <input type="checkbox" name="paytm_payment" value="1" <?= $eshop_settings->paytm_payment ? 'checked' : '' ?> />  Paytm</label>

                        </div>                   
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group all">
                            <label> <input type="checkbox" name="accept_cc_dc_delivery" value="1" <?= $eshop_settings->accept_cc_dc_delivery ? 'checked' : '' ?> />  Accept Credit/Debit card on delivery.</label>
                        </div>                   
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group all">
                            <label><i class="fa fa-google-plus-official text-danger"></i> Payment UPI Link</label>
                            <?= form_input('upi_id', (isset($_POST['upi_id']) && !empty($_POST['upi_id']) ? $_POST['upi_id'] : ($eshop_settings ? $eshop_settings->upi_id : '')), 'class="form-control" id="google_link" maxlength="100"'); ?>

                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group all">
                            <label><i class="fa fa-google-plus-official text-danger"></i> Payment QR Code</label>
                            <input type="file" class="form-control" name="payment_qrcode" />

                        </div> 
                    </div>
                </div>
            </fieldset>
            <fieldset class="scheduler-border">
                <legend class="scheduler-border">Eshop Scheduling</legend>                   
                <div class="row">                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?= lang("Disable Ordering", "order_disable"); ?></label>
                            <?php
                            $odec = array('0' => 'No', '1' => 'Yes');
                            echo form_dropdown('disabled_ordering', $odec, (isset($_POST['disabled_ordering']) ? $_POST['disabled_ordering'] : $eshop_settings->disabled_ordering), 'id="disabled_ordering" class="form-control" style="width:100%;"');
                            ?>
                        </div>
                    </div> 
                    <div class="col-md-4">
                        <div class="form-group ">
                            <label><?= lang("Order Receive Time", "ordering_time"); ?> <span>(Open To Close)</span></label><br/>
                            <?php
                            $times = !empty($eshop_settings->ordering_time) ? explode('~', $eshop_settings->ordering_time) : '';
                            ?>
<!--                            <input type="time" name="ordering_time_open" value="<?= $times[0] ?>" class="form-control" style="width: 45%;display: inline;" />
                            <input type="time" name="ordering_time_close" value="<?= $times[1] ?>" class="form-control" style="width: 45%;display: inline;" />-->
                            <div class="row">
                                <div class="col-sm-5"> 
                                    <select class="form-control" name="ordering_time_open" >
                                        <?php echo get_times($times[0]); ?>
                                    </select> 
                                </div>
                                <label class="col-sm-1"> <?= lang('To') ?>  </label>
                                <div class="col-sm-5">
                                    <select class="form-control" name="ordering_time_close" >
                                        <?php echo get_times($times[1]); ?>
                                    </select>  
                                </div> 
                            </div>
                        </div>
                    </div> 
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?= lang("Order Receive Days", "ordering_days"); ?></label><br/>
                            <select multiple="multiple" name="ordering_days[]" id="ordering_days" class="form-control">
                                <?php
                                $days = $eshop_settings->ordering_days ? $eshop_settings->ordering_days : 'all';
                                if (!empty($days) && $days != 'all') {
                                    $daysArr = explode(',', $days);
                                    foreach ($daysArr as $day) {
                                        $d = "_" . $day;
                                        $$d = 'selected="selected"';
                                    }
                                } else {
                                    $all = 'selected="selected"';
                                }
                                ?>
                                <option value="" <?= $all ?>>All Day</option>                                
                                <option value="1" <?= $_1 ?>>Monday</option>
                                <option value="2" <?= $_2 ?>>Tuesday</option>
                                <option value="3" <?= $_3 ?>>Wednesday</option>
                                <option value="4" <?= $_4 ?>>Thursday</option>
                                <option value="5" <?= $_5 ?>>Friday</option>
                                <option value="6" <?= $_6 ?>>Saturday</option>
                                <option value="7" <?= $_7 ?>>Sunday</option>
                            </select>
                        </div>
                    </div> 
                </div>
            </fieldset>
            <!-- End Payment Option -->
            <div class="row" style="padding:10px;">
                <div class="form-group text-center">
                    <?php echo form_submit('send', $this->lang->line("Submit"), 'id="send" class="btn btn-primary"  style="margin-top:20px;"'); ?> 
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
<?php

function get_times($default = '', $interval = '+30 minutes') {

    $output = "<option value=\"\">Any Time</option>";

    $current = strtotime('00:00');
    $end = strtotime('23:59');

    while ($current <= $end) {
        $time = date('H:i', $current);
        $sel = ( $time == $default ) ? ' selected' : '';

        $output .= "<option value=\"{$time}\"{$sel}>" . date('h.i A', $current) . '</option>';
        $current = strtotime($interval, $current);
    }

    return $output;
}
?>

<script type="text/javascript">
    $(document).ready(function () {

        $('.cloud_upload').on('change', function () {
            var ID = this.id;

            $('#' + ID + '_selectedfile').html('Image: ' + this.value);
        });


        $.ajax({
            type: "get",
            async: false,
            url: "<?= site_url('customers/getCustomers') ?>",
            data: "data",
            dataType: "json",
            success: function (data) {
                $('#customers').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    //console.log(data.aaData);
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers'));
                });
                $('#customers').select2('val');
                $("#send").click(function () {
                    var cust_list = $('.select2-container').select2('val');

                    $('#hiddencust').val(cust_list);
                });
                $("#customers option").each(function () {
                    $customer_list = $(this).val();

                });
            },
            error: function () {
                bootbox.alert('<?= lang('ajax_error') ?>');
            }

        });
        $("#sendsmsemail").submit(function (event) {
            var subject = $('#subject').val();
            if (subject.trim() == '') {
                bootbox.alert('Please Enter Subject ');
                $('#pcc_year_1').parent().addClass('has-error');
                $('#pcc_year_1').focus();
                return false;
                event.preventDefault();
            }
        });
    });
</script>
