<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .select2-container .select2-choice {
        display: block;
        height: 37px !important;
    }

    #notification {
        position: fixed;
        top: -4px;
        right: 30px;
        min-width: 250px;
        z-index: 9999;
        padding: 15px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    #POSData td:nth-child(5),
    #POSData tfoot th:nth-child(5){
       text-align: center !important; 
    }
    #POSData td:nth-child(6),
    #POSData td:nth-child(7),
    #POSData tfoot th:nth-child(6),
    #POSData tfoot th:nth-child(7),
    #POSData td:nth-child(8),
    #POSData tfoot th:nth-child(8) {
        width: 10% !important;
        text-align: left !important;
    }
</style>
<script>
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";
</script>
<script>
    $(document).ready(function() {
        $('#recent_pos_sale_modal-loading').hide();
        $('#POSData').dataTable({
            "bSort": false,
            "iDisplayLength": 5,
        });

        // Store original values of rider dropdowns on page load
        $('.rider-assign').each(function() {
            var currentValue = $(this).val();
            $(this).data('original-value', currentValue);
        });
        $(document).on('click', '.email_receipt', function(e) {

            e.preventDefault();

            var sid = $(this).attr('data-id');
            var ea = $(this).attr('data-email-address');
            var email = prompt("<?= lang("email_address"); ?>", ea);
            if (email != null) {
                $.ajax({
                    type: "post",
                    url: "<?= site_url('pos/email_receipt') ?>/" + sid,
                    data: {
                        <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>",
                        email: email,
                        id: sid
                    },
                    dataType: "json",
                    success: function(data) {
                        bootbox.alert(data.msg);
                        return true;
                    },
                    error: function() {
                        bootbox.alert('<?= lang('ajax_request_failed'); ?>');
                        return false;
                    }
                });
            }
        });

    });

    function showNotification(type, message) {
        let notif = $("#notification");
        notif.removeClass("d-none alert-success alert-danger")
            .addClass("alert-" + type)
            .text(message)
            .fadeIn();

        // Auto hide after 3 seconds
        setTimeout(function() {
            notif.fadeOut();
        }, 2000);
    }
    $(document).on('change', '.rider-assign', function(e) {
        console.log('Change event triggered');
        e.preventDefault();
        e.stopImmediatePropagation();

        var $this = $(this); // keep reference of current select
        var sale_id = $this.data('sale-id');
        var reference_no = $this.data('reference-no');
        var customer_id = $this.data('customer-id');
        var option = $this.find(':selected');
        var rider_id = option.val();
        var rider_name = option.data('rider-name');
        var rider_phone = option.data('rider-phone');
        if (rider_id) {
            // Get the previous value from the data attribute we'll store on page load
            var previousValue = $this.data('original-value') || '';

            // If there was already a rider assigned, show confirmation
            if (previousValue && previousValue !== '' && previousValue !== 'Select' && previousValue !== rider_id) {
                var previousOption = $this.find('option[value="' + previousValue + '"]');
                var previousRiderName = previousOption.length > 0 ? previousOption.text() : 'Unknown Rider';

                if (!confirm('This order is already assigned. Do you want to reassign it?')) {


                    $this.val(previousValue);
                    // $this.text(previousRiderName);
                    // if ($this.hasClass('select2-hidden-accessible')) {
                    $this.select2('val', previousValue);
                    document.querySelector('#select2-chosen-15').textContent = previousRiderName;
                    // }
                    return false; // Exit the entire function to prevent AJAX call
                }
            }

            $.ajax({
                type: "POST",
                url: "<?= site_url('pos/assign_delivery'); ?>",
                data: {
                    sale_id: sale_id,
                    reference_no: reference_no,
                    customer_id: customer_id,
                    delivery_person_id: rider_id,
                    delivered_by: rider_name,
                    delivered_person_phone: rider_phone,
                    <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
                },
                dataType: "json",
                success: function(res) {
                    console.log('AJAX success response:', res);
                    if (res.status === 'success') {
                        showNotification('success', res.message);
                        $this.data('original-value', rider_id);
                        var row = $this.closest('tr');
                        var deliveryStatusCell = row.find('td:nth-child(9)');
                        if (deliveryStatusCell.length > 0) {
                            deliveryStatusCell.text('On the way');
                        }

                        // disable the dropdown after success
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function() {
                    console.log('AJAX error');
                    toastr.error("<?= lang('ajax_request_failed'); ?>");
                }
            });
        }
    });

    function detailPosDetail(POSID, ActionName) {
        $('#recent_pos_sale_modal-loading').show();
        $('#recentPOsDetailModal').html('');
        if (ActionName == 'sale_detail_modal') {
            $('#recentPOsDetailModal').modal({
                remote: site.base_url + 'sales/modal_view/' + POSID
            });
            $('#recentPOsDetailModal').modal('show');

        }
        if (ActionName == 'view_payment') {
            $('#recentPOsDetailModal').modal({
                remote: site.base_url + 'sales/payments/' + POSID
            });
            $('#recentPOsDetailModal').modal('show');

        }
        if (ActionName == 'add_payment') {
            $('#recentPOsDetailModal').modal({
                remote: site.base_url + 'pos/add_payment/' + POSID
            });
            $('#recentPOsDetailModal').modal('show');

        }
        if (ActionName == 'add_delivery') {
            $('#recentPOsDetailModal').modal({
                remote: site.base_url + 'sales/add_delivery/' + POSID
            });
            $('#recentPOsDetailModal').modal('show');

        }

    }
    $('.rider-select').select2({
        placeholder: "<?= lang('select'); ?>",
        width: '100%',
        dropdownAutoWidth: true,
        dropdownParent: $('body') // or $('#myModal') if inside a modal
    });

    function markTakeawayDelivered(saleId) {
        if (!saleId) return;
        // Ask for confirmation first
        if (!confirm("Are you sure you want to mark this order as delivered?")) {
            let checkbox = document.querySelector('input.takeaway-pickup[data-sale-id="' + saleId + '"]');
            if ($(checkbox).data('iCheck')) {
                $(checkbox).iCheck('uncheck');
            } else {
                checkbox.checked = false;
            }
            return;
        }
        let formData = new URLSearchParams();
        formData.append('sale_id', saleId);
        formData.append(csrfName, csrfHash);

        fetch(base_url + 'pos/mark_takeaway_delivered', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', //Sending data in application/x-www-form-urlencoded format (like a normal HTML form).
                    'X-Requested-With': 'XMLHttpRequest' //tells the server this is an AJAX request.
                },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                console.log("Response:", data);
                if (data.status === 'success') {
                    //  Update delivery_status cell dynamically
                    let row = document.querySelector('input.takeaway-pickup[data-sale-id="' + saleId + '"]')
                        ?.closest('tr');
                    if (row) {
                        let statusCell = row.querySelector('td:nth-child(9)'); // 10th <td> = delivery_status column
                        if (statusCell) {
                            statusCell.textContent = data.new_status || 'Delivered';
                        }
                    }

                    //  Update checkbox (disable after delivered)
                    let checkbox = document.querySelector('input.takeaway-pickup[data-sale-id="' + saleId + '"]');
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.disabled = true;
                    }

                    //  Show toastr notification instead of alert
                    toastr.success((data.message || 'Order marked as delivered') + ' at ' + new Date().toLocaleTimeString());

                } else {
                    toastr.error(data.message || 'Something went wrong');
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error('Error marking order delivered.');
            });
    }


    // Native capture-phase fallback (works even when iCheck helper is clicked)
    document.addEventListener('click', function(e) {
        var el = e.target;
        if (!el) return;

        // If click lands on the iCheck helper span, locate the related input
        if (el.classList && el.classList.contains('iCheck-helper')) {
            var wrapper = el.parentNode; // .icheckbox_* wrapper
            if (wrapper) {
                var input = wrapper.querySelector('input.takeaway-pickup');
                if (input && !input.disabled) {
                    // Let iCheck toggle first, then act
                    setTimeout(function() {
                        if (input.checked) {
                            var id = input.getAttribute('data-sale-id') || '';
                            markTakeawayDelivered(id);
                        }
                    }, 0);
                }
            }
            return;
        }
    }, true);
</script>
<div id="notification" class="alert d-none" role="alert"></div>
<div class="modal-dialog modal-lg no-print" style="width:95%;">

    <div class="modal-content ">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <!--<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>-->

            <h4 class="modal-title"
                id="myModalLabel"><?= lang('recent_pos_list'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="POSData" class="table table-bordered table-hover table-striped pos_sale_table">
                    <thead>
                        <tr>

                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th><?= lang("Sale_Status"); ?></th>
                            <th><?= lang("Order_Type"); ?></th>
                            <th><?= lang("Rider"); ?></th>
                            <th><?= lang("Delivery"); ?></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        if (!empty($POSDATA)) {
                            foreach ($POSDATA as $ResValue) {
                                $POSId = $ResValue["id"];
                                $cemail = $ResValue["cemail"];
                                $SaleDetailModalName = "'sale_detail_modal'";
                                $AddPaymentName = "'add_payment'";
                                $ViewPaymentName = "'view_payment'";
                                $AddDeliveryName = "'add_delivery'";
                                $ViewReceiptName = "'view_receipt'";

                                $duplicate_link = anchor("sales/add?sale_id=$POSId", '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'), array('id' => 'SaleDetailsView', 'target' => 'new'));
                                $detail_link = anchor("pos/view/$POSId", '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'), array('target' => 'new'));
                                //$detail_link2 = anchor("sales/modal_view/$POSId", '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-target="#pos_details_views"');
                                $detail_link2 = anchor("#", '<i class="fa fa-file-text-o"></i> ' . lang('pos_details_modal'), 'data-toggle="modal" data-target="#pos_details_views"', array('onclick' => 'return detailPosDetail(' . $POSId . ');'));
                                $detail_link3 = anchor("sales/view/$POSId", '<i class="fa fa-file-text-o"></i> ' . lang('pos_details'), array('target' => 'new'));
                                $payments_link = anchor("sales/payments/$POSId", '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
                                $add_payment_link = anchor("pos/add_payment/$POSId", '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
                                $add_delivery_link = anchor("sales/add_delivery/$POSId", '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
                                $email_link = anchor('#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="' . $POSId . '" data-email-address="' . $cemail . '"');
                                $edit_link = anchor("sales/edit/$POSId", '<i class="fa fa-edit"></i> ' . lang('edit_sale'), array('target' => 'new', 'class' => 'sledit'));
                                $return_link = anchor("sales/return_sale/$POSId", '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'), array('target' => 'new'));
                                $delete_link = "<a href='javascript:void(0);' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
                                    . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$POSId') . "'>"
                                    . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                                    . lang('delete_sale') . "</a>";
                                $action = '<div class="text-center"><div class="btn-group text-left">'
                                    . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                                    . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li><a href="javascript:void(0);" onclick="return detailPosDetail(' . $POSId . ', ' . $SaleDetailModalName . ');"> <i class="fa fa-file-text-o"></i>' . lang('pos_details_modal') . '</a></li>
        <li>' . $detail_link3 . '</li>
        <li>' . $duplicate_link . '</li>
        <li>
        <li><a href="javascript:void(0);" onclick="return detailPosDetail(' . $POSId . ', ' . $ViewPaymentName . ');"> <i class="fa fa-money"></i>' . lang('view_payments') . '</a></li>
        <li><a href="javascript:void(0);" onclick="return detailPosDetail(' . $POSId . ', ' . $AddPaymentName . ');"> <i class="fa fa-money"></i>' . lang('add_payment') . '</a></li>
        <li><a href="javascript:void(0);" onclick="return detailPosDetail(' . $POSId . ', ' . $AddDeliveryName . ');"> <i class="fa fa-money"></i>' . lang('add_delivery') . '</a></li>
        <li>' . $edit_link . '</li>
        <li>' . $email_link . '</li>
        <li>' . $return_link . '</li>
      
    </ul>
</div></div>';
                        ?>
                                <tr>
                                    <td><?= $ResValue["date"]; ?></td>
                                    <td><?= $ResValue["reference_no"]; ?></td>
                                    <td><?= $ResValue["customer"]; ?></td>
                                    <td class="text-right"><?= $ResValue["grand_total"]; ?></td>
                                    <td><?= $ResValue["payment_status"]; ?></td>
                                    <td><?= $ResValue["sale_status"]; ?></td>
                                    <td><?= $ResValue["order_type"]; ?></td>
                                    <td>
                                        <?php if ($ResValue["order_type"] == 'Delivery') { ?>
                                            <select name="delivered_by"
                                                class="form-control rider-select rider-assign"
                                                id="delivered_by"
                                                data-sale-id="<?= $ResValue["id"]; ?>"
                                                data-reference-no="<?= $ResValue["reference_no"]; ?>"
                                                data-customer-id="<?= $ResValue["customer_id"]; ?>">
                                                <option><?= lang('select'); ?></option>
                                                <?php foreach ($delivery_person as $deliveryP) { ?>
                                                    <option value="<?= $deliveryP->id ?>"
                                                        data-rider-name="<?= $deliveryP->name ?>"
                                                        data-rider-phone="<?= $deliveryP->phone ?>"
                                                        <?php
                                                        // If delivery_person_id exists → select by ID
                                                        if (!empty($ResValue["delivery_person_id"]) && $ResValue["delivery_person_id"] == $deliveryP->id) {
                                                            echo 'selected';
                                                        }
                                                        // Else fallback to existing rider check (name~phone)
                                                        elseif (empty($ResValue["delivery_person_id"]) && !empty($ResValue["rider"]) && $ResValue["rider"] == ($deliveryP->name . '~' . $deliveryP->phone)) {
                                                            echo 'selected';
                                                        }
                                                        ?>>
                                                        <?= $deliveryP->name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        <?php } elseif ($ResValue["order_type"] == 'Take Away') { ?>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox"
                                                        class="takeaway-pickup"
                                                        name="pickup_<?= $ResValue['id']; ?>"
                                                        value="1"
                                                        data-sale-id="<?= $ResValue['id']; ?>"
                                                        data-reference-no="<?= $ResValue['reference_no']; ?>"
                                                        data-customer-id="<?= $ResValue['customer_id']; ?>"
                                                        title="Mark order #<?= $ResValue['id']; ?> as Delivered"
                                                        <?= (
                                                            (!empty($ResValue['sale_status']) && $ResValue['sale_status'] == 'Delivered')
                                                            || (!empty($ResValue['delivery_status']) && $ResValue['delivery_status'] == 'Delivered')
                                                        )
                                                            ? 'checked disabled'
                                                            : ''
                                                        ?>>
                                                    Pick Up
                                                </label>
                                            </div>

                                        <?php } else { ?>
                                            <div class="text-center">N/A</div>
                                        <?php } ?>
                                    </td>

                                    <td><?= $ResValue["delivery_status"]; ?></td>
                                    <td style="width:80px; text-align:center;"><?php echo $action; ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="dtFilter">
                        <tr class="active">

                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>