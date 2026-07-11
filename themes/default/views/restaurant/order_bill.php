<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Bill #<?= htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Segoe UI", Arial, sans-serif;
        }
        body {
            background: #f5f6fb;
            margin: 0;
            padding: 24px;
            display: flex;
            justify-content: center;
        }
        .bill-wrapper {
            background: #ffffff;
            width: 100%;
            max-width: 720px;
            border-radius: 18px;
            box-shadow: 0 30px 60px rgba(17, 24, 39, 0.12);
            padding: 32px;
        }
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }
        .bill-header h1 {
            font-size: 22px;
            margin: 0 0 4px 0;
            color: #1f2937;
        }
        .bill-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .bill-meta {
            margin-bottom: 24px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 14px;
            padding: 16px 20px;
            background: #f8fafc;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            font-size: 13px;
            color: #374151;
        }
        .bill-meta span {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .bill-meta label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        th, td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            font-size: 14px;
        }
        th {
            font-weight: 600;
            color: #1f2937;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        .guest-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 600;
            margin-top: 4px;
        }
        .item-notes {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }
        .summary {
            margin-left: auto;
            width: 280px;
        }
        .summary div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            color: #374151;
        }
        .summary div.total {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            border-top: 1px solid rgba(148, 163, 184, 0.3);
            padding-top: 12px;
        }
        .bill-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 36px;
        }
        .bill-footer small {
            color: #6b7280;
        }
        .btn-print {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 10px 22px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.25);
        }
        @media print {
            body {
                padding: 0;
                background: #ffffff;
            }
            .bill-wrapper {
                box-shadow: none;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="bill-wrapper">
        <div class="bill-header">
            <div>
                <h1><?= isset($Settings->site_name) ? htmlspecialchars($Settings->site_name, ENT_QUOTES, 'UTF-8') : 'Restaurant Bill'; ?></h1>
                <p>Pre-Finalization Bill</p>
            </div>
            <div style="text-align:right;">
                <p style="font-weight:600; color:#111827;">Order #<?= htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'); ?></p>
                <p><?= htmlspecialchars($generated_at, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <div class="bill-meta">
            <span>
                <label>Table</label>
                <?= isset($order->table_name) && $order->table_name !== null
                    ? htmlspecialchars($order->table_name, ENT_QUOTES, 'UTF-8')
                    : 'Table #' . htmlspecialchars($order->res_tables_id, ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span>
                <label>Status</label>
                <?= isset($order->status) ? htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8') : 'Active'; ?>
            </span>
            <span>
                <label>Guests</label>
                <?= isset($order->guest_count) ? (int) $order->guest_count : count($guests); ?>
            </span>
            <span>
                <label>Total Items</label>
                <?= isset($totals['item_count']) ? (int) $totals['item_count'] : 0; ?>
            </span>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40%">Item</th>
                    <th width="15%">Guest</th>
                    <th width="15%" style="text-align:center;">Qty</th>
                    <th width="15%" style="text-align:right;">Price</th>
                    <th width="15%" style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <?php
                            $guestNumber = isset($item->guest_display_number) && $item->guest_display_number
                                ? 'Guest ' . (int) $item->guest_display_number
                                : 'All Guests';
                            $notes = array();
                            if (!empty($item->add_on_names)) { $notes[] = 'Add-ons: ' . implode(', ', $item->add_on_names); }
                            if (!empty($item->topping_names)) { $notes[] = 'Toppings: ' . implode(', ', $item->topping_names); }
                            if (!empty($item->allergy_names)) { $notes[] = 'Allergies: ' . implode(', ', $item->allergy_names); }
                            if (!empty($item->special_instructions)) { $notes[] = $item->special_instructions; }
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <?php if (!empty($notes)): ?>
                                    <div class="item-notes"><?= htmlspecialchars(implode(' | ', $notes), ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="guest-tag"><?= htmlspecialchars($guestNumber, ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td style="text-align:center;"><?= $this->sma->formatDecimal($item->quantity, isset($Settings->qty_decimals) ? (int) $Settings->qty_decimals : 0); ?></td>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($item->price); ?></td>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($item->amount); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px 0; color:#9ca3af;">No items have been added yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="summary">
            <div>
                <span>Subtotal</span>
                <?php $subtotal = isset($totals['subtotal']) ? $totals['subtotal'] : 0; ?>
                <span><?= $this->sma->formatMoney($subtotal); ?></span>
            </div>
            <div>
                <span>SGST</span>
                <?php $sgst = isset($totals['sgst']) ? $totals['sgst'] : 0; ?>
                <span><?= $this->sma->formatMoney($sgst); ?></span>
            </div>
            <div>
                <span>CGST</span>
                <?php $cgst = isset($totals['cgst']) ? $totals['cgst'] : 0; ?>
                <span><?= $this->sma->formatMoney($cgst); ?></span>
            </div>
            <div class="total">
                <span>Grand Total</span>
                <?php $grand_total = isset($totals['grand_total']) ? $totals['grand_total'] : ($subtotal + $sgst + $cgst); ?>
                <span><?= $this->sma->formatMoney($grand_total); ?></span>
            </div>
        </div>

        <div class="bill-footer">
            <small>Thank you for dining with us.</small>
            <button class="btn-print" onclick="window.print()">Print Order Bill</button>
        </div>
    </div>
    <script>
        (function () {
            var returnUrl = '<?= site_url('Restaurant_Order_Taking/order_screen/' . $order->id); ?>';
            function goBack() {
                if (returnUrl) {
                    window.location.href = returnUrl;
                }
            }
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    setTimeout(function () {
                        window.print();
                    }, 150);
                } catch (err) {
                    if (window.console && console.warn) {
                        console.warn('Auto print failed', err);
                    }
                }
            });
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function (mql) {
                    if (!mql.matches) {
                        goBack();
                    }
                });
            }
            window.onafterprint = goBack;
        })();
    </script>
</body>
</html>

