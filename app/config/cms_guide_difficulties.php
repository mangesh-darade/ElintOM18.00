<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extra steps from new-user testing — common difficulties and fixes.
 *
 * @param string $base
 * @return array<string,array<string,mixed>>
 */
function cms_guide_difficulty_steps_map($base)
{
    $cms = rtrim((string) $base, '/');

    return array(
        'dashboard' => array(
            'prepend_steps' => array(
                cms_guide_journey_step(
                    '<strong>Left dark sidebar</strong> = main menu. Always use it to switch modules (Products, CMS Pages, Media, etc.).',
                    'dashboard.png',
                    cms_guide_sidebar_hl('Sidebar menu')
                ),
                cms_guide_journey_step(
                    '<strong>Top white bar</strong> = breadcrumbs (where you are) and your user menu. <strong>Search menu…</strong> finds sidebar links — it does not search products or pages.',
                    'dashboard.png',
                    cms_guide_main_hl(0, 7, 'Top bar', 18.8, 79.5)
                ),
                cms_guide_journey_step(
                    '<strong>Main white area</strong> = where you work. Each module opens different forms and tables here.',
                    'dashboard.png',
                    cms_guide_main_hl(10, 75, 'Main work area')
                ),
                cms_guide_difficulty_step(
                    'You clicked <strong>ElintOM</strong> in the sidebar and left CMS Admin.',
                    'Click <strong>Guide</strong> in the sidebar, or type <code>/cms_admin</code> in the browser address bar to return.',
                    'dashboard.png',
                    cms_guide_nav_hl(14, 'Guide')
                ),
                cms_guide_journey_step(
                    '<strong>Setup order:</strong> Products → Prices → Media → Header &amp; Footer → CMS Pages → check live site.',
                    'dashboard.png',
                    cms_guide_main_hl(38, 48, 'Quick access')
                ),
            ),
            'steps' => array(
                cms_guide_difficulty_step(
                    'You cannot find <strong>Robots</strong> or <strong>Sitemap</strong> in the sidebar.',
                    'Click <strong>Site Settings</strong> (with the small arrow) to expand the submenu — Robots, Sitemap.xml, and LLMS are inside.',
                    'site-settings.png',
                    cms_guide_nav_hl(9, 'Site Settings')
                ),
                cms_guide_difficulty_step(
                    'Quick access card says <strong>Entity tags</strong> but the sidebar says <strong>Entity Pages</strong>.',
                    'They are the same — use <strong>Entity Pages</strong> for SEO on individual products and categories.',
                    'dashboard.png',
                    cms_guide_main_hl(38, 22, 'Entity tags card', 48, 22)
                ),
            ),
            'tips' => array(
                'Dashboard card says <strong>Entity tags</strong> but the sidebar link is <strong>Entity Pages</strong> — same module.',
                '<strong>Site Settings</strong> (Robots, Sitemap) is hidden until you click the arrow next to it in the sidebar.',
            ),
        ),
        'access' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'After login you see the <strong>POS / sales screen</strong>, not CMS Admin.',
                    'Type <code>/cms_admin</code> at the end of your site URL (e.g. <code>http://yoursite.com/cms_admin</code>). Bookmark this page.',
                    'dashboard.png',
                    cms_guide_sidebar_hl('CMS Admin URL')
                ),
            ),
        ),
        'catalog' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'Right panel says <em>“Select a category from the left panel to load products”</em> and looks empty.',
                    'Click a <strong>category name</strong> in the left table, or click the <strong>list icon</strong> in the Manage column (View products).',
                    'catalog.png',
                    cms_guide_main_hl(28, 55, 'View products', 59, 37)
                ),
                cms_guide_difficulty_step(
                    'Product is ON in ERP but <strong>not visible on the website</strong>.',
                    'Check the <strong>Active</strong> checkbox for the category AND each product row. Then check <strong>Prices</strong> has a webshop price set.',
                    'catalog.png',
                    cms_guide_main_hl(28, 52, 'Active checkbox', 18.8, 6)
                ),
                cms_guide_difficulty_step(
                    'Page title says <em>Manage Products For E-Shop</em> but the menu says <strong>Products</strong>.',
                    'Same screen — sidebar <strong>Products</strong> is the correct menu name.',
                    'catalog.png',
                    cms_guide_nav_hl(1, 'Products')
                ),
            ),
        ),
        'pages' => array(
            'workflows' => array(
                array(
                    'title' => 'J. Troubleshooting — when something does not show',
                    'steps' => array(
                        cms_guide_difficulty_step(
                            'New page does <strong>not appear in the website menu</strong>.',
                            'On CMS Pages list: turn <strong>Show In Header</strong> ON <em>and</em> make sure page <strong>Status</strong> is <strong>Published</strong> (green). Header links also need <strong>Header &amp; Footer</strong> configured.',
                            'pages-list.png',
                            cms_guide_main_hl(22, 35, 'Header / Footer toggles', 46, 14)
                        ),
                        cms_guide_difficulty_step(
                            'You <strong>cannot change page name or URL</strong> on the edit screen.',
                            'The page is <strong>Published</strong>. Click orange <strong>Unpublish</strong> first, edit fields, then click green <strong>Publish</strong> again.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(10.5, 5.5, 'Unpublish', 72, 14)
                        ),
                        cms_guide_difficulty_step(
                            'You added a section but <strong>nothing shows on the website</strong>.',
                            'Expand <strong>Current page sections</strong> → turn <strong>Active</strong> ON for that row → confirm page is <strong>Published</strong>.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(60, 8, 'Active toggle', 58, 8)
                        ),
                        cms_guide_difficulty_step(
                            'You cannot find <strong>Add dynamic section</strong> or <strong>Tag values</strong>.',
                            'These are <strong>collapsed panels</strong> on the edit page — click the row title (with the ▶ chevron) to expand each panel.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(28, 7, 'Click to expand')
                        ),
                        cms_guide_difficulty_step(
                            '<strong>SEO / GEO fields are missing</strong> under Tag values.',
                            'Ask your administrator to open <a href="' . $cms . '/tags_master">Tag Master</a> and turn on <strong>Show on CMS Pages edit</strong> for each tag.',
                            'tags-master.png',
                            cms_guide_nav_hl(8, 'Tag Master')
                        ),
                        cms_guide_difficulty_step(
                            'You edited <strong>Home</strong> page but the storefront homepage looks unchanged.',
                            'Find the <strong>Home</strong> row in CMS Pages → click <strong>Edit</strong>. Homepage content is built from <strong>sections</strong> on that page, not a separate menu.',
                            'pages-list.png',
                            cms_guide_main_hl(18, 8, 'Home page row', 19, 75)
                        ),
                    ),
                ),
            ),
        ),
        'blogs' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'Blog post is <strong>Published</strong> but <strong>not visible on the website</strong>.',
                    'Blogs do not appear automatically. Edit a CMS Page → <strong>Add dynamic section</strong> → choose <strong>Blog Grid</strong> → Publish the page.',
                    'pages-edit-sections.png',
                    cms_guide_main_hl(46, 8, 'Blog Grid section', 21, 38)
                ),
                cms_guide_difficulty_step(
                    'Table shows <em>No records found</em>.',
                    'Click green <strong>+ Add Blog Post</strong> at the top right first — the list is empty until you create posts.',
                    'blogs.png',
                    cms_guide_btn_hl(9, '+ Add Blog Post')
                ),
            ),
        ),
        'testimonials' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'Testimonial saved but <strong>not on the website</strong>.',
                    'Add a <strong>Testimonials Grid</strong> section on a CMS Page (same as Blog Grid). Status must be <strong>Published</strong> and <strong>Active</strong>.',
                    'pages-edit-sections.png',
                    cms_guide_main_hl(46, 8, 'Section dropdown', 21, 38)
                ),
            ),
        ),
        'layout' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'You changed the header but the <strong>menu links are wrong or missing</strong>.',
                    'Menu links come from <strong>CMS Pages</strong> — turn <strong>Show In Header</strong> ON for each page that should appear. Header &amp; Footer only controls design.',
                    'pages-list.png',
                    cms_guide_main_hl(22, 35, 'Show In Header', 46, 14)
                ),
                cms_guide_difficulty_step(
                    'Layout builder screen looks blank or keeps loading.',
                    'Wait a few seconds for the builder to load. If still blank, refresh the page. Start with the <strong>Header</strong> tab, choose <strong>Default</strong> layout, then click <strong>Save</strong>.',
                    'layout-builder.png',
                    cms_guide_main_hl(26, 58, 'Layout builder')
                ),
            ),
        ),
        'media' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'You uploaded an image but <strong>cannot paste it into SEO og:image</strong>.',
                            'In Media, click the image → <strong>Copy path</strong> → paste the full URL into the <strong>og:image</strong> field on CMS Pages → Tag values → Social tab.',
                    'media.png',
                    cms_guide_main_hl(22, 70, 'Copy path', 35, 25)
                ),
                cms_guide_difficulty_step(
                    'You do not know which <strong>folder</strong> to use.',
                    'Use <strong>Logos</strong> for header logo, <strong>Page banners</strong> for CMS page tops, <strong>Hero images</strong> for large banners. <strong>All media</strong> shows everything.',
                    'media.png',
                    cms_guide_main_hl(18, 55, 'Folders', 18.8, 16)
                ),
            ),
        ),
        'entity' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'You are not sure whether to use <strong>Entity Pages</strong> or <strong>CMS Pages</strong> for SEO.',
                    '<strong>CMS Pages</strong> = general website pages (About, Home). <strong>Entity Pages</strong> = SEO for one specific <strong>product</strong>, <strong>category</strong>, or <strong>blog post</strong>.',
                    'entity-tags.png',
                    cms_guide_main_hl(16, 70, 'Entity Pages')
                ),
            ),
        ),
        'tags' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'You opened Tag Master and it looks too technical.',
                    'Normal content editors rarely need this. Only administrators use it to choose which SEO fields appear on CMS Pages and Entity Pages forms.',
                    'tags-master.png',
                    cms_guide_main_hl(14, 72, 'Tag list')
                ),
            ),
        ),
        'prices' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'Price is set here but product still shows <strong>wrong price or zero</strong> on website.',
                    'Also check <strong>Products</strong> — category and product must be <strong>Active</strong> for webshop. Clear browser cache and refresh the storefront.',
                    'catalog.png',
                    cms_guide_nav_hl(1, 'Products')
                ),
                cms_guide_difficulty_step(
                    'Right side is <strong>empty</strong> — no products to edit.',
                    'Click a <strong>category name</strong> in the left list first — prices load per category.',
                    'prices.png',
                    cms_guide_main_hl(18, 55, 'Categories', 18.8, 22)
                ),
            ),
        ),
        'forms' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'Contact form created but <strong>submissions not appearing</strong>.',
                    'You must also add a <strong>Contact form</strong> section on a CMS Page and select this template. Submissions then appear under <strong>Leads</strong>.',
                    'pages-edit-sections.png',
                    cms_guide_main_hl(46, 8, 'Contact form section', 21, 38)
                ),
            ),
            'tips' => array(
                'Full path: <strong>Form Templates</strong> → create form → <strong>CMS Pages</strong> → add section → <strong>Leads</strong> to read replies.',
            ),
        ),
        'leads' => array(
            'steps' => array(
                cms_guide_difficulty_step(
                    'Leads list is <strong>empty</strong> after someone filled your contact form.',
                    'Confirm the CMS page with the contact form is <strong>Published</strong>, the form template is linked in the section, and test again. Check spam folder if email notifications are used.',
                    'form-templates.png',
                    cms_guide_nav_hl(11, 'Form Templates')
                ),
            ),
        ),
    );
}
