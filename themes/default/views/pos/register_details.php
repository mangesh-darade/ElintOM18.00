<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
          
            <h4 class="modal-title"
                id="myModalLabel"><?= lang('sales') . ' (' . $this->sma->hrld($this->session->userdata('register_open_time')) . ' - ' . $this->sma->hrld(date('Y-m-d H:i:s')) . ')'; ?></h4>
        </div>
        <div class="modal-body">
       
         
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($this->session->userdata('cash_in_hand')); ?></span></h4>
                    </td>
                </tr>
                <?php if (!empty($dynamic_payment_rows) && is_array($dynamic_payment_rows)) { ?>
                    <?php foreach ($dynamic_payment_rows as $dynamic_row) { ?>
                        <tr>
                            <td style="border-bottom: 1px solid #EEE;"><h4><?= $dynamic_row['label']; ?>:</h4></td>
                            <td style="text-align:right;border-bottom: 1px solid #EEE;">
                                <h4><span><?= $this->sma->formatMoney(isset($dynamic_row['paid']) ? $dynamic_row['paid'] : 0); ?></span></h4>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                
                <tr>
                    <td width="300px;" style="font-weight:bold; border-bottom: 1px solid #DDD;""><h4><strong><?= lang('Total Paid'); ?>:</strong></h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                            <span><strong><?= $this->sma->formatMoney((isset($totalsales->paid) ? $totalsales->paid : 0)) ?></strong> </span>
                          
                        </h4></td>
                </tr>
                 <tr>
                   
                    <td width="300px;" style="font-weight:bold; border-bottom: 1px solid #DDD;""><h4><strong><?= lang('Total Due'); ?>: </strong></h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                            <span><strong>   <?= $this->sma->formatMoney((isset($duesales->duetotal) ? $duesales->duetotal : 0) + (isset($duepartial->partial_due) ? $duepartial->partial_due : 0)) ?> </strong></span>
                          
                        </h4></td>
                </tr>  
                 <tr>
                    <td width="300px;" style="font-weight:bold;border-bottom: 1px solid #DDD;""><h4><strong><?= lang('total_sales'); ?>:</strong></h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                          <?php /* legacy totalsales row removed — was totalsales+duesales+refunds combined */ ?>
                          <span><strong><?= $this->sma->formatMoney((isset($totalsales->paid) && $totalsales->paid) ? $totalsales->paid + (isset($duesales->duetotal) ? $duesales->duetotal : 0) : '0.00') ?></strong> </span>
                        </h4></td>
                </tr>
                 
               
                <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('Refunds  On Cash'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney(isset($refunds->returned) && $refunds->returned ? $refunds->returned : '0.00') ?></span>
                            <?php /* legacy refunds total comment */ ?>
                        </h4></td>
                </tr>

               <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('Refunds On Other'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney(isset($refunds->returned_other) && $refunds->returned_other ? $refunds->returned_other : '0.00') ?></span>
                            <?php /* legacy refunds other total comment */ ?>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->sma->formatMoney($expense) ?></span>
                            <?php /* legacy expense comment */ ?>
                        </h4></td>
                </tr>

                <!-- for showing withdrawal and bank deposite (28-11-2025) -->
                <?php if ($pos_settings->display_coinage == 1 || $pos_settings->display_coinage == 2) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('Bank Deposit'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span>
                                <?= $this->sma->formatMoney(!empty($bank_details->bank_deposit) ? $bank_details->bank_deposit : 0); ?>
                            </span>
                        </h4>
                    </td>
                </tr>

                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('Withdrawal'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span>
                                <?= $this->sma->formatMoney(!empty($bank_details->withdrawal) ? $bank_details->withdrawal : 0); ?>
                            </span>
                        </h4>
                    </td>
                </tr>
                <?php } ?>
                <!-- substracting the bank deposite and withdrawal amount in total cash(28-11-2025) -->
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                          <span><strong><?= (isset($cashsales->paid) && $cashsales->paid)
                            ? $this->sma->formatMoney(
                                ($cashsales->paid + ($this->session->userdata('cash_in_hand')))
                                + (isset($refunds->returned) && $refunds->returned ? $refunds->returned : 0)
                                - $expense
                                - (!empty($bank_details->bank_deposit) ? $bank_details->bank_deposit : 0)
                                - (!empty($bank_details->withdrawal) ? $bank_details->withdrawal : 0)
                            )
                            : $this->sma->formatMoney(
                                ($this->session->userdata('cash_in_hand')
                                - $expense
                                - (!empty($bank_details->bank_deposit) ? $bank_details->bank_deposit : 0)
                                - (!empty($bank_details->withdrawal) ? $bank_details->withdrawal : 0))
                            );
                        ?></strong></span>

                        </h4></td>
                </tr>

             <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('Deposit Received'); ?></strong>:</h4>
                        <span style="font-size:12px; font-weight: normal;">Paid By : <?= (isset($deposit_received->paid_by) ? $deposit_received->paid_by : '') ?></span>
                    </td>
                    <td style="text-align:right;"><h4>
                          <span><strong><?= $this->sma->formatMoney(isset($deposit_received->deposit_amount) ? $deposit_received->deposit_amount : 0) ?></strong></span>

                        </h4></td>
                </tr>
               
            </table>
        </div>
    </div>

</div>



<style>
    @media print {
        body * {
            visibility: hidden !important;
        }

        #myModal,
        #myModal * {
            visibility: visible !important;
        }

        #myModal {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        #myModal .modal-dialog {
            width: 100% !important;
            margin: 0 !important;
        }

        #myModal .modal-content {
            border: 0 !important;
            box-shadow: none !important;
        }

        #myModal .no-print {
            display: none !important;
        }
    }
</style>
