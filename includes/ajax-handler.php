<?php
// File: includes/ajax-handler.php

add_action('wp_ajax_save_order_data', 'orderfly_save_order_data');
add_action('wp_ajax_nopriv_save_order_data', 'orderfly_save_order_data');

/**
 * Handle order data saving via AJAX.
 */
function orderfly_save_order_data() {
    global $wpdb;

    // Decode order data
    $order_data = json_decode(stripslashes($_POST['orderData']), true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        wp_send_json_error(['error' => 'Invalid JSON data']);
        return;
    }

    // Insert customer information into the database
    $customer_info = $wpdb->insert(
        $wpdb->prefix . 'customer_info',
        [
            'userName' => sanitize_text_field($order_data['shippingInfo']['userName']),
            'userPhone' => sanitize_text_field($order_data['shippingInfo']['userPhone']),
            'userEmail' => sanitize_email($order_data['shippingInfo']['userEmail']),
            'userAddress' => sanitize_textarea_field($order_data['shippingInfo']['userAddress']),
        ]
    );

    if ($customer_info === false) {
        error_log('Failed to insert customer info: ' . $wpdb->last_error);
        wp_send_json_error(['error' => 'Failed to save customer information']);
        return;
    }

    $customer_id = $wpdb->insert_id;

    // Insert each order item into the database
    foreach ($order_data['items'] as $item) {
        $order_items = $wpdb->insert(
            $wpdb->prefix . 'customer_order_info',
            [
                'order_id' => $customer_id,
                'product_id' => sanitize_text_field($item['id']),
                'quantity' => intval($item['quantity']),
                'price' => floatval($item['price']),
            ]
        );

        if ($order_items === false) {
            error_log('Failed to insert order item: ' . $wpdb->last_error);
            wp_send_json_error(['error' => 'Failed to save order items']);
            return;
        }
    }

    // Insert order confirmation details into the database
    $order_confirmation = $wpdb->insert(
        $wpdb->prefix . 'customer_confirm_order',
        [
            'order_id' => $customer_id,
            'subTotal' => floatval($order_data['subTotal']),
            'shippingCharge' => floatval($order_data['shippingCharge']),
            'grandTotal' => floatval($order_data['grandTotal']),
            'orderNote' => sanitize_textarea_field($order_data['orderNote']),
        ]
    );

    if ($order_confirmation === false) {
        error_log('Failed to insert order confirmation: ' . $wpdb->last_error);
        wp_send_json_error(['error' => 'Failed to confirm order']);
        return;
    }

    // Generate PDF Invoice
    $pdf_url = orderfly_generate_invoice_pdf($customer_id);
    if (!$pdf_url) {
        error_log('PDF generation failed for customer ID: ' . $customer_id);
        wp_send_json_error(['error' => 'Failed to generate PDF invoice']);
        return;
    }

    // Send email with PDF attachment
    $email_sent = orderfly_send_invoice_email($order_data['shippingInfo']['userEmail'], $pdf_url);
    
    if (!$email_sent) {
        error_log('Email sending failed for customer ID: ' . $customer_id . ', PDF URL: ' . $pdf_url);
        wp_send_json_error(['error' => 'Failed to send email with PDF']);
        return;
    }

    wp_send_json_success(['pdf_url' => $pdf_url, 'order_id' => $customer_id]);
    wp_die();
}
