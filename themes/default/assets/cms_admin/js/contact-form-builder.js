/**
 * CMS Admin — Contact form field builder (Form Templates).
 */
(function ($) {
    'use strict';

    var typeOptions = [
        { v: 'text', l: 'Text' },
        { v: 'email', l: 'Email' },
        { v: 'tel', l: 'Phone' },
        { v: 'textarea', l: 'Textarea' },
        { v: 'select', l: 'Dropdown' },
        { v: 'country', l: 'Country' },
        { v: 'date', l: 'Date' },
        { v: 'hidden', l: 'Hidden' }
    ];
    var mapOptions = [
        { v: 'name', l: 'Name' },
        { v: 'phone', l: 'Phone' },
        { v: 'email', l: 'Email' },
        { v: 'message', l: 'Message' },
        { v: 'country', l: 'Country' },
        { v: 'extra', l: 'Extra field' }
    ];

    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function optionRowsHtml(options) {
        var html = '';
        (options || []).forEach(function (opt) {
            html += '<div class="cms-cf-opt-row">'
                + '<input type="text" class="form-control input-sm cms-cf-opt-value" placeholder="value" value="' + escHtml(opt.value || '') + '"> '
                + '<input type="text" class="form-control input-sm cms-cf-opt-label" placeholder="label" value="' + escHtml(opt.label || '') + '"> '
                + '<button type="button" class="btn btn-xs btn-danger cms-cf-remove-opt">&times;</button>'
                + '</div>';
        });
        return html;
    }

    function extraCellHtml(type, field) {
        field = field || {};
        if (type === 'select') {
            return '<div class="cms-cf-options-wrap">' + optionRowsHtml(field.options || [])
                + '<button type="button" class="btn btn-default btn-xs cms-cf-add-opt"><i class="fa fa-plus"></i> Option</button></div>';
        }
        if (type === 'country') {
            return '<span class="cms-cf-country-hint text-muted"><i class="fa fa-globe"></i> Options from <code>country_master</code> table</span>';
        }
        return '<input type="text" class="form-control input-sm cms-cf-placeholder" placeholder="Placeholder" value="' + escHtml(field.placeholder || '') + '">';
    }

    function fieldRowHtml(field) {
        field = field || {};
        var type = field.type || 'text';
        var optsWrap = extraCellHtml(type, field);

        var typeSelect = '<select class="form-control input-sm cms-cf-type cms-native-select">';
        typeOptions.forEach(function (o) {
            typeSelect += '<option value="' + o.v + '"' + (type === o.v ? ' selected' : '') + '>' + o.l + '</option>';
        });
        typeSelect += '</select>';

        var mapSelect = '<select class="form-control input-sm cms-cf-map cms-native-select">';
        mapOptions.forEach(function (o) {
            mapSelect += '<option value="' + o.v + '"' + ((field.map || 'extra') === o.v ? ' selected' : '') + '>' + o.l + '</option>';
        });
        mapSelect += '</select>';

        return '<tr class="cms-cf-field-row">'
            + '<td><input type="text" class="form-control input-sm cms-cf-name" value="' + escHtml(field.name || '') + '" placeholder="field_name"></td>'
            + '<td><input type="text" class="form-control input-sm cms-cf-label" value="' + escHtml(field.label || '') + '"></td>'
            + '<td>' + typeSelect + '</td>'
            + '<td class="cms-cf-req-cell"><input type="checkbox" class="cms-cf-required"' + (field.required ? ' checked' : '') + '></td>'
            + '<td>' + mapSelect + '</td>'
            + '<td class="cms-cf-extra-cell">' + optsWrap + '</td>'
            + '<td class="cms-cf-actions-cell"><button type="button" class="btn btn-xs btn-danger cms-cf-remove-field" title="Remove">&times;</button></td>'
            + '</tr>';
    }

    function metaScope($root) {
        var $ft = $root.closest('.cms-form-template-builder');
        if ($ft.length) {
            return $ft;
        }
        return $root;
    }

    function syncHiddenFields($root) {
        var fields = [];
        $root.find('.cms-cf-field-row').each(function () {
            var $row = $(this);
            var name = $.trim($row.find('.cms-cf-name').val()).toLowerCase().replace(/[^a-z0-9_]/g, '');
            if (name === '') {
                return;
            }
            var type = $row.find('.cms-cf-type').val() || 'text';
            var field = {
                name: name,
                label: $.trim($row.find('.cms-cf-label').val()) || name,
                type: type,
                required: $row.find('.cms-cf-required').is(':checked'),
                map: $row.find('.cms-cf-map').val() || 'extra'
            };
            if (type === 'select') {
                field.options = [];
                $row.find('.cms-cf-opt-row').each(function () {
                    var val = $.trim($(this).find('.cms-cf-opt-value').val());
                    var lbl = $.trim($(this).find('.cms-cf-opt-label').val());
                    if (val !== '') {
                        field.options.push({ value: val, label: lbl !== '' ? lbl : val });
                    }
                });
            } else if (type === 'country') {
                field.options_source = 'country_master';
            } else {
                var ph = $.trim($row.find('.cms-cf-placeholder').val());
                if (ph !== '') {
                    field.placeholder = ph;
                }
                if (type === 'textarea') {
                    field.rows = 4;
                }
            }
            fields.push(field);
        });
        var $meta = metaScope($root);
        $root.find('.cms-cf-fields-json').val(JSON.stringify(fields));
        $meta.find('.cms-cf-subtitle-hidden').val($.trim($meta.find('.cms-cf-subtitle').val()));
        $meta.find('.cms-cf-button-hidden').val($.trim($meta.find('.cms-cf-button-text').val()) || 'Send Message');
    }

    function bindEvents($root) {
        if ($root.data('cf-bound')) {
            return;
        }
        $root.data('cf-bound', true);

        $root.on('click', '.cms-cf-add-field', function () {
            $root.find('.cms-cf-fields-body').append(fieldRowHtml({ type: 'text', map: 'extra' }));
            syncHiddenFields($root);
        });
        $root.on('click', '.cms-cf-remove-field', function () {
            $(this).closest('.cms-cf-field-row').remove();
            syncHiddenFields($root);
        });
        $root.on('change input', '.cms-cf-subtitle, .cms-cf-button-text, .cms-cf-source, .cms-cf-name, .cms-cf-label, .cms-cf-type, .cms-cf-map, .cms-cf-required, .cms-cf-placeholder, .cms-cf-opt-value, .cms-cf-opt-label', function () {
            syncHiddenFields($root);
        });
        $root.on('change', '.cms-cf-type', function () {
            var $row = $(this).closest('.cms-cf-field-row');
            var type = $(this).val();
            var $cell = $row.find('.cms-cf-extra-cell');
            if (type === 'select') {
                $cell.html('<div class="cms-cf-options-wrap">' + optionRowsHtml([])
                    + '<button type="button" class="btn btn-default btn-xs cms-cf-add-opt"><i class="fa fa-plus"></i> Option</button></div>');
            } else if (type === 'country') {
                $cell.html('<span class="cms-cf-country-hint text-muted"><i class="fa fa-globe"></i> Options from <code>country_master</code> table</span>');
            } else {
                $cell.html('<input type="text" class="form-control input-sm cms-cf-placeholder" placeholder="Placeholder">');
            }
            syncHiddenFields($root);
        });
        $root.on('click', '.cms-cf-add-opt', function () {
            $(this).before('<div class="cms-cf-opt-row">'
                + '<input type="text" class="form-control input-sm cms-cf-opt-value" placeholder="value"> '
                + '<input type="text" class="form-control input-sm cms-cf-opt-label" placeholder="label"> '
                + '<button type="button" class="btn btn-xs btn-danger cms-cf-remove-opt">&times;</button></div>');
            syncHiddenFields($root);
        });
        $root.on('click', '.cms-cf-remove-opt', function () {
            $(this).closest('.cms-cf-opt-row').remove();
            syncHiddenFields($root);
        });
    }

    function init(selector) {
        var $root = $(selector);
        if (!$root.length) {
            return;
        }
        bindEvents($root);
        $root.find('select.cms-native-select').each(function () {
            var $sel = $(this);
            if ($sel.data('select2')) {
                $sel.select2('destroy');
            }
        });
        var raw = $root.find('.cms-cf-fields-json').val() || '[]';
        var fields = [];
        try {
            fields = JSON.parse(raw);
        } catch (e) {
            fields = [];
        }
        var $body = $root.find('.cms-cf-fields-body');
        var hasRenderedRows = $body.find('.cms-cf-field-row').length > 0;
        if (!fields.length && hasRenderedRows) {
            syncHiddenFields($root);
            return;
        }
        $body.empty();
        if (!fields.length) {
            fields = [
                { name: 'name', label: 'Name', type: 'text', required: true, map: 'name', placeholder: 'Enter your name' },
                { name: 'phone', label: 'Phone No', type: 'tel', required: true, map: 'phone', placeholder: 'Enter phone number' },
                { name: 'email', label: 'Email', type: 'email', required: false, map: 'email', placeholder: 'Enter email address' },
                { name: 'message', label: 'Message', type: 'textarea', required: false, map: 'message', placeholder: 'Write your message', rows: 4 }
            ];
        }
        fields.forEach(function (f) {
            $body.append(fieldRowHtml(f));
        });
        syncHiddenFields($root);
    }

    window.CmsContactFormBuilder = {
        init: init,
        sync: function (selector) {
            syncHiddenFields($(selector));
        }
    };
}(jQuery));
