<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('cms_media_presets')) {
    /**
     * Folder presets for webshop CMS media library.
     *
     * @return array<string,array{slug:string,label:string,folder:string,hint:string}>
     */
    function cms_media_presets()
    {
        return array(
            'logos' => array(
                'slug'   => 'logos',
                'label'  => 'Logos',
                'folder' => 'cms_media/logos',
                'hint'   => 'Recommended ~400×150 px',
            ),
            'favicons' => array(
                'slug'   => 'favicons',
                'label'  => 'Favicons',
                'folder' => 'cms_media/favicons',
                'hint'   => '32×32 or 64×64 px',
            ),
            'page_banners' => array(
                'slug'   => 'page_banners',
                'label'  => 'Page banners',
                'folder' => 'cms_media/page_banners',
                'hint'   => 'Recommended ~1920×600 px',
            ),
            'heroes' => array(
                'slug'   => 'heroes',
                'label'  => 'Hero images',
                'folder' => 'cms_media/heroes',
                'hint'   => 'Recommended ~1600×900 px',
            ),
            'content' => array(
                'slug'   => 'content',
                'label'  => 'Content blocks',
                'folder' => 'cms_media/content',
                'hint'   => 'Up to ~1200×800 px',
            ),
            'social' => array(
                'slug'   => 'social',
                'label'  => 'Social icons',
                'folder' => 'cms_media/social',
                'hint'   => '64×64 px square',
            ),
            'misc' => array(
                'slug'   => 'misc',
                'label'  => 'General',
                'folder' => 'cms_media/misc',
                'hint'   => 'Any storefront image',
            ),
        );
    }
}

if (!function_exists('cms_media_preset')) {
    /**
     * @param string $slug
     * @return array<string,mixed>|null
     */
    function cms_media_preset($slug)
    {
        $slug = strtolower(trim((string) $slug));
        $presets = cms_media_presets();
        return isset($presets[$slug]) ? $presets[$slug] : null;
    }
}

if (!function_exists('cms_media_allowed_extensions')) {
    function cms_media_allowed_extensions()
    {
        return array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico');
    }
}

if (!function_exists('cms_media_is_image_file')) {
    function cms_media_is_image_file($filename)
    {
        $ext = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
        return in_array($ext, cms_media_allowed_extensions(), true);
    }
}

if (!function_exists('cms_media_webshop_disk_root')) {
    /**
     * Absolute path to uploads/webshop/ for a tenant.
     *
     * @param string $customer_assets
     * @return string
     */
    function cms_media_webshop_disk_root($customer_assets)
    {
        $tenant = trim((string) $customer_assets);
        if ($tenant === '') {
            $tenant = 'localhost';
        }
        return rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'mdata'
            . DIRECTORY_SEPARATOR . $tenant . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'webshop'
            . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('cms_media_uploads_base_url')) {
    function cms_media_uploads_base_url($customer_assets)
    {
        $tenant = trim((string) $customer_assets);
        if ($tenant === '') {
            $tenant = 'localhost';
        }
        return base_url('assets/mdata/' . $tenant . '/uploads/');
    }
}

if (!function_exists('cms_media_stored_path')) {
    /**
     * Build stored value written to JSON/DB (relative to uploads/).
     *
     * @param string $preset_slug
     * @param string $file_name
     * @return string
     */
    function cms_media_stored_path($preset_slug, $file_name)
    {
        $preset = cms_media_preset($preset_slug);
        $folder = $preset ? (string) $preset['folder'] : 'cms_media/misc';
        $file_name = ltrim(str_replace('\\', '/', (string) $file_name), '/');
        return 'webshop/' . trim($folder, '/') . '/' . $file_name;
    }
}

if (!function_exists('cms_media_preset_from_stored_path')) {
    /**
     * Infer preset slug from stored path or legacy folder layout.
     *
     * @param string $stored_path Relative to uploads/ (webshop/…)
     * @return string
     */
    function cms_media_preset_from_stored_path($stored_path)
    {
        $p = strtolower(str_replace('\\', '/', trim((string) $stored_path, '/')));
        if (preg_match('#^webshop/cms_media/([^/]+)/#', $p, $m)) {
            $slug = (string) $m[1];
            if ($slug === 'page_banners') {
                return 'page_banners';
            }
            if (isset(cms_media_presets()[$slug])) {
                return $slug;
            }
        }
        if (preg_match('#^webshop/cms_pages/#', $p)) {
            return 'page_banners';
        }
        if (preg_match('#^webshop/uploads/#', $p)) {
            return 'content';
        }
        if (preg_match('#/(favicon|ico)#', $p)) {
            return 'favicons';
        }
        if (preg_match('#/(logo|brand)#', $p)) {
            return 'logos';
        }
        if (preg_match('#/social#', $p)) {
            return 'social';
        }
        if (preg_match('#/(hero|banner)#', $p)) {
            return preg_match('#cms_pages#', $p) ? 'page_banners' : 'heroes';
        }
        $basename = basename($p);
        if (preg_match('#favicon|\.ico$#i', $basename)) {
            return 'favicons';
        }
        if (preg_match('#logo#i', $basename)) {
            return 'logos';
        }
        if (preg_match('#banner|hero#i', $basename)) {
            return preg_match('#cms_pages#', $p) ? 'page_banners' : 'heroes';
        }
        return 'misc';
    }
}

if (!function_exists('cms_media_public_url')) {
    /**
     * Full public URL for a stored media path (uploads-relative or filename-only legacy banner).
     *
     * @param string $stored_path
     * @param string $customer_assets
     * @return string
     */
    function cms_media_public_url($stored_path, $customer_assets = '')
    {
        $stored_path = trim((string) $stored_path);
        if ($stored_path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $stored_path)) {
            return $stored_path;
        }
        $base = cms_media_uploads_base_url($customer_assets);
        $stored_path = str_replace('\\', '/', $stored_path);
        $stored_path = preg_replace('#^cms_pages/(cms_blogs|cms_testimonials)/#i', '$1/', $stored_path);
        if (strpos($stored_path, 'webshop/') === 0) {
            return rtrim($base, '/') . '/' . $stored_path;
        }
        if (preg_match('#^(cms_blogs|cms_testimonials|cms_pages)/#i', $stored_path)) {
            return rtrim($base, '/') . '/webshop/' . ltrim($stored_path, '/');
        }
        // Legacy page banner: filename only under cms_pages/
        return rtrim($base, '/') . '/webshop/cms_pages/' . ltrim($stored_path, '/');
    }
}

if (!function_exists('cms_media_normalize_stored_path')) {
    /**
     * Normalize picker/post value to uploads-relative stored path.
     *
     * @param string $value
     * @return string
     */
    function cms_media_normalize_stored_path($value)
    {
        $v = trim(str_replace('\\', '/', (string) $value));
        if ($v === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $v)) {
            $path = parse_url($v, PHP_URL_PATH);
            $v = is_string($path) ? $path : '';
        }
        if (preg_match('#/assets/mdata/[^/]+/uploads/(.+)$#i', $v, $m)) {
            $v = (string) $m[1];
        }
        $v = preg_replace('#^uploads/#i', '', $v);
        $v = ltrim($v, '/');
        if (strpos($v, 'webshop/') !== 0 && cms_media_is_image_file($v)) {
            $v = 'webshop/cms_pages/' . $v;
        }
        return $v;
    }
}

if (!function_exists('cms_media_scan_files')) {
    /**
     * Scan webshop uploads tree for image files.
     *
     * @param string $customer_assets
     * @param array<string,mixed> $options preset, q, limit, offset
     * @return array{items:array<int,array<string,mixed>>,total:int}
     */
    function cms_media_scan_files($customer_assets, array $options = array())
    {
        $root = cms_media_webshop_disk_root($customer_assets);
        $uploads_base = cms_media_uploads_base_url($customer_assets);
        $preset_filter = isset($options['preset']) ? strtolower(trim((string) $options['preset'])) : '';
        $q = isset($options['q']) ? strtolower(trim((string) $options['q'])) : '';
        $limit = isset($options['limit']) ? max(1, (int) $options['limit']) : 200;
        $offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;

        $skip_dirs = array(
            'images' . DIRECTORY_SEPARATOR . 'credit-cards',
            'images' . DIRECTORY_SEPARATOR . 'secured-by',
        );

        $items = array();
        if (!is_dir($root)) {
            return array('items' => array(), 'total' => 0);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $name = $fileInfo->getFilename();
            if (strtolower($name) === 'index.html') {
                continue;
            }
            if (!cms_media_is_image_file($name)) {
                continue;
            }

            $full = $fileInfo->getPathname();
            $rel_from_webshop = str_replace('\\', '/', substr($full, strlen($root)));
            $rel_from_webshop = ltrim($rel_from_webshop, '/');

            $skip = false;
            foreach ($skip_dirs as $skip_dir) {
                if (stripos(str_replace('/', DIRECTORY_SEPARATOR, $rel_from_webshop), str_replace('/', DIRECTORY_SEPARATOR, $skip_dir)) === 0) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            $stored_path = 'webshop/' . $rel_from_webshop;
            $preset = cms_media_preset_from_stored_path($stored_path);
            if ($preset_filter !== '' && $preset_filter !== 'all' && $preset !== $preset_filter) {
                continue;
            }
            if ($q !== '' && strpos(strtolower($name), $q) === false && strpos(strtolower($stored_path), $q) === false) {
                continue;
            }

            $size = (int) $fileInfo->getSize();
            $mtime = (int) $fileInfo->getMTime();
            $width = 0;
            $height = 0;
            if (in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)) {
                $dims = @getimagesize($full);
                if (is_array($dims)) {
                    $width = isset($dims[0]) ? (int) $dims[0] : 0;
                    $height = isset($dims[1]) ? (int) $dims[1] : 0;
                }
            }

            $items[] = array(
                'stored_path' => $stored_path,
                'file_name'   => $name,
                'preset'      => $preset,
                'url'         => rtrim($uploads_base, '/') . '/' . $stored_path,
                'size'        => $size,
                'size_label'  => cms_media_format_bytes($size),
                'width'       => $width,
                'height'      => $height,
                'modified'    => $mtime,
                'modified_label' => date('Y-m-d H:i', $mtime),
                'deletable'   => (bool) preg_match('#^webshop/cms_media/#i', $stored_path),
            );
        }

        usort($items, function ($a, $b) {
            return (int) $b['modified'] - (int) $a['modified'];
        });

        $total = count($items);
        if ($offset > 0 || $limit < $total) {
            $items = array_slice($items, $offset, $limit);
        }

        return array('items' => $items, 'total' => $total);
    }
}

if (!function_exists('cms_media_format_bytes')) {
    function cms_media_format_bytes($bytes)
    {
        $bytes = (int) $bytes;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 2) . ' MB';
    }
}

if (!function_exists('cms_media_is_deletable_path')) {
    function cms_media_is_deletable_path($stored_path)
    {
        $stored_path = cms_media_normalize_stored_path($stored_path);
        return (bool) preg_match('#^webshop/cms_media/#i', $stored_path);
    }
}
