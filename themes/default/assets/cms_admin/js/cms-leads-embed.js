/**
 * CMS Admin — tune embedded Leads DataTable (column visibility, layout, tooltips).
 */
(function ($) {
    'use strict';

    if (!window.CMS_LEADS_EMBED) {
        return;
    }

    /** City, Product Sel 1, Business Name — often empty for webshop leads */
    var HIDE_COLS = [3, 4, 6];

    function getApi() {
        var $table = $('#LeadData');
        if (!$table.length || !$.fn.dataTable || !$.fn.dataTable.fnIsDataTable($table[0])) {
            return null;
        }
        return $table.dataTable();
    }

    function hideSparseColumns(api) {
        if (!api || api._cmsEmbedColsHidden) {
            return;
        }
        var i;
        for (i = 0; i < HIDE_COLS.length; i++) {
            api.fnSetColumnVis(HIDE_COLS[i], false, false);
        }
        api._cmsEmbedColsHidden = true;
    }

    function cellTitles() {
        $('#LeadData tbody tr').each(function () {
            $(this).find('td').each(function () {
                var $td = $(this);
                if ($td.find('select, .btn-group, input').length) {
                    return;
                }
                var text = $.trim($td.text());
                if (text !== '') {
                    $td.attr('title', text);
                }
            });
        });
    }

    function layoutPanel() {
        var $table = $('#LeadData');
        if (!$table.length) {
            return;
        }

        var $panel = $table.closest('.cms-leads-table-scroll');
        if (!$panel.length) {
            $table.closest('.table-responsive').addClass('cms-leads-table-scroll');
            $panel = $table.closest('.cms-leads-table-scroll');
        }

        $panel.css({
            maxWidth: '100%',
            overflowX: 'auto',
            overflowY: 'visible',
            WebkitOverflowScrolling: 'touch'
        });

        $table.css({
            width: '100%',
            minWidth: '860px',
            tableLayout: 'auto'
        });

        var $wrapper = $table.closest('.dataTables_wrapper');
        if ($wrapper.length) {
            $wrapper.css({ maxWidth: '100%', width: '100%' });
        }
    }

    function tune() {
        var api = getApi();
        if (!api) {
            return false;
        }
        hideSparseColumns(api);
        layoutPanel();
        cellTitles();
        return true;
    }

    $(function () {
        var attempts = 0;
        var timer = setInterval(function () {
            attempts++;
            if (tune() || attempts > 40) {
                clearInterval(timer);
            }
        }, 150);

        $(document).on('draw.dt', function () {
            tune();
        });
    });
})(jQuery);
