<div class="container" id="printableArea">
<div style="text-align: center; font-weight: bold; margin-bottom: 10px;">
    <?= htmlspecialchars( $Settings->site_name ) ?>
    <!-- <sub class="subtext"><?= $Settings->site_name . " (ver. $pos_ver)" ?></sub> -->
</div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
    <div style="margin-bottom: 10px;">
    <?php if (!$this->Owner && !$this->Admin) { ?>
        <h1 style="margin: 0;"><?= htmlspecialchars($location_name) ?></h1>
    <?php } ?>
    <?php if ($is_procurement_order_ref_no): ?>
        <div style="font-size: 16px; margin-top: 4px;">Order Number: <?= htmlspecialchars($procurement_order_ref_no) ?></div>
    <?php endif; ?>
</div>
        <div>
        <div>
    <strong>Date</strong>: <?= date('d-M-y') ?> &nbsp;&nbsp; <strong>Printed @</strong>: <?= date('g:i:s A') ?>
</div>

        </div>
    </div>

    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Product</th>
            <th>Order Quantity</th>
            <th>Stock Quantity</th>
            <th>Build Quantity</th>
            <th>Unit</th>
            <?php if (!$is_kot_by_order): ?>
                <th>Outlets Requesting</th>
            <?php endif; ?>
            <th>Packing Instructions</th>
            <th>Special Instruction</th>
        </tr>
    </thead>
        <tbody>
        <?php foreach ($all_kot as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['Product']) ?></td>
            <td><?= htmlspecialchars($row['Order Quantity']) ?></td>
            <td><?= htmlspecialchars($row['Stock Quantity'] !== '' && $row['Stock Quantity'] !== null ? $row['Stock Quantity'] : '0') ?></td>
            <td><?= htmlspecialchars($row['Built Quantity']) ?></td>
            <td><?= htmlspecialchars($row['Unit']) ?></td>
            <?php if (!$is_kot_by_order): ?>
                <td><?= htmlspecialchars($row['Outlets Requesting']) ?></td>
            <?php endif; ?>
            <td><?= strip_tags($row['Packing Instructions'], '<b><i><u><br>') ?></td>
            <td><?= strip_tags($row['Special Instruction'], '<b><i><u><br>') ?></td>
        </tr>
        <!-- Empty row after each record -->
        <tr>
            <td colspan="<?= $is_kot_by_order ? 7 : 8 ?>">&nbsp;</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
</div>
