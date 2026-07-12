/**
 * Entity tags — reload FAQ rows when entity selection changes.
 */
(function ($) {
    'use strict';

    var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};

    function syncCsrfHash(nextHash) {
        if (!nextHash) {
            return;
        }
        if (window.CmsAdmin && window.CmsAdmin.config) {
            window.CmsAdmin.config.csrfHash = nextHash;
        }
        var csrfName = cmsCfg.csrfName || 'token';
        $('input[name="' + csrfName + '"]').val(nextHash);
    }

    function updateFaqCountBadge(count) {
        var $badge = $('#cmsEntityFaqsCountBadge');
        if (!$badge.length) {
            return;
        }
        count = parseInt(count, 10) || 0;
        if (count > 0) {
            $badge.text(count + ' row' + (count === 1 ? '' : 's')).show();
        } else {
            $badge.hide().text('');
        }
    }

    function loadEntityFaqs() {
        var $inner = $('#cmsEntityFaqsPanelInner');
        if (!$inner.length) {
            return;
        }

        var entityMasterId = $.trim($('#entity_master_id').val() || '');
        var entityId = $.trim($('#entity_id').val() || '');
        if (entityMasterId === '' || entityId === '') {
            return;
        }

        $inner.addClass('is-loading');
        $.getJSON(
            cmsCfg.entityFaqsFormUrl || 'cms_admin/entity_tags/faqs_form_partial',
            { entity_master_id: entityMasterId, entity_id: entityId }
        ).done(function (res) {
            if (res && res.csrf_hash) {
                syncCsrfHash(res.csrf_hash);
            }
            if (res && res.status === 'success' && res.html) {
                $inner.html(res.html);
                updateFaqCountBadge(res.faq_count);
            }
        }).fail(function () {
            $inner.html('<div class="alert alert-danger">Could not load FAQs for this entity.</div>');
        }).always(function () {
            $inner.removeClass('is-loading');
        });
    }

    $(function () {
        var faqDebounce = null;

        function scheduleEntityFaqsReload() {
            clearTimeout(faqDebounce);
            faqDebounce = setTimeout(loadEntityFaqs, 200);
        }

        $('#entity_id').on('change.cmsEntityFaqs', function () {
            if ($.trim($('#entity_master_id').val() || '') !== '') {
                scheduleEntityFaqsReload();
            }
        });
    });
}(jQuery));
