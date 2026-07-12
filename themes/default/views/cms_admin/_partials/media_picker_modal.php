<?php defined('BASEPATH') OR exit('No direct script access allowed');
$presets = isset($presets) && is_array($presets) ? $presets : array();
$mode = isset($mode) ? (string) $mode : 'embed';
$upload_base = isset($upload_base) ? (string) $upload_base : cms_media_uploads_base_url(isset($Customer_assets) ? $Customer_assets : 'localhost');
?>

<div class="cms-media-modal" id="cmsMediaPickerModal" aria-hidden="true" role="dialog" aria-labelledby="cmsMediaPickerTitle">
    <div class="cms-media-modal__backdrop" data-cms-media-close="1"></div>
    <div class="cms-media-modal__dialog">
        <header class="cms-media-modal__header">
            <h3 id="cmsMediaPickerTitle"><i class="fa fa-picture-o"></i> Choose from Media Library</h3>
            <button type="button" class="cms-media-modal__close" data-cms-media-close="1" aria-label="Close">&times;</button>
        </header>
        <div class="cms-media-modal__toolbar">
            <select id="cmsMediaPickerPreset" class="form-control input-sm">
                <option value="all">All folders</option>
                <?php foreach ($presets as $preset) { ?>
                <option value="<?= htmlspecialchars(isset($preset['slug']) ? $preset['slug'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars(isset($preset['label']) ? $preset['label'] : '', ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php } ?>
            </select>
            <div class="cms-media-search cms-media-search--modal">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input type="search" id="cmsMediaPickerSearch" placeholder="Search…" autocomplete="off">
            </div>
            <label class="btn btn-default btn-sm cms-media-modal-upload-btn">
                <i class="fa fa-cloud-upload"></i> Upload
                <input type="file" id="cmsMediaPickerUploadInput" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.ico" hidden>
            </label>
        </div>
        <div class="cms-media-modal__body">
            <div class="cms-media-modal__grid" id="cmsMediaPickerGrid"></div>
            <p class="cms-media-modal__empty" id="cmsMediaPickerEmpty" style="display:none;">No images in this folder.</p>
            <p class="cms-media-modal__loading" id="cmsMediaPickerLoading"><i class="fa fa-spinner fa-spin"></i> Loading…</p>
        </div>
        <footer class="cms-media-modal__footer">
            <span class="cms-media-modal__hint text-muted">Click an image to select it.</span>
            <button type="button" class="btn btn-default btn-sm" data-cms-media-close="1">Cancel</button>
        </footer>
    </div>
</div>

<input type="file" id="cmsMediaPageUploadInput" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.ico" hidden>

<div class="cms-media-upload-panel" id="cmsMediaUploadPanel" aria-hidden="true">
    <div class="cms-media-upload-panel__backdrop" data-cms-upload-close="1"></div>
    <div class="cms-media-upload-panel__sheet">
        <header class="cms-media-upload-panel__head">
            <h3><i class="fa fa-cloud-upload"></i> Upload to Media Library</h3>
            <button type="button" class="cms-media-modal__close" data-cms-upload-close="1">&times;</button>
        </header>
        <form id="cmsMediaUploadForm" enctype="multipart/form-data">
            <div id="cmsMediaUploadError" class="alert alert-danger" style="display:none;" role="alert"></div>
            <div class="form-group">
                <label for="cmsMediaUploadPreset">Folder preset</label>
                <select name="preset" id="cmsMediaUploadPreset" class="form-control">
                    <?php foreach ($presets as $preset) { ?>
                    <option value="<?= htmlspecialchars(isset($preset['slug']) ? $preset['slug'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars(isset($preset['label']) ? $preset['label'] : '', ENT_QUOTES, 'UTF-8'); ?>
                        — <?= htmlspecialchars(isset($preset['hint']) ? $preset['hint'] : '', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="cmsMediaUploadFile">Image file</label>
                <input type="file" name="media_file" id="cmsMediaUploadFile" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.ico" required>
                <p class="help-block text-muted">JPG, PNG, GIF, WEBP, SVG, ICO — max 2 MB.</p>
            </div>
            <div class="cms-media-upload-panel__actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
                <button type="button" class="btn btn-default" data-cms-upload-close="1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
window.CmsMediaLibrary = window.CmsMediaLibrary || {};
window.CmsMediaLibrary.config = {
    listUrl: <?= json_encode(site_url('cms_admin/media/list_json')) ?>,
    uploadUrl: <?= json_encode(site_url('cms_admin/media/upload')) ?>,
    deleteUrl: <?= json_encode(site_url('cms_admin/media/delete')) ?>,
    pageUrl: <?= json_encode(site_url('cms_admin/media')) ?>,
    uploadsBase: <?= json_encode(rtrim($upload_base, '/') . '/') ?>,
    csrfName: <?= json_encode($this->security->get_csrf_token_name()) ?>,
    csrfHash: <?= json_encode($this->security->get_csrf_hash()) ?>
};
</script>
