<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('day_profit').' ('.$this->sma->hrsd($date).')'; ?></h4>
        </div>
        <div class="modal-body">
            <p><?= lang('unit_and_net_tip'); ?></p>
            <div class="table-responsive">
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('products_sale'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <!--<span><?= $this->sma->formatMoney($costing->sales); ?></span></h4>-->
                            <!--<span><?= $this->sma->formatMoney($costing->sales).' ('.$this->sma->formatMoney($costing->net_sales).')'; ?></span></h4>-->
                            <span><?= $this->sma->formatMoney($saleData->grand_total).' ('.$this->sma->formatMoney($saleData->total).')'; ?></span>
                    </td>
                </tr>
                <!-- <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('order_discount'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?php $discount = $discount ? $discount->order_discount : 0; echo $this->sma->formatMoney($discount); ?></span>
                            <span><?php $discount = $saleData->total_discount ? $saleData->total_discount : 0; echo $this->sma->formatMoney($discount); ?></span>
                        </h4>
                    </td>
                </tr> -->
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('products_cost'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <!--<span><?= $this->sma->formatMoney($costing->cost); ?></span>-->
                             <span><?= $this->sma->formatMoney($costing->cost).' ('.$this->sma->formatMoney($costing->net_cost).')'; ?></span> 
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->sma->formatMoney($expense); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('profit'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                            <!--<span><strong><?= $this->sma->formatMoney($costing->sales - $costing->cost - $discount - $expense); ?></strong></span>-->
                             <!-- <span><strong><?= $this->sma->formatMoney($saleData->grand_total - $costing->cost - $discount - $expense).' ('.$this->sma->formatMoney($saleData->total - $costing->net_cost - $discount - $expense).')'; ?></strong></span>  -->
                             <span><strong><?= $this->sma->formatMoney($saleData->grand_total - $costing->cost - $expense).' ('.$this->sma->formatMoney($saleData->total - $costing->net_cost - $expense).')'; ?></strong></span> 
                        </h4></td>
                </tr>
                <?php if (isset($returns->total)) { ?>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('return_sales'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                            <span><strong><?= $this->sma->formatMoney($returns->total).' ('.$this->sma->formatMoney($returns->total - $returns->total_tax)  .')'; ?></strong></span>
                        </h4></td>
                </tr>
                <?php } ?>
            </table>
            </div>
        </div>
    </div>

</div>

<style>
@media print {
    /* Reset page margins and sizing */
    @page {
        margin: 0.5in !important;
        size: auto !important;
    }
    
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        overflow: visible !important;
        background: white !important;
    }
    
    /* Hide everything by default */
    body * {
        visibility: hidden;
    }
    
    /* Show modal content with tight layout */
    .modal-dialog,
    .modal-dialog *,
    .modal-content,
    .modal-content *,
    .modal-header,
    .modal-header *,
    .modal-body,
    .modal-body *,
    .table-responsive,
    .table-responsive *,
    .stable,
    .stable * {
        visibility: visible !important;
    }
    
    .modal-dialog {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 10px !important;
        height: auto !important;
        max-height: none !important;
    }
    
    .modal-content {
        display: block !important;
        width: 100% !important;
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        background: white !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }
    
    .modal-header {
        border-bottom: 1px solid #ddd !important;
        padding: 10px !important;
        margin-bottom: 10px !important;
        background: white !important;
        height: auto !important;
    }
    
    .modal-header h4 {
        font-size: 16px !important;
        font-weight: bold !important;
        color: #333 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .modal-body {
        padding: 10px !important;
        height: auto !important;
        overflow: visible !important;
    }
    
    .modal-body p {
        font-style: italic !important;
        color: #666 !important;
        margin: 0 0 10px 0 !important;
        padding: 0 !important;
    }
    
    .table-responsive {
        width: 100% !important;
        overflow: visible !important;
        height: auto !important;
    }
    
    .stable {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 0 !important;
        height: auto !important;
    }
    
    .stable td {
        padding: 6px !important;
        border-bottom: 1px solid #eee !important;
        color: #333 !important;
        font-size: 13px !important;
        height: auto !important;
    }
    
    .stable h4 {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 13px !important;
        color: #333 !important;
        font-weight: bold !important;
        height: auto !important;
    }
    
    .stable span {
        color: #333 !important;
        font-weight: normal !important;
    }
    
    /* Hide buttons */
    .close, button, .no-print {
        display: none !important;
    }
    
    /* Prevent extra pages and page breaks */
    .modal-content {
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
    }
    
    .modal-dialog {
        page-break-after: avoid !important;
    }
    
    /* Ensure content fits on one page */
    html {
        height: auto !important;
        overflow: visible !important;
    }
    
    body {
        height: auto !important;
        overflow: visible !important;
        min-height: auto !important;
    }
    
    /* Remove any potential height constraints */
    .modal-dialog,
    .modal-content,
    .modal-header,
    .modal-body,
    .table-responsive,
    .stable {
        min-height: auto !important;
        max-height: none !important;
    }
}
</style>