<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <style>
        .select2-drop select2-drop-multi{width: 211px!important;}
        .select2-container{width: 100%!important;}
        h1.upload-image {            
            text-align: center!important;
        }

        h1.upload-image i {
            font-size: 50px !important;            
            cursor: pointer!important;           
        }
    </style>
    <?php

    function get_times($default = '', $interval = '+30 minutes') {

        $output = "<option value=\"\">Any Time</option>";

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
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Eshop_Shippings'); ?></h2>
    </div>
    <div class="box-content">
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'name' => "eshop_shippings", 'id' => "eshop_shippings");
        echo form_open_multipart("eshop_admin/shipping_methods", $attrib, ['action' => 'save_shipping'])
        ?>
        <div class="row">
            <div class="col-md-1"><label>Active</label></div>
            <div class="col-md-3"><label>Shipping Method</label></div>
            <div class="col-md-2"><label>Shipping Charges (Rs.)</label></div>
            <div class="col-md-6 text-center"><label>Shipping Time & Schedules</label></div>
        </div>

        <?php
        if (is_array($shippings)):
            foreach ($shippings as $key => $shiping) {
                ?>
                <hr/>
                <div class="row">
                    <div class="col-md-1">
                        <input type="checkbox" name="active[<?= $shiping['id'] ?>]"  id="active[<?= $shiping['id'] ?>]" value="1" <?= ($shiping['is_active'] ? 'checked' : '') ?> />
                        <input type="hidden" name="code[<?= $shiping['id'] ?>]" value="<?= $shiping['code'] ?>" />
                    </div> 
                    <div class="col-md-3"> 
                        <?= form_input('name[' . $shiping['id'] . ']', (isset($_POST['name']) && !empty($_POST['name']) ? $_POST['name'] : $shiping['name']), 'class="form-control" placeholder="' . $shiping['code'] . '" '); ?>
                        <div><small class="text-primary">Shipping Code : <?= $shiping['code'] ?></small></div>
                        <?php if (($shiping['code'] == "deliver_later" || $shiping['code'] == "pickup_later" ) && $eshop_setting->active_multi_outlets) { ?>                         
                            <div class="form-group all">
                                <label>Order Received By Outlet</label>
                                <select name="order_to_warehouse[<?= $shiping['id'] ?>]" id="order_to_warehouse_<?= $shiping['id'] ?>">
                                    <option value="">Selected Outlet By User</option>
                                    <?php
                                    if (is_array($warehouses)) {
                                        foreach ($warehouses as $whid => $warehouse) {
                                            $selected = $shiping['order_to_warehouse'] == $whid ? ' selected="selected" ' : '';
                                            echo '<option value="' . $whid . '" ' . $selected . '>' . $warehouse . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-2">         
                        <div class="form-group row">
                            <?= form_input('price[' . $shiping['id'] . ']', (isset($_POST['price'][$shiping['id']]) && !empty($_POST['price'][$shiping['id']]) ? $_POST['price'][$shiping['id']] : ($shiping ? number_format($shiping['price'], 0) : '')), 'class="form-control" placeholder="Shipping Charges" maxlength="4"'); ?>
                        </div>
                        <div class="form-group row">
                             <label>Minimum Order Amount</label>
                            <?= form_input('minimum_order_amount[' . $shiping['id'] . ']', (isset($_POST['minimum_order_amount'][$shiping['id']]) && !empty($_POST['minimum_order_amount'][$shiping['id']]) ? $_POST['minimum_order_amount'][$shiping['id']] : ($shiping ? number_format($shiping['minimum_order_amount'], 0) : '')), 'class="form-control" placeholder="Minimum Order Amount" maxlength="6"'); ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="checkbox" id="alltime[<?= $shiping['id'] ?>]" name="alltime[<?= $shiping['id'] ?>]" <?= (($shiping['all_time']) ? 'checked' : '') ?> value='1' />
                                <label for="alltime[<?= $shiping['id'] ?>]"> All Time </label>
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-sm btn-primary " onclick="add_more('<?= str_replace(" ", "", $shiping['name']) ?>', '<?= $shiping['id'] ?>')"><i class="fa fa-plus"></i></button>

                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2"></div>
                            <div class="col-md-10 text-right">
                                <?php
                                $getSlotetime = $this->eshop_model->getsloteTiming($shiping['id']);
                                if ($getSlotetime) {
                                    foreach ($getSlotetime as $value_timeslote) {
                                        ?>
                                        <div class="form-group row">
                                            <div class="col-sm-4"> 
                                                <select class="form-control"  name="<?= $shiping['id'] ?>_slots_start_time_method[<?= $shiping['id'] ?>]" >
                                                    <?php echo get_times(($value_timeslote->start_time) ? date("H:i", strtotime($value_timeslote->start_time)) : ''); ?>
                                                </select> 
                                            </div>
                                            <label class="col-sm-1"> <?= lang('To') ?>  </label>
                                            <div class="col-sm-4">
                                                <select class="form-control"  name="<?= $shiping['id'] ?>_slots_end_time_method[<?= $shiping['id'] ?>]" >
                                                    <?php echo get_times(($value_timeslote->end_time) ? date("H:i", strtotime($value_timeslote->end_time)) : ''); ?>
                                                </select>  
                                            </div>
                                            <div class="col-sm-2">
                                                <span class="btn btn-sm btn-danger"  id="DeleteShip" onclick="return deleteShiptime('<?= $value_timeslote->id ?>');"  type="button" ><i class="fa fa-times"></i></span>
                                            </div>
                                            <div class="cleafix"></div> 
                                        </div>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <div class="form-group row">
                                        <div class="col-sm-4"> 
                                            <select class="form-control"  name="<?= $shiping['id'] ?>_slots_start_time_method[<?= $shiping['id'] ?>]" >
                                                <?php echo get_times(); ?>
                                            </select> 
                                        </div>
                                        <label class="col-sm-1"> <?= lang('To') ?>  </label>
                                        <div class="col-sm-4">
                                            <select class="form-control"  name="<?= $shiping['id'] ?>_slots_end_time_method[<?= $shiping['id'] ?>]" >
                                                <?php echo get_times(); ?>
                                            </select>  
                                        </div>
                                        <div class="col-sm-1">
                                        </div>
                                        <div class="cleafix"></div>
                                    </div>
                                <?php }
                                ?>

                                <div class="<?= str_replace(" ", "", $shiping['name']) ?>"></div>
                            </div>

                        </div>
                    </div>

                </div>
                <?php
            }//end foreach
        endif;
        ?>

        <hr/>
        <div class="row">
            <div class="col-md-4">
                <label>Manage Delivery Pincode / Locations : </label>
            </div>
            <div class="col-md-4">

                <select name="delivery_pincode" onchange="checkFunction(this.value)" >
                    <option value="All Pincodes" <?= (($eshop_setting->delivery_pincode == 'All Pincodes') ? 'selected' : '') ?>>Delivery On All Pincode</option>
                    <option value="Specific Pincodes" <?= (($eshop_setting->delivery_pincode == 'Specific Pincodes') ? 'selected' : '') ?>>Delivery Only Specific Pincodes</option>
                </select> 
            </div>
        </div>
        <hr/>
        <div id="pincodeblock">  
            <div class="row" >                 
                <div class="col-md-2">
                    <label>Delivery Pincode</label>
                    <input type="text" name="pincode" min="6" maxlength="6" multiple="multiple" pattern = "[0-9]"  id="pincode" class="col-md-2 form-control" placeholder="Pincode" />
                    <span class="text-danger" id="errpincode"></span>
                </div>

                 <div class="col-md-2">
                    <label>Charges</label>
                    <input type="text" name="charges" min="6" maxlength="6" multiple="multiple" pattern = "[0-9]"  id="charges" class="col-md-2 form-control" placeholder="Charges" />
                    <span class="text-danger" id="errcharges"></span>
                </div>

                <?php if ($eshop_setting->active_multi_outlets) { ?>
                    <div class="col-md-3">
                        <label>Delivery Outlet</label>
                        <select name="warehouse" id="warehouse">
                            <option value="">Select Outlet</option>
                            <?php
                            if (is_array($warehouses)) {
                                foreach ($warehouses as $whid => $warehouse) {
                                    echo '<option value="' . $whid . '">' . $warehouse . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php } else { ?>
                <input type="hidden" name="warehouse" id="warehouse"  value="<?=$warehouse_id?>" /> 
                <?php } ?>
                <div class="col-md-5 text-center">
                    <label>Delivery Timing for Pincode</label>
                    <div class="form-group row">
                        <label class="col-sm-1"> <?= lang('From') ?> </label>
                        <div class="col-sm-5"> 
                            <select class="form-control"  name="delivery_time_from" id="delivery_time_from" >
                                <?php echo get_times(); ?>
                            </select> 
                        </div>
                        <label class="col-sm-1"> <?= lang('Till') ?></label>
                        <div class="col-sm-5">
                            <select class="form-control"  name="delivery_time_till" id="delivery_time_till" >
                                <?php echo get_times(); ?>
                            </select>  
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>Click to Add</label>
                    <button type="button" name="addpincode" id="addpincode" class="btn btn-success"> Add Pincode </button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12" id="show_pincode" style="margin-top:1em"></div>
            </div>
        </div>

        <div class="row">
            <div class="form-group text-center">
                <?php echo form_submit('send', $this->lang->line("Submit"), 'id="send" class="btn btn-primary"  style="margin-top:20px;"'); ?> 
            </div>
            <?= form_close(); ?>
        </div>



    </div>
</div>

<script>
    $(document).ready(function () {
        getPincode();

        checkFunction('<?= $eshop_setting->delivery_pincode ?>');
    });


    function checkFunction(keyvalue) {

        if (keyvalue == 'Specific Pincodes') {
            $('#pincodeblock').show();
        } else {
            $('#pincodeblock').hide();
        }
    }

    $('#addpincode').click(function () {
        $('#errpincode').html('');
        var pincode = $('#pincode').val();
        if (pincode == '') {
            $('#errpincode').html('Please Enter Pincode');
            $('#pincode').focus();
        } else if ($.isNumeric(pincode)) {
            if (pincode.length == 6) {
                actionPincode('add', pincode);
            } else {
                $('#errpincode').html('Invalid pincode no.');
                $('#pincode').focus();
            }
        } else {
            $('#errpincode').html('Invalid pincode no.');
            $('#pincode').focus();
        }

        setTimeout(function () {
            $('#errpincode').html('')
        }, 5000);

    });

    function deletepincode(pincode) {
        if (confirm("Are you sure delete " + pincode + ' pincode?')) {
            actionPincode('delete', pincode)
        }

    }

    function actionPincode(action, pincode) {

        if (action == 'add') {

            var pincode = $('#pincode').val();
            var charges = $('#charges').val();
            var warehouse = $('#warehouse').val();
            var delivery_time_from = $('#delivery_time_from').val();
            var delivery_time_till = $('#delivery_time_till').val();

            var postData = 'action=' + action;
            postData += '&pincode=' + pincode;
            postData  +='&charges='+ charges;  
            postData += '&warehouse=' + warehouse;
            postData += '&delivery_time_from=' + delivery_time_from;
            postData += '&delivery_time_till=' + delivery_time_till;

        } else {
            var postData = 'action=' + action + '&pincode=' + pincode;
        }

        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'POST',
            url: '<?= base_url("eshop_admin/actionPincode") ?>',
            data: postData,
            success: function (response) {
                if (response.status == 'success') {
                    $('#errpincode').html(response.message);
                    getPincode();
                } else {
                    $('#errpincode').html(response.message);
                }
                $('#pincode').val('');
                $('#pincode').focus();

                setTimeout(function () {
                    $('#errpincode').html('')
                }, 5000);


            }, error: function () {
                console.log('error');
            }
        });
    }

    function getPincode() {

        $.ajax({
            type: 'ajax',
            dataType: 'html',
            method: 'GET',
            url: '<?= base_url("eshop_admin/pincodes") ?>',
            success: function (response) {
                $('#show_pincode').html(response);
            }, error: function () {
                console.log('error');
            }
        });
    }


    function add_more(show_block, id) {
        var randid = "<?= time() ?>";
        var delierytime = '';
        delierytime += '<div class="form-group row" id="row_' + randid + '">';
        delierytime += '<div class="col-sm-4">';
        delierytime += '<select class="form-control"  name="' + id + '_slots_start_time_method[]" >';
        delierytime += '<?= get_times() ?>';
        delierytime += '</select>';
        delierytime += '</div>';
        delierytime += '<label class="col-sm-1"> <?= lang('To') ?>  </label>';
        delierytime += '<div class="col-sm-4">';
        delierytime += '<select class="form-control"  name="' + id + '_slots_end_time_method[]" >';
        delierytime += '<?= get_times() ?>';
        delierytime += '</select>';
        delierytime += '</div>';
        delierytime += '<div class="col-sm-3">';
        delierytime += '<span class="btn btn-sm btn-danger" onclick="removeblock(\'' + randid + '\')" id="' + randid + '"  type="button" ><i class="fa fa-times"></i></span>';
        delierytime += '</div>';
        delierytime += '</div>';
        $('.' + show_block).append(delierytime);
    }

    function removeblock(getid) {
        $('#row_' + getid).remove();
    }

    function deleteShiptime(del_id) {
        var con = confirm('Are you sure you want to delete?');
        var id = del_id;
        if (!con) {
            return false;
        }
        console.log(id);
        $.ajax({
            type: "get", async: false,
            url: "<?= site_url('eshop/deleteShippingTm') ?>/" + id,
            //dataType: "json",
            success: function (response) {
                location.reload();
            },
        });
    }
</script>    