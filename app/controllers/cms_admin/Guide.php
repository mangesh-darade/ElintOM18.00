<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * In-panel user guide for every CMS Admin menu (linked from dashboard).
 */
class Guide extends Cms_admin_base
{
    public function index()
    {
        $meta = array('page_title' => 'User Guide');
        $this->data['cms_enhancements'] = true;
        $this->data['guide_sections'] = $this->guide_sections();
        $this->cms_page_construct('cms_admin/guide/index', $meta, $this->data);
    }

    /**
     * Single module guide (card click → steps + screenshots).
     *
     * @param string|null $id
     */
    public function module($id = null)
    {
        $id = trim((string) $id);
        $section = $this->find_guide_section($id);
        if ($section === null) {
            show_404();
        }

        $title = !empty($section['card_title']) ? $section['card_title'] : $section['title'];
        $meta = array('page_title' => $title . ' — Guide');
        $this->data['cms_enhancements'] = true;
        $sections = $this->guide_sections();
        $this->load->helper('cms_guide');
        $this->data['guide_section'] = $section;
        $this->data['guide_sections'] = $sections;
        $this->data['guide_nav'] = cms_guide_module_nav($sections, $id);
        $this->cms_page_construct('cms_admin/guide/module', $meta, $this->data);
    }

    /**
     * @param string $id
     * @return array<string,mixed>|null
     */
    protected function find_guide_section($id)
    {
        foreach ($this->guide_sections() as $section) {
            if (isset($section['id']) && (string) $section['id'] === $id) {
                return $section;
            }
        }
        return null;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    protected function guide_sections()
    {
        $base = site_url('cms_admin');
        $sections = array(
            array(
                'id'          => 'access',
                'icon'        => 'fa-sign-in',
                'card_title'  => 'Login & access',
                'title'       => '0. Access & login',
                'summary'     => 'How to sign in and open CMS Admin for the first time.',
                'intro'       => 'Start here if you are new. You use the same username and password as your main ElintOM account.',
                'url'         => site_url('login'),
                'purpose'     => 'Opens the CMS Admin panel where you manage your webshop content, products, and pages.',
                'image'       => 'login.png',
                'steps'       => array(
                    'Open your website login page in the browser (ask your IT team for the link, or use <code>/login</code> on your site).',
                    'Type your <strong>Username</strong> in the first box.',
                    'Type your <strong>Password</strong> in the second box.',
                    'Click the blue <strong>Log In</strong> button.',
                    'After login, open <a href="' . $base . '/dashboard">CMS Admin Dashboard</a> from the menu, or type <code>/cms_admin</code> in the address bar.',
                ),
                'tips'        => array('If you see a Welcome page instead of CMS Admin, ask an administrator to turn on <strong>Active Webshop</strong> in settings.'),
            ),
            array(
                'id'          => 'dashboard',
                'icon'        => 'fa-th-large',
                'card_title'  => 'Dashboard',
                'title'       => '1. Dashboard',
                'summary'     => 'Home screen with shortcuts to every module.',
                'intro'       => 'The Dashboard is your starting point. You do not edit content here — you pick which module to open next.',
                'url'         => $base . '/dashboard',
                'purpose'     => 'Shows a quick overview (how many products, pages, etc.) and buttons to jump to each area.',
                'image'       => 'dashboard.png',
                'steps'       => array(
                    'After login, click <strong>Dashboard</strong> in the left sidebar (grid icon) if you are not already there.',
                    'Read the welcome banner — it shows how many products are online.',
                    'Look at the white stat cards (Products, CMS pages, Prices, etc.) for a quick count.',
                    'Scroll to <strong>Quick access</strong> and click any card to open that module.',
                    'Click <strong>User guide</strong> anytime you need help (you are reading it now).',
                ),
            ),
            array(
                'id'          => 'catalog',
                'icon'        => 'fa-cubes',
                'card_title'  => 'Products',
                'title'       => '2. Products (Catalog)',
                'summary'     => 'Choose which products and categories appear on your webshop.',
                'intro'       => 'Use this when you want to show or hide products on the website. Customers only see products you turn on here.',
                'url'         => $base . '/catalog',
                'purpose'     => 'Controls which items from your inventory are visible on the online store.',
                'image'       => 'catalog.png',
                'steps'       => array(
                    'Click <strong>Products</strong> in the left sidebar.',
                    'On the left, click a <strong>category name</strong> to see its products.',
                    'Find the green check or <strong>eye icon</strong> on a row — click it to show or hide that category/product on the webshop.',
                    'Click a <strong>product name</strong> to see more details in a popup.',
                    'Changes save automatically when you toggle — no extra Save button needed.',
                ),
                'tips'        => array('If a product is hidden here, it will not appear in product lists or “Related Products” on the website.'),
            ),
            array(
                'id'          => 'pages',
                'icon'        => 'fa-file-text-o',
                'card_title'  => 'CMS Pages',
                'title'       => '3. CMS Pages',
                'summary'     => 'Create website pages, add sections, and set SEO / GEO tags.',
                'intro'       => 'This is the main place to build your website pages (About Us, Products page, Privacy Policy, etc.). Follow sections A–I below in order the first time.',
                'url'         => $base . '/pages',
                'purpose'     => 'Create and edit storefront pages, add content blocks (product carousels, blogs, FAQs), and fill in search-engine and location meta tags.',
                'image'       => 'pages-list.png',
                'workflows'   => array(
                    array(
                        'title' => 'A. Open the CMS Pages list',
                        'steps' => array(
                            'Log in to ElintOM, then open <a href="' . $base . '">CMS Admin</a> (or go to <code>/cms_admin</code>).',
                            'In the <strong>left sidebar</strong>, under <strong>Storefront</strong>, click <strong>CMS Pages</strong>.',
                            'You will see the pages table: page name, URL, menu type, header/footer visibility, and <strong>Edit</strong> / <strong>Delete</strong> actions.',
                            'Use the <strong>Search</strong> box to find a page by name or URL.',
                            'Tick <strong>Show in header menu</strong> or <strong>Show in footer menu</strong> on a row to control navigation (page must be <strong>Published</strong>).',
                        ),
                    ),
                    array(
                        'title' => 'B. Create a new page',
                        'steps' => array(
                            'On the CMS Pages list, click the green <strong>+ Add New Page</strong> button (top right).',
                            'Fill <strong>Page name</strong> (e.g. <em>About Us</em>) and <strong>URL path</strong> (e.g. <code>about-us</code> — storefront URL becomes <code>/cmspage/about-us</code>).',
                            'Optional: choose a <strong>Parent page</strong> if this should appear as a dropdown sub-item under another menu link.',
                            'Optional: upload a <strong>Page banner image</strong> (or pick from Media later).',
                            'Click <strong>Create CMS Page</strong>. The page is saved as <strong>Draft</strong> and you are taken to the edit screen.',
                        ),
                    ),
                    array(
                        'title' => 'C. Publish the page and edit page details',
                        'steps' => array(
                            'On the edit screen, review <strong>Page details</strong> (name, URL, banner, parent menu).',
                            'If the page is <strong>Published</strong> and you need to change name/URL/banner, click <strong>Unpublish</strong> first, edit, then click <strong>Publish</strong> again.',
                            'If the page is still <strong>Draft</strong>, click <strong>Publish</strong> when you are ready for it to appear on the webshop.',
                            'Optional: expand <strong>Page header &amp; footer design</strong> to assign a storefront header/footer layout profile to this page.',
                        ),
                    ),
                    array(
                        'title' => 'D. Add a dynamic section to the page',
                        'steps' => array(
                            'On the page edit screen, find the panel <strong>Add dynamic section</strong> and click the row to expand it.',
                            'In the <strong>Section</strong> dropdown, pick a template — e.g. <strong>Related Products</strong>, <strong>Recent View Products</strong>, <strong>Blog Grid</strong>, <strong>Header</strong>, <strong>Footer</strong>, <strong>HTML block</strong>, <strong>FAQ</strong>.',
                            'Optional: set <strong>Order</strong> (sort position) and <strong>Section title</strong> (heading shown on the storefront).',
                            'For product carousels: set title/limit in section JSON after adding (see step E).',
                            'Click the green <strong>+ Add section</strong> button. The section appears under <strong>Current page sections</strong>.',
                        ),
                    ),
                    array(
                        'title' => 'E. Manage sections (reorder, enable, edit)',
                        'steps' => array(
                            'Expand <strong>Current page sections</strong> — you will see all sections on this page.',
                            'Drag the <strong>☰ handle</strong> on a row to change order (saves automatically).',
                            'Use the <strong>Active</strong> toggle on each row — only sections with Active <strong>ON</strong> render on the storefront.',
                            'Click <strong>Edit Section</strong> on a row to change sort order, title, or section content (e.g. carousel limit, HTML).',
                            'Click <strong>Delete Section</strong> to remove a section from this page.',
                        ),
                    ),
                    array(
                        'title' => 'F. Add SEO tags (title, description, canonical)',
                        'steps' => array(
                            'Scroll down to <strong>Tag values by category</strong> and click the panel to expand it.',
                            'Click the <strong>SEO</strong> tab (first tab may vary — look for tabs: AI, SEO, Technical, GEO, Social).',
                            'Fill <strong>title</strong> — browser tab title for this page.',
                            'Fill <strong>meta description</strong> — short summary for search results.',
                            'Fill <strong>canonical</strong> — preferred URL if this page has duplicates.',
                            'Set <strong>robots</strong> if needed (e.g. <code>index, follow</code> or <code>noindex</code>).',
                            'Click the blue <strong>Save tag values</strong> button at the top of this panel.',
                        ),
                    ),
                    array(
                        'title' => 'G. Add GEO tags (region, coordinates)',
                        'steps' => array(
                            'In the same <strong>Tag values by category</strong> panel, click the <strong>GEO</strong> tab.',
                            'Fill <strong>geo.region</strong> — country/region code (e.g. <code>IN-MH</code>).',
                            'Fill <strong>geo.position</strong> — latitude;longitude (e.g. <code>19.0760;72.8777</code>).',
                            'Fill <strong>ICBM</strong> if used by your Tag Master setup.',
                            'Fill <strong>hreflang</strong> for multi-language sites if applicable.',
                            'Click <strong>Save tag values</strong>.',
                        ),
                    ),
                    array(
                        'title' => 'H. Add Social / Open Graph tags',
                        'steps' => array(
                            'In <strong>Tag values by category</strong>, click the <strong>Social</strong> tab.',
                            'Fill <strong>og:title</strong>, <strong>og:description</strong>, <strong>og:image</strong> (full image URL from Media library), and <strong>og:url</strong>.',
                            'Set <strong>og:type</strong> (usually <code>website</code> for static pages).',
                            'Click <strong>Save tag values</strong>.',
                            'Preview the live page and use browser dev tools → Elements → <code>&lt;head&gt;</code> to confirm meta tags.',
                        ),
                    ),
                    array(
                        'title' => 'I. Quick import — paste existing &lt;head&gt; HTML (optional)',
                        'steps' => array(
                            'Inside <strong>Tag values by category</strong>, scroll to <strong>Import from head script</strong>.',
                            'Paste your full <code>&lt;head&gt;</code> block (title, meta, canonical, Open Graph, geo, JSON-LD).',
                            'Click import — values are mapped into the tag fields above (missing tags can be auto-created).',
                            'Review each tab (SEO, GEO, Social) and click <strong>Save tag values</strong>.',
                        ),
                    ),
                ),
                'tips'        => array(
                    'Public URL: <code>/cmspage/{url}</code> or a custom route (e.g. Products page uses <code>/product</code>).',
                    'Which fields appear in Tag values is controlled in <a href="' . $base . '/tags_master">Tag Master</a> → <strong>Show on CMS Pages edit</strong>.',
                    'Per-product SEO lives under <a href="' . $base . '/entity_tags">Entity Pages</a>, not on CMS Pages.',
                ),
            ),
            array(
                'id'          => 'blogs',
                'icon'        => 'fa-newspaper-o',
                'card_title'  => 'Blog Posts',
                'title'       => '4. Blog Posts',
                'summary'     => 'Write and publish articles for your website.',
                'intro'       => 'Use this to add news or articles. They can appear on any page where you add a “Blog Grid” section.',
                'url'         => $base . '/blogs',
                'purpose'     => 'Create blog articles with title, image, and text that visitors can read on your webshop.',
                'image'       => 'blogs.png',
                'steps'       => array(
                    'Click <strong>Blog Posts</strong> in the left sidebar.',
                    'Optional: open <strong>Blog Categories</strong>, type a category name (e.g. Wellness), and click <strong>Save category</strong>.',
                    'Click the green <strong>+ Add Blog Post</strong> button.',
                    'Fill in <strong>title</strong>, <strong>short description</strong>, and <strong>full article text</strong>.',
                    'Upload an <strong>image</strong> or pick one from the Media library.',
                    'Set status to <strong>Published</strong> and turn <strong>Active</strong> ON.',
                    'Click <strong>Save</strong>. The article is now ready to show in a Blog Grid section on a CMS page.',
                ),
                'tips'        => array('To display blogs on the website: edit a CMS Page → Add section → choose <strong>Blog Grid</strong>.'),
            ),
            array(
                'id'          => 'testimonials',
                'icon'        => 'fa-quote-left',
                'card_title'  => 'Testimonials',
                'title'       => '5. Testimonials',
                'summary'     => 'Add customer quotes and reviews to show on your site.',
                'intro'       => 'Collect short quotes from happy customers with their name and photo.',
                'url'         => $base . '/testimonials',
                'purpose'     => 'Stores customer testimonials that you can display in a grid on any CMS page.',
                'image'       => 'testimonials.png',
                'steps'       => array(
                    'Click <strong>Testimonials</strong> in the left sidebar.',
                    'Click <strong>+ Add Testimonial</strong>.',
                    'Enter the person’s <strong>name</strong>, their <strong>quote</strong>, and upload a <strong>photo</strong> if you have one.',
                    'Set status to <strong>Published</strong> and click <strong>Save</strong>.',
                    'On a CMS Page, add a <strong>Testimonials Grid</strong> section to show them on the website.',
                ),
            ),
            array(
                'id'          => 'layout',
                'icon'        => 'fa-object-group',
                'card_title'  => 'Header & Footer',
                'title'       => '6. Header & Footer',
                'summary'     => 'Design the top menu bar and bottom footer of your website.',
                'intro'       => 'Set your logo, navigation links, and footer text once — then use the same design on all pages.',
                'url'         => $base . '/layout_builder',
                'purpose'     => 'Build the site header (logo, menu, cart icon) and footer (links, contact, social icons).',
                'image'       => 'layout-builder.png',
                'steps'       => array(
                    'Click <strong>Header & Footer</strong> in the left sidebar.',
                    'Click the <strong>Header</strong> tab to edit the top bar, or <strong>Footer</strong> for the bottom.',
                    'Choose a layout from the dropdown (start with <strong>Default</strong> if unsure).',
                    'Use the builder to set your <strong>logo</strong>, menu links, and colors.',
                    'Click <strong>Save</strong> when finished.',
                    'Header menu links come from CMS Pages where <strong>Show in header menu</strong> is turned on.',
                ),
            ),
            array(
                'id'          => 'media',
                'icon'        => 'fa-picture-o',
                'card_title'  => 'Media Library',
                'title'       => '7. Media',
                'summary'     => 'Upload and reuse images across your whole website.',
                'intro'       => 'Upload logos, banners, and photos once here — then pick them when editing pages or blogs.',
                'url'         => $base . '/media',
                'purpose'     => 'Central photo library for logos, banners, page images, and blog pictures.',
                'image'       => 'media.png',
                'steps'       => array(
                    'Click <strong>Media</strong> in the left sidebar.',
                    'Click the green <strong>Upload</strong> button and choose image files from your computer.',
                    'Use the folder links (Logos, Page banners, Hero images, etc.) to filter files.',
                    'Click an image to copy its link or use the picker when editing a page/blog.',
                    'To remove an unused file, click the trash icon and confirm.',
                ),
            ),
            array(
                'id'          => 'entity',
                'icon'        => 'fa-tags',
                'card_title'  => 'Entity Pages',
                'title'       => '8. Entity Pages',
                'summary'     => 'SEO settings for individual products and categories.',
                'intro'       => 'Use this when you need special search settings for one product or category — not for general website pages.',
                'url'         => $base . '/entity_tags',
                'purpose'     => 'Add title, description, and structured data for a specific product, category, or blog post.',
                'image'       => 'entity-tags.png',
                'steps'       => array(
                    'Click <strong>Entity Pages</strong> in the left sidebar.',
                    'Click <strong>+ Add Mapping</strong>.',
                    'Choose type: <strong>Product</strong>, <strong>Category</strong>, or <strong>Blog</strong>.',
                    'Search and select the item, then fill in the SEO fields shown.',
                    'Click <strong>Save</strong>. These values apply when that item’s page is viewed on the website.',
                ),
            ),
            array(
                'id'          => 'tags',
                'icon'        => 'fa-database',
                'card_title'  => 'Tag Master',
                'title'       => '9. Tag Master',
                'summary'     => 'Control which SEO and GEO fields appear on forms.',
                'intro'       => 'Usually set up once by an administrator. Controls which fields you see when editing CMS Pages or Entity Pages.',
                'url'         => $base . '/tags_master',
                'purpose'     => 'Turns SEO, GEO, and Social meta fields on or off for page and product editors.',
                'image'       => 'tags-master.png',
                'steps'       => array(
                    'Click <strong>Tag Master</strong> in the left sidebar.',
                    'Browse the list of tag names (title, meta description, geo.region, og:image, etc.).',
                    'Click <strong>Edit details</strong> on a row to change where it appears.',
                    'Turn on <strong>Show on CMS Pages edit</strong> or <strong>Show on Entity Tag Mapping</strong> as needed.',
                    'Save. The fields will appear on the matching edit screens.',
                ),
            ),
            array(
                'id'          => 'site',
                'icon'        => 'fa-cog',
                'card_title'  => 'Site Settings',
                'title'       => '10. Site Settings',
                'summary'     => 'Robots.txt, sitemap, and AI crawler settings.',
                'intro'       => 'Advanced settings for search engines. Ask your web team before changing these if you are unsure.',
                'url'         => $base . '/site_settings/robots',
                'purpose'     => 'Manage robots.txt rules, sitemap preview, and llms.txt for search and AI crawlers.',
                'image'       => 'site-settings.png',
                'steps'       => array(
                    'Click <strong>Site Settings</strong> in the sidebar to expand the submenu.',
                    'Click <strong>Robots</strong> to review which pages search engines may crawl.',
                    'Click <strong>Sitemap.xml</strong> to preview your sitemap URL list.',
                    'Click <strong>LLMS</strong> to edit llms.txt content for AI tools, then Save.',
                ),
            ),
            array(
                'id'          => 'prices',
                'icon'        => 'fa-inr',
                'card_title'  => 'Prices',
                'title'       => '11. Prices',
                'summary'     => 'Set online selling prices and MRP for products.',
                'intro'       => 'Update what customers see on the website without changing your main inventory prices.',
                'url'         => $base . '/prices',
                'purpose'     => 'Bulk edit webshop price and MRP by product category.',
                'image'       => 'prices.png',
                'steps'       => array(
                    'Click <strong>Prices</strong> in the left sidebar.',
                    'Click a <strong>category</strong> name to load its products in the grid.',
                    'Type new values in the <strong>E-shop price</strong> and <strong>MRP</strong> columns.',
                    'Click <strong>Save Price Updates</strong> at the bottom when you are done.',
                ),
            ),
            array(
                'id'          => 'forms',
                'icon'        => 'fa-list-alt',
                'card_title'  => 'Form Templates',
                'title'       => '12. Form Templates',
                'summary'     => 'Build contact forms for your website.',
                'intro'       => 'Create a form (name, email, message fields) and place it on a CMS page. Submissions go to Leads.',
                'url'         => $base . '/form_templates',
                'purpose'     => 'Design reusable contact forms for “Contact us” sections on CMS pages.',
                'image'       => 'form-templates.png',
                'steps'       => array(
                    'Click <strong>Form Templates</strong> in the left sidebar.',
                    'Click <strong>+ New Form Template</strong>.',
                    'Enter a <strong>template name</strong> and <strong>form key</strong> (a short code name).',
                    'Add fields: text, email, phone, message box, etc.',
                    'Click <strong>Save</strong>.',
                    'On a CMS Page, add a <strong>Contact form</strong> section and select this template.',
                ),
            ),
            array(
                'id'          => 'leads',
                'icon'        => 'fa-users',
                'card_title'  => 'Leads',
                'title'       => '13. Leads',
                'summary'     => 'View contact form submissions and customer enquiries.',
                'intro'       => 'When someone fills a form on your website, their details appear here.',
                'url'         => $base . '/leads',
                'purpose'     => 'List of enquiries from webshop forms and ERP leads — update status and assign to staff.',
                'image'       => 'leads.png',
                'steps'       => array(
                    'Click <strong>Leads</strong> in the left sidebar.',
                    'Browse the table — each row is one enquiry.',
                    'Click a name to open details, or use the green <strong>Actions</strong> button.',
                    'Change <strong>Status</strong> (New, Follow up, Converted, etc.) from the dropdown.',
                    'Use Actions to edit, convert to customer, or view history.',
                ),
            ),
            array(
                'id'          => 'newsletter',
                'icon'        => 'fa-envelope-o',
                'card_title'  => 'Newsletter',
                'title'       => '14. Newsletter',
                'summary'     => 'See who signed up for your email newsletter.',
                'intro'       => 'Read-only list — visitors subscribe using the email box in your website footer.',
                'url'         => $base . '/newsletter_subscribers',
                'purpose'     => 'Shows email addresses collected from the footer newsletter field on your webshop.',
                'image'       => 'newsletter.png',
                'steps'       => array(
                    'Click <strong>Newsletter</strong> in the left sidebar.',
                    'Review the list of subscriber emails and signup dates.',
                    'Use the search box to find a specific email.',
                    'Export or copy emails for your email marketing tool (outside CMS Admin).',
                ),
            ),
        );

        return $this->apply_guide_difficulties(
            $this->apply_guide_visual_steps($sections, $base),
            $base
        );
    }

    /**
     * Merge per-step screenshots and highlight coordinates from config.
     *
     * @param array<int,array<string,mixed>> $sections
     * @param string                         $base
     * @return array<int,array<string,mixed>>
     */
    protected function apply_guide_visual_steps(array $sections, $base)
    {
        $this->load->helper('cms_guide');
        require_once APPPATH . 'config/cms_guide_visual_steps.php';
        $map = cms_guide_visual_steps_map($base);

        foreach ($sections as &$section) {
            if (empty($section['id']) || !isset($map[$section['id']])) {
                continue;
            }
            $visual = $map[$section['id']];
            if (!empty($visual['workflows']) && is_array($visual['workflows'])) {
                $section['workflows'] = $visual['workflows'];
            } elseif (!empty($visual['steps']) && is_array($visual['steps'])) {
                $section['steps'] = $visual['steps'];
            }
        }
        unset($section);

        return $sections;
    }

    /**
     * Append new-user difficulty steps and journey notes from config.
     *
     * @param array<int,array<string,mixed>> $sections
     * @param string                         $base
     * @return array<int,array<string,mixed>>
     */
    protected function apply_guide_difficulties(array $sections, $base)
    {
        require_once APPPATH . 'config/cms_guide_difficulties.php';
        $map = cms_guide_difficulty_steps_map($base);

        foreach ($sections as &$section) {
            if (empty($section['id']) || !isset($map[$section['id']])) {
                continue;
            }
            $extra = $map[$section['id']];
            if (!empty($extra['prepend_steps']) && is_array($extra['prepend_steps'])) {
                if (empty($section['steps']) || !is_array($section['steps'])) {
                    $section['steps'] = array();
                }
                $section['steps'] = array_merge($extra['prepend_steps'], $section['steps']);
            }
            if (!empty($extra['workflows']) && is_array($extra['workflows'])) {
                if (empty($section['workflows']) || !is_array($section['workflows'])) {
                    $section['workflows'] = array();
                }
                $section['workflows'] = array_merge($section['workflows'], $extra['workflows']);
            } elseif (!empty($extra['steps']) && is_array($extra['steps'])) {
                if (empty($section['steps']) || !is_array($section['steps'])) {
                    $section['steps'] = array();
                }
                $section['steps'] = array_merge($section['steps'], $extra['steps']);
            }
            if (!empty($extra['tips']) && is_array($extra['tips'])) {
                if (empty($section['tips']) || !is_array($section['tips'])) {
                    $section['tips'] = array();
                }
                $section['tips'] = array_merge($section['tips'], $extra['tips']);
            }
        }
        unset($section);

        return $sections;
    }
}
