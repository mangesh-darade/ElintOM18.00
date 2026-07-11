<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"> -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Bulk_Deposit'); ?></h4>
        </div>
        <?php
		$OtherField = '';
		$CSVFileName = '';
		if($this->Settings->pos_type=='restaurant'){
			// $OtherField = ', '. lang("UP_Price");
			$CSVFileName = '_restaurant';
		}
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/getBulkDeposit", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="well well-small">

                           <a href="<?php echo base_url(); ?>assets/mdata/<?php echo $Customer_assets; ?>/csv/sample_customers_bulk_deposit.xls"id="excel" class="btn btn-primary pull-right" data-action="export_deposit">
                           <i class="fa fa-download"></i> <?= lang('download_sample_file') ?>
                            </a>
                           <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2") ?> <span
                           class="text-info">(<?= lang("Customer Name").','.lang("Phone_NO") . ', ' . lang("Customer_Group"). ', ' . lang(" Member_Card_No"). ','. lang("Flat_NO").','. lang("Amount").','. lang("Supercash").','. lang("Payment_Mode").','. lang("Deposit_Type") ?>
                           )</span> <?= lang("csv3"); ?></br>
                           
                           <b>
                            <p>Please Note:</p>
                           <li><?= lang("Only Service Deposit should be done using this Method.") ?></li>
                           <li><?= lang("Service Deposit amount and super cash amount should be specified in excel sheet in provided separate columns.") ?></li>
                           <li><?= lang("Use Payment mode as following:- Cash, UPI, QR,Code, CC, DC, Paytm, Google pay.") ?></li>
                        
                        
                        </b>

                       </div>
                       <div class="form-group">
                       <?= lang("upload_file","deposit_file") ?>
                            <input id="deposit_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="deposit_file" required="required"
                                   data-show-upload="false" data-show-preview="false"  accept=".xls" class="form-control file">
                        <!-- <label for="deposit_file"><?= lang("upload_file"); ?></label>
                        <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false"
                        data-show-preview="false" accept=".xls" required="required"/> -->
                    </div>

                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('Bulk_Deposit', lang('Submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>