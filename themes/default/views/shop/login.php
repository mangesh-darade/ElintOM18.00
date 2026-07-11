<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>POS e-shop</title>
        <link href="<?= $assets?>T1/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?= $assets?>T1/css/font-awesome.min.css" rel="stylesheet">
        <link href="<?= $assets?>T1/css/animate.css" rel="stylesheet">
        <link href="<?= $assets?>T1/css/main.css" rel="stylesheet">
        <link href="<?= $assets?>T1/css/responsive.css" rel="stylesheet">	
        <link href="<?= $assets?>T1/css/hover.css" rel="stylesheet" media="all">
    </head><!--/head-->
    <body style="background: url('<?=base_url("assets/mdata/$Customer_assets/uploads/eshop_user/login_bg.jpg")?>') 50% 50%!important;">
        
        <section class="middle_section"><!--Middle section view-->
            <div class="container">
                <div class="row">                    
<div class="container wrapper login-page">
     
    <div class="col-md-4 col-md-offset-4  col-xs-12">
    	<p><span class="login-logo1" ><img src="<?= $baseurl;?>assets/mdata/<?= $Customer_assets ?>/uploads/logos/<?= $this->Settings->logo?>" alt="Logo" class="img-responsivee" /></span></p>
        <input type="hidden" id="baseurl" value="<?= $baseurl;?>" />
        <div class="login-form"><!--login form-->
            <h2>Login to your account</h2> 
            <p class="text-success bg-success"><?php echo $msg; ?></p>
           
            <p class="text-danger bg-danger"><?php echo $login_error; ?></p>
            <?php
            if($resend_verification_link === TRUE)
            {   
            ?>                             
            <p class="text-center"><a href="<?=base_url("shop/resend_verification_link/$customer_id")?>">Resend Email Verification Link</a></p>
            <?php
            }
            ?>
            <?php echo form_open('shop/login');?>
            <input type="text" id="login_id" name="login_id" required="required" class="form-control" maxlength="30" placeholder="Email/Phone number" />
            <input type="password" id="login_passkey" required="required" name="login_passkey" autocomplete="new_password" maxlength="30" class="form-control" placeholder="Password" />
                <span>
                    <input type="checkbox" class="checkbox" /> 
                    Keep me signed in
                </span>
                   <button type="submit" name="btn_submit" style="width: 100%;" value="Authentication" class="btn btn-lg btnlogin">Login</button>
                    <hr/>

                    <div class="text-center">OR</div>
                    <button type="button" onclick="window.location='<?= base_url('shop/guestlogin') ?>'"  style="width: 100%;margin-top: 7px;"  class="btn btn-lg">Guest Login</button>
                    <hr/>
                <table style="text-align:center;width: 100%">
                <tr>
                    <td>
                        <a href="<?=$authUrl?>" class="btn" style="background: #556ea5;color: #FFF;font-size: large;">
                            <i class="fa fa-facebook fa-fw"></i> Login
                        </a>
                   </td>
                    <td class="text-center" style="vertical-align: middle;" >
                        <span>  OR  </span>
                    </td>
                    
                   <td>
                   
                        <?= $login_button ?>
                    </td>
                </tr>
            </table>
            <br/>
            <!--<button type="submit" name="btn_submit" value="Authentication" class="btn btn-lg btnlogin">Login</button>
           <a href="<?=$authUrl?>"><img src="<?= $baseurl;?>assets/images/fblogin-btn.png" alt="" width="32%" height="20%"/></a> --><br/>
               <!-- <a href="https://signup.simplypos.co.in/?merchant=#!/login">Forgot Password</a> -->
			   <a href="<?=base_url('shop/forgot_password')?>">Forgot Password</a>
                <?php echo form_close();?>
                <div>
             
              <!--  <div class="sign-up"><a href="https://signup.simplypos.co.in?merchant=<?= $phone;?>" class="btn btn-lg" ng-model="custdata.submit" >New User Signup! </a></div> -->
                 <div class="sign-up" style="margin: -22px 0 0;"><a href="<?= base_url('shop/signup');?>" class="" >New User Signup! </a></div> 
                    
                </div> 
            </div>
        </div><!--/login form-->
    </div>
</div>
        </div>
    </div>
</section><!--/Middle section view -->
<script src="<?= $assets?>T1/js/jquery.js"></script>
<script src="<?= $assets?>T1/js/bootstrap.min.js"></script>
<!-- Loader -->
<div class="loader" style="display: none;" ></div>
<style>
.loader {
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #fa1818;
  width: 80px;
  height: 80px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
  margin-left: 50%;
  position: fixed;
 top:21em;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<script>
    $('a').click(function(){
      $('.loader').show();
    });
    
    $('.btnlogin').click(function(){
        $('.loader').show();
    });
</script>    

<!-- End Loader -->



    </body>
</html>