<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <script type="text/javascript">
    if (parent.frames.length !== 0) {
        top.location = '<?= site_url('pos') ?>';
    }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/helpers/login.css" rel="stylesheet" />
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <!--[if lt IE 9]>
        <script src="<?= $assets ?>js/respond.min.js"></script>
        <![endif]-->
    <style>
    .login-page .input-group .input-group-addon {
        cursor: pointer;
    }

    .login-form {
        width: 100%;
        display: block;
        box-sizing: border-box;
        margin: 10px 0;
        padding: 14px 12px;
        font-size: 16px;
        border-radius: 2px;
    }
    
    body {
    font-weight: 100;
    font-size: 13px;
    line-height: 30px;
    background: url('<?= base_url('assets/images/Background.png') ?>') no-repeat fixed;
    background-size: cover; 
    }

    .container {
        max-width: 400px;
        width: 100%;
        margin: 0 auto;
        position: relative;
    }

    #contact {
        background: #F9F9F9;
        padding: 25px;
        Border-radius: 15px !important;
        margin: 150px 0;
        box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
    }

    #contact h3 {
        display: block;
        font-size: 30px;
        color: #000;
        font-weight: 500;
        margin-bottom: 10px;
        font-family: 'Exo 2', sans-serif;
        margin-top: 20px;
    }

    #contact h4 {
        margin: 5px 0 15px;
        display: block;
        font-size: 15px;
        color: #000;
        font-family: 'Open Sans', sans-serif;
        font-weight: 400;
    }

    fieldset {
        border: medium none !important;
        margin: 20px 0 13px;
        min-width: 100%;
        padding: 0;
        width: 100%;
        font-family:'Open Sans', sans-serif;
    }

    #contact input[type="text"],
    #contact input[type="email"],
    #contact input[type="password"] {
        width: 100%;
        border: 1px solid #ccc;
        background: #FFF;
        
        margin: 0 0 5px;
        padding: 10px;
    }

    #contact button[type="submit"] {
        cursor: pointer;
        width: 100%;
        border: none;
        background: #428bca;
        border-radius: 5px !important;
        color: #FFF;
        margin: 0 0 5px;
        padding: 10px;
        font-size: 15px;
    }

    #contact button[type="submit"]:hover {
        background: #428bca;
        -webkit-transition: background 0.3s ease-in-out;
        -moz-transition: background 0.3s ease-in-out;
        transition: background-color 0.3s ease-in-out;
    }

    .center-button {
    display: block;
    margin: 10px auto;
    width: 80%; 
    }
    .radius{
    border-radius: 5px;
    }
    .password-requirements {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .back-link {
        text-align: center;
        margin-top: 15px;
    }
    </style>

    <?php $logopath = base_url("assets/icons/")?>
    <link rel="apple-touch-icon" sizes="57x57" href="<?=$logopath?>apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= $logopath?>apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= $logopath?>apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= $logopath?>apple-icon-76x76.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $logopath?>android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $logopath?>favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= $logopath?>favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $logopath?>favicon-16x16.png">
    <link rel="manifest" href="<?= $logopath?>manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= $logopath?>ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body class="login-page">
    <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p>
                    <strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript
                    enabled in
                    your browser to utilize the functionality of this website.
                </p>
            </div>
        </div>
    </noscript>

    <script src="<?= $assets ?>js/jquery.js"></script>
    <script src="<?= $assets ?>js/bootstrap.min.js"></script>
    <script src="<?= $assets ?>js/jquery.cookie.js"></script>

    <div class="container">
        <form id="contact" action="" method="post">
            <div class="text-center" style="margin-bottom:20px;">
                    <?php if (!empty($Settings->logo2)) : ?>
                        <img src="<?= base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/logos/' . $Settings->logo2; ?>" alt="<?= $Settings->site_name; ?>" style="margin-bottom:10px; width:250px; height:auto;" />
                    <?php else : ?>
                        <img src="<?= base_url('assets/images/ElintOm_Logo.png') ?>" 
                            alt="Your Image Description" 
                            style="margin-bottom:10px; width:150px; height:auto;" />
                    <?php endif; ?>
            </div>
            
            <h3 class="text-center">Reset Password</h3>
            <h4 class="text-center">Create a new password for your account</h4>
            
            <?php if ($error) { ?>
            <div class="alert alert-danger">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <ul class="list-group"><?= $error; ?></ul>
            </div>
            <?php } ?>
            
            <?php if ($message) { ?>
            <div class="alert alert-success">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <ul class="list-group"><?= $message; ?></ul>
            </div>
            <?php } ?>
            
            <?php echo form_open("auth/reset_password_mobile", 'class="login"'); ?>

            <fieldset style="position: relative; width: 100%;">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                        <?php echo form_input($new_password); ?>
                        <img id="toggleNewPassword" src="<?= base_url('assets/images/ViewOFF.svg') ?>" 
                            onclick="viewNewPassword();" 
                            style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); cursor: pointer; width: 20px; height: 20px;" />
                    </div>
                    <div class="password-requirements">
                        Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.
                    </div>
                </div>
            </fieldset>

            <fieldset style="position: relative; width: 100%;">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                        <?php echo form_input($new_password_confirm); ?>
                        <img id="toggleConfirmPassword" src="<?= base_url('assets/images/ViewOFF.svg') ?>" 
                            onclick="viewConfirmPassword();" 
                            style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); cursor: pointer; width: 20px; height: 20px;" />
                    </div>
                </div>
            </fieldset>

            <?php echo form_input($user_id); ?>
            <?php echo form_input($csrf); ?>

            <div class="center-button">
                <button type="submit" class="btn btn-primary" style="font-family:'Exo 2', sans-serif">Reset Password</button>
            </div>
            
            <div class="back-link">
                <a href="<?= site_url('login') ?>" style="color: #428bca; text-decoration: none;">← Back to Login</a>
            </div>
            
        </form>
    </div>

    <?php echo form_close(); ?>

<script type="text/javascript">
    function viewNewPassword() {
        var passwordField = $("#new_password");
        var togglePasswordImg = $("#toggleNewPassword");
        
        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
            togglePasswordImg.attr("src", "<?= base_url('assets/images/View.svg') ?>");
        } else {
            passwordField.attr("type", "password");
            togglePasswordImg.attr("src", "<?= base_url('assets/images/ViewOFF.svg') ?>");
        }
    }

    function viewConfirmPassword() {
        var passwordField = $("#new_password_confirm");
        var togglePasswordImg = $("#toggleConfirmPassword");
        
        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
            togglePasswordImg.attr("src", "<?= base_url('assets/images/View.svg') ?>");
        } else {
            passwordField.attr("type", "password");
            togglePasswordImg.attr("src", "<?= base_url('assets/images/ViewOFF.svg') ?>");
        }
    }

    $(document).ready(function() {
        // Auto-focus on new password input
        $('#new_password').focus();
        
        // Password strength validation
        $('#new_password').on('input', function() {
            var password = $(this).val();
            var requirements = $('.password-requirements');
            
            if (password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password)) {
                requirements.css('color', 'green');
            } else {
                requirements.css('color', '#666');
            }
        });
        
        // Confirm password validation
        $('#new_password_confirm').on('input', function() {
            var password = $('#new_password').val();
            var confirmPassword = $(this).val();
            
            if (confirmPassword !== '' && password !== confirmPassword) {
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '#ccc');
            }
        });
    });
</script>

</body>

</html>
