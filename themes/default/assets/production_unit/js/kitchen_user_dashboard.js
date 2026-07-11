(function sanitizeHash() {
    // Run immediately to avoid Bootstrap/JQ selector errors on bare "#"
    if (window.location && window.location.hash === '#') {
        if (history.replaceState) {
            history.replaceState(null, document.title, window.location.pathname + window.location.search);
        } else {
            window.location.hash = '';
        }
    }
})();

$(document).ready(function () {

    // Cache frequently used selectors to avoid repeated DOM queries
    var $menu = $('.mainmenu');
    var $search = $('.search-box');

    //////////////////////////////////////////////////Left Menu////////////////////////////////////////////////////////////////////////////////////////////
    // search products (debounced to reduce DOM work while typing)
    var searchTimer;
    $search.on('keyup', function () {
        clearTimeout(searchTimer);
        var searchText = $(this).val().toLowerCase();
        searchTimer = setTimeout(function () {
            $menu.find('li').each(function () {
                var productText = $(this).text().toLowerCase();
                var isVisible = productText.indexOf(searchText) > -1;
                $(this).toggle(isVisible);
            });

            // If search hides all products, clear dashboard data
            if ($menu.find('li:visible').length === 0) {
                clearDashboardState();
            }
        }, 200);
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
    //     setTimeout(function() {
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

    // Clear dashboard numbers / selections
    function clearDashboardState() {
        $('#tablebody').empty();
        $('#batchtablebody').empty();
        $('#latestbatchestablebody').empty();
        $('#productName').text('');
        $('#unit').text('');
        $('#price').text('');
        $('#expiryDate').text('');
        resetQuantities(null);
        localStorage.removeItem('productId');
    }

    // Function to display the product list
    // Note: logic unchanged; renders menu and restores previously selected product from localStorage.
    function productList(products) {
        // reset state and search filter so newly loaded products are visible
        clearDashboardState();
        $search.val('');
        $menu.empty();
        localStorage.removeItem('productId');

        // normalize products (handle object maps)
        var normalized = Array.isArray(products) ? products : Object.values(products || {});

        // If no products, clear UI and exit
        if (!normalized || normalized.length === 0) {
            return;
        }

        $.each(normalized, function (index, product) {
            var activeClass = index === 0 ? ' active' : ''; // first item active
            // Respect qty_decimals setting for left-menu display (Order/Stock)
            // var orderQtyText = quantityDecimal(product.order_quantity || 0);
            var stockQtyText = quantityDecimal(product.stock_quantity || 0);
            // var productText = product.name + ' (' + orderQtyText + '/' + stockQtyText + ')';
            var productText = product.name + ' (' + stockQtyText + ')';
            var productItem = '<li class="btn-sty product' + activeClass + '" data-product-id="' + product.id + '">' + productText + '</li>';
            $menu.append(productItem);
        });

        // Auto-trigger the first product to load details
        setTimeout(function () {
            var $first = $('.mainmenu .product:first');
            if ($first.length) {
                localStorage.setItem('productId', $first.data('product-id'));
                $first.trigger('click');
            }
        }, 100);
    }
    // Expose for external calls (workstation filter)
    window.kitchenProductList = productList;

    // Call productList function to initialize when the document is ready
    productList(products);
    function clearIngredientStorage() {
        localStorage.removeItem('selectedIngredientIds');
        localStorage.removeItem('selectedBatchIds');
        localStorage.removeItem('allIngredients');
        localStorage.removeItem('Ingredients_details');
        console.log('Cleared: selectedIngredientIds, allIngredients, Ingredients_details');
    }
    
    // clear on page load
    clearIngredientStorage();
    
    // clear on .btn-sty click
    $(document).on('click', '.btn-sty', function () {
        clearIngredientStorage();
    });
    
    // Click event for product items
    // Delegated click for product items (menu is rebuilt)
    $menu.on('click', '.product', function () {
        $('.product').removeClass('active');
        $(this).addClass('active');
        var productId = $(this).data('product-id');
        localStorage.setItem('productId', productId); // Store selected product ID
        var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time
        orderDetails(productId, manufacturingDate);
    });




    //////////////////////////////////////////////////show product wise orders in grid////////////////////////////////////////////////////////////////////////////////////////////

    function orderDetails(productId, manufacturingDate = null) {
        $.ajax({
            url: site.base_url + "Production_Unit/getProductWiseAllDetailsForKitchenUser",
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
                    var Ingredients = OrderDetails.Ingredients;

                    if (OrderDetails.OrderDetails && OrderDetails.OrderDetails.length > 0) {
                        loadOrderItems(OrderDetails.OrderDetails);
                    } else {
                        var stockQty = (productStock && productStock.stock_quantity) ? productStock.stock_quantity : 0;
                        resetQuantities(stockQty);  // If no order details found, clear and reset quantities
                        $('#tablebody').empty();
                    }

                    createBatchNymber(lastBatch, locationCode);  // create new batch number against product
                    if (productDetails) {
                        $('#unit').text(productDetails.unit_name);
                        $('#price').text(productDetails.price);
                        $('#productName').text(productDetails.name);
                        $('#ingredientsModalLabel').text('Ingredients : ' + productDetails.name);
                        $('#rmConsumptionModalLabel').text('RM Consumption: ' + productDetails.name);
                    } else {
                        $('#unit').text('');
                        $('#price').text('');
                        $('#productName').text('');
                        $('#ingredientsModalLabel').text('Ingredients');
                        $('#rmConsumptionModalLabel').text('RM Consumption');
                    }
                    if (Ingredients) {
                        localStorage.setItem('Ingredients_details', JSON.stringify(Ingredients));
                    } else {
                        localStorage.removeItem('Ingredients_details');
                        $('#ingredientsTableBody').empty();
                    }
                    if (productBatches) {
                        productBatches.forEach(function (batch) {
                            $('#expiryDate').text(batch.expiry_date);
                        });
                    } else {
                        $('#expiryDate').text(productDetails && productDetails.expiryDate ? productDetails.expiryDate : '');
                    }
                    if (productBatches && latestProductBatches) {
                        localStorage.setItem('productBatches', JSON.stringify(productBatches));
                        localStorage.setItem('latestProductBatches', JSON.stringify(latestProductBatches));
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

    // Quantity decimal formatter using core.js helpers & global settings
    function quantityDecimal(x) {
        // Use core formatNumber with qty_decimals so it respects Settings->qty_decimals
        if (typeof formatNumber === 'function' && site && site.settings) {
            return formatNumber(x, site.settings.qty_decimals);
        }
        var n = parseFloat(x);
        if (isNaN(n)) return '0';
        return n.toFixed(1); // sensible fallback
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
            // Set headline KPIs with consistent quantity decimal formatting
            $('#stockQuantity').text(quantityDecimal(stockQuantity));
            $('#buildQuantity').text(quantityDecimal(buildQuantity));
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

        // After processing all orders, show total order quantity with quantity decimal format
        $('#orderQuantity').text(quantityDecimal(totalOrderQuantity));
        localStorage.setItem('totalOrderQuantity', totalOrderQuantity);
        setupNotePopups(); // Call function to setup note popups after loading items
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
    function resetQuantities(stockQuantity) {
        if (stockQuantity === null) {
            $('#stockQuantity').text('0');
        } else {
            $('#stockQuantity').text(quantityDecimal(stockQuantity));
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
            $("#batchtablebody").append(row);
        });
    }
    // View Latest Batches
    function loadLatestBatches(latestProductBatches) {

        $('#latestbatchestablebody').empty();
        latestProductBatches.forEach(function (batch) {
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
    // Hide batches table
    $('#hideTable').on('click', function () {
        var div = document.getElementById('mytable');
        div.style.display = 'none';
        localStorage.removeItem('productBatches');
        localStorage.removeItem('latestProductBatches');

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
    $('#rmConsumptionSubmit').click(function () {
        var batchQuantity = $('#allotbatchqty').val();
        var productId = localStorage.getItem('productId');
        var manufacturingDate = getCurrentDateTime();
    
        if (batchQuantity === '' || batchQuantity == 0) {
            alert('Please enter batch quantity.');
            return;
        }
    
        const selected = JSON.parse(localStorage.getItem('selectedIngredientIds') || '[]');
        const ingredientsDetails = JSON.parse(localStorage.getItem('Ingredients_details') || '[]');
    
        // Build wastage map first
        let wastageMap = {};
        ingredientsDetails.forEach(ing => {
            const materialId = ing.material_id;
            if (!materialId) return;
            const batchNo = ing.batch_no || '';
            const key = batchNo ? `${materialId}|${batchNo}` : `${materialId}`;
            wastageMap[key] = 0; // default 0 if no input yet
        });

        // Collect wastage values from inputs (if exist)
        var wastageData = [];
        // Build wastage map first
        ingredientsDetails.forEach(ing => {
            const materialId = ing.material_id;
            if (!materialId) return;
            const batchNo = ing.batch_no || '';
            const key = batchNo ? `${materialId}|${batchNo}` : `${materialId}`;
            wastageMap[key] = 0; // default 0 if no input yet
        });

        // Collect wastage values from inputs
        var wastageData = [];

        $('.wastage-input').each(function () {
            const $input = $(this);
            const row = $input.closest('tr');
            const materialId = row.data('material-id');
            const batchNo = row.data('batch-no') || '';
            const key = batchNo ? `${materialId}|${batchNo}` : `${materialId}`;
            const val = $input.val().trim();
            const intVal = val === '' || isNaN(val) ? 0 : parseInt(val, 10);

            wastageData.push({ key, wastage: intVal });
        });

        // 🔁 Update wastageMap with actual input values
        wastageData.forEach(item => {
            if (wastageMap.hasOwnProperty(item.key)) {
                wastageMap[item.key] = item.wastage;
            }
        });

        // Prepare other ingredient lists
        const materialIdCounts = ingredientsDetails.reduce((counts, ing) => {
            counts[ing.material_id] = (counts[ing.material_id] || 0) + 1;
            return counts;
        }, {});
    
        const compulsoryIds = ingredientsDetails
            .filter(ing =>
                ing.is_alternative == 0 &&
                materialIdCounts[ing.material_id] === 1
            )
            .map(ing => {
                const batch = ing.batch_no || '';
                const expiry = ing.expiry_date || '';
                return (batch && expiry)
                    ? `${ing.material_id}|${batch}|${expiry}`
                    : `${ing.material_id}`;
            });
    
        const batchSelections = JSON.parse(localStorage.getItem('selectedBatchIds') || '[]');
        const allIngredients = [...compulsoryIds, ...selected, ...batchSelections];
    
        // Now send everything in one AJAX GET
        $.ajax({
            url: site.base_url + "Production_Unit/KitchenUseraddProductWiseBatches",
            method: 'GET',
            data: {
                batchQuantity: batchQuantity,
                productId: productId,
                manufacturingDate: manufacturingDate,
                ingredients: allIngredients.join(','),
                wastageMap: JSON.stringify(wastageMap) // encoded automatically by jQuery
            },
            dataType: 'json',
            success: function (response) {
                alert('Batch added successfully.');
                var productBatches = response.productBatches;
                var latestProductBatches = response.latestProductBatches;
                var productStock = response.productStock;
    
                $('#stockQuantity').text(quantityDecimal(productStock.stock_quantity));
                loadLatestBatches(latestProductBatches);
                loadBatches(productBatches);
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    });
    $('#addBatch').on('click', function () {
        var productId = localStorage.getItem('productId');
        var batchQty = $('#allotbatchqty').val(); // current batch quantity
        var Ingredients = JSON.parse(localStorage.getItem('Ingredients_details')) || [];
        if (batchQty === '' || batchQty == 0) {
            bootbox.alert('Please enter batch quantity.');
            return;
        }
        $('#rmBatchQuantity').text(batchQty); // display batch quantity

        if (Ingredients && Ingredients.length) {
            Ingredients.forEach(function (rawMaterial) {
                // only show ingredients for current product
                if (rawMaterial.product_id === productId) {
                    // update required_qty based on batch quantity
                    rawMaterial.required_qty = parseFloat(rawMaterial.quantity_required) * parseFloat(batchQty || 1);
                }
            });
            loadRMConsumptionTable(Ingredients); // populate modal table
        }

        $('#rmConsumptionModal').modal('show');
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

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    $('#addBatchModal').on('click', function () {

        var productId = localStorage.getItem('productId');
        $('#productsId').text(productId);
        $('#allotbatchqty').val(0);
    
        var currentDateTime = getCurrentDateTime();
        $('#manufacturingDate').text(currentDateTime);
    
        orderDetails(productId);  // ✅ first load product details
    
        setTimeout(function() {   // ✅ give time for AJAX response
            var prodName = $('#productName').text(); 
            $('#batchModalProductName').text('Product: ' + prodName);
        }, 200);
    
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
        currentTimeElement.textContent = formattedHours + ':' + formattedMinutes + ':' + formattedSeconds;
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
                    $('#stockQuantity').text(quantityDecimal(stockQuantity)); // Show updated stock

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
                        $('#buildQuantity').text(quantityDecimal(buildQuantity));
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
    /////////////////////////////////// Ingredients //////////////////////////////////////////////////////////////////////

    $('#ingredients').on('click', function () {

        var productId = localStorage.getItem('productId', productId);
        orderDetails(productId);

        var div = document.getElementById('ingredientsModal');
        div.style.display = 'block';
        var Ingredients = JSON.parse(localStorage.getItem('Ingredients_details'));

        var buildQty = $('#buildQuantity').text();
        // $('#builds_Quantity').val(buildQty);
        $('#builds_Quantity').val(1);


        if (Ingredients) {
            console.log(Ingredients);
            Ingredients.forEach(function (rawMaterial) {
            console.log(rawMaterial.sales_units);
                const minBatchQty = parseFloat(rawMaterial.min_batch_qty);
                const salesUnits  = parseFloat(rawMaterial.sales_units);
                const requiredQty = parseFloat(rawMaterial.quantity_required) * buildQty;
                const [numberPart, unitPart] = (rawMaterial.sales_units).split(' ');
                // calculate selling_unit
                const sellingUnit = (minBatchQty * salesUnits) / buildQty;
                $('#Min_batch_quantity').text(
                    (parseFloat(rawMaterial.min_batch_qty) || 0).toFixed(2) +
                    (rawMaterial.batch_unit_name ? ' ' + rawMaterial.batch_unit_name : '')
                );

                //  const displayValue = isNaN(sellingUnit) ? 0 : sellingUnit.toFixed(2);
                // $('#selling_unit').text(`${displayValue} ${unitPart || ''}`);

                $('#selling_unit').text(rawMaterial.sales_units || '');

                // If product matches, load with updated required qty
                if (rawMaterial.product_id === productId) {
                    rawMaterial.required_qty = parseFloat(rawMaterial.quantity_required) * parseFloat(buildQty || 0);
                    loadIngredients(Ingredients);
                } else {
                    loadIngredients();
                }
            });
        }

    });

    // Handle the modal shown and hidden events to style the input field
    $('#ingredientsModal').on('shown.bs.modal', function () {
        $('#builds_Quantity').css({
            'font-weight': 'bold',
            'color': 'black'
        }).focus(); // focus on the input field
    });

    $('#ingredientsModal').on('hidden.bs.modal', function () {
        $('#builds_Quantity').css({
            'font-weight': 'normal',
            'color': ''
        });
    });
    
    $(document).on('change', '#rmConsumptionModal .rm-check', function () {
        const materialId = $(this).data('material-id');
        const altGroup = $(this).data('alt-group') || materialId;
        let selected = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];

        if (this.checked) {
            // Enforce single selection within the alternative group: disable all other alternatives in this alt group
            $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']").not(this).prop('checked', false).prop('disabled', true);
            // Disable all batch checkboxes belonging to other materials in the same alt group
            $("#rmConsumptionModal .batch-check[data-alt-group='" + altGroup + "'][data-material-id!='" + materialId + "']").prop('checked', false).prop('disabled', true);
            // Disable all base batch options for this alt group (those without data-alt-group)
            $("#rmConsumptionModal .batch-check[data-material-id='" + altGroup + "']:not([data-alt-group])").prop('checked', false).prop('disabled', true);
            if (!selected.includes(String(materialId))) selected.push(String(materialId));
        } else {
            // Re-enable other alternatives in the same group on deselect
            $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']").prop('disabled', false);
            // Re-enable batch checkboxes belonging to other materials in the same alt group
            $("#rmConsumptionModal .batch-check[data-alt-group='" + altGroup + "'][data-material-id!='" + materialId + "']").prop('disabled', false);
            // Re-enable base batch options when alt unchecked
            $("#rmConsumptionModal .batch-check[data-material-id='" + altGroup + "']:not([data-alt-group])").prop('disabled', false);
            selected = selected.filter(id => id !== String(materialId));
        }

        localStorage.setItem('selectedIngredientIds', JSON.stringify(selected));
    });

    $(document).on('change', '#rmConsumptionModal .batch-check', function () {
        const batchKey = $(this).val();
        const materialId = $(this).data('material-id');
        const altGroup = $(this).data('alt-group') || materialId;
        let selectedBatchIds = JSON.parse(localStorage.getItem('selectedBatchIds')) || [];

        // Allow multiple batch choices for the same material; only toggle storage
        if (this.checked) {
            if (!selectedBatchIds.includes(batchKey)) selectedBatchIds.push(batchKey);
        } else {
            selectedBatchIds = selectedBatchIds.filter(x => x !== batchKey);
        }

        localStorage.setItem('selectedBatchIds', JSON.stringify(selectedBatchIds));

        // If this batch has data-alt-group, it's part of an alternative group; enforce mutual exclusion across different materials in the same alt group
        if ($(this).data('alt-group')) {
            // Determine if any batch in this alt group is now checked
            const anyAltChecked = $("#rmConsumptionModal .batch-check[data-alt-group='" + altGroup + "']:checked").length > 0;
            if (anyAltChecked) {
                // Disable all other alternatives in the same alt group (different materials)
                $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']").prop('checked', false).prop('disabled', true);
                // Disable batch checkboxes of other materials in the same alt group
                $("#rmConsumptionModal .batch-check[data-alt-group='" + altGroup + "'][data-material-id!='" + materialId + "']").prop('checked', false).prop('disabled', true);
                // Disable base material batches for this alt group (those without data-alt-group)
                $("#rmConsumptionModal .batch-check[data-material-id='" + altGroup + "']:not([data-alt-group])").prop('checked', false).prop('disabled', true);
                // Update selectedIngredientIds to reflect that this alt group is selected
                let selected = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];
                // Ensure the current material is in selected list
                if (!selected.includes(String(materialId))) selected.push(String(materialId));
                // Remove other materials in the same alt group from selected list
                const otherMaterialIds = $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']").map(function(){ return String($(this).data('material-id')); }).get();
                selected = selected.filter(id => !otherMaterialIds.includes(id));
                localStorage.setItem('selectedIngredientIds', JSON.stringify(selected));
            } else {
                // No batch checked in this alt group: re-enable alternatives and base batches
                $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']").prop('disabled', false);
                $("#rmConsumptionModal .batch-check[data-alt-group='" + altGroup + "'][data-material-id!='" + materialId + "']").prop('disabled', false);
                $("#rmConsumptionModal .batch-check[data-material-id='" + altGroup + "']:not([data-alt-group])").prop('disabled', false);
                // Remove this material from selectedIngredientIds if no batch is checked
                let selected = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];
                selected = selected.filter(id => id !== String(materialId));
                localStorage.setItem('selectedIngredientIds', JSON.stringify(selected));
            }
        } else {
            // Base material batch logic: enable/disable the alternative checkbox based on any base selection
            const anyBaseChecked = $("#rmConsumptionModal .batch-check[data-material-id='" + materialId + "']:checked").length > 0;
            if (anyBaseChecked) {
                $("#rmConsumptionModal .rm-check[data-alt-group='" + materialId + "']").prop('checked', false).prop('disabled', true);
                // Also remove from selectedIngredientIds store
                let selected = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];
                selected = selected.filter(id => id !== String(materialId));
                localStorage.setItem('selectedIngredientIds', JSON.stringify(selected));
            } else {
                $("#rmConsumptionModal .rm-check[data-alt-group='" + materialId + "']").prop('disabled', false);
            }
        }
    });
    
    let Ingredients = []; // declare Ingredients globally

    // this function is for kitchen user dashboard js exclusively
    // function loadIngredientsTable() {
    //     $('#ingredientsTableBody').empty();
    
    //     const selectedIds = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];
    //     const selectedBatchIds = JSON.parse(localStorage.getItem('selectedBatchIds')) || []; // new
    //     const buildQty = parseFloat($('#builds_Quantity').val()) || 1;
    //     const rawMaterialIds = Ingredients.map(ing => ing.material_id);
    //     // Count occurrences of each material_id
    //     const materialIdCounts = rawMaterialIds.reduce((counts, id) => {
    //         counts[id] = (counts[id] || 0) + 1;
    //         return counts;
    //     }, {});

    //     Ingredients.forEach(function (rawMaterial) {
    //         // update required_qty based on buildQty
    //         rawMaterial.required_qty = parseFloat(rawMaterial.quantity_required) * buildQty;
    //         const batchKey = rawMaterial.material_id + '|' + (rawMaterial.batch_no || '') + '|' + (rawMaterial.expiry_date || '');
    //         const isBatchChecked = selectedBatchIds.includes(batchKey) ? 'checked' : '';
    //         const availableQty = parseInt(rawMaterial.available_qty);
    //         const requiredQty = parseInt(rawMaterial.required_qty);
    
    //         const displayValue = availableQty + " " + rawMaterial.unit_name;
    //         const availableQtyClass =
    //             (availableQty < requiredQty || availableQty === 0)
    //                 ? "<span class='qty-badge'>" + displayValue + "</span>"
    //                 : displayValue;
    
    //         let checkboxTd = "<td></td>";
    //         if (rawMaterial.is_alternative == 1 && materialIdCounts[rawMaterial.material_id] === 1) {
    //             const isChecked = selectedIds.includes(rawMaterial.material_id.toString()) ? "checked" : "";
    //             checkboxTd =
    //                 "<td><input type='checkbox' class='rm-check large-checkbox' value='" +
    //                 rawMaterial.material_id +
    //                 "' " + isChecked + "></td>";
    //         }
    
    //         const row = $(
    //             "<tr class='text-center'>" +
    //                 "<td style='border-right: 1px solid #ddd;'>" + `${idx + 1}` + "</td>" +
    //                 "<td style='border-right: 1px solid #ddd;'>" + rawMaterial.raw_material + "</td>" +
    //                 "<td>" + rawMaterial.required_qty + " " + rawMaterial.unit_name + "</td>" +
    //                 "<td style='display:flex; align-items:center; justify-content:center; gap:5px;'>" +
    //                     // "<input type='checkbox' class='batch-check large-checkbox' value='" + batchKey + "' " + isBatchChecked + ">" +
    //                     availableQtyClass +
    //                 "</td>" +
    //                 "<td>" + (rawMaterial.batch_no ? rawMaterial.batch_no : '') + "</td>" +
    //                 "<td>" + (rawMaterial.expiry_date ? rawMaterial.expiry_date : '') + "</td>" +
    //                 // checkboxTd 
    //                 +
    //             "</tr>"
    //         );
    
    //         $("#ingredientsTableBody").append(row);
    //     });
    // }

    // we use this function from manager dashboard js 
    function loadIngredientsTable() {
        $('#ingredientsTableBody').empty();

        const selectedBatchIds = JSON.parse(localStorage.getItem('selectedBatchIds')) || [];
        const selectedIds = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];
        const buildQty = parseFloat($('#builds_Quantity').val()) || 1;
        
        // Build alternative index per primary_product_id
        const altIndexMap = {};
        Ingredients.forEach(item => {
            if (item.is_alternative == 1 && item.primary_product_id) {
                const key = item.primary_product_id;
                if (!altIndexMap[key]) altIndexMap[key] = [];
                if(!altIndexMap[key].includes(item.material_id)) altIndexMap[key].push(item.material_id);
            }
        });
        
        // Precompute required qty and alt group id for each item
        const items = Ingredients.map(it => {
            const clone = { ...it };
            clone.required_qty = parseFloat(clone.quantity_required) * buildQty;
            clone.__altGroup = clone.primary_product_id || clone.alternative_for || clone.alt_group || clone.material_id;
            return clone;
        });

        // Group by alternative group id
        const altGroups = items.reduce((acc, it) => {
            const k = String(it.__altGroup);
            if (!acc[k]) acc[k] = [];
            acc[k].push(it);
            return acc;
        }, {});

        let serial = 1;
        Object.keys(altGroups).forEach(altKey => {
            const groupItems = altGroups[altKey];
            // Group within altGroup by material_id
            const byMaterial = groupItems.reduce((acc, it) => {
                const m = String(it.material_id);
                if (!acc[m]) acc[m] = [];
                acc[m].push(it);
                return acc;
            }, {});
            
            const totalRows = Object.values(byMaterial).reduce((sum, arr) => sum + arr.length, 0);
            let isFirstRowOfAlt = true;
            const sortedPrimAltArr = [];
            Object.keys(byMaterial).forEach(altKey => {
                let arr = byMaterial[altKey];  
                if(arr[0].primary_product_id){
                    sortedPrimAltArr.push(altKey);
                }else{
                    sortedPrimAltArr.unshift(altKey);
                }
            });
            
            // Object.keys(byMaterial).forEach(materialId => {
            sortedPrimAltArr.forEach(materialId => {
                const rows = byMaterial[materialId];
                const matRowspan = rows.length;

                rows.forEach((rawMaterial, idx) => {
                    const batchKey = rawMaterial.material_id + '|' + (rawMaterial.batch_no || '') + '|' + (rawMaterial.expiry_date || '');
                    const isBatchChecked = selectedBatchIds.includes(batchKey) ? 'checked' : '';
                    const availableQty = parseFloat(rawMaterial.available_qty) || 0;
                    const requiredQty = parseFloat(rawMaterial.required_qty) || 0;

                    const displayValue = availableQty + " " + rawMaterial.unit_name;
                    
                    // Build checkbox HTML (if needed) - REMOVED since ingredients popup is display-only
                    let availableCellCheckboxes = "";
                    const altGroup = rawMaterial.__altGroup;
                    // No checkboxes for ingredients popup - display only
    
                    // Qty HTML - simplified since no checkboxes
                    const qtyHtml =
                        (availableQty <= 0 || availableQty < requiredQty)
                            ? "<span class='qty-badge'><span>" +
                              availableQty + "</span> <span>" +
                              rawMaterial.unit_name + "</span></span>"
                            : "<span class='qty-text'>" +
                              displayValue + "</span>";

                    // Row Start
                    let tr =
                        "<tr class='text-left' data-material-id='" + rawMaterial.material_id + "'" +
                        " data-batch-no='" + (rawMaterial.batch_no || '') + "'" +
                        " data-alt-group='" + altGroup + "'>";

                    // Sr. No. (rowspan for entire alt group)
                    if (isFirstRowOfAlt) {
                        const srClass = (totalRows > 1) ? " merge-border" : "";
                        tr += "<td class='" + srClass + "' rowspan='" + totalRows + "' style='border-right: 1px solid #ddd;'>" + serial + "</td>";
                        isFirstRowOfAlt = false;
                    }

                    // Ingredient + Required Qty (merged per material group)
                    if (idx === 0) {
                        const mergeCls = (matRowspan > 1) ? " merge-border" : "";
                        let altBadge = "";

                        if (
                            rawMaterial.is_alternative == 1 &&
                            rawMaterial.primary_product_id != null &&
                            altIndexMap[rawMaterial.primary_product_id]
                        ) {
                            const altList = altIndexMap[rawMaterial.primary_product_id];
                            const index = altList.indexOf(rawMaterial.material_id) + 1;
                            altBadge = "<div class='alt-badge'>Alternative " + index + "</div>";
                        }

                        tr +=
                            "<td class='" + mergeCls + " ingredient_name' rowspan='" + matRowspan + "'>" +
                                altBadge +
                                "<div>" + rawMaterial.raw_material + "</div>" +
                            "</td>";

                        tr +=
                            "<td class='" + mergeCls + " req_qty_td' rowspan='" + matRowspan + "'>" +
                                "<span class='req-badge'><span>" +
                                rawMaterial.required_qty +
                                "</span> <span>" +
                                rawMaterial.unit_name +
                                "</span></span>" +
                            "</td>";
                    }

                    // Available Column - simplified without checkboxes
                    tr +=
                        "<td class='available-qty-td'>" + qtyHtml + "</td>";
    
                    // Batch / Expiry
                    tr += "<td class='batch-qty-td'>" + (rawMaterial.batch_no || '') + "</td>";
                    
                    // Check if expiry date is within 24 hours and add warning icon
                    let expiryCell = (rawMaterial.expiry_date || '');
                    let expiryWarningIcon = '';
                    if (rawMaterial.expiry_date) {
                        const expiryDate = new Date(rawMaterial.expiry_date);
                        const now = new Date();
                        const hoursUntilExpiry = (expiryDate - now) / (1000 * 60 * 60);
                        
                        if (hoursUntilExpiry <= 24 && hoursUntilExpiry > 0) {
                            expiryWarningIcon = ' <i class="fa fa-exclamation-triangle" style="color: #ff9800; margin-left: 5px;" title="Expiring within 24 hours"></i>';
                        }
                    }
                    
                    tr += "<td class='expiry_date'>" + expiryCell + expiryWarningIcon + "</td>";

                    tr += "</tr>";
                    $('#ingredientsTableBody').append($(tr));
                });
            });

            serial += 1;
        });
    }

    function loadRMConsumptionTable(Ingredients) {
        $('#rmConsumptionTableBody').empty();

        const selectedBatchIds = JSON.parse(localStorage.getItem('selectedBatchIds')) || [];
        const selectedIds = JSON.parse(localStorage.getItem('selectedIngredientIds')) || [];
        const batchQty = parseFloat($('#rmBatchQuantity').text()) || 1;

        // Precompute required qty and alt group id for each item
        const items = Ingredients.map(it => {
            const clone = { ...it };
            clone.required_qty = parseFloat(clone.quantity_required) * batchQty;
            clone.__altGroup = clone.primary_product_id || clone.alternative_for || clone.alt_group || clone.material_id;
            return clone;
        });

        // Group by alternative group id
        const altGroups = items.reduce((acc, it) => {
            const k = String(it.__altGroup);
            if (!acc[k]) acc[k] = [];
            acc[k].push(it);
            return acc;
        }, {});

        let serial = 1;
        Object.keys(altGroups).forEach(altKey => {
            const groupItems = altGroups[altKey];
            // within alt group, further group by material_id to merge ingredient/required cells
            const byMaterial = groupItems.reduce((acc, it) => {
                const m = String(it.material_id);
                if (!acc[m]) acc[m] = [];
                acc[m].push(it);
                return acc;
            }, {});

            // total rows in this alt group = sum of batch rows for each material
            const totalRows = Object.values(byMaterial).reduce((sum, arr) => sum + arr.length, 0);

            let remainingForSr = totalRows; // to use for rowspan once
            let isFirstRowOfAlt = true;

            Object.keys(byMaterial).forEach(materialId => {
                const rows = byMaterial[materialId];
                const matRowspan = rows.length;

                rows.forEach((rawMaterial, idx) => {
                    const batchKey = rawMaterial.material_id + '|' + (rawMaterial.batch_no || '') + '|' + (rawMaterial.expiry_date || '');
                    const isBatchChecked = selectedBatchIds.includes(batchKey) ? 'checked' : '';
                    const availableQty = parseFloat(rawMaterial.available_qty) || 0;
                    const requiredQty = parseFloat(rawMaterial.required_qty) || 0;

                    const displayValue = availableQty + " " + rawMaterial.unit_name;
                    const qtyHtml = (availableQty <= 0 || availableQty < requiredQty)
                        ? "<span class='qty-badge'><span>" + availableQty + "</span> <span>" + rawMaterial.unit_name + "</span></span>"
                        : "<span class='qty-text'>" + displayValue + "</span>";

                    // Available column content
                    let availableCellCheckboxes = "";
                    const altGroup = rawMaterial.__altGroup;
                    if (rows.length > 1) {
                        availableCellCheckboxes += "<input type='checkbox' class='batch-check large-checkbox' data-material-id='" + rawMaterial.material_id + "' data-alt-group='" + altGroup + "' value='" + batchKey + "' " + isBatchChecked + ">";
                    }
                    if (rawMaterial.is_alternative == 1 && rows.length === 1) {
                        const isAltChecked = selectedIds.includes(String(rawMaterial.material_id)) ? 'checked' : '';
                        availableCellCheckboxes += "<input type='checkbox' class='rm-check large-checkbox' data-material-id='" + rawMaterial.material_id + "' data-alt-group='" + altGroup + "' " + isAltChecked + ">";
                    }

                    const disabledAttr = (availableQty <= 0) ? " disabled" : "";
                    const wastageInputHtml =
                        "<div class='wastage-container'>" +
                            "<input type='number' class='wastage-input' inputmode='decimal' step='any' min='0' value='0'" + disabledAttr + " onkeydown='event.stopPropagation();' onfocus='this.select();'>" +
                            "<span class='wastage-unit'>" + rawMaterial.unit_name + "</span>" +
                        "</div>";

                    let tr = "<tr class='text-left' data-material-id='" + rawMaterial.material_id + "' data-batch-no='" + (rawMaterial.batch_no || '') + "'>";

                    // Sr. No. merged across the entire alt group
                    if (isFirstRowOfAlt) {
                        const srClass = (totalRows > 1) ? " merge-border" : "";
                        tr += "<td class='" + srClass.trim() + "' rowspan='" + totalRows + "'>" + serial + "</td>";
                        isFirstRowOfAlt = false;
                    }

                    // Ingredient and Required merged per material group
                    if (idx === 0) {
                        const mergeCls = (matRowspan > 1) ? " merge-border" : "";
                        // tr += "<td class='" + mergeCls.trim() + "' rowspan='" + matRowspan + "' class='ingredient_name'>" + rawMaterial.raw_material + "</td>";
                        tr += "<td class='" + mergeCls.trim() + " ingredient_name' rowspan='" + matRowspan + "'>" + rawMaterial.raw_material + "</td>";
                        tr += "<td class='" + mergeCls.trim() + " req_qty_td' rowspan='" + matRowspan + "'><span class='req-badge'><span>" + rawMaterial.required_qty + "</span> <span>" + rawMaterial.unit_name + "</span></span></td>";
                    }

                    // Available column per row (left-aligned): checkbox first, then quantity (reverted as requested)
                    tr += "<td class='available-qty-td'><div class='td-left-flex'>" + availableCellCheckboxes + qtyHtml + "</div></td>";

                    tr += "<td>" + (rawMaterial.batch_no || '') + "</td>";
                    // tr += "<td> class='expiry_date'" + (rawMaterial.expiry_date || '') + "</td>";
                    tr += "<td class='expiry_date'>" + (rawMaterial.expiry_date || '') + "</td>";
                    tr += "<td class='wastage_td'>" + wastageInputHtml + "</td>";

                    tr += "</tr>";
                    $('#rmConsumptionTableBody').append($(tr));
                });
            });

            serial += 1;
        });
    }
    
    // ✅ Ensure typing always works
    $(document).on('focus click keydown', '.wastage-input', function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    
    
    // initial load — pass your Ingredients here
    function loadIngredients(initialIngredients) {
        Ingredients = initialIngredients; // store globally
        loadIngredientsTable();           // render first time
    }
    
    // listen for buildQty change
    $('#builds_Quantity').on('input', function () {
        loadIngredientsTable(); // just reload with updated buildQty
    });
    
    


    ///////////////////////////////////////////////// Ingredients pop build Quantity//////////////////////////////////////
    $('#builds_Quantity').on('input change', function () {  
        $('#ingredientsTableBody').empty();
        var productId = localStorage.getItem('productId', productId);
        orderDetails(productId);
        var div = document.getElementById('ingredientsModal');
        div.style.display = 'block';
        var Ingredients = JSON.parse(localStorage.getItem('Ingredients_details'));
        var buildQty = $('#builds_Quantity').val();
        $('#builds_Quantity').val(buildQty);
        if (Ingredients) {
            Ingredients.forEach(function (rawMaterial) {
                if (rawMaterial.product_id === productId) {
                    rawMaterial.required_qty = parseFloat(rawMaterial.quantity_required) * buildQty;
                    loadIngredients(Ingredients);
                } else {
                    loadIngredients();
                }
            });
        }
    });
    $('#print_raw_materials').on('click', function () {
        // var productId = localStorage.getItem('productId', productId);
        // orderDetails(productId)
        // var Ingredients = JSON.parse(localStorage.getItem('Ingredients_details'));

        // print_raw_materials(Ingredients);
    });
    

});

