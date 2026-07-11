<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    /* Premium Elite Styling for Open Register */
    #content {
        background: #06121b !important;
        /* display: flex !important; */
        justify-content: center !important;
        align-items: center !important;
        min-height: calc(100vh - 100px);
        padding: 20px !important;
        margin-top:0px!important;
    }
    
    .register-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .register-card {
        width: 100%;
        max-width: 450px;
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 40px;
        padding: 50px 40px;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.8);
        animation: eliteFadeIn 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        position: relative;
        overflow: hidden;
    }

    /* Subtle glowing ornament */
    .register-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(57, 160, 211, 0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    @keyframes eliteFadeIn {
        0% { opacity: 0; transform: translateY(40px) scale(0.95); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .register-card .header-section {
        text-align: center;
        margin-bottom: 45px;
    }

    .register-card .header-section .icon-box {
        width: 90px;
        height: 90px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .register-card .header-section i {
        font-size: 40px;
        background: linear-gradient(135deg, #39a0d3 0%, #166a9e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 0 15px rgba(57, 160, 211, 0.4));
    }

    .register-card .header-section h2 {
        color: #fff;
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -1px;
        text-transform: uppercase;
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .register-card .header-section p {
        color: rgba(255, 255, 255, 0.4);
        font-size: 14px;
        margin-top: 10px;
        font-weight: 500;
    }

    .register-card .form-group {
        margin-bottom: 35px;
    }

    .register-card .form-group label {
        display: block;
        color: rgba(255, 255, 255, 0.5);
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 12px;
        margin-left: 5px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .register-card .input-container {
        position: relative;
    }

    .register-card .input-container i {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #39a0d3;
        font-size: 20px;
    }

    .register-card .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 2px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 20px !important;
        height: 75px !important;
        color: #fff !important;
        font-size: 28px !important;
        font-weight: 800 !important;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        box-shadow: none !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    .mobile-header-nav .navbar-toggle{
        display:none!important;
    }

    .register-card .form-control:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #39a0d3 !important;
        transform: scale(1.02);
        box-shadow: 0 0 30px rgba(57, 160, 211, 0.15) !important;
    }

    .register-card .btn-submit {
        background: linear-gradient(135deg, #39a0d3 0%, #166a9e 100%);
        border: none !important;
        border-radius: 20px !important;
        height: 75px !important;
        width: 100%;
        color: #fff !important;
        font-size: 18px !important;
        font-weight: 900 !important;
        text-transform: uppercase;
        letter-spacing: 2px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 20px 40px -10px rgba(22, 106, 158, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .register-card .btn-submit:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px -10px rgba(22, 106, 158, 0.8);
        background: linear-gradient(135deg, #4ab3e6 0%, #1c7cb8 100%);
    }

    .register-card .btn-submit:active {
        transform: translateY(-2px) scale(0.98);
    }

    .alert-danger{
        background: rgba(255, 68, 68, 0.1);
        border: 1px solid rgba(255, 68, 68, 0.2);
        border-radius: 20px;
        padding: 15px 20px;
        color: #ff9999;
        font-size: 14px;
        margin-bottom: 30px;
        text-align: center;
        align-items: center;
        justify-content: center;
        gap: 10px;
        animation: shake 0.5s ease-in-out;
    }
    button.close {
    color: rgb(249 149 8);
    }
    ul.breadcrumb {
    display: none;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    /* Mobile adjustments */
    @media (max-width: 480px) {
        .register-card {
            padding: 40px 25px;
            margin: 0;
            border-radius: 30px;
            border-left: none;
            border-right: none;
        }
        .register-card .header-section h2 {
            font-size: 24px;
        }
        .register-card .form-control {
            height: 65px !important;
            font-size: 24px !important;
        }
        .register-card .btn-submit {
            height: 65px !important;
        }
    }
</style>

<div class="register-wrapper">
    <div class="register-card">
        <div class="header-section">
            <div class="icon-box">
                <i class="fa fa-briefcase"></i>
            </div>
            <h2><?= lang("open_register"); ?></h2>
            <!-- <p><?= lang('cash_in_hand'); ?> </p> -->
        </div>

        <!-- <?php if ($error) { ?>
            <div class="error-box">
                <i class="fa fa-exclamation-triangle"></i> <?= $error; ?>
            </div>
        <?php } ?> -->

        <?= form_open("pos_elite/open_register", ['id' => 'open-register-form']); ?>
            <div class="form-group">
                <label for="cash_in_hand"><?= lang('cash_in_hand') ?></label>
                <div class="input-container">
                    <?= form_input('cash_in_hand', (isset($_POST['cash_in_hand']) ? $_POST['cash_in_hand'] : ''), 'id="cash_in_hand" class="form-control" placeholder="0.00" autofocus autocomplete="off"'); ?>
                </div>
            </div>

            <button type="submit" class="btn btn-submit">
                <?= lang('open_register') ?>
                <i class="fa fa-chevron-right"></i>
            </button>
        <?= form_close(); ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Auto-select text on focus
        $('#cash_in_hand').on('focus', function() {
            $(this).select();
        });
        
        // Input mask for numbers
        $('#cash_in_hand').on('keypress', function(e) {
            if ((e.which != 46 || $(this).val().indexOf('.') != -1) && (e.which < 48 || e.which > 57)) {
                e.preventDefault();
            }
        });
    });
</script>
