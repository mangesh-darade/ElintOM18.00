<div class="modal-header alert alert-success">
    <h4 class="modal-title">Order Details (<?= $order['sale']['invoice_no'];?>) </h4>
    <button type="button" class="close pull-right" style=" margin-top: -1em;" data-dismiss="modal">&times;</button>

</div>
<div class="modal-body">
    
    <div class="row">
        <div class="col-sm-6">
             <?php if($order_status_type!='pending'){ ?>
		<!--<b class="modal-title">Invoice No: </b> <?= $order['sale']['invoice_no'];?> -->
		<?php } ?>
            <b class="modal-title">Customer Name : </b> <?= $order['sale']['customer'];?> 
        </div>
    <?php
    
        if(!empty($order['sale']['cf1']) || !empty($order['sale']['cf2'])) {
    ?>
        <div class="col-sm-6" >
            <div class="col-sm-6"><b>Patient Name : </b> <?= $order['sale']['cf1'];?></div>
            <div class="col-sm-6"><b>Doctor Name : </b> <?= $order['sale']['cf2'];?></div>
        </div>
    <?php }//end if. ?>
    </div>
    <div class="row">                        
        <div class="col-sm-12 clearfix bling-div" style="background: #fff;width: 100%;">
            <div class="bill-to">
                <div class="form-outer">
                    <div class="cart-heading last">
                        <h4>Order Items <span class="pull-right" style="margin-right: 30px;">Date: <?= $order['sale']['date'];?></span></h4>
                        <div class="clearfix"></div>
                    </div>
                    <div class="table-outer">
                        <div class="table-responsive" ><br/>
                            <table class="table table-bordered ">
                                <thead>
                                    <tr class="cart_menu"  style="border-top: 1px solid #ddd !important;">
                                        <td class="text-center">Item</td>
                                        <td class="text-center">Price</td>
                                        <td class="text-center">Quantity</td>
                                        <td align="center">Tax</td>
                                        <td align="center">Total</td>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                  $orderitems = $order['items'];

                                 
                                  foreach ($orderitems as $key => $oitem) {
                                ?>
                                    <tr>
                                        <td class="product-image text-center">
                                            <img src="<?= base_url('/assets/mdata/'.$Customer_assets.'/uploads/thumbs/'.$oitem->image);?>"  alt="<?php // $oitem->product_name;?>" />
                                            <span class="cart_description"> 
                                               <h5><?= $oitem->product_name; if($oitem->variant){echo ' <sub>('.$oitem->variant.')</sub>';} ?></h5></span>
                                            <?= ($oitem->note)?'<p style="font-size:11px">'.$oitem->note.'</p>':'' ?>

                                        </td>
                                        <td class="text-center">
                                            <span><?= $Settings->symbol ?> <?= number_format($oitem->unit_price,2);?></span>
                                        </td>
                                        <td class="product-qty">
                                            <div class="cart_quantity_button text-center">
                                                <span><?= ($oitem->quantity);?></span> 
                                            </div> 
                                        </td>
                                        <td class="product-qty">
                                            <div class="cart_quantity_button text-center">
                                                <span><?= $Settings->symbol ?> <?= number_format($oitem->item_tax,2);?></span> 
                                            </div> 
                                        </td>
                                        <td class="product-total text-center">
                                            <span><?= $Settings->symbol ?> <?= number_format($oitem->subtotal,2);?></span>
                                        </td>                                                                                                  
                                    </tr>
                                <?php                                    
                                  }
                                ?>
                                    <tr class="total-count">                                     
                                        <td colspan="3"></td>
                                        <td align="right">Sub Total:</td>
                                        <td class="tot-price"><?= $Settings->symbol ?> <?= number_format($order['sale']['total'],2)?></td>
                                    </tr>
                                    <tr class="total-count">                                     
                                        <td colspan="3"></td>
                                         <td align="right">Item Tax:</td>
                                         <td class="tot-price"><?= $Settings->symbol ?> <?= number_format($order['sale']['product_tax'],2)?></td>
                                      </tr>
                                      <?php  if($order['sale']['order_tax'] > 0) { ?>
                                      <tr class="total-count">
                                          <td colspan="3"></td>
                                         <td align="right">Order Tax:</td>
                                        <td class="tot-price"><?= $Settings->symbol ?> <?= number_format($order['sale']['order_tax'],2)?></td>
                                     </tr>
                                      <?php } else{ ?>
                                     <tr class="total-count" style="display: none;">
                                          <td colspan="3"></td>
                                     </tr>
                                      <?php } ?>
                                
                                       <?php  if($order['sale']['total_tax'] > 0) { ?>
                                    <tr class="total-count">                                     
                                        <td colspan="3"></td>
                                        <td align="right">Total Tax:</td>
                                        <td class="tot-price"><?= $Settings->symbol ?> <?= number_format($order['sale']['total_tax'],2)?></td>
                                    </tr>
                                <?php } ?>
                                    
                                
                                <?php if($order['sale']['shipping'] > 0) { ?>
                                    <tr class="total-count">                                     
                                        <td colspan="3"></td>
                                        <td align="right">Shipping:</td>
                                        <td class="tot-price"><?= $Settings->symbol ?> <?= number_format($order['sale']['shipping'],2)?></td>
                                    </tr>
                                <?php } ?>
                                
                                <?php if($order['sale']['rounding'] !='0.0000'){ ?>
                                    <tr class="total-count">                                     
                                        <td colspan="3"></td>
                                        <td align="right">Rounding:</td>
                                        <td class="tot-price"><?= $Settings->symbol ?> <?= number_format($order['sale']['rounding'],2)?></td>
                                    </tr>
                                <?php } ?>       
                                    <tr class="total-count">                                    
                                        <td colspan="3"></td>
                                        <td align="right"><b>Gross Total</b></td>
                                        <td class="tot-price"><b><?= $Settings->symbol ?> <?= number_format($order['sale']['grand_total'] + $order['sale']['rounding'],2)?></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>                                                                                    
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>			
    </div>
    <div class="container">
      <?=   ($order['sale']['note'])?'<p style="font-size:12px"><b>Remark:- </b>'.$order['sale']['note'].'</p>' :'' ?>
    </div>
    <div class="row">
        <?php
        $billing_shipping = $order['billing_shipping'];
       
        $payments = $order['payment'];
        ?>
        <div class="col-sm-6 clearfix bling-div">
            <div class="bill-to">
                <div class="form-outer">
                    <div class="cart-heading">
                        <h4>Shipping Method</h4>
                        <div class="clearfix"></div>
                    </div>
                    <p>&nbsp;</p>
                     <div style="padding-left: 18px; font-size:14px;">
                               <?= '<b>'.$billing_shipping['shipping_method_name'].'</b>'?>
                               <?php if($order['sale']['deliver_later']){ ?> 
                               <b>Date : </b><?= date('d-m-Y',strtotime($order['sale']['deliver_later'])) ?><br/>
                               <?php } if($order['sale']['time_slotes']){ ?>
                               <b>Time : </b><?= $order['sale']['time_slotes'] ?>
                               <?php } ?>
                     </div

                    <div class="clearfix"></div>
                </div>
            </div>
        
        <div class="col-sm-6 clearfix bling-div">
            <div class="bill-to">
                <div class="form-outer">
                    <div class="cart-heading">
                        <h4>Payment Information</h4>
                        <div class="clearfix"></div>
                    </div>
                      <p style="padding-left: 18px;font-size:14px;">
                        <?php 
                            $grossTotal = $order['sale']['grand_total'] + $order['sale']['rounding'];
                            $balanceamount = ($grossTotal - $order['paidamount']);
                           
                            if($balanceamount > 0 ){
                                echo "<b>Balance Amount : </b>". $Settings->symbol ." ".number_format($balanceamount,2)."/-";
                            }
                            
                        ?>
                    </p>
                    

                     <?php if($payments->paid_by == 'instomojo'){
                        $paid_by = 'Credit Card / Debit Card / Netbanking';
                    } else if($payments->paid_by == 'paytm'){
                        $paid_by = 'PAYTM';
                    } else if($payments->paid_by == 'UPI_QRCODE') {
                        $paid_by = 'UPI & QR CODE';
                    } else {
                        $paid_by = 'COD';
                    } ?>
                    
                    <p style="padding-left: 18px;">
                    <b>Method: </b> <?php echo $paid_by; //$paid_by = ($payments->paid_by == 'instomojo') ? 'Credit Card / Debit Card / Netbanking' : 'COD' ?>
                    <span class="pull-right"><b> Status:</b> <?= $order['sale']['payment_status']?></span>
                    </p>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div> 
    <div class="row">
        <div class="bill-to col-sm-6">
            <div class="form-outer">
                <div class="cart-heading">
                    <h4>Billing Details</h4><br/>
                    <div class="clearfix"></div>
                </div>									 
                <div class="row" >
                    <div class="col-sm-12">
                        <table class="table table-bordered">
                            <tr>
                                <td><i class="fa fa-user" aria-hidden="true" style="width:20px;float:left;padding: 3px 0 8px;"></i> <?= $billing_shipping['billing_name']?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-home" aria-hidden="true" style="width:20px;float:left;padding: 3px 0 8px;"></i> <?= $billing_shipping['billing_addr']?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-envelope" aria-hidden="true" style="width:20px;float:left;padding: 3px 0 8px;"></i> <?= $billing_shipping['billing_email']?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-phone" aria-hidden="true" style="width:20px;float:left;padding: 3px 0 8px;"></i> <?= $billing_shipping['billing_phone']?></td>
                            </tr>                                                 
                        </table>
                    </div>                                         
                </div> 
            </div> 
        </div>
        <div class="bill-to col-sm-6">
            <div class="form-outer">
                <div class="cart-heading">
                    <h4>Shipping Details</h4> <br/>
                    <div class="clearfix"></div>
                </div>									 
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-bordered">
                            <tr>
                                <td><i class="fa fa-user" aria-hidden="true" style="width:20px;float:left;padding:3px 0 8px;"></i> <?= $billing_shipping['shipping_name']?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-home" aria-hidden="true" style="width:20px;float:left;padding:3px 0 8px;"></i> <?= $billing_shipping['shipping_addr']?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-envelope" aria-hidden="true" style="width:20px;float:left;padding:3px 0 8px;"></i> <?= $billing_shipping['shipping_email']?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-phone" aria-hidden="true" style="width:20px;float:left;padding:3px 0 8px;"></i> <?= $billing_shipping['shipping_phone']?></td>
                            </tr>                                                 
                        </table>
                    </div>                                         
                </div> 
            </div> 
        </div>
    </div>
</div>
<div class="modal-footer">
        <button type="button" class="btn btn-default modal-close waves-effect waves-green" data-dismiss="modal">Close</button>
    </div>
