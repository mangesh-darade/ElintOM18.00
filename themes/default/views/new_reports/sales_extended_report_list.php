<div class="row" style="max-width:67%; min-width:1150px;">
   <?php 
        $pagingAttributes = array(
            "page_no"           =>  $page_no,
            "per_page_records"  =>  $per_page_records,
            "totalRecord"       =>  $totalRecord,
            "position"          =>  'top', 
            "search_key"        =>  $search_key,    
        );
        
        echo pagignations($pagingAttributes);
   ?>
</div>
<div class="table-responsive">    
    <table id="SlRData" class="table table-bordered table-hover table-striped table-condensed reports-table">
        <thead>
            <tr>
                <th><?= lang("date"); ?></th>
                <th><?= lang("Invoice No"); ?></th>
                <th><?= lang("reference_no"); ?></th>
                <th><?= lang("biller"); ?></th>
                <th><?= lang("customer"); ?></th>
                <th><?= lang("Invoice_Value"); ?></th>
                <th><?= lang("Taxable_Amount"); ?></th>
                <th><?= lang("Tax_Amount"); ?></th>
                <th><?= lang("paid"); ?></th>
                <th><?= lang("balance"); ?></th>
                <th><?= lang("Payment Method"); ?></th>
                <th><?= lang("payment_status"); ?></th>
                <th><?= lang("sale_status"); ?></th>
                <th><?= lang("Total_Items"); ?></th>
                <th><?= lang("total_weight"); ?></th>
                <th>Sale Items</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($totalRecord && !empty($data) && is_array($data)) {
                foreach ($data as $key => $salesData) {

                    $sale = $salesData['sale'];
                    $due = $pending = 'danger';
                    $paid = 'success';
                    $partial = 'info';
                    
                    $payment_status = $sale['payment_status'];
                    ?>
                    <tr>
                        <td><?= $sale['date'] ?></td>
                        <td><?= $sale['invoice_no'] ?></td>
                        <td><?= $sale['reference_no'] ?></td>
                        <td><?= $sale['biller'] ?></td>
                        <td><?= $sale['customer'] ?><br/><?= $sale['city'] ?> <?= $sale['state_code'] ?> <?= $sale['postal_code'] ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney(($sale['grand_total'])) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($sale['grand_total'] - ($sale['total_tax'] + $sale['shipping'] + $sale['rounding'])) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($sale['total_tax']) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($sale['paid']) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($sale['grand_total'] - $sale['paid']) ?></td>
                        <td><?= $sale['payment_method'] ?></td>
                        <td><span class="label label-<?=$$payment_status?>"><?= $payment_status ?></span></td>
                        <td><?= $sale['sale_status'] ?></td>
                        <td style="text-align:center;"><?= $this->sma->formatQuantity($sale['total_items']) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatQuantity($sale['total_weight']) ?></td>
                        <td>
                            <table class="table table-bordered table-hover table-striped table-condensed reports-table">
                                <tr>
                                    <th>Product(s)</th>
                                    <th>Price<br/>(<i class="fa fa-inr"></i>)</th>
                                    <th>Qty</th>
                                    <th>Disc<br/>(<i class="fa fa-inr"></i>)</th>
                                    <th>Tax Rate</th>
                                    <th>Tax<br/>(<i class="fa fa-inr"></i>)</th>
                                    <th>GST Rate</th>
                                    <th>CGST<br/>(<i class="fa fa-inr"></i>)</th>
                                    <th>SGST<br/>(<i class="fa fa-inr"></i>)</th>
                                    <th>IGST<br/>(<i class="fa fa-inr"></i>)</th>
                                    <th>HSN Code</th>
                                    <th>Weight</th>
                                </tr>
                                <?php
                                $sale_items = $salesData['items'];
                                if (is_array($sale_items)) {
                                    foreach ($sale_items as $siid => $item) {
                                        ?>        
                                        <tr>
                                            <td><?= $item['product_name'] ?> <?= $item['option_name'] ? "(" . $item['option_name'] . ")" : '' ?></td>
                                            <td style="text-align:right;"><?= $this->sma->formatMoney($item['unit_price']) ?></td>
                                            <td style="text-align:center;"><?= $this->sma->formatQuantity($item['unit_quantity']) ?></td>                       
                                            <td style="text-align:right;"><?= $this->sma->formatMoney($item['item_discount']) ?></td>
                                            <td style="text-align:center;"><?= number_format((int) $item['tax_rate'], 0) ?>%</td>
                                            <td style="text-align:right;"><?= $this->sma->formatMoney($item['item_tax']) ?></td>
                                            <td style="text-align:right;"><?= number_format($item['gst_rate'], 1) ?>%</td>
                                            <td style="text-align:right;"><?= $this->sma->formatMoney($item['cgst']) ?></td>
                                            <td style="text-align:right;"><?= $this->sma->formatMoney($item['sgst']) ?></td>
                                            <td style="text-align:right;"><?= $this->sma->formatMoney($item['igst']) ?></td>                        
                                            <td><?= $item['hsn_code'] ?></td>                        
                                            <td style="text-align:center;"><?= $this->sma->formatQuantity($item['item_weight']) ?></td>                        
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                <?php
                }
            } else {
                
                echo '<tr><td colspan="16"><p class="text-danger">No records found</p></td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
<div class="row" style="width:67%; min-width:1150px;">
   <?php 
        $pagingAttributes = array(
            "page_no"           =>  $page_no,
            "per_page_records"  =>  $per_page_records,
            "totalRecord"       =>  $totalRecord,
            "position"          =>  'bottom',            
            "search_key"        =>  $search_key,            
        );
        
        echo pagignations($pagingAttributes);
   ?>
</div>