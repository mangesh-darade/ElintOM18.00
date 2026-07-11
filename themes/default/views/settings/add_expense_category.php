<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_expense_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_expense_category", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code *', 'code'); ?>
                <?= form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name *', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("parent_category", "parent") ?>
                <?php
                $cat[''] = lang('select') . ' ' . lang('parent_category');
                if (!empty($categories)) {
                    foreach ($categories as $pcat) {
                        $cat[$pcat->id] = $pcat->name;
                    }
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : ''), 'class="form-control select" id="parent" style="width:100%"');
                ?>
            </div>

        </div>
        <div class="modal-footer">
            <?= form_submit('add_expense_category', lang('add_expense_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script>
    $(document).ready(function () {
        var $parent = $('#parent');
        if ($parent.length && $.fn.select2) {
            $parent.select2({
                width: '100%',
                dropdownParent: $parent.closest('.modal-content')
            });
        }
    });
</script>
<?= $modal_js ?>