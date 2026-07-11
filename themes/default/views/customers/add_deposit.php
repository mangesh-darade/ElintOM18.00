<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
 <?php if($company->id != '1'){ ?>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_deposit') . " (" . $company->name . ")"; ?>
                <br/> Balance : <?= $this->sma->formatMoney( $company->deposit_amount) ?>
            </h4>
 <?php } ?>
        </div>

        <?php if($company->id == '1'){
            echo '<h3 class="text-center"><strong> Walk In Customer Deposit Not Allow. </strong</h3>';
         } else{ ?>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("customers/add_deposit/" . $company->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
 
            <div class="row">
                <div class="col-sm-12">
                    <?php if ($Owner || $Admin) { ?>
                    <div class="form-group">
                        <?php echo lang('date', 'date'); ?>
                        <div class="controls">
                            <?php echo form_input('date', set_value('date', date($dateFormats['php_ldate'])), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="form-group">
                        <input type="checkbox" name="services-check" id="servicescheck"  > <label for="servicescheck"> Add Service </label>    
                    </div>   
                    
                    <div class="form-group" id="serviceblock" style="display: none;">
                        <?php echo lang('Service', 'service_amount'); ?>
                        <div class="controls">
                            <select class="form-control" name="service_amount" id="service_amount">
                                <option value="">Select Service Amount </option>
                                <?php for($i=1; $i <= 1; $i++){ 
                                    $amt = 500 * $i;
                                    ?>
                                <option value="<?= $amt ?>"> <?= $amt ?> </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group"id="cashblock" >
                        <?php echo lang('amount', 'amount'); ?>
                        <div class="controls">
                            <?php echo form_input('cash', set_value('amount'), 'class="form-control" placeholder="Amount" id="amount1" '); ?>
                        </div>
                    </div>
                    
                    <div class="form-group"id="super_pricelock" >
                        <?php echo lang('Super Cash', 'Super Cash'); ?>
                        <div class="controls">
                            <?php echo form_input('super_price', set_value('super_price'),  'class="form-control" readonly="true" placeholder="Super Cash" id="super_price" '); ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <?php echo lang('Total', 'Total'); ?>
                        <div class="controls">
                            <?php echo form_input('amount', set_value('amount'), 'class="form-control" id="amount" readonly="true" placeholder="Total Amount"   required="required"'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo lang('paid_by', 'paid_by'); ?>
                        <div class="controls">
                             <select name="paid_by" id="paid_by_1" class="form-control paid_by">
<?= $this->sma->paid_opts(); ?>
                                                    </select>
                            <?php //echo form_input('paid_by', set_value('paid_by'), 'class="form-control" id="paid_by"'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo lang('note', 'note'); ?>
                        <div class="controls">
                            <?php echo form_textarea('note', set_value('note'), 'class="form-control" id="note"'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_deposit', lang('add_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
 <?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>

<script>
  
    
       $('#servicescheck').on('ifChecked', function () {
          $('#serviceblock').show();        
          $('#cashblock').hide();
          $('#note').html('Services');
        });
        $('#servicescheck').on('ifUnchecked', function () {
             $('#serviceblock').hide();        
             $('#cashblock').show();
              $('#note').html('');
        });
        
        $('#service_amount').change(function(){
              var amt = $('#service_amount').val();
              $('#amount').val(amt);
             $('#amount1').val(0);
        });
        
        $('#amount1').change(function () {
        let amt = $(this).val();
        let cash_limit = '<?= $Settings->deposit_cash_limit ?>';
        let calper = 0;
        let total =  parseFloat(amt);
        if(parseFloat(amt) >= parseFloat(cash_limit)){
            let offer = '<?= $Settings->deposit_discount ?>';            
            if (offer.indexOf("%") !== -1) {
                let per = offer.split('%');
                calper = parseFloat(amt) * parseFloat(per[0]) / parseFloat(100);
                total = parseFloat(amt) + parseFloat(calper);
            } else{
                calper = parseFloat(offer);
                total = parseFloat(amt) + parseFloat(calper);
            }           
        }
        $('#super_price').val(calper);
        $('#amount').val(total);
    });

    $('#service_amount').change(function () {
    if ('<?= $Settings->deposit_service_offer_manage ?>' == '1') {
        let amt = $(this).val();
        let offer = '<?= $Settings->deposit_service_offer ?>'.trim();
        let calper = "";
        let total = parseFloat(amt) || 0;
        if (offer.indexOf("%") !== -1) {
            let per = offer.split('%');
            let percentValue = parseFloat(per[0]);
            if (!isNaN(percentValue)) {
                calper = (amt * percentValue) / 100;
                total = amt + calper;
            }
        } else {
            let offerValue = parseFloat(offer);
            if (!isNaN(offerValue)) {
                calper = offerValue;
                total = amt + calper;
            }
        }
        $('#super_price').val(calper); 
        $('#amount').val(total);
    }
});
</script>  