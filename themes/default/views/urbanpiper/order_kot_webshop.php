<?php // echo "<pre>"; print_r($item);

   // print_r($order);
    $order_details = $order[$salesid];
    
    $up_response =  unserialize($order_details->up_response);
    $orderid = $up_response->order->details->ext_platforms;
    
?>

<button class="btn btn-primary pull-right" onclick="printDiv('printableArea')">Print</button>
<div class="container  " id="printableArea">
    
    <h3 class="text-center"> <strong><?= $Settings->site_name ?></strong></h3>
    <h4 class="text-center"><?= $order_details->customer ?></h4>
     <h4 class="text-center"> ORDER ID : <?= $order_details->id ?> &nbsp; Channel : <?= 'Webshop' ?></h4>
     
     <!-- <h5 class="text-center"> OTP  : <?= substr($up_response->customer->phone, -4) ?>  &nbsp; <?= date('d/m/Y H:i',strtotime($order_details->up_state_timestamp))?> </h5> -->
    
    
     <table class="table">
    <?php foreach($items as $item): ?>
    <tr>
        <td><?= $item->product_name ?></td>
        <td>Quantity: <?= number_format($item->quantity, 2); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

    
</div>

    


   