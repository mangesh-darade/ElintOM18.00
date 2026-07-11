<?php include_once 'header.php'; ?>
<!-- banner -->
<style>
    /* The container */

    /* Hide the browser's default checkbox */
    .containercheck input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    /* Create a custom checkbox */
    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        background-color: #eee;
    }

    /* On mouse-over, add a grey background color */
    .containercheck:hover input ~ .checkmark {
        background-color: #ccc;
    }

    /* When the checkbox is checked, add a blue background */
    .containercheck input:checked ~ .checkmark {
        background-color: #2196F3;
    }

    /* Create the checkmark/indicator (hidden when not checked) */
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show the checkmark when checked */
    .containercheck input:checked ~ .checkmark:after {
        display: block;
    }

    /* Style the checkmark/indicator */
    .containercheck .checkmark:after {
        left: 9px;
        top: 5px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 3px 3px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    #catlist{height: 300px !important;}

    .button_disabled {
        color: #585252 !important;
        background: #e2dfdf !important;
        text-decoration: overline;
    }
</style>

<div class="banner clearfix" style="background: antiquewhite;">
    <button class="btn btn-danger" id="filterbtn">Filter Products</button>
    <div class="w3l_banner_nav_left category" id="filter_toggle"  style="background:#fff">
        <div align="center">
            <div class="sidehead">
                <h4 style="padding:10px 0;">Category</h4>
            </div>    
            <ul class="list-group" style="max-height: 300px !important; overflow: overlay; ">
                <?php
                $subcategoryid = 0;
                if (!empty($category)) {
                    foreach ($category as $catdata) {
                        if ($selectCategory[0]['parent'] == $catdata['name'] || $selectCategory[0]['category'] == $catdata['name']) {
                            $subcategoryid = (($selectCategory[0]['parent']) ? ($selectCategory[0]['parent'] == $catdata['name'] ? $catdata['id'] : '0') : ($selectCategory[0]['category'] == $catdata['name'] ? $catdata['id'] : '0') );
                        }
                        ?>
                        <li class="list-group-item category_check">
                            <div class="form-check">
                                <label class="form-check-label containercheck"> &nbsp; &nbsp; <?= $catdata['name'] ?>
                                    <input type="checkbox" name="category" <?= (($selectCategory[0]['parent']) ? ($selectCategory[0]['parent'] == $catdata['name'] ? 'checked' : '') : ($selectCategory[0]['category'] == $catdata['name'] ? 'checked' : '') ) ?>  class="form-check-input filter_check" id="cat_<?= $catdata['id'] ?>" value="<?= $catdata['id'] ?>">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </li>
                        <?php
                    }
                }
                ?>
               <!-- <span id="loadMore">Show more</span> -->
            </ul>
        </div>


        <div align="center">
            <div class="sidehead">
                <h4 style="padding:10px 0;">Sub Category</h4>
            </div> 
            <br/>
            <div id="sub_category">
                <ul class="list-group" id="subcatlist" style="max-height: 300px !important; overflow: overlay;">
                    <?php
                    if ($subcategoryid) {
                        if (!empty($subCategories[$subcategoryid])) {

                            foreach ($subCategories[$subcategoryid] as $subcategorys_val) {
                                ?>
                                <li class="list-group-item category_check">
                                    <div class="form-check">
                                        <label class="form-check-label containercheck" for="cat_<?= $subcategorys_val['id'] ?>"> &nbsp; &nbsp;  <?= ucfirst($subcategorys_val['name']) ?>
                                            <input type="checkbox" <?= ($selectCategory[0]['category'] == $subcategorys_val['name'] ? 'checked' : '') ?> name="subcategory" class="form-check-input filter_subclick" onclick="change_value()" id="cat_<?= $subcategorys_val['id'] ?>" value="<?= $subcategorys_val['id'] ?>">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>   
                                </li>
                            <?php
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div align="center">
            <div class="sidehead">
                <h4 style="padding:10px 0;">Select Brand</h4>
            </div> 
            <ul class="list-group" id="brandlist">
                <?php
                if (!empty($brands)) {
                    foreach ($brands as $BrandsName) {
                        ?>
                        <li class="list-group-item brand_check" >
                            <div class="form-check">
                                <label class="form-check-label containercheck"> &nbsp; &nbsp;  <?= $BrandsName['name']; ?>
                                    <input type="checkbox" name="brand" class="form-check-input filter_brandcheck" onclick="change_value()"  id="brands_<?= $BrandsName['id']; ?>" value="<?= $BrandsName['id']; ?>"> 
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </li>
                    <?php }
                }
                ?>
                <span id="more">Show More</span>
            </ul>
        </div>
        
    </div> 
    <div class="w3l_banner_nav_right products" style="min-hight:800px;">
        <div id="searchData" class="w3ls_w3l_banner_nav_right_grid" style="padding: 1px 1em 5em;"></div>
        <div class="w3ls_w3l_banner_nav_right_grid  defaultProlist">
            <h4 class="w3l_fruit" style="padding: 20px 0 0 20px;"><?= $navigation ?></h4>
            <?php 
            $productsDataArr = $catlogProducts['items'];
            include_once('products_listing.php'); 
            ?>
        </div>
    </div>

</div>
<!-- //banner -->
<?php include_once 'footer.php'; ?>


