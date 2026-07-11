 <?php if ((isset($Settings->theme) && $Settings->theme == 'theme_three') && isset($pos_settings->display_CRM) && (int)$pos_settings->display_CRM === 1) { ?>
 <script type="text/javascript" src="<?= $assets ?>pos/js/customer_family_relation.js?v=20260408_1"></script>
 <script type="text/javascript" src="<?= $assets ?>js/customer_add_address_modal.js?v=20260630_2"></script>
 <script type="text/javascript" src="<?= $assets ?>pos/js/edit_customer_details.js?v=20260620"></script>
 <link rel="stylesheet" href="<?= $assets ?>pos/css/customer_relation.css" type="text/css" />
 <style>
   #paymentModal .modal-content { border-radius: 6px; }
   #paymentModal .modal-body { max-height: calc(100vh - 220px); overflow-y: auto; }
   #paymentModal .customerDetails,
   #paymentModal .modal-body.profile,
   #paymentModal .modal-body.addressSection,
   #paymentModal .modal-body.family_relation { padding: 10px 15px; }
   #paymentModal .sub-tab-nav { margin: 8px 0 10px; }
   #paymentModal .panel { margin-bottom: 15px; }
   #paymentModal .table { width: 100%; }
   #paymentModal .table td { vertical-align: middle; }
   #paymentModal .select2-container { width: 100% !important; }
   #paymentModal .form-control { max-width: 100%; }
   /* Widen only when Customer Details is active */
   #paymentModal .modal-dialog.modal-lg.wide-modal { max-width: 1024px; width: 100%; }
   .editsty {
    background-color: transparent !important;
    color: #007bff !important;
    border: 1px solid #007bff !important;
    padding: 4px 29px !important;
    font-size: 13px !important;
    border-radius: 3px !important;
    margin-top: -2rem !important;
    margin-left: -1em;
}
div#amnt {
    height: 250px;
    position: absolute;
    overflow: hidden;
    width: 325px;
    padding: 10px 8px 5px 8px;
    background: #f6f6f6 none repeat scroll 0 0;
    border: 1px solid #ddd;
    margin: -15px 0 0!important;
}
@media (min-width: 1441px) and (max-width: 1600px) {
    .form-container {
        gap: 20px!important;
        padding: 8px;
        margin-left: 10px;
    }
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
<?php } ?>
 <div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-times-circle" aria-hidden="true"></i>
                            </span><span class="sr-only"><?= lang('close'); ?></span></button>
                        <?php if ((isset($Settings->theme) && $Settings->theme == 'theme_three') && isset($pos_settings->display_CRM) && (int)$pos_settings->display_CRM === 1) { ?>
                            <ul class="tab-nav">
                                <li><button id="showDivButton1" class="active">Payment</button></li>
                                <li><button id="showDivButton2">Customer Details</button></li>
                            </ul>
                        <?php } else { ?>
                            <h4 class="modal-title" id="payModalLabel"><?= lang('finalize_sale'); ?></h4>
                        <?php } ?>
                    </div>
                    <div class="modal-body<?= ((isset($Settings->theme) && $Settings->theme == 'theme_three') && isset($pos_settings->display_CRM) && (int)$pos_settings->display_CRM === 1) ? ' payments_mainsection' : '' ?>" id="payment_content">
                        <!-- //////////////////////////////////////////////// -->
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="class-title" style="font-weight: bold;"><?= lang('quick_cash'); ?></div>
                                <div class="btn-group btn-group-vertical">
                                    <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable">0.00 </button>
                                    <?php
                                    foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                        if ($cash_note_amount != 1000 && $cash_note_amount != 5000) {
                                            echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . $cash_note_amount . '</button>';
                                        }
                                    }
                                    ?>
                                    <button type="button" class="btn btn-lg btn-danger" id="clear-cash-notes"><?= lang('clear'); ?></button>
                                </div>
                            </div>
                        </div>
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
                                </div>
                                <div class="col-xs-12" style="padding-left: 0; padding-right: 0; margin-right: -30em;">
                                    <?php if ($sms_limit == 0 || $sms_limit === false) { ?>
                                        <div class="custom-alert-danger" style="color: #a94442;  padding: 8px; border-radius: 4px;  text-align: center; font-size: 11px; margin-bottom: 0; "><i class="fa fa-exclamation-triangle"></i> Your SMS package is expired.<br>Please recharge with a valid SMS Package</div>
                                    <?php } ?>
                                </div>
                            </div>

                         <?php } else { ?>
                             <div class="container text-danger" id="showamtbalance" style="display:none">
                                <strong id="showdeposit"></strong> <br/>
                                <strong id="showawardpoint"></strong> <br/>
                                <strong id="showgiftcard"></strong>
                            </div>
                            <?php
                            if ($sms_limit == 0) {
                                echo '<strong class="text-danger">  If SMS bal is 0 then (Your SMS package is expired. Please recharge with a valid SMS Package) </strong>';
                            } elseif ($sms_limit < 100) {
                                echo '<strong class="text-danger">If SMS bal is less that 100 (Your SMS balance is low, SMS balance:- 98) </strong>';
                            }
                            ?>
                         <?php } ?>
                         
                         <?php if($pos_settings->active_repeat_customer_discount && $pos_settings->auto_apply_repeat_customer_discount =='0' ){ ?>
                            <input type="checkbox"  name="repeate_sales_discount" id="repeate_sales_discount">
                            <label for="repeate_sales_discount"> Apply Repeat Sales Discount </label>
                        <?php } ?>

                        
                        <div class="row">
                            <div class="amount-outer">
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <div id="amnt" class="ps-container">
                                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                            <div class="form-group" style="margin:0;">
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
                                        <div id="payments" style="cursor:">
                                            <div class="well well-sm well_1">
                                                <div class="payment">
                                                    <div class="row">
                                                        <div class="col-sm-6 col-xs-6">
                                                            <div class="form-group">
                                                                <?= lang("amount", "amount_1"); ?>
                                                                <input name="amount[]" type="text" id="amount_1"  class="pa form-control kb-pad1 amount paidby_amount" onKeyPress="return isNumberKey(event)" autocomplete="off"/>
                                                                <button id="edt" class="btn-edt" onClick="enDis('amount_1')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-xs-6">
                                                            <div class="form-group">
                                                                <?= lang("paying_by", "paid_by_1"); ?>
                                                                <select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                                    <?php
                                                                    if (isset($payment_methods) && is_array($payment_methods)) {
                                                                        foreach ($payment_methods as $pm) {
                                                                            echo '<option value="' . htmlspecialchars($pm['code']) . '">' . htmlspecialchars($pm['name']) . '</option>';
                                                                        }
                                                                    } else {
                                                                        echo $this->sma->paid_opts();
                                                                    }
                                                                    ?>
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
                                                                    <?php echo (isset($pos_settings->paytm_opt) && $pos_settings->paytm_opt== '1') ? ' <option value="paytm">Paytm PG</option>' : ''; ?>
<!--<?php echo (isset($pos_settings->paytm) && $pos_settings->paytm == '1') ? ' <option value="paytm">Paytm</option>' : ''; ?>-->
                                                                    <?php echo (isset($pos_settings->paynear) && $pos_settings->paynear == '1') ? ' <option value="paynear">Paynear</option>' : ''; ?>
                                                                    <?php echo (isset($pos_settings->payumoney) && $pos_settings->payumoney == '1') ? ' <option value="payumoney">Payumoney</option>' : ''; ?>
                                                                     <?php echo (isset($pos_settings->UPI_QRCODE) && $pos_settings->UPI_QRCODE == '1') ? ' <option value="UPI_QRCODE">UPI & QR Code</option>' : ''; ?>
                                        <?php echo (isset($pos_settings->award_point) && $pos_settings->award_point == '1') ? ' <option value="award_point">Award Point</option>' : ''; ?>

                                                                    <?php } ?>
                                                                </select>
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
                                <div class="col-md-5 col-sm-5 col-xs-5 text-center card-div">	
                                    <div class="row card-box" <?= $is_mobile ? 'style="display:none;"' : '' ?>>
                                        <?php
                                        foreach ($payment_methods as $pm) {
                                            $pm_code = $pm['code'];
                                            $pm_name = $pm['name'];
                                            $pm_icon = $pm['icon'];
                                            
                                            if (!empty($pm_icon) && strpos($pm_icon, '://') === false) {
                                                $icon_url = $assets . 'pos/images/' . ltrim($pm_icon, '/');
                                            } else {
                                                $icon_url = !empty($pm_icon) ? $pm_icon : $assets . 'pos/images/NCash.svg';
                                            }
                                            
                                            $checked = ($pm['is_default'] == 1) ? 'checked="checked"' : '';
                                            ?>
                                            <div class="col-md-4 col-sm-4 col-xs-4">
                                                <div class="radio-div" data-toggle="tooltip" title="<?= htmlspecialchars($pm_name) ?>">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" <?= $checked ?> value="<?= htmlspecialchars($pm_code) ?>">
                                                    <label for="checkbox1">
                                                        <span>
                                                            <?php if (preg_match('/\.(svg|png|gif|jpe?g)$/i', $pm_icon)) { ?>
                                                                <img src="<?= $icon_url ?>" alt="<?= htmlspecialchars($pm_name) ?>">
                                                            <?php } else { ?>
                                                                <span style="padding:12px 0px 13px 0px; color:#fff; font-size: 12px; display: block; text-align: center;"><?= htmlspecialchars($pm_name) ?></span>
                                                            <?php } ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                                       
                                    <?php if ($pos_settings->paynear == '1' && !empty($this->pos_settings->paynear_app)): ?>
                                        <div class="row card-box" id="paynear_btn_app_holder" style="display:none;">

                                            <div class="col-md-4 col-sm-4 col-xs-4">  
                                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn1" value="paynear" data-value="1"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico14.png" alt=""></span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4">  
                                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn2" value="paynear"  data-value="2"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico15.png" alt=""></span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4">  
                                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                                    <input type="radio" class="card custom_payment_icon" name="colorRadio" id="paynear_btn3" value="paynear"  data-value="3"><label for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico16.png" alt=""></span></label>
                                                </div>
                                            </div>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="font16" style="margin-top: 17px;">
                                    <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
                                        <tbody>
                                            <tr>
                                                <td>Total<br />Items</td>
                                                <td class="text-right"><span id="item_count">0.00</span></td>
                                                <td>Total<br />Payable</td>
                                                <td class="text-right"><span id="twt">0.00</span></td>
                                                <td>Total<br />Paying</td>
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
                        <div class="payments_mainsection checkoutmodalbtn hidden_div">                                
                            <button class="col-5 col-xs-4 btn btn-primary cmdnotprint final-submit-btn" name="cmd"  id="submit-sale">Quick <?= lang('submit'); ?></button>                                 
                            <button class="col-5 col-xs-4 btn btn-primary cmdprint final-submit-btn" name="cmdprint" id="submit-sale"><?= lang('submit'); ?> & Print</button>                                 
                            <!-- hiding the split pay and split check if theme is clothing -->
                            <?php 
                                $allowed_types = ['restaurant', 'cafe', 'bakery'];
                                if (in_array($Settings->pos_type, $allowed_types)) { ?>
                            <button class="col-5 col-xs-4 btn btn-primary splitpay final-submit-btn" name="splitpay" id="splitpay" onclick="split_order_pay()">Split Pay</button>                                 
                            <button class="col-5 col-xs-4 btn btn-primary final-submit-btn" type="button" onclick="split_order();"  > Split Check</button>                                 
                            <?php } ?>
                            <button class="col-5 col-xs-4 btn btn-primary cmdprint1 final-submit-btn" name="cmdprint1" id="submit-sale">Other</button>
                            <!--  <a href="javascript:void(0);" onclick="return paynear_mobile_app()">Paynear APP</a> -->                                 
                        </div>
                    </div>
                    <?php if ((isset($Settings->theme) && $Settings->theme == 'theme_three') && isset($pos_settings->display_CRM) && (int)$pos_settings->display_CRM === 1) { ?>
                        <div class="modal-body hidden_div customerDetails">
                            <ul class="tab-nav sub-tab-nav">
                                <li><button id="showDivButton3">Profile</button></li>
                                 <li><button id="showDivButton5">Addresses</button></li>
                                <li><button id="showDivButton4">Family & Relations</button></li>
                            </ul>
                        </div>
                        <div class="modal-body profile hidden_div" id="eventForm">
                            <?php $this->load->view($this->theme . 'pos/edit_customer_details', $this->data); ?>
                        </div>
                         <div class="modal-body addressSection hidden_div" id="addressSection" style="top: -16px;">
                            <?php $this->load->view($this->theme . 'pos/customer_addresses', $this->data); ?>
                        </div>
                        <div class="modal-body family_relation hidden_div" id="eventFormFamily">
                            <?php $this->load->view($this->theme . 'pos/customer_family_relation', $this->data); ?>
                        </div>
                        <script>
                          document.addEventListener('DOMContentLoaded', function() {
                            var footer = document.querySelector('#paymentModal .modal-footer');
                            var dialog = document.querySelector('#paymentModal .modal-dialog.modal-lg');
                            var btnPay = document.getElementById('showDivButton1');
                            var btnCust = document.getElementById('showDivButton2');
                            var btnProf = document.getElementById('showDivButton3');
                            var btnAddr = document.getElementById('showDivButton5');
                            var btnRel = document.getElementById('showDivButton4');
                            function showFooter(show) { if (footer) { footer.style.display = show ? '' : 'none'; } }
                            function setWide(on) {
                              if (!dialog) return;
                              if (on) { dialog.classList.add('wide-modal'); }
                              else { dialog.classList.remove('wide-modal'); }
                            }
                            // Default state: Payment tab active
                            showFooter(true);
                            setWide(false);
                            if (btnPay)  btnPay.addEventListener('click',  function(){ showFooter(true);  setWide(false); });
                            if (btnCust) btnCust.addEventListener('click', function(){ showFooter(false); setWide(true);  });
                            if (btnProf) btnProf.addEventListener('click', function(){ showFooter(false); setWide(true);  });
                            if (btnAddr) btnAddr.addEventListener('click', function(){ showFooter(false); setWide(true);  });
                            if (btnRel)  btnRel.addEventListener('click',  function(){ showFooter(false); setWide(true);  });
                          });
                        </script>
                        <!-- Rely on themes/default/assets/pos/js/customer_family_relation.js to manage tab visibility -->
                    <?php } ?>
                </div>
            </div>
        </div>