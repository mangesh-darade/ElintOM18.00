 <?php
   
            $itemsPerRow = 4;
            $item_col = 12 / $itemsPerRow;
            if (is_array($productsDataArr) && !empty($productsDataArr)) {
                $p = 0;
                foreach ($productsDataArr as $product) {
                    $p++;
                    if ($p == 1) {
                        echo '<div class="w3ls_w3l_banner_nav_right_grid1 w3ls_w3l_banner_nav_right_grid1_veg">';
                    }//end if.
                    //  echo $assets;
                    ?>    
                    <div class="col-md-<?= $item_col ?> w3ls_w3l_banner_left w3ls_w3l_banner_left_asdfdfd" title="<?= $product['name'] ?>">
                        <div class="hover14 column">
                            <div class="agile_top_brand_left_grid w3l_agile_top_brand_left_grid">
                                <div class="agile_top_brand_left_grid_pos"><img src="<?= $assets . $shoptheme ?>/images/instock.png" alt=" " class="img-responsive img-rounded" /> </div>
                                <!--<div class="tag"><img src="<?= $assets . $shoptheme ?>/images/tag.png" alt=" " class="img-responsive"></div>-->
                                <div class="agile_top_brand_left_grid1">
                                    <figure>
                                        <div class="snipcart-item block">
                                            <div class="snipcart-thumb">
                                                <a href="<?= base_url('shop/product_info/' . md5($product['id'])) ?>" />   
                                                <?php
                                                $fielname = (file_exists("assets/mdata/$Customer_assets/uploads/thumbs/" . $product['image'])) ? $product['image'] : 'no_image.png';
                                                ?>
                                                <img src="<?= $thumbs . $fielname ?>" alt="<?= $product['code'] ?>" class="img-responsive img-rounded img-thumbnail" style="width: auto; height:90px;" />
                                                <p class="text-center" title='<?=$product['name']?>'><?= (strlen($product['name']) > 30 ? substr($product['name'], 0, 27) . '...' : substr($product['name'], 0, 30) ) ?></p>
                                                </a>
                                            <?php if ($product['promotion']) { ?>
                                                    <input type='hidden' name="product_price_<?= $product['id'] ?>" id="Pricehidden_<?= $product['id'] ?>" value='<?= $product_price = $product['promo_price'] ?>'>
                                            <?php } else { ?>   
                                                    <input type='hidden' name="product_price_<?= $product['id'] ?>" id="Pricehidden_<?= $product['id'] ?>" value='<?= $product_price = $product['price'] ?>'>

                                            <?php } ?>
                                                <input type="hidden" name="mrp_<?= $product['id'] ?>" id="mrp_<?= $product['id'] ?>" value='<?= $mrp = (($product['mrp'] >= $product_price) ? $product['mrp'] : $product_price) ?>' />
                                                <input type="hidden" name="real_price_<?= $product['id'] ?>" id="real_price_<?= $product['id'] ?>" value='<?= $product['price'] ?>' />
                                                <input type="hidden" name="promotion_<?= $product['id'] ?>" id="promotion_<?= $product['id'] ?>" value='<?= $product['promotion'] ?>' />
                                                <input type="hidden" name="storage_type_<?= $product['id'] ?>" id="storage_type_<?= $product['id'] ?>" value='<?= $product['storage_type'] ?>' />
                                            </div>
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
                                                
                                            $veriants = $this->shop_model->getProductVeriantsById($product['id']);
                                            
                                            if ($veriants) {
                                            ?>
                                                <div class="snipcart-details" >
                                                    <select class="form-control option1 " onChange="return getVariantDetails(this.value, this.id);" id="variants_<?= $product['id'] ?>" name="variants_<?= $product['id'] ?>">
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
                                                </div>
                                            <?php
                                            } else {
                                                
                                                echo '<div class="snipcart-details" style="margin: 0px auto 5px;">&nbsp;</div>';
                                            }//end else
                                            ?>
                                            
                                            <?php if ($product['promotion']) { ?>
                                                <h4 class="text-center Price_<?= $product['id'] ?>" > Price <?= $currency_symbol . number_format($product['promo_price'] + $PVPrice, 2) ?> </h4>
                                                <?php if($product['promo_price'] < $product['price']) { ?><h5 class="text-center" style="margin-top: 0.5em;"><del><?= $currency_symbol?> <span class="mrp_<?= $product['id'] ?>"><?= number_format(($product['price'] + $PVPrice),2) ?></del> </h5><?php } ?>
                                            <?php } else { ?>
                                                <h4 class="text-center Price_<?= $product['id'] ?>"> Price <?= $currency_symbol ?> <?= number_format(($product['price'] + $PVPrice), 2) ?></h4> 
                                                <h5 class="text-center" style="margin-top: 0.5em;">MRP. <?= $currency_symbol?> <span class="mrp_<?= $product['id'] ?>"><?= number_format(($mrp + $PVPrice),2) ?></span> </h5>
                                            <?php } ?>                                      
                                            
                                            <?php
                                            if(!$shopinfo['eshop_overselling']) {     
                                                if((float)$instocks <= 0) {                                                    
                                                    $btn_disabled   = 'disabled="disabled"';
                                                    $class_disabled = "button_disabled btn-default";
                                                    $qty_disabled   = 'disabled="disabled"';
                                                    $ofs_display    = "display:block;";
                                                    $qty_display    = "display:none;";                                    
                                                } else {
                                                    $btn_disabled   = ''; 
                                                    $class_disabled = 'btn-success';
                                                    $qty_disabled   = '';
                                                    $ofs_display    = "display:none;";
                                                    $qty_display    = "display:block;";
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
                                            <div class="snipcart-details " >
                                                <?php
//                                                echo "<br/>storage_type : ".$product['storage_type'];
//                                                echo "<br/>in_stocks : ".$in_stocks;                                                 
//                                                echo "<br/>pv_unit_quantity : ".$pv_unit_quantity;
                                                ?>
                                                <div id="out_of_stock_<?= $product['id'] ?>" style="<?=$ofs_display?>" class="form-control text-danger"><b>Out of Stock</b></div>
                                                <table id="quantity_table_<?= $product['id'] ?>" style="<?=$qty_display?>">
                                                    <tr>
                                                        <td style="width: 50%"><strong>QTY:</strong></td>  
                                                        <td style="text-align: left;">
                                                            <!--//NOTE: input attribute max value should be maximumStock + 1 for exicute oninput events.-->
                                                            <input type="number" name="qty_<?= $product['id'] ?>" value="1" id="qty_<?= $product['id'] ?>" class="form-control qty_number" min="1"  <?=$qty_max?> <?=$qty_action ?> <?=$qty_disabled; ?> step="1" />
                                                        </td>
                                                    </tr>
                                                </table>        
                                            </div>  
                                            <div class="snipcart-details">
                                                <input type="button" <?= $btn_disabled ?> name="addtocart" id="addtocart"  onclick="addToCart('<?= $product['id'] ?>', '')" value="Add to cart" class="button btn_add_cart_<?= $product['id'] ?> <?= $class_disabled ?> pull-left" />
                                                <?php if ($visitor == 'user') { ?> 
                                                    <span id="addtowishlist_<?= $product['id'] ?>" onclick="addTowishlist('<?= $product['id'] ?>')" class="button pull-right" style="background:green; padding:5px; font-size:12px;color:#fff;width:40%; cursor: pointer;">WISHLIST</span>
                                                <?php } else { ?>
                                                    <a href="<?= base_url('shop/login') ?>"><span id="addtowishlist_<?= $product['id'] ?>" onclick="addTowishlist('<?= $product['id'] ?>')" class="button wishbtn pull-right">WISHLIST</span></a>
                                                <?php } ?>
                                            </div>
                                            <div class="snipcart-details">
                                                <a href="<?= base_url('shop/product_info/' . md5($product['id'])) ?>"><input type="button" name="view"  value="View Details" class="btn btn-info col-sm-12" /></a>
                                            </div>
                                        </div>
                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    if ($p == $itemsPerRow) {
                        $p = 0;
                        echo ' <div class="clearfix"> </div>
                                        </div>';
                    }//end if
                }//end foreach.
                if ($p != $itemsPerRow && $p != 0) {
                    echo ' <div class="clearfix"> </div>
                                        </div>';
                }//end if
            }//endif
            else {
                echo $catlogProducts['msg'];
            }
            ?>  
            <div align="center" style="margin-top:20px;">             
                <?php echo $pagignation; ?>            
            </div>