<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Lang extends CI_Lang {

    function __construct() {
        parent::__construct();
    }

    private $_in_line = false;

    function line($line, $params = null)
    {
        ///////////////////////////////////// POS type labels /////////////////////////////////////

        if (!$this->_in_line) {
            $this->_in_line = true;

            $ci = get_instance();

            // Only attempt DB lookup if everything is fully ready
            if (
                isset($ci->Settings) &&
                is_object($ci->Settings) &&
                isset($ci->Settings->pos_type) &&
                $ci->Settings->pos_type !== '' &&
                isset($ci->db) &&
                is_object($ci->db) &&
                $ci->db->conn_id !== false  // DB connection is actually open
            ) {
                if (!isset($ci->pos_type_labels_model)) {
                    $ci->load->model('pos_type_labels_model', 'pos_type_labels_model');
                }

                if (isset($ci->pos_type_labels_model)) {
                    $custom = $ci->pos_type_labels_model->get_label($ci->Settings->pos_type, $line);
                    if ($custom !== null && $custom !== '') {
                        $custom = preg_replace('/^@\s*-[0-9,]+\s*\+[0-9,]+\s*@@\s*/', '', $custom);
                        if ($custom !== '') {
                            $this->_in_line = false;
                            return is_null($params) ? $custom : $this->_ni_line($custom, $params);
                        }
                    }
                }
            }

            $this->_in_line = false;
        }

        ///////////////////////////////////// POS type labels /////////////////////////////////////

        $return = parent::line($line);
        if ($return === false) {
            return str_replace('_', ' ', $line);
        } else {
            if (!is_null($params)) {
                $return = $this->_ni_line($return, $params);
            }
            return $return;
        }
    }

    private function _ni_line($str, $params)
    {
        $return = $str;
        $params = is_array($params) ? $params : array($params);
        $search = array();
        $cnt = 1;
        foreach ($params as $param) {
            $search[$cnt] = "/\\${$cnt}/";
            $cnt++;
        }
        unset($search[0]);
        $return = preg_replace($search, $params, $return);
        return $return;
    }
}