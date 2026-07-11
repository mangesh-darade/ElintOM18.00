
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');
$user_pre_defined = $this->session->userdata('user_id');
if ($this->input->post('start_date')) {
    $start_date = $this->input->post('start_date');
} else {
    // $start_date = date('d/m/Y');
    $start_date = date('d/m/Y') . ' 00:00';
}

if ($this->input->post('end_date')) {
    $end_date = $this->input->post('end_date');
} else {
    // $end_date = date('d/m/Y') ;
    $end_date = date('d/m/Y') . ' 23:59';
}
if ($this->input->post('warehouse')) {
    $warehouse .= "&warehouse=" . $this->input->post('warehouse');
}else{
    $warehouse .=($user_warehouse=='0' ||$user_warehouse==NULL)?'':"&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if ($this->input->post('time_slot')) {
    $time_slot = $this->input->post('time_slot');
} 
// else {
//     var_dump("else");
//     $time_slot = ''; // or default slot ID if needed
//     $start_date = date('d/m/Y') . ' 00:00';
//     $end_date = date('d/m/Y') . ' 23:59';
// }
$this->data['selected_time_slot'] = $time_slot;

// if ($this->input->post('report_type')) {
//     $report_type = $this->input->post('report_type');
// } else {
//     $report_type = 1;
// }

$v = "&start_date=$start_date&end_date=$end_date";
$v .= "&report_type=".$report_type;
$v .= "&warehouse=".$warehouse;
$v .= "&time_slot=".$time_slot;

$selected_warehouse = $this->input->post('warehouse');
// $v .= ($selected_warehouse) ? "&warehouse=" . $selected_warehouse : '';
$created_by = $this->input->post('created_by'); // <-- You forgot this line!
// $v .= ($created_by) ? "&created_by=" . $created_by 
// $final_created_by = isset($created_by) && $created_by !== '' ? $created_by : $user_pre_defined;
// $v .= "&created_by=" . $final_created_by;
if ($Owner || $Admin) {
    if ($created_by) {
        $v .= "&created_by=" . $created_by;
    }
}
//  else {
//     $final_created_by = isset($created_by) && $created_by !== '' ? $created_by : $user_pre_defined;
//     $v .= "&created_by=" . $final_created_by;
// }



?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
    
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i> <?= lang('sales_dash') ?> <?php
            if ($start_date) {
                echo "From " . $start_date . " To " . $end_date;
            }
            ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="box-content">
    <div class="row">
        <div class="col-lg-12">
            <div id="form">
                <?php echo form_open("reports/sales_dash"); ?>
                <div class="row">

                <div class="col-md-3 choose-date-div">
                <div class="form-group choose-date hidden-xs" style="<?php echo empty($time_slot) ? 'display:none;' : ''; ?>">
                            <div class="controls">
                                <?= lang("date_range", "date_range"); ?>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text"
                                           autocomplete="off"
                                           value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>"
                                           id="daterange_new" class="form-control">
                                    <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
                                    <input type="hidden" name="start_date" id="start_date"
                                           value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ""; ?>">
                                    <input type="hidden" name="end_date" id="end_date"
                                           value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ""; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Manual Start/End Date (visible only when no time slot selected) -->
                    <div class="col-md-3 manual-dates" style="<?php echo empty($time_slot) ? '' : 'display:none;'; ?>">
                        <div class="form-group">
                            <label><?= lang("start_date"); ?></label>
                            <!-- <?= form_input('start_date',  isset($_POST['start_date']) ? $_POST['start_date'] : $start_date, 'class="form-control input-tip datetime" id="start_date_input" required="required"'); ?> -->
                            <?= form_input('start_date', $start_date, 'class="form-control input-tip datetime" id="start_date_input" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3 manual-dates" style="<?php echo empty($time_slot) ? '' : 'display:none;'; ?>">
                        <div class="form-group">
                            <label><?= lang("end_date"); ?></label>
                            <!-- <?= form_input('end_date',   isset($_POST['end_date']) ? $_POST['end_date'] : $end_date, 'class="form-control input-tip datetime" id="end_date_input" required="required"'); ?> -->
                            <?= form_input('end_date', $end_date, 'class="form-control input-tip datetime" id="end_date_input" required="required"'); ?>
                        </div>
                    </div>
                    <!-- Time Slot -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?= lang("Time Slot"); ?></label>
                            <?php $selected_time_slot = $this->input->post('time_slot') ?: ''; ?>
                            <select name="time_slot" id="time_slot" class="form-control">
                                <option value="" <?= ($selected_time_slot === '') ? 'selected' : '' ?>><?= lang('select') . ' ' . lang('Time Slot'); ?></option>
                                <?php 
                                foreach ($time_slots as $slot): 
                                    $is_selected = ($selected_time_slot !== '' && $slot->id == $selected_time_slot);
                                ?>
                                    <option value="<?= $slot->id ?>"
                                            data-start="<?= $slot->start_time ?>"
                                            data-end="<?= $slot->end_time ?>"
                                            <?= $is_selected ? 'selected' : '' ?>>
                                        <?= $slot->name . ' - ' . date("h:i A", strtotime($slot->start_time)) . ' to ' . date("h:i A", strtotime($slot->end_time)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Report Type -->
                    <!-- <div class="col-md-3">
                        <div class="form-group">
                            <label><?= lang("Report type"); ?></label>
                            <select name="report_type" id="report_type" class="form-control">
                                <option value="1" <?= ($report_type == 1) ? 'selected' : '' ?>>Sales compare and balance stock</option>
                                <option value="2" <?= ($report_type == 2) ? 'selected' : '' ?>>Sales compare and sold items</option>
                            </select>
                        </div>
                    </div> -->

                    <!-- Created By -->
                    <!-- <div class="col-md-3">
                        <div class="form-group">
                            <label><?= lang("User"); ?></label>
                            <select name="created_by" id="created_by" class="form-control">
                                <option value=""><?= lang('select') . ' ' . lang('user'); ?></option>
                                <?php
                                foreach ($users as $user) {
                                    if ($Owner || $Admin || $this->session->userdata('user_id') == $user->id) {
                                        $selected = ($this->input->post('created_by') == $user->id) ? 'selected' : '';
                                        echo "<option value='{$user->id}' {$selected}>{$user->first_name} {$user->last_name}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div> -->
                    <!-- Created By -->
                    <?php if ($Owner || $Admin): ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang("User"); ?></label>
                                <select name="created_by" id="created_by" class="form-control">
                                    <option value=""><?= lang('select') . ' ' . lang('user'); ?></option>
                                    <?php
                                    $user_pre_defined = $this->session->userdata('user_id'); // your preselected ID
                                    $posted_user = $this->input->post('created_by');
                                    $selected_user = ($posted_user !== null) ? $posted_user : '';

                                    foreach ($users as $user) {
                                        $selected = ($selected_user == $user->id) ? 'selected' : '';
                                        echo "<option value='{$user->id}' {$selected}>{$user->first_name} {$user->last_name}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Warehouse -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="warehouse"><?= lang("Location"); ?></label>
                            <?php
                            $permisions_werehouse = explode(",", $user_warehouse);
                            $wh[""] = lang('select') . ' ' . lang('Location');

                            foreach ($warehouses as $warehouse) {
                                if ($Owner || $Admin || in_array($warehouse->id, $permisions_werehouse)) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                            }

                            // Set selected warehouse
                            if (!$Owner && !$Admin && empty($_POST['warehouse'])) {
                                // For regular users, set the first allowed warehouse as selected
                                $selected_warehouse = $permisions_werehouse[0];
                            } else {
                                // For admin/owner or if user selected manually
                                $selected_warehouse = isset($_POST['warehouse']) ? $_POST['warehouse'] : '';
                            }

                            // Render dropdown
                            echo form_dropdown('warehouse', $wh, $selected_warehouse, 'class="form-control" id="warehouse"');
                            ?>

                        </div>
                    </div>

                </div> <!-- end row -->

                <!-- Submit & Reset Buttons -->
                <div class="row">
                    <div class="col-md-12 text-right" style="margin-bottom:10px;">
                        <?= form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                        <a id="report_reset" href="<?= base_url('reports/sales_dash'); ?>" class="btn btn-warning">Reset</a>
                    </div>
                </div>

                <?= form_close(); ?>
                <div class="clearfix"></div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" id="report_table">
                    <?= lang('loading_data_from_server') ?>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<div id="modalDailySales" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:80%; max-height: 500px;">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="model_title"></h4>
      </div>
      <div class="modal-body" id="model_body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>


<script type="text/javascript">
       
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRes_dashReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRes_dashReport/0/xls/?v=1'.$v)?>";
            return false;
        });        
		$('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getRes_dashReport/0/0/img/?v=1'.$v)?>";
            return false;
    });
    $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

        const timeSlotSelect = document.getElementById('time_slot');
        function updateDateInputMode() {
            const hasSlot = $('#time_slot').val() !== '';
            if (hasSlot) {
                // Use chooser hidden fields
                $('#start_date, #end_date').prop('disabled', false);
                let today = new Date();
                let day = today.getDate().toString().padStart(2, '0');  // Adds leading zero if day < 10
                let month = (today.getMonth() + 1).toString().padStart(2, '0');  // Months are 0-indexed, so add 1
                let year = today.getFullYear();

                // Combine them into "d/m/Y" format
                let formattedDate = `${day}/${month}/${year}`;
                if(! $('#start_date').val()) {
                    $("#daterange_new").val(`${formattedDate} - ${formattedDate}`);
                    $('#start_date').val(formattedDate)  ;
                    $('#end_date').val(formattedDate) ;
                }
                console.log("start", $('#start_date').val());
                // Disable manual fields so they are not submitted
                $('#start_date_input, #end_date_input').prop('disabled', true);
            } else {
                // Use manual fields
                $('#start_date, #end_date').prop('disabled', true);
                $('#start_date_input, #end_date_input').prop('disabled', false);
            }
        }
        console.log("timeSlotSelect:", timeSlotSelect.options.selectedIndex);
        if(timeSlotSelect.options.selectedIndex == 0) {
            console.log("No time slot selected, showing manual date inputs");
            document.querySelector('.choose-date').closest('.choose-date-div').style.setProperty('display', 'none', 'important');
            $('.manual-dates').show();
            updateDateInputMode();
        } else {
            console.log("Time slot selected, hiding manual date inputs");
            document.querySelector('.choose-date').closest('.choose-date-div').style.setProperty('display', 'block', 'important');

            $('.manual-dates').hide();
            updateDateInputMode();
        }
        $('#time_slot').on('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    console.log("Selected option",selectedOption);
                    if(selectedOption) {
                        if(selectedOption.value) {
                            document.querySelector('.choose-date').closest('.choose-date-div').style.setProperty('display', 'block', 'important');

                            $('.manual-dates').hide();
                            updateDateInputMode();
                            return;
                        } 
                        document.querySelector('.choose-date').closest('.choose-date-div').style.setProperty('display', 'none', 'important');
                        $('.manual-dates').show();
                        updateDateInputMode();
                        return;
                    }
                });
        // Ensure correct inputs are enabled on submit as well
        $('form').on('submit', function() {
            updateDateInputMode();
        });
    });
  
     $.ajax({
            type: "get",
            url: 'reports/getRes_dashReport',
            data:"<?='v=1'.$v?>",
            beforeSend: function(){
                $("#report_table").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i>Loading data from server</div>");
                
            },
            success: function(data){			 
               $("#report_table").html(data);
               
            //    setTimeout(function(){ $('#warehouses_products').DataTable(); }, 1000);
            setTimeout(function () {
    $('#warehouses_products').DataTable({
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        footerCallback: function (row, data, start, end, display) {
            const api = this.api();

            const columnCount = api.columns().count();

            // warehouse columns start from index 3 up to (columnCount-2)
            const startIndex = 3;
            const endIndex = columnCount - 2; // because last column is "Total"

            for (let i = startIndex; i <= endIndex; i++) {
                let total = api
                    .column(i, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        const x = parseFloat(a.toString().replace(/,/g, '')) || 0;
                        const y = parseFloat(b.toString().replace(/,/g, '')) || 0;
                        return x + y;
                    }, 0);

                $(api.column(i).footer()).html(total.toLocaleString());
            }

            let grandTotal = 0;

api.rows({ page: 'current' }).every(function (rowIdx) {
    const rowData = this.data();

    const totalCellRaw = rowData[columnCount - 1];
    console.log(`Row ${rowIdx}: Raw cell value:`, totalCellRaw);

    const totalCellClean = totalCellRaw.toString().replace(/<[^>]*>/g, '').replace(/,/g, '');
    console.log(`  Cleaned cell value:`, totalCellClean);

    const totalCell = parseFloat(totalCellClean) || 0;
    console.log(`  Parsed cell value:`, totalCell);

    grandTotal += totalCell;
    console.log(`  Running grandTotal:`, grandTotal);
});

console.log(`Final grandTotal:`, grandTotal);

$(api.column(columnCount - 1).footer()).html(grandTotal.toLocaleString());

        }
    });
}, 1000);

            }
	}); 
        
    function getsaleitems(startdate, enddata, wh, wh_name) {
    $('#model_title').html(wh_name + ' items sale report dated between: ' + startdate + ' to ' + enddata);
    $('#model_body').html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait ... </h4>');

    var postData = 'startdate=' + startdate + '&enddata=' + enddata + '&werehouse=' + wh;
    var href = '<?= site_url('reports/get_sales_items'); ?>?' + postData;

    $.get(href, function(data) {
        $("#model_body").html(data);

        // ✅ Initialize DataTable AFTER the table is added to the DOM
        $('#salesItemsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            info: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            footerCallback: function (row, data, start, end, display) {
                const api = this.api();

                // Robust parser: handles currency symbol anywhere, arabic-Indic digits,
                // Arabic decimal/thousand separators, parentheses negatives, etc.
                const parseNumber = (input) => {
                    if (input == null) return 0;
                    let text = String(input).trim();

                    // map Arabic-Indic digits (U+0660..U+0669) and Eastern Arabic-Indic (U+06F0..U+06F9) to ASCII
                    text = text.replace(/[\u0660-\u0669]/g, c => String(c.charCodeAt(0) - 0x0660));
                    text = text.replace(/[\u06F0-\u06F9]/g, c => String(c.charCodeAt(0) - 0x06F0));

                    // map Arabic decimal/thousand separators to '.' and ','
                    text = text.replace(/\u066B/g, '.').replace(/\u066C/g, ',');

                    // detect negative (parentheses or leading -)
                    let negative = false;
                    if (/^\(.*\)$/.test(text)) {
                        negative = true;
                        text = text.replace(/^\(|\)$/g, '');
                    } else if (/^\s*-/.test(text)) {
                        negative = true;
                        text = text.replace(/^\s*-/, '');
                    }

                    // keep only digits, dot and comma
                    let s = text.replace(/[^0-9.,]/g, '');
                    if (!s) return 0;

                    const lastDot = s.lastIndexOf('.');
                    const lastComma = s.lastIndexOf(',');

                    if (lastDot > -1 && lastComma > -1) {
                        // both present -> rightmost one is decimal separator
                        if (lastDot > lastComma) {
                            s = s.replace(/,/g, ''); // comma was thousands separator
                        } else {
                            s = s.replace(/\./g, ''); // dot was thousands separator
                            s = s.replace(/,/g, '.'); // comma is decimal
                        }
                    } else if (lastComma > -1) {
                        const parts = s.split(',');
                        // if last group has length 3 it's likely thousands separators (e.g. 1,234)
                        if (parts.length > 1 && parts[parts.length - 1].length === 3) {
                            s = s.replace(/,/g, '');
                        } else {
                            s = s.replace(/,/g, '.'); // comma as decimal
                        }
                    } else if (lastDot > -1) {
                        const parts = s.split('.');
                        if (parts.length > 1 && parts[parts.length - 1].length === 3) {
                            s = s.replace(/\./g, ''); // dot as thousands separator
                        }
                        // else dot is decimal -> keep it
                    }

                    const num = parseFloat(s);
                    return (isNaN(num) ? 0 : (negative ? -num : num));
                };

                let totalQty = 0, totalTax = 0, totalDiscount = 0, totalAmount = 0, totalprice = 0;

                api.rows({ page: 'current' }).nodes().each(function (row) {
                    const cols = $('td', row);

                    const price = parseNumber(cols.eq(4).text());       // price Amount
                    const qty = parseNumber(cols.eq(5).text());       // Qty (may be decimal)
                    const tax = parseNumber(cols.eq(8).text());       // Tax Amount
                    const discount = parseNumber(cols.eq(9).text());  // Discount
                    const total = parseNumber(cols.eq(10).text());    // Total

                    totalQty += qty;
                    totalprice += price;
                    totalTax += tax;
                    totalDiscount += discount;
                    totalAmount += total;
                });

                // show quantities with up to 2 decimals if needed (don't forcibly round)
                const qtyDisplay = Number.isInteger(totalQty) ? totalQty : totalQty.toFixed(2);

                $(api.column(5).footer()).html(`<div style="text-align: center;">${qtyDisplay}</div>`);

                // use your existing currencyFormat if present; otherwise fallback
                const formatCurrency = (value) => {
                    if (typeof currencyFormat === 'function') {
                        return currencyFormat(value);
                    }
                    return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                };

                $(api.column(4).footer()).html(formatCurrency(totalprice));
                $(api.column(8).footer()).html(formatCurrency(totalTax));
                $(api.column(9).footer()).html(formatCurrency(totalDiscount));
                $(api.column(10).footer()).html(formatCurrency(totalAmount));
            }
        });

    });

    $('#modalDailySales').modal('show');
}

</script>
<style>
    .right {
    text-align: right !important;
}
.dataTables_filter {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}
.center {
    text-align: center !important;
}
</style>
<script>
// Date range is handled globally by core.js on #daterange_new, which fills #start_date and #end_date.
</script>