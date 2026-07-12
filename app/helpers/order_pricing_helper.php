<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Order line pricing helpers (zero unit_price / use MRP or net_price).
 */

if (!function_exists('order_pricing_item_qty')) {
    function order_pricing_item_qty(array $item) {
        $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
        if ($qty <= 0) {
            $qty = isset($item['unit_quantity']) ? (float) $item['unit_quantity'] : 1;
        }
        return $qty > 0 ? $qty : 1;
    }
}

if (!function_exists('order_pricing_unit_price')) {
    function order_pricing_unit_price(array $item) {
        foreach (array('unit_price', 'net_unit_price', 'invoice_unit_price', 'real_unit_price') as $k) {
            if (isset($item[$k]) && (float) $item[$k] > 0) {
                return (float) $item[$k];
            }
        }
        $qty = order_pricing_item_qty($item);
        if (isset($item['subtotal']) && (float) $item['subtotal'] > 0) {
            return (float) $item['subtotal'] / $qty;
        }
        if (isset($item['net_price']) && (float) $item['net_price'] > 0) {
            return (float) $item['net_price'] / $qty;
        }
        if (isset($item['mrp']) && (float) $item['mrp'] > 0) {
            return (float) $item['mrp'];
        }
        return 0;
    }
}

if (!function_exists('order_pricing_line_total')) {
    function order_pricing_line_total(array $item) {
        if (isset($item['subtotal']) && (float) $item['subtotal'] > 0) {
            return (float) $item['subtotal'];
        }
        if (isset($item['net_price']) && (float) $item['net_price'] > 0) {
            return (float) $item['net_price'];
        }
        return order_pricing_unit_price($item) * order_pricing_item_qty($item);
    }
}

if (!function_exists('order_pricing_normalize_item_row')) {
    function order_pricing_normalize_item_row(array $item) {
        $oid = 0;
        foreach (array('option_id', 'variant_id', 'product_option_id') as $k) {
            if (isset($item[$k]) && (int) $item[$k] > 0) {
                $oid = (int) $item[$k];
                break;
            }
        }
        $item['option_id'] = $oid;

        $qty = order_pricing_item_qty($item);
        $unit = order_pricing_unit_price($item);
        $line = order_pricing_line_total($item);

        if ($unit > 0) {
            $item['unit_price'] = $unit;
            if (empty($item['net_unit_price']) || (float) $item['net_unit_price'] <= 0) {
                $item['net_unit_price'] = $unit;
            }
        }
        if ($line > 0) {
            $item['subtotal'] = $line;
        }
        foreach (array('variant_id', 'variant_price', 'product_option_id') as $drop) {
            unset($item[$drop]);
        }
        return $item;
    }
}

if (!function_exists('order_pricing_sum_lines')) {
    function order_pricing_sum_lines($items) {
        $sum = 0;
        foreach ($items as $item) {
            $row = is_array($item) ? $item : (array) $item;
            $sum += order_pricing_line_total(order_pricing_normalize_item_row($row));
        }
        return $sum;
    }
}

if (!function_exists('order_pricing_apply_order_totals')) {
    /**
     * @param array $order
     * @param array $items
     * @return array{order: array, items: array}
     */
    function order_pricing_apply_order_totals(array $order, array $items) {
        $line_sum = order_pricing_sum_lines($items);
        if ($line_sum > 0) {
            if (empty($order['total']) || (float) $order['total'] <= 0) {
                $order['total'] = $line_sum;
            }
            if (empty($order['grand_total']) || (float) $order['grand_total'] <= 0) {
                $shipping = isset($order['shipping']) ? (float) $order['shipping'] : 0;
                $tax = isset($order['total_tax']) ? (float) $order['total_tax'] : 0;
                if (isset($order['product_tax']) && (float) $order['product_tax'] > 0) {
                    $tax = (float) $order['product_tax'];
                }
                $order['grand_total'] = $line_sum + $shipping + $tax;
            }
        }
        return array('order' => $order, 'items' => $items);
    }
}

if (!function_exists('order_pricing_display_total')) {
    function order_pricing_display_total($order, $items) {
        $order = is_array($order) ? $order : (array) $order;
        if (isset($order['grand_total']) && (float) $order['grand_total'] > 0) {
            return (float) $order['grand_total'];
        }
        if (isset($order['total']) && (float) $order['total'] > 0) {
            return (float) $order['total'];
        }
        return order_pricing_sum_lines($items);
    }
}

if (!function_exists('order_pricing_items_label_list')) {
    function order_pricing_items_label_list($items) {
        $list = array();
        foreach ($items as $item) {
            $row = is_object($item) ? $item : (object) $item;
            $name = isset($row->product_name) ? (string) $row->product_name : 'Item';
            $qty = isset($row->quantity) ? (float) $row->quantity : 1;
            $list[] = $name . ($qty > 1 ? '(' . (int) $qty . ')' : '');
        }
        return $list;
    }
}

if (!function_exists('order_db_table_fields')) {
    /**
     * Cached column list for orders / order_items (avoids unknown-column insert failures).
     *
     * @param object $db CI DB driver
     * @param string $table Logical table name without prefix
     * @return array<string,bool>
     */
    function order_db_table_fields($db, $table) {
        static $cache = array();
        $table = trim((string) $table);
        if ($table === '') {
            return array();
        }
        if (!isset($cache[$table])) {
            $fields = array();
            if (is_object($db) && method_exists($db, 'list_fields') && method_exists($db, 'dbprefix')) {
                $qname = $db->dbprefix($table);
                $list = @$db->list_fields($qname);
                if (is_array($list)) {
                    foreach ($list as $col) {
                        $fields[(string) $col] = true;
                    }
                }
            }
            $cache[$table] = $fields;
        }
        return $cache[$table];
    }
}

if (!function_exists('order_db_filter_row')) {
    /**
     * Keep only keys that exist on the target table.
     *
     * @param object $db
     * @param string $table
     * @param array  $row
     * @return array
     */
    function order_db_filter_row($db, $table, array $row) {
        $table = trim((string) $table);
        if ($table === 'order_items' && function_exists('order_pick_order_item_line_for_db')) {
            $row = order_pick_order_item_line_for_db($row);
        }
        $allowed = order_db_table_fields($db, $table);
        if ($allowed === array()) {
            return $row;
        }
        $out = array();
        foreach ($row as $k => $v) {
            if (isset($allowed[$k])) {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

if (!function_exists('order_elintom_order_item_column_names')) {
    /**
     * Columns allowed on sma_order_items for webshop API inserts (no variant_price).
     *
     * @return array<int,string>
     */
    function order_elintom_order_item_column_names() {
        return array(
            'sale_id', 'product_id', 'product_code', 'article_code', 'product_name', 'product_type',
            'option_id', 'net_unit_price', 'unit_discount', 'unit_tax', 'invoice_unit_price',
            'invoice_net_unit_price', 'unit_price', 'quantity', 'net_price', 'invoice_total_net_unit_price',
            'warehouse_id', 'item_tax', 'tax_method', 'tax_rate_id', 'tax', 'discount', 'item_discount',
            'subtotal', 'real_unit_price', 'product_unit_id', 'product_unit_code', 'unit_quantity',
            'mrp', 'hsn_code', 'note', 'delivery_status', 'pending_quantity', 'delivered_quantity',
            'gst_rate', 'cgst', 'sgst', 'igst', 'item_weight',
        );
    }
}

if (!function_exists('order_pick_order_item_line_for_db')) {
    /**
     * Whitelist API line payload before order_items INSERT (drops variant_price, etc.).
     *
     * @param array $row
     * @return array
     */
    function order_pick_order_item_line_for_db(array $row) {
        $src = is_array($row) ? $row : (array) $row;
        $flat = array();
        foreach ($src as $k => $v) {
            if (!is_string($k) || $k === '' || is_array($v) || is_object($v)) {
                continue;
            }
            $flat[$k] = $v;
        }
        foreach (array('variant_id', 'variant_price', 'product_option_id') as $drop) {
            unset($flat[$drop]);
        }
        $pick = array();
        foreach (order_elintom_order_item_column_names() as $key) {
            if (array_key_exists($key, $flat)) {
                $pick[$key] = $flat[$key];
            }
        }
        return $pick;
    }
}

if (!function_exists('order_items_prepare_row_for_insert')) {
    /**
     * Normalize one order_items row for sma_order_items insert (webshop API payload).
     *
     * @param object $db
     * @param array  $item
     * @param int    $sale_id
     * @return array|null null when product_id missing
     */
    function order_items_prepare_row_for_insert($db, array $item, $sale_id) {
        $clean = array();
        foreach ($item as $k => $v) {
            if (!is_string($k) || $k === '') {
                continue;
            }
            if (is_array($v) || is_object($v)) {
                continue;
            }
            $clean[$k] = $v;
        }
        $item = order_pricing_normalize_item_row($clean);
        $pid = isset($item['product_id']) ? (int) $item['product_id'] : 0;
        if ($pid < 1) {
            return null;
        }

        $item['sale_id'] = (int) $sale_id;
        $item['product_id'] = $pid;

        $oid = isset($item['option_id']) ? (int) $item['option_id'] : 0;
        if ($oid < 1) {
            unset($item['option_id']);
        } else {
            $item['option_id'] = $oid;
        }

        foreach (array('variant_id', 'variant_price', 'product_option_id') as $drop) {
            unset($item[$drop]);
        }

        if (empty($item['product_code']) && $item['product_code'] !== '0') {
            $item['product_code'] = '';
        }
        if (empty($item['product_name'])) {
            $item['product_name'] = 'Product #' . $pid;
        }

        $qty = order_pricing_item_qty($item);
        $item['quantity'] = $qty;
        if (!isset($item['unit_quantity']) || (float) $item['unit_quantity'] <= 0) {
            $item['unit_quantity'] = $qty;
        }

        $unit = order_pricing_unit_price($item);
        if ($unit > 0) {
            $item['unit_price'] = $unit;
            if (empty($item['net_unit_price']) || (float) $item['net_unit_price'] <= 0) {
                $item['net_unit_price'] = $unit;
            }
        }

        $line = order_pricing_line_total($item);
        if ($line > 0) {
            $item['subtotal'] = $line;
        }

        if (!isset($item['item_tax'])) {
            $item['item_tax'] = 0;
        }
        if (!isset($item['item_discount'])) {
            $item['item_discount'] = 0;
        }

        $pick = order_pick_order_item_line_for_db($item);

        return order_db_filter_row($db, 'order_items', $pick);
    }
}
