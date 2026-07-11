//////////////////////////////////////////////////////// REQUEST DATE FUNCTION ///////////////////////////////////////////////////


// 🚨 Prevent form submission unless explicitly allowed
$(document).on('submit', 'form', function (e) {
    if (!$(this).hasClass('allow-submit')) {
        e.preventDefault();
    }
});

// Get currency symbol from data attribute or use default
var currencySymbol = document.documentElement.getAttribute('data-currency') || 'Rs.';

// Format price with currency symbol
function formatPrice(price) {
    if (price === undefined || price === null || price === '') return currencySymbol + ' 0.00';
    // Remove any existing currency symbols and extra spaces
    var cleanPrice = String(price).replace(/[^0-9.]/g, '');
    // Format with 2 decimal places
    return currencySymbol + ' ' + parseFloat(cleanPrice).toFixed(2);
}

$(document).ready(function () {
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .dynamic-title {
                min-width: 200px;
                white-space: normal;
                word-break: break-word;
            }
            .price-cell {
                text-align: right;
                white-space: nowrap;
            }
            .price-input {
                padding-right: 5px;
                text-align: right;
                background-color: #fff;
            }
            .text-right {
                text-align: right !important;
            }
        `)
        .appendTo('head');
});

$(document).ready(function () {
    if ($('#dynamicTable thead').children().length === 0) {
        $('#dynamicTable thead').html(`
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price (${currencySymbol})</th>
                <th>Actions</th>
            </tr>
        `);
    }
});

$(function () {
    let previousDate = $("#requestDeliveryDate").datepicker("getDate");
    
    $("#requestDeliveryDate").datepicker({
        dateFormat: "MM dd, yy",
        showOn: "focus",
        onSelect: function (dateText, inst) {
            let selectedDate = $(this).datepicker("getDate");
            let currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0);

            if (selectedDate < currentDate) {
                $(this).datepicker("setDate", currentDate);
                alert("Please select a date that is today or later.");
                return;
            }

            let prevDateKey = $.datepicker.formatDate("yy-mm-dd", previousDate);
            let newDateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

            let currentTitle = $("#title").val();
            let currentCart = JSON.parse(localStorage.getItem(`selectedProducts_${prevDateKey}`)) || [];

            // Save current state for previous date
            localStorage.setItem(`title_${prevDateKey}`, currentTitle);
            localStorage.setItem(`selectedProducts_${prevDateKey}`, JSON.stringify(currentCart));

            // PATCH: Deep copy for new date key (fixes cross-date bugs!)
            let deepClonedCart = JSON.parse(JSON.stringify(currentCart));
            localStorage.setItem(`selectedProducts_${newDateKey}`, JSON.stringify(deepClonedCart));

            previousDate = selectedDate;

            loadItems('add_items');
        }
    });

    $("#requestDeliveryDate").datepicker("setDate", new Date());
    $(".input-group-addon").on("click", function () {
        $("#requestDeliveryDate").focus();
    });
});


$(document).ready(function () {
    //////////////////////////////////////////////////////// RESET //////////////////////////////////////////
    localStorage.removeItem('selectedHorizonatlTab');
    $('.categories').show();
    $('#reset').click(function () {
        const originalResetFunction = function() {
            const savedTitle = $('#title').val();
            if (confirm("Are you sure you want to reset? (Title will be kept)")) {
                $('#title').val(savedTitle);
                loadItems('add_items'); 
                showToast("Title preserved - Items unchanged", true);
            }
        };
        if ($('#title').val().trim() !== '') {
            if (confirm("Do you want to clear the title?")) {
                $('#title').val(''); // Clear title
                loadItems('add_items');
                showToast("Title cleared - Items reset", true);
            } else {
                originalResetFunction(); // Use original behavior
            }
        } else {
            originalResetFunction(); // Use original behavior if no title
        }
    });
    
    function showCategories() {
        $('.categoriesList').empty();
        $('.subcategoriesList').empty();
        $('.productList').empty();

        $.each(categories, function (index, category) {
            $('.categoriesList').append('<li class="btn btn-sty category last-category" data-category-id="' + category.category_id + '">' + category.category_name + '<span class="hover-fill"></span></li>');

            $('.categoriesList li:last-child').css({
                'background': 'linear-gradient(to right, #fff 96%, #008E80 4%)',
                'position': 'relative',
                'padding': '10px 20px',
                'margin': '5px 0',
                'color': '#000',
                'font-weight': 'bold',
                'overflow': 'hidden',
                'z-index': '1',
                'font-weight': '300',
            });

            $('.categoriesList li:last-child .hover-fill').css({
                'content': "''",
                'position': 'absolute',
                'top': '0',
                'left': '100%',
                'width': '100%',
                'height': '100%',
                'background': '#008E80',
                'transition': 'transform 0.4s ease-in-out',
                'z-index': '-1',
            });

            $.each(category.subcategories, function (index, subcategory) {
                $('.subcategoriesList').append('<li class="btn btn-sty subcategory" style="display:none;" data-category-id="' + category.category_id + '">' + subcategory.subcategory_name + '</li>');
            });
        });

        $('.categoriesList').on('mouseenter', '.last-category', function () {
            $(this).find('.hover-fill').css({
                'transform': 'translateX(-100%)',
            });
            $(this).css('color', '#fff');
        }).on('mouseleave', '.last-category', function () {
            $(this).find('.hover-fill').css({
                'transform': 'translateX(100%)',
            });
            $(this).css('color', '#000');
        });
    }
    showCategories();
    $('.hand-o-left').click(function () {
        showCategories();
        $('.subcategoriesList').hide();
        $('.categoriesList').show();
        $('.subCatName').empty();
        $('.recent').hide();
    });
    $('.recent').hide();

    $(document).on('click', '.category', function () {
        window.currentCategoryID = $(this).data('category-id');
        window.currentCategoryName = $(this).text().trim();

        $('.catName').text(window.currentCategoryName);
        $('.categoriesList').hide();
        $('.subcategoriesList').show();
        $('.recent').show();

        var $subcategories = $('.subcategory[data-category-id="' + window.currentCategoryID + '"]');
        if ($subcategories.length > 0) {
            $subcategories.show();
        } else {
            getProducts(window.currentCategoryID);
        }
        
        const currentCart = JSON.parse(localStorage.getItem('selectedProducts')) || [];
        localStorage.setItem('selectedProducts', JSON.stringify(currentCart));
    });

    $(document).on('click', '.subcategory', function () {
        var subCategoryID = $(this).data('category-id');
        var subCategoryName = $(this).text().trim();

        window.currentCategoryID = subCategoryID;
        window.currentCategoryName = subCategoryName;

        $('.subCatName').text(subCategoryName);
        $('.recent').show();

        getProducts(subCategoryID);
    });

    function getProducts(CategoryID, SubCategoryID) {
        $.ajax({
            url: site.base_url + "Todays_Special/GetProductsbyCategoriesID",
            method: 'GET',
            data: {
                subcategoryId: SubCategoryID,
                categoriesId: CategoryID,
            },
            dataType: 'json',
            success: function (response) {
                response.forEach(function (subCategorie) {
                    var productDetails = subCategorie.productsByCat;
                    getProductsbyCategories(productDetails, CategoryID);
                });
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }

    function getProductsbyCategories(products, CategoryID) {
    $('.subcategoriesList').hide();
    $('.productList').empty();

    localStorage.setItem('currentCategoryProducts', JSON.stringify(products));

    const selectedDateKey = $.datepicker.formatDate("yy-mm-dd", $("#requestDeliveryDate").datepicker("getDate"));
    const selectedProducts = JSON.parse(localStorage.getItem(`selectedProducts_${selectedDateKey}`)) || [];
    const currentProductNames = selectedProducts.map(p => p.name.trim().toLowerCase());

    products.forEach(function (product) {
        if (product) {
            const isInCart = currentProductNames.includes(product.name.trim().toLowerCase());
            const productItem = $(`
                <li class="btn btn-sty ItemsDetails ${isInCart ? 'already-added' : ''}" 
                    data-product-id="${product.id}"
                    data-product-name="${product.name.trim().toLowerCase()}">
                    ${product.name}
                    <span class="hover-fill"></span>
                </li>
            `);
            $('.productList').append(productItem);
        }
    });

    $('.productList').off('click', '.ItemsDetails').on('click', '.ItemsDetails', function() {
        const productName = $(this).data('product-name');
        const selectedDateKey = $.datepicker.formatDate("yy-mm-dd", $("#requestDeliveryDate").datepicker("getDate"));
        const selectedProducts = JSON.parse(localStorage.getItem(`selectedProducts_${selectedDateKey}`)) || [];

        if (selectedProducts.some(p => p.name.trim().toLowerCase() === productName)) {
            Toastify({
                text: "This product is already in your cart",
                duration: 2000,
                gravity: 'top-right',
                close: true,
                backgroundColor: "#FFA500"
            }).showToast();

            $(this).addClass('duplicate-clicked');
            setTimeout(() => {
                $(this).removeClass('duplicate-clicked');
            }, 500);
            return;
        }

        const productId = $(this).data('product-id');
        const storedRequests = JSON.parse(localStorage.getItem('currentCategoryProducts')) || [];
        const productDetails = storedRequests.find(p => p.id.toString() === productId.toString());

        if (productDetails) {
            // PATCH: clone and REMOVE id for unsaved carts
            const productForCart = {
                ...productDetails,
                category_id: CategoryID || window.currentCategoryID || '',
                category_name: window.currentCategoryName || ''
            };
            delete productForCart.id; // <-- this fixes the unsaved issue
            selectedProducts.push(productForCart);

            localStorage.setItem(`selectedProducts_${selectedDateKey}`, JSON.stringify(selectedProducts));
            loadItems();

            $(this).addClass('already-added');

            Toastify({
                text: "Product added to cart",
                duration: 1500,
                gravity: 'top-right',
                close: true,
                backgroundColor: "#4CAF50"
            }).showToast();
        }
    });
}


    function syncTableToLocalStorage() {
        let selectedDateKey = $.datepicker.formatDate("yy-mm-dd", $("#requestDeliveryDate").datepicker("getDate"));
        var updatedArray = [];

        $('#dynamicTable tbody tr').each(function () {
            var $row = $(this);
            var id = $row.data('id') || $row.data('product-id');
            var index = $row.data('index');
            var category_id = $row.find('.category-select').val();
            var category_name = $row.find('.category-select option:selected').text();
            var name = $row.find('td:eq(1)').text().trim();
            var price = $row.find('.price-input').val() || 0;
            updatedArray.push({
                id: id,
                category_id: category_id,
                category_name: category_name,
                date: '',
                name: name,
                price: price,
                product_id: id
            });
        });
        localStorage.setItem(`selectedProducts_${selectedDateKey}`, JSON.stringify(updatedArray));
    }

    function loadItems(selectedHorizonatlTabValue = null) {
    $('#dynamicTable thead').empty();
    $('#dynamicTable tbody').empty();
    $('#dynamicTable tfoot').empty();

    const selectedDateKey = $.datepicker.formatDate("yy-mm-dd", $("#requestDeliveryDate").datepicker("getDate"));

    if (selectedHorizonatlTabValue === 'previous_order') {
        var previous_order = JSON.parse(localStorage.getItem('previous_order')) || [];
        var theadHtml = `
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Product</th>
                <th>Price</th>
                <th>Action</th>
            </tr>`;
        $("#dynamicTable thead").html(theadHtml);

        var tbodyHtml = "";
        previous_order.forEach(function (product, index) {
            var price = product.price != null ? product.price : '';
            var priceWithCurrency = price ? formatPrice(price) : '';

            tbodyHtml += `
                <tr data-id="${product.id}" data-product-id="${product.product_id}" data-index="${index}">
                    <td class="dynamic-title">${product.title || ''}</td>
                    <td data-category-id="${product.category_id}">${product.category_name || ''}</td>
                    <td>${product.name || ''}</td>
                    <td class="text-right">${priceWithCurrency}</td>
                    <td>
                        <i 
                            class="fa fa-plus-circle partial_order_item_addToCurrentOrder" 
                            data-id="${product.id}"
                            data-title="${product.title || ''}"
                            data-category-id="${product.category_id}"
                            data-category_name="${product.category_name}"
                            data-product-id="${product.product_id}"
                            data-name="${product.name}"
                            data-price="${product.price || ''}"
                            style="cursor:pointer; color:#007bff;">
                        </i>
                        <span class="addedText" style="display:none; color:#00C314; margin-left:5px;">Added to Current Order</span>
                    </td>
                </tr>`;
        });
        $("#dynamicTable tbody").html(tbodyHtml);

    } else if (selectedHorizonatlTabValue === 'date_wise_item') {
        var date_wise_item = JSON.parse(localStorage.getItem('date_wise_item')) || [];
        var theadHtml = `
            <tr>
                <th>Date</th>
                <th>Title</th>
                <th>Category</th>
                <th>Product</th>
                <th>Price</th>
            </tr>`;
        $("#dynamicTable thead").html(theadHtml);

        var tbodyHtml = "";
        date_wise_item.forEach(function (product, index) {
            var price = product.price != null ? product.price : '';
            var priceWithCurrency = price ? formatPrice(price) : '';

            tbodyHtml += `
                <tr data-id="${product.id}" data-product-id="${product.product_id}" data-index="${index}">
                    <td>${product.date || ''}</td>
                    <td class="dynamic-title">${product.title || ''}</td>
                    <td data-category-id="${product.category_id}">${product.category_name || ''}</td>
                    <td>${product.name || ''}</td>
                    <td class="text-right">${priceWithCurrency}</td>
                </tr>`;
        });
        $("#dynamicTable tbody").html(tbodyHtml);

    } else {
        var productArray = JSON.parse(localStorage.getItem(`selectedProducts_${selectedDateKey}`)) || [];
        var theadHtml = `
            <tr>
                <th>Category</th>
                <th>Product</th>
                <th>Price</th>
                <th>Action</th>
            </tr>`;
        $("#dynamicTable thead").html(theadHtml);

        var tbodyHtml = "";
        productArray.forEach(function (product, index) {
            var categoryOptions = "";
            $.each(categories, function (i, category) {
                var selected = (category.category_id.toString() === product.category_id.toString()) ? "selected" : "";
                categoryOptions += `<option value="${category.category_id}" ${selected}>${category.category_name}</option>`;
            });

            // CHANGE IS ONLY HERE! (Conditional data-id)
            tbodyHtml += `
                <tr
                    ${product.id && !isNaN(Number(product.id)) ? `data-id="${product.id}"` : ""}
                    data-product-id="${product.product_id || product.id}" data-index="${index}">
                    <td>
                        <select class="form-control category-select" name="category_${index}">
                            ${categoryOptions}
                        </select>
                    </td>
                    <td>${product.name}</td>
                    <td>
                        <input type="text" class="form-control price-input" name="price_${index}" 
                               value="${product.price !== undefined && product.price !== null ? currencySymbol + ' ' + product.price : currencySymbol + ' '}" 
                               onfocus="this.value = this.value.replace(/\\D/g,'')" 
                               onblur="this.value = this.value === '${currencySymbol} ' ? '' : '${currencySymbol} ' + this.value.replace(/\\D/g,'')">
                    </td>
                    <td><i class="fa fa-trash delete-btn"></i></td>
                </tr>`;
        });
        $("#dynamicTable tbody").html(tbodyHtml);
    }

    // All remaining code — NO CHANGES AT ALL
    $(".category-select").off('change').on('change', function () {
        var index = $(this).closest('tr').data('index');
        var selectedCategoryId = $(this).val();
        var selectedCategoryName = $(this).find('option:selected').text();

        var selectedProducts = JSON.parse(localStorage.getItem(`selectedProducts_${selectedDateKey}`)) || [];
        if (selectedProducts[index]) {
            selectedProducts[index].category_id = selectedCategoryId;
            selectedProducts[index].category_name = selectedCategoryName;
            localStorage.setItem(`selectedProducts_${selectedDateKey}`, JSON.stringify(selectedProducts));
        }
    });

    $(".price-input").off('input').on('input', function () {
        var index = $(this).closest('tr').data('index');
        var newPrice = $(this).val();
        newPrice = newPrice.replace(/[^\d.]/g, '');

        var selectedProducts = JSON.parse(localStorage.getItem(`selectedProducts_${selectedDateKey}`)) || [];
        if (selectedProducts[index]) {
            selectedProducts[index].price = newPrice;
            localStorage.setItem(`selectedProducts_${selectedDateKey}`, JSON.stringify(selectedProducts));
        }
    });
}

    
    $(document).on('click', '.partial_order_item_addToCurrentOrder', function () {
        var $icon = $(this);
        var $addedText = $icon.next('.addedText');
        var id = $icon.data('id').toString();
        var categoryId = $icon.data('category-id');
        var product_id = $icon.data('product-id');
        var category_name = $icon.data('category_name');
        var name = $icon.data('name');
        var price = $icon.data('price');

        var productArray = JSON.parse(localStorage.getItem('selectedProducts')) || [];

        var exists = productArray.some(function (p) {
            return p.id.toString() === id && p.category_id.toString() === categoryId.toString();
        });

        if (!exists) {
            productArray.push({
                id: id,
                category_id: categoryId,
                category_name: category_name,
                date: '',
                name: name,
                price: price,
                product_id: product_id
            });

            localStorage.setItem('selectedProducts', JSON.stringify(productArray));
            $addedText.text("Added to Current Order").fadeIn();
        } else {
            $addedText.text("Already Added").fadeIn();
        }
    });

    $('.search-box').on('keyup', function () {
        var searchText = $(this).val().toLowerCase();

        $('.categoriesList li').each(function () {
            var categoryText = $(this).text().toLowerCase();
            var isVisible = categoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });

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

    $('#clickMe').click(function () {
        var selectedHorizonatlTab = '';
        selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
        if (selectedHorizonatlTab === "partial_order_item") {
            selectedHorizonatlTab = "date_wise_item";
        }
        if (selectedHorizonatlTab === "previous_order_received") {
            selectedHorizonatlTab = "previous_order";
        }
        $('#' + selectedHorizonatlTab + ' .status').click();
    });

$(document).on('click', '.delete-btn', function() {
    const $btn = $(this);
    const $row = $btn.closest('tr');
    
    // Get the database record id and the product id
    const id = $row.data('id');                // DB record id (undefined/null/''/0 for unsaved)
    const productId = $row.data('product-id'); // Always present
    
    const originalBtnHtml = $btn.html();

    // --- UNSAVED ITEMS: No valid DB id ---
    if (!id || id === 0 || id === '' || id === null) {
        //alert("dlmlcjodjojdodjdo")
        // Remove unsaved item locally (no backend call)
        const selectedDate = $("#requestDeliveryDate").datepicker("getDate");
        const dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

        let products = JSON.parse(localStorage.getItem(`selectedProducts_${dateKey}`)) || [];
        // Remove product by productId
        products = products.filter(item => String(item.product_id) !== String(productId));
        localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(products));

        // Remove the row from UI
        $row.fadeOut(400, function() {
            $(this).remove();
            if (typeof updateRowCount === 'function') updateRowCount();
        });

        showToast("Unsaved product removed from cart.", 'success');
        return; // Stop here — no backend call
    }

    // --- SAVED ITEMS: Has DB id ---
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
    $row.addClass('processing');

    $.ajax({
        url: site.base_url + "Todays_Special/deleteProcurementItem",
        method: 'POST',
        dataType: 'json',
        data: {
            id: id,               // Required by backend to soft-delete
            product_id: productId,
            [csrfName]: csrfHash  // Your CSRF token parameter
        },
        success: function(response) {
            if (response.status === 'success') {
                const selectedDate = $("#requestDeliveryDate").datepicker("getDate");
                const dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

                let products = JSON.parse(localStorage.getItem(`selectedProducts_${dateKey}`)) || [];
                // Remove product by id or product_id
                products = products.filter(item => !(item.id == id || String(item.product_id) === String(productId)));
                localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(products));

                $row.fadeOut(400, function() {
                    $(this).remove();
                    if (typeof updateRowCount === 'function') updateRowCount();
                });

                // Refresh cart UI from backend for full sync
                refreshCartFromBackend();

                showToast(response.message, 'success');
            } else {
                // Failure handler: re-enable button & show error
                $btn.prop('disabled', false).html(originalBtnHtml);
                $row.removeClass('processing');
                showToast(response.message || 'Delete failed', 'error');
            }
        },
        error: function() {
            // Network or server error handler
            $btn.prop('disabled', false).html(originalBtnHtml);
            $row.removeClass('processing');
            showToast('Network error while deleting item.', 'error');
        }
    });
});


// Helper function to refresh cart from backend after a delete
function refreshCartFromBackend() {
    const selectedDate = $("#requestDeliveryDate").datepicker("getDate");
    const dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

    $.ajax({
        url: site.base_url + "Todays_Special/getProcurementOrderList",
        method: "GET",
        data: { orderStatus: "add_items" },
        dataType: "json",
        success: function(data) {
            if (data) {
                localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(data));
                localStorage.setItem('selectedProducts', JSON.stringify(data));
                loadItems('add_items'); // reload UI after fresh data
            }
        },
        error: function() {
            console.error('Failed to refresh cart from backend after delete.');
        }
    });
}



    function refreshCartFromServer() {
        let selectedDate = $("#requestDeliveryDate").datepicker("getDate");
        let dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

        localStorage.removeItem(`selectedProducts_${dateKey}`);
        localStorage.removeItem('selectedProducts');

        $.ajax({
            url: site.base_url + "Todays_Special/getProcurementOrderList",
            method: 'GET',
            data: { orderStatus: 'add_items' },
            dataType: 'json',
            success: function(response) {
                if (response) {
                    localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(response));
                    loadItems('add_items');
                }
            },
            error: function(xhr, status, error) {
                console.error("Failed to refresh cart from server", status, error);
            }
        });
    }

    function resetDeleteButton($btn, originalHtml) {
        $btn.prop('disabled', false).html(originalHtml);
        $btn.closest('tr').removeClass('processing');
    }

    $(document).on('click', '.plusIcon1', function () {
        $("#addedText1").show();
        $(".plusIcon1").hide();
    });

    function updateRowCount() {
        var rowCount = $('#dynamicTable tbody tr').length;
        $('#rowCount').text(rowCount);
        localStorage.setItem('rowCount', rowCount);
    }
    
    $(document).ready(function () {
        function toggleDivs() {
            if ($('#activeStatus').hasClass('active')) {
                $('.hide-div').show();
                $('.show-div').hide();
            } else {
                $('.hide-div').hide();
                $('.show-div').show();
            }
            if ($('#dynamicTable').is(':hidden')) {
                $('.show-div').hide();
            }
        }
        $('.nav-link').on('click', function () {
            $('.nav-item').removeClass('active');
            $(this).parent().addClass('active');
            toggleDivs();
        });
        toggleDivs();
    });

    $(document).ready(function () {
        $('#activeStatus .status').click();
    });

    $('.nav-tabs .nav-link.status').on('click', function (e) {
        e.preventDefault();

        let selectedDate = $("#requestDeliveryDate").datepicker("getDate");
        let dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);
        let selectedHorizonatlTab = $(this).attr('value');

        if ($("#activeStatus").hasClass("active")) {
    let tableData = [];
    $('#dynamicTable tbody tr').each(function (index, row) {
        let $row = $(row);
        let rowData = {};
        rowData['id'] = $row.data('id') || $row.data('product-id');            // Add unique item ID
        rowData['product_id'] = $row.data('product-id');                      // Add product id
        rowData['category_id'] = $row.find('.category-select').val();
        rowData['category_name'] = $row.find('.category-select option:selected').text();
        rowData['name'] = $row.find('td:eq(1)').text().trim();               // product name
        rowData['price'] = $row.find('.price-input').val().trim();
        tableData.push(rowData);
    });
    localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(tableData));
}


        $('.nav-tabs .nav-item').removeClass('active');
        $(this).parent().addClass('active');
        localStorage.setItem('selectedHorizonatlTab', selectedHorizonatlTab);

        $(".orderNo").hide();
        $('#editorRow').hide();

        if (selectedHorizonatlTab === "date_wise_item" || selectedHorizonatlTab === "previous_order") {
            $(".right_section").hide();
            $('#add_note').hide();
            $(".repeat_order").hide();
            $('#place_order').hide();
        } else if (selectedHorizonatlTab === "add_items") {
            var theadHtml = `
        <tr>
            <th>Category</th>
            <th>Product</th>
            <th>Price</th>
            <th>Action</th>
        </tr>`;
    $("#dynamicTable thead").html(theadHtml);
            $('#dynamicTable tfoot').empty();
            $('#update_order').hide();
            $('#add_note').show();
            $('#place_order').show();
            $(".repeat_order").hide();
            $(".right_section").show();

            const cartProducts = JSON.parse(localStorage.getItem(`selectedProducts_${dateKey}`)) || [];
            if (cartProducts.length > 0) {
                loadItems('add_items');
            } else {
                $('#dynamicTable tbody').empty();
                addItemListById('add_items');
            }
        }

        if (selectedHorizonatlTab !== "add_items") {
            $('#dynamicTable thead').empty();
            $('#dynamicTable tbody').empty();
            $('#dynamicTable tfoot').empty();
            addItemListById(selectedHorizonatlTab);
        }
    });

    function addItemListById(selectedHorizonatlTabValue) {
        let selectedDate = $("#requestDeliveryDate").datepicker("getDate");
        let dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

        $.ajax({
            url: site.base_url + "Todays_Special/getProcurementOrderList",
            method: 'GET',
            data: { orderStatus: selectedHorizonatlTabValue },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    if (selectedHorizonatlTabValue === 'add_items' && response.length > 0) {
                        localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(response));
                    } else if (selectedHorizonatlTabValue === 'date_wise_item') {
                        localStorage.setItem('date_wise_item', JSON.stringify(response));
                    } else if (selectedHorizonatlTabValue === 'previous_order') {
                        localStorage.setItem('previous_order', JSON.stringify(response));
                    }
                    loadItems(selectedHorizonatlTabValue);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }

    function formatDate(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);
        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }

    $('#place_order').off('click').on('click', function () {
        let selectedDate = $("#requestDeliveryDate").datepicker("getDate");
        let dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);

        if ($('#dynamicTable tbody').children().length === 0) {
            Toastify({
                text: '❌ No items in table',
                duration: 2000,
                gravity: 'top-right',
                close: true,
            }).showToast();
            return;
        }

        let outletNames = $('#outletName').val();
        let title = $('#title').val();
        let requestedDeliveryDate = formatDate(selectedDate);
        let tableData = [];

        if (title === "") {
            Toastify({
                text: '⚠️ Title is empty. Please enter a title before saving.',
                duration: 3000,
                gravity: 'top-right',
                close: true,
                backgroundColor: "#ff0000",
            }).showToast();
            return;
        }

        $('#dynamicTable tbody tr').each(function (index, row) {
            let rowData = {};
            rowData['category_id'] = $(row).find('.category-select').val();
            rowData['category_name'] = $(row).find('.category-select option:selected').text();
            rowData['product'] = $(row).find('td:eq(1)').text().trim();
            rowData['price'] = $(row).find('.price-input').val().trim();
            rowData['outletNames'] = outletNames;
            rowData['requested_delivery_date'] = requestedDeliveryDate;
            rowData['title'] = title;
            tableData.push(rowData);
        });

        $.ajax({
            url: site.base_url + "Todays_Special/PlaceProcurementOrder",
            method: 'POST',
            data: {
                orders: tableData,
                [csrfName]: csrfHash
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Toastify({
                        text: '✅ ' + response.message,
                        duration: 3500,
                        gravity: 'top-right',
                        close: true,
                        backgroundColor: "#28a745",
                    }).showToast();

                    loadLatestItemsAfterSave();
                    localStorage.removeItem(`selectedProducts_${dateKey}`);
                    resetDropdown();
                    $('.status').click();
                } else {
                    Toastify({
                        text: '❌ ' + response.message,
                        duration: 3000,
                        gravity: 'top-right',
                        close: true,
                        backgroundColor: "#FF5F6D",
                    }).showToast();

                    if (response.message.includes('A title for this date already exists')) {
                        $("#title").val(response.existing_title);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Error sending data: ' + error);
                Toastify({
                    text: '❌ Server Error',
                    duration: 3000,
                    gravity: 'top-right',
                    close: true,
                    backgroundColor: "#ff0000",
                }).showToast();
            }
        });
    });

    function loadLatestItemsAfterSave() {
        $.ajax({
            url: site.base_url + "Todays_Special/getProcurementOrderList",
            method: 'GET',
            data: { orderStatus: 'add_items' },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    let selectedDate = $("#requestDeliveryDate").datepicker("getDate");
                    let dateKey = $.datepicker.formatDate("yy-mm-dd", selectedDate);
                    localStorage.setItem(`selectedProducts_${dateKey}`, JSON.stringify(response));
                    loadItems('add_items');
                } else {
                    console.log('No data returned');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching updated order list:', error);
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Only target Today's Special module specific links
    const specificLinks = document.querySelectorAll('#todays_special .submenu, #clickMe, .nav-link[value="Open"], .nav-link[value="date_wise_item"], .nav-link[value="previous_order"]');
    const divToChange = document.querySelector('#procurement_order .sixty-eight-percent');

    function applyWidth() {
        if (divToChange) {
            divToChange.style.setProperty('width', '82%', 'important');
            console.log("Width changed to 82%");
        }
    }

    function resetWidth() {
        if (divToChange) {
            divToChange.style.removeProperty('width');
            console.log("Width has been reset");
        }
    }

    // Only handle clicks for specific Today's Special module elements
    $(document).on('click', '#todays_special .nav-link[value="date_wise_item"], #todays_special .nav-link[value="previous_order"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        applyWidth();
    });
    
    // Handle submenu clicks within Today's Special module
    $(document).on('click', '#todays_special .submenu', function(e) {
        if ($(this).closest('#todays_special').length) {
            e.preventDefault();
            e.stopPropagation();
            applyWidth();
        }
    });
    
    // Allow main menu navigation to work normally
    $(document).on('click', '.main-menu a:not(#todays_special a)', function() {
        resetWidth();
    });

    document.addEventListener('click', function (e) {
        const target = e.target;

        if (target.matches('#repeat_order, .Update_rec-order-link, .nav-link[value="add_items"]')) {
            e.preventDefault();
            resetWidth();
        }
    });
});
