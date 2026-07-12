/**
 * CMS page section HTML editor — raw code mode (bypasses Redactor), base64 POST, preview tab.
 */
(function ($) {
    'use strict';

    function utf8ToBase64(str) {
        try {
            return btoa(unescape(encodeURIComponent(str)));
        } catch (e) {
            return '';
        }
    }

    function syncPreview($editor) {
        var $ta = $editor.find('textarea.cms-html-code-textarea').first();
        var previewId = $editor.find('.cms-html-code-editor__preview').attr('id');
        var html = $ta.val() || '';
        var $preview = previewId ? $('#' + previewId) : $editor.find('.cms-html-code-editor__preview');
        $preview.html(html !== '' ? html : '<em class="text-muted">Nothing to preview.</em>');
    }

    function setEditorMode($editor, mode) {
        var isCode = mode !== 'preview';
        $editor.find('.cms-html-code-editor__tab').each(function () {
            var active = $(this).data('mode') === (isCode ? 'code' : 'preview');
            $(this).toggleClass('is-active', active).attr('aria-selected', active ? 'true' : 'false');
        });
        $editor.find('[data-pane="code"]').toggleClass('is-active', isCode).prop('hidden', !isCode);
        $editor.find('[data-pane="preview"]').toggleClass('is-active', !isCode).prop('hidden', isCode);
        if (!isCode) {
            syncPreview($editor);
        }
    }

    function initHtmlEditors() {
        $('[data-cms-html-editor]').each(function () {
            var $editor = $(this);
            if ($editor.data('cmsHtmlEditorInit') === 1) {
                return;
            }
            $editor.data('cmsHtmlEditorInit', 1);
            setEditorMode($editor, 'code');
        });
    }

    function attachB64ToForm($form) {
        var $ta = $form.find('textarea.cms-html-code-textarea:visible').first();
        if (!$ta.length) {
            $ta = $form.find('textarea.cms-html-code-textarea').first();
        }
        if (!$ta.length) {
            return;
        }

        var field = $ta.attr('name') || 'page_text';
        var b64Name = field + '_b64';
        var value = $ta.val() || '';

        $form.find('input[name="' + b64Name + '"]').remove();
        $('<input>', { type: 'hidden', name: b64Name, value: utf8ToBase64(value) }).appendTo($form);
        if (!$ta.attr('data-cms-original-name')) {
            $ta.attr('data-cms-original-name', field);
        }
        $ta.removeAttr('name');
    }

    function syncSectionChromeHtmlVisibility($form) {
        var sectionType = String($form.data('section-type') || '').toLowerCase();
        var $htmlRow = $form.find('.cms-section-html-field, [data-cms-html-editor]').closest('.cms-section-editor-row, .col-md-12');
        var useProfile = false;

        if (sectionType === 'header') {
            useProfile = String($form.find('.cms-section-header-mode').val() || '') === 'storefront_profile';
        } else if (sectionType === 'footer') {
            useProfile = String($form.find('.cms-section-footer-mode').val() || '') === 'storefront_profile';
        }

        if (sectionType === 'header' || sectionType === 'footer') {
            $htmlRow.toggle(!useProfile);
        }
    }

    function openSectionFromQuery() {
        var match = window.location.search.match(/(?:^|[?&])edit_section=(\d+)/);
        if (!match) {
            return;
        }
        var mappingId = match[1];
        var $modal = $('#editSectionModal_' + mappingId);
        if (!$modal.length) {
            return;
        }

        var $sectionsPanel = $('#cmsPeSectionList');
        if ($sectionsPanel.length && !$sectionsPanel.hasClass('is-expanded')) {
            $sectionsPanel.addClass('is-expanded');
            $sectionsPanel.find('.cms-pe-collapsible__toggle').first().attr('aria-expanded', 'true');
        }

        $modal.modal('show');
        setTimeout(function () {
            var $editor = $modal.find('[data-cms-html-editor]').first();
            if ($editor.length) {
                setEditorMode($editor, 'code');
                $editor.find('textarea.cms-html-code-textarea').first().trigger('focus');
            }
        }, 400);
    }

    $(function () {
        initHtmlEditors();

        $(document).on('click', '.cms-html-code-editor__tab', function (e) {
            e.preventDefault();
            var mode = $(this).data('mode');
            setEditorMode($(this).closest('[data-cms-html-editor]'), mode);
        });

        $(document).on('shown.bs.modal', '.cms-section-edit-modal', function () {
            initHtmlEditors();
            var $form = $(this).find('.cms-section-edit-form').first();
            syncSectionChromeHtmlVisibility($form);
            var $editor = $(this).find('[data-cms-html-editor]').first();
            if ($editor.length) {
                setEditorMode($editor, 'code');
            }
        });

        $(document).on('change', '.cms-section-header-mode, .cms-section-footer-mode', function () {
            syncSectionChromeHtmlVisibility($(this).closest('.cms-section-edit-form'));
        });

        $(document).on('submit', '#form_add_cms_section, .cms-section-edit-form', function () {
            var $form = $(this);
            attachB64ToForm($form);
        });

        openSectionFromQuery();
    });
})(jQuery);
