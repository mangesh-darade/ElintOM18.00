<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Make the modal as wide as needed to avoid scroll */
.modal-dialog {
    max-width: 90%;
    margin: auto;
}

/* No vertical scrolling */
.modal-content {
    max-height: 100vh;
    overflow: hidden;
    padding: 20px;
    border-radius: 8px;
}

/* Flex layout to align fields side by side */
.modal-body {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 0;
    justify-content: space-between;
}

/* Form groups side by side */
.modal-body .form-group {
    flex: 0 0 48%; /* Two in a row */
    min-width: 240px;
}

/* Full width for larger fields like description */
.modal-body .form-group.full-width {
    flex: 0 0 100%;
}

/* Keep modal header clean */
.modal-header {
    background: #f7f7f7;
    padding: 10px 20px;
    border-bottom: 1px solid #ddd;
}

/* Modal footer */
.modal-footer {
    justify-content: flex-end;
    padding: 10px 20px;
}

/* Input and label spacing */
.modal-body label {
    font-weight: 500;
    margin-bottom: 4px;
    display: inline-block;
}

.modal-body input,
.modal-body select,
.modal-body textarea {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
input.btn.btn-primary{
    margin-top:-8px;
}
</style>
<div class="custom-modal-width">
    <div class="">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">
                <?php echo lang('Deals') . " (" . $leadDetails->full_name . ")"; ?>
            </h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("leads/add_deals/" . $lead_id, $attrib); ?>
        <div class="modal-body">
            <input type="hidden" id="editId" name="id" value="<?php echo $lead_id; ?>">
            <div class="form-group person">
                <?= lang("Category *", "Category") ?>
                <select class="form-control" name="category" required="true">
                    <?php foreach ($categories as $lead) { ?>
                    <option value="<?= $lead->id . '~' . $lead->name ?>"
                        <?= ($lead->id == $deals->CategoryId ? '' : '') ?>>
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
                        <?= ($lead->id == $deals->ProductsId ? '' : '') ?>>
                        <?= $lead->name ?>
                        <!-- Use $lead->type instead of $lead->description -->
                    </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity"><?= lang('Quantity'); ?></label>
                <input type="number" class="form-control" id="quantity" name="quantity"
                    value="<?= set_value('quantity'); ?>" min="1" oninput="validity.valid||(value='');">
            </div>
            <div class="form-group">
                <label for="expected_rate"><?= lang('Expected Rate'); ?></label>
                <input type="number" step="0.01" class="form-control" id="expected_rate" name="expected_rate"
                    value="<?= set_value('expected_rate'); ?>" min="0.01" oninput="validity.valid||(value='');">
            </div>
            <div class="form-group">
                <label for="finalized_rate"><?= lang('Finalized Rate'); ?></label>
                <input type="number" step="0.01" class="form-control" id="finalized_rate" name="finalized_rate"
                    value="<?= set_value('finalized_rate'); ?>" min="0.01" oninput="validity.valid||(value='');">
            </div>
            <div class="form-group">
                <label for="status"><?= lang('Status'); ?></label>
                <input type="text" class="form-control" id="status" name="status" value="">
            </div>
            <div class="form-group">
                <label for="process_stage"><?= lang('Process Stage'); ?></label>
                <input type="text" class="form-control" id="process_stage" name="process_stage" value="">
            </div>
            <div class="form-group">
                <!-- <label for="created_by"><?= lang('Created By'); ?></label> -->
                <input type="hidden" class="form-control" id="created_by" name="created_by" value="">
            </div>
            <div class="form-group">
                <label for="description"><?= lang('Description'); ?></label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <!-- <div class="form-group text-center">
                    <button type="submit" class="btn btn-success"><?= lang('save_changes'); ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('cancel'); ?></button>
                </div> -->
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_deals', lang('Add'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>