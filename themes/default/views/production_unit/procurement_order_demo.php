<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?= $assets ?>production_unit/css/style.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <!-- Include Redactor.js script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/redactor/3.5.4/redactor.js"></script>
    <!-- Include Redactor.css for styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/redactor/3.5.4/redactor.css">
</head>

<style>
/* Style for search container */
.search-container {
    position: relative;
    width: 100%;
    /* Adjust width as needed */
}

/* Style for search box */
.search-box {
    width: 100%;
    padding: 6px 15px 5px 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    outline: none;
}

.bg-set {
    text-align: left !important;
}

/* Style for search button */
.search-button {
    position: absolute;
    right: 0;
    top: 0;
    padding: 5px 8px;
    /* Adjust padding to match search box */
    border: none;
    background: none;
    color: #555;
    cursor: pointer;
}

#note {
    display: none;
    border: 1px solid #ccc;
    padding: 10px;
    margin-top: 10px;
}

#app {
    width: 100%;
    /* Adjust as needed */
    max-width: 650px;
    /* Adjust as needed */
    margin: 0 auto;
}

#editor {
    width: 100%;
    height: 200px;
    /* Adjust as needed */
    resize: both;
    /* Allows both horizontal and vertical resizing */
    overflow: auto;
    /* Adds scrollbar when content exceeds the size */
    word-wrap: break-word;
    /* Breaks long words to prevent overflow */
    white-space: pre-wrap;
    /* Preserves whitespace and wraps text */
    padding: 10px;
    box-sizing: border-box;
}

.fa-arrow-left:before {
    content: "\f060";
    /* Unicode for the left arrow icon in Font Awesome */
}

.fa-arrow-right:before {
    content: ">";
    /* Unicode for the left arrow icon in Font Awesome */
}

.active-li {
    background-color: #039be5 !important;
    /* Change to your desired color */
    color: white;
    /* Optional: Change text color */
}

#procurement_order .btn-sty:hover {
    background-color: #039be5 !important;
    color: #fff;
}

#procurement_order .clr-yellow {
    background-color: #FFEFCF !important;
}

.view-set {
    width: 32%;
    margin-right: 5px;
    border: 1px solid #0088cc;
    line-height: 2.6rem;
    text-align: center;
}

.view-brd {
    border: 1px solid #0088cc;
}

.wd-set {
    width: 16%;
}

.wd-set2 {
    width: 16%;
}

.custom-hide {
    display: none !important;
}




#procurement_order .set-bg-81 {
    margin-top: 1rem;
    padding: 1rem;
    border: 2px solid #dfdfdf;
    background: #f3f3f3;
    border-radius: 0.3rem;
    margin-bottom: 3rem;
}

#procurement_order .set-bg-81 .red-dot {
    font-size: 15px;
    /* Adjust the size as needed */
    color: #CB1010;
    /* Change the color to red */
    margin-right: 0.5rem;
    /* Adjust the spacing as needed */
}

.text-left {
    text-align: start
}

.fa-hand-o-left:before {
    content: "\f0a5";
    font-size: 2rem;
}

.bg-light-y {
    background: #FFEFCF !important;

}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var menuList1 = document.getElementById("menuList1");
    var menuList2 = document.getElementById("menuList2");
    var menuList3 = document.getElementById("menuList3");

    menuList1.addEventListener("click", function(event) {
        var target = event.target;
        if (target && target.dataset.category === "Cake") {
            menuList1.style.display = "none";
            menuList2.style.display = "block";
        }
    });

    menuList2.addEventListener("click", function(event) {
        var target = event.target;
        if (target && target.dataset.category === "Cake") {
            menuList2.style.display = "none";
            menuList1.style.display = "block";
        }
    });

    menuList1.addEventListener("click", function(event) {
        var target = event.target;
        if (target && target.dataset.category === "Chat") {
            menuList1.style.display = "none";
            menuList3.style.display = "block";
        }
    });

    menuList3.addEventListener("click", function(event) {
        var target = event.target;
        if (target && target.dataset.category === "Chat") {
            menuList3.style.display = "none";
            menuList1.style.display = "block";
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var pineappleCakeBtn = document.getElementById("pineappleCakeBtn");
    var VegSamosaBtn1 = document.getElementById("VegSamosaBtn1");
    var orderTable = document.getElementById("orderTable");
    var orderTable1 = document.getElementById("orderTable1");
    var orderTable12 = document.getElementById("orderTable12");
    var orderTable2 = document.getElementById("orderTable2");
    var orderTable3 = document.getElementById("orderTable3");
    var btnHide1 = document.getElementById("btn-hide1");
    var btnHide112 = document.getElementById("btn-hide112");
    // var reset_button = document.querySelector(".reset-btn");

    function toggleResetButton() {
        var tables = [orderTable, orderTable1, orderTable12, orderTable2, orderTable3];
        var showButton = false;

        tables.forEach(function(table) {
            var tbody = table.querySelector("tbody");
            if (tbody && tbody.children.length > 0) {
                showButton = true;
            }
        });

        // reset_button.style.display = showButton ? "block" : "none";
    }

    pineappleCakeBtn.addEventListener("click", function() {
        orderTable.style.display = "table";
        orderTable1.style.display = "none";
        orderTable1.querySelector("tbody").innerHTML = "";

        var tbody = orderTable.querySelector("tbody");
        tbody.innerHTML = `
            <tr>
                <td>Baked Goods</td>
                <td>Pineapple Cake</td>
                <td><input type="number" class="input-w" value="1"></td>
                <td>Unit</td>
                <td>$10.00</td>
                <td><i class="fa fa-trash"></i></td>
            </tr>
            <tr>
                <td class="font-weight">Total Items</td>
                <td></td>
                <td class="font-weight">1</td>
                <td class="font-weight">Order Total</td>
                <td></td>
                <td class="font-weight">Rs. 10750</td>
            </tr>
        `;

        var startNewOrderTabLink = document.querySelector('a[href="#tabs-1"]');
        if (startNewOrderTabLink) startNewOrderTabLink.click();

        btnHide1.style.display = "block";
        btnHide112.style.display = "block";

        document.querySelectorAll('li.btn').forEach(function(li) {
            li.classList.remove('active-li');
        });
        pineappleCakeBtn.classList.add('active-li');

        toggleResetButton();
    });

    VegSamosaBtn1.addEventListener("click", function() {
        orderTable1.style.display = "table";
        orderTable.style.display = "none";
        var btnHide11 = document.getElementById("btn-hide11");
        orderTable.querySelector("tbody").innerHTML = "";

        var tbody = orderTable1.querySelector("tbody");
        tbody.innerHTML = `
            <tr>
                <td>Baked Goods</td>
                <td>Pineapple Cake</td>
                <td><input type="number" class="input-w" value="1"></td>
                <td>Pcs.</td>
                <td>Rs. 3750</td>
                <td><i class="fa fa-trash"></i></td>
            </tr>
            <tr>
                <td>Chaat</td>
                <td>Veg Samosa</td>
                <td><input type="number" class="input-w" value="200"></td>
                <td>Pcs.</td>
                <td>Rs. 2000</td>
                <td><i class="fa fa-trash"></i></td>
            </tr>
            <tr>
                <td class="font-weight">Total Items</td>
                <td></td>
                <td class="font-weight">2</td>
                <td class="font-weight">Order Total</td>
                <td></td>
                <td class="font-weight">Rs. 10750</td>
            </tr>
        `;

        var startNewOrderTabLink = document.querySelector('a[href="#tabs-1"]');
        if (startNewOrderTabLink) startNewOrderTabLink.click();

        var viewOrderTabLink = document.querySelector('a[href="#tabs-1"]');
        if (viewOrderTabLink) viewOrderTabLink.click();

        btnHide11.style.display = "block";

        toggleResetButton();
    });

    var update_Orders = document.getElementById("update_Orders");
    var updateOrderLinks = document.querySelectorAll(".update-link");

    function updateOrder() {
        var tbody = orderTable12.querySelector("tbody");
        tbody.innerHTML = `
            <tr class="text-center">
                <td>Baked Goods</td>
                <td>Pineapple Cake</td>
                <td><input type="number" class="input-w" value="10"></td>
                <td>Pcs.</td>
                <td>Rs. 3750</td>
                <td><i class="fa fa-trash"></i></td>
            </tr>
            <tr>
                <td>Chaat</td>
                <td>Veg Samosa</td>
                <td><input class="input-w" type="number" value="200"></td>
                <td>Pcs.</td>
                <td>Rs. 2000</td>
                <td><i class="fa fa-trash"></i></td>
            </tr>
            <tr>
                <td class="font-weight">Total Items</td>
                <td></td>
                <td class="font-weight">2</td>
                <td class="font-weight">Order Total</td>
                <td></td>
                <td class="font-weight">Rs. 5750</td>
            </tr>
        `;

        // Add CSS classes
        // document.querySelectorAll(".set-bg-8").forEach(element => {
        //     element.classList.add("custom-hide");
        // });
        // document.querySelectorAll(".wd-set2").forEach(element => {
        //     element.classList.add("custom-hide");
        // });
        // document.querySelectorAll(".wd-set1").forEach(element => {
        //     element.classList.add("full-width");
        // });

        orderTable12.style.display = "table";
        update_Orders.style.display = "none";
        var updateOrderTabLink = document.querySelector('a[href="#tabs-2"]');
        updateOrderTabLink.click();

        var trashIcons = document.querySelectorAll(".fa-trash");
        trashIcons.forEach(function(icon) {
            icon.addEventListener("click", function() {
                var row = icon.closest("tr");
                row.style.display = "none";
                toggleResetButton();
            });
        });

        toggleResetButton();
    }

    updateOrderLinks.forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            updateOrder();
        });
    });

    var Partial_Orders = document.getElementById("Partial_Orders");
    var viewOrderLinks = document.querySelectorAll(".view-link");

    function viewOrder() {
        var tbody = orderTable2.querySelector("tbody");
        tbody.innerHTML = `
            <tr class="text-center">
                <td>Baked Goods</td>
                <td>Pineapple Cake</td>
                <td>200</td>
                <td>150</td>
                <td>50</td>
                <td><i class="fa fa-plus-circle" id="plusIcon1"></i><span id="addedText1" style="display: none; color:#00C314">Added to Current Order</span></td>
            </tr>
            <tr>
                <td>Chaat</td>
                <td>Veg Samosa</td>
                <td>250</td>
                <td>225</td>
                <td>25</td>
                <td><i class="fa fa-plus-circle" id="plusIcon2"></i><span id="addedText2" style="display: none; color:#00C314">Added to Current Order</span></td>
            </tr>
            <tr>
                <td class="font-weight">Total Items</td>
                <td></td>
                <td class="font-weight">2</td>
                <td class="font-weight">Order Total</td>
                <td></td>
                <td class="font-weight">Rs. 5750</td>
            </tr>
        `;

        // Add CSS classes
        document.querySelectorAll(".set-bg-8").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set2").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set1").forEach(element => {
            element.classList.add("full-width");
        });

        orderTable2.style.display = "table";
        Partial_Orders.style.display = "none";
        var viewOrderTabLink = document.querySelector('a[href="#tabs-3"]');
        viewOrderTabLink.click();

        var plusIcons = document.querySelectorAll(".fa-plus-circle");
        plusIcons.forEach(function(icon) {
            icon.addEventListener("click", function() {
                icon.style.display = "none";
                var addedText = icon.nextElementSibling;
                if (addedText) {
                    addedText.style.display = "inline";
                }
                toggleResetButton();
            });
        });

        toggleResetButton();
    }

    viewOrderLinks.forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            viewOrder();
        });
    });


    var Pending_Orders = document.getElementById("Pending_Orders");
    var dispatchOrderLinks = document.querySelectorAll(".dispatch-link");

    function dispatchOrder() {
        var tbody = orderTable3.querySelector("tbody");
        tbody.innerHTML = `
            <tr class="text-center">
                <td>Baked Goods</td>
                <td>Pineapple Cake</td>
                <td>200</td>
                <td>200</td>
                <td><i class="fa fa-plus-circle" id="plusIcon3"></i><span id="addedText3" style="display: none; color:#00C314">Added to Current Order</span></td>
            </tr>
            <tr>
                <td>Chaat</td>
                <td>Veg Samosa</td>
                <td>225</td>
                <td>225</td>
                <td><i class="fa fa-plus-circle" id="plusIcon4"></i><span id="addedText4" style="display: none; color:#00C314">Added to Current Order</span></td>
            </tr>
            <tr>
                <td class="font-weight">Total Items</td>
                <td></td>
                <td class="font-weight">2</td>
                <td class="font-weight">Order Total</td>
                <td></td>
                <td class="font-weight">Rs. 5750</td>
            </tr>
        `;

        // Add CSS classes
        document.querySelectorAll(".set-bg-8").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set2").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set1").forEach(element => {
            element.classList.add("full-width");
        });

        orderTable3.style.display = "table";
        Pending_Orders.style.display = "none";
        var dispatchOrderTabLink = document.querySelector('a[href="#tabs-4"]');
        dispatchOrderTabLink.click();

        var plusIcons = document.querySelectorAll(".fa-plus-circle");
        plusIcons.forEach(function(icon) {
            icon.addEventListener("click", function() {
                icon.style.display = "none";
                var addedText = icon.nextElementSibling;
                if (addedText) {
                    addedText.style.display = "inline";
                }
                toggleResetButton();
            });
        });

        toggleResetButton();
    }

    dispatchOrderLinks.forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            dispatchOrder();
        });
    });

    toggleResetButton();
});
</script>
<!-- view orders only -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var Received_orders = document.getElementById("Received_orders");
    var orderTable3 = document.getElementById("orderTable3");
    var viewOrderLinks = document.querySelectorAll(".view-link1");

    function viewOrder1() {
        // Add dummy content to the table rows
        var tbody = orderTable3.querySelector("tbody");
        tbody.innerHTML = `
                    <tr class="text-center">
                        <td>Cake</td>
                        <td>Pineapple Cake<br>1kg-#PC1234</td>
                        <td>10</td>
                        <td>10</td>
                        <td>
                        <input type="text" name="quantity[]" class="view-set">
                        <span class="ml-2 text-center"><input type="checkbox" name="allot[]" value="1" class="large-checkbox view-brd"></span>
                        </td>
                        <td>Pcs.</td>
                        <td>Rs. 3750</td>
                    </tr>
                    <tr>
                        <td>Chaat</td>
                        <td>Veg Samosa<br>#SA1234</td>
                        <td>200</td>
                        <td>100</td>
                        <td>
                        <input type="text" name="quantity[]" class="view-set">
                        <span class="ml-2 text-center"><input type="checkbox" name="allot[]" value="1" class="large-checkbox view-brd"></span>
                        </td>
                        <td>Pcs.</td>
                        <td>Rs.1000</td>
                    </tr>
                    <tr>
                        <td>Chaat</td>
                        <td>Veg Veg Puff<br>#VP1234</td>
                        <td>250</td>
                        <td>200</td>
                        <td>
                        <input type="text" name="quantity[]" class="view-set">
                        <span class="ml-2 text-center"><input type="checkbox" name="allot[]" value="1" class="large-checkbox view-brd"></span>
                        </td>
                        <td>Pcs.</td>
                        <td>Rs.4000</td>
                    </tr>
                    <tr>
                        <td class="font-weight">Total Items</td>
                        <td></td>
                        <td class="font-weight">3</td>
                        <td class="font-weight">Order Total</td>
                        <td></td>
                        <td></td>
                        <td class="font-weight">Rs. 5750</td>
                    </tr>
                `;

        // Add CSS classes
        document.querySelectorAll(".set-bg-8").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set2").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set1").forEach(element => {
            element.classList.add("full-width");
        });

        // Show the order table 3
        orderTable3.style.display = "table";
        // Hide the Received orders
        Received_orders.style.display = "none";

        // Simulate clicking on the "Start New Order" tab
        var viewOrderLink = document.querySelector('a[href="#tabs-4"]');
        viewOrderLink.click();
    }

    // Loop through each view link and add event listener
    viewOrderLinks.forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault(); // Prevent the default action of the link
            viewOrder1();
        });
    });
});
</script>
<!-- view orders only End-->

<!-- recive order start -->

<script>
document.addEventListener("DOMContentLoaded", function() {
    var orderTable3 = document.getElementById("orderTable3");
    var orderTable4 = document.getElementById("orderTable4");
    var received_button = document.querySelectorAll(".received_button");

    function viewOrder12() {
        // Add dummy content to the table rows
        var tbody = orderTable4.querySelector("tbody");
        tbody.innerHTML = `
                    <tr class="text-center">
                        <td>Cake</td>
                        <td>Pineapple Cake<br>1kg-#PC1234</td>
                        <td>10</td>
                        <td>10</td>
                        <td>Pcs.</td>
                        <td>Rs. 3750</td>
                    </tr>
                    <tr>
                        <td>Chaat</td>
                        <td>Veg Samosa<br>#SA1234</td>
                        <td>200</td>
                        <td class="set-bg-81">100 <i class="fa fa-circle red-dot"></i></td>
                        <td>Pcs.</td>
                        <td>Rs.1000</td>
                    </tr>
                    <tr>
                        <td>Chaat</td>
                        <td>Veg Veg Puff<br>#VP1234</td>
                        <td>250</td>
                        <td class="set-bg-81">100 <i class="fa fa-circle red-dot"></i></td>
                        <td>Pcs.</td>
                        <td>Rs.4000</td>
                    </tr>
                    <tr>
                        <td class="font-weight">Total Items</td>
                        <td></td>
                        <td class="font-weight">3</td>
                        <td class="font-weight">Order Total</td>
                        <td></td>
                        <td class="font-weight">Rs. 5750</td>
                    </tr>
                `;

        // Add CSS classes
        document.querySelectorAll(".set-bg-8").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set2").forEach(element => {
            element.classList.add("custom-hide");
        });
        document.querySelectorAll(".wd-set1").forEach(element => {
            element.classList.add("full-width");
        });

        // Show the order table 3
        orderTable4.style.display = "table";
        // Hide the Received orders
        orderTable3.style.display = "none";

        // Simulate clicking on the "Start New Order" tab
        var viewOrderLink = document.querySelector('a[href="#tabs-4"]');
        viewOrderLink.click();
    }

    // Loop through each view link and add event listener
    received_button.forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault(); // Prevent the default action of the link
            viewOrder12();
        });
    });
});
</script>
<!-- recive order End -->


<script>
document.addEventListener("DOMContentLoaded", function() {
    var navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(function(item) {
        item.addEventListener('click', function() {
            // Get the ID of the clicked tab's link
            var linkId = this.querySelector('.nav-link').id;

            if (linkId.includes('startNewOrderTabLink')) {
                // Specific handling for "Current Orders" tab
                document.querySelectorAll('.wd-set2').forEach(function(element) {
                    element.classList.remove('custom-hide');
                });

                document.querySelectorAll('.wd-set1').forEach(function(element) {
                    element.classList.add('sixty-eight-percent');
                    element.classList.remove('full_width');
                });
                document.querySelectorAll('.set-bg-8').forEach(function(element) {
                    element.classList.add('set-bg-81');
                    element.classList.remove('custom-hide');
                });

            } else if (linkId.includes('open_orders')) {
                // Specific handling for "Open Orders" tab
                document.querySelectorAll('.wd-set2').forEach(function(element) {
                    element.classList.remove('custom-hide');
                });

                document.querySelectorAll('.wd-set1').forEach(function(element) {
                    element.classList.add('sixty-eight-percent');
                    element.classList.remove('full_width');
                });
                document.querySelectorAll('.set-bg-8').forEach(function(element) {
                    element.classList.add('custom-hide');
                    element.classList.remove('set-bg-81');
                });

            } else {
                // Handling for other tabs
                document.querySelectorAll('.wd-set2').forEach(function(element) {
                    element.classList.add('custom-hide');
                });
                document.querySelectorAll('.wd-set1').forEach(function(element) {
                    element.classList.remove('sixty-eight-percent');
                    element.classList.add('full_width');
                });
                document.querySelectorAll('.set-bg-8').forEach(function(element) {
                    element.classList.add('custom-hide');
                    element.classList.remove('set-bg-81');
                });
            }
        });
    });

    // Add custom CSS
    const style = document.createElement("style");
    style.textContent = `
        .custom-hide {
            display: none !important;
        }
        .full_width {
            width: 85% !important;
        }
        .sixty-eight-percent {
            width: 68% !important;
        }
        .set-bg-81 {
            /* Add any specific styles for set-bg-81 if needed */
        }
    `;
    document.head.appendChild(style);
});

</script>


<script>
// Ensure the DOM is fully loaded before executing JavaScript
$(document).ready(function() {
    // Initialize Redactor.js
    $('#editor').redactor({
        toolbar: [
            ['format', 'bold', 'italic', 'underline', 'deleted'],
            ['unorderedlist', 'orderedlist', 'outdent', 'indent'],
            ['link', 'image', 'video'],
            ['html']
        ]
    });
});
</script>

<script>
$(document).ready(function() {
    // Hide the editor row initially
    $('#editorRow').hide();

    // Attach click event handler to the button
    $('#btn-hide112').click(function() {
        // Toggle the visibility of the editor row
        $('#editorRow').toggle();
    });
});
</script>


<body>
    <section id="procurement_order">
        <div class="container-fluid">
            <div class="row">
                <div class="wd-set p-0 bg-setting">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-barcode icon-th"></i><span class="break"></span>Orders
                        </h2>
                    </div>

                    <div class="mrgn-tp">
                        <ul class="nav nav-tabs nav-justified m-4 nav-set" role="tablist">
                            <li class="nav-item active p-0 mt-1">
                                <a class="nav-link bg-set" id="startNewOrderTabLink" data-toggle="tab" href="#tabs-1"
                                    role="tab">Current Orders</a>
                            </li>
                            <li class="nav-item p-0 mt-1">
                                <a class="nav-link bg-set" id="open_orders" data-toggle="tab" href="#tabs-2"
                                    role="tab">Open Orders
                                </a>
                            </li>
                            <li class="nav-item p-0 mt-1">
                                <a class="nav-link bg-set" data-toggle="tab" href="#tabs-3" role="tab">Partial
                                    Orders</a>
                            </li>

                            <li class="nav-item p-0 mt-1">
                                <a class="nav-link bg-set" data-toggle="tab" href="#tabs-4" role="tab">Previous Orders
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>

                <div class="wd-set1 sty-bg-set full_width sixty-eight-percent">
                    <div class="d-flex align-items-center set-bg-8">
                        <h3 class="m-0_10 font-weight mt-set">
                            Request a Delivery Date :
                        </h3>

                        <form>
                            <div class="input-group w-set">
                                <input type="text" id="birthday" name="birthday" class="form-control"
                                    placeholder="Select a date">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </form>
                        <div class="w-set1 ml-auto">
                            <h4>
                                <i class="fa fa-people"> </i> Jane Doe
                            </h4>
                        </div>

                    </div>

                    <div class="tab-content overflowset">
                        <div class="tab-pane fade in active text-center" id="tabs-1" role="tabpanel">
                            <div class="col-md-12 p-0" id="orderTable">
                                <table class="table table-bordered rounded-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Sub Total</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>

                                <div class="col-md-12" id="editorRow">

                                    <div id="app">
                                        <textarea id="editor"></textarea>

                                    </div>
                                </div>


                                <div class="d-flex row col-md-12 p-0">
                                    <button class="btn" id="btn-hide1" style="display: none;" type="submit">Place
                                        Order</button>

                                    <button class="btn" id="btn-hide112" style="display: none;" type="submit">
                                        <i class="fa fa-file-text-o mr-set"></i> Add Note </button>
                                </div>
                            </div>
                            <div class="col-md-12 p-0" id="orderTable1" style="display: none;">
                                <table class="table table-bordered rounded-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Sub Total</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>

                                <div class="d-flex row col-md-12 p-0">
                                    <button class="btn" id="btn-hide11" style="display: none;" type="submit">Place
                                        Order</button>
                                </div>
                            </div>
                            <div class="col-md-12 p-0" id="orderTable1" style="display: none;">
                                <table id="" class="table table-bordered rounded-table" style="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Sub Total</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>
                                <button class="btn" id="btn-hide12" style="" type="submit">Place
                                    Order</button>
                            </div>
                        </div>


                        <div class="tab-pane fade" id="tabs-2" role="tabpanel">

                            <div class="col-md-12 p-0" id="update_Orders">

                                <div class="d-flex align-items-center set-bg-81">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        <i class="fa fa-hand-o-left"></i> All Open Orders
                                    </h3>
                                </div>

                                <table class="table table-bordered rounded-table" id="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order #</th>
                                            <th scope="col">Total Items</th>
                                            <th scope="col">Placed On</th>
                                            <th scope="col">Placed By</th>
                                            <th scope="col">Update</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-center">
                                            <td>#A1B2C3D4E5</td>
                                            <td>3</td>
                                            <td>05/10/2024</td>
                                            <td>@Jane D</td>
                                            <td><a href="#" id="updateOrderLink" class="update-link">Update Order</a>
                                            </td>
                                        </tr>
                                        <tr class="text-center">
                                            <td>#Z0Y9X8W7V6</td>
                                            <td>5</td>
                                            <td>05/10/2024</td>
                                            <td>John K</td>
                                            <td><a href="#" id="updateOrderLink" class="update-link">Update Order</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>


                            <div class="text-center col-md-12 p-0" id="orderTable12" style="display: none;">
                                <div class="d-flex align-items-center set-bg-81">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        Request a Delivery Date :
                                    </h3>

                                    <form>
                                        <div class="input-group w-set">
                                            <input type="text" id="birthday" name="birthday" class="form-control"
                                                placeholder="Select a date">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </form>
                                    <div class="">
                                        <button class="reset-btn">Reset orders</button>
                                    </div>
                                    <div class="w-set1 ml-auto">
                                        <h4>
                                            <i class="fa fa-people"> </i> Jane Doe
                                        </h4>
                                    </div>

                                </div>

                                <table id="" class="table table-bordered rounded-table" style="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Sub Total</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>
                                <button class="btn" id="btn-hide1" type="submit">Update Order</button>

                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabs-3" role="tabpanel">
                            <div class="col-md-12 p-0" id="Partial_Orders">
                                <div class="d-flex align-items-center set-bg-81">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        <i class="fa fa-hand-o-left"></i> Partially Received Orders
                                    </h3>
                                </div>

                                <table class="table table-bordered rounded-table" id="Partial_Orders">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order #</th>
                                            <th scope="col">Total Items</th>
                                            <th scope="col">Placed On</th>
                                            <th scope="col">Placed By</th>
                                            <th scope="col">Received On</th>
                                            <th scope="col">Update</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-center">
                                            <td>#A1B2C3D4E5</td>
                                            <td>3</td>
                                            <td>05/10/2024</td>
                                            <td>@Jane D</td>
                                            <td>05/12/2024</td>
                                            <td><a href="#" id="viewOrderLink" class="view-link">View</a></td>
                                        </tr>
                                        <tr class="text-center">
                                            <td>#Z0Y9X8W7V6</td>
                                            <td>5</td>
                                            <td>05/10/2024</td>
                                            <td>John K</td>
                                            <td>05/12/2024</td>
                                            <td><a href="#" id="viewOrderLink" class="view-link">View</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-12 p-0 text-center" id="orderTable2" style="display: none;">

                                <div class="d-flex align-items-center set-bg-81 bg-light-y">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        <i class="fa fa-hand-o-left"></i> Order #A1B2C3D4E5
                                    </h3>
                                </div>

                                <table id="" class="table table-bordered rounded-table" style="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Qty.<br>
                                                Requested.</th>
                                            <th scope="col">Qty.<br>
                                                Received</th>
                                            <th scope="col">Difference</th>
                                            <th scope="col">Add to Current Order</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>
                                <button class="btn" id="btn-hide1" type="submit"> Go to Current Order</button>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabs-4" role="tabpanel">

                            <div class="col-md-12 p-0" id="Received_orders">
                                <div class="d-flex align-items-center set-bg-81">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        <i class="fa fa-hand-o-left"></i> Previous Orders
                                    </h3>
                                </div>
                                <table class="table table-bordered rounded-table" id="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order #</th>
                                            <th scope="col">Total Items</th>
                                            <th scope="col">Placed On</th>
                                            <th scope="col">Placed By</th>
                                            <th scope="col">Received On</th>
                                            <th scope="col">View Order</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-center clr-yellow">
                                            <td>#A1B2C3D4E5</td>
                                            <td>3</td>
                                            <td>05/31/2024</td>
                                            <td>Jane D</td>
                                            <td>--</td>
                                            <td><a href="#" id="viewOrderLink1" class="view-link1">View</a></td>
                                        </tr>

                                        <tr class="text-center">
                                            <td>#A1B2C3D4E5</td>
                                            <td>5</td>
                                            <td>05/25/2024</td>
                                            <td>John K</td>
                                            <td>05/26/2024</td>
                                            <td><a href="#" id="viewOrderLink1" class="view-link1">View</a></td>
                                        </tr>

                                        <tr class="text-center">
                                            <td>#Z0Y9X8W7V6</td>
                                            <td>10</td>
                                            <td>05/10/2024</td>
                                            <td>Nancy P</td>
                                            <td>05/12/2024</td>
                                            <td><a href="#" id="viewOrderLink1" class="view-link1">View</a></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-12 p-0 text-center" id="orderTable3" style="display: none;">
                                <div class="d-flex align-items-center set-bg-81 bg-light-y">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        <i class="fa fa-hand-o-left"></i> Order #A1B2C3D4E5 - In Progress
                                    </h3>
                                </div>
                                <table id="" class="table table-bordered rounded-table" style="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Ordered Qty.<br>
                                            <th scope="col">Allotted Qty.<br>
                                            <th scope="col">Received Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Sub Total</th>

                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>
                                <button class="btn received_button" id="btn-hide1" type="submit">Received
                                    Order</button>
                            </div>


                            <div class="col-md-12 p-0 text-center" id="orderTable4" style="display: none;">


                                <div class="d-flex align-items-center set-bg-81 bg-light-y">
                                    <h3 class="m-0_10 font-weight mt-set">
                                        <i class="fa fa-hand-o-left"></i> Order #A1B2C3D4E5 - Received
                                    </h3>
                                </div>

                                <table id="" class="table table-bordered rounded-table" style="">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Ordered Qty.<br>
                                            <th scope="col">Received Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <!-- Dummy content will be added here -->
                                    </tbody>
                                </table>
                                <button class="btn" id="btn-hide1" type="submit">Repeat Order</button>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="wd-set2 p-0 bg-setting">
                    <div class="search-container">
                        <form action="search.php" method="GET">
                            <input type="text" placeholder="Search..." name="query" class="search-box">
                            <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
                        </form>
                    </div>

                    <div class="row mrgn-tp padding-set">
                        <div class="">
                            <ul id="menuList1" class="menu-list">
                                <!-- First list -->
                                <li class="btn btn-sty" data-category="Baked Goods" type="submit">Baked Goods</li>
                                <li class="btn btn-sty" data-category="Cake" type="submit">Cake</li>
                                <li class="btn btn-sty" data-category="Sweets" type="submit">Sweets</li>
                                <li class="btn btn-sty" data-category="Chat" type="submit">Chaat</li>
                                <li class="btn btn-sty" data-category="Namkeen" type="submit">Namkeen</li>
                            </ul>

                            <ul id="menuList2" class="menu-list" style="display: none;">
                                <!-- Second list -->
                                <li class="btn btn-sty set-bdr-none" data-category="Cake" type="submit">
                                    <span class="font-weight">
                                        <i class="fa fa-hand-o-left"></i> </span> Baked Goods <span class="font-weight">
                                        > </span> cakes
                                </li>
                                <li class="btn btn-sty" id="pineappleCakeBtn" data-category="Sweets" type="submit">
                                    Pineapple Cake</li>
                                <li class="btn btn-sty" data-category="Chaat" type="submit">Mango Cake</li>
                                <li class="btn btn-sty" data-category="Namkeen" type="submit">Chocolate Cake</li>
                                <li class="btn btn-sty" data-category="Lorem Ipsum" type="submit">Strawberry Cake
                                </li>
                                <li class="btn btn-sty" data-category="Lorem Ipsum" type="submit">Truffle Cake</li>
                                <li class="btn btn-sty" data-category="Lorem Ipsum" type="submit">Blueberry Cake
                                </li>
                                <li class="btn btn-sty" data-category="Lorem Ipsum" type="submit">Red Velvet Cake
                                </li>
                            </ul>


                            <ul id="menuList3" class="menu-list" style="display: none;">
                                <!-- Second list -->
                                <li class="btn btn-sty" data-category="Chat" type="submit">
                                    <span class="font-weight">
                                        <i class="fa fa-hand-o-left"></i> </span> Chat > Samosa
                                </li>
                                <li class="btn btn-sty" id="VegSamosaBtn1" data-category="Sweets" type="submit">
                                    Veg Samosa</li>
                                <li class="btn btn-sty" data-category="Namkeen" type="submit">Patti Samosa</li>
                                <li class="btn btn-sty" data-category="Lorem Ipsum" type="submit">Paneer Samosa</li>
                            </ul>


                        </div>
                        <!-- <ul id="dynamicList">
                          
                        </ul> -->

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    $(function() {
        // Initialize datepicker
        $("#birthday").datepicker({
            dateFormat: "MM dd, yy", // Set the date format to "Month Day, Year"
            showOn: "focus" // Only show datepicker on input focus
        });

        // Set the default date to today
        $("#birthday").datepicker("setDate", new Date());

        // Show date picker when clicking on the calendar icon
        $(".input-group-addon").on('click', function() {
            $("#birthday").focus(); // Focus on the input to show datepicker
        });
    });
    </script>
</body>

</html>