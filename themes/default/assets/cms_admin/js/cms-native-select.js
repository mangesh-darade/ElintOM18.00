/**
 * CMS admin: keep select.cms-native-select as native dropdowns.
 * core.js applies select2 to all selects globally; this runs after core.js and removes it
 * from CMS fields that use AJAX or dynamic options (entity tags, form builders, etc.).
 */
(function ($) {
    'use strict';

    function stripNativeSelect2(root) {
        var $scope = root ? $(root) : $(document);
        $scope.find('select.cms-native-select').addBack('select.cms-native-select').each(function () {
            var $sel = $(this);
            if ($sel.data('select2')) {
                $sel.select2('destroy');
            }
        });
    }

    window.CmsNativeSelect = {
        strip: stripNativeSelect2
    };

    $(document).ready(function () {
        // After all other document.ready handlers (including core.js select2 init).
        setTimeout(stripNativeSelect2, 0);
    });

    $(window).on('load', stripNativeSelect2);
})(jQuery);
