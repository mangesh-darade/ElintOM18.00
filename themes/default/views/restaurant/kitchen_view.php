<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kitchen View #<?= (int)$order->id ?> - Table <?= htmlspecialchars($order->table_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { font-size: 14px; background: #f5f6f8; }
        .ticket { max-width: 900px; margin: 24px auto; background: #fff; padding: 16px; border-radius: 8px; }
        .kot-item { border-bottom: 1px dashed #ccc; padding: 8px 0; }
    </style>
</head>
<body>
    <div class="ticket">
        <h4 class="mb-3">Kitchen View — Order #<?= (int)$order->id ?> (Table <?= htmlspecialchars($order->table_name) ?>)</h4>
        <?php if (!empty($items)) { foreach ($items as $it) { ?>
            <div class="kot-item">
                <strong><?= htmlspecialchars($it->product_name ?? 'Item') ?></strong>
                x <?= htmlspecialchars((string)$it->quantity) ?>
                <?php if (!empty($it->allergy_names) && is_array($it->allergy_names)) { ?>
                    <div class="small text-muted">Allergies: <?= htmlspecialchars(implode(', ', $it->allergy_names)) ?></div>
                <?php } ?>
            </div>
        <?php } } else { ?>
            <p class="text-muted">No items on this order.</p>
        <?php } ?>
    </div>
</body>
</html>
