<?php
/**
 * Probe All Modules sheet RED screens (GET/AJAX only first pass).
 * Syntax upgrade test harness — no business assertions beyond HTTP + no PHP fatal.
 */
require __DIR__ . '/phase4_test_lib.php';

$base = PHASE4_BASE_URL;
$c = sys_get_temp_dir() . '/red_screens.txt';
@unlink($c);
$phase4_fail = 0;
$phase4_pass = 0;
$phase4_skip = 0;

phase4_check('Login', phase4_login($base, 'Admin', 'Admin@554', $c));

$page = phase4_httpGetFollow($base . '/products', $c);
$token = phase4_extractCsrf($page['body']);

// Resolve real IDs from DataTables
$productId = null;
$saleId = null;
$deliveryId = null;
$transferId = null;
$userId = null;
$employeeId = null;
$variantId = null;
$billerId = null;
$storeId = null;

$dt = phase4_httpRequest($base . '/products/getProducts', $c, phase4_dtPost($token));
$productId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/sales/getSales', $c, phase4_dtPost($token));
$saleId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/sales/getDeliveries', $c, phase4_dtPost($token));
$deliveryId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/transfers/getTransfers/?v=1', $c, phase4_dtPost($token));
$transferId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/auth/getUsers', $c, phase4_dtPost($token));
$userId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/employees/get_employees', $c, phase4_dtPost($token));
$employeeId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/system_settings/getVariants', $c, phase4_dtPost($token));
$variantId = phase4_firstDtId($dt['body']);

$dt = phase4_httpRequest($base . '/billers/getBillers', $c, phase4_dtPost($token));
$billerId = phase4_firstDtId($dt['body']);

echo "IDs product=$productId sale=$saleId delivery=$deliveryId transfer=$transferId user=$userId emp=$employeeId var=$variantId biller=$billerId\n";

function red_get($label, $path, $cookie, $codes = [200, 302, 303])
{
    global $base, $phase4_pass, $phase4_fail;
    $r = phase4_httpGetFollow($base . $path, $cookie);
    $ok = in_array($r['code'], $codes, true) && !phase4_hasPhpIssue($r['body']);
    // CI access denied / soft redirect pages still OK if no PHP error
    if (!$ok && $r['code'] == 200 && stripos($r['body'], 'access denied') !== false) {
        $ok = !phase4_hasPhpIssue($r['body']);
    }
    phase4_check($label, $ok, 'HTTP ' . $r['code'] . ' ' . substr(preg_replace('/\s+/', ' ', $r['body']), 0, 90));
    return $r;
}

function red_ajax($label, $path, $cookie)
{
    global $base;
    $r = phase4_httpGetFollow($base . $path, $cookie);
    $ok = $r['code'] == 200 && !phase4_hasPhpIssue($r['body']);
    // JSON or HTML-partial both fine if no PHP issue
    phase4_check($label, $ok, 'HTTP ' . $r['code'] . ' ' . substr(preg_replace('/\s+/', ' ', $r['body']), 0, 90));
    return $r;
}

// -------- PRODUCTS --------
red_get('Products list', '/products', $c);
if ($productId) {
    red_get('Edit Product', '/products/edit/' . $productId, $c);
    red_get('Add Favourite', '/products/favourite/?product_id=' . $productId, $c);
    red_get('Manage Batches Popup', '/products/add_batch?p=' . $productId, $c);
    red_get('Edit Favourite Product', '/products/edit/' . $productId, $c);
} else {
    echo "[SKIP] no product id\n";
    $phase4_skip++;
}
red_get('Update Price Popup', '/products/update_price', $c);
red_get('Raw Materials', '/products/rawMaterials', $c);
red_get('POS Combo', '/products/poscombo', $c);
red_get('Import CSV page', '/products/import_csv', $c);
red_get('Sample product CSV', '/products/sample_product_csv', $c, [200, 302]);
red_get('Bulk images page', '/products/bulk_images', $c);
red_get('Favourite list', '/products/list_favourite', $c);

// -------- INVENTORY --------
red_get('Qty adjustments', '/products/quantity_adjustments', $c);
red_get('Add adj CSV', '/products/add_adjustment_by_csv', $c);
red_get('Stock Check', '/CheckStock', $c);

// -------- PURCHASES/SUPPLIERS --------
red_get('Suppliers list', '/suppliers', $c);
red_get('Suppliers import CSV', '/suppliers/import_csv', $c);

// -------- SALES --------
red_get('Sales list', '/sales', $c);
if ($saleId) {
    red_get('Invoice modal_view', '/sales/modal_view/' . $saleId, $c);
    red_get('Sale view', '/sales/view/' . $saleId, $c);
    red_get('Sale PDF', '/sales/pdf/' . $saleId, $c);
    red_get('Add delivery modal', '/sales/add_delivery/' . $saleId, $c);
    red_get('Email sale modal', '/sales/email/' . $saleId, $c);
    red_get('Return sale page', '/sales/return_sale/' . $saleId, $c);
    red_get('POS receipt view', '/pos/view/' . $saleId, $c);
} else {
    echo "[SKIP] no sale id\n";
    $phase4_skip++;
}
red_get('Eshop sales', '/eshop_sales/sales', $c);
red_get('POS sales list', '/pos/sales', $c);
red_get('All sale lists', '/sales/all_sale_lists', $c);
red_get('Deliveries list', '/sales/deliveries', $c);
if ($deliveryId) {
    red_get('Edit delivery', '/sales/edit_delivery/' . $deliveryId, $c);
}
red_get('Challans list', '/sales/challans', $c);

// -------- QUOTES / TRANSFERS --------
red_get('Quotes add', '/quotes/add', $c);
red_ajax('Quotes suggestions', '/quotes/suggestions?term=lapt&warehouse_id=1&customer_id=1', $c);
red_get('Transfers list', '/transfers', $c);
red_get('Transfers add', '/transfers/add', $c);
red_ajax('Transfers suggestions', '/transfers/suggestions?term=lapt&warehouse_id=26&warehouse_2=23', $c);
if ($transferId) {
    red_get('Transfers edit', '/transfers/edit/' . $transferId, $c);
}
red_get('Transfer by CSV', '/transfers/transfer_by_csv', $c);
red_get('Transfer add request', '/transfers/add_request', $c);

// -------- PEOPLE --------
red_get('Users list', '/auth/users', $c);
red_get('Billers list', '/billers', $c);
red_get('Employees list', '/employees', $c);
if ($employeeId) {
    red_get('Edit employee', '/employees/edit/' . $employeeId, $c);
}

// -------- JOB WORKS / PROCUREMENT --------
red_get('Variant BOM', '/Variant_bill_of_materials', $c);
red_ajax('BOM get by product', '/Variant_bill_of_materials/GetBomDetailsByProductId?productId=287&job_work_id=3', $c);
red_get('Purchases list', '/purchases', $c);
red_get('Procurement orders', '/Production_Unit/procurementOrders', $c);

// -------- ECOMMERCE / OMNI --------
red_get("Today's Special", '/Todays_Special', $c);
red_get('Holidays list', '/Omnichannel/view_holidays', $c);
red_get('Import holiday page', '/Omnichannel/import_holiday', $c);
red_get('Schedules list', '/Omnichannel/view_schdules', $c);
red_get('Import schedule page', '/Omnichannel/import_schedule', $c);

// -------- URBANPIPER --------
red_get('UP settings', '/urban_piper/settings', $c);
red_get('UP store info', '/urban_piper/store_info', $c);
red_get('UP add store', '/urban_piper/add_store', $c);
red_get('UP product platform', '/urban_piper/product_platform', $c);
red_get('UP products', '/urban_piper/product', $c);

// -------- SETTINGS VARIANTS --------
red_get('Variants list', '/system_settings/variants', $c);
red_get('Add variant modal', '/system_settings/add_variant', $c);
red_get('Manage variants screen', '/system_settings/variant_manage', $c);
if ($variantId) {
    red_get('Edit variant', '/system_settings/edit_variant/' . $variantId, $c);
}

// -------- REPORTS --------
red_get('Sale GST report', '/reports/sales_gst_report', $c);

// -------- POS --------
red_get('POS main', '/pos', $c);
red_get('Recent POS list', '/pos/recent_pos_list', $c);
red_get('Short settings', '/pos/short_setting', $c);
red_ajax('Category tab', '/pos/ajaxcategorydata?category_id=40&seasonId=', $c);
red_ajax('Search product', '/pos/getProductDataByCode/lapt/26', $c);
if ($saleId) {
    // email receipt is POST usually; GET may 404/redirect - just ensure no fatal
    red_get('POS view for email', '/pos/view/' . $saleId, $c);
}
red_get('Stripe balance', '/pos/stripe_balance', $c);
red_get('PayPal balance', '/pos/paypal_balance', $c);

echo "\n=== SUMMARY pass=$phase4_pass fail=$phase4_fail skip=$phase4_skip ===\n";
exit($phase4_fail ? 1 : 0);
