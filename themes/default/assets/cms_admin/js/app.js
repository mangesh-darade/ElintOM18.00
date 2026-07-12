/**
 * CMS Admin Panel — shell UI (sidebar, flash messages).
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        setTimeout(function () {
            $('.cms-flash .alert').fadeOut('slow');
        }, 5000);

        function closeSidebar() {
            $('#cmsSidebar').removeClass('open');
            $('#cmsSidebarBackdrop').removeClass('show');
        }

        $('#cmsMenuToggle').on('click', function () {
            $('#cmsSidebar').toggleClass('open');
            $('#cmsSidebarBackdrop').toggleClass('show');
        });

        $('#cmsSidebarBackdrop').on('click', closeSidebar);

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        $(document).on('click', '.cms-nav-group-toggle', function () {
            var $group = $(this).closest('.cms-nav-group');
            var open = !$group.hasClass('is-open');
            $group.toggleClass('is-open', open);
            $(this).attr('aria-expanded', open ? 'true' : 'false');
        });

        var $navSearch = $('#cmsNavSearch');
        if ($navSearch.length) {
            $navSearch.on('input', function () {
                var q = $.trim($(this).val()).toLowerCase();
                $('.cms-nav-main > li').each(function () {
                    var $li = $(this);
                    var text = $li.text().toLowerCase();
                    var match = q === '' || text.indexOf(q) !== -1;
                    $li.toggle(match);
                    if (match && q !== '' && $li.hasClass('cms-nav-group')) {
                        $li.addClass('is-open');
                        $li.find('.cms-nav-group-toggle').attr('aria-expanded', 'true');
                    }
                });
            });
        }
    });
})(jQuery);
