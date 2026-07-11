<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$csrfTokenName = $this->security->get_csrf_token_name();
$csrfTokenHash = $this->security->get_csrf_hash();
?>

<style>
    .vr-rate-input { width: 140px; display: inline-block; }
    .vr-modal .select2-container { width: 100% !important; }
    .vr-add-btn {
        margin-top: 9px;
        margin-right: 47px;
    }
    .vr-grid-wrap,
    .dataTables_wrapper {
        overflow-x: hidden;
    }
    #vendorRatesGrid {
        width: 100% !important;
    }
    #vendorRatesGrid td,
    #vendorRatesGrid th {
        word-break: break-word;
        white-space: normal;
    }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa fa-money"></i> Vendor Rates Grid
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <button type="button" class="btn btn-primary btn-xs vr-add-btn" id="btnAddVendorRate">
                        <i class="fa fa-plus"></i> Add
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('list_results'); ?></p>

    <div class="box-content">
        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <div class="table-responsive vr-grid-wrap">
            <table class="table table-bordered table-condensed table-hover table-striped" id="vendorRatesGrid" cellpadding="0" cellspacing="0" border="0">
                <thead>
                    <tr class="active">
                        <th>Vendor</th>
                        <th>Job Work</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Rate per item</th>
                        <th style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($rates)) { ?>
                    <?php foreach ($rates as $row) { ?>
                        <tr data-id="<?php echo (int) $row->id; ?>">
                            <td><?php echo html_escape($row->vendor); ?></td>
                            <td><?php echo html_escape($row->job_work_items); ?></td>
                            <td><?php echo html_escape($row->product_name); ?></td>
                            <td><?php echo html_escape($row->variant_name); ?></td>
                            <td class="rate-cell">
                                <span class="rate-value"><?php echo html_escape($row->rate_per_item); ?></span>
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-primary btn-xs js-rate-action"
                                        data-id="<?php echo (int) $row->id; ?>"
                                        data-mode="change">
                                    Change
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
                <tfoot class="dtFilter">
                    <tr class="active">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th style="text-align:center;"><?= lang('actions'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Add Vendor Rate Modal -->
<div class="modal fade vr-modal" id="vendorRateModal" tabindex="-1" role="dialog" aria-labelledby="vendorRateModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="vendorRateModalLabel">Add Vendor Rate</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="vrModalError" style="display:none;"></div>

                <div class="form-group">
                    <label>Vendor</label>
                    <select class="form-control select" id="vr_vendor">
                        <option value="">Select Vendor</option>
                        <?php if (!empty($vendors)) { foreach ($vendors as $v) { ?>
                            <option value="<?php echo (int) $v->id; ?>"><?php echo html_escape($v->company); ?></option>
                        <?php } } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Job Work</label>
                    <select class="form-control select" id="vr_job_work">
                        <option value="">Select Job Work</option>
                        <?php if (!empty($job_works)) { foreach ($job_works as $jw) { ?>
                            <option value="<?php echo (int) $jw->id; ?>"><?php echo html_escape($jw->items); ?></option>
                        <?php } } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product</label>
                    <select class="form-control select" id="vr_product">
                        <option value="">Select Product</option>
                        <?php if (!empty($products)) { foreach ($products as $p) { ?>
                            <option value="<?php echo (int) $p->id; ?>"><?php echo html_escape($p->name); ?></option>
                        <?php } } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Variant</label>
                    <select class="form-control select" id="vr_variant" disabled="disabled">
                        <option value="0">NA</option>
                    </select>
                    <small class="text-muted">If product has no variants, keep NA.</small>
                </div>

                <div class="form-group">
                    <label>Rate per item</label>
                    <input type="text" class="form-control" id="vr_rate" placeholder="Enter rate (numeric only)" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="vrSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var csrfTokenName = <?php echo json_encode($csrfTokenName); ?>;
        var csrfTokenHash = <?php echo json_encode($csrfTokenHash); ?>;
        var csrfData = {};
        csrfData[csrfTokenName] = csrfTokenHash;
        
        $(function () {
            if (!$.fn.dataTable) return;

            // Avoid "Cannot reinitialise DataTable" if this view is reloaded via PJAX/partials
            if ($.fn.dataTable.fnIsDataTable && $.fn.dataTable.fnIsDataTable($('#vendorRatesGrid')[0])) {
                $('#vendorRatesGrid').dataTable().fnDestroy();
            }

            $('#vendorRatesGrid').dataTable({
                "aaSorting": [[0, "asc"]],
                "aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "iDisplayLength": <?= isset($Settings->rows_per_page) ? (int) $Settings->rows_per_page : 10 ?>,
                "bPaginate": true,
                "bInfo": true,
                "bFilter": true,
                "bSort": true,
                "bAutoWidth": false,
                "aoColumnDefs": [
                    { "bSortable": false, "aTargets": [5] }
                ],
                "oLanguage": {
                    "sEmptyTable": "No data found",
                    "sSearch": "Search:"
                }
            });
        });

        function setSaving($btn) {
            $btn.prop('disabled', true);
            $btn.data('mode', 'saving');
            $btn.text('Saving...');
        }

        function setChange($btn) {
            $btn.prop('disabled', false);
            $btn.data('mode', 'change');
            $btn.text('Change');
        }

        function setSaveMode($btn) {
            $btn.prop('disabled', false);
            $btn.data('mode', 'save');
            $btn.text('Save');
        }

        // Event delegation for dynamic rows
        $('#vendorRatesGrid').on('click', '.js-rate-action', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var mode = $btn.data('mode');
            if (mode === 'saving') return;

            var $tr = $btn.closest('tr');
            var id = $btn.data('id');
            var $rateCell = $tr.find('.rate-cell');

            if (mode === 'change') {
                var currentVal = $.trim($tr.find('.rate-value').text());
                $rateCell.html('<input type="text" class="form-control input-sm vr-rate-input js-rate-input" value="' + currentVal + '" />');
                setSaveMode($btn);
                return;
            }

            if (mode === 'save') {
                var $input = $tr.find('.js-rate-input');
                var rate = $.trim($input.val());

                // Validate numeric (supports decimals)
                if (rate === '' || !/^\d+(\.\d+)?$/.test(rate)) {
                    alert('Invalid rate. Numbers only (example: 10 or 10.5).');
                    return;
                }

                setSaving($btn);

                $.ajax({
                    url: '<?php echo site_url('vendor_rates/update_rate'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: $.extend({
                        id: id,
                        rate: rate
                    }, csrfData),
                    success: function(resp) {
                        if (resp && resp.status) {
                            $rateCell.html('<span class="rate-value">' + resp.rate + '</span>');
                            setChange($btn);
                        } else {
                            alert((resp && resp.message) ? resp.message : 'Rate update failed');
                            // Keep input for retry
                            setChange($btn);
                        }
                    },
                    error: function() {
                        alert('Server error while saving rate.');
                        setChange($btn);
                    }
                });
            }
        });

        function vrShowError(msg) {
            $('#vrModalError').text(msg).show();
        }
        function vrClearError() {
            $('#vrModalError').hide().text('');
        }

        function resetModal() {
            vrClearError();
            $('#vr_vendor').val('').trigger('change');
            $('#vr_job_work').val('').trigger('change');
            $('#vr_product').val('').trigger('change');
            $('#vr_variant').html('<option value="0">NA</option>').prop('disabled', true).trigger('change');
            $('#vr_rate').val('');
            $('#vrSaveBtn').prop('disabled', false).text('Save');
        }

        $('#btnAddVendorRate').on('click', function(e){
            e.preventDefault();
            resetModal();
            $('#vendorRateModal').modal('show');
        });

        // Load variants when product changes
        $('#vr_product').on('change', function(){
            var productId = parseInt($(this).val() || '0', 10);
            $('#vr_variant').html('<option value="0">NA</option>').prop('disabled', true).trigger('change');
            if (!productId) return;

            $.ajax({
                url: '<?php echo site_url('vendor_rates/get_variants_by_product'); ?>',
                type: 'POST',
                dataType: 'json',
                data: $.extend({ product_id: productId }, csrfData),
                success: function(resp){
                    if (!resp || !resp.status) return;
                    var list = resp.variants || [];
                    var html = '<option value="0">NA</option>';
                    for (var i=0;i<list.length;i++){
                        html += '<option value="'+ list[i].id +'">'+ $('<div>').text(list[i].name).html() +'</option>';
                    }
                    $('#vr_variant').html(html).prop('disabled', false).trigger('change');
                }
            });
        });

        $('#vrSaveBtn').on('click', function(){
            vrClearError();
            var $btn = $(this);
            if ($btn.prop('disabled')) return;

            var vendor = parseInt($('#vr_vendor').val() || '0', 10);
            var job_work = parseInt($('#vr_job_work').val() || '0', 10);
            var product_id = parseInt($('#vr_product').val() || '0', 10);
            var variant_id = parseInt($('#vr_variant').val() || '0', 10);
            var rate = $.trim($('#vr_rate').val());

            if (!vendor || !job_work || !product_id) {
                vrShowError('Please select Vendor, Job Work and Product.');
                return;
            }
            if (rate === '' || !/^\d+(\.\d+)?$/.test(rate)) {
                vrShowError('Invalid rate. Numbers only (example: 10 or 10.5).');
                return;
            }

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '<?php echo site_url('vendor_rates/create_rate'); ?>',
                type: 'POST',
                dataType: 'json',
                data: $.extend({
                    vendor: vendor,
                    job_work: job_work,
                    product_id: product_id,
                    variant_id: variant_id,
                    rate_per_item: rate
                }, csrfData),
                success: function(resp){
                    if (resp && resp.status) {
                        // simplest: refresh to show new row in grid
                        window.location.reload();
                        return;
                    }
                    vrShowError((resp && resp.message) ? resp.message : 'Save failed');
                    $btn.prop('disabled', false).text('Save');
                },
                error: function(){
                    vrShowError('Server error while saving.');
                    $btn.prop('disabled', false).text('Save');
                }
            });
        });
    })();
</script>

