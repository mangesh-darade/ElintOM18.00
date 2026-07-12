<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Dynamic Meta Tags -->
    <?= $meta_tags ?>
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/webshop.css') ?>">
    
    <style>
        /* Basic CMS styling */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .product-card { border: 1px solid #eee; padding: 15px; text-align: center; border-radius: 8px; transition: transform 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-image img { max-width: 100%; height: auto; margin-bottom: 15px; }
        .product-info h3 { font-size: 1.1rem; margin-bottom: 10px; }
        .product-info .price { font-weight: bold; color: #e67e22; font-size: 1.2rem; }
        .add-to-cart-btn { background: #e67e22; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        .section-title { text-align: center; margin-bottom: 30px; font-size: 2rem; }
    </style>
</head>
<body>
    
    <div id="cms-render-context">
        <?= $page_content ?>
    </div>

    <!-- Core Scripts -->
    <script src="<?= base_url('assets/js/jquery.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Handle Add to Cart via AJAX if needed
            $('.add-to-cart-btn').on('click', function() {
                const productId = $(this).data('id');
                console.log('Adding product ' + productId + ' to cart...');
                // Implement AJAX call to Webshop controller here
            });
        });
    </script>
</body>
</html>
