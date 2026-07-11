<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>E-Shop User Landing</title>
        <link href="<?= $assets ?>T1/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?= $assets ?>T1/css/font-awesome.min.css" rel="stylesheet">
        <link href="<?= $assets ?>T1/css/animate.css" rel="stylesheet">
        <link href="<?= $assets ?>T1/css/main.css" rel="stylesheet">
        <link href="<?= $assets ?>T1/css/responsive.css" rel="stylesheet">	
        <link href="<?= $assets ?>T1/css/hover.css" rel="stylesheet" media="all">
    </head><!--/head-->
    <body style="background: url('<?=base_url("assets/mdata/$Customer_assets/uploads/eshop_user/login_bg.jpg")?>') 50% 50%!important;">
        <section class="middle_section"><!--Middle section view-->
            <div class="container">
                <div class="row">                    
                    <div class="container wrapper login-page"> 
                        <div class="col-md-8 col-md-offset-2  col-xs-12">
                            <p><span class="login-logo1" ><img src="<?= $baseurl; ?>assets/mdata/<?= $Customer_assets ?>/uploads/logos/<?= $this->Settings->logo ?>" alt="Logo" class="img-responsivee" /></span></p>
                            <input type="hidden" id="baseurl" value="<?= $baseurl; ?>" />
                            <div class="login-form"><!--login form-->
                                <h2>Choose Order Methods</h2> 
                                <p class="text-success bg-success"><?php echo $msg; ?></p>
                                <p class="text-danger bg-danger"><?php echo $login_error; ?></p>
                                
                                <?php
                                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'name' => "delivery_options", id => "delivery_options");
                                echo form_open_multipart("shop/set_shipping_methods", $attrib, ['action' => 'set_shippings'])
                                ?>
                                
                                <div class="col-md-8  col-md-offset-2  col-xs-12" >
                                    <div class="form-group row">               
                                        <label>Choose Delivery Method *</label>
                                        <?php                 
                                        if(!empty($shipping_methods)){
                                        ?>
                                        <select class="form-control" name="shipping_methods" id="shipping_methods">
                                        <?php foreach ($shipping_methods as $method) {
                                            
                                                $minimum_order_text = ($method['minimum_order_amount'] > 0) ? ' - <small>(Minimum Order Amount '.$currency_symbol.' '.$method['minimum_order_amount'].')</small>' : '';
                                                echo '<option value="'.$method['code'].'" data-id="'.$method['id'].'"  data-alltime="'.$method['all_time'].'" data-warehouse="'.$method['order_to_warehouse'].'">'.$method['name'].$minimum_order_text.'</option>';    
                                         } ?>
                                        </select>
                                        <?php } ?>
                                        <input type="hidden" name="order_received_outlet" id="order_received_outlet" value="" />
                                    </div>
                                    <div class="form-group row hide_me" id="divpincode">                
                                        <label class="shipp_pincode_lable">Pincode *</label>
                                        <input type="text" name="pincode" id="pincode" maxlength="6" required="required" class="form-control " />                
                                    </div>
                                    <?php if($eshop_settings->active_multi_outlets) { ?>
                                    <div class="form-group row hide_me" id="divlocation">
                                        <label class="shipp_outlet_lable">Outlet *</label>
                                        <select name="location" id="location" class="form-control">
                                            <option>--Select Outlet--</option>
                                        </select>
                                    </div>  
                                    <?php } ?>
                                    <div class="form-group row hide_me" id="divdate">                 
                                        <label class="shipp_date_lable">Date *</label>
                                        <input type="date" name="delivery_date" id="delivery_date" required="required"  class="form-control" />                
                                    </div>
                                    <div class="form-group row hide_me" id="divtime">                
                                        <label class="shipp_time_lable">Time</label>                 
                                        <select name="delivery_time" id="delivery_time_slots" class="form-control">
                                            <option>--Select Time--</option>
                                        </select>
                                    </div>            
                                </div>

                                <div style="margin:30px 15%"><button type="submit" name="btn_submit" id="btn_submit" style="width: 100%;" value="save" class="btn btn-lg">Continue Shopping</button></div>
                                                                
                                <?php echo form_close(); ?>
                                <div class="clear"></div>   
                            </div>
                        </div><!--/login form-->
                    </div>
                </div>
            </div>
        </div>
    </section><!--/Middle section view -->
    <script src="<?= $assets ?>T1/js/jquery.js"></script>
    <script src="<?= $assets ?>T1/js/bootstrap.min.js"></script>
    <!-- Loader -->
        
    <script>
$(document).ready(function(){
    
    multi_outlet = '<?=$eshop_settings->active_multi_outlets?>';
    
    loadShippingMethods();
  
    $('#shipping_methods').on('change', function(){
    
        loadShippingMethods();

    });

<?php //if ($eshop_setting->active_multi_outlets) { ?>
    $('#pincode').on('change', function(){
        
        var pincode = $('#pincode').val();
        
        validate_pincode(pincode);
        
        if(pincode && parseInt(multi_outlet)) {
            
            setPincodeLocation(pincode);
        }
        
    });
<?php //} ?>
    
    
    $('#btn_submit').on('click', function(){
        
        var shipping_methods = $('#shipping_methods').val(); 
        
        var pincode = $('#pincode').val();
         
        validate_pincode(pincode);
        
        if(shipping_methods == 'deliver_later' || shipping_methods == 'pickup_later'){
            
            var delivery_date = $('#delivery_date').val();
            if(delivery_date == '') {
                alert('Please select '+shipping_methods+' date');
                return false;
            }            
            var today = '<?=date('Y-m-d')?>';            
            if( delivery_date <= today ) {
                alert("Please select date after today");
                return false;
            }            
        }        
    });
    
    
});

function validate_pincode(pincode){
        
    var pinrejex =/^\d{6}$/;

    if(!pinrejex.test(pincode))
    {
        alert("Pincode should be valid 6 digits number");
        $('#pincode').val('');
        $('#pincode').focus();
        return false;
    } 
}

function loadShippingMethods(){
   
    var shipping_methods = $('#shipping_methods').val();   
    var method_id = $('#shipping_methods').find(':selected').attr('data-id');
    var method_alltime = $('#shipping_methods').find(':selected').attr('data-alltime');
    var method_warehouse = $('#shipping_methods').find(':selected').attr('data-warehouse');
    
    $('#order_received_outlet').val(method_warehouse);
    $('#delivery_date').attr('required', true);
    
    $('.hide_me').hide();
    switch(shipping_methods){
        case "delivery":
            
            $('#divpincode').show();
            $('#divlocation').show();
            $('#divtime').show();
            
            $('.shipp_pincode_lable').html('Delivery Pincode *');           
            $('.shipp_time_lable').html('Delivery Time');
            $('.shipp_outlet_lable').html('Delivery Outlet *');
            $('#delivery_date').removeAttr('required');
            //$('#location').attr('disabled', true);
            break;
        case "pickup":
            $('#divpincode').show();
            $('#divlocation').show();            
            $('#divtime').show();            
            
            $('.shipp_pincode_lable').html('Enter Your Pincode *');
            $('.shipp_time_lable').html('Select Pickup Time');
            $('.shipp_outlet_lable').html('Nearest Pickup Outlet *');
            $('#delivery_date').removeAttr('required');
            //$('#location').attr('disabled', true);
            break;
        case "deliver_later":
            
            $('#divpincode').show();
            $('#divlocation').show();
            $('#divdate').show();
            $('#divtime').show();
            
            $('.shipp_pincode_lable').html('Enter Delivery Pincode *');           
            $('.shipp_time_lable').html('Delivery Time');
            $('.shipp_date_lable').html('Delivery Date *');
            $('.shipp_outlet_lable').html('Delivery Outlet *');
           // $('#location').attr('disabled', true);
            
            break;
        case "pickup_later":
            
            $('#divpincode').show();
            $('#divlocation').show();
            $('#divdate').show();
            $('#divtime').show();
            
            $('.shipp_date_lable').html('Select Pickup Date *');
            $('.shipp_pincode_lable').html('Enter Your Pincode *');
            $('.shipp_time_lable').html('Select Pickup Time');
            $('.shipp_outlet_lable').html('Nearest Pickup Outlet *');
           // $('#location').attr('disabled', true);
            
            break;
    }
     
    if(method_alltime != 1) {
        setMethodDeliveryTime(method_id);
    } else {
        setTimeSlots();
    }
}


function setMethodDeliveryTime(method_id){
    
    $.ajax({
            type: 'ajax',
            dataType: 'html',
            method: 'GET',
            url: '<?= base_url("shop/get_shipping_times/") ?>'+method_id,           
            success: function (response) {                
                $('#delivery_time_slots').html(response);
            }, error: function () {
                console.log('error');
            }
        });
}

function setTimeSlots(){
   
     $.ajax({
            type: 'ajax',
            dataType: 'html',
            method: 'GET',
            url: '<?= base_url("shop/get_time_slots/") ?>',           
            success: function (response) {
                
                $('#delivery_time_slots').html(response);

            }, error: function () {
                console.log('error');
            }
        });
}


function setPincodeLocation(pincode){
    
     $.ajax({
            type: 'ajax',
            dataType: 'html',
            method: 'GET',
            url: '<?= base_url("shop/get_pincode_location")?>?pincode='+pincode,           
            success: function (response) {                
                $('#location').html(response);
                $('#location').attr('disabled', false);
            }, error: function () {
                console.log('error');
            }
        });
}

</script>
    <!-- End Loader -->
</body>
</html>