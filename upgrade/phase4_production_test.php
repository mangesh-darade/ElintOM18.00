<?php
/**
 * Phase 4 — Production Unit screen tests
 * Run: php phase4_production_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_production_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];
$prefix = '/Production_Unit';

if (!$identity || !$password) {
    echo "Usage: php phase4_production_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Order dispatch' => $prefix,
    'Procurement orders' => $prefix . '/procurementOrders',
    'Manager dashboard' => $prefix . '/manager_dashboard',
    'Production dashboard' => $prefix . '/production_dashboard',
    'Kitchen dashboard' => $prefix . '/kitchen_user_dashboard',
    'Receive delivery' => $prefix . '/receive_delivery',
    'Ordering history' => $prefix . '/ordering_history',
    'Add product' => $prefix . '/add_product',
    'Inventory' => $prefix . '/inventory',
    'KOT' => $prefix . '/kot?productionUnits=1',
    'KDS' => $prefix . '/kds',
    'KDS display' => $prefix . '/kds_D',
    'Ready to dispatch' => $prefix . '/Ready_To_Dispatch',
    'Bill of material' => '/Bill_of_material',
    'Variant BOM' => '/Variant_bill_of_materials',
    'Dine-in BOM (Recipies)' => '/Recipies',
    'Working orders (New)' => '/Production_Unit_New/working_orders',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$warehouseId = 1;
$m = @new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m && !$m->connect_error) {
    $q = $m->query('SELECT id FROM sma_warehouses ORDER BY id ASC LIMIT 1');
    if ($q && $q->num_rows) {
        $warehouseId = (int) $q->fetch_assoc()['id'];
    }
}

phase4_linkGet($base, 'Inventory by warehouse', $prefix . '/inventory/' . $warehouseId, $cookieFile, [200], $identity, $password);

$r = phase4_httpGetFollow("$base/Bill_of_material", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if (!$token) {
    $r = phase4_httpGetFollow("$base$prefix/procurementOrders", $cookieFile);
    $token = phase4_extractCsrf($r['body']);
}

$r = phase4_httpGetFollow("$base$prefix/getProducts?warehouse_id=$warehouseId", $cookieFile);
$gpJson = json_decode($r['body'], true);
$gpOk = $r['code'] === 200 && is_array($gpJson) && isset($gpJson['aaData']);
phase4_check('getProducts AJAX', $gpOk, 'warehouse_id=' . $warehouseId);

$r = phase4_httpGetFollow("$base$prefix/GetProductsbyCategoriesID?categoriesId=1", $cookieFile);
$catOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
phase4_check('GetProductsbyCategoriesID AJAX', $catOk, 'HTTP ' . $r['code']);

echo "\n=== Production Unit screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
