<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
//////////////////////////////////////// whatsapp integration /////////////////////////////////////////
$whatsapp_api_key = $this->Settings->whatsapp_api_key;
$sms_option = array(1 => lang('SMS'), 0 => lang('No'));

if (!empty($whatsapp_api_key)) {
    $sms_option[2] = lang('Whatsapp Message');
    $sms_option[3] = lang('Both SMS/Whatsapp');
}
function get_times($default = '', $interval = '+30 minutes') {
    $output = "<option value=''>Any Time</option>";
    $current = strtotime('00:00');
    $end = strtotime('23:59');
    while ($current <= $end) {
        $time = date('H:i', $current);
        $sel = ( $time == $default ) ? ' selected' : '';
        $output .= "<option value=\"{$time}\"{$sel}>" . date('h.i A', $current) . '</option>';
        $current = strtotime($interval, $current);
    }
    return $output;
}
?>
<style>
hr {
    height: 0.05em;
    background: #cccccc;
}
.pm-section-wrap { padding: 0px 5px 0; }
.pm-section-head { display: flex; align-items: center; flex-wrap: wrap; margin-bottom: 10px; }
.pm-section-head .pm-btn-add { margin-left: 0; float: none; }
.pm-section-head h3 { margin: 0 0 6px; font-size: 22px; font-weight: 700; color: #1a1a2e; }
.pm-section-head p { margin: 0; color: #6b7280; font-size: 13px; max-width: 520px; }
.pm-btn-add { background: #428bca !important; border-color: #428bca !important; border-radius: 8px; padding: 10px 18px; font-weight: 600; }
.pm-btn-add:hover { background: #428bca !important; border-color: #428bca !important; }
.pm-methods-scroll { max-height: 430px; overflow-y: auto; padding-right: 4px; }
.pm-methods-list { list-style: none; margin: 0; padding: 0; }
.pm-method-item { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; margin-bottom: 12px; cursor: default; }
.pm-method-item.ui-sortable-helper { box-shadow: 0 8px 24px rgba(0,0,0,.12); z-index: 1000; }
.pm-sortable-placeholder {
    border: 2px dashed #c4b5fd !important;
    background: #f5f3ff !important;
    visibility: visible !important;
    margin-bottom: 12px;
    min-height: 72px;
    box-sizing: border-box;
}
.pm-drag-handle { color: #9ca3af; cursor: grab; font-size: 18px; width: 22px; text-align: center; flex-shrink: 0; touch-action: none; user-select: none; }
.pm-drag-handle:active { cursor: grabbing; }
.pm-methods-scroll.ui-sortable-scroll-parent { position: relative; }
.pm-method-icon { width: 48px; height: 48px; border-radius: 10px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #4b5563; font-size: 20px; overflow: hidden; }
.pm-method-icon img.pm-method-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
}
.pm-logo-preview-wrap { margin-top: 10px; }
.pm-logo-preview-wrap img { max-height: 64px; max-width: 160px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; background: #fff; }
.pm-method-body { flex: 1; min-width: 0; }
.pm-method-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.pm-method-type { font-size: 12px; color: #6b7280; margin: 0 0 2px; }
.pm-method-updated { font-size: 11px; color: #9ca3af; margin: 0; }
.pm-method-status { flex-shrink: 0; }
.pm-status-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
.pm-status-active { background: #ecfdf5; color: #059669; }
.pm-status-inactive { background: #f3f4f6; color: #6b7280; }
.pm-status-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.pm-status-active .pm-status-dot { background: #10b981; }
.pm-status-inactive .pm-status-dot { background: #9ca3af; }
.pm-method-default { flex-shrink: 0; text-align: center; min-width: 110px; }
.pm-default-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #428bca;
}
.pm-default-badge i {
    font-size: 16px;
    color: #428bca;
}
.pm-method-actions { display: flex; gap: 8px; flex-shrink: 0; }
.pm-action-btn { width: 36px; height: 36px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; color: #6b7280; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
.pm-action-btn:hover { background: #f9fafb; }
.pm-action-edit:hover { color: #2563eb; border-color: #bfdbfe; }
.pm-action-delete:hover { color: #dc2626; border-color: #fecaca; }
.pm-info-bar { margin-top: 14px; padding: 12px 14px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px; font-size: 13px; color: #1e40af; }
.pm-info-bar i { margin-right: 8px; }
.pm-empty { text-align: center; padding: 40px 20px; color: #9ca3af; border: 1px dashed #d1d5db; border-radius: 12px; }
#pmMethodModal .modal-header { border-bottom: 1px solid #eee; }
#pmMethodModal .modal-title { font-weight: 700; }
#pmMethodModal .pm-gateway-box { background: #f5f3ff; border: 1px solid #e9e5ff; border-radius: 8px; padding: 14px; margin: 12px 0; }
#pmMethodModal .pm-gateway-box h5 { margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #428bca; }
#pmMethodModal .btn-primary { background: #428bca; border-color: #428bca; }
#pmMethodModal .btn-primary:hover { background: #428bca; border-color: #428bca; }
#pmMethodModal.modal { overflow: hidden; }
#pmMethodModal .modal-dialog {
    max-height: calc(100vh - 40px);
    margin: 20px auto;
}
#pmMethodModal .modal-content {
    max-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
}
#pmMethodModal .modal-body {
    max-height: none;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    flex: 1 1 auto;
}
#pmMethodModal #pm-gateway-fields { display: none; }
#legacy-pos-payment-fields { display: none; }
.checkbox label {
    padding-left: 0px !important;
}
/* Force POS settings fields into strict 3-column rows */
fieldset.scheduler-border {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
}
fieldset.scheduler-border > .col-md-4,
fieldset.scheduler-border > .col-sm-4,
fieldset.scheduler-border > .col-sm-4.col-md-4 {
    float: none;
    width: 33.33333333%;
    max-width: 33.33333333%;
}
fieldset.scheduler-border > legend,
fieldset.scheduler-border > .row,
fieldset.scheduler-border > .pm-section-wrap,
fieldset.scheduler-border > .modal,
fieldset.scheduler-border > .col-md-12,
fieldset.scheduler-border > .col-lg-12 {
    flex: 0 0 100%;
    width: 100%;
    max-width: 100%;
}
fieldset.scheduler-border > .col-md-4 .form-group,
fieldset.scheduler-border > .col-sm-4 .form-group {
    min-height: 74px;
    margin-bottom: 10px;
}
.text-danger {
    color: #a94442 !important;
}
@media (max-width: 991px) {
    fieldset.scheduler-border > .col-md-4,
    fieldset.scheduler-border > .col-sm-4,
    fieldset.scheduler-border > .col-sm-4.col-md-4 {
        width: 50%;
        max-width: 50%;
    }
}
@media (max-width: 767px) {
    fieldset.scheduler-border > .col-md-4,
    fieldset.scheduler-border > .col-sm-4,
    fieldset.scheduler-border > .col-sm-4.col-md-4 {
        width: 100%;
        max-width: 100%;
    }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cogs"></i><?= lang('pos_settings'); ?></h2>
        <?php if (isset($pos->purchase_code) && !empty($pos->purchase_code) && $pos->purchase_code != 'purchase_code') { ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= site_url('pos/updates') ?>" class="toggle_down"><i
                            class="icon fa fa-upload"></i><span
                            class="padding-right-10"><?= lang('updates'); ?></span></a>
                </li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('update_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos_setting');
                echo form_open_multipart("pos/settings", $attrib);
                ?>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('pos_config') ?></legend>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('pro_limit', 'limit'); ?>
                            <?= form_input('pro_limit', $pos->pro_limit, 'class="form-control" id="limit" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('pos_screen_products', 'pos_screen_products'); ?>
                            <?php $arr1 = array('0'=>'Default Category','1'=>'Favourite Products','2'=>'Rank Wise Products','3'=>'Seasons Wise Products')?>
                            <?= form_dropdown('pos_screen_products', $arr1, $pos->pos_screen_products, 'class="form-control" id="pos_screen_products" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('delete_code', 'pin_code'); ?>
                            <?= form_input('pin_code', $pos->pin_code, 'class="form-control" pattern="[0-9]{4,8}"id="pin_code"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('default_category', 'default_category'); ?>
                            <?php
                            $ct[''] = lang('select') . ' ' . lang('default_category');
                            foreach ($categories as $catrgory) {
                                $ct[$catrgory->id] = $catrgory->name;
                            }
                            echo form_dropdown('category', $ct, $pos->default_category, 'class="form-control" id="default_category" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('default_biller', 'default_biller'); ?>
                            <?php
                            $bl[0] = "";
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            if (isset($_POST['biller'])) {
                                $biller = $_POST['biller'];
                            } else {
                                $biller = "";
                            }
                            echo form_dropdown('biller', $bl, $pos->default_biller, 'class="form-control" id="default_biller" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('default_customer', 'customer1'); ?>
                            <?= form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $pos->default_customer), 'id="customer1" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control" style="width:100%;"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('display_time', 'display_time'); ?>
                            <?php
                            $yn = array('1' => lang('yes'), '0' => lang('no'));
                            echo form_dropdown('display_time', $yn, $pos->display_time, 'class="form-control" id="display_time" required="required"');
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('onscreen_keyboard', 'keyboard'); ?>
                            <?php
                            echo form_dropdown('keyboard', $yn, $pos->keyboard, 'class="form-control" id="keyboard" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('product_button_color', 'product_button_color'); ?>
                            <?php
                            $col = array('default' => lang('default'), 'primary' => lang('primary'), 'info' => lang('info'), 'warning' => lang('warning'), 'danger' => lang('danger'));
                            echo form_dropdown('product_button_color', $col, $pos->product_button_color, 'class="form-control" id="product_button_color" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">

                            <?= lang('product_background_color', 'limit'); ?>
                            <?= form_input('pos_theme[css_class_product][background_color]', $pos->pos_theme->css_class_product->background_color, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('tooltips', 'tooltips'); ?>
                            <?php
                            echo form_dropdown('tooltips', $yn, $pos->tooltips, 'class="form-control" id="tooltips" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('rounding', 'rounding'); ?>
                            <?php
                            $rnd = array('0' => lang('disable'), '1' => lang('to_nearest_005'), '2' => lang('to_nearest_050'), '3' => lang('to_nearest_number'), '4' => lang('to_next_number'));
                            echo form_dropdown('rounding', $rnd, $pos->rounding, 'class="form-control" id="rounding" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('item_order', 'item_order'); ?>
                            <?php $oopts = array(0 => lang('Newest To Oldest'), 2 => lang('Oldest To Newest'),1 => lang('category')); ?>
                            <?= form_dropdown('item_order', $oopts, $pos->item_order, 'class="form-control" id="item_order" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('after_sale_page', 'after_sale_page'); ?>
                            <?php $popts = array(0 => lang('receipt'), 1 => lang('pos')); ?>
                            <?= form_dropdown('after_sale_page', $popts, $pos->after_sale_page, 'class="form-control" id="after_sale_page" required="required"'); ?>
                        </div>
                    </div>


                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('enable_java_applet', 'enable_java_applet'); ?>
                            <?= form_dropdown('enable_java_applet', $yn, $pos->java_applet, 'class="form-control" id="enable_java_applet" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <input type="hidden" id="whatsapp_api_key" value="<?= $whatsapp_api_key ?>">
                        <div class="form-group">
                            <?= lang('Auto Invoice Message', 'Auto Invoice SMS') ?>
                            <?= form_dropdown('invoice_auto_sms', $sms_option, $pos->invoice_auto_sms, 'class="form-control" id="invoice_auto_sms" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label>Apply Offers* </label>
                            <?php $offersStatus = array(1 => lang('Enable'), 0 => lang('Disable')); ?>
                            <?= form_dropdown('offers_status', $offersStatus, $pos->offers_status, 'class="form-control" id="offers_status" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('Active Offers', 'Active Offers'); ?>
                            <?php
                            $offersCategory[''] = 'None';
                            foreach ($offer_categories as $id => $offer) {
                                $offersCategory[$offer->offer_keyword] = $offer->offer_category;
                            }
                            ?>
                            <?= form_dropdown('active_offer_category', $offersCategory, $pos->active_offer_category, 'class="form-control" id="active_offer_category" '); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('Recent_Sale_Limit', 'Recent_Sale_Limit'); ?>
                            <?php
                            //$ArrPosSaleLimit = array('5'=>5, '10'=>10, '15'=>15, '20'=>20); 
                            $ArrPosSaleLimit = [];
                            for ($i = 10; $i <= 100; $i = $i + 10) {
                                $ArrPosSaleLimit[$i] = $i;
                            }
                            ?>
                            <?= form_dropdown('recent_pos_limit', $ArrPosSaleLimit, $pos->recent_pos_limit, 'class="form-control" id="Recent_POS_Limit" '); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label>Display Token No </label>
                            <?php $TokenArr = array(1 => 'Yes', 0 => 'No'); ?>
                            <?= form_dropdown('display_token', $TokenArr, $pos->display_token, 'class="form-control" id="display_token" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label>Auto Selected Checkout Amount </label>
                            <?php $AmtArr = array(1 => 'Yes', 0 => 'No'); ?>
                            <?= form_dropdown('pos_amount', $AmtArr, $pos->pos_amount, 'class="form-control" id="pos_amount" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label>Display Salesperson </label>
                            <?php $TokenArr = array(4 => 'Product Level',3 => 'Product Level(Mandatory)',2 => 'Yes(Mandatory)',1 => 'Yes', 0 => 'No'); ?>
                            <?= form_dropdown('display_seller', $TokenArr, $pos->display_seller, 'class="form-control" id="display_seller" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 ">
                        <div class="form-group">
                            <label>Auto Email For Alert Quantity</label>
                            <?php //$autoEmail = array(1 => 'Yes', 0 => 'No');  ?>
                            <select name='alert_qty_auto_email' id="alert_qty_auto_email" class="form-control">
                                <option value="" selected>Choose here</option>
                                <option value="0" <?php echo ($pos->alert_qty_auto_email == 0) ? "selected" : "" ?>>
                                    Don't Send</option>
                                <option value="1" <?php echo ($pos->alert_qty_auto_email == 1) ? "selected" : "" ?>>Send
                                    Email on Register closed</option>
                                <option value="2" <?php echo ($pos->alert_qty_auto_email == 2) ? "selected" : "" ?>>Send
                                    Email on Logout</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 ">
                        <div class="form-group">
                            <label>Auto Email For Daily Sale</label>
                            <?php //$autoEmail = array(1 => 'Yes', 0 => 'No');  ?>
                            <select name='daily_sale_auto_email' id="daily_sale_auto_email" class="form-control">
                                <option value="" selected>Choose here</option>
                                <option value="0" <?php echo ($pos->daily_sale_auto_email == 0) ? "selected" : "" ?>>
                                    Don't Send</option>
                                <option value="1" <?php echo ($pos->daily_sale_auto_email == 1) ? "selected" : "" ?>>
                                    Send Email on Register closed</option>
                                <option value="2" <?php echo ($pos->daily_sale_auto_email == 2) ? "selected" : "" ?>>
                                    Send Email on Logout</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <!-- <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label>Display Category </label>
                            <?php $DisplayCategoryArr = array(1 => 'Yes', 0 => 'No'); ?>
                            <?= form_dropdown('display_category', $DisplayCategoryArr, $pos->display_category, 'class="form-control" id="display_category" required="required"'); ?>
                        </div>
                    </div> -->
                    <div class="col-md-4 col-sm-4 pos-settings-grid-item">
                        <div class="form-group">
                            <label>Product Variant Selection Popup </label>
                            <?php $selectarr = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('product_variant_popup', $selectarr, $pos->product_variant_popup, 'class="form-control" id="product_variant_popup" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 pos-settings-grid-item">
                        <div class="form-group">
                            <label>Use Product Price </label>
                            <select name='use_product_price' id="use_product_price" class="form-control">
                                <option value="price"
                                    <?php echo ($pos->use_product_price == 'price') ? "selected" : "" ?>>Price</option>
                                <option value="mrp" <?php echo ($pos->use_product_price == 'mrp') ? "selected" : "" ?>>
                                    MRP</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 pos-settings-grid-item">
                        <div class="form-group">
                            <?= lang('Show Cart On Pos2', 'Show Cart On Pos2'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $pos2 = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('cart_show_pos2', $pos2, $pos->cart_show_pos2, 'class="form-control" id="cart_show_pos2" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 pos-settings-grid-item">
                        <div class="form-group" title="Apply Only on Loose Products">
                            <?= lang('QR Code Scan', 'QR Code Scan'); ?> <img src="<?= $assets ?>images/new.gif"
                                height="30px" alt="new" />
                            <?php $qr_code_scanner = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('display_qr_code_scanner', $qr_code_scanner, $pos->display_qr_code_scanner, 'class="form-control" id="display_qr_code_scanner" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 pos-settings-grid-item">
                        <div class="form-group">
                            <?= lang('Adjust Cart Quantity On Cart Price Change', 'update_cart_quantity'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $setUserPrice = array(1 => 'Enable (Apply Only On Loose Products)', 0 => 'Disable'); ?>
                            <?= form_dropdown('change_qty_as_per_user_price', $setUserPrice, $pos->change_qty_as_per_user_price, 'class="form-control" id="change_qty_as_per_user_price" required="required"'); ?>
                        </div>
                    </div>

                    <!--  Order Receipt -->
                    <div class="col-md-4 col-sm-4 pos-settings-grid-item">
                        <div class="form-group">
                            <?= lang('Order Receipt Print', 'Order Receipt Print'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $setReceipt = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('order_receipt', $setReceipt, $pos->order_receipt, 'class="form-control" id="order_receipt_print" required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4" id="PrintAllCategoryBlock">
                        <div class="form-group">
                            <?= lang('Print All Category', 'Print All Category'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $setReceipt = array(1 => 'Yes', 0 => 'No'); ?>
                            <?= form_dropdown('print_all_category', $setReceipt, $pos->print_all_category, 'class="form-control" id="print_all_category" required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4" id="orderPrintCategorys" style="display:none">
                        <div class="form-group">
                            <?= lang('Order Print Category', 'Order Print Category'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <select name="categorys[]" class="form-control" multiple="true" id="categorys">
                                <?php 
                                 $categoryArr =  explode(',',$pos->categorys);
                                 foreach($categories as $category_Items){ 
                                     $selection = '';
                                     if(in_array($category_Items->id,$categoryArr )){
                                          $selection ='selected';
                                     }
                                     ?>
                                <option value="<?= $category_Items->id ?>" <?=$selection?>> <?= $category_Items->name ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <!-- End Order Receipt -->

                    <div class="col-sm-4 col-md-4 pos-settings-grid-item">
                        <div class="form-group">
                            <?= lang('Show Deposit Button', 'Show Deposit Button'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $setReceipt = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('add_deposit_btn_show', $setReceipt, $pos->add_deposit_btn_show, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('active_repeat_customer_discount','active_repeat_customer_discount') ?>
                            <?php $repeatSaleDiscount = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('active_repeat_customer_discount', $repeatSaleDiscount, $pos->active_repeat_customer_discount, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('auto_apply_repeat_customer_discount', 'auto_apply_repeat_customer_discount') ?>
                            <?php $AutorepeatSaleDiscount = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('auto_apply_repeat_customer_discount', $AutorepeatSaleDiscount, $pos->auto_apply_repeat_customer_discount, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>


                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Combo Product Create on Pos', 'Combo Product Create on Pos') ?>
                            <?php $comboaddpos = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('combo_add_pos', $comboaddpos, $pos->combo_add_pos, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>


                    <?php if($Settings->pos_type == 'restaurant'){ ?>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Restaurant Table', 'Restaurant Table') ?>
                            <?php $restaurantTable = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('restaurant_table', $restaurantTable, $pos->restaurant_table, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Show Featuerd Products Price', 'Show Featuerd Products Price') ?>
                            <?php $featuerdProducts = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('pos_price_display', $featuerdProducts, $pos->pos_price_display, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Filter_Only_Available_Category', 'Filter_only_available_category') ?>
                            <?php $featuerdProducts = array(1 => 'Yes', 0 => 'No'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('categorylocation', $featuerdProducts, $pos->categorylocation, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Hide_Product_Code', 'Hide_Product_Code') ?>
                            <?php $featuerdProducts = array(0 => 'Yes', 1 => 'No'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('hideProductsCode', $featuerdProducts, $pos->hideProductsCode, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Auto Print Receipt', 'Auto Print Receipt') ?>
                            <?php $featuerdProducts = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('auto_print_receipt',$featuerdProducts, $pos->auto_print_receipt, 'class="form-control"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display CRM', 'Display CRM') ?>
                            <?php $displaycrm = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('display_CRM',$displaycrm, $pos->display_CRM, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display_coinage','Display_coinage') ?>
                            <?php $displaycoinage = array(1 => 'Enable', 0 => 'Disable',2 => 'Enable Register'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('display_coinage', $displaycoinage, $pos->display_coinage, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display Return Button', 'Display Return Button') ?>
                             <?php $display_return_button = array(1 => 'Enable', 0 => 'Disable'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                              <?= form_dropdown('display_return',$display_return_button, $pos->display_return, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display Exchange Button', 'Display Exchange Button') ?>
                             <?php $display_exchange_button = array(1 => 'Enable', 0 => 'Disable'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                              <?= form_dropdown('display_exchange',$display_exchange_button, $pos->display_exchange, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display Customer Scan', 'Display Customer Scan') ?>
                             <?php $displaycustomer_scan = array(1 => 'Enable', 0 => 'Disable'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                              <?= form_dropdown('display_customer_scan',$displaycustomer_scan, $pos->display_customer_scan, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <!-- <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display Season', 'Display Season') ?>
                             <?php $displayseason = array(1 => 'Enable', 0 => 'Disable'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                              <?= form_dropdown('display_season',$displayseason, $pos->display_season, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display Search Category', 'Display Search Category') ?>
                             <?php $displaycategory = array(1 => 'Enable', 0 => 'Disable'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                              <?= form_dropdown('display_search_category',$displaycategory, $pos->display_search_category, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div> -->
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display_Coupon_Code', 'display_coupon_code') ?>
                            <?php $display_coupon_code = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('display_coupon_code',$display_coupon_code, $pos->display_coupon_code, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Display_Description', 'display_description') ?>
                            <?php $display_description = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('display_description',$display_description, $pos->display_description, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Show Current Stock', 'Show Current Stock') ?>
                            <?php $show_current_stock = array(1 => 'Enable', 0 => 'Disable'); ?> <img
                                src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?= form_dropdown('show_current_stock',$show_current_stock, $pos->show_current_stock, 'class="form-control"  required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Kitchen Printer', 'Kitchen Printer') ?>
                             <?php $tokenprinter = array(1 => 'Global', 0 => 'Local'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                             <?= form_dropdown('token_printer', $tokenprinter, $pos->token_printer, 'class="form-control" id="token_printer" required="required"'); ?>

                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group">
                            <?= lang('Counter Functionality', 'Counter Functionality') ?>
                             <?php $counter_functionality = array(1 => 'Enable', 0 => 'Disable'); ?>  <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                             <?= form_dropdown('counter_functionality', $counter_functionality, $pos->counter_functionality, 'class="form-control" id="counter_functionality" required="required"'); ?>

                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group" title="Control Season Dropdown Visibility">
                            <label> Display Season</label> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $select_season = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('select_season', $select_season, $pos->select_season, 'class="form-control" id="select_season" required="required"'); ?>
                        </div>
                    </div>
                    
                    <div class="col-sm-4 col-md-4">
                        <div class="form-group" title="Control Category Search Visibility">
                            <label>Display Category Search</label> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php $category_search = array(1 => 'Enable', 0 => 'Disable'); ?>
                            <?= form_dropdown('category_search', $category_search, $pos->category_search, 'class="form-control" id="category_search" required="required"'); ?>
                        </div>
                    </div>  
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('sale_source_order_type_setting', 'sale_source_order_type_mode'); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php
                            $ssot_opts = array(
                                '0' => lang('no'),
                                '1' => lang('yes'),
                                '2' => lang('sale_source_order_type_yes_mandatory'),
                            );
                            $_ssotm_val = isset($pos->sale_source_order_type_mode) ? (string) (int) $pos->sale_source_order_type_mode : '1';
                            echo form_dropdown('sale_source_order_type_mode', $ssot_opts, $_ssotm_val, 'class="form-control" id="sale_source_order_type_mode" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <!-- <div class="col-md-4 col-sm-4 ">
                        <div class="form-group">
                            <label>Auto Email For Alert Quantity</label>
                    <?php $autoEmail = array(1 => 'Yes', 0 => 'No'); ?>
                    <?= form_dropdown('alert_qty_auto_email', $autoEmail, $pos->alert_qty_auto_email, 'class="form-control" id="alert_qty_auto_email" required="required"'); ?>
                        </div>
                    </div>
                       <div class="col-md-4 col-sm-4 <?php
                    if ($pos->alert_qty_auto_email == 0) {
                        echo 'auto_email_time';
                    }
                    ?>" >
                      <div class="form-group">
                            <label>Select Time</label>
<?= form_dropdown('auto_email_time', get_times(), $pos->auto_email_time, 'class="form-control" id="auto_email_time" required="required"'); ?>

                        </div>
                     </div>-->

                    <div class="clearfix"></div>
                    <div id="jac" class="col-md-12" style="display: none;">
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <?= lang('receipt_printer', 'rec1'); ?>
                                <?= form_input('receipt_printer', $pos->receipt_printer, 'class="form-control tip" id="rec1"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <?= lang('char_per_line', 'char_per_line'); ?>
                                <?= form_input('char_per_line', $pos->char_per_line, 'class="form-control tip" id="char_per_line" placeholder="' . lang('char_per_line') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <?= lang('cash_drawer_codes', 'cash1'); ?>
                                <?= form_input('cash_drawer_codes', $pos->cash_drawer_codes, 'class="form-control tip" id="cash1" placeholder="Hex value (x1C)"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <?= lang('pos_list_printers', 'pos_printers'); ?>
                                <?= form_input('pos_printers', $pos->pos_printers, 'class="form-control tip" id="pos_printers"'); ?>
                            </div>
                        </div>
                        <div class="well well-sm">
                            <p>Please add <strong><?= base_url() ?></strong> to your java Exception Site List under
                                Security tab.</p>

                            <p><strong>Access Java Control Panel</strong></p>
                            <pre><strong>Windows:</strong> Control Panel > (Java Icon) Java > Security tab > Exception Site List > Edit Site List > add<br><strong>Mac:</strong> System Preferences > (Java Icon) Java > Security tab > Exception Site List > Edit Site List > add</pre>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('custom_fileds') ?></legend>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_title1', 'tcf1'); ?>
                            <?= form_input('cf_title1', $pos->cf_title1, 'class="form-control tip" id="tcf1"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_value1', 'vcf1'); ?>
                            <?= form_input('cf_value1', $pos->cf_value1, 'class="form-control tip" id="vcf1"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_title2', 'tcf2'); ?>
                            <?= form_input('cf_title2', $pos->cf_title2, 'class="form-control tip" id="tcf2"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_value2', 'vcf2'); ?>
                            <?= form_input('cf_value2', $pos->cf_value2, 'class="form-control tip" id="vcf2"'); ?>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('shortcuts') ?></legend>
                    <p><?= lang('shortcut_heading') ?></p>
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('focus_add_item', 'focus_add_item'); ?>
                                <?= form_input('focus_add_item', $pos->focus_add_item, 'class="form-control tip" id="focus_add_item"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('add_manual_product', 'add_manual_product'); ?>
                                <?= form_input('add_manual_product', $pos->add_manual_product, 'class="form-control tip" id="add_manual_product"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('customer_selection', 'customer_selection'); ?>
                                <?= form_input('customer_selection', $pos->customer_selection, 'class="form-control tip" id="customer_selection"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('add_customer', 'add_customer'); ?>
                                <?= form_input('add_customer', $pos->add_customer, 'class="form-control tip" id="add_customer"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4" style="display: none;">
                            <div class="form-group">
                                <?= lang('toggle_category_slider', 'toggle_category_slider'); ?>
                                <?= form_input('toggle_category_slider', $pos->toggle_category_slider, 'class="form-control tip" id="toggle_category_slider"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4" style="display: none;">
                            <div class="form-group">
                                <?= lang('toggle_subcategory_slider', 'toggle_subcategory_slider'); ?>
                                <?= form_input('toggle_subcategory_slider', $pos->toggle_subcategory_slider, 'class="form-control tip" id="toggle_subcategory_slider"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4" style="display: none;">
                            <div class="form-group">
                                <?= lang('toggle_brands_slider', 'toggle_brands_slider'); ?>
                                <?= form_input('toggle_brands_slider', $pos->toggle_brands_slider, 'class="form-control tip" id="toggle_brands_slider"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('cancel_sale', 'cancel_sale'); ?>
                                <?= form_input('cancel_sale', $pos->cancel_sale, 'class="form-control tip" id="cancel_sale"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('suspend_sale', 'suspend_sale'); ?>
                                <?= form_input('suspend_sale', $pos->suspend_sale, 'class="form-control tip" id="suspend_sale"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4" style="display: none;">
                            <div class="form-group">
                                <?= lang('print_items_list', 'print_items_list'); ?>
                                <?= form_input('print_items_list', $pos->print_items_list, 'class="form-control tip" id="print_items_list"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('finalize_sale', 'finalize_sale'); ?>
                                <?= form_input('finalize_sale', $pos->finalize_sale, 'class="form-control tip" id="finalize_sale"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('today_sale', 'today_sale'); ?>
                                <?= form_input('today_sale', $pos->today_sale, 'class="form-control tip" id="today_sale"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('open_hold_bills', 'open_hold_bills'); ?>
                                <?= form_input('open_hold_bills', $pos->open_hold_bills, 'class="form-control tip" id="open_hold_bills"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('close_register', 'close_register'); ?>
                                <?= form_input('close_register', $pos->close_register, 'class="form-control tip" id="close_register"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('submit_and_print', 'submit_and_print'); ?>
                                <?= form_input('submit_and_print', $pos->submit_and_print, 'class="form-control tip" id="submit_and_print"  readonly="disabled"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('other', 'other'); ?>
                                <?= form_input('other', $pos->other, 'class="form-control tip" id="other"  readonly="disabled"'); ?>
                            </div>
                        </div>


                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('KOT Save', 'KOT Save'); ?>
                                <?= form_input('kot_save', $pos->kot_save, 'class="form-control tip" id="kot_save"  readonly="disabled"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('Bill Print', 'Bill Print'); ?>
                                <?= form_input('bill_print', $pos->bill_print, 'class="form-control tip" id="bill_print"  readonly="disabled"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('Suspend Popup on Checkout', 'Suspend Popup on Checkout'); ?>
                                <?= form_input('suspend_popup_checkout', $pos->suspend_popup_checkout, 'class="form-control tip" id="suspend_popup_checkout"  readonly="disabled"'); ?>
                            </div>
                        </div>
                    </div>

                </fieldset>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Payment Methods</legend>
                    <div class="pm-section-wrap">
                        <div class="pm-section-head">
                            <button type="button" class="btn btn-primary pm-btn-add" id="pm-add-btn"><i class="fa fa-plus"></i> Add Payment Method</button>
                        </div>
                        <div class="pm-methods-scroll" id="pm-methods-scroll">
                            <ul class="pm-methods-list" id="pm-methods-list">
                                <?php if (!empty($payment_methods_list)) { ?>
                                    <?php foreach ($payment_methods_list as $pm) { ?>
                                    <li class="pm-method-item" data-id="<?= (int) $pm['id'] ?>" data-pm="<?= htmlspecialchars(json_encode($pm), ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="pm-drag-handle" title="Drag to reorder"><i class="fa fa-ellipsis-v"></i></span>
                                        <div class="pm-method-icon">
                                            <?php if (!empty($pm['logo_url'])) { ?>
                                            <img src="<?= htmlspecialchars($pm['logo_url']) ?>" alt="" class="pm-method-logo">
                                            <?php } else { ?>
                                            <i class="fa <?= htmlspecialchars($pm['icon_class']) ?>"></i>
                                            <?php } ?>
                                        </div>
                                        <div class="pm-method-body">
                                            <p class="pm-method-title"><?= htmlspecialchars($pm['display_name']) ?></p>
                                            <p class="pm-method-type"><?= htmlspecialchars($pm['type_label']) ?></p>
                                            <?php if (!empty($pm['updated_label'])) { ?>
                                            <p class="pm-method-updated">Last updated: <?= htmlspecialchars($pm['updated_label']) ?></p>
                                            <?php } ?>
                                        </div>
                                        <div class="pm-method-status">
                                            <?php if ($pm['is_active']) { ?>
                                            <span class="pm-status-badge pm-status-active"><span class="pm-status-dot"></span> Active</span>
                                            <?php } else { ?>
                                            <span class="pm-status-badge pm-status-inactive"><span class="pm-status-dot"></span> Inactive</span>
                                            <?php } ?>
                                        </div>
                                        <div class="pm-method-default">
                                            <?php if ($pm['is_default']) { ?>
                                            <span class="pm-default-badge" title="Default payment method">
                                                <i class="fa fa-check-circle"></i> Default
                                            </span>
                                            <?php } ?>
                                        </div>
                                        <div class="pm-method-actions">
                                            <button type="button" class="pm-action-btn pm-action-edit" title="Edit"><i class="fa fa-pencil"></i></button>
                                            <button type="button" class="pm-action-btn pm-action-delete" title="Delete"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </li>
                                    <?php } ?>
                                <?php } else { ?>
                                    <li class="pm-empty" id="pm-empty-msg">No payment methods yet. Click <strong>Add Payment Method</strong> to create one.</li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="pm-info-bar"><i class="fa fa-info-circle"></i> Drag and drop to reorder payment methods. The first active method will be shown first at checkout.</div>
                    </div>

                <div class="modal fade" id="pmMethodModal" tabindex="-1" role="dialog" aria-labelledby="pmMethodModalLabel" data-backdrop="static">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="pmMethodModalLabel">Add Payment Method</h4>
                            </div>
                            <div class="modal-body">
                                <div id="pm-method-form" class="pm-method-form">
                                <input type="hidden" id="pm-form-id" value="">
                                <div class="form-group">
                                    <label for="pm-form-name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pm-form-name" placeholder="Enter payment method name" autocomplete="off">
                                    <p class="help-block text-danger" id="pm-form-name-error" style="display:none;margin:4px 0 0;"></p>
                                </div>
                                <div class="form-group">
                                    <label for="pm-form-type">Type</label>
                                    <select class="form-control" id="pm-form-type" autocomplete="off">
                                        <option value="payment_method" selected="selected">Payment Method</option>
                                        <option value="payment_gateway">Payment Gateway</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="pm-form-logo">Logo</label>
                                    <input type="file" class="form-control" id="pm-form-logo" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,image/svg+xml">
                                    <p class="help-block" style="margin:4px 0 0;">Upload payment method logo (PNG, JPG, GIF, WEBP, SVG).</p>
                                    <div class="pm-logo-preview-wrap" id="pm-logo-preview-wrap" style="display:none;">
                                        <img src="" alt="Logo preview" id="pm-logo-preview">
                                    </div>
                                </div>
                                <div class="pm-gateway-box" id="pm-gateway-fields">
                                    <h5>Payment Gateway Details</h5>
                                    <div class="form-group">
                                        <label for="pm-form-merchant-id">Merchant ID</label>
                                        <input type="text" class="form-control" id="pm-form-merchant-id" placeholder="Enter merchant ID">
                                    </div>
                                    <div class="form-group">
                                        <label for="pm-form-api-path">API Path</label>
                                        <input type="text" class="form-control" id="pm-form-api-path" placeholder="Enter API path (e.g. https://api.example.com)">
                                    </div>
                                    <div class="form-group">
                                        <label for="pm-form-api-key">API Key</label>
                                        <input type="text" class="form-control" id="pm-form-api-key" placeholder="Enter API key">
                                    </div>
                                    <div class="form-group">
                                        <label for="pm-form-secret-key">Secret Key</label>
                                        <input type="text" class="form-control" id="pm-form-secret-key" placeholder="Enter secret key">
                                    </div>
                                    <div class="form-group">
                                        <label for="pm-form-access-code">Access Code</label>
                                        <input type="text" class="form-control" id="pm-form-access-code" placeholder="Enter access code">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="pm-status-radios" style="padding-top:6px;">
                                        <label class="radio-inline" style="margin-right:18px;">
                                            <input type="radio" class="skip pm-form-status-radio" name="pm_form_is_active" value="1"> Active
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" class="skip pm-form-status-radio" name="pm_form_is_active" value="0"> Inactive
                                        </label>
                                    </div>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="pm-form-is-default" value="1"> Set as default payment method
                                    </label>
                                    <p class="help-block" style="margin:4px 0 0;">Only one payment method can be set as default.</p>
                                    <p class="help-block text-danger" id="pm-form-default-error" style="display:none;margin:4px 0 0;"></p>
                                </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="pm-form-save">Save Payment Method</button>
                            </div>
                        </div>
                    </div>
                </div>
                </fieldset>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Eshop Setting</legend>
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <b><?= lang('Eshop Warehouse'); ?></b>
                                <?php
                                $wh = array();
                                $wh[0] = "";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                $_warehouse = '';
                                if (isset($_POST['warehouse'])) {
                                    $_warehouse = $_POST['warehouse'];
                                }
                                echo form_dropdown('default_eshop_warehouse', $wh, $pos->default_eshop_warehouse, 'class="form-control" id="default_eshop_warehouse" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <b><?= lang('Eshop Payment System'); ?></b>
                                <?php
                                $es_pay = array();
                                $es_pay[''] = "";
                                if (!empty($pos->instamojo)) {
                                    $es_pay['instamojo'] = "Instamojo";
                                }
                                if (!empty($pos->ccavenue)) {
                                    $es_pay['ccavenue'] = "CCavenue";
                                }
                                if (empty($pos->paypal_pro)) {
                                    $es_pay['paypal_pro'] = "Paypal_pro";
                                }
                                if (!empty($pos->payumoney)) {
                                    $es_pay['payumoney'] = "Payumoney";
                                }
                                if (!empty($pos->paynear)) {
                                    $es_pay['paynear'] = "Paynear";
                                }
                                if (empty($pos->stripe)) {
                                    $es_pay['stripe'] = "Stripe";
                                }
                                if (!empty($pos->authorize)) {
                                    $es_pay['authorize'] = "Authorize.net";
                                }
                                echo form_dropdown('default_eshop_pay', $es_pay, $pos->default_eshop_pay, 'class="form-control" id="default_eshop_pay"   style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <b> <?= lang('Allow COD Option'); ?></b>
                                <?= form_dropdown('eshop_cod', $yn, $pos->eshop_cod, 'class="form-control" id="eshop_cod" required="required"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("order_tax", "sltax2"); ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('eshop_order_tax', $tr, (isset($_POST['eshop_order_tax']) ? $_POST['eshop_order_tax'] : $pos->eshop_order_tax), 'id="eshop_order_tax" data-placeholder="' . lang("select") . ' ' . lang("order_tax") . '" class="form-contro" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Default Eshop Theme *</label>
                                <?php
                                $eshopThems = array('T1' => 'Default', 'T2' => 'Green & Red', 'T3' => 'New Theme');

                                echo form_dropdown('default_eshop_theame', $eshopThems, (isset($_POST['default_eshop_theame']) ? $_POST['default_eshop_theame'] : $pos->default_eshop_theame), 'id="default_eshop_theame" data-placeholder="' . lang("select") . ' " class="form-contro" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Free delivery on minimum order (<span id="currency_symbol">Rs.</span>)* </label>
                                <?= form_input('eshop_free_delivery_on_order', $pos->eshop_free_delivery_on_order, 'class="form-control tip" id="eshop_free_delivery_on_order"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang('Eshop_biller', 'eshop_biller'); ?>
                                <?php
                                foreach ($billers as $biller) {
                                    $ebl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                if (isset($_POST['default_eshop_biller'])) {
                                    $biller = $_POST['biller'];
                                } else {
                                    $biller = "";
                                }
                                echo form_dropdown('default_eshop_biller', $ebl, $pos->default_eshop_biller, 'class="form-control" id="default_eshop_biller" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("Eshop_Overselling", "Overselling"); ?>
                                <?php
                                $osel = array('0' => 'No', '1' => 'Yes');
                                echo form_dropdown('eshop_overselling', $osel, (isset($_POST['eshop_overselling']) ? $_POST['eshop_overselling'] : $pos->eshop_overselling), 'id="eshop_overselling" class="form-contro" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Customer Display</legend>
                    <div class="row">
                        <?php
                        for ($b = 1; $b <= 5; $b++) {
                            ?>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php
                                    $bl = "pos2_banner_" . $b;
                                    if (!empty($pos->$bl) && @getimagesize($pos->$bl)) {

                                        echo '<label><i class="fa fa-image text-success"></i> Custom Banner Image ' . $b . ' <span><a class="text-danger" href="' . base_url('pos/deleteimage/' . $bl) . '"><i class="fa fa-remove"></i> Delete</a></span></label>';
                                        echo '<img src="' . base_url($pos->$bl) . '" style="height: 177px; width: 100%;" class="img img-thumbnail img-responsive " alt="' . $pos->$bl . '">';
                                    } else {

                                        echo '<label><i class="fa fa-image text-success"></i> Custom Banner Image ' . $b . '<br/><small class="text-primary">(Minimum image size: 1600 x 500 pixcel)</small></label>';
                                        echo '<h1 class="upload-image"><label for="' . $bl . '"><i class="fa fa-cloud-upload"></i><br/><small id="' . $bl . '_selectedfile">Upload Image</small></label></h1>';
                                        echo form_upload("banner_image[$b]", (isset($_POST["banner_image[$b]"]) && !empty($_POST["banner_image[$b]"]) ? $_POST["banner_image[$b]"] : ($pos ? $pos->$bl : '')), 'class="form-control cloud_upload" style="display:none;" id="' . $bl . '"');
                                    }
                                    ?>
                                <span id="html_msg"></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </fieldset>
                <?= form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary"'); ?>

                <?= form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(e) {

    $('.cloud_upload').on('change', function() {
        var ID = this.id;

        $('#' + ID + '_selectedfile').html('Image: ' + this.value);
    });


    $('.auto_email_time').hide();
    <?php if ($_GET["pos_setting_change"] == 1): ?>
    localStorage.setItem('poscustomer', '<?php echo $pos->default_customer; ?>');
    <?php endif; ?>
    //        $('#pos_setting').bootstrapValidator({
    //            feedbackIcons: {
    //                valid: 'fa fa-check',
    //                invalid: 'fa fa-times',
    //                validating: 'fa fa-refresh'
    //            }, excluded: [':disabled']
    //        });
    $('select.select').select2({
        minimumResultsForSearch: 7
    });
    fields = $('.form-control');
    $.each(fields, function() {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');

        var iid = '#' + id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            // $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function() {
                $('#pos_setting').bootstrapValidator('revalidateField', iname);
            });
        }
    });
    $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });

    $('#customer1').val('<?= $pos->default_customer; ?>').select2({
        minimumInputLength: 1,
        data: [],
        initSelection: function(element, callback) {
            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "customers/getCustomer/" + $(element).val(),
                dataType: "json",
                success: function(data) {
                    callback(data[0]);
                }
            });
        },
        ajax: {
            url: site.base_url + "customers/suggestions",
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

    $('#enable_java_applet').change(function() {
        var ja = $(this).val();
        if (ja == 1) {
            $('#jac').slideDown();
        } else {
            $('#jac').slideUp();
        }
    });
    var ja = '<?= $pos->java_applet ?>';
    if (ja == 1) {
        $('#jac').slideDown();
    } else {
        $('#jac').slideUp();
    }
    $('.auto_email_time').hide();
    $('#alert_qty_auto_email').change(function() {
        var email = $(this).val();
        if (email == 1) {
            $('.auto_email_time').show();
        } else {
            $('.auto_email_time').hide();
        }
    })

});

function changeText1() {
    var setEmail = '<?= $this->pos_settings->alert_qty_auto_email ?>';
    var setTime = '<?php echo $this->pos_settings->auto_email_time ?>';

    // var m = (d.getMinutes()<10?'0':'') + d.getMinutes() ;var d = new Date();
    // var h = d.getHours();
    var d = new Date($.now());
    var currtime = d.getHours() + ":" + d.getMinutes();
    if (String(setTime) == String(currtime)) {
        $.ajax({
            type: "get",
            async: false,
            url: "<?= base_url('pos/SendAutoEmail') ?>",
            success: function(data) {
                console.log('success');
                // alert('success' + currtime +'$$$' +  setTime);
                /* if(data) {
                 alert('success');
                 }
                 else{
                 alert('failed');
                 }*/
            },
            error: function() {
                console.log('error');
            },
        });
    }
}


setInterval(changeText1, 30000);
changeText1();



$('#print_all_category').change(function() {
    var status = $(this).val();
    if (status == '0') {
        $('#orderPrintCategorys').show();
    } else {
        $('#orderPrintCategorys').hide();
    }

});

$('#order_receipt_print').change(function() {
    var orderReceipt = $(this).val();

    if (orderReceipt == '1') {
        $('#PrintAllCategoryBlock').show();
        var status = $('#print_all_category').val();
        if (status == '0') {
            $('#orderPrintCategorys').show();
        } else {
            $('#orderPrintCategorys').hide();
        }

    } else {
        $('#PrintAllCategoryBlock').hide();
        $('#orderPrintCategorys').hide();
    }
})

$(document).ready(function() {
    var orderReceipt = $('#order_receipt_print').val();
    var status = $('#print_all_category').val();
    if (status == '0') {
        $('#orderPrintCategorys').show();
    } else {
        $('#orderPrintCategorys').hide();
    }


    if (orderReceipt == '1') {
        $('#PrintAllCategoryBlock').show();
    } else {
        $('#PrintAllCategoryBlock').hide();
        $('#orderPrintCategorys').hide();
    }

});

(function($) {
    var pmUrls = {
        save: '<?= site_url('pos/payment_method_save') ?>',
        delete: '<?= site_url('pos/payment_method_delete') ?>/',
        reorder: '<?= site_url('pos/payment_methods_reorder') ?>',
        list: '<?= site_url('pos/payment_methods_list_json') ?>',
    };
    var pmCsrfName = '<?= $this->security->get_csrf_token_name() ?>';
    var pmCsrfHash = '<?= $this->security->get_csrf_hash() ?>';

    function pmWithCsrf(data) {
        var payload = data || {};
        payload[pmCsrfName] = pmCsrfHash;
        return payload;
    }

    function pmRefreshCsrf(res) {
        if (res && res[pmCsrfName]) {
            pmCsrfHash = res[pmCsrfName];
        }
    }

    function pmEscapeHtml(text) {
        return $('<div/>').text(text || '').html();
    }

    function pmMethodIconHtml(pm) {
        if (pm.logo_url) {
            return '<div class="pm-method-icon"><img src="' + pmEscapeHtml(pm.logo_url) + '" alt="" class="pm-method-logo"></div>';
        }
        return '<div class="pm-method-icon"><i class="fa ' + pmEscapeHtml(pm.icon_class) + '"></i></div>';
    }

    function pmSetLogoPreview(url) {
        if (url) {
            $('#pm-logo-preview').attr('src', url);
            $('#pm-logo-preview-wrap').show();
        } else {
            $('#pm-logo-preview').attr('src', '');
            $('#pm-logo-preview-wrap').hide();
        }
    }

    function pmModalFields() {
        return $('#pmMethodModal');
    }

    function pmSetFormType(type) {
        var $sel = pmModalFields().find('#pm-form-type');
        type = type || 'payment_method';
        $sel.find('option').prop('selected', false);
        $sel.find('option[value="' + type + '"]').first().prop('selected', true);
        if ($sel.val() !== type) {
            $sel.prop('selectedIndex', 0);
        }
        $sel.trigger('change');
    }

    function pmToggleGatewayFields() {
        var $gw = pmModalFields().find('#pm-gateway-fields');
        $gw.stop(true, true);
        if (pmModalFields().find('#pm-form-type').val() === 'payment_gateway') {
            $gw.show();
        } else {
            $gw.hide();
        }
    }

    function pmSetDefaultCheckbox(checked) {
        var $cb = pmModalFields().find('#pm-form-is-default');
        if (typeof $.fn.iCheck !== 'undefined' && $cb.parent().hasClass('icheckbox_square-blue')) {
            $cb.iCheck(checked ? 'check' : 'uncheck');
        } else {
            $cb.prop('checked', !!checked);
        }
    }

    function pmIsDefaultChecked() {
        var $cb = pmModalFields().find('#pm-form-is-default');
        if (typeof $.fn.iCheck !== 'undefined' && $cb.parent().hasClass('icheckbox_square-blue')) {
            return $cb.parent().hasClass('checked');
        }
        return $cb.is(':checked');
    }

    function pmIsActive(pm) {
        return parseInt(pm && pm.is_active, 10) === 1;
    }

    function pmSetEditStatus(val) {
        val = (val === '1' || val === 1) ? '1' : '0';
        var $radios = $('#pmMethodModal').find('input.pm-form-status-radio');
        $radios.prop('checked', false);
        $radios.filter('[value="' + val + '"]').prop('checked', true);
    }

    function pmGetEditStatusVal() {
        var v = $('#pmMethodModal').find('input.pm-form-status-radio:checked').val();
        return v === '0' ? '0' : '1';
    }

    function pmIsDefault(pm) {
        if (!pm) {
            return false;
        }
        var val = pm.is_default;
        if (val === undefined || val === null) {
            val = pm.isDefault;
        }
        return parseInt(val, 10) === 1;
    }

    function pmLockedSystemNames() {
        return ['award point', 'deposit', 'gift card'];
    }

    function pmIsLockedSystemMethod(pm) {
        if (!pm || !pm.display_name) {
            return false;
        }
        var name = String(pm.display_name).toLowerCase().trim();
        return pmLockedSystemNames().indexOf(name) !== -1;
    }

    function pmSetNameTypeLocked(locked) {
        pmModalFields().find('#pm-form-name, #pm-form-type').prop('disabled', !!locked);
    }

    function pmResetForm() {
        var $m = pmModalFields();
        $m.find('#pm-form-id').val('');
        $m.find('#pm-form-name').val('');
        $m.find('#pm-form-merchant-id, #pm-form-api-path, #pm-form-api-key, #pm-form-secret-key, #pm-form-access-code').val('');
        $m.find('#pm-form-logo').val('');
        pmSetFormType('payment_method');
        pmSetEditStatus('1');
        pmSetDefaultCheckbox(false);
        pmSetLogoPreview('');
        $m.find('#pm-gateway-fields').stop(true, true).hide();
        pmClearFormErrors();
        pmSetNameTypeLocked(false);
        $m.removeData('pmWasDefault');
    }

    function pmShowNameError(msg) {
        var $err = pmModalFields().find('#pm-form-name-error');
        $err.text(msg || '').toggle(!!msg);
        pmModalFields().find('#pm-form-name').closest('.form-group').toggleClass('has-error', !!msg);
    }

    function pmClearNameError() {
        pmShowNameError('');
    }

    function pmShowDefaultError(msg) {
        var $err = pmModalFields().find('#pm-form-default-error');
        $err.text(msg || '').toggle(!!msg);
    }

    function pmClearDefaultError() {
        pmShowDefaultError('');
    }

    function pmClearFormErrors() {
        pmClearNameError();
        pmClearDefaultError();
    }

    function pmAlert(msg, callback) {
        bootbox.alert(msg, function() {
            pmRestoreModalScrollLock();
            if (typeof callback === 'function') {
                callback();
            }
        });
    }

    function pmRestoreModalScrollLock() {
        setTimeout(function() {
            var $modal = $('#pmMethodModal');
            if (!$modal.length || !$modal.hasClass('in')) {
                return;
            }
            $('body').addClass('modal-open');
            if ($('.modal-backdrop').length < 1) {
                $('<div class="modal-backdrop fade in"></div>').appendTo('body');
            }
        }, 50);
    }

    function pmEnsureModalInBody() {
        var $modal = $('#pmMethodModal');
        if ($modal.length && !$modal.parent().is('body')) {
            $modal.appendTo('body');
        }
    }

    function pmGetRowData($item) {
        var d = $item.data('pm');
        if (d) {
            return d;
        }
        try {
            return JSON.parse($item.attr('data-pm'));
        } catch (e) {
            return null;
        }
    }

    function pmOpenModal(editData) {
        pmEnsureModalInBody();
        var $m = pmModalFields();
        if (editData) {
            pmClearFormErrors();
            $m.find('#pmMethodModalLabel').text('Edit Payment Method');
            $m.find('#pm-form-id').val(editData.id);
            $m.find('#pm-form-name').val(editData.display_name || '');
            pmSetFormType(editData.type || 'payment_method');
            $m.find('#pm-form-merchant-id').val(editData.merchant_id || '');
            $m.find('#pm-form-api-path').val(editData.api_path || '');
            $m.find('#pm-form-api-key').val(editData.api_key || '');
            $m.find('#pm-form-secret-key').val(editData.secret_key || '');
            $m.find('#pm-form-access-code').val(editData.access_code || '');
            $m.find('#pm-form-logo').val('');
            pmSetDefaultCheckbox(parseInt(editData.is_default, 10) === 1);
            $m.data('pmWasDefault', parseInt(editData.is_default, 10) === 1);
            pmSetLogoPreview(editData.logo_url || '');
            pmToggleGatewayFields();
            pmSetEditStatus(parseInt(editData.is_active, 10) === 1 ? '1' : '0');
            pmSetNameTypeLocked(pmIsLockedSystemMethod(editData));
        } else {
            $m.data('pmOpenMode', 'add');
            pmResetForm();
            $m.find('#pmMethodModalLabel').text('Add Payment Method');
        }
        $m.modal({ backdrop: 'static', keyboard: true, show: true });
    }


    function pmBuildRow(pm) {
        var activeBadge = pmIsActive(pm)
            ? '<span class="pm-status-badge pm-status-active"><span class="pm-status-dot"></span> Active</span>'
            : '<span class="pm-status-badge pm-status-inactive"><span class="pm-status-dot"></span> Inactive</span>';
        var defaultHtml = pmIsDefault(pm)
            ? '<span class="pm-default-badge" title="Default payment method"><i class="fa fa-check-circle"></i> Default</span>'
            : '';
        var updated = pm.updated_label
            ? '<p class="pm-method-updated">Last updated: ' + pmEscapeHtml(pm.updated_label) + '</p>'
            : '';
        var $li = $('<li class="pm-method-item"></li>').attr('data-id', pm.id);
        $li.attr('data-pm', JSON.stringify(pm));
        $li.data('pm', pm);
        $li.append(
            '<span class="pm-drag-handle" title="Drag to reorder"><i class="fa fa-ellipsis-v"></i></span>' +
            pmMethodIconHtml(pm) +
            '<div class="pm-method-body">' +
            '<p class="pm-method-title">' + pmEscapeHtml(pm.display_name) + '</p>' +
            '<p class="pm-method-type">' + pmEscapeHtml(pm.type_label) + '</p>' + updated +
            '</div>' +
            '<div class="pm-method-status">' + activeBadge + '</div>' +
            '<div class="pm-method-default">' + defaultHtml + '</div>' +
            '<div class="pm-method-actions">' +
            '<button type="button" class="pm-action-btn pm-action-edit" title="Edit"><i class="fa fa-pencil"></i></button>' +
            '<button type="button" class="pm-action-btn pm-action-delete" title="Delete"><i class="fa fa-trash"></i></button>' +
            '</div>'
        );
        return $li;
    }

    function pmReloadList() {
        $.getJSON(pmUrls.list, function(r) {
            if (r.status === 'success') {
                pmRenderList(r.data);
            }
        });
    }

    function pmRenderList(list) {
        var $ul = $('#pm-methods-list');
        $ul.empty();
        if (!list || !list.length) {
            $ul.append('<li class="pm-empty" id="pm-empty-msg">No payment methods yet. Click <strong>Add Payment Method</strong> to create one.</li>');
            return;
        }
        $.each(list, function(i, pm) {
            $ul.append(pmBuildRow(pm));
        });
        pmBootSortable();
    }

    function pmInitSortable() {
        if (typeof $.fn.sortable !== 'function') {
            return false;
        }
        var $list = $('#pm-methods-list');
        var $scroll = $('#pm-methods-scroll');
        if ($list.hasClass('ui-sortable')) {
            $list.sortable('destroy');
        }
        if ($list.find('.pm-method-item').length < 1) {
            return true;
        }
        $list.sortable({
            handle: '.pm-drag-handle',
            items: '> .pm-method-item',
            axis: 'y',
            containment: $scroll.length ? $scroll : 'parent',
            scroll: true,
            scrollSensitivity: 60,
            scrollSpeed: 20,
            tolerance: 'pointer',
            cursor: 'move',
            forcePlaceholderSize: true,
            placeholder: 'pm-method-item pm-sortable-placeholder',
            helper: function(e, $item) {
                var $helper = $item.clone();
                $helper.css({ width: $item.outerWidth() });
                return $helper;
            },
            start: function(e, ui) {
                ui.placeholder.height(ui.item.outerHeight());
            },
            update: function() {
                var order = [];
                $list.find('.pm-method-item').each(function() {
                    order.push(parseInt($(this).attr('data-id'), 10));
                });
                $.ajax({
                    url: pmUrls.reorder,
                    type: 'POST',
                    dataType: 'json',
                    data: pmWithCsrf({ order: order }),
                    success: function(res) {
                        pmRefreshCsrf(res);
                        if (res.status === 'success') {
                            pmReloadList();
                        } else {
                            bootbox.alert(res.message || 'Failed to save payment method order.');
                        }
                    },
                    error: function() {
                        bootbox.alert('Failed to save payment method order.');
                    }
                });
            }
        });
        return true;
    }

    function pmBootSortable(attempts) {
        attempts = attempts || 0;
        if (pmInitSortable()) {
            return;
        }
        if (attempts < 40) {
            setTimeout(function() {
                pmBootSortable(attempts + 1);
            }, 100);
        }
    }

    $('#pm-add-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        pmOpenModal(null);
        return false;
    });

    $(document).on('change', '#pmMethodModal #pm-form-type', pmToggleGatewayFields);

    $(document).on('input', '#pm-form-name', pmClearNameError);

    function pmValidateDefaultInactive() {
        if (pmIsDefaultChecked() && pmGetEditStatusVal() === '0') {
            pmShowDefaultError('Default payment method cannot be inactive. Uncheck default or set status to Active.');
            pmSetEditStatus('1');
            return false;
        }
        return true;
    }

    $(document).on('change', '#pmMethodModal input.pm-form-status-radio', function() {
        if ($(this).val() === '0' && pmIsDefaultChecked()) {
            pmValidateDefaultInactive();
        } else if (pmGetEditStatusVal() === '1' || !pmIsDefaultChecked()) {
            pmClearDefaultError();
        }
    });

    $(document).on('change ifChecked', '#pmMethodModal #pm-form-is-default', function() {
        if (pmIsDefaultChecked() && pmGetEditStatusVal() === '0') {
            pmValidateDefaultInactive();
        } else if (!pmIsDefaultChecked()) {
            pmClearDefaultError();
        }
    });

    $('#pm-form-logo').on('change', function() {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            pmSetLogoPreview('');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            pmSetLogoPreview(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    function pmPaymentMethodNameExists(name, excludeId) {
        var normalized = $.trim(name || '').toLowerCase();
        if (!normalized) {
            return false;
        }
        excludeId = parseInt(excludeId, 10) || 0;
        var exists = false;
        $('#pm-methods-list .pm-method-item').each(function () {
            var pm = pmGetRowData($(this));
            if (!pm || !pm.display_name) {
                return;
            }
            if (excludeId > 0 && parseInt(pm.id, 10) === excludeId) {
                return;
            }
            if (String(pm.display_name).toLowerCase() === normalized) {
                exists = true;
                return false;
            }
        });
        return exists;
    }

    $('#pm-form-save').on('click', function() {
        var $btn = $(this).prop('disabled', true);
        var editId = parseInt($('#pm-form-id').val(), 10) || 0;
        var displayName = $.trim($('#pm-form-name').val());
        pmClearFormErrors();
        if (!displayName) {
            pmShowNameError('Please enter payment method name.');
            $btn.prop('disabled', false);
            pmModalFields().find('#pm-form-name').focus();
            pmRestoreModalScrollLock();
            return;
        }
        if (pmPaymentMethodNameExists(displayName, editId)) {
            pmShowNameError('Payment method already exist.');
            $btn.prop('disabled', false);
            pmModalFields().find('#pm-form-name').focus();
            pmRestoreModalScrollLock();
            return;
        }
        if (editId > 0 && pmModalFields().data('pmWasDefault') && !pmIsDefaultChecked()) {
            pmShowDefaultError('At least one payment method must be set as default.');
            $btn.prop('disabled', false);
            pmRestoreModalScrollLock();
            return;
        }
        if (pmIsDefaultChecked() && pmGetEditStatusVal() === '0') {
            pmShowDefaultError('Default payment method cannot be inactive. Uncheck default or set status to Active.');
            $btn.prop('disabled', false);
            pmRestoreModalScrollLock();
            return;
        }
        var formData = new FormData();
        formData.append('id', $('#pm-form-id').val());
        formData.append('display_name', displayName);
        formData.append('type', pmModalFields().find('#pm-form-type').val());
        formData.append('merchant_id', $('#pm-form-merchant-id').val());
        formData.append('api_path', $('#pm-form-api-path').val());
        formData.append('api_key', $('#pm-form-api-key').val());
        formData.append('secret_key', $('#pm-form-secret-key').val());
        formData.append('access_code', $('#pm-form-access-code').val());
        formData.append('is_active', pmGetEditStatusVal());
        formData.append('is_default', pmIsDefaultChecked() ? 1 : 0);
        formData.append(pmCsrfName, pmCsrfHash);
        var logoFile = $('#pm-form-logo')[0].files[0];
        if (logoFile) {
            formData.append('logo', logoFile);
        }
        $.ajax({
            url: pmUrls.save,
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $btn.prop('disabled', false);
                pmRefreshCsrf(res);
                if (res.status === 'success') {
                    pmResetForm();
                    pmModalFields().removeData('pmOpenMode');
                    $('#pmMethodModal').modal('hide');
                    pmReloadList();
                } else {
                    pmAlert(res.message || 'Could not save payment method.');
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                pmAlert('Could not save payment method.');
            }
        });
    });

    $(document).on('click', '.pm-action-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var data = pmGetRowData($(this).closest('.pm-method-item'));
        if (data) {
            pmOpenModal(data);
        }
        return false;
    });

    $(document).on('click', '.pm-action-delete', function() {
        var $item = $(this).closest('.pm-method-item');
        var pm = pmGetRowData($item);
        if (pm && pmIsDefault(pm)) {
            bootbox.alert('Default payment method cannot be deleted. Set another method as default first.');
            return;
        }
        var id = $item.data('id');
        bootbox.confirm('Delete this payment method?', function(ok) {
            if (!ok) {
                return;
            }
            $.ajax({
                url: pmUrls.delete + id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $item.remove();
                        if (!$('#pm-methods-list .pm-method-item').length) {
                            $('#pm-methods-list').html('<li class="pm-empty" id="pm-empty-msg">No payment methods yet. Click <strong>Add Payment Method</strong> to create one.</li>');
                        }
                        pmBootSortable();
                    } else {
                        bootbox.alert(res.message || 'Could not delete.');
                    }
                }
            });
        });
    });

    function pmParseRowData() {
        $('#pm-methods-list .pm-method-item').each(function() {
            var $el = $(this);
            if (!$el.data('pm') && $el.attr('data-pm')) {
                try {
                    $el.data('pm', JSON.parse($el.attr('data-pm')));
                } catch (e) {}
            }
        });
    }

    $(function() {
        pmEnsureModalInBody();
        pmParseRowData();
        pmBootSortable();
        $('#pmMethodModal').on('shown.bs.modal', function() {
            var $m = pmModalFields();
            if ($m.data('pmOpenMode') === 'add') {
                pmResetForm();
            }
        });
        $('#pmMethodModal').on('hidden.bs.modal', function() {
            $('.bootbox.modal').modal('hide');
            pmResetForm();
            pmModalFields().removeData('pmOpenMode');
        });
    });
})(jQuery);
</script>