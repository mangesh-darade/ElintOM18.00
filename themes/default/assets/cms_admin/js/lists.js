/**
 * CMS Admin — DataTables for list screens (Sr. No., hidden ID, name sort A–Z).
 */
(function ($) {
    'use strict';

    function srNoRender(data, type, row, meta) {
        if (type === 'display' || type === 'filter') {
            return meta.row + meta.settings._iDisplayStart + 1;
        }
        return data;
    }

    function buildColumnDefs(actionsIndex) {
        return [
            { targets: 0, visible: false, searchable: false },
            {
                targets: 1,
                orderable: false,
                searchable: false,
                className: 'cms-col-srno text-center',
                width: '56px',
                render: srNoRender
            },
            { targets: actionsIndex, orderable: false, searchable: false, className: 'cms-col-actions' }
        ];
    }

    var presets = {
        pages: {
            ordering: false,
            pageLength: 100,
            columnDefs: [
                { targets: 0, visible: false, searchable: false },
                { targets: 1, orderable: false, searchable: false, className: 'cms-col-srno text-center', width: '56px' },
                { targets: 2, orderable: false, searchable: false, className: 'cms-drag-cell text-center', width: '44px' },
                { targets: 8, orderable: false, searchable: false, className: 'cms-col-actions' }
            ]
        },
        storefront: {
            order: [[3, 'asc']],
            scrollX: false,
            columnDefs: buildColumnDefs(8)
        },
        entity_tags: {
            order: [[3, 'asc']],
            columnDefs: buildColumnDefs(6)
        },
        entity_faqs: {
            order: [[3, 'asc']],
            columnDefs: buildColumnDefs(6)
        },
        tags_master: {
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 4, orderable: false, searchable: false },
                { targets: 5, orderable: false, searchable: false },
                { targets: 6, orderable: false, searchable: false },
                { targets: 7, orderable: false, searchable: false, className: 'cms-col-actions' }
            ]
        },
        form_templates: {
            order: [[2, 'asc']],
            columnDefs: buildColumnDefs(9)
        },
        newsletter_subscribers: {
            order: [[6, 'desc']],
            columnDefs: [
                { targets: 0, visible: false, searchable: false },
                {
                    targets: 1,
                    orderable: false,
                    searchable: false,
                    className: 'cms-col-srno text-center',
                    width: '56px',
                    render: srNoRender
                }
            ]
        },
        blogs: {
            order: [[2, 'asc']],
            columnDefs: buildColumnDefs(8)
        },
        testimonials: {
            order: [[2, 'asc']],
            columnDefs: buildColumnDefs(7)
        }
    };

    var baseOptions = {
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        ordering: true,
        searching: true,
        info: true,
        scrollX: true,
        autoWidth: false,
        dom: '<"cms-dt-toolbar"lf>rt<"cms-dt-footer"ip>',
        language: {
            emptyTable: 'No records found.',
            zeroRecords: 'No matching records found.',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Prev'
            }
        }
    };

    function initTable($table) {
        if (!$table.length || !$.fn.DataTable) {
            return null;
        }
        if ($.fn.DataTable.isDataTable($table[0])) {
            return $table.DataTable();
        }

        var key = $table.data('cms-list') || 'pages';
        var preset = presets[key] || presets.pages;
        var opts = $.extend(true, {}, baseOptions, preset);

        return $table.DataTable(opts);
    }

    $(document).ready(function () {
        $('table.cms-datatable').each(function () {
            initTable($(this));
        });
    });

    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.initListTable = initTable;
})(jQuery);
