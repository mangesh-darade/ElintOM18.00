<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Screen: Purchase Modal Print View (modal_view_print)
$itemTaxes = isset($inv->rows_tax)?$inv->rows_tax:array();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= lang('purchase_details'); ?> - <?= $inv->reference_no; ?></title>
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
        /* Totals section styling */
        .totals-section { border: 1px solid #ddd; margin-top: 10px; }
        .totals-section table { border: none; }
        .totals-section td { border: none; border-bottom: 1px solid #ddd; padding: 8px; }
        
        @media print {
            .no-print { display:none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            /* Table header repeats on each page */
            thead { display: table-header-group; }
            /* Hide any tfoot to prevent repetition */
            tfoot { display: none !important; }
            /* Prevent breaks inside rows */
            tbody tr { page-break-inside: avoid; page-break-after: auto; }
            /* Totals and tax sections stay together at document end */
            .totals-section, .tax-summary-section { 
                display: block !important;
                page-break-before: auto;
                page-break-after: auto;
                page-break-inside: avoid !important;
            }
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
    <?php if ($logo) { ?>
        <div class="text-center" style="margin-bottom:15px;">
            <img src="<?= base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
        </div>
        <div class="text-center" style="margin-bottom:15px;">
                <h2><strong>PURCHASE ORDER</strong> </h2>
            </div>
    <?php } ?>

    <!-- Screen: Purchase Print - Header -->
    <div class="well">
        <div class="row bold" style="display:flex; align-items:flex-start;">
            <div style="flex:1;">
                <p>
                    <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                    <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                    <?php if (!empty($inv->return_purchase_ref)) {
                        echo lang("return_ref").': '.$inv->return_purchase_ref; echo '<br>';
                    } ?>
                  
                </p>
            </div>
            <div class="order_barcodes text-right" style="flex:1;">
                <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                <?= $this->sma->qrcode('link', urlencode(site_url('purchases/view/' . $inv->id)), 2); ?>
            </div>
        </div>
    </div>

    <!-- Screen: Purchase Print - From and Supplier Details -->
    <div class="row" style="display:flex; gap:20px; margin-bottom:10px;">
        <div style="flex:1;">
            <strong><?= $this->lang->line("Delivery Location"); ?></strong>
            <h2 style="margin:6px 0;"><?= $Settings->site_name; ?></h2>
            <?php if ($biller->gstn_no != "-" && $biller->gstn_no != "" && count($itemTaxes) > 0) { echo  '<strong>'.lang("gstn_no")." : </strong>". $biller->gstn_no."<br>"; } ?>
            <?php foreach($warehouse as $ware){ ?>
                <?= ($ware->name!='')?'<b> Location : </b> '.$ware->name:'' ?>
                <address>
                    <?= ($biller->address!='')?'<b> Address : </b> '.$biller->address:'' ?>
                    <?= ($biller->phone!='')?'<br/><b> '.lang("tel").' : </b> '.$biller->phone:''?>
                    <?= ($biller->email!='')?'<br/><b> '.lang("email").' : </b>'.$biller->email:''?>
                </address>
            <?php } ?>
        </div>
        <div style="flex:1;">
            <strong><?= $this->lang->line("To Vendor"); ?></strong>
            <h2 style="margin:6px 0;"><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
            <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>
            <address>
                <?= ($supplier->address!='')?'<b> Address : </b> '.$supplier->address.', <br/>':'' ?>
                <?= ($supplier->city!='')?$supplier->city.' - ':'' ?> <?= ($supplier->postal_code!='')?$supplier->postal_code.', ':'' ?>
                <?= ($supplier->state!='')?$supplier->state.', ':'' ?> <?= ($supplier->state!='')?$supplier->country.'. ':'' ?>
                <?= ($supplier->phone!='')?'</br><b> '.lang("tel").' : </b> '.$supplier->phone:'' ?>
                <?= ($supplier->email!='')?'</br><b> '.lang("email").' : </b> '.$supplier->email:'' ?>
                <?php 
                    if ($supplier->gstn_no != "-" && $supplier->gstn_no != "") {
                        echo "<br><b> " . lang("gstn_no") . " : </b>" . $supplier->gstn_no ;
                    } elseif ($supplier->vat_no != "-" && $supplier->vat_no != "" && count($itemTaxes) ==0) {
                        echo "<br><b> " . lang("vat_no") . " :  </b>" . $supplier->vat_no;
                    }
                    if ($supplier->cf1 != "-" && $supplier->cf1 != "") { echo "<br><b> ".$this->Settings->prd_cmfield1 . " :  </b>". $supplier->cf1; }
                    if ($supplier->cf2 != "-" && $supplier->cf2 != "") { echo "<br><b> ".$this->Settings->prd_cmfield2 . " : </b> ". $supplier->cf2; }
                    if ($supplier->cf3 != "-" && $supplier->cf3 != "") { echo "<br><b> ".$this->Settings->prd_cmfield3 . " : </b> ". $supplier->cf3; }
                    if ($supplier->cf4 != "-" && $supplier->cf4 != "") { echo "<br><b> " .$this->Settings->prd_cmfield4 .  " : </b>". $supplier->cf4; }
                    if ($supplier->cf5 != "-" && $supplier->cf5 != "") { echo "<br><b> "  .$this->Settings->prd_cmfield5 .  " : </b>". $supplier->cf5; }
                    if ($supplier->cf6 != "-" && $supplier->cf6 != "") { echo "<br><b> ".$this->Settings->prd_cmfield6 ." : </b> ". $supplier->cf6; }
                ?>
            </address>
        </div>
    </div>

    <!-- Screen: Purchase Print - Items Grid -->
    <div class="table-responsive">
        <table class="table order-table">
            <thead>
                <tr>
                    <th><?= lang("no"); ?></th>
                    <th><?= lang("description"); ?></th>
                    <th><?= lang("batch_number"); ?></th>
                    <?php if ($Owner || $Admin || ($GP['products-cost']=='1')) { ?>
                        <th><?= lang("unit_cost"); ?></th>
                    <?php } ?>
                    <th><?= lang("quantity"); ?></th>
                    <?php if ($inv->status == 'partial') { echo '<th>'.lang("received").'</th>'; } ?>
                    <?php if ($Owner || $Admin || ($GP['products-cost']=='1')) { ?>
                        <th><?= lang("Net_Cost"); ?></th>
                    <?php } ?>
                    <?php if ($Settings->tax1 && $inv->product_tax > 0) { echo '<th>' . lang("tax") . '</th>'; } ?>
                    <?php if ($Settings->product_discount && $inv->product_discount != 0) { echo '<th>' . lang("discount") . '</th>'; } ?>
                    <th><?= lang("subtotal"); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $r = 1; $tax_summary = array(); $total_netcost = 0; $totalqty=0; foreach ($rows as $row): $offset=6; if($row->tax_code==''){ $row->tax_code='0GST'; }
                if (isset($tax_summary[$row->tax_code])) {
                    $tax_summary[$row->tax_code]['items'] += $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity* $row->net_unit_cost);
                } else {
                    $tax_summary[$row->tax_code] = array('items'=>$row->quantity,'tax'=>$row->item_tax,'amt'=>($row->unit_quantity*$row->net_unit_cost),'name'=>$row->tax_name,'code'=>$row->tax_code,'rate'=>$row->tax_rate,'tax_rate_id'=>$row->tax_rate_id);
                }
            ?>
            <tr>
                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                <td style="vertical-align:middle;">
                    <?php if($Settings->purchase_image == '1') { ?>
                        <img src="assets/mdata/<?= $Customer_assets ?>/uploads/thumbs/<?=$row->image?>" style="width:30px; height:30px;" alt="<?=$row->product_code?>" />
                    <?php } ?>
                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''). ($row->shade_name ? ' (' . $row->shade_name . ')' : ''); ?>
                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                </td>
                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $row->batch_number; ?></td>
                <?php if ($Owner || $Admin || ($GP['products-cost']=='1')) { ?>
                    <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_cost); ?></td>
                <?php } ?>
                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity)?></td>
                <?php if ($inv->status == 'partial') { echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>'; }
                    $total_netcost += ($row->real_unit_cost*$row->unit_quantity);
                ?>
                <?php if ($Owner || $Admin || ($GP['products-cost']=='1')) { ?>
                    <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_cost*$row->unit_quantity); ?></td>
                <?php } ?>
                <?php if ($Settings->tax1 && $inv->product_tax > 0) { echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>'; $offset++; }
                if ($Settings->product_discount && $inv->product_discount != 0) { echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>'; $offset++; }
                ?>
                <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
            </tr>
            <?php 
                $dynamic = $this->sma->taxAttrTBL_csi_dynamic($row, 'purchase', $offset);
                $purchase_tax_prdWise = !empty($dynamic) ? $dynamic : $this->sma->taxAttrTBL_csi($row->gst_rate, $row->cgst, $row->sgst, $row->igst, $offset);
                echo $purchase_tax_prdWise;
                $r++; $totalqty += $row->unit_quantity;
            endforeach; ?>
            <?php 
                // Compute colspan to align the totals under Net Cost, Tax, Discount, Subtotal
                if ($Owner || $Admin || ($GP['products-cost']=='1')) { $col = 5; } else { $col = 3; }
                if ($inv->status == 'partial') { $col++; }
                if ($Settings->product_discount && $inv->product_discount != 0) { $col++; }
                if ($Settings->tax1 && $inv->product_tax > 0) { $col++; }
                if ( $Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) { $tcol_inline = $col - 2; }
                elseif ( $Settings->product_discount && $inv->product_discount != 0) { $tcol_inline = $col - 1; }
                elseif ($Settings->tax1 && $inv->product_tax > 0) { $tcol_inline = $col - 1; } else { $tcol_inline = $col; }
            ?>
            <?php if ($inv->grand_total != $inv->total) { ?>
            <tr>
                <td colspan="<?= $tcol_inline; ?>" class="text-right"><strong><?= lang("total"); ?> (<?= $default_currency->code; ?>)</strong></td>
                <td class="text-right"><?= $this->sma->formatMoney($total_netcost)?></td>
                <?php if ($Settings->tax1 && $inv->product_tax > 0) { echo '<td class="text-right">' . $this->sma->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax) . '</td>'; } ?>
                <?php if ($Settings->product_discount && $inv->product_discount != 0) { echo '<td class="text-right">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>'; } ?>
                <td class="text-right"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Totals Section (Only shows at end, not on every page) -->
    <div class="totals-section" id="purchase-totals-footer">
        <table class="table">
            <tbody>
            <?php
                if ($Owner || $Admin || ($GP['products-cost']=='1')) { $col = 5; } else { $col = 3; }
                if ($inv->status == 'partial') { $col++; }
                if ($Settings->product_discount && $inv->product_discount != 0) { $col++; }
                if ($Settings->tax1 && $inv->product_tax > 0) { $col++; }
                if ( $Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) { $tcol = $col - 2; }
                elseif ( $Settings->product_discount && $inv->product_discount != 0) { $tcol = $col - 1; }
                elseif ($Settings->tax1 && $inv->product_tax > 0) { $tcol = $col - 1; } else { $tcol = $col; }
                $cc = $col+1;
            ?>
            <?php /* moved the column-aligned totals row inside the items table for exact alignment */ ?>
            <?php if ($return_purchase) { echo '<tr><td colspan="'.$cc.'" class="text-right">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>'; }
            if ($inv->surcharge != 0) { echo '<tr><td colspan="' . $cc . '" class="text-right">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>'; }
            if ($inv->order_discount != 0) { echo '<tr><td colspan="' . $cc . '" class="text-right">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td class="text-right">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>'; }
            if ($Settings->tax2 && $inv->order_tax != 0) { echo '<tr><td colspan="' . $cc . '" class="text-right">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>'; }
            if ($inv->shipping != 0) { echo '<tr><td colspan="' . $cc . '" class="text-right">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>'; }
            if ($inv->rounding != 0.0000) { echo '<tr><td colspan="' . $cc . '" class="text-right">' . lang("rounding") . '</td><td class="text-right">' . $this->sma->formatMoney($inv->rounding) . '</td></tr>'; }
            ?>
            <tr>
                <td colspan="<?= $cc; ?>" class="text-right bold"><?= lang("total_amount"); ?> (<?= $default_currency->code; ?>)</td>
                <td class="text-right bold"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : ($inv->grand_total+$inv->rounding)); ?></td>
            </tr>
            <tr>
                <td colspan="<?= $cc; ?>" class="text-right bold"><?= lang("paid"); ?> (<?= $default_currency->code; ?>)</td>
                <td class="text-right bold"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); ?></td>
            </tr>
            <tr>
                <td colspan="<?= $cc; ?>" class="text-right bold"><?= lang("balance"); ?> (<?= $default_currency->code; ?>)</td>
                <td class="text-right bold"><?= $this->sma->formatMoney(($return_purchase ? (($inv->grand_total+$inv->rounding)+$return_purchase->grand_total) : ($inv->grand_total+$inv->rounding)) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Screen: Purchase Print - Tax Summary (Full Width) -->
    <?php if ($Settings->invoice_view_purchase == 1) { ?>
        <div class="tax-summary-section" style="margin-top:15px;">
            <?php echo $this->sma->getTaxpurchaseReport($inv->id, $Settings); ?>
        </div>
    <?php } ?>

    <!-- Screen: Purchase Print - Notes and Creation Details -->
    <div class="row" style="display:flex; gap:20px; margin-top:10px;">
        <div style="flex:2;">
            <?php if ($inv->note || $inv->note != "") { ?>
                <div class="well">
                    <p class="bold"><?= lang("note"); ?>:</p>
                    <div><?= $this->sma->decode_html($inv->note); ?></div>
                </div>
            <?php } ?>
        </div>
        <div style="flex:1;">
            <div class="well">
                <p>
                    <?= lang("created_by"); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                    <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?>
                </p>
                <?php if ($inv->updated_by) { ?>
                <p>
                    <?= lang("updated_by"); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?><br>
                    <?= lang("update_at"); ?>: <?= $this->sma->hrld($inv->updated_at); ?>
                </p>
                <?php } ?>
            </div>
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
