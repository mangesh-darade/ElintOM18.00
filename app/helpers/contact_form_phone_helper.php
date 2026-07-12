<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('contact_form_phone_picker_caret_svg')) {
    /**
     * @return string
     */
    function contact_form_phone_picker_caret_svg()
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . '<path d="M6 9l6 6 6-6"/></svg>';
    }
}

if (!function_exists('contact_form_phone_name_to_iso_map')) {
    /**
     * @return array<string, string> lowercase name => iso2
     */
    function contact_form_phone_name_to_iso_map()
    {
        $path = APPPATH . 'helpers/contact_form_phone_iso_data.php';
        if (is_file($path)) {
            $data = include $path;
            if (is_array($data)) {
                return $data;
            }
        }

        return array();
    }
}

if (!function_exists('contact_form_phone_iso_from_name')) {
    /**
     * @param string $name
     * @return string Two-letter ISO code or empty
     */
    function contact_form_phone_iso_from_name($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }
        $map = contact_form_phone_name_to_iso_map();
        $key = strtolower($name);
        if (isset($map[$key])) {
            return (string) $map[$key];
        }
        foreach ($map as $countryName => $iso) {
            if ($countryName === $key) {
                return (string) $iso;
            }
        }
        if (stripos($name, 'united states') !== false || $name === 'USA' || $name === 'US') {
            return 'us';
        }
        if (stripos($name, 'united kingdom') !== false || $name === 'UK') {
            return 'gb';
        }
        if (stripos($name, 'uae') !== false || stripos($name, 'emirates') !== false) {
            return 'ae';
        }

        return '';
    }
}

if (!function_exists('contact_form_phone_flag_img_url')) {
    /**
     * @param string $iso2
     * @return string
     */
    function contact_form_phone_flag_img_url($iso2)
    {
        $iso2 = strtolower(substr(preg_replace('/[^A-Za-z]/', '', (string) $iso2), 0, 2));
        if (strlen($iso2) !== 2) {
            return 'data:image/svg+xml,' . rawurlencode(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8">'
                . '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18"/><path d="M12 3a14 14 0 0 0 0 18"/>'
                . '</svg>'
            );
        }

        return 'https://flagcdn.com/w40/' . $iso2 . '.png';
    }
}

if (!function_exists('contact_form_phone_countries')) {
    /**
     * Country rows for phone fields (flag + dial code).
     *
     * @return array<int, array<string, mixed>>
     */
    function contact_form_phone_countries()
    {
        $CI =& get_instance();
        $rows = array();
        if ($CI && isset($CI->db)) {
            $q = $CI->db->select('id, name, code, phone_digits')
                ->order_by('name', 'ASC')
                ->get('country_master');
            if ($q && $q->num_rows() > 0) {
                $rows = $q->result_array();
            }
        }
        if (empty($rows)) {
            $rows = array(
                array('id' => 1, 'name' => 'India', 'code' => '+91', 'phone_digits' => 10),
                array('id' => 2, 'name' => 'United States', 'code' => '+1', 'phone_digits' => 10),
                array('id' => 3, 'name' => 'United Arab Emirates', 'code' => '+971', 'phone_digits' => 9),
            );
        }

        $out = array();
        foreach ($rows as $row) {
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            $code = isset($row['code']) ? trim((string) $row['code']) : '';
            if ($name === '' || $code === '') {
                continue;
            }
            if ($code[0] !== '+') {
                $code = '+' . ltrim($code, '+');
            }
            $iso = contact_form_phone_iso_from_name($name);
            $out[] = array(
                'id'            => (int) (isset($row['id']) ? $row['id'] : 0),
                'name'          => $name,
                'code'          => $code,
                'phone_digits'  => (int) (isset($row['phone_digits']) ? $row['phone_digits'] : 0),
                'iso'           => $iso,
                'flag_img'      => contact_form_phone_flag_img_url($iso),
                'option_value'  => $code . '~' . (int) (isset($row['id']) ? $row['id'] : 0),
            );
        }

        return $out;
    }
}

if (!function_exists('contact_form_country_list')) {
    require_once APPPATH . 'helpers/contact_form_country_helper.php';
}

if (!function_exists('contact_form_phone_default_country_value')) {
    /**
     * @param array<int, array<string, mixed>> $countries
     * @return string option_value (+code~id)
     */
    function contact_form_phone_default_country_value(array $countries)
    {
        $preferred = array('India', 'United States', 'United Arab Emirates');
        $CI =& get_instance();
        if ($CI && isset($CI->Settings) && is_object($CI->Settings) && !empty($CI->Settings->default_biller)
            && isset($CI->site) && is_object($CI->site) && method_exists($CI->site, 'getCompanyByID')) {
            $biller = $CI->site->getCompanyByID((int) $CI->Settings->default_biller);
            if ($biller && !empty($biller->country)) {
                array_unshift($preferred, (string) $biller->country);
            }
        }
        foreach ($preferred as $prefName) {
            foreach ($countries as $c) {
                if (isset($c['name']) && strcasecmp((string) $c['name'], $prefName) === 0 && !empty($c['option_value'])) {
                    return (string) $c['option_value'];
                }
            }
        }

        return !empty($countries[0]['option_value']) ? (string) $countries[0]['option_value'] : '';
    }
}

if (!function_exists('contact_form_phone_country_by_option_value')) {
    /**
     * @param array<int, array<string, mixed>> $countries
     * @param string                           $optionValue
     * @return array<string, mixed>|null
     */
    function contact_form_phone_country_by_option_value(array $countries, $optionValue)
    {
        $optionValue = trim((string) $optionValue);
        if ($optionValue === '') {
            return null;
        }
        foreach ($countries as $c) {
            if (isset($c['option_value']) && (string) $c['option_value'] === $optionValue) {
                return $c;
            }
        }

        return null;
    }
}

if (!function_exists('contact_form_phone_render_country_select')) {
    /**
     * @param string                           $fieldName
     * @param array<int, array<string, mixed>> $countries
     * @param string                           $selectedValue
     * @param array<string, mixed>             $attrs
     * @return string
     */
    function contact_form_phone_render_country_select($fieldName, array $countries, $selectedValue = '', array $attrs = array())
    {
        $fieldName = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $fieldName));
        if ($fieldName === '') {
            return '';
        }
        if ($selectedValue === '') {
            $selectedValue = contact_form_phone_default_country_value($countries);
        }
        $id = isset($attrs['id']) ? (string) $attrs['id'] : 'cf_country_' . $fieldName;
        $disabled = !empty($attrs['disabled']);
        $required = !empty($attrs['required']);
        $class = isset($attrs['class']) ? (string) $attrs['class'] : 'cf-phone-picker';

        $selected = null;
        foreach ($countries as $c) {
            if (isset($c['option_value']) && (string) $c['option_value'] === (string) $selectedValue) {
                $selected = $c;
                break;
            }
        }
        if (!$selected && !empty($countries)) {
            $selected = $countries[0];
            $selectedValue = isset($selected['option_value']) ? (string) $selected['option_value'] : '';
        }

        $disAttr = $disabled ? ' data-disabled="1"' : '';
        $reqAttr = $required ? ' required' : '';
        $selIso = $selected && !empty($selected['iso']) ? (string) $selected['iso'] : '';
        $selCode = $selected && !empty($selected['code']) ? (string) $selected['code'] : '';
        $selFlag = $selected && !empty($selected['flag_img']) ? (string) $selected['flag_img'] : contact_form_phone_flag_img_url('');
        $selDigits = $selected && !empty($selected['phone_digits']) ? (int) $selected['phone_digits'] : 0;

        $html = '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' . $disAttr . '>';
        $html .= '<input type="hidden" class="cf-phone-country-value" name="' . htmlspecialchars($fieldName . '_country', ENT_QUOTES, 'UTF-8') . '"'
            . ' value="' . htmlspecialchars((string) $selectedValue, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-digits="' . $selDigits . '"'
            . ' data-code="' . htmlspecialchars($selCode, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-iso="' . htmlspecialchars($selIso, ENT_QUOTES, 'UTF-8') . '"' . $reqAttr . '>';
        $html .= '<button type="button" class="cf-phone-picker-trigger" aria-haspopup="listbox" aria-expanded="false"'
            . ($disabled ? ' disabled' : '') . '>';
        $html .= '<img class="cf-phone-picker-flag" src="' . htmlspecialchars($selFlag, ENT_QUOTES, 'UTF-8') . '" width="22" height="16" alt="" loading="lazy" decoding="async">';
        $html .= '<span class="cf-phone-picker-code">' . htmlspecialchars($selCode, ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '<span class="cf-phone-picker-caret" aria-hidden="true">' . contact_form_phone_picker_caret_svg() . '</span>';
        $html .= '</button>';
        $html .= '<div class="cf-phone-picker-panel" role="listbox" hidden>';
        $html .= '<ul class="cf-phone-picker-list">';
        foreach ($countries as $c) {
            $val = isset($c['option_value']) ? (string) $c['option_value'] : '';
            if ($val === '') {
                continue;
            }
            $iso = isset($c['iso']) ? (string) $c['iso'] : '';
            $code = isset($c['code']) ? (string) $c['code'] : '';
            $cname = isset($c['name']) ? (string) $c['name'] : '';
            $flag = isset($c['flag_img']) ? (string) $c['flag_img'] : contact_form_phone_flag_img_url($iso);
            $digits = isset($c['phone_digits']) ? (int) $c['phone_digits'] : 0;
            $isSel = ($val === (string) $selectedValue) ? ' is-selected' : '';
            $html .= '<li class="cf-phone-picker-item' . $isSel . '" role="option" tabindex="-1"'
                . ' data-value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-digits="' . $digits . '"'
                . ' data-code="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-iso="' . htmlspecialchars($iso, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-flag="' . htmlspecialchars($flag, ENT_QUOTES, 'UTF-8') . '">';
            $html .= '<img class="cf-phone-picker-flag" src="' . htmlspecialchars($flag, ENT_QUOTES, 'UTF-8') . '" width="22" height="16" alt="" loading="lazy" decoding="async">';
            $html .= '<span class="cf-phone-picker-item-name">' . htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') . '</span>';
            $html .= '<span class="cf-phone-picker-item-code">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul></div></div>';

        return $html;
    }
}

if (!function_exists('contact_form_phone_render_field')) {
    /**
     * Phone input with country code + flag selector.
     *
     * @param array<string, mixed>             $field
     * @param array<int, array<string, mixed>> $countries
     * @param array<string, mixed>             $opts
     * @return string
     */
    function contact_form_phone_render_field(array $field, array $countries, array $opts = array())
    {
        $name = isset($field['name']) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $field['name'])) : '';
        if ($name === '') {
            return '';
        }
        $placeholder = isset($field['placeholder']) ? (string) $field['placeholder'] : 'Phone number';
        $required = !empty($field['required']);
        $disabled = !empty($opts['disabled']);
        $idPrefix = isset($opts['id_prefix']) ? (string) $opts['id_prefix'] : 'ef_';
        $inputClass = isset($opts['input_class']) ? (string) $opts['input_class'] : 'cf-phone-number';
        $fieldId = $idPrefix . $name;
        $countryId = $idPrefix . 'country_' . $name;
        $selectedCountry = isset($opts['country_value']) ? (string) $opts['country_value'] : '';
        $value = isset($opts['value']) ? (string) $opts['value'] : '';
        $reqAttr = $required ? ' required' : '';
        $disAttr = $disabled ? ' disabled' : '';

        $html = '<div class="cf-phone-field">';
        $html .= '<div class="cf-phone-country-wrap">';
        $html .= contact_form_phone_render_country_select($name, $countries, $selectedCountry, array(
            'id'       => $countryId,
            'disabled' => $disabled,
            'required' => $required,
        ));
        $html .= '</div>';
        $html .= '<input type="tel" class="' . htmlspecialchars($inputClass, ENT_QUOTES, 'UTF-8') . '"'
            . ' id="' . htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8') . '"'
            . ' name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"'
            . ' value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"'
            . ' inputmode="tel" autocomplete="tel-national"'
            . ($placeholder !== '' ? ' placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"' : '')
            . ' pattern="[0-9\\s\\-]{6,15}" title="Enter digits only (no country code)"'
            . $reqAttr . $disAttr . '>';
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('contact_form_phone_merge_submitted')) {
    /**
     * @param string $nationalNumber
     * @param string $countryOptionValue +code~id
     * @param array<int, array<string, mixed>> $countries
     * @return string
     */
    function contact_form_phone_merge_submitted($nationalNumber, $countryOptionValue, array $countries)
    {
        $nationalNumber = preg_replace('/\D+/', '', (string) $nationalNumber);
        $country = contact_form_phone_country_by_option_value($countries, $countryOptionValue);
        if (!$country || $nationalNumber === '') {
            return $nationalNumber;
        }
        $dial = isset($country['code']) ? preg_replace('/\D+/', '', (string) $country['code']) : '';
        if ($dial === '') {
            return $nationalNumber;
        }
        if (strpos($nationalNumber, $dial) === 0) {
            return $nationalNumber;
        }

        return $dial . $nationalNumber;
    }
}

if (!function_exists('contact_form_phone_validate_national')) {
    /**
     * @param string                           $nationalNumber
     * @param string                           $countryOptionValue
     * @param array<int, array<string, mixed>> $countries
     * @return bool
     */
    function contact_form_phone_validate_national($nationalNumber, $countryOptionValue, array $countries)
    {
        $digits = preg_replace('/\D+/', '', (string) $nationalNumber);
        if ($digits === '') {
            return false;
        }
        $country = contact_form_phone_country_by_option_value($countries, $countryOptionValue);
        $expected = $country && !empty($country['phone_digits']) ? (int) $country['phone_digits'] : 0;
        if ($expected > 0) {
            return strlen($digits) === $expected;
        }

        return strlen($digits) >= 6 && strlen($digits) <= 15;
    }
}
