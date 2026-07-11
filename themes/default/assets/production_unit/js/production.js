$(document).ready(function () {

    //======================================== Filter By Timestamp ===============================================================
    $('#filterByTimestamp').on('click', function () {
        $('#timestampCheckboxList').show();
        $('#searchForm').hide();
    });
    $('#Oldest').on('click', function () {
        if ($(this).prop('checked')) {
            Filter = 'Oldest';
            procurmentOrderRefrenceNo(null, null, null, null, null, null, null, null, Filter);
            $('#Newest').prop('checked', false);
        }
    });
    $('#Newest').on('click', function () {
        if ($(this).prop('checked')) {
            Filter = 'Newest';
            procurmentOrderRefrenceNo(null, null, null, null, null, null, null, null, Filter);
            $('#Oldest').prop('checked', false);
        }
    });
    //======================================== Items Status ===============================================================
    $('.nav-link').click(function () {
        var locationName = localStorage.getItem('locationName');
        if (locationName || menuClicked == true) {

            var Itemstatus = $(this).attr('values');
            // var locationName = localStorage.getItem('result');
            var procurmentRefId = localStorage.getItem('procurmentRefId');
            procurmentOrderRefrenceNo(locationName, null, procurmentRefId, Itemstatus);
        } else {
            alert("Please Applied Location Filter")
        }
    });

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
        procurmentOrderRefrenceNo(locationName);
    });
    $('#searchInput').select2({
        placeholder: 'Select Location Name',
        allowClear: true // Allow clearing the selection
    });
    $('#filterOutlets').on('click', function () {
        $('#searchForm').show();// Show the search form
        $('#timestampCheckboxList').hide();
    });

    //======================================== Procurment Order Ref No List  ===============================================================
    $('.mainmenu').on('click', '.procurementItem', function () {
        var procurmentId = $(this).attr('id');
        var procurmentRefId = procurmentId.split('_')[1];
        var locationData = $(this).data('location');    //Location Name And location Code 
        var result = locationData.split(" - ")[0];
        var locationName = localStorage.getItem('locationName');
        if (procurmentRefId || result) {
            menuClicked = true;

            localStorage.setItem('result', result);
            localStorage.setItem('procurmentRefId', procurmentRefId);
            sessionStorage.setItem('procurmentRefId', procurmentRefId);
            var OrderStatus = sessionStorage.getItem('OrderStatus_' + procurmentRefId);
            procurmentOrderRefrenceNo(locationName ? result : null, null, procurmentRefId);
            getReqLocationName(locationData);
            $('#order_status').text((OrderStatus)); // show order status in middle section
        }
    });

    //======================================== Request from Outlet  ===============================================================
    function getReqLocationName(locationData) {
        $('#locationName').text((locationData));
    }

    //======================================== Get Procurment order data  ===============================================================
    var globalProcurmentDetails;
    function procurmentOrderRefrenceNo(location = null, Term = null, procurmentRefNo = null, Itemstatus = null, itemId = null, quantity = null, itemData = null, checkbox = null, Filter = null, Complete_unit_order = null) {

        $.ajax({
            url: site.base_url + "Production_Unit/getProcurementRefrenceNo",
            method: 'GET',
            data: {
                procurmentRefNo: procurmentRefNo,
                location: location,
                search: Term,
                status: Itemstatus,
                itemId: itemId,
                quantity: quantity,
                itemData: itemData,
                checkbox: checkbox,
                Filter: Filter,
                Complete_unit_order: Complete_unit_order
            },
            dataType: 'json',
            success: function (response) {
                globalProcurmentDetails = response; //for complete order button functionality
                procurmentRefrenceNo(response);
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }

    //======================================== Allot quantity for order items  ===============================================================

    // allot quantity for bulk order item
    $('#selectAll').on('click', function () {

        var allChecked = $(this).is(':checked');
        var checkbox = allChecked ? 1 : 0;
        var itemData = [];

        $('#tablebody tr').each(function () {
            var checkbox = $(this).find('input[type="checkbox"]');
            var quantityInput = $(this).find('input[name="quantity[]"]');
            var itemId = checkbox.val();
            var quantity = quantityInput.val();

            if (checkbox) {
                checkbox.prop('checked', true);
                quantityInput.prop('disabled', true);
                localStorage.setItem('checkbox_' + itemId, checkbox);
                localStorage.setItem('quantity_' + itemId, quantity);
            } else {
                checkbox.prop('checked', false);
                quantityInput.prop('disabled', false);
                localStorage.removeItem('checkbox_' + itemId, checkbox);
                localStorage.removeItem('quantity_' + itemId);
            }
            itemData.push({
                itemId: itemId,
                quantity: quantity
            });
        });
        var procurmentRefNo = localStorage.getItem('procurmentRefId');

        procurmentOrderRefrenceNo(null, null, procurmentRefNo, null, null, null, itemData, checkbox);
        $('#tablebody input[type="checkbox"]').each(function () {
            var itemId = $(this).val();
            if (checkbox) {
                localStorage.getItem('checkbox_' + itemId); // Bulk operation selected: All checkboxes will be selected
            } else {
                localStorage.removeItem('checkbox_' + itemId);  // Bulk operation removed: All checkboxes will be deselected.
            }
        });
    });

    // allot quantity for specific order item
    $('#tablebody').on('click', 'input[type="checkbox"]', function () {
        var isChecked = $(this).is(':checked');
        var checkbox = isChecked ? 1 : 0;
        var itemId = null;
        var quantity = null;
        var quantityInput = $(this).closest('tr').find('input[name="quantity[]"]');
        var isCheckedItem = $('#toggleSwitch').prop('checked');
        var AllItems = isCheckedItem ? 'true' : 'false';

        if (checkbox === 1) {
            // $('#completeOrder').removeClass('disabled').prop('disabled', false);
            quantityInput.prop('disabled', true); // Disable the quantity input
            itemId = $(this).val();
            quantity = $(this).closest('tr').find('input[name="quantity[]"]').val(); //allot quantity
            localStorage.setItem('checkbox_' + itemId, checkbox);
            localStorage.setItem('quantity_' + itemId, quantity);
            var procurmentRefNo = localStorage.getItem('procurmentRefId');
            $(this).closest('tr').find('input[name="quantity[]"]').prop('disabled', true); // Disable the quantity input
            procurmentOrderRefrenceNo(null, null, procurmentRefNo, null, itemId, quantity, null, checkbox);
            if (isCheckedItem) {
                ViewAllOrderItems(procurmentRefNo, AllItems);// for view full orders
                $('#completeOrder').show();
            }

        } else {
            // $('#completeOrder').removeClass('disabled').prop('disabled', true).css('opacity', 0.5);
            quantity = $(this).closest('tr').find('input[name="quantity[]"]').val(); //allot quantity
            itemId = $(this).val();
            var procurmentRefNo = localStorage.getItem('procurmentRefId');
            procurmentOrderRefrenceNo(null, null, procurmentRefNo, null, itemId, quantity, null, checkbox);
            quantityInput.prop('disabled', false); // Enable the quantity input
            localStorage.removeItem('checkbox_' + itemId);
            localStorage.removeItem('quantity_' + itemId);
            if (isCheckedItem) {
                ViewAllOrderItems(procurmentRefNo, AllItems); // for view full orders
                $('#completeOrder').show();
            }
        }
    });

    //======================================== Procurment Order Ref No List  ===============================================================

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



    function procurmentRefrenceNo(refrenceNo) {
        // Clear the current menu items
        $('.mainmenu').empty();

        // Separate completed, open, and locked orders
        var completedOrders = [];
        var openLockedOrders = [];

        // Iterate through the reference numbers to populate the menu
        refrenceNo.forEach(function (proRefrenceNo) {
            var requests = proRefrenceNo.refrenceNo;
            requests.forEach(function (request) {
                var locationData = request.location_name + ' - ' + request.location_code;
                var plannedDeliveryDate = request.planned_delivery_date;
                var note = request.note;
                var infoicon = '';
                var OrderStatus = request.status;
                sessionStorage.setItem('OrderStatus_' + request.itemId, OrderStatus);

                // Check if the note is not blank, then display the info icon
                if (note && note.trim() !== '') {
                    var note = note.replace(/<[^>]+>/g, ''); // Remove HTML tags from note
                    infoicon = '<i class="fa fa-info-circle info-icon" onclick="showDiv(\'' + note + '\')"></i>';
                }

                // Create the list item with procurement details
                var li = $('<li class="procurementItem" id="procurmentRefNo_' + request.itemId + '" data-location="' + locationData + '">' +
                    request.procurement_order_ref_no + '<br>' +
                    request.order_creation_date + infoicon + // Append the info icon here
                    '</li>');

                // Check if this item was previously locked and add the lock icon if it was
                if (OrderStatus.toLowerCase() === 'locked') {
                    li.append('<i class="fa fa-lock info-icon1"></i>');
                }

                // Append the list item to the main menu
                $('.mainmenu').append(li);

                // Update the order status color
                updateOrderStatusColor1(OrderStatus, li);

                // Add the list item to the appropriate array
                if (OrderStatus.toLowerCase() === 'completed') {
                    completedOrders.push(li);
                } else {
                    openLockedOrders.push(li);
                }
            });
        });

        // Append open and locked orders first, then completed orders
        openLockedOrders.forEach(function (li) {
            $('.mainmenu').append(li);
        });
        completedOrders.forEach(function (li) {
            $('.mainmenu').append(li);
        });

        // Retrieve the active item from local storage
        var activeItemId = localStorage.getItem('activeItemId');
        if (!activeItemId) {
            // If no active item is set, set the first item as active and store it in local storage
            activeItemId = $('.procurementItem:first').attr('id');
            localStorage.setItem('activeItemId', activeItemId);
        }

        // Set the style for the active item
        $('#' + activeItemId).css({
            'color': '#fff',
            'background-color': '#039be5'
        });

        // Update the lock button state based on the active item's lock status
        if (localStorage.getItem(activeItemId + '_isLocked') === 'true') {
            $('#locked').addClass('locked');
            $('#locked .button-text').text('🔒 Locked');
        } else {
            $('#locked').removeClass('locked');
            $('#locked .button-text').text('Lock Order');
        }

        // Add click event to change the active item and update the lock button state
        $('.procurementItem').click(function () {
            // Reset the style of all items to default
            $('.procurementItem').css({
                'color': '', // Reset to default text color
                'background-color': '' // Reset to default background color
            });

            // Change the style of the clicked item
            $(this).css({
                'color': '#fff',
                'background-color': '#039be5'
            });

            // Store the ID of the clicked item in local storage
            localStorage.setItem('activeItemId', $(this).attr('id'));

            // Update the lock button state based on the clicked item's lock status
            if (localStorage.getItem($(this).attr('id') + '_isLocked') === 'true') {
                $('#locked').addClass('locked');
                $('#locked .button-text').text('🔒 Locked');
            } else {
                $('#locked').removeClass('locked');
                $('#locked .button-text').text('Lock Order');
            }

            // Enable the elements
            enableElements();
        });

        // Call loadItems function to load the items
        loadItems(refrenceNo);
    }

    function showDiv(note) {
        // Example implementation to show a div with the note content
        alert(note);
    }


    function updateOrderStatusColor1(OrderStatus, li) {
        switch (OrderStatus.toLowerCase()) {
            case 'completed':
                li.css({
                    'background-color': 'gray', // Set background color to gray for completed orders
                    'color': '#fff' // Set text color to white for completed orders
                });
                break;
            case 'locked':
                // Do nothing, the locked icon is already added in the main function
                break;
            default:
                // Do nothing for other statuses
                break;
        }
    }



    // Add this script at the end of your existing JavaScript code
    $(document).ready(function () {
        // Remove the active item ID from local storage
        localStorage.removeItem('activeItemId');

        // Apply the CSS styles to the first item
        $('.procurementItem:first').css({
            'color': '#fff',
            'background-color': '#039be5'
        });
    });


    //======================================== Procurment Order Details ===============================================================

    function loadItems(procurmentDetails) {

        $('#tablebody').empty();
        localStorage.removeItem('orderStatus');
        var orderStatus = null;

        procurmentDetails.forEach(function (orders_items) {
            var orderitemsdata = orders_items.procurmentDetails;
            var location_data  = orders_items.location_data;
            var locationId     = location_data.warehouse_id;

            orderitemsdata.forEach(function (orders_item) {

                if (!orderStatus) {
                    orderStatus = orders_item.order_status;
                    localStorage.setItem('orderStatus', orderStatus);
                }
                
                var calculatedValues = calculate(orders_item);
                var build_quantity = calculatedValues.build_quantity;
                var isChecked      = localStorage.getItem('checkbox_' + orders_item.itemId);
                var quantityInput  = localStorage.getItem('quantity_' + orders_item.itemId);
                // var allot_quantity = quantityInput ? quantityInput : calculatedValues.allot_quantity;
                var isDisabled     =  $('#locked').prop('disabled');
                var isEditable     = locationId.includes(orders_item.production_unit_id);

              
                if(orders_item.item_status == 'Completed' || orders_item.item_status == 'partially_completed'){
                    var allot_quantity = orders_item.allot_quantity;   // For complete order items
                    isChecked = 'checked';
                }else{
                    var allot_quantity = quantityInput ? quantityInput : calculatedValues.allot_quantity;
                }

                var row = $("<tr class='text-center red'>" +
                    "<td class='cen-set'><p class='circle-set'>" + orders_item.order_quantity + "</p></td>" +
                    "<td>" + orders_item.product_name + "</td>" +
                    "<td>" + orders_item.stock_quantity + "</td>" +
                    "<td><input type='number' class='allot-set' name='quantity[]' style='width:20%; text-align:right;' value='" + parseFloat(allot_quantity) + "' " + (isDisabled  ? 'disabled' : '') + " onchange='checkQty(this, " + orders_item.order_quantity + ", \"" + orders_item.itemId + "\")'>" +
                    "<span class='ml-2'><input type='checkbox' class='large-checkbox' name='allot[]' id='checkbox_" + orders_item.itemId + "' value='" + orders_item.itemId + "' " + (isChecked ? 'checked' : '') + (isDisabled ||  !isEditable  ? 'disabled' : '') + "></span></td>" +
                    "</tr>");

                    if (orders_item.order_status =='Open' || orders_item.order_status =='Completed' || orders_item.order_status =='partially_completed' ) {
                        row.find('.allot-set').prop('disabled', true);
                        row.find('.large-checkbox').prop('disabled', true);
                        // $('.sty-set').removeClass('disabled').prop('disabled', true).css('opacity', 0.5);
                    
                    }
                    if (orders_item.order_status =='Locked' || !isEditable) {
                        if(!isEditable){
                            row.find('.allot-set').prop('disabled', true);
                            row.find('.large-checkbox').prop('disabled', true);
                        }else{
                            if(orders_item.item_status == 'Completed' || orders_item.item_status == 'partially_completed' || orders_item.item_status == 'Pending'){
                                row.find('.allot-set').prop('disabled', true);
                                row.find('.large-checkbox').prop('disabled', true);
                            }else{
                                row.find('.allot-set').prop('disabled', false);
                                row.find('.large-checkbox').prop('disabled', false);
                            }
                        }
                    }

                $("#tablebody").append(row);
                $('#productionUnit').text((orders_item.production_unit_name));
                $('#order_status').text((orders_item.status));

                // Add event listener to the checkbox
                var checkbox = row.find('input.large-checkbox');
                checkbox.on('change', function () {
                    var inputValue = parseFloat(row.find('input.allot-set').val());
                    var stockQuantity = parseFloat(orders_item.stock_quantity);
                    var orderQuantity = parseFloat(orders_item.order_quantity);

                    // Check if input value exceeds stock quantity or is negative
                    if (inputValue > stockQuantity || inputValue < 0) {
                        $(this).prop('checked', false);
                        if (inputValue > stockQuantity) {
                            alert('Allocation quantity cannot exceed stock quantity.');
                        } else {
                            alert('Input value cannot be negative');
                        }
                        return;
                    }

                    // Continue with checkbox state change and background color logic
                    if ($(this).is(':checked')) {
                        if (inputValue === orderQuantity) {
                            row.css('background', 'linear-gradient(90deg, #00C314, rgba(0, 195, 20, 0))'); // Green
                        // } else if (inputValue > orderQuantity || stockQuantity === 0) {
                        } else if (inputValue > orderQuantity) {

                            row.css('background', 'linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))'); // Red
                        }else if(inputValue == 0){
                            row.css('background', 'linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))'); // Red
                        }
                    } else {
                        row.css('background', ''); // Reset background color if unchecked
                    }

                    // Save the checkbox state to localStorage
                    localStorage.setItem('checkbox_' + orders_item.itemId, $(this).is(':checked') ? 'checked' : '');
                });


                // Add event listener to the input field to prevent exceeding stock value and negative values
                var inputField = row.find('input.allot-set');
                inputField.on('input', function () {
                    var inputValue = parseFloat($(this).val());
                    var stockQuantity = parseFloat(orders_item.stock_quantity);
                    var orderQuantity = parseFloat(orders_item.order_quantity);

                    // Prevent negative values
                    if (inputValue < 0) {
                        $(this).val(0);
                        inputValue = 0; // Reset inputValue to 0
                        alert('Input value cannot be negative');
                    }

                    // Prevent exceeding stock quantity
                    if (inputValue > stockQuantity) {
                        $(this).val(stockQuantity);
                        alert('Input value cannot exceed stock quantity');
                    } else if (inputValue > orderQuantity) {
                        // Prevent exceeding order quantity
                        $(this).val(orderQuantity);
                        alert('Input value cannot exceed order quantity');
                    }

                    // Save the input value to localStorage
                    localStorage.setItem('quantity_' + orders_item.itemId, $(this).val());
                });

                // Initial check for the checkbox state
                if (isChecked) {
                    var inputValue = parseFloat(row.find('input.allot-set').val());
                    var stockQuantity = parseFloat(orders_item.stock_quantity);
                    var orderQuantity = parseFloat(orders_item.order_quantity);

                    if (inputValue === orderQuantity) {
                        row.css('background', 'linear-gradient(90deg, #00C314, rgba(0, 195, 20, 0))'); // Green
                    } else if (inputValue > orderQuantity || stockQuantity === 0) {
                        row.css('background', 'linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))'); // Red
                    } else if (orderQuantity > inputValue && stockQuantity !== 0 && inputValue !== 0) {
                        row.css('background', 'linear-gradient(90deg, #FFFF00, rgba(255, 255, 0, 0))'); // Yellow
                    }
                }

            });
        });
        updateOrderStatusColor();
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

    procurmentOrderRefrenceNo();

    //======================================== Locked order Functionality ===============================================================

    function lockOrder() {
        // Set the global Locked variable
        Locked = true;

        // Retrieve necessary data
        var Itemstatus = 'Locked';
        var procurmentRefNo = sessionStorage.getItem('procurmentRefId');
        var isChecked = $('#toggleSwitch').prop('checked');
        var AllItems = isChecked ? 'true' : 'false';

        // Update the order status in the DOM
        $('#order_status').text(Itemstatus);

        // Call the function to update order status
        procurmentOrderRefrenceNo(null, null, procurmentRefNo, Itemstatus, null, null, null, null);

        // Check all checkboxes if not already checked
        var $checkboxes = $('#tablebody').find('input[type="checkbox"]');
        var checkedCount = $checkboxes.filter(':checked').length;
        if (checkedCount < $checkboxes.length) {
            $checkboxes.prop('checked', true).trigger('click');
            $('#selectAll').trigger('click');
        }

        // Show completeOrder button if toggleSwitch is checked
        if (isChecked) {
            var status = Itemstatus;
            ViewAllOrderItems(procurmentRefNo, AllItems, status);
            $('#completeOrder').show();
        }

        // Update the lock button's CSS
        $('#locked').css({
            'background-color': '#ff0000',
            'color': '#ffffff',
            'border': '1px solid #ff0000'
        });

        // Add Font Awesome lock icon to the active li with id="procurmentRefNo_"
        $('li#procurmentRefNo_' + procurmentRefNo).addClass('active').append('<i class="fa fa-lock info-icon1"></i>');
    }

    // Attach click event handler to the lock button
    $('#locked').click(function () {
        var orderStatus = localStorage.getItem('orderStatus');
        // $('#order_status').text(orderStatus);

        if(orderStatus =='Locked'){
            return
        }else{
            // Enable all checkboxes and input fields
            $('#tablebody').find('input.allot-set, input.large-checkbox').prop('disabled', false);
            lockOrder();
        }
    });



    //======================================== Complete Order Button Functionality ===============================================================
    $('#completeOrder').click(function () {

        $('#tablebody').find('input.allot-set, input.large-checkbox').prop('disabled', true);
        var Itemstatus = "Committed";
        var isCheckedItem = $('#toggleSwitch').prop('checked');
        var AllItems = isCheckedItem ? 'true' : 'false';
        console.log(globalProcurmentDetails);

        var orderConfirmed = false;
        globalProcurmentDetails.forEach(function (Procurment_Details) {
            var item_data = Procurment_Details.procurmentDetails;

            item_data.forEach(function (items) {
                var procurement_order_ref_no = items.procurement_order_ref_no;
                var procurmentRefNo = items.procurement_orders_id;

                if (!orderConfirmed && confirm("Are you sure you want to complete the order with procurement order reference number: " + procurement_order_ref_no + "?")) {
                    orderConfirmed = true; // Set flag to true once confirmed

                    Complete_unit_order = true;
                    procurmentOrderRefrenceNo(null, null, procurmentRefNo, Itemstatus, null, null, null, null, null, Complete_unit_order);

                    // Disable the input and checkbox elements inside the <td>
                    $('#procurmentRefNo_' + items.itemId + ' input').prop('disabled', true);
                    // Disable the "Lock Order" button
                    $('#locked').addClass('disabled').prop('disabled', true);
                    $('#completeOrder').addClass('disabled').prop('disabled', true);
                    $('.large-checkbox').addClass('disabled').prop('disabled', true);

                    // $("#toggleSwitch").prop("disabled", true).closest("div").addClass("disabled");
                    // $(".mainmenu").prop("disabled", true).closest("div").addClass("disabled");
                    if (isCheckedItem) {
                        ViewAllOrderItems(procurmentRefNo, AllItems);// for view full orders
                        $('#completeOrder').show();
                    }
                } else if (!orderConfirmed) {
                    Complete_unit_order = false;
                    console.log("Order completion cancelled.");
                    orderConfirmed = true;
                }
            });
        });
    });


    function enableElements() {
        $('#locked').removeClass('disabled').prop('disabled', false);
        $('#completeOrder').removeClass('disabled').prop('disabled', false);
        $('.large-checkbox').removeClass('disabled').prop('disabled', false);
        $('.input-element').removeClass('disabled').prop('disabled', false);
        $('.checkbox-element').removeClass('disabled').prop('disabled', false);
    }



    $(document).ready(function () {
        var completeOrderStatus = localStorage.getItem('Complete_unit_order');
        if (completeOrderStatus === 'true') {
            // Remove lock icon if order is completed
            $('.procurementItem .fa-lock').remove();
        }
    });
    //======================================== View full order toggle button ===============================================================

    $('#toggleSwitch').click(function () {
        var isChecked = $(this).prop('checked');
        var AllItems = isChecked ? 'true' : 'false';
        var procurmentRefNo = localStorage.getItem('procurmentRefId');
        // var div = $('#completeOrder');
        if (isChecked) {
            ViewAllOrderItems(procurmentRefNo, AllItems);
            div.show();
        } else {
            $('#tablebody').empty();
            ViewAllOrderItems(procurmentRefNo);
            div.hide();
        }
        // $('#completeOrder').addClass('disabled').prop('disabled', true);
    });
    function ViewAllOrderItems(procurmentRefNo, AllItems, status) {
        $.ajax({
            url: site.base_url + "Production_Unit/getProcurementRefrenceNo",
            method: 'GET',
            data: {
                procurmentRefNo: procurmentRefNo,
                AllItems: AllItems,
                status: status
            },
            dataType: 'json',
            success: function (response) {
                loadItems(response);
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }
});
//======================================== check allot quantity input wise disable checkbox functionality  ===============================================================

// checkQty function in grid
function checkQty(inputElement, orderQuantity, itemId) {
    var quantityInput = parseFloat(inputElement.value);
    var checkbox = document.getElementById('checkbox_' + itemId);
    if (quantityInput > parseFloat(orderQuantity)) {
        checkbox.disabled = true;
    } else {
        checkbox.disabled = false;
    }
    checkAllRows();
}

// checkQty function in grid for bulk action 
document.getElementById('selectAll').addEventListener('change', function () {
    var allInputElements = document.querySelectorAll('.allot-set');
    allInputElements.forEach(function (inputElement) {
        var orderQuantity = inputElement.closest('tr').querySelector('.circle-set').innerText;
        var itemId = inputElement.closest('tr').querySelector('.large-checkbox').value;
        checkQty(inputElement, orderQuantity, itemId);
    });
});

// Function to check all rows and update the "Select All" checkbox
function checkAllRows() {
    var allInputElements = document.querySelectorAll('.allot-set');
    var selectAllCheckbox = document.getElementById('selectAll');
    var DisableAll = false;

    allInputElements.forEach(function (inputElement) {
        var orderQuantity = inputElement.closest('tr').querySelector('.circle-set').innerText;
        var quantityInput = parseFloat(inputElement.value);
        if (quantityInput > parseFloat(orderQuantity)) {
            DisableAll = true;
        }
    });
    if (DisableAll) {
        selectAllCheckbox.disabled = true;
    } else {
        selectAllCheckbox.disabled = false;
    }
}
//======================================== Time to Delivery ===============================================================

var countdownInterval;
var deliveryTime;

// Function to update the "Request Age" clock
function updateRequestAgeClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    // Pad single digit minutes and seconds with a leading zero
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    var timeString = hours + ':' + minutes + ':' + seconds;

    // Update "Request Age" with current time
    document.getElementById('currentTime1').textContent = timeString;
}

// Function to start the countdown timer
function startCountdown() {
    clearInterval(countdownInterval); // Clear any existing interval to avoid multiple timers

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

        // Pad single digit minutes and seconds with a leading zero
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        var countdownString = hours + ':' + minutes + ':' + seconds;

        // Update "Time to Delivery" with countdown time
        document.getElementById('currentTime2').textContent = countdownString;
    }, 1000);
}

// Function to show the input field for setting the delivery time
function toggleDeliveryTimeInput() {
    // Hide the set button and show the input field
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
    $.ajax({
        url: site.base_url + "Production_Unit/getProcurementRefrenceNo",
        method: 'GET',
        data: {
            procurmentRefNo: procurmentRefNo,
            deliveryDateTime: deliveryDateTime
        },
        dataType: 'json',
        success: function (response) {
            procurmentRefrenceNo(response);
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


//======================================== Calculate Quantity ===============================================================

var Locked = false; // Flag to track if the locked button is clicked
function calculate(orders_item) {

    // var build_quantity    = Math.abs(parseFloat(orders_item.request_quantity - orders_item.stock_quantity));
    var build_quantity = Math.abs(orders_item.open_order_quantity - orders_item.stock_quantity);
    // var allot_quantity = (orders_item.order_quantity) < (orders_item.stock_quantity) ? (orders_item.order_quantity) : (orders_item.stock_quantity);
    var orderStatus = localStorage.getItem('orderStatus');
    
    // if (Locked) {
    //     var allot_quantity = (orders_item.order_quantity < orders_item.stock_quantity) ? orders_item.order_quantity : orders_item.stock_quantity;
    // } else {
    //     var allot_quantity = '';
    // }
    
    if (orderStatus = 'Locked') {
        var allot_quantity = (orders_item.order_quantity < orders_item.stock_quantity) ? orders_item.order_quantity : orders_item.stock_quantity;
    // alert(allot_quantity)
    // alert(orders_item.order_quantity)
    // alert(orders_item.stock_quantity)

    } else {
        var allot_quantity = '';
    }
    return { allot_quantity: allot_quantity, build_quantity: build_quantity };

}

//======================================== Show Note for specific orders ===============================================================

function showDiv(note) {
    // Set the note content inside the modal
    document.getElementById('noteContent').textContent = note;
    document.getElementById('welcomeDiv100').style.display = "block";
}

function hideDiv() {
    document.getElementById('welcomeDiv100').style.display = "none";
}