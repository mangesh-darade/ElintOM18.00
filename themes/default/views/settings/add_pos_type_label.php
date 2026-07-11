<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title"><?= lang('add_label'); ?></h4>
        </div>
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open('system_settings/add_pos_type_label', $attrib);
        ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('label_key', 'label_key'); ?>
                <?php
                $opts = array('' => lang('select') . ' ' . lang('label_key'));
                foreach ($suggested_label_keys as $k => $v) {
                    $opts[$k] = $v;
                }
                $opts['_other_'] = '-- ' . lang('other') . ' --';
                ?>
                <?= form_dropdown('label_key', $opts, set_value('label_key'), 'class="form-control tip" id="label_key" style="width:100%;"'); ?>
            </div>
            <div class="form-group" id="custom_key_wrap" style="display:none;">
                <?= lang('label_key', 'custom_label_key'); ?>
                <?= form_input('custom_label_key', set_value('custom_label_key'), 'class="form-control tip" id="custom_label_key" placeholder="e.g. my_menu_item"'); ?>
            </div>
            <div class="form-group">
                <?= lang('label_value', 'label_value'); ?>
                <?= form_input('label_value', set_value('label_value'), 'class="form-control tip" id="label_value" required="required"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('add_label', lang('add_label'), 'class="btn btn-primary"'); ?>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<?= isset($modal_js) ? $modal_js : ''; ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#label_key').on('change', function() {
            var v = $(this).val();
            if (v === '_other_') {
                $('#custom_key_wrap').slideDown();
                $('#custom_label_key').attr('required', 'required');
            } else {
                $('#custom_key_wrap').slideUp();
                $('#custom_label_key').removeAttr('required').val('');
            }
        }).trigger('change');
        $('form').on('submit', function() {
            if ($('#label_key').val() === '_other_') {
                var custom = $('#custom_label_key').val();
                if (custom) {
                    $('<input>').attr({ type: 'hidden', name: 'label_key', value: custom }).appendTo(this);
                    $('#label_key').removeAttr('name');
                }
            }
        });
    });
</script>
