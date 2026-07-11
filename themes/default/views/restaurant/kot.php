<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>KOT #<?= (int)$order->id ?> - Table <?= htmlspecialchars($order->table_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php /* Paper size selection removed; printing defaults to A4 */ ?>
    <style>
        /* Screen defaults */
        html, body { font-size: 12px; background: #fff; }
        .ticket { margin: 0 auto; }
        /* Full-screen center on screen */
        @media screen {
            html, body { height: 100%; background: #f5f6f8; }
            /* Top-center alignment */
            body { min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 24px 16px 16px; }
            .ticket { background: #fff; padding: 12px; border-radius: 6px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        }
        /* Removed fixed mm widths; using flexible layout for A4 print */
        .kot-header { border-bottom: 2px dashed #000; padding-bottom: 6px; margin-bottom: 8px; }
        .kot-item { border-bottom: 1px dashed #000; padding: 6px 0; }
        .mono { color: #000 !important; background: #fff !important; }
        .badge-line { font-size: 10px; border: 1px solid #000; padding: 0 3px; border-radius: 2px; background: #fff !important; color: #000 !important; }
        .section-muted { background: #f7f7f7; border: 1px solid #ddd; border-radius: 4px; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .title { font-weight: 700; }
        .kot-item, .section-muted { page-break-inside: avoid; }

        /* Print optimizations */
        @media print {
            body { display: block !important; }
            /* Fluid print to fit any printer width (58mm/80mm/A4) */
            html, body { padding: 0; margin: 0; background: #fff; }
            @page { size: auto; margin: 2mm; }
            /* Use full printable width; browser/driver will fit to device */
            body { width: 100% !important; text-align: center !important; }
            .ticket { display: inline-block; width: 100% !important; max-width: 100% !important; text-align: left; box-shadow: none !important; border-radius: 0; }
            .no-print, .paper-note { display: none !important; }
            .ticket { margin: 0; }
            /* Compact typography for receipts but readable on A4 */
            .kot-header { margin-bottom: 6px; padding-bottom: 4px; }
            .kot-item { padding: 4px 0; }
            /* Force monochrome-friendly badges */
            .badge, .badge-line { border: 1px solid #000 !important; background: #fff !important; color: #000 !important; }
            /* Reduce spacing for compact print */
            /* Better color fidelity on thermal */
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <script>
        function printKot(){ window.print(); }
        document.addEventListener('DOMContentLoaded', function(){
            setTimeout(function(){ printKot(); }, 150);
        });
        if (window.matchMedia) {
            var mql = window.matchMedia('print');
            mql.addListener(function(e){
                if (!e.matches) {
                    window.close();
                }
            });
        }
        window.onafterprint = function(){ window.close(); };
    </script>
</head>
<body class="p-3">
    <div class="paper-note text-center mb-2">Paper: 58mm 80mm</div>
    <div class="ticket">
    <div class="d-flex justify-content-between align-items-center kot-header">
        <div>
            <div class="fw-bold">Kitchen Order Ticket</div>
            <div>KOT #: <?= (int)$order->id ?></div>
            <div>Table: <?= htmlspecialchars($order->table_name) ?></div>
            <div>Guests: <?= (int)$order->guest_count ?></div>
        </div>
        <div class="text-end">
            <div><?= htmlspecialchars($generated_at) ?></div>
            <div class="no-print mt-1">
                <button class="btn btn-sm btn-dark" onclick="printKot()">Print</button>
            </div>
        </div>
    </div>

    <?php if (!empty($items)): ?>
        <?php foreach ($items as $it): ?>
            <div class="kot-item">
                <div class="d-flex justify-content-between">
                    <div class="fw-bold">
                        <?= htmlspecialchars($it->product_name) ?>
                        <span class="ms-2">x <?= (int)$it->quantity ?></span>
                    </div>
                    <div>
                        <?php if (!empty($it->guest_display_number)): ?>
                            <span class="badge-line">Guest <?= (int)$it->guest_display_number ?></span>
                        <?php endif; ?>
                        <?php if (!empty($it->spice_level)): ?>
                            <span class="badge-line">Spice: <?= htmlspecialchars($it->spice_level) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($it->meat_wellness_type)): ?>
                            <span class="badge-line">Meat: <?= htmlspecialchars($it->meat_wellness_type) ?></span>
                        <?php endif; ?>
                        <?php if (isset($it->onion_flag) && $it->onion_flag == 0): ?>
                            <span class="badge-line">No Onion</span>
                        <?php endif; ?>
                        <?php if (isset($it->garlic_flag) && $it->garlic_flag == 0): ?>
                            <span class="badge-line">No Garlic</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                    // Prepare instructions without embedded allergy tags; render later below allergies
                    $instructions_display = '';
                    if (!empty($it->instructions_line)) {
                        $instructions_display = preg_replace('/\[Allergies:\s*[^\]]+\]/i', '', (string)$it->instructions_line);
                        $instructions_display = trim(preg_replace('/\s{2,}/', ' ', $instructions_display));
                    }
                ?>
                <?php
                    // Build combined allergy list: preset names + custom allergies text + parsed tags in special instructions
                    $allergy_display_names = isset($it->allergy_names) && is_array($it->allergy_names) ? $it->allergy_names : [];
                    if (!empty($it->custom_allergies_text)) {
                        $extra = array_filter(array_map('trim', explode('|', (string)$it->custom_allergies_text)));
                        if (!empty($extra)) { $allergy_display_names = array_merge($allergy_display_names, $extra); }
                    }
                    if (!empty($it->special_instructions) && preg_match('/\[Allergies:\s*([^\]]+)\]/i', (string)$it->special_instructions, $m)) {
                        $extra2 = array_filter(array_map('trim', explode(',', $m[1])));
                        if (!empty($extra2)) { $allergy_display_names = array_merge($allergy_display_names, $extra2); }
                    }
                    // De-duplicate
                    if (!empty($allergy_display_names)) { $allergy_display_names = array_values(array_unique($allergy_display_names)); }
                ?>
                <?php if (!empty($it->add_on_names) || !empty($it->topping_names) || !empty($allergy_display_names)): ?>
                    <div class="mt-1">
                        <?php if (!empty($it->add_on_names)): ?>
                            <div><small>Add-ons:</small> <?= htmlspecialchars(implode(', ', $it->add_on_names)) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($it->topping_names)): ?>
                            <div><small>Toppings:</small> <?= htmlspecialchars(implode(', ', $it->topping_names)) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($allergy_display_names)): ?>
                            <div><small>Allergies:</small> <?= htmlspecialchars(implode(', ', $allergy_display_names)) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($instructions_display)): ?>
                    <div class="mt-1 p-2 section-muted">
                        <strong>Special Instructions:</strong>
                        <div><?= htmlspecialchars($instructions_display) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No items to print.</div>
    <?php endif; ?>

    <div class="mt-3 small">
        Status: <?= htmlspecialchars($order->status) ?> , Type: <?= htmlspecialchars($order->order_type) ?>
    </div>
    </div>
</body>
</html>

