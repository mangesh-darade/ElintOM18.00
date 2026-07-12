/**
 * CMS Admin — Form template builder (fields + design + live preview).
 */
(function ($) {
    'use strict';

    var defaultStyle = {
        container_bg: '#ffffff',
        container_border: '#f3f4f6',
        container_radius: '16',
        container_padding: '32',
        title_color: '#0a1d37',
        title_size: '24',
        subtitle_color: '#64748b',
        label_color: '#0a1d37',
        input_border: '#e5e7eb',
        input_radius: '6',
        input_focus: '#b9860b',
        button_bg: '#b9860b',
        button_hover: '#9a7209',
        button_text_color: '#ffffff',
        grid_columns: '1',
        card_position: 'full',
        custom_css: '',
        preset_key: 'default'
    };

    function normalizeCardPosition(pos) {
        pos = String(pos || 'full').toLowerCase();
        if (pos === 'left' || pos === 'right') {
            return pos;
        }
        return 'full';
    }

    function cardPositionLayoutCss(cardPosition, scope) {
        cardPosition = normalizeCardPosition(cardPosition);
        scope = scope || '';
        return ''
            + scope + '.contact-us-layout{display:grid;gap:32px;align-items:start;max-width:1200px;margin:0 auto;width:100%;}'
            + scope + '.contact-us-layout--full{grid-template-columns:1fr;}'
            + scope + '.contact-us-layout--full .contact-us-layout__aside{display:none;}'
            + scope + '.contact-us-layout--full .contact-us-layout__form{max-width:720px;margin:0 auto;width:100%;}'
            + scope + '.contact-us-layout--left{grid-template-columns:minmax(280px,420px) 1fr;}'
            + scope + '.contact-us-layout--left .contact-us-layout__form{grid-column:1;}'
            + scope + '.contact-us-layout--left .contact-us-layout__aside{grid-column:2;}'
            + scope + '.contact-us-layout--right{grid-template-columns:1fr minmax(280px,420px);}'
            + scope + '.contact-us-layout--right .contact-us-layout__form{grid-column:2;}'
            + scope + '.contact-us-layout--right .contact-us-layout__aside{grid-column:1;}'
            + '@media (max-width:991px){'
            + scope + '.contact-us-layout--left,' + scope + '.contact-us-layout--right{grid-template-columns:1fr;}'
            + scope + '.contact-us-layout--left .contact-us-layout__form,' + scope + '.contact-us-layout--right .contact-us-layout__form{grid-column:1;max-width:720px;margin:0 auto;width:100%;}'
            + scope + '.contact-us-layout--left .contact-us-layout__aside,' + scope + '.contact-us-layout--right .contact-us-layout__aside{display:none;}'
            + '}';
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function parseJson(raw, fallback) {
        try {
            return JSON.parse(raw || '');
        } catch (e) {
            return fallback;
        }
    }

    function readStyleFromDom($root) {
        var style = $.extend({}, defaultStyle);
        $root.find('.cms-ft-style-input').each(function () {
            var key = $(this).data('style-key');
            if (!key) {
                return;
            }
            style[key] = $.trim($(this).val());
        });
        style.custom_css = $root.find('.cms-ft-custom-css').val() || '';
        style.preset_key = $root.find('.cms-ft-preset-key').val() || 'custom';
        return style;
    }

    function applyStyleToDom($root, style, presetKey) {
        style = style || {};
        $root.find('.cms-ft-style-input').each(function () {
            var key = $(this).data('style-key');
            if (!key || typeof style[key] === 'undefined') {
                return;
            }
            $(this).val(style[key]);
        });
        if (typeof style.custom_css !== 'undefined') {
            $root.find('.cms-ft-custom-css').val(style.custom_css);
        }
        if (typeof presetKey !== 'undefined') {
            $root.find('.cms-ft-preset-key').val(presetKey);
        } else if (typeof style.preset_key !== 'undefined') {
            $root.find('.cms-ft-preset-key').val(style.preset_key);
        }
        syncStyleHidden($root, readStyleFromDom($root));
    }

    function syncStyleHidden($root, style) {
        style = style || readStyleFromDom($root);
        $root.find('.cms-ft-style-json').val(JSON.stringify(style));
    }

    function getPresets($root) {
        return parseJson($root.attr('data-style-presets'), {});
    }

    function presetLabel($root, key) {
        var presets = getPresets($root);
        if (key && key !== 'custom' && presets[key] && presets[key].label) {
            return presets[key].label;
        }
        return 'Custom design';
    }

    function markPresetActive($root, key) {
        key = key || 'custom';
        $root.find('.cms-ft-preset-card').removeClass('is-active');
        $root.find('.cms-ft-preset-card[data-preset-key="' + key + '"]').addClass('is-active');
        $root.find('.cms-ft-preview-design-label').text(presetLabel($root, key));
    }

    function setCustomPreset($root) {
        $root.find('.cms-ft-preset-key').val('custom');
        markPresetActive($root, 'custom');
    }

    function getPreviewMode($root) {
        var mode = $root.find('.cms-ft-preview-mode.is-active').attr('data-preview-mode');
        if (mode === 'mobile' || mode === 'tablet') {
            return mode;
        }
        return 'desktop';
    }

    function setPreviewMode($root, mode) {
        if (mode !== 'mobile' && mode !== 'tablet') {
            mode = 'desktop';
        }
        $root.find('.cms-ft-preview-mode').removeClass('is-active');
        $root.find('.cms-ft-preview-mode[data-preview-mode="' + mode + '"]').addClass('is-active');
        $root.find('[data-preview-viewport="1"]')
            .removeClass('is-desktop is-tablet is-mobile')
            .addClass('is-' + mode);
    }

    function styleToCss(style, previewMode) {
        style = $.extend({}, defaultStyle, style || {});
        var cols = Math.max(1, Math.min(4, parseInt(style.grid_columns, 10) || 1));
        if (previewMode === 'mobile' || previewMode === 'tablet') {
            cols = 1;
        }
        var scope = '.cms-ft-preview-html';
        var css = ''
            + scope + '.contact-us-component{background:' + style.container_bg + ';border:1px solid ' + style.container_border + ';border-radius:' + style.container_radius + 'px;padding:' + style.container_padding + 'px;box-shadow:0 20px 25px -5px rgba(15,23,42,.08);}'
            + scope + ' .contact-us-title{margin:0 0 32px;font-size:' + style.title_size + 'px;color:' + style.title_color + ';font-weight:700;line-height:1.25;}'
            + scope + ' .contact-us-subtitle{margin:-20px 0 24px;color:' + style.subtitle_color + ';font-size:14px;}'
            + scope + ' .contact-us-grid{display:grid;grid-template-columns:repeat(' + cols + ',minmax(0,1fr)) !important;gap:24px;}'
            + scope + ' .contact-us-label{font-size:14px;font-weight:700;color:' + style.label_color + ';margin-bottom:8px;display:block;}'
            + scope + ' .contact-us-label-req{color:#dc2626;font-weight:700;}'
            + scope + ' .contact-us-input,' + scope + ' .contact-us-select,' + scope + ' .contact-us-textarea{width:100%;border:1px solid ' + style.input_border + ';border-radius:' + style.input_radius + 'px;padding:12px 16px;font-size:14px;box-sizing:border-box;background:#fff;}'
            + scope + ' .cf-phone-field{display:flex;align-items:stretch;gap:0;border:1px solid ' + style.input_border + ';border-radius:' + style.input_radius + 'px;background:#fff;overflow:visible;}'
            + scope + ' .cf-phone-country-wrap{border-right:1px solid ' + style.input_border + ';}'
            + scope + ' .cf-phone-picker-trigger{border:none;border-radius:0;background:transparent;padding:8px 10px;min-height:44px;}'
            + scope + ' .cf-phone-number{border:none !important;border-radius:0;box-shadow:none !important;background:transparent;padding:12px 16px;font-size:14px;}'
            + scope + ' .contact-us-textarea{min-height:120px;resize:vertical;}'
            + scope + ' .contact-us-field{display:flex;flex-direction:column;min-width:0;}'
            + scope + ' .contact-us-field-full{grid-column:1 / -1;}'
            + scope + ' .contact-us-submit-wrap{grid-column:1 / -1;margin-top:4px;}'
            + scope + ' .contact-us-submit{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;border:0;border-radius:' + style.input_radius + 'px;padding:16px 24px;font-weight:700;font-size:14px;color:' + style.button_text_color + ';background:' + style.button_bg + ';}'
            + scope + ' .contact-us-submit:hover{background:' + style.button_hover + ';}'
            + scope + ' .contact-us-security{grid-column:1 / -1;}';
        css += cardPositionLayoutCss(style.card_position, '.cms-ft-preview-layout');
        if (style.custom_css) {
            css += '\n' + style.custom_css;
        }
        return css;
    }

    function fieldWrapperClass(field) {
        field = field || {};
        var type = field.type || 'text';
        if (type === 'textarea' || type === 'hidden') {
            return 'contact-us-field contact-us-field-full';
        }
        if ((field.map || '') === 'message') {
            return 'contact-us-field contact-us-field-full';
        }
        return 'contact-us-field';
    }

    function renderPreviewField(field) {
        if (!field || !field.name) {
            return '';
        }
        var type = field.type || 'text';
        if (type === 'hidden') {
            return '';
        }
        var label = field.label || field.name;
        var ph = field.placeholder ? ' placeholder="' + escHtml(field.placeholder) + '"' : '';
        var html = '<div class="' + fieldWrapperClass(field) + '">';
        html += '<label class="contact-us-label' + (field.required ? ' contact-us-label--required' : '') + '">' + escHtml(label) + (field.required ? '<span class="contact-us-label-req" aria-hidden="true">*</span>' : '') + '</label>';
        if (type === 'textarea') {
            var rows = field.rows || 4;
            html += '<textarea class="contact-us-textarea" rows="' + rows + '"' + ph + ' disabled></textarea>';
        } else if (type === 'select') {
            html += '<select class="contact-us-select" disabled><option>Select...</option>';
            (field.options || []).forEach(function (opt) {
                html += '<option>' + escHtml(opt.label || opt.value || '') + '</option>';
            });
            html += '</select>';
        } else if (type === 'country') {
            html += '<select class="contact-us-select" disabled><option>Select country...</option>';
            (window.CF_COUNTRIES || []).forEach(function (c) {
                html += '<option>' + escHtml(c.label || c.name || c.value || '') + '</option>';
            });
            html += '</select>';
        } else if (type === 'date') {
            html += '<input type="date" class="contact-us-input" disabled>';
        } else if (type === 'tel' && window.ContactFormPhone) {
            html += window.ContactFormPhone.renderFieldHtml(field, {
                disabled: true,
                idPrefix: 'cf_preview_',
                inputClass: 'cf-phone-number contact-us-input'
            });
        } else {
            html += '<input type="' + escHtml(type === 'tel' ? 'tel' : type) + '" class="contact-us-input"' + ph + ' disabled>';
        }
        html += '</div>';
        return html;
    }

    function updatePreview($root) {
        var $inner = $root.find('.cms-contact-form-builder');
        if (window.CmsContactFormBuilder) {
            window.CmsContactFormBuilder.sync($inner);
        }

        var title = $.trim($root.find('.cms-ft-title').val());
        var subtitle = $.trim($root.find('.cms-cf-subtitle').val());
        var buttonText = $.trim($root.find('.cms-cf-button-text').val()) || 'Send Message';
        var fields = parseJson($root.find('.cms-cf-fields-json').val(), []);
        var style = readStyleFromDom($root);

        if (title) {
            $root.find('.cms-ft-preview-title').text(title).show();
        } else {
            $root.find('.cms-ft-preview-title').empty().hide();
        }
        if (subtitle) {
            $root.find('.cms-ft-preview-subtitle').text(subtitle).show();
        } else {
            $root.find('.cms-ft-preview-subtitle').hide();
        }
        $root.find('.cms-ft-preview-btn-text').text(buttonText);

        var gridHtml = '';
        fields.forEach(function (f) {
            gridHtml += renderPreviewField(f);
        });
        $root.find('.cms-ft-preview-fields').html(gridHtml || '<p class="text-muted contact-us-field-full">Add fields to see preview.</p>');
        if (window.ContactFormPhone) {
            window.ContactFormPhone.init($root.find('.cms-ft-preview-fields'));
        }
        $root.find('.cms-ft-preview-layout')
            .removeClass('contact-us-layout--full contact-us-layout--left contact-us-layout--right')
            .addClass('contact-us-layout--' + normalizeCardPosition(style.card_position));
        $root.find('.cms-ft-preview-style').text(styleToCss(style, getPreviewMode($root)));
        markPresetActive($root, style.preset_key || 'custom');
        syncStyleHidden($root, style);
    }

    function applyPreset($root, key) {
        var presets = getPresets($root);
        if (key === 'custom') {
            setCustomPreset($root);
            updatePreview($root);
            return;
        }
        if (!presets[key] || !presets[key].style) {
            return;
        }
        var merged = $.extend({}, defaultStyle, presets[key].style, { preset_key: key });
        applyStyleToDom($root, merged, key);
        markPresetActive($root, key);
        updatePreview($root);
    }

    function bindEvents($root) {
        if ($root.data('ft-bound')) {
            return;
        }
        $root.data('ft-bound', true);

        $root.on('input change', '.cms-ft-title, .cms-cf-subtitle, .cms-cf-button-text', function () {
            updatePreview($root);
        });

        $root.on('input change', '.cms-ft-style-input, .cms-ft-custom-css', function () {
            setCustomPreset($root);
            updatePreview($root);
        });

        $root.on('change', 'select.cms-ft-style-input', function () {
            setCustomPreset($root);
            updatePreview($root);
        });

        $root.on('change input', '.cms-contact-form-builder .cms-cf-name, .cms-contact-form-builder .cms-cf-label, .cms-contact-form-builder .cms-cf-type, .cms-contact-form-builder .cms-cf-required, .cms-contact-form-builder .cms-cf-map, .cms-contact-form-builder .cms-cf-placeholder, .cms-contact-form-builder .cms-cf-opt-value, .cms-contact-form-builder .cms-cf-opt-label', function () {
            updatePreview($root);
        });

        $root.on('click', '.cms-contact-form-builder .cms-cf-add-field, .cms-contact-form-builder .cms-cf-remove-field, .cms-contact-form-builder .cms-cf-add-opt, .cms-contact-form-builder .cms-cf-remove-opt', function () {
            setTimeout(function () {
                updatePreview($root);
            }, 0);
        });

        $root.on('click', '.cms-ft-preset-card', function () {
            applyPreset($root, $(this).attr('data-preset-key') || 'custom');
        });

        $root.on('click', '.cms-ft-preview-mode', function () {
            setPreviewMode($root, $(this).attr('data-preview-mode'));
            updatePreview($root);
        });

        $root.find('.cms-ft-tabs a[data-toggle="tab"]').on('shown.bs.tab', function () {
            updatePreview($root);
        });
    }

    function init(selector) {
        var $root = $(selector);
        if (!$root.length) {
            return;
        }

        var $cf = $root.find('[data-contact-form-builder="1"]');
        if (window.CmsContactFormBuilder && $cf.length) {
            var cfId = $cf.attr('id');
            window.CmsContactFormBuilder.init(cfId ? ('#' + cfId) : $cf);
        }

        var rawStyle = $root.find('.cms-ft-style-json').val() || '{}';
        var style = parseJson(rawStyle, defaultStyle);
        var presetKey = style.preset_key || $root.find('.cms-ft-preset-key').val() || 'default';
        applyStyleToDom($root, $.extend({}, defaultStyle, style), presetKey);

        $root.find('select.cms-native-select').each(function () {
            var $sel = $(this);
            if ($sel.data('select2')) {
                $sel.select2('destroy');
            }
        });

        bindEvents($root);
        setPreviewMode($root, 'desktop');
        updatePreview($root);
    }

    window.CmsFormTemplateBuilder = {
        init: init,
        updatePreview: function (selector) {
            updatePreview($(selector));
        }
    };

    $(function () {
        $('[data-form-template-builder="1"]').each(function () {
            window.CmsFormTemplateBuilder.init('#' + $(this).attr('id'));
        });

        $('#formTemplateSaveForm input[name="form_key"]').on('input', function () {
            var val = String($(this).val() || '').toLowerCase().replace(/[^a-z0-9._-]/g, '');
            if ($(this).val() !== val) {
                $(this).val(val);
            }
        });

        $('#formTemplateSaveForm').on('submit', function () {
            var $form = $(this);
            $('[data-form-template-builder="1"]').each(function () {
                var $root = $(this);
                if (window.CmsContactFormBuilder) {
                    window.CmsContactFormBuilder.sync($root.find('[data-contact-form-builder="1"]'));
                }
                syncStyleHidden($root, readStyleFromDom($root));
            });

            var fieldsJson = $form.find('.cms-cf-fields-json').val() || '[]';
            var fields = [];
            try {
                fields = JSON.parse(fieldsJson);
            } catch (e) {
                fields = [];
            }
            if (!fields.length) {
                alert('Add at least one form field before saving.');
                return false;
            }

            var formKey = $.trim($form.find('input[name="form_key"]').val());
            if (!formKey) {
                alert('Form key is required (letters, numbers, underscore, hyphen).');
                $form.find('input[name="form_key"]').focus();
                return false;
            }
        });
    });
}(jQuery));
