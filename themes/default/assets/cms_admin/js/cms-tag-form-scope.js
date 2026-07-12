/**
 * CMS tag forms — reload tag fields when entity type or CMS page type changes.
 */
(function ($) {
    'use strict';

    var cmsCfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    var csrfName = cmsCfg.csrfName || 'token';
    var csrfHash = cmsCfg.csrfHash || '';

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

    function loadEntityTagFields() {
        var $wrap = $('#cmsEntityTagFormRoot');
        if (!$wrap.length) {
            return;
        }

        var entityMasterId = $.trim($('#entity_master_id').val() || '');
        var entityId = $.trim($('#entity_id').val() || '');
        if (entityMasterId === '') {
            $wrap.html(
                '<div class="cms-collapsible-panel" id="cmsEntitySeoCollapsible">'
                + '<button type="button" class="cms-collapsible-header" aria-expanded="false" aria-controls="cmsEntitySeoCollapsibleBody">'
                + '<span class="cms-collapsible-chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>'
                + '<span class="cms-collapsible-header-text"><i class="fa fa-filter"></i> SEO &amp; Schema Fields</span>'
                + '</button>'
                + '<div class="cms-collapsible-body" id="cmsEntitySeoCollapsibleBody">'
                + '<div class="alert alert-info cms-tag-scope-empty" style="margin-bottom:0;"><i class="fa fa-hand-pointer-o"></i> '
                + 'Select <strong>Type</strong> above to load SEO fields for that entity.</div>'
                + '</div></div>'
            );
            return;
        }

        $wrap.addClass('is-loading');
        $.getJSON(
            (cmsCfg.entityTagsFormUrl || 'cms_admin/entity_tags/tags_form_partial'),
            { entity_master_id: entityMasterId, entity_id: entityId }
        ).done(function (res) {
            if (res && res.csrf_hash) {
                syncCsrfHash(res.csrf_hash);
            }
            if (res && res.status === 'success' && res.html) {
                $wrap.html(res.html);
                $(document).trigger('cms:entityTagFormReloaded');
            }
        }).fail(function () {
            $wrap.html('<div class="alert alert-danger">Could not load tag fields. Please refresh the page.</div>');
        }).always(function () {
            $wrap.removeClass('is-loading');
        });
    }

    $(function () {
        var entityDebounce = null;

        function scheduleEntityTagFieldsReload() {
            clearTimeout(entityDebounce);
            entityDebounce = setTimeout(loadEntityTagFields, 200);
        }

        $('#entity_master_id').on('change.cmsTagScope', scheduleEntityTagFieldsReload);

        $('#entity_id').on('change.cmsTagScope', function () {
            if ($('#cmsEntityTagFormRoot').length && $.trim($('#entity_master_id').val() || '') !== '') {
                scheduleEntityTagFieldsReload();
            }
        });
    });
}(jQuery));
