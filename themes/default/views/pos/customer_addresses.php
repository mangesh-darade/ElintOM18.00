<?php defined('BASEPATH') OR exit('No direct script access allowed');

$crm_addresses_json = array();
if (!empty($customer_addresses) && is_array($customer_addresses)) {
    foreach ($customer_addresses as $addr) {
        $db_type = isset($addr->type) ? trim((string) $addr->type) : '';
        $addr_type = 'Billing';
        if (strcasecmp($db_type, 'Shipping') === 0) {
            $addr_type = 'Shipping';
            } elseif (strcasecmp($db_type, 'Site') === 0) {
            $addr_type = 'Site';
        }
        $addr_city = isset($addr->city) ? trim((string) $addr->city) : '';
        if ($addr_city === '-') {
            $addr_city = '';
        }
        $addr_name = isset($addr->address_name) ? trim((string) $addr->address_name) : '';
        if ($addr_name === '-' || $addr_name === '') {
            $addr_name = '';
        }
        $crm_addresses_json[] = array(
            'id' => (int) $addr->id,
            'type' => $addr_type,
            'address_name' => $addr_name,
            'line1' => (string) $addr->line1,
            'country' => (string) $addr->country,
            'state' => (string) $addr->state,
            'state_code' => isset($addr->state_code) ? trim((string) $addr->state_code) : '',
            'city' => $addr_city,
            'postal_code' => isset($addr->postal_code) ? trim((string) $addr->postal_code) : '',
            'is_default' => isset($addr->is_default) ? (int) $addr->is_default : 0,
        );
    }
}
?>
<style>
.customer-addresses-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.customer-addresses-head h4 {
    margin: 0;
}
.js-add-address-btn {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #fff !important;
}
.js-add-address-btn:hover,
.js-add-address-btn:focus,
.js-add-address-btn:active {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
    color: #fff !important;
}
.crm-addr-section {
    margin-bottom: 18px;
}
.crm-addr-section-title {
    font-size: 14px;
    font-weight: 700;
    color: #333;
    margin: 0 0 10px;
    padding-bottom: 6px;
    border-bottom: 2px solid #e8e8e8;
}
.crm-addr-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.crm-address-card {
    position: relative;
    flex: 1 1 280px;
    max-width: 100%;
    min-width: 240px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 40px 36px 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
#paymentModal .crm-address-card {
    cursor: pointer;
}
.crm-address-card.crm-address-card-default {
    border: 2px solid #0284c7;
    background: #f0f9ff;
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.15);
}
.crm-address-card-default-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    background: #0284c7;
    border-radius: 4px;
    padding: 2px 8px;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.crm-address-card-footer {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #e8e8e8;
}
.crm-address-card-default .crm-address-card-footer {
    border-top-color: #bae6fd;
}
.crm-address-card-footer .js-set-default-address {
    font-size: 12px;
    padding: 4px 10px;
}
.crm-address-card-name {
    font-size: 14px;
    font-weight: 600;
    color: #222;
    margin-bottom: 4px;
    word-break: break-word;
}
.crm-address-card-line {
    font-size: 13px;
    color: #333;
    margin-bottom: 8px;
    word-break: break-word;
}
.crm-address-card-meta {
    font-size: 12px;
    color: #555;
    line-height: 1.5;
}
.crm-address-card-meta span {
    display: block;
}
.crm-address-card-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 4px;
}
.crm-address-card-actions .btn {
    padding: 3px 7px;
    line-height: 1.2;
    border-color: #0284c7;
    background: #0284c7;
    color: #fff;
}
.crm-address-card-actions .btn i {
    color: #fff;
}
.crm-address-card-actions .btn:hover,
.crm-address-card-actions .btn:focus,
.crm-address-card-actions .btn:active {
    border-color: #0284c7;
    background: #0284c7;
    color: #fff;
}
.crm-addr-empty {
    font-size: 13px;
    color: #888;
    font-style: italic;
    padding: 8px 0;
}
.bootbox.modal {
    z-index: 10070;
}
.bootbox.modal ~ .modal-backdrop {
    z-index: 10060;
}
</style>

<div class="table-container crm-address-book" style="margin-top: 15px;" data-addresses="<?= htmlspecialchars(json_encode($crm_addresses_json), ENT_QUOTES, 'UTF-8') ?>">
    <div class="customer-addresses-head">
        <h4><b>Customer Addresses</b></h4>
        <button type="button" class="btn btn-primary btn-sm js-add-address-btn">
            <i class="fa fa-plus"></i> Add Address
        </button>
    </div>

    <div class="crm-addr-section">
        <h5 class="crm-addr-section-title">Billing</h5>
        <div class="crm-addr-cards js-addr-cards-billing"></div>
    </div>

    <div class="crm-addr-section">
        <h5 class="crm-addr-section-title">Shipping</h5>
        <div class="crm-addr-cards js-addr-cards-shipping"></div>
    </div>

    <div class="crm-addr-section">
        <h5 class="crm-addr-section-title">Site</h5>
        <div class="crm-addr-cards js-addr-cards-site"></div>
    </div>
</div>

<select id="customer-addr-country-options-html" class="hidden">
    <option value="">Select Country</option>
    <?php if (!empty($country) && is_array($country)): ?>
        <?php foreach ($country as $country_val): ?>
            <option value="<?= htmlspecialchars((string) $country_val->name, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars((string) $country_val->name, ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
    <option value="other">Other</option>
</select>

<input type="hidden" class="js-customer-address-deleted" value="[]">

<?php $this->load->view($this->theme . 'customers/add_address_modal', array('show_edit_id' => true)); ?>
