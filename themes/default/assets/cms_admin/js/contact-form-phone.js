/**
 * Contact form phone fields — custom country picker with flag images.
 */
(function (window) {
    'use strict';

    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function countries() {
        return Array.isArray(window.CF_PHONE_COUNTRIES) ? window.CF_PHONE_COUNTRIES : [];
    }

    function pickerCaretSvg() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            + '<path d="M6 9l6 6 6-6"/></svg>';
    }

    function flagImgUrl(iso) {
        iso = String(iso || '').toLowerCase().replace(/[^a-z]/g, '').substr(0, 2);
        if (iso.length !== 2) {
            return 'data:image/svg+xml,' + encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8">'
                + '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18"/><path d="M12 3a14 14 0 0 0 0 18"/>'
                + '</svg>'
            );
        }
        return 'https://flagcdn.com/w40/' + iso + '.png';
    }

    function defaultCountryValue() {
        var list = countries();
        if (!list.length) {
            return '';
        }
        var preferred = ['India', 'United States', 'United Arab Emirates'];
        var i;
        for (i = 0; i < preferred.length; i++) {
            var j;
            for (j = 0; j < list.length; j++) {
                if (list[j].name === preferred[i] && list[j].option_value) {
                    return list[j].option_value;
                }
            }
        }
        return list[0].option_value || '';
    }

    function countryByValue(val) {
        var list = countries();
        var i;
        for (i = 0; i < list.length; i++) {
            if (list[i].option_value === val) {
                return list[i];
            }
        }
        return list.length ? list[0] : null;
    }

    function renderPickerHtml(fieldName, opts) {
        opts = opts || {};
        var selectedVal = opts.selected || defaultCountryValue();
        var disabled = !!opts.disabled;
        var required = !!opts.required;
        var id = opts.id || ('cf_country_' + fieldName);
        var selected = countryByValue(selectedVal) || {};
        var iso = selected.iso || '';
        var code = selected.code || '';
        var flag = selected.flag_img || flagImgUrl(iso);
        var digits = selected.phone_digits || 0;

        var html = '<div class="cf-phone-picker" id="' + escHtml(id) + '"' + (disabled ? ' data-disabled="1"' : '') + '>';
        html += '<input type="hidden" class="cf-phone-country-value" name="' + escHtml(fieldName) + '_country"'
            + ' value="' + escHtml(selectedVal) + '"'
            + ' data-digits="' + escHtml(String(digits)) + '"'
            + ' data-code="' + escHtml(code) + '"'
            + ' data-iso="' + escHtml(iso) + '"'
            + (required ? ' required' : '') + '>';
        html += '<button type="button" class="cf-phone-picker-trigger" aria-haspopup="listbox" aria-expanded="false"'
            + (disabled ? ' disabled' : '') + '>';
        html += '<img class="cf-phone-picker-flag" src="' + escHtml(flag) + '" width="22" height="16" alt="" loading="lazy" decoding="async">';
        html += '<span class="cf-phone-picker-code">' + escHtml(code) + '</span>';
        html += '<span class="cf-phone-picker-caret" aria-hidden="true">' + pickerCaretSvg() + '</span>';
        html += '</button>';
        html += '<div class="cf-phone-picker-panel" role="listbox" hidden><ul class="cf-phone-picker-list">';
        countries().forEach(function (c) {
            if (!c || !c.option_value) {
                return;
            }
            var cIso = c.iso || '';
            var cFlag = c.flag_img || flagImgUrl(cIso);
            var isSel = c.option_value === selectedVal ? ' is-selected' : '';
            html += '<li class="cf-phone-picker-item' + isSel + '" role="option" tabindex="-1"'
                + ' data-value="' + escHtml(c.option_value) + '"'
                + ' data-digits="' + escHtml(String(c.phone_digits || '')) + '"'
                + ' data-code="' + escHtml(c.code || '') + '"'
                + ' data-iso="' + escHtml(cIso) + '"'
                + ' data-flag="' + escHtml(cFlag) + '">';
            html += '<img class="cf-phone-picker-flag" src="' + escHtml(cFlag) + '" width="22" height="16" alt="" loading="lazy" decoding="async">';
            html += '<span class="cf-phone-picker-item-name">' + escHtml(c.name || '') + '</span>';
            html += '<span class="cf-phone-picker-item-code">' + escHtml(c.code || '') + '</span>';
            html += '</li>';
        });
        html += '</ul></div></div>';
        return html;
    }

    function renderFieldHtml(field, opts) {
        field = field || {};
        opts = opts || {};
        var name = String(field.name || '').toLowerCase().replace(/[^a-z0-9_]/g, '');
        if (!name) {
            return '';
        }
        var placeholder = field.placeholder || 'Phone number';
        var required = !!field.required;
        var disabled = !!opts.disabled;
        var idPrefix = opts.idPrefix || 'cf_';
        var inputClass = opts.inputClass || 'cf-phone-number contact-us-input';
        var fieldId = idPrefix + name;
        var reqAttr = required ? ' required' : '';
        var disAttr = disabled ? ' disabled' : '';

        var html = '<div class="cf-phone-field">';
        html += '<div class="cf-phone-country-wrap">';
        html += renderPickerHtml(name, {
            id: idPrefix + 'country_' + name,
            disabled: disabled,
            required: required
        });
        html += '</div>';
        html += '<input type="tel" class="' + escHtml(inputClass) + '" id="' + escHtml(fieldId) + '" name="' + escHtml(name) + '"'
            + ' inputmode="tel" autocomplete="tel-national"'
            + ' placeholder="' + escHtml(placeholder) + '"'
            + ' pattern="[0-9\\s\\-]{6,15}" title="Enter digits only (no country code)"'
            + reqAttr + disAttr + '>';
        html += '</div>';
        return html;
    }

    function closeAllPickers(except) {
        var nodes = document.querySelectorAll('.cf-phone-picker.is-open');
        for (var i = 0; i < nodes.length; i++) {
            if (except && nodes[i] === except) {
                continue;
            }
            nodes[i].classList.remove('is-open');
            var panel = nodes[i].querySelector('.cf-phone-picker-panel');
            var trigger = nodes[i].querySelector('.cf-phone-picker-trigger');
            if (panel) {
                panel.hidden = true;
            }
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        }
    }

    function setPickerValue(picker, item) {
        if (!picker || !item) {
            return;
        }
        var hidden = picker.querySelector('.cf-phone-country-value');
        var trigger = picker.querySelector('.cf-phone-picker-trigger');
        var flagImg = trigger ? trigger.querySelector('.cf-phone-picker-flag') : null;
        var codeEl = trigger ? trigger.querySelector('.cf-phone-picker-code') : null;
        var val = item.getAttribute('data-value') || '';
        var digits = item.getAttribute('data-digits') || '0';
        var code = item.getAttribute('data-code') || '';
        var iso = item.getAttribute('data-iso') || '';
        var flag = item.getAttribute('data-flag') || flagImgUrl(iso);
        if (hidden) {
            hidden.value = val;
            hidden.setAttribute('data-digits', digits);
            hidden.setAttribute('data-code', code);
            hidden.setAttribute('data-iso', iso);
        }
        if (flagImg) {
            flagImg.src = flag;
        }
        if (codeEl) {
            codeEl.textContent = code;
        }
        var items = picker.querySelectorAll('.cf-phone-picker-item');
        for (var i = 0; i < items.length; i++) {
            items[i].classList.toggle('is-selected', items[i] === item);
        }
        var row = picker.closest('.cf-phone-field');
        if (row) {
            syncPhoneDigits(row);
        }
    }

    function syncPhoneDigits(row) {
        var hidden = row.querySelector('.cf-phone-country-value');
        var phone = row.querySelector('.cf-phone-number');
        if (!hidden || !phone) {
            return;
        }
        var digits = parseInt(hidden.getAttribute('data-digits') || '0', 10);
        if (digits > 0) {
            phone.setAttribute('maxlength', String(digits));
            phone.setAttribute('title', 'Enter ' + digits + ' digit phone number');
        } else {
            phone.removeAttribute('maxlength');
            phone.setAttribute('title', 'Enter digits only (no country code)');
        }
    }

    function bindPicker(picker) {
        if (!picker || picker.getAttribute('data-cf-picker-bound') === '1') {
            return;
        }
        picker.setAttribute('data-cf-picker-bound', '1');
        if (picker.getAttribute('data-disabled') === '1') {
            return;
        }

        var trigger = picker.querySelector('.cf-phone-picker-trigger');
        var panel = picker.querySelector('.cf-phone-picker-panel');
        if (!trigger || !panel) {
            return;
        }

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var isOpen = picker.classList.contains('is-open');
            closeAllPickers();
            if (!isOpen) {
                picker.classList.add('is-open');
                panel.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        panel.addEventListener('click', function (e) {
            var item = e.target.closest('.cf-phone-picker-item');
            if (!item) {
                return;
            }
            setPickerValue(picker, item);
            closeAllPickers();
        });
    }

    function bindRowEl(row) {
        if (!row || row.getAttribute('data-cf-phone-bound') === '1') {
            return;
        }
        row.setAttribute('data-cf-phone-bound', '1');

        var picker = row.querySelector('.cf-phone-picker');
        if (picker) {
            bindPicker(picker);
        }

        var phone = row.querySelector('.cf-phone-number');
        if (phone) {
            phone.addEventListener('input', function () {
                phone.value = phone.value.replace(/[^\d\s\-]/g, '');
            });
        }
        syncPhoneDigits(row);
    }

    function init(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var rows = scope.querySelectorAll ? scope.querySelectorAll('.cf-phone-field') : [];
        for (var i = 0; i < rows.length; i++) {
            bindRowEl(rows[i]);
        }
    }

    if (!window.__cfPhonePickerDocBound) {
        window.__cfPhonePickerDocBound = true;
        document.addEventListener('click', function () {
            closeAllPickers();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllPickers();
            }
        });
    }

    window.ContactFormPhone = {
        countries: countries,
        renderFieldHtml: renderFieldHtml,
        renderPickerHtml: renderPickerHtml,
        init: init
    };
}(window));
