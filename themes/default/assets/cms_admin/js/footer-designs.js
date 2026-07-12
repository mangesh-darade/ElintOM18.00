/**
 * Footer designs — WordPress-style live preview (customizer layout).
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
        var $frame = $('#cmsFdPreviewFrame');
        if (!$frame.length || !url) {
            return;
        }
        var sep = url.indexOf('?') >= 0 ? '&' : '?';
        $frame.attr('src', url + sep + 't=' + Date.now());
        $('#cmsFdOpenPreviewTab').attr('href', url + sep + 't=' + Date.now());
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
                $('#cmsFdItemCount').text(res.item_count);
            }
            if (res.strip_html) {
                $('#cmsFdPreviewFallback').html(res.strip_html).show();
            }
        }, 'json');
    }

    $(document).on('click', '.cms-fd-editor .cms-hd-panel-toggle', function () {
        $(this).closest('.cms-hd-panel-section, .cms-design-assign').toggleClass('is-open');
    });

    $(document).on('click', '.cms-fd-editor .cms-hd-edit-item', function () {
        var target = $(this).data('target');
        if (target) {
            $(target).modal('show');
        }
    });

    $(document).on('click', '#cmsFdDeviceDesktop, #cmsFdDeviceMobile', function () {
        var w = $(this).data('width') || '100%';
        $('#cmsFdPreviewWrap').css('max-width', w);
        $('#cmsFdDeviceDesktop, #cmsFdDeviceMobile').removeClass('btn-primary').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('btn-primary');
    });

    $('#cmsFdRefreshPreview').on('click', function () {
        var $root = $('.cms-fd-editor');
        refreshPreviewIframe($root);
        fetchPreviewData($root);
    });

    $(document).on('change', '.ftr-item-active-toggle', function () {
        var $input = $(this);
        var $wrap = $input.closest('.ftr-item-toggle-wrap');
        var url = $wrap.data('toggle-url');
        var $root = $('.cms-fd-editor');
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
                $('#cmsFdPreviewFallback').html(res.strip_html);
            }
        }, 'json').fail(function () {
            $input.prop('checked', !payload.is_active);
            window.alert('Request failed.');
        });
    });

    $(document).on('click', '.cms-fd-editor .cms-hd-presets .cms-hd-preset-btn', function () {
        var $btn = $(this);
        $('#cmsFdItemKey').val($btn.data('key') || '');
        $('#cmsFdItemLabel').val($btn.data('label') || '');
        if ($btn.data('value')) {
            $('#cmsFdItemValue').val($btn.data('value'));
        }
        if ($btn.data('sort')) {
            $('#cmsFdItemSort').val($btn.data('sort'));
        }
        if ($btn.data('icon')) {
            $('#cmsFdItemIcon').val($btn.data('icon'));
        }
        $('#cmsFdItemValue').focus();
    });

    $(function () {
        if (!$('.cms-fd-editor').length) {
            return;
        }
        $('#cmsFdDeviceDesktop').addClass('btn-primary').removeClass('btn-default');
    });
})(jQuery);
