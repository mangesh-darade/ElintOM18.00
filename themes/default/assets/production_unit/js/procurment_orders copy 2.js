//////////////////////////////////////////////////////// REQUEST DATE FUNCTION ///////////////////////////////////////////////////
$(function () {
    $("#requestDeliveryDate").datepicker({
        dateFormat: "MM dd, yy", // Set the date format to "Month Day, Year"
        showOn: "focus", // Only show datepicker on input focus
        onSelect: function (dateText, inst) {
            var selectedDate = $(this).datepicker('getDate');
            var currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0); // Set hours to 0 for accurate comparison
            if (selectedDate < currentDate) {
                $(this).datepicker('setDate', currentDate);
                alert("Please select a date that is today or later.");
            }
        }
    });

    $("#requestDeliveryDate").datepicker("setDate", new Date());
    $(".input-group-addon").on('click', function () {
        $("#requestDeliveryDate").focus(); // Focus on the input to show datepicker
    });
});

$(document).ready(function () {

    ///////////////////////////////////////////////////// ADD NOTES //////////////////////////////////////////
    // $('#editor').redactor('destroy');
    $('#editor').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slnote', v);
        }
    });
    if (slnote = localStorage.getItem('slnote')) {
        $('#editor').redactor('set', slnote);
    }

    // // prevent default action usln enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    $('#add_note').click(function () {
        $('#editorRow').toggle();
        var buttonText = $('#editorRow').is(':visible') ? 'Hide Note' : 'Add Note';
        $(this).text(buttonText);
    });
    //////////////////////////////////////////////////////// RESET //////////////////////////////////////////
    localStorage.removeItem('selectedHorizonatlTab');
    $('.categories').show();
    $('#reset').click(function () {
        localStorage.removeItem('selectedHorizonatlTab');
        localStorage.removeItem('currentOrderItems');
        localStorage.removeItem('productId');
        localStorage.removeItem('add_note');


        if (confirm("Are you sure you want to removet the Items")) {
            window.location.reload(true);
        } else {

        }
    });

    /////////////////////////////////////////////////////  GET All DATA ////////////////////////////////////
    function getProcuremntOrdersScreenLoadData(subcategoryId, selectedHorizonatlTabValue, order_id, categoriesId, product_name) {
        $(".repeat_order").hide();
        $(".right_section").show();
        localStorage.setItem('selectedHorizonatlTab', selectedHorizonatlTabValue);

        $.ajax({
            url: site.base_url + "Production_Unit/ProcuremntOrdersScreenLoadData",
            method: 'GET',
            data: {
                product_name: product_name,
                subcategoryId: subcategoryId,
                orderStatus: selectedHorizonatlTabValue,
                order_id: order_id,
                categoriesId: categoriesId,
            },
            dataType: 'json',
            success: function (response) {
                if (response) {


                    if (selectedHorizonatlTabValue) {
                        switch (selectedHorizonatlTabValue) {
                            case 'current_order':
                                $(".update_order").hide();
                                $(".right_section").show();
                                $(".repeat_order").hide();
                                $('#order_status').text(('Current Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    var product_details = orderItem.productsByCat;
                                    loadOrderItems(Items, product_details);
                                });
                                break;
                            case 'update_order':
                                $("#place_order").hide();
                                $('#order_status').text(('Update Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    loadOrderItems(Items);
                                });
                                break;

                            case 'previous_order_received':
                                $(".update_order").hide();
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    loadOrderItems(Items);
                                });
                                break;

                            case 'Open':
                                $(".right_section").hide();
                                $('#order_status').text(('Open Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.getOrderData;
                                    var productdata = orderItem.productsByCat;
                                    loadOrderItems(Items, productdata);
                                });
                                break;
                            case 'partially':
                                $('#order_status').text(('Partial Order'));
                                $(".right_section").hide();
                                $(".update_order").hide();

                                response.forEach(function (orderItem) {
                                    var Items = orderItem.getOrderData;
                                    loadOrderItems(Items);
                                });
                                break;

                            case 'partial_order_item':
                                $(".update_order").hide();
                                $(".right_section").hide();
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    loadOrderItems(Items);
                                });
                                break;

                            case 'previous_order':
                                $(".update_order").hide();
                                $('#order_status').text(('Previous Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.getOrderData;
                                    loadOrderItems(Items);
                                });
                                break;
                            default:
                                console.error('Unknown order type');
                                break;
                        }
                    }
                    response.forEach(function (subCategorie) {
                        var productDetails = subCategorie.productsByCat;
                        getProductbyCat(productDetails);

                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
    getProcuremntOrdersScreenLoadData();

    ////////////////////////////////////////////////////// UPDATE ORDER ////////////////////////////////////////

    $(document).on('click', '.Update-order-link', function (event) {
        $(".right_section").show();
        $(".orderNo").show();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        $('#repeat_order').attr('data-id', orderId);
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);

        if (orderId) {
            var orderStatus = 'previous_order_received';
            getProcuremntOrdersScreenLoadData(null, orderStatus, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });
    $(document).on('click', '.Update_rec-order-link', function (event) {
        $(".orderNo").show();
        $(".place_order").hide();
        $(".right_section").show();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);


        if (orderId) {
            var orderStatus = 'update_order';
            getProcuremntOrdersScreenLoadData(null, orderStatus, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });
    $(document).on('click', '.partial_order_item_addToCurrentOrder', function (event) {
        // Prevent default action (if any)
        event.preventDefault();
        var $row = $(this).closest('tr');
        var rowData = {
            categoryName: $row.find('td:eq(0)').text().trim(),
            product_name: $row.find('td:eq(1)').text().trim(),
            order_quantity: $row.find('td:eq(2)').text().trim(),
            quantity: $row.find('td:eq(2)').text().trim(),
            received_quantity: $row.find('td:eq(3)').text().trim(),
            // Add more fields as needed
            order_id: $(this).data('order-id'), // Assuming data-order-id is set correctly
            unit: $(this).data('unit'),
            subtotal: $(this).data('unit-price'),
            unit_price: $(this).data('unit-price'),
            tax_method: $(this).data('tax_method'),
            tax_rate: $(this).data('tax_rate')
        };

        // Store row data in local storage
        var orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
        orders.push(rowData);
        console.log("darae");
        console.log(orders);
        localStorage.setItem('currentOrderItems', JSON.stringify(orders));

        // Update UI: Replace plus icon with "Added to Current Order" message in green
        $(this).hide(); // Hide the plus icon
        $row.find('.addedText').show(); // Show the added message
    });
    $(document).on('click', '.partial-order-link', function (event) {
        $(".orderNo").show();
        $(".right_section").hide();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);
        if (orderId) {
            var selectedHorizonatlTabValue = 'partial_order_item';
            getProcuremntOrdersScreenLoadData(null, selectedHorizonatlTabValue, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });
    /////////////////////////////////////////////////////// VIEW ORDER ////////////////////////////////////////
    $(document).on('click', '.view-order-link', function (event) {
        $(".right_section").show();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        var clickorder = 'true';
        localStorage.setItem('clickorder', clickorder);
        if (orderId) {
            getProcuremntOrdersScreenLoadData(null, null, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });

    function loadOrdersFromLocalStorage() {
        var orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];

        $('#dynamicTable tbody tr').each(function () {
            var $row = $(this);
            var order_id = $row.find('.partial_order_item_addToCurrentOrder').data('order-id');
            // Check if current row's order_id exists in stored orders
            var foundOrder = orders.find(function (order) {
                return order.order_id === order_id;
            });

            if (foundOrder) {
                // Row data is already in local storage, update UI
                $row.find('.partial_order_item_addToCurrentOrder').hide(); // Hide plus icon
                $row.find('.addedText').show(); // Show added message
            }
        });
    }
    // Call function to load and check stored orders on page load
    loadOrdersFromLocalStorage();
    //////////////////////////////////////////////////////// NAVIGATON ////////////////////////////////////////////////
    $('#clickMe').click(function () {
        var selectedHorizonatlTab = '';
        selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
        if (selectedHorizonatlTab === "partial_order_item") {
            selectedHorizonatlTabselectedHorizonatlTab = "partially";
        }
        if (selectedHorizonatlTab === "update_order") {
            $('#Open').click();
            return;
        }
        if (selectedHorizonatlTab === "previous_order_received") {
            selectedHorizonatlTab = "previous_order";
        }
        $('#' + selectedHorizonatlTab + ' .status').click();
    });

    /////////////////////////////////////////////////////// LEFT SATUS //////////////////////////////////////////////
    $('.status').click(function (event, status) {
        $(".orderNo").hide();
        $('#editorRow').hide();
        var selectedHorizonatlTab = $(this).attr('value');
        localStorage.setItem('selectedHorizonatlTab', selectedHorizonatlTab);
        if (selectedHorizonatlTab === "partially") {
            $(".right_section").hide();
            $('#add_note').hide();
            $(".repeat_order").hide();
        }
        if (selectedHorizonatlTab === "Open") {
            $('#add_note').hide();
        }
        if (status === "open_order") {
            $('#add_note').hide();
            var selectedHorizonatlTab = "Open";

        }
        if (selectedHorizonatlTab === "previous_order") {
            $('#add_note').hide();
            $(".right_section").hide();

        }
        if (selectedHorizonatlTab === "current_order") {
            $('#dynamicTable tbody').empty();
            $('#add_note').show();
            $('#place_order').show();
        }
        getProcuremntOrdersScreenLoadData(null, selectedHorizonatlTab)
    });
    //////////////////////////////////////////////////////// REPEAT ORDER ///////////////////////////////////////
    $('.repeat_order').click(function () {
        var dataIdValue = $(this).attr('data-id');
        var orderStatus = $(this).attr('value');
        $(".right_section").show();
        $("#activeStatus").toggleClass("active");
        $("#previous_order").removeClass("active");
        getProcuremntOrdersScreenLoadData(null, orderStatus, dataIdValue)
    });

    /////////////////////////////////////////////////////// REMOVE RECORD ////////////////////////////////////////////////
    // jQuery code to remove table row when delete button is clicked
    $(document).on('click', '.delete-btn', function () {
        $(this).closest('tr').remove(); // Removes the closest tr from the DOM
        // calculateAndDisplayTotal();
        calculateOrderTotal();
        updateRowCount();
    });

    $(document).on('click', '.plusIcon1', function () {
        $("#addedText1").show();
        $(".plusIcon1").hide();

    });
    ////////////////////////////////////////////////////// UPDATE ROW COUNT //////////////////////////////////////////////////
    function updateRowCount() {
        var rowCount = $('#dynamicTable tbody tr').length;
        $('#rowCount').text(rowCount);
        localStorage.setItem('rowCount', rowCount);
        console.log("Updated row count:", rowCount);
        // You can update a UI element showing the row count, or perform any other action here
    }

    /////////////////////////////////////////////////////// LOAD ITEMS ////////////////////////////////////////////////////

    function loadOrderItems(response, productDetails, order) {

        localStorage.setItem('productDetails', response);
        var selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
        if (selectedHorizonatlTab === null) {
            $(".right_section").show();
        }
        if (order == 'currentorder') {
            $(".right_section").show();
            $('#dynamicTable tfoot').empty();
            var unit_price = parseFloat(productDetails.unit_price).toFixed(2);
            const orderdetails = `
           <tr>
                <th scope="col">Category</th>
                <th scope="col">Product</th>
                <th scope="col">Qty.</th>
                <th scope="col">Unit</th>
                <th scope="col">Net Price</th>
                <th scope="col">Tax</th>
                <th scope="col">Sub Total</th>
                <th scope="col">Delete</th>
            </tr>`;
            $('#dynamicTable thead').html(orderdetails);
            console.log('currentorder');
            console.log(productDetails);
            var current_order =
                '<tr>' +
                '<td><input type="hidden" name="categoryName[]" class="text-center categoryName-input" value="' + productDetails.categoryName + '">' + productDetails.categoryName + '</td>' +
                '<td><input type="hidden" name="productName[]" class="text-center productName-input" value="' + productDetails.name + '">' + productDetails.name + '</td>' +
                '<td><input type="number" name="order_quantity[]" style="width:50%" class="text-end quantity-input" value = "' + productDetails.quantity + '" required></td>' +
                '<td><input type="hidden" name="unitName[]" class="text-center unitName-input" value="' + productDetails.unitName + '">' + productDetails.unitName + '</td>' +
                '<td id="net_price" class="text-end net_price " data-net_price="' + productDetails.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + productDetails.tax_rate + '"  data-tax_rate="' + productDetails.tax_rate + '">' + formatMoney(productDetails.tax_rate) + '</td>' +
                '<td id="subtotal" class="text-end subtotal " data-order-id="' + productDetails.unit_price + '" data-tax_method="' + productDetails.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                '<td><i class="fa fa-trash delete-btn"></i></td>' +
                '</tr>';
            var newRow = $(current_order);
            $('#dynamicTable tbody').append(newRow);
            // var rowCount = localStorage.getItem('rowCount');
            var rowCount = $('#dynamicTable tbody tr').length;
            var tfootRow = '<tr>' + //Item footer
                '<td><strong>Total Items</strong></td>' +
                '<td id = "rowCount">' + rowCount + '</td>' +
                '<td></td>' +
                '<td></td>' +
                '<td id ="net_prices" class="text-end">' + '</td>' +
                '<td id ="tax_rates" class="text-end">' + '</td>' +
                // '<td><strong>Order Total</strong></td>' +
                '<td id ="subTotals" class="text-end">' + '</td>' +
                '<td></td>' +
                '</tr>';

            $('#dynamicTable tfoot').append(tfootRow);
            calculateProductTax(productDetails, newRow);
            calculateOrderTotal();


        } else {
            if (selectedHorizonatlTab == 'update_order') {
                $('#dynamicTable tbody').empty();

                const current_order = `
                <tr>
                    <th scope="col">Category</th>
                    <th scope="col">Product</th>
                    <th scope="col">Qty.</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Net Price</th>
                    <th scope="col">Tax</th>
                    <th scope="col">Sub Total</th>
                    <th scope="col">Delete</th>
                </tr>`;
                $('#dynamicTable thead').html(current_order);
                response.forEach(function (Open_order) {
                    var unit_price = parseFloat(Open_order.unit_price).toFixed(2);
                    console.log('Update_order');
                    console.log(Open_order);
                    $('#editorRow').show();
                    $('#editor').val(Open_order.order_note);
                    var current_order =
                        '<tr>' +
                        '<td><input type="hidden" name="categoryName[]" class="text-center categoryName-input" value="' + Open_order.categoryName + '">' + Open_order.categoryName + '</td>' +
                        '<td><input type="hidden" name="productName[]" class="text-center productName-input" value="' + Open_order.product_name + '">' + Open_order.product_name + '</td>' +
                        '<td><input type="number" name="order_quantity[]" style="width:50%" class="text-end quantity-input" value = "' + Open_order.order_quantity + '" required></td>' +
                        '<td><input type="hidden" name="unitName[]" class="text-center unitName-input" value="' + Open_order.unitName + '">' + Open_order.unitName + '</td>' +
                        '<td id="net_price" class="text-end net_price " data-net_price="' + Open_order.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + Open_order.tax_rate + '"  data-tax_rate="' + Open_order.tax_rate + '">' + formatMoney(Open_order.tax_rate) + '</td>' +
                        // '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '">' + Open_order.subtotal + '</td>' +
                        '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '" data-tax_method="' + Open_order.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(Open_order.subtotal) + '</td>' +
                        '<td><i class="fa fa-trash delete-btn"></i></td>' +
                        '</tr>';
                    $('#update_order').attr('data-id', Open_order.itemsId);
                    var newRow = $(current_order);
                    $('#dynamicTable tbody').append(newRow);
                    calculateProductTax(Open_order, newRow);


                });
                var rowCount = $('#dynamicTable tbody tr').length;
                var tfootRow = '<tr>' + //Item footer
                    '<td><strong>Total Items</strong></td>' +
                    '<td id = "rowCount">' + rowCount + '</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td id ="net_prices" class="text-end">' + '</td>' +
                    '<td id ="tax_rates" class="text-end">' + '</td>' +
                    '<td class = "text-end" id ="subTotals">' + '</td>' +
                    '<td></td>' +
                    '</tr>';

                $('#dynamicTable tfoot').append(tfootRow);

                $(".repeat_order").hide();
                $("#add_note").show();
                $(".update_order").show();
                $("#place_order").hide();
                calculateOrderTotal();

            }
            if (selectedHorizonatlTab == 'current_order') {
                $('#dynamicTable tbody').empty();
                $('#dynamicTable tfoot').empty();
                const current_order = `
                <tr>
                    <th scope="col">Category</th>
                    <th scope="col">Product</th>
                    <th scope="col">Qty.</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Net Price</th>
                    <th scope="col">Tax</th>
                    <th scope="col">Sub Total</th>
                    <th scope="col">Delete</th>
                </tr>`;
                $('#dynamicTable thead').html(current_order);
                var Open_orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
                Open_orders.forEach(function (Open_order) {
                    var unit_price = parseFloat(Open_order.unit_price).toFixed(2);
                    console.log('current_order');
                    console.log(Open_orders);
                    const partial_current_order =   // Item Body
                        '<tr >' +
                        '<td>' + Open_order.categoryName + '</td>' +
                        '<td>' + Open_order.product_name + '</td>' +
                        '<td class=""><input type="number" name = "order_quantity[]" style="width:50%"  class="text-end quantity-input" value="' + Open_order.order_quantity + '"></td>' +
                        '<td>' + Open_order.unit + '</td>' +
                        '<td id="net_price" class="text-end net_price " data-net_price="' + Open_order.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + Open_order.tax_rate + '"  data-tax_rate="' + Open_order.tax_rate + '">' + formatMoney(Open_order.tax_rate) + '</td>' +
                        // '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.subtotal + '">' + Open_order.subtotal + '</td>' +
                        '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.subtotal + '" data-tax_method="' + Open_order.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(Open_order.subtotal) + '</td>' +
                        '<td><i class="fa fa-trash delete-btn" ></i></td>' +
                        // '<td><a id="orderAction" class="view-order-link"  href=""  data-order-id="' + Items.itemsId + '">delete</a></td>';
                        '</tr>';
                    // Append each row to the tbody of the table
                    var newRow = $(partial_current_order);
                    $('#dynamicTable tbody').append(newRow);
                    calculateProductTax(Open_order, newRow);

                });
                response.forEach(function (Open_order) {
                    var unit_price = parseFloat(Open_order.unit_price).toFixed(2);
                    console.log('current_order');
                    console.log(Open_order);
                    const current_order =   // Item Body
                        '<tr >' +
                        '<td>' + Open_order.categoryName + '</td>' +
                        '<td>' + Open_order.product_name + '</td>' +
                        '<td class=""><input type="number" name = "order_quantity[]" style="width:50%" class="text-end quantity-input" value="' + Open_order.order_quantity + '"></td>' +
                        '<td>' + Open_order.unitName + '</td>' +
                        '<td id="net_price" class="text-end net_price " data-net_price="' + Open_order.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + Open_order.tax_rate + '"  data-tax_rate="' + Open_order.tax_rate + '">' + formatMoney(Open_order.tax_rate) + '</td>' +
                        // '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '">' + Open_order.subtotal + '</td>' +
                        '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '"  data-tax_method="' + Open_order.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +

                        '<td><i class="fa fa-trash delete-btn" ></i></td>' +
                        // '<td><a id="orderAction" class="view-order-link"  href=""  data-order-id="' + Items.itemsId + '">delete</a></td>';
                        '</tr>';
                    var newRow = $(current_order);
                    $('#dynamicTable tbody').append(newRow);
                    calculateProductTax(Open_order, newRow);
                });
                var rowCount = $('#dynamicTable tbody tr').length;
                var tfootRows = '<tr>' + //Item footer
                    '<td><strong>Total Items</strong></td>' +
                    '<td id = "rowCount">' + rowCount + '</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td id ="net_prices" class="text-end">' + '</td>' +
                    '<td id ="tax_rates" class="text-end">' + '</td>' +
                    '<td id ="subTotals" class="text-end">' + '</td>' +
                    '<td></td>' +
                    '</tr>';
                $('#dynamicTable tfoot').append(tfootRows);
                $(".repeat_order").hide();
                $("#add_note").show();
                $("#place_order").show();
                calculateOrderTotal();

            }
            if (selectedHorizonatlTab == 'Open') {
                $('#dynamicTable tfoot').empty();
                $('#dynamicTable tbody').empty();

                let Open_orders = '<tr>' +
                    '<th scope="col">Order #</th>' +
                    '<th scope="col">Total Items</th>' +
                    '<th scope="col">Placed On</th>' +
                    '<th scope="col">Placed By</th>' +
                    '<th scope="col">Update</th>' +
                    '</tr>';
                $('#dynamicTable thead').html(Open_orders);
                response.forEach(function (Open_order) {

                    // var arrayLength = myArray.length;
                    var Open_orders =
                        '<tr>' +
                        '<td class="">' + Open_order.orderNo + '</td>' +
                        '<td class="text-end">' + Open_order.itemCount + '</td>' +
                        '<td>' + Open_order.placedOn + '</td>' +
                        '<td>' + Open_order.placedBy + '</td>' +
                        '<td><a href="" id = "orderAction" class="Update_rec-order-link" data-order-id="' + Open_order.orderId + '" data-order-no="' + Open_order.orderNo + '">Update Order</a></td>';
                    '</tr>';
                    $('#dynamicTable tbody').append(Open_orders);
                });
                $(".place_order").hide();
                $(".update_order").hide();


            }
            if (selectedHorizonatlTab == 'partially') {
                $('#dynamicTable tfoot').empty();
                $('#dynamicTable tbody').empty();
                let partially_order = '<tr>' +
                    '<th scope="col">Order #</th>' +
                    '<th scope="col">Total Items</th>' +
                    '<th scope="col">Placed On</th>' +
                    '<th scope="col">Placed By</th>';
                partially_order += '<th scope="col">Received On</th><th scope="col">Partial Items</th>';
                partially_order += '</tr>';
                $('#dynamicTable thead').html(partially_order);

                response.forEach(function (Open_order) {
                    var partially_order =
                        '<tr>' +
                        '<td>' + Open_order.orderNo + '</td>' +
                        '<td class="text-end">' + Open_order.itemCount + '</td>' +
                        '<td>' + Open_order.placedOn + '</td>' +
                        '<td>' + Open_order.placedBy + '</td>' +
                        '<td>' + Open_order.receivedOn + '</td>' +
                        '<td><a href="" id = "orderAction" class="partial-order-link" data-order-id="' + Open_order.orderId + '" data-order-no="' + Open_order.orderNo + '">View</a></td>';
                    '</tr>';
                    // Append each row to the tbody of the table
                    $('#dynamicTable tbody').append(partially_order);
                });
                $("#repeat_order").hide();
                $(".update_order").hide();
                $("#place_order").hide();
                $(".repeat_order").hide();

            }
            if (selectedHorizonatlTab == 'partial_order_item') {
                $('#dynamicTable tbody').empty();
                let partial_order_item =
                    '<tr>' +
                    '<th scope="col">Category</th>' +
                    '<th scope="col">Product</th>' +
                    '<th scope="col">Qty. Requested</th>' +
                    '<th scope="col">Qty. Received</th>' +
                    '<th scope="col">Difference</th>';
                partial_order_item += '<th scope="col">Add to Current Order</th>';
                partial_order_item += '</tr>';
                $('#dynamicTable thead').html(partial_order_item);
                response.forEach(function (Open_order) {
                    console.log("partial");
                    console.log(Open_order);
                    //    $('#plusIcon1').attr('data-id', Open_order.order_id);
                    var Difference = (Open_order.order_quantity - Open_order.received_quantity);
                    var partial_order_item =
                        '<tr>' +
                        '<td>' + Open_order.categoryName + '</td>' +
                        '<td>' + Open_order.product_name + '</td>' +
                        '<td>' + Open_order.order_quantity + '</td>' +
                        '<td>' + Open_order.received_quantity + '</td>' +
                        '<td>' + Difference + '</td>' +
                        // '<td><input type="hidden" name="subtotal[]" class="text-center unitName-input" value="' + Open_order.subtotal + '">' + '</td>' +
                        // '<td><i class="fa fa-plus-circle plusIcon1 partial_order_link" data-order-id="' + Open_order.order_id + '"></i><span id="addedText1" style="display: none; color:#00C314">Added to Current Order</span></td>' +
                        // '</tr>';
                        '<td><i class="fa fa-plus-circle partial_order_item_addToCurrentOrder" data-order-id="' + Open_order.order_id + '" data-unit="' + Open_order.unitName + '" data-unit_price="' + Open_order.unit_price + '" data-tax_method="' + Open_order.tax_method + '" data-tax_rate="' + Open_order.tax_rate + '"data-unit-price="' + Open_order.subtotal + '"></i>' +
                        '<span class="addedText" style="display: none; color: #00C314;">Added to Current Order</span></td>' +
                        '</tr>';

                    // Append each row to the tbody of the table
                    $('#dynamicTable tbody').append(partial_order_item);
                    loadOrdersFromLocalStorage();
                });

            }
            if (selectedHorizonatlTab == 'previous_order_received') {
                $('#dynamicTable tbody').empty();
                let previous_order_received = '<tr>' +
                    '<th scope="col">Category</th>' +
                    '<th scope="col">Product</th>' +
                    '<th scope="col">Ordered Qty.</th>' +
                    '<th scope="col">Received Qty.</th>' +
                    '<th scope="col">Unit</th>';
                previous_order_received += '<th scope="col">Sub Total</th>';
                previous_order_received += '</tr>';
                $('#dynamicTable thead').html(previous_order_received);
                response.forEach(function (received_order_item) {
                    var subtotal = parseFloat(received_order_item.subtotal).toFixed(2);
                    //     var redDotStyle = (received_order_item.received_quantity < received_order_item.order_quantity) ? 'display: inline-block;' : 'display: none;';
                    var previous_order_received =
                        '<tr>' +
                        '<td>' + received_order_item.categoryName + '</td>' +
                        '<td>' + received_order_item.product_name + '</td>' +
                        '<td class="text-end">' + received_order_item.order_quantity + '</td>' +
                        // '<td class="received-quantity">' + received_order_item.received_quantity + '<span class="red-dot" style="' + redDotStyle + '"></span></td>' +
                        '<td class="received-quantity text-end">' + received_order_item.received_quantity + '</td>' +
                        '<td>' + received_order_item.unitName + '</td>' +
                        '<td id="subtotal" class="text-end subtotal " >' + formatMoney(subtotal) + '</td>' +
                        '</tr>';

                    // Append each row to the tbody of the table
                    $('#dynamicTable tbody').append(previous_order_received);
                    $("#repeat_order").show();
                    $("#add_note").hide();
                    $(".right_section").hide();
                    // var orderQty = received_order_item.order_quantity;
                    // var receivedQty = received_order_item.received_quantity;
                    // alert(orderQty)
                    // alert(receivedQty)
                    // if ((orderQty < receivedQty)) {
                    //     $(".red-dot").show();
                    // }
                });
                var rowCount = $('#dynamicTable tbody tr').length;
                var tfootRow = '<tr>' + //Item footer
                    '<td><strong>Total Items</strong></td>' +
                    '<td id = "rowCount">' + rowCount + '</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td><strong>Order Total</strong></td>' +
                    '<td id ="subTotals" class="text-end">' + '</td>' +
                    '</tr>';

                $('#dynamicTable tfoot').append(tfootRow);
                calculateOrderTotal();

            }
            if (selectedHorizonatlTab == 'previous_order') {
                $('#dynamicTable tfoot').empty();
                $('#dynamicTable tbody').empty();
                let previous_order = '<tr>' +
                    '<th scope="col">Order #</th>' +
                    '<th scope="col">Total Items</th>' +
                    '<th scope="col">Placed On</th>' +
                    '<th scope="col">Placed By</th>' +
                    '<th scope="col">Received On</th>' +
                    '<th scope="col">Order Status</th>' +
                    '<th scope="col">View Order</th>';
                previous_order += '</tr>';
                $('#dynamicTable thead').html(previous_order);

                response.forEach(function (Open_order) {
                    console.log('Open_order');
                    console.log(Open_order);
                    var previous_order =
                        '<tr>' +
                        '<td>' + Open_order.orderNo + '</td>' +
                        '<td class="text-end">' + Open_order.itemCount + '</td>' +
                        '<td>' + Open_order.placedOn + '</td>' +
                        '<td>' + Open_order.placedBy + '</td>' +
                        '<td>' + Open_order.receivedOn + '</td>' +
                        '<td>' + Open_order.orderStatus + '</td>' +
                        '<td><a href="" id = "orderAction" class="Update-order-link" data-order-id="' + Open_order.orderId + '" data-order-no="' + Open_order.orderNo + '">View</a></td>';
                    '</tr>';
                    // Append each row to the tbody of the table
                    $('#dynamicTable tbody').append(previous_order);


                });

                $(".place_order").hide();
                $(".right_section").hide();

            }
            // $('#dynamicTable tfoot').empty();
        }
    }
    ////////////////////////////////////////////////////// CHANGE QUANTITY /////////////////////////////////////////////////////////

    $(document).on('input', '.quantity-input', function () {
        var inputValue = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(inputValue);
        if (isNaN(inputValue) || inputValue <= 0) {
            $(this).val(parseInt(inputValue, 10) + 1);
        }
        var total = 0;
        var totalTax = 0;
        var totalnetPrice = 0;
        var row = $(this).closest('tr');
        var unitPrice = parseFloat(row.find('.subtotal').data('order-id'));
        var taxRate = parseFloat(row.find('.tax_rate').data('tax_rate'));
        var taxMethod = parseFloat(row.find('.subtotal').data('tax_method'));
        var quantity = parseFloat($(this).val());
        if (!isNaN(quantity) && !isNaN(unitPrice)) {
            var subtotal = quantity * unitPrice;
            row.find('.subtotal').text(formatMoney(subtotal)); // Update the subtotal cell in this row

            // Calculate tax based on tax method
            var productDetails = {
                unit_price: unitPrice,
                tax_rate: taxRate,
                quantity: quantity,
                tax_method: taxMethod
            };
            calculateProductTax(productDetails, row);

            // Calculate total of all subtotals and tax
            $('#dynamicTable tbody tr').each(function () {
                var subtotalValue = parseFloat($(this).find('.subtotal').data('item_price'));
                var subtotaltax_rate = parseFloat($(this).find('.tax_rate').data('tax_rate'));
                var subtotalnet_price = parseFloat($(this).find('.net_price').data('net_price'));
                var rowTax = parseFloat($(this).find('.tax_rate').text().replace(/[^\d.-]/g, ''));
                if (!isNaN(subtotalValue)) {
                    total += subtotalValue;
                    totalTax += subtotaltax_rate;
                    totalnetPrice += subtotalnet_price;
                }
            });

            // Update total and tax displays
            $('#subTotals').text(formatMoney(total));
            $('#net_prices').text(formatMoney(totalnetPrice));
            $('#tax_rates').text(formatMoney(totalTax));
            calculateOrderTotal();
        }

    });
    ///////////////////////////////////////////////////// CALCULATE TAX ///////////////////////////////////////////////////////
    // Function to calculate tax based on product details
    function calculateProductTax(productDetails, row = null) {

        var unitPrice = parseFloat(productDetails.unit_price);
        var taxRate = parseFloat(productDetails.tax_rate);
        var quantity = parseFloat(productDetails.quantity);
        var taxMethod = parseFloat(productDetails.tax_method);

        var netPrice = 0;
        var productTax = 0;
        if (taxMethod == 0) {
            pr_tax_val = formatDecimal((((unitPrice) * parseFloat(taxRate)) / (100 + parseFloat(taxRate))), 4);
            pr_tax_rate = formatDecimal(taxRate) + '%';
        } else {
            pr_tax_val = formatDecimal((((unitPrice) * parseFloat(taxRate)) / 100), 4);
            pr_tax_rate = formatDecimal(taxRate) + '%';
        }
        // var net = netPrice * quantity;
        productTax += pr_tax_val * quantity;
        netPrice = (taxMethod == 0) ? formatDecimal(unitPrice - pr_tax_val, 4) : formatDecimal(unitPrice);
        netPrice = netPrice * quantity;
        // $('.tax_rate').text(formatMoney(productTax));
        // $('.net_price').text(formatMoney(netPrice));
        // Update the tax rate and net price in the row
        row.find('.tax_rate').text(formatMoney(productTax));
        row.find('.net_price').text(formatMoney(netPrice));

    }
    //////////////////////////////////////////////////// CALCULATE TOTAL ////////////////////////////////////////////////////////////
    function calculateOrderTotal() {
        var totalSubtotal = 0;
        var totalTax = 0;
        var totalNetPrice = 0;

        $('#dynamicTable tbody').find('.subtotal').each(function () {
            var subtotalText = $(this).text().trim();
            var subtotalValue = parseFloat(subtotalText.replace(/^Rs.\s*/, '').replace(/,/g, ''));
            if (!isNaN(subtotalValue)) {
                totalSubtotal += subtotalValue;
            }
        });
        $('#dynamicTable tbody').find('.tax_rate').each(function () {
            var taxText = $(this).text().trim();
            taxValue = parseFloat(taxText.replace(/^Rs.\s*/, '').replace(/,/g, ''));
            if (!isNaN(taxValue)) {
                totalTax += taxValue;
            }
        });

        $('#dynamicTable tbody').find('.net_price').each(function () {
            var netPriceText = $(this).text().trim();
            var netPriceValue = parseFloat(netPriceText.replace(/^Rs.\s*/, '').replace(/,/g, ''));

            if (!isNaN(netPriceValue)) {
                totalNetPrice += netPriceValue;
            }
        });

        $('#subTotals').text(formatMoney(totalSubtotal));
        $('#tax_rates').text(formatMoney(totalTax));
        $('#net_prices').text(formatMoney(totalNetPrice));
        return;
    }
    //////////////////////////////////////////////////// POPULATE CATEGORIES //////////////////////////////////////////
    function showCategories() {
        $('.categoriesList').empty();
        $('.subcategoriesList').empty();
        $('.productList').empty();
        $.each(categories, function (index, category) {

            $('.categoriesList').append('<li class="btn btn-sty category" data-category-id="' + category.category_id + '">' + category.category_name + '</li>');
            $('.categoriesList li:last-child').css({
                //'background-color': '#009DFF',
                // 'color': '#fff'
                'background': 'linear-gradient(to right, transparent 96%, rgb(255 0 165) 4%)',
            });
            $.each(category.subcategories, function (index, subcategory) {
                $('.subcategoriesList').append('<li class="btn btn-sty subcategory" style="display:none;" data-category-id="' + category.category_id + '">' + subcategory.subcategory_name + '</li>');
            });
        });
    }
    showCategories();
    $('.recent').hide();
    $(document).on('click', '.category', function () {
        $('.categoriesList').hide();
        $('.subcategoriesList').show();
        $('.recent').show();
        categoryName = $(this).text();
        var categoryId = $(this).data('category-id');
        var categoryName = $(this).text();
        $('.catName').text(categoryName);
        var $subcategories = $('.subcategory[data-category-id="' + categoryId + '"]');
        if ($subcategories.length > 0) {
            $subcategories.show();
        } else {
            var categoryName = $(this).text();
            var categoryId = $(this).data('category-id');
            getProcuremntOrdersScreenLoadData(null, null, null, categoryId);
        }
    });
    $(document).on('click', '.subcategory', function () {
        $('.recent').show();
        var subcategoriesId = $(this).data('category-id');
        var subcategoryName = $(this).text();
        $('.subCatName').text(subcategoryName);
        getProcuremntOrdersScreenLoadData(subcategoriesId);
    });
    $('.hand-o-left').click(function () {
        showCategories();
        $('.subcategoriesList').hide();  // Hide subcategories list
        $('.categoriesList').show();
        $('.subCatName').empty();
        $('.recent').hide();
        // Show categories list
    });
    /////////////////////////////////////////////////// SEARCH CATEGORIES //////////////////////////////////////////////
    $('.search-box').on('keyup', function () {
        var searchText = $(this).val().toLowerCase(); // Get user input and convert to lowercase

        // Filter categories
        $('.categoriesList li').each(function () {
            var categoryText = $(this).text().toLowerCase();
            var isVisible = categoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });

        // Filter subcategories
        $('.subcategoriesList li').each(function () {
            var subcategoryText = $(this).text().toLowerCase();
            var isVisible = subcategoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });
        $('.productList li').each(function () {
            var subcategoryText = $(this).text().toLowerCase();
            var isVisible = subcategoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });
    });
    ////////////////////////////////////////////////// RIGHT SECTION DATA PRODUCT NAME LIST ///////////////////////////////////
    // Show products when subcategory is clicked

    function getProductbyCat(requests) {
        requests.forEach(function (product) {
            $('.subcategoriesList').hide();
            if (product) {
                var productItem = $('<li class="btn btn-sty ItemsDetails" data-product-id="' + product.id + '">' + product.name + '</li>');
                // $('.productList li:last-child').css('background-color', '#CCCCCC'); 
                $('.productList').append(productItem);
                productItem.click(function () {
                    localStorage.setItem('requestsData', JSON.stringify(requests));
                    var storedRequests = JSON.parse(localStorage.getItem('requestsData'));
                    console.log("Data stored in local storage:", storedRequests);
                    var productId = $(this).data('product-id').toString();
                    console.log("Product ID:", productId);
                    function findById(storedRequests, id) {
                        for (var i = 0; i < storedRequests.length; i++) {
                            console.log("Comparing:", storedRequests[i].id, id);
                            if (storedRequests[i].id === id) {
                                return storedRequests[i];
                            }
                        }
                        return null;
                    }
                    var productDetails = findById(storedRequests, productId);
                    $('#add_note').show();
                    var order = 'currentorder';
                    loadOrderItems(null, productDetails, order);

                });
            }
        });
    }


    ////////////////////////////////////////////////// UPDATE ORDER DB SAVE //////////////////////////////////////////////////////////////
    $('.update_order').click(function () {
        var tableData = [];
        var dataId = $(this).data('id');
        var note = $('#editor').redactor('get');
        // Iterate through each row in tbody
        $('#dynamicTable tbody tr').each(function (index, row) {
            var rowData = {};
            $(row).find('td').each(function (index, column) {
                var columnName = $('#dynamicTable thead th').eq(index).text().trim().toLowerCase().replace(/\s+/g, '_'); // Modify column header text
                var cellValue;
                var inputElement = $(column).find('input');
                if (inputElement.length > 0) {
                    cellValue = inputElement.val().trim(); // Get input value
                } else {
                    cellValue = $(column).text().trim(); // Get text content
                }
                rowData[columnName] = cellValue; // Store column data in the rowData object
            });
            rowData['order_id'] = dataId;
            rowData['note'] = note;
            tableData.push(rowData);
        });
        console.log('tableData', tableData);
        var queryParams = $.param({ orders: tableData });
        var url = site.base_url + "Production_Unit/updateOrder?" + queryParams;
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json', // Expect JSON data in response
            success: function (response) {
                Toastify({
                    text: 'Data Update successfully',
                    duration: 1000,  // Duration in milliseconds (1 seconds)
                    gravity: 'top-right', // Display position: 'bottom-left', 'bottom-right', 'top-left', 'top-right'
                    close: true,  // Whether to add a close button
                    callback: function () {
                        $('#dynamicTable thead').empty();
                        $('#dynamicTable tbody').empty();
                        $('#dynamicTable tfoot').empty();
                        localStorage.removeItem('currentOrderItems');
                        localStorage.removeItem('add_note');

                        $('.status').click();
                    }
                }).showToast();
            },
            error: function (xhr, status, error) {
                console.error('Error sending data: ' + error);
            }
        });
    });

});

$(document).ready(function () {

    function formatDate(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);
        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }
    function resetDropdown() {
        $('#outletName')[0].selectedIndex = 0; // Set to index 0 (first option)
        return true;
    }
    /////////////////////////////////////////////////////////// PLACE ORDER ////////////////////////////////////////
    // Initialize an empty array to store tbody data
    $('#place_order').click(function () {

        if ($('#dynamicTable tbody').children().length === 0) {
            alert('Please add Product.');
            return;
        }
        var outletNames = $('#outletName').val();
        var tableData = [];
        // var note = localStorage.getItem('add_note') || '';
        var note = $('#editor').redactor('get');
        var requestedDeliveryDate = formatDate($("#requestDeliveryDate").datepicker("getDate"));
        // Iterate through each row in tbody
        $('#dynamicTable tbody tr').each(function (index, row) {
            var rowData = {};
            $(row).find('td').each(function (index, column) {
                var columnName = $('#dynamicTable thead th').eq(index).text().trim().toLowerCase().replace(/\s+/g, '_'); // Modify column header text
                var cellValue;
                // Check if the cell contains an input element
                var inputElement = $(column).find('input');
                if (inputElement.length > 0) {
                    cellValue = inputElement.val().trim(); // Get input value
                } else {
                    cellValue = $(column).text().trim(); // Get text content
                }
                rowData[columnName] = cellValue; // Store column data in the rowData object
            });
            rowData['note'] = note;
            rowData['outletNames'] = outletNames;

            rowData['requested_delivery_date'] = requestedDeliveryDate;
            tableData.push(rowData);
        });
        console.log('tableData', tableData);
        var queryParams = $.param({ orders: tableData });
        var url = site.base_url + "Production_Unit/procurementOrders?" + queryParams;
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json', // Expect JSON data in response
            success: function (response) {
                Toastify({
                    text: 'Data inserted successfully',
                    duration: 1000,  // Duration in milliseconds (1 seconds)
                    gravity: 'top-right', // Display position: 'bottom-left', 'bottom-right', 'top-left', 'top-right'
                    close: true,  // Whether to add a close button
                    callback: function () {
                        $('#dynamicTable thead').empty();
                        $('#dynamicTable tbody').empty();
                        $('#dynamicTable tfoot').empty();
                        localStorage.removeItem('currentOrderItems');
                        localStorage.removeItem('add_note');
                        resetDropdown();
                        $('.status').click();
                    }
                }).showToast();
            },
            error: function (xhr, status, error) {
                console.error('Error sending data: ' + error);
            }
        });
    });

});

// --------------------------------------- css style change to full with of open order tab -----------------------------------//


$(document).ready(function () {
    $('.nav-item').click(function () {
        $('.sixty-eight-percent.sty-bg-set').css('width', '80%');
        $('.wd-set.p-0.bg-setting').css('width', '20%');
    });
});

$(document).ready(function () {
    function toggleDivs() {
        if ($('#activeStatus').hasClass('active')) {
            $('.hide-div').show();
            $('.show-div').hide();
        } else {
            $('.hide-div').hide();
            $('.show-div').show();
        }

        // Additional check for #dynamicTable visibility
        if ($('#dynamicTable').is(':hidden')) {
            $('.show-div').hide();
        }
    }

    $('.nav-link').on('click', function () {
        $('.nav-item').removeClass('active');
        $(this).parent().addClass('active');
        toggleDivs();
    });

    // Click event for #repeat_order
    $('#repeat_order').on('click', function () {
        // Redirect to current order tab logic (not provided in the snippet)

        // Hide .show-div after redirect
        $('.hide-div').show();
        $('.show-div').hide();
    });

    // Ensure the correct div is shown on page load based on the active tab and dynamicTable visibility
    toggleDivs();
});

$(document).ready(function () {
    $('#activeStatus .status').click(); // This line triggers the click event on page load
});