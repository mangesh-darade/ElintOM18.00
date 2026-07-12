<?php defined('BASEPATH') OR exit('No direct script access allowed');
$category_id   = isset($category_id) ? $category_id : null;
$subcategories = (isset($subcategories) && is_array($subcategories)) ? $subcategories : array();
$products      = isset($products) ? $products : null;
$categories    = isset($categories) ? $categories : array();
$cat_eshop     = isset($cat_eshop) && is_array($cat_eshop) ? $cat_eshop : array();
$catalog_stats = isset($catalog_stats) && is_array($catalog_stats) ? $catalog_stats : array();

$stat_total_categories = isset($catalog_stats['total_categories']) ? (int) $catalog_stats['total_categories'] : 0;
$stat_total_products   = isset($catalog_stats['total_products']) ? (int) $catalog_stats['total_products'] : 0;
$stat_active_products  = isset($catalog_stats['active_products']) ? (int) $catalog_stats['active_products'] : 0;
$stat_hidden_products  = isset($catalog_stats['hidden_products']) ? (int) $catalog_stats['hidden_products'] : 0;

$cat_is_eshop = function ($id) use ($cat_eshop) {
    $id = (int) $id;
    return $id > 0 && isset($cat_eshop[$id]) && (int) $cat_eshop[$id] === 1;
};
$prod_is_eshop = function ($val) {
    return (int) $val === 1 || $val === '1' || $val === true;
};

$catalog_manage_url = site_url('cms_admin/catalog');
$customer_assets = isset($Customer_assets) ? $Customer_assets : '';
$uploads_base = 'assets/mdata/' . $customer_assets . '/uploads/';
$img_placeholder = site_url($uploads_base . 'no_image.png');

$cms_image_urls = function ($image) use ($uploads_base) {
    $base_rel = rtrim($uploads_base, '/') . '/';
    $base_abs = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $base_rel);
    $thumbs_abs = rtrim($base_abs, '/\\') . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR;

    $file = ($image !== '' && $image !== null) ? trim((string) $image) : 'no_image.png';
    $thumb_abs = $thumbs_abs . $file;
    $full_abs = rtrim($base_abs, '/\\') . DIRECTORY_SEPARATOR . $file;
    if (!is_file($thumb_abs) && !is_file($full_abs)) {
        $file = 'no_image.png';
    }

    return array(
        'thumb' => site_url($base_rel . 'thumbs/' . $file),
        'full' => site_url($base_rel . $file),
        'placeholder' => site_url($base_rel . 'no_image.png'),
    );
};
?>

<div class="ws-manage-products-container cms-catalog-page">

    <div class="cms-mini-stats" role="list">
        <div class="cms-mini-stat" role="listitem">
            <div class="cms-mini-stat-label">Total Categories</div>
            <div class="cms-mini-stat-value is-primary"><?= number_format($stat_total_categories) ?></div>
        </div>
        <div class="cms-mini-stat" role="listitem">
            <div class="cms-mini-stat-label">Total Products</div>
            <div class="cms-mini-stat-value"><?= number_format($stat_total_products) ?></div>
        </div>
        <div class="cms-mini-stat" role="listitem">
            <div class="cms-mini-stat-label">Active in E-Shop</div>
            <div class="cms-mini-stat-value is-success"><?= number_format($stat_active_products) ?></div>
        </div>
        <div class="cms-mini-stat" role="listitem">
            <div class="cms-mini-stat-label">Hidden from E-Shop</div>
            <div class="cms-mini-stat-value is-muted"><?= number_format($stat_hidden_products) ?></div>
        </div>
    </div>

    <div class="cms-catalog-grid">
        <div class="ws-card-box cms-catalog-card cms-catalog-card--categories">
            <div class="ws-card-header">
                <h3><i class="fa fa-folder-open-o"></i> Webshop Categories</h3>
            </div>
            <div class="ws-card-body has-table">
                <div class="cms-table-wrap cms-dt-panel cms-categories-dt-panel">
                    <table class="ws-modern-table" id="cmsCatalogCategoriesTable" width="100%">
                        <thead>
                            <tr>
                                <th class="col-active">Active</th>
                                <th class="col-img">Image</th>
                                <th class="col-name">Name</th>
                                <th class="col-actions">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (is_array($categories)) {
                                foreach ($categories as $category) {
                                    $checked = $cat_is_eshop((int) $category['id']) ? ' checked="checked" ' : '';
                                    $active_row_class  = $category['id'] == $category_id ? ' bg-active-row ' : '';
                                    $cat_img = isset($category['image']) ? $category['image'] : '';
                                    $image_urls = $cms_image_urls($cat_img);
                                    $thumb_src = $image_urls['thumb'];
                                    $full_src = $image_urls['full'];
                                    $in_eshop = $cat_is_eshop((int) $category['id'])
                                        ? '<a href="' . $catalog_manage_url . '/' . (int) $category['id'] . '" class="ws-badge-active" title="View products"><i class="fa fa-list"></i></a>'
                                        : '<span class="ws-badge-inactive" title="Disabled in e-shop"><i class="fa fa-ban"></i></span>';
                                    ?>
                                    <tr class="<?= trim($active_row_class) ?>">
                                        <td class="col-active">
                                            <input type="checkbox" name="categories[]" value="<?= $category['id'] ?>" <?= $checked ?> parent="0" class="checkbox eshop_categories" />
                                        </td>
                                        <td class="col-img">
                                            <span class="cms-thumb-wrap">
                                                <img src="<?= htmlspecialchars($thumb_src, ENT_QUOTES, 'UTF-8') ?>"
                                                     data-fallback="<?= htmlspecialchars($full_src, ENT_QUOTES, 'UTF-8') ?>"
                                                     data-placeholder="<?= htmlspecialchars($img_placeholder, ENT_QUOTES, 'UTF-8') ?>"
                                                     width="44" height="44" alt="" class="ws-product-img cms-img-fallback" loading="lazy" />
                                            </span>
                                        </td>
                                        <td class="col-name"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="col-actions eshop_category_<?= $category['id'] ?>"><?= $in_eshop ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ws-card-box cms-catalog-card cms-catalog-card--products">
            <div class="ws-card-header">
                <div>
                    <h3><i class="fa fa-cubes"></i> Subcategories &amp; Products</h3>
                    <p class="cms-card-subtitle">Select a category on the left · Click a product row to view full details</p>
                </div>
            </div>
            <div class="ws-card-body has-table">
                <?php if (!empty($subcategories)) { ?>
                    <div class="ws-subcat-container">
                        <div class="ws-subcat-title"><i class="fa fa-tags"></i> Filter by subcategory</div>
                        <div class="ws-subcat-chips-list">
                            <?php
                            foreach ($subcategories as $subcategory) {
                                $sid        = (int) $subcategory['id'];
                                $pid        = (int) $subcategory['parent_id'];
                                $subchecked = $cat_is_eshop($sid) ? ' checked="checked" ' : '';
                                $active_chip_class = isset($subcategory['id']) && $subcategory['id'] == $category_id ? ' bg-active-chip' : '';
                                $sname      = isset($subcategory['name']) ? $subcategory['name'] : '';
                                $sub_img = isset($subcategory['image']) ? $subcategory['image'] : '';
                                $sub_urls = $cms_image_urls($sub_img);
                                ?>
                                <label class="ws-subcat-chip-item <?= $active_chip_class ?>" for="categories_<?= $sid ?>">
                                    <input type="checkbox" name="categories[]" value="<?= $sid ?>" <?= $subchecked ?>
                                           parent="<?= $pid ?>" class="checkbox eshop_categories parent_<?= $pid ?>" id="categories_<?= $sid ?>" />
                                    <?php if ($sub_img) { ?>
                                    <img src="<?= htmlspecialchars($sub_urls['thumb'], ENT_QUOTES, 'UTF-8') ?>"
                                         data-fallback="<?= htmlspecialchars($sub_urls['full'], ENT_QUOTES, 'UTF-8') ?>"
                                         data-placeholder="<?= htmlspecialchars($img_placeholder, ENT_QUOTES, 'UTF-8') ?>"
                                         class="ws-subcat-chip-img cms-img-fallback" alt="" loading="lazy" />
                                    <?php } ?>
                                    <span><?= htmlspecialchars($sname, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="cms-table-wrap cms-dt-panel">
                    <table class="ws-modern-table cms-catalog-products-table" id="cmsCatalogProductsTable" width="100%">
                        <thead>
                            <tr>
                                <th class="col-active">Active</th>
                                <th class="col-img">Image</th>
                                <th class="col-code">Code</th>
                                <th>Product Name</th>
                                <th class="col-storage">Storage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (is_array($products)) {
                                foreach ($products as $product) {
                                    $pchecked      = $prod_is_eshop(isset($product->in_eshop) ? $product->in_eshop : 0) ? ' checked="checked" ' : '';
                                    $product_image = (isset($product->image) && $product->image !== '') ? $product->image : 'no_image.png';
                                    $product_urls = $cms_image_urls($product_image);
                                    $thumb_src = $product_urls['thumb'];
                                    $full_src = $product_urls['full'];
                                    $cid           = isset($product->category_id) ? (int) $product->category_id : 0;
                                    $scid          = isset($product->subcategory_id) ? (int) $product->subcategory_id : 0;
                                    $row_hidden    = '';
                                    if (!$cat_is_eshop($cid) || ($scid > 0 && !$cat_is_eshop($scid))) {
                                        $row_hidden = ' style="display:none;"';
                                    }
                                    $code          = isset($product->code) ? $product->code : '';
                                    $name          = isset($product->name) ? $product->name : '';
                                    $storage       = isset($product->storage_type) ? trim($product->storage_type) : '';
                                    $pid           = (int) $product->id;

                                    $storage_class = 'ws-storage-default';
                                    $storage_key = strtolower($storage);
                                    if ($storage_key === 'normal') {
                                        $storage_class = 'ws-storage-normal';
                                    } elseif ($storage_key === 'cold') {
                                        $storage_class = 'ws-storage-cold';
                                    } elseif ($storage_key === 'ambient') {
                                        $storage_class = 'ws-storage-ambient';
                                    } elseif ($storage_key === 'packed') {
                                        $storage_class = 'ws-storage-packed';
                                    }
                                    ?>
                                    <tr class="cms-product-row prdcat_<?= $cid ?> prdsubcat_<?= $scid ?>"<?= $row_hidden ?> data-product-id="<?= $pid ?>">
                                        <td class="col-active">
                                            <input type="checkbox" name="products[]" value="<?= $pid ?>" <?= $pchecked ?> variant="0" class="checkbox eshop_product prd_chk" />
                                        </td>
                                        <td class="col-img">
                                            <span class="cms-thumb-wrap">
                                                <img src="<?= htmlspecialchars($thumb_src, ENT_QUOTES, 'UTF-8') ?>"
                                                     data-fallback="<?= htmlspecialchars($full_src, ENT_QUOTES, 'UTF-8') ?>"
                                                     data-placeholder="<?= htmlspecialchars($img_placeholder, ENT_QUOTES, 'UTF-8') ?>"
                                                     width="48" height="48" alt="" class="ws-product-img cms-img-fallback" loading="lazy" />
                                            </span>
                                        </td>
                                        <td class="col-code"><span class="cms-product-code"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="col-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="col-storage">
                                            <span class="ws-storage-badge <?= $storage_class ?>">
                                                <?= htmlspecialchars($storage ? $storage : 'Standard', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr class="cms-empty-row">
                                    <td colspan="5" class="cms-empty-cell">
                                        <i class="fa fa-hand-o-left"></i>
                                        <p>Select a category from the left panel to load products.</p>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
