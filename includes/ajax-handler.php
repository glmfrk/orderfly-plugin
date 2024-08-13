<?php

// Save order data via AJAX
add_action('wp_ajax_save_order_data', 'orderfly_save_order_data');
add_action('wp_ajax_nopriv_save_order_data', 'orderfly_save_order_data');

function orderfly_save_order_data() {
    global $wpdb;

    $order_data = json_decode(stripslashes($_POST['orderData']), true);

    // Insert into customer_info
    $wpdb->insert(
        $wpdb->prefix . 'customer_info',
        [
            'userName' => sanitize_text_field($order_data['shippingInfo']['userName']),
            'userPhone' => sanitize_text_field($order_data['shippingInfo']['userPhone']),
            'userEmail' => sanitize_email($order_data['shippingInfo']['userEmail']),
            'userAddress' => sanitize_textarea_field($order_data['shippingInfo']['userAddress']),
        ]
    );

    $customer_id = $wpdb->insert_id;

    // Insert into customer_order_info
    foreach ($order_data['items'] as $item) {
        $wpdb->insert(
            $wpdb->prefix . 'customer_order_info',
            [
                'order_id' => $customer_id,
                'product_id' => sanitize_text_field($item['id']),
                'quantity' => intval($item['quantity']),
                'price' => floatval($item['price']),
            ]
        );
    }

    // Insert into customer_confirm_order
    $wpdb->insert(
        $wpdb->prefix . 'customer_confirm_order',
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
