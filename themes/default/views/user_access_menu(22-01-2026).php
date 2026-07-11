<?php if ($Settings->active_omnichannel) { ?>
    <!-- Urbanpiper -->
        <li class="mm_urban_piper <?= $is_urbanpiper_active ? 'active' : '' ?>">
            <a class="dropmenu" href="#">
                <i class="fa fa-globe"></i>
                <span class="text"> <?= lang('Omnichannel'); ?> </span>
                <span class="chevron <?= $is_urbanpiper_active ? 'opened' : 'closed' ?>"></span>
            </a>
            <ul style="<?= $is_urbanpiper_active ? 'display: block;' : '' ?>">
                <?php // Working Orders - no UrbanPiper permission required ?>
                    <li id="urban_piper_index" class="<?= ($segment1 == 'Omnichannel') ? 'active' : '' ?>">
                        <a href="<?= site_url('Omnichannel') ?>">
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
                <?php if ($GP['up_orders_3p']) { ?>
                    <li id="urbanpiper_category1" class="<?= ($segment1 == 'up_orders') ? 'active' : '' ?>">
                        <a href="<?= site_url('Omnichannel/orders_inactive') ?>">
                            <i class="fa fa-list" aria-hidden="true"></i>
                            <span class="text"> Up Orders </span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($GP['orders-eshop_order']) { ?>
                    <li id="urbanpiper_category2" class="<?= ($segment1 == 'webshop_orders') ? 'active' : '' ?>">
                        <a href="<?= site_url('orders/eshop_order') ?>">
                            <i class="fa fa-list" aria-hidden="true"></i>
                            <span class="text"> Webshop Orders </span>
                        </a>
                    </li>
                <?php } ?>
                    </ul>
                </li>
            </ul>
        </li>
    <?php } ?>

<?php if ($Settings->active_urbanpiper) { ?>
    <?php if (
        $GP['urbanpiper_manage_order'] || 
        $GP['urbanpiper_settings'] || 
        $GP['urbanpiper_manage_stores'] || 
        $GP['urbanpiper_manage_catalogue']
    ) { ?>
        <!-- Urbanpiper Specific -->
        <li class="mm_urban_piper <?= $is_urbanpiper_active ? 'active' : '' ?>">
            <a class="dropmenu" href="#">
                <i class="fa fa-magnet"></i>
                <span class="text"> Urbanpiper</span>
                <span class="chevron <?= $is_urbanpiper_active ? 'opened' : 'closed' ?>"></span>
            </a>
            <ul style="<?= $is_urbanpiper_active ? 'display: block;' : '' ?>">
                <?php if ($GP['urbanpiper_manage_order']) { ?>
                    <li id="urban_piper" class="<?= ($segment1 == 'urban_piper' && $segment2 == '') ? 'active' : '' ?>">
                        <a class="submenu" href="<?= site_url('urban_piper') ?>">
                            <i class="fa fa-list" aria-hidden="true"></i>
                            <span class="text"> Urbanpiper Orders </span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($GP['urbanpiper_settings']) { ?>
                    <li id="urban_piper_settings" class="<?= ($segment1 == 'urban_piper' && $segment2 == 'settings') ? 'active' : '' ?>">
                        <a href="<?= site_url('urban_piper/settings') ?>">
                            <i class="fa fa-cogs" aria-hidden="true"></i>
                            <span class="text"> Urbanpiper Settings </span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($GP['urbanpiper_manage_stores']) { ?>
                    <li id="urban_piper_store_info" class="<?= ($segment1 == 'urban_piper' && $segment2 == 'store_info') ? 'active' : '' ?>">
                        <a href="<?= site_url('urban_piper/store_info') ?>">
                            <i class="fa fa-list" aria-hidden="true"></i>
                            <span class="text"> Manage Stores </span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($GP['urbanpiper_manage_catalogue']) { ?>
                    <li id="urban_piper_product_platform" class="<?= ($segment1 == 'urban_piper' && $segment2 == 'product_platform') ? 'active' : '' ?>">
                        <a href="<?= site_url('urban_piper/product_platform') ?>">
                            <i class="fa fa-archive" aria-hidden="true"></i>
                            <span class="text"> Manage Catalogue </span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>
        <!-- Urbanpiper Specific -->
    <?php } ?>
<?php } ?>

<?php if ($GP['products-index'] || $GP['products-add'] || $GP['products-barcode'] || $GP['products-adjustments'] || $GP['products-stock_count'] || $GP['products-import'] || $GP['products-batches'] || $GP['raw_materials'] ) { ?>
    <li class="mm_products main-item">
        
        <a class="dropmenu" href="#">
            <i class="fa fa-barcode"></i>
            <span class="text"> <?= lang('products'); ?>
            </span> <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($GP['products-add']) { ?>
                <li id="products_add" class="list-item">
                    <a class="submenu" href="<?= site_url('products/add'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_product'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="products_index" class="list-item">
                <a class="submenu" href="<?= site_url('products'); ?>">
                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('list_products'); ?></span>
                </a>
            </li>
            <?php if ($GP['raw_materials']) { ?>
                <li id="raw_materials" class="list-item">
                    <a href="<?= site_url('products/rawMaterials') ?>">
                        <i class="fa fa-database"></i><span class="text"> <?= lang('Raw_Materials'); ?></span> 
                    </a>
                </li>
                <!-- End Batch No -->
            <?php } ?>
            <?php if ($Settings->pos_type == 'restaurant' && $pos_settings->combo_add_pos) { ?>
                <li id="products_index1" class="list-item">
                    <a class="submenu" href="<?= site_url('products/poscombo'); ?>">
                        <i class="fa fa-barcode"></i>
                        <span class="text"> <?= lang('List_POS Combo Product'); ?></span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($GP['products-barcode']) { ?>
                <li id="products_sheet" class="list-item">
                    <a class="submenu" href="<?= site_url('products/print_barcodes'); ?>">
                        <i class="fa fa-tags"></i><span class="text"> <?= lang('print_barcode_label'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['products-import']) { ?>
                <li id="products_import_csv" class="list-item">
                    <a class="submenu" href="<?= site_url('products/import_csv'); ?>">
                        <i class="fa fa-file-text"></i><span class="text"> <?= lang('import_products'); ?></span>
                    </a>
                </li>
            <?php } ?>
        
          

            <!-- <?php if ($GP['todays_special']) { ?>
        <li id="todays_special">
            <a href="<?= site_url('Todays_Special') ?>">
                <i class="fa fa-database"></i><span class="text"> <?= lang('Todays Special'); ?></span> 
            </a>
        </li>
        
        <?php } ?> -->
        <!-- <li id="business_hour" class="mm_business_hour">
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
</li> -->


        </ul>
    </li>
<?php } ?>

<!-- <?php if ($GP['orders-eshop_order'] || $GP['orders-order_items'] || $GP['orders-order_items_stocks']) {  ?>
    <li class="mm_orders main-item <?= strtolower($this->router->fetch_method()) == 'settings' ? '' : 'mm_pos' ?>">
        <a class="dropmenu" href="#">
            <i class="fa fa-bar-chart"></i>
            <span class="text"> <?= lang('orders'); ?>
            </span> <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($GP['orders-eshop_order'] && $Settings->active_eshop) { ?>
                <li id="orders_eshop_order" class="list-item">
                    <a class="submenu" href="<?= site_url('orders/eshop_order'); ?>">
                        <i class="fa fa-list-ol"></i>
                        <span class="text"> Eshop Orders</span>
                    </a>
                </li>
            <?php }
            if ($GP['orders-order_items']) { ?>
                <li id="orders_order_items" class="list-item">
                    <a class="submenu" href="<?= site_url('orders/order_items'); ?>">
                        <i class="fa fa-list-ol"></i>
                        <span class="text"> <?= lang('Order_Items_List'); ?></span>
                    </a>
                </li>
            <?php }
            if ($GP['orders-order_items_stocks']) { ?>
                <?php if (in_array($Settings->pos_type, ['fruits_vegetables', 'fruits_vegetabl', 'grocerylite', 'grocery'])) { ?>
                    <li id="orders_order_items_stocks" class="list-item">
                        <a class="submenu" href="<?= site_url('orders/order_items_stocks'); ?>">
                            <i class="fa fa-list-ol"></i>
                            <span class="text"> <?= lang('Order Products Quantity'); ?></span>
                        </a>
                    </li>
            <?php }
            } ?>
        </ul>
    </li>
<?php } ?> -->
<li class="mm_inventory main-item">
    <a class="dropmenu" href="#">
        <i class="fa fa-dropbox"></i>
        <span class="text"> <?= lang('Inventory'); ?>
        </span> <span class="chevron closed"></span>
    </a>
    <ul>
        <?php if ($GP['products-adjustments']) { ?>
                
                <li id="products_add_adjustment" class="list-item">
                    <a class="submenu" href="<?= site_url('products/add_adjustment'); ?>">
                        <i class="fa fa-filter"></i><span class="text"> <?= lang('add_adjustment'); ?></span>
                    </a>
                </li>
                <li id="products_quantity_adjustments" class="list-item">
                    <a class="submenu" href="<?= site_url('products/quantity_adjustments'); ?>">
                        <i class="fa fa-filter"></i><span class="text"> <?= lang('quantity_adjustments'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['products-stock_count']) { ?>
                <li id="products_stock_counts" class="list-item">
                    <a class="submenu" href="<?= site_url('products/stock_counts'); ?>">
                        <i class="fa fa-list-ol"></i>
                        <span class="text"> <?= lang('stock_counts'); ?></span>
                    </a>
                </li>
                <li id="products_count_stock" class="list-item">
                    <a class="submenu" href="<?= site_url('products/count_stock'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('count_stock'); ?></span>
                    </a>
                </li>
                <li id="reports_checkstock" class="list-item">
                    <a class="submenu" href="<?= site_url('CheckStock') ?>">
                        <i class="fa fa-line-chart" aria-hidden="true">
                            </i><span class="text"> <?= lang('Stock Check'); ?></span> 
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['products-batches']) { ?>
                <li id="products_batches" class="list-item">
                    <a href="<?= site_url('products/batches') ?>">
                        <i class="fa fa-database"></i><span class="text"> <?= lang('Manage Batches'); ?></span> 
                    </a>
                </li>
                <!-- End Batch No -->
            <?php } ?>
    </ul>
</li>
<?php if ($GP['sales-index'] || $GP['sales-add'] || $GP['sales-deliveries'] || $GP['sales-gift_cards'] || $GP['eshop_sales-sales'] || $GP['offline-sales']) { ?>
    <li class="mm_sales main-item <?= strtolower($this->router->fetch_method()) == 'settings' ? '' : 'mm_pos' ?>">
        <a class="dropmenu" href="#">
            <i class="fa fa-heart"></i>
            <span class="text"> <?= lang('sales'); ?>
            </span> <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($GP['sales-index']) { ?>
                <li id="sales_index" class="list-item">
                    <a class="submenu" href="<?= site_url('sales'); ?>">
                        <i class="fa fa-heart"></i><span class="text"> <?= lang('list_sales'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if (POS && $GP['pos-index']) { ?>
                <li id="pos_sales" class="list-item">
                    <a class="submenu" href="<?= site_url('pos/sales'); ?>">
                        <i class="fa fa-heart"></i><span class="text"> <?= lang('pos_sales'); ?></span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($GP['eshop_sales-sales'] && $Settings->active_eshop) { ?>
                <li id="eshop_sales_sales" class="list-item">
                    <a class="submenu" href="<?= site_url('eshop_sales/sales'); ?>">
                        <i class="fa fa-heart"></i>
                        <span class="text"> Eshop Sales</span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($GP['offline-sales'] && $Settings->active_offline) {  ?>
                <li id="offline_sales" class="list-item">
                    <a class="submenu" href="<?= site_url('offline/sales'); ?>">
                        <i class="fa fa-heart"></i>
                        <span class="text"> Offline Sales <?= lang('offline_sales'); ?></span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($Settings->pos_type == 'restaurant' && $Settings->active_urbanpiper) { ?>
                <?php if ($GP['urban_piper_sales']) { ?>
                    <li class="urbanpiper_sales" class="list-item">
                        <a class="submenu" href="<?= site_url('urban_piper/sales'); ?>">
                            <i class="fa fa-plus-circle"></i>
                            <span class="text"> <?= lang('Urban Piper Sales'); ?></span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>

            <?php if ($GP['sales-add']) { ?>
                <li id="sales_add" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/add'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_sale'); ?></span>
                    </a>
                </li>
            <?php
            }
            if ($GP['sales-deliveries']) {
            ?>
                <li id="sales_deliveries" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/deliveries'); ?>">
                        <i class="fa fa-truck"></i><span class="text"> <?= lang('deliveries'); ?></span>
                    </a>
                </li>
            <?php
            }
            if ($GP['sales-gift_cards']) {
            ?>
                <li id="sales_gift_cards" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/gift_cards'); ?>">
                        <i class="fa fa-gift"></i><span class="text"> <?= lang('gift_cards'); ?></span>
                    </a>
                </li>
            <?php }
            if ($GP['sales_add_csv']) { ?>

                <li id="sales_sale_by_csv" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/sale_by_csv'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('add_sale_by_csv'); ?></span>
                    </a>
                </li>
            <?php }
            if ($GP['all_sale_lists']) { ?>
                <li id="sales_all_sale_lists" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/all_sale_lists'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('All_Sale_List'); ?> 
                    </a>
                </li>
            <?php }
            if ($GP['sales-challans']) { ?>
                <li id="sales_challans" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/challans'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('Challans List'); ?> </span>
                    </a>
                </li>
            <?php }
            if ($GP['sales-add_challans']) { ?>
                <li id="sales_add" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/add?sale_action=chalan'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('Add Challan'); ?></span>
                    </a>
                </li>
            <?php } ?>
             <?php if ($GP['customers-index']) { ?>
                <li id="customers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('customers'); ?>">
                        <i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                    </a>
                </li>
            <?php
            }
            if ($GP['customers-add']) {
            ?>
                <li id="customers_index1" class="list-item">
                    <a class="submenu" href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
<?php }
if ($GP['sales-add_challans'] || $GP['sales-challans']) {
?>
    <li class="mm_challans main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-file-text-o"></i>
            <span class="text"> <?= lang('Challans'); ?>
                   </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($GP['sales-challans']) { ?>
                <li id="sales_challans" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/challans'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('Challans List'); ?> </span>
                    </a>
                </li>
            <?php }
            if ($GP['sales-add_challans']) { ?>
                <li id="sales_challans" class="list-item">
                    <a class="submenu" href="<?= site_url('sales/add?sale_action=chalan'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('Add Challan'); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
<?php } ?>
<!-- for production Unit -->
<?php if ($Settings->enable_module_production_unit && $Settings->mother_kitchen == 1) { ?>
<?php if ($GP['Order_Dispatch'] || $GP['production_manager_dashboard']) { ?>

<li class="mm_production_unit main-item">
    <a class="dropmenu" href="">
        <i class="fa fa-industry"></i>
        <span class="text">
            <?= lang('Mother Kitchen'); ?>
        </span>
        <span class="chevron closed"></span>
    </a>

    <ul>
        <?php if ($Settings->enable_module_production_unit && $Settings->mother_kitchen == 1) { ?>

            <!-- Recipies (Mother Kitchen) -->
            <!-- <li id="Bill_of_material" class="list-item">
                <a class="submenu item" href="<?= site_url('Bill_of_material'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item">Recipies</span>
                </a>
            </li> -->
            <li id="Recipies" class="list-item">
                    <a class="submenu item" href="<?= site_url('Recipies'); ?>">
                        <i class="fa fa-home item"></i>
                        <span class="text item">Recipies</span>
                    </a>
                </li>
            <!-- Manager Dashboard -->
            <?php if ($GP['production_manager_dashboard']) { ?>
                <li id="production_units" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/manager_dashboard'); ?>">
                        <i class="fa fa-industry"></i>
                        <span class="text"><?= lang('MK Dashboard'); ?></span>
                    </a>
                </li>
            <?php } ?>

            <!-- Order Dispatch -->
            <?php if ($GP['Order_Dispatch']) { ?>

                <li id="production_units1" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit_New/working_orders'); ?>">
                        <i class="fa fa-industry"></i>
                        <span class="text"><?= lang('Working_Orders'); ?></span>
                    </a>
                </li>

                <li id="production_units2" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/Ready_To_Dispatch'); ?>">
                        <i class="fa fa-industry"></i>
                        <span class="text"><?= lang('Ready_To_Dispatch'); ?></span>
                    </a>
                </li>

                <!-- Production Dashboard -->
                <?php if ($GP['Production_Dashboard']) { ?>
            <li id="production_units3" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/production_dashboard'); ?>">
                        <i class="fa fa-industry"></i>
                        <span class="text"><?= lang('Production Dashboard'); ?></span>
                    </a>
                </li>
                <?php } ?>

                <!-- Add Product -->
                <li id="production_units4" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/add_product'); ?>">
                        <i class="fa fa-industry"></i>
                        <span class="text"><?= lang('Add_Product'); ?></span>
                    </a>
                </li> 

            <?php } ?>

        <?php } ?>

    </ul>
</li>

<?php } ?>
<?php } ?>
<!-- ////////////////////////////// Generate Variant PO ////////////////////// -->
<?php if ($Settings->display_job_work) { ?>
<li class="mm_production_unit main-item">
        <a class="dropmenu" href="#">
            <!-- <i class="fa fa-warehouse custom-icon" style="width: 20px;"></i> -->
            <i class="fa fa-industry"></i>
            <span class="text"> <?= lang('Job_Works'); ?> <img
                    src="<?= site_url('themes/default/assets/images/new.gif') ?>" height="30px" alt="new"></span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="Variant_Bill_of_material" class="list-item">
                <a class="submenu" href="<?= site_url('Variant_bill_of_materials'); ?>">
                    <i class="fa fa-home item"></i>
                    <span class="text item"> <?= lang('Variant_Bill_Of_Materials'); ?> </span>
                </a>
            </li>
            
             <li id="transfers_transfer_rm" class="list-item">
            <a class="submenu item" href="<?= site_url('transfers/transfer_rm'); ?>">
                <i class="fa fa-exchange item"></i><span class="text item"> Transfer RM </span>
            </a>
        </li>
        </ul>
    </li>
    <li id="production_units9" class="list-item">
        <a class="submenu item" href="<?= site_url('RM_Calculator'); ?>">
            <i class="fa fa-industry item"></i>
            <span class="text item"> <?= lang('RM Calculator'); ?><img
                    src="<?= site_url('themes/default/assets/images/new.gif') ?>" height="30px" alt="new"> </span>
        </a>
    </li>
<?php } ?>


<!-- for place order -->

<?php if ($Settings->enable_module_production_unit) { ?>
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
                <li id="Recipies" class="list-item">
                    <a class="submenu item" href="<?= site_url('Recipies'); ?>">
                        <i class="fa fa-home item"></i>
                        <span class="text item">Recipies</span>
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
                <a class="submenu item" href="<?= site_url('Production_Unit/kds'); ?>">
                    <i class="fa fa-desktop item"></i>
                    <span class="text item"> KDS </span>
                </a>
            </li>
        </ul>
    </li>
<?php } ?>

<!-- for place order -->
<?php
if ($GP['Procurement_Orders']) {
?>
    <li class="mm_production_unit main-item">
        <a class="dropmenu" href="">
            <i class="fa fa-home"></i>
                <span class="text"> <?= lang('Procurement_Orders'); ?> 
            <span class="chevron closed"></span>
        </a>
        <ul>

            <?php if ($GP['Procurement_Orders']) { ?>
                <li id="production_units5" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/procurementOrders'); ?>">
                        <i class="fa fa-home"></i>
                        <span class="text"> <?= lang('Place_Orders'); ?> </span>
                    </a>
                </li>
                <li id="production_units6" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/receive_delivery'); ?>">
                        <i class="fa fa-home"></i>
                        <span class="text"> <?= lang('Receive_Delivery'); ?> </span>
                    </a>
                </li>
                <li id="production_units7" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/inventory'); ?>">
                        <i class="fa fa-home"></i>
                        <span class="text"> <?= lang('Inventory'); ?> </span>
                    </a>
                </li>
                <li id="production_units8" class="list-item">
                    <a class="submenu" href="<?= site_url('Production_Unit/ordering_history'); ?>">
                        <i class="fa fa-home"></i>
                        <span class="text"> <?= lang('Ordering_History'); ?> </span>
                    </a>
                </li>
            <?php } //end if 
            ?>

        </ul>
    </li>
<?php } ?>
<?php if ($GP['crm_portal']) {
?>
    <li id="CRM-PORTAL" class="main-item">
        <a class="submenu" href="<?= site_url('smsdashboard'); ?>">
            <i class="fa fa-envelope"></i><span class="text"> <?= lang('CRM Portal'); ?></span><img
            src="<?= site_url('themes/default/assets/images/new.gif') ?>" height="30px" alt="new">
        </a>
    </li>
<?php }
if ($GP['quotes-index'] || $GP['quotes-add']) { ?>
    <li class="mm_quotes main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-heart-o"></i>
            <span class="text"> <?= lang('quotes'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="sales_index" class="list-item">
                <a class="submenu" href="<?= site_url('quotes'); ?>">
                    <i class="fa fa-heart-o"></i><span class="text"> <?= lang('list_quotes'); ?></span>
                </a>
            </li>
            <?php if ($GP['quotes-add']) { ?>
                <li id="sales_add" class="list-item">
                    <a class="submenu" href="<?= site_url('quotes/add'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_quote'); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
<?php } ?>

<?php if ($GP['purchases-index'] || $GP['purchases-add'] || $GP['purchases-expenses'] || $GP['purchases-notification']) { ?>
    <li class="mm_purchases main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-star"></i>
            <span class="text"> <?= lang('purchases'); ?>
            </span> <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="purchases_index" class="list-item">
                <a class="submenu" href="<?= site_url('purchases'); ?>">
                    <i class="fa fa-star"></i><span class="text"> <?= lang('list_purchases'); ?></span>
                </a>
            </li>
            <?php if ($GP['purchases-add']) { ?>
                <li id="purchases_add" class="list-item">
                    <a class="submenu" href="<?= site_url('purchases/add'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_purchase'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['purchases-expenses']) { ?>
                <li id="purchases_expenses" class="list-item">
                    <a class="submenu" href="<?= site_url('purchases/expenses'); ?>">
                        <i class="fa fa-dollar"></i><span class="text"> <?= lang('list_expenses'); ?></span>
                    </a>
                </li>
                <li id="purchases_add_expense" class="list-item">
                    <a class="submenu" href="<?= site_url('purchases/add_expense'); ?>" data-toggle="modal"
                        data-target="#myModal">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_expense'); ?></span>
                    </a>
                </li>
            <?php }
            if ($GP['purchase_add_csv']) { ?>

                <li id="purchases_purchase_by_csv" class="list-item">
                    <a class="submenu" href="<?= site_url('purchases/purchase_by_csv'); ?>">
                        <i class="fa fa-plus-circle"></i>
                        <span class="text"> <?= lang('add_purchase_by_csv'); ?></span>
                    </a>
                </li>
                <?php }
            if ($Settings->synced_data_sales) {
                if ($GP['purchases-notification']) { ?>

                    <li id="purchases_noification" class="list-item">
                        <a class="submenu" href="<?= site_url('purchases/purchase_notification'); ?>">
                            <i class="fa fa-dollar"></i>
                            <span class="text"> <?= lang('Purchase_Notification'); ?></span>
                        </a>
                    </li>
            <?php
                }
            } ?>
                <?php if ($GP['suppliers-index']) {
            ?>
                <li id="suppliers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('suppliers'); ?>">
                        <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                    </a>
                </li>
            <?php
            }
            if ($GP['suppliers-add']) {
            ?>
                <li id="suppliers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
<?php } ?>
<?php if ($GP['transfers-index'] || $GP['transfers-add'] || $GP['transfers_add_csv'] || $GP['transfers-request'] || $GP['transfers-add_request']) { ?>
    <li class="mm_transfers main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-exchange"></i>
            <span class="text"> <?= lang('transfers'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($GP['transfers-index']) { ?>
                <li id="transfers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('transfers'); ?>">
                        <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['transfers-add']) { ?>
                <li id="transfers_add" class="list-item">
                    <a class="submenu" href="<?= site_url('transfers/add'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['transfers_add_csv']) { ?>
                <li id="transfers_transfer_by_csv" class="list-item">
                    <a class="submenu" href="<?= site_url('transfers/transfer_by_csv'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer_by_csv'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['transfers-request']) { ?>
                <li id="transfers_request" class="list-item">
                    <a class="submenu" href="<?= site_url('transfers/request'); ?>">
                        <i class="fa fa-exchange"></i><span class="text"> <?= lang('Requests'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <?php if ($GP['transfers-add_request']) { ?>
                <li id="transfers_add_request" class="list-item">
                    <a class="submenu" href="<?= site_url('transfers/add_request'); ?>">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('Add Request'); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
<?php } ?>
<?php
/*
<?php if ($GP['customers-index'] || $GP['customers-add'] || $GP['suppliers-index'] || $GP['suppliers-add'] || $GP['leads-index']) { ?>
    <li class="mm_auth mm_customers mm_suppliers mm_billers main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-users"></i>
            <span class="text"> <?= lang('people'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <!-- <?php if ($GP['customers-index']) { ?>
                <li id="customers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('customers'); ?>">
                        <i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                    </a>
                </li>
            <?php
            }
            if ($GP['customers-add']) {
            ?>
                <li id="customers_index1" class="list-item">
                    <a class="submenu" href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                    </a>
                </li>
            <?php } ?> -->
            <?php if ($GP['lead-index']) { ?>
                <li id="leads_index" class="list-item">
                    <a class="submenu" href="<?= site_url('Leads/index'); ?>">
                        <i class="fa fa-users"></i><span
                            class="text"> <?= lang('All Leads List'); ?></span> 
                    </a>
                </li>
                <li id="leads_add" class="list-item">
                    <a class="submenu" href="<?= site_url('Leads/add'); ?>">
                        <i class="fa fa-users"></i><span
                            class="text"> <?= lang('Add_Lead'); ?></span> 
                    </a>
                </li>
                 <!-- <li id="get_count" class="list-item">
                <a class="submenu item" href="<?= site_url('Leads/get_count'); ?>">
                    <i class="fa fa-users item"></i><span
                        class="text item"> <?= lang('Check_Count'); ?></span> <img class="item" src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                </a>
            </li> -->
            <?php } ?>
            <!-- <?php if ($GP['suppliers-index']) {
            ?>
                <li id="suppliers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('suppliers'); ?>">
                        <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                    </a>
                </li>
            <?php
            }
            if ($GP['suppliers-add']) {
            ?>
                <li id="suppliers_index" class="list-item">
                    <a class="submenu" href="<?= site_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
                        <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                    </a>
                </li>
            <?php } ?> -->
        </ul>
    </li>
<?php } ?> 
*/
?>
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

<!-- <?php

            if ($this->shopowner->group_id == '8' || $this->shopowner->group_id == '9') { ?>
    <li class="mm_system_settings main-item <?= strtolower($this->router->fetch_method()) != 'settings' ? '' : 'mm_pos' ?>">
        <a class="dropmenu" href="#">
            <i class="fa fa-cog"></i><span class="text"> <?= lang('settings'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <li id="system_settings_index" class="list-item">
                <a href="<?= site_url('system_settings') ?>">
                    <i class="fa fa-cog"></i><span class="text"> <?= lang('system_settings'); ?></span>
                </a>
            </li>
            <?php if (POS) { ?>
                <li id="pos_settings" class="list-item">
                    <a href="<?= site_url('pos/settings') ?>">
                        <i class="fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_custom_fields" class="list-item">
                <a href="<?= site_url('system_settings/custom_fields') ?>">
                    <i class="fa fa-cog"></i><span class="text"> <?= lang('custom_fields'); ?></span> 
                </a>
            </li>
            <li id="system_settings_change_logo" class="list-item">
                <a href="<?= site_url('system_settings/change_logo') ?>" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-upload"></i><span class="text"> <?= lang('change_logo'); ?></span>
                </a>
            </li>
            <li id="system_settings_currencies" class="list-item">
                <a href="<?= site_url('system_settings/currencies') ?>">
                    <i class="fa fa-money"></i><span class="text"> <?= lang('currencies'); ?></span>
                </a>
            </li>
            <li id="system_settings_customer_groups" class="list-item">
                <a href="<?= site_url('system_settings/customer_groups') ?>">
                    <i class="fa fa-chain"></i><span class="text"> <?= lang('customer_groups'); ?></span>
                </a>
            </li>
            <li id="system_settings_price_groups" class="list-item">
                <a href="<?= site_url('system_settings/price_groups') ?>">
                    <i class="fa fa-dollar"></i><span class="text"> <?= lang('price_groups'); ?></span>
                </a>
            </li>
            <?php if ($Settings->pos_type == 'restaurant') { ?>
                <li id="system_settings_restaurant_tables" class="list-item">
                    <a href="<?= site_url('system_settings/restaurant_tables') ?>">
                        <i class="fa fa-dollar"></i><span class="text"><?= lang('Restaurant_Tables'); ?> </span>
                    </a>
                </li>

                <li id="system_settings_price_groups" class="list-item">
                    <a href="<?= site_url('system_settings/restaurant_tables_price_groups') ?>">
                        <i class="fa fa-dollar"></i><span class="text"> <?= lang('Table Price Groups'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_categories" class="list-item">
                <a href="<?= site_url('system_settings/categories') ?>">
                    <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories'); ?></span>
                </a>
            </li>
            <li id="system_settings_expense_categories" class="list-item">
                <a href="<?= site_url('system_settings/expense_categories') ?>">
                    <i class="fa fa-folder-open"></i><span class="text"> <?= lang('expense_categories'); ?></span>
                </a>
            </li>
            <li id="system_settings_units" class="list-item">
                <a href="<?= site_url('system_settings/units') ?>">
                    <i class="fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
                </a>
            </li>
            <li id="system_settings_brands" class="list-item">
                <a href="<?= site_url('system_settings/brands') ?>">
                    <i class="fa fa-th-list"></i><span class="text"> <?= lang('brands'); ?></span>
                </a>
            </li>
            <li id="system_settings_variants" class="list-item">
                <a href="<?= site_url('system_settings/variants') ?>">
                    <i class="fa fa-tags"></i><span class="text"> <?= lang('variants'); ?></span>
                </a>
            </li>
            <li id="system_settings_variants" class="list-item">
                <a href="<?= site_url('system_settings/variant_manage') ?>">
                    <i class="fa fa-tags"></i><span class="text">
                        <?= lang('Manage Variants'); ?> </span>
                </a>
            </li>
            <li id="system_settings_tax_rates" class="list-item">
                <a href="<?= site_url('system_settings/tax_rates') ?>">
                    <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?></span>
                </a>
            </li>
            <li id="system_settings_tax_rates_attr" class="list-item">
                <a href="<?= site_url('system_settings/tax_rates_attr') ?>">
                    <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?>
                        Attributes </span>
                </a>
            </li>
            <?php if ($Owner) { ?>
                <li id="system_settings_warehouses" class="list-item">
                    <a href="<?= site_url('system_settings/warehouses') ?>">
                        <i class="fa fa-building-o"></i><span class="text"> <?= lang('warehouses'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_email_templates" class="list-item">
                <a href="<?= site_url('system_settings/email_templates') ?>">
                    <i class="fa fa-envelope"></i><span class="text"> <?= lang('email_templates'); ?></span>
                </a>
            </li>
            <?php if ($Owner) { ?>
                <li id="system_settings_user_groups" class="list-item">
                    <a href="<?= site_url('system_settings/user_groups') ?>">
                        <i class="fa fa-key"></i><span class="text"> <?= lang('group_permissions'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="system_settings_backups" class="list-item">
                <a href="<?= site_url('system_settings/backups') ?>">
                    <i class="fa fa-database"></i><span class="text"> <?= lang('backups'); ?></span>
                </a>
            </li>

            <li id="coupon" class="list-item">
                <a class="submenu" href="<?= site_url('system_settings/discount_coupon_list'); ?>">
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    <span class="text"> Discount Coupon </span>
                </a>
            </li>



            <li id="system_settings_offer_list" class="list-item">
                <a href="<?= site_url('system_settings/offer_list'); ?>">
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    <span class="text"> Offer </span>
                </a>
            </li>
            <li id="system_settings_offercategory" class="list-item">
                <a href="<?= site_url('system_settings/offercategory'); ?>">
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    <span class="text"> Offer Category
                </a>
            </li>
            <li id="system_settings_sms_configs" class="list-item">
                <a href="<?= site_url('system_settings/sms_configs'); ?>">
                    <i class="fa fa-send" aria-hidden="true"></i>
                    <span class="text"> SMS Config
                </a>
            </li>
            <li id="system_settings_printers" class="list-item">
                <a href="<?= site_url('system_settings/printers'); ?>">
                    <i class="fa fa-print"></i>
                    <span class="text"> Manage Printers Option</span>
                </a>
            </li>
            <li id="printers_wifi" style="display:none" class="list-item">
                <a href="javascript:window.MyHandler.OpenWifiPrinterDialog()">
                    <i class="fa fa-wifi"></i>
                    <span class="text"> Wifi Printer Setting</span>
                </a>
            </li>
        </ul>
    </li>
<?php } ?> -->
<?php if ($GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts'] || $GP['reports-products'] || $GP['reports-monthly_sales'] || $GP['reports-sales'] || $GP['reports-payments'] || $GP['reports-purchases'] || $GP['reports-customers'] || $GP['reports-suppliers'] || $GP['reports-expenses'] || $GP['reports-warehouse_sales_report'] || $GP['reports-gst_reports']) { ?>

    <li class="mm_reports mm_reports_new main-item">
        <a class="dropmenu" href="#">
            <i class="fa fa-pie-chart"></i>
            <span class="text"> <?= lang('reports'); ?> </span>
            <span class="chevron closed"></span>
        </a>
        <ul>
            <?php if ($GP['reports-overview-chart']) { ?>
                <li id="Overview chart" class="list-item">
                    <a href="<?= site_url('reports') ?>">
                        <i class="fa fa-user"></i>
                        <span class="text"><?= lang('Overview chart'); ?></span>
                    </a>
                </li>
            <?php } ?>
            <li id="Accounts" class="mm_Accounts inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Accounts'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-payments']) { ?>
                        <li id="Payments_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/payments') ?>">
                                <i class="fa fa-money"></i>
                                <span class="text"><?= lang('Payments Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-hsncode_reports']) { ?>
                        <li id="HSN_Report" class="inner-inner-list-item <?= $active_item == 'HSN_Report' ? 'active' : ''; ?>">
                            <a class="submenu" href="<?= site_url('reports/hsncode_reports') ?>">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                                <span class="text"> <?= lang('HSN Report'); ?> </span>
                            </a>
                        </li>
                    <?php } ?>


                    <?php if ($GP['reports-payment_chart_details']) { ?>
                        <li id="Payment_Chart_Details" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/payment_chart_details') ?>">
                                <i class="fa fa-pie-chart"></i>
                                <span class="text"><?= lang('Payment Chart Details'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php
                    if ($GP['reports-tax-report']) { ?>
                        <li id="Tax_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/taxreports') ?>">
                                <i class="fa fa-file-text"></i>
                                <span class="text"><?= lang('Tax Report'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>
                    <?php
                    if ($GP['reports-payments-summary']) { ?>
                        <li id="Payment_Summary" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/paymentssummary') ?>">
                                <i class="fa fa-file-text-o"></i>
                                <span class="text"><?= lang('Payment Summary'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>

                    <?php
                    if ($GP['reports-profit-loss-report']) { ?>
                        <li id="Profit_Loss" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/profit_loss') ?>">
                                <i class="fa fa-line-chart"></i>
                                <span class="text"><?= lang('Profit & Loss'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>



                    <?php if ($GP['reports-gst_reports']) { ?>
                        <li id="Simple_Tax_GST_Reports" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/gst_reports') ?>">
                                <i class="fa fa-file-text-o"></i>
                                <span class="text"><?= lang('Simple Tax / GST Reports'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-cash-transaction-report']) { ?>
                        <li id="Cash_Transaction_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/cash_transaction') ?>">
                                <i class="fa fa-file-text-o"></i>
                                <span class="text"><?= lang('Cash Transaction Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>

            <!-- POWER BI REPORTS   -->
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
                document.querySelectorAll('.mm_BI_Reports .inner-inner-list-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelectorAll('.mm_BI_Reports .inner-inner-list-item > a').forEach(a => {
                    const href = a.getAttribute('href');
                    if (href && currentUrl.indexOf(href) !== -1) {
                        a.parentElement.classList.add('active');
                    }
                });
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
            setActiveReport();
        });
        </script>

           <script>
            #biReportModal .modal-content {
                height: 100%;
                border: 0;
                border-radius: 0;
            }
            #biReportModal .modal-body {
                padding: 0;
                height: calc(100% - 60px) !important;
            }
            </script> 
        
        <style>
        /* Style for active BI report item */
        /* .inner-inner-list-item.active > a {
            background-color: #f5f5f5;
            color: #3c8dbc !important;
            border-left: 3px solid #3c8dbc;
        }
        
        .inner-inner-list-item.active > a i {
            color: #3c8dbc;
        } */
        </style>

            <li id="Challan" class="mm_Challan inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Challan'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-challan-reports']) { ?>
                        <li id="Challan_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/challans') ?>">
                                <i class="fa fa-file"></i>
                                <span class="text"><?= lang('Challan Report'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>
                </ul>
            </li>

            <li id="Customer" class="mm_Customer inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Customer') ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-customers']) { ?>
                        <li id="Customers Report" class="Customers Report inner-inner-list-item">
                            <a href="<?= site_url('reports/customers') ?>">
                                <i class="fa fa-file"></i>
                                <span class="text"><?= lang('Customers Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($GP['reports-deposit']) { ?>
                        <li id="Deposit_Recharge_Report" class="Deposit_Recharge_Report inner-inner-list-item">
                            <a href="<?= site_url('reports/deposit') ?>">
                                <i class="fa fa-credit-card"></i>
                                <span class="text"><?= lang('Deposit Recharge Report'); ?></span>
                            </a>
                        </li>
                        <li id="reports_ledger">
                            <a href="<?= site_url('reports/customer_ledger_v1') ?>">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <span class="text"> <?= lang('Customer_Ledger'); ?> </span>
                                <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-register-report']) { ?>
                        <li id="Register_Report" class="Register_Report inner-inner-list-item">
                            <a href="<?= site_url('reports/register') ?>">
                                <i class="fa fa-book"></i>
                                <span class="text"><?= lang('Register Report'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>

                </ul>
            </li>
            <li id="Ledger" class="mm_Ledger inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Ledger'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-customer-ledgers']) { ?>
                        <li id="Customer_Ledgers" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/customer_ledger') ?>">
                                <i class="fa fa-user"></i>
                                <span class="text"><?= lang('Customer Ledgers'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>

                    <?php if ($GP['reports-deposit-ledgers']) { ?>
                        <li id="Deposit_Ledgers" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/customerDepositLedger') ?>">
                                <i class="fa fa-credit-card"></i>
                                <span class="text"><?= lang('Deposit Ledgers'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>
                    <?php
                    if ($GP['reports-products_ledgers']) {
                    ?>
                        <li id="reports_products_ledgers" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/products_ledgers') ?>">
                                <i class="fa fa-barcode"></i><span class="text"> <?= lang('Products_Ledgers'); ?></span>
                            </a>
                        </li>
                    <?php }
                    ?>
                </ul>
            </li>
            <li id="Logs" class="mm_Logs inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Logs'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-user-log-report']) { ?>
                        <li id="User_Log_Action" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/user_log_action') ?>">
                                <i class="fa fa-book"></i> <span class="text"><?= lang('User Log Action'); ?></span>
                            </a>
                        </li>
                    <?php   } ?>
                </ul>
            </li>
            <li id="Products" class="mm_Products inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Products'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-products']) { ?>
                        <li id="Adjustment_Reports" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/adjustments') ?>">
                                <i class="fa fa-adjust"></i>
                                <span class="text"><?= lang('adjustments_report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($GP['reports-products']) { ?>
                        <li id="reports_brands" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/brands') ?>">
                                <i class="fa fa-tags"></i>
                                <span class="text"><?= lang('brands_report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-categories_brand_chart_details']) { ?>
                        <li id="Categories_Brand_Chart_Details" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/categories_brand_chart_details') ?>">
                                <i class="fa fa-pie-chart"></i>
                                <span class="text"><?= lang('Categories and Brand Chart Details'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-products']) { ?>
                        <li id="Categories_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/categories_report') ?>">
                                <i class="fa fa-list"></i>
                                <span class="text"><?= lang('Categories Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-product-costing']) { ?>
                        <li id="Products_Costing" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/products_costing') ?>">
                                <i class="fa fa-calculator"></i>
                                <span class="text"><?= lang('Products Costing'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($GP['reports-product-combo-item']) { ?>
                        <li id="Products_Combo_Items" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/products_combo_items') ?>">
                                <i class="fa fa-cubes"></i>
                                <span class="text"><?= lang('Products Combo Items'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-expiry_alerts']) { ?>
                        <li id="Product_Expiry_Alerts" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/expiry_alerts') ?>">
                                <i class="fa fa-exclamation-triangle"></i>
                                <span class="text"><?= lang('Product Expiry Alerts'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($GP['reports-product-profit-loss-report']) { ?>
                        <li id="Products_Profit_Loss" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/products_profitloss') ?>">
                                <i class="fa fa-line-chart"></i>
                                <span class="text"><?= lang('Products Profit and Loss'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-quantity_alerts']) { ?>
                        <li id="Product_Quantity_Alerts" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/quantity_alerts') ?>">
                                <i class="fa fa-bell"></i>
                                <span class="text"><?= lang('Product Quantity Alerts'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-products']) { ?>
                        <li id="Products_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/products') ?>">
                                <i class="fa fa-file-text"></i>
                                <span class="text"><?= lang('Products Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-products_transactions']) { ?>
                        <li id="Products_Transaction_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/products_transactions') ?>">
                                <i class="fa fa-exchange"></i>
                                <span class="text"><?= lang('Products Transaction Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-product-varient-stock-report']) { ?>
                        <li id="Product_Variant_Stock_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/product_varient_stock_report') ?>">
                                <i class="fa fa-archive"></i>
                                <span class="text"><?= lang('Product Variant Stock Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($GP['reports-warehouse-stock-report']) { ?>
                        <li id="Warehouse_Stock_Chart" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/warehouse_stock') ?>">
                                <i class="fa fa-bar-chart"></i>
                                <span class="text"><?= lang('Warehouse Stock Chart'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>
            <li id="Purchase" class="mm_Purchase inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Purchase'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-daily_purchases']) { ?>
                        <li id="Daily_Purchases" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/daily_purchases') ?>">
                                <i class="fa fa-calendar"></i>
                                <span class="text"><?= lang('Daily Purchases'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-due-purchase-reports']) { ?>
                        <li id="Due_Purchases_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/purchases_due') ?>">
                                <i class="fa fa-clock-o"></i>
                                <span class="text"><?= lang('Due Purchases Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-expenses']) { ?>
                        <li id="Expenses_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/expenses') ?>">
                                <i class="fa fa-money"></i>
                                <span class="text"><?= lang('Expenses Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-monthly_purchases']) { ?>
                        <li id="Monthly_Purchases" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/monthly_purchases') ?>">
                                <i class="fa fa-calendar-o"></i>
                                <span class="text"><?= lang('Monthly Purchases'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-purchases']) { ?>
                        <li id="Purchases_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/purchases') ?>">
                                <i class="fa fa-file-text"></i>
                                <span class="text"><?= lang('Purchases Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($Shopowner == '8' || $Shopowner == '9') { ?>
                        <li id="Purchases_Report_GST" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/purchases_gst_report') ?>">
                                <i class="fa fa-shopping-cart"></i>
                                <span class="text"><?= lang('Purchases Report GST'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-product-varient-purchase-report']) { ?>
                        <li id="Product_Variant_Purchase_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/product_varient_purchase_report') ?>">
                                <i class="fa fa-archive"></i>
                                <span class="text"><?= lang('Product Variant Purchase Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                      <?php if ($GP['reports-suppliers']) { ?>
                        <li id="Suppliers_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/suppliers') ?>">
                                <i class="fa fa-truck"></i>
                                <span class="text"><?= lang('Suppliers Report'); ?></span>
                            </a>
                        </li>
                         <?php } ?>
                </ul>
            </li>
            <li id="Sales" class="mm_Sales inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Sales'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-best-seller']) { ?>
                        <li id="Best_Seller" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/best_sellers') ?>">
                                <i class="fa fa-star"></i>
                                <span class="text"><?= lang('Best Seller'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-get_customer_wise_sales']) { ?>
                        <!-- <li id="Customerwise_Sale_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/get_customer_wise_sales') ?>">
                                <i class="fa fa-users"></i>
                                <span class="text"><?= lang('Customerwise Sale Report'); ?></span>
                            </a>
                        </li> -->
                    <?php } ?>
                    <?php if ($GP['reports-daily_sales']) { ?>
                        <li id="Daily_Sales" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/daily_sales') ?>">
                                <i class="fa fa-calendar"></i>
                                <span class="text"><?= lang('Daily Sales'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-due-sales-reports']) { ?>
                        <li id="Due_Sales_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/sales_due') ?>">
                                <i class="fa fa-clock-o"></i>
                                <span class="text"><?= lang('Due Sales Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-monthly_sales']) { ?>
                        <li id="Monthly_Sales" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/monthly_sales') ?>">
                                <i class="fa fa-calendar-o"></i>
                                <span class="text"><?= lang('Monthly Sales'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-product-wise-sale-report']) { ?>
                        <li id="Productwise_Sale_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/Product_wise_Sale_Report') ?>">
                                <i class="fa fa-cogs"></i>
                                <span class="text"><?= lang('Productwise Sale Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-sales-extended-report']) { ?>


                        <li id="Sales_Extended_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/sales_extended_report') ?>">
                                <i class="fa fa-file-text"></i>
                                <span class="text"><?= lang('Sales Extended Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-sales']) { ?>
                        <li id="Sales_GST_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports_new/sales_gst_reportnew') ?>">
                                <i class="fa fa-table"></i>
                                <span class="text"><?= lang('Sales GST Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-sale_purchase_chart_details']) { ?>
                        <li id="Sale_Purchase_Chart_Details" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/sale_purchase_chart_details') ?>">
                                <i class="fa fa-bar-chart"></i>
                                <span class="text"><?= lang('Sale Purchase Chart Details'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-sales']) { ?>
                        <li id="Sale_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/sales') ?>">
                                <i class="fa fa-file"></i>
                                <span class="text"><?= lang('Sale Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['sales_transaction_report_UAE'])
             { ?>

                <li id="Sale_Report_VAT_UAE">
                    <a class="submenu" href="<?= site_url('reports_new/sales_vat_report_uae') ?>">
                        <i class="fa fa-file"></i>
                        <span class="text"><?= lang('Sales Transaction Report'); ?></span>
                    </a>
                </li>
                <?php } ?>
                    <?php if ($GP['reports-term-wise-sale-report']) { ?>
                        <li id="Term_Wise_Sales_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/term_wise_sale_report') ?>">
                                <i class="fa fa-table"></i>
                                <span class="text"><?= lang('Term Wise Sales Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-warehouse_sales_report']) { ?>
                        <li id="Warehouse_Sales_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/warehouse_sales') ?>">
                                <i class="fa fa-warehouse"></i>
                                <span class="text"><?= lang('Warehouse Sales Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-sales'])
            { ?>
            <li id="sales_dash">
                <a href="<?= site_url('reports/sales_dash') ?>">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('sales_dash'); ?></span>
                </a>
            </li>
        <?php } ?>
                    <li id="variant_sale_report" class="inner-inner-list-item">
                    <a class="submenu item" href="<?= site_url('reports/product_varient_sale_report') ?>">
                        <i class="fa fa-shopping-cart item"></i>
                        <span class="text item"><?= lang('Product_Variant_sale_report'); ?></span>
                    </a>
                </li>
                  <?php if ($GP['reports-sales-person-report']) { ?>
                        <li id="Sales_Person_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/sales_person_report') ?>">
                                <i class="fa fa-user"></i>
                                <span class="text"><?= lang('Sales Person Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-staff-report']) { ?>
                        <li id="Staff_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/users') ?>">
                                <i class="fa fa-users"></i>
                                <span class="text"><?= lang('Staff Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>
            <li id="Transfers" class="mm_Transfers inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Transfers'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-transfer-report']) { ?>
                        <li id="Transfer_Reports" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/transferReport') ?>">
                                <i class="fa fa-exchange"></i>
                                <span class="text"><?= lang('Transfer Reports'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-transfer_request']) { ?>
                        <li id="Transfer_Request_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/transfer_request') ?>">
                                <i class="fa fa-file-text"></i>
                                <span class="text"><?= lang('Transfer Request Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>
            <!-- <li id="Users" class="mm_Users inner-list-item">
                <a class="dropmenu" href="#">
                    <i class="fa fa-bars"></i>
                    <span class="text"><?= lang('Users'); ?></span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <?php if ($GP['reports-sales-person-report']) { ?>
                        <li id="Sales_Person_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/sales_person_report') ?>">
                                <i class="fa fa-user"></i>
                                <span class="text"><?= lang('Sales Person Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-staff-report']) { ?>
                        <li id="Staff_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/users') ?>">
                                <i class="fa fa-users"></i>
                                <span class="text"><?= lang('Staff Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($GP['reports-suppliers']) { ?>
                        <li id="Suppliers_Report" class="inner-inner-list-item">
                            <a href="<?= site_url('reports/suppliers') ?>">
                                <i class="fa fa-truck"></i>
                                <span class="text"><?= lang('Suppliers Report'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li> -->
        </ul>
    </li>
<?php } ?>

<?php

if ($GP['printer-setting']) {
?>
    <li id="printers" class="inner-list-item">
        <a href="<?= site_url('system_settings/printers'); ?>">
            <i class="fa fa-print"></i>
            <span class="text"> Manage Printers Option</span>
        </a>
    </li>
<?php } ?>
<script>
    // $(document).ready(function() {
    //     // User Section
    //     if (window.location.href.indexOf("sales_person_report") > -1 ||
    //         window.location.href.indexOf("users") > -1 ||
    //         window.location.href.indexOf("suppliers") > -1) {
    //         $("#Users .dropmenu").addClass('open');
    //     }

    //     // Transfer Section
    //     if (window.location.href.indexOf("transferReport") > -1 ||
    //         window.location.href.indexOf("transfer_request") > -1) {
    //         $("#Transfers .dropmenu").addClass('open');
    //     }

    //     // Sale Section
    //     if (window.location.href.indexOf("best_sellers") > -1 ||
    //         window.location.href.indexOf("get_customer_wise_sales") > -1 ||
    //         window.location.href.indexOf("daily_sales") > -1 ||
    //         window.location.href.indexOf("sales_due") > -1 ||
    //         window.location.href.indexOf("monthly_sales") > -1 ||
    //         window.location.href.indexOf("Product_wise_Sale_Report") > -1 ||
    //         window.location.href.indexOf("sales_extended_report") > -1 ||
    //         window.location.href.indexOf("sales_gst_reportnew") > -1 ||
    //         window.location.href.indexOf("sale_purchase_chart_details") > -1 ||
    //         window.location.href.indexOf("sales") > -1 ||
    //         window.location.href.indexOf("term_wise_sale_report") > -1 ||
    //         window.location.href.indexOf("warehouse_sales") > -1) {
    //         $("#Sales .dropmenu").addClass('open');
    //     }

    //     // Purchases Section
    //     if (window.location.href.indexOf("daily_purchases") > -1 ||
    //         window.location.href.indexOf("purchases_due") > -1 ||
    //         window.location.href.indexOf("expenses") > -1 ||
    //         window.location.href.indexOf("monthly_purchases") > -1 ||
    //         window.location.href.indexOf("purchases") > -1 ||
    //         window.location.href.indexOf("purchases_gst_report") > -1 ||
    //         window.location.href.indexOf("product_varient_purchase_report") > -1) {
    //         $("#Purchase .dropmenu").addClass('open');
    //     }

    //     // Products Section
    //     if (window.location.href.indexOf("adjustments") > -1 ||
    //         window.location.href.indexOf("brands") > -1 ||
    //         window.location.href.indexOf("categories_brand_chart_details") > -1 ||
    //         window.location.href.indexOf("categories_report") > -1 ||
    //         window.location.href.indexOf("products_costing") > -1 ||
    //         window.location.href.indexOf("products_combo_items") > -1 ||
    //         window.location.href.indexOf("expiry_alerts") > -1 ||
    //         window.location.href.indexOf("products_profitloss") > -1 ||
    //         window.location.href.indexOf("quantity_alerts") > -1 ||
    //         window.location.href.indexOf("products") > -1 ||
    //         window.location.href.indexOf("products_transactions") > -1 ||
    //         window.location.href.indexOf("product_varient_stock_report") > -1 ||
    //         window.location.href.indexOf("warehouse_stock") > -1) {
    //         $("#Products .dropmenu").addClass('open');
    //     }

    //     // Logs Section
    //     if (window.location.href.indexOf("user_log_action") > -1) {
    //         $("#Logs .dropmenu").addClass('open');
    //         $("#User_Log_Action").addClass('active');
    //     }

    //     // Customer Section
    //     if (window.location.href.indexOf("customers") > -1) {
    //         $("#Customer .dropmenu").addClass('open');
    //         $("#Customers_Report").addClass('active');
    //     } else if (window.location.href.indexOf("deposit") > -1) {
    //         $("#Customer .dropmenu").addClass('open');
    //         $("#Deposit_Recharge_Report").addClass('active');
    //     } else if (window.location.href.indexOf("register") > -1) {
    //         $("#Customer .dropmenu").addClass('open');
    //         $("#Register_Report").addClass('active');
    //     }

    //     // Ledger Section
    //     if (window.location.href.indexOf("customer_ledger") > -1) {
    //         $("#Ledger .dropmenu").addClass('open');
    //         $("#Customer_Ledgers").addClass('active');
    //     } else if (window.location.href.indexOf("customerDepositLedger") > -1) {
    //         $("#Ledger .dropmenu").addClass('open');
    //         $("#Deposit_Ledgers").addClass('active');
    //     } else if (window.location.href.indexOf("products_ledgers") > -1) {
    //         $("#Ledger .dropmenu").addClass('open');
    //         $("#reports_products_ledgers").addClass('active');
    //     }

    //     // Challan Section
    //     if (window.location.href.indexOf("challans") > -1) {
    //         $("#Challan .dropmenu").addClass('open');
    //     }

    //     // Accounts Section
    //     if (window.location.href.indexOf("payments") > -1 ||
    //         window.location.href.indexOf("hsncode_reports") > -1 ||
    //         window.location.href.indexOf("payment_chart_details") > -1 ||
    //         window.location.href.indexOf("taxreports") > -1 ||
    //         window.location.href.indexOf("paymentssummary") > -1 ||
    //         window.location.href.indexOf("profit_loss") > -1 ||
    //         window.location.href.indexOf("gst_reports") > -1) {
    //         $("#Accounts .dropmenu").addClass('open');
    //     }

    //     //discount_coupoun
    //     if (window.location.href.indexOf("system_settings/discount_coupon_list") > -1) {
    //         $("#SystemSettings .dropmenu").addClass("open");

    //         $("#SystemSettings").addClass("active");

    //         $("#coupon").addClass("active");
    //     }

    //     $(".dropmenu").on("click", function(e) {
    //         e.stopPropagation();
    //         $(this).toggleClass("open");
    //     });

    //     $(document).on("click", function() {
    //         $(".dropmenu").removeClass("open");
    //     });

    // });


    // document.addEventListener('DOMContentLoaded', function() {
    //     const currentUrl = window.location.href;
    //     const sections = [
    //         '.mm_Users li a',
    //         '.mm_Transfers li a',
    //         '.mm_Sales li a',
    //         '.mm_Purchase li a',
    //         '.mm_Products li a',
    //         '.mm_Logs li a',
    //         '.mm_Customer li a',
    //         '.mm_Ledger li a',
    //         '.mm_Challan li a',
    //         '.mm_Accounts li a'
    //     ];

    //     sections.forEach(selector => {
    //         document.querySelectorAll(selector).forEach(item => {
    //             if (currentUrl.includes(item.getAttribute('href'))) {
    //                 item.closest('li').classList.add('active');
    //             }
    //         });
    //     });
    // });

    const url = window.location.pathname.split('/');
    if (url[url.length - 1] === "welcome" || url[url.length - 1] === "") {
        sessionStorage.setItem('activeTab', 'dashboard');
    }

    document.addEventListener("DOMContentLoaded", () => {

        const mainItems = document.querySelectorAll('.main-item');
        Array.from(mainItems).forEach((mainItem) => {
            mainItem.addEventListener("click", (event) => {
                let val;
                if (event.target.closest('.list-item')) {
                    sessionStorage.setItem('activeTab', event.target.closest('.list-item').id);
                } else if (event.target.closest('.inner-inner-list-item')) {
                    sessionStorage.setItem('activeTab', event.target.closest('.inner-inner-list-item').id);
                } else if (event.target.closest('.inner-list-item')) {
                    sessionStorage.setItem('activeTab', event.target.closest('.inner-list-item').id);
                } else {
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
            let activeElement = document.getElementById(activeTab);

            if (activeTab) {
                if (activeElement.classList.contains('inner-inner-list-item')) {
                    activeElement.closest('.inner-list-item').children[1].style.display = "block";
                    activeElement.closest('.main-item').classList.add('active');
                    activeElement.closest('.main-item').children[1].style.display = "block";
                    activeElement.closest('.main-item').children[0].children[2].classList.remove('closed');
                    activeElement.closest('.main-item').children[0].children[2].classList.add('opened');
                } else if (activeElement.closest('.main-item').children[1]) {
                    if (window.getComputedStyle(activeElement.closest('.main-item').children[1]).display === "none") {
                        activeElement.closest('.main-item').classList.add('active');
                        activeElement.closest('.main-item').children[1].style.display = "block";
                        // activeElement.closest('.main-item').children[1].style.transition = 'all 0.1s linear';
                        activeElement.closest('.main-item').children[0].children[2].classList.remove('closed');
                        activeElement.closest('.main-item').children[0].children[2].classList.add('opened');
                    }
                }
                activeElement.classList.add('active');

                setTimeout(() => {
                    Array.from(document.querySelectorAll('.list-item')).forEach((item) => {
                        if (item.id != activeTab) {
                            item.classList.remove('active');
                        };
                    });

                    Array.from(document.querySelectorAll('.main-item')).forEach((mainItem) => {
                        const activeText = activeElement.closest('.main-item').children[0].children[1].textContent;
                        if (activeText) {
                            if (mainItem.closest('.main-item').children[0].children[1].textContent !== activeText) {
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
                }, 100)
            }
        },600)
    })
</script>

</script>
<style>
    #Users .dropmenu.open+ul,
    #Transfers .dropmenu.open+ul,
    #Sales .dropmenu.open+ul,
    #Purchase .dropmenu.open+ul,
    #Products .dropmenu.open+ul,
    #Logs .dropmenu.open+ul,
    #Customer .dropmenu.open+ul,
    #Ledger .dropmenu.open+ul,
    #Challan .dropmenu.open+ul,
    #Accounts .dropmenu.open+ul {
        display: block;
    }
</style>