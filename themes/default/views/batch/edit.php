<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 

<style>
    #myModal{
        display: block; overflow: scroll;
    }
    
    .modal.fade {
        -webkit-transition: opacity .3s linear, top .3s ease-out;
        -moz-transition: opacity .3s linear, top .3s ease-out;
        -ms-transition: opacity .3s linear, top .3s ease-out;
        -o-transition: opacity .3s linear, top .3s ease-out;
        transition: opacity .3s linear, top .3s ease-out;
        top: -3%;
    }

    .modal-header .btnGrp{
        position: absolute;
        top:18px;
        right: 10px;
    } 
    .form-group {
        margin-bottom: 10px;
    }
</style>
<!--<div class="container" >-->
<div class="mymodal" id="modal_batch_edit" role="dailog">
    <div class="modal-dialog modal-ms add_quick">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"> <?php echo lang('Edit Products Batch'); ?></h4>
            </div>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
            echo form_open_multipart("products/edit_batch", $attrib);
            ?>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>    
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label" for="products"><?php echo $this->lang->line("Products"); ?>*</label>                                                         
                            <input type="text" disabled="disabled" class="form-control" name="products_name" value="<?= $products->name ?>" />
                            <input type="hidden" name="products" value="<?= $batchDetails->product_id ?>" id="products" />                        
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="option_id"><?php echo $this->lang->line("Variants"); ?>* </label> 
                        
                            <input type="text" disabled="disabled" class="form-control" name="option_name" value="<?= $variant->name ? $variant->name : '--NA--' ?>" />
                        
                            <input type="hidden" name="option_id" value="<?= $batchDetails->option_id ?>" id="option_id" />   
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label" for="batch"><?php echo $this->lang->line("Batch Number"); ?>*</label>
                            <input type="text" readonly="readonly" class="form-control" required="required" name="batch_no" value="<?= $batchDetails->batch_no ?>" id="batch" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="expiry_date"><?php echo $this->lang->line("expiry_date"); ?></label>
                            <input type="date" class="form-control" name="expiry_date" value="<?= $batchDetails->expiry_date ?>" id="expiry_date" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group person">
                            <label class="control-label" for="cost"><?= lang("Cost", "Cost"); ?>*</label>
                            <?php echo form_input('cost', ($batchDetails->cost > 0 ?$batchDetails->cost:''), 'class="form-control tip" required="required"  id="cost" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" type="text" type="number" id="cost" ondrop="return false;" onpaste="return false;"'); ?>
                            <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group person">
                            <label class="control-label" for="price"><?= lang("Price", "Price"); ?>*</label>
                            <?php echo form_input('price', ($batchDetails->price > 0 ?$batchDetails->price:''), 'class="form-control tip" required="required"  id="price" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);"  type="number" id="price" ondrop="return false;" onpaste="return false;"'); ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group person">
                            <label class="control-label" for="mrp"><?= lang("MRP", "MRP"); ?>*</label>
                            <?php echo form_input('mrp', ($batchDetails->mrp > 0 ?$batchDetails->mrp:''), 'class="form-control tip" required="required"  id="mrp" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);"  type="number" id="mrp" ondrop="return false;" onpaste="return false;"'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="edit_id" id="edit_id" value="<?= $batchDetails->id ?>" />
                <?php echo form_submit('submit_batch', lang('Update'), 'class="btn btn-primary" id="submit_batch"'); ?>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<!--</div>-->
<?= $modal_js ?>
