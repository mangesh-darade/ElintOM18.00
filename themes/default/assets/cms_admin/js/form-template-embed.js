/**
 * CMS Admin — Form template embed link copy + index modal.
 */
(function ($) {
    'use strict';

    function copyFromPanel($panel, target) {
        var $field = $panel.find('[data-embed-copy="' + target + '"]').first();
        if (!$field.length) {
            return false;
        }
        var val = $field.val();
        if (!val) {
            return false;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(val);
            return true;
        }
        $field[0].focus();
        $field[0].select();
        try {
            return document.execCommand('copy');
        } catch (e) {
            return false;
        }
    }

    function flashCopied($btn) {
        var $btnEl = $btn;
        var original = $btnEl.html();
        $btnEl.prop('disabled', true).html('<i class="fa fa-check"></i> Copied');
        window.setTimeout(function () {
            $btnEl.prop('disabled', false).html(original);
        }, 1600);
    }

    function renderEmbedPanelHtml(links) {
        if (!links || !links.form_key) {
            return '<p class="text-muted">No embed links available.</p>';
        }
        var esc = function (s) {
            return String(s || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        };
        var warn = '';
        if (!links.is_active) {
            warn += '<div class="alert alert-warning cms-ft-embed-alert"><strong>Template is inactive.</strong> Activate before using on the live storefront.</div>';
        }
        return ''
            + '<div class="cms-ft-embed-panel cms-ft-embed-panel--compact" data-form-embed-panel="1">'
            + warn
            + '<div class="cms-ft-embed-row"><label class="cms-ft-embed-label">Direct link</label>'
            + '<div class="cms-ft-embed-copy"><input type="text" class="form-control cms-ft-embed-input" readonly value="' + esc(links.direct_url) + '" data-embed-copy="direct">'
            + '<button type="button" class="btn btn-default cms-ft-embed-copy-btn" data-copy-target="direct"><i class="fa fa-copy"></i> Copy</button>'
            + '<a href="' + esc(links.direct_url) + '" class="btn btn-default" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open</a></div></div>'
            + '<div class="cms-ft-embed-row"><label class="cms-ft-embed-label">Iframe embed</label>'
            + '<div class="cms-ft-embed-copy cms-ft-embed-copy--stack"><textarea class="form-control cms-ft-embed-textarea" rows="4" readonly data-embed-copy="iframe">' + esc(links.iframe_html) + '</textarea>'
            + '<button type="button" class="btn btn-default cms-ft-embed-copy-btn" data-copy-target="iframe"><i class="fa fa-copy"></i> Copy iframe</button></div></div>'
            + '<div class="cms-ft-embed-row"><label class="cms-ft-embed-label">CMS section key</label>'
            + '<div class="cms-ft-embed-copy"><input type="text" class="form-control cms-ft-embed-input" readonly value="' + esc(links.cms_section_key) + '" data-embed-copy="cms_key">'
            + '<button type="button" class="btn btn-default cms-ft-embed-copy-btn" data-copy-target="cms_key"><i class="fa fa-copy"></i> Copy key</button></div></div>'
            + '</div>';
    }

    $(document).on('click', '.cms-ft-embed-copy-btn', function () {
        var $btn = $(this);
        var target = $btn.attr('data-copy-target');
        var $panel = $btn.closest('[data-form-embed-panel]');
        if (copyFromPanel($panel, target)) {
            flashCopied($btn);
        }
    });

    $(document).on('click', '.cms-ft-embed-open', function (e) {
        e.preventDefault();
        var id = parseInt($(this).attr('data-template-id'), 10);
        var name = $(this).attr('data-template-name') || 'Form template';
        if (!id) {
            return;
        }
        var baseUrl = (window.site && site.base_url) ? String(site.base_url) : '';
        if (baseUrl !== '' && baseUrl.charAt(baseUrl.length - 1) !== '/') {
            baseUrl += '/';
        }
        var $modal = $('#cmsFtEmbedModal');
        if (!$modal.length) {
            $('body').append(
                '<div class="modal fade" id="cmsFtEmbedModal" tabindex="-1" role="dialog">'
                + '<div class="modal-dialog modal-lg" role="document"><div class="modal-content">'
                + '<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>'
                + '<h4 class="modal-title">Embed form</h4></div>'
                + '<div class="modal-body cms-ft-embed-modal-body"><p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Loading…</p></div>'
                + '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>'
                + '</div></div></div>'
            );
            $modal = $('#cmsFtEmbedModal');
        }
        $modal.find('.modal-title').text('Embed: ' + name);
        $modal.find('.cms-ft-embed-modal-body').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Loading…</p>');
        $modal.modal('show');
        $.getJSON(baseUrl + 'cms_admin/form_templates/ajax_embed_links/' + id)
            .done(function (res) {
                if (res && res.ok && res.links) {
                    $modal.find('.cms-ft-embed-modal-body').html(renderEmbedPanelHtml(res.links));
                } else {
                    $modal.find('.cms-ft-embed-modal-body').html('<p class="text-danger">' + (res && res.message ? res.message : 'Could not load embed links.') + '</p>');
                }
            })
            .fail(function () {
                $modal.find('.cms-ft-embed-modal-body').html('<p class="text-danger">Could not load embed links.</p>');
            });
    });
}(jQuery));
