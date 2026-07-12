-- CMS Admin full schema for ElintOM18.00 (sma_cms_* tables)
-- Run once per tenant DB.

-- CMS schema + seed reference
-- Saved for project reference and reuse.

-- =========================================
-- 1. sma_cms_sections_master
-- =========================================
CREATE TABLE sma_cms_sections_master (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL,
    section_type VARCHAR(100) NOT NULL,
    ui_component VARCHAR(100) NOT NULL,
    util_function VARCHAR(100) NOT NULL,
    config_schema JSON NULL,
    is_dynamic BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_section_type (section_type)
);

-- =========================================
-- 2. sma_cms_tags_master
-- =========================================
CREATE TABLE sma_cms_tags_master (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100) NOT NULL,
    tag_type VARCHAR(50) NOT NULL,
    category VARCHAR(50) NULL,
    page_type VARCHAR(50) NULL,
    template TEXT NULL,
    implementation_code TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_tag_name_type (tag_name, tag_type)
);

-- =========================================
-- 3. sma_cms_pages
-- =========================================
CREATE TABLE sma_cms_pages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(150) NOT NULL,
    page_type VARCHAR(50) NOT NULL,
    reference_id BIGINT NULL,
    url VARCHAR(255) NOT NULL,
    status ENUM('draft','published') DEFAULT 'draft',
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_url (url),
    INDEX idx_page_type (page_type),
    INDEX idx_reference_id (reference_id)
);

-- Ensure page level media columns are present for CMS page editor upload fields.
ALTER TABLE sma_cms_pages
    ADD COLUMN IF NOT EXISTS `banner_image` VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS `logo_image` VARCHAR(255) NULL;

-- =========================================
-- 4. sma_cms_page_section_mapping
-- =========================================
CREATE TABLE sma_cms_page_section_mapping (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT NOT NULL,
    section_id BIGINT NOT NULL,
    sort_order INT NOT NULL,
    section_contain JSON NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_psm_page
        FOREIGN KEY (page_id)
        REFERENCES sma_cms_pages(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_psm_section
        FOREIGN KEY (section_id)
        REFERENCES sma_cms_sections_master(id),

    UNIQUE KEY uk_page_section_order (page_id, sort_order),
    INDEX idx_page_sections (page_id)
);

-- Add visibility columns for section placement controls.
ALTER TABLE sma_cms_page_section_mapping
    ADD COLUMN IF NOT EXISTS `header` ENUM('yes','no') DEFAULT 'no' AFTER `section_contain`,
    ADD COLUMN IF NOT EXISTS `footer` ENUM('yes','no') DEFAULT 'no' AFTER `header`,
    ADD COLUMN IF NOT EXISTS `banner` ENUM('yes','no') DEFAULT 'no' AFTER `footer`,
    ADD COLUMN IF NOT EXISTS `logo` ENUM('yes','no') DEFAULT 'no' AFTER `banner`;

-- =========================================
-- 5. sma_cms_page_tag_mapping
-- =========================================
CREATE TABLE sma_cms_page_tag_mapping (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT NOT NULL,
    tag_id BIGINT NOT NULL,
    property_name VARCHAR(100) NOT NULL,
    value TEXT NULL,
    is_dynamic BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ptm_page
        FOREIGN KEY (page_id)
        REFERENCES sma_cms_pages(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_ptm_tag
        FOREIGN KEY (tag_id)
        REFERENCES sma_cms_tags_master(id),

    UNIQUE KEY uk_page_tag_property
        (page_id, tag_id, property_name),

    INDEX idx_page_tags (page_id)
);

-- =========================================
-- 6. sma_cms_page_type_tag_defaults
-- =========================================
CREATE TABLE sma_cms_page_type_tag_defaults (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_type VARCHAR(50) NOT NULL,
    tag_id BIGINT NOT NULL,
    property_name VARCHAR(100) NOT NULL,
    value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pttd_tag
        FOREIGN KEY (tag_id)
        REFERENCES sma_cms_tags_master(id),

    UNIQUE KEY uk_page_type_tag
        (page_type, tag_id, property_name)
);

-- =========================================
-- 7. sma_cms_global_tag_defaults
-- =========================================
CREATE TABLE sma_cms_global_tag_defaults (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tag_id BIGINT NOT NULL,
    property_name VARCHAR(100) NOT NULL,
    value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_global_tag
        FOREIGN KEY (tag_id)
        REFERENCES sma_cms_tags_master(id),

    UNIQUE KEY uk_global_tag
        (tag_id, property_name)
);

-- =========================================
-- INSERT INTO sma_cms_sections_master
-- =========================================
INSERT INTO sma_cms_sections_master
(section_name, section_type, ui_component, util_function, config_schema, is_dynamic)
VALUES
('HTML Component', 'html_block', 'HtmlBlockComponent', 'getHtmlBlockData', NULL, FALSE),
('Product Carousel', 'product_carousel', 'ProductCarouselComponent', 'getProductCarouselData', NULL, TRUE),
('Product Grid', 'product_grid', 'ProductGridComponent', 'getProductGridData', NULL, TRUE),
('Category Carousel', 'category_carousel', 'CategoryCarouselComponent', 'getCategoryCarouselData', NULL, TRUE),
('Category Grid', 'category_grid', 'CategoryGridComponent', 'getCategoryGridData', NULL, TRUE),
('Header', 'header', 'HeaderComponent', 'getHeaderData', NULL, FALSE),
('Footer', 'footer', 'FooterComponent', 'getFooterData', NULL, FALSE);

-- =========================================
-- INSERT INTO sma_cms_pages
-- =========================================
INSERT INTO sma_cms_pages
(page_name, page_type, reference_id, url, status)
VALUES
('Home', 'home', NULL, '/', 'published'),
('Category Page / Product List', 'category', NULL, '/category', 'published'),
('Product Details', 'product', NULL, '/product', 'published'),
('Category List', 'category_list', NULL, '/categories', 'published'),
('About Us', 'static', NULL, '/about-us', 'published'),
('Privacy Policy', 'static', NULL, '/privacy-policy', 'published'),
('Terms & Conditions', 'static', NULL, '/terms', 'published');

-- =========================================
-- INSERT INTO sma_cms_page_section_mapping
-- =========================================

-- HOME PAGE
INSERT INTO sma_cms_page_section_mapping
(page_id, section_id, sort_order, section_contain, is_enabled)
VALUES
(1, 6, 1, NULL, TRUE),
(1, 1, 2, JSON_OBJECT('content', '<h1>Welcome to Our Store</h1>'), TRUE),
(1, 3, 3, JSON_OBJECT('products_per_page', 8, 'columns_desktop', 4), TRUE),
(1, 5, 4, JSON_OBJECT('columns_desktop', 4), TRUE),
(1, 7, 5, NULL, TRUE);

-- =========================================
-- INSERT INTO sma_cms_tags_master
-- =========================================
INSERT INTO sma_cms_tags_master
(tag_name, tag_type, category, page_type, template, implementation_code)
VALUES
('title', 'meta', 'SEO', 'all',
'{page_title}',
'<title>{page_title}</title>'),

('meta_description', 'meta', 'SEO', 'all',
'{meta_description}',
'<meta name="description" content="{meta_description}">'),

('canonical', 'link', 'SEO', 'all',
'{canonical_url}',
'<link rel="canonical" href="{canonical_url}">'),

('robots', 'meta', 'SEO', 'all',
'{robots}',
'<meta name="robots" content="{robots}">'),

('viewport', 'meta', 'SEO', 'all',
'width=device-width, initial-scale=1.0',
'<meta name="viewport" content="width=device-width, initial-scale=1.0">');

-- =========================================
-- INSERT REMAINING TAG MASTER ENTRIES (6..30)
-- =========================================
INSERT INTO sma_cms_tags_master
(tag_name, tag_type, category, page_type, template, implementation_code)
VALUES
('og:type', 'og', 'SEO', 'all',
'{og_type}',
'<meta property="og:type" content="{og_type}">'),

('og:title', 'og', 'SEO', 'all',
'{og_title}',
'<meta property="og:title" content="{og_title}">'),

('og:description', 'og', 'SEO', 'all',
'{og_description}',
'<meta property="og:description" content="{og_description}">'),

('og:url', 'og', 'SEO', 'all',
'{og_url}',
'<meta property="og:url" content="{og_url}">'),

('og:image', 'og', 'SEO', 'all',
'{og_image}',
'<meta property="og:image" content="{og_image}">'),

('hreflang', 'link', 'SEO', 'all',
'{hreflang_url}',
'<link rel="alternate" hreflang="en" href="{hreflang_url}">'),

('geo.region', 'meta', 'SEO', 'all',
'{geo_region}',
'<meta name="geo.region" content="{geo_region}">'),

('geo.position', 'meta', 'SEO', 'all',
'{geo_position}',
'<meta name="geo.position" content="{geo_position}">'),

('ICBM', 'meta', 'SEO', 'all',
'{geo_lat},{geo_lng}',
'<meta name="ICBM" content="{geo_lat},{geo_lng}">'),

('theme_color', 'meta', 'SEO', 'all',
'{theme_color}',
'<meta name="theme-color" content="{theme_color}">'),

('last_modified', 'meta', 'SEO', 'all',
'{last_modified}',
'<meta name="last-modified" content="{last_modified}">'),

('copyright', 'meta', 'SEO', 'all',
'{copyright}',
'<meta name="copyright" content="{copyright}">'),

('rss_feed', 'link', 'SEO', 'all',
'{rss_url}',
'<link rel="alternate" type="application/rss+xml" title="RSS" href="{rss_url}">'),

('pharmacy_schema', 'schema', 'SEO', 'all',
'{
"@context":"https://schema.org",
"@type":"Pharmacy",
"name":"{site_name}"
}',
'<script type="application/ld+json">{schema_json}</script>'),

('product_schema', 'schema', 'SEO', 'product',
'{
"@context":"https://schema.org",
"@type":"Product",
"name":"{product_name}"
}',
'<script type="application/ld+json">{schema_json}</script>'),

('article_schema', 'schema', 'SEO', 'blog',
'{
"@context":"https://schema.org",
"@type":"Article",
"headline":"{headline}"
}',
'<script type="application/ld+json">{schema_json}</script>'),

('faq_schema', 'schema', 'SEO', 'all',
'{faq_json}',
'<script type="application/ld+json">{faq_json}</script>'),

('ai_entity', 'meta', 'AI', 'all',
'{ai_entity}',
'<meta name="ai:entity" content="{ai_entity}">'),

('ai_summary', 'meta', 'AI', 'all',
'{ai_summary}',
'<meta name="ai:summary" content="{ai_summary}">'),

('ai_category', 'meta', 'AI', 'all',
'{ai_category}',
'<meta name="ai:category" content="{ai_category}">'),

('ai_industry', 'meta', 'AI', 'all',
'{ai_industry}',
'<meta name="ai:industry" content="{ai_industry}">'),

('ai_brand', 'meta', 'AI', 'all',
'{ai_brand}',
'<meta name="ai:brand" content="{ai_brand}">'),

('ai_purpose', 'meta', 'AI', 'all',
'{ai_purpose}',
'<meta name="ai:purpose" content="{ai_purpose}">'),

('ai_keyphrase', 'meta', 'AI', 'all',
'{ai_keyphrase}',
'<meta name="ai:keyphrase" content="{ai_keyphrase}">'),

('ai_context', 'meta', 'AI', 'all',
'{ai_context}',
'<meta name="ai:context" content="{ai_context}">');

-- =========================================
-- INSERT DATA INTO sma_cms_page_tag_mapping
-- PAGE ID = 1 (HOME PAGE)
-- =========================================

INSERT INTO sma_cms_page_tag_mapping
(page_id, tag_id, property_name, value, is_dynamic)
VALUES

-- =========================================
-- CORE SEO TAGS
-- =========================================

(1, 1, 'title', 'Gulf Pharmacy Dubai | Online Pharmacy UAE', TRUE),

(1, 2, 'meta_description',
'Gulf Pharmacy is a trusted Dubai pharmacy offering vitamins, supplements, skincare products, and home health devices with fast delivery across UAE.',
TRUE),

(1, 3, 'canonical',
'https://gulfpharmacy.com/',
FALSE),

(1, 4, 'robots',
'index, follow, max-snippet:-1, max-image-preview:large',
FALSE),

(1, 5, 'viewport',
'width=device-width, initial-scale=1.0',
FALSE),

-- =========================================
-- OPEN GRAPH TAGS
-- =========================================

(1, 6, 'og:type',
'website',
TRUE),

(1, 7, 'og:title',
'Gulf Pharmacy Dubai',
TRUE),

(1, 8, 'og:description',
'Online pharmacy offering vitamins, skincare and health devices.',
TRUE),

(1, 9, 'og:url',
'https://gulfpharmacy.com',
FALSE),

(1, 10, 'og:image',
'https://gulfpharmacy.com/assets/images/og-image.jpg',
FALSE),

-- =========================================
-- GEO TAGS
-- =========================================

(1, 11, 'hreflang',
'https://gulfpharmacy.com/',
FALSE),

(1, 12, 'geo.region',
'AE-DU',
FALSE),

(1, 13, 'geo.position',
'25.2048;55.2708',
FALSE),

(1, 14, 'ICBM',
'25.2048,55.2708',
FALSE),

-- =========================================
-- TECHNICAL TAGS
-- =========================================

(1, 15, 'theme_color',
'#1f7a63',
FALSE),

(1, 16, 'last_modified',
'2026-05-06T10:00:00Z',
TRUE),

(1, 17, 'copyright',
'© Gulf Pharmacy UAE',
FALSE),

(1, 18, 'rss_feed',
'https://gulfpharmacy.com/blog/rss.xml',
FALSE),

-- =========================================
-- SCHEMA TAGS
-- =========================================

(1, 19, 'pharmacy_schema',
'{
  "@context":"https://schema.org",
  "@type":"Pharmacy",
  "name":"Gulf Pharmacy",
  "url":"https://gulfpharmacy.com",
  "logo":"https://gulfpharmacy.com/logo.png",
  "description":"Dubai based online pharmacy"
}',
TRUE),

(1, 20, 'product_schema',
'{
  "@context":"https://schema.org",
  "@type":"Product",
  "name":"Vitamin D3 Supplements"
}',
TRUE),

(1, 21, 'article_schema',
'{
  "@context":"https://schema.org",
  "@type":"Article",
  "headline":"Best Vitamins for Immunity"
}',
TRUE),

(1, 22, 'faq_schema',
'{
  "@context":"https://schema.org",
  "@type":"FAQPage"
}',
TRUE),

-- =========================================
-- AI METADATA TAGS
-- =========================================

(1, 23, 'ai_entity',
'Gulf Pharmacy; Dubai Pharmacy; Online Pharmacy UAE',
TRUE),

(1, 24, 'ai_summary',
'Gulf Pharmacy is a Dubai-based online pharmacy offering vitamins, supplements, skincare products, and healthcare devices.',
TRUE),

(1, 25, 'ai_category',
'Healthcare Retail, Online Pharmacy',
TRUE),

(1, 26, 'ai_industry',
'Pharmacy, Healthcare Retail',
TRUE),

(1, 27, 'ai_brand',
'Gulf Pharmacy',
TRUE),

(1, 28, 'ai_purpose',
'Helping UAE residents improve health through trusted pharmacy products.',
TRUE),

(1, 29, 'ai_keyphrase',
'Dubai pharmacy, vitamin supplements UAE, skincare Dubai climate',
TRUE),

(1, 30, 'ai_context',
'Healthcare Retail, Pharmacy, Wellness Supplements, Skincare',
TRUE);

-- =========================================
-- CHECK INSERTED DATA
-- =========================================

SELECT
    ptm.id,
    ptm.page_id,
    stm.tag_name,
    ptm.property_name,
    ptm.value
FROM sma_cms_page_tag_mapping ptm
LEFT JOIN sma_cms_tags_master stm
    ON stm.id = ptm.tag_id
WHERE ptm.page_id = 1;

-- =========================================
-- MEDICINE WEBSITE COMPLETE DEMO DATA
-- =========================================

-- =========================================
-- INSERT 5 PAGES
-- =========================================

INSERT INTO sma_cms_pages
(page_name, page_type, reference_id, url, status)
VALUES
('Home', 'home', NULL, '/', 'published'),
('Medicines', 'category', NULL, '/medicines', 'published'),
('Product Details - Paracetamol', 'product', 101, '/product/paracetamol-500', 'published'),
('Health Blog', 'blog', NULL, '/health-blog', 'published'),
('Contact Us', 'static', NULL, '/contact-us', 'published');

-- =========================================
-- HOME PAGE SECTION MAPPING
-- PAGE ID = 1
-- =========================================

INSERT INTO sma_cms_page_section_mapping
(page_id, section_id, sort_order, section_contain, is_enabled)
VALUES
(1, 6, 1, NULL, TRUE),
(1, 1, 2,
JSON_OBJECT(
'content',
'<div class="hero-banner">
<h1>Trusted Online Pharmacy</h1>
<p>Buy medicines online with fast delivery</p>
</div>'
),
TRUE),
(1, 3, 3,
JSON_OBJECT(
'title', 'Popular Medicines',
'products_per_page', 8,
'columns_desktop', 4
),
TRUE),
(1, 4, 4,
JSON_OBJECT(
'title', 'Shop By Category'
),
TRUE),
(1, 7, 5, NULL, TRUE);

-- =========================================
-- MEDICINES PAGE
-- PAGE ID = 2
-- =========================================

INSERT INTO sma_cms_page_section_mapping
(page_id, section_id, sort_order, section_contain, is_enabled)
VALUES
(2, 6, 1, NULL, TRUE),
(2, 1, 2,
JSON_OBJECT(
'content',
'<h2>All Medicines</h2>'
),
TRUE),
(2, 3, 3,
JSON_OBJECT(
'products_per_page', 20,
'columns_desktop', 5,
'filter', TRUE
),
TRUE),
(2, 7, 4, NULL, TRUE);

-- =========================================
-- PRODUCT PAGE
-- PAGE ID = 3
-- =========================================

INSERT INTO sma_cms_page_section_mapping
(page_id, section_id, sort_order, section_contain, is_enabled)
VALUES
(3, 6, 1, NULL, TRUE),
(3, 1, 2,
JSON_OBJECT(
'content',
'<h1>Paracetamol 500mg Tablets</h1>'
),
TRUE),
(3, 3, 3,
JSON_OBJECT(
'title', 'Related Products',
'products_per_page', 4
),
TRUE),
(3, 7, 4, NULL, TRUE);

-- =========================================
-- BLOG PAGE
-- PAGE ID = 4
-- =========================================

INSERT INTO sma_cms_page_section_mapping
(page_id, section_id, sort_order, section_contain, is_enabled)
VALUES
(4, 6, 1, NULL, TRUE),
(4, 1, 2,
JSON_OBJECT(
'content',
'<h1>Healthcare Blog</h1>'
),
TRUE),
(4, 1, 3,
JSON_OBJECT(
'content',
'<div class="blog-list">
<h3>Best Vitamins For Immunity</h3>
<p>Improve your health naturally.</p>
</div>'
),
TRUE),
(4, 7, 4, NULL, TRUE);

-- =========================================
-- CONTACT PAGE
-- PAGE ID = 5
-- =========================================

INSERT INTO sma_cms_page_section_mapping
(page_id, section_id, sort_order, section_contain, is_enabled)
VALUES
(5, 6, 1, NULL, TRUE),
(5, 1, 2,
JSON_OBJECT(
'content',
'<h1>Contact Our Pharmacy</h1>
<p>Email: support@medicarepharmacy.com</p>
<p>Phone: +971500000000</p>'
),
TRUE),
(5, 7, 3, NULL, TRUE);

-- =========================================
-- HOME PAGE SEO TAGS
-- PAGE ID = 1
-- =========================================

INSERT INTO sma_cms_page_tag_mapping
(page_id, tag_id, property_name, value, is_dynamic)
VALUES
(1, 1, 'title',
'Medicare Pharmacy UAE | Buy Medicines Online',
TRUE),
(1, 2, 'meta_description',
'Online pharmacy in UAE offering medicines, vitamins, skincare and healthcare products with quick delivery.',
TRUE),
(1, 6, 'og:type',
'website',
TRUE),
(1, 7, 'og:title',
'Medicare Pharmacy UAE',
TRUE),
(1, 23, 'ai_entity',
'Online Pharmacy UAE; Medicare Pharmacy',
TRUE);

-- =========================================
-- MEDICINES PAGE SEO
-- PAGE ID = 2
-- =========================================

INSERT INTO sma_cms_page_tag_mapping
(page_id, tag_id, property_name, value, is_dynamic)
VALUES
(2, 1, 'title',
'Buy Medicines Online UAE',
TRUE),
(2, 2, 'meta_description',
'Browse all pharmacy medicines online with best prices.',
TRUE),
(2, 29, 'ai_keyphrase',
'medicines online UAE, pharmacy products',
TRUE);

-- =========================================
-- PRODUCT PAGE SEO
-- PAGE ID = 3
-- =========================================

INSERT INTO sma_cms_page_tag_mapping
(page_id, tag_id, property_name, value, is_dynamic)
VALUES
(3, 1, 'title',
'Paracetamol 500mg Tablets',
TRUE),
(3, 2, 'meta_description',
'Buy Paracetamol 500mg tablets online for fever and pain relief.',
TRUE),
(3, 20, 'product_schema',
'{
"@context":"https://schema.org",
"@type":"Product",
"name":"Paracetamol 500mg",
"brand":"MediCare",
"description":"Pain relief medicine"
}',
TRUE);

-- =========================================
-- BLOG PAGE SEO
-- PAGE ID = 4
-- =========================================

INSERT INTO sma_cms_page_tag_mapping
(page_id, tag_id, property_name, value, is_dynamic)
VALUES
(4, 1, 'title',
'Healthcare Tips Blog',
TRUE),
(4, 2, 'meta_description',
'Read healthcare articles and wellness tips.',
TRUE),
(4, 21, 'article_schema',
'{
"@context":"https://schema.org",
"@type":"Article",
"headline":"Best Vitamins For Immunity"
}',
TRUE);

-- =========================================
-- CONTACT PAGE SEO
-- PAGE ID = 5
-- =========================================

INSERT INTO sma_cms_page_tag_mapping
(page_id, tag_id, property_name, value, is_dynamic)
VALUES
(5, 1, 'title',
'Contact Medicare Pharmacy',
TRUE),
(5, 2, 'meta_description',
'Contact our pharmacy support team for medicine assistance.',
TRUE),
(5, 24, 'ai_summary',
'Customer support page for Medicare Pharmacy UAE.',
TRUE);

-- =========================================
-- CHECK PAGE + SECTION DATA
-- =========================================

SELECT
    p.id,
    p.page_name,
    p.page_type,
    p.url,
    s.section_name,
    psm.sort_order
FROM sma_cms_pages p
LEFT JOIN sma_cms_page_section_mapping psm
    ON p.id = psm.page_id
LEFT JOIN sma_cms_sections_master s
    ON s.id = psm.section_id
ORDER BY p.id, psm.sort_order;

-- =========================================
-- CHECK SEO TAGS
-- =========================================

SELECT
    p.page_name,
    t.tag_name,
    ptm.property_name,
    ptm.value
FROM sma_cms_page_tag_mapping ptm
LEFT JOIN sma_cms_pages p
    ON p.id = ptm.page_id
LEFT JOIN sma_cms_tags_master t
    ON t.id = ptm.tag_id
ORDER BY p.id;


-- =============================================================================
-- Additional CMS tables (runtime sma_cms_* names)
-- =============================================================================

CREATE TABLE IF NOT EXISTS sma_cms_entities_master (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entity_code VARCHAR(50) NOT NULL,
    entity_name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_entity_code (entity_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO sma_cms_entities_master (entity_code, entity_name, is_active) VALUES
('product', 'Product', 1),
('category', 'Category', 1),
('blog', 'Blog', 1);

CREATE TABLE IF NOT EXISTS sma_cms_entity_tag_mapping (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entity_master_id BIGINT NOT NULL,
    entity_id BIGINT NOT NULL,
    tag_id BIGINT NOT NULL,
    property_name VARCHAR(100) NOT NULL,
    value TEXT NULL,
    is_dynamic TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_entity_tag_property (entity_master_id, entity_id, tag_id, property_name),
    KEY idx_entity (entity_master_id, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sma_cms_webshop_header_footer (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    section_type ENUM('header','footer') NOT NULL,
    field_key VARCHAR(100) NOT NULL,
    layoutname VARCHAR(120) NULL,
    label VARCHAR(255) NOT NULL,
    value TEXT NULL,
    icons VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_section_field (section_type, field_key),
    KEY idx_layoutname (layoutname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE sma_cms_pages
    ADD COLUMN IF NOT EXISTS parent_page_id INT NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS submenu_order INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS nav_order INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS show_in_header TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS show_in_footer TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS page_type VARCHAR(32) NOT NULL DEFAULT 'static';

ALTER TABLE sma_cms_tags_master
    ADD COLUMN IF NOT EXISTS show_on_page TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS show_on_entity TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE sma_settings
    ADD COLUMN IF NOT EXISTS active_cms_admin_panel TINYINT(1) NOT NULL DEFAULT 0;

