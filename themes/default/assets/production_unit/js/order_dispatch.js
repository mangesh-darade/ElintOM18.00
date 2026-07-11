$(document).ready(function () {

    //======================================== Filter By Outlets ===============================================================

    $('#searchInput').change(function () {
        var locationNames = $(this).find("option:selected").text();
        var parts = locationNames.split('-');
        var locationName = parts[1];
        var locationcode = parts[0];
        var reLlocationName = locationName + ' - ' + locationcode;
        // var locationName = $(this).val();
        if (locationNames) {
            $('#locationName').text((reLlocationName));
            localStorage.setItem('locationName', locationName);
        } else {
            $('#locationName').text((''));
            localStorage.removeItem('locationName');
        }
        getAllOrders(locationName);
        getOrderItems(null, locationName);

    });
    $('#searchInput').select2({
        placeholder: 'Select Location Name',
        allowClear: true // Allow clearing the selection
    });
    $('#filterOutlets').on('click', function () {
        $('#searchForm').show();// Show the search form
        $('#timestampCheckboxList').hide();
    });

    //======================================== Filter By Timestamp ===============================================================

    $('#filterByTimestamp').on('click', function () {
        $('#timestampCheckboxList').show();
        $('#searchForm').hide();
    });
    $('#Oldest').on('click', function () {
        if ($(this).prop('checked')) {
            sortOrderByTime = 'Oldest';
            getAllOrders(locationName = null, sortOrderByTime);
            $('#Newest').prop('checked', false);
        }
    });
    $('#Newest').on('click', function () {
        if ($(this).prop('checked')) {
            sortOrderByTime = 'Newest';
            getAllOrders(locationName = null, sortOrderByTime);
            $('#Oldest').prop('checked', false);
        }
    });

    //======================================== Left menu show all orders ===============================================================

    // Ajax call to fetch all orders
    function getAllOrders(locationName = null, sortOrderByTime) {

        $.ajax({
            url: site.base_url + "Production_Unit_New/getCompleteOrdersForLoggedInProductionUnit",
            method: 'GET',
            data: {
                locationName: locationName,
                sortOrderByTime: sortOrderByTime

            },
            dataType: 'json',
            success: function (response) {
                var AllOrders = response;
                // AllOrders.forEach(function (Data) {
                //     var procurmentOrderId = Data.procurmentOrderId;
                //     getOrderItems(procurmentOrderId, locationName);  // get location and order id for filter
                // });
                AllOrdersList(AllOrders)
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }
    getAllOrders();

    document.addEventListener('DOMContentLoaded', function () {
        var lockButton = document.getElementById('locked');

        // Add event listener for the lock button
        lockButton.addEventListener('click', function (event) {
            console.log('Lock button clicked'); // Check if the event is triggered

            // Prevent default action if needed
            event.preventDefault();

            // Get the active item ID from local storage
            var activeItemId = localStorage.getItem('activeItemId');

            // Check if the active item is already locked
            if (activeItemId && localStorage.getItem(activeItemId + '_isLocked') === 'true') {
                return; // Do nothing if the active item is already locked
            }

            // Toggle the 'locked' class to change the background and text color
            this.classList.toggle('locked');

            // Change the text and add the icon
            var buttonText = this.querySelector('.button-text');
            if (this.classList.contains('locked')) {
                buttonText.innerHTML = '🔒 Locked';

                // Add locked icon to the active li if it's not already present
                if (activeItemId) {
                    var activeLi = $('#' + activeItemId);
                    if (activeLi.find('.fa-lock').length === 0) {
                        activeLi.append('<i class="fa fa-lock info-icon1"></i>');
                    }
                    // Save locked state in local storage for this item
                    localStorage.setItem(activeItemId + '_isLocked', 'true');
                }
            } else {
                buttonText.innerHTML = 'Lock Order';

                // We are not removing the lock icon or locked state from local storage
                // because the requirement is to keep the lock icon once added.
            }

            // Reload the page
            location.reload();
        });

        // Function to update the lock button state on page load
        function updateLockButtonState() {
            var activeItemId = localStorage.getItem('activeItemId');
            if (activeItemId && localStorage.getItem(activeItemId + '_isLocked') === 'true') {
                lockButton.classList.add('locked');
                lockButton.querySelector('.button-text').innerHTML = '🔒 Locked';
            } else {
                lockButton.classList.remove('locked');
                lockButton.querySelector('.button-text').innerHTML = 'Lock Order';
            }
        }

        // Call the function on page load
        updateLockButtonState();
    });

    var globalorderItemsDetails;

    // Show all order list in left menu
    function AllOrdersList(AllOrders) {
        $('.mainmenu').empty();  // Clear the current menu items
        // Separate completed, open, and locked orders
        var completedOrders = [];
        var openLockedOrders = [];
        var currentLi;

        AllOrders.forEach(function (Orders, index) {
            var OrderData = Orders.AllOrders;
            OrderData.forEach(function (Order, orderIndex) {

                // OrderData.forEach(function (Order, orderIndex) {
                // Only set locationData for the first order
                // if (index === 0 && orderIndex === 0) {
                //     var locationData = Order.location_name + ' - ' + Order.location_code;
                //     $('#locationName').text(locationData); // Show location data for the first order
                //     $('#order_status').text((Order.status)); // Show order status for the first order
                // }
                // $('#productionUnit').text((Order.ProductionUnitName)); // show production unit name 
                $('#productionUnit').text((Order.ProductionUnitName));
                var deliveryTime = Order.planned_delivery_datetime;
                var locationData = Order.location_name + ' - ' + Order.location_code;
                var plannedDeliveryDate = ((deliveryTime == null) ? "00:00:00" : deliveryTime); //condition ? trueExpression : falseExpression;
                var plantime = plannedDeliveryDate.split(' ')[1];
                var orderCreationDate = Order.order_creation_date;
                var note = Order.note;
                var infoicon = '';
                var OrderStatus = Order.status.toLowerCase();

                sessionStorage.setItem('OrderStatus_' + Order.id, OrderStatus);

                // Check if the note is not blank, then display the info icon
                if (note && note.trim() !== '') {
                    // var orderNote = note.replace(/<[^>]+>/g, ''); // Remove HTML tags from note
                    infoicon = `<i class="fa fa-info-circle info-icon" onclick="showDiv('${encodeURIComponent(note)}')"></i>`;
                }
                var li = $('<li class="procurementOrder" id="procurementOrderId_' + Order.id + '" data-location="' + locationData + '" data-refno ="' + Order.procurement_order_ref_no + '" data-date="' + plantime + '" data-order-creation-date="' + orderCreationDate + '" data-order-status="' + OrderStatus + '">' +
                    Order.procurement_order_ref_no + '<br>' +
                    orderCreationDate + infoicon +
                    '</li>');

                // Check if this item was previously locked and add the lock icon if it was
                if (OrderStatus === 'locked') {
                    // Store the lock status in localStorage
                    localStorage.setItem('OrderStatus_' + Order.id + '_isLocked', 'true');
                    li.append('<i class="fa fa-lock info-icon1"></i>');
                }
                $('.mainmenu').append(li);
                // Update the order status color
                setHardcodedDeliveryTime(plantime);
                setOrderAge(orderCreationDate);
                //updateOrderStatusColor1(OrderStatus, li);

                // Add the list item to the appropriate array
                if (OrderStatus === 'completed') {
                    completedOrders.push(li);
                    // Check if this is the currently selected item
                    if (localStorage.getItem('activeItemId') === 'procurementOrderId_' + Order.id) {
                        currentLi = li;  // Mark this item as the current one
                    }
                } else {
                    openLockedOrders.push(li);
                }
            });
        });

        // Append open and locked orders first, then completed orders
        openLockedOrders.forEach(function (li) {
            $('.mainmenu').append(li);
        });

        // Append completed orders
        completedOrders.forEach(function (li) {
            $('.mainmenu').append(li);
        });

        // Append the current item at the end if it was marked as current
        if (currentLi) {
            $('.mainmenu').append(currentLi);
        }

        // Retrieve the active item from local storage
        var activeItemId = localStorage.getItem('activeItemId');
        if (!activeItemId) {
            // If no active item is set, set the first item as active and store it in local storage
            activeItemId = $('.procurementOrder:first').attr('id');
            localStorage.setItem('activeItemId', activeItemId);
        }

        // Update the style of the active item
        $('.procurementOrder').each(function () {
            var $li = $(this);
            if ($li.attr('id') === activeItemId) {
                // Apply active styles to the active item
                $li.css({
                    'background-color': 'rgb(3, 155, 229)', // Active background color
                    'color': 'rgb(255, 255, 255)' // Active text color
                });
                // Add locked icon if the current active item is locked
                if ($li.data('order-status') === 'locked') {
                    $li.append('<i class="fa fa-lock info-icon1"></i>');
                }
            } else {
                // Maintain gray color for completed items
                if ($li.data('order-status') === 'completed') {
                    $li.css({
                        'background-color': 'rgb(128, 128, 128)', // Gray background
                        'color': 'rgb(255, 255, 255)' // White text color
                    });
                } else {
                    // Reset other items to default
                    $li.css({
                        'background-color': '', // Default background color
                        'color': '' // Default text color
                    });
                }
            }
        });

        // Add click event to change the active item and update the lock button state
        $('.procurementOrder').click(function () {
            var clickedItem = $(this);
            var clickedItemId = clickedItem.attr('id');
            var isCurrentlyActive = (clickedItemId === localStorage.getItem('activeItemId'));

            if (isCurrentlyActive) {
                // Toggle the active status of the currently active item
                if (clickedItem.css('background-color') === 'rgb(128, 128, 128)') {
                    // Change gray to active style
                    clickedItem.css({
                        'background-color': 'rgb(3, 155, 229)', // Active background color
                        'color': 'rgb(255, 255, 255)' // Active text color
                    });
                } else {
                    // If clicked item is active but not gray, revert to gray for completed
                    if (clickedItem.data('order-status') === 'completed') {
                        clickedItem.css({
                            'background-color': 'rgb(128, 128, 128)', // Gray background
                            'color': 'rgb(255, 255, 255)' // White text color
                        });
                    }
                }
            } else {
                // Reset the style of all items to default
                $('.procurementOrder').each(function () {
                    var $li = $(this);
                    if ($li.data('order-status') === 'completed') {
                        $li.css({
                            'background-color': 'rgb(128, 128, 128)', // Gray background
                            'color': 'rgb(255, 255, 255)' // White text color
                        });
                    } else {
                        $li.css({
                            'background-color': '', // Default background color
                            'color': '' // Default text color
                        });
                    }
                });

                // Change the style of the clicked item to active
                clickedItem.css({
                    'background-color': 'rgb(3, 155, 229)', // Active background color
                    'color': 'rgb(255, 255, 255)' // Active text color
                });

                // Store the ID of the clicked item in local storage
                localStorage.setItem('activeItemId', clickedItem.attr('id'));
            }

            // Update the lock button state based on the clicked item's lock status
            if (localStorage.getItem(clickedItem.attr('id') + '_isLocked') === 'true') {
                $('#locked').addClass('locked');
                $('#locked .button-text').text('🔒 Locked');
            } else {
                $('#locked').removeClass('locked');
                $('#locked .button-text').text('Lock Order');
            }

            // Ensure that lock icons for locked orders are not removed
            $('.procurementOrder').each(function () {
                var $li = $(this);
                if ($li.data('order-status') === 'locked') {
                    if ($li.find('.fa-lock').length === 0) {
                        $li.append('<i class="fa fa-lock info-icon1"></i>');
                    }
                }
            });

            // Enable the elements
            enableElements();
        });

        // Call loadItems function to load the order items
        // procurmentOrderId = localStorage.getItem('procurmentOrderId');
        // loadOrderItems(procurmentOrderId);
        // Forcefully update the color and reposition completed orders dynamically
        $('.procurementOrder').each(function () {
            var $li = $(this);
            if ($li.data('order-status') === 'completed') {
                $li.css({
                    'background-color': 'rgb(128, 128, 128)', // Gray background
                    'color': 'rgb(255, 255, 255)' // White text color
                });
                // Move completed items to the end of the list
                $('.mainmenu').append($li);
            }
        });
        // On page load, trigger the click event on the active item if exists
        var activeItemId = localStorage.getItem('activeItemId');
        if (activeItemId) {
            $('#' + activeItemId).click(); // Trigger click on the item with the active ID
        }
    }


    function enableElements() {
        $('#locked').removeClass('disabled').prop('disabled', false);
        $('#completeOrder').removeClass('disabled').prop('disabled', false);
        $('.large-checkbox').removeClass('disabled').prop('disabled', false);
        $('.input-element').removeClass('disabled').prop('disabled', false);
        $('.checkbox-element').removeClass('disabled').prop('disabled', false);
    }

    // Set background color to gray for completed orders
    // function updateOrderStatusColor1(OrderStatus, li) {
    //     switch (OrderStatus.toLowerCase()) {
    //         case 'completed':
    //             li.css({
    //                 'background-color': 'gray', // Set background color to gray for completed orders
    //                 'color': '#fff' // Set text color to white for completed orders
    //             });
    //             break;
    //         case 'locked':
    //             // Do nothing, the locked icon is already added in the main function
    //             break;
    //         default:
    //             // Do nothing for other statuses
    //             break;
    //     }
    // }
    // Add this script at the end of your existing JavaScript code
    $(document).ready(function () {
        // Remove the active item ID from local storage
        localStorage.removeItem('activeItemId');

        // Apply the CSS styles to the first item
        $('.procurementOrder:first').css({
            'color': '#fff',
            'background-color': '#039be5'
        });
    });

    $('.mainmenu').on('click', '.procurementOrder', function () {
        // sessionStorage.removeItem('OrderItems_' + procurmentOrderId);

        var procurmentId = $(this).attr('id');
        var procurmentOrderId = procurmentId.split('_')[1];  // order id
        var locationData = $(this).data('location');    //Location Name And location Code
        var refnumber = $(this).data('refno');    //Location Name And location Code
        var plantime = $(this).data('date');
        var OrderAge = $(this).data('order-creation-date');
        var displayTime = plantime == "undefined" ? "00:00:00" : plantime;
        setHardcodedDeliveryTime(displayTime);
        setOrderAge(OrderAge);
        var outletName = locationData.split(" - ")[0]; // location name
        $('#timestampCheckboxList').hide();
        $('#refNumber').val((refnumber));
        $('input[name="procurement_order_ref_no"]').val(refnumber);

        // var locationName = localStorage.getItem('locationName');
        if (procurmentOrderId || outletName) {
            menuClicked = true;

            localStorage.setItem('outletName', outletName);
            localStorage.setItem('procurmentOrderId', procurmentOrderId);

            // var OrderStatus = sessionStorage.getItem('OrderStatus_' + procurmentOrderId);

            $('#locationName').text((locationData)); // show Request from Outlet in header section
            // $('#order_status').text((OrderStatus)); // show order status in middle section

            // Activate "All Items" tab
            $('.nav-item').removeClass('active');  // Remove active class to previous tab
            $('.nav-item-all').addClass('active');  // Add active class to "All Items" tab
            $('.tab-pane').addClass('in active');  // Remove active class from all tab contents
            // $('#tabs-1').addClass('in active');  // Add active class to "All Items" tab content
            getOrderItems(procurmentOrderId);
        }

    });
    //============================================== show orders Items  ===============================================================

    function getOrderItems(procurmentOrderId, locationName) {
        $.ajax({
            url: site.base_url + "Production_Unit_New/getOrderItemsForSelectedOrder",
            method: 'GET',
            data: {
                procurmentOrderId: procurmentOrderId,
                locationName: locationName,
            },
            dataType: 'json',
            success: function (response) {
                globalorderItemsDetails = response;  // define global for acess this items data anywhere

                // // Store the response data in sessionStorage
                // sessionStorage.setItem('OrderItems_' + procurmentOrderId, JSON.stringify(globalorderItemsDetails));

                globalorderItemsDetails.forEach(function (orderItemsDetails) {
                    var OrderItemData = orderItemsDetails.order_items;
                    var UserAddress = orderItemsDetails.userAddress;
                    $('#userAddress').text((UserAddress));  

                    if(OrderItemData === false){
                        sessionStorage.removeItem('OrderItems_' + procurmentOrderId);  // Remove the item from sessionStorage if already present
                        localStorage.removeItem('procurmentOrderId');
                        loadOrderItems(procurmentOrderId = null); // if location filter apply and data is blank against location
                    }else{
                        // Store the response data in sessionStorage
                        sessionStorage.setItem('OrderItems_' + procurmentOrderId, JSON.stringify(globalorderItemsDetails));

                        OrderItemData.forEach(function (data) {
                            var procurmentOrderId = data.procurement_orders_id;  // when page load show order items by dafault for 1st order
                            localStorage.setItem('procurmentOrderId', procurmentOrderId);
                            var location_name = data.location_name;
                            var location_code = data.location_code;
                            var ordering_outlet = location_name + ' - ' + location_code;
                            $('#locationName').text((ordering_outlet));  // show outlet name by default 1st order 
                            // loadOrderItems(procurmentOrderId);
                        });
                    }

                });
                // Store the response data in sessionStorage
                // sessionStorage.setItem('OrderItems_' + procurmentOrderId, JSON.stringify(globalorderItemsDetails));
                loadOrderItems(procurmentOrderId);
                // loadOrderItems();


            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }
    getOrderItems();

    // load order items in grid
    function loadOrderItems(procurmentOrderId, tabsInMiddleSection) {

        // Retrieve order items data from sessionStorage 
        var storedOrderItemsData = sessionStorage.getItem('OrderItems_' + procurmentOrderId);
        var orderItemsDetails = JSON.parse(storedOrderItemsData);

        $('#tablebody').empty();
        // localStorage.removeItem('orderStatus');
        // var orderStatus = null;

        var isToggleOn = $('.toggle-switch').is(':checked'); // Check the state of the toggle

        // globalorderItemsDetails.forEach(function (orderItems) {
        orderItemsDetails.forEach(function (orderItems) {

            var OrderItemsData = orderItems.order_items;
            OrderItemsData.forEach(function (Items) {

                if (Items.item_status == 'Open') {
                    var allotQuantity = '';   // For Open order items
                    localStorage.removeItem('checkbox_' + Items.itemId);
                    localStorage.removeItem('bulkAllotCheck_' + Items.procurement_orders_id); // remove allot quantity for bulk
                    localStorage.removeItem('allotQuantity_' + Items.itemId); // remove allot quantity for specific item
                }

                var orderStatus = Items.order_status;
                // var orderItemStatus = Items.item_status;
                var locationId = Items.user_location_id;  // loggedIn user location id
                var production_unit_id = Items.production_unit_id; // production unit id for item
                var procurement_order_ref_no = Items.procurement_order_ref_no;

                localStorage.setItem('locationId_' + procurmentOrderId, locationId); // set user loggedIn location Id in session
                localStorage.setItem('production_unit_id_' + procurmentOrderId, production_unit_id); // set production unit Id for specific item in session
                localStorage.setItem('procurement_order_ref_no_' + procurmentOrderId, procurement_order_ref_no); // set procurement_order_ref_no for specific order in session

                // Check the condition based on toggle state and production unit id
                if (!isToggleOn && Items.production_unit_id !== Items.user_location_id) {
                    return; // Skip this item if toggle is off and IDs don't match
                }
                var bulkAllotCheck = localStorage.getItem('bulkAllotCheck_' + procurmentOrderId); // get flag for bulk allot 
                var calculatedValues = calculateQuantity(Items);
                var isChecked = localStorage.getItem('checkbox_' + Items.itemId);

                var allotQuantityInput = localStorage.getItem('allotQuantity_' + Items.itemId); // get allot quantity
                var isEditable = locationId.includes(Items.production_unit_id);

                // check checbox state in session when click on bulk allot checbox
                if (bulkAllotCheck === "allChecked") {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }

                if (Items.item_status == 'Completed' || Items.item_status == 'partially_completed' || Items.item_status == 'Pending') {
                    var allotQuantity = Items.allot_quantity;   // For complete order items
                    isChecked = true; // set checkbox selected 
                } else if (Items.item_status == 'Open') {
                    var allotQuantity = '';   // For Open order items
                } else if (Items.item_status == 'Received') {
                    var allotQuantity = Items.received_quantity;   // For Received order items
                    isChecked = true; // set checkbox selected 
                } else {
                    var allotQuantity = allotQuantityInput ? allotQuantityInput : calculatedValues.allot_quantity;
                }

                // Filter items based on tabsInMiddleSection value and show items
                var orderQty = parseFloat(Items.order_quantity);
                var allotQty = parseFloat(Items.allot_quantity);

                if (tabsInMiddleSection === 'Completed' && allotQty !== orderQty) return; // Skip this item and do not display it in the table.
                if (tabsInMiddleSection === 'Pending' && allotQty !== 0) return;
                if (tabsInMiddleSection === 'Partially Completed' && (allotQty === orderQty || allotQty === 0)) return;

                var row = $("<tr class='text-center red'>" +
                    "<td class='cen-set'><p class='circle-set'>" + Items.order_quantity + "</p></td>" +
                    "<td class='textleft'>" + Items.product_name + "</td>" +
                    "<td>" + Items.stock_quantity + "</td>" +
                    "<td><input type='number' class='allot-set' name='quantity[]' style='width:20%; text-align:right;' value='" + parseFloat(allotQuantity) + "' " + Items.itemId + "\")'>" +
                    "<span class='ml-2'><input type='checkbox' class='large-checkbox' name='allot[]' id='checkbox_" + Items.itemId + "' value='" + Items.itemId + "' " + (isChecked ? 'checked' : '') + "></span></td>" +
                    "</tr>");

                if (orderStatus == 'Open' || orderStatus == 'Completed' || orderStatus == 'Received') {
                    row.find('.allot-set').prop('disabled', true);
                    row.find('.large-checkbox').prop('disabled', true);
                    $('#selectAll').prop('disabled', true);  // Disable the select all checkbox in the header
                }
                if (!isEditable) {
                    row.find('.allot-set').prop('disabled', true);
                    row.find('.large-checkbox').prop('disabled', true);
                }
                if (isChecked) {
                    row.find('.allot-set').prop('disabled', true);
                }

                $("#tablebody").append(row);
                $('#order_status').text((orderStatus)); // show order status in middle section

                // validation for input allot qty
                var inputField = row.find('input.allot-set');
                inputField.on('input', function () {

                    var inputValue = parseFloat($(this).val());
                    var stockQuantity = parseFloat(Items.stock_quantity);
                    var orderQuantity = parseFloat(Items.order_quantity);

                    // Check if the input value is not empty and is a valid integer
                    if (inputValue !== '' && !Number.isInteger(Number(inputValue))) {
                        alert('Please enter an integer value.');
                        var intValue = parseInt(inputValue, 10);
                        $(this).val(intValue);
                        return;
                    }

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

                // Initial check for the checkbox state
                if (isChecked) {
                    var inputValue = parseFloat(row.find('input.allot-set').val());
                    var stockQuantity = parseFloat(Items.stock_quantity);
                    var orderQuantity = parseFloat(Items.order_quantity);

                    if (inputValue === orderQuantity) {
                        row.css('background', 'linear-gradient(90deg, #00C314, rgba(0, 195, 20, 0))'); // Green
                        row.data('color', 'green'); // Store color information

                        // } else if (inputValue > orderQuantity || stockQuantity === 0) {
                    } else if (inputValue > orderQuantity && stockQuantity !== 0) {

                        row.css('background', 'linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))'); // Red
                        row.data('color', 'red'); // Store color information

                        // } else if (orderQuantity > inputValue && stockQuantity !== 0 && inputValue !== 0) {
                    } else if (orderQuantity > inputValue && inputValue !== 0) {

                        row.css('background', 'linear-gradient(90deg, #FFFF00, rgba(255, 255, 0, 0))'); // Yellow
                        row.data('color', 'yellow'); // Store color information
                    } else {

                        row.css('background', 'linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))'); // Red
                        row.data('color', 'red'); // Store color information
                    }
                }
                updateCompleteOrderButtonState(orderStatus);

            });

        });
        // Remove data from sessionStorage
        // sessionStorage.removeItem('OrderItems_' + procurmentOrderId);
        localStorage.removeItem('locationId_' + procurmentOrderId); // remove user loggedIn location Id from session
        localStorage.removeItem('production_unit_id_' + procurmentOrderId); // remove production unit Id for specific item from session
        updateOrderStatusColor();
        // updateCompleteOrderButtonState();

    }
    // on order status change disabled or enabled the buttons and some elements  //
    function updateOrderStatusColor() {

        var statusElement = $('#order_status');
        var status = statusElement.text().trim().toLowerCase();

        if (status === 'completed') {
            disableElements();
        } else if (status === 'locked') {
            $('#locked')
                .text('🔒 Locked')
                .css({
                    'color': '#fff',
                    'background-color': 'red'
                });
        } else if (status === 'received') {
            disableElements();
        } else {
            $('#locked')
                .text('Lock Order') // Reset the text to "Lock Order" for other statuses
                .css({
                    'color': '',           // Reset text color
                    'background-color': '' // Reset background color
                });
        }
    }
    function disableElements() {
        $('#tablebody input').prop('disabled', true);
        $('#locked').addClass('disabled').prop('disabled', true);
        $('#completeOrder').addClass('disabled').prop('disabled', true);
        $('.large-checkbox').addClass('disabled').prop('disabled', true);
    }

    // Function to check if any checkboxes are selected and update the completeOrder button state
    function updateCompleteOrderButtonState(orderStatus) {
        var anyChecked = $('#tablebody').find('input.large-checkbox:checked').length > 0;
        if (anyChecked) {
            $('#completeOrder').removeClass('disabled').prop('disabled', false);
            if (orderStatus === 'Completed') {
                $('#completeOrder').removeClass('disabled').prop('disabled', true);
            }
        } else {
            $('#completeOrder').addClass('disabled').prop('disabled', true);
        }
    }

    // for View Full Order
    $('.toggle-switch').on('change', function () {

        // localStorage.removeItem('procurmentOrderId'); 
        procurmentOrderId = localStorage.getItem('procurmentOrderId');
        getOrderItems(procurmentOrderId);
    });

    //======================================== show Items by middle sections tabs ===============================================================

    $('.nav-link').click(function () {
        var tabsInMiddleSection = $(this).attr('values'); // tabs values in grid
        procurmentOrderId = localStorage.getItem('procurmentOrderId');
        loadOrderItems(procurmentOrderId, tabsInMiddleSection);
    });
    //======================================== Locked order Functionality ===============================================================

    function lockOrder() {

        var Locked = true;  // flag for update status when click on lock button

        // Retrieve necessary data
        procurmentOrderId = localStorage.getItem('procurmentOrderId');

        // Call the function to update order status
        updateOrderDetails(procurmentOrderId, Locked);

        // Update the lock button's CSS
        $('#locked').css({
            'background-color': '#ff0000',
            'color': '#ffffff',
            'border': '1px solid #ff0000'
        });

        // Add Font Awesome lock icon to the active li with id="procurementOrderId_"
        $('li#procurementOrderId_' + procurmentOrderId).addClass('active').append('<i class="fa fa-lock info-icon1"></i>');
    }

    // Attach click event handler to the lock button
    $('#locked').click(function () {

        var orderStatus = localStorage.getItem('orderStatus');
        if (orderStatus == 'Locked') {
            return
        } else {
            $('#selectAll').prop('disabled', false);  // enable the select all checkbox in the header
            $('#tablebody').find('input.allot-set, input.large-checkbox').prop('disabled', false); // Enable all checkboxes and input fields
            // $('#tablebody').find('tr').each(function () {
            //     var isEditable = $(this).data('editable');
            //     if (!isEditable) {
            //         $(this).find('input.allot-set, input.large-checkbox').prop('disabled', true);
            //     }
            // });
            lockOrder();
        }
    });

    // update order status 
    function updateOrderDetails(procurmentOrderId, Locked, completeOrderFlag) {

        $.ajax({
            url: site.base_url + "Production_Unit_New/updateOrder",
            method: 'GET',
            data: {
                procurmentOrderId: procurmentOrderId,
                Locked: Locked,
                completeOrderFlag: completeOrderFlag

            },
            dataType: 'json',
            success: function (response) {
                var updatedOrderDetails = response;
                updatedOrderDetails.forEach(function (updatedOrderDetail) {

                    var OrderStatus = updatedOrderDetail.order_status; // get updated order status when click on lock button
                    $('#order_status').text(OrderStatus); //show order status when click on lock button

                });
                // Store the response data in sessionStorage
                sessionStorage.setItem('OrderItems_' + procurmentOrderId, JSON.stringify(updatedOrderDetails));
                // var procurmentOrderId = localStorage.getItem('procurmentOrderId');
                loadOrderItems(procurmentOrderId);

            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });

    }
    //======================================== checkbox check uncheck functionality ===============================================================

    // allot quantity for specific order item
    $('#tablebody').on('click', 'input[type="checkbox"]', function () {

        var isChecked = $(this).is(':checked');  // check checkbox select or not
        // var allotQuantityInput = $(this).closest('tr').find('input[name="quantity[]"]');
        orderItemId = $(this).val();  // order item id
        allotQuantityInput = $(this).closest('tr').find('input[name="quantity[]"]').val(); //allot quantity
        var procurmentOrderId = localStorage.getItem('procurmentOrderId');

        if (isChecked === true) {

            localStorage.setItem('checkbox_' + orderItemId, isChecked);
            localStorage.setItem('allotQuantity_' + orderItemId, allotQuantityInput); // store allot quantity
            var isChecked = "Checked"; // set flag for click on checkbox
            updateOrderItemDetails(procurmentOrderId, orderItemId, isChecked, allotQuantityInput);
        } else {

            localStorage.removeItem('checkbox_' + orderItemId);
            localStorage.removeItem('allotQuantity_' + orderItemId); // remove allot quantity
            var isChecked = "Unchecked"; // set flag for click on checkbox
            updateOrderItemDetails(procurmentOrderId, orderItemId, isChecked, allotQuantityInput);

        }
    });
    // allot quantity for bulk order item
    // $('#selectAll').on('click', function () {

    //     var allChecked = $(this).is(':checked');
    //     // $('#tablebody input[type="checkbox"]').prop('checked', allChecked);
    //     var itemData = [];

    //     var bulkAllotCheck    = allChecked ? "allChecked" : "allUnChecked";
    //     var procurmentOrderId = localStorage.getItem('procurmentOrderId');
    //     var locationId        = localStorage.getItem('locationId_' + procurmentOrderId, locationId); // get user loggedIn location Id from session

    //     // Iterate through each checkbox in the table body
    //     $('#tablebody tr').each(function () {

    //         var checkbox = $(this).find('input[type="checkbox"]');
    //         var orderItemId = checkbox.val();

    //         if (!checkbox.prop('disabled')) {

    //             var production_unit_id = localStorage.getItem('production_unit_id_' + procurmentOrderId, production_unit_id); // get production unit Id for specific item from session

    //             var allotQuantity = parseFloat($(this).closest('tr').find('.allot-set').val());
    //             allotQuantityInput = $(this).closest('tr').find('input[name="quantity[]"]').val(); //allot quantity

    //             localStorage.setItem('checkbox_' + orderItemId, allChecked);  // set checbox state in session when click on bulk allot checbox
    //             localStorage.setItem('bulkAllotCheck_' + procurmentOrderId, bulkAllotCheck);
    //             localStorage.setItem('allotQuantity_' + orderItemId, allotQuantityInput); // store allot quantity input 

    //             if (bulkAllotCheck === 'allUnChecked') {
    //                 // remove checbox state in session when click on bulk allot checbox
    //                 localStorage.removeItem('checkbox_' + orderItemId);
    //                 localStorage.removeItem('bulkAllotCheck_' + procurmentOrderId);
    //             }

    //             itemData.push({
    //                 orderItemId: orderItemId,
    //                 allotQuantity: allotQuantity
    //             });
    //         }
    //     });
    //     console.log(itemData);
    //     updateOrderItemDetails(procurmentOrderId, null, null, null, itemData, bulkAllotCheck);

    // });

    $('#selectAll').on('click', function () {
        var allChecked = $(this).is(':checked');
        var itemData = [];
        var bulkAllotCheck = allChecked ? "allChecked" : "allUnChecked";
        var procurmentOrderId = localStorage.getItem('procurmentOrderId');
        var locationId = localStorage.getItem('locationId_' + procurmentOrderId); // get user loggedIn location Id from session

        // Iterate through each checkbox in the table body
        $('#tablebody tr').each(function () {
            var checkbox = $(this).find('input[type="checkbox"]');
            var orderItemId = checkbox.val();

            if (!checkbox.prop('disabled')) {
                var production_unit_id = localStorage.getItem('production_unit_id_' + procurmentOrderId); // get production unit Id for specific item from session

                var allotQuantityInput = $(this).closest('tr').find('input[name="quantity[]"]').val(); //allot quantity

                // Check if the checkbox is already checked for this item
                var singleItemChecked = localStorage.getItem('checkbox_' + orderItemId);

                if (bulkAllotCheck === 'allChecked' && singleItemChecked !== 'true') {
                    // Only add item data if bulkAllotCheck is 'allChecked'
                    // and the single item checkbox is not already checked
                    localStorage.setItem('checkbox_' + orderItemId, allChecked.toString()); // store checkbox state
                    localStorage.setItem('bulkAllotCheck_' + procurmentOrderId, bulkAllotCheck);
                    localStorage.setItem('allotQuantity_' + orderItemId, allotQuantityInput); // store allot quantity input 

                    itemData.push({
                        orderItemId: orderItemId,
                        allotQuantity: allotQuantityInput
                    });
                } else if (bulkAllotCheck === 'allUnChecked') {
                    // Remove checkbox state from localStorage if bulkAllotCheck is 'allUnChecked'
                    localStorage.removeItem('checkbox_' + orderItemId);
                    localStorage.removeItem('bulkAllotCheck_' + procurmentOrderId);
                }
            }
        });

        console.log(itemData);
        updateOrderItemDetails(procurmentOrderId, null, null, null, itemData, bulkAllotCheck);
    });


    // update order items details when click on checbox in grid
    function updateOrderItemDetails(procurmentOrderId, orderItemId, isChecked, allotQuantityInput, itemData, bulkAllotCheck) {

        $.ajax({
            url: site.base_url + "Production_Unit_New/updateOrder",
            method: 'GET',
            data: {
                procurmentOrderId: procurmentOrderId,
                orderItemId: orderItemId,
                checkbox: isChecked,
                allotQuantityInput: allotQuantityInput,
                itemData: itemData,
                bulkAllotCheck: bulkAllotCheck
            },
            dataType: 'json',
            success: function (response) {
                var updateOrderItemDetails = response;

                // Clear the old data from session storage
                // sessionStorage.removeItem('OrderItems_' + procurmentOrderId);
                var procurmentOrderId = localStorage.getItem('procurmentOrderId');
                // Store the updated order items data in sessionStorage
                sessionStorage.setItem('OrderItems_' + procurmentOrderId, JSON.stringify(updateOrderItemDetails));
                loadOrderItems(procurmentOrderId);

            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });

    }
    //======================================== Complete Order Button Functionality ===============================================================
    $('#completeOrder').click(function () {

        var completeOrderFlag = "CompleteOrder";  // set flag for complete order
        procurmentOrderId = localStorage.getItem('procurmentOrderId');
        var procurementOrderRefNo = localStorage.getItem('procurement_order_ref_no_' + procurmentOrderId); // get procurement_order_ref_no for specific order in session

        var confirmation = confirm("Are you sure you want to complete this order? Ref No: " + procurementOrderRefNo);
        if (confirmation) {
            updateOrderDetails(procurmentOrderId, null, completeOrderFlag); // update status when click on complete order button
            disableElements();
        } else {
            return;
        }
    });
});

//======================================== Show Note for specific orders ===============================================================
function showDiv(orderNote) {
    // Set the note content inside the modal, using innerHTML to allow HTML formatting
    document.getElementById('noteContent').innerHTML = decodeURIComponent(orderNote); // Set formatted note
    document.getElementById('welcomeDiv100').style.display = "block"; // Show modal
}

function hideDiv() {
    document.getElementById('welcomeDiv100').style.display = "none"; // Hide modal
}

//======================================== Calculate Allot Quantity ===============================================================

function calculateQuantity(Items) {
    if (Items.order_status = 'Locked') {
        // var allot_quantity = (Items.order_quantity < Items.stock_quantity) ? Items.order_quantity : Items.stock_quantity;

        var allot_quantity = Math.min(Items.order_quantity, Items.stock_quantity);
        // var allot_quantity = min(Items.order_quantity, Items.stock_quantity);
    } else if (Items.item_status == 'Completed' || Items.item_status == 'partially_completed') {

        var allot_quantity = Items.allot_quantity;   // For complete order items
    } else {

        var allot_quantity = '';
    }
    return { allot_quantity: allot_quantity };

}

//////////////////////////////////////////////////////// Delivery Time ////////////////////////////////////////////////////
var countdownInterval;
var deliveryTime;

function updateRequestAgeClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    var timeString = hours + ':' + minutes + ':' + seconds;

    document.getElementById('currentTime1').textContent = timeString;
}

function startCountdown() {
    clearInterval(countdownInterval);

    countdownInterval = setInterval(function () {
        var currentTime = new Date();
        var timeDifference = deliveryTime.getTime() - currentTime.getTime();

        if (timeDifference <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('currentTime2').textContent = '00:00:00';
            return;
        }

        var hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        var countdownString = hours + ':' + minutes + ':' + seconds;

        document.getElementById('currentTime2').textContent = countdownString;
    }, 1000);
}

// Function to set the hardcoded delivery time and start the countdown
function setHardcodedDeliveryTime(plantime) {
    // var hardcodedTime = (plantime !== undefined && plantime !== null) ? plantime : 
    hardcodedTime = plantime !== undefined ? plantime : "00:00:00";
    var [hours, minutes, seconds] = hardcodedTime.split(':');
    deliveryTime = new Date();
    deliveryTime.setHours(parseInt(hours));
    deliveryTime.setMinutes(parseInt(minutes));
    deliveryTime.setSeconds(parseInt(seconds));

    startCountdown();
}

function toggleDeliveryTimeInput() {
    document.getElementById('setButton').style.display = 'none';
    document.getElementById('timeInputContainer').style.display = 'block';
    document.getElementById('timeDisplayContainer').style.display = 'none';
}

// Function to save the delivery time and start the countdown timer
function saveDeliveryTime() {
    var deliveryTimeInput = document.getElementById('deliveryTimeInput').value;

    if (deliveryTimeInput) {
        var [hours, minutes] = deliveryTimeInput.split(':');
        deliveryTime = new Date();
        deliveryTime.setHours(parseInt(hours));
        deliveryTime.setMinutes(parseInt(minutes));
        deliveryTime.setSeconds(0);

        // Extract and format the date and time components
        var year = deliveryTime.getFullYear();
        var month = ('0' + (deliveryTime.getMonth() + 1)).slice(-2);
        var day = ('0' + deliveryTime.getDate()).slice(-2);
        var hour = ('0' + deliveryTime.getHours()).slice(-2);
        var minute = ('0' + deliveryTime.getMinutes()).slice(-2);
        var second = ('0' + deliveryTime.getSeconds()).slice(-2);

        // Combine into the desired format yyyy-mm-dd hh:mm:ss
        var deliveryDateTime = `${year}-${month}-${day} ${hour}:${minute}:${second}`;

        // Hide the input field and show the countdown timer
        document.getElementById('timeInputContainer').style.display = 'none';
        document.getElementById('timeDisplayContainer').style.display = 'block';
        document.getElementById('setButton').style.display = 'block';

        // Start the countdown timer
        startCountdown();

    }
    setTimeToDelivery(deliveryDateTime); // upadte delivery time for orders
}

// upadte delivery time for orders
function setTimeToDelivery(deliveryDateTime) {
    var procurmentRefNo = localStorage.getItem('procurmentRefId');
    var procurmentOrderId = localStorage.getItem('procurmentOrderId');

    $.ajax({
        // url: site.base_url + "Production_Unit/getProcurementRefrenceNo",
        url: site.base_url + "Production_Unit_New/updateOrder",

        method: 'GET',
        data: {
            procurmentRefNo: procurmentRefNo,
            procurmentOrderId: procurmentOrderId,
            deliveryDateTime: deliveryDateTime
        },
        dataType: 'json',
        success: function (response) {


        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
        }
    });
}

// Function to cancel setting the delivery time
function cancelSetDeliveryTime() {
    // Hide the input field and show the countdown timer without changing anything
    document.getElementById('timeInputContainer').style.display = 'none';
    document.getElementById('timeDisplayContainer').style.display = 'block';
    document.getElementById('setButton').style.display = 'block';
}

// Update the "Request Age" clock every second
setInterval(updateRequestAgeClock, 1000);

var orderTime; // Declare the global variable
var formattedDifference; // Initialize formattedDifference

function setOrderAge(orderAgeStr) {
    orderTime = new Date(orderAgeStr); // Parse the date and time from orderAgeStr
}

// Function to calculate and display the time increment
function calculateAndDisplayIncrement() {
    if (orderTime) {
        var now = new Date(); // Current time
        var differenceMs = now.getTime() - orderTime.getTime(); // Calculate elapsed time in milliseconds

        // Calculate the difference in hours, minutes, and seconds
        var seconds = Math.floor(differenceMs / 1000);
        var hours = Math.floor(seconds / 3600);
        seconds %= 3600;
        var minutes = Math.floor(seconds / 60);
        seconds %= 60;

        // Format the time difference as hh:mm:ss
        if (production_order_age == 1) { // If "shows in days" is selected
            var days = Math.floor(differenceMs / (1000 * 60 * 60 * 24));
            var hours = Math.floor((differenceMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((differenceMs % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((differenceMs % (1000 * 60)) / 1000);
            formattedDifference = '';
            formattedDifference += String(days).padStart(2, '0') + ':';
            formattedDifference += String(hours).padStart(2, '0') + ':';
            formattedDifference += String(minutes).padStart(2, '0') + ':';
            formattedDifference += String(seconds).padStart(2, '0') ;
        } else {
            formattedDifference = String(hours).padStart(2, '0') + ':' + 
                                  String(minutes).padStart(2, '0') + ':' + 
                                  String(seconds).padStart(2, '0');

        }
        // Update the display
        $('#currentTimes1').text(formattedDifference);
    }
}

// function setOrderAge(orderAgeStr) {
//     var timeStr = orderAgeStr.split(' ')[1]; // Extract time part from input
//     var orderTimeParts = timeStr.split(':');
//     var orderHours = parseInt(orderTimeParts[0], 10);
//     var orderMinutes = parseInt(orderTimeParts[1], 10);
//     var orderSeconds = parseInt(orderTimeParts[2], 10);

//     // Initialize orderTime with today's date and the specified time
//     orderTime = new Date();
//     orderTime.setHours(orderHours, orderMinutes, orderSeconds, 0);

//     // Initialize totalSeconds based on the difference between current time and orderTime
//     var now = new Date();
//     var differenceMs = now.getTime() - orderTime.getTime(); // Calculate difference in milliseconds
//     totalSeconds = Math.floor(differenceMs / 1000); // Convert milliseconds to seconds
// }

// //Function to calculate and display the time increment
// function calculateAndDisplayIncrement() {
//     if (orderTime) {
//         var now = new Date();
//         var differenceMs = now.getTime() - orderTime.getTime(); // Calculate elapsed time

//         var seconds = Math.floor(differenceMs / 1000);
//         var hours = Math.floor(seconds / 3600);
//         seconds %= 3600;
//         var minutes = Math.floor(seconds / 60);
//         seconds %= 60;

//         // Format the incremented time
//         formattedDifference = ('0' + hours).slice(-2) + ':' +
//             ('0' + minutes).slice(-2) + ':' +
//             ('0' + seconds).slice(-2);

//         // Update the display
//         $('#currentTimes1').text(formattedDifference);
//     }
// }

// Set interval to update the display every second
setInterval(calculateAndDisplayIncrement, 1000);

function timeToSeconds(timeStr) {
    const [hours, minutes, seconds] = timeStr.split(':').map(Number);
    return hours * 3600 + minutes * 60 + seconds;
}

function formatTime(seconds) {
    const hours = Math.floor(Math.abs(seconds) / 3600);
    const minutes = Math.floor((Math.abs(seconds) % 3600) / 60);
    const secs = Math.abs(seconds) % 60;

    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}


var totalSeconds = timeToSeconds(formattedDifference);

function updateTime() {
    if (totalSeconds !== undefined) {
    totalSeconds += 1;
    const formattedTime = formatTime(totalSeconds);
    $('#currentTimes1').text(formattedTime);
}
}

// Update the timer every second
setInterval(updateTime, 1000);

