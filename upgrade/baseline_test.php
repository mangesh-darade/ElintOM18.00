<?php
/**
 * PHP 8.5 baseline bootstrap test — run from CLI:
 * c:\wamp64\bin\php\php8.5.0\php.exe -n baseline_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/ElintOM18.00/index.php';
$_SERVER['REQUEST_URI'] = '/ElintOM18.00/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTPS'] = 'off';

$errors = [];
set_error_handler(function ($errno, $errstr, $file, $line) use (&$errors) {
    $errors[] = "[$errno] $errstr in $file:$line";
    return false;
});

register_shutdown_function(function () use (&$errors) {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $errors[] = "[FATAL] {$e['message']} in {$e['file']}:{$e['line']}";
    }
    echo "\n=== PHP " . PHP_VERSION . " Baseline Test ===\n";
    echo "Errors captured: " . count($errors) . "\n";
    foreach (array_slice($errors, 0, 50) as $err) {
        echo $err . "\n";
    }
    if (count($errors) > 50) {
        echo "... and " . (count($errors) - 50) . " more\n";
    }
    exit(count($errors) > 0 ? 1 : 0);
});

define('PHPUPGRADE_ROOT', dirname(__DIR__));
chdir(PHPUPGRADE_ROOT);
ob_start();
try {
    require PHPUPGRADE_ROOT . '/index.php';
} catch (Throwable $t) {
    $errors[] = "[EXCEPTION] " . $t->getMessage() . " in " . $t->getFile() . ":" . $t->getLine();
}
$output = ob_get_clean();
echo "Bootstrap completed. Output length: " . strlen($output) . " bytes\n";
