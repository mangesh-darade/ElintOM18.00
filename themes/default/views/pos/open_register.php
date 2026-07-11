<style>
.denomination-box.highlight {
    background-color: lightgreen;
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
</style>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-briefcase"></i><?= lang("open_register"); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div id="Denomination" class="col-md-5 col-sm-6 " style="display:none">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Denominations</strong></div>
                    <div class="panel-body" id="denomination-container">
                        <p>No denominations added yet.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-7">

                <div class="well well-sm">
                    <?= form_open("pos/open_register"); ?>
                    <?= form_open("pos/open_register", ['id' => 'openRegisterForm']); ?>
                    <?php if ($pos_settings->display_coinage == 1) { ?>
                    <div class="form-group">
                        <?= lang('Previous_Closer_Amount', 'Previous_Closer_Amount') ?>
                        <?= form_input('previous_amount', $this->sma->formatMoney($closer_amount->total_cash_submitted), 'id="previous_amount" class="form-control" readonly'); ?>
                    </div>
                    <?php  } ?>
                    <div class="form-group">
                        <?= lang('cash_in_hand', 'cash_in_hand') ?>
                        <?= form_input('cash_in_hand', '', 'id="cash_in_hand" class="form-control" '); ?>
                    </div>
                    <?php if ($pos_settings->display_coinage == 1) { ?>
                    <div class="form-group">
                        <?= lang('Notes', 'Notes') ?>
                        <textarea name="register_note" id="register_note" class="form-control" rows="3"></textarea>
                    </div>
                    <?php  } ?>
                    <!-- <?php echo form_submit('open_register', lang('open_register'), 'class="btn btn-primary" id="open_register"'); ?>
                    <?php echo form_close(); ?> -->
                    <button type="button" class="btn btn-primary"
                        id="open_register"><?= lang('open_register') ?></button>
                    <?= form_close(); ?>
                </div>
                <button id="clearAll" class="btn btn-warning">Reset</button>
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
    var container = $("#denomination-container");
    container.empty();

    var currentType = "";
    var grid = $('<div class="grid-container"></div>');
    denominations.forEach(function(item) {
        if (item.type !== currentType) {
            if (grid.children().length > 0) {
                container.append(grid);
                grid = $('<div class="grid-container"></div>');
            }
            container.append(`<h3 class="denomination-title">${item.type}</h3>`);
            currentType = item.type;
        }
        var count = (item.count !== undefined && item.count !== null && item.count !== '') ? item.count : 0;
        var formattedCurrencyValue = formatMoney(item.currency_value);
        var denominationBox = `
      <div class="denomination-box" data-type="${item.type}"> <!-- Store the type here -->
        <span class="denom-label currency-value"> ${formattedCurrencyValue}</span>
        <div class="counter">
            <button class="decrease" data-value="${item.currency_value}" data-type="${item.type}">−</button>
        <input type="number" class="count inputValue" data-value="${item.currency_value}" data-original="${count}" value="${count}" min="0">
            <button class="increase" data-value="${item.currency_value}" data-type="${item.type}">+</button>
        </div>
        <button class="remove hidden" data-value="${item.currency_value}" id="cross">✖</button>
      </div>`;


        grid.append(denominationBox);
    });

    container.append(grid);


}

function formatMoneys(amount) {
    amount = parseFloat(amount);
    if (isNaN(amount)) {
        return 'Invalid amount';
    }
    let formattedAmount = amount.toFixed(2);
    return `د.إ ${formattedAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
}

function updateCashInHand() {
    let total = 0;
    $(".count").each(function() {
        let value = parseFloat($(this).data("value"));
        console.log("value", value);
        let quantity = parseInt($(this).val());
        total += value * quantity;
    });
    $("#cash_in_hand").val(formatMoney(total));
}


$(document).ready(function() {
    if (displayCoinage == 1) {
        setTimeout(() => {
            updateCashInHand();
        }, 600);
        fetchDenomination();
    }


    $(document).on("click", ".increase", function() {
        let box = $(this).closest(".denomination-box");
        let input = $(this).siblings(".count");
        input.val(parseInt(input.val()) + 1);
        box.find(".remove").removeClass("hidden").show();
        box.css("background", "lightgreen");
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
            box.css("background", "lightgreen");
            box.find(".remove").removeClass("hidden").show();
        }

        updateCashInHand();
    });

    $(document).on("change", ".inputValue", function() {
        updateCashInHand();
    });
    // $(document).on("change", ".inputValue", function() {
    //     updateCashInHand();
    // });

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
            const original = $(this).data("original");
            $(this).val(original);

            const box = $(this).closest(".denomination-box");
            box.css("background", "#f9f9f9"); // Remove highlight always
            box.find(".remove").hide().addClass("hidden"); // Always hide remove button
        });
        updateCashInHand();
    });


    fetchDenomination();


    $('#open_register').on('click', function(event) {
        event.preventDefault();
        // var cash_in_hand = $('#cash_in_hand').val().replace(/^[^\d]+/, '').replace(/,/g, '');
        var cash_in_hand = $('#cash_in_hand').val().replace(/^[^\d]+/, '').replace(/[^\d]+$/, '').replace(/,/g, '');
        if (!cash_in_hand || isNaN(cash_in_hand)) {
            alert("Please enter a valid cash amount.");
            return;
        }

        var denominations = [];
        // Only collect denomination data if coinage is enabled
        if (displayCoinage == 1) {
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
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?= base_url() ?>pos/open_register',
            data: {
                'cash_in_hand': cash_in_hand,
                'denominations': denominations,
                'register_note': $('#register_note').val()
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
$(document).on('input change', '.count', function() {
    const $input = $(this);
    const value = parseInt($input.val()) || 0;
    const $box = $input.closest('.denomination-box');
    const $removeBtn = $box.find('.remove');

    if (value > 0) {
        $box.addClass('highlight');
        $removeBtn.removeClass('hidden');
    } else {
        $box.removeClass('highlight');
        $removeBtn.addClass('hidden');
    }
});
$(document).on('input', '.inputValue', function () {
    let val = parseInt($(this).val(), 10);
    if (isNaN(val) || val < 0) {
        $(this).val(0);
    }
});

</script>
<script>
const displayCoinage = <?php echo json_encode($pos_settings->display_coinage); ?>;

if (displayCoinage == 1) {
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