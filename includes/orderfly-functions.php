<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create database tables
function orderfly_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        $wpdb->prefix . 'customer_information' => "
            CREATE TABLE {$wpdb->prefix}customer_information (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                userName varchar(255) NOT NULL,
                userPhone varchar(20) NOT NULL,
                userEmail varchar(100) NOT NULL,
                userAddress text NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ",
        $wpdb->prefix . 'customer_orders' => "
            CREATE TABLE {$wpdb->prefix}customer_orders (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                order_id mediumint(9) NOT NULL,
                product_id varchar(255) NOT NULL,
                quantity int NOT NULL,
                price float NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ",
        $wpdb->prefix . 'order_confirmation' => "
            CREATE TABLE {$wpdb->prefix}order_confirmation (
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

// Save order data via AJAX
add_action('wp_ajax_save_order_data', 'orderfly_save_order_data');
add_action('wp_ajax_nopriv_save_order_data', 'orderfly_save_order_data');

function orderfly_save_order_data() {
    global $wpdb;

    $order_data = json_decode(stripslashes($_POST['orderData']), true);

    // Insert into customer_information
    $wpdb->insert(
        $wpdb->prefix . 'customer_information',
        [
            'userName' => sanitize_text_field($order_data['shippingInfo']['userName']),
            'userPhone' => sanitize_text_field($order_data['shippingInfo']['userPhone']),
            'userEmail' => sanitize_email($order_data['shippingInfo']['userEmail']),
            'userAddress' => sanitize_textarea_field($order_data['shippingInfo']['userAddress']),
        ]
    );

    $customer_id = $wpdb->insert_id;

    // Insert into customer_orders
    foreach ($order_data['items'] as $item) {
        $wpdb->insert(
            $wpdb->prefix . 'customer_orders',
            [
                'order_id' => $customer_id,
                'product_id' => sanitize_text_field($item['id']),
                'quantity' => intval($item['quantity']),
                'price' => floatval($item['price']),
            ]
        );
    }

    // Insert into order_confirmation
    $wpdb->insert(
        $wpdb->prefix . 'order_confirmation',
        [
            'order_id' => $customer_id,
            'subTotal' => floatval($order_data['subTotal']),
            'shippingCharge' => floatval($order_data['shippingCharge']),
            'grandTotal' => floatval($order_data['grandTotal']),
            'orderNote' => sanitize_textarea_field($order_data['orderNote']),
        ]
    );


    if (true) { // Replace with actual success condition
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

    wp_die();
}


