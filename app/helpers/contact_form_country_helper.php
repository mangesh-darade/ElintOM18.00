<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('contact_form_country_list')) {
    /**
     * Country rows for country dropdown fields (from country_master).
     *
     * @return array<int, array{value:string,label:string,id:int}>
     */
    function contact_form_country_list()
    {
        $CI =& get_instance();
        $rows = array();
        if ($CI && isset($CI->db)) {
            $q = $CI->db->select('id, name, code')
                ->order_by('name', 'ASC')
                ->get('country_master');
            if ($q && $q->num_rows() > 0) {
                $rows = $q->result_array();
            }
        }
        if (empty($rows) && function_exists('contact_form_phone_countries')) {
            foreach (contact_form_phone_countries() as $phoneRow) {
                if (!is_array($phoneRow)) {
                    continue;
                }
                $rows[] = array(
                    'id'   => isset($phoneRow['id']) ? (int) $phoneRow['id'] : 0,
                    'name' => isset($phoneRow['name']) ? (string) $phoneRow['name'] : '',
                    'code' => isset($phoneRow['code']) ? (string) $phoneRow['code'] : '',
                );
            }
        }
        if (empty($rows)) {
            $rows = array(
                array('id' => 1, 'name' => 'India', 'code' => '+91'),
                array('id' => 2, 'name' => 'United States', 'code' => '+1'),
                array('id' => 3, 'name' => 'United Arab Emirates', 'code' => '+971'),
            );
        }

        $out = array();
        $seen = array();
        foreach ($rows as $row) {
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            if ($name === '' || isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $out[] = array(
                'value' => $name,
                'label' => $name,
                'id'    => (int) (isset($row['id']) ? $row['id'] : 0),
            );
        }

        return $out;
    }
}
