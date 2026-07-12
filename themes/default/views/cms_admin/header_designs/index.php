<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="cms-header-designs-page cms-hd-index">
    <div class="cms-page-toolbar cms-header-designs-toolbar">
        <a class="btn btn-default" href="<?= site_url('cms_admin/storefront_designs'); ?>"><i class="fa fa-magic"></i> Layout hub</a>
        <a class="cms-btn-primary btn-add-cms" href="<?= site_url('cms_admin/header_designs/create'); ?>">
            <i class="fa fa-plus"></i> New header design
        </a>
        <a class="btn btn-default" href="<?= site_url('cms_admin/storefront'); ?>">
            <i class="fa fa-columns"></i> Advanced rows
        </a>
    </div>

    <?php if (empty($schema_ready)) { ?>
    <div class="alert alert-danger">
        <strong>Setup required.</strong> Import <code>sma_cms_webshop_header_footer</code>, then refresh.
    </div>
    <?php } else { ?>
    <?php $this->load->view($this->theme . 'cms_admin/_partials/design_workflow_guide', array(
        'design_type' => 'header',
        'context' => 'index',
    )); ?>
    <p class="cms-header-designs-intro">
        Each card is one header layout. Click <strong>Customize</strong> to add logo, phone, promo text, etc., then attach it to a page (step 3 in the guide above).
    </p>

    <div class="cms-header-designs-grid">
        <?php if (!empty($profiles)) { ?>
            <?php foreach ($profiles as $p) {
                $slug = isset($p['slug']) ? (string) $p['slug'] : '';
                $lbl = isset($p['label']) ? (string) $p['label'] : $slug;
                $cnt = isset($p['item_count']) ? (int) $p['item_count'] : 0;
                $prev_url = site_url('cms_admin/header_designs/preview/' . rawurlencode($slug) . '?compact=1&t=' . time());
                ?>
                <article class="cms-header-design-card">
                    <div class="cms-header-design-card__preview">
                        <iframe class="cms-header-design-card__iframe" title="Preview <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?>"
                            src="<?= htmlspecialchars($prev_url, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy"></iframe>
                        <a class="cms-header-design-card__preview-link" href="<?= site_url('cms_admin/header_designs/preview/' . rawurlencode($slug)); ?>" target="_blank" rel="noopener" title="Full preview">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </div>
                    <div class="cms-header-design-card__body">
                        <div class="cms-header-design-card__head">
                            <h3><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <code class="cms-header-design-card__slug"><?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?></code>
                        </div>
                        <p class="cms-header-design-card__meta">
                            <?= (int) $cnt; ?> item<?= $cnt === 1 ? '' : 's'; ?>
                        </p>
                        <div class="cms-header-design-card__actions">
                            <a class="btn btn-primary btn-sm" href="<?= site_url('cms_admin/header_designs/edit/' . rawurlencode($slug)); ?>">
                                <i class="fa fa-pencil"></i> Customize
                            </a>
                            <a class="btn btn-default btn-sm" href="<?= site_url('cms_admin/header_designs/preview/' . rawurlencode($slug)); ?>" target="_blank" rel="noopener">
                                <i class="fa fa-eye"></i> Preview
                            </a>
                            <?php if ($slug !== 'default') { ?>
                            <a class="btn btn-danger btn-sm" href="<?= site_url('cms_admin/header_designs/delete_profile/' . rawurlencode($slug)); ?>"
                               onclick="return confirm('Delete this header design and all its items?');">
                                <i class="fa fa-trash-o"></i>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </article>
            <?php } ?>
        <?php } else { ?>
            <div class="cms-header-designs-empty">
                <p>No header designs yet.</p>
                <a class="cms-btn-primary btn-add-cms" href="<?= site_url('cms_admin/header_designs/create'); ?>">Create first design</a>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>
