<?php
$CI = &get_instance();
if (!isset($GP) || !is_array($GP)) {
    $GP = (isset($CI->GP) && is_array($CI->GP)) ? $CI->GP : array();
}
if (!isset($active_dropdown)) { $active_dropdown = ''; }
if (!isset($active_item)) { $active_item = ''; }
?>
<?php if($Settings->active_omnichannel) { ?>
    <!--  Urbanpiper -->
    <li class="mm_urban_piper">
        <a class="dropmenu" href="#">
            <i class="fa fa-globe"></i>
            <span class="text"> <?= lang('Omnichannel'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="urbanpiper_category">
                <a class="submenu" href="<?= site_url('Omnichannel') ?>">
                    <i class="fa fa-list" aria-hidden="true"></i>
                    <span class="text"> Working Orders </span>
                </a>
            </li>
            <li class="mm_urban_piper">
            <a class="dropmenu" href="#">
                <i class="fa fa-magnet"></i>
                <span class="text"> <?= lang('Orders History'); ?> </span>
                <span class="chevron closed"></span>
            </a>
            <ul>
            <li id="urbanpiper_category1">
                <a class="submenu" href="<?= site_url('Omnichannel/orders_inactive') ?>">
                    <i class="fa fa-list" aria-hidden="true"></i>
                    <span class="text"> Up Orders </span>
                </a>
            </li>
            <li id="urbanpiper_category2">
                <a class="submenu" href="<?= site_url('orders/eshop_order') ?>">
                    <i class="fa fa-list" aria-hidden="true"></i>
                    <span class="text"> Webshop Orders </span>
                </a>
            </li>
            </ul>
            </li>
        </ul>
    </li>
    <!-- Urbanpiper -->
<?php }//end if ?>
<li id="products" class="mm_products main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-archive"></i>
        <span class="text"> <?= lang('products'); ?> </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <li id="products_add" class="list-item">
            <a class="submenu item" href="<?= site_url('products/add'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('add_product'); ?></span>
            </a>
        </li>
        <?php
            $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $products_list_url = (preg_match('/android|webos|iphone|ipad|ipod|mobile|blackberry|opera mini/i', $ua))
                ? site_url('Products_Mobile')
                : site_url('products');
        ?>
        <li id="products_index" class="list-item" itemvalue=<?= lang('list_products'); ?>>
            <a class="submenu item" href="<?= $products_list_url; ?>">
                <i class="fa fa-barcode item"></i>
                <span class="text item"> <?= lang('list_products'); ?></span>
            </a>
        </li>
        
        <?php if ($Settings->enable_module_production_unit) { ?>
            <li id="products_raw_materials" class="list-item">
                <a class="submenu item"
                    href="<?= site_url('products/rawMaterials'); ?>">
                    <i class="fa fa-star item"></i>
                    <span class="text item"> <?= lang('Raw_Materials'); ?></span> 
                </a>
            </li>
        <?php } ?>
        <?php if ($Settings->pos_type == 'restaurant' && $pos_settings->combo_add_pos) { ?>
            <li id="products_index" class="list-item">
                <a class="submenu item" href="<?= site_url('products/poscombo'); ?>">
                    <i class="fa fa-barcode item"></i>
                    <span class="text item"> <?= lang('List_POS Combo Product'); ?></span>
                </a>
            </li>
        <?php } ?>
        
        
        <li id="products_import_csv" class="list-item">
            <a class="submenu item" href="<?= site_url('products/import_csv'); ?>">
                <i class="fa fa-file-text item"></i>
                <span class="text item"> <?= lang('import_products'); ?></span>
            </a>
        </li>

        <li id="products_print_barcodes" class="list-item">
            <a class="submenu item"
                href="<?= site_url('products/print_barcodes'); ?>">
                <i class="fa fa-tags item"></i>
                <span class="text item"> <?= lang('print_barcode_label'); ?></span>
            </a>
        </li>
        
        
        <?php if ($pos_settings->pos_screen_products) { ?>
            <li id="products_favourite" class="list-item">
                <a class="submenu item"
                    href="<?= site_url('products/list_favourite'); ?>">
                    <i class="fa fa-star item"></i>
                    <span class="text item"> <?= lang('List_Favourite_Products'); ?></span> 
                </a>
            </li>
        <?php } ?>        


    </ul>
    </li>
<!-- <li class="mm_orders main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-bar-chart"></i>
        <span class="text"> <?= lang('orders'); ?>
        </span> <span class="chevron closed"></span>
    </a>
    <ul>
        <?php if ($Settings->active_eshop) { ?>
            <li id="orders_eshop_order" class="list-item">
                <a class="submenu item" href="<?= site_url('orders/eshop_order'); ?>">
                    <i class="fa fa-list-ol item"></i>
                    <span class="text item"> Eshop Orders</span>
                </a>
            </li>
        <?php } ?>
        <li id="orders_order_items" class="list-item">
            <a class="submenu item" href="<?= site_url('orders/order_items'); ?>">
                <a class="submenu item" href="">
                <i class="fa fa-list-ol item"></i>
                <span class="text item"> <?= lang('Order_Items_List'); ?></span>
            </a>
        </li>
        <?php if (in_array($Settings->pos_type, ['fruits_vegetables', 'fruits_vegetabl', 'grocerylite', 'grocery'])) { ?>
            <li id="orders_order_items_stocks" class="list-item">
                <a class="submenu item" href="<?= site_url('orders/order_items_stocks'); ?>">
                    <i class="fa fa-list-ol item"></i>
                    <span class="text item"> <?= lang('Order Products Quantity'); ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>
</li> -->
<?php if ($Owner || $Admin || $GP['products-adjustments'] || $GP['products-stock_count'] || $GP['products-batches']) { ?>
<li class="mm_inventory main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-dropbox"></i>
        <span class="text"> <?= lang('Inventory'); ?>
        </span> <span class="chevron closed"></span>
    </a>
    <ul>
        <?php if ($Owner || $Admin || $GP['products-adjustments']) { ?>
        <li id="products_add_adjustment" class="list-item">
            <a class="submenu item"
                href="<?= site_url('products/add_adjustment'); ?>">
                <i class="fa fa-filter item"></i>
                <span class="text item"> <?= lang('add_adjustment'); ?></span>
            </a>
        </li>
        <li id="products_quantity_adjustments" class="list-item">
            <a class="submenu item"
                href="<?= site_url('products/quantity_adjustments'); ?>">
                <i class="fa fa-filter item"></i>
                <span class="text item"> <?= lang('quantity_adjustments'); ?></span>
            </a>
        </li>
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['products-stock_count']) { ?>
        <li id="products_stock_counts" class="list-item">
            <a class="submenu item" href="<?= site_url('products/stock_counts'); ?>">
                <i class="fa fa-list-ol item"></i>
                <span class="text item"> <?= lang('stock_counts'); ?></span>
            </a>
        </li>
        <li id="products_count_stock" class="list-item">
            <a class="submenu item" href="<?= site_url('products/count_stock'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('count_stock'); ?></span>
            </a>
        </li>
        <li id="reports_checkstock" class="list-item">
            <a class="submenu item" href="<?= site_url('CheckStock') ?>">
                <i class="fa fa-line-chart" aria-hidden="true">
                    </i><span class="text"> <?= lang('Stock Check'); ?></span> 
            </a>
        </li>
        <?php } ?>
        <?php if (($Settings->product_batch_setting > 0 && $GP['products-batches']) || $Owner || $Admin) { ?>
            <li id="products_batches" class="list-item">
                <a href="<?= site_url('products/batches') ?>" class="item">
                    <i class="fa fa-database item"></i><span
                        class="text item"> <?= lang('Manage Batches'); ?></span> 
                </a>
            </li>
        <?php } ?>
        </ul>
</li>
<?php } ?>
    <li class="mm_purchases main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-shopping-cart"></i>
            <span class="text"> <?= lang('purchases'); ?>
            </span> <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="purchases_index" class="list-item">
                <a class="submenu item" href="<?= site_url('purchases'); ?>">
                    <i class="fa fa-star item"></i>
                    <span class="text item"> <?= lang('list_purchases'); ?></span>
                </a>
            </li>
            <li id="purchases_add" class="list-item">
                <a class="submenu item" href="<?= site_url('purchases/add'); ?>">
                    <i class="fa fa-plus-circle item"></i>
                    <span class="text item"> <?= lang('add_purchase'); ?></span>
                </a>
            </li>
            <li id="purchases_purchase_by_csv" class="list-item">
                <a class="submenu item"
                    href="<?= site_url('purchases/purchase_by_csv'); ?>">
                    <i class="fa fa-plus-circle item"></i>
                    <span class="text item"> <?= lang('add_purchase_by_csv'); ?></span>
                </a>
            </li>
            <li id="purchases_expenses" class="list-item">
                <a class="submenu item" href="<?= site_url('purchases/expenses'); ?>">
                    <i class="fa fa-dollar item"></i>
                    <span class="text item"> <?= lang('list_expenses'); ?></span>
                </a>
            </li>
            <li id="purchases_add_expense" class="list-item">
                <a class="submenu item" href="<?= site_url('purchases/add_expense'); ?>"
                    data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-plus-circle item"></i>
                    <span class="text item"> <?= lang('add_expense'); ?></span>
                </a>
            </li>
            <?php if ($Settings->synced_data_sales) { ?>
                <li id="purchases_noification" class="list-item">
                    <a class="submenu item" href="<?= site_url('purchases/purchase_notification'); ?>">
                        <i class="fa fa-dollar item"></i>
                        <span class="text item"> <?= lang('Purchase_Notification'); ?></span>
                    </a>
                </li>
            <?php } ?>
                <li id="suppliers_index" class="list-item">
                <a class="submenu item" href="<?= site_url('suppliers'); ?>">
                    <i class="fa fa-users item"></i><span
                        class="text item"> <?= lang('list_suppliers'); ?></span>
                </a>
            </li>
            <li id="suppliers_index" class="list-item">
                <a class="submenu item" href="<?= site_url('suppliers/add'); ?>"
                    data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-plus-circle item"></i><span
                        class="text item"> <?= lang('add_supplier'); ?></span>
                </a>
            </li>
        </ul>
    </li>
<?php
$gp_attendance_adm = (isset($GP) && is_array($GP)) ? $GP : array();
$attendance_module_enabled_adm = !isset($Settings->enable_module_attendance) || (string) $Settings->enable_module_attendance === '1';
$show_attendance_menu_adm = $attendance_module_enabled_adm && (!empty($Owner) || !empty($Admin) || !empty($gp_attendance_adm['attendance-index']) || !empty($gp_attendance_adm['attendance-create']) || !empty($gp_attendance_adm['attendance-edit']) || !empty($gp_attendance_adm['attendance-delete']) || !empty($gp_attendance_adm['attendance-enroll']));
?>
<?php if ($show_attendance_menu_adm) { ?>
<li class="mm_attendance main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-calendar-check-o"></i>
        <span class="text"> Attendance </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <?php if (!empty($Owner) || !empty($Admin) || !empty($gp_attendance_adm['attendance-create'])) { ?>
        <li id="attendance_create" class="list-item">
            <a class="submenu item" href="<?= site_url('attendance/captured'); ?>">
                <i class="fa fa-camera item"></i>
                <span class="text item"> Capture Attendance </span>
            </a>
        </li>
        <?php } ?>
        <?php if (!empty($Owner) || !empty($Admin) || !empty($gp_attendance_adm['attendance-index'])) { ?>
        <li id="attendance_index" class="list-item">
            <a class="submenu item" href="<?= site_url('attendance'); ?>">
                <i class="fa fa-list item"></i>
                <span class="text item"> Attendance List </span>
            </a>
        </li>
        <?php } ?>
       
    </ul>
</li>
<?php } ?>

<!-- <li class="mm_service_site_report main-item">
    <a class="submenu item" href="<?= site_url('service-site-report'); ?>">
        <i class="fa fa-file-text-o item"></i>
        <span class="text item"> Service Site Report </span>
    </a>
</li> -->

<li class="mm_sales mm_eshop_sales mm_pos main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-bar-chart"></i>
        <span class="text"> <?= lang('sales'); ?>
        </span> <span class="chevron closed"></span>
    </a>
    <ul>
        <li id="sales_index" class="list-item">
            <a class="submenu item" href="<?= site_url('sales'); ?>">
                <i class="fa fa-heart item"></i>
                <span class="text item"> <?= lang('list_sales'); ?></span>
            </a>
        </li>
        <?php if ($Settings->active_eshop) { ?>
            <li id="eshop_sales_sales" class="list-item">
                <a class="submenu item" href="<?= site_url('eshop_sales/sales'); ?>">
                    <i class="fa fa-heart item"></i>
                    <span class="text item"> Eshop Sales</span>
                </a>
            </li>
        <?php } ?>

        <li id="offline_sales" class="list-item">
            <a class="submenu item" href="<?= site_url('offline/sales'); ?>">
                <i class="fa fa-heart item"></i>
                <span class="text item"> Offline Sales </span>
            </a>
        </li>
        <?php if (POS) { ?>
            <li id="pos_sales" class="list-item">
                <a class="submenu item" href="<?= site_url('pos/sales'); ?>">
                    <i class="fa fa-heart item"></i>
                    <span class="text item"> <?= lang('pos_sales'); ?></span>
                </a>
            </li>
        <?php } ?>
        <?php if ($Settings->active_urbanpiper) { ?>
            <li class="urban_piper_sales" class="list-item">
                <a class="submenu item" href="<?= site_url('urban_piper/sales'); ?>">
                    <i class="fa fa-plus-circle item"></i>
                    <span class="text item"> <?= lang('Urban Piper Sales'); ?></span>
                </a>
            </li>
        <?php } ?>

        <?php
            $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $sales_all_url = (preg_match('/android|webos|iphone|ipad|ipod|mobile|blackberry|opera mini/i', $ua))
                ? site_url('sales_mobile/all_sale_lists')
                : site_url('sales/all_sale_lists');
        ?>
        <li id="sales_all_sale_lists" class="list-item">
            <a class="submenu item" href="<?= $sales_all_url; ?>">
                <i class="fa fa-heart item"></i>
                <span class="text item"> <?= lang('All_Sale_List'); ?> </span>
            </a>
        </li>
        <li id="sales_add" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/add'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('add_sale'); ?></span>
            </a>
        </li>
        <li id="sales_sale_by_csv" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/sale_by_csv'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('add_sale_by_csv'); ?></span>
            </a>
        </li>
        <li id="sales_deliveries" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/deliveries'); ?>">
                <i class="fa fa-truck item"></i>
                <span class="text item"> <?= lang('deliveries'); ?></span>
            </a>
        </li>
        <li id="sales_gift_cards" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/gift_cards'); ?>">
                <i class="fa fa-gift item"></i>
                <span class="text item"> <?= lang('list_gift_cards'); ?></span>
            </a>
        </li>
        <li id="sales_credit_note" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/credit_note'); ?>">
                <i class="fa fa-gift item"></i>
                <span class="text item"> <?= lang('List_Credit_Note'); ?></span>
            </a>
        </li>
         <li id="customers_index" class="list-item">
            <a class="submenu item" href="<?= site_url('customers'); ?>">
                <i class="fa fa-users item"></i><span
                    class="text item"> <?= lang('list_customers'); ?></span>
            </a>
        </li>
        <li id="customers_index" class="list-item">
            <a class="submenu item" href="<?= site_url('customers/add'); ?>"
                data-toggle="modal" data-target="#myModal">
                <i class="fa fa-plus-circle item"></i><span
                    class="text item"> <?= lang('add_customer'); ?></span>
            </a>
        </li>
    </ul>
</li>

<li class="mm_challans main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-file-text-o"></i>
        <span class="text"> <?= lang('Challans'); ?> 
        <span class="chevron closed"></span>
    </a>
    <ul>
        <li id="sales_challans" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/challans'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('Challans List'); ?> </span>
            </a>
        </li>
        <li id="sales_challan" class="list-item">
            <a class="submenu item" href="<?= site_url('sales/add?sale_action=chalan'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('Add Challan'); ?></span>
            </a>
        </li>
    </ul>
</li>
<li class="mm_quotes main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-file-text-o"></i>
        <span class="text"> <?= lang('quotes'); ?> </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <li id="quotes_index" class="list-item">
            <a class="submenu item" href="<?= site_url('quotes'); ?>">
                <i class="fa fa-heart-o item"></i>
                <span class="text item"> <?= lang('list_quotes'); ?></span>
            </a>
        </li>
        <li id="quotes_add" class="list-item">
            <a class="submenu item" href="<?= site_url('quotes/add'); ?>">
                <i class="fa fa-plus-circle item"></i>
                <span class="text item"> <?= lang('add_quote'); ?></span>
            </a>
        </li>
    </ul>
</li>

<li class="mm_transfers main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-exchange"></i>
        <span class="text"> <?= lang('transfers'); ?> </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <li id="transfers_index" class="list-item">
            <a class="submenu item" href="<?= site_url('transfers'); ?>">
                <i class="fa fa-star-o item"></i><span
                    class="text item"> <?= lang('list_transfers'); ?></span>
            </a>
        </li>
        <li id="transfers_add" class="list-item">
            <a class="submenu item" href="<?= site_url('transfers/add'); ?>">
                <i class="fa fa-plus-circle item"></i><span
                    class="text item"> <?= lang('add_transfer'); ?></span>
            </a>
        </li>
        <li id="transfers_transfer_by_csv" class="list-item">
            <a class="submenu item"
                href="<?= site_url('transfers/transfer_by_csv'); ?>">
                <i class="fa fa-plus-circle item"></i><span
                    class="text item"> <?= lang('add_transfer_by_csv'); ?></span>
            </a>
        </li>

        <li id="transfers_request" class="list-item">
            <a class="submenu item"
                href="<?= site_url('transfers/request'); ?>">
                <i class="fa fa-exchange item"></i><span
                    class="text item"> <?= lang('Requests'); ?> </span>
            </a>
        </li>
        <li id="transfers_add_request" class="list-item">
            <a class="submenu item"
                href="<?= site_url('transfers/add_request'); ?>">
                <i class="fa fa-plus-circle item"></i><span
                    class="text item"> <?= lang('Add Request'); ?> </span>
            </a>
        </li>
    </ul>
</li>

<li class="mm_auth mm_customers mm_suppliers mm_billers main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-users"></i>
        <span class="text"> <?= lang('people'); ?> </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <?php if ($Owner) { ?>
            <li id="auth_users" class="list-item">
                <a class="submenu item" href="<?= site_url('users'); ?>">
                    <i class="fa fa-users item"></i><span
                        class="text item"> <?= lang('list_users'); ?></span>
                </a>
            </li>
            <li id="auth_create_user" class="list-item">
                <a class="submenu item" href="<?= site_url('users/create_user'); ?>">
                    <i class="fa fa-user-plus item"></i><span
                        class="text item"> <?= lang('new_user'); ?></span>
                </a>
            </li>
            <li id="billers_index" class="list-item">
                <a class="submenu item" href="<?= site_url('billers'); ?>">
                    <i class="fa fa-users item"></i><span
                        class="text item"> <?= lang('list_billers'); ?></span>
                </a>
            </li>
            <li id="billers_index" class="list-item">
                <a class="submenu item" href="<?= site_url('billers/add'); ?>"
                    data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-plus-circle item"></i><span
                        class="text item"> <?= lang('add_biller'); ?></span>
                </a>
            </li>
            <li id="employees_index" class="list-item">
                <a class="submenu item" href="<?= site_url('employees/index'); ?>">
                    <i class="fa fa-users item"></i><span
                        class="text item"> <?= lang('List_Employees'); ?></span> 
                </a>
            </li>
            <li id="employees_add" class="list-item">
                <a class="submenu item" href="<?= site_url('employees/add'); ?>"
                    data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-plus-circle item"></i><span
                        class="text item"> <?= lang('Add_Employee'); ?></span> 
                </a>
            </li>
        <?php } ?>
    </ul>
</li>
<li class="mm_notifications list-item main-item" id="mm_notifications">
    <a class="submenu item" href="<?= site_url('notifications'); ?>">
        <i class="fa fa-info-circle item"></i><span
            class="text item"> <?= lang('notifications'); ?></span>
    </a>
</li>
<?php if ($Settings->enable_module_production_unit && $Settings->mother_kitchen == 1) { ?>
    <li class="mm_production_unit main-item">
        <a class="dropmenu" href="#">
            <!-- <i class="fa fa-warehouse custom-icon" style="width: 20px;"></i> -->
            <i class="fa fa-industry"></i>
            <span class="text"> <?= lang('Production Unit'); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></span>
            <span class="chevron closed"></span>
        </a>
        <ul>
           <?php if ($Settings->enable_module_production_unit && (!isset($Settings->mother_kitchen) || $Settings->mother_kitchen == 1)) { ?>
            <li id="Recipies" class="list-item">
                <a class="submenu item" href="<?= site_url('Recipies'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Bill of Materials'); ?> </span>
                </a>
            </li>
             <li id="production_units" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/manager_dashboard'); ?>">
                    <i class="fa fa-industry item"></i>
                    <span class="text item"> <?= lang('Production Dashboard'); ?> </span>
                </a>
            </li>
           
            <li id="production_units1" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit_New/working_orders'); ?>">
                    <i class="fa fa-industry item"></i>
                    <span class="text item"> <?= lang('Working_Orders'); ?> </span>
                </a>
            </li>
            <li id="production_units2" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/Ready_To_Dispatch'); ?>">
                    <i class="fa fa-industry item"></i>
                    <span class="text item"> <?= lang('Ready_To_Dispatch'); ?> </span>
                </a>
            </li>

             <li id="production_units3" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/add_product'); ?>">
                    <i class="fa fa-industry item"></i>
                    <span class="text item"> <?= lang('Add_Product'); ?> </span>
                </a>
            </li> 

            <li id="production_units4" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/production_dashboard'); ?>">
                    <i class="fa fa-industry item"></i>
                    <span class="text item"> <?= lang('Floor Dashboard'); ?> </span>
                </a>
            </li>
            <?php } ?>        
           
        </ul>
    </li>
<?php } ?>
<!-- for Outlet Kitchen -->
<?php if ($Settings->enable_module_production_unit && (!isset($Settings->mother_kitchen) || $Settings->mother_kitchen == 1)) { ?>
    <!-- Outlet Kitchen (same permissions as existing Production Unit module) -->
    <li class="mm_production_unit main-item">
        <a class="dropmenu" href="">
            <i class="fa fa-industry"></i>
            <span class="text">
                <?= lang('Outlet Kitchen'); ?>
            </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($Settings->enable_module_production_unit && (!isset($Settings->mother_kitchen) || $Settings->mother_kitchen == 1)) { ?>
                <li id="Recipies-ok" class="list-item">
                    <a class="submenu item" href="<?= site_url('Recipies'); ?>">
                        <i class="fa fa-home item"></i>
                        <span class="text item"><?= lang('Bill of Materials'); ?></span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($Settings->enable_module_production_unit && (!isset($Settings->mother_kitchen) || $Settings->mother_kitchen == 1)) { ?>
                <li id="kitchen_user_dashboard" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/kitchen_user_dashboard'); ?>">
                        <i class="fa fa-cutlery"></i>
                        <span class="text">OK Dashboard</span>
                    </a>
                </li>
            <?php } ?>
            <li id="production_units_sdk" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/kds_D'); ?>">
                    <i class="fa fa-desktop item"></i>
                    <span class="text item"> KDS </span>
                </a>
            </li>
        </ul>
    </li>
<?php } ?>  
<!-- /////////////////Generate Variant PO ///////////////////////////////// -->
<?php if ($Settings->display_job_work) { ?>
    <li class="mm_production_unit main-item">
        <a class="dropmenu" href="#">
            <!-- <i class="fa fa-warehouse custom-icon" style="width: 20px;"></i> -->
            <i class="fa fa-industry"></i>
            <span class="text"> <?= lang('Job_Works'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="Variant_Bill_of_material" class="list-item">
                <a class="submenu" href="<?= site_url('Variant_bill_of_materials'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Variant_Bill_Of_Materials'); ?> </span>
                </a>
            </li>
            
             <li id="production_units9" class="list-item">
                <a class="submenu item" href="<?= site_url('RM_Calculator'); ?>">
                    <i class="fa fa-industry item"></i>
                    <span class="text item"> <?= lang('RM Calculator'); ?> </span>
                </a>
            </li>
             <li id="transfers_transfer_rm" class="list-item">
                <a class="submenu item" href="<?= site_url('transfers/transfer_rm'); ?>">
                    <i class="fa fa-exchange item"></i><span class="text item"> Transfer RM </span>
                </a>
            </li>
            <li id="purchases_index_job_works" class="list-item">
                <a class="submenu item" href="<?= site_url('purchases'); ?>">
                    <i class="fa fa-star item"></i>
                    <span class="text item"> <?= lang('list_purchases'); ?></span>
                </a>
            </li>
            <li id="vendor_rates_job_works" class="list-item">
                <a class="submenu item" href="<?= site_url('vendor_rates'); ?>">
                    <i class="fa fa-money item"></i>
                    <span class="text item"> <?= lang('Vendor_Rates'); ?></span>
                </a>
            </li>
            <li id="vendors_stock_report" class="list-item">
                <a class="submenu item" href="<?= site_url('Variant_bill_of_materials/vendor_stock'); ?>">
                    <i class="fa fa-exchange item"></i><span class="text item"> Vendors Stock Report </span>
                </a>
            </li>
            <?php if (!empty($GP['raw_materials'])) { ?>
                <li id="raw_materials_job_works" class="list-item">
                    <a href="<?= site_url('products/rawMaterials') ?>">
                        <i class="fa fa-database"></i><span class="text"> <?= lang('List_Raw_Material_Products'); ?></span> 
                    </a>
                </li>
                <!-- End Batch No -->
            <?php } ?>
        </ul>
    </li>
<?php } ?>
    
<!-- for place order -->
<?php if ($Settings->enable_module_procurment_order) { ?>
    <li class="mm_production_unit main-item">
        <a class="dropmenu" href="#">
            <!-- <i class="fa fa-warehouse custom-icon" style="width: 20px;"></i> -->
            <i class="fa fa-home"></i>
            <span class="text"> <?= lang('Procurement_Orders'); ?> </span>
            <span class="chevron closed"></span>
        </a> 
        <ul>
            <li id="production_units5" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/procurementOrders'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Place_Orders'); ?> </span>
                </a>
            </li>
            <li id="production_units6" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/receive_delivery'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Receive_Delivery'); ?> </span>
                </a>
            </li>
            <li id="production_units7" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/inventory'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Inventory'); ?> </span>
                </a>
            </li>
            <li id="production_units8" class="list-item">
                <a class="submenu item" href="<?= site_url('Production_Unit/ordering_history'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Ordering_History'); ?> </span>
                </a>
            </li>
        </ul>
    </li>
<?php } ?>
<li class="main-item" id="CRM PORTAL">
    <a class="submenu" href="<?= site_url('smsdashboard'); ?>">
        <i class="fa fa-envelope item"></i><span
            class="text item"> <?= lang('CRM Portal'); ?></span> 
    </a>
</li>
<?php if ($Owner) { ?>
    <?php if ($Settings->active_eshop) { ?>
        <li class="mm_eshop_admin main-item<?= strtolower($this->router->fetch_method()) != 'eshop_admin' ? '' : 'eshop_admin' ?>">
            <a class="dropmenu" href="#">
                <i class="fa fa-cart-plus"></i><span
                    class="text">Eshop Settings</span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="eshop_admin_pages" class="list-item">
                    <a class="submenu item" href="<?= site_url('eshop_admin/pages'); ?>">
                        <i class="fa fa-newspaper-o item"></i>
                        <span class="text item"> Eshop Custom Pages</span>
                    </a>
                </li>
                <li id="eshop_admin_shipping_methods" class="list-item">
                    <a class="submenu item" href="<?= site_url('eshop_admin/shipping_methods'); ?>">
                        <i class="fa fa-cog item"></i>
                        <span class="text item"> Shipping & Deliveries</span>
                    </a>
                </li>
                <li id="eshop_admin_settings" class="list-item">
                    <a class="submenu item" href="<?= site_url('eshop_admin/settings'); ?>">
                        <i class="fa fa-image item"></i>
                        <span class="text item"> Media & Settings</span>
                    </a>
                </li>
                <li id="eshop_admin_manage_products" class="list-item">
                    <a class="submenu item" href="<?= site_url('eshop_admin/manage_products'); ?>">
                        <i class="fa fa-list-ol item"></i>
                        <span class="text item"> Manage Products</span> 
                    </a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if ($Settings->active_webshop) { ?>
        <li class="mm_webshop_settings">
            <a class="dropmenu" href="#">
                <i class="fa fa-cart-plus"></i><span
                    class="text">Ecommerce </span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="webshop_settings_index" class="list-item">
                    <a class="submenu item" href="<?= site_url('webshop_settings'); ?>">
                        <i class="fa fa-cogs item" aria-hidden="true"></i>
                        <span class="text item"> Homepage Layout</span>
                    </a>
                </li>
                <li id="webshop_settings_sections" class="list-item">
                    <a class="submenu item" href="<?= site_url('webshop_settings/sections'); ?>">
                        <i class="fa fa-cogs item" aria-hidden="true"></i>
                        <span class="text item"> Homepage Sections</span>
                    </a>
                </li>
                <li id="webshop_settings_sliders" class="list-item">
                    <a class="submenu item" href="<?= site_url('webshop_settings/sliders'); ?>">
                        <i class="fa fa-image item" aria-hidden="true"></i>
                        <span class="text item"> Homepage Sliders</span>
                    </a>
                </li>
                <li id="webshop_settings_shipping_methods" class="list-item">
                    <a class="submenu item" href="<?= site_url('webshop_settings/shipping_methods'); ?>">
                        <i class="fa fa-file-text item" aria-hidden="true"></i>
                        <span class="text item"> Shipping Methods</span>
                    </a>
                </li>
                <li id="webshop_settings_manage_products" class="list-item">
                    <a class="submenu item" href="<?= site_url('webshop_settings/manage_products'); ?>">
                        <i class="fa fa-file-text item" aria-hidden="true"></i>
                        <span class="text item"> Manage Products</span>
                    </a>
                </li>
                <li id="webshop_settings_custom_pages" class="list-item">
                    <a class="submenu item" href="<?= site_url('webshop_settings/custom_pages'); ?>">
                        <i class="fa fa-file-text item" aria-hidden="true"></i>
                        <span class="text item"> Custom Pages</span>
                    </a>
                </li>
                <li id="products_manage_price" class="list-item">
                    <a href="<?= site_url('products/manage_price'); ?>" class="item">
                             <i class="fa fa-rupee" aria-hidden="true"></i>
                            <span class="text item">Manage E-commerce Price</span> 
                    </a>
                </li>
                <li id="todays_special">
                    <a class="submenu" href="<?= site_url('Todays_Special'); ?>">
                        <i class="fa fa-star"></i>
                        <span class="text"> <?= lang("Today's Special"); ?></span>
                    </a>
                </li>
                
            <?php if($Settings->business_hour) { ?>
                <li id="business_hour" class="mm_business_hour">
            <a class="dropmenu" href="#">
            <i class="fa fa-clock-o"></i>
                <span class="text"> <?= lang('Businesses Hour'); ?> </span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="business_holidays_index">
                    <a class="submenu" href="<?= site_url('Omnichannel/view_holidays'); ?>">
                    <i class="fa fa-calendar"></i>
                        <span class="text"> <?= lang('List_Holidays'); ?></span>
                    </a>
                </li>
                <li id="add_business_holiday">
                    <a class="submenu" href="<?= site_url('Omnichannel/add_holiday'); ?>">
                    <i class="fa fa-plus-circle"></i> 
                        <span class="text"> <?= lang('Add_Holiday'); ?></span>
                    </a>
                </li>
                <li id="business_schedules_index">
                    <a class="submenu" href="<?= site_url('Omnichannel/view_schdules'); ?>">
                    <i class="fa fa-calendar"></i>
                        <span class="text"> <?= lang('List_Schedules'); ?></span>
                    </a>
                </li>
                <li id="add_business_schedules">
                    <a class="submenu" href="<?= site_url('Omnichannel/add_schedule'); ?>">
                    <i class="fa fa-plus-circle"></i> 
                        <span class="text"> <?= lang('Add_Schedule'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <?php } ?>
            </ul>
        </li>
    <?php } ?>
    <?php if (isset($Settings->active_cms_admin_panel) && $Settings->active_cms_admin_panel) { ?>
        <li class="mm_cms_admin_panel main-item" id="cms_admin_panel_index">
            <a href="<?= site_url('cms_admin/dashboard'); ?>">
                <i class="fa fa-folder-open"></i>
                <span class="text">CMS Admin Panel</span>
            </a>
        </li>
    <?php } ?>
    <?php
        // Android-only visibility
        $ua_str = isset($this->agent) ? $this->agent->agent_string() : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        $isAndroidUA = (stripos($ua_str, 'Android') !== false);
        $canSeeFileManager = true; // adjust if you have per-user permission flag
    ?>
    <?php if ($isAndroidUA && $canSeeFileManager): ?>
    <li class="main-item" id="file_manager_menu">
        <a class="submenu" href="<?= site_url('file_manager'); ?>">
            <i class="fa fa-folder-open item"></i><span class="text item"> <?= lang('File_Manager'); ?></span>
        </a>
    </li>
    <?php endif; ?>


<?php if($Settings->active_urbanpiper) { ?>
    <!-- Urbanpiper Specific -->
    <li class="mm_urban_piper">
        <a class="dropmenu" href="#">
            <i class="fa fa-magnet"></i>
            <span class="text"> Urbanpiper </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="urban_piper_index">
                <a class="submenu" href="<?= site_url('urban_piper') ?>">
                    <i class="fa fa-list" aria-hidden="true"></i>
                    <span class="text"> Urbanpiper Orders </span>
                </a>
            </li>
            <li id="urban_piper_settings">
                <a class="submenu" href="<?= site_url('urban_piper/settings') ?>">
                    <i class="fa fa-cogs" aria-hidden="true"></i>
                    <span class="text"> Urbanpiper Settings </span>
                </a>
            </li>
            <li id="urban_piper_store_info">
                <a class="submenu" href="<?= site_url('urban_piper/store_info') ?>">
                    <i class="fa fa-list" aria-hidden="true"></i>
                    <span class="text"> Manage Stores </span>
                </a>
            </li>
            <li id="urban_piper_product_platform">
                <a class="submenu" href="<?= site_url('urban_piper/product_platform') ?>">
                    <i class="fa fa-archive" aria-hidden="true"></i>
                    <span class="text"> Manage Catalogue </span>
                </a>
            </li>
        </ul>
    </li>
    <!-- Urbanpiper Specific -->
<?php } ?>

    <li class="mm_system_settings main-item <?= strtolower($this->router->fetch_method()) != 'settings' ? '' : 'mm_pos' ?>">
        <a class="dropmenu" href="#">
            <i class="fa fa-cog"></i><span
                class="text"> <?= lang('settings'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="system_settings_index" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings') ?>">
                    <i class="fa fa-cog item"></i><span
                        class="text item"> <?= lang('system_settings'); ?></span>
                </a>
            </li>
            <?php if (POS) { ?>
                <li id="pos_settings" class="list-item">
                    <a class="submenu item" href="<?= site_url('pos/settings') ?>">
                        <i class="fa fa-th-large item"></i><span
                            class="text item"> <?= lang('pos_settings'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_custom_fields" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/custom_fields') ?>">
                    <i class="fa fa-cog item"></i><span class="text"> <?= lang('custom_fields'); ?>
                </a>
            </li>
            <!-- POS type labels -->
            <?php if (!empty($Settings->pos_type)) { ?>
            <li id="system_settings_pos_type_labels" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/pos_type_labels') ?>">
                    <i class="fa fa-tag item"></i><span class="text item"> <?= lang('menu_labels'); ?></span>
                </a>
            </li>
            <?php } ?>
            <!-- POS type labels -->
            <li id="system_settings_change_logo" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/change_logo') ?>"
                    data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-upload item"></i><span
                        class="text item"> <?= lang('change_logo'); ?></span>
                </a>
            </li>
            <li id="system_settings_currencies" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/currencies') ?>">
                    <i class="fa fa-money item"></i><span
                        class="text item"> <?= lang('currencies'); ?></span>
                </a>
            </li>
            <li id="system_settings_customer_groups" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/customer_groups') ?>">
                    <i class="fa fa-chain item"></i><span
                        class="text item"> <?= lang('customer_groups'); ?></span>
                </a>
            </li>
            <li id="system_settings_price_groups" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/price_groups') ?>">
                    <i class="fa fa-dollar item"></i><span
                        class="text item"> <?= lang('price_groups'); ?></span>
                </a>
            </li>
            <?php if ($Settings->pos_type == 'restaurant') { ?>
                <li id="system_settings_restaurant_tables" class="list-item">
                    <a class="submenu item" href="<?= site_url('system_settings/restaurant_tables') ?>">
                        <i class="fa fa-dollar item"></i><span class="text item"><?= lang('Restaurant_Tables'); ?> </span>
                    </a>
                </li>

                <li id="system_settings_price_groups1" class="list-item">
                    <a class="submenu item" href="<?= site_url('system_settings/restaurant_tables_price_groups') ?>">
                        <i class="fa fa-dollar item"></i><span
                            class="text item"> <?= lang('Table Price Groups'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_categories" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/categories') ?>">
                    <i class="fa fa-folder-open item"></i><span
                        class="text item"> <?= lang('categories'); ?></span>
                </a>
            </li>
            <li id="system_settings_expense_categories" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/expense_categories') ?>">
                    <i class="fa fa-folder-open item"></i><span
                        class="text item"> <?= lang('expense_categories'); ?></span>
                </a>
            </li>
            <li id="system_settings_units" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/units') ?>">
                    <i class="fa fa-wrench item"></i><span
                        class="text item"> <?= lang('units'); ?></span>
                </a>
            </li>
            <li id="system_settings_brands" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/brands') ?>">
                    <i class="fa fa-th-list item"></i><span
                        class="text item"> <?= lang('brands'); ?></span>
                </a>
            </li>
            <li id="system_settings_variants" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/variants') ?>">
                    <i class="fa fa-tags item"></i><span
                        class="text item"> <?= lang('variants'); ?></span>
                </a>
            </li>
            <li id="system_settings_variants1" class="list-item">
                <a href="<?= site_url('system_settings/variant_manage') ?>" class="item">
                    <i class="fa fa-tags item"></i><span class="text item">
                        <?= lang('Manage Variants'); ?> </span>
                </a>
            </li>
            <li id="system_settings_tax_rates" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/tax_rates') ?>">
                    <i class="fa fa-plus-circle item"></i><span
                        class="text item"> <?= lang('tax_rates'); ?></span>
                </a>
            </li>
            <li id="system_settings_tax_rates_attr" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/tax_rates_attr') ?>">
                    <i class="fa fa-plus-circle item"></i><span
                        class="text item"> <?= lang('tax_rates'); ?>
                        Attributes </span>
                </a>
            </li>
            <?php if ($Owner) { ?>
                <li id="system_settings_warehouses" class="list-item">
                    <a class="submenu item" href="<?= site_url('system_settings/warehouses') ?>">
                        <i class="fa fa-building-o item"></i><span
                            class="text item"> <?= lang('warehouses'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_email_templates" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/email_templates') ?>">
                    <i class="fa fa-envelope item"></i><span
                        class="text item"> <?= lang('email_templates'); ?></span>
                </a>
            </li>
            <?php if ($Owner) { ?>
                <li id="system_settings_user_groups" class="list-item">
                    <a class="submenu item" href="<?= site_url('system_settings/user_groups') ?>">
                        <i class="fa fa-key item"></i><span
                            class="text item"> <?= lang('group_permissions'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_backups" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/backups') ?>">
                    <i class="fa fa-database item"></i><span
                        class="text item"> <?= lang('backups'); ?></span>
                </a>
            </li>

            <li id="coupon" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/discount_coupon_list'); ?>">
                    <i class="fa fa-gift item" aria-hidden="true"></i>
                    <span class="text item"> Discount Coupon </span>
                </a>
            </li>



            <li id="system_settings_offer_list" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/offer_list'); ?>">
                    <i class="fa fa-gift item" aria-hidden="true"></i>
                    <span class="text item"> Offer </span>
                </a>
            </li>
            <li id="system_settings_offercategory" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/offercategory'); ?>">
                    <i class="fa fa-gift item" aria-hidden="true"></i>
                    <span class="text item"> Offer Category
                </a>
            </li>
            <li id="system_settings_sms_configs" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/sms_configs'); ?>">
                    <i class="fa fa-send item" aria-hidden="true"></i>
                    <span class="text item"> SMS Config
                </a>
            </li>
            <li id="system_settings_printers" class="list-item">
                <a class="submenu item" href="<?= site_url('system_settings/printers'); ?>">
                    <i class="fa fa-print item"></i>
                    <span class="text item"> Manage Printers Option</span>
                </a>
            </li>
            <li id="printers_wifi" style="display:none" class="list-item">
                <a class="submenu item" href="javascript:window.MyHandler.OpenWifiPrinterDialog()">
                    <i class="fa fa-wifi item"></i>
                    <span class="text item"> Wifi Printer Setting</span>
                </a>
            </li>
        </ul>
    </li>
<?php } ?>

<li class="mm_reports mm_reports_new main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-pie-chart"></i>
        <span class="text"> <?= lang('reports'); ?> </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <li id="Overview chart" class="list-item">
            <a class="submenu item" href="<?= site_url('reports') ?>">
                <i class="fa fa-user item"></i>
                <span class="text item"><?= lang('Overview chart'); ?></span>
            </a>
        </li>
        <li id="attendance_report" class="list-item">
            <a class="submenu item" href="<?= site_url('attendance/report'); ?>">
                <i class="fa fa-file-text-o item"></i>
                <span class="text item"> Attendance Report </span>
            </a>
        </li>
        <li id="Accounts" class="mm_Accounts inner-list-item <?= $active_dropdown == 'Accounts' ? 'active' : ''; ?>">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Accounts'); ?></span>
                <span class="chevron <?= $active_dropdown == 'Accounts' ? 'open' : 'closed'; ?>"></span>
            </a>
            <ul>
                <li id="Cash_Transaction_Report" class="inner-inner-list-item ">
                    <a class="submenu item" href="<?= site_url('reports/cash_transaction') ?>">
                        <i class="fa fa-file-text item"></i>
                        <span class="text item"><?= lang('Cash Transaction Report'); ?></span>
                    </a>
                </li>
                <li id="HSN_Report" class="inner-inner-list-item <?= $active_item == 'HSN_Report' ? 'active' : ''; ?>">
                    <a class="submenu item" href="<?= site_url('reports/hsncode_reports_new') ?>">
                        <i class="fa fa-bar-chart item" aria-hidden="true"></i>
                        <span class="text item"> <?= lang('HSN Report'); ?> </span>
                    </a>
                </li>
                <li id="Payment_Chart_Details" class="inner-inner-list-item <?= $active_item == 'Payment_Chart_Details' ? 'active' : ''; ?>">
                    <a class="submenu item" href="<?= site_url('reports/payment_chart_details') ?>">
                        <i class="fa fa-pie-chart item" aria-hidden="true"></i>
                        <span class="text item"> <?= lang('Payment Chart Details'); ?> </span>
                    </a>
                </li>
                <li id="Payment_Summary" class="inner-inner-list-item ">
                    <a class="submenu item" href="<?= site_url('reports/paymentssummary') ?>">
                        <i class="fa fa-file-text-o item"></i>
                        <span class="text item"><?= lang('Payment Summary'); ?></span>
                    </a>
                </li>
                <li id="Payments_Report" class="inner-inner-list-item <?= $active_item == 'Payments_Report' ? 'active' : ''; ?>">
                    <a class="submenu item" href="<?= site_url('reports/payments') ?>">
                        <i class="fa fa-money item" aria-hidden="true"></i>
                        <span class="text item"> <?= lang('Payments Report'); ?> </span>
                    </a>
                </li>
                <li id="Profit_Loss" class="inner-inner-list-item ">
                    <a class="submenu item" href="<?= site_url('reports/profit_loss') ?>">
                        <i class="fa fa-line-chart item"></i>
                        <span class="text item"><?= lang('Profit & Loss'); ?></span>
                    </a>
                </li>
                <li id="Simple_Tax_GST_Reports" class="inner-inner-list-item ">
                    <a class="submenu item" href="<?= site_url('reports_new/gst_reports_new') ?>">
                        <i class="fa fa-file-text-o item"></i>
                        <span class="text item"><?= lang('Simple Tax / GST Reports'); ?></span>
                    </a>
                </li>
                <li id="Tax_Report" class="inner-inner-list-item ">
                    <a class="submenu item" href="<?= site_url('reports/taxreports_new') ?>">
                        <i class="fa fa-file-text item"></i>
                        <span class="text item"><?= lang('Tax Report'); ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <li id="BI_Reports" class="mm_BI_Reports inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bar-chart"></i>
                <span class="text"><?= lang('BI Reports'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <?php
                $CI = &get_instance();
                $bi_table = 'bi_reports';
                $bi_items = [];
                if ($CI->db->table_exists($bi_table)) {
                    $CI->db->select('id, name, manu_name, links');
                    $CI->db->from($bi_table);
                    $CI->db->where('is_active', 1);
                    $CI->db->order_by('id', 'ASC');
                    $bi_items = $CI->db->get()->result();
                }
                ?>
                <?php if (!empty($bi_items)) { ?>
                     
                    <?php foreach ($bi_items as $item) { 
                      
                        $manu = isset($item->manu_name) ? $item->manu_name : '';
                        $name = isset($item->name) ? $item->name : '';
                        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', trim($manu)));
                        $href = site_url('reports/bi/' . $slug);
                        $id_attr = strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', $name));
                    ?>
                        <li id="bi_<?= $id_attr ?>" class="inner-inner-list-item">
                            <a class="submenu item" href="<?= $href; ?>">
                                <i class="fa fa-file-text-o item"></i>
                                <span class="text item"><?= htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </li>

        <script>
        // Function to set active state for BI report menu items
        document.addEventListener('DOMContentLoaded', function() {
            // Set active state based on current URL
            function setActiveReport() {
                const currentUrl = window.location.href;
                let isAnyActive = false;
                document.querySelectorAll('.mm_BI_Reports .inner-inner-list-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelectorAll('.mm_BI_Reports .inner-inner-list-item > a').forEach(a => {
                    const href = a.getAttribute('href');
                    if (href && currentUrl.indexOf(href) !== -1) {
                        a.parentElement.classList.add('active');
                        isAnyActive = true;
                    }
                });
                if (isAnyActive) {
                const biReportsMenu = document.querySelector('.mm_BI_Reports');
                if (biReportsMenu) {
                    const chevron = biReportsMenu.querySelector('.chevron');
                    if (chevron) {
                        chevron.classList.remove('closed');
                        chevron.classList.add('open');
                    }
                    const submenu = biReportsMenu.querySelector('ul');
                    if (submenu) {
                        submenu.style.display = 'block';
                    }
                }
                const reportsMenu = document.querySelector('.mm_reports_new.main-item');
                if (reportsMenu) {
                    reportsMenu.classList.add('active');
                    const chevron = reportsMenu.querySelector('.chevron');
                    if (chevron) {
                        chevron.classList.remove('closed');
                        chevron.classList.add('open');
                    }
                    }
                }
            }
            setActiveReport();
        });
        </script>

           <style>
            #biReportModal .modal-content {
                height: 100%;
                border: 0;
                border-radius: 0;
            }
            #biReportModal .modal-body {
                padding: 0;
                height: calc(100% - 60px) !important;
            }
            </style> 

        <li id="Challan" class="mm_Challan inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Challan'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="Challan_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/challans') ?>">
                        <i class="fa fa-file item"></i>
                        <span class="text item"><?= lang('Challan Report'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <li id="Customer" class="mm_Customer inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Customer') ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="Customers Report" class="Customers Report inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/customers') ?>">
                        <i class="fa fa-file item"></i>
                        <span class="text item"><?= lang('Customers Report'); ?></span>
                    </a>
                </li>

                <li id="Deposit_Recharge_Report" class="Deposit_Recharge_Report inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/deposit') ?>">
                        <i class="fa fa-credit-card item"></i>
                        <span class="text item"><?= lang('Deposit Recharge Report'); ?></span>
                    </a>
                </li>
                <li id="Register_Report" class="Register_Report inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/register') ?>">
                        <i class="fa fa-book item"></i>
                        <span class="text item"><?= lang('Register Report'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <li id="Ledger" class="mm_Ledger inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Ledger'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="reports_ledger">
                    <a href="<?= site_url('reports/customer_ledger_v1') ?>">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span class="text"> <?= lang('Customer_Ledger'); ?> </span>
                    </a>
                </li>
                <li id="Deposit_Ledgers" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/customerDepositLedger') ?>">
                        <i class="fa fa-credit-card item"></i>
                        <span class="text item"><?= lang('Deposit_Ledgers'); ?></span>
                    </a>
                </li>
                <li id="reports_products_ledgers" class="inner-inner-list-item">
                    <a href="<?= site_url('reports/products_ledgers') ?>" class="item">
                        <i class="fa fa-barcode item"></i><span class="text item"> <?= lang('Products_Ledgers'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <li id="Logs" class="mm_Logs inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Logs'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="User_Log_Action" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/user_log_action') ?>">
                        <i class="fa fa-book item"></i>
                        <span class="text item"><?= lang('User Log Action'); ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <li id="Products" class="mm_Products inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Products'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="Adjustment_Reports" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/adjustments') ?>">
                        <i class="fa fa-adjust item"></i>
                        <span class="text item"><?= lang('Adjustment Reports'); ?></span>
                    </a>
                </li>
                <li id="Brands_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/brands') ?>">
                        <i class="fa fa-tags item"></i>
                        <span class="text item"><?= lang('Brands Report'); ?></span>
                    </a>
                </li>
                <li id="Categories_Brand_Chart_Details" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/categories_brand_chart_details') ?>">
                        <i class="fa fa-pie-chart item"></i>
                        <span class="text item"><?= lang('Categories and Brand Chart Details'); ?></span>
                    </a>
                </li>
                <li id="Categories_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/categories_report') ?>">
                        <i class="fa fa-list item"></i>
                        <span class="text item"><?= lang('Categories Report'); ?></span>
                    </a>
                </li>
                <li id="Product_Expiry_Alerts" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/expiry_alerts') ?>">
                        <i class="fa fa-exclamation-triangle item"></i>
                        <span class="text item"><?= lang('Product Expiry Alerts'); ?></span>
                    </a>
                </li>
                <li id="Product_Quantity_Alerts" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/quantity_alerts') ?>">
                        <i class="fa fa-bell item"></i>
                        <span class="text item"><?= lang('Product Quantity Alerts'); ?></span>
                    </a>
                </li>
                <li id="Product_Variant_Stock_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/product_varient_stock_report') ?>">
                        <i class="fa fa-archive item"></i>
                        <span class="text item"><?= lang('Product Variant Stock Report'); ?></span>
                    </a>
                </li>
                <li id="Products_Combo_Items" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/products_combo_items') ?>">
                        <i class="fa fa-cubes item"></i>
                        <span class="text item"><?= lang('Products Combo Items'); ?></span>
                    </a>
                </li>
                <li id="Products_Costing" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/products_costing') ?>">
                        <i class="fa fa-calculator item"></i>
                        <span class="text item"><?= lang('Products Costing'); ?></span>
                    </a>
                </li>
                <li id="Products_Profit_Loss" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/products_profitloss') ?>">
                        <i class="fa fa-line-chart item"></i>
                        <span class="text item"><?= lang('Products Profit and Loss'); ?></span>
                    </a>
                </li>
                <li id="Products_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/products') ?>">
                        <i class="fa fa-file-text item"></i>
                        <span class="text item"><?= lang('Products Report'); ?></span>
                    </a>
                </li>
                <li id="Products_Transaction_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/products_transactions') ?>">
                        <i class="fa fa-exchange item"></i>
                        <span class="text item"><?= lang('Products Transaction Report'); ?></span>
                    </a>
                </li>
                <li id="Warehouse_Stock_Chart" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/warehouse_stock') ?>">
                        <i class="fa fa-bar-chart item"></i>
                        <span class="text item"><?= lang('Warehouse Stock Chart'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <li id="Purchase" class="mm_Purchase inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Purchase'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="Daily_Purchases" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports_new/daily_purchases') ?>">
                        <i class="fa fa-calendar item"></i>
                        <span class="text item"><?= lang('Daily Purchases'); ?></span>
                    </a>
                </li>
                <li id="Due_Purchases_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/purchases_due') ?>">
                        <i class="fa fa-clock-o item"></i>
                        <span class="text item"><?= lang('Due Purchases Report'); ?></span>
                    </a>
                </li>
                <li id="Expenses_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/expenses') ?>">
                        <i class="fa fa-money item"></i>
                        <span class="text item"><?= lang('Expenses Report'); ?></span>
                    </a>
                </li>
                <li id="Monthly_Purchases" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports_new/monthly_purchases') ?>">
                        <i class="fa fa-calendar-o item"></i>
                        <span class="text item"><?= lang('Monthly Purchases'); ?></span>
                    </a>
                </li>
                <li id="Product_Variant_Purchase_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/product_varient_purchase_report') ?>">
                        <i class="fa fa-archive item"></i>
                        <span class="text item"><?= lang('Product Variant Purchase Report'); ?></span>
                    </a>
                </li>
                <li id="Purchases_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/purchases') ?>">
                        <i class="fa fa-file-text item"></i>
                        <span class="text item"><?= lang('Purchases Report'); ?></span>
                    </a>
                </li>
                <li id="Purchases_Report_GST" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports_new/purchases_gst_report') ?>">
                        <i class="fa fa-shopping-cart item"></i>
                        <span class="text item"><?= lang('Purchases Report GST'); ?></span>
                    </a>
                </li>
                 <li id="Suppliers_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/suppliers') ?>">
                        <i class="fa fa-truck item"></i>
                        <span class="text item"><?= lang('Suppliers Report'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <li id="Sales" class="mm_Sales inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Sales'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="Best_Seller" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/best_sellers') ?>">
                        <i class="fa fa-star item"></i>
                        <span class="text item"><?= lang('Best Seller'); ?></span>
                    </a>
                </li>
                <li id="Customerwise_Sale_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/get_customer_wise_sales') ?>">
                        <i class="fa fa-users item"></i>
                        <span class="text item"><?= lang('Customerwise Sale Report'); ?></span>
                    </a>
                </li>
                <li id="Daily_Sales" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports_new/daily_sales') ?>">
                        <i class="fa fa-calendar item"></i>
                        <span class="text item"><?= lang('Daily Sales'); ?></span>
                    </a>
                </li>
                <li id="Due_Sales_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/sales_due') ?>">
                        <i class="fa fa-clock-o item"></i>
                        <span class="text item"><?= lang('Due Sales Report'); ?></span>
                    </a>
                </li>
                <li id="Monthly_Sales" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports_new/monthly_sales') ?>">
                        <i class="fa fa-calendar-o item"></i>
                        <span class="text item"><?= lang('Monthly Sales'); ?></span>
                    </a>
                </li>
                <li id="Productwise_Sale_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/Product_wise_Sale_Report') ?>">
                        <i class="fa fa-cogs item"></i>
                        <span class="text item"><?= lang('Productwise Sale Report'); ?></span>
                    </a>
                </li>
                <li id="Sale_Purchase_Chart_Details" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/sale_purchase_chart_details') ?>">
                        <i class="fa fa-bar-chart item"></i>
                        <span class="text item"><?= lang('Sale Purchase Chart Details'); ?></span>
                    </a>
                </li>
                <li id="Sale_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/sales') ?>">
                        <i class="fa fa-file item"></i>
                        <span class="text item"><?= lang('Sale GST Report'); ?></span>
                    </a>
                </li>
                <li id="Sales_Extended_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports_new/sales_extended_report') ?>">
                        <i class="fa fa-file-text item"></i>
                        <span class="text item"><?= lang('Sales Extended Report'); ?></span>
                    </a>
                </li>
                <li id="Sales_Person_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/sales_person_report') ?>">
                        <i class="fa fa-user item"></i>
                        <span class="text item"><?= lang('Sales Person Report'); ?></span>
                    </a>
                </li>
                <li id="reports_sales_gst_report" class="inner-inner-list-item" style="display:none">
                    <a class="submenu item" href="<?= site_url('reports/sales_gst_report') ?>">
                        <i class="fa fa-line-chart item"></i><span
                            class="text"> <?= lang('sales_report'); ?> GST </span>
                    </a>
                </li>
                <li id="Sale_Report_VAT_UAE">
                    <a class="submenu" href="<?= site_url('reports_new/sales_vat_report_uae') ?>">
                        <i class="fa fa-file"></i>
                        <span class="text"><?= lang('Sales Transaction Report'); ?></span>
                    </a>
                </li>
                <li id="Staff_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/users') ?>">
                        <i class="fa fa-users item"></i>
                        <span class="text item"><?= lang('Staff Report'); ?></span>
                    </a>
                </li>
                <li id="Term_Wise_Sales_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/term_wise_sale_report') ?>">
                        <i class="fa fa-table item"></i>
                        <span class="text item"><?= lang('Term Wise Sales Report'); ?></span>
                    </a>
                </li>
                <li id="Warehouse_Sales_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/warehouse_sales') ?>">
                        <i class="fa fa-warehouse item"></i>
                        <span class="text item"><?= lang('Warehouse Sales Report'); ?></span>
                    </a>
                </li>
                <?php if ($Owner || $Admin || $GP['reports-product_variant_sale_report']) { ?>
                <li id="variant_sale_report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/product_varient_sale_report') ?>">
                        <i class="fa fa-shopping-cart item"></i>
                        <span class="text item"><?= lang('Product_Variant_sale_report'); ?></span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <li id="sales_dash">
            <a href="<?= site_url('reports/sales_dash') ?>">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('sales_dash'); ?></span>
            </a>
        </li>
        <li id="Transfers" class="mm_Transfers inner-list-item">
            <a class="dropmenu" href="#">
                <i class="fa fa-bars"></i>
                <span class="text"><?= lang('Transfers'); ?></span>
                <span class="chevron closed"></span>
            </a>
            <ul>
                <li id="Transfer_Request_Report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/transfer_request') ?>">
                        <i class="fa fa-file-text item"></i>
                        <span class="text item"><?= lang('Transfer Request Report'); ?></span>
                    </a>
                </li>
                <li id="Transfer_Reports" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/transferReport') ?>">
                        <i class="fa fa-exchange item"></i>
                        <span class="text item"><?= lang('Transfer Reports'); ?></span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</li>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const url = window.location.pathname.split('/');
        if (url[url.length - 1] === "welcome" || url[url.length - 1] === "") {
            sessionStorage.setItem('activeTab', 'dashboard');
        }

        const mainItems = document.querySelectorAll('.main-item');
        Array.from(mainItems).forEach((mainItem) => {
            mainItem.addEventListener("click", (event) => {
                let val;
                if (event.target.closest('.list-item')) {
                    sessionStorage.setItem('activeTab', event.target.closest('.list-item').id);
                }
                else if (event.target.closest('.inner-inner-list-item')) { 
                    sessionStorage.setItem('activeTab', event.target.closest('.inner-inner-list-item').id);
                }
                else{
                    sessionStorage.setItem('activeTab', mainItem.id);
                } 
            });
        })

        setTimeout(() => {
            Array.from(document.querySelectorAll('.main-item')).forEach(mainItem => {
                if (mainItem.children[1]) {
                    mainItem.children[1].style.display = "none";
                }
                mainItem.classList.remove('active');
            });

            Array.from(document.querySelectorAll('.chevron')).forEach(chev => {
                chev.classList.remove('opened');
                chev.classList.add('closed');
            });
        }, 100)

        setTimeout(() => {
            let activeTab = sessionStorage.getItem('activeTab');
            <?php if (isset($m) && isset($v)) { ?>
                // Force sync with server state to override stale sessionStorage from previous clicks
                activeTab = '<?= $m ?>_<?= $v ?>';
                // Remap CMS sub-pages so the CMS Admin Panel sidebar item stays active
                const cmsSubPages = [
                    'webshop_settings_manage_products',
                    'webshop_settings_cms_pages',
                    'webshop_settings_storefront_identity',
                    'entity_mapping_index',
                    'products_manage_price',
                    'cms_admin_panel_index'
                ];
                const cmsAdminTabMap = {
                    'cms_admin_dashboard': 'cms_admin_panel_index',
                    'cms_admin_catalog': 'webshop_settings_manage_products',
                    'cms_admin_pages': 'webshop_settings_cms_pages',
                    'cms_admin_storefront': 'webshop_settings_storefront_identity',
                    'cms_admin_entity_tags': 'entity_mapping_index',
                    'cms_admin_prices': 'products_manage_price'
                };
                <?php if (!empty($m) && $m === 'cms_admin') {
                    $cms_seg = $this->uri->segment(2);
                    $cms_erp_tab = 'cms_admin_panel_index';
                    $cms_seg_map = array(
                        'catalog'     => 'webshop_settings_manage_products',
                        'pages'       => 'webshop_settings_cms_pages',
                        'storefront'  => 'webshop_settings_storefront_identity',
                        'entity_tags' => 'entity_mapping_index',
                        'prices'      => 'products_manage_price',
                        'dashboard'   => 'cms_admin_panel_index',
                    );
                    if (isset($cms_seg_map[$cms_seg])) {
                        $cms_erp_tab = $cms_seg_map[$cms_seg];
                    }
                ?>
                activeTab = '<?= $cms_erp_tab ?>';
                <?php } else { ?>
                if (cmsSubPages.indexOf(activeTab) !== -1) {
                    /* keep legacy ecommerce submenu item active */
                } else if (cmsAdminTabMap[activeTab]) {
                    activeTab = cmsAdminTabMap[activeTab];
                }
                <?php } ?>
                sessionStorage.setItem('activeTab', activeTab);
            <?php } ?>
            
            let activeElement = document.getElementById(activeTab);

            // Fallback: if ID lookup fails (e.g. activeTab='reports_challans' but element id='Challan_Report'),
            // find the sidebar item whose anchor href matches the current page URL
            if (!activeElement) {
                var _normUrl = function(u) {
                    if (!u) return '';
                    u = u.replace(/https?:\/\/[^\/]+/i, '');
                    u = u.replace(/\/index\.php/i, '');
                    u = u.split('?')[0].split('#')[0];
                    return u.replace(/\/+$/, '').toLowerCase();
                };
                var _curPath = _normUrl(window.location.href);
                var _links = document.querySelectorAll('#sidebar-left a[href]');
                for (var _i = 0; _i < _links.length; _i++) {
                    var _href = _links[_i].getAttribute('href');
                    if (!_href || _href === '#' || _href.length < 5) continue;
                    var _linkPath = _normUrl(_href);
                    if (_linkPath.length > 0 && _curPath === _linkPath) {
                        activeElement = _links[_i].closest('li');
                        break;
                    }
                }
            }

            // Null guard: if no matching sidebar element found, do nothing
            if (activeElement) {
                if (activeElement.classList.contains('inner-inner-list-item')) {
                    activeElement.closest('.inner-list-item').children[1].style.display = "block";
                    activeElement.closest('.main-item').classList.add('active');
                    activeElement.closest('.main-item').children[1].style.display = "block";
                    activeElement.closest('.main-item').children[0].children[2].classList.remove('closed');
                    activeElement.closest('.main-item').children[0].children[2].classList.add('opened');
                } else if (activeElement.closest('.main-item') && activeElement.closest('.main-item').children[1]) {
                    if (window.getComputedStyle(activeElement.closest('.main-item').children[1]).display === "none") {
                        activeElement.closest('.main-item').classList.add('active');
                        activeElement.closest('.main-item').children[1].style.display = "block";
                        if (activeElement.closest('.main-item').children[0].children[2]) {
                            activeElement.closest('.main-item').children[0].children[2].classList.remove('closed');
                            activeElement.closest('.main-item').children[0].children[2].classList.add('opened');
                        }
                    }
                } else if (activeElement.closest('.main-item')) {
                    activeElement.closest('.main-item').classList.add('active');
                }
                activeElement.classList.add('active');

                setTimeout(() => {
                    Array.from(document.querySelectorAll('.list-item')).forEach((item) => {
                        if (item.id != activeTab) {
                            item.classList.remove('active');
                        };
                    });

                    const activeMain = activeElement.closest('.main-item');
                    if (activeMain && activeMain.children[0] && activeMain.children[0].children[1]) {
                        const activeText = activeMain.children[0].children[1].textContent;
                        Array.from(document.querySelectorAll('.main-item')).forEach((mainItem) => {
                            if (mainItem.children[0] && mainItem.children[0].children[1]) {
                                if (mainItem.children[0].children[1].textContent !== activeText) {
                                    mainItem.classList.remove('active');
                                    if (mainItem.children[1]) {
                                        mainItem.children[1].style.display = "none";
                                    }
                                    if (mainItem.children[0].children[2]) {
                                        mainItem.children[0].children[2].classList.remove('opened');
                                        mainItem.children[0].children[2].classList.add('closed');
                                    }
                                }
                             }
                        });
                    }
                }, 100)
            }
        }, 600)
    })
</script>

<style>
    .list-item a {
        background-color: #929292;
    }
</style>