<?php
/**
 * Phase 4 — Purchases return flow: return_purchase screen, submit, view_return
 * Run: php phase4_purchases_return_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_purchases_return_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

function purchaseDb()
{
    static $pdo;
    if ($pdo) {
        return $pdo;
    }
    $_SERVER['HTTP_HOST'] = 'localhost';
    define('BASEPATH', true);
    require PHPUPGRADE_ROOT . '/app/config/database.php';
    $c = $db['default'];
    $pdo = new PDO("mysql:host={$c['hostname']};dbname={$c['database']}", $c['username'], $c['password']);
    return $pdo;
}

if (!$identity || !$password) {
    echo "Usage: php phase4_purchases_return_deep_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$pdo = purchaseDb();
$poId = (int) $pdo->query("SELECT id FROM sma_purchases WHERE status IN ('received','partial') AND (return_id IS NULL OR return_id = 0) ORDER BY id DESC LIMIT 1")->fetchColumn();
phase4_check('Find returnable PO', $poId > 0, 'purchase_id=' . ($poId ?: 'none'));

if ($poId) {
    phase4_linkGet($base, 'Return purchase screen', "/purchases/return_purchase/$poId", $cookieFile);

    $r = phase4_httpGetFollow("$base/purchases/return_purchase/$poId", $cookieFile);
    $token = phase4_extractCsrf($r['body']);
    $items = null;
    if (preg_match("/localStorage\.setItem\('reitems',\s*JSON\.stringify\((.+?)\)\);/s", $r['body'], $m)) {
        $items = json_decode($m[1], true);
    }
    $first = is_array($items) ? reset($items) : null;
    $row = is_array($first) && !empty($first['row']) ? (is_array($first['row']) ? $first['row'] : (array) $first['row']) : null;
    phase4_check('Parse return items', $row && !empty($row['code']), $row ? 'code=' . $row['code'] : 'no items');

    if ($row && $token) {
        $qty = 1;
        $cost = (float) ($row['cost'] ?? $row['real_unit_cost'] ?? 10);
        $taxId = (string) ($row['tax_rate'] ?? '0');
        $taxMethod = (string) ($row['tax_method'] ?? '0');
        $unit = (string) ($row['unit'] ?? '1');
        $retPost = [
            'token' => $token,
            'return_surcharge' => '0',
            'note' => 'PHP upgrade return test',
            'order_tax' => '',
            'discount' => '',
            'total_items' => '1',
            'purchase_item_id[]' => (string) ($row['purchase_item_id'] ?? ''),
            'product_id[]' => (string) ($row['id'] ?? ''),
            'product[]' => (string) ($row['code'] ?? ''),
            'product_name[]' => (string) ($row['name'] ?? 'Item'),
            'product_option[]' => (string) ($row['option'] ?? '0'),
            'product_option_color[]' => (string) ($row['option_color'] ?? '0'),
            'part_no[]' => (string) ($row['supplier_part_no'] ?? ''),
            'net_cost[]' => (string) $cost,
            'unit_cost[]' => (string) $cost,
            'real_unit_cost[]' => (string) ($row['real_unit_cost'] ?? $cost),
            'quantity[]' => (string) $qty,
            'product_unit[]' => $unit,
            'product_base_quantity[]' => (string) $qty,
            'product_tax[]' => $taxId,
            'product_discount[]' => '0',
            'tax_method[]' => $taxMethod,
            'batch_number[]' => (string) ($row['batch_number'] ?? ''),
            'expiry[]' => (string) ($row['expiry'] ?? ''),
            'add_return' => 'Submit',
        ];

        $sr = phase4_httpRequest("$base/purchases/return_purchase/$poId", $cookieFile, http_build_query($retPost), [
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: http://localhost/ElintOM18.00/purchases/return_purchase/' . $poId,
        ]);
        $retOk = in_array($sr['code'], [302, 303], true) && stripos($sr['location'] ?? '', 'purchases') !== false;
        $detail = "HTTP {$sr['code']}";
        if ($retOk) {
            $detail .= ' → ' . $sr['location'];
        } elseif (phase4_hasPhpIssue($sr['body'])) {
            preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,150}/', $sr['body'], $em);
            $detail = trim(strip_tags($em[0] ?? $detail));
        }
        phase4_check('Return purchase submit', $retOk, $detail);

        $returnId = (int) $pdo->query("SELECT return_id FROM sma_purchases WHERE id = $poId")->fetchColumn();
        if (!$returnId) {
            $returnId = (int) $pdo->query("SELECT id FROM sma_purchases WHERE purchase_id = $poId AND status = 'returned' ORDER BY id DESC LIMIT 1")->fetchColumn();
        }
        if ($returnId) {
            phase4_linkGet($base, 'view_return', "/purchases/view_return/$returnId", $cookieFile);
            phase4_linkGet($base, 'return modal_view', "/purchases/modal_view/$returnId", $cookieFile);
        } else {
            phase4_check('view_return deep link', false, 'no return_id after submit');
            phase4_check('return modal_view', false, 'skipped');
        }
    } else {
        phase4_check('Return purchase submit', false, 'skipped');
        phase4_check('view_return deep link', false, 'skipped');
        phase4_check('return modal_view', false, 'skipped');
    }
} else {
    phase4_check('Return purchase screen', false, 'skipped');
    phase4_check('Parse return items', false, 'skipped');
    phase4_check('Return purchase submit', false, 'skipped');
    phase4_check('view_return deep link', false, 'skipped');
    phase4_check('return modal_view', false, 'skipped');
}

echo "\n=== Purchases return deep | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
