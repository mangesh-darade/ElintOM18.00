/**
 * Storefront layout builder — live preview without save.
 * Toggles use hidden 0/1 fields + button switches (no native checkbox).
 */
(function ($) {
    'use strict';

    var $root = $('#cmsLayoutBuilder');
    if (!$root.length) {
        return;
    }

    var previewUrl = $root.data('preview-url') || '';
    var tab = $root.data('tab') || 'header';
    var profileSlug = $root.data('profile') || 'site';
    var headerProfileSlug = $root.data('header-profile') || profileSlug;
    var footerProfileSlug = $root.data('footer-profile') || profileSlug;
    var refreshTimer = null;
    var tabBase = $root.data('page-url') || '';
    var cmsFooterPages = [];

    try {
        cmsFooterPages = JSON.parse($root.attr('data-cms-footer-pages') || '[]');
        if (!Array.isArray(cmsFooterPages)) {
            cmsFooterPages = [];
        }
    } catch (ignoreCmsPages) {
        cmsFooterPages = [];
    }

    function isFlagOn(name) {
        var $h = $('input.cms-lb-flag[name="' + name + '"]');
        return $h.length && String($h.val()) === '1';
    }

    function setFlagOn(name, on) {
        on = !!on;
        $('input.cms-lb-flag[name="' + name + '"]').val(on ? '1' : '0');
        $('button.cms-lb-header-el__switch[data-flag="' + name + '"]')
            .toggleClass('is-on', on)
            .attr('aria-checked', on ? 'true' : 'false');
    }

    function collectElementColors() {
        var colors = {};
        var $scope = tab === 'footer' ? $('#cmsFooterBuilderForm') : $('#cmsHeaderBuilderForm');
        if (!$scope.length) {
            $scope = $(document);
        }
        $scope.find('input[data-el-color="1"]').each(function () {
            var key = $(this).attr('name') || $(this).data('preview-key');
            var val = $(this).val() || '';
            if (key && val !== '') {
                colors[key] = val;
            }
        });
        return colors;
    }

    function syncStylesEnabledFlags($scope) {
        $scope = $scope && $scope.length ? $scope : $(document);
        $scope.find('.cms-lb-styles-toggle').each(function () {
            var checked = $(this).prop('checked');
            var ek = $(this).data('style-for');
            if (!ek) {
                return;
            }
            var $hidden = $scope.find('input.cms-lb-styles-enabled-flag[data-style-for="' + ek + '"]');
            if ($hidden.length) {
                $hidden.val(checked ? '1' : '0');
            }
        });
    }

    function collectElementStyles() {
        var styles = {};
        var $scope = tab === 'footer' ? $('#cmsFooterBuilderForm') : $('#cmsHeaderBuilderForm');
        if (!$scope.length) {
            $scope = $(document);
        }
        $scope.find('.cms-lb-element-pos-row--styles').each(function () {
            var $row = $(this);
            var $toggle = $row.find('.cms-lb-styles-toggle').first();
            if (!$toggle.length || !$toggle.prop('checked')) {
                return;
            }
            $row.find('.cms-lb-element-styles-body').find('input, select').each(function () {
                var key = $(this).attr('name');
                var val = $(this).val() || '';
                if (key && val !== '') {
                    styles[key] = val;
                }
            });
        });
        return styles;
    }

    function syncElementStylesPanels($scope) {
        $scope = $scope && $scope.length ? $scope : $(document);
        $scope.find('.cms-lb-element-pos-row--styles').each(function () {
            var $row = $(this);
            var $toggle = $row.find('.cms-lb-styles-toggle').first();
            var $body = $row.find('.cms-lb-element-styles-body').first();
            if (!$toggle.length || !$body.length) {
                return;
            }
            $body.toggleClass('is-expanded', $toggle.prop('checked'));
        });
    }

    function collectHeaderConfig() {
        return $.extend({
            show_logo: isFlagOn('show_logo'),
            show_cart: isFlagOn('show_cart'),
            show_wishlist: isFlagOn('show_wishlist'),
            show_account: isFlagOn('show_account'),
            show_search: isFlagOn('show_search'),
            show_nav: isFlagOn('show_nav'),
            nav_active_underline: isFlagOn('nav_active_underline'),
            show_phone: isFlagOn('show_phone'),
            show_promo: isFlagOn('show_promo'),
            show_button: isFlagOn('show_button'),
            logo_image: $('#logoImagePath').val() || '',
            phone: $('input[name="phone"]').val() || '',
            promo_text: $('textarea[name="promo_text"]').val() || '',
            button_text: $('textarea[name="button_text"]').val() || '',
            button_link: $('input[name="button_link"]').val() || '',
            bg_color: $('input[name="bg_color"]').val() || '#0f172a',
            text_color: $('input[name="text_color"]').val() || '#ffffff',
            accent_color: $('input[name="accent_color"]').val() || '#3b82f6',
            icon_bg: $('input[name="icon_bg"]').val() || 'transparent',
            promo_align: $('input[name="promo_align"]:checked').val() || 'center',
            logo_align: $('input[name="logo_align"]:checked').val() || 'left',
            nav_align: $('input[name="nav_align"]:checked').val() || 'center',
            phone_align: $('input[name="phone_align"]:checked').val() || 'right',
            search_align: $('input[name="search_align"]:checked').val() || 'right',
            wishlist_align: $('input[name="wishlist_align"]:checked').val() || 'right',
            cart_align: $('input[name="cart_align"]:checked').val() || 'right',
            account_align: $('input[name="account_align"]:checked').val() || 'right',
            button_align: $('input[name="button_align"]:checked').val() || 'right'
        }, collectElementColors(), collectElementStyles());
    }

    function setElementAlign(name, value) {
        var $input = $('input[name="' + name + '"][value="' + value + '"]');
        if (!$input.length) {
            return;
        }
        $input.prop('checked', true);
        var $segment = $input.closest('.cms-lb-segment');
        if ($segment.length) {
            $segment.find('.cms-lb-segment__opt').removeClass('is-active');
            $input.closest('.cms-lb-segment__opt').addClass('is-active');
            return;
        }
        var $group = $input.closest('.cms-lb-align');
        if ($group.length) {
            $group.find('.cms-lb-align__opt').removeClass('is-active');
            $input.closest('.cms-lb-align__opt').addClass('is-active');
        }
    }

    function matchCmsFooterPageByTitle(title) {
        title = String(title || '').trim().toLowerCase();
        if (!title) {
            return null;
        }
        for (var i = 0; i < cmsFooterPages.length; i++) {
            var p = cmsFooterPages[i];
            var label = String(p.label || '').trim().toLowerCase();
            if (label !== '' && label === title) {
                return p;
            }
        }
        return null;
    }

    function autofillSectionLinksFromTitle(title, links) {
        if (links && links.length) {
            return links;
        }
        var match = matchCmsFooterPageByTitle(title);
        if (!match) {
            return links;
        }
        return [{
            label: String(match.label || title),
            url: String(match.path || match.url || ''),
            page_id: String(match.id || '')
        }];
    }

    function resolveFooterPageSelectForTitle(title) {
        var match = matchCmsFooterPageByTitle(title);
        return match ? String(match.id || '') : '';
    }

    function readFooterLinkRow($row) {
        var $sel = $row.find('.cms-lb-ft-link-page-select');
        var val = $sel.val() || '';
        var label = '';
        var url = '';
        var pageId = '';
        if (val && val !== '__other__') {
            var $opt = $sel.find('option:selected');
            label = String($opt.attr('data-label') || $opt.text() || '').replace(/\s*★\s*$/, '').trim();
            url = String($opt.attr('data-path') || $opt.attr('data-url') || '').trim();
            pageId = val;
        } else if (val === '__other__') {
            label = $row.find('.cms-lb-ft-link-label').val() || '';
            url = $row.find('.cms-lb-ft-link-url').val() || '';
        }
        return { label: label, url: url, page_id: pageId };
    }

    function syncFooterLinkRowUi($row) {
        var $sel = $row.find('.cms-lb-ft-link-page-select');
        var val = $sel.val() || '';
        var isOther = (val === '__other__');
        var $resolved = $row.next('.cms-lb-ft-link-resolved');
        $row.toggleClass('is-other', isOther);
        if (val && val !== '__other__') {
            var $opt = $sel.find('option:selected');
            var optLabel = String($opt.attr('data-label') || $opt.text() || '').replace(/\s*★\s*$/, '').trim();
            var optPath = String($opt.attr('data-path') || '').trim();
            var optPublic = String($opt.attr('data-url') || '').trim();
            $row.find('.cms-lb-ft-link-label').val(optLabel);
            $row.find('.cms-lb-ft-link-url').val(optPath || optPublic);
            $row.find('.cms-lb-ft-link-page-id').val(val);
            if ($resolved.length) {
                $resolved.show().find('.cms-lb-ft-link-resolved-url').text(optPublic || optPath);
            }
        } else if (val === '__other__') {
            $row.find('.cms-lb-ft-link-page-id').val('');
            if ($resolved.length) {
                $resolved.hide();
            }
        } else {
            $row.find('.cms-lb-ft-link-label, .cms-lb-ft-link-url, .cms-lb-ft-link-page-id').val('');
            if ($resolved.length) {
                $resolved.hide();
            }
        }
    }

    function syncAllFooterLinkRows() {
        $('#cmsFooterSections .cms-lb-ft-link-row').each(function () {
            syncFooterLinkRowUi($(this));
        });
        reindexFooterSections();
    }

    function buildFooterLinkPageSelectOptions(selected) {
        selected = selected || '';
        var html = '<option value="">— Select page —</option>';
        cmsFooterPages.forEach(function (p) {
            var id = String(p.id || '');
            if (!id) {
                return;
            }
            var label = String(p.label || ('Page ' + id));
            var path = String(p.path || p.url || '');
            var url = String(p.url || path);
            var headerMark = p.in_header ? ' ★' : '';
            var sel = (selected === id) ? ' selected' : '';
            html += '<option value="' + id + '" data-label="' + $('<div>').text(label).html() + '" data-path="'
                + $('<div>').text(path).html() + '" data-url="'
                + $('<div>').text(url).html() + '"' + sel + '>' + $('<div>').text(label + headerMark).html() + '</option>';
        });
        html += '<option value="__other__"' + (selected === '__other__' ? ' selected' : '') + '>Other (custom link)</option>';
        return html;
    }

    function collectFooterSections() {
        var sections = [];
        $('#cmsFooterSections .cms-lb-ft-section').each(function (si) {
            var $sec = $(this);
            var id = $.trim($sec.find('.cms-lb-ft-section-id').val() || '') || ('sec_' + Date.now() + '_' + si);
            var title = $sec.find('.cms-lb-ft-section-title').val() || '';
            var skey = sectionStorageKey(id);
            var links = [];
            $sec.find('.cms-lb-ft-link-row').each(function () {
                var link = readFooterLinkRow($(this));
                if (link.label || link.url || link.page_id) {
                    var row = { label: link.label, url: link.url };
                    if (link.page_id) {
                        row.page_id = link.page_id;
                    }
                    links.push(row);
                }
            });
            links = autofillSectionLinksFromTitle(title, links);
            var showKey = 'show_' + skey;
            var showOn = $('input[name="' + showKey + '"]').length ? isFlagOn(showKey) : true;
            sections.push({
                id: id,
                title: title,
                show: showOn,
                title_align: $('input[name="' + skey + '_title_align"]:checked').val() || 'left',
                links_align: $('input[name="' + skey + '_align"]:checked').val() || 'left',
                links: links
            });
        });
        return sections;
    }

    function reindexFooterSections() {
        $('#cmsFooterSections .cms-lb-ft-section').each(function (si) {
            var $sec = $(this);
            $sec.attr('data-section-index', si);
            $sec.find('.cms-lb-ft-section-id').attr('name', 'footer_sections[' + si + '][id]');
            $sec.find('.cms-lb-ft-section-title').attr('name', 'footer_sections[' + si + '][title]');
            $sec.find('.cms-lb-ft-link-row').each(function (li) {
                var $row = $(this);
                $row.find('.cms-lb-ft-link-page-select').attr('name', 'footer_sections[' + si + '][links][' + li + '][cms_page_key]');
                $row.find('.cms-lb-ft-link-label').attr('name', 'footer_sections[' + si + '][links][' + li + '][label]');
                $row.find('.cms-lb-ft-link-url').attr('name', 'footer_sections[' + si + '][links][' + li + '][url]');
                $row.find('.cms-lb-ft-link-page-id').attr('name', 'footer_sections[' + si + '][links][' + li + '][page_id]');
            });
        });
    }

    function buildFooterLinkRowHtml(sectionIndex, linkIndex, selected) {
        return ''
            + '<div class="cms-lb-ft-link-row">'
            + '<select name="footer_sections[' + sectionIndex + '][links][' + linkIndex + '][cms_page_key]" class="form-control input-sm cms-lb-ft-link-page-select cms-lb-ft-section-input" title="Select CMS page or Other">'
            + buildFooterLinkPageSelectOptions(selected || '')
            + '</select>'
            + '<input type="hidden" name="footer_sections[' + sectionIndex + '][links][' + linkIndex + '][page_id]" class="cms-lb-ft-link-page-id" value="">'
            + '<div class="cms-lb-ft-link-custom">'
            + '<input type="text" name="footer_sections[' + sectionIndex + '][links][' + linkIndex + '][label]" class="form-control input-sm cms-lb-ft-section-input cms-lb-ft-link-label" placeholder="Link label" value="">'
            + '<input type="text" name="footer_sections[' + sectionIndex + '][links][' + linkIndex + '][url]" class="form-control input-sm cms-lb-ft-section-input cms-lb-ft-link-url" placeholder="/page or https://..." value="">'
            + '</div>'
            + '<button type="button" class="btn btn-default btn-xs cms-lb-ft-remove-link" title="Remove link"><i class="fa fa-times"></i></button>'
            + '</div>'
            + '<p class="cms-lb-ft-link-resolved cms-hd-field-help" style="display:none;">Opens: <code class="cms-lb-ft-link-resolved-url"></code> <span class="text-muted">(same as header nav for this page)</span></p>';
    }

    function buildFooterSectionHtml(index, id, title) {
        id = id || ('sec_' + Date.now());
        title = title || '';
        return ''
            + '<div class="cms-lb-ft-section" data-section-index="' + index + '">'
            + '<div class="cms-lb-ft-section__head">'
            + '<span class="cms-lb-ft-section__drag"><i class="fa fa-bars"></i></span>'
            + '<input type="hidden" name="footer_sections[' + index + '][id]" class="cms-lb-ft-section-id" value="' + id + '">'
            + '<input type="text" name="footer_sections[' + index + '][title]" class="form-control input-sm cms-lb-ft-section-title cms-lb-ft-section-input" placeholder="Section name (e.g. Solutions)" value="' + $('<div>').text(title).html() + '">'
            + '<button type="button" class="btn btn-danger btn-xs cms-lb-ft-remove-section" title="Remove section"><i class="fa fa-trash-o"></i></button>'
            + '</div>'
            + '<div class="cms-lb-ft-section__links">'
            + buildFooterLinkRowHtml(index, 0, resolveFooterPageSelectForTitle(title))
            + '</div>'
            + '<button type="button" class="btn btn-default btn-xs cms-lb-ft-add-link"><i class="fa fa-plus"></i> Add link</button>'
            + '</div>';
    }

    function sectionStorageKey(id) {
        return 'section_' + String(id || '').replace(/[^a-zA-Z0-9_-]/g, '');
    }

    function buildAlignMini(name, current, cssClass) {
        cssClass = cssClass || 'cms-lb-ft-live';
        current = current || 'left';
        var opts = [
            ['left', 'fa-align-left', 'Left'],
            ['center', 'fa-align-center', 'Center'],
            ['right', 'fa-align-right', 'Right']
        ];
        var html = '<div class="cms-lb-segment" role="group" aria-label="Alignment" data-align-name="' + name + '">';
        opts.forEach(function (o) {
            var val = o[0];
            var icon = o[1];
            var label = o[2];
            var id = name + '_' + val;
            var checked = current === val ? ' checked' : '';
            var active = current === val ? ' is-active' : '';
            html += '<label class="cms-lb-segment__opt' + active + '" for="' + id + '" title="' + label + '">'
                + '<input type="radio" id="' + id + '" name="' + name + '" value="' + val + '" class="cms-lb-align-input ' + cssClass + '"' + checked + '>'
                + '<i class="fa ' + icon + '"></i>'
                + '<span class="cms-lb-segment__text">' + label + '</span></label>';
        });
        return html + '</div>';
    }

    function buildFooterElementStylesRow(skey) {
        var fields = [
            ['font_size', 'Font size'],
            ['width', 'Width'],
            ['height', 'Height'],
            ['padding_top', 'Pad Top'],
            ['padding_bottom', 'Pad Bot']
        ];
        var html = '<div class="cms-lb-element-pos-row cms-lb-element-pos-row--styles is-visible">'
            + '<input type="hidden" name="' + skey + '_styles_enabled" value="0" class="cms-lb-styles-enabled-flag" data-style-for="' + skey + '">'
            + '<div class="cms-lb-element-styles-head">'
            + '<label class="cms-lb-element-styles-toggle">'
            + '<input type="checkbox" class="cms-lb-styles-toggle" data-style-for="' + skey + '">'
            + '<span class="cms-lb-element-pos-row__label"><i class="fa fa-paint-brush"></i> Styles</span>'
            + '</label>'
            + '</div>'
            + '<div class="cms-lb-element-styles-body">'
            + '<div class="cms-lb-element-color-row__pickers">';
        fields.forEach(function (pair) {
            var prop = pair[0];
            var label = pair[1];
            var key = skey + '_' + prop;
            html += '<label class="cms-lb-element-color">' + label
                + ' <input type="text" name="' + key + '" value="" class="form-control input-sm cms-lb-ft-live" placeholder="auto" data-preview-key="' + key + '" style="width:60px;display:inline-block;margin-left:5px;height:24px;padding:2px 5px;font-size:11px;"></label>';
        });
        var posKey = skey + '_position';
        html += '<label class="cms-lb-element-color">Position <select name="' + posKey + '" class="form-control input-sm cms-lb-ft-live" data-preview-key="' + posKey + '" style="width:70px;display:inline-block;margin-left:5px;height:24px;padding:2px;font-size:11px;">'
            + '<option value="">Static</option><option value="relative">Relative</option><option value="absolute">Absolute</option></select></label>';
        ['top', 'bottom', 'left', 'right'].forEach(function (prop, idx) {
            var labels = ['Top', 'Bot', 'Left', 'Right'];
            var key = skey + '_' + prop;
            html += '<label class="cms-lb-element-color">' + labels[idx]
                + ' <input type="text" name="' + key + '" value="" class="form-control input-sm cms-lb-ft-live" placeholder="auto" data-preview-key="' + key + '" style="width:50px;display:inline-block;margin-left:5px;height:24px;padding:2px 5px;font-size:11px;"></label>';
        });
        return html + '</div></div></div>';
    }

    function buildFooterSectionElementRow(skey, title) {
        title = title || 'Footer section';
        var showKey = 'show_' + skey;
        var alignKey = skey + '_align';
        var titleAlignKey = skey + '_title_align';
        var safeTitle = $('<div>').text(title).html();
        return ''
            + '<div class="cms-lb-header-el is-active" data-show-field="' + showKey + '" data-element-key="' + skey + '" data-footer-section="1">'
            + '<div class="cms-lb-header-el__head">'
            + '<span class="cms-lb-header-el__drag cms-drag-handle" title="Drag to reorder"><i class="fa fa-bars"></i></span>'
            + '<input type="hidden" name="' + showKey + '" value="1" class="cms-lb-flag" data-flag="' + showKey + '">'
            + '<button type="button" class="cms-lb-header-el__switch is-on" role="switch" aria-checked="true" data-flag="' + showKey + '" aria-label="' + safeTitle + '">'
            + '<span class="cms-lb-header-el__track" aria-hidden="true"></span></button>'
            + '<span class="cms-lb-header-el__meta" data-flag="' + showKey + '"><i class="fa fa-list"></i> '
            + '<span class="cms-lb-header-el__name">' + safeTitle + '</span></span>'
            + '</div>'
            + '<div class="cms-lb-element-pos-row is-visible" data-pos-for="' + showKey + '">'
            + '<span class="cms-lb-element-pos-row__label"><i class="fa fa-font"></i> Section title align</span>'
            + buildAlignMini(titleAlignKey, 'left', 'cms-lb-ft-live')
            + '</div>'
            + '<div class="cms-lb-element-pos-row is-visible" data-pos-for="' + showKey + '">'
            + '<span class="cms-lb-element-pos-row__label"><i class="fa fa-link"></i> Links align</span>'
            + buildAlignMini(alignKey, 'left', 'cms-lb-ft-live')
            + '</div>'
            + buildFooterElementStylesRow(skey)
            + '</div>';
    }

    function upsertFooterSectionElement(sectionId, title) {
        var skey = sectionStorageKey(sectionId);
        if (!skey || skey === 'section_') {
            return;
        }
        var $existing = $('#cmsFooterElementsSortable .cms-lb-header-el[data-element-key="' + skey + '"]');
        if ($existing.length) {
            $existing.find('.cms-lb-header-el__name').text(title || 'Footer section');
            return;
        }
        $('#cmsFooterElementsSortable').append(buildFooterSectionElementRow(skey, title || 'Footer section'));
        collectFooterElementOrder();
        initFooterSortables();
    }

    function removeFooterSectionElement(sectionId) {
        var skey = sectionStorageKey(sectionId);
        $('#cmsFooterElementsSortable .cms-lb-header-el[data-element-key="' + skey + '"]').remove();
        $('input.cms-lb-ft-section-show-fallback[name="show_' + skey + '"]').remove();
        collectFooterElementOrder();
    }

    function ensureFooterSectionShowFlags() {
        $('.cms-lb-ft-section-show-fallback').remove();
        $('#cmsFooterSections .cms-lb-ft-section').each(function () {
            var id = $.trim($(this).find('.cms-lb-ft-section-id').val() || '');
            if (!id) {
                return;
            }
            var showKey = 'show_' + sectionStorageKey(id);
            if (!$('input.cms-lb-flag[name="' + showKey + '"]').length) {
                $('#cmsFooterBuilderForm').append(
                    '<input type="hidden" name="' + showKey + '" value="1" class="cms-lb-flag cms-lb-ft-section-show-fallback">'
                );
            }
        });
    }

    function collectFooterElementOrder() {
        var order = [];
        var seen = {};
        $('#cmsFooterElementsSortable .cms-lb-header-el').each(function () {
            var key = $(this).data('element-key');
            if (key && !seen[key]) {
                order.push(String(key));
                seen[key] = true;
            }
        });
        $('#cmsFooterSections .cms-lb-ft-section').each(function () {
            var id = $.trim($(this).find('.cms-lb-ft-section-id').val() || '');
            if (!id) {
                return;
            }
            var skey = sectionStorageKey(id);
            if (!seen[skey]) {
                order.push(skey);
                seen[skey] = true;
            }
        });
        $('#cmsFooterElementOrder').val(JSON.stringify(order));
        return order;
    }

    function collectSocialItems() {
        var items = [];
        $('#cmsFooterSocialItems .cms-lb-ft-social-item').each(function (si) {
            var $row = $(this);
            items.push({
                id: $row.find('.cms-lb-ft-social-id').val() || ('soc_' + Date.now() + '_' + si),
                title: $row.find('input[name*="[title]"]').val() || '',
                url: $row.find('input[name*="[url]"]').val() || '',
                icon: $row.find('input[name*="[icon]"]').val() || '',
                icon_image: $row.find('.cms-lb-ft-social-icon-path').val() || ''
            });
        });
        return items;
    }

    function reindexSocialItems() {
        $('#cmsFooterSocialItems .cms-lb-ft-social-item').each(function (si) {
            var $row = $(this);
            $row.attr('data-social-index', si);
            $row.find('.cms-lb-ft-social-id').attr('name', 'social_items[' + si + '][id]');
            $row.find('input[name*="[title]"]').attr('name', 'social_items[' + si + '][title]');
            $row.find('input[name*="[url]"]').attr('name', 'social_items[' + si + '][url]');
            $row.find('input[name*="[icon]"]').attr('name', 'social_items[' + si + '][icon]');
            $row.find('.cms-lb-ft-social-icon-path').attr('name', 'social_items[' + si + '][icon_image]');
            $row.find('.cms-lb-ft-social-icon-file').attr('name', 'social_icon_file_' + si);
        });
    }

    function buildSocialItemHtml(index, id) {
        id = id || ('soc_' + Date.now());
        return ''
            + '<div class="cms-lb-ft-social-item" data-social-index="' + index + '">'
            + '<div class="cms-lb-ft-social-item__head">'
            + '<span class="cms-lb-ft-social-item__drag cms-drag-handle"><i class="fa fa-bars"></i></span>'
            + '<input type="hidden" name="social_items[' + index + '][id]" class="cms-lb-ft-social-id" value="' + id + '">'
            + '<input type="text" name="social_items[' + index + '][title]" class="form-control input-sm cms-lb-ft-social-input" placeholder="Label" value="">'
            + '<button type="button" class="btn btn-danger btn-xs cms-lb-ft-remove-social"><i class="fa fa-trash-o"></i></button>'
            + '</div>'
            + '<div class="cms-lb-ft-social-item__row"><input type="text" name="social_items[' + index + '][url]" class="form-control input-sm cms-lb-ft-social-input" placeholder="URL" value=""></div>'
            + '<div class="cms-lb-ft-social-item__row cms-lb-ft-social-item__icon-row">'
            + '<input type="text" name="social_items[' + index + '][icon]" class="form-control input-sm cms-lb-ft-social-input" placeholder="fa-facebook" value="">'
            + '<input type="hidden" name="social_items[' + index + '][icon_image]" class="cms-lb-ft-social-icon-path" value="">'
            + '<label class="btn btn-default btn-xs cms-lb-ft-social-upload-btn"><i class="fa fa-image"></i> Icon<input type="file" name="social_icon_file_' + index + '" accept=".ico,.jpg,.jpeg,.png,.gif,.webp,.svg" class="cms-lb-ft-social-icon-file" hidden></label>'
            + '</div></div>';
    }

    function initFooterSortables() {
        if (tab !== 'footer') {
            return;
        }
        var $elList = $('#cmsFooterElementsSortable');
        if ($elList.length && $.fn.sortable) {
            if ($elList.hasClass('ui-sortable')) {
                $elList.sortable('destroy');
            }
            $elList.sortable({
                handle: '.cms-lb-header-el__drag',
                axis: 'y',
                tolerance: 'pointer',
                update: function () {
                    collectFooterElementOrder();
                    scheduleRefresh();
                }
            });
        }
        var $socialList = $('#cmsFooterSocialItems');
        if ($socialList.length && $.fn.sortable) {
            if ($socialList.hasClass('ui-sortable')) {
                $socialList.sortable('destroy');
            }
            $socialList.sortable({
                handle: '.cms-lb-ft-social-item__drag',
                axis: 'y',
                tolerance: 'pointer',
                update: function () {
                    reindexSocialItems();
                    scheduleRefresh();
                }
            });
        }
        var $secList = $('#cmsFooterSections');
        if ($secList.length && $.fn.sortable) {
            if ($secList.hasClass('ui-sortable')) {
                $secList.sortable('destroy');
            }
            $secList.sortable({
                handle: '.cms-lb-ft-section__drag',
                axis: 'y',
                tolerance: 'pointer',
                update: function () {
                    reindexFooterSections();
                    scheduleRefresh();
                }
            });
        }
    }

    function applyFooterSectionFlagsToCfg(cfg, sections) {
        (sections || []).forEach(function (sec) {
            if (!sec || !sec.id) {
                return;
            }
            var sk = sectionStorageKey(sec.id);
            cfg['show_' + sk] = sec.show !== false;
            cfg[sk + '_align'] = sec.links_align || 'left';
            cfg[sk + '_title_align'] = sec.title_align || 'left';
        });
    }

    function collectFooterConfig() {
        syncAllFooterLinkRows();
        collectFooterElementOrder();
        var cfg = {
            tagline: $('textarea[name="tagline"]').val() || '',
            copyright: $('input[name="copyright"]').val() || '',
            newsletter_title: $('input[name="newsletter_title"]').val() || '',
            newsletter_desc: $('textarea[name="newsletter_desc"]').val() || '',
            newsletter_placeholder: $('input[name="newsletter_placeholder"]').val() || '',
            legal_link_1_label: $('input[name="legal_link_1_label"]').val() || '',
            legal_link_1_url: $('input[name="legal_link_1_url"]').val() || '',
            legal_link_2_label: $('input[name="legal_link_2_label"]').val() || '',
            legal_link_2_url: $('input[name="legal_link_2_url"]').val() || '',
            logo_image: $('#footerLogoImagePath').val() || '',
            bg_color: $('input[name="footer_bg_color"]').val() || '#ffffff',
            text_color: $('input[name="footer_text_color"]').val() || '#1e293b',
            accent_color: $('input[name="footer_accent_color"]').val() || $('input[data-ft-key="accent_color"]').val() || '#c28913',
            content_align: $('input[name="content_align"]:checked').val() || 'center'
        };
        $('input.cms-lb-flag').each(function () {
            var name = $(this).attr('name');
            if (name && name.indexOf('show_') === 0 && name.indexOf('show_section_') !== 0) {
                cfg[name] = isFlagOn(name);
            }
        });
        $('input.cms-lb-align-input.cms-lb-ft-live, input.cms-lb-align-input[name$="_align"]').each(function () {
            var name = $(this).attr('name');
            if (!name || name.indexOf('_align') < 0) {
                return;
            }
            if ($(this).is(':checked')) {
                cfg[name] = $(this).val();
            }
        });
        $('input.cms-lb-align-input[name$="_title_align"]').each(function () {
            var name = $(this).attr('name');
            if (name && $(this).is(':checked')) {
                cfg[name] = $(this).val();
            }
        });
        cfg.footer_sections = collectFooterSections();
        applyFooterSectionFlagsToCfg(cfg, cfg.footer_sections);
        cfg.social_items = collectSocialItems();
        var orderRaw = $('#cmsFooterElementOrder').val();
        try {
            cfg.footer_element_order = orderRaw ? JSON.parse(orderRaw) : collectFooterElementOrder();
        } catch (e2) {
            cfg.footer_element_order = collectFooterElementOrder();
        }
        return $.extend(cfg, collectElementColors(), collectElementStyles());
    }

    function previewQueryString(extra) {
        var q = 'tab=' + encodeURIComponent(tab)
            + '&header_profile=' + encodeURIComponent(headerProfileSlug)
            + '&footer_profile=' + encodeURIComponent(footerProfileSlug);
        if (tab === 'footer') {
            q += '&compact=1';
        }
        if (extra) {
            q += extra;
        }
        return q;
    }

    function layoutBuilderBaseUrl() {
        return String($root.attr('data-page-url') || tabBase || '').trim();
    }

    function readUrlProfileSlugs() {
        var params;
        try {
            params = new URLSearchParams(window.location.search);
        } catch (ignoreParams) {
            return { header: '', footer: '', tab: '' };
        }
        return {
            header: String(params.get('header_profile') || '').trim(),
            footer: String(params.get('footer_profile') || '').trim(),
            tab: String(params.get('tab') || '').trim().toLowerCase()
        };
    }

    function layoutBuilderWorkspaceUrl() {
        var pageBase = layoutBuilderBaseUrl();
        if (!pageBase) {
            return String($root.attr('data-workspace-url') || $root.data('workspace-url') || '').trim();
        }
        var wsBase = pageBase.replace(/\/?$/, '') + '/workspace';
        var urlSlugs = readUrlProfileSlugs();
        var tabVal = urlSlugs.tab === 'footer' ? 'footer' : (String($root.data('tab') || tab || 'header'));
        var header = urlSlugs.header || String($root.attr('data-header-profile') || headerProfileSlug || 'site');
        var footer = urlSlugs.footer || String($root.attr('data-footer-profile') || footerProfileSlug || header);
        return wsBase
            + '?tab=' + encodeURIComponent(tabVal)
            + '&header_profile=' + encodeURIComponent(header)
            + '&footer_profile=' + encodeURIComponent(footer);
    }

    function applyUrlProfileSlugsToRoot() {
        var urlSlugs = readUrlProfileSlugs();
        if (!urlSlugs.header && !urlSlugs.footer) {
            return;
        }
        var header = urlSlugs.header || headerProfileSlug;
        var footer = urlSlugs.footer || footerProfileSlug || header;
        var activeTab = urlSlugs.tab === 'footer' ? 'footer' : tab;
        var active = activeTab === 'footer' ? footer : header;
        applyProfileSlugs(header, footer, active);
    }

    function syncLayoutProfileHiddenFields(header, footer, active) {
        header = String(header || '');
        footer = String(footer || '');
        active = String(active || (tab === 'footer' ? footer : header));
        $('#cmsHeaderBuilderForm, #cmsFooterBuilderForm').each(function () {
            var $form = $(this);
            if (header !== '') {
                $form.find('input[name="header_layout_profile"]').val(header);
            }
            if (footer !== '') {
                $form.find('input[name="footer_layout_profile"]').val(footer);
            }
            if (active !== '') {
                $form.find('input[name="layout_profile"]').val(active);
            }
        });
    }

    function applyProfileSlugs(header, footer, active) {
        header = String(header || headerProfileSlug || 'site');
        footer = String(footer || footerProfileSlug || header);
        active = String(active || (tab === 'footer' ? footer : header));
        headerProfileSlug = header;
        footerProfileSlug = footer;
        profileSlug = active;
        $root.attr('data-profile', profileSlug);
        $root.attr('data-header-profile', headerProfileSlug);
        $root.attr('data-footer-profile', footerProfileSlug);
    }

    function syncProfileSlugsFromDom(opts) {
        opts = opts || {};
        var preferSelect = opts.preferSelect !== false;

        var urlSlugs = readUrlProfileSlugs();
        var $form = tab === 'footer' ? $('#cmsFooterBuilderForm') : $('#cmsHeaderBuilderForm');
        var $select = $('#cmsLbProfileSelect');

        var header = urlSlugs.header || String($root.attr('data-header-profile') || headerProfileSlug || 'site');
        var footer = urlSlugs.footer || String($root.attr('data-footer-profile') || footerProfileSlug || header);
        var active = String($root.attr('data-profile') || profileSlug || (tab === 'footer' ? footer : header));

        if ($form.length) {
            var formHeader = String($form.find('input[name="header_layout_profile"]').val() || '');
            var formFooter = String($form.find('input[name="footer_layout_profile"]').val() || '');
            var formProfile = String($form.find('input[name="layout_profile"]').val() || '');
            if (!urlSlugs.header && formHeader !== '') {
                header = formHeader;
            }
            if (!urlSlugs.footer && formFooter !== '') {
                footer = formFooter;
            }
            if (formProfile !== '') {
                active = formProfile;
            }
        }

        if (preferSelect && $select.length) {
            var selected = String($select.val() || '');
            if (selected !== '') {
                active = selected;
                if (tab === 'footer') {
                    footer = selected;
                } else {
                    header = selected;
                }
            }
        }

        applyProfileSlugs(header, footer, active);
        syncLayoutProfileHiddenFields(header, footer, active);
    }

    function navigateToLayoutProfile(activeTab, slug) {
        slug = String(slug || '').trim();
        var base = layoutBuilderBaseUrl();
        if (!slug || !base) {
            return;
        }
        var header = String($root.attr('data-header-profile') || headerProfileSlug || 'site');
        var footer = String($root.attr('data-footer-profile') || footerProfileSlug || 'site');
        if (activeTab === 'footer') {
            footer = slug;
        } else {
            header = slug;
        }
        applyProfileSlugs(header, footer, slug);
        syncLayoutProfileHiddenFields(header, footer, slug);
        var q = '?header_profile=' + encodeURIComponent(header)
            + '&footer_profile=' + encodeURIComponent(footer);
        if (activeTab === 'footer') {
            q += '&tab=footer';
        }
        window.location.assign(base + q);
    }

    function refreshPreview(useSavedConfig) {
        if (!previewUrl) {
            return;
        }
        syncProfileSlugsFromDom();
        var $frame = $('#cmsLbPreviewFrame');
        var url;
        if (useSavedConfig) {
            url = previewUrl + '?' + previewQueryString('&saved=1&t=' + Date.now());
        } else {
            var cfg = tab === 'footer' ? collectFooterConfig() : collectHeaderConfig();
            var b64 = '';
            try {
                b64 = btoa(unescape(encodeURIComponent(JSON.stringify(cfg))));
            } catch (e) {
                return;
            }
            url = previewUrl + '?' + previewQueryString(
                '&live=1&cfg=' + encodeURIComponent(b64) + '&t=' + Date.now()
            );
        }
        $frame.attr('src', url);
    }

    function syncStickyPreviewTop() {
        var topbar = document.querySelector('.cms-topbar');
        var preview = document.querySelector('.cms-layout-builder__preview--top, .cms-layout-builder__preview--side');
        if (!preview) {
            return;
        }
        var top = 0;
        if (topbar) {
            top = Math.ceil(topbar.getBoundingClientRect().height);
        }
        preview.style.top = top + 'px';
        document.documentElement.style.setProperty('--cms-lb-preview-sticky-top', top + 'px');
    }

    function fitPreviewFrameHeight() {
        var frame = document.getElementById('cmsLbPreviewFrame');
        if (!frame) {
            return;
        }
        var wrap = frame.parentElement;
        try {
            var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
            if (!doc || !doc.body) {
                return;
            }
            var win = frame.contentWindow;
            if (win) {
                win.scrollTo(0, 0);
            }
            var h;
            if (tab === 'header') {
                var header = doc.querySelector('.cms-ws-header');
                if (header) {
                    h = Math.ceil(header.getBoundingClientRect().height);
                } else {
                    h = doc.body.scrollHeight;
                }
                h = Math.max(h, 48);
            } else {
                var footer = doc.querySelector('.cms-ws-footer');
                var compactFooter = doc.body && doc.body.classList.contains('cms-builder-preview-body--footer-compact');
                if (footer) {
                    h = Math.ceil(footer.getBoundingClientRect().height);
                } else {
                    h = doc.body.scrollHeight;
                }
                if (!compactFooter) {
                    h = Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight, h);
                }
                h = Math.max(h, 120);
            }
            frame.style.height = h + 'px';
            if (wrap && wrap.classList.contains('cms-layout-builder__iframe-wrap')) {
                if (tab === 'header') {
                    wrap.style.height = h + 'px';
                    wrap.style.maxHeight = h + 'px';
                    wrap.style.overflowY = 'hidden';
                } else {
                    var maxH = Math.max(200, Math.round(window.innerHeight * 0.72));
                    wrap.style.height = Math.min(h, maxH) + 'px';
                    wrap.style.maxHeight = maxH + 'px';
                    wrap.style.overflowY = h > maxH ? 'auto' : 'hidden';
                }
            }
        } catch (e) {
            var fallback = tab === 'header' ? 56 : 280;
            frame.style.height = fallback + 'px';
            if (wrap && wrap.classList.contains('cms-layout-builder__iframe-wrap')) {
                if (tab === 'header') {
                    wrap.style.height = fallback + 'px';
                    wrap.style.maxHeight = fallback + 'px';
                } else {
                    wrap.style.height = fallback + 'px';
                    wrap.style.maxHeight = fallback + 'px';
                }
            }
        }
        syncStickyPreviewTop();
    }

    function scheduleInitialPreview() {
        var run = function () {
            refreshPreview(true);
        };
        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(run, { timeout: 2000 });
        } else {
            window.setTimeout(run, 600);
        }
    }

    function layoutBuilderPageUrl(activeTab, slug) {
        var base = layoutBuilderBaseUrl();
        if (!base) {
            return window.location.href;
        }
        var header = headerProfileSlug;
        var footer = footerProfileSlug;
        slug = String(slug || '').trim();
        if (activeTab === 'footer') {
            footer = slug || footer;
        } else if (slug !== '') {
            header = slug;
        } else {
            syncProfileSlugsFromDom();
            header = headerProfileSlug;
            footer = footerProfileSlug;
        }
        var q = '?header_profile=' + encodeURIComponent(header)
            + '&footer_profile=' + encodeURIComponent(footer);
        if (activeTab === 'footer') {
            q += '&tab=footer';
        }
        return base + q;
    }

    function scheduleRefresh() {
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(refreshPreview, 280);
    }

    function syncHeaderElementPositionRows() {
        $('.cms-lb-header-el').each(function () {
            var $el = $(this);
            var showKey = $el.data('show-field');
            if (!showKey) {
                return;
            }
            var on = isFlagOn(showKey);
            $el.toggleClass('is-active', on);
            $el.find('.cms-lb-element-pos-row').toggleClass('is-visible', on);
        });
    }

    function syncFooterToggleRows() {
        $('.cms-lb-toggle-row').each(function () {
            var $row = $(this);
            var $flag = $row.find('input.cms-lb-flag').first();
            if (!$flag.length) {
                return;
            }
            var name = $flag.attr('name');
            $row.toggleClass('is-on', isFlagOn(name));
        });
    }

    function toggleFlag(name) {
        if (!name) {
            return;
        }
        setFlagOn(name, !isFlagOn(name));
        if ($('.cms-lb-header-el[data-show-field="' + name + '"]').length) {
            syncHeaderElementPositionRows();
        }
        syncFooterToggleRows();
        scheduleRefresh();
    }

    function showLayoutPanel($panel) {
        if (!$panel || !$panel.length) {
            return;
        }
        $('#cmsLbNewLayoutPanel, #cmsLbDupLayoutPanel').each(function () {
            var $p = $(this);
            $p.prop('hidden', true).attr('aria-hidden', 'true').addClass('is-collapsed');
        });
        $panel.prop('hidden', false).attr('aria-hidden', 'false').removeClass('is-collapsed');
    }

    function hideLayoutPanels() {
        $('#cmsLbNewLayoutPanel, #cmsLbDupLayoutPanel').each(function () {
            var $p = $(this);
            $p.prop('hidden', true).attr('aria-hidden', 'true').addClass('is-collapsed');
        });
    }

    function initWorkspaceBindings() {
        syncProfileSlugsFromDom({ preferSelect: false });
        hideLayoutPanels();

        $('#cmsLbNewLayoutBtn').on('click', function () {
            showLayoutPanel($('#cmsLbNewLayoutPanel'));
        });
        $('#cmsLbDupLayoutBtn').on('click', function () {
            showLayoutPanel($('#cmsLbDupLayoutPanel'));
        });
        $('.cms-lb-cancel-new, .cms-lb-cancel-dup').on('click', function () {
            hideLayoutPanels();
        });

        $('#cmsFooterSections .cms-lb-ft-section').each(function () {
            var $sec = $(this);
            var title = $sec.find('.cms-lb-ft-section-title').val() || '';
            var $firstRow = $sec.find('.cms-lb-ft-link-row').first();
            if (title && !$firstRow.find('.cms-lb-ft-link-page-select').val()) {
                var autoPage = resolveFooterPageSelectForTitle(title);
                if (autoPage) {
                    $firstRow.find('.cms-lb-ft-link-page-select').val(autoPage);
                }
            }
        });
        $('#cmsFooterSections .cms-lb-ft-link-row').each(function () {
            syncFooterLinkRowUi($(this));
        });

        $('#cmsLbAddSocialItem').on('click', function () {
            var $wrap = $('#cmsFooterSocialItems');
            if (!$wrap.length) {
                return;
            }
            var index = $wrap.find('.cms-lb-ft-social-item').length;
            $wrap.append(buildSocialItemHtml(index, 'soc_' + Date.now()));
            reindexSocialItems();
            initFooterSortables();
            scheduleRefresh();
        });

        $('#cmsLbAddFooterSection').on('click', function () {
            var $wrap = $('#cmsFooterSections');
            if (!$wrap.length) {
                return;
            }
            var index = $wrap.find('.cms-lb-ft-section').length;
            var newId = 'sec_' + Date.now();
            var $sec = $(buildFooterSectionHtml(index, newId, ''));
            $wrap.append($sec);
            upsertFooterSectionElement(newId, '');
            reindexFooterSections();
            scheduleRefresh();
        });

        $('#cmsLbPresetSplit').on('click', function () {
            var preset = {
                show_promo: true,
                show_logo: true,
                show_nav: true,
                show_phone: false,
                show_search: false,
                show_wishlist: true,
                show_cart: true,
                show_account: true,
                promo_align: 'center',
                logo_align: 'left',
                nav_align: 'center',
                phone_align: 'right',
                search_align: 'right',
                wishlist_align: 'right',
                cart_align: 'right',
                account_align: 'right'
            };
            $.each(preset, function (key, val) {
                if (key.indexOf('show_') === 0) {
                    setFlagOn(key, !!val);
                } else if (key.indexOf('_align') > 0) {
                    setElementAlign(key, val);
                }
            });
            syncHeaderElementPositionRows();
            scheduleRefresh();
        });

        $('#cmsLbRefreshPreview').on('click', function () {
            refreshPreview(true);
        });
        $('#cmsLbPreviewFrame').on('load', fitPreviewFrameHeight);
        $(window).on('resize.cmsLbPreview', function () {
            window.clearTimeout(window._cmsLbPreviewResizeTimer);
            window._cmsLbPreviewResizeTimer = window.setTimeout(function () {
                fitPreviewFrameHeight();
                syncStickyPreviewTop();
            }, 120);
        });
        syncStickyPreviewTop();
        function previewFaviconThumb(src, storedPath) {
            var $thumb = $('#faviconPreviewThumb');
            var $wrap = $('.cms-lb-favicon-preview-wrap');
            var $path = $('#faviconPreviewPath');
            if (!$thumb.length) {
                return;
            }
            if (src) {
                $thumb.attr('src', src);
                $wrap.removeClass('is-empty').show();
            }
            if ($path.length) {
                $path.text(storedPath || $('#faviconImagePath').val() || '');
            }
        }

        $('input[name="logo_file"]').on('change', scheduleRefresh);
        $('input[name="favicon_file"]').on('change', function () {
            var file = this.files && this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    previewFaviconThumb(e.target.result, file.name + ' (not saved yet)');
                };
                reader.readAsDataURL(file);
            }
            scheduleRefresh();
        });
        function faviconPreviewUrlFromStoredPath(path) {
            path = (path || '').trim();
            if (!path) {
                return '';
            }
            if (/^https?:\/\//i.test(path)) {
                return path;
            }
            var base = ($('#cmsHeaderBuilderForm').attr('data-upload-base') || '').replace(/\/$/, '');
            if (!base) {
                return '';
            }
            if (path.indexOf('webshop/') === 0 || path.indexOf('cms_media/') === 0) {
                return base + '/' + path;
            }
            return base + '/webshop/' + path;
        }

        $('#logoImagePath, #footerLogoImagePath').on('change', scheduleRefresh);
        $('#faviconImagePath').on('change', function () {
            var path = $(this).val() || '';
            if (path === '') {
                previewFaviconThumb('', '');
                $('.cms-lb-favicon-preview-wrap').addClass('is-empty');
            } else {
                previewFaviconThumb(faviconPreviewUrlFromStoredPath(path), path);
            }
            scheduleRefresh();
        });
        $('#cmsHeaderBuilderForm, #cmsFooterBuilderForm').on('submit', function () {
            syncStylesEnabledFlags($(this));
            syncHeaderElementPositionRows();
            syncFooterToggleRows();
            ensureFooterSectionShowFlags();
            syncAllFooterLinkRows();
            collectFooterElementOrder();
        });

        syncHeaderElementPositionRows();
        syncElementStylesPanels($('#cmsHeaderBuilderForm, #cmsFooterBuilderForm'));
        syncFooterToggleRows();
        initFooterSortables();
        scheduleInitialPreview();
    }

    function loadWorkspaceAsync() {
        var $host = $('#cmsLbAsyncHost');
        var url = layoutBuilderWorkspaceUrl();
        if (!$host.length || !url) {
            initWorkspaceBindings();
            return;
        }
        $.ajax({
            url: url,
            cache: false,
            dataType: 'html'
        })
            .done(function (html) {
                $host.replaceWith(html);
                var $json = $('#cmsLbFooterPagesJson');
                if ($json.length) {
                    try {
                        cmsFooterPages = JSON.parse($json.text());
                        if (!Array.isArray(cmsFooterPages)) {
                            cmsFooterPages = [];
                        }
                    } catch (ignorePages) {
                        cmsFooterPages = [];
                    }
                    $json.remove();
                    $root.attr('data-cms-footer-pages', JSON.stringify(cmsFooterPages));
                }
                initWorkspaceBindings();
            })
            .fail(function () {
                $host.html('<p class="text-danger cms-layout-builder__loading">Could not load layout editor. <a href="#" onclick="location.reload();return false;">Retry</a></p>');
            });
    }

    $(document).on('change', '#cmsLbProfileSelect', function () {
        navigateToLayoutProfile(tab, $(this).val());
    });

    $(document).on('click', 'button.cms-lb-header-el__switch[role="switch"]', function (e) {
        e.preventDefault();
        toggleFlag($(this).data('flag'));
    });

    $(document).on('click', '.cms-lb-header-el__meta', function (e) {
        e.preventDefault();
        toggleFlag($(this).data('flag'));
    });

    $(document).on('keydown', 'button.cms-lb-header-el__switch[role="switch"]', function (e) {
        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            toggleFlag($(this).data('flag'));
        }
    });

    $(document).on('click', '.cms-lb-toggle-row', function (e) {
        if ($(e.target).closest('button.cms-lb-header-el__switch').length) {
            return;
        }
        var name = $(this).find('input.cms-lb-flag').attr('name');
        toggleFlag(name);
    });

    $(document).on('input change', '.cms-lb-live, .cms-lb-color', scheduleRefresh);
    $(document).on('input change', '.cms-lb-ft-live, .cms-lb-ft-color, .cms-lb-ft-text, .cms-lb-ft-section-input, .cms-lb-ft-social-input', scheduleRefresh);

    $(document).on('change', '.cms-lb-ft-link-page-select', function () {
        syncFooterLinkRowUi($(this).closest('.cms-lb-ft-link-row'));
        scheduleRefresh();
    });

    $(document).on('click', '.cms-lb-ft-remove-social', function (e) {
        e.preventDefault();
        $(this).closest('.cms-lb-ft-social-item').remove();
        reindexSocialItems();
        scheduleRefresh();
    });

    function reindexCustomHeaderTags() {
        $('#cmsHeaderCustomTags .cms-lb-custom-header-tag-item').each(function (i) {
            var $row = $(this);
            $row.attr('data-tag-index', i);
            $row.find('.cms-lb-custom-header-tag-name').attr('name', 'custom_header_tags[' + i + '][name]');
            $row.find('.cms-lb-custom-header-tag-code').attr('name', 'custom_header_tags[' + i + '][code]');
        });
    }

    function buildCustomHeaderTagRow(index) {
        return ''
            + '<div class="cms-lb-custom-header-tag-item" data-tag-index="' + index + '">'
            + '<div class="cms-lb-custom-header-tag-item__head">'
            + '<label class="cms-lb-custom-header-tag-item__label">Tag name</label>'
            + '<input type="text" name="custom_header_tags[' + index + '][name]" class="form-control input-sm cms-lb-custom-header-tag-name" placeholder="e.g. Meta Pixel, Hotjar" value="">'
            + '<button type="button" class="btn btn-default btn-xs cms-lb-custom-header-tag-remove" title="Remove tag"><i class="fa fa-trash-o"></i></button>'
            + '</div>'
            + '<label class="cms-lb-custom-header-tag-item__label">Header code</label>'
            + '<textarea name="custom_header_tags[' + index + '][code]" class="form-control input-sm cms-lb-custom-header-tag-code" rows="8" '
            + 'placeholder="Paste &lt;title&gt;, &lt;meta&gt;, &lt;link&gt;, &lt;script&gt;, JSON-LD {&quot;@context&quot;:...}, or &lt;noscript&gt;. Shown in webshop &lt;head&gt; on every page."></textarea>'
            + '</div>';
    }

    $(document).on('click', '#cmsLbAddCustomHeaderTag', function (e) {
        e.preventDefault();
        var $wrap = $('#cmsHeaderCustomTags');
        if (!$wrap.length) {
            return;
        }
        var index = $wrap.find('.cms-lb-custom-header-tag-item').length;
        $wrap.find('#cmsLbAddCustomHeaderTag').before(buildCustomHeaderTagRow(index));
        reindexCustomHeaderTags();
        setCustomHeaderTagsSectionExpanded(true);
        updateCustomHeaderTagsBadge();
    });

    $(document).on('click', '.cms-lb-custom-header-tag-remove', function (e) {
        e.preventDefault();
        var $wrap = $('#cmsHeaderCustomTags');
        var $row = $(this).closest('.cms-lb-custom-header-tag-item');
        if ($wrap.find('.cms-lb-custom-header-tag-item').length <= 1) {
            $row.find('.cms-lb-custom-header-tag-name').val('');
            $row.find('.cms-lb-custom-header-tag-code').val('');
            updateCustomHeaderTagsBadge();
            return;
        }
        $row.remove();
        reindexCustomHeaderTags();
        updateCustomHeaderTagsBadge();
    });

    function setLbCollapsibleSectionExpanded($sec, expanded) {
        if (!$sec || !$sec.length) {
            return;
        }
        $sec.toggleClass('is-expanded', !!expanded);
        $sec.find('.cms-lb-section__heading--toggle').attr('aria-expanded', expanded ? 'true' : 'false');
    }

    function setCustomHeaderTagsSectionExpanded(expanded) {
        setLbCollapsibleSectionExpanded($('#cmsLbCustomHeaderTagsSection'), expanded);
    }

    function updateCustomHeaderTagsBadge() {
        var $sec = $('#cmsLbCustomHeaderTagsSection');
        if (!$sec.length) {
            return;
        }
        var count = 0;
        $('#cmsHeaderCustomTags .cms-lb-custom-header-tag-code').each(function () {
            if ($.trim($(this).val() || '') !== '') {
                count++;
            }
        });
        var $heading = $sec.find('.cms-lb-section__heading--toggle');
        var $badge = $heading.find('.cms-lb-section__badge');
        if (count > 0) {
            if (!$badge.length) {
                $badge = $('<span class="cms-lb-section__badge"></span>');
                $heading.append($badge);
            }
            $badge.text(String(count));
        } else if ($badge.length) {
            $badge.remove();
        }
    }

    $(document).on('click', '.cms-lb-section--collapsible .cms-lb-section__heading--toggle', function (e) {
        e.preventDefault();
        var $sec = $(this).closest('.cms-lb-section--collapsible');
        setLbCollapsibleSectionExpanded($sec, !$sec.hasClass('is-expanded'));
    });

    $(document).on('keydown', '.cms-lb-section--collapsible .cms-lb-section__heading--toggle', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            var $sec = $(this).closest('.cms-lb-section--collapsible');
            setLbCollapsibleSectionExpanded($sec, !$sec.hasClass('is-expanded'));
        }
    });

    $(document).on('input', '.cms-lb-custom-header-tag-code', updateCustomHeaderTagsBadge);

    $(document).on('click', '.cms-lb-ft-remove-section', function (e) {
        e.preventDefault();
        var $sec = $(this).closest('.cms-lb-ft-section');
        var sectionId = $.trim($sec.find('.cms-lb-ft-section-id').val() || '');
        $sec.remove();
        if (sectionId) {
            removeFooterSectionElement(sectionId);
        }
        reindexFooterSections();
        scheduleRefresh();
    });

    $(document).on('input', '.cms-lb-ft-section-title', function () {
        var $sec = $(this).closest('.cms-lb-ft-section');
        var sectionId = $.trim($sec.find('.cms-lb-ft-section-id').val() || '');
        var title = $(this).val() || '';
        if (sectionId) {
            upsertFooterSectionElement(sectionId, title);
        }
        var pageId = resolveFooterPageSelectForTitle(title);
        if (pageId) {
            var $row = $sec.find('.cms-lb-ft-link-row').first();
            $row.find('.cms-lb-ft-link-page-select').val(pageId);
            syncFooterLinkRowUi($row);
        }
        scheduleRefresh();
    });

    $(document).on('click', '.cms-lb-ft-add-link', function (e) {
        e.preventDefault();
        var $sec = $(this).closest('.cms-lb-ft-section');
        var si = parseInt($sec.attr('data-section-index'), 10) || 0;
        var li = $sec.find('.cms-lb-ft-link-row').length;
        var $row = $(buildFooterLinkRowHtml(si, li));
        $sec.find('.cms-lb-ft-section__links').append($row);
        syncFooterLinkRowUi($row);
        reindexFooterSections();
        scheduleRefresh();
    });

    $(document).on('click', '.cms-lb-ft-remove-link', function (e) {
        e.preventDefault();
        var $sec = $(this).closest('.cms-lb-ft-section');
        var $rows = $sec.find('.cms-lb-ft-link-row');
        if ($rows.length <= 1) {
            var $row = $(this).closest('.cms-lb-ft-link-row');
            $row.find('.cms-lb-ft-link-page-select').val('');
            $row.find('.cms-lb-ft-link-label, .cms-lb-ft-link-url, .cms-lb-ft-link-page-id').val('');
            $row.removeClass('is-other');
        } else {
            $(this).closest('.cms-lb-ft-link-row').remove();
        }
        reindexFooterSections();
        scheduleRefresh();
    });

    $(document).on('change', '.cms-lb-styles-toggle', function () {
        var checked = $(this).prop('checked');
        var ek = $(this).data('style-for');
        var $row = $(this).closest('.cms-lb-element-pos-row--styles');
        $row.find('.cms-lb-element-styles-body').toggleClass('is-expanded', checked);
        if (ek) {
            $row.find('input.cms-lb-styles-enabled-flag[data-style-for="' + ek + '"]').val(checked ? '1' : '0');
        }
        if (!checked) {
            $row.find('.cms-lb-element-styles-body').find('input, select').val('');
        }
        scheduleRefresh();
    });

    $(document).on('change', '.cms-lb-align-input', function () {
        var $segment = $(this).closest('.cms-lb-segment');
        if ($segment.length) {
            $segment.find('.cms-lb-segment__opt').removeClass('is-active');
            $(this).closest('.cms-lb-segment__opt').addClass('is-active');
        } else {
            var $group = $(this).closest('.cms-lb-align');
            $group.find('.cms-lb-align__opt').removeClass('is-active');
            $(this).closest('.cms-lb-align__opt').addClass('is-active');
        }
        scheduleRefresh();
    });

    applyUrlProfileSlugsToRoot();

    if (typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(function () {
            loadWorkspaceAsync();
        }, { timeout: 120 });
    } else {
        setTimeout(loadWorkspaceAsync, 0);
    }
})(jQuery);
