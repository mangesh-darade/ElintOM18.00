/**
 * Header designs — WordPress-style live preview (customizer layout).
 */
(function ($) {
    'use strict';

    var cfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    var csrfName = cfg.csrfName || '';
    var csrfHash = cfg.csrfHash || '';

    function syncCsrf(res) {
        if (res && res.csrf_hash) {
            csrfHash = res.csrf_hash;
            if (window.CmsAdmin && window.CmsAdmin.config) {
                window.CmsAdmin.config.csrfHash = csrfHash;
            }
            $('input[name="' + csrfName + '"]').val(csrfHash);
        }
    }

    function refreshPreviewIframe($root) {
        var url = $root.data('preview-url');
        var $frame = $('#cmsHdPreviewFrame');
        if (!$frame.length || !url) {
            return;
        }
        var sep = url.indexOf('?') >= 0 ? '&' : '?';
        $frame.attr('src', url + sep + 't=' + Date.now());
        $('#cmsHdOpenPreviewTab').attr('href', url + sep + 't=' + Date.now());
    }

    function fetchPreviewData($root) {
        var dataUrl = $root.data('preview-data-url');
        if (!dataUrl) {
            return;
        }
        var payload = {};
        if (csrfName && csrfHash) {
            payload[csrfName] = csrfHash;
        }
        $.post(dataUrl, payload, function (res) {
            syncCsrf(res);
            if (!res || res.status !== 'success') {
                return;
            }
            if (res.item_count !== undefined) {
                $('#cmsHdItemCount').text(res.item_count);
            }
            if (res.strip_html) {
                $('#cmsHdPreviewFallback').html(res.strip_html).show();
            }
        }, 'json');
    }

    /* Panel accordion (WP customizer) */
    $(document).on('click', '.cms-hd-editor .cms-hd-panel-toggle', function () {
        $(this).closest('.cms-hd-panel-section, .cms-design-assign').toggleClass('is-open');
    });

    /* Edit item modal trigger */
    $(document).on('click', '.cms-hd-edit-item', function () {
        var target = $(this).data('target');
        if (target) {
            $(target).modal('show');
        }
    });

    /* Device width toggles */
    $(document).on('click', '#cmsHdDeviceDesktop, #cmsHdDeviceMobile', function () {
        var w = $(this).data('width') || '100%';
        $('#cmsHdPreviewWrap').css('max-width', w);
        $('#cmsHdDeviceDesktop, #cmsHdDeviceMobile').removeClass('btn-primary').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('btn-primary');
    });

    $('#cmsHdRefreshPreview').on('click', function () {
        var $root = $('.cms-hd-editor');
        refreshPreviewIframe($root);
        fetchPreviewData($root);
    });

    /* Toggle active + refresh preview */
    $(document).on('change', '.hdr-item-active-toggle', function () {
        var $input = $(this);
        var $wrap = $input.closest('.hdr-item-toggle-wrap');
        var url = $wrap.data('toggle-url');
        var $root = $('.cms-hd-editor');
        var payload = { is_active: $input.is(':checked') ? 1 : 0 };
        if (csrfName && csrfHash) {
            payload[csrfName] = csrfHash;
        }
        $wrap.closest('.cms-hd-item-card').toggleClass('is-inactive', !payload.is_active);

        $.post(url, payload, function (res) {
            syncCsrf(res);
            if (!res || res.status !== 'success') {
                $input.prop('checked', !payload.is_active);
                window.alert(res && res.message ? res.message : 'Could not update.');
                return;
            }
            $wrap.find('.cms-section-status-label').text(payload.is_active ? 'Visible' : 'Hidden');
            refreshPreviewIframe($root);
            if (res.strip_html) {
                $('#cmsHdPreviewFallback').html(res.strip_html);
            }
        }, 'json').fail(function () {
            $input.prop('checked', !payload.is_active);
            window.alert('Request failed.');
        });
    });

    /* After form save the page reloads — iframe loads fresh src with timestamp in PHP */

    $(document).on('click', '.cms-hd-presets .cms-hd-preset-btn', function () {
        var $btn = $(this);
        $('#cmsHdItemKey').val($btn.data('key') || '');
        $('#cmsHdItemLabel').val($btn.data('label') || '');
        if ($btn.data('value')) {
            $('#cmsHdItemValue').val($btn.data('value'));
        }
        if ($btn.data('sort')) {
            $('#cmsHdItemSort').val($btn.data('sort'));
        }
        if ($btn.data('icon')) {
            $('#cmsHdItemIcon').val($btn.data('icon'));
        }
        $('#cmsHdItemValue').focus();
    });

    $(function () {
        if (!$('.cms-hd-editor').length) {
            return;
        }
        $('#cmsHdDeviceDesktop').addClass('btn-primary').removeClass('btn-default');
    });
})(jQuery);
