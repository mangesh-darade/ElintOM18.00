<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$receipt_context = isset($receipt_context) ? $receipt_context : 'sale';
$is_challan = ($receipt_context === 'challan');
if (!isset($pos_settings) && isset($pos_settingss)) {
    $pos_settings = $pos_settingss;
}
if (!isset($hide_customer_phone)) {
    $hide_customer_phone = $this->sma->shouldHideCustomerPhone($customer);
}
if (!isset($sms_url)) {
    $sms_code = md5('Reciept' . $inv->reference_no . $inv->id);
    if ($is_challan) {
        $sms_url = base_url('reciept/send_sms_challan') . '?code=' . md5('Reciept' . $inv->reference_no . $inv->id);
    } else {
        $sms_url = base_url('reciept/send_sms') . '?code=' . md5('Reciept' . $inv->reference_no . $inv->id);
    }
    if (!$hide_customer_phone && !empty($customer->phone)) {
        $sms_url .= '&phone=' . rawurlencode($customer->phone);
    }
}
$print_redirect_url = isset($print_redirect_url) ? $print_redirect_url : ($is_challan ? site_url('sales/challans') : site_url(isset($_SESSION['Sales']) ? 'sales' : 'pos'));
$email_endpoint = $is_challan ? 'sales/email_challan_receipt' : 'pos/email_receipt';
$whatsapp_type = $is_challan ? 'challan' : 'sale';
$page_label = $is_challan ? lang('challan_no') : lang('invoice_no');
$doc_no = $is_challan ? (isset($inv->challan_no) ? $inv->challan_no : $inv->id) : (isset($inv->invoice_no) ? $inv->invoice_no : $inv->id);

$document_title = $is_challan ? 'PROFORMA INVOICE' : 'TAX INVOICE';
$inv_date = !empty($inv->date) ? date('d/m/Y', strtotime($inv->date)) : '';
$proforma_no = '';
if ($is_challan) {
    $proforma_no = !empty($inv->challan_no) ? $inv->challan_no : (isset($inv->reference_no) ? $inv->reference_no : '');
} else {
    $proforma_no = !empty($inv->invoice_no) ? $inv->invoice_no : (isset($inv->reference_no) ? $inv->reference_no : '');
}
$po_no = !empty($inv->order_no) ? $inv->order_no : '';
$po_dated = $po_no !== '' ? $inv_date : '';
$destination = !empty($inv->place_of_supply) ? $inv->place_of_supply : (isset($customer->state) ? $customer->state : '');
$dispatch_through = !empty($inv->transporter_mode) ? $inv->transporter_mode : '';
$dispatch_date = !empty($inv->updated_at) ? date('d/m/Y', strtotime($inv->updated_at)) : '';
$docket_no = !empty($inv->LR_No) ? $inv->LR_No : (!empty($inv->bill_no) ? $inv->bill_no : 'NA');
$biller_name = ($biller->company && $biller->company != '-') ? $biller->company : $biller->name;
$biller_address = trim(implode(', ', array_filter(array(
    isset($biller->address) ? $biller->address : '',
    isset($biller->city) ? $biller->city : '',
    isset($biller->postal_code) ? $biller->postal_code : '',
    isset($biller->state) ? $biller->state : '',
))));
$biller_phone = isset($biller->phone) ? $biller->phone : '';
$biller_email = isset($biller->email) ? $biller->email : '';
$biller_website = !empty($biller->cf2) && $biller->cf2 != '-' ? $biller->cf2 : '';
$biller_country = isset($biller->country) ? $biller->country : '';
$a4nf_assets = isset($Customer_assets) ? $Customer_assets : '';
$a4nf_logo_file = '';
if (!empty($biller->logo) && $biller->logo != '-') {
    $a4nf_logo_file = $biller->logo;
} elseif (!empty($Settings->logo) && $Settings->logo != '-') {
    $a4nf_logo_file = $Settings->logo;
}
$biller_show_logo = false;
$biller_logo_url = '';
if ($a4nf_logo_file && $a4nf_assets) {
    $a4nf_logo_rel = 'assets/mdata/' . $a4nf_assets . '/uploads/logos/' . $a4nf_logo_file;
    if (file_exists(FCPATH . $a4nf_logo_rel)) {
        $biller_show_logo = true;
        $biller_logo_url = base_url($a4nf_logo_rel);
    }
}
$biller_show_cf = isset($default_printer) && isset($default_printer->show_order_cf) && (int) $default_printer->show_order_cf === 1;
if (!function_exists('a4nf_format_notes_html')) {
    function a4nf_format_notes_html($html) {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        if ($html === strip_tags($html)) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES, 'UTF-8'));
        }
        return $html;
    }
}
if (!function_exists('a4nf_is_gst_state_code')) {
    function a4nf_is_gst_state_code($code) {
        return preg_match('/^\d{1,2}$/', trim((string) $code)) === 1;
    }
}
if (!function_exists('a4nf_normalize_state_name')) {
    function a4nf_normalize_state_name($state_name) {
        $state_name = trim((string) $state_name);
        if ($state_name === '') {
            return '';
        }
        if (strpos($state_name, '~') !== false) {
            $parts = explode('~', $state_name, 2);
            return trim($parts[0]);
        }
        return $state_name;
    }
}
if (!function_exists('a4nf_state_with_code')) {
    function a4nf_state_with_code($state_name, $state_code) {
        $state_name = trim((string) $state_name);
        $state_code = trim((string) $state_code);
        if ($state_name === '') {
            return '';
        }
        if ($state_code !== '') {
            return htmlspecialchars($state_name, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($state_code, ENT_QUOTES, 'UTF-8') . ')';
        }
        return htmlspecialchars($state_name, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('a4nf_resolve_state_code')) {
    function a4nf_resolve_state_code($addr_row, $fallback_row, $state_name, $site) {
        $raw_state = $addr_row && !empty($addr_row->state) ? trim((string) $addr_row->state) : trim((string) $state_name);
        if (strpos($raw_state, '~') !== false) {
            $parts = explode('~', $raw_state, 2);
            if (isset($parts[1]) && a4nf_is_gst_state_code($parts[1])) {
                return trim($parts[1]);
            }
            $state_name = trim($parts[0]);
        }
        $state_name = a4nf_normalize_state_name($state_name);

        if ($state_name !== '' && $site) {
            $resolved = $site->getGstStateCodeFromName($state_name);
            if ($resolved) {
                return trim((string) $resolved);
            }
        }
        if ($fallback_row && !empty($fallback_row->gst_state_code)) {
            return trim((string) $fallback_row->gst_state_code);
        }
        if ($addr_row && !empty($addr_row->state_code) && a4nf_is_gst_state_code($addr_row->state_code)) {
            return trim((string) $addr_row->state_code);
        }
        if ($fallback_row && !empty($fallback_row->state_code) && a4nf_is_gst_state_code($fallback_row->state_code)) {
            return trim((string) $fallback_row->state_code);
        }
        if ($addr_row && !empty($addr_row->state_code) && $site) {
            $abbr = trim((string) $addr_row->state_code);
            $name_from_code = $site->getStateFromStateCode($abbr);
            if ($name_from_code) {
                $resolved = $site->getGstStateCodeFromName($name_from_code);
                if ($resolved) {
                    return trim((string) $resolved);
                }
            }
        }
        return '';
    }
}
if (!function_exists('a4nf_strip_bank_details_heading')) {
    function a4nf_strip_bank_details_heading($html) {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        $patterns = array(
            '/^<p[^>]*>\s*(?:<(?:strong|b)[^>]*>)?\s*Bank\s+Details\s*:?\s*-?\s*(?:<\/(?:strong|b)>)?\s*<\/p>\s*/i',
            '/^(?:<(?:strong|b)[^>]*>)?\s*Bank\s+Details\s*:?\s*-?\s*(?:<\/(?:strong|b)>)?\s*(?:<br\s*\/?>|\r?\n)\s*/i',
            '/^Bank\s+Details\s*:?\s*-?\s*(?:<br\s*\/?>|\r?\n)\s*/i',
        );
        foreach ($patterns as $pattern) {
            $new = preg_replace($pattern, '', $html, 1);
            if ($new !== null && $new !== $html) {
                return ltrim($new);
            }
        }
        return $html;
    }
}
if (!function_exists('a4nf_strip_footer_label')) {
    function a4nf_strip_footer_label($html, $label) {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        $quoted = preg_quote($label, '/');
        $patterns = array(
            '/<(?:strong|b)[^>]*>\s*' . $quoted . '\s*:?\s*-?\s*<\/(?:strong|b)>\s*/i',
            '/<p[^>]*>\s*(?:<(?:strong|b)[^>]*>)?\s*' . $quoted . '\s*:?\s*-?\s*(?:<\/(?:strong|b)>)?\s*<\/p>\s*/i',
            '/(?:^|[\r\n>])\s*' . $quoted . '\s*:?\s*-?\s*(?:<br\s*\/?>|\r?\n)?/i',
        );
        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, '', $html, 1);
        }
        return ltrim($html);
    }
}
if (!function_exists('a4nf_build_address_text')) {
    function a4nf_build_address_text($addr_row, $include_name = true) {
        if (!$addr_row) {
            return '';
        }
        $parts = array();
        if ($include_name && !empty($addr_row->address_name)) {
            $parts[] = $addr_row->address_name;
        }
        foreach (array('line1', 'line2', 'city', 'postal_code', 'country') as $field) {
            if (!empty($addr_row->$field)) {
                $parts[] = $addr_row->$field;
            }
        }
        return trim(implode(', ', array_filter($parts)));
    }
}
if (!function_exists('a4nf_strip_address_name_from_text')) {
    function a4nf_strip_address_name_from_text($text, $addr_row = null) {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }
        if ($addr_row && !empty($addr_row->address_name)) {
            $name = trim($addr_row->address_name);
            if ($name !== '' && stripos($text, $name) === 0) {
                $text = trim(substr($text, strlen($name)), " \t\n\r\0\x0B,");
            }
        }
        return $text;
    }
}
if (!function_exists('a4nf_payment_method_label')) {
    function a4nf_payment_method_label($paid_by) {
        if (empty($paid_by)) {
            return '';
        }
        if ($paid_by === 'gift_card') {
            return lang('Gift_Card');
        }
        if ($paid_by === 'credit_note') {
            return lang('Credit_Note');
        }
        if ($paid_by === 'award_point') {
            return lang('Award_Point');
        }
        if (strpos($paid_by, 'other_') === 0) {
            return ucwords(str_replace('_', ' ', substr($paid_by, 6)));
        }
        $label = lang($paid_by);
        return ($label && $label !== $paid_by) ? $label : ucfirst(str_replace('_', ' ', $paid_by));
    }
}
if (!function_exists('a4nf_payment_reference_text')) {
    function a4nf_payment_reference_text($payment) {
        if (!$payment || empty($payment->paid_by)) {
            return '';
        }
        $paid_by = $payment->paid_by;
        if (($paid_by === 'ppp' || $paid_by === 'stripe') && !empty($payment->cc_no)) {
            return 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4);
        }
        if ($paid_by === 'Cheque' && !empty($payment->cheque_no)) {
            return lang('cheque_no') . ': ' . $payment->cheque_no;
        }
        if ($paid_by === 'gift_card' && !empty($payment->cc_no)) {
            return lang('no') . ': ' . $payment->cc_no;
        }
        if ($paid_by === 'credit_note' && !empty($payment->cc_no)) {
            return lang('no') . ': ' . $payment->cc_no;
        }
        if (in_array($paid_by, array('CC', 'DC', 'Credit Card', 'Debit Card'), true) && !empty($payment->transaction_id)) {
            return 'Transaction No: ' . $payment->transaction_id;
        }
        if (!empty($payment->cc_no) && !in_array($paid_by, array('cash', 'deposit', 'Due Payment', 'award_point'), true)) {
            $cc_no = trim((string) $payment->cc_no);
            if (strlen($cc_no) >= 4 && ctype_digit(preg_replace('/\D/', '', $cc_no))) {
                return 'xxxx xxxx xxxx ' . substr($cc_no, -4);
            }
            return lang('no') . ': ' . $cc_no;
        }
        if (!empty($payment->transaction_id)) {
            return 'Transaction No: ' . $payment->transaction_id;
        }
        if (!empty($payment->cheque_no)) {
            return lang('cheque_no') . ': ' . $payment->cheque_no;
        }
        return '';
    }
}
if (!function_exists('a4nf_payment_reference_value')) {
    function a4nf_payment_reference_value($payment) {
        if (!$payment || empty($payment->paid_by)) {
            return '';
        }
        $paid_by = $payment->paid_by;
        if (($paid_by === 'ppp' || $paid_by === 'stripe') && !empty($payment->cc_no)) {
            $cc_no = trim((string) $payment->cc_no);
            return (strlen($cc_no) >= 4 && ctype_digit(preg_replace('/\D/', '', $cc_no)))
                ? 'xxxx xxxx xxxx ' . substr($cc_no, -4)
                : $cc_no;
        }
        if ($paid_by === 'Cheque' && !empty($payment->cheque_no)) {
            return $payment->cheque_no;
        }
        if (!empty($payment->transaction_id)) {
            return $payment->transaction_id;
        }
        if (!empty($payment->cc_no) && !in_array($paid_by, array('cash', 'deposit', 'Due Payment', 'award_point'), true)) {
            return trim((string) $payment->cc_no);
        }
        if (!empty($payment->cheque_no)) {
            return $payment->cheque_no;
        }
        return '';
    }
}
if (!function_exists('a4nf_payment_breakdown_html')) {
    function a4nf_payment_breakdown_html($payments, $fmt_callback) {
        if (empty($payments)) {
            return '&nbsp;';
        }
        $rows = '';
        foreach ($payments as $payment) {
            if (empty($payment->paid_by)) {
                continue;
            }
            $method = a4nf_payment_method_label($payment->paid_by) . ':-';
            $ref = a4nf_payment_reference_value($payment);
            $amount = isset($payment->amount) ? (float) $payment->amount : 0;
            $rows .= '<tr>'
                . '<td class="a4nf-paid-method">' . htmlspecialchars($method) . '</td>'
                . '<td class="a4nf-paid-ref">' . ($ref !== '' ? htmlspecialchars($ref) : '&nbsp;') . '</td>'
                . '<td class="a4nf-paid-amt">' . call_user_func($fmt_callback, $amount) . '</td>'
                . '</tr>';
        }
        if ($rows === '') {
            return '&nbsp;';
        }
        return '<table class="a4nf-paid-breakdown"><tbody>' . $rows . '</tbody></table>';
    }
}
if (!function_exists('a4nf_payment_display_line')) {
    function a4nf_payment_display_line($payment) {
        $method_label = a4nf_payment_method_label($payment->paid_by);
        if ($method_label === '') {
            return '';
        }
        $reference = a4nf_payment_reference_text($payment);
        return $reference !== '' ? $method_label . ' (' . $reference . ')' : $method_label;
    }
}
$customer_name = !empty($inv->customer) ? $inv->customer : (($customer->company && $customer->company != '-') ? $customer->company : $customer->name);
$customer_address = '';
$billing_addr_row = null;
$shipping_addr_row = null;
if (!empty($inv->billing_address_id)) {
    $billing_addr_row = $this->db->get_where('addresses', array('id' => (int) $inv->billing_address_id), 1)->row();
    if ($billing_addr_row) {
        $customer_address = a4nf_build_address_text($billing_addr_row, false);
    }
}
if ($customer_address === '' && !empty($billing_address_text)) {
    $customer_address = a4nf_strip_address_name_from_text($billing_address_text, $billing_addr_row);
}
if ($customer_address === '') {
    $customer_address = trim(implode(', ', array_filter(array(
        isset($customer->address) ? $customer->address : '',
        isset($customer->city) ? $customer->city : '',
        isset($customer->postal_code) ? $customer->postal_code : '',
        isset($customer->country) ? $customer->country : '',
    ))));
}
$customer_address = a4nf_strip_address_name_from_text($customer_address, $billing_addr_row);
if ($customer_name !== '' && $customer_address !== '' && stripos($customer_address, $customer_name) === 0) {
    $customer_address = trim(substr($customer_address, strlen($customer_name)), " \t\n\r\0\x0B,");
}
$shipping_address = '';
if (!empty($inv->shipping_address_id)) {
    $shipping_addr_row = $this->db->get_where('addresses', array('id' => (int) $inv->shipping_address_id), 1)->row();
    if ($shipping_addr_row) {
        $shipping_address = a4nf_build_address_text($shipping_addr_row, false);
    }
}
if ($shipping_address === '' && !empty($shipping_address_text)) {
    $shipping_address = a4nf_strip_address_name_from_text($shipping_address_text, $shipping_addr_row);
} elseif ($shipping_address !== '') {
    $shipping_address = a4nf_strip_address_name_from_text($shipping_address, $shipping_addr_row);
}
$customer_phone = (!$this->sma->shouldHideCustomerPhone($customer) && !empty($customer->phone)) ? $customer->phone : '';
$customer_email = !empty($customer->email) ? $customer->email : '';
$customer_gst = !empty($customer->gstn_no) ? $customer->gstn_no : '';
$customer_default_state = !empty($customer->state) ? trim($customer->state) : '';
$billing_state = '';
if ($billing_addr_row && !empty($billing_addr_row->state)) {
    $billing_state = a4nf_normalize_state_name($billing_addr_row->state);
} elseif ($customer_default_state !== '') {
    $billing_state = a4nf_normalize_state_name($customer_default_state);
}
$shipping_state = '';
if ($shipping_addr_row && !empty($shipping_addr_row->state)) {
    $shipping_state = a4nf_normalize_state_name($shipping_addr_row->state);
}
$place_of_supply = $shipping_state;
$billing_state_code = a4nf_resolve_state_code($billing_addr_row, $customer, $billing_state, isset($this->site) ? $this->site : null);
$shipping_state_code = a4nf_resolve_state_code($shipping_addr_row, null, $shipping_state, isset($this->site) ? $this->site : null);
$place_of_supply_code = $shipping_state_code;
$customer_pan = !empty($customer->pan_card) ? $customer->pan_card : (!empty($customer->cf1) ? $customer->cf1 : '');
$terms_of_payment = !empty($customer->payment_term) ? $customer->payment_term : 'As Per Quotation';
$total_before_tax = isset($inv->total) ? (float) $inv->total : 0;
$total_cgst = 0;
$total_sgst = 0;
$total_igst = 0;
$total_discount = isset($inv->product_discount) ? (float) $inv->product_discount : 0;
$freight = isset($inv->shipping) ? (float) $inv->shipping : 0;
$round_off = isset($inv->rounding) ? (float) $inv->rounding : 0;
$grand_total = isset($inv->grand_total) ? (float) $inv->grand_total : 0;
if (!empty($rows)) {
    foreach ($rows as $row) {
        $total_cgst += isset($row->cgst) ? (float) $row->cgst : 0;
        $total_sgst += isset($row->sgst) ? (float) $row->sgst : 0;
        $total_igst += isset($row->igst) ? (float) $row->igst : 0;
    }
}
$product_tax = isset($inv->product_tax) ? (float) $inv->product_tax : ($total_cgst + $total_sgst + $total_igst);
if ($total_cgst == 0 && $total_sgst == 0 && $total_igst == 0 && $product_tax > 0) {
    $total_cgst = $product_tax / 2;
    $total_sgst = $product_tax / 2;
}
$a4nf_show_igst_total = $total_igst > 0;
$a4nf_show_cgst_sgst_total = !$a4nf_show_igst_total;
$a4nf_footer_rowspan = $a4nf_show_igst_total ? 7 : 8;
$amount_words = ucwords($this->sma->convert_number_to_words($grand_total + $round_off));
$bank_details = $this->sma->decode_html(isset($biller->invoice_footer) ? $biller->invoice_footer : '');
$bank_details = a4nf_strip_bank_details_heading($bank_details);
$terms_conditions = !empty($inv->note) ? $this->sma->decode_html($inv->note) : '';
$terms_conditions = a4nf_strip_footer_label($terms_conditions, 'Terms and Conditions');
$terms_conditions = a4nf_format_notes_html($terms_conditions);
$invoice_declaration = '';
$a4nf_show_declaration = false;
if (!empty($biller->invoice_declaration) && trim(strip_tags($biller->invoice_declaration)) !== '') {
    $invoice_declaration = $this->sma->decode_html($biller->invoice_declaration);
    $invoice_declaration = a4nf_strip_footer_label($invoice_declaration, 'Declaration');
    $invoice_declaration = a4nf_format_notes_html($invoice_declaration);
    $a4nf_show_declaration = trim(strip_tags($invoice_declaration)) !== '';
}
$biller_pan = !empty($biller->pan_card) ? $biller->pan_card : (!empty($biller->cf1) ? $biller->cf1 : '');
$biller_gst = !empty($biller->gstn_no) ? $biller->gstn_no : '';
$biller_state_code = !empty($biller->gst_state_code) ? trim($biller->gst_state_code) : '';
if ($biller_state_code === '' && !empty($biller->state_code) && a4nf_is_gst_state_code($biller->state_code)) {
    $biller_state_code = trim($biller->state_code);
}
if ($biller_state_code === '' && !empty($biller->state) && isset($this->site)) {
    $resolved_biller_code = $this->site->getGstStateCodeFromName(a4nf_normalize_state_name($biller->state));
    if ($resolved_biller_code) {
        $biller_state_code = trim((string) $resolved_biller_code);
    }
}
$biller_cf6 = !empty($biller->cf6) && $biller->cf6 != '-' ? $biller->cf6 : '';
$paid_amount = isset($inv->paid) ? (float) $inv->paid : 0;
if (!empty($return_sale) && isset($return_sale->paid)) {
    $paid_amount += (float) $return_sale->paid;
}
$total_after_tax = $grand_total + $round_off;
$due_amount = $total_after_tax - $paid_amount;
$fmt = array($this->sma, 'a4NewFormatMoney');
$paid_breakdown_html = a4nf_payment_breakdown_html(isset($payments) ? $payments : array(), $fmt);
if (!isset($resOutput) || empty($resOutput)) {
    $return_sale = isset($return_sale) && !empty($return_sale) ? $return_sale : array();
    $return_rows = isset($return_rows) ? $return_rows : null;
    if ($is_challan) {
        $resOutput = $this->sma->posBillTable($default_printer, $inv, $return_sale, $rows, $return_rows);
    } else {
        $resOutput = $this->sma->posBillTableCSI($default_printer, $inv, $return_sale, $rows, $return_rows, isset($salestax) ? $salestax : '');
    }
}

if ($modal) {
    echo '<div class="modal-dialog modal-lg no-modal-header"><div class="modal-content"><div class="modal-body"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>';
} else {
    ?>
    <!doctype html>
    <html>
        <head>
            <meta charset="utf-8">
            <title><?= $page_title . ' ' . $page_label . ' ' . $doc_no; ?></title>
            <base href="<?= base_url(); ?>"/>
            <script type="text/javascript">const site = { base_url: "<?= base_url(); ?>", site_url: "<?= site_url(); ?>" };</script>
            <meta http-equiv="cache-control" content="max-age=0"/>
            <meta http-equiv="cache-control" content="no-cache"/>
            <meta http-equiv="expires" content="0"/>
            <meta http-equiv="pragma" content="no-cache"/>
            <link rel="shortcut icon" href="<?= $assets; ?>images/icon.png"/>
            <link rel="stylesheet" href="<?= $assets; ?>styles/theme.css" type="text/css"/>
            <link rel="stylesheet" href="<?= $assets; ?>styles/a4_new_format.css?v=a4nf27" type="text/css"/>
        </head>
        <body>
    <?php
}
?>
<div class="a4nf-wrapper" id="a4-new-format-receipt">
<div class="a4nf-page">
    <table class="a4nf-table a4nf-master-table a4nf-header-table">
        <tbody class="a4nf-doc-top">
        <tr>
            <td colspan="12" class="a4nf-company-header">
                <div class="a4nf-biller-name-wrap">
                    <div class="a4nf-company-name"><?= htmlspecialchars($biller_name); ?></div>
                </div>
                <div class="a4nf-biller-name-line"></div>
                <div class="a4nf-biller-block">
                    <div class="a4nf-biller-body">
                        <div class="a4nf-biller-logo">
                            <?php if ($biller_show_logo) { ?>
                                <img src="<?= $biller_logo_url; ?>" alt="<?= htmlspecialchars($biller_name); ?>" class="a4nf-biller-logo-img">
                            <?php } ?>
                        </div>
                        <div class="a4nf-biller-details">
                        <?php if ($biller_address || $biller_country) { ?>
                            <div class="a4nf-header-address"> <?= htmlspecialchars(trim($biller_address . ($biller_country ? ', ' . $biller_country : ''))); ?></div>
                        <?php } ?>
                        <div class="a4nf-header-contact-row">
                            <?php
                            $contact_parts = array();
                            if ($biller_phone) {
                                $contact_parts[] = 'Phone No.: ' . htmlspecialchars($biller_phone);
                            }
                            if ($biller_email) {
                                $contact_parts[] = 'Email ID: ' . htmlspecialchars($biller_email);
                            }
                            if ($biller_gst) {
                                $contact_parts[] = 'GSTIN: ' . htmlspecialchars($biller_gst);
                            }
                            if ($biller_pan) {
                                $contact_parts[] = 'PAN: ' . htmlspecialchars($biller_pan);
                            }
                            if ($biller_state_code) {
                                $contact_parts[] = 'Code: ' . htmlspecialchars($biller_state_code);
                            }
                            echo implode(' &nbsp;|&nbsp; ', $contact_parts);
                            ?>
                        </div>
                        <?php
                        $pos_cf_parts = array();
                        if (isset($pos_settings) && $pos_settings->cf_title1 != '' && $pos_settings->cf_value1 != '') {
                            $pos_cf_parts[] = htmlspecialchars($pos_settings->cf_title1) . ': ' . htmlspecialchars($pos_settings->cf_value1);
                        }
                        if (isset($pos_settings) && $pos_settings->cf_title2 != '' && $pos_settings->cf_value2 != '') {
                            $pos_cf_parts[] = htmlspecialchars($pos_settings->cf_title2) . ': ' . htmlspecialchars($pos_settings->cf_value2);
                        }
                        if (!empty($pos_cf_parts)) {
                            echo '<div class="a4nf-header-extra-row">' . implode(' &nbsp;|&nbsp; ', $pos_cf_parts) . '</div>';
                        }
                        if ($biller_show_cf) {
                            $bcf_parts = array();
                            $bcf_fields = array('cf1', 'cf2', 'cf3', 'cf4', 'cf5');
                            foreach ($bcf_fields as $bcf_key) {
                                if ($bcf_key === 'cf2' && $biller_website) {
                                    continue;
                                }
                                if (!empty($biller->$bcf_key) && $biller->$bcf_key != '-') {
                                    $bcf_parts[] = lang('b' . $bcf_key) . ': ' . htmlspecialchars($biller->$bcf_key);
                                }
                            }
                            if ($biller_cf6) {
                                $bcf_parts[] = lang('bcf6') . ': ' . htmlspecialchars($biller_cf6);
                            }
                            if (!empty($bcf_parts)) {
                                echo '<div class="a4nf-header-extra-row">' . implode(' &nbsp;|&nbsp; ', $bcf_parts) . '</div>';
                            }
                        } elseif ($biller_cf6) {
                            echo '<div class="a4nf-header-extra-row">' . lang('bcf6') . ': ' . htmlspecialchars($biller_cf6) . '</div>';
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="12" class="a4nf-doc-title-cell"><?= $document_title; ?></td>
        </tr>
        <tr>
            <td colspan="6" rowspan="5" class="a4nf-party-left" valign="top">
                <div class="a4nf-section-title"><?= $is_challan ? 'Proforma Invoiced To' : 'Invoiced To'; ?></div>
                <?php if ($customer_name) { ?><div class="a4nf-customer-line a4nf-customer-name"><?= htmlspecialchars($customer_name); ?></div><?php } ?>
                <?php if ($customer_address) { ?><div class="a4nf-customer-line"><?= nl2br(htmlspecialchars($customer_address)); ?></div><?php } ?>
                <?php
                $billing_contact_parts = array();
                if ($customer_phone) {
                    $billing_contact_parts[] = 'Mobile No. : ' . htmlspecialchars($customer_phone);
                }
                if ($customer_email) {
                    $billing_contact_parts[] = 'Email ID : ' . htmlspecialchars($customer_email);
                }
                if (!empty($billing_contact_parts)) {
                    echo '<div class="a4nf-customer-line">' . implode(' &nbsp;|&nbsp; ', $billing_contact_parts) . '</div>';
                }
                $billing_tax_parts = array();
                if ($customer_gst) {
                    $billing_tax_parts[] = 'GST No. : ' . htmlspecialchars($customer_gst);
                }
                if ($customer_pan) {
                    $billing_tax_parts[] = 'PAN No. : ' . htmlspecialchars($customer_pan);
                }
                if (!empty($billing_tax_parts)) {
                    echo '<div class="a4nf-customer-line">' . implode(' &nbsp;|&nbsp; ', $billing_tax_parts) . '</div>';
                }
                ?>
                <?php if ($billing_state) { ?><div class="a4nf-customer-line">State : <?= a4nf_state_with_code($billing_state, $billing_state_code); ?></div><?php } ?>
                <?php if ($place_of_supply) { ?><div class="a4nf-customer-line">Place of Supply : <?= a4nf_state_with_code($place_of_supply, $place_of_supply_code); ?></div><?php } ?>
                <div class="a4nf-customer-line">Terms of Payment : <?= htmlspecialchars($terms_of_payment); ?></div>
                <?php if ($shipping_address !== '') { ?>
                <div class="a4nf-shipping-block">
                    <div class="a4nf-section-title">Shipping Address :</div>
                    <div class="a4nf-customer-line"><?= nl2br(htmlspecialchars($shipping_address)); ?></div>
                    <?php if ($shipping_state) { ?><div class="a4nf-customer-line">State : <?= a4nf_state_with_code($shipping_state, $shipping_state_code); ?></div><?php } ?>
                </div>
                <?php } ?>
            </td>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label"><?= $is_challan ? 'Proforma No.' : 'Invoice No.'; ?></div>
                <div class="a4nf-meta-pair-value"><strong><?= htmlspecialchars($proforma_no); ?></strong></div>
            </td>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Dated</div>
                <div class="a4nf-meta-pair-value"><?= $inv_date; ?></div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">PO No.</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Dated</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Dispatch Through</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Destination</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Docket No</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
            <td colspan="3" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Dispatch Date</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td colspan="6" class="a4nf-meta-pair" valign="top">
                <div class="a4nf-meta-pair-label">Dispatch Details :</div>
                <div class="a4nf-meta-pair-value a4nf-meta-pair-value-empty">&nbsp;</div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="a4nf-items-wrap a4nf-product-details">
        <?= $resOutput; ?>
    </div>
    <table class="a4nf-table a4nf-master-table a4nf-footer-table">
        <tbody>
            <tr>
                <td colspan="6" rowspan="<?= $a4nf_footer_rowspan; ?>" class="a4nf-bank-cell" valign="top">
                    <div class="a4nf-left-stack">
                        <div class="a4nf-stack-block"><strong>Amount in Word :</strong>Rupees <?= $amount_words; ?> only</div>
                        <div class="a4nf-stack-block a4nf-bank-details"><?= $bank_details; ?></div>
                        <div class="a4nf-stack-block a4nf-notes-block"><?= $terms_conditions ? $terms_conditions : '&nbsp;'; ?></div>
                    </div>
                </td>
                <td colspan="2" class="a4nf-total-label">Total Amount before Tax</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $total_before_tax); ?></td>
            </tr>
            <?php if ($a4nf_show_cgst_sgst_total) { ?>
            <tr>
                <td colspan="2" class="a4nf-total-label">CGST</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $total_cgst); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="a4nf-total-label">SGST</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $total_sgst); ?></td>
            </tr>
            <?php } ?>
            <?php if ($a4nf_show_igst_total) { ?>
            <tr>
                <td colspan="2" class="a4nf-total-label">IGST</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $total_igst); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="2" class="a4nf-total-label">Rounding</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $round_off); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="a4nf-total-label">Shipping Charges</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $freight); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="a4nf-total-label a4nf-total-label-bold">Total Amount after Tax</td>
                <td colspan="4" class="a4nf-total-value a4nf-total-value-bold"><?= call_user_func($fmt, $total_after_tax); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="a4nf-total-label">Paid Amount</td>
                <td colspan="4" class="a4nf-total-value a4nf-paid-amount-cell"><?= $paid_breakdown_html; ?></td>
            </tr>
            <tr>
                <td colspan="2" class="a4nf-total-label">Due Amount</td>
                <td colspan="4" class="a4nf-total-value"><?= call_user_func($fmt, $due_amount); ?></td>
            </tr>
            <tr class="a4nf-signatures-row">
                <td colspan="6" class="a4nf-sig-box a4nf-sig-customer">
                    <div class="a4nf-sig-fill"></div>
                    <div class="a4nf-sig-label">Customer Signature</div>
                </td>
                <td colspan="6" class="a4nf-sig-box a4nf-sig-authorised">
                    <div class="a4nf-sig-company">For <?= htmlspecialchars($biller_name); ?></div>
                    <div class="a4nf-sig-fill"></div>
                    <div class="a4nf-sig-label">Authorised signatory</div>
                </td>
            </tr>
            <?php if ($a4nf_show_declaration) { ?>
            <tr class="a4nf-declaration-row">
                <td colspan="12" class="a4nf-declaration-cell">
                    <strong>Declaration :</strong> <?= $invoice_declaration; ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</div>
<?php
if ($modal) {
    echo '</div></div></div></div>';
} else {
    ?>
            <div class="a4nf-wrapper a4nf-actions-bar no-print" id="buttons">
                <hr>
                <?php if (!empty($message)) { ?>
                    <div class="alert alert-success"><?= is_array($message) ? print_r($message, true) : $message; ?></div>
                <?php } ?>
                <div class="a4nf-action-item">
                    <a href="javascript:window.print()" id="web_print" class="btn btn-block btn-primary" onClick="window.print(); return false;"><?= lang('web_print'); ?></a>
                </div>
                <div class="a4nf-action-item">
                    <a class="btn btn-block btn-success" href="#" id="email"><?= lang('email'); ?></a>
                </div>
                <?php if (!empty($sms_url)) { ?>
                    <div class="a4nf-action-item">
                        <?php $DisSMSLink = (isset($sms_limit) && (int) $sms_limit < 1) ? '<br><a href="http://simplypos.in/login.php" style="color:#000" target="_blank">Please Login on merchant panel & Rechagre Now</a>' : ''; ?>
                        <a class="btn btn-block btn-info" href="javascript:void(0);" id="sms_bill">SMS</a>
                    </div>
                <?php } ?>
                <div class="a4nf-action-item">
                    <a class="btn btn-block btn-info" href="javascript:void(0);" id="send_whatsapp_app">Whatsapp</a>
                </div>
                <div class="a4nf-action-item">
                    <a class="btn btn-block btn-warning" href="<?= $print_redirect_url; ?>" id="back_to_pos">
                        <?= $is_challan ? 'Back To Challans' : (isset($_SESSION['Sales']) ? 'Back To Sales' : lang('back_to_pos')); ?>
                    </a>
                </div>
                <div class="a4nf-action-note">
                    <p>Please don't forget to disable the header and footer in browser print settings.</p>
                </div>
                <?php if (isset($sms_limit)) { ?>
                <div class="a4nf-action-note">
                    <div class="sms_note blue">(Note : Available SMS limit <?php print((int) $sms_limit); ?> <?php echo isset($DisSMSLink) ? $DisSMSLink : ''; ?>)</div>
                </div>
                <?php } ?>
            </div>
            <script type="text/javascript" src="<?= $assets; ?>pos/js/jquery-1.7.2.min.js"></script>
            <script type="text/javascript" src="<?= $assets; ?>js/whatsapp.js?v=a4nf1"></script>
            <script type="text/javascript">
                window.receiptType = "<?= $whatsapp_type; ?>";
                $(document).ready(function () {
                    $('#sms_bill').click(function () {
                        var phone = prompt('Please enter Phone Number');
                        if (phone != null) {
                            $.ajax({
                                type: 'GET',
                                url: '<?= $sms_url; ?>&phone=' + phone,
                                dataType: 'json',
                                success: function (data) {
                                    alert(data.msg || data.message || 'SMS request sent');
                                },
                                error: function () {
                                    alert('Unable to send SMS');
                                }
                            });
                        }
                    });
                    $('#email').click(function () {
                        var email = prompt('Please enter email address');
                        if (email != null) {
                            $.ajax({
                                type: 'POST',
                                url: '<?= site_url($email_endpoint); ?>',
                                data: { <?= $is_challan ? 'challan_id' : 'sale_id'; ?>: '<?= (int) $sid; ?>', email: email },
                                dataType: 'json',
                                success: function (data) {
                                    alert(data.msg || data.message || 'Email sent');
                                },
                                error: function () {
                                    alert('Unable to send email');
                                }
                            });
                        }
                    });
                });
            </script>
            <?php
            $_print = isset($_SESSION['print_type']) ? $_SESSION['print_type'] : '';
            $should_autoprint = !empty($auto_print_receipt) || (isset($_SESSION['print_type']) && $_SESSION['print_type'] === 'print');
            if ($should_autoprint) {
                unset($_SESSION['print_type']);
                ?>
                <script>
                    $(window).load(function () { window.print(); });
                </script>
                <?php
            }
            ?>
        </body>
    </html>
    <?php
}
