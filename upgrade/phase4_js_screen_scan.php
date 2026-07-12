<?php
/**
 * Phase 4 — Module-wise screen JS/render scan (logged-in)
 * Detects: HTTP errors, PHP fatals/warnings in HTML (break JS), missing/broken script src.
 *
 * Run: php phase4_js_screen_scan.php Admin "Admin@554"
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);
require __DIR__ . '/phase4_test_lib.php';

$base = PHASE4_BASE_URL;
list($identity, $password) = phase4_credentials();
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_js_screen_scan.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$moduleIssues = [];

if (!$identity || !$password) {
    phase4_usage('phase4_js_screen_scan.php');
    exit(1);
}

function phase4_extractScriptSrcs($html, $base)
{
    $srcs = [];
    $html = preg_replace('/<!--[\s\S]*?-->/', '', $html);
    if (preg_match_all('/<script[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
        foreach ($m[1] as $src) {
            if (strpos($src, '//') === 0) {
                continue;
            }
            if (strpos($src, 'http') !== 0) {
                $src = rtrim($base, '/') . '/' . ltrim($src, '/');
            }
            $srcs[$src] = true;
        }
    }
    return array_keys($srcs);
}

function phase4_headStatus($url, $cookieFile)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 PHPUpgradeJsScan',
    ]);
    curl_exec($ch);
    return (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
}

function phase4_scanScreen($module, $label, $path, $base, $cookieFile)
{
    global $phase4_fail, $phase4_pass, $moduleIssues;

    $r = phase4_httpGetFollow("$base$path", $cookieFile);
    $issues = [];

    if ($r['code'] !== 200) {
        $issues[] = "HTTP {$r['code']}";
    }
    if (phase4_hasPhpIssue($r['body'])) {
        if (preg_match('/(Fatal error|Parse error|Uncaught (Error|TypeError)).{0,120}/', $r['body'], $mm)) {
            $issues[] = 'PHP: ' . trim(strip_tags($mm[0]));
        } else {
            $issues[] = 'PHP warning/notice in HTML (can break JS/DOM)';
        }
    }
    if (stripos($r['body'], '<script') === false && strlen($r['body']) > 500 && stripos($r['body'], '<html') !== false) {
        $issues[] = 'No <script> tags in full page (layout/JS may not load)';
    }
    if (preg_match('/<script[^>]*>[\s\S]{0,800}?(Undefined variable|Undefined array key|Trying to access array offset on null)/i', $r['body'], $jm)) {
        $issues[] = 'PHP error inside/near script block';
    }

    $brokenJs = [];
    foreach (phase4_extractScriptSrcs($r['body'], $base) as $src) {
        if (strpos($src, '/pos/js/') !== false || strpos($src, '/themes/default/assets/') !== false) {
            $code = phase4_headStatus($src, $cookieFile);
            if ($code >= 400 || $code === 0) {
                $brokenJs[] = basename(parse_url($src, PHP_URL_PATH)) . " ($code)";
            }
        }
    }
    if ($brokenJs) {
        $issues[] = 'Broken JS: ' . implode(', ', array_slice($brokenJs, 0, 3));
    }

    $ok = empty($issues);
    phase4_check("[$module] $label", $ok, $ok ? "HTTP 200" : implode(' | ', $issues));
    if (!$ok) {
        if (!isset($moduleIssues[$module])) {
            $moduleIssues[$module] = [];
        }
        $moduleIssues[$module][] = "$label ($path): " . implode(' | ', $issues);
    }
    return $ok;
}

// Module => [ label => path ] — list + add screens per upgraded module
$screens = [
    '1 Auth' => [
        'Login' => '/login',
        'Users list' => '/auth/users',
        'Add user' => '/auth/create_user',
    ],
    '2 Welcome' => [
        'Dashboard' => '/welcome',
    ],
    '3 POS' => [
        'POS main' => '/pos',
        'Open register' => '/pos/open_register',
    ],
    '4 Sales' => [
        'Sales list' => '/sales',
        'Add sale' => '/sales/add',
    ],
    '5 Products' => [
        'Products list' => '/products',
        'Add product' => '/products/add',
    ],
    '6 Purchases' => [
        'Purchases list' => '/purchases',
        'Add purchase' => '/purchases/add',
    ],
    '7 Reports' => [
        'Sales report' => '/reports/sales',
        'Products report' => '/reports/products',
    ],
    '8 Customers' => [
        'Customers list' => '/customers',
        'Add customer' => '/customers/add',
    ],
    '9 Suppliers' => [
        'Suppliers list' => '/suppliers',
        'Billers list' => '/billers',
    ],
    '10 Quotes' => [
        'Quotes list' => '/quotes',
        'Add quote' => '/quotes/add',
    ],
    '11 Transfers' => [
        'Transfers list' => '/transfers',
        'Add transfer' => '/transfers/add',
    ],
    '12 Restaurant' => [
        'Order taking' => '/Restaurant_Order_Taking',
    ],
    '13 Production' => [
        'Dashboard' => '/Production_Unit',
        'KOT' => '/Production_Unit/kot?productionUnits=1',
    ],
    '14 Webshop' => [
        'Webshop' => '/webshop',
    ],
    '15 Webshop Settings' => [
        'Settings' => '/webshop_settings',
    ],
    '16 Eshop' => [
        'Eshop' => '/eshop',
    ],
    '17 Shop' => [
        'Shop' => '/shop',
    ],
    '18 System Settings' => [
        'Settings' => '/system_settings',
    ],
    '19 Attendance' => [
        'Attendance' => '/attendance',
    ],
    '20 Leads' => [
        'Leads list' => '/leads',
    ],
    '21 Service Requests' => [
        'List' => '/service_requests',
    ],
    '22 Urban Piper' => [
        'Settings' => '/urban_piper/settings',
    ],
];

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ($screens as $module => $paths) {
    foreach ($paths as $label => $path) {
        phase4_scanScreen($module, $label, $path, $base, $cookieFile);
        usleep(80000);
    }
}

echo "\n=== JS/Screen scan | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";

if ($moduleIssues) {
    echo "\n--- Modules with JS/render issues ---\n";
    foreach ($moduleIssues as $mod => $list) {
        echo "\n$mod:\n";
        foreach ($list as $line) {
            echo "  - $line\n";
        }
    }
} else {
    echo "\nAll scanned module screens: no JS/render blockers detected.\n";
}

exit($phase4_fail > 0 ? 1 : 0);
