<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$llms = isset($llms) && is_array($llms) ? $llms : array();
$content = isset($llms['content']) ? (string) $llms['content'] : '';
$enabled = !empty($llms['enabled']);
$auto_preview = isset($llms['auto_generated_preview']) ? (string) $llms['auto_generated_preview'] : '';
$updated_at = isset($llms['updated_at']) ? trim((string) $llms['updated_at']) : '';
$live_url = isset($llms_live_url) ? (string) $llms_live_url : '';
$default_template = isset($llms_default_template) ? (string) $llms_default_template : '';
?>
<div class="box cms-llms-page">
    <div class="box-content">
        <div class="cms-llms-intro">
            <h4 class="entity-title"><i class="fa fa-file-text-o"></i> LLMS.txt — AI assistant index</h4>
            <p class="cms-panel-desc">
                Served at <code>/llms.txt</code> on your <strong>webshop storefront</strong> (e.g.
                <code>http://localhost/ElintOm_Webshop_PHP_8.4/llms.txt</code> on WAMP — not under CMS Admin).
                The live file always includes auto-generated links (home, shop, CMS pages, sitemaps).
                Your <strong>custom markdown</strong> below is merged in when the checkbox is on.
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

        <?= form_open('cms_admin/llms_txt', array('id' => 'cmsLlmsTxtForm')); ?>
        <div class="form-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="llms_txt_include_custom" value="1"<?= $enabled ? ' checked' : ''; ?>>
                Include custom block in <code>llms.txt</code>
            </label>
            <p class="help-block">
                When <strong>checked</strong>, your custom markdown is published on the live <code>/llms.txt</code>.
                When <strong>unchecked</strong>, only auto-generated storefront links appear on the live file — but you can still edit and save your custom draft here.
            </p>
            <?php if (!$enabled) { ?>
                <div class="alert alert-warning cms-llms-custom-off-notice" style="margin-top:10px;margin-bottom:0;">
                    <i class="fa fa-exclamation-triangle"></i>
                    Custom block is <strong>off</strong> on the live file. Check the box above and click <strong>Save</strong> to publish your markdown to visitors and AI bots.
                </div>
            <?php } ?>
        </div>

        <div class="form-group cms-llms-editor-wrap">
            <label for="llms_txt_custom">Custom markdown</label>
            <textarea
                name="llms_txt_custom"
                id="llms_txt_custom"
                class="form-control cms-llms-txt-editor skip"
                rows="22"
                placeholder="## About&#10;Online Pharmacy UAE&#10;&#10;## Products&#10;Vitamins, Skincare, Medical Devices"
            ><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p class="help-block">
                Example sections: <strong>About</strong>, <strong>Products</strong>, <strong>Key URLs</strong> (markdown links).
            </p>
        </div>

        <?php if ($auto_preview !== '') { ?>
            <details class="cms-llms-auto-ref">
                <summary>Auto-generated part (always on live <code>llms.txt</code> — read-only reference)</summary>
                <textarea
                    class="form-control cms-llms-txt-editor cms-llms-txt-editor--readonly skip"
                    rows="14"
                    readonly="readonly"
                    aria-label="Auto-generated llms.txt reference"
                ><?= htmlspecialchars($auto_preview, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </details>
        <?php } ?>

        <div class="cms-llms-actions">
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

<script type="text/javascript">
(function ($) {
    var template = <?= json_encode($default_template); ?>;

    $('#cmsLlmsInsertTemplate').on('click', function () {
        var $ta = $('#llms_txt_custom');
        if ($.trim($ta.val()) !== '' && !window.confirm('Replace current content with the example template?')) {
            return;
        }
        $ta.val(template);
        $ta.focus();
    });
}(jQuery));
</script>
