<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'errors/error_404';
$route['translate_uri_dashes'] = FALSE;

// CMS Admin Panel (standalone MVC under app/controllers/cms_admin/)
$route['cms_admin'] = 'cms_admin/dashboard/index';
$route['cms_admin_panel'] = 'cms_admin/dashboard/index';
$route['cms_admin_panel/(:any)'] = 'cms_admin/dashboard/$1';

// CMS pages AJAX (section toggle / reorder)
$route['cms_admin/pages/toggle_section/(:num)/(:num)'] = 'cms_admin/pages/toggle_section/$1/$2';
$route['cms_admin/pages/reorder_sections/(:num)'] = 'cms_admin/pages/reorder_sections/$1';
$route['cms_admin/pages/reorder_nav'] = 'cms_admin/pages/reorder_nav';
$route['cms_admin/pages/toggle_nav_visibility/(:num)'] = 'cms_admin/pages/toggle_nav_visibility/$1';

// Catalog: /catalog/123 must map to index(123), not method "123"
$route['cms_admin/catalog/ajax'] = 'cms_admin/catalog/ajax';
$route['cms_admin/catalog/(:num)'] = 'cms_admin/catalog/index/$1';
$route['cms_admin/catalog'] = 'cms_admin/catalog/index';

// CMS admin catch-all (subdir controllers: pages, storefront, entity_tags, prices, …)
$route['cms_admin/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'cms_admin/$1/$2/$3/$4/$5';
$route['cms_admin/(:any)/(:any)/(:any)/(:any)'] = 'cms_admin/$1/$2/$3/$4';
$route['cms_admin/(:any)/(:any)/(:any)'] = 'cms_admin/$1/$2/$3';
$route['cms_admin/(:any)/(:any)'] = 'cms_admin/$1/$2';
$route['cms_admin/(:any)'] = 'cms_admin/$1';

// CMS storefront page routes (public)
$route['cmspage'] = 'cmspage/index';
$route['Cmspage'] = 'cmspage/index';
$route['cmspage/(:any)'] = 'cmspage/index/$1';
$route['Cmspage/(:any)'] = 'cmspage/index/$1';
$route['product'] = 'cmspage/index/product';

$route['users'] = 'auth/users';
$route['users/create_user'] = 'auth/create_user';
$route['users/profile/(:num)'] = 'auth/profile/$1';
$route['login'] = 'auth/login';
$route['login/(:any)'] = 'auth/login/$1';
$route['logout'] = 'auth/logout';
$route['logout/(:any)'] = 'auth/logout/$1';
$route['restandlogout'] = 'auth/restandlogout';
$route['register'] = 'auth/register';
$route['forgot_password'] = 'auth/forgot_password';
$route['sales/(:num)'] = 'sales/index/$1';
$route['products/(:num)'] = 'products/index/$1';
$route['purchases/(:num)'] = 'purchases/index/$1';
$route['quotes/(:num)'] = 'quotes/index/$1';
$route['attendance'] = 'attendance/index';
$route['attendance/captured'] = 'attendance/captured';
$route['attendance/store'] = 'attendance/store';
$route['attendance/save_user'] = 'attendance/save_user';
$route['attendance/edit_user/(:num)'] = 'attendance/edit_user/$1';
$route['attendance/update_user/(:num)'] = 'attendance/update_user/$1';
$route['attendance/details/(:num)'] = 'attendance/details/$1';
$route['attendance/edit/(:num)'] = 'attendance/edit/$1';
$route['attendance/update/(:num)'] = 'attendance/update/$1';
$route['attendance/delete/(:num)'] = 'attendance/delete/$1';
$route['attendance/report'] = 'attendance/report';
$route['attendance/list_actions'] = 'attendance/list_actions';

// Service Site Report
$route['service-site-report'] = 'service_requests/service_site_report';
$route['service-site-report/(:any)'] = 'service_requests/service_site_report/$1';
$route['service_site_report_mobile'] = 'service_requests/service_site_report_mobile';
$route['service_site_report_mobile/(:any)'] = 'service_requests/service_site_report_mobile/$1';

// Mobile view controllers
$route['Production_Unit_Mobile'] = 'mobile_view/Production_Unit_Mobile';
$route['Production_Unit_Mobile/(:any)'] = 'mobile_view/Production_Unit_Mobile/$1';
$route['Products_Mobile'] = 'mobile_view/Products_Mobile';
$route['Products_Mobile/(:any)'] = 'mobile_view/Products_Mobile/$1';
$route['sales_mobile'] = 'mobile_view/Sales_Mobile';
$route['sales_mobile/(:any)'] = 'mobile_view/Sales_Mobile/$1';
$route['Purchases_Mobile'] = 'mobile_view/Purchases_Mobile';
$route['Purchases_Mobile/(:any)'] = 'mobile_view/Purchases_Mobile/$1';
