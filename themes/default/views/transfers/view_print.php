<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php // Screen: Transfers Print View (view_print) ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= lang('transfer'); ?> - <?= $transfer->transfer_no; ?></title>
    <style>
        body { font-family: Arial, sans-serif; color:#000; }
        .container { max-width: 1024px; margin: 0 auto; padding: 10px 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .well { background:#f7f7f7; border:1px solid #ddd; padding:10px; border-radius:4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        thead th { background:#f0f0f0; }
        address { margin:0; }
        img { max-height: 60px; }
        .order_barcodes img { max-height: 50px; }
        
        @media print { 
            .no-print { display:none !important; } 
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            /* Table header repeats on each page */
            thead { display: table-header-group; }
            /* Prevent breaks inside rows */
            tbody tr { page-break-inside: avoid; page-break-after: auto; }
            /* Remove browser-generated headers and footers */
            @page { 
                margin: 20mm;
                margin-bottom: 15mm;
                margin-top: 15mm;
            }
            /* Hide page URLs in footer */
            body::after { display: none !important; }
            a[href]:after { content: none !important; }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Screen: Transfers Print - Header -->
    <?php if ($logo) { ?>
        <div class="text-center" style="margin-bottom:15px;">
            <img src="<?= base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
        </div>
            <div class="text-center" style="margin-bottom:15px;">
                <h2><strong>TRANSFER ORDER</strong> </h2>
            </div>
    <?php } ?>

    <div class="well">
        <div class="row bold" style="display:flex; align-items:flex-start;">
            <div style="flex:1;">
                <div><?= lang('date'); ?>: <?= $this->sma->hrld($transfer->date); ?></div>
                <div><?= lang('ref'); ?>: <?= $transfer->transfer_no; ?></div>
            </div>
            <div class="order_barcodes text-right" style="flex:1;">
                <?= $this->sma->save_barcode($transfer->transfer_no, 'code128', 66, false); ?>
                <?= $this->sma->qrcode('link', urlencode(site_url('transfers/view/' . $transfer->id)), 2); ?>
            </div>
        </div>
    </div>

    <!-- Screen: Transfers Print - From/To Details -->
    <div class="row" style="display:flex; gap:20px; margin-bottom:10px;">
        <div style="flex:1;">
            <strong><?= $this->lang->line('From Location'); ?></strong>
            <h2 style="margin:6px 0;"><?= ucfirst($from_warehouse->name) . " ( " . $from_warehouse->code . " )"; ?></h2>
            <address>
                <?= ($from_warehouse->address!='')?'<b> Address : </b> '.$from_warehouse->address:'' ?>
                <?= ($from_warehouse->phone!='')?'<br/><b> '.lang("tel").' : </b>'.$from_warehouse->phone:''?>
                <?= ($from_warehouse->email!='')?'<br/><b> '.lang("email").' : </b> '.$from_warehouse->email:''?>
            </address>
        </div>
        <div style="flex:1;">
            <strong><?= lang('To Location'); ?></strong>
            <h2 style="margin:6px 0;"><?= ucfirst($to_warehouse->name) . " ( " . $to_warehouse->code . " )"; ?></h2>
            <address>
                <?= ($to_warehouse->address!='')?'<b> Address : </b> '.$to_warehouse->address:'' ?>
                <?= ($to_warehouse->phone!='')?'<br/><b> '.lang("tel").' : </b> '.$to_warehouse->phone:''?>
                <?= ($to_warehouse->email!='')?'<br/><b> '.lang("email").' : </b> '.$to_warehouse->email:''?>
            </address>
        </div>
    </div>

    <!-- Screen: Transfers Print - Items Grid (MRP and subtotal use Qty*MRP) -->
    <div class="table-responsive">
        <table class="table order-table">
            <thead>
                <tr>
                    <th style="text-align:center; vertical-align:middle;"><?= lang('No'); ?></th>
                    <th style="vertical-align:middle;"><?= lang('Description'); ?></th>
                    <th style="text-align:center; vertical-align:middle;"><?= lang('Category'); ?></th>
                    <th style="text-align:center; vertical-align:middle;"><?= lang('Quantity'); ?></th>
                    <th style="text-align:center; vertical-align:middle;"><?= lang('Price'); ?></th>
                    <?php if ($Settings->tax1) { echo '<th style="text-align:center; vertical-align:middle;">' . lang('Tax') . '</th>'; } ?>
                    <th style="text-align:center; vertical-align:middle;"><?= lang('Subtotal'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $r = 1;$totalqty = 0;$totalMrp = 0; $totalSubtotal = 0; foreach ($rows as $row): 
                    if($transfer->status=='sent_balance'){
                        $item_tax_rate = $row->tax_rate_id;
                        $unit_cost = $row->unit_cost;
                        $net_unit_cost = $row->net_unit_cost;
                        $item_code = $row->product_code;
                        $subtotal = $row->subtotal;
                        $pr_item_tax=$row->item_tax;
                        $Qty = $row->unit_quantity;
                        $item_tax = 0;
                        if($pr_item_tax==0){ $Qty = $row->sent_quantity; }
                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            $product_details = $this->transfers_model->getProductByCode($item_code);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                }
                            } elseif ($tax_details->type == 2) {
                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $Qty, 4);
                        }
                        $subtotal =  $this->sma->formatDecimal((($net_unit_cost * $Qty) + $pr_item_tax), 4);
                    } else {
                        $Qty = $row->unit_quantity;
                        $subtotal = $row->subtotal;
                        $pr_item_tax = $row->item_tax;
                    }
                    // Use MRP * Qty for displayed subtotal in print view
                    $line_subtotal = ($row->mrp * $Qty);
                ?>
                <tr>
                    <td style="text-align:center; width:25px;"><?= $r; ?></td>
                    <td style="text-align:left;"><?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''). ($row->shade_name ? ' (' . $row->shade_name . ')' : ''); ?></td>
                    <td style="text-align:left;"><?= $row->category_name; ?></td>
                    <td style="text-align:center; width:80px; "><?= $this->sma->formatQuantity($Qty).' '.$row->product_unit_code; ?></td>
                    <td style="width: 100px; text-align:right; padding-right:10px; vertical-align:middle;"><?= $this->sma->formatMoney($row->mrp * $Qty); ?></td>
                    <?php if ($Settings->tax1) { echo '<td style="width: 80px; text-align:right; vertical-align:middle;">' . $this->sma->formatMoney($pr_item_tax) . '</td>'; } ?>
                    <td style="width: 100px; text-align:right; padding-right:10px; vertical-align:middle;"><?= $this->sma->formatMoney($line_subtotal); ?></td>
                </tr>
                <?php $r++; $totalqty += $row->unit_quantity; $totalMrp += ($row->mrp * $Qty); $totalSubtotal += $line_subtotal; endforeach; ?>
            </tbody>
            <tfoot>
                <?php $col = 3; $tcol = 3; if ($Settings->tax1) { $col += 1; $tcol += 1; } if ($Settings->show_total_unit_quantity != 0) { $col = $col - 1; } ?>
                <tr>
                    <td colspan="<?= $col; ?>" class="text-right"><?= lang('Total'); ?></td>
                    <?php if ($Settings->show_total_unit_quantity != 0) { echo '<td class="text-right">'. $this->sma->formatQuantity($totalqty).'</td>'; } ?>
                    <td class="text-right"><?= $this->sma->formatMoney($totalMrp); ?></td>
                    <td class="text-right"><?= $this->sma->formatMoney($transfer->total_tax); ?></td>
                    <td class="text-right"><?= $this->sma->formatMoney($totalSubtotal); ?></td>
                </tr>
                <tr>
                    <td colspan="<?= $tcol+2; ?>" class="text-right bold"><?= lang('Total_Amount'); ?> (<?= $default_currency->code; ?>)</td>
                    <td class="text-right bold"><?= $this->sma->formatMoney($totalSubtotal + $transfer->total_tax); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Screen: Transfers Print - Notes (Full Width) -->
    <?php if ($transfer->note || $transfer->note != "") { ?>
        <div style="margin-top:10px;">
            <div class="well">
                <p class="bold"><?= lang('Note'); ?>:</p>
                <div><?= $this->sma->decode_html($transfer->note); ?></div>
            </div>
        </div>
    <?php } ?>

    <!-- Screen: Transfers Print - Signature Section -->
    <div class="row" style="display:flex; gap:40px; margin-top:10px;">
        <div style="flex:1;">
            <p><?= lang('Created_By'); ?>: <?= $created_by->first_name.' '.$created_by->last_name; ?></p>
            <p>&nbsp;</p><p>&nbsp;</p>
            <hr>
            <p><?= lang('Stamp_Sign'); ?></p>
        </div>
        <div style="flex:1;">
            <p><?= lang('Received_By'); ?>:</p>
            <p>&nbsp;</p><p>&nbsp;</p>
            <hr>
            <p><?= lang('Stamp_Sign'); ?></p>
        </div>
    </div>
</div>
<script>
    (function(){
        function getParam(name){
            var m = new RegExp('[?&]'+name+'=([^&]*)').exec(window.location.search);
            return m && decodeURIComponent(m[1].replace(/\+/g,'%20'));
        }
        function afterPrint(){
            var next = getParam('next');
            var back = getParam('back');
            if (next) {
                window.location.href = next;
            } else if (back) {
                window.location.href = back;
            }
        }
        
        // Ensure totals section only appears once (not duplicated)
        window.addEventListener('load', function(){
            var totalsSections = document.querySelectorAll('.totals-section');
            if (totalsSections.length > 1) {
                // Remove all except the last one
                for (var i = 0; i < totalsSections.length - 1; i++) {
                    totalsSections[i].remove();
                }
            }
            
            if ('onafterprint' in window) {
                window.onafterprint = function(){ setTimeout(afterPrint, 50); };
            } else {
                setTimeout(afterPrint, 600);
            }
            setTimeout(function(){ window.print(); }, 150);
        });
    })();
</script>
</body>
</html>
