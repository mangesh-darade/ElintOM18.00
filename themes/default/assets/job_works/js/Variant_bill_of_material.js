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
    if (x == null || x === '') return '';
    var n = parseFloat(x);
    if (isNaN(n)) return '0';

    var decimals = 2;
    if (site && site.settings) {
        if (site.settings.qty_decimals !== undefined && site.settings.qty_decimals !== null) {
            decimals = parseInt(site.settings.qty_decimals);
        } else if (site.settings.decimals !== undefined && site.settings.decimals !== null) {
            decimals = parseInt(site.settings.decimals);
        }
    }
    return n.toFixed(decimals);
}

function preserveDecimal(x) {
    if (x == null || x === '') return '';
    var s = String(x).trim();
    if (s === '') return '';
    if (s.indexOf('.') === -1) return s;
    s = s.replace(/0+$/, '');
    s = s.replace(/\.$/, '');
    return s;
}
var buttonText = '';
var buttonText_changed = '';

function clearProcessSelectValidation() {
    $('#processSelectError').hide().text('');
    $('#intermediateProductsDropdown').css({ borderColor: '', boxShadow: '' });
}

function showProcessSelectValidation() {
    $('#processSelectError').text('Please select Process first.').show();
    $('#intermediateProductsDropdown').css({
        borderColor: '#d9534f',
        boxShadow: 'inset 0 1px 1px rgba(0,0,0,.075), 0 0 6px #ce8483'
    }).focus();
}

function hideRawMaterialSidebar() {
    $('.right_section').removeClass('bom-raw-sidebar-visible');
    $('.middle_body').css('width', '82%');
}

function showRawMaterialSidebar() {
    $('.middle_body').css('width', '66%');
    $('.right_section').addClass('bom-raw-sidebar-visible');
}

var rawProductsLoadPromise = null;

$(document).ready(function () {
    hideRawMaterialSidebar();
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
    localStorage.removeItem('variantChoice'); // Clear variant choice on reset
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
                $('.subcategoriesList').append('<li class="btn btn-sty subcategory Sub_Categoryset" style="display:none;" data-category-id="' + category.category_id + '">' + subcategory.subcategory_name + '</li>');
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
        if (localStorage.getItem('addBomItemClicked') === 'true') {
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
        showRawMaterialSidebar();
        var id = $(this).data('id');
        $('.bom_material_section_item').show();
        $('.bom_material_section').hide();
        $('#saveRawData').show();
        $('#add_note').closest('div').show();
        $('#is_batch_only_toggle').prop('checked', true);
        localStorage.setItem('addBomItemClicked', 'true');
        localStorage.setItem('addBomItemClickTime', new Date().toISOString());
        // $.ajax({
        //         url: site.base_url + "Variant_bill_of_materials/getLatestBomVersion",
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
            url: site.base_url + "Variant_bill_of_materials/getBomMaterialItems", // Updated endpoint
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
                const uniqueItems = []; // To store unique items
                const seenItems = new Set(); // To track duplicates

                items.forEach(item => {
                    // Create a unique key for each item (material + variant combination)
                    const uniqueKey = `${item.material_id}_${item.variant_option_id || 'N/A'}`;

                    // Skip if we've already seen this combination or if essential data is missing
                    if (seenItems.has(uniqueKey) || !item.raw_material) {
                        return;
                    }

                    seenItems.add(uniqueKey);

                    // Add variant information to the item
                    uniqueItems.push({
                        rawProductId: item.material_id,
                        rawCode: item.product_code,
                        rawProductName: item.raw_material,
                        quantity: item.quantity_required,
                        unitId: item.uom_id || '',
                        wastage_percent: item.wastage_percent,
                        is_alternative: item.is_alternative,
                        variant_option_id: item.variant_option_id || null,
                        variant_name: item.variant_name || null,
                        is_existing: true
                    });
                });

                // Store the items with variant information
                localStorage.setItem('productRawDetailsList', JSON.stringify(uniqueItems));

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

                // Set note into Redactor editor if available
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

                // Set batch and sale unit information
                if (items.length > 0) {
                    $('.min_batch_qty').val(preserveDecimal(items[0]['min_batch_qty'] || ''));

                    // Safely handle sales_units which might be undefined
                    if (items[0]['sales_units']) {
                        const salesUnits = items[0]['sales_units'].split(" ");
                        $('.sale_unit').val(preserveDecimal(salesUnits[0] || ''));
                        if (salesUnits[1]) {
                            $('#sale_unit_uom').val(salesUnits[1]);
                        }
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error("Error loading BOM items:", error);
                alert("Failed to load BOM items. Please try again.");
            }
        });

        $('.hand-o-left').css({
            'pointer-events': 'none',
            'opacity': '0.5'
        });
    });
    $('#addBomItemBtn').on('click', function () {
        // When Process dropdown is shown for this product, require a process before Add
        const $processContainer = $('#intermediateProductsContainer');
        if ($processContainer.length && $processContainer.is(':visible')) {
            const selectedProcessId = $('#intermediateProductsDropdown').val();
            if (!selectedProcessId) {
                showProcessSelectValidation();
                return;
            }
        }
        clearProcessSelectValidation();

        showRawMaterialSidebar();
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
                url: site.base_url + "Variant_bill_of_materials/getLatestBomVersion",
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
        // If a process is selected, auto-add suffix products for this main product
        const selectedProcessId = $('#intermediateProductsDropdown').val();
        if (selectedProcessId && selectedBomProduct.id) {
            checkProductWithSuffix(selectedBomProduct.id, selectedProcessId, function () {
                loadOrderItems();
            });
        } else {
            loadOrderItems();
        }

    });
    const clicked = localStorage.getItem('addBomItemClicked');

    function getProducts(CategoryID, SubCategoryID) {
        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/GetProductsbyCategoriesID",
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

                    // Get product ID from data attribute
                    const productId = $(this).data('product-id');


                    localStorage.setItem("activecategory", productId);

                    selectedCategoryProductName = product.name;  // Store selected category
                    selectedCategoryProductid = product.id;  // Store selected category

                    // Store the selected product for BOM
                    selectedBomProduct.id = product.id;
                    selectedBomProduct.name = product.name;

                    // Important: ensure we are not stuck in add mode when switching products
                    // This flag forces the right-side item editor to stay open; clear it here
                    localStorage.removeItem('addBomItemClicked');
                    localStorage.removeItem('addBomItemClickTime');
                    hideRawMaterialSidebar();
                    $('.bom_material_section_item').hide();
                    $('.bom_material_section').show();

                    // Fetch variants for the main product (Kurti, etc.) - NOT for raw materials
                    fetchMainProductVariants(productId, function (variants) {

                        // Store variants globally for this product
                        selectedBomProduct.variants = variants;

                        // Continue with BOM details
                        getBomdetailsByProductId(product.id, product.name);
                    });
                });

            }
        });
    }
    let selectedRawName = null;
    function getRowProductsList(requests) {
        if (!requests || !Array.isArray(requests)) {
            return;
        }
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

                    // Get product ID from data attribute for logging
                    const rawMaterialId = $(this).data('product-id');
                    // Directly call updateSelectedNames - it will use main product variants (NO API CALL)
                    updateSelectedNames(product);
                    
                    // Show confirmation dialog for variants
                    // showVariantConfirmationDialog(product);
                });
            }
        });
    }

    function getBomdetailsByProductId(productId, productName) {
        // Clear variant choice when selecting a new product
        localStorage.removeItem('variantChoice');
        
        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/GetBomDetailsByProductId",
            method: 'GET',
            data: {
                productId: productId,
                productName: productName,
                job_work_id: $('#intermediateProductsDropdown').val() || ''
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
                            if (bom.product_id) {
                                loadIntermediateProducts(bom.product_id);
                            }
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
                                    url: site.base_url + "Variant_bill_of_materials/getLatestBomVersion",
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
                                url: site.base_url + "Variant_bill_of_materials/getLatestBomVersion",
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
        // Use main product variants for ALL raw materials (no individual API calls)
        const mainProductVariants = selectedBomProduct.variants || [];

        products.forEach(product => {

            // Only process if we have main product variants available
            if (mainProductVariants && mainProductVariants.length > 0) {
                // If product has a variant_option_id, find the matching variant
                if (product.variant_option_id) {
                    const matchingVariant = mainProductVariants.find(v => v.id == product.variant_option_id);
                    if (matchingVariant) {

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
                            wastage_percent: product.wastage_percent,
                            variant_option_id: product.variant_option_id,
                            variant_name: matchingVariant.name,
                            variants: mainProductVariants
                        };

                        // Find if exact item exists
                        const existingIndex = storedItems.findIndex(item =>
                            item.productId === productRawDetails.productId &&
                            item.rawProductId === productRawDetails.rawProductId &&
                            item.type === productRawDetails.type &&
                            item.unitId === productRawDetails.unitId &&
                            item.wastage_percent === productRawDetails.wastage_percent &&
                            item.variant_option_id === productRawDetails.variant_option_id
                        );

                        if (existingIndex !== -1) {
                            // If same item with same variant, sum the quantity
                            storedItems[existingIndex].quantity = quantityDecimal(
                                parseFloat(storedItems[existingIndex].quantity) + parseFloat(productRawDetails.quantity)
                            );
                            storedItems[existingIndex].variants = mainProductVariants;
                        } else {
                            // Otherwise, treat it as a new item
                            storedItems.push(productRawDetails);
                        }
                    } else {
                        console.log(" No matching variant found for variant_option_id:", product.variant_option_id);
                    }
                } else {
                    console.log("Product has no variant_option_id, skipping (no 'N/A' row)");
                }
            } else {
                console.log("No main product variants available, skipping product");
            }
        });

        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
        loadOrderItems(); // Refresh UI immediately (no waiting for API calls)
    }
    async function getBomMaterialItemByBomId(bomId, productName) {
        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/getBomMaterialItems",
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
        let bomDetails = {};

        // Parse BOM details if available
        if (bom && bom !== 'null') {
            bomDetails = JSON.parse(bom);
            $('#selected_product_name').text(bomDetails.name || '');
        }

        // API endpoint (single URL since both branches were same)
        const apiURL = site.base_url + "Variant_bill_of_materials/getBomMaterialItems";

        $.ajax({
            url: apiURL,
            type: 'POST',
            data: {
                bom_id: itemId,
                [csrf_token_name]: csrf_hash
            },
            dataType: 'json',
            success: function (response) {
                const items = response?.bomItems || [];

                // Display product name, process (from job_works → sma_standard_job_works), and status
                const statusText = items[0]?.is_active == 1 ? 'Active' : 'Inactive';
                const processName = (items[0] && items[0].process_name) ? String(items[0].process_name).trim() : '';
                const processHtml = processName
                    ? ` <span class="text-secondary">&middot; ${$('<span/>').text(processName).html()}</span>`
                    : '';
                $('#selected_product_name').html(`
                    ${bomDetails.name || ''}${processHtml}
                    <span class="text-muted"> (${statusText})</span>
                `);

                // Show created date only (no time)
                const createdDate = items[0]?.created_at
                    ? items[0].created_at.split(' ')[0]
                    : 'N/A';
                $('#created_at').text(createdDate);

                // Initialize or reset DataTable
                if ($.fn.DataTable.isDataTable('#itemTable')) {
                    table = $('#itemTable').DataTable();
                    table.clear().draw();
                } else {
                    table = $('#itemTable').DataTable({
                        paging: false,
                        searching: false,
                        info: false,
                        columns: [
                            { title: "Name" },
                            // { title: "Ref. No." },
                            { title: "Variant" },
                            { title: "Required Qty." },
                            { title: "UOM" },
                            { title: "Wastage (%)" },
                            // { title: "Is Alternative" }
                        ]
                    });
                }

                // Basic info
                // if (items.length > 0) {
                //     $('#min_batch_qty_new').text(items[0].min_batch_qty || '');
                //     $('#sale_unit_view').text(items[0].sales_units || '');
                // }

                // Filter unique items (material + variant combo)
                const uniqueItems = [];
                const seenKeys = new Set();

                items.forEach(item => {
                    if (!item.raw_material) return;
                    const key = `${item.material_id}_${item.variant_option_id || 'N/A'}`;
                    if (!seenKeys.has(key)) {
                        seenKeys.add(key);
                        uniqueItems.push(item);
                    }
                });

                // Sort: items with variants first
                uniqueItems.sort((a, b) => {
                    if (a.variant_name && !b.variant_name) return -1;
                    if (!a.variant_name && b.variant_name) return 1;
                    return 0;
                });

                // Populate DataTable
                uniqueItems.forEach(item => {
                    const bomRef = item.bom_ref || '-';
                    const variantDisplay = item.variant_name
                        ? `<span class="badge bg-primary">${item.variant_name}</span>`
                        : `<span class="badge bg-secondary">N/A</span>`;

                    table.row.add([
                        `<div class="text-left">${item.raw_material}</div>`,
                        // `<div class="text-center">${bomRef}</div>`,
                        `<div class="text-center">${variantDisplay}</div>`,
                        `<div class="text-center">${preserveDecimal(item.quantity_required || 0)}</div>`,
                        `<div class="text-left">${item.unit_name || ''}</div>`,
                        `<div class="text-right">${preserveDecimal(item.wastage_percent || 0)}%</div>`,
                        // `<div class="text-left">${item.is_alternative == 1 ? 'Yes' : 'No'}</div>`
                    ]);
                });

                table.draw();

                // Handle notes display
                $('.note-display').remove();
                const notes = items[0]?.notes;
                if (notes) {
                    $('#itemTable').after(`
                        <div class="note-display" 
                            style="margin-top: 20px; padding: 10px; border-top: 1px solid #ddd;">
                            <strong>Note:</strong><br>${notes}
                        </div>
                    `);
                }

                console.log(`Populated modal with ${uniqueItems.length} unique items (filtered from ${items.length})`);
                $('#itemModal').modal('show');
            },
            error: function (xhr, status, error) {
                console.error(' AJAX Error:', status, error);
                alert('Failed to load BOM material items.');
            }
        });
    });
    //////////////////////////////////////////////////////////////////////////////////// Load BOM Materials //////////////////////////////////////////
    function loadMaterials() {
        hideRawMaterialSidebar();
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
                // Hide Min Qty and reallocate width
                $('#minQtyContainer').hide();
                $('.uom-field').removeClass('col-md-3').addClass('col-md-4');
                $('.sale-uom-field').removeClass('col-md-3').addClass('col-md-5');
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
        // Check if this is being called from dropdown selection
        const isDropdownSelection = localStorage.getItem('dropdownSelectionInProgress') === 'true';
        // Load intermediate products if we have a product ID
        if (bom.product_id) {
            if (!isDropdownSelection) {
                loadIntermediateProducts(bom.product_id);
            } 
        }
        // Clear the dropdown selection flag
        localStorage.removeItem('dropdownSelectionInProgress');

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
        var sale_uom = (bom.sales_units || "").match(/^([\d.]+)\s*(.*)$/);
        if (sale_uom) {
            var qty = sale_uom[1]; // Now correctly captures "1.5" instead of just "1"
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
        $('#min_batch_qty').val(preserveDecimal(bom.min_batch_qty || ''));
    }

    const storedBom = localStorage.getItem('bomDetails');
    if (storedBom) {
        const bom = JSON.parse(storedBom);
        updateBomUI(bom);
    }
    
    // Function to show confirmation dialog for variants
    function showVariantConfirmationDialog(product) {
        if (!selectedCategoryProductid || !selectedCategoryProductName) {
            showCustomPopup('Please select a product', 'error');
            return;
        }
        // Check if main product has variants
        const mainProductVariants = selectedBomProduct.variants || [];
        if (mainProductVariants.length === 0) {
            // No variants available, proceed without confirmation
            updateSelectedNames(product);
            return;
        }
        // Check if user has already made a choice for previous raw materials
        const variantChoice = localStorage.getItem('variantChoice');
        if (variantChoice) {
            // User has already made a choice, apply the same
            if (variantChoice === 'withVariants') {
                updateSelectedNames(product);
            } else {
                addProductWithoutVariantsOnly(product);
            }
            return;
        }
        // Show custom confirmation popup for first time
        showVariantPopup(product, mainProductVariants);
    }
    
    // Function to show custom variant confirmation popup
    function showVariantPopup(product, variants) {
        // Create popup HTML
        const popupHtml = `
            <div id="variantConfirmPopup" style=" position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999; min-width: 300px; max-width: 400px; ">
                <h2 style="margin: 0 0 15px 0; color: #333;">Add Variants?</h2>
                <p style="margin: 0 0 10px 0; color: #666;">
                    Do you want to add variants for "<strong>${product.name}</strong>"?
                </p>
                <p style="margin: 0 0 10px 0; color: #888; font-size: 14px;">
                    Available variants: ${variants.map(v => v.name).join(', ')}
                </p>
                <p style="margin: 0 0 20px 0; color: #e67e22; font-size: 13px; font-style: italic;">
                    This choice will be applied to all subsequent raw materials
                </p>
                <div style="text-align: right; margin-top: 20px;">
                    <button id="cancelVariantBtn" style="background: #6c757d; color: white; border: none; padding: 8px 16px; margin-right: 10px; border-radius: 4px; cursor: pointer; ">
                    No</button>
                    <button id="confirmVariantBtn" style=" background: #007bff;color: white; border: none;padding: 8px 16px; border-radius: 4px; cursor: pointer; ">
                    Yes</button>
                </div>
            </div>
            <div id="variantConfirmOverlay" style="position: fixed;top: 0;left: 0;width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; "></div>
        `;
        // Remove existing popup if any
        $('#variantConfirmPopup, #variantConfirmOverlay').remove();
        // Add popup to body
        $('body').append(popupHtml);
        // Handle button clicks
        $('#confirmVariantBtn').click(function() {
            $('#variantConfirmPopup, #variantConfirmOverlay').remove();
            // Save user's choice for future raw materials
            localStorage.setItem('variantChoice', 'withVariants');
            updateSelectedNames(product);
        });
        $('#cancelVariantBtn').click(function() {
            $('#variantConfirmPopup, #variantConfirmOverlay').remove();
            // Save user's choice for future raw materials
            localStorage.setItem('variantChoice', 'withoutVariants');
            addProductWithoutVariantsOnly(product);
        });
        // Close on overlay click
        $('#variantConfirmOverlay').click(function() {
            $('#variantConfirmPopup, #variantConfirmOverlay').remove();
        });
    }
    
    // Function to show custom popup (for error messages)
    function showCustomPopup(message, type = 'info') {
        const popupHtml = `
            <div id="customPopup" style="position: fixed;top: 20px; right: 20px;
              background: ${type === 'error' ? '#dc3545' : '#28a745'}; color: white;padding: 15px 20px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 10000; max-width: 300px; ">
              ${message}
            </div>
        `;
        $('#customPopup').remove();
        $('body').append(popupHtml);
        // Auto remove after 3 seconds
        setTimeout(function() {
            $('#customPopup').fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Function to add raw material without variants (for "No" response)
    function addProductWithoutVariantsOnly(product) {
        // Get current list from localStorage or initialize
        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
        const productRawDetails = {
            productId: selectedCategoryProductid,
            productName: selectedCategoryProductName,
            rawProductId: product.id,
            rawProductName: product.name,
            type: product.type,
            quantity: product.quantity || 1,
            unitId: product.unit,
            unit_name: product.unit_name,
            rawCode: product.code,
            variant_option_id: null,
            variant_name: 'N/A',
            variants: []
        };
        // Check if this product already exists (without variants)
        const existingIndex = storedItems.findIndex(item =>
            item.productId === productRawDetails.productId &&
            item.rawProductId === productRawDetails.rawProductId &&
            !item.variant_option_id
        );
        if (existingIndex !== -1) {
            // If exists, increase quantity
            storedItems[existingIndex].quantity = parseFloat(storedItems[existingIndex].quantity) + parseFloat(productRawDetails.quantity);
        } else {
            // Add new product without variants
            storedItems.push(productRawDetails);
        }
        // Save updated list and reload
        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
        // Reload table
        loadOrderItems();
    }
    
    // Helper function to update display of selected names
    function updateSelectedNames(product) {

        if (!selectedCategoryProductid || !selectedCategoryProductName) {
            alert('Please select a product');
            return;
        }

        // Use variants from the main product (Kurti, etc.) not from raw material
        const mainProductVariants = selectedBomProduct.variants || [];
        if (mainProductVariants.length === 0) {
            console.log(" No variants available for main product - will not create grid");
        } else {
            console.log(" Will create", mainProductVariants.length, "rows for variants:", mainProductVariants.map(v => v.name));
        }

        processProductWithVariants(product, mainProductVariants);
    }

    // Helper function to process product with variants
    function processProductWithVariants(product, variants) {
        // Get current list from localStorage or initialize
        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];

        if (variants && variants.length > 0) {
            // Filter out any 'N/A' variants and empty/undefined variants
            const validVariants = variants.filter(variant =>
                variant && variant.name && variant.name.trim() !== '' && variant.name.trim() !== 'N/A'
            );

            if (validVariants.length > 0) {
                // Only add rows if we have valid variants
                validVariants.forEach(variant => {
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
                        variant_option_id: variant.id,
                        variant_name: variant.name,
                        variants: validVariants
                    };

                    // Check if this specific variant + raw material combination already exists
                    const existingIndex = storedItems.findIndex(item =>
                        item.productId === productRawDetails.productId &&
                        item.rawProductId === productRawDetails.rawProductId &&
                        item.variant_option_id === variant.id
                    );

                    if (existingIndex !== -1) {
                        // If exists, increase quantity
                        storedItems[existingIndex].quantity = parseFloat(storedItems[existingIndex].quantity) + parseFloat(productRawDetails.quantity);
                    } else {
                        // Add new variant row
                        storedItems.push(productRawDetails);
                    }
                });
            } else {
                // No valid variants - add single row with N/A
                addProductWithoutVariant(storedItems, product);
            }
        } else {
            // No variants array - add single row with N/A
            addProductWithoutVariant(storedItems, product);
        }

        // Save updated list and reload
        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
        // Reload table
        loadOrderItems();
    }

    // Helper function to add a product without variants
    function addProductWithoutVariant(storedItems, product) {
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
            variant_option_id: null,
            variant_name: 'N/A',
            variants: []
        };

        // Check if this product already exists (without variants)
        const existingIndex = storedItems.findIndex(item =>
            item.productId === productRawDetails.productId &&
            item.rawProductId === productRawDetails.rawProductId &&
            !item.variant_option_id
        );

        if (existingIndex !== -1) {
            storedItems[existingIndex].quantity = parseFloat(storedItems[existingIndex].quantity) + parseFloat(productRawDetails.quantity);
        } else {
            storedItems.push(productRawDetails);
        }
    }

    // Function to fetch variants for MAIN PRODUCT only (not raw materials)
    function fetchMainProductVariants(mainProductId, callback) {

        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/getRawMaterialVariants",
            method: 'GET',
            data: {
                rawMaterialId: mainProductId // Using same parameter name for backend compatibility
            },
            dataType: 'json',
            success: function (response) {
                const variants = response.variants || [];
                callback(variants);
            },
            error: function (xhr, status, error) {
                callback([]); // Return empty array on error
            }
        });
    }

    // Handle intermediate product dropdown change
    // Process dropdown – update inline label and reload BOM list for this product + process
    $(document).on('change', '#intermediateProductsDropdown', function() {
        if ($(this).val()) {
            clearProcessSelectValidation();
        }
        const selectedProcessName = $(this).find('option:selected').text();
        const cleanName = selectedProcessName ? selectedProcessName.replace(/\s*\([^)]*\)$/, '') : '';
        $('#product_process_inline').text(cleanName);
        const pid = selectedBomProduct && selectedBomProduct.id ? selectedBomProduct.id : $('#product_id').val();
        const jid = $(this).val();
        if (!pid || localStorage.getItem('addBomItemClicked') === 'true') {
            return;
        }
        // No process selected: show all BOMs for product (no job_work filter)
        if (!jid) {
            fetchAndRenderBOMList(pid, null, null, true);
            return;
        }
        fetchAndRenderBOMList(pid, null, jid, true);
    });
    function getRowProducts() {
        if (rawProductsLoadPromise) {
            return rawProductsLoadPromise;
        }
        rawProductsLoadPromise = $.ajax({
            url: site.base_url + "Variant_bill_of_materials/GetRawProducts",
            method: 'GET',
            dataType: 'json'
        }).done(function (response) {
            var allProducts = [];
            if (Array.isArray(response)) {
                response.forEach(function (rowProduct) {
                    var rowProductDetails = rowProduct.Rawproducts || rowProduct.rawproducts;
                    if (Array.isArray(rowProductDetails)) {
                        allProducts = allProducts.concat(rowProductDetails);
                    }
                });
            }
            getRowProductsList(allProducts);
        }).fail(function (xhr, status, error) {
            console.error("AJAX error:", status, error);
            console.error("Response Text:", xhr.responseText);
        }).always(function () {
            rawProductsLoadPromise = null;
        });
        return rawProductsLoadPromise;
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
        $('#saveRawData').show();
        $('#add_note').show(); // Clear previous header
        const orderdetails = `
        <tr>
            <th scope="col">Raw Material CD</th>
            <th scope="col">Material Name</th>
            <th scope="col">Variants</th>
            <th scope="col">Required Qty. </th>
            <th scope="col">UOM</th>
            <th scope="col">Wastage %</th>
            <th scope="col" class="text-center no-print bom-col-delete" title="Remove line"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove line</span></th>
        </tr>`;
        $('#dynamicTable thead').html(orderdetails);
        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];

        // Filter out items with N/A variants
        // storedItems = storedItems.filter(item => 
        //     item.variant_name !== 'N/A' && 
        //     item.variant_name !== undefined && 
        //     item.variant_name !== null
        // );

        $('#dynamicTable tbody').html(''); // Clear previous rows

        storedItems.forEach((item, index) => {
            const row = `
            <tr data-index="${index}" class="${item.is_alternative == 1 ? 'alt-row' : ''}">
                <td class="text-start">
                    <input type="hidden" name="product_code[]" value="${item.rawProductId}">
                    ${item.rawCode}
                </td>
                <td class="text-start" style="text-align: start !important;">
                    <input type="hidden" name="rawProductId[]" value="${item.rawProductId}">
                    ${item.rawProductName}
                </td>
                <td class="text-center">
                    <input type="hidden" name="variant_label[]" value="${(item.variant_name || '').replace(/"/g, '&quot;')}">
                    <input type="hidden" name="Variants[]" value="${item.variant_option_id || ''}">
                    <span class="variant-display badge ${item.variant_name && item.variant_name !== 'N/A' ? 'bg-primary' : 'bg-secondary'}">
                        ${item.variant_name || 'N/A'}
                    </span>
                </td>
                <td class="text-end">
                    <input type="number" step="${site && site.settings && site.settings.qty_decimals ? (site.settings.qty_decimals == 0 ? '1' : '0.' + '0'.repeat(site.settings.qty_decimals - 1) + '1') : '0.01'}" name="quantity[]" class="form-control text-end quantity-input" value="${item && item.is_existing ? preserveDecimal(item.quantity) : quantityDecimal(item.quantity)}" data-existing="${item && item.is_existing ? '1' : '0'}" style="width: 100px;" required min="0">
                </td>
                <td class="text-center">
                    <select name="unit[]" class="form-select unit-select text-center" style="width: 100px;" required>
                        ${units.map(uom => `
                            <option value="${uom.id}" ${uom.id == item.unitId ? 'selected' : ''}>${uom.name}</option>
                        `).join('')}
                    </select>
                </td>
                <td class="text-end">
                    <input type="number" step="${site && site.settings && site.settings.qty_decimals ? (site.settings.qty_decimals == 0 ? '1' : '0.' + '0'.repeat(site.settings.qty_decimals - 1) + '1') : '0.01'}" name="wastage[]" class="form-control text-end wastage-input" value="${item && item.is_existing ? preserveDecimal(item.wastage_percent || 0) : quantityDecimal(parseFloat(item.wastage_percent || 0))}" data-existing="${item && item.is_existing ? '1' : '0'}" style="width: 80px;" required min="0">
                </td>
                <td class="text-center no-print bom-col-delete">
                    <button type="button" class="bom-line-delete-btn" title="Remove row"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </td>
            
            </tr>
            `;
            $('#dynamicTable tbody').append(row);
        });

        // Rest of the function remains the same...
        attachDeleteEvents();

        // Highlight row when "Yes" is selected
        $(document).off('change', '.alt-select').on('change', '.alt-select', function () {
            const row = $(this).closest('tr');
            if ($(this).val() == "1") {
                row.addClass('alt-row');
            } else {
                row.removeClass('alt-row');
            }
        });
    }

    // Auto-add suffix products for selected process (Input / DCC flow)
    function checkProductWithSuffix(mainProductId, processId, callback) {
        if (!mainProductId || !processId) {
            if (typeof callback === 'function') {
                callback();
            }
            return;
        }
        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/checkProductWithSuffix",
            method: 'GET',
            data: {
                main_product_id: mainProductId,
                process_id: processId
            },
            dataType: 'json',
            success: function (resp) {
                try {
                    if (resp && resp.status === 'success' && resp.suffix_products && resp.suffix_products.length) {
                        let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
                        (resp.suffix_products || []).forEach(function (sp) {
                            var vid = sp.matched_variant_id ? parseInt(sp.matched_variant_id, 10) : 0;
                            if (isNaN(vid)) vid = 0;
                            var vLabel = (sp.matched_variant_name && String(sp.matched_variant_name).trim() !== '')
                                ? sp.matched_variant_name
                                : '';
                            if (!vLabel) {
                                // fallback to tail of product name
                                var parts = String(sp.name || '').split('_');
                                var tail  = parts.length ? parts[parts.length - 1].trim() : '';
                                vLabel = tail || 'N/A';
                            }

                            var productRawDetails = {
                                productId: selectedCategoryProductid,
                                productName: selectedCategoryProductName,
                                rawProductId: sp.id,
                                rawProductName: sp.name,
                                type: 'raw',
                                quantity: 1,
                                unitId: null,
                                unit_name: '',
                                rawCode: sp.code || '',
                                variant_option_id: vid,
                                variant_name: vLabel,
                                variants: []
                            };

                            var existingIndex = storedItems.findIndex(function (item) {
                                return item.productId === productRawDetails.productId &&
                                    item.rawProductId === productRawDetails.rawProductId &&
                                    String(item.variant_option_id || 0) === String(productRawDetails.variant_option_id || 0);
                            });
                            if (existingIndex !== -1) {
                                storedItems[existingIndex].quantity = parseFloat(storedItems[existingIndex].quantity) + 1;
                            } else {
                                storedItems.push(productRawDetails);
                            }
                        });
                        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
                    }
                } finally {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function () {
                if (typeof callback === 'function') {
                    callback();
                }
            }
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
        const updatedIsAlt = Number($row.find('select.alt-select').val());

        storedItems[index].quantity = updatedQuantity;
        storedItems[index].unitId = updatedUnitId;
        storedItems[index].wastage_percent = updatedWastage;
        storedItems[index].is_alternative = updatedIsAlt;
        // variant_option_id remains unchanged as it's fixed per row

        localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));

        // loadOrderItems();
    });

    function attachDeleteEvents() {
        $('.bom-line-delete-btn').off('click').on('click', function () {
            const row = $(this).closest('tr');
            const index = row.data('index');
            let storedItems = JSON.parse(localStorage.getItem('productRawDetailsList')) || [];
            // Remove the item at that index
            storedItems.splice(index, 1);
            // Update localStorage
            localStorage.setItem('productRawDetailsList', JSON.stringify(storedItems));
            // Reload table
            loadOrderItems();
        });
    }

    //////////////////////////////////////////////////////////////  SAVE Raw ITEMS ///////////////////////////////////////////////////////
    let isSaving = false; // Flag to prevent multiple submissions

    $('#saveRawData').on('click', function () {
        // Prevent multiple clicks
        if (isSaving) {
            return;
        }

        isSaving = true;
        $(this).prop('disabled', true).text('Saving...');

        localStorage.removeItem('bomDetails');
        const orderData = [];
        const batchdata = [];

        // General header data
        const productId = $('#product_id').val();
        const jobWorkId = $('#intermediateProductsDropdown').val(); // selected process/job work id
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
            const isAlternative = $(this).find('select[name="is_alternative[]"]').val();
            const variantLabel = $(this).find('input[name="variant_label[]"]').val() || '';
            const variantOptionIdRaw = $(this).find('input[name="Variants[]"]').val(); // Get variant option ID from hidden input
            const variantOptionId = variantOptionIdRaw === '' || variantOptionIdRaw == null ? 0 : parseInt(variantOptionIdRaw, 10);
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
                is_alternative: isAlternative,
                variant_option_id: isNaN(variantOptionId) ? 0 : variantOptionId,
                variant_label: variantLabel
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
            job_work_id: jobWorkId,
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
            isSaving = false;
            $('#saveRawData').prop('disabled', false).text('Save');
            return;
        }
        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/addProddUnitBomData",
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
                        hideRawMaterialSidebar();

                        // Load process list for this product; after load, select the saved job work (not product id)
                        loadIntermediateProducts(payload.productId, payload.job_work_id);

                        //  Fetch latest BOM list from backend
                        const productId = response.bomData.product_id;
                        if (productId) {
                            localStorage.setItem('bomDetails', JSON.stringify(response.bomData));

                            fetchAndRenderBOMList(
                                productId,
                                response.bomData.id,
                                payload.job_work_id || $('#intermediateProductsDropdown').val() || null
                            ); // Highlight latest saved, same process filter
                        }

                        // Reset the save button state
                        isSaving = false;
                        $('#saveRawData').prop('disabled', false).text('Save');

                    }, 1500);
                } else {
                    Toastify({
                        text: response.message || 'Failed to save BOM',
                        duration: 3000,
                        gravity: 'top',
                        position: 'right',
                        close: true,
                        backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)'
                    }).showToast();
                }
                
                // Reset the save button state
                isSaving = false;
                $('#saveRawData').prop('disabled', false).text('Save');
            },
            error: function () {
                alert('Server error while saving.');
                isSaving = false;
                $('#saveRawData').prop('disabled', false).text('Save');
            }
        });
    });
    function fetchAndRenderBOMList(productId, highlightBomId = null, jobWorkId = null, refreshHeaderFromFirst = false) {
        const ajaxData = { productId: productId };
        if (jobWorkId) {
            ajaxData.job_work_id = jobWorkId;
        }
        const listThead = `
                    <tr>
                        <th scope="col">Version</th>
                        <th scope="col">BOM_Id</th>
                        <th scope="col">Status</th>
                        <th scope="col">Date</th>
                        <th scope="col">Actions</th>
                    </tr>`;
        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/GetBomDetailsByProductId",
            type: 'GET',
            data: ajaxData,
            dataType: 'json',
            success: function (data) {
                if (data.bomDetails && Array.isArray(data.bomDetails) && data.bomDetails.length > 0) {
                    const bomList = data.bomDetails.filter(function (item) {
                        return item && item.id;
                    });
                    if (bomList.length === 0) {
                        localStorage.removeItem('bomMaterialsDetails');
                        localStorage.removeItem('bomDetails');
                        $('#dynamicTable thead').html(listThead);
                        $('#dynamicTable tbody').html(
                            '<tr><td colspan="5" class="text-center">' +
                            (jobWorkId ? 'No BOM for this process' : 'No BOM found.') +
                            '</td></tr>'
                        );
                        return;
                    }
                    localStorage.setItem('bomMaterialsDetails', JSON.stringify(bomList));

                    $('#dynamicTable thead').html(listThead);

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

                    if (refreshHeaderFromFirst) {
                        const first = bomList[0];
                        if (first && first.id) {
                            localStorage.setItem('bomDetails', JSON.stringify(first));
                            localStorage.setItem('dropdownSelectionInProgress', 'true');
                            updateBomUI(first);
                        }
                    }
                } else {
                    localStorage.removeItem('bomMaterialsDetails');
                    localStorage.removeItem('bomDetails');
                    $('#dynamicTable thead').html(listThead);
                    $('#dynamicTable tbody').html(
                        '<tr><td colspan="5" class="text-center">' +
                        (jobWorkId ? 'No BOM for this process' : 'No BOM found.') +
                        '</td></tr>'
                    );
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
            url: site.base_url + "Variant_bill_of_materials/getProcurementOrderList",
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
                                if (localStorage.getItem('addBomItemClicked') === 'true') {
                                    showRawMaterialSidebar();
                                } else {
                                    hideRawMaterialSidebar();
                                }
                                $('#dynamicTable tfoot').empty();
                                $('#dynamicTable tbody').empty();
                                $('.hide-div').show();
                                $('#saveRawData').show();
                                $(".bom_material_section_item").hide();
                                $(".bom_material_section").show();
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
        if (localStorage.getItem('addBomItemClicked') !== 'true') {
            hideRawMaterialSidebar();
        }
    };
});

// Function to load intermediate products - moved to global scope
    let isLoadingIntermediateProducts = false; // Flag to prevent multiple simultaneous calls
    
    function loadIntermediateProducts(mainProductId, preferredJobWorkId) {
        
        if (!mainProductId) {
            $('#intermediateProductsContainer').hide();
            clearProcessSelectValidation();
            return;
        }

        // Store the currently selected value before repopulating
        const currentlySelected = $('#intermediateProductsDropdown').val();

        $.ajax({
            url: site.base_url + "Variant_bill_of_materials/getIntermediateProducts",
            method: 'GET',
            data: {
                mainProductId: mainProductId
            },
            dataType: 'json',
            success: function (response) {
                const intermediateProducts = response.intermediateProducts || [];
                // Get dropdown element
                const dropdown = $('#intermediateProductsDropdown');
                
                // Clear and rebuild dropdown completely
                dropdown.empty();
                
                // Add default option
                dropdown.append('<option value="">Select process</option>');
                
                if (intermediateProducts.length > 0) {
                    // Populate dropdown with processes (standard job works)
                    intermediateProducts.forEach(function(product, index) {
                        const option = '<option value="' + product.id + '">' + product.name + '</option>';
                        dropdown.append(option);
                    });
                    
                    // Prefer explicit job work id (e.g. after save); else restore previous selection if still valid
                    let valueToSet = null;
                    if (preferredJobWorkId !== undefined && preferredJobWorkId !== null && String(preferredJobWorkId).trim() !== '') {
                        const pref = preferredJobWorkId;
                        if (intermediateProducts.some(function (p) { return String(p.id) === String(pref) || p.id == pref; })) {
                            valueToSet = String(pref);
                        }
                    }
                    if (valueToSet === null && currentlySelected) {
                        if (intermediateProducts.some(function (product) { return product.id == currentlySelected; })) {
                            valueToSet = String(currentlySelected);
                        }
                    }
                    if (valueToSet !== null) {
                        dropdown.val(valueToSet);
                        dropdown.trigger('change');
                    }
                    
                    // Show the dropdown container
                    $('#intermediateProductsContainer').show();
                } else {
                    // Hide dropdown if no intermediate products found
                    $('#intermediateProductsContainer').hide();
                    clearProcessSelectValidation();
                }
            },
            error: function (xhr, status, error) {
                console.error('=== AJAX ERROR ===');
                console.error("Error loading intermediate products:", error);
                console.error("XHR status:", status);
                console.error("Response text:", xhr.responseText);
                $('#intermediateProductsContainer').hide();
                clearProcessSelectValidation();
            }
        });
    }
    
    // Test function - call this from browser console to test manually
    window.testIntermediateProducts = function(productId) {
        productId = productId || 1; // Default to product ID 1 if not provided
        loadIntermediateProducts(productId);
    };

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
                const isExisting = String($(this).attr('data-existing') || '') === '1';
                $(this).val(isExisting ? preserveDecimal(val) : quantityDecimal(val));
            } else {
                $(this).val(quantityDecimal(val));
            }
        }
    });

});
