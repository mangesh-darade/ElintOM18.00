<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Update Store'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php if (validation_errors()) { ?>
                    <div class="alert alert-danger" id="errormsg">
                        <button type="button" class="close fa-2x" id="msgclose">&times;</button>
                        <?= validation_errors() ?>            
                    </div>
                    <?php
                }
                if ($this->session->flashdata('success')) {
                    ?>
                    <div class="alert alert-success" id="errormsg">
                        <button type="button" class="close fa-2x" id="msgclose">&times;</button>
                        <?= $this->session->flashdata('success') ?>            
                    </div>
                <?php } ?>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'StoreForm'); //
                echo form_open("urban_piper/update_store", $attrib);
                ?>
                <input type="hidden" name="store_id" value="<?= $store_info->id ?>" >
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="name"> Store Name</label>
                            <div class="controls">
                                <input type="text" required="required" class="form-control" name="name" maxlength="100" required="true" readonly="readonly" placeholder="Store Name" value="<?= $store_info->name ?>" id="name" />
                                <span class="text-danger errormsg" id="name_err"></span>
                            </div>
                        </div> 
                    </div>    
                </div>  
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="address"><?= lang("Store Address"); ?></label>
                            <div class="controls">
                                <input type="text" readonly="readonly" required="required" maxlength="250" class="form-control" placeholder="Store Address" name="address" id="address" value="<?= $store_info->address ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="warehouse">Assign Warehouse</label>
                            <div class="controls"> <?php
                                foreach ($warehouses as $warehouse) {
                                    $wh[0] = '-- Select --';
                                    $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                }
                                $selected_warehouse = $postdata['warehouse'] ? $postdata['warehouse'] : $store_info->warehouse_id;
                                echo form_dropdown('warehouse', $wh, $selected_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;" disabled= "disabled"');
                                ?>
                            </div>
                        </div>
                    </div>                    
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="emial">Email</label>
                            <div class="controls">
                                <input type="email" class="form-control" name="email" value="<?= $store_info->email ?>" placeholder="Email Address" required="required"  />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="contact_phone"><?= lang("Mobile No"); ?> </label>
                            <div class="controls"> 
                                <input type="hidden" name="code" id="code" >
                                <input type="text" maxlength="10" class="form-control" name="contact_phone" value="<?= $store_info->contact_phone ?>" required="required" placeholder="Mobile No" />
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="City "><?= lang("City "); ?> *</label>
                            <div class="controls">
                                <input type="text" class="form-control" name="city" value="<?= $store_info->city ?>" required="required" placeholder="City" id="city" > 
                                <span class="text-danger errormsg" id="city_err"></span>								
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="zip_code"><?= lang("Zip Code"); ?></label>
                            <div class="controls">
                                <input type="text" required="required" class="form-control" value="<?= $store_info->zip_codes ?>" name="zip_code" value="<?php echo set_value('zip_code'); ?>"  placeholder="Zip code " id="zip_code" >
                                <span class="text-danger">Note: Add notification zip code multiple's using ", " separated</span>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="notifi_email"><?= lang("Notification Emails Address"); ?></label>
                            <div class="controls">
                                <input type="text" class="form-control" required="required" value="<?= $store_info->notification_emails ?>" name="notification_emails" placeholder="Notification Emails" id="notifi_email" >
                                <span class="text-danger">Note: Add notification email address multiple's using ", " separated</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="notifi_phone"><?= lang("Notification Mobile No"); ?></label>
                            <div class="controls"> 
                                <input type="text" inputmode="numeric" oninput="formatNumber(event)" required="required" class="form-control" value="<?= $store_info->notification_phones ?>" name="notification_phones" placeholder="Notification Mobile No" id="notifi_phone" />
                                <span class="text-danger">Note: Add notification mobile no multiple's using ", " separated</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="min_pickup_time"><?= lang("Min Pickup Time"); ?> (In Minutes)</label>
                            <div class="controls">
                                <select name="min_pickup_time" class="form-control" id="min_pickup_time" >
                                    <?php
                                    for ($iMinPickupTime = 10; $iMinPickupTime <= 60; $iMinPickupTime += 5) {
                                        $iMinPickupTimeValue = $iMinPickupTime * 60;
                                        ?>
                                        <option value="<?php echo $iMinPickupTimeValue; ?>" <?php
                                        if ($store_info->min_pickup_time && $store_info->min_pickup_time == $iMinPickupTimeValue) {
                                            echo 'selected';
                                        } else {
                                            if ($iMinPickupTimeValue == 900)
                                                echo 'selected';
                                        }
                                        ?>> <?php echo $iMinPickupTime; ?> </option>
                                            <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="min_delivery_time"><?= lang("Min Delivery Time"); ?> (In Minutes)</label>
                            <div class="controls"> 
                                <select name="min_delivery_time" class="form-control" id="min_delivery_time" >
                                    <?php
                                    for ($iMinDeliveryTime = 10; $iMinDeliveryTime <= 60; $iMinDeliveryTime += 5) {
                                        $iMinDeliveryTimeValue = $iMinDeliveryTime * 60;
                                        ?>
                                        <option value="<?php echo $iMinDeliveryTimeValue; ?>" 
                                        <?php
                                        if ($store_info->min_delivery_time && $store_info->min_delivery_time == $iMinDeliveryTimeValue) {
                                            echo 'selected';
                                        } else {
                                            if ($iMinDeliveryTimeValue == 1800)
                                                echo 'selected';
                                        }
                                        ?> > <?php echo $iMinDeliveryTime; ?> </option>
                                            <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="min_order_value"><?= lang("Min Order Value"); ?> </label>
                            <div class="controls">
                                <input type="text" maxlength="4" value="<?= $store_info->min_order_value ?>" min="0" name="min_order_value" class="form-control" id="min_order_value"  onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="ordering_enabled"><?= lang("Receive Orders"); ?></label>
                            <div class="controls"> 
                                <select name="ordering_enabled" class="form-control" >
                                    <option value="true" <?= ($store_info->ordering_enabled == 'true') ? 'Selected' : '' ?>> Enabled </option>
                                    <option value="false" <?= ($store_info->ordering_enabled == 'false') ? 'Selected' : '' ?>> Disable </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="geo_longitude"><?= lang("Longitude"); ?></label>
                            <div class="controls">
                                <input type="text" class="form-control"  name="geo_longitude" placeholder="Longitude" value="<?= $store_info->geo_longitude ?>"   id="geo_longitude" >
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="geo_latitude"><?= lang("Latitude"); ?></label>
                            <div class="controls"> 
                                <input type="text" class="form-control"  name="geo_latitude" placeholder="Latitude" value="<?= $store_info->geo_latitude ?>" id="geo_latitude" >
                            </div>
                        </div>
                    </div>
                </div>
         
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="excluded_platforms"><?= lang("Excluded_Platforms"); ?></label>
                            <div class="controls"> 
                                <select class="form-control" multiple="multiple" name="excluded_platforms[]"   id="excluded_platforms" >
                                    <?php   
                                        $expdata= json_decode($store_info->excluded_platforms);
                                        foreach (UrbanpiperExcludedPlatform() as $explatform) { 
                                     ?>
                                        <option value="<?= $explatform ?>" <?= (($expdata)?(in_array( $explatform,$expdata)?'selected': ''):'') ?> >  <?= ucfirst($explatform) ?> </option>
                                    <?php } ?>
                                </select>    
                            </div>
                        </div>
                    </div>

                    <?php 
                      
                    ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label"
                                   for="included_platforms"><?= lang("Included Platforms"); ?></label>
                            <div class="controls"> 
                                <select class="form-control" multiple="multiple" name="included_platforms[] "   id="included_platforms " >
                                    <?php 
                                     $incdata= json_decode($store_info->included_platforms);
                                    $inselectdata =$incdata->incdata->included_platforms;
                                       foreach (UrbanpiperIncludedPlatform() as $inplatform) { ?>
                                        <option value="<?= $inplatform ?>"  <?= (($inselectdata)?(in_array( $inplatform,$inselectdata)?'selected': ''):'') ?> > <?= ucfirst($inplatform) ?> </option>
                                    <?php } ?>
                                </select>    
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                                                   <table class="table table-border">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Days</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    function get_times($default = '', $interval = '+30 minutes') {

                                        $output = "<option value=''>Any Time</option>";

                                        $current = strtotime('00:00:00');
                                        $end = strtotime('23:59:00');

                                        while ($current <= $end) {
                                            $time = date('H:i:s', $current);
                                            $sel = ( $time == $default ) ? ' selected' : '';

                                            $output .= "<option value=\"{$time}\"{$sel}>" . date('h.i A', $current) . '</option>';
                                            $current = strtotime($interval, $current);
                                        }

                                        return $output;
                                    }
                                    ?>
                                    <?php
                                    $DaysArr = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                                    for ($iDaysArr = 0; $iDaysArr <= 6; $iDaysArr++) {
                                        ?>
                                        <tr>
                                            <td>
                                                <?php
                                                if ($store_info->days) {
                                                    $DaysArrVal = json_decode(stripslashes($store_info->days), true);
                                                    $DayActive = '';
                                                    $StartTimeSlot = '';
                                                    $EndTimeSlot = '';
                                                    foreach ($DaysArrVal as $keyDays) {
                                                        if ($DaysArr[$iDaysArr] == $keyDays['day']) {
                                                            $DayActive = $keyDays['day'];                                                            
                                                           foreach($keyDays['slots'] as $time){
                                                                $StartTimeSlot = $time['start_time'];
                                                                $EndTimeSlot = $time['end_time'];
                                                           }
                                                           
                                                        }
                                                    }
                                                }
                                                   
                                                ?>
                                                <input class="checkbox checkdays" type="checkbox" name="Days[]" id="Days_<?php echo $DaysArr[$iDaysArr]; ?>" value="<?php echo $DaysArr[$iDaysArr]; ?>" <?php if ($DaysArr[$iDaysArr] == $DayActive) echo 'checked'; ?>/></td>
                                            <td><?php echo ucfirst($DaysArr[$iDaysArr]); ?></td>
                                            <td>
                                                <select class="form-control"  name="<?php echo $DaysArr[$iDaysArr]; ?>_start_time" id="<?php echo $DaysArr[$iDaysArr]; ?>_start_time">
                                                    <?php echo get_times($StartTimeSlot); ?>
                                                </select> 
                                                <span class="text-danger errormsg days_error_msg"  id="<?php echo $DaysArr[$iDaysArr]; ?>_start_time_err"></span>
                                            </td>
                                            <td>
                                                <select class="form-control"  name="<?php echo $DaysArr[$iDaysArr]; ?>_end_time" id="<?php echo $DaysArr[$iDaysArr]; ?>_end_time">
                                                    <?php echo get_times($EndTimeSlot); ?>
                                                </select>  
                                                <span class="text-danger errormsg days_error_msg"  id="<?php echo $DaysArr[$iDaysArr]; ?>_end_time_err"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                <?php } ?>
                            </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <input type="hidden" name="DaysTime" id="DaysTime" >
                        <button type="submit" class="btn btn-success"> Save </button> 
                        <button type="button" onclick="window.location = '<?= site_url('urban_piper/store_info') ?>'" class="btn btn-primary" > Back </button> 
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>    
</div>    

<script>

    $('#msgclose').click(function () {
        $('#errormsg').hide();
    });
            const element = document.getElementById('notifi_phone');
            const keys = ', 0123456789';
            element.addEventListener('keypress', (e) => {
            if (keys.includes(e.key) === false) {
                e.stopPropagation();
                e.preventDefault();
                }
            }
            , false);
            
            
             const pincode = document.getElementById('zip_code');
            const keys_zip = ', 0123456789';
            pincode.addEventListener('keypress', (e) => {
            if (keys_zip.includes(e.key) === false) {
                e.stopPropagation();
                e.preventDefault();
                }
            }
            , false);
            
</script>    
