<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Print Page</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" media="all">
        <script>
        // Enhanced print function for Web and Android
        var contentLoaded = false;
        var imagesLoaded = false;
        
        function checkIfReadyToPrint() {
            return contentLoaded && imagesLoaded;
        }
        
        function printPage() {
            // Check if content is ready
            if (!checkIfReadyToPrint()) {
                console.log('Waiting for content to load...');
                setTimeout(printPage, 300);
                return;
            }
            
            // For Android WebView compatibility
            if (typeof Android !== 'undefined' && Android.print) {
                console.log('Using Android print interface');
                try {
                    Android.print();
                    window.location.href = "pos";
                } catch (e) {
                    console.error('Android print failed, using web print', e);
                    window.print();
                    window.location.href = "pos";
                }
            } else {
                // Standard web print
                console.log('Using web print interface');
                window.print();
                window.location.href = "pos";
            }
        }
        
        // Load event to ensure page is ready
        document.addEventListener('DOMContentLoaded', function() {
            contentLoaded = true;
            console.log('DOM Content Loaded');
        });
        
        // Optional: Auto-redirect after printing (uncomment if needed)
        // window.onafterprint = function() {
        //     setTimeout(function() {
        //         window.location.href = "<?= site_url('pos/index') ?>";
        //     }, 500);
        // };
        </script>
        <script>
        function handleButtonClick() {
            window.location.href = "<?= site_url('pos/index') ?>/" + <?= $suspend_data->id ?>;
        }
        function handleButtonClick1() {
            window.location.href = "<?= site_url('pos/index') ?>/";
        }
        </script>
        <style>
        /* ============================================
           SCREEN STYLES (Normal View)
           ============================================ */
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        #wrapper {
            max-width: fit-content;
            margin: 0 auto;
            padding-top: 20px;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .well {
            padding: 0.5rem;
            margin-bottom: 3px;
            background-color: #ddd;
            border: 1px solid #e3e3e3;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
        }

        p {
            font-size: 10pt;
            margin: 0 0 5px;
        }
        
        .print-content {
            background: white;
            padding: 20px;
        }

        /* ============================================
           PRINT STYLES (For Web & Android)
           ============================================ */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        
        @media print {
            /* Hide non-print elements */
            .no-print,
            button,
            .btn,
            .cusom-flexsetting {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Reset everything for print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            html, body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                font-size: 10pt !important;
            }
            
            /* Reset Bootstrap containers */
            .container-fluid,
            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .row {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }
            
            .col-md-6,
            .col-md-8,
            [class*="col-"] {
                width: 100% !important;
                max-width: 100% !important;
                padding: 10px !important;
                margin: 0 !important;
                float: none !important;
                display: block !important;
            }
            
            /* Remove Bootstrap spacing utilities */
            .py-5, .py-4, .py-3, .py-2, .py-1,
            .px-5, .px-4, .px-3, .px-2, .px-1,
            .p-5, .p-4, .p-3, .p-2, .p-1 {
                padding: 5px !important;
            }
            
            .mt-2, .mt-3, .mt-4, .mt-5 {
                margin-top: 5px !important;
            }
            
            .mb-2, .mb-3, .mb-4, .mb-5 {
                margin-bottom: 5px !important;
            }
            
            /* Text alignment */
            .text-center {
                text-align: center !important;
            }
            
            .text-right,
            .text-end {
                text-align: right !important;
            }
            
            .text-left,
            .text-start {
                text-align: left !important;
            }
            
            /* Typography */
            h3 {
                font-size: 16pt !important;
                margin: 5px 0 !important;
                font-weight: bold !important;
            }
            
            p {
                margin: 3px 0 !important;
                font-size: 10pt !important;
                line-height: 1.4 !important;
            }
            
            strong {
                font-weight: bold !important;
            }
            
            /* QR Code and Logo section */
            .order_barcodes {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin: 10px 0 !important;
                width: 100% !important;
            }
            
            .order_barcodes img,
            .order_barcodes canvas {
                max-width: 80px !important;
                max-height: 80px !important;
                height: auto !important;
                width: auto !important;
            }
            
            /* Customer and Invoice Info */
            .d-flx {
                display: flex !important;
                justify-content: space-between !important;
                margin: 10px 0 !important;
                width: 100% !important;
            }
            
            .d-flx > div {
                flex: 1 !important;
            }
            
            /* Table styles */
            .table {
                width: 100% !important;
                margin: 15px 0 !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
            }
            
            .table thead {
                display: table-header-group !important;
            }
            
            .table tbody {
                display: table-row-group !important;
            }
            
            .table tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            
            .table th,
            .table td {
                padding: 6px 8px !important;
                font-size: 10pt !important;
                border: 1.5px solid #333 !important;
                vertical-align: middle !important;
            }
            
            .table thead th {
                background-color: #428BCA !important;
                color: white !important;
                font-weight: bold !important;
                text-align: center !important;
            }
            
            .table tbody td {
                background-color: white !important;
            }
            
            /* Custom font class */
            .custom-font {
                font-size: 10pt !important;
            }
            
            /* Ensure content fits on page */
            .print-content,
            .print-wrapper {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        .d-flx {
            display: flex;
            justify-content: space-between;
            /* align-items:end; */
        }

        .table .thead-light th {
            background: #428BCA;
            color: #fff;
            font-weight: bold;
        }

        .table-bordered {
            border: 1.5px solid #aeaeae !important;
        }

        thead.thead-light.text-center{
            border: 1.5px solid #aeaeae!important;
        }

        .table td,
        .table th {
            padding: .3rem;
            border: 1.5px solid #aeaeae!important;
        }

        .btn-sky {
            background: #428BCA;
            color: #fff;
            font-weight: 700;
        }

        .qrimg {
            width: 10%;
        }

        .custom-font {
            font-size: 0.75rem;
        }
        .cusom-flexsetting{
            display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        }

        #deleteSuspend{
            background:#AA0000;
        }

        .backtopos{
        background: #fff;
        color: #333;
        border: 2px solid #009dff;
        }
        
        /* Fallback styles for when Bootstrap doesn't load */
        .container, .container-fluid {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .justify-content-center {
            justify-content: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        .py-5 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }
        </style>
    </head>
    <?php
session_start();
$_SESSION['flag'] = "back_to_pos";
    ?>
    <body class="print-wrapper">
        <span id="currency_symbol" style="display:none;"><?= $Settings->default_currency_symbol ? $Settings->default_currency_symbol : 'Rs.' ?></span>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div id="" class="col-md-6 py-5" style="padding: 20px;"  data-print-padding="0">
                    <!-- <?= $this->sma->save_barcode($customer_detail->reference_no, 'code128', 66, false); ?> -->
                    <div class="order_barcodes" style="display: flex; align-items: center; justify-content:space-between;">
                    <?php
                     
                    ?>   
                    <?= $this->sma->qrcode('link', $refNo->reference_no, 4); ?>
                        <img src="<?= $assets ?>pos/images/UnpaidLogo.svg" alt="Logo" style="display: block; width: 10%;" />

                    </div>

                    <div class="container">
                        <div class="text-center mt-2">
                            <h3 style="text-transform:uppercase; margin-bottom: 0px;">
                                <?= $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                            <div class="row justify-content-center">
                                <div class="text-center col-md-8">
                                    <?php
                                    echo "<p style='margin: 0 0 5px;'>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' .
                                    lang("tel") . ":&nbsp;" . $biller->phone .', '. lang("email") . ";&nbsp;" . $biller->email; 
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flx">
                            <div>
                                <!-- <h5>Billed To</h5> -->
                                <!-- <p><strong>Customer: <?= $item_detail->name ?></strong></p> -->
                                <p><strong>Customer:</strong> <?= $customer_detail->name ?></p>

                                <?php if ($customer_detail->name == 'Walk in Customer') { ?>
                                <!-- <p><strong>Mobile:</strong> <?= $customer_detail->phone ?></p> -->
                                <?php }else { ?>
                                <p><strong>Mobile:</strong> <?= $customer_detail->phone ?></p>
                                <?php  }?>
                                <!-- <p><strong>Email:</strong> <?= $item_detail->email ?></p>
                                <p><strong>Address:</strong> <?= $item_detail->address ?></p> -->
                            </div>

                            <div class="">
                                <!-- <h5 class="text-right">Invoice</h5> -->
                                <p class="text-right"><strong>Date:</strong> <?= $customer_detail->date ?></p>
                                <!-- <p class="text-right"><strong>Invoice No.:</strong> <?= $item_details->date ?></p> -->
                                <p class="text-right"><strong>Reference No:</strong>
                                    <?= $customer_detail->reference_no ?>
                                </p>
                            </div>

                        </div>

                        <table class="table table-bordered custom-font">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Product Name</th>
                                    <th>Product Code</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $total_quantity = 0; // To accumulate total quantity
                                    $total_amount = 0; // To accumulate total amount
                                    $index = 0; 
                                    ?>

                                <?php foreach ($item_details as $items): ?>
                                <tr>
                                    <td><?= ++$index ?></td>
                                    <td><?= htmlspecialchars($items->product_name) ?></td>
                                    <td><?= htmlspecialchars($items->product_code) ?></td>
                                    <td class="text-center"><?= number_format($items->quantity,2) ?></td>
                                    <td class="text-right"><?= $this->sma->formatMoney($items->unit_price) ?></td>
                                    <td class="text-right">
                                        <?php 
                                            $amount = $items->quantity * $items->unit_price; 
                                            $total_quantity += $items->quantity; // Accumulate total quantity
                                            $total_amount += $amount; // Accumulate total amount
                                        ?>
<?= $this->sma->formatMoney($amount) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-center"><?= number_format($total_quantity,2) ?></td>
                                    <td class="text-right"></td>
                                    <td class="text-right"><?= $this->sma->formatMoney(ceil($total_amount)) ?></td>
                                </tr>



                                <?php 
                                    $formatter = new NumberFormatter('en_US', NumberFormatter::SPELLOUT);
                                    $grandTotal = $formatter->format(ceil($total_amount));
                                ?>
                                <tr>
                                    <td colspan="5" class="text-start">
                                        <div class="d-flex justify-content-between">
                                            <strong>Grand Total:</strong>
                                            <span class="text-right"><?php echo '( ' . strtoupper(($grandTotal)) . ' RUPEES ONLY )'; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-right"><?= $this->sma->formatMoney(ceil($total_amount)) ?></td>
                                </tr>
                            </tbody>
                        </table>


                        
                            <span class="pull-right col-xs-12">
                                <button class="btn  btn-block no-print btn-sky"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);"
                                    onclick="printPage()">Print</button>
                            </span>
                            <div class="cusom-flexsetting">  
                            <span class="pull-right col-md-3 p-0">
                                <button class="btn no-print btn-sky col-md-12 backtopos"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);"
                                    onclick="handleButtonClick()">Add More Items</button>

                            </span>
                            <span class="pull-right col-md-3 p-0">
                                <button class="btn no-print btn-sky col-md-12 backtopos"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);"
                                    onclick="handleButtonClick1()">Next Customer</button>

                            </span>
                            <span class="pull-right col-md-3 p-0">
                                <button id="deleteSuspend" class="btn no-print btn-sky col-md-12"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);">Cancel</button>
                            </span>
                        </div>

                        <!-- <p><a href="http://localhost/elintpos_in_15.01_AWS_India/reciept/pdf/0b71c90f0d8a3aaa5ebdbdc6eada7c7a"
                        class="btn btn-primary">Download Receipt</a></p> -->

                        <!-- <div class="col-xs-12 mt-4 no-print" style="background:#f7f2f2; padding:1rem">
                            <h5 style="font-weight:bold;"><strong>Printing Instructions:</strong></h5>
                            <ul>
                                <li style="text-transform: capitalize;">Please disable the header and footer in browser print
                                    settings.</li>
                                <li style="text-transform: capitalize;"><strong>Chrome:</strong> Menu > Print > Disable
                                    Header/Footer in Options & Set Margins to None.</li>
                            </ul>
                        </div> -->

                    </div>

                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>

    </html>
    <script>
    $(document).ready(function() {
        // Delete suspend button handler
        $('#deleteSuspend').on('click', function() {
            window.location.href = "<?= site_url('pos/deleteSuspendForUnpaidInvoice') ?>/" + <?= $suspend_data->id ?>;
        });
        
        // Track image loading for print readiness
        var images = document.getElementsByTagName('img');
        var canvases = document.getElementsByTagName('canvas'); // QR codes might be canvas
        var totalElements = images.length + canvases.length;
        var loadedElements = 0;
        
        function checkAllLoaded() {
            loadedElements++;
            console.log('Loaded ' + loadedElements + ' of ' + totalElements + ' elements');
            
            if (loadedElements >= totalElements) {
                imagesLoaded = true;
                console.log('All images and QR codes loaded - Ready to print!');
            }
        }
        
        // Check images
        if (images.length > 0) {
            for (var i = 0; i < images.length; i++) {
                if (images[i].complete) {
                    checkAllLoaded();
                } else {
                    images[i].addEventListener('load', checkAllLoaded);
                    images[i].addEventListener('error', function() {
                        console.warn('Image failed to load');
                        checkAllLoaded();
                    });
                }
            }
        }
        
        // If QR code is canvas, mark as loaded
        if (canvases.length > 0) {
            setTimeout(function() {
                for (var i = 0; i < canvases.length; i++) {
                    checkAllLoaded();
                }
            }, 500);
        }
        
        // If no images/canvas, mark as loaded
        if (totalElements === 0) {
            imagesLoaded = true;
            console.log('No images to load');
        }
        
        // Fallback: Set loaded after 2 seconds anyway
        setTimeout(function() {
            if (!imagesLoaded) {
                console.log('Timeout reached - enabling print anyway');
                imagesLoaded = true;
            }
        }, 2000);
    });
    
    // Page fully loaded event
    window.addEventListener('load', function() {
        console.log('Window loaded event fired');
    });

    </script>