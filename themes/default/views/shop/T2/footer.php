<div id="cartNotify" class="modal fade" role="dialog">
    <div class="modal-dialog" id="bootstrapAlert"></div>
</div>
<div id="cartNotify1" class="modal fade" role="dialog">
    <div class="modal-dialog" id="bootstrapAlert1"></div>
</div>
<div class="footer_note">Footer Text</div>
<footer class="footer">
    
    <div class="container" >
        <div class="row">
            <div class="col-sm-6 col-xl-2">
                <h1>Information</h1>
                <ul>					 
                    <li><a href="<?= base_url('shop/about_us') ?>">About Us</a></li>
                    <li><a href="<?= base_url('shop/faq') ?>">FAQ</a></li>
                    <li><a href="<?= base_url('shop/privacy_policy') ?>">Privacy Policy</a></li>
                    <li><a href="<?= base_url('shop/terms_conditions') ?>">Terms of use</a></li>
                    <li><a href="<?= base_url('shop/contact') ?>">Contact Us</a></li>
                </ul>
            </div>

            <div class="col-sm-6 col-xl-10">
                <h1>what in stores</h1>
                <div class="row" >
                    <?php
                    if (!empty($category)) {
                        $i = 0;
                        foreach ($category as $catdata) {
                            $i++;
                            //if($visitor == 'user') {
                            $link = base_url('shop/home/' . md5($catdata['id']));
                            /* } else {
                              $link =  base_url('shop/login');
                              } */
                            ?>
                            <div class="col-md-3 col-xl-3"style="margin: 0.2em 0px;"> 
                                <a href="<?= $link ?>" class="categoryfooter" > 
                                    <i class="fa fa-circle" style="font-size:11px" aria-hidden="true"></i>
                                    <?= $catdata['name'] ?>
                                </a>
                            </div>
                            <?php
//                                if($i > 4) break;
                        }//end foreach.
                    }//End if.
                    ?>                
                </div>
            </div>

            <!--            <div class="col-sm-6 col-xl-3">
                            <h1>100% secure payments</h4>
                            <img src="<?= $assets . $shoptheme ?>/images/card.png" alt=" " class="img-responsive" />
                            
                           
                        </div>-->
        </div>
        <hr style="border: 1px solid #736f6f;"/>
        <div class="row">
            <div class="col-sm-4">
                <h1>connect with us</h1>
                <ul class="agileits_social_icons">
                    <li><a href="<?= empty($eshop_settings->facebook_link) ? '#' : $eshop_settings->facebook_link ?>" <?php
                        if (!empty($eshop_settings->facebook_link)) {
                        echo 'target="_new"';
                        }
                        ?> class="facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                    <li><a href="<?= empty($eshop_settings->twitter_link) ? '#' : $eshop_settings->twitter_link ?>" <?php
                           if (!empty($eshop_settings->twitter_link)) {
                        echo 'target="_new"';
                           }
                        ?> class="twitter"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                    <li><a href="<?= empty($eshop_settings->google_link) ? '#' : $eshop_settings->google_link ?>" <?php
                           if (!empty($eshop_settings->google_link)) {
                        echo 'target="_new"';
                           }
                           ?> class="google"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
                    <li><a href="<?= empty($eshop_settings->instagram_link) ? '#' : $eshop_settings->instagram_link ?>" <?php
                           if (!empty($eshop_settings->instagram_link)) {
                        echo 'target="_new"';
                           }
                           ?> class="instagram"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>

 <!-- <li><a href="#" class="dribbble"><i class="fa fa-dribbble" aria-hidden="true"></i></a></li>-->
                </ul>

            </div>
            <div class="col-sm-4">
                <h1>Get In Touch</h1>
                <ul>
<?php if (!empty($eshop_settings->shop_phone)) { ?>
                        <li style="color: #fff;"><i class="fa fa-phone"></i> : <a style="font-weight: 400;" href="tel://<?= $eshop_settings->shop_phone ?>"><?= $eshop_settings->shop_phone ?></a></li>
<?php } ?>
<?php if (!empty($eshop_settings->shop_email)) { ?>  
                        <li style="color: #fff;"><i class="fa fa-envelope"></i> : <a style="font-weight: 400;" href="mailto:<?= $eshop_settings->shop_email ?>"><?= $eshop_settings->shop_email ?></a></li>
<?php } ?>
                </ul> 
            </div>
            <div class="col-sm-4">
                <h1>100% secure payments</h4>
                    <img src="<?= $assets . $shoptheme ?>/images/card.png" alt=" " class="img-responsive" />
            </div>
        </div>  
        <br/>
        <!--<hr style="border: 1px solid #736f6f;"/>-->
        <div class="text-center">
            <p style="color: #bbb9b9;">© <?= date('Y') ?> POS Eshop. All rights reserved</p>
        </div>
    </div>

</footer>
<style>
    .categoryfooter{color: #999; font-weight: bold; font-size: 14px;}
    .categoryfooter:hover{color: #fff;}
    /* STYLES SPECIFIC TO FOOTER  */
    .footer {
        width: 100%;
        position: relative;
        height: auto;
        background-color: #1a1a21; /*#070617;*/
    }
    .footer .col {
        width: 190px;
        height: auto;
        float: left;
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        padding: 0px 20px 20px 20px;
    }
    .footer h1 {
        margin: 0;
        padding: 0;
        font-family: inherit;
        font-size: 13px;
        line-height: 17px;
        padding: 20px 0px 5px 0px;
        color: rgba(255,255,255,0.2);
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.250em;
        color:#FFF;
    }
    .footer  ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .footer  ul li {
        color: #999999;
        font-size: 14px;
        font-family: inherit;
        font-weight: bold;
        padding: 5px 0px 5px 0px;
        cursor: pointer;
        transition: .2s;
        -webkit-transition: .2s;
        -moz-transition: .2s;
    }
    .footer ul li a{color: #999999;}
    .social ul li a{
        display: inline-block;
        padding-right: 5px !important;
    }

    .footer  ul li a:hover {
        color: #ffffff;
        transition: .1s;
        -webkit-transition: .1s;
        -moz-transition: .1s;
    }
</style>    


<!-- Bootstrap Core JavaScript -->
<script src="<?= $assets . $shoptheme ?>/js/bootstrap.min.js"></script>
<script>
    /* $(document).ready(function () {
     
     $(".dropdown").hover(
     function () {
     $('.dropdown-menu', this).stop(true, true).slideDown("fast");
     $(this).toggleClass('open');
     },
     function () {
     $('.dropdown-menu', this).stop(true, true).slideUp("fast");
     $(this).toggleClass('open');
     }
     );     
     
     $().UItoTop({easingType: 'easeOutQuart'});
     
     });*/
    $(document).ready(function () {
        $('#filterbtn').click(function () {
            $('#filter_toggle').toggle();
        });
        // Show hide popover
        $('#searchData').hide();
        $('.bootstrapAlert').hide();
        $(".dropdown").click(function () {
            $(this).find(".dropdown-menu").slideToggle("fast");
        });
    });
    $(document).on("click", function (event) {
        var $trigger = $(".dropdown");
        if ($trigger !== event.target && !$trigger.has(event.target).length) {
            $(".dropdown-menu").slideUp("fast");
        }
    });

    window.localStorage.setItem('baseurl', '<?= base_url() ?>');

    function goto(page) {
        window.location = page;
    }

    function addToCart(prodId, carttype = '') {

<?php
date_default_timezone_set("Asia/Kolkata");
$msg = '';
$disabled_ordering = FALSE;
if ($eshop_settings->disabled_ordering) {
    $disabled_ordering = TRUE;
    $msg = "Sorry! Online ordering is temporarily disabled.";
} else {
    if (!empty($eshop_settings->ordering_days)) {
        $days = explode(',', $eshop_settings->ordering_days);
        if (!in_array(date('N'), $days)) {
            $msg = "Sorry! Online ordering is closed today.";
            $disabled_ordering = TRUE;
        }
    }
    if (!empty($eshop_settings->ordering_time) && !$disabled_ordering) {

        $time = explode('~', $eshop_settings->ordering_time);
        $now = strtotime(date('H:i'));
        $startTime = strtotime($time[0]);
        $endTime = strtotime($time[1]);

        if ($now < $startTime || $now > $endTime) {
            $msg = "Sorry! Online ordering is closed Now. Please visit between " . $time[0] . ' to ' . $time[1];
            $disabled_ordering = TRUE;
        }
    }
}
?>

        if ('<?= $disabled_ordering ?>') {
            alert('<?= $msg ?>');
            return false;
        }

        var varId = $('#variants_' + prodId).val();
        var qty = $('#qty_' + prodId).val();

        if (varId == 'null') {
            alert('Please Select Option');
            return false;
        }

        var baseUrl = window.localStorage.getItem('baseurl');
        var postData = 'product_id=' + prodId;

        if (varId) {
            postData = postData + '&option=' + varId;
        }

        postData = postData + '&qty=' + qty;

        $('#cartNotify').modal('show');
        $('#bootstrapAlert').html('<div class="alert alert-info"><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait! Item is adding to cart</div>');
        $.ajax({
            type: "get",
            url: baseUrl + 'shop/addCartItems',
            data: postData,
            success: function (Data) {
                // console.log(Data);

                $('#bootstrapAlert').html('<div class="alert alert-success"><i class="fa fa-check"></i> Item successfully added. Thank you.</div>');

                $('.cart-count').html(Data);

                setTimeout(function () {
                    $('#cartNotify').modal('hide');
                }, 500);
                if (carttype == 'movetoaddtocart')
                    removeItemFromWishlist(prodId);
            }
        });
    }

    function qtyChange(obj){
    
        if((parseInt(obj.value)) > parseInt((parseInt(obj.max)-1))) {
            alert('Only '+(parseInt(obj.max)-1)+' Qty Balance'); obj.value = (parseInt(obj.max)-1); 
        }
    }

    function updateCartCount(prodId, qty, optid) {

        var baseUrl = $('#baseurl').val();
        var postData = 'product_id=' + prodId;
        postData = postData + '&option=' + optid;
        postData = postData + '&qty=' + qty;

        $.ajax({
            type: "get",
            url: baseUrl + 'shop/addCartItems',
            data: postData,
            success: function (Data) {

                $('.cart-count').html(Data);
            }
        });

    }

    function updateQtyCost(itemId) {

        var qty = parseFloat($('#qty_' + itemId).val());

        var tax = parseFloat($('#item_tax_rate_' + itemId).val());

        var ordertax = $('#order_tax_' + itemId).val();
        var taxType = $('#item_tax_type_' + itemId).val();

        var order_tax_type = $('#order_tax_type_' + itemId).val();

        var price = parseFloat($('#item_price_' + itemId).val());
        var real_unit_price = parseFloat($('#real_unit_price_' + itemId).val());
        var item_tax_method = parseFloat($('#item_tax_method_' + itemId).val());

        var OptionId = $('#item_option_id_' + itemId).val();
        var optid = '';
        var OptionPrice = 0;

        if (OptionId >0) {
            var OptionName = $('#item_option_name_' + itemId).val();
            var OptionPrice = parseFloat($('#item_option_price_' + itemId).val());

            optid = OptionId + '~' + OptionName + '~' + OptionPrice;
        }

        // var total = qty * ( parseFloat(price) + parseFloat(OptionPrice) );
        var total = qty * (parseFloat(real_unit_price));

        var itemtax = 0;
        //percentage Tax
       // console.log(item_tax_method);
        if (taxType == 1) {

            if (item_tax_method == 1) {

                itemtax = ((total * parseFloat(tax)) / 100);

            } else {

                itemtax = total * parseFloat(tax) / (100 + parseFloat(tax));
                total = parseFloat(total) - parseFloat(itemtax);
            }
        }

        //Fixed Tax
        if (taxType == 2) {
            var itemtax = tax * qty;
        }
        // alert(taxType+' gg '+itemtax);
        if (order_tax_type == 1) {
            var order_tax = ((total + itemtax) * ordertax / 100);
            //console.log(order_tax);
        } else if (order_tax_type == 2) {
            var order_tax = $('#order_tax_fix').val();
            //console.log(order_tax);
        }
        // console.log(total);
        $('#show_total_' + itemId).html(total.toFixed(2));
        $('#item_price_total_' + itemId).val(total.toFixed(4));


        $('#show_tax_total_' + itemId).html(itemtax.toFixed(2));
        $('#item_tax_total_' + itemId).val(itemtax.toFixed(4));

//        $('#cart_ordertax_total_show' + itemId).html(order_tax.toFixed(2));
        $('#order_tax_total_' + itemId).val(order_tax);
        //$('#show_total_ordertax_' + itemId).html(order_tax.toFixed(2));

        calculateCart();

        updateCartCount(itemId, qty, optid);
        
        
    }

    function calculateCart() {

        var cart_sub_total = 0;
        var cart_tax_total = 0;
        var rounding = 0;
        var cart_ordertax_total = 0;
        var order_tax_type = $('#order_tax_type').val();
        var order_tax_fix = $('#order_tax_fix').val();

        $('.item_tax_total').each(function () {

            cart_tax_total += parseFloat($(this).val());

        });

        $('.item_price_total').each(function () {

            cart_sub_total += parseFloat($(this).val());
        });

        if (order_tax_type == 1) {
            $('.order_tax_total').each(function () {
                cart_ordertax_total += parseFloat($(this).val());
               // console.log(cart_ordertax_total);
            });
        } else if (order_tax_type == 2) {
            $('.order_tax_fix').each(function () {
                cart_ordertax_total = parseFloat($(this).val());
               // console.log(cart_ordertax_total);
            });
        } 

        var cart_gross_total = (parseFloat(cart_sub_total) + parseFloat(cart_tax_total) + parseFloat(cart_ordertax_total));
        $('#cart_sub_total_show').html(cart_sub_total.toFixed(2));
        $('#cart_tax_total_show').html(cart_tax_total.toFixed(2));
        $('#cart_ordertax_total_show').html(cart_ordertax_total.toFixed(2));

        //console.log(cart_gross_total);
        rounding = Math.round(cart_gross_total) - cart_gross_total;
        //console.log(rounding);
        var cart_gross_total1 = Math.round(cart_gross_total);

        $('#cart_sub_total').val(cart_sub_total.toFixed(2));
        $('#cart_tax_total').val(cart_tax_total.toFixed(2));
        $('#cart_ordertax_total_show').val(cart_ordertax_total.toFixed(2));
        $('#cart_gross_total').val(cart_gross_total.toFixed(2));
        $('#order_tax_total').val(cart_ordertax_total.toFixed(2));
        $('#cart_rounding').html(rounding.toFixed(2));
        $('#cart_gross_rounding').val(rounding.toFixed(2));
        $('#cart_gross_total_show').html(cart_gross_total1.toFixed(2));
        
        var minimum_order_amount = $('#minimum_order_amount').val();
        
        if(cart_gross_total1 < minimum_order_amount){
            $('#cart_to_checkout').attr('disabled', true);
            $('#minimum_order_error').removeClass('hide');
            $('#minimum_order_error').show();
        } else {
            $('#cart_to_checkout').attr('disabled', false);
            $('#minimum_order_error').removeClass('show');
            $('#minimum_order_error').hide();
        }
        
    }

    function submitSearch(page) {

        var search_keyword = $('#search_keyword').val();

        $('#search_keyword').val($.trim(search_keyword));
        $('#page').val(page);

        if ($.trim(search_keyword).length >= 3)
        {
            return true
        } else {
            alert('Search keyword should be at lease 3 charectors long');
            return false;
        }
    }

    function searchPage(keyword, page) {

        $('#search_keyword').val($.trim(keyword));
        $('#page').val(page);

        document.search_products.submit();

    }
    //15/07/2019
    function addTowishlist(prodId) {

        var varId = $('#variants_' + prodId).val();

        if (varId == 'null') {
            alert('Please Select Option');
            return false;
        }

        var baseUrl = window.localStorage.getItem('baseurl');
        var postData = 'product_id=' + prodId;
        if (varId) {
            postData = postData + '&option=' + varId;
        }

        $('#cartNotify1').modal('show');
        $('#bootstrapAlert1').html('<div class="alert alert-info"><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait! Item is adding to wishlist</div>');
        $.ajax({
            type: "get",
            url: baseUrl + 'shop/addTowishlistItems',
            data: postData,
            success: function (Data) {
                // console.log(Data);
                $('#bootstrapAlert1').html('<div class="alert alert-success"><i class="fa fa-check"></i> Item to wishlist.</div>');
                $('.wish-count').html(Data);

                $('#addtowishlist_' + prodId).html('WISHLISTED');
                $('#addtowishlist_' + prodId).val('wishlisted');
                $('#addtowishlist_' + prodId).css('background-color', 'green');
                setTimeout(function () {
                    $('#cartNotify1').modal('hide');
                }, 500);
            },
            error: function () {
                console.log('error');
            }
        })
    }

    function removeItemFromWishlist(prodId) {
        var postData = 'product_id=' + prodId;
        var baseUrl = window.localStorage.getItem('baseurl');
        $.ajax({
            type: "get",
            url: baseUrl + 'shop/removewishlist',
            data: postData,
            cache: false,
            success: function (html) {
                //$('#removeIcon_' + prodId).fadeOut('slow');
                document.location = '<?= base_url() ?>' + 'shop/WishListItems/';
            },
            error: function () {
                console.log('error');
            }
        })
        return false;
    }

    $(document).ready(function () {
        size_li = $("#catlist li").size();
        size_li1 = $("#brandlist li").size();
        size_li2 = $("#pricelist li").size();
        x = 5;
        $('#catlist li:lt(' + x + ')').show();
        $('#brandlist li:lt(' + x + ')').show();
        $('#pricelist li:lt(' + x + ')').show();
        $('#loadMore').click(function () {
            x = (x + 10 <= size_li) ? x + 10 : size_li;
            $('#catlist li:lt(' + x + ')').show();
            $('#catlist').css({'height': '150px', 'overflow-y': 'scroll'});
        });
        $('#more').click(function () {
            x = (x + 10 <= size_li1) ? x + 10 : size_li1;
            $('#brandlist li:lt(' + x + ')').show();
            $('#brandlist').css({'height': '150px', 'overflow-y': 'scroll'});
        });
        $('#pmore').click(function () {
            x = (x + 10 <= size_li2) ? x + 10 : size_li2;
            $('#pricelist li:lt(' + x + ')').show();
            $('#pricelist').css({'height': '150px', 'overflow-y': 'scroll'});
        });

        /* $('#showLess').click(function () {
         x=(x-5<0) ? 3 : x-5;
         $('#catlist li').not(':lt('+x+')').hide();
         });*/
    })


    $(".filter_check").click(function () {
        var select_cat = [];

        $.each($("input[name=category]:checked"), function () {
            var cat = (this).value;
            select_cat.push(cat);
            unchecked = 1;
        });

        if (unchecked == 0)
            location.reload();
        var selected_CatId = select_cat.join("_");
        var baseUrl = window.localStorage.getItem('baseurl');
        var catId = 'catId=' + selected_CatId;

        postData = catId;


        $.ajax({
            type: "get",
            url: baseUrl + 'shop/FilterSubcategory',
            data: postData,

            success: function (Data) {
                $('#sub_category').html(Data);

            }
        });
        filterProducts(1);
    });

    function change_value() {
        filterProducts(1);
    }

    function filterProducts(pageNo) {
        var select_brand = [];
        var select_cat = [];
        var select_cat_sub = [];
        var select_price = [];
        var unchecked = 0;
        pageNo = pageNo ? pageNo : 1;

        $.each($("input[name=brand]:checked"), function () {
            var brnd = (this).value;
            select_brand.push(brnd);
            unchecked = 1;
        });
        $.each($("input[name=category]:checked"), function () {
            var cat = (this).value;
            select_cat.push(cat);
            unchecked = 1;
        });

        $.each($("input[name=subcategory]:checked"), function () {
            var cat_sub = (this).value;
            select_cat_sub.push(cat_sub);
            unchecked = 1;
        });

        $.each($("input[name=price]:checked"), function () {
            var price = (this).value;
            select_price.push(price);
            unchecked = 1;
        });

        if (unchecked == 0)
            location.reload();
        var selected_CatId = select_cat.join("_");
        var selected_subCatId = select_cat_sub.join("_");
        var selected_BrandsId = select_brand.join("_");
        var selected_Priceval = select_price.join("_");
        var baseUrl = window.localStorage.getItem('baseurl');
        var catId = 'catId=' + selected_CatId;
        var BrandsId = 'BrandsId=' + selected_BrandsId;
        var PriceVals = 'PriceVal=' + selected_Priceval;
        var subcatId = 'subcategory=' + selected_subCatId;

        postData = catId + '&' + subcatId + '&' + BrandsId + '&' + PriceVals + '&pageno=' + pageNo + '&itemsPerPage=' + 20;

        $.ajax({
            type: "get",
            url: baseUrl + 'shop/Filterproducts',
            data: postData,

            success: function (Data) {
                //console.log(Data);
                $('#searchData').show();
                $('#searchData').html(Data);
                $('.defaultProlist').hide();


            }
        });



    }
    function getVariantDetails(VariantValue, VariantId) {

        var optionArr = VariantValue.split('~');
        var Variant_Price = optionArr[2];
        var VariantIdArr = VariantId.split('_');
        var productId = VariantIdArr[1];
        var productStock = optionArr[3];
        var PriceWithiD = ('.Price_' + productId);
        var product_price = $('#Pricehidden_' + productId).val();

        var real_price = $('#real_price_' + productId).val();
        var promotion = $('#promotion_' + productId).val();
        var promotion_price = 0
        if (promotion == '1') {
            promotion_price = ' <del>' + (parseInt(Variant_Price) + parseInt(real_price)).toFixed(2) + '</del>';
        }

        var SettingOverselling = parseInt('<?= $shopinfo['eshop_overselling'] ?>');

        if (!SettingOverselling) {
            if (productStock > 0) {
                $('#out_of_stock_' + productId).hide();
                $('#quantity_table_' + productId).show();
                //NOTE: input attribute max value should be maximumStock + 1 for exicute oninput events.
                $('#qty_' + productId).attr('max', parseInt(productStock)+1);
                $('#qty_' + productId).val(1);
                $('.btn_add_cart_' + productId).removeClass('button_disabled btn-default');
                $('.btn_add_cart_' + productId).addClass('btn-success');
                $('.btn_add_cart_' + productId).removeAttr('disabled');
                $('#qty_' + productId).removeAttr('disabled');
            } else {
//            $('#out_of_stock_'+productId).addClass('form-control text-danger');
//            $('#out_of_stock_'+productId).html('<b>Out Of Stock</b>');
                $('#out_of_stock_' + productId).show();
                $('#quantity_table_' + productId).hide();
                $('.btn_add_cart_' + productId).removeClass('btn-success');
                $('.btn_add_cart_' + productId).addClass('button_disabled btn-default');
                $('.btn_add_cart_' + productId).attr('disabled', 'disabled');
                $('#qty_' + productId).attr('disabled', 'disabled');
            }
        }

        var pricechange = $(PriceWithiD).html((parseInt(Variant_Price) + parseInt(product_price)).toFixed(2) + ' ' + ' ' + ((promotion_price != 0) ? promotion_price : ' '));
        $(pricechange).prepend("<b>Rs.: </b>");

        var mrp = parseFloat($('#mrp_' + productId).val()) + parseFloat(Variant_Price);
        $('.mrp_' + productId).html(mrp);
    }
</script>


<script>
    function autocomplete(inp, arr) {
        /*the autocomplete function takes two arguments,
         the text field element and an array of possible autocompleted values:*/
        var currentFocus;
        /*execute a function when someone writes in the text field:*/
        inp.addEventListener("input", function (e) {
            var a, b, i, val = this.value;
            /*close any already open lists of autocompleted values*/
            closeAllLists();
            if (!val) {
                return false;
            }
            currentFocus = -1;
            /*create a DIV element that will contain the items (values):*/
            a = document.createElement("DIV");
            a.setAttribute("id", this.id + "autocomplete-list");
            a.setAttribute("class", "autocomplete-items");
            /*append the DIV element as a child of the autocomplete container:*/
            this.parentNode.appendChild(a);
            /*for each item in the array...*/
            for (i = 0; i < arr.length; i++) {
                /*check if the item starts with the same letters as the text field value:*/
                if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    /*create a DIV element for each matching element:*/
                    b = document.createElement("DIV");
                    /*make the matching letters bold:*/
                    b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                    b.innerHTML += arr[i].substr(val.length);
                    /*insert a input field that will hold the current array item's value:*/
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                    /*execute a function when someone clicks on the item value (DIV element):*/
                    b.addEventListener("click", function (e) {
                        /*insert the value for the autocomplete text field:*/
                        inp.value = this.getElementsByTagName("input")[0].value;
                        $('#searchbtn').click();
                        /*close the list of autocompleted values,
                         (or any other open lists of autocompleted values:*/
                        closeAllLists();
                    });
                    a.appendChild(b);
                }
            }
        });
        /*execute a function presses a key on the keyboard:*/
        inp.addEventListener("keydown", function (e) {
            var x = document.getElementById(this.id + "autocomplete-list");
            if (x)
                x = x.getElementsByTagName("div");
            if (e.keyCode == 40) {
                /*If the arrow DOWN key is pressed,
                 increase the currentFocus variable:*/
                currentFocus++;
                /*and and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 38) { //up
                /*If the arrow UP key is pressed,
                 decrease the currentFocus variable:*/
                currentFocus--;
                /*and and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 13) {
                /*If the ENTER key is pressed, prevent the form from being submitted,*/
                e.preventDefault();
                if (currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x)
                        x[currentFocus].click();
                }
            }
        });
        function addActive(x) {
            /*a function to classify an item as "active":*/
            if (!x)
                return false;
            /*start by removing the "active" class on all items:*/
            removeActive(x);
            if (currentFocus >= x.length)
                currentFocus = 0;
            if (currentFocus < 0)
                currentFocus = (x.length - 1);
            /*add class "autocomplete-active":*/
            x[currentFocus].classList.add("autocomplete-active");
        }
        function removeActive(x) {
            /*a function to remove the "active" class from all autocomplete items:*/
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }
        function closeAllLists(elmnt) {
            /*close all autocomplete lists in the document,
             except the one passed as an argument:*/
            var x = document.getElementsByClassName("autocomplete-items");
            for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }
        /*execute a function when someone clicks in the document:*/
        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });
    }

    /*An array containing all the product names in the world:*/
    var baseUrl = window.localStorage.getItem('baseurl');
    $.ajax({
        type: "get",
        url: baseUrl + 'shop/getProductName',
        success: function (Data) {
            var product_name = JSON.parse(Data);
            autocomplete(document.getElementById("search_keyword"), product_name);

        }
    });
    $('#search_keyword').keydown(function (e) {
        if (e.keyCode == 13) {
            $(this).closest('form').submit();
        }
    });
</script>

<!--  Loader -->
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
        margin-left: 40%;
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
    $('a').click(function () {
        $('.loader').show();
        setTimeout(function () {
            $('.loader').hide();
        }, 5000);
    });
    $('.btn').click(function () {
        $('.loader').show();
        setTimeout(function () {
            $('.loader').hide();
        }, 5000);
    });
</script>    

<!-- End Loader  -->
<!-- Pincode check -->
<script>

    $("#search_pincode").click(function () { //keypress

        var pincode = $('#check_pincode_header').val();
        if ($.isNumeric(pincode)) {
            if (pincode.length == 6) {
                check_pincode('pincode_check_msg', pincode);
            }
        } else {
            $('#pincode_check_msg').html('Please enter valid pincode');
        }
//     setTimeout(function(){$('#pincode_check_msg').html('');},10000)
    });


    function check_pincode(blockid, pincode) {

        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'GET',
            url: '<?= base_url('shop/checkpincode'); ?>/' + pincode,
            success: function (response) {
                if (response.status == 'success') {
                    $('#' + blockid).html('<span style="color:#FFF;"><i class="fa fa-check  style="color:#FFF"></i> ' + pincode + ' pincode delivery available </span>');

                } else {
                    $('#' + blockid).html('<i class="fa fa-times text-danger"></i> <span class="text-danger">' + response.message + '<span>');

                }
            }, error: function () {
                console.log('error');
            }
        });
    }


</script>    
<!-- End Pincode Check -->
</body>
</html>