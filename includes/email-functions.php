<?php
/**
 * Send the invoice PDF via email.
 *
 * @param string $customer_email Customer's email address.
 * @param string $pdf_url URL of the generated PDF invoice.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function orderfly_send_invoice_email($customer_email, $pdf_url) {
    $subject = 'Your Order Invoice from Orderfly';
    $message = '
    <p>Thank you for your order! Please find your invoice attached below:</p>
    <p><a href="' . esc_url($pdf_url) . '">Download your invoice</a></p>
    <p>If you have any questions, feel free to contact us.</p>
    ';

    // Get the WordPress admin email
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;

    // Build the email headers
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: Orderfly <' . esc_attr($current_user_email) . '>';

    // Attach the PDF file
    $attachments = [$pdf_url];

    // Send the email
    $email_sent = wp_mail($customer_email, $subject, $message, $headers, $attachments);

    return $email_sent;
}
