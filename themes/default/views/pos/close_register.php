<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;"
                onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>

            <h4 class="modal-title" id="myModalLabel">
                <?= lang('close_register') . ' (' . $this->sma->hrld($register_open_time ? $register_open_time : $this->session->userdata('register_open_time')) . ' - ' . $this->sma->hrld(date('Y-m-d H:i:s')) . ')'; ?>
            </h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("pos/close_register/" . $user_id, $attrib);
        ?>
        <div class="modal-body">
            <div id="alerts"></div>

            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('cash_in_hand'); ?>:</h4>
                    </td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($this->session->userdata('cash_in_hand')); ?></span>
                        </h4>
                    </td>
                </tr>
                <?php if (!empty($dynamic_payment_rows) && is_array($dynamic_payment_rows)) { ?>
                    <?php foreach ($dynamic_payment_rows as $dynamic_row) { ?>
                        <tr>
                            <td style="border-bottom: 1px solid #EEE;">
                                <h4><?= $dynamic_row['label']; ?>:</h4>
                            </td>
                            <td style="text-align:right;border-bottom: 1px solid #EEE;">
                                <h4>
                                    <span><?= $this->sma->formatMoney(isset($dynamic_row['paid']) ? $dynamic_row['paid'] : 0); ?></span>
                                </h4>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>

                <tr>
                    <td width="300px;" style="font-weight:bold; border-bottom: 1px solid #DDD;""><h4><strong><?= lang('Total Paid'); ?>:</strong></h4></td>
                    <td width=" 200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                            <span><strong><?= $this->sma->formatMoney((isset($totalsales->paid) && $totalsales->paid) ? $totalsales->paid : '0.00') ?></strong> </span>
                          
                        </h4></td>
                </tr>    
                
                <tr>
                    <td width=" 300px;" style="font-weight:bold;border-bottom: 1px solid #DDD;""><h4><strong><?= lang('total_sales'); ?>:</strong></h4></td>
                    <td width=" 200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                          <?php /* legacy totalsales row — was totalsales+duesales+refunds combined */ ?>
                          <span><strong><?= $this->sma->formatMoney((isset($totalsales->paid) && $totalsales->paid) ? $totalsales->paid + (isset($duesales->duetotal) ? $duesales->duetotal : 0) : '0.00') ?></strong> </span>
                            <?php /* legacy paid/total display comment */ ?>
                        </h4></td>
                </tr>
                
                <tr>
                    <td width=" 300px;" style="font-weight:bold; border-bottom: 1px solid #DDD;""><h4><strong><?= lang('Total Due'); ?>: </strong></h4></td>
                    <td width=" 200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                            <span><strong><?= $this->sma->formatMoney((isset($duesales->duetotal) ? $duesales->duetotal : 0) + (isset($duepartial->partial_due) ? $duepartial->partial_due : 0)) ?> </strong></span>
                          
                        </h4></td>
                </tr>   
                
                <tr>
                    <td style=" border-top: 1px solid #DDD;">
                        <h4><?= lang('Refunds On Cash'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-top: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney(isset($refunds->returned) && $refunds->returned ? $refunds->returned : '0.00')?></span>
                            <?php /* legacy refunds total comment */ ?>
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;">
                        <h4><?= lang('Refunds On Other'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-top: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney(isset($returned_other->returned) && $returned_other->returned ? $returned_other->returned : '0.00') ?></span>
                            <?php /* legacy refunds other total comment */ ?>
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('expenses'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->sma->formatMoney($expense) ?></span>
                            <!--<span><?php // $expense = $expenses ? $expenses->total : 0; echo $this->sma->formatMoney($expense) . ' (' . $this->sma->formatMoney($expense) . ')'; ?></span>-->
                        </h4>
                    </td>
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
                    <td width="300px;" style="font-weight:bold;">
                        <h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;">
                        <h4>
                            <?php 
                                $bank_deposit = !empty($bank_details->bank_deposit) ? $bank_details->bank_deposit : 0;
                                $withdrawal   = !empty($bank_details->withdrawal) ? $bank_details->withdrawal : 0;
                                $total_cash_amount = (isset($cashsales->paid) && $cashsales->paid)
                                    ? (($cashsales->paid + ($this->session->userdata('cash_in_hand'))) 
                                        + (isset($refunds->returned) && $refunds->returned ? $refunds->returned : 0) 
                                        - $expense)
                                    : ($this->session->userdata('cash_in_hand') - $expense);
                                $total_cash_amount = $total_cash_amount - $bank_deposit - $withdrawal;
                            ?>
                            <span><strong><?= $this->sma->formatMoney($total_cash_amount); ?></strong></span>
                        </h4>
                    </td>
                </tr>
                <!--  show deposite recived and paid by only when add_deposit_btn_show = enable -->
                <?php if ($pos_settings->add_deposit_btn_show == 1 && !empty($deposit_received)) { ?>
                <tr>
                    <td width="300px;" style="font-weight:bold;">
                        <h4><strong><?= lang('Deposit Received'); ?></strong>:</h4>
                        <span style="font-size:12px; font-weight: normal;">Paid By :
                            <?= isset($deposit_received->paid_by) ? $deposit_received->paid_by : '' ?></span>
                    </td>
                    <td style="text-align:right;">
                        <h4>
                            <span><strong><?= $this->sma->formatMoney(isset($deposit_received->deposit_amount) ? $deposit_received->deposit_amount : 0); ?></strong></span>
                        </h4>
                    </td>
                </tr>
                <?php } ?>
            </table>

            <?php

                if ($suspended_bills) {
                    echo '<hr><h3>' . lang('opened_bills') . '</h3><table class="table table-hovered table-bordered"><thead><tr><th>' . lang('customer') . '</th><th>' . lang('date') . '</th><th>' . lang('total_items') . '</th><th>' . lang('amount') . '</th>';
                    if ($Owner || $Admin || $GP['sales-delete-suspended']) {
                        echo '<th><i class="fa fa-trash-o"></i></th>';
                    }
                    echo '</tr></thead><tbody>';
                    foreach ($suspended_bills as $bill) {
                        echo '<tr><td>' . $bill->customer . '</td><td>' . $this->sma->hrld($bill->date) . '</td><td class="text-center">' . $bill->count . '</td><td class="text-right">' . $bill->total . '</td>';
                        if ($Owner || $Admin || $GP['sales-delete-suspended']) {
                            echo '<td class="text-center"><a href="#" class="tip po" title="<b>' . $this->lang->line("delete_bill") . '</b>" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger po-delete\' href=\'' . site_url('pos/delete/' . $bill->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"  rel="popover"><i class="fa fa-trash-o"></i></a></td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

            ?>
            <hr>
            <div class="row no-print">
                <div class="col-sm-6">
                    <!-- <div class="form-group">
                        <?= lang("total_cash", "total_cash_submitted"); ?>
                        <?= form_hidden('total_cash', $total_cash_amount); ?>
                        <?= form_input('total_cash_submitted', (isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash_amount), 'class="form-control input-tip" id="total_cash_submitted" required="required"'); ?>
                    </div> -->
                    <div class="form-group row">
                        <?php
                            $col_class = ($pos_settings->display_coinage == 1 || $pos_settings->display_coinage == 2) ? 'col-xs-6' : 'col-xs-12';
                        ?>
                        <div class="<?= $col_class ?>">
                            <?= lang("total_cash", "total_cash_submitted"); ?>
                            <?= form_hidden('total_cash', $total_cash_amount); ?>
                            <?php
                                $readonly = (isset($pos_settings->display_coinage) && $pos_settings->display_coinage) ? 'readonly' : '';
                                $input_value = isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash_amount;
                            ?>
                            <?= form_input('total_cash_submitted', $input_value, 'class="form-control input-tip" id="total_cash_submitted" required="required" ' . $readonly); ?>
                        </div>

                        <?php if ($pos_settings->display_coinage == 1 || $pos_settings->display_coinage == 2) { ?>
                        <div class="col-xs-6" style="margin-top: 25px;">
                            <a href="#" id="update_register_btn" class="btn btn-primary btn-block" data-toggle="modal"
                                data-target="#updateRegisterModal">
                                <i class="fa fa-refresh"></i> <?= lang('Update_Register'); ?>
                            </a>
                        </div>
                        <?php } ?>


                    </div>
                    <div class="form-group">
                        <?= lang("total_cheques", "total_cheques_submitted"); ?>
                        <?= form_hidden('total_cheques', $chsales->total_cheques); ?>
                        <?= form_input('total_cheques_submitted', (isset($_POST['total_cheques_submitted']) ? $_POST['total_cheques_submitted'] : $chsales->total_cheques), 'class="form-control input-tip" id="total_cheques_submitted" required="required"'); ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <?php if ($suspended_bills) { ?>
                        <div class="form-group">
                            <?= lang("transfer_opened_bills", "transfer_opened_bills"); ?>
                            <?php $u = $user_id ? $user_id : $this->session->userdata('user_id');
                            if ($Owner || $Admin || $GP['sales-delete-suspended']) {
                                $usrs[-1] = lang('delete_all');
                            }
                            $usrs[0] = lang('leave_opened');
                            foreach ($users as $user) {
                                if ($user->id != $u) {
                                    $usrs[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                            }
                            ?>
                            <?= form_dropdown('transfer_opened_bills', $usrs, (isset($_POST['transfer_opened_bills']) ? $_POST['transfer_opened_bills'] : 0), 'class="form-control input-tip" id="transfer_opened_bills" required="required"'); ?>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <?= lang("total_cc_slips", "total_cc_slips_submitted"); ?>
                        <?= form_hidden('total_cc_slips', $ccsales->total_cc_slips); ?>
                        <?= form_input('total_cc_slips_submitted', (isset($_POST['total_cc_slips_submitted']) ? $_POST['total_cc_slips_submitted'] : $ccsales->total_cc_slips), 'class="form-control input-tip" id="total_cc_slips_submitted" required="required"'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group no-print">
                <label for="note"><?= lang("note"); ?></label>

                <div class="controls">
                    <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                </div>
            </div>

        </div>
        <div class="modal-footer no-print">
            <?= form_submit('close_register', lang('close_register'), 'class="btn btn-primary" id="close-register-btn"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

</div>
<?= $modal_js ?>
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.po', function(e) {
        e.preventDefault();
        $('.po').popover({
            html: true,
            placement: 'left',
            trigger: 'manual'
        }).popover('show').not(this).popover('hide');
        return false;
    });
    $(document).on('click', '.po-close', function() {
        $('.po').popover('hide');
        return false;
    });
    $(document).on('click', '.po-delete', function(e) {
        var row = $(this).closest('tr');
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        $.ajax({
            type: "get",
            url: link,
            success: function(data) {
                row.remove();
                addAlert(data, 'success');
            },
            error: function(data) {
                addAlert('Failed', 'danger');
            }
        });
        return false;
    });
});

function addAlert(message, type) {
    $('#alerts').empty().append(
        '<div class="alert alert-' + type + '">' +
        '<button type="button" class="close" data-dismiss="alert">' +
        '&times;</button>' + message + '</div>');
}
$(document).ready(function() {
    $('#update_register_btn').on('click', function(e) {
        e.preventDefault();
        $('#myModal').html(
            '<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');

        $.get('<?= site_url('pos/update_register'); ?>', function(data) {
            $('#myModal').html(data);
        });
    });
});
document.getElementById('close-register-btn').addEventListener('click', function(e) {
    const confirmed = confirm("Are you sure you want to close the register?");
    if (!confirmed) {
        e.preventDefault();
    }
});
</script>
<style>
a#update_register_btn {
    margin-top: 5px;
}

input#total_cash_submitted {
    text-align: right;
}

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

    #myModal .table,
    #myModal table.table {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #myModal .table thead,
    #myModal .table tbody,
    #myModal .table tfoot {
        display: table-row-group !important;
    }

    #myModal .table tr {
        display: table-row !important;
    }

    #myModal .table th,
    #myModal .table td {
        display: table-cell !important;
    }

    #myModal .no-print {
        display: none !important;
    }

    #myModal .opened-bills-section {
        display: none !important;
    }
}
</style>