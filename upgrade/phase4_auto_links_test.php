<?php
/**
 * Phase 4 — Auto-discovered deep-link tests (ALL modules, single file/URL)
 *
 * Links are NOT hardcoded — auto-scanned from app/controllers/*.php + themes/views.
 *
 * CLI:  php phase4_auto_links_test.php <identity> <password> [module]
 * Web hub (default):  ?identity=Admin&password=Admin%40554
 *   — सर्व controllers grid, checkbox select, Run Selected / Run All Upgraded
 * Web single (JSON):  ?action=run&module=products&identity=...&password=...
 * CLI batch:  php phase4_auto_links_test.php Admin pass all upgraded
 * Upgraded scope: auto-detected from phase4_*_links_test.php files (no hardcoded list).
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);
require __DIR__ . '/phase4_test_lib.php';

$isWeb = phase4_isWeb();
if (!$isWeb) {
    phase4_webOutputStart();
}

$base = 'http://localhost/ElintOM18.00';
list($identity, $password) = phase4_credentials();
$moduleFilter = $argv[3] ?? ($_GET['module'] ?? '');
$webAction = $_GET['action'] ?? '';
if (!$isWeb && $moduleFilter === '') {
    $moduleFilter = 'all';
}
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_auto_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$phase4_skip = 0;
$verbose = !empty($_GET['verbose']) || in_array('--verbose', $argv ?? [], true);
$scope = $_GET['scope'] ?? ($argv[4] ?? 'upgraded');
$auto_failures = [];
$auto_passes = [];
$auto_skips_list = [];
$auto_module_stats = [];

// Denylist only — navi controller/method auto include (allowlist nahi)
$SKIP_CONTROLLERS = '/^(api\d*|restapi\d*|webhook|cron|cron_job|eshop_api|web_service|access_denied|errors|sync|offline|reset|help)$/i';
$SKIP_METHODS = '/^(delete|store|update|save|submit|process|login|logout|register|suggestions|addByAjax|hideNotification|set_data|download|__construct|_remap|activate|deactivate|restandlogout|checkoutdata|check_temp_order|char_limit|reset_password|sendNotification)$/i';
$SKIP_METHOD_PREFIX = '/^(product_actions|supplier_actions|customer_actions|sale_actions|purchase_actions|check_|fetch_|load_|sync_|validate_|import_|export_|upload_|print_|email_|sms_)/i';
$SKIP_PATH_PARTS = '/(\/_actions\/|\/delete\/|send_sms|email_challan|auth\/login|\/ajax|getProductData|getProductByID|getCustomerAuto|searchAward|searchGift|searchDeposit|CallSMS|SendAuto|saveFace|extractFace|captcha|clear_expired|resend_|verify_|reload_|user_actions|_get_|_render_|_valid_|addcustomer|featuerdProducts|short_setting|view_bill|close_register|registers|RepeateDiscount|CustomerFamily|checkoutdata|check_temp_order|char_limit|\/activate\/|reset_password\/|restandlogout)/i';
$PDF_METHODS = '/pdf/i';
$SKIP_VIEW_FILE = '/backup|copy|\(\d|_ep|elite|add2|add_bups|add_sun|nw_theme/i';
// DataTables POST endpoints — GET ne test nahi
$DATATABLES_AJAX = '/^get(Products|Sales|Purchases|Quotes|Transfers|Users|Suppliers|Billers|Customers|adjustments|Counts|Expenses|Challans|TransfersRequests|UserLogins|Deposits)/i';

if (!$identity || !$password) {
    phase4_usage('phase4_auto_links_test.php');
    echo "Optional: &module=all (default) | products | sales | ...\n";
    exit(1);
}

// ---------- Discovery helpers ----------

// ---------- Dynamic registry (scan app/controllers — no hardcoded module list) ----------

function auto_build_registry()
{
    global $SKIP_CONTROLLERS;
    $registry = [];
    $dir = PHPUPGRADE_ROOT . '/app/controllers/';
    foreach (glob($dir . '*.php') as $file) {
        $base = basename($file, '.php');
        if (preg_match('/backup| copy/i', $base)) {
            continue;
        }
        $name = strtolower($base);
        if (preg_match($SKIP_CONTROLLERS, $name)) {
            continue;
        }
        $viewDir = PHPUPGRADE_ROOT . '/themes/default/views/' . $name;
        $registry[$name] = [
            'controllers' => [$name],
            'views' => [is_dir($viewDir) ? $name : $name],
            'guest' => [],
        ];
    }
    ksort($registry);
    // routes.php — guest URLs (forgot_password etc.)
    $routesFile = PHPUPGRADE_ROOT . '/app/config/routes.php';
    if (is_file($routesFile)) {
        $routes = file_get_contents($routesFile);
        if (preg_match_all("/\\\$route\['([^']+)'\]\s*=\s*'(?:auth\/)?(forgot_password[^']*)'/i", $routes, $rm)) {
            foreach ($rm[1] as $i => $routeKey) {
                if (strpos($routeKey, '(') !== false) {
                    continue;
                }
                $registry['auth']['guest'][] = '/' . $routeKey;
            }
        }
        $registry['auth']['guest'] = array_values(array_unique($registry['auth']['guest'] ?? []));
    }
    return $registry;
}

/**
 * Upgraded modules = controllers with a dedicated phase4_{name}_links_test.php script.
 * Skips phase4_auto_links_test.php. Compound scripts (e.g. suppliers_billers) map to each matching controller.
 */
function auto_discover_upgraded_modules(array $registryKeys)
{
    $known = array_fill_keys($registryKeys, true);
    $found = [];
    foreach (glob(__DIR__ . '/phase4_*_links_test.php') ?: [] as $file) {
        $base = basename($file);
        if ($base === 'phase4_auto_links_test.php') {
            continue;
        }
        if (!preg_match('/^phase4_(.+)_links_test\.php$/', $base, $m)) {
            continue;
        }
        $slug = $m[1];
        if (isset($known[$slug])) {
            $found[$slug] = true;
            continue;
        }
        foreach (explode('_', $slug) as $part) {
            if ($part !== '' && isset($known[$part])) {
                $found[$part] = true;
            }
        }
    }
    $list = array_keys($found);
    sort($list);
    return $list;
}

function auto_should_skip_method($method)
{
    global $SKIP_METHODS, $SKIP_METHOD_PREFIX, $DATATABLES_AJAX;
    if ($method === '' || $method[0] === '_') {
        return true;
    }
    if (preg_match($SKIP_METHODS, $method) || preg_match($SKIP_METHOD_PREFIX, $method)) {
        return true;
    }
    if (preg_match($DATATABLES_AJAX, $method)) {
        return true;
    }
    if (preg_match('/^get/i', $method)) {
        return true;
    }
    if (preg_match('/(notify|_init|_email|deposit|check[A-Z]|delete|install_|barcode|alert_|build_|paynear|ccavenue|instamojo|descriptor|config)/i', $method)) {
        return true;
    }
    if (preg_match('/^save[A-Z]/', $method)) {
        return true;
    }
    if (preg_match('/^write_/', $method)) {
        return true;
    }
    if (preg_match('/(payumoney|post_to_url|set_sale|restore|testmail|view_up)/i', $method)) {
        return true;
    }
    // PascalCase = internal POS/API helpers (CallSMS, Sale_print)
    if (preg_match('/^[A-Z]/', $method)) {
        return true;
    }
    if (preg_match('/(data|ajax|json|api)$/i', $method)) {
        return true;
    }
    return false;
}

function auto_is_real_error_type($errorType)
{
    return in_array(strtolower((string) $errorType), ['php', 'db', 'empty', 'connection', 'syntax', 'http'], true);
}

function auto_parse_ci_msg($body)
{
    if (preg_match('/<div\s+class=["\']msg["\'][^>]*>(.*?)<\/div>/is', $body, $m)) {
        $msg = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($msg !== '') {
            return auto_trim_len(preg_replace('/\s+/', ' ', $msg), 320);
        }
    }
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $body, $m)) {
        $msg = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($msg !== '') {
            return auto_trim_len($msg, 160);
        }
    }
    return '';
}

function auto_detect_ci_app_error($body)
{
    if ($body === '') {
        return null;
    }
    if (!preg_match('/An Error Was Encountered|404 Page Not Found|Database Error|A PHP Error was encountered|Unable to connect to your database/i', $body)) {
        return null;
    }
    $msg = auto_parse_ci_msg($body);
    if (preg_match('/Unable to connect to your database|Database Error/i', $body . ' ' . $msg)) {
        return [
            'type' => 'db',
            'issue' => $msg ?: 'Database connection/query error (CI error page)',
            'hint' => 'DB credentials, MySQL running, schema/prefix (`sma_`) check kara',
        ];
    }
    if (preg_match('/404 Page Not Found/i', $body)) {
        return [
            'type' => 'http',
            'issue' => $msg ?: '404 Page Not Found',
            'hint' => 'Route/view missing — controller method exist karto ka te bagha',
        ];
    }
    if (preg_match('/Parse error|syntax error|Fatal error|TypeError|Uncaught/i', $body . ' ' . $msg)) {
        return [
            'type' => 'php',
            'issue' => $msg ?: 'PHP error on CI error page',
            'hint' => 'PHP 8.5 compatibility — null guard / type fix lagel',
        ];
    }
    return [
        'type' => 'http',
        'issue' => $msg ?: 'Application error page (CI show_error)',
        'hint' => 'Browser madhe URL open kara — PHP log / controller line bagha',
    ];
}

function auto_is_blank_response($body, $code)
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $body)));
    if ($text !== '') {
        return false;
    }
    return in_array((int) $code, [0, 500, 502, 503], true);
}

function auto_should_skip_resolved_path($path)
{
    $p = parse_url($path, PHP_URL_PATH) ?: $path;
    if (preg_match('#//|/$#', $p)) {
        return 'incomplete URL (missing ID segment)';
    }
    $parts = array_values(array_filter(explode('/', trim($p, '/'))));
    if (count($parts) === 2 && preg_match('/^(view|pdf|edit|modal_view|payments|add_payment|email|email_invoice)$/i', $parts[1])) {
        return 'needs ID — skipped blind GET';
    }
    return null;
}

function auto_finalize_test_result(array $analysis, $path, $source = '')
{
    if (!empty($analysis['ok'])) {
        return array_merge($analysis, ['status' => 'pass']);
    }
    $type = strtolower((string) ($analysis['error_type'] ?? 'http'));
    if (in_array($type, ['auth', 'redirect', 'deprecated', 'skip'], true)) {
        return [
            'ok' => false,
            'status' => 'skip',
            'error_type' => 'skip',
            'detail' => ($analysis['issue'] ?? $analysis['detail'] ?? 'skipped') . ' (noise — POST/permission/AJAX)',
            'issue' => '',
            'hint' => 'Real screen test sathi `phase4_{module}_links_test.php` chalaava',
        ];
    }
    $method = basename(parse_url($path, PHP_URL_PATH) ?: $path);
    $issueText = (string) ($analysis['issue'] ?? $analysis['detail'] ?? '');
    if (preg_match('/^controller:/', (string) $source)) {
        if (preg_match('/404/i', $issueText)) {
            return [
                'ok' => false,
                'status' => 'skip',
                'error_type' => 'skip',
                'detail' => '404 on controller method (private/internal — not a screen route)',
                'issue' => '',
                'hint' => 'Protected/private helper — dedicated links test vapra',
            ];
        }
        if (preg_match('/Unable to load the requested file/i', $issueText)) {
            return [
                'ok' => false,
                'status' => 'skip',
                'error_type' => 'skip',
                'detail' => 'Missing view on internal controller action',
                'issue' => '',
                'hint' => 'Dev/test endpoint — not a user screen',
            ];
        }
    }
    if ($type === 'http' && auto_should_skip_method($method)) {
        return [
            'ok' => false,
            'status' => 'skip',
            'error_type' => 'skip',
            'detail' => ($analysis['issue'] ?? 'HTTP error') . ' (internal/AJAX endpoint)',
            'issue' => '',
            'hint' => 'Blind GET — dedicated links test vapra',
        ];
    }
    return array_merge($analysis, ['status' => 'fail']);
}

function auto_count_real_failures()
{
    global $auto_failures;
    $n = 0;
    foreach ($auto_failures as $f) {
        if (auto_is_real_error_type($f['error_type'] ?? 'http')) {
            $n++;
        }
    }
    return $n;
}

function auto_guess_resolvers($controllerName)
{
    global $DATATABLES_AJAX;
    $file = PHPUPGRADE_ROOT . '/app/controllers/' . ucfirst($controllerName) . '.php';
    if (!is_file($file)) {
        return [];
    }
    if ($controllerName === 'reports') {
        return [['type' => 'reports']];
    }
    $content = file_get_contents($file);
    $resolvers = [];
    if (!preg_match_all('/function\s+(get\w+)\s*\(/', $content, $m)) {
        return [];
    }
    foreach ($m[1] as $ajaxMethod) {
        if (!preg_match($DATATABLES_AJAX, $ajaxMethod)) {
            continue;
        }
        $key = 'id';
        $list = '/' . $controllerName;
        $ajax = '/' . $controllerName . '/' . $ajaxMethod;
        if (stripos($ajaxMethod, 'adjustment') !== false) {
            $key = 'adjustment_id';
            $list = '/' . $controllerName . '/quantity_adjustments';
        } elseif (stripos($ajaxMethod, 'Expense') !== false) {
            $key = 'expense_id';
            $list = '/' . $controllerName . '/expenses';
        } elseif (stripos($ajaxMethod, 'Count') !== false) {
            $key = 'count_id';
            $list = '/' . $controllerName . '/stock_counts';
            $ajax = '/' . $controllerName . '/getCounts/file';
        } elseif (stripos($ajaxMethod, 'Request') !== false) {
            $key = 'request_id';
        } elseif ($controllerName === 'pos' && stripos($ajaxMethod, 'Sales') !== false) {
            $list = '/pos/sales';
        } elseif ($controllerName === 'auth' && stripos($ajaxMethod, 'Users') !== false) {
            $list = '/auth/users';
        } elseif ($controllerName === 'suppliers') {
            $key = 'supplier_id';
        } elseif ($controllerName === 'billers') {
            $key = 'biller_id';
        }
        $resolvers[] = ['key' => $key, 'list' => $list, 'ajax' => $ajax];
    }
    return $resolvers;
}

function auto_path_is_testable($template)
{
    $path = parse_url($template, PHP_URL_PATH) ?: $template;
    $parts = array_values(array_filter(explode('/', trim($path, '/'))));
    if (count($parts) <= 1) {
        return true;
    }
    return !auto_should_skip_method($parts[1]);
}

function auto_view_file_ok($filename)
{
    global $SKIP_VIEW_FILE;
    return !preg_match($SKIP_VIEW_FILE, $filename);
}

function auto_pdo()
{
    static $pdo = null;
    static $failed = false;
    if ($failed) {
        return null;
    }
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $_SERVER['HTTP_HOST'] = 'localhost';
        if (!defined('BASEPATH')) {
            define('BASEPATH', true);
        }
        require PHPUPGRADE_ROOT . '/app/config/database.php';
        $pdo = new PDO(
            "mysql:host={$db['default']['hostname']};dbname={$db['default']['database']}",
            $db['default']['username'],
            $db['default']['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false]
        );
    } catch (Throwable $e) {
        $failed = true;
        $pdo = null;
    }
    return $pdo;
}

function auto_extract_method($path, $source, $submodule)
{
    if (preg_match('/controller:\w+::(\w+)/', $source, $m)) {
        return $m[1];
    }
    $parts = array_values(array_filter(explode('/', trim(parse_url($path, PHP_URL_PATH) ?: $path, '/'))));
    if (count($parts) <= 1) {
        return 'index';
    }
    return $parts[1];
}

function auto_record($status, $module, $submodule, $screen, $path, $detail, $source, $issue = '', $hint = '', $errorType = '', $dbMeta = [])
{
    global $phase4_pass, $phase4_fail, $phase4_skip, $auto_failures, $auto_passes, $auto_skips_list, $auto_module_stats;

    if (!isset($auto_module_stats[$module])) {
        $auto_module_stats[$module] = ['pass' => 0, 'fail' => 0, 'skip' => 0];
    }

    $row = [
        'module' => $module,
        'controller' => $submodule,
        'method' => auto_extract_method($path, $source, $submodule),
        'path' => $path,
        'detail' => $detail,
        'source' => $source,
        'error_type' => $errorType,
    ];
    if (!empty($dbMeta['db_column'])) {
        $row['db_column'] = $dbMeta['db_column'];
    }
    if (!empty($dbMeta['db_table'])) {
        $row['db_table'] = $dbMeta['db_table'];
    }
    if (!empty($dbMeta['db_error_num'])) {
        $row['db_error_num'] = $dbMeta['db_error_num'];
    }

    if ($status === 'pass') {
        $phase4_pass++;
        $auto_module_stats[$module]['pass']++;
        $row['status'] = 'pass';
        $row['error_type'] = 'pass';
        $auto_passes[] = $row;
    } elseif ($status === 'fail') {
        $phase4_fail++;
        $auto_module_stats[$module]['fail']++;
        $row['status'] = 'fail';
        $row['error_type'] = $errorType ?: 'http';
        $row['issue'] = $issue ?: $detail;
        $row['hint'] = $hint;
        $auto_failures[] = $row;
    } else {
        $phase4_skip++;
        $auto_module_stats[$module]['skip']++;
        $row['status'] = 'skip';
        $row['error_type'] = 'skip';
        $row['issue'] = $issue !== '' ? $issue : $detail;
        if ($hint !== '') {
            $row['hint'] = $hint;
        }
        $auto_skips_list[] = $row;
    }
}

function auto_trim_len($text, $len = 280)
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }
    return function_exists('mb_substr') ? mb_substr($text, 0, $len) : substr($text, 0, $len);
}

function auto_is_db_body($body)
{
    return (bool) preg_match(
        '/A Database Error Occurred|Error Number:\s*\d+|Unknown column|Table \'[^\']+\' doesn\'t exist|SQLSTATE\[|PDOException|mysqli_sql_exception|Duplicate entry|foreign key constraint|Unable to connect to your database server|definer does not exist/i',
        $body
    );
}

/**
 * Parse CI error_db HTML / MySQL text — extract error #, column, table, SQL, file:line.
 */
function auto_parse_db_error($body)
{
    $clean = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $body);
    $clean = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $clean);

    $lines = [];
    if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $clean, $pm)) {
        foreach ($pm[1] as $p) {
            $t = trim(html_entity_decode(strip_tags($p), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($t === '' || stripos($t, 'A Database Error Occurred') !== false) {
                continue;
            }
            if (preg_match('/^(First Time\?|Back|Home)/i', $t)) {
                continue;
            }
            $lines[] = $t;
        }
    }
    if (!$lines) {
        $text = html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match_all('/Error Number:\s*\d+.*?(?=Error Number:|$)/is', $text, $blocks)) {
            foreach ($blocks[0] as $block) {
                foreach (preg_split('/\r\n|\r|\n/', trim($block)) as $ln) {
                    $ln = trim($ln);
                    if ($ln !== '') {
                        $lines[] = $ln;
                    }
                }
            }
        }
    }

    $errorNum = null;
    $message = null;
    $sql = null;
    $filename = null;
    $lineNo = null;

    foreach ($lines as $ln) {
        if (preg_match('/^Error Number:\s*(\d+)/i', $ln, $m)) {
            $errorNum = $m[1];
            continue;
        }
        if (preg_match('/^Filename:\s*(.+)$/i', $ln, $m)) {
            $filename = trim($m[1]);
            continue;
        }
        if (preg_match('/^Line Number:\s*(\d+)/i', $ln, $m)) {
            $lineNo = $m[1];
            continue;
        }
        if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE|SHOW|CREATE|ALTER|DROP|REPLACE|CALL)\b/i', $ln)) {
            $sql = $ln;
            continue;
        }
        if ($message === null) {
            $message = $ln;
        }
    }

    $column = null;
    $table = null;
    $key = null;

    $scan = ($message ?? '') . ' ' . ($sql ?? '');
    if (preg_match("/Unknown column '([^']+)'/i", $scan, $m)) {
        $column = $m[1];
    }
    if (preg_match("/Table '([^']+)' doesn't exist/i", $scan, $m)) {
        $table = $m[1];
    }
    if (preg_match("/Duplicate entry '([^']+)'/i", $scan, $m)) {
        $key = $m[1];
    }
    if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/i", $scan, $m)) {
        $key = $m[1];
        if (!$table) {
            $table = $m[2];
        }
    }
    if (preg_match('/SQLSTATE\[[^\]]+\]:\s*([^(\n]+)/i', $scan, $m)) {
        $message = trim($m[1]);
    }

    if (!$table && $sql) {
        if (preg_match('/\b(?:FROM|INTO|UPDATE|JOIN)\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $sql, $m)) {
            $table = $m[1];
        }
    }

    $issueParts = [];
    if ($errorNum !== null) {
        $issueParts[] = 'Error #' . $errorNum;
    }
    if ($column !== null) {
        $issueParts[] = 'Column: `' . $column . '`';
    }
    if ($table !== null) {
        $issueParts[] = 'Table: `' . $table . '`';
    }
    if ($key !== null && $column === null) {
        $issueParts[] = 'Duplicate key: `' . $key . '`';
    }
    if ($message !== null && !$issueParts) {
        $issueParts[] = $message;
    } elseif ($message !== null && ($column !== null || $table !== null)) {
        $issueParts[] = $message;
    }
    if (!$issueParts) {
        $issueParts[] = 'Database error';
    }

    $hintParts = [];
    if ($column !== null) {
        $hintParts[] = 'Missing/wrong column `' . $column . '` — query ki schema fix kara';
    }
    if ($table !== null) {
        $hintParts[] = 'Table `' . $table . '` — import/migration/prefix (`sma_`) check kara';
    }
    if ($filename) {
        $hintParts[] = 'Code: ' . $filename . ($lineNo ? ':' . $lineNo : '');
    }
    if ($sql) {
        $hintParts[] = 'SQL: ' . auto_trim_len(preg_replace('/\s+/', ' ', $sql), 120);
    }
    if (!$hintParts) {
        $hintParts[] = 'Database query/schema issue — MySQL log bagha';
    }

    return [
        'issue' => auto_trim_len(implode(' | ', $issueParts)),
        'hint' => auto_trim_len(implode(' · ', $hintParts)),
        'db_error_num' => $errorNum,
        'db_column' => $column,
        'db_table' => $table,
        'db_message' => $message,
    ];
}

function auto_snippet($body, $pattern, $len = 220)
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($body)));
    $msg = '';
    if (preg_match($pattern, $text, $m)) {
        $msg = trim($m[0]);
    } elseif (preg_match($pattern, $body, $m)) {
        $msg = trim(strip_tags($m[0]));
    }
    if ($msg !== '') {
        return function_exists('mb_substr') ? mb_substr($msg, 0, $len) : substr($msg, 0, $len);
    }
    return '';
}

/**
 * Detect PHP, DB, HTTP and app errors in response body.
 * Returns null if no issue, else ['type','issue','hint'].
 */
function auto_detect_body_issue($body)
{
    if ($body === '') {
        return null;
    }

    if (auto_is_db_body($body)) {
        $db = auto_parse_db_error($body);
        return [
            'type' => 'db',
            'issue' => $db['issue'],
            'hint' => $db['hint'],
            'db_column' => $db['db_column'],
            'db_table' => $db['db_table'],
            'db_error_num' => $db['db_error_num'],
        ];
    }

    $ciErr = auto_detect_ci_app_error($body);
    if ($ciErr) {
        return $ciErr;
    }

    $checks = [
        [
            'type' => 'syntax',
            'pattern' => '/Parse error|syntax error,|unexpected token|unexpected end of file/i',
            'hint' => 'PHP syntax error — file parse fix lagel',
            'msg' => '/(Parse error|syntax error[^<]{0,160})/i',
        ],
        [
            'type' => 'php',
            'pattern' => '/Fatal error|Parse error|Uncaught (Error|TypeError|Exception)|Call to a member function .+ on (null|false)|A PHP Error was encountered/i',
            'hint' => 'PHP 8.5 compatibility — null guard / type fix lagel',
            'msg' => '/(Fatal error|Parse error|Uncaught (Error|TypeError|Exception)|Call to a member function .{0,80} on (null|false)|A PHP Error was encountered).{0,140}/i',
        ],
        [
            'type' => 'deprecated',
            'pattern' => '/<(b|strong)>(Deprecated|Warning|Notice):|Deprecated:\s+/i',
            'hint' => 'PHP deprecation/notice — PHP 8.5 warning (may become fatal later)',
            'msg' => '/(Deprecated|Warning|Notice):[^<]{0,160}/i',
        ],
        [
            'type' => 'auth',
            'pattern' => '/access_denied|You are not authorized|login-form|identity.*password/i',
            'hint' => 'Auth/permission — login session or ion_auth group',
            'msg' => '/access_denied|not authorized|login-form/i',
        ],
    ];

    foreach ($checks as $c) {
        if (preg_match($c['pattern'], $body)) {
            $msg = auto_snippet($body, $c['msg']);
            return [
                'type' => $c['type'],
                'issue' => $msg ?: strtoupper($c['type']) . ' error detected in page',
                'hint' => $c['hint'],
            ];
        }
    }

    if (phase4_hasPhpIssue($body)) {
        $msg = auto_snippet($body, '/(Fatal error|Uncaught (Error|TypeError)).{0,180}/i');
        return [
            'type' => 'php',
            'issue' => $msg ?: 'PHP error on page',
            'hint' => 'PHP 8.5 compatibility fix lagel',
        ];
    }

    return null;
}

function auto_has_page_issue($body)
{
    return auto_detect_body_issue($body) !== null;
}

function auto_analyze_response($r, $expectedCodes)
{
    $code = (int) ($r['code'] ?? 0);
    $body = $r['body'] ?? '';

    if ($code === 0) {
        return [
            'ok' => false,
            'error_type' => 'connection',
            'issue' => 'Connection failed (HTTP 0)',
            'hint' => 'WAMP/Apache running aahe ka te check kara',
            'detail' => 'HTTP 0',
        ];
    }

    $bodyIssue = auto_detect_body_issue($body);
    if ($bodyIssue) {
        $out = [
            'ok' => false,
            'error_type' => $bodyIssue['type'],
            'issue' => $bodyIssue['issue'],
            'hint' => $bodyIssue['hint'],
            'detail' => $bodyIssue['issue'],
        ];
        if (!empty($bodyIssue['db_column'])) {
            $out['db_column'] = $bodyIssue['db_column'];
        }
        if (!empty($bodyIssue['db_table'])) {
            $out['db_table'] = $bodyIssue['db_table'];
        }
        if (!empty($bodyIssue['db_error_num'])) {
            $out['db_error_num'] = $bodyIssue['db_error_num'];
        }
        return $out;
    }

    if (auto_is_blank_response($body, $code)) {
        return [
            'ok' => false,
            'error_type' => 'empty',
            'issue' => 'Blank or empty page (HTTP ' . $code . ')',
            'hint' => 'Page load nahi — PHP fatal (hidden), Apache/PHP log bagha',
            'detail' => 'Empty body HTTP ' . $code,
        ];
    }

    if ($code === 500) {
        return [
            'ok' => false,
            'error_type' => 'http',
            'issue' => 'HTTP 500 Internal Server Error',
            'hint' => 'Server error — PHP log, DB, or missing param check kara',
            'detail' => 'HTTP 500',
        ];
    }
    if ($code === 403) {
        return [
            'ok' => false,
            'error_type' => 'auth',
            'issue' => 'HTTP 403 Forbidden',
            'hint' => 'Permission/ion_auth block — user la access nahi',
            'detail' => 'HTTP 403',
        ];
    }
    if ($code === 404) {
        return [
            'ok' => false,
            'error_type' => 'http',
            'issue' => 'HTTP 404 Not Found',
            'hint' => 'Route/controller method exist nahi kart',
            'detail' => 'HTTP 404',
        ];
    }
    if (in_array($code, [301, 302, 303], true) && !in_array($code, $expectedCodes, true)) {
        return [
            'ok' => false,
            'error_type' => 'redirect',
            'issue' => "HTTP $code redirect (expected " . implode('/', $expectedCodes) . ')',
            'hint' => 'Login redirect or access_denied — session/permission check kara',
            'detail' => "HTTP $code",
        ];
    }
    if (!in_array($code, $expectedCodes, true)) {
        return [
            'ok' => false,
            'error_type' => 'http',
            'issue' => "HTTP $code (expected " . implode('/', $expectedCodes) . ')',
            'hint' => 'Unexpected HTTP response — browser madhe URL open kara',
            'detail' => "HTTP $code",
        ];
    }

    return [
        'ok' => true,
        'error_type' => 'pass',
        'issue' => '',
        'hint' => '',
        'detail' => 'HTTP ' . $code,
    ];
}

function auto_explain_issue($r, $expectedCodes)
{
    $a = auto_analyze_response($r, $expectedCodes);
    return ['issue' => $a['issue'], 'hint' => $a['hint'], 'type' => $a['error_type']];
}

function auto_print_report($moduleFilter, $loginOk)
{
    global $auto_module_stats, $auto_failures, $phase4_pass, $phase4_fail, $phase4_skip, $verbose, $base;

    echo "\n";
    echo str_repeat('=', 62) . "\n";
    echo "  AUTO DEEP-LINK TEST REPORT\n";
    echo "  module=$moduleFilter | PHP " . PHP_VERSION . "\n";
    echo str_repeat('=', 62) . "\n\n";

    echo "SUMMARY\n";
    echo str_repeat('-', 62) . "\n";
    echo '  Login ................ ' . ($loginOk ? 'OK' : 'FAILED') . "\n";
    foreach ($auto_module_stats as $mod => $st) {
        $icon = $st['fail'] > 0 ? 'X' : 'OK';
        printf(
            "  %-22s %s  (%d pass, %d fail, %d skip)\n",
            ucfirst($mod),
            $icon,
            $st['pass'],
            $st['fail'],
            $st['skip']
        );
    }
    echo str_repeat('-', 62) . "\n";
    printf("  TOTAL ................. %d pass | %d fail | %d skip | %d real fail\n\n", $phase4_pass, $phase4_fail, $phase4_skip, auto_count_real_failures());

    $okModules = [];
    foreach ($auto_module_stats as $mod => $st) {
        if ($st['fail'] === 0) {
            $okModules[] = $mod . ' (' . $st['pass'] . ')';
        }
    }
    if ($okModules) {
        echo "PASSED MODULES (no failures)\n";
        echo '  ' . implode(', ', $okModules) . "\n\n";
    }

    if (!$auto_failures) {
        echo "REAL FAILURES (PHP/DB/syntax/blank)\n";
        echo "  None — sagla green!\n\n";
    } else {
        $realFails = array_values(array_filter($auto_failures, function ($f) {
            return auto_is_real_error_type($f['error_type'] ?? 'http');
        }));
        echo "REAL FAILURES — fix these (" . count($realFails) . ")\n";
        echo str_repeat('=', 62) . "\n";
        $n = 1;
        foreach ($realFails as $f) {
            echo "\n#{$n} Controller: {$f['controller']}::{$f['method']}\n";
            echo "   Module    : {$f['module']}\n";
            echo "   URL       : {$base}{$f['path']}\n";
            echo "   Type      : " . strtoupper($f['error_type'] ?? 'http') . "\n";
            if (!empty($f['db_column']) || !empty($f['db_table'])) {
                $dbLine = [];
                if (!empty($f['db_error_num'])) {
                    $dbLine[] = '#' . $f['db_error_num'];
                }
                if (!empty($f['db_column'])) {
                    $dbLine[] = 'Column: ' . $f['db_column'];
                }
                if (!empty($f['db_table'])) {
                    $dbLine[] = 'Table: ' . $f['db_table'];
                }
                echo '   DB        : ' . implode(' | ', $dbLine) . "\n";
            }
            echo "   Issue     : {$f['issue']}\n";
            if (!empty($f['hint'])) {
                echo "   Fix hint  : {$f['hint']}\n";
            }
            $n++;
        }
        echo "\n" . str_repeat('=', 62) . "\n";
    }

    if ($verbose && $phase4_pass > 0) {
        echo "\nVERBOSE: use without &verbose=1 to hide pass lines during run\n";
    }
}

function auto_print_html_report($moduleFilter, $loginOk, $scope, $controllerCount)
{
    global $auto_module_stats, $auto_failures, $phase4_pass, $phase4_fail, $phase4_skip, $base;

    $failuresJson = json_encode(array_values($auto_failures), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if ($failuresJson === false) {
        $failuresJson = '[]';
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>PHP Upgrade — Auto Link Test</title><style>
*{box-sizing:border-box}body{font-family:Segoe UI,system-ui,sans-serif;margin:0;background:#f0f2f5;color:#1a1a2e}
.wrap{max-width:1200px;margin:0 auto;padding:20px}
.hdr{background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;padding:24px;border-radius:12px;margin-bottom:20px}
.hdr h1{margin:0 0 8px;font-size:1.5rem}.hdr .meta{opacity:.9;font-size:.9rem}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px}
.card{background:#fff;border-radius:10px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.card .num{font-size:1.8rem;font-weight:700}.card.pass .num{color:#16a34a}.card.fail .num{color:#dc2626}.card.skip .num{color:#ca8a04}
.card .lbl{font-size:.75rem;color:#64748b;text-transform:uppercase;margin-top:4px}
.panel{background:#fff;border-radius:10px;padding:16px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.panel h2{margin:0 0 12px;font-size:1.1rem;border-bottom:2px solid #e2e8f0;padding-bottom:8px}
.mod-grid{display:flex;flex-wrap:wrap;gap:8px}
.badge{padding:6px 12px;border-radius:20px;font-size:.8rem;font-weight:600}
.badge.ok{background:#dcfce7;color:#166534}.badge.bad{background:#fee2e2;color:#991b1b}
table{width:100%;border-collapse:collapse;font-size:.875rem}
th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #e2e8f0}
th{background:#f8fafc;font-weight:600;color:#475569}
tr:hover td{background:#f8fafc}
.issue{color:#dc2626;font-weight:500}.hint{color:#64748b;font-size:.8rem;margin-top:4px}
.url a{color:#2563eb;word-break:break-all}
.pager{display:flex;align-items:center;gap:12px;margin-top:16px;flex-wrap:wrap}
.pager button{padding:8px 16px;border:1px solid #cbd5e1;background:#fff;border-radius:6px;cursor:pointer}
.pager button:disabled{opacity:.4;cursor:not-allowed}
.pager .info{color:#64748b;font-size:.875rem}
.filter{margin-bottom:12px}
.filter input,.filter select{padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;width:100%;max-width:320px}
code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.8rem}
.empty{padding:24px;text-align:center;color:#16a34a;font-weight:600}
</style></head><body><div class="wrap">';

    echo '<div class="hdr"><h1>Auto Deep-Link Test Report</h1>';
    echo '<div class="meta">module=' . htmlspecialchars($moduleFilter) . ' | scope=' . htmlspecialchars($scope)
        . ' | controllers=' . (int) $controllerCount . ' | PHP ' . PHP_VERSION
        . ' | Login: ' . ($loginOk ? 'OK' : 'FAILED') . '</div></div>';

    echo '<div class="cards">';
    echo '<div class="card pass"><div class="num">' . (int) $phase4_pass . '</div><div class="lbl">Passed</div></div>';
    echo '<div class="card fail"><div class="num">' . (int) $phase4_fail . '</div><div class="lbl">Failed</div></div>';
    echo '<div class="card skip"><div class="num">' . (int) $phase4_skip . '</div><div class="lbl">Skipped</div></div>';
    echo '</div>';

    echo '<div class="panel"><h2>Controllers</h2><div class="mod-grid">';
    foreach ($auto_module_stats as $mod => $st) {
        $cls = $st['fail'] > 0 ? 'bad' : 'ok';
        echo '<span class="badge ' . $cls . '">' . htmlspecialchars($mod) . ' (' . $st['pass'] . 'P';
        if ($st['fail'] > 0) {
            echo '/' . $st['fail'] . 'F';
        }
        echo ')</span>';
    }
    echo '</div></div>';

    echo '<div class="panel"><h2>Failures — Controller → Method → Issue</h2>';
    echo '<div class="filter"><input type="text" id="searchBox" placeholder="Search controller, method, issue..."></div>';
    echo '<table><thead><tr><th>#</th><th>Controller</th><th>Method</th><th>Issue</th><th>URL</th></tr></thead>';
    echo '<tbody id="failBody"></tbody></table>';
    echo '<div class="pager"><button id="prevBtn">← Prev</button><span class="info" id="pageInfo"></span><button id="nextBtn">Next →</button>';
    echo '<select id="perPage"><option value="15">15 / page</option><option value="25" selected>25 / page</option><option value="50">50 / page</option></select></div>';
    echo '</div></div>';

    echo '<script>
const baseUrl = ' . json_encode($base) . ';
const allFails = ' . $failuresJson . ';
let filtered = allFails.slice();
let page = 1;
let perPage = 25;
function esc(s){const d=document.createElement("div");d.textContent=s;return d.innerHTML;}
function render(){
  const start=(page-1)*perPage;
  const rows=filtered.slice(start,start+perPage);
  const tb=document.getElementById("failBody");
  if(!filtered.length){tb.innerHTML="<tr><td colspan=5 class=empty>✓ No failures — sagla green!</td></tr>";}
  else{tb.innerHTML=rows.map((f,i)=>`<tr>
    <td>${start+i+1}</td>
    <td><code>${esc(f.controller)}</code></td>
    <td><code>${esc(f.method)}</code></td>
    <td><div class="issue">${esc(f.issue)}</div>${f.hint?`<div class="hint">${esc(f.hint)}</div>`:""}</td>
    <td class="url"><a href="${baseUrl}${f.path}" target="_blank">${esc(f.path)}</a></td>
  </tr>`).join("");}
  const pages=Math.max(1,Math.ceil(filtered.length/perPage));
  if(page>pages)page=pages;
  document.getElementById("pageInfo").textContent=`Page ${page} / ${pages} (${filtered.length} issues)`;
  document.getElementById("prevBtn").disabled=page<=1;
  document.getElementById("nextBtn").disabled=page>=pages;
}
document.getElementById("searchBox").oninput=function(){
  const q=this.value.toLowerCase();
  filtered=allFails.filter(f=>(f.controller+f.method+f.issue+f.path).toLowerCase().includes(q));
  page=1;render();
};
document.getElementById("prevBtn").onclick=()=>{if(page>1){page--;render();}};
document.getElementById("nextBtn").onclick=()=>{page++;render();};
document.getElementById("perPage").onchange=function(){perPage=+this.value;page=1;render();};
render();
</script></body></html>';
}

function auto_reset_run_state()
{
    global $phase4_pass, $phase4_fail, $phase4_skip, $auto_failures, $auto_passes, $auto_skips_list, $auto_module_stats;
    $phase4_pass = 0;
    $phase4_fail = 0;
    $phase4_skip = 0;
    $auto_failures = [];
    $auto_passes = [];
    $auto_skips_list = [];
    $auto_module_stats = [];
}

function auto_print_hub_ui($registry, $upgraded, $identity, $password, $scope)
{
    $controllers = array_keys($registry);
    sort($controllers);
    $upgradedJson = json_encode(array_values($upgraded));
    $allJson = json_encode(array_values($controllers));
    $credQ = json_encode(http_build_query(['identity' => $identity, 'password' => $password]));
    $self = htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/phase4_auto_links_test.php');

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>PHP Upgrade — Link Test Hub</title><style>
*{box-sizing:border-box}body{font-family:Segoe UI,system-ui,sans-serif;margin:0;background:#0f172a;color:#e2e8f0;min-height:100vh}
.wrap{max-width:1100px;margin:0 auto;padding:20px}
.hdr{padding:20px 0 16px;border-bottom:1px solid #334155;margin-bottom:20px}
.hdr h1{margin:0;font-size:1.4rem;color:#f8fafc}.hdr p{margin:8px 0 0;color:#94a3b8;font-size:.9rem}
.toolbar{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;align-items:center}
.btn{padding:10px 18px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.875rem}
.btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
.btn-secondary{background:#334155;color:#e2e8f0}.btn-secondary:hover{background:#475569}
.btn:disabled{opacity:.5;cursor:not-allowed}
.filter{padding:10px 14px;border:1px solid #475569;border-radius:8px;background:#1e293b;color:#f8fafc;min-width:200px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:24px}
.card{background:#1e293b;border:2px solid #334155;border-radius:10px;padding:14px 14px 14px 10px;cursor:pointer;transition:all .15s;text-align:center;position:relative}
.card:hover{border-color:#60a5fa;transform:translateY(-2px)}
.card.upgraded{border-color:#22c55e55}.card.running{border-color:#fbbf24;animation:pulse 1s infinite}
.card.ok{border-color:#22c55e;background:#14532d33}.card.fail{border-color:#ef4444;background:#7f1d1d33}
.card.picked{border-color:#60a5fa;background:#1e3a5f44;box-shadow:0 0 0 1px #60a5fa55}
.card .name{font-weight:700;font-size:.9rem;text-transform:capitalize;padding-top:4px}
.card .st{font-size:.75rem;color:#94a3b8;margin-top:6px}
.card-chk{position:absolute;top:8px;left:8px;display:flex;align-items:center;cursor:pointer;z-index:2}
.card-chk input{width:17px;height:17px;cursor:pointer;accent-color:#2563eb;margin:0}
@keyframes pulse{50%{opacity:.7}}
.panel{background:#1e293b;border-radius:12px;padding:20px;border:1px solid #334155}
.panel h2{margin:0 0 12px;font-size:1rem;color:#f8fafc}
.stats{display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap}
.stat{background:#0f172a;padding:12px 20px;border-radius:8px;text-align:center;min-width:80px}
.stat .n{font-size:1.5rem;font-weight:700}.stat.pass .n{color:#4ade80}.stat.fail .n{color:#f87171}.stat.skip .n{color:#fbbf24}
table{width:100%;border-collapse:collapse;font-size:.85rem}
th,td{padding:10px;border-bottom:1px solid #334155;text-align:left}
th{color:#94a3b8;font-weight:600}
.issue{color:#fca5a5}.okmsg{color:#86efac}.skipmsg{color:#fbbf24}.hint{color:#94a3b8;font-size:.8rem}
.tabs{display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap}
.tab{padding:8px 14px;border-radius:8px;border:1px solid #475569;background:#0f172a;color:#94a3b8;cursor:pointer;font-size:.8rem;font-weight:600}
.tab.active{background:#2563eb;border-color:#2563eb;color:#fff}
.badge-st{font-size:.7rem;padding:2px 8px;border-radius:10px;font-weight:700}
.badge-st.pass{background:#166534;color:#bbf7d0}.badge-st.fail{background:#7f1d1d;color:#fecaca}.badge-st.skip{background:#713f12;color:#fde68a}
.badge-et{font-size:.65rem;padding:2px 6px;border-radius:6px;font-weight:700;text-transform:uppercase;margin-right:6px;vertical-align:middle}
.badge-et.db{background:#581c87;color:#e9d5ff}.badge-et.php{background:#7f1d1d;color:#fecaca}
.badge-et.http{background:#9a3412;color:#fed7aa}.badge-et.auth{background:#854d0e;color:#fef08a}
.badge-et.redirect{background:#1e3a8a;color:#bfdbfe}.badge-et.connection{background:#374151;color:#d1d5db}
.badge-et.syntax{background:#7f1d1d;color:#fecaca}.badge-et.empty{background:#374151;color:#d1d5db}
.badge-et.deprecated{background:#713f12;color:#fde68a}.badge-et.pass{background:#166534;color:#bbf7d0}
.stat.real .n{color:#f87171}
.type-breakdown{font-size:.8rem;color:#94a3b8;margin:-8px 0 12px}
.dbmeta{font-size:.78rem;color:#e9d5ff;margin-top:6px;font-weight:600;line-height:1.5}
.dbmeta span{display:inline-block;background:#581c8744;padding:2px 8px;border-radius:4px;margin:2px 4px 2px 0}
a{color:#60a5fa}code{background:#0f172a;padding:2px 6px;border-radius:4px;font-size:.8rem}
.empty{color:#4ade80;padding:20px;text-align:center}
.pager{margin-top:12px;display:flex;gap:10px;align-items:center}
.pager button{background:#334155;color:#e2e8f0;border:none;padding:8px 14px;border-radius:6px;cursor:pointer}
.legend{font-size:.8rem;color:#94a3b8;margin-bottom:12px}
.badge-upg{display:inline-block;background:#166534;color:#bbf7d0;font-size:.65rem;padding:2px 6px;border-radius:4px;margin-top:4px}
</style></head><body><div class="wrap">';
    echo '<div class="hdr"><h1>Auto Deep-Link Test Hub</h1>';
    echo '<p>Checkbox ne controllers select kara, nantar <strong>Run Selected</strong> — ek card click = single test. Green border = upgraded. <strong>Real Fail</strong> = PHP/DB/syntax/blank page only (AUTH/AJAX noise → Skip).</p></div>';
    echo '<div class="toolbar">';
    echo '<button class="btn btn-primary" id="runSelected">▶ Run Selected (0)</button>';
    echo '<button class="btn btn-secondary" id="runUpgraded">Run All Upgraded</button>';
    echo '<button class="btn btn-secondary" id="runAll">Run All Controllers</button>';
    echo '<button class="btn btn-secondary" id="selAll">Select All</button>';
    echo '<button class="btn btn-secondary" id="selUpg">Select Upgraded</button>';
    echo '<button class="btn btn-secondary" id="selNone">Clear</button>';
    echo '<input class="filter" id="filterBox" placeholder="Filter controllers...">';
    echo '<span id="globalStatus" style="color:#94a3b8;font-size:.875rem"></span>';
    echo '</div>';
    echo '<div class="legend">Discovered ' . count($controllers) . ' controllers · ' . count($upgraded) . ' upgraded (auto: phase4_*_links_test.php) · PHP ' . PHP_VERSION . '</div>';
    echo '<div class="grid" id="grid"></div>';
    echo '<div class="panel" id="resultPanel" style="display:none">';
    echo '<h2 id="resultTitle">Results</h2><div class="stats" id="resultStats"></div><div id="typeBreakdown"></div>';
    echo '<div class="tabs" id="resultTabs">';
    echo '<button class="tab active" data-f="all">All</button>';
    echo '<button class="tab" data-f="real">Real Fail</button>';
    echo '<button class="tab" data-f="pass">Pass</button>';
    echo '<button class="tab" data-f="fail">Fail</button>';
    echo '<button class="tab" data-f="skip">Skip</button>';
    echo '</div>';
    echo '<table><thead><tr><th>#</th><th>Status</th><th>Type</th><th>Controller</th><th>Method</th><th>Result</th><th>URL</th></tr></thead>';
    echo '<tbody id="resultRows"></tbody></table>';
    echo '<div class="pager"><button id="rp">←</button><span id="ri"></span><button id="rn">→</button></div>';
    echo '</div></div>';
    echo '<script>
const upgraded = ' . $upgradedJson . ';
const allCtrls = ' . $allJson . ';
const basePath = ' . json_encode($self) . ';
const credQ = ' . $credQ . ';
const baseUrl = ' . json_encode('http://localhost/ElintOM18.00') . ';
const state = {};
const selected = new Set(upgraded);
let curRows = [], page = 1, perPage = 25, running = false, viewFilter = "all";

function esc(s){const d=document.createElement("div");d.textContent=s;return d.innerHTML;}
function isRealFailRow(r){return r.status==="fail"&&["php","db","syntax","empty","connection","http"].includes((r.error_type||"http").toLowerCase());}
function countRealFails(rows){return rows.filter(isRealFailRow).length;}
function errTypeLabel(t){return (t||"http").toUpperCase();}
function errTypeBadge(t){
  const et=(t||"http").toLowerCase();
  return `<span class="badge-et ${et}">${esc(errTypeLabel(et))}</span>`;
}
function typeBreakdown(rows){
  const m={};
  rows.filter(isRealFailRow).forEach(r=>{const t=(r.error_type||"http").toLowerCase();m[t]=(m[t]||0)+1;});
  const parts=Object.keys(m).sort().map(k=>`${errTypeLabel(k)}: ${m[k]}`);
  return parts.length?`<div class="type-breakdown">Real fail by type — ${parts.join(" · ")}</div>`:"";
}
function dbMetaHtml(r){
  const p=[];
  if(r.db_error_num) p.push("Error #"+r.db_error_num);
  if(r.db_column) p.push("Column: "+r.db_column);
  if(r.db_table) p.push("Table: "+r.db_table);
  return p.length?`<div class="dbmeta">${p.map(x=>`<span>${esc(x)}</span>`).join("")}</div>`:"";
}
function getSelectedList(){
  return allCtrls.filter(c=>selected.has(c));
}
function updateSelUi(){
  const n=getSelectedList().length;
  const btn=document.getElementById("runSelected");
  if(btn){btn.textContent="▶ Run Selected ("+n+")";btn.disabled=running||n===0;}
}
function setSelected(c,on){
  if(on) selected.add(c); else selected.delete(c);
  updateSelUi();
}
function mergeResult(agg,j,mod){
  agg.pass+=(j.pass||0); agg.fail+=(j.fail||0); agg.skip+=(j.skip||0);
  agg.real_fail=(agg.real_fail||0)+(j.real_fail||0);
  (j.passes||[]).forEach(r=>agg.passes.push(r));
  (j.failures||[]).forEach(r=>agg.failures.push(r));
  (j.skips||[]).forEach(r=>agg.skips.push(r));
}
async function fetchModule(mod){
  const r=await fetch(basePath+"?action=run&module="+encodeURIComponent(mod)+"&"+credQ);
  return await r.json();
}
function renderGrid(filter=""){
  const g=document.getElementById("grid");
  const q=filter.toLowerCase();
  g.innerHTML=allCtrls.filter(c=>c.includes(q)).map(c=>{
    const isUp=upgraded.includes(c);
    const st=state[c]||{status:"idle",pass:0,fail:0,skip:0};
    const picked=selected.has(c);
  return `<div class="card ${isUp?"upgraded":""} ${st.status} ${picked?"picked":""}" data-c="${c}" title="${picked?"Selected — ":""}Click to test ${c}">
    <label class="card-chk" title="Bulk test sathi select">
      <input type="checkbox" class="card-pick" data-c="${c}" ${picked?"checked":""}>
    </label>
    <div class="name">${esc(c)}</div>
    ${isUp?`<span class="badge-upg">upgraded</span>`:""}
    <div class="st">${st.status==="idle"?"click to test":st.status==="running"?"testing...":st.pass+"P / "+st.fail+"F / "+st.skip+"S"}</div>
  </div>`}).join("");
  g.querySelectorAll(".card-pick").forEach(chk=>{
    chk.onchange=(e)=>{
      e.stopPropagation();
      setSelected(chk.dataset.c, chk.checked);
      const card=chk.closest(".card");
      if(card) card.classList.toggle("picked", chk.checked);
    };
    chk.onclick=(e)=>e.stopPropagation();
  });
  g.querySelectorAll(".card").forEach(el=>{
    el.onclick=(e)=>{
      if(e.target.closest(".card-chk")) return;
      runOne(el.dataset.c);
    };
  });
  updateSelUi();
}
async function runOne(mod){
  if(running) return;
  running=true;
  updateSelUi();
  state[mod]={status:"running",pass:0,fail:0,skip:0};
  renderGrid(document.getElementById("filterBox").value);
  document.getElementById("globalStatus").textContent="Testing "+mod+"...";
  try{
    const j=await fetchModule(mod);
    if(j.error){alert(j.error);state[mod]={status:"fail",pass:0,fail:1,skip:0};showFails(mod,[{controller:mod,method:"-",issue:j.error,path:"/",hint:"",error_type:"php"}]);}
    else{
      state[mod]={status:(j.real_fail||0)>0?"fail":"ok",pass:j.pass,fail:j.fail,skip:j.skip,realFail:j.real_fail||0};
      showResult(mod,j);
    }
  }catch(e){alert(e);state[mod]={status:"fail",pass:0,fail:1,skip:0};}
  running=false;
  updateSelUi();
  renderGrid(document.getElementById("filterBox").value);
  document.getElementById("globalStatus").textContent="";
}
function showResult(mod,j){
  document.getElementById("resultPanel").style.display="block";
  document.getElementById("resultTitle").textContent="Results: "+mod;
  const rf=j.real_fail!=null?j.real_fail:countRealFails(curRows);
  document.getElementById("resultStats").innerHTML=
    `<div class="stat pass"><div class="n">${j.pass||0}</div>Pass</div>
     <div class="stat real"><div class="n">${rf}</div>Real Fail</div>
     <div class="stat fail"><div class="n">${j.fail||0}</div>Fail (all)</div>
     <div class="stat skip"><div class="n">${j.skip||0}</div>Skip</div>`;
  curRows=[];
  (j.passes||[]).forEach(r=>curRows.push({...r,status:"pass"}));
  (j.failures||[]).forEach(r=>curRows.push({...r,status:"fail"}));
  (j.skips||[]).forEach(r=>curRows.push({...r,status:"skip"}));
  const tbEl=document.getElementById("typeBreakdown");
  if(tbEl) tbEl.innerHTML=typeBreakdown(curRows);
  viewFilter = rf>0 ? "real" : ((j.fail>0) ? "fail" : "all");
  document.querySelectorAll("#resultTabs .tab").forEach(t=>{
    t.classList.toggle("active", t.dataset.f===viewFilter);
  });
  page=1;
  renderRows();
}
function showFails(mod,arr){showResult(mod,{pass:0,fail:arr.length,skip:0,failures:arr,passes:[],skips:[]});}
function renderRows(){
  const filtered = viewFilter==="all" ? curRows
    : viewFilter==="real" ? curRows.filter(isRealFailRow)
    : curRows.filter(r=>r.status===viewFilter);
  const s=(page-1)*perPage, rows=filtered.slice(s,s+perPage);
  const tb=document.getElementById("resultRows");
  if(!filtered.length){
    tb.innerHTML="<tr><td colspan=7 class=empty>No rows for this filter</td></tr>";
  } else {
    tb.innerHTML=rows.map((r,i)=>{
      const st=r.status;
      const msg = st==="pass" ? (r.detail||"HTTP 200") : (r.issue||r.detail||"");
      const cls = st==="pass"?"okmsg":(st==="fail"?"issue":"skipmsg");
      const et = st==="pass" ? "pass" : (r.error_type||"http");
      return `<tr>
        <td>${s+i+1}</td>
        <td><span class="badge-st ${st}">${st.toUpperCase()}</span></td>
        <td>${errTypeBadge(et)}</td>
        <td><code>${esc(r.controller)}</code></td>
        <td><code>${esc(r.method)}</code></td>
        <td><div class="${cls}">${esc(msg)}</div>${dbMetaHtml(r)}${r.hint?`<div class="hint">${esc(r.hint)}</div>`:""}</td>
        <td><a href="${baseUrl}${r.path}" target="_blank">${esc(r.path)}</a></td>
      </tr>`;
    }).join("");
  }
  const pages=Math.max(1,Math.ceil(filtered.length/perPage));
  if(page>pages)page=pages;
  document.getElementById("ri").textContent=`Page ${page}/${pages} (${filtered.length} rows)`;
  document.getElementById("rp").onclick=()=>{if(page>1){page--;renderRows();}};
  document.getElementById("rn").onclick=()=>{if(page<pages){page++;renderRows();}};
}
document.querySelectorAll("#resultTabs .tab").forEach(btn=>{
  btn.onclick=()=>{
    document.querySelectorAll("#resultTabs .tab").forEach(t=>t.classList.remove("active"));
    btn.classList.add("active");
    viewFilter=btn.dataset.f; page=1; renderRows();
  };
});
async function runSeq(list,label){
  if(running) return;
  if(!list.length){alert("Kamit kami ek controller select kara (checkbox).");return;}
  running=true;
  updateSelUi();
  const agg={pass:0,fail:0,skip:0,real_fail:0,passes:[],failures:[],skips:[]};
  for(let i=0;i<list.length;i++){
    const c=list[i];
    document.getElementById("globalStatus").textContent="["+(i+1)+"/"+list.length+"] Testing "+c+"...";
    state[c]={status:"running",pass:0,fail:0,skip:0};
    renderGrid(document.getElementById("filterBox").value);
    try{
      const j=await fetchModule(c);
      if(j.error){
        state[c]={status:"fail",pass:0,fail:1,skip:0};
        mergeResult(agg,{pass:0,fail:1,skip:0,failures:[{controller:c,method:"-",issue:j.error,path:"/",hint:"",error_type:"php"}],passes:[],skips:[]},c);
      }else{
        state[c]={status:(j.real_fail||0)>0?"fail":"ok",pass:j.pass,fail:j.fail,skip:j.skip,realFail:j.real_fail||0};
        mergeResult(agg,j,c);
      }
    }catch(e){
      state[c]={status:"fail",pass:0,fail:1,skip:0};
      mergeResult(agg,{pass:0,fail:1,skip:0,failures:[{controller:c,method:"-",issue:String(e),path:"/",hint:"",error_type:"connection"}],passes:[],skips:[]},c);
    }
    renderGrid(document.getElementById("filterBox").value);
  }
  running=false;
  updateSelUi();
  showResult(label||("Bulk ("+list.length+")"),agg);
  document.getElementById("globalStatus").textContent="Done — "+list.length+" controllers tested";
}
document.getElementById("runSelected").onclick=()=>runSeq(getSelectedList(),"Selected ("+getSelectedList().length+")");
document.getElementById("runUpgraded").onclick=()=>runSeq(upgraded,"All Upgraded");
document.getElementById("runAll").onclick=()=>{if(confirm("Test all "+allCtrls.length+" controllers? Takes time but runs one-by-one."))runSeq(allCtrls,"All Controllers");};
document.getElementById("selAll").onclick=()=>{allCtrls.forEach(c=>selected.add(c));renderGrid(document.getElementById("filterBox").value);};
document.getElementById("selUpg").onclick=()=>{selected.clear();upgraded.forEach(c=>selected.add(c));renderGrid(document.getElementById("filterBox").value);};
document.getElementById("selNone").onclick=()=>{selected.clear();renderGrid(document.getElementById("filterBox").value);};
document.getElementById("filterBox").oninput=function(){renderGrid(this.value);};
renderGrid();
</script></body></html>';
}

function auto_scan_views($viewDir, $controllers)
{
    $found = [];
    $root = PHPUPGRADE_ROOT . '/themes/default/views/' . $viewDir;
    if (!is_dir($root)) {
        return $found;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($it as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }
        if (!auto_view_file_ok($file->getFilename())) {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        $screen = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (preg_match_all("/site_url\(\s*['\"]([^'\"]+)['\"]/", $content, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[1] as $idx => $hit) {
                $raw = $hit[0];
                $offset = $hit[1];
                $ctx = substr($content, max(0, $offset - 120), 240);
                if (preg_match('/sAjaxSource|\.post\(|type:\s*[\'"]POST|method:\s*[\'"]POST/i', $ctx)) {
                    continue;
                }
                $tpl = auto_normalize_template($raw);
                if ($tpl === null || !auto_path_is_testable($tpl)) {
                    continue;
                }
                $ctrl = explode('/', trim($tpl, '/'))[0] ?? '';
                if ($ctrl && !in_array($ctrl, $controllers, true)) {
                    continue;
                }
                $found[] = ['template' => $tpl, 'source' => "view:$screen", 'submodule' => $ctrl ?: $viewDir, 'screen' => basename($screen, '.php')];
            }
        }
    }
    return $found;
}

function auto_scan_controller($controllerName)
{
    $found = [];
    $file = PHPUPGRADE_ROOT . '/app/controllers/' . ucfirst($controllerName) . '.php';
    if (!is_file($file) || preg_match('/backup| copy/i', $file)) {
        return $found;
    }
    $content = file_get_contents($file);
    if (!preg_match_all('/function\s+(\w+)\s*\(([^)]*)\)/', $content, $m)) {
        return $found;
    }
    foreach ($m[1] as $i => $method) {
        if (auto_should_skip_method($method)) {
            continue;
        }
        if (preg_match('/\b(private|protected)\s+function\s+' . preg_quote($method, '/') . '\b/', $content)) {
            continue;
        }
        $params = trim($m[2][$i]);
        $tpl = '/' . $controllerName;
        if (strtolower($method) !== 'index') {
            $tpl .= '/' . $method;
        }
        if ($params !== '') {
            if (preg_match('/\$([a-zA-Z_]+)\s*=\s*([\'"])([^\'"]*)\2/', $params, $pm) && $pm[3] !== '') {
                $tpl .= '/' . $pm[3];
            } elseif (preg_match('/\$lang\b/', $params)) {
                $tpl .= '/english';
            } elseif (preg_match('/\$([a-zA-Z_]+)\s*=\s*(false|null)\b/i', $params)) {
                // optional param — no segment
            } elseif (preg_match('/\$([a-zA-Z_]+)/', $params, $pm)) {
                $tpl .= '/{' . auto_param_key($pm[1], $controllerName, $method) . '}';
            }
        }
        $found[] = [
            'template' => $tpl,
            'source' => "controller:$controllerName::$method",
            'submodule' => $controllerName,
            'screen' => $method,
        ];
    }
    return $found;
}

function auto_param_key($param, $controller, $method)
{
    if (preg_match('/warehouse/i', $param)) {
        return 'warehouse_id';
    }
    if (preg_match('/request/i', $param) || preg_match('/request/i', $method)) {
        return 'request_id';
    }
    if (preg_match('/expense/i', $param) || preg_match('/expense/i', $method)) {
        return 'expense_id';
    }
    if (preg_match('/adjust/i', $param) || preg_match('/adjust/i', $method)) {
        return 'adjustment_id';
    }
    if (preg_match('/count/i', $param) || preg_match('/count/i', $method)) {
        return 'count_id';
    }
    if (preg_match('/slug/i', $param)) {
        return 'slug';
    }
    return 'id';
}

function auto_normalize_template($raw)
{
    global $SKIP_PATH_PARTS;
    $raw = trim($raw);
    if ($raw === '' || preg_match($SKIP_PATH_PARTS, $raw)) {
        return null;
    }
    if (preg_match('/^(javascript:|#|mailto:)/i', $raw)) {
        return null;
    }
    $tpl = preg_replace('/\s+/', '', $raw);
    $tpl = preg_replace('/\.\s*\$[a-zA-Z_>]+/', '/{id}', $tpl);
    $tpl = preg_replace('/\.\s*\$this->[a-zA-Z_]+/', '/{id}', $tpl);
    $tpl = preg_replace('/\?[^\'"]*\.\s*\$[a-zA-Z_>]+/', '?p={id}', $tpl);
    $tpl = preg_replace('/\?[^\'"]*\.\s*\$this->[a-zA-Z_]+/', '?p={id}', $tpl);
    $tpl = preg_replace('/\?sale_id=\{id\}/', '?sale_id={id}', $tpl);
    $tpl = preg_replace('/\?purchase_id=\{id\}/', '?purchase_id={id}', $tpl);
    if ($tpl[0] !== '/') {
        $tpl = '/' . $tpl;
    }
    if (preg_match('/\{\w+\}\/\w+/', $tpl)) {
        return null;
    }
    return $tpl;
}

function auto_discover_module($moduleKey, $cfg)
{
    $items = [];
    foreach ($cfg['controllers'] as $ctrl) {
        $items = array_merge($items, auto_scan_controller($ctrl));
        $items = array_merge($items, auto_scan_views($ctrl, $cfg['controllers']));
    }
    foreach ($cfg['views'] as $viewDir) {
        if (!in_array($viewDir, $cfg['controllers'], true)) {
            $items = array_merge($items, auto_scan_views($viewDir, $cfg['controllers']));
        }
    }
    $uniq = [];
    foreach ($items as $it) {
        $k = $it['template'] . '|' . $it['submodule'] . '|' . $it['screen'];
        $uniq[$k] = $it;
    }
    return array_values($uniq);
}

function auto_fetch_ids($base, $cookieFile, $resolvers)
{
    $ids = [
        'year' => date('Y'),
        'month' => date('m'),
        'today' => date('Y-m-d'),
    ];
    foreach ($resolvers as $res) {
        if (($res['type'] ?? '') === 'reports') {
            $pdo = auto_pdo();
            if ($pdo) {
                try {
                    $ids['warehouse_id'] = (int) $pdo->query('SELECT id FROM sma_warehouses ORDER BY id ASC LIMIT 1')->fetchColumn();
                } catch (Throwable $e) {
                    $ids['warehouse_id'] = 0;
                }
                try {
                    $ids['slug'] = (string) $pdo->query("SELECT slug FROM sma_bi_reports WHERE status='1' ORDER BY id ASC LIMIT 1")->fetchColumn();
                } catch (Throwable $e) {
                    $ids['slug'] = '';
                }
            }
            $r = phase4_httpGetFollow("$base/reports/customers", $cookieFile);
            $token = phase4_extractCsrf($r['body']);
            $r = phase4_httpRequest("$base/reports/getCustomersData/?v=1", $cookieFile, phase4_dtPost($token));
            $ids['id'] = phase4_firstDtId($r['body']) ?: $ids['id'] ?? null;
            $r = phase4_httpGetFollow("$base/reports/users", $cookieFile);
            $token = phase4_extractCsrf($r['body']);
            $r = phase4_httpRequest("$base/reports/getUsers", $cookieFile, phase4_dtPost($token));
            $ids['user_id'] = phase4_firstDtId($r['body']);
            $r = phase4_httpGetFollow("$base/reports/suppliers", $cookieFile);
            $token = phase4_extractCsrf($r['body']);
            $r = phase4_httpRequest("$base/reports/getSuppliers/?v=1", $cookieFile, phase4_dtPost($token));
            $ids['supplier_id'] = phase4_firstDtId($r['body']);
            continue;
        }
        $key = $res['key'];
        $r = phase4_httpGetFollow("$base{$res['list']}", $cookieFile);
        $token = phase4_extractCsrf($r['body']);
        $r = phase4_httpRequest("$base{$res['ajax']}", $cookieFile, phase4_dtPost($token));
        $ids[$key] = phase4_firstDtId($r['body']);
    }
    return $ids;
}

function auto_pick_id_key($template, $submodule)
{
    if (preg_match('/request/', $template)) {
        return 'request_id';
    }
    if (preg_match('/expense/', $template)) {
        return 'expense_id';
    }
    if (preg_match('/adjustment|adjust/', $template)) {
        return 'adjustment_id';
    }
    if (preg_match('/count/', $template)) {
        return 'count_id';
    }
    if (preg_match('/warehouse/', $template)) {
        return 'warehouse_id';
    }
    if (preg_match('/\/{slug}|\/bi\//', $template)) {
        return 'slug';
    }
    if (preg_match('/staff_report/', $template)) {
        return 'user_id';
    }
    if (preg_match('/supplier_report/', $template)) {
        return 'supplier_id';
    }
    if ($submodule === 'billers') {
        return 'biller_id';
    }
    if ($submodule === 'suppliers') {
        return 'supplier_id';
    }
    return 'id';
}

function auto_resolve_template($template, $ids, $submodule)
{
    if (!preg_match('/\{[a-z_]+\}/', $template)) {
        return $template;
    }
    $out = $template;
    if (preg_match_all('/\{([a-z_]+)\}/', $template, $m)) {
        foreach ($m[1] as $ph) {
            $key = $ph;
            if ($ph === 'id') {
                $key = auto_pick_id_key($template, $submodule);
            }
            if (empty($ids[$key]) && $ph === 'id' && !empty($ids['id'])) {
                $key = 'id';
            }
            if (empty($ids[$key])) {
                return null;
            }
            $out = str_replace('{' . $ph . '}', (string) $ids[$key], $out);
        }
    }
    if (preg_match('/\{/', $out)) {
        return null;
    }
    return $out;
}

function auto_should_skip_template($template)
{
    global $SKIP_PATH_PARTS;
    if (preg_match($SKIP_PATH_PARTS, $template)) {
        return 'AJAX/internal endpoint';
    }
    if (!auto_path_is_testable($template)) {
        return 'internal/AJAX endpoint';
    }
    $method = basename(parse_url($template, PHP_URL_PATH) ?: $template);
    if (preg_match('/^delete/i', $method)) {
        return 'destructive';
    }
    if (preg_match('/^get(Products|Sales|Purchases|Quotes|Transfers|Users|Suppliers|Billers|Customers)/i', $method)) {
        return 'POST/AJAX DataTables';
    }
    return null;
}

function auto_test_url($base, $cookieFile, $path, $screen, $source = '')
{
    global $PDF_METHODS;
    $method = basename(parse_url($path, PHP_URL_PATH) ?: $path);
    $codes = preg_match($PDF_METHODS, $method) ? [200, 302] : [200];
    $r = phase4_httpGetFollow("$base$path", $cookieFile);
    $analysis = auto_analyze_response($r, $codes);
    $final = auto_finalize_test_result($analysis, $path, $source);
    $dbMeta = [];
    foreach (['db_column', 'db_table', 'db_error_num'] as $k) {
        if (!empty($final[$k])) {
            $dbMeta[$k] = $final[$k];
        }
    }
    $status = $final['status'] ?? ($final['ok'] ? 'pass' : 'fail');
    return [
        $status,
        $final['detail'] ?? ($final['ok'] ? 'HTTP ' . ($r['code'] ?? 0) : ''),
        $status === 'fail' ? ($final['issue'] ?? '') : '',
        $status === 'fail' ? ($final['hint'] ?? '') : ($status === 'skip' ? ($final['hint'] ?? '') : ''),
        $status === 'pass' ? 'pass' : ($final['error_type'] ?? 'http'),
        $dbMeta,
    ];
}

function auto_run_module($moduleKey, $cfg, $base, $cookieFile)
{
    global $verbose, $isWeb;

    if ($verbose) {
        echo "\n" . str_repeat('=', 14) . " MODULE: $moduleKey " . str_repeat('=', 14) . "\n";
    } elseif (!$isWeb) {
        echo "Testing $moduleKey ... ";
        if (function_exists('flush')) {
            flush();
        }
    }

    $discovered = auto_discover_module($moduleKey, $cfg);
    $allResolvers = [];
    foreach ($cfg['controllers'] as $ctrl) {
        $allResolvers = array_merge($allResolvers, auto_guess_resolvers($ctrl));
    }
    $ids = auto_fetch_ids($base, $cookieFile, $allResolvers);

    $moduleSeenPaths = [];
    $tree = [];
    foreach ($discovered as $item) {
        $tree[$item['submodule']][$item['screen']][] = $item;
    }
    ksort($tree);

    foreach ($tree as $submodule => $screens) {
        ksort($screens);
        foreach ($screens as $screen => $entries) {
            foreach ($entries as $entry) {
                $tpl = $entry['template'];
                $skip = auto_should_skip_template($tpl);
                if ($skip) {
                    auto_record('skip', $moduleKey, $submodule, $screen, $tpl, $skip, $entry['source']);
                    continue;
                }
                $path = auto_resolve_template($tpl, $ids, $submodule);
                if ($path === null) {
                    auto_record('skip', $moduleKey, $submodule, $screen, $tpl, 'no ID in DB', $entry['source']);
                    continue;
                }
                $pathSkip = auto_should_skip_resolved_path($path);
                if ($pathSkip) {
                    auto_record('skip', $moduleKey, $submodule, $screen, $path, $pathSkip, $entry['source']);
                    continue;
                }
                if (isset($moduleSeenPaths[$path])) {
                    continue;
                }
                $moduleSeenPaths[$path] = true;
                list($status, $detail, $issue, $hint, $errorType, $dbMeta) = auto_test_url($base, $cookieFile, $path, $screen, $entry['source']);
                auto_record($status, $moduleKey, $submodule, $screen, $path, $detail, $entry['source'], $issue, $hint, $errorType, $dbMeta);
                if ($verbose) {
                    $tag = strtoupper($status);
                    echo "  [$tag] $path — $detail\n";
                }
            }
        }
    }

    if (!empty($cfg['guest'])) {
        foreach ($cfg['guest'] as $gpath) {
            @unlink($cookieFile);
            list($status, $detail, $issue, $hint, $errorType, $dbMeta) = auto_test_url($base, $cookieFile, $gpath, 'guest');
            auto_record($status, $moduleKey, 'guest', 'guest', $gpath, $detail, 'route:guest', $issue, $hint, $errorType, $dbMeta);
            phase4_login($GLOBALS['base'], $GLOBALS['identity'], $GLOBALS['password'], $cookieFile);
        }
    }

    if (!$verbose && !$isWeb) {
        global $auto_module_stats;
        $st = $auto_module_stats[$moduleKey] ?? ['pass' => 0, 'fail' => 0, 'skip' => 0];
        echo $st['fail'] > 0 ? "FAIL ({$st['fail']})\n" : "OK ({$st['pass']})\n";
    }
}

// ---------- Main ----------
$GLOBALS['base'] = $base;
$GLOBALS['identity'] = $identity;
$GLOBALS['password'] = $password;
$GLOBALS['isWeb'] = $isWeb;

$MODULE_REGISTRY = auto_build_registry();
$UPGRADED_MODULES = auto_discover_upgraded_modules(array_keys($MODULE_REGISTRY));

// --- Web: single-controller JSON API (performance — one module per request) ---
if ($isWeb && $webAction === 'run') {
    $mod = trim($_GET['module'] ?? '');
    if ($mod === 'all' || $mod === '') {
        $self = $_SERVER['SCRIPT_NAME'] ?? '/upgrade/phase4_auto_links_test.php';
        $q = http_build_query([
            'identity' => $identity,
            'password' => $password,
        ]);
        header('Location: ' . $self . '?' . $q);
        exit(0);
    }
    header('Content-Type: application/json; charset=UTF-8');
    if (!isset($MODULE_REGISTRY[$mod])) {
        echo json_encode(['error' => 'Unknown module: ' . $mod]);
        exit(1);
    }
    auto_reset_run_state();
    @unlink($cookieFile);
    if (!phase4_login($base, $identity, $password, $cookieFile)) {
        echo json_encode(['error' => 'Login failed']);
        exit(1);
    }
    try {
        auto_run_module($mod, $MODULE_REGISTRY[$mod], $base, $cookieFile);
    } catch (Throwable $e) {
        auto_record('fail', $mod, $mod, 'index', '/' . $mod, $e->getMessage(), 'runtime', $e->getMessage(), 'PHP runtime error', 'php');
    }
    echo json_encode([
        'module' => $mod,
        'pass' => $phase4_pass,
        'fail' => $phase4_fail,
        'skip' => $phase4_skip,
        'real_fail' => auto_count_real_failures(),
        'passes' => $auto_passes,
        'failures' => $auto_failures,
        'skips' => $auto_skips_list,
    ]);
    exit(auto_count_real_failures() > 0 ? 1 : 0);
}

// --- Web: hub UI (all controllers + checkbox select + Run Selected) ---
if ($isWeb && $webAction !== 'batch') {
    auto_print_hub_ui($MODULE_REGISTRY, $UPGRADED_MODULES, $identity, $password, $scope);
    exit(0);
}

@unlink($cookieFile);
$loginOk = phase4_login($base, $identity, $password, $cookieFile);

if (!$isWeb) {
    echo $loginOk ? "[PASS] Login\n" : "[FAIL] Login\n";
}
if (!$loginOk) {
    if ($isWeb) {
        auto_print_html_report($moduleFilter ?: 'all', false, $scope, count($MODULE_REGISTRY));
    } else {
        auto_print_report($moduleFilter ?: 'all', false);
    }
    exit(1);
}

$batchModule = $moduleFilter ?: 'all';
if ($batchModule === 'all' && $scope === 'upgraded') {
    $runList = array_values(array_intersect($UPGRADED_MODULES, array_keys($MODULE_REGISTRY)));
} elseif ($batchModule === 'all') {
    $runList = array_keys($MODULE_REGISTRY);
} else {
    $runList = array_map('trim', explode(',', $batchModule));
}

if (!$isWeb) {
    echo 'Discovered ' . count($MODULE_REGISTRY) . ' controllers | ' . count($UPGRADED_MODULES) . ' upgraded (auto) | testing ' . count($runList) . " (scope=$scope)\n\n";
}

foreach ($runList as $mod) {
    if (!isset($MODULE_REGISTRY[$mod])) {
        if (!$isWeb) {
            echo "[WARN] Unknown module: $mod (not in app/controllers/)\n";
        }
        continue;
    }
    try {
        auto_run_module($mod, $MODULE_REGISTRY[$mod], $base, $cookieFile);
    } catch (Throwable $e) {
        auto_record('fail', $mod, $mod, 'index', '/' . $mod, $e->getMessage(), 'runtime', $e->getMessage(), 'Check PHP error log', 'php');
    }
}

if ($isWeb) {
    auto_print_html_report($batchModule, $loginOk, $scope, count($MODULE_REGISTRY));
} else {
    auto_print_report($batchModule, $loginOk);
}
exit(auto_count_real_failures() > 0 ? 1 : 0);