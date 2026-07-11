/**
 * Reusable customer address modal (#addCustomerAddressModal).
 * All field lookups are scoped to this.$modal so duplicate IDs on POS (checkout + CRM) do not break state loading.
 */
(function ($, window) {
    'use strict';

    function parseAddrStateValue(stateVal) {
        var raw = String(stateVal || '').trim();
        var parts = raw.split('~');
        return {
            name: parts[0] ? String(parts[0]).trim() : '',
            code: parts[1] ? String(parts[1]).trim() : ''
        };
    }

    function getSelectedStateCode($state) {
        var code = $.trim(String($state.find('option:selected').attr('data-code') || ''));
        if (!code) {
            code = parseAddrStateValue($state.val()).code;
        }
        return code;
    }

    function buildGetStatesUrl(url) {
        var u = String(url || '').trim();
        if (!u) {
            var base = (typeof site !== 'undefined' && site.base_url) ? String(site.base_url) : '';
            if (base && base.slice(-1) !== '/') {
                base += '/';
            }
            u = base + 'customers/getstates';
        }
        return u;
    }

    function refreshSelect2($el) {
        if (typeof $.fn.select2 === 'function') {
            try {
                $el.select2('destroy');
            } catch (e) {}
            $el.select2({
                minimumResultsForSearch: 7,
                width: '100%'
            });
        }
    }
    
    SmaCustomerAddAddressModal._active = null;
    SmaCustomerAddAddressModal._registry = {};
    SmaCustomerAddAddressModal._docBound = false;

    function SmaCustomerAddAddressModal(options) {
        this.cfg = $.extend({
            modalSelector: '#addCustomerAddressModal',
            getStatesUrl: '',
            countryOptionsSelector: '#customer-addr-country-options-html',
            defaultCountry: '',
            defaultState: '',
            onSave: null,
            instanceKey: '',
            saveButtonSelector: '#btnSaveCustomerAddressModal',
            cancelButtonSelector: '#btnCancelCustomerAddressModal, #btnCloseCustomerAddressModal',
            triggerSelector: null
        }, options || {});
        this._countryOptionsHtml = '';
        this.prepareModalElement();
        this.register();
        this.init();
    }

    SmaCustomerAddAddressModal.prototype.register = function () {
        if (this.cfg.instanceKey) {
            SmaCustomerAddAddressModal._registry[this.cfg.instanceKey] = this;
        }
    };

    SmaCustomerAddAddressModal.prototype.$f = function (id) {
        return this.$modal.find('#' + id);
    };

    SmaCustomerAddAddressModal.prototype.prepareModalElement = function () {
        var selector = this.cfg.modalSelector;
        var $all = $(selector);
        if ($all.length > 1) {
            var $visibleBook = $('.addressSection:visible .crm-address-book, .mymodal:visible .crm-address-book').first();
            var $keep = $visibleBook.length ? $visibleBook.find(selector).first() : $();
            if (!$keep.length) {
                $keep = $all.filter(':visible').first();
            }
            if (!$keep.length) {
                $keep = $all.last();
            }
            $all.not($keep).remove();
        }
        this.$modal = $(selector).first();
        if (this.$modal.length && !this.$modal.parent().is('body')) {
            this.$modal.appendTo('body');
        }
        return this.$modal.length > 0;
    };

    SmaCustomerAddAddressModal.prototype.findCountryTemplate = function () {
        var selector = this.cfg.countryOptionsSelector;
        var $src = this.$modal.closest('.crm-address-book').find(selector);
        if (!$src.length) {
            $src = $('.addressSection:visible ' + selector + ', .mymodal:visible ' + selector).first();
        }
        if (!$src.length) {
            $src = $(selector).filter(function () {
                return $(this).children('option').length > 1;
            }).first();
        }
         if (!$src.length) {
            $src = $('#country_name, .country_name').filter(function () {
                return $(this).children('option').length > 1;
            }).first();
        }
        if (!$src.length) {
            $src = $(selector).first();
        }
        return $src;
    };

    SmaCustomerAddAddressModal.prototype.getCountryOptionsHtml = function (forceRefresh) {
        if (forceRefresh) {
            this._countryOptionsHtml = '';
        }
        if (!this._countryOptionsHtml) {
            var $src = this.findCountryTemplate();
            if ($src.is('select')) {
                this._countryOptionsHtml = $src.html();
            } else {
                this._countryOptionsHtml = String($src.val() || $src.text() || '').trim();
            }
        }
        return this._countryOptionsHtml;
    };

    function resolveActiveAddressModal() {
        if (SmaCustomerAddAddressModal._active && SmaCustomerAddAddressModal._active.$modal.length) {
            var $activeModal = SmaCustomerAddAddressModal._active.$modal;
            if ($activeModal.hasClass('in') || $activeModal.is(':visible')) {
                return SmaCustomerAddAddressModal._active;
            }
        }
        var $visibleModal = $('#addCustomerAddressModal').filter('.in, :visible').first();
        if (!$visibleModal.length) {
            $visibleModal = $('body > #addCustomerAddressModal').first();
        }
        if ($visibleModal.length) {
            var key;
            var registry = SmaCustomerAddAddressModal._registry || {};
            for (key in registry) {
                if (registry.hasOwnProperty(key) && registry[key]) {
                    registry[key].$modal = $visibleModal;
                    return registry[key];
                }
            }
        }
        return SmaCustomerAddAddressModal._active || null;
    }

    SmaCustomerAddAddressModal.prototype.bindCountryChange = function () {
        var self = this;
        var $country = this.$f('add_addr_modal_country');
        $country.off('change.smaAddrModalInst').on('change.smaAddrModalInst', function () {
            if (self._resetting) {
                return;
            }
            var country = $(this).val();
            var selectedState = '';
            if (country && country === self.cfg.defaultCountry && self.cfg.defaultState) {
                selectedState = self.cfg.defaultState;
            }
            self.loadStates(country, selectedState);
        });
    };

    SmaCustomerAddAddressModal.prototype.loadStates = function (country, selectedState) {
        var $state = this.$f('add_addr_modal_state');
        var $stateCode = this.$f('add_addr_modal_state_code');
        if (!$state.length) {
            return;
        }
        if (!country || country === 'other') {
            $state.html('<option value="other">Other</option>');
            $stateCode.val('');
            refreshSelect2($state);
            return;
        }
        var statesUrl = buildGetStatesUrl(this.cfg.getStatesUrl);
        if (!statesUrl) {
            return;
        }
        $state.html('<option value="">--Select State--</option>');
        $stateCode.val('');
        refreshSelect2($state);
        $.ajax({
            type: 'get',
            dataType: 'json',
            url: statesUrl,
            data: { country: country },
            success: function (response) {
                if (response && response.data) {
                    $state.html(response.data);
                } else {
                    $state.html('<option value="">--Select State--</option><option value="other">Other</option>');
                }
                if (selectedState) {
                    $state.val(selectedState);
                    if (($state.val() || '') !== selectedState) {
                        var target = String(selectedState).split('~')[0].toLowerCase();
                        $state.find('option').each(function () {
                            var optVal = String($(this).val() || '');
                            var optText = String($(this).text() || '').toLowerCase();
                            if (optVal.toLowerCase().indexOf(target) === 0 || optText.indexOf(target) === 0) {
                                $state.val(optVal);
                                return false;
                            }
                        });
                    }
                }
                $stateCode.val(getSelectedStateCode($state));
                refreshSelect2($state);
            },
            error: function () {
                $state.html('<option value="">--Select State--</option><option value="other">Other</option>');
                $stateCode.val('');
                refreshSelect2($state);
            }
        });
    };

    SmaCustomerAddAddressModal.prototype.validateFields = function (data, rowNum) {
        var suffix = rowNum ? (' for address row ' + rowNum) : '';
        if (data.type !== 'Shipping' && data.type !== 'Billing' && data.type !== 'Site') {
            return { msg: 'Please select address Type (Shipping, Billing or Site)' + suffix + '.', field: 'type' };
        }
        if (!data.address_name) {
            return { msg: 'Please enter Address Name' + suffix + '.', field: 'address_name' };
        }
        if (!data.line1) {
            return { msg: 'Please enter Address 1' + suffix + '.', field: 'line1' };
        }
        if (!data.state) {
            return { msg: 'Please select State' + suffix + '.', field: 'state' };
        }
        return null;
    };

    SmaCustomerAddAddressModal.prototype.readFields = function () {
        var $state = this.$f('add_addr_modal_state');
        var stateVal = $.trim($state.val() || '');
        var stateCode = getSelectedStateCode($state);
        if (!stateCode) {
            stateCode = $.trim(this.$f('add_addr_modal_state_code').val() || '');
        }
        var data = {
            type: $.trim(this.$f('add_addr_modal_type').val() || ''),
            address_name: $.trim(this.$f('add_addr_modal_address_name').val() || ''),
            line1: $.trim(this.$f('add_addr_modal_line1').val() || ''),
            country: $.trim(this.$f('add_addr_modal_country').val() || ''),
            state: stateVal,
            state_code: stateCode,
            city: $.trim(this.$f('add_addr_modal_city').val() || ''),
            postal_code: $.trim(this.$f('add_addr_modal_postal').val() || '')
        };
        if (this.$f('add_addr_modal_id').length) {
            data.id = $.trim(this.$f('add_addr_modal_id').val() || '');
        }
        return data;
    };

    SmaCustomerAddAddressModal.prototype.setTitle = function (title) {
        this.$modal.find('#addCustomerAddressModalLabel').text(title || 'Add Address');
    };

    SmaCustomerAddAddressModal.prototype.reset = function (prefill) {
        this._resetting = true;
        prefill = prefill || {};
        this.$f('add_customer_addr_modal_error').hide().text('');
        var $type = this.$f('add_addr_modal_type');
        $type.val(prefill.type || 'Billing');
        refreshSelect2($type);
        this.$f('add_addr_modal_address_name').val(prefill.address_name || '');
        this.$f('add_addr_modal_line1').val(prefill.line1 || '');
        this.$f('add_addr_modal_city').val(prefill.city || '');
        this.$f('add_addr_modal_postal').val(prefill.postal_code || '');
        this.$f('add_addr_modal_state_code').val(prefill.state_code || '');
        if (this.$f('add_addr_modal_id').length) {
            this.$f('add_addr_modal_id').val(prefill.id || '');
        }
        var $country = this.$f('add_addr_modal_country');
        $country.html(this.getCountryOptionsHtml(true));
        var country = prefill.country || this.cfg.defaultCountry || '';
        if (country) {
            $country.val(country);
        }
        refreshSelect2($country);
        var defaultState = prefill.state || '';
        if (!defaultState && $country.val() && $country.val() === this.cfg.defaultCountry && this.cfg.defaultState) {
            defaultState = this.cfg.defaultState;
        }
        this.loadStates($country.val(), defaultState);
         this._resetting = false;
    };

    SmaCustomerAddAddressModal.prototype.close = function (e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var active = document.activeElement;
        if (active && this.$modal.length && $.contains(this.$modal[0], active)) {
            $(active).blur();
        }
        this.$modal.modal('hide');
    };

    SmaCustomerAddAddressModal.prototype.open = function (prefill, title) {
        this.prepareModalElement();
        if (!this.$modal.length) {
            return;
        }
        SmaCustomerAddAddressModal._active = this;
        if (title) {
            this.setTitle(title);
        }
        this.reset(prefill || {});
        this.bindCountryChange();
        this.$modal.modal({ backdrop: 'static', keyboard: true, show: true });
    };

    SmaCustomerAddAddressModal.prototype.save = function () {
        var data = this.readFields();
        var err = this.validateFields(data);
        var $err = this.$f('add_customer_addr_modal_error');
        $err.hide().text('');
        if (err) {
            $err.text(err.msg).show();
            this.$f(err.field === 'line1' ? 'add_addr_modal_line1' : ('add_addr_modal_' + err.field)).focus();
            return false;
        }
        if (typeof this.cfg.onSave === 'function') {
            this.cfg.onSave(data);
        }
        this.close();
        return true;
    };

    SmaCustomerAddAddressModal.bindDocumentEventsOnce = function () {
        if (SmaCustomerAddAddressModal._docBound) {
            return;
        }
        SmaCustomerAddAddressModal._docBound = true;

        $(document).on('show.bs.modal.smaCustAddrModal', '#addCustomerAddressModal', function () {
            var $modal = $(this);
            $modal.css('z-index', 10600);
            setTimeout(function() {
                var $backdrop = $modal.data('bs.modal') ? $modal.data('bs.modal').$backdrop : null;
                if ($backdrop && $backdrop.length) {
                    $backdrop.css('z-index', 10590).addClass('modal-stack');
                }
            }, 0);
        });

        $(document).on('shown.bs.modal.smaCustAddrModal', '#addCustomerAddressModal', function () {
            var $modal = $(this);
            var $backdrop = $modal.data('bs.modal') ? $modal.data('bs.modal').$backdrop : null;
            if ($backdrop && $backdrop.length) {
                $backdrop.css('z-index', 10590).addClass('modal-stack');
            } else {
                $('.modal-backdrop').not('.modal-stack').last().css('z-index', 10590).addClass('modal-stack');
            }
        });

        $(document).on('click.smaCustAddrModal', '#btnAddCustomerAddressRow', function (e) {
            e.preventDefault();
            var modal = SmaCustomerAddAddressModal._registry.addCustomer;
            if (modal) {
                modal.open();
            }
        });

        $(document).on('click.smaCustAddrModal', '#btnCancelCustomerAddressModal, #btnCloseCustomerAddressModal', function (e) {
            var modal = resolveActiveAddressModal();
            if (modal) {
                modal.close(e);
            }
        });

        $(document).on('click.smaCustAddrModal', '#btnSaveCustomerAddressModal', function (e) {
            e.preventDefault();
            var modal = resolveActiveAddressModal();
            if (modal) {
                modal.save();
            }
        });

        $(document).on('hidden.bs.modal.smaCustAddrModal', '#addCustomerAddressModal', function () {
            var $addrModal = $(this);
            $addrModal.find(':focus').blur();
            if ($('.modal.in').length) {
                $('body').addClass('modal-open');
            }
        });

        $(document).on('change.smaCustAddrModal', '#addCustomerAddressModal #add_addr_modal_country', function () {
            var modal = resolveActiveAddressModal();
            if (!modal || modal._resetting || !$.contains(modal.$modal[0], this)) {
                return;
            }
            var country = $(this).val();
            var selectedState = '';
            if (country && country === modal.cfg.defaultCountry && modal.cfg.defaultState) {
                selectedState = modal.cfg.defaultState;
            }
            modal.loadStates(country, selectedState);
        });

        $(document).on('change.smaCustAddrModal', '#addCustomerAddressModal #add_addr_modal_state', function () {
            var modal = resolveActiveAddressModal();
            if (!modal || modal._resetting || !$.contains(modal.$modal[0], this)) {
                return;
            }
            modal.$f('add_addr_modal_state_code').val(getSelectedStateCode($(this)));
        });
    };

    SmaCustomerAddAddressModal.prototype.init = function () {
        if (!this.$modal.length) {
            return;
        }
        SmaCustomerAddAddressModal.bindDocumentEventsOnce();
        var $country = this.$f('add_addr_modal_country');
        if ($country.length && !$country.children().length) {
            $country.html(this.getCountryOptionsHtml(true));
        }
    };

    SmaCustomerAddAddressModal.parseAddrStateValue = parseAddrStateValue;

    window.SmaCustomerAddAddressModal = SmaCustomerAddAddressModal;
})(jQuery, window);
