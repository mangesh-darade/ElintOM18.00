<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="cms-storefront-hub">
    <?php if (empty($schema_ready)) { ?>
    <div class="alert alert-danger">
        <strong>Database setup required.</strong> Import <code>sma_cms_webshop_header_footer</code>, then refresh.
    </div>
    <?php } else { ?>

    <div class="cms-storefront-hub__hero">
        <h2 class="cms-page-title"><i class="fa fa-magic"></i> Header &amp; footer layouts</h2>
        <p class="cms-storefront-hub__lead">
            Build reusable header and footer designs, preview them live, then attach each design to any <strong>CMS Page</strong>.
            One design can be used on many pages (e.g. <code>home</code> header on Home, About, Contact).
        </p>
    </div>

    <div class="cms-storefront-hub__flow">
        <div class="cms-storefront-hub__step"><span>1</span> Create design</div>
        <div class="cms-storefront-hub__arrow"><i class="fa fa-chevron-right"></i></div>
        <div class="cms-storefront-hub__step"><span>2</span> Add logo, links, text</div>
        <div class="cms-storefront-hub__arrow"><i class="fa fa-chevron-right"></i></div>
        <div class="cms-storefront-hub__step"><span>3</span> Assign to CMS page</div>
    </div>

    <div class="row cms-storefront-hub__cards">
        <div class="col-md-6">
            <article class="cms-storefront-hub__card cms-storefront-hub__card--header">
                <div class="cms-storefront-hub__card-icon"><i class="fa fa-object-group"></i></div>
                <h3>Header designs</h3>
                <p>Top bar: logo, phone, promo, links. <?= count(isset($header_profiles) ? $header_profiles : array()); ?> design(s).</p>
                <div class="cms-storefront-hub__card-actions">
                    <a class="btn btn-primary" href="<?= site_url('cms_admin/header_designs'); ?>">Manage headers</a>
                    <a class="btn btn-default" href="<?= site_url('cms_admin/header_designs/create'); ?>"><i class="fa fa-plus"></i> New header</a>
                </div>
            </article>
        </div>
        <div class="col-md-6">
            <article class="cms-storefront-hub__card cms-storefront-hub__card--footer">
                <div class="cms-storefront-hub__card-icon"><i class="fa fa-th-large"></i></div>
                <h3>Footer designs</h3>
                <p>Bottom: tagline, columns, copyright. <?= count(isset($footer_profiles) ? $footer_profiles : array()); ?> design(s).</p>
                <div class="cms-storefront-hub__card-actions">
                    <a class="btn btn-primary" href="<?= site_url('cms_admin/footer_designs'); ?>">Manage footers</a>
                    <a class="btn btn-default" href="<?= site_url('cms_admin/footer_designs/create'); ?>"><i class="fa fa-plus"></i> New footer</a>
                </div>
            </article>
        </div>
    </div>

    <div class="cms-storefront-hub__links">
        <a href="<?= site_url('cms_admin/pages'); ?>"><i class="fa fa-file-text-o"></i> CMS Pages</a>
        <span class="text-muted">|</span>
        <a href="<?= site_url('cms_admin/storefront'); ?>"><i class="fa fa-columns"></i> Advanced row editor (legacy)</a>
    </div>

    <?php } ?>
</div>
