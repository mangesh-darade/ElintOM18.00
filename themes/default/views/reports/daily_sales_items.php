<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<button type="button" class="btn btn-sm btn-default no-print pull-right" style="margin-right:10px;margin-bottom:10px;" onClick="printdivc();">
    <i class="fa fa-print"></i><?= lang('print'); ?>
</button>

<div class="table-responsive" id="dailysalesitemtable">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th><?= lang('product_name'); ?></th>
                <th><?= lang('product_code'); ?></th>
                <th><?= lang('category'); ?></th>
                <th><?= lang('price'); ?></th>
                <th><?= lang('quantity'); ?></th>
                <th><?= lang('unit'); ?></th>
                <th><?= lang('tax_rate'); ?></th>
                <th><?= lang('tax_amount'); ?></th>
                <th><?= lang('discount'); ?></th>
                <th><?= lang('total'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sale_data)): ?>
                <?php $i = 0; foreach ($sale_data as $key => $item): ?>
                    <tr>
                        <td><?= ++$i ?></td>
                        <td><?= $item->product_name ?></td>
                        <td><?= $item->product_code ?></td>
                        <td><?= $item->category_name ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($item->net_unit_price) ?></td>
                        <td style="text-align:center;"><?= $this->sma->formatQuantity($item->qty) ?></td>
                        <td><?= $item->unit ?></td>
                        <td style="text-align:center;"><?= $item->tax_rate ? $this->sma->formatQuantity($item->tax_rate) : 0; ?>%</td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($item->tax) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($item->discount) ?></td>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($item->total) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="text-center"><?= lang('no_data_available'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    function printdivc() {
        var printContents = document.getElementById('dailysalesitemtable').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>