$(document).ready(function () {

    //////////////////////////////////////////////////Left Menu////////////////////////////////////////////////////////////////////////////////////////////
    // search products
    $('.search-box').on('keyup', function () {
        var searchText = $(this).val().toLowerCase();
        $('.mainmenu li').each(function () {
            var productText = $(this).text().toLowerCase();
            var isVisible = productText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });
    });

    // Function to populate the product list dynamically
    // function productList(products) {

    //     $('.mainmenu').empty();
    //     $.each(products, function (index, product) {
    //         var activeClass = index === 0 ? ' active' : '';
    //         var productText = product.name + ' (' + product.order_quantity + '/' + product.stock_quantity + ')';
    //         var productItem = '<li class=" btn-sty product" data-product-id="' + product.id + '">' + productText + '</li>';
    //         $('.mainmenu').append(productItem);
    //     });
    //     setTimeout(function () {
    //         $('.mainmenu .product:first').trigger('click'); // Trigger click event for the first product to load its details immediately
    //     }, 100);
    // }
    // productList(products);
    // $(document).on('click', '.product', function () {
    //     $('.product').removeClass('active');
    //     $(this).addClass('active');
    //     var productId = $(this).data('product-id');
    //     localStorage.setItem('productId', productId);
    //     // var productDetails = JSON.parse(localStorage.getItem('productDetails'));
    //     // $('#productName').text(productDetails.name);
    //     var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time
    //     orderDetails(productId, manufacturingDate);

    // });





    // Function to log out
    function logout() {
        // Clear the stored product ID on logout
        localStorage.removeItem('productId');
        // Redirect or refresh page as needed
        location.reload(); // Reload the page to apply changes
    }

    // Add click handler for the logout button
    $('#logoutButton').click(function () {
        logout(); // Call the logout function when the logout button is clicked
    });

    // Function to display the product list
    function productList(products) {
        // products to the list are added from here 
        var storedProductId = localStorage.getItem('productId'); // Retrieve stored product ID

        $('.mainmenu').empty();
        $.each(products, function (index, product) {
            var activeClass = product.id == storedProductId ? ' active' : ''; // Check if it matches stored product ID
            var productText = product.name + ' (' + product.order_quantity + '/' + product.stock_quantity + ')';
            var productItem = '<li class="btn-sty product' + activeClass + '" data-product-id="' + product.id + '">' + productText + '</li>';
            $('.mainmenu').append(productItem);
        });

        setTimeout(function () {
            // activate the first warehouse or production unit
            $('.mainmenu .product:first').addClass('active').trigger('click');
        }, 100);
    }

    // Function to display the warehouseList
    function warehouseList(warehouses) {
        // warehouses to the list are added from here 
        $('.mainmenu').empty();
        $.each(warehouses, function (index, warehouse) {
            var activeClass = warehouse.name == warehouses[0].name ? ' active' : ''; // Check if it matches stored warehouse ID
            var warehouseText = warehouse.name;
            var warehouseItem = '<li class="btn-sty warehouse' + activeClass + '" data-warehouse-id="' + warehouse.id + '">' + warehouseText + '</li>';
            $('.mainmenu').append(warehouseItem);
        });

        setTimeout(function () {
            // activate the first warehouse
            $('.mainmenu .warehouse:first').addClass('active').trigger('click');
        }, 100);
    }

    // Call warehouseList function to initialize when the document is ready

    // product list on side nav's function ----- populated here  
    // $(document).ready(function () {
    // productList(products);
    // });

    // function to populate cards  
    function populateCards(warehouseId, arr) {
        let targetElement = document.getElementById("cards-section");
        targetElement.innerHTML = "";
        let count = 0;
        for (let i = 0; i < arr.length; i++) {

            if (String(warehouseId) === arr[i]["location_id"]) {
                count++;
                let newElement = document.createElement("div");
                const stock_quantity = (isNaN(arr[i].stock_quantity) || arr[i].stock_quantity === null) ? 0 : arr[i].stock_quantity;
                const order_quantity = (isNaN(arr[i].total_order_quantity) || arr[i].total_order_quantity === null) ? 0 : arr[i].total_order_quantity;
                const build_quantity = ((Number(order_quantity) > Number(stock_quantity)) ? (Number(order_quantity) - Number(stock_quantity)) : 0);
                const color = ((Number(stock_quantity) < Number(order_quantity)) ? "yellow-color" : (Number(stock_quantity) === 0 ? "red-color" : "green-color"));
                newElement.innerHTML = `
                <div class="card-div showTable" product_id=${arr[i].product_id}>
                    <div class="table-content">            
                        <div class="${color}-card col hover" >
                                <p class="p-0 item-prod white-text">${arr[i].name}</p>
                            <div class="table-div" >
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center quantity_text_heading" >Stock</th>
                                            <th scope="col" class="text-center quantity_text_heading">Order Quantity</th>
                                            <th scope="col" class="text-center quantity_text_heading" style="border-right:0px;">Build</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center quantity_text" id=${Number(arr[i].product_id)}>${stock_quantity}</td>
                                            <td class="text-center quantity_text">${order_quantity}</td>
                                            <td class="text-center quantity_text" style="border-right : 0px;">${build_quantity}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div >                        
                        </div > 
                    </div>
                </div>
            `
                targetElement.append(newElement);
            }
        }
        targetElement.style.display =  'grid';
        if (!count) {
            newElement = document.createElement("div");
            newElement.innerHTML = '<h2 class="no-product" >There are no products added to the unit</h2>';
            targetElement.append(newElement);
            targetElement.style.display =  'block';
            return;
        }
    }

    //  --------------- populate prod_units and populate card with each item details-------------------------
    $(document).ready(function () {
        if (warehousesWithProductsDetails["warehouses"].length) {
            warehouseList(warehousesWithProductsDetails["warehouses"]);
            const warehouseId = document.getElementsByClassName("warehouse active")[0].getAttribute("data-warehouse-id");
            populateCards(warehouseId, warehousesWithProductsDetails["productsDetails"]);
            return;
        }
        warehouseList([{ name: "THERE ARE NO LOCATIONS TO DISPLAY", id: 0 }]);
        return;
    });


    // Click event for product items
    // $(document).on('click', '.product', function () {
    //     $('.product').removeClass('active');
    //     $(this).addClass('active');
    //     var productId = $(this).data('product-id');
    //     localStorage.setItem('productId', productId); // Store selected product ID
    //     var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time
    //     orderDetails(productId, manufacturingDate);    //   order details table below quantity table is called from here -------- ashitosh
    // });

    // Click event for production_unit
    $(document).on('click', '.warehouse', function () {
        $('.warehouse').removeClass('active');
        $(this).addClass('active');
        const warehouseId = document.getElementsByClassName("warehouse active")[0].getAttribute("data-warehouse-id");
        populateCards(warehouseId, warehousesWithProductsDetails["productsDetails"]);
        localStorage.clear();
    });

    //////////////////////////////////////////////////show product wise orders in grid////////////////////////////////////////////////////////////////////////////////////////////

    async function orderDetails(productId, manufacturingDate) {  // is invoked when clicked on an item in nav to show oreder details
        await $.ajax({
            url: site.base_url + "Production_Unit/getProductWiseAllDetails",
            method: 'GET',
            data: {
                productId: productId,
                manufacturingDate: manufacturingDate
            },
            dataType: 'json',
            success: function (response) {

                $.each(response, function (index, OrderDetails) {

                    var productStock = OrderDetails.productStock;
                    var productBatches = OrderDetails.productBatches;
                    var latestProductBatches = OrderDetails.latestProductBatches;
                    var productDetails = OrderDetails.productDetails;
                    var lastBatch = OrderDetails.lastBatch;
                    var locationCode = OrderDetails.locationCode;

                    if (OrderDetails.OrderDetails && OrderDetails.OrderDetails.length > 0) {
                        loadOrderItems(OrderDetails.OrderDetails);
                    }
                    else {
                        resetQuantities(productStock.stock_quantity);  // If no order details found, clear and reset quantities
                        $('#tablebody').empty();
                    }

                    createBatchNymber(lastBatch, locationCode);  // create new batch number against product
                    if (productDetails) {
                        // console.log("productDetails", productDetails);
                        $('#unit').text(productDetails.unit_name);
                        $('#price').text(productDetails.price);
                        $('#productName').text(productDetails.name);

                    }
                    if (productBatches) {
                        // console.log("productBatches", productBatches);
                        productBatches.forEach(function (batch) {
                            $('#expiryDate').text(batch.expiry_date);
                        });
                    } else {
                        $('#expiryDate').text(productDetails.expiryDate);
                    }
                    if (productBatches && latestProductBatches) {
                        // console.log("latestProductBatches", latestProductBatches);
                        localStorage.setItem('productBatches', JSON.stringify(productBatches));
                        localStorage.setItem('latestProductBatches', JSON.stringify(latestProductBatches));
                        // console.log("wrrote");
                    } else {
                        localStorage.removeItem('productBatches');
                        localStorage.removeItem('latestProductBatches');
                        $('#batchtablebody').empty();
                        $('#latestbatchestablebody').empty();
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }

    //create new batch number against location and product
    function createBatchNymber(lastBatch, locationCode) {

        var productId = localStorage.getItem('productId', productId);

        if (lastBatch) {
            var batch_no = lastBatch.batch_no;
            var parts = batch_no.split('/');  // Split the batch number by '/'
            var lastBatchNumber = parts[parts.length - 1];  // Get the last part which contains the last three digits
            var newBatchNumber = (parseInt(lastBatchNumber, 10) + 1).toString().padStart(3, '0'); // Increment and pad the batch number

        } else {
            var newBatchNumber = '001';
        }
        var newBatchNo = locationCode + '/' + productId + '/' + newBatchNumber; // Display the new batch number
        $('#batchId').text(newBatchNo);
    }

    // load Orders in grid
    function loadOrderItems(OrderDetails) {
        $('#tablebody').empty();
        let totalOrderQuantity = 0; // Initialize total order quantity
        let totalAllottedQuantity = 0; // Initialize total allotted quantity

        OrderDetails.forEach(function (orders) {

            var stockQuantity = parseFloat(orders.stock_quantity);
            var orderQuantity = parseFloat(orders.order_quantity); // Update to get the current order's quantity
            totalOrderQuantity += orderQuantity; // Add the current order's quantity to the total
            var note = orders.note;
            var infoicon = '';
            var procurementOrderRefNo = orders.procurement_order_ref_no;
            var orderStatus = orders.order_status;


            // Check if the note is not blank, then display the info icon
            if (note && note.trim() !== '') {
                infoicon = '<i class="fa fa-info-circle info-note ml-auto mr-2 cursor" data-toggle="modal" data-id="' + orders.procurement_orders_id + '" data-note="' + note + '" data-ref="' + procurementOrderRefNo + '"></i>';
            }
            // var calculatedQty = calculateQuantity(orders, totalOrderQuantity); 
            // var calculatedQty = calculateQuantity(orders, totalOrderQuantity,totalAllottedQuantity); 

            // var buildQuantity = calculatedQty.build_quantity;
            var isChecked = localStorage.getItem('checkbox_' + orders.itemId);
            var allotQuantityInput = localStorage.getItem('allotQuantity_' + orders.itemId); // get allot quantity
            var allotQuantity = allotQuantityInput ? parseFloat(allotQuantityInput) : 0;
            totalAllottedQuantity += allotQuantity;
            var calculatedQty = calculateQuantity(orders, totalOrderQuantity, totalAllottedQuantity);
            var buildQuantity = calculatedQty.build_quantity;

            var orderCreationDate = new Date(orders.order_creation_date);

            if (orders.item_status == 'Open') {
                var allotQuantity = '';
            } else {
                var allotQuantity = allotQuantityInput ? allotQuantityInput : calculatedQty.allot_quantity;
            }

            var row = $("<tr class='text-center bg-light-orange'>" +
                "<td class='cen-set' style='width:28%'><p class='circle-set'>" + orders.procurement_order_ref_no + infoicon + "</p></td>" +
                //"<td style='width:25%'>" + orders.order_creation_date + "</td>" +
                "<td style='width:25%' id='orderAge_" + orders.itemId + "'>" + "<span id='orderAgeValue_" + orders.itemId + "'></span><br>" + "<span id='orderAgeFormat_" + orders.itemId + "'></span>" + "</td>" +
                "<td style='width:20%'>" + orders.order_quantity + "</td>" +
                //"<td class='d-flx11'>" + "<input type='number' class='allot-set' name='quantity[]' style='width:20%; text-align:right;' value='" + "' " + ">" +
                "<td class='d-flx11'>" + "<input type='number' class='allot-set' name='allotQuantity[]' style='width:35%; text-align:right; color: grey;' value='" + allotQuantity + "' >" +
                "<span class='ml-3'><input type='checkbox' class='large-checkbox' name='allot[]' id='checkbox_" + orders.itemId + "' value='" + orders.itemId + "' " + (isChecked ? 'checked' : '') + "></span></td>" +
                "</tr>");

            if (orderStatus == 'Open' || orderStatus == 'Completed') {
                row.find('.allot-set').prop('disabled', true);
                row.find('.large-checkbox').prop('disabled', true);
            }
            if (isChecked) {
                row.find('.allot-set').prop('disabled', true);
            }


            $("#tablebody").append(row);

            $('#stockQuantity').text(stockQuantity);
            // $('#orderQuantity').text(orderQuantity);
            $('#buildQuantity').text(buildQuantity);

            // Update order age and format text every second
            setInterval(function () {
                var formattedOrderAge = calculateOrderAge(orderCreationDate);
                $('#orderAgeValue_' + orders.itemId).text(formattedOrderAge);

                // Update format text
                var formatText = productionOrderAgeFormat == 1 ? 'DD:HH:MM:SS' : 'HH:MM:SS';
                $('#orderAgeFormat_' + orders.itemId).text(formatText);
            }, 1000);

            // validation for input allot qty
            var inputField = row.find('input.allot-set');
            inputField.on('input', function () {

                var inputValue = parseFloat($(this).val());
                var stockQuantity = parseFloat(orders.stock_quantity);
                var orderQuantity = parseFloat(orders.order_quantity);

                // Check if the input value is not empty and is a valid integer
                // if (inputValue !== '' && !Number.isInteger(Number(inputValue))) {
                //     alert('Please enter an integer value.');
                //     var intValue = parseInt(inputValue, 10);
                //     $(this).val(intValue); 
                //     return;
                // }
                if (inputValue > stockQuantity) {
                    $(this).val(stockQuantity);
                    alert('Allocation quantity cannot exceed stock quantity.');
                    return;
                }
                if (inputValue > orderQuantity) {
                    $(this).val(orderQuantity);
                    alert('Input value cannot exceed order quantity.');
                    return;
                }
                if (inputValue < 0) {
                    $(this).val('0');
                    alert('Input value cannot be negative.');
                    return;
                }
            });
            // Initial check for the checkbox state and set colour 
            if (isChecked) {
                var inputValue = parseFloat(row.find('input.allot-set').val());
                var stockQuantity = parseFloat(orders.stock_quantity);
                var orderQuantity = parseFloat(orders.order_quantity);

                if (inputValue === orderQuantity) {
                    row.css('background', 'linear-gradient(90deg, #00C314, rgba(0, 195, 20, 0))'); // Green

                    // } else if (inputValue > orderQuantity || stockQuantity !== 0) {
                } else if (inputValue > orderQuantity) {
                    row.css('background', 'linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))'); // Red

                } else if (orderQuantity > inputValue && inputValue !== 0) {

                    row.css('background', 'linear-gradient(90deg, #FFFF00, rgba(255, 255, 0, 0))'); // Yellow
                }
            }

        });
        localStorage.setItem('totalOrderQuantity', totalOrderQuantity);
        setupNotePopups(); // Call function to setup note popups after loading items
        $('#orderQuantity').text(totalOrderQuantity);
    }
    // Function to calculate the order age
    function calculateOrderAge(orderCreationDate) {
        var now = new Date(); // Current time
        var differenceMs = now.getTime() - orderCreationDate.getTime();

        // Calculate the difference in hours, minutes, and seconds
        var seconds = Math.floor(differenceMs / 1000);
        var hours = Math.floor(seconds / 3600);
        seconds %= 3600;
        var minutes = Math.floor(seconds / 60);
        seconds %= 60;

        var formattedDifference = '';
        if (productionOrderAgeFormat == 1) { // If "shows in days" is selected
            var days = Math.floor(differenceMs / (1000 * 60 * 60 * 24));
            hours = Math.floor((differenceMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            minutes = Math.floor((differenceMs % (1000 * 60 * 60)) / (1000 * 60));
            seconds = Math.floor((differenceMs % (1000 * 60)) / 1000);
            formattedDifference = String(days).padStart(2, '0') + ':' +
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        } else {
            formattedDifference = String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        }

        return formattedDifference;
    }
    // orderDetails();

    // calculate build quantity, allot quantity
    function calculateQuantity(orders, totalOrderQuantity, totalAllottedQuantity) {

        if (orders.stock_quantity > totalOrderQuantity) {
            var build_quantity = 0;
        } else {
            // var build_quantity = parseFloat(totalOrderQuantity - orders.stock_quantity); 
            var stockQuantity = parseFloat(orders.stock_quantity);
            var remainingOrderQuantity = totalOrderQuantity - totalAllottedQuantity;
            var build_quantity = Math.max(remainingOrderQuantity - stockQuantity, 0);
        }

        if (orders.order_status = 'Locked') {
            var allot_quantity = Math.min(orders.order_quantity, orders.stock_quantity);
        } else if (orders.item_status == 'Completed' || orders.item_status == 'partially_completed') {
            var allot_quantity = orders.allot_quantity;   // For complete order items
        } else {
            var allot_quantity = '';
        }
        return { build_quantity: Math.abs(build_quantity), allot_quantity: allot_quantity };
    }

    // If no order details found, clear and reset quantities
    function resetQuantities(productStock) {
        if (productStock === null) {
            $('#stockQuantity').text('0');
        } else {
            $('#stockQuantity').text(productStock);
        }
        $('#orderQuantity').text('0');
        $('#buildQuantity').text('0');

    }
    // show Note popup
    function setupNotePopups() {
        var infoIcons = document.querySelectorAll('.info-note');

        infoIcons.forEach(function (icon) {
            icon.addEventListener('click', function () {
                var orderId = this.getAttribute('data-id');
                var note = this.getAttribute('data-note');
                var procurementOrderRefNo = this.getAttribute('data-ref');

                // Update modal content with the note data
                var modalTitle = document.querySelector('#exampleModalLongTitle');
                var modalBodyContent = document.querySelector('#modal-body-content');

                modalTitle.innerHTML = `<i class="fa fa-info-circle"></i> Note for Order ID: ${procurementOrderRefNo}`;
                modalBodyContent.innerHTML = `
                    <div class="padding-left-2">
                        <p>Note:</p>
                        <p>${note}</p>
                    </div>
                `;

                $('#note').modal('show');// Show the modal
            });
        });
    }

    //////////////////////////////////////////////////Batches////////////////////////////////////////////////////////////////////////////////////////////

    // View All Batches
    function loadBatches(productBatches) {

        $('#batchtablebody').empty();
        productBatches.forEach(function (batch) {
            let date;
            if(batch.created_at = "0000-00-00 00:00:00"){
                date = batch.updated_at;
            }else{
                date = batch.created_at;
            }
            var row = $(
                "<tr class='text-center'>" +
                "<td>" + batch.batch_no + "</td>" +
                "<td>" + batch.product_id + "</td>" +
                "<td>" + parseFloat(batch.quantity) + "</td>" +
                "<td>" + batch.unit_name + "</td>" +
                "<td>" + date + "</td>" +
                "<td>" + batch.expiry_date + "</td>" +
                "</tr>"
            );
            $("#batchtablebody").append(row);
        });
    }
    // View Latest Batches
    function loadLatestBatches(latestProductBatches) {

        $('#latestbatchestablebody').empty();
        latestProductBatches.forEach(function (batch) {
            let date;
            if(batch.created_at = "0000-00-00 00:00:00"){
                date = batch.updated_at;
            }else{
                date = batch.created_at;
            }
            var row = $(
                "<tr class='text-center'>" +
                "<td>" + batch.batch_no + "</td>" +
                "<td>" + batch.product_id + "</td>" +
                "<td>" + parseFloat(batch.quantity) + "</td>" +
                "<td>" + batch.unit_name + "</td>" +
                "<td>" + batch.created_at + "</td>" +
                "<td>" + batch.expiry_date + "</td>" +
                "</tr>"
            );
            $("#latestbatchestablebody").append(row);
        });
    }

    function loadLatestBatchesProduction(latestProductBatches) {

        $('#latestbatchestablebodyProduction').empty();
        latestProductBatches.forEach(function (batch) {
            let date;
            if(batch.created_at = "0000-00-00 00:00:00"){
                date = batch.updated_at;
            }else{
                date = batch.created_at;
            }
            var row = $(
                "<tr class='text-center'>" +
                "<td>" + batch.batch_no + "</td>" +
                "<td>" + batch.product_id + "</td>" +
                "<td>" + parseFloat(batch.quantity) + "</td>" +
                "<td>" + batch.unit_name + "</td>" +
                "<td>" + date + "</td>" +
                "<td>" + batch.expiry_date + "</td>" +
                "</tr>"
            );
            $("#latestbatchestablebodyProduction").append(row);
        });
    }



    // show batches table
    $('#showTable').on('click', function () {
        var productId = localStorage.getItem('productId', productId);
        var div = document.getElementById('mytable');
        div.style.display = 'block';
        var productBatches = JSON.parse(localStorage.getItem('productBatches'));
        var latestProductBatches = JSON.parse(localStorage.getItem('latestProductBatches'));

        if (latestProductBatches) {
            latestProductBatches.forEach(function (batch) {
                if (batch.product_id === productId) {
                    loadLatestBatches(latestProductBatches);  // view latest batches
                } else {
                    loadLatestBatches(); // view latest batches
                }
            });
        }
        if (productBatches) {
            productBatches.forEach(function (batch) {
                if (batch.product_id === productId) {
                    loadBatches(productBatches);  // view all batches
                } else {
                    loadBatches();  // view all batches
                }
            });
        }
    });

    $(document).on('click', '.showTable', async function () {
        const element = $(this);
        let productId = element[0]["attributes"]['product_id'].value;
        localStorage.setItem("productId", JSON.stringify(productId));
        var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time
        await orderDetails(productId, manufacturingDate);
        $('#recentBatchModal').modal('show');
        var productBatches = JSON.parse(localStorage.getItem('productBatches'));
        var latestProductBatches = JSON.parse(localStorage.getItem('latestProductBatches'));
        if (latestProductBatches) {
            latestProductBatches.forEach(function (batch) {
                if (batch.product_id === productId) {
                    loadLatestBatchesProduction(latestProductBatches);  // view latest batches
                } else {
                    loadLatestBatchesProduction(); // view latest batches
                }
            });
        }

        if (productBatches) {
            productBatches.forEach(function (batch) {
                if (batch.product_id === productId) {
                    loadBatches(productBatches);  // view all batches
                } else {
                    loadBatches();  // view all batches
                }
            });
        }
    });
    // Hide batches table
    $('#hideTable').on('click', function () {
        var div = document.getElementById('mytable');
        div.style.display = 'none';
        localStorage.removeItem('productBatches');
        localStorage.removeItem('latestProductBatches');
        localStorage.removeItem('productId');
    });

    // Insert Batches
    // $('#addBatch').click(function() {

    //     var batchQuantity = $('#allotbatchqty').val();
    //     var productId     =  localStorage.getItem('productId', productId);
    //     var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time

    //     if (batchQuantity === '') {
    //         alert('Please enter batch quantity.');
    //         return; 
    //     }

    //     $.ajax({
    //         url: site.base_url + "Production_Unit/addProductWiseBatches",
    //         method:'GET',
    //         data: {
    //             batchQuantity: batchQuantity,
    //             productId: productId,
    //             manufacturingDate: manufacturingDate
    //         },
    //         dataType:'json',
    //         success: function (response) {
    //             alert('Batch added successfully.');

    //             var productBatches = response.productBatches;
    //             var latestProductBatches = response.latestProductBatches;
    //             var productStock = response.productStock;
    //             var stockQuantity   = parseFloat(productStock.stock_quantity);

    //             $('#stockQuantity').text(stockQuantity); // show updated stock if stock is reset
    //             loadLatestBatches(latestProductBatches);
    //             loadBatches(productBatches);
    //             // $('#batchModal').modal('hide');
    //             location.reload(); // Reload the page after add batch

    //         },
    //         error: function(xhr, status, error) {
    //             console.error("AJAX error:", status, error);
    //         }
    //     });
    // });

    // Add Batch button click handler
    $('#addBatch').click(function () {

        const path = "production_dashboard";
        const URL_PATH = window.location.pathname;
        let arr = URL_PATH.split("/");
        if (path === arr[arr.length - 1]) {
            var batchQuantity = $('#allotbatchqty').val();
            var productId = localStorage.getItem('productId');
            var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time

            if (batchQuantity === '') {
                alert('Please enter batch quantity.');
                return;
            }

            $.ajax({
                url: site.base_url + "Production_Unit/addProductWiseBatchesinProductionDashboard",
                method: 'GET',
                data: {
                    batchQuantity: batchQuantity,
                    productId: productId,
                    manufacturingDate: manufacturingDate
                },
                dataType: 'json',
                success: function (response) {
                    alert('Batch added successfully.');

                    var productBatches = response.productBatches;
                    var latestProductBatches = response.latestProductBatches;
                    var productStock = response.productStock;
                    var stockQuantity = parseFloat(productStock.stock_quantity);

                    $(`#${Number(productId)}`).text(stockQuantity); // Show updated stock if stock is reset
                    loadLatestBatches(latestProductBatches);
                    loadBatches(productBatches);
                    // Refresh the page to maintain state
                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", status, error);
                }
            });
            // localStorage.removeItem('productId');
            return;
        }

        var batchQuantity = $('#allotbatchqty').val();
        var productId = localStorage.getItem('productId');
        var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time

        if (batchQuantity === '') {
            alert('Please enter batch quantity.');
            return;
        }

        $.ajax({
            url: site.base_url + "Production_Unit/addProductWiseBatches",
            method: 'GET',
            data: {
                batchQuantity: batchQuantity,
                productId: productId,
                manufacturingDate: manufacturingDate
            },
            dataType: 'json',
            success: function (response) {
                alert('Batch added successfully.');

                var productBatches = response.productBatches;
                var latestProductBatches = response.latestProductBatches;
                var productStock = response.productStock;
                var stockQuantity = parseFloat(productStock.stock_quantity);

                $('#stockQuantity').text(stockQuantity); // Show updated stock if stock is reset
                loadLatestBatches(latestProductBatches);
                loadBatches(productBatches);
                // Refresh the page to maintain state
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    });



    // Function to get current date and time in 'YYYY-MM-DD HH:MM:SS' format
    function getCurrentDateTime() {

        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        return `${year} - ${month} - ${day} ${hours}: ${minutes}: ${seconds}`;
    }

    $('#addBatchModal').on('click', function () {

        var productId = localStorage.getItem('productId', productId);
        $('#productsId').text(productId);

        var currentDateTime = getCurrentDateTime(); // Get the formatted current date and time
        $('#manufacturingDate').text(currentDateTime);

        orderDetails(productId);
        $('#batchModal').modal('show');
    });

    //////////////////////////////////////////////////current time in Header////////////////////////////////////////////////////////////////////////////////////////////

    // Function to update current time
    function updateTime() {
        var currentTimeElement = document.getElementById('currentTime1');
        var currentTime = new Date();

        var hours = currentTime.getHours();
        var minutes = currentTime.getMinutes();
        var seconds = currentTime.getSeconds();

        // Formatting time to ensure two digits
        var formattedHours = hours < 10 ? '0' + hours : hours;
        var formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        var formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

        // Update the #currentTime1 element with the current time
        // currentTimeElement.textContent = formattedHours + ':' + formattedMinutes + ':' + formattedSeconds;
    }

    // Update time every second
    setInterval(updateTime, 1000);
    updateTime();

    //////////////////////////////////////////////////Update stock and allot algorithm////////////////////////////////////////////////////////////////////////////////////////////

    $('#confirmResetStock').click(function () {
        var productId = localStorage.getItem('productId', productId);
        updateProductionDashboardData(productId);
        $('#reset').modal('hide');
        location.reload(); // Reload the page after click on confirm button
    });

    //update stock
    function updateProductionDashboardData(productId = Null) {

        var selectedProductionUnit = $('#productionUnitName').val();  // Get the selected production unit name
        $.ajax({
            url: 'Production_Unit/manager_dashboard',
            type: 'GET',
            data: {
                productId: productId,
                productionUnitName: selectedProductionUnit
            },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    var products = response;
                    // $.each(response, function(index, products) {
                    localStorage.setItem('products', JSON.stringify(products));
                    productList(products); // Update productList with filtered(location wise) products
                    // });
                } else {
                    localStorage.removeItem('products');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error updating data:', error);
            }
        });
    }

    //////////////////////////////////////////////////Currently viewing orders for kitchen filter////////////////////////////////////////////////////////////////////////////////////////////

    // Currently viewing orders for kitchen filter
    $('.production-unit-select').on('change', function () {
        updateProductionDashboardData(productId = null);
    });

    ////////////////////////////////////////////////// checkbox check uncheck functionality ////////////////////////////////////////////////////////////////////////////////////////////

    // allot quantity for specific order item
    $('#tablebody').on('click', 'input[type="checkbox"]', function () {

        var isChecked = $(this).is(':checked');  // check checkbox select or not
        orderItemId = $(this).val();  // order item id
        allotQuantityInput = $(this).closest('tr').find('input[name="allotQuantity[]"]').val(); //allot quantity
        var productId = localStorage.getItem('productId', productId);

        if (isChecked === true) {
            localStorage.setItem('checkbox_' + orderItemId, isChecked);
            localStorage.setItem('allotQuantity_' + orderItemId, allotQuantityInput); // store allot quantity
            var isChecked = "Checked"; // set flag for click on checkbox
            updateOrderItem(orderItemId, isChecked, allotQuantityInput, productId);

        } else {
            localStorage.removeItem('checkbox_' + orderItemId);
            localStorage.removeItem('allotQuantity_' + orderItemId); // remove allot quantity
            var isChecked = "Unchecked"; // set flag for click on checkbox
            updateOrderItem(orderItemId, isChecked, allotQuantityInput, productId);
        }

    });
    // update stock qty and status of order item
    // function updateOrderItem(orderItemId, isChecked, allotQuantityInput, productId) {

    //     $.ajax({
    //         url: site.base_url + "Production_Unit/updateDashboardOrderItemDetails",
    //         method: 'GET',
    //         data: {
    //             orderItemId: orderItemId,
    //             isChecked : isChecked,
    //             allotQuantityInput :allotQuantityInput,
    //             productId : productId
    //         },
    //         dataType: 'json',
    //         success: function (response) {
    //             var OrderDetails = response[0]; 
    //             if (OrderDetails.OrderDetails && OrderDetails.OrderDetails.length > 0) {
    //                 loadOrderItems(OrderDetails.OrderDetails);
    //             }
    //             var Data = OrderDetails.OrderDetails;
    //             var totalOrderQuantity = localStorage.getItem('totalOrderQuantity'); // total order qty against products
    //             var CommittedOrderItem = false;  //flag for check committed order item

    //             Data.forEach(function (orders) {
    //                 var stockQuantity   = parseFloat(orders.stock_quantity);
    //                 $('#stockQuantity').text(stockQuantity); // show updated stock if stock is reset 

    //                 if (orders.item_status === 'Committed') {
    //                     CommittedOrderItem = true;
    //                     AllotQuantity = parseFloat(orders.allot_quantity);
    //                 }

    //             });

    //             // Only show the build quantity if there's at least one committed order
    //             if (CommittedOrderItem) {
    //                 var buildQuantity = totalOrderQuantity - AllotQuantity;
    //                 $('#buildQuantity').text(buildQuantity);
    //             } 
    //         },
    //         error: function (xhr, status, error) {
    //             console.error("AJAX error:", status, error);
    //         }
    //     });
    // }
    function updateOrderItem(orderItemId, isChecked, allotQuantityInput, productId) {

        $.ajax({
            url: site.base_url + "Production_Unit/updateDashboardOrderItemDetails",
            method: 'GET',
            data: {
                orderItemId: orderItemId,
                isChecked: isChecked,
                allotQuantityInput: allotQuantityInput,
                productId: productId
            },
            dataType: 'json',
            success: function (response) {
                var OrderDetails = response[0];
                if (OrderDetails.OrderDetails && OrderDetails.OrderDetails.length > 0) {
                    loadOrderItems(OrderDetails.OrderDetails);
                }
                var Data = OrderDetails.OrderDetails;
                var totalOrderQuantity = parseFloat(localStorage.getItem('totalOrderQuantity')) || 0; // total order qty against products
                var stockQuantity = 0;
                var totalAllottedQuantity = 0; // Total quantity allotted to all orders
                var buildQuantity = 0;
                var committedOrderFound = false;

                Data.forEach(function (orders) {
                    stockQuantity = parseFloat(orders.stock_quantity);
                    $('#stockQuantity').text(stockQuantity); // Show updated stock

                    if (orders.item_status === 'Committed') {
                        committedOrderFound = true;
                        var allotQuantity = parseFloat(orders.allot_quantity);
                        totalAllottedQuantity += allotQuantity;
                    }
                });

                // Calculate build quantity if there are committed orders
                if (committedOrderFound) {

                    // // buildQuantity = stockQuantity - (totalOrderQuantity - totalAllottedQuantity);
                    // buildQuantity = (totalOrderQuantity - totalAllottedQuantity) - stockQuantity; //akshu

                    // $('#buildQuantity').text(buildQuantity);
                    if (stockQuantity > totalOrderQuantity) {

                        var remainingOrderQuantity = totalOrderQuantity - totalAllottedQuantity;
                        var remainingStock = stockQuantity - totalAllottedQuantity;
                        buildQuantity = Math.max(remainingOrderQuantity - remainingStock, 0);
                        $('#buildQuantity').text(buildQuantity);
                    } else {
                        // var stockQuantity1 = stockQuantity + totalAllottedQuantity;

                        // buildQuantity = (totalOrderQuantity - totalAllottedQuantity) - stockQuantity;
                        buildQuantity = (totalOrderQuantity - stockQuantity);

                        // $('#buildQuantity').text(buildQuantity);

                    }
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }

})
