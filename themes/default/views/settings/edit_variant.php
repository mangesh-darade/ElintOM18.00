<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_variant'); ?></h4>
        </div>
        <?php echo form_open("system_settings/edit_variant/" . $variant->id); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group" id="variant_groupIdError">
                <b> <?= lang('Variant_Group *'); ?></b>
                <?php
            $arr[0] ="select";
            foreach ($variant_group as $variant_groups) {
                $arr[$variant_groups->id] = $variant_groups->name;
            }
            echo form_dropdown('variant_groupId', $arr, $variant->group_id, 'id="variant_groupId" data-placeholder="' . lang("select") . ' ' . lang("variant_group_name") . '" class="form-control" style="width:100%;"');
            ?>
            </div>
            <div class="form-group" id="nameError">
                <label class="control-label" for="name"><?= $this->lang->line("Name *"); ?></label>

                <div class="controls">
                    <?php echo form_input('name', $variant->name, 'class="form-control" id="name" required="required"'); ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_variant', lang('Edit_Variant'), 'id="edit_variant" class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
$(document).ready(function() {

    $("#edit_variant").click(function() {
        var error = 0;
        var name = $('#name');
        var variant_groupId = $("#variant_groupId option:selected").text();
        //alert(variant_groupId);
        var flag = false;

        if (variant_groupId == 'select') {
            $('#variant_groupIdError').addClass('has-error');
            error++;
            flag = true;
        } else {
            $('#variant_groupIdError').removeClass('has-error');
        }
        if (name.val() == '') {
            $('#nameError').addClass('has-error');
            error++;
            flag = true;
        } else {
            $('#nameError').removeClass('has-error');
        }

        if (flag)
            return false;
    });


});
</script>
<?= $modal_js ?>