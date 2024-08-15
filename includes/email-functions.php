<?php
// File: includes/email-functions.php

/**
 * Send an invoice email with PDF attachment.
 *
 * @param string $recipient_email The recipient's email address.
 * @param string $pdf_url The URL of the PDF file to attach.
 * @return bool True if email sent successfully, false otherwise.
 */
function orderfly_send_invoice_email($recipient_email, $pdf, $customer_id) {
    $subject = 'Your Invoice from Orderfly';
    $message = 'Dear Customer,<br><br>Thank you for your order. Please find your invoice attached.<br><br>Best regards,<br>Orderfly Team';

    // Prepare email headers
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Get the upload directory path and URL
    $upload_dir = wp_upload_dir();
    $pdf_path = $upload_dir['basedir'] . '/invoices/';
    
    // Create the invoices directory if it doesn't exist
    if (!file_exists($pdf_path)) {
        mkdir($pdf_path, 0755, true);
    }
    
    // Set the PDF filename
    $pdf_file = $pdf_path . 'invoice_' . $customer_id . '.pdf';
    
    // Output the PDF to a file
    $pdf->Output('F', $pdf_file);

    // Prepare email attachments
    $attachments = array($pdf_file);

    // Send the email
    $email_sent = wp_mail($recipient_email, $subject, $message, $headers, $attachments);

    if (!$email_sent) {
        error_log('Failed to send email to ' . $recipient_email);
        return false;
    }

    return true;
}

