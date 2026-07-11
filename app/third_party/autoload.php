<?php
/**
 * ElintOM18.00 — Composer autoload for third_party (MPDF 8, Stripe, Google API).
 */
defined('BASEPATH') OR define('BASEPATH', true);
$vendorAutoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($vendorAutoload)) {
    trigger_error('Composer vendor not installed. Run: composer install in app/third_party/', E_USER_ERROR);
}
require_once $vendorAutoload;
