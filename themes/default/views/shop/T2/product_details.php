<?php include('header.php') ?>
<!-- banner -->
<style>.option{ margin-bottom:10px;border:none;width:30%;height:25px; padding:0 10px; border: 1px solid #ccc;} .padding10{padding:10px 0;} </style>
<div class="container" style="padding: 30px; height:500px; margin-bottom: 2em;">     
    <div class="row">
        <div class="col-md-6">
            <div class="hover14 column">
                <div class="agile_top_brand_left_grid w3l_agile_top_brand_left_grid">
                    <div class="agile_top_brand_left_grid_pos"><img src="<?= $assets . $shoptheme ?>/images/instock.png" alt=" " class="img-responsive img-rounded" /> </div>

                    <div class="agile_top_brand_left_grid1">
                        <figure>
                        <div class="snipcart-item block">
                            <div class="snipcart-thumb">                                
                            <?php
                                $fielname = (file_exists("assets/mdata/$Customer_assets/uploads/" . $product['image'])) ? $product['image'] : 'no_image.png'; //$thumbs.$fielname
                            ?>
                                <img src="<?= base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/' . $fielname ?>" id="bigimg" alt="<?= $product['code'] ?>" class="img-responsive img-rounded"  />
                            </div>
                          </div>
                        </figure>
                    </div>
                </div>
                <div id="multiimages" class="padding10">
                    <?php
                    if (!empty($images)) {
                        // echo '<a class="img-thumbnail" data-toggle="lightbox" data-gallery="multiimages" data-parent="#multiimages" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                         foreach ($images as $ph) {
                             echo '<div class="gallery-image" style="float:left"><a class="img-thumbnail" data-toggle="lightbox" data-gallery="multiimages" data-parent="#multiimages" href="javascript:void(0);" style="margin-right:5px;"><img class="img-responsive gallery_image"  src="' . base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/' . $ph->photo . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';                                           
                             echo '</div>';
                         }
                     }
                     ?>
                  <div class="clearfix"></div>
                </div>
            </div>            
        </div>
        <div class="col-md-6">
            <p> <nav style="color: #999999; font-size:14px; ">Home <?php 
                foreach ($navigation as $key => $nav) {
                    if($nav) echo ' / '. $nav;
                }
            ?></nav></p>
            <h4 class="product-title" style="margin-top: 10px;text-transform: capitalize; "><?=$product['name']?> <span>(<?=$product['code']?>)</span></h4>
            <?php if ($product['brandname']) { ?>
            <strong>Brand : <?= $product['brandname'] ?></strong><br/>
            <?php } ?> 
            <?php if(!empty($product['product_details'])) { ?>
            <div style="margin: 20px 0;">
                <h5><b>Descriptions : </b></h5>
                <p><?= html_entity_decode($product['product_details']); ?></p>
                    <?php } ?>
                <div class="snipcart-details">
                        <?php if($product['promotion']){?>
                        <input type='hidden' name="product_price_<?= $product['id'] ?>" id="Pricehidden_<?= $product['id'] ?>" value='<?= $product_price = $product['promo_price'] ?>'>
                    <?php } else { ?>   
                        <input type='hidden' name="product_price_<?= $product['id'] ?>" id="Pricehidden_<?= $product['id'] ?>" value='<?= $product_price = $product['price'] ?>'>
                    <?php } ?>
                        <input type="hidden" name="mrp_<?= $product['id'] ?>" id="mrp_<?= $product['id'] ?>" value='<?= $mrp = (($product['mrp'] >= $product_price) ? $product['mrp'] : $product_price) ?>' />
                        <input type="hidden" name="real_price_<?= $product['id'] ?>" id="real_price_<?= $product['id'] ?>" value='<?= $product['price'] ?>' />
                        <input type="hidden" name="promotion_<?= $product['id'] ?>" id="promotion_<?= $product['id'] ?>" value='<?= $product['promotion'] ?>' />
                        <input type="hidden" name="storage_type_<?= $product['id'] ?>" id="storage_type_<?= $product['id'] ?>" value='<?= $product['storage_type'] ?>' />                                             
                    <div class="snipcart-details col-sm-12" style="margin-left:-3%">
                        <div class="row">
                            <div class="col-sm-6">
                            <?php 
                                    $PVPrice = $max = $in_stocks = $instocks = $qtymax = 0; 
                                   
                                    if($shopinfo['eshop_overselling']==0) {
                                        
                                        $itemStocks = $products_stocks[$product['id']];
                                        $itemPendingOrder = !empty($pending_orders[$product['id']]) ? $pending_orders[$product['id']] : FALSE;
//                                                echo "<pre>";
//                                                print_r($itemStocks);
//                                                print_r($itemPendingOrder);
//                                                echo "</pre>";
                                          
                                        if($product['storage_type']=='loose'){
                                            $stock_quantity = isset($itemStocks['total_quantity']) ? ($itemStocks['total_quantity'] + 0) : 0;
                                        } elseif($itemStocks['storage_type']=='packed'){
                                            if($itemStocks['varients_count']){
                                                $stock_quantity = isset($itemStocks['total_quantity']) ? ($itemStocks['total_quantity'] + 0) : 0;
                                            } else {
                                                $stock_quantity = isset($itemStocks['total_quantity']) ? ($itemStocks['total_quantity'] + 0) : 0;
                                            }
                                        }

                                        $item_order = ($itemPendingOrder) ? ($itemPendingOrder['order_quantity'] + 0) : 0;
                                        $in_stocks  = $instocks = $stock_quantity - $item_order; 
                                                
                                    }
                                   
                                    if ($veriants) { ?>
                                        <select class="form-control option" style="width:100%;" onChange="return getVariantDetails(this.value, this.id);" id="variants_<?= $product['id'] ?>" name="variants_<?= $product['id'] ?>">
                                        <?php
                                            $icounter = 1; 
                                            $selected = '';
                                            $is_selected = FALSE;
                                            foreach ($veriants as $veriantskey => $veriantss) {                                                            

                                                if($shopinfo['eshop_overselling']==0) {

                                                    if($product['storage_type']=='packed') {
                                                                    
                                                        $stock_quantity = (is_array($itemStocks) && $itemStocks['varients_count']) ? ($itemStocks[$veriantskey]['quantity'] + 0) : 0;

                                                        $item_order     = (isset($itemPendingOrder[$veriantskey])) ? ($itemPendingOrder[$veriantskey]['order_quantity'] + 0) : 0;

                                                        $optionMax      = (bool)(($stock_quantity - $item_order) > 0) ? floor((($stock_quantity - $item_order)/$veriantss->unit_quantity)) : 0;

                                                    } else {
                                                        $optionMax = (bool)($in_stocks > 0) ? floor(($in_stocks/$veriantss->unit_quantity)) : 0;
                                                    }
//                                                   
                                                    if ($icounter == 1) {
                                                        $PVPrice = $veriantss->price;
                                                        $instocks = $optionMax;
                                                        if($optionMax > 0) { 
                                                            $is_selected = TRUE; 
                                                            $selected = 'selected="selected"';                                                                          
                                                        }
                                                    } elseif($icounter > 1 && $product['primary_variant'] == $veriantskey && $optionMax > 0 ) {
                                                        $PVPrice = $veriantss->price;                                                                     
                                                        $mrp += $veriantss->price;
                                                        $selected = 'selected="selected"';
                                                        $instocks = $optionMax;
                                                        $is_selected = TRUE;
                                                    } elseif($product['primary_variant'] == $veriantskey && $is_selected == FALSE) {
                                                        $selected = 'selected="selected"';
                                                        $PVPrice = $veriantss->price;
                                                        $instocks = $optionMax;
                                                        $is_selected = TRUE;
                                                    }
                                                }

                                                ?>
                                                <option <?=$selected?> value="<?php echo $veriantskey . '~' . $veriantss->name . '~' . $veriantss->price .'~'.$optionMax ?>"><?php echo $veriantss->name?> <?= ((float)$veriantss->price ? '(+' . number_format($veriantss->price, 2).')' : ''); ?></option>
                                        <?php $icounter++;
                                        if($is_selected == TRUE) { $selected = ''; } 
                                    }
                                    ?>
                                        </select>                            
                                    <?php
                                    } //end if
                                    else {
                                        echo '<div class="snipcart-details" style="margin: 0px auto 5px;">&nbsp;</div>';
                                    }  
                                ?>
                                </div>   
                            <div class="col-sm-6">                            
                                <?php
                              
                                if($shopinfo['eshop_overselling']==0) {     
                                    if((float)$instocks <= 0) {                                                    
                                        $btn_disabled = 'disabled="disabled"';
                                        $class_disabled = "button_disabled btn-default";
                                        $qty_disabled = 'disabled="disabled"';
                                        $ofs_display = "display:block;";
                                        $qty_display = "display:none;";                                    
                                    } else {
                                        $btn_disabled = ''; 
                                        $class_disabled = 'btn-success';
                                        $qty_disabled = '';
                                        $ofs_display = "display:none;";
                                        $qty_display = "display:block;";
                                    }
                                    $qty_action = ' onblur="qtyChange(this);" oninput="qtyChange(this);" ';
                                    $qty_max = ' max="'. ($instocks+1).'" ';
                                } else {
                                    $ofs_display = "display:none;";
                                    $qty_action = '';
                                    $qty_disabled = '';
                                    $qty_max = ' max="99999" ';
                                }
                                ?>                         
                                <div id="out_of_stock_<?= $product['id'] ?>" style="<?=$ofs_display?>" class="form-control text-danger"><b>Out of Stock</b></div>
                                <table id="quantity_table_<?= $product['id'] ?>" style="<?=$qty_display?>">
                                    <tr>
                                        <td style="width: 50%"><strong>QTY:</strong></td>  
                                        <td>
                                            <!--//NOTE: input attribute max value should be maximumStock + 1 for exicute oninput events.-->
                                            <input type="number" name="qty_<?= $product['id'] ?>" value="1" id="qty_<?= $product['id'] ?>" class="form-control qty_number" min="1"  <?=$qty_max?> <?=$qty_action ?> <?=$qty_disabled; ?> step="1" />
                                        </td>
                                    </tr>                            
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12"style="margin:1em 0;">
                                <?php if ($product['promotion']) { ?>
                                    <h4 class="text-center Price_<?= $product['id'] ?>" > Price <?= $currency_symbol . number_format($product['promo_price'] + $PVPrice, 2) ?> </h4>
                                    <?php if($product['promo_price'] < $product['price']) { ?><h5 class="text-center" style="margin-top: 0.5em;"><del><?= $currency_symbol?> <span class="mrp_<?= $product['id'] ?>"><?= number_format(($product['price'] + $PVPrice),2) ?></del> </h5><?php } ?>
                                <?php } else { ?>
                                        <h4 class="text-left Price_<?= $product['id'] ?>"> Price : <?= $currency_symbol ?> <?= number_format(($product['price'] + $PVPrice), 2) ?></h4> 
                                        <h5 class="text-left" style="margin-top: 0.5em;">MRP : <?= $currency_symbol?> <span class="mrp_<?= $product['id'] ?>"><?= number_format(($mrp+ $PVPrice),2) ?></span> </h5>
                                <?php } ?>
                            </div>
                        </div>
                       <div class="row">
                            <div class="col-sm-4">
                                <button onclick="window.location = '<?= base_url('shop/home') ?>'" title=" Back To Products" class="btn btn-warning col-md-12 col-xl-12" style="margin-bottom: 1em;"> 
                                       <i class="fa fa-arrow-left" aria-hidden="true"></i> 
                                       Back 
                                </button>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" <?=$btn_disabled?> name="addtocart" onclick="addToCart('<?= $product['id'] ?>')" title="Add to cart" value=" Add to cart" class="btn col-md-12 col-xl-12 btn_add_cart_<?= $product['id'] ?> <?=$class_disabled?>" style="margin-bottom: 1em;" > 
                                <i class="fa fa-shopping-cart" aria-hidden="true"> </i> Add to cart </button>
                            </div>
                            <div class="col-sm-4">
                                <?php if ($visitor == 'user') { ?> 
                                     <button type="button" name="addTowishlist" id="addtowishlist_<?= $product['id'] ?>" title="Add to Wishlist" onclick="addTowishlist('<?= $product['id'] ?>')" value="Add to Wishlist" style="margin-bottom: 1em;" class="btn btn-info col-md-12 col-xl-12" >
                                    <i class="fa fa-heart" aria-hidden="true"></i> Add to Wishlist </button>
                                <?php } else { ?>
                                    <button type="button" name="addTowishlist" title="Add to Wishlist" onclick="window.location = '<?= base_url('shop/login') ?>'" value="Add to Wishlist" style="margin-bottom: 1em;" class="btn btn-info col-md-12 col-xl-12" >
                                    <i class="fa fa-heart" aria-hidden="true"></i> Add to Wishlist </button>
                                      <!--<a href="<?= base_url('shop/login') ?>" style="margin-bottom: 1em;" class="btn btn-info col-md-12 col-xl-12"><span id="addtowishlist_<?= $product['id'] ?>" onclick="addTowishlist('<?= $product['id'] ?>')" >WISHLIST</span></a>-->
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<?php include_once 'footer.php'; ?>
<!-- //banner -->
<script>
    $('.gallery_image').on('click', function () {
        var img_src = $(this).attr('src');
	$('#bigimg').attr('src', img_src);
});
</script>
