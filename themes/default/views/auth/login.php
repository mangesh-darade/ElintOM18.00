<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <script type="text/javascript">
    iif (parent.frames.length !== 0) {
        <?php
            $CI =& get_instance();
            $CI->load->library('user_agent');
            $back_to_pos = $CI->agent->is_mobile() ? 'pos_elite' : 'pos';
        ?>
        top.location = '<?= site_url($back_to_pos) ?>';
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
    <script>
    function viewPassword() {
    var passwordField = $("#Password");
    var togglePasswordImg = $("#togglePassword");
    
    if (passwordField.attr("type") === "password") {
        passwordField.attr("type", "text");
        togglePasswordImg.attr("src", "<?= base_url('assets/images/View.svg') ?>");
    } else {
        passwordField.attr("type", "password");
        togglePasswordImg.attr("src", "<?= base_url('assets/images/ViewOff.svg') ?>");
    }
}
    </script>
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
        /* font-family: Raleway, sans-serif; */
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
        {
        width: 100%;
        border: 1px solid #ccc;
        background: #FFF;
        
        margin: 0 0 5px;
        padding: 10px;
    }

    #contact textarea {
        height: 100px;
        max-width: 100%;
        resize: none;
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
    .terms-container{
    margin-top:15px;
    text-align:center;
    font-size:13px;
    color:#777;
    line-height:22px;
}

.terms-container a{
    color:#428bca;
    text-decoration:none;
    cursor:pointer;
}

.terms-container a:hover{
    color:#1d6fb8;
    text-decoration:underline;
}
#termsModal .modal-body h4 {
    font-weight: 600;
}
#termsModal .modal-header{
    background: #428bca;   /* Same blue as Login button */
    color: #fff;
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
    <script src="<?= $assets ?>js/login.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        localStorage.clear();
        var hash = window.location.hash;
        if (hash && hash != '') {
            $("#login").hide();
            $(hash).show();
        }
    });

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(
            navigator.userAgent)) {
        $('#login_device').val('mobile');
    } else {
        $('#login_device').val('computer');
    }
    </script>


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
            
            <h3 class="text-center">Welcome Back!</h3>
            <h4 class="text-center" >Enter your details to Sign In</h4>
            <?php if ($Settings->mmode) { ?>
            <div class="alert alert-warning">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <?= lang('site_is_offline') ?>
            </div>
            <?php
                }
                if ($error) {
             ?>
            <div class="alert alert-danger">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <ul class="list-group"><?= $error; ?></ul>
            </div>
            <?php
                }
                if ($message) {
             ?>
            <div class="alert alert-success">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <ul class="list-group"><?= $message; ?></ul>
            </div>
            <?php
                }
            ?>
            <?php echo form_open("auth/login", 'class="login"'); ?>

            <fieldset>
                <input type="text" value="" required="required" class="form-control" name="identity"
                    placeholder="<?= lang('username') ?>" />
            </fieldset>
                <fieldset style="position: relative; width: 100%;">
                    <input type="password" id="Password" required="required" class="form-control" name="password"
                        placeholder="<?= lang('pw') ?>" />
                    <img id="togglePassword" src="<?= base_url('assets/images/ViewOFF.svg') ?>" 
                        onclick="viewPassword();" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; width: 20px; height: 20px;" />
                </fieldset>

            <p style="text-align: right;">
   <!----------------- Email OTP Verification commit the code As Per Medha mam Permission  --------------------------------------->
            <!-- <a href="#forgot_password" class="forgot-password-link" title="Forgot Password">Forgot Password</a> |  -->
            <a href="#forgot_password_mobile" class="forgot-password-mobile-link" title="Forgot Password via Mobile">Forgot Password?</a>
            </p>

            <input type="hidden" name="login_device" id="login_device" />
            <div class="center-button">
                <button type="submit" class="btn btn-primary" style="font-family:'Exo 2', sans-serif" ><?= lang('Log In') ?> &nbsp; </button>
                <div class="terms-container">
                    <p style=" margin-right: -30px; text-align:right;">
                    <a href="javascript:void(0);" class="terms-link">
                        Terms &amp; Conditions
                    </a>
                </div>
            </div>
            <!-- <fieldset>
                <p class="text-center">Don't have an account? <a href=" " target="_blank" title=""><u>Registere here</u>  </a></p>
            </fieldset> -->
        </form>
    </div>

    <?php
        if ($Settings->captcha) {
     ?>
    <div class="col-sm-12">
        <div class="textbox-wrap form-group">
            <div class="row">
                <div class="col-sm-6 div-captcha-left">
                    <span class="captcha-image"><?php echo $image; ?></span>
                </div>
                <div class="col-sm-6 div-captcha-right">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <a href="<?= base_url(); ?>auth/reload_captcha" class="reload-captcha">
                                <i class="fa fa-refresh"></i>
                            </a>
                        </span>
                        <?php echo form_input($captcha); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        } 
    ?>

    <?php echo form_close(); ?>
<!-- Modal Forget Password -->

<div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel"><?= lang('forgot_password') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                if ($error) {
                    ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <ul class="list-group"><?= $error; ?></ul>
                    </div>
                    <?php
                }
                if ($message) {
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <ul class="list-group"><?= $message; ?></ul>
                    </div>
                    <?php
                }
                ?>
                <?php echo form_open("auth/forgot_password", 'class="login" data-toggle="validator"'); ?>
                <p><?= lang('type_email_to_reset'); ?></p>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="forgot_email" class="form-control" placeholder="<?= lang('email_address') ?>" required="required" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="#login" data-dismiss="modal">
                    <i class="fa fa-chevron-left"></i> <?= lang('back') ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= lang('submit') ?> &nbsp;&nbsp; <i class="fa fa-envelope"></i>
                </button>
            </div>
                <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!--------------------------------------- Modal Forget Password Mobile --------------------------------------------->
<div class="modal fade" id="forgotPasswordMobileModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordMobileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordMobileModalLabel">Forgot Password via Mobile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                if ($error) {
                    ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <ul class="list-group"><?= $error; ?></ul>
                    </div>
                    <?php
                }
                if ($message) {
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <ul class="list-group"><?= $message; ?></ul>
                    </div>
                    <?php
                }
                ?>
                <?php echo form_open("auth/forgot_password_mobile", 'class="login" data-toggle="validator"'); ?>
                <p>Enter your mobile number to receive OTP for password reset.</p>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-mobile"></i></span>
                        <input type="text" name="forgot_mobile" class="form-control" placeholder="Mobile Number (10 digits)" required="required" pattern="[0-9]{10}" maxlength="10" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="#login" data-dismiss="modal">
                    <i class="fa fa-chevron-left"></i> <?= lang('back') ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= lang('submit') ?> &nbsp;&nbsp; <i class="fa fa-mobile"></i>
                </button>
            </div>
                <?php echo form_close(); ?>
        </div>
    </div>
</div>

    <?php
            if ($Settings->allow_reg) {
                ?>
    <div id="register">
        <div class="container">
            <div class="registration-form-div reg-content">
                <?php echo form_open("auth/register", 'class="login" data-toggle="validator"'); ?>
                <div class="div-title col-sm-12">
                    <h3 class="text-primary"><?= lang('register_account_heading') ?></h3>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('first_name', 'first_name'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-user"></i></span>
                            <input type="text" name="first_name" class="form-control "
                                placeholder="<?= lang('first_name') ?>" required="required" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('last_name', 'last_name'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-user"></i></span>
                            <input type="text" name="last_name" class="form-control "
                                placeholder="<?= lang('last_name') ?>" required="required" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('company', 'company'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-building"></i></span>
                            <input type="text" name="company" class="form-control "
                                placeholder="<?= lang('company') ?>" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-phone-square"></i></span>
                            <input type="text" name="phone" class="form-control " placeholder="<?= lang('phone') ?>"
                                required="required" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('username', 'username'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-user"></i></span>
                            <input type="text" name="username" class="form-control "
                                placeholder="<?= lang('username') ?>" required="required" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('email', 'email'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control "
                                placeholder="<?= lang('email_address') ?>" required="required" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('password', 'password1'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-key"></i></span>
                            <?php echo form_password('password', '', 'class="form-control tip" id="password1" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-regexp-message="' . lang('pasword_hint') . '"'); ?>
                        </div>
                        <span class="help-block"><?= lang('pasword_hint') ?></span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('confirm_password', 'confirm_password'); ?>
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-key"></i></span>
                            <?php echo form_password('confirm_password', '', 'class="form-control" id="confirm_password" required="required" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <a href="#login" class="btn btn-success pull-left login_link">
                        <i class="fa fa-chevron-left"></i> <?= lang('back') ?>
                    </a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <?= lang('register_now') ?> <i class="fa fa-user"></i>
                    </button>
                </div>
                <?php echo form_close(); ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <?php
        }
    ?>

<!-- Terms & Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>

                <h4 class="modal-title" id="termsModalLabel">
                    Terms &amp; Conditions
                </h4>
            </div>

            <div class="modal-body" style="max-height:500px; overflow-y:auto;">

                <h4>1. Acceptance</h4>

                <p>
                    By using this application you agree to comply with these
                    Terms and Conditions.
                </p>

                <hr>

                <h4>2. User Responsibilities</h4>

                <ul>
                    <li>Keep your username and password confidential.</li>
                    <li>Do not share your account with anyone.</li>
                    <li>Use the application only for authorized business purposes.</li>
                </ul>

                <hr>

                <h4>3. Privacy Policy</h4>

                <p>
                    Your information will be used only for authorized business
                    purposes and handled according to company policies.
                </p>

                <hr>

                <h4>4. Restrictions</h4>

                <ul>
                    <li>No unauthorized access.</li>
                    <li>No misuse of application data.</li>
                    <li>No illegal activities.</li>
                </ul>

                <hr>

                <h4>5. Contact</h4>

                <p>
                    For any questions regarding these Terms & Conditions,
                    please contact your administrator.
                </p>

            </div>

        </div>

    </div>
</div>

</body>

<script type="text/javascript">
    $(document).on('click', '.forgot-password-link', function(e) {
    e.preventDefault(); 
    $('#forgotPasswordModal').modal('show'); 
});
///////////////////////////////// forgot_password_mobile////////////////////////////////////////
    $(document).on('click', '.forgot-password-mobile-link', function(e) {
    e.preventDefault(); 
    $('#forgotPasswordMobileModal').modal('show'); 
});

$(document).on('click', '.terms-link', function(e) {
    e.preventDefault();
    $('#termsModal').modal('show');
});
</script>

</html>