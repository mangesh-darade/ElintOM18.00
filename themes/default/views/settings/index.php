<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$wm = array('0' => lang('no'), '1' => lang('yes'));
$ps = array('0' => lang("disable"), '1' => lang("enable")); 
?>
<script>
    $(document).ready(function () {
        
<?php if (isset($message)) {
    echo 'localStorage.clear();';
} ?>
        var timezones = '<?php echo json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL)); ?>';
        $('#timezone').autocomplete({
            source: timezones
        });
        if ($('#protocol').val() == 'smtp') {
            $('#smtp_config').slideDown();
        } else if ($('#protocol').val() == 'sendmail') {
            $('#sendmail_config').slideDown();
        }
        $('#protocol').change(function () {
            if ($(this).val() == 'smtp') {
                $('#sendmail_config').slideUp();
                $('#smtp_config').slideDown();
            } else if ($(this).val() == 'sendmail') {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideDown();
            } else {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideUp();
            }
        });
        $('#overselling').change(function () {
            if ($(this).val() == 1) {
                if ($('#accounting_method').select2("val") != 2) {
                    bootbox.alert('<?= lang('overselling_will_only_work_with_AVCO_accounting_method_only') ?>');
                    $('#accounting_method').select2("val", '2');
                }
            }
        });
        $('#accounting_method').change(function () {
            var oam = <?= $Settings->accounting_method ?>, nam = $(this).val();
            if (oam != nam) {
                bootbox.alert('<?= lang('accounting_method_change_alert') ?>');
            }
        });
        $('#accounting_method').change(function () {
            if ($(this).val() != 2) {
                if ($('#overselling').select2("val") == 1) {
                    bootbox.alert('<?= lang('overselling_will_only_work_with_AVCO_accounting_method_only') ?>');
                    $('#overselling').select2("val", 0);
                }
            }
        });
        $('#item_addition').change(function () {
            if ($(this).val() == 1) {
                bootbox.alert('<?= lang('product_variants_feature_x') ?>');
            }
        });
        var sac = $('#sac').val()
        if (sac == 1) {
            $('.nsac').slideUp();
        } else {
            $('.nsac').slideDown();
        }
        $('#sac').change(function () {
            if ($(this).val() == 1) {
                $('.nsac').slideUp();
            } else {
                $('.nsac').slideDown();
            }
        });

        $('#generate_api_privatekey').click(function () {

            $('#apiprivatekey').html('<span class="text-danger">wait, key generating...</span>');

            $.ajax({
                type: "get",
                async: false,
                url: '<?php echo base_url('system_settings/generate_privatekey') ?>',
                dataType: "json",
                success: function (data) {

                    setTimeout(function () {
                        $('#apiprivatekey').html(data.api_privatekey);
                    }, 5000);

                }
            });

        });
    });

</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('system_settings'); ?></h2>
        <div class="box-icon" style="display:none;">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= site_url('system_settings/paypal') ?>" class="toggle_up"><i
                            class="icon fa fa-paypal"></i><span
                            class="padding-right-10"><?= lang('paypal'); ?></span></a></li>
                <li class="dropdown"><a href="<?= site_url('system_settings/skrill') ?>" class="toggle_down"><i
                            class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('skrill'); ?></span></a>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('update_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-info" role="alert"><p>
                                <strong>Cron Job:</strong> <code>0 1 * * * wget -qO- <?= site_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</code> to run at 1:00 AM daily. For local installation, you can run cron job manually at any time.
<?php if (!DEMO) { ?>
                                    <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= site_url('cron/run'); ?>">Run cron job now</a>
<?php } ?>
                            </p></div>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('site_config') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
<?= lang("site_name", "site_name"); ?>
<?= form_input('site_name', $Settings->site_name, 'class="form-control tip" id="site_name"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("language", "language"); ?>
                                    <?php
                                    $lang = array(
                                        'english' => 'English',
//                                        'arabic'                    => 'Arabic',                                        
//                                        'german'                    => 'German',
//                                        'portuguese-brazilian'      => 'Portuguese (Brazil)',
//                                        'simplified-chinese'        => 'Simplified Chinese',
//                                        'spanish'                   => 'Spanish',
//                                        'thai'                      => 'Thai',
//                                        'traditional-chinese'       => 'Traditional Chinese',
//                                        'turkish'                   => 'Turkish',																				'vietnamese'                => 'Vietnamese',
//                                        'gujarati'                  => 'Gujarati',																				'hindi'                     => 'Hindi',										'marthi'                    => 'Marathi'
                                    );
                                    echo form_dropdown('language', $lang, $Settings->language, 'class="form-control tip" id="language" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="currency"><?= lang("default_currency"); ?></label>

                                    <div class="controls"> <?php
                                        foreach ($currencies as $currency) {
                                            $cu[$currency->code] = $currency->name;
                                        }
                                        echo form_dropdown('currency', $cu, $Settings->default_currency, 'class="form-control tip noedit" id="currency" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("accounting_method", "accounting_method"); ?>
                                    <?php
                                    $am = array(
                                        0 => 'FIFO (First In First Out)',
                                        1 => 'LIFO (Last In First Out)',
                                        2 => 'AVCO (Average Cost Method)',
                                    );
                                    
                                    echo form_dropdown('accounting_method', $am, $Settings->accounting_method, 'class="form-control tip noedit" id="accounting_method" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="email"><?= lang("default_email"); ?></label>

<?= form_input('email', $Settings->default_email, 'class="form-control tip" required="required" id="email"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="customer_group"><?= lang("default_customer_group"); ?></label>
                                    <?php
                                    foreach ($customer_groups as $customer_group) {
                                        $pgs[$customer_group->id] = $customer_group->name;
                                    }
                                    echo form_dropdown('customer_group', $pgs, $Settings->customer_group, 'class="form-control tip" id="customer_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="price_group"><?= lang("default_price_group"); ?></label>
                                    <?php
                                    foreach ($price_groups as $price_group) {
                                        $cgs[$price_group->id] = $price_group->name;
                                    }
                                    echo form_dropdown('price_group', $cgs, $Settings->price_group, 'class="form-control tip" id="price_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                        <?= lang('maintenance_mode', 'mmode'); ?>
                                    <div class="controls">  <?php
                                        echo form_dropdown('mmode', $wm, (isset($_POST['mmode']) ? $_POST['mmode'] : $Settings->mmode), 'class="tip form-control noedit" required="required" id="mmode" style="width:100%;"');
                                        ?> </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="theme"><?= 'POS ' . lang("theme"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $themes[""];
                                        foreach ($post_theme as $pt) {
                                            $pt->theme_name;
                                            $themes[$pt->theme_name] = ucfirst($pt->theme_label);
                                        }
                                        /* $themes = array(
                                          'default' => 'Default',

                                          ); */
                                        echo form_dropdown('theme', $themes, $Settings->theme, 'id="theme" class="form-control tip noedit" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="rtl"><?= lang("rtl_support"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('rtl', $ps, $Settings->rtl, 'id="rtl" class="form-control tip noedit" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="captcha"><?= lang("login_captcha"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('captcha', $ps, $Settings->captcha, 'id="captcha" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="disable_editing"><?= lang("disable_editing"); ?></label>
<?= form_input('disable_editing', $Settings->disable_editing, 'class="form-control tip" id="disable_editing" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="rows_per_page"><?= lang("rows_per_page"); ?></label>
<?= form_input('rows_per_page', $Settings->rows_per_page, 'class="form-control tip" id="rows_per_page" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="dateformat"><?= lang("dateformat"); ?></label>

                                    <div class="controls">
                                        <?php
                                        foreach ($date_formats as $date_format) {
                                            $dt[$date_format->id] = $date_format->js;
                                        }
                                        echo form_dropdown('dateformat', $dt, $Settings->dateformat, 'id="dateformat" class="form-control tip noedit" style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="country"><?= lang("country"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $dt = [];
                                        foreach ($countrys as $country) {
                                            $dt[$country->name] = $country->name; // or "$country->name . ' (' . $country->code . ')'" if you want both
                                        }
                                        echo form_dropdown('country', $dt, $Settings->country_name, 'id="country" class="form-control tip noedit" style="width:100%;" required="required"');?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="timezone"><?= lang("timezone"); ?></label>
                                    <?php
                                    $timezone_identifiers = DateTimeZone::listIdentifiers();
                                    foreach ($timezone_identifiers as $tzi) {
                                        $tz[$tzi] = $tzi;
                                    }
                                    ?>
<?= form_dropdown('timezone', $tz, TIMEZONE, 'class="form-control tip noedit" id="timezone" required="required"'); ?>
                                </div>
                            </div>
                            <!--<div class="col-md-4">
                                <div class="form-group">
                            <?= lang('reg_ver', 'reg_ver'); ?>
                                    <div class="controls">  <?php
                            echo form_dropdown('reg_ver', $wm, (isset($_POST['reg_ver']) ? $_POST['reg_ver'] : $Settings->reg_ver), 'class="tip form-control" required="required" id="reg_ver" style="width:100%;"');
                            ?> </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                            <?= lang('allow_reg', 'allow_reg'); ?>
                                    <div class="controls">  <?php
                            echo form_dropdown('allow_reg', $wm, (isset($_POST['allow_reg']) ? $_POST['allow_reg'] : $Settings->allow_reg), 'class="tip form-control" required="required" id="allow_reg" style="width:100%;"');
                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                            <?= lang('reg_notification', 'reg_notification'); ?>
                                    <div class="controls">  <?php
                            echo form_dropdown('reg_notification', $wm, (isset($_POST['reg_notification']) ? $_POST['reg_notification'] : $Settings->reg_notification), 'class="tip form-control" required="required" id="reg_notification" style="width:100%;"');
                            ?>
                                    </div>
                                </div>
                            </div>-->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="restrict_calendar">Calendar</label>

                                    <div class="controls">
                                        <?php
                                        $opt_cal = array(1 => lang('private'), 0 => lang('shared'));
                                        echo form_dropdown('restrict_calendar', $opt_cal, $Settings->restrict_calendar, 'class="form-control tip" required="required" id="restrict_calendar" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="warehouse"><?= 'POS ' . lang("default_location"); ?></label>

                                    <div class="controls"> <?php
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                        }
                                        echo form_dropdown('warehouse', $wh, $Settings->default_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" >
                                <div class="form-group">
                                    <?= '<b>POS</b> ' . lang("default_biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="default_printer">Default Bill Print Option </label>
                                    <?php
                                    $pl[""] = "";
                                    foreach ($printers as $printer) {
                                        $pl[$printer->id] = $printer->name;
                                    }
                                    echo form_dropdown('default_printer', $pl, (isset($_POST['default_printer']) ? $_POST['default_printer'] : $Settings->default_printer), 'id="default_printer" data-placeholder="' . lang("select") . ' ' . lang("default_printer") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                        <?= lang("Offline_Location", "Offline_Location"); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />

                                    <div class="controls"> <?php
                                        foreach ($warehouses as $warehouse) {
                                            $offwh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                        }
                                        echo form_dropdown('offlinepos_warehouse', $offwh, (isset($_POST['offlinepos_biller']) ? $_POST['offlinepos_biller'] : $Settings->offlinepos_warehouse), 'class="form-control tip" id="offlinepos_warehouse" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                         
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("Offline Biller", "Offline Biller"); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $offbl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('offlinepos_biller', $offbl, (isset($_POST['offlinepos_biller']) ? $_POST['offlinepos_biller'] : $Settings->offlinepos_biller), 'id="offlinepos_biller" data-placeholder="' . lang("select") . ' ' . lang("offlinepos_biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                                
                            </div>
                            <?php
                                $auto_customer_number_enabled = isset($Settings->auto_customer_number) ? (int) $Settings->auto_customer_number : 0;
                                $auto_customer_number_options = array(0 => lang('no'), 1 => lang('yes'));
                                ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <?= lang('auto_customer_number', 'auto_customer_number'); ?>
                                            <?= form_dropdown('auto_customer_number', $auto_customer_number_options, $auto_customer_number_enabled, 'class="form-control" id="auto_customer_number"'); ?>
                                        </div>
                                    </div>
                                </div> 
                            

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("Barcode Scan Camare", "Barcode Scan Camare"); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                    <?php
                                     $barcodeCamera = array(1 => lang('Enable'), 0 => lang('Disable'));
                                    echo form_dropdown('barcode_scan_camera', $barcodeCamera, (isset($_POST['barcode_scan_camera']) ? $_POST['barcode_scan_camera'] : $Settings->barcode_scan_camera), 'id="barcode_scan_camera" data-placeholder="' . lang("select") . ' ' . lang("barcode_scan_camera") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                                
                            </div> 
                            <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="control-label" for="use_invoice_number_prefix"><?= lang("Use_Invoice_Number_Prefix"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = array(1 => lang('yes'), 0 => lang('no'));
                                        echo form_dropdown('use_invoice_number_prefix', $opt, $Settings->use_invoice_number_prefix, 'class="form-control tip noedit" id="use_invoice_number_prefix" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                
                            </div> 
                            
                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('products') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("product_tax", "tax_rate"); ?>
<?php
echo form_dropdown('tax_rate', $ps, $Settings->default_tax_rate, 'class="form-control tip" id="tax_rate" required="required" style="width:100%;"');
?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="racks"><?= lang("racks"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('racks', $ps, $Settings->racks, 'id="racks" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="attributes"><?= lang("attributes"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('attributes', $ps, $Settings->attributes, 'id="attributes" class="form-control tip noedit"  required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="product_expiry"><?= lang("product_expiry"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('product_expiry', $ps, $Settings->product_expiry, 'id="product_expiry" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="packing_size_column">Packing Size Column</label>

                                    <div class="controls">
<?php
echo form_dropdown('packing_size_column', $ps, (isset($Settings->packing_size_column) ? $Settings->packing_size_column : 1), 'id="packing_size_column" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="remove_expired"><?= lang("remove_expired"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $re_opts = array(0 => lang('no') . ', ' . lang('i_ll_remove'), 1 => lang('yes') . ', ' . lang('remove_automatically'));
                                        echo form_dropdown('remove_expired', $re_opts, $Settings->remove_expired, 'id="remove_expired" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="image_size"><?= lang("image_size"); ?> (Width :
                                        Height) *</label>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?= form_input('iwidth', $Settings->iwidth, 'class="form-control tip" id="iwidth" placeholder="image width" required="required"'); ?>
                                        </div>
                                        <div class="col-xs-6">
<?= form_input('iheight', $Settings->iheight, 'class="form-control tip" id="iheight" placeholder="image height" required="required"'); ?></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="thumbnail_size"><?= lang("thumbnail_size"); ?>
                                        (Width : Height) *</label>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?= form_input('twidth', $Settings->twidth, 'class="form-control tip" id="twidth" placeholder="thumbnail width" required="required"'); ?>
                                        </div>
                                        <div class="col-xs-6">
<?= form_input('theight', $Settings->theight, 'class="form-control tip" id="theight" placeholder="thumbnail height" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('watermark', 'watermark'); ?>
<?php
echo form_dropdown('watermark', $wm, (isset($_POST['watermark']) ? $_POST['watermark'] : $Settings->watermark), 'class="tip form-control" required="required" id="watermark" style="width:100%;"');
?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('display_all_products', 'display_all_products'); ?>
                                    <?php
                                    $dopts = array(0 => lang('hide_with_0_qty'), 1 => lang('show_with_0_qty'));
                                    echo form_dropdown('display_all_products', $dopts, (isset($_POST['display_all_products']) ? $_POST['display_all_products'] : $Settings->display_all_products), 'class="tip form-control" required="required" id="display_all_products" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('barcode_separator', 'barcode_separator'); ?>
                                    <?php
                                    $bcopts = array('-' => lang('-'), '.' => lang('.'), '~' => lang('~'), '_' => lang('_'));
                                    echo form_dropdown('barcode_separator', $bcopts, (isset($_POST['barcode_separator']) ? $_POST['barcode_separator'] : $Settings->barcode_separator), 'class="tip form-control" required="required" id="barcode_separator" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('barcode_renderer', 'barcode_renderer'); ?>
                                    <?php
                                    $bcropts = array(1 => lang('image'), 0 => lang('svg'));
                                    echo form_dropdown('barcode_renderer', $bcropts, (isset($_POST['barcode_renderer']) ? $_POST['barcode_renderer'] : $Settings->barcode_img), 'class="tip form-control" required="required" id="barcode_renderer" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
<?= lang('update_cost_with_purchase', 'update_cost'); ?>
<?= form_dropdown('update_cost', $wm, $Settings->update_cost, 'class="form-control noedit" id="update_cost" required="required"'); ?>
                                </div>
                            </div>

                            <!--<div class="col-md-4">
                                <div class="form-group">
                                     <label class="control-label" for="Show_Total_Unit_Quantity" ><?= lang('Show_Total_Unit_Quantity', 'show_total_unit_quantity'); ?></label>
<?= form_dropdown('show_total_unit_quantity', $wm, $Settings->show_total_unit_quantity, 'class="form-control" id="show_total_unit_quantity" required="required"'); ?>
                                </div>
                            </div>-->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_external_platform"><?= lang("Delivery Platform"); ?><small> (Ex.Zomato,Swiggy,etc.)</small></label>
                                    <div class="controls">
                                        <?php
                                        $product_Platform[0] = 'Hide';
                                        $product_Platform[1] = 'Show';
                                        echo form_dropdown('product_external_platform', $product_Platform, $Settings->product_external_platform, 'class="form-control tip '.(($Settings->pos_type != 'restaurant')?'noedit':' ').'" required="required" id="product_external_platform" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_batch_setting"><?= lang("Batches Setting"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                    <?php
                                        $batch_setting[0] = 'Hide/Disabled Batches';
                                        $batch_setting[1] = 'Select Batch From The List';
                                        $batch_setting[2] = 'Select/Add Batch While Transaction';
//noedit
                                        echo form_dropdown('product_batch_setting', $batch_setting, $Settings->product_batch_setting, 'class="form-control tip " required="required" id="product_batch_setting" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_batch_required"><?= lang("Batch Stocks Required"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <?php
                                        $batch_required[0] = 'Stocks Optional';
                                        $batch_required[1] = 'Stocks Required For Packed Products';
                                        $batch_required[2] = 'Stocks Required For All Products';
                                        echo form_dropdown('product_batch_required', $batch_required, $Settings->product_batch_required, 'class="form-control tip noedit" required="required" id="product_batch_required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                              <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Barcode separator for weight', 'Barcode separator for weight'); ?>
                                    <?= form_input('barcode_separator_weight', $Settings->barcode_separator_weight, 'class="form-control" id="barcode_separator_weight" placeholder="Barcode Separator for weight"  '); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('Product name auto generate, isn\'t existing', 'Product name auto generate, isn\'t existing'); ?>
                                        <?= form_dropdown('product_name_auto_generate', $wm, $Settings->product_name_auto_generate, 'class="form-control" id="update_cost" required="required"'); ?>
                                    </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("Display other category", "othercategoryforproduct"); ?>
                                    <?php
                                    echo form_dropdown('othercategoryforproduct', $ps, $Settings->other_category_for_product, 'class="form-control tip" id="othercategoryforproduct" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <?php
                                $discount_on_mrp_enabled = isset($Settings->discount_on_mrp) ? (int) $Settings->discount_on_mrp : 1;
                                ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="discount_on_mrp">Discount on MRP</label>
                                    <?= form_dropdown('discount_on_mrp', $wm, $discount_on_mrp_enabled, 'class="form-control tip" id="discount_on_mrp" required="required" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?= lang('Business Hour', 'business_hour'); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>                             
                                    <?php
                                    $business_hour_options = array(1 => lang('Enable'), 0 => lang('Disable'));
                                    echo form_dropdown('business_hour', $business_hour_options, $Settings->business_hour, 'class="form-control" id="business_hour"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Active CMS Admin Panel<img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>                             
                                    <?php
                                    $active_cms_admin_panel_options = array(1 => lang('Enable'), 0 => lang('Disable'));
                                    $active_cms_admin_panel_val = isset($Settings->active_cms_admin_panel) ? $Settings->active_cms_admin_panel : 0;
                                    echo form_dropdown('active_cms_admin_panel', $active_cms_admin_panel_options, $active_cms_admin_panel_val, 'class="form-control" id="active_cms_admin_panel"');
                                    ?>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border">Show/Hide Product Images</legend>       
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="sales_image">Sales Product Image </label>
                                    <?php
                                    $addsaleimg[0] = 'Hide';
                                    $addsaleimg[1] = 'Show';
                                    echo form_dropdown('sales_image', $addsaleimg, (isset($_POST['sales_image']) ? $_POST['sales_image'] : $Settings->sales_image), 'id="sales_image" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="purchase_image">Purchase Product Image</label>
                                    <?php
                                    $purchaseimg[0] = 'Hide';
                                    $purchaseimg[1] = 'Show';
                                    echo form_dropdown('purchase_image', $purchaseimg, (isset($_POST['purchase_image']) ? $_POST['purchase_image'] : $Settings->purchase_image), 'id="purchase_image" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="quotation_image">Quotations Product Image</label>
                                    <?php
                                    $quotation[0] = 'Hide';
                                    $quotation[1] = 'Show';
                                    echo form_dropdown('quotation_image', $quotation, (isset($_POST['quotation_image']) ? $_POST['quotation_image'] : $Settings->quotation_image), 'id="quotation_image" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_product_image">Invoice Product Image</label>
                                    <?php
                                    $invoiceimg[0] = 'Hide';
                                    $invoiceimg[1] = 'Show';
                                    echo form_dropdown('invoice_product_image', $invoiceimg, (isset($_POST['invoice_product_image']) ? $_POST['invoice_product_image'] : $Settings->invoice_product_image), 'id="invoice_product_image" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </fieldset>
                       
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('sales') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="overselling"><?= lang("over_selling"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = array(1 => lang('yes'), 0 => lang('no'));
                                        echo form_dropdown('restrict_sale', $opt, $Settings->overselling, 'class="form-control tip noedit" id="overselling" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="reference_format"><?= lang("reference_format"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $ref = array(1 => lang('prefix_year_no'), 2 => lang('prefix_month_year_no'), 3 => lang('sequence_number'), 4 => lang('random_number'));
                                        echo form_dropdown('reference_format', $ref, $Settings->reference_format, 'class="form-control tip noedit" required="required" id="reference_format" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("invoice_tax", "tax_rate2"); ?>
                                    <?php
                                    $tr['0'] = lang("disable");
                                    foreach ($tax_rates as $rate) {
                                        $tr[$rate->id] = $rate->name;
                                    }
                                    echo form_dropdown('tax_rate2', $tr, $Settings->default_tax_rate2, 'id="tax_rate2" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="product_discount"><?= lang("product_level_discount"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('product_discount', $ps, $Settings->product_discount, 'id="product_discount" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="product_serial"><?= lang("product_serial"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('product_serial', $ps, $Settings->product_serial, 'id="product_serial" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="detect_barcode"><?= lang("auto_detect_barcode"); ?></label>

                                    <div class="controls">
<?php
echo form_dropdown('detect_barcode', $ps, $Settings->auto_detect_barcode, 'id="detect_barcode" class="form-control tip" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="bc_fix"><?= lang("bc_fix"); ?></label>


                                    <?= form_input('bc_fix', $Settings->bc_fix, 'class="form-control tip" required="required" id="bc_fix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
<?= lang("set_focus", "set_focus"); ?>
<?php
$sfopts = array(0 => lang('add_item_input'), 1 => lang('last_order_item'));
echo form_dropdown('set_focus', $sfopts, (isset($_POST['set_focus']) ? $_POST['set_focus'] : $Settings->set_focus), 'id="set_focus" data-placeholder="' . lang("select") . ' ' . lang("set_focus") . '" required="required" class="form-control input-tip select" style="width:100%;"');
?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_view"><?= lang("invoice_view"); ?></label>
                                    <div class="controls">
<?php
$opt_inv = array(1 => lang('tax_invoice'), 0 => lang('standard'), 2 => lang('tax_invoice_hide_summary'));
echo form_dropdown('invoice_view', $opt_inv, $Settings->invoice_view, 'class="form-control tip" required="required" id="invoice_view" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                           
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="tax_classification_view"><?= lang("tax_classification_view"); ?></label>
                                    <div class="controls">
<?php
$opt_inv = array(1 => lang('Yes'), 0 => lang('No'));
echo form_dropdown('tax_classification_view', $opt_inv, $Settings->tax_classification_view, 'class="form-control tip" required="required" id="tax_classification_view" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"  for="item_addition"><?= lang("item_addition"); ?></label>

                                    <div class="controls">
<?php
$ia = array(0 => lang('add_new_item') . ' (Recomended for product batches)', 1 => lang('increase_quantity_if_item_exist'));
echo form_dropdown('item_addition', $ia, $Settings->item_addition, 'id="item_addition" class="form-control tip noedit" required="required" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="add_tax_in_cart_unit_price"><?= lang("Cart Unit Price (Includeing Tax)"); ?></label>
                                    <div class="controls">
<?php
$addunittax = array(1 => lang('Yes'), 0 => lang('No'));
echo form_dropdown('add_tax_in_cart_unit_price', $addunittax, $Settings->add_tax_in_cart_unit_price, 'class="form-control tip noedit" required="required" id="add_tax_in_cart_unit_price" style="width:100%;"');
?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="add_discount_in_cart_unit_price" style="margin-top:17px;"><?= lang("Cart Unit Price (Including Discount)"); ?></label>
                                    <div class="controls">
                                    <?php
                                        $addunitdiscount = array(1 => lang('Yes'), 0 => lang('No'));
                                        echo form_dropdown('add_discount_in_cart_unit_price', $addunitdiscount, $Settings->add_discount_in_cart_unit_price, 'class="form-control tip noedit" required="required" id="add_discount_in_cart_unit_price" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_length" style="margin-top:17px;"><?= lang("Invoice Number Length"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $no_of_invoice_digits = array(4 => '4 (0001)',
                                            5 => '5 (00001)', 6 => '6 (000001)', 7 => '7 (0000001)', 8 => '8 (00000001)',
                                        );
                                        echo form_dropdown('invoice_length', $no_of_invoice_digits, $Settings->invoice_length, 'class="form-control tip noedit" required="required" id="invoice_length" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_format"  style="margin-top:17px;"><?= lang("Invoice Number Format"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $invoice_format = array(
                                            'inv' => '154 (Invoice No)',
                                            'short-fy-inv' => '19-20/154 (FY/Invoice No)',
                                            'long-fy-inv' => '2019-2020/154 (FY/Invoice No)',
                                            'y-m-inv' => '2020/01/154 (Y/M/Invoice No)',
                                            'prepend-inv' => '000154 (Invoice No)',
                                            'short-fy-prepend-inv' => '19-20/000154 (FY/Invoice No)',
                                            'long-fy-prepend-inv' => '2019-2020/000154 (FY/Invoice No)',
                                            'y-m-prepend-inv' => '2020/01/000154  (Y/M/Invoice No)',
                                        );
                                        echo form_dropdown('invoice_format', $invoice_format, $Settings->invoice_format, 'class="form-control tip noedit" title="" required="required" id="invoice_format" style="width:100%;"');
                                        ?>


                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="tax_classification_view"><?= lang("Display_Zero_Sale_for_Product_Report"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                        $Display_Zero_Sale_for_Product_Report_Arr = array(1 => lang('Yes'), 0 => lang('No'));
                                        echo form_dropdown('display_zero_sale_for_product_report', $Display_Zero_Sale_for_Product_Report_Arr, $Settings->display_zero_sale_for_product_report, 'class="form-control tip" required="required" id="display_zero_sale_for_product_report" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="order_discount"><?= lang("Sales Order Discount"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                        $orderdiscount[0] = 'Hide';
                                        $orderdiscount[1] = 'Show';
                                        echo form_dropdown('sales_order_discount', $orderdiscount, $Settings->sales_order_discount, 'class="form-control tip" required="required" id="sales_order_discount" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="order_discount"><?= lang("Reset Invoice Number"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                        $financial_year = [
                                            'F' => 'Year',
                                            'M' => 'Month',
                                            'C' => 'Continue',
                                        ];
                                        echo form_dropdown('financial_type', $financial_year, $Settings->financial_type, 'class="form-control tip noedit" required="required" id="financial_type" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="sale_multiple_return_edit"><?= lang("Multiple_Return_Edit"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('sale_multiple_return_edit', $ps, $Settings->sale_multiple_return_edit, 'id="sale_multiple_return_edit" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="sale_loose_products_with_variants"><?= lang("Loose Products Selling Options"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                    <?php
                                        $saleloose[0] = 'Hide variants while selling';
                                        $saleloose[1] = 'Sale product with variants if exists';
                                        echo form_dropdown('sale_loose_products_with_variants', $saleloose, $Settings->sale_loose_products_with_variants, 'class="form-control tip noedit" required="required" id="sale_loose_products_with_variants" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_weight"><?= lang("Products Weight"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <?php
                                        $pw[0] = 'Disabled';
                                        $pw[1] = 'Enabled';
                                        echo form_dropdown('product_weight', $pw, $Settings->product_weight, 'id="product_weight" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="send_sales_excel"><?= lang("Send Sales Excel"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                    <?php
                                        $sendexcel[0] = 'No';
                                        $sendexcel[1] = 'Yes';
                                        //noedit
                                        echo form_dropdown('send_sales_excel', $sendexcel, $Settings->send_sales_excel, 'class="form-control tip " required="required" id="send_sales_excel" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>
                            
                           <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="synced_data_sales"><?= lang("Sales Synch Data"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                    <?php
                                        $syncedSalesData[0] = 'No';
                                        $syncedSalesData[1] = 'Yes';
                                        //noedit
                                        echo form_dropdown('synced_data_sales', $syncedSalesData, $Settings->synced_data_sales, 'class="form-control tip " required="required" id="send_sales_excel" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>

                           <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="modify_qty_add_products"><?= lang("Modify Qty and add Product"); ?> <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                    <?php
                                        $modifyqtyaddproduct[0] = 'No';
                                        $modifyqtyaddproduct[1] = 'Yes';
                                        //noedit
                                        echo form_dropdown('modify_qty_add_products', $modifyqtyaddproduct, $Settings->modify_qty_add_products, 'class="form-control tip " required="required" id="modify_qty_add_products" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>

                           <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="deposit_discount"><?= lang("Deposit Discount"); ?> (%) <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <input type="text" value="<?= $Settings->deposit_discount?>" 
name="deposit_discount" class="form-control" placeholder="Deposit Discoutn in %" />
                                    </div>
                                </div>
                            </div>

                       
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="deposit_cash_limit"><?= lang("Deposit Cash Limit"); ?> (%) <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <input type="number" value="<?= $Settings->deposit_cash_limit?>" min="0" name="deposit_cash_limit" class="form-control" placeholder="Deposit Cash Limit" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="deposit_service_offer_manage"><?= lang("Deposit Service Offer Manage"); ?> (%) <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <?php
                                        $service_offer = array(1 => lang('Enable'), 0 => lang('Disable'));
                                        echo form_dropdown('deposit_service_offer_manage', $service_offer, $Settings->deposit_service_offer_manage, 'class="form-control tip " required="required" id="invoice_view_purchase" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="deposit_service_offer"><?= lang("Deposit Service Discount"); ?> (%) <img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <input type="text" value="<?= $Settings->deposit_service_offer?>" name="deposit_service_offer" class="form-control" placeholder="Deposit Service Discount in %" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="payment_settlement_method"><?= lang("Bulk Payment settlement Method"); ?> *<img src="<?= $assets ?>images/new.gif" height="25px" alt="new" /> </label>
                                    <div class="controls">
                                        <?php
                                        $payment_method = array(1 => lang('Newest to Oldest'), 0 => lang('Oldest to Newest'));
                                        echo form_dropdown('payment_settlement_method', $payment_method, $Settings->payment_settlement, 'class="form-control tip " required="required"  style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="challan_transporter_details"><?= lang("challan_transporter_details"); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('challan_transporter_details', $ps, $Settings->challan_transporter_details, 'class="form-control tip" required="required" id="challan_transporter_details" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="view_tax_classification_on_challan">View tax classification on challan</label>
                                    <div class="controls">
                                        <?php
                                        $opt_tax_challan = array(1 => lang('Yes'), 0 => lang('No'));
                                        echo form_dropdown('view_tax_classification_on_challan', $opt_tax_challan, $Settings->view_tax_classification_on_challan, 'class="form-control tip" required="required" id="view_tax_classification_on_challan" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('Production Unit') ?></legend>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="production_order_age"><?= lang("Order Age"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                         $ord_age = array(1 => lang('DD:HH:MM:SS'), 0 => lang('HH:MM:SS'));
                                        echo form_dropdown('production_order_age', $ord_age, $Settings->production_order_age, 'class="form-control tip " required="required" id="production_order_age" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Display_Procurment_Order', 'enable_module_procurment_order'); ?><img
                                        src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                    <?php
                                    $options = ['0' => 'No', '1' => 'Yes'];
                                    echo form_dropdown('enable_module_procurment_order', $options, $Settings->enable_module_procurment_order, 'class="form-control" id="enable_module_procurment_order"');
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="set_order_dispatch"><?= lang("Set_Order_Dispatch"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                        $opt = array(1 => lang('Yes'), 0 => lang('No'));
                                        echo form_dropdown('set_order_dispatch', $opt, $Settings->set_order_dispatch, 'class="form-control tip" required="required" id="set_order_dispatch" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Display_Production_Unit', 'enable_module_production_unit'); ?><img
                                        src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                    <?php
                                    echo form_dropdown('enable_module_production_unit', $options, $Settings->enable_module_production_unit, 'class="form-control" id="enable_module_production_unit"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="mother_kitchen"><?= lang("Mother Kitchen"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt_mk = array(1 => lang('Yes'), 0 => lang('No'));
                                        $mother_kitchen_val = isset($Settings->mother_kitchen) ? $Settings->mother_kitchen : 0;
                                        echo form_dropdown('mother_kitchen', $opt_mk, $mother_kitchen_val, 'class="form-control tip" id="mother_kitchen" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('Job Work') ?></legend>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="display_job_work"><?= lang("Display Job Work"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                         $ord_age = array(1 => lang('Yes'), 0 => lang('No'));
                                        echo form_dropdown('display_job_work', $ord_age, $Settings->display_job_work, 'class="form-control tip " required="required" id="display_job_work" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border">Attendance</legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="enable_module_attendance">Display Attendance Module</label>
                                    <?php
                                    $attendance_options = array('1' => lang('Yes'), '0' => lang('No'));
                                    $attendance_enabled = isset($Settings->enable_module_attendance) ? (string) $Settings->enable_module_attendance : '1';
                                    echo form_dropdown('enable_module_attendance', $attendance_options, $attendance_enabled, 'class="form-control" id="enable_module_attendance"');
                                    ?>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('purchase') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_view_purchase"  style="margin-top:16px;"><?= lang("invoice_view_purchase"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt_inv = array(1 => lang('tax_invoice'), 0 => lang('standard'));
                                        echo form_dropdown('invoice_view_purchase', $opt_inv, $Settings->invoice_view_purchase, 'class="form-control tip " required="required" id="invoice_view_purchase" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="tax_classification_view__purchase"  style="margin-top:16px;"><?= lang("tax_classification_view__purchase"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt_inv = array(1 => lang('Yes'), 0 => lang('No'));
                                        echo form_dropdown('tax_classification_view__purchase', $opt_inv, $Settings->tax_classification_view__purchase, 'class="form-control tip" required="required" id="tax_classification_view__purchase" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="purchase_discount"><?= lang("Purchase Order Discount"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                        <?php
                                        $purchasediscount[0] = 'Hide';
                                        $purchasediscount[1] = 'Show';
                                        echo form_dropdown('purchase_order_discount', $purchasediscount, $Settings->purchase_order_discount, 'class="form-control tip" required="required" id="purchase_order_discount" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('prefix') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="sales_prefix"><?= lang("sales_prefix"); ?></label>

<?= form_input('sales_prefix', $Settings->sales_prefix, 'class="form-control tip noedit" id="sales_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="return_prefix"><?= lang("return_prefix"); ?></label>

<?= form_input('return_prefix', $Settings->return_prefix, 'class="form-control tip noedit" id="return_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="payment_prefix"><?= lang("payment_prefix"); ?></label>
<?= form_input('payment_prefix', $Settings->payment_prefix, 'class="form-control tip noedit" id="payment_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="ppayment_prefix"><?= lang("ppayment_prefix"); ?></label>
<?= form_input('ppayment_prefix', $Settings->ppayment_prefix, 'class="form-control tip noedit" id="ppayment_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="delivery_prefix"><?= lang("delivery_prefix"); ?></label>

<?= form_input('delivery_prefix', $Settings->delivery_prefix, 'class="form-control tip noedit" id="delivery_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="quote_prefix"><?= lang("quote_prefix"); ?></label>

<?= form_input('quote_prefix', $Settings->quote_prefix, 'class="form-control tip noedit" id="quote_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="purchase_prefix"><?= lang("purchase_prefix"); ?></label>

<?= form_input('purchase_prefix', $Settings->purchase_prefix, 'class="form-control tip noedit" id="purchase_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="returnp_prefix"><?= lang("returnp_prefix"); ?></label>

<?= form_input('returnp_prefix', $Settings->returnp_prefix, 'class="form-control tip noedit" id="returnp_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="transfer_prefix"><?= lang("transfer_prefix"); ?></label>
<?= form_input('transfer_prefix', $Settings->transfer_prefix, 'class="form-control tip noedit" id="transfer_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('expense_prefix', 'expense_prefix'); ?>
<?= form_input('expense_prefix', $Settings->expense_prefix, 'class="form-control tip noedit" id="expense_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('qa_prefix', 'qa_prefix'); ?>
<?= form_input('qa_prefix', $Settings->qa_prefix, 'class="form-control tip noedit" id="qa_prefix"'); ?>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('money_number_format') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="decimals"><?= lang("decimals"); ?></label>

                                    <div class="controls"> <?php
                                        $decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                        echo form_dropdown('decimals', $decimals, $Settings->decimals, 'class="form-control tip" id="decimals"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="qty_decimals"><?= lang("qty_decimals"); ?></label>

                                    <div class="controls"> <?php
                                        $qty_decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                        echo form_dropdown('qty_decimals', $qty_decimals, $Settings->qty_decimals, 'class="form-control tip" id="qty_decimals"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('sac', 'sac'); ?>
<?= form_dropdown('sac', $ps, set_value('sac', $Settings->sac), 'class="form-control tip" id="sac"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="nsac">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="decimals_sep"><?= lang("decimals_sep"); ?></label>

                                        <div class="controls"> <?php
                                            $dec_point = array('.' => lang('dot'), ',' => lang('comma'));
                                            echo form_dropdown('decimals_sep', $dec_point, $Settings->decimals_sep, 'class="form-control tip" id="decimals_sep"  style="width:100%;" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="thousands_sep"><?= lang("thousands_sep"); ?></label>
                                        <div class="controls"> <?php
                                            $thousands_sep = array('.' => lang('dot'), ',' => lang('comma'), '0' => lang('space'));
                                            echo form_dropdown('thousands_sep', $thousands_sep, $Settings->thousands_sep, 'class="form-control tip" id="thousands_sep"  style="width:100%;" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('display_currency_symbol', 'display_symbol'); ?>
                                    <?php $opts = array(0 => lang('disable'), 1 => lang('before'), 2 => lang('after')); ?>
<?= form_dropdown('display_symbol', $opts, $Settings->display_symbol, 'class="form-control" id="display_symbol" style="width:100%;" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('currency_symbol', 'symbol'); ?>
<?= form_input('symbol', $Settings->symbol, 'class="form-control" id="symbol" style="width:100%;"'); ?>
                                </div>
                            </div>
                        </fieldset>



                        <fieldset class="scheduler-border" id="emailNsms">
                            <legend class="scheduler-border"><?= lang('email') ?> & SMS & WhatsApp</legend>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="protocol"><?= lang("email_protocol"); ?></label>

                                    <div class="controls"> <?php
                                        $popt = array('smtp' => 'SMTP','mail' => 'PHP Mail Function', 'sendmail' => 'Send Mail' ); //noedit
                                        echo form_dropdown('protocol', $popt, $Settings->protocol, 'class="form-control tip noedit" id="protocol"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="sms_sender"><?= 'SMS Transactional Header'; ?> *</label>

                                    <div class="controls">                                   
<?= form_input('sms_sender', $Settings->sms_sender, 'class="form-control tip " id="sms_sender" maxlength="10"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="sms_promotional_header"><?= 'SMS Promotional Header'; ?> *</label>

                                    <div class="controls">                                   
<?= form_input('sms_promotional_header', $Settings->sms_promotional_header, 'class="form-control tip " id="sms_promotional_header" maxlength="10"'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="reportsend"><?= 'Report Send On Email'; ?> *</label>

                                    <div class="controls">                                   
                                        <select id="reportsend" class="form-control" name="reports_send_on_email">
                                            <option value="0" <?= ($Settings->reports_send_on_email == '0' ? 'Selected' : '') ?>>None</option>
                                            <option value="1" <?= ($Settings->reports_send_on_email == '1' ? 'Selected' : '') ?>>Monthly</option>

                                        </select>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="sms_brand_name"><?= 'SMS Brand Name'; ?> *</label>

                                    <div class="controls">                                   
                                     <?= form_input('sms_brand_name', $Settings->sms_brand_name, 'class="form-control tip " id="sms_brand_name" maxlength="30"'); ?>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"
                                        for="whatsapp_service"><?= lang("WhatsApp Service"); ?></label>
                                    <div class="controls">
                                        <?php
                                            $addunittax = array(1 => lang('Yes'), 0 => lang('No'));
                                            echo form_dropdown('whatsapp_service', $addunittax, $Settings->whatsapp_service, 'class="form-control tip noedit" required="required" id="whatsapp_service" style="width:100%;"');
                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row" id="sendmail_config" style="display: none;">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="mailpath"><?= lang("mailpath"); ?></label>
<?= form_input('mailpath', $Settings->mailpath, 'class="form-control tip" id="mailpath"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row" id="smtp_config" style="display: none;">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label"
                                                   for="smtp_host"><?= lang("smtp_host"); ?></label>

<?= form_input('smtp_host', $Settings->smtp_host, 'class="form-control tip" id="smtp_host"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label"
                                                   for="smtp_user"><?= lang("smtp_user"); ?></label>

<?= form_input('smtp_user', $Settings->smtp_user, 'class="form-control tip" id="smtp_user"'); ?> </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label"
                                                   for="smtp_pass"><?= lang("smtp_pass"); ?></label>

<?= form_password('smtp_pass', $smtp_pass, 'class="form-control tip" id="smtp_pass"'); ?> </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label"
                                                   for="smtp_port"><?= lang("smtp_port"); ?></label>

<?= form_input('smtp_port', $Settings->smtp_port, 'class="form-control tip" id="smtp_port"'); ?> </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label"
                                                   for="smtp_crypto"><?= lang("smtp_crypto"); ?></label>

                                            <div class="controls"> <?php
                                                $crypto_opt = array('' => lang('none'), 'tls' => 'TLS', 'ssl' => 'SSL');
                                                echo form_dropdown('smtp_crypto', $crypto_opt, $Settings->smtp_crypto, 'class="form-control tip" id="smtp_crypto"');
                                                ?> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="scheduler-border" >
                            <legend class="scheduler-border">Customer <?= lang('award_points') ?></legend>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><?= lang("customer_award_points"); ?></label>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_spent'); ?><br>
<?= form_input('each_spent', $this->sma->formatDecimal($Settings->each_spent), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-3 col-xs-5">
                                            <?= lang('award_points'); ?><br>
<?= form_input('ca_point', $Settings->ca_point, 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-left"></i>
                                        </div>
                                        <div class="col-sm-3 col-xs-6">
                                            <?= lang('each_redeem'); ?><br>
<?= form_input('each_redeem', $this->sma->formatDecimal($Settings->each_redeem), 'class="form-control"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><?= lang("staff_award_points"); ?></label>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_in_sale'); ?><br>
<?= form_input('each_sale', $this->sma->formatDecimal($Settings->each_sale), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-3 col-xs-5">
                                            <?= lang('award_points'); ?><br>
<?= form_input('sa_point', $Settings->sa_point, 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center">&nbsp;</div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label" for="award_point_by_percent"><?= lang("Redeemption_Percent"); ?></label>

                                                <div class="controls">
                                                    <?php
                                                    $RedeemptionPercent = array(10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 100);
                                                    echo form_dropdown('award_point_by_percent', $RedeemptionPercent, $Settings->award_point_by_percent, 'id="award_point_by_percent" class="form-control tip" required="required" style="width:100%;"');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-12">
                                <hr>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <?= lang('Synch Award Points With Consumer App', 'Synch Award Points'); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                        <?php $poptRwdpt = array(0 => lang('no'), 1 => lang('yes')); ?>
<?= form_dropdown('synch_reward_points', $poptRwdpt, $Settings->synch_reward_points, 'class="form-control" id="synch_reward_points" required="required" disabled'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <?= lang('Synch Customers With Consumer App', 'Synch Customers'); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                        <?php $poptsyncu = array(0 => lang('no'), 1 => lang('yes')); ?>
<?= form_dropdown('synch_customers', $poptsyncu, $Settings->synch_customers, 'class="form-control" id="synch_customers" required="required" disabled'); ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
<?php if ($Owner || $Admin) { ?>    
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border"><?= lang('API 3') ?></legend>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="api_privatekey"><?= "API3 Access Private key"; ?></label>                                
                                        <?php
                                        if ($Settings->api_privatekey) {
                                            ?>
                                            <span class="form-control"><?= $Settings->api_privatekey ?></span> 
                                            <?php
                                        } else {
                                            ?> 
                                            <div id="apiprivatekey"><button class="btn btn-warning" id="generate_api_privatekey">Generate Private Key</button></div>
                                            <?php
                                        }//end else
                                        ?>                                
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <!--<span class="pull-right"><a href="https://simplypos.in/api/api3/POS_Eshop_API_3_Documents.pdf" target="_new">Documents</a></span>-->
                                        <label class="control-label " for="api_privatekey"><?= "Eshop API3 URL"; ?> </label>                              
                                        <span class="form-control"><?= base_url('api3/eshop'); ?></span> 

                                    </div>
                                </div><div class="col-md-4">
                                    <div class="form-group">                                
                                        <label class="control-label" for="api_url"><?= "Offline POS API3 URL"; ?></label>                              
                                        <span class="form-control"><?= base_url('api3/offline'); ?></span> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="api_access"><?= "API3 Access Status"; ?></label>                              
                                        <span class="form-control"><?= $Settings->api_access ? 'Active' : 'Blocked' ?></span>
                                    </div>
                                </div>

                            </fieldset>
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border">External Modules Status</legend>                                 
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label " for="active_eshop">Eshop Module</label>                              
                                        <span class="form-control"><?= $Settings->active_eshop ? 'Active' : 'Deactive' ?></span> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label " for="active_webshop">Webshop / E-commerce Module</label>                              
                                        <span class="form-control"><?= $Settings->active_webshop ? 'Active' : 'Deactive' ?></span> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label " for="active_urbanpiper">Urban Piper Delivery Platform</label>                              
                                        <span class="form-control"><?= $Settings->active_urbanpiper ? 'Active' : 'Deactive' ?></span> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label " for="active_omnichannel">Omnichannel</label>                              
                                        <span class="form-control"><?= $Settings->active_omnichannel ? 'Active' : 'Deactive' ?></span> 
                                    </div>
                                </div>
                            </fieldset>
<?php } ?>    
                        
                    </div>
                </div>
                <div style="clear: both; height: 10px;"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="controls">
                            <?php if($_GET['edit_code']=='e603796dddf1b823126b32fd6ad212d2') { ?>
                            <input type="hidden" name="edit_code" value="<?=$_GET['edit_code']?>" />
                            <?php } ?>
                            <?= form_submit('update_settings', lang("update_settings"), 'class="btn btn-primary"'); ?>
                        </div>
                    </div>
                </div>
<?= form_close(); ?>
            </div>
        </div>
        <div class="alert alert-info" role="alert"><p>
                <strong>Cron Job:</strong> <code>0 1 * * * wget -qO- <?= site_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</code> to run at 1:00 AM daily. For local installation, you can run cron job manually at any time.
                <?php if (!DEMO) { ?>
                <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= site_url('cron/run'); ?>">Run cron job now</a>
<?php } ?>
            </p></div>
    </div>
</div>
 <?php if($_GET['edit_code']!='e603796dddf1b823126b32fd6ad212d2') { ?>
<script>
$(document).ready(function(){
 
   $('.noedit option').attr('disabled','disabled'); 
   $('.noedit option:selected').attr('disabled',false); 
  
   $('input.noedit').attr('readonly',true);
    var  Elable = '';
    $('.noedit').each(function() {       
       Elable = $('label[for="'+this.id+'"]').html();       
       $('label[for="'+this.id+'"]').html(Elable + ' <i class="fa fa-ban text-danger" aria-hidden="true"></i>');
    });
});
</script>
 <?php } ?>
