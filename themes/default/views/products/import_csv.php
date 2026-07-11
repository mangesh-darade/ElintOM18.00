<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('import_products_by_csv'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/import_csv", $attrib);
                $MerchantFields = '';
		$CSVFileName = '';
		if($this->Settings->pos_type=='restaurant'){
			$MerchantFields = ', ' . lang("up_items"). ', ' . lang("food_type_id"). ', ' . lang("up_price"). ', ' . lang("available");
			$CSVFileName = '_restaurant';
		}
                $ProductionTypeField = '';
                if ((int) $this->Settings->enable_module_production_unit === 1 || (int) $this->Settings->display_job_work === 1) {
                    $ProductionTypeField = ', product_type';
                }
                ?>
                <div class="row">
                    <div class="col-md-12">

                        <div class="well well-small">
                            <a href="<?= site_url('products/sample_product_csv'); ?>"
                            class="btn btn-primary pull-right">
                                <i class="fa fa-download"></i> <?= lang("download_sample_file") ?>
                            </a>
                            <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2"); ?> <span
                            <span class="text-info">(<?= lang("name") . ', ' . lang("code"). ', ' . lang("divisionid") . ',  Article Code, ' . lang("barcode_symbology") . ', ' .  lang("brand") . ', ' . lang("category_code") . ', ' . lang("unit_code") . ', ' . lang("sale").' '.lang('unit_code') . ', ' . lang("purchase").' '.lang("unit_code") . ', ' .  lang("cost") . ', ' . lang("price") . ', ' . lang("alert_quantity") . ', ' . lang("tax") . ', ' . lang("tax_method") . ', ' . lang("image") . ', ' . lang("subcategory_code") . ', ' . lang("product_variants_sep_by"). ', ' . lang("MRP_Price"). ', ' . lang("HSN_Code"). ', ' . lang("Warehouse_Code"). ', ' . lang("Quantity"). ', ' . lang("pcf1"). ', ' . lang("pcf2"). ', ' . lang("pcf3"). ', ' . lang("pcf4"). ', ' . lang("pcf5"). ', ' . lang("pcf6") . ', ' . lang("season_id") . $ProductionTypeField . ', ' . lang("flag_visible") . ', ' . lang("color") . ($this->Settings->other_category_for_product == 1 ? ', ' . lang("other_categories") : '') . ($this->Settings->packing_size_column == 1 ? ', ' . lang("Packing Size") : '') . $MerchantFields ; ?>
                                )</span> <?= lang("csv3"); ?>
                                <p><b>Note :</b> In case of variants for multiple variants,use comma separated values.EX:Cost,Price and Mrp: </p>
                                <p><?= lang('images_location_tip'); ?></p>
								<?php if($this->Settings->pos_type=='restaurant'){ ?>
                                <p><b>Note:</b> The Division Ids and Food type should be filled in the following format: </p>
				<p>For Division Ids : Kitchen - 1, Bar - 2, Lounge -3, Terrace - 4, No print - 5</p>
								<p>For food type : Vegeterian = 1, Non vegeterian = 2, Eggeterian = 3, Not specified = 4</p> <?php } ?>
<?php
									if(!empty($ProductCustomField)){
										echo '<p>For Custom Field Identification: ';
										foreach($ProductCustomField as $keyCustomeField){
											echo $keyCustomeField['custom_field'].'='.$keyCustomeField['display_custom_field'].', ';
										}
										echo '</p>';
									}
								?>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="csv_file"><?= lang("upload_file"); ?></label>
                                <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="csv_file" accept=".csv,.xlsx,.xls" required="required"/>
                                <input type="hidden" name="skip_duplicate_codes" id="skip_duplicate_codes" value="0"/>
                            </div>
                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line("import"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>

    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Upload Product Images'); ?></h2>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-12">
                        <?php 
                            $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                            echo form_open_multipart("products/bulk_images", $attrib);
                        ?>

                        <div class="well well-small">
                            <a href="<?php echo base_url(); ?>assets/mdata/<?php echo $Customer_assets; ?>/csv/sample_product_image_upload.xls"
                               class="btn btn-primary pull-right"><i
                                    class="fa fa-download"></i> <?= lang("download_sample_file") ?></a>
                            <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2"); ?> <span
                                class="text-info">(<?= lang("code") . ', ' . lang("Product_Name") . ', ' . lang("Image_Name") . ',  Gallery Images, ' . lang("Variants_Name") . ', ' . lang("Variants_Images"); ?>
                                )</span> 
                            <p><?= lang('images_location_tip'); ?>
                             <br>
                                <strong>Note : </strong> Select Multiple images using Ctrl+A or Shift+A
                            </p>

                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="csv_file"><?= lang("upload_file"); ?></label>
                                <input type="file" accept=".xls" data-browse-label="<?= lang('browse'); ?>" name="userxls" class="form-control file" data-show-upload="false" data-show-preview="false" id="xls_file" required="required"/>
                            </div>

                            <div class="form-group">
                                <label for="csv_file"><?= lang("product_gallery_images", "images"); ?></label>
                                 <input id="images" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile[]" multiple="true" data-show-upload="false" required="required"
                                  data-show-preview="false" class="form-control file" accept="image/*">
                            </div>

                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line("Upload"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

    </div> 
</div>
<script type="text/javascript">
$(function () {
    // Override generic #csv_file validators from global JS for this page only.
    $('#csv_file').off('change').on('change', function (e) {
        var v = $(this).val();
        if (v !== '') {
            var validExts = ['.csv', '.xlsx', '.xls'];
            var fileExt = v.substring(v.lastIndexOf('.')).toLowerCase();
            if (validExts.indexOf(fileExt) < 0) {
                e.preventDefault();
                bootbox.alert("Invalid file selected. Only .csv, .xlsx or .xls file is allowed.");
                $(this).val('');
                if ($(this).data('fileinput')) {
                    $(this).fileinput('clear');
                }
                $('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'csv_file', 'NOT_VALIDATED');
                return false;
            }
        }
        return true;
    });
    <?php if ($this->session->flashdata('duplicate_prompt')) { ?>
    setTimeout(function () {
        bootbox.confirm({
            message: "Do you want to skip duplicate entry and proceed with product import?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-primary'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (result) {
                    var $form = $('form[action*="products/import_csv"]').first();
                    $('#skip_duplicate_codes').val('1');
                    $('#csv_file').prop('required', false);
                    // Bypass validator/browser required checks; server already has cached rows.
                    $form.attr('novalidate', 'novalidate');
                    if ($form.length && $form.get(0)) {
                        $form.get(0).submit();
                    }
                }
            }
        });
    }, 150);
    <?php } ?>

});
</script>