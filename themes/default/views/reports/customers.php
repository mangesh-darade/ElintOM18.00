<style>
    thead th:nth-child(7) .yadcf-filter-wrapper input,
    thead th:nth-child(8) .yadcf-filter-wrapper input,
    thead th:nth-child(9) .yadcf-filter-wrapper input,
    thead th:nth-child(10) .yadcf-filter-wrapper input,
    thead th:nth-child(11) .yadcf-filter-wrapper input {
    text-align: right;
}

    tbody td:nth-child(7),
    tbody td:nth-child(8),
    tbody td:nth-child(9),
    tbody td:nth-child(10),
    tbody td:nth-child(11) {
    text-align:right;
}
.row-checkbox {
    width: 20px;   /* set desired width */
    height: 20px;  /* set desired height */
}
button {
    position: relative;
    display: contents;
}
button.send-bulk-whatsapp {
    display: inline-flex;
    align-items: center;
    gap: 7px; /* modern way to space icon and text */
    border: none;
    background: none;
    cursor: pointer;
    padding: 3px 19px;
    clear: both;
    font-weight: 400;
    line-height: 1.42857143;
    color: #333;
    white-space: nowrap;
}
.fa-bell:before {
    content: "\f0f3";
    margin-left: 8px;
}
button.send-bulk-whatsapp:hover{
    background-color: #f0f0f0;
}
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- <?php
$v = "";

if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('customer_group')) {
    $v .= "&customer_group=" . $this->input->post('customer_group');
}
?> -->
<?php
$v = "";

foreach (['start_date', 'end_date', 'customer_group', 'user', 'customer', 'biller', 'warehouse', 'filter_sale_type'] as $field) {
    if ($this->input->post($field)) {
        $v .= "&{$field}=" . urlencode($this->input->post($field));
    }
}
?>

<script>
    ////////////////////////////////// Whatsapp Integration /////////////////////////////////////
    const whatsapp_service = "<?php echo $this->Settings->whatsapp_service; ?>";
    $(document).on('click', '.send-bulk-whatsapp', function (e) {
          e.preventDefault();
         if (whatsapp_service == '0') {
                alert("WhatsApp reminders are currently disabled. Please contact the administrator to enable this feature.");
                return;
        }
        let selectedIds = [];
        $('.row-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select a customer.');
            return;
        }
        selectedIds.forEach(function (customerId) {
            const $row = $(`.row-checkbox[value="${customerId}"]`).closest('tr');
            const customer_name = $row.find('td').eq(2).text().trim();
            const phone = $row.find('td').eq(3).text().trim();
            const totalAmount = $row.find('td').eq(6).text().trim();
            const paid = $row.find('td').eq(7).text().trim();
            const balance = $row.find('td').eq(8).text().trim();
            const usedAmount = $row.find('td').eq(10).text().trim();
            $.ajax({
                url: site.base_url + "Customers/send_customer_reminder_on_whatsapp",
                type: 'GET',
                data: {
                    id: customerId,
                    total_amount: totalAmount,
                    customer_name: customer_name,
                    paid: paid,
                    balance: balance,
                    used_amount: usedAmount,
                    phone: phone,
                },
                dataType: 'json',
                success: function (response) {
                    console.log("Message sent for customer ID " + customer_name);
                },
                error: function (xhr, status, error) {
                    console.error('Error sending message for customer ID ' + customer_name, error);
                }
            });
        });

        alert("WhatsApp messages are being sent for selected customers.");
    });

    $(document).on('click', '.send-whatsapp', function (e) {
         e.preventDefault(); // prevent the href="#" from jumping to top
        if (whatsapp_service == '0') {
                alert("WhatsApp reminders are currently disabled. Please contact the administrator to enable this feature.");
                return;
        }
        try {
            const $row = $(this).closest('tr'); // Get the row
            const customerId = $(this).data('id');
            const customer_name = $row.find('td').eq(2).text().trim();
            const phone = $row.find('td').eq(3).text().trim();
            const totalAmount = $row.find('td').eq(6).text().trim();
            const paid = $row.find('td').eq(7).text().trim();
            const balance = $row.find('td').eq(8).text().trim();
            const usedAmount = $row.find('td').eq(10).text().trim();

            if (!customerId) {
                alert('Customer ID is missing.');
                return;
            }
            if (typeof site !== 'undefined' && site.base_url) {
                $.ajax({
                    url: site.base_url + "Customers/send_customer_reminder_on_whatsapp",
                    type: 'GET',
                    data: {
                        id: customerId,
                        total_amount: totalAmount,
                        customer_name: customer_name,
                        paid: paid,
                        balance: balance,
                        used_amount: usedAmount,
                        phone: phone,
                    },
                    dataType: 'json',
                    success: function (response) {
                        alert("WhatsApp message sent successfully!");
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        alert('Failed to send WhatsApp message.');
                    }
                });
            } else {
                console.error("site.base_url is undefined.");
            }
        } catch (err) {
            console.error("WhatsApp AJAX Error:", err);
        }
    });
$(document).ready(function () {
    const csrfName = "<?= $this->security->get_csrf_token_name() ?>";
    const csrfHash = "<?= $this->security->get_csrf_hash() ?>";

    function footerCallback(nRow, aaData, iStart, iEnd, aiDisplay, isWalkin = false) {
        let sales = 0, total = 0, paid = 0, balance = 0, recharge = 0, used = 0, deposit = 0;

        for (let i = 0; i < aiDisplay.length; i++) {
            const row = aaData[aiDisplay[i]];
            let offset = isWalkin ? 0 : 5;

            sales     += parseFloat(row[offset]) || 0;
            total     += parseFloat(row[offset+1]) || 0;
            paid      += parseFloat(row[offset+2]) || 0;
            balance   += parseFloat(row[offset+3]) || 0;

            if (!isWalkin) {
                recharge  += parseFloat(row[offset+4]) || 0;
                used      += parseFloat(row[offset+5]) || 0;
                deposit   += parseFloat(row[offset+6]) || 0;
            }

        }

        const nCells = nRow.getElementsByTagName('th');
        if (!isWalkin) {
            nCells[5].innerHTML = (sales);
            nCells[6].innerHTML = currencyFormat(total, 'footer');
            nCells[7].innerHTML = currencyFormat(paid, 'footer');
            nCells[8].innerHTML = currencyFormat(balance, 'footer');
            nCells[9].innerHTML = currencyFormat(recharge, 'footer');
            nCells[10].innerHTML = currencyFormat(used, 'footer');
            nCells[11].innerHTML = currencyFormat(deposit, 'footer');
        }

    }

    function rowCallback(nRow, aaData) {
        // if ((parseFloat(aaData[7]) || 0) > 0) {
        //     $(nRow).css('background-color', '#ffe0e0');
        // }
    }

    const customerColumns = [
        {
         bSortable: false,
            mRender: function (data, type, row) {
                const customerId = row[0];
                return `<input type="checkbox" class="row-checkbox" value="${customerId}" />`;
            },
        sClass: "text-center"
    },
        null,  // Company
        null,  // Name
        null,  // Phone
        null,  // Email
        { sClass: "text-center" },  
        { mRender: currencyFormat, sClass: "text-right" },  
        { mRender: currencyFormat, sClass: "text-right" },  
        { mRender: currencyFormat, sClass: "text-right" },  
        { mRender: currencyFormat, sClass: "text-right" },  
        { mRender: currencyFormat, sClass: "text-right" },  
        { mRender: currencyFormat, sClass: "text-right" },  
        { mRender: (data) => data ? new Date(data).toLocaleDateString() : '', sClass: "text-center" },
        { bSortable: false }
    ];

    const walkinColumns = [
    // null,  // Name
    { sClass: "text-center" },    // Total Sales
    { mRender: currencyFormat, sClass: "text-right" },    // Total Amount
    { mRender: currencyFormat, sClass: "text-right" }     // Paid
];
    let defaultOrder = [0, 'asc'];

    const filter_sale_type = $('#filter_sale_type').val();

    if (filter_sale_type === 'Top_Sale') {
        defaultOrder = [6, 'desc'];
    } else if (filter_sale_type === 'Bottom_Sale') {
        defaultOrder = [6, 'asc'];
    }

    $('#customersTable').dataTable({
        "aaSorting": [defaultOrder],
        "iDisplayLength": 10,
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?= site_url('reports/getCustomersData/?v=1'.$v) ?>',
        "fnServerData": function (sSource, aoData, fnCallback) {
            aoData.push({ name: "type", value: "customer" });
            aoData.push({ name: csrfName, value: csrfHash });
            aoData.push({ name: "user", value: $('#user').val() });
            aoData.push({ name: "customer", value: $('#customer').val() });
            aoData.push({ name: "biller", value: $('#biller').val() });
            aoData.push({ name: "warehouse", value: $('#warehouse').val() });
            aoData.push({ name: "filter_sale_type", value: $('#filter_sale_type').val() });

            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: sSource,
                data: aoData,
                success: fnCallback
            });
        },
        "aoColumns": customerColumns,
        "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
            footerCallback(nRow, aaData, iStart, iEnd, aiDisplay, false);
        },
        "fnRowCallback": rowCallback
    });

    $('#walkinTable').dataTable({
        "aaSorting": [[0, "asc"]],
        "iDisplayLength": 10,
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": '<?= site_url('reports/getCustomersData/?v=1'.$v) ?>',
        "fnServerData": function (sSource, aoData, fnCallback) {
            aoData.push({ name: "type", value: "walkin" });
            aoData.push({ name: csrfName, value: csrfHash });
            aoData.push({ name: "user", value: $('#user').val() });
            aoData.push({ name: "customer", value: $('#customer').val() });
            aoData.push({ name: "biller", value: $('#biller').val() });
            aoData.push({ name: "warehouse", value: $('#warehouse').val() });
            aoData.push({ name: "filter_sale_type", value: $('#filter_sale_type').val() });

            $.ajax({ dataType: 'json', type: 'POST', url: sSource, data: aoData, success: fnCallback });
        },
        "aoColumns": walkinColumns,
        "bPaginate": false,           
        "bInfo": false, 
        "bFilter": false,  
        // "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
        //     footerCallback(nRow, aaData, iStart, iEnd, aiDisplay, true);
        // },
        "fnRowCallback": rowCallback
    });
});
// console.log(customerColumns);
</script>


<style>
    .text-right {
        text-align: right;
    }
</style>


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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?><?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?></h2>
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
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
         <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="right" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                              <button class="send-bulk-whatsapp">
                                 <i class="fa fa-bell"></i> Send Reminder
                              </button>

                        </li>
                      
                    </ul>
                </li>
               
            </ul>
        </div>
    </div>
    <!-- <p class="introtext"><?= lang('view_report_customer'); ?></p> -->
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                  <div id="form">

                    <?php echo form_open("reports/customers"); ?>
                    <div class="row">
                        <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?= lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                            value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
                                            id="daterange_new" class="form-control">
                                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
                                        <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
                                        <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("customer_group", "customer_group"); ?>
                                <?php
                                echo form_dropdown(
                                    'customer_group',
                                    ['' => 'All Groups'] + $customer_groups,
                                    isset($_POST['customer_group']) ? $_POST['customer_group'] : '',
                                    'class="form-control" id="customer_group"'
                                );
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $perms_wh = explode(",", $user_warehouse);
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    if ($Owner || $Admin || in_array($warehouse->id, $perms_wh)) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="filter_sale_type"><?= lang("Filter Sale"); ?></label>
                                <?php
                                $us_sale[""] = lang('select').' '.lang('Sale');
                                $us_sale['No_sale'] = 'No sale';
                                $us_sale['Top_Sale'] = 'Top Sale';
                                $us_sale['Bottom_Sale'] = 'Bottom Sale';
                                echo form_dropdown('filter_sale_type', $us_sale, (isset($_POST['filter_sale_type']) ? $_POST['filter_sale_type'] : ""), 'class="form-control" id="filter_sale_type"');
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div  class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <!--<input type="button" id="report_reset" data-value="<?=base_url('reports/categories');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                             <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>
<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#customers">Customers</a></li>
  <li><a data-toggle="tab" href="#walkin">Walk-in Customers</a></li>
</ul>

<div class="tab-content">
  <!-- Tab 1 -->
  <div id="customers" class="tab-pane fade in active">
    <!-- <h3>Customers</h3> -->
    <table id="customersTable" class="table table-bordered">
      <thead>
        <tr>
          <th style="min-width:30px; width: 30px; text-align: center;">
                <input class="checkbox checkft" type="checkbox" name="check" />
          </th>
          <th>Company</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Total Sales</th>
          <th>Total Amount</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Recharge Amount</th>
          <th>Used Amount</th>
          <th>Deposit Balance</th>
          <th>Last Invoice Date</th>
          <th style="width:85px;"><?= lang("actions"); ?></th>

        </tr>
      </thead>
      <tbody></tbody>
        <tfoot class="dtFilter">
            <tr class="active">
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th class="text-center">Total Sales</th>
            <th class="text-center">Total Amount</th>
            <th class="text-center">Paid</th>
            <th class="text-center">Balance</th>
            <th class="text-center">Recharge Amount</th>
            <th class="text-center">Used Amount</th>
            <th class="text-center">Deposit Balance</th>
            <th class="text-center">Last Invoice Date</th>
            <th style="width:85px;"><?= lang("actions"); ?></th>
            </tr>
    </table>
  </div>

  <div id="walkin" class="tab-pane fade">
  <!-- <h3>Walk-in Customers</h3> -->
  <table id="walkinTable" class="table table-bordered">
  <thead>
<tr>
<!-- <th>Name</th> -->
<th>Total Sales</th>
<th>Total Amount</th>
<th>Paid</th>
</tr>
</thead>

<tbody></tbody>
</table>

</div>


</div>

                
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">


    $(document).ready(function () {
        function getActiveType() {
            if ($('.nav-tabs li.active a').attr('href') === '#walkin') {
                return 'walkin';
            }
            return 'customer';
        }
        let currentType = 'customer'; // default

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).attr("href");
    currentType = (target === '#customers') ? 'customer' : 'walkin';
});
        $('#pdf').click(function (event) {
            event.preventDefault();
            const type = getActiveType();
            window.location.href = "<?=site_url('reports/getCustomersData/pdf/?v=1'.$v)?>" + "&type=" + type;
            return false;
        });

        $('#xls').click(function (event) {
            event.preventDefault();
            const type = getActiveType();
            window.location.href = "<?=site_url('reports/getCustomersData/0/xls/?v=1'.$v)?>" + "&type=" + type;
            return false;
        });

        $('#image').click(function (event) {
            event.preventDefault();
            const type = getActiveType();
            window.location.href = "<?=site_url('reports/getCustomersData/0/0/img/?v=1'.$v)?>" + "&type=" + type;
            return false;
        });
    });

</script>

