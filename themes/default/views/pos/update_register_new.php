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
    /* margin-left: -7px;
    margin-right: -7px; */
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

.text-right {
    text-align: right;
}

@media (min-width: 992px) {
    .col-md-5 {
        width: 52.666667%;
    }
}

@media only screen and (min-width: 768px) and (max-width: 1024px) {
    .denomination-row-wrapper.row {
        display: flex;
        gap: 24px !important;
    }
}

.well.well-sm {
    height: 100px !important;
}

.denom-amount,
th.amount-header,
td.amount-cell {
    text-align: right !important;
}

.amount-header,
.amount-cell {
    text-align: right !important;
}

.panel {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    margin-top: -16px !important;
}

.panel-heading {
    font-weight: bold;
    font-size: 18px;
    border: 1px solid #007bff;
    padding: 10px;
    background-color: #f6f6f6;
    ;
    border-radius: 8px 8px 0 0;
}

/* Hide browser increment/decrement arrows on quantity inputs */
.count1::-webkit-outer-spin-button,
.count1::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.count1[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}

.modal-body {
    padding: 13px;
}

.form-control {
    font-size: 13px !important;
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
            <div class="col-md-12">
                <div class="well well-sm " style="margin-top: 8px;margin-left: -14px;margin-bottom: 8px;width: 103%;">
                    <div class="row">
                        <div class="form-group col-lg-3 col-md-6 col-12">
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
                        <!-- Cash in Hand -->
                        <div class="form-group col-lg-3 col-md-6 col-12">
                            <?= lang('Counter_Cash', 'Counter_Cash') ?>
                            <?= form_input('counter_cash',$this->sma->formatMoney($counter_cash, 2),'id="cash_in_hand1" class="form-control text-right" readonly'); ?>
                        </div>
                        <!--Current_Amount -->
                        <div class="form-group col-lg-3 col-md-6 col-12" style="margin-top: -5px;">
                            <label><?= lang('Current_Amount', 'Current_Amount') ?></label>
                            <div class="form-control-plaintext form-control readonly" id="current_amount"
                                style="margin-top: -6px; background-color: #8080801c; text-align: right;">
                                <?= $this->sma->formatMoney(0, 2); ?>
                            </div>
                            <input type="hidden" name="current_amount_hidden" id="current_amount_hidden">
                        </div>
                        <!-- Difference -->
                        <div class="form-group col-lg-3 col-md-6 col-12">
                            <?= lang('Difference', 'Difference') ?>
                            <?= form_input('Difference',  $this->sma->formatMoney($counter_cash - 0,2), 'id="Difference" class="form-control text-right" readonly'); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php
      $attrib = array('data-toggle' => 'validator', 'role' => 'form');
      echo form_open_multipart("pos/update_register/" . $user_id, $attrib);
    ?>
        <div class="modal-body" style="max-height: 65vh; overflow-y: scroll;">
            <div class="box-content">
                <div class="row" style="margin-top: -11px!important;">
                    <div class="col-md-12 col-sm-12">
                        <div class="">
                            <div class="panel-heading"><strong><?= lang('Denominations') ?></strong></div>
                            <div class="panel-body" id="update-denomination-container" style="overflow:scroll;">
                                <p>No denominations added yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer no-print">
            <div class="form-group col-md-6" style="text-align: left;">
                <?= lang('Notes', 'Notes') ?>
                <textarea name="register_note" id="register_note" class="form-control" rows="3"></textarea>
            </div>
            <button type="button" id="clear_all_denominations1" class="btn btn-warning"><?= lang('Reset') ?></button>
            <button type="button" class="btn btn-primary" id="update_register"><?= lang('Update_Register') ?></button>
        </div>
        <?= form_close(); ?>
    </div>
</div>
</div>

<?= $modal_js ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


<script type="text/javascript">
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

function updateCashInHand() {
    let grandTotal = 0;

    $(".denomination-table-section").each(function() {
        let sectionTotal = 0;
        let quantityTotal = 0;

        $(this).find(".denomination-row").each(function() {
            const value = parseFloat($(this).data("value")) || 0;
            const quantity = parseInt($(this).find(".count1").val()) || 0;
            const amount = value * quantity;
            $(this).find(".denom-amount").text(formatMoneys(amount));
            sectionTotal += amount;
            quantityTotal += quantity;
        });

        $(this).find(".total-amount").text(formatMoneys(sectionTotal));
        $(this).find(".total-quantity").text(quantityTotal);
        grandTotal += sectionTotal;
    });
    // Update cash in hand display
    // $("#cash_in_hand1").val(formatMoneys(grandTotal));
    $("#current_amount").text(formatMoneys(grandTotal));
    $("#current_amount_hidden").val(grandTotal);
    // for validation of current amount not be greater than counter cash
    const counterCash = parseFloat("<?= $counter_cash ?>") || 0;
    if (grandTotal > counterCash) {
        alert("Current Amount cannot be greater than Counter Cash!");
        // Reset all denomination amounts and section totals
        $(".count1").val(0);
        $("#current_amount").text(formatMoneys(0));
        $("#current_amount_hidden").val(formatMoneys(0));
        $(".denomination-row .denom-amount").text(formatMoneys(0)); // Reset each row's amount
        $(".denomination-table-section .total-amount").text(formatMoneys(0)); // Reset section totals
        $(".denomination-table-section .total-quantity").text(0); // Reset section quantity
    }
    // Calculate and update difference
    calculateDifference();
}

function calculateDifference() {
    // Counter Cash from PHP (fixed value)
    const originalCashInHand = parseFloat("<?= $counter_cash ?>") || 0;
    const currentCashValue = $("#current_amount").text();
    // Get currency symbol from site settings and create dynamic regex
    const currencySymbol = site.settings.symbol || '';
    const escapedSymbol = currencySymbol.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // Escape special regex characters
    const currencyRegex = new RegExp(escapedSymbol + '\\s*', 'gi');
    const cleanValue = currentCashValue.replace(currencyRegex, '').replace(/[^\d.-]/g, '');
    const currentAmount = parseFloat(cleanValue) || 0;
    const difference = Math.abs(currentAmount - originalCashInHand);
    $("#Difference").val(formatMoneys(difference));
}

function renderDenominations(denominations) {
    const container = $("#update-denomination-container");
    container.empty();
    denominations = denominations.filter(item => Number(item.currency_value) !== 1000);
    const grouped = {};
    denominations.forEach(item => {
        if (!grouped[item.type]) grouped[item.type] = [];
        grouped[item.type].push(item);
    });

    const wrapper = $('<div class="denomination-row-wrapper row" style="display:flex;gap:65px;"></div>');

    ['Bills', 'Coins'].forEach(type => {
        const items = grouped[type];
        if (!items) return;

        const section = $(`
            <div class="denomination-table-section">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td colspan="2"><strong>Total</strong></td>
                            <td class="total-quantity" style="text-align: center !important;">0</td>
                            <td class="total-amount" style="text-align: right !important;">${formatMoneys(0)}</td>
                        </tr>
                        <tr>
                        <td colspan="2" style="border: none; height: 10px;"></td>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <th style="text-align: right;">${type === 'Bills' ? 'Notes' : 'Coins'}</th>
                            <th>×</th>
                            <th style="text-align: center !important;">Quantity</th>
                            <th style="text-align: right !important;">Amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        `);

        const tbody = section.find('tbody');

        items.forEach(item => {
            // const originalCount = item.count || 0;
            // for showing all denomination 0 in initial condition
            const originalCount = 0;
            const displayValue = (type === 'Bills') ?
                parseInt(item.currency_value, 10) // Notes → no decimals
                :
                formatMoney(item.currency_value); // Coins → keep format

            const tr = $(`
                <tr class="denomination-row denomination-box1" data-type="${type}" data-value="${item.currency_value}">
                    <td class="denom-label denomination-box1" style="text-align: right;">${displayValue}</td>
                    <td>×</td>
                    <td>
                        <div class="denom-counter count1er">
                            <button type="button" class="btn btn-sm btn-decrease-denom">−</button>
                            <input type="number" class="count1 form-control input-sm text-center"
                                   value="${originalCount}" 
                                   data-original-count="${originalCount}"
                                   data-value="${item.currency_value}"
                                   min="0" style="width: 60px; display: inline-block;">
                            <button type="button" class="btn btn-sm btn-increase-denom">+</button>
                        </div>
                    </td>
                    <td class="denom-amount " style="text-align: right !important;">${formatMoneys(item.currency_value * originalCount)}</td>
                </tr>
            `);

            tbody.append(tr);
        });

        wrapper.append(section);
    });

    container.append(wrapper);
    updateCashInHand();
    container.on('keydown', '.count1', function(e) {
        const input = $(this);
        const currentValue = input.val();

        if (e.key === "Backspace" && currentValue === "0") {
            input.val(""); // Clear the value if it’s 0 and backspace is pressed
        }
    });
}


function formatMoneys(x, symbol) {
    if (!symbol) {
        symbol = "";
    }
    if (site.settings.sac == 1) {
        return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
            '' + (parseFloat(x).toFixed(site.settings.decimals)) +
            (site.settings.display_symbol == 2 ? site.settings.symbol : '');
    }
    var fmoney = accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site
        .settings.thousands_sep, site.settings.decimals_sep, "%s%v");
    fmoney = (fmoney == '-0.00') ? '0.00' : fmoney; //convert -0.00 to 0.00
    return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
        fmoney +
        (site.settings.display_symbol == 2 ? site.settings.symbol : '');
}
// function formatMoneys(amount) {
//    return formatMoney(amount);

//     amount = parseFloat(amount);
//     if (isNaN(amount)) {
//         return 'Invalid amount';
//     }
//     let formattedAmount = amount.toFixed(2);
//     return `د.إ ${formattedAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
// }

$(document).on('change', '.count1', function() {
    let box = $(this).closest(".denomination-box1");
    box.find(".btn-remove-denom").show();
    updateCashInHand();
});



$(document).ready(function() {
    fetchDenomination();

    $(document).on("click", ".btn-increase-denom", function() {
        let box = $(this).closest(".denomination-box1");
        let input = $(this).siblings(".count1");

        let currentVal = parseInt(input.val()) || 0;
        input.val(currentVal + 1);
        box.find(".btn-remove-denom").show();
        updateCashInHand();
    });


    $(document).on("click", ".btn-decrease-denom", function() {
        let box = $(this).closest(".denomination-box1");
        let input = $(this).siblings(".count1");
        let currentVal = parseInt(input.val()) || 0;

        if (currentVal <= 0) {
            alert("Amount is already 0. Cannot decrease further.");
            return;
        }

        let newValue = Math.max(0, currentVal - 1);
        input.val(newValue);

        box.find(".btn-remove-denom").removeClass("hidden").show();

        if (newValue === 0) {
            box.css("background", "#f9f9f9");
            box.find(".btn-remove-denom").hide();
        } else {
            box.find(".btn-remove-denom").removeClass("hidden").show();
        }

        updateCashInHand();
    });


    $(document).on("click", ".btn-remove-denom", function() {
        let box = $(this).closest(".denomination-box1");
        let countInput = box.find(".count1");
        let originalCount = parseInt(countInput.data("original-count")) || 0;

        countInput.val(originalCount);
        $(this).hide();
        box.css("background", "#f9f9f9");

        updateCashInHand();
    });
    $(document).on('input', '.count1', function() {
        const $input = $(this);
        let inputValue = $input.val().trim();
        let val = 0;

        if (inputValue === '' || inputValue === null) {
            val = 0;
        } else {
            val = parseInt(inputValue, 10);
            if (isNaN(val) || val < 0) {
                val = 0;
                $input.val(0);
            }
        }
        updateCashInHand();
    });


    $("#clear_all_denominations1").click(function() {
        $(".denomination-row").each(function() {
            let input = $(this).find(".count1");
            let originalCount = parseInt(input.data("original-count")) || 0;

            input.val(originalCount);
        });

        updateCashInHand();
    });



    $(function() {
        $('#myModal').on('hidden.bs.modal', function() {
            location.reload();
        });
    });
    $('#update_register_button').click(function() {
        $('#myModal').load('<?= site_url("pos/update_register") ?>', function() {
            $('#myModal').modal('show');
        });
    });
    // Allow only digits 0–9 in the count1 inputs
    $(document).on('keypress', '.count1', function(e) {
        if (e.which < 48 || e.which > 57) {
            e.preventDefault(); // block non-digits
        }
    });

    // $('#update_register').on('click', function(event) {
    //     event.preventDefault();
    //     // var cash_in_hand = $('#cash_in_hand1').val().trim();
    //     // var cash_in_hand_clean = cash_in_hand.replace(/[^0-9.]/g,
    //     // ''); // removes everything except digits and dot
    //     // var cash_in_hand_number = parseFloat(cash_in_hand_clean);
    //     var cash_in_hand_number = $('#cash_in_hand1').val().replace(/^[^\d]+/, '').replace(/,/g, '');
    //     var selectedType = $('#transaction_type').val();
    //     if (!cash_in_hand_number || isNaN(cash_in_hand_number || cash_in_hand_number <= 0)) {
    //         alert("Please enter a valid cash amount.");
    //         return;
    //     }
    //     if (selectedType == '') {
    //         alert("Select Transaction Type.");
    //         return;
    //     }
    //     var denominations = [];
    //     $('.denomination-box1').each(function() {
    //         var denominationValue = parseFloat($(this).find('.count1').data('value'));
    //         var count1 = parseInt($(this).find('.count1').val().trim());
    //         var type = $(this).data("type");
    //         // var type = $(this).closest('.coins-section').length ? "Bills" : "Coins";
    //         var selectedType = $('#transaction_type').val();
    //         if (!isNaN(count1) && count1 >= 0) {
    //             denominations.push({
    //                 value: denominationValue,
    //                 count: count1,
    //                 type: type,
    //                 selectedType: selectedType
    //             });
    //         }
    //     });

    //     $.ajax({
    //         type: 'POST',
    //         dataType: 'json',
    //         url: '<?= base_url() ?>pos/update_register',
    //         data: {
    //             'cash_in_hand': cash_in_hand_number,
    //             'denominations': denominations
    //         },
    //         success: function(response) {
    //             if (response.status == 'success') {
    //                 window.location.href = "<?= site_url('pos') ?>";
    //             } else {
    //                 alert(response.message);
    //             }
    //         },
    //         error: function(xhr, status, error) {
    //             console.error("AJAX Error:", status, error);
    //         }
    //     });
    // });
    $('#update_register').on('click', function(event) {
        event.preventDefault();
        var $btn = $(this);
        $btn.prop('disabled', true);

        // ✅ Get cash in hand value and clean it
        var currentCashValue = $('#cash_in_hand1').val();
        const currencySymbol = site.settings.symbol || '';
        const escapedSymbol = currencySymbol.replace(/[.*+?^${}()|[\]\\]/g,
            '\\$&'); // Escape special regex characters
        const currencyRegex = new RegExp(escapedSymbol + '\\s*', 'gi');
        const cash_in_hand_number = currentCashValue.replace(currencyRegex, '').replace(/[^\d.-]/g, '');
        var selectedTypeId = $('#transaction_type').val();
        var transactionTypeName = $('#transaction_type option:selected').text();

        // if (!cash_in_hand_number || isNaN(cash_in_hand_number) || parseFloat(cash_in_hand_number) <=
        //     0) {
        //     alert("Please enter a valid cash amount.");
        //     return;
        // }

        if (selectedTypeId == '') {
            alert("Select Transaction Type.");
            $btn.prop('disabled', false);
            return;
        }

        // ✅ Collect denominations
        var denominations = [];
        var transactionTypeName = '';
        $('.denomination-box1').each(function() {
            var denominationValue = parseFloat($(this).find('.count1').data('value'));
            // var count1 = parseInt($(this).find('.count1').val().trim());
            var count1 = parseInt($(this).find('.count1').val());
            var type = $(this).data("type");
            var selectedType = $('#transaction_type').val();
            transactionTypeName = $('#transaction_type option:selected').text();

            if (!isNaN(count1) && count1 >= 0) {
                denominations.push({
                    value: denominationValue,
                    count: count1,
                    type: type,
                    selectedType: selectedTypeId,
                    selectedTypeName: transactionTypeName
                });
            }
        });

        var current_amount = $("#current_amount_hidden").val();
        console.log("Submitting denominations:", denominations);

        // ✅ Submit via AJAX with CSRF token if required
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?= base_url() ?>pos/update_register',
            data: {
                'cash_in_hand': cash_in_hand_number,
                'denominations': denominations,
                'register_note': $('#register_note').val(),
                'transactionTypeName': transactionTypeName,
                'current_amount': $("#current_amount_hidden").val(),
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>' // ✅ CSRF support
            },
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = "<?= site_url('pos') ?>";
                } else {
                    alert(response.message || 'Update failed. Please try again.');
                    $btn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log("Response Text:", xhr.responseText);
                alert("Server error occurred. Check console for details.");
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
<script>
$(document).ready(function() {
    $('#register_note').redactor({
        minHeight: 100,
        maxHeight: 100,
        autoresize: false,
        buttons: [
            'bold', 'italic', 'underline',
            'unorderedlist', 'orderedlist',
            'alignment',
            'image', 'link', 'html'
        ]
    });
});
</script>