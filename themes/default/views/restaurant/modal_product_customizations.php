<style>
    /* Modal visual makeover to match target UI (Original Styles) */
    .section-card {
        border: 1px solid #e2e2e2;
        border-radius: 10px;
        padding: 10px;
        background: #fff;
        margin-bottom: 12px;
    }

    .section-header {
        font-weight: 600;
        margin-bottom: 8px;
        color: #2c3e50;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        border-radius: 16px;
        padding: 6px 12px;
        margin: 4px;
        cursor: pointer;
        background: #fff;
        color: #333;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .chip.active {
        border-color: #e9176b;
        background: #fff0f5;
        color: #e9176b;
    }

    .chip.active i {
        color: #e9176b;
    }
    
    .chip::before {
        content: '\2713';
        display: inline-block;
        width: 16px;
        height: 16px;
        line-height: 16px;
        font-size: 12px;
        text-align: center;
        color: transparent;
        border: 1px solid #bbb;
        border-radius: 3px;
        margin-right: 4px;
    }

    .chip.active::before {
        color: #fff;
        background: #e9176b;
        border-color: #e9176b;
    }

    .spice-level {
        display: flex;
        gap: 10px;
        margin-top: 5px;
    }

    .spice-btn {
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 6px 14px;
        cursor: pointer;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .spice-btn.active {
        background: #fff0f5;
        border-color: #e9176b;
        color: #e9176b;
    }

    .modal-sticky-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #eee;
        padding: 16px;
        margin: 0 -16px -16px -16px; /* Negative margin to stretch full width of modal-body pad */
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 100;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.05);
    }
    
    .qty-control {
        display: flex;
        align-items: center;
        border: 1.5px solid #e9176b;
        border-radius: 8px;
        padding: 2px;
        background: #fff;
        width: fit-content;
        min-width: 100px;
        justify-content: space-between;
    }
    .qty-btn {
        width: 32px;
        height: 32px;
        border: none !important;
        background: #e9176b !important;
        color: #fff !important;
        border-radius: 4px !important;
        font-size: 1.5rem !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0 !important;
        margin: 0 !important;
    }
    .qty-btn:hover { background: #d00f5c !important; }
    .qty-val {
        width: 40px;
        text-align: center;
        font-size: 1.2rem;
        font-weight: 700;
        border: none;
        color: #000;
        background: transparent;
        outline: none;
    }

    /* Allergies specific */
    .allergy-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .modal-sticky-footer {
            flex-direction: column;
            gap: 12px;
        }
        .qty-control, .btn-add-item {
            width: 100%;
            justify-content: center;
        }
        .btn-add-item { width: 100%; }
        .footer-actions { width: 100%; display: flex; gap: 10px; }
        .footer-actions .btn { flex: 1; }
    }

    /* ===== OPTIMIZED TOPPINGS LIST UI ===== */
    /* Target only toppings and addons for the clean list look */
    #toppings-container, #addons-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
        margin: 0 -5px; /* Offset padding */
    }

    #toppings-container .chip,
    #addons-container .chip {
        width: 50%; /* 2 columns */
        background: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 10px 10px !important;
        margin: 0 !important;
        justify-content: space-between; /* Text left, Checkbox right */
        color: #333;
        font-size: 1rem;
        box-shadow: none !important;
        align-items: center;
        display: flex;
    }
    
    /* Hover effect for better UX */
    #toppings-container .chip:hover,
    #addons-container .chip:hover {
        background-color: #f9f9f9 !important;
    }

    /* Hide the old left-side checkbox */
    #toppings-container .chip::before,
    #addons-container .chip::before {
        display: none !important;
    }

    /* Create new right-side checkbox using ::after */
    #toppings-container .chip::after,
    #addons-container .chip::after {
        content: '';
        width: 22px;
        height: 22px;
        border: 2px solid #e9176b; /* Pink border */
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
        font-size: 14px;
        margin-left: 10px;
        transition: all 0.2s;
        background: #fff;
    }

    /* Checked State */
    #toppings-container .chip.active::after,
    #addons-container .chip.active::after {
        border-color: #22c55e; /* Green border */
        background-color: #fff; /* White background */
        content: '\2713'; /* Checkmark */
        color: #22c55e; /* Green checkmark */
    }

    /* ===== IMPROVED ALLERGY UI ===== */
    .allergy-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(30%, 1fr));
        gap: 12px 15px;
    }
    
    /* Hide allergies container initially */
    #allergies-container {
        display: none;
    }
    
    .allergy-chip {
        display: flex;
        align-items: center;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        font-weight: 500;
        cursor: pointer;
        color: #333;
    }

    /* checkbox box */
    .allergy-chip::before {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #e9176b; /* Pink border */
        border-radius: 4px;
        margin-right: 8px;
        flex-shrink: 0;
        transition: all 0.2s;
        background: #fff;
    }

    /* active state */
    .allergy-chip.active::before {
        background-color: #e9176b;
        content: '!'; /* Using exclamation or check based on design, design shows some '!' */
        color: #fff;
        text-align: center;
        line-height: 16px;
        font-weight: bold;
        font-family: sans-serif;
    }
    
    /* Hover */
    .allergy-chip:hover {
        opacity: 0.8;
    }

    .toggle-item {
    display: inline-flex;
    align-items: center;
    margin-right: 10px;
    gap: 5px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    background-color: #ccc;
    border-radius: 20px;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transition: 0.3s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}

/* ON State */
.switch input:checked + .slider {
    background-color: #28a745;
}

.switch input:checked + .slider:before {
    transform: translateX(20px);
}
</style>

<div id="product-customization-content">
    
    <!-- Hidden inputs for backend -->
    <input type="hidden" id="cust-product-id" value="<?= $product_id ?>">
    <input type="hidden" id="cust-toppings" value="">
    <input type="hidden" id="cust-addons" value="">
    <input type="hidden" id="cust-spice" value="">
    <input type="hidden" id="cust-allergies" value="">
    <input type="hidden" id="cust-custom-allergies" value="">
    
    <!-- Toppings -->
    <div class="section-card">
        <div class="section-header">Toppings</div>
        <div id="toppings-container" class="d-flex flex-wrap">
            <?php if (!empty($toppings)): ?>
                <?php foreach ($toppings as $topping): ?>
                    <span class="chip" data-id="<?= $topping->id ?>" data-price="<?= $topping->price ?>" onclick="toggleChip(this)">
                        <?= $topping->name ?> 
                        <?php if($topping->price > 0): ?>
                            <small class="text-muted ms-1">(<?= $this->sma->formatMoney($topping->price) ?>)</small>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted small">No toppings available.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Ons -->
    <div class="section-card">
        <div class="section-header">Add Ons</div>
        <div id="addons-container" class="d-flex flex-wrap">
            <?php if (!empty($add_ons)): ?>
                <?php foreach ($add_ons as $addon): ?>
                    <span class="chip" data-id="<?= $addon->id ?>" data-price="<?= $addon->price ? $addon->price : $addon->amount ?>" onclick="toggleChip(this)">
                        <?= $addon->name ?>
                        <small class="text-muted ms-1">(<?= $this->sma->formatMoney($addon->price ? $addon->price : $addon->amount) ?>)</small>
                    </span>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted small">No add-ons available.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cooking Instructions -->
    <div class="section-card">
        <div class="section-header">Additional cooking instructions</div>
        
        <!-- Spice & Meat Row -->
        <div class="row g-3">
            <div class="col-md-7">
                <!-- <label class="form-label small text-muted">Spice Level</label> -->
                <div class="spice-level">
                    <div class="toggle-item">
                        <img src="<?= base_url('themes/default/assets/restaurant/images/garlic_icon.svg'); ?>">
                        <label class="switch">
                            <input type="checkbox" id="cust-garlic" name="garlic_flag" value="1" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Onion Toggle -->
                    <div class="toggle-item">
                        <img src="<?= base_url('themes/default/assets/restaurant/images/onion_icon.svg'); ?>">
                        <label class="switch">
                            <input type="checkbox" id="cust-onion" name="onion_flag" value="1" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="spice-btn" data-spice="mild" onclick="selectSpice(this)">
                        <img 
                            src="<?= base_url('themes/default/assets/restaurant/images/low_spicy_icon.svg'); ?>" 
                            alt="Mild Spicy">
                    </div>

                    <div class="spice-btn" data-spice="medium" onclick="selectSpice(this)">
                        <img 
                            src="<?= base_url('themes/default/assets/restaurant/images/medium_spicy_icon.svg'); ?>" 
                            alt="Medium Spicy">
                    </div>
                    <div class="spice-btn" data-spice="hot" onclick="selectSpice(this)">
                        <img 
                            src="<?= base_url('themes/default/assets/restaurant/images/high_spicy_icon.svg'); ?>" 
                            alt="Hot Spicy">
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <!-- <label class="form-label small text-muted">Meat Preparation</label> -->
                <select class="form-select" id="cust-meat-wellness">
                    <option value="">Not Applicable</option>
                    <?php if(!empty($meat_wellness)): ?>
                        <?php foreach($meat_wellness as $mw): ?>
                            <option value="<?= $mw->id ?>"><?= $mw->type ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Allergies Section (Merged) -->
        <div class="doc-line d-flex justify-content-between align-items-center mb-2 mt-4">
            <div class="d-flex align-items-center allergy-toggle" onclick="toggleAllergies()" style="cursor:pointer;">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2"></i>
                <div class="section-header mb-0" style="font-size:1rem;">Allergies</div>
            </div>
            <!-- Circular Add Button -->
            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center" 
                    style="width: 32px; height: 32px; border-color: #e9176b; color: #e9176b;"
                    onclick="$('#custom-allergy-overlay').css('display', 'flex'); $('#modal-allergy-input').val('').focus();">
                <i class="bi bi-plus fs-4"></i>
            </button>
        </div>
        
        <div class="allergy-list" id="allergies-container">
            <?php if(!empty($allergies)): ?>
                <?php foreach($allergies as $allergy): ?>
                    <span class="chip allergy-chip" data-id="<?= $allergy->id ?>" onclick="toggleAllergy(this)">
                        <?= $allergy->name ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Old Allergies Section Removed -->

    <!-- Special Instructions -->
    <!-- <div class="section-card">
        <div class="section-header">Special Instructions</div>
        <textarea class="form-control" id="cust-instructions" rows="2" placeholder="E.g. extra crispy, pack separately..."></textarea>
    </div> -->

    <!-- Sticky Footer -->
    <div class="modal-sticky-footer">
        <div class="qty-control">
            <button type="button" class="qty-btn" onclick="updateCustQty(-1)">-</button>
            <input type="text" class="qty-val" id="cust-qty" value="1" readonly>
            <button type="button" class="qty-btn" onclick="updateCustQty(1)">+</button>
        </div>
             <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> -->
        
        <div class="footer-actions d-flex align-items-center gap-3">
            <button type="button"class="btn btn-primary btn-add-item" style="background: #e9176b; border:none;white-space: nowrap; height:3.2rem; text-align:center; margin: 0 auto; display: flex; align-items: center; justify-content: center; padding: 0 14rem;"
            id="cust-action-btn" onclick="addItemToOrder()">Add Item (<span id="cust-total-price"></span>)
            </button>
        </div>
    </div>

</div>

<!-- Custom Allergy Overlay Modal -->
<div id="custom-allergy-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; width:90%; max-width:400px; border-radius:12px; padding:20px; box-shadow:0 10px 25px rgba(0,0,0,0.2); animation: popIn 0.2s ease;">
        <h6 style="font-weight:700; margin:0 0 15px 0; color:#000; font-size:1rem;">Add allergies</h6>
        <textarea id="modal-allergy-input" rows="5" style="width:100%; border:1px solid #ddd; border-radius:8px; padding:12px; resize:none; outline:none; font-size:1rem; color:#333;" placeholder=""></textarea>
        
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
            <button type="button" onclick="$('#custom-allergy-overlay').hide()" style="border:1px solid #e9176b; color:#e9176b; background:#fff; padding:6px 20px; border-radius:6px; font-weight:500; font-size:0.9rem;">Cancel</button>
            <button type="button" onclick="confirmCustomAllergy()" style="border:none; background:#e9176b; color:#fff; padding:6px 25px; border-radius:6px; font-weight:500; font-size:0.9rem;">Add</button>
        </div>
    </div>
</div>

<style>
@keyframes popIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
#modal-allergy-input:focus { border-color: #e9176b !important; }
</style>
<script>
        $('input[name="no_garlic"], input[name="no_onion"]').on('change', function() {
        console.log($(this).attr('name') + ":", $(this).is(':checked'));
    });
    function toggleAllergies() {
        var section = $('#allergies-container');
        if (section.css('display') === 'none') {
            section.css('display', 'grid');
        } else {
            section.hide();
        }
    }
</script>
<script>
    var basePrice = 0;
    var currentSymbol = '';

    // Init from window global if available (called by openQuickAdd)
    window.setCustBasePrice = function(price, symbol) {
        basePrice = parseFloat(price) || 0;
        calculateTotal();
    };

    function toggleChip(el) {
        $(el).toggleClass('active');
        syncOptions();
        calculateTotal();
    }

    function syncOptions() {
        var toppings = [];
        $('#toppings-container .chip.active').each(function(){
            toppings.push($(this).data('id'));
        });
        $('#cust-toppings').val(toppings.join(','));

        var addons = [];
        $('#addons-container .chip.active').each(function(){
            addons.push($(this).data('id'));
        });
        $('#cust-addons').val(addons.join(','));
    }

    function selectSpice(el) {
        $('.spice-btn').removeClass('active');
        $(el).addClass('active');
        $('#cust-spice').val($(el).data('spice'));
    }

    function toggleAllergy(el) {
        $(el).toggleClass('active');
        syncAllergies();
    }

    function syncAllergies() {
        var ids = [];
        $('#allergies-container .allergy-chip.active').each(function(){
            ids.push($(this).data('id'));
        });
        $('#cust-allergies').val(ids.join(','));
    }

    function confirmCustomAllergy() {
        var val = $('#modal-allergy-input').val().trim();
        if(!val) {
             $('#modal-allergy-input').focus();
             return;
        }
        
        // Add chip visual matching existing allergy chips
        $('#allergies-container').append(
            `<span class="chip allergy-chip active custom-allergy" data-val="${val.replace(/"/g, '&quot;')}" onclick="$(this).remove(); syncCustomAllergies();">
                ${val}
            </span>`
        );
        $('#custom-allergy-overlay').hide();
        $('#modal-allergy-input').val('');
        syncCustomAllergies();
    }

    function syncCustomAllergies() {
        var vals = [];
        $('#allergies-container .custom-allergy').each(function(){
            vals.push($(this).data('val'));
        });
        $('#cust-custom-allergies').val(vals.join('|'));
    }

    function updateCustQty(delta) {
        var el = $('#cust-qty');
        var val = parseInt(el.val()) || 1;
        val += delta;
        if(val < 1) val = 1;
        el.val(val);
        calculateTotal();
    }

    function calculateTotal() {
        var total = basePrice;
        
        // Add toppings price
        $('#toppings-container .chip.active').each(function(){
            var p = parseFloat($(this).data('price')) || 0;
            total += p;
        });

        // Add options price
        $('#addons-container .chip.active').each(function(){
            var p = parseFloat($(this).data('price')) || 0;
            total += p;
        });

        var qty = parseInt($('#cust-qty').val()) || 1;
        total = total * qty;

        // Format money (basic fallback)
        var formatted = total.toFixed(2);
        if(window.sma && window.sma.formatMoney) {
            formatted = window.sma.formatMoney(total);
        }
        $('#cust-total-price').html(formatted);
    }
    
    // Auto-select Medium spice if none
    if(!$('#cust-spice').val()) {
        $('.spice-btn[data-spice="medium"]').trigger('click');
    }
    
</script>
