<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    hr{height: 0.05em;
    background: #cccccc;}
     .select2-container-multi{height: auto;}
</style>
<?php 
    function get_times( $default = '', $interval = '+30 minutes' ) {

    $output = "<option value=''>Any Time</option>";

    $current = strtotime( '00:00' );
    $end = strtotime( '23:59' );

    while( $current <= $end ) {
        $time = date( 'H:i', $current );
        $sel = ( $time == $default ) ? ' selected' : '';

        $output .= "<option value=\"{$time}\"{$sel}>" . date( 'h.i A', $current ) .'</option>';
        $current = strtotime( $interval, $current );
    }

    return $output;
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue "><i class="fa-fw fa fa-gift"></i><?= lang('Coupon Edit'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('update_info'); ?></p>
                <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos_setting');
                    echo form_open("system_settings/coupon_edit/$offerdata->id");//, $attrib
                ?>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Coupon Edit : <?= $offerdata->coupon_name; ?></legend>
						<div class="form-group row">
                            <label class="col-sm-3" > <?= lang('Coupon_Name')  ?> * </label>
                            <div class="col-sm-8">
                                <?= form_input('coupon_name', $offerdata->coupon_name, 'placeholder="Coupon Name" class="form-control"  id="coupon_name" '); ?>
                                <span class="text-danger errormsg" id="errcoupon_name"></span>
                                
                            </div>
                        </div>
						<div class="form-group row">
                            <label class="col-sm-3" > <?= lang('Coupon_Code')  ?> * </label>
                            <div class="col-sm-8">
                                <?= form_input('coupon_code', $offerdata->coupon_code, 'placeholder="Coupon Code" class="form-control"  id="coupon_code" style="text-transform:uppercase"'); ?>
                                <span class="text-danger errormsg" id="errcoupon_code"></span>
                                
                            </div>
                        </div>
						
                        <div class="form-group row">
                            <label class="col-sm-3"> <?= lang('validity_date')  ?> <span>*</span> </label>
                            <label class="col-sm-2"> <?= lang('starting_date')  ?>  </label>
                            <div class="col-sm-2">
                                <?= form_input('offer_start_date',($offerdata->offer_start_date)?date('d/m/Y',strtotime($offerdata->offer_start_date)):'', 'placeholder="Offer Start Date" autocomplete="off" class="form-control date "  onchange="date_validation()"  id="offer_start_date" '); ?>
                                <span class="text-danger errormsg" id="erroffer_start_date"></span>
                            </div>
                           <label class="col-sm-2"> <?= lang('end_date')  ?>  </label>
                            <div class="col-sm-2">
                                <?= form_input('offer_end_date', ($offerdata->offer_end_date)?date('d/m/Y',strtotime($offerdata->offer_end_date)):'', 'placeholder="Offer End Date" autocomplete="off" class="form-control date " onchange="date_validation()"  id="offer_end_date" '); ?>
                                <span class="text-danger errormsg" id="erroffer_end_date"></span>
                            </div>
                        </div>
                    
                        <div class="form-group row">
                            <label class="col-sm-3"> <?= lang('specific_time')  ?>  <span>*</span></label>
                            <label class="col-sm-2"> <?= lang('from_time')  ?> </label>
                            <div class="col-sm-2"> 
                                <select class="form-control" name="offer_start_time" id="offer_start_time">
                                    <?php echo get_times(($offerdata->offer_start_time)?date("H:i",strtotime($offerdata->offer_start_time)):''); ?>
                                </select>   
                                <span class="text-danger errormsg"  id="erroffer_start_time"></span>
                            </div>
                            <label class="col-sm-2"> <?= lang('to_time')  ?>   </label>
                            <div class="col-sm-2">
                                <select class="form-control" name="offer_end_time" id="offer_end_time">
                                    <?php echo get_times(($offerdata->offer_end_time)?date("H:i",strtotime($offerdata->offer_end_time)):''); ?>
                                </select>  
                               <span class="text-danger errormsg"  id="erroffer_end_time"></span>
                            </div>

                        </div>
                    <center><span class="text-danger errormsg" style="display:block" id="erroffer_time"></span></center>
						<div class="form-group row">
                            <label class="col-sm-3">Customer<span> * </span></label>
                            <div class="col-sm-8">
                                <?php
                                $cs[0] = 'All';
                                foreach ($customers as $Customer) {
                                    $cs[$Customer->id] = $Customer->name;
                                }
								$selected_customer = explode(',', $offerdata->offer_on_customer);
                                echo form_multiselect('Customer[]', $cs, $selected_customer, 'class="form-control select" id="Customer" style="width:100%;" required="required"');
                                ?>
                                <span class="text-danger errormsg" id="erroffer_for_customer"></span>
                            </div>    
                        </div>
                       <div class="hideshowelement" id="block_offer_on_customer_group"> 
                        <div class="form-group row">
                            <label class="col-sm-3">Offer For Customer Groups <span>*</span></label>
                            <div class="col-sm-8">
                                <?php
                                $cgs[0] = 'All';
                                foreach ($customer_groups as $customer_group) {
                                    $cgs[$customer_group->id] = $customer_group->name;
                                }
                                $selected_customer_group = explode(',', $offerdata->offer_on_customer_group);
                                echo form_multiselect('offer_on_customer_group[]', $cgs, $selected_customer_group, 'class="form-control select" id="offer_on_customer_group" style="width:100%;" required="required"');
                                ?>
                                <span class="text-danger errormsg"  id="erroffer_for_customer_group"></span>
                            </div>    
                        </div>
                    </div> 
                    <div class="hideshowelement" id="block_offer_on_category_amount"  >
                             <div class="form-group row "  >
                                <label class="col-sm-3" > Minimum Cart Value <span>*</span>  </label>
                                <div class="col-sm-8">
                                   <input type="number" name='minimum_cart_value' value="<?= $offerdata->minimum_cart_value; ?>" placeholder="Minimum Cart Value"  autocomplete="off" class="form-control requiredfield" id="minimum_cart_value" />
                                <span class="text-danger errormsg" id="errminimum_cart_value" ></span>
                                </div>
                           </div>    
                        </div>
						<div   >
                             <div class="form-group row "  >
                                <label class="col-sm-3" > Discount<span>*</span>  </label>
                                <div class="col-sm-8">
                                   <input type="text" maxlength="5" name='Discount' value="<?= $offerdata->discount; ?>" placeholder="Discount"  autocomplete="off" class="form-control requiredfield" id="Discount" />
                                <span class="text-danger errormsg" id="errDiscount" ></span>
                                </div>
                           </div>    
                        </div>
                        <div class="" id=""  >
                            <div class="form-group row">
                                 <label class="col-sm-3" ><?= lang('Note')  ?> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control " name="offer_invoice_descriptions" placeholder="Note" value="<?php echo $offerdata->note;?>"/>
                                    </div>
                            </div>   
                        </div>
                        
                    <input type="hidden" name="offer_id" value="<?= $offerdata->id ?>" />
                </fieldset>    
                <button type="Submit" class="btn btn-primary" id="form_validation"> Update </button>
                <button type="button" class="btn btn-primary" onclick="window.location='system_settings/offer_list'" > Back </button>
                <?= form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    function date_validation(){   
        var start_date = $('#offer_start_date').val();
        var enddate = $('#offer_end_date').val();
       if(start_date==''){
           if(enddate!=' '){
           bootbox.alert('Please Select Offer Start Date');
           $('#offer_start_date').focus();
          }
           
       }else{
          if(!enddate==' '){
            if(enddate <  start_date){
                bootbox.alert('The end date must be a valid date and later than the start date');
            }
          }  
       }
       
    };
    
    
    // Form Validation
     $('#form_validation').click(function(){
       var flag=false;
       $('.errormsg').text('');
        var coupon_name = $('#coupon_name').val();
        var coupon_code = $('#coupon_code').val();
        var offer_start_date = $('#offer_start_date').val();
        var offer_end_date = $('#offer_end_date').val();
        var offer_start_time = $('#offer_start_time').val();
        var offer_end_time = $('#offer_end_time').val();
        var minimum_cart_value = $('#minimum_cart_value').val();
        var Discount = $('#Discount').val();
		var Customer_group = $('#offer_on_customer_group').val();
		var Customer = $('#Customer').val();
		
		if (Discount.length == '') {
			$('#errDiscount').html("Please enter Discount");
			flag = true;
		}
		if (minimum_cart_value.length == '') {
			$('#errminimum_cart_value').html("Please enter Minimum Cart Value");
			flag = true;
		}
		if (Customer == null) {
			$('#erroffer_for_customer').html("Select Customer");
			flag = true;
		}
		if (Customer_group == null) {
			$('#erroffer_for_customer_group').html("Select Customer Group");
			flag = true;
		}
		if (offer_start_time.length == '') {
			$('#erroffer_start_time').html("Please enter Start time");
			flag = true;
		}
		if (offer_end_time.length == '') {
			$('#erroffer_end_time').html("Please enter End time");
			flag = true;
		}
		if (offer_start_date.length == '') {
			$('#erroffer_start_date').html("Please enter Start Date");
			flag = true;
		}
		if (offer_end_date.length == '') {
			$('#erroffer_end_date').html("Please enter End Date");
			flag = true;
		}
       if(coupon_code.length==''){
            $('#errcoupon_code').html("Please enter coupon code");
			flag=true;
        }
        if(coupon_name.length==''){
            $('#errcoupon_name').html("Please enter coupon name");
			flag=true;
        }
       if(flag)
		   return false;
    });
    // End Form Validation
    
</script>
<script>
    $('#offer_end_time').change(function(){
        $('.errormsg').html('');
        if($('#offer_start_time').val()==''){
            $('#erroffer_time').html('Please select start date');
        }
        else if($('#offer_start_time').val() > $('#offer_end_time').val()){
            $('#erroffer_time').html('Please ensure that the End Date is greater than or equal to the Start Date.<br/>');
        } else {
            $('.errormsg').html('');
        }
    });
</script>    
 
