// Shared helpers (used inside document.ready and DOMContentLoaded cash-payment block)
function parseAmountFromText(text) {
  var match = String(text || '').replace(/,/g, '').match(/(\d+(?:\.\d+)?)/);
  return match ? parseFloat(match[1]) : 0;
}

function normalizeDenomKey(value) {
  var num = parseFloat(value);
  return isNaN(num) ? '0.00' : num.toFixed(2);
}

function normalizeDenomType(type) {
  var low = String(type || '').toLowerCase();
  if (low === 'coins') {
    return 'coin';
  }
  if (low === 'bills') {
    return 'note';
  }
  if (low === 'coin' || low === 'note') {
    return low;
  }
  return low;
}

function findAvailableDenomination(availableList, currencyValue, currencyType) {
  var key = normalizeDenomKey(currencyValue);
  var t = normalizeDenomType(currencyType);
  return (availableList || []).find(function (it) {
    return normalizeDenomKey(it.currency_value) === key && normalizeDenomType(it.type) === t;
  });
}

function getCoinageInvoiceAmount() {
  var $modal = $('#paymentModal');
  return parseAmountFromText($modal.find('#twt').text())
    || parseAmountFromText($modal.find('#gtotal1').text())
    || parseAmountFromText($('#gtotal').text())
    || parseFloat($modal.find('#amount_1').val()) || parseFloat($('#amount_1').val()) || 0;
}

// Coinage loadData can recalc without coupon; main POS #gtotal is already correct after loadItems
function applyMainPosTotalsToCoinageModal() {
  var mainPayable = parseAmountFromText($('#gtotal').text());
  if (mainPayable <= 0) {
    return;
  }
  gtotal = mainPayable;
  var $modal = $('#paymentModal');
  $modal.find('#gtotal1').text(formatMoney(mainPayable));
  $modal.find('#twt').text(formatMoney(mainPayable));
  var mainDiscount = $('#tds').text();
  if (mainDiscount) {
    $modal.find('#tds1').text(mainDiscount);
  }
  var mainTotal = $('#total').text();
  if (mainTotal) {
    $modal.find('#total1').text(mainTotal);
  }
}

function syncCoinagePaidBySelect(paidByCode) {
  if (!paidByCode) {
    return;
  }
  var $sel = $('#paymentModal #paid_by_1');
  if (!$sel.length) {
    return;
  }
  // If the option doesn't exist yet (custom portal added via settings), inject it dynamically
  if ($sel.find('option[value="' + paidByCode + '"]').length === 0) {
    // Only auto-inject for custom other_ prefixed codes — not unknown legacy codes
    if (String(paidByCode).indexOf('other_') === 0 || String(paidByCode) === 'other') {
      var label = paidByCode.replace(/^other_/, '').replace(/_/g, ' ');
      label = label.charAt(0).toUpperCase() + label.slice(1);
      $sel.append('<option value="' + paidByCode + '" class="coinage-custom-pm">' + label + '</option>');
    } else {
      return; // Unknown code with no matching option — leave dropdown unchanged
    }
  }
  $sel.val(paidByCode);
  if ($('#paid_by_val_1').length) {
    $('#paid_by_val_1').val(paidByCode);
  }
  if ($sel.hasClass('select2-hidden-accessible')) {
    try {
      $sel.select2('val', paidByCode);
    } catch (e) {
      /* select2 v4+ uses val + change only */
    }
    $sel.trigger('change.select2');
  }
  $sel.trigger('change');
}

function renderCoinagePaymentMethods(methods) {
  var $container = $('#coinage-payment-methods');
  if (!$container.length) {
    return;
  }

  var html = '';
  (methods || []).forEach(function (method, index) {
    var id = 'coinage_pm_' + (method.id || index);
    var code = method.code || '';
    var name = method.name || code;
    var iconUrl = (method.icon_url && String(method.icon_url).trim()) ? method.icon_url : '';
    var iconClass = method.icon_class || 'whitebgblueB';

    html += '<div class="col-md-4 col-sm-6 col-xs-6">';
    html += '<div class="radio-div">';
    html += '<input type="radio" id="' + id + '" class="card custom_payment_icon" name="colorRadio" value="' + code + '">';
    html += '<label for="' + id + '" class="payment-methods">';
    html += '<span class="paddingTop ' + iconClass + (iconUrl ? ' coinage-pm-has-logo' : '') + '">';
    if (iconUrl) {
      html += '<img class="icon_image_option coinage-pm-logo" src="' + iconUrl + '" alt="">';
    }
    html += name;
    html += '</span></label></div></div>';
  });

  $container.html(html);

  // Inject custom payment method options into the #paid_by_1 dropdown
  // so syncCoinagePaidBySelect can find and set them when a portal is default
  var $sel = $('#paymentModal #paid_by_1');
  if ($sel.length) {
    // Remove previously injected custom options to avoid duplicates
    $sel.find('option.coinage-custom-pm').remove();
    (methods || []).forEach(function (method) {
      var code = method.code || '';
      var name = method.name || code;
      if (code && (String(code).indexOf('other_') === 0 || String(code) === 'other')) {
        if ($sel.find('option[value="' + code + '"]').length === 0) {
          $sel.append('<option value="' + code + '" class="coinage-custom-pm">' + name + '</option>');
        }
      }
    });
  }
}

function applyCoinageDepositUiState(isDeposit) {
  if (isDeposit) {
    window.depositModeActive = true;
    setTimeout(function () {
      $("#yourDivId").show().css('display', 'block !important');
      $("div#payment-box").addClass('deposit-hide').attr('style', 'display: none !important');
      $("#denomination-containerr").hide().css('display', 'none !important');
    }, 500);
    return;
  }
  window.depositModeActive = false;
  $("#denomination-containerr").show();
  $("#yourDivId").hide();
  $("#amount-section-container").hide();
  $("#clearAll").show();
}

/** Select rail + Paying By: deposit first, then server preferred_code, then DB is_default, then cash. */
function selectCoinagePaymentRail(paidByCode) {
  if (!paidByCode) {
    return false;
  }
  var $icon = $('#paymentModal input.custom_payment_icon[value="' + paidByCode + '"]');
  if (!$icon.length) {
    return false;
  }
  applyCoinageDepositUiState(paidByCode === 'deposit');
  $icon.prop('checked', true).trigger('change');
  return true;
}

function initCoinagePaymentSelectionAfterLoad() {
  var selectedCode = null;
  var methods = window.coinagePaymentMethodsLoaded || [];
  var customerId = $('#poscustomer').val();

  if (customerId) {
    $.ajax({
      type: 'GET',
      url: site.base_url + 'customers/getdeposit/' + customerId,
      dataType: 'json',
      async: false,
      success: function (result) {
        if (result.deposit && result.deposit > 0) {
          selectedCode = 'deposit';
        }
      }
    });
  }

  if (!selectedCode && window.coinagePreferredCode) {
    selectedCode = window.coinagePreferredCode;
  }

  if (!selectedCode) {
    for (var i = 0; i < methods.length; i++) {
      if (methods[i].is_default == 1 && methods[i].code) {
        selectedCode = methods[i].code;
        break;
      }
    }
  }

  if (!selectedCode) {
    selectedCode = 'cash';
  }

  if (!selectCoinagePaymentRail(selectedCode)) {
    selectCoinagePaymentRail('cash');
  } else if (selectedCode === 'award_point' && typeof setCustomerAwardPoint === 'function') {
    var billAmt = getCoinageInvoiceAmount() || parseFloat($('#amount_1').val()) || 0;
    if (billAmt > 0) {
      setCustomerAwardPoint(billAmt, 'award_point');
    }
  }
}

function loadCoinagePaymentMethods(done) {
  var customerId = $('#poscustomer').val() || '';
  $.ajax({
    type: 'GET',
    url: site.base_url + 'pos/get_coinage_payment_methods',
    data: { customer_id: customerId },
    dataType: 'json',
    success: function (response) {
      if (response && response.status === 'success' && response.methods && response.methods.length) {
        window.coinagePaymentMethodsLoaded = response.methods;
        window.coinagePreferredCode = response.preferred_code || null;
        renderCoinagePaymentMethods(response.methods);
      }
      if (typeof done === 'function') {
        done();
      }
    },
    error: function () {
      if (typeof done === 'function') {
        done();
      }
    }
  });
}

window.cashExtraPayments = window.cashExtraPayments || [];

function isCoinageCashPaymentSelected() {
  var raw = String($('#paymentModal input[name="colorRadio"]:checked').val() || '').toLowerCase().trim();
  return raw === 'cash';
}

function toggleCashExtraPaySlot(isCash) {
  var $slot = $('#cash-extra-pay-slot');
  if (!$slot.length) {
    return;
  }
  if (isCash) {
    $slot.addClass('cash-extra-pay-visible').show();
    $('#paymentModal #nc-extra-pay-grid-host').empty();
    $('#paymentModal #amnts').removeClass('has-nc-extra-grid');
    renderExtraPayGridTo('#cash-extra-pay-grid-body', '#cash-extra-pay-grid-wrap');
  } else {
    $slot.removeClass('cash-extra-pay-visible').hide();
    closeCashExtraPayPopup();
    renderCoinageExtraPayGrids();
  }
}

function buildNcExtraPayGridRowsHtml(list) {
  var html = '';
  list.forEach(function (row, idx) {
    html += '<tr class="cash-extra-pay-row" data-idx="' + idx + '" title="Click to view details">';
    html += '<td class="cash-grid-col-amount">' + (typeof formatMoney === 'function' ? formatMoney(row.amount) : row.amount) + '</td>';
    html += '<td class="cash-grid-col-paidby">' + getCashExtraPayLabel(row.paidBy) + '</td>';
    html += '<td class="cash-grid-col-action"><button type="button" class="btn btn-xs btn-danger cash-extra-pay-remove" data-idx="' + idx + '">&times;</button></td>';
    html += '</tr>';
  });
  return html;
}

function renderNcExtraPayInMultiPayment() {
  var list = window.cashExtraPayments || [];
  var $host = $('#paymentModal #nc-extra-pay-grid-host');
  var $amnts = $('#paymentModal #amnts');
  var $template = $('#coinage-extra-pay-side');

  if (!$host.length || isCoinageCashPaymentSelected()) {
    if ($host.length) {
      $host.empty();
    }
    if ($amnts.length) {
      $amnts.removeClass('has-nc-extra-grid');
    }
    return;
  }

  if (!list.length) {
    $host.empty();
    $amnts.removeClass('has-nc-extra-grid');
    return;
  }

  // var title = $template.find('.cash-extra-pay-grid-title').first().text() || 'Add More Payments';
  var headHtml = $template.find('thead').html() || '';
  var rowsHtml = buildNcExtraPayGridRowsHtml(list);

  var html = '<div class="coinage-nc-extra-grid coinage-extra-pay-visible">';
  // html += '<div class="cash-extra-pay-grid-title">' + title + '</div>';
  html += '<table class="table table-bordered table-condensed cash-extra-pay-grid">';
  html += '<thead>' + headHtml + '</thead>';
  html += '<tbody>' + rowsHtml + '</tbody>';
  html += '</table></div>';

  $host.html(html);
  $amnts.addClass('has-nc-extra-grid');
  $('#coinage-extra-pay-side-grid-body').html(rowsHtml);
}

function renderExtraPayGridTo($bodySel, $wrapSel) {
  var list = window.cashExtraPayments || [];
  var $body = $($bodySel);
  var $wrap = $($wrapSel);
  if (!$body.length) {
    return;
  }
  if (!list.length) {
    $body.empty();
    $wrap.addClass('is-hidden').removeClass('coinage-extra-pay-visible').hide();
    return;
  }
  var html = '';
  list.forEach(function (row, idx) {
    html += '<tr class="cash-extra-pay-row" data-idx="' + idx + '" title="Click to view details">';
    html += '<td class="cash-grid-col-amount">' + (typeof formatMoney === 'function' ? formatMoney(row.amount) : row.amount) + '</td>';
    html += '<td class="cash-grid-col-paidby">' + getCashExtraPayLabel(row.paidBy) + '</td>';
    html += '<td class="cash-grid-col-action"><button type="button" class="btn btn-xs btn-danger cash-extra-pay-remove" data-idx="' + idx + '">&times;</button></td>';
    html += '</tr>';
  });
  $body.html(html);
  $wrap.removeClass('is-hidden').addClass('coinage-extra-pay-visible');
  $wrap.attr('style', 'display:block !important; visibility:visible !important; opacity:1 !important;');
}

function renderCoinageExtraPayGrids() {
  renderExtraPayGridTo('#cash-extra-pay-grid-body', '#cash-extra-pay-grid-wrap');
  renderNcExtraPayInMultiPayment();
}

function renderCashExtraPayGrid() {
  renderCoinageExtraPayGrids();
}

function resetCashExtraPayments() {
  window.cashExtraPayments = [];
  renderCashExtraPayGrid();
  refreshCoinagePaymentTotals();
}

function getCashExtraPayLabel(code) {
  var label = code;
  var methods = window.coinagePaymentMethodsLoaded || [];
  methods.some(function (m) {
    if (m.code === code) {
      label = m.name || code;
      return true;
    }
    return false;
  });
  var $opt = $('#paymentModal #paid_by_1 option[value="' + code + '"]');
  if ($opt.length) {
    label = $opt.text() || label;
  }
  return label;
}

/** Primary Paying By from dropdown (not payment rail tab). */
function getCoinagePrimaryPaidBy() {
  var $sel = $('#paymentModal #paid_by_1');
  var val = '';
  if ($sel.length) {
    val = $sel.val();
    if (!val && $sel.hasClass('select2-hidden-accessible')) {
      try {
        var s2val = $sel.select2('val');
        if (s2val) {
          val = s2val;
        }
      } catch (e) {}
    }
  }
  return String(val || '').trim();
}

function finalizeCoinagePaymentHiddenFieldsForSubmit(balance) {
  var extras = getCashExtraPaymentsList();
  if (isCoinageCashPaymentSelected()) {
    if (extras.length > 0) {
      var collected = parseFloat($('#depositedAmount').text().replace(/,/g, '')) || 0;
      syncCashExtraPaymentsToHiddenFields(collected, balance);
    }
    return;
  }
  if (extras.length > 0) {
    syncNcExtraPaymentsToHiddenFields(balance);
    return;
  }
  var primaryPaidBy = getCoinagePrimaryPaidBy();
  if (primaryPaidBy && $('#paid_by_val_1').length) {
    $('#paid_by_val_1').val(primaryPaidBy);
  }
  if ($('#amount_val_1').length) {
    $('#amount_val_1').val(parseFloat($('#amount_1').val()) || 0);
  }
}

function normalizeExtraPayCode(code) {
  return String(code || '').toLowerCase().trim();
}

function formatExtraPayMoney(val) {
  if (val === null || val === undefined || val === '') {
    return '-';
  }
  return typeof formatMoney === 'function' ? formatMoney(val) : val;
}

function fetchExtraPayGiftCardMeta(billAmt) {
  var customerId = $('#poscustomer').val();
  return $.ajax({
    type: 'get',
    url: site.base_url + 'pos/searchGiftcardByCustomer',
    data: { customer_id: customerId, bill_amt: billAmt || 0 },
    dataType: 'json'
  });
}

function fetchExtraPayDepositMeta(billAmt) {
  var customerId = $('#poscustomer').val();
  return $.ajax({
    type: 'get',
    url: site.base_url + 'pos/searchDepositByCustomer',
    data: { customer_id: customerId, bill_amt: billAmt || 0 },
    dataType: 'json'
  });
}

function resetCashPopupMeta() {
  $('#cash_popup_meta').hide().removeData('giftMeta').removeData('depositMeta');
  $('#cash_popup_meta_body').empty();
}

function updateCashPopupMetaFields() {
  var code = normalizeExtraPayCode($('#cash_popup_paid_by').val());
  resetCashPopupMeta();
  if (code === 'cc' || code === 'dc') {
    $('#cash_popup_pcc').show();
    return;
  }
  $('#cash_popup_pcc').hide();
  $('#cash_popup_trans, #cash_popup_other').val('');
  if (code === 'gift_card') {
    fetchExtraPayGiftCardMeta(parseFloat($('#cash_popup_amount').val()) || 0).done(function (data) {
      if (data && data.card_no && parseFloat(data.balance) > 0) {
        $('#cash_popup_meta_body').html(
          '<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>'
        );
        $('#cash_popup_meta').data('giftMeta', data).show();
      } else {
        $('#cash_popup_meta_body').html('<small class="red">Giftcard not found for this customer</small>');
        $('#cash_popup_meta').show();
      }
    });
  } else if (code === 'deposit') {
    fetchExtraPayDepositMeta(parseFloat($('#cash_popup_amount').val()) || 0).done(function (data) {
      if (data && parseFloat(data.balanceamt) > 0) {
        $('#cash_popup_meta_body').html(
          '<small>Value: ' + data.value + ' - Balance: ' + data.balanceamt + '</small>'
        );
        $('#cash_popup_meta').data('depositMeta', data).show();
      } else {
        $('#cash_popup_meta_body').html('<small class="red">Deposit not found for this customer</small>');
        $('#cash_popup_meta').show();
      }
    });
  }
}

function buildExtraPayDetailsHtml(row) {
  var code = normalizeExtraPayCode(row.paidBy);
  var html = '<dl class="cash-extra-pay-details-list">';
  html += '<dt>Paying By</dt><dd>' + getCashExtraPayLabel(row.paidBy) + '</dd>';
  html += '<dt>Amount</dt><dd>' + formatExtraPayMoney(row.amount) + '</dd>';
  if (code === 'gift_card') {
    if (row.gift_card_no) {
      html += '<dt>Card Number</dt><dd>' + row.gift_card_no + '</dd>';
    }
    if (row.gift_card_value != null && row.gift_card_value !== '') {
      html += '<dt>Value</dt><dd>' + formatExtraPayMoney(row.gift_card_value) + '</dd>';
    }
    if (row.gift_card_balance != null && row.gift_card_balance !== '') {
      html += '<dt>Balance</dt><dd>' + formatExtraPayMoney(row.gift_card_balance) + '</dd>';
    }
  } else if (code === 'deposit') {
    if (row.deposit_value != null && row.deposit_value !== '') {
      html += '<dt>Deposit Amount</dt><dd>' + formatExtraPayMoney(row.deposit_value) + '</dd>';
    }
    if (row.deposit_balance != null && row.deposit_balance !== '') {
      html += '<dt>Balance</dt><dd>' + formatExtraPayMoney(row.deposit_balance) + '</dd>';
    }
  } else if (code === 'cc' || code === 'dc') {
    if (row.cc_transac_no) {
      html += '<dt>Transaction No.</dt><dd>' + row.cc_transac_no + '</dd>';
    }
    if (row.other_tran_mode) {
      html += '<dt>Other</dt><dd>' + row.other_tran_mode + '</dd>';
    }
  } else {
    if (row.cc_transac_no) {
      html += '<dt>Transaction No.</dt><dd>' + row.cc_transac_no + '</dd>';
    }
    if (row.other_tran_no) {
      html += '<dt>Reference No.</dt><dd>' + row.other_tran_no + '</dd>';
    }
    if (row.other_tran_mode) {
      html += '<dt>Mode</dt><dd>' + row.other_tran_mode + '</dd>';
    }
    if (row.cheque_no) {
      html += '<dt>Cheque No.</dt><dd>' + row.cheque_no + '</dd>';
    }
  }
  if (row.payment_note) {
    html += '<dt>Note</dt><dd>' + row.payment_note + '</dd>';
  }
  html += '</dl>';
  return html;
}

function openExtraPayDetailsPopup(idx) {
  var row = (window.cashExtraPayments || [])[idx];
  if (!row) {
    return;
  }
  var code = normalizeExtraPayCode(row.paidBy);
  var renderDetails = function () {
    var $popup = $('#cashExtraPayDetailsPopup');
    if (!$popup.length) {
      return;
    }
    if (!$popup.parent().is('body')) {
      $popup.appendTo('body');
    }
    clearCashPopupBlockers();
    $(document).off('focusin.bs.modal');
    $('#cashExtraPayDetailsBody').html(buildExtraPayDetailsHtml(row));
    $('body').addClass('cash-extra-pay-details-open');
    $popup.stop(true, true).css({ display: 'flex', opacity: 1, visibility: 'visible' });
  };
  if (code === 'gift_card' && !row.gift_card_no) {
    fetchExtraPayGiftCardMeta(row.amount).done(function (data) {
      if (data && data.card_no) {
        row.gift_card_no = data.card_no;
        row.gift_card_value = data.value;
        row.gift_card_balance = data.balance;
      }
    }).always(function () {
      renderDetails();
    });
    return;
  }
  if (code === 'deposit' && (row.deposit_balance == null || row.deposit_balance === '')) {
    fetchExtraPayDepositMeta(row.amount).done(function (data) {
      if (data && parseFloat(data.balanceamt) > 0) {
        row.deposit_value = data.value;
        row.deposit_balance = data.balanceamt;
      }
    }).always(function () {
      renderDetails();
    });
    return;
  }
  renderDetails();
}

function closeExtraPayDetailsPopup() {
  $('body').removeClass('cash-extra-pay-details-open');
  $('#cashExtraPayDetailsPopup').fadeOut(120, function () {
    $(this).css({ display: 'none', visibility: '' });
  });
}

function applyExtraPayRowMetaToHidden(slot, row) {
  if ($('#paying_gift_card_no_val_' + slot).length) {
    $('#paying_gift_card_no_val_' + slot).val(row.gift_card_no || '');
  }
}

function populateCashPopupPaidByOptions(excludeCodes) {
  excludeCodes = excludeCodes || [];
  var excludeLower = excludeCodes.map(function (c) { return String(c).toLowerCase(); });
  var $src = $('#paymentModal #paid_by_1');
  var $dst = $('#cash_popup_paid_by');
  if (!$dst.length) {
    return;
  }
  $dst.empty();
  if ($src.length) {
    $src.find('option').each(function () {
      var code = String($(this).val() || '').trim();
      if (code && excludeLower.indexOf(code.toLowerCase()) === -1) {
        $dst.append($(this).clone());
      }
    });
  }
  var methods = window.coinagePaymentMethodsLoaded || [];
  methods.forEach(function (m) {
    var code = m.code || '';
    if (code && excludeLower.indexOf(String(code).toLowerCase()) === -1 && $dst.find('option[value="' + code + '"]').length === 0) {
      $dst.append('<option value="' + code + '">' + (m.name || code) + '</option>');
    }
  });
  if ($dst.find('option').length) {
    $dst.val($dst.find('option').first().val());
  }
  ensureCashPopupNativeSelect();
}

function ensureCashPopupNativeSelect() {
  var $sel = $('#cash_popup_paid_by');
  if (!$sel.length) {
    return;
  }
  if ($sel.hasClass('select2-hidden-accessible')) {
    try {
      $sel.select2('destroy');
    } catch (e) {}
  }
  $('#s2id_cash_popup_paid_by').remove();
  $sel
    .removeClass('select2-offscreen')
    .show()
    .css({
      display: 'block',
      visibility: 'visible',
      opacity: 1,
      position: 'relative',
      width: '100%',
      pointerEvents: 'auto'
    });
}

function mountCashExtraPayPopupsToBody() {
  ['#cashExtraPayPopup', '#cashExtraPayDetailsPopup'].forEach(function (sel) {
    var $el = $(sel);
    if ($el.length && !$el.parent().is('body')) {
      $el.appendTo('body');
    }
  });
}

function clearCashPopupBlockers() {
  $('#select2-drop-mask, .select2-drop-mask').remove();
  $('.select2-drop').remove();
}

function openCoinageExtraPayPopup() {
  mountCashExtraPayPopupsToBody();
  clearCashPopupBlockers();
  $(document).off('focusin.bs.modal');
  ensureCashPopupNativeSelect();
  var isCash = isCoinageCashPaymentSelected();
  populateCashPopupPaidByOptions(isCash ? ['cash'] : []);
  var invoiceAmount = getCoinageInvoiceAmount();
  var allocated = 0;
  if (isCash) {
    allocated = parseFloat($('#depositedAmount').text().replace(/,/g, '')) || 0;
  } else {
    allocated = parseFloat($('#amount_1').val()) || 0;
  }
  var extrasTotal = (window.cashExtraPayments || []).reduce(function (s, p) { return s + p.amount; }, 0);
  var remaining = parseFloat((invoiceAmount - allocated - extrasTotal).toFixed(4));
  $('#cash_popup_amount').val(remaining > 0 ? remaining : '').prop('readonly', false).prop('disabled', false);
  $('#cash_popup_trans, #cash_popup_other').val('').prop('readonly', false).prop('disabled', false);
  $('#cash_popup_pcc').hide();
  resetCashPopupMeta();
  $('#cash_popup_paid_by').prop('disabled', false).trigger('change');
  $('body').addClass('cash-extra-pay-open');
  setTimeout(function () {
    ensureCashPopupNativeSelect();
  }, 0);
  $('#cashExtraPayPopup').stop(true, true).css('display', 'flex').hide().fadeIn(120);
  setTimeout(function () {
    $('#cash_popup_amount').trigger('focus').trigger('click');
  }, 180);
}

function openCashExtraPayPopup() {
  openCoinageExtraPayPopup();
}

function closeCashExtraPayPopup() {
  $('#cashExtraPayPopup').fadeOut(120, function () {
    $(this).css('display', 'none');
  });
  $('body').removeClass('cash-extra-pay-open');
  clearCashPopupBlockers();
}

function getCashExtraPaymentsList() {
  return (window.cashExtraPayments || []).slice();
}

function getCashExtraPayTotal() {
  return getCashExtraPaymentsList().reduce(function (sum, row) {
    return sum + (parseFloat(row.amount) || 0);
  }, 0);
}

function hasCoinageReturnDenominations(returnDenoms) {
  if (!returnDenoms || typeof returnDenoms !== 'object') {
    return false;
  }
  return Object.keys(returnDenoms).some(function (typeKey) {
    var bucket = returnDenoms[typeKey];
    if (!bucket || typeof bucket !== 'object') {
      return false;
    }
    return Object.keys(bucket).some(function (denomKey) {
      return parseInt(bucket[denomKey], 10) > 0;
    });
  });
}

function refreshCoinagePaymentTotals() {
  if (localStorage.getItem('modeFlag') === 'return') {
    return;
  }

  var invoiceAmount = getCoinageInvoiceAmount();
  var extraTotal = getCashExtraPayTotal();
  var tendered = 0;

  if (isCoinageCashPaymentSelected()) {
    var collected = parseFloat($('#depositedAmount').text().replace(/,/g, '')) || 0;
    tendered = collected + extraTotal;
  } else {
    tendered = (parseFloat($('#amount_1').val()) || 0) + extraTotal;
  }

  var signedBalance = parseFloat((tendered - invoiceAmount).toFixed(4));
  var displayBalance = Math.abs(signedBalance);
  var returnAmount = Math.max(0, tendered - invoiceAmount);
  var fmt = typeof formatNumber === 'function' ? formatNumber : function (v) { return v; };

  if ($('#total_paying').length) {
    $('#total_paying').text(fmt(tendered));
  }
  if ($('#balance').length) {
    $('#balance').text(fmt(displayBalance));
  }
  if (isCoinageCashPaymentSelected()) {
    if ($('#returnAmount').length) {
      $('#returnAmount').text(fmt(returnAmount));
    }
    var $returnMatButton = $('#returnAmt');
    if ($returnMatButton.length) {
      var enableReturn = tendered > invoiceAmount;
      $returnMatButton.prop('disabled', !enableReturn).css({
        opacity: enableReturn ? '1' : '0.6',
        'pointer-events': enableReturn ? 'auto' : 'none'
      });
    }
  }
  if ($('#balance_amount_1').length) {
    $('#balance_amount_1').val(signedBalance);
  }
  if (typeof window.toggleCashButtons === 'function') {
    window.toggleCashButtons();
  }
}

function syncCashExtraPaymentsToHiddenFields(collectedAmount, balance) {
  var extras = getCashExtraPaymentsList();
  for (var c = 2; c <= 5; c++) {
    $('#amount_val_' + c).val('');
    $('#paid_by_val_' + c).val('');
    $('#cc_transac_no_val' + c).val('');
    $('#cheque_no_val_' + c).val('');
    $('#other_tran_no_val' + c).val('');
    $('#other_tran_mode_val' + c).val('');
    $('#payment_note_val_' + c).val('');
    if ($('#paying_gift_card_no_val_' + c).length) {
      $('#paying_gift_card_no_val_' + c).val('');
    }
  }
  var slot = collectedAmount > 0 ? 2 : 1;
  var lastSlot = 0;
  extras.forEach(function (row) {
    if (slot > 5) {
      return;
    }
    $('#amount_val_' + slot).val(row.amount);
    $('#paid_by_val_' + slot).val(row.paidBy);
    if ($('#cc_transac_no_val' + slot).length) {
      $('#cc_transac_no_val' + slot).val(row.cc_transac_no || '');
    }
    if ($('#other_tran_no_val' + slot).length) {
      $('#other_tran_no_val' + slot).val(row.other_tran_no || '');
      $('#other_tran_mode_val' + slot).val(row.other_tran_mode || '');
    }
    applyExtraPayRowMetaToHidden(slot, row);
    lastSlot = slot;
    slot++;
  });
  if (collectedAmount > 0) {
    $('#paid_by_val_1').val('cash');
    $('#amount_val_1').val(collectedAmount);
    lastSlot = lastSlot || 1;
    if ($('#payment_method').length) {
      $('#payment_method').val('cash');
    }
  } else if (extras.length === 1) {
    $('#paid_by_val_1').val(extras[0].paidBy);
    $('#amount_val_1').val(extras[0].amount);
    if ($('#cc_transac_no_val1').length) {
      $('#cc_transac_no_val1').val(extras[0].cc_transac_no || '');
    }
    applyExtraPayRowMetaToHidden(1, extras[0]);
    if ($('#payment_method').length) {
      $('#payment_method').val(extras[0].paidBy);
    }
    lastSlot = 1;
  }
  for (var bc = 1; bc <= 5; bc++) {
    if ($('#balance_amount_' + bc).length) {
      $('#balance_amount_' + bc).val('');
    }
  }
  if (lastSlot && balance > 0 && $('#balance_amount_' + lastSlot).length) {
    $('#balance_amount_' + lastSlot).val(balance);
  } else if ($('#balance_amount_1').length) {
    $('#balance_amount_1').val(balance);
  }
}

function syncNcExtraPaymentsToHiddenFields(balance) {
  var extras = getCashExtraPaymentsList();
  var primaryAmount = parseFloat($('#amount_1').val()) || 0;
  var primaryPaidBy = getCoinagePrimaryPaidBy();
  if (!primaryPaidBy) {
    primaryPaidBy = String($('#paymentModal input[name="colorRadio"]:checked').val() || '').trim();
  }

  for (var c = 1; c <= 5; c++) {
    $('#amount_val_' + c).val('');
    $('#paid_by_val_' + c).val('');
    $('#cc_transac_no_val' + c).val('');
    $('#cheque_no_val_' + c).val('');
    $('#other_tran_no_val' + c).val('');
    $('#other_tran_mode_val' + c).val('');
    $('#payment_note_val_' + c).val('');
    if ($('#paying_gift_card_no_val_' + c).length) {
      $('#paying_gift_card_no_val_' + c).val('');
    }
  }

  $('#amount_val_1').val(primaryAmount);
  $('#paid_by_val_1').val(primaryPaidBy);
  if ($('#cc_transac_no_val1').length) {
    $('#cc_transac_no_val1').val($('#cc_transac_no_1').val() || '');
  }
  if ($('#cheque_no_val_1').length) {
    $('#cheque_no_val_1').val($('#cheque_no_1').val() || '');
  }
  if ($('#other_tran_no_val1').length) {
    $('#other_tran_no_val1').val($('#other_tran_no_1').val() || '');
    $('#other_tran_mode_val1').val($('#other_tran_mode_1').val() || '');
  }
  if ($('#payment_note_val_1').length) {
    $('#payment_note_val_1').val($('#payment_note_1').val() || '');
  }
  if ($('#payment_method').length) {
    $('#payment_method').val($('#paymentModal input[name="colorRadio"]:checked').val() || primaryPaidBy);
  }

  var slot = 2;
  var lastSlot = extras.length ? 1 : 0;
  extras.forEach(function (row) {
    if (slot > 5) {
      return;
    }
    $('#amount_val_' + slot).val(row.amount);
    $('#paid_by_val_' + slot).val(String(row.paidBy || '').trim());
    if ($('#cc_transac_no_val' + slot).length) {
      $('#cc_transac_no_val' + slot).val(row.cc_transac_no || '');
    }
    if ($('#other_tran_no_val' + slot).length) {
      $('#other_tran_no_val' + slot).val(row.other_tran_no || '');
      $('#other_tran_mode_val' + slot).val(row.other_tran_mode || '');
    }
    applyExtraPayRowMetaToHidden(slot, row);
    lastSlot = slot;
    slot++;
  });
  if (!extras.length) {
    lastSlot = 1;
  }
  for (var bc = 1; bc <= 5; bc++) {
    if ($('#balance_amount_' + bc).length) {
      $('#balance_amount_' + bc).val('');
    }
  }
  if (lastSlot && balance > 0 && $('#balance_amount_' + lastSlot).length) {
    $('#balance_amount_' + lastSlot).val(balance);
  } else if ($('#balance_amount_1').length) {
    $('#balance_amount_1').val(balance);
  }
}

function resetCoinageModalUiState() {
  window.depositModeActive = false;
  $("#payment-box").removeClass('deposit-hide').removeAttr('style');
  $("#denomination-containerr").show().removeAttr('style');
  $("#yourDivId").hide().removeAttr('style');
  $("#amount-section-container").hide();
  $("#clearAll").show();
  $('#paymentModal #nc-extra-pay-grid-host').empty();
  $('#paymentModal #amnts').removeClass('has-nc-extra-grid');
  closeCashExtraPayPopup();
  resetCashExtraPayments();
  toggleCashExtraPaySlot(isCoinageCashPaymentSelected());
}

$(document).ready(function () {
  mountCashExtraPayPopupsToBody();
  $('<style>div#payment-box.deposit-hide { display: none !important; }</style>').appendTo('head');

  $(document).on('click', '#cashShowExtraPayBtn', function () {
    if (!isCoinageCashPaymentSelected()) {
      return;
    }
    if ((window.cashExtraPayments || []).length >= 4) {
      $("#returnToastMessage").text("Maximum extra payments reached.");
      $("#returnOverlay").show();
      $("#returnToast").show();
      return;
    }
    openCashExtraPayPopup();
  });

  $(document).on('click', '#ncShowExtraPayBtn', function () {
    if (isCoinageCashPaymentSelected()) {
      return;
    }
    if ((window.cashExtraPayments || []).length >= 4) {
      $("#returnToastMessage").text("Maximum extra payments reached.");
      $("#returnOverlay").show();
      $("#returnToast").show();
      return;
    }
    openCoinageExtraPayPopup();
  });

  $(document).on('click', '#cashPopupSubmit', function () {
    var paidBy = ($('#cash_popup_paid_by').val() || '').trim();
    var amount = parseFloat($('#cash_popup_amount').val()) || 0;
    if (!paidBy || (isCoinageCashPaymentSelected() && paidBy.toLowerCase() === 'cash')) {
      $("#returnToastMessage").text("Please select a payment method.");
      $("#returnOverlay").show();
      $("#returnToast").show();
      return;
    }
    if (amount <= 0) {
      $("#returnToastMessage").text("Please enter a valid amount.");
      $("#returnOverlay").show();
      $("#returnToast").show();
      $('#cash_popup_amount').focus();
      return;
    }
    if (paidBy === 'CC' || paidBy === 'DC') {
      if (!String($('#cash_popup_trans').val() || '').trim()) {
        $("#returnToastMessage").text("Please Enter Card Transaction No.");
        $("#returnOverlay").show();
        $("#returnToast").show();
        $('#cash_popup_trans').focus();
        return;
      }
    }
    var paidByCode = normalizeExtraPayCode(paidBy);
    var giftMeta = $('#cash_popup_meta').data('giftMeta');
    var depositMeta = $('#cash_popup_meta').data('depositMeta');
    if (paidByCode === 'gift_card') {
      if (!giftMeta || !giftMeta.card_no) {
        $("#returnToastMessage").text("Giftcard not found for this customer.");
        $("#returnOverlay").show();
        $("#returnToast").show();
        return;
      }
    }
    if (paidByCode === 'deposit') {
      if (!depositMeta || !(parseFloat(depositMeta.balanceamt) > 0)) {
        $("#returnToastMessage").text("Deposit not found for this customer.");
        $("#returnOverlay").show();
        $("#returnToast").show();
        return;
      }
    }
    var entry = {
      paidBy: String(paidBy || '').trim(),
      amount: amount,
      cc_transac_no: $('#cash_popup_trans').val() || '',
      other_tran_no: '',
      other_tran_mode: $('#cash_popup_other').val() || '',
      payment_note: ''
    };
    if (paidByCode === 'gift_card' && giftMeta) {
      entry.gift_card_no = giftMeta.card_no;
      entry.gift_card_value = giftMeta.value;
      entry.gift_card_balance = giftMeta.balance;
    }
    if (paidByCode === 'deposit' && depositMeta) {
      entry.deposit_value = depositMeta.value;
      entry.deposit_balance = depositMeta.balanceamt;
    }
    window.cashExtraPayments.push(entry);
    renderCashExtraPayGrid();
    closeCashExtraPayPopup();
    refreshCoinagePaymentTotals();
    if (typeof window.toggleCashButtons === 'function') {
      window.toggleCashButtons();
    }
  });

  $(document).on('click', '.cash-extra-pay-remove', function (e) {
    e.stopPropagation();
    var idx = parseInt($(this).data('idx'), 10);
    if (!isNaN(idx)) {
      window.cashExtraPayments.splice(idx, 1);
      renderCashExtraPayGrid();
      refreshCoinagePaymentTotals();
      if (typeof window.toggleCashButtons === 'function') {
        window.toggleCashButtons();
      }
    }
  });

  $(document).on('click', '#paymentModal #cash-extra-pay-grid tbody td, #paymentModal #nc-extra-pay-grid-host .cash-extra-pay-grid tbody td', function (e) {
    if ($(e.target).closest('.cash-extra-pay-remove').length) {
      return;
    }
    var $row = $(this).closest('tr.cash-extra-pay-row');
    if (!$row.length) {
      return;
    }
    var idx = parseInt($row.attr('data-idx'), 10);
    if (!isNaN(idx)) {
      openExtraPayDetailsPopup(idx);
    }
  });

  $(document).on('click', '.cash-extra-pay-details-close', function () {
    closeExtraPayDetailsPopup();
  });

  $(document).on('click', '#cashExtraPayDetailsPopup', function (e) {
    if (e.target === this) {
      closeExtraPayDetailsPopup();
    }
  });

  $(document).on('click', '#cashExtraPayDetailsPopup .cash-extra-pay-popup-card', function (e) {
    e.stopPropagation();
  });

  $(document).on('click', '.cash-extra-pay-popup-close', function () {
    closeCashExtraPayPopup();
  });

  $(document).on('click', '#cashExtraPayPopup', function (e) {
    if (e.target === this) {
      closeCashExtraPayPopup();
    }
  });

  $(document).on('click', '#cashExtraPayPopup .cash-extra-pay-popup-card', function (e) {
    e.stopPropagation();
  });

  $(document).on('mousedown touchstart', '.cash-extra-pay-popup-card .cash-popup-field', function (e) {
    if ($(this).is('select') || $(this).hasClass('cash-popup-native-select')) {
      return;
    }
    e.stopPropagation();
  });

  $(document).on('mousedown touchstart', '#cash_popup_paid_by', function () {
    ensureCashPopupNativeSelect();
  });

  $(document).on('change', '#cash_popup_paid_by', function () {
    updateCashPopupMetaFields();
  });

  $(document).on('keyup change', '#cash_popup_amount', function () {
    var code = normalizeExtraPayCode($('#cash_popup_paid_by').val());
    if (code === 'gift_card' || code === 'deposit') {
      updateCashPopupMetaFields();
    }
  });

  $(document).on('keyup change', '#paymentModal #amount_1', function () {
    if (!isCoinageCashPaymentSelected()) {
      refreshCoinagePaymentTotals();
    }
  });

  $("#paymentModal").on("shown.bs.modal", function () {
    mountCashExtraPayPopupsToBody();
    ensureCashPopupNativeSelect();
    resetCoinageModalUiState();
    loadCoinagePaymentMethods(function () {
      fetchDenomination();
    });
  });

  // fetch denomination 
  function fetchDenomination() {
    $.ajax({
      type: "GET",
      url: site.base_url + "pos/get_denominations",
      dataType: "json",
      success: function (data) {
        Alldenominations = (data || []).map(function (item) {
          item.currency_value = normalizeDenomKey(item.currency_value);
          item.type = normalizeDenomType(item.type);
          return item;
        });
        // Store a pristine copy for availability checks in Return mode
        localStorage.setItem('AvailableDenominations', JSON.stringify(Alldenominations));
        localStorage.setItem('Alldenominations', JSON.stringify(Alldenominations));  // working copy for Collected mode UI state

        renderDenominations(Alldenominations);
        loadData();
        applyMainPosTotalsToCoinageModal();
        initialTotalPayable();
        updateTotalPayableSection();
        toggleCashButtons();

        // Select default/deposit payment method and show denomination UI (after methods + denominations exist)
        initCoinagePaymentSelectionAfterLoad();
        if (typeof window.coinageSyncPaymentUi === 'function') {
          window.coinageSyncPaymentUi();
        }

      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
      },
    });
  }

  // Right side total payable section
  function initialTotalPayable() {

    var invoiceAmount = getCoinageInvoiceAmount();
    var Amount = "0";
    $("#totalPayableAmount").text(formatNumber(invoiceAmount));
    $("#returnAmount").text(formatNumber(Amount));
    $("#depositedAmount").text(formatNumber(Amount));
    // Also set the amount_1 field for consistency
    $("#amount_1").val(invoiceAmount);
    $(".payment-box").hide();
    $("#yourDivId").hide();
  }

  // load selected items on left side
  function loadData() {

    var scanValue = $('#scan_item_qr').val();

    var per_cartunitview = ($('#per_cartunitview').val() == 1) ? true : false;
    var per_cartpriceedit = ($('#per_cartpriceedit').val() == 1) ? true : false;
    var permission_owner = ($('#permission_owner').val() == 1) ? true : false;
    var permission_admin = ($('#permission_admin').val() == 1) ? true : false;
    var add_tax_in_cart_unit_price = ($('#add_tax_in_cart_unit_price').val() == 1) ? true : false;
    var add_discount_in_cart_unit_price = ($('#add_discount_in_cart_unit_price').val() == 1) ? true : false;
    var changeQtyAsPerPrice = ($('#change_qty_as_per_user_price').val() == 1) ? true : false;

    if (localStorage.getItem('positems')) {
      total = 0;
      invoice_total_withtax = 0;      //For Apply Offers
      invoice_total_withouttax = 0;   //For Apply Offers 
      offerCartItems = {};        //For Apply Offers 
      count = 1;
      an = 1;
      product_tax = 0;
      invoice_tax = 0;
      product_discount = 0;
      order_discount = 0;
      total_discount = 0;
      poscartitems = null;
      item_cart_qty = [];


      $("#getsdta tbody").empty();

      if (java_applet == 1) {
        order_data = "";
        bill_data = "";
        bill_data += chr(27) + chr(69) + "\r" + chr(27) + "\x61" + "\x31\r";
        bill_data += site.settings.site_name + "\n\n";
        order_data = bill_data;
        bill_data += lang.bill + "\n";
        order_data += lang.order + "\n";
        bill_data += $('#select2-chosen-1').text() + "\n\n";
        bill_data += " \x1B\x45\x0A\r\n ";
        order_data += $('#select2-chosen-1').text() + "\n\n";
        order_data += " \x1B\x45\x0A\r\n ";
        bill_data += "\x1B\x61\x30";
        order_data += "\x1B\x61\x30";
      } else {
        $("#order_span").empty();
        $("#bill_span").empty();
        var styles = '<style>table, th, td { border-collapse:collapse; border-bottom: 1px solid #CCC; } .no-border { border: 0; } .bold { font-weight: bold; }</style>';
        // var pos_head1 = '<span style="text-align:center;"><h3>' + site.settings.site_name + '</h3><h4>';
        //var pos_head2 = '</h4><h5> Token No.: ' + tokan_no + ' </h5><h5>' + $('#select2-chosen-1').text() + '<br>' + hrld() + '</h5></span>';
        //$("#order_span").prepend(styles + pos_head1 + ' Order ' + pos_head2);

        var pos_head1 = '<div style="text-align:center;"><strong>' + site.settings.site_name + '</strong><br/>';
        if (site.settings.pos_type == 'restaurant') {
          var pos_head2 = ' Table No: ' + localStorage.getItem('table_name') + '</div>';
          $("#bill_span").prepend(styles + pos_head1 + pos_head2);

        } else {
          var pos_head2 = ' Token No.: ' + tokan_no + ' ' + ',' + hrld() + '</div>';
          $("#bill_span").prepend(styles + pos_head1 + ' Bill ' + pos_head2);

        }
        $("#order_span").prepend(styles + pos_head1 + pos_head2);

        // $("#bill_span").prepend(styles + pos_head1 + ' Bill ' + pos_head2);
        $("#order-table").empty();
        $("#bill-table").empty();
      }

      positems = JSON.parse(localStorage.getItem('positems'));

      console.log('=========positems=============');
      console.log(positems);

      var posItemsCount = Object.keys(positems).length;

      var poscartitems = {};
      /*********************Code For Offers Add Free Items*******************/
      //         console.log('Status addfreeitems: '+localStorage.getItem('addfreeitems'));


      if (localStorage.getItem('addfreeitems') == 'false') {
        var temp_item_id = '';
        //When do not have to add free items in cart but in localstorage have free items then remove from localstorage and cart

        $.each(positems, function () {

          if (this.note == 'Free Items' || this.is_free) {

            var objitemid = '';
            var objitemid2 = '';

            if (this.row.option) {
              objitemid = this.item_id + this.row.option;
              objitemid2 = this.item_id + '_' + this.row.option;
            } else if (this.category) {
              objitemid = this.item_id + this.category;
              objitemid2 = this.item_id + '_' + this.category;
            } else {
              objitemid = this.item_id;
              objitemid2 = this.item_id;
            }

            delete positems['free_item_' + objitemid2];
            localStorage.removeItem('free_item_' + objitemid2);

            delete positems[objitemid];
            localStorage.removeItem(objitemid);
          } else {

            temp_item_id = this.id;  //(this.row.option) ?  this.item_id + this.row.option :  this.item_id; // Add new Item to card Not Working
            poscartitems[temp_item_id] = this;
          }
        });
      } else {
        poscartitems = positems;

        if (localStorage.getItem('posfreeitems')) {
          var freepositems = JSON.parse(localStorage.getItem('posfreeitems'));
          jQuery.extend(poscartitems, freepositems); // Extend cart veriables with free items.
          localStorage.removeItem('posfreeitems');
        }
      }

      /**********************************************************************/

      if (pos_settings.item_order == 1) {
        sortedItems = _.sortBy(poscartitems, function (o) {
          return [parseInt(o.category), parseInt(o.order)];
        });
      } else if (site.settings.item_addition == 1) {
        sortedItems = _.sortBy(poscartitems, function (o) {
          return [parseInt(o.order)];
        })
      } else {
        sortedItems = poscartitems;
      }

      //        console.log('--------------sortedItems---------------------');
      //        console.log(sortedItems);

      //Get the total cart unit items
      var cart_item_unit_count = 0;

      $.each(sortedItems, function () {
        cart_item_unit_count += parseFloat(this.row.qty);
      });

      var category = 0, print_cate = false;
      // var itn = parseInt(Object.keys(sortedItems).length);
      $("#bill-table").append('<tr><th>  Item Code  </th><th>Item Name</th><th>Qty</th><th>Price</th><th style="text-align:right;">Total</th></tr>');
      var previous_row_no = '';

      $('#payment').attr('disabled', false);

      //        console.log('--------------sortedItems---------------------');
      //        console.log(sortedItems);

      $.each(sortedItems, function () {

        var item = this;
        var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
        division_array.push(item.row.divisionid);
        var hsn_code = '';
        if (item.row.hsn_code) {
          hsn_code = item.row.hsn_code;
        }
        // positems[item_id] = item;

        item.order = item.order ? item.order : new Date().getTime();
        var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_desc = item.row.description, item_option = item.row.option, item_code = item.row.code, item_article_code = item.row.article_code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
        var product_unit = item.row.unit;
        var item_weight = 0;
        if (item.row.storage_type == 'loose') {
          var base_quantity = formatDecimal((parseFloat(item.row.base_quantity) * parseFloat(item.row.qty)), 3);
        } else {
          var base_quantity = formatDecimal((parseFloat(item.row.qty)), 3);
        }
        var tax_rate = item.row.tax_rate;
        var mrp = item.row.mrp;
        var discount_on_mrp = item.row.discount_on_mrp;
        var pr_var_discount = item.row.pr_var_discount;
        var customer_group_discount = item.row.customer_group_discount; // flag for customer discount apply

        // Category Tax
        var category_tax = item.category_tax;
        var fixtax = item.fixtax;

        var warehouse_price_group_id = item.row.warehouse_price_group_id;
        if (!warehouse_price_group_id) {
          var unit_price = parseFloat(item.row.real_unit_price) > 0 ? item.row.real_unit_price : item.row.unit_price;
          if (scanValue) {
            var unit_price = item.row.mrp;
          }
        } else {
          var unit_price = item.row.unit_price;
          // var unit_price = item.row.mrp;
          // var unit_price = item_option == "0" ? item.row.mrp : item.row.unit_price;
        }
        // var customerName = $('#customer_name').val();
        // alert('customerName')
        // alert(customerName)
        // let inputString = "Swarup(8633683837)";
        // let parts = $('#customer_name').val().split('(');
        // let name = parts[0];

        //var base_quantity = (parseFloat(item.row.unit_quantity) * parseFloat(item.row.qty));
        // var unit_price = item.row.real_unit_price;
        var manualedit = (item.row.manualedit) ? item.row.manualedit : ''; // 05-09-19

        item_cart_qty[item.item_id] = parseFloat(item_cart_qty[item.item_id]) > 0 ? (item_cart_qty[item.item_id] + item.row.qty) : item.row.qty;

        var cf1 = item.row.cf1;
        var cf2 = item.row.cf2;
        var cf3 = item.row.cf3;
        var cf4 = item.row.cf4;
        var cf5 = item.row.cf5;
        var cf6 = item.row.cf6;

        var batchno = item.row.batch_number ? item.row.batch_number : '';

        if (item.row.fup != 1 && product_unit != item.row.base_unit) {
          $.each(item.units, function () {
            if (this.id == product_unit) {
              base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 6);
              unit_price = formatDecimal((parseFloat(item.row.base_unit_price) * (unitToBaseQty(1, this))), 6);
            }
          });
        }
        var sel_opt = '';
        var option_input_hidden = '<input name="product_option[]" type="hidden" class="roption" value="' + item.row.option + '">';

        if (site.settings.attributes == 1) {
          if (item.options !== false) {
            $.each(item.options, function () {

              var this_options = this;

              //If Select multiple options
              if (jQuery.type(item.row.option) == 'string') {
                var optionArr = item.row.option.split(",");
                $.each(optionArr, function (k, opt) {

                  if (this_options.id == opt) {
                    if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                      if (manualedit == '') {
                        // item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.price), 6);
                        item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.mrp), 6);
                        unit_price = item_price;
                        item_aqty = this_options.quantity;
                      }
                    }
                    if (k) {
                      sel_opt = sel_opt + ',' + this_options.name;
                    } else {
                      sel_opt = this_options.name;
                    }
                  }
                });
              } else {
                if (this_options.id == item.row.option) {
                  if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                    if (manualedit == '') {
                      // item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.price)), 6);
                      item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.mrp)), 6);
                      unit_price = item_price;
                      item_aqty = this_options.quantity;
                    }
                  }
                  sel_opt = this_options.name;
                }
              }
            });
          }
        }


        // Order level discount distributed in each items as item discount.
        var posdiscount = localStorage.getItem('posdiscount');

        if (posdiscount) {
          //Order Level Discount Calculations               
          var ods = posdiscount;
          var item_discount_on_mrp = 0;
          var item_order_discount = 0;
          // var mrp = unit_price;

          // calculating unit_price after apply discount on mrp 
          // start
          var ds = discount_on_mrp ? String(discount_on_mrp) : '0';
          if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
              item_discount_on_mrp = formatDecimal((parseFloat(((mrp) * parseFloat(pds[0])) / 100)), 6);
            } else {
              item_discount_on_mrp = formatDecimal(ds, 6);
            }
          } else {
            item_discount_on_mrp = formatDecimal(ds, 6);
          }
          unit_price = formatDecimal(mrp - item_discount_on_mrp, 6);
          // end


          if (ods.indexOf("%") !== -1) {
            var pds = ods.split("%");
            if (!isNaN(pds[0])) {
              item_order_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
              item_ds = ods;
            } else {
              item_order_discount = formatDecimal(parseFloat(ods), 6);
              item_ds = item_order_discount;
            }
          } else {
            //If Discount in amount then divided equal in each items unit equally.
            item_order_discount = formatDecimal((parseFloat(ods) / cart_item_unit_count), 6);
            item_ds = item_order_discount;
          }
          order_discount += formatDecimal((item_order_discount * item_qty), 6);
          // unit_price = formatDecimal(parseFloat(mrp) - parseFloat(item_discount_on_mrp), 6);
          unit_price = mrp;
          item_discount = item_discount_on_mrp;

          if (offer_categories = localStorage.getItem('offer_on_category')) {
            var offer_on_category = offer_categories.split(',');
            if (offer_on_category.indexOf(item.category) != -1) {
              //alert('found');
            } else {
              //alert('not found');
              if (offer_on_category.indexOf(item.sub_category) != -1) {  //alert('sub found');	
              } else {
                item_discount = 0;
                item_ds = 0;
                //alert('not sub found');
              }
            }
          }
          //Set Order Discount Value null.
          //$('#posdiscount').val('');
          $('#offer_on_category').val(localStorage.getItem('offer_on_category'));
          $('#offer_category').val(localStorage.getItem('offer_category'));
          $('#offer_description').val(localStorage.getItem('offer_description'));

          // alert('offer_category: '+localStorage.getItem('offer_category'));
          // alert('offer_description: '+localStorage.getItem('offer_description'));
          localStorage.setItem('applyOffers', true);
        } else {
          //Item Level Discount Calculations  
          // var ds = item_ds ? String(item_ds) : '0';

          if (manualedit == "1") {
            var flat_discount = mrp - unit_price;  // Calculate flat discount
            item_discount = flat_discount.toFixed(0);
            discount_on_mrp = item_discount;
          } else {
            if (customer_group_discount == '1') { // override customer discount to discount on mrp if customer discount applied
              discount_on_mrp = item_ds;
            }
            var ds = discount_on_mrp ? String(discount_on_mrp) : '0';
            if (ds.indexOf("%") !== -1) {
              var pds = ds.split("%");
              if (!isNaN(pds[0])) {
                item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
              } else {
                item_discount = formatDecimal(ds, 6);
              }
            } else {
              item_discount = formatDecimal(ds, 6);
            }
          }

        }

        if (item.row.editpopup == 'edititems') {

          unit_price = manualedit == "1" ? item.row.unit_price : mrp;

          if (manualedit == "1") {
            var flat_discount = mrp - unit_price;  // Calculate flat discount
            item_discount = flat_discount.toFixed(0);
          } else {
            if (customer_group_discount == '1') { // override customer discount to discount on mrp if customer discount applied
              discount_on_mrp = item.row.discount_on_mrp;
            }
            // Discount on mrp
            if (discount_on_mrp) {
              // item_ds = discount_on_mrp;
              //Item Level Discount Calculations  
              var ds = discount_on_mrp ? String(discount_on_mrp) : '0';

              if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                  item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
                } else {
                  item_discount = formatDecimal(ds, 6);
                }
              } else {
                item_discount = formatDecimal(ds, 6);
              }
            }
          }

        }

        if (posdiscount) {
          product_discount += formatDecimal((item_discount_on_mrp * item_qty), 6);
        } else {
          product_discount += formatDecimal((item_discount * item_qty), 6);
        }

        // item.row.discount = formatDecimal(item_discount, 4);
        if (changeQtyAsPerPrice) {
          var cart_user_price = parseFloat(item.row.user_price) > 0 ? parseFloat(item.row.user_price) : 0;
        }

        // unit_price = formatDecimal(unit_price - item_discount, 6);
        if (manualedit == '') {
          unit_price = formatDecimal(unit_price - item_discount, 6);
        }
        if (posdiscount) { // for calculate unit_price after apply order level discount
          unit_price = formatDecimal(unit_price - item_order_discount, 6);
        }

        // var pr_tax = item.tax_rate;
        // var pr_tax_val = 0;
        // if (site.settings.tax1 == 1) {
        //     if (pr_tax !== false) {
        //         if (pr_tax.type == 1) {
        //             if (item_tax_method == '0') {
        //                 pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 6);
        //                 pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
        //             } else {
        //                 pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / 100, 6);
        //                 pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
        //             }
        //         } else if (pr_tax.type == 2) {
        //             pr_tax_val = formatDecimal(pr_tax.rate);
        //             pr_tax_rate = pr_tax.rate;
        //         }
        //         product_tax += pr_tax_val * item_qty;
        //     }
        // }//end if.
        // if(item.row.editpopup == 'edititems') {
        //     unit_price = mrp;
        //     // var unit_price = $('#selling').val();
        // }
        var pr_tax = item.tax_rate;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (site.settings.tax1 == 1) {
          if (pr_tax !== false) {
            if (pr_tax.type == 1) {
              if (item_tax_method == '0') {
                if (fixtax) {
                  var exptax = fixtax.split("~");
                  pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1]))), 4);
                  pr_tax_rate = formatDecimal(exptax[1]) + '%';
                  tax_rate = exptax[0];
                } else {
                  if (category_tax) {
                    $.each(category_tax, function (k, categorytax) {
                      var uptocheck = categorytax.upto;
                      if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }
                      } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }

                      }
                    });
                  }

                }
              } else {
                if (fixtax) {
                  var exptax = fixtax.split("~");
                  pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / 100), 4);
                  pr_tax_rate = formatDecimal(exptax[1]) + '%';
                  tax_rate = exptax[0];
                } else {
                  if (category_tax) {
                    $.each(category_tax, function (k, categorytax) {
                      var uptocheck = categorytax.upto;
                      if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }

                      } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }

                      }
                    });
                  }

                }
              }

            } else if (pr_tax.type == 2) {
              if (fixtax) {
                var exptax = fixtax.split("~");
                pr_tax_val = parseFloat(exptax[1]);
                pr_tax_rate = exptax[1];
                tax_rate = exptax[0];
              } else {
                if (category_tax) {
                  $.each(category_tax, function (k, categorytax) {
                    var uptocheck = categorytax.upto;
                    if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                      if (uptocheck) {
                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        }
                      } else {
                        var taxvalue = categorytax.taxratevalue;
                        var exptax = taxvalue.split("~");
                        pr_tax_val = formatDecimal(exptax[1]);
                        pr_tax_rate = formatDecimal(exptax[1]);
                        tax_rate = exptax[0];
                      }

                    } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {

                      if (uptocheck) {
                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        }
                      } else {
                        var taxvalue = categorytax.taxratevalue;
                        var exptax = taxvalue.split("~");
                        pr_tax_val = formatDecimal(exptax[1]);
                        pr_tax_rate = formatDecimal(exptax[1]);
                        tax_rate = exptax[0];
                      }

                    }
                  });
                }

              }
            }
            product_tax += pr_tax_val * item_qty;
          }
        }
        // if(item.row.editpopup == 'edititems') {
        //     unit_price = item_tax_method == 0 ? formatDecimal((parseFloat(unit_price)), 4) : formatDecimal((parseFloat(unit_price)), 4);
        // }
        if (posdiscount) { // for adding order level discount in price again
          unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_order_discount), 6);
        }
        item_price = item_tax_method == 0 ? formatDecimal((unit_price - pr_tax_val), 6) : formatDecimal(unit_price, 6);
        // unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_discount), 6);
        if (manualedit == '') {
          unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_discount), 6);
        }
        /********************************************/
        if (item_tax_method == 0) {
          offerCartItems[item.row.id] = JSON.parse('{"item_id":"' + item.row.id + '", "price_with_tax":"' + unit_price + '", "price_without_tax":"' + (parseFloat(unit_price) - parseFloat(pr_tax_val)) + '", "qty":"' + item_qty + '", "category":"' + item.row.category_id + '", "discount":"' + item.row.discount + '"}');
        } else {
          offerCartItems[item.row.id] = JSON.parse('{"item_id":"' + item.row.id + '", "price_with_tax":"' + (parseFloat(unit_price) + parseFloat(pr_tax_val)) + '", "price_without_tax":"' + unit_price + '", "qty":"' + item_qty + '", "category":"' + item.row.category_id + '", "discount":"' + item.row.discount + '"}');
        }
        /************************************************/

        if (pos_settings.item_order == 1 && category != item.row.category_id) {
          category = item.row.category_id;
          print_cate = true;
          var newTh = $('<tr id="category_' + category + '"></tr>');
          newTh.html('<td colspan="100%"><strong>' + item.row.category_name + '</strong></td>');
          newTh.prependTo("#getsdta");
        } else {
          print_cate = false;
        }

        var row_no = (new Date).getTime();
        var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');

        item_weight = (item.row.unit_weight) ? (parseFloat(item_qty) * parseFloat(item.row.unit_weight)) : '';

        var tr_html = '<td><input name="row[]" type="hidden" id="item_' + item_id + '" class="roid" value="' + row_no + '">';
        tr_html += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">';
        tr_html += '<input name="hsn_code[]" type="hidden" class="rid hsn_code" value="' + hsn_code + '">';
        tr_html += '<input name="product_type[]" type="hidden" class="rtype product_type"  value="' + item_type + '">';
        tr_html += '<input name="product_code[]" type="hidden" class="rcode product_code" value="' + item_code + '">';
        tr_html += '<input name="article_code[]" type="hidden" class="rcode article_code" value="' + item_article_code + '">';
        tr_html += '<input name="product_name[]" type="hidden" class="rname product_name" value="' + item_name + '">';
        tr_html += '<input name="productids[]" type="hidden" class="productids" value="' + item.row.id + '">';
        tr_html += '<input name="manualedit[]"   type="hidden" class="rmanualedit" value="' + manualedit + '">';
        tr_html += '<input name="item_weight[]"  type="hidden" class="rweight" value="' + item_weight + '">';
        tr_html += '<input name="return_ref_no[]" type="hidden" class="return_ref_no" value="' + item.row.return_ref_no + '">';
        tr_html += '<input  name="customerRefNo" type="hidden" class="customerRefNo" value=" ' + scanValue + '">';

        // tr_html += '<input  name="customerRefNo" type="hidden" class="customerRefNo" value=" ' + scanValue +'">';

        //Options Input Hiddens 
        tr_html += option_input_hidden;

        tr_html += '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ((item.note == '') ? item.note : ': ' + item.note) + ')' : '') + '</span>';

        //Hide Item Edit Options if Items is free
        if ((item.note == 'Free Items')) {
          var item_disabled = ' readonly="readonly" ';
          tr_html += '</td>';
        } else {
          var item_disabled = '';
          // tr_html += '<i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
        }

        //tr_html += '<i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
        item.note = (item.note == undefined) ? '' : item.note;
        tr_html += '<input name="item_note[]" type="hidden" class="rid" value="' + item.note + '">';
        tr_html += '<input name="cf1[]" type="hidden" class="rid" value="' + cf1 + '">';
        tr_html += '<input name="cf2[]" type="hidden" class="rid" value="' + cf2 + '">';
        tr_html += '<input name="cf3[]" type="hidden" class="rid" value="' + cf3 + '">';
        tr_html += '<input name="cf4[]" type="hidden" class="rid" value="' + cf4 + '">';
        tr_html += '<input name="cf5[]" type="hidden" class="rid" value="' + cf5 + '">';
        tr_html += '<input name="cf6[]" type="hidden" class="rid" value="' + cf6 + '">';
        tr_html += '<input name="batch_number[]" type="hidden" class="rid" value="' + batchno + '">';

        tr_html += '<td class="text-right">';

        if (site.settings.product_serial == 1) {
          tr_html += '<input class="form-control input-sm rserial" name="serial[]" type="hidden" id="serial_' + row_no + '" value="' + item_serial + '">';
        }
        if (site.settings.product_discount == 1) {
          tr_html += '<input class="form-control input-sm rdiscount product_discount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '">';
        }

        if (site.settings.tax1 == 1) {
          pr_tax.id = (tax_rate > 0) ? tax_rate : pr_tax.id;
          tr_html += '<input class="form-control input-sm text-right rproduct_tax product_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><input type="hidden" class="sproduct_tax" id="sproduct_tax_' + row_no + '" value="' + formatMoney(pr_tax_val * item_qty) + '">';
        }
        item_desc = item_desc == undefined ? '' : item_desc;
        tr_html += '<input class="rdescription" name="item_description[]" type="hidden" id="description_' + row_no + '" value="' + item_desc + '">';
        tr_html += '<input class="rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '">';
        tr_html += '<input class="ruprice unitprices" name="unit_price[]" type="hidden" value="' + unit_price + '">';
        tr_html += '<input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '">';
        tr_html += '<input class="rmrp mrp" name="mrp[]" type="hidden" value="' + mrp + '">';
        tr_html += '<input class="rmrpdiscount mrpdiscount" name="discount_on_mrp[]" type="hidden" value="' + discount_on_mrp + '">';
        tr_html += '<input class="rtaxrate rtaxrate" name="taxrate[]" type="hidden" value="' + tax_rate + '">';
        tr_html += '<input class="reditpopup reditpopup" name="editpopup[]" type="hidden" value="' + item.row.editpopup + '">';


        // var cart_item_price =  (add_tax_in_cart_unit_price == true) ? (parseFloat(item_price) + parseFloat(pr_tax_val)) : parseFloat(item_price);
        //alert(cart_item_price);

        var cart_item_price = 0;

        if (add_tax_in_cart_unit_price == true && add_discount_in_cart_unit_price == true) {

          cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val) + parseFloat(item_discount); //item_ds
        } else if (add_tax_in_cart_unit_price == true) {
          cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val);
        } else if (add_discount_in_cart_unit_price == true) {
          cart_item_price = parseFloat(item_price) + parseFloat(item_discount);
        } else {
          cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val);
        }

        if (permission_admin || permission_owner || per_cartpriceedit) {
          if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose') {
            tr_html += '<input type="text" maxlength="10" name="item_user_price[]" id="suserprice_' + row_no + '" value="' + ((cart_user_price > 0) ? parseInt(cart_user_price) : parseInt(cart_item_price)) + '"  class="form-control input-sm kb-pad text-center userprice" />';
            tr_html += (cart_user_price > 0) ? '<small class="text-left">' + parseInt(cart_item_price) + '/qty</small>' : '';
            tr_html += '<input type="hidden" name="item_price[]" id="sprice_' + row_no + '" value="' + (formatMoney(cart_item_price)) + '" />';
          } else {
            tr_html += '<span class="item_price userprice" data-id="' + row_no + '" id="sprice_' + row_no + '" ' + item_disabled + '>' + formatMoney(cart_item_price) + '</span>';
            // tr_html += '<input type="text" maxlength="10" name="item_price[]" id="sprice_' + row_no + '" value="' + (formatMoney(cart_item_price)) + '"  ' + item_disabled + '  class="form-control input-sm kb-pad text-center item_price userprice" />';
          }
        } else {
          tr_html += formatMoney(parseFloat(cart_item_price)) + '<input type="hidden"  maxlength="10" name="item_price[]" id="sprice_' + row_no + '" value="' + formatMoney(cart_item_price) + '" onchange="return false" class="form-control input-sm kb-pad text-center  item_price userprice" />';
        }
        tr_html += '</td>';

        // tr_html += '<td>';
        // tr_html += '<table style="border: none;"><tr ><td style="border-bottom: 0px !important;"> ';
        // if (oldProductSearch(item_id)) {
        //     // tr_html += '<button onclick="qtyMinus(\'' + item_id + '\')" type="button" style="border: 0; background: none;" ><i class="fa fa-minus"></i> </button>';
        // }
        // tr_html += ' &nbsp;  </td>';
        tr_html += '<td style="border-bottom: 0px !important; text-align: center;">';


        tr_html += '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">';
        tr_html += '<input name="product_base_quantity[]" maxlength="6" type="hidden" class="rbase_quantity product_base_quantity" value="' + base_quantity + '">';


        if (permission_admin || permission_owner || per_cartpriceedit) {

          var qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(item_aqty, 0) : 1000;

          if (item.row.type == 'combo') {
            var cmax = 1000, cimax = '';
            $.each(combo_items, function () {
              cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
              cmax = (cimax > cmax) ? cmax : cimax;
            });
            qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax, 0) : 1000;
          }//end if.

          if (item.row.type == 'Bundle') {
            var cmax = 1000, cimax = '';
            $.each(combo_items, function () {
              cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
              cmax = (cimax > cmax) ? cmax : cimax;
            });
            qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax, 0) : 1000;
          }//end if.

          if (item.row.storage_type == 'packed') {
            var qotp = '', selected = '';
            for (var q = 1; q <= (qmax ? qmax : 1); q++) {
              selected = '';
              if (formatDecimal(item_qty, 0) == q) {
                selected = ' selected="selected" ';
              }
              qotp += '<option ' + selected + '>' + q + '</option>';
            }//end for
            // tr_html += '<select class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" >' + qotp + '</select>';
            tr_html += '<span class="returnquantity rquantity" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '">' + item_qty + '</span>', style = "width: 43px";
          } else {
            if (changeQtyAsPerPrice == true && cart_user_price > 0) {
              tr_html += formatDecimal(item_qty, 3) + '<input style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + ' type="hidden" value="' + formatDecimal(item_qty, 3) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
            } else {
              tr_html += '<input style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + ' type="text"    value="' + formatDecimal(item_qty, 3) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
            }
          }

        } else {
          tr_html += '<input readonly="readonly" style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + '  type="text" value="' + item_qty + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
        }
        // tr_html += ' </td><td style="border-bottom: 0px !important;">';
        // // tr_html += '&nbsp;  <button type="button" onclick="qtyPlus(\'' + item_id + '\')"  style="border: 0; background: none; bottom:7px!important; padding:4px;"> <i class="fa fa-plus"></i> </button> ';
        // tr_html += '</td></table>';
        // tr_html += '</td>';

        var item_sale_unit = (item_name == 'Gift Card') ? 'pcs' : '';
        if (item.row.sale_unit) {
          $.each(item.units, function () {
            if (this.id == item.row.sale_unit) {
              item_sale_unit = this.code;
            }
          });
        }
        //Show/Hide Cart Unit
        // if(permission_admin || permission_owner || per_cartunitview){
        tr_html += '<td class="text-center"><small>' + item_sale_unit + '</small></td>';
        //}            
        //tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';

        //Hide Item Edit Options if Items is free
        if ((item.note == 'Free Items')) {
          tr_html += '<td class="text-center" colspan="2" style="color:green;">Offer Free Item</td>';
        } else {
          if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose' && cart_user_price > 0) {
            tr_html += '<td class="text-right"><span class="text-right ssubtotal returntotal returnsubtotal" id="subtotal_' + row_no + '">' + formatMoney(cart_user_price) + '</span></td>';
            tr_html += '<input class="returntotal returnsubtotal"  type="hidden" value="' + formatMoney(cart_user_price) + '">';

          } else {
            tr_html += '<input class="returntotal returnsubtotal"  type="hidden" value="' + formatMoney(parseFloat(cart_item_price) * parseFloat(item_qty)) + '">';
            tr_html += '<td class="text-right" style="font-size: 13px;"><span class="text-right ssubtotal returntotal returnsubtotal" id="subtotal_' + row_no + '">' + formatMoney(parseFloat(cart_item_price) * parseFloat(item_qty)) + '</span></td>';
          }
          // tr_html += '<td class="text-center"><i class="fa fa-times tip pointer posdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
        }

        newTr.html(tr_html);
        if (pos_settings.item_order == 1) {
          //newTr.prependTo("#getsdta");
          $('#getsdta').find('#category_' + category).after(newTr);
        } else if (pos_settings.item_order == 2) { // This is the new else if block for adding the "conduit"
          if (previous_row_no == '') {
            newTr.prependTo("#getsdta");
          } else {
            $('#getsdta').find('#row_' + previous_row_no).after(newTr);
          }
        } else {
          if (previous_row_no == '') {
            newTr.prependTo("#getsdta");
          } else {
            $('#getsdta').find('#row_' + previous_row_no).before(newTr);
          }
        }
        previous_row_no = row_no;

        invoice_total_withtax += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 6);
        invoice_total_withouttax += formatDecimal((parseFloat(item_price) * parseFloat(item_qty)), 6);

        if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose' && cart_user_price > 0) {
          total += formatDecimal(cart_user_price, 6);
        } else {
          total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 6);
        }
        item_qtys = Math.abs(item_qty);
        count += parseFloat(item_qtys);
        an++;

        if (item_type == 'standard' && item.options !== false) {

          $.each(item.options, function () {
            if (this.id == item_option && (base_quantity > this.quantity || item_cart_qty[item.item_id] > this.quantity)) {
              $('#row_' + row_no).addClass('danger');
              if (site.settings.overselling != 1) {
                $('#payment').attr('disabled', true);
              }
            }
          });
        } else if (item_type == 'standard' && (base_quantity > item_aqty || item_cart_qty[item.item_id] > item_aqty)) {
          $('#row_' + row_no).addClass('danger');
          if (site.settings.overselling != 1) {
            $('#payment').attr('disabled', false);
          }
        } else if (item_type == 'combo') {
          if (combo_items === false) {
            $('#row_' + row_no).addClass('danger');
            if (site.settings.overselling != 1) {
              $('#payment').attr('disabled', true);
            }
          } else {
            $.each(combo_items, function () {
              if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                $('#row_' + row_no).addClass('danger');
                if (site.settings.overselling != 1) {
                  $('#payment').attr('disabled', true);
                }
              }
            });
          }
        } else if (item_type == 'Bundle') {
          if (combo_items === false) {
            $('#row_' + row_no).addClass('danger');
            if (site.settings.overselling != 1) {
              $('#payment').attr('disabled', true);
            }
          } else {
            $.each(combo_items, function () {
              if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                $('#row_' + row_no).addClass('danger');
                if (site.settings.overselling != 1) {
                  $('#payment').attr('disabled', true);
                }
              }
            });
          }
        }


        if (java_applet == 1) {
          bill_data += "#" + (an - 1) + " " + item_name + "\n";
          bill_data += printLine(item_qty + " x " + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + ": " + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)))) + "\n";
          order_data += printLine("#" + (an - 1) + " " + item_name + ":" + formatDecimal(item_qty)) + item.row.unit_lable + "\n";
        } else {
          if (pos_settings.item_order == 1 && print_cate) {
            var bprTh = $('<tr></tr>');
            bprTh.html('<td colspan="100%" class="no-border"><strong>' + item.row.category_name + '</strong></td>');
            var oprTh = $('<tr></tr>');
            oprTh.html('<td colspan="100%" class="no-border"><strong>' + item.row.category_name + '</strong></td>');
            $("#order-table").append(oprTh);
            //$("#bill-table").append(bprTh);
          }
          var bprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td> ' + item_code + ' </td><td class="no-border">  ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item.options ? '(' + item.row.option + ')' : '') + '</td><td>' + formatDecimal(item_qty) + ' ' + item.row.unit_lable + '</td> <td>' + (item_discount != 0 ? '<del>' + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount) + '</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + '</td><td style="text-align:right;">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</td></tr>';
          //var bprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td colspan="2" class="no-border">#'+(an-1)+' '+ item_name + ' (' + item_code + ')</td></tr>';
          //bprTr += '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>(' + formatDecimal(item_qty) + ' x ' + (item_discount != 0 ? '<del>'+formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount)+'</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val))+ ')</td><td style="text-align:right;">'+ formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) +'</td></tr>';
          var oprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>#' + (an - 1) + ' ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item.options ? '(' + item.row.option + ')' : '') + ')</td><td>' + formatDecimal(item_qty) + '</td></tr>';
          $("#order-table").append(oprTr);
          $("#bill-table").append(bprTr);
        }
      });

      // Order level discount calculations
      /* if (posdiscount = localStorage.getItem('posdiscount')) {
          var ds = posdiscount;
          if (ds.indexOf("%") !== -1) {
              var pds = ds.split("%");
              if (!isNaN(pds[0])) {
                  order_discount = formatDecimal((parseFloat(((total) * parseFloat(pds[0])) / 100)), 4);
              } else {
                  order_discount = parseFloat(ds);
              }
          } else {
              order_discount = parseFloat(ds);
          }
          total_discount += parseFloat(order_discount);
      }*/


      // Order level tax calculations
      if (site.settings.tax2 != 0) {
        if (postax2 = localStorage.getItem('postax2')) {
          $.each(tax_rates, function () {
            if (this.id == postax2) {
              if (this.type == 2) {
                invoice_tax = formatDecimal(this.rate);
              }
              if (this.type == 1) {
                invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 6);
              }
            }
          });
        }
      }

      total = formatDecimal(total, 2);
      product_tax = formatDecimal(product_tax, 2);
      total_discount = formatDecimal(order_discount + product_discount, 2);


      // Totals calculations after item addition
      gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
      $('#total1').text(formatMoney(total + product_discount));
      $('#titems1').text((an - 1) + ' (' + formatDecimal(parseFloat(count) - 1) + ')');
      $('#total_items').val((parseFloat(count) - 1));
      // $('#tds').text('(' + formatMoney(product_discount) + ') ' + formatMoney(order_discount));
      $('#tds1').text(formatMoney(product_discount + order_discount));
      if (site.settings.tax2 != 0) {
        $('#ttax21').text('(' + formatMoney(product_tax) + ') ' + formatMoney(invoice_tax))
      } else {
        $('#ttax21').text('(' + formatMoney(product_tax) + ') ')
      }
      $('#gtotal1').text(formatMoney(gtotal));
      applyMainPosTotalsToCoinageModal();
      if (java_applet == 1) {
        bill_data += "\n" + printLine(lang_total + ': ' + formatMoney(total)) + "\n";
        bill_data += printLine(lang_items + ': ' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')') + "\n";
        if (total_discount > 0) {
          bill_data += printLine(lang_discount + ': (' + formatMoney(product_discount) + ') ' + formatMoney(order_discount)) + "\n";
        }
        if (site.settings.tax2 != 0 && invoice_tax != 0) {
          bill_data += printLine(lang_tax2 + ': ' + formatMoney(invoice_tax)) + "\n";
        }
        bill_data += printLine(lang_total_payable + ': ' + formatMoney(gtotal)) + "\n";
      } else {
        var bill_totals = '';
        bill_totals += '<tr class="bold"><td>' + lang_total + '</td><td></td><td style="text-align:right;">' + formatMoney(total) + '</td></tr>';
        bill_totals += '<tr class="bold"><td>' + lang_items + '</td><td></td><td style="text-align:right;">' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')</td></tr>';
        if (order_discount > 0) {
          bill_totals += '<tr class="bold"><td>' + lang_discount + '</td><td></td><td style="text-align:right;">' + formatMoney(order_discount) + '</td></tr>';
        }
        if (site.settings.tax2 != 0 && invoice_tax != 0) {
          bill_totals += '<tr class="bold"><td>' + lang_tax2 + '</td><td></td><td style="text-align:right;">' + formatMoney(invoice_tax) + '</td></tr>';
        }
        bill_totals += '<tr class="bold"><td>' + lang_total_payable + '</td><td></td><td style="text-align:right;">' + formatMoney(gtotal) + '</td></tr>';

        if (site.settings.pos_type == 'restaurant') {
          bill_totals += '<tr><td>Waiter </td><td> ' + $('#sales_person').find('option:selected').text() + '</td><td></td>';
          bill_totals += '<tr><td>Date and Time</td><td> ' + hrld() + '</td><td></td>';
        }

        $('#bill-total-table').empty();
        $('#bill-total-table').append(bill_totals);
      }
      if (count > 1) {
        $('#poscustomer').select2("readonly", true);
        $('#poswarehouse').select2("readonly", true);
      } else {
        $('#poscustomer').select2("readonly", false);
        $('#poswarehouse').select2("readonly", false);
      }

      // Hide Keybord on mobile and Android device
      /* if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      $('input').attr("onfocus","blur()");
      KB = true;
      }
      if (KB) {
      display_keyboards();
      }
      if (site.settings.set_focus == 1) {
      $('#add_item').attr('tabindex', an);
      //  $('[tabindex='+(an-1)+']').focus().select();
      } else {
      $('#add_item').attr('tabindex', 1);
      // $('#add_item').focus();
      }*/
    }
    var customerName = document.getElementById('customer_name').value;
    // Loop through items
    var isExchange = localStorage.getItem("isExchange");
    if (isExchange === true) {
      exchangeOperation(isExchange);
    }
  }

  // shown denomination box with currency values
  function renderDenominations(Alldenominations) {

    const container = $("#denomination-containerr");
    container.empty();

    let currentType = "";
    let grid = $('<div class="grid-container"></div>');

    Alldenominations.forEach(function (item, index) {
      // If the denomination type changes, close current grid and start a new one
      if (item.type !== currentType) {
        if (grid.children().length > 0) {
          container.append(grid);
          grid = $('<div class="grid-container"></div>');
        }

        const title = item.type === "Bills" ? "Notes" : item.type;
        container.append(`<h3 class="denomination-title">${title}</h3>`);
        currentType = item.type;
      }

      const formattedCurrencyValue = formatMoneys(item.currency_value);
      const valFixed = parseFloat(item.currency_value).toFixed(2);
      // Normalize type to match internal logic and DOM selectors
      const normalizedType = (function (t) {
        const low = String(t || "").toLowerCase();
        if (low === "coins") return "coin";
        if (low === "bills") return "note";
        if (low === "coin" || low === "note") return low;
        return low; // fallback to lowercase
      })(item.type);

      const denominationBox = `
                <div class="denomination-box" data-type="${normalizedType}">
                    <span class="denom-label currency-value">${formattedCurrencyValue}</span>
                    <div class="counter">
                        <button class="decrease" data-value="${valFixed}" data-type="${normalizedType}">−</button>
                        <span class="count" data-value="${valFixed}">0</span>
                        <button class="increase" data-value="${valFixed}" data-type="${normalizedType}">+</button>
                    </div>
                    <button class="remove hidden" data-value="${valFixed}" id="cross">✖</button>
                </div>
            `;

      grid.append(denominationBox);


    });

    // Append the final grid after loop
    if (grid.children().length > 0) {
      container.append(grid);
    }
  }

  // Increase button click
  $(document).on('click', '.increase', function () {

    var currencyType = $(this).data("type");
    var currencyValue = parseFloat($(this).data('value'));
    var countElement = $(this).siblings('.count');
    var currencyCount = parseInt(countElement.text());
    var $btn = $(this);
    // reset any previous grey-out state before recalculating
    $btn.removeClass('gray-out');

    // In return mode, do not allow increasing beyond available count for this type/value
    var modeFlag = localStorage.getItem('modeFlag');
    if (modeFlag === 'return') {
      try {
        var availableList = JSON.parse(localStorage.getItem('AvailableDenominations')) || [];
        var match = findAvailableDenomination(availableList, currencyValue, currencyType);
        var avail = match ? (parseInt(match.count) || 0) : 0;
        // If already at or above availability, grey-out and block
        if (currencyCount >= avail) {
          $btn.addClass('gray-out');
          // Block increment if already at availability
          return;
        }
        // If the next increment will reach the limit, mark to grey-out after increment
        var shouldGreyAfter = (currencyCount + 1) >= avail;
      } catch (e) { /* ignore guard errors */ }
    }
    currencyCount += 1;
    countElement.text(currencyCount);
    var denominationBox = $(this).closest(".denomination-box");

    toggleCashButtons();// as per denomination selected enable/disable submit button
    updateDenominationBoxColorBasedOnCount(denominationBox); // Call the universal function to update color and visibility
    updateSelectedDenominationCount(currencyValue, currencyCount, currencyType);
    // Apply or clear grey-out state based on mode and availability
    if (modeFlag === 'return') {
      try {
        if (typeof shouldGreyAfter !== 'undefined' && shouldGreyAfter) {
          $btn.addClass('gray-out');
        } else {
          $btn.removeClass('gray-out');
        }
      } catch (e) { /* ignore */ }
    } else {
      $btn.removeClass('gray-out');
    }
  });

  // Increase button click
  $(document).on('click', '.decrease', function () {

    var currencyType = $(this).data("type");
    var currencyValue = parseFloat($(this).data('value'));
    var countElement = $(this).siblings('.count');
    var currencyCount = parseInt(countElement.text());
    // currencyCount -= 1;
    currencyCount = Math.max(0, currencyCount - 1);
    countElement.text(currencyCount);
    var denominationBox = $(this).closest(".denomination-box");
    // On decrease, if we were previously at the limit in return mode, allow increasing again (remove grey-out)
    try {
      var modeFlag = localStorage.getItem('modeFlag');
      if (modeFlag === 'return') {
        var availableList = JSON.parse(localStorage.getItem('AvailableDenominations')) || [];
        var match = findAvailableDenomination(availableList, currencyValue, currencyType);
        var avail = match ? (parseInt(match.count) || 0) : 0;
        // If below the availability, ensure increase is active (not greyed)
        if (currencyCount < avail) {
          $(this).siblings('.increase').removeClass('gray-out');
        }
      } else {
        $(this).siblings('.increase').removeClass('gray-out');
      }
    } catch (e) { /* ignore */ }

    toggleCashButtons();// as per denomination selected enable/disable submit button
    updateDenominationBoxColorBasedOnCount(denominationBox);  // Call the universal function to update color and visibility
    updateSelectedDenominationCount(currencyValue, currencyCount, currencyType);
  });

  //  update denomination count in localstoarge
  function updateSelectedDenominationCount(currencyValue, currencyCount, currencyType) {

    const modeFlag = localStorage.getItem("modeFlag");
    if (modeFlag === 'return') {
      var available = JSON.parse(localStorage.getItem("AvailableDenominations")) || JSON.parse(localStorage.getItem("Alldenominations")) || [];
      checkAvailableCurrency(available);
      return;
    }
    var key = normalizeDenomKey(currencyValue);
    var typeKey = normalizeDenomType(currencyType);
    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];

    var existing = denominations.find(item =>
      normalizeDenomKey(item.currency_value) === key &&
      normalizeDenomType(item.type) === typeKey &&
      item.selected === "1"
    );

    if (existing) {
      existing.count = currencyCount.toString();
    } else {
      denominations.push({
        id: Date.now().toString(),
        currency_value: key,
        type: typeKey,
        is_active: "1",
        count: currencyCount.toString(),
        selected: "1"
      });
    }

    localStorage.setItem("Alldenominations", JSON.stringify(denominations));
    // console.log("Alldenominations:", denominations);

    const collected = { Bills: {}, Coins: {} };

    denominations.forEach(item => {
      const isSelected = item.selected === "1";
      const count = parseInt(item.count);
      const value = parseFloat(item.currency_value).toFixed(2);

      if (isSelected && count > 0) {
        const typeKey = normalizeDenomType(item.type) === "note" ? "Bills" : "Coins";
        collected[typeKey][value] = count;
      }
    });

    localStorage.setItem("CollectedDenominations", JSON.stringify(collected));
    // console.log("Collected Denominations:", collected);
    updateTotalPayableSection();
  }

  function updateTotalPayableSection() {

    var modeFlag = localStorage.getItem("modeFlag");
    var invoiceAmount = getCoinageInvoiceAmount();
    var returnAmt = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];
    var collectedAmount = 0;
    var selectedAmount = 0;


    // ---------- RETURN FLOW ----------
    if (modeFlag == 'return') {

      $(".denomination-box").each(function () {
        const count = parseInt($(this).find(".count").text()) || 0;
        const value = parseFloat($(this).find(".increase").data("value")) || 0;
        selectedAmount += count * value;
      });

      const pendingAmount = returnAmt - selectedAmount;

      if (pendingAmount > 0) {
        $("#returnToastMessage").text(`Currency of ${pendingAmount.toFixed(2)} is not available for issuing Return Amount.`);
        $("#returnOverlay").fadeIn();
        $("#returnToast").fadeIn();
      } else {
        $("#returnOverlay").hide();
        $("#returnToast").hide();
      }
      $("#amount-section-container").css({
        display: "flex",
        gap: "5px",
        "margin-top": "10px",
        "justify-content": "space-between",
        "align-items": "center",
        width: "100%",
      });

      $("#selectedAmount").html(`<strong>Selected Amount:</strong> ${formatNumber(selectedAmount)}`).css({
        "background-color": "#90EE90",
        "padding": "5px",
        "text-align": "center",
        "border-radius": "5px",
        "font-weight": "bold",
        "width": "fit-content",
        "margin": "5px auto"
      }).show();
      $("#pendingAmount").html(`<strong>Pending Amount:</strong> ${formatNumber(pendingAmount)}`).css({
        "background": "#FFC0CB",
        "padding": "5px",
        "text-align": "center",
        "border-radius": "5px",
        "font-weight": "bold",
        "width": "fit-content",
        "margin": "5px auto"
      }).show();

      $("#selectedAmount").css("background", "#90EE90");
      $("#pendingAmount").css("background", "#FFC0CB");
      $("#yourDivId").hide();
      $(".payment-box").show();
      $("#returnCompleteBtn").show();
      $("#balance").text(formatNumber(pendingAmount));

      $(
        ".payment-box, #denomination-containerr, #clearAll, #amount-section-container"
      ).css({
        border: "9px solid #F9C4DA",
      });

      // Change background color and text color of the return amount row
      $(this).closest(".row").css({
        color: "#fff",
        padding: "2px",
        "border-radius": "5px",
        "margin-left": "-12px;",
      });

      // Style the button itself
      $(this).css({
        "background-color": "#E9136B",
        color: "#fff",
        border: "1px solid #E9136B",
      });
      $("#returnComplete").show();
      return;
    } else {
      // ---------- NORMAL AND COLLECTED FLOW ----------

      denominations.forEach(item => {
        if (item.selected === "1") {
          var count = parseInt(item.count) || 0;
          var value = parseFloat(item.currency_value) || 0;
          collectedAmount += count * value;
        }
      });

      // Prevent negative return amount in Collected/Normal flow
      $("#totalPayableAmount").text(formatNumber(invoiceAmount));
      $("#depositedAmount").text(formatNumber(collectedAmount));
      $("#pendingAmount").hide();

      $("#selectedAmount").html(`<strong>Selected Amount:</strong> ${formatNumber(collectedAmount)}`).css({
        "background-color": "#90EE90",
        "padding": "5px",
        "text-align": "center",
        "border-radius": "5px",
        "font-weight": "bold",
        "width": "fit-content",
        "margin": "5px auto"
      }).show();

      if (collectedAmount > 0) {
        $("#denomination-containerr").css("border", "9px solid #FFE0A7");
        $("#clearAll").css("border", "9px solid #FFE0A7");
        $(".payment-box").css("border", "9px solid #FFE0A7");
        $(".clear-btn").removeClass("hidden").show();
        $("#Collected").attr("style", "background-color: #FFE0A7 !important; color: #000 !important;");
        $("#amount-section-container").css({ border: "9px solid #FFE0A7" });
      }

      refreshCoinagePaymentTotals();
    }

  }

  // Remove button click and update denomination count
  $(document).on('click', '.remove', function () {
    var denominationBox = $(this).closest('.denomination-box');
    var currencyValue = parseFloat($(this).data("value")).toFixed(2);
    var currencyType = denominationBox.data("type").toLowerCase();

    // Set count to 0 in the UI
    denominationBox.find('.count').text('0');
    $(this).addClass('hidden');
    denominationBox.removeClass('highlight');
    // Reset the Increase button to initial state for this denomination
    denominationBox.find('.increase')
      .removeClass('gray-out')
      .prop('disabled', false)
      .css({ 'pointer-events': 'auto', 'opacity': '' });

    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];  // Update localStorage count = 0

    denominations.forEach(item => {
      if (normalizeDenomKey(item.currency_value) === normalizeDenomKey(currencyValue) && normalizeDenomType(item.type) === normalizeDenomType(currencyType) && item.selected === "1") {
        item.count = "0";
      }
    });

    localStorage.setItem("Alldenominations", JSON.stringify(denominations));
    toggleCashButtons(); // as per denomination selected enable/disable submit button

    // Update the visual appearance after resetting count
    updateDenominationBoxColorBasedOnCount(denominationBox);
    updateTotalPayableSection();
    // Do not immediately re-enforce availability here; keep UI in initial state after removal
  });

  // clear all data from localStorage and also reset denomination box
  $("#clearAll").click(function () {

    localStorage.removeItem("modeFlag");
    // Clear computed stores to avoid stale state
    localStorage.removeItem("CollectedDenominations");
    localStorage.removeItem("ReturnDenominations");
    // Restore working copy from immutable availability snapshot (jQuery style)
    var availableStr = localStorage.getItem("AvailableDenominations");
    var available = availableStr ? JSON.parse(availableStr) : null;
    if ($.isArray(available)) {
      localStorage.setItem("Alldenominations", JSON.stringify(available));
    }
    $(".denomination-box").each(function () {
      const $box = $(this);
      const currencyValue = parseFloat($box.find(".increase").data("value"));
      const currencyType = $box.data("type");
      const $label = $box.find(".denom-label");
      const $countElem = $box.find(".count");
      const $increase = $box.find(".increase");
      const $decrease = $box.find(".decrease");
      const $remove = $box.find(".remove");

      $box.find(".count").text("0");
      $box.css({
        "background-color": "",
        "opacity": "",
        "pointer-events": "",
      });

      $label.add($countElem).css({
        "background-color": "",
        "opacity": "",
        "pointer-events": "",
        "color": ""
      });

      $increase.add($decrease).add($remove).css({
        "opacity": "",
        "pointer-events": "",
      }).prop("disabled", false); // ensure they are not disabled
      // $box.removeClass("highlight").css("background-color", "");
      // $box.find(".remove").addClass("hidden");
      updateDenominationBoxColorBasedOnCount($box);

      updateSelectedDenominationCount(currencyValue, 0, currencyType);
    });

    $("#depositedAmount").text("0");
    refreshCoinagePaymentTotals();

    $("#yourDivId").hide();
    $("#selectedAmount").hide();
    $("#pendingAmount").hide();
    $("#amount-section-container").hide();

    $("#Collected").css("background-color", "");
    $("#denomination-containerr").css("border", "9px solid #E6F5FF");
    $("#clearAll").css("border", "9px solid #E6F5FF");
    $(".payment-box").css("border", "9px solid rgb(230, 245, 255)").show();
    $("#amnts").show();
  });

  // collected click event
  $("#Collected").click(function () {
    localStorage.setItem("modeFlag", "collected");
    // render collected denomination when click on collected
    renderCollectedDenominations();
    // Update totals and return amount
    updateTotalPayableSection();
    $("#amount-section-container").show();
  });

  // render collected denomination when click on collected
  function renderCollectedDenominations() {

    $(".denomination-box").each(function () {
      const $box = $(this);
      // Reset highlight/background
      $box.removeClass("highlight").css("background-color", "#ffffff");
      // Reset counts
      $box.find(".count").text("0");
      // Ensure buttons and labels are fully enabled (undo Return mode greying)
      $box.find(".denom-label, .count").css({
        "background-color": "",
        "opacity": "1",
        "pointer-events": "auto",
      });
      $box.find(".increase, .decrease, .remove").css({
        "opacity": "1",
        "pointer-events": "auto",
      });
      // Clear any inline display:none from previous .hide()
      $box.find(".remove").show().addClass("hidden");
    });
    var Alldenominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];

    Alldenominations.forEach(item => {
      if (item.selected === "1" && parseInt(item.count) > 0) {
        const currencyValue = parseFloat(item.currency_value).toFixed(2);
        const type = item.type;

        const $box = $(`.denomination-box[data-type="${type}"]`).filter((_, el) => {
          const value = parseFloat($(el).find(".increase").data("value")).toFixed(2);
          return value === currencyValue;
        });

        // Apply updates to existing box
        $box.find(".count").text(item.count);
        // $box.addClass("highlight").css("background-color", "#90EE90");
        // $box.find(".remove").removeClass("hidden");
        // Call the universal function to update color and visibility
        updateDenominationBoxColorBasedOnCount($box);
      }
    });
  }

  // count wise set colour for denomination box
  function updateDenominationBoxColorBasedOnCount(denominationBox) {
    const count = parseInt(denominationBox.find('.count').text()) || 0;

    if (count === 0) {
      denominationBox.css("background-color", "#ffffff");
      denominationBox.removeClass("highlight");
      denominationBox.find('.remove').addClass('hidden').hide();
    } else if (count > 0) {
      denominationBox.css("background-color", "#90EE90");
      denominationBox.addClass("highlight");
      // Remove hidden class and ensure it becomes visible
      denominationBox.find('.remove').removeClass('hidden').show();
    }
  }
  // Return click event
  $("#returnAmt").click(function () {
    localStorage.setItem("modeFlag", "return");
    calculateReturnDenominationAmount();
    updateTotalPayableSection();
    
    // Ensure buttons are enabled after setting return mode
    setTimeout(function() {
      ensureButtonsEnabled();
    }, 100);
  });
  $(document).on("click", "#returnToastOkBtn, #returnToastClose", function () {
    $("#returnOverlay").fadeOut();
    $("#returnToast").fadeOut();
  });

  // calculate return amount when we click on return button
  function calculateReturnDenominationAmount() {
    // Always use immutable availability snapshot for return
    var Alldenominations = JSON.parse(localStorage.getItem("AvailableDenominations")) || JSON.parse(localStorage.getItem("Alldenominations")) || [];
    var returnAmount = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
    var returnDenominations = { coin: {}, note: {}, };

    // Reset UI highlights and counts before return rendering 
    $(".denomination-box").each(function () {
      $(this).removeClass("highlight").css("background-color", "#ffffff");
      $(this).find(".count").text("0");
      // In Return mode, rely on the 'hidden' class only; don't set inline display:none
      $(this).find(".remove").addClass("hidden");
    });

    // Sort denominations by currency value (highest to lowest)
    Alldenominations.sort((a, b) => b.currency_value - a.currency_value);
    // var originalAmount = returnAmount; // Save the original amount for final comparison

    Alldenominations.forEach(function (item) {
      var currency = item.currency_value;
      var availableCount = Number(item.count) || 0;
      var type = (function (t) {
        const low = String(t || "").toLowerCase();
        if (low === "coins") return "coin";
        if (low === "bills") return "note";
        if (low === "coin" || low === "note") return low;
        return low;
      })(item.type);
      var requiredCount = Math.floor(returnAmount / currency); // How many are count is needed

      disableDenominationBoxAsPerAvaCount(availableCount, requiredCount, currency, type); // grey out denomination box as per checking availability of currency

      // Calculate how much to use
      if (requiredCount > 0 && availableCount > 0) {
        var usedCount = Math.min(requiredCount, availableCount);
        if (type === "coin") {
          returnDenominations.coin[currency] = usedCount;
        } else if (type === "note") {
          returnDenominations.note[currency] = usedCount;
        } else {
          // In case an unexpected type sneaks in, place by matching DOM type
          returnDenominations[type] = returnDenominations[type] || {};
          returnDenominations[type][currency] = usedCount;
        }
        returnAmount -= usedCount * currency;
      }
    });

    localStorage.setItem("ReturnDenominations", JSON.stringify(returnDenominations));
    // console.log("ReturnDenominations :", returnDenominations);

    renderReturnDenomination(returnDenominations);
    // After rendering, enforce availability-based button states
    try {
      var _available = JSON.parse(localStorage.getItem("AvailableDenominations")) || Alldenominations;
      if (Array.isArray(_available)) {
        checkAvailableCurrency(_available);
      }
    } catch (e) { /* ignore */ }
    
    ensureButtonsEnabled();
    // return returnDenominations;
  }

  // Render return denominations
  function renderReturnDenomination(returnDenominations) {
    const allDenominations = [];

    // Merge coin denominations
    if (returnDenominations.coin) {
      Object.entries(returnDenominations.coin).forEach(([value, count]) => {
        allDenominations.push({
          value: parseFloat(value),
          count: parseInt(count),
          type: "coin",
        });
      });
    }

    // Merge note denominations
    if (returnDenominations.note) {
      Object.entries(returnDenominations.note).forEach(([value, count]) => {
        allDenominations.push({
          value: parseFloat(value),
          count: parseInt(count),
          type: "note",
        });
      });
    }

    // Sort all denominations in descending order by value
    const sortedDenoms = allDenominations.sort((a, b) => b.value - a.value);

    // Render denominations
    sortedDenoms.forEach(({ value, count, type }) => {
      if (count > 0) {
        // Find all denomination boxes with matching value
        const $boxes = $(".denomination-box").filter(function () {
          return $(this).find(".count").data("value") == value;
        });

        // Match box by type (coin/note)
        let $preferredBox = $boxes.filter(`[data-type='${type}']`).first();

        // If not found, fallback to any box with that value
        if ($preferredBox.length === 0) {
          $preferredBox = $boxes.first();
        }

        if ($preferredBox.length) {
          $preferredBox.addClass("highlight");
          $preferredBox.find(".count").text(count);
          $preferredBox.find(".remove").removeClass("hidden").show();
        }
      }
    });

    // Fallback rendering if nothing was rendered
    if (sortedDenoms.length === 0) {
      for (let key in returnDenominations) {
        let countElement = $('.count[data-value="' + key + '"]');

        if (countElement.length === 0) {
          console.error("No .count element found for denomination:", key);
        } else {
          const count = returnDenominations[key];
          countElement.text(count);
          if (count > 0) {
            countElement.closest(".denomination-box").addClass("highlight");
          } else {
            countElement.closest(".denomination-box").removeClass("highlight");
          }
        }
      }
    }
  }
  // grey out denomination box as per checking availability of currency
  function disableDenominationBoxAsPerAvaCount(availableCount, requiredCount, currency, type) {
    // console.log("availableCount:", availableCount, "requiredCount:", requiredCount, "currency:", currency, "type:", type);
    // Format currency to two decimal places
    const fixedCurrency = parseFloat(currency).toFixed(2);
    // const $countElem = $(`.denomination-box .count[data-value="${fixedCurrency}"]`);
    const $countElem = $(`.denomination-box[data-type="${type}"] .count[data-value="${fixedCurrency}"]`);

    if ($countElem.length) {
      const $box = $countElem.closest(".denomination-box");
      const $increase = $box.find(".increase");
      const $decrease = $box.find(".decrease");
      const $remove = $box.find(".remove");
      const $label = $box.find(".denom-label");

      // 1) No availability: gray out fully and disable increase
      if (availableCount === 0) {
        $label.css({ "background-color": "#eee", "opacity": "0.6", "pointer-events": "none" });
        $countElem.css({ "background-color": "#eee", "opacity": "0.6", "pointer-events": "none" });
        $increase.css({ "opacity": "0.4", "pointer-events": "none" });
        $decrease.css({ "opacity": "1", "pointer-events": "auto" });
        $remove.css({ "opacity": "1", "pointer-events": "auto" });
        $box.css({ "background-color": "#f5f5f5" });
        return;
      }

      // 2) Availability exists but selection reached the available count: disable only increase
      if (requiredCount >= availableCount && requiredCount > 0) {
        // Restore normal visuals, but lock the increase
        $label.add($countElem).css({ "background-color": "", "opacity": "1", "pointer-events": "auto" });
        $decrease.add($remove).css({ "opacity": "1", "pointer-events": "auto" });
        $increase.css({ "opacity": "0.4", "pointer-events": "none" });
        $box.css({ "background-color": "" });
        return;
      }

      // 3) Availability exists and selection is below available: fully interactive
      $label.add($countElem).css({ "background-color": "", "opacity": "1", "pointer-events": "auto" });
      $increase.add($decrease).add($remove).css({ "opacity": "1", "pointer-events": "auto" });
      $box.css({ "background-color": "" });
    }
  }

  // after return mode and increse event then check available count
  function checkAvailableCurrency(Alldenominations) {

    // Sort denominations by currency value (highest to lowest)
    Alldenominations.sort((a, b) => b.currency_value - a.currency_value);
    // var originalAmount = returnAmount; // Save the original amount for final comparison
    Alldenominations.forEach(function (item) {
      var currency = item.currency_value;
      var availableCount = Number(item.count) || 0;
      var type = (function (t) {
        const low = String(t || "").toLowerCase();
        if (low === "coins") return "coin";
        if (low === "bills") return "note";
        if (low === "coin" || low === "note") return low;
        return low;
      })(item.type);
      const selector = `.denomination-box[data-type="${type}"] .increase[data-value="${parseFloat(currency).toFixed(2)}"]`;
      const requiredCount = parseInt($(selector).siblings('.count').text(), 10) || 0;

      disableDenominationBoxAsPerAvaCount(availableCount, requiredCount, currency, type); // grey out denomination box as per checking availability of currency
    });
    updateTotalPayableSection();
  }

  //format money
  function formatMoneys(x, symbol) {

    if (!symbol) {
      symbol = "";
    }
    if (site.settings.sac == 1) {
      return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
        '' + (parseFloat(x).toFixed(site.settings.decimals)) +
        (site.settings.display_symbol == 2 ? site.settings.symbol : '');
    }
    var fmoney = accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
    fmoney = (fmoney == '-0.00') ? '0.00' : fmoney; //convert -0.00 to 0.00
    return (site.settings.display_symbol == 1 ? site.settings.symbol : '') + fmoney + (site.settings.display_symbol == 2 ? site.settings.symbol : '');
  }

  // Update Register Modal Trigger 
  $(document).on('click', '.UpdtRegister', function () {

    $('#paymentModal').modal('hide');
    // When the modal is fully hidden
    $('#paymentModal').one('hidden.bs.modal', function () {
      // Trigger the update_register_button
      $('#update_register_button').click();
    });
  });

  // TOGGLE of payment options
  const divElement = document.getElementById("toToggle");
  const togglElement = document.getElementById("arrow-toggle");
  // Toggle expand/collapse on arrow click
  togglElement.addEventListener("click", () => {
    if (divElement.style.height === "100%") {
      closeToggle();
      return;
    }

    divElement.style.height = "100%";
    divElement.style.zIndex = "1000";
    togglElement.style.transform = "rotate(180deg)";
    togglElement.style.transition = "transform 0.3s ease";
  });

  // Collapse function
  function closeToggle() {
    divElement.style.height = "13vh";
    togglElement.style.transform = "rotate(0deg)";
    togglElement.style.transition = "transform 0.3s ease";
  }

  // Collapse on payment method selection
  divElement.addEventListener("click", (event) => {
    const target = event.target.closest(".payment-methods");
    if (target) {
      // console.log("Payment method clicked:", target.textContent);
      closeToggle();
    }
  });

  // Toggle button states based on payment type and denomination selection
  function toggleCashButtons() {
    const selectedPaymentRaw = $('input[name="colorRadio"]:checked').val();
    const selectedPayment = String(selectedPaymentRaw || '').toLowerCase().trim();
    const isCash = selectedPayment.indexOf('cash') !== -1;

    // Check if any denomination count is > 0
    let anySelected = false;
    
    // If in return mode, don't disable buttons even if no denominations selected
    const modeFlag = localStorage.getItem("modeFlag");
    if (modeFlag === "return") {
      anySelected = true; 
    } else {
      const returnAmount = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
      if (returnAmount > 0) {
        anySelected = true; // Allow buttons to remain enabled when there's a return amount
      } else {
        $('.denomination-box').each(function () {
          const count = parseInt($(this).find('.count').text()) || 0;
          if (count > 0) {
            anySelected = true;
            return false;
          }
        });
      }
    }

    var hasSplitPayments = getCashExtraPaymentsList().length > 0;

    var $targets = $('.disable-on-cash, .final-submit-btn');
    if (isCash) {
      $targets.prop('disabled', !anySelected && !hasSplitPayments);
    } else {
      $targets.prop('disabled', false);
    }
    
    // Ensure buttons are enabled when needed after toggling
    ensureButtonsEnabled();
  }
  window.toggleCashButtons = toggleCashButtons;

  // detect cash selection
  function isCashSelected() {
    const raw = $('input[name="colorRadio"]:checked').val();
    return String(raw || '').toLowerCase().trim().indexOf('cash') !== -1;
  }

  function hasAnyDenominationSelected() {
    // If in return mode, don't disable buttons even if no denominations selected
    const modeFlag = localStorage.getItem("modeFlag");
    if (modeFlag === "return") {
      return true; 
    }
    
    // Check if there's a return amount that needs to be handled
    const returnAmount = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
    if (returnAmount > 0) {
      return true; // Allow buttons to remain enabled when there's a return amount
    }
    
    let anySelected = false;
    $('.denomination-box').each(function () {
      const count = parseInt($(this).find('.count').text()) || 0;
      if (count > 0) { anySelected = true; return false; }
    });
    return anySelected;
  }

  function ensureButtonsEnabled() {
    const modeFlag = localStorage.getItem("modeFlag");
    const returnAmount = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
    
    // If in return mode or there's a return amount, ensure buttons are enabled
    if (modeFlag === "return" || returnAmount > 0) {
      var $scope = $('#paymentModal');
      $scope.find('.disable-on-cash, .final-submit-btn, button[type="submit"], input[type="submit"]').prop('disabled', false);
    }
  }
  
  setInterval(function () {
    try {
      // Don't disable buttons if in return mode 
      const modeFlag = localStorage.getItem("modeFlag");
      if (modeFlag === "return") {
        return; 
      }
      
      // Check if there's a return amount that needs to be handled
      const returnAmount = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
      if (returnAmount > 0) {
        return; 
      }
      
      if (isCashSelected() && !hasAnyDenominationSelected()) {
        var $scope = $('#paymentModal');
        $scope.find('.disable-on-cash, .final-submit-btn, button[type="submit"], input[type="submit"]').prop('disabled', true);
      }
      
      ensureButtonsEnabled();
    } catch (e) {}
  }, 250);
  // Function to show/hide payment fields based on payment method
  function showPaymentFields(selectedPaymentMethod) {
    // Hide all payment-specific fields first
    $('.pcc_1').hide();
    $('.pcheque_1').hide();
    $('.pother_1').hide();
    $('.pcash_1').hide();
    $('.gc_1').hide();
    $('.db_1').hide();
    $('.ap_1').hide();

    if (selectedPaymentMethod == 'cash') {
      $('.pcash_1').show();
      if ($('#amount_1').length) $('#amount_1').focus();
    } else if (selectedPaymentMethod == 'CC' || selectedPaymentMethod == 'DC' || selectedPaymentMethod == 'stripe' ||
      selectedPaymentMethod == 'ppp' || selectedPaymentMethod == 'authorize' ||
      selectedPaymentMethod == 'payswiff' || selectedPaymentMethod == 'paytm' ||
      selectedPaymentMethod == 'PAYTM' || selectedPaymentMethod == 'Googlepay' ||
      selectedPaymentMethod == 'UPI_QRCODE' || selectedPaymentMethod == 'razorpay' ||
      selectedPaymentMethod == 'zomato' || selectedPaymentMethod == 'swiggy' ||
      selectedPaymentMethod == 'ubereats' || selectedPaymentMethod == 'magicpin' ||
      selectedPaymentMethod == 'complimentry' || selectedPaymentMethod == 'payumoney' ||
      selectedPaymentMethod == 'instamojo' || selectedPaymentMethod == 'Stripe_PM' ||
      selectedPaymentMethod == 'NEFT' || selectedPaymentMethod == 'Tabby' || selectedPaymentMethod == 'Pos' || selectedPaymentMethod == 'Tamara') {
      $('.pcc_1').show();
      setTimeout(function () {
        if ($('#cc_transac_no_1').length) $('#cc_transac_no_1').focus();
      }, 100);
    } else if (selectedPaymentMethod == 'Cheque') {
      $('.pcheque_1').show();
      setTimeout(function () {
        if ($('#cheque_no_1').length) $('#cheque_no_1').focus();
      }, 100);
    } else if (selectedPaymentMethod == 'other' || selectedPaymentMethod.indexOf('other_') === 0) {
      $('.pother_1').show();
      setTimeout(function () {
        if ($('#other_tran_no_1').length) $('#other_tran_no_1').focus();
      }, 100);
    } else if (selectedPaymentMethod == 'gift_card') {
      $('.gc_1').show();
      setTimeout(function () {
        if ($('#gift_card_no_1').length) $('#gift_card_no_1').focus();
      }, 100);
    } else if (selectedPaymentMethod == 'deposit') {
      $('.db_1').show();
    } else if (selectedPaymentMethod == 'award_point') {
      $('.ap_1').show();
    }
  }

  // Handle click on payment method radio buttons and labels - show fields immediately
  $(document).on('click', 'input[name="colorRadio"], label.payment-methods', function (e) {
    var $radio = $(this);
    // If clicked on label, find the associated radio button
    if ($(this).is('label')) {
      var radioId = $(this).attr('for');
      $radio = $('#' + radioId);
    }

    var selectedPaymentMethod = $radio.val();
    if (selectedPaymentMethod) {
      // Show fields immediately on click, before change event fires
      // Use a small delay to ensure the radio is checked first
      setTimeout(function () {
        // Double-check that this radio is actually checked
        if ($radio.is(':checked')) {
          showPaymentFields(selectedPaymentMethod);
        }
      }, 20);
    }
  });

  $(document).on('change', '#paymentModal #paid_by_1', function () {
    var val = getCoinagePrimaryPaidBy();
    if (val && $('#paid_by_val_1').length) {
      $('#paid_by_val_1').val(val);
    }
  });

  $(document).on('change', '#paymentModal input[name="colorRadio"]', function () {
    toggleCashButtons();

    // Update the paid_by_1 field when payment method is changed
    var selectedPaymentMethod = $(this).val();

    if (typeof window.coinageToggleDenominationBox === 'function') {
      window.coinageToggleDenominationBox(selectedPaymentMethod);
    }

    // Show/hide payment fields - use setTimeout to ensure it runs after other handlers
    setTimeout(function () {
      showPaymentFields(selectedPaymentMethod);
      if (selectedPaymentMethod === 'award_point' && typeof setCustomerAwardPoint === 'function') {
        var billAmt = getCoinageInvoiceAmount() || parseFloat($('#amount_1').val()) || 0;
        if (billAmt > 0) {
          setCustomerAwardPoint(billAmt, 'award_point');
        }
      }
    }, 50);

    syncCoinagePaidBySelect(selectedPaymentMethod);

    // Reset balance when changing from cash to any other payment method
    if (selectedPaymentMethod !== 'cash') {
      $("#balance").text(formatNumber(0));

      
    }
  });

  $(document).on('change', '#paymentModal .paid_by', function () {
    showPaymentFields($(this).val());
  });
  $('#paymentModal').on('shown.bs.modal', function () {
    toggleCashButtons();
  });

  //Splitpay && SplitCheck Disable 
  setInterval(function () {
    $('#splitpay, #split-check').prop('disabled', true);
  }, 100);

  // right side corner cross button
  $(document).on("click", ".reset-denominations", function () {
    $('#clearAll').trigger('click');
    localStorage.clear();
  });

  // remove all data from localstorage
  $("#backToPOS").click(function () {
    // Clear only denomination-related localStorage keys
    localStorage.removeItem("Alldenominations");
    localStorage.removeItem("CollectedDenominations");
    localStorage.removeItem("ReturnDenominations");
    localStorage.removeItem("modeFlag");
    localStorage.removeItem("AvailableDenominations");
    localStorage.removeItem("selectedDenominations");
  });

  window.addEventListener("load", function () {
    localStorage.removeItem("Alldenominations");
    localStorage.removeItem("CollectedDenominations");
    localStorage.removeItem("ReturnDenominations");
    localStorage.removeItem("modeFlag");
  });

  // $(".final-submit-btn").click(function () {
  //   //  var formattedData =  saveDenominationDb();

  //   var ReturnDenominations = JSON.parse(localStorage.getItem("ReturnDenominations")) || {};
  //   var CollectedDenominations = JSON.parse(localStorage.getItem("CollectedDenominations")) || [];

  //   $.ajax({
  //     url: site.base_url + "pos/saveDenominations",
  //     type: "POST",
  //     data: {
  //       CollectedDenomination: JSON.stringify(CollectedDenominations),
  //       ReturnsDwnominations: JSON.stringify(ReturnDenominations),
  //     },
  //     success: function (response) {
  //       localStorage.removeItem("Alldenominations");
  //       localStorage.removeItem("CollectedDenominations");
  //       localStorage.removeItem("ReturnDenominations");
  //       localStorage.removeItem("modeFlag");
  //       console.log(response);
  //     },
  //     error: function (xhr, status, error) {
  //       alert("There was an error saving the data.");
  //       console.log(error);
  //     },
  //   });
  // });

  function syncAwardPointHiddenFields(apVal) {
    apVal = apVal || '';
    $('#ap_1, #ap_val_1').val(apVal);
    for (var i = 2; i <= 5; i++) {
      $('#ap_val_' + i).val('');
    }
  }

  function coinageSyncMainFormPaymentFields(invoiceAmount, collectedAmount, modeFlag) {
    var currentPaidBy = (getCoinagePrimaryPaidBy() || $('#paymentModal input[name="colorRadio"]:checked').val() || 'cash').trim();
    var extras = getCashExtraPaymentsList();
    var extraTotal = getCashExtraPayTotal();
    var isCash = isCoinageCashPaymentSelected();
    var primaryAmount = parseFloat($('#amount_1').val()) || invoiceAmount || 0;
    var tendered = primaryAmount;

    if (currentPaidBy === 'award_point') {
      var awardAmount = parseFloat($('#amount_1').val()) || 0;
      if (awardAmount > 0) {
        tendered = awardAmount;
      }
    } else if (modeFlag === 'return') {
      tendered = invoiceAmount;
    } else if (isCash && (collectedAmount > 0 || extraTotal > 0)) {
      tendered = collectedAmount + extraTotal;
    } else if (!isCash && extraTotal > 0) {
      tendered = primaryAmount + extraTotal;
    }

    var balance = 0;
    if (modeFlag === 'return') {
      balance = 0;
    } else {
      balance = parseFloat((tendered - invoiceAmount).toFixed(4));
    }

    if (extras.length > 0) {
      if (isCash) {
        syncCashExtraPaymentsToHiddenFields(collectedAmount, balance);
        return { currentPaidBy: collectedAmount > 0 ? 'cash' : (extras[0].paidBy || currentPaidBy), tendered: tendered, balance: balance };
      }
      syncNcExtraPaymentsToHiddenFields(balance);
      return { currentPaidBy: currentPaidBy, tendered: tendered, balance: balance };
    }

    if ($('#paid_by_val_1').length) {
      $('#paid_by_val_1').val(currentPaidBy);
    }
    if ($('#paymentModal #paid_by_1').length) {
      syncCoinagePaidBySelect(currentPaidBy);
    }
    if ($('#amount_1').length) {
      $('#amount_1').val(tendered);
    }
    if ($('#amount_val_1').length) {
      $('#amount_val_1').val(tendered);
    }
    if ($('#balance_amount_1').length) {
      $('#balance_amount_1').val(balance);
    }
    if ($('#cc_transac_no_1').length && $('#cc_transac_no_val1').length) {
      $('#cc_transac_no_val1').val($('#cc_transac_no_1').val());
    }
    if ($('#cheque_no_1').length && $('#cheque_no_val_1').length) {
      $('#cheque_no_val_1').val($('#cheque_no_1').val());
    }
    if ($('#other_tran_no_1').length && $('#other_tran_no_val1').length) {
      $('#other_tran_no_val1').val($('#other_tran_no_1').val());
      $('#other_tran_mode_val1').val($('#other_tran_mode_1').val());
    }
    if ($('#payment_note_1').length && $('#payment_note_val_1').length) {
      $('#payment_note_val_1').val($('#payment_note_1').val());
    }
    if ($('#payment_method').length) {
      $('#payment_method').val(currentPaidBy);
    }
    if (currentPaidBy === 'award_point') {
      var apVal = $('#ap_val_1').val() || $('#ap_1').val() || '';
      syncAwardPointHiddenFields(apVal);
    } else {
      syncAwardPointHiddenFields('');
    }

    return { currentPaidBy: currentPaidBy, tendered: tendered, balance: balance };
  }

  $(".final-submit-btn").click(function (e) {
    e.preventDefault();
    if (window.isSubmittingSale) {
      return false;
    }
    window.isSubmittingSale = true;

    // Validation for Transaction No.
    var currentPaidBy = $('input[name="colorRadio"]:checked').val() || 'cash';
    if (currentPaidBy === 'award_point') {
      var awardAp = parseFloat($('#ap_val_1').val() || $('#ap_1').val() || 0);
      if (!awardAp || awardAp <= 0) {
        $("#returnToastMessage").text("Award points are not calculated. Select Award Point again and complete OTP verification.");
        $("#returnOverlay").show();
        $("#returnToast").show();
        if (typeof setCustomerAwardPoint === 'function') {
          var billAmt = getCoinageInvoiceAmount() || parseFloat($('#amount_1').val()) || 0;
          if (billAmt > 0) {
            setCustomerAwardPoint(billAmt, 'award_point');
          }
        }
        window.isSubmittingSale = false;
        return false;
      }
    }
    if (currentPaidBy == 'CC' || currentPaidBy == 'DC') {
        var transNo = $('#cc_transac_no_1').val();
        if (transNo == '') {
            $("#returnToastMessage").text("Please Enter Card Transaction No.");
            $("#returnOverlay").show();
            $("#returnToast").show();
            $('#cc_transac_no_1').focus();
            window.isSubmittingSale = false;
            return false;
        }
    }
    var modeFlag = localStorage.getItem("modeFlag");
    var invoiceAmount = getCoinageInvoiceAmount();
    var returnAmt = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];

    let selectedAmount = 0;
    let pendingAmount = 0;
    let collectedAmount = 0;

    // Always compute collected amount from stored selections (tendered cash)
    try {
      denominations.forEach(item => {
        if (item.selected === "1") {
          const count = parseInt(item.count) || 0;
          const value = parseFloat(item.currency_value) || 0;
          collectedAmount += count * value;
        }
      });
    } catch (e) { collectedAmount = 0; }

    if (modeFlag === "return") {
      // Return mode: Calculate selected amount from DOM
      $(".denomination-box").each(function () {
        const count = parseInt($(this).find(".count").text()) || 0;
        const value = parseFloat($(this).find(".increase").data("value")) || 0;
        selectedAmount += count * value;
      });

      pendingAmount = selectedAmount - returnAmt;

    } else {
      selectedAmount = collectedAmount;
      pendingAmount = selectedAmount - invoiceAmount;
      returnAmt = selectedAmount - invoiceAmount;
    }

    var synced = coinageSyncMainFormPaymentFields(invoiceAmount, collectedAmount, modeFlag);

    // Set calculated values into hidden fields
    $("#selected_amounts").val(selectedAmount);
    
    $("#deposited_amount").val(collectedAmount > 0 ? collectedAmount : synced.tendered);
    $("#invoice_amounts").val(invoiceAmount);
    $("#return_Amount").val(returnAmt);
    // Fetch saved denomination data
    var ReturnDenominations = JSON.parse(localStorage.getItem("ReturnDenominations")) || {};
    var CollectedDenominations = JSON.parse(localStorage.getItem("CollectedDenominations")) || [];
    var coinageReturnFlag = (modeFlag === "return" || hasCoinageReturnDenominations(ReturnDenominations)) ? "1" : "0";
    if ($("#coinage_return_mode").length) {
      $("#coinage_return_mode").val(coinageReturnFlag);
    }
    $("#amount-paid").val(synced.tendered);
    $("#pending_amount").val(synced.balance);

    // Save denominations first, then submit the main form
    $.ajax({
      url: site.base_url + "pos/saveDenominations",
      type: "POST",
      data: {
        CollectedDenomination: JSON.stringify(CollectedDenominations),
        ReturnsDwnominations: JSON.stringify(ReturnDenominations),
        coinage_return_mode: coinageReturnFlag
      },
      success: function (response) {
        localStorage.removeItem("Alldenominations");
        localStorage.removeItem("CollectedDenominations");
        localStorage.removeItem("ReturnDenominations");
        localStorage.removeItem("modeFlag");
        
        // After saving denominations, submit the main POS form to complete the sale
        setTimeout(function() {
          var mainForm = $('#pos-sale-form');
          if (mainForm.length) {
            if ($("#coinage_return_mode").length) {
              $("#coinage_return_mode").val(coinageReturnFlag);
            }
            finalizeCoinagePaymentHiddenFieldsForSubmit(synced.balance);

            var $btn = $(e.target).closest('.final-submit-btn');
            var buttonName = $btn.attr('name');

            if (buttonName === 'cmd') {
              // Quick Submit: complete sale without opening invoice (same as add_ep.php AJAX flow)
              $('#submit_type').val('notprint');
              $.ajax({
                type: mainForm.attr('method'),
                url: mainForm.attr('action'),
                data: mainForm.serialize()
              }).done(function () {
                localStorage.removeItem('ordertype');
                location.reload();
              }).fail(function () {
                window.isSubmittingSale = false;
                alert('failed');
              });
              return;
            }

            if (buttonName === 'cmdprint') {
              $('#submit_type').val('print');
              $('<input>').attr({
                type: 'hidden',
                name: 'cmdprint',
                value: 'submit'
              }).appendTo(mainForm);
            } else if (buttonName === 'cmdprint1') {
              $('#submit_type').val('notprint_notredirect');
              $('<input>').attr({
                type: 'hidden',
                name: 'cmdprint1',
                value: 'submit'
              }).appendTo(mainForm);
            }

            mainForm.submit();
          }
          
          // Reset submission flag after form is submitted
          setTimeout(function() {
            window.isSubmittingSale = false;
          }, 500);
        },200);
      },
      error: function (xhr, status, error) {
        window.isSubmittingSale = false;
        alert("There was an error saving the data.");
        console.log("Error:", error);
      }
    });
  });

});

// exclude cash payment method : other payment method wise shown payment box and hide denomination box
document.addEventListener("DOMContentLoaded", function () {
  const excludedPayments = [
    "Cheque", "award_point", "deposit", "other", "gift_card", "NEFT", "DC", "CC", "payswiff", "PAYTM", "Googlepay", "magicpin",
    "complimentry", "UPI_QRCODE", "ubereats", "razorpay", "zomato", "paytm", "swiggy", "payumoney", "ccavenue", "authorize",
    "instamojo", "stripe", "ppp", "Pos", "Tabby", "Tamara", "Stripe_PM"
  ];

  const excludedPaymentsLower = excludedPayments.map(p => String(p).toLowerCase());

  // Select DOM elements
  const paymentRadios = document.querySelectorAll('input[name="colorRadio"]');
  const denominationContainer = document.getElementById(
    "denomination-containerr"
  );
  const clearButton = document.getElementById("clearAll");
  const amountOuterDiv = document.getElementById("yourDivId");
  const paymentBox = document.getElementById("payment-box");

  if (!denominationContainer || !clearButton || !amountOuterDiv) {
    console.error(
      "Error: #denomination-containerr, #clearAll or #yourDivId not found in the DOM."
    );
    return;
  }

  //Reset all denomination-related UI
  function resetDenominations() {
    // Reset count text to 0
    document.querySelectorAll(".denomination-box .count").forEach(el => {
      el.textContent = "0";
    });

    // Remove highlight classes
    document.querySelectorAll(".denomination-box.active").forEach(el => {
      el.classList.remove("active");
    });

    // Clear total amount display if exists
    const totalEl = document.getElementById("totalAmount");
    if (totalEl) {
      totalEl.textContent = "0.00";
    }

    // Clear localStorage if used
    localStorage.removeItem("selectedDenominations");

    // Optionally: trigger clear button if needed
    try {
      clearButton.click();
    } catch (e) {}
  }
  // shown correct balance and total paying after change payemnt method
  function updateBalance() {
    const twt = getCoinageInvoiceAmount();
    const isCash = typeof isCoinageCashPaymentSelected === 'function' && isCoinageCashPaymentSelected();

    if (!isCash && !getCashExtraPayTotal()) {
      $('#amount_1').val(twt);
    }
    if (isCash) {
      $("#amount_1").val(twt);
    }
    refreshCoinagePaymentTotals();
  }
  
  function toggleDenominationBox(selectedValueRaw, skipReset) {
    const selectedValue = String(selectedValueRaw || '').toLowerCase().trim();
    if (!skipReset && window._coinagePrevRail !== selectedValue) {
      resetCashExtraPayments();
    }
    window._coinagePrevRail = selectedValue;
    updateBalance();
    toggleCashExtraPaySlot(selectedValue.indexOf('cash') !== -1);
    if (excludedPaymentsLower.includes(selectedValue) || selectedValue.indexOf('other_') === 0) {
      denominationContainer.style.display = "none";
      clearButton.style.display = "none";

      amountOuterDiv.style.display = "block";
      paymentBox.style.setProperty("display", "none", "important");

      amountOuterDiv.style.position = "absolute";
      amountOuterDiv.style.top = "10px";
      amountOuterDiv.style.left = "-57rem";
      amountOuterDiv.style.width = "100%";

      amountOuterDiv.style.setProperty("display", "block", "important");

    } else {
      denominationContainer.style.display = "block";
      clearButton.style.display = "block";

      if (selectedValue.indexOf('cash') !== -1) {
        // Hide amountOuterDiv (#yourDivId) and show paymentBox
        if (!skipReset) {
          resetDenominations();
        }
        // Reset denominations when switching to cash
          setTimeout(() => {
          const twtValue = getCoinageInvoiceAmount();
          const currentCollected = parseFloat($("#depositedAmount").text().replace(/,/g, "")) || 0;
          $("#total_paying").text(formatNumber(twtValue)); 
          $("#balance").text(formatNumber(Math.abs(twtValue - currentCollected)));
        }, 5);
        amountOuterDiv.style.position = "relative";
        amountOuterDiv.style.top = "0px";
        amountOuterDiv.style.left = "0px";
        amountOuterDiv.style.width = "";

        amountOuterDiv.style.setProperty("display", "none", "important");
        paymentBox.style.setProperty("display", "block", "important");

      } else {
        const rect = denominationContainer.getBoundingClientRect();

        amountOuterDiv.style.position = "absolute";
        amountOuterDiv.style.top = `${rect.top + window.scrollY}px`;
        amountOuterDiv.style.left = "-38em";
        amountOuterDiv.style.width = `${rect.width}px`;

        amountOuterDiv.style.setProperty("display", "block", "important");
        paymentBox.style.setProperty("display", "none", "important");
      }
    }
  }

  window.coinageToggleDenominationBox = toggleDenominationBox;

  window.coinageSyncPaymentUi = function () {
    const checkedRadio = document.querySelector('#paymentModal input[name="colorRadio"]:checked');
    if (checkedRadio) {
      toggleDenominationBox(checkedRadio.value, true);
    }
  };

  // Dynamic payment radios (loaded via API) — delegated change
  document.addEventListener('change', function (e) {
    if (!e.target || e.target.name !== 'colorRadio') {
      return;
    }
    if (!e.target.closest || !e.target.closest('#paymentModal')) {
      return;
    }
    toggleDenominationBox(e.target.value);
  });

  // CRM Button Click Handler
  $('#crmButton').click(function() {
    var getname = $('#customer_name').val();
    if (!getname) {
      alert('No customer selected');
      return;
    }
    var phoneNumber = getname.match(/\((\d+)\)/);
    if (!phoneNumber) {
      alert('Customer phone not found');
      return;
    }
    phone = phoneNumber[1];
    customer_details(phone);
    $('#crmOverlay').show();
    $('#modal-1').css('z-index', '5000').modal('show');
  });

  // Hide CRM overlay when modal is hidden
  $('#modal-1').on('hidden.bs.modal', function () {
    $('#crmOverlay').hide();
  });
});
