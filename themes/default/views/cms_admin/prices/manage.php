<?php defined('BASEPATH') OR exit('No direct script access allowed');

$customer_assets = isset($Customer_assets) ? $Customer_assets : '';

$uploads_base = 'assets/mdata/' . $customer_assets . '/uploads/';
$cat_image_tag = function ($image, $class) use ($uploads_base) {
    $uploads_base = rtrim($uploads_base, '/') . '/';
    $uploads_abs = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $uploads_base);
    $thumbs_abs = rtrim($uploads_abs, '/\\') . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR;

    $file = ($image !== '' && $image !== null) ? trim((string) $image) : 'no_image.png';
    $thumb_abs = $thumbs_abs . $file;
    $full_abs = rtrim($uploads_abs, '/\\') . DIRECTORY_SEPARATOR . $file;
    if (!is_file($thumb_abs) && !is_file($full_abs)) {
        $file = 'no_image.png';
    }

    $thumb = site_url($uploads_base . 'thumbs/' . $file);
    $full = site_url($uploads_base . $file);
    $placeholder = site_url($uploads_base . 'no_image.png');
    return '<img src="' . htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8') . '"'
        . ' data-fallback="' . htmlspecialchars($full, ENT_QUOTES, 'UTF-8') . '"'
        . ' data-placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"'
        . ' alt="" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . ' cms-img-fallback" loading="lazy" />';
};

$cat_in_eshop = function ($category) {

    return isset($category['in_eshop']) && (int) $category['in_eshop'] === 1;

};

?>



<div class="price-mgmt-container cms-prices-page">



    <div class="ws-card-box cms-prices-card">

        <?php

        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'name' => 'frm_products', 'id' => 'frm_products');

        echo form_open_multipart('cms_admin/prices/save/', $attrib, array('action' => 'save_changes'));

        ?>

        <div class="row cms-prices-layout">

            <div class="col-md-4 col-sm-12 cms-prices-nav-col">

                <div id="cmsPricesCategoryNav" class="price-side-menu cms-prices-side-panel">

                    <div class="price-side-header">

                        Active categories

                        <span class="price-side-header-hint">E-shop enabled · click category to expand subcategories</span>

                    </div>

                    <div class="cms-prices-nav-scroll">

                    <?php if (is_array($categories) && !empty($categories[0])) {

                        foreach ($categories[0] as $category) {

                            if (!$cat_in_eshop($category)) {

                                continue;

                            }

                            $cat_id = (int) $category['id'];

                            $cat_img = isset($category['image']) ? $category['image'] : '';

                            $cat_name = htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8');

                            $subcats = (isset($categories[$cat_id]) && is_array($categories[$cat_id]))

                                ? array_values(array_filter($categories[$cat_id], $cat_in_eshop))

                                : array();

                            ?>

                            <?php
                            $sub_count = count($subcats);
                            $has_subcats = $sub_count > 0;
                            $wrap_class = 'price-cat-item-wrap' . ($has_subcats ? ' has-subcategories' : '');
                            ?>
                            <div class="<?= $wrap_class ?>" data-category-id="<?= $cat_id ?>" data-subcategory-count="<?= $sub_count ?>">

                                <div class="price-cat-row price-nav-item price-nav-item--category"

                                     data-category-id="<?= $cat_id ?>"

                                     data-subcategory-id="0"

                                     data-category-name="<?= $cat_name ?>"

                                     data-nav-role="category"

                                     role="button"

                                     tabindex="0"

                                     title="<?= $has_subcats ? 'Click to show subcategories and load all products' : 'Load all products in this category' ?>">

                                    <?= $cat_image_tag($cat_img, 'price-cat-thumb') ?>

                                    <div class="price-nav-text">

                                        <div class="price-cat-name">

                                            <i class="fa fa-check-circle" title="Active in e-shop"></i>

                                            <?= $cat_name ?>

                                        </div>

                                    </div>

                                    <?php if ($has_subcats) { ?>

                                        <span class="price-subcat-indicator" title="<?= $sub_count ?> active subcategor<?= $sub_count > 1 ? 'ies' : 'y' ?>">

                                            <i class="fa fa-sitemap"></i> <?= $sub_count ?>

                                        </span>

                                        <span class="price-nav-expand" aria-hidden="true"><i class="fa fa-chevron-down"></i></span>

                                    <?php } else { ?>

                                        <span class="price-nav-action" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>

                                    <?php } ?>

                                </div>



                                <?php if ($has_subcats) { ?>

                                    <div class="price-subcat-group" id="price-subcats-<?= $cat_id ?>">

                                        <div class="price-subcat-group-label">

                                            <i class="fa fa-level-down"></i> Subcategories

                                        </div>

                                        <?php foreach ($subcats as $subcategory) {

                                            $sub_id = (int) $subcategory['id'];

                                            $sub_img = isset($subcategory['image']) ? $subcategory['image'] : '';

                                            $sub_name = htmlspecialchars($subcategory['name'], ENT_QUOTES, 'UTF-8');

                                            ?>

                                            <div class="price-subcat-row price-nav-item price-nav-item--sub"

                                                 data-category-id="<?= $cat_id ?>"

                                                 data-subcategory-id="<?= $sub_id ?>"

                                                 data-category-name="<?= $cat_name ?>"

                                                 data-subcategory-name="<?= $sub_name ?>"

                                                 data-nav-role="subcategory"

                                                 role="button"

                                                 tabindex="0"

                                                 title="Load products in this subcategory">

                                                <?= $cat_image_tag($sub_img, 'price-cat-thumb price-cat-thumb--sm') ?>

                                                <div class="price-nav-text">

                                                    <span class="price-subcat-name">

                                                        <i class="fa fa-check-circle" title="Active in e-shop"></i>

                                                        <?= $sub_name ?>

                                                    </span>

                                                </div>

                                                <span class="price-nav-action" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>

                                            </div>

                                        <?php } ?>

                                    </div>

                                <?php } ?>

                            </div>

                            <?php

                        }

                    } else { ?>

                        <p class="cms-prices-nav-empty">No active categories available.</p>

                    <?php } ?>

                    </div><!-- .cms-prices-nav-scroll -->

                </div>

            </div>



            <div class="col-md-8 col-sm-12 cms-prices-workspace-col">

                <div id="cmsPricesActiveSelection" class="cms-prices-selection is-empty" aria-live="polite">

                    <i class="fa fa-folder-open-o"></i>

                    <span class="cms-prices-selection-text">Select a category or subcategory on the left</span>

                </div>

                <div class="cms-prices-search-bar">
                    <label for="cmsPricesProductSearch" class="cms-prices-search-label">
                        Search products
                    </label>
                    <input type="text"
                           id="cmsPricesProductSearch"
                           class="form-control cms-price-input cms-prices-search-input"
                           placeholder="Type product or variant name" />
                </div>

                <div id="div_product_list" class="product-list-wrapper cms-prices-products-panel">

                    <div class="cms-prices-empty-state">

                        <i class="fa fa-shopping-cart"></i>

                        <h3>Pricing workspace empty</h3>

                        <p>Choose a <strong>category</strong> (all products) or a <strong>subcategory</strong> from the list. Click a product row to view full details.</p>

                    </div>

                </div>



                <div class="cms-prices-save-row">

                    <button type="submit" id="updateprice" class="btn-submit-prices">

                        <i class="fa fa-save"></i> Save Price Updates

                    </button>

                </div>

            </div>

        </div>

        <?= form_close(); ?>

    </div>



</div>


