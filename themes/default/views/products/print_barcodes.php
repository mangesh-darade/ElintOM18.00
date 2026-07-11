<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .barcode_price {
        font-weight: bold;
    }

    .barcode_row_100 {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
    border-bottom: 1px dotted #CCC; /* horizontal line */
    margin-bottom: 2px;
}

.barcode_row_100 .item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4px;
    text-align: center;
    text-transform: uppercase;
    font-family: Arial, sans-serif;
    font-size: 10px;
    line-height: 1.3;
    border-left: 1px dotted #CCC; /* vertical middle line */
}

/* Remove left border from the first item so only center line shows */
.barcode_row_100 .item:first-child {
    border-left: none;
}

.barcode_row_100 .barcode_name,
.barcode_row_100 .barcode_price,
.barcode_row_100 .barcode_image {
    display: block;
    margin: 2px 0;
}

.barcode_row_100 .barcode_price {
    font-weight: bold;
    font-size: 11px;
}

.barcode_row_100 img {
    height: 35px;
}



    <?php if ($style == 50) {
        echo '.barcode {width: auto;height: auto;}';
    }

  

    if ($style == 65) {
    ?>.barcodea4 {
        padding-left: 0.19685in;
        padding-top: 0.472441in;
    }

    <?php
    } elseif ($style == '24N' || $style == '24NN') {
    ?>.bcimg {
        width: 90%;
        height: 35px;
    }

    .barcodea4 {
        padding: 0.4921in 0 0 0.2559in;
    }

    <?php
    }

    if ($style == 'A4_Dynamic') {
        $pages = unserialize($Settings->barcode_a4_page_dynamic);

    ?>.barcodea4 {
        width: 210mm;
        height: 297mm;
        display: block;
        border: 1px solid #CCC;
        page-break-after: always;
    }

    .barcodea4 {
        padding-top: <?= (($pages['margin_top']) ? $pages['margin_top'] : '0') ?>mm;
        padding-bottom: <?= (($pages['margin_bottom']) ? $pages['margin_bottom'] : '0') ?>mm;
        padding-left: <?= (($pages['margin_left']) ? $pages['margin_left'] : '0') ?>mm;
        padding-right: <?= (($pages['margin_right']) ? $pages['margin_right'] : '0') ?>mm;
    }

    .barcodea4 .styleA4_Dynamic {
        width: <?= (($pages['label_width']) ? $pages['label_width'] : '0') ?>mm;
        height: <?= (($pages['label_height']) ? $pages['label_height'] : '0') ?>mm;
        margin-top: <?= (($pages['gap_label_top']) ? $pages['gap_label_top'] : '0') ?>mm;
        margin-bottom: <?= (($pages['gap_label_bottom']) ? $pages['gap_label_bottom'] : '0') ?>mm;
        margin-left: <?= (($pages['gap_label_left']) ? $pages['gap_label_left'] : '0') ?>mm;
        margin-right: <?= (($pages['gap_label_right']) ? $pages['gap_label_right'] : '0') ?>mm;
        padding-top: 0.05in;
    }

    <?php
    }

    ?>.style100 {
        margin-left: 3mm;
        float: left;
    }
</style>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('print_barcode_label'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">

                    <a href="<?= base_url('system_settings/barcode_stiker_size') ?>" data-toggle="modal"
                        data-target="#myModal">
                        <i class="fa fa-file-o" aria-hidden="true"></i> A4 Paper Settings</a> |
                    <a href="<?= base_url('system_settings/manage_barcode') ?>" target="blank">
                        <i class="fa fa-cogs" aria-hidden="true"></i> Barcode Settings </a>
                    <a href="#" onclick="window.print();
                            return false;" id="print-icon" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext"><?php
                            echo sprintf(
                                lang('print_barcode_heading'),
                                anchor('system_settings/categories', lang('categories')),
                                //anchor('system_settings/subcategories', lang('subcategories')),
                                anchor('purchases', lang('purchases')),
                                anchor('transfers', lang('transfers'))
                            );
                            ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">


                <div class="well well-sm no-print">
                    <div class="form-group">
                        <?= lang("add_product", "add_item"); ?>
                        <?php echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                    </div>
                    <?= form_open("products/print_barcodes", 'id="barcode-print-form" data-toggle="validator"'); ?>
                    <div class="controls table-controls">
                        <table id="bcTable"
                            class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th class="col-xs-4">
                                        <?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?>
                                    </th>
                                    <th class="col-xs-3"><?= lang("variants"); ?></th>
                                    <th class="col-xs-2"><?= lang("Color"); ?></th>
                                    <th class="col-xs-2"><?= lang("quantity"); ?></th>
                                    <th> Exp. Date </th>
                                    <?php if ($Settings->product_batch_setting > 0) : ?>
                                    <th class="col-xs-1"> Batch No. </th>
                                    <?php endif; ?>
                                    <th class="text-center" style="width:30px;">
                                        <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <?= lang('style', 'style'); ?>
                        <?php $opts = array('' => lang('select') . ' ' . lang('style'), 65 => lang('65_per_sheet'), 48 => lang('48_per_sheet'), '40N' => lang('40_new_per_sheet'), 40 => lang('40_per_sheet'), 30 => lang('30_per_sheet'), 24 => lang('24_per_sheet'), '24N' => lang('24N_per_sheet'), '24NN' => lang('24NN_per_sheet'), '21N' => lang('21N_per_sheet'), '18N' => lang('18N_per_sheet'), 20 => lang('20_per_sheet'), 18 => lang('18_per_sheet'), 14 => lang('14_per_sheet'), 12 => lang('12_per_sheet'), 10 => lang('10_per_sheet'), 50 => lang('continuous_feed'), 100 => lang('Continuous_Feed_Side_By_Side'), 'A4_Dynamic' => lang('A4_Dynamic'));  ?>
                        <?= form_dropdown('style', $opts, set_value('style', 24), 'class="form-control tip" id="style" required="required"'); ?>
                        <div class="row cf-con" style="margin-top: 10px; display: none;">
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <?= form_input('cf_width', '', 'class="form-control" id="cf_width" placeholder="' . lang("width") . '"'); ?>
                                        <span class="input-group-addon"
                                            style="padding-left:10px;padding-right:10px;"><?= lang('inches'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <?= form_input('cf_height', '', 'class="form-control" id="cf_height" placeholder="' . lang("height") . '"'); ?>
                                        <span class="input-group-addon"
                                            style="padding-left:10px;padding-right:10px;"><?= lang('inches'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <?php $oopts = array(0 => lang('portrait'), 1 => lang('landscape')); ?>
                                    <?= form_dropdown('cf_orientation', $oopts, '', 'class="form-control" id="cf_orientation" placeholder="' . lang("orientation") . '"'); ?>
                                </div>
                            </div>
                        </div>
                        <span class="help-block"><?= lang('barcode_tip'); ?></span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <?= lang("Mfg. Date", "date"); ?>
                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip date" id="podate" '); ?>
                        </div>
                        <div class="form-group col-md-4">
                            <?= lang("Exp. Date", "date"); ?>
                            <?php echo form_input('txtexpdate', (isset($_POST['txtexpdate']) ? $_POST['txtexpdate'] : ""), 'class="form-control input-tip date" id="txtexpdate" '); ?>
                        </div>

                        <div class="form-group col-md-4">
                            <?= lang("Net Quantity", "Quantity"); ?>

                            <?php echo form_input('pro_quantity', (isset($_POST['pro_quantity']) ? $_POST['pro_quantity'] : ""), 'class="form-control input-tip" type="number" min="0" step="1" id="poquantity" '); ?>
                        </div>
                        <div class="form-group col-md-4">
                            <?= lang("Net Weight", "Weight"); ?>

                            <?php echo form_input('pro_weight', (isset($_POST['pro_weight']) ? $_POST['pro_weight'] : ""), 'class="form-control input-tip" type="number" min="0" step="0.01" id="proweight" '); ?>
                        </div>
                        <?php if ($Settings->product_batch_setting > 0) : ?>
                            <div class="form-group col-md-4">
                                <?= lang("Batch No", "Batch No"); ?>

                                <?php echo form_input('txtbatchno', (isset($_POST['txtbatchno']) ? $_POST['txtbatchno'] : ""), 'class="form-control " id="txtbatchno" '); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <span style="font-weight: bold; margin-right: 15px;"><?= lang('print'); ?>:</span>
                        <input name="barcode_img" type="checkbox" id="barcode_img" value="1"
                            <?= isset($_POST) && isset($_POST['barcode_img']) ? 'checked="checked"' : '' ?>
                            style="display:inline-block;" />
                        <label for="barcode_img" class="padding05"><?= lang('Barcode'); ?></label>
                        <input name="site_name" type="checkbox" id="site_name" value="1" checked="checked"
                            style="display:inline-block;" />
                        <label for="site_name" class="padding05"><?= lang('Site_Name'); ?></label>
                        <input name="product_name" type="checkbox" id="product_name" value="1" checked="checked"
                            style="display:inline-block;" />
                        <label for="product_name" class="padding05"><?= lang('product_name'); ?></label>
                        <input name="price" type="checkbox" id="price" value="1" checked="checked"
                            style="display:inline-block;" />
                        <label for="price" class="padding05"><?= lang('price'); ?></label>
                        <!-- 3/04/19 -->
                        <input name="mrp" type="checkbox" id="mrp" value="1" style="display:inline-block;" />
                        <label for="mrp" class="padding05"><?= lang('MRP'); ?></label>
                        <!-- 03/04/19 -->
                        <input name="currencies" type="checkbox" id="currencies" value="1"
                            style="display:inline-block;" />
                        <label for="currencies" class="padding05"><?= lang('currencies'); ?></label>
                        <input name="unit" type="checkbox" id="unit" value="1" style="display:inline-block;" />
                        <label for="unit" class="padding05"><?= lang('unit'); ?></label>
                        <input name="category" type="checkbox" id="category" value="1" style="display:inline-block;" />
                        <label for="category" class="padding05"><?= lang('category'); ?></label>
                        <!-- 27/09/19 -->
                        <input name="Brand" type="checkbox" id="Brand" value="1" style="display:inline-block;" />
                        <label for="Brand" class="padding05"><?= lang('Brand'); ?></label>
                        <!-- 27/09/19 -->
                        <input name="variants" type="checkbox" id="variants" value="1" style="display:inline-block;" />
                        <label for="variants" class="padding05"><?= lang('variants'); ?></label></br>
                        <input name="color" type="checkbox" id="color" checked="checked" value="1" style="display:inline-block;" />
                        <label for="color" class="padding05"><?= lang('Color'); ?></label>
                        <input name="product_image" type="checkbox" id="product_image" value="1"
                            style="display:inline-block;" />
                        <label for="product_image" class="padding05"><?= lang('product_image'); ?></label>
                        <input name="check_promo" type="checkbox" id="check_promo" value="1"
                            style="display:inline-block;" />
                        <label for="check_promo" class="padding05"><?= lang('check_promo'); ?></label>
                        <input name="address" type="checkbox" id="address" value="1" style="display:inline-block;" />
                        <label for="address" class="padding05"><?= lang('Address'); ?></label>

                    </div>

                    <div class="form-group">
                        <?php echo form_submit('print', lang("update"), 'class="btn btn-primary"'); ?>
                        <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
                    </div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
                <script>
                (function() {
                    var form = document.getElementById('barcode-print-form');
                    if (!form) return;
                    // Clear validity when user edits
                    var fieldsToWatch = ['podate','txtexpdate','poquantity','proweight'];
                    fieldsToWatch.forEach(function(id){
                        var el = document.getElementById(id);
                        if (el && typeof el.addEventListener === 'function') {
                            el.addEventListener('input', function(){ this.setCustomValidity(''); });
                            el.addEventListener('change', function(){ this.setCustomValidity(''); });
                        }
                    });

                    form.addEventListener('submit', function(e) {

                        var mfg = document.getElementById('podate') ? document.getElementById('podate').value.trim() : '';
                        var exp = document.getElementById('txtexpdate') ? document.getElementById('txtexpdate').value.trim() : '';
                        var qty = document.getElementById('poquantity') ? document.getElementById('poquantity').value : '';
                        var weight = document.getElementById('proweight') ? document.getElementById('proweight').value : '';
                        var hasError = false;

                        var toTime = function(v){
                            if (!v) return null;
                            // Try D/M/Y or similar with separators
                            var spl = v.split(/[\/\-.]/);
                            if (spl.length === 3) {
                                var day = parseInt(spl[0],10), mon = parseInt(spl[1],10)-1, yr = parseInt(spl[2],10);
                                if (!isNaN(day) && !isNaN(mon) && !isNaN(yr)) {
                                    if (yr < 100) yr += 2000;
                                    var d2 = new Date(yr, mon, day);
                                    if (!isNaN(d2.getTime())) return d2.getTime();
                                }
                            }
                            // Fallback to native parse
                            var d = new Date(v);
                            if (!isNaN(d.getTime())) return d.getTime();
                            return null;
                        };

                        if (qty !== '' && parseFloat(qty) < 0) {
                            hasError = true;
                            var qEl = document.getElementById('poquantity');
                            if (qEl && qEl.setCustomValidity) {
                                qEl.setCustomValidity('Net Quantity cannot be negative.');
                                if (qEl.reportValidity) qEl.reportValidity();
                            } else { alert('Net Quantity cannot be negative.'); }
                        }

                        if (weight !== '' && parseFloat(weight) < 0) {
                            hasError = true;
                            var wEl = document.getElementById('proweight');
                            if (wEl && wEl.setCustomValidity) {
                                wEl.setCustomValidity('Net Weight cannot be negative.');
                                if (wEl.reportValidity) wEl.reportValidity();
                            } else { alert('Net Weight cannot be negative.'); }
                        }

                        var rowQtyInputs = form.querySelectorAll('input[name="quantity[]"]');
                        for (var i = 0; i < rowQtyInputs.length; i++) {
                            var v = rowQtyInputs[i].value;
                            if (v !== '' && parseFloat(v) < 0) {
                                hasError = true;
                                alert('Quantity cannot be negative.');
                            }

                        }

                        var mt = toTime(mfg), et = toTime(exp);
                        if (mt !== null && et !== null && mt > et) {
                            hasError = true;
                            var expEl = document.getElementById('txtexpdate');
                            if (expEl && expEl.setCustomValidity) {
                                expEl.setCustomValidity('Expiry Date must be after or same as Manufacture Date.');
                                if (expEl.reportValidity) expEl.reportValidity();
                            } else { alert('Manufacture date must be earlier than or equal to expiry date.'); }
                        }
                        if (hasError) {
                            if (typeof e.preventDefault === 'function') e.preventDefault();
                            if (typeof e.stopPropagation === 'function') e.stopPropagation();
                            if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
                            return false;
                        }
                        // Valid: let the browser submit normally
                        return true;
                    });
                })();
                </script>
                <div id="barcode-con">
                    <?php
                    // echo $Settings->barcode_type;
                    if ($Settings->barcode_type == 'dynamic') {
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                /**
                                 * NovaJet
                                 */
                                if ($style == '24N') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';

                                            echo '<table style="width:100%;line-height: 1.5;">';
                                            echo '<tr>';
                                            echo '<td width="30%">';
                                            if ($Settings->logo) {
                                                echo '<span class="product_image">';
                                                echo '<img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo) . '" alt="" style="width: 60%; height:60%"/>';
                                                echo '</span>';
                                            }
                                            echo '</td>';
                                            echo '<td style="vertical-align: middle; text-align: left;">';
                                            if ($item['site']) {
                                                echo '<u><span class="barcode_site" style="font-size: 13px"><b> ' . $item['site'] . '</b></span></u>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';



                                            if ($item['category']) {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i>' . $item['category'] . '</i></b></span> ';
                                            } else {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i> &nbsp; </i></b></span> ';
                                            }


                                            if ($item['brand']) {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b>' . $item['brand'] . '</b></span>';
                                            } else {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b> &nbsp; </b></span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name" style="font-size: 12px;line-height: 1.5;"><b>' . $item['name'] . '</b></span>';
                                            }

                                            echo '<table width="100%">';
                                            echo '<tr>';
                                            echo '<td   style="text-align: left;padding-left: 10px;width:40%" >';
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price" style="font-size:11px; font-weight: normal;"> MRP: ';

                                                echo round($item['mrp'], 1);

                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '<td rowspan="2" style="vertical-align: middle;">';

                                            echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';

                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td style="text-align: left;padding-left: 10px;">';
                                            if ($item['price']) {
                                                echo '<span class="barcode_price" style="font-size:12px">' . lang('Price') . ': ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo round($item['price'], 1);
                                                }
                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td colspan="3" style="text-align: left;padding-left: 10px;">';
                                            if ($item['expdate']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Exp: ";
                                                echo $item['expdate'] . "</span>";
                                            }
                                            if ($item['Date']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Mfg: ";
                                                echo $item['Date'] . "</span>";
                                            }
                                            if ($item['batchno']) {
                                                echo "<span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Batch No." . $item['batchno'] . '</span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';


                                            echo '</div>';

                                            if ($c % $style == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }
                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                } // End Novajet

                                // Dynamic Page A4
                                else if ($style == 'A4_Dynamic') {

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div style="text-align:' . $Settings->barcode_align . '; line-height: 1.4;" class="item style' . $style . '" >';

                                            foreach ($manage_barcode as $key => $managePosition) {
                                                $optionkey = $managePosition->name_key;

                                                switch ($optionkey) {

                                                    case 'site':
                                                        if ($item['site']) {
                                                            //                                                     
                                                            echo '<div class="barcode_site text-center" style="text-transform: uppercase; margin-bottom: 4px;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '15px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . $item['site'] . '</div>';
                                                        }
                                                        break;

                                                    case 'barcode':

                                                        echo '<span class="barcode_image" >' . $item['barcode'] . '</span>';
                                                        break;

                                                    case 'Date':
                                                        if ($item['Date']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> MFG : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['Date'] . '</small>';
                                                        }


                                                        if ($item['allexpdate']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Exp : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allexpdate'] . '</small>';
                                                        }

                                                        if ($item['NETQTYSHOW']) {
                                                            echo ' &nbsp; <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> NET QTY ' . $item['NetQTY'] . 'N </small><br/>';
                                                        }

                                                        if ($item['allbatchno']) {
                                                            echo '<br/> <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Batch : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allbatchno'] . '</small>';
                                                        }

                                                        break;

                                                    case 'product_name':

                                                        if ($item['name']) {
                                                            echo '<span class="  barcode_name" style=" text-transform: uppercase; font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['name'] . '</span>';
                                                        }
                                                        break;

                                                    case 'price':


                                                        if ($item['price']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size:' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'bold') . ';  display: block;" >' . (($managePosition->display) ? $managePosition->display : lang('Sale price')) . ' ';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }


                                                            echo '</span> ';
                                                        }
                                                        break;

                                                    case 'mrp':
                                                        if ($item['mrp']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . (($managePosition->display) ? $managePosition->display : lang('MRP')) . ' ';
                                                            echo $item['mrp'] . " <span style='font-size:7px;text-transform: initial;'> (incl of all taxes)</span>";
                                                            echo '</span>';
                                                        }
                                                        break;

                                                    case 'unit':
                                                        if ($item['unit']) {
                                                            $getunit = $this->site->getUnitByID($item['unit']);
                                                            echo '<span class="barcode_unit" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . lang('unit') . ': ' . $getunit->name . ' </span> ';
                                                        }
                                                        break;

                                                    case 'category':
                                                        if ($item['category']) {
                                                            echo ' <span class=" barcode_category"  style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['category'] . '  &nbsp; &nbsp; &nbsp;  </span> ';
                                                        }



                                                        break;

                                                    case 'Subcategory':
                                                        if ($item['Subcategory']) {
                                                            echo '<span  style="text-transform: uppercase;' . (($managePosition->display) ? '' : 'display:block;') . ' font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['Subcategory'] . ' </span>';
                                                        }
                                                        break;
                                                    case 'brand':
                                                        if ($item['brand']) {
                                                            echo '<div style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . $item['brand'] . '</div>';
                                                        }

                                                        break;

                                                    case 'variants':
                                                        if ($item['variants']) {
                                                            echo '<table style="width: 90%"><tr><td style="width: 20%">';
                                                            echo '<span class="variants " style=" text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';" > ' . lang('Size');

                                                            echo '</span></td><td style="width: 5%"> : </td><td style="width: 40%">';
                                                            echo '<span class="variants " style=" text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';" > ';

                                                            foreach ($item['variants'] as $variant) {
                                                                if ($item['variant_name'] == $variant->name)
                                                                    echo $variant->name;
                                                            }

                                                            echo '</span></td></tr></table>';
                                                        }

                                                        break;
                                                    case 'color':
                                                            if ($item['color']) {
                                                                 echo '<table style="width: 90%"><tr><td style="width: 20%">';
                                                                    echo '<span class="color " style=" text-transform: uppercase;'.(($managePosition->display)?'':'display:block;').'font-size: '.(($managePosition->font_size)?$managePosition->font_size.'px;' :'13px;').' font-weight:'.(($managePosition->font_weight)?$managePosition->font_weight:'normal').';"> ' . lang('Color');
                                                                    echo '</span></td><td style="width: 5%"> : </td><td style="width: 40%">';
                                                                    echo '<span class="variants " style=" text-transform: uppercase;font-size: '.(($managePosition->font_size)?$managePosition->font_size.'px;' :'13px;').' font-weight:'.(($managePosition->font_weight)?$managePosition->font_weight:'normal').';" > ' ;
                                                                    foreach ($item['color'] as $color) {
                                                                           if ($item['color_name'] == $color)
                                                                               echo $color;
                                                                       }
                                                                    echo '</span></td></tr></table>';
                                                                }
                                                            break;



                                                    case 'image':
                                                        if ($item['image']) {
                                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                                            if (file_exists($imgsrc)) {
                                                                echo '<span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt=""  /></span> ';
                                                            }
                                                        }
                                                        break;

                                                    case 'address':
                                                        if ($item['Address']) {

                                                            echo '<div style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '9px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">';
                                                            if ($item['MFG_PKD']) {
                                                                echo "<strong  style='text-transform: initial;'>MFG & PKD. By : </strong> ";
                                                                echo ($biller->company) ? "<strong style='text-transform: uppercase; font-size:8px'>" . $biller->company . "</strong>" : "<strong style='text-transform: uppercase; font-size:8px;'>" . $biller->name . "</strong>";
                                                            }
                                                            echo "<div style='text-transform: capitalize;  padding: 0px 0px; font-weight: bold;'>" . $biller->cf2 . "</div>";
                                                            echo "</div>";
                                                        }
                                                        break;
                                                }
                                            }

                                            echo '</div>';

                                            if ($c % $pages['sticker_per_page'] == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }
                                    echo '</div>';
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }

                                // End Dynamic Page A4

                                else {


                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    if ($style == 12 || $style == 18 || $style == 24 || $style == 40 || $style == '40N' || $style == 48 || $style == 65 || $style == '21N' || $style == '18N' || $style == '24NN') {
                                        echo '<div class="barcodea4">';
                                    } elseif ($style != 50 || $style != 100) {
                                        echo '<div class="barcode">';
                                    }
                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div style="text-align:' . $Settings->barcode_align . '; line-height: 1.4;" class="item style' . $style . '" ' .
                                                (($style == 50 || $style == 100) && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                                    'style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border:0;"' : '')
                                                . ' >';
                                            if ($style == 50 || $style == 100) {
                                                print_r($this->input->post('cf_orientation'));
                                                exit;
                                                if ($this->input->post('cf_orientation')) {
                                                    $ty = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                                    $landscape = '
                                                    -webkit-transform-origin: 0 0;
                                                    -moz-transform-origin:    0 0;
                                                    -ms-transform-origin:     0 0;
                                                    transform-origin:         0 0;
                                                    -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                    -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                    -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                    transform:         translateY(' . $ty . ') rotate(-90deg);
                                                    ';
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                                } else {
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                                }
                                            }
                                            foreach ($manage_barcode as $key => $managePosition) {
                                                $optionkey = $managePosition->name_key;

                                                switch ($optionkey) {

                                                    case 'site':
                                                        if ($item['site']) {
                                                            //                                                     
                                                            echo '<div class="barcode_site text-center" style="text-transform: uppercase; margin-bottom: 4px;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '15px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . $item['site'] . '</div>';
                                                        }
                                                        break;

                                                    case 'barcode':

                                                        echo '<span class="barcode_image" >' . $item['barcode'] . '</span>';
                                                        break;

                                                    case 'Date':
                                                        if ($item['Date']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> MFG : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['Date'] . '</small>';
                                                        }


                                                        if ($item['allexpdate']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Exp : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allexpdate'] . '</small>';
                                                        }

                                                        if ($item['NETQTYSHOW']) {
                                                            echo ' &nbsp; <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> NET QTY ' . $item['NetQTY'] . 'N </small><br/>';
                                                        }

                                                        if ($item['allbatchno']) {
                                                            echo '<br/> <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Batch : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allbatchno'] . '</small>';
                                                        }

                                                        break;

                                                    case 'product_name':

                                                        if ($item['name']) {
                                                            echo '<span class="  barcode_name" style=" text-transform: uppercase; font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['name'] . '</span>';
                                                        }
                                                        break;

                                                    case 'price':


                                                        if ($item['price']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size:' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'bold') . ';  display: block;" >' . (($managePosition->display) ? $managePosition->display : lang('Sale price')) . ' ';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }


                                                            echo '</span> ';
                                                        }
                                                        break;

                                                    case 'mrp':
                                                        if ($item['mrp']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . (($managePosition->display) ? $managePosition->display : lang('MRP')) . ' ';
                                                            echo $item['mrp'] . " <span style='font-size:7px;text-transform: initial;'> (incl of all taxes)</span>";
                                                            echo '</span>';
                                                        }
                                                        break;

                                                    case 'unit':
                                                        if ($item['unit']) {
                                                            $getunit = $this->site->getUnitByID($item['unit']);
                                                            echo '<span class="barcode_unit" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . lang('unit') . ': ' . $item['unit'] . ' </span> ';
                                                        }
                                                        break;

                                                    case 'category':
                                                        if ($item['category']) {
                                                            echo ' <span class=" barcode_category"  style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['category'] . '  &nbsp; &nbsp; &nbsp;  </span> ';
                                                        }



                                                        break;

                                                    case 'Subcategory':
                                                        if ($item['Subcategory']) {
                                                            echo '<span  style="text-transform: uppercase;' . (($managePosition->display) ? '' : 'display:block;') . ' font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['Subcategory'] . ' </span>';
                                                        }
                                                        break;
                                                    case 'brand':
                                                        if ($item['brand']) {
                                                            echo '<div style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . $item['brand'] . '</div>';
                                                        }

                                                        break;

                                                    case 'variants':
                                                        if ($item['variants']) {
                                                            echo '<table style="width: 90%"><tr><td style="width: 20%">';
                                                            echo '<span class="variants " style=" text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';" > ' . lang('Size');

                                                            echo '</span></td><td style="width: 5%"> : </td><td style="width: 40%">';
                                                            echo '<span class="variants " style=" text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';" > ';

                                                            foreach ($item['variants'] as $variant) {
                                                                if ($variant->name)
                                                                    echo $variant->name . ", ";
                                                            }

                                                            echo '</span></td></tr></table>';
                                                        }

                                                        break;
                                                    case 'color':
                                                        if ($item['color']) {
                                                            echo '<span class="color">' . lang('Promotion Price') . ': ' . $item['color'] . '</span><br> ';
                                                        }
                                                    break;


                                                    case 'image':
                                                        if ($item['image']) {
                                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                                            if (file_exists($imgsrc)) {
                                                                echo '<span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt=""  /></span> ';
                                                            }
                                                        }
                                                        break;
                                                    case 'check_promo':
                                                        if ($item['check_promo']) {
                                                            echo '<span class="check_promo">' . lang('Promotion Price') . ': ' . $item['check_promo'] . '</span><br> ';
                                                        }
                                                        break;
                                                    case 'address':
                                                        if ($item['Address']) {

                                                            echo '<div style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '9px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">';
                                                            // if ($item['MFG_PKD']) {
                                                            echo "<strong  style='text-transform: initial;'>MFG & PKD. By : </strong> ";
                                                            echo ($biller->company) ? "<strong style='text-transform: uppercase; font-size:8px'>" . $biller->company . "</strong>" : "<strong style='text-transform: uppercase; font-size:8px;'>" . $biller->name . "</strong>";
                                                            // }
                                                            echo "<div style='text-transform: capitalize;  padding: 0px 0px; font-weight: bold;'>" . $biller->cf2 . "</div>";
                                                            echo "</div>";
                                                        }
                                                        break;
                                                }
                                            }

                                            if ($style == 50 || $style == 100) {
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                            if ($style == 40) {
                                                if ($c % 40 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == '40N') {
                                                if ($c % '40N' == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 48) {
                                                if ($c % 48 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 65) {
                                                if ($c % 65 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 30) {
                                                if ($c % 30 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 24 || $style == '24NN') {
                                                if ($c % 24 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 20) {
                                                if ($c % 20 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 18) {
                                                if ($c % 18 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 14) {
                                                if ($c % 14 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 12) {
                                                if ($c % 12 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 10) {
                                                if ($c % 10 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            }
                                            $c++;
                                        }
                                    }
                                    if ($style != 50 || $style != 100) {
                                        echo '</div>';
                                    }
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }
                            } else {
                                echo '<h3>' . lang('no_product_selected') . '</h3>';
                            }
                        }
                    } else if ($Settings->barcode_type == 'dynamic2') {
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                /**
                                 * Novajet
                                 */
                                if ($style == '24N') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';

                                            echo '<table style="width:100%;line-height: 1.5;">';
                                            echo '<tr>';
                                            echo '<td width="30%">';
                                            if ($Settings->logo) {
                                                echo '<span class="product_image">';
                                                echo '<img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo) . '" alt="" style="width: 60%; height:60%"/>';
                                                echo '</span>';
                                            }
                                            echo '</td>';
                                            echo '<td style="vertical-align: middle; text-align: left;">';
                                            if ($item['site']) {
                                                echo '<u><span class="barcode_site" style="font-size: 13px"><b> ' . $item['site'] . '</b></span></u>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';



                                            if ($item['category']) {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i>' . $item['category'] . '</i></b></span> ';
                                            } else {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i> &nbsp; </i></b></span> ';
                                            }


                                            if ($item['brand']) {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b>' . $item['brand'] . '</b></span>';
                                            } else {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b> &nbsp; </b></span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name" style="font-size: 12px;line-height: 1.5;"><b>' . $item['name'] . '</b></span>';
                                            }

                                            echo '<table width="100%">';
                                            echo '<tr>';
                                            echo '<td   style="text-align: left;padding-left: 10px;width:40%" >';
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price" style="font-size:11px; font-weight: normal;"> MRP: ';

                                                echo round($item['mrp'], 1);

                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '<td rowspan="2" style="vertical-align: middle;">';

                                            echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';

                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td style="text-align: left;padding-left: 10px;">';
                                            if ($item['price']) {
                                                echo '<span class="barcode_price" style="font-size:12px">' . lang('Price') . ': ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo round($item['price'], 1);
                                                }
                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td colspan="3" style="text-align: left;padding-left: 10px;">';
                                            if ($item['expdate']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Exp: ";
                                                echo $item['expdate'] . "</span>";
                                            }
                                            if ($item['Date']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Mfg: ";
                                                echo $item['Date'] . "</span>";
                                            }
                                            if ($item['batchno']) {
                                                echo "<span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Batch No." . $item['batchno'] . '</span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';


                                            echo '</div>';

                                            if ($c % $style == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }
                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                } // End Novajet

                                // Dynamic A4 page
                                else if ($style == 'A4_Dynamic') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div style="text-align:' . $Settings->barcode_align . '; line-height: 1.4;" class="item style' . $style . '" >';

                                            foreach ($manage_barcode as $key => $managePosition) {
                                                $optionkey = $managePosition->name_key;

                                                switch ($optionkey) {

                                                    case 'dynamic2_site':
                                                        if ($item['site']) {
                                                            //                                                     
                                                            echo '<div class="barcode_site text-center" style="text-transform: uppercase; margin-bottom: 4px;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '15px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . $item['site'] . '</div>';
                                                        }
                                                        break;

                                                    case 'dynamic2_barcode':

                                                        echo '<span class="barcode_image" >' . $item['barcode'] . '</span>';
                                                        break;

                                                    case 'dynamic2_Date':
                                                        if ($item['Date']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> MFG : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['Date'] . '</small>';
                                                        }

                                                        if ($item['allexpdate']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Exp : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allexpdate'] . '</small>';
                                                        }

                                                        if ($item['NETQTYSHOW']) {
                                                            echo ' &nbsp; <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> NET QTY ' . $item['NetQTY'] . 'N </small><br/>';
                                                        }

                                                        if ($item['allbatchno']) {
                                                            echo '<br/> <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Batch : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allbatchno'] . '</small>';
                                                        }

                                                        break;

                                                    case 'dynamic2_product_name':

                                                        if ($item['name']) {
                                                            echo '<span class="  barcode_name" style=" text-transform: uppercase; font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['name'] . '</span>';
                                                        }
                                                        break;

                                                    case 'dynamic2_price':


                                                        if ($item['price']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size:' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'bold') . ';  display: block;" >' . (($managePosition->display) ? $managePosition->display : lang('Sale price')) . ' ';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }

                                                            echo '</span> ';
                                                        }
                                                        break;

                                                    case 'dynamic2_mrp':
                                                        if ($item['mrp']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . (($managePosition->display) ? $managePosition->display : lang('MRP')) . ' ';
                                                            echo $item['mrp'] . " <span style='font-size:7px;text-transform: initial;'> (incl of all taxes)</span>";
                                                            echo '</span>';
                                                        }
                                                        break;

                                                    case 'dynamic2_unit':
                                                        if ($item['unit']) {
                                                            $getunit = $this->site->getUnitByID($item['unit']);
                                                            echo '<span class="barcode_unit" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . lang('unit') . ': ' . $getunit->name . ' </span> ';
                                                        }
                                                        break;

                                                    case 'dynamic2_category_subcategory':
                                                        echo '<table width="90%"><tr>';
                                                        if ($item['category']) {
                                                            echo '<td width="20%">';
                                                            echo ' <span class=" barcode_category"  style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['category'] . '</span> ';
                                                            echo '</td><td width="5%">&nbsp</td>';
                                                        }

                                                        if ($item['Subcategory']) {
                                                            echo '<td width="40%">';
                                                            echo '<span  style="text-transform: uppercase;' . (($managePosition->display) ? '' : 'display:block;') . ' font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['Subcategory'] . ' </span>';
                                                            echo '</td>';
                                                        }
                                                        echo '</tr></table>';

                                                        break;


                                                    case 'dynamic2_brand':
                                                        if ($item['brand']) {
                                                            echo '<div style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . $item['brand'] . '</div>';
                                                        }

                                                        break;

                                                    case 'dynamic2_variants_color':
                                                        echo '<table width="90%"><tr>';
                                                            if ($item['variants']) {
                                                                echo '<td width="20%">';
                                                                echo '<span class="variants " style=" text-transform: uppercase;font-size: '.(($managePosition->font_size)?$managePosition->font_size.'px;' :'13px;').' font-weight:'.(($managePosition->font_weight)?$managePosition->font_weight:'normal').';" > ' . lang('Size') . ' : ';
                                                                foreach ($item['variants'] as $variant) {
                                                                    if ($item['variant_name'] == $variant->name)
                                                                        echo $variant->name. '';
                                                                }
                                                                    echo '</span>';
                                                            }
                                                        echo '</td><td width="5%">&nbsp</td>';
                                                        if ($item['color']) {
                                                            echo '<td width="40%">';
                                                            echo '<span class="color " style=" text-transform: uppercase;'.(($managePosition->display)?'':'display:block;').'font-size: '.(($managePosition->font_size)?$managePosition->font_size.'px;' :'13px;').' font-weight:'.(($managePosition->font_weight)?$managePosition->font_weight:'normal').';"> ' . lang('Color') . ' : ';
                                                                foreach ($item['color'] as $color) {
                                                                    if ($item['color_name'] == $color)
                                                                        echo $color;
                                                                    }
                                                                echo '</span>  ';
                                                                echo '</td>';
                                                        }
                                                        echo '</tr></table>';
                                                    break;

                                                    case 'dynamic2_image':
                                                        if ($item['image']) {
                                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                                            if (file_exists($imgsrc)) {
                                                                echo '<span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt=""  /></span> ';
                                                            }
                                                        }
                                                        break;

                                                    case 'dynamic2_address':
                                                        if ($item['Address']) {

                                                            echo '<div style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '9px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">';
                                                            if ($item['MFG_PKD']) {
                                                                echo "<strong  style='text-transform: initial;'>MFG & PKD. By : </strong> ";
                                                                echo ($biller->company) ? "<strong style='text-transform: uppercase; font-size:8px'>" . $biller->company . "</strong>" : "<strong style='text-transform: uppercase; font-size:8px;'>" . $biller->name . "</strong>";
                                                            }
                                                            echo "<div style='text-transform: capitalize;  padding: 0px 0px; font-weight: bold;'>" . $biller->cf2 . "</div>";
                                                            echo "</div>";
                                                        }
                                                        break;
                                                }
                                            }
                                            echo '</div>';
                                            if ($c % $pages['sticker_per_page'] == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }

                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }

                                // End Dynamic A4 Page

                                else { ///////////////////////////////////////////////////////////////////


                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    if ($style == 12 || $style == 18 || $style == 24 || $style == 40 || $style == '40N' || $style == 48 || $style == 65 || $style == '21N' || $style == '18N' || $style == '24NN') {
                                        echo '<div class="barcodea4">';
                                    } elseif ($style != 50 || $style != 100) {
                                        echo '<div class="barcode">';
                                    }
                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div style="text-align:' . $Settings->barcode_align . '; line-height: 1.4;" class="item style' . $style . '" ' .
                                                (($style == 50 || $style == 100) && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                                    'style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border:0;"' : '')
                                                . ' >';
                                            if ($style == 50 || $style == 100) {
                                                if ($this->input->post('cf_orientation')) {
                                                    $ty = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                                    $landscape = '
                                                    -webkit-transform-origin: 0 0;
                                                    -moz-transform-origin:    0 0;
                                                    -ms-transform-origin:     0 0;
                                                    transform-origin:         0 0;
                                                    -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                    -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                    -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                    transform:         translateY(' . $ty . ') rotate(-90deg);
                                                    ';
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                                } else {
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                                }
                                            }
                                            foreach ($manage_barcode as $key => $managePosition) {
                                                $optionkey = $managePosition->name_key;

                                                switch ($optionkey) {

                                                    case 'dynamic2_site':
                                                        if ($item['site']) {
                                                            //                                                     
                                                            echo '<div class="barcode_site text-center" style="text-transform: uppercase; margin-bottom: 4px;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '15px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . $item['site'] . '</div>';
                                                        }
                                                        break;

                                                    case 'dynamic2_barcode':

                                                        echo '<span class="barcode_image" >' . $item['barcode'] . '</span>';
                                                        break;

                                                    case 'dynamic2_Date':
                                                        if ($item['Date']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> MFG : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['Date'] . '</small>';
                                                        }

                                                        if ($item['allexpdate']) {
                                                            echo ' <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Exp : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allexpdate'] . '</small>';
                                                        }

                                                        if ($item['NETQTYSHOW']) {
                                                            echo ' &nbsp; <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> NET QTY ' . $item['NetQTY'] . 'N </small><br/>';
                                                        }

                                                        if ($item['allbatchno']) {
                                                            echo '<br/> <small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; "> Batch : </small>';
                                                            echo '<small style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '10px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['allbatchno'] . '</small>';
                                                        }

                                                        break;

                                                    case 'dynamic2_product_name':

                                                        if ($item['name']) {
                                                            echo '<span class="  barcode_name" style=" text-transform: uppercase; font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; ">' . $item['name'] . '</span>';
                                                        }
                                                        break;

                                                    case 'dynamic2_price':


                                                        if ($item['price']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size:' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'bold') . ';  display: block;" >' . (($managePosition->display) ? $managePosition->display : lang('Sale price')) . ' ';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }

                                                            echo '</span> ';
                                                        }
                                                        break;

                                                    case 'dynamic2_mrp':
                                                        if ($item['mrp']) {
                                                            echo '<span class="barcode_price" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . '; " >' . (($managePosition->display) ? $managePosition->display : lang('MRP')) . ' ';
                                                            echo $item['mrp'] . " <span style='font-size:7px;text-transform: initial;'> (incl of all taxes)</span>";
                                                            echo '</span>';
                                                        }
                                                        break;

                                                    case 'dynamic2_unit':
                                                        if ($item['unit']) {
                                                            $getunit = $this->site->getUnitByID($item['unit']);
                                                            echo '<span class="barcode_unit" style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '12px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . lang('unit') . ': ' . $getunit->name . ' </span> ';
                                                        }
                                                        break;

                                                    case 'dynamic2_category_subcategory':
                                                        echo '<table width="90%"><tr>';
                                                        if ($item['category']) {
                                                            echo '<td width="20%">';
                                                            echo ' <span class=" barcode_category"  style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['category'] . '</span> ';
                                                            echo '</td><td width="5%">&nbsp</td>';
                                                        }

                                                        if ($item['Subcategory']) {
                                                            echo '<td width="40%">';
                                                            echo '<span  style="text-transform: uppercase;' . (($managePosition->display) ? '' : 'display:block;') . ' font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';"> ' . $item['Subcategory'] . ' </span>';
                                                            echo '</td>';
                                                        }
                                                        echo '</tr></table>';

                                                        break;


                                                    case 'dynamic2_brand':
                                                        if ($item['brand']) {
                                                            echo '<div style="text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">' . $item['brand'] . '</div>';
                                                        }

                                                        break;

                                                    case 'dynamic2_variants':

                                                        if ($item['variants']) {
                                                            echo '<table width="90%"><tr>';
                                                            echo '<td width="20%">';
                                                            echo '<span class="variants " style=" text-transform: uppercase;font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '13px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';" > ' . lang('Size') . ' : ';
                                                            foreach ($item['variants'] as $variant) {
                                                                if ($item['variant_name'] == $variant->name)
                                                                    echo $variant->name . '';
                                                            }
                                                            echo '</span>';


                                                            echo '</tr></table>';
                                                        }


                                                        break;



                                                    case 'dynamic2_image':
                                                        if ($item['image']) {
                                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                                            if (file_exists($imgsrc)) {
                                                                echo '<span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt=""  /></span> ';
                                                            }
                                                        }
                                                        break;

                                                    case 'dynamic2_address':
                                                        if ($item['Address']) {

                                                            echo '<div style="font-size: ' . (($managePosition->font_size) ? $managePosition->font_size . 'px;' : '9px;') . ' font-weight:' . (($managePosition->font_weight) ? $managePosition->font_weight : 'normal') . ';">';
                                                            if ($item['MFG_PKD']) {
                                                                echo "<strong  style='text-transform: initial;'>MFG & PKD. By : </strong> ";
                                                                echo ($biller->company) ? "<strong style='text-transform: uppercase; font-size:8px'>" . $biller->company . "</strong>" : "<strong style='text-transform: uppercase; font-size:8px;'>" . $biller->name . "</strong>";
                                                            }
                                                            echo "<div style='text-transform: capitalize;  padding: 0px 0px; font-weight: bold;'>" . $biller->cf2 . "</div>";
                                                            echo "</div>";
                                                        }
                                                        break;
                                                }
                                            }

                                            if ($style == 50 || $style == 100) {
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                            if ($style == 40) {
                                                if ($c % 40 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == '40N') {
                                                if ($c % '40N' == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 48) {
                                                if ($c % 48 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 65) {
                                                if ($c % 65 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 30) {
                                                if ($c % 30 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 24 || $style == '24NN') {
                                                if ($c % 24 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 20) {
                                                if ($c % 20 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 18) {
                                                if ($c % 18 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 14) {
                                                if ($c % 14 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 12) {
                                                if ($c % 12 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 10) {
                                                if ($c % 10 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            }
                                            $c++;
                                        }
                                    }
                                    if ($style != 50 || $style != 100) {
                                        echo '</div>';
                                    }
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }
                            } else {
                                echo '<h3>' . lang('no_product_selected') . '</h3>';
                            }
                        }
                    } else if ($Settings->barcode_type == 'sidebyside') {
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                if ($style == '24N') { // NovaJet
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';

                                            echo '<table style="width:100%;line-height: 1.5;">';
                                            echo '<tr>';
                                            echo '<td width="30%">';
                                            if ($Settings->logo) {
                                                echo '<span class="product_image">';
                                                echo '<img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo) . '" alt="" style="width: 60%; height:60%"/>';
                                                echo '</span>';
                                            }
                                            echo '</td>';
                                            echo '<td style="vertical-align: middle; text-align: left;">';
                                            if ($item['site']) {
                                                echo '<u><span class="barcode_site" style="font-size: 13px"><b> ' . $item['site'] . '</b></span></u>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';



                                            if ($item['category']) {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i>' . $item['category'] . '</i></b></span> ';
                                            } else {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i> &nbsp; </i></b></span> ';
                                            }


                                            if ($item['brand']) {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b>' . $item['brand'] . '</b></span>';
                                            } else {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b> &nbsp; </b></span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name" style="font-size: 12px;line-height: 1.5;"><b>' . $item['name'] . '</b></span>';
                                            }

                                            echo '<table width="100%">';
                                            echo '<tr>';
                                            echo '<td   style="text-align: left;padding-left: 10px;width:40%" >';
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price" style="font-size:11px; font-weight: normal;"> MRP: ';

                                                echo round($item['mrp'], 1);

                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '<td rowspan="2" style="vertical-align: middle;">';

                                            echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';

                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td style="text-align: left;padding-left: 10px;">';
                                            if ($item['price']) {
                                                echo '<span class="barcode_price" style="font-size:12px">' . lang('Price') . ': ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo round($item['price'], 1);
                                                }
                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td colspan="3" style="text-align: left;padding-left: 10px;">';
                                            if ($item['expdate']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Exp: ";
                                                echo $item['expdate'] . "</span>";
                                            }
                                            if ($item['Date']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Mfg: ";
                                                echo $item['Date'] . "</span>";
                                            }
                                            if ($item['batchno']) {
                                                echo "<span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Batch No." . $item['batchno'] . '</span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';


                                            echo '</div>';

                                            if ($c % $style == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }
                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                } //End Novajet

                                // Dynamic A4 page
                                else if ($style == 'A4_Dynamic') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;

                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';

                                            echo '<table width="90%" style="font-weight: bold; text-align:' . $Settings->barcode_align . ';  line-height: 1.4; text-transform: uppercase;">';

                                            foreach ($manage_barcode as $key => $managePositionside) {
                                                $optionkeyside = $managePositionside->name_key;

                                                switch ($optionkeyside) {

                                                    case 'side_site':
                                                        echo '<tr><td colspan="3" >';
                                                        if ($item['site']) {
                                                            echo '<div class="barcode_site" style="margin-bottom: 2px;text-align:center;font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '15') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">' . $item['site'] . '</div>';
                                                        }
                                                        echo '</td></tr>';
                                                        break;

                                                    case 'side_barcode':
                                                        echo '<tr><td colspan="3">';
                                                        $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                                        if ($item['image']) {
                                                            if (file_exists($imgsrc)) {
                                                                echo '<div style="width:100%; text-align:center;"><span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt=""  /></span>';
                                                                echo '<span class="barcode_image" >' . $item['barcode'] . '</span></div>';
                                                            } else {
                                                                echo "";
                                                                echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';
                                                            }
                                                        } else {
                                                            echo "";
                                                            echo '<span class="barcode_image" style="text-align:center; ">' . $item['barcode'] . '</span>';
                                                        }
                                                        echo '</td></tr>';
                                                        break;

                                                    case 'side_productname':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';
                                                        if ($item['name']) {
                                                            echo '<td style="width: 45% !important;">';
                                                            echo $item['name'];
                                                            echo '</td><td width="5%"></td>';
                                                        }

                                                        echo '</tr>';

                                                        break;

                                                    case 'side_brandname':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';"> ';
                                                        if ($item['brand']) {
                                                            echo '<td style="width: 50% !important;">';
                                                            echo $item['brand_code'];
                                                            echo '</td><td width="5%"></td>';
                                                        }

                                                        echo '</tr>';
                                                        break;

                                                    case 'side_categorySubcategory':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';
                                                        if ($item['category']) {
                                                            echo '<td style="width: 45% !important;">';
                                                            echo $item['category_code'];
                                                            echo '</td><td width="5%"></td>';
                                                        }
                                                        if ($item['Subcategory']) {
                                                            echo '<td >';
                                                            echo $item['Subcategory_code'];
                                                            echo '</td>';
                                                        }
                                                        echo '</tr>';
                                                        break;
                                                    case 'side_variants':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';

                                                        if ($item['variants']) {
                                                            echo '<td style="width: 45% !important;">';
                                                            echo lang('Size') . ': ';
                                                            foreach ($item['variants'] as $variant) {
                                                                if ($item['variant_name'] == $variant->name)
                                                                    echo $variant->name;
                                                            }
                                                            echo '</td><td width="5%"></td>';
                                                        }

                                                        echo '</tr>';
                                                        break;

                                                    case 'side_productprice':
                                                        if ($item['price']) {
                                                            echo '<tr>';
                                                            echo '<td colspan="3">';
                                                            echo '<span class="barcode_price" style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; text-transform: uppercase; display: block;" >' . (($managePositionside->display) ? $managePositionside->display : lang('Sale price')) . ' ';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }



                                                            echo '</span> ';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        break;

                                                    case 'side_mrp':
                                                        if ($item['mrp']) {
                                                            echo '<tr>';
                                                            echo '<td colspan="3">';
                                                            echo '<span class="barcode_price" style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; text-transform: uppercase; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; " >' . (($managePositionside->display) ? $managePositionside->display : lang('MRP')) . ' ';
                                                            echo $item['mrp'] . " <span style='font-size:7px;text-transform: initial;'> (incl of all taxes)</span>";
                                                            echo '</span>';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        break;
                                                    case 'side_mfg_date':
                                                        echo '<tr>';
                                                        echo '<td colspan="3" style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; ">';
                                                        if ($item['Date']) {

                                                            echo " <small > MFG : </small>";
                                                            echo "<small>" . $item['Date'] . "</small> &nbsp; &nbsp; &nbsp; ";
                                                        }

                                                        if ($item['allbatchno']) {

                                                            echo " <br/><small > Batch : </small>";
                                                            echo "<small>" . $item['allbatchno'] . "</small> &nbsp; &nbsp; &nbsp; ";
                                                        }

                                                        if ($item['NETQTYSHOW']) {
                                                            echo '<small style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; "> NET QTY ' . $item['NetQTY'] . 'N </small>';
                                                        }

                                                        if ($item['allbatchno']) {

                                                            echo " <br/><small > Batch : </small>";
                                                            echo "<small>" . $item['allbatchno'] . "</small> &nbsp; &nbsp; &nbsp; ";
                                                        }
                                                        echo '</td>';
                                                        echo '</tr>';
                                                        break;

                                                    case 'side_address':

                                                        if ($item['Address']) {
                                                            echo '<tr>';
                                                            echo '<td colspan="3">';

                                                            echo '<small style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '9') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';
                                                            if ($item['MFG_PKD']) {
                                                                echo '<strong  style="text-transform: initial; font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '9') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">MFG & PKD. By : </strong>';
                                                                echo ($biller->company) ? "<strong style='text-transform: uppercase; font-size:8px;'>" . $biller->company . "</strong>" : "<strong style='text-transform: uppercase; font-size:8px'>" . $biller->name . "</strong>";
                                                            }
                                                            echo '<div style="text-transform: capitalize;  padding: 0px 0px; font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '9') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">' . $biller->cf2 . '</div>';
                                                            echo "</small>";
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        break;
                                                }
                                            }

                                            echo '</table>';

                                            echo '</div>';
                                            if ($c % $pages['sticker_per_page'] == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }

                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }
                                //End Dynamic A4 page
                                else {

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    if (
                                        $style == 12 || $style == 18 || $style == 24 || $style == 40 || $style == '40N' ||  $style == 48
                                        ||  $style == 65 || $style == '21N' || $style == '18N' || $style == '24NN'
                                    ) {
                                        echo '<div class="barcodea4">';
                                    } elseif ($style != 50 || $style != 100) {
                                        echo '<div class="barcode">';
                                    }
                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" ' .
                                                (($style == 50 || $style == 100) && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                                    'style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border:0;"' : '')
                                                . '>';
                                            if ($style == 50 || $style == 100) {
                                                if ($this->input->post('cf_orientation')) {
                                                    $ty = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                                    $landscape = '
                                                    -webkit-transform-origin: 0 0;
                                                    -moz-transform-origin:    0 0;
                                                    -ms-transform-origin:     0 0;
                                                    transform-origin:         0 0;
                                                    -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                    -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                    -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                    transform:         translateY(' . $ty . ') rotate(-90deg);
                                                    ';
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                                } else {
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                                }
                                            }

                                            echo '<table width="90%" style="font-weight: bold; text-align:' . $Settings->barcode_align . ';  line-height: 1.4; text-transform: uppercase;">';

                                            foreach ($manage_barcode as $key => $managePositionside) {
                                                $optionkeyside = $managePositionside->name_key;




                                                switch ($optionkeyside) {

                                                    case 'side_site':
                                                        echo '<tr><td colspan="3" >';
                                                        if ($item['site']) {
                                                            echo '<div class="barcode_site" style="margin-bottom: 2px;text-align:center;font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '15') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">' . $item['site'] . '</div>';
                                                        }
                                                        echo '</td></tr>';
                                                        break;

                                                    case 'side_barcode':
                                                        echo '<tr><td colspan="3">';
                                                        $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                                        if ($item['image']) {
                                                            if (file_exists($imgsrc)) {
                                                                echo '<div style="width:100%; text-align:center;"><span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt=""  /></span>';
                                                                echo '<span class="barcode_image" >' . $item['barcode'] . '</span></div>';
                                                            } else {
                                                                echo "";
                                                                echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';
                                                            }
                                                        } else {
                                                            echo "";
                                                            echo '<span class="barcode_image" style="text-align:center; ">' . $item['barcode'] . '</span>';
                                                        }
                                                        echo '</td></tr>';
                                                        break;

                                                    case 'side_productname':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';
                                                        if ($item['name']) {
                                                            echo '<td style="width: 45% !important;">';
                                                            echo $item['name'];
                                                            echo '</td><td width="5%"></td>';
                                                        }

                                                        echo '</tr>';

                                                        break;

                                                    case 'side_brandname':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';"> ';
                                                        if ($item['brand']) {
                                                            echo '<td style="width: 50% !important;">';
                                                            echo $item['brand_code'];
                                                            echo '</td><td width="5%"></td>';
                                                        }

                                                        echo '</tr>';
                                                        break;

                                                    case 'side_categorySubcategory':
                                                        echo '<tr style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';
                                                        if ($item['category']) {
                                                            echo '<td style="width: 45% !important;">';
                                                            echo $item['category_code'];
                                                            echo '</td><td width="5%"></td>';
                                                        }
                                                        if ($item['Subcategory']) {
                                                            echo '<td >';
                                                            echo $item['Subcategory_code'];
                                                            echo '</td>';
                                                        }
                                                        echo '</tr>';
                                                        break;
                                                    case 'side_sizecolor':
                                                        echo '<tr style="font-size:'.(($managePositionside->font_size)?$managePositionside->font_size :'13').'px; font-weight:'.(($managePositionside->font_weight)?$managePositionside->font_weight:'normal').';">';

                                                        if($item['variants']){
                                                            echo '<td style="width: 45% !important;">';
                                                            echo  lang('Size') . ': ';
                                                                foreach ($item['variants'] as $variant) {
                                                                    if ($item['variant_name'] == $variant->name)
                                                                        echo $variant->name;
                                                                    }
                                                                        echo '</td><td width="5%"></td>';

                                                        }
                                                        if($item['color']){
                                                            echo '<td >';
                                                            echo lang('Color') . ' : ';
                                                            echo $item['color_name'];
                                                            echo '</td>';
                                                        }
                                                            echo '</tr>';
                                                    break;

                                                    case 'side_productprice':
                                                        if ($item['price']) {
                                                            echo '<tr>';
                                                            echo '<td colspan="3">';
                                                            echo '<span class="barcode_price" style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; text-transform: uppercase; display: block;" >' . (($managePositionside->display) ? $managePositionside->display : lang('Sale price')) . ' ';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }



                                                            echo '</span> ';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        break;

                                                    case 'side_mrp':
                                                        if ($item['mrp']) {
                                                            echo '<tr>';
                                                            echo '<td colspan="3">';
                                                            echo '<span class="barcode_price" style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; text-transform: uppercase; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; " >' . (($managePositionside->display) ? $managePositionside->display : lang('MRP')) . ' ';
                                                            echo $item['mrp'] . " <span style='font-size:7px;text-transform: initial;'> (incl of all taxes)</span>";
                                                            echo '</span>';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        break;
                                                    case 'side_mfg_date':
                                                        echo '<tr>';
                                                        echo '<td colspan="3" style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; ">';
                                                        if ($item['Date']) {

                                                            echo " <small > MFG : </small>";
                                                            echo "<small>" . $item['Date'] . "</small> &nbsp; &nbsp; &nbsp; ";
                                                        }

                                                        if ($item['allbatchno']) {

                                                            echo " <br/><small > Batch : </small>";
                                                            echo "<small>" . $item['allbatchno'] . "</small> &nbsp; &nbsp; &nbsp; ";
                                                        }

                                                        if ($item['NETQTYSHOW']) {
                                                            echo '<small style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '13') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . '; "> NET QTY ' . $item['NetQTY'] . 'N </small>';
                                                        }

                                                        if ($item['allbatchno']) {

                                                            echo " <br/><small > Batch : </small>";
                                                            echo "<small>" . $item['allbatchno'] . "</small> &nbsp; &nbsp; &nbsp; ";
                                                        }
                                                        echo '</td>';
                                                        echo '</tr>';
                                                        break;

                                                    case 'side_address':

                                                        if ($item['Address']) {
                                                            echo '<tr>';
                                                            echo '<td colspan="3">';

                                                            echo '<small style="font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '9') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">';
                                                            if ($item['MFG_PKD']) {
                                                                echo '<strong  style="text-transform: initial; font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '9') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">MFG & PKD. By : </strong>';
                                                                echo ($biller->company) ? "<strong style='text-transform: uppercase; font-size:8px;'>" . $biller->company . "</strong>" : "<strong style='text-transform: uppercase; font-size:8px'>" . $biller->name . "</strong>";
                                                            }
                                                            echo '<div style="text-transform: capitalize;  padding: 0px 0px; font-size:' . (($managePositionside->font_size) ? $managePositionside->font_size : '9') . 'px; font-weight:' . (($managePositionside->font_weight) ? $managePositionside->font_weight : 'normal') . ';">' . $biller->cf2 . '</div>';
                                                            echo "</small>";
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        break;
                                                }
                                            }

                                            echo '</table>';
                                            if ($style == 50 ||  $style == 100) {
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                            if ($style == 40) {
                                                if ($c % 40 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == '40N') {
                                                if ($c % '40N' == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 48) {
                                                if ($c % 48 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 65) {
                                                if ($c % 65 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 30) {
                                                if ($c % 30 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 24 || $style == '24NN') {
                                                if ($c % 24 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 20) {
                                                if ($c % 20 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 18) {
                                                if ($c % 18 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 14) {
                                                if ($c % 14 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 12) {
                                                if ($c % 12 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 10) {
                                                if ($c % 10 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            }
                                            $c++;
                                        }
                                    }
                                    if ($style != 50 || $style != 100) {
                                        echo '</div>';
                                    }
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }
                            } else {
                                echo '<h3>' . lang('no_product_selected') . '</h3>';
                            }
                        }
                    } else if ($Settings->barcode_type == 'sillagefragrances') {
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                /**
                                 * NovaJet
                                 */
                                if ($style == '24N') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';

                                            echo '<table style="width:100%;line-height: 1.5;">';
                                            echo '<tr>';
                                            echo '<td width="30%">';
                                            if ($Settings->logo) {
                                                echo '<span class="product_image">';
                                                echo '<img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo) . '" alt="" style="width: 60%; height:60%"/>';
                                                echo '</span>';
                                            }
                                            echo '</td>';
                                            echo '<td style="vertical-align: middle; text-align: left;">';
                                            if ($item['site']) {
                                                echo '<u><span class="barcode_site" style="font-size: 13px"><b> ' . $item['site'] . '</b></span></u>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';



                                            if ($item['category']) {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i>' . $item['category'] . '</i></b></span> ';
                                            } else {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i> &nbsp; </i></b></span> ';
                                            }


                                            if ($item['brand']) {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b>' . $item['brand'] . '</b></span>';
                                            } else {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b> &nbsp; </b></span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name" style="font-size: 12px;line-height: 1.5;"><b>' . $item['name'] . '</b></span>';
                                            }

                                            echo '<table width="100%">';
                                            echo '<tr>';
                                            echo '<td   style="text-align: left;padding-left: 10px;width:40%" >';
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price" style="font-size:11px; font-weight: normal;"> MRP: ';

                                                echo round($item['mrp'], 1);

                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '<td rowspan="2" style="vertical-align: middle;">';

                                            echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';

                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td style="text-align: left;padding-left: 10px;">';
                                            if ($item['price']) {
                                                echo '<span class="barcode_price" style="font-size:12px">' . lang('Price') . ': ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo round($item['price'], 1);
                                                }
                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td colspan="3" style="text-align: left;padding-left: 10px;">';
                                            if ($item['expdate']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Exp: ";
                                                echo $item['expdate'] . "</span>";
                                            }
                                            if ($item['Date']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Mfg: ";
                                                echo $item['Date'] . "</span>";
                                            }
                                            if ($item['batchno']) {
                                                echo "<span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Batch No." . $item['batchno'] . '</span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';


                                            echo '</div>';

                                            if ($c % $style == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }
                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }  // End NovaJet

                                // Dynamic A4 page
                                else if ($style == 'A4_Dynamic') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';


                                            echo '<spna style="font-size:10px">Researched and Marketed by </span>';

                                            if ($item['site']) {
                                                echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name">' . $item['name'] . '</span>';
                                            }

                                            if ($item['brand']) {
                                                echo '<span class="barcode_name">' . $item['brand'] . '</span>';
                                            }

                                            if ($item['price']) {
                                                echo '<span class="barcode_price">' . lang('price') . ' ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo $item['price'];
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price">' . lang('MRP') . ' ';

                                                echo $item['mrp'] . ' ';

                                                echo '</span> ';
                                            }
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['color']) {
                                                echo '<tr><td><span class="color">' . lang('Color') . '</span></td><td>:</td><td> <span class="color"> ';
                                                foreach ($item['color'] as $color) {
                                                    if ($item['color_name'] == $color)
                                                    echo $color . '';
                                                }
                                                echo '</span></td></tr>';
                                            }
                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                            if ($item['image']) {
                                                if (file_exists($imgsrc)) {
                                                    echo '<div class="barcode-media-wrap"><span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                                    echo '<span class="barcode_image" >' . $item['barcode'] . '</span></div>';
                                                } else {
                                                    echo "";
                                                    echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';
                                                }
                                            } else {
                                                echo "";
                                                echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';
                                            }

                                            if ($item['Date']) {
                                                echo " <span> Mfg : </span>";
                                                echo "<span>" . $item['Date'] . "</span>";
                                            }
                                            if ($item['allexpdate']) {
                                                echo "<span> Exp : </span>";
                                                echo "<span>" . $item['allexpdate'] . "</span> </br>";
                                            }

                                            echo " QTY " . $item['pro_quantity'] . '&nbsp;';
                                            if ($item['allbatchno']) {
                                                echo " <span> Batch  : </span>";
                                                echo "<span>" . $item['allbatchno'] . "</span>";
                                            }


                                            if ($item['Address']) {
                                                echo "<small style='font-size: 9px;   '>";

                                                echo "<strong  style='text-transform: initial;'> MFG & PKD. By : </strong> ";
                                                echo ($biller->company) ? "<strong>" . $biller->company . "</strong>" : "<strong>" . $biller->name . "</strong>";
                                                echo "<div style='text-transform: capitalize;  padding: 0px 5px;'>" . $biller->cf2 . "</div>";

                                                echo "</small>";
                                            }

                                            echo '</div>';
                                            if ($c % $pages['sticker_per_page'] == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }

                                    echo '</div>';
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }
                                // End Dynamic A4 Page

                                else {

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    if ($style == 12 || $style == 18 || $style == 24 || $style == 40 || $style == '40N' || $style == 48 || $style == 65 || $style == '21N' || $style == '18N' || $style == '24NN') {
                                        echo '<div class="barcodea4">';
                                    } elseif ($style != 50 || $style != 100) {
                                        echo '<div class="barcode">';
                                    }
                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" ' .
                                                (($style == 50 || $style == 100) && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                                    'style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border:0;"' : '')
                                                . '>';
                                            if ($style == 50 || $style == 100) {
                                                if ($this->input->post('cf_orientation')) {
                                                    $ty = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                                    $landscape = '
                                                    -webkit-transform-origin: 0 0;
                                                    -moz-transform-origin:    0 0;
                                                    -ms-transform-origin:     0 0;
                                                    transform-origin:         0 0;
                                                    -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                    -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                    -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                    transform:         translateY(' . $ty . ') rotate(-90deg);
                                                    ';
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                                } else {
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                                }
                                            }

                                            echo '<spna style="font-size:10px">Researched and Marketed by </span>';

                                            if ($item['site']) {
                                                echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name">' . $item['name'] . '</span>';
                                            }

                                            if ($item['brand']) {
                                                echo '<span class="barcode_name">' . $item['brand'] . '</span>';
                                            }

                                            if ($item['price']) {
                                                echo '<span class="barcode_price">' . lang('price') . ' ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo $item['price'];
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price">' . lang('MRP') . ' ';

                                                echo $item['mrp'] . ' ';

                                                echo '</span> ';
                                            }
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                            if ($item['image']) {
                                                if (file_exists($imgsrc)) {
                                                    echo '<div class="barcode-media-wrap"><span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                                    echo '<span class="barcode_image" >' . $item['barcode'] . '</span></div>';
                                                } else {
                                                    echo "";
                                                    echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';
                                                }
                                            } else {
                                                echo "";
                                                echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';
                                            }

                                            if ($item['Date']) {
                                                echo " <span> Mfg : </span>";
                                                echo "<span>" . $item['Date'] . "</span>";
                                            }
                                            if ($item['allexpdate']) {
                                                echo "<span> Exp : </span>";
                                                echo "<span>" . $item['allexpdate'] . "</span> </br>";
                                            }

                                            echo " QTY " . $item['pro_quantity'] . '&nbsp;';
                                            if ($item['allbatchno']) {
                                                echo " <span> Batch  : </span>";
                                                echo "<span>" . $item['allbatchno'] . "</span>";
                                            }


                                            if ($item['Address']) {
                                                echo "<small style='font-size: 9px;   '>";

                                                echo "<strong  style='text-transform: initial;'> MFG & PKD. By : </strong> ";
                                                echo ($biller->company) ? "<strong>" . $biller->company . "</strong>" : "<strong>" . $biller->name . "</strong>";
                                                echo "<div style='text-transform: capitalize;  padding: 0px 5px;'>" . $biller->cf2 . "</div>";

                                                echo "</small>";
                                            }
                                            if ($style == 50 || $style == 100) {
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                            if ($style == 40) {
                                                if ($c % 40 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == '40N') {
                                                if ($c % '40N' == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 48) {
                                                if ($c % 48 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 65) {
                                                if ($c % 65 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 30) {
                                                if ($c % 30 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 24 || $style == '24NN') {
                                                if ($c % 24 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 20) {
                                                if ($c % 20 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 18) {
                                                if ($c % 18 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 14) {
                                                if ($c % 14 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            } elseif ($style == 12) {
                                                if ($c % 12 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                }
                                            } elseif ($style == 10) {
                                                if ($c % 10 == 0) {
                                                    echo '</div><div class="clearfix"></div><div class="barcode">';
                                                }
                                            }
                                            $c++;
                                        }
                                    }
                                    if ($style != 50 || $style != 100) {
                                        echo '</div>';
                                    }
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }
                            } else {
                                echo '<h3>' . lang('no_product_selected') . '</h3>';
                            }
                        }
                    } else {
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                /**
                                 * NovaJet
                                 */
                                if ($style == '24N') {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;
                                    echo '<div class="barcodea4">';

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '" >';

                                            echo '<table style="width:100%;line-height: 1.5;">';
                                            echo '<tr>';
                                            echo '<td width="30%">';
                                            if ($Settings->logo) {
                                                echo '<span class="product_image">';
                                                echo '<img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo) . '" alt="" style="width: 60%; height:60%"/>';
                                                echo '</span>';
                                            }
                                            echo '</td>';
                                            echo '<td style="vertical-align: middle; text-align: left;">';
                                            if ($item['site']) {
                                                echo '<u><span class="barcode_site" style="font-size: 13px"><b> ' . $item['site'] . '</b></span></u>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';



                                            if ($item['category']) {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i>' . $item['category'] . '</i></b></span> ';
                                            } else {
                                                echo '<span class="barcode_category" style="display: inline;font-size:11px; line-height: 1;"><b><i> &nbsp; </i></b></span> ';
                                            }


                                            if ($item['brand']) {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b>' . $item['brand'] . '</b></span>';
                                            } else {
                                                echo '<span class="barcode_name" style="display: inline;font-size: 11px; line-height: 1;"><b> &nbsp; </b></span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name" style="font-size: 12px;line-height: 1.5;"><b>' . $item['name'] . '</b></span>';
                                            }

                                            echo '<table width="100%">';
                                            echo '<tr>';
                                            echo '<td   style="text-align: left;padding-left: 10px;width:40%" >';
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price" style="font-size:11px; font-weight: normal;"> MRP: ';

                                                echo round($item['mrp'], 1);

                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '<td rowspan="2" style="vertical-align: middle;">';

                                            echo '<span class="barcode_image" style="text-align:center;">' . $item['barcode'] . '</span>';

                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td style="text-align: left;padding-left: 10px;">';
                                            if ($item['price']) {
                                                echo '<span class="barcode_price" style="font-size:12px">' . lang('Price') . ': ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo round($item['price'], 1);
                                                }
                                                echo '</span> ';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<td colspan="3" style="text-align: left;padding-left: 10px;">';
                                            if ($item['expdate']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Exp: ";
                                                echo $item['expdate'] . "</span>";
                                            }
                                            if ($item['Date']) {
                                                echo " <span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Mfg: ";
                                                echo $item['Date'] . "</span>";
                                            }
                                            if ($item['batchno']) {
                                                echo "<span style='font-size: 10px;text-transform: capitalize;font-weight: bold;'> Batch No." . $item['batchno'] . '</span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            echo '</table>';


                                            echo '</div>';

                                            if ($c % $style == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }

                                            $c++;
                                        }
                                    }
                                    echo '</div>';

                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                } // End NovaJet
                                //A4 Dynamic

                                else if ($style == 'A4_Dynamic') {
                                    $c = 1;
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    echo '<div class="barcodea4">';
                                    foreach ($barcodes as $item) {

                                        for ($r = 1; $r <= $item['quantity']; $r++) {
                                            echo '<div class="item style' . $style . '">';
                                            //                                                if ($style == 50 ||$style == 100) {
                                            //                                                    if ($this->input->post('cf_orientation')) {
                                            //                                                        $ty = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                            //                                                        $landscape = '
                                            //                                                            -webkit-transform-origin: 0 0;
                                            //                                                            -moz-transform-origin:    0 0;
                                            //                                                            -ms-transform-origin:     0 0;
                                            //                                                            transform-origin:         0 0;
                                            //                                                            -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                            //                                                            -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                            //                                                            -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                            //                                                            transform:         translateY(' . $ty . ') rotate(-90deg);
                                            //                                                            ';
                                            //                                                        echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                            //                                                    } else {
                                            //                                                        echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                            //                                                    }
                                            //                                                }

                                            if ($item['site']) {
                                                echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name">' . $item['name'] . '</span>';
                                            }

                                            if ($item['brand']) {
                                                echo '<span class="barcode_name">' . $item['brand'] . '</span>';
                                            }

                                            if ($item['price']) {
                                                echo '<span class="barcode_price">' . lang('price') . ' ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo $item['price'];
                                                }


                                                echo '</span> ';
                                            }
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price">' . lang('MRP') . ' ';

                                                echo $item['mrp'] . ' ';

                                                echo '</span> ';
                                            }
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                            $has_product_image = ($item['image'] && file_exists($imgsrc));
                                            if ($item['barcode_img'] || $has_product_image) {
                                                $media_class = ($item['barcode_img'] && $has_product_image) ? 'media-both' : ($has_product_image ? 'media-image-only' : 'media-barcode-only');
                                                echo '<div class="barcode-media-wrap ' . $media_class . '">';
                                                if ($has_product_image) {
                                                    echo '<span class="product_image" style=""><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                                }
                                                if ($item['barcode_img']) {
                                                    echo '<span class="barcode_image" >' . $item['barcode'] . '</span>';
                                                }
                                                echo '</div>';
                                            }

                                            if ($item['Date'] || $item['netqty'] || $item['weight'] || $item['allexpdate'] || $item['allexpdate']) {
                                                echo "<div style='text-transform: initial;'>";
                                                if ($item['Date']) {
                                                    echo "<span> Mfg : </span>";
                                                    echo "<span>" . $item['Date'] . "</span>";
                                                }
                                                //echo "Qty: 1N ";
                                                if ($item['allexpdate']) {
                                                    echo "<span> Exp : </span>";
                                                    echo "<span>" . $item['allexpdate'] . "</span>";
                                                }
                                                if ($item['netqty']) {
                                                    echo " | Net.Qty.&nbsp;" . $item['netqty'] . 'N';
                                                }
                                                if ($item['weight']) {
                                                    echo " | Net.Wt.&nbsp;" . $item['weight'];
                                                }
                                                if ($item['allbatchno']) {
                                                    echo "<br/><span> Batch  : </span>";
                                                    echo "<span>" . $item['allbatchno'] . "</span>";
                                                }
                                                echo "</div>";
                                            }

                                            if ($item['Address']) {
                                                echo "<small style='font-size: 9px;   '>";

                                                echo "<strong  style='text-transform: initial;'> MFG & PKD. By : </strong> ";
                                                echo ($biller->company) ? "<strong>" . $biller->company . "</strong>" : "<strong>" . $biller->name . "</strong>";
                                                echo "<div style='text-transform: capitalize;  padding: 0px 5px;'>" . $biller->cf2 . "</div>";

                                                echo "</small>";
                                            }

                                            echo '</div>';
                                            if ($c % $pages['sticker_per_page'] == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                            $c++;
                                        }
                                    }
                                    echo '</div>';
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                }

                                // End Dynamic
                                else {
                                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    $c = 1;

                                    if ($style == 12 || $style == 18 || $style == 24 || $style == 40 || $style == '40N' || $style == 48 || $style == 65 || $style == '21N' || $style == '18N' || $style == '24NN') {
                                        echo '<div class="barcodea4">';
                                    } elseif ($style != 50 && $style != 100) {
                                        echo '<div class="barcode">';
                                    }

                                    foreach ($barcodes as $item) {
                                        for ($r = 1; $r <= $item['quantity']; $r++) {

                                            // // Open row for style 100 every 2 items
                                            if ($style == 100) {
                                                if ($c % 2 == 1) {
                                                    echo '<div class="barcode_row_100" style="display: flex; justify-content: space-between; margin-bottom: 5px;">';
                                                }
                                            }

                                            echo '<div class="item style' . $style . '" ' .
                                                (($style == 50 || $style == 100) && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                                    'style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border:0;"' : '') .
                                                '>';

                                            if ($style == 50 || $style == 100) {
                                                if ($this->input->post('cf_orientation')) {
                                                    $ty = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                                    $landscape = '
                                                        -webkit-transform-origin: 0 0;
                                                        -moz-transform-origin:    0 0;
                                                        -ms-transform-origin:     0 0;
                                                        transform-origin:         0 0;
                                                        -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                        -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                        -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                        transform:         translateY(' . $ty . ') rotate(-90deg);
                                                        ';
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                                } else {
                                                    echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                                }
                                            }

                                            if ($item['site']) {
                                                echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span class="barcode_name">' . $item['name'] . '</span>';
                                            }
                                            if ($item['brand']) {
                                                echo '<span class="barcode_name">' . $item['brand'] . '</span>';
                                            }
                                            if ($item['price']) {
                                                echo '<span class="barcode_price">' . lang('price') . ' ';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo $currency->code . ': ' . $this->sma->formatMoney($item['price'] * $currency->rate) . ', ';
                                                    }
                                                } else {
                                                    echo $item['price'];
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['mrp']) {
                                                echo '<span class="barcode_price">' . lang('MRP') . ' ' . $item['mrp'] . '</span> ';
                                            }
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['color']) {
                                                echo '<span class="color">' . lang('Color') . ': ' . $item['color'] . '</span><br> ';
                                            }
                                            // Category display removed to prevent duplication - handled by manage_barcode

                                            $imgsrc = 'assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image'];
                                            $has_product_image = ($item['image'] && file_exists($imgsrc));
                                            if ($item['barcode_img'] || $has_product_image) {
                                                $media_class = ($item['barcode_img'] && $has_product_image) ? 'media-both' : ($has_product_image ? 'media-image-only' : 'media-barcode-only');
                                                echo '<div class="barcode-media-wrap ' . $media_class . '">';
                                                if ($has_product_image) {
                                                    echo '<span class="product_image"><img src="' . base_url('assets/mdata/' . $Customer_assets . '/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                                }
                                                if ($item['barcode_img']) {
                                                    echo '<span class="barcode_image">' . $item['barcode'] . '</span>';
                                                }
                                                echo '</div>';
                                            }

                                            if ($item['Date'] || $item['netqty'] || $item['weight'] || $item['allexpdate'] || $item['allbatchno']) {
                                                echo "<div style='text-transform: initial;'>";
                                                if ($item['Date']) {
                                                    echo "<span> Mfg : </span><span>" . $item['Date'] . "</span>";
                                                }
                                                if ($item['allexpdate']) {
                                                    echo "<span> Exp : </span><span>" . $item['allexpdate'] . "</span>";
                                                }
                                                if ($item['netqty']) {
                                                    echo " | Net.Qty.&nbsp;" . $item['netqty'] . 'N';
                                                }
                                                if ($item['weight']) {
                                                    echo " | Net.Wt.&nbsp;" . $item['weight'];
                                                }
                                                if ($item['allbatchno']) {
                                                    echo "<br/><span> Batch  : </span><span>" . $item['allbatchno'] . "</span>";
                                                }
                                                echo "</div>";
                                            }

                                            if ($item['Address']) {
                                                echo "<small style='font-size: 9px;'>";
                                                echo "<strong style='text-transform: initial;'> MFG & PKD. By : </strong>";
                                                echo ($biller->company) ? "<strong>" . $biller->company . "</strong>" : "<strong>" . $biller->name . "</strong>";
                                                echo "<div style='text-transform: capitalize; padding: 0px 5px;'>" . $biller->cf2 . "</div>";
                                                echo "</small>";
                                            }

                                            if ($style == 50 || $style == 100) {
                                                echo '</div>';
                                            }

                                            echo '</div>'; // end .item

                                            // Close row for style 100 after 2 items
                                            if ($style == 100) {
                                                if ($c % 2 == 0) {
                                                    echo '</div>';
                                                }
                                            }

                                            // Style-specific line breaks
                                            if ($style == 40 && $c % 40 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == '40N' && $c % 40 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == 48 && $c % 48 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == 65 && $c % 65 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == 30 && $c % 30 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            } elseif (($style == 24 || $style == '24NN') && $c % 24 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == 20 && $c % 20 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            } elseif ($style == 18 && $c % 18 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == 14 && $c % 14 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            } elseif ($style == 12 && $c % 12 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            } elseif ($style == 10 && $c % 10 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }

                                            $c++;
                                        }
                                    }
                                    if ($style != 50 && $style != 100) {
                                        echo '</div>';
                                        echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                    }
                                }
                            } else {
                                echo '<h3>' . lang('no_product_selected') . '</h3>';
                            }
                        }
                    }
                    ?>
                </div>
                <?php
                if ($style == 100 || $style == 50) {
                    echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" ><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ac = false;
    bcitems = {};
    if (localStorage.getItem('bcitems')) {
        bcitems = JSON.parse(localStorage.getItem('bcitems'));
    }
    <?php if ($items) { ?>
        localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
    <?php } ?>
    $(document).ready(function() {
        <?php if ($this->input->post('print')) { ?>
            $(window).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top) - 15
                }, 1000);
            });
        <?php } ?>
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#add_item").autocomplete({
            source: '<?= site_url('products/get_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
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
                    $(this).removeClass('ui-autocomplete-loading');
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
                if (ui.item.variants) {
                    $.each(ui.item.variants, function(key, variant) {
                        var row = add_product_item(variant);
                        if (row) {
                            $(this).val('');
                        }
                    });
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
        check_add_item_val();

        $('#style').change(function(e) {
            localStorage.setItem('bcstyle', $(this).val());
            if ($(this).val() == 50 || $(this).val() == 100) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        });
        if (style = localStorage.getItem('bcstyle')) {
            $('#style').val(style);
            $('#style').select2("val", style);
            if (style == 50 || style == 100) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        }

        $('#cf_width').change(function(e) {
            localStorage.setItem('cf_width', $(this).val());
        });
        if (cf_width = localStorage.getItem('cf_width')) {
            $('#cf_width').val(cf_width);
        }

        $('#cf_height').change(function(e) {
            localStorage.setItem('cf_height', $(this).val());
        });
        if (cf_height = localStorage.getItem('cf_height')) {
            $('#cf_height').val(cf_height);
        }

        $('#cf_orientation').change(function(e) {
            localStorage.setItem('cf_orientation', $(this).val());
        });
        if (cf_orientation = localStorage.getItem('cf_orientation')) {
            $('#cf_orientation').val(cf_orientation);
        }

        $(document).on('ifChecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 1);
        });
        $(document).on('ifUnchecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 0);
        });
        if (site_name = localStorage.getItem('bcsite_name')) {
            if (site_name == 1)
                $('#site_name').iCheck('check');
            else
                $('#site_name').iCheck('uncheck');
        }



        // Address
        $(document).on('ifChecked', '#address', function(event) {
            localStorage.setItem('bcaddress', 1);
        });
        $(document).on('ifUnchecked', '#address', function(event) {
            localStorage.setItem('bcaddress', 0);
        });

        $(document).on('change', '#poquantity', function(event) {
            localStorage.setItem('bcquantity', $('#poquantity').val());
        });

        $(document).on('change', '#podate', function(event) {
            localStorage.setItem('bcdate', $('#podate').val());
        });

        if (category = localStorage.getItem('bcaddress')) {
            if (category == 1)
                $('#address').iCheck('check');
            else
                $('#address').iCheck('uncheck');
        }

        // End Address


        $(document).on('ifChecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 1);
        });
        $(document).on('ifUnchecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 0);
        });
        if (product_name = localStorage.getItem('bcproduct_name')) {
            if (product_name == 1)
                $('#product_name').iCheck('check');
            else
                $('#product_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#price', function(event) {
            localStorage.setItem('bcprice', 1);
        });
        $(document).on('ifUnchecked', '#price', function(event) {
            localStorage.setItem('bcprice', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcprice')) {
            if (price == 1)
                $('#price').iCheck('check');
            else
                $('#price').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#mrp', function(event) {
            localStorage.setItem('bcmrp', 1);
        });
        $(document).on('ifUnchecked', '#mrp', function(event) {
            localStorage.setItem('bcmrp', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcmrp')) {
            if (price == 1)
                $('#mrp').iCheck('check');
            else
                $('#mrp').iCheck('uncheck');
        }

        if (price = localStorage.getItem('bcBrand')) {
            if (price == 1)
                $('#Brand').iCheck('check');
            else
                $('#Brand').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#allexpdate', function(event) {
            localStorage.setItem('bcexpirydate', 1);
        });
        $(document).on('ifUnchecked', '#allexpdate', function(event) {
            localStorage.setItem('bcexpirydate', 0);

        });
        if (expirtydate = localStorage.getItem('bcexpirydate')) {
            if (expirtydate == 1) {
                $('#allexpdate').iCheck('check');
            } else {
                $('#allexpdate').iCheck('uncheck');
            }
        }



        $(document).on('ifChecked', '#allbatchno', function(event) {
            localStorage.setItem('bcbatchno', 1);
        });
        $(document).on('ifUnchecked', '#allbatchno', function(event) {
            localStorage.setItem('bcbatchno', 0);
        });
        if (batchno = localStorage.getItem('bcbatchno')) {
            if (batchno == 1) {
                $('#allbatchno').iCheck('check');
            } else {
                $('#allbatchno').iCheck('uncheck');
            }
        }




        $(document).on('ifChecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 1);
        });
        $(document).on('ifUnchecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 0);
        });
        if (currencies = localStorage.getItem('bccurrencies')) {
            if (currencies == 1)
                $('#currencies').iCheck('check');
            else
                $('#currencies').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 1);
        });
        $(document).on('ifUnchecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 0);
        });
        if (unit = localStorage.getItem('bcunit')) {
            if (unit == 1)
                $('#unit').iCheck('check');
            else
                $('#unit').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#category', function(event) {
            localStorage.setItem('bccategory', 1);
        });
        $(document).on('ifUnchecked', '#category', function(event) {
            localStorage.setItem('bccategory', 0);
        });
        if (category = localStorage.getItem('bccategory')) {
            if (category == 1)
                $('#category').iCheck('check');
            else
                $('#category').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 1);
        });
        $(document).on('ifUnchecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 0);
        });
        if (product_image = localStorage.getItem('bcproduct_image')) {
            if (product_image == 1)
                $('#product_image').iCheck('check');
            else
                $('#product_image').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 1);
        });
        $(document).on('ifUnchecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 0);
        });

        $(document).on('ifChecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 1);
        });
        $(document).on('ifUnchecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 0);
        });
        if (product_image = localStorage.getItem('bccheck_promo')) {
            if (product_image == 1)
                $('#check_promo').iCheck('check');
            else
                $('#check_promo').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#Brand', function(event) {
            localStorage.setItem('bcBrand', 1);
        });
        $(document).on('ifUnchecked', '#Brand', function(event) {
            localStorage.setItem('bcBrand', 0);
        });
        if (variants = localStorage.getItem('bcvariants')) {
            if (variants == 1)
                $('#variants').iCheck('check');
            else
                $('#variants').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#color', function (event) {
            localStorage.setItem('bccolor', 1);
        });
        $(document).on('ifUnchecked', '#color', function (event) {
            localStorage.setItem('bccolor', 0);
        });
        if (color = localStorage.getItem('bccolor')) {
            if (color == 1)
                $('#color').iCheck('check');
            else
                $('#color').iCheck('uncheck');
        }

        $(document).on('ifChecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 1;
            bcitems[item_id]['selected_variants_color'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('ifUnchecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 0;
            bcitems[item_id]['selected_variants_color'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

        $(document).on('click', '.del', function() {
            var id = $(this).closest('tr').attr('data-item-id');
            Object.keys(bcitems).forEach(function(key) {
                if (bcitems[key].id === id) {
                    delete bcitems[key];
                }
            });
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            console.log("bcitems", $(this).closest(`tr`));
            $(this).closest(`tr`).remove();

        });

        $('#reset').click(function(e) {

            bootbox.confirm(lang.r_u_sure, function(result) {
                if (result) {
                    if (localStorage.getItem('bcitems')) {
                        localStorage.removeItem('bcitems');
                    }
                    if (localStorage.getItem('bcstyle')) {
                        localStorage.removeItem('bcstyle');
                    }
                    if (localStorage.getItem('bcsite_name')) {
                        localStorage.removeItem('bcsite_name');
                    }
                    if (localStorage.getItem('bcproduct_name')) {
                        localStorage.removeItem('bcproduct_name');
                    }
                    if (localStorage.getItem('bcprice')) {
                        localStorage.removeItem('bcprice');
                    }
                    if (localStorage.getItem('bccurrencies')) {
                        localStorage.removeItem('bccurrencies');
                    }
                    if (localStorage.getItem('bcunit')) {
                        localStorage.removeItem('bcunit');
                    }
                    if (localStorage.getItem('bccategory')) {
                        localStorage.removeItem('bccategory');
                    }

                    if (localStorage.getItem('bcquantity')) {
                        localStorage.removeItem('bcquantity');
                    }
                    if (localStorage.getItem('bcdate')) {
                        localStorage.removeItem('bcdate');
                    }

                    if (localStorage.getItem('bcbatchno')) {
                        localStorage.removeItem('bcbatchno');
                    }

                    if (localStorage.getItem('bcexpirydate')) {
                        localStorage.removeItem('bcexpirydate');
                    }

                    // if (localStorage.getItem('cf_width')) {
                    //     localStorage.removeItem('cf_width');
                    // }
                    // if (localStorage.getItem('cf_height')) {
                    //     localStorage.removeItem('cf_height');
                    // }
                    // if (localStorage.getItem('cf_orientation')) {
                    //     localStorage.removeItem('cf_orientation');
                    // }

                    $('#modal-loading').show();
                    window.location.replace("<?= site_url('products/print_barcodes'); ?>");
                }
            });
        });

        var old_row_qty;
        $(document).on("focus", '.quantity', function() {
            old_row_qty = $(this).val();
        }).on("change", '.', function() {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            bcitems[item_id].qty = new_qty;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });


        /* $('.expdate').change(function (){
              var row = $(this).closest('tr');
                var expdate = $(this).val();
               item_id = row.attr('data-item-id');
            
             bcitems[item_id].expdate = expdate;
             localStorage.setItem('bcitems', JSON.stringify(bcitems));
         });*/

        /* $('.batchno').change(function(){
             var row = $(this).closest('tr');
             var batchno = $(this).val();
             item_id = row.attr('data-item-id');
            
             bcitems[item_id].batchno = batchno;
             localStorage.setItem('bcitems', JSON.stringify(bcitems));
         });*/

    });

    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }
        item_id = item.id;
        if (bcitems[item_id]) {
            // Existing item: just bump qty
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        } else {
            // New item from autocomplete
            bcitems[item_id] = item;

            // If batch-wise data is available, use first batch as default
            var initialQty = null;
            var initialBatchNo = null;
            var initialExpiry = null;

            if (bcitems[item_id].batches && bcitems[item_id].batches.length > 0) {
                var firstBatch = bcitems[item_id].batches[0];
                if (firstBatch) {
                    // Support both purchase_items-style and product_batches-style fields
                    initialBatchNo = firstBatch.batch_number || firstBatch.batch_no || '';
                    initialExpiry  = firstBatch.expiry || '';

                    if (typeof firstBatch.quantity !== 'undefined' && firstBatch.quantity !== null) {
                        initialQty = parseFloat(firstBatch.quantity);
                    } else if (typeof firstBatch.qty !== 'undefined' && firstBatch.qty !== null) {
                        initialQty = parseFloat(firstBatch.qty);
                    }
                }
            }

            if (initialQty !== null && !isNaN(initialQty)) {
                bcitems[item_id].qty = initialQty;
                bcitems[item_id].quantity = initialQty;
            } else {
                // Fallback: use existing qty from suggestion or 0
                if (typeof bcitems[item_id].qty !== 'undefined' && bcitems[item_id].qty !== null) {
                    bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) || 0;
                } else {
                    bcitems[item_id].qty = 0;
                }
                if (typeof bcitems[item_id].quantity !== 'undefined') {
                    bcitems[item_id].quantity = bcitems[item_id].qty;
                }
            }

            if (initialBatchNo) {
                bcitems[item_id].batchno = initialBatchNo;
            }
            if (initialExpiry) {
                bcitems[item_id].expdate = initialExpiry;
            }

            // bcitems[item_id]['selected_variants'] = {};
            // $.each(item.variants, function () {
            //     bcitems[item_id]['selected_variants'][this.id] = 1;
            // });
        }

        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;

    }

    function loadItems() {

        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));

            $.each(bcitems, function() {
                var item = this;
                var row_no;
                if (item.variant_name) {
                    var row_no = parseInt(item.product_id);
                } else {
                    row_no = parseInt(item.id);
                }

                var variant_name = '-';
                var color_name = '-';
                if (item.variants && item.variants.length > 0) {
                    $.each(item.variants, function(index, variant) {
                        variant_name = variant.name;
                        color_name = variant.color ? variant.color : '-';
                    });
                } else {
                    variant_name = item.variant_name ? item.variant_name : '-';
                    color_name = item.color ? item.color : '-';
                }

                // Determine initial quantity and expiry to display, possibly from pre-selected batch
                var displayQty = item.qty ? item.qty : item.quantity;
                var displayExp = item.expdate ? item.expdate : '';
<?php if ($Settings->product_batch_setting > 0) : ?>
                if (item.batchno && item.batches && item.batches.length > 0) {
                    $.each(item.batches, function(i, b) {
                        var bno = b.batch_number ? b.batch_number : '';
                        if (bno && bno == item.batchno) {
                            var bqty = (typeof b.quantity !== 'undefined' && b.quantity !== null) ? b.quantity : '';
                            var bexp = b.expiry ? b.expiry : '';
                            if (bqty !== '') {
                                displayQty = bqty;
                                item.qty = parseFloat(bqty);
                            }
                            if (bexp) {
                                displayExp = bexp;
                                item.expdate = bexp;
                            }
                        }
                    });
                }
<?php endif; ?>
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id +
                    '"></tr>');
                tr_html = '<td><input name="product[]" type="hidden" value="' + row_no + '"><span id="name_' +
                    row_no + '">' + item.name + ' (' + item.code + ')</span></td>';
                tr_html += '<td><label for="' + this.id + '" class="padding05" name="vt_' + item.id + '_' + this
                    .id + '">' + variant_name + '</label></td>';
                tr_html += '<td><label for="' + this.id + '" class="padding05" name="color[]' + item.id + '_' + this
                    .id + '">' + color_name + '</label></td>';
                tr_html +=
                    '<td><input class="form-control quantity text-center" name="quantity[]" type="text" value="' +
                    formatDecimal(displayQty) + '" data-id="' + row_no + '" data-item="' +
                    item.id + '" id="quantity_' + item.id + '" onClick="this.select();" onchange="qtyset(\'' + item
                    .id + '\',\'' + item.id + '\')"></td>';
                tr_html += '<td><input type="text" name="expdate[]" value="' + ((displayExp) ? displayExp :
                        '') + '" id="expdata_' + this.id +
                    '" class="form-control input-tip date expdate" onchange="expdate(\'' + this.id + '\',\'' + item
                    .id + '\')" style="width: 150px;"></td>';

<?php if ($Settings->product_batch_setting > 0) : ?>
                // Batch column: dropdown if multiple batches exist, otherwise textbox
                if (item.batches && item.batches.length > 1) {
                    tr_html += '<td><select name="batchno[]" id="batchno_' + this.id +
                        '" class="form-control batchno" onchange="batchSelectChange(\'' + this.id + '\',\'' + item.id + '\')" style="width: 180px;">';
                    $.each(item.batches, function (bi, b) {
                        var bno = b.batch_number || b.batch_no || '';
                        var bexp = b.expiry || '';
                        var bqty = (typeof b.quantity !== 'undefined' && b.quantity !== null) ? b.quantity : '';
                        var selected = (item.batchno && item.batchno == bno) ? ' selected="selected"' : '';

                        tr_html += '<option value="' + bno + '" data-expiry="' + bexp + '" data-qty="' + bqty + '"' + selected + '>' + bno + '</option>';
                    });

                    tr_html += '</select></td>';
                } else {
                    tr_html += '<td><input type="text" name="batchno[]" value="' + ((item.batchno) ? item.batchno :
                            '') + '" id="batchno_' + this.id +
                        '" class="form-control batchno" onchange="batchnoset(\'' +
                        this.id + '\',\'' + item.id + '\')" style="width: 180px;"></td>';
                }
<?php endif; ?>
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no +
                    '" title="Remove" style="cursor:pointer; width:40px;"></i></td>';
                tr_html += '<td><input type="hidden" name="option_name[]" value="' + variant_name + '"></td>';

                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            return true;
        }
    }

    function expdate(id, itemId) {
        var passid = 'expdata_' + id;
        var expdate = $('#' + passid).val();
        item_id = itemId;
        bcitems[item_id].expdate = expdate;
        localStorage.setItem('bcitems', JSON.stringify(bcitems));
    }

    function batchnoset(id, itemId) {
        var passid = 'batchno_' + id;
        var batchno = $('#' + passid).val();
        item_id = itemId;
        bcitems[item_id].batchno = batchno;
        localStorage.setItem('bcitems', JSON.stringify(bcitems));
    }

    function batchSelectChange(id, itemId) {
        var selectId = 'batchno_' + id;
        var $sel = $('#' + selectId);
        var batchno = $sel.val();
        var expiry = $sel.find('option:selected').data('expiry') || '';
        var bqty = $sel.find('option:selected').data('qty');

        item_id = itemId;
        if (!bcitems[item_id]) {
            return;
        }

        bcitems[item_id].batchno = batchno;
        bcitems[item_id].expdate = expiry;
        if (typeof bqty !== 'undefined' && bqty !== null && bqty !== '') {
            bcitems[item_id].qty = parseFloat(bqty);
        }
        localStorage.setItem('bcitems', JSON.stringify(bcitems));

        // keep expiry input in sync with selected batch
        var expInputId = '#expdata_' + id;
        $(expInputId).val(expiry);

        // keep quantity input in sync with selected batch quantity, if provided
        if (typeof bqty !== 'undefined' && bqty !== null && bqty !== '') {
            var qtyInputId = '#quantity_' + itemId;
            var qtyNum = parseFloat(bqty);
            if (!isNaN(qtyNum) && Math.floor(qtyNum) === qtyNum) {
                $(qtyInputId).val(parseInt(qtyNum, 10));   // 16
            } else {
                $(qtyInputId).val(qtyNum);                 // 16.5 etc.
            }
        }
    }

    function qtyset(id, itemId) {
        var passid = 'quantity_' + id;
        var new_qty = parseFloat($('#' + passid).val());
        item_id = itemId;
        bcitems[item_id].qty = new_qty;
        localStorage.setItem('bcitems', JSON.stringify(bcitems));
    }
</script>