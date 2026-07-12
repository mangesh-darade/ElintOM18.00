/**
 * CMS catalog page — image fallbacks & UI helpers (additive).
 */
(function ($) {
    'use strict';

    function applyImageFallback(img) {
        var $img = $(img);
        var step = parseInt($img.data('fallback-step') || 0, 10);
        if (step === 0 && $img.data('fallback')) {
            $img.data('fallback-step', 1);
            $img.attr('src', $img.data('fallback'));
            return;
        }
        if (step <= 1 && $img.data('placeholder')) {
            $img.data('fallback-step', 2);
            $img.addClass('cms-img-error');
            $img.attr('src', $img.data('placeholder'));
        }
    }

    function bindImageFallbacks($root) {
        ($root || $(document)).find('img.cms-img-fallback').each(function () {
            var img = this;
            if (img.complete && img.naturalWidth === 0) {
                applyImageFallback(img);
            }
        });
    }

    $(document).on('error', 'img.cms-img-fallback', function () {
        applyImageFallback(this);
    });

    function openErpProductModal(productId) {
        productId = parseInt(productId, 10);
        if (productId < 1 || !window.site || !site.base_url) {
            return;
        }
        var $modal = $('#myModal');
        if (!$modal.length) {
            return;
        }
        $modal.removeData('bs.modal');
        $modal.find('.modal-content').remove();
        $modal.modal({ remote: site.base_url + 'products/modal_view/' + productId });
        $modal.modal('show');
    }

    $(document).ready(function () {
        if ($('body').attr('data-cms-section') !== 'catalog') {
            return;
        }
        bindImageFallbacks();

        $('#myModal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
            $(this).find('.modal-content').remove();
        });

        $(document).on('click', '.cms-catalog-products-table tbody tr.cms-product-row[data-product-id]', function (e) {
            if ($(e.target).closest('input, a, button, label, .iCheck-helper').length) {
                return;
            }
            var id = $(this).attr('data-product-id');
            if (id) {
                e.preventDefault();
                openErpProductModal(id);
            }
        });
    });

    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.bindCatalogImageFallbacks = bindImageFallbacks;
    window.CmsAdmin.openErpProductModal = openErpProductModal;
})(jQuery);
