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
    .payment-setting {
        display: none;
    }

    .tab-nav button {
        background: #FFFFFF;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        transition: 0.3s ease-in-out;
        
    }

    .tab-nav button.active {
        background: #009DFF;
        color: white;
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
</style>

<div class="modal fade in payment-setting" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-times-circle" aria-hidden="true"></i>
                    </span><span class="sr-only"><?= lang('close'); ?></span></button>
                <!-- <h2 class="modal-title" id="payModalLabel"><?= lang('finalize_sale'); ?></h2> -->
                <ul class="tab-nav">
                <?php if ($pos_settings->display_CRM) { ?>
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
                        <?php if ($pos_settings->active_repeat_customer_discount && $pos_settings->auto_apply_repeat_customer_discount == '0') { ?>
                            <input type="checkbox" name="repeate_sales_discount" id="repeate_sales_discount">
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
                              
                            </div>
                        
                        </div>

                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="col-md-1 col-sm-3" id="verticalbtns">
                            <div class="class-title" style="font-weight: bold;"><?= lang('quick_cash'); ?></div>
                            <div class="btn-group btn-group-vertical">
                                <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable"><i class="fa fa-inr" aria-hidden="true"></i> 0.00 </button>
                                <?php
                                foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                    if ($cash_note_amount != 1000 && $cash_note_amount != 5000) {
                                        echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . '<i class="fa fa-inr" aria-hidden="true"></i>' . ' ' . $cash_note_amount . '</button>';
                                    }
                                }
                                ?>
                                <button type="button" class="btn btn-lg btn-danger" id="clear-cash-notes"><?= lang('clear'); ?></button>
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
                                                            <input name="amount[]" type="text" id="amount_1" class="pa form-control kb-pad1 amount paidby_amount" onKeyPress="return isNumberKey(event)" autocomplete="off" />
                                                            <button id="edt" class="btn-edt" onClick="enDis('amount_1')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 col-xs-6">
                                                        <div class="form-group">
                                                                <?= lang("paying_by", "paid_by_1"); ?>
                                                                <select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                                    <?= $this->sma->paid_opts(); ?>
                                                                    <?php if (get_instance()->agent->is_mobile()) { ?>
                                                                       <option value="payswiff"><?= lang("Payswiff") ?></option>
                                                                       <option value="ppp"><?= lang("paypal_pro") ?></option>
                                                                       <option value="stripe"><?= lang("stripe") ?></option>
                                                                       <option value="authorize"><?= lang("authorize") ?></option>
                                                                       <option value="instamojo">Instamojo</option>
                                                                       <option value="ccavenue">CCavenue</option>
                                                                       <option value="paytm">Paytm PG</option>
                                                                       <option value="paynear">Paynear</option>
                                                                       <option value="payumoney">Payumoney</option>
                                                                       <option value="award_point">Award Point</option>
                                                                       <option value="razorpay">Razorpay</option>
                                                                    <?php } else { ?>
                                                                <?= '<option value="payswiff">' . lang("Payswiff") . '</option>'; ?>
                                                                <?= $pos_settings->paypal_pro ? '<option value="ppp">' . lang("paypal_pro") . '</option>' : ''; ?>
                                                                <?= $pos_settings->stripe ? '<option value="stripe">' . lang("stripe") . '</option>' : ''; ?>
                                                                <?= $pos_settings->authorize ? '<option value="authorize">' . lang("authorize") . '</option>' : ''; ?>
                                                                <?php echo (isset($pos_settings->instamojo) && $pos_settings->instamojo == '1') ? ' <option value="instamojo">Instamojo</option>' : ''; ?>
                                                                <?php echo (isset($pos_settings->ccavenue) && $pos_settings->ccavenue == '1') ? ' <option value="ccavenue">CCavenue</option>' : ''; ?>
                                                                <?php echo (isset($pos_settings->paytm) && $pos_settings->paytm == '1') ? ' <option value="paytm">Paytm PG</option>' : ''; ?>

                                                                <!--<?php echo (isset($pos_settings->paytm_opt) && $pos_settings->paytm_opt == '1') ? ' <option value="paytm">Paytm</option>' : ''; ?>-->
                                                                <?php echo (isset($pos_settings->paynear) && $pos_settings->paynear == '1') ? ' <option value="paynear">Paynear</option>' : ''; ?>
                                                                <?php echo (isset($pos_settings->payumoney) && $pos_settings->payumoney == '1') ? ' <option value="payumoney">Payumoney</option>' : ''; ?>

                                                                <?php echo (isset($pos_settings->UPI_QRCODE) && $pos_settings->UPI_QRCODE == '1') ? ' <option value="UPI_QRCODE">UPI & QR CODE</option>' : ''; ?>
                                                                <?php echo (isset($pos_settings->award_point) && $pos_settings->award_point == '1') ? ' <option value="award_point">Award Point</option>' : ''; ?>
                                                                <?= '<option value="razorpay">' . lang("Razorpay") . '</option>'; ?>
                                                                <?php } ?>  
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="form-group gc_1" style="display: none;">
                                                            <?= lang("gift_card_no", "gift_card_no_1"); ?>
                                                            <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1" class="pa form-control kb-pad gift_card_no" />
                                                            <div id="gc_details_1"></div>
                                                            <div id="errorgift_1"></div>
                                                        </div>
                                                        <!--Show Deposite Balance-->
                                                        <div class="form-group db_1" style="display:none;">
                                                            <?= lang("Deposit Balance"); ?>
                                                            <div id="depositdetails_1"></div>
                                                            <div id="errordeposit_1"></div>
                                                        </div>
                                                        <div class="form-group ap_1" style="display:none;">
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
                                                                            placeholder="Transaction No." />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                                    <div class="form-group">
                                                                        <input name="cc_payment_other[]" type="text" id="cc_payment_other"
                                                                            class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                            placeholder="Other" />
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
                                                                    class="form-control cheque_no kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" />
                                                            </div>
                                                        </div>
                                                        <div class="display pother_1" style="display:none;">
                                                            <div class="form-group">
                                                                <input name="other_tran_no" placeholder="Transaction No" type="text" id="other_tran_no_1"
                                                                    class="form-control cheque_no kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" />
                                                            </div>
                                                            <div class="form-group" id="note">
                                                                <input name="other_tran_mode" placeholder="Transaction Mode" type="text" id="other_tran_mode_1"
                                                                    class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" maxlength="55" />
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
                            <div class="row card-box ps-scrollbar-y" <?= $is_mobile ? 'style="display:none;"' : '' ?>>
                                <div class="col-md-3 col-sm-6 col-xs-6">
                                    <div class="radio-div">
                                        <input type="radio" id="checkbox1" class="card custom_payment_icon" name="colorRadio" checked value="cash">
                                        <label for="checkbox1" class="payment-method"><span class="paddingTop whitebgredB">Cash</span></label>
                                        <span class="whitebgredB payment-icon">
                                            <img class="icon_image_option" src="<?= $assets ?>pos/images/cashh.png" alt="" class="icon_image_option2"></span></label>
                                    </div>
                                </div>
                                <?php if ($pos_settings->Cheque == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input type="radio" id="checkbox2" class="card custom_payment_icon" name="colorRadio" value="Cheque">
                                            <label for="checkbox2" class="payment-method"><span class="paddingTop whitebgredB">Cheque</span></label>
                                            <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/cheque.jpg" alt="" class="icon_image_option"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pos_settings->award_point == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input type="radio" id="checkbox18" class="card custom_payment_icon" name="colorRadio" value="award_point">
                                            <label for="checkbox18" class="payment-method"><span class="paddingTop whitebgredB">Award Point</span></label>
                                            <span class="payment-icon whitebgredB"> <img src="<?= $assets ?>pos/images/Deposit.png" alt="" class="icon_image_option"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pos_settings->deposit == '1'): ?>

                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input type="radio" id="checkbox3" class="card custom_payment_icon" name="colorRadio" value="deposit">
                                            <label for="checkbox3" class="payment-method"><span class="paddingTop whitebgredB">Deposit</span></label>
                                            <span class="payment-icon whitebgredB"> <img src="<?= $assets ?>pos/images/Deposit.png" alt="" class="icon_image_option"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-3 col-sm-6 col-xs-6">
                                    <div class="radio-div">
                                        <input type="radio" id="checkbox4" class="card custom_payment_icon" name="colorRadio" value="other">
                                        <label for="checkbox4" class="payment-method"><span class="paddingTop whitebgredB">Other</span></label>
                                        <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/other.png" alt="" class="icon_image_option"></span>
                                    </div>
                                </div>
                                <?php if ($pos_settings->gift_card == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input id="checkbox5" type="radio" class="card custom_payment_icon" name="colorRadio" value="gift_card">
                                            <label for="checkbox5" class="payment-method"><span class="paddingTop orangebg">Gift Card</span></label>
                                            <span class="payment-icon orangebg"><img src="<?= $assets ?>pos/images/gift-card1.jpg" alt="" class="icon_image_option"> </span></label>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pos_settings->neft == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input id="checkbox6" type="radio" class="card custom_payment_icon" name="colorRadio" value="NEFT">
                                            <label for="checkbox6" class="payment-method"><span class="paddingTop orangebg">NEFT</span></label>
                                            <span class="payment-icon orangebg"><img src="<?= $assets ?>pos/images/neft.png" alt="" class="icon_image_option2"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pos_settings->debit_card == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input id="checkbox7" title="Debit Card" type="radio" class="card custom_payment_icon" name="colorRadio" value="DC">
                                            <label for="checkbox7" class="payment-method">
                                                <span class="paddingTop whitebgblueB">Debit Card</span></label>
                                            <span class="payment-icon whitebgblueB"><img src="<?= $assets ?>pos/images/debit.png" alt="debit" class="icon_image_option"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pos_settings->credit_card == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input id="checkbox8" title="Credit Card" type="radio" class="card custom_payment_icon" name="colorRadio" value="CC">
                                            <label for="checkbox8" class="payment-method">
                                                <span class="paddingTop whitebgblueB">Credit Card</span></label>
                                            <span class="payment-icon whitebgblueB"><img src="<?= $assets ?>pos/images/creaditcard.jpg" alt="creaditcard" class="icon_image_option"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input title="Payswiff" type="radio" class="card custom_payment_icon" name="colorRadio" id="payswiff" value="payswiff">
                                            <label for="payswiff" class="payment-method">
                                                <span class="paddingTop whitebgblueB">Pay Swiff</span></label>
                                            <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/paysweef.jpg" class="icon_image_option" alt="credit-card">
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pos_settings->paytm == '1'): ?>
                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input id="checkbox9" type="radio" class="card custom_payment_icon" name="colorRadio" value="paytm">
                                            <label for="checkbox9" class="payment-method">
                                                <span class="paddingTop whitebgblueB">Paytm Gateway</span>
                                            </label>
                                            <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/patm.png" class="icon_image_option" alt="credit-card""></span>
                                                </div>
                                            </div>
                                         <?php endif; ?>

                                         <?php if ($pos_settings->paytm_opt == '1'): ?>
                                            <div class=" col-md-3 col-sm-6 col-xs-6">
                                                <div class="radio-div">
                                                    <input id="checkbox20" type="radio" class="card custom_payment_icon" name="colorRadio" value="PAYTM">
                                                    <label for="checkbox20" class="payment-method">
                                                        <span class="paddingTop whitebgblueB">PAYTM</span>
                                                    </label>
                                                    <span class="payment-icon whitebgblueB">
                                                        <img src="<?= $assets ?>pos/images/patm.png" class="icon_image_option" alt="credit-card""></span>
                                                </div>
                                            </div>
                                         <?php endif; ?>                                        

                                        <?php if ($pos_settings->google_pay == '1'): ?>
                                            <div class=" col-md-3 col-sm-6 col-xs-6">
                                                        <div class="radio-div">
                                                            <input id="checkbox10" title="Google pay" type="radio" class="card custom_payment_icon" name="colorRadio" value="Googlepay">
                                                            <label for="checkbox10" class="payment-method">
                                                                <span class="paddingTop whitebgblueB">Google pay</span>
                                                            </label>
                                                            <span class="payment-icon whitebgblueB">
                                                                <img src="<?= $assets ?>pos/images/googlepay.jpg" class="icon_image_option" alt="googlepay">
                                                            </span>
                                                        </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->swiggy == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox11" title="Swiggy" type="radio" class="card custom_payment_icon" name="colorRadio" value="swiggy">
                                                        <label for="checkbox11" class="payment-method"><span class="paddingTop whitebgblueB">Swiggy</span></label>
                                                        <span class="payment-icon whitebgblueB">
                                                            <img src="<?= $assets ?>pos/images/swiggy.png" class="icon_image_option1">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($pos_settings->zomato == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox12" title="Zomato" type="radio" class="card custom_payment_icon" name="colorRadio" value="zomato">
                                                        <label for="checkbox12" class="payment-method"><span class="paddingTop whitebgblueB">zomato</span></label>
                                                        <span class="payment-icon whitebgblueB">
                                                            <img src="<?= $assets ?>pos/images/zomato.png" class="icon_image_option">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->ubereats == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox13" title="Ubereats" type="radio" class="card custom_payment_icon" name="colorRadio" value="ubereats">
                                                        <label for="checkbox13" class="payment-method"><span class="paddingTop whitebgblueB">ubereats</span></label>
                                                        <span class="payment-icon whitebgblueB">
                                                            <img src="<?= $assets ?>pos/images/ubereats.jpg" class="icon_image_option">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($pos_settings->magicpin == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox14" title="Magicpin" type="radio" class="card custom_payment_icon" name="colorRadio" value="magicpin">
                                                        <label for="checkbox14" class="payment-method"><span class="paddingTop whitebgblueB">magicpin</span></label>
                                                        <span class="payment-icon whitebgblueB">
                                                            <img src="<?= $assets ?>pos/images/magicpin.png" class="icon_image_option">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($pos_settings->complimentary == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox14" title="Debit Card" type="radio" class="card custom_payment_icon" name="colorRadio" value="complimentry">
                                                        <label for="checkbox14" class="payment-method"><span class="paddingTop whitebgblueB">Complimentry</span></label>
                                                        <span class="payment-icon whitebgblueB">
                                                            <img src="<?= $assets ?>pos/images/credit-card.jpg" class="icon_image_option2" alt="credit-card">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($pos_settings->UPI_QRCODE == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox16" title="UPI & QR CODE" type="radio" class="card custom_payment_icon" name="colorRadio" value="UPI_QRCODE">
                                                        <label for="checkbox16" class="payment-method"><span class="paddingTop whitebgblueB">UPI & QR CODE</span></label>
                                                        <span class="payment-icon whitebgblueB">
                                                            <img src="<?= $assets ?>pos/images/credit-card.jpg" class="icon_image_option2" alt="credit-card">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($pos_settings->paypal_pro == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox15" type="radio" class="card custom_payment_icon" name="colorRadio" value="ppp">
                                                        <label for="checkbox15" class="payment-method"><span class="paddingTop whitebgredB">PayPal</span></label>
                                                        <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/paypal.jpg" class="icon_image_option" alt=""></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->stripe == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox16" type="radio" class="card custom_payment_icon" name="colorRadio" value="stripe">
                                                        <label for="checkbox16" class="payment-method"><span class="paddingTop orangebg">Stripe</span></label>
                                                        <span class="payment-icon orangebg ">
                                                            <img src="<?= $assets ?>pos/images/Stripe.png" class="icon_image_option1" alt=""></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->authorize == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox17" type="radio" class="card custom_payment_icon" name="colorRadio" value="authorize">
                                                        <label for="checkbox17" class="payment-method"><span class="paddingTop orangebg">Authorize</span></label>
                                                        <span class="payment-icon orangebg"><img src="<?= $assets ?>pos/images/authorize.png" class="icon_image_option1" alt=""></span>
                                                    </diV>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->instamojo == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox18" type="radio" class="card custom_payment_icon" name="colorRadio" value="instamojo">
                                                        <label for="checkbox18" class="payment-method"><span class="paddingTop whitebg">Instamojo</span></label>
                                                        <span class="payment-icon whitebg"><img src="<?= $assets ?>pos/images/instamojo.jpg" class="icon_image_option" alt="">
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->ccavenue == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input id="checkbox19" type="radio" class="card custom_payment_icon" name="colorRadio" value="ccavenue">
                                                        <label for="checkbox19" class="payment-method"><span class="paddingTop whitebgredB">CCavenue</span></label>
                                                        <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/ccavenue.png" class="icon_image_option" alt=""></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>



                                            <?php if ($pos_settings->paynear == '1' && !empty($this->pos_settings->paynear_web)): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6" id="paynear_btn_holder">
                                                    <div class="radio-div">
                                                        <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn" value="paynear">
                                                        <label for="paynear_btn" class="payment-method"><span class="paddingTop whitebgredB">Paynear</span></label>
                                                        <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/paynear.png" class="icon_image_option1" alt=""></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($pos_settings->payumoney == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6" id="payumoney_btn_holder">
                                                    <div class="radio-div">
                                                        <input type="radio" class="card custom_payment_icon" name="colorRadio" id="payumoney_btn" value="payumoney">
                                                        <label for="payumoney_btn" class="payment-method"><span class="paddingTop whitebgredB">Payumoney</span></label>
                                                        <span class="payment-icon whitebgredB"><img src="<?= $assets ?>pos/images/payu.png" class="icon_image_option1" alt=""></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($pos_settings->razorpay == '1'): ?>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div">
                                                        <input type="radio" id="checkbox21" class="card custom_payment_icon" name="colorRadio" value="razorpay">
                                                        <label for="checkbox21" class="payment-method"><span class="paddingTop whitebgredB">Razorpay</span></label>
                                                        <span class="payment-icon whitebgredB"> <img src="<?= $assets ?>pos/images/razorpay.svg" alt="" class="icon_image_option"></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>



                                        <?php if ($pos_settings->paynear == '1' && !empty($this->pos_settings->paynear_app)): ?>
                                            <div class="row card-box" id="paynear_btn_app_holder" style="display:none;">

                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                        <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn1" value="paynear" data-value="1"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico14.png" alt=""></span></label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                        <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn2" value="paynear" data-value="2"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico15.png" alt=""></span></label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                    <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                        <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn3" value="paynear" data-value="3"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico16.png" alt=""></span></label>
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
                    <div class="modal-footer payments_mainsection hidden_div">
                        <div class="btn-group col-sm-12 marginleft1 checkoutmodalbtn">
                            <button class="col-5 col-xs-4 btn btn-primary cmdnotprint marginLR10 final-submit-btn" name="cmd" id="submit-sale"><strong>Quick <?= lang('submit'); ?></strong> <img src="<?= $assets ?>pos/images/submit.png" alt="submit"></button>
                            <button class="col-5 col-xs-4 btn btn-info cmdprint marginLR10 borderradius padding10 final-submit-btn" name="cmdprint" id="submit-sale"><strong><?= lang('submit'); ?> & Print</strong> <img src="<?= $assets ?>pos/images/print.png" alt="submit"></button>
                            <!-- hiding the split pay and split check if theme is clothing -->
                            <?php 
                                $allowed_types = ['restaurant', 'cafe', 'bakery'];
                                if (in_array($Settings->pos_type, $allowed_types)) { ?>
                            <button class="col-5 col-xs-4 btn btn-success splitpay marginLR10 borderradius padding10 final-submit-btn" name="splitpay" id="splitpay" onclick="split_order_pay()"><strong>Split Pay</strong> <img src="<?= $assets ?>pos/images/split-pay.png" alt="submit"></button>
                            <button class="col-5 col-xs-4 btn btn-danger marginLR10 borderradius padding10 splitcheck final-submit-btn" type="button" onclick="split_order();"><strong>Split Check</strong> <img src="<?= $assets ?>pos/images/split-check.png" alt="submit"></button>
                            <?php } ?>
                            <button class="col-5 col-xs-4 btn cmdprint1 newblue marginLR10 padding10 final-submit-btn" name="cmdprint1" id="submit-sale"><strong>Other</strong> <img src="<?= $assets ?>pos/images/check.png" alt="submit"></button>
                            <!--  <a href="javascript:void(0);" onclick="return paynear_mobile_app()">Paynear APP</a> -->
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