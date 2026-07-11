<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
    <link href="<?= $assets ?>job_works/css/variant_order.css" rel="stylesheet" />

    <script type="text/javascript" src="<?= $assets ?>job_works/js/variant_order.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <!-- Bootstrap Select -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>

    <!-- <style>
.variant-scroll-container {
    overflow-x: auto;
    overflow-y: hidden;
    max-width: 100%;
} 
 .variant-scroll-container::-webkit-scrollbar {
    height: 8px;
}
.variant-scroll-container::-webkit-scrollbar-thumb {
    background-color: #428BCA;
    border-radius: 4px;
} 
/* Scroll only inside table container when variants > 10 */
 .variant-scroll-container.large-table {
    overflow-x: auto !important;
    overflow-y: hidden;
    max-width: 100%;
    display: block;
    padding-bottom: 5px;
}
.variant-scroll-container.large-table .custom-table {
    width: max-content;
    min-width: 1200px;
} 

/* Sticky first column */
  .first-col {
    position: sticky;
    left: 0;
    z-index: 2;
    background-color: #428BCA;
    color: white;
}
</style> -->
</head>

<body>
    <div class="dropdown-section" >
         <div class="page-wrapper container-fluid" style="padding: 0 40px; margin-top: 20px !important;">
        <div style="font-size:16px; font-weight:bold; margin-bottom: 10px;">
                <span style="color: red; font-size:large;">*</span>Choose one of the following:
            </div>
        <div class="top-filters" style="display: flex; align-items: flex-end; gap: 40px;">
            <div style="display: flex; flex-direction: column; width:250px;">
                <label for="categoryDropdown" style="font-weight:600;">
                    Category:
                </label>
                <select id="categoryDropdown" name="category" class="form-control selectpicker"
                    data-live-search="true" data-width="250px">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?= $cat->id ?>"><?= $cat->name ?></option>
                    <?php } ?>
                </select>
            </div>

            <div style="font-size:18px; font-weight:bold; margin-bottom: 10px;">OR</div>
            <div style="display: flex; flex-direction: column; width:250px;">
                <label for="brandDropdown" style="font-weight:600;">
                    Brand:
                </label>
                <select id="brandDropdown" name="brand" class="form-control selectpicker"
                    data-live-search="true" data-width="250px">
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand) { ?>
                        <option value="<?= $brand->id ?>"><?= $brand->name ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="button" id="resetFilters" class="btn btn-primary" style="padding: 5px;" disabled>
                <span class="glyphicon glyphicon-refresh"></span> Reset
            </button>

        </div>
    </div>


    <div class="page-wrapper container-fluid" style="padding: 0 40px; margin-top: 20px !important;">
        <div class="top-filters" style="display:flex; align-items:flex-start; gap:40px;">
            <div id="productDropdownContainer" class="hidden" style="display:none; display:flex; flex-direction:column; width:250px;">
                <label for="productDropdown" style="font-weight:600; margin-bottom:5px;">
                    <span style="color:red;font-size:large;">*</span>Product:
                </label>
                <select id="productDropdown" name="product" class="form-control selectpicker" data-live-search="true" data-width="250px" disabled>
                    <option value="">Select Product</option>
                </select>
            </div>

            <!-- Job Work / Process dropdown: visible after product selection -->
            <div id="processDropdownContainer" class="hidden" style="display:none; display:flex; flex-direction:column; width:250px; margin-left: 10%;">
                <label for="processDropdown" style="font-weight:600; margin-bottom:5px;">
                    <span style="color:red;font-size:large;">*</span>Job Work:
                </label>
                <select id="processDropdown" name="process" class="form-control selectpicker" data-live-search="true" data-width="250px" disabled>
                    <option value="">Select Process</option>
                </select>
            </div>

            <div id="supplierDropdownContainer" class="hidden" style="display:none; display:flex; flex-direction:column; width:250px;">
                <label for="supplierDropdown" style="font-weight:600; margin-bottom:5px;">
                    <span style="color:red;font-size:large;">*</span>Vendor:
                </label>
                <select id="supplierDropdown" name="supplier" class="form-control selectpicker" data-live-search="true" data-width="250px" required disabled>
                    <option value="">Select Vendor</option>
                    <?php foreach ($suppliers as $supplier) { ?>
                        <option value="<?= $supplier->id ?>"><?= $supplier->name ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    </div>
   



    <div class="page-wrapper container-fluid" style="padding: 0 40px; margin-top: 25px !important;">
        <div class="table-section"></div>
    </div>

    <div class="page-wrapper container-fluid" style="padding: 0 40px; margin-top: 40px !important;">
        <div class="note-box" id="noteBox">
            <p>Please Select Above Category Or Brand.</p>
        </div>



        <script>
        var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
        // === Category and Product logic ===
        $(document).ready(function() {
            // Store CSRF name and hash from PHP
            var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
            var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

            $('#categoryDropdown').on('change', function() {
                var categoryId = $(this).val();
                var $productDropdown = $('#productDropdown');

                // --- Reset all data related to previous product selection ---
                $('.table-section').empty(); // Clear table
                // Hide and reset Process and Vendor dropdowns
                $('#processDropdownContainer').addClass('hidden');
                $('#processDropdown').val('').prop('disabled', true).selectpicker('refresh');
                $('#supplierDropdownContainer').addClass('hidden'); // Hide Vendor dropdown
                $('#supplierDropdown').val('').prop('disabled', true).selectpicker('refresh');
                $('#productDropdownContainer').addClass('hidden'); // Hide product dropdown initially

                // Reset product dropdown
                $productDropdown.empty().append('<option value="">Select Product</option>').prop(
                    'disabled', true).selectpicker('refresh');

                // Disable reset button initially
                $('#resetFilters').prop('disabled', true);

                // --- If category selected, load products ---
                if (categoryId) {
                    //  Show note box until product is selected
                    $('#noteBox').show();

                    $('#productDropdownContainer').removeClass('hidden');
                    $('#resetFilters').prop('disabled', false);
                    $('#brandDropdown').val('').prop('disabled', true).selectpicker('refresh');
                    $('label[for="brandDropdown"]').addClass('disabled-label');

                    // Load products...
                    $.ajax({
                        url: '<?= site_url('RM_Calculator/get_products_by_category') ?>',
                        type: 'POST',
                        data: {
                            category_id: categoryId,
                            [csrfName]: csrfHash
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.length > 0) {
                                $.each(response, function(index, product) {
                                    $productDropdown.append('<option value="' +
                                        product.id + '">' + product.name +
                                        '</option>');
                                });
                                $productDropdown.prop('disabled', false).selectpicker(
                                    'refresh');
                            }

                            // Update CSRF hash if it's rotated
                            if (response.csrfHash) csrfHash = response.csrfHash;
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                        }
                    });
                } else {
                    // If category deselected
                    $('#noteBox').show();
                }
            });
            $('#brandDropdown').on('change', function() {
                var brandId = $(this).val();
                var $productDropdown = $('#productDropdown');

                // --- Reset all data related to previous product selection ---
                $('.table-section').empty(); // Clear table
                // Hide and reset Process and Vendor dropdowns
                $('#processDropdownContainer').addClass('hidden');
                $('#processDropdown').val('').prop('disabled', true).selectpicker('refresh');
                $('#supplierDropdownContainer').addClass('hidden'); // Hide Vendor dropdown
                $('#supplierDropdown').val('').prop('disabled', true).selectpicker('refresh');
                $('#productDropdownContainer').addClass('hidden'); // Hide product dropdown initially

                // Reset product dropdown
                $productDropdown.empty().append('<option value="">Select Product</option>').prop(
                    'disabled', true).selectpicker('refresh');

                // Disable reset button initially
                $('#resetFilters').prop('disabled', true);

                // --- If category selected, load products ---
                if (brandId) {
                    //  Show note box until product is selected
                    $('#noteBox').show();
                    $('#productDropdownContainer').removeClass('hidden');
                    $('#resetFilters').prop('disabled', false);
                    $('#categoryDropdown').val('').prop('disabled', true).selectpicker('refresh');
                    $('label[for="categoryDropdown"]').addClass('disabled-label');
                    // Load products...
                    $.ajax({
                        url: '<?= site_url('RM_Calculator/get_products_by_category') ?>',
                        type: 'POST',
                        data: {
                            brand_id: brandId,
                            [csrfName]: csrfHash
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.length > 0) {
                                $.each(response, function(index, product) {
                                    $productDropdown.append('<option value="' +
                                        product.id + '">' + product.name +
                                        '</option>');
                                });
                                $productDropdown.prop('disabled', false).selectpicker(
                                    'refresh');
                            }
                            // Update CSRF hash if it's rotated
                            if (response.csrfHash) csrfHash = response.csrfHash;
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                        }
                    });
                } else {
                    // If category deselected
                    $('#noteBox').show();
                }
            });
        });
        </script>


        <script>
        // === Product dropdown logic ===
        // Use existing globals if defined in external JS, otherwise initialize here.
        if (typeof currentProductId === 'undefined') { window.currentProductId = null; }
        if (typeof currentProcessId === 'undefined') { window.currentProcessId = null; }
        $('#productDropdown').on('change', function() {
            var productId = $(this).val();
            var selectedProductName = $(this).find('option:selected').text();
            currentProductId = productId || null;
            window.currentProductId = currentProductId;
            currentProcessId = null;
            window.currentProcessId = null;

            if (productId) {
                applySourceLock();
                // Until process is selected, keep note visible and table empty
                $('#noteBox').show();
                $('.table-section').empty();

                // Reset and hide Vendor until a process is selected
                $('#supplierDropdownContainer').addClass('hidden');
                $('#supplierDropdown').val('').prop('disabled', true).selectpicker('refresh');

                // Prepare Job Work / processes dropdown for this product
                $('#processDropdown')
                    .empty()
                    .append('<option value="">Select Process</option>')
                    .prop('disabled', true)
                    .selectpicker('refresh');
                $('#processDropdownContainer').addClass('hidden');

                $.ajax({
                    url: '<?= site_url('RM_Calculator/get_job_works_by_product') ?>',
                    type: 'POST',
                    data: {
                        product_id: productId,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.csrfHash) csrfHash = res.csrfHash;

                        if (res.status === 'ok') {
                            if (res.data && res.data.length > 0) {
                                // We have job works: only show table after user picks a process.
                                $.each(res.data, function(index, item) {
                                    $('#processDropdown').append(
                                        '<option value="' + item.id + '">' + item.name + '</option>'
                                    );
                                });
                                $('#processDropdown').prop('disabled', false);
                                $('#processDropdown').selectpicker('refresh');
                                $('#processDropdownContainer').removeClass('hidden');

                                // Hide Vendor until process is chosen
                                $('#supplierDropdownContainer').addClass('hidden');
                                $('#supplierDropdown').val('').prop('disabled', true).selectpicker('refresh');
                            } else {
                                // No job works configured: behave like old flow (load table on product)
                                $('#processDropdownContainer').addClass('hidden');
                                $('#supplierDropdown').prop('disabled', false).selectpicker('refresh');
                                $('#supplierDropdownContainer').removeClass('hidden');

                                // Load product-wise details immediately (no process filter)
                                $.ajax({
                                    url: '<?= site_url('RM_Calculator/getProductWiseAllDetails') ?>',
                                    type: 'POST',
                                    data: {
                                        product_id: productId,
                                        [csrfName]: csrfHash
                                    },
                                    dataType: 'json',
                                    success: function(response) {
                                        $('#noteBox').hide();
                                        fullVariantsData = response.variants || [];
                                        fullIngredientsData = response.Ingredients || [];
                                        fullOtherCategoriesNames = response.other_categories_names || '';
                                        fullPrimaryCategory = response.Primary_category || [];

                                        if (typeof shouldUseSimpleQuantityView === 'function' ? shouldUseSimpleQuantityView(response) : (response.recipe_has_variants === false)) {
                                            loadSimpleQuantityView(selectedProductName, productId, response.Ingredients);
                                        } else {
                                            if (response.variants && response.variants.length > 0) {
                                                loadIngredientsTable(response.variants, response.Ingredients, response.other_categories_names, response.Primary_category);
                                            } else {
                                                $('.table-section').html(
                                                    '<div class="alert alert-warning">No variants found for this product.</div>'
                                                );
                                            }
                                        }
                                        if (response.csrfHash) csrfHash = response.csrfHash;
                                    },
                                    error: function(xhr) {
                                        console.log('Error:', xhr.responseText);
                                    }
                                });
                            }
                        } else {
                            // On error, fall back to Vendor only
                            $('#processDropdownContainer').addClass('hidden');
                            $('#supplierDropdown').prop('disabled', false).selectpicker('refresh');
                            $('#supplierDropdownContainer').removeClass('hidden');
                        }
                    },
                    error: function(xhr) {
                        console.log('Error loading Job Works:', xhr.responseText);
                        // On error, fall back to Vendor only
                        $('#processDropdownContainer').addClass('hidden');
                        $('#supplierDropdown').prop('disabled', false).selectpicker('refresh');
                        $('#supplierDropdownContainer').removeClass('hidden');
                    }
                });
            } else {
                // Hide and disable Process and Vendor dropdown when no product selected
                $('#processDropdownContainer').addClass('hidden');
                $('#processDropdown').val('').prop('disabled', true).selectpicker('refresh');
                $('#supplierDropdownContainer').addClass('hidden');
                $('#supplierDropdown').prop('disabled', true).selectpicker('refresh');
                $('#noteBox').show();
            }
        });

        // When a Job Work / process is selected, show Vendor dropdown
        $('#processDropdown').on('change', function() {
            var processId = $(this).val();
            currentProcessId = processId || null;
            window.currentProcessId = currentProcessId;
            if (processId) {
                $('#supplierDropdown').prop('disabled', false).selectpicker('refresh');
                $('#supplierDropdownContainer').removeClass('hidden');

                // Reload product details using selected Job Work so that
                // BOM/raw materials come from latest active record for this process.
                if (currentProductId) {
                    var selectedProductName = $('#productDropdown option:selected').text();
                    $.ajax({
                        url: '<?= site_url('RM_Calculator/getProductWiseAllDetails') ?>',
                        type: 'POST',
                        data: {
                            product_id: currentProductId,
                            job_work_id: processId,
                            [csrfName]: csrfHash
                        },
                        dataType: 'json',
                        success: function(response) {
                            // Store full data for Generate PO functionality
                            fullVariantsData = response.variants || [];
                            fullIngredientsData = response.Ingredients || [];
                            fullOtherCategoriesNames = response.other_categories_names || '';
                            fullPrimaryCategory = response.Primary_category || [];

                            // Check if recipe has variants
                            if (typeof shouldUseSimpleQuantityView === 'function' ? shouldUseSimpleQuantityView(response) : (response.recipe_has_variants === false)) {
                                // Recipe has no variants - show simple quantity view with raw materials
                                loadSimpleQuantityView(selectedProductName, currentProductId, response.Ingredients);
                            } else {
                                // Recipe has variants - show full variant workflow
                                if (response.variants && response.variants.length > 0) {
                                    loadIngredientsTable(response.variants, response.Ingredients, response
                                        .other_categories_names, response.Primary_category);
                                } else {
                                    $('.table-section').html(
                                        '<div class="alert alert-warning">No variants found for this product.</div>'
                                    );
                                }
                            }

                            if (response.csrfHash) csrfHash = response.csrfHash;
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                        }
                    });
                }
            } else {
                $('#supplierDropdownContainer').addClass('hidden');
                $('#supplierDropdown').val('').prop('disabled', true).selectpicker('refresh');
            }
        });

        // === Reset filters button ===
        $('#resetFilters').on('click', function() {
            // Reset dropdowns
            $('#categoryDropdown').val('').selectpicker('refresh');
            $('#brandDropdown').val('').selectpicker('refresh');
            $('#categoryDropdown').prop('disabled', false).selectpicker('refresh');
            $('#brandDropdown').prop('disabled', false).selectpicker('refresh');
            $('#productDropdown')
                .empty()
                .append('<option value="">Select Product</option>')
                .prop('disabled', true)
                .selectpicker('refresh');
            $('#processDropdown')
                .empty()
                .append('<option value="">Select Process</option>')
                .prop('disabled', true)
                .selectpicker('refresh');
            $('#processDropdownContainer').addClass('hidden');

            // ✅ Hide and disable Vendor dropdown on reset
            $('#supplierDropdown').val('').prop('disabled', true).selectpicker('refresh');
            $('#supplierDropdownContainer').addClass('hidden');

            // Clear table section
            $('.table-section').empty();

            // Show note box again
            $('#noteBox').show();

            // Hide product dropdown and disable reset button
            $('#productDropdownContainer').addClass('hidden');
            $('#resetFilters').prop('disabled', true);
        });
        </script>
        <script>
        // === Preserve filters and table data across refresh ===

        // Save state on dropdown changes or after loading table
        function saveState() {
            const state = {
                category: $('#categoryDropdown').val(),
                product: $('#productDropdown').val(),
                process: $('#processDropdown').val(),
                tableHTML: $('.table-section').html(),
                supplier: $('#supplierDropdown').val(),
                orderQuantities: {},
                simpleQuantities: {},
                noteBoxVisible: $('#noteBox').is(':visible')
            };

            // Save all current order quantity inputs
            $('.order-quantity-input').each(function() {
                const id = $(this).data('variant-id');
                state.orderQuantities[id] = $(this).val();
            });
            // Save simple-view raw material quantities (use .attr so keys match HTML; .data() can differ)
            $('.raw-material-quantity').each(function() {
                const materialId = $(this).attr('data-material-id');
                const materialName = $(this).attr('data-material');
                const key = (materialId !== undefined && materialId !== null && String(materialId).trim() !== '')
                    ? ('id_' + String(materialId).trim())
                    : ('name_' + String(materialName || '').trim());
                state.simpleQuantities[key] = $(this).val();
            });

            localStorage.setItem('variantOrderState', JSON.stringify(state));
        }

        function restoreProcessAndVendor(productId, state) {
            const savedProcess = state.process || localStorage.getItem('savedProcess');
            const savedSupplier = state.supplier || localStorage.getItem('savedSupplier');
            if (!productId) return;

            $.ajax({
                url: '<?= site_url('RM_Calculator/get_job_works_by_product') ?>',
                type: 'POST',
                data: {
                    product_id: productId,
                    [csrfName]: csrfHash
                },
                dataType: 'json',
                success: function(res) {
                    if (res.csrfHash) csrfHash = res.csrfHash;

                    $('#processDropdown').empty().append('<option value="">Select Process</option>');

                    if (res.status === 'ok' && res.data && res.data.length > 0) {
                        $.each(res.data, function(index, item) {
                            $('#processDropdown').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        $('#processDropdown').prop('disabled', false).selectpicker('refresh');
                        $('#processDropdownContainer').removeClass('hidden');

                        if (savedProcess && $('#processDropdown option[value="' + savedProcess + '"]').length) {
                            $('#processDropdown').val(savedProcess).selectpicker('refresh').trigger('change');
                        }
                    } else {
                        // No process mapping: keep process hidden and restore vendor directly
                        $('#processDropdownContainer').addClass('hidden');
                        $('#processDropdown').prop('disabled', true).selectpicker('refresh');
                        if (savedSupplier) {
                            $('#supplierDropdownContainer').removeClass('hidden');
                            $('#supplierDropdown').prop('disabled', false);
                            $('#supplierDropdown').val(savedSupplier).selectpicker('refresh').trigger('change');
                        }
                    }
                }
            });
        }


        // Restore previous state
        function restoreState() {
            const saved = localStorage.getItem('variantOrderState');
            if (!saved) return;
            const state = JSON.parse(saved);

            // Restore note box visibility
            if (state.noteBoxVisible === false) {
                $('#noteBox').hide();
            } else {
                $('#noteBox').show();
            }
            const savedBrand = localStorage.getItem('savedBrand');
            if (state.category) {
                $('#categoryDropdown').val(state.category).selectpicker('refresh');
                const savedBrand = localStorage.getItem('savedBrand');
                if (savedBrand) {
                    $('#brandDropdown').val(savedBrand).selectpicker('refresh').trigger('change');
                }

                // Show product dropdown and enable reset button
                $('#productDropdownContainer').removeClass('hidden');
                $('#resetFilters').prop('disabled', false);

                // Load products for this category
                $.ajax({
                    url: '<?= site_url('RM_Calculator/get_products_by_category') ?>',
                    type: 'POST',
                    data: {
                        category_id: state.category,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function(products) {
                        $('#productDropdown')
                            .empty()
                            .append('<option value="">Select Product</option>');

                        $.each(products, function(index, product) {
                            $('#productDropdown').append(
                                '<option value="' + product.id + '">' + product.name +
                                '</option>'
                            );
                        });

                        $('#productDropdown').prop('disabled', false).selectpicker('refresh');

                        // Restore selected product and table if product exists
                        if (state.product && $('#productDropdown option[value="' + state.product + '"]')
                            .length) {
                            $('#productDropdown').val(state.product).selectpicker('refresh');
                            restoreProcessAndVendor(state.product, state);

                            // ✅ Always fetch fresh product data to ensure event handlers are properly attached
                            // This ensures the variants array and event handlers are in sync
                            $.ajax({
                                url: '<?= site_url('RM_Calculator/getProductWiseAllDetails') ?>',
                                type: 'POST',
                                data: (function() {
                                    var reqData = {
                                    product_id: state.product,
                                    [csrfName]: csrfHash
                                    };
                                    var savedProcess = state.process || localStorage.getItem('savedProcess');
                                    if (savedProcess) {
                                        reqData.job_work_id = savedProcess;
                                    }
                                    return reqData;
                                })(),
                                dataType: 'json',
                                success: function(response) {
                                    // Store full data for Generate PO functionality
                                    fullVariantsData = response.variants || [];
                                    fullIngredientsData = response.Ingredients || [];
                                    fullOtherCategoriesNames = response.other_categories_names || '';
                                    fullPrimaryCategory = response.Primary_category || [];
                                    
                                    // Check if recipe has variants
                                    if (typeof shouldUseSimpleQuantityView === 'function' ? shouldUseSimpleQuantityView(response) : (response.recipe_has_variants === false)) {
                                        // Recipe has no variants - show simple quantity view
                                        const productName = $('#productDropdown option:selected').text();
                                        loadSimpleQuantityView(productName, state.product, response.Ingredients);
                                        if (state.simpleQuantities) {
                                            setTimeout(function() {
                                                $('.raw-material-quantity').each(function() {
                                                    const materialId = $(this).attr('data-material-id');
                                                    const materialName = $(this).attr('data-material');
                                                    const keyById = (materialId !== undefined && materialId !== null && String(materialId).trim() !== '')
                                                        ? ('id_' + String(materialId).trim())
                                                        : null;
                                                    const keyByName = 'name_' + String(materialName || '').trim();
                                                    const savedQty = (keyById && typeof state.simpleQuantities[keyById] !== 'undefined')
                                                        ? state.simpleQuantities[keyById]
                                                        : state.simpleQuantities[keyByName];
                                                    if (typeof savedQty !== 'undefined') {
                                                        $(this).val(savedQty);
                                                    }
                                                });
                                            }, 280);
                                        }
                                    } else {
                                        // Recipe has variants - show full variant workflow
                                        if (response.variants && response.variants.length > 0) {
                                            loadIngredientsTable(response.variants, response
                                                .Ingredients, response.other_categories_names,
                                                response.Primary_category);

                                            // Restore saved quantities after table is loaded
                                            if (state.orderQuantities) {
                                                setTimeout(function() {
                                                    Object.entries(state.orderQuantities)
                                                        .forEach(([variantId, qty]) => {
                                                            const $input = $(
                                                                `input[data-variant-id="${variantId}"]`
                                                                );
                                                            if ($input.length) {
                                                                $input.val(qty);
                                                                $input.trigger(
                                                                'input'); // Trigger recalculation
                                                            }
                                                        });
                                                }, 100);
                                            }
                                        }
                                    }
                                    if (response.csrfHash) csrfHash = response.csrfHash;
                                },
                                error: function(xhr) {
                                    console.log('Error:', xhr.responseText);
                                }
                            });
                        }
                        // === Restore Vendor selection and visibility ===
                        const savedSupplier = localStorage.getItem('savedSupplier');
                        if (savedSupplier) {
                            $('#supplierDropdownContainer').removeClass('hidden');
                            $('#supplierDropdown').prop('disabled', false);
                            $('#supplierDropdown').val(savedSupplier).selectpicker('refresh');
                            $('#supplierDropdown').trigger('change');
                        }
                    }
                });
            } else {
                $('#brandDropdown').val(savedBrand).selectpicker('refresh');
                $('#categoryDropdown').val('').prop('disabled', true).selectpicker('refresh');
                $('label[for="categoryDropdown"]').addClass('disabled-label');

                $('#productDropdownContainer').removeClass('hidden');
                $('#resetFilters').prop('disabled', false);

                $.ajax({
                    url: '<?= site_url('RM_Calculator/get_products_by_category') ?>',
                    type: 'POST',
                    data: {
                        brand_id: savedBrand,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function(products) {

                        $('#productDropdown').empty().append('<option value="">Select Product</option>');
                        $.each(products, function(i, p) {
                            $('#productDropdown').append(
                                `<option value="${p.id}">${p.name}</option>`);
                        });
                        $('#productDropdown').prop('disabled', false).selectpicker('refresh');
                        // Restore the selected product
                        if (state.product) {
                            $('#productDropdown').val(state.product).selectpicker('refresh');
                            restoreProcessAndVendor(state.product, state);
                        }
                        // Load product details + refresh table
                        if (state.product) {
                            $.ajax({
                                url: '<?= site_url('RM_Calculator/getProductWiseAllDetails') ?>',
                                type: 'POST',
                                data: (function() {
                                    var reqData = {
                                        product_id: state.product,
                                        [csrfName]: csrfHash
                                    };
                                    var savedProcess = state.process || localStorage.getItem('savedProcess');
                                    if (savedProcess) {
                                        reqData.job_work_id = savedProcess;
                                    }
                                    return reqData;
                                })(),
                                dataType: 'json',
                                success: function(response) {
                                    // Store full data for Generate PO functionality
                                    fullVariantsData = response.variants || [];
                                    fullIngredientsData = response.Ingredients || [];
                                    fullOtherCategoriesNames = response.other_categories_names || '';
                                    fullPrimaryCategory = response.Primary_category || [];
                                    
                                    // Check if recipe has variants
                                    if (typeof shouldUseSimpleQuantityView === 'function' ? shouldUseSimpleQuantityView(response) : (response.recipe_has_variants === false)) {
                                        // Recipe has no variants - show simple quantity view
                                        const productName = $('#productDropdown option:selected').text();
                                        loadSimpleQuantityView(productName, state.product, response.Ingredients);
                                        if (state.simpleQuantities) {
                                            setTimeout(function() {
                                                $('.raw-material-quantity').each(function() {
                                                    const materialId = $(this).attr('data-material-id');
                                                    const materialName = $(this).attr('data-material');
                                                    const keyById = (materialId !== undefined && materialId !== null && String(materialId).trim() !== '')
                                                        ? ('id_' + String(materialId).trim())
                                                        : null;
                                                    const keyByName = 'name_' + String(materialName || '').trim();
                                                    const savedQty = (keyById && typeof state.simpleQuantities[keyById] !== 'undefined')
                                                        ? state.simpleQuantities[keyById]
                                                        : state.simpleQuantities[keyByName];
                                                    if (typeof savedQty !== 'undefined') {
                                                        $(this).val(savedQty);
                                                    }
                                                });
                                            }, 280);
                                        }
                                    } else {
                                        // Recipe has variants - show full variant workflow
                                        loadIngredientsTable(
                                            response.variants,
                                            response.Ingredients,
                                            response.other_categories_names,
                                            response.Primary_category
                                        );

                                        // Restore saved quantities
                                        setTimeout(() => {
                                            if (state.orderQuantities) {
                                                Object.entries(state.orderQuantities)
                                                    .forEach(([vid, qty]) => {
                                                        const $input = $(
                                                            `input[data-variant-id="${vid}"]`
                                                            );
                                                        if ($input.length) {
                                                            $input.val(qty).trigger(
                                                            'input');
                                                        }
                                                    });
                                            }
                                        }, 150);
                                    }
                                }
                            });
                        }

                        // Restore Vendor
                        const savedSupplier = localStorage.getItem('savedSupplier');
                        if (savedSupplier) {
                            $('#supplierDropdownContainer').removeClass('hidden');
                            $('#supplierDropdown').prop('disabled', false);
                            $('#supplierDropdown')
                                .val(savedSupplier).selectpicker('refresh').trigger('change');
                        }
                    }
                });
                return;
            }
        }
        $('#supplierDropdown').on('change', function() {
            var supplierId = $(this).val();

            if (supplierId) {
                $('#noteBox').hide();

                $.ajax({
                    url: '<?= site_url('RM_Calculator/get_supplier_stock') ?>',
                    type: 'POST',
                    data: {
                        supplier_id: supplierId,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function(response) {

                        if (response.csrfHash) csrfHash = response.csrfHash;

                        if (response.status === 'ok' && response.data) {
                            // response.data = [{ raw_material_id, quantity, warehouse_name }]

                            response.data.forEach(item => {
                                // Match based on material name or ID
                                const $cell = $(
                                    `td.supplier-stock[data-material="${item.raw_material}"]`
                                    );
                                $cell.text(formatDecimal(item.quantity));
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching Vendor stock:', xhr.responseText);
                    }
                });
            } else {
                // Reset Vendor stock column when deselected
                $('.supplier-stock').text('-');
                $('#noteBox').show();
            }
        });


        // --- Save on dropdown change ---
        $('#categoryDropdown').on('change', function() {
            localStorage.setItem('savedCategory', $(this).val());
            localStorage.removeItem('savedProduct'); // clear old product
        });

        $('#productDropdown').on('change', function() {
            localStorage.setItem('savedProduct', $(this).val());
        });
        $('#processDropdown').on('change', function() {
            localStorage.setItem('savedProcess', $(this).val());
        });
        $('#supplierDropdown').on('change', function() {
            localStorage.setItem('savedSupplier', $(this).val());
        });
        // --- Clear saved filters on Reset ---
        $('#resetFilters').on('click', function() {
            localStorage.removeItem('savedCategory');
            localStorage.removeItem('savedProduct');
            localStorage.removeItem('savedProcess');
            localStorage.removeItem('savedSupplier');
            localStorage.removeItem('savedBrand');
            $('label[for="brandDropdown"], label[for="categoryDropdown"]').removeClass('disabled-label');
        });
        $('#brandDropdown').on('change', function() {
            localStorage.setItem('savedBrand', $(this).val());
        });
        </script>
        <script>
        // Save whenever filters or table update
        $(document).on('change', '#categoryDropdown, #productDropdown', saveState);
        $(document).on('input', '.order-quantity-input', saveState);
        $(document).on('input', '.raw-material-quantity', saveState);
        $(document).on('click', '#place_order', saveState);

        // Restore on page load
        $(document).ready(function() {
            restoreState();
            applySourceLock();
        });

        // Clear localStorage on reset
        $('#resetFilters').on('click', function() {
            localStorage.removeItem('variantOrderState');
            $('label[for="brandDropdown"], label[for="categoryDropdown"]').removeClass('disabled-label');
        });
        function applySourceLock() {
            const savedCategory = localStorage.getItem('savedCategory');
            const savedBrand = localStorage.getItem('savedBrand');

            if (savedCategory && !savedBrand) {
                // Category flow → disable Brand
                $('#brandDropdown')
                    .prop('disabled', true)
                    .selectpicker('refresh');
                $('label[for="brandDropdown"]').addClass('disabled-label');

                $('#categoryDropdown')
                    .prop('disabled', false)
                    .selectpicker('refresh');
                $('label[for="categoryDropdown"]').removeClass('disabled-label');
            }

            if (savedBrand && !savedCategory) {
                // Brand flow → disable Category
                $('#categoryDropdown')
                    .prop('disabled', true)
                    .selectpicker('refresh');
                $('label[for="categoryDropdown"]').addClass('disabled-label');

                $('#brandDropdown')
                    .prop('disabled', false)
                    .selectpicker('refresh');
                $('label[for="brandDropdown"]').removeClass('disabled-label');
            }
        }

        </script>
</body>

</html>
