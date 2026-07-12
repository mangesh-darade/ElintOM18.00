<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cms_seo {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Generate HTML meta tags from CMS data
     */
    public function generate_meta_tags($tags, $data = []) {
        $html = '';
        foreach ($tags as $tag) {
            $template = $tag['template'];
            $impl_code = $tag['implementation_code'];
            $value = $tag['value'];
            $property_name = $tag['property_name'];

            // Replace placeholders in value if dynamic
            if (isset($tag['is_dynamic']) && $tag['is_dynamic']) {
                $value = $this->parse_placeholders($value, $data);
            }

            $tag_html = $impl_code;
            
            // Replace standard placeholders
            $tag_html = str_replace('{value}', $value, $tag_html);
            $tag_html = str_replace('{' . $property_name . '}', $value, $tag_html);
            
            // Handle Schema JSON specifically
            if ($tag['tag_type'] == 'schema') {
                $schema_json = $this->parse_placeholders($template, $data);
                $tag_html = str_replace('{schema_json}', $schema_json, $tag_html);
            }

            // General placeholder replacement in the final HTML
            $tag_html = $this->parse_placeholders($tag_html, $data);

            $html .= $tag_html . "\n";
        }
        return $html;
    }

    /**
     * Parse placeholders like {page_title} in text
     */
    private function parse_placeholders($text, $data) {
        if (empty($text)) return '';
        
        foreach ($data as $key => $val) {
            if (is_scalar($val)) {
                $text = str_replace('{' . $key . '}', $val, $text);
            }
        }
        
        // Remove remaining placeholders
        $text = preg_replace('/\{[a-zA-Z0-9_]+\}/', '', $text);
        
        return $text;
    }
}
