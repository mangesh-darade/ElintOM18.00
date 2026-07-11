<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.custom-checkbox-inline {
    display: flex;
    align-items: center;
    font-size: 14px;
    gap: 8px;
    /* space between checkbox and text */
    cursor: pointer;
}

.custom-checkbox {
    appearance: none;
    -webkit-appearance: none;
    background-color: #fff;
    border: 2px solid #007bff;
    border-radius: 3px;
    width: 22px;
    height: 22px;
    position: relative;
    cursor: pointer;
    vertical-align: middle;
}

.custom-checkbox:checked::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 6px;
    width: 5px;
    height: 12px;
    border: solid #007bff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.custom-checkbox:focus {
    outline: none;
    box-shadow: none;
    border-color: #007bff;
}
</style>
<div class="box">
    <!-- <div class="box-header">
        <div class="col-sm-10">
            <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Active Orders')  ?> <span id="lastsynch"> </span></h2>
        </div>
        <div class="col-sm-2"><a href="<?= base_url('Omnichannel/orders_inactive') ?>" class="btn btn-primary">Inactive
                Orders</a></div>
    </div> -->
    <div class="box-header">
        <div class="box-content mobile-hei">
            <div class="row" style="margin-left: 0; margin-right: 0;">
                <!-- Received -->
                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom: 10px; margin-right: 70px; margin-left: 60px;">
                    <div class="quick-button small" style="display: inline-flex; align-items: center; background-color: black; color: white; padding: 6px 10px; border-radius: 4px; width: 100%;">
                        <span style="font-size: 16px; margin-right: 15px; white-space: nowrap;">
                            <?= lang('Received') ?>
                        </span>
                        <span id="count-received" style="font-size: 18px; font-weight: bold; background: white; color: black; padding: 2px 6px; border-radius: 4px;">
                            <?= isset($status_counts['Received']) ? $status_counts['Received'] : 0 ?>
                        </span>
                    </div>
                </div>
                <!-- In Progress -->
                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom: 10px; margin-right: 70px;">
                    <div class="quick-button small" style="display: inline-flex; align-items: center; background-color: #fa8507; color: white; padding: 6px 10px; border-radius: 4px; width: 100%;">
                        <span style="font-size: 16px; margin-right: 15px; white-space: nowrap;">
                            <?= lang('In Progress') ?>
                        </span>
                        <span id="count-in-progress" style="font-size: 18px; font-weight: bold; background: white; color: #fa8507; padding: 2px 6px; border-radius: 4px;">
                            <?= isset($status_counts['In Progress']) ? $status_counts['In Progress'] : 0 ?>
                        </span>
                    </div>
                </div>
                <!-- Food Ready -->
                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom: 10px; margin-right: 70px;">
                    <div class="quick-button small" style="display: inline-flex; align-items: center; background-color: #28a745; color: white; padding: 6px 10px; border-radius: 4px; width: 100%;">
                        <span style="font-size: 16px; margin-right: 15px; white-space: nowrap;">
                            <?= lang('Ready') ?>
                        </span>
                        <span id="count-food-ready" style="font-size: 18px; font-weight: bold; background: white; color: #28a745; padding: 2px 6px; border-radius: 4px;">
                            <?= isset($status_counts['Food Ready']) ? $status_counts['Food Ready'] : 0 ?>
                        </span>
                    </div>
                </div>
                <!-- Dispatched -->
                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom: 10px; margin-right: 70px;">
                    <div class="quick-button small" style="display: inline-flex; align-items: center; background-color: #428bca; color: white; padding: 6px 10px; border-radius: 4px; width: 100%;">
                        <span style="font-size: 16px; margin-right: 15px; white-space: nowrap;">
                            <?= lang('Dispatched') ?>
                        </span>
                        <span id="count-dispatched" style="font-size: 18px; font-weight: bold; background: white; color: #428bca; padding: 2px 6px; border-radius: 4px;">
                            <?= isset($status_counts['Dispatched']) ? $status_counts['Dispatched'] : 0 ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div id="showmsgalert"></div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered" id="orderlist">
                        <thead>
                            <tr>
                                <!-- <th>#</th> -->
                                <th>Order Time</th>
                                <th>Order Number</th>
                                <th style="width: 10% !important;">Customer</th>
                                <th>Amount</th>
                                <th>Channel</th>
                                <th>
                                    <label style="display: inline-block;">
                                        <div style="font-weight: bold;">Status</div>
                                        <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                            <input type="checkbox" id="filterDispatched" class="custom-checkbox" />
                                            <span style="margin-top: 2px;">Dispatched</span>
                                        </div>
                                    </label>
                                </th>
                                <!-- <th>Payment</th> -->
                                <th colspan="1" style="width: 12% !important;">Action</th>
                                <th>Other Actions</th>

                                <!-- <th>Order Id</th>
                                <th>Delivery</th>
                                <th>Rider</th>
                                <th>Rider OTP</th> -->
                            </tr>
                        </thead>
                        <tbody id="uporderstable">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="myModal" class="modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content -->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modeltitle"></h4>
            </div>
            <div class="modal-body">
                <h3 class="text-center" id="showmsg"></h3>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirm_ok" style="display:none;"
                    class="btn btn-success msg_model_buttons_ok"> Ok </button>
                <button type="button" id="closemodel" class="btn btn-danger msg_model_buttons_close"
                    data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Message model --->

<!-- Pass Status -->
<div id="pass_datamodal" class="modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close closemodels" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"> Message</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="hidden" id="up_order_id" />
                    <input type="hidden" id="up_order_status" />
                    <label> Message </label>
                    <input type="text" name="message" placeholder="Message" id="status_message" class="form-control">
                </div>
            </div>
            <div class="modal-footer" id="msg_model_buttons">
                <button type="button" id="passdatastatus" class="btn btn-success "> Ok </button>
                <button type="button" class="btn btn-danger closemodels " data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--  End pass Status -->


<!-- Modal -->
<div class="modal fade" id="orderdetails" role="dialog">
    <div class="modal-dialog" style="width:80%;">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="orderdetailtitle">Modal Header</h4>
            </div>
            <div class="modal-body" id="model_body">
                <p>Some text in the modal.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
<!--<img src="< ?= $assets ?>images/loader.gif" class="loaderclass" id="pageloader">-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Event listener for all buttons with class "order_status_whatsapp"
    $('.order_status_whatsapp').on('click', function() {
        var buttonText = $(this).text(); // Get the button text
        var orderId = $(this).data('order-id'); // Get the order ID from data attribute

        var dataToSend = {
            order_id: orderId,
            order_status: buttonText
        };
        var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
        dataToSend[csrfName] = csrfHash;
        $.ajax({
            url: '<?= base_url('webshop/call_whatsapp_api') ?>',
            type: 'POST',
            data: dataToSend,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    // alert("Done")
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Request failed');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                console.error('XHR:', xhr);
            }
        });

    });
});

$('#filterDispatched').change(function() {
    console.log("Checkbox changed");
    const includeDispatched = $(this).is(':checked');
    loadWebshopOrders(includeDispatched);
});

function loadWebshopOrders(includeDispatched) {
    $.ajax({
        url: '<?= site_url("Omnichannel/orders_list_webshop/") ?>',
        type: "GET",
        data: {
            include_dispatched: includeDispatched ? 1 : 0
        },
        success: function(html) {
            $('#uporderstable').html(html);
        }
    });
}
$(document).ready(function() {
    $('#pageloader').hide();
    $('#confirm_ok').hide();

});

// Get the modal
var modal = document.getElementById('myModal');
var statusmodal = document.getElementById('pass_datamodal');

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal 


// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

$('#closemodel').click(function() {
    modal.style.display = "none";
});

$('.closemodels').click(function() {
    statusmodal.style.display = "none";
    $('#status_message').val('');

});
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
    if (event.target == statusmodal) {
        statusmodal.style.display = "none";
    }
}

function order_details(saleid) {

    $('#orderdetailtitle').html('Order Details');

    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_details/") ?>' + saleid,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);
        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });
}

function order_detailsforwebshop(saleid) {
    $('#orderdetailtitle').html('Order Details');
    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_detailsforwebshop/") ?>' + saleid,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);
        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });
}
function order_detailsforpos(saleid) {
    $('#orderdetailtitle').html('Order Details');
    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_detailsforpos/") ?>' + saleid,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);
        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });
}
function order_kot(saleid) {
    $('#orderdetailtitle').html('KOT Details');
    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_kot/") ?>' + saleid,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);

            printDiv('printableArea');

        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });

}

function order_kotforwebshop(saleid) {
    $('#orderdetailtitle').html('KOT Details');
    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_kot_forwebshop/") ?>' + saleid,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);

            printDiv('printableArea');

        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });

}

function printDiv(divId) {
    var printContents = document.getElementById(divId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = "<html><head><title></title></head><body>" + printContents + "</body>";
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload()
}

function reloadOrdersTable() {

        $.ajax({
            type: "GET",
            url: '<?= site_url("Omnichannel/orders_list_combined") ?>',
            beforeSend: function() {
                $("#uporderstable").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
            },
            success: function(data) {
                $("#uporderstable").html(data);

        },
        error: function() {
            $("#uporderstable").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });


}
function refreshOrderStatusCounts() {
    $.ajax({
        url: "<?= site_url('Omnichannel/get_order_status_counts_ajax') ?>", 
        type: "GET",
        dataType: "json",
        success: function(data) {
            $("#count-received").text(data['Received'] ?? 0);
            $("#count-in-progress").text(data['In Progress'] ?? 0);
            $("#count-food-ready").text(data['Food Ready'] ?? 0);
            $("#count-dispatched").text(data['Dispatched'] ?? 0);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching counts:", error);
        }
    });
}
setInterval(refreshOrderStatusCounts, 10000);
refreshOrderStatusCounts();



function order_status(order_id, order_status) {

    $('#up_order_id').val('');
    $('#up_order_status').val('');
    $('#status_message').val('');
    $('#confirm_ok').show();
    $('#showmsg').html('Are you sure to change ' + order_status + ' order status?');
    $('#modeltitle').html('confirm');
    modal.style.display = "block";

    $('#confirm_ok').click(function() {
        modal.style.display = "none";
        statusmodal.style.display = "block";
        $('#up_order_id').val(order_id);
        $('#up_order_status').val(order_status);
    });
}


$('#passdatastatus').click(function() {
    statusmodal.style.display = "none";
    modal.style.display = "block";
    var order_id = $('#up_order_id').val();
    var order_status = $('#up_order_status').val();
    var status_message = $('#status_message').val();
    // statusmodal.style.display = "none";
    $('.msg_model_buttons_ok').hide();
    $('.msg_model_buttons_close').hide();
    $('#showmsg').html(
        '<div style="text-align:center;"><img src="<?= base_url('assets/images/ajax-loader.gif') ?>" alt="please wait loading..."></div>'
    );

    setTimeout(function() {
        update_order_status(order_id, order_status, status_message);
    }, 500);
});


function update_order_status(order_id, order_status, status_message) {
    $('.msg_model_buttons_close').show();
    $('#modeltitle').html('response');
    $.ajax({
        type: 'ajax',
        dataType: 'json',
        url: '<?= site_url("Omnichannel/update_order_status/") ?>' + order_id + '/' + order_status,
            
        async: false,

        // success: function(result) {

        //     if (result.status == 'success') {

        //         $('#showmsgalert').html('<div class="alert alert-success">' + result.message + '</div>');
        //         $('#showmsg').html('<div class="alert alert-success">' + result.message + '</div>');

        //         $('#current_status_' + order_id).html(order_status);
        //         $('#current_status_' + order_id).removeClass('btn-info');
        //         $('#current_status_' + order_id).removeClass('btn-warning');
        //         $('#current_status_' + order_id).removeClass('btn-primady');
        //         $('#current_status_' + order_id).removeClass('btn-danger');
        //         $('#current_status_' + order_id).addClass('btn-success');
        //         order_status = (order_status == 'Food Ready') ? 'FoodReady' : order_status;

        //         $('#' + order_status + '_status_' + order_id).hide();
        //     } else {
        //         $('#showmsgalert').html('<div class="alert alert-danger">' + result.message + '</div>');
        //         $('#showmsg').html('<div class="alert alert-danger">' + result.message + '</div>');
        //     }


        //     setTimeout(function() {
        //         modal.style.display = "none";
        //     }, 1000);
        //     // setTimeout(function(){ location.reload(true); }, 2000);

        //     //               modal.style.display = "block";

        // },
        // error: function() {
        //     console.log('error');
        // }
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                alert('Status updated to ');
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Request failed');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            console.error('XHR:', xhr);
        }
    });
}

    //  setInterval(reloadOrdersTable, 10000);

reloadOrdersTable();



/**
 * Manage Stock Urbanpiper
 * @returns {undefined}     */
function checkStock() {
    $.ajax({
        type: 'ajax',
        method: 'get',
        url: '<?= base_url('Omnichannel/stockstatus') ?>',
        dataType: 'json',
        async: false,
        success: function(result) {
            if (result.status) {
                $('#urbanpiper-stock-alert').html(
                    '<div class="alert urbanpiper-stock_notify alert-success"><button type="button" class="close fa-2x" onclick="upstocknotify_close()" >&times;</button> ' +
                    result.message + ' </div>');
                $('#lastsynch').html('Last Synch :' + result.lastsync);
                $('.urbanpiper-stock_notify').show();
                setTimeout(function() {
                    $('.urbanpiper-stock_notify').hide();
                }, 30000);

            }
        }


    });
}

    // setInterval(checkStock, 180000);

function showDeliveringPopup(id) {
    $('#myModal').load(site.base_url + "Omnichannel/add_delivery/" + id, function() {
        $('#myModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });
}

function showDeliveredPopup(id) {
    $('#myModal').load(site.base_url + "Omnichannel/edit_delivery/" + id, function() {
        $('#myModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });
}

function callOrderReady(orderId, order, inv_items, payments, flag = null) {
    var dataToSend = order; // Send the entire order object
    dataToSend.inv_items = inv_items;
    dataToSend.payments = payments;
    dataToSend.flag = flag;
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    dataToSend[csrfName] = csrfHash;
    $.ajax({
        url: '<?= base_url('Omnichannel/createWebshopSales') ?>',
        type: 'POST',
        data: dataToSend,
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                if (flag === "flag_paynow") {
                    $.ajax({
                        url: '<?= base_url('orders/add_3p_order_payments') ?>',
                        method: 'GET',
                        data: {
                            order_id: orderId,
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert("Error: " + xhr.responseText);
                        }
                    });
                } else {
                    order_kotforwebshop(orderId);
                }
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Request failed');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            console.error('XHR:', xhr);
        }
    });
}

function updatestatus(orderId, sale_status) {
    var dataToSend = {
        order_id: orderId,
        sale_status: sale_status
    };
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    dataToSend[csrfName] = csrfHash;
    $.ajax({
        url: '<?= base_url('Omnichannel/updatestatus') ?>',
        type: 'POST',
        data: dataToSend,
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
            //    if (res.new_status == 'Ready') {
            //         updatecustomerorderstatus(orderId,res.new_status);
            //    }
                // alert('Status updated to ' + res.new_status + '!');
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Request failed');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            console.error('XHR:', xhr);
        }
    });
}

function pay_now_function(orderId, order, inv_items, payments, flag = null) {
    flag = "flag_paynow";
    callOrderReady(orderId, order, inv_items, payments, flag);
}
function order_details_amazon(orderId) {
    $('#orderdetailtitle').html('Amazon Order Details');
    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_details_amazon/") ?>' + orderId,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);
        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });
}

function order_kot_amazon(orderId) {
    $('#orderdetailtitle').html('Amazon KOT Details');
    $.ajax({
        type: "GET",
        url: '<?= site_url("Omnichannel/order_kot_amazon/") ?>' + orderId,
        beforeSend: function() {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data) {
            $("#model_body").html(data);
            printDiv('printableArea');
        },
        error: function() {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });
}

///////////////////////////////// Call Whatsapp API ///////////////////////////////
</script>
