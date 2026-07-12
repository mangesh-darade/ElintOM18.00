/**
 * CMS page / entity tag edit — import head HTML/scripts into tag value fields.
 */
(function ($) {
    'use strict';

    var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    var csrfName = cmsCfg.csrfName || 'token';
    var csrfHash = cmsCfg.csrfHash || '';

    function getCsrfToken() {
        var tokenFromInput = $('input[name="' + csrfName + '"]').first().val();
        return tokenFromInput || csrfHash;
    }

    function syncCsrfHash(nextHash) {
        if (!nextHash) {
            return;
        }
        csrfHash = nextHash;
        if (window.CmsAdmin && window.CmsAdmin.config) {
            window.CmsAdmin.config.csrfHash = nextHash;
        }
        $('input[name="' + csrfName + '"]').val(nextHash);
    }

    function setStatus(message, ok) {
        var $status = $('#cms-head-tag-import-status');
        if (!$status.length) {
            return;
        }
        $status
            .text(message || '')
            .toggleClass('text-success', !!ok)
            .toggleClass('text-danger', !ok);
    }

    function setFieldValue($field, value) {
        if (!$field || !$field.length) {
            return;
        }

        $field.val(value);

        if ($field.data('redactor')) {
            try {
                $field.redactor('set', value);
            } catch (ignore) {}
        } else if (typeof $field.redactor === 'function') {
            try {
                $field.redactor('set', value);
            } catch (ignore2) {}
        }

        $field.trigger('change');
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

    function applyValuesToFields(applied) {
        if (!applied || typeof applied !== 'object') {
            return 0;
        }

        var count = 0;
        var $firstField = null;

        $.each(applied, function (tagId, value) {
            var $field = $('#tag_value_' + tagId);
            if ($field.length) {
                setFieldValue($field, value);
                if (!$firstField) {
                    $firstField = $field;
                }
                count++;
            }
        });

        if ($firstField) {
            activateTabForField($firstField);
            var $group = $firstField.closest('.form-group');
            $group.addClass('cms-tag-import-highlight');
            setTimeout(function () {
                $group.removeClass('cms-tag-import-highlight');
            }, 2500);
        }

        return count;
    }

    function hasMissingTagFields(applied) {
        if (!applied || typeof applied !== 'object') {
            return false;
        }
        var missing = false;
        $.each(applied, function (tagId) {
            if (!$('#tag_value_' + tagId).length) {
                missing = true;
                return false;
            }
        });
        return missing;
    }

    function encodeMarkupBase64(markup) {
        try {
            return btoa(unescape(encodeURIComponent(markup)));
        } catch (e) {
            return '';
        }
    }

    function reloadTagFormThenApply(applied, resMessage) {
        var appliedCopy = applied;
        var finish = function () {
            var filled = applyValuesToFields(appliedCopy);
            $(document).trigger('cms:headTagsImported', [appliedCopy || {}, resMessage || '']);
            if (filled === 0) {
                setStatus('Imported on server but no matching fields found on this form. Save and reload the page.', false);
                return;
            }
            setStatus(resMessage || (filled + ' tag field(s) updated.'), true);
        };

        var $pageWrap = $('#cmsPageTagFieldsWrap');
        if ($pageWrap.length && cmsCfg.pageTagsFormUrl) {
            var pageId = parseInt($('#cmsPageTagSection').data('page-id'), 10);
            if (!pageId) {
                window.location.reload();
                return;
            }
            $.getJSON(cmsCfg.pageTagsFormUrl + '/' + pageId, {
                page_type: $.trim(String($('#cmsPageTagSection').data('page-type') || 'static'))
            })
                .done(function (res) {
                    if (res && res.csrf_hash) {
                        syncCsrfHash(res.csrf_hash);
                    }
                    if (res && res.status === 'success' && res.html) {
                        $pageWrap.html(res.html);
                    }
                    finish();
                })
                .fail(function () {
                    window.location.reload();
                });
            return;
        }

        var $entityRoot = $('#cmsEntityTagFormRoot');
        if ($entityRoot.length && cmsCfg.entityTagsFormUrl) {
            var entityMasterId = $.trim($('#entity_master_id').val() || '');
            var entityId = $.trim($('#entity_id').val() || '');
            if (entityMasterId === '') {
                window.location.reload();
                return;
            }
            $.getJSON(cmsCfg.entityTagsFormUrl, {
                entity_master_id: entityMasterId,
                entity_id: entityId
            }).done(function (res) {
                if (res && res.csrf_hash) {
                    syncCsrfHash(res.csrf_hash);
                }
                if (res && res.status === 'success' && res.html) {
                    $entityRoot.html(res.html);
                }
                finish();
            }).fail(function () {
                window.location.reload();
            });
            return;
        }

        window.location.reload();
    }

    function importHeadTags() {
        var $panel = $('#cms-head-tag-import');
        var $textarea = $('#cms-head-tag-import-textarea');
        var $btn = $('#cms-head-tag-import-apply');
        var url = $panel.data('import-url');
        var markup = $.trim($textarea.val() || '');

        if (!url) {
            setStatus('Import URL is not configured.', false);
            return;
        }
        if (markup === '') {
            setStatus('Paste head HTML or scripts first.', false);
            $textarea.focus();
            return;
        }

        var payload = {
            head_markup_b64: encodeMarkupBase64(markup),
            head_markup: markup
        };

        if ($('#entity_master_id').length) {
            payload.entity_master_id = $('#entity_master_id').val();
            payload.entity_id = $('#entity_id').val();
            if (!payload.entity_master_id || !payload.entity_id) {
                setStatus('Select Type and Entity before importing head scripts.', false);
                return;
            }
        }

        payload[csrfName] = getCsrfToken();

        $btn.prop('disabled', true);
        setStatus('Parsing…', true);

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            if (res && res.csrf_hash) {
                syncCsrfHash(res.csrf_hash);
            }
            if (!res || res.status !== 'success') {
                setStatus((res && res.message) ? res.message : 'Import failed.', false);
                return;
            }

            if (res.reload || (res.created_tags && res.created_tags.length) || hasMissingTagFields(res.applied) || $('#cmsEntityTagFormRoot').length) {
                reloadTagFormThenApply(res.applied, res.message);
                return;
            }

            var filled = applyValuesToFields(res.applied);
            $(document).trigger('cms:headTagsImported', [res.applied || {}, res.message || '']);
            if (filled === 0) {
                setStatus('Imported on server but no matching fields found on this form. Save and reload the page.', false);
                return;
            }

            setStatus(res.message || (filled + ' tag field(s) updated.'), true);
        }).fail(function () {
            setStatus('Request failed. Please try again.', false);
        }).always(function () {
            $btn.prop('disabled', false);
        });
    }

    $(function () {
        $(document).on('click', '#cms-head-tag-import-apply', function (e) {
            e.preventDefault();
            importHeadTags();
        });
    });
}(jQuery));
