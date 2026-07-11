/* Restaurant Order Screen - extracted scripts */
(function(window, $){
  'use strict';

  // Ensure namespace
  window.site = window.site || {};
  window.site.vars = window.site.vars || {};
  window.site.settings = window.site.settings || {};

  // Utils
  function debounce(fn, wait){
    var t; return function(){
      var ctx = this, args = arguments; clearTimeout(t);
      t = setTimeout(function(){ fn.apply(ctx, args); }, wait);
    };
  }

  function markServed(){
    var $btn = $('[data-action="mark-served"]').first();
    var original = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Serving...');
    $.ajax({
      url: getBaseUrl() + 'restaurant_order_taking/mark_served',
      type: 'POST',
      data: { order_id: (window.site && window.site.vars && window.site.vars.orderId) },
      dataType: 'json'
    }).done(function(resp){
      if (resp && resp.status === 'success') {
        showAlert('success', 'Order marked as Served');
        setTimeout(function(){ location.reload(); }, 600);
      } else {
        showAlert('error', (resp && resp.message) || 'Failed to mark Served');
      }
    }).fail(function(){
      showAlert('error', 'Network error');
    }).always(function(){
      $btn.prop('disabled', false).html(original);
    });
  }

  

  // Read base_url from existing global or infer from document
  function getBaseUrl(){
    if (window.site && window.site.base_url) return window.site.base_url.replace(/\/?$/, '/');
    var base = $('base').attr('href') || '/';
    return base.replace(/\/?$/, '/');
  }

  // CSRF helpers (expects meta tags to be present in the HTML)
  function getCsrf(){
    return {
      name: $('meta[name="ci-csrf-name"]').attr('content') || '',
      hash: $('meta[name="ci-csrf-hash"]').attr('content') || ''
    };
  }

  function setupAjaxCsrf(){
    var csrf = getCsrf();
    if (!csrf.name || !csrf.hash) return; // graceful if not provided
    $.ajaxSetup({
      beforeSend: function(xhr, settings){
        if ((settings.type || '').toUpperCase() === 'POST'){
          if (typeof settings.data === 'string') {
            settings.data += (settings.data ? '&' : '') +
              encodeURIComponent(csrf.name) + '=' + encodeURIComponent(csrf.hash);
          } else if (settings.data && typeof settings.data === 'object') {
            settings.data[csrf.name] = csrf.hash;
          } else {
            settings.data = encodeURIComponent(csrf.name) + '=' + encodeURIComponent(csrf.hash);
          }
        }
      }
    });
  }

  // Set the X-Requested-With header for all AJAX requests
  $.ajaxSetup({
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  });

  // Global AJAX error handler to catch non-JSON responses
  $(document).ajaxError(function(event, jqXHR, settings, thrownError) {
    if (jqXHR.responseText && jqXHR.responseText.trim().startsWith('<!DOCTYPE')) {
      // This is likely an HTML error page
      showAlert('error', 'Server returned an error page. Please check the console for details.');
      console.error('AJAX Error: Server returned HTML instead of JSON. URL: ' + settings.url + '\nResponse: ' + jqXHR.responseText.substring(0, 500));
    }
  });

  // Bootstrap from DOM attributes
  function bootstrapFromDom(){
    var $root = $(document.body);
    var oid = $root.data('order-id');
    if (oid && !window.site.vars.orderId) { window.site.vars.orderId = oid; }
  }

  // UI helpers
  function showAlert(type, message){
    // Prefer Bootstrap toast/alert if present
    if (window.toastr && toastr[type]) { toastr[type](message); return; }
    console[(type === 'error' ? 'error' : 'log')](message);
  }
  function showPopup(message, type){ showAlert(type === 'error' ? 'error' : 'info', message); }

  // State
  var selectedGuestId = 'all';
  var selectedCategoryId = 'all';
  var selectedSpiceLevel = 'medium';
  var activeFilters = { 'meal-type': [], 'allergy': [], 'spice-level': [] };

  // iCheck init (if available)
  function initICheck(){
    if (typeof $.fn.iCheck === 'function') {
      $('input[type="checkbox"].icheck, input[type="radio"].icheck').not('.icheckbox_minimal, .iradio_minimal').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%'
      });
    }
  }

  // Event handlers
  function initEventHandlers(){
    // Inline handlers are used for guest tabs, categories, and search input

    // Filter badges
    $(document).off('click.order.filterbadge', '.filter-badge').on('click.order.filterbadge', '.filter-badge', function(){
      var filterType = $(this).data('filter');
      var filterValue = $(this).data('value');
      $(this).toggleClass('active');
      if ($(this).hasClass('active')) {
        if (activeFilters[filterType].indexOf(filterValue) === -1) activeFilters[filterType].push(filterValue);
      } else {
        activeFilters[filterType] = activeFilters[filterType].filter(function(v){ return v !== filterValue; });
      }
      loadMenuItems();
    });

    // Inline oninput handles search updates

    // Custom allergy Enter to add
    $(document).off('keydown.order.allergy', '#custom-allergy-input').on('keydown.order.allergy', '#custom-allergy-input', function(e){
      if (e.key === 'Enter') { e.preventDefault(); addCustomAllergy(); }
    });

    // Offcanvas toggle button alias
    $(document).off('click.order.carttoggle', '.cart-toggle').on('click.order.carttoggle', '.cart-toggle', function(e){ e.preventDefault(); toggleCart(); });

    // Generic data-action handlers (no inline onclicks in HTML)
    $(document).off('click.order.actions', '[data-action]').on('click.order.actions', '[data-action]', function(e){
      var action = $(this).data('action');
      if (action === 'increase-guest') { e.preventDefault(); return increaseGuest(); }
      if (action === 'decrease-guest') { e.preventDefault(); return decreaseGuest(); }
      if (action === 'toggle-cart') { e.preventDefault(); return toggleCart(); }
      if (action === 'finalize-order') { e.preventDefault(); return (typeof completeAndFree === 'function' ? completeAndFree(true) : (window.finalizeOrder && window.finalizeOrder(true))); }
      if (action === 'close-table') { e.preventDefault(); return (typeof closeTable === 'function' ? closeTable() : undefined); }
      if (action === 'mark-served') { e.preventDefault(); return markServed(); }
    });

    // Re-init after AJAX loads
    $(document).ajaxComplete(function(){ initICheck(); });
  }

  // Public functions expected by HTML
  function markTableOccupied(tableId){
    if (!tableId) return;
    $.post(getBaseUrl() + 'restaurant_order_taking/mark_occupied', { table_id: tableId })
      .always(function(){ /* no-op */ });
  }
  function toggleCart(){
    var el = document.getElementById('offcanvasCart');
    if (!el) return;
    var off = new bootstrap.Offcanvas(el);
    off.show();
    var $container = $('#offcanvas-cart-container');
    if ($container.length) $container.html($('#order-items-container').html());
  }

  function backToTables(){ window.location.href = getBaseUrl() + 'restaurant_order_taking/tables'; }

  function increaseGuest(){
    $.ajax({
      url: getBaseUrl() + 'restaurant_order_taking/increase_guest',
      type: 'POST', data: { order_id: window.site.vars.orderId }, dataType: 'json'
    }).done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showAlert('error', (resp && resp.message) || 'Failed to add guest'); })
      .fail(function(){ showAlert('error', 'Error adding guest'); });
  }

  function decreaseGuest(){
    if (!confirm('Are you sure you want to remove a guest? This cannot be undone.')) return;
    $.ajax({
      url: getBaseUrl() + 'restaurant_order_taking/decrease_guest',
      type: 'POST', data: { order_id: window.site.vars.orderId }, dataType: 'json'
    }).done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showAlert('error', (resp && resp.message) || 'Cannot remove guest with items'); })
      .fail(function(){ showAlert('error', 'Error removing guest'); });
  }

  function updateQuantity(itemId, change){
    var d = (window.site.settings && (window.site.settings.qty_decimals || window.site.settings.qty_decimals === 0)) ? parseInt(window.site.settings.qty_decimals) : 0;
    var step = d > 0 ? 1 / Math.pow(10, d) : 1;
    var currentQty = parseFloat($(".order-item[data-item-id="+itemId+"] .quantity-control span").text());
    if (isNaN(currentQty)) currentQty = 1;
    var newQty = currentQty + (change * step);
    if (newQty <= 0) { removeItem(itemId); return; }
    newQty = parseFloat(newQty.toFixed(d));
    $.ajax({
      url: getBaseUrl() + 'restaurant_order_taking/update_item',
      type: 'POST', data: { item_id: itemId, quantity: newQty }, dataType: 'json'
    }).done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showPopup((resp && resp.message) || 'Failed to update quantity', 'error'); })
      .fail(function(){ showPopup('Network error while updating quantity', 'error'); });
  }

  function removeItem(itemId){
    if (!confirm('Are you sure you want to remove this item?')) return;
    $.ajax({
      url: getBaseUrl() + 'restaurant_order_taking/remove_item',
      type: 'POST', data: { item_id: itemId }, dataType: 'json'
    }).done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showPopup((resp && resp.message) || 'Failed to remove item', 'error'); })
      .fail(function(){ showPopup('Network error while removing item', 'error'); });
  }

  function selectGuest(guestId){
    selectedGuestId = guestId;
    $('.guest-tab').removeClass('active');
    $('.guest-tab[data-guest-id="'+guestId+'"]').addClass('active');
    var $tab = $('.guest-tab[data-guest-id="'+guestId+'"]').first();
    var label = (guestId === 'all') ? 'All Guests' : ('Guest ' + ($tab.data('guest-number')));
    $('#apply-to-label, #footer-guest-label, #modal-guest-label').text(label);
    loadMenuItems();
  }

  function selectCategory(categoryId){
    selectedCategoryId = categoryId;
    $('.menu-category').removeClass('active');
    $('.menu-category[data-category-id="'+categoryId+'"]').addClass('active');
    loadMenuItems();
  }

  function toggleFilter(el){ $(el).toggleClass('active'); loadMenuItems(); }

  function completeAndFree(confirmFirst){
    if (confirmFirst === undefined) confirmFirst = true;
    var confirmMessage = 'Are you sure you want to complete and free this table? This will finalize the order and make the table available.';
    if (confirmFirst && !confirm(confirmMessage)) return;
    var $btn = $('.btn-complete-free, .btn-complete').first();
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Completing...');
    $.ajax({
      url: getBaseUrl() + 'restaurant_order_taking/complete_and_free',
      type: 'POST', data: { order_id: (window.site.vars && window.site.vars.orderId) || $(document.body).data('order-id') }, dataType: 'json', timeout: 30000
    }).done(function(response){
      if (response && (response.success || response.status === 'success')){
        showAlert('success', response.message || 'Table completed and freed successfully!');
        setTimeout(function(){ window.location.href = response.redirect_url || (getBaseUrl() + 'restaurant_order_taking/tables'); }, 1000);
      } else {
        showAlert('error', (response && response.message) || 'Failed to complete and free table');
        $btn.prop('disabled', false).html(originalText);
      }
    }).fail(function(xhr, status, error){
      showAlert('error', error || 'Request failed');
      $btn.prop('disabled', false).html(originalText);
      console.error('complete_and_free Error:', { status: xhr.status, statusText: xhr.statusText, response: xhr.responseText, error: error });
    });
  }

  // Stubs for functions referenced elsewhere (actual logic may live in other files)
  function loadMenuItems(){ $(document).trigger('order_screen:load_menu', { selectedGuestId: selectedGuestId, selectedCategoryId: selectedCategoryId, filters: activeFilters, query: $('#menu-search').val()||'' }); }
  function addCustomAllergy(){ $(document).trigger('order_screen:add_custom_allergy'); }
  function syncPresetAllergies(){ $(document).trigger('order_screen:sync_allergies'); }
  function collectSelectedIds(){ return []; }
  function recalcSelectedPrice(){ $(document).trigger('order_screen:recalc_price'); }

  // Expose to window as required by inline attributes
  window.toggleCart = window.toggleCart || toggleCart;
  window.backToTables = window.backToTables || backToTables;
  window.increaseGuest = window.increaseGuest || increaseGuest;
  window.decreaseGuest = window.decreaseGuest || decreaseGuest;
  window.updateQuantity = window.updateQuantity || updateQuantity;
  window.removeItem = window.removeItem || removeItem;
  window.selectGuest = window.selectGuest || selectGuest;
  window.selectCategory = window.selectCategory || selectCategory;
  window.toggleFilter = window.toggleFilter || toggleFilter;
  window.completeAndFree = window.completeAndFree || completeAndFree;
  window.markServed = window.markServed || markServed;
  window.markTableOccupied = window.markTableOccupied || markTableOccupied;
  // Back-compat alias: some HTML still calls finalizeOrder()
  window.finalizeOrder = window.finalizeOrder || function(confirmFirst){ return completeAndFree(confirmFirst); };

  // Initialize
  $(function(){
    bootstrapFromDom();
    setupAjaxCsrf();
    initICheck();
    initEventHandlers();
    // Optionally auto-mark occupied if the page sets these vars
    try {
      if (window.site && window.site.vars && window.site.vars.autoMarkOccupied && window.site.vars.tableId){
        markTableOccupied(window.site.vars.tableId);
      }
    } catch(_e) { /* ignore */ }
    // Initial load
    loadMenuItems();
    // Fade out any loading overlay if present
    $(window).on('load', function(){ $('#loading').fadeOut('slow'); });
  });

})(window, window.jQuery);
