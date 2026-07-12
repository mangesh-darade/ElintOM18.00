/**
 * CMS Pages list — drag rows to set webshop nav order (auto-saves via reorder_nav).
 */
(function ($) {
    'use strict';

    var _sortSaveTimer = null;
    var _sortInFlight = false;
    var _sortPending = false;

    function getCfg() {
        return window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
    }

    function refreshSrNumbers() {
        $('#cmsPagesSortableBody tr[data-page-id]').each(function (index) {
            $(this).find('.cms-col-srno').text(index + 1);
        });
    }

    function collectNavOrdersFromRows() {
        var orders = {};
        $('#cmsPagesSortableBody tr[data-page-id]').each(function (index) {
            var pageId = parseInt($(this).data('page-id'), 10);
            if (pageId > 0) {
                orders[pageId] = index + 1;
            }
        });
        return orders;
    }

    function setSortStatus(message, isError) {
        var $status = $('#cmsNavSortStatus');
        $status.text(message || '').toggleClass('err', !!isError).toggleClass('ok', !isError && !!message).show();
    }

    function saveNavOrder() {
        var orders = collectNavOrdersFromRows();
        if (!Object.keys(orders).length) {
            return;
        }

        if (_sortInFlight) {
            _sortPending = true;
            return;
        }

        var cfg = getCfg();
        var payload = { orders: JSON.stringify(orders) };
        if (cfg.csrfName && cfg.csrfHash) {
            payload[cfg.csrfName] = cfg.csrfHash;
        }

        _sortInFlight = true;
        $('#cmsNavSortSaving').show();
        setSortStatus('', false);

        $.ajax({
            url: site.base_url + 'cms_admin/pages/reorder_nav',
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            if (res && res.csrfHash && cfg) {
                cfg.csrfHash = res.csrfHash;
            }
            if (res && res.status === 'success') {
                setSortStatus('Menu order saved.', false);
                return;
            }
            setSortStatus((res && res.message) ? res.message : 'Failed to save menu order.', true);
        }).fail(function () {
            setSortStatus('Failed to save menu order. Please try again.', true);
        }).always(function () {
            $('#cmsNavSortSaving').hide();
            _sortInFlight = false;
            if (_sortPending) {
                _sortPending = false;
                saveNavOrder();
            }
        });
    }

    function debouncedSaveNavOrder() {
        clearTimeout(_sortSaveTimer);
        _sortSaveTimer = setTimeout(saveNavOrder, 400);
    }

    function initPagesNavSortable() {
        var $tbody = $('#cmsPagesSortableBody');
        if (!$tbody.length || $tbody.find('tr[data-page-id]').length < 2) {
            return;
        }

        if ($.fn.sortable) {
            $tbody.sortable({
                axis: 'y',
                handle: '.cms-drag-handle',
                items: 'tr[data-page-id]',
                helper: function (e, $tr) {
                    var $originals = $tr.children();
                    var $helper = $tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                placeholder: 'cms-pages-sort-placeholder',
                forcePlaceholderSize: true,
                update: function () {
                    refreshSrNumbers();
                    debouncedSaveNavOrder();
                }
            });
            return;
        }

        var draggedRow = null;
        $tbody.find('tr[data-page-id]').attr('draggable', true);

        $tbody.on('dragstart', 'tr[data-page-id]', function (e) {
            draggedRow = this;
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });

        $tbody.on('dragover', 'tr[data-page-id]', function (e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('cms-drag-over');
        });

        $tbody.on('dragleave', 'tr[data-page-id]', function () {
            $(this).removeClass('cms-drag-over');
        });

        $tbody.on('drop', 'tr[data-page-id]', function (e) {
            e.preventDefault();
            $(this).removeClass('cms-drag-over');
            if (!draggedRow || draggedRow === this) {
                return;
            }
            if ($(this).index() < $(draggedRow).index()) {
                $(this).before(draggedRow);
            } else {
                $(this).after(draggedRow);
            }
            draggedRow = null;
            refreshSrNumbers();
            debouncedSaveNavOrder();
        });
    }

    function syncCsrfHash(nextHash) {
        if (!nextHash) {
            return;
        }
        var cfg = getCfg();
        cfg.csrfHash = nextHash;
        var csrfName = cfg.csrfName;
        if (csrfName) {
            $('input[name="' + csrfName + '"]').val(nextHash);
        }
    }

    function setNavToggleUi($wrap, isOn) {
        var $checkbox = $wrap.find('input.cms-page-nav-toggle');
        var $label = $checkbox.closest('.cms-toggle-switch');
        $checkbox.prop('checked', isOn);
        $label.toggleClass('is-on', isOn).toggleClass('is-off', !isOn);
    }

    function ajaxPageNavToggle($checkbox) {
        var $wrap = $checkbox.closest('.cms-page-nav-toggle-wrap');
        var url = $wrap.data('toggle-url');
        var field = $wrap.data('field');
        var isOn = $checkbox.prop('checked');
        var previousOn = !isOn;

        if (!url || !field || $wrap.data('toggle-busy') === 1) {
            $checkbox.prop('checked', previousOn);
            return;
        }

        setNavToggleUi($wrap, isOn);
        $wrap.data('toggle-busy', 1);
        $checkbox.prop('disabled', true);
        $wrap.addClass('is-saving');

        var cfg = getCfg();
        var payload = { field: field, toggle_active: isOn ? '1' : '0' };
        if (cfg.csrfName && cfg.csrfHash) {
            payload[cfg.csrfName] = cfg.csrfHash;
        }

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            if (res && res.csrfHash) {
                syncCsrfHash(res.csrfHash);
            }
            if (res && res.status === 'success') {
                setSortStatus(res.message || 'Visibility updated.', false);
                return;
            }
            setNavToggleUi($wrap, previousOn);
            setSortStatus((res && res.message) ? res.message : 'Failed to update visibility.', true);
        }).fail(function () {
            setNavToggleUi($wrap, previousOn);
            setSortStatus('Failed to update visibility. Please try again.', true);
        }).always(function () {
            $wrap.data('toggle-busy', 0);
            $checkbox.prop('disabled', false);
            $wrap.removeClass('is-saving');
        });
    }

    function initPageNavToggles() {
        $(document).on('change', '#cmsPagesTable input.cms-page-nav-toggle', function () {
            ajaxPageNavToggle($(this));
        });
    }

    $(document).ready(function () {
        var $table = $('#cmsPagesTable');
        if (window.CmsAdmin && typeof window.CmsAdmin.initListTable === 'function') {
            window.CmsAdmin.initListTable($table);
        }
        refreshSrNumbers();
        initPagesNavSortable();
        initPageNavToggles();
    });
})(jQuery);
