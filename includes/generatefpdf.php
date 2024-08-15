<?php

// File: includes/generatefpdf.php

require_once __DIR__ . '/../fpdf/fpdf.php';

/**
 * Generate PDF invoice for a given customer ID.
 *
 * @param int $customer_id The ID of the customer.
 * @return string|false The URL of the generated PDF or false on failure.
 */
function orderfly_generate_invoice_pdf($customer_id) {
    global $wpdb;

    // Fetch order and customer details from the database
    $customer_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}customer_info WHERE id = %d", $customer_id), ARRAY_A);

    $order_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}customer_order_info WHERE order_id = %d", $customer_id), ARRAY_A);

    $order_confirmation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}customer_confirm_order WHERE order_id = %d", $customer_id), ARRAY_A);

    if (!$customer_info || !$order_items || !$order_confirmation) {
        error_log('Failed to fetch order details for PDF generation');
        return false;
    }

    // Create a new PDF document
    $pdf = new FPDF();
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Order Invoice', 0, 1, 'C');

    // Supplier Details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(0, 6, 'Supplier: Pran Foods Limited', 0, 1);
    $pdf->Cell(0, 6, 'Dhaka, Bangladesh', 0, 1);
    $pdf->Cell(0, 6, 'Phone: +8801706428282', 0, 1);
    $pdf->Cell(0, 6, 'BSTI License No: FR2050137055', 0, 1);

    // Client Details
    $pdf->Ln(10);
    $pdf->Cell(0, 6, 'Client: ' . $customer_info['userName'], 0, 1);
    $pdf->Cell(0, 6, $customer_info['userAddress'], 0, 1);
    $pdf->Cell(0, 6, 'Phone: ' . $customer_info['userPhone'], 0, 1);

    // Invoice Details
    $pdf->Ln(10);
    $pdf->Cell(0, 6, 'Invoice: ' . $customer_id, 0, 1);
    $pdf->Cell(0, 6, 'Payment Method: ' . 'Cash On Delivery', 0, 1);
    $pdf->Cell(0, 6, 'Order Number: #' . $customer_id, 0, 1);
    $pdf->Cell(0, 6, 'Issue Date: ' . $order_confirmation['issueDate'], 0, 1);
    $pdf->Cell(0, 6, 'Delivery Date: ' . $order_confirmation['deliveryDate'], 0, 1);
    $pdf->Cell(0, 6, 'Due Date: ' . $order_confirmation['dueDate'], 0, 1);

    // Order Items
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 7, 'Item', 1);
    $pdf->Cell(60, 7, 'Description', 1);
    $pdf->Cell(20, 7, 'Qty', 1);
    $pdf->Cell(30, 7, 'Unit Price', 1);
    $pdf->Cell(30, 7, 'Total', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($order_items as $item) {
        $pdf->Cell(40, 7, $item['productName'], 1);
        $pdf->Cell(60, 7, $item['description'], 1);
        $pdf->Cell(20, 7, $item['quantity'], 1, 0, 'C');
        $pdf->Cell(30, 7, $item['price'], 1, 0, 'R');
        $pdf->Cell(30, 7, $item['quantity'] * $item['price'], 1, 0, 'R');
        $pdf->Ln();
    }

    // Order Total
    $pdf->Ln(10);
    $pdf->Cell(0, 6, 'Subtotal: ' . $order_confirmation['subTotal'] . ' Tk', 0, 1, 'R');
    $pdf->Cell(0, 6, 'Shipping Charge: ' . $order_confirmation['shippingCharge'] . ' Tk', 0, 1, 'R');
    $pdf->Cell(0, 6, 'Grand Total: ' . $order_confirmation['grandTotal'] . ' Tk', 0, 1, 'R');

    // Footer
    $pdf->Ln(10);
    $pdf->Cell(0, 6, 'Thank you for your purchase.', 0, 1, 'C');

    // Output the PDF to a file
    $upload_dir = wp_upload_dir();
    $pdf_path = $upload_dir['basedir'] . '/invoices/';
    if (!file_exists($pdf_path)) {
        mkdir($pdf_path, 0755, true);
    }
    $pdf_file = $pdf_path . 'invoice_' . $customer_id . '.pdf';
    $pdf->Output('F', $pdf_file);

    // Return the PDF URL
    return $upload_dir['baseurl'] . '/invoices/invoice_' . $customer_id . '.pdf';
}
