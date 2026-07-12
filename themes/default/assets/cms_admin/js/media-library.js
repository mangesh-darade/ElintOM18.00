(function ($) {
    'use strict';

    function mediaConfig() {
        var cfg = (window.CmsMediaLibrary && window.CmsMediaLibrary.config)
            ? window.CmsMediaLibrary.config
            : {};
        if ((!cfg.csrfName || !cfg.csrfHash) && window.CmsAdmin && window.CmsAdmin.config) {
            cfg.csrfName = cfg.csrfName || window.CmsAdmin.config.csrfName;
            cfg.csrfHash = cfg.csrfHash || window.CmsAdmin.config.csrfHash;
        }
        return cfg;
    }

    var pickerState = {
        preset: 'all',
        onSelect: null,
        targetInput: null,
        previewSelector: null
    };

    function csrfPayload() {
        var cfg = mediaConfig();
        var data = {};
        if (cfg.csrfName && cfg.csrfHash) {
            data[cfg.csrfName] = cfg.csrfHash;
        }
        return data;
    }

    function ajaxError(xhr, fallback) {
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
            return xhr.responseJSON.error;
        }
        if (xhr && xhr.status === 403) {
            return 'Session expired. Refresh the page and try again.';
        }
        return fallback || 'Request failed.';
    }

    function showToast(message, isError) {
        if (!message) {
            return;
        }
        var $toast = $('#cmsMediaToast');
        if (!$toast.length) {
            $toast = $('<div id="cmsMediaToast" class="cms-media-toast" role="alert"></div>').appendTo('body');
        }
        $toast.toggleClass('cms-media-toast--error', !!isError).text(message).addClass('is-visible');
        window.clearTimeout(window._cmsMediaToastTimer);
        window._cmsMediaToastTimer = window.setTimeout(function () {
            $toast.removeClass('is-visible');
        }, isError ? 4000 : 2500);
    }

    function showUploadError(message) {
        var $el = $('#cmsMediaUploadError');
        if (!$el.length) {
            if (message) {
                showToast(message, true);
            }
            return;
        }
        if (!message) {
            $el.hide().empty();
            return;
        }
        $el.text(message).show();
    }

    function copyText(text) {
        text = String(text || '').trim();
        if (!text) {
            return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                showToast('Path copied to clipboard');
            }).catch(function () {
                fallbackCopy(text);
            });
            return;
        }
        fallbackCopy(text);
    }

    function fallbackCopy(text) {
        var $tmp = $('<textarea>').val(text).appendTo('body').select();
        try {
            document.execCommand('copy');
            showToast('Path copied to clipboard');
        } catch (e) {
            showToast('Could not copy path', true);
        }
        $tmp.remove();
    }

    function publicUrl(storedPath) {
        var cfg = mediaConfig();
        return String(cfg.uploadsBase || '') + String(storedPath || '').replace(/^\/+/, '');
    }

    function renderPickerItems(items) {
        var $grid = $('#cmsMediaPickerGrid');
        var $empty = $('#cmsMediaPickerEmpty');
        $('#cmsMediaPickerLoading').hide();
        $grid.empty();

        if (!items || !items.length) {
            $empty.text('No images in this folder.').show();
            return;
        }
        $empty.hide();

        items.forEach(function (item) {
            var url = item.url || publicUrl(item.stored_path);
            var $btn = $('<button type="button" class="cms-media-picker-item"></button>');
            $btn.attr('data-stored-path', item.stored_path || '');
            $btn.attr('data-url', url);
            $btn.append($('<img>').attr('src', url).attr('alt', item.file_name || ''));
            $btn.append($('<span></span>').text(item.file_name || ''));
            $grid.append($btn);
        });
    }

    function loadPickerItems() {
        var cfg = mediaConfig();
        var preset = $('#cmsMediaPickerPreset').val() || pickerState.preset || 'all';
        var q = $('#cmsMediaPickerSearch').val() || '';
        $('#cmsMediaPickerLoading').show();
        $('#cmsMediaPickerEmpty').hide();
        $('#cmsMediaPickerGrid').empty();

        if (!cfg.listUrl) {
            $('#cmsMediaPickerLoading').hide();
            $('#cmsMediaPickerEmpty').text('Media library is not configured on this page.').show();
            return;
        }

        $.getJSON(cfg.listUrl, { preset: preset, q: q, limit: 80 })
            .done(function (res) {
                if (!res || !res.success) {
                    $('#cmsMediaPickerLoading').hide();
                    showToast((res && res.error) ? res.error : 'Could not load media.', true);
                    $('#cmsMediaPickerEmpty').text('Could not load media.').show();
                    return;
                }
                renderPickerItems(res.items || []);
            })
            .fail(function (xhr) {
                $('#cmsMediaPickerLoading').hide();
                showToast(ajaxError(xhr, 'Could not load media.'), true);
                $('#cmsMediaPickerEmpty').text('Could not load media.').show();
            });
    }

    function openPickerModal(options) {
        options = options || {};
        pickerState.preset = options.preset || 'all';
        pickerState.onSelect = typeof options.onSelect === 'function' ? options.onSelect : null;
        pickerState.targetInput = options.targetInput || null;
        pickerState.previewSelector = options.previewSelector || null;

        $('#cmsMediaPickerPreset').val(pickerState.preset);
        $('#cmsMediaPickerSearch').val('');
        $('#cmsMediaPickerModal').addClass('is-open').attr('aria-hidden', 'false');
        loadPickerItems();
    }

    function closePickerModal() {
        $('#cmsMediaPickerModal').removeClass('is-open').attr('aria-hidden', 'true');
        pickerState.onSelect = null;
        pickerState.targetInput = null;
        pickerState.previewSelector = null;
    }

    function applySelection(storedPath, url) {
        if (pickerState.targetInput) {
            var $input = $(pickerState.targetInput);
            if ($input.length) {
                $input.val(storedPath).trigger('change');
            }
        }
        if (pickerState.previewSelector) {
            var $preview = $(pickerState.previewSelector);
            if ($preview.length) {
                if ($preview.is('img')) {
                    $preview.attr('src', url);
                    if ($preview.attr('id') === 'faviconPreviewThumb') {
                        $('.cms-lb-favicon-preview-wrap').removeClass('is-empty');
                        $('#faviconPreviewPath').text(storedPath || '');
                    }
                    $preview.closest('.cms-banner-preview, .cms-lb-logo-preview, .cms-media-pick-preview').show();
                } else {
                    var $img = $preview.find('img').first();
                    if ($img.length) {
                        $img.attr('src', url);
                    } else {
                        $preview.html($('<img>').attr('src', url).attr('alt', 'Preview'));
                    }
                    $preview.closest('.cms-media-pick-preview, .cms-banner-preview, .cms-lb-logo-preview').show();
                    $preview.show();
                }
            }
        }
        if (pickerState.onSelect) {
            pickerState.onSelect({ stored_path: storedPath, url: url });
        }
        closePickerModal();
    }

    function openUploadPanel(preset) {
        showUploadError('');
        if (preset) {
            $('#cmsMediaUploadPreset').val(preset);
        }
        $('#cmsMediaUploadFile').val('');
        $('#cmsMediaUploadPanel').addClass('is-open').attr('aria-hidden', 'false');
    }

    function closeUploadPanel() {
        showUploadError('');
        $('#cmsMediaUploadPanel').removeClass('is-open').attr('aria-hidden', 'true');
    }

    function uploadFile(file, preset, done) {
        if (!file) {
            showUploadError('Please choose an image file.');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showUploadError('File is too large. Maximum size is 2 MB.');
            return;
        }

        var cfg = mediaConfig();
        if (!cfg.uploadUrl) {
            showUploadError('Media library upload is not configured on this page.');
            return;
        }

        var formData = new FormData();
        formData.append('media_file', file);
        formData.append('preset', preset || 'misc');
        if (cfg.csrfName && cfg.csrfHash) {
            formData.append(cfg.csrfName, cfg.csrfHash);
        }

        $.ajax({
            url: cfg.uploadUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (res && res.success) {
                showToast(res.message || 'File uploaded.');
                showUploadError('');
                if (typeof done === 'function') {
                    done(res);
                }
                return;
            }
            var err = (res && res.error) ? res.error : 'Upload failed.';
            showUploadError(err);
            if (!$('#cmsMediaUploadPanel').hasClass('is-open')) {
                showToast(err, true);
            }
        }).fail(function (xhr) {
            var err = ajaxError(xhr, 'Upload failed.');
            showUploadError(err);
            if (!$('#cmsMediaUploadPanel').hasClass('is-open')) {
                showToast(err, true);
            }
        });
    }

    window.CMSMediaPicker = {
        open: openPickerModal,
        close: closePickerModal,
        publicUrl: publicUrl
    };

    $(function () {
        if (!$('#cmsMediaPickerModal').length && !$('[data-cms-media-index="1"]').length) {
            return;
        }

        $(document).on('click', '[data-cms-media-close="1"]', closePickerModal);
        $(document).on('click', '[data-cms-upload-close="1"]', closeUploadPanel);

        $('#cmsMediaPickerPreset, #cmsMediaPickerSearch').on('change input', function () {
            if ($('#cmsMediaPickerModal').hasClass('is-open')) {
                window.clearTimeout(window._cmsMediaPickerSearchTimer);
                window._cmsMediaPickerSearchTimer = window.setTimeout(loadPickerItems, 250);
            }
        });

        $(document).on('click', '.cms-media-picker-item', function () {
            applySelection($(this).attr('data-stored-path') || '', $(this).attr('data-url') || '');
        });

        $(document).on('click', '.cms-media-pick-btn', function (e) {
            e.preventDefault();
            openPickerModal({
                preset: $(this).attr('data-media-preset') || 'misc',
                targetInput: $(this).attr('data-media-target') || null,
                previewSelector: $(this).attr('data-media-preview') || null
            });
        });

        $('#cmsMediaUploadOpen, #cmsMediaUploadOpenEmpty').on('click', function () {
            var pagePreset = $('[data-cms-media-index="1"]').attr('data-cms-media-preset') || '';
            openUploadPanel(pagePreset);
        });

        $('#cmsMediaUploadForm').on('submit', function (e) {
            e.preventDefault();
            var file = $('#cmsMediaUploadFile')[0] && $('#cmsMediaUploadFile')[0].files
                ? $('#cmsMediaUploadFile')[0].files[0]
                : null;
            uploadFile(file, $('#cmsMediaUploadPreset').val() || 'misc', function () {
                closeUploadPanel();
                if ($('[data-cms-media-index="1"]').length) {
                    window.location.reload();
                } else if ($('#cmsMediaPickerModal').hasClass('is-open')) {
                    loadPickerItems();
                }
            });
        });

        $('#cmsMediaPickerUploadInput').on('change', function () {
            var file = this.files && this.files[0] ? this.files[0] : null;
            var inputEl = this;
            if (!file) {
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                showToast('File is too large. Maximum size is 2 MB.', true);
                inputEl.value = '';
                return;
            }
            uploadFile(file, $('#cmsMediaPickerPreset').val() || 'misc', function () {
                inputEl.value = '';
                loadPickerItems();
            });
        });

        $(document).on('click', '.cms-media-copy-path', function () {
            copyText($(this).attr('data-path') || '');
        });

        $(document).on('click', '.cms-media-delete-file', function () {
            var path = $(this).attr('data-path') || '';
            if (!path || !window.confirm('Delete this file from the Media Library?')) {
                return;
            }
            var cfg = mediaConfig();
            if (!cfg.deleteUrl) {
                showToast('Media library delete is not configured on this page.', true);
                return;
            }
            $.ajax({
                url: cfg.deleteUrl,
                type: 'POST',
                dataType: 'json',
                data: $.extend({ stored_path: path }, csrfPayload())
            }).done(function (res) {
                if (res && res.success) {
                    showToast(res.message || 'File deleted.');
                    window.location.reload();
                    return;
                }
                showToast((res && res.error) ? res.error : 'Delete failed.', true);
            }).fail(function (xhr) {
                showToast(ajaxError(xhr, 'Delete failed.'), true);
            });
        });
    });
})(jQuery);
