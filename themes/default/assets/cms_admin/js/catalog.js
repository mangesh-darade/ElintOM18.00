/**
 * CMS Admin — catalog / manage products (e-shop visibility toggles).
 */
(function ($) {
    'use strict';

    var cfg = window.CmsAdmin && window.CmsAdmin.config && window.CmsAdmin.config.catalog;
    if (!cfg || !cfg.ajaxUrl) {
        return;
    }

    function appendCsrf(postData) {
        if (!cfg.csrfName || !cfg.csrfHash) {
            return postData;
        }
        return postData + '&' + encodeURIComponent(cfg.csrfName) + '=' + encodeURIComponent(cfg.csrfHash);
    }

    function applyCatalogEshopVisibility() {
        $('input.eshop_categories[parent="0"]').each(function () {
            var cid = $(this).val();
            var on = $(this).prop('checked');
            if (!on) {
                $('.prdcat_' + cid).hide();
            } else {
                $('.prdcat_' + cid).show();
            }
        });
        $('input.eshop_categories').each(function () {
            var parentId = $(this).attr('parent');
            var sid = $(this).val();
            if (!parentId || parentId === '0') {
                return;
            }
            var parentOn = $('input.eshop_categories[parent="0"][value="' + parentId + '"]').prop('checked');
            var subOn = $(this).prop('checked');
            if (!parentOn || !subOn) {
                $('.prdsubcat_' + sid).hide();
            } else {
                $('.prdsubcat_' + sid).show();
            }
        });
    }

    function manageEshopCategory(categoryId, parentId, eshopStatus) {
        var postData = 'action=manage_eshop_category';
        postData += '&category_id=' + categoryId;
        postData += '&parent_id=' + parentId;
        postData += '&eshop_status=' + eshopStatus;
        postData = appendCsrf(postData);

        $.ajax({
            type: 'POST',
            url: cfg.ajaxUrl,
            data: postData,
            success: function (data) {
                var objData = JSON.parse(data);
                if (objData.status !== 'SUCCESS') {
                    return;
                }
                if (parseInt(parentId, 10) === 0) {
                    var inEshop;
                    if (eshopStatus) {
                        $('.prdcat_' + categoryId).show();
                        inEshop = '<a href="' + cfg.manageUrl + '/' + categoryId + '" class="ws-badge-active"><i class="fa fa-list"></i></a>';
                    } else {
                        $('.prdcat_' + categoryId).hide();
                        inEshop = '<span class="ws-badge-inactive"><i class="fa fa-ban"></i></span>';
                        $('input.eshop_categories[parent="' + categoryId + '"]').iCheck('uncheck');
                    }
                    $('.eshop_category_' + categoryId).html(inEshop);
                    if (window.CmsAdmin && typeof window.CmsAdmin.applyCatalogEshopVisibility === 'function') {
                        window.CmsAdmin.applyCatalogEshopVisibility();
                    }
                    if (window.CmsAdmin && window.CmsAdmin.catalogProductsTable) {
                        window.CmsAdmin.catalogProductsTable.columns.adjust();
                    }
                } else {
                    applyCatalogEshopVisibility();
                }
            }
        });
    }

    function manageEshopProduct(productId, variantId, eshopStatus) {
        var postData = 'action=manage_eshop_product';
        postData += '&product_id=' + productId;
        postData += '&variant_id=' + variantId;
        postData += '&eshop_status=' + eshopStatus;
        postData = appendCsrf(postData);

        $.ajax({
            type: 'POST',
            url: cfg.ajaxUrl,
            data: postData
        });
    }

    $(document).ready(function () {
        $('input.eshop_categories').on('ifToggled', function () {
            var checked = $(this).is(':checked');
            var eshopStatus = checked ? 1 : 0;
            manageEshopCategory($(this).val(), $(this).attr('parent'), eshopStatus);
        });

        $('input.eshop_product').on('ifToggled', function () {
            var checked = $(this).is(':checked');
            var eshopStatus = checked ? 1 : 0;
            manageEshopProduct($(this).val(), $(this).attr('variant'), eshopStatus);
        });

        $('input#all_products').on('ifToggled', function () {
            var checked = $(this).is(':checked');
            if (checked) {
                $('input.prd_chk:visible').iCheck('check');
            } else {
                $('input.prd_chk:visible').iCheck('uncheck');
            }
        });

        applyCatalogEshopVisibility();

        window.CmsAdmin = window.CmsAdmin || {};
        window.CmsAdmin.applyCatalogEshopVisibility = applyCatalogEshopVisibility;
    });
})(jQuery);
