  // Generate Invoice
  function generateInvoice() {
    const { jsPDF } = window.jspdf;  // Access jsPDF from window.jspdf
    const doc = new jsPDF();
    doc.text("Order Invoice", 20, 20);
    doc.text(`Name: ${initialObj.shippingInfo.userName}`, 20, 30);
    doc.text(`Phone: ${initialObj.shippingInfo.userPhone}`, 20, 40);
    doc.text(`Address: ${initialObj.shippingInfo.userAddress}`, 20, 50);
    doc.text(`Order Note: ${initialObj.orderNote}`, 20, 60);

    let y = 70;
    initialObj.items.forEach((item) => {
      doc.text(
        `Item: ${item.title}, Quantity: ${item.quantity}, Price: ${item.price}`,
        20,
        y
      );
      y += 10;
    });

    doc.text(`Subtotal: ${initialObj.subTotal}`, 20, y + 10);
    doc.text(`Shipping: ${initialObj.shippingCharge}`, 20, y + 20);
    doc.text(`Grand Total: ${initialObj.grandTotal}`, 20, y + 30);

    return doc;  // Return the document object
  }

  // Send Email
  function sendEmail() {
    const doc = generateInvoice();
    const pdfContent = doc.output('datauristring');
  
    fetch(frontend_ajax.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: 'send_invoice_email',
        to: "golamfaruk204@gmail.com",
        subject: "Order Invoice",
        body: "Please find attached the order invoice.",
        attachments: [
          {
            filename: "invoice.pdf",
            content: pdfContent.split(',')[1],  // Extract base64 content
            encoding: "base64",
          },
        ],
      }),
    }).then((response) => {
      if (response.ok) {
        console.log("Successfully sent order invoice to your email.");
      } else {
        console.log("Failed to send the invoice. Status:", response.status);
      }
    }).catch(error => {
      console.error("Error sending email:", error);
    });
  }