<?php
/**
 * Bootstrap minimal sma_res_* tables for Module 12 Restaurant tests (sitadmin_phpupgarde).
 * Run once: php restaurant_bootstrap_db.php
 */
$m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m->connect_error) {
    fwrite(STDERR, "DB connect failed\n");
    exit(1);
}

$sqls = [
    "CREATE TABLE IF NOT EXISTS sma_res_sections (
        id int unsigned NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_subsections (
        id int unsigned NOT NULL AUTO_INCREMENT,
        section_id int unsigned NOT NULL,
        name varchar(100) NOT NULL,
        is_active tinyint(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_table_status (
        id int unsigned NOT NULL AUTO_INCREMENT,
        value varchar(50) NOT NULL,
        color varchar(20) DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_tables (
        id int unsigned NOT NULL AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        section_id int unsigned NOT NULL,
        subsection_id int unsigned DEFAULT NULL,
        status_id int unsigned NOT NULL DEFAULT 1,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        updated_at datetime DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_orders (
        id int unsigned NOT NULL AUTO_INCREMENT,
        res_tables_id int unsigned NOT NULL,
        guest_count int NOT NULL DEFAULT 1,
        status varchar(50) NOT NULL DEFAULT 'Active',
        payment_status varchar(50) DEFAULT 'Pending',
        order_type varchar(50) DEFAULT 'Dine-In',
        created_at datetime DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_orders_guests (
        id int unsigned NOT NULL AUTO_INCREMENT,
        res_orders_id int unsigned NOT NULL,
        created_at datetime DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_orders_items (
        id int unsigned NOT NULL AUTO_INCREMENT,
        sma_product_id int unsigned DEFAULT NULL,
        res_orders_id int unsigned NOT NULL,
        res_orders_guests_id int unsigned DEFAULT NULL,
        quantity decimal(15,4) DEFAULT 1,
        mrp decimal(15,4) DEFAULT 0,
        price decimal(15,4) DEFAULT 0,
        discount decimal(15,4) DEFAULT 0,
        amount decimal(15,4) DEFAULT 0,
        spice_level varchar(50) DEFAULT NULL,
        sma_res_meat_wellness_id int unsigned DEFAULT NULL,
        sma_res_common_allergies_list varchar(255) DEFAULT NULL,
        on_add_on_id varchar(255) DEFAULT NULL,
        on_toppings_id varchar(255) DEFAULT NULL,
        onion_flag tinyint(1) DEFAULT NULL,
        garlic_flag tinyint(1) DEFAULT NULL,
        meal_type_id int unsigned DEFAULT NULL,
        status varchar(50) DEFAULT 'Pending',
        created_by int unsigned DEFAULT NULL,
        created_at datetime DEFAULT NULL,
        special_instructions text,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_common_allergies (
        id int unsigned NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        is_active tinyint(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_meal_type (
        id int unsigned NOT NULL AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_meat_wellness (
        id int unsigned NOT NULL AUTO_INCREMENT,
        type varchar(50) NOT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_add_ons (
        id int unsigned NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        is_active tinyint(1) DEFAULT 1,
        price decimal(15,4) DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_toppings (
        id int unsigned NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        is_active tinyint(1) DEFAULT 1,
        price decimal(15,4) DEFAULT 0,
        total decimal(15,4) DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS sma_res_product_details (
        id int unsigned NOT NULL AUTO_INCREMENT,
        product_id int unsigned NOT NULL,
        price decimal(15,4) DEFAULT NULL,
        meal_type_id int unsigned DEFAULT NULL,
        is_active tinyint(1) DEFAULT 1,
        PRIMARY KEY (id),
        KEY product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
];

foreach ($sqls as $sql) {
    if (!$m->query($sql)) {
        fwrite(STDERR, "Failed: " . $m->error . "\n");
        exit(1);
    }
}

$seed = [
    "INSERT IGNORE INTO sma_res_table_status (id, value, color) VALUES
        (1, 'Available', '#28a745'),
        (2, 'Occupied', '#dc3545'),
        (7, 'Order Placed', '#ffc107')",
    "INSERT IGNORE INTO sma_res_sections (id, name, is_active) VALUES (1, 'Main Hall', 1)",
    "INSERT IGNORE INTO sma_res_tables (id, name, section_id, status_id, is_active) VALUES (1, 'T1', 1, 2, 1)",
    "INSERT IGNORE INTO sma_res_orders (id, res_tables_id, guest_count, status, payment_status, order_type, created_at)
        VALUES (1, 1, 2, 'Active', 'Pending', 'Dine-In', NOW())",
    "INSERT IGNORE INTO sma_res_orders_guests (id, res_orders_id, created_at) VALUES (1, 1, NOW())",
    "INSERT IGNORE INTO sma_res_meal_type (id, name) VALUES (1, 'Veg')",
];

foreach ($seed as $sql) {
    $m->query($sql);
}

$alters = [
    "ALTER TABLE sma_res_meal_type ADD COLUMN is_active tinyint(1) NOT NULL DEFAULT 1",
    "ALTER TABLE sma_res_meat_wellness ADD COLUMN is_active tinyint(1) NOT NULL DEFAULT 1",
];
foreach ($alters as $sql) {
    @$m->query($sql);
}

echo "Restaurant bootstrap OK — section=1 table=1 order=1\n";
