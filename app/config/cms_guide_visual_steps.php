<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Per-step screenshots + highlight boxes for CMS Admin user guide.
 * Coordinates calibrated for 1440×900 screenshots (sidebar = 260px).
 *
 * @param string $base CMS Admin base URL (site_url('cms_admin'))
 * @return array<string,array<string,mixed>>
 */
function cms_guide_visual_steps_map($base)
{
    $cms = rtrim((string) $base, '/');

    return array(
        'access' => array(
            'steps' => array(
                cms_guide_step(
                    'Open your website login page in the browser (ask your IT team for the link, or use <code>/login</code> on your site).',
                    'login.png',
                    cms_guide_hl(16, 37, 26, 42, 'Login form')
                ),
                cms_guide_step(
                    'Type your <strong>Username</strong> in the first box.',
                    'login.png',
                    cms_guide_hl(33, 39, 23, 3.8, 'Username')
                ),
                cms_guide_step(
                    'Type your <strong>Password</strong> in the second box.',
                    'login.png',
                    cms_guide_hl(39, 39, 23, 3.8, 'Password')
                ),
                cms_guide_step(
                    'Click the blue <strong>Log In</strong> button.',
                    'login.png',
                    cms_guide_hl(48, 41, 18, 4.2, 'Log In')
                ),
                cms_guide_step(
                    'After login, open <a href="' . $cms . '/dashboard">CMS Admin Dashboard</a> from the menu, or type <code>/cms_admin</code> in the address bar.',
                    'dashboard.png',
                    cms_guide_sidebar_hl('CMS sidebar')
                ),
            ),
        ),
        'dashboard' => array(
            'steps' => array(
                cms_guide_step(
                    'After login, click <strong>Dashboard</strong> in the left sidebar (grid icon) if you are not already there.',
                    'dashboard.png',
                    cms_guide_nav_hl(0, 'Dashboard')
                ),
                cms_guide_step(
                    'Read the welcome banner — it shows how many products are online.',
                    'dashboard.png',
                    cms_guide_main_hl(10.5, 12, 'Welcome banner')
                ),
                cms_guide_step(
                    'Look at the white stat cards (Products, CMS pages, Prices, etc.) for a quick count.',
                    'dashboard.png',
                    cms_guide_main_hl(23, 13, 'Stat cards')
                ),
                cms_guide_step(
                    'Scroll to <strong>Quick access</strong> and click any card to open that module.',
                    'dashboard.png',
                    cms_guide_main_hl(38, 48, 'Quick access')
                ),
                cms_guide_step(
                    'Click <strong>User guide</strong> anytime you need help (you are reading it now).',
                    'dashboard.png',
                    cms_guide_nav_hl(14, 'Guide')
                ),
            ),
        ),
        'catalog' => array(
            'steps' => array(
                cms_guide_step(
                    'Click <strong>Products</strong> in the left sidebar.',
                    'catalog.png',
                    cms_guide_nav_hl(1, 'Products')
                ),
                cms_guide_step(
                    'On the left, click a <strong>category name</strong> to see its products.',
                    'catalog.png',
                    cms_guide_main_hl(24, 68, 'Categories', 18.8, 40)
                ),
                cms_guide_step(
                    'Find the green check or <strong>eye icon</strong> on a row — click it to show or hide that category/product on the webshop.',
                    'catalog.png',
                    cms_guide_main_hl(28, 52, 'Active checkbox', 18.8, 6)
                ),
                cms_guide_step(
                    'Click a <strong>product name</strong> to see more details in a popup.',
                    'catalog.png',
                    cms_guide_main_hl(28, 55, 'Products panel', 59, 37)
                ),
                cms_guide_step(
                    'Changes save automatically when you toggle — no extra Save button needed.',
                    'catalog.png',
                    cms_guide_main_hl(28, 52, 'Auto-save toggle', 18.8, 6)
                ),
            ),
        ),
        'pages' => array(
            'workflows' => array(
                array(
                    'title' => 'A. Open the CMS Pages list',
                    'steps' => array(
                        cms_guide_step(
                            'Log in to ElintOM, then open <a href="' . $cms . '">CMS Admin</a> (or go to <code>/cms_admin</code>).',
                            'dashboard.png',
                            cms_guide_sidebar_hl('CMS Admin')
                        ),
                        cms_guide_step(
                            'In the <strong>left sidebar</strong>, under <strong>Storefront</strong>, click <strong>CMS Pages</strong>.',
                            'pages-list.png',
                            cms_guide_nav_hl(2, 'CMS Pages')
                        ),
                        cms_guide_step(
                            'You will see the pages table: page name, URL, menu type, header/footer visibility, and <strong>Edit</strong> / <strong>Delete</strong> actions.',
                            'pages-list.png',
                            cms_guide_main_hl(14, 78, 'Pages table')
                        ),
                        cms_guide_step(
                            'Use the <strong>Search</strong> box to find a page by name or URL.',
                            'pages-list.png',
                            cms_guide_search_hl('Search')
                        ),
                        cms_guide_step(
                            'Tick <strong>Show in header menu</strong> or <strong>Show in footer menu</strong> on a row to control navigation (page must be <strong>Published</strong>).',
                            'pages-list.png',
                            cms_guide_main_hl(22, 35, 'Header / Footer toggles', 46, 14)
                        ),
                    ),
                ),
                array(
                    'title' => 'B. Create a new page',
                    'steps' => array(
                        cms_guide_step(
                            'On the CMS Pages list, click the green <strong>+ Add New Page</strong> button (top right).',
                            'pages-list.png',
                            cms_guide_btn_hl(8.5, '+ Add New Page')
                        ),
                        cms_guide_step(
                            'Fill <strong>Page name</strong> (e.g. <em>About Us</em>) and <strong>URL path</strong> (e.g. <code>about-us</code> — storefront URL becomes <code>/cmspage/about-us</code>).',
                            'pages-add.png',
                            cms_guide_main_hl(21, 14, 'Page name & URL')
                        ),
                        cms_guide_step(
                            'Optional: choose a <strong>Parent page</strong> if this should appear as a dropdown sub-item under another menu link.',
                            'pages-add.png',
                            cms_guide_main_hl(36, 7, 'Parent page', 19, 42)
                        ),
                        cms_guide_step(
                            'Optional: upload a <strong>Page banner image</strong> (or pick from Media later).',
                            'pages-add.png',
                            cms_guide_main_hl(58, 12, 'Banner image', 19, 45)
                        ),
                        cms_guide_step(
                            'Click <strong>Create CMS Page</strong>. The page is saved as <strong>Draft</strong> and you are taken to the edit screen.',
                            'pages-add.png',
                            cms_guide_btn_hl(17, 'Create CMS Page', 74, 14)
                        ),
                    ),
                ),
                array(
                    'title' => 'C. Publish the page and edit page details',
                    'steps' => array(
                        cms_guide_step(
                            'On the edit screen, review <strong>Page details</strong> (name, URL, banner, parent menu).',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(9, 11, 'Page status')
                        ),
                        cms_guide_step(
                            'If the page is <strong>Published</strong> and you need to change name/URL/banner, click <strong>Unpublish</strong> first, edit, then click <strong>Publish</strong> again.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(10.5, 5.5, 'Publish / Unpublish', 72, 14)
                        ),
                        cms_guide_step(
                            'If the page is still <strong>Draft</strong>, click <strong>Publish</strong> when you are ready for it to appear on the webshop.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(10.5, 5.5, 'Publish', 72, 14)
                        ),
                        cms_guide_step(
                            'Optional: expand <strong>Page header &amp; footer design</strong> to assign a storefront header/footer layout profile to this page.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(21, 6, 'Header & footer design')
                        ),
                    ),
                ),
                array(
                    'title' => 'D. Add a dynamic section to the page',
                    'steps' => array(
                        cms_guide_step(
                            'On the page edit screen, find the panel <strong>Add dynamic section</strong> and click the row to expand it.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(28, 7, 'Add dynamic section')
                        ),
                        cms_guide_step(
                            'In the <strong>Section</strong> dropdown, pick a template — e.g. <strong>Related Products</strong>, <strong>Recent View Products</strong>, <strong>Blog Grid</strong>, <strong>Header</strong>, <strong>Footer</strong>, <strong>HTML block</strong>, <strong>FAQ</strong>.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(35, 8, 'Section dropdown', 21, 38)
                        ),
                        cms_guide_step(
                            'Optional: set <strong>Order</strong> (sort position) and <strong>Section title</strong> (heading shown on the storefront).',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(35, 8, 'Order & title', 58, 35)
                        ),
                        cms_guide_step(
                            'For product carousels: set title/limit in section JSON after adding (see step E).',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(52, 28, 'Current page sections')
                        ),
                        cms_guide_step(
                            'Click the green <strong>+ Add section</strong> button. The section appears under <strong>Current page sections</strong>.',
                            'pages-edit-sections.png',
                            cms_guide_btn_hl(28, '+ Add section', 74, 14)
                        ),
                    ),
                ),
                array(
                    'title' => 'E. Manage sections (reorder, enable, edit)',
                    'steps' => array(
                        cms_guide_step(
                            'Expand <strong>Current page sections</strong> — you will see all sections on this page.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(52, 7, 'Current page sections')
                        ),
                        cms_guide_step(
                            'Drag the <strong>☰ handle</strong> on a row to change order (saves automatically).',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(60, 5, 'Drag handle', 20, 5)
                        ),
                        cms_guide_step(
                            'Use the <strong>Active</strong> toggle on each row — only sections with Active <strong>ON</strong> render on the storefront.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(60, 8, 'Active toggle', 58, 8)
                        ),
                        cms_guide_step(
                            'Click <strong>Edit Section</strong> on a row to change sort order, title, or section content (e.g. carousel limit, HTML).',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(60, 6, 'Edit Section', 72, 6)
                        ),
                        cms_guide_step(
                            'Click <strong>Delete Section</strong> to remove a section from this page.',
                            'pages-edit-sections.png',
                            cms_guide_main_hl(60, 6, 'Delete Section', 84, 6)
                        ),
                    ),
                ),
                array(
                    'title' => 'F. Add SEO tags (title, description, canonical)',
                    'steps' => array(
                        cms_guide_step(
                            'Scroll down to <strong>Tag values by category</strong> and click the panel to expand it.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(52, 7, 'Tag values by category')
                        ),
                        cms_guide_step(
                            'Click the <strong>SEO</strong> tab (first tab may vary — look for tabs: AI, SEO, Technical, GEO, Social).',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(60, 5, 'SEO tab', 19, 48)
                        ),
                        cms_guide_step(
                            'Fill <strong>title</strong> — browser tab title for this page.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(66, 7, 'title', 19, 75)
                        ),
                        cms_guide_step(
                            'Fill <strong>meta description</strong> — short summary for search results.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(72, 9, 'meta description', 19, 75)
                        ),
                        cms_guide_step(
                            'Fill <strong>canonical</strong> — preferred URL if this page has duplicates.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(80, 7, 'canonical', 19, 75)
                        ),
                        cms_guide_step(
                            'Set <strong>robots</strong> if needed (e.g. <code>index, follow</code> or <code>noindex</code>).',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(86, 7, 'robots', 19, 40)
                        ),
                        cms_guide_step(
                            'Click the blue <strong>Save tag values</strong> button at the top of this panel.',
                            'pages-edit-tags.png',
                            cms_guide_btn_hl(52, 'Save tag values', 74, 14)
                        ),
                    ),
                ),
                array(
                    'title' => 'G. Add GEO tags (region, coordinates)',
                    'steps' => array(
                        cms_guide_step(
                            'In the same <strong>Tag values by category</strong> panel, click the <strong>GEO</strong> tab.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(60, 5, 'GEO tab', 38, 12)
                        ),
                        cms_guide_step(
                            'Fill <strong>geo.region</strong> — country/region code (e.g. <code>IN-MH</code>).',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(66, 7, 'geo.region', 19, 75)
                        ),
                        cms_guide_step(
                            'Fill <strong>geo.position</strong> — latitude;longitude (e.g. <code>19.0760;72.8777</code>).',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(72, 7, 'geo.position', 19, 75)
                        ),
                        cms_guide_step(
                            'Fill <strong>ICBM</strong> if used by your Tag Master setup.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(78, 7, 'ICBM', 19, 75)
                        ),
                        cms_guide_step(
                            'Fill <strong>hreflang</strong> for multi-language sites if applicable.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(84, 7, 'hreflang', 19, 75)
                        ),
                        cms_guide_step(
                            'Click <strong>Save tag values</strong>.',
                            'pages-edit-tags.png',
                            cms_guide_btn_hl(52, 'Save tag values', 74, 14)
                        ),
                    ),
                ),
                array(
                    'title' => 'H. Add Social / Open Graph tags',
                    'steps' => array(
                        cms_guide_step(
                            'In <strong>Tag values by category</strong>, click the <strong>Social</strong> tab.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(60, 5, 'Social tab', 52, 14)
                        ),
                        cms_guide_step(
                            'Fill <strong>og:title</strong>, <strong>og:description</strong>, <strong>og:image</strong> (full image URL from Media library), and <strong>og:url</strong>.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(66, 28, 'Open Graph fields', 19, 75)
                        ),
                        cms_guide_step(
                            'Set <strong>og:type</strong> (usually <code>website</code> for static pages).',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(86, 7, 'og:type', 19, 40)
                        ),
                        cms_guide_step(
                            'Click <strong>Save tag values</strong>.',
                            'pages-edit-tags.png',
                            cms_guide_btn_hl(52, 'Save tag values', 74, 14)
                        ),
                        cms_guide_step(
                            'Preview the live page and use browser dev tools → Elements → <code>&lt;head&gt;</code> to confirm meta tags.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(60, 35, 'Saved tag fields', 19, 75)
                        ),
                    ),
                ),
                array(
                    'title' => 'I. Quick import — paste existing &lt;head&gt; HTML (optional)',
                    'steps' => array(
                        cms_guide_step(
                            'Inside <strong>Tag values by category</strong>, scroll to <strong>Import from head script</strong>.',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(78, 8, 'Import from head script', 19, 75)
                        ),
                        cms_guide_step(
                            'Paste your full <code>&lt;head&gt;</code> block (title, meta, canonical, Open Graph, geo, JSON-LD).',
                            'pages-edit-tags.png',
                            cms_guide_main_hl(84, 10, 'Paste area', 19, 75)
                        ),
                        cms_guide_step(
                            'Click import — values are mapped into the tag fields above (missing tags can be auto-created).',
                            'pages-edit-tags.png',
                            cms_guide_btn_hl(78, 'Import button', 74, 14)
                        ),
                        cms_guide_step(
                            'Review each tab (SEO, GEO, Social) and click <strong>Save tag values</strong>.',
                            'pages-edit-tags.png',
                            cms_guide_btn_hl(52, 'Save tag values', 74, 14)
                        ),
                    ),
                ),
            ),
        ),
        'blogs' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Blog Posts</strong> in the left sidebar.', 'blogs.png', cms_guide_nav_hl(3, 'Blog Posts')),
                cms_guide_step('Optional: open <strong>Blog Categories</strong>, type a category name (e.g. Wellness), and click <strong>Save category</strong>.', 'blogs.png', cms_guide_main_hl(18, 16, 'Blog Categories')),
                cms_guide_step('Click the green <strong>+ Add Blog Post</strong> button.', 'blogs.png', cms_guide_btn_hl(9, '+ Add Blog Post')),
                cms_guide_step('Fill in <strong>title</strong>, <strong>short description</strong>, and <strong>full article text</strong>.', 'blogs.png', cms_guide_main_hl(36, 42, 'Article list / form')),
                cms_guide_step('Upload an <strong>image</strong> or pick one from the Media library.', 'blogs.png', cms_guide_main_hl(36, 42, 'Blog posts table')),
                cms_guide_step('Set status to <strong>Published</strong> and turn <strong>Active</strong> ON.', 'blogs.png', cms_guide_main_hl(36, 42, 'Status columns')),
                cms_guide_step('Click <strong>Save</strong>. The article is now ready to show in a Blog Grid section on a CMS page.', 'blogs.png', cms_guide_main_hl(36, 42, 'Actions column')),
            ),
        ),
        'testimonials' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Testimonials</strong> in the left sidebar.', 'testimonials.png', cms_guide_nav_hl(4, 'Testimonials')),
                cms_guide_step('Click <strong>+ Add Testimonial</strong>.', 'testimonials.png', cms_guide_btn_hl(9, '+ Add Testimonial')),
                cms_guide_step('Enter the person’s <strong>name</strong>, their <strong>quote</strong>, and upload a <strong>photo</strong> if you have one.', 'testimonials.png', cms_guide_main_hl(16, 70, 'Testimonial form')),
                cms_guide_step('Set status to <strong>Published</strong> and click <strong>Save</strong>.', 'testimonials.png', cms_guide_main_hl(16, 70, 'Published & Save')),
                cms_guide_step('On a CMS Page, add a <strong>Testimonials Grid</strong> section to show them on the website.', 'pages-edit-sections.png', cms_guide_main_hl(28, 7, 'Add dynamic section')),
            ),
        ),
        'layout' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Header & Footer</strong> in the left sidebar.', 'layout-builder.png', cms_guide_nav_hl(5, 'Header & Footer')),
                cms_guide_step('Click the <strong>Header</strong> tab to edit the top bar, or <strong>Footer</strong> for the bottom.', 'layout-builder.png', cms_guide_main_hl(10, 6, 'Header / Footer tabs', 19, 28)),
                cms_guide_step('Choose a layout from the dropdown (start with <strong>Default</strong> if unsure).', 'layout-builder.png', cms_guide_main_hl(18, 7, 'Layout dropdown', 19, 35)),
                cms_guide_step('Use the builder to set your <strong>logo</strong>, menu links, and colors.', 'layout-builder.png', cms_guide_main_hl(26, 58, 'Layout builder')),
                cms_guide_step('Click <strong>Save</strong> when finished.', 'layout-builder.png', cms_guide_btn_hl(82, 'Save', 19, 12)),
                cms_guide_step('Header menu links come from CMS Pages where <strong>Show in header menu</strong> is turned on.', 'pages-list.png', cms_guide_main_hl(22, 35, 'Show in header menu', 46, 14)),
            ),
        ),
        'media' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Media</strong> in the left sidebar.', 'media.png', cms_guide_nav_hl(6, 'Media')),
                cms_guide_step('Click the green <strong>Upload</strong> button and choose image files from your computer.', 'media.png', cms_guide_btn_hl(12, 'Upload', 78, 10)),
                cms_guide_step('Use the folder links (Logos, Page banners, Hero images, etc.) to filter files.', 'media.png', cms_guide_main_hl(18, 55, 'Folders', 18.8, 16)),
                cms_guide_step('Click an image to copy its link or use the picker when editing a page/blog.', 'media.png', cms_guide_main_hl(18, 70, 'Image grid', 35, 62)),
                cms_guide_step('To remove an unused file, click the trash icon and confirm.', 'media.png', cms_guide_main_hl(22, 8, 'Delete icon', 78, 55)),
            ),
        ),
        'entity' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Entity Pages</strong> in the left sidebar.', 'entity-tags.png', cms_guide_nav_hl(7, 'Entity Pages')),
                cms_guide_step('Click <strong>+ Add Mapping</strong>.', 'entity-tags.png', cms_guide_btn_hl(9, '+ Add Mapping')),
                cms_guide_step('Choose type: <strong>Product</strong>, <strong>Category</strong>, or <strong>Blog</strong>.', 'entity-tags.png', cms_guide_main_hl(16, 10, 'Entity type', 19, 32)),
                cms_guide_step('Search and select the item, then fill in the SEO fields shown.', 'entity-tags.png', cms_guide_main_hl(24, 55, 'SEO fields')),
                cms_guide_step('Click <strong>Save</strong>. These values apply when that item’s page is viewed on the website.', 'entity-tags.png', cms_guide_btn_hl(78, 'Save', 19, 12)),
            ),
        ),
        'tags' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Tag Master</strong> in the left sidebar.', 'tags-master.png', cms_guide_nav_hl(8, 'Tag Master')),
                cms_guide_step('Browse the list of tag names (title, meta description, geo.region, og:image, etc.).', 'tags-master.png', cms_guide_main_hl(14, 72, 'Tag list')),
                cms_guide_step('Click <strong>Edit details</strong> on a row to change where it appears.', 'tags-master.png', cms_guide_main_hl(30, 6, 'Edit details', 78, 6)),
                cms_guide_step('Turn on <strong>Show on CMS Pages edit</strong> or <strong>Show on Entity Tag Mapping</strong> as needed.', 'tags-master.png', cms_guide_main_hl(22, 35, 'Show on forms', 52, 38)),
                cms_guide_step('Save. The fields will appear on the matching edit screens.', 'tags-master.png', cms_guide_main_hl(30, 6, 'Save', 78, 6)),
            ),
        ),
        'site' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Site Settings</strong> in the sidebar to expand the submenu.', 'site-settings.png', cms_guide_nav_hl(9, 'Site Settings')),
                cms_guide_step('Click <strong>Robots</strong> to review which pages search engines may crawl.', 'site-settings.png', cms_guide_hl(53.5, 0.3, 17.5, 4.2, 'Robots')),
                cms_guide_step('Click <strong>Sitemap.xml</strong> to preview your sitemap URL list.', 'site-settings.png', cms_guide_hl(57.7, 0.3, 17.5, 4.2, 'Sitemap.xml')),
                cms_guide_step('Click <strong>LLMS</strong> to edit llms.txt content for AI tools, then Save.', 'site-settings.png', cms_guide_hl(61.9, 0.3, 17.5, 4.2, 'LLMS')),
            ),
        ),
        'prices' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Prices</strong> in the left sidebar.', 'prices.png', cms_guide_nav_hl(10, 'Prices')),
                cms_guide_step('Click a <strong>category</strong> name to load its products in the grid.', 'prices.png', cms_guide_main_hl(18, 55, 'Categories', 18.8, 22)),
                cms_guide_step('Type new values in the <strong>E-shop price</strong> and <strong>MRP</strong> columns.', 'prices.png', cms_guide_main_hl(22, 50, 'Price columns', 48, 38)),
                cms_guide_step('Click <strong>Save Price Updates</strong> at the bottom when you are done.', 'prices.png', cms_guide_main_hl(88, 7, 'Save Price Updates', 19, 22)),
            ),
        ),
        'forms' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Form Templates</strong> in the left sidebar.', 'form-templates.png', cms_guide_nav_hl(11, 'Form Templates')),
                cms_guide_step('Click <strong>+ New Form Template</strong>.', 'form-templates.png', cms_guide_btn_hl(9, '+ New Form Template')),
                cms_guide_step('Enter a <strong>template name</strong> and <strong>form key</strong> (a short code name).', 'form-templates.png', cms_guide_main_hl(16, 12, 'Name & key', 19, 50)),
                cms_guide_step('Add fields: text, email, phone, message box, etc.', 'form-templates.png', cms_guide_main_hl(28, 55, 'Form fields')),
                cms_guide_step('Click <strong>Save</strong>.', 'form-templates.png', cms_guide_btn_hl(78, 'Save', 19, 12)),
                cms_guide_step('On a CMS Page, add a <strong>Contact form</strong> section and select this template.', 'pages-edit-sections.png', cms_guide_main_hl(28, 7, 'Add dynamic section')),
            ),
        ),
        'leads' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Leads</strong> in the left sidebar.', 'leads.png', cms_guide_nav_hl(12, 'Leads')),
                cms_guide_step('Browse the table — each row is one enquiry.', 'leads.png', cms_guide_main_hl(14, 72, 'Leads table')),
                cms_guide_step('Click a name to open details, or use the green <strong>Actions</strong> button.', 'leads.png', cms_guide_main_hl(30, 6, 'Actions', 78, 6)),
                cms_guide_step('Change <strong>Status</strong> (New, Follow up, Converted, etc.) from the dropdown.', 'leads.png', cms_guide_main_hl(30, 6, 'Status', 55, 10)),
                cms_guide_step('Use Actions to edit, convert to customer, or view history.', 'leads.png', cms_guide_main_hl(30, 6, 'Actions menu', 78, 6)),
            ),
        ),
        'newsletter' => array(
            'steps' => array(
                cms_guide_step('Click <strong>Newsletter</strong> in the left sidebar.', 'newsletter.png', cms_guide_nav_hl(13, 'Newsletter')),
                cms_guide_step('Review the list of subscriber emails and signup dates.', 'newsletter.png', cms_guide_main_hl(14, 72, 'Subscriber list')),
                cms_guide_step('Use the search box to find a specific email.', 'newsletter.png', cms_guide_search_hl('Search')),
                cms_guide_step('Export or copy emails for your email marketing tool (outside CMS Admin).', 'newsletter.png', cms_guide_main_hl(14, 72, 'Export / copy')),
            ),
        ),
    );
}
