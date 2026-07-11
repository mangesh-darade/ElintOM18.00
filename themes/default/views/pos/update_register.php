<style>
.grid-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: flex-start;
}

.denomination-box1 {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    width: calc(33.33% - 10px);
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    position: relative;
}

.count1er {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px;
    gap: 10px;
}

.count1er input {
    width: 60px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 1px;
    font-size: 16px;
}

.count1er button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 3px 9px;
    cursor: pointer;
    border-radius: 4px;
    margin-left: -8px;
    margin-right: -9px; 
}

.count1er button:hover {
    background-color: #0056b3;
}

.btn-remove-denom {
    position: absolute;
    bottom: -13px;
    left: 48%;
    transform: translateX(-50%);
    background: red;
    color: white;
    border: none;
    padding: 1px 6px;
    border-radius: 50%;
    cursor: pointer;
    display: none;
}
@media (min-width: 992px) {
    .col-md-5 {
        width: 52.666667%;
    }
}
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">
                <?= lang('update_register') . ' (' . $this->sma->hrld($register_open_time ? $register_open_time : $this->session->userdata('register_open_time')) . ' - ' . $this->sma->hrld(date('Y-m-d H:i:s')) . ')'; ?>
            </h4>
        </div>

        <?php
      $attrib = array('data-toggle' => 'validator', 'role' => 'form');
      echo form_open_multipart("pos/update_register/" . $user_id, $attrib);
    ?>
          <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
          <div class="box-content">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong><?= lang('Denominations') ?></strong></div>
                            <div class="panel-body" id="update-denomination-container">
                                <p>No denominations added yet.</p>
                            </div>
                        </div>
                     
                    </div>
                    <div class="col-md-6">
                        <div class="well well-sm">
                            <div class="form-group">
                                <?= lang('cash_in_hand', 'cash_in_hand') ?>
                                <?= form_input('cash_in_hand',  $this->sma->formatMoney($cash_in_hand->cash_in_hand,2), 'id="cash_in_hand1" class="form-control" readonly'); ?>
                            </div>


                            <!-- Transaction Type Dropdown  -->
                            <div class="form-group">
                                <label for="transaction_type"><?= lang('Transaction_Type') ?> <span
                                        class="text-danger">*</span></label>
                                <select name="transaction_type" id="transaction_type" class="form-control" required>
                                    <option value=""><?= lang('Select_Transaction_Type') ?></option>
                                    <?php if (!empty($transaction_types)) {
                                              foreach ($transaction_types as $type) {
                                                  $selected = set_value('transaction_type') == $type->id ? 'selected' : '';
                                                  echo '<option value="' . $type->id . '" ' . $selected . '>' . $type->type . '</option>';
                                              }
                                          } ?>
                                </select>
                                <?= form_error('transaction_type', '<small class="text-danger">', '</small>'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('Notes', 'Notes') ?>
                                <textarea name="register_note" id="register_note" class="form-control" rows="3"></textarea>
                            </div> 

                            <!-- End Transaction Type Dropdown -->
                            <div class="modal-footer no-print">
                            <button type="button" id="clear_all_denominations1"
                            class="btn btn-warning"><?= lang('Reset') ?></button>
                            <button type="button" class="btn btn-primary" id="update_register"><?= lang('Update_Register') ?></button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?= form_close(); ?>
    </div>
</div>
                                        </div>

<?= $modal_js ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


<script type="text/javascript">
// Currency settings from system
var currencySymbol = '<?= $currency_symbol ?>';
var displaySymbol = '<?= $display_symbol ?>';

function fetchDenomination() {
    $.ajax({
        type: "GET",
        url: site.base_url + "pos/get_denominations",
        dataType: "json",
        success: function(data) {
            renderDenominations(data);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
}

function renderDenominations(denominations) {
    const container = $("#update-denomination-container");
    container.empty();

    // Group by denomination type
    const grouped = {};
    denominations.forEach(item => {
        if (!grouped[item.type]) grouped[item.type] = [];
        grouped[item.type].push(item);
    });

    // Create sections for each type
    for (const [type, items] of Object.entries(grouped)) {
        container.append(`<h3>${type}</h3>`);
        const grid = $('<div class="grid-container"></div>');

        items.forEach(item => {
            const originalCount = item.count || 0;
            const box = $(`
                <div class="denomination-box1" data-type="${type}" data-value="${item.currency_value}">
                    <span class="denom-label">${formatMoney(item.currency_value)}</span>
                    <div class="count1er">
                        <button type="button" class="btn-decrease-denom">-</button>
                        <input type="number" class="count1" 
                               data-value="${item.currency_value}"
                               value="${originalCount}"
                               min="0"
                               data-original-count="${originalCount}">
                        <button type="button" class="btn-increase-denom">+</button>
                    </div>
                    <button type="button" class="btn-remove-denom" style="display: none">✖</button>
                </div>
            `);
            grid.append(box);
        });
        container.append(grid);
    }
    updateCashInHand();
}


function formatMoney(amount) {
    amount = parseFloat(amount);
    if (isNaN(amount)) {
        return 'Invalid amount';
    }
    let formattedAmount = amount.toFixed(2);
    formattedAmount = formattedAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    // Apply currency symbol based on system settings
    if (displaySymbol == 1) {
        // Symbol before amount
        return currencySymbol + ' ' + formattedAmount;
    } else if (displaySymbol == 2) {
        // Symbol after amount
        return formattedAmount + ' ' + currencySymbol;
    } else {
        // No symbol (disabled)
        return formattedAmount;
    }
}
$(document).on('change', '.count1', function() {
    let box = $(this).closest(".denomination-box1");
    box.find(".btn-remove-denom").show();
    box.css("background", "lightgreen");
    updateCashInHand();
});

function updateCashInHand() {
    let total = 0;
    
    // Group counts by denomination value first
    const denominationTotals = {};
    
    $(".count1").each(function() {
        const value = parseFloat($(this).data("value")) || 0;
        const quantity = parseInt($(this).val()) || 0;
        
        if (!denominationTotals[value]) {
            denominationTotals[value] = 0;
        }
        denominationTotals[value] += quantity;
    });

    // Calculate total by multiplying each denomination's total count by its value
    for (const [value, totalCount] of Object.entries(denominationTotals)) {
        total += (parseFloat(value) * totalCount);
    } 
    $("#cash_in_hand1").val(formatMoney(total));
}

$(document).ready(function() {
    fetchDenomination();

    $(document).on("click", ".btn-increase-denom", function() {
        let box = $(this).closest(".denomination-box1");
        let input = $(this).siblings(".count1");
        input.val(parseInt(input.val()) + 1);
        box.find(".btn-remove-denom").show();
        box.css("background", "lightgreen");
        updateCashInHand();
    });

   
    $(document).on("click", ".btn-decrease-denom", function() {
    let box = $(this).closest(".denomination-box1");
    let input = $(this).siblings(".count1");

    let newValue = Math.max(0, parseInt(input.val()) - 1);
    input.val(newValue);

    box.find(".btn-remove-denom").removeClass("hidden").show();

    if (newValue === 0) {
        // Remove highlight if count is 0
        box.css("background", "#f9f9f9");
        box.find(".btn-remove-denom").hide();
    } else {
        // Highlight if count > 0
        box.css("background", "lightgreen");
        box.find(".btn-remove-denom").removeClass("hidden").show();
    }

    updateCashInHand();
});


    $(document).on("click", ".btn-remove-denom", function () {
    let box = $(this).closest(".denomination-box1");
    let countInput = box.find(".count1");
    let originalCount = parseInt(countInput.data("original-count")) || 0;

    countInput.val(originalCount);
    $(this).hide();
    box.css("background", "#f9f9f9");

    updateCashInHand();
});
$(document).on('input', '.count1', function () {
    let val = parseInt($(this).val(), 10);
    if (isNaN(val) || val < 0) {
        $(this).val(0);
    }
    updateCashInHand();
});

$("#clear_all_denominations1").click(function() {
    $(".denomination-box1").each(function() {
        var originalCount = $(this).find(".count1").data("original-count");
        $(this).find(".count1").val(originalCount);
        $(this).find(".btn-remove-denom").hide();
        $(this).css("background", "#f9f9f9");
    });
    updateCashInHand(); // Make sure this is called after resetting
});

$(function() {
    $('#myModal').on('hidden.bs.modal', function () {
        location.reload();
    });
});
    $('#update_register_button').click(function() {
        $('#myModal').load('<?= site_url("pos/update_register") ?>', function() {
            $('#myModal').modal('show');
        });
    });

    $('#update_register').on('click', function(event) {
        event.preventDefault();
        // Clean the cash_in_hand value by removing all non-numeric characters except dots and commas
        var cash_in_hand_clean = $('#cash_in_hand1').val().replace(/[^0-9.,]/g, '').replace(/,/g, '');
        var cash_in_hand_number = parseFloat(cash_in_hand_clean);
        var selectedType = $('#transaction_type').val();
        if (!cash_in_hand_number || isNaN(cash_in_hand_number || cash_in_hand_number <= 0)) {
            alert("Please enter a valid cash amount.");
            return;
        }
        if (selectedType == '') {
            alert("Select Transaction Type.");
            return;
        }
        var denominations = [];
        $('.denomination-box1').each(function() {
            var denominationValue = parseFloat($(this).find('.count1').data('value'));
            var count1 = parseInt($(this).find('.count1').val().trim());
            var type = $(this).data("type");
            // var type = $(this).closest('.coins-section').length ? "Bills" : "Coins";
            var selectedType = $('#transaction_type').val();
            if (!isNaN(count1) && count1 >= 0) {
                denominations.push({
                    value: denominationValue,
                    count: count1,
                    type: type,
                    selectedType: selectedType
                });
            }
        });

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?= base_url() ?>pos/update_register',
            data: {
                'cash_in_hand': cash_in_hand_number,
                'denominations': denominations,
                'register_note': $('#register_note').val(),
                'transaction_type_name': $('#transaction_type option:selected').text()
            },
            success: function(response) {
                if (response.status == 'success') {
                    window.location.href = "<?= site_url('pos') ?>";
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });
});
</script>
<script>
$(document).ready(function () {
    $('#register_note').redactor({
        minHeight: 150,
        buttons: [
            'bold', 'italic', 'underline',
            'unorderedlist', 'orderedlist',
            'alignment',
            'image', 'link', 'html'
        ]
    });
});
</script>