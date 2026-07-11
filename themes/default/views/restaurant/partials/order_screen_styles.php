<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    /* Popup container */
    #popup-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        max-width: 350px;
        width: 100%;
    }

    /* Popup animations */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    /* Popup styles */
    .popup {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        animation: slideIn 0.3s ease-out;
        display: flex;
        align-items: center;
        justify-content: space-between;
        pointer-events: auto;
        will-change: transform, opacity;
    }

    .popup.success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #c3e6cb;
    }

    .popup.error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #f5c6cb;
    }

    .popup.warning {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffeeba;
    }

    .popup.info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #bee5eb;
    }

    .popup .popup-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 0 8px;
        margin-left: 10px;
        line-height: 1;
    }

    .popup .popup-icon {
        margin-right: 10px;
        font-weight: bold;
        font-size: 16px;
    }

    .popup .popup-message {
        flex: 1;
    }
</style>

<style>
    :root {
        --primary-color: #e9176b;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
    }

    /* Base responsive adjustments */
    .container-fluid.full-bleed {
        padding-bottom: 70px;
    }

    /* Menu items grid */
    .menu-items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        padding: 10px;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .menu-items-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        .order-summary {
            position: fixed;
            bottom: 60px;
            left: 0;
            right: 0;
            z-index: 1000;
            max-height: 50vh;
            overflow-y: auto;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .menu-section {
            margin-bottom: 70px;
        }
    }

    /* Mobile bottom bar */
    .mobile-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 10px;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1050;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mobile-bottom-bar .btn {
        flex: 1;
        margin: 0 5px;
    }

    /* Additional layout styles */
    html,
    body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        background: #fff;
    }

    .container-fluid.full-bleed {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
        width: 100vw;
        margin-left: calc(50% - 50vw) !important;
        margin-right: calc(50% - 50vw) !important;
    }

    .container-fluid.full-bleed .row {
        --bs-gutter-x: 0;
        --bs-gutter-y: 0;
    }

    .container-fluid.full-bleed [class^="col-"],
    .container-fluid.full-bleed [class*=" col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .order-header {
        background: linear-gradient(135deg, var(--primary-color), #c7136d);
        color: white;
        padding: 20px;
        border-radius: 0;
        margin-bottom: 20px;
    }

    .order-header .header-back {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border-width: 2px;
    }

    .guest-tab {
        cursor: pointer;
        padding: 10px 15px;
        border: 2px solid var(--primary-color);
        border-radius: 25px;
        margin: 5px;
        background: white;
        color: var(--primary-color);
        transition: all 0.3s ease;
    }

    .guest-tab.active {
        background: var(--primary-color);
        color: white;
    }

    .menu-category {
        cursor: pointer;
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 20px;
        margin: 5px;
        background: white;
        transition: all 0.3s ease;
    }

    .menu-category.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .menu-item {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 44px;
    }

    .menu-item:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .menu-item.veg {
        border-left: 4px solid #28a745;
    }

    .menu-item.nonveg {
        border-left: 4px solid #dc3545;
    }

    .order-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .order-header-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .order-header-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    .cart-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
    }

    .cart-toggle .icon-wrapper {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .cart-count-pill {
        position: absolute;
        top: -8px;
        right: -12px;
        background: var(--primary-color);
        color: #fff;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 6px;
        min-width: 20px;
        text-align: center;
        line-height: 1.2;
    }

    @media (min-width: 768px) {
        .order-header-actions {
            justify-content: flex-end;
        }
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .cart-sidebar {
        position: fixed;
        right: 0;
        top: 0;
        height: 100vh;
        width: 350px;
        background: white;
        box-shadow: -3px 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .cart-sidebar.show {
        transform: translateX(0);
    }

    .filter-badge {
        cursor: pointer;
        padding: 5px 10px;
        border: 1px solid #ddd;
        border-radius: 15px;
        margin: 2px;
        background: white;
        transition: all 0.3s ease;
    }

    .filter-badge.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .scroll-x {
        display: flex;
        overflow-x: auto;
        gap: 8px;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x proximity;
        padding-bottom: 4px;
    }

    .scroll-x>* {
        flex: 0 0 auto;
        scroll-snap-align: start;
    }

    .guest-tab,
    .menu-category,
    .filter-badge {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        font-size: 16px;
        font-weight: 500;
    }

    @media (min-width: 768px) and (max-width: 1200px) {
        .container-fluid {
            padding: 15px 25px;
        }

        .container-fluid.full-bleed {
            padding: 0 !important;
        }

        .order-header {
            padding: 25px;
            margin-bottom: 25px;
        }

        .card {
            margin-bottom: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 25px;
        }

        .menu-item {
            padding: 25px;
            border-radius: 15px;
            min-height: 120px;
        }

        .menu-item img {
            width: 80px !important;
            height: 80px !important;
            margin-right: 20px !important;
        }

        .menu-item h6 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .menu-item .badge {
            font-size: 14px;
            padding: 6px 12px;
        }

        .menu-item .fw-bold {
            font-size: 20px;
        }

        .guest-tab {
            padding: 15px 25px;
            font-size: 16px;
            margin: 8px;
            border-radius: 30px;
            min-height: 50px;
        }

        .menu-category {
            padding: 12px 20px;
            font-size: 16px;
            margin: 8px;
            border-radius: 25px;
            min-height: 48px;
        }

        .filter-badge {
            padding: 10px 18px;
            font-size: 15px;
            margin: 6px;
            border-radius: 20px;
            min-height: 44px;
        }

        .quantity-btn {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .btn {
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 10px;
        }

        .btn-lg {
            padding: 15px 30px;
            font-size: 18px;
        }

        .order-item {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
        }

        .order-item h6 {
            font-size: 17px;
            margin-bottom: 8px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .form-control {
            padding: 12px 15px;
            font-size: 16px;
            border-radius: 8px;
        }

        .col-lg-4 .card {
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
    }

    .mobile-bottom-bar {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 15px 20px;
        background: #fff;
        border-top: 2px solid #e5e5e5;
        box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.1);
        z-index: 1030;
        display: none;
        gap: 12px;
    }

    .mobile-bottom-bar .btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 14px;
        font-size: 15px;
        font-weight: 600;
    }

    .mobile-bottom-bar .cart-count-pill {
        top: -10px;
        right: -14px;
    }

    @media (max-width: 991.98px) {
        body {
            padding-bottom: 80px;
        }

        .mobile-bottom-bar {
            display: flex;
        }

        .cart-sidebar {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .cart-sidebar {
            width: 100%;
        }

        .menu-item {
            padding: 10px;
        }

        .order-header {
            padding: 15px;
        }
    }

    .spice-level {
        display: flex;
        gap: 5px;
        margin: 5px 0;
    }

    .spice-btn {
        padding: 2px 8px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 12px;
        cursor: pointer;
        background: white;
    }

    .spice-btn.active {
        background: #ff6b35;
        color: white;
        border-color: #ff6b35;
    }
</style>

