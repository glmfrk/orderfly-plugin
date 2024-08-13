<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create database tables
function orderfly_create_database_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        $wpdb->prefix . 'customer_info' => "
            CREATE TABLE {$wpdb->prefix}customer_info (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                userName varchar(255) NOT NULL,
                userPhone varchar(20) NOT NULL,
                userEmail varchar(100) NOT NULL,
                userAddress text NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ",
        $wpdb->prefix . 'customer_order_info' => "
            CREATE TABLE {$wpdb->prefix}customer_order_info (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                order_id mediumint(9) NOT NULL,
                product_id varchar(255) NOT NULL,
                quantity int NOT NULL,
                price float NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ",
        $wpdb->prefix . 'customer_confirm_order' => "
            CREATE TABLE {$wpdb->prefix}customer_confirm_order (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                order_id mediumint(9) NOT NULL,
                subTotal float NOT NULL,
                shippingCharge float NOT NULL,
                grandTotal float NOT NULL,
                orderNote text,
                PRIMARY KEY (id)
            ) $charset_collate;
        "
    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
}



