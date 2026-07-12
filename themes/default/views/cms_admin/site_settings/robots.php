<?php defined('BASEPATH') OR exit('No direct script access allowed');
$robots = isset($robots) && is_array($robots) ? $robots : array();
$content = isset($robots['content']) ? (string) $robots['content'] : '';
$live_preview = isset($robots['live_preview']) ? (string) $robots['live_preview'] : '';
$preview_from_live = !empty($robots['preview_from_live']);
$live_url = isset($robots['live_url']) ? (string) $robots['live_url'] : '';
$storefront_url = isset($robots['storefront_url']) ? (string) $robots['storefront_url'] : '';
$updated_at = isset($robots['updated_at']) ? trim((string) $robots['updated_at']) : '';
$default_template = isset($robots_default_template) ? (string) $robots_default_template : '';
$start_in_edit = !empty($this->input->get('edit'));
?>
<div class="box cms-site-settings-page cms-robots-page">
    <div class="box-content">
        <?php $this->load->view($this->theme . 'cms_admin/site_settings/_subnav', get_defined_vars()); ?>

        <div class="cms-site-settings-head cms-robots-intro">
            <h4 class="entity-title"><i class="fa fa-android"></i> Robots.txt</h4>
            <p class="cms-panel-desc">
                Crawler rules served at <code>/robots.txt</code> on your webshop storefront
                <?php if ($storefront_url !== '') { ?>
                (<code><?= htmlspecialchars($storefront_url, ENT_QUOTES, 'UTF-8'); ?></code>).
                <?php } ?>
                Preview the live output below, then click <strong>Edit</strong> to change the source code.
                Use <code>{BASE_URL}</code> and <code>{HOST}</code> placeholders in the editor.
            </p>
            <?php if ($live_url !== '') { ?>
            <p>
                <a href="<?= htmlspecialchars($live_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default btn-sm" target="_blank" rel="noopener">
                    <i class="fa fa-external-link"></i> Open live robots.txt
                </a>
            </p>
            <?php } ?>
            <?php if ($updated_at !== '') { ?>
            <p class="text-muted" style="margin-top:8px;"><small>Last saved: <?= htmlspecialchars($updated_at, ENT_QUOTES, 'UTF-8'); ?></small></p>
            <?php } ?>
        </div>

        <div class="cms-site-settings-panel" id="cmsRobotsPanel" data-mode="<?= $start_in_edit ? 'edit' : 'preview'; ?>">
            <div class="cms-site-settings-panel__toolbar">
                <div class="cms-site-settings-panel__title">
                    <span class="cms-site-settings-panel__mode cms-site-settings-panel__mode--preview">
                        <i class="fa fa-eye"></i> Live output preview
                    </span>
                    <span class="cms-site-settings-panel__mode cms-site-settings-panel__mode--edit">
                        <i class="fa fa-code"></i> Edit source code
                    </span>
                </div>
                <div class="cms-site-settings-panel__actions">
                    <button type="button" class="btn btn-primary btn-sm cms-site-settings-edit-btn" id="cmsRobotsEditBtn">
                        <i class="fa fa-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-default btn-sm cms-site-settings-cancel-btn" id="cmsRobotsCancelBtn">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <div class="cms-site-settings-preview-panel" id="cmsRobotsPreviewPanel">
                <label for="cmsRobotsLivePreview" class="sr-only">Live robots.txt preview</label>
                <textarea id="cmsRobotsLivePreview" class="form-control cms-site-settings-preview cms-site-settings-code-output skip" rows="18" readonly="readonly" aria-label="Live robots.txt preview"><?= htmlspecialchars($live_preview, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <p class="help-block cms-site-settings-preview-note">
                    This is what search engines see at <code>/robots.txt</code> (placeholders already resolved).
                    <?php if (!$preview_from_live) { ?>
                    <span class="text-muted">Preview built from your saved settings<?= $storefront_url !== '' ? ' — storefront fetch unavailable' : ''; ?>.</span>
                    <?php } ?>
                </p>
            </div>

            <?= form_open('cms_admin/site_settings/robots', array('id' => 'cmsRobotsTxtForm', 'class' => 'cms-site-settings-edit-panel')); ?>
            <div class="form-group cms-robots-editor-wrap">
                <label for="robots_txt_content">Source code</label>
                <textarea name="robots_txt_content" id="robots_txt_content" class="form-control cms-robots-txt-editor cms-site-settings-code-editor skip" rows="18" spellcheck="false"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <p class="help-block">
                    Placeholders <code>{BASE_URL}</code> and <code>{HOST}</code> are replaced automatically when the live file is served.
                </p>
            </div>

            <div class="cms-site-settings-form-actions">
                <button type="submit" name="save_robots_txt" value="1" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save robots.txt
                </button>
                <button type="button" class="btn btn-default" id="cmsRobotsInsertTemplate">
                    <i class="fa fa-magic"></i> Insert default template
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
(function ($) {
    var template = <?= json_encode($default_template); ?>;
    var $panel = $('#cmsRobotsPanel');

    function setMode(mode) {
        $panel.attr('data-mode', mode);
        if (mode === 'edit') {
            $('#robots_txt_content').focus();
        }
    }

    setMode($panel.attr('data-mode') || 'preview');

    $('#cmsRobotsEditBtn').on('click', function () {
        setMode('edit');
    });
    $('#cmsRobotsCancelBtn').on('click', function () {
        setMode('preview');
    });
    $('#cmsRobotsInsertTemplate').on('click', function () {
        var $ta = $('#robots_txt_content');
        if ($.trim($ta.val()) !== '' && !window.confirm('Replace current content with the default template?')) {
            return;
        }
        $ta.val(template).focus();
    });
}(jQuery));
</script>
