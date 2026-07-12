/**
 * Tag Master — auto-save scope and visibility toggles.
 */
(function ($) {
    'use strict';

    var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    var csrfName = cmsCfg.csrfName || 'token';
    var csrfHash = cmsCfg.csrfHash || '';
    var saveUrl = cmsCfg.tagsMasterSaveUrl || '';

    function getCsrfToken() {
        return $('input[name="' + csrfName + '"]').first().val() || csrfHash;
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
        var $s = $('#cmsTagMasterSaveStatus');
        if (!$s.length) {
            return;
        }
        $s.text(message || '')
            .toggleClass('is-ok', !!ok)
            .toggleClass('is-err', !ok);
        if (ok) {
            setTimeout(function () {
                $s.text('').removeClass('is-ok is-err');
            }, 2200);
        }
    }

    function saveTagField(tagId, data) {
        if (!saveUrl || !tagId) {
            return;
        }
        data.id = tagId;
        data[csrfName] = getCsrfToken();

        $.ajax({
            url: saveUrl,
            type: 'POST',
            dataType: 'json',
            data: data
        }).done(function (res) {
            if (res && res.csrf_hash) {
                syncCsrfHash(res.csrf_hash);
            }
            if (res && res.status === 'success') {
                setStatus('Saved', true);
            } else {
                setStatus((res && res.message) ? res.message : 'Save failed', false);
            }
        }).fail(function () {
            setStatus('Save failed', false);
        });
    }

    $(function () {
        if (!$('.cms-tag-master-table').length) {
            return;
        }

        $(document).on('change', '.cms-tag-master-scope', function () {
            saveTagField($(this).data('tag-id'), { page_type: $(this).val() });
        });

        $(document).on('change', '.cms-tag-master-show-page', function () {
            saveTagField($(this).data('tag-id'), { show_on_page: $(this).is(':checked') ? 1 : 0 });
        });

        $(document).on('change', '.cms-tag-master-show-entity', function () {
            saveTagField($(this).data('tag-id'), { show_on_page: undefined, show_on_entity: $(this).is(':checked') ? 1 : 0 });
        });
    });
}(jQuery));
