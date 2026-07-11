<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function () {
$('#POSData').dataTable();
});
</script>
<div class="modal-dialog modal-lg" style="width:95%;">
    <div class="modal-content ">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <!--<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>-->
          
            <h4 class="modal-title"
                id="myModalLabel"><?= lang('recent_pos_list'); ?></h4>
        </div>
        <div class="modal-body">
		<div class="table-responsive">
                     <table id="POSData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                           
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("biller"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("sale_status"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th><?= lang("Delivery"); ?></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                       <tbody>
							<?php
								
								if(!empty($POSDATA)){
								foreach($POSDATA as $ResValue){
							?>
							<tr>
							   
								<td><?= $ResValue["date"]; ?></td>
								<td><?= $ResValue["reference_no"]; ?></td>
								<td><?= $ResValue["biller"]; ?></td>
								<td><?= $ResValue["customer"]; ?></td>
								<td><?= $ResValue["grand_total"]; ?></td>
								<td><?= $ResValue["paid"]; ?></td>
								<td><?= $ResValue["balance"]; ?></td>
								<td><?= $ResValue["sale_status"]; ?></td>
								<td><?= $ResValue["payment_status"]; ?></td>
								<td><?= $ResValue["delivery_status"]; ?></td>
								<td style="width:80px; text-align:center;"><a href="<?php echo base_url('sales/view/'.$ResValue["id"]); ?>" ><?php echo lang('pos_details'); ?></a></td>
							</tr>
								<?php } }else{ ?>
							<tr>
								<td colspan="12" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
								<?php } ?>
							</tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
        </div>
    </div>

</div>



