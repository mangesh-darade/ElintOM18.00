<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDS - Kitchen Display System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=SF+Pro+Display:wght@500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background: #0F0F0F;
        }

        /* Main Container */
        .main-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            background: #0F0F0F;
            margin: 0;
            overflow: hidden;
        }

        /* Status Bar */
        .status-bar {
            position: absolute;
            width: 100%;
            height: 24px;
            left: 0px;
            top: 0px;
            background: #000000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            color: #FFFFFF;
            font-size: 12px;
        }

        .status-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .status-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Top Bar */
        .top-bar-rect {
            position: absolute;
            width: 100%;
            height: 68px;
            left: 0px;
            top: 24px;
            background: #1A1A1A;
            border-bottom: 1px solid #333333;
        }

        /* Logo */
        .logo-container {
            position: absolute;
            width: 140px;
            height: 40px;
            left: 20px;
            top: 43px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .logo-container img {
            max-height: 40px;
            width: auto;
            display: block;
        }

        /* Time Display */
        .time-display {
            position: absolute;
            width: 100px;
            height: 30px;
            left: 50%;
            transform: translateX(-50%);
            top: 48px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 18px;
            color: #FFFFFF;
            text-align: center;
            line-height: 30px;
        }

        /* Menu Icon */
        .menu-icon {
            position: absolute;
            width: 30px;
            height: 30px;
            right: 20px;
            top: 48px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M3 12h18M3 6h18M3 18h18" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>') no-repeat center;
            background-size: 24px 24px;
            cursor: pointer;
        }

        /* Orders Container */
        .orders-container {
            position: absolute;
            width: 100%;
            height: calc(100vh - 102px);
            left: 0px;
            top: 102px;
            /* Allow vertical scroll, show multiple rows */
            overflow-y: auto;
            overflow-x: hidden;
            background: #0F0F0F;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            padding: 20px;
        }

        /* Order Ticket Base Styles */
        .order-ticket {
            position: relative;
            /* Exactly 4 cards per row: each takes one quarter minus gaps */
            flex: 0 0 calc((100% - 3 * 16px) / 4);
            max-width: calc((100% - 3 * 16px) / 4);
            background: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: none;
            margin-bottom: 16px;
        }

        /* Show only first 8 orders (4 in first row, 4 in second) */
        .order-ticket:nth-child(n+9) {
            display: none;
        }

        /* Order Header Styles */
        .order-header {
            width: 100%;
            background: #FFFFFF;
            border-bottom: 1px solid #E9ECEF;
        }

        .order-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 20px;
            background: #00C896;
            color: #FFFFFF;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
        }

        .order-header-top.blue {
            background: #2196F3;
        }

        .order-header-top.white {
            background: #6C757D;
        }

        .order-header-top.orange {
            background: #FF9A63;
        }

        .order-time-badge {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 4px 12px;
            font-size: 14px;
            font-weight: 500;
            min-width: 60px;
            text-align: center;
        }

        .order-header-bottom {
            display: flex;
            align-items: center;
            padding: 13.5px 20px;
            background: #F8F9FA;
            gap: 8px;
        }

        .table-info {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .table-icon {
            width: 32px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .table-number {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #000000;
        }

        .staff-customer {
            text-align: right;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            color: #6C757D;
            min-width: 120px;
        }

        .staff-customer div {
            margin-bottom: 2px;
        }

        .staff-customer div:last-child {
            margin-bottom: 0;
        }

        /* Order Items */
        .order-items {
            padding: 0;
        }

        .order-item {
            display: flex;
            border-bottom: 1px solid #22272E;
            min-height: 34px;
            background: #FFFFFF;
        }

        .order-item.status-red {
            border-left: 28px solid #FF3B47;
        }

        .order-item.status-yellow {
            border-left: 28px solid #FFD97A;
        }

        .order-item.status-white {
            border-left: 28px solid #FFFFFF;
        }

        .order-item.status-orange {
            border-left: 28px solid #FF9A63;
        }

        .order-item.status-green {
            border-left: 28px solid #00C896;
        }

        .order-item.status-blue {
            border-left: 28px solid #2196F3;
        }

        .order-item-content {
            flex: 1;
            padding: 8px 12px 8px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .item-quantity {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 400;
            color: #000000;
            margin-bottom: 5px;
        }

        .item-name {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 400;
            color: #000000;
            margin-bottom: 2px;
        }

        .item-modifiers {
            font-family: 'Inter', sans-serif;
            font-size: 9px;
            color: #000000;
            line-height: 1.4;
        }

        .item-modifier {
            margin-bottom: 1px;
        }

        .item-action {
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 15px;
        }

        .item-action img {
            width: 34px;
            height: 30px;
            object-fit: contain;
        }

        /* Serve Button */
        .serve-button-container {
            padding: 10px 12px;
            background: #FFFFFF;
            border-top: 1px solid #E9ECEF;
        }

        .serve-button {
            width: 100%;
            padding: 10px;
            background: #00C896;
            border: 1px solid #000000;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 20px;
            font-weight: 400;
            text-align: center;
            color: #000000;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .serve-button:hover {
            background: #00B085;
        }
    </style>
</head>
<body>
<div class="main-container">
    <!-- Status Bar -->
    <!-- <div class="status-bar">
        <div class="status-left">
            <span>Production Unit</span>
            <span id="status-time-left">11:27</span>
        </div>
        <div class="status-right">
            <span>Orders: 6</span>
            <span id="status-time-right">11:27:55</span>
        </div>
    </div> -->

    <!-- Top Bar Rectangle -->
    <div class="top-bar-rect"></div>

    <!-- Logo -->
    <div class="logo-container">
        <img src="<?= base_url('assets/images/ElintOm_Logo_png.png'); ?>" alt="Elintom Logo">
    </div>

    <!-- Time Display -->
    <div class="time-display" id="current-time">11:27:55</div>

    <!-- Menu Icon -->
    <div class="menu-icon"></div>

    <!-- Orders Container -->
    <div class="orders-container">
        <!-- Order #12567 -->
        <div class="order-ticket">
            <div class="order-header">
                <div class="order-header-top">
                    <span>Order #12567</span>
                    <div class="order-time-badge">08:42</div>
                </div>
                <div class="order-header-bottom">
                    <div class="table-info">
                        <div class="table-icon">
                            <img src="<?= site_url('assets/images/diein.png') ?>" alt="dinein">
                        </div>
                        <div class="table-number">Table 08</div>
                    </div>
                    <div class="staff-customer">
                        <div>S: Michael R.</div>
                        <div>C: Sarah</div>
                    </div>
                </div>
            </div>
            <div class="order-items">
                <div class="order-item status-red">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Jumbo BLT Sandwich</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra lettuce</div>
                            <div class="item-modifier">– No tomato</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/bell_notification.png'); ?>" alt="New Order">
                    </div>
                </div>
                <div class="order-item status-green">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Chicken Sandwich</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-blue">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Shrimp Tempura Sushi</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-orange">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Iced Cappuccino</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra espresso shot</div>
                            <div class="item-modifier">– No sugar</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Tom Yum</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
            </div>
            <div class="serve-button-container">
                <button class="serve-button">Serve & Complete</button>
            </div>
        </div>

        <!-- Order #12568 -->
        <div class="order-ticket">
            <div class="order-header">
                <div class="order-header-top blue">
                    <span>Order #12568</span>
                    <div class="order-time-badge">12:25</div>
                </div>
                <div class="order-header-bottom">
                    <div class="table-info">
                        <div class="table-icon">
                            <img src="<?= site_url('assets/images/diein.png') ?>" alt="dinein">
                        </div>
                        <div class="table-number">Table 14</div>
                    </div>
                    <div class="staff-customer">
                        <div>S: Jane D.</div>
                        <div>C: David</div>
                    </div>
                </div>
            </div>
            <div class="order-items">
                <div class="order-item status-red">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Jumbo BLT Sandwich</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra lettuce</div>
                            <div class="item-modifier">– No tomato</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/bell_notification.png'); ?>" alt="New Order">
                    </div>
                </div>
                <div class="order-item status-orange">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Chicken Sandwich</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-green">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Iced Cappuccino</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra espresso shot</div>
                            <div class="item-modifier">– No sugar</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-blue">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Tom Yum</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
            </div>
        </div>

        <!-- Order #12569 -->
        <div class="order-ticket">
            <div class="order-header">
                <div class="order-header-top white">
                    <span>Order #12569</span>
                    <div class="order-time-badge">07:06</div>
                </div>
                <div class="order-header-bottom">
                    <div class="table-info">
                        <div class="table-icon">
                            <img src="<?= site_url('assets/images/diein.png') ?>" alt="dinein">
                        </div>
                        <div class="table-number">Table 10</div>
                    </div>
                    <div class="staff-customer">
                        <div>S: Jane D.</div>
                        <div>C: David</div>
                    </div>
                </div>
            </div>
            <div class="order-items">
                <div class="order-item status-red">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Jumbo BLT Sandwich</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra lettuce</div>
                            <div class="item-modifier">– No tomato</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/bell_notification.png'); ?>" alt="New Order">
                    </div>
                </div>
                <div class="order-item status-white">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Chicken Sandwich</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-white">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Shrimp Tempura Sushi</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Iced Cappuccino</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra espresso shot</div>
                            <div class="item-modifier">– No sugar</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Tom Yum</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Tom Kha Kai</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
            </div>
        </div>

        <!-- Order #12570 -->
        <div class="order-ticket">
            <div class="order-header">
                <div class="order-header-top orange">
                    <span>Order #12570</span>
                    <div class="order-time-badge">04:56</div>
                </div>
                <div class="order-header-bottom">
                    <div class="table-info">
                        <div class="table-icon">
                            <img src="<?= site_url('assets/images/diein.png') ?>" alt="dinein">
                        </div>
                        <div class="table-number">Table 7</div>
                    </div>
                    <div class="staff-customer">
                        <div>S: Jane D.</div>
                        <div>C: David</div>
                    </div>
                </div>
            </div>
            <div class="order-items">
                <div class="order-item status-orange">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Chicken Sandwich</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-orange">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Shrimp Tempura Sushi</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Iced Cappuccino</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra espresso shot</div>
                            <div class="item-modifier">– No sugar</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Tom Yum</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
            </div>
            <div class="serve-button-container">
                <button class="serve-button">Serve & Complete</button>
            </div>
        </div>

        <!-- Order #12571 -->
        <div class="order-ticket">
            <div class="order-header">
                <div class="order-header-top white">
                    <span>Order #12571</span>
                    <div class="order-time-badge">01:48</div>
                </div>
                <div class="order-header-bottom">
                    <div class="table-info">
                        <div class="table-icon">
                            <img src="<?= site_url('assets/images/diein.png') ?>" alt="dinein">
                        </div>
                        <div class="table-number">Table 5</div>
                    </div>
                    <div class="staff-customer">
                        <div>S: Jane D.</div>
                        <div>C: David</div>
                    </div>
                </div>
            </div>
            <div class="order-items">
                <div class="order-item status-white">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Chicken Sandwich</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-white">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Shrimp Tempura Sushi</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
            </div>
        </div>

        <!-- Order #12572 -->
        <div class="order-ticket">
            <div class="order-header">
                <div class="order-header-top white">
                    <span>Order #12572</span>
                    <div class="order-time-badge">00:27</div>
                </div>
                <div class="order-header-bottom">
                    <div class="table-info">
                        <div class="table-icon">
                            <img src="<?= site_url('assets/images/delivery.png') ?>" alt="Delivery">
                        </div>
                        <div class="table-number">Takeaway</div>
                    </div>
                    <div class="staff-customer">
                        <div>C: David</div>
                    </div>
                </div>
            </div>
            <div class="order-items">
                <div class="order-item status-white">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Chicken Sandwich</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Cooking">
                    </div>
                </div>
                <div class="order-item status-white">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Shrimp Tempura Sushi</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Food_tray.png'); ?>" alt="Ready to Serve">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">2</div>
                        <div class="item-name">Iced Cappuccino</div>
                        <div class="item-modifiers">
                            <div class="item-modifier">+ Extra espresso shot</div>
                            <div class="item-modifier">– No sugar</div>
                        </div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
                <div class="order-item status-yellow">
                    <div class="order-item-content">
                        <div class="item-quantity">1</div>
                        <div class="item-name">Tom Yum</div>
                    </div>
                    <div class="item-action">
                        <img src="<?= base_url('assets/images/Check_mark.png'); ?>" alt="Completed">
                    </div>
                </div>
            </div>
            <div class="serve-button-container">
                <button class="serve-button">Serve & Complete</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('current-time').textContent = hours + ':' + minutes + ':' + seconds;
        
        const statusTimeLeft = document.getElementById('status-time-left');
        if (statusTimeLeft) {
            statusTimeLeft.textContent = hours + ':' + minutes;
        }
        
        const statusTimeRight = document.getElementById('status-time-right');
        if (statusTimeRight) {
            statusTimeRight.textContent = hours + ':' + minutes + ':' + seconds;
        }
    }

    updateTime();
    setInterval(updateTime, 1000);
</script>

<!-- KDS JavaScript -->
<script src="<?= base_url('themes/default/assets/production_unit/js/kds.js'); ?>"></script>
</body>
</html>
