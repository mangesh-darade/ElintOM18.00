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
<style>
    input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}

</style>
    </head><!--/head-->
    <body>   
        
        
        <section class="middle_section"><!--Middle section view-->
            <div class="container">
                <div class="row">                    
<div class="container wrapper login-page">
    <div class="login-bg"></div>
    <div class="col-md-4 col-md-offset-4  col-xs-12">
    	<p><span class="login-logo1" ><img src="<?= $baseurl;?>assets/mdata/<?= $Customer_assets ?>/uploads/logos/<?= $this->Settings->logo?>" alt="Logo" class="img-responsivee" /></span></p>
        <input type="hidden" id="baseurl" value="<?= $baseurl;?>" />
        <div class="login-form"><!--login form-->
            <h2>Guest Login to your account</h2> 
            <p class="text-success bg-success"><?php echo $msg; ?></p>
           
            <p class="text-danger bg-danger"><?php echo $login_error; ?></p>
           
            <?php echo form_open('shop/guestlogin');?>
                <input type="text" id="name" name="name" required="required" class="form-control name-valid" maxlength="30" placeholder="Name *" />                
                <input type="number" id="phone_no" name="phone" pattern="[0-9]{10}" onKeyDown="if(this.value.length==12 && event.keyCode!=10) return false;" required="required" class="form-control" maxlength="10" placeholder="Phone No. *" />
                <input type="email" id="email" name="email"  class="form-control" maxlength="30" placeholder="Email" />
                <button type="submit" name="btn_submit" style="width: 100%;"  class="btn btn-lg btnlogin">Login</button>
                  
                           
                <?php echo form_close();?>
                <div>
                    <br/>
                    <br/>

                 <div class="sign-up"><a href="<?= base_url('shop/signup');?>" class="btn btn-lg" >New User Signup! </a></div> 
                    
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
        setTimeout(function(){
             $('.loader').hide();
        },3000)
    });
</script>    

<script>
$(document).ready(function() {
 $('.name-valid').on('keypress', function(e) {
  var regex = new RegExp("^[a-zA-Z ]*$");
  var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
  if (regex.test(str)) {
     return true;
  }
  e.preventDefault();
  return false;
 });

  $(function() {
    $('.btnlogin').prop('disabled', true);
    $('#phone_no').on('input', function(e) {
        if(this.value.length === 10) {
            $('.btnlogin').prop('disabled', false);
        } else {
            $('.btnlogin').prop('disabled', true);
        }
    });
});
});
</script>  
<!-- End Loader -->



    </body>
</html>