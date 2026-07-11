<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 

<style>
    /* body{overflow: hidden !important;}*/
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

    #product_batches_list tr td:last-child{
        display : flex;
    }

    .select2-drop.batch-products-dropdown .select2-results {
        max-height: 150px !important;
        overflow: auto !important;
    }
</style>
<!--<div class="container" >-->				
<div class="modal-dialog modal-ms add_quick">
        <div class="modal-content" style="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"> <?php echo lang('Add Products Batches'); ?></h4>
            </div>
            
            <div class="modal-body"> 
                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
                echo form_open_multipart("products/add_batch", $attrib);
                ?>
                <div class="row">                    
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label" for="products"><?php echo $this->lang->line("products"); ?>* </label>
                            <?php
                            $pgs[''] = '--Select Products--';
                            foreach ($products as $productval) {
                                $optval = $productval->id;
                                $pgs[$optval] = $productval->name .' - '. $productval->code;
                            }
                            echo form_dropdown('products', $pgs, $product_id, 'id="products" data-placeholder="' . lang("Products") . '" required="required" class="form-control input-tip select" style="width:100%;height:30px; "  ');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="products"><?php echo $this->lang->line("Variants"); ?>* </label>                             
                            <select class="form-control input-tip select" name="option_id" id="option_id" style="width:100%;height:30px; " >
                                <option value="">--Select Variants--</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label" for="batch"><?php echo $this->lang->line("Batch Number"); ?>*</label>
                            <input type="text" class="form-control" name="batch_no" id="batch_no" required="required" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="expiry_date"><?php echo $this->lang->line("expiry_date"); ?></label>
                            <input type="date" class="form-control" name="expiry_date" value="" id="expiry_date" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group person">
                            <label class="control-label" for="cost"><?= lang("Cost", "Cost"); ?>*</label>
<?php echo form_input('cost', '', 'required="required" class="form-control tip" id="cost" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);" type="text" type="number" id="cost" ondrop="return false;" onpaste="return false;"'); ?>
                            <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter alphabets only</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group person">
                            <label class="control-label" for="price"><?= lang("Price", "Price"); ?>*</label>
<?php echo form_input('price', '', ' required="required" class="form-control tip" id="price" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);"  type="number" id="price" ondrop="return false;" onpaste="return false;"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group person">
                            <label class="control-label" for="mrp"><?= lang("MRP", "MRP"); ?>*</label>
<?php echo form_input('mrp', '', ' required="required" class="form-control tip" id="mrp" data-bv-notempty="true" onkeypress="return onlyAlphabets1(event,this);"  type="number" id="mrp" ondrop="return false;" onpaste="return false;"'); ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <input type="hidden" name="edit_id" id="edit_id" />
                    <?php echo form_submit('submit_batch', lang('Add'), 'class="btn btn-success" id="submit_batch"'); ?>
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-hidden="true">Close</button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
            <div class="modal-footer">                
                <div id="product_batches_list"></div>                
            </div>
            
        </div>
</div>
<!--</div>-->
<?= $modal_js ?>
<script>

function initBatchProductsSelect2() {
    var $products = $('#myModal #products');
    if (!$products.length) {
        return;
    }
    if ($products.data('select2')) {
        $products.select2('destroy');
    }
    $products.select2({
        minimumResultsForSearch: 7,
        width: '100%',
        dropdownCssClass: 'batch-products-dropdown'
    });
}

$(document).ready(function(){
    $('#modal-loading').hide();
    initBatchProductsSelect2();
    setTimeout(initBatchProductsSelect2, 150);

    if('<?= isset($product_id) ? $product_id : '' ?>') {
        get_product_batches('<?= isset($product_id) ? $product_id : '' ?>', '<?= isset($option_id) ? $option_id : '' ?>');
    }
    
    $(document).on('change', '#products', function(){
        
        var product_id = $(this).val();
        get_product_variants(product_id);
        
        get_product_batches(product_id, '');
    });
    
    $('#option_id').change(function(){
        
        var product_id = $('#products').val();
        var option_id = $('#option_id').val();
        
        get_product_batches(product_id, option_id);
    }); 
    
});

function edit_batch(id){

    var Posturl = '<?= base_url('products/ajaxBatchesRequest')?>';
    if(id!='') {
        $.ajax({
            type: "POST",
            url: Posturl,
            data:'id='+id+'&ajaxAction=getBatchData',
            beforeSend: function(){
               // $("#product_batches_list").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
            },
            success: function(data){
                
                var objBatch = JSON.parse(data);
                //console.log(objBatch);
                
                $('#form_action').val('edit');
                $('#edit_id').val(objBatch.id);
                $('#products').val(objBatch.product_id);
                $('#products').attr('readonly', 'readonly');
                $('#option_id').val(objBatch.option_id);
                $('#option_id').attr('readonly', 'readonly');
                $('#batch_no').val(objBatch.batch_no);
                $('#batch_no').attr('readonly', 'readonly');
                $('#cost').val(objBatch.cost);
                $('#price').val(objBatch.price);
                $('#mrp').val(objBatch.mrp);
                $('#expiry_date').val(objBatch.expiry_date);
                $('#submit_batch').val('Update');
                $('#myModalLabel').html('Edit Product Batch');
                $('#add-customer-form').attr('action','<?= base_url('products/edit_batch')?>');
            }
	});
    } else {
        return false;
    }
}

function get_product_batches(product_id, option_id){
    
    var Posturl = '<?= base_url('products/ajaxBatchesRequest')?>';
    if(product_id!='') {
        $.ajax({
            type: "POST",
            url: Posturl,
            data:'product_id='+product_id+'&option_id='+option_id+'&ajaxAction=getBatchesList',
            beforeSend: function(){
                $("#product_batches_list").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
            },
            success: function(data){			 
                $("#product_batches_list").html(data);			 
            }
	});
    } else {
        return false;
    }
}

function get_product_variants(product_id){
    
    var Posturl = '<?= base_url('products/ajaxBatchesRequest')?>';
    if(product_id!='') {
        $.ajax({
            type: "POST",
            url: Posturl,
            data:'product_id='+product_id+'&ajaxAction=getVariantsList',
            beforeSend: function(){
                $("#option_id").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
            },
            success: function(data){			 
                $("#option_id").html(data);			 
            }
	});
    } else {
        return false;
    }
}

</script>
