<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('settings_screen_name_column_map')) {

    /**
     * Menu label (settings_screen_enabled key) => sma_settings_screens column.
     *
     * @return array
     */
    function settings_screen_name_column_map()
    {
        return array(
            'System Settings' => 'system_settings',
            'POS Settings' => 'pos_settings',
            'Custom Fields' => 'custom_fields',
            'Menu Labels' => 'menu_labels',
            'Change Logo' => 'change_logo',
            'Currencies' => 'currencies',
            'Customer Groups' => 'customer_groups',
            'Price Groups' => 'price_groups',
            'Restaurant Tables' => 'restaurant_tables',
            'Table Price Groups' => 'table_price_groups',
            'Categories' => 'categories',
            'Expense Categories' => 'expense_categories',
            'Units' => 'units',
            'Brands' => 'brands',
            'Variants' => 'variants',
            'Manage Variants' => 'manage_variants',
            'Tax Rates' => 'tax_rates',
            'Tax Rates Attributes' => 'tax_rates_attributes',
            'Locations' => 'locations',
            'Email Templates' => 'email_templates',
            'Group Permissions' => 'group_permissions',
            'Backups' => 'backups',
            'Discount Coupon' => 'discount_coupon',
            'Offer' => 'offer',
            'Offer Category' => 'offer_category',
            'SMS Config' => 'sms_config',
            'Manage Printers Option' => 'manage_printers_option',
            'Wifi Printer Setting' => 'wifi_printer_setting',
        );
    }
}

if (!function_exists('settings_screens_flags')) {

    /**
     * Cached map of screen label => active (0|1) for the logged-in user's group_id.
     *
     * @return array
     */
    function settings_screens_flags()
    {
        static $flags = null;
        if ($flags !== null) {
            return $flags;
        }
        $flags = array();
        foreach (settings_screen_name_column_map() as $name => $column) {
            $flags[$name] = 0;
        }

        $CI = &get_instance();
        if (!isset($CI->db) || !$CI->db->table_exists('settings_screens')) {
            return $flags;
        }

        $group_id = (int) $CI->session->userdata('group_id');
        if (!$group_id) {
            return $flags;
        }

        $q = $CI->db->get_where('settings_screens', array('group_id' => $group_id), 1);
        if ($q && $q->num_rows() > 0) {
            $row = $q->row();
            foreach (settings_screen_name_column_map() as $name => $column) {
                $raw = isset($row->$column) ? $row->$column : 0;
                $flags[$name] = ((int) $raw) === 1 ? 1 : 0;
            }
        }
        return $flags;
    }
}

if (!function_exists('settings_screen_enabled')) {

    /**
     * Whether a settings UI screen should be shown (matches database screen_name).
     * If the registry is empty, all screens are shown (backward compatibility).
     * If screen_name is missing from the registry, the screen is shown.
     *
     * @param array  $flags        from settings_screens_flags()
     * @param string $screen_name  sma_settings_screens.screen_name
     * @return bool
     */
    function settings_screen_enabled(array $flags, $screen_name)
    {
        $screen_name = trim((string) $screen_name);
        if (empty($flags)) {
            return true;
        }
        if (!array_key_exists($screen_name, $flags)) {
            return true;
        }
        return $flags[$screen_name] === 1;
    }
}

if (!function_exists('settings_parent_module_enabled')) {

    /**
     * Whether the Settings sidebar block should be allowed from DB toggles for this group.
     *
     * @param array $flags from settings_screens_flags()
     * @return bool
     */
    function settings_parent_module_enabled(array $flags)
    {
        if (empty($flags)) {
            return true;
        }
        foreach ($flags as $active) {
            if ((int) $active === 1) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('settings_user_menu_has_any_visible_item')) {

    /**
     * True if at least one settings submenu would render from sma_settings_screens + app context.
     * Manage Printers / Wifi: when $user_printer (GP printer-setting) and screen enabled.
     * Other screens: when enabled in sma_settings_screens for the user's group.
     *
     * @param array   $flags
     * @param bool    $user_printer   GP printer-setting
     * @param object  $settings       $Settings (pos_type, etc.)
     * @return bool
     */
    function settings_user_menu_has_any_visible_item(array $flags, $user_printer, $settings)
    {
        $pos = defined('POS') && constant('POS');
        $restaurant = isset($settings->pos_type) && $settings->pos_type == 'restaurant';
        $has_pos_type = !empty($settings->pos_type);

        $items = array(
            array('System Settings', true),
            array('POS Settings', $pos),
            array('Custom Fields', true),
            array('Menu Labels', $has_pos_type),
            array('Change Logo', true),
            array('Currencies', true),
            array('Customer Groups', true),
            array('Price Groups', true),
            array('Restaurant Tables', $restaurant),
            array('Table Price Groups', $restaurant),
            array('Categories', true),
            array('Expense Categories', true),
            array('Units', true),
            array('Brands', true),
            array('Variants', true),
            array('Manage Variants', true),
            array('Tax Rates', true),
            array('Tax Rates Attributes', true),
            array('Locations', true),
            array('Email Templates', true),
            array('Group Permissions', true),
            array('Backups', true),
            array('Discount Coupon', true),
            array('Offer', true),
            array('Offer Category', true),
            array('SMS Config', true),
            array('Manage Printers Option', true),
            array('Wifi Printer Setting', true),
        );
        foreach ($items as $it) {
            $screen = $it[0];
            $cond = $it[1];
            if ($screen === 'Manage Printers Option' || $screen === 'Wifi Printer Setting') {
                if (!$user_printer) {
                    continue;
                }
            }
            if ($cond && settings_screen_enabled($flags, $screen)) {
                return true;
            }
        }
        return false;
    }
}
