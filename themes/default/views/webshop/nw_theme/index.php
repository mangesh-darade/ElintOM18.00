<?php
$banner_image = 'webshop/default_banner.png';
$message_section = "";
$bullet_points = "";
$company_updates = "";
$phone_number = "";
$logo_image = "";
// var_dump($this->data['website_setting']);
if (!empty($this->data['website_setting'])) {
    foreach ($this->data['website_setting'] as $item) {
        if ($item->fields === 'banner_image' && !empty($item->value)) {
            $banner_image = $item->value;
        }
        if ($item->fields === 'message_section') {
            $message_section = $item->value;
        }
        if ($item->fields === 'bullet_points') {
            $bullet_points = $item->value;
        }
        if ($item->fields === 'company_updates') {
            $company_updates = $item->value;
        }
        if ($item->fields === 'company_certification') {
            $company_updates = $item->value;
        }
        if ($item->fields === 'phone_number') {
            $phone_number = $item->value;
        }
        if ($item->fields === 'logo_image') {
            $logo_image = $item->value;
        }
    }
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- <meta property="og:image" content="<?= $uploads . $logo_image ?>">
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="627" />
    <meta property="og:type" content="website" /> -->
    <title>
        <?= !empty($this->data['title']) ? $this->data['title'] : "Default Website Title" ?>
    </title>
    <meta
        name=" description"
        content="Established in the year 1994 we Herbinn Micro Medicines" />
    <link rel="canonical" href="https://herbinnmicromedicines.elintpos.in/webshop" />
    <link
        href="https://fonts.googleapis.com/css?family=Lato:400,400italic,700,700italic|Oxygen:700"
        rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>nw_theme/css/main.css?ver=210616_01" />
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>nw_theme/css/print.css" />
    <link
        href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"
        rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>nw_theme/css/jquery-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>nw_theme/css/new-template.css?ver=230912_06" />
    <link rel="stylesheet" href="home.css" />
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>nw_theme/css/slick-slider.css" />
    <link rel="stylesheet" href="<?= $assets ?>nw_theme/css/common.css">
    <link rel="icon" type="image/x-icon" href="<?= $uploads ?>webshop/herbinn_favicon.ico">
    <style>
        .category-contain {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .category-contain a {
            display: block;
            text-decoration: none;
            color: #000;
        }

        .category-contain>div {
            text-align: center;
            background: #eee;
            border-radius: 8px;
            transition: all 0.2s ease-out;
        }

        .category-contain>div:hover {
            transform: scale(110%);
        }

        .category-contain>div img {
            width: 100%;
            height: auto;
            border-radius: 8px 8px 0 0;
        }

        .category-contain>div h5 {
            padding: 10px;
        }

        .best-sellers-new {
            overflow-x: auto;
            flex-wrap: nowrap;
            display: flex;
            flex-flow: row wrap;
            width: 100%;
        }

        .best-sellers-new::-webkit-scrollbar {
            width: 4px;
            height: 10px;
            background-color: #ededed;
        }

        .best-sellers-new::-webkit-scrollbar-thumb {
            background-color: #000;
            border-radius: 10px;
            height: 5px;
        }

        .best-sellers-new>div {
            flex: 0 0 auto;
            min-height: 0;
            min-width: 0;
            width: 60%;
            height: auto;
            margin: 12px 15px 20px 15px;
            padding: 20px;
            box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .best-sellers-new>div a {
            color: #000;
        }

        .best-sellers-new>div img {
            display: block;
            width: 100%;
            height: auto;
        }

        .best-sellers-new>div p {
            margin-bottom: 0;
        }

        @media (min-width: 640px) {
            .category-contain {
                grid-template-columns: 1fr 1fr 1fr;
            }

            .best-sellers-new>div {
                width: 25%;
            }
        }

        @media (min-width: 992px) {
            .category-contain {
                grid-template-columns: 1fr 1fr 1fr 1fr;
            }

            .best-sellers-new>div {
                width: 20%;
            }
        }

        @media (min-width: 1200px) {
            .category-contain {
                grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            }
        }
    </style>
    </script>

</head>

<body>

    <?php include_once('header.php'); ?>
    <div class="main">
        <div class="container splash">
            <div class="splash">
                <h1
                    style="
                font-size: 25px;
                line-height: 22px;
                color: #000000;
                margin: 0;
                text-align: center;
                ">
                    Welcome to Pharma Industry, delivering quality healthcare solutions and innovation since 1994.
                </h1>
            </div>

        </div>
        <div class="container splash">

            <img src="<?= $uploads . $banner_image ?>" alt="Celebrate! 10% Off Sitewide" class="img-responsive img-ctr lazyload" data-srcset="<?= $uploads . $banner_image ?>" style="height: 300px; width: 113rem; max-width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            margin: 20px auto;
            display: block;
            border-radius: 8px;
            padding: 10px;" />
            <!-- <div>
                <a
                    href="#"
                    class="banner-ad">
                    <?php
                    $banner_image = 'webshop/default_banner.png';
                    if (!empty($this->data['website_setting'])) {
                        foreach ($this->data['website_setting'] as $item) {
                            if ($item->fields === 'banner_image' && !empty($item->value)) {
                                $banner_image = $item->value;
                                break;
                            }
                        }
                    }
                    ?>
                    <img src="<?= $uploads . $banner_image ?>" alt="Celebrate! 10% Off Sitewide" class="img-responsive img-ctr lazyload" data-srcset="<?= $uploads . $banner_image ?>" style="height: 300px; width: 113rem; max-width: 100%;
            box-shadow: 0 4px 12px rgba(161, 129, 129, 0.2);
            margin: 20px auto;
            display: block;
            border-radius: 8px;
            padding: 10px;" />
                </a>
            </div> -->
        </div>
    </div>
    <?php
    $homePageSectionsArr = $this->data['custom_pages']['header_strip'];
    foreach ($homePageSectionsArr as $section) {
        if ($section['page_key'] == 'homepagewelcomemessagesection') $homePageWelcomeMessageSection = $section;
        if ($section['page_key'] == 'homepagecompanycertificationsection') $homePageCompanyCertificationSection = $section;
        if ($section['page_key'] == 'homepagebulletpoints') $homePageBulletPoints = $section;
        if ($section['page_key'] == 'homepagecompanyupdatessection') $homePageCompanyUpdatesSection = $section;
    }
    ?>
    <div class="container">
        <h1
            style="
            font-size: 25px;
            line-height: 22px;
            color: #567c34;
            margin: 10;
            " class="text-ban-be">
            Welcome to Pharma Industry, delivering quality healthcare solutions and innovation since 1994.
        </h1>
        <br />
        <p>
            <?php
            // $homePageWelcomeMessageSection = $this->data['custom_pages']->homepagewelcomemessagesection;
            if ($homePageWelcomeMessageSection)
                echo !empty($homePageWelcomeMessageSection['page_text']) ? $homePageWelcomeMessageSection['page_text'] : "";
            ?>
        </p>
    </div>

    <div class="section">
        <div class="container">
            <h2 class="h-section"><span>Shop by Category</span></h2>
            <div class="category-contain">
                <?php
                $baseUrl = base_url('webshop/');
                $categoriesArray = $this->data["categories"];
                if (is_array($categoriesArray)) {
                    $categories = $categoriesArray["main"];
                    foreach ($categories as $category) {
                ?>
                        <div>
                            <a
                                href="<?= $baseUrl . 'category_products/' . $category->id ?>"
                                class="item-alt">
                                <img
                                    src="<?= $category->image ? $uploads . $category->image : $thumbs . 'no_image.png' ?>"
                                    alt="Trending" />
                                <h5><?= $category->name; ?></h5>
                            </a>
                        </div>
                <?php
                    }
                } ?>

            </div>
        </div>
    </div>
    <div class="container" id="bullet-points">
        <?php
        // $homePageBulletPoints = $this->data['custom_pages']->homepagebulletpoints;
        if ($homePageBulletPoints)
            echo !empty($homePageBulletPoints['page_text']) ? $homePageBulletPoints['page_text'] : "";
        ?>
    </div>
    <!-- <div class="section best-sellers-alt">
        <div class="container">
        </div>
    </div> -->

    <!-- <div class="section nwupdate">
        <div class="container"> -->
    <?php
    // $homePageCompanyUpdatesSection = $this->data['custom_pages']->homepagecompanyupdatessection;
    if ($homePageCompanyUpdatesSection)
        if (!empty($homePageCompanyUpdatesSection['page_text'])) { ?>
        <div class="section nwupdate">
            <div class="container"> <?php }
                            echo $homePageCompanyUpdatesSection['page_text'];
                                    ?>
            </div>
        </div>

        <!-- <div class="container">
        <h1 class="bullet-head">
            Why Choose Herbinn Micro Medicines<sup>®</sup>
        </h1>
        <br />
        <ul>
            <li>
                <strong>Trusted Since 1996</strong>: Over two decades of expertise in
                natural health.
            </li>
            <li>
                <strong>High-Quality Ingredients</strong>: Every product is formulated
                with premium, science-backed ingredients.
            </li>
            <li>
                <strong>Manufactured in the USA</strong>: Produced under cGMP
                regulations for guaranteed safety and quality.
            </li>
            <li>
                <strong>Recommended by Experts</strong>: Trusted by healthcare
                professionals worldwide.
            </li>
        </ul>
        <br />
        <h1 class="bullet-head">
            Explore Our Products
        </h1>
        <br />
        <p>
            Whether you're looking for <strong>liver detox supplements</strong>,
            <strong>kidney health essentials</strong>, or
            <strong>immune system support</strong>, Herbinn Micro Medicines has the
            solutions to help you live a healthier, more productive life.
        </p>
        <p>
            Call us today at <strong>0-000-0-0000</strong> or explore our full
            range of
            <strong>Herbinn Micro Medicines at
                <a class="company-link">herbinnmicromedicines.com</a></strong>.
        </p>
    </div> -->

        <!-- <div
            class="section our-company2"
            style="padding-bottom: 20px; background: #fff">
            <div class="container"> -->
        <!-- <h2 class="h-section">
                <span>Product Focused, Quality Verified</span>
            </h2>
            <div class="row">
                <div class="col-sm-4" style="margin-bottom: 20px">
                    <img
                        class="seal-img"
                        alt="Doctor" />
                    <p>
                        For over 20 years, our supplements have been recommended by
                        healthcare professionals and sold worldwide. When developing,
                        formulating and producing supplements, we focus on science,
                        quality, and proven performance.
                    </p>
                </div>
                <div class="col-sm-4" style="margin-bottom: 20px">
                    <img
                        class="seal-img"
                        alt="GMPSeal" />
                    <p>
                        All ingredients used are of the highest quality, and all Herbinn Micro Medicines supplements are manufactured in the USA under strict
                        current Good Manufacturing Practice (cGMP) regulations ensuring
                        all safety and quality standards are met.
                    </p>
                </div>
                <div class="col-sm-4" style="margin-bottom: 20px">
                    <img
                        class="seal-img"
                        alt="LabTestedSeal" />
                    <p>
                        All products are tested both before and after production to
                        confirm that what is on the product label matches what is in each
                        and every capsule and tablet.
                    </p>
                </div>
            </div>
            <div style="margin-bottom: 30px">
                <img
                    class="seal-img"
                    alt="ManufacturedintheUSASeal" />
                <img
                    class="seal-img"
                    alt="MadeinFDARegisteredFacilitySeal" />
            </div> -->
        <?php
        // $homePageCompanyCertificationSection  = $this->data['custom_pages']->homepagecompanycertificationsection;
        if ($homePageCompanyCertificationSection) {
            if (!empty($homePageCompanyCertificationSection['page_text'])) { ?>
                <div
                    class="section our-company2"
                    style="padding-bottom: 20px; background: #fff">
                    <div class="container">
                        <?=
                        $homePageCompanyCertificationSection['page_text'];
                        ?>
                    </div>
                </div> <?php
                    }
                }
                // echo !empty($homePageCompanyCertificationSection->page_text) ? $homePageCompanyCertificationSection->page_text : "";
                        ?>
        <!-- </div>
        </div> -->



        <?php include_once("footer.php"); ?>
        <div class="search-overlay"></div>
        <div
            class="modal fade"
            id="modal-free-shipping"
            tabindex="-1"
            role="dialog"
            aria-labelledby="modal_banner_fine_print"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size: 120%">
                            FREE standard shipping only available in the contiguous United
                            States.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <script src="<?= $assets . 'nw_theme/js/main.js?ver=200406' ?>"></script>
        <script src="<?= $assets . 'nw_theme/js/jquery.magnific-popup-new.min.js' ?>"></script>
        <script src="<?= $assets . 'nw_theme/js/welcome-message.js?ver=221017_01' ?>"></script>
        <script src="<?= $assets . 'nw_theme/js/jquery.scrolldepth.min.js' ?>"></script>
        <script>
            jQuery(function() {
                jQuery.scrollDepth();
            });
        </script>


        <div
            id="chat-widget-container"
            style="
            opacity: 1;
            visibility: visible;
            z-index: 2147483639;
            position: fixed;
            bottom: 0px;
            width: 84px;
            height: 84px;
            max-width: 100%;
            max-height: calc(100% + 0px);
            min-height: 0px;
            min-width: 0px;
            background-color: transparent;
            border: 0px;
            overflow: hidden;
            right: 0px;
            transition: none !important;
        ">
            <!-- <iframe
            allow="clipboard-read; clipboard-write; autoplay; display-capture *;"
            src="https://secure.livechatinc.com/customer/action/open_chat?license_id=8217371&amp;group=1&amp;group=1&amp;embedded=1&amp;widget_version=3&amp;unique_groups=0&amp;organizationId=57290da6-4259-4362-aa80-f8d28b0a0191&amp;use_parent_storage=1&amp;x-region=us-south1"
            id="chat-widget"
            name="chat-widget"
            title="LiveChat chat widget"
            scrolling="no"
            style="
            width: 100%;
            height: 100%;
            min-height: 0px;
            min-width: 0px;
            margin: 0px;
            padding: 0px;
            background-image: none;
            background-position: 0% 0%;
            background-size: initial;
            background-attachment: scroll;
            background-origin: initial;
            background-clip: initial;
            background-color: rgba(0, 0, 0, 0);
            border-width: 0px;
            float: none;
            color-scheme: normal;
            position: absolute;
            inset: 0px;
            transition: none !important;
            display: none;
            visibility: hidden;
            "></iframe><iframe
            id="chat-widget-minimized"
            name="chat-widget-minimized"
            title="LiveChat chat widget"
            scrolling="no"
            style="
            width: 100%;
            height: 100%;
            min-height: 0px;
            min-width: 0px;
            margin: 0px;
            padding: 0px;
            background-image: none;
            background-position: 0% 0%;
            background-size: initial;
            background-attachment: scroll;
            background-origin: initial;
            background-clip: initial;
            background-color: rgba(0, 0, 0, 0);
            border-width: 0px;
            float: none;
            color-scheme: normal;
            display: block;
            "></iframe> -->
            <div
                aria-live="polite"
                id="lc-aria-announcer-polite"
                tabindex="-1"
                style="
            height: 1px;
            width: 1px;
            margin: -1px;
            overflow: hidden;
            white-space: nowrap;
            border: 0px;
            padding: 0px;
            position: absolute;
            "></div>
            <div
                aria-live="assertive"
                id="lc-aria-announcer-assertive"
                tabindex="-1"
                style="
            height: 1px;
            width: 1px;
            margin: -1px;
            overflow: hidden;
            white-space: nowrap;
            border: 0px;
            padding: 0px;
            position: absolute;
            "></div>
        </div>



        <script defer type="text/javascript" src="<?= $assets ?>nw_theme/js/index.js"></script>
        <script defer type="module/javascript" src="<?= $assets ?>nw_theme/js/common.js"></script>

        <script>
            const baseUrl = "<?= baseUrl('webshop') ?>"
            const message = "<?= $this->session->flashdata('message') ?>";
            const assets = "<?= $assets ?>";
            const websitePhoneNumber = "<?= $phone_number ?>";
        </script>

</body>

</html>