<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    // Customer Group 
    $getcgr = explode(',',$offerdata->offer_on_customer_group);
    if(is_array($getcgr)){
        foreach($getcgr as $cgr){
            $CustomerGroup.=$customer_group_list[$cgr].', ';
        }
    }
    
   // Customer
    $getc = explode(',',$offerdata->offer_on_customer);
    if(is_array($getc)){
        foreach($getc as $oop){
            $customer.= $companies_list[$oop].', ';
        }
    }
?>
<style>
    #view_data tr td{padding:1px 5px !important;}
    #view_data{margin-bottom: 5px !important;}
    .modal-footer {
    padding: 4px !important;
    text-align: right;
    border-top: 1px solid #e5e5e5;
}   .datashow{margin-left: 1em;}
</style>    

<div class="modal-dialog modal-lg">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>

            <h4 class="modal-title" id="myModalLabel">Coupon Name : <?= $offerdata->coupon_name;?></h4>
        </div>
        <div class="modal-body">
                      
            <div class="form-group row">
                <strong class="col-sm-3" > <?= lang('Coupon code')  ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow"> <?= $offerdata->coupon_code; ?> </span> </p> 
                </div>
            </div>  
            
            <div class="form-group row">
                <strong class="col-sm-3"> <?= lang('validity_date')  ?>  </strong>
                <strong class="col-sm-2"> <?= lang('starting_date')  ?>  </strong>
                <div class="col-sm-2">
                    <p > : <span class="datashow"> <?= ($offerdata->offer_start_date=='0000-00-00'||$offerdata->offer_start_date=='')?'---':date('d - M - Y',strtotime($offerdata->offer_start_date)); ?> </span> </p> 
                </div>
                <strong class="col-sm-2"> <?= lang('end_date')  ?>  </strong>
                <div class="col-sm-2">
                    <p > : <span class="datashow"> <?= ($offerdata->offer_end_date=='0000-00-00'||$offerdata->offer_end_date=='')?'---':date('d - M - Y',strtotime($offerdata->offer_end_date)); ?> </span> </p> 
                </div>
            </div>
                    
            <div class="form-group row">
                <strong class="col-sm-3"> <?= lang('specific_time')  ?> </strong>
                <strong class="col-sm-2"> <?= lang('from_time')  ?> </strong>
                <div class="col-sm-2"> 
                    <p > : <span class="datashow"> <?= ($offerdata->offer_start_time=='00:00'||$offerdata->offer_start_time=='')?'---':date("g:i A", strtotime($offerdata->offer_start_time)); ?> </span> </p> 
                </div>
                <strong class="col-sm-2"> <?= lang('to_time')  ?>   </strong>
                <div class="col-sm-2">
                    <p > : <span class="datashow"> <?= ($offerdata->offer_end_time=='00:00'||$offerdata->offer_end_time=='')?'---':date("g:i A", strtotime($offerdata->offer_end_time)); ?> </span> </p> 
                </div>
            </div>
             
                   
            <div class="form-group row">
                <strong class="col-sm-3"> Customer</strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow"> <?= rtrim($customer,", ") ?> </span> </p> 
                </div>    
            </div> 
                        
            <div class="hideshowelement" id="block_offer_on_category"  >
                <div class="form-group row">
                    <strong class="col-sm-3" > Customer Group </strong>
                    <div class="col-sm-8">
                        <p > : <span class="datashow"> <?= rtrim($CustomerGroup,", ") ?> </span> </p>  
                    </div>
                </div>    
            </div> 
            <div class="hideshowelement" id="block_offer_on_invoice_amount"  >
                <div class="form-group row "  >
                    <strong class="col-sm-3" > Minimum Cart Value</strong>
                    <div class="col-sm-2">
                        <p > : <span class="datashow"> <?= ($offerdata->minimum_cart_value=='')?'---':$offerdata->minimum_cart_value ?> </span> </p>  
                    </div>
                </div>
            </div>
            <div class="hideshowelement" id="block_offer_on_invoice_amount"  >
                <div class="form-group row "  >
                    <strong class="col-sm-3" > Discount</strong>
                    <div class="col-sm-2">
                        <p > : <span class="datashow"> <?= ($offerdata->discount=='')?'---':$offerdata->discount ?> </span> </p>  
                    </div>
                </div>
            </div>

            

            <div class="" id=""  >
                <div class="form-group row">
                    <strong class="col-sm-3" ><?= lang('Invoice Note')  ?> </strong>
                    <div class="col-sm-8">
                        <p > : <span class="datashow">  <?= ($offerdata->note=='')?'---':$offerdata->note ?></span> </p>  
                    </div>
                </div>   
            </div>
                        
           <div class="modal-footer no-print">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><?= lang('close'); ?></button>
            </div>
            
            <div class="clearfix"></div>
        </div>
    </div>
</div>