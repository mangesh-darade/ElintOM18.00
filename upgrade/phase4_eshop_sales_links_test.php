<?php
/**
 * Phase 4 — Eshop Sales deep-link tests (Sales → Eshop Sales row actions)
 * Run: php phase4_eshop_sales_links_test.php <identity> <password>
 *
 * Covers: list, receipt popup, view receipt, sale details, payments,
 * add payment, add delivery, edit, pdf, return sale.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_eshop_sales_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_eshop_sales_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/eshop_sales/sales", $cookieFile);
phase4_check('Eshop Sales list', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
$token = phase4_extractCsrf($r['body']);

$r = phase4_httpRequest("$base/eshop_sales/getSales", $cookieFile, phase4_dtPost($token));
$saleId = phase4_firstDtId($r['body']);
phase4_check('eshop getSales AJAX', $r['code'] === 200 && $saleId !== null && !phase4_hasPhpIssue($r['body']), 'sale_id=' . ($saleId ?? 'none'));

if (!$saleId) {
    // Fallback: DB discovery (seed script may have created row)
    try {
        $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
        if ($m && !$m->connect_error) {
            $q = $m->query("SELECT id FROM sma_sales WHERE eshop_sale=1 ORDER BY id DESC LIMIT 1");
            if ($q && $q->num_rows) {
                $saleId = (int) $q->fetch_assoc()['id'];
            }
        }
    } catch (Throwable $e) {
        // optional
    }
}

if ($saleId) {
    $links = [
        'Eshop Receipt Popup (row click)' => ["/pos/view/$saleId/1", [200]],
        'View Receipt' => ["/pos/view/$saleId", [200]],
        'Sale Details' => ["/sales/view/$saleId", [200]],
        'View Payments Modal' => ["/sales/payments/$saleId", [200]],
        'Add Payment Modal' => ["/sales/add_payment/$saleId", [200]],
        'Add Delivery Modal' => ["/sales/add_delivery/$saleId", [200]],
        'Edit Sale' => ["/sales/edit/$saleId", [200]],
        'Download PDF' => ["/sales/pdf/$saleId", [200, 302]],
        'Return Sale' => ["/sales/return_sale/$saleId", [200]],
    ];
    foreach ($links as $label => $cfg) {
        phase4_linkGet($base, $label, $cfg[0], $cookieFile, $cfg[1], $identity, $password);
    }
} else {
    phase4_check('Eshop Sales deep links', false, 'no eshop_sale=1 id (run upgrade/seed_eshop_sale_for_tests.php)');
}

echo "\n=== Eshop Sales deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
