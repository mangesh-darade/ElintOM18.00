<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('group_permissions'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("set_permissions"); ?></p>

                <?php
                if (!empty($p)) {
                    if ($p->group_id != 1) {

                        echo form_open("system_settings/permissions/" . $id);
                        echo form_hidden('group', $id);
                        ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">

                                <thead>
                                    <tr>
                                        <th colspan="6"
                                            class="text-center"><?php echo $group->description . ' ( ' . $group->name . ' ) ' . $this->lang->line("group_permissions"); ?></th>
                                    </tr>
                                    <tr>
                                        <th rowspan="2" class="text-center"><?= lang("module_name"); ?>
                                        </th>
                                        <th colspan="5" class="text-center"><?= lang("permissions"); ?></th>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><?= lang("view"); ?></th>
                                        <th class="text-center"><?= lang("add"); ?></th>
                                        <th class="text-center"><?= lang("edit"); ?></th>
                                        <th class="text-center"><?= lang("delete"); ?></th>
                                        <th class="text-center"><?= lang("misc"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= lang("products"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-index" <?php echo $p->{'products-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-add" <?php echo $p->{'products-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-edit" <?php echo $p->{'products-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-delete" <?php echo $p->{'products-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="products_import" class="checkbox"
                                                           name="products_import" <?php echo $p->{'products-import'} ? "checked" : ''; ?>>
                                                    <label for="products_import"
                                                           class="padding05"><?= lang('products_import') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="products-cost" class="checkbox"
                                                           name="products-cost" <?php echo $p->{'products-cost'} ? "checked" : ''; ?>>
                                                    <label for="products-cost" class="padding05"><?= lang('product_cost') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="products-price" class="checkbox"
                                                           name="products-price" <?php echo $p->{'products-price'} ? "checked" : ''; ?>>
                                                    <label for="products-price" class="padding05"><?= lang('product_price') ?>
                                                    </label>
                                                </div>
                                                <?php ////////////////////////// Mrp , Price , Cost////////////////////////////////// ?>
                                                    <div class="col-sm-5">
                                                        <input type="checkbox" value="1" id="products-mrp" class="checkbox"
                                                            name="products-mrp" <?php echo $p->{'products-mrp'} ? "checked" : ''; ?>>
                                                        <label for="products-mrp" class="padding05"><?= lang('product_mrp') ?>
                                                        </label>
                                                    </div>
                                            
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="products-barcode" class="checkbox"
                                                           name="products-barcode" <?php echo $p->{'products-barcode'} ? "checked" : ''; ?>>
                                                    <label for="products-barcode"
                                                           class="padding05"><?= lang('print_barcodes') ?></label>
                                                </div>
                                                <!-- <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="products-stock_count" class="checkbox"
                                                           name="products-stock_count" <?php echo $p->{'products-stock_count'} ? "checked" : ''; ?>>
                                                    <label for="products-stock_count"
                                                           class="padding05"><?= lang('stock_counts') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-adjustments" class="checkbox"
                                                           name="products-adjustments" <?php echo $p->{'products-adjustments'} ? "checked" : ''; ?>>
                                                    <label for="products-adjustments"
                                                           class="padding05"><?= lang('adjustments') ?></label>
                                                </div> -->
                                            <!-- <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="products-ad_type_addition" class="checkbox"
                                                           name="products-ad_type_addition" <?php echo $p->{'Allow_Stock_Addition'} ? "checked" : ''; ?>>
                                                    <label for="products-ad_type_addition"
                                                           class="padding05"><?= lang('Allow_Stock_Addition') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="products-ad_type_subtraction" class="checkbox"
                                                           name="products-ad_type_subtraction" <?php echo $p->{'Allow_Stock_Subtraction'} ? "checked" : ''; ?>>
                                                    <label for="products-ad_type_subtraction"
                                                           class="padding05"><?= lang('Allow_Stock_Subtraction') ?></label>
                                                </div> -->
                                                <!-- <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-adjustments_byCSV" class="checkbox"
                                                           name="products-adjustments_byCSV" <?php echo $p->{'products-adjustments_byCSV'} ? "checked" : ''; ?>>
                                                    <label for="products-adjustments_byCSV"
                                                           class="padding05"><?= lang('Adjustments_By_CSV') ?></label>
                                                </div> -->
                                            <!-- </div> -->
                                            <div class="col-sm-3" style ="display:flex;">
                                                    <input type="checkbox" value="1" id="raw_materials" class="checkbox"
                                                           name="raw_materials" <?php echo $p->{'raw_materials'} ? "checked" : ''; ?>>
                                                    <label for="raw_materials"
                                                           class="padding05"><?= lang('Raw Materials') ?></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><?= lang("products"); ?> Batches</td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-batches" <?php echo $p->{'products-batches'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-add_batch" <?php echo $p->{'products-add_batch'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-edit_batch" <?php echo $p->{'products-edit_batch'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="products-delete_batch" <?php echo $p->{'products-delete_batch'} ? "checked" : ''; ?>>
                                        </td>
                                        <td></td>                                    
                                    </tr>
                                    <tr>
                                        <td><?= lang("Inventory"); ?></td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-stock_count" class="checkbox"
                                                           name="products-stock_count" <?php echo $p->{'products-stock_count'} ? "checked" : ''; ?>>
                                                    <label for="products-stock_count"
                                                           class="padding05"><?= lang('stock_counts') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-adjustments" class="checkbox"
                                                           name="products-adjustments" <?php echo $p->{'products-adjustments'} ? "checked" : ''; ?>>
                                                    <label for="products-adjustments"
                                                           class="padding05"><?= lang('adjustments') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-adjustments_byCSV" class="checkbox"
                                                           name="products-adjustments_byCSV" <?php echo $p->{'products-adjustments_byCSV'} ? "checked" : ''; ?>>
                                                    <label for="products-adjustments_byCSV"
                                                           class="padding05"><?= lang('Adjustments_By_CSV') ?></label>
                                                </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-ad_type_addition" class="checkbox"
                                                           name="products-ad_type_addition" <?php echo $p->{'Allow_Stock_Addition'} ? "checked" : ''; ?>>
                                                    <label for="products-ad_type_addition"
                                                           class="padding05"><?= lang('Allow_Stock_Addition') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="products-ad_type_subtraction" class="checkbox"
                                                           name="products-ad_type_subtraction" <?php echo $p->{'Allow_Stock_Subtraction'} ? "checked" : ''; ?>>
                                                    <label for="products-ad_type_subtraction"
                                                           class="padding05"><?= lang('Allow_Stock_Subtraction') ?></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                        <td><?= lang("sales"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-index" <?php echo $p->{'sales-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-add" <?php echo $p->{'sales-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-edit" <?php echo $p->{'sales-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-delete" <?php echo $p->{'sales-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="sales_date" class="checkbox"
                                                           name="sales_date" <?php echo $p->{'sales-date'} ? "checked" : ''; ?>>
                                                    <label for="sales_date" class="padding05"><?= lang('sales_date') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="sales-payments" class="checkbox"
                                                           name="sales-payments" <?php echo $p->{'sales-payments'} ? "checked" : ''; ?>>
                                                    <label for="sales-payments" class="padding05"><?= lang('payments') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="sales-return_sales" class="checkbox"
                                                           name="sales-return_sales" <?php echo $p->{'sales-return_sales'} ? "checked" : ''; ?>>
                                                    <label for="sales-return_sales"
                                                           class="padding05"><?= lang('return_sales') ?></label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <label><input type="checkbox" value="1" id="sales_delete_suspended" class="checkbox"
                                                                  name="sales_delete_suspended" <?php echo $p->{'sales-delete-suspended'} ? "checked" : ''; ?>>&nbsp; <?= lang('Delete_Suspended_Sales') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="sales-email" class="checkbox"
                                                           name="sales-email" <?php echo $p->{'sales-email'} ? "checked" : ''; ?>>
                                                    <label for="sales-email" class="padding05"><?= lang('email') ?></label>
                                                </div>
                                                <div class="col-sm-2">
                                                    <input type="checkbox" value="1" id="sales-pdf" class="checkbox"
                                                           name="sales-pdf" <?php echo $p->{'sales-pdf'} ? "checked" : ''; ?>>
                                                    <label for="sales-pdf" class="padding05"><?= lang('pdf') ?></label>
                                                </div>
                                                <div class="col-sm-2">
                                                    <?php if (POS) { ?>
                                                        <input type="checkbox" value="1" id="pos-index" class="checkbox"
                                                               name="pos-index" <?php echo $p->{'pos-index'} ? "checked" : ''; ?>>
                                                        <label for="pos-index" class="padding05"><?= lang('pos') ?></label>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" id="sales_add_csv" class="checkbox"
                                                                  name="sales_add_csv" <?php echo $p->{'sales_add_csv'} ? "checked" : ''; ?>>&nbsp; <?= lang('Add CSV') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" id="all_sale_lists" class="checkbox"
                                                                  name="all_sale_lists" <?php echo $p->{'all_sale_lists'} ? "checked" : ''; ?>>&nbsp; <?= lang('All Sale Lists') ?></label>
                                                </div> 
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" id="eshop_sales-sales" class="checkbox"
                                                                  name="eshop_sales-sales" <?php echo $p->{'eshop_sales-sales'} ? "checked" : ''; ?>>&nbsp; <?= lang('Eshop Sale Lists') ?></label>
                                                </div>
                                            </div>	   
                                            <div class="row">                                                
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" id="offline-sales" class="checkbox" name="offline-sales" <?php echo $p->{'offline-sales'} ? "checked" : ''; ?>>&nbsp; <?= lang('Offline Sale Lists') ?></label>
                                                </div>    
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" id="sales-challans" class="checkbox" name="sales-challans" <?php echo $p->{'sales-challans'} ? "checked" : ''; ?>>&nbsp; <?= lang('Sales Challans List') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" id="sales-add_challans" class="checkbox" name="sales-add_challans" <?php echo $p->{'sales-add_challans'} ? "checked" : ''; ?>>&nbsp; <?= lang('Add Challans') ?></label>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                    <label> Invoice Return Days </label>
                                                    <input type="number" value="<?php echo $p->{'sales-return_invoice_days'} ?>" placeholder="Return Invoice Days" id="return_invoice_days" class="form-control" name="sales-return_invoice_days" >
                                                </div>
                                        </td>
                                    </tr>

                                    <tr>
                                    <td><?= lang("Delivery Challan "); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="challan-index" <?php echo $p->{'challan-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="challan-add" <?php echo $p->{'challan-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="challan-edit" <?php echo $p->{'challan-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="challan-delete" <?php echo $p->{'challan-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <div class="row">
                                           
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-pdf" class="checkbox"
                                                        name="challan-pdf" <?php echo $p->{'challan-pdf'} ? "checked" : ''; ?>>
                                                <label for="challan-pdf" class="padding05"><?= lang('pdf') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-email" class="checkbox"
                                                        name="challan-email" <?php echo $p->{'challan-email'} ? "checked" : ''; ?>>
                                                <label for="challan-email" class="padding05"><?= lang('Email') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-return" class="checkbox"
                                                        name="challan-return" <?php echo $p->{'challan-return'} ? "checked" : ''; ?>>
                                                <label for="challan-return" class="padding05"><?= lang('Return Challan') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-add_sale" class="checkbox"
                                                        name="challan-add_sale" <?php echo $p->{'challan-add_sale'} ? "checked" : ''; ?>>
                                                <label for="challan-add_sale" class="padding05"><?= lang('Create Sales') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-payments" class="checkbox"
                                                        name="challan-payments" <?php echo $p->{'challan-payments'} ? "checked" : ''; ?>>
                                                <label for="challan-payments" class="padding05"><?= lang('view_payments') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-add_payment" class="checkbox"
                                                        name="challan-add_payment" <?php echo $p->{'challan-add_payment'} ? "checked" : ''; ?>>
                                                <label for="challan-add_payment" class="padding05"><?= lang('add_payment') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="challan-edit_payment" class="checkbox"
                                                        name="challan-edit_payment" <?php echo $p->{'challan-edit_payment'} ? "checked" : ''; ?>>
                                                <label for="challan-edit_payment" class="padding05"><?= lang('Edit Payment') ?></label>
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="checkbox" value="1" id="reports-challan" class="checkbox"
                                                        name="reports-challan" <?php echo $p->{'reports-challan'} ? "checked" : ''; ?>>
                                                <label for="reports-challan" class="padding05"><?= lang('Challan Reports') ?></label>
                                            </div>
                                             <div class="col-sm-6">
                                                <input type="checkbox" value="1" id="challancompleted_status" class="checkbox"
                                                        name="challancompleted_status" <?php echo $p->{'challancompleted_status'} ? "checked" : ''; ?>>
                                                <label for="challancompleted_status" class="padding05"><?= lang('Complete Status (Show / Hide)') ?></label>
                                            </div>
                                            
                                            
                                        </div>    
                                    </td>
                                </tr>
                                    <tr>
                                        <td><?= lang("Orders"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="order-index" <?php echo $p->{'order-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="order-add" <?php echo $p->{'order-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="order-edit" <?php echo $p->{'order-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="order-delete" <?php echo $p->{'order-delete'} ? "checked" : ''; ?>>
                                        </td> 

                                        <td colspan="5">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="orders-eshop_order" class="checkbox"
                                                           accept="" name="orders-eshop_order" <?php echo $p->{'orders-eshop_order'} ? "checked" : ''; ?>>
                                                    <label for="orders-eshop_order" class="padding05"><?= lang('Eshop_Order') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="orders-order_items" class="checkbox"
                                                           accept="" name="orders-order_items" <?php echo $p->{'orders-order_items'} ? "checked" : ''; ?>>
                                                    <label for="orders-order_items" class="padding05"><?= lang('Order Items') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="orders-order_items_stocks" class="checkbox"
                                                           accept="" name="orders-order_items_stocks" <?php echo $p->{'orders-order_items_stocks'} ? "checked" : ''; ?>>
                                                    <label for="orders-order_items_stocks" class="padding05"><?= lang('Order Items Stocks') ?></label>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                    <?php if ($Settings->pos_type == 'restaurant') { ?>
                                        <tr>
                                            <td>   Urbanpiper </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="urbanpiper_view" <?php echo $p->{'urbanpiper_view'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="urbanpiper_add" <?php echo $p->{'urbanpiper_add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="urbanpiper_edit" <?php echo $p->{'urbanpiper_edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="urbanpiper_delete" <?php echo $p->{'urbanpiper_delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <div class="row">
                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="urbanpiper_sales" class="checkbox"
                                                                      name="urbanpiper_sales" <?php echo $p->{'urbanpiper_sales'} ? "checked" : ''; ?>>&nbsp; <?= lang('Urbanpiper Sales') ?></label>
                                                    </div>

                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="urbanpiper_manage_order" class="checkbox"
                                                                      name="urbanpiper_maange_order" <?php echo $p->{'urbanpiper_maange_order'} ? "checked" : ''; ?>>&nbsp; <?= lang('Urbanpiper Manage Order') ?></label>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="urbanpiper_settings" class="checkbox"
                                                                      name="urbanpiper_settings" <?php echo $p->{'urbanpiper_settings'} ? "checked" : ''; ?>>&nbsp; <?= lang('Urbanpiper Settings') ?></label>
                                                    </div>

                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="urbanpiper_maange_stores" class="checkbox"
                                                                      name="urbanpiper_maange_stores" <?php echo $p->{'urbanpiper_maange_stores'} ? "checked" : ''; ?>>&nbsp; <?= lang('Manage Stores') ?></label>
                                                    </div>
                                                </div>
                                                <div class="row">


                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="urbanpiper_maange_catalogue" class="checkbox"
                                                                      name="urbanpiper_maange_catalogue" <?php echo $p->{'urbanpiper_maange_catalogue'} ? "checked" : ''; ?>>&nbsp; <?= lang('Manage Catalogue') ?></label>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="orders_3p" class="checkbox"
                                                                name="orders_3p"
                                                                <?php echo $p->{'urbanpiper_orders_3p'} ? "checked" : ''; ?>>&nbsp;
                                                            <?= lang('Manage Orders') ?></label>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="redirecting_login_orders_3p" class="checkbox"
                                                                name="redirecting_login_orders_3p"
                                                                <?php echo $p->{'redirecting_login_orders_3p'} ? "checked" : ''; ?>>&nbsp;
                                                            <?= lang('Redirection Login Manage Orders') ?></label>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <label><input type="checkbox" value="1" id="up_orders_3p" class="checkbox"
                                                                name="up_orders_3p"
                                                                <?php echo $p->{'up_orders_3p'} ? "checked" : ''; ?>>&nbsp;
                                                            <?= lang('Up Orders') ?></label>
                                                    </div>
                                                    
                                                </div>

                                            </td>

                                        </tr>


                                    <?php } ?>
                                    <tr>
                                        <td><?= lang("deliveries"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-deliveries" <?php echo $p->{'sales-deliveries'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-add_delivery" <?php echo $p->{'sales-add_delivery'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-edit_delivery" <?php echo $p->{'sales-edit_delivery'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-delete_delivery" <?php echo $p->{'sales-delete_delivery'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <input type="checkbox" value="1" id="sales-pdf" class="checkbox"
                                                           accept=""name="sales-pdf_delivery" <?php echo $p->{'sales-pdf_delivery'} ? "checked" : ''; ?>>
                                                    <label for="sales-pdf_delivery" class="padding05"><?= lang('pdf') ?></label>
                                                </div>
                                                <div class="col-sm-6">
                                            <!--<input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email_delivery" <?php echo $p->{'sales-email_delivery'} ? "checked" : ''; ?>><label for="sales-email_delivery" class="padding05"><?= lang('email') ?></label>-->
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("gift_cards"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-gift_cards" <?php echo $p->{'sales-gift_cards'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-add_gift_card" <?php echo $p->{'sales-add_gift_card'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-edit_gift_card" <?php echo $p->{'sales-edit_gift_card'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="sales-delete_gift_card" <?php echo $p->{'sales-delete_gift_card'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td><?= lang("quotes"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="quotes-index" <?php echo $p->{'quotes-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="quotes-add" <?php echo $p->{'quotes-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="quotes-edit" <?php echo $p->{'quotes-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="quotes-delete" <?php echo $p->{'quotes-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id=quotes_date" class="checkbox"
                                                           name="quotes_date" <?php echo $p->{'quotes-date'} ? "checked" : ''; ?>>
                                                    <label for="quotes_date" class="padding05"><?= lang('quotes-date') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="quotes-email" class="checkbox"
                                                           name="quotes-email" <?php echo $p->{'quotes-email'} ? "checked" : ''; ?>>
                                                    <label for="quotes-email" class="padding05"><?= lang('email') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="quotes-pdf" class="checkbox"
                                                           name="quotes-pdf" <?php echo $p->{'quotes-pdf'} ? "checked" : ''; ?>>
                                                    <label for="quotes-pdf" class="padding05"><?= lang('pdf') ?></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><?= lang("purchases"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="purchases-index" <?php echo $p->{'purchases-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="purchases-add" <?php echo $p->{'purchases-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="purchases-edit" <?php echo $p->{'purchases-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="purchases-delete" <?php echo $p->{'purchases-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <label ><input type="checkbox" value="1" id="purchases_date" class="checkbox"
                                                                   name="purchases_date" <?php echo $p->{'purchases-date'} ? "checked" : ''; ?>>&nbsp; <?= lang('purchases_date') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="purchases-payments" class="checkbox"
                                                           name="purchases-payments" <?php echo $p->{'purchases-payments'} ? "checked" : ''; ?>>
                                                    <label for="purchases-payments"
                                                           class="padding05"><?= lang('payments') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="purchases-expenses" class="checkbox"
                                                           name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? "checked" : ''; ?>>
                                                    <label for="purchases-expenses"
                                                           class="padding05"><?= lang('expenses') ?></label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <label ><input type="checkbox" value="1" id="purchases-return_purchases"
                                                                   class="checkbox"
                                                                   name="purchases-return_purchases" <?php echo $p->{'purchases-return_purchases'} ? "checked" : ''; ?>>&nbsp; <?= lang('return_purchases') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" id="purchases-email" class="checkbox"
                                                           name="purchases-email" <?php echo $p->{'purchases-email'} ? "checked" : ''; ?>>
                                                    <label for="purchases-email" class="padding05"><?= lang('email') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="purchases-pdf" class="checkbox"
                                                           name="purchases-pdf" <?php echo $p->{'purchases-pdf'} ? "checked" : ''; ?>>
                                                    <label for="purchases-pdf" class="padding05"><?= lang('pdf') ?></label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="purchase_add_csv" class="checkbox"
                                                           name="purchase_add_csv" <?php echo $p->{'purchase_add_csv'} ? "checked" : ''; ?>>
                                                    <label for="purchase_add_csv" class="padding05"><?= lang('Add CSV') ?></label>
                                                </div>

                                                 <?php if($Settings->synced_data_sales){ ?>
                                                    <div class="col-sm-5">
                                                        <input type="checkbox" value="1" id="purchases-notification" class="checkbox"
                                                               name="purchases-notification" <?php echo $p->{'purchases-notification'} ? "checked" : ''; ?>>
                                                        <label for="purchases-notification" class="padding05"><?= lang('Purchase Notification') ?></label>
                                                    </div>
                                                <?php } ?>
                                            </div>    
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("transfers"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="transfers-index" <?php echo $p->{'transfers-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="transfers-add" <?php echo $p->{'transfers-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="transfers-edit" <?php echo $p->{'transfers-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="transfers-delete" <?php echo $p->{'transfers-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-4" style="padding-right:0px">
                                                    <input type="checkbox" value="1" id=transfers-date" class="checkbox"
                                                           name="transfers-date" <?php echo $p->{'transfers-date'} ? "checked" : ''; ?>>
                                                    <label for="transfers-date"
                                                           class="padding05"><?= lang('transfers_date') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="transfers-email" class="checkbox"
                                                           name="transfers-email" <?php echo $p->{'transfers-email'} ? "checked" : ''; ?>>
                                                    <label for="transfers-email" class="padding05"><?= lang('email') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="transfers-pdf" class="checkbox"
                                                           name="transfers-pdf" <?php echo $p->{'transfers-pdf'} ? "checked" : ''; ?>>
                                                    <label for="transfers-pdf" class="padding05"><?= lang('pdf') ?></label>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4" style="padding-right:0px">
                                                    <input type="checkbox" value="1" id="transfers_add_csv" class="checkbox"
                                                           name="transfers_add_csv" <?php echo $p->{'transfers_add_csv'} ? "checked" : ''; ?>>
                                                    <label for="transfers_add_csv" class="padding05"><?= lang('Add CSV') ?></label>
                                                </div>
                                                <div class="col-sm-4" >
                                                    <input type="checkbox" value="1" id="transfer_status_completed" class="checkbox"
                                                           name="transfer_status_completed" <?php echo $p->{'transfer_status_completed'} ? "checked" : ''; ?>>
                                                    <label for="transfer_status_completed" class="padding05"><?= lang('Status Completed') ?></label>
                                                </div>
                                                <div class="col-sm-4" >
                                                    <input type="checkbox" value="1" id="product_remove" class="checkbox"
                                                           name="product_remove" <?php echo $p->{'product_remove'} ? "checked" : ''; ?>>
                                                    <label for="product_remove" class="padding05"><?= lang('Show/Hide Product Remove') ?></label>
                                                </div>

                                            </div> 
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="transfer_status_request" class="checkbox"
                                                           name="transfer_status_request" <?php echo $p->{'transfer_status_request'} ? "checked" : ''; ?>>
                                                    <label for="transfer_status_request" class="padding05"><?= lang('Status Request') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="transfer_status_sent" class="checkbox"
                                                           name="transfer_status_sent" <?php echo $p->{'transfer_status_sent'} ? "checked" : ''; ?>>
                                                    <label for="transfer_status_sent" class="padding05"><?= lang('Status Sent') ?></label>
                                                </div>
                                            </div>

                                        </td>
                                    </tr> 
        <!--                                    <tr>
                            <td><?= lang("Transfers"); ?></td>
                            <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox"
                                       name="transfersnew-index" <?php echo $p->{'transfersnew-index'} ? "checked" : ''; ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox"
                                       name="transfersnew-add" <?php echo $p->{'transfersnew-add'} ? "checked" : ''; ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox"
                                       name="transfersnew-edit" <?php echo $p->{'transfersnew-edit'} ? "checked" : ''; ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox"
                                       name="transfersnew-delete" <?php echo $p->{'transfersnew-delete'} ? "checked" : ''; ?>>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-sm-4" style="padding-right:0px">
                                        <input type="checkbox" value="1" id="transfersnew-date" class="checkbox"
                                               name="transfersnew-date" <?php echo $p->{'transfersnew-date'} ? "checked" : ''; ?>>
                                        <label for="transfersnew_date"
                                               class="padding05"><?= lang('transfers_date') ?></label>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="checkbox" value="1" id="transfersnew-email" class="checkbox"
                                               name="transfersnew-email" <?php echo $p->{'transfersnew-email'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-email" class="padding05"><?= lang('email') ?></label>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="checkbox" value="1" id="transfersnew-pdf" class="checkbox"
                                               name="transfersnew-pdf" <?php echo $p->{'transfersnew-pdf'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-pdf" class="padding05"><?= lang('pdf') ?></label>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-sm-4" style="padding-right:0px">
                                        <input type="checkbox" value="1" id="transfersnew-add_csv" class="checkbox"
                                               name="transfersnew-add_csv" <?php echo $p->{'transfersnew-add_csv'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-add_csv" class="padding05"><?= lang('Add CSV') ?></label>
                                    </div>

                                    <div class="col-sm-4" >
                                        <input type="checkbox" value="1" id="transfersnew-add_request" class="checkbox"
                                               name="transfersnew-add_request" <?php echo $p->{'transfersnew-add_request'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-add_request" class="padding05"><?= lang('Add Request') ?></label>
                                    </div>

                                    <div class="col-sm-4" >
                                        <input type="checkbox" value="1" id="transfersnew-status_completed" class="checkbox"
                                               name="transfersnew-status_completed" <?php echo $p->{'transfersnew-status_completed'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-status_completed" class="padding05"><?= lang('Status Completed') ?></label>
                                    </div>

                                </div> 
                                <div class="row">
                                    <div class="col-sm-4">
                                        <input type="checkbox" value="1" id="transfersnew-status_request" class="checkbox"
                                               name="transfersnew-status_request" <?php echo $p->{'transfersnew-status_request'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-status_request" class="padding05"><?= lang('Status Request') ?></label>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="checkbox" value="1" id="transfersnew-status_sent" class="checkbox"
                                               name="transfersnew-status_sent" <?php echo $p->{'transfersnew-status_sent'} ? "checked" : ''; ?>>
                                        <label for="transfersnew-status_sent" class="padding05"><?= lang('Status Sent') ?></label>
                                    </div>
                                </div>

                            </td>
                        </tr>-->
                                    <tr>
                                        <td><?= lang("Transfer Request"); ?></td>
                                        <td class="text-center" title="View List">
                                            <input type="checkbox" value="1" class="checkbox" name="transfers-request" <?php echo $p->{'transfers-request'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center" title="Add">
                                            <input type="checkbox" value="1" class="checkbox" name="transfers-add_request" <?php echo $p->{'transfers-add_request'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center" title="Edit">
                                            <input type="checkbox" value="1" class="checkbox" name="transfers-edit_request" <?php echo $p->{'transfers-edit_request'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center" title="Delete">
                                            <input type="checkbox" value="1" class="checkbox" name="transfers-delete_request" <?php echo $p->{'transfers-delete_request'} ? "checked" : ''; ?>>
                                        </td>
                                         <td>
                                             <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" id="transfers-cancel_request" class="checkbox" name="transfers-cancel_request" <?php echo $p->{'transfers-cancel_request'} ? "checked" : ''; ?>>
                                                    <label for="transfers-cancel_request" class="padding05"><?= lang('Cancel_Request') ?></label>
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="transfers-request_change_status" class="checkbox" name="transfers-request_change_status" <?php echo $p->{'transfers-request_change_status'} ? "checked" : ''; ?>>
                                                    <label for="transfers-request_change_status" class="padding05"><?= lang('Change Request Status') ?></label>
                                                </div> 
                                                 
                                             </div>
                                         </td>                                         
                                    </tr>
                                    <tr>
                                        <td><?= lang("customers"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="customers-index" <?php echo $p->{'customers-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="customers-add" <?php echo $p->{'customers-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="customers-edit" <?php echo $p->{'customers-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="customers-delete" <?php echo $p->{'customers-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="customers-deposits" class="checkbox"
                                                           name="customers-deposits" <?php echo $p->{'customers-deposits'} ? "checked" : ''; ?>>
                                                    <label for="customers-deposits"
                                                           class="padding05"><?= lang('Deposits (view, add & edit)') ?></label>
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="checkbox" value="1" id="customers-delete_deposit" class="checkbox"
                                                           name="customers-delete_deposit" <?php echo $p->{'customers-delete_deposit'} ? "checked" : ''; ?>>
                                                    <label for="customers-delete_deposit"
                                                           class="padding05"><?= lang('delete_deposit') ?></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Attendance</td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendance-index" id="attendance-index" <?php echo !empty($p->{'attendance-index'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendance-create" id="attendance-create" <?php echo !empty($p->{'attendance-create'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendance-edit" id="attendance-edit" <?php echo !empty($p->{'attendance-edit'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendance-delete" id="attendance-delete" <?php echo !empty($p->{'attendance-delete'}) ? "checked" : ''; ?>>
                                        </td>
                                       
                                    </tr>
                                    <tr>
                                        <td><?= lang("suppliers"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="suppliers-index" <?php echo $p->{'suppliers-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="suppliers-add" <?php echo $p->{'suppliers-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="suppliers-edit" <?php echo $p->{'suppliers-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="suppliers-delete" <?php echo $p->{'suppliers-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("users"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="users-index" <?php echo !empty($p->{'users-index'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="users-add" <?php echo !empty($p->{'users-add'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="users-edit" <?php echo !empty($p->{'users-edit'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="users-delete" <?php echo !empty($p->{'users-delete'}) ? "checked" : ''; ?>>
                                        </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td><?= lang("reports"); ?></td>
                                        <td colspan="5">

                                            <div class="row">
                                                
                                                <!-- Accounts Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="accounts" id="main-accounts" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="accounts" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Accounts') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="reports-cash-transaction-report"
                                                           name="cash-transaction-report" <?php echo $p->{'reports-cash-transaction-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-cash-transaction-report" class="padding05"><?= lang('Cash_Transaction_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="reports-hsncode_reports"
                                                           name="reports-hsncode_reports" <?php echo $p->{'reports-hsncode_reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-hsncode_reports" class="padding05"><?= lang('HSNCode_Reports') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="reports-payment_chart_details"
                                                           name="reports-payment_chart_details" <?php echo $p->{'reports-payment_chart_details'} ? "checked" : ''; ?>>
                                                    <label for="reports-payment_chart_details" class="padding05"><?= lang('Payment_Chart_Details') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="reports-payments-summary"
                                                           name="payments-summary" <?php echo $p->{'reports-payments-summary'} ? "checked" : ''; ?>>
                                                    <label for="reports-payments-summary" class="padding05"><?= lang('Payments_Summary') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label><input type="checkbox" value="1" class="checkbox accounts-sub" id="payments"
                                                                  name="reports-payments" <?php echo $p->{'reports-payments'} ? "checked" : ''; ?>>&nbsp; <?= lang('payments') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="reports-profit-loss-report"
                                                           name="profit-loss-report" <?php echo $p->{'reports-profit-loss-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-profit-loss-report" class="padding05"><?= lang('Profit_And_loss_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="gstreports"
                                                           name="reports-gst_reports" <?php echo $p->{'reports-gst_reports'} ? "checked" : ''; ?>>
                                                    <label for="gstreports" class="padding05"><?= lang('Simple GST Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="purchase_gst_report"
                                                           name="purchase_gst_report" <?php echo $p->{'purchase_gst_report'} ? "checked" : ''; ?>>
                                                    <label for="purchase_gst_report" class="padding05"><?= lang('Simple Purchase GST Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="sales_gst_report"
                                                           name="sales_gst_report" <?php echo $p->{'sales_gst_report'} ? "checked" : ''; ?>>
                                                    <label for="sales_gst_report" class="padding05"><?= lang('Simple Sales GST Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox accounts-sub" id="reports-tax-report"
                                                           name="tax-report" <?php echo $p->{'reports-tax-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-tax-report" class="padding05"><?= lang('Tax_Report') ?></label>
                                                </div>

                                                <!-- Challan Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="challan" id="main-challan" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="challan" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Challan') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox challan-sub" id="reports-challan-reports"
                                                           name="challan-reports" <?php echo $p->{'reports-challan-reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-challan-reports" class="padding05"><?= lang('Challan_Reports') ?></label>
                                                </div>

                                                <!-- Customer Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="customer" id="main-customer" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="customer" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Customer') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox customer-sub" id="customers"
                                                           name="reports-customers" <?php echo $p->{'reports-customers'} ? "checked" : ''; ?>>
                                                    <label for="customers" class="padding05"><?= lang('customers') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox customer-sub" id="reports-deposit"
                                                           name="reports-deposit" <?php echo $p->{'reports-deposit'} ? "checked" : ''; ?>>
                                                    <label for="reports-deposit" class="padding05"><?= lang('Deposit_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox customer-sub" id="reports-register-report"
                                                           name="register-report" <?php echo $p->{'reports-register-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-register-report" class="padding05"><?= lang('Register_Report') ?></label>
                                                </div>

                                                <!-- Ledger Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="ledger" id="main-ledger" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="ledger" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Ledger') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox ledger-sub" id="reports-customer-ledgers"
                                                           name="customer-ledgers" <?php echo $p->{'reports-customer-ledgers'} ? "checked" : ''; ?>>
                                                    <label for="reports-customer-ledgers" class="padding05"><?= lang('Customer_Ledgers') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox ledger-sub" id="reports-deposit-ledgers"
                                                           name="deposit-ledgers" <?php echo $p->{'reports-deposit-ledgers'} ? "checked" : ''; ?>>
                                                    <label for="reports-deposit-ledgers" class="padding05"><?= lang('Deposit_Ledgers') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox ledger-sub" id="reports-deposit-recharge-ledgers"
                                                           name="deposit-recharge-ledgers" <?php echo $p->{'reports-deposit-recharge-ledgers'} ? "checked" : ''; ?>>
                                                    <label for="reports-deposit-recharge-ledgers" class="padding05"><?= lang('Deposit_Recharge_Ledgers') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox ledger-sub" id="reports-products_ledgers"
                                                           name="reports-products_ledgers" <?php echo $p->{'reports-products_ledgers'} ? "checked" : ''; ?>>
                                                    <label for="reports-products_ledgers" class="padding05"><?= lang('Products_Ledgers') ?></label>
                                                </div>

                                                <!-- Logs Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="logs" id="main-logs" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="logs" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Logs') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox logs-sub" id="attendance-report"
                                                           name="attendance-report" <?php echo $p->{'attendance-report'} ? "checked" : ''; ?>>
                                                    <label for="attendance-report" class="padding05"><?= lang('Attendance_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox logs-sub" id="reports-user-log-report"
                                                           name="user-log-report" <?php echo $p->{'reports-user-log-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-user-log-report" class="padding05"><?= lang('User_Log_Report') ?></label>
                                                </div>

                                                <!-- Products Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="products" id="main-products" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="products" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Products') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-adjustments-reports"
                                                           name="adjustments-reports" <?php echo $p->{'reports-adjustments-reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-adjustments-reports" class="padding05"><?= lang('Adjustments_Reports') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-brand-reports"
                                                           name="brand-reports" <?php echo $p->{'reports-brand-reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-brand-reports" class="padding05"><?= lang('Brand_Reports') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-categories_brand_chart_details"
                                                           name="reports-categories_brand_chart_details" <?php echo $p->{'reports-categories_brand_chart_details'} ? "checked" : ''; ?>>
                                                    <label for="reports-categories_brand_chart_details" class="padding05"><?= lang('Categories_Brand_Chart_Details') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-categories-reports"
                                                           name="categories-reports" <?php echo $p->{'reports-categories-reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-categories-reports" class="padding05"><?= lang('Categories_Reports') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="Product_expiry_alerts"
                                                           name="reports-expiry_alerts" <?php echo $p->{'reports-expiry_alerts'} ? "checked" : ''; ?>>
                                                    <label for="Product_expiry_alerts" class="padding05"><?= lang('product_expiry_alerts') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="product_quantity_alerts"
                                                           name="reports-quantity_alerts" <?php echo $p->{'reports-quantity_alerts'} ? "checked" : ''; ?>>
                                                    <label for="product_quantity_alerts" class="padding05"><?= lang('Product_Qty_Alerts') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-product-varient-stock-report"
                                                           name="product-varient-stock-report" <?php echo $p->{'reports-product-varient-stock-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-product-varient-stock-report" class="padding05"><?= lang('Product_Varient_Stock_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-product-combo-item"
                                                           name="product-combo-item" <?php echo $p->{'reports-product-combo-item'} ? "checked" : ''; ?>>
                                                    <label for="reports-product-combo-item" class="padding05"><?= lang('Product_Combo_Item') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-product-costing"
                                                           name="product-costing" <?php echo $p->{'reports-product-costing'} ? "checked" : ''; ?>>
                                                    <label for="reports-product-costing" class="padding05"><?= lang('Product_Costing') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-product-profit-loss-report"
                                                           name="product-profit-loss-report" <?php echo $p->{'reports-product-profit-loss-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-product-profit-loss-report" class="padding05"><?= lang('Product_Profit_Loss_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="products"
                                                           name="reports-products" <?php echo $p->{'reports-products'} ? "checked" : ''; ?>><label
                                                           for="products" class="padding05"><?= lang('products') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-products_transactions"
                                                           name="reports-products_transactions" <?php echo $p->{'reports-products_transactions'} ? "checked" : ''; ?>>
                                                    <label for="reports-products_transactions" class="padding05"><?= lang('Products_Transactions') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox products-sub" id="reports-warehouse-stock-report"
                                                           name="warehouse-stock-report" <?php echo $p->{'reports-warehouse-stock-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-warehouse-stock-report" class="padding05"><?= lang('Location_Stock_Report') ?></label>
                                                </div>

                                                <!-- Purchase Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="purchase" id="main-purchase" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="purchase" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Purchase') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="daily_purchases"
                                                           name="reports-daily_purchases" <?php echo $p->{'reports-daily_purchases'} ? "checked" : ''; ?>>
                                                    <label for="daily_purchases" class="padding05"><?= lang('daily_purchases') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="reports-due-purchase-reports"
                                                           name="due-purchase-reports" <?php echo $p->{'reports-due-purchase-reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-due-purchase-reports" class="padding05"><?= lang('Due-Purchase_Reports') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="expenses"
                                                           name="reports-expenses" <?php echo $p->{'reports-expenses'} ? "checked" : ''; ?>>
                                                    <label for="expenses" class="padding05"><?= lang('expenses') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="monthly_purchases"
                                                           name="reports-monthly_purchases" <?php echo $p->{'reports-monthly_purchases'} ? "checked" : ''; ?>>
                                                    <label for="monthly_purchases" class="padding05"><?= lang('monthly_purchases') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="reports-product-varient-purchase-report"
                                                           name="product-varient-purchase-report" <?php echo $p->{'reports-product-varient-purchase-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-product-varient-purchase-report" class="padding05"><?= lang('Product_Varient_Purchase_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="purchases"
                                                           name="reports-purchases" <?php echo $p->{'reports-purchases'} ? "checked" : ''; ?>>
                                                    <label for="purchases" class="padding05"><?= lang('purchases') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="purchasegstreport"
                                                           name="report_purchase_gst" <?php echo $p->{'report_purchase_gst'} ? "checked" : ''; ?>>
                                                    <label for="purchasegstreport" class="padding05"><?= lang('Purchases GST Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox purchase-sub" id="suppliers"
                                                           name="reports-suppliers" <?php echo $p->{'reports-suppliers'} ? "checked" : ''; ?>>
                                                    <label for="suppliers" class="padding05"><?= lang('suppliers') ?></label>
                                                </div>

                                                <!-- Sales Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="sales" id="main-sales" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="sales" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Sales') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-best-seller"
                                                           name="best-seller" <?php echo $p->{'reports-best-seller'} ? "checked" : ''; ?>>
                                                    <label for="reports-best-seller" class="padding05"><?= lang('Best_Seller') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-get_customer_wise_sales"
                                                           name="reports-get_customer_wise_sales" <?php echo $p->{'reports-get_customer_wise_sales'} ? "checked" : ''; ?>>
                                                    <label for="reports-get_customer_wise_sales" class="padding05"><?= lang('Customer_Wise_Sales') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="daily_sales"
                                                           name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? "checked" : ''; ?>>
                                                    <label for="daily_sales" class="padding05"><?= lang('daily_sales') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-due-sales-reports"
                                                           name="due-sales-reports" <?php echo $p->{'reports-due-sales-reports'} ? "checked" : ''; ?>>
                                                    <label for="reports-due-sales-reports" class="padding05"><?= lang('Due_Sales_Reports') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="monthly_sales"
                                                           name="reports-monthly_sales" <?php echo $p->{'reports-monthly_sales'} ? "checked" : ''; ?>>
                                                    <label for="monthly_sales" class="padding05"><?= lang('monthly_sales') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-product-wise-sale-report"
                                                           name="product-wise-sale-report" <?php echo $p->{'reports-product-wise-sale-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-product-wise-sale-report" class="padding05"><?= lang('Product_Wise_Sale_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-sale_purchase_chart_details"
                                                           name="reports-sale_purchase_chart_details" <?php echo $p->{'reports-sale_purchase_chart_details'} ? "checked" : ''; ?>>
                                                    <label for="reports-sale_purchase_chart_details" class="padding05"><?= lang('Sale_Purchase_Chart_Details') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="sales"
                                                           name="reports-sales" <?php echo $p->{'reports-sales'} ? "checked" : ''; ?>>
                                                    <label for="sales" class="padding05"><?= lang('sales') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-sales-extended-report"
                                                           name="sales-extended-report" <?php echo $p->{'reports-sales-extended-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-sales-extended-report" class="padding05"><?= lang('Sales_Extended_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-sales-person-report"
                                                           name="reports-sales-person-report" <?php echo $p->{'reports-sales-person-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-sales-person-report" class="padding05"><?= lang('Sales_Person_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-product_variant_sale_report"
                                                        name="reports-product_variant_sale_report"<?php echo $p->{'reports-product_variant_sale_report'} ? "checked" : ''; ?>>
                                                    <label for="reports-product_variant_sale_report" class="padding05"><?= lang('Product_Variant_sale_report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="sales_uae"
                                                        name="sales_transaction_report_UAE"
                                                        <?php echo $p->{'sales_transaction_report_UAE'} ? "checked" : ''; ?>>
                                                    <label for="sales_uae" class="padding05"><?= lang('Sales Transaction Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-staff-report"
                                                           name="reports-staff-report" <?php echo $p->{'reports-staff-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-staff-report" class="padding05"><?= lang('Staff_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="reports-term-wise-sale-report"
                                                           name="term-wise-sale-report" <?php echo $p->{'reports-term-wise-sale-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-term-wise-sale-report" class="padding05"><?= lang('Term_Wise_Sale_Report') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox sales-sub" id="warehousesalereport"
                                                           name="reports-warehouse_sales_report" <?php echo $p->{'reports-warehouse_sales_report'} ? "checked" : ''; ?>>
                                                    <label for="warehousesalereport" class="padding05"><?= lang('Warehouse Sales Report') ?></label>
                                                </div>

                                                <!-- Transfers Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="transfers" id="main-transfers" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="transfers" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Transfers') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox transfers-sub" id="reports-transfer_request"
                                                           name="reports-transfer_request" <?php echo $p->{'reports-transfer_request'} ? "checked" : ''; ?>>
                                                    <label for="reports-transfer_request" class="padding05"><?= lang('Transfer_Request') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox transfers-sub" id="reports-transfer-report"
                                                           name="transfer-report" <?php echo $p->{'reports-transfer-report'} ? "checked" : ''; ?>>
                                                    <label for="reports-transfer-report" class="padding05"><?= lang('Transfer_Report') ?></label>
                                                </div>

                                                <!-- Dashboard / BI Reports Section -->
                                                <div class="col-xs-12" style="border-top: 1px solid #bbb; margin: 15px 0 10px 0; padding-top: 5px; font-weight: bold; font-size: 14px; clear: both; display: flex; align-items: center; gap: 8px;">
                                                    <span class="blue" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <input type="checkbox" class="main-module-checkbox" data-section="dashboard" id="main-dashboard" style="margin: 0; vertical-align: middle;">
                                                        <span class="main-module-toggle" data-section="dashboard" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; user-select: none;">
                                                            <i class="fa fa-folder main-folder-icon"></i> <?= lang('Dashboard') ?>
                                                            <i class="fa fa-angle-down toggle-caret" style="margin-left: 3px;"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox dashboard-sub" id="reports-overview-chart"
                                                           name="overview-chart" <?php echo $p->{'reports-overview-chart'} ? "checked" : ''; ?>>
                                                    <label for="reports-overview-chart" class="padding05"><?= lang('Overview_Chart') ?></label>
                                                </div>
                                            </div>
                                        
                                        
                                            
                                            
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><?= lang("misc"); ?></td>
                                        <td colspan="5">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                                           name="bulk_actions" <?php echo $p->bulk_actions ? "checked" : ''; ?>>
                                                    <label for="bulk_actions"
                                                           class="padding05"><?= lang('bulk_actions') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                                           name="edit_price" <?php echo $p->edit_price ? "checked" : ''; ?>>
                                                    <label for="edit_price"
                                                           class="padding05"><?= lang('edit_price_on_sale') ?></label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" class="checkbox" id="printer_setting"
                                                           name="printer_setting" <?php echo $p->{'printer-setting'} ? "checked" : ''; ?>>
                                                    <label for="printer_setting"
                                                           class="padding05"><?= lang('Printer Settings') ?>  </label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" class="checkbox" id="cart_price_edit"
                                                           name="cart-price_edit" <?php echo $p->{'cart-price_edit'} ? "checked" : ''; ?>>
                                                    <label for="cart_price_edit"
                                                           class="padding05"><?= lang('cart_price_edit') ?>  </label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input type="checkbox" value="1" class="checkbox" id="cart_unit_view"
                                                           name="cart-unit_view" <?php echo $p->{'cart-unit_view'} ? "checked" : ''; ?>>
                                                    <label for="cart_unit_view"
                                                           class="padding05"><?= lang('cart_unit_view') ?>  </label>
                                                </div>
                                                <div class="col-sm-3">
                                                    <label><input type="checkbox" value="1" class="checkbox" id="cart_show_bill_btn"
                                                                  name="cart-show_bill_btn" <?php echo $p->{'cart-show_bill_btn'} ? "checked" : ''; ?> >&nbsp; <?= lang('cart_show_bill_btn') ?>  </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("pos"); ?></td>
                                        <td colspan="5">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="pos_show_order_btn"
                                                           name="pos_show_order_btn" <?php echo $p->{'pos-show-order-btn'} ? "checked" : ''; ?>>
                                                    <label for="pos_show_order_btn" class="padding05"><?= lang('Show Order Button') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="pos_clear_table"
                                                           name="pos_clear_table" <?php echo $p->{'pos_clear_table'} ? "checked" : ''; ?>>
                                                    <label for="pos_clear_table" class="padding05"><?= lang('Clear Table') ?></label>
                                                </div>

                                               <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                           name="checkout" <?php echo $p->{'checkout'} ? "checked" : ''; ?>>
                                                    <label for="checkout" class="padding05"><?= lang('Checkout') ?></label>
                                                </div>

                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                           name="bill_print" <?php echo $p->{'bill_print'} ? "checked" : ''; ?>>
                                                    <label for="bill_print" class="padding05"><?= lang('Bill_Print') ?></label>
                                                </div>


                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                           name="pos-apply_coupon" <?php echo $p->{'pos-apply_coupon'} ? "checked" : ''; ?>>
                                                    <label for="pos-apply_coupon" class="padding05"><?= lang('Apply_Coupon') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                           name="pos-discription" <?php echo $p->{'pos-discription'} ? "checked" : ''; ?>>
                                                    <label for="pos-discription" class="padding05"><?= lang('Discription') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                           name="pos-category_search" <?php echo $p->{'pos-category_search'} ? "checked" : ''; ?>>
                                                    <label for="pos-category_search" class="padding05"><?= lang('Category_Search') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="pos-select_season"
                                                           name="pos-select_season" <?php echo $p->{'pos-select_season'} ? "checked" : ''; ?>>
                                                    <label for="pos-select_season" class="padding05"><?= lang('Select_Season') ?></label>
                                                </div>
                                                 <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                           name="pos-customer_scan" <?php echo $p->{'pos-customer_scan'} ? "checked" : ''; ?>>
                                                    <label for="pos-customer_scan" class="padding05"><?= lang('Customer_Scan') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                        <input type="checkbox" value="1" class="checkbox" id="checkout"
                                                            name="pos-show_season" <?php echo $p->{'pos-show_season'} ? "checked" : ''; ?>>
                                                        <label for="pos-show_season" class="padding05"><?= lang('Show_Season') ?></label>
                                                </div>

                                                <div class="col-sm-4">
                                                        <input type="checkbox" value="1" class="checkbox" id="pos-return"
                                                            name="pos-return" <?php echo $p->{'pos-return'} ? "checked" : ''; ?>>
                                                        <label for="pos-return" class="padding05"><?= lang('Show Return Button') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                        <input type="checkbox" value="1" class="checkbox" id="pos-exchange"
                                                            name="pos-exchange" <?php echo $p->{'pos-exchange'} ? "checked" : ''; ?>>
                                                        <label for="pos-exchange" class="padding05"><?= lang('Show Exchange Button') ?></label>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><?= lang("CRM Portal") ?></td>
                                        <td colspan="5"> 
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="crm_portal"
                                                           name="crm_portal" <?php echo $p->{'crm_portal'} ? "checked" : ''; ?>>
                                                    <label for="crm_portal" class="padding05"><?= lang('CRM Portal') ?></label>
                                                </div>
                                            </div>
                                        </td>    
                                    </tr>
                                    <tr>
                                        <td><?= lang("Data Synchronization") ?></td>
                                        <td colspan="5"> 
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="data_synchronization"
                                                           name="data_synchronization" <?php echo $p->{'offlinepos-synchronization'} ? "checked" : ''; ?>>
                                                    <label for="data_synchronization" class="padding05"><?= lang('Data Synchronization') ?></label>
                                                </div>
                                            </div>
                                        </td>    
                                    </tr>
									<tr>
                                        <td><?= lang("Production_Unit") ?></td>
                                        <td colspan="5"> 
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="Procurement_Orders"
                                                           name="Procurement_Orders" <?php echo $p->{'Procurement_Orders'} ? "checked" : ''; ?>>
                                                    <label for="Procurement_Orders" class="padding05"><?= lang('Procurement Orders') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="Order_Dispatch"
                                                           name="Order_Dispatch" <?php echo $p->{'Order_Dispatch'} ? "checked" : ''; ?>>
                                                    <label for="Order_Dispatch" class="padding05"><?= lang('Working Orders') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_manager_dashboard"
                                                           name="production_manager_dashboard" <?php echo $p->{'production_manager_dashboard'} ? "checked" : ''; ?>>
                                                    <label for="production_manager_dashboard" class="padding05"><?= lang('Production Manager dashboard') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-add_product"
                                                           name="production_unit-add_product" <?php echo $p->{'production_unit-add_product'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-add_product" class="padding05"><?= lang('MK – Add Product') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-ready_to_dispatch"
                                                           name="production_unit-ready_to_dispatch" <?php echo $p->{'production_unit-ready_to_dispatch'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-ready_to_dispatch" class="padding05"><?= lang('MK – Ready to Dispatch') ?></label>
                                                </div>
                                                <!-- <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-recipes"
                                                           name="production_unit-recipes" <?php echo $p->{'production_unit-recipes'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-recipes" class="padding05"><?= lang('Recipes') ?></label>
                                                </div> -->
                                                <!-- <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-ok_dashboard"
                                                           name="production_unit-ok_dashboard" <?php echo $p->{'production_unit-ok_dashboard'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-ok_dashboard" class="padding05"><?= lang('OKitchen -OK Dashboard') ?></label>
                                                </div> -->
                                                <!-- <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-kds"
                                                           name="production_unit-kds" <?php echo $p->{'production_unit-kds'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-kds" class="padding05"><?= lang('OKitchen - KDS') ?></label>
                                                </div> -->
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-receive_delivery"
                                                           name="production_unit-receive_delivery" <?php echo $p->{'production_unit-receive_delivery'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-receive_delivery" class="padding05"><?= lang('PO - Receive Delivery') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-inventory"
                                                           name="production_unit-inventory" <?php echo $p->{'production_unit-inventory'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-inventory" class="padding05"><?= lang('PO - Inventory') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_unit-ordering_history"
                                                           name="production_unit-ordering_history" <?php echo $p->{'production_unit-ordering_history'} ? "checked" : ''; ?>>
                                                    <label for="production_unit-ordering_history" class="padding05"><?= lang('PO - Ordering History') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="production_dashboard"
                                                           name="production_dashboard" <?php echo $p->{'Production_Dashboard'} ? "checked" : ''; ?>>
                                                    <label for="production_dashboard" class="padding05"><?= lang('Production Dashboard') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="Recipies"
                                                           name="Recipies" <?php echo $p->{'Recipies'} ? "checked" : ''; ?>>
                                                    <label for="Recipies" class="padding05">MK - <?= lang('Recipies') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="OK_Recipies"
                                                           name="OK_Recipies" <?php echo !empty($p->{'OK_Recipies'}) ? "checked" : ''; ?>>
                                                    <label for="OK_Recipies" class="padding05">Outlet Kitchen - <?= lang('Recipies') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="OK_Dashboard"
                                                           name="OK_Dashboard" <?php echo $p->{'OK_Dashboard'} ? "checked" : ''; ?>>
                                                    <label for="OK_Dashboard" class="padding05"><?= lang('Outlet Kitchen Dashboard') ?></label>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="checkbox" value="1" class="checkbox" id="KDS"
                                                           name="KDS" <?php echo $p->{'KDS'} ? "checked" : ''; ?>>
                                                    <label for="KDS" class="padding05"><?= lang('KDS') ?></label>
                                                </div>
                                            </div>
                                        </td>    
                                    </tr>


                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
                        </div>
                        <?php
                        echo form_close();
                    } else {
                        echo $this->lang->line("group_x_allowed");
                    }
                } else {
                    echo $this->lang->line("group_x_allowed");
                }
                ?>


            <script type="text/javascript">
$(document).ready(function() {
    var isProgrammaticUpdate = false;

    // Function to update the main checkbox based on sub-checkboxes
    function updateMainCheckbox(section) {
        var total = $('.' + section + '-sub').length;
        var checked = $('.' + section + '-sub:checked').length;
        var $main = $('#main-' + section);
        var shouldBeChecked = total > 0 && total === checked;
        
        isProgrammaticUpdate = true;
        if (shouldBeChecked) {
            if (typeof $main.iCheck === 'function') {
                $main.iCheck('check');
            } else {
                $main.prop('checked', true);
            }
        } else {
            if (typeof $main.iCheck === 'function') {
                $main.iCheck('uncheck');
            } else {
                $main.prop('checked', false);
            }
        }
        isProgrammaticUpdate = false;
    }

    // Toggle sub-checkboxes when main checkbox is checked/unchecked
    $('.main-module-checkbox').on('ifChecked', function() {
        if (isProgrammaticUpdate) return;
        var section = $(this).data('section');
        isProgrammaticUpdate = true;
        var $subs = $('.' + section + '-sub');
        if (typeof $subs.iCheck === 'function') {
            $subs.iCheck('check');
        } else {
            $subs.prop('checked', true);
        }
        isProgrammaticUpdate = false;
        
        // Auto-expand section on check
        expandSection(section);
    });

    $('.main-module-checkbox').on('ifUnchecked', function() {
        if (isProgrammaticUpdate) return;
        var section = $(this).data('section');
        isProgrammaticUpdate = true;
        var $subs = $('.' + section + '-sub');
        if (typeof $subs.iCheck === 'function') {
            $subs.iCheck('uncheck');
        } else {
            $subs.prop('checked', false);
        }
        isProgrammaticUpdate = false;
    });

    // Also support standard change event for non-iCheck fallback
    $('.main-module-checkbox').on('change', function() {
        if (isProgrammaticUpdate) return;
        var section = $(this).data('section');
        var isChecked = $(this).prop('checked');
        isProgrammaticUpdate = true;
        var $subs = $('.' + section + '-sub');
        $subs.prop('checked', isChecked);
        if (typeof $subs.iCheck === 'function') {
            $subs.iCheck(isChecked ? 'check' : 'uncheck');
        }
        isProgrammaticUpdate = false;
        
        if (isChecked) {
            expandSection(section);
        }
    });

    // Toggle section visibility
    $('.main-module-toggle').on('click', function() {
        var section = $(this).data('section');
        var $subs = $('.' + section + '-sub').closest('.col-sm-4');
        var $folder = $(this).find('.main-folder-icon');
        var $caret = $(this).find('.toggle-caret');
        
        if ($subs.is(':visible')) {
            $subs.slideUp(200);
            $folder.removeClass('fa-folder-open').addClass('fa-folder');
            $caret.removeClass('fa-angle-up').addClass('fa-angle-down');
        } else {
            $subs.slideDown(200);
            $folder.removeClass('fa-folder').addClass('fa-folder-open');
            $caret.removeClass('fa-angle-down').addClass('fa-angle-up');
        }
    });

    // Function to expand a section
    function expandSection(section) {
        var $subs = $('.' + section + '-sub').closest('.col-sm-4');
        var $toggle = $('.main-module-toggle[data-section="' + section + '"]');
        var $folder = $toggle.find('.main-folder-icon');
        var $caret = $toggle.find('.toggle-caret');
        
        if (!$subs.is(':visible')) {
            $subs.slideDown(200);
            $folder.removeClass('fa-folder').addClass('fa-folder-open');
            $caret.removeClass('fa-angle-down').addClass('fa-angle-up');
        }
    }

    // List of all sections
    var sections = ['accounts', 'challan', 'customer', 'ledger', 'logs', 'products', 'purchase', 'sales', 'transfers', 'dashboard'];
    
    sections.forEach(function(section) {
        // Initialize parent checkbox state on load
        updateMainCheckbox(section);
        
        // Initially hide all submodules
        $('.' + section + '-sub').closest('.col-sm-4').hide();
        
        // Listen to changes in sub-checkboxes
        $('.' + section + '-sub').on('ifChanged change', function() {
            if (isProgrammaticUpdate) return;
            updateMainCheckbox(section);
        });
    });
});
</script>
</div>
        </div>
    </div>
</div>