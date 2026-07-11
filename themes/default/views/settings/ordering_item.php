<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 8px;
    text-align: left;
    cursor: pointer;
    /* Change cursor to indicate sortability */
}

.hidden-checkbox {
    display: none;
}

.checkbox-success {
    background-color: green;
    color: white;
    border: 2px solid green;
    width: 20px;
    height: 20px;
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    line-height: 20px;
    border-radius: 50%;
    font-size: 14px;
    font-weight: bold;
}

.sort-arrow {
    margin-left: 5px;
    font-size: 12px;
    /* Adjust size of arrows */
}

.custom-set {
    padding: 1rem;
    border: 1px solid #ccc;
    margin-bottom: 2rem;
}

.pl-0 {
    padding-left: 0rem;
}

.mt-0 {
    margin-top: 0rem;
}

.d-flx {
    display: flex;
    align-items: center;
}

.ml-3 {
    margin-left: 1rem;
}

.font-weight {
    font-weight: bold;
}

#submitButtons {
    width: 50%;
}

.ordering-input {
    width: 40%;
}

.text-right {
    text-align: right !important;
}

.p-0 {
    padding: 0rem;
}

.pr-0 {
    padding-right: 0rem;
}

.mb-2 {
    margin-bottom: 1.5rem;
}

.mb-0 {
    margin-bottom: 0rem;
}

.modal-header .close {
    margin-top: -3px !important;
}

.select-checkbox {
    transform: scale(1.5);
    -webkit-transform: scale(1.5);
    width: 13px;
    /* Set the width */
    height: 13px;
    /* Set the height */
    margin: 5px;
    /* Optional: Adjust margin */
}

#headerCheckbox {
    transform: scale(1.5);
    -webkit-transform: scale(1.5);
    width: 13px;
    /* Set the width */
    height: 13px;
    /* Set the height */
    margin: 8px;
}

.custom-table-sty {
    border: 1px solid #ccc;
}

.table-container {
    overflow-x: auto;
    position: relative;
    height: 50rem;
    scrollbar-width: thin;
}

.height-setting {
    height: 65rem;
}
</style>

<div class="modal-dialog">
    <div class="modal-content height-setting">
        <div class="modal-header d-flx">

            <!-- <button id="submitButton">Submit</button> -->
            <div class="col-md-6">
                <h3 class="modal-title" id="myModalLabel"><?php echo lang('Product Display On POS'); ?> </h3>
            </div>
            <div class="col-md-6">
                <div style="text-align: right;">
                    <button type="button" class="btn btn-primary" id="submitButtons">Submit</button>
                </div>
            </div>

            <button type="button" class="close" id="rankUI" data-dismiss="modal" aria-hidden="true"><i
                    class="fa fa-2x">&times;</i></button>
        </div>

        <div class="modal-body">
            <div class="col-md-12 d-flx p-0 mb-2">
                <div class="col-md-6">
                    <h3 class="font-weight mt-0 mb-0">Category Name:-
                        <?php echo htmlspecialchars($categoryName->name); ?>
                    </h3>
                </div>
                <div class="col-md-6">
                    <input type="text" id="searchBox" placeholder="Search Product..." class="form-control">
                </div>
            </div>

            <div class="form-group">
                <div class="table-container col-md-12">
                    <table class="table custom-table-sty">
                        <thead>
                            <tr>
                                <th id="productNameHeader">Product Name
                                    <span class="sort-arrow" id="sortArrow"></span>
                                </th>
                                <th id="rankHeader">Rank
                                    <span class="sort-arrow" id="rankArrow"></span>
                                </th>
                                <th>Visible<input type="checkbox" class="text-center" id="headerCheckbox"></th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- <div class="modal-footer"></div> -->
    </div>
</div>
<!-- <script type="text/javascript" src="<?= $assets ?>js/ordering_item.js"></script> -->
<script>
$(document).ready(function() {

    function updateHeaderCheckbox(rowcount = null, checkedCount = null) {
        var checkbox = (rowcount > 0 && rowcount === checkedCount);
        $('#headerCheckbox').prop('checked', checkbox);
    }

    function updateHeaderCheckboxs() {
        const allChecked = $('.select-checkbox').length === $('.select-checkbox:checked').length;
        $('#headerCheckbox').prop('checked', allChecked);
    }

    $('#productTableBody').on('click', '.select-checkbox', function() {
        updateHeaderCheckboxs();
    });

    $('#headerCheckbox').change(function() {
        if ($(this).is(':checked')) {
            $('.select-checkbox').prop('checked', true);
        } else {
            $('.select-checkbox').prop('checked', false);
        }
        updateHeaderCheckboxs(); // Update header checkbox after changing all checkboxes
    });

    var products = <?= json_encode($products) ?>;
    var sortedAscending = true;

    function populateTable(filteredProducts) {
        const $tbody = $('#productTableBody');
        $tbody.empty();
        if (Array.isArray(filteredProducts)) {
            filteredProducts.forEach((product) => {
                console.log(product);

                const isChecked = product.flag_visible === '1' ? 'checked' : '';
                const row = `
            <tr>
                <td>${product.name}</td>
                <td class="text-center"><input type="number" class="ordering-input text-right" min="0" data-index="${product.rank}" value="${product.rank}"></td>
                <td class="text-center">
                    <label>
                        <input type="checkbox" id ="tbodycheck" class="select-checkbox text-center" data-index="${product.flag_visible}" ${isChecked}>
                    </label>
                </td>
            </tr>`;

                $tbody.append(row);
            });
        }

        const rowCount = $tbody.children('tr').length;
        const checkedCount = $tbody.find('input.select-checkbox:checked').length;
        updateHeaderCheckbox(rowCount, checkedCount);
    }

    function gatherTableData() {
        const data = [];
        $('#productTableBody tr').each(function() {
            const $row = $(this);
            const variantName = $row.find('td').eq(0).text();
            const rankNo = $row.find('.ordering-input').val();
            const isSelected = $row.find('.select-checkbox').is(':checked');
            data.push({
                variantName: variantName,
                rankNo: rankNo,
                isSelected: isSelected
            });
        });
        return data;
    }

    // $('#submitButtons').on('click', function() {
    //     var tableData = gatherTableData();
    //     console.log('tableData');
    //     console.log(tableData);
    //     var queryParams = $.param({
    //         variants: tableData
    //     });
    //     var url = '<?php echo base_url('system_settings/saveOrderingItem'); ?>' + '?' + queryParams;
    //     $.ajax({
    //         url: url,
    //         type: 'GET',
    //         contentType: 'application/json',
    //         data: JSON.stringify(tableData),
    //         success: function(response) {
    //             console.log('Data successfully sent:', response);
    //             alert("Products rank & visibility data saved successfully.");
    //             $('#rankUI').modal('hide');
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error sending data:', error);
    //         }
    //     });
    // });

    $('#submitButtons').on('click', function() {
        var tableData = gatherTableData();
        console.log('tableData');
        console.log(tableData);

        $.ajax({
            url: '<?php echo base_url('system_settings/saveOrderingItem'); ?>',  
            type: 'POST',  
            contentType: 'application/json',  
            data: JSON.stringify({ variants: tableData }),  
            success: function(response) {
                console.log('Data successfully sent:', response);
                alert("Products rank & visibility data saved successfully.");
                $('#rankUI').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error('Error sending data:', error);
            }
        });
    });

    $('#productNameHeader').on('click', function() {
        sortedAscending = !sortedAscending;
        if (Array.isArray(products)) {
            products.sort((a, b) => {
                return sortedAscending ? a.name.localeCompare(b.name) : b.name.localeCompare(a
                    .name);
            });
        }
        populateTable(products);
        $('#sortArrow').html(sortedAscending ? '&#9650;' : '&#9660;');
    });

    $('#rankHeader').on('click', function() {
        sortedAscending = !sortedAscending;
        if (Array.isArray(products)) {
            products.sort((a, b) => {
                return sortedAscending ? parseInt(a.rank) - parseInt(b.rank) : parseInt(b
                    .rank) -
                    parseInt(a.rank);
            });
        }
        populateTable(products);
        $('#rankArrow').html(sortedAscending ? '&#9650;' : '&#9660;');
    });
    $('#searchBox').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const filteredProducts = products.filter(product => product.name.toLowerCase().includes(
            searchTerm));
        populateTable(filteredProducts);
    });

    populateTable(products);
    $('#sortArrow').html('');
    $('#rankHeader').trigger('click');
    $('#rankHeader').trigger('click');
});
</script>