<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Customer Group 
$getcgr = explode(',', $CouponsInfo->customer_group_id);
if (is_array($getcgr)) {
    foreach ($getcgr as $cgr) {
        $CustomerGroup.=$customer_group_list[$cgr] . '';
    }
}

// Customer
$getc = explode(',', $CouponsInfo->customer_id);
if (is_array($getc)) {
    foreach ($getc as $oop) {
        $customer.= $companies_list[$oop] . '';
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

        <div class="modal-body">

            <div class="form-group row">
                <strong class="col-sm-3" > <?= lang('Coupon code') ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow"> <?= $CouponsInfo->coupon_code; ?> </span> </p> 
                </div>
            </div>  

            <div class="form-group row">
                <strong class="col-sm-3"> Customer</strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow"> <?= (($customer) ? $customer : 'ALL') ?> </span> </p> 
                </div>    
            </div> 

            <div class="form-group row">
                <strong class="col-sm-3" > Customer Group </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow"> <?= (($CustomerGroup) ? $CustomerGroup : 'ALL') ?> </span> </p>  
                </div>
            </div>    
            <div class="form-group row "  >
                <strong class="col-sm-3" > Minimum Cart Value</strong>
                <div class="col-sm-2">
                    <p > : <span class="datashow"> <?= ($CouponsInfo->minimum_cart_amount == '') ? '---' : $CouponsInfo->minimum_cart_amount ?> </span> </p>  
                </div>
            </div>
            <div class="form-group row "  >
                <strong class="col-sm-3" > Discount: (Flat or %)</strong>
                <div class="col-sm-2">
                    <p > : <span class="datashow"> <?= ($CouponsInfo->discount_rate == '') ? '---' : $CouponsInfo->discount_rate ?> </span> </p>  
                </div>
            </div>

            <div class="form-group row">
                <strong class="col-sm-3" ><?= lang('Maximum_Discount_Amount') ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow">  <?= ($CouponsInfo->maximum_discount_amount == '') ? '---' : $CouponsInfo->maximum_discount_amount ?></span> </p>  
                </div>
            </div>   

            <div class="form-group row">
                <strong class="col-sm-3" ><?= lang('Expiry_Date') ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow">  <?= ($CouponsInfo->expiry_date == '') ? '---' : date('d-m-Y', strtotime($CouponsInfo->expiry_date)) ?></span> </p>  
                </div>
            </div>   

            <div class="form-group row">
                <strong class="col-sm-3" ><?= lang('Max_Coupons') ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow">  <?= ($CouponsInfo->max_coupons == '') ? '---' : $CouponsInfo->max_coupons ?></span> </p>  
                </div>
            </div> 
            
            <div class="form-group row">
                <strong class="col-sm-3" ><?= lang('Used_Coupons') ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow">  <?= ($CouponsInfo->used_coupons == '') ? '---' : $CouponsInfo->used_coupons ?></span> </p>  
                </div>
            </div> 
            
             <div class="form-group row">
                <strong class="col-sm-3" ><?= lang('Status') ?> </strong>
                <div class="col-sm-8">
                    <p > : <span class="datashow">  <?= ($CouponsInfo->status == '') ? '---' : ucfirst($CouponsInfo->status) ?></span> </p>  
                </div>
            </div> 


            <div class="modal-footer no-print">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><?= lang('close'); ?></button>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>
</div>