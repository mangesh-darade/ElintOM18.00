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

// Quantity decimal formatter using qty_decimals setting
function quantityDecimal(x) {
    // Use core formatNumber with qty_decimals so it respects Settings->qty_decimals
    if (typeof formatNumber === 'function' && site && site.settings) {
        return formatNumber(x, site.settings.qty_decimals);
    }
    var n = parseFloat(x);
    if (isNaN(n)) return '0';
    
    var decimals = site && site.settings && site.settings.qty_decimals ? site.settings.qty_decimals : 2;
    return n.toFixed(decimals);
}
var buttonText = '';
var buttonText_changed = '';
$(document).ready(function () {
    ///////////////////////////////////////////////////// ADD NOTES //////////////////////////////////////////
    // Initialize Redactor
    $('#editor').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function () {
            var v = this.get();
            localStorage.setItem('slnote', v);
        }
    });
    // Load saved note unless we're on the 'current_order' tab
    if ('selectedHorizonatlTab' === 'current_order') {
        $('#editor').redactor('set', '');
    } else {
        const slnote = localStorage.getItem('slnote');
        if (slnote) {
            $('#editor').redactor('set', slnote);
        }
    }
    // Prevent Enter key outside the editor
    $('body').on('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    const $addNoteButton = $('#add_note');
    $addNoteButton.click(function () {
        $('#editorRow').toggle();
        const isVisible = $('#editorRow').is(':visible');
        const newText = isVisible ? 'Hide Note' : 'Add Note';
        $addNoteButton.html('<i class="fa fa-file-text-o set-fnt"></i> ' + newText);
    });
    //////////////////////////////////////////////////////// RESET //////////////////////////////////////////
    localStorage.removeItem('selectedHorizonatlTab');
    $('#backid').click(function () {
        if (confirm("Are you sure you want to remove the Items?")) {
            localStorage.clear();
            window.location.reload(true);
        } else {

        }
    });
    //////////////////////////////////////////////////////// CATEGORIES //////////////////////////////////////////
    getProcurementOrderList('current_order');
    showCategories();
    getRowProducts();
    function showCategories() {
        $('.categoriesList').empty();
        $('.subcategoriesList').empty();
        $('.productList').empty();

        $.each(categories, function (index, category) {
            // Append categories with the necessary class and hover-fill span
            $('.categoriesList').append('<li class="btn btn-sty category last-category" data-category-id="' + category.category_id + '">' + category.category_name + '<span class="hover-fill"></span></li>');

            // Set initial background for the last category
            $('.categoriesList li:last-child').css({
                'background': 'linear-gradient(to right, #fff 96%, #008E80 4%)',
                'position': 'relative', // Required for absolute positioning of the hover-fill
                'padding': '10px 20px', // Padding for better click area
                'margin': '5px 0', // Margin for spacing
                'color': '#000', // Default text color
                'font-weight': 'bold', // Make text bold for visibility
                'overflow': 'hidden', // Prevent overflow of the pseudo-element
                'z-index': '1', // Behind the text
                'font-weight': '300',
            });

            // Style for the hover-fill span
            $('.categoriesList li:last-child .hover-fill').css({
                'content': "''",
                'position': 'absolute',
                'top': '0',
                'left': '100%', // Start off to the right
                'width': '100%',
                'height': '100%',
                'background': '#008E80', // Background color
                'transition': 'transform 0.4s ease-in-out', // Transition for the slide effect
                'z-index': '-1', // Behind the text
            });

            // Append subcategories
            $.each(category.subcategories, function (index, subcategory) {
                $('.subcategoriesList').append('<li class="btn btn-sty subcategory" style="display:none;" data-category-id="' + category.category_id + '">' + subcategory.subcategory_name + '</li>');
            });
        });

        // Apply hover effect using jQuery
        $('.categoriesList').on('mouseenter', '.last-category', function () {
            $(this).find('.hover-fill').css({
                'transform': 'translateX(-100%)', // Slide fill to left on hover
            });
            $(this).css('color', '#fff'); // Change text color on hover
        }).on('mouseleave', '.last-category', function () {
            $(this).find('.hover-fill').css({
                'transform': 'translateX(100%)', // Reset fill position to right on mouse leave
            });
            $(this).css('color', '#000'); // Reset text color
        });
    }
    $('.hand-o-left').click(function () {
        showCategories();
        $('.subcategoriesList').hide();  // Hide subcategories list
        $('.categoriesList').show();
        $('.subCatName').empty();
        $('.recent').hide();
        // Show categories list
    });
    $(document).on('click', '.category', function () {
        $('.categoriesList').hide();
        $('.subcategoriesList').show();
        $('.recent').show();
        categoryName = $(this).text();
        var CategoryID = $(this).data('category-id');
        var categoryName = $(this).text();
        $('.catName').text(categoryName);
        var $subcategories = $('.subcategory[data-category-id="' + CategoryID + '"]');
        if ($subcategories.length > 0) {
            $subcategories.show();
        } else {
            var categoryName = $(this).text();
            var CategoryID = $(this).data('category-id');
            getProducts(CategoryID);
        }
    });
    $(document).on('click', '.subcategory', function () {
        $('.recent').show();
        var SubCategoryID = $(this).data('category-id');
        var subcategoryName = $(this).text();
        $('.subCatName').text(subcategoryName);
        getProducts(SubCategoryID);
    });
        $(document).ready(function () {
            if(localStorage.getItem('addBomItemClicked')=== 'true'){
            $('.middle_body').css('width', '66%');
            $('.right_section').addClass('is-visible');
            $('.bom_material_section_item').show();
            $('.bom_material_section').hide();
            $('#sale_unit').val(quantityDecimal(1.00));
            $('#sale_unit_uom').val('');
            $('#uom_select').val('');
            $('#min_batch_qty').val(quantityDecimal(1.00));
            $('.status-toggle input[type="checkbox"]').prop('checked', false);
            $('#is_batch_only_toggle').prop('checked', false);
            $('#is_batch_only').val('0');
            $('#minQtyContainer').val('');
            $('#minQtyContainer').hide();
            $('#saveRawData').show();
            $('#add_note').closest('div').show();
    }
        // selected product from localStorage
        const storedProductId = localStorage.getItem('selectedCategoryProductid');
        const storedProductName = localStorage.getItem('selectedCategoryProductName');

        if (storedProductId && storedProductName) {
            selectedCategoryProductid = parseInt(storedProductId); 
            selectedCategoryProductName = storedProductName;

            $('#product_name').text(selectedCategoryProductName);
        } else {
            //  clear product name in UI if no selection
            $('#product_name').text('');
        }
        getRowProducts();
    });

    $(document).on('click', '.edit-icon', async function (e) {
        const productId = $(this).data('id');

        // Disable all products except the clicked one
        $('.Detailsrow').css('pointer-events', 'none').addClass('disabled-product');

        $('.Detailsrow[data-product-id="' + localStorage.getItem('activecategory') + '"]')
            .css('pointer-events', 'auto')
            .removeClass('disabled-product');

        localStorage.setItem('editMode', 'true');
        localStorage.setItem('editProductId', productId);       
        $('.middle_body').css('width', '66%');
        $(".right_section").addClass('is-visible');
        var id = $(this).data('id');
        $('.bom_material_section_item').show();
        $('.bom_material_section').hide();
        $('#saveRawData').show();                     
        $('#add_note').closest('div').show(); 
        localStorage.setItem('addBomItemClicked', 'true');
        localStorage.setItem('addBomItemClickTime', new Date().toISOString());
            // $.ajax({
            //         url: site.base_url + "Bill_of_material/getLatestBomVersion",
            //         data: { productId: selectedBomProduct.id },
            //         method: 'GET',
            //         dataType: 'json',
            //         success: function (res) {
            //             $('#version_bom').text(res.version); // Show in UI
            //         },
            //         error: function () {
            //             $('#version_bom').text('1'); // fallback
            //         }
            //     });
        

            // Fetch data and handle note display
            $.ajax({
                url: site.base_url + "Bill_of_material/getBomMaterialItems",
                type: 'POST',
                data: {
                    bom_id: id,
                    [csrf_token_name]: csrf_hash
                },
                dataType: 'json',
                success: function (response) {
                    const items = response.bomItems || [];
                    
                    // Set current version in UI when editing existing BOM
                    const currentVersion = (items && items.length > 0 && items[0].version_no) ? items[0].version_no : null;
                    if (currentVersion) {
                        $('#version_bom').text(currentVersion);
                    }
                    
                    if (items.length > 0) {
                        $('#status_toggle').prop('checked', parseInt(items[0].is_active) === 1);
                    }

                    // Map BOM items to localStorage format for loadOrderItems
                    const formattedItems = items.map(item => ({
                        rawProductId: item.material_id,
                        rawCode: item.product_code,
                        rawProductName: item.raw_material,
                        quantity: item.quantity_required,
                        unitId: item.uom_id || '',
                        wastage_percent: item.wastage_percent,
                        // is_alternative: item.is_alternative 
                        primary_product_id: item.primary_product_id || null
                    }));

                    localStorage.setItem('productRawDetailsList', JSON.stringify(formattedItems));

                    loadOrderItems();

                    // Set the original BOM creation date (not current date) for editing
                    if (items.length > 0 && items[0].created_at) {
                        const createdDate = items[0].created_at.split(' ')[0]; // Get date part only
                        $('#created_date').text(createdDate);
                        // Parse and set the date in datepicker
                        const dateObj = new Date(createdDate);
                        if (!isNaN(dateObj.getTime())) {
                            $("#requestDeliveryDate").datepicker("setDate", dateObj);
                        }
                        
                        // Store the correct creation date to prevent updateBomUI from overriding it
                        localStorage.setItem('currentBomCreatedDate', createdDate);
                    }

                    //  Set note into Redactor editor if available
                if ($('#editor').length && typeof $('#editor').redactor === 'function') {
                try {
                    const noteContent = items[0]?.notes || '';
                    $('#editor').redactor('set', noteContent);

                    if (noteContent.trim() !== '') {
                        $('#editorRow').show();

                        $('#add_note').html('<i class="fa fa-file-text-o set-fnt"></i> Hide Note');

                        setTimeout(() => {
                            $('#editor').redactor('focus');
                        }, 300);
                    }
                } catch (e) {
                    console.error("Failed to load note into Redactor:", e);
                }
            }
             $('.min_batch_qty').val(quantityDecimal(items[0]['min_batch_qty']));
             $('.sale_unit').val(quantityDecimal(items[0]['sales_units'].split(" ")[0]));
             $('#sale_unit_uom').val(items[0]['sales_units'].split(" ")[1]);

            // Ensure Is Batch Only toggle reflects stored value when editing
            if (items.length > 0 && typeof items[0].is_batch_only !== 'undefined') {
                const isBatchOnly = items[0].is_batch_only === 1 || items[0].is_batch_only === '1';
                $('#is_batch_only_toggle').prop('checked', isBatchOnly);
                $('#is_batch_only').val(isBatchOnly ? 1 : 0);

                if (isBatchOnly) {
                    $('#minQtyContainer').show();
                } else {
                    $('#minQtyContainer').hide();
                }
            }

            // loadProductData(productId);
        }      
        });
             $('.hand-o-left').css({
        'pointer-events': 'none',
        'opacity': '0.5'
    });

        // await getBomMaterialItemByBomId(id, null);
    });
    $('#addBomItemBtn').on('click', function () {

        if (!selectedBomProduct || !selectedBomProduct.id) {
            alert("Please select a product first.");
            return false;
        }
        $('.middle_body').css('width', '66%');
        $(".right_section").addClass('is-visible');
        $('.bom_material_section_item').show();
        $('.bom_material_section').hide();

        // Reset fields that should not carry over from previous selection
        $('#sale_unit').val(quantityDecimal(1.00));
        $('#sale_unit_uom').val('');
        $('#uom_select').val('');
        $('#min_batch_qty').val(quantityDecimal(1.00));
        $('.status-toggle input[type="checkbox"]').prop('checked', false);
        $('#is_batch_only_toggle').prop('checked', false);
        $('#is_batch_only').val('0');
        $('#minQtyContainer').val(''); 
        $('#minQtyContainer').hide();
        $('#saveRawData').show();                     
        $('#add_note').closest('div').show(); 


        if (selectedBomProduct.id) {
            $('#product_name').text(selectedBomProduct.name);
            $('#product_id').val(selectedBomProduct.id);
            $('#created_date').text(new Date().toLocaleDateString());
            
            // Set current date in the date picker field
            $("#requestDeliveryDate").datepicker("setDate", new Date());

            // Fetch the latest version from server
            $.ajax({
                url: site.base_url + "Bill_of_material/getLatestBomVersion",
                data: { productId: selectedBomProduct.id },
                method: 'GET',
                dataType: 'json',
                success: function (res) {
                    $('#version_bom').text(res.version); // Show in UI
                },
                error: function () {
                    $('#version_bom').text('1'); // fallback
                }
            });
        }

        localStorage.setItem('addBomItemClicked', 'true');
        localStorage.setItem('addBomItemClickTime', new Date().toISOString());
        loadOrderItems();

    });
    const clicked = localStorage.getItem('addBomItemClicked');

    function getProducts(CategoryID, SubCategoryID) {
        $.ajax({
            url: site.base_url + "Bill_of_material/GetProductsbyCategoriesID",
            method: 'GET',
            data: {
                subcategoryId: SubCategoryID,
                categoriesId: CategoryID,
            },
            dataType: 'json',
            success: function (response) {
                response.forEach(function (subCategorie) {
                    var productDetails = subCategorie.productsByCat;
                    getProductsbyCategories(productDetails);

                });
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
    let selectedCategoryProductName = null;
    let selectedCategoryProductid = null;
    let selectedBomProduct = {
    id: null,
    name: null
    };
    function getProductsbyCategories(requests) {
        requests.forEach(function (product) {
            if (product) {
                var productItem = $('<li class="btn btn-sty ItemsDetails Detailsrow categoryProduct" data-product-id="' + product.id + '">' + product.name + '<span class="hover-fill"></span></li>');
                $('#procurement_order').on('mouseenter', '.ItemsDetails', function () {
                    $(this).find('.hover-fill').css({ 'transform': 'translateX(-100%)' });
                }).on('mouseleave', '.ItemsDetails', function () {
                    $(this).find('.hover-fill').css({ 'transform': 'translateX(100%)' });
                });

                $('.productList').append(productItem);
                productItem.click(function () {
                    $('.categoryProduct').removeClass('active');
                    $(this).addClass('active');

                localStorage.setItem("activecategory",$('.categoryProduct').attr('data-product-id'));

                    selectedCategoryProductName = product.name;  // Store selected category
                    selectedCategoryProductid = product.id;  // Store selected category

                    // Store the selected product for BOM
                    selectedBomProduct.id = product.id;
                    selectedBomProduct.name = product.name; 

                    // Important: ensure we are not stuck in add mode when switching products
                    // This flag forces the right-side item editor to stay open; clear it here
                    localStorage.removeItem('addBomItemClicked');
                    localStorage.removeItem('addBomItemClickTime');
                    
                    // Re-enable the back button when switching products
                    $('.hand-o-left').css({
                       'pointer-events': 'auto',
                       'opacity': '1'
                    });
                    
                    // getBomMaterialDetails(product.id);
                    getBomdetailsByProductId(product.id, product.name);
                });

            }
        });
    }
    let selectedRawName = null;
    function getRowProductsList(requests) {
    $('.rawProductList').empty();
    
    requests.forEach(function (product) {
        if (product) {
            var productItem = $('<li class="btn btn-sty ItemsDetails rawProduct Detailshover" data-product-id="' + product.id + '">' + product.name + '<span class="hover-fill Details-fill"></span></li>');

           $('#procurement_order').on('mouseenter', '.ItemsDetails', function () {
                    $(this).find('.hover-fill').css({ 'transform': 'translateX(-100%)' });
                }).on('mouseleave', '.ItemsDetails', function () {
                    $(this).find('.hover-fill').css({ 'transform': 'translateX(100%)' });
                });
            
            $('.rawProductList').append(productItem);
            
            productItem.click(function () {
                $('.rawProduct').removeClass('active');
                $(this).addClass('active');
                selectedRawName = product.name;
                updateSelectedNames(product);
            });
        }
        });
    }
    
    function getBomdetailsByProductId(productId, productName) {
        $.ajax({
            url: site.base_url + "Bill_of_material/GetBomDetailsByProductId",
            method: 'GET',
            data: {
                productId: productId,
                productName: productName
            },
            dataType: 'json',
            success: function (response) {
                const clicked = localStorage.getItem('addBomItemClicked');
                if (response && response.bomDetails) {
                    const bom = response.bomDetails[0];
                    const bomMaterialsDetails = response.bomDetails;
                    localStorage.setItem('bomDetails', JSON.stringify(bom));
                    localStorage.setItem('bomMaterialsDetails', JSON.stringify(bomMaterialsDetails));
                    if (productName) {
                        // Check if BOM ID is valid
                        if (!bom.id || bom.id === null) {
                            // Clear localStorage and tablev
                            $('#SelectedProductName').text(bom.name);
                            localStorage.removeItem('bomDetails');
                            localStorage.removeItem('bomMaterialsDetails');
                            $('#dynamicTable thead').html('');
                            if (clicked != 'true') {
                                $('#dynamicTable tbody').html('<tr><td colspan="5" class="text-center">No data available</td></tr>');

                            } else {
                                //--------------------For Version no increment--------------------
                                // In add mode with no previous BOM, prepare UI for creating new BOM
                                $('.bom_material_section_item').show();
                                $('.bom_material_section').hide();
                                $('#product_name').text(productName || '');
                                $('#product_id').val(productId || '');
                                $('#created_date').text(new Date().toLocaleDateString());
                                $("#requestDeliveryDate").datepicker("setDate", new Date());
                                // Fetch the latest version to display next version number
                                $.ajax({
                                    url: site.base_url + "Bill_of_material/getLatestBomVersion",
                                    data: { productId: productId },
                                    method: 'GET',
                                    dataType: 'json',
                                    success: function (res) {
                                        $('#version_bom').text(res.version);
                                    },
                                    error: function () {
                                        $('#version_bom').text('1');
                                    }
                                });
                                loadOrderItems();
                            }
                            return; // Exit early
                        }
                        if (clicked === 'true') {
                            // Add mode: do NOT override date/version with previous BOM
                            $('.bom_material_section_item').show();
                            $('.bom_material_section').hide();
                            // Ensure selected product details are shown
                            $('#product_name').text(productName || '');
                            $('#product_id').val(productId || '');
                            $('#created_date').text(new Date().toLocaleDateString());
                            $("#requestDeliveryDate").datepicker("setDate", new Date());
                            // Show next version number
                            $.ajax({
                                url: site.base_url + "Bill_of_material/getLatestBomVersion",
                                data: { productId: productId },
                                method: 'GET',
                                dataType: 'json',
                                success: function (res) {
                                    $('#version_bom').text(res.version);
                                },
                                error: function () {
                                    $('#version_bom').text('1');
                                }
                            });
                            loadOrderItems();
                        } else {
                            // View mode: load existing BOM list and reflect selected BOM header
                            loadMaterials();
                            updateBomUI(bom);
                        }
                    }
                } else {
                    // No response or empty data
                    localStorage.removeItem('bomDetails');
                    localStorage.removeItem('bomMaterialsDetails');
                    $('#dynamicTable thead').html('');
                    $('#dynamicTable tbody').html('<tr><td colspan="5" class="text-center">No BOM details found</td></tr>');
                }

            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
    async function editMaterialToItem(products) {
        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
        products.forEach(product => {
            const productRawDetails = {
                productId: selectedCategoryProductid,
                productName: selectedCategoryProductName,
                rawProductId: product.product_id,
                rawProductName: product.raw_material,
                type: product.type,
                quantity: product.quantity_required,
                unitId: product.uom,
                unit_name: product.unit_name,
                rawCode: product.product_code,
                wastage_percent: product.wastage_percent
            };
            // Find if exact item exists (comparing more fields)
            const existingIndex = storedItems.findIndex(item =>
                item.productId === productRawDetails.productId &&
                item.rawProductId === productRawDetails.rawProductId &&
                item.type === productRawDetails.type &&
                item.unitId === productRawDetails.unitId &&
                item.wastage_percent === productRawDetails.wastage_percent
            );
            if (existingIndex !== -1) {
                // If same item with same type/unit/wastage, sum the quantity
                storedItems[existingIndex].quantity = (
                    parseFloat(storedItems[existingIndex].quantity) + parseFloat(productRawDetails.quantity)
                ).toFixed(site && site.settings && site.settings.qty_decimals ? site.settings.qty_decimals : 2);
            } else {
                // Otherwise, treat it as a new item
                storedItems.push(productRawDetails);
            }
        });

        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));

        loadOrderItems(); // Refresh UI
    }
    async function getBomMaterialItemByBomId(bomId, productName) {
        $.ajax({
            url: site.base_url + "Bill_of_material/getBomMaterialItems",
            type: 'POST',
            data: {
                bom_id: bomId,
                [csrf_token_name]: csrf_hash
            },
            dataType: 'json',
            success: async function (response) {
                const clicked = localStorage.getItem('addBomItemClicked');
                if (response && response.bomItems) {
                    const bom = response.bomItems[0];
                    const bomMaterialsDetails = response.bomItems;
                    localStorage.setItem('bomDetails', JSON.stringify(bom));
                    localStorage.setItem('bomMaterialsDetails', JSON.stringify(bomMaterialsDetails));
                    var bomDetails = response.bomItems;
                    await editMaterialToItem(bomDetails);
                } else {
                    // No response or empty data
                    localStorage.removeItem('bomDetails');
                    localStorage.removeItem('bomMaterialsDetails');
                    $('#dynamicTable thead').html('');
                    $('#dynamicTable tbody').html('<tr><td colspan="5" class="text-center">No BOM details found</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('Failed to load BOM material items.');
            }
        });
    }


    ////////////////////////////////////////////////////////// LEFT SECTION //////////////////////////////////////////
    /////////////////////////////////////////////////// SEARCH CATEGORIES //////////////////////////////////////////////
    $('#left_search').on('keyup', function () {
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
    /////////////////////////////////////////////////////////////// View BOM Material Items //////////////////////////////////////////
    let table;
    $(document).on('click', '.view-icon', function () {
        const itemId = $(this).data('id');
        const bom = localStorage.getItem('bomDetails');
        if (bom && bom !== 'null') {
            const bomDetails = JSON.parse(bom);
            $('#selected_product_name').text(bomDetails.name);
        }         
        $.ajax({
            url: site.base_url + "Bill_of_material/getBomMaterialItems",
            type: 'POST',
            data: {
                bom_id: itemId,
                [csrf_token_name]: csrf_hash
            },
            dataType: 'json',
            success: function (response) {
                const items = response.bomItems || [];
                let bomDetails = {};
                if (bom && bom !== 'null') {
                    bomDetails = JSON.parse(bom);
                }

                $('#selected_product_name').html(`
                ${bomDetails.name || ''} 
                <span class="text-muted">(${items[0].is_active == 1 ? 'Active' : 'Inactive'})</span>
            `);

                // Display date only (no time) - use actual BOM creation date from database
                const actualCreatedDate = items.length > 0 && items[0].created_at ? items[0].created_at.split(' ')[0] : 'N/A';
                $('#created_at').text(actualCreatedDate);
                
                if ($.fn.DataTable.isDataTable('#itemTable')) {
                    table = $('#itemTable').DataTable();
                    table.clear().draw();
                } else {
                    table = $('#itemTable').DataTable({
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false   //  disable sorting completely
                    });
                }

                $('#min_batch_qty_new').text(quantityDecimal(items[0]['min_batch_qty']));
                $('#sale_unit_view ').text(items[0]['sales_units'].split(" ")[0] + ' ' + (items[0]['sales_units'].split(" ")[1] || ''));

                items.forEach(item => {
                    var bom_ref = item.bom_ref === null ? '-' : item.bom_ref;
                    var alternativeText = item.alternative_material_name ? item.alternative_material_name : '-';
                    table.row.add([
                        `<div class="text-left">${item.raw_material}</div>`,
                        `<div class="text-center">${bom_ref}</div>`,
                        `<div class="text-center">${quantityDecimal(item.quantity_required)}</div>`,
                        `<div class="text-left">${item.unit_name}</div>`,
                        `<div class="text-right">${quantityDecimal(item.wastage_percent)}</div>`,
                        // `<div class="text-left">${item.is_alternative == 1 ? 'Yes' : 'No'}</div>`
                        `<div class="text-left">${alternativeText}</div>`
                    ]);
                });

                table.draw();
                $('#itemTable thead th').removeClass('sorting sorting_asc sorting_desc');

                // Clear and re-add note below table
                $('.note-display').remove();
                // Add note display after table (only if note exists)
                if (items.length > 0 && items[0].notes) {
                $('#itemTable').after(`
                    <div class="note-display" style="margin-top: 20px; padding: 10px; border-top: 1px solid #ddd;">
                        <strong>Note:</strong><br>
                        ${items[0].notes}
                    </div>
                `);
            }

                $('#itemModal').modal('show');
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('Failed to load BOM material items.');
            }
        });
    });
    //////////////////////////////////////////////////////////////////////////////////// Load BOM Materials //////////////////////////////////////////
     function loadMaterials() {
        const bomList = JSON.parse(localStorage.getItem('bomMaterialsDetails')) || [];
        // Table Header
        const tableHeader = `
        <tr>
             <th scope="col">Version</th>
            <th scope="col">BOM_Id</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
            <th scope="col">Actions</th>
        </tr>`;
        $('#saveRawData').hide();
        $('#dynamicTable thead').html(tableHeader);

        // Clear existing table body
        $('#dynamicTable tbody').html('');

        // Build each row
        bomList.forEach((item, index) => {
            const isActive = item.is_active == "1";
            const statusText = isActive ? 'Active' : 'Inactive';
            const statusClass = isActive ? 'text-success' : 'text-danger';
            const row = `
                <tr data-index="${index}">
                    <td>${item.version_no || '-'}</td>
                    <td>${item.id || '-'}</td>
                    <td class="${statusClass}">${statusText}</td>
                    <td>${item.created_at || '-'}</td>
                    <td class="text-center">
                        <i class="fa fa-eye text-primary me-2 view-icon" title="View" data-id="${item.id}" style="cursor:pointer;"></i>
                        <i class="fa fa-edit text-success edit-icon" title="Edit" data-id="${item.id}" style="cursor:pointer;"></i>
                    </td>
                </tr>
            `;
            $('#dynamicTable tbody').append(row);
        });

        // Attach icon click handlers
    }

    // Utility function to update UI
    // Toggle handler
    // $('#is_batch_only_toggle').on('change', function () {
    //     const isChecked = this.checked;
    //     $('#is_batch_only').val(isChecked ? 1 : 0);

    //     if (isChecked) {
    //         $('#minQtyContainer').show();
    //     } else {
    //         $('#minQtyContainer').hide();
    //     }
    // });
    $(document).ready(function () {
        function updateBatchUI() {
            var isChecked = $('#is_batch_only_toggle').is(':checked');
            $('#is_batch_only').val(isChecked ? 1 : 0);

            if (isChecked) {
                // Show Min Qty
                $('#minQtyContainer').show();
                $('.uom-field').removeClass('col-md-4').addClass('col-md-3');
                $('.sale-uom-field').removeClass('col-md-5').addClass('col-md-3');
            } else {
                // Hide Min Qty and reallocate width, and reset Min Qty to default 1.00
                $('#minQtyContainer').hide();
                $('.uom-field').removeClass('col-md-3').addClass('col-md-4');
                $('.sale-uom-field').removeClass('col-md-3').addClass('col-md-5');

                // Reset Min Qty value when batch toggle is OFF
                $('#min_batch_qty').val('1.00');

                // Reset Sale Unit value when batch toggle is OFF
                $('#sale_unit').val('1.00');
            }
        }
        updateBatchUI();
        $('#is_batch_only_toggle').change(updateBatchUI);
    });

    // Ensure correct state on load or update
    function updateBomUI(bom) {
        var version = (bom.version_no < 0 || bom.version_no == null) ? 1 : bom.version_no;
        // var version = (!bom.version_no || bom.version_no < 0) ? 1 : parseInt(bom.version_no) + 1;
        $('#product_name').text(bom.name);
        $('#SelectedProductName').text(bom.name);
        $('#version_bom').text(version);
        
        // Check if we have a stored correct creation date from the database
        const storedCreatedDate = localStorage.getItem('currentBomCreatedDate');
        if (storedCreatedDate) {
            $('#created_date').text(storedCreatedDate);
            // Also set the datepicker to the correct date
            const dateObj = new Date(storedCreatedDate);
            if (!isNaN(dateObj.getTime())) {
                $("#requestDeliveryDate").datepicker("setDate", dateObj);
            }
        } else {
            const createdDate = bom.created_at ? bom.created_at.split(' ')[0] : '';
            $('#created_date').text(createdDate);
        }
        $('#product_id').val(bom.product_id);
        // Sale unit & UOM
        $('#uom_select').val(bom.batch_uom || ''); 
         var sale_uom = bom.sales_units.match(/^(\d+)\s*(.*)$/);
        if (sale_uom) {
           var qty = sale_uom[1];               
           var unit_name = sale_uom[2].trim();  
            $('#sale_unit').val(qty || '');
            $('#sale_unit_uom').val(unit_name || '');
        }
        // Status toggle
        const isActive = bom.is_active === "1" || bom.is_active === 1;
        $('.status-toggle input[type="checkbox"]').prop('checked', isActive);
        // Is Batch Only toggle
        const isBatchOnly = bom.is_batch_only === "1" || bom.is_batch_only === 1;
        $('#is_batch_only_toggle').prop('checked', isBatchOnly);
        $('#is_batch_only').val(isBatchOnly ? 1 : 0);

        // Min Qty visibility based on toggle
        if (isBatchOnly) {
            $('#minQtyContainer').show();
        } else {
            $('#minQtyContainer').hide();
        }
        // Min Qty
        $('#min_batch_qty').val(quantityDecimal(bom.min_batch_qty || ''));
    }

    const storedBom = localStorage.getItem('bomDetails');
    if (storedBom) {
        const bom = JSON.parse(storedBom);
        updateBomUI(bom);
    }
    // Helper function to update display of selected names
    function updateSelectedNames(product) {
        if (!selectedCategoryProductid || !selectedCategoryProductName) {
            alert('Please select a product');
            return;
        }
        const productRawDetails = {
            productId: selectedCategoryProductid,
            productName: selectedCategoryProductName,
            rawProductId: product.id,
            rawProductName: product.name,
            type: product.type,
            quantity: product.quantity,
            unitId: product.unit,
            unit_name: product.unit_name,
            rawCode: product.code,
        };
        // Get current list from localStorage or initialize
        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
        const existingIndex = storedItems.findIndex(item =>
            item.productId === productRawDetails.productId &&
            item.rawProductId === productRawDetails.rawProductId
        );
        if (existingIndex !== -1) {
            // If exists, increase quantity
            storedItems[existingIndex].quantity = parseFloat(storedItems[existingIndex].quantity) + parseFloat(productRawDetails.quantity);
        } else {
            // If not, push new item
            storedItems.push(productRawDetails);
        }
        // Save updated list
        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
        // Reload table
        loadOrderItems();
    }
    function getRowProducts() {
        $.ajax({
            url: site.base_url + "Bill_of_material/GetRawProducts",
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                response.forEach(function (rowProduct) {
                    var rowProductDetails = rowProduct.Rawproducts;
                    getRowProductsList(rowProductDetails);

                });
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
    $('#raw_material_search').on('keyup', function () {
        var searchText = $(this).val().toLowerCase(); // Get user input and convert to lowercase
        $('.rawProductList li').each(function () {
            var subcategoryText = $(this).text().toLowerCase();
            var isVisible = subcategoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });
    });
    //////////////////////////////////////////////////////// NAVIGATON ////////////////////////////////////////////////
    $('#clickMe').click(function () {
        var selectedHorizonatlTab = '';
        selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
        if (selectedHorizonatlTab === "partial_order_item") {
            // selectedHorizonatlTabselectedHorizonatlTab = "partially";
            selectedHorizonatlTab = "partially";
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
   
    function loadOrderItems() {
    $("#saveRawData").show();
    $("#add_note").show(); // Clear previous header
    const orderdetails = `
        <tr>
            <th scope="col">Raw Material CD</th>
            <th scope="col">Material Name</th>
            <th scope="col">Required Qty. </th>
            <th scope="col">UOM</th>
            <th scope="col">Wastage %</th>
            <th scope="col">Alternative</th>
            <th scope="col">Delete</th>
        </tr>`;
    $("#dynamicTable thead").html(orderdetails);
    const storedItems =
      JSON.parse(localStorage.getItem("productRawDetailsList")) || [];
    // Validation: if array is empty

    $("#dynamicTable tbody").html(""); // Clear previous rows

    // Build set of materials that already selected an alternative (parents)
    const parentsWithAlternative = new Set(
      (storedItems || [])
        .filter(si => si && si.primary_product_id != null)
        .map(si => String(si.rawProductId))
    );

    storedItems.forEach((item, index) => {
      // Build alternative dropdown options with global filtering rules
      let alternativeOptions = '<option value="">None</option>';
      for (let i = 0; i < storedItems.length; i++) {
        const optItem = storedItems[i];
        if (!optItem) continue;
        const optIdStr = String(optItem.rawProductId);
        const selfIdStr = String(item.rawProductId);
        const selectedIdStr = String(item.primary_product_id || '');

        // Exclude itself to avoid self-referencing
        if (optIdStr === selfIdStr) continue;

        // Hide material name in other dropdowns when an alternative is selected for it
        // (i.e., when that material acts as a parent with a chosen alternative)
        // But keep it visible if it's the current row's selected value
        if (parentsWithAlternative.has(optIdStr) && selectedIdStr !== optIdStr) continue;

        const isSelected = selectedIdStr === optIdStr;
        alternativeOptions += `<option value="${optItem.rawProductId}" ${isSelected ? 'selected' : ''}>${optItem.rawProductName}</option>`;
      }

      const row = `
            <tr data-index="${index}" class="${
        item.primary_product_id ? "alt-row" : ""
      }">
                <td class="text-start">
                    <input type="hidden" name="product_code[]" value="${
                      item.rawProductId
                    }">
                    ${item.rawCode}
                </td>
                <td class="text-start" style="text-align: start !important;">
                    <input type="hidden" name="rawProductId[]" value="${
                      item.rawProductId
                    }">
                    ${item.rawProductName}
                </td>
                <td class="text-end">
                    <input type="number" step="${site && site.settings && site.settings.qty_decimals ? (site.settings.qty_decimals == 0 ? '1' : '0.' + '0'.repeat(site.settings.qty_decimals - 1) + '1') : '0.01'}" name="quantity[]" class="form-control text-end quantity-input" value="${quantityDecimal(
                      item.quantity
                    )}" style="width: 100px;" required min="0">
                </td>
                <td class="text-center">
                    <select name="unit[]" class="form-select unit-select text-center" style="width: 100px;" required>
                        ${units
                          .map(
                            (uom) => `
                            <option value="${uom.id}" ${
                              uom.id == item.unitId ? "selected" : ""
                            }>${uom.name}</option>
                        `
                          )
                          .join("")}
                    </select>
                </td>
                <td class="text-end">
                    <input type="number" step="${site && site.settings && site.settings.qty_decimals ? (site.settings.qty_decimals == 0 ? '1' : '0.' + '0'.repeat(site.settings.qty_decimals - 1) + '1') : '0.01'}" name="wastage[]" class="form-control text-end wastage-input" value="${quantityDecimal(
                      item.wastage_percent || 0
                    )}" style="width: 80px;" required min="0">
                </td>
                <td class="text-center">
                    <select name="alternative[]" class="form-select alt-select text-left" style="width: 150px;" ${
                      index === 0 ? "" : ""
                    }>
                        ${alternativeOptions}
                    </select>
                </td>
                <td class="text-center">
                    <i class="fa fa-trash delete-btn" style="cursor:pointer; color:red;"></i>
                </td>
            </tr>
            `;
      $("#dynamicTable tbody").append(row);
    });

    // Attach delete functionality
    attachDeleteEvents();

    // Highlight row when alternative is selected and update localStorage
    $(document)
      .off("change", ".alt-select")
      .on("change", ".alt-select", function () {
        const row = $(this).closest("tr");
        const index = row.data("index");
        const selectedValue = $(this).val();

        // Update localStorage
        let storedItems =
          JSON.parse(localStorage.getItem("productRawDetailsList")) || [];
        if (storedItems[index]) {
          storedItems[index].primary_product_id = selectedValue
            ? selectedValue
            : null;
          localStorage.setItem(
            "productRawDetailsList",
            JSON.stringify(storedItems)
          );
        }

        // Highlight row
        if (selectedValue != "") {
          row.addClass("alt-row");
        } else {
          row.removeClass("alt-row");
        }

        // Reload table to update other dropdowns (exclude newly selected alternative)
        loadOrderItems();
      });
  }

    // Add CSS for pink highlight once
    if (!$('#altRowStyle').length) {
        $('<style id="altRowStyle">')
            .prop('type', 'text/css')
            .html(`
                .alt-row {
                    background-color: #98D7FF !important; /* light pink */
                }
            `)
            .appendTo('head');
    }

    let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
    $('#dynamicTable tbody').on('change', 'input.quantity-input, select.unit-select, input.wastage-input, select.alt-select', function () {
        const $row = $(this).closest('tr');
        const index = $row.data('index');

        if (index === undefined) return;

        // Fetch fresh copy every time
        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];

        const updatedQuantity = Number($row.find('input.quantity-input').val());
        const updatedUnitId = Number($row.find('select.unit-select').val());
        const updatedWastage = Number($row.find('input.wastage-input').val());
        // const updatedIsAlt = Number($row.find('select.alt-select').val());
        const updatedPrimaryProductId = $row.find('select.alt-select').val();
        
        storedItems[index].quantity = updatedQuantity;
        storedItems[index].unitId = updatedUnitId;
        storedItems[index].wastage_percent = updatedWastage;
        // storedItems[index].is_alternative = updatedIsAlt; 
        storedItems[index].primary_product_id = updatedPrimaryProductId ? updatedPrimaryProductId : null;

        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));

        // loadOrderItems();
});

    function attachDeleteEvents() {
        $('.delete-btn').off('click').on('click', function () {
            const row = $(this).closest('tr');
            // if ($(this).val() == "1") {
            const index = row.data('index');
            let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
            
            const deletedProductId = storedItems[index].rawProductId;
            
            // Remove the item at that index
            storedItems.splice(index, 1);
            
            // Update primary_product_id for items that referenced the deleted item
            storedItems.forEach(item => {
                if (item.primary_product_id == deletedProductId) {
                    item.primary_product_id = null;
                }
            });
            
            // Update localStorage
            localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
            // Reload table
            loadOrderItems();
        });
    }
  
    //////////////////////////////////////////////////////////////  SAVE Raw ITEMS ///////////////////////////////////////////////////////
    $('#saveRawData').on('click', function () {
        localStorage.removeItem('bomDetails');
        const orderData = [];
        const batchdata = [];

        // General header data
        const productId = $('#product_id').val();
        const createdDate = ($("#requestDeliveryDate").datepicker("getDate"));
        const outletNames = $('#outletName').val();
        const productName = $('#product_name').text().trim();
        const bomId = $('#bom_id').text().trim();
        const version = $('#version_bom').text().trim();
        const isActive = $('#status_toggle').is(':checked') ? 1 : 0;
        const isBatchOnly = $('#is_batch_only_toggle').is(':checked') ? 1 : 0;
        var min_batch_qty = $('#min_batch_qty').val();
        var uom_id = $('.units').val();
        var sale_unit = $('#sale_unit').val();
        var sale_unit_uom_id = $('#sale_unit_uom').val();

        // When Is Batch Only is turned off, reset Min Qty and Sale Unit to default 1.00 so old values do not persist
        if (!isBatchOnly) {
            min_batch_qty = '1.00';
            $('#min_batch_qty').val('1.00');

            sale_unit = '1.00';
            $('#sale_unit').val('1.00');
        }

        let notes = '';
        if ($('#editor').length && typeof $('#editor').redactor === 'function') {
            try {
                notes = $('#editor').redactor('get'); //  Correct method for older Redactor
            } catch (e) {
                console.error("Redactor get() failed:", e);
            }
        }

        // Loop over table rows
        $('#dynamicTable tbody tr').each(function () {
            const rawProductId = $(this).find('input[name="rawProductId[]"]').val();
            const quantity = $(this).find('input[name="quantity[]"]').val();
            const unitId = $(this).find('select[name="unit[]"]').val(); // unit should be <select>
            const wastage = $(this).find('input[name="wastage[]"]').val();
            const primaryProductId = $(this).find('select[name="alternative[]"]').val();
            if (!productId || !rawProductId) {
                alert('Some fields are missing in one of the rows.');
                return false; // exits each loop
            }
            orderData.push({
                rawProductId,
                quantity,
                unitId,
                createdDate,
                wastage,
                primary_product_id: primaryProductId ? primaryProductId : null
            });
            batchdata.push({
                min_batch_qty,
                uom_id,
                sale_unit,
                sale_unit_uom_id,
                isBatchOnly
            });
        });

        // Final data to send

        const payload = {
            product_name: productName,
            productId: productId,
            bom_id: bomId,
            version_no: version,
            created_at: createdDate,
            is_active: isActive,
            outlet_name: outletNames,
            items: orderData,
            batchData: batchdata,
            notes: notes // note in payload
        };
        
        if (orderData.length === 0) {
            alert('No data to save.');
            return;
        }
        $.ajax({
            url: site.base_url + "Bill_of_material/addProddUnitBomData",
            type: 'POST',
            data: {
                data: JSON.stringify(payload),
                // Add CSRF token here
                [csrf_token_name]: csrf_hash
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Toastify({
                        text: 'New Version is created.',
                        duration: 1500,
                        gravity: 'top',
                        position: 'right',
                        close: true
                    }).showToast();
                    // Show the note in modal if present
                    // const noteContent = payload.notes?.trim();
                    // $('.note-display').remove(); // Remove any existing note display
                    
                    // if (noteContent) {
                    //     $('#itemTable').before(
                    //         `<div class="note-display mb-3" style="padding:10px; border:1px solid #ccc; background:#fdf6f6;">
                    //             <strong>Note:</strong><br>${noteContent}
                    //         </div>`
                    //     );
                    // }

                    // Clear the editor after successful submission
                    if ($('#editor').length && typeof $('#editor').redactor === 'function') {
                        try {
                            $('#editor').redactor('set', '');
                        } catch (e) {
                            console.error("Redactor clear failed:", e);
                        }
                        $('#editorRow').hide();
                        $('#add_note').html('<i class="fa fa-file-text-o set-fnt"></i> Add Note');
                    }
                // Remove any existing note display
                $('.note-display').remove();
                        // Clear flags that keep the item editor open so the list view is shown next
                        localStorage.removeItem('addBomItemClicked');
                        localStorage.removeItem('addBomItemClickTime');
                        localStorage.removeItem('editMode');
                        localStorage.removeItem('editProductId');
                        setTimeout(function () {

                        $('.status-toggle input[type="checkbox"]').prop('checked', false);
                        $('#is_batch_only_toggle').prop('checked', false);


                        // CLEAR BOM TABLE & LOCALSTORAGE
                        // $('#dynamicTable thead').empty();
                        $('#dynamicTable tbody').empty();
                        $('#dynamicTable tfoot').empty();
                        // localStorage.clear();
                        localStorage.removeItem('productRawDetailsList');

                        $('.status').click();

                        loadOrderItems(); // reloads the header and attaches empty rows
                         $('.bom_material_section_item').hide();       
                         $('#saveRawData').hide();                     
                         $('#add_note').closest('div').hide();         
                         $('#editorRow').hide();                       
                         $('.note-display').remove();
                         $('#SelectedProductName').text(payload.product_name);
                         $('.bom_material_section').show();
                         $('.right_section').removeClass('is-visible'); // Hides the raw material sidebar
                         $('.middle_body').css('width', '82%');
                         
                         // Re-enable the back button after saving
                         $('.hand-o-left').css({
                            'pointer-events': 'auto',
                            'opacity': '1'
                         });


                        //  Fetch latest BOM list from backend
                        const productId = response.bomData.product_id;
                        if (productId) {
                            localStorage.setItem('bomDetails', JSON.stringify(response.bomData));

                            fetchAndRenderBOMList(productId, response.bomData.id); // Highlight latest saved
                        }

                    }, 1500);

                    // setTimeout(function () {
                    //    window.location.reload(true);
                    // }, 900);
                } else {
                    alert('Save failed: ' + response.message);
                }
            },
            error: function () {
                alert('Server error while saving.');
            }
        });
    });
    function fetchAndRenderBOMList(productId, highlightBomId = null) {
    $.ajax({
        url: site.base_url + "Bill_of_material/GetBomDetailsByProductId",
        type: 'GET',
        data: { productId },
        dataType: 'json',
        success: function (data) {
            if (data.bomDetails && Array.isArray(data.bomDetails)) {
                const bomList = data.bomDetails;
                localStorage.setItem('bomMaterialsDetails', JSON.stringify(bomList)); // Optional

                $('#dynamicTable thead').html(`
                    <tr>
                        <th scope="col">Version</th>
                        <th scope="col">BOM_Id</th>
                        <th scope="col">Status</th>
                        <th scope="col">Date</th>
                        <th scope="col">Actions</th>
                    </tr>
                `);

                const tbody = $('#dynamicTable tbody');
                tbody.empty();

                bomList.forEach((item, index) => {
                    const isActive = item.is_active == "1";
                    const statusText = isActive ? 'Active' : 'Inactive';
                    const statusClass = isActive ? 'text-success' : 'text-danger';

                    const highlightClass = (item.id == highlightBomId) ? 'table-success' : '';

                    const row = `
                        <tr data-index="${index}" class="${highlightClass}">
                            <td>${item.version_no || '-'}</td>
                            <td>${item.id || '-'}</td>
                            <td class="${statusClass}">${statusText}</td>
                            <td>${item.created_at || '-'}</td>
                            <td class="text-center">
                                <i class="fa fa-eye text-primary me-2 view-icon" title="View" data-id="${item.id}" style="cursor:pointer;"></i>
                                <i class="fa fa-edit text-success edit-icon" title="Edit" data-id="${item.id}" style="cursor:pointer;"></i>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                $('#dynamicTable tbody').html('<tr><td colspan="5">No BOM found.</td></tr>');
            }
        },
        error: function () {
            alert('Failed to fetch BOM list.');
        }
    });
}

    /////////////////////////////////////////////////////// REMOVE RECORD ////////////////////////////////////////////////
    // jQuery code to remove table row when delete button is clicked
    $(document).on('click', '.delete-btn', function () {
        var $row = $(this).closest('tr');
        var productName = $row.find('td:nth-child(2)').text(); // Adjust selector to target the product name
        $row.remove();
        var Open_orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
        Open_orders = Open_orders.filter(function (order) {
            return order.name !== productName; // Remove item based on product_name
        });
        localStorage.setItem('currentOrderItems', JSON.stringify(Open_orders));
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
        // You can update a UI element showing the row count, or perform any other action here
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
    function getProcurementOrderList(selectedHorizonatlTabValue) {
        $.ajax({
            url: site.base_url + "Bill_of_material/getProcurementOrderList",
            method: 'GET',
            data: {
                orderStatus: selectedHorizonatlTabValue
            },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    if (selectedHorizonatlTabValue) {
                        switch (selectedHorizonatlTabValue) {
                            case 'current_order':
                                (localStorage.getItem('addBomItemClicked')=== 'true')?$(".right_section").addClass('is-visible') :$(".right_section").removeClass('is-visible');
                                $('#dynamicTable tfoot').empty();
                                $('#dynamicTable tbody').empty();
                                $('.hide-div').show();
                                $('#saveRawData').show();
                                $(".bom_material_section_item").hide();
                                $(".bom_material_section").show();
                                
                                // Re-enable the back button when switching tabs
                                $('.hand-o-left').css({
                                   'pointer-events': 'auto',
                                   'opacity': '1'
                                });
                                if (clicked === 'true') {
                                    $(".bom_material_section_item").show();
                                    $(".bom_material_section").hide();
                                    loadOrderItems();
                                } else {

                                    loadMaterials();
                                }
                                break;

                            default:
                                console.error('Unknown order type');
                                break;
                        }
                    }

                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
        window.onload = function () {
        $('.middle_body').css('width', '82%');
        
        // Ensure the back button is enabled on page load
        $('.hand-o-left').css({
           'pointer-events': 'auto',
           'opacity': '1'
        });
    };
    });

document.addEventListener('DOMContentLoaded', function () {
    const specificLinks = document.querySelectorAll('#clickMe');
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
    // ✅ Trigger click on first #clickMe button on page load
    if (specificLinks.length > 0) {
        specificLinks[0].click();
    }
    specificLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            applyWidth();
        });
    });

    document.addEventListener('click', function (e) {
        const target = e.target;

        if (target.matches('#repeat_order, .Update_rec-order-link, .nav-link[value="current_order"]')) {
            e.preventDefault();
            resetWidth();
        }
    });
    // Prevent typing negative sign for wastage and quantity inputs
    $(document).on('keypress', '.wastage-input, .quantity-input', function (e) {
        if (e.key === '-' || e.keyCode === 45) {
            e.preventDefault();
        }
    });

    $(document).on('blur', '.quantity-input, .wastage-input, .minqtyset, .Saleunitset', function () {
        let val = parseFloat($(this).val());
        if (!isNaN(val)) {
            if ($(this).hasClass('quantity-input') || $(this).hasClass('wastage-input')) {
                val = Math.max(val, 0); // force value to be >= 0
                $(this).val(quantityDecimal(val));
            } else {
                $(this).val(quantityDecimal(val));
            }
        }
    });

});
