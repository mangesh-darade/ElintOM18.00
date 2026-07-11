<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title"><?= lang('edit_label'); ?></h4>
        </div>
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open('system_settings/edit_pos_type_label/' . $label_row->id, $attrib);
        ?>
        <div class="modal-body">
            <div class="form-group">
                <?= lang('label_key', 'label_key'); ?>
                <?php
                $opts = array();
                foreach ($suggested_label_keys as $k => $v) {
                    $opts[$k] = $v;
                }
                $opts['_other_'] = '-- ' . lang('other') . ' --';
                $sel = in_array($label_row->label_key, array_keys($suggested_label_keys)) ? $label_row->label_key : '_other_';
                ?>
                <?= form_dropdown('label_key', $opts, $sel, 'class="form-control tip" id="label_key" style="width:100%;"'); ?>
            </div>
            <div class="form-group" id="custom_key_wrap" style="display:<?= $sel === '_other_' ? 'block' : 'none'; ?>;">
                <?= lang('label_key', 'custom_label_key'); ?>
                <?= form_input('custom_label_key', $sel === '_other_' ? $label_row->label_key : '', 'class="form-control tip" id="custom_label_key"'); ?>
            </div>
            <div class="form-group">
                <?= lang('label_value', 'label_value'); ?>
                <?= form_input('label_value', $label_row->label_value, 'class="form-control tip" id="label_value" required="required"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('edit_label', lang('update'), 'class="btn btn-primary"'); ?>
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
        });
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
