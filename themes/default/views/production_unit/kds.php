<!DOCTYPE html>
<html lang="en">
<!-- <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDS - Kitchen Display System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=SF+Pro+Display:wght@500&display=swap" rel="stylesheet">
  
</head> -->
<link href="<?= $assets ?>production_unit/css/kds.css" rel="stylesheet" />
<link href="<?= $assets ?>styles/style.css" rel="stylesheet" />

<!-- jQuery FIRST -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI (after jQuery) -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<!-- Bootstrap (after jQuery) -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<!-- Your custom JS LAST -->
<script type="text/javascript" src="<?= $assets ?>production_unit/js/kds.js"></script>
<script>
    window.APP = {
        baseUrl: "<?= site_url(); ?>",
        csrfTokenName: "<?= $this->security->get_csrf_token_name(); ?>",
        csrfTokenHash: "<?= $this->security->get_csrf_hash(); ?>"
    };
</script>
<body>
<div class="main-container">
    

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

    <!-- Reset Button -->
    <button id="reset-btn" class="reset-button" title="Reset All Data">Reset</button>

    <!-- Orders Container -->
    <div class="orders-container" id="orders-container"></div>
    
    <!-- Prep Time Modal -->
    <div id="prep-time-modal" class="prep-time-modal hidden">
        <div class="prep-time-modal-content">
            <div class="prep-time-modal-header">
                <span>Set Prep Time</span>
                <button class="prep-time-modal-close">✕</button>
            </div>
            <div class="prep-time-modal-body">
                <div class="prep-time-input-group">
                    <label>Minutes:</label>
                    <input type="number" id="prep-time-minutes" min="0" max="59" value="5" />
                </div>
                <div class="prep-time-input-group">
                    <label>Seconds:</label>
                    <input type="number" id="prep-time-seconds" min="0" max="59" value="0" />
                </div>
            </div>
            <div class="prep-time-modal-footer">
                <button id="prep-time-ok" class="btn-prep-time-ok">OK</button>
                <button id="prep-time-cancel" class="btn-prep-time-cancel">Cancel</button>
            </div>
        </div>
    </div>
    
    <div id="floating-bar" class="floating-bar hidden">
        <!-- Prep/Ready/Serve Button FIRST (with icon) -->
        <button id="prep-btn" class="btn prep">
            <img id="prep-btn-icon" src="<?= base_url('assets/images/frying_pan.png'); ?>" alt="Prep" class="btn-icon">
            <span id="prep-btn-text">Prep</span>
        </button>

        <!-- Timer SECOND (with clock icon) -->
        <div class="bar-timer">
        <img src="<?= base_url('assets/images/clock.png'); ?>" 
            alt="Timer" 
            class="timer-icon-img">
        <span id="cook-timer">00:00</span>
    </div>


        <!-- Cancel LAST -->
        <button id="cancel-btn" class="btn cancel">
            <span>✕</span>
            <span class="cancel-text">Cancel</span>
        </button>
    </div>

    <!-- New Timer Section (Bottom Right) -->
    <div id="timer-section" class="timer-section hidden">
        <!-- Initial Timer Button -->
        <div id="initial-timer-button" class="initial-timer-button">
            <span class="timer-plus-icon">+</span>
            <span class="timer-text">Timer</span>
        </div>
        
        <!-- Expanded Timer Display -->
        <div id="expanded-timer" class="expanded-timer hidden">
            <!-- Add Time Button (positioned above) -->
            <button id="timer-add-btn" class="timer-btn add-btn" title="Add Time">+</button>
            
            <div class="timer-container">
                <div class="timer-display" id="custom-timer-display">
                    <span id="timer-time">00:00</span>
                    <button id="timer-cancel-btn" class="timer-btn cancel-btn" title="Cancel Timer">✕</button>
                </div>
            </div>
        </div>
    </div>

    <?php /*
    <div class="orders-container">
        <!-- Order #12567 -->
        <!-- <div class="order-ticket">
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
        </div> -->

        <!-- Order #12568 -->
        <!-- <div class="order-ticket">
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
        </div> -->

        <!-- Order #12569 -->
        <!-- <div class="order-ticket">
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
        </div> -->

        <!-- Order #12570 -->
        <!-- <div class="order-ticket">
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
        </div> -->

        <!-- Order #12571 -->
        <!-- <div class="order-ticket">
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
        </div> -->

        <!-- Order #12572 -->
        <!-- <div class="order-ticket">
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
        </div> -->
    </div> 
    */ ?>
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
</body>
</html>
