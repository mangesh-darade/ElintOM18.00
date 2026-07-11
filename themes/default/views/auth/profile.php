<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <style>
    .select2-container-multi {
        height: auto;
    }
   
    .select2-container-multi {
        height: auto;
    }

    /* Select2 red border on validation error */
    .has-error .select2-selection {
        border: 1px solid #a94442 !important;
        box-shadow: 0 0 5px rgba(169,68,66,.5);
    }

    .has-error .select2-selection--multiple {
        border-color: #a94442 !important;
    }

</style>
    </style>
    <div class="col-sm-2">
        <div class="row">
            <div class="col-sm-12 text-center">
                <div style="max-width:200px; margin: 0 auto;">
                    <?php $_gender = empty($user->gender)?'male':$user->gender?>
                    <?=
                    $user->avatar ? '<img alt="" src="' . base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/avatars/thumbs/' . $user->avatar . '" class="avatar">' :
                        '<img alt="" src="' . base_url() . 'assets/images/' . $_gender . '.png" class="avatar">';
                    ?>
                </div>
                <!--<h4><?= lang('login_email'); ?></h4>

                <p><i class="fa fa-envelope"></i> <?= $user->email; ?></p>-->
            </div>
        </div>
    </div>

    <div class="col-sm-10">
        <?php $show_user_options = ($id != $this->session->userdata('user_id')) && ($Owner || $Admin || !empty($GP['users-edit'])); ?>

        <ul id="myTab" class="nav nav-tabs">
            <li class=""><a href="#edit" class="tab-grey"><?= lang('edit') ?></a></li>
            <?php if ($id == $this->session->userdata('user_id')) { ?>
            <li class=""><a href="#cpassword" class="tab-grey"><?= lang('change_password') ?></a></li>
            <?php } ?>
            <li class=""><a href="#avatar" class="tab-grey"><?= lang('avatar') ?></a></li>
            <li class=""><a href="#merchant_profile" class="tab-grey">Merchant Profile</a></li </ul>

            <div class="tab-content">
                <div id="edit" class="tab-pane fade in">

                    <div class="box">
                        <div class="box-header">
                            <h2 class="blue"><i class="fa-fw fa fa-edit nb"></i><?= lang('edit_profile'); ?></h2>
                        </div>
                        <div class="box-content">
                            <div class="row">
                                <div class="col-lg-12">

                                    <?php $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                                echo form_open('auth/edit_user/' . $user->id, $attrib);
                                ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <?php echo lang('first_name', 'first_name'); ?>
                                                    <div class="controls">
                                                        <?php echo form_input('first_name', $user->first_name, 'class="form-control" id="first_name"  onkeypress="return onlyAlphabets1(event,this);" type="text" required="required"'); ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('last_name', 'last_name'); ?>

                                                    <div class="controls">
                                                        <?php echo form_input('last_name', $user->last_name, 'class="form-control" id="last_name" 
  onkeypress="return onlyAlphabets1(event,this);" type="text" required="required"'); ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <?php echo lang('email', 'email'); ?>

                                                    <input type="email" name="email" class="form-control" id="email"
                                                        value="<?= $user->email ?>" required="required" />
                                                </div>
                                                <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                <div class="form-group">
                                                    <?php echo lang('company', 'company'); ?>
                                                    <div class="controls">
                                                        <?php echo form_input('company', $user->company, 'class="form-control" id="company" required="required"'); ?>
                                                    </div>
                                                </div>
                                                <?php } else {
                                                echo form_hidden('company', ($user->company)?$user->company:'0');
                                            } ?>
                                                <div class="form-group">

                                                    <?php echo lang('phone', 'phone'); ?>
                                                    <div class="controls">
                                                        <input type="tel" name="phone" class="form-control" id="phone"
                                                            required="required" value="<?= $user->phone ?>"
                                                            maxlength="10" data-bv-phone="true"
                                                            data-bv-phone-country="US" />
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?= lang('gender', 'gender'); ?>
                                                    <div class="controls"> <?php
                                                    $ge[''] = array('male' => lang('male'), 'female' => lang('female'));
                                                    echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : $user->gender), 'class="tip form-control" id="gender" required="required"');
                                                    ?>
                                                    </div>
                                                </div>
                                                <?php if ($show_user_options) { ?>
                                                <div class="form-group">
                                                    <?= lang('award_points', 'award_points'); ?>
                                                    <?= form_input('award_points', set_value('award_points', $user->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
                                                </div>
                                                <?php } ?>

                                                <?php if ($show_user_options) { ?>
                                                <div class="form-group">
                                                    <?php echo lang('username', 'username'); ?>
                                                    <input type="text" name="username" class="form-control"
                                                        id="username" value="<?= $user->username ?>"
                                                        required="required" />
                                                </div>
                                                <!-- <div class="form-group">
                                                    <?php echo lang('email', 'email'); ?>

                                                    <input type="email" name="email" class="form-control" id="email"
                                                           value="<?= $user->email ?>" required="required"/>
                                                </div> -->
                                                <div class="row">
                                                    <div class="panel panel-warning">
                                                        <div class="panel-heading">
                                                            <?= lang('if_you_need_to_rest_password_for_user') ?></div>
                                                        <div class="panel-body" style="padding: 5px;">
                                                            <div class="col-md-12">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <?php echo lang('password', 'password'); ?>
                                                                        <div class="input-group">
                                                                            <?php echo form_input($password,'', 'class="form-control tip" id="password"  pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[\W]){1,})(?!.*\s).{8,}$"
 data-bv-regexp-message="'.lang('pasword_hint').'"'); ?>
                                                                            <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                                                                <i class="fa fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <?php echo lang('confirm_password', 'password_confirm'); ?>
                                                                        <div class="input-group">
                                                                            <?php echo form_input($password_confirm); ?>
                                                                            <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                                                                <i class="fa fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php } ?>

                                            </div>
                                            <div class="col-md-6 col-md-offset-1">
                                                <?php if ($show_user_options) { ?>

                                                <div class="row">
                                                    <div class="panel panel-warning">
                                                        <div class="panel-heading"><?= lang('user_options') ?></div>
                                                        <div class="panel-body" style="padding: 5px;">
                                                            <div class="col-md-12">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <?= lang('status', 'status'); ?>
                                                                        <?php
                                                                            $opt = array(1 => lang('active'), 0 => lang('inactive'));
                                                                            echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : $user->active), 'id="status" required="required" class="form-control input-tip select" style="width:100%;"');
                                                                            ?>
                                                                    </div>

                                                                    <?php //if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                                    <div <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { }else{ ?>style="display:none;"
                                                                        <?php } ?>>
                                                                        <div class="form-group">
                                                                            <?= lang("group", "group"); ?>
                                                                            <?php
                                                                            $gp[""] = "";
                                                                            foreach ($groups as $group) {
                                                                                if ($group['name'] != 'customer' && $group['name'] != 'supplier') {
                                                                                    $gp[$group['id']] = $group['name'];
                                                                                }
                                                                            }
                                                                            echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $user->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                                            ?>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <div class="no">


                                                                            <div class="form-group" id="warehouse_group">
                                                                                <?= lang("location*", "location*"); ?>
                                                                                <?php /*
                                                                                $wh[''] = lang('select').' '.lang('warehouse');
                                                                                foreach ($warehouses as $warehouse) {
                                                                                    $wh[$warehouse->id] = $warehouse->name;
                                                                                }
                                                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $user->warehouse_id), 'id="warehouse" class="form-control select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" style="width:100%;" ');
                                                                              */  ?>
                                                                               <?php if($Settings->mother_kitchen == 0){ ?>
                                                                                <select name="warehouse[]"
                                                                                    id="warehouse" multiple
                                                                                    class="form-control select"
                                                                                    style="width:100%;">
                                                                                    <?php
                                                                                    // Keep user-selected warehouses first, in the saved order,
                                                                                    // then list remaining warehouses.
                                                                                    $userwarehouse = array_filter(explode(",", $user->warehouse_id));

                                                                                    // Index warehouses by id.
                                                                                    $warehousesById = [];
                                                                                    if (!empty($warehouses)) {
                                                                                        foreach ($warehouses as $w) {
                                                                                            $warehousesById[$w->id] = $w;
                                                                                        }
                                                                                    }

                                                                                    // First, add selected warehouses in the same order as saved.
                                                                                    $orderedWarehouses = [];
                                                                                    if (!empty($userwarehouse)) {
                                                                                        foreach ($userwarehouse as $wid) {
                                                                                            if (isset($warehousesById[$wid])) {
                                                                                                $orderedWarehouses[] = $warehousesById[$wid];
                                                                                                unset($warehousesById[$wid]);
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    // Then, add the rest of the warehouses.
                                                                                    if (!empty($warehousesById)) {
                                                                                        foreach ($warehousesById as $remainingWarehouse) {
                                                                                            $orderedWarehouses[] = $remainingWarehouse;
                                                                                        }
                                                                                    }

                                                                                    // Render options using the ordered list.
                                                                                    foreach ($orderedWarehouses as $warehouse) { ?>
                                                                                    <option value="<?= $warehouse->id?>"
                                                                                        <?= in_array($warehouse->id, $userwarehouse) ? 'selected' : '' ?>>
                                                                                        <?=  $warehouse->name?>
                                                                                    </option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                                <span class="help-block text-danger" id="warehouse_error" style="display:none;">Please select at least one location</span>
                                                                                <?php } else { ?>
                                                                                <select name="warehouse[]"
                                                                                    id="warehouse" multiple
                                                                                    class="form-control select"
                                                                                    style="width:100%;">
                                                                                    <?php
                                                                                    $userwarehouse = array_filter(explode(",", $user->warehouse_id));
                                                                                    $warehousesById = [];
                                                                                    if (!empty($warehouses)) {
                                                                                        foreach ($warehouses as $w) {
                                                                                            $warehousesById[$w->id] = $w;
                                                                                        }
                                                                                    }
                                                                                    $orderedWarehouses = [];
                                                                                    if (!empty($userwarehouse)) {
                                                                                        foreach ($userwarehouse as $wid) {
                                                                                            if (isset($warehousesById[$wid])) {
                                                                                                $orderedWarehouses[] = $warehousesById[$wid];
                                                                                                unset($warehousesById[$wid]);
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    if (!empty($warehousesById)) {
                                                                                        foreach ($warehousesById as $remainingWarehouse) {
                                                                                            $orderedWarehouses[] = $remainingWarehouse;
                                                                                        }
                                                                                    }
                                                                                    foreach ($orderedWarehouses as $warehouse) { ?>
                                                                                    <option value="<?= $warehouse->id?>"
                                                                                        <?= in_array($warehouse->id, $userwarehouse) ? 'selected' : '' ?>>
                                                                                        <?=  $warehouse->name?>
                                                                                    </option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                                <?php } ?>
                                                                                <span class="help-block text-danger" id="warehouse_error" style="display:none;">Please select at least one location</span>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <?= lang("biller", "biller"); ?>
                                                                                <?php
                                                                                $bl[""] = lang('select').' '.lang('biller');
                                                                                foreach ($billers as $biller) {
                                                                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                                                                }
                                                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $user->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" class="form-control select" style="width:100%;"');
                                                                                ?>
                                                                                <input type="hidden" name="biller"
                                                                                    id="biller_id">
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <?= lang('offline_mobile_app_access', 'offline_mobile_app_access'); ?>
                                                                                <?php
                                                                                $opt_mbaccess = array(1 => lang('active'), 0 => lang('inactive'));
                                                                                echo form_dropdown('offline_mobile_app_access', $opt_mbaccess, (isset($_POST['offline_mobile_app_access']) ? $_POST['offline_mobile_app_access'] : $user->offline_mobile_app_access), 'id="offline_mobile_app_access" class="form-control select" style="width:100%;"');
                                                                                ?>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <?= lang('offline_windows_app_access', 'offline_windows_app_access'); ?>
                                                                                <?php
                                                                                 $opt_winacces = array(1 => lang('active'), 0 => lang('inactive'));
                                                                                
                                                                                echo form_dropdown('offline_windows_app_access', $opt_winacces, (isset($_POST['offline_windows_app_access']) ? $_POST['offline_windows_app_access'] : $user->offline_windows_app_access), 'id="offline_windows_app_access" class="form-control select" style="width:100%;"');
                                                                                ?>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <?= lang("view_right", "view_right"); ?>
                                                                                <?php
                                                                                $vropts = array(1 => lang('all_records'), 0 => lang('own_records'));
                                                                                echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : $user->view_right), 'id="view_right" class="form-control select" style="width:100%;"');
                                                                                ?>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <?= lang("edit_right", "edit_right"); ?>
                                                                                <?php
                                                                                $opts = array(1 => lang('yes'), 0 => lang('no'));
                                                                                echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : $user->edit_right), 'id="edit_right" class="form-control select" style="width:100%;"');
                                                                                ?>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <?= lang("allow_discount", "allow_discount"); ?>
                                                                                <?= form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : $user->allow_discount), 'id="allow_discount" class="form-control select" style="width:100%;"'); ?>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="date_of_joining">Date of Joining</label>
                                                                                <?php $date_of_joining = isset($_POST['date_of_joining']) ? $_POST['date_of_joining'] : (!empty($user->date_of_joining) ? date('Y-m-d', strtotime($user->date_of_joining)) : ''); ?>
                                                                                <input type="date" name="date_of_joining" id="date_of_joining" class="form-control" value="<?= $date_of_joining; ?>" />
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="termination_date">Termination Date</label>
                                                                                <?php $termination_date = isset($_POST['termination_date']) ? $_POST['termination_date'] : (!empty($user->termination_date) ? date('Y-m-d', strtotime($user->termination_date)) : ''); ?>
                                                                                <input type="date" name="termination_date" id="termination_date" class="form-control" value="<?= $termination_date; ?>" />
                                                                            </div>

                                                                            <?php if($pos_type == 'restaurant'){ 
                                                                                  $tablesSelected = explode(",", $user->table_assign);
                                                                                ?>
                                                                            <div class="form-group">
                                                                                <label for="restaurantTables">
                                                                                    Tables</label>
                                                                                <select class="form-control"
                                                                                    name="table_assign[]"
                                                                                    multiple="true">
                                                                                    <?php  foreach($restaurantTables as $tables){ ?>
                                                                                    <option value="<?= $tables->id ?>"
                                                                                        <?= (in_array($tables->id ,$tablesSelected)?'selected' :'') ?>>
                                                                                        <?= $tables->name ?></option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                            </div>
                                                                            <?php } ?>

                                                                        </div>
                                                                        <?php //} ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php } ?>
                                                <?php echo form_hidden('id', $id); ?>
                                                <?php echo form_hidden($csrf); ?>
                                                <input type="hidden" name="warehouse_order" id="warehouse_order" value="">
                                                <div class="row" id="profile-face-register" style="margin-top:10px;">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Register Face (Optional)</label>
                                                            <video class="face-register-video" width="320" height="240" autoplay muted playsinline style="display:block;background:#000;border-radius:4px;"></video>
                                                            <canvas class="face-register-canvas" width="320" height="240" style="display:none;"></canvas>
                                                            <div style="margin-top:8px;">
                                                                <button type="button" class="btn btn-info start-face-camera">Start Face Camera</button>
                                                                <button type="button" class="btn btn-primary capture-face-descriptor">Capture Face</button>
                                                            </div>
                                                            <div style="margin-top:8px;">
                                                                <img class="face-register-preview img-thumbnail" src="" alt="Captured face preview" style="display:none;max-width:160px;">
                                                            </div>
                                                            <small class="text-muted face-register-status" style="display:block;margin-top:6px;"></small>
                                                            <input type="hidden" name="face_descriptor" class="face-register-descriptor" value="">
                                                            <input type="hidden" name="face_image_data" class="face-register-image-data" value="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p><?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
                                    </p>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="cpassword" class="tab-pane fade">
                    <div class="box">
                        <div class="box-header">
                            <h2 class="blue"><i class="fa-fw fa fa-key nb"></i><?= lang('change_password'); ?></h2>
                        </div>
                        <div class="box-content">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php echo form_open("auth/change_password", 'id="change-password-form"'); ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <?php echo lang('old_password', 'curr_password'); ?> <br />
                                                    <div class="input-group">
                                                        <?php echo form_password('old_password', '', 'class="form-control" id="curr_password" required="required"'); ?>
                                                        <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                                            <i class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label
                                                        for="new_password"><?php echo sprintf(lang('new_password'), $min_password_length); ?></label>
                                                    <br />
                                                    <div class="input-group">
                                                        <?php echo form_password('new_password', '', 'class="form-control" id="new_password" required="required" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[\W]){1,})(?!.*\s).{8,}$" data-bv-regexp-message="'.lang('pasword_hint').'"'); ?>
                                                        <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                                            <i class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                    <span class="help-block"><?= lang('pasword_hint') ?></span>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('confirm_password', 'new_password_confirm'); ?>
                                                    <br />
                                                    <div class="input-group">
                                                        <?php echo form_password('new_password_confirm', '', 'class="form-control" id="new_password_confirm" required="required" data-bv-identical="true" data-bv-identical-field="new_password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                                        <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility(this)">
                                                            <i class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <?php echo form_input($user_id); ?>
                                                <p><?php echo form_submit('change_password', lang('change_password'), 'class="btn btn-primary"'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="avatar" class="tab-pane fade">
                    <div class="box">
                        <div class="box-header">
                            <h2 class="blue"><i class="fa-fw fa fa-file-picture-o nb"></i><?= lang('change_avatar'); ?>
                            </h2>
                        </div>
                        <div class="box-content">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-md-5">
                                        <div style="position: relative;">
                                            <?php if ($user->avatar) { ?>
                                            <img alt=""
                                                src="<?= base_url() ?>assets/mdata/<?= $Customer_assets ?>/uploads/avatars/<?= $user->avatar ?>"
                                                class="profile-image img-thumbnail">
                                            <a href="#" class="btn btn-danger btn-xs po"
                                                style="position: absolute; top: 0;" title="<?= lang('delete_avatar') ?>"
                                                data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-block btn-danger po-delete23' href='<?= site_url('auth/delete_avatar/' . $id . '/' . $user->avatar) ?>'> <?= lang('i_m_sure') ?></a> <button class='btn btn-block po-close'> <?= lang('no') ?></button>"
                                                data-html="true" rel="popover"><i class="fa fa-trash-o"></i></a><br>
                                            <br><?php } ?>
                                        </div>
                                        <script>
                                        $('#delete-po').click(function() {

                                            location.reload();

                                        });
                                        </script>
                                        <?php echo form_open_multipart("auth/update_avatar"); ?>
                                        <div class="form-group">
                                            <?= lang("change_avatar", "change_avatar"); ?></br>
                                            <?= lang("Upload image size should be 153 px x 153 px."); ?></br></br>
                                            <input id="product_image" type="file"
                                                data-browse-label="<?= lang('browse'); ?>" name="avatar"
                                                data-show-upload="false" data-show-preview="false" accept="image/*"
                                                class="form-control file">
                                        </div>

                                        <div class="form-group">
                                            <?php echo form_hidden('id', $id); ?>
                                            <?php echo form_hidden($csrf); ?>
                                            <?php echo form_submit('update_avatar', lang('update_avatar'), 'class="btn btn-primary"'); ?>
                                            <?php echo form_close(); ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--merchant profile-->

                <div id="merchant_profile" class="tab-pane fade">
                    <div class="box">
                        <div class="box-header">
                            <h2 class="blue"><i class="fa-fw fa fa-key nb"></i>Merchant Profile</h2>
                        </div>
                        <?php
			$data = array(
				"apikey" => "32468723PWERWE234324SADA",
				"phone" => $user->phone,
				);
				$surl   = "https://simplypos.in/api/merchantDetail.php";
				$res    = post_to_url($surl, $data); 
				//var_dump(json_decode($res));
				$result1 = json_decode($res, true);
				foreach ($result1 as $merchant) {
				$mer_type = $merchant['type'];
				$mer_name = $merchant['name'];
				$mer_address = $merchant['address'];
				$mer_email = $merchant['email'];
				$mer_phone = $merchant['phone'];
				$mer_business_name = $merchant['business_name'];
				$mer_pos_create_at = $merchant['pos_create_at'];
				$mer_pos_demo_expiry_at = $merchant['pos_demo_expiry_at'];
				$mer_pos_name = $merchant['pos_name'];
				}

				//return $res;
			?>
                        <div class="box-content">
                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <?php echo lang('name'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_name; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('Type'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $pos_type; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('Email'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_email; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('Mobile'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_phone; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('Business Name'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_business_name; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <?php echo lang('Pos Name'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_pos_name; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('Address'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_address; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <?php echo lang('Created At'); ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $mer_pos_create_at; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?php echo lang('Updated At'); ?>
                                                    <div class="controls">
                                                        <input type="text"
                                                            value="<?php echo $mer_pos_demo_expiry_at; ?>"
                                                            class="form-control" disabled>
                                                    </div>
                                                </div>





                                                <?php echo form_input($user_id); ?>

                                            </div>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!--merchant profile-->

            </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#change-password-form').bootstrapValidator({
            message: 'Please enter/select a value',
            submitButtons: 'input[type="submit"]'
        });
    });

    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        //document.getElementById("error2").style.display = ret ? "none" : "inline";
        return ret;
    }
    </script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= $assets; ?>js/auth_face_register.js"></script>
    <?php if ($show_user_options) { ?>
    <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        $('#group').change(function(event) {
            var group = $(this).val();
            if (group == 1 || group == 2) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
        var group = <?=$user->group_id?>;
        if (group == 1 || group == 2) {
            $('.no').slideUp();
        } else {
            $('.no').slideDown();
        }
    });
    </script>
    <?php } ?>
    <?php

				
		
	//sms//

function post_to_url($url, $data) {
    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    
    
    $post = curl_init();
    curl_setopt($post, CURLOPT_URL, $url);
    curl_setopt($post, CURLOPT_POST, count($data));
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($post);
    
    return $result;
}

?>
<!-- Multiple Biller functionality -->
 <script>
$(document).ready(function() {
    var warehouseId = $('#warehouse').val();
   
    if (warehouseId) {
        getbillerbyWarehoueseid(warehouseId);
    }
    $('#warehouse').on('change', function() {
        var warehouseId = $(this).val();
        getbillerbyWarehoueseid(warehouseId);
    });
    $('#biller').on('change', function() {
        var biller_name = $(this).val();
        $("#biller_id").val(biller_name);
    });

    function getbillerbyWarehoueseid(warehouseId) {
        if (warehouseId) {
            $.ajax({
                url: "<?= site_url('sales/get_biller_details'); ?>",
                type: "GET",
                data: {
                    warehouse_id: warehouseId[0] // Assuming warehouseId is an array, take the first element
                },
                dataType: "json",
                success: function(response) {
                    const slbiller = $('#biller');
                    if (response.success) {
                        slbiller.empty(); // Clear existing options
                        response.billers.forEach(function(biller, index) {
                            const option = $('<option>', {
                                value: biller.id,
                                text: biller.name
                            });

                            slbiller.append(option);
                        });
                        response.primer_biller ? slbiller.val(response.primer_biller).trigger(
                            'change') : slbiller.val(response.billers[0].id).trigger('change');

                    } else {
                        // alert(response.message);
                        // slbiller.empty(); // Clear existing options if no billers found
                        // slbiller.append($('<option>', {
                        //     value: '',
                        //     text: 'No Billers Available'
                        // }));
                    }
                },
                error: function() {
                    // alert('Error fetching warehouse data');
                }
            });
        }
    }
     ////////////////////////// Generate Variant PO ////////////////////////////

    // Filter warehouses based on selected group
    $('#group').on('change', function() {
        var groupId = $(this).val();
      
        if (groupId) {
            $.ajax({
                url: "<?= site_url('auth/get_warehouses_by_group'); ?>",
                type: "POST",
                data: {
                    group_id: groupId,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                dataType: "json",
                success: function(response) {
                    const warehouseSelect = $('#warehouse');
                    
                    if (response.status == 'success') {
                        var selectedWarehouses = warehouseSelect.val() || [];
                        warehouseSelect.empty();
                        if (response.warehouses && response.warehouses.length > 0) {
                            response.warehouses.forEach(function(warehouse) {
                                const option = $('<option>', {
                                    value: warehouse.id,
                                    text: warehouse.name
                                });
                                if (selectedWarehouses.indexOf(String(warehouse.id)) !== -1 || selectedWarehouses.indexOf(warehouse.id) !== -1) {
                                    option.prop('selected', true);
                                }
                                warehouseSelect.append(option);
                            });
                        } else {
                            warehouseSelect.append($('<option>', {
                                value: '',
                                text: 'No Locations Available'
                            }));
                        }
                        if (warehouseSelect.hasClass('select2-hidden-accessible')) {
                            warehouseSelect.select2('destroy').select2({minimumResultsForSearch: 7});
                        } else if (warehouseSelect.data('chosen')) {
                            warehouseSelect.trigger('chosen:updated');
                        }
                    } else {
                        console.error('Error loading warehouses:', response.message);
                    }
                },
                error: function() {
                    console.error('AJAX error while fetching warehouses');
                }
            });
        }
    });

    $('#warehouse').closest('form').on('submit', function () {
        var $s = $('#warehouse'), order = [], used = {};
        if (!$s.length) return;

        if ($s.data('select2')) {
            var data = $s.select2('data');
            if ($.isArray(data)) {
                $.each(data, function (i, item) {
                    if (item && item.id !== undefined && item.id !== '') {
                        order.push(String(item.id));
                    }
                });
            }
        }
        if (!order.length) {
            $s.next('.select2-container').find('li.select2-search-choice').each(function () {
                var d = $(this).data('select2-data');
                if (d && d.id !== undefined && d.id !== '') {
                    order.push(String(d.id));
                }
            });
        }
        if (!order.length && $s.val()) {
            order = $.map($s.val(), String);
        }

        $('#warehouse_order').val(order.join(','));

        order.forEach(function (id) {
            used[id] = 1;
            $s.append($s.find('option[value="' + id + '"]').prop('selected', true).detach());
        });
        $s.find('option').each(function () {
            if (!used[String($(this).val())]) {
                $s.append($(this).detach());
            }
        });
    });

});

function togglePasswordVisibility(element) {
    var field = element.parentElement.querySelector('input');
    var icon = element.querySelector('i');
    if (field && (field.type === "password" || field.type === "text")) {
        if (field.type === "password") {
            field.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}
</script>