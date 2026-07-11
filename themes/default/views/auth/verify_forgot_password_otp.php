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
    .otp-input {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 5px;
    }
    .resend-link {
        text-align: center;
        margin-top: 15px;
    }
    .otp-timer {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        margin: 15px 0;
    }
    .timer-warning {
        font-size: 14px;
        color: #d9534f;
        margin-top: 5px;
        font-weight: normal;
    }
    .btn-back-login:hover {
        background-color: #e2e6ea !important;
        border-color: #dae0e5 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.15) !important;
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
            
            <h3 class="text-center" style="font-size: 28px; font-weight: 600; margin-bottom: 8px;">Verify OTP</h3>
            <h4 class="text-center" style="font-size: 16px; color: #666; margin-bottom: 20px;">Enter the OTP sent to your mobile number</h4>
            
            <div class="otp-timer text-center">
                <div id="timer-display" style="font-size: 20px; font-weight: 700; color: #428bca; margin-bottom: 5px;">
                    ⏱️ OTP expires in: <span id="countdown" style="font-family: 'Courier New', monospace;">05:00</span>
                </div>
                <div id="timer-warning" class="timer-warning" style="display: none; font-size: 14px; color: #d9534f; font-weight: 500;">
                    ⚠️ Time is running out! Please enter OTP quickly or request a new one.
                </div>
            </div>
            
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
            
            <?php echo form_open("auth/verify_forgot_password_otp", 'class="login"'); ?>

            <fieldset>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background: #f8f9fa; border-color: #dee2e6;"><i class="fa fa-lock" style="color: #6c757d;"></i></span>
                        <input type="text" name="otp" class="form-control otp-input" placeholder="Enter 6-digit OTP code" required="required" pattern="[0-9]{6}" maxlength="6" style="font-size: 18px; text-align: center; letter-spacing: 3px; font-weight: 600;" />
                    </div>
                </div>
            </fieldset>

            <div class="center-button">
                <button type="submit" class="btn btn-primary" style="font-family:'Exo 2', sans-serif; font-size: 16px; font-weight: 600; padding: 12px 30px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🔐 Verify OTP</button>
            </div>
            
            <div class="resend-link" style="margin-top: 20px;">
                <a href="#" id="resend-otp" style="color: #428bca; text-decoration: none; font-size: 15px; font-weight: 500; padding: 8px 16px; border-radius: 4px; transition: all 0.3s ease;">📱 Didn't receive OTP? Resend</a>
            </div>
            
            <div class="center-button" style="margin-top: 20px;">
                <a href="<?= site_url('login') ?>" class="btn btn-default btn-back-login" style="display: inline-block; width: 100%; text-align: center; font-family:'Exo 2', sans-serif; font-size: 16px; font-weight: 600; padding: 12px 30px; border-radius: 6px; background-color: #f8f9fa; color: #495057; text-decoration: none; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;">🏠 Back to Login</a>
            </div>
            
        </form>
    </div>

    <?php echo form_close(); ?>

<script type="text/javascript">
    // Timer functionality
    var timeLeft = 300; // 5 minutes in seconds
    var timerInterval;
    
    function startTimer() {
        timerInterval = setInterval(function() {
            var minutes = Math.floor(timeLeft / 60);
            var seconds = timeLeft % 60;
            
            // Format time display
            var timeString = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            $('#countdown').text(timeString);
            
            // Change color and show warning when time is running low
            if (timeLeft <= 60) {
                $('#timer-display').css('color', '#d9534f'); // Red color
                $('#timer-warning').show(); // Show warning message
            } else if (timeLeft <= 120) {
                $('#timer-display').css('color', '#f0ad4e'); // Orange color
                $('#timer-warning').hide(); // Hide warning message
            } else {
                $('#timer-warning').hide(); // Hide warning message
            }
            
            timeLeft--;
            
            // Timer expired
                if (timeLeft < 0) {
                    clearInterval(timerInterval);
                    $('#timer-display').html('<span style="color: #d9534f; font-size: 18px; font-weight: 700;">⏰ OTP has expired!</span>');
                    $('#resend-otp').text('🔄 Request new OTP').css('color', '#d9534f');
                    $('input[name="otp"]').prop('disabled', true);
                
                // Clear OTP from database when timer expires
                $.ajax({
                    url: '<?= site_url("auth/clear_expired_otp") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                    },
                    success: function(response) {
                        console.log('Expired OTP cleared from database:', response);
                        if (response.status === 'success') {
                            console.log('Database rows affected:', response.rows_affected);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error clearing expired OTP:', error);
                        console.log('Response:', xhr.responseText);
                    }
                });
            }
        }, 1000);
    }
    
    function resetTimer() {
        console.log('resetTimer() called - timeLeft before:', timeLeft);
        clearInterval(timerInterval);
        timeLeft = 300; // Reset to 5 minutes (300 seconds)
        console.log('resetTimer() - timeLeft after:', timeLeft);
        $('#timer-display').css('color', '#428bca');
        $('#timer-display').html('⏱️ OTP expires in: <span id="countdown" style="font-family: \'Courier New\', monospace;">05:00</span>');
        $('#timer-warning').hide(); // Hide warning message
        $('input[name="otp"]').prop('disabled', false);
        startTimer();
        console.log('Timer reset completed');
    }

    $(document).ready(function() {
        // Start the timer when page loads
        startTimer();
        
        // Auto-focus on OTP input
        $('input[name="otp"]').focus();
        
        // Only allow numbers in OTP input
        $('input[name="otp"]').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Resend OTP functionality
        $('#resend-otp').click(function(e) {
            e.preventDefault();
            var button = $(this);
            button.text('Sending...').css('pointer-events', 'none');
            
            $.ajax({
                url: '<?= site_url("auth/resend_forgot_password_otp") ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        button.text('OTP sent!').css('color', 'green');
                        console.log('Resend successful, resetting timer...');
                        resetTimer(); // Reset the timer for new OTP
                        setTimeout(function() {
                            button.text('Didn\'t receive OTP? Resend').css('color', '#428bca').css('pointer-events', 'auto');
                        }, 3000);
                    } else {
                        button.text('Failed: ' + response.message).css('color', 'red');
                        setTimeout(function() {
                            button.text('Didn\'t receive OTP? Resend').css('color', '#428bca').css('pointer-events', 'auto');
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr.responseText);
                    button.text('Error: ' + error).css('color', 'red');
                    setTimeout(function() {
                        button.text('Didn\'t receive OTP? Resend').css('color', '#428bca').css('pointer-events', 'auto');
                    }, 3000);
                }
            });
        });
    });
</script>

</body>

</html>
