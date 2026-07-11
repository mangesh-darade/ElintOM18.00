<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_sms_config'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_sms_config/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="template_name">SMS Template Name</label>
                <div class="controls form-control"><?php echo $config->template_name; ?></div>
            </div>
            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line("Client DLT_TE_ID"); ?></label>

                <div class="controls"> 
                    <?php echo form_input('client_dlt_te_id', ($config->client_dlt_te_id ? $config->client_dlt_te_id : $config->dlt_te_id), 'class="form-control" id="client_dlt_te_id"'); ?> 
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_sms_config', lang('edit_sms_config'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>