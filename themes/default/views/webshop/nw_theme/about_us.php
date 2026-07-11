<!-- HTML -->
<html lang="en" webcrx="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - <?= !empty($this->data['title']) ? $this->data['title'] : "Default Website Title" ?></title>
    <meta name="description"
        content="Established in the year 1994 we Herbinn Micro Medicines">
    <link rel="canonical" href="https://herbinnmicromedicines.elintpos.in/webshop">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,400italic,700,700italic|Oxygen:700" rel="stylesheet"
        type="text/css">
    <link href="<?= $assets ?>nw_theme/css/main.css?ver=210616_01" rel="stylesheet" media="screen"
        charset="utf-8">
    <link href="<?= $assets ?>nw_theme/css/print.css" rel="stylesheet" type="text/css" media="print"
        charset="utf-8">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= $assets ?>nw_theme/css/jquery-ui.min.css" rel="stylesheet" type="text/css" charset="utf-8">
    <link href="<?= $assets ?>nw_theme/css/new-template.css?ver=230912_06" rel="stylesheet" type="text/css"
        media="screen" charset="utf-8">
    <link rel="stylesheet" href="<?= $assets ?>nw_theme/css/about-us.css">
    <link rel="icon" type="image/x-icon" href="<?= $uploads ?>webshop/herbinn_favicon.ico">
    <link rel="stylesheet" href="<?= $assets ?>nw_theme/css/common.css">


</head>

<body class="" style="top: 101px;">
    <?php include_once('header.php') ?>
    <div class="main ">
        <div class="container">
            <div class="main-row">
                <div class="main-content about-us">
                    <?=
                    $about_us->page_text ? $about_us->page_text : ''
                    ?>
                </div>


            </div>
        </div>
    </div>

    <?php include_once('footer.php') ?>
    <script src="<?= $assets ?>nw_theme/js/main.js?ver=200406"></script>
    <script src="<?= $assets ?>nw_theme/js/jquery.magnific-popup-new.min.js"></script>
    <script src="<?= $assets ?>nw_theme/js/welcome-message.js?ver=221017_01"></script>
    <script src="<?= $assets ?>nw_theme/js/jquery.scrolldepth.min.js"></script>
    <script>
        jQuery(function() {
            jQuery.scrollDepth();
        });
    </script>
</body>

</html>