/*
 * Variant Order UI logic
 *
 * Business logic (why):
 * - Renders a matrix where columns are Product Variants and rows are Raw Materials required.
 * - Lets user set order quantities per variant, recomputing raw material totals on the fly.
 * - On "Place Order", we stage a compact payload (variants, grouped materials, note) and redirect
 *   to Purchases/generate_vriant_po where a proper Purchase Order is created (Vendor, taxes, etc).
 * - CSRF token is attached to all AJAX posts for security.
 */

// Global variables to store full data for Generate PO functionality
let fullVariantsData = null;
let fullIngredientsData = null;
let fullOtherCategoriesNames = null;
let fullPrimaryCategory = null;
let currentProductId = null;

/**
 * Decide whether to use simple quantity view.
 * Rule:
 * - Keep existing behavior when recipe has no variants.
 * - Also use simple view when raw materials look like suffix products
 *   (e.g. Pink Shalu_Stiched_M => name contains 2 underscores).
 */
function shouldUseSimpleQuantityView(response) {
    if (!response) return false;
    if (response.recipe_has_variants === false) return true;
    const ingredients = Array.isArray(response.Ingredients) ? response.Ingredients : [];
    if (!ingredients.length) return false;
    return ingredients.some((item) => {
        const name = item && item.raw_material ? String(item.raw_material).trim() : '';
        return /.+_.+_.+/.test(name);
    });
}

/**
 * Escape text for safe HTML body (minimal)
 */
function simpleViewEscapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * Escape for HTML attribute value
 */
function simpleViewEscapeAttr(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Re-apply Order Qty from variantOrderState after simple table HTML is injected.
 * Needed because restoreProcessAndVendor → process change → second loadSimpleQuantityView
 * replaces the table after restoreState's setTimeout, which cleared re-applied values.
 */
function applyStoredSimpleQuantitiesForProduct(productId) {
    if (productId === undefined || productId === null || String(productId) === '') {
        return;
    }
    var simpleQuantities = null;
    try {
        var raw = localStorage.getItem('variantOrderState');
        if (!raw) {
            return;
        }
        var state = JSON.parse(raw);
        if (String(state.product) !== String(productId)) {
            return;
        }
        if (!state.simpleQuantities) {
            return;
        }
        simpleQuantities = state.simpleQuantities;
        var hasAny = false;
        for (var key in simpleQuantities) {
            if (Object.prototype.hasOwnProperty.call(simpleQuantities, key)) {
                hasAny = true;
                break;
            }
        }
        if (!hasAny) {
            return;
        }
    } catch (e) {
        return;
    }

    $('.table-section .raw-material-quantity').each(function () {
        var $inp = $(this);
        var materialId = $inp.attr('data-material-id');
        var materialName = $inp.attr('data-material');
        var keyById = (materialId !== undefined && materialId !== null && String(materialId).trim() !== '')
            ? ('id_' + String(materialId).trim())
            : null;
        var keyByName = 'name_' + String(materialName || '').trim();
        var savedQty = (keyById && typeof simpleQuantities[keyById] !== 'undefined')
            ? simpleQuantities[keyById]
            : simpleQuantities[keyByName];
        if (typeof savedQty !== 'undefined' && savedQty !== null) {
            $inp.val(savedQty);
        }
        clampSimpleRawMaterialOrderQty($inp, false);
    });
}

/**
 * Cap simple-view Order Qty to total Available Qty (sum of warehouse_qty for that material).
 * @param {jQuery} $inp
 * @param {boolean} showErrorIfExceeded — if true, bootbox when qty was above available stock
 */
function clampSimpleRawMaterialOrderQty($inp, showErrorIfExceeded) {
    if (!$inp || !$inp.length) {
        return;
    }
    if (showErrorIfExceeded === undefined) {
        showErrorIfExceeded = false;
    }
    var maxStr = $inp.attr('data-max-available');
    if (maxStr === undefined || maxStr === null || maxStr === '') {
        return;
    }
    var maxAvail = parseFloat(maxStr);
    if (isNaN(maxAvail) || maxAvail < 0) {
        maxAvail = 0;
    }
    var raw = String($inp.val() || '').trim();
    if (raw === '') {
        return;
    }
    var num = parseFloat(raw);
    if (isNaN(num)) {
        return;
    }
    if (num < 0) {
        num = 0;
    }
    if (num > maxAvail) {
        if (showErrorIfExceeded && typeof bootbox !== 'undefined') {
            var maxDisp = (typeof formatDecimal === 'function') ? formatDecimal(maxAvail) : String(maxAvail);
            bootbox.alert('Quantity cannot be greater than available stock. Maximum allowed is ' + maxDisp + '.');
        }
        num = maxAvail;
    }
    var formatted = (typeof formatDecimal === 'function') ? formatDecimal(num) : String(num);
    $inp.val(formatted);
}

/**
 * loadSimpleQuantityView
 * Shows raw materials from recipe with individual quantity inputs when recipe has no variants.
 * Warehouses + stock + vendor stock columns match loadIngredientsTable (supplier-stock cells updated on vendor change).
 */
function loadSimpleQuantityView(productName, productId, ingredients) {
    currentProductId = productId;
    // A valid product/process response is loaded, so hide helper note.
    $('#noteBox').hide();

    // Group by raw material name; keep all ingredient rows for warehouse breakdown (same as loadIngredientsTable)
    const groupedMaterials = {};
    if (ingredients && ingredients.length > 0) {
        ingredients.forEach((item) => {
            const rm = item.raw_material;
            if (!groupedMaterials[rm]) {
                groupedMaterials[rm] = {
                    raw_material_id: item.raw_material_id || item.product_id || item.id || null,
                    unit_name: item.unit_name || null,
                    quantity_required: item.quantity_required || 0,
                    rows: []
                };
            }
            groupedMaterials[rm].rows.push(item);
            if (!groupedMaterials[rm].raw_material_id) {
                groupedMaterials[rm].raw_material_id = item.raw_material_id || item.product_id || item.id || null;
            }
            if (!groupedMaterials[rm].unit_name && item.unit_name) {
                groupedMaterials[rm].unit_name = item.unit_name;
            }
        });
    }

    let tableRows = '';
    let materialGroupIndex = 0;
    if (Object.keys(groupedMaterials).length > 0) {
        Object.keys(groupedMaterials).forEach((material) => {
            const groupRowClass = 'simple-rm-row simple-rm-mat-' + (materialGroupIndex % 2);
            materialGroupIndex += 1;
            const data = groupedMaterials[material];
            const related = data.rows || [];
            const warehouseGroups = {};
            related.forEach((x) => {
                const warehouseName = x.warehouse_name ? String(x.warehouse_name).trim() : '-';
                if (!warehouseGroups[warehouseName]) warehouseGroups[warehouseName] = [];
                warehouseGroups[warehouseName].push(x);
            });
            const validWarehouseGroups = Object.keys(warehouseGroups).filter(function (wh) {
                return wh && wh !== '-';
            }).map(function (wh) {
                return [wh, warehouseGroups[wh]];
            });
            const rowSpan = Math.max(validWarehouseGroups.length, 1);
            const matEsc = simpleViewEscapeHtml(material);
            const unitEsc = data.unit_name ? simpleViewEscapeHtml(data.unit_name) : '';
            const matLabel = '<strong>' + matEsc + '</strong>' + (unitEsc ? ' (' + unitEsc + ')' : '');
            const matAttr = simpleViewEscapeAttr(material);
            const idAttr = data.raw_material_id || '';
            const unitAttr = simpleViewEscapeAttr(data.unit_name || '');

            const stockCell = function (qty) {
                return (typeof formatDecimal === 'function') ? formatDecimal(qty) : String(qty);
            };

            var totalAvailable = 0;
            validWarehouseGroups.forEach(function (wg) {
                var its = wg[1];
                var wq = its && its.length > 0 ? (parseFloat(its[0].warehouse_qty) || 0) : 0;
                totalAvailable += wq;
            });

            const qtyInputHtml = '<div class="simple-rm-qty-wrap">' +
                '<input type="text" class="form-control raw-material-quantity input-sm" data-material="' + matAttr + '" data-material-id="' + idAttr + '" data-unit="' + unitAttr + '" data-max-available="' + totalAvailable + '" placeholder="0" inputmode="decimal" pattern="[0-9]*" value="0" title="Max: total Available Qty (' + totalAvailable + ')"/>' +
                '</div>';

            if (validWarehouseGroups.length === 0) {
                tableRows += '<tr class="' + groupRowClass + '">' +
                    '<td class="simple-rm-cell-material">' + matLabel + '</td>' +
                    '<td class="simple-rm-cell-wh">-</td>' +
                    '<td class="simple-rm-cell-stock">' + stockCell(0) + '</td>' +
                    '<td class="supplier-stock simple-rm-cell-vendor" rowspan="1" data-material="' + matAttr + '">0</td>' +
                    '<td class="simple-rm-cell-order" rowspan="1">' + qtyInputHtml + '</td>' +
                    '</tr>';
            } else {
                validWarehouseGroups.forEach(function (entry, i) {
                    const wh = entry[0];
                    const items = entry[1];
                    const whQty = items && items.length > 0 ? (parseFloat(items[0].warehouse_qty) || 0) : 0;
                    const rowClass = groupRowClass + (i === 0 ? ' simple-rm-first-wh' : '');
                    tableRows += '<tr class="' + rowClass + '">';
                    if (i === 0) {
                        tableRows += '<td class="simple-rm-cell-material" rowspan="' + rowSpan + '">' + matLabel + '</td>';
                    }
                    tableRows += '<td class="simple-rm-cell-wh">' + simpleViewEscapeHtml(wh) + '</td>';
                    tableRows += '<td class="simple-rm-cell-stock">' + stockCell(whQty) + '</td>';
                    if (i === 0) {
                        tableRows += '<td class="supplier-stock simple-rm-cell-vendor" rowspan="' + rowSpan + '" data-material="' + matAttr + '">0</td>';
                        tableRows += '<td class="simple-rm-cell-order" rowspan="' + rowSpan + '">' + qtyInputHtml + '</td>';
                    }
                    tableRows += '</tr>';
                });
            }
        });
    } else {
        tableRows = '<tr><td colspan="5"><div class="alert alert-warning" style="margin:0;">No raw materials found in recipe.</div></td></tr>';
    }

    const simpleView = `
        <div class="simple-quantity-view" style="margin-top: 30px;">
            <div class="table-title" style="text-align: left; margin-bottom: 20px; font-size: 16px;">
                Raw Materials & Quantities
                <button class="btn btn-primary" id="place_order_simple" style="margin-left:auto; float: right;">Generate PO</button>
            </div>
            
            <div class="simple-product-section" style="background-color: #f9f9f9; padding: 20px; border-radius: 6px; border: 1px solid #ddd;">
                <div class="variant-scroll-container simple-rm-scroll" style="padding: 0; background-color: #fff; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px;">
                    <table class="simple-rm-table table table-bordered" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th class="simple-rm-th-material">Raw Material</th>
                                <th class="simple-rm-th-wh">Warehouses</th>
                                <th class="simple-rm-th-stock">Available Qty</th>
                                <th class="simple-rm-th-vendor supplier-stock-col">Vendor Current Stock</th>
                                <th class="simple-rm-th-order">Order Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('.table-section').html(simpleView);

    // If vendor already selected, reload stock into new .supplier-stock cells (same AJAX as variant table)
    var $supDd = $('#supplierDropdown');
    if ($supDd.length && $supDd.val()) {
        $supDd.trigger('change');
    }

    applyStoredSimpleQuantitiesForProduct(productId);
    setTimeout(function () {
        applyStoredSimpleQuantitiesForProduct(productId);
    }, 400);
    setTimeout(function () {
        applyStoredSimpleQuantitiesForProduct(productId);
    }, 900);

    // Add event handlers for raw material quantity inputs (cannot exceed sum of Available Qty)
    $('.raw-material-quantity').on('input', function() {
        let val = $(this).val().replace(/[^0-9.]/g, '');
        if ((val.match(/\./g) || []).length > 1) {
            val = val.substr(0, val.lastIndexOf('.'));
        }
        $(this).val(val);
        clampSimpleRawMaterialOrderQty($(this), true);
    });
    $('.raw-material-quantity').on('blur', function() {
        clampSimpleRawMaterialOrderQty($(this), true);
    });
    
    // Add event handler for Generate PO button in simple view
    $(document).off('click', '#place_order_simple').on('click', '#place_order_simple', function() {
        // Persist Order Qty to localStorage before redirect (same as #place_order for variant view)
        if (typeof saveState === 'function') {
            saveState();
        }
        // Collect all quantities
        const quantities = {};
        let hasValidQuantity = false;
        
        $('.raw-material-quantity').each(function() {
            clampSimpleRawMaterialOrderQty($(this), false);
        });

        var overAvailMsg = null;
        $('.raw-material-quantity').each(function() {
            const maxAvail = parseFloat($(this).attr('data-max-available'));
            const quantity = parseFloat($(this).val()) || 0;
            if (!isNaN(maxAvail) && quantity > maxAvail) {
                overAvailMsg = 'Quantity cannot be greater than available stock. Maximum allowed is ' +
                    (typeof formatDecimal === 'function' ? formatDecimal(maxAvail) : maxAvail) + '.';
                return false;
            }
        });
        if (overAvailMsg) {
            bootbox.alert(overAvailMsg);
            return;
        }

        $('.raw-material-quantity').each(function() {
            const material = $(this).data('material');
            const materialId = $(this).data('material-id');
            const unit = $(this).data('unit');
            const quantity = parseFloat($(this).val()) || 0;

            if (quantity > 0) {
                hasValidQuantity = true;
                quantities[material] = {
                    quantity: quantity,
                    material_id: materialId,
                    unit: unit
                };
            }
        });

        if (!hasValidQuantity) {
            bootbox.alert('Please enter at least one quantity greater than 0.');
            return;
        }
        
        // Get selected Vendor
        const supplierId = $('#supplierDropdown').val();
        if (!supplierId) {
            bootbox.alert('Please select a Vendor before generating PO.');
            return;
        }
        
        // Create variant payload for simple view:
        // each entered raw material becomes one order line in Generate PO.
        // Keep unique ids so Raw Materials Supplied math can map per row.
        const variantsPayload = Object.entries(quantities).map(([materialName, data], idx) => {
            const materialId = parseInt(data.material_id, 10) || 0;
            const parts = String(materialName || '').split('_');
            const tail = parts.length > 1 ? parts[parts.length - 1].trim() : '';
            const qty = parseFloat(data.quantity) || 0;
            return {
                id: 'simple_' + idx,
                name: tail || materialName || 'N/A',
                product_id: materialId > 0 ? materialId : productId,
                required_quantity: qty
            };
        }).filter(v => (parseFloat(v.required_quantity) || 0) > 0);
        
        // Create materials payload with entered quantities
        const materialsPayload = Object.entries(quantities).map(([materialName, data], idx) => {
            // For simple flow, keep Total Req equal to entered qty:
            // total_req = sum(baseQty * orderQty) => set baseQty=1 only for own line, else 0.
            const ownVariantId = 'simple_' + idx;
            const quantitiesByVariant = {};
            variantsPayload.forEach((vp) => {
                quantitiesByVariant[vp.id] = (vp.id === ownVariantId) ? 1 : 0;
            });
            return {
                raw_material: materialName,
                raw_material_id: data.material_id || null,
                quantities: variantsPayload.map(vp => (vp.id === ownVariantId ? 1 : 0)),
                quantities_by_variant: quantitiesByVariant,
                available_qty: 0,
                unit_name: data.unit || null
            };
        });
        
        // Send data directly to generate_variant_po endpoint
        const formData = {
            variants: JSON.stringify(variantsPayload),
            ingredients: JSON.stringify(materialsPayload),
            supplier_id: supplierId,
            job_work_id: (typeof window.currentProcessId !== 'undefined' && window.currentProcessId) ? window.currentProcessId : '',
            note: ''
        };
        formData[csrfName] = csrfHash;

        $.ajax({
            url: site.base_url ? (site.base_url + 'RM_Calculator/generate_variant_po') : 'RM_Calculator/generate_variant_po',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                if (res.status === 'ok' && res.redirect) {
                    if (typeof saveState === 'function') {
                        saveState();
                    }
                    // Clear any existing PO data
                    var vpoKeys = ['variantpoitems', 'vpo_supplied_quantities', 'vpo_note', 'vpo_raw_materials_data',
                        'podiscount', 'potax2', 'poshipping', 'poref', 'powarehouse', 'ponote',
                        'posupplier', 'pocurrency', 'poextras', 'podate', 'postatus', 'popayment_term'];
                    for (var i = 0; i < vpoKeys.length; i++) {
                        try { localStorage.removeItem(vpoKeys[i]); } catch (e) {}
                    }
                    try { sessionStorage.removeItem('__vpo_should_clear'); } catch (e) {}
                    // Redirect to purchase screen
                    window.location.href = res.redirect;
                } else {
                    alert('Failed to proceed. Please try again.');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            }
        });
    });
}

function loadIngredientsTable(variants, Ingredients, other_categories_names, Primary_category) {
    // A valid product/process response is loaded, so hide helper note.
    $('#noteBox').hide();
    console.log("Ingredients");
    console.log(Ingredients);
    if (!Ingredients || Ingredients.length === 0)
    $('.table-section').html('<div class="alert alert-warning">No recipe found for this product.</div>');
    if (!variants || variants.length === 0) return;
    variants = variants.filter(v => v && v.name && v.name.trim() !== '');

    // --- Group raw materials ---
    const groupedMaterials = {};
    Ingredients.forEach((item) => {
        const rm = item.raw_material;
        const rawId = item.raw_material_id || item.product_id || item.id || null;
        if (!groupedMaterials[rm]) groupedMaterials[rm] = { quantities: [], available_qty: item.available_qty || 0 };
        groupedMaterials[rm].quantities.push(item.quantity_required || 0);
        groupedMaterials[rm].available_qty = item.available_qty || groupedMaterials[rm].available_qty;
        groupedMaterials[rm].unit_name = item.unit_name || groupedMaterials[rm].unit_name;
        groupedMaterials[rm].warehouse_name = item.warehouse_name || groupedMaterials[rm].warehouse_name;
        groupedMaterials[rm].warehouse_qty = item.warehouse_qty || groupedMaterials[rm].warehouse_qty;
         if (!groupedMaterials[rm].raw_material_id && rawId) {
            groupedMaterials[rm].raw_material_id = rawId;
        }
    });

    // --- Combined Primary & Secondary Category (inline layout) ---
    let categoryLine = '';
    const primaryText = Array.isArray(Primary_category) && Primary_category.length > 0 
        ? `<span style="font-weight: bold;">Primary Category:</span> <span>${Primary_category.join(', ')}</span>` 
        : '';
    const secondaryText = other_categories_names && other_categories_names.trim() !== ''
        ? `<span style="font-weight: bold; margin-left: 30px;">Secondary Category:</span> <span>${other_categories_names}</span>`
        : '';

    if (primaryText || secondaryText) {
        categoryLine = `
            <div class="category-line" style="margin-bottom: 15px; display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                ${primaryText} ${secondaryText}
            </div>`;
    }

     // --- Extract note if present ---
    const noteItem = Ingredients.find(i => i.notes && i.notes.trim() !== '');
    const noteSection = noteItem ? `
        <div class="note-section" style="background-color: #FAD4C0; padding: 10px; border-radius: 6px; margin-bottom: 10px; font-weight: bold; color: #333; display: flex; align-items: center;">
            <span class="glyphicon glyphicon-file" style="top: -15px;margin-right: 8px; font-size: 16px;"></span>
            <span>Note: ${noteItem.notes}</span>
        </div>` : '';
    
    // --- Variant Table (Single unified layout) ---
    let variantTable = `
    ${categoryLine}
    ${noteSection}
    <div class="table-title" style="text-align: left; margin-bottom: 10px; display: flex; margin-top: 30px !important; font-size: 16px;">
         Variant-Wise Order Quantities
         <button class="btn btn-primary" id="place_order" style="margin-left:auto;">Generate PO</button>
    </div>

    <div class="variant-scroll-container">
        <table class="custom-table table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="first-col" style="width: 10%;">Variants</th>
                    ${variants.map(v => `<th class="variant-col">${v.name}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="first-col" style="text-align: left;font-weight:bold;background-color: #428BCA; color: white; width: 10%;">
                        <label id="total_order_qty_label"
                            style="font-size: 15px;font-weight:bold; margin-right:10px; background-color:#428BCA; color:white; padding:8px;">
                            Total (0)
                        </label>
                    </td>
                    ${variants.map(v => `
                        <td>
                            <input type="text" value="${v.required_quantity || 0}" data-variant-id="${v.id}" class="order-quantity-input" style="height: 30px; width:70%; text-align:center;" inputmode="decimal" pattern="[0-9]*"/>
                        </td>`).join('')}
                </tr>
            </tbody>
        </table>
    </div>
    `;

        let rawMaterialTable = `
        <div class="table-title" style="text-align: left; margin-top: 30px; font-size: 16px;">Raw Material Requirements</div>
        <div class="variant-scroll-container">
            <table class="custom-table table table-bordered table-striped">
                <thead>
                    <tr>
                    <th class="first-col" 
                        style="position: relative; background: linear-gradient(to bottom right, #40b960 50%, #428BCA 50%);  color:white; height: 50px; min-width: 150px; width: 12%;">
                        <span style="position: absolute; top: 5px; left: 8px;">Raw Material</span>
                        <span style="position: absolute; bottom: 5px; right: 8px;">Variants</span>
                    </th>
                        <th class="total-col" style="width: 7%;">Total</th>
                        <th class="warehouse-col" style="width: 10%;">Warehouses</th>
                        <th class="warehouse-col" style="width: 8%;">Stock</th>
                        <th class="supplier-stock-col" style="width: 12%; white-space: normal !important; word-wrap: break-word; word-break: break-word;">Vendor Current Stock</th>
                        ${variants.map(v => `<th class="variant-col" style="width: 5%;"><span>${v.name}</span></th>`).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${Object.entries(groupedMaterials).map(([material, data]) => {
                        const related = Ingredients.filter(x => x.raw_material === material);
                        const warehouseGroups = {};

                        related.forEach(x => {
                            const warehouseName = x.warehouse_name ? x.warehouse_name.trim() : '-';
                            if (!warehouseGroups[warehouseName]) warehouseGroups[warehouseName] = [];
                            warehouseGroups[warehouseName].push(x);
                        });

                        let rows = '';

                        // ✅ Total required quantity across variants
                        let grandTotal = 0;
                        if (typeof data.quantity_required !== 'undefined' && data.quantity_required !== null) {
                            grandTotal = parseFloat(data.quantity_required) || 0;
                        } else if (Array.isArray(data.quantities)) {
                            grandTotal = data.quantities.reduce((sum, q) => sum + (parseFloat(q) || 0), 0);
                        }

                        // ✅ Filter out warehouses that are '-' or blank
                        const validWarehouseGroups = Object.entries(warehouseGroups).filter(
                            ([wh]) => wh && wh !== '-'
                        );

                        validWarehouseGroups.forEach(([wh, items], i) => {
                            const whQty = items && items.length > 0
                                ? parseFloat(items[0].warehouse_qty) || 0
                                : 0;

                            const whTotal = whQty;

                            let materialTotal = 0;
                            // if (Array.isArray(data.quantities)) {
                            //     materialTotal = variants.reduce((sum, v, idx) => {
                            //         const baseQty = parseFloat(data.quantities[idx]) || 0;
                            //         const orderQty = parseFloat(v.required_quantity) || 1;
                            //         return sum + (baseQty * orderQty);
                            //     }, 0);
                            // } else if (typeof data.quantity_required !== 'undefined' && data.quantity_required !== null) {
                            //     materialTotal = parseFloat(data.quantity_required) || 0;
                            // }

                            rows += `
                                <tr>
                                    ${i === 0 
                                        ? `<td class="first-col" rowspan="${validWarehouseGroups.length}" style="text-align:left;">
                                            ${material}${data.unit_name ? ` (${data.unit_name})` : ''}
                                        </td>
                                        <td class="total-qty total-col" rowspan="${validWarehouseGroups.length}" data-material="${material}" style="font-weight:bold;color:#40b960;">${formatDecimal(materialTotal)}</td>`
                                        : ''
                                    }
                                    <td class="warehouse-col" style="white-space: normal !important;word-wrap: break-word;word-break: break-word;">${wh}</td>
                                    <td class="total-col">${formatDecimal(whTotal)}</td>
                                    ${i === 0 
                                        ? `<td class="supplier-stock" rowspan="${validWarehouseGroups.length}" data-material="${material}" data-warehouse="${wh}">0</td>`
                                        : ''
                                    }
                                    ${variants.map((v, idx) => {
                                        return i === 0
                                            ? `<td class="variant-col" rowspan="${validWarehouseGroups.length}" 
                                                data-material="${material}" 
                                                data-variant-id="${v.id}">0</td>`
                                            : '';
                                    }).join('')}
                                </tr>
                            `;
                        });

                        return rows;
                    }).join('')}
                </tbody>
            </table>
        </div>
    `;

    

    // Inject all in .table-section
        $('.table-section').html(variantTable + rawMaterialTable);

        // Hide variant columns if all quantities = 0 
        // variants.forEach(v => {
        //     const $cells = $(`td[data-variant-id="${v.id}"]`);
        //     let allZero = true;
        //     $cells.each(function() {
        //         const val = parseFloat($(this).text()) || 0;
        //         if (val !== 0) {
        //             allZero = false;
        //             return false; // break
        //         }
        //     });
        //     if (allZero) {
        //         // Hide visually (no element removal)
        //         const variantSelector = `[data-variant-id="${v.id}"]`;
        //         $(`td${variantSelector}`).css('display', 'none');
        //         $(`input${variantSelector}`).closest('td').css('display', 'none');
        //         $('th.variant-col').filter(function() {
        //             return $(this).text().trim() === v.name.trim();
        //         }).css('display', 'none');
        //     }
        // });   
    
    
    // --- Variant ID to index map (for consistent quantity lookups) ---
    const variantIdToIndex = {};
    variants.forEach((v, idx) => { variantIdToIndex[v.id] = idx; });

    // --- On quantity change ---
    $('.order-quantity-input').on('input', function() {
        const variantId = $(this).data('variant-id');
        // const orderQty = parseFloat($(this).val()) || 1;
        let orderQty = parseFloat($(this).val());
        if (isNaN(orderQty) || $(this).val().trim() === '') orderQty = 0;
        // Update per material cell
        $('td[data-variant-id="' + variantId + '"]').each(function() {
            const material = $(this).data('material');
            const baseQty = groupedMaterials[material].quantities[variantIdToIndex[variantId]] || 0;
            const newQty = formatDecimal((baseQty * orderQty * 100) / 100);
            $(this).text(newQty);
        });

        // Update material totals
        Object.keys(groupedMaterials).forEach(material => {
            const total = variants.reduce((sum, v) => {
                const idx = variantIdToIndex[v.id];
                const baseQty = groupedMaterials[material].quantities[idx] || 0;
                // const oq = parseFloat($(`input[data-variant-id="${v.id}"]`).val()) || 1;
                let oq = parseFloat($(`input[data-variant-id="${v.id}"]`).val());
                if (isNaN(oq) || $(`input[data-variant-id="${v.id}"]`).val().trim() === '') oq = 0;
                return sum + (baseQty * oq);
            }, 0);
            $(`td.total-qty[data-material="${material}"]`).text(formatDecimal((total * 100) / 100));
        });

    });
    $(document).ready(function() {
        function updateTotalQty() {
            let totalOrderQty = 0;
            $('.order-quantity-input:visible').each(function() {
                const value = parseFloat($(this).val()) || 0;
                totalOrderQty += value;
            });
            $('#total_order_qty_label').text('Total (' + totalOrderQty + ')');
        }
        // Run once when page loads
        updateTotalQty();
        // Update when user changes a quantity
        $(document).on('input change', '.order-quantity-input', updateTotalQty);
    });
    // --- Place Order handler ---
    $(document).off('click', '#place_order').on('click', '#place_order', function() {
        try {
            // Build variants payload from CURRENT input values (not from stale variants array)
            const variantsPayload = [];
            $('.order-quantity-input:visible').each(function() {
                const variantId = $(this).data('variant-id');
                const qtyInput = $(this).val();
                const qty = parseFloat(qtyInput);
                
                // Find the variant data from the original array
                const variantData = variants.find(v => v.id == variantId);
                if (variantData && !isNaN(qty) && qty > 0) {
                    variantsPayload.push({
                        id: variantData.id,
                        name: variantData.name,
                        product_id: variantData.product_id || variantData.product || variantData.pid || null,
                        required_quantity: qty
                    });
                }
            });
            
            // Validate that at least one variant has quantity > 0
            if (variantsPayload.length === 0) {
                bootbox.alert('Please enter quantity greater than 0 for at least one variant.');
                return;
            }
            
            // Build materials payload so that quantities are ALIGNED with the selected variantsPayload.
            // This avoids index mismatches later when Purchases/generate_variant_po reconstructs totals.
            const materialsPayload = Object.entries(groupedMaterials).map(([material, data]) => {
                const quantitiesByVariant = {};
                const selectedBaseQuantities = [];

                variantsPayload.forEach((vp, selIdx) => {
                    const originalIdx = typeof variantIdToIndex[vp.id] !== 'undefined'
                        ? variantIdToIndex[vp.id]
                        : -1;
                    const baseQty = (originalIdx >= 0 && Array.isArray(data.quantities))
                        ? (parseFloat(data.quantities[originalIdx]) || 0)
                        : 0;

                    selectedBaseQuantities[selIdx] = baseQty;
                    quantitiesByVariant[vp.id] = baseQty;
                });

                return {
                    raw_material: material,
                    raw_material_id: data.raw_material_id || null,
                    quantities: selectedBaseQuantities,
                    quantities_by_variant: quantitiesByVariant, // keyed by variant_id
                    available_qty: data.available_qty,
                    unit_name: data.unit_name || null
                };
            });

            // Get selected Vendor
            const supplierId = $('#supplierDropdown').val();
            if (!supplierId) {
                bootbox.alert('Please select a Vendor before generating PO.');
                return;
            }
            
            const formData = {
                variants: JSON.stringify(variantsPayload),
                ingredients: JSON.stringify(materialsPayload),
                supplier_id: supplierId,
                job_work_id: (typeof window.currentProcessId !== 'undefined' && window.currentProcessId) ? window.currentProcessId : '',
                note: ''
            };
            formData[csrfName] = csrfHash;

            $.ajax({
                url: site.base_url ? (site.base_url + 'RM_Calculator/generate_variant_po') : 'RM_Calculator/generate_variant_po',
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function(res) {

                    if (res.csrfHash) csrfHash = res.csrfHash;
                    if (res.status === 'ok' && res.redirect) {
                        var vpoKeys = ['variantpoitems', 'vpo_supplied_quantities', 'vpo_note', 'vpo_raw_materials_data',
                            'podiscount', 'potax2', 'poshipping', 'poref', 'powarehouse', 'ponote',
                            'posupplier', 'pocurrency', 'poextras', 'podate', 'postatus', 'popayment_term'];
                        for (var i = 0; i < vpoKeys.length; i++) {
                            try { localStorage.removeItem(vpoKeys[i]); } catch (e) {}
                        }
                        try { sessionStorage.removeItem('__vpo_should_clear'); } catch (e) {}
                        window.location.href = res.redirect;
                    } else {
                        alert('Failed to proceed. Please try again.');
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                }
            });
        } catch (e) {
            console.error('Place Order error:', e);
            alert('Unable to proceed. Please refresh and try again.');
        }
    });
    // Restrict order quantity fields to numeric input only
    $(document).on('input', '.order-quantity-input', function() {
        // Allow only digits and one dot (for decimals)
        let val = $(this).val().replace(/[^0-9.]/g, '');
        // Prevent multiple dots
        if ((val.match(/\./g) || []).length > 1) {
            val = val.substr(0, val.lastIndexOf('.'));
        }
        $(this).val(val);
    });

}
