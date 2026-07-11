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

.variant_cost::-webkit-outer-spin-button,
.variant_cost::-webkit-inner-spin-button,
.variant_mrp::-webkit-outer-spin-button,
.variant_mrp::-webkit-inner-spin-button,
.unit_quantity::-webkit-inner-spin-button,
.unit_quantity::-webkit-outer-spin-button,
.unit_weight::-webkit-inner-spin-button,
.unit_weight::-webkit-outer-spin-button,
.variant_price::-webkit-outer-spin-button,
.variant_price::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.variant_cost,
.variant_mrp,
.variant_price,
.unit_quantity,
.unit_weight {
    -moz-appearance: textfield;
}
.custommargin {
    width: 48% !important;
    margin-left: 14px !important;
}

.widthmarg {
    width: 100% !important;
    margin-left: 0px !important;
    ;
}

.well {
    border: 1px solid #ddd;
    background-color: #f6f6f6;
    box-shadow: none;
    border-radius: 0px;
    margin-left: -5px;
    width: 105%;
}

label {
    margin-top: 6px;
}

input#discount_on_mrp {
    margin-top: -6px;
}

.well.well-sm {
    width: 105%;
    margin-left: -5px;
}

.siderig {
    padding-right: 36px;
}

.padside {
    padding-right: 36px;
}

.table th:nth-child(4),
.table td:nth-child(4),
.table th:nth-child(5),
.table td:nth-child(5) {
    font-size: 16px;
    padding: 2px;
}

#attrTable th:nth-child(2),
#attrTable td:nth-child(3),
#attrTable th:nth-child(4),
#attrTable th:nth-child(5) {
    width: 90px;
}

#attrTable th:nth-child(6),
#attrTable td:nth-child(7) {
    width: 75px;
}

.table-condensed>thead>tr>th,
.table-condensed>tbody>tr>th,
.table-condensed>tfoot>tr>th,
.table-condensed>thead>tr>td,
.table-condensed>tbody>tr>td,
.table-condensed>tfoot>tr>td {
    padding: 2px;
}

.introtext {
    width: 103.4% !important;
}

@media (min-width: 992px) {
    .col-md-4 {
        width: 43.333333%;
    }
}

@media (min-width: 992px) {
    .col-md-8 {
        width: 49.666667%;
    }
}

/* For Tablets and iPads (768px - 1024px) */
@media (max-width: 1024px) and (min-width: 768px) {

    /* .well.well-sm {
        width: 100%;
        margin-left: 0;
    } */
    .custommargin {
        width: 48% !important;
        margin-left: 14px !important;
    }

    .well {
        border: 1px solid #ddd;
        background-color: #f6f6f6;
        box-shadow: none;
        border-radius: 0px;
        margin-left: -53px;
        width: 116%;
    }

    .widthmarg {
        width: 100% !important;
        ;
        margin-left: 0px !important;
        ;
    }

    .well.well-sm {
        width: 116%;
        margin-left: -53px;
    }

    .table th:nth-child(4),
    .table td:nth-child(4),
    .table th:nth-child(5),
    .table td:nth-child(5) {
        font-size: 12px;
        padding: 4px;
    }

    #attrTable th:nth-child(2),
    #attrTable td:nth-child(2),
    #attrTable th:nth-child(3),
    #attrTable th:nth-child(4),
    #attrTable td:nth-child(3),
    #attrTable td:nth-child(4) {
        width: 100px;
    }

    #attrTable th:nth-child(7),
    #attrTable td:nth-child(7) {
        width: 45px;
    }

    .table-condensed>thead>tr>th,
    .table-condensed>tbody>tr>th,
    .table-condensed>tfoot>tr>th,
    .table-condensed>thead>tr>td,
    .table-condensed>tbody>tr>td,
    .table-condensed>tfoot>tr>td {
        padding: 4px;
    }

    .introtext {
        width: 100% !important;
    }
}

@media (max-width: 1366px) {
    #attrTable input {
        font-size: 13px;
        padding: 4px 6px;
    }

    .attr-input {
        min-width: 50px;
        max-width: 100px;
        width: 110%;
        font-size: 13px;
        padding: 4px 6px;
    }

    #attrTable {
        table-layout: auto;
    }
}

/* For screens 1367px to 1536px */
@media (min-width: 1367px) and (max-width: 1536px) {
    #attrTable input {
        font-size: 14px;
        padding: 5px 6px;
    }

    .attr-input {
        min-width: 50px;
        max-width: 100px;
        width: 110%;
        font-size: 14px;
        padding: 5px 6px;
        box-sizing: border-box;
    }

    #attrTable {
        table-layout: auto;
    }
}

/* For screens larger than 1536px (e.g., 1920x1080) */
@media (min-width: 1537px) {
    #attrTable input {
        font-size: 15px;
        padding: 6px 8px;
    }

    .attr-input {
        min-width: 50px;
        max-width: 100px;
        width: 120%;
        font-size: 15px;
        padding: 6px 8px;
        box-sizing: border-box;
    }

    #attrTable {
        table-layout: auto;
    }
}

/* Enable horizontal scroll if table overflows */
.table-responsive-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>")
        .select2({
            placeholder: "<?= lang('select_category_to_load') ?>",
            data: [{
                id: '',
                text: '<?= lang('select_category_to_load') ?>'
            }]
        });
    $('#category').change(function() {
        var v = $(this).val();
        $('#modal-loading').show();
        if (v) {
            $.ajax({
                type: "get",
                async: false,
                url: "<?= site_url('products/getSubCategories') ?>/" + v,
                dataType: "json",
                success: function(scdata) {
                    if (scdata != null) {
                        $("#subcategory").select2("destroy").empty().attr("placeholder",
                            "<?= lang('select_subcategory') ?>").select2({
                            placeholder: "<?= lang('select_category_to_load') ?>",
                            data: scdata
                        });
                    }
                },
                error: function() {
                    bootbox.alert('<?= lang('ajax_error') ?>');
                    $('#modal-loading').hide();
                }
            });
        } else {
            $("#subcategory").select2("destroy").empty().attr("placeholder",
                "<?= lang('select_category_to_load') ?>").select2({
                placeholder: "<?= lang('select_category_to_load') ?>",
                data: [{
                    id: '',
                    text: '<?= lang('select_category_to_load') ?>'
                }]
            });
        }
        $('#modal-loading').hide();
    });
    $('#code').bind('keypress', function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('update_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'novalidate' => 'novalidate', 'id' => 'product-edit-form', 'data-bv-excluded' => ':disabled');
                echo form_open_multipart("products/edit/" . $product->id, $attrib)
                ?>
                <?= form_hidden('id', $product->id) ?>
                <input type="hidden" name="has_new_variants" id="has_new_variants" value="<?= $this->input->post('has_new_variants') ? '1' : '0' ?>" />
                <input type="hidden" name="pos_type" id="pos_type" value="<?= $Settings->pos_type ?>" />
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <?= lang("Product Type", "product type") ?>
                                <?php
                                $opts = array('standard' => lang('standard'), 'combo' => lang('combo'),'Bundle' => lang('Bundle'), 'digital' => lang('digital'), 'service' => lang('service') ,'raw' => lang('Raw'),'Intermediate' => lang('Intermediate'));
                                echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("Storage Type", "storage_type") ?>
                                <?php                        
                                $ist = ['packed'=>'Packed Products', 'loose'=>'Loose Products'];
                                echo form_dropdown('storage_type', $ist, (isset($_POST['storage_type']) ? $_POST['storage_type'] : $product->storage_type), 'class="form-control select" id="storage_type" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($Settings->pos_type == 'restaurant' || $Settings->pos_type == 'amstead') { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group all">
                                <?= lang("Division", "division") ?>

                                <?php
                                $div[''] = "";
                                foreach ($division as $division) {
                                    $div[$division->id] = $division->name;
                                }
                                echo form_dropdown('division', $div, (isset($_POST['divisionid']) ? $_POST['divisionid'] : ($product ? $product->divisionid : $_SESSION['divisionid'])), 'class="form-control select" id="division" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group all">
                                <?= lang("product_name", "name") ?>
                                <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_code", "code") ?>
                                <div class="input-group">
                                    <?php
                                    // Keep Product Code read-only if product has any transactions or stock quantity
                                    $code_input_attrs = 'class="form-control" id="code" required="required"';
                                    $span_style = '';
                                    if (!empty($product_code_locked)) {
                                        $code_input_attrs .= ' readonly="readonly"';
                                        $span_style .= 'pointer-events: none;';
                                    }
                                    ?>
                                    <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), $code_input_attrs) ?>
                                     <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px; <?= $span_style ?>">
                                        <i class="fa fa-random"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group all" style="width: 370px;">
                        <?= lang("Product_Weight", 'weight') ?>
                        <div class="input-group">
                            <?= form_input('weight', (isset($_POST['weight']) ? $_POST['weight'] : ($product ? $product->weight : '')), 'class="form-control" id="weight" size="6" ') ?>
                            <span class="input-group-addon" style="padding: 1px 10px;">
                                / Kilogram (KG)
                            </span>
                        </div>
                        <span
                            class="help-block"><?= lang('Products weight should be in Kilogram (Ex. 1Kg = 1 | 500Gm = 0.500 | 250Gm = 0.250, etc.)') ?></span>
                    </div>
                    <!-- 12-03-19 -->

                    <div class="row" style="width: 400px;">
                        <div class="col-md-6">
                            <div class="form-group standard">
                                <?= lang("alert_quantity", "alert_quantity") ?>
                                <div class="input-group">
                                    <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->sma->formatDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                                    <span class="input-group-addon">
                                        <input type="checkbox" name="track_quantity" id="inlineCheckbox1" value="1"
                                            <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php
                    if (!empty($brands) && is_array($brands)) {
                    ?>
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("brand", "brand") ?>
                                <?php
                        $br[''] = "";
                        foreach ($brands as $brand) {
                            $br[$brand->id] = $brand->name;
                        }
                        echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                        
                        ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row" style="width: 400px;">
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("category", "category") ?>
                                <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
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
                            <div class="form-group all " style="width: 100%;">
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

                                        echo form_dropdown('othercategory[]',$other_cat_options, $selected_other_categories,'class="form-control select" id="othercategory" multiple="multiple" style="width:100%;"'
                                        );
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($Settings->display_job_work)) { ?>
                        <?php 
                            // DCC stages on main product (sma_product_dcc_stages.product_id = this product)
                            $job_work_products = $this->products_model->getJobWorkProductsByMainId($product->id);
                            $dcc_stages = $this->db->get_where('sma_product_dcc_stages', array('product_id' => $product->id))->result();
                            $has_job_work_setup = !empty($job_work_products) || !empty($dcc_stages);
                            
                            if ($has_job_work_setup) {
                                // Show readonly job work data from sma_product_dcc_stages
                                $job_work_ids = array();
                                
                                foreach ($dcc_stages as $stage) {
                                    if (!empty($stage->job_work)) {
                                        $job_work_ids[] = $stage->job_work;
                                    }
                                }
                                
                                // Get job work names from sma_standard_job_works
                                $job_work_names = array();
                                if (!empty($job_work_ids)) {
                                    $job_work_ids = array_unique($job_work_ids);
                                    $this->db->where_in('id', $job_work_ids);
                                    $job_work_data = $this->db->get('sma_standard_job_works')->result();
                                    foreach ($job_work_data as $jw) {
                                        $job_work_names[] = $jw->items;
                                    }
                                }
                            ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group all">                         
                                        <?= lang("Inward_Type", "Inward_Type") ?>
                                        <input type="text" class="form-control" value="Job Works" readonly="readonly" />
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <?= lang("Job_Works", "Job_Works"); ?>
                                        <input type="text" class="form-control" id="job_work_readonly" value="<?= implode(', ', $job_work_names) ?>" readonly="readonly" />
                                        <?php foreach ($job_work_ids as $jw_id): ?>
                                        <input type="hidden" name="job_work[]" value="<?= $jw_id ?>" />
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php } elseif (!empty($product->mainproduct_id)) { ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group all">                         
                                        <?= lang("Inward_Type", "Inward_Type") ?>
                                        <input type="text" class="form-control" value="Purchase" readonly="readonly" />
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <?= lang("Job_Works", "Job_Works"); ?>
                                        <input type="text" class="form-control" value="This is a job work product" readonly="readonly" />
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group all">                         
                                        <?= lang("Inward_Type", "Inward_Type") ?>
                                        <?php
                                        $pit = array();
                                        $default_inward = '';
                                        $purchase_inward_id = '';
                                        if (!empty($product_inward_type)) {
                                            foreach ($product_inward_type as $key => $row) {
                                                $pit[$row->id] = $row->type;
                                                if (strcasecmp(trim($row->type), 'Purchase') === 0) {
                                                    $purchase_inward_id = $row->id;
                                                }
                                                if ($key === 0) {
                                                    $default_inward = $row->id;
                                                }
                                            }
                                        }
                                        if (isset($_POST['product_inward_type']) && $_POST['product_inward_type'] !== '') {
                                            $selected_value = $_POST['product_inward_type'];
                                        } elseif ($product && !empty($product->product_inward_type)) {
                                            $selected_value = $product->product_inward_type;
                                        } elseif ($purchase_inward_id !== '') {
                                            $selected_value = $purchase_inward_id;
                                        } else {
                                            $selected_value = $default_inward;
                                        }
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
                                        $selected_job_works = array();
                                        echo form_dropdown('job_work[]', $jw, $selected_job_works,'class="form-control select" id="job_work" multiple="multiple" style="width:100%"' );
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <div class="row" id="dcc_stage_btn_wrap" style="display:none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-info" id="open_dcc_stage_modal">
                                Direct Cost Components
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    </div>
                    </div>
                    <div class="row" style="width: 463px;">
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("Article Number", "Article Number") ?>
                                <?= form_input('article_code', (isset($_POST['article_code']) ? $_POST['article_code'] : ($product ? $product->article_code : '')), 'class="form-control" id="article_no"  ') ?>
                            </div>
                        </div>
                        <!-- End 12-03-19 -->
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("hsn_code", "hsn_code") ?> Code
                                <?= form_input('hsn_code', (isset($_POST['hsn_code']) ? $_POST['hsn_code'] : ($product ? $product->hsn_code : '')), 'class="form-control" id="hsn_code"  '); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("barcode_symbology", "barcode_symbology") ?>
                                <?php
                                $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E');
                                echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group standard">
                                <?= lang('Purchase_Unit', 'default_purchase_unit'); ?>
                                <?php
                                $uopts = [];
                                $uopts[''] = lang('select') . ' ' . lang('unit');
                                foreach ($subunits as $sunit) {
                                    $uopts[$sunit->id] = $sunit->name . ' (' . $sunit->code . ')';
                                }
                                foreach ($base_units as $bu) {
                                    if ($bu->id == $product->unit) {
                                        $uopts[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                                        break;
                                    }
                                }
                                ?>
                                <?= form_dropdown('default_purchase_unit', $uopts, $product->purchase_unit, 'class="form-control" id="default_purchase_unit" style="width:100%;"'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="width: 462px;">
                        <div class="col-md-4">
                            <div class="form-group standard">
                                <?= lang('product_unit', 'unit'); ?>
                                <?php
                        $pu[''] = lang('select') . ' ' . lang('unit');
                        foreach ($base_units as $bu) {
                            $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                        }
                        ?>
                                <?= form_dropdown('unit', $pu, set_value('unit', $product->unit), 'class="form-control tip" required="required" id="unit" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group standard">
                                <?= lang('Sale_Unit', 'default_sale_unit'); ?>
                                <?php
                        $uopts[''] = lang('select') . ' ' . lang('unit');
                        foreach ($subunits as $sunit) {
                            $uopts[$sunit->id] = $sunit->name . ' (' . $sunit->code . ')';
                        }
                        ?>
                                <?= form_dropdown('default_sale_unit', $uopts, $product->sale_unit, 'class="form-control" id="default_sale_unit" style="width:100%;"'); ?>
                            </div>
                        </div>

                    </div>
                    <!-- Product Unit Type Selection -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">

                                <div style="display: flex; align-items: center; gap: 25px; font-size: 15px;">
                                    
                                    <label class="control-label" style="margin-bottom: 0;">
                                        Yield Unit Type:
                                    </label>

                                    <div class="radio" style="margin: 0;">
                                        <label style="margin-bottom: 0;">
                                            <input type="radio" name="unit_type" value="0" <?= ($product->yield_unit == 0) ? 'checked' : ''; ?>>
                                            <strong>Product Unit</strong>
                                        </label>
                                    </div>

                                    <div class="radio" style="margin: 0;">
                                        <label style="margin-bottom: 0;">
                                            <input type="radio" name="unit_type" value="1" <?= ($product->yield_unit == 1) ? 'checked' : ''; ?>>
                                            <strong>Sale Unit</strong>
                                        </label>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                    <!--
                    <div class="form-group standard">
                        <?= lang("product_cost", "cost") ?> *
                        <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->sma->formatDecimal($product->cost) : '')), 'class="form-control tip custom_price" id="cost" required="required"') ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_price", "price") ?>
                        <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price" required="required"') ?>
                    </div>
                    
                     

                    <div class="form-group all">
                        <?= lang("product_mrp", "price") ?>
                        <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                    </div>
                    -->
                    <?php 
                    ////////////////////////// Mrp , Price , Cost//////////////////////////////////

                    $show_price_fields = ($Owner || $Admin || $this->session->userdata('show_price'));
                    $show_cost_fields = ($Owner || $Admin || $this->session->userdata('show_cost'));
                    $show_mrp_fields = ($Owner || $Admin || $this->session->userdata('show_mrp'));
                    $row_width = '462px';
                    if ($show_price_fields && $show_cost_fields && $show_mrp_fields) {
                        $row_width = '462px'; // All fields shown
                    } elseif ($show_price_fields || $show_cost_fields || $show_mrp_fields) {
                        $row_width = '310px'; // Some fields hidden
                    } else {
                        $row_width = 'auto'; // All fields hidden (unlikely but handled)
                    }
                    ?>
                    <div class="row" style="width: <?= $row_width; ?>;">
                        <?php if ($show_mrp_fields) { ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_mrp", "mrp") ?>
                                <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($show_price_fields) { ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_price", "price") ?>
                                <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price" required="required"') ?>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                    <div class="row" style="width: <?= $row_width; ?>;">
                        <?php if ($show_mrp_fields && $enable_discount_on_mrp) { ?>
                        <div class="col-md-4 discount-on-mrp-col">
                            <div class="form-group all">
                                <label for="discount_on_mrp"><?= lang("Discount On MRP", "discount_on_mrp") ?></label>
                                <!-- <?= form_input('discount_on_mrp', $product->discount_on_mrp, 'class="form-control" id="discount_on_mrp" placeholder=""'); ?> -->
                                <?= form_input('discount_on_mrp', (isset($product->discount_on_mrp) ? $product->discount_on_mrp: ''), 'class="form-control tip" id="discount_on_mrp"') ?>

                            </div>
                        </div>
                        <?php } elseif (!$enable_discount_on_mrp) { ?>
                        <input type="hidden" name="discount_on_mrp" id="discount_on_mrp" value="0%" />
                        <?php } ?>

                        <?php if ($show_cost_fields) { ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_cost", "cost") ?> *
                                <?php
                                $cost_value = isset($_POST['cost'])
                                    ? (($_POST['cost'] === '' || $_POST['cost'] === null) ? '0' : $_POST['cost'])
                                    : ($product ? $this->sma->formatDecimal($product->cost) : '0');
                                ?>
                                <?= form_input('cost', $cost_value, 'class="form-control tip custom_price" id="cost" required="required"') ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($Settings->packing_size_column == 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("Packing Size", "packing_size") ?>
                                    <?php
                                    $packing_size_value = isset($_POST['packing_size'])
                                        ? (($_POST['packing_size'] === '' || $_POST['packing_size'] === null) ? '0' : $_POST['packing_size'])
                                        : ($product ? $this->sma->formatDecimal($product->packing_size) : '0');
                                    ?>
                                    <?= form_input('packing_size', $packing_size_value, 'class="form-control tip" id="packing_size" placeholder="0"') ?>
                                    <span class="help-block">
                                        <?= lang('e.g., 30 for 30 bottles per crate') ?>
                                    </span>
                                </div>
                            </div>
                        <?php } ?>

                    </div>


                    <div class="row" style="width:400px;">
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("Repeat Sale Discount Rate", "repeat_sale_discount_rate") ?>
                                <input type="text" id="repeat_sale_discount_rate"
                                    value="<?= (isset($_POST['repeat_sale_discount_rate']) ? $_POST['repeat_sale_discount_rate']:$product->repeat_sale_discount_rate) ?>"
                                    placeholder="Repeat Sale Discount Rate" class="form-control"
                                    name="repeat_sale_discount_rate" />

                            </div>
                        </div>
                        <div class="col-md-6">
                            <?= lang("Repeat_Sale_Validity", "repeat_sale_validity") ?>
                            <input type="number" min="1" id="repeat_sale_validity"
                                placeholder="Repeat Sale Validity In Days"
                                value="<?= (isset($_POST['repeat_sale_validity']) ? $_POST['repeat_sale_validity']:$product->repeat_sale_validity) ?>"
                                class="form-control" name="repeat_sale_validity" />

                        </div>
                    </div>

                    <div style="display: flex; width: 90%;" ;>
                        <div class="form-group col-xs-7" style="padding-left: 0 !important;">
                            <?= lang("Product_Rank", "Product Rank") ?>
                            <input type="number" name="rank" class="product-rank" id="rank"
                                value="<?= isset($_POST['rank']) ? $_POST['rank'] : ($product ? $product->rank : '') ?>"
                                min="0">
                        </div>
                        <div class="form-group col-xs-5">
                            <input type="checkbox" class="checkbox" value="1" name="flag_visible"
                                <?= isset($product->flag_visible) && $product->flag_visible == 1 ? 'checked="checked"' : ''; ?>>
                            <label for="flag_visible" class="padding05">
                                <?= lang("Flag_Visible", "Flag Visible"); ?>
                            </label>
                        </div>
                    </div>
                    <div class="row" style="width:400px;">
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("Shelf_Life", "shelf_life") ?>
                                <?= form_input('shelf_life', (isset($_POST['shelf_life']) ? $_POST['shelf_life'] : ($product ? $product->shelf_life : '')), 'class="form-control" id="shelf_life"'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group all" style="width:371px;">
                        <?= lang("Season", "season") ?>
                        <?php 
                            $br1[''] = lang('select').' '.lang('season');
                            if (!empty($seasons) && is_array($seasons)) {
                                foreach ($seasons as $season) {
                                    $br1[$season->id] = $season->SeasonName; 
                                }
                            }
                            echo form_dropdown( 'season', $br1, set_value('season', $product->season_id),  'class="form-control tip" id="season" style="width:100%;"');
                            ?>
                    </div>

                    <div class="form-group" style="width:371px;">
                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion"
                            <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                        <label for="promotion" class="padding05">
                            <?= lang('promotion'); ?>
                        </label>
                    </div>

                    <div id="promo" <?= $product->promotion ? '' : ' style="display:none;"'; ?>>
                        <div class="well well-sm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('promo_price', 'promo_price'); ?>
                                        <?= form_input('promo_price', set_value('promo_price', $product->promo_price ? $this->sma->formatDecimal($product->promo_price) : ''), 'class="form-control tip" id="promo_price"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('start_date', 'start_date'); ?>
                                        <?= form_input('start_date', set_value('start_date', $product->start_date ? $this->sma->hrld($product->start_date) : ''), 'class="form-control tip datetime" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('end_date', 'end_date'); ?>
                                        <?= form_input('end_date', set_value('end_date', $product->end_date ? $this->sma->hrld($product->end_date) : ''), 'class="form-control tip datetime" id="end_date"'); ?>
                                    </div>
                                </div>
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
                            // echo form_dropdown('tax_rate', $tx, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                            echo form_dropdown('tax_rate', $tx, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" placeholder="Category tax" style="width:100%"')
                            ?>
                        </div> -->
                        <div class="form-group all">
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tx[""] = "Category Tax"; 
                            if (!empty($tax_rates)) {
                                foreach ($tax_rates as $tax) {
                                    if ($tax->is_substitutable == 0) {
                                        $tx[$tax->id] = $tax->name;
                                    }
                                }
                            }
                            // $selected_tax = isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product->tax_rate ?? '');
                            // $selected_tax = (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : '');
                            $selected_tax = isset($product->tax_rate) ? $product->tax_rate : (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : '');
                            echo form_dropdown('tax_rate',$tx,$selected_tax,'class="form-control select" placeholder="' . lang("select") . ' ' . lang("tax") . '" style="width:100%"');
                            ?>
                        </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("tax_method", "tax_method") ?>
                                <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                            ?>
                            </div>
                        </div>
                    </div>
                    <!--<div class="form-group all">
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("tax_method", "tax_method") ?>
                            <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                            ?>
                        </div>--->


                    <?php } ?>
                    <!---<div class="form-group standard">
                        <?= lang("alert_quantity", "alert_quantity") ?>
                        <div
                            class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->sma->formatDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                            <span class="input-group-addon">
                                <input type="checkbox" name="track_quantity" id="inlineCheckbox1"
                                       value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                            </span>
                        </div>
                    </div--->

                    <div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>"
                            name="product_image" data-show-upload="false" data-show-preview="false" accept="image/*"
                            class="form-control file">
                    </div>

                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile[]"
                            multiple="true" data-show-upload="false" data-show-preview="false" class="form-control file"
                            accept="image/*">
                    </div>
                    <div id="img-details"></div>

                    <!--- Restaurant Type POS  ---->
                    <?php if ($Settings->pos_type == 'restaurant' || $Settings->pos_type == 'bakery') { ?>

                    <?php if ($Settings->product_external_platform == '1') { ?>
                    <!--- Urbanpiper  ---->
                    <div class="form-group all">
                        <?= lang("Used By External Platform (Ex. Zomato, Swiggy, Etc.)", "UrbanPiper Products") ?> <img
                            src="http://localhost/pos_in/themes/default/assets/images/new.gif" height="30px" alt="new">
                        <select class="form-control" name="up_items" id="urbanpiperitem">
                            <?php
                                    $selected = 'select_' . $product->up_items;
                                    $$selected = ' selected="selected" ';
                                    ?>
                            <option value="1" <?= $select_1 ?>>Yes</option>
                            <option value="0" <?= $select_0 ?>> No </option>
                        </select>
                    </div>

                    <div id="urbanpipercontain"
                        style="<?= ($product->up_items == '1') ? 'display:block' : 'display:none' ?>">
                        <input type="hidden" name="up_products_data_id" value="<?= $urbanbpiper_Data->id ?>" />
                        <fieldset style=" border: 1px solid #ccc; padding: 5px;">
                            <legend style="width: auto; padding: 0px 10px; border-bottom: 0;margin-bottom: 0px;">
                                External Platform Options <img
                                    src="http://localhost/pos_in/themes/default/assets/images/new.gif" height="30px"
                                    alt="new"></legend>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group ">
                                        <?= lang("Price ", "Price") ?>
                                        <input class="form-control" type="text" name="upprice" id="upprice"
                                            value="<?= $urbanbpiper_Data->price ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group ">
                                        <?= lang("Food Type ", "Food Type") ?>
                                        <select class="form-control" name="up_food_type" id="up_food_type">
                                            <option value="">--Select--</option>
                                            <?php foreach ($foodtype as $foodtype_value) { ?>
                                            <option value="<?= $foodtype_value->id ?>"
                                                <?php echo ($urbanbpiper_Data->food_type_id == $foodtype_value->id) ? ' selected="selected" ' : '' ?>>
                                                <?= $foodtype_value->food_type ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group ">
                                        <?= lang("Is Available", "Is Available") ?>
                                        <?php
                                                $available_selected = 'available_' . $urbanbpiper_Data->available;
                                                $$available_selected = ' selected="selected" ';
                                                ?>
                                        <select class="form-control" name="available" id="available">
                                            <option value="1" <?= $available_1 ?>>Yes</option>
                                            <option value="0" <?= $available_0 ?>>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group ">
                                        <?= lang("Sold At Store", "Sold At Store") ?>
                                        <?php
                                                $at_store_selected = 'at_store_' . $urbanbpiper_Data->sold_at_store;
                                                $$at_store_selected = ' selected="selected" ';
                                                ?>
                                        <select class="form-control" name="sold_at_store" id="sold_at_store">
                                            <option value="1" <?= $at_store_1 ?>>Yes</option>
                                            <option value="0" <?= $at_store_0 ?>>No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group ">
                                        <?= lang("Is Recommended", "Is Recommended") ?>
                                        <?php
                                                $recommended_selected = 'recommended_' . $urbanbpiper_Data->recommended;
                                                $$recommended_selected = ' selected="selected" ';
                                                ?>
                                        <select class="form-control" name="recommended" id="recommended">
                                            <option value="1" <?= $recommended_1 ?>>Yes</option>
                                            <option value="0" <?= $recommended_0 ?>>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group ">
                                        <?= lang("Manage_stock", "Manage_stock") ?>

                                        <select class="form-control" name="manage_stock" id="manage_stock">
                                            <option value="1"
                                                <?= ( $urbanbpiper_Data->manage_stock == 1? 'selected':'') ?>>Yes
                                            </option>
                                            <option value="0"
                                                <?= ( $urbanbpiper_Data->manage_stock == 0? 'selected':'') ?>>No
                                            </option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="form-group ">
                                        <?= lang("Tags for Default ", "Tags") ?> <small class="text-info">(*Use comma
                                            for multiple tags)</small><br />
                                        <select class="form-control" name="default_tag" id="default_tag"
                                            data-role="tagsinput">
                                            <option value="packaged-good"> Packaged Good</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group ">
                                        <?= lang("Tags for Zomato ", "Tags") ?> <small class="text-info">(*Use comma for
                                            multiple tags)</small><br />
                                        <input class="form-control" type="text" name="tag_zomato" id="tag_zomato"
                                            data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_zomato ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group ">
                                        <?= lang("Tags for Swiggy ", "Tags") ?> <small class="text-info">(*Use comma for
                                            multiple tags)</small><br />
                                        <input class="form-control" type="text" name="tag_swiggy" id="tag_swiggy"
                                            data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_swiggy ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group ">
                                        <?= lang("Tags for Food Panda ", "Tags") ?> <small class="text-info">(*Use comma
                                            for multiple tags)</small><br />
                                        <input class="form-control" type="text" name="tag_foodpanda" id="tag_foodpanda"
                                            data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_foodpanda ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group ">
                                        <?= lang("Tags for Uber Eats ", "Tags") ?> <small class="text-info">(*Use comma
                                            for multiple tags)</small><br />
                                        <input class="form-control" type="text" name="tag_ubereats" id="tag_ubereats"
                                            data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_ubereats ?>" />
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
                <div class="col-md-7">
                    <div class="standard">
                        <div>
                            <?php
                            if (!empty($warehouses) || !empty($warehouses_products)) {
                                echo '<div class="row"><div class="col-md-12"><div class="well">';
                                echo '<p><strong>' . lang("warehouse_quantity") . '</strong></p>';
                                if (!empty($warehouses_products)) {

                                    $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                    foreach ($warehouses_products as $wh_pr) {
                                        if ($Owner || $Admin) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <input type="hidden" value="' . $this->sma->formatDecimal($wh_pr->quantity) . '" id="vwh_qty_' . $wh_pr->id . '"><span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->sma->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        } elseif (in_array($wh_pr->id, $permisions_werehouse)) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <input type="hidden" value="' . $this->sma->formatDecimal($wh_pr->quantity) . '" id="vwh_qty_' . $wh_pr->id . '"><span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->sma->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        }
                                    }
                                }
                                echo '<div class="clearfix"></div></div></div></div>';
                            }
                            ?>
                        </div>
                        <div class="clearfix"></div>

                        <div id="attrs"></div>
                        <div class="well well-sm">
                            <?php if ($product_options) { ?>
                            <table class="table table-bordered table-condensed table-striped"
                                style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?> margin-top: 10px;">
                                <thead>
                                <tr class="active">
                                <th><?= lang('name') ?></th>
                                <th><?= lang('warehouse') ?></th>
                                <th><?= lang('quantity') ?></th>
                                <!-- <th><?= lang('price_addition') ?></th> -->
                                <?php 
                                ////////////////////////// Mrp , Price , Cost//////////////////////////////////
                                if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
                                <th><?= lang('Price') ?></th>
                                <?php } ?>

                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($product_options as $option) {
                                            echo '<tr>'
                                                . '<td class="col-xs-3"><input type="hidden" name="attr_id[]" value="' . $option->id . '"><span>' . $option->name . '</span></td>'
                                                . '<td class="code text-center col-xs-3"><span>' . $option->wh_name . '</span></td>'
                                                . '<td class="quantity text-center col-xs-2"><span>' . $this->sma->formatQuantity($option->wh_qty) . '</span></td>';
                                            if ($Owner || $Admin || $this->session->userdata('show_price')) {
                                                echo '<td class="price text-right col-xs-2">' . $this->sma->formatMoney($option->price) . '</td>';
                                            }
                                            echo '</tr>';
                                        }
                                        ?>
                                </tbody>
                            </table>
                            <?php
                            }
                            if ($product_variants) {
                                
                                ?>
                            <h3 class="bold"><?= lang('update_variants'); ?></h3>
                            <table class="table table-bordered table-condensed table-striped" style="margin-top: 10px;">
                                <thead>
                                    <tr class="active">
                                        <th style="width: 40px;"><?= lang('Pri ma ry') ?></th>
                                        <?php 
                                        // Adjust Variant Name column width based on permissions
                                        $variant_name_width = '25rem';
                                        if (!($Owner || $Admin || $this->session->userdata('show_cost')) && !($Owner || $Admin || $this->session->userdata('show_price'))) {
                                            // Both cost and price hidden - make variant name wider
                                            $variant_name_width = '35rem';
                                        } elseif (!($Owner || $Admin || $this->session->userdata('show_cost')) || !($Owner || $Admin || $this->session->userdata('show_price'))) {
                                            // One of them hidden - medium width
                                            $variant_name_width = '28rem';
                                        }
                                        ?>
                                        <th style="width: <?= $variant_name_width; ?>;"><?= lang('Variant Name') ?></th>
                                        <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
                                        <th style="width: 110px;"><?= lang('Cost') ?></th>
                                        <?php } ?>
                                        <!-- <th class="col-xs-2"><?= lang('Price_Addition') ?></th> -->
                                         <?php if ($Owner || $Admin || $this->session->userdata('show_mrp')) { ?>
                                        <th style="width: 110px;"><?= lang('MRP') ?></th>
                                        <?php } ?>
                                        
                                        <?php if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
                                        <th style="width: 110px;"><?= lang('Price') ?></th>
                                        <?php } ?>
                                        <th class="discount-on-mrp-col" style="width: 90px;"><?= lang('Dis') ?></th>

                                        <?php if ($Settings->pos_type == 'restaurant') { ?>
                                        <th style="width: 140px;"><?= lang('Urbanpiper_Price_Addition') ?></th>
                                        <?php } ?>
                                        <th style="width: 90px;"><?= lang('Unit_Qty') ?></th>
                                        <th style="width: 120px;"><?= lang('Unit_Wgt(In KG)') ?></th>
                                        <th style="width: 50px;"><i class="fa fa-trash attr-remove-all"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        ////////////////////////// Mrp , Price , Cost//////////////////////////////////
                                        
                                        foreach ($product_variants as $pv) {
                                            
                                            
                                            $pv_checked = ($product->primary_variant == $pv->id) ? ' checked="checked" ' : '';
                                            echo '<tr class="variant-row" data-id="' . $pv->id . '">'
                                            . '<td title="Set Primary Variable"><input type="radio" name="primary_variant" value="' . $pv->id . '" '.$pv_checked.'/></td>'
                                            . '<td><input type="hidden" name="variant_id_' . $pv->id . '" value="' . $pv->id . '"><input type="text" name="variant_name_' . $pv->id . '" value="' . $pv->name . '" class="form-control"></td>';
                                            
                                            if ($Owner || $Admin || $this->session->userdata('show_cost')) {
                                                echo '<td><input type="number" step="0.01" name="variant_cost_' . $pv->id . '" value="' . rtrim(rtrim((string)$pv->cost, '0'), '.') . '" class="form-control attr-input variant_cost"></td>';
                                            }
                                            
                                            if ($Owner || $Admin || $this->session->userdata('show_mrp')) {
                                                echo '<td><input type="number" step="0.01" name="variant_mrp_' . $pv->id . '" value="' . rtrim(rtrim((string)$pv->mrp, '0'), '.') . '" class="form-control attr-input variant_mrp"></td>';
                                            }
                                            if ($Owner || $Admin || $this->session->userdata('show_price')) {
                                                echo '<td><input type="number" step="0.01" name="variant_price_' . $pv->id . '" value="' . rtrim(rtrim((string)$pv->price, '0'), '.') . '" class="form-control attr-input variant_price"></td>';
                                            }
                                            $discount_value = $pv->variant_discount_on_mrp;

                                                // // Check if the discount is a percentage (contains '%') or fixed amount
                                                // $discount_value = (strpos($discount_value, '%') !== false) 
                                                //     ? $discount_value  // If it's a percentage, leave as-is
                                                //     : number_format($discount_value, 2); // If it's a fixed amount, format it to 2 decimals
                                                    if (strpos($discount_value, '%') !== false) {
                                                    } else {
                                                        if (is_numeric($discount_value)) {
                                                            $discount_value = (float) $discount_value;
                                                            $discount_value = ($discount_value == 0) ? '0' : rtrim(rtrim((string) $discount_value, '0'), '.');
                                                        }
                                                    }
                                                    

                                                echo '<td class="discount-on-mrp-col"><input type="text" name="variant_discount_' . $pv->id . '" value="' . ($enable_discount_on_mrp ? $discount_value : '0%') . '" class="form-control attr-input variant_discount "></td>';
                                            

                                        if ($Settings->pos_type == 'restaurant') { 
                                            echo '<td><input type="text" name="variant_upprice_' . $pv->id . '" value="' . $pv->up_price . '" class="form-control"></td>';
                                           }
                                            echo  '<td><input type="number" step="0.1" name="unit_quantity_' . $pv->id . '" value="' . number_format((float)$pv->unit_quantity, 2, ".", "") . '" class="form-control attr-input unit_quantity"></td>'
                                            . '<td><input type="number" step="0.1" name="unit_weight_' . $pv->id . '" value="' . number_format((float)$pv->unit_weight, 2, ".", "") . '" class="form-control attr-input unit_weight"></td>'
                                            . '<td class="text-center"> <a href="javascript:void(0);" class="DeleteVarient" onclick="return deleteVarient(' . $pv->id . ');" title="Delete"><i class="fa fa-trash" aria-hidden="true " style="cursor: pointer;" id="row_'.$pv->id.'"></i></a></td>'
                                            . '</tr>';
                                        }
                                        ?>
                                </tbody>
                            </table>
                            <?php
                            }
                            ?>
                            <?php
                            $has_no_size_variants = empty($product_variants);
                            $show_attr_section = $this->input->post('attributes') || $this->input->post('has_new_variants') || ($has_no_size_variants && ($this->input->post('attr_name') || $this->input->post('attributesInput')));
                            ?>
                            <div class="form-group variant-label variant-container">
                                <input type="checkbox" class="checkbox" name="attributes" id="attributes" value="1"
                                    <?= $show_attr_section ? 'checked="checked"' : ''; ?>>
                                <label for="attributes" class="padding05"><?= $has_no_size_variants ? lang('product_has_attributes') : lang('add_more_variants'); ?></label>
                                <?php if ($has_no_size_variants) { ?>
                                <br /><span class="text-info">Ex. Sizes, Models or Weight</span>
                                <?php } ?>
                            </div>

                            <div id="attr-con" <?= $show_attr_section ? '' : 'style="display:none;"'; ?>>
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
                                    <div class="table-responsive-scroll">
                                        <table id="attrTable" class="table table-bordered table-condensed table-striped"
                                            style="margin-bottom: 0; margin-top: 10px;">

                                            <thead>
                                                <tr class="active">
                                                    <th><?= lang('Name') ?></th>
                                                    <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
                                                    <th><?= lang('Cost') ?></th>
                                                    <?php } ?>
                                                    <?php if ($Owner || $Admin || $this->session->userdata('show_mrp')) { ?>
                                                    <th><?= lang('MRP') ?></th>
                                                    <?php } ?>
                                                    <?php if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
                                                    <th><?= lang('Price') ?></th>
                                                    <?php } ?>
                                                    <th class="discount-on-mrp-col"><?= lang('Dis') ?></th>
                                                    <?php if ($Settings->pos_type == 'restaurant') { ?>
                                                    <th><?= lang('Urbanpier_Price_Addition') ?></th>
                                                    <?php } ?>
                                                    <th><?= lang('Unit Qty') ?></th>
                                                    <th><?= lang('Unit Wgt') ?></th>
                                                    <th><i class="fa fa-times attr-remove-all"></i></th>
                                                </tr>
                                            </thead>

                                            <tbody><?php
                                            if ($this->input->post('attributes') || $this->input->post('attr_name')) {
                                                $a = sizeof($_POST['attr_name']);
                                                for ($r = 0; $r <= $a; $r++) {
                                                    if (isset($_POST['attr_name'][$r]) && trim($_POST['attr_name'][$r]) !== '') {
                                                        echo '<tr class="attr">
                                                            <td><input type="hidden" class="attr_name" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span>
                                                            <input type="hidden" name="attr_warehouse[]" value="' . (isset($_POST['attr_warehouse'][$r]) ? $_POST['attr_warehouse'][$r] : '') . '">
                                                            <input type="hidden" name="attr_wh_name[]" value="' . (isset($_POST['attr_wh_name'][$r]) ? $_POST['attr_wh_name'][$r] : '') . '">
                                                            <input type="hidden" name="attr_quantity[]" value="' . (isset($_POST['attr_quantity'][$r]) ? $_POST['attr_quantity'][$r] : '0') . '"></td>';
                                                            
                                                            if ($Owner || $Admin || $this->session->userdata('show_cost')) {
                                                                echo '<td class="cost text-right"><input type="text" name="attr_cost[]" value="' . (isset($_POST['attr_cost'][$r]) ? $_POST['attr_cost'][$r] : '') . '"><span>' . (isset($_POST['attr_cost'][$r]) ? $_POST['attr_cost'][$r] : '') . '</span></span></td>';
                                                            }
                                                            
                                                            if ($Owner || $Admin || $this->session->userdata('show_mrp')) {
                                                                echo '<td class="variantmrp text-right"><input type="text" name="attr_mrp[]" value="' . (isset($_POST['attr_mrp'][$r]) ? $_POST['attr_mrp'][$r] : '') . '"><span>' . (isset($_POST['attr_mrp'][$r]) ? $_POST['attr_mrp'][$r] : '') . '</span></span></td>';
                                                            }
                                                            if ($Owner || $Admin || $this->session->userdata('show_price')) {
                                                                echo '<td class="price text-right"><input type="text" name="attr_price[]" value="' . (isset($_POST['attr_price'][$r]) ? $_POST['attr_price'][$r] : '') . '"><span>' . (isset($_POST['attr_price'][$r]) ? $_POST['attr_price'][$r] : '') . '</span></span></td>';
                                                            }

                                                            if ($Settings->pos_type == 'restaurant') {
                                                                echo '<td class="upprice text-right"><input type="text" name="attr_upprice[]" value="' . $_POST['attr_upprice'][$r] . '"><span>' . $_POST['attr_upprice'][$r] . '</span></span></td>';
                                                            }

                                                            echo '<td class="unit_quantity text-center"><input type="text" name="attr_unit_quantity[]" value="' . $_POST['attr_unit_quantity'][$r] . '"><span>' . $_POST['attr_unit_quantity'][$r] . '</span></td>
                                                            <td class="unit_weight text-center"><input type="text" name="attr_unit_weight[]" value="' . $_POST['attr_unit_weight'][$r] . '"><span>' . $_POST['attr_unit_weight'][$r] . '</span></td>
                                                            <td class="text-center"><i class="fa fa-times delAttr"></i></td>
                                                         </tr>';
                                                    }
                                                }
                                            }
                                            ?></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="color-box">
                            <h2>Color:</h2>
                            <?php
                                    $colorarray = array();
                                    $colorNameMap = array();
                                    if (isset($variants_color)) {
                                        foreach ($variants_color as $vc) {
                                            if (isset($vc->id)) {
                                                $colorNameMap[(string) $vc->id] = $vc->name;
                                            }
                                        }
                                    }
                                    if (isset($product_options_color)) {
                                        foreach ($product_options_color as $color_attr) {
                                            $raw = $color_attr->name;
                                            $parts = explode(',', $raw);
                                            foreach ($parts as $part) {
                                                $part = trim($part);
                                                if ($part === '') {
                                                    continue;
                                                }
                                                $name = $part;
                                                if (isset($colorNameMap[$part])) {
                                                    $name = $colorNameMap[$part];
                                                }
                                                $normalized = strtolower(trim($name));
                                                $colorarray[$normalized] = $normalized;
                                            }
                                        }
                                    }
                                ?>
                            <select class="form-control" name="AttrColor[]">
                                <option value=""> -- Select Color -- </option>
                                <?php foreach($variants_color as $color_attr){ ?>
                                <?php $color_option_normalized = strtolower(trim($color_attr->name)); ?>
                                <option value="<?= $color_attr->name ?>"
                                    <?= in_array($color_option_normalized,$colorarray)?'Selected' :'' ?>><?= $color_attr->name ?>
                                </option>
                                <?php } ?>
                            </select>

                        </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="combo" style="display:none;">

                        <div class="form-group">
                            <!-- <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?> -->
                            <?= lang("add_product", "add_item"); ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>
                            <!--<div class="row"><div class="ccol-md-10 col-sm-10 col-xs-10"><label class="table-label" for="combo"><?= lang("combo_products"); ?></label></div>
                            <div class="ccol-md-2 col-sm-2 col-xs-2"><div class="form-group no-help-block" style="margin-bottom: 0;"><input type="text" name="combo" id="combo" value="" data-bv-notEmpty-message="" class="form-control" /></div></div></div>-->
                            <div class="controls table-controls">
                                <table id="prTable"
                                    class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th class="col-md-5 col-sm-5 col-xs-5">
                                                <?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?>
                                            </th>
                                            <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                            <th class="col-md-3 col-sm-3 col-xs-3 bundel" style="display:none;">
                                                <?= lang("unit_price"); ?></th>
                                            <th class="col-md-3 col-sm-3 col-xs-3 bundel" style="display:none;">
                                                <?= lang("Price"); ?></th>
                                            <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                                <i class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i>
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
                            <input id="digital_file" type="file" data-browse-label="<?= lang('browse'); ?>"
                                name="digital_file" data-show-upload="false" data-show-preview="false"
                                class="form-control file">
                        </div>
                    </div>

                    <div class="form-group standard">
                        <div class="form-group">
                            <?= lang("supplier", "supplier") ?>
                            <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i
                                    class="fa fa-plus"></i>
                            </button>
                        </div>
                        <!--
                        <div class="col-xs-12">
                                <div class="form-group">
                        <?php
                        echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                        ?>
                                </div>
                        </div>
                        -->
                        <div class="row" id="supplierrow_1">
                            <!--  supplier_con-->
                            <div style="width:102%;">
                                <div class="col-xs-11">
                                    <div class="form-group">
                                        <?php
                                        echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-xs">
                                    <i class="fa fa-times deleteSupplier" id="1" style="cursor:pointer;"></i>
                                </button>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <?= form_input('supplier_part_no', (isset($_POST['supplier_part_no']) ? $_POST['supplier_part_no'] : ""), 'class="form-control tip" id="supplier_part_no" placeholder="' . lang('supplier_part_no') . '"'); ?>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <?= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ""), 'class="form-control tip" id="supplier_price" placeholder="' . lang('supplier_price') . '"'); ?>
                                </div>
                            </div>
                        </div>
                        <div id="ex-suppliers"></div>
                    </div>

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
                        <input name="cf" type="checkbox" class="checkbox" id="extras" value="1"
                            <?= ($active_cf) ? 'checked="checked" disabled="disabled"' : '' ?> />
                        <label for="extras" class="padding05"><?= lang('custom_fields') ?></label>
                    </div>

                    <div class="row" id="extras-con"
                        style="<?=  ($active_cf) ? 'display: block;' : 'display: none;' ?>">
                        <div class="well well-sm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group all">
                                        <?php  echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'pcf1') : lang('pcf1', 'pcf1')) ?>
                                        <?php                        
                                if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {                            
                                    echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)) , (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control tip" id="cf1"'. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control" id="cf1" '. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); 
                                }
                                ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group all">
                                        <?php  echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'pcf2') : lang('pcf2', 'pcf2')) ?>
                                        <?php                        
                                if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {                            
                                    echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)) , (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control tip" id="cf2"'. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control" id="cf2" '. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                }  ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group all">
                                        <?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'pcf3') : lang('pcf3', 'pcf3')) ?>
                                        <?php                        
                                if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {                            
                                    echo form_dropdown('cf3', (json_decode($custome_fields->cf3_input_options, TRUE)) , (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control tip" id="cf3"'. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control" id="cf3" '. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); 
                                }    
                                ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group all">
                                        <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'pcf4') : lang('pcf4', 'pcf4')) ?>
                                        <?php                        
                                if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {                            
                                    echo form_dropdown('cf4', (json_decode($custome_fields->cf4_input_options, TRUE)) , (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control tip" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); 
                                }   
                                ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group all">
                                        <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'pcf5') : lang('pcf5', 'pcf5')) ?>
                                        <?php                        
                                if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {                            
                                    echo form_dropdown('cf5', (json_decode($custome_fields->cf5_input_options, TRUE)) , (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control tip" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); 
                                }    
                                ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group all">
                                        <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'pcf6') : lang('pcf6', 'pcf6')) ?>
                                        <?php                        
                                if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {                            
                                    echo form_dropdown('cf6', (json_decode($custome_fields->cf6_input_options, TRUE)) , (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control tip" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); 
                                }   
                                ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="product_details"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details_for_invoice", "details") ?>
                        <?= form_textarea('details', (isset($_POST['details']) ? $_POST['details'] : ($product ? $product->details : '')), 'class="form-control" id="details"'); ?>
                    </div>

                    <div class="form-group">
                        <button type="button" id="custom-submit-edit" class="btn btn-primary"><?= $this->lang->line("edit_product") ?></button>
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
function smaProductEditSafeBv(method, field) {
    var $f = $('#product-edit-form').length ? $('#product-edit-form') : $('form[data-toggle="validator"]');
    if (!$f.length || !$f.data('bootstrapValidator')) {
        return;
    }
    try {
        $f.bootstrapValidator(method, field);
    } catch (e) {}
}
$(document).ready(function() {
    $.ajaxSetup({
        beforeSend: function(jqXHR, settings) {
            if (settings.url.includes("products/suggestions")) {
                const type = $('#type').val();
                const url = new URL(settings.url, window.location.origin);
                url.searchParams.set('type', type);
                settings.url = url.toString();
            }
        }
    });
});
$(document).ready(function() {
    var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    var items = {};
    <?php
if ($combo_items) {
    echo '
                var ci = ' . json_encode($combo_items) . ';
                $.each(ci, function() { add_product_item(this); });
                ';
}
?>
    <?= isset($_POST['cf']) ? '$("#extras").iCheck("check");' : '' ?>
    // $('#extras').on('ifChecked', function() {
    //     $('#extras-con').slideDown();
    // });
    // $('#extras').on('ifUnchecked', function() {
    //     $('#extras-con').slideUp();
    // });

    $(document).on('ifChecked', '#extras', function() {
        $('#extras-con').slideDown();
    }).on('ifUnchecked', '#extras', function() {
        $('#extras-con').slideUp();
    });

    <?= isset($_POST['promotion']) || $product->promotion ? '$("#promotion").iCheck("check");' : '' ?>
    $('#promotion').on('ifChecked', function(e) {
        $('#promo').slideDown();
    });
    $('#promotion').on('ifUnchecked', function(e) {
        $('#promo').slideUp();
    });

    $('.attributes').on('ifChecked', function(event) {
        $('#options_' + $(this).attr('id')).slideDown();
    });
    $('.attributes').on('ifUnchecked', function(event) {
        $('#options_' + $(this).attr('id')).slideUp();
    });
    //$('#cost').removeAttr('required');
    $('#type').change(function() {
        var t = $(this).val();
        $('.bundel').show();
        if (t !== 'standard') {
            $('.standard').slideUp();
            $('#unit').removeAttr('required');
            smaProductEditSafeBv('removeField', 'unit');
            if ($('#cost').length) {
                $('#cost').attr('required', 'required');
                smaProductEditSafeBv('addField', 'cost');
            }
        } else {
            $('.standard').slideDown();
            $('#unit').attr('required', 'required');
            smaProductEditSafeBv('addField', 'unit');
            if ($('#cost').length) {
                $('#cost').removeAttr('required');
                smaProductEditSafeBv('removeField', 'cost');
            }
        }
        if (t !== 'digital') {
            $('.digital').slideUp();
            $('#digital_file').removeAttr('required');
            smaProductEditSafeBv('removeField', 'digital_file');
        } else {
            $('.digital').slideDown();
            $('#digital_file').attr('required', 'required');
            smaProductEditSafeBv('addField', 'digital_file');
        }
        if (t !== 'combo') {
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

    $("#add_item").autocomplete({
        source: '<?= site_url('products/suggestions'); ?>',
        minLength: 1,
        autoFocus: false,
        delay: 5,
        response: function(event, ui) {
            if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                //audio_error.play();
                bootbox.alert('<?= lang('no_product_found') ?>', function() {
                    $('#add_item').focus();
                });
                $(this).val('');
            } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
            } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                //audio_error.play();
                bootbox.alert('<?= lang('no_product_found') ?>', function() {
                    $('#add_item').focus();
                });
                $(this).val('');

            }
        },
        select: function(event, ui) {
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
                    $('#add_item').removeAttr('required');
                    smaProductEditSafeBv('removeField', 'add_item');
                }
            } else {
                //audio_error.play();
                bootbox.alert('<?= lang('no_product_found') ?>');
            }
        }
    });
    $('#add_item').removeAttr('required');
    smaProductEditSafeBv('removeField', 'add_item');

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
        $("#prTable tbody").empty();
        $.each(items, function() {
            var selectedValue = document.getElementById("type").value;
            var row_no = this.id;
            var displayName = this.name + ' (' + this.code + ')';
            if (this.product_name) {
                displayName = this.product_name + ' (' + this.name + ') (' + this.code + ')';
            }
            var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '"></tr>');
            tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id +
                '"><input name="combo_item_name[]" type="hidden" value="' + this.name +
                '"><input name="combo_item_code[]" type="hidden" value="' + this.code +
                '"><span id="name_' + row_no + '">' + displayName + '</span></td>';
            tr_html +=
                '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' +
                formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id +
                '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
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
            tr_html += '<input name="combo_item_variant_id[]" type="hidden" value="' + (this
                .variant_id ? this.variant_id : '') + '">';
            tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no +
                '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#prTable");
        });
        $('.item_' + item_id).addClass('warning');
        return true;

    }
    window.addProductToVarientProduct = function(option_id, option_name, currentType) {
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
                product_options += '<button onclick="addProductToVarientProduct(\'' + element.id +
                    '\',\'' +
                    element.name + '\')" type="button"  title="' + element.name +
                    '" class="btn-prni btn-info pos-tip" tabindex="-1"><img src="assets/mdata/' +
                    subdomain +
                    '/uploads/thumbs/no_image.png" alt="' + element.name +
                    '" style="width:33px;height:33px;" class="img-rounded"><span>' + element.name +
                    '</span></button>';
            }
        });
        product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" +
            product.id + "' >";
        product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product
            .code +
            "' >";
        $('.modalvarient').find('.modal-title').html(product.name);
        $('.modalvarient').find('.modal-body').empty();
        $('.modalvarient').find('.modal-body').append(product_options);
        $('.modalvarient').show();
        return true;
    }
    window.modalClose = function(modalClass) {
        $('.' + modalClass).hide();
    };

    function calculate_price() {

        var rows = $('#prTable').children('tbody').children('tr');
        var pp = 0;
        var row_total = 0
        $.each(rows, function() {
            row_total = formatDecimal(parseFloat($(this).find('.rprice').val()) * parseFloat($(this)
                .find('.rquantity').val()));
            $(this).find('.rtprice').val(row_total);
            //console.log(this);
            //alert(row_total);
            pp += row_total;
        });
        //console.log(pp);
        //$('#price').val(pp);
        return true;
    }

    $(document).on('change textchange', '.rquantity, .rprice', function() {
        calculate_price();
    });

    $(document).on('click', '.del', function() {
        var id = $(this).attr('id');
        $(this).closest('#row_' + id).remove();
        $.each(items, function(i, v) {
            if (v.id == id) {
                delete items[i];
            }
        });
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

    $('#addSupplier').click(function() { //onClick="delete_supplier(' + su + ')"
        if (su <= 5) {
            //$('#supplier_1').select2('destroy');//style="width:100%;display: block !important;"
            var html = '<div style="clear:both;height:5px;" ></div><div class="row" id="supplierrow_' +
                su +
                '"><div><div class="col-xs-11"><div class="form-group"><input type="hidden" name="supplier_' +
                su + '", class="form-control" id="supplier_' + su +
                '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>"  /></div></div><div><button type="button" class="btn btn-primary btn-xs" ><i class="fa fa-times deleteSupplier"  id="' +
                su +
                '"  style="cursor:pointer;"></i></button></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' +
                su + '_part_no" class="form-control tip" id="supplier_' + su +
                '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' +
                su + '_price" class="form-control tip" id="supplier_' + su +
                '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
            $('#ex-suppliers').append(html);
            var sup = $('#supplier_' + su);
            suppliers(sup);
            su++;
        } else {
            bootbox.alert('<?= lang('max_reached') ?>');
            return false;
        }
    });


    $(document).on('click', '.deleteSupplier', function() {
        var id = $(this).attr('id');
        console.log(id);
        su--;
        $(this).closest('#supplierrow_' + id).remove();
    });



    var _URL = window.URL || window.webkitURL;
    $("input#images").on('change.bs.fileinput', function() {
        var ele = document.getElementById($(this).attr('id'));
        var result = ele.files;
        $('#img-details').empty();
        for (var x = 0; x < result.length; x++) {
            var fle = result[x];
            for (var i = 0; i <= result.length; i++) {
                var img = new Image();
                img.onload = (function(value) {
                    return function() {
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
    $(document).on('ifChecked', '#attributes', function(e) {
        $('#attr-con').slideDown();
        $('#has_new_variants').val('1');
        $('#price').val('00');
        $('#mrp').val('00');
        $('#discount_on_mrp').val('0%');
        $('#price').attr('readonly', true);
        $('#mrp').attr('readonly', true);
        $('#discount_on_mrp').attr('readonly', true);
    });
    $(document).on('ifUnchecked', '#attributes', function(e) {
        $('#has_new_variants').val('0');
        $(".select-tags").select2("val", "");
        $('.attr-remove-all').trigger('click');
        $('#attr-con').slideUp();
    });
    var existvariants = <?= json_encode($vars); ?>;

    $('#addAttributes').click(function(e) {
        e.preventDefault();
        var attrs_val = $('#attributesInput').val(),
            attrs;
        attrs = attrs_val.split(',').map(s => s.trim()).filter(s => s !== '');

        var wh_arr = [];
        var attr_arr = [];
        if ($.trim($('#attrTable tbody').html()) != '') {
            $.each($(".attr_name"), function(index, ele) {
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
            bootbox.alert('"' + invalidAttrs.join(', ') +
                '" is not a valid variant. Please add it in the variants screen first.');
        }

        for (var i in validAttrs) {
            var attrTrimmed = validAttrs[i];

            if (attrTrimmed !== '' && $.inArray(attrTrimmed, attr_arr_unique) === -1) {
                <?php if ($Settings->pos_type == 'restaurant') { ?>
                $('#attrTable').show().append(
                    '<tr class="attr">' +
                    '<td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrTrimmed + '"><span>' + attrTrimmed + '</span></td>' +
                    <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
                    '<td class="cost text-right" id="variant_cost">' +
                    '<input type="number" class="form-control cost text-right variant_cost" name="attr_cost[]" value="0">' +
                    '</td>' +
                    <?php } ?>
                    <?php if ($Owner || $Admin || $this->session->userdata('show_mrp')) { ?>
                    '<td class="variantmrp text-right" id="variant_mrp">' +
                    '<input type="number" class="form-control variantmrp text-right variant_mrp" name="attr_mrp[]" value="0">' +
                    '</td>' +
                    <?php } ?>
                    <?php if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
                    '<td class="price text-right" id="variant_price">' +
                    '<input type="number" class="form-control variantprice text-right variant_price" name="attr_price[]" value="0">' +
                    '</td>' +
                    <?php } ?>
                    '<td class="variantdiscount text-right discount-on-mrp-col" id="variant_discount">' +
                    '<input type="text" class="form-control variantdiscount variant_discount text-right" name="attr_discount[]" value="0%" oninput="updateHiddenInput(this, \'attr_discount\')">' +
                    '</td>' +
                    '<td class="upprice text-right">' +
                    '<input type="number" class="form-control upprice text-right" name="attr_upprice[]" value="0" oninput="updateHiddenInput(this, \'attr_upprice\')">' +
                    '</td>' +
                    '<td class="unit_quantity text-right">' +
                    '<input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" min="0" max ="100" oninput="updateHiddenInput(this, \'attr_unit_quantity\')">' +
                    '</td>' +
                    '<td class="unit_weight text-right">' +
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
                    <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
                    '<td class="cost text-right" id="variant_cost">' +
                    '<input type="number" class="form-control cost text-right variant_cost" name="attr_cost[]" value="0" min = "0">' +
                    '</td>' +
                    <?php } ?>
                    <?php if ($Owner || $Admin || $this->session->userdata('show_mrp')) { ?>
                    '<td class="variantmrp text-right" id="variant_mrp">' +
                    '<input type="number" class="form-control variantmrp text-right variant_mrp" name="attr_mrp[]" value="0"  min = "0">' +
                    '</td>' +
                    <?php } ?>
                    <?php if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
                    '<td class="price text-right"  id="variant_price">' +
                    '<input type="number" class="form-control variantprice text-right variant_price" name="attr_price[]" value="0"  min="0">' +
                    '</td>' +
                    <?php } ?>
                    '<td class="variantdiscount text-right discount-on-mrp-col" id="variant_discount">' +
                    '<input type="text" class="form-control variantdiscount variant_discount text-right" name="attr_discount[]" value="0%" oninput="updateHiddenInput(this, \'attr_discount\')">' +
                    '</td>' +
                    '<td class="unit_quantity text-right">' +
                    '<input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" min = "0" max ="100" oninput="updateHiddenInput(this, \'attr_unit_quantity\')">' +
                    '</td>' +
                    '<td class="unit_weight text-right">' +
                    '<input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="0"  min = "0" oninput="updateHiddenInput(this, \'attr_unit_weight\')">' +
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
    $(document).on('click', '.delAttr', function() {
        $(this).closest("tr").remove();
    });
    $(document).on('click', '.attr-remove-all', function() {
        $('#attrTable tbody').empty();
        $('#attrTable').hide();
    });
    var row, warehouses = <?= json_encode($warehouses); ?>;
    // $(document).on('click', '.attr td:not(:last-child)', function () {
    $(document).on('click', '.attr td:first-child', function(event) {

        row = $(this).closest("tr");
        $('#aModalLabel').text(row.children().eq(0).find('span').text());
        //            $('#awarehouse').select2("val", (row.children().eq(1).find('input').val()));
        //            $('#aquantity').val(row.children().eq(2).find('span').text());
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
        $('#aModal').appendTo('body').modal('show');
    });

    $(document).on('click', '#updateAttr', function() {
        //            var wh = $('#awarehouse').val(), wh_name;
        //            $.each(warehouses, function () {
        //                if (this.id == wh) {
        //                    wh_name = this.name;
        //                }
        //            });
        // row.children().eq(1).html('<input type="hidden" name="attr_warehouse[]" value="' + wh + '"><input type="hidden" name="attr_wh_name[]" value="' + wh_name + '"><span>' + wh_name + '</span>');
        //  row.children().eq(2).html('<input type="hidden" name="attr_quantity[]" value="' + $('#aquantity').val() + '"><span>' + $('#aquantity').val() + '</span>');
        row.children().eq(1).html('<input type="text" class="text-right" name="attr_cost[]" value="' +
            $('#acost').val() + '" size="1">');
        row.children().eq(2).html('<input type="text" class="text-right" name="attr_mrp[]" value="' + $(
            '#amrp').val() + '" size="1">');
        row.children().eq(3).html('<input type="text" class="text-right" name="attr_price[]" value="' +
            $('#aprice').val() + '" size="1">');
        row.children().eq(4).html(
            '<input type="text" class="text-right" name="attr_discount[]" value="' + $('#adiscount')
            .val() + '" size="1">');
        <?php if ($Settings->pos_type == 'restaurant') { ?>
        row.children().eq(5).html('<input type="text" name="attr_upprice[]" value="' + $('#aupprice')
            .val() + '"size="1">');
        row.children().eq(6).html('<input type="text" name="attr_unit_quantity[]" value="' + $(
            '#aunit_quantity').val() + '"size="1">');
        row.children().eq(7).html('<input type="text" name="attr_unit_weight[]" value="' + $(
            '#aunit_weight').val() + '"size="1">');
        <?php }else{ ?>
        row.children().eq(5).html('<input type="text" name="attr_unit_quantity[]" value="' + $(
            '#aunit_quantity').val() + '"size="1">');
        row.children().eq(6).html('<input type="text" name="attr_unit_weight[]" value="' + $(
            '#aunit_weight').val() + '"size="1">');
        <?php } ?>
        $('#aModal').modal('hide');
    });
});

<?php if ($product) { ?>
$(document).ready(function() {
    $('#enable_wh').click(function() {
        var whs = $('.wh');
        $.each(whs, function() {
            $(this).val($('#v' + $(this).attr('id')).val());
        });
        $('#warehouse_quantity').val(1);
        $('.wh').attr('disabled', false);
        $('#show_wh_edit').slideDown();
    });
    $('#disable_wh').click(function() {
        $('#warehouse_quantity').val(0);
        $('#show_wh_edit').slideUp();
    });
    $('#show_wh_edit').hide();
    $('.wh').attr('disabled', true);
    var t = "<?= $product->type ?>";
    if (t !== 'standard') {
        $('.standard').slideUp();
    } else {
        $('.standard').slideDown();
    }
    if (t !== 'digital') {
        $('.digital').slideUp();
        $('#digital_file').removeAttr('required');
    } else {
        $('.digital').slideDown();
    }
    if (t !== 'combo') {
        $('.combo').slideUp();
    } else {
        $('.combo').slideDown();
    }
    if (t == 'Bundle') {
        $('.combo').slideDown();
        $('.bundel').hide();
    } else {
        $('.bundel').show();
    }
    function smaProductEditRunBvForLoadedType() {
        var pt = "<?= $product->type ?>";
        if (pt !== 'standard') {
            $('#unit').removeAttr('required');
            smaProductEditSafeBv('removeField', 'unit');
            if ($('#cost').length) {
                $('#cost').attr('required', 'required');
                smaProductEditSafeBv('addField', 'cost');
            }
        } else {
            $('#unit').attr('required', 'required');
            smaProductEditSafeBv('addField', 'unit');
            if ($('#cost').length) {
                $('#cost').removeAttr('required');
                smaProductEditSafeBv('removeField', 'cost');
            }
        }
        if (pt !== 'digital') {
            smaProductEditSafeBv('removeField', 'digital_file');
        } else {
            $('#digital_file').attr('required', 'required');
            smaProductEditSafeBv('addField', 'digital_file');
        }
        $('#add_item').removeAttr('required');
        smaProductEditSafeBv('removeField', 'add_item');
    }
    setTimeout(smaProductEditRunBvForLoadedType, 0);
    setTimeout(smaProductEditRunBvForLoadedType, 250);
    //$("#code").parent('.form-group').addClass("has-error");
    //$("#code").focus();
    $("#product_image").parent('.form-group').addClass("text-warning");
    $("#images").parent('.form-group').addClass("text-warning");
    $.ajax({
        type: "get",
        async: false,
        url: "<?= site_url('products/getSubCategories') ?>/" + <?= $product->category_id ?>,
        dataType: "json",
        success: function(scdata) {
            if (scdata != null) {
                $("#subcategory").select2("destroy").empty().attr("placeholder",
                    "<?= lang('select_subcategory') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: scdata
                });
            }
        }
    });
    <?php if ($product->supplier1) { ?>
    select_supplier('supplier1', "<?= $product->supplier1; ?>");
    $('#supplier_price').val("<?= $this->sma->formatDecimal($product->supplier1price); ?>");
    $('#supplier_part_no').val("<?= $product->supplier1_part_no; ?>");
    <?php } else { ?>
    $('#supplier1').addClass('rsupplier');
    <?php } ?>
    <?php if ($product->supplier2) { ?>
    $('#addSupplier').click();
    select_supplier('supplier_2', "<?= $product->supplier2; ?>");
    $('#supplier_2_price').val("<?= $this->sma->formatDecimal($product->supplier2price); ?>");
    $('#supplier_2_part_no').val("<?= $product->supplier2_part_no; ?>");
    <?php } ?>
    <?php if ($product->supplier3) { ?>
    $('#addSupplier').click();
    select_supplier('supplier_3', "<?= $product->supplier3; ?>");
    $('#supplier_3_price').val("<?= $this->sma->formatDecimal($product->supplier3price); ?>");
    $('#supplier_3_part_no').val("<?= $product->supplier3_part_no; ?>");
    <?php } ?>
    <?php if ($product->supplier4) { ?>
    $('#addSupplier').click();
    select_supplier('supplier_4', "<?= $product->supplier4; ?>");
    $('#supplier_4_price').val("<?= $this->sma->formatDecimal($product->supplier4price); ?>");
    $('#supplier_4_part_no').val("<?= $product->supplier4_part_no; ?>");
    <?php } ?>
    <?php if ($product->supplier5) { ?>
    $('#addSupplier').click();
    select_supplier('supplier_5', "<?= $product->supplier5; ?>");
    $('#supplier_5_price').val("<?= $this->sma->formatDecimal($product->supplier5price); ?>");
    $('#supplier_5_part_no').val("<?= $product->supplier5_part_no; ?>");
    <?php } ?>

    function select_supplier(id, v) {
        $('#' + id).val(v).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function(element, callback) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function(data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return {
                            results: data.results
                        };
                    } else {
                        return {
                            results: [{
                                id: '',
                                text: 'No Match Found'
                            }]
                        };
                    }
                }
            }
        });
    }
});
<?php } ?>
$(document).ready(function() {
    $('#enable_wh').trigger('click');
    $('#unit').change(function(e) {
        var v = $(this).val();
        if (v) {
            $.ajax({
                type: "get",
                async: false,
                url: "<?= site_url('products/getSubUnits') ?>/" + v,
                dataType: "json",
                success: function(data) {
                    $('#default_sale_unit').select2("destroy").empty().select2({
                        minimumResultsForSearch: 7
                    });
                    $('#default_purchase_unit').select2("destroy").empty().select2({
                        minimumResultsForSearch: 7
                    });
                    $.each(data, function() {
                        $("<option />", {
                            value: this.id,
                            text: this.name + ' (' + this.code + ')'
                        }).appendTo($('#default_sale_unit'));
                        $("<option />", {
                            value: this.id,
                            text: this.name + ' (' + this.code + ')'
                        }).appendTo($('#default_purchase_unit'));
                    });
                    $('#default_sale_unit').select2('val', v);
                    $('#default_purchase_unit').select2('val', v);
                },
                error: function() {
                    bootbox.alert('<?= lang('ajax_error') ?>');
                }
            });
        } else {
            $('#default_sale_unit').select2("destroy").empty();
            $('#default_purchase_unit').select2("destroy").empty();
            $("<option />", {
                value: '',
                text: '<?= lang('select_unit_first') ?>'
            }).appendTo($('#default_sale_unit'));
            $("<option />", {
                value: '',
                text: '<?= lang('select_unit_first') ?>'
            }).appendTo($('#default_purchase_unit'));
            $('#default_sale_unit').select2({
                minimumResultsForSearch: 7
            }).select2('val', '');
            $('#default_purchase_unit').select2({
                minimumResultsForSearch: 7
            }).select2('val', '');
        }
    });
});
</script>
<?php if (!empty($Settings->display_job_work)) { ?>
<div class="modal fade" id="dccStageModal" tabindex="-1" role="dialog" aria-labelledby="dccStageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="dccStageModalLabel">Direct Cost Components</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Intermediate Stage Input</th>
                                <th>Process</th>
                                <th>Output</th>
                            </tr>
                        </thead>
                        <tbody id="dccStageTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div class="modal" id="aModal" tabindex="-1" role="dialog" aria-labelledby="aModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="aModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <!--                    <div class="form-group">
                        <label for="awarehouse" class="col-sm-4 control-label"><?= lang('warehouse') ?></label>
                        <div class="col-sm-8">
                            <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, '', 'id="awarehouse" class="form-control"');
                            ?>
                        </div>
                    </div>-->
                    <!--                     <div class="form-group">
                                             <label for="aquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                                             <div class="col-sm-8">
                                                 <input type="text" class="form-control" id="aquantity">
                                             </div>
                                         </div> -->
                    <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
                    <div class="form-group">
                        <label for="acost" class="col-sm-4 control-label"><?= lang('Cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="acost"
                                onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorc" style="color:#a94442; display: none;font-size:11px;">please enter numbers
                                only</span>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ($Owner || $Admin || $this->session->userdata('show_mrp')) { ?>
                    <div class="form-group">
                        <label for="amrp" class="col-sm-4 control-label"><?= lang('MRP') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="amrp"
                                onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please enter numbers
                                only</span>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ($Owner || $Admin || $this->session->userdata('show_price')) { ?>
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('Price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice" min="0"
                                onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorp" style="color:#a94442; display: none;font-size:11px;">please enter numbers
                                only</span>
                        </div>
                    </div>
                    <?php } ?>
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
                        <label for="aupprice"
                            class="col-sm-4 control-label"><?= lang('Urbanpiper_Price_Addition') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aupprice"
                                onkeypress="return isNumberKeyPrice(event)">
                            <span id="errorup" style="color:#a94442; display: none;font-size:11px;">please enter numbers
                                only</span>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="form-group">
                        <label for="uquantity" class="col-sm-4 control-label"><?= lang('Unit Quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" min="0" max="1000" step="0.125" id="uquantity"
                                value="1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="uweight" class="col-sm-4 control-label"><?= lang('Unit Weight (In KG)') ?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" min="0" max="1000" step="0.125" id="uweight">
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



<!-- Urbanpiper ---->

<script type="text/javascript">
$(document).ready(function() {
    if ('<?= $product->up_items ?>' == '1') {
        $('#urbanpipercontain').show();
    } else {
        $('#urbanpipercontain').hide();
    }

});

$('#urbanpiperitem').click(function() {
    let values = $(this).val();
    if (values == '1') {
        $('#urbanpipercontain').show();
    } else {
        $('#urbanpipercontain').hide();
    }
});

function deleteVarient(del_id) {
    var con = confirm('Are you sure you want to delete?');
    var id = del_id;
    if (!con) {
        return false;
    }

    $.ajax({
        type: "get",
        async: false,
        url: "<?= site_url('products/deleteVariant') ?>/" + id,
        dataType: "json",
        success: function(response) {
            //console.log(response);
            //bootbox.alert('response')');
            //$('#variant_'+id).hide();
            if (!response.status) {
                alert(response.message); 
                return;
            }
            location.reload();
        },
    });
}

var enableDiscountOnMrp = <?= $enable_discount_on_mrp ?>;
function setDiscountOnMrp(val) {
    $('#discount_on_mrp').val(enableDiscountOnMrp ? val : '0%');
}
function setVariantDiscount($input, val) {
    if ($input && $input.length) {
        $input.val(enableDiscountOnMrp ? val : '0%');
    }
}

function handlePriceInput() {
    // For each input, get its value and log it on load
    $('.variant-price').each(function() {
        var newValue = $(this).val();
        var mrpValue = parseFloat($('#mrp').val());
        var priceValue = parseFloat($('#price').val());
        var productPrice = newValue + priceValue;
        calculateDiscount(newValue, mrpValue, priceValue);

    });
    $('.variant-price').on('input', function() {
        var newValue = $(this).val();
        var mrpValue = parseFloat($('#mrp').val());
        var priceValue = parseFloat($('#price').val());
        var productPrice = newValue + priceValue;
        calculateDiscount(newValue, mrpValue, priceValue);
    });
}

function calculateDiscount(priceValue, mrpValue, optionPrice) {
    if (!enableDiscountOnMrp) {
        setDiscountOnMrp('0%');
        return;
    }
    if (!isNaN(priceValue) && !isNaN(mrpValue) && mrpValue >= 0) {
        priceValue = optionPrice + priceValue;
        var discountValue, discountType;
        if (mrpValue >= priceValue) {
            if (priceValue == '0') {
                var calculatedDiscount = 0;
                setDiscountOnMrp(calculatedDiscount + '%');

            } else {
                var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                setDiscountOnMrp(calculatedDiscount.toFixed(0) + '%');
            }

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
    var newValue = $(this).val();
    const priceSpan = $(this).next('span'); // Get the associated span for displaying price
    checkvarientPrice(newValue);
});

function checkvarientPrice(newValue) {
    var priceValue = parseFloat($('#price').val());
    var calculatePrice = (priceValue + newValue)
    var mrpValue = parseFloat($('#mrp').val());
    if (calculatePrice >= mrpValue) {
        alert('Price should be smaller than MRP.');
        $('#attr_price').val('');
    }
    return;
}
$(document).ready(function() {
    discountDisebled();
    $('#mrp').on('change', function() {
        var mrpValue = parseFloat($('#mrp').val());

        if (!isNaN(mrpValue) && mrpValue > 0) {
            $('#price').val(mrpValue.toFixed(0)); // Set price same as MRP initially
            setDiscountOnMrp('0%'); // Reset discount when MRP is changed
        } else {
            $('#price').val('0');
        }

        discountDisebled();
    });
    $('#mrp, #discount_on_mrp').on('change', function() {
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
            setDiscountOnMrp('0%'); // Reset input to 0%
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

    $('#price').on('change', function() {
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
    $('#cost').on('change', function() {
        var costValue = parseFloat($('#cost').val());

        if (costValue < 0 || 1 / costValue === -Infinity) {
            alert('Cost cannot be negative!');
            $('#cost').val(Math.abs(costValue));
            return;
        }
    });
    $('#product_discount, #discount_on_mrp').on('change', function() {
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

    function getAttrRowPriceInput($row) {
        return $row.find('input.variantprice, input.variant_price, .variantprice input, input[name="attr_price[]"]').first();
    }
    function getAttrRowMrpInput($row) {
        return $row.find('input.variantmrp, input.variant_mrp, .variantmrp input, input[name="attr_mrp[]"]').first();
    }
    function getAttrRowDiscountInput($row) {
        return $row.find('input.variantdiscount, input.variant_discount, .variantdiscount input, input[name="attr_discount[]"]').first();
    }

    $(document).on('input change keyup', '#attrTable .cost, #attrTable .variantmrp, #attrTable .variant_mrp, #attrTable .variantdiscount, #attrTable .variant_discount, #attrTable .variantprice, #attrTable .variant_price, #attrTable .unit_quantity, #attrTable .unit_weight',
        function() {
            if (!$(this).closest('#attrTable').length) {
                return;
            }
            var $row = $(this).closest('tr');
            var $priceInput = getAttrRowPriceInput($row);
            var $mrpInput = getAttrRowMrpInput($row);
            var $discountInput = getAttrRowDiscountInput($row);
            var cost = parseFloat($row.find('input.cost, .cost input').first().val()) || 0;
            var mrp = parseFloat($mrpInput.val()) || 0;
            var price = parseFloat($priceInput.val()) || 0;
            var quantity = parseFloat($row.find('.unit_quantity input').val()) || 0;
            var weight = parseFloat($row.find('.unit_weight input').val()) || 0;
            var discount = $discountInput.val() || '0%';
            var discountValue = 0;
            var isPercentage = false;

            if (cost < 0 || String($row.find('input.cost, .cost input').first().val() || '').trim().startsWith('-')) {
                alert("Cost cannot be negative!");
                cost = Math.abs(cost);
                $row.find('input.cost, .cost input').first().val(cost.toFixed(0));
            }
            if (mrp < 0 || String($mrpInput.val() || '').trim().startsWith('-')) {
                alert("MRP cannot be negative!");
                mrp = Math.abs(mrp);
                $mrpInput.val(mrp.toFixed(0));
            }
            if (quantity < 0 || String($row.find('.unit_quantity input').val() || '').trim().startsWith('-')) {
                alert("Quantity cannot be negative!");
                quantity = Math.abs(quantity);
                $row.find('.unit_quantity input').val(quantity.toFixed(0));
            }
            if (weight < 0 || String($row.find('.unit_weight input').val() || '').trim().startsWith('-')) {
                alert("Weight cannot be negative!");
                weight = Math.abs(weight);
                $row.find('.unit_weight input').val(weight.toFixed(0));
            }

            if (discount.indexOf('-') !== -1) {
                alert("Discount cannot be negative!");
                discountValue = 0;
                $discountInput.val("0%");
                $priceInput.val(mrp.toFixed(0));
                return;
            }

            if (discount.indexOf('%') !== -1) {
                discountValue = parseFloat(discount.replace('%', '')) || 0;
                isPercentage = true;
            } else {
                discountValue = parseFloat(discount) || 0;
            }

            if ($(this).is('.variantmrp') || $(this).hasClass('variant_mrp')) {
                $priceInput.val(mrp.toFixed(0));
                $discountInput.val("0%");
                return;
            }

            if ($(this).is('.variantprice') || $(this).hasClass('variant_price')) {
                price = parseFloat($priceInput.val()) || 0;
                if (isNaN(price) || price < 0 || String($priceInput.val() || '').trim().startsWith('-')) {
                    alert('Price cannot be negative!Please enter a valid price greater than or equal to 0');
                    $priceInput.val(mrp.toFixed(0));
                    $discountInput.val("0%");
                    return;
                }
                if (price > mrp) {
                    alert('Price cannot be greater than MRP!');
                    $priceInput.val(mrp.toFixed(0));
                    $discountInput.val("0%");
                    return;
                }
                if (mrp > 0 && enableDiscountOnMrp) {
                    var calculatedDiscount = ((mrp - price) / mrp) * 100;
                    setVariantDiscount($discountInput, calculatedDiscount.toFixed(0) + '%');
                } else if (!enableDiscountOnMrp) {
                    setVariantDiscount($discountInput, '0%');
                }
            }

            if ($(this).is('.variantdiscount') || $(this).hasClass('variant_discount')) {
                if (isPercentage) {
                    if (discountValue > 100) {
                        alert("Discount should not exceed 100%");
                        $discountInput.val("0%");
                        $priceInput.val(mrp.toFixed(0));
                        return;
                    }
                    if (mrp > 0) {
                        $priceInput.val((mrp - (mrp * (discountValue / 100))).toFixed(0));
                    }
                } else {
                    if (discountValue > mrp) {
                        alert("Discount cannot be more than MRP");
                        $discountInput.val("0");
                        $priceInput.val(mrp.toFixed(0));
                        return;
                    }
                    if (mrp > 0) {
                        var calculatedPrice = mrp - discountValue;
                        if (calculatedPrice < 0) {
                            calculatedPrice = 0;
                        }
                        $priceInput.val(calculatedPrice.toFixed(0));
                    }
                }
            }
        });

    $(document).on('input change keyup', '#attrTable input.variantmrp, #attrTable input.variant_mrp', function() {
        var $row = $(this).closest('tr');
        var mrp = parseFloat($(this).val()) || 0;
        var $priceInput = getAttrRowPriceInput($row);
        var $discountInput = getAttrRowDiscountInput($row);
        $priceInput.val(mrp.toFixed(0));
        $discountInput.val('0%');
    });

    $(document).on('input change keyup', '#attrTable input.variantprice, #attrTable input.variant_price', function() {
        var $row = $(this).closest('tr');
        var mrp = parseFloat(getAttrRowMrpInput($row).val()) || 0;
        var price = parseFloat($(this).val()) || 0;
        var $discountInput = getAttrRowDiscountInput($row);
        if (String($(this).val() || '').trim().startsWith('-')) {
            alert('Price cannot be negative! Please enter a valid price greater than or equal to 0');
            $(this).val(mrp.toFixed(0));
            $discountInput.val('0%');
            return;
        }
        if (price > mrp) {
            alert('Price cannot be greater than MRP!');
            $(this).val(mrp.toFixed(0));
            $discountInput.val('0%');
        } else if (mrp > 0 && enableDiscountOnMrp) {
            setVariantDiscount($discountInput, (((mrp - price) / mrp) * 100).toFixed(0) + '%');
        } else if (!enableDiscountOnMrp) {
            setVariantDiscount($discountInput, '0%');
        }
    });

    $(document).on('input change keyup', '#attrTable input.variantdiscount, #attrTable input.variant_discount', function() {
        var $row = $(this).closest('tr');
        var mrp = parseFloat(getAttrRowMrpInput($row).val()) || 0;
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
        if (price < 0) {
            price = 0;
        }
        getAttrRowPriceInput($row).val(price.toFixed(0));
    });


    // validation and autocalculate of discount for variant on pop up
    $('#acost, #amrp, #aprice, #adiscount').on('change', function() {
        var variant_mrp = parseFloat($('#amrp').val()) || 0; // Get MRP value
        var variant_price = parseFloat($('#aprice').val()) || 0; // Get price value
        var variant_discount = $('#adiscount').val(); // Get Discount value

        var isPercentage = variant_discount.includes('%');
        var discountValue = isPercentage ? parseFloat(variant_discount.replace('%', '')) : parseFloat(
            variant_discount);

        //  MRP change: Set price to MRP and discount to 0%
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

//Update Variant Grid
function validatePriceMRP(input) {
    var row = $(input).closest('tr');
    var mrp = row.find('.variant_mrp').val();
    var price = row.find('.variant_price').val();

    if (parseFloat(price) >= parseFloat(mrp) && mrp > 0) {
        alert('Price should be less than MRP.');
        row.find('.variant_price').val(0);
    }
}


$(document).on('input change keyup', 'tr.variant-row .variant_cost, tr.variant-row .variant_mrp, tr.variant-row .variant_price, tr.variant-row .variant_discount, tr.variant-row .unit_quantity, tr.variant-row .unit_weight',
    function() {
        var row = $(this).closest('tr');
        if (!row.hasClass('variant-row')) {
            return;
        }
        var costInput = row.find('.variant_cost');
        var mrpInput = row.find('.variant_mrp');
        var priceInput = row.find('.variant_price');
        var discountInput = row.find('.variant_discount');
        var quantityInput = row.find('.unit_quantity');
        var weightInput = row.find('.unit_weight');


        var cost = parseFloat(costInput.val().replace(/,/g, '')) || 0;
        var mrp = parseFloat(mrpInput.val().replace(/,/g, '')) || 0;
        var price = parseFloat(priceInput.val().replace(/,/g, '')) || 0;
        var quantity = parseFloat(quantityInput.val().replace(/,/g, '')) || 0;
        var weight = parseFloat(weightInput.val().replace(/,/g, '')) || 0;
        var discountText = (discountInput.val() || '').toString().trim();
        var discount = 0;
        var isPercentage = discountText.includes('%');

        // When MRP is updated, reset the price to match the MRP and reset the discount to 0%

        if (cost < 0) {
            alert("Cost cannot be a negative number!");
            cost = Math.abs(cost);
            costInput.val(cost);
        }

        if (mrp < 0) {
            alert("MRP cannot be a negative number!");
            mrp = Math.abs(mrp);
            mrpInput.val(mrp);
        }

        if (price < 0) {
            alert("Price cannot be a negative number!");
            price = Math.abs(price);
            priceInput.val(price);
        }
        if (quantity < 0) {
            alert("Quantity cannot be a negative number!");
            quantity = Math.abs(quantity);
            quantityInput.val(quantity);
        }
        if (weight < 0) {
            alert("Weight cannot be a negative number!");
            weight = Math.abs(weight);
            weightInput.val(weight);
        }


        if ($(this).hasClass('variant_mrp')) {
            priceInput.val(mrp);
            discountInput.val('0%');
        }

        // When discount field is updated
        if ($(this).hasClass('variant_discount')) {
            discount = parseFloat(discountText.replace('%', '')) || 0;
            if (discount < 0) {
                alert("Discount cannot be a negative number!");
                discount = 0;
                discountInput.val('0'); // Optional: reset field to 0
                priceInput.val(mrp); // Optional: reset price too
                return; // ✅ Stop further checks
            }


            if (isPercentage) {
                // If discount is in percentage
                if (discount >= 0 && discount <= 100) {
                    price = mrp - (mrp * discount / 100);
                } else {
                    alert("Discount should not exceed 100%");
                    discount = 0;
                }
                price = roundToTwo(price); // Round price to 2 decimal places
                priceInput.val(parseFloat(price.toFixed(2))); // Save price rounded to 2 decimal places
                discountInput.val(discount.toFixed(0) + '%'); // Save percentage discount


            } else {
                // If discount is a fixed amount
                discount = parseFloat(discountText) || 0;
                if (discount >= 0 && discount <= mrp) {
                    price = mrp - discount;
                } else {
                    alert("Discount should not exceed 100%");
                    discount = 0;
                }
                price = roundToTwo(price); // Round price to 2 decimal places
                priceInput.val(parseFloat(price.toFixed(2))); // Save price rounded to 2 decimal places
                //discountInput.val(discount.toFixed(2)); // Save fixed discount
                discountInput.val(Math.floor(discount)); // ✅ new: always show whole number

            }
            priceInput.val(parseFloat(price.toFixed(2)));
        }

        // When price is updated
        if ($(this).hasClass('variant_price')) {
            if (price > mrp) {
                alert('Price cannot be greater than MRP!');
                price = mrp;
            }
            discount = mrp - price;

            if (discount > 0) {
                var percentageDiscount = (discount / mrp) * 100;
                discountInput.val(percentageDiscount.toFixed(0) + '%');
            } else {
                discountInput.val('0%');
            }
            price = roundToTwo(price); // Round price to 2 decimal places
            priceInput.val(parseFloat(price.toFixed(2))); // Ensure price is shown with 2 decimals
        }
    });

function formatNumber(value) {
    if (isNaN(value)) return '0';
    return parseFloat(parseFloat(value).toFixed(2)); // Remove unnecessary trailing zeros
}

function roundToTwo(num) {
    // Round the number to 2 decimal places
    return Math.round(num * 100) / 100;
}

function discountDisebled() {
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
handlePriceInput();
</script>
<script>
$(function() {
    var isEditing = <?= isset($product) ? 'true' : 'false'; ?>;
    var hasVariants = <?= !empty($product_variants) ? 'true' : 'false'; ?>;

    setTimeout(function() {
        if (isEditing && hasVariants) {
            $('#price').val('00').prop('readonly', true);
            $('#mrp').val('00').prop('readonly', true);
            $('#discount_on_mrp').val('0%').prop('readonly', true);
            $('#cost').prop('readonly', true); // Don't forget this!
        }
    }, 200);
});
</script>
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
<!-- End Urbanpiper --->
<script>
$(document).ready(function() {
    $('#category').change(function() {
        var selectedCategoryId = $(this).val();

        $('#othercategory option').each(function() {
            // Always show all first
            $(this).show();

            // Then hide the one that matches selected category
            if ($(this).val() === selectedCategoryId) {
                $(this).prop('selected', false); // Also deselect if already selected
                $(this).hide();
            }
        });

        $('#othercategory').trigger('change'); // In case you're using Select2
    });

    // Trigger on load if needed
    $('#category').trigger('change');
});
</script>


<!-- ///////////////////////other category/////////////////////////// -->
<script>
$(document).ready(function() {
    const fullCategoryList = <?= json_encode($categories) ?>;

    $('#category').change(function() {
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
$(document).ready(function() {
    $('#othercategory').select2({
        placeholder: "Select Other Categories",
        width: '100%'
    });
});
$(document).ready(function() {
  $('input[type="checkbox"].checkbox').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    increaseArea: '20%' // optional
  });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/redactor/3.5.4/redactor.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/redactor/3.5.4/redactor.css">
<script>
$(document).ready(function() {
    // Initialize Redactor for Product Details textarea
    $('#product_details').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100
    });
        
    // Initialize Redactor for Product Details for Invoice textarea
    $('#details').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100
    });
});
<?php if (!empty($Settings->display_job_work)) { ?>
$(document).ready(function() {
    var mainProductId = <?= (int) (isset($product->id) ? $product->id : 0) ?>;
    var productName = <?= json_encode(isset($product->name) ? $product->name : '') ?>;
    var existingReadonlyJobWorks = [];
    var csrfTokenName = <?= json_encode($this->security->get_csrf_token_name()) ?>;
    var csrfTokenValue = <?= json_encode($this->security->get_csrf_hash()) ?>;

    function isJobWorkInwardType() {
        var selectedText = $.trim($('#product_inward_type option:selected').text()).toLowerCase();
        return selectedText === 'job works' || selectedText === 'job work';
    }

    function updateDccButtonVisibility() {
        var hasReadonlyJobWork = $('input[name="job_work[]"]').length > 0 && $('#job_work').length === 0;
        var showFromEditable = $('#job_work').length > 0 && isJobWorkInwardType();
        $('#dcc_stage_btn_wrap').toggle(showFromEditable || hasReadonlyJobWork);
    }

    function getSelectedJobWorkNames() {
        var names = [];
        if ($('#job_work').length > 0) {
            $('#job_work option:selected').each(function() {
                var text = $.trim($(this).text());
                if (text) {
                    names.push(text);
                }
            });
        } else {
            var readonlyText = $.trim($('#job_work_readonly').val() || '');
            if (readonlyText.length) {
                names = $.map(readonlyText.split(','), function(item) {
                    var v = $.trim(item);
                    return v ? v : null;
                });
            } else if (existingReadonlyJobWorks.length) {
                names = existingReadonlyJobWorks.slice(0);
            }
        }
        return names;
    }

    function buildDccRows(processNames) {
        var rows = [];
        var inputName = productName;
        $.each(processNames, function(index, processName) {
            if (!processName) {
                return true;
            }
            var outputName = productName + ' - ' + processName;
            rows.push({
                product: productName,
                input: inputName,
                process: processName,
                output: outputName
            });
            inputName = outputName;
        });
        return rows;
    }

    function escapeHtml(value) {
        return $('<div/>').text(value || '').html();
    }

    function getInputOptionsFromRows(rows) {
        var options = [];
        if (productName) {
            options.push(productName);
        }
        $.each(rows || [], function(_, row) {
            if (row.input) {
                options.push($.trim(row.input));
            }
            if (row.output) {
                options.push($.trim(row.output));
            }
        });
        options = $.grep(options, function(v) { return !!v; });
        return $.grep(options, function(value, index) {
            return $.inArray(value, options) === index;
        });
    }

    function buildInputSelectHtml(stageId, selectedValue, inputOptions) {
        var html = '<select class="form-control dcc-input-select" data-stage-id="' + (stageId || '') + '">';
        html += '<option value="">-- Select Input --</option>';
        $.each(inputOptions, function(_, option) {
            var selected = option === selectedValue ? ' selected="selected"' : '';
            html += '<option value="' + escapeHtml(option) + '"' + selected + '>' + escapeHtml(option) + '</option>';
        });
        html += '</select>';
        return html;
    }

    function renderDccRows(rows, editable) {
        var bodyHtml = '';
        var inputOptions = getInputOptionsFromRows(rows);
        if (!inputOptions.length && productName) {
            inputOptions.push(productName);
        }
        $.each(rows, function(_, row) {
            var processText = row.process || row.job_work_name || '';
            var suffixText = row.job_work_suffix || '';
            if (!suffixText) {
                suffixText = processText;
            }
            var displayOutput = row.output || '';
            if (!displayOutput && row.input && suffixText) {
                displayOutput = $.trim(row.input + '- ' + suffixText);
            } else if (!displayOutput && row.input) {
                displayOutput = row.input;
            }
            var displayProduct = row.main_product_name || row.product || row.product_name || productName;
            bodyHtml += '<tr>';
            bodyHtml += '<td>' + escapeHtml(displayProduct) + '</td>';
            bodyHtml += '<td>' + buildInputSelectHtml(row.id || '', row.input || '', inputOptions) + '</td>';
            bodyHtml += '<td class="dcc-process-cell" data-suffix="' + escapeHtml(suffixText) + '">' + escapeHtml(processText) + '</td>';
            bodyHtml += '<td class="dcc-output-cell">' + escapeHtml(displayOutput) + '</td>';
            bodyHtml += '</tr>';
        });
        $('#dccStageTableBody').html(bodyHtml);
        if (!editable) {
            $('.dcc-input-select').prop('disabled', true);
        }
        $('.dcc-input-select').each(function() {
            $(this).data('last-value', $.trim($(this).val() || ''));
        });
    }

    function openDccModalFromDb() {
        var loaded = false;
        $.ajax({
            url: "<?= site_url('products/get_dcc_stages') ?>/" + mainProductId,
            type: "GET",
            dataType: "json",
            async: false,
            success: function(response) {
                if (response && response.status && response.rows && response.rows.length) {
                    renderDccRows(response.rows, true);
                    loaded = true;
                }
            }
        });
        return loaded;
    }

    function appendOptionIfMissing(selectElem, value) {
        if (!value) {
            return;
        }
        var exists = false;
        $(selectElem).find('option').each(function() {
            if ($.trim($(this).val()) === $.trim(value)) {
                exists = true;
                return false;
            }
        });
        if (!exists) {
            $(selectElem).append('<option value="' + escapeHtml(value) + '">' + escapeHtml(value) + '</option>');
        }
    }

    // Match Products_model::buildDccOutputValue + buildDccStageOutputForMainProductInsert (same as product add / job work create).
    function buildPopupOutputValue(baseName, suffixText, processText) {
        var label = $.trim(suffixText || '');
        if (!label.length) {
            label = $.trim(processText || '');
        }
        if (!label.length) {
            label = 'items';
        }
        return $.trim((baseName || '') + '-' + label);
    }

    $('#open_dcc_stage_modal').on('click', function() {
        if (mainProductId > 0 && openDccModalFromDb()) {
            $('#dccStageModal').appendTo('body').modal('show');
            return;
        }

        var processNames = getSelectedJobWorkNames();
        if (!processNames.length) {
            bootbox.alert('Please select Job Works first.');
            return;
        }

        var rows = buildDccRows(processNames);
        renderDccRows(rows, false);
        $('#dccStageModal').appendTo('body').modal('show');
    });

    $(document).on('change', '.dcc-input-select', function() {
        var $select = $(this);
        var $row = $select.closest('tr');
        var stageId = parseInt($select.data('stage-id'), 10) || 0;
        var lastValue = $.trim($select.data('last-value') || '');
        var inputValue = $.trim($select.val() || '');
        var processText = $.trim($select.closest('tr').find('.dcc-process-cell').text() || '');
        var processSuffix = $.trim($select.closest('tr').find('.dcc-process-cell').data('suffix') || '');
        var immediateOutput = buildPopupOutputValue(productName, processSuffix, processText);
        var previousOutput = $.trim($row.find('.dcc-output-cell').text() || '');
        if (!stageId) {
            return;
        }
        if (!inputValue.length) {
            // Allow blank selection and persist it to DB, while keeping visible output unchanged.
            $row.find('.dcc-output-cell').text(previousOutput);
        } else {
            $row.find('.dcc-output-cell').text(immediateOutput);
            $('.dcc-input-select').each(function() {
                appendOptionIfMissing(this, immediateOutput);
            });
        }

        var postData = {
            stage_id: stageId,
            main_product_id: mainProductId,
            input: inputValue
        };
        postData[csrfTokenName] = csrfTokenValue;
        $.ajax({
            url: "<?= site_url('products/save_dcc_stage_input') ?>",
            type: "POST",
            dataType: "json",
            data: postData,
            success: function(response) {
                if (response && response.status) {
                    $select.data('last-value', inputValue);
                    if (inputValue.length) {
                        $row.find('.dcc-output-cell').text(response.output || immediateOutput);
                    } else {
                        $row.find('.dcc-output-cell').text(response.output || previousOutput);
                    }
                } else {
                    $select.val(lastValue);
                    $row.find('.dcc-output-cell').text(previousOutput);
                    bootbox.alert((response && response.message) ? response.message : 'Failed to save stage.');
                }
            },
            error: function() {
                $select.val(lastValue);
                $row.find('.dcc-output-cell').text(previousOutput);
                bootbox.alert('Unable to save DCC stage.');
            }
        });
    });

    function initJobWorkSelect2() {
        var $jw = $('#job_work');
        if ($jw.length === 0) {
            return;
        }
        try {
            if ($jw.data('select2')) {
                $jw.select2('destroy');
            }
        } catch (e) {}
        $jw.select2({
            placeholder: <?= json_encode(lang('select') . ' ' . lang('Job_Works')) ?>,
            width: '100%',
            allowClear: true
        });
    }

    function toggleJobWork() {
        if ($('#product_inward_type').length === 0) {
            return;
        }
        var $jw = $('#job_work');
        var inwardType = $.trim($('#product_inward_type option:selected').text());
        if (inwardType === 'Job Works') {
            $('#job_work_div').show();
            initJobWorkSelect2();
        } else {
            if ($jw.data('select2')) {
                $jw.select2('destroy');
            }
            $jw.val(null);
            $('#job_work_div').hide();
        }
    }

    // core.js initializes select2 on hidden #job_work before show — destroy so we can re-init cleanly
    if ($('#job_work').length && $('#job_work_div').is(':hidden') && $('#job_work').data('select2')) {
        $('#job_work').select2('destroy');
    }

    toggleJobWork();
    $('#product_inward_type').on('change', function() {
        toggleJobWork();
        updateDccButtonVisibility();
    });
    $('#job_work').on('change', function() {
        updateDccButtonVisibility();
    });

    updateDccButtonVisibility();
});
<?php } ?>
</script>
<script>
function buildNewVariantsPayloadForSubmit() {
    var rows = [];
    $('#attrTable tbody tr.attr').each(function() {
        var $row = $(this);
        var nameVal = $.trim($row.find('input[name="attr_name[]"]').val() || $row.find('.attr_name').val() || $row.find('td:first span').text());
        if (!nameVal) {
            return;
        }
        rows.push({
            name: nameVal,
            cost: $row.find('input[name="attr_cost[]"], td.cost input, .cost input').first().val() || 0,
            mrp: $row.find('input[name="attr_mrp[]"], td.variantmrp input, .variantmrp input').first().val() || 0,
            price: $row.find('input[name="attr_price[]"], td.price input, .variantprice input, .price input').first().val() || 0,
            discount: $row.find('input[name="attr_discount[]"], .variantdiscount input').first().val() || '0%',
            unit_quantity: $row.find('input[name="attr_unit_quantity[]"], .unit_quantity input').first().val() || 1,
            unit_weight: $row.find('input[name="attr_unit_weight[]"], .unit_weight input').first().val() || 0,
            up_price: $row.find('input[name="attr_upprice[]"], .upprice input').first().val() || null
        });
    });
    $('#new_variants_payload').remove();
    if (rows.length) {
        $('<input type="hidden" id="new_variants_payload" name="new_variants_payload">')
            .val(JSON.stringify(rows))
            .appendTo('#product-edit-form');
    }
    return rows;
}
$(document).ready(function() {
    // Same as products/add: bypass bootstrapValidator blocking native submit button
    $('#custom-submit-edit').on('click', function() {
        var category = $('#category').val();
        var unit = $('#unit').val();
        var type = $('#type').val();
        if (!category) {
            alert('Please select a category before submitting.');
            $('#category').focus();
            return;
        }
        if (type === 'standard' && !unit) {
            alert('Please select a unit before submitting.');
            $('#unit').focus();
            return;
        }
        try {
            var $pd = $('#product_details');
            var $dt = $('#details');
            if ($pd.length && $.fn.redactor) {
                $pd.val($pd.redactor('source.getCode'));
            }
            if ($dt.length && $.fn.redactor) {
                $dt.val($dt.redactor('source.getCode'));
            }
        } catch (err) {}
        var $attrChk = $('#attributes');
        var attrSectionActive = $attrChk.is(':checked') || $attrChk.parent().hasClass('checked');
        var hasAttrRows = $('#attrTable tbody tr.attr').length > 0;
        if (attrSectionActive) {
            var tagVals = null;
            try {
                tagVals = $('.select-tags').select2('val');
            } catch (e) {
                tagVals = $('.select-tags').val();
            }
            if (tagVals && tagVals.length) {
                $('#attributesInput').val(tagVals.join(','));
            }
            if (!hasAttrRows && $.trim($('#attributesInput').val()) !== '') {
                $('#addAttributes').trigger('click');
                alert('Please fill Cost, MRP and Price for each variant row, then click Save again.');
                return;
            }
        }
        $('#attrTable input').prop('disabled', false);
        if (hasAttrRows || attrSectionActive) {
            $('#has_new_variants').val('1');
            $attrChk.prop('checked', true);
            if ($.fn.iCheck) {
                try {
                    $attrChk.iCheck('check');
                } catch (e) {}
            }
            buildNewVariantsPayloadForSubmit();
        }
        var form = document.getElementById('product-edit-form') || $('form[data-toggle="validator"]')[0];
        if (form) {
            form.submit();
        }
    });
});
</script>