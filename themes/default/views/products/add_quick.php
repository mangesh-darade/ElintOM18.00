<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<script type="text/javascript" src="<?= $assets ?>js/purchases.js"></script>

<?php
if (!empty($variants)) {
    foreach ($variants as $variant) {
        $vars[] = addslashes($variant->name);
    }
} else {
    $vars = array();
}
$enable_discount_on_mrp = isset($Settings->discount_on_mrp) ? (int) $Settings->discount_on_mrp : 1;
?>
 
 <style>
<?php if (!$enable_discount_on_mrp) { ?>
.discount-on-mrp-col { display: none !important; }
<?php } ?>



input[name="attr_cost[]"]::-webkit-outer-spin-button,
input[name="attr_cost[]"]::-webkit-inner-spin-button,
input[name="attr_upprice[]"]::-webkit-outer-spin-button,
input[name="attr_upprice[]"]::-webkit-inner-spin-button,
input[name="attr_unit_quantity[]"]::-webkit-outer-spin-button,
input[name="attr_unit_quantity[]"]::-webkit-inner-spin-button,
input[name="attr_unit_weight[]"]::-webkit-inner-spin-button,
input[name="attr_unit_weight[]"]::-webkit-inner-spin-button,
input[name="attr_price[]"]::-webkit-outer-spin-button,
input[name="attr_price[]"]::-webkit-inner-spin-button,
input[name="attr_mrp[]"]::-webkit-outer-spin-button,
input[name="attr_mrp[]"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

#attrTable th,
#attrTable td {
    white-space: nowrap;
    font-size: 15px;
    padding: 4px 6px;
}

#attrTable input.form-control {
    min-width: 60px;
    font-size: 12px;
    padding: 3px 5px;
}

    #myModal{
        display: block;overflow: scroll;
    }
    // body{overflow: hidden !important;}
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
    .modal-dialog.add_quick {
        max-width: 85%;
        width: 85%;
    }
    .custom-attr-width {
    width: 105%;
    overflow-x: hidden;
}

    .well.well-sm {
        width: 107%;
        margin-left: 2px;
    }
    .table th:nth-child(4), 
    .table td:nth-child(4), 
    .table th:nth-child(5), 
    .table td:nth-child(5) {
        font-size: 14px; 
        padding: 4px;
    }
#attrTable th:last-child,
#attrTable td:last-child {
    width: 30px !important;
    text-align: center;
    padding: 0;
}



    
</style>
			
<div class="mymodal" id="modal-1" role="dailog">
    <div class="modal-dialog modal-lg add_quick">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel">Quick <?php echo lang('add_product'); ?></h4>
            </div>
            <?php
            $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-product-form');
            echo form_open("products/quick_add_product", $attrib);
                

            ?>
            
            <div class="modal-body">
            <div id="error-message" class="alert alert-danger alert-dismissible" style="display: none;">
                <button type="button" class="close" onclick="$('#error-message').hide();">&times;</button>
                    <span id="error-text"></span>
            </div>


                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("product_type", "type") ?>
                            <?php
                            $opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'digital' => lang('digital'), 'service' => lang('service'),'raw' => lang('Raw'));
                            echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ''), 'class="form-control" id="type" required="required"');
                            ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("product_name", "name") ?>
                            <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->cf1 : '')), 'class="form-control" id="name" '.($Settings->product_name_auto_generate?'readonly' :'').' required="required"'); ?>
                            <?php if($Settings->product_name_auto_generate){ ?>
                            <span class="text-danger">
                                <b>Note:</b> Product name auto generate. 
                            </span>
                            <?php } ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("product_barcode", "code") ?>
                            <div class="input-group">
                                <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code" required="required" placeholder="Use comma to enter codes per color in order"') ?>
                                <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                    <i class="fa fa-random"></i>
                                </span>
                            </div>
                            <span id="codeerr" class="errmsg text-danger"> </span>
                        </div>
                        <div class="form-group all">
                            <?= lang("Article Number", "Article Number") ?>
                            <?= form_input('article_code', (isset($_POST['article_code']) ? $_POST['article_code'] : ($product ? $product->article_code : '')), 'class="form-control" id="article_no" type="text"') ?>
                            <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                        <div class="form-group all">
                            <?= lang("hsn_code", "hsn_code") ?>
                            <?= form_input('hsn_code', (isset($_POST['hsn_code']) ? $_POST['hsn_code'] : ($product ? $product->hsn_code : '')), 'class="form-control" id="hsn_code"  '); ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("category", "category") ?>
                            <?php
                            $cat[''] = "";
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                            ?>
                            <span id="categoryerr" class="errmsg text-danger"> </span>
                        </div>
                        <div class="form-group all">
                            <?= lang("subcategory", "subcategory") ?>
                            <div class="controls" id="subcat_data"> <?php
                                echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="form-group all">                         
                            <?= lang("brand", "brand") ?>
                            <?php
                            $brnd[''] = "";
                            if (!empty($brands) && is_array($brands)) {
                                foreach ($brands as $brand) {
                                    $brnd[$brand->id] = $brand->name;
                                }
                            }
                            echo form_dropdown('brand', $brnd, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group standard">
                            <?= lang('product_unit', 'unit'); ?>
                            <?php
                            foreach ($base_units as $bu) {
                                $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                            }
                            ?>
                            <?= form_dropdown('unit', $pu, set_value('unit', ($product ? $product->unit : '')), 'class="form-control tip" id="unit" required="required" style="width:100%;"'); ?>
                        </div>
                        <!-- Product Unit Type Selection -->
                        <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" style="margin-top: 10px;">
                                
                                <label class="control-label" 
                                    style="display:inline-block; margin-right:35px; font-weight:600;">
                                    Yield Unit Type :
                                </label>

                                <label class="radio-inline" 
                                    style="margin-left:-34px; font-weight:600;">
                                    <input type="radio" name="unit_type" value="0" checked>
                                    Product Unit
                                </label>

                                <label class="radio-inline" style="font-weight:600; margin-top:3px;">
                                    <input type="radio" name="unit_type" value="1">
                                    Yield Unit
                                </label>

                            </div>
                        </div>
                    </div>
                </div>

                    <div class="col-md-6">
                        <!-- <div class="form-group standard">
                            <?= lang('product_unit', 'unit'); ?>
                            <?php
                            foreach ($base_units as $bu) {
                                $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                            }
                            ?>
                            <?= form_dropdown('unit', $pu, set_value('unit', ($product ? $product->unit : '')), 'class="form-control tip" id="unit" required="required" style="width:100%;"'); ?>
                        </div> -->

                        

                        <div id="attrs"></div>

                        <div class="form-group">
                            <input type="checkbox" class="checkbox" name="attributes"
                                   id="attributes" <?= $this->input->post('attributes') || $product_options ? 'checked="checked"' : ''; ?>><label
                                   for="attributes"
                                   class="padding05"><?= lang('product_has_attributes'); ?></label> <br/><span class="text-info">Ex. Sizes, Colors, Models or Weight</span>
                        </div>
                        <div class="well well-sm custom-attr-width" id="attr-con"
                             style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>">
                            <div class="form-group" id="ui" style="margin-bottom: 0;">
                                <div class="input-group">
                                    <?php echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line("enter_attributes") . '"'); ?>
                                    <div class="input-group-addon" style="padding: 2px 5px;">
                                        <a href="#" id="addAttributes">
                                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                        </a>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="table-responsive">
                                <table id="attrTable" class="table table-bordered table-condensed table-striped"
                                       style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>margin-bottom: 0; margin-top: 10px;">
                                    <thead>
                                        <tr class="active">
                                            <th><?= lang('name') ?></th>
                                            <!--<th><?= lang('warehouse') ?></th>-->
                                            <!--<th><?= lang('quantity') ?></th>-->
                                            <th style="min-width: 80px"><?= lang('Cost') ?></th>
                                            <th style="min-width: 80px"><?= lang('MRP') ?></th>
                                            <th style="min-width: 80px"><?= lang('Price') ?></th>
                                            <th class="discount-on-mrp-col"><?= lang('Dis') ?></th>
                                            <th style="min-width: 40px"><?= 'Unit Qty'?></th>
                                            <th style="min-width: 40px"><?= 'Unit Wht'?></th>
                                            <th><i class="fa fa-times attr-remove-all"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody><?php
                                        if ($this->input->post('attributes')) {
                                            $a = sizeof($_POST['attr_name']);
                                            for ($r = 0; $r <= $a; $r++) {
                                                if (isset($_POST['attr_name'][$r]) && (isset($_POST['attr_warehouse'][$r]) || isset($_POST['attr_quantity'][$r]))) {
                                                    echo '<tr class="attr">'
                                                    . '<td><input type="hidden" class="attr_name" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span></td>'
                                                    . '<!--<td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $_POST['attr_warehouse'][$r] . '"><input type="hidden" class="attr_wh_name" name="attr_wh_name[]" value="' . $_POST['attr_wh_name'][$r] . '"><span>' . $_POST['attr_wh_name'][$r] . '</span></td>-->'
                                                    . '<!--<td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $_POST['attr_quantity'][$r] . '"><span>' . $_POST['attr_quantity'][$r] . '</span></td>-->'
                                                    . '<td class="cost text-right"><input type="hidden" name="attr_cost[]" value="' . $_POST['attr_cost'][$r] . '"><span>' . $_POST['attr_cost'][$r] . '</span></span></td>'
                                                    . '<td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td>'
                                                    . '<td class="price text-right"><input type="hidden" name="attr_unit_quantity[]" value="' . $_POST['attr_unit_quantity'][$r] . '"><span>' . $_POST['attr_unit_quantity'][$r] . '</span></span></td>'
                                                    . '<td class="price text-right"><input type="hidden" name="attr_unit_weight[]" value="' . $_POST['attr_unit_weight'][$r] . '"><span>' . $_POST['attr_unit_weight'][$r] . '</span></span></td>'
                                                    . '<td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                                }
                                            }
                                        } elseif ($product_options) {
                                            foreach ($product_options as $option) {
                                                echo '<tr class="attr">'
                                                . '<td><input type="hidden" class="attr_name" name="attr_name[]" value="' . $option->name . '"><span>' . $option->name . '</span></td>'
                                                . '<!--<td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $option->warehouse_id . '"><input type="hidden" class="attr_wh_name" name="attr_wh_name[]" value="' . $option->wh_name . '"><span>' . $option->wh_name . '</span></td>-->'
                                                . '<!--<td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $this->sma->formatQuantity($option->wh_qty) . '"><span>' . $this->sma->formatQuantity($option->wh_qty) . '</span></td>-->'
                                                . '<td class="cost text-right"><input type="hidden" name="attr_cost[]" value="' . $this->sma->formatMoney($option->cost) . '"><span>' . $this->sma->formatMoney($option->cost) . '</span></span></td>'
                                                . '<td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $this->sma->formatMoney($option->price) . '"><span>' . $this->sma->formatMoney($option->price) . '</span></span></td>'
                                                . '<td class="unit_quantity text-center"><input type="hidden" name="attr_unit_quantity[]" value="' . ($option->wh_unit_qty) . '"><span>' . ($option->wh_unit_qty) . '</span></td>'
                                                . '<td class="unit_weight text-center"><input type="hidden" name="attr_unit_weight[]" value="' . ($option->wh_unit_weight) . '"><span>' . ($option->wh_unit_weight) . '</span></td>'
                                                . '<td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                            }
                                        }
                                        ?></tbody>
                                </table>
                                <div class="color-box">
                                <h2>Color:</h2>
                                <?php
                                    $colorarray = array();
                                    if(isset($product_color)){
                                        foreach($product_color as $color_attr){
                                            $colorarray[$color_attr->name] = $color_attr->name ;
                                        }
                                    }
                                ?>
                                <select class="form-control select" name="AttrColor[]" id="AttrColor" multiple="multiple" style="width:100%">
                                    <?php foreach($variants_color as $color){ ?>
                                        <option value="<?= $color->name ?>" <?= in_array($color->name,$colorarray)?'Selected' :'' ?>><?= $color->name ?></option>
                                    <?php } ?>
                                </select>
                                <?php
								?>
                        </div>
                            </div>
                        </div>
                        
                        <!-- <div class="form-group all">
                            <?= lang("Color", "color") ?>
                            <?php
                            $colorarray = array();
                            if (isset($product_color)) {
                                foreach ($product_color as $color_attr) {
                                    $colorarray[$color_attr->name] = $color_attr->name;
                                }
                            }
                            ?>
                            <select class="form-control" name="AttrColor[]">
                                <option value=""> -- Select Color -- </option>
                                <?php foreach ($variants_color as $color) { ?>
                                    <option vlaue="<?= $color->name ?>" <?= in_array($color->name, $colorarray) ? 'Selected' : '' ?>><?= $color->name ?></option>
                                <?php } ?>
                            </select>
                        </div> -->
                        <div class="form-group all">
                            <?= lang("product_cost", "cost") ?> 
                            <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->sma->formatDecimal($product->cost) : '')), 'class="form-control tip custom_price" id="cost" required="required"') ?>
                             <span id="costerr" class="errmsg text-danger"> </span>
                        </div>
                        <div class="form-group all">
                                <?= lang("product_mrp", "mrp") ?>
                                <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("product_price", "price") ?>
                            <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price" required="required"') ?>
                             <span id="priceerr" class="errmsg text-danger"> </span>
                        </div>
                        <div class="form-group all">
                            <?= lang("Packing Size", "packing_size") ?>
                            <?= form_input('packing_size', (isset($_POST['packing_size']) ? $_POST['packing_size'] : ($product ? $this->sma->formatDecimal($product->packing_size) : '')), 'class="form-control tip" id="packing_size" placeholder="0"') ?>
                            <span class="help-block"><?= lang('e.g., 30 for 30 bottles per crate') ?></span>
                        </div>
                        <!-- <div class="form-group all">
                                <?= lang("product_mrp", "mrp") ?>
                                <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                        </div> -->
                        <!-- <div class="form-group all">
                            <?= lang("product_mrp", "price") ?>
                            <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                             <span id="mrperr" class="errmsg text-danger"> </span>
                        </div> -->
                        <?php if ($enable_discount_on_mrp) { ?>
                        <div class="form-group all discount-on-mrp-col">
                                <label for="discount_on_mrp"><?= lang("Discount On MRP", "discount_on_mrp") ?></label>
                                <?= form_input('discount_on_mrp', (isset($_POST['discount_on_mrp']) && $_POST['discount_on_mrp'] !== '') ? $_POST['discount_on_mrp'] : '', 'class="form-control" id="discount_on_mrp" placeholder=""'); ?>
                        </div>
                        <?php } else { ?>
                        <input type="hidden" name="discount_on_mrp" id="discount_on_mrp" value="0%" />
                        <?php } ?>
                        

                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group all" >
                                <?= lang("product_tax", "tax_rate") ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                                ?>
                            </div>
                            <div class="form-group all"  >
                                <?= lang("tax_method", "tax_method") ?>
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                ?>
                            </div>
                        <?php } ?>

                        <div class="form-group col-xs-4">
                            <!-- <input type="checkbox" class="checkbox" value="1" name="flag_visible"
                            <?= $this->input->post('flag_visible') ? 'checked="checked"' : ''; ?>> -->
                            <input type="checkbox" class="checkbox" value="1" name="flag_visible" checked="checked">
                            <label for="flag_visible" class="padding05">
                                <?= lang("Flag_Visible", "Flag Visible"); ?>
                            </label>
                        </div>


                        <div class="form-group all text-danger" id="errormsg">
                            
                        </div>
                    </div>

                   
                        <!-- <div class="form-group all">
                            <?= lang("Size", "size") ?>
                            <select clas="form-control " style="width:100%" id="attributesInput" name="attributesInput[]" multiple="true">
                                <?php foreach ($vars as $value) { ?>
                                    <option value="<?= $value ?>"><?= $value ?> </option> 
                                <?php } ?>
                            </select>
                        </div>
                        <div >
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th> Size</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="sizeqty">
                                   
                                </tbody>
                            </table>
                        </div> -->
                                        
                <div class="col-md-5">
                    

                </div>    
            </div>
            <div class="modal-footer">
                <?php echo form_submit('add_product', lang('Add Product'), 'class="btn btn-primary" id="add_product"'); ?>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
if(!window.jQuery) {
    var pn = window.location.pathname;
    var modal_exp = pn.split('/');
    window.location.replace(window.location.protocol+'//'+window.location.host+'/'+modal_exp[1]);
}



$(document).ready(function(e) {
    $('form[data-toggle="validator"]').bootstrapValidator({ feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'}, excluded: [':disabled'] });
    fields = $('.modal-content').find('.form-control');
    $.each(fields, function() {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');
        var iid = '#'+id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function(){
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
            });
        }
    });
    $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });
    $("textarea").not('.skip').redactor({
        buttons: ["formatting", "|", "alignleft", "aligncenter", "alignright", "justify", "|", "bold", "italic", "underline", "|", "unorderedlist", "orderedlist", "|", "link", "|", "html"],
        formattingTags: ["p", "pre", "h3", "h4"],
        minHeight: 100,
        changeCallback: function(e) {
            var editor = this.$editor.next('textarea');
            if($(editor).attr('required')){
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', $(editor).attr('name'));
            }
	   }
    });
    $(".input-tip").tooltip({placement: "top", html: true, trigger: "hover focus", container: "body",
        title: function() {
            return $(this).attr("data-tip");
        }
    });
    $(".input-pop").popover({placement: "top", html: true, trigger: "hover focus", container: "body",
        content: function() {
            return $(this).attr("data-tip");
        },
        title: function() {
            return "<b>" + $('label[for="' + $(this).attr("id") + '"]').text() + "</b>";
        }
    });
    $('select, select.select').select2({minimumResultsForSearch: 7});
    $('#date_range').daterangepicker({ format: site.dateFormats.js_sdate }, function(start, end, label) {
        $('#from_date').val(start.format('YYYY-MM-DD'));
        $('#to_date').val(end.format('YYYY-MM-DD'));
    });
    $('#myModal').on('shown.bs.modal', function() {
        $('.modal-body :input:first').focus();
    });
    $('#csv_file').change(function(e) {
	v = $(this).val();
	if (v != '') {
	    var validExts = new Array(".csv");
	    var fileExt = v;
	    fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
	    if (validExts.indexOf(fileExt) < 0) {
		e.preventDefault();
		bootbox.alert("Invalid file selected. Only .csv file is allowed.");
		$(this).val('');
		$('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'csv_file', 'NOT_VALIDATED');
		return false;
	    }
	    else
		return true;
	}
    });
});
$(function() {
    $('.datetime').datetimepicker({format: site.dateFormats.js_ldate, language: 'sma', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
    $('.date').datetimepicker({format: site.dateFormats.js_sdate, language: 'sma', todayBtn: 1, autoclose: 1, minView: 2 });
});
</script>

<script>
    $("document").ready(function () {
        setTimeout(function () {
            var url = "<?= site_url('products/add') ?>";
            $("[id*='random_num']").trigger('click');
            return false;
        }, 10);
    });
    $('#random_num').click(function () {
        $(this).parent('.input-group').children('input').val(generateCardNo(5));
    });


    $('#category').change(function () {
        var v = $(this).val();
            $('#modal-loading').show();
        if (v) {
            $.ajax({
                type: "get",
                async: false,
                url: "<?= site_url('products/getSubCategories') ?>/" + v,
                dataType: "json",
                success: function (scdata) {
                    if (scdata != null) {
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                            placeholder: "<?= lang('select_category_to_load') ?>",
                            data: scdata
                        });
                    } else {
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                            placeholder: "<?= lang('no_subcategory') ?>",
                            data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                        });
                    }
                },
                error: function () {
                    bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                }
            });

        } else {
            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                placeholder: "<?= lang('select_category_to_load') ?>",
                data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
            });
        }
            $('#modal-loading').hide();
    });

    $("#add_product").click(function () {
        var formdata = $('#add-product-form').serialize();
        // console.log('formdata');
        // console.log(formdata);
        // console.log('formdata');

        $.ajax({
            type: 'ajax',
            dataType: 'json',
            url: '<?= base_url("products/quick_add_product") ?>',
            data: formdata,
            method: 'post',
            async: false,
            success: function (result) {
                if (result.status) {
                   if(result.type=='array'){
                        $('.close').trigger('click');
                        $.each(result.data, function(index, value){
                            var q = value['quantity'] ? value['quantity'] : '';
                            getProductDetails(value['product_code'], q);
                        });
                    }else{
                       $('.close').trigger('click');
                        getProductDetails(result.product_code, '');
                   }
                   $("#error-message").hide();
                } else {
                // Display validation errors inside a div
                $("#error-text").html(result.message);
                $("#error-message").show();
            }
            },
            error: function (xhr, status, error) {
        console.log("AJAX request failed!");
        console.log("Status:", status);
        console.log("Error:", error);
        console.log("Response Text:", xhr.responseText);
    }
        });
        return false;
    });


    function getProductDetails(term, quickQty) {
        var add_quick_product = 1;
        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'get',
            url: '<?= site_url('purchases/suggestions'); ?>',
            data: {
                term: term,
                add_quick_product: add_quick_product,
                quick_product_quantity: quickQty,
                supplier_id: $("#posupplier").val()
            },
            success: function (data) {

               add_purchase_item(data[0]);
            }, error:function(){
                console.log('error');
            }
        });
    }

</script>

<div class="modal" id="aModal" tabindex="-1" role="dialog" aria-labelledby="aModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                        <i class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="aModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
<!--                    <div class="form-group">
                        <label for="awarehouse" class="col-sm-4 control-label"><?= lang('warehouse') ?></label>
                        <div class="col-sm-8">
                            < ?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, '', 'id="awarehouse" class="form-control"');
                            ?>
                        </div>
                    </div>-->
                    <!--<div class="form-group">
                         <label for="aquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                         <div class="col-sm-8">
                             <input type="text" class="form-control" id="aquantity" onkeypress="return isNumberKeyQua(event)">
                             <span id="errorq" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                         </div>
                     </div>-->
                    <div class="form-group">
                        <label for="acost" class="col-sm-4 control-label"><?= lang('Cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="acost" onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorc" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('MRP') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice" onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="aunit_quantity" class="col-sm-4 control-label"><?= 'Unit Quantity'?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="aunit_quantity" min="0" max="100" step="0.125"  value="1" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="aunit_weight" class="col-sm-4 control-label"><?= 'Unit Weight (In KG)'?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" min="0" max="100" step="0.125" id="aunit_weight" >
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="updateAttr"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>


<script>
    var enableDiscountOnMrp = <?= $enable_discount_on_mrp ?>;
    function setDiscountOnMrp(val) {
        $('#discount_on_mrp').val(enableDiscountOnMrp ? val : '0%');
    }
    function setVariantDiscount($input, val) {
        if ($input && $input.length) {
            $input.val(enableDiscountOnMrp ? val : '0%');
        }
    }

    $('#attributesInput').change(function(){
        var sizetable = '';
        $.each($(this).val(),function(index, value){
           sizetable +='<tr>';
                sizetable +='<td>'+value+'</td>' ;
                sizetable +='<td><input name="sizename[]" type="hidden" value="'+value+'"> <input type="number" class="form-control" step="0" min="0" name="sizeqty[]" value="" /></td>';
           sizetable +='</tr>';
        });
        $('#sizeqty').html(sizetable);
        
    });

    var variants = <?= json_encode($vars); ?>;
        $(".select-tags").select2({
            tags: variants,
            tokenSeparators: [","],
            multiple: true
        });

        $('.attributes').on('ifChecked', function (event) {
            $('#options_' + $(this).attr('id')).slideDown();
        });
        $('.attributes').on('ifUnchecked', function (event) {
            $('#options_' + $(this).attr('id')).slideUp();
        });

        $(document).on('ifChecked', '#attributes', function (e) {
            $('#attr-con').slideDown();
            $('#cost').val('0');
            $('#mrp').val('0');
            $('#price').val('0');
            setDiscountOnMrp('0%');
            $('#cost').attr('readonly', true);
            $('#mrp').attr('readonly', true);
            $('#price').attr('readonly', true);
            if (enableDiscountOnMrp) {
                $('#discount_on_mrp').attr('readonly', true);
            }
        });
        $(document).on('ifUnchecked', '#attributes', function (e) {
            $(".select-tags").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();

            $('#price').attr('readonly', false);
            $('#mrp').attr('readonly', false);
            $('#cost').attr('readonly', false);
            if (enableDiscountOnMrp) {
                $('#discount_on_mrp').attr('readonly', false);
            }
        });
		
		var allowedAttrs = <?= json_encode($vars); ?>;
		
        $('#addAttributes').click(function (e) {
            e.preventDefault();
            var attrs_val = $('#attributesInput').val(), attrs;
            attrs = attrs_val.split(',');
            var wh_arr = [];
            var attr_arr = [];
            if ($.trim($('#attrTable tbody').html()) != '') {
//                $.each($(".attr_wh_name"), function (index, ele) {
//                    wh_arr.push(ele.value);
//
//                });
            $.each($(".attr_name"), function (index, ele) {
                    attr_arr.push(ele.value);

            });
            }
            var wh_arr_unique = $.unique(wh_arr);
            var attr_arr_unique = $.unique(attr_arr);
            for (var i in attrs) {
                if (attrs[i] !== '') {
                    var attrTrimmed = $.trim(attrs[i]);

                    // Check if attribute exists in allowed list
                    if (allowedAttrs.indexOf(attrTrimmed) === -1) {
                        bootbox.alert("'" + attrTrimmed + "' is not a valid variant, Please add new variant from variants screen!");
                        continue; // skip adding invalid ones
                    }
                    if ($('.attr_name[value="' + attrTrimmed + '"]').length > 0) {
                        continue;
                    }
<?php
//if (!empty($warehouses)) {
//    foreach ($warehouses as $warehouse) {
        ?>
//                            var wh_id = '< ?php echo $warehouse->name; ?>';
//                            //console.log(wh_id);
//
//                            if (($.inArray(attrs[i], attr_arr_unique) >= 0) && ($.inArray(wh_id, wh_arr_unique) >= 0)) {
//                                //alert('found');
//                            } else {
                        <?php //echo '$(\'#attrTable\').show().append(\'<tr class="attr"><td><input type="hidden" class="attr_name" name="attr_name[]" value="\' + attrs[i] + \'"><span>\' + attrs[i] + \'</span></td><td class="code text-center"><input type="hidden" class="attr_warehouse" name="attr_warehouse[]" value="' . $warehouse->id . '"><input type="hidden" class="attr_wh_name" name="attr_wh_name[]" value="' . $warehouse->name . '"><span>' . $warehouse->name . '</span></td><!--<td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value=""><span>0</span></td>--><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="unit_quantity text-right"><input type="hidden" name="attr_unit_quantity[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>\');'; ?>
//                        }
        <?php
//    }
//} else {
    ?>
                     //   $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden"  class="attr_warehouse" name="attr_warehouse[]" value=""><input type="hidden" class="attr_wh_name" name="attr_wh_name[]" value=""><span></span></td><!--<td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value=""><span></span></td>--><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="unit_quantity text-right"><input type="hidden" name="attr_unit_quantity[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                //$('#attrTable').show().append('<tr class="attr"><td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="0"><span>0</span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="unit_quantity text-right"><input type="hidden" name="attr_unit_quantity[]" value="0"><span>0</span></span></td><td class="unit_quantity text-right"><input type="hidden" name="attr_unit_weight[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');

                $('#attrTable').show().append(
                    '<tr class="attr">' +
                        '<td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrTrimmed + '"><span>' + attrTrimmed + '</span></td>' +
                        '<td class="cost text-right">' +
                            '<input type="number" class="form-control cost text-right" name="attr_cost[]" value="0" min="0">' +
                        '</td>' +
                        '<td class="variantmrp text-right" id="variant_mrp">' +
                            '<input type="number" class="form-control variantmrp text-right" name="attr_mrp[]" value="0" min="0">' +
                        '</td>' +
                        '<td class="price text-right" id="variant_price">' +
                            '<input type="number" class="form-control variantprice text-right" name="attr_price[]" value="0" min="0">' +
                        '</td>' +
                        '<td class="variantdiscount text-right discount-on-mrp-col" id="variant_discount">' +
                            '<input type="text" class="form-control variantdiscount text-right" name="attr_discount[]" value="0%" min="0">' +
                        '</td>' +
                        '<td class="unit_quantity text-right">' +
                            '<input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" min="0">' +
                        '</td>' +
                        '<td class="unit_weight text-right">' +
                            '<input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="0" min="0">' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<i class="fa fa-times delAttr"></i>' +
                        '</td>' +
                    '</tr>'
                );  


<?php //} ?>
                }
            }
        });

        $(document).on('click', '.delAttr', function () {
            $(this).closest("tr").remove();
        });
        $(document).on('click', '.attr-remove-all', function () {
            $('#attrTable tbody').empty();
            $('#attrTable').hide();
        });

        var row, warehouses = <?= json_encode($warehouses); ?>;
        // $(document).on('click', '.attr td:not(:last-child)', function () {
        $(document).on('click', '.attr td:first-child', function(event) {
            row = $(this).closest("tr");
            $('#aModalLabel').text(row.children().eq(0).find('span').text());
            
            $('#acost').val(row.children().eq(1).find('input').val());
            $('#aprice').val(row.children().eq(2).find('input').val());
            $('#aunit_quantity').val(row.children().eq(5).find('input').val());
            $('#aunit_weight').val(row.children().eq(6).find('input').val());
            
            $('#aModal').appendTo('body').modal('show');
        });

        $('#aModal').on('shown.bs.modal', function () {
            $('#aquantity').focus();
            $(this).keypress(function (e) {
                if (e.which == 13) {
                    $('#updateAttr').click();
                }
            });
        });
        $(document).on('click', '#updateAttr', function () {
                //            var wh = $('#awarehouse').val(), wh_name;
                //            $.each(warehouses, function () {
                //                if (this.id == wh) {
                //                    wh_name = this.name;
                //                }
                //            });
           // row.children().eq(1).html('<input type="hidden" name="attr_warehouse[]" value="' + wh + '"><input type="hidden" name="attr_wh_name[]" class="attr_wh_name" value="' + wh_name + '"><span>' + wh_name + '</span>');
            //row.children().eq(2).html('<input type="hidden" name="attr_quantity[]" value="' + $('#aquantity').val() + '"><span>' + decimalFormat($('#aquantity').val()) + '</span>');
            //row.children().eq(3).html('<input type="hidden" name="attr_price[]" value="' + $('#aprice').val() + '"><span>' + currencyFormat($('#aprice').val()) + '</span>');
           
            row.children().eq(1).html('<input type="number" class="form-control cost text-right" name="attr_cost[]" value="' + $('#acost').val() + '" min="0">');
            row.children().eq(2).html('<input type="number" class="form-control variantmrp text-right" name="attr_mrp[]" value="' + $('#aprice').val() + '" min="0">');
            row.children().eq(5).html('<input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="' + $('#aunit_quantity').val() + '" min="0">');
            row.children().eq(6).html('<input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="' + $('#aunit_weight').val() + '" min="0">');
            
            $('#aModal').modal('hide');

        });


    $(document).ready(function () {
    $('#mrp, #discount_on_mrp').on('change', function () {
        var priceValue = parseFloat($('#price').val());
        var mrpValue = parseFloat($('#mrp').val());
        var discountInput = $('#discount_on_mrp').val().trim();

        mrpValue = isNaN(mrpValue) ? 0 : mrpValue;
        if (isNaN(mrpValue) || mrpValue < 0) {
            alert("MRP cannot be negative!");
            $('#mrp').val(Math.abs(mrpValue));
            $('#price').val(Math.abs(mrpValue));
            setDiscountOnMrp('0%');
            return;
        }
        priceValue = isNaN(priceValue) ? 0 : priceValue;

        if (!enableDiscountOnMrp) {
            $('#price').val(mrpValue.toFixed(0));
            setDiscountOnMrp('0%');
            return;
        }

        $('#price').val(mrpValue.toFixed(0));

        var discountValue, discountType;
        if (discountInput.endsWith('%')) {
            discountValue = parseFloat(discountInput.slice(0, -1)); // Remove "%" and convert to number
            discountType = 'percentage';
        } else {
            discountValue = parseFloat(discountInput);
            discountType = 'absolute';
        }
        if (discountInput === '' || isNaN(discountValue)) {
            setDiscountOnMrp('0%');
            $('#price').val(mrpValue.toFixed(0)); // Reset price to MRP
            return;
        }

        // Immediate validation: Prevent discount > 100% (percentage) or discount > MRP (absolute)
        if (!isNaN(discountValue)) {
            if (discountType === 'percentage' && discountValue > 100) {
                alert("Discount should not exceed 100%");
                setDiscountOnMrp('0%');
                return;
            } else if (discountType === 'absolute' && discountValue > mrpValue) {
                alert("Discount cannot be greater than MRP!");
                setDiscountOnMrp('0');
                return;
            }
            if (discountValue < 0) {
                    alert("Discount cannot be negative!");
                    setDiscountOnMrp('0');
                    return;
                }
        }

        if (!isNaN(mrpValue) && mrpValue > 0) {
            if (!isNaN(priceValue) && priceValue > 0 && mrpValue >= priceValue) {
                var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                
                // Update discount field ONLY if it's empty or '0%'
                if (!discountInput || discountInput === '0%') {
                    setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
                }

                if (!isNaN(discountValue) && discountValue >= 0) {
                    var calculatedPrice;

                    if (discountType === 'percentage') {
                        calculatedPrice = mrpValue - (mrpValue * (discountValue / 100));
                    } else {
                        calculatedPrice = mrpValue - discountValue;
                    }
                    $('#price').val(calculatedPrice.toFixed(0));
                }
            } else {
                setDiscountOnMrp('0%');
            }
        } else if (mrpValue === 0) {
            $('#price').val(0);
        }

        discountDisebled();
    });

    $('#price').on('change', function () {
        var priceInput = $('#price').val().trim();
        var priceValue = parseFloat(priceInput);
        var mrpValue = parseFloat($('#mrp').val());

        if (priceInput === '' || isNaN(priceValue)) {
            $('#price').val(mrpValue.toFixed(0));
            setDiscountOnMrp('0%');
            return;
        }

        if (priceValue < 0) {
            alert('Price cannot be negative!');
            $('#price').val(mrpValue.toFixed(0));
            setDiscountOnMrp('0%');
            return;
        }

        if (priceValue > mrpValue) {
            alert('Price cannot be greater than MRP!');
            $('#price').val(mrpValue.toFixed(0));
            setDiscountOnMrp("0%");
            $('#product_discount').attr('readonly', false);
            return;
        }

        if (!enableDiscountOnMrp) {
            setDiscountOnMrp('0%');
            $('.attr_price').trigger('click');
            discountDisebled();
            return;
        }

        if (!isNaN(priceValue) && !isNaN(mrpValue) && mrpValue > 0) {
            var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
            setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
        }

        $('.attr_price').trigger('click');
        discountDisebled();
    });
    $('#cost').on('change', function () {
        var costValue = parseFloat($('#cost').val());

        if (costValue < 0) {
            alert('Cost cannot be negative!');
            $('#cost').val(Math.abs(costValue));
            return;
        }
    });

    $('#product_discount, #discount_on_mrp').on('change', function () {
        discountDisebled();
    });

    $(document).on('input', '.cost,.variantmrp, .variantdiscount, .variantprice,.unit_quantity,.unit_weight', function () {
        var $row = $(this).closest('tr');
        var cost = parseFloat($row.find('.cost input').val()) || 0; 
        var mrp = parseFloat($row.find('.variantmrp input').val()) || 0; 
        var price = parseFloat($row.find('.variantprice input').val()) || 0;
        var quantity = parseFloat($row.find('.unit_quantity input').val()) || 0; 
        var weight = parseFloat($row.find('.unit_weight input').val()) || 0;  
        var discount = $row.find('.variantdiscount input').val();
        
        if (cost < 0 || $row.find('.cost input').val().trim().startsWith('-')) {
            alert("Cost cannot be negative!");
            cost = Math.abs(cost);
            $row.find('.cost input').val(cost.toFixed(0));
        } 
        if (mrp < 0 || $row.find('.variantmrp input').val().trim().startsWith('-')) {
            alert("MRP cannot be negative!");
            mrp = Math.abs(mrp);
            $row.find('.variantmrp input').val(mrp.toFixed(0));
        } 
        if (quantity < 0 || $row.find('.unit_quantity input').val().trim().startsWith('-')) {
            alert("Quantity cannot be negative!");
            quantity = Math.abs(quantity);
            $row.find('.unit_quantity input').val(quantity.toFixed(0)); 
        }
        if (weight < 0 || $row.find('.unit_weight input').val().trim().startsWith('-')) {
            alert("Weight cannot be negative!");
            weight = Math.abs(weight);
            $row.find('.unit_weight input').val(weight.toFixed(0)); 
        }
        var discountValue = 0;
        var isPercentage = false;
        if (discount.includes('-')) {
            alert("Discount cannot be negative!");
            discountValue = 0;
            $row.find('.variantdiscount input').val("0%"); 
            $row.find('.variantprice').val(mrp.toFixed(0)); 
            return;
        }

        if (discount.includes('%')) {
            discountValue = parseFloat(discount.replace('%', '')) || 0;
            isPercentage = true;
        } else {
            discountValue = parseFloat(discount) || 0;
        }

        // When MRP is changed, set the price to MRP and reset discount to 0%
        if ($(this).is('.variantmrp')) {
            $row.find('.variantprice').val(mrp.toFixed(0)); 
            setVariantDiscount($row.find('.variantdiscount input').first(), "0%"); 
            return;
        }

        // When price is changed, calculate the discount
        if ($(this).is('.variantprice')) {
            price = parseFloat($row.find('.variantprice').val()) || 0;

            if (
                    isNaN(price) || 
                    price < 0 || 
                    $row.find('.variantprice').val().trim().startsWith('-')
            ) {
                alert('Price cannot be negative!Please enter a valid price greater than or equal to 0');
                $row.find('.variantprice').val(mrp.toFixed(0)); 
                $row.find('.variantdiscount').val("0%"); 
                return;
            }

            if (price > mrp) {
                alert('Price cannot be greater than MRP!');
                $row.find('.variantprice').val(mrp.toFixed(0)); 
                $row.find('.variantdiscount').val("0%"); 
                return;
            }

            if (mrp > 0) {
                var calculatedDiscount = ((mrp - price) / mrp) * 100;
                setVariantDiscount($row.find('.variantdiscount input').first(), calculatedDiscount.toFixed(0) + '%');
            } else if (!enableDiscountOnMrp) {
                setVariantDiscount($row.find('.variantdiscount input').first(), '0%');
            }
        }

        // When discount is changed, calculate the price
        if ($(this).is('.variantdiscount')) {
            if (!enableDiscountOnMrp) {
                setVariantDiscount($row.find('.variantdiscount input').first(), '0%');
                return;
            }
            if (isPercentage) {
                if (discountValue > 100) {
                    alert("Discount should not exceed 100%");
                    $row.find('.variantdiscount').val("0%"); 
                    $row.find('.variantprice').val(mrp.toFixed(0)); 
                    return;
                }

                if (mrp > 0) {
                    var calculatedPrice = mrp - (mrp * (discountValue / 100));
                    $row.find('.variantprice').val(calculatedPrice.toFixed(0)); 
                }
                } else {
                if (discountValue > mrp) {
                    alert("Discount cannot be more than MRP");
                    $row.find('.variantdiscount').val("0"); 
                    $row.find('.variantprice').val(mrp.toFixed(0)); 
                    return;
                }

                if (mrp > 0) {
                    var calculatedPrice = mrp - discountValue;
                    if (calculatedPrice < 0) calculatedPrice = 0; 
                    $row.find('.variantprice').val(calculatedPrice.toFixed(0)); 
                }
        }
    }

    });
    });
//Validation for variant popup
    $(document).on('input', '.cost, .price, .unit_quantity', function () {
    var $row = $(this).closest('tr');
    var value = parseFloat($(this).val()) || 0;

    if ($(this).hasClass('cost') && value < 0) {
        alert('Cost cannot be negative!');
        $(this).val(Math.abs(value));
    } else if ($(this).hasClass('price') && value < 0) {
        alert('Price cannot be negative!');
        $(this).val(Math.abs(value));
    } else if ($(this).hasClass('unit_quantity') && value < 0) {
        alert('Unit Quantity cannot be negative!');
        $(this).val(Math.abs(value));
    }
});


    function discountDisebled(){
       var product_discount = parseFloat($('#product_discount').val()) || 0;
        var discount_on_mrp = parseFloat($('#discount_on_mrp').val()) || 0;

        if (product_discount > 0) {
            $('#discount_on_mrp').attr('readonly', true);
            setDiscountOnMrp('0%');

        } else {
            $('#discount_on_mrp').attr('readonly', false);
        }
        if (discount_on_mrp > 0) {
            $('#product_discount').attr('readonly', true);
            $('#product_discount').val('0%');

        } else {
            $('#product_discount').attr('readonly', false);
        }
    }


    


</script>    
