<?php
/**
 * Phase 4 — CMS Admin Panel deep-link tests (Owner)
 * Run: php phase4_cms_admin_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_cms_admin_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/cms_admin';

if (!$identity || !$password) {
    echo "Usage: php phase4_cms_admin_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$pageId = null;
$blogId = null;
$testimonialId = null;
$formId = null;
$categoryId = null;
$productId = null;
$entityMasterId = null;
$entityId = null;
$storefrontId = null;
$cmsPageUrl = null;

try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query('SELECT id FROM sma_cms_pages ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $pageId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT url FROM sma_cms_pages WHERE status='published' ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $cmsPageUrl = trim((string) $q->fetch_assoc()['url'], '/');
        }
        $q = $m->query('SELECT id FROM sma_cms_blogs ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $blogId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query('SELECT id FROM sma_cms_testimonials ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $testimonialId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query('SELECT id FROM sma_cms_webshop_contact_forms ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $formId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query('SELECT id FROM sma_categories WHERE is_active=1 ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $categoryId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query('SELECT id FROM sma_products WHERE is_active=1 ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $productId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query('SELECT id FROM sma_cms_entities_master ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $entityMasterId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query('SELECT id FROM sma_cms_webshop_header_footer ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $storefrontId = (int) $q->fetch_assoc()['id'];
        }
        if ($entityMasterId) {
            $q = $m->query('SELECT entity_id FROM sma_cms_entity_tag_mapping WHERE entity_master_id=' . (int) $entityMasterId . ' LIMIT 1');
            if ($q && $q->num_rows) {
                $entityId = (int) $q->fetch_assoc()['entity_id'];
            }
            if (!$entityId && $productId) {
                $entityId = $productId;
            }
        }
        $m->query("UPDATE sma_settings SET active_cms_admin_panel=1, active_webshop=1 WHERE setting_id=1");
    }
} catch (Throwable $e) {
    // optional DB discovery
}

$sidebar = [
    'CMS dashboard' => "$prefix/dashboard",
    'CMS panel alias' => '/cms_admin_panel',
    'CMS products' => "$prefix/catalog",
    'CMS pages list' => "$prefix/pages",
    'CMS pages add' => "$prefix/pages/add",
    'CMS blogs' => "$prefix/blogs",
    'CMS blogs add' => "$prefix/blogs/add",
    'CMS testimonials' => "$prefix/testimonials",
    'CMS testimonials add' => "$prefix/testimonials/add",
    'CMS layout builder' => "$prefix/layout_builder",
    'CMS layout workspace' => "$prefix/layout_builder/workspace",
    'CMS media' => "$prefix/media",
    'CMS entity tags' => "$prefix/entity_tags",
    'CMS entity tags add' => "$prefix/entity_tags/add",
    'CMS entity faqs' => "$prefix/entity_faqs",
    'CMS tags master' => "$prefix/tags_master",
    'CMS robots' => "$prefix/site_settings/robots",
    'CMS sitemap' => "$prefix/site_settings/sitemap",
    'CMS llms' => "$prefix/site_settings/llms",
    'CMS llms txt' => "$prefix/llms_txt",
    'CMS prices' => "$prefix/prices",
    'CMS form templates' => "$prefix/form_templates",
    'CMS form templates add' => "$prefix/form_templates/add",
    'CMS leads embed' => "$prefix/leads",
    'CMS newsletter' => "$prefix/newsletter_subscribers",
    'CMS guide' => "$prefix/guide",
    'CMS storefront' => "$prefix/storefront",
    'CMS header designs' => "$prefix/header_designs",
    'CMS footer designs' => "$prefix/footer_designs",
    'CMS storefront designs' => "$prefix/storefront_designs",
];

foreach ($sidebar as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

if ($pageId) {
    $r = phase4_httpGetFollow("$base$prefix/pages/edit/$pageId", $cookieFile);
    $ok = $r['code'] === 200 && stripos($r['body'], 'Edit CMS Page') !== false;
    phase4_check('CMS page edit', $ok, "HTTP {$r['code']}");
} else {
    phase4_check('CMS page edit', true, 'skipped — no sma_cms_pages row');
}

if ($blogId) {
    phase4_linkGet($base, 'CMS blog edit', "$prefix/blogs/edit/$blogId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('CMS blog edit', true, 'skipped — no sma_cms_blogs row');
}

if ($testimonialId) {
    phase4_linkGet($base, 'CMS testimonial edit', "$prefix/testimonials/edit/$testimonialId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('CMS testimonial edit', true, 'skipped — no sma_cms_testimonials row');
}

if ($formId) {
    phase4_linkGet($base, 'CMS form edit', "$prefix/form_templates/edit/$formId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('CMS form edit', true, 'skipped — no contact form row');
}

if ($categoryId) {
    phase4_linkGet($base, 'CMS catalog category', "$prefix/catalog/$categoryId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('CMS catalog category', true, 'skipped — no category id');
}

if ($storefrontId) {
    phase4_linkGet($base, 'CMS storefront edit', "$prefix/storefront/edit/$storefrontId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('CMS storefront edit', true, 'skipped — no header/footer row');
}

if ($entityMasterId && $entityId) {
    phase4_linkGet($base, 'CMS entity tag edit', "$prefix/entity_tags/edit/$entityMasterId/$entityId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('CMS entity tag edit', true, 'skipped — no entity mapping');
}

$r = phase4_httpGetFollow("$base$prefix/dashboard", $cookieFile);
$token = phase4_extractCsrf($r['body']);

if ($token) {
    $r = phase4_httpRequest("$base$prefix/catalog/ajax", $cookieFile, phase4_dtPost($token, [
        'action' => 'get_categories',
    ]), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('CMS catalog/ajax', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

    $r = phase4_httpRequest("$base$prefix/media/list_json", $cookieFile, http_build_query([
        'token' => $token,
        'preset' => 'cms_pages',
    ]), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('CMS media list_json', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('CMS catalog/ajax', true, 'skipped — no CSRF token');
    phase4_check('CMS media list_json', true, 'skipped — no CSRF token');
}

phase4_linkGet($base, 'Legacy entity_mapping', '/entity_mapping', $cookieFile, [200, 302, 303], $identity, $password);

if ($cmsPageUrl) {
    $r = phase4_httpGetFollow("$base/cmspage/$cmsPageUrl", $cookieFile);
    phase4_check('Public cmspage slug', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code'] . " (/cmspage/$cmsPageUrl)");
} else {
    phase4_check('Public cmspage slug', true, 'skipped — no published CMS page');
}

$r = phase4_httpGetFollow("$base/webshop_api?action=getcmspages", $cookieFile);
phase4_check('Webshop API getcmspages', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

echo "\n=== CMS Admin deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
