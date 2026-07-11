<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if (!empty($customer)) { ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="printCustomerViewModal(this);">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
            	
                <table class="table table-striped table-bordered" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td><strong><?= lang("company"); ?></strong></td>
                        <td><?= $customer->company; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("name"); ?></strong></td>
                        <td><?= $customer->name; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("customer_group"); ?></strong></td>
                        <td><?= $customer->customer_group_name; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("vat_no"); ?></strong></td>
                        <td><?= $customer->vat_no; ?></strong></td>
                    </tr>
                    <?php
                        $tax_label = lang("GSTIN");
                        if (!empty($customer->country)) {
                            $c_master = $this->db->get_where('sma_country_master', ['name' => $customer->country])->row();
                            if ($c_master && !empty($c_master->TaxNumberLabelText)) {
                                $tax_label = $c_master->TaxNumberLabelText;
                            }
                        }
                    ?>
                    <tr>
                        <td><strong><?= $tax_label; ?></strong></td>
                        <td><?= ($customer->gstn_no == '') ? '---' : $customer->gstn_no; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("deposit"); ?></strong></td>
                        <td><?= $this->sma->formatMoney($customer->deposit_amount); ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("award_points"); ?></strong></td>
                        <td><?= $customer->award_points; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("email"); ?></strong></td>
                        <td><?= $customer->email; ?></strong></td>
                    </tr>
                    <?php if (!$this->sma->shouldHideCustomerPhone($customer)) : ?>
                    <tr>
                        <td><strong><?= lang("phone"); ?></strong></td>
                        <td><?= $customer->phone; ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong><?= lang("address"); ?></strong></td>
                        <td><?= $customer->address; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("city"); ?></strong></td>
                        <td><?= $customer->city; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("state"); ?></strong></td>
                        <td><?= $customer->state; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("postal_code"); ?></strong></td>
                        <td><?= $customer->postal_code; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("country"); ?></strong></td>
                        <td><?= $customer->country; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'ccf1') : lang('ccf1', 'ccf1')) ?>  </strong></td>
                        <td><?= $customer->cf1; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'ccf2') : lang('ccf2', 'ccf2')) ?></strong></td>
                        <td><?= $customer->cf2; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'ccf3') : lang('ccf3', 'ccf3')) ?></strong></td>
                        <td><?= $customer->cf3; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'ccf4') : lang('ccf4', 'ccf4')) ?></strong></td>
                        <td><?= $customer->cf4; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'ccf5') : lang('ccf5', 'ccf5')) ?></strong></td>
                        <td><?= $customer->cf5; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'ccf6') : lang('ccf6', 'ccf6')) ?></strong></td>
                        <td><?= $customer->cf6; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("DOB"); ?></strong></td>
                        <td><?= ($customer->dob) ? $this->sma->hrsd($customer->dob) : ''; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Anniversary Date"); ?></strong></td>
                        <td><?= ($customer->anniversary) ? $this->sma->hrsd($customer->anniversary):''; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Fathers Birthday"); ?></strong></td>
                        <td><?= ($customer->dob_father) ? $this->sma->hrsd($customer->dob_father):''; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Mothers Birthday"); ?></strong></td>
                        <td><?= ($customer->dob_mother) ? $this->sma->hrsd($customer->dob_mother) : ''; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Older Child's Birthday"); ?></strong></td>
                        <td><?= ($customer->dob_child1) ? $this->sma->hrsd($customer->dob_child1): ''; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Younger Child's Birthday"); ?></strong></td>
                        <td><?= ($customer->dob_child2) ? $this->sma->hrsd($customer->dob_child2) : ''; ?></strong></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                <?php if ($Owner || $Admin || $GP['reports-customers']) { ?>
                    <a href="<?=site_url('reports/customer_report/'.$customer->id);?>"  class="btn btn-primary"><?= lang('customers_report'); ?></a>
                <?php } ?>
                <?php if ($Owner || $Admin || $GP['customers-edit']) { ?>
                    <a href="<?=site_url('customers/edit/'.$customer->id);?>" data-toggle="modal" data-target="#myModal2" class="btn btn-primary"><?= lang('edit_customer'); ?></a>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<?php } ?>
<script type="text/javascript">
function printCustomerViewModal(btn) {
    var $dialog = $(btn).closest('.modal-dialog');
    if (!$dialog.length) {
        window.print();
        return;
    }
    var title = $.trim($dialog.find('.modal-title').text());
    var bodyHtml = $dialog.find('.modal-body .table-responsive').html();
    if (!bodyHtml) {
        window.print();
        return;
    }
    var printWindow = window.open('', 'customer_view_print', 'height=700,width=900');
    if (!printWindow) {
        window.print();
        return;
    }
    printWindow.document.write('<html><head><title>' + $('<div>').text(title).html() + '</title>');
    printWindow.document.write('<style>body{font-family:Arial,Helvetica,sans-serif;padding:15px;color:#000;}h3{margin:0 0 10px 0;font-size:18px;}table{width:100%;border-collapse:collapse;}table td,table th{border:1px solid #ddd;padding:8px;vertical-align:top;}.table-striped tbody tr:nth-child(odd){background:#f9f9f9;}@media print{body{padding:0;}}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h3>' + $('<div>').text(title).html() + '</h3>');
    printWindow.document.write('<div class="table-responsive">' + bodyHtml + '</div>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function () {
        printWindow.print();
        printWindow.close();
    }, 150);
}
</script>