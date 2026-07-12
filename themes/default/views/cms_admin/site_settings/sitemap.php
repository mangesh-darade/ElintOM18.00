<?php defined('BASEPATH') OR exit('No direct script access allowed');
$preview = isset($preview) && is_array($preview) ? $preview : array();
$variant = isset($preview['variant']) ? (string) $preview['variant'] : 'index';
$label = isset($preview['label']) ? (string) $preview['label'] : 'Sitemap';
$description = isset($preview['description']) ? (string) $preview['description'] : '';
$content = isset($preview['content']) ? (string) $preview['content'] : '';
$liveUrl = isset($preview['live_url']) ? (string) $preview['live_url'] : '';
$storefrontUrl = isset($preview['storefront_url']) ? (string) $preview['storefront_url'] : '';
$preview_from_live = !empty($preview['preview_from_live']);
$urlCount = isset($preview['url_count']) ? (int) $preview['url_count'] : 0;
$variants = isset($preview['variants']) && is_array($preview['variants']) ? $preview['variants'] : array();
$tableType = isset($preview['table_type']) ? (string) $preview['table_type'] : '';
$tableRows = isset($preview['table_rows']) && is_array($preview['table_rows']) ? $preview['table_rows'] : array();
?>
<div class="box cms-site-settings-page cms-sitemap-page">
    <div class="box-content">
        <?php $this->load->view($this->theme . 'cms_admin/site_settings/_subnav', get_defined_vars()); ?>

        <div class="cms-site-settings-head">
            <h4 class="entity-title"><i class="fa fa-sitemap"></i> Sitemap.xml</h4>
            <p class="cms-panel-desc">
                XML sitemaps for search engines on your webshop storefront.
                <?php if ($description !== '') { ?>
                <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>:</strong> <?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>
                <?php } ?>
            </p>

            <?php if (!empty($variants)) { ?>
            <form method="get" action="<?= site_url('cms_admin/site_settings/sitemap'); ?>" class="form-inline cms-site-settings-variant-form" style="margin:12px 0;">
                <label for="sitemap_variant">Preview:</label>
                <select name="variant" id="sitemap_variant" class="form-control input-sm" onchange="this.form.submit()">
                    <?php foreach ($variants as $key => $row) { ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"<?= $variant === $key ? ' selected' : ''; ?>>
                        <?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                    <?php } ?>
                </select>
            </form>
            <?php } ?>
        </div>

        <div class="cms-site-settings-panel" data-mode="preview">
            <div class="cms-site-settings-panel__toolbar">
                <div class="cms-site-settings-panel__title">
                    <span class="cms-site-settings-panel__mode cms-site-settings-panel__mode--preview">
                        <i class="fa fa-table"></i> Output — <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <?php if ($liveUrl !== '') { ?>
                <div class="cms-site-settings-panel__actions">
                    <a href="<?= htmlspecialchars($liveUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default btn-sm" target="_blank" rel="noopener">
                        <i class="fa fa-external-link"></i> Open live file
                    </a>
                </div>
                <?php } ?>
            </div>

            <div class="cms-site-settings-preview-panel">
                <?php if (!empty($tableRows)) { ?>
                <div class="cms-sitemap-table-wrap">
                    <table class="cms-sitemap-table table table-bordered table-striped">
                        <thead>
                            <tr>
                                <?php if ($tableType === 'index') { ?>
                                <th>Sitemap</th>
                                <th>Last Modified</th>
                                <?php } else { ?>
                                <th>URL</th>
                                <th>Last Modified</th>
                                <th>Changefreq</th>
                                <th>Priority</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableRows as $row) { ?>
                            <tr>
                                <td>
                                    <a href="<?= htmlspecialchars($row['loc'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                        <?= htmlspecialchars($row['loc'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars(isset($row['lastmod']) && $row['lastmod'] !== '' ? $row['lastmod'] : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php if ($tableType !== 'index') { ?>
                                <td><?= htmlspecialchars(isset($row['changefreq']) && $row['changefreq'] !== '' ? $row['changefreq'] : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars(isset($row['priority']) && $row['priority'] !== '' ? $row['priority'] : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php } ?>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <p class="help-block cms-site-settings-preview-note">
                    Same layout as the live sitemap browser view (sitemap.xsl).
                    <?php if (!$preview_from_live) { ?>
                    <span class="text-muted">Built from CMS data (storefront offline).</span>
                    <?php } ?>
                </p>
                <?php } elseif ($content !== '') { ?>
                <div class="alert alert-warning" style="margin:0 0 12px;">
                    Could not parse sitemap rows for table view.
                </div>
                <?php } else { ?>
                <div class="alert alert-warning" style="margin:0;">
                    No sitemap output available for this variant yet.
                </div>
                <?php } ?>

                <?php if ($content !== '') { ?>
                <details class="cms-sitemap-xml-source">
                    <summary>View raw XML source</summary>
                    <label for="cmsSitemapPreview" class="sr-only">Sitemap XML source</label>
                    <textarea id="cmsSitemapPreview" class="form-control cms-site-settings-preview cms-site-settings-preview--xml cms-site-settings-code-output skip" rows="16" readonly="readonly" aria-label="Sitemap XML source"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </details>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
