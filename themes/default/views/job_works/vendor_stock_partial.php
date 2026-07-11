<?php if (!empty($vendor_stock_details)) { ?>
<div class="" style="margin-top: 0px;">
    <div class="box-content">
        <div class="">
            <h2 class="blue">Vendor Stock Details</h2>
            <p class="" style = "color: #fa8507">Note : Negative stock denotes surplus inventory available with the vendor.</p>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="vendorStockTable">
                        <thead>
                            <tr class="info">
                                <th>Vendor</th>
                                <th>Raw Material</th>
                                <th>Location</th>
                                <th>Unit</th>
                                <th>Stock</th>
                                <th>Req. For Pending Orders</th>
                                <th>To Be Supplied</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendor_stock_details as $idx => $row) { ?>
                            <tr class="vstock-row" data-idx="<?= $idx ?>" style="cursor: pointer;">
                                <td><?= $row->supplier_name ?></td>
                                <td><a href="#" class="vstock-link"><?= $row->product_name ?></a></td>
                                <td><?= $row->warehouse_name ?></td>
                                <td><?= $row->unit_name ?></td>
                                <td class="text-center"><?= $this->sma->formatQuantity($row->quantity) ?></td>
                                <td class="text-center"><?= $this->sma->formatQuantity($row->pending_required) ?></td>
                                <td class="text-center"><?= $this->sma->formatQuantity($row->to_supply) ?></td>
                            </tr>
                            <?php } // end foreach vendor_stock_details ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Detailed Breakdown Container (Initially Hidden) -->
                <div id="vstock-detail-container" style="display: none; margin-top: 20px; border-top: 2px solid #ddd; padding-top: 15px;">
                    <h4 id="vstock-detail-title" style="color: #428bca; margin-bottom: 15px;"></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed" id="vstock-detail-table">
                            <thead>
                                <tr class="active">
                                    <th>PO#</th>
                                    <th>Unit</th>
                                    <th>Total Req Qty</th>
                                    <th>Total Supplied</th>
                                    <th>Consumed Till Date</th>
                                    <th>Locked Stock</th>
                                    <th>Need To Be Supplied</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Populated by JS -->
                            </tbody>
                            <tfoot>
                                <tr style="font-weight:bold; background-color: #f0f0f0;">
                                    <td colspan="2">Total</td>
                                    <td class="text-center" id="dt-total-req">0.00</td>
                                    <td class="text-center" id="dt-total-sup">0.00</td>
                                    <td class="text-center" id="dt-total-cons">0.00</td>
                                    <td class="text-center" id="dt-total-lock">0.00</td>
                                    <td class="text-center" id="dt-total-need">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <script>
                    var vendorStockData = <?= json_encode($vendor_stock_details); ?>;
                    // Update main table if function exists
                    setTimeout(function() {
                        if (typeof window.updateAdditionalTransferCalculations === 'function') {
                            window.updateAdditionalTransferCalculations();
                        }
                    }, 200);
                </script>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    // Handle row click
    $(document).on('click', '.vstock-row', function(e) {
         // If clicked on a link inside the row, let the link handle it (prevent double trigger if needed)
         // But we want the whole row or the link to trigger the same action.
         e.preventDefault();
         
         var idx = $(this).data('idx');
         var data = vendorStockData[idx];
         
         // Visual feedback
         $('.vstock-row').removeClass('info'); // remove highlight
         $(this).addClass('info'); // highlight selected
         
         // Populate Details
         var details = data.details || [];
         var tbody = $('#vstock-detail-table tbody');
         tbody.empty();
         
         var t_req = 0, t_sup = 0, t_cons = 0, t_lock = 0, t_need = 0;
         
         if (details.length > 0) {
             $.each(details, function(i, d) {
                 var tr = $('<tr>');
                 tr.append($('<td>').text(d.po_reference_no));
                 tr.append($('<td>').text(d.unit_name));
                 tr.append($('<td>').addClass('text-center').html(formatQuantity(d.total_req_qty)));
                 tr.append($('<td>').addClass('text-center').html(formatQuantity(d.total_supplied)));
                 tr.append($('<td>').addClass('text-center').html(formatQuantity(d.total_consumed_till_date)));
                 tr.append($('<td>').addClass('text-center').html(formatQuantity(d.locked_stock)));
                 tr.append($('<td>').addClass('text-center').html(formatQuantity(d.need_to_supply)));
                 tbody.append(tr);
                 
                 t_req += parseFloat(d.total_req_qty) || 0;
                 t_sup += parseFloat(d.total_supplied) || 0;
                 t_cons += parseFloat(d.total_consumed_till_date) || 0;
                 t_lock += parseFloat(d.locked_stock) || 0;
                 t_need += parseFloat(d.need_to_supply) || 0;
             });
         } else {
             tbody.append('<tr><td colspan="7" class="text-center">No details available</td></tr>');
         }
         
         // Update Totals
         $('#dt-total-req').html(formatQuantity(t_req));
         $('#dt-total-sup').html(formatQuantity(t_sup));
         $('#dt-total-cons').html(formatQuantity(t_cons));
         $('#dt-total-lock').html(formatQuantity(t_lock));
         $('#dt-total-need').html(formatQuantity(t_need));
         
         // Show Container
         $('#vstock-detail-title').text('Vendor: ' + data.supplier_name + ' | RM: ' + data.product_name);
         $('#vstock-detail-container').show(); 
         
         // Scroll to details
         if (e.originalEvent) {
            $('#vstock-detail-container').slideDown();
             $('html, body').animate({
                 scrollTop: $("#vstock-detail-container").offset().top - 100
             }, 500);
         } else {
            $('#vstock-detail-container').show();
         }
    });

    // Trigger first row selection on load
    if ($('.vstock-row').length > 0) {
        $('.vstock-row').first().trigger('click');
    }
});
</script>
<?php } ?>
