/**
 * CMS Admin enhancements — reusable product details modal (additive).
 */
(function ($) {
    'use strict';

    var cfg = window.CmsAdmin && window.CmsAdmin.config && window.CmsAdmin.config.productModal;

    function detailsUrl(productId) {
        if (!cfg || !cfg.detailsUrl) {
            return '';
        }
        return cfg.detailsUrl.replace(/\/0\/?$/, '') + '/' + parseInt(productId, 10);
    }

    function renderProductGallery() {
        var $body = $('#cmsProductDetailsModalBody');
        $body.off('click.cmsGallery', '.cms-product-gallery-thumb').on('click.cmsGallery', '.cms-product-gallery-thumb', function () {
            var src = $(this).data('src');
            if (!src) {
                return;
            }
            $body.find('.cms-product-gallery-thumb').removeClass('is-active');
            $(this).addClass('is-active');
            $('#cmsProductModalMainImg').attr('src', src);
        });
    }

    function getProductDetailsAjax(productId, callback) {
        $.ajax({
            url: detailsUrl(productId),
            type: 'GET',
            dataType: 'html'
        }).done(function (html) {
            if (typeof callback === 'function') {
                callback(null, html);
            }
        }).fail(function () {
            if (typeof callback === 'function') {
                callback(new Error('load failed'));
            }
        });
    }

    function openProductDetailsModal(productId) {
        productId = parseInt(productId, 10);
        if (productId < 1) {
            return;
        }
        var $cmsModal = $('#cmsProductDetailsModal');
        var $body = $('#cmsProductDetailsModalBody');

        if ($cmsModal.length && $body.length && cfg && cfg.detailsUrl) {
            $('#cmsProductDetailsModalLabel').text('Product details');
            $body.html('<div class="text-center text-muted" style="padding:40px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $cmsModal.modal('show');
            getProductDetailsAjax(productId, function (err, html) {
                if (err || !html) {
                    $body.html('<div class="alert alert-warning">Unable to load product details right now.</div>');
                    return;
                }
                $body.html(html);
                renderProductGallery();
            });
            return;
        }

        // Legacy fallback for older screens that still rely on ERP modal view.
        if (!window.site || !site.base_url) {
            return;
        }
        var targetUrl = site.base_url + 'products/modal_view/' + productId;
        var $legacyModal = $('#myModal');
        if ($legacyModal.length) {
            $legacyModal.removeData('bs.modal');
            $legacyModal.find('.modal-content').remove();
            $legacyModal.modal({ remote: targetUrl });
            $legacyModal.modal('show');
            return;
        }
        if ($cmsModal.length && $body.length) {
            $('#cmsProductDetailsModalLabel').text('Product details');
            $body.html(
                '<iframe src="' + targetUrl + '" ' +
                'style="width:100%;height:75vh;border:0;" ' +
                'loading="lazy"></iframe>'
            );
            $cmsModal.modal('show');
        }
    }

    $(document).ready(function () {
        $(document).on('click', '.cms-view-product-btn', function (e) {
            e.preventDefault();
            openProductDetailsModal($(this).data('product-id'));
        });
    });

    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.getProductDetailsAjax = getProductDetailsAjax;
    window.CmsAdmin.renderProductGallery = renderProductGallery;
    window.CmsAdmin.openProductDetailsModal = openProductDetailsModal;
})(jQuery);
