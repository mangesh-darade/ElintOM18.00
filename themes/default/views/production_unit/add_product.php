    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    ?>
    <style>
    .custom-suggestions {border: 1px solid #ccc;
        background: #FFF;
        color: #333333;
        position: absolute;
        z-index: 1;
        width: 91.5%;
        left: 7.4rem;
        padding: 1rem;
        line-height: 2rem;
        font-size: 1.1em;
    }
    .box .box-content{
        height: 640px;  
        overflow:auto;
    }

        .suggestion{
            position: relative;
        margin: 0;
        padding: 3px 1em 3px .4em;
        cursor: pointer;
        min-height: 0;
        list-style-image: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7);
        }

        .suggestion:hover{
            background: #0088cc;
            color: #fff;
        }
        .final-btn{
            box-shadow: rgba(255, 255, 255, 0.5) 2px 2px 2px 0px inset, rgba(0, 0, 0, 0.1) 7px 7px 20px 0px, rgba(0, 0, 0, 0.1) 4px 4px 5px 0px;
            outline: none;
            border-radius:0.4rem;
        }
      
        </style>
    <script>
    $(document).ready(function() {
        $('#product-search').on('input', function() {
            
            const term = $(this).val();

            if (term.length >= 2) {
                $.ajax({
                    url: 'Production_Unit/suggestions',
                    type: 'GET',
                    data: {
                        term: term
                    },
                    dataType: 'json',
                    success: function(data) {

                        const suggestions = data.map(product => {
                            const variantDisplay = product.varient_name ?
                                ` (${product.varient_name})` : '';

                            return `<div class="suggestion" 
                            data-id="${product.id}" 
                            data-name="${product.name}" 
                            data-varient_name="${product.varient_name || ''}">
                            ${product.name}${variantDisplay}
                        </div>`;
                        }).join('');
                        $('#suggestions').html(suggestions).show();
                    }
                });
            } else {
                $('#suggestions').hide();
            }
        });

        $(document).on('click', '.suggestion', function() {
            var productId = $(this).data('id');
            var productName = $(this).data('name');
            var variantName = $(this).data('varient_name');

            $('#slTable tbody').append(`
                <tr>
                <td style="text-align: center;">${productName}</td>
                    <td style="text-align: center;">${variantName}</td>
                    <td style="text-align: center;"><i class="fa fa-trash-o remove-row" onclick="removeRow(this)"></i>
                        <input type="hidden" name="productIds[]" value="${productId}">
                        <input type="hidden" name="variant_names[]" value="${variantName}">
                    </td>
                </tr>
                
            `);

            $('#product-search').val('');
            $('#suggestions').hide();
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });
    });
    </script>



    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
    <link href="<?= $assets ?>production_unit/css/order_dispatch.css" rel="stylesheet" />
    <!-- <script type="text/javascript" src="<?= $assets ?>production_unit/js/add_products.js"></script> -->


    <!-- <input type="hidden" name="sale_action" id="sale_action" value="<?php echo $sale_action; ?>"> -->
    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Add_Product'); ?></h2>
        </div>
        <!-- <p class="introtext"><?php echo lang('enter_info'); ?></p> -->
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                    echo form_open_multipart("Production_Unit/add_product", $attrib); ?>
                    <div class="row">
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i>
                                        </div>
                                        <?php echo form_input('product-search', '', 'class="form-control input-lg" id="product-search" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                        <?php if($Settings->barcode_scan_camera){ ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                id="scancamerabtn" data-target="#scan_barcode_camera">
                                                <i class="fa fa-camera"></i> Scan
                                            </button>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div id="suggestions" class="custom-suggestions"
                                    style="display: none; border: 1px solid #ccc; max-height: 250px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("Products"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="slTable"
                                        class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th><?= lang("product_name") ?></th>
                                                <th><?= lang("Variant") ?></th>
                                                <!-- <th class="col-md-5">Variant</th> -->
                                                <!-- <th class="col-md-2">Quantity</th> -->
                                                <th style="width: 40px !important; text-align: center;">
                                                    <i class="fa fa-trash-o"
                                                        style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Table rows will be dynamically added here -->
                                        </tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="submit_type"  value="">
                        <div class="fprom-group text-center">
                            <?php echo form_submit('', lang("Add Products"), ' class="btn btn-primary final-btn" style="padding: 6px 15px; margin:15px 0;"'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>