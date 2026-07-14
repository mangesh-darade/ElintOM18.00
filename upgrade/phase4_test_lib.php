<?php
/**
 * Shared helpers for phase4_* test scripts (include only).
 */
if (!defined('PHPUPGRADE_ROOT')) {
    define('PHPUPGRADE_ROOT', dirname(__DIR__));
}
if (!defined('PHASE4_BASE_URL')) {
    define('PHASE4_BASE_URL', 'http://localhost/ElintOM18.00');
}

if (!function_exists('phase4_check')) {
    function phase4_isWeb()
    {
        return PHP_SAPI !== 'cli';
    }

    function phase4_webOutputStart()
    {
        if (phase4_isWeb()) {
            header('Content-Type: text/plain; charset=UTF-8');
        }
    }

    function phase4_credentials()
    {
        global $argv;
        $identity = $argv[1] ?? ($_GET['identity'] ?? '');
        $password = $argv[2] ?? ($_GET['password'] ?? '');
        return [$identity, $password];
    }

    function phase4_usage($scriptName)
    {
        $cli = "Usage: php $scriptName <identity> <password>";
        if (phase4_isWeb()) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $self = $_SERVER['SCRIPT_NAME'] ?? "/$scriptName";
            echo "$cli\n";
            echo "Browser: {$scheme}://{$host}{$self}?identity=USER&password=PASS\n";
            echo "Example: {$scheme}://{$host}{$self}?identity=Admin&password=Admin%40554\n";
        } else {
            echo "$cli\n";
        }
    }

    function phase4_check($name, $ok, $detail = '')
    {
        global $phase4_fail, $phase4_pass;
        if ($ok) {
            $phase4_pass++;
            echo "[PASS] $name" . ($detail ? " — $detail" : '') . "\n";
        } else {
            $phase4_fail++;
            echo "[FAIL] $name" . ($detail ? " — $detail" : '') . "\n";
        }
    }

    function phase4_httpRequest($url, $cookieFile, $post = null, $headers = [])
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHPUpgradeTest',
        ];
        if ($post !== null) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $post;
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        if ($headers) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        return [
            'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'body' => $body ?: '',
            'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
            'type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
        ];
    }

    function phase4_httpGetFollow($url, $cookieFile, $max = 6)
    {
        for ($i = 0; $i < $max; $i++) {
            $r = phase4_httpRequest($url, $cookieFile);
            if (!in_array($r['code'], [301, 302, 303, 307, 308], true) || empty($r['location'])) {
                return $r;
            }
            $url = $r['location'];
        }
        return $r;
    }

    function phase4_hasPhpIssue($body)
    {
        return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)|<(b|strong)>(Deprecated|Warning|Notice):/i', $body);
    }

    function phase4_extractCsrf($html)
    {
        if (preg_match('/name="token"\s+value="([^"]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/name="([^"]+)"\s+value="([^"]+)"[^>]*class="[^"]*token/i', $html, $m)) {
            return $m[2];
        }
        if (preg_match('/"name":\s*"token",\s*"value":\s*"([^"]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/CSRF_TOKEN_HASH\s*=\s*[\'"]([^\'"]+)[\'"]/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/meta\s+name="ci-csrf-hash"\s+content="([^"]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/get_csrf_hash\(\)\s*;\s*\?>\s*[\'"]([^\'"]+)[\'"]/', $html, $m)) {
            return $m[1];
        }
        return null;
    }

    function phase4_login($base, $identity, $password, $cookieFile)
    {
        @unlink($cookieFile);
        $r = phase4_httpRequest("$base/login", $cookieFile);
        $token = phase4_extractCsrf($r['body']);
        $r = phase4_httpRequest("$base/auth/login", $cookieFile, http_build_query([
            'identity' => $identity,
            'password' => $password,
            'login_device' => 'web',
            'token' => $token ?? '',
        ]));
        if (in_array($r['code'], [302, 303], true) && $r['location']) {
            phase4_httpRequest($r['location'], $cookieFile);
        }
        return !phase4_hasPhpIssue($r['body']);
    }

    function phase4_dtPost($csrfToken, $extra = [])
    {
        return http_build_query(array_merge([
            'sEcho' => 1,
            'iDisplayStart' => 0,
            'iDisplayLength' => 10,
            'token' => $csrfToken ?? '',
        ], $extra));
    }

    function phase4_firstDtId($jsonBody)
    {
        $json = json_decode($jsonBody, true);
        if (!is_array($json) || empty($json['aaData'][0][0])) {
            return null;
        }
        return (int) $json['aaData'][0][0];
    }

    function phase4_linkGet($base, $label, $path, $cookieFile, $codes = [200], $identity = '', $password = '')
    {
        for ($try = 0; $try < 3; $try++) {
            if ($try > 0) {
                usleep(250000);
                if ($identity && $password) {
                    phase4_login($base, $identity, $password, $cookieFile);
                }
            }
            $r = phase4_httpGetFollow("$base$path", $cookieFile);
            $ok = in_array($r['code'], $codes, true) && !phase4_hasPhpIssue($r['body']);
            if ($ok || !in_array($r['code'], [500, 403, 502, 503], true)) {
                break;
            }
        }
        $issue = '';
        if (phase4_hasPhpIssue($r['body']) && preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,160}/', $r['body'], $mm)) {
            $issue = ' | ' . trim(strip_tags($mm[0]));
        }
        phase4_check("$label ($path)", $ok, "HTTP {$r['code']}" . $issue);
        return $ok ? $r : null;
    }

    function phase4_actionsGetRedirect($base, $label, $actionsPath, $cookieFile, $expectFragment)
    {
        $r = phase4_httpRequest($base . $actionsPath, $cookieFile);
        $loc = $r['location'] ?? '';
        $ok = in_array($r['code'], [301, 302, 303, 307, 308], true)
            && stripos($loc, $expectFragment) !== false
            && !phase4_hasPhpIssue($r['body']);
        phase4_check($label, $ok, 'HTTP ' . $r['code'] . ' loc=' . $loc);
        return $ok;
    }

    function phase4_listHasTools($base, $label, $listPath, $cookieFile, $needles = [])
    {
        $r = phase4_httpGetFollow($base . $listPath, $cookieFile);
        $ok = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
        foreach ($needles as $needle) {
            if (stripos($r['body'], $needle) === false) {
                $ok = false;
            }
        }
        phase4_check($label, $ok, 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));
        return $ok ? $r : null;
    }

    function phase4_actionPost($base, $actionsPath, $cookieFile, $token, $action, $ids, $refererPath)
    {
        $post = ['form_action' => $action, 'token' => $token ?? ''];
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $i => $id) {
            $post['val[' . $i . ']'] = $id;
        }
        $ch = curl_init($base . $actionsPath);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post),
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Referer: ' . $base . $refererPath,
            ],
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 180,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($raw, 0, $hs);
        $body = substr($raw, $hs);
        $ct = '';
        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $m)) {
            $ct = trim($m[1]);
        }
        $loc = '';
        if (preg_match('/^Location:\s*([^\r\n]+)/im', $headers, $m)) {
            $loc = trim($m[1]);
        }
        return [
            'code' => $code,
            'body' => $body,
            'ct' => $ct,
            'location' => $loc,
            'issue' => phase4_hasPhpIssue($body),
        ];
    }

    function phase4_checkActionPost($base, $label, $actionsPath, $cookieFile, $token, $action, $ids, $refererPath, $expectCodes = [200, 302, 303])
    {
        $r = phase4_actionPost($base, $actionsPath, $cookieFile, $token, $action, $ids, $refererPath);
        $ok = in_array($r['code'], $expectCodes, true) && !$r['issue'];
        if ($ok && $r['code'] === 200) {
            $ok = strlen($r['body']) > 50;
        }
        $detail = 'HTTP ' . $r['code'];
        if ($r['ct']) {
            $detail .= ' CT=' . $r['ct'];
        }
        if ($r['location']) {
            $detail .= ' loc=' . $r['location'];
        }
        $detail .= ' len=' . strlen($r['body']);
        phase4_check($label, $ok, $detail);
        return $ok ? $r : null;
    }
}
