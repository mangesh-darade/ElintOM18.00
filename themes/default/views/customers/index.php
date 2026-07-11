<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
#s2id_autogen1 {
    width: 75px;
}
a#main-menu-act {
    left: 205px; /* Sidebar width */
}
</style>
<script>
$(document).ready(function() {
    var cTable = $('#CusData').dataTable({
        "aaSorting": [
            [1, "asc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, 500, 1000, 2000, 5000, -1],
            [10, 25, 50, 100, 500, 1000, 2000, 5000, "<?= lang('all') ?>"]
        ],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= site_url('customers/getCustomers') ?>',
        'fnServerData': function(sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
        },
        'fnRowCallback': function(nRow, aData, iDisplayIndex) {
            nRow.id = aData[0];
            nRow.className = "customer_details_link";
            /* Gift Card Column Add*/
            var nCells = nRow.getElementsByTagName('td');
            var id = nRow.id;
            console.log(id);
            var url = '<?= site_url("Customers/getGiftBalance/") ?>?id=' + id;
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                url: url,
                async: true,
                success: function(result) {
                    console.log(result);
                    nCells[12].innerHTML = currencyFormat(result.opening_balance);
                    nCells[13].innerHTML = currencyFormat(result.closing_balance);
                    nCells[14].innerHTML = currencyFormat(result.giftcard);
                    // nRow.className = "text-right text-capitalize";
                },
                error: function() {
                    nCells[14].innerHTML = currencyFormat(0);
                }
            });

            //nCells[12].innerHTML =  currencyFormat(100);  
            // nCells[13].innerHTML =  currencyFormat(100);  

            return nRow;
        },
        'fnDrawCallback': function(oSettings) {
            $('.dataTables_empty').hide();
            if (oSettings.fnRecordsDisplay() === 0) {
                $('.dataTables_empty_text').show();
            }else {
                $('.dataTables_empty_text').hide();
            }
        },
        "aoColumns": [
            {"bSortable": false, "mRender": checkbox}, 
            null, // company
            null, // name  
            null, // email
            null, // phone
            null, // price_group
            null, // customer_group
            null, // gstn_no
            {"mRender": currencyFormat}, // deposit_amount
            null, // award_points
            null, // cf1 (Card No.)
            null, // cf2 (Room No.)
            {"mRender": currencyFormat}, // opening_deposit_balance
            {"mRender": currencyFormat}, // closing_deposit_balance
            {"mRender": currencyFormat}, // GiftCard
            {"bSortable": false} // Actions
        ]
    }).dtFilter([
            {
            column_number: 1,
            filter_default_label: "[<?=lang('company');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 2,
            filter_default_label: "[<?=lang('name');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 3,
            filter_default_label: "[<?=lang('email_address');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 4,
            filter_default_label: "[<?=lang('phone');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 5,
            filter_default_label: "[<?=lang('price_group');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 6,
            filter_default_label: "[<?=lang('customer_group');?>]",
            filter_type: "text",
            data: []
        },
        //            {column_number: 7, filter_default_label: "[<?=lang('vat_no');?>]", filter_type: "text", data: []},
        // {column_number: 7, filter_default_label: "[<?=lang('GST No');?>]", filter_type: "text", data: []},
        {
            column_number: 8,
            filter_default_label: "[<?=lang('deposit');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 9,
            filter_default_label: "[<?=lang('award_points');?>]",
            filter_type: "text",
            data: []
        },
        {
            column_number: 14,
            filter_default_label: "[<?=lang('gift_card');?>]",
            filter_type: "text",
            data: []
        },
    ], "footer");
    $('#myModal').on('hidden.bs.modal', function() {
        cTable.fnDraw(false);
    });

});
</script>
<?php if ($Owner || !empty($GP['bulk_actions'])) {
    echo form_open('customers/customer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("Actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal"
                                id="add">
                                <i class="fa fa-plus-circle"></i> <?= lang("add_customer"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/import_csv'); ?>" data-toggle="modal"
                                data-target="#myModal">
                                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/getBulkDeposit'); ?>" data-toggle="modal"
                                data-target="#myModal">
                                <i class="fa fa-plus-circle"></i> <?= lang("Bulk_Deposit"); ?>
                            </a>
                        </li>
                        <?php if ($Owner) { ?>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_customers") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('list_results'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $show_tax_field = false;
                $tax_label = '';
                    if (!empty($settings->country_name) && !empty($country)) {
                            foreach ($country as $c) {
                            if (strcasecmp($c->name, $settings->country_name) === 0 && !empty($c->TaxNumberLabelText)) {
                                $tax_label = $c->TaxNumberLabelText;
                                $show_tax_field = true;
                                break;
                            }
                        }
                    }
            ?>


                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0"
                        class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("company"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th><?= lang("email_address"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("price_group"); ?></th>
                                <th><?= lang("customer_group"); ?></th>
                                <!--                            <th><?= lang("vat_no"); ?></th>-->
                                <?php if ($show_tax_field): ?>
                                <th><?= $tax_label; ?></th>
                                <?php endif; ?>
                                <th><?= lang("deposit"); ?></th>
                                <th><?= lang("award_points"); ?></th>

                                <th><?= lang("Card No."); ?></th>
                                <th><?= lang("Room No."); ?></th>
                                <th><?= lang("Opening Balance"); ?></th>
                                <th><?= lang("Closing Balance"); ?></th>
                                <th><?= lang("gift_card"); ?></th>
                                <th style="min-width:135px !important;"><?= lang("Action"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody>
                            <?php
                        // Count custom fields dynamically
                        $cf_count = 0;
                        if (!empty($cfields['customer'])) {
                            for ($i = 1; $i <= 6; $i++) {
                                if (!empty($cfields['customer']->{"cf{$i}"})) {
                                    $cf_count++;
                                }
                            }
                        }
                        $colspan = 15 + $cf_count;
                    ?>
                        <tbody>
                            <?php
                        // Count custom fields dynamically
                        $cf_count = 0;
                        if (!empty($cfields['customer'])) {
                            for ($i = 1; $i <= 6; $i++) {
                                if (!empty($cfields['customer']->{"cf{$i}"})) {
                                    $cf_count++;
                                }
                            }
                        }
                        $colspan = 15 + $cf_count;
                    ?>
                            <tr>
                                <td colspan="16" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                <td colspan="16" class="dataTables_empty_text"><?= lang('No data available') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:135px !important;" class="text-center"><?= lang("Actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || !empty($GP['bulk_actions'])) { ?>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<?php } ?>
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>


<?php 
            if(!empty($_SESSION['Print_Deposite_Receipt']['status']) && $_SESSION['Print_Deposite_Receipt']['status']=='1'){ 
              $deposit_data =  $_SESSION['Print_Deposite_Receipt']['last_deposit'];
              $customerData = $_SESSION['Print_Deposite_Receipt']['customer_Details'];
              $opningBalance = $_SESSION['Print_Deposite_Receipt']['openingBalance'];
              unset($_SESSION['Print_Deposite_Receipt']);
        ?>
<div class="page-break" id="deposit_print_bill" style="display:none">
    <style>
    .page-break {
        width: 480px
    }

    @media print {
        .page-break {
            display: block;
            page-break-before: always;
        }

        .page-break {
            width: 480px
        }
    }

    /*#orderTable_<?php // $key ?>, th, td { border-collapse:collapse; border-bottom: 1px solid #CCC; }*/
    .no-border {
        border: 0;
    }

    #depositTable>tbody>tr>td,
    #depositTable>tbody>tr>th {
        border: 1px solid;
        padding: 5px 2px
    }

    .bold {
        font-weight: bold;
    }
    </style>
    <div class="text-center" style="text-align: center;">
        <strong
            style="text-transform:uppercase; margin-bottom: 0px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></strong><br />
        <span> Date : <?= $deposit_data['date'] ?></span><br />
        <span> Name: <?= $customerData->name?></span><br />
        <?php if (!$this->sma->shouldHideCustomerPhone($customerData)) : ?>
        <span> Tel No.: <?= $customerData->phone?></span><br />
        <?php endif; ?>
        <span> Email: <?= $customerData->email?></span><br />
        <span> Card No: <?= $customerData->cf1?></span><br />
        <span> Room No: <?= $customerData->cf2?></span><br />
    </div>

    <table id="depositTable" style="width: 100%;border-collapse: collapse;text-align: left;">
        <tbody>
            <tr>
                <td>Opening Balance</td>
                <td><?= $this->sma->formatMoney(($customerData->deposit_amount?$customerData->deposit_amount - $deposit_data['amount']: $deposit_data['amount'])) ?>
                </td>
                <!-- <td><?= $this->sma->formatMoney($opningBalance) ?></td> -->
            </tr>
            <tr>
                <td> Recharge Amount </td>
                <td> <?= $this->sma->formatMoney($deposit_data['amount'] - $deposit_data['super_cash']) ?> </td>
            </tr>
            <tr>
                <td> Super Cash Recived</td>
                <td> <?= $this->sma->formatMoney($deposit_data['super_cash']) ?> </td>
            </tr>
            <tr>
                <td> Total Deposit Amount</td>
                <td> <?= $this->sma->formatMoney($deposit_data['amount']) ?> </td>
            </tr>
            <tr>
                <td> Closing Balance </td>
                <td> <?= $this->sma->formatMoney($customerData->deposit_amount ) ?> </td>
            </tr>
            <tr>
                <td> Paid By </td>
                <td> <?= $deposit_data['paid_by'] ?> </td>
            </tr>


        </tbody>
    </table>
    <div style="padding:2px;">
        <table>
            <tr>
                <td>Remark :</td>
                <td> <?= $deposit_data['note'] ?></td>
            </tr>
        </table>

    </div>
</div>
<script>
openWin('deposit_print_bill')
setTimeout(function() {
    openWin('deposit_print_bill');;
}, 100);


function openWin(div) {
    var winPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
    winPrint.document.write($('#' + div).html());
    winPrint.document.close();
    winPrint.focus();
    winPrint.print();
    setTimeout(function() {
        winPrint.close();
    }, 100)
}
</script>

<?php  } ?>