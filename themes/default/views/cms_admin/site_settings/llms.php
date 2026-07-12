<?php defined('BASEPATH') OR exit('No direct script access allowed');
$llms = isset($llms) && is_array($llms) ? $llms : array();
$content = isset($llms['content']) ? (string) $llms['content'] : '';
$enabled = !empty($llms['enabled']);
$auto_preview = isset($llms['auto_generated_preview']) ? (string) $llms['auto_generated_preview'] : '';
$live_preview = isset($llms_live_preview) ? (string) $llms_live_preview : '';
$updated_at = isset($llms['updated_at']) ? trim((string) $llms['updated_at']) : '';
$live_url = isset($llms_live_url) ? (string) $llms_live_url : '';
$default_template = isset($llms_default_template) ? (string) $llms_default_template : '';
$start_in_edit = !empty($this->input->get('edit'));
?>
<div class="box cms-site-settings-page cms-llms-page">
    <div class="box-content">
        <?php $this->load->view($this->theme . 'cms_admin/site_settings/_subnav', get_defined_vars()); ?>

        <div class="cms-site-settings-head cms-llms-intro">
            <h4 class="entity-title"><i class="fa fa-file-text-o"></i> LLMS.txt — AI assistant index</h4>
            <p class="cms-panel-desc">
                Served at <code>/llms.txt</code> on your webshop storefront.
                Preview the live output below, then click <strong>Edit</strong> to change your custom markdown block.
            </p>
            <?php if ($live_url !== '') { ?>
            <p>
                <a href="<?= htmlspecialchars($live_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default btn-sm" target="_blank" rel="noopener">
                    <i class="fa fa-external-link"></i> Open live llms.txt
                </a>
            </p>
            <?php } ?>
            <?php if ($updated_at !== '') { ?>
            <p class="text-muted" style="margin-top:8px;"><small>Last saved: <?= htmlspecialchars($updated_at, ENT_QUOTES, 'UTF-8'); ?></small></p>
            <?php } ?>
        </div>

        <div class="cms-site-settings-panel" id="cmsLlmsPanel" data-mode="<?= $start_in_edit ? 'edit' : 'preview'; ?>">
            <div class="cms-site-settings-panel__toolbar">
                <div class="cms-site-settings-panel__title">
                    <span class="cms-site-settings-panel__mode cms-site-settings-panel__mode--preview">
                        <i class="fa fa-eye"></i> Live output preview
                    </span>
                    <span class="cms-site-settings-panel__mode cms-site-settings-panel__mode--edit">
                        <i class="fa fa-code"></i> Edit custom markdown
                    </span>
                </div>
                <div class="cms-site-settings-panel__actions">
                    <button type="button" class="btn btn-primary btn-sm cms-site-settings-edit-btn" id="cmsLlmsEditBtn">
                        <i class="fa fa-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-default btn-sm cms-site-settings-cancel-btn" id="cmsLlmsCancelBtn">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <div class="cms-site-settings-preview-panel" id="cmsLlmsPreviewPanel">
                <label for="cmsLlmsLivePreview" class="sr-only">Live llms.txt preview</label>
                <textarea id="cmsLlmsLivePreview" class="form-control cms-site-settings-preview cms-site-settings-code-output skip" rows="18" readonly="readonly" aria-label="Live llms.txt preview"><?= htmlspecialchars($live_preview !== '' ? $live_preview : $auto_preview, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <p class="help-block cms-site-settings-preview-note">
                    Full live file at <code>/llms.txt</code> — auto-generated links<?= $enabled ? ' plus your custom block' : ' (custom block is currently off)'; ?>.
                </p>
            </div>

            <?= form_open('cms_admin/site_settings/llms', array('id' => 'cmsLlmsTxtForm', 'class' => 'cms-site-settings-edit-panel')); ?>
            <div class="form-group">
                <label class="checkbox-inline">
                    <input type="checkbox" name="llms_txt_include_custom" value="1"<?= $enabled ? ' checked' : ''; ?>>
                    Include custom block in <code>llms.txt</code>
                </label>
                <?php if (!$enabled) { ?>
                <div class="alert alert-warning cms-llms-custom-off-notice" style="margin-top:10px;margin-bottom:0;">
                    Custom block is <strong>off</strong> on the live file. Check the box and save to publish.
                </div>
                <?php } ?>
            </div>

            <div class="form-group cms-llms-editor-wrap">
                <label for="llms_txt_custom">Custom markdown source</label>
                <textarea name="llms_txt_custom" id="llms_txt_custom" class="form-control cms-llms-txt-editor cms-site-settings-code-editor skip" rows="14" spellcheck="false"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <?php if ($auto_preview !== '') { ?>
            <details class="cms-llms-auto-ref">
                <summary>Auto-generated reference (read-only)</summary>
                <textarea class="form-control cms-llms-txt-editor cms-llms-txt-editor--readonly cms-site-settings-code-output skip" rows="10" readonly="readonly"><?= htmlspecialchars($auto_preview, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </details>
            <?php } ?>

            <div class="cms-site-settings-form-actions cms-llms-actions">
                <button type="submit" name="save_llms_txt" value="1" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save llms.txt
                </button>
                <button type="button" class="btn btn-default" id="cmsLlmsInsertTemplate">
                    <i class="fa fa-magic"></i> Insert example template
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
(function ($) {
    var template = <?= json_encode($default_template); ?>;
    var $panel = $('#cmsLlmsPanel');

    function setMode(mode) {
        $panel.attr('data-mode', mode);
        if (mode === 'edit') {
            $('#llms_txt_custom').focus();
        }
    }

    setMode($panel.attr('data-mode') || 'preview');

    $('#cmsLlmsEditBtn').on('click', function () {
        setMode('edit');
    });
    $('#cmsLlmsCancelBtn').on('click', function () {
        setMode('preview');
    });
    $('#cmsLlmsInsertTemplate').on('click', function () {
        var $ta = $('#llms_txt_custom');
        if ($.trim($ta.val()) !== '' && !window.confirm('Replace current content with the example template?')) {
            return;
        }
        $ta.val(template).focus();
    });
}(jQuery));
</script>
