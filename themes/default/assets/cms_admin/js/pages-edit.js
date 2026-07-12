/**
 * CMS page edit — section active toggle via AJAX (uses .skip to avoid ERP core.js iCheck).
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

    function showToggleStatus(message, ok) {
        var $status = $('#cmsSortStatus');
        if (!$status.length) {
            return;
        }
        $status.text(ok ? '\u2713 ' + message : '\u2717 ' + message)
            .removeClass('ok err')
            .addClass(ok ? 'ok' : 'err')
            .show();
        if (ok) {
            setTimeout(function () {
                $status.fadeOut();
            }, 2500);
        }
    }

    function setToggleUi($wrap, isOn) {
        var $checkbox = $wrap.find('input.cms-section-enabled-toggle');
        var $label = $checkbox.closest('.cms-toggle-switch');
        var $statusLabel = $wrap.find('.cms-section-status-label');

        $checkbox.prop('checked', isOn);
        $label.toggleClass('is-on', isOn).toggleClass('is-off', !isOn);
        $statusLabel
            .toggleClass('is-active', isOn)
            .toggleClass('is-inactive', !isOn)
            .text(isOn ? 'Active' : 'Inactive');
        $wrap.closest('tr[data-mapping-id]').attr('data-section-enabled', isOn ? '1' : '0');
    }

    function ajaxSectionToggle($checkbox) {
        var $wrap = $checkbox.closest('.cms-section-toggle-wrap');
        var url = $wrap.data('toggle-url');
        var isOn = $checkbox.prop('checked');
        var previousOn = !isOn;

        if (!url || $wrap.data('toggle-busy') === 1) {
            $checkbox.prop('checked', previousOn);
            return;
        }

        setToggleUi($wrap, isOn);
        $wrap.data('toggle-busy', 1);
        $checkbox.prop('disabled', true);
        $wrap.addClass('is-saving');

        var payload = { toggle_active: isOn ? '1' : '0' };
        payload[csrfName] = getCsrfToken();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            syncCsrfHash(res && res.csrf_hash ? res.csrf_hash : null);

            if (res && res.status === 'success') {
                var enabled = res.is_enabled === 1 || res.is_enabled === '1' || res.is_enabled === true
                    || res.webshop_active === true;
                setToggleUi($wrap, enabled);
                showToggleStatus(res.message || (enabled ? 'Section active on webshop.' : 'Section hidden from webshop.'), true);
            } else {
                setToggleUi($wrap, previousOn);
                showToggleStatus((res && res.message) ? res.message : 'Failed to update section status.', false);
            }
        }).fail(function (xhr) {
            setToggleUi($wrap, previousOn);
            var errMsg = 'Save failed';
            try {
                var json = JSON.parse(xhr.responseText);
                if (json && json.message) {
                    errMsg = json.message;
                }
                if (json && json.csrf_hash) {
                    syncCsrfHash(json.csrf_hash);
                }
            } catch (e) { /* ignore */ }
            showToggleStatus(errMsg + ' (HTTP ' + xhr.status + ')', false);
        }).always(function () {
            $wrap.data('toggle-busy', 0);
            $checkbox.prop('disabled', false);
            $wrap.removeClass('is-saving');
        });
    }

    function setPeSectionExpanded($section, expanded) {
        if (!$section || !$section.length) {
            return;
        }
        var on = !!expanded;
        $section.toggleClass('is-expanded', on);
        $section.find('.cms-pe-collapsible__toggle').first().attr('aria-expanded', on ? 'true' : 'false');
    }

    function togglePeSection($section) {
        setPeSectionExpanded($section, !$section.hasClass('is-expanded'));
    }

    $(function () {
        $(document).on('click', '.cms-pe-collapsible__toggle', function (e) {
            e.preventDefault();
            togglePeSection($(this).closest('.cms-pe-collapsible'));
        });

        $(document).on('keydown', '.cms-pe-collapsible__toggle', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePeSection($(this).closest('.cms-pe-collapsible'));
            }
        });

        $(document).on('change', 'input.cms-section-enabled-toggle', function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            ajaxSectionToggle($(this));
        });

        $('#cmsSectionsSortableBody').on('mousedown click', '.cms-section-status-cell, .cms-section-toggle-wrap', function (e) {
            e.stopPropagation();
        });
    });
})(jQuery);
