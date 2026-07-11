<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_deals') . " (" . $deals->name . ")"; ?>
            </h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("leads/edit_deals/" . $deals->id, $attrib); ?>
        <div class="modal-body">
            <input type="hidden" id="editId" name="id" value="<?php echo $deals->id; ?>">
            <div class="form-group person">
                <?= lang("Category *", "Category") ?>
                <select class="form-control" name="category" required="true">
                    <?php foreach ($categories as $lead) { ?>
                    <option value="<?= $lead->id . '~' . $lead->name ?>"
                        <?= ($lead->id == $deals->CategoryId ? 'Selected' : '') ?>>
                        <?= $lead->name ?>
                        <!-- Use $lead->type instead of $lead->description -->
                    </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group person">
                <?= lang("Products *", "Products") ?>
                <select class="form-control" name="Products" required="true">
                    <?php foreach ($products as $lead) { ?>
                    <option value="<?= $lead->id . '~' . $lead->name ?>"
                        <?= ($lead->id == $deals->ProductsId ? 'Selected' : '') ?>>
                        <?= $lead->name ?>
                        <!-- Use $lead->type instead of $lead->description -->
                    </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity"><?= lang('Quantity'); ?></label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $deals->Quantity ?>" min="1" oninput="validity.valid||(value='');">

            </div>
            <div class="form-group">
                <label for="expected_rate"><?= lang('Expected Rate'); ?></label>
                <input type="number" step="0.01" class="form-control" id="expected_rate" name="expected_rate" value="<?= $deals->RateExpected ?>" min="0.01" oninput="validity.valid || (value='');">
                </div>
            <div class="form-group">
                <label for="finalized_rate"><?= lang('Finalized Rate'); ?></label>
                <input type="number" step="0.01" class="form-control" id="finalized_rate" name="finalized_rate" value="<?= $deals->RateFinalized ?>" min="0.01" oninput="validity.valid||(value='');">
            </div>
            <div class="form-group">
                <label for="status"><?= lang('Status'); ?></label>
                <input type="text" class="form-control" id="status" name="status" value="<?php echo $deals->Status; ?>">
            </div>
            <div class="form-group">
                <label for="process_stage"><?= lang('Process Stage'); ?></label>
                <input type="text" class="form-control" id="process_stage" name="process_stage"
                    value="<?php echo $deals->ProcessStage; ?>">
            </div>
            <div class="form-group">
                <!-- <label for="created_by"><?= lang('Created By'); ?></label> -->
                <input type="hidden" class="form-control" id="created_by" name="created_by"
                    value="<?php echo $deals->created_by; ?>">
            </div>
            <div class="form-group">
                <label for="description"><?= lang('Description'); ?></label>
                <textarea class="form-control" id="description"
                    name="description"><?php echo $deals->Description; ?></textarea>
            </div>
            <!-- <div class="form-group text-center">
                    <button type="submit" class="btn btn-success"><?= lang('save_changes'); ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('cancel'); ?></button>
                </div> -->
        </div>
        <div class="modal-footer">
        <?php echo form_submit('edit_deals', lang('Edit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>