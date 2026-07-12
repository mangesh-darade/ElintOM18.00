/**
 * CMS Admin — expand/collapse panels with chevron toggle.
 */
(function ($) {
    'use strict';

    function setPanelState($panel, expanded) {
        $panel.toggleClass('is-expanded', expanded);
        $panel.find('> .cms-collapsible-header').attr('aria-expanded', expanded ? 'true' : 'false');
    }

    function togglePanel($panel) {
        setPanelState($panel, !$panel.hasClass('is-expanded'));
    }

    $(document).on('click', '.cms-collapsible-header', function (e) {
        e.preventDefault();
        togglePanel($(this).closest('.cms-collapsible-panel'));
    });

    window.CmsCollapsible = {
        expand: function (selector) {
            $(selector).each(function () {
                setPanelState($(this), true);
            });
        },
        collapse: function (selector) {
            $(selector).each(function () {
                setPanelState($(this), false);
            });
        }
    };
}(jQuery));
