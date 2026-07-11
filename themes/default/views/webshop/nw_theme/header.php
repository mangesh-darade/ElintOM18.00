<?php
$cartCount = "";
if (is_array($cart_items) && count($cart_items)) {
    $cartCount = count($cart_items);
}
?>
<header>
    <div class="header-inner">
        <?php $logo_image = 'webshop/default_banner.png';
        foreach ($this->data['website_setting'] as $item) {
            if ($item->fields === 'logo_image' && !empty($item->value)) {
                $logo_image = $item->value;
                break;
            }
        }
        ?>
        <nav>
            <a href="<?= base_url('webshop') ?>">
                <img src="<?= $uploads . $logo_image ?>" alt="Logo" style="height: 120px; width: 125px; margin-top: -44px;">
            </a>


            <a href="#" class="close-nav"><i class="fa fa-close"></i> Close</a>
            <a href="<?= base_url('webshop') ?>" class="">Home</a>
            <a href="<?= base_url('webshop/about_us') ?>" class="">About Us</a>
            <!-- <?php
                    if ($this->session->webshop->is_login == "true") { ?>
                <a href="<?= base_url('webshop/your_account') ?>" class=""><img src="<?= $assets . 'restaurant/img/my_profile_icon.svg' ?>" /><?= explode(" ", $this->session->webshop->name)[0] ?></a>
            <?php }
            ?> -->
            <!-- <a href="#">Specials</a>
            <a href="#">Product Finder</a>
            <a href="#">Health News</a> -->
        </nav>
        <a class="show show-nav"><img src="<?= $assets ?>nw_theme/images/nav-icon-menu.svg" alt="Menu"></a>
        <!-- <a class="show show-search"><img src="<?= $assets ?>nw_theme/images/nav-icon-search.svg" alt="Search"></a> -->
        <?php
        if ($this->session->webshop->is_login == "true") { ?>
            <a href="<?= base_url('webshop/your_account') ?>" class="show-user"><img src="<?= $assets . 'restaurant/img/my_profile_icon.svg' ?>" /><?= explode(" ", $this->session->webshop->name)[0] ?></a>
        <?php }
        ?>
        <a class="show show-my-account" href="<?= $this->session->webshop->is_login == "true" ? base_url('webshop/logout') : base_url('webshop/login') ?>"><?= $this->session->webshop->is_login == "true" ? "Logout" : "Login" ?></a>
        <a class="show show-cart" href="<?= base_url('webshop/cart') ?>">
            <div><img src="<?= $assets ?>nw_theme/images/nav-icon-cart.svg" alt="View Cart"><?= $cartCount ? "<span>$cartCount</span>" : "" ?></div>
        </a>
        <div class="cart">
            <h5>Cart</h5>
            <div class="cart-inner">
                <p>There are no items in your cart.</p>
            </div>
        </div><!--/cart-->
        <!-- <div class="search-contain">
            <form action="#">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control input-medium search-query ui-autocomplete-input" name="q" placeholder="Enter search term" id="search-term" autocomplete="off">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </span>
                </div>
                <ul id="ui-id-1" tabindex="0" class="ui-menu ui-widget ui-widget-content ui-autocomplete ui-front" style="display: none;"></ul>
            </form>
        </div> -->
        <!--/search-contain-->
    </div><!--/header-inner-->
</header>