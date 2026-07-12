<?php defined('BASEPATH') OR exit('No direct script access allowed');
$presets = isset($presets) && is_array($presets) ? $presets : array();
$items = isset($items) && is_array($items) ? $items : array();
$total = isset($total) ? (int) $total : 0;
$active_preset = isset($active_preset) ? (string) $active_preset : 'all';
$search_q = isset($search_q) ? (string) $search_q : '';
$upload_base = isset($upload_base) ? (string) $upload_base : '';
?>

<div class="cms-media-page" data-cms-media-index="1" data-cms-media-preset="<?= htmlspecialchars($active_preset !== 'all' ? $active_preset : 'misc', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="cms-media-page__head">
        <div>
            <h2 class="cms-page-title">Media Library</h2>
            <p class="cms-media-page__sub">All webshop images in one place — upload once, reuse in header, pages, and content blocks.</p>
        </div>
        <div class="cms-media-page__actions">
            <form method="get" action="<?= site_url('cms_admin/media'); ?>" class="cms-media-search-form">
                <?php if ($active_preset !== 'all') { ?>
                <input type="hidden" name="preset" value="<?= htmlspecialchars($active_preset, ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <div class="cms-media-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="search" name="q" value="<?= htmlspecialchars($search_q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search files…" autocomplete="off">
                </div>
            </form>
            <button type="button" class="btn btn-primary" id="cmsMediaUploadOpen">
                <i class="fa fa-cloud-upload"></i> Upload
            </button>
        </div>
    </div>

    <div class="cms-media-layout">
        <aside class="cms-media-folders" aria-label="Media folders">
            <p class="cms-media-folders__label">Folders</p>
            <ul class="cms-media-folder-list">
                <li>
                    <a href="<?= site_url('cms_admin/media' . ($search_q !== '' ? '?q=' . rawurlencode($search_q) : '')); ?>"
                       class="cms-media-folder-link<?= $active_preset === 'all' ? ' is-active' : ''; ?>">
                        <i class="fa fa-folder-open-o"></i> All media
                    </a>
                </li>
                <?php foreach ($presets as $preset) {
                    $slug = isset($preset['slug']) ? (string) $preset['slug'] : '';
                    $label = isset($preset['label']) ? (string) $preset['label'] : $slug;
                    $qs = array('preset' => $slug);
                    if ($search_q !== '') {
                        $qs['q'] = $search_q;
                    }
                    ?>
                <li>
                    <a href="<?= site_url('cms_admin/media?' . http_build_query($qs)); ?>"
                       class="cms-media-folder-link<?= $active_preset === $slug ? ' is-active' : ''; ?>">
                        <i class="fa fa-folder-o"></i> <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <div class="cms-media-folder-hints">
                <p class="cms-media-folder-hints__title">Upload presets</p>
                <?php foreach ($presets as $preset) { ?>
                <p class="cms-media-folder-hints__row">
                    <strong><?= htmlspecialchars(isset($preset['label']) ? $preset['label'] : '', ENT_QUOTES, 'UTF-8'); ?>:</strong>
                    <?= htmlspecialchars(isset($preset['hint']) ? $preset['hint'] : '', ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <?php } ?>
            </div>
        </aside>

        <div class="cms-media-main">
            <p class="cms-media-count"><?= number_format($total); ?> file<?= $total === 1 ? '' : 's'; ?></p>

            <?php if (empty($items)) { ?>
            <div class="cms-media-empty">
                <i class="fa fa-picture-o"></i>
                <p>No images found<?= $search_q !== '' ? ' for this search' : ''; ?>.</p>
                <button type="button" class="btn btn-default btn-sm" id="cmsMediaUploadOpenEmpty">
                    <i class="fa fa-cloud-upload"></i> Upload your first image
                </button>
            </div>
            <?php } else { ?>
            <div class="cms-media-grid" id="cmsMediaGrid">
                <?php foreach ($items as $item) {
                    $stored = isset($item['stored_path']) ? (string) $item['stored_path'] : '';
                    $url = isset($item['url']) ? (string) $item['url'] : '';
                    $name = isset($item['file_name']) ? (string) $item['file_name'] : '';
                    $preset = isset($item['preset']) ? (string) $item['preset'] : 'misc';
                    $preset_label = isset($presets[$preset]['label']) ? $presets[$preset]['label'] : ucfirst($preset);
                    $deletable = !empty($item['deletable']);
                    ?>
                <article class="cms-media-card" data-stored-path="<?= htmlspecialchars($stored, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="cms-media-card__thumb">
                        <img src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    </div>
                    <div class="cms-media-card__body">
                        <p class="cms-media-card__name" title="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="cms-media-card__meta">
                            <span class="cms-media-card__preset"><?= htmlspecialchars($preset_label, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if (!empty($item['width']) && !empty($item['height'])) { ?>
                            · <?= (int) $item['width']; ?>×<?= (int) $item['height']; ?>
                            <?php } ?>
                            · <?= htmlspecialchars(isset($item['size_label']) ? $item['size_label'] : '', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <div class="cms-media-card__actions">
                            <button type="button" class="btn btn-xs btn-default cms-media-copy-path" data-path="<?= htmlspecialchars($stored, ENT_QUOTES, 'UTF-8'); ?>" title="Copy stored path">
                                <i class="fa fa-clipboard"></i> Copy path
                            </button>
                            <?php if ($deletable) { ?>
                            <button type="button" class="btn btn-xs btn-danger cms-media-delete-file" data-path="<?= htmlspecialchars($stored, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                            <?php } ?>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php
$this->load->view($this->theme . 'cms_admin/_partials/media_picker_modal', array(
    'presets'     => $presets,
    'upload_base' => $upload_base,
    'mode'        => 'page',
));
?>
