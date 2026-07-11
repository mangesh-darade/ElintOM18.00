<?php
$displayStyle = ($pos_settingss->display_seller == 2) ? 'none' : 'block';
?>
<script type="text/javascript" src="<?= $assets ?>pos/js/customer_family_relation.js?v=20260408_1"></script>
<script type="text/javascript" src="<?= $assets ?>js/customer_add_address_modal.js?v=20260630_2"></script>
<script type="text/javascript" src="<?= $assets ?>pos/js/edit_customer_details.js?v=20260620"></script>
<link rel="stylesheet" href="<?= $assets ?>pos/css/customer_relation.css" type="text/css" />
<style>
  /* #paymentModal {
    display: <?php echo $displayStyle; ?> !important;
  } */
  .payment-setting{
    display:none; 
  }
  .checkoutmodalbtn {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.checkoutmodalbtn button {
    flex: 1;
    min-width: 150px;
}
.denomination-wrapper {
    background: #e9ecef;
    padding: 15px;
    border-radius: 10px;
    height: 110%;
    width: 150% !important;
    margin-top: -9px;
}
.denomination{
    background: #e9ecef;
    padding: 15px;
    border-radius: 10px;
    height: 110%;
    width: 142% !important;
    margin-top: -9px;
}
button#clear-cash-notes{
    margin-top: -12px;
}
.denomination-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    margin-bottom: 15px;
}

.denom-box {
    border: 2px solid #1e88e5;
    border-radius: 15px;
    padding: 10px;
    width: 90px;
    text-align: center;
    position: relative;
    background: #fff;
}

.denom-value {
    position: absolute;
    top: -10px;
    left: 10px;
    background: #e9ecef;
    padding: 0 6px;
    font-weight: bold;
}

.denom-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.circle-btn {
    width: 19px;
    height: 27px;
    border-radius: 50%;
    background: #1e88e5;
    color: #fff;
    border: none;
    font-size: 18px;
}

.count {
    font-size: 18px;
    font-weight: bold;
}
#paymentModal #payment_content, #paymentModal #eventForm, #paymentModal #eventFormFamily {
    max-height: 65vh !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}
/* ONLY visual cursor */
.cash-not-allowed,
.cash-not-allowed * {
    cursor: not-allowed !important;
}
.cashmethod{
    width: 38% !important;
    margin-left: 4% !important;
}
#paymentModal .col-md-3 {
    margin: 1px -7px 40px 6px !important;
}
</style>

<div class="modal fade in payment-setting" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-times-circle" aria-hidden="true"></i>
                            </span><span class="sr-only"><?= lang('close'); ?></span></button>
                          <ul class="tab-nav">
                <?php if ( (in_array($Settings->theme, array('theme_four','theme_five', 'theme_six', 'theme_seven'))) && $pos_settings->display_CRM==1){?>
                    <li><button id="showDivButton1" class="active">Payment</button></li>
                    <li><button id="showDivButton2">Customer Details</button></li>
                <?php } else { ?>
                <li> <h2>Finalize Sale</h2></li>
                <?php } ?>
    
                </ul>
                    </div>
                    <div class="modal-body payments_mainsection hidden_div" id="payment_content">
                       <div class="row">
                            <div class="col-sm-6"> 
                             <?php if($pos_settings->active_repeat_customer_discount && $pos_settings->auto_apply_repeat_customer_discount =='0' ){ ?>
                            <input type="checkbox"  name="repeate_sales_discount" id="repeate_sales_discount">
                            <label for="repeate_sales_discount"> Apply Repeat Sales Discount </label>
                        <?php } ?>
                        <!-- //////////////////////////////////////////////// -->
                        <?php
                         $is_mobile = get_instance()->agent->is_mobile();
                         if ($is_mobile) { ?>
                            <div class="row" style="display: flex; align-items: baseline; margin: 15px 0 10px 0;flex-direction: row;justify-content: space-evenly;">
                                <div class="col-xs-12" style="padding-right: 0;">
                                     <div class="text-danger" id="showamtbalance" style="display:none; padding-left: 0; font-size: 14px; font-weight: bold;">
                                        <strong id="showdeposit" style="font-weight: 700;"></strong> <br/>
                                        <strong id="showawardpoint" style="font-weight: 700;"></strong> <br/>
                                        <strong id="showgiftcard" style="font-weight: 700;"></strong>
                                    </div>
                                    <div class="text-danger" id="showduebalance" style="display:none; padding-left: 0; font-size: 14px; font-weight: bold;">
                                        <strong id="showdue" style="font-weight: 700;"></strong> <br />
                                    </div>
                                </div>
                                <div class="col-xs-12" style="padding-left: 0; padding-right: 0; margin-right: -30em;">
                                    <?php if ($sms_limit == 0 || $sms_limit === false) { ?>
                                        <div class="custom-alert-danger" style="color: #a94442;  padding: 8px; border-radius: 4px;  text-align: center; font-size: 11px; margin-bottom: 0; "><i class="fa fa-exclamation-triangle"></i> Your SMS package is expired.<br>Please recharge with a valid SMS Package</div>
                                    <?php } ?>
                                </div>
                            </div>

                         <?php } else { ?>
                            <div class="container text-danger" id="showamtbalance" style="display:none">
                                <strong id="showawardpoint"></strong> <br/>
                                <strong id="showdeposit"></strong> <br/>
                                <strong id="showgiftcard"></strong>
                                
                            </div>
                            <div class="container text-danger" id="showduebalance" style="display:none">
                                <strong id="showdue"></strong> <br />
                            </div>
                        <?php } ?>
                         </div>
                            <div class="col-sm-6">
                                <?php
                                    if(!$is_mobile){
                                        if($sms_limit == 0){
                                            echo '<strong class="text-danger">  If SMS bal is 0 then (Your SMS package is expired. Please recharge with a valid SMS Package) </strong>';
                                        }elseif($sms_limit < 100){
                                            echo '<strong class="text-danger">  If SMS bal is less that 100 (Your SMS balance is low, SMS balance:- 98)</strong>';
                                        }
                                    }
                               ?>
                              
                            <!-- </div> -->
                        
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 col-sm-12" style="display: flex; gap: 6%;">
                            <div class="col-md-3 col-sm-6" id="verticalbtns">

                            <div class="denomination-wrapper denomination denom-theme-seven"id="denom" >
                                <!-- BILLS -->
                                <h4 class="text-center"><b>Bills</b></h4>
                                <div class="denomination-grid" id="bills-container"></div>
                                <!-- COINS -->
                                <h4 class="text-center"><b>Coins</b></h4>
                                <div class="denomination-grid" id="coins-container"></div>
                                <!-- CLEAR -->
                                <button type="button"class="btn btn-danger"id="clear-cash-notes"style="width:100%;"><?= lang('clear'); ?>
                                </button>

                            </div>

                        </div>
                          <div class="col-md-4 col-sm-5 col-xs-7">
                                    <div class="amount-outer">
                                    <div id="amnt" class="ps-container">
                                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                            <div class="form-group" style="margin:15px 0;">
                                                <!--?=lang("biller", "biller");?-->
                                                <?php
                                                foreach ($billers as $biller) {
                                                    $bl[$biller->id] = $biller->company != '-' ? $biller->name . '(' . $biller->company . ')' : $biller->name;
                                                }
                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                                ?>
                                            </div>
                                            <?php
                                        } else {
                                            $biller_input = array(
                                                'type' => 'hidden',
                                                'name' => 'biller',
                                                'id' => 'posbiller',
                                                'value' => $this->session->userdata('biller_id'),
                                            );
                                            echo form_input($biller_input);
                                        }
                                        ?>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-sm-6 col-xs-6">
                                                    <?= form_textarea('sale_note', '', 'id="sale_note" class="form-control kb-text skip" style="height: 35px;" placeholder="' . lang('sale_note') . '" maxlength="250"'); ?>
                                                </div>
                                                <div class="col-sm-6 col-xs-6">
                                                    <?= form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 35px;" placeholder="' . lang('staff_note') . '" maxlength="250"'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfir"></div>
                                        <div class="card-div" id="payments" style="cursor:pointer">
                                            <div class="well well-sm well_1">
                                                <div class="payment">
                                                    <div class="row">
                                                        <div class="col-sm-6 col-xs-6">
                                                            <div class="form-group">
                                                            <?= lang("amount", "amount_1"); ?>
                                                            <div class="input-group">
                                                                <input name="amount[]"type="text"id="amount_1"value="0"class="pa form-control kb-pad1 amount paidby_amount"onkeypress="return isNumberKey(event)"utocomplete="off" />
                                                                <span class="input-group-btn">
                                                                    <button type="button"id="edt"class="btn btn-default btn-edt"
                                                                            onclick="enDis('amount_1')"><i class="fa fa-pencil" style="font-size: 1.2em;"></i>
                                                                    </button>
                                                                </span>

                                                            </div>

                                                        </div>
                                                        </div>
                                                        <div class="col-sm-6 col-xs-6">
                                                            <div class="form-group">
                                                                <?= lang("paying_by", "paid_by_1"); ?>
                                                                <select name="paid_by[]" id="paid_by_1" class="form-control paid_by"></select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group gc_1" style="display: none;">
                                                                <?= lang("gift_card_no", "gift_card_no_1"); ?>
                                                                <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1" class="pa form-control kb-pad gift_card_no"/>
                                                                <div id="gc_details_1"></div>
                                                                <div id="errorgift_1"></div>
                                                            </div>
                                                             <!--Show Deposite Balance-->
                                                             <div class="form-group db_1" style="display:none;" >
                                                                <?= lang("Deposit Balance"); ?>
                                                                <div id="depositdetails_1"></div>
                                                                <div id="errordeposit_1"></div>
                                                            </div>
                                                            <div class="form-group ap_1" style="display:none;" >
                                                                <div id="apdetails_1"></div>
                                                                <div id="errorap_1"></div>
																<input type="hidden" name="ap[]" id="ap_1">
                                                            </div>
                                                            <!----->
                                                            <div class="display pcc_1" style="display:none;">
                                                                <!-- Card Number: <div id="cardNo"></div>-->
                                                                <div id="cardty" style="display: none;"></div>
                                                                <div class="row">
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="form-group">
                                                                            <input name="cc_transac_no[]" type="text" id="cc_transac_no_1"
                                                                                   class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                   placeholder="Transaction No."/>
                                                                        </div>
                                                                    </div>
                                                                </div>    
                                                                <div class="row">
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="form-group">
                                                                            <input name="cc_payment_other[]" type="text" id="cc_payment_other"
                                                                                   class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                   placeholder="Other"/>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="form-group">
                                                                    <input type="text" id="swipe_1" class="form-control swipe kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                            placeholder="<?= lang('swipe') ?>"/>
                                                            </div>
                                                            <div class="row">
                                                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                    <input name="cc_no[]" type="text" id="pcc_no_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('cc_no') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                    <input name="cc_holer[]" type="text" id="pcc_holder_1"
                                                                                            class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('cc_holder') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <select name="cc_type[]" id="pcc_type_1"  placeholder="<?= lang('card_type') ?>">
                                                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                                                            <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                                                            <option  value="Discover"><?= lang("Discover"); ?></option>
                                                                                    </select>
                                                                                     <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <input name="cc_month[]" type="text" id="pcc_month_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('month') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <input name="cc_year" type="text" id="pcc_year_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('year') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <input name="cc_cvv2" type="text" id="pcc_cvv2_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="cvv"/>
                                                                            </div>
                                                                    </div>
                                                            </div>-->
                                                            </div>
                                                            <div class="display pcheque_1" style="display:none;">
                                                                <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                                                    <input name="cheque_no[]" type="text" id="cheque_no_1"
                                                                           class="form-control cheque_no kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"/>
                                                                </div>
                                                            </div>
                                                            <div class="display pother_1" style="display:none;">
                                                                <div class="form-group">
                                                                    <input name="other_tran_no" placeholder="Transaction No" type="text" id="other_tran_no_1"
                                                                           class="form-control cheque_no kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"/>
                                                                </div>
                                                                <div class="form-group" id="note">
                                                                    <input name="other_tran_mode" placeholder="Transaction Mode" type="text" id="other_tran_mode_1"
                                                                           class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" maxlength="55"/>
                                                                </div>
                                                            </div>

                                                          

                                                            <div class="display form-group payment_note">
                                                                <?= lang('payment_note', 'payment_note'); ?>
                                                                <textarea name="payment_note[]" id="payment_note_1" class="pa form-control kb-text payment_note"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="multi-payment"></div>
                                        <button type="button" class="btn btn-primary col-md-12 addButton"><i class="fa fa-plus"></i> <?= lang('add_more_payments') ?></button>
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-7 col-sm-4 col-xs-6 text-center mobcard card-div">	
                                    <div class="row card-box ps-scrollbar-y" id="coinage-payment-methods" data-assets-base="<?= $assets ?>" <?= $is_mobile ? 'style="display:none;"' : '' ?>></div>
                                                  
                                    
                                             
                                    <?php if ($pos_settings->paynear == '1' && !empty($this->pos_settings->paynear_app)): ?>
                                        <div class="row card-box" id="paynear_btn_app_holder" style="display:none;">

                                            <div class="col-md-3 col-sm-6 col-xs-6">  
                                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn1" value="paynear" data-value="1"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico14.png" alt=""></span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-xs-6">  
                                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn2" value="paynear"  data-value="2"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico15.png" alt=""></span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-xs-6">  
                                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn3" value="paynear"  data-value="3"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico16.png" alt=""></span></label>
                                                </div>
                                            </div>

                                        </div>
                                    <?php endif; ?>
                                     
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="font16" style="margin-top: 10px;">
                                    <table class="table table-bordered table-condensed table-striped" id="totaltab" style="margin-bottom: 0;">
                                        <tbody>
                                            <tr>
                                                <td>Total Items</td>
                                                <td class="text-right"><span id="item_count">0.00</span></td>
                                                <td>Total Payable</td>
                                                <td class="text-right"><span id="twt">0.00</span></td>
                                                <td>Total Paying</td>
                                                <td class="text-right"><span id="total_paying">0.00</span></td>
                                                <td><?= lang("balance"); ?></td>
                                                <td class="text-right"><span id="balance" class="bal">0.00</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                           </div>
                        </div>
                      <div class="modal-footer">
                        <div class="btn-group col-sm-12 marginleft1  payments_mainsection checkoutmodalbtn hidden_div">                                
                            <button class="col-5 col-xs-4 btn btn-primary cmdnotprint marginLR10 final-submit-btn" name="cmd"  id="submit-sale"><strong>Quick <?= lang('submit'); ?></strong> <img src="<?= $assets ?>pos/images/submit.png" alt="submit"></button>                                 
                            <button class="col-5 col-xs-4 btn btn-info cmdprint marginLR10 borderradius padding10 final-submit-btn" name="cmdprint" id="submit-sale"><strong><?= lang('submit'); ?> & Print</strong> <img src="<?= $assets ?>pos/images/print.png" alt="submit"></button>                                 
                            <!-- hiding the split pay and split check if theme is clothing -->
                            <?php 
                                $allowed_types = ['restaurant', 'cafe', 'bakery'];
                                if (in_array($Settings->pos_type, $allowed_types)) { ?>
                            <button class="col-5 col-xs-4 btn btn-success splitpay marginLR10 borderradius padding10 final-submit-btn" name="splitpay" id="splitpay" onclick="split_order_pay()"><strong>Split Pay</strong> <img src="<?= $assets ?>pos/images/split-pay.png" alt="submit"></button>                                 
                            <button class="col-5 col-xs-4 btn btn-danger marginLR10 borderradius padding10 splitcheck final-submit-btn" type="button" onclick="split_order();" ><strong>Split Check</strong> <img src="<?= $assets ?>pos/images/split-check.png" alt="submit"></button>                                 
                            <?php } ?>
                            <button class="col-5 col-xs-4 btn cmdprint1 newblue marginLR10 padding10 final-submit-btn" name="cmdprint1" id="submit-sale"><strong>Other</strong> <img src="<?= $assets ?>pos/images/check.png" alt="submit"></button>
                            <!--  <a href="javascript:void(0);" onclick="return paynear_mobile_app()">Paynear APP</a> -->                                 
                        </div>
                    </div>
                    </div>
                <!-- Customer Details -->
                    <div class="modal-body hidden_div customerDetails">
                        <ul class="tab-nav sub-tab-nav">
                            <li><button id="showDivButton3">Profile</button></li>
                            <li><button id="showDivButton5">Addresses</button></li>
                            <li><button id="showDivButton4">Family & Relations</button></li>
                        </ul>
                    </div>
                    <div class="modal-body profile hidden_div" id="eventForm">
                        <?php
                        include_once 'themes/default/views/pos/edit_customer_details.php';
                        ?>
                    </div>
                     <div class="modal-body addressSection hidden_div" id="addressSection" style="top: -16px;">
                        <?php
                        include_once 'themes/default/views/pos/customer_addresses.php';
                        ?>
                    </div>
                    <div class="modal-body family_relation hidden_div" id="eventFormFamily">
                        <?php
                        include_once 'themes/default/views/pos/customer_family_relation.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("showDivButton1").classList.add("active");
                document.getElementById("showDivButton3").classList.add("active");

                const buttons = document.querySelectorAll(".tab-nav button");

                buttons.forEach(button => {
                    button.addEventListener("click", function() {
                        const parent = this.closest(".tab-nav");
                        const siblingButtons = parent.querySelectorAll("button");

                        siblingButtons.forEach(btn => btn.classList.remove("active"));

                        this.classList.add("active");

                        if (this.id === "showDivButton2") {
                            document.getElementById("showDivButton3").classList.add("active");
                            document.getElementById("showDivButton5").classList.remove("active");
                            document.getElementById("showDivButton4").classList.remove("active");
                        }

                        if (this.id === "showDivButton4" || this.id === "showDivButton3") {
                            if (this.id === "showDivButton4" || this.id === "showDivButton3" || this.id === "showDivButton5") {
                            document.getElementById("showDivButton2").classList.add("active");
                        }
                    });
                });
            });
        </script>
        <script>
// ==========================
// Dynamic payment methods (active only) for checkout modal
// Uses: pos/get_coinage_payment_methods
// ==========================
function pmEscapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (s) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[s];
    });
}

function pmPaidBySelect() {
    return $('#paymentModal #paid_by_1');
}

function pmCardsWrap() {
    return $('#paymentModal #coinage-payment-methods');
}

function pmSyncHiddenPaidBy(code) {
    var normalized = (code || '').trim();
    if (!normalized) {
        return;
    }
    // Main POS submit payload comes from hidden fields in add.php.
    $('#paid_by_val_1').val(normalized);
    $('#rpaidby').val(normalized);
}

function renderDynamicPaymentMethods(methods) {
    var list = Array.isArray(methods) ? methods : [];
    var $select = pmPaidBySelect();
    var $cards = pmCardsWrap();
    var optionHtml = '';
    var cardHtml = '';

    if (!$select.length || !$cards.length) {
        return;
    }

    list.forEach(function (method, idx) {
        var code = (method.code || '').trim();
        if (!code) {
            return;
        }
        var name = method.name || code;
        var iconUrl = method.icon_url || '<?= $assets ?>pos/images/NCash.svg';
        var iconClass = method.icon_class || (String(code).toLowerCase() === 'cash' ? 'whitebgredB' : 'whitebgblueB');
        var id = 'dynamic_pm_' + (method.id || idx);

        optionHtml += '<option value="' + pmEscapeHtml(code) + '">' + pmEscapeHtml(name) + '</option>';
        cardHtml += ''
            + '<div class="col-md-3 col-sm-6 col-xs-6">'
            + '  <div class="radio-div">'
            + '    <input type="radio" id="' + pmEscapeHtml(id) + '" class="card pm-payment-radio custom_payment_icon" name="pm_color_radio" value="' + pmEscapeHtml(code) + '">'
            + '    <label for="' + pmEscapeHtml(id) + '" class="payment-method"><span class="paddingTop ' + pmEscapeHtml(iconClass) + '">' + pmEscapeHtml(name) + '</span></label>'
            + '    <span class="payment-icon ' + pmEscapeHtml(iconClass) + '"><img src="' + pmEscapeHtml(iconUrl) + '" alt="" class="icon_image_option"></span>'
            + '  </div>'
            + '</div>';
    });

    if (!optionHtml) {
        optionHtml = '<option value="cash">Cash</option>';
    }
    if (!cardHtml) {
        cardHtml = ''
            + '<div class="col-md-3 col-sm-6 col-xs-6">'
            + '  <div class="radio-div">'
            + '    <input type="radio" id="dynamic_pm_cash_fallback" class="card pm-payment-radio custom_payment_icon" name="pm_color_radio" value="cash">'
            + '    <label for="dynamic_pm_cash_fallback" class="payment-method"><span class="paddingTop whitebgredB">Cash</span></label>'
            + '    <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/NCash.svg" alt="" class="icon_image_option"></span>'
            + '  </div>'
            + '</div>';
    }

    $select.html(optionHtml);
    $cards.html(cardHtml);
    refreshPaidBySelectUi();
}

function refreshPaidBySelectUi() {
    var $select = pmPaidBySelect();
    if (!$select.length || typeof $.fn.select2 !== 'function') {
        return;
    }
    try { $select.select2('destroy'); } catch (e) {}
    $select.select2({
        minimumResultsForSearch: 7,
        width: '100%'
    });
}

function syncPaymentSelection(code) {
    var normalized = (code || '').trim();
    if (!normalized) {
        return;
    }
    var $select = pmPaidBySelect();
    var $radio = pmCardsWrap().find('input.pm-payment-radio[value="' + normalized.replace(/"/g, '\\"') + '"]');

    if ($select.find('option[value="' + normalized.replace(/"/g, '\\"') + '"]').length) {
        $select.val(normalized).trigger('change').trigger('change.select2');
    }
    pmSyncHiddenPaidBy(normalized);
    if ($radio.length) {
        $radio.prop('checked', true).trigger('change');
    }
}

function pickDynamicDefault(methods, preferredCode) {
    var selected = '';
    var list = Array.isArray(methods) ? methods : [];
    list.some(function (method) {
        if (parseInt(method.is_default, 10) === 1 && method.code) {
            selected = method.code;
            return true;
        }
        return false;
    });
    if (!selected && preferredCode) {
        selected = String(preferredCode).trim();
    }
    if (!selected) {
        list.some(function (method) {
            if (String(method.code || '').toLowerCase() === 'cash') {
                selected = method.code;
                return true;
            }
            return false;
        });
    }
    if (!selected && list.length && list[0].code) {
        selected = list[0].code;
    }
    if (!selected) {
        selected = 'cash';
    }
    return selected;
}

function applyDynamicPaymentSelection(methods, preferredCode) {
    var list = Array.isArray(methods) ? methods : [];
    var hasDepositMethod = list.some(function (m) { return String(m.code || '').toLowerCase() === 'deposit'; });
    var customerId = parseInt($('#poscustomer').val(), 10) || 0;
    var fallbackCode = pickDynamicDefault(list, preferredCode);
    // Show selection immediately (no visible lag).
    syncPaymentSelection(fallbackCode);

    if (!hasDepositMethod || customerId < 1) {
        return;
    }
    $.ajax({
        type: 'GET',
        url: site.base_url + 'customers/getdeposit/' + customerId,
        dataType: 'json',
        success: function (result) {
            if (result && parseFloat(result.deposit) > 0) {
                syncPaymentSelection('deposit');
            }
        },
        error: function () {}
    });
}

function loadDynamicPaymentMethods() {
    var customerId = parseInt($('#poscustomer').val(), 10) || 0;
    $.ajax({
        type: 'GET',
        url: site.base_url + 'pos/get_coinage_payment_methods',
        data: { customer_id: customerId },
        dataType: 'json',
        success: function (response) {
            var methods = (response && response.status === 'success' && Array.isArray(response.methods)) ? response.methods : [];
            renderDynamicPaymentMethods(methods);
            applyDynamicPaymentSelection(methods, response ? response.preferred_code : null);
        },
        error: function () {
            renderDynamicPaymentMethods([]);
            syncPaymentSelection('cash');
        }
    });
}

// COINAGE UI — integrates with existing quick-cash flow from add.php
// + button  →  ADD denomination to #amount_1  (same as clicking a .quick-cash button)
// - button  →  SUBTRACT denomination from #amount_1
// Clear     →  reset counts + reset #amount_1 to 0  (same as #clear-cash-notes in add.php)
// ==========================

$(document).ready(function () {
    loadDynamicPaymentMethods();
    loadCoinageUI();
});

$('#paymentModal').on('shown.bs.modal', function () {
    loadDynamicPaymentMethods();
    setTimeout(function () {
        var $defaultRadio = pmCardsWrap().find('input.pm-payment-radio:checked');
        if ($defaultRadio.length) {
            pmSyncHiddenPaidBy($defaultRadio.val());
        }
    }, 300);
});

$(document).on('change', '#coinage-payment-methods input.pm-payment-radio', function () {
    var code = $(this).val();
    if (code) {
        pmSyncHiddenPaidBy(code);
        pmPaidBySelect().val(code).trigger('change').trigger('change.select2');
    }
});

$(document).on('click', '#coinage-payment-methods label.payment-method', function () {
    var inputId = $(this).attr('for');
    if (!inputId) {
        return;
    }
    var $radio = $('#' + inputId);
    if ($radio.length) {
        $radio.prop('checked', true).trigger('change');
    }
});

$(document).on('change', '#paid_by_1', function () {
    var code = $(this).val();
    if (!code) {
        return;
    }
    pmSyncHiddenPaidBy(code);
    var $radio = pmCardsWrap().find('input.pm-payment-radio[value="' + String(code).replace(/"/g, '\\"') + '"]');
    if ($radio.length) {
        $radio.prop('checked', true);
    }
});

function loadCoinageUI() {
    $.ajax({
        url: '<?= base_url("pos/getCoinage") ?>',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res.status) return;

            let bills = '';
            let coins = '';
            let seen  = {};

            $.each(res.data, function (i, item) {
                let amount = parseFloat(item.currency_value);
                if (isNaN(amount)) return;

                let key = item.type + '_' + amount;
                if (seen[key]) return;
                seen[key] = true;

                let box = `
                    <div class="denom-box" data-value="${amount}">
                        <div class="denom-value">${amount.toFixed(2)}</div>
                        <div class="denom-controls">
                            <button class="circle-btn minus" type="button">-</button>
                            <span class="count">0</span>
                            <button class="circle-btn plus" type="button">+</button>
                        </div>
                    </div>`;

                if (item.type === 'Bills') {
                    bills += box;
                } else if (item.type === 'Coins') {
                    coins += box;
                }
            });

            $('#bills-container').html(bills);
            $('#coins-container').html(coins);
            updateCashCursorOnly();
            let value = parseFloat($('#amount_1').val()) || 0;
            if (value > 0) {
                lockAmountInput(true);          // 🔒 lock input
                lockDenominationButtons(true);  // 🔒 disable + -
            } else {
                lockDenominationButtons(false); // 🔓 enable + -
            }
        },
        error: function () {
            console.warn('Coinage: failed to load denominations.');
        }
    });
}

// ==========================
// syncTotals — calls calculateTotals (add.php) so #balance_amount_1 hidden
// input is correctly POSTed and saved as pos_balance in the DB.
// Falls back to a local calculation if calculateTotals is not in scope.
// ==========================
function syncTotals() {
    if (typeof calculateTotals === 'function') {
        // Use the existing POS function — updates display AND the hidden
        // balance_amount[] input that is submitted with the sale form.
        calculateTotals('amount_1');
    } else {
        // Fallback: update display only (no hidden input update possible here)
        let twtMatch     = $('#twt').text().replace(/,/g, '').match(/(\d+(?:\.\d+)?)/);
        let totalPayable = twtMatch ? parseFloat(twtMatch[1]) : 0;
        let paying       = parseFloat($('#amount_1').val()) || 0;
        let balance      = paying - totalPayable;

        $('#total_paying').text(paying.toFixed(2));
        $('#balance')
            .text(balance.toFixed(2))
            .css('color', balance < 0 ? 'red' : 'black');

        // Also update the hidden input directly so the form value is correct
        let balVal = (typeof formatDecimal === 'function')
                        ? formatDecimal(balance)
                        : parseFloat(balance.toFixed(2));
        $('#balance_amount_1').val(balVal);
    }
}

// ==========================
// PLUS — add denomination to existing #amount_1 (mirrors .quick-cash click)
// ==========================
$(document).on('click', '.denom-box .plus', function () {
    lockAmountInput(true); // 🔥 LOCK amount input
    let box     = $(this).closest('.denom-box');
    let value   = parseFloat(box.data('value')) || 0;
    let countEl = box.find('.count');
    let count   = parseInt(countEl.text()) || 0;

    countEl.text(count + 1);

    let current = parseFloat($('#amount_1').val()) || 0;
    let newAmt  = (typeof formatDecimal === 'function')
                    ? formatDecimal(current + value)
                    : parseFloat((current + value).toFixed(2));

    $('#amount_1').val(newAmt).trigger('change');
    syncTotals();
    autoUnlockAmountIfZero();
});

// ==========================
// MINUS — subtract denomination from #amount_1
// ==========================
$(document).on('click', '.denom-box .minus', function () {
    lockAmountInput(true); // 🔥 LOCK amount input
    let box     = $(this).closest('.denom-box');
    let value   = parseFloat(box.data('value')) || 0;
    let countEl = box.find('.count');
    let count   = parseInt(countEl.text()) || 0;

    if (count <= 0) return;

    countEl.text(count - 1);

    let current = parseFloat($('#amount_1').val()) || 0;
    let newAmt  = Math.max(0, (typeof formatDecimal === 'function')
                    ? formatDecimal(current - value)
                    : parseFloat((current - value).toFixed(2)));

    $('#amount_1').val(newAmt).trigger('change');
    syncTotals();
    autoUnlockAmountIfZero();
    fixReadonlyOnZeroCount();
});

// ==========================
// CLEAR — resets counts + amount_1
// ==========================
$(document).on('click', '#clear-cash-notes', function () {
    $('.denom-box .count').text('0');
    $('#amount_1').val('0').trigger('change');
    lockAmountInput(false);       // 🔓 enable input
    lockDenominationButtons(false); // 🔓 enable + -
    syncTotals();
    autoUnlockAmountIfZero();
});

// ==========================
// MANUAL INPUT — keep totals in sync when user types directly
// ==========================
$(document).on('keyup', '#amount_1', function () {
    let value = parseFloat($(this).val()) || 0;

    if (value > 0) {
        lockDenominationButtons(true); // 🔥 disable + -
    } else {
        lockDenominationButtons(false);
    }
    syncTotals();
});

function lockAmountInput(lock = true) {
    $('#amount_1').prop('readonly', lock);
}

function lockDenominationButtons(lock = true) {
    $('.denom-box .plus, .denom-box .minus').prop('disabled', lock);
}

$('#paymentModal').on('shown.bs.modal', function () {
    let value = parseFloat($('#amount_1').val()) || 0;

    if (value > 0) {
        lockDenominationButtons(true);  // 🔒 disable + -
    } else {
        lockDenominationButtons(false); // 🔓 enable + -
    }
});
function autoUnlockAmountIfZero() {
    let totalCount = 0;

    $('.denom-box .count').each(function () {
        totalCount += parseInt($(this).text()) || 0;
    });

    let amount = parseFloat($('#amount_1').val()) || 0;

    // 👉 ONLY condition to unlock
    if (totalCount === 0 && amount === 0) {
        $('#amount_1').prop('readonly', false); // 🔓 unlock ONLY here
    }
}
// ==========================
// CASH CURSOR CONTROL (ADD ONLY)
// ==========================

function updateCashCursorOnly() {
    let method = pmPaidBySelect().val(); // actual payment source

    if (method !== 'cash') {
        $('#bills-container, #coins-container').addClass('cash-not-allowed');
    } else {
        $('#bills-container, #coins-container').removeClass('cash-not-allowed');
    }
}

// trigger when payment method changes
$(document).on('change', '#paid_by_1', function () {
    updateCashCursorOnly();
});

// trigger after modal opens
$('#paymentModal').on('shown.bs.modal', function () {
    updateCashCursorOnly();
});

function fixReadonlyOnZeroCount() {
    let totalCount = 0;

    $('.denom-box .count').each(function () {
        totalCount += parseInt($(this).text()) || 0;
    });

    if (totalCount === 0) {
        $('#amount_1').prop('readonly', false); // 🔓 force unlock
    }
}
$(document).on('click', '.denom-box .minus', function () {
    setTimeout(function () {
        fixReadonlyOnZeroCount();
    }, 0);
});
        </script>