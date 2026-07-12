/**
 * CMS Admin enhancements — initDataTableGrid for catalog/prices (additive).
 */
(function ($) {
    'use strict';

    function initDataTableGrid($table, options) {
        if (!$table || !$table.length || !$.fn.DataTable) {
            return null;
        }
        if ($.fn.DataTable.isDataTable($table[0])) {
            return $table.DataTable();
        }
        var defaults = {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            ordering: true,
            searching: true,
            info: true,
            autoWidth: false,
            scrollY: '320px',
            scrollCollapse: true,
            dom: '<"cms-dt-toolbar"lf>rt<"cms-dt-footer"ip>',
            language: {
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.',
                search: 'Filter:',
                lengthMenu: 'Show _MENU_'
            }
        };
        return $table.DataTable($.extend(true, {}, defaults, options || {}));
    }

    function syncCatalogVisibility() {
        if (window.CmsAdmin && typeof window.CmsAdmin.applyCatalogEshopVisibility === 'function') {
            window.CmsAdmin.applyCatalogEshopVisibility();
        }
    }

    $(document).ready(function () {
        var isCatalog = $('body').attr('data-cms-section') === 'catalog';
        if (isCatalog) {
            initDataTableGrid($('#cmsCatalogCategoriesTable'), {
                order: [[2, 'asc']],
                scrollY: '360px',
                scrollX: false,
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false, width: '44px' },
                    { targets: 1, orderable: false, searchable: false, width: '52px' },
                    { targets: 2, orderable: true },
                    { targets: 3, orderable: false, searchable: false, width: '44px' }
                ],
                drawCallback: function () {
                    if (window.CmsAdmin && typeof window.CmsAdmin.bindCatalogImageFallbacks === 'function') {
                        window.CmsAdmin.bindCatalogImageFallbacks($('#cmsCatalogCategoriesTable'));
                    }
                }
            });

            var $products = $('#cmsCatalogProductsTable');
            if ($products.length && $products.find('tbody tr[data-product-id]').length) {
                var productsDt = initDataTableGrid($products, {
                    order: [[3, 'asc']],
                    columnDefs: [
                        { targets: 0, orderable: false, searchable: false },
                        { targets: 1, orderable: false, searchable: false }
                    ],
                    drawCallback: function () {
                        syncCatalogVisibility();
                        if (window.CmsAdmin && typeof window.CmsAdmin.bindCatalogImageFallbacks === 'function') {
                            window.CmsAdmin.bindCatalogImageFallbacks($('#cmsCatalogProductsTable'));
                        }
                    }
                });
                window.CmsAdmin = window.CmsAdmin || {};
                window.CmsAdmin.catalogProductsTable = productsDt;
                syncCatalogVisibility();
                if (window.CmsAdmin.bindCatalogImageFallbacks) {
                    window.CmsAdmin.bindCatalogImageFallbacks();
                }
            }
        }
    });

    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.initDataTableGrid = initDataTableGrid;
})(jQuery);
