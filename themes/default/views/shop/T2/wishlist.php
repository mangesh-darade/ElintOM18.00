<?php include_once 'header.php'; ?>
	<div class="banner">
            <div class="w3l_banner_nav_right products" style="width:100%; background-color: antiquewhite;">
                <div class="container">
                    <div class="w3ls_w3l_banner_nav_right_grid1 w3ls_w3l_banner_nav_right_grid1_veg" style="margin: 1.2em 0 2.2em 0em;">
                    <h4 class="w3l_fruit" style="padding: 20px 0 0 20px;"><?= $navigation?></h4>
                    <?php 
                        $itemsPerRow = 4;
                        $item_col = 12 / $itemsPerRow;
                         if(is_array($wishlistdata['result']) && !empty($wishlistdata['result'])) {    
                         $p=0;
                         foreach ($wishlistdata['result'] as $pdata) {
                         $p++;
                         $wishlist = $product = (array)$pdata;
                         $wishlist['product_id'] = $wishlist['id'];
                     ?>    
                    <div title="<?= $wishlist['name'] ?>" class="col-md-<?=$item_col?> w3ls_w3l_banner_left w3ls_w3l_banner_left_asdfdfd_<?= $wishlist['product_id']?>">
                            <div class="hover14 column" id="hover14_<?= $wishlist['product_id']?>'">
                                                   
                                <div class="agile_top_brand_left_grid w3l_agile_top_brand_left_grid" id="removeIcon_<?= $wishlist['product_id']?>">
                                    <div class="itemcard-removeIcon" style="z-index:999;" onclick="removetoItemFromWishlist('<?= $wishlist['product_id']?>','<?= $wishlist['wsh_id']?>');"><span class="cross" aria-hidden="true" title="remove from wishlist">&times;</span></div>
                                 
                                    <div class="agile_top_brand_left_grid_pos"><img src="<?= $assets.$shoptheme?>/images/instock.png" alt=" " class="img-responsive img-rounded" /> </div>
                                        <div class="agile_top_brand_left_grid1">
                                            <figure>
                                                <div class="snipcart-item block">                                           
                                                    <div class="snipcart-thumb">
                                                        <a href="<?=base_url('shop/product_info/'.md5($wishlist['product_id']))?>" />   <?php
                                                            $fielname = (file_exists("assets/mdata/$Customer_assets/uploads/thumbs/".$wishlist['image'])) ?  $wishlist['image'] :  'no_image.png';
                                                            ?>
                                                            <img src="<?= $thumbs.$fielname?>" alt="<?= $wishlist['code']?>" class="img-responsive img-rounded"  style="width: auto; height:90px;"/>                                                        
                                                            <p class="text-center" title='<?=$wishlist['name']?>'><?= (strlen($wishlist['name']) > 30 ? substr($wishlist['name'], 0, 27) . '...' : substr($wishlist['name'], 0, 30) ) ?></p>
                                                            <h4 class="text-center"><?= $currency_symbol?> <?= number_format($wishlist['price'] + $wishlist['variant_price'] , 2)?>  </h4>
                                                        
                                                        </a>
                                                       </div>
                                                      <?php 
                                                        if(!$shopinfo['eshop_overselling']) {
                                                            $itemStocks = $products_stocks[$product['id']];
                                                            $itemPendingOrder = !empty($pending_orders[$product['id']]) ? $pending_orders[$product['id']] : FALSE;
                                                            
                                                            $PVPrice = $inStocks = $max = $in_stocks = $qtymax = 0; 
                                                            
                                                            if($itemStocks['storage_type']=='loose'){
                                                                
                                                            } elseif($itemStocks['storage_type']=='packed'){
                                                                if($itemStocks['varients_count']){
                                                                    $stock_quantity = isset($itemStocks['total_quantity']) ? ($itemStocks['total_quantity'] + 0) : 0;
                                                                } else {
                                                                    $stock_quantity = isset($itemStocks['total_quantity']) ? ($itemStocks['total_quantity'] + 0) : 0;
                                                                }
                                                            }
                                                            $stock_quantity = $item_order = 0;
                                                            if($product['storage_type']=='loose'){
                                                                $stock_quantity = isset($itemStocks['total_quantity']) ? ($itemStocks['total_quantity'] + 0) : 0;
                                                                $item_order = ($itemPendingOrder) ? ($itemPendingOrder['order_quantity'] + 0) : 0;
                                                                
                                                            } elseif($product['storage_type']=='packed' ){
                                                                if($itemStocks['varients_count']==0){
                                                                    $stock_quantity = isset($itemStocks['total_quantity']) ? $itemStocks['total_quantity'] + 0 : 0;
                                                                    $item_order     = ($itemPendingOrder) ? ($itemPendingOrder['order_quantity'] + 0) : 0;
                                                                
                                                                } elseif( $wishlist['wsh_option_id'] && !empty($itemStocks[$veriant_id]) ) {
                                                                    $veriant_id     = $wishlist['wsh_option_id'];                                                                
                                                                    $stock_quantity = $itemStocks[$veriant_id]['quantity'] + 0;
                                                                    $item_order     = ($itemPendingOrder) ? $itemPendingOrder[$veriant_id]['order_quantity'] + 0 : 0;                                                                    
                                                                }
                                                            }
                                                            
                                                            $item_order = ($itemPendingOrder) ? ($itemPendingOrder['order_quantity'] + 0) : 0;
                                                            $in_stocks  = $instocks = $stock_quantity - $item_order; 
                                                            
                                                            $unit_quintity = !empty($product['variant_unit_quantity']) ? (float)$product['variant_unit_quantity'] : 1;
                                                            $qtymax =  floor(($in_stocks/$unit_quintity)); 
                                                        } 
                                                ?>
                                                <?php  
                                                if($wishlist['wsh_option_id']){ 
                                                ?>                                                    
                                                    <div class="snipcart-details" >
                                                        <div class="snipcart-details" style=""><?= $wishlist['variant_name'] ?></div>
                                                        <input type="hidden" name="option" id="optionvalue_<?= $wishlist['wsh_id'] ?>" value="<?= $wishlist['wsh_option_id'].'~'.$wishlist['variant_name'].'~'.$wishlist['variant_price'].'~'.$qtymax ?>"/>
                                                    </div>
                                                    <?php } else {                                                                                                               
                                                        echo '<div class="snipcart-details" >&nbsp;</div>';
                                                      } 
                                                  
                                                    echo '<div class="snipcart-details " >';      
                                                if( $qtymax <= 0 && !$shopinfo['eshop_overselling']) {
                                                    echo '<div class="form-control text-danger"><b>Out of Stock</b></div>';
                                                } else {
                                                    echo '<input type="button" name="addtocart" id="addtocart" onclick="addToCartformWishlist(\''.$wishlist['product_id'].'\',\'movetoaddtocart\',\''.$wishlist['wsh_id'].'\')" value="Add to cart" class="button btn_add_cart_'. $product['id'] .'" />';
                                                } 
                                                    echo '</div>';
                                            ?>
                                                    <div class="snipcart-details">
                                                        <a href="<?=base_url('shop/product_info/'.md5($wishlist['product_id']))?>"><input type="button" name="view"  value="View Details" class="btn btn-info col-sm-12" /></a>
                                                    </div>
                                                </div>
                                                </figure>
                                            </div>
                                    </div>
                                    </div>
                                </div>
                            <?php 
                            }//end foreach.
                             
                        }//endif
                        else
                        {
                          echo '<div class="text-danger text-center" style="padding:50px 0;"><p>Sorry, you have not added any product to wishlist! </p></div>' ;
                        }
                        ?>  
                            <div style="margin: 20px;"><?php echo $pagignation;?></div>
                            <div class="clearfix"> </div>
                         </div>
	            </div>
                </div>
           <div class="clearfix"></div>
        </div>
<!-- //banner -->

<?php include_once 'footer.php'; ?>

<script>
     function addToCartformWishlist(prodId, carttype='',wishlistid) { 
       
//       var varId = $('#variants_'+prodId).val();
       
        var varId = $('#optionvalue_'+wishlistid).val();
        if(varId == 'null'){
           alert('Please Select Option');
               return false;
        }
        
        var baseUrl = window.localStorage.getItem('baseurl');
        var postData = 'product_id=' + prodId;
        
        if(varId){
            postData = postData + '&option=' + varId;
        }
        
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
                if(carttype=='movetoaddtocart')
                    removetoItemFromWishlist(prodId,wishlistid);
            }
        });
    }

    
     function removetoItemFromWishlist(prodId, wishlistid){
    // var varId = $('#variants_'+prodId).val();
       var varId = $('#optionvalue_'+wishlistid).val();
       
        if(varId == 'null'){
           alert('Please Select Option');
               return false;
        }
           
    var postData = 'product_id=' + prodId;
    var baseUrl = window.localStorage.getItem('baseurl');
    
     if(varId){
            postData = postData + '&option=' + varId;
        }
        
    $.ajax({
        type:"get",
        url : baseUrl + 'shop/removewishlist',
        data: postData,
        cache: false,
        success:function(html){
            //$('#removeIcon_' + prodId).fadeOut('slow');
            document.location = '<?=base_url()?>' + 'shop/WishListItems/';
        },
        error:function(){
            console.log('error');
        }
    })
    return false;
    }
</script>    
