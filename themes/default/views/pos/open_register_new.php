<style>
.denomination-box.highlight {
    /* background-color: lightgreen; */
}

.denomination-box .remove {
    display: inline-block;
    margin-top: 5px;
    color: #d9534f;
    cursor: pointer;
}

.denomination-box .remove.hidden {
    display: none;
}

.grid-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: flex-start;
    /* Ensures no large gaps */
}

.denomination-box {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    width: calc(33.33% - 10px);
    /* Ensures three boxes per row without gaps */
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    position: relative;
}

.counter {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px;
    gap: 10px;
    /* Adds space between elements */
}

.counter input {
    width: 60px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 1px;
    font-size: 16px;
}

.counter button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 3px 9px;
    cursor: pointer;
    border-radius: 4px;
}

.counter button:hover {
    background-color: #0056b3;
}

.remove {
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

input#previous_amount {
    text-align: right;
}

input#cash_in_hand {
    text-align: right;
}

@media (min-width: 1301px) and (max-width: 1440px) {
    .counter button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 4px 5px 0px 6px;
        cursor: pointer;
        border-radius: 4px;
        margin-left: -7px;
    }

    button.decrease {
        margin-left: 2px;
    }
}

.panel {
    margin-bottom: 15px;
    border-radius: 0;
    box-shadow: none;
    width: 101%;
    margin-left: -6px;
}

.box .box-header {
    background: white;
    color: #34383c;
    font-size: 16px;
    background: #f7f7f8;
    border-bottom: 1px solid #dbdee0;
    height: 40px;
    margin-bottom: 20px;
}

/* Hide browser increment/decrement arrows on quantity inputs */
.count::-webkit-outer-spin-button,
.count::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.count[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}

.box {
    border: none !important;
    margin-bottom: 15px;
}
</style>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-briefcase"></i><?= lang("open_register"); ?></h2>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="well well-sm" style="height: 70px;">
                <?= form_open("pos/open_register"); ?>
                <?= form_open("pos/open_register", ['id' => 'openRegisterForm']); ?>
                <?php if ($pos_settings->display_coinage == 1 || $pos_settings->display_coinage == 2) { ?>
                <div class="form-group col-md-4" style="margin-top: -10px;">
                    <?= lang('Previous_Closer_Amount', 'Previous_Closer_Amount') ?>
                    <?= form_input('previous_amount', $this->sma->formatMoney($closer_amount->total_cash_submitted), 'id="previous_amount" class="form-control" readonly'); ?>
                </div>
                <?php  } ?>
                <div class="form-group col-md-4" style="margin-top: -10px;">
                    <?= lang('cash_in_hand', 'cash_in_hand') ?>
                    <?= form_input('cash_in_hand', '', 'id="cash_in_hand" class="form-control" '); ?>
                </div>
                <!-- <?php echo form_submit('open_register', lang('open_register'), 'class="btn btn-primary" id="open_register"'); ?>
                    <?php echo form_close(); ?> -->
                <button type="button" class="btn btn-primary" id="open_register"
                    style="margin-top: 19px;margin-left: 10px;"><?= lang('open_register') ?></button>
                <?= form_close(); ?>
                <button id="clearAll" class="btn btn-warning"
                    style="margin-top: -56px; margin-left: 151px;">Reset</button>
            </div>


        </div>
        <div class="box-content">
            <div class="row">
                <div id="Denomination" class="col-md-12 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong style="margin-left: 8px;">Denominations</strong></div>

                        <div class="panel-body" id="denomination-container">
                            <p>No denominations added yet.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script defer type="text/javascript">
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

    //     function renderDenominations(denominations) {
    //     var container = $("#denomination-container");
    //     container.empty();

    //     if (denominations.length === 0) {
    //         container.append('<p>No denominations added yet.</p>');
    //         return;
    //     }

    //     var coinsContainer = $('<div class="denomination-section coins-section"><h4><strong>Coins</strong></h4><div class="grid-container"></div></div>');
    //     var notesContainer = $('<div class="denomination-section notes-section"><h4><strong>Notes</strong></h4><div class="grid-container"></div></div>');

    //     var coinsList = coinsContainer.find(".grid-container");
    //     var notesList = notesContainer.find(".grid-container");

    //     var addedCoins = new Set();
    //     var addedNotes = new Set();

    //     var coinValues = [1, 2, 5, 10, 20]; // ₹10 and ₹20 as coins
    //     var noteValues = [10, 20, 50, 100, 200, 500]; // ₹10 and ₹20 as notes

    //     denominations.forEach(function (item) {
    //         let value = parseFloat(item.currency_value);

    //         var denominationBox = `
    //         <div class="denomination-box" data-value="${value}">
    //             <span class="denom-label">₹ ${item.currency_value}</span>
    //             <div class="counter">
    //                 <button class="decrease" data-value="${item.currency_value}">−</button>
    //                 <input type="number" class="count" data-value="${item.currency_value}" value="0" min="0">
    //                 <button class="increase" data-value="${item.currency_value}">+</button>
    //             </div>
    //             <button class="remove" data-value="${item.currency_value}">✖</button>
    //         </div>`;

    //         if (coinValues.includes(value) && !addedCoins.has(value)) {
    //             coinsList.append(denominationBox);
    //             addedCoins.add(value);
    //         }

    //         if (noteValues.includes(value) && !addedNotes.has(value)) {
    //             notesList.append(denominationBox);
    //             addedNotes.add(value);
    //         }
    //     });

    //     if (coinsList.children().length > 0) {
    //         container.append(coinsContainer);
    //     }
    //     if (notesList.children().length > 0) {
    //         container.append(notesContainer);
    //     }
    // }
    function renderDenominations(denominations) {
        const container = $("#denomination-container");
        container.empty();

        const tableLayout = `
<div class="row">
    <div class="col-md-6">
        <table class="table table-bordered denomination-table">
        <thead>
        <tr>
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td id="note-total" class="text-center">0</td>
                    <td id="note-amount-total" class="text-right">0.00</td>
                </tr>
        </thead>
        <tr class="header-gap"><td colspan="2"></td></tr> 
            <thead class="bg-teal">
                <tr>
                    <th class="text-center">Notes</th>
                    <th></th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody id="notes-body"></tbody>
            <tfoot>
                
            </tfoot>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-bordered denomination-table">
        <thead>
        <tr>
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td id="coin-total" class="text-center">0</td>
                    <td id="coin-amount-total" class="text-right">0.00</td>
                </tr>
        </thead>
        <tr class="header-gap"><td colspan="2"></td></tr> 
            <thead class="bg-teal">
                <tr>
                    <th class="text-center">Coins</th>
                    <th></th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody id="coins-body"></tbody>
            <tfoot>
                
            </tfoot>
        </table>
    </div>
</div>`;

        container.append(tableLayout);

        let notesHTML = '';
        let coinsHTML = '';

        denominations.forEach(item => {
            const value = parseFloat(item.currency_value);
            if (value === 1000) return;
            const type = item.type.toLowerCase();
            const count = item.count ?? 0;
            const amount = value * count;
            const formattedAmount = formatMoney(amount);

            const rowHTML = `
        <tr class="denomination-row denomination-box" data-type="${type}" data-value="${value}">
            <td class="denom-label" currency-value>
                ${(type === 'bills' || type === 'notes') ? parseInt(value, 10) : value.toFixed(2)}
            </td>
            <td>×</td>
            <td>
                <div class="input-group input-group-sm counter" style="display: flex; justify-content: center; align-items: center;">
                    <button class="btn btn-xs btn-default decrease" data-value="${value}" data-type="${type}">−</button>
                    <input type="number" class="form-control input-sm count text-center" 
                        value="${count}" data-value="${value}" data-type="${type}"
                        min="0" style="width: 60px; margin: 0 5px;">
                    <button class="btn btn-xs btn-default increase" data-value="${value}" data-type="${type}">+</button>
                </div>
            </td>
            <td class="amount text-right" style="padding-right: 20px;">${formattedAmount}</td>
        </tr>`;

            if (type === 'bills' || type === 'notes') {
                notesHTML += rowHTML;
            } else {
                coinsHTML += rowHTML;
            }
        });

        $("#notes-body").html(notesHTML);
        $("#coins-body").html(coinsHTML);

        updateCashInHand();
    }



    function formatMoney(amount) {
        amount = parseFloat(amount);
        if (isNaN(amount)) return 'د.إ 0.00';
        return 'د.إ ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function updateCashInHand() {
        let total = 0;
        let noteQty = 0;
        let noteAmt = 0;
        let coinQty = 0;
        let coinAmt = 0;
        $(".denomination-row").each(function() {
            const $row = $(this);
            const value = parseFloat($row.data("value"));
            const type = $row.data("type");
            const qty = parseInt($row.find(".count").val()) || 0;
            const amount = value * qty;

            $row.find(".amount").text(formatMoney(amount));
            total += amount;

            if (type === 'coins' || type === 'coin') {
                coinQty += qty;
                coinAmt += amount;
            } else if (type === 'bills' || type === 'notes') {
                noteQty += qty;
                noteAmt += amount;
            }
        });
        $("#coin-total").text(coinQty);
        $("#coin-amount-total").text(formatMoney(coinAmt));
        $("#note-total").text(noteQty);
        $("#note-amount-total").text(formatMoney(noteAmt));
        $("#cash_in_hand").val(formatMoney(total));
    }


    $(document).ready(function() {
        if (displayCoinage == 1) {
            setTimeout(() => {
                updateCashInHand();
            }, 600);
        }


        $(document).on("click", ".increase", function() {
            let box = $(this).closest(".denomination-box");
            let input = $(this).siblings(".count");
            input.val(parseInt(input.val()) + 1);
            box.find(".remove").removeClass("hidden").show();
            // box.css("background", "lightgreen");
            updateCashInHand();
        });


        $(document).on("click", ".decrease", function() {
            let box = $(this).closest(".denomination-box");
            let input = $(this).siblings(".count");

            let newValue = Math.max(0, parseInt(input.val()) - 1);
            input.val(newValue);

            box.find(".remove").removeClass("hidden").show();

            if (newValue === 0) {
                // Remove highlight if count is 0
                box.css("background", "#f9f9f9");
                box.find(".remove").hide();
            } else {
                // Highlight if count > 0
                // box.css("background", "lightgreen");
                box.find(".remove").removeClass("hidden").show();
            }

            updateCashInHand();
        });

        $(document).on("input change", ".count", function() {
            updateCashInHand();
        });

        $(document).on("click", ".remove", function() {
            let box = $(this).closest(".denomination-box");
            let countInput = box.find(".count");

            let originalCount = parseInt(countInput.data("original")) || 0;

            countInput.val(originalCount);
            $(this).hide();
            box.css("background", "#f9f9f9");

            updateCashInHand();
        });


        $("#clearAll").click(function() {
            $(".count").each(function() {
                let original = $(this).data("original");
                // Fix: if original missing → set to 0
                if (original === undefined || original === "" || isNaN(original)) {
                    original = 0;
                }
                $(this).val(original);

                const box = $(this).closest(".denomination-box");
                box.css("background", "#f9f9f9"); // Remove highlight always
                box.find(".remove").hide().addClass("hidden"); // Always hide remove button
            });
            // Reset .count1 denomination
            $(".count1").each(function() {
                let original = $(this).data("original");

                if (original === undefined || original === "" || isNaN(original)) {
                    original = 0;
                }
                $(this).val(original);
                $(this).data("last-valid", original);
            });
            updateCashInHand();
            // window.location.reload();
        });


        fetchDenomination();


        $('#open_register').on('click', function(event) {
            event.preventDefault();
            var cash_in_hand = $('#cash_in_hand').val().replace(/^[^\d]+/, '').replace(/,/g, '');
            if (!cash_in_hand || isNaN(cash_in_hand)) {
                alert("Please enter a valid cash amount.");
                return;
            }

            var denominations = [];
            $('.denomination-box').each(function() {
                var denominationValue = parseFloat($(this).find('.count').data('value'));
                var count = parseInt($(this).find('.count').val().trim());
                var type = $(this).data("type");

                // var type = $(this).closest('.coins-section').length ? "coin" : "note";

                if (!isNaN(count) && count >= 0) {
                    denominations.push({
                        value: denominationValue,
                        count: count,
                        type: type
                    });
                }
            });

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?= base_url() ?>pos/open_register',
                data: {
                    'cash_in_hand': cash_in_hand,
                    'denominations': denominations
                },
                success: function(response) {
                    if (response.status == 'success') {
                        // return
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
    $(document).on('keydown', '.count', function(e) {
        const $input = $(this);
        const currentValue = $input.val();

        if (e.key === "Backspace" && currentValue === "0") {
            $input.val(""); // Clear the value if it's 0 and backspace is pressed
            e.preventDefault(); // Prevent default backspace behavior
        }
    });

    $(document).on('input change', '.count', function() {
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

        const $box = $input.closest('.denomination-box');
        const $removeBtn = $box.find('.remove');

        if (val > 0) {
            $box.addClass('highlight');
            $removeBtn.removeClass('hidden').show();
        } else {
            $box.removeClass('highlight');
            $removeBtn.addClass('hidden').hide();
        }
        updateCashInHand();
    });
    $(document).on('input', '.inputValue', function() {
        let val = parseInt($(this).val(), 10);
        if (isNaN(val) || val < 0) {
            $(this).val(0);
        }
    });
    </script>
    <script>
    const displayCoinage = <?php echo json_encode($pos_settings->display_coinage); ?>;

    if (displayCoinage == 1 || displayCoinage == 2) {
        document.getElementById('Denomination').style.display = 'block';
        $('#cash_in_hand').prop('readonly', true);

    } else {
        document.getElementById('Denomination').style.display = 'none';

        const input = document.getElementById('cash_in_hand');
        if (input) {
            input.removeAttribute('readonly');
        }
    }
    </script>