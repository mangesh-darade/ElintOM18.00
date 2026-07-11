<?php
/**
 * Phase 4 — Production Unit deep-link tests
 * Run: php phase4_production_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_production_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/Production_Unit';

if (!$identity || !$password) {
    echo "Usage: php phase4_production_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$warehouseId = 1;
$orderId = null;
$m = @new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m && !$m->connect_error) {
    $q = $m->query('SELECT id FROM sma_warehouses ORDER BY id ASC LIMIT 1');
    if ($q && $q->num_rows) {
        $warehouseId = (int) $q->fetch_assoc()['id'];
    }
    foreach (['sma_procurement_orders', 'sma_production_unit_orders', 'sma_procurement_order'] as $tbl) {
        $q = $m->query("SHOW TABLES LIKE '$tbl'");
        if ($q && $q->num_rows) {
            $q2 = $m->query("SELECT id FROM $tbl ORDER BY id DESC LIMIT 1");
            if ($q2 && $q2->num_rows) {
                $orderId = (int) $q2->fetch_assoc()['id'];
                break;
            }
        }
    }
}

foreach ([
    'inventory/{id}' => "$prefix/inventory/$warehouseId",
    'inventory alert_qty' => "$prefix/inventory/$warehouseId/1",
    'kot productionUnits' => "$prefix/kot?productionUnits=$warehouseId",
    'add_product screen' => "$prefix/add_product",
    'vendor stock' => '/Variant_bill_of_materials/vendor_stock',
] as $label => $path) {
    phase4_linkGet($base, "Production $label", $path, $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base$prefix/getProducts?warehouse_id=$warehouseId", $cookieFile);
phase4_check('getProducts deep link', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'warehouse_id=' . $warehouseId);

if ($orderId) {
    phase4_linkGet($base, 'Production kot_by_order', "$prefix/kot_by_order/$orderId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Production kot_by_order', true, 'skipped — no procurement order id in DB');
}

echo "\n=== Production Unit deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
