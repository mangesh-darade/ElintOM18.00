<!DOCTYPE html>
<html>
    <head>
        <title><?= empty($eshop_settings->shop_name) ? "E-Shop" : $eshop_settings->shop_name ?>:: <?= $shop_pagename ?></title>
        <!-- for-mobile-apps -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="<?= $shopMeta['keywords'] ?>" />
        <script type="application/x-javascript"> 
            addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
            function hideURLbar(){ window.scrollTo(0,1); } 
        </script>
        <!-- //for-mobile-apps -->
        <link href="<?= $assets . $shoptheme ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="all" />
        <link href="<?= $assets . $shoptheme ?>/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
        <link href="<?= $assets . $shoptheme ?>/css/style.css" rel="stylesheet" type="text/css" media="all" />
        <!-- font-awesome icons -->
        <link href="<?= $assets . $shoptheme ?>/css/font-awesome.css" rel="stylesheet" type="text/css" media="all" /> 
        <!-- //font-awesome icons -->
        <!-- js -->
        <script src="<?= $assets . $shoptheme ?>/js/jquery-1.11.1.min.js"></script>
       <!--  <script src="<?= $assets . $shoptheme ?>/js/bootstrap.min.js"></script> -->
        <!-- //js -->
        <link href='//fonts.googleapis.com/css?family=Ubuntu:400,300,300italic,400italic,500,500italic,700,700italic' rel='stylesheet' type='text/css'>
        <link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>
        <!-- start-smoth-scrolling -->
        <script type="text/javascript" src="<?= $assets . $shoptheme ?>/js/move-top.js"></script>
        <script type="text/javascript" src="<?= $assets . $shoptheme ?>/js/easing.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $(".scroll").click(function (event) {
                    event.preventDefault();
                    $('html,body').animate({scrollTop: $(this.hash).offset().top}, 1000);
                });
            });
        </script>

        <!-- start-smoth-scrolling -->
        <!-- DataTables -->
        <link rel="stylesheet" href="<?= $assets ?>bs-assets/datatables.net-bs/css/dataTables.bootstrap.min.css">
<style>
.width20 {width:19%;}
.font13 {font-size:12px;}
#search_keyword, #searchbtn {margin-top:3px;}
     .option,.option1{text-transform: capitalize;}
.w3l_banner_nav_right_banner {
	background:url(<?=base_url('assets/uploads/eshop_user/banner_1.jpg')?>) no-repeat 0px 0px;
	background-size:cover;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	-ms-background-size:cover;
}
.w3l_banner_nav_right_banner1{
	background:url(<?=base_url('assets/uploads/eshop_user/banner_2.jpg')?>) no-repeat 0px 0px;
	background-size:cover;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	-ms-background-size:cover;
}
.w3l_banner_nav_right_banner2{
	background:url(<?=base_url('assets/uploads/eshop_user/banner_3.jpg')?>) no-repeat 0px 0px;
	background-size:cover;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	-ms-background-size:cover;
}
.w3l_banner_nav_right_banner3{
	background:url(<?=base_url($eshop_settings->banner_image_1)?>) no-repeat 0px 0px;
	background-size:cover;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	-ms-background-size:cover;
}
.w3l_banner_nav_right_banner4{
	background:url(<?=base_url($eshop_settings->banner_image_2)?>) no-repeat 0px 0px;
	background-size:cover;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	-ms-background-size:cover;
}
.w3l_banner_nav_right_banner5{
	background:url(<?=base_url($eshop_settings->banner_image_3)?>) no-repeat 0px 0px;
	background-size:cover;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	-ms-background-size:cover;
}
.w3l_header_right1 h2 a{
        font-size: 23px; padding: .5em 15px;
}
.product_list_header span {
    font-size: 14px;
    padding-left: 10px;
}
.w3ls_logo_products_left h1{font-size:29px;}
.sidehead{color:green; text-transform: uppercase; text-align: center; background: #f1f1f1; padding: 2px; border-bottom: 1px solid #D2D2D2;}
.list-group {
    margin-bottom: 26px;
}
.w3l_banner_nav_left{background:#fff;}
ul.pagination li a{cursor:pointer;}
.navbar-nav > li{float: left} .dropdown-menu > li > a{display: inline; padding: 3px 0}
            .agile_top_brand_left_grid1 p{ margin: 0.5em 0 0em;} .option{ margin-bottom:10px;border:none;width:10%;height:25px; padding:0 10px;}
            .snipcart-details input.button{width:55%; font-size:12px; padding:7px 0; margin-bottom:10px; font-weight: 700; border-radius: 8px;}
            .product_list_header span.cart-count{margin-left: -20px;}
            #filterbtn{display:none;}
            .snipcart-details span{padding: 8px 0px !important; font-weight: 700; border-radius: 8px;    font-size: 11px !important;}
         
         
.option1{
    width: 65% !important;
    height: 24px!important;
    margin: 0 19%!important;
    padding: 0px 12px!important;
    border: 1px solid #ffecec!important;
 }
 #catlist li, #brandlist li, #pricelist li{ display:none;}
.list-group li{text-align: left;}
#loadMore, #more, #pmore {
    margin-left: 6%;
    color:red;
    cursor:pointer;
     float: left;
     font-size:12px;
}
.product_list_header{margin-left: 3em;}
#loadMore:hover {
    color:black;
}
.w3ls_w3l_banner_nav_right_grid {
    padding: 0 1em 5em;}

.w3l_agile_top_brand_left_grid {
    margin: 5px 0 !important;
}

.list-group-item{padding: 5px 15px; border:none;}
.snipcart-details {margin: 0.5em auto 0;}
.bootstrapAlert{display:none}
.top_brand_home_details{margin: 0.5em auto 0em;}
.agile_top_brands_grids { margin: 2em 0 0;}
.agile_top_brand_left_grid1 p{ margin: 0.5em 0 0em;}
.cross{font-size: 24px; color:red; position: absolute; right:0;left:0;top:-7px;}
.itemcard-removeIcon {
    position: absolute;
    right: 10px;
    top: 10px;
    -webkit-border-radius: 20px;
    -moz-border-radius: 20px;
    border-radius: 20px;
    height: 24px;
    width: 24px;
    background-color: rgba(255, 255, 255, 0.6);
    border: solid 1.2px #94969F;
    cursor: pointer;
    text-align: center;}
    .wishbtn{background:green; padding:5px; font-size:12px;color:#fff;width:40%; cursor: pointer;}

       
    @media (max-width:800px){
        .w3l_offers{width: 100%;}
        .fixed-top {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
                /*background: #84c639;*/
        }
        .navbar {
            display: unset; 
        }
        .navbar-toggler {
            background-color: #fdfbfbf5;   
        }
        .mainmenu {padding: 8px !important;}

      <?php if($visitor == 'user') { ?> 
         .logo_products {margin-top: 8em;}
      <?php } else { ?>
         .logo_products {margin-top: 5em;}
      <?php } ?>
       .snipcart-details span{padding: 6px 0px !important;}
     /*   .product_list_header span.wish-count{
        margin-left:69px !important;
        -webkit-margin-left: 0px;
        -moz-margin-left: 69px;
        }*/
       /*.product_list_header span.cart-count {  margin-left: 12em;}*/
    }
   /* .product_list_header span.wish-count{ -webkit-margin-left: 0px; -moz-margin-left: 69px;}*/
      .close1{top:15px;    left:22px;}
@media (max-width: 736px) and (orientation: landscape){
		 .logo_products {margin-top: 0 !important;}
	  }
@media (min-width: 280px) and (max-width: 767px) {
#search_keyword, #searchbtn {margin-top:0px;}
}

 </style>          
    </head>
    <body>
        <!-- header -->
        <div class="agileits_header fixed-top">
            <div class="w3l_offers">
                <a href="#">Hello <?=$user_name?>! Welcome to <?= empty($eshop_settings->shop_name) ? "E-Shop" : $eshop_settings->shop_name ?></a>
            </div>
            
            <div class="w3l_search"> 
                <?php //if($visitor == 'user') { ?> 
               <?php
               $search_hidden = ['action'=>"search_products"];
               $search_attributes = ['name'=>'search_products', 'method'=>'post', 'onsubmit'=>"return submitSearch(1)"];
               echo form_open(base_url('shop/home'), $search_attributes, $search_hidden);
               ?>
                <input type="hidden" name="page" id="page" value="1" />
                <input type="text" name="search_keyword" id="search_keyword" autocomplete="off" placeholder="Search a product..." value="<?php echo (isset($_POST['search_keyword'])) ? $_POST['search_keyword'] : '' ?>" required="required" >
                <input type="submit" name="search" id="searchbtn" value=" " />
              <?php echo form_close()?>
                <?php //}?>
            </div>
             
            <!--<div class="product_list_header notifications-menu">  
               <?php if($visitor == 'user') { ?> 
                <a href="<?= base_url('shop/cart') ?>" class="button" ><input type="button" name="submit" value="View your cart" class="button" /></a>
                <span class="label label-warning cart-count"><?=$cartqty?></span>
                <a href="<?= base_url('shop/WishListItems') ?>"><span>Wishlist</span> 
                    <sub><i class="fa fa-heart" aria-hidden="true" style="color:red;"></i></sub> 
                    </a><span class="label label-warning wish-count"><?= $wishlistdata['count'];?></span>
                <?php } else { ?>
                <a href="<?= base_url('shop/login') ?>" class="button" ><input type="button" name="submit" value="Start Shoping" class="button" /></a>
                <a href="<?= base_url('shop/login') ?>"><span>Wishlist</span> <i class="fa fa-heart" aria-hidden="true" style="color:red;"></i></a>
               <?php }?>
             </div>--->
                <?php //if($visitor == 'user') { ?>
             <div class="product_list_header notifications-menu">  
                <table>
                     <tr>
                         <td>
                             <a href="<?= base_url('shop/cart') ?>" class="button" ><input type="button" name="submit" value="View your cart" class="button" /></a>
                             <span class="label label-warning cart-count"><?=$cartqty?></span> 
                         </td>
<?php if($visitor == 'user') { ?>
                         <td>
                              <a href="<?= base_url('shop/WishListItems') ?>"><span>Wishlist</span> 
                                <sub><i class="fa fa-heart" aria-hidden="true" style="color:red;"></i></sub> </a>
                                <span class="label label-warning wish-count"><?= $wishlistdata['count'];?></span>
                         </td>
                       <?php } ?>
                     </tr>
                 </table>
                
                </div>
                <?php /*}else { ?>
             <div class="product_list_header notifications-menu withoutlogin"> 
                <a href="<?= base_url('shop/login') ?>" class="button" ><input type="button" name="submit" value="Start Shoping" class="button" /></a>
                <a href="<?= base_url('shop/login') ?>"><span>Wishlist</span> <i class="fa fa-heart" aria-hidden="true" style="color:red;"></i></a>
              </div>
            <?php } */?>
            
            <div class="w3l_header_right1">
                <?php if($visitor == 'user') { ?> 
                <h2><a href="<?= base_url('shop/logout') ?>">Logout</a></h2>
                <?php } else { ?>
                <h2><a href="<?= base_url('shop/login') ?>">Login</a></h2>
               <?php }?>
            </div>
            <div class="clearfix"> </div>
        </div>
        <!-- script-for sticky-nav -->
        <script>
            $(document).ready(function () {
                var navoffeset = $(".agileits_header").offset().top;
                $(window).scroll(function () {
                    var scrollpos = $(window).scrollTop();
                    if (scrollpos >= navoffeset) {
                        $(".agileits_header").addClass("fixed");
                    } else {
                        $(".agileits_header").removeClass("fixed");
                    }
                });

            });
        </script>
        <!-- //script-for sticky-nav -->
        <div class="logo_products">

            <div class="container">
                <div class="w3ls_logo_products_left col-md-2 col-xs-5">
                <?php
                if(file_exists($eshop_settings->eshop_logo)) {
                ?>
                    <a href="<?= base_url('shop/index') ?>"><img src="<?= base_url($eshop_settings->eshop_logo) ?>" alt="<?=$eshop_settings->shop_name?>" class="img-responsive" style="max-height: 100px;" /></a>
                <?php
                } else {
                ?>
                    <h1><a href="<?= base_url('shop/index') ?>"><span><?= empty($eshop_settings->shop_name) ? $Settings->site_name : $eshop_settings->shop_name ?></span> E-shop</a></h1>
                <?php } ?>
                </div>
                <div class="w3ls_logo_products_left1 col-md-6"> <!-- xs-hide  -->
                    <ul class="special_items">					
                        <li><a href="<?= base_url('shop/about_us') ?>">About Us</a><i>/</i></li>
                        <li><a href="<?= base_url('shop/contact') ?>">Contact Us</a><i>/</i></li>
                        <li><a href="<?= base_url('shop/terms_conditions') ?>">Terms & Conditions</a><i>/</i></li>
                        <li><a href="<?= base_url('shop/privacy_policy') ?>">Policies</a><i>/</i></li>
                        <li><a href="<?= base_url('shop/faq') ?>">Faq's</a></li>
                    </ul>
                </div>
                <div class="w3ls_logo_products_right col-md-4"><!-- xs-hide  -->
                      <ul class="phone_email">
                            <?php if(!empty($eshop_settings->shop_phone)) { ?><li><i class="fa fa-phone" aria-hidden="true"></i> <a href="tel://<?=$eshop_settings->shop_phone?>"><?=$eshop_settings->shop_phone?></a></li><?php } ?>
                            <?php if(!empty($eshop_settings->shop_email)) { ?> | <li><i class="fa fa-envelope-o" aria-hidden="true"></i> <a href="mailto:<?=$eshop_settings->shop_email?>"><?=$eshop_settings->shop_email?></a></li><?php } ?>
                     </ul> 
                </div>
                <div class="clearfix"> </div>
            </div>
        </div>
        <!-- //header -->
          
         <style type="text/css">
             .navbar-collapse {
     text-align: left; 
}
        .menu-area{background: #84C639}
.dropdown-menu{ z-index:111111;border: 1px solid  #d6dad2 !important;
    border-top: 0 !important; padding:0;margin:0;border:0 solid transition!important;border:0 solid rgba(0,0,0,.15);border-radius:0;-webkit-box-shadow:none!important;box-shadow:none!important}
.mainmenu a, .navbar-default .navbar-nav > li > a, .mainmenu ul li a , .navbar-expand-lg .navbar-nav .nav-link{color:#fff;font-size:16px;text-transform:capitalize;padding:16px 15px;font-family:'Roboto',sans-serif;display: block !important;}
.mainmenu .active a,.mainmenu .active a:focus,.mainmenu .active a:hover,.mainmenu li a:hover,.mainmenu li a:focus ,.navbar-default .navbar-nav>.show>a, .navbar-default .navbar-nav>.show>a:focus, .navbar-default .navbar-nav>.show>a:hover{color: #fff;background: #4CAF50;outline: 0;}
/*==========Sub Menu=v==========*/
.mainmenu .collapse ul > li:hover > a{background: #4CAF50;}
.mainmenu .collapse ul ul > li:hover > a, .navbar-default .navbar-nav .show .dropdown-menu > li > a:focus, .navbar-default .navbar-nav .show .dropdown-menu > li > a:hover{background: #4CAF50;}
.mainmenu .collapse ul ul ul > li:hover > a{background: #4CAF50;}

.mainmenu .collapse ul ul, .mainmenu .collapse ul ul.dropdown-menu{background:#FFF;}
.mainmenu .collapse ul ul ul, .mainmenu .collapse ul ul ul.dropdown-menu{background:#FFF}
.mainmenu .collapse ul ul ul ul, .mainmenu .collapse ul ul ul ul.dropdown-menu{background:#FFF}

/******************************Drop-down menu work on hover**********************************/
.mainmenu{border: 0 solid;margin: 0;padding: 0;min-height:20px;width: 100%;}
@media only screen and (min-width: 767px) {
.mainmenu .collapse ul li:hover> ul{display:block}
.mainmenu .collapse ul ul{position:absolute;top:100%;left:0;min-width:250px;display:none}
/*******/
.mainmenu .collapse ul ul li{position:relative}
.mainmenu .collapse ul ul li:hover> ul{display:block}
.mainmenu .collapse ul ul ul{position:absolute;top:0px;left:100%;min-width:250px;display:none}
/*******/
.mainmenu .collapse ul ul ul li{position:relative}
.mainmenu .collapse ul ul ul li:hover ul{display:block}
.mainmenu .collapse ul ul ul ul{position:absolute;top:0px;min-width:250px;display:none;z-index:1}

}
@media only screen and (max-width: 767px) {
.navbar-nav .show .dropdown-menu .dropdown-menu > li > a{padding:16px 15px 16px 35px}
.navbar-nav .show .dropdown-menu .dropdown-menu .dropdown-menu > li > a{padding:16px 15px 16px 45px}
}
.dropdown-menu > li > a {
   
    padding: 5px 10px !important;text-transform: capitalize;
    border-bottom: 1px solid #d6dad2;
    color : #000 !important;
}

.dropdown-menu > li > a::after {
    display: none;
    width: 0;
    height: 0;
    margin-left: .255em;
    vertical-align: .255em;
    /*content: "";*/
    border-top: .3em solid;
    border-right: .3em solid transparent;
    border-bottom: 0;
    border-left: .3em solid transparent;
}
    </style>
        
        <div id="menu_area" class="menu-area">
    <div class="container">
        <div class="row">
            <nav class="navbar navbar-light navbar-expand-lg mainmenu">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li><a href="<?= base_url('shop/welcome') ?>" style="font-size: 19px"> <i class="fa fa-home" aria-hidden="true"></i> Home </a></li>
                        <li class="dropdown">
                            <a class="dropdown-toggle" href="<?= base_url('shop/home') ?>" id="navbarDropdown" style="font-size: 19px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-list" aria-hidden="true"></i> Categories</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <?php
                                   if (!empty($category)) {
                                       $i = 0;
                                       foreach ($category as $catdata) {                                 
                                           ?>
                                           <?php //if($visitor == 'user') { ?> 
                                                <li class="dropdown">
                                                  <?php  $subcategory = $this->shop_model->getChildCategories($catdata['id']); ?>
                                                    <a   href="<?= base_url('shop/home/' . md5($catdata['id'])) ?>" <?= ($subcategory)?'class="dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"':''?> ><?= $catdata['name'] ?> 
                                                        
                                                       <?= ($subcategory)?'<i class="fa fa-caret-right pull-right" aria-hidden="true"></i>':''?>
                                                     </a>
                                                    <?php 
                                                       if($subcategory){
                                                           echo '<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                                             ';
                                                         foreach($subcategory as $sub_category){ ?>
                                                                <li class="dropdown">
                                                                    <?php $products = $this->shop_model->getCategoryProducts(md5($sub_category['id']), '1', '20'); ?>
                                                                    <a  href="<?= base_url('shop/home/' . md5($sub_category['id'])) ?>" <?= ($products)?'class="dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"':''?>> <?= $sub_category['name'] ?>
                                                                         <?= ($products)?'<i class="fa fa-caret-right pull-right" aria-hidden="true"></i>':''?>
                                                    
                                                                    </a>
                                                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown" style="max-height: 400px; overflow-x: visible;">
                                                                        <?php 
                                                                            if($products){
                                                                            foreach($products['items'] as $products_value){ 
                                                                                ?>
                                                                                <li><a href="<?= base_url('shop/product_info/' . md5($products_value['id'])) ?>"><?= $products_value['name'] ?></a></li>
                                                                        <?php }
                                                                        } ?>
                                                                    </ul>
                                                                </li>
                                                            
                                                       <?php  }
                                                       echo '</ul>';
                                                     }
                                                            
                                                    
                                                    ?>
                                                   
                                                </li>
                                              <?php /*} else { ?>
                                                <li class="dropdown">
                                                  <?php  $subcategory = $this->shop_model->getChildCategories($catdata['id']); ?>
                                                    <a href="<?= base_url('shop/login/')?>" <?= ($subcategory)?'class="dropdown-toggle"  id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"':''?> ><?= $catdata['name'] ?> 
                                                        
                                                       <?= ($subcategory)?'<i class="fa fa-caret-right pull-right" aria-hidden="true"></i>':''?>
                                                     </a>
                                                    <?php 
                                                       if($subcategory){
                                                           echo '<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                                             ';
                                                         foreach($subcategory as $sub_category){ ?>
                                                                <li class="dropdown">
                                                                    <?php $products = $this->shop_model->getCategoryProducts(md5($sub_category['id']), '1', '20'); ?>
                                                                    <a  href="<?= base_url('shop/login/') ?>" <?= ($products)?'class="dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"':'' ?>> <?= $sub_category['name'] ?>
                                                                         <?= ($products)?'<i class="fa fa-caret-right pull-right" aria-hidden="true"></i>':''?>
                                                    
                                                                    </a>
                                                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown" style="max-height: 400px; overflow-x: visible;">
                                                                        <?php 
                                                                            if($products){
                                                                            foreach($products['items'] as $products_value){ 
                                                                                ?>
                                                                                <li><a href="<?= base_url('shop/login/') ?>"><?= $products_value['name'] ?></a></li>
                                                                        <?php }
                                                                        } ?>
                                                                    </ul>
                                                                </li>
                                                            
                                                       <?php  }
                                                       echo '</ul>';
                                                     }
                                                            
                                                    
                                                    ?>
                                                   
                                                </li>
                                             <?php }//end else */ ?>  
                                         <?php
                                       }//end foreach.
                                   }//End if.
                                   ?>
                           
                                
                            </ul>
                        </li>
                        <li ><a href="<?= base_url('shop/cart') ?>" style="font-size: 19px"><i class="fa fa-shopping-cart" aria-hidden="true"></i> My Cart  </a></li>
                        <li ><a href="<?= base_url('shop/myaccount') ?>" style="font-size: 19px"><i class="fa fa-user" aria-hidden="true"></i> Account </a></li>

                   <li class="width20">
                            <input type="text" name="pincode" min="6" maxlength="6" style="margin: 8px;" placeholder="Enter pincode to Check Delivery" class="font13 form-control" id="check_pincode_header">
                        </li>
                        <li style=" padding-top: 0.6em;padding-left: 0.5em;">
                            <button type="button" id="search_pincode" class="btn btn-success">Submit</button>
                        </li>
                        <li style=" padding-top: 1em; padding-left: 1em;">
                          <b id="pincode_check_msg"  class="text-danger" ></b>
                        </li>

                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>


        <script>
            $('.dropdown-toggle').click(function(){
                window.location= $(this).attr('href');
               
            });
        </script>
    <script type="text/javascript">
(function($){
	$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
	  if (!$(this).next().hasClass('show')) {
		$(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
	  }
	  var $subMenu = $(this).next(".dropdown-menu");
	  $subMenu.toggleClass('show');

	  $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
		$('.dropdown-submenu .show').removeClass("show");
	  });

	  return false;
	});
})(jQuery)
</script>