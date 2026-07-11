
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
input#discount_on_mrp{
        margin-top: -7px;

}
div#attr-con {
    width: 120%;
    max-width: 150%;
    margin-left: 1px;
}
table#attrTable{
    margin-bottom: 0px;
    margin-top: 10px;
    margin-left: -5px;
}
.form-group.variant-label.variant-container {
    margin-left: 0px;
}
.variant-container {
    display: flex;
    align-items: center;  
    gap: 10px; 
}
.variant-label {
    font-size: 14px;
    font-weight: bold;
    white-space: nowrap; 
}
input.text-right{
    width: 97%;
}
input[type="text"]{
    width: 97%;
}
.attr-input {
    width: 100px;
    height: 31px;
    font-size: 15px;
    padding: 4px;
    text-align: center;
}
label{
    margin-top: 2px;
}
.marginset{
    margin-top: 10px;
}
@media (min-width: 768px) and (max-width: 1199px) {
    div#attr-con {
        width: 120%;
        margin-left: auto;
        margin-right: auto;
    }
}
@media (min-width: 768px) {
    .form-horizontal .control-label {
        padding-top: 7px;
        margin-bottom: 0;
        text-align: left;
        margin-left: 0px;
    }
}
@media (max-width: 767px) {
    .modal-open .modal {
        overflow-y: auto; 
        padding: 0px;
        margin-top: -6px;
    }
}
@media (max-width: 767px) {
    div#attr-con {
        width: 100%;
        margin-left: 0;
    }
}
@media (max-width: 767px) {
    .modal-body {
        max-height: 70vh;
        overflow-y: scroll;
        padding: 6px;
        margin-top: -14px;
    }
}

@media (min-width: 992px) {
    .col-md-6 {
        width: 50%;
    }
}
</style>
<script type="text/javascript">
    $(document).ready(function () {
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#subcategory').change(function () {
            var id = $(this).val();
            if (id) {
                setProductTaxRate(id);
            }
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
                        console.log(scdata);
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

                setProductTaxRate(v);

            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });
        $('#code').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    });

    function setProductTaxRate(id) {

        $.ajax({
            type: "get",
            async: false,
            url: "<?= site_url('products/getCategoryTaxrate') ?>/" + id,
            dataType: "json",
            success: function (taxrateid) {
                if (taxrateid != null) {
                    $('#tax_rate').val(taxrateid);
                    $('#tax_rate').select2().trigger('change');
                } else {
                    $('#tax_rate').val(1);
                    $('#tax_rate').select2().trigger('change');
                }
            },
            error: function () {
                bootbox.alert('<?= lang('ajax_error') ?>');
                $('#modal-loading').hide();
            }
        });
    }

</script>
<style>
    .select2-container-multi{
        height: auto;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_product'); ?></h2>
    </div>
<p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/add", $attrib);
                ?>
                <input type="hidden" name="pos_type" id="pos_type" value="<?= $Settings->pos_type ?>" />
                <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("product_type", "type") ?>
                                <?php
                                $opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'Bundle' => lang('Bundle'),'digital' => lang('digital'), 'service' => lang('service'),'raw' => lang('Raw'), 'Intermediate' => lang('Intermediate'));
                                echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                                ?>
                            </div>
                        </div>                    
                        <div class="col-md-6">
                            <div class="form-group all ">
                                <?= lang("Storage Type", "storage_type") ?>
                                <?php                        
                                $ist = ['packed'=>'Packed Products','loose'=>'Loose Products'];
                                echo form_dropdown('storage_type', $ist, (isset($_POST['storage_type']) ? $_POST['storage_type'] : ''), 'class="form-control select" id="storage_type" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                ?>
                            </div>
                         </div>                     
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = "";
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category"  placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("subcategory", "subcategory") ?>
                                <div class="controls" id="subcat_data"> <?php
                                    echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($Settings->other_category_for_product == 1) : ?>
                        <div class="col-md-12">
                            <div class="form-group all">
                                <?= lang("Other Categorys", "Other Categorys") ?>
                                <div class="controls" id="othercategory_data">
                                    <?php
                                    $selected_category = isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '');

                                    $other_cat_options = ['' => ''];
                                    foreach ($categories as $category) {
                                        if ($category->id != $selected_category) {
                                            $other_cat_options[$category->id] = $category->name;
                                        }
                                    }
                                    $selected_other_categories = [];
                                    if ($product && !empty($product->other_categories)) {
                                        $selected_other_categories = explode(',', $product->other_categories);
                                    }

                                    echo form_dropdown('othercategory[]',$other_cat_options,$selected_other_categories,'class="form-control select" id="othercategory" multiple="multiple" style="width:100%;"'
                                    );
                                ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                          <div class="form-group all">
                            <?= lang("product_name", "name") ?>
                            <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required" autocomplete="off" '); ?>
                          </div>
                        </div>
                        <div class="col-md-4">
                              <div class="form-group all">
                                <?= lang("product_code", "code") ?>
                                    <div class="input-group">
                                        <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code" required="required" placeholder="Use comma to enter codes per color in order"') ?>
                                        <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                            <i class="fa fa-random"></i>
                                        </span>
                                    </div>
                               <!-- <span class="help-block"><?= lang('you_scan_your_barcode_too') ?></span>-->
                               </div>
                        </div>
                            <script>
                              $("document").ready(function () {
                                  setTimeout(function () {
                                      var url = "<?= site_url('products/add') ?>";
                                      //alert(url);
                                      $("[id*='random_num']").trigger('click');
                                      //window.location.href='products/add';
                                      return false;
                                  }, 10);
                              });
                          </script>
                    </div>
                      <div class="row">
                        <div class="col-md-4">
                            <div class="form-group all">
                                        <?= lang("Article Number", "Article Number") ?>
                                <?= form_input('article_code', (isset($_POST['article_code']) ? $_POST['article_code'] : ($product ? $product->article_code : '')), 'class="form-control" id="article_no"   type="text"  ') ?>
                                <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                            </div> 
                        </div>  
                        <!-- End 12-03-19 -->
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("hsn_code", "hsn_code") ?>
                                <?= form_input('hsn_code', (isset($_POST['hsn_code']) ? $_POST['hsn_code'] : ($product ? $product->hsn_code : '')), 'class="form-control" id="hsn_code"  '); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">                         
                                <?= lang("brand", "brand") ?>
                                <?php
                                if (!empty($brands) && is_array($brands)) {
                        
                                    $brnd[''] = "";

                                    foreach ($brands as $brand) {
                                        $brnd[$brand->id] = $brand->name;
                                    }                                    
                                    echo form_dropdown('brand', $brnd, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"');
                                }   
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($Settings->display_job_work)) { ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group all">                         
                                <?= lang("Inward_Type", "Inward_Type") ?>
                                <?php
                                $pit = array('' => lang('select').' '.lang('inward_type'));
                                $default_inward = '';
                                if (!empty($product_inward_type)) {
                                    foreach ($product_inward_type as $key => $row) {
                                        $pit[$row->id] = $row->type;
                                        // get first value
                                        if ($key === 0) {
                                            $default_inward = $row->id;
                                        }
                                    }
                                }
                                $selected_value = isset($_POST['product_inward_type']) ? $_POST['product_inward_type'] : ($product ? $product->product_inward_type : $default_inward);
                                echo form_dropdown('product_inward_type', $pit, $selected_value, 'class="form-control select" id="product_inward_type" style="width:100%"' );
                                ?>
                            </div>
                        </div>
                        <div class="col-md-8" id="job_work_div" style="display:none;">
                            <div class="form-group">
                                <?= lang("Job_Works", "Job_Works"); ?>
                                <?php
                                $jw = array();
                                if (!empty($job_works)) {
                                    foreach ($job_works as $row) {
                                        $jw[$row->id] = $row->items;
                                    }
                                }
                                echo form_dropdown('job_work[]', $jw, (isset($_POST['job_work']) ? $_POST['job_work'] : ''),'class="form-control select" id="job_work" multiple="multiple" style="width:100%"' );
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group standard">
                                <?= lang('product_unit', 'unit'); ?>
                                <?php
                                $pu[''] = lang('select') . ' ' . lang('unit');
                                foreach ($base_units as $bu) {
                                    $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                                }
                                ?>
                                <?= form_dropdown('unit', $pu, set_value('unit', ($product ? $product->unit : '')), 'class="form-control tip" id="unit" required="required" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group standard">
                                <?= lang('default_sale_unit', 'default_sale_unit'); ?>
                                <?php $uopts[''] = lang('select_unit_first'); ?>
                                <?= form_dropdown('default_sale_unit', $uopts, ($product ? $product->sale_unit : ''), 'class="form-control" id="default_sale_unit" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group standard">
                                <?= lang('default_purchase_unit', 'default_purchase_unit'); ?>
                                <?= form_dropdown('default_purchase_unit', $uopts, ($product ? $product->purchase_unit : ''), 'class="form-control" id="default_purchase_unit" style="width:100%;"'); ?>
                            </div>
                        </div>
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
                                Sale Unit
                            </label>

                        </div>
                    </div>
                </div>
                    <div class="standard">

                        <div id="attrs"></div>

                        <div class="form-group variant-label variant-container    ">
                            <input type="checkbox" class="checkbox" name="attributes" id="attributes"
                                <?= $this->input->post('attributes') || $product_options ? 'checked="checked"' : ''; ?>><label
                                for="attributes" class="padding05"><?= lang('product_has_attributes'); ?></label>
                            <br /><span class="text-info">Ex. Sizes, Models or Weight</span>
                        </div>
                        <div class="well well-sm" id="attr-con"
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
                                    <option value="<?= $color->name ?>"
                                        <?= in_array($color->name,$colorarray)?'Selected' :'' ?>><?= $color->name ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <?php
                                	/*foreach($variants_color as $color){
										echo '<input type="checkbox" class="Color_chk" name="AttrColor[]" id="'.$color->id.'" value="'.$color->name.'"> '.$color->name;
									}*/
								?>
                            </div>
                            <div class="table-responsive">
                                <table id="attrTable" class="table table-bordered table-condensed table-striped"
                                    style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>margin-bottom: 0; margin-top: 10px;">
                                    <thead>
                                        <tr class="active">
                                            <th><?= lang('name') ?></th>
                                            <!--<th><?= lang('warehouse') ?></th>-->
                                            <!--<th><?= lang('quantity') ?></th>-->
                                            <th><?= lang('Cost') ?></th>
                                            <th><?= lang('MRP') ?></th>
                                            <th><?= lang('Price') ?></th>
                                            <th class="discount-on-mrp-col"><?= lang('Dis') ?></th>

                                            <?php if ($Settings->pos_type == 'restaurant') { ?>               
                                            <th><?= lang('Urbanpier_Price_Addition') ?></th>
                                            <?php } ?>
                                            <th><?= 'Unit Qty'?></th>
                                            <th><?= 'Unit Wgt'?></th>
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
                                                    . '<td class="variantmrp text-right"><input type="hidden" name="attr_mrp[]" value="' . $_POST['attr_mrp'][$r] . '"><span>' . $_POST['attr_mrp'][$r] . '</span></span></td>'
                                                    . '<td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td>'
                                                    . '<td class="variantdiscount text-right discount-on-mrp-col"><input type="hidden" name="attr_discount[]" value="' . ($enable_discount_on_mrp ? $_POST['attr_discount'][$r] : '0%') . '"><span>' . ($enable_discount_on_mrp ? $_POST['attr_discount'][$r] : '0%') . '</span></span></td>';

                                                   if ($Settings->pos_type == 'restaurant') {  
                                                    echo '<td class="upprice text-right"><input type="hidden" name="attr_upprice[]" value="' . $_POST['attr_upprice'][$r] . '"><span>' . $_POST['attr_upprice'][$r] . '</span></span></td>';
                                                   }  
                                                    echo  '<td class="price text-right"><input type="hidden" name="attr_unit_quantity[]" value="' . $_POST['attr_unit_quantity'][$r] . '"><span>' . $_POST['attr_unit_quantity'][$r] . '</span></span></td>'
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
                                                . '<td class="variantmrp text-right"><input type="hidden" name="attr_mrp[]" value="' . $this->sma->formatMoney($option->mrp) . '"><span>' . $this->sma->formatMoney($option->mrp) . '</span></span></td>'
                                                . '<td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $this->sma->formatMoney($option->price) . '"><span>' . $this->sma->formatMoney($option->price) . '</span></span></td>'
                                                . '<td class="variantdiscount text-right discount-on-mrp-col"><input type="hidden" name="attr_discount[]" value="' . ($enable_discount_on_mrp ? $this->sma->formatMoney($option->variant_discount_on_mrp) : '0%') . '"><span>' . ($enable_discount_on_mrp ? $this->sma->formatMoney($option->variant_discount_on_mrp) : '0%') . '</span></span></td>';

                                                if ($Settings->pos_type == 'restaurant'){ 
                                                 echo  '<td class="upprice text-right"><input type="hidden" name="attr_upprice[]" value="' . $this->sma->formatMoney($option->up_price) . '"><span>' . $this->sma->formatMoney($option->up_price) . '</span></span></td>';
                                               } 
                                                echo '<td class="unit_quantity text-center"><input type="hidden" name="attr_unit_quantity[]" value="' . ($option->wh_unit_qty) . '"><span>' . ($option->wh_unit_qty) . '</span></td>'
                                                . '<td class="unit_weight text-center"><input type="hidden" name="attr_unit_weight[]" value="' . ($option->wh_unit_weight) . '"><span>' . ($option->wh_unit_weight) . '</span></td>'
                                                . '<td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                            }
                                        }
                                        ?></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="<?= $product ? 'text-warning' : '' ?>" style="display:none;">
                            <strong><?= lang("warehouse_quantity") ?></strong><br>
                            <?php
                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            if (!empty($warehouses)) {
                                if ($product) {
                                    echo '<div class="row"><div class="col-md-12"><div class="well"><div id="show_wh_edit">';
                                    if (!empty($warehouses_products)) {
                                        echo '<div style="display:none;">';
                                        foreach ($warehouses_products as $wh_pr) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->sma->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        }
                                        echo '</div>';
                                    }
                                    foreach ($warehouses as $warehouse) {

                                        if ($Owner || $Admin) {
                                            //$whs[$warehouse->id] = $warehouse->name;
                                            echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                            if ($Settings->racks) {
                                                echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                            }
                                            echo '</div>';
                                        } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                            echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                            if ($Settings->racks) {
                                                echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                    }
                                    echo '</div><div class="clearfix"></div></div></div></div>';
                                } else {
                                    echo '<div class="row"><div class="col-md-12"><div class="well">';
                                    foreach ($warehouses as $warehouse) {
                                        //$whs[$warehouse->id] = $warehouse->name;
                                        if ($Owner || $Admin) {
                                            echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : ''), 'class="form-control" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                            if ($Settings->racks) {
                                                echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : ''), 'class="form-control" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                            }
                                            echo '</div>';
                                        } elseif (in_array($warehouse->id, $permisions_werehouse)) {
                                            echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                            if ($Settings->racks) {
                                                echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                    }
                                    echo '<div class="clearfix"></div></div></div></div>';
                                }
                            }
                            ?>
                        </div>
                        <div class="clearfix"></div>

                    </div>
                    <div class="combo" style="display:none;">

                        <div class="form-group">
                            <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>
                            <div class="controls table-controls">
                                <table id="prTable"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th class="col-md-5 col-sm-5 col-xs-5"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                            <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                            <th class="col-md-3 col-sm-3 col-xs-3 bundel" style="display:none;"><?= lang("unit_price"); ?></th>
                                            <th class="col-md-3 col-sm-3 col-xs-3 bundel" style="display:none;"><?= lang("Price"); ?></th>

                                            <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="digital" style="display:none;">
                        <div class="form-group digital">
                            <?= lang("digital_file", "digital_file") ?>
                            <input id="digital_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="digital_file" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>
                    </div>
                    <div class="row"> 
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_mrp", "mrp") ?>
                                <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                            </div>
                        </div>  
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_price", "price") ?>
                                <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price" required="required"') ?>
                            </div>
                        </div>
                    
                    </div>
                    <div class="row">
                        <?php if ($enable_discount_on_mrp) { ?>
                        <div class="col-md-4 discount-on-mrp-col">
                            <div class="form-group all">
                                <label for="discount_on_mrp"><?= lang("Discount On MRP", "discount_on_mrp") ?></label>
                                <?= form_input('discount_on_mrp', (isset($_POST['discount_on_mrp']) && $_POST['discount_on_mrp'] !== '') ? $_POST['discount_on_mrp'] : '', 'class="form-control" id="discount_on_mrp" placeholder=""'); ?>
                            </div>
                        </div>
                        <?php } else { ?>
                        <input type="hidden" name="discount_on_mrp" id="discount_on_mrp" value="0%" />
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_cost", "cost") ?> *
                                <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->sma->formatDecimal($product->cost) : '')), 'class="form-control tip custom_price" id="cost" required="required"') ?>
                            </div>
                        </div>
                        <?php if ($Settings->packing_size_column == 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("Packing Size", "packing_size") ?>
                                    <?= form_input(
                                        'packing_size',
                                        (isset($_POST['packing_size']) 
                                            ? $_POST['packing_size'] 
                                            : ($product ? $this->sma->formatDecimal($product->packing_size) : '')
                                        ),
                                        'class="form-control tip" id="packing_size" placeholder="0"'
                                    ) ?>
                                    <span class="help-block">
                                        <?= lang('e.g., 30 for 30 bottles per crate') ?>
                                    </span>
                                </div>
                            </div>
                        <?php } ?>

                    </div>  
                    
                    <?php if (in_array($Settings->pos_type , ['restaurant', 'amstead'])) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group all">
                                 <?= lang("division", "division") ?>
                                <?php
                                $br[''] = "";
                                foreach ($divisions as $division) {
                                    $br[$division->id] = $division->name;
                                }
                                echo form_dropdown('division', $br, (isset($_POST['division']) ? $_POST['division'] : ($product ? $product->division : $_SESSION['division'])), 'class="form-control select" id="division" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (in_array($Settings->pos_type , ['fruits_vegetables', 'fruits_vegetabl', 'grocerylite', 'grocery'])) { ?>
                    <div class="form-group all">
                        <?= lang("Product_Weight", 'weight') ?>
                        <div class="input-group">
                            <?= form_input('weight', (isset($_POST['weight']) ? $_POST['weight'] : ($product ? $product->weight : '')), 'class="form-control" id="weight" size="6" ') ?>
                            <span class="input-group-addon" style="padding: 1px 10px;">
                                / Kilogram (KG)
                            </span>
                        </div>  
                        <span class="help-block"><?= lang('Products weight should be in Kilogram (Ex. 1Kg = 1 | 500Gm = 0.500 | 250Gm = 0.250, etc.)') ?></span>
                    </div>
                    <?php } ?>
                    <!-- 12-03-19 -->
                  
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group standard">
                                <?= lang("alert_quantity", "alert_quantity") ?>
                                <div class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->sma->formatQuantity($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                                    <span class="input-group-addon">
                                        <input type="checkbox" name="track_quantity" id="track_quantity"
                                               value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("barcode_symbology", "barcode_symbology") ?>
                                <?php
                                $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E');
                                echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    
                   <div class="row">
                        <div class="col-md-6">
                            <div class="form-group all">
                                  <?= lang("Repeat Sale Discount Rate", "repeat_sale_discount_rate") ?>
                                <input type="text" id="repeat_sale_discount_rate" placeholder="Repeat Sale Discount Rate" class="form-control" name="repeat_sale_discount_rate" />
                                
                            </div>    
                        </div>
                         <div class="col-md-6">
                              <?= lang("Repeat_Sale_Validity", "repeat_sale_validity") ?>
                             <input type="number" min="1" id="repeat_sale_validity" placeholder="Repeat Sale Validity In Days" class="form-control" name="repeat_sale_validity" />
                                
                        </div>
                    </div>
                    <div style="display: flex" ;>
                        <div class="form-group col-xs-8" style="padding-left: 0 !important;">
                            <?= lang("Product_Rank", "Product Rank") ?>
                            <input type="number" name="rank" class="product-rank" id="rank"
                                value="<?= isset($_POST['rank']) ? $_POST['rank'] : ($product ? $product->rank : '') ?>"
                                min="0" >
                        </div>
                        <div class="form-group col-xs-4">
                            <!-- <input type="checkbox" class="checkbox" value="1" name="flag_visible"
                                <?= $this->input->post('flag_visible') ? 'checked="checked"' : ''; ?>> -->
                            <input type="checkbox" class="checkbox" value="1" name="flag_visible" checked="checked">
                            <label for="flag_visible" class="padding05">
                                <?= lang("Flag_Visible", "Flag Visible"); ?>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("Shelf_Life", "shelf_life") ?>
                                <?= form_input('shelf_life', (isset($_POST['shelf_life']) ? $_POST['shelf_life'] : ($product ? $product->shelf_life : '')), 'class="form-control" id="shelf_life" autocomplete="off" '); ?>
                            </div>
                        </div>
                    </div>


                    <div class="form-group all">
                        <?= lang("Season", "Season") ?>
                        <?php
                            $brnd1[''] = "";
                            if(!empty($seasons) && is_array($seasons)) {
                                foreach ($seasons as $season) {
                                    $brnd1[$season->id] = $season->SeasonName;
                                }
                            }
                            echo form_dropdown('season', $brnd1, (isset($_POST['season']) ? $_POST['season'] : ($product ? $product->season : '')), 'class="form-control select" id="season" placeholder="' . lang("select") . " " . lang("season") . '" style="width:100%"')
                        ?>
                    </div>
                    
                       
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion" <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                        <label for="promotion" class="padding05">
                            <?= lang('promotion'); ?>
                        </label>
                    </div>
                   

                    <div id="promo" style="display:none;">
                        <div class="well well-sm">
                            <div class="form-group">
                                <?= lang('promo_price', 'promo_price'); ?> 
                                <?= form_input('promo_price', set_value('promo_price'), 'class="form-control tip custom_price" id="promo_price" required="required"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?= form_input('start_date', set_value('start_date'), 'class="form-control tip datetime" id="start_date"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?= form_input('end_date', set_value('end_date'), 'class="form-control tip datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($Settings->tax1) { ?>
                        <div class="row">
                            <div class="col-md-8">
                                <!-- <div class="form-group all" >
                                    <?= lang("product_tax", "tax_rate") ?>
                                    <?php
                                    $tx[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        if($tax->is_substitutable == 0) {
                                            $tx[$tax->id] = $tax->name;
                                        }
                                    }
                                    // echo form_dropdown('tax_rate', $tx, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : ($Settings->pos_type == 'restaurant'?15:$Settings->default_tax_rate))), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                                    echo form_dropdown('tax_rate',$tx,(isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : ($Settings->pos_type == 'restaurant' ? 15 : null))),'class="form-control select" placeholder="Category tax" style="width:100%"');
                                    ?>
                                </div> -->
                                 <div class="form-group all" >
                                    <?= lang("product_tax", "tax_rate") ?>
                                    <?php
                                    $tx[""] = "Category Tax"; 
                                    foreach ($tax_rates as $tax) {
                                        if($tax->is_substitutable == 0) {
                                            $tx[$tax->id] = $tax->name;
                                        }
                                    }
                                    $selected_tax = (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : ($Settings->pos_type == 'restaurant' ? 15 : null)));
                                    echo form_dropdown('tax_rate',$tx,$selected_tax,'class="form-control select" placeholder="' . lang("select") . ' ' . lang("tax") . '" style="width:100%"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                               <div class="form-group all"  >
                                   <?= lang("tax_method", "tax_method") ?>
                                   <?php
                                   $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                   echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                   ?>
                               </div>
                            </div>
                        </div>
                    <?php } ?>
                   

                    <div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>

                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile[]" multiple="true" data-show-upload="false"
                               data-show-preview="false" class="form-control file" accept="image/*">
                    </div>
                    <div id="img-details"></div>


                    <!--- Restaurant Type POS  ---->                    
                    <?php if ($Settings->pos_type == 'restaurant' || $Settings->pos_type == 'bakery') { ?>
                        <?php if ($Settings->product_external_platform == '1') { ?>
                            <!--- Urbanpiper  ----> 
                            <div class="form-group all">
                                <?= lang("Used By External Platform (Ex. Zomato, Swiggy, Etc.)", "UrbanPiper Products") ?> 
                                <select class="form-control" name="up_items" id="urbanpiperitem">
                                    <option value="1">Yes</option>
                                    <option selected="selected" value="0"> No </option>
                                </select>     
                            </div>                     
                            <div id="urbanpipercontain" style="display:none">
                                <fieldset style=" border: 1px solid #ccc; padding: 5px;">
                                    <legend style="width: auto; padding: 0px 10px; border-bottom: 0;margin-bottom: 0px;">External Platform Options </legend>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Price ", "Price") ?>
                                                <input  class="form-control" type="number" name="upprice" id="upprice" />                                 
                                            </div> 
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Food Type ", "Food Type") ?>
                                                <select class="form-control" name="up_food_type" id="up_food_type">
                                                    <option value="">Select  </option>
                                                    <?php foreach ($foodtype as $foodtype_value) { ?>
                                                        <option value="<?= $foodtype_value->id ?>"><?= $foodtype_value->food_type ?></option>
                                                    <?php } ?>
                                                </select>    
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Is Available", "Is Available") ?>
                                                <select class="form-control" name="available" id="available">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>    
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Sold At Store", "Sold At Store") ?>
                                                <select class="form-control" name="sold_at_store" id="sold_at_store">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>    
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Is Recommended", "Is Recommended") ?>
                                                <select class="form-control" name="recommended" id="recommended">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>    
                                            </div>
                                        </div>                                
                                        <div class="col-sm-6">
                                           <div class="form-group ">
                                                <?= lang("Manage_stock", "Manage_stock") ?>
                                                <select class="form-control" name="manage_stock" id="manage_stock">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>    
                                            </div>
                                        </div>
                                    </div>                          
                                    <div class="row">

                                         <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Default ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <select class="form-control" name="default_tag" id="default_tag" data-role="tagsinput">
                                                    <option value="packaged-good"> Packaged Good</option> 
                                                </select>      
                                             
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Zomato ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_zomato" id="tag_zomato" data-role="tagsinput" />
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Swiggy ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_swiggy" id="tag_swiggy" data-role="tagsinput" />
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Food Panda ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                          -                      <input class="form-control" type="text" name="tag_foodpanda" id="tag_foodpanda" data-role="tagsinput" />
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Uber Eats ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_ubereats" id="tag_ubereats" data-role="tagsinput" />
                                            </div>
                                        </div>
                                    </div>                          

                                </fieldset>    
                                <br>
                            </div>   
                        <?php } ?> 
                        <!---- End Urbanpiper ----->
                    <?php } ?>
                    <!--- End Restaurant Type POS  ----> 
                </div>
                
                <!-----vcvcvcvbcv-->
                <div class="col-md-8">
                    <div class="standard">

                        
                        <div class="clearfix"></div>
                        <div class="clearfix"></div>

                    </div>
                    

                    


                        <!--                    <div class="form-group standard">
                        <div class="form-group">
                            < ?= lang("supplier", "supplier") ?>
                            <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i class="fa fa-plus"></i>
                            </button>
                        </div>-->
                        <!--
                        <div class="col-xs-12">
                                <div class="form-group">
                        < ?php
                        //echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                        ?>
                                </div>
                        </div>
                        -->
                        <!--                        <div class="row" id="supplierrow_1">  supplier_con
                            <div>
                                <div class="col-xs-11">
                                    <div class="form-group">
                                        < ?php
                                        echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-xs" >
                                    <i class="fa fa-times deleteSupplier"  id="1"  style="cursor:pointer;"></i>
                                </button>
                            </div>    
                            <div class="col-xs-6">
                                <div class="form-group">
                                    < ?= form_input('supplier_part_no', (isset($_POST['supplier_part_no']) ? $_POST['supplier_part_no'] : ""), 'class="form-control tip" id="supplier_part_no" placeholder="' . lang('supplier_part_no') . '"'); ?>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    < ?= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ""), 'class="form-control tip" id="supplier_price" placeholder="' . lang('supplier_price') . '"'); ?>
                                </div>
                            </div>
                        </div>
                        <div id="ex-suppliers"></div>
                    </div>-->


                </div>


                

                <div class="col-md-12">
                    <?php                    
                    $active_cf = FALSE;
                    for($i=1; $i<=6; $i++){
                        if($custome_fields->{"cf$i"}){
                            $active_cf = TRUE;
                            break;
                        }
                    }                     
                    ?>
                    <div class="form-group">
                        <input name="cf" type="checkbox" class="checkbox" id="extras" value="1" <?= ($active_cf) ? 'checked="checked" disabled="disabled"' : '' ?> />
                        <label for="extras" class="padding05"><?= lang('custom_fields') ?> <small><a href="<?=base_url('system_settings/custom_fields');?>">(Change Custome Fields Name)</a></small></label>
                    </div>                    
                    <div class="row" id="extras-con" style="<?= ($active_cf) ? 'display: block;' : 'display: none;' ?>">
                         
                        <div class="col-md-4">                             
                            <div class="form-group all">                         
                                <?php echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'pcf1') : lang('pcf1', 'pcf1')) ?>                                
                                <?php                        
                                if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {                            
                                    echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)) , '', 'class="form-control tip" id="cf1"'. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf1', '', 'class="form-control" id="cf1" '. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); 
                                }
                                ?>                        
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'pcf2') : lang('pcf2', 'pcf2')) ?> 
                               <?php                        
                                if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {                            
                                    echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)) , '', 'class="form-control tip" id="cf2"'. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf2', '', 'class="form-control" id="cf2" '. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                }  ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'pcf3') : lang('pcf3', 'pcf3')) ?>
                                <?php                        
                                if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {                            
                                    echo form_dropdown('cf3', (json_decode($custome_fields->cf3_input_options, TRUE)) , '', 'class="form-control tip" id="cf3"'. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf3', '', 'class="form-control" id="cf3" '. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); 
                                }    
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'pcf4') : lang('pcf4', 'pcf4')) ?>
                                <?php                        
                                if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {                            
                                    echo form_dropdown('cf4', (json_decode($custome_fields->cf4_input_options, TRUE)) , '', 'class="form-control tip" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf4', '', 'class="form-control" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); 
                                }   
                                ?> 
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'pcf5') : lang('pcf5', 'pcf5')) ?>
                                <?php                        
                                if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {                            
                                    echo form_dropdown('cf5', (json_decode($custome_fields->cf5_input_options, TRUE)) , '', 'class="form-control tip" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf5', '', 'class="form-control" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); 
                                }    
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'pcf6') : lang('pcf6', 'pcf6')) ?>
                                <?php                        
                                if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {                            
                                    echo form_dropdown('cf6', (json_decode($custome_fields->cf6_input_options, TRUE)) , '', 'class="form-control tip" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf6', '', 'class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); 
                                }   
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group all">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="details"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details_for_invoice", "details") ?>
                        <?= form_textarea('details', (isset($_POST['details']) ? $_POST['details'] : ($product ? $product->details : '')), 'class="form-control" id="details"'); ?>
                    </div>

                    <div class="form-group">
                        <button type="button" id="custom-submit" class="btn btn-primary">Add Product</button>
                    </div>

                </div>
                <?= form_close(); ?>

            </div>

        </div>
    </div>
</div>
<div class="modal  modalvarient" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="modalClose('modalvarient')" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modal title</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="modalClose('modalvarient')" class="btn btn-default"
                        data-toggle="modal">Close</button>
                    <!--button type="button" class="btn btn-primary" onclick="addProductToVarientProduct('modalvarient')">Save changes</button -->
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
    $(document).ready(function () {
    $.ajaxSetup({
        beforeSend: function (jqXHR, settings) {
            if (settings.url.includes("products/suggestions")) {
                const type = $('#type').val();
                const storageType = $('#storage_type').val();
                const url = new URL(settings.url, window.location.origin);
                url.searchParams.set('type', type);
                url.searchParams.set('storage_type', storageType);
                settings.url = url.toString();
            }
        }
    });
});
    $(document).ready(function () {
        var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
        var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
        // var items = {};
<?php
if ($combo_items) {
    foreach ($combo_items as $item) {
        //echo 'ietms['.$item->id.'] = '.$item.';';
        if ($item->code) {
            echo 'add_product_item(' . json_encode($item) . ');';
        }
    }
}
?>
<?= isset($_POST['cf']) ? '$("#extras").iCheck("check");' : '' ?>
        $('#extras').on('ifChecked', function () {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function () {
            $('#extras-con').slideUp();
        });

<?= isset($_POST['promotion']) ? '$("#promotion").iCheck("check");' : '' ?>
        $('#promotion').on('ifChecked', function (e) {
            $('#promo').slideDown();
        });
        $('#promotion').on('ifUnchecked', function (e) {
            $('#promo').slideUp();
            $('#promo_price').removeAttr('required');
            $('#add_product').removeAttr('disabled');
        });

        $('.attributes').on('ifChecked', function (event) {
            $('#options_' + $(this).attr('id')).slideDown();
        });
        $('.attributes').on('ifUnchecked', function (event) {
            $('#options_' + $(this).attr('id')).slideUp();
        });
        //$('#cost').removeAttr('required');
        $('#type').change(function () {
            $("#prTable tbody").empty();
            var t = $(this).val();
            $('.bundel').show();

            if (t !== 'standard') {
                $('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('#track_quantity').iCheck('uncheck');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
                $('#track_quantity').iCheck('check');
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#digital_file').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
            } else {
                $('.digital').slideDown();
                $('#digital_file').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
            }
            if (t !== 'combo' ) {
                $('.combo').slideUp();
            } else {
                $("#prTable tbody").empty();
                $('.combo').slideDown();
            }
            if (t == 'Bundle') {
                $("#prTable tbody").empty();
                $('.combo').slideDown();
                $('.bundel').hide();
            } 
        });

        var t = $('#type').val();
        if (t !== 'standard') {
            $('.standard').slideUp();
            $('#cost').attr('required', 'required');
            $('#track_quantity').iCheck('uncheck');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
        } else {
            $('.standard').slideDown();
            $('#track_quantity').iCheck('check');
            $('#cost').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
        }
        if (t !== 'digital') {
            $('.digital').slideUp();
            $('#digital_file').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
        } else {
            $('.digital').slideDown();
            $('#digital_file').attr('required', 'required');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
        }
        if (t !== 'combo') {
            $('.combo').slideUp();
        } else {
            $('.combo').slideDown();
        }
        if (t == 'Bundle') {
                $('.combo').slideDown();
            } 
            $("#add_item").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.options) {
                    product_option_model_call(ui.item);
                    $(this).val('');
                    return true;
                }
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });

// <?php
// if ($this->input->post('type') == 'combo') {
//     $c = sizeof($_POST['combo_item_code']);
//     for ($r = 0; $r <= $c; $r++) {
//         if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
//             $items[] = array('id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'price' => $_POST['combo_item_price'][$r]);
//         }
//     }
//     echo '
//             var ci = ' . json_encode($items) . ';
//             $.each(ci, function() { add_product_item(this); });
//             ';
// }
// if ($this->input->post('type') == 'Bundle') {
//     $c = sizeof($_POST['combo_item_code']);
//     for ($r = 0; $r <= $c; $r++) {
//         if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
//             $items[] = array('id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'price' => $_POST['combo_item_price'][$r]);
//         }
//     }
//     echo '
//             var ci = ' . json_encode($items) . ';
//             $.each(ci, function() { add_product_item(this); });
//             ';
// }
// ?>
        // function add_product_item(item) {
        //     if (item == null) {
        //         return false;
        //     }
        //     item_id = item.id;
        //     if (items[item_id]) {
        //         items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
        //     } else {
        //         items[item_id] = item;
        //     }
        //     var pp = 0;
        //     $("#prTable tbody").empty();
        //     $.each(items, function () {
        //         var selectedValue = document.getElementById("type").value;
        //         var row_no = this.id;
        //         var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
        //         tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.name + ' (' + this.code + ')</span></td>';
        //         tr_html += '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
        //         if (selectedValue != "Bundle") {
        //             tr_html += '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
        //             tr_html += '<td><input class="form-control text-center rtprice" name="combo_item_total_price[]" type="text" value="' + (formatDecimal(this.price) * formatDecimal(this.qty)) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_total_price_' + row_no + '" onClick="this.select();"></td>';
        //         }
        //         tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
        //         newTr.html(tr_html);
        //         newTr.prependTo("#prTable");
        //         pp += formatDecimal(parseFloat(this.price) * parseFloat(this.qty));
        //     });
        //     $('.item_' + item_id).addClass('warning');
        //     $('#price').val(pp);
        //     $('#cost').val(pp);
        //     return true;
        // }

        function calculate_price() {

            var rows = $('#prTable').children('tbody').children('tr');
            var pp = 0;
            var row_total = 0
            $.each(rows, function () {
                row_total = formatDecimal(parseFloat($(this).find('.rprice').val()) * parseFloat($(this).find('.rquantity').val()));
                $(this).find('.rtprice').val(row_total);
                //console.log(this);
                //alert(row_total);
                pp += row_total;
            });
            //console.log(pp);
            $('#price').val(pp);
            return true;
        }

        $(document).on('change textchange', '.rquantity, .rprice', function () {
            calculate_price();
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete items[id];
            $(this).closest('#row_' + id).remove();
            calculate_price();
        });
        var su = 2;
        /*
         $('#addSupplier').click(function () {
         if (su <= 5) {
         $('#supplier_1').select2('destroy');
         var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>" style="width:100%;display: block !important;" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_part_no" class="form-control tip" id="supplier_' + su + '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
         $('#ex-suppliers').append(html);
         var sup = $('#supplier_' + su);
         suppliers(sup);
         su++;
         } else {
         bootbox.alert('<?= lang('max_reached') ?>');
         return false;
         }
         });*/

        $('#addSupplier').click(function () {//onClick="delete_supplier(' + su + ')"
            if (su <= 5) {
                //$('#supplier_1').select2('destroy');//style="width:100%;display: block !important;"
                var html = '<div style="clear:both;height:5px;" ></div><div class="row" id="supplierrow_' + su + '"><div><div class="col-xs-11"><div class="form-group"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>"  /></div></div><div><button type="button" class="btn btn-primary btn-xs" ><i class="fa fa-times deleteSupplier"  id="' + su + '"  style="cursor:pointer;"></i></button></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_part_no" class="form-control tip" id="supplier_' + su + '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
                $('#ex-suppliers').append(html);
                var sup = $('#supplier_' + su);
                suppliers(sup);
                su++;
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
        });


        $(document).on('click', '.deleteSupplier', function () {
            var id = $(this).attr('id');
            console.log(id);
            su--;
            $(this).closest('#supplierrow_' + id).remove();
        });

        var _URL = window.URL || window.webkitURL;
        $("input#images").on('change.bs.fileinput', function () {
            var ele = document.getElementById($(this).attr('id'));
            var result = ele.files;
            $('#img-details').empty();
            for (var x = 0; x < result.length; x++) {
                var fle = result[x];
                for (var i = 0; i <= result.length; i++) {
                    var img = new Image();
                    img.onload = (function (value) {
                        return function () {
                            ctx[value].drawImage(result[value], 0, 0);
                        }
                    })(i);
                    img.src = 'images/' + result[i];
                }
            }
        });
        var variants = <?= json_encode($vars); ?>;
        $(".select-tags").select2({
            tags: variants,
            tokenSeparators: [","],
            multiple: true
        });
        $(document).on('ifChecked', '#attributes', function (e) {
            $('#attr-con').slideDown();
        $('#price').val('0');
        $('#mrp').val('0');
        $('#discount_on_mrp').val('0%');
        $('#cost').val('0');
        $('#price').attr('readonly', true);
        $('#mrp').attr('readonly', true);
        $('#cost').attr('readonly', true);
        $('#discount_on_mrp').attr('readonly', true);
        });
        $(document).on('ifUnchecked', '#attributes', function (e) {
            $(".select-tags").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();

            $('#price').attr('readonly', false);
            $('#mrp').attr('readonly', false);
            $('#cost').attr('readonly', false);
            $('#discount_on_mrp').attr('readonly', false);
        });
        var existvariants = <?= json_encode($vars); ?>;

$('#addAttributes').click(function (e) {
    e.preventDefault();
    var attrs_val = $('#attributesInput').val(), attrs;
    attrs = attrs_val.split(',').map(s => s.trim()).filter(s => s !== '');
    
    var wh_arr = [];
    var attr_arr = [];
    if ($.trim($('#attrTable tbody').html()) != '') {
        $.each($(".attr_name"), function (index, ele) {
            attr_arr.push(ele.value);
        });
    }

    var wh_arr_unique = $.unique(wh_arr);
    var attr_arr_unique = $.unique(attr_arr);

    var validAttrs = [];
    var invalidAttrs = [];

    for (var i in attrs) {
        var attrTrimmed = $.trim(attrs[i]);
        var allowed = false;
        for (var j in existvariants) {
            if (existvariants[j].toLowerCase() === attrTrimmed.toLowerCase()) {
                allowed = true;
                break;
            }
        }

        if (!allowed) {
            invalidAttrs.push(attrTrimmed);
        } else {
            validAttrs.push(attrTrimmed);
        }
    }

    if (invalidAttrs.length > 0) {
        bootbox.alert('"' + invalidAttrs.join(', ') + '" is not a valid variant. Please add it in the variants screen first.');
    }

    for (var i in validAttrs) {
        var attrTrimmed = validAttrs[i];

        if (attrTrimmed !== '' && $.inArray(attrTrimmed, attr_arr_unique) === -1) {
            <?php if ($Settings->pos_type == 'restaurant') { ?>
                $('#attrTable').show().append(
                    '<tr class="attr">' +
                        '<td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrTrimmed + '"><span>' + attrTrimmed + '</span></td>' +
                        '<td class="cost text-right">' +
                            '<input type="number" class="form-control cost text-right" name="attr_cost[]" value="0" oninput="updateHiddenInput(this, \'attr_cost\')">' +
                        '</td>' +
                        '<td class="variantmrp text-right">' +
                            '<input type="number" class="form-control variantmrp text-right" name="attr_mrp[]" value="0" oninput="updateHiddenInput(this, \'attr_mrp\')">' +
                        '</td>' +
                        '<td class="price text-right" id="variant_price">' +
                            '<input type="number" class="form-control variantprice text-right" name="attr_price[]" value="0" oninput="updateHiddenInput(this, \'attr_price\')">' +
                        '</td>' +
                        '<td class="variantdiscount text-right discount-on-mrp-col">' +
                            '<input type="text" class="form-control variantdiscount text-right" name="attr_discount[]" value="0%" oninput="updateHiddenInput(this, \'attr_discount\')">' +
                        '</td>' +
                        '<td class="upprice text-right">' +
                            '<input type="number" class="form-control upprice text-right" name="attr_upprice[]" value="0" oninput="updateHiddenInput(this, \'attr_upprice\')">' +
                        '</td>' +
                        '<td class="unit_quantity text-right">' +
                            '<input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" oninput="updateHiddenInput(this, \'attr_unit_quantity\')">' +
                        '</td>' +
                        '<td class="unit_quantity text-right">' +
                            '<input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="0" oninput="updateHiddenInput(this, \'attr_unit_weight\')">' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<i class="fa fa-times delAttr"></i>' +
                        '</td>' +
                    '</tr>'
                );
            <?php } else { ?>
                $('#attrTable').show().append(
                    '<tr class="attr">' +
                        '<td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrTrimmed + '"><span>' + attrTrimmed + '</span></td>' +
                        '<td class="cost text-right">' +
                            '<input type="number" class="form-control cost text-right" name="attr_cost[]" value="0" min="0" oninput="updateHiddenInput(this, \'attr_cost\')">' +
                        '</td>' +
                        '<td class="variantmrp text-right" id="variant_mrp">' +
                            '<input type="number" class="form-control variantmrp text-right" name="attr_mrp[]" value="0" min="0" oninput="updateHiddenInput(this, \'attr_mrp\')">' +
                        '</td>' +
                        '<td class="price text-right" id="variant_price">' +
                            '<input type="number" class="form-control variantprice text-right" name="attr_price[]" value="0" min="0" oninput="updateHiddenInput(this, \'attr_price\')">' +
                        '</td>' +
                        '<td class="variantdiscount text-right discount-on-mrp-col" id="variant_discount">' +
                            '<input type="text" class="form-control variantdiscount text-right" name="attr_discount[]" value="0%" min="0" oninput="updateHiddenInput(this, \'attr_discount\')">' +
                        '</td>' +
                        '<td class="unit_quantity text-right">' +
                            '<input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" min="0" oninput="updateHiddenInput(this, \'attr_unit_quantity\')">' +
                        '</td>' +
                        '<td class="unit_weight text-right">' +
                            '<input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="0" min="0" oninput="updateHiddenInput(this, \'attr_unit_weight\')">' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<i class="fa fa-times delAttr"></i>' +
                        '</td>' +
                    '</tr>'
                );
            <?php } ?>
        }
    }

        var currentValues = $(".select-tags").val() || [];
        var newValues = [...new Set([...currentValues, ...validAttrs])];
        $(".select-tags").val(newValues).trigger("change");
    });
//$('#attributesInput').on('select2-blur', function(){
//    $('#addAttributes').click();
//});
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
            //   $('#awarehouse').select2("val", (row.children().eq(1).find('input').val()));
            //   $('#aquantity').val(row.children().eq(2).find('input').val());
            $('#acost').val(row.children().eq(1).find('input').val()); 
            $('#amrp').val(row.children().eq(2).find('input').val());

           $('#aprice').val(row.children().eq(3).find('input').val());
           $('#adiscount').val(row.children().eq(4).find('input').val());

           <?php if ($Settings->pos_type == 'restaurant') { ?>
                $('#aupprice').val(row.children().eq(5).find('input').val());
                $('#aunit_quantity').val(row.children().eq(6).find('input').val());
                $('#aunit_weight').val(row.children().eq(7).find('input').val());
            <?php }else{ ?>
             $('#aunit_quantity').val(row.children().eq(5).find('input').val());
             $('#aunit_weight').val(row.children().eq(6).find('input').val());
            <?php } ?>

            //$('#aunit_quantity').val(row.children().eq(3).find('input').val());
            
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
           
            // row.children().eq(1).html('<input type="text-right " name="attr_cost[]" value="' + $('#acost').val() + '">');
            // row.children().eq(2).html('<input type="text-right" name="attr_price[]" value="' + $('#aprice').val() + '">');
            
            row.children().eq(1).html('<input type="text" class="text-right" name="attr_cost[]" value="' + $('#acost').val() + '" size="1">');
            row.children().eq(2).html('<input type="text" class="text-right" name="attr_mrp[]" value="' + $('#amrp').val() + '" size="1">');
            row.children().eq(3).html('<input type="text" class="text-right" name="attr_price[]" value="' + $('#aprice').val() + '" size="1">');
            row.children().eq(4).html('<input type="text" class="text-right" name="attr_discount[]" value="' + $('#adiscount').val() + '" size="1">');

            <?php if ($Settings->pos_type == 'restaurant') { ?>
                                row.children().eq(5).html('<input type="text" name="attr_upprice[]" value="' + $('#aupprice').val() + '"size="1">');
                                row.children().eq(6).html('<input type="text" name="attr_unit_quantity[]" value="' + $('#aunit_quantity').val() + '"size="1">');
                row.children().eq(7).html('<input type="text" name="attr_unit_weight[]" value="' + $('#aunit_weight').val() + '"size="1">');
                            <?php }else{ ?>
                                row.children().eq(5).html('<input type="text" name="attr_unit_quantity[]" value="' + $('#aunit_quantity').val() + '"size="1">');
                row.children().eq(6).html('<input type="text" name="attr_unit_weight[]" value="' + $('#aunit_weight').val() + '"size="1">');
                            <?php } ?> 
                    ///row.children().eq(3).html('<input type="hidden" name="attr_unit_quantity[]" value="' + $('#aunit_quantity').val() + '"><span>' + ($('#aunit_quantity').val()) + '</span>');
                        
            $('#aModal').modal('hide');

        });
    });

<?php if ($product) { ?>
        $(document).ready(function () {
            var t = "<?= $product->type ?>";
            if (t !== 'standard') {
                $('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('#track_quantity').iCheck('uncheck');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
                $('#track_quantity').iCheck('check');
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#digital_file').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
            } else {
                $('.digital').slideDown();
                $('#digital_file').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
            }
            if (t !== 'combo') {
                $('.combo').slideUp();
                //$('#add_item').removeAttr('required');
                //$('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
            } else {
                $('.combo').slideDown();
                //$('#add_item').attr('required', 'required');
                //$('form[data-toggle="validator"]').bootstrapValidator('addField', 'add_item');
            }
            if (t == 'Bundle') {
                $('.combo').slideDown();

            } 
            $("#code").parent('.form-group').addClass("has-error");
            $("#code").focus();
            $("#product_image").parent('.form-group').addClass("text-warning");
            $("#images").parent('.form-group').addClass("text-warning");
            $.ajax({
                type: "get", async: false,
                url: "<?= site_url('products/getSubCategories') ?>/" + <?= $product->category_id ?>,
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
                }
            });
    <?php if ($product->supplier1) { ?>
                select_supplier('supplier1', "<?= $product->supplier1; ?>");
                $('#supplier_price').val("<?= $product->supplier1price == 0 ? '' : $this->sma->formatDecimal($product->supplier1price); ?>");
    <?php } ?>
    <?php if ($product->supplier2) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_2', "<?= $product->supplier2; ?>");
                $('#supplier_2_price').val("<?= $product->supplier2price == 0 ? '' : $this->sma->formatDecimal($product->supplier2price); ?>");
    <?php } ?>
    <?php if ($product->supplier3) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_3', "<?= $product->supplier3; ?>");
                $('#supplier_3_price').val("<?= $product->supplier3price == 0 ? '' : $this->sma->formatDecimal($product->supplier3price); ?>");
    <?php } ?>
    <?php if ($product->supplier4) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_4', "<?= $product->supplier4; ?>");
                $('#supplier_4_price').val("<?= $product->supplier4price == 0 ? '' : $this->sma->formatDecimal($product->supplier4price); ?>");
    <?php } ?>
    <?php if ($product->supplier5) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_5', "<?= $product->supplier5; ?>");
                $('#supplier_5_price').val("<?= $product->supplier5price == 0 ? '' : $this->sma->formatDecimal($product->supplier5price); ?>");
    <?php } ?>
            function select_supplier(id, v) {
                $('#' + id).val(v).select2({
                    minimumInputLength: 1,
                    data: [],
                    initSelection: function (element, callback) {
                        $.ajax({
                            type: "get", async: false,
                            url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                            dataType: "json",
                            success: function (data) {
                                callback(data[0]);
                            }
                        });
                    },
                    ajax: {
                        url: site.base_url + "suppliers/suggestions",
                        dataType: 'json',
                        quietMillis: 15,
                        data: function (term, page) {
                            return {
                                term: term,
                                limit: 10
                            };
                        },
                        results: function (data, page) {
                            if (data.results != null) {
                                return {results: data.results};
                            } else {
                                return {results: [{id: '', text: 'No Match Found'}]};
                            }
                        }
                    }
                });//.select2("val", "<?= $product->supplier1; ?>");
            }

            var whs = $('.wh');
            $.each(whs, function () {
                $(this).val($('#r' + $(this).attr('id')).text());
            });
        });
<?php } ?>
    $(document).ready(function () {

       <?php if($product){ ?> 
            productunit('<?= $product->unit ?>'); 
        <?php } ?> 


        $('#unit').change(function (e) {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubUnits') ?>/" + v,
                    dataType: "json",
                    success: function (data) {
                        $('#default_sale_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $('#default_purchase_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $.each(data, function () {
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_sale_unit'));
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_purchase_unit'));
                        });
                        $('#default_sale_unit').select2('val', v);
                        $('#default_purchase_unit').select2('val', v);
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $('#default_sale_unit').select2("destroy").empty();
                $('#default_purchase_unit').select2("destroy").empty();
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_sale_unit'));
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_purchase_unit'));
                $('#default_sale_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
                $('#default_purchase_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
            }
        });
    });


     function productunit(v){
       
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubUnits') ?>/" + v,
                    dataType: "json",
                    success: function (data) {
                        $('#default_sale_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $('#default_purchase_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $.each(data, function () {
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_sale_unit'));
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_purchase_unit'));
                        });
                        $('#default_sale_unit').select2('val', v);
                        $('#default_purchase_unit').select2('val', v);
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $('#default_sale_unit').select2("destroy").empty();
                $('#default_purchase_unit').select2("destroy").empty();
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_sale_unit'));
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_purchase_unit'));
                $('#default_sale_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
                $('#default_purchase_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
            }
        
    }

    var items = {};
    <?php
if ($this->input->post('type') == 'combo') {
    $c = sizeof($_POST['combo_item_code']);
    for ($r = 0; $r <= $c; $r++) {
        if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
            $items[] = array('id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'price' => $_POST['combo_item_price'][$r]);
        }
    }
    echo '
            var ci = ' . json_encode($items) . ';
            $.each(ci, function() { add_product_item(this); });
            ';
}
if ($this->input->post('type') == 'Bundle') {
    $c = sizeof($_POST['combo_item_code']);
    for ($r = 0; $r <= $c; $r++) {
        if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
            $items[] = array('id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'price' => $_POST['combo_item_price'][$r]);
        }
    }
    echo '
            var ci = ' . json_encode($items) . ';
            $.each(ci, function() { add_product_item(this); });
            ';
}
?>
    function add_product_item(item) {
    if (item == null) {
        
        return false;
    }
    
    item_id = item.id;
    if (items[item_id]) {
        
        items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
    } else {
        
        items[item_id] = item;
    }
    var pp = 0;
    var mp = 0;
    var ct = 0;
    $("#prTable tbody").empty();
    $.each(items, function() {
       
        var selectedValue = document.getElementById("type").value;
        var row_no = this.id;
        var displayName = this.name + ' (' + this.code + ')';
        if (this.product_name) {
            displayName = this.product_name + ' (' + this.name + ') (' + this.code + ')';
        }
        var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no +
            '"></tr>');
        tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id +
            '"><input name="combo_item_name[]" type="hidden" value="' + this.name +
            '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' +
            row_no + '">' + displayName + '</span></td>';
        tr_html +=
            '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' +
            formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' +
            row_no + '" onClick="this.select();"></td>';
        if (selectedValue != "Bundle") {
            tr_html +=
                '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' +
                formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id +
                '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';  
            tr_html +=
                '<td><input class="form-control text-center rtprice" name="combo_item_total_price[]" type="text" value="' +
                (formatDecimal(this.price) * formatDecimal(this.qty)) + '" data-id="' + row_no +
                '" data-item="' + this.id + '" id="combo_item_total_price_' + row_no +
                '" onClick="this.select();"></td>';
                
        }
        tr_html += '<input name="combo_item_variant_id[]" type="hidden" value="' + (this.variant_id ? this
            .variant_id : '') + '">';
        tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no +
            '" title="Remove" style="cursor:pointer;"></i></td>';
        newTr.html(tr_html);
        newTr.prependTo("#prTable");
        pp += formatDecimal(parseFloat(this.price) * parseFloat(this.qty));
        mp += formatDecimal(parseFloat(this.mrp) * parseFloat(this.qty));
         ct += formatDecimal(parseFloat(this.cost) * parseFloat(this.qty));
         var discount = mp > 0 ? (((mp - pp) / mp) * 100) : 0;


        });
        $('.item_' + item_id).addClass('warning');
        $('#price').val(pp);
        $('#cost').val(ct);
        $('#mrp').val(mp);
        var discount = mp > 0 ? (((mp - pp) / mp) * 100) : 0;
        $('#discount_on_mrp').val(Math.round(discount) + '%');

    $('#prTable').off('input', '.rprice, .rquantity').on('input', '.rprice, .rquantity', function () {
        let total = 0;
        $('#prTable tbody tr').each(function () {
            let row = $(this);
            let price = parseFloat(row.find('.rprice').val()) || 0;
            let mrp = parseFloat(row.find('.rmrp').val()) || 0;
            let cost = parseFloat(row.find('.rcost').val()) || 0;
            let qty = parseFloat(row.find('.rquantity').val()) || 0;
            let lineTotal = (price * qty).toFixed(2);
            row.find('.rtprice').val(lineTotal);
            total += parseFloat(lineTotal);
            totalMrp += mrp * qty;
            totalCost += cost * qty;
        });

        total = formatDecimal(total);
        $('#price').val(total);
        $('#cost').val(formatDecimal(totalCost));
        $('#mrp').val(formatDecimal(totalMrp));
        $('#discount_on_mrp').val('0%');
    });

    return true;
}
        function addProductToVarientProduct(option_id, option_name) {
            var note = '';
            if (option_name.toLowerCase() == 'note') {

                note = prompt("Please enter your note");
                if (note == null) {
                    return false;
                }
            }
            var itemId = $(".modalvarient").find('.product_item_id').attr("value")
            var term = $(".modalvarient").find('.product_term').val() +
                "<?php echo $this->Settings->barcode_separator; ?>" + option_id;
            wh = $('#poswarehouse').val(),
                cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?= site_url('products/suggestions') ?>",
                data: {
                    term: term,
                    option_id: option_id,
                    warehouse_id: wh,
                    customer_id: cu,
                    option_note: note
                },
                dataType: "json",
                success: function(data) {
                    if (data !== null) {
                        add_product_item(data[0]);
                        $(this).val('');
                        $('.modalvarient').hide();
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                        $('.modalvarient').hide();
                    }
                }
            });
        }
        
    function product_option_model_call(product) {
        var subdomain = new URL(window.location.href).hostname.split('.')[0];
        var product_options = '';
        product_options = "" +
            "<div class='row'>" +
            "<div class='col-sm-12'>";
            var optionsArray = Object.values(product.options);
            console.log("Options Array:", optionsArray);
            $.each(optionsArray, function(index, element) {
            if (element.name.toLowerCase() == 'note') {
                product_options +=
                    '</div><div style="clear:both"></div></div><div class="note-btn"><button onclick="addProductToVarientProduct(\'' +
                    element.id + '\',\'' + element.name +
                    '\')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>Note</button></div>';
            } else {
                product_options += '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' +
                    element.name + '\')" type="button"  title="' + element.name +
                    '" class="btn-prni btn-info pos-tip" tabindex="-1"><img src="assets/mdata/' + subdomain +
                    '/uploads/thumbs/no_image.png" alt="' + element.name +
                    '" style="width:33px;height:33px;" class="img-rounded"><span>' + element.name +
                    '</span></button>';
            }
        });
        product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.id + "' >";
        product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.code +
            "' >";
        $('.modalvarient').find('.modal-title').html(product.name);
        $('.modalvarient').find('.modal-body').empty();
        $('.modalvarient').find('.modal-body').append(product_options);
        $('.modalvarient').show();
        return true;
    }
    function modalClose(modalClass) {
        $('.' + modalClass).hide();
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
                        <label for="amrp" class="col-sm-4 control-label"><?= lang('MRP') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="amrp" onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('Price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice" min="0" onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                    </div>
                    <?php if ($enable_discount_on_mrp) { ?>
                    <div class="form-group discount-on-mrp-col">
                        <label for="adiscount" class="col-sm-4 control-label"><?= lang('Discount') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="adiscount" min="0">
                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;"></span>
                        </div>
                    </div>
                    <?php } ?>
                    
<?php if ($Settings->pos_type == 'restaurant') { ?>
                     <div class="form-group">
                        <label for="aupprice" class="col-sm-4 control-label"><?= lang('Urbanpiper_Price_Addition') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aupprice" onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorup" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                        </div>
                    </div>
                    <?php } ?>
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
<!-- Urbanpiper --->

<script type="text/javascript">
    $('#urbanpiperitem').click(function () {
        let values = $(this).val();
        if (values == '1') {
            $('#urbanpipercontain').show();
        } else {
            $('#urbanpipercontain').hide();
        }
    });
    $(document.activeElement).filter(':select#category:focus').blur();

    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("error2").style.display = ret ? "none" : "inline";
        return ret;
    }

    /****/
    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            document.getElementById("error").style.display = "inline";
            return false;
        }
        document.getElementById("error").style.display = "none";
        return true;
    }

    function isNumberKeyQua(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            document.getElementById("errorq").style.display = "inline";
            return false;
        }
        document.getElementById("errorq").style.display = "none";
        return true;
    }

    function isNumberKeyPrice(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            document.getElementById("errorp").style.display = "inline";
            return false;
        }
        document.getElementById("errorp").style.display = "none";
        return true;
    }
</script> 

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

    $(document).on('focus', '.attr_price', function(e) {
    e.stopPropagation(); // Prevents the modal from opening
});

// Capture the input change
$(document).on('change', '.attr_price', function() {
    var mrpValue = parseFloat($('#mrp').val());
    var newValue = parseFloat($(this).val());
    const priceSpan = $(this).next('span');
    var priceValue = parseFloat($('#price').val());
    var productPrice = newValue + priceValue;
    calculateDiscount(newValue,mrpValue,priceValue);
    priceSpan.text(newValue);
    // if (mrpValue < productPrice) {
    //     alert('MRP is smaller than the total price!');
    //     $('.attr_price').val('00');
    // }
    
});  
function calculateDiscount(priceValue,mrpValue,optionPrice){
    if (!enableDiscountOnMrp) {
        setDiscountOnMrp('0%');
        return;
    }
    if (!isNaN(priceValue) && !isNaN(mrpValue) && mrpValue >= 0) {
         priceValue = optionPrice + priceValue;
        var discountValue, discountType;
            if (mrpValue >= priceValue) {
                if(priceValue == '0'){
                    var calculatedDiscount = 0;
                    setDiscountOnMrp(calculatedDiscount + '%');

                }else{
                    var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                    setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
                }
                // var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                // $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                if (!isNaN(discountValue) && discountValue >= 0) {
                    var calculatedPrice;
                    if (discountType === 'percentage') {
                        calculatedPrice = mrpValue - (mrpValue * (discountValue / 100));
                        setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
                    } else {
                        calculatedPrice = mrpValue - discountValue;
                        setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
                    }
                    $('#price').val(calculatedPrice.toFixed(0));
                }
            } else {
                setDiscountOnMrp('0%');
            }
        } else {
            setDiscountOnMrp('0%');
        }
} 
$(document).on('change', '.attr_price', function() {
    const newValue = $(this).val();
    const priceSpan = $(this).next('span'); // Get the associated span for displaying price

    // Update the span with the new value
    priceSpan.text(newValue);
    
    // Store the value in the modal's hidden input
    $('#aprice').val(newValue); // Set the new value in the modal's price input
    // alert($('#aprice').val());
});


</script>
<script>
$(document).ready(function () {
    $('#mrp, #discount_on_mrp').on('change', function () {
        var priceValue = parseFloat($('#price').val());
        var mrpValue = parseFloat($('#mrp').val());
        var discountInput = $('#discount_on_mrp').val().trim();
        

        mrpValue = isNaN(mrpValue) ? 0 : mrpValue;
        if (isNaN(mrpValue) || mrpValue < 0 || 1 / mrpValue === -Infinity) {
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
            if (discountValue < 0 || 1 / discountValue === -Infinity) {
                    alert("Discount cannot be negative!");
                    setDiscountOnMrp('0');
                    return;
                }
        }

        if (!isNaN(mrpValue) && mrpValue > 0) {
            if (!isNaN(priceValue) && priceValue > 0 && mrpValue >= priceValue) {
                var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                
                // ✅ Update discount field ONLY if it's empty or '0%'
                if (!discountInput || discountInput === '0%') {
                    setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
                }

                if (!isNaN(discountValue) && discountValue >= 0) {
                    var calculatedPrice;

                    // ✅ Valid discount: Apply calculations
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

    // If price field is cleared or not a number
    if (priceInput === '' || isNaN(priceValue)) {
        $('#price').val(mrpValue.toFixed(0)); // Reset to MRP
        setDiscountOnMrp('0%');
        return;
    }

    if (priceValue < 0 || 1 / priceValue === -Infinity) {
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

    if (!isNaN(mrpValue) && mrpValue > 0) {
        var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
        setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
    }

    $('.attr_price').trigger('click');
    discountDisebled();
});

    $('#cost').on('change', function () {
        var costValue = parseFloat($('#cost').val());

        if (costValue < 0 || 1 / costValue === -Infinity) {
        alert('Cost cannot be negative!');
        $('#cost').val(Math.abs(costValue));
        return;
    }
    });

    $('#product_discount, #discount_on_mrp').on('change', function () {
        discountDisebled();
    });


    function updateHiddenInput(el, baseName) {
        var $input = $(el);
        var $row = $input.closest('tr');
        var $hidden = $row.find('input[type="hidden"][name="' + baseName + '[]"]');
        if ($hidden.length) {
            $hidden.val($input.val());
        }
    }

    $(document).on('input change', '.cost,.variantmrp, .variantdiscount, .variantprice,.unit_quantity,.unit_weight', function () {
        var $row = $(this).closest('tr');
        var cost = parseFloat($row.find('input.cost, .cost input').first().val()) || 0; 
        var mrp = parseFloat($row.find('input.variantmrp, .variantmrp input').first().val()) || 0; 
        var price = parseFloat($row.find('input.variantprice, .variantprice input').first().val()) || 0;
        var quantity = parseFloat($row.find('.unit_quantity input').val()) || 0;
        var weight = parseFloat($row.find('.unit_weight input').val()) || 0; 
        var discount = $row.find('input.variantdiscount, .variantdiscount input').first().val();
        
        if (cost < 0 || $row.find('.cost input').val().trim().startsWith('-')) {
            alert("Cost cannot be negative!");
            cost = Math.abs(cost);
            $row.find('.cost input').val(cost.toFixed(0));
        }
        if (mrp < 0 || $row.find('input.variantmrp, .variantmrp input').first().val().trim().startsWith('-')) {
            alert("MRP cannot be negative!");
            mrp = Math.abs(mrp);
            $row.find('input.variantmrp, .variantmrp input').val(mrp.toFixed(0));
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
            $row.find('input.variantdiscount, .variantdiscount input').val("0%"); 
            $row.find('input.variantprice, .variantprice input').val(mrp.toFixed(0)); 
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
            $row.find('.variantdiscount').val("0%"); 
            return;
        }

        // When price is changed, calculate the discount
        if ($(this).is('.variantprice')) {
            price = parseFloat($row.find('input.variantprice, .variantprice input').first().val()) || 0;

            if (
                    isNaN(price) || 
                    price < 0 || 
                    $row.find('.variantprice').val().trim().startsWith('-')
            ) {
                alert('Price cannot be negative!Please enter a valid price greater than or equal to 0');
                $row.find('input.variantprice, .variantprice input').val(mrp.toFixed(0)); 
                $row.find('input.variantdiscount, .variantdiscount input').val("0%"); 
                return;
            }

            if (price > mrp) {
                alert('Price cannot be greater than MRP!');
                $row.find('input.variantprice, .variantprice input').val(mrp.toFixed(0)); 
                $row.find('input.variantdiscount, .variantdiscount input').val("0%"); 
                return;
            }

            if (mrp > 0) {
                var calculatedDiscount = ((mrp - price) / mrp) * 100;
                setVariantDiscount($row.find('input.variantdiscount, .variantdiscount input').first(), calculatedDiscount.toFixed(0) + '%');
            } else if (!enableDiscountOnMrp) {
                setVariantDiscount($row.find('input.variantdiscount, .variantdiscount input').first(), '0%');
            }
        }

        // When discount is changed, calculate the price
        if ($(this).is('.variantdiscount')) {
            if (isPercentage) {
                if (discountValue > 100) {
                    alert("Discount should not exceed 100%");
                    $row.find('input.variantdiscount, .variantdiscount input').val("0%"); 
                    $row.find('input.variantprice, .variantprice input').val(mrp.toFixed(0)); 
                    return;
                }

                if (mrp > 0) {
                    var calculatedPrice = mrp - (mrp * (discountValue / 100));
                    $row.find('input.variantprice, .variantprice input').val(calculatedPrice.toFixed(0)); 
                }
            } else {
                if (discountValue > mrp) {
                    alert("Discount cannot be more than MRP");
                    $row.find('input.variantdiscount, .variantdiscount input').val("0"); 
                    $row.find('input.variantprice, .variantprice input').val(mrp.toFixed(0)); 
                    return;
                }

                if (mrp > 0) {
                    var calculatedPrice = mrp - discountValue;
                    if (calculatedPrice < 0) calculatedPrice = 0; 
                    $row.find('input.variantprice, .variantprice input').val(calculatedPrice.toFixed(0)); 
                }
        }
    }
    });

    $(document).on('input change keyup', 'input.variantmrp', function () {
        var $row = $(this).closest('tr');
        var mrp = parseFloat($(this).val()) || 0;
        var $priceInput = $row.find('input.variantprice');
        if ($priceInput.length === 0) {
            $priceInput = $(this).closest('td').next().find('input');
        }
        var $discountInput = $row.find('input.variantdiscount');
        if ($discountInput.length === 0) {
            $discountInput = $(this).closest('td').next().next().find('input');
        }
        $priceInput.val(mrp.toFixed(0));
        $discountInput.val('0%');
        updateHiddenInput($priceInput[0], 'attr_price');
        updateHiddenInput($discountInput[0], 'attr_discount');
        updateHiddenInput(this, 'attr_mrp');
    });

    $(document).on('input change keyup', 'input.variantprice', function () {
        var $row = $(this).closest('tr');
        var $mrpInput = $row.find('input.variantmrp');
        if ($mrpInput.length === 0) {
            $mrpInput = $(this).closest('td').prev().prev().find('input');
        }
        var mrp = parseFloat($mrpInput.val()) || 0;
        var price = parseFloat($(this).val()) || 0;
        var raw = $(this).val() || '';
        if (raw.trim().startsWith('-')) {
            alert('Price cannot be negative! Please enter a valid price greater than or equal to 0');
            $(this).val(mrp.toFixed(0));
            var $discNeg = $row.find('input.variantdiscount');
            if ($discNeg.length === 0) {
                $discNeg = $(this).closest('td').next().find('input');
            }
            $discNeg.val('0%');
            updateHiddenInput(this, 'attr_price');
            updateHiddenInput($discNeg[0], 'attr_discount');
            return;
        }
        if (price > mrp) {
            alert('Price cannot be greater than MRP!');
            $(this).val(mrp.toFixed(0));
            var $discountInput = $row.find('input.variantdiscount');
            if ($discountInput.length === 0) {
                $discountInput = $(this).closest('td').next().find('input');
            }
            $discountInput.val('0%');
        } else if (mrp > 0 && enableDiscountOnMrp) {
            var calculatedDiscount = ((mrp - price) / mrp) * 100;
            var $discountInput2 = $row.find('input.variantdiscount');
            if ($discountInput2.length === 0) {
                $discountInput2 = $(this).closest('td').next().find('input');
            }
            setVariantDiscount($discountInput2, calculatedDiscount.toFixed(0) + '%');
        } else if (!enableDiscountOnMrp) {
            var $discountInputOff = $row.find('input.variantdiscount');
            if ($discountInputOff.length === 0) {
                $discountInputOff = $(this).closest('td').next().find('input');
            }
            setVariantDiscount($discountInputOff, '0%');
        }
        updateHiddenInput(this, 'attr_price');
        var $dHidden = $row.find('input.variantdiscount');
        if ($dHidden.length === 0) {
            $dHidden = $(this).closest('td').next().find('input');
        }
        updateHiddenInput($dHidden[0], 'attr_discount');
    });

    $(document).on('input change keyup', 'input.variantdiscount', function () {
        var $row = $(this).closest('tr');
        var $mrpInput = $row.find('input.variantmrp');
        if ($mrpInput.length === 0) {
            $mrpInput = $(this).closest('td').prev().prev().prev().find('input');
        }
        var mrp = parseFloat($mrpInput.val()) || 0;
        var discountStr = $(this).val() || '0%';
        var isPercentage = discountStr.indexOf('%') !== -1;
        var discountValue = isPercentage ? parseFloat(discountStr.replace('%', '')) || 0 : parseFloat(discountStr) || 0;
        var price = mrp;
        if (isPercentage) {
            if (discountValue > 100) {
                alert('Discount should not exceed 100%');
                discountValue = 0;
                $(this).val('0%');
            }
            price = mrp - (mrp * (discountValue / 100));
        } else {
            if (discountValue > mrp) {
                alert('Discount cannot be more than MRP');
                discountValue = 0;
                $(this).val('0');
            }
            price = mrp - discountValue;
        }
        if (price < 0) price = 0;
        var $priceInput = $row.find('input.variantprice');
        if ($priceInput.length === 0) {
            $priceInput = $(this).closest('td').prev().find('input');
        }
        $priceInput.val(price.toFixed(0));
        updateHiddenInput(this, 'attr_discount');
        updateHiddenInput($priceInput[0], 'attr_price');
    });

    // validation and autocalculate of discount for variant on pop up
    $('#acost, #amrp, #aprice, #adiscount').on('change', function() {
        var variant_mrp = parseFloat($('#amrp').val()) || 0; // Get MRP value
        var variant_price = parseFloat($('#aprice').val()) || 0; // Get price value
        var variant_discount = $('#adiscount').val(); // Get Discount value

        var isPercentage = variant_discount.includes('%');
        var discountValue = isPercentage ? parseFloat(variant_discount.replace('%', '')) : parseFloat(variant_discount);

        // MRP change: Set price to MRP and discount to 0%
        if ($(this).is('#amrp')) {
            $('#aprice').val(variant_mrp.toFixed(0));  
            $('#adiscount').val("0%"); 
            return;  
        }

        // Price change: Calculate the discount based on MRP and Price
        if ($(this).is('#aprice')) {
            if (variant_mrp > 0 && enableDiscountOnMrp) {
                var calculated_discount = ((variant_mrp - variant_price) / variant_mrp) * 100;
                $('#adiscount').val(calculated_discount.toFixed(0) + '%');  
            } else if (!enableDiscountOnMrp) {
                $('#adiscount').val('0%');
            }
            // Validation: Ensure Price is not greater than MRP
            if (variant_price > variant_mrp) {
                alert('Price cannot be greater than MRP!');
                $('#aprice').val(variant_mrp.toFixed(0));  
                $('#adiscount').val("0%"); 
                return; 
            }
        }

        // Discount change: Calculate the price based on MRP and Discount
        if ($(this).is('#adiscount')) {
            // Validate if discount is greater than 100%
            if (discountValue > 100) {
                alert("Discount should not exceed 100%");
                $('#adiscount').val("0%"); // Reset discount
                $('#aprice').val(variant_mrp.toFixed(0)); // Reset price to MRP
                return;
            }

            if (variant_mrp > 0) {
                // If discount is a percentage
                if (isPercentage) {
                    var calculated_price = variant_mrp - (variant_mrp * (discountValue / 100));
                    $('#aprice').val(calculated_price.toFixed(0));  
                }
                // If discount is a flat amount (no % sign)
                else if (!isPercentage && !isNaN(discountValue)) {
                    var calculated_price = variant_mrp - discountValue;
                    if (calculated_price < 0) calculated_price = 0; // Prevent negative prices
                    $('#aprice').val(calculated_price.toFixed(0));  
                }
            }
        }
    });

});
function discountDisebled(){
       var product_discount = parseFloat($('#product_discount').val()) || 0;
        var discount_on_mrp = parseFloat($('#discount_on_mrp').val()) || 0;

        if (product_discount > 0) {
            $('#discount_on_mrp').attr('readonly', true);
            $('#discount_on_mrp').val('0%');

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
<script>
$(document).ready(function () {
    $('#custom-submit').on('click', function () {
        const category = $('#category').val();
        const unit = $('#unit').val();
        if (!category) {
            alert('Please select a category before submitting.');
            $('#category').focus();
            return;
        }
        if (!unit) {
            alert('Please select a unit before submitting.');
            $('#unit').focus();
            return;
        }


        $('form[data-toggle="validator"]')[0].submit();
    });
});
</script>


<!-- End Urbanpiper --->

<!-- ///////////////////////other category/////////////////////////// -->

<script>
$(document).ready(function () {
    const fullCategoryList = <?= json_encode($categories) ?>;

    $('#category').change(function () {
        const selectedCatId = $(this).val();
        const $otherCat = $('#othercategory');
        $otherCat.empty();
        fullCategoryList.forEach(cat => {
            if (cat.id != selectedCatId) {
                $otherCat.append(
                    $('<option>', {
                        value: cat.id,
                        text: cat.name
                    })
                );
            }
        });
        $otherCat.trigger('change');
    });
});
</script>
<?php if (!empty($Settings->display_job_work)) { ?>
<script>
$(document).ready(function(){
    function toggleJobWork(){
        var inwardType = $("#product_inward_type option:selected").text();
        if(inwardType == "Job Works"){
            $("#job_work_div").show();
        }else{
            $("#job_work_div").hide();
            $("#job_work").val(null).trigger("change");
        }
    }
    toggleJobWork();
    $("#product_inward_type").change(function(){
        toggleJobWork();
    });
});
</script>
<?php } ?>
<script>
$(document).ready(function() {
    $('#packing_size').on('change', function() {
        var val = parseFloat($(this).val());
        if (val < 0) {
            bootbox.alert("Packing Size cannot be negative!");
            $(this).val(0);
        }
    });
});
</script>
