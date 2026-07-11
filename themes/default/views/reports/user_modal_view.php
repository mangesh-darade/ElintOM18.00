<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
?>
<style>
    table td p{    width: 300px;
     overflow-wrap: break-word;}
</style> 
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
           
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?php echo $Type = $UserLogView[0]->action_comment; ?> 
                    </p>
                    </div>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="table-responsive">
			
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    
					<?php
					$Explode = explode(' ',$Type);
					if($Explode[1]=='sales'){ ?>
					<thead>
                    <tr>
                        <th><?= lang("Name"); ?></th>
                        <th><?= lang("Code"); ?></th>
                        <th><?= lang("quantity"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						foreach($LogData->sale_items as $val_log_data){ ?>
						<tr>
							<th><?= $val_log_data->product_name; ?></th>
							<th><?= $val_log_data->product_code; ?></th>
							<th><?= $val_log_data->quantity; ?></th>
						</tr>
						<?php  } 
					}
					if($Explode[1]=='purchases'){
						?>
						<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
							<th><?= lang("quantity"); ?></th>
						</tr>
						</thead>
						<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						foreach($LogData->purchase_items as $val_log_data){ ?>
						<tr>
							<th><?= $val_log_data->product_name; ?></th>
							<th><?= $val_log_data->product_code; ?></th>
							<th><?= $val_log_data->quantity; ?></th>
						</tr>
						<?php  } 
					}
					if($Explode[1]=='transfers' || $Explode[1]=='quotes'){
						?>
						<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
							<th><?= lang("quantity"); ?></th>
						</tr>
						</thead>
						<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						foreach($LogData->products as $val_log_data){ ?>
						<tr>
							<th><?= $val_log_data->product_name; ?></th>
							<th><?= $val_log_data->product_code; ?></th>
							<th><?= $val_log_data->quantity; ?></th>
						</tr>
						<?php  } 
					}
					if($Explode[1]=='products'){
						?>
						<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
						<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						//print_r($LogData);
						if($Type!='Delete products'){
						?>
						<tr>
							<th><?= $LogData->product_data->name; ?></th>
							<th><?= $LogData->product_data->code; ?></th>
						</tr>
						<?php
						}else{ ?>
							<tr>
							<th><?= $LogData[0]->name; ?></th>
							<th><?= $LogData[0]->code; ?></th>
						</tr>
						<?php }
					}
					if($Explode[1]=='currencies' || $Explode[1]=='expense_categories'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						//print_r($LogData);
						//foreach($LogData as $val_log_data){ ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							<th><?= $LogData->code; ?></th>
						</tr>
						<?php //}
					}
					if($Explode[1]=='customer_groups'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Percent"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
					 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							<th><?= $LogData->percent; ?></th>
						</tr>
						<?php
					}
					if($Explode[1]=='categories'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						//print_r($LogData);
						if($Type=='Add categories'){
					 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							<th><?= $LogData->code; ?></th>
						</tr>
						<?php
						}
						if($Type=='Edit categories'){
					 ?>
						<tr>
							<th><?= $LogData->categories->name; ?></th>
							<th><?= $LogData->categories->code; ?></th>
						</tr>
						<?php
						}
						if($Type=='Delete categories'){
					 ?>
						<tr>
							<th><?= $LogData[0]->name; ?></th>
							<th><?= $LogData[0]->code; ?></th>
						</tr>
						<?php
						}
					}
					if($Explode[1]=='units'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						if($Type=='Delete units'){
					 ?>
						<tr>
							<th><?= $LogData[0]->name; ?></th>
							<th><?= $LogData[0]->code; ?></th>
						</tr>
						<?php
						}else{
					 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							<th><?= $LogData->code; ?></th>
						</tr>
						<?php
						}
					}
					if($Explode[1]=='brands'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						if($Type=='Delete brands'){
					 ?>
						<tr>
							<th><?= $LogData[0]->name; ?></th>
							<th><?= $LogData[0]->code; ?></th>
						</tr>
						<?php
						}else{
					 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							<th><?= $LogData->code; ?></th>
						</tr>
						<?php
						}
					}
					if($Explode[1]=='warehouses'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						if($Type=='Delete warehouses'){
					 ?>
						<tr>
							<th><?= $LogData[0]->name; ?></th>
							<th><?= $LogData[0]->code; ?></th>
						</tr>
						<?php
						}else{
					 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							<th><?= $LogData->code; ?></th>
						</tr>
						<?php
						}
					}
					if($Explode[1]=='variants'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						if($Type=='Delete variants'){
					 ?>
						<tr>
							<th><?= $LogData[0]->name; ?></th>
							
						</tr>
						<?php
						}else{
					 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
							
						</tr>
						<?php
						}
					}
					if($Explode[1]=='price_groups'){ ?>
					<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
						</tr>
						</thead>
					<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						 ?>
						<tr>
							<th><?= $LogData->name; ?></th>
						</tr>
						<?php
					}
					if($Explode[1]=='adjustments'){
						
						?>
						<thead>
						<tr>
							<th><?= lang("Name"); ?></th>
							<th><?= lang("Code"); ?></th>
						</tr>
						</thead>
						<tbody>
					<?php
						$LogData = json_decode($UserLogView[0]->action_affected_data);
						if($Type=='Delete adjustments'){
							foreach($LogData->products as $val_log_data){
						?>
						<tr>
							<th><?= $val_log_data->product_name; ?></th>
							<th><?= $val_log_data->product_code; ?></th>
						</tr>
							<?php } }else{
								
							foreach($LogData->products as $key => $val_log_data){
								$Res = $this->reports_model->getProductById($val_log_data->product_id);
								//print_r($Res);
								//foreach($val_log_data as $value){
									//echo $value;
							?>
							<tr>
								<th><?= $Res->name; ?></th>
								<th><?= $Res->code; ?></th>
							</tr>
							<?php } //}
						}
						
						
					 }
					
					?>
					
                    </tbody>
                    
                </table>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
