<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class webshop_settings extends MY_Controller {

    public $webshop_settings;

    public function __construct() {
        parent::__construct();

        $this->active_webshop = (bool) $this->Settings->active_webshop ? $this->Settings->active_webshop : 0;
        if (!$this->active_webshop) {
            redirect('access_denied');
        }

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Owner) {
            $allowed = 0;

            if ($allowed === 0) {
                $this->session->set_flashdata('warning', lang('access_denied'));
                redirect('welcome');
            }
        }

        $this->lang->load('settings', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('webshop_settings_model');
        $this->load->model('cms_model');

        $this->webshop_settings = $this->webshop_settings_model->getWebshopSettings();
        if (!$this->webshop_settings) {
            $this->webshop_settings = (object) ['home_page' => 'theme_1'];
        }

        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';

        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '2048';
        
    }

    public function service_off() {

        $this->load->view('default/views/service_off', $this->data);
    }

    public function index() {

        $this->form_validation->set_rules('home_page', lang('home_page'), 'trim|required');
        $this->form_validation->set_rules('product_list_page', lang('product_list_page'), 'trim|required');
        $this->form_validation->set_rules('product_list_view', lang('product_list_view'), 'trim|required');
        $this->form_validation->set_rules('product_description', lang('product_description'), 'trim|required');
        $this->form_validation->set_rules('header_strip_style', lang('header_strip_style'), 'trim|required');
        $this->form_validation->set_rules('theme_color', lang('theme_color'), 'trim|required');
        $this->form_validation->set_rules('header_style', lang('header_style'), 'trim|required');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                "home_page" => $this->input->post('home_page'),
                "product_list_page" => $this->input->post('product_list_page'),
                "product_list_view" => $this->input->post('product_list_view'),
                "product_description" => $this->input->post('product_description'),
                "theme_color" => $this->input->post('theme_color'),
                "header_strip_style" => $this->input->post('header_strip_style'),
                "header_style" => $this->input->post('header_style'),
            );

            if ($this->webshop_settings_model->updateWebshopSettings($data)) {
                $this->session->set_flashdata('message', lang('setting_updated'));
                redirect('webshop_settings/index');
            } else {
                $this->session->set_flashdata('error', lang('setting_updated_failed'));
                redirect('webshop_settings/index');
            }
        } else {

            $this->data['webshop_settings'] = $this->webshop_settings;

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Ecommerce Layout')));
            $meta = array('page_title' => lang('Ecommerce Layout'), 'bc' => $bc);

            $this->page_construct('webshop_settings/index', $meta, $this->data);
        }
    }

    public function sliders() {

        if (isset($_POST['update_settings'])) {

            if ($this->input->post('is_active_1') == 1) {

                $data[] = array(
                    "slide_key" => 'SLIDE_1',
                    "slide_image" => $this->input->post('slide_image_1'),
                    "background_image" => $this->input->post('slide_bg_1'),
                    "title" => $this->input->post('slide_title_1'),
                    "sub_title" => $this->input->post('slide_subtitle_1'),
                    "button_caption" => $this->input->post('slide_button_1'),
                    "button_link" => $this->input->post('slide_button_link_1'),
                    "bottom_caption" => $this->input->post('slide_bottom_1'),
                    "title_color" => $this->input->post('title_color_1'),
                    "subtitle_color" => $this->input->post('subtitle_color_1'),
                    "is_active" => 1,
                    "is_updated" => 1,
                    "updated_at" => date('Y-m-d H:i:s'),
                );
            } else {
                $data[] = array("slide_key" => 'SLIDE_1', "is_active" => 0, "updated_at" => date('Y-m-d H:i:s'));
            }

            if ($this->input->post('is_active_2') == 2) {

                $data[] = array(
                    "slide_key" => 'SLIDE_2',
                    "slide_image" => $this->input->post('slide_image_2'),
                    "background_image" => $this->input->post('slide_bg_2'),
                    "title" => $this->input->post('slide_title_2'),
                    "sub_title" => $this->input->post('slide_subtitle_2'),
                    "button_caption" => $this->input->post('slide_button_2'),
                    "button_link" => $this->input->post('slide_button_link_2'),
                    "bottom_caption" => $this->input->post('slide_bottom_2'),
                    "title_color" => $this->input->post('title_color_2'),
                    "subtitle_color" => $this->input->post('subtitle_color_2'),
                    "is_active" => 1,
                    "is_updated" => 1,
                    "updated_at" => date('Y-m-d H:i:s'),
                );
            } else {
                $data[] = array("slide_key" => 'SLIDE_2', "is_active" => 0, "updated_at" => date('Y-m-d H:i:s'),);
            }

            if ($this->webshop_settings_model->updateWebshopSliderSettings($data)) {
                $this->session->set_flashdata('message', 'Slider Setting Updated');
                redirect('webshop_settings/sliders');
            }
        } elseif (isset($_POST['reset_default'])) {

            $data[] = array("slide_key" => 'SLIDE_1', "is_active" => 1, "is_updated" => 0, "updated_at" => date('Y-m-d H:i:s'));
            $data[] = array("slide_key" => 'SLIDE_2', "is_active" => 1, "is_updated" => 0, "updated_at" => date('Y-m-d H:i:s'));

            if ($this->webshop_settings_model->updateWebshopSliderSettings($data)) {
                $this->session->set_flashdata('message', 'Slider Reset Successfully');
                redirect('webshop_settings/sliders');
            }
        } else {

            $this->data['sliders'] = $this->webshop_settings_model->get_sliders();

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Home Slider')));
            $meta = array('page_title' => lang('Home Slider'), 'bc' => $bc);

            $this->page_construct('webshop_settings/sliders', $meta, $this->data);
        }
    }

    public function sliders_images() {

        if ($_POST['upload_images'] == "Upload Images") {


            if ($_FILES["background_images"]["error"] && $_FILES["slider_images"]["error"]) {

                $this->session->set_flashdata('error', lang('Please Select Images'));
                redirect('webshop_settings/sliders');
            }

            $statusBg = $statusImg = TRUE;
            // Check if file was uploaded without errors
            if (isset($_FILES["background_images"]) && $_FILES["background_images"]["error"] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png");
                $filename = $_FILES["background_images"]["name"];
                $filetype = $_FILES["background_images"]["type"];
                $filesize = $_FILES["background_images"]["size"];

                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed))
                    die("Error: Please select a valid file format.");

                // Verify file size - 512MB maximum
                $maxsize = 0.5 * 1024 * 1024;
                if ($filesize > $maxsize)
                    die("Error: File size is larger than the allowed limit.");

                // Verify MYME type of the file
                if (in_array($filetype, $allowed)) {
                    // Check whether file exists before uploading it
                    if (file_exists("assets/mdata/$this->Customer_assets/uploads/webshop/slider/bg/" . $filename)) {
                        $statusBg = FALSE;
                        $statusBgMsg = $filename . " is already exists.";
                    } else {
                        move_uploaded_file($_FILES["background_images"]["tmp_name"], "assets/mdata/$this->Customer_assets/uploads/webshop/slider/bg/" . $filename);
                        $statusBg = TRUE;
                    }
                } else {
                    $statusBg = FALSE;
                }
            } else {
                $statusBg = FALSE;
                $statusBgMsg = $_FILES["background_images"]["error"];
            }

            // Check if file was uploaded without errors
            if (isset($_FILES["slider_images"]) && $_FILES["slider_images"]["error"] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png");
                $filename = $_FILES["slider_images"]["name"];
                $filetype = $_FILES["slider_images"]["type"];
                $filesize = $_FILES["slider_images"]["size"];

                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed))
                    die("Error: Please select a valid file format.");

                // Verify file size - 512MB maximum
               /* $maxsize = 0.5 * 1024 * 1024;
                if ($filesize > $maxsize)
                    die("Error: File size is larger than the allowed limit.");*/

                // Verify MYME type of the file
                if (in_array($filetype, $allowed)) {
                    // Check whether file exists before uploading it
                    if (file_exists("assets/mdata/$this->Customer_assets/uploads/webshop/slider/slide/" . $filename)) {
                        $statusImg = false;
                        $statusImgMsg = $filename . " is already exists.";
                    } else {
                        move_uploaded_file($_FILES["slider_images"]["tmp_name"], "assets/mdata/$this->Customer_assets/uploads/webshop/slider/slide/" . $filename);
                        $statusImg = true;
                    }
                } else {
                    $statusImg = false;
                }
            } else {
                $statusImg = false;
                $statusImgMsg = "Error: " . $_FILES["slider_images"]["error"];
            }

            if ($statusImg || $statusBg) {

                $this->session->set_flashdata('message', lang('Images Uploaded Successfully'));
                redirect('webshop_settings/sliders');
            }
        } else {

            echo "Invalid Action";
        }
    }

    public function sections() {

        if ($this->input->post('update_settings')) {

            $sections = $this->input->post('section_id');
            $section_title = $this->input->post('section_title');
            $display_status = $this->input->post('display_status');
            $display_order = $this->input->post('display_order');

            if (!empty($sections) && is_array($sections)) {
            foreach ($sections as $section_id) {

                $status = ($display_status[$section_id] ? $display_status[$section_id] : 0);

                $data[] = array(
                    "id" => $section_id,
                    "section_title" => $section_title[$section_id],
                    "display_status" => $status,
                    "display_order" => $display_order[$section_id],
                );
            }//end foreach 
            }

            if (!empty($data) && $this->webshop_settings_model->updateWebshopSections($data)) {
                $this->session->set_flashdata('message', lang('section_updated'));
                redirect('webshop_settings/sections');
            } else {
                $this->session->set_flashdata('error', lang('sections_updated_failed'));
                redirect('webshop_settings/sections');
            }
        } else {

            $this->data['sections'] = $this->webshop_settings_model->getThemeSections($this->webshop_settings->home_page);

            $bc = array(array('link' => base_url(), 'page' => lang('Home')), array('link' => '#', 'page' => lang('Ecommerce / Homepage Sections')));
            $meta = array('page_title' => lang('Ecommerce Homepage Sections'), 'bc' => $bc);

            $this->page_construct('webshop_settings/sections', $meta, $this->data);
        }
    }

    public function elements($element_name) {

        if (!$element_name) {
            redirect('webshop_settings/sections');
        }

//        echo '<pre>';
//        print_r($this->webshop_settings->theme_color);
//        echo '</pre>';

        $this->data['webshop_settings'] = $this->webshop_settings;

        $this->data['active_sections'] = array();
        $this->data['sections'] = array();

        $sections = $this->webshop_settings_model->getActiveSections($this->webshop_settings->home_page);

        if (!empty($sections) && is_array($sections)) {
        foreach ($sections as $key => $section) {
            $this->data['active_sections'][] = $section->section_name;
            $this->data['sections'][$section->section_name] = $section->section_data;
        }
        }

        $this->data['section_name'] = $element_name;

        switch ($element_name) {

            case "section_subcategory_tabs_multiple_sections":

                $this->data['categories'] = $this->webshop_settings_model->get_categories();

                break;

            case "section_category_tab_right_highlite_products":
            case "section_category_exclusive_products":
            case "section_category_tab_vertical_align":
            case "section_category_tab_center_align":
            case "section_category_tab_right_align":
            case "section_category_tab_left_align":

                $this->data['categories'] = $this->webshop_settings_model->get_categories();
                $this->data['category_products'] = $this->webshop_settings_model->get_category_products();

                break;

            case "section_features_list":

                $this->data['features'] = $this->webshop_settings_model->get_features();

                break;

            case "section_fullwidth_notice":

                $this->data['section_data'] = isset($this->data['sections']['section_fullwidth_notice']) ? $this->data['sections']['section_fullwidth_notice'] : '';

                break;

            case "section_top_categories":

                $this->data['categories'] = $this->webshop_settings_model->get_categories();

                $this->data['section_data'] = isset($this->data['sections']['section_top_categories']) ? $this->data['sections']['section_top_categories'] : '';

                break;

            default:
                break;
        }//end switch.


        $bc = array(array('link' => base_url(), 'page' => lang('Home')), array('link' => base_url('webshop_settings/sections'), 'page' => lang('Ecommerce Homepage ')), array('link' => '#', 'page' => ucwords(lang($element_name)) . ' Settings'));

        $meta = array('page_title' => lang('Ecommerce Homepage Sections Setting'), 'bc' => $bc);

        $this->data['elemtnt_name'] = 'elements_' . $element_name;

        $this->page_construct('webshop_settings/elements', $meta, $this->data);
    }

    public function elements_section_features_list() {

        if ($this->input->post('update_elements')) {

            $titles = $this->input->post('title');
            $subtitles = $this->input->post('subtitle');
            $icons = $this->input->post('icon');
            $status = $this->input->post('is_active');

            foreach ($titles as $key => $title) {
                $data[] = array(
                    "id" => $key,
                    "title" => $title,
                    "subtitle" => $subtitles[$key],
                    "icon" => $icons[$key],
                    "is_active" => $status[$key],
                );
            }

            $update = $this->webshop_settings_model->updateFeatures($data);

            if ($update) {
                $this->session->set_flashdata('message', lang('Features_updated'));
                redirect('webshop_settings/elements/section_features_list');
            } else {
                $this->session->set_flashdata('error', lang('Features_not_updated'));
                redirect('webshop_settings/elements/section_features_list');
            }
        }
    }

    public function elements_section_subcategory_tabs_multiple_sections() {

        if ($this->input->post('update_elements')) {

            if (isset($_POST['section_subcategory_tabs_products'])) {

                $postData['section_titles'] = $this->input->post('section_title');
                $postData['section_tab_categories'] = $this->input->post('section_subcategory_tabs_products');

                $section_data = serialize(json_encode($postData, TRUE));

                $update = $this->db->where(["section_name" => "section_subcategory_tabs_multiple_sections"])
                        ->update('webshop_homepage_sections', ['section_data' => $section_data]);

                if ($update) {
                    $this->session->set_flashdata('message', lang('elements_updated'));
                    redirect('webshop_settings/elements/section_subcategory_tabs_multiple_sections');
                } else {
                    $this->session->set_flashdata('error', lang('elements_updated_failed'));
                    redirect('webshop_settings/elements/section_subcategory_tabs_multiple_sections');
                }
            }
        }
    }

    public function elements_section_category_tab_right_highlite_products() {

        if ($this->input->post('update_elements')) {

            $this->set_section_category_tab_data('section_category_tab_right_highlite_products', $_POST);
        }
    }

    public function elements_section_category_tab_vertical_align() {

        if ($this->input->post('update_elements')) {

            $this->set_section_category_tab_data('section_category_tab_vertical_align', $_POST);
        }
    }

    public function elements_section_category_tab_center_align() {

        if ($this->input->post('update_elements')) {

            $this->set_section_category_tab_data('section_category_tab_center_align', $_POST);
        }
    }

    public function elements_section_category_tab_left_align() {

        if ($this->input->post('update_elements')) {

            $this->set_section_category_tab_data('section_category_tab_left_align', $_POST);
        }
    }

    public function elements_section_category_tab_right_align() {

        if ($this->input->post('update_elements')) {

            $this->set_section_category_tab_data('section_category_tab_right_align', $_POST);
        }
    }

    public function elements_section_category_exclusive_products() {

        if ($this->input->post('update_elements')) {

            $this->set_section_category_tab_data('section_category_exclusive_products', $_POST);
        }
    }

    public function set_section_category_tab_data($section_name, $post_data) {

        if ($post_data) {

            if (is_array($post_data['section_category_tabs'])) {

                $section_category_tabs = $post_data['section_category_tabs'];
                $section_title = $post_data['section_title'];
                $section_products = $post_data['section_category_products'];

                if (isset($post_data['section_category_highlite_products'])) {
                    $section_highlite_products = $post_data['section_category_highlite_products'];
                }

                foreach ($section_category_tabs as $category_id) {

                    $postData['tabs'][$category_id] = $section_title[$category_id];
                    $postData['products'][$category_id] = $section_products[$category_id];

                    if (isset($section_highlite_products[$category_id])) {
                        $postData['highlite'][$category_id] = $section_highlite_products[$category_id];
                    }
                }//end foreach

                $sectionData = serialize(json_encode($postData, TRUE));

                $update = $this->db->where(["section_name" => $section_name])
                        ->update('webshop_homepage_sections', ['section_data' => $sectionData]);

                if ($update) {
                    $this->session->set_flashdata('message', lang('elements_updated'));
                    redirect('webshop_settings/elements/' . $section_name);
                } else {
                    $this->session->set_flashdata('error', lang('elements_updated_failed'));
                    redirect('webshop_settings/elements/' . $section_name);
                }
            }
        }
    }

    public function elements_section_top_categories() {

        if ($this->input->post('update_elements')) {

            if (isset($_POST['section_top_categories'])) {

                $postData['category_titles'] = $this->input->post('category_titles');
                $postData['section_top_categories'] = $this->input->post('section_top_categories');

                $section_data = serialize(json_encode($postData, TRUE));

                $update = $this->db->where(["section_name" => "section_top_categories"])
                        ->update('webshop_homepage_sections', ['section_data' => $section_data]);

                if ($update) {
                    $this->session->set_flashdata('message', lang('elements_updated'));
                    redirect('webshop_settings/elements/section_top_categories');
                } else {
                    $this->session->set_flashdata('error', lang('elements_updated_failed'));
                    redirect('webshop_settings/elements/section_top_categories');
                }
            }
        }
    }

    public function elements_section_fullwidth_notice() {

        if ($this->input->post('update_elements')) {

            $section_data = $this->input->post('section_data');

            $update = $this->db->where(["section_name" => "section_fullwidth_notice"])
                    ->update('webshop_homepage_sections', ['section_data' => $section_data]);

            if ($update) {
                $this->session->set_flashdata('message', lang('elements_updated'));
                redirect('webshop_settings/elements/section_fullwidth_notice');
            } else {
                $this->session->set_flashdata('error', lang('elements_updated_failed'));
                redirect('webshop_settings/elements/section_fullwidth_notice');
            }
        }
    }

    public function custom_pages() {

        $custom_pages = $this->webshop_settings_model->getCustomPages();

        $bc = array(array('link' => base_url(), 'page' => lang('Home')), array('link' => '#', 'page' => lang('Custom Pages')));

        $meta = array('page_title' => lang('Ecommerce Custom Pages'), 'bc' => $bc);

        $this->data['custom_pages'] = $custom_pages;

        $this->page_construct('webshop_settings/pages', $meta, $this->data);
    }

    public function edit_custom_pages($page = null, $page_key = null) {

        if (isset($_POST['update_custom_pages'])) {

            $page_title = trim($this->input->post('page_title'));

            $page_type = $this->input->post('page_type');
            $page_section = $this->input->post('page_section');
            $is_active = $this->input->post('is_active');
            $page_id = $this->input->post('page_id');

            $page_key = str_replace([' & ',' ','&','-','\''], ['_'], strtolower($page_title));

            $page_file = $page_text = '';
            if ($page_type == 'text') {
                $page_text = $_POST['page_text']; //trim($this->input->post('page_text'));
            } else {
                $page_file = trim($_FILES['page_file']['name']);
                if(!empty($page_file)){ 
                    $this->do_upload('page_file', 'pages');
                }
            }

            $data = [
                'page_title'    => $page_title,
                'page_text'     => $page_text,
                'page_file'     => $page_file,
                'page_type'     => $page_type,
                'page_section'  => $page_section,
                'is_active'     => $is_active,
                'page_key'      => $page_key,
            ];
            
            if($this->db->where(['id'=>$page_id])->update('webshop_static_pages' ,$data)){
                $this->session->set_flashdata('message', 'Page Updated Successfully');
                redirect('webshop_settings/custom_pages');
            }            
            
        } else {

            $pageData = $this->webshop_settings_model->getCustomPages($page_key);

            $pageTitle = (is_array($pageData) && $page && isset($pageData[$page]['page_title'])) ? $pageData[$page]['page_title'] : lang('Edit Custom Pages');
            $bc = array(array('link' => base_url(), 'page' => lang('Home')), array('link' => base_url('webshop_settings/custom_pages/'), 'page' => lang('Edit Custom Pages')), array('link' => '#', 'page' => ucwords('Edit ' . $pageTitle)));

            $meta = array('page_title' => lang('Edit Custom Pages'), 'bc' => $bc);

            $this->data['page_data'] = (is_array($pageData) && $page && isset($pageData[$page])) ? $pageData[$page] : array();

            $this->page_construct('webshop_settings/page_edit', $meta, $this->data);
        }
    }

    public function do_upload($field_name, $folder='') {
        
        $config = array(
            'upload_path'   => "./assets/mdata/$this->Customer_assets/uploads/webshop/".($folder?$folder.'/':''),
            'allowed_types' => "gif|jpg|png|jpeg|pdf|doc|docx",
            'overwrite'     => TRUE,
            'max_size'      => "2048000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
            'max_height'    => "768",
            'max_width'     => "1024"
        );
        $this->load->library('upload', $config);
        if ($this->upload->do_upload($field_name)) {
            $data = array('status'=>'success', 'upload_data' => $this->upload->data());
            return $data;
        } else {
            $data = array('status'=>'fail', 'error' => $this->upload->display_errors());
            return $data;
        }
    }
    
    public function shipping_methods() {
        
        $this->load->model('eshop_model');
        
        $this->data['shippings'] = $this->eshop_model->getShippingMethods();

        if ($this->webshop_settings->active_multi_outlets) {
            $this->data['warehouses'] = $this->eshop_model->getEshopOutlets();
        } else {
            $this->data['warehouse_id'] = $this->webshop_settings->warehouse_id;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Shippings')));
        $meta = array('page_title' => lang('Shippings'), 'bc' => $bc);
        $this->page_construct('eshop/shipping_methods', $meta, $this->data);
    }
    
    public function manage_products($category_id = null) {
        
        $this->load->model('products_model');
        $this->data['products'] = null;
        if ($category_id) {

            $this->data['category_id'] = $category_id;
            $this->data['subcategories'] = $this->products_model->getCategories($category_id);
            $this->data['products'] = $this->products_model->getCategoryProducts($category_id);
        }

        $this->data['categories'] = $this->products_model->getCategories('', 'parent_id=0');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('Products'), 'bc' => $bc);
        $this->page_construct('webshop_settings/manage_products', $meta, $this->data);
    }
    
    public function settings() {
        
        $this->form_validation->set_rules('free_delivery_above_amount', lang('free_delivery_above_amount'), 'trim|required');
        $this->form_validation->set_rules('suport_email',           lang('suport_email'), 'trim|required');
        $this->form_validation->set_rules('suport_phone',           lang('suport_phone'), 'trim|required');
        $this->form_validation->set_rules('return_within_days',     lang('return_within_days'), 'trim|required');
        $this->form_validation->set_rules('active_multi_outlets',   lang('active_multi_outlets'), 'trim|required');
        $this->form_validation->set_rules('overselling', lang('overselling'), 'trim|required');
        $this->form_validation->set_rules('rounding', lang('rounding'), 'trim|required');
        $this->form_validation->set_rules('cod', lang('cod'), 'trim|required');
        $this->form_validation->set_rules('online_payment', lang('online_payment'), 'trim|required');
        $this->form_validation->set_rules('warehouse_id', lang('warehouse_id'), 'trim|required');
        $this->form_validation->set_rules('biller_id', lang('biller_id'), 'trim|required');
        

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                "free_delivery_above_amount" => $this->input->post('free_delivery_above_amount'),
                "suport_email"       => $this->input->post('suport_email'),
                "suport_phone"       => $this->input->post('suport_phone'),
                "return_within_days" => $this->input->post('return_within_days'),
                "overselling"        => $this->input->post('overselling'),
                "rounding"           => $this->input->post('rounding'),
                "cod"                => $this->input->post('cod'),
                "online_payment"     => $this->input->post('online_payment'),
                "warehouse_id"       => $this->input->post('warehouse_id'),
                "biller_id"          => $this->input->post('biller_id'),
            );

            if ($this->webshop_settings_model->updateWebshopSettings($data)) {
                $this->session->set_flashdata('message', lang('setting_updated'));
                redirect('webshop_settings/settings');
            } else {
                $this->session->set_flashdata('error', lang('setting_updated_failed'));
                redirect('webshop_settings/settings');
            }
        } else {

            $this->data['webshop_settings'] = $this->webshop_settings;
            $this->data['warehouses']       = $this->webshop_settings_model->get_warehouses();
            $this->data['billers']          = $this->webshop_settings_model->get_billers();            
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Ecommerce Settings')));
            $meta = array('page_title' => lang('Ecommerce Settings'), 'bc' => $bc);

            $this->page_construct('webshop_settings/settings', $meta, $this->data);
        }
    }
    
     public function webshop_ajax_request() {
    
        $action = $_POST['action'];
        $postData = $_POST;
    
        switch ($action) {
                        
            case "manage_eshop_category":
                
                $this->manage_eshop_category($postData);
                
                break;
            
            case "manage_eshop_product":
                
                $this->manage_eshop_product($postData);
                
                break;
            
            default:
                break;
        }//end switch.
}

    
    public function manage_eshop_category($postData) {
        
        $category_id  = isset($postData['category_id'])  ? $postData['category_id']  : false;
        $parent_id    = ((bool)$postData['parent_id'])   ? $postData['parent_id']    : 0;
        $eshop_status = isset($postData['eshop_status']) ? $postData['eshop_status'] : 0;
        $data['in_eshop'] = $eshop_status;
        $where = null;
        if((bool)$category_id){
            $where['id'] = $category_id;
        } 
        elseif(!(bool)$category_id && (bool)$parent_id ){
            $where['parent_id'] = $parent_id;
        }
        $response = [
                    'status_code'   => 500,
                    'status'        => 'ERROR',
                    'messages'      => 'Failed'
                ];
        
        if($where) {
            if($this->webshop_settings_model->update_eshop_status('categories', $where, $data)) {
                $response = [
                    'status_code'   => 200,
                    'status'        => 'SUCCESS',
                    'messages'      => 'Updated'
                ];
            }
        }
        
        echo json_encode($response);
    }
    
    public function manage_eshop_product($postData) {
        
        $product_id = isset($postData['product_id']) ? $postData['product_id'] : false;
        $variant_id = isset($postData['variant_id']) ? $postData['variant_id'] : 0;
        $eshop_status = isset($postData['eshop_status']) ? $postData['eshop_status'] : 0;
        $data['in_eshop'] = $eshop_status;
        $where = null;
        
        if((bool)$product_id){
            if((bool)$variant_id){
                $where['id'] = $variant_id;
                $where['product_id'] = $product_id;
                $tablename = "product_variants";
            } else {
                $where['id'] = $product_id;
                $tablename = "products";
            }
        }
        
        $response = [
                    'status_code'   => 500,
                    'status'        => 'ERROR',
                    'messages'      => 'Failed'
                ];
        
        if($where) {
            if($this->webshop_settings_model->update_eshop_status($tablename, $where, $data)) {
                $response = [
                    'status_code'   => 200,
                    'status'        => 'SUCCESS',
                    'messages'      => 'Updated'
                ];
            }
        }
        
        echo json_encode($response);
        
    }
    
    
    
    
    
    

    public function cms_pages() {
        $cms_pages = $this->cms_model->getAdminPages();

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => lang('CMS Pages'))
        );
        $meta = array('page_title' => lang('CMS Pages'), 'bc' => $bc);

        $this->data['cms_pages'] = $cms_pages;
        $this->page_construct('webshop_settings/cms_pages', $meta, $this->data);
    }

    public function add_storefront_identity_REMOVED() {
        if ($this->input->post('save_identity_row')) {
            if (!$this->webshop_settings_model->header_footer_schema_ready()) {
                $this->session->set_flashdata('error', 'Database tables are missing. Ensure <code>sma_cms_webshop_header_footer</code> exists, then retry.');
                redirect('webshop_settings/add_storefront_identity');
            }

            $section_type = strtolower(trim((string) $this->input->post('section_type')));
            $field_key    = strtolower(trim((string) $this->input->post('field_key')));
            $label        = trim((string) $this->input->post('label'));
            $value        = (string) $this->input->post('value');
            $icons        = trim((string) $this->input->post('icons'));
            $sort_order   = (int) $this->input->post('sort_order');
            $is_active    = $this->input->post('is_active') ? 1 : 0;

            if ($section_type !== 'header' && $section_type !== 'footer') {
                $this->session->set_flashdata('error', 'Choose a valid section (Header or Footer).');
                redirect('webshop_settings/add_storefront_identity');
            }
            if ($field_key === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $field_key)) {
                $this->session->set_flashdata('error', 'Field key is required (letters, numbers, underscore only).');
                redirect('webshop_settings/add_storefront_identity');
            }
            if ($label === '') {
                $this->session->set_flashdata('error', 'Label is required.');
                redirect('webshop_settings/add_storefront_identity');
            }
            if ($this->webshop_settings_model->storefront_identity_field_key_exists($section_type, $field_key)) {
                $this->session->set_flashdata('error', 'This section and field key already exists.');
                redirect('webshop_settings/add_storefront_identity');
            }

            $stored_value = $value;
            if (!empty($_FILES['media_file']['name'])) {
                $up = $this->do_upload('media_file', '', true);
                if ($up['status'] === 'success') {
                    $stored_value = 'webshop/' . $up['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($up['error']));
                    redirect('webshop_settings/add_storefront_identity');
                }
            }

            $insert = array(
                'section_type' => $section_type,
                'field_key'    => $field_key,
                'label'        => $label,
                'value'        => $stored_value,
                'icons'        => $icons === '' ? null : $icons,
                'sort_order'   => $sort_order,
                'is_active'    => $is_active,
            );

            if ($this->webshop_settings_model->insert_storefront_identity($insert)) {
                $this->session->set_flashdata('message', 'Storefront content row added.');
                redirect('webshop_settings/storefront_identity');
            }
            $this->session->set_flashdata('error', 'Could not save row.');
            redirect('webshop_settings/add_storefront_identity');
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('webshop_settings/storefront_identity'), 'page' => 'Storefront header & footer'),
            array('link' => '#', 'page' => 'Add row'),
        );
        $meta = array('page_title' => 'Add storefront row', 'bc' => $bc);
        $this->data['identity_row'] = null;
        $this->data['header_footer_schema_ready'] = $this->webshop_settings_model->header_footer_schema_ready();
        $this->page_construct('webshop_settings/storefront_identity_form', $meta, $this->data);
    }

    public function edit_storefront_identity($id = null) {
        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid row.');
            redirect('webshop_settings/storefront_identity');
        }

        $row = $this->webshop_settings_model->get_storefront_identity_by_id($id);
        if (!$row) {
            $this->session->set_flashdata('error', 'Row not found.');
            redirect('webshop_settings/storefront_identity');
        }

        if ($this->input->post('save_identity_row')) {
            if (!$this->webshop_settings_model->header_footer_schema_ready()) {
                $this->session->set_flashdata('error', 'Database tables are missing. Ensure <code>sma_cms_webshop_header_footer</code> exists, then retry.');
                redirect('webshop_settings/edit_storefront_identity/' . $id);
            }

            $section_type = strtolower(trim((string) $this->input->post('section_type')));
            $field_key    = strtolower(trim((string) (isset($row['field_key']) ? $row['field_key'] : '')));
            $label        = trim((string) $this->input->post('label'));
            $value        = (string) $this->input->post('value');
            $icons        = trim((string) $this->input->post('icons'));
            $sort_order   = (int) $this->input->post('sort_order');
            $is_active    = $this->input->post('is_active') ? 1 : 0;

            if ($section_type !== 'header' && $section_type !== 'footer') {
                $this->session->set_flashdata('error', 'Choose a valid section (Header or Footer).');
                redirect('webshop_settings/edit_storefront_identity/' . $id);
            }
            if ($field_key === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $field_key)) {
                $this->session->set_flashdata('error', 'Field key is required (letters, numbers, underscore only).');
                redirect('webshop_settings/edit_storefront_identity/' . $id);
            }
            if ($label === '') {
                $this->session->set_flashdata('error', 'Label is required.');
                redirect('webshop_settings/edit_storefront_identity/' . $id);
            }
            if ($this->webshop_settings_model->storefront_identity_field_key_exists($section_type, $field_key, $id)) {
                $this->session->set_flashdata('error', 'This section and field key already exists.');
                redirect('webshop_settings/edit_storefront_identity/' . $id);
            }

            $stored_value = $value;
            if (!empty($_FILES['media_file']['name'])) {
                $up = $this->do_upload('media_file', '', true);
                if ($up['status'] === 'success') {
                    $stored_value = 'webshop/' . $up['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($up['error']));
                    redirect('webshop_settings/edit_storefront_identity/' . $id);
                }
            }

            $update = array(
                'section_type' => $section_type,
                'field_key'    => $field_key,
                'label'        => $label,
                'value'        => $stored_value,
                'icons'        => $icons === '' ? null : $icons,
                'sort_order'   => $sort_order,
                'is_active'    => $is_active,
            );

            if ($this->webshop_settings_model->update_storefront_identity($id, $update)) {
                $this->session->set_flashdata('message', 'Storefront content updated.');
                redirect('webshop_settings/storefront_identity');
            }
            $this->session->set_flashdata('error', 'Could not update row.');
            redirect('webshop_settings/edit_storefront_identity/' . $id);
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => site_url('webshop_settings/storefront_identity'), 'page' => 'Storefront header & footer'),
            array('link' => '#', 'page' => 'Edit row'),
        );
        $meta = array('page_title' => 'Edit storefront row', 'bc' => $bc);
        $this->data['identity_row'] = $row;
        $this->data['header_footer_schema_ready'] = $this->webshop_settings_model->header_footer_schema_ready();
        $this->page_construct('webshop_settings/storefront_identity_form', $meta, $this->data);
    }

    public function delete_storefront_identity($id = null) {
        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid row.');
            redirect('webshop_settings/storefront_identity');
        }
        if ($this->webshop_settings_model->delete_storefront_identity($id)) {
            $this->session->set_flashdata('message', 'Row deleted.');
        } else {
            $this->session->set_flashdata('error', 'Could not delete row.');
        }
        redirect('webshop_settings/storefront_identity');
    }

    public function add_cms_page() {
        if ($this->input->post('create_cms_page')) {
            $name = trim((string) $this->input->post('page_name'));
            $url = '/' . ltrim(trim((string) $this->input->post('url')), '/');
            $status = trim((string) $this->input->post('status')) === 'published' ? 'published' : 'draft';

            if ($name === '' || $url === '/') {
                $this->session->set_flashdata('error', 'Please provide valid page details.');
                redirect('webshop_settings/add_cms_page');
            }

            if (!$this->cms_model->ensurePageMediaColumns()) {
                $this->session->set_flashdata('error', 'Failed to prepare CMS media columns in pages table.');
                redirect('webshop_settings/add_cms_page');
            }

            $insert_data = array(
                'page_name' => $name,
                'page_type' => 'static',
                'url'       => $url,
                'status'    => $status,
            );

            if ($this->cms_model->hasPageColumn('banner_image') && !empty($_FILES['banner_image']['name'])) {
                $banner_upload = $this->do_upload('banner_image', 'cms_pages');
                if ($banner_upload['status'] === 'success') {
                    $insert_data['banner_image'] = $banner_upload['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($banner_upload['error']));
                    redirect('webshop_settings/add_cms_page');
                }
            }
            if ($this->cms_model->hasPageColumn('logo_image') && !empty($_FILES['logo_image']['name'])) {
                $logo_upload = $this->do_upload('logo_image', 'cms_pages');
                if ($logo_upload['status'] === 'success') {
                    $insert_data['logo_image'] = $logo_upload['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($logo_upload['error']));
                    redirect('webshop_settings/add_cms_page');
                }
            }

            $new_id = $this->cms_model->addPage($insert_data);
            if ($new_id) {
                $this->session->set_flashdata('message', 'CMS page created successfully.');
                redirect('webshop_settings/edit_cms_page/' . (int) $new_id);
            }
            $this->session->set_flashdata('error', 'Failed to create CMS page.');
            redirect('webshop_settings/add_cms_page');
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => base_url('webshop_settings/cms_pages'), 'page' => lang('CMS Pages')),
            array('link' => '#', 'page' => lang('Add CMS Page'))
        );
        $meta = array('page_title' => lang('Add CMS Page'), 'bc' => $bc);
        $this->page_construct('webshop_settings/cms_page_add', $meta, $this->data);
    }

    public function delete_cms_page($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('webshop_settings/cms_pages');
        }
        $page_data = $this->cms_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('webshop_settings/cms_pages');
        }

        $this->cms_model->deletePageSectionsByPageId($page_id);
        if ($this->cms_model->deletePageById($page_id)) {
            $this->session->set_flashdata('message', 'CMS page deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete CMS page.');
        }
        redirect('webshop_settings/cms_pages');
    }

    public function remove_cms_page_media($page_id = null, $media_type = '') {
        $page_id = (int) $page_id;
        $media_type = strtolower(trim((string) $media_type));
        if ($page_id <= 0 || !in_array($media_type, array('banner', 'logo'), true)) {
            $this->session->set_flashdata('error', 'Invalid media remove request.');
            redirect('webshop_settings/cms_pages');
        }
        $page_data = $this->cms_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('webshop_settings/cms_pages');
        }
        $column = $media_type === 'banner' ? 'banner_image' : 'logo_image';
        if (!$this->cms_model->hasPageColumn($column)) {
            $this->session->set_flashdata('error', 'Media column not available.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }
        if ($this->cms_model->updatePageById($page_id, array($column => null))) {
            $this->session->set_flashdata('message', ucfirst($media_type) . ' removed successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to remove ' . $media_type . '.');
        }
        redirect('webshop_settings/edit_cms_page/' . $page_id);
    }

    /**
     * Entity tag mapping screen (product/category -> tags).
     */
    public function entity_tags() {
        redirect('entity_mapping');

        $selected_entity_master_id = (int) $this->input->get('entity_master_id');
        $selected_entity_id = (int) $this->input->get('entity_id');

        if ($this->input->post('save_entity_tags')) {
            $selected_entity_master_id = (int) $this->input->post('entity_master_id');
            $selected_entity_id = (int) $this->input->post('entity_id');
            $tag_values = (array) $this->input->post('tag_values');

            if ($selected_entity_master_id <= 0 || $selected_entity_id <= 0) {
                $this->session->set_flashdata('error', 'Please select Type and Entity.');
                redirect('webshop_settings/entity_tags');
            }

            $entity_master = $this->cms_model->getEntityMasterById($selected_entity_master_id);
            if (!$entity_master) {
                $this->session->set_flashdata('error', 'Invalid entity type.');
                redirect('webshop_settings/entity_tags');
            }

            $tags_master = $this->cms_model->getTagsMaster();
            $master_by_id = array();
            foreach ($tags_master as $tag) {
                $master_by_id[(int) $tag['id']] = $tag;
            }

            $saved = 0;
            foreach ($tag_values as $tag_id => $value) {
                $tag_id = (int) $tag_id;
                $value = trim((string) $value);
                if ($tag_id <= 0 || $value === '' || !isset($master_by_id[$tag_id])) {
                    continue;
                }
                $property_name = $master_by_id[$tag_id]['tag_name'];
                if ($this->cms_model->upsertEntityTagValue($selected_entity_master_id, $selected_entity_id, $tag_id, $property_name, $value)) {
                    $saved++;
                }
            }

            if ($saved > 0) {
                $this->session->set_flashdata('message', 'Entity tag values saved successfully.');
            } else {
                $this->session->set_flashdata('warning', 'No tag values were saved.');
            }
            redirect('webshop_settings/entity_tags?entity_master_id=' . $selected_entity_master_id . '&entity_id=' . $selected_entity_id);
        }

        $entity_masters = $this->cms_model->getEntityMasters();
        $entity_master = null;
        foreach ($entity_masters as $em) {
            if ((int) $em['id'] === $selected_entity_master_id) {
                $entity_master = $em;
                break;
            }
        }

        $entity_items = array();
        if ($entity_master && !empty($entity_master['entity_code'])) {
            $entity_items = $this->cms_model->getEntitiesByMasterCode($entity_master['entity_code']);
        }

        $entity_tags = array();
        if ($selected_entity_master_id > 0 && $selected_entity_id > 0) {
            $entity_tags = $this->cms_model->getEntityTagMappings($selected_entity_master_id, $selected_entity_id);
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => 'Entity Tags')
        );
        $meta = array('page_title' => 'Entity Tags', 'bc' => $bc);

        $this->data['entity_masters'] = $entity_masters;
        $this->data['entity_items'] = $entity_items;
        $this->data['selected_entity_master_id'] = $selected_entity_master_id;
        $this->data['selected_entity_id'] = $selected_entity_id;
        $this->data['tags_master'] = $this->cms_model->getTagsMaster();
        $this->data['entity_tags'] = $entity_tags;
        $this->page_construct('webshop_settings/entity_tags', $meta, $this->data);
    }

    /**
     * AJAX: entity options by entity master id.
     */
    public function entity_items_by_master() {
        $entity_master_id = (int) $this->input->get('entity_master_id');
        $entity_master = $this->cms_model->getEntityMasterById($entity_master_id);
        $items = array();
        if ($entity_master && !empty($entity_master['entity_code'])) {
            $items = $this->cms_model->getEntitiesByMasterCode($entity_master['entity_code']);
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'success',
                'items' => $items,
                'csrf_hash' => $this->security->get_csrf_hash(),
            )));
    }

    public function edit_cms_page($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('webshop_settings/cms_pages');
        }

        if ($this->input->post('save_cms_tags')) {
            $tag_values = $this->input->post('tag_values', false);
            if (!is_array($tag_values)) {
                $tag_values = array();
            }
            $tags_master = $this->cms_model->getTagsMaster();
            $master_by_id = array();
            foreach ($tags_master as $tag) {
                $master_by_id[(int) $tag['id']] = $tag;
            }

            $saved = 0;
            foreach ($tag_values as $tag_id => $value) {
                $tag_id = (int) $tag_id;
                $value = trim((string) $value);
                if ($tag_id <= 0 || $value === '' || !isset($master_by_id[$tag_id])) {
                    continue;
                }

                $property_name = $master_by_id[$tag_id]['tag_name'];
                if ($this->cms_model->upsertPageTagValue($page_id, $tag_id, $property_name, $value)) {
                    $saved++;
                }
            }

            if ($saved > 0) {
                $this->session->set_flashdata('message', 'Tag values saved successfully.');
            } else {
                $this->session->set_flashdata('warning', 'No tag values were saved.');
            }
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        if ($this->input->post('update_cms_page')) {
            $name = trim($this->input->post('page_name'));
            $url = '/' . ltrim(trim($this->input->post('url')), '/');
            $status = trim($this->input->post('status')) === 'published' ? 'published' : 'draft';

            if (!$this->cms_model->ensurePageMediaColumns()) {
                $this->session->set_flashdata('error', 'Failed to prepare CMS media columns in pages table.');
                redirect('webshop_settings/edit_cms_page/' . $page_id);
            }

            if ($name === '' || $url === '') {
                $this->session->set_flashdata('error', 'Please provide valid page details.');
                redirect('webshop_settings/edit_cms_page/' . $page_id);
            }

            $existing = $this->cms_model->getPageById($page_id);
            $page_type = is_array($existing) && isset($existing['page_type'])
                ? trim((string) $existing['page_type'])
                : 'static';
            if ($page_type === '') {
                $page_type = 'static';
            }

            $update_data = array(
                'page_name' => $name,
                'page_type' => $page_type,
                'url'       => $url,
                'status'    => $status,
            );

            if ($this->cms_model->hasPageColumn('banner_image')) {
                if (!empty($_FILES['banner_image']['name'])) {
                    $banner_upload = $this->do_upload('banner_image', 'cms_pages');
                    if ($banner_upload['status'] === 'success') {
                        $update_data['banner_image'] = $banner_upload['upload_data']['file_name'];
                    } else {
                        $this->session->set_flashdata('error', strip_tags($banner_upload['error']));
                        redirect('webshop_settings/edit_cms_page/' . $page_id);
                    }
                }
            }

            if ($this->cms_model->hasPageColumn('logo_image')) {
                if (!empty($_FILES['logo_image']['name'])) {
                    $logo_upload = $this->do_upload('logo_image', 'cms_pages');
                    if ($logo_upload['status'] === 'success') {
                        $update_data['logo_image'] = $logo_upload['upload_data']['file_name'];
                    } else {
                        $this->session->set_flashdata('error', strip_tags($logo_upload['error']));
                        redirect('webshop_settings/edit_cms_page/' . $page_id);
                    }
                }
            }

            if ($this->cms_model->updatePageById($page_id, $update_data)) {
                $this->session->set_flashdata('message', 'CMS page updated successfully.');
                redirect('webshop_settings/cms_pages');
            }

            $this->session->set_flashdata('error', 'Failed to update CMS page.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        $page_data = $this->cms_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('webshop_settings/cms_pages');
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => base_url('webshop_settings/cms_pages'), 'page' => lang('CMS Pages')),
            array('link' => '#', 'page' => lang('Edit CMS Page'))
        );
        $meta = array('page_title' => lang('Edit CMS Page'), 'bc' => $bc);

        $this->data['page_data'] = $page_data;
        // Make sure the Header/Footer rows exist before reading the dropdown source;
        // sections_master has UNIQUE(section_type) so this is a safe no-op when seeded.
        $this->cms_model->ensureHeaderFooterSectionMasters();
        $this->data['section_masters'] = $this->cms_model->getSectionMasters();
        $this->data['page_sections'] = $this->cms_model->getAdminPageSections($page_id);
        $this->data['tags_master'] = $this->cms_model->getTagsMaster();
        $this->data['page_tags'] = $this->cms_model->getPageTagMappings($page_id);
        $this->page_construct('webshop_settings/cms_page_edit', $meta, $this->data);
    }

    public function add_cms_page_section($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('webshop_settings/cms_pages');
        }

        $page_data = $this->cms_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('webshop_settings/cms_pages');
        }

        $section_id = (int) $this->input->post('section_id');
        $sort_order = (int) $this->input->post('sort_order');
        $is_enabled = (int) $this->input->post('is_enabled') === 1 ? 1 : 0;
        $section_heading = trim((string) $this->input->post('section_heading'));
        $show_header = $section_heading !== '' ? 'yes' : 'no';
        $this->load->helper('cms_layout');
        $page_text = trim(cms_read_html_field_from_post('page_text', $this));

        if ($section_id <= 0 || $sort_order <= 0) {
            $this->session->set_flashdata('error', 'Section and sort order are required.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        if ($this->cms_model->isPageSectionSortOrderExists($page_id, $sort_order)) {
            $sort_order = $this->cms_model->getNextPageSectionSortOrder($page_id);
        }
        $config_data = array(
            'show_header' => $show_header,
            'show_footer' => 'no',
            'show_banner' => 'no',
            'show_logo'   => 'no',
            'content'     => $page_text,
        );
        if ($section_heading !== '') {
            $config_data['title'] = $section_heading;
            $config_data['heading'] = $section_heading;
        }

        $section_contain = json_encode($config_data);

        $insert_data = array(
            'page_id'     => $page_id,
            'section_id'  => $section_id,
            'sort_order'  => $sort_order,
            'is_enabled'  => $is_enabled,
            'section_contain' => $section_contain,
        );
        $insert_data = array_merge($insert_data, $this->buildSectionVisibilityColumns($show_header, 'no', 'no', 'no'));

        if ($this->cms_model->addPageSection($insert_data)) {
            $this->session->set_flashdata('message', 'Dynamic section added successfully.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to add section. Sort order may already exist.');
        redirect('webshop_settings/edit_cms_page/' . $page_id);
    }

    public function update_cms_page_section($page_id = null, $mapping_id = null) {
        $page_id = (int) $page_id;
        $mapping_id = (int) $mapping_id;
        if ($page_id <= 0 || $mapping_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid page section.');
            redirect('webshop_settings/cms_pages');
        }

        $sort_order = (int) $this->input->post('sort_order');
        $is_enabled = (int) $this->input->post('is_enabled') === 1 ? 1 : 0;
        $section_heading = trim((string) $this->input->post('section_heading'));
        $show_header = $section_heading !== '' ? 'yes' : 'no';
        $this->load->helper('cms_layout');
        $page_text = trim(cms_read_html_field_from_post('page_text', $this));
        if ($sort_order <= 0) {
            $this->session->set_flashdata('error', 'Sort order is required.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        if ($this->cms_model->isPageSectionSortOrderExists($page_id, $sort_order, $mapping_id)) {
            $sort_order = $this->cms_model->getNextPageSectionSortOrder($page_id);
        }

        $config_data = array(
            'show_header' => $show_header,
            'show_footer' => 'no',
            'show_banner' => 'no',
            'show_logo'   => 'no',
            'content'     => $page_text,
        );
        if ($section_heading !== '') {
            $config_data['title'] = $section_heading;
            $config_data['heading'] = $section_heading;
        }

        $update_data = array(
            'sort_order' => $sort_order,
            'is_enabled' => $is_enabled,
            'section_contain' => json_encode($config_data),
        );
        $update_data = array_merge($update_data, $this->buildSectionVisibilityColumns($show_header, 'no', 'no', 'no'));

        if ($this->cms_model->updatePageSectionById($mapping_id, $page_id, $update_data)) {
            $this->session->set_flashdata('message', 'Section updated successfully.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to update section.');
        redirect('webshop_settings/edit_cms_page/' . $page_id);
    }

    public function delete_cms_page_section($page_id = null, $mapping_id = null) {
        $page_id = (int) $page_id;
        $mapping_id = (int) $mapping_id;
        if ($page_id <= 0 || $mapping_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid page section.');
            redirect('webshop_settings/cms_pages');
        }

        if ($this->cms_model->deletePageSectionById($mapping_id, $page_id)) {
            $this->session->set_flashdata('message', 'Section deleted successfully.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to delete section.');
        redirect('webshop_settings/edit_cms_page/' . $page_id);
    }

    private function normalizeYesNo($value) {
        $value = strtolower(trim((string) $value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true) ? 'yes' : 'no';
    }

    private function buildSectionVisibilityColumns($show_header, $show_footer, $show_banner, $show_logo) {
        $data = array();

        if ($this->cms_model->hasPageSectionColumn('header')) {
            $data['header'] = $show_header;
        }
        if ($this->cms_model->hasPageSectionColumn('footer')) {
            $data['footer'] = $show_footer;
        }
        if ($this->cms_model->hasPageSectionColumn('banner')) {
            $data['banner'] = $show_banner;
        }
        if ($this->cms_model->hasPageSectionColumn('logo')) {
            $data['logo'] = $show_logo;
        }

        if ($this->cms_model->hasPageSectionColumn('show_header')) {
            $data['show_header'] = $show_header;
        }
        if ($this->cms_model->hasPageSectionColumn('show_footer')) {
            $data['show_footer'] = $show_footer;
        }
        if ($this->cms_model->hasPageSectionColumn('show_banner')) {
            $data['show_banner'] = $show_banner;
        }
        if ($this->cms_model->hasPageSectionColumn('show_logo')) {
            $data['show_logo'] = $show_logo;
        }

        return $data;
    }

    public function reorder_cms_page_sections($page_id = null) {
        $this->output->set_content_type('application/json');

        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid page.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $raw_orders = $this->input->post('orders');

        // The JS sends orders as JSON.stringify({}), so it arrives as a JSON string.
        // Gracefully handle both a plain string and an already-decoded array.
        if (is_string($raw_orders) && $raw_orders !== '') {
            $decoded = json_decode($raw_orders, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw_orders = $decoded;
            }
        }

        if (!is_array($raw_orders) || empty($raw_orders)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'No sort order data received.',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $mapping_orders = array();
        foreach ($raw_orders as $mapping_id => $sort_order) {
            $mapping_id = (int) $mapping_id;
            $sort_order = (int) $sort_order;
            if ($mapping_id > 0 && $sort_order > 0) {
                $mapping_orders[$mapping_id] = $sort_order;
            }
        }

        if (empty($mapping_orders)) {
            echo json_encode(array(
                'status'    => 'fail',
                'message'   => 'Invalid sort order data (all entries filtered).',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ));
            return;
        }

        $updated = $this->cms_model->updatePageSectionSortOrders($page_id, $mapping_orders);
        echo json_encode(array(
            'status'    => $updated ? 'success' : 'fail',
            'message'   => $updated ? 'Sort order updated.' : 'Failed to update sort order in database.',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ));
    }

    public function add_cms_page_tag($page_id = null) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            $this->session->set_flashdata('error', 'Invalid CMS page.');
            redirect('webshop_settings/cms_pages');
        }

        $page_data = $this->cms_model->getPageById($page_id);
        if (!$page_data) {
            $this->session->set_flashdata('error', 'CMS page not found.');
            redirect('webshop_settings/cms_pages');
        }

        $tag_id = (int) $this->input->post('tag_id');
        $property_name = trim((string) $this->input->post('property_name'));
        $value = trim((string) $this->input->post('value'));

        if ($tag_id <= 0 || $property_name === '' || $value === '') {
            $this->session->set_flashdata('error', 'Tag, property name and value are required.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        if ($this->cms_model->upsertPageTagValue($page_id, $tag_id, $property_name, $value)) {
            $this->session->set_flashdata('message', 'Tag value saved successfully.');
            redirect('webshop_settings/edit_cms_page/' . $page_id);
        }

        $this->session->set_flashdata('error', 'Failed to save tag value.');
        redirect('webshop_settings/edit_cms_page/' . $page_id);
    }

}
//End class
