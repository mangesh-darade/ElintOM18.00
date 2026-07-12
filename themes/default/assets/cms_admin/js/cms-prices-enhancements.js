/**
 * CMS Admin — prices page (category selection, grid, ERP modal).
 */
(function ($) {
    'use strict';

    var filterUrl = '';

    function getFilterUrl() {
        if (filterUrl) {
            return filterUrl;
        }
        if (window.CmsAdmin && CmsAdmin.config && CmsAdmin.config.prices && CmsAdmin.config.prices.filterUrl) {
            filterUrl = CmsAdmin.config.prices.filterUrl;
        }
        return filterUrl;
    }

    function openErpProductModal(productId) {
        productId = parseInt(productId, 10);
        if (productId < 1 || !window.site || !site.base_url) {
            return;
        }
        var $modal = $('#myModal');
        if (!$modal.length) {
            return;
        }
        $modal.removeData('bs.modal');
        $modal.find('.modal-content').remove();
        $modal.modal({ remote: site.base_url + 'products/modal_view/' + productId });
        $modal.modal('show');
    }

    function setActiveCategorySelection(categoryId, subcategoryId, categoryName, subcategoryName) {
        categoryId = parseInt(categoryId, 10) || 0;
        subcategoryId = parseInt(subcategoryId, 10) || 0;

        $('#cmsPricesCategoryNav .price-nav-item').removeClass('is-selected');
        $('#cmsPricesCategoryNav .price-cat-item-wrap').removeClass('has-selected-sub');

        var $target;
        if (subcategoryId > 0) {
            $target = $('#cmsPricesCategoryNav .price-subcat-row[data-subcategory-id="' + subcategoryId + '"]');
            var $parentWrap = $('#cmsPricesCategoryNav .price-cat-item-wrap[data-category-id="' + categoryId + '"]');
            $parentWrap.addClass('has-selected-sub is-expanded');
        } else {
            $target = $('#cmsPricesCategoryNav .price-cat-row[data-category-id="' + categoryId + '"][data-subcategory-id="0"]');
        }
        if ($target.length) {
            $target.addClass('is-selected');
        }

        var $bar = $('#cmsPricesActiveSelection');
        var label;
        if (subcategoryId > 0 && subcategoryName) {
            label = categoryName + ' \u203a ' + subcategoryName;
            $bar.removeClass('is-empty').addClass('is-subcategory')
                .html('<i class="fa fa-tags"></i><span class="cms-prices-selection-text"><span class="cms-prices-selection-muted">' + escapeHtml(categoryName) + '</span> ' + escapeHtml(subcategoryName) + '</span>');
        } else if (categoryId > 0 && categoryName) {
            label = categoryName;
            $bar.removeClass('is-empty is-subcategory')
                .html('<i class="fa fa-folder-open-o"></i><span class="cms-prices-selection-text">' + escapeHtml(categoryName) + ' <span class="cms-prices-selection-tag">All products</span></span>');
        } else {
            $bar.addClass('is-empty').removeClass('is-subcategory')
                .html('<i class="fa fa-folder-open-o"></i><span class="cms-prices-selection-text">Select a category or subcategory on the left</span>');
        }
        return label;
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function loadPricesProducts(categoryId, subcategoryId, categoryName, subcategoryName) {
        categoryId = parseInt(categoryId, 10) || 0;
        subcategoryId = parseInt(subcategoryId, 10) || 0;
        var url = getFilterUrl();
        if (!url || categoryId < 1) {
            return;
        }

        var $search = $('#cmsPricesProductSearch');
        if ($search.length) {
            $search.val('');
        }

        setActiveCategorySelection(categoryId, subcategoryId, categoryName, subcategoryName);

        var $panel = $('#div_product_list');
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                action: 'manage_eshop_product',
                category_id: categoryId,
                subcategory_id: subcategoryId
            },
            beforeSend: function () {
                $panel.removeClass('is-loaded').addClass('is-loading')
                    .html('<div class="overlay-loader"><i class="fa fa-refresh fa-spin fa-2x"></i><span>Fetching products, please wait...</span></div>');
            },
            success: function (data) {
                $panel.removeClass('product-list-wrapper is-loading').addClass('is-loaded').html(data);
                initPricesGrid();
            },
            error: function () {
                $panel.removeClass('is-loading').addClass('product-list-wrapper')
                    .html('<div class="cms-prices-empty-state"><i class="fa fa-exclamation-circle"></i><h3>Could not load products</h3><p>Please try again.</p></div>');
            }
        });
    }

    function collapseCategoryPanels() {
        $('#cmsPricesCategoryNav .price-cat-item-wrap.has-subcategories').removeClass('is-expanded');
    }

    function bindCategoryNav() {
        var $nav = $('#cmsPricesCategoryNav');

        // Category click: always collapse other groups and expand this category's subcategories (if any),
        // then load ALL products for that category.
        $nav.on('click', '.price-nav-item--category', function (e) {
            if ($(e.target).closest('a').length) {
                return;
            }
            var $row = $(this);
            var $wrap = $row.closest('.price-cat-item-wrap');
            collapseCategoryPanels();
            if ($wrap.hasClass('has-subcategories')) {
                $wrap.addClass('is-expanded');
            }
            loadPricesProducts(
                $row.data('category-id'),
                0,
                $row.data('category-name'),
                ''
            );
        });

        // Subcategory click: expand its parent category and load only that subcategory's products.
        $nav.on('click', '.price-nav-item--sub', function (e) {
            if ($(e.target).closest('a').length) {
                return;
            }
            var $row = $(this);
            var $wrap = $row.closest('.price-cat-item-wrap');
            collapseCategoryPanels();
            $wrap.addClass('is-expanded');
            loadPricesProducts(
                $row.data('category-id'),
                $row.data('subcategory-id'),
                $row.data('category-name'),
                $row.data('subcategory-name')
            );
        });
        $nav.on('keydown', '.price-nav-item', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    }

    function bindPriceRowModal() {
        $(document).off('click.cmsPricesRow', '.cms-prices-product-table tbody tr.cms-price-product-row[data-product-id]');
        $(document).on('click.cmsPricesRow', '.cms-prices-product-table tbody tr.cms-price-product-row[data-product-id]', function (e) {
            if ($(e.target).closest('input, select, textarea, a, button, label, .cms-price-variants, .cms-price-variants-row, summary, .cms-price-variant-item, .cms-price-variant-row-full').length) {
                return;
            }
            var id = $(this).attr('data-product-id');
            if (id) {
                e.preventDefault();
                openErpProductModal(id);
            }
        });
    }

    function bindPriceImages($root) {
        if (window.CmsAdmin && typeof window.CmsAdmin.bindCatalogImageFallbacks === 'function') {
            window.CmsAdmin.bindCatalogImageFallbacks($root || $('#cmsPricesProductsTable'));
        }
    }

    function normalizeSearchValue(value) {
        return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }

    function getProductRowSearchText($row) {
        var cache = $row.data('cmsSearchText');
        if (cache) {
            return cache;
        }
        var $variantRow = $row.next('.cms-price-variants-row');
        var chunks = [];
        chunks.push($row.find('.cms-price-product-erp').text());
        chunks.push($row.find('input[name^="eshop_name["]').val());
        chunks.push($row.find('input[name^="eshop_price["]').val());
        chunks.push($variantRow.text());
        $variantRow.find('input').each(function () {
            chunks.push($(this).val());
        });
        cache = normalizeSearchValue(chunks.join(' '));
        $row.data('cmsSearchText', cache);
        return cache;
    }

    function clearProductRowSearchCache($scope) {
        var $root = $scope && $scope.length ? $scope : $('#cmsPricesProductsTable');
        $root.find('tr.cms-price-product-row').removeData('cmsSearchText');
    }

    function toggleSearchEmptyState(hasVisibleProducts) {
        var $tableWrap = $('.cms-prices-dt-panel');
        if (!$tableWrap.length) {
            return;
        }
        var $empty = $tableWrap.find('.cms-prices-search-empty');
        if (!hasVisibleProducts) {
            if (!$empty.length) {
                $tableWrap.append('<div class="cms-prices-search-empty">No matching products found.</div>');
            }
        } else if ($empty.length) {
            $empty.remove();
        }
    }

    function filterPricesTableRows(term) {
        var query = normalizeSearchValue(term);
        var visibleCount = 0;
        var $products = $('#cmsPricesProductsTable tbody tr.cms-price-product-row');
        if (!$products.length) {
            return;
        }
        $products.each(function () {
            var $row = $(this);
            var $variantRow = $row.next('.cms-price-variants-row');
            var haystack = getProductRowSearchText($row);
            var isMatch = !query || haystack.indexOf(query) !== -1;
            $row.toggle(isMatch);
            if ($variantRow.length) {
                $variantRow.toggle(isMatch);
            }
            if (isMatch) {
                visibleCount += 1;
            }
        });
        toggleSearchEmptyState(visibleCount > 0);
    }

    function initPricesGrid() {
        var $table = $('#cmsPricesProductsTable');
        var $searchBar = $('.cms-prices-search-bar');
        if (!$table.length) {
            if ($searchBar.length) {
                $searchBar.addClass('is-hidden');
            }
            return;
        }

        // Prices table includes expandable variant rows with colspan. DataTables expects a strict
        // one-row/one-column matrix and crashes (`_DT_CellIndex`) on this mixed structure.
        // Keep plain table rendering and use our custom search instead.
        if ($.fn.DataTable && $.fn.DataTable.isDataTable($table[0])) {
            $table.DataTable().destroy();
        }

        var $search = $('#cmsPricesProductSearch');
        if ($search.length) {
            if ($searchBar.length) {
                $searchBar.removeClass('is-hidden');
            }
            $search.off('.cmsPricesSearch').on('input.cmsPricesSearch', function () {
                clearProductRowSearchCache($table);
                filterPricesTableRows(this.value || '');
            });
        } else if ($searchBar.length) {
            $searchBar.addClass('is-hidden');
        }

        $(document)
            .off('input.cmsPricesSearchCache change.cmsPricesSearchCache', '#cmsPricesProductsTable input')
            .on('input.cmsPricesSearchCache change.cmsPricesSearchCache', '#cmsPricesProductsTable input', function () {
                var $productRow = $(this).closest('tr.cms-price-product-row');
                if ($productRow.length) {
                    $productRow.removeData('cmsSearchText');
                } else {
                    $(this).closest('tr.cms-price-variants-row').prev('.cms-price-product-row').removeData('cmsSearchText');
                }
                filterPricesTableRows($('#cmsPricesProductSearch').val() || '');
            });

        filterPricesTableRows($('#cmsPricesProductSearch').val() || '');

        bindPriceImages($table);
    }

    $(document).ready(function () {
        if ($('body').attr('data-cms-section') !== 'prices') {
            return;
        }
        bindCategoryNav();
        bindPriceRowModal();
        if (window.CmsAdmin && typeof window.CmsAdmin.bindCatalogImageFallbacks === 'function') {
            window.CmsAdmin.bindCatalogImageFallbacks($('#cmsPricesCategoryNav'));
        }
        $('#myModal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
            $(this).find('.modal-content').remove();
        });
    });

    window.load_product = function (categoryId, subcategoryId) {
        var $row = subcategoryId
            ? $('#cmsPricesCategoryNav .price-subcat-row[data-subcategory-id="' + subcategoryId + '"]')
            : $('#cmsPricesCategoryNav .price-cat-row[data-category-id="' + categoryId + '"][data-subcategory-id="0"]');
        loadPricesProducts(
            categoryId,
            subcategoryId,
            $row.data('category-name'),
            $row.data('subcategory-name')
        );
    };

    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.initPricesGrid = initPricesGrid;
    window.CmsAdmin.loadPricesProducts = loadPricesProducts;
    window.CmsAdmin.openErpProductModal = window.CmsAdmin.openErpProductModal || openErpProductModal;
})(jQuery);
