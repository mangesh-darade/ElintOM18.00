<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= isset($page_title) ? html_escape($page_title) : 'Restaurant Order' ?></title>

    <!-- Vendor assets -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<?php
    $rtl = isset($site_settings['rtl']) ? (int) $site_settings['rtl'] : 0;
    $customer_assets_val = isset($site_settings['customer_assets']) ? $site_settings['customer_assets'] : 'localhost';
    $date_formats = isset($site_settings['date_formats']) ? $site_settings['date_formats'] : array();
?>
    <script>
        var site = site || {};
        site.settings = site.settings || {};
        site.settings.rtl = <?= $rtl ?>;
        site.base_url = '<?= base_url() ?>';
        site.customer_assets = '<?= addslashes($customer_assets_val) ?>';
        site.dateFormats = <?= json_encode($date_formats) ?>;
    </script>

    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/theme.css" />
    <link rel="stylesheet" href="<?= $assets ?>restaurant/css/order_screen.css" />

    <?php if (!empty($extra_head)) { echo $extra_head; } ?>
</head>
<style>
        :root {
            --primary-color: #e9176b;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        } 

        .navbar {
            background: #e9176b !important;
            border-bottom: none;
            box-shadow: none;
            padding: 0px 0;
        }

        .navbar-brand {
            font-weight: 600;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            font-size: 1.2rem;
            margin-left: 28rem !important;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #ffffff !important;
        }

        .navbar-toggler {
            border: none;
            color: #ffffff;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

       /* === Tabs container outer shell (Image-1 look) === */
        .nav-tabs-container {
            background: #ffffff;
            border: 2px solid #e9176b;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
        }

        /* ===== Tabs wrapper ===== */
        .nav-tabs-custom {
            display: flex;
        }

        /* ===== Each tab ===== */
        .nav-tab-custom {
            flex: 1;
            background: #ffffff;
            color: #000;
            font-weight: 600;
            text-align: center;
            padding: 16px 0;
            border-right: 1.5px solid #e9176b;
            border-radius: 0;
            transition: all 0.25s ease;
        }

        /* Remove last divider */
        .nav-tab-custom:last-child {
            border-right: none;
        }

        /* ===== Active tab ===== */
        .nav-tab-custom.active {
            background: #e9176b;
            color: #ffffff;
        }

        /* ===== Hover effect ===== */
        .nav-tab-custom:hover {
            background: #e9176b;
            color: #ffffff;
        }

        @media (max-width: 1200px) {
            .table-card {
                min-height: 180px;
            }
        }

        @media (max-width: 768px) {
            .section-card {
                padding: 22px;
                margin-bottom: 16px;
            }

            .table-card {
                padding: 20px 16px;
                min-height: 150px;
            }

            .table-name {
                font-size: 1.35rem;
            }

            .table-actions .btn {
                font-size: 0.9rem;
                padding: 8px 14px;
            }
        }

        @media (max-width: 576px) {
            .table-card {
                padding: 18px 14px;
                min-height: 140px;
            }

            .table-actions .btn {
                font-size: 0.88rem;
            }

            .navbar-brand {
                font-size: 1rem;
            }
        }
</style>
<body>
<!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
            <div class="container-fluid">
                <!-- Date on left -->
                <div class="navbar-text text-white me-auto">
                    <?= date('d M Y') ?>
                </div>
                
                <!-- Logo in center -->
                <a class="navbar-brand mx-auto d-flex align-items-center justify-content-center">
                <img 
                src="<?= base_url('themes/default/assets/restaurant/images/elintom_logo.svg'); ?>" 
                alt="ELINTOM"
                class="navbar-logo" >
                </a>
                <!-- Hamburger menu on right -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link p-2" href="javascript:void(0)">
                            <img 
                                src="<?= base_url('themes/default/assets/restaurant/images/nav-image.svg'); ?>" 
                                alt="ELINTOM"
                                class="navbar-logo" >
                        </a>
                    </li>
                </ul>
                </div>
            </div>
        </nav>