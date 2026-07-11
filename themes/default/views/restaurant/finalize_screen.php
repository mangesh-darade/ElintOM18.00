<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalize Order - ELINTOM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-pink: #e91e63;
            --brand-pink-light: #f8c6d8;
            --brand-pink-border: #f8d7da;
            --success-green: #d1e7dd;
            --success-green-text: #0f5132;
        }

        body {
            background: #ffffff;
            font-family: 'Inter', sans-serif;
            color: #333;
            margin: 0;
            padding-bottom: 120px;
        }

        /* HEADER */
        .header {
            background-color: var(--brand-pink);
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }
        .header-date { font-size: 0.85rem; line-height: 1.2; }
        .header-logo img, .header-logo span { font-weight: 700; font-size: 1.2rem; letter-spacing: 1px; }
        .header-menu { font-size: 1.5rem; cursor: pointer; }

        .container-xl { max-width: 900px; padding-top: 20px; }

        /* SUCCESS BANNER */
        .success-banner {
            background-color: var(--success-green);
            color: var(--success-green-text);
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* CONTROLS */
        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        .control-group { display: flex; align-items: center; gap: 15px; }
        .control-label { font-weight: 600; font-size: 1.1rem; }
        
        .table-select {
            border: 1px solid var(--brand-pink);
            border-radius: 10px;
            padding: 8px 15px;
            font-weight: 500;
            background: white;
            min-width: 120px;
        }

        .guest-counter {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .guest-btn {
            background-color: var(--brand-pink);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .guest-count-display { font-size: 1.4rem; font-weight: 600; min-width: 25px; text-align: center; }

        .section-title {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        /* GUEST LIST / ITEM CARDS */
        .guest-section { margin-bottom: 15px; }
        .guest-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .guest-info { display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .guest-icon { font-size: 1.2rem; color: #666; }
        .guest-actions { display: flex; gap: 10px; align-items: center; }
        .btn-add-item { color: var(--brand-pink); background: none; border: none; font-size: 1.4rem; padding: 0; line-height: 1; }
        .btn-expand { background: none; border: none; font-size: 1.2rem; color: #333; padding: 0; line-height: 1; }

        .order-item-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }
        .item-meta-icons { display: flex; align-items: center; gap: 5px; margin-bottom: 3px; flex-wrap: wrap; }
        .item-info { flex: 1; }
        .item-name { font-weight: 600; display: block; margin-bottom: 4px; }
        .item-icons { display: flex; gap: 5px; margin-bottom: 5px; }
        
        .qty-control {
            border: 1px solid var(--brand-pink);
            border-radius: 8px;
            display: flex;
            align-items: center;
            overflow: hidden;
            height: 36px;
        }
        .btn-qty {
            background: white;
            border: none;
            color: var(--brand-pink);
            width: 36px;
            height: 100%;
            font-weight: bold;
        }
        .qty-val { padding: 0 12px; font-weight: 700; min-width: 35px; text-align: center; }

        /* FOOTER */
        .footer-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            display: flex;
            gap: 15px;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
            max-width: 900px;
            margin: 0 auto;
        }
        .btn-back {
            flex: 1;
            border: 1px solid var(--brand-pink);
            color: var(--brand-pink);
            background: white;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px;
        }
        .total-box {
            flex: 1.5;
            background: var(--brand-pink-light);
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .btn-complete {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 700;
            margin: 20px auto;
            display: block;
            width: 200px;
        }

        /* ICONS STYLES */
        .veg-icon-box { width:16px; height:16px; border:1px solid; border-radius: 2px; display:inline-flex; align-items:center; justify-content:center; }
        .veg-icon-box.veg { border-color: #28a745; }
        .veg-icon-box.non-veg { border-color: #dc3545; }
        .dot { width:8px; height:8px; border-radius:50%; }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-date">
            Friday<br>April 11, 2025
        </div>
        <div class="header-logo">
            ELINTOM
        </div>
        <div class="header-menu">
            <i class="bi bi-list"></i>
        </div>
    </div>

    <div class="container-xl mt-4">
        
        <div class="success-banner">
            <i class="bi bi-check-circle-fill"></i> Order Confirmed
        </div>

        <div class="controls-row">
            <div class="control-group">
                <span class="control-label">Table</span>
                <select class="table-select" id="table-select">
                    <?php if(isset($table)): ?>
                        <option value="<?= $table->id ?>" selected><?= $table->name ?></option>
                    <?php else: ?>
                        <option value="">No Table</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="control-group">
                <span class="control-label">Guests</span>
                <div class="guest-counter">
                    <button class="guest-btn">-</button>
                    <span class="guest-count-display"><?= $order->guest_count ?></span>
                    <button class="guest-btn">+</button>
                </div>
            </div>
        </div>

        <div class="section-title">
            Order Items (<?= count($items) ?> Items)
        </div>

        <!-- Guest List Container -->
        <div id="guest-list-container">
            <!-- Populated via JS -->
        </div>

        <!-- <button class="btn-complete" onclick="completeSale()">
            Complete Sale
        </button> -->

    </div>

    <div class="footer-actions">
        <button class="btn-back" onclick="window.history.back()">
            Back to Order
        </button>
        <div class="total-box">
            <span>Total Amount:</span>
            <span>$<?= number_format($totals['grand_total'], 2) ?></span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        window.site = { base_url: '<?= base_url() ?>' };
        const site = window.site;
        const serverOrderItems = <?= json_encode($items) ?>;
        const serverGuests = <?= json_encode($guests) ?>;
        const guestCount = <?= (int)$order->guest_count ?>;

        function renderGuestList(count) {
            const container = $('#guest-list-container');
            container.empty();

            // 1. "All" Row
            const allRow = `
                <div class="guest-section">
                    <div class="guest-row" onclick="toggleItems('all')">
                        <div class="guest-info">
                            <i class="bi bi-people-fill guest-icon"></i>
                            <span class="guest-name">All</span>
                        </div>
                        <div class="guest-actions">
                            <button class="btn-add-item" onclick="event.stopPropagation(); backToTables('all')"><i class="bi bi-plus-square"></i></button>
                            <button class="btn-expand" id="exp-all"><i class="bi bi-chevron-up"></i></button>
                        </div>
                    </div>
                    <div class="guest-items-container" id="guest-items-all">
                        ${renderGuestItems('all', 0)}
                    </div>
                </div>
            `;
            container.append(allRow);

            // 2. Individual Guest Rows
            for (let i = 1; i <= count; i++) {
                const guestId = i.toString();
                let realGuestId = guestId;
                if (serverGuests && serverGuests.length >= i) {
                    realGuestId = serverGuests[i-1].id;
                }

                const row = `
                    <div class="guest-section">
                        <div class="guest-row" onclick="toggleItems('${realGuestId}')">
                            <div class="guest-info">
                                <i class="bi bi-person-fill guest-icon"></i>
                                <span class="guest-name">${i}</span>
                            </div>
                            <div class="guest-actions">
                                <button class="btn-add-item" onclick="event.stopPropagation(); backToTables('${realGuestId}')"><i class="bi bi-plus-square"></i></button>
                                <button class="btn-expand" id="exp-${realGuestId}"><i class="bi bi-chevron-down"></i></button>
                            </div>
                        </div>
                        <div class="guest-items-container" id="guest-items-${realGuestId}" style="display:none;">
                            ${renderGuestItems(realGuestId, i)}
                        </div>
                    </div>
                `;
                container.append(row);
            }
        }

        function renderGuestItems(guestId, guestIndex) {
            const items = serverOrderItems.filter(item => {
                if (guestId === 'all') {
                    return true;
                }
                if (item.guest_display_number && parseInt(item.guest_display_number) === guestIndex) return true;
                if (item.res_orders_guests_id == guestId) return true;
                if (item.res_orders_guests_id == guestIndex) return true;
                return false;
            });

            if (items.length === 0) return '';

            let html = '<div class="guest-items-list">';
            items.forEach(item => {
                const metaIcons = [];
                // 1. Allergies
                let allergyIconHtml = '';
                if (item.sma_res_common_allergies_list || item.custom_allergies_text) {
                    allergyIconHtml = `<i class="bi bi-exclamation-triangle-fill text-danger fs-4 mb-1 d-block"></i>`;
                }
                
                // 2. Veg/Non-veg
                const isVegItem = (item.meal_type_name || '').toLowerCase() === 'veg' || item.meal_type_id == 1;
                const vegIconFile = isVegItem ? 'veg_icon.svg' : 'non-veg_icon.svg';
                metaIcons.push(`
                    <div class="veg-icon-box ${isVegItem?'veg':'non-veg'}" style="width:20px;height:20px;border:1px solid ${isVegItem?'#28a745':'#dc3545'};padding:1px;display:inline-flex;align-items:center;justify-content:center;margin-right:8px; border-radius: 4px; background: #fff;">
                        <img src="${site.base_url}themes/default/assets/restaurant/images/${vegIconFile}" width="14" height="14">
                    </div>
                `);

                // 3. Garlic
                if (item.garlic_flag == 1) {
                     metaIcons.push(`
                        <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 6px; border:1px solid #ddd; height: 26px;">
                            <img src="${site.base_url}themes/default/assets/restaurant/images/garlic_icon.svg" width="16" height="16">
                            <img src="${site.base_url}themes/default/assets/restaurant/images/toggle-on.svg" width="22" class="ms-1">
                        </div>
                     `);
                }

                // 4. Onion
                if (item.onion_flag == 1) {
                     metaIcons.push(`
                        <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 6px; border:1px solid #ddd; height: 26px;">
                            <img src="${site.base_url}themes/default/assets/restaurant/images/onion_icon.svg" width="16" height="16">
                            <img src="${site.base_url}themes/default/assets/restaurant/images/toggle-on.svg" width="22" class="ms-1">
                        </div>
                     `);
                }

                // 5. Spice Level
                if (item.spice_level) {
                    let spicyIcon = 'low_spicy_icon.svg';
                    const sl = item.spice_level.toLowerCase();
                    if (sl.includes('medium')) spicyIcon = 'medium_spicy_icon.svg';
                    if (sl.includes('high') || sl.includes('spicy') || sl.includes('hot')) spicyIcon = 'high_spicy_icon.svg';
                    metaIcons.push(`
                         <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 8px; border:1px solid #ddd; height: 26px;">
                            <img src="${site.base_url}themes/default/assets/restaurant/images/${spicyIcon}" height="16">
                         </div>
                    `);
                }

                html += `
                    <div class="order-item-card">
                        <div class="item-info">
                            ${allergyIconHtml}
                            <div class="item-meta-icons d-flex align-items-center mb-1">
                                ${metaIcons.join('')}
                            </div>
                            <span class="item-name">${item.product_name}</span>
                        </div>
                        <div class="qty-control">
                            <button class="btn-qty">-</button>
                            <span class="qty-val">${parseFloat(item.quantity)}</span>
                            <button class="btn-qty">+</button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            return html;
        }

        function toggleItems(guestId) {
            const container = $(`#guest-items-${guestId}`);
            const icon = $(`#exp-${guestId} i`);
            container.slideToggle(200);
            if (icon.hasClass('bi-chevron-down')) {
                icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            } else {
                icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
            }
        }

        function completeSale() {
            $.ajax({
                url: '<?= base_url("Restaurant_Order_Taking/finalize_order") ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    order_id: <?= $order->id ?>,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                },
                success: function(resp) {
                    if(resp.status === 'success'){
                        window.location.href = resp.invoice_url;
                    } else {
                        alert(resp.message);
                    }
                }
            });
        }

        function backToTables(guestId = 'all') {
            const tid = '<?= isset($table->id) ? $table->id : "" ?>';
            const orderId = '<?= isset($order->id) ? $order->id : "" ?>';
            if (orderId) {
                window.location.href = '<?= base_url("Restaurant_Order_Taking/orders/") ?>' + tid + '?guest_id=' + guestId;
            } else {
                window.location.href = '<?= base_url("Restaurant_Order_Taking/orders/") ?>' + tid;
            }
        }

        $(document).ready(function() {
            renderGuestList(guestCount);
            setTimeout(() => {
                $('.success-banner').fadeOut(500);
            }, 5000);

            // Redirect to tables section after 7 seconds
            setTimeout(() => {
                <?php 
                $sec_id = isset($table->section_id) ? $table->section_id : "";
                $sub_id = isset($table->subsection_id) ? $table->subsection_id : "";
                ?>
                window.location.href = '<?= base_url("Restaurant_Order_Taking/tables/" . $sec_id . "/" . $sub_id) ?>';
            }, 7000);
        });
    </script>

</body>
</html>