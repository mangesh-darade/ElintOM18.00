<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$billing_text  = isset($billing_address_text) ? trim($billing_address_text) : '';
$shipping_text = isset($shipping_address_text) ? trim($shipping_address_text) : '';
if ($billing_text === '' && $shipping_text === '') {
    return;
}
?>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <?php if ($billing_text !== '') { ?>
            <td style="width: 48%; vertical-align: top; padding-right: 25px;">
                <strong style="font-weight: bold"><?= lang('Billing Address'); ?>:</strong>
                <?= nl2br(htmlspecialchars($billing_text, ENT_QUOTES, 'UTF-8')); ?>
            </td>
        <?php } ?>
        <?php if ($shipping_text !== '') { ?>
            <td style="width: 48%; vertical-align: top; padding-left: 25px;">
                <strong style="font-weight: bold"><?= lang('Shipping Address'); ?>:</strong>
                <?= nl2br(htmlspecialchars($shipping_text, ENT_QUOTES, 'UTF-8')); ?>
            </td>
        <?php } ?>
    </tr>
</table>
