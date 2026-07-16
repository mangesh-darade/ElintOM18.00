<?php
/**
 * Urban Piper — catalogue / store / API smoke for user checklist
 * Run: php phase4_urban_piper_catalogue_api_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_up_cat_api_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if (!$identity || !$password) {
    echo "Usage: php phase4_urban_piper_catalogue_api_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$storeId = 2;
$catId = 4;
$productId = 782;
$storeRef = '1731913080-2';
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id, ref_id FROM sma_up_stores ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $row = $q->fetch_assoc();
            $storeId = (int) $row['id'];
            $storeRef = $row['ref_id'];
        }
        $q = $m->query("SELECT category_id FROM sma_up_stores_categories WHERE store_id=$storeId ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $catId = (int) $q->fetch_assoc()['category_id'];
        }
        $q = $m->query("SELECT product_id FROM sma_up_products_platform WHERE up_store_id=$storeId AND add_status=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $productId = (int) $q->fetch_assoc()['product_id'];
        }
    }
} catch (Throwable $e) {
}

echo "Using store_id=$storeId cat_id=$catId product_id=$productId ref=$storeRef\n\n";

// --- Screens (user checklist) ---
foreach ([
    'Settings' => '/urban_piper/settings',
    'Manage Stores' => '/urban_piper/store_info',
    'Add Store' => '/urban_piper/add_store',
    'Edit Store' => "/urban_piper/update_store/$storeId",
    'Manage Catalogue' => '/urban_piper/product_platform',
    'Category' => "/urban_piper/category/$storeId",
    'Store Products' => "/urban_piper/platfrom_product_list/$storeId/$catId",
    'Global Products' => '/urban_piper/product',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

// --- AJAX / catalogue actions ---
$r = phase4_httpRequest("$base/urban_piper/getCategories/$storeId", $cookieFile, null, $ajaxHdr);
phase4_check('getCategories AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));

$r = phase4_httpRequest("$base/urban_piper/getproductplatform/$storeId/$catId", $cookieFile, null, $ajaxHdr);
phase4_check('getproductplatform AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));

$r = phase4_httpRequest("$base/urban_piper/importStoreCategory/$storeId", $cookieFile, null, $ajaxHdr);
$okImport = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
$snip = substr(preg_replace('/\s+/', ' ', $r['body']), 0, 180);
phase4_check('Import Category', $okImport, 'HTTP ' . $r['code'] . ' body=' . $snip);

$r = phase4_httpRequest("$base/urban_piper/store_platform_list", $cookieFile, null, $ajaxHdr);
phase4_check('store_platform_list', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

// Deactivate store — expects JSON (UP API may fail); must not 500
$r = phase4_httpRequest("$base/urban_piper/action/Store_Deactivate/$storeId/Disable", $cookieFile, null, $ajaxHdr);
$okDeact = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
$snip = substr(preg_replace('/\s+/', ' ', $r['body']), 0, 220);
phase4_check('Deactivate Store (Store_Deactivate)', $okDeact, 'HTTP ' . $r['code'] . ' body=' . $snip);

// Change product status — UP API may error JSON; must not 500
$r = phase4_httpRequest(
    "$base/urban_piper/action/Single_Store_Product/$productId/$storeId/UP_Product_status?action=Disable",
    $cookieFile,
    null,
    $ajaxHdr
);
$okProd = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
$snip = substr(preg_replace('/\s+/', ' ', $r['body']), 0, 220);
phase4_check('Change Product Status', $okProd, 'HTTP ' . $r['code'] . ' body=' . $snip);

// --- Public APIs / webhooks (no auth / CSRF excluded where configured) ---
$apiCookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_up_api_cookies.txt';
@unlink($apiCookie);

// api_key_add — POST without changing key (empty would wipe — send placeholder then note)
// Safer: POST with invalid empty and see no fatal, OR skip write. Read current and re-post same from DB.
$currentKey = '';
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query('SELECT api_key FROM sma_up_settings WHERE id=1 LIMIT 1');
        if ($q && $q->num_rows) {
            $currentKey = $q->fetch_assoc()['api_key'];
        }
    }
} catch (Throwable $e) {
}

$ch = curl_init("$base/urban_piper/api_key_add");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['api_key' => $currentKey ?: 'test-key-dont-use']),
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);
$body = curl_exec($ch) ?: '';
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
phase4_check('API api_key_add', $code === 200 && !phase4_hasPhpIssue($body), "HTTP $code body=" . substr($body, 0, 120));

// storescallback — minimal JSON
$ch = curl_init("$base/urban_piper/storescallback");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => '{}',
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$body = curl_exec($ch) ?: '';
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
phase4_check('API storescallback', $code === 200 && !phase4_hasPhpIssue($body), "HTTP $code body=" . substr(preg_replace('/\s+/', ' ', $body), 0, 160));

// catalogueingestioncallback
$ch = curl_init("$base/urban_piper/catalogueingestioncallback");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['status' => 'success', 'reference' => 'phase4-test']),
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$body = curl_exec($ch) ?: '';
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
phase4_check('API catalogueingestioncallback', $code === 200 && !phase4_hasPhpIssue($body), "HTTP $code body=" . substr(preg_replace('/\s+/', ' ', $body), 0, 160));

// inventorycallback
$ch = curl_init("$base/urban_piper/inventorycallback");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => '{}',
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$body = curl_exec($ch) ?: '';
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
phase4_check('API inventorycallback', $code === 200 && !phase4_hasPhpIssue($body), "HTTP $code body=" . substr(preg_replace('/\s+/', ' ', $body), 0, 160));

// add_order — existing customer + real sma_products.code; payment is top-level
$productCode = '25935977'; // Samosa
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT code FROM sma_products WHERE id=12 LIMIT 1");
        if ($q && $q->num_rows) {
            $productCode = $q->fetch_assoc()['code'];
        }
    }
} catch (Throwable $e) {
}

$upOrderId = 900000000 + (int) (time() % 100000);
$orderPayload = [
    'customer' => [
        'name' => 'Walk In Customer',
        'phone' => '9990180001',
        'email' => 'walkin-up-test@example.com',
        'address' => [
            'city' => 'Pune',
            'is_guest_mode' => false,
            'landmark' => 'Test',
            'latitude' => '18.52',
            'longitude' => '73.85',
            'line_1' => 'Test Line 1',
            'line_2' => 'Test Line 2',
            'pin' => '411001',
            'sub_locality' => 'Test',
            'tag' => 'home',
        ],
    ],
    'payment' => [
        'option' => 'paid',
        'amount' => 100,
    ],
    'order' => [
        'details' => [
            'order_state' => 'Placed',
            'id' => $upOrderId,
            'channel' => 'zomato',
            'created' => (int) (microtime(true) * 1000),
            'delivery_datetime' => (int) (microtime(true) * 1000) + 3600000,
            'order_subtotal' => 100,
            'total_external_discount' => 0,
            'discount' => 0,
            'item_level_total_taxes' => 0,
            'order_level_total_taxes' => 0,
            'item_level_total_charges' => 0,
            'order_level_total_charges' => 0,
            'total_charges' => 0,
            'order_total' => 100,
            'instructions' => 'phase4 catalogue api test',
            'order_type' => 'delivery',
            'coupon' => null,
        ],
        'next_state' => 'Acknowledged',
        'store' => [
            'merchant_ref_id' => $storeRef,
        ],
        'items' => [
            [
                'id' => 1,
                'title' => 'Test Item',
                'merchant_id' => $productCode,
                'price' => 100,
                'quantity' => 1,
                'total' => 100,
                'discount' => 0,
                'charges' => [],
                'taxes' => [],
                'options_to_add' => [],
            ],
        ],
    ],
];

$ch = curl_init("$base/urban_piper/add_order");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($orderPayload),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$body = curl_exec($ch) ?: '';
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$issue = phase4_hasPhpIssue($body);
$snip = substr(preg_replace('/\s+/', ' ', $body), 0, 250);
$okAdd = $code === 200 && !$issue;
phase4_check('API add_order (no PHP fatal)', $okAdd, "HTTP $code body=$snip");

// Soft-check: JSON status success or already exists (not required for PASS above)
$decoded = json_decode($body, true);
if (is_array($decoded) && isset($decoded['status'])) {
    phase4_check('API add_order status field', in_array($decoded['status'], ['success', 'error'], true), 'status=' . $decoded['status'] . ' msg=' . ($decoded['msg'] ?? ''));
}

echo "\n=== UP catalogue/API smoke | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
