<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
$(document).ready(function () {
    var productSummaryUrl = '<?= site_url('cms_admin/catalog/product_summary_ajax/0'); ?>'.replace(/\/0\/?$/, '');
    var categorySummaryUrl = '<?= site_url('cms_admin/catalog/category_summary_ajax/0'); ?>'.replace(/\/0\/?$/, '');
    var blogSummaryUrl = '<?= site_url('cms_admin/catalog/blog_summary_ajax/0'); ?>'.replace(/\/0\/?$/, '');
    var updateBaseUrl = '<?= site_url('cms_admin/catalog/update_product_summary_ajax/0'); ?>'.replace(/\/0\/?$/, '');
    var placeholderImage = '<?= site_url('assets/uploads/no_image.png'); ?>';
    var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    var csrfName = cmsCfg.csrfName || '';
    var csrfHash = cmsCfg.csrfHash || '';

    var detailTitles = {
        product: 'Product Details',
        category: 'Category Details',
        blog: 'Blog Details'
    };

    function getEntityTypeCode() {
        return ($('#entity_master_id option:selected').data('code') || '').toString().toLowerCase();
    }

    function supportsDetailsPreview(code) {
        return code === 'product' || code === 'category' || code === 'blog';
    }

    function $preview() {
        return $('#cmsEntityDetailsPreview');
    }

    function $schemaPreview() {
        return $('#cmsEntitySchemaPreview');
    }

    var schemaSource = '';

    function formatNumber(value) {
        var parsed = parseFloat(value);
        if (isNaN(parsed)) {
            parsed = 0;
        }
        return parsed.toFixed(2);
    }

    function getCsrfToken() {
        if (!csrfName) {
            return null;
        }
        var tokenFromInput = $('input[name="' + csrfName + '"]').first().val();
        return tokenFromInput || csrfHash;
    }

    function updateDetailsSection(code) {
        var $row = $('#cmsEntityDetailsRow');
        var $schemaRow = $('#cmsEntitySchemaCollapsible');
        if (!supportsDetailsPreview(code)) {
            $row.hide();
            $preview().hide().empty();
            $schemaRow.hide();
            $schemaPreview().hide().empty();
            schemaSource = '';
            return;
        }
        $('#cmsEntityDetailsTitleText').text(detailTitles[code] || 'Entity Details');
        $row.show();
        if (code === 'product') {
            $schemaRow.show();
        } else {
            $schemaRow.hide();
            $schemaPreview().hide().empty();
            schemaSource = '';
        }
    }

    function findProductSchemaField() {
        var $field = $();
        $('label[for^="tag_value_"]').each(function () {
            var label = $.trim($(this).text()).toLowerCase();
            if (label === 'product schema' || label.indexOf('product schema') >= 0) {
                $field = $('#' + $(this).attr('for'));
                return false;
            }
        });
        return $field;
    }

    function scriptCloseTag() {
        return '</scr' + 'ipt>';
    }

    function extractJsonLdFromScriptMarkup(text) {
        var lower = text.toLowerCase();
        var open = lower.indexOf('<script');
        if (open < 0) {
            return text;
        }
        var gt = text.indexOf('>', open);
        if (gt < 0) {
            return text;
        }
        var close = lower.indexOf(scriptCloseTag(), gt);
        if (close < 0) {
            return text;
        }
        return $.trim(text.substring(gt + 1, close));
    }

    function normalizeSchemaJson(raw) {
        var text = $.trim((raw || '').toString());
        if (text === '') {
            return '';
        }
        if (text.indexOf('<script') >= 0) {
            text = extractJsonLdFromScriptMarkup(text);
        }
        try {
            return JSON.stringify(JSON.parse(text), null, 2);
        } catch (ignore) {
            return text;
        }
    }

    function wrapSchemaScript(jsonPretty) {
        return '<script type="application/ld+json">\n' + jsonPretty + '\n' + scriptCloseTag();
    }

    function renderSchemaPreview(jsonRaw, source) {
        var pretty = normalizeSchemaJson(jsonRaw);
        if (pretty === '') {
            $schemaPreview().hide().empty();
            schemaSource = '';
            return;
        }
        schemaSource = source || 'generated';
        var badgeClass = schemaSource === 'imported' ? 'is-imported' : '';
        var badgeText = schemaSource === 'imported' ? 'Imported from head script' : 'Generated from product';
        var html = ''
            + '<div class="cms-entity-schema-shell">'
            +   '<div class="cms-entity-schema-preview-head">'
            +     '<span class="cms-entity-schema-badge ' + badgeClass + '">' + badgeText + '</span>'
            +     '<div class="cms-entity-schema-actions">'
            +       '<button type="button" class="btn btn-default btn-xs cms-entity-schema-copy">Copy script</button>'
            +       '<button type="button" class="btn btn-warning btn-xs cms-entity-schema-apply-field">Apply to Product Schema field</button>'
            +     '</div>'
            +   '</div>'
            +   '<pre class="cms-entity-schema-code">' + $('<div/>').text(wrapSchemaScript(pretty)).html() + '</pre>'
            +   '<div class="cms-entity-schema-apply-msg" style="display:none;" aria-live="polite"></div>'
            + '</div>';
        $schemaPreview().html(html).show().data('schema-json', pretty);
    }

    function showSchemaApplyMessage(msg, isError) {
        var $msg = $schemaPreview().find('.cms-entity-schema-apply-msg');
        if (!$msg.length) {
            return;
        }
        $msg
            .removeClass('is-error is-success')
            .addClass(isError ? 'is-error' : 'is-success')
            .text(msg)
            .show();
        clearTimeout($schemaPreview().data('schema-msg-timer'));
        var timer = setTimeout(function () {
            $msg.fadeOut(200);
        }, 4000);
        $schemaPreview().data('schema-msg-timer', timer);
    }

    function syncSchemaPreviewFromForm() {
        if (getEntityTypeCode() !== 'product') {
            return;
        }
        var $field = findProductSchemaField();
        if ($field.length && $.trim($field.val()) !== '') {
            renderSchemaPreview($field.val(), 'imported');
            return true;
        }
        return false;
    }

    function activateTabForField($field) {
        var $pane = $field.closest('.tab-pane');
        if (!$pane.length) {
            return;
        }
        var paneId = $pane.attr('id');
        if (!paneId) {
            return;
        }
        var $tab = $('a[href="#' + paneId + '"]');
        if ($tab.length && typeof $tab.tab === 'function') {
            $tab.tab('show');
        }
    }

    function applySchemaToProductField() {
        var json = $.trim($schemaPreview().data('schema-json') || '');
        if (json === '') {
            showSchemaApplyMessage('No schema JSON to apply.', true);
            return;
        }
        var $field = findProductSchemaField();
        if (!$field.length) {
            showSchemaApplyMessage('Product Schema field is not visible yet. Select Type = Product and wait for tag fields to load.', true);
            return;
        }
        $field.val(json).trigger('change');
        if ($field.data('redactor')) {
            try {
                $field.redactor('set', json);
            } catch (ignore) {}
        }
        activateTabForField($field);
        var $group = $field.closest('.form-group');
        $group.addClass('cms-tag-import-highlight');
        setTimeout(function () {
            $group.removeClass('cms-tag-import-highlight');
        }, 2500);
        showSchemaApplyMessage('Applied to Product Schema field. Remember to click Save Tag Values.', false);
    }

    function renderProductPreview(data) {
        var imageUrl = data.image_url || placeholderImage;
        var variants = $.isArray(data.variants) ? data.variants : [];
        var variantChips = '';
        var variantNames = [];
        var inEshop = parseInt(data.in_eshop, 10) === 1;

        for (var i = 0; i < variants.length; i++) {
            var preferredName = $.trim((variants[i].eshop_name || '').toString());
            if (preferredName === '') {
                preferredName = $.trim((variants[i].name || '').toString());
            }
            if (preferredName !== '') {
                variantNames.push(preferredName);
            }
        }

        if (variantNames.length > 0) {
            for (var j = 0; j < variantNames.length; j++) {
                variantChips += '<span class="cms-entity-variant-chip">' + $('<div/>').text(variantNames[j]).html() + '</span>';
            }
        } else {
            variantChips = '<span class="cms-entity-variant-empty">No variants</span>';
        }

        var html = ''
            + '<div class="cms-entity-product-shell" data-preview-type="product">'
            +   '<div class="cms-entity-product-head">'
            +     '<div class="cms-entity-product-image-wrap">'
            +       '<img class="cms-entity-product-image" src="' + $('<div/>').text(imageUrl).html() + '" alt="" onerror="this.onerror=null;this.src=\'' + $('<div/>').text(placeholderImage).html() + '\';" />'
            +     '</div>'
            +     '<div class="cms-entity-product-head-main">'
            +       '<h3 class="cms-entity-product-title">' + $('<div/>').text(data.name || '').html() + '</h3>'
            +       '<span class="cms-entity-status-badge ' + (inEshop ? 'is-active' : 'is-inactive') + '">' + (inEshop ? 'In E-Shop' : 'Not In E-Shop') + '</span>'
            +     '</div>'
            +     '<div class="cms-entity-product-actions">'
            +       '<button type="button" class="btn btn-primary btn-sm cms-entity-edit-btn">Edit</button>'
            +       '<button type="button" class="btn btn-success btn-sm cms-entity-save-btn" style="display:none;">Save</button>'
            +       '<button type="button" class="btn btn-default btn-sm cms-entity-cancel-btn" style="display:none;">Cancel</button>'
            +     '</div>'
            +   '</div>'
            +   '<div class="cms-entity-info-grid">'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Product Code <i class="fa fa-ban text-danger cms-readonly-icon" title="Read only"></i></span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.code || '').html() + '" data-field="code" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">E-Shop Name</span><input type="text" class="form-control cms-entity-field is-editable" value="' + $('<div/>').text(data.eshop_name || '').html() + '" data-field="eshop_name" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Category <i class="fa fa-ban text-danger cms-readonly-icon" title="Read only"></i></span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.category || '').html() + '" data-field="category" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Subcategory <i class="fa fa-ban text-danger cms-readonly-icon" title="Read only"></i></span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.subcategory || '').html() + '" data-field="subcategory" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">ERP Price</span><input type="number" step="0.01" class="form-control cms-entity-field cms-entity-field--price is-editable" value="' + formatNumber(data.price) + '" data-field="price" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">MRP</span><input type="number" step="0.01" class="form-control cms-entity-field cms-entity-field--price is-editable" value="' + formatNumber(data.mrp) + '" data-field="mrp" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">E-Shop Price</span><input type="number" step="0.01" class="form-control cms-entity-field cms-entity-field--price is-editable" value="' + formatNumber(data.eshop_price) + '" data-field="eshop_price" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">E-Shop MRP</span><input type="number" step="0.01" class="form-control cms-entity-field cms-entity-field--price is-editable" value="' + formatNumber(data.eshop_mrp) + '" data-field="eshop_mrp" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Stock <i class="fa fa-ban text-danger cms-readonly-icon" title="Read only"></i></span><input type="text" class="form-control cms-entity-field cms-entity-field--stock" value="' + formatNumber(data.total_qty) + '" data-field="stock" readonly></div>'
            +     '<div class="cms-entity-info-card cms-entity-info-card--variants"><span class="cms-label">Variants <i class="fa fa-ban text-danger cms-readonly-icon" title="Read only"></i></span><div class="cms-entity-variant-list">' + variantChips + '</div></div>'
            +   '</div>'
            +   '<div class="cms-entity-save-msg" style="display:none;"></div>'
            + '</div>';
        $preview().html(html).show().data('entity-id', parseInt(data.id, 10)).data('preview-type', 'product');
    }

    function renderCategoryPreview(data) {
        var imageUrl = data.image_url || placeholderImage;
        var inEshop = parseInt(data.in_eshop, 10) === 1;
        var html = ''
            + '<div class="cms-entity-product-shell" data-preview-type="category">'
            +   '<div class="cms-entity-product-head">'
            +     '<div class="cms-entity-product-image-wrap">'
            +       '<img class="cms-entity-product-image" src="' + $('<div/>').text(imageUrl).html() + '" alt="" onerror="this.onerror=null;this.src=\'' + $('<div/>').text(placeholderImage).html() + '\';" />'
            +     '</div>'
            +     '<div class="cms-entity-product-head-main">'
            +       '<h3 class="cms-entity-product-title">' + $('<div/>').text(data.name || '').html() + '</h3>'
            +       '<span class="cms-entity-status-badge ' + (inEshop ? 'is-active' : 'is-inactive') + '">' + (inEshop ? 'In E-Shop' : 'Not In E-Shop') + '</span>'
            +     '</div>'
            +   '</div>'
            +   '<div class="cms-entity-info-grid">'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Category Code</span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.code || '').html() + '" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Parent Category</span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.parent_name || '—').html() + '" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Products</span><input type="text" class="form-control cms-entity-field" value="' + parseInt(data.product_count, 10) + '" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Subcategories</span><input type="text" class="form-control cms-entity-field" value="' + parseInt(data.subcategory_count, 10) + '" readonly></div>'
            +   '</div>'
            + '</div>';
        $preview().html(html).show().data('entity-id', parseInt(data.id, 10)).data('preview-type', 'category');
    }

    function renderBlogPreview(data) {
        var status = $.trim((data.status || '').toString());
        var isPublished = status.toLowerCase() === 'published';
        var html = ''
            + '<div class="cms-entity-product-shell" data-preview-type="blog">'
            +   '<div class="cms-entity-product-head">'
            +     '<div class="cms-entity-product-head-main">'
            +       '<h3 class="cms-entity-product-title">' + $('<div/>').text(data.page_name || '').html() + '</h3>'
            +       '<span class="cms-entity-status-badge ' + (isPublished ? 'is-active' : 'is-inactive') + '">' + (status !== '' ? status : 'Unknown') + '</span>'
            +     '</div>'
            +   '</div>'
            +   '<div class="cms-entity-info-grid">'
            +     '<div class="cms-entity-info-card"><span class="cms-label">URL Slug</span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.url || '').html() + '" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Page Type</span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.page_type || 'blog').html() + '" readonly></div>'
            +     '<div class="cms-entity-info-card"><span class="cms-label">Last Updated</span><input type="text" class="form-control cms-entity-field" value="' + $('<div/>').text(data.updated_at || '—').html() + '" readonly></div>'
            +   '</div>'
            + '</div>';
        $preview().html(html).show().data('entity-id', parseInt(data.id, 10)).data('preview-type', 'blog');
    }

    function setEditMode(enabled) {
        var $el = $preview();
        $el.find('.cms-entity-field.is-editable').prop('readonly', !enabled);
        $el.find('.cms-entity-product-shell').toggleClass('is-edit-mode', enabled);
        $el.find('.cms-entity-edit-btn').toggle(!enabled);
        $el.find('.cms-entity-save-btn, .cms-entity-cancel-btn').toggle(enabled);
    }

    function showSaveMessage(msg, isError) {
        var $msg = $preview().find('.cms-entity-save-msg');
        $msg.removeClass('is-error is-success').addClass(isError ? 'is-error' : 'is-success').text(msg).show();
    }

    function saveEditedProduct() {
        var $el = $preview();
        var productId = parseInt($el.data('entity-id'), 10);
        if (!productId || $el.data('preview-type') !== 'product') {
            return;
        }

        var payload = {
            eshop_name: $.trim($el.find('[data-field="eshop_name"]').val()),
            price: $el.find('[data-field="price"]').val(),
            mrp: $el.find('[data-field="mrp"]').val(),
            eshop_price: $el.find('[data-field="eshop_price"]').val(),
            eshop_mrp: $el.find('[data-field="eshop_mrp"]').val()
        };
        var token = getCsrfToken();
        if (csrfName && token) {
            payload[csrfName] = token;
        }

        $el.find('.cms-entity-save-btn').prop('disabled', true).text('Saving...');
        $.ajax({
            url: updateBaseUrl + '/' + productId,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            if (!res || !res.ok) {
                showSaveMessage((res && res.message) ? res.message : 'Failed to save.', true);
                return;
            }
            showSaveMessage('Saved successfully.', false);
            loadEntityDetailsPreview(productId);
        }).fail(function () {
            showSaveMessage('Failed to save product details.', true);
        }).always(function () {
            $el.find('.cms-entity-save-btn').prop('disabled', false).text('Save');
        });
    }

    function loadEntityDetailsPreview(entityId) {
        var code = getEntityTypeCode();
        updateDetailsSection(code);

        if (!entityId || !supportsDetailsPreview(code)) {
            $preview().hide().empty();
            $schemaPreview().hide().empty();
            schemaSource = '';
            return;
        }

        var loadingText = 'Loading ' + (detailTitles[code] || 'details').toLowerCase() + '...';
        $preview().html('<div class="text-muted"><i class="fa fa-spinner fa-spin"></i> ' + loadingText + '</div>').show();

        var url = '';
        var failText = 'Details not found.';
        if (code === 'product') {
            url = productSummaryUrl + '/' + parseInt(entityId, 10);
            failText = 'Product details not found.';
        } else if (code === 'category') {
            url = categorySummaryUrl + '/' + parseInt(entityId, 10);
            failText = 'Category details not found.';
        } else if (code === 'blog') {
            url = blogSummaryUrl + '/' + parseInt(entityId, 10);
            failText = 'Blog page details not found.';
        }

        $.getJSON(url, function (res) {
            if (!res || !res.ok) {
                $preview().html('<div class="text-danger">' + failText + '</div>').show();
                return;
            }
            if (code === 'product') {
                renderProductPreview(res);
                if (!syncSchemaPreviewFromForm() && res.schema_json) {
                    renderSchemaPreview(res.schema_json, 'generated');
                }
            } else if (code === 'category') {
                renderCategoryPreview(res);
            } else if (code === 'blog') {
                renderBlogPreview(res);
            }
        }).fail(function () {
            $preview().html('<div class="text-danger">Failed to load details.</div>').show();
        });
    }

    $('#entity_master_id').on('change.cmsEnhance', function () {
        updateDetailsSection(getEntityTypeCode());
        $preview().hide().empty();
        $schemaPreview().hide().empty();
        schemaSource = '';
    });

    $('#entity_id').on('change.cmsEnhance', function () {
        loadEntityDetailsPreview($(this).val());
    });

    $(document).on('click', '.cms-entity-edit-btn', function () {
        if ($preview().data('preview-type') === 'product') {
            setEditMode(true);
        }
    });
    $(document).on('click', '.cms-entity-cancel-btn', function () {
        var entityId = parseInt($preview().data('entity-id'), 10);
        if (entityId && $preview().data('preview-type') === 'product') {
            loadEntityDetailsPreview(entityId);
        } else {
            setEditMode(false);
        }
    });
    $(document).on('click', '.cms-entity-save-btn', function () {
        saveEditedProduct();
    });

    $(document).on('click', '.cms-entity-schema-copy', function () {
        var json = $.trim($schemaPreview().data('schema-json') || '');
        if (json === '') {
            showSchemaApplyMessage('Nothing to copy.', true);
            return;
        }
        var script = wrapSchemaScript(json);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(script).then(function () {
                showSchemaApplyMessage('Script copied to clipboard.', false);
            }).fail(function () {
                showSchemaApplyMessage('Could not copy to clipboard.', true);
            });
        } else {
            window.prompt('Copy this script:', script);
            showSchemaApplyMessage('Copy the script from the dialog box.', false);
        }
    });

    $(document).on('click', '.cms-entity-schema-apply-field', function () {
        applySchemaToProductField();
    });

    $(document).on('cms:headTagsImported', function (e, applied) {
        if (getEntityTypeCode() !== 'product') {
            return;
        }
        setTimeout(function () {
            if (syncSchemaPreviewFromForm()) {
                return;
            }
            if (applied && typeof applied === 'object') {
                $.each(applied, function (tagId, value) {
                    if (!value) {
                        return;
                    }
                    var parsed = normalizeSchemaJson(value);
                    if (parsed !== '' && (parsed.indexOf('"@type"') >= 0 || parsed.indexOf('"Product"') >= 0)) {
                        renderSchemaPreview(parsed, 'imported');
                        return false;
                    }
                });
            }
        }, 300);
    });

    $(document).on('cms:entityTagFormReloaded', function () {
        if (getEntityTypeCode() === 'product') {
            syncSchemaPreviewFromForm();
        }
    });

    updateDetailsSection(getEntityTypeCode());
    if ($('#entity_id').val()) {
        loadEntityDetailsPreview($('#entity_id').val());
    }
});
</script>
