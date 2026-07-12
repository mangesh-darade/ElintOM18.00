<?php defined('BASEPATH') OR exit('No direct script access allowed');
$details = isset($details) ? $details : array();
$product = isset($details['product']) ? $details['product'] : null;
if (!$product) {
    echo '<div class="alert alert-warning">Product not found.</div>';
    return;
}
$uploads_base = isset($uploads_base) ? $uploads_base : 'assets/uploads/';
$category = isset($details['category']) ? $details['category'] : null;
$subcategory = isset($details['subcategory']) ? $details['subcategory'] : null;
$variants = isset($details['variants']) ? $details['variants'] : array();
$stock_rows = isset($details['stock_rows']) ? $details['stock_rows'] : array();
$total_qty = isset($details['total_qty']) ? (float) $details['total_qty'] : 0;
$gallery = isset($details['gallery']) ? $details['gallery'] : array();
$placeholder_url = site_url($uploads_base . 'no_image.png');
$img_url = function ($file) use ($uploads_base) {
    $file = ($file !== '' && $file !== null) ? $file : 'no_image.png';
    return site_url($uploads_base . $file);
};
?>
<div class="cms-product-modal-body">
    <div class="row">
        <div class="col-sm-5">
            <div class="cms-product-modal-main-img">
                <img src="<?= htmlspecialchars($img_url(isset($gallery[0]) ? $gallery[0] : $product->image), ENT_QUOTES, 'UTF-8') ?>"
                     data-placeholder="<?= htmlspecialchars($placeholder_url, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') ?>"
                     id="cmsProductModalMainImg"
                     class="cms-img-fallback"
                     loading="lazy"
                     onerror="this.onerror=null;this.src=this.getAttribute('data-placeholder');" />
            </div>
        </div>
        <div class="col-sm-7">
            <h4 class="cms-product-modal-title"><?= htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted"><strong>Code:</strong> <?= htmlspecialchars($product->code, ENT_QUOTES, 'UTF-8') ?></p>
            <dl class="cms-product-dl dl-horizontal">
                <dt>Category</dt><dd><?= $category ? htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8') : '—' ?></dd>
                <dt>Subcategory</dt><dd><?= $subcategory ? htmlspecialchars($subcategory->name, ENT_QUOTES, 'UTF-8') : '—' ?></dd>
                <dt>MRP</dt><dd><?= number_format((float) $product->mrp, 2) ?></dd>
                <dt>E-shop price</dt><dd><?= number_format((float) $product->eshop_price, 2) ?></dd>
                <dt>In e-shop</dt><dd><?= (int) $product->in_eshop === 1 ? 'Yes' : 'No' ?></dd>
                <dt>Total stock</dt><dd><?= number_format($total_qty, 2) ?></dd>
            </dl>
        </div>
    </div>
    <?php if (!empty($variants)) { ?>
    <h5 class="cms-product-section-title">Variants</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-condensed ws-modern-table">
            <thead><tr><th>Name</th><th>Qty</th><th>MRP</th><th>E-shop price</th></tr></thead>
            <tbody>
            <?php foreach ($variants as $v) { ?>
                <tr>
                    <td><?= htmlspecialchars(!empty($v['eshop_name']) ? $v['eshop_name'] : $v['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= number_format((float) $v['unit_quantity'], 2) ?></td>
                    <td><?= number_format((float) $v['mrp'], 2) ?></td>
                    <td><?= number_format((float) $v['eshop_price'], 2) ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
    <?php if (!empty($stock_rows)) { ?>
    <h5 class="cms-product-section-title">Stock by warehouse</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-condensed ws-modern-table">
            <thead><tr><th>Warehouse</th><th>Quantity</th></tr></thead>
            <tbody>
            <?php foreach ($stock_rows as $s) { ?>
                <tr><td><?= htmlspecialchars($s['warehouse'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format($s['quantity'], 2) ?></td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
    <?php if (count($gallery) > 0) { ?>
    <h5 class="cms-product-section-title">Images</h5>
    <div class="cms-product-gallery" id="cmsProductGallery">
        <?php foreach ($gallery as $idx => $file) {
            $url = $img_url($file); ?>
        <button type="button" class="cms-product-gallery-thumb<?= $idx === 0 ? ' is-active' : '' ?>" data-src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"
                 data-placeholder="<?= htmlspecialchars($placeholder_url, ENT_QUOTES, 'UTF-8') ?>"
                 class="cms-img-fallback"
                 alt=""
                 loading="lazy"
                 onerror="this.onerror=null;this.src=this.getAttribute('data-placeholder');" />
        </button>
        <?php } ?>
    </div>
    <?php } ?>
</div>
