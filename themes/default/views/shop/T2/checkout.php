<?php include('header.php'); ?>
<style>
    label{    font-size: small;}
    .billersection{    background: #efecec;
                       padding-bottom: 1em;
                       padding-top: 1em;}
    </style>
    <!-- banner -->
    <div class="banner">
        <?php
        $grossTotalSend = str_replace(",", "", number_format(str_replace(",", "", $cart['cart_sub_total']) + str_replace(",", "", $cart['cart_tax_total']), 2));

        $grossRoundingcal = number_format(round($grossTotalSend) - $grossTotalSend, 4);
        $grossTotalSend = round($grossTotalSend);
        $cart['cart_gross_rounding'] = $grossRoundingcal;
        $cart['cart_gross_total'] = $grossTotalSend;

        echo form_open('shop/payment', $attributes, ['order_data' => serialize($cart)]);
        ?>
    <div class="container"style="margin-top: 0em;">
        <div class="row">
            <div class="col-md-12 col-xl-7 billersection">
                <h3 class="text-center">Billing & <span>Shipping</span></h3>
                <div class="checkout-right">
                    <div class="row">                        
                        <div class="col-md-6  col-xs-12">                        
                            <div class="clearfix">
                                <div class="form-group">
                                    <label><span class="text-danger">*</span> Billing Name</label>
                                    <input class="form-control billing_input" name="billing_name" id="billing_name" value="<?= ($billing_shipping['billing_name']) ? $billing_shipping['billing_name'] : $_SESSION['name'] ?>" required="required" placeholder="* Billing Name" maxlength="50" type="text" />
                                </div>                                
                                <div class="form-group">
                                    <label><span class="text-danger">*</span> Billing Contact</label>
                                    <input class="form-control billing_input" name="billing_phone" id="billing_phone" value="<?= ($billing_shipping['billing_phone']) ? $billing_shipping['billing_phone'] : (($_SESSION['phone'] != '' || $_SESSION['phone'] != 'null' ) ? $_SESSION['phone'] : '') ?>"  required="required" placeholder="* Mobile Number" maxlength="10" type="text" />
                                </div>
                                <div class="form-group">                                    
                                    <input class="form-control billing_input" name="billing_email" id="billing_email" value="<?= ($billing_shipping['billing_email']) ? $billing_shipping['billing_email'] : $_SESSION['email'] ?>"  placeholder="* Email Address" maxlength="50" type="email" />
                                </div>
                                <div class="form-group">
                                    <label><span class="text-danger">*</span> House No, Street Name </label>
                                    <input class="form-control billing_input" name="billing_addr1" id="billing_addr1" value="<?= ($billing_shipping['billing_addr1']) ? $billing_shipping['billing_addr1'] : $customer['address'] ?>"  required="required" placeholder="Billing Address Line 1" maxlength="250" type="text" />
                                </div>

                                <div class="form-group">
                                    <input class="form-control billing_input" name="billing_city" id="billing_city" value="<?= ($billing_shipping['billing_city']) ? $billing_shipping['billing_city'] : $customer['city'] ?>"  required="required" placeholder="* City" maxlength="50" type="text" />
                                </div>
                                <div class="form-group">
                                    <input class="form-control billing_input" name="billing_addr2" id="billing_addr2" value="<?= ($billing_shipping['billing_addr2']) ? $billing_shipping['billing_addr2'] : '' ?>"  placeholder="Nearest Landmark" maxlength="250" type="text" />
                                </div>
                                <div class="form-group">        
                                    <label><span class="text-danger">*</span>   State</label>
                                    <select class="form-control billing_input" name="billing_state" id="billing_state" required="required">
                                        <option  value="">-- Select State --</option>
                                        <?php
                                        foreach ($state as $getstateValue) {
                                            $cust_state = ($billing_shipping['billing_state']) ? $billing_shipping['billing_state'] : $customer['state'];
                                            ?>
                                            <option value="<?= $getstateValue->name ?>" <?= ($getstateValue->name == $cust_state) ? 'Selected' : '' ?>><?= $getstateValue->name ?></option>
<?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">                                    
                                    <input class="form-control billing_input" name="billing_country" id="billing_country" value="<?= ($billing_shipping['billing_country']) ? $billing_shipping['billing_country'] : $customer['country'] ?>"   placeholder="*Country" value="US" maxlength="50" type="text" />
                                </div>
                                <div class="form-group">  
                                    <span  id="bpincode" class="pull-right text-success"></span>                                  
                                    <input class="form-control billing_input" name="billing_zipcode" id="billing_zipcode" value="<?= ($billing_shipping['billing_zipcode']) ? $billing_shipping['billing_zipcode'] : $customer['postal_code'] ?>"   placeholder="* Pin Code" maxlength="6" type="text" />
                                </div>
                            </div>
                            <div class="checkbox checkbox-small">
                                <label>
                                    <input class="i-check" name="shipping_billing_is_same" id="shipping_billing_is_same" value="1" type="checkbox" <?= (!empty($billing_shipping) ? 'checked' : '') ?> >Billing & Shipping Address is same</label>
                            </div>                    
                        </div>                       
                        <div class="col-md-6  col-xs-12">
                            <div class="clearfix">
                                <div class="form-group">
                                    <label><span class="text-danger">*</span> Shipping Name</label>
                                    <input class="form-control shipping_input" name="shipping_name" id="shipping_name" value="<?= ($billing_shipping['shipping_name']) ? $billing_shipping['shipping_name'] : '' ?>"  required="required" placeholder="* Shipping Name" maxlength="60" type="text" />
                                </div>                                
                                <div class="form-group">
                                    <label><span class="text-danger">*</span> Shipping Contact</label>
                                    <input class="form-control shipping_input" name="shipping_phone" id="shipping_phone" value="<?= ($billing_shipping['shipping_phone']) ? $billing_shipping['shipping_phone'] : '' ?>"  required="required" placeholder="* Mobile Number" maxlength="10" type="text" />
                                </div>
                                <div class="form-group">                                    
                                    <input class="form-control shipping_input" name="shipping_email" id="shipping_email" value="<?= ($billing_shipping['shipping_email']) ? $billing_shipping['shipping_email'] : '' ?>"  placeholder="* Email Address" maxlength="50" type="email" />
                                </div>
                                <div class="form-group">
                                    <label><span class="text-danger">*</span> Shipping House No, Street Name</label>
                                    <input class="form-control shipping_input" name="shipping_addr1" id="shipping_addr1" value="<?= ($billing_shipping['shipping_addr1']) ? $billing_shipping['shipping_addr1'] : '' ?>"  required="required" placeholder="* Shipping House No, Street Name" maxlength="250" type="text" />
                                </div>
                                <div class="form-group">                                    
                                    <input class="form-control shipping_input" name="shipping_city" id="shipping_city" value="<?= ($billing_shipping['shipping_city']) ? $billing_shipping['shipping_city'] : '' ?>"  required="required" placeholder="* City" maxlength="50" type="text" />
                                </div>
                                <div class="form-group">
                                    <input class="form-control shipping_input" name="shipping_addr2" id="shipping_addr2" value="<?= ($billing_shipping['shipping_addr2']) ? $billing_shipping['shipping_addr2'] : '' ?>"  placeholder="Shipping Nearest Landmark" maxlength="250" type="text" />
                                </div>

                                <div class="form-group"> 
                                    <label><span class="text-danger">*</span>   State</label>
                                    <select class="form-control shipping_input" name="shipping_state" id="shipping_state" required="required">
                                        <option  value="">-- Select State --</option>
                                        <?php foreach ($state as $getstateValue) { ?>
                                            <option value="<?= $getstateValue->name ?>" <?= ($getstateValue->name == $billing_shipping['shipping_state']) ? 'Selected' : '' ?>><?= $getstateValue->name ?></option>
<?php } ?>
                                    </select>
                                    <!--<input class="form-control shipping_input" name="shipping_state" id="shipping_state" value="<?= ($billing_shipping['shipping_state']) ? $billing_shipping['shipping_state'] : '' ?>"  required="required" placeholder="State" maxlength="50" type="text" />-->
                                </div>
                                <div class="form-group">                                    
                                    <input class="form-control shipping_input" name="shipping_country" id="shipping_country" value="<?= ($billing_shipping['shipping_country']) ? $billing_shipping['shipping_country'] : '' ?>"  placeholder="Country" value="US" maxlength="50" type="text" />
                                </div>
                                <div class="form-group">   
                                    <span  id="spincode" class="pull-right text-success"> </span>                                 
                                    <input class="form-control shipping_input" name="shipping_zipcode" id="shipping_zipcode" value="<?= (isset($_SESSION['shipping_methods']['pincode']) ? $_SESSION['shipping_methods']['pincode'] : $billing_shipping['shipping_zipcode'])?>"  required="required" <?php (isset($_SESSION['shipping_methods']['pincode']) ?  'readonly ="readonly"' :'') ?> placeholder="* Pin Code" maxlength="6" type="text" />
                                </div>                                </div>
                            <div class="checkbox checkbox-small">
                                <label><input class="i-check" name="save_info" <?= (!empty($billing_shipping) ? 'checked' : '') ?> type="checkbox" value="1">Save address for future reference</label>
                            </div>
                        </div> 
                        <div class="clearfix"></div>
                    </div>
                    <?php
                    if ($shopinfo['pos_type'] == 'pharma') {
                        ?>            
                        <div class="row">
                            <div class="col-sm-12 clearfix bling-div">   
                                <div class="form-group">
                                    <label>Prescription Details</label>
                                </div>
                                <div class="form-group">                                     
                                    <div class="col-sm-6">
                                        <label><span class="text-danger">*</span> Patient Name</label>
                                        <input type="text" name="cf1" required="required" placeholder="Patient Name" class="form-control" />
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Doctor Name</label>
                                        <input type="text" name="cf2" placeholder="Doctor Name" class="form-control" />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                            </div>
                        </div>
<?php } ?>
                </div>
            </div>
            <div class="col-md-12 col-xl-5"style="padding-top: 1em;">
                <h3 class="text-center">Order <span>Review</span></h3>

                <div class="checkout-right ">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Products</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Tax</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($cart['items'] as $pid => $items) {
                                ?>
                                <tr>
                                    <td><?= $items['name'] ?> <?php if ($items['vname']) {
                                echo '<br/><sub>(' . $items['vname'] . ')</sub>';
                            } ?></td>
                                    <td><?= $items['qty'] ?></td>
                                    <td><?= $currency_symbol ?>&nbsp;<?= number_format(str_replace(",", "", $items['item_price']), 2) ?></td>
                                    <td><?= $currency_symbol ?>&nbsp;<?= number_format(str_replace(",", "", $items['item_tax_total']), 2) ?></td>
                                    <td><?= $currency_symbol ?>&nbsp;<?= number_format(str_replace(",", "", $items['item_price'] * $items['qty']), 2) ?></td>                                
                                </tr>
<?php } ?>    
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5">
                                    <span class="pull-left">Items Subtotal</span>
                                    <span class="pull-right"><?= $currency_symbol ?>&nbsp;<?= number_format(str_replace(",", "", $cart['cart_sub_total']), 2) ?></span>
                                    <span class="clearfix"></span>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="5">
                                    <span class="pull-left">Tax Amount</span>
                                    <span class="pull-right"><?= $currency_symbol ?>&nbsp;<?= number_format(str_replace(",", "", $cart['cart_tax_total']), 2) ?></span>
                                    <span class="clearfix"></span>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="5">
                                    <span class="pull-left">Total Order Amount</span>
                                    <span class="pull-right"><?= $currency_symbol ?>&nbsp;
                                    <?= $grosstotal = number_format(str_replace(",", "", $cart['cart_sub_total']) + str_replace(",", "", $cart['cart_tax_total']), 2) ?></span>
                                    <span class="clearfix"></span>
                                </th>
                            </tr>
                        <?php if ($cart['cart_gross_rounding'] != '0.0000') { ?>
                            <tr>
                                <th colspan="5">
                                    <span class="pull-left">Rounding</span>
                                    <span class="pull-right"><?= $currency_symbol ?>&nbsp;
                                        <?= $cart['cart_gross_rounding'] ?></span>
                                    <span class="clearfix"></span>

                                </th>
                            </tr>
                        <?php } ?>

                        <?php   
                            $ordert = array_values($cart['cart_order_tax']);
                            if ($ordert[0] > 0) { 
                        ?>
                                <tr>
                                    <th colspan="5">
                                        <span class="pull-left">Order Tax</span>
                                        <span class="pull-center">&nbsp;(<?= $cart['order_tax_name'] ?>)</span>
                                        <span class="pull-right"><?= $currency_symbol ?>&nbsp;<?= number_format(str_replace(",", "", $cart['order_tax_total']), 2); ?></span>
                                        <span class="clearfix"></span>
                                    </th>
                                </tr>
                        <?php } else { ?>
                                <tr style="display:none;">

                                </tr>
                        <?php } ?>
                            <tr>
                                <?php
                                $grosstotalnum = str_replace(',', '', $grosstotal);
                                if (is_numeric($grosstotalnum)) {
                                    $grosstotal = $grosstotalnum;
                                }
                                ?>
                                <th colspan="5">
                                    <span class="pull-left">Total Payable Amount</span>
                                    <span class="pull-right"><?= $currency_symbol ?>&nbsp;<?= $grosstotal = number_format(str_replace(",", "", $cart['cart_gross_total']), 2) ?></span>
                                    <span class="clearfix"></span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <!-- // Pay -->                
                </div>

                <div class="row"style="margin:0" >
                    <div class="col-sm-12 clearfix bling-div">

                        <div class="form-outer">                            
                            <?php
                            
                            $shipingAmt = 0;
                            $crossShippingAmt = '';
                            if (is_array($shipping_methods)) {
                                 
                                if(isset($_SESSION['shipping_methods']) && $_SESSION['shipping_methods']['methods']){
                                    $shipping_code = $_SESSION['shipping_methods']['methods'];
                                     
                                    foreach ($shipping_methods as $shippings) {
                                        if ($shippings['code'] == $_SESSION['shipping_methods']['methods']) { 
                                    
                                            $var = floatval(preg_replace('/[^\d.]/', '', $cart['cart_gross_total']));
                                            if ($var >= $shopinfo['eshop_free_delivery_on_order']) {
                                                $shipingAmt = '0.00';
                                                $crossShippingAmt = ' <del class="text-danger">'.$currency_symbol . ' ' . number_format($shippings['price'], 2).'</del> ';
                                            } else {
                                                $shipingAmt = number_format($shippings['price'], 2);                                                
                                            }                                            
                                             break;
                                        }//end if 
                                    }//end foreach
                                ?>   
                                    <div class="form-group">
                                        <input type="hidden" name="time_slotes" id="time_slotes" value="<?= $_SESSION['shipping_methods']['time'] ?>" /> 
                                        <input type="hidden" name="DeliverLater" id="DeliverLater" value="<?= $_SESSION['shipping_methods']['date'] ?>" /> 
                                        <input type="hidden" name="shippingTypeName" id='shippingTypeName' value="<?= $shippings['name']; ?>" />
                                        
                                        <input type="hidden" id="<?php echo 'shipping_price_' . $shippings['id']; ?>" value="<?= $shipingAmt ?>" /> 
                                        <input type="hidden" id="<?php echo 'minimum_order_amount_' . $shippings['id']; ?>" value="<?= $shippings['minimum_order_amount'] ?>" /> 
                                        <input type="hidden" data_id="<?= $shippings['all_time'] ?>" name="shippingType" id="shippingType1" value="<?php echo $shippings['id']; ?>" />
                                        <div class="row">
                                            <div class="col-sm-12"><?= ($active_multi_outlets) ? $outlets[$_SESSION['eshop_location_id']] : $shipping_info; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><b>Shipping Amt.:</b></div>
                                            <div class="col-sm-5"><?=$crossShippingAmt?> <?=$currency_symbol . $shipingAmt?></div>
                                            <div class="col-sm-3"><a href="<?= base_url('shop/reset_shipping_methods') ?>" ><i class="fa fa-pencil"></i> Edit </a></div>
                                        </div>
                                    </div>
                                <?php 
                                } else {
                                ?>                            
                                <div  class="form-group">
                                    <label><span class="text-danger">*</span> Shipping Methods</label>
                                </div>
                                <?php
                                    foreach ($shipping_methods as $key => $shippings) {
                                        if ($shippings['code'] == 'delivery') {
                                            $var = floatval(preg_replace('/[^\d.]/', '', $cart['cart_gross_total']));
                                            if ($var >= $shopinfo['eshop_free_delivery_on_order']) {
                                                $shipingAmt = '0.00';
                                                $crossShippingAmt = ' <del class="text-danger">'.$currency_symbol . ' ' . number_format($shippings['price'], 2).'</del> ';
                                            } else {
                                                $shipingAmt = number_format($shippings['price'], 2);
                                            }
                                            ?>
                                            <div class="form-group"><!-- onChange="return getShippinMethodName('<?= $shippings['name']; ?>')" -->
                                                <label class="price">
                                                    <input type="radio" data_id="<?= $shippings['all_time'] ?>" class="shippingType" name="shippingType" id="shippingType1" required="required" value="<?php echo $shippings['id']; ?>" />
                                                    <?php echo $shippings['name']; ?> 
                                                </label>
                                                <span class="pull-right"><?= $currency_symbol ?> <?= ($cart['cart_gross_total'] >= $shopinfo['eshop_free_delivery_on_order'] ) ? '0.00 <del class="text-danger"> Rs.' . number_format($shippings['price'], 2) . '</del>' : number_format($shippings['price'], 2); ?> </span>
                                                <input type="hidden" id="<?php echo 'shipping_price_' . $shippings['id']; ?>" value="<?= $shipingAmt ?>" />                
                                                <input type="hidden" id="<?php echo 'minimum_order_amount_' . $shippings['id']; ?>" value="<?= $shippings['minimum_order_amount'] ?>" />                
                                                <div class="row">
                                                <?php if($shippings['minimum_order_amount']){ ?>     
                                                    <div class="col-sm-12"><small class="text-danger">*Note: Minimum Order Amt. <?= $currency_symbol ?> <?= $shippings['minimum_order_amount']; ?></small></div>
                                                <?php } ?>    
                                                    <div class="show_slot col-sm-6" id="block_id_<?= $shippings['id'] ?>"></div>
                                                </div>
                                            </div>   
                                        <?php
                                        } else {

                                            if ($cart['cart_gross_total'] >= $shopinfo['eshop_free_delivery_on_order']) {
                                                $shipingAmt1 = '0.00';
                                                $crossShippingAmt = ' <del class="text-danger">'.$currency_symbol . ' ' . number_format($shippings['price'], 2).'</del> ';
                                            } else {
                                                $shipingAmt1 = number_format($shippings['price'], 2);
                                            }
                                            ?>
                                            <div class="form-group">
                                                <label class="price">
                                                    <input type="radio" data_id="<?= $shippings['all_time'] ?>" class="shippingType" name="shippingType" id="shippingType2" required="required" value="<?php echo $shippings['id']; ?>" />
                                                    <?php echo $shippings['name']; ?>
                                                </label>
                                                <span class="pull-right"><?= $currency_symbol ?> <?= ($cart['cart_gross_total'] >= $shopinfo['eshop_free_delivery_on_order'] ) ? '0.00 <del class="text-danger"> '.$currency_symbol . ' ' . number_format($shippings['price'], 2) . '</del>' : number_format($shippings['price'], 2); ?> </span>
                                                <input type="hidden" id="<?php echo 'shipping_price_' . $shippings['id']; ?>" value="<?= $shipingAmt1 ?>" />
                                                <input type="hidden" id="<?php echo 'minimum_order_amount_' . $shippings['id']; ?>" value="<?= $shippings['minimum_order_amount'] ?>" /> 
                                                <div class="row">
                                                <?php if($shippings['minimum_order_amount']){ ?>    
                                                    <div class="col-sm-12"><small class="text-danger">*Note: Minimum Order Amt. <?= $currency_symbol ?> <?= $shippings['minimum_order_amount']; ?></small></div>
                                                <?php } ?>
                                                    <div class="show_slot" id="block_id_<?= $shippings['id'] ?>"></div>
                                                </div>
                                            </div>
                                        <?php }//End else
                                    } //end foreach
                                ?>
                                    <input type="hidden" name="shippingTypeName" id='shippingTypeName' value="<?= $shippings['name']; ?>" /> 
                                <?php
                                    }//end else $_SESSION['shipping_methods']
                                }
                                ?>
                             
                            <div class="clearfix"></div>
 
                        </div>

                    </div>                            
                </div>

                <!--<div class="row">-->
                <h3 >Total Billing Amount: <?= $currency_symbol ?>&nbsp;<span id="billing_amt"><?php echo (number_format($shipingAmt + (str_replace(",", "", $cart['cart_gross_total'])), 2)); ?></span></h3>
                <!--</div>-->
                <div style="padding: 20px; text-align: center;">
                    <a href="<?= base_url('shop/cart') ?>" class="btn btn-md btn-primary submit">Back To Cart</a>
                    <input class="btn btn-md btn-primary submit" id="btn_proceed_payment" onClick="return changevalue();" name="submit_checkout" type="submit" value="Proceed To Payment" />
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <input type="hidden" id="order_total" value="<?= str_replace(",", "", $cart['cart_gross_total']) ?>" />
    <input type="hidden" name="shipping_amount" id="order_shipping_amt" value="<?= $shipingAmt ?>" />
    <input type="hidden" name="withordertax_amount" id="withordertax_amount" value="<?= str_replace(',', '', $cart['order_tax_total']) ?>" />
    <input type="hidden" name="order_tax_id" id="order_tax_id" value="<?= $cart['cart_order_tax_id'] ?>" />
    <input type="hidden" name="order_tax" id="order_tax" value="<?= $ordert[0]; ?>" />


    <?php
    echo form_close();
    ?>
</div>
<!-- //banner -->

<?php include('footer.php'); ?>

<script>
    $(document).ready(function () {
        $('.shippingType').on('click', function () {
            if ($(this).is(':checked')) {
                var v = $(this).val();
                var shipping_price = $('#shipping_price_' + v).val();
                
               
                
                var order_total = $('#order_total').val();

                var orderwithtax = $('#withordertax_amount').val();
                //var total = parseInt(order_total) + parseInt(shipping_price);
                var total = (parseFloat(order_total) + parseFloat(shipping_price)).toFixed(2);
                // console.log(total);

                $('#billing_amt').html(total);
                $('#order_shipping_amt').val(shipping_price);
                
                var minimum_order_amount = $('#minimum_order_amount_' + v).val();
                 
                if(parseFloat(order_total) < parseFloat(minimum_order_amount)){                    
                    $('#btn_proceed_payment').attr('disabled', true);
                } else {
                    $('#btn_proceed_payment').attr('disabled', false);
                }
            }
            
        });

        $('.billing_input').on('blur', function () {

            if ($('#shipping_billing_is_same').is(':checked')) {

                billing_shipping_is_same();

            }

        });

        $('#shipping_billing_is_same').on('click', function () {

            if ($('#shipping_billing_is_same').is(':checked')) {

                billing_shipping_is_same();

            }

        });

        var alltime = $("[name=shippingType]").attr('data_id');
        if (alltime != '1') {
            show_slote_time($("[name=shippingType]").val());
        }
    });

    function billing_shipping_is_same() {
        $('#shipping_name').val($('#billing_name').val());
        $('#shipping_phone').val($('#billing_phone').val());
        $('#shipping_email').val($('#billing_email').val());
        $('#shipping_addr1').val($('#billing_addr1').val());
        $('#shipping_addr2').val($('#billing_addr2').val());
        $('#shipping_city').val($('#billing_city').val());
        $('#shipping_state').val($('#billing_state').val());
        $('#shipping_country').val($('#billing_country').val());
        $('#shipping_zipcode').val($('#billing_zipcode').val());
    }
    
    function changevalue() {
        if ($('#shippingType1').is(':checked'))
            $('#shippingTypeName').val('Delivery at home');
        if ($('#shippingType2').is(':checked'))
            $('#shippingTypeName').val('Pickup from store');
    }

    $('.shippingType').click(function () {
        $('#dilivery_late_block').hide();
        $('#deliverydata').val('');
        $('input[type="date"]').removeAttr('required');

        var getid = $(this).val();
        var alltime = $(this).attr('data_id');
        if (alltime != '1') {
            show_slote_time(getid);
        }

        if (getid == 3) {
            $('#dilivery_late_block').show();
            $('input[type="date"]').attr("required", "true");
        }
    });
    
    function show_slote_time(getid) {
        $('.show_slot').html('');
        $('#dilivery_late_block').hide();
        $('#deliverydata').val('');
        $.ajax({
            type: 'ajax',
            dataType: 'html',
            method: 'get',
            url: '<?= base_url('shop/getSloteTime') ?>/' + getid,
            success: function (response) {
                $('#block_id_' + getid).html(response)

            }, error: function () {
                console.log('error');
            }
        });
    }

</script>


<?php if ($eshop_settings->delivery_pincode == 'Specific Pincodes') { ?>
    <script>

        $(document).ready(function () {
            
            if($('#shipping_zipcode').val()){
                checkpincode('spincode', $('#shipping_zipcode').val());
            } else {
                $('#btn_proceed_payment').attr('disabled', true);
            }
//            if ($('#billing_zipcode').val() != '') {
//                checkpincode('bpincode', $('#billing_zipcode').val());
//            }
    
            if ($('#shipping_zipcode').val() != '') {
                
                checkpincode('spincode', $('#shipping_zipcode').val());
                               
            }
            
            $('.shippingType').on('click', function () {
                
                checkpincode('spincode', $('#shipping_zipcode').val());
            });

        });

//        $('#billing_zipcode').change(function () {
//            $('#bpincode').html('');
//            checkpincode('bpincode', $(this).val());
//        });

        $('#shipping_zipcode').change(function () {
            $('#spincode').html('');
            
            checkpincode('spincode', $(this).val());
            
        });


//        $('#billing_zipcode').on('paste', function (e) {
//
//            $('#bpincode').html('');
//            checkpincode('bpincode', $(this).val());
//
//        });

        $('#shipping_zipcode').on('paste', function (e) {
            $('#spincode').html('');
            
            checkpincode('spincode', $(this).val());

        });


        function checkpincode(blockid, pincode) {
            
            var shippingType = $('input[class="shippingType"]:checked').val();
            
            var order_total = $('#order_total').val();
            var minimum_order_amount = $('#minimum_order_amount_' + shippingType).val();
            
            if(shippingType == 1 || shippingType == 3) {
            
                $.ajax({
                    type: 'ajax',
                    dataType: 'json',
                    method: 'GET',
                    url: '<?= base_url('shop/checkpincode'); ?>/' + pincode,
                    success: function (response) {
                        if (response.status == 'success') {
                            $('#' + blockid).html('<i class="fa fa-check text-success"></i>');
                            $('#btn_proceed_payment').attr('disabled', false);
                        } else {
                            $('#' + blockid).html('<i class="fa fa-times text-danger"></i> <span class="text-danger">' + response.message + '<span>');
                             $('#btn_proceed_payment').attr('disabled', true);
                            if (blockid == 'bpincode') {
                                $('#billing_zipcode').val('');
                                $('#billing_zipcode').focus();
                            }
                            if (blockid == 'spincode') {
                                $('#shipping_zipcode').val('');
                                $('#shipping_zipcode').focus();
                            }
                        }
                        
                        if(parseFloat(order_total) < parseFloat(minimum_order_amount)){                    
                            $('#btn_proceed_payment').attr('disabled', true);
                        }

                    }, error: function () {
                        console.log('error');
                    }
                });
            
            } else {
                
                if(parseFloat(order_total) < parseFloat(minimum_order_amount)){                    
                    $('#btn_proceed_payment').attr('disabled', true);
                } else {
                    $('#btn_proceed_payment').attr('disabled', false);
                }
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
       
    </script>
<?php } ?>
