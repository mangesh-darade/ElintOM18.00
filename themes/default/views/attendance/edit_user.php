<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-user"></i>Edit User</h2>
    </div>
    <div class="box-content">
        <?php echo form_open('attendance/update_user/' . (int) $user->id, array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" value="<?= html_escape($user->first_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" value="<?= html_escape($user->last_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <?php
                    $ge = array('male' => lang('male'), 'female' => lang('female'));
                    echo form_dropdown('gender', $ge, isset($user->gender) ? $user->gender : '', 'class="form-control" id="gender"');
                    ?>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= html_escape($user->phone); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= html_escape($user->email); ?>">
                </div>
                <div class="form-group">
                    <label for="company">Company</label>
                    <input type="text" name="company" id="company" class="form-control" value="<?= html_escape($user->company); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="group_id">Group</label>
                    <select name="group_id" id="group_id" class="form-control" required>
                        <option value="">Select group</option>
                        <?php if (!empty($groups)) { foreach ($groups as $group) { ?>
                            <option value="<?= (int) $group->id; ?>" <?= ((int) $user->group_id === (int) $group->id) ? 'selected' : ''; ?>>
                                <?= html_escape($group->name); ?>
                            </option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_of_joining">Date of Joining</label>
                    <input type="date" name="date_of_joining" id="date_of_joining" class="form-control" value="<?= !empty($user->date_of_joining) ? html_escape(date('Y-m-d', strtotime($user->date_of_joining))) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="termination_date">Termination Date</label>
                    <input type="date" name="termination_date" id="termination_date" class="form-control" value="<?= !empty($user->termination_date) ? html_escape(date('Y-m-d', strtotime($user->termination_date))) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="active">Active</label>
                    <select name="active" id="active" class="form-control">
                        <option value="1" <?= !empty($user->active) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?= empty($user->active) ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password (leave blank to keep)</label>
                    <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="face_image">Face Image (optional)</label>
                    <input type="file" name="face_image" id="face_image" class="form-control" accept="image/*">
                </div>
                <input type="hidden" name="descriptor" id="descriptor" value="">
            </div>
        </div>
        <div class="form-group">
            <a href="<?= site_url('attendance'); ?>" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
