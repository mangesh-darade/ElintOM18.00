<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cheerio WhatsApp — single HTTP transport for ElintOm (canonical copy; sync webshopapi helper).
 *
 * Webshop API callers (POST notifywebshoporderwhatsapp / passwordotpsend):
 *   Webshop_api_model::notify_webshop_order_whatsapp() | send_password_otp()
 *   → Whatsapp_model → cheerio_whatsapp_send_*()
 *
 * POS (same helper via Sma::send_cheerio_template): invoice, payment reminder, challan.
 *
 * API key: sma_settings.whatsapp_api_key only — never hardcode in code.
 */

if (!function_exists('cheerio_whatsapp_template_url')) {
    function cheerio_whatsapp_template_url() {
        return 'https://pre-prod.cheerio.in/direct-apis/v1/whatsapp/template/send';
    }
}

if (!function_exists('cheerio_whatsapp_direct_url')) {
    function cheerio_whatsapp_direct_url() {
        return 'https://pre-prod.cheerio.in/direct-apis/v1/whatsapp/direct/send';
    }
}

if (!function_exists('cheerio_whatsapp_contacts_url')) {
    function cheerio_whatsapp_contacts_url() {
        return 'https://pre-prod.cheerio.in/direct-apis/v1/contacts/uploadSingleContact';
    }
}

if (!function_exists('cheerio_whatsapp_settings')) {
    function cheerio_whatsapp_settings() {
        $ci = get_instance();
        if (isset($ci->Settings) && is_object($ci->Settings)) {
            return $ci->Settings;
        }
        if (isset($ci->site)) {
            return $ci->site->get_setting();
        }
        return null;
    }
}

if (!function_exists('cheerio_whatsapp_api_key')) {
    function cheerio_whatsapp_api_key($runtime_override = null) {
        if ($runtime_override !== null && trim((string) $runtime_override) !== '') {
            return trim((string) $runtime_override);
        }
        $s = cheerio_whatsapp_settings();
        if ($s && !empty($s->whatsapp_api_key)) {
            return trim((string) $s->whatsapp_api_key);
        }
        return '';
    }
}

if (!function_exists('cheerio_whatsapp_brand_name')) {
    function cheerio_whatsapp_brand_name() {
        $s = cheerio_whatsapp_settings();
        return ($s && !empty($s->site_name)) ? (string) $s->site_name : 'Webshop';
    }
}

if (!function_exists('cheerio_whatsapp_support_phone')) {
    function cheerio_whatsapp_support_phone() {
        $s = cheerio_whatsapp_settings();
        if ($s) {
            if (!empty($s->phone)) {
                return (string) $s->phone;
            }
            if (!empty($s->telephone)) {
                return (string) $s->telephone;
            }
        }
        return '-';
    }
}

if (!function_exists('cheerio_whatsapp_default_dial_digits')) {
    function cheerio_whatsapp_default_dial_digits() {
        $s = cheerio_whatsapp_settings();
        $country = '';
        if ($s) {
            if (!empty($s->default_country)) {
                $country = trim((string) $s->default_country);
            } elseif (!empty($s->country)) {
                $country = trim((string) $s->country);
            }
        }
        if ($country === '') {
            return '';
        }
        $ci = get_instance();
        $cq = $ci->db->select('code')->from('country_master')->where('name', $country)->limit(1)->get();
        if ($cq && $cq->num_rows() > 0) {
            return preg_replace('/\D+/', '', (string) $cq->row()->code);
        }
        return '';
    }
}

if (!function_exists('cheerio_whatsapp_phone_digits')) {
    function cheerio_whatsapp_phone_digits($phone, $apply_default_dial = true) {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        if ($phone === '') {
            return '';
        }
        if (strlen($phone) >= 11 || !$apply_default_dial) {
            return $phone;
        }
        if (strlen($phone) === 10) {
            $dial = cheerio_whatsapp_default_dial_digits();
            if ($dial !== '') {
                return $dial . $phone;
            }
        }
        return $phone;
    }
}

if (!function_exists('cheerio_whatsapp_storefront_base_url')) {
    /** Customer-facing webshop base URL (checkout API, WhatsApp links, order email). */
    function cheerio_whatsapp_storefront_base_url() {
        $ci = get_instance();
        $from_api = trim((string) $ci->input->post('storefront_base_url'));
        if ($from_api !== '') {
            return rtrim($from_api, '/');
        }
        $ci->load->config('elintom_api', false, true);
        $raw = $ci->config->item('webshop_storefront_base_url', 'elintom_api');
        if (is_string($raw) && trim($raw) !== '') {
            return rtrim(trim($raw), '/');
        }
        foreach (array('shop_host', 'http_host') as $k) {
            $h = trim((string) $ci->input->post($k));
            if ($h === '' || stripos($h, 'localhost') !== false) {
                continue;
            }
            $hostOnly = strtolower(explode(':', $h, 2)[0]);
            $hostOnly = preg_replace('/[^a-zA-Z0-9_.-]/', '', $hostOnly);
            if ($hostOnly === '' || $hostOnly === 'localhost') {
                continue;
            }
            $scheme = 'http';
            if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $xf = strtolower(trim((string) $_SERVER['HTTP_X_FORWARDED_PROTO']));
                if ($xf === 'https' || $xf === 'http') {
                    $scheme = $xf;
                }
            } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                $scheme = 'https';
            }
            return $scheme . '://' . $hostOnly;
        }
        return rtrim((string) base_url(), '/');
    }
}

if (!function_exists('cheerio_whatsapp_is_local_url')) {
    function cheerio_whatsapp_is_local_url($url) {
        $url = strtolower(trim((string) $url));
        if ($url === '') {
            return true;
        }
        foreach (array('localhost', '127.0.0.1', '::1', '0.0.0.0') as $needle) {
            if (strpos($url, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('cheerio_whatsapp_install_path_suffix')) {
    /**
     * Path segment for this ElintOm install (e.g. /ElintOm) from base_url().
     */
    function cheerio_whatsapp_install_path_suffix() {
        $parsed = @parse_url(rtrim((string) base_url(), '/'));
        if (!empty($parsed['path']) && (string) $parsed['path'] !== '/') {
            return rtrim((string) $parsed['path'], '/');
        }
        return '';
    }
}

if (!function_exists('cheerio_whatsapp_allow_local_tunnel_base_url')) {
    /**
     * Tunnel auto-discovery is for local dev only — never used on live domains.
     */
    function cheerio_whatsapp_allow_local_tunnel_base_url() {
        if (empty($_SERVER['HTTP_HOST'])) {
            return false;
        }
        $host = strtolower(trim((string) $_SERVER['HTTP_HOST']));
        $host = preg_replace('/:\d+$/', '', $host);
        $host = trim($host, '[]');
        if ($host === '' || cheerio_whatsapp_is_local_url('http://' . $host)) {
            return true;
        }
        foreach (array('trycloudflare.com', 'ngrok-free.app', 'ngrok.io', 'ngrok.app', 'loca.lt') as $marker) {
            if (strpos($host, $marker) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('cheerio_whatsapp_tunnel_cache_file')) {
    function cheerio_whatsapp_tunnel_cache_file() {
        return APPPATH . 'cache/cheerio_whatsapp_public_base_url.txt';
    }
}

if (!function_exists('cheerio_whatsapp_read_tunnel_cache_base_url')) {
    function cheerio_whatsapp_read_tunnel_cache_base_url() {
        $file = cheerio_whatsapp_tunnel_cache_file();
        if (!is_readable($file)) {
            return '';
        }
        $raw = trim((string) @file_get_contents($file));
        if ($raw === '' || cheerio_whatsapp_is_local_url($raw)) {
            return '';
        }
        return rtrim($raw, '/');
    }
}

if (!function_exists('cheerio_whatsapp_write_tunnel_cache_base_url')) {
    function cheerio_whatsapp_write_tunnel_cache_base_url($url) {
        $url = rtrim(trim((string) $url), '/');
        if ($url === '' || cheerio_whatsapp_is_local_url($url)) {
            return false;
        }
        $file = cheerio_whatsapp_tunnel_cache_file();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return @file_put_contents($file, $url) !== false;
    }
}

if (!function_exists('cheerio_whatsapp_discover_ngrok_base_url')) {
    /**
     * Read active ngrok HTTPS tunnel from the local inspector API (127.0.0.1:4040).
     */
    function cheerio_whatsapp_discover_ngrok_base_url() {
        $ctx = stream_context_create(array('http' => array('timeout' => 1)));
        $json = @file_get_contents('http://127.0.0.1:4040/api/tunnels', false, $ctx);
        if ($json === false || $json === '') {
            return '';
        }
        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['tunnels']) || !is_array($data['tunnels'])) {
            return '';
        }
        $suffix = cheerio_whatsapp_install_path_suffix();
        $https = '';
        $http = '';
        foreach ($data['tunnels'] as $tunnel) {
            if (!is_array($tunnel) || empty($tunnel['public_url'])) {
                continue;
            }
            $url = rtrim((string) $tunnel['public_url'], '/');
            if (cheerio_whatsapp_is_local_url($url)) {
                continue;
            }
            if (stripos($url, 'https://') === 0) {
                $https = $url;
                break;
            }
            if ($http === '') {
                $http = $url;
            }
        }
        $chosen = $https !== '' ? $https : $http;
        if ($chosen === '') {
            return '';
        }
        return rtrim($chosen . $suffix, '/');
    }
}

if (!function_exists('cheerio_whatsapp_discover_cloudflared_base_url')) {
    /**
     * Parse trycloudflare.com URL from tools/cloudflared.err.log (local quick tunnel).
     */
    function cheerio_whatsapp_discover_cloudflared_base_url() {
        $log = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'cloudflared.err.log';
        if (!is_readable($log)) {
            return '';
        }
        $content = (string) @file_get_contents($log);
        if ($content === '') {
            return '';
        }
        if (!preg_match('#https://[a-z0-9-]+\.trycloudflare\.com#i', $content, $match)) {
            return '';
        }
        $suffix = cheerio_whatsapp_install_path_suffix();
        return rtrim($match[0] . $suffix, '/');
    }
}

if (!function_exists('cheerio_whatsapp_discover_local_tunnel_base_url')) {
    function cheerio_whatsapp_discover_local_tunnel_base_url() {
        foreach (array(
            'cheerio_whatsapp_discover_ngrok_base_url',
            'cheerio_whatsapp_discover_cloudflared_base_url',
        ) as $fn) {
            $url = $fn();
            if ($url !== '') {
                cheerio_whatsapp_write_tunnel_cache_base_url($url);
                return $url;
            }
        }
        return '';
    }
}

if (!function_exists('cheerio_whatsapp_resolve_request_public_base_url')) {
    /**
     * Build public base from the current HTTP request (works when browsing via ngrok / live domain).
     */
    function cheerio_whatsapp_resolve_request_public_base_url() {
        if (empty($_SERVER['HTTP_HOST']) || php_sapi_name() === 'cli') {
            return '';
        }
        $host = trim((string) $_SERVER['HTTP_HOST']);
        if ($host === '' || cheerio_whatsapp_is_local_url('http://' . $host)) {
            return '';
        }
        $cfgUrl = rtrim((string) base_url(), '/');
        $parsed = @parse_url($cfgUrl);
        $scheme = 'https';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $xf = strtolower(trim((string) $_SERVER['HTTP_X_FORWARDED_PROTO']));
            if ($xf === 'https' || $xf === 'http') {
                $scheme = $xf;
            }
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = 'https';
        } elseif (!empty($parsed['scheme'])) {
            $scheme = strtolower((string) $parsed['scheme']);
        } else {
            $scheme = 'http';
        }
        $path = '';
        if (!empty($parsed['path'])) {
            $path = rtrim((string) $parsed['path'], '/');
        }
        return rtrim($scheme . '://' . $host . $path, '/');
    }
}

if (!function_exists('cheerio_whatsapp_public_base_url')) {
    /**
     * HTTPS/HTTP base reachable by Cheerio/Meta for WhatsApp media links.
     * Priority: elintom_api override → current request host (ngrok) → base_url().
     */
    function cheerio_whatsapp_public_base_url() {
        $ci = get_instance();
        $ci->load->config('elintom_api', false, true);
        foreach (array('webshop_email_asset_base_url', 'webshop_storefront_base_url') as $key) {
            $raw = trim((string) $ci->config->item($key, 'elintom_api'));
            if ($raw !== '' && !cheerio_whatsapp_is_local_url($raw)) {
                return rtrim($raw, '/');
            }
        }
        if (cheerio_whatsapp_allow_local_tunnel_base_url()) {
            $cached = cheerio_whatsapp_read_tunnel_cache_base_url();
            if ($cached !== '') {
                return $cached;
            }
            $fromTunnel = cheerio_whatsapp_discover_local_tunnel_base_url();
            if ($fromTunnel !== '') {
                return $fromTunnel;
            }
        }
        $fromRequest = cheerio_whatsapp_resolve_request_public_base_url();
        if ($fromRequest !== '') {
            return $fromRequest;
        }
        $base = rtrim((string) base_url(), '/');
        if ($base !== '' && !cheerio_whatsapp_is_local_url($base)) {
            return $base;
        }
        return '';
    }
}

if (!function_exists('cheerio_whatsapp_dial_digits_for_country')) {
    function cheerio_whatsapp_dial_digits_for_country($country_name) {
        $country_name = trim((string) $country_name);
        if ($country_name === '') {
            return '';
        }
        $ci = get_instance();
        $cq = $ci->db->select('code')->from('country_master')->where('name', $country_name)->limit(1)->get();
        if ($cq && $cq->num_rows() > 0) {
            return preg_replace('/\D+/', '', (string) $cq->row()->code);
        }
        return '';
    }
}

if (!function_exists('cheerio_whatsapp_resolve_order_phone_digits')) {
    /**
     * International digits for Cheerio from orders row (billing/shipping address or customer).
     *
     * @param array $orderRow
     * @return string
     */
    function cheerio_whatsapp_resolve_order_phone_digits(array $orderRow) {
        $ci = get_instance();
        $addr_id = 0;
        if (!empty($orderRow['billing_address_id'])) {
            $addr_id = (int) $orderRow['billing_address_id'];
        } elseif (!empty($orderRow['shipping_address_id'])) {
            $addr_id = (int) $orderRow['shipping_address_id'];
        }

        $phone = '';
        $country_name = '';

        if ($addr_id > 0) {
            $addr = $ci->db->where('id', $addr_id)->get('addresses')->row();
            if ($addr) {
                $phone = isset($addr->phone) ? preg_replace('/\D+/', '', (string) $addr->phone) : '';
                $country_name = isset($addr->country) ? trim((string) $addr->country) : '';
            }
        }

        if ($phone === '' && !empty($orderRow['customer_id'])) {
            $cust = $ci->db->select('phone, country')->where('id', (int) $orderRow['customer_id'])->get('companies')->row();
            if ($cust) {
                $phone = isset($cust->phone) ? preg_replace('/\D+/', '', (string) $cust->phone) : '';
                if ($country_name === '' && isset($cust->country)) {
                    $country_name = trim((string) $cust->country);
                }
            }
        }

        if ($phone === '') {
            return '';
        }
        if (strlen($phone) >= 11) {
            return $phone;
        }
        $dial = cheerio_whatsapp_dial_digits_for_country($country_name);
        if ($dial !== '') {
            return $dial . $phone;
        }
        return $phone;
    }
}

if (!function_exists('cheerio_whatsapp_send_template')) {
    function cheerio_whatsapp_send_template($phone, $template_name, array $params = array(), $order_id = null, $api_key_override = null) {
        $phone = cheerio_whatsapp_phone_digits($phone);
        if ($phone === '') {
            return array('status' => 'error', 'message' => 'Invalid phone', 'order_id' => $order_id);
        }
        $payload = cheerio_whatsapp_build_template_payload($phone, $template_name, $params);
        return cheerio_whatsapp_json_post(
            cheerio_whatsapp_template_url(),
            cheerio_whatsapp_api_key($api_key_override),
            $payload,
            $order_id
        );
    }
}

if (!function_exists('cheerio_whatsapp_send_direct_text')) {
    function cheerio_whatsapp_send_direct_text($phone, $message, $order_id = null, $api_key_override = null) {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        if ($phone === '') {
            return array('status' => 'error', 'message' => 'Invalid phone', 'order_id' => $order_id);
        }
        $payload = cheerio_whatsapp_build_direct_text_payload($phone, $message);
        return cheerio_whatsapp_json_post(
            cheerio_whatsapp_direct_url(),
            cheerio_whatsapp_api_key($api_key_override),
            $payload,
            $order_id
        );
    }
}

if (!function_exists('cheerio_whatsapp_upload_contact')) {
    function cheerio_whatsapp_upload_contact($customer_name, $phone, $order_id, $api_key_override = null) {
        $api_key = cheerio_whatsapp_api_key($api_key_override);
        if ($api_key === '') {
            return false;
        }
        $postData = array(
            'name' => $customer_name,
            'mobile' => cheerio_whatsapp_phone_digits($phone),
            'email' => '',
            'customData' => array('order_id' => (int) $order_id),
            'labels' => array('webshop'),
        );
        $res = cheerio_whatsapp_json_post(cheerio_whatsapp_contacts_url(), $api_key, $postData, (int) $order_id);
        $code = isset($res['http_code']) ? (int) $res['http_code'] : 0;
        return ($code === 200 || $code === 201) && cheerio_whatsapp_delivery_ok($res);
    }
}

if (!function_exists('cheerio_whatsapp_build_otp_message')) {
    function cheerio_whatsapp_build_otp_message($otp) {
        $brand = cheerio_whatsapp_brand_name();
        return 'Your OTP for password reset at ' . $brand . ' is: ' . trim((string) $otp)
            . '. Valid for 10 minutes. Do not share it with anyone.';
    }
}

if (!function_exists('cheerio_whatsapp_result_message')) {
    function cheerio_whatsapp_result_message($sent, $res, $success_msg = 'WhatsApp notification sent.', $fail_msg = 'WhatsApp send did not complete.') {
        if ($sent) {
            if (is_array($res) && !empty($res['delivery_method'])) {
                return $success_msg . ' (' . $res['delivery_method'] . ')';
            }
            return $success_msg;
        }
        if (is_array($res)) {
            if (!empty($res['message'])) {
                return (string) $res['message'];
            }
            if (isset($res['response']['message'])) {
                return (string) $res['response']['message'];
            }
        }
        return $fail_msg;
    }
}

if (!function_exists('cheerio_whatsapp_failure_message')) {
    function cheerio_whatsapp_failure_message($res, $default = 'WhatsApp send failed') {
        if (!is_array($res)) {
            return $default;
        }
        if (!empty($res['message'])) {
            return (string) $res['message'];
        }
        $body = isset($res['response']) ? $res['response'] : null;
        if (is_array($body)) {
            if (!empty($body['error_data']['details'])) {
                return (string) $body['error_data']['details'];
            }
            if (!empty($body['message'])) {
                return (string) $body['message'];
            }
        }
        return $default;
    }
}

if (!function_exists('cheerio_whatsapp_log_send_failure')) {
    function cheerio_whatsapp_log_send_failure($context, $res) {
        if (!is_array($res)) {
            return;
        }
        $why = cheerio_whatsapp_failure_message($res, '');
        log_message('error', $context
            . ' http=' . (isset($res['http_code']) ? $res['http_code'] : '?')
            . ($why !== '' ? ' msg=' . $why : ''));
    }
}

if (!function_exists('cheerio_whatsapp_response_is_error')) {
    function cheerio_whatsapp_response_is_error($body) {
        if (!is_array($body)) {
            return false;
        }
        if (!empty($body['error']) || !empty($body['error_data'])) {
            return true;
        }
        if (isset($body['type']) && stripos((string) $body['type'], 'error') !== false) {
            return true;
        }
        $msg = isset($body['message']) ? strtolower((string) $body['message']) : '';
        if ($msg !== '' && (
            strpos($msg, 'not available') !== false
            || strpos($msg, 'failed') !== false
            || strpos($msg, 'invalid') !== false
            || strpos($msg, 'error') !== false
        )) {
            return true;
        }
        return false;
    }
}

if (!function_exists('cheerio_whatsapp_delivery_ok')) {
    function cheerio_whatsapp_delivery_ok($res) {
        if ($res === true) {
            return true;
        }
        if (!is_array($res)) {
            return false;
        }
        if (cheerio_whatsapp_response_is_error(isset($res['response']) ? $res['response'] : null)) {
            return false;
        }
        if (isset($res['status']) && $res['status'] === 'success') {
            return true;
        }
        if (isset($res['http_code']) && (int) $res['http_code'] >= 200 && (int) $res['http_code'] < 300) {
            return !cheerio_whatsapp_response_is_error(isset($res['response']) ? $res['response'] : null);
        }
        $body = isset($res['response']) ? $res['response'] : null;
        if (!is_array($body) || cheerio_whatsapp_response_is_error($body)) {
            return false;
        }
        return !empty($body['success']) || !empty($body['flag'])
            || (isset($body['status']) && is_numeric($body['status']) && (int) $body['status'] === 200)
            || (isset($body['status']) && strtolower((string) $body['status']) === 'success')
            || !empty($body['data']['messages']);
    }
}

if (!function_exists('cheerio_whatsapp_apply_ssl_options')) {
    function cheerio_whatsapp_apply_ssl_options($ch) {
        $local = (defined('ENVIRONMENT') && ENVIRONMENT === 'development');
        if (!$local && function_exists('base_url')) {
            $bu = (string) base_url();
            $local = (stripos($bu, 'localhost') !== false || stripos($bu, '127.0.0.1') !== false);
        }
        if ($local) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
    }
}

if (!function_exists('cheerio_whatsapp_json_post')) {
    function cheerio_whatsapp_json_post($url, $api_key, array $payload, $order_id = null) {
        if ($api_key === '') {
            return array(
                'status' => 'error',
                'message' => 'WhatsApp API key not configured',
                'http_code' => 0,
                'order_id' => $order_id,
            );
        }

        $do_request = function ($verify_ssl) use ($url, $api_key, $payload) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'x-api-key: ' . $api_key,
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            if ($verify_ssl) {
                cheerio_whatsapp_apply_ssl_options($ch);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return array($response, $error, $http_code);
        };

        list($response, $error, $http_code) = $do_request(true);
        if ($error !== '' && stripos($error, 'SSL') !== false) {
            list($response, $error, $http_code) = $do_request(false);
        }
        if ($error !== '') {
            log_message('error', 'cheerio_whatsapp_json_post: ' . $error);
            return array('status' => 'error', 'message' => $error, 'order_id' => $order_id);
        }

        $decoded = json_decode($response, true);
        $ok = ($http_code >= 200 && $http_code < 300) && !cheerio_whatsapp_response_is_error($decoded);
        $message = '';
        if (is_array($decoded)) {
            if (!empty($decoded['error_data']['details'])) {
                $message = (string) $decoded['error_data']['details'];
            } elseif (!empty($decoded['message'])) {
                $message = (string) $decoded['message'];
            }
        }

        return array(
            'status' => $ok ? 'success' : 'error',
            'http_code' => $http_code,
            'response' => $decoded,
            'message' => $message,
            'order_id' => $order_id,
        );
    }
}

if (!function_exists('cheerio_whatsapp_ssr_pdf_template_language')) {
    function cheerio_whatsapp_ssr_pdf_template_language() {
        return 'en';
    }
}

if (!function_exists('cheerio_whatsapp_ssr_otp_template_language')) {
    function cheerio_whatsapp_ssr_otp_template_language() {
        return 'en_US';
    }
}

if (!function_exists('cheerio_whatsapp_ssr_pdf_template')) {
    function cheerio_whatsapp_ssr_pdf_template() {
        return 'elintom_ssr_pdf';
    }
}

if (!function_exists('cheerio_whatsapp_ssr_otp_template')) {
    function cheerio_whatsapp_ssr_otp_template() {
        return 'elintom_otp_ssr';
    }
}

if (!function_exists('cheerio_whatsapp_build_text_parameters')) {
    function cheerio_whatsapp_build_text_parameters(array $params) {
        $body_parameters = array();
        foreach ($params as $text) {
            $body_parameters[] = array('type' => 'text', 'text' => (string) $text);
        }
        return $body_parameters;
    }
}

if (!function_exists('cheerio_whatsapp_build_template_payload')) {
    function cheerio_whatsapp_build_template_payload($phone, $template_name, array $params, array $extra_components = array(), $language = 'en') {
        $components = $extra_components;
        $components[] = array(
            'type' => 'body',
            'parameters' => cheerio_whatsapp_build_text_parameters($params),
        );
        return array(
            'to' => $phone,
            'data' => array(
                'name' => $template_name,
                'language' => array('code' => (string) $language),
                'components' => $components,
            ),
        );
    }
}

if (!function_exists('cheerio_whatsapp_send_template_components')) {
    function cheerio_whatsapp_send_template_components($phone, $template_name, array $components, $language = 'en', $order_id = null, $api_key_override = null) {
        $phone = cheerio_whatsapp_phone_digits($phone);
        if ($phone === '') {
            return array('status' => 'error', 'message' => 'Invalid phone', 'order_id' => $order_id);
        }
        $payload = array(
            'to' => $phone,
            'data' => array(
                'name' => $template_name,
                'language' => array('code' => (string) $language),
                'components' => $components,
            ),
        );
        return cheerio_whatsapp_json_post(
            cheerio_whatsapp_template_url(),
            cheerio_whatsapp_api_key($api_key_override),
            $payload,
            $order_id
        );
    }
}

if (!function_exists('cheerio_whatsapp_send_ssr_pdf')) {
    /**
     * Service Site Report PDF via Cheerio template (document header + body placeholders).
     *
     * @param string $phone
     * @param string $customerName  {{1}}
     * @param string $reportNo      {{2}}
     * @param string $siteName      {{3}}
     * @param string $pdfUrl        Public URL for PDF document header
     * @param string $pdfFilename   e.g. service_site_report_123.pdf
     * @param mixed  $api_key_override
     * @return array
     */
    function cheerio_whatsapp_send_ssr_pdf($phone, $customerName, $reportNo, $siteName, $pdfUrl, $pdfFilename, $api_key_override = null) {
        $phone = cheerio_whatsapp_phone_digits($phone);
        if ($phone === '') {
            return array('status' => 'error', 'message' => 'Invalid phone');
        }
        $pdfUrl = trim((string) $pdfUrl);
        if ($pdfUrl === '') {
            return array('status' => 'error', 'message' => 'PDF URL is required');
        }
        $pdfFilename = trim((string) $pdfFilename);
        if ($pdfFilename === '') {
            $pdfFilename = 'service_site_report.pdf';
        }

        $header = array(
            'type' => 'header',
            'parameters' => array(
                array(
                    'type' => 'document',
                    'document' => array(
                        'link' => $pdfUrl,
                        'filename' => $pdfFilename,
                    ),
                ),
            ),
        );
        $payload = cheerio_whatsapp_build_template_payload(
            $phone,
            cheerio_whatsapp_ssr_pdf_template(),
            array($customerName, $reportNo, $siteName),
            array($header),
            cheerio_whatsapp_ssr_pdf_template_language()
        );
        return cheerio_whatsapp_json_post(
            cheerio_whatsapp_template_url(),
            cheerio_whatsapp_api_key($api_key_override),
            $payload
        );
    }
}

if (!function_exists('cheerio_whatsapp_probe_public_pdf_url')) {
    /**
     * Verify Meta/Cheerio can fetch the signed public PDF URL before sending template.
     */
    function cheerio_whatsapp_probe_public_pdf_url($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return array('ok' => false, 'message' => 'PDF URL is empty');
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_USERAGENT => 'facebookexternalhit/1.1',
            CURLOPT_RANGE => '0-2047',
        ));
        cheerio_whatsapp_apply_ssl_options($ch);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false || $code < 200 || $code >= 400) {
            $msg = 'PDF URL not reachable for WhatsApp';
            if ($code > 0) {
                $msg .= ' (HTTP ' . $code . ')';
            }
            if ($err !== '') {
                $msg .= ': ' . $err;
            }
            return array('ok' => false, 'message' => $msg);
        }
        if (strncmp((string) $body, '%PDF', 4) !== 0 && stripos($ctype, 'pdf') === false) {
            return array(
                'ok' => false,
                'message' => 'PDF URL did not return a PDF file (Content-Type: ' . ($ctype !== '' ? $ctype : 'unknown') . ')',
            );
        }
        return array('ok' => true);
    }
}

if (!function_exists('cheerio_whatsapp_ssr_missing_public_url_message')) {
    function cheerio_whatsapp_ssr_missing_public_url_message() {
        return 'Cannot send report PDF on WhatsApp from localhost without a public URL. '
            . 'Run tools/start_whatsapp_tunnel.ps1 (starts a Cloudflare tunnel automatically), then try again. '
            . 'Or set webshop_email_asset_base_url in app/config/elintom_api.php to your live/ngrok URL.';
    }
}

if (!function_exists('cheerio_whatsapp_send_ssr_invoice_link_fallback')) {
    /**
     * Fallback template when elintom_ssr_pdf document send fails but a public PDF URL exists.
     * Uses invoice_without_award_points (same as POS receipts) with report link in the URL slot.
     */
    function cheerio_whatsapp_send_ssr_invoice_link_fallback($phone, $customerName, $reportNo, $siteName, $pdfUrl, $api_key_override = null) {
        $pdfUrl = trim((string) $pdfUrl);
        if ($pdfUrl === '') {
            return array('status' => 'error', 'message' => 'PDF URL is required');
        }
        return cheerio_whatsapp_send_template(
            $phone,
            'invoice_without_award_points',
            array(
                trim((string) $customerName) !== '' ? trim((string) $customerName) : 'Customer',
                trim((string) $siteName) !== '' ? trim((string) $siteName) : cheerio_whatsapp_brand_name(),
                trim((string) $reportNo),
                $pdfUrl,
                trim((string) $siteName) !== '' ? trim((string) $siteName) : cheerio_whatsapp_brand_name(),
            ),
            null,
            $api_key_override
        );
    }
}

if (!function_exists('cheerio_whatsapp_send_ssr_otp')) {
    /**
     * Service Site Report OTP via Cheerio template elintom_otp_ssr (en_US, auth button required).
     */
    function cheerio_whatsapp_send_ssr_otp($phone, $otp, $api_key_override = null) {
        $otp = trim((string) $otp);
        if ($otp === '') {
            return array('status' => 'error', 'message' => 'OTP is required');
        }
        $components = array(
            array(
                'type' => 'body',
                'parameters' => array(
                    array('type' => 'text', 'text' => $otp),
                ),
            ),
            array(
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => array(
                    array('type' => 'text', 'text' => $otp),
                ),
            ),
        );
        return cheerio_whatsapp_send_template_components(
            $phone,
            cheerio_whatsapp_ssr_otp_template(),
            $components,
            cheerio_whatsapp_ssr_otp_template_language(),
            null,
            $api_key_override
        );
    }
}

if (!function_exists('cheerio_whatsapp_build_direct_text_payload')) {
    function cheerio_whatsapp_build_direct_text_payload($phone, $message) {
        return array(
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => array('body' => (string) $message),
        );
    }
}
