/**
 * CMS Admin loads core.js for iCheck/checkboxes but not every ERP plugin.
 * Stub optional jQuery plugins when the DOM node is absent or the script was not loaded.
 */
(function ($) {
    'use strict';

    if (!$.fn.calculator) {
        $.fn.calculator = function () {
            return this;
        };
    }
    if (!$.fn.redactor) {
        $.fn.redactor = function () {
            return this;
        };
    }
    if (!$.fn.fileinput) {
        $.fn.fileinput = function () {
            return this;
        };
    }
})(jQuery);
