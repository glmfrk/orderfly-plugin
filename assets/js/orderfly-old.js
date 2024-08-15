document.addEventListener("DOMContentLoaded", function () {
  const initialObj = {
    items: [],
    shippingInfo: {},
    shippingCharge: 0,
    subTotal: 0,
    grandTotal: 0,
    orderNote: "",
  };

  const checkBoxes = document.querySelectorAll('input[type="checkbox"]');
  const radioBoxes = document.querySelectorAll('input[type="radio"]');
  const inputBoxes = document.querySelectorAll(".input-box");
  const minusButtons = document.querySelectorAll(".minus");
  const plusButtons = document.querySelectorAll(".plus");
  const orderNote = document.querySelector('input[name="orderNote"]');
  const userForm = document.getElementById('userInfoForm');
  // const viewInvoice = document.getElementById('invoice-confirmation');
  // const downloadInvoice = document.getElementById('download-invoice');
  // const printInvoice = document.getElementById('print-invoice');

  const updateItemData = (itemRow, quantity) => {
    const itemId = itemRow.getAttribute("data-id");
    const price = parseInt(itemRow.querySelector(".pd_pricing span").textContent);
    const title = itemRow.querySelector("label").textContent;
    const image = itemRow.querySelector("img").src;
    return { id: itemId, title, image, quantity, price };
  };

  const updateTotals = () => {
    initialObj.subTotal = initialObj.items.reduce((total, item) => total + item.price * item.quantity, 0);
    initialObj.grandTotal = initialObj.subTotal + initialObj.shippingCharge;
    updateViewData();
  };

  const updateViewData = () => {
    document.querySelector(".net_price span").textContent = initialObj.subTotal;
    document.querySelector(".delivery_charge").textContent = initialObj.shippingCharge;
    document.querySelector(".total_price span").textContent = initialObj.grandTotal;
  };

  const handleInputChange = (item) => {
    item.addEventListener("keyup", function () {
      const itemRow = this.closest(".product__table_row");
      const quantity = parseInt(this.value);
      const checkbox = itemRow.querySelector('input[type="checkbox"]');

      if (!isNaN(quantity)) {
        checkbox.checked = true;
        initialObj.items = initialObj.items.filter(i => i.id !== itemRow.getAttribute("data-id"));
        initialObj.items.push(updateItemData(itemRow, quantity));
      } else {
        checkbox.checked = false;
        initialObj.items = initialObj.items.filter(i => i.id !== itemRow.getAttribute("data-id"));
      }

      updateTotals();
    });
  };

  const handleCheckboxChange = (item) => {
    item.addEventListener("change", function () {
      const itemRow = this.closest(".product__table_row");
      const quantityInput = itemRow.querySelector('input[type="number"]');
      let quantity = parseInt(quantityInput.value);

      if (this.checked) {
        quantity = isNaN(quantity) || quantity < 1 ? 1 : quantity;
        quantityInput.value = quantity;
        initialObj.items = initialObj.items.filter(i => i.id !== itemRow.getAttribute("data-id"));
        initialObj.items.push(updateItemData(itemRow, quantity));
      } else {
        initialObj.items = initialObj.items.filter(i => i.id !== itemRow.getAttribute("data-id"));
      }

      updateTotals();
    });
  };

  const handleDecrementButton = (item) => {
    item.addEventListener("click", function () {
      const itemRow = this.closest(".product__table_row");
      const quantityInput = itemRow.querySelector('input[type="number"]');
      let quantity = parseInt(quantityInput.value);

      quantity = isNaN(quantity) || quantity <= 1 ? 0 : quantity - 1;
      quantityInput.value = quantity;
      itemRow.querySelector('input[type="checkbox"]').checked = quantity > 0;

      initialObj.items = initialObj.items.filter(i => i.id !== itemRow.getAttribute("data-id"));
      if (quantity > 0) initialObj.items.push(updateItemData(itemRow, quantity));

      updateTotals();
    });
  };

  const handleIncrementButton = (item) => {
    item.addEventListener("click", function () {
      const itemRow = this.closest(".product__table_row");
      const quantityInput = itemRow.querySelector('input[type="number"]');
      let quantity = parseInt(quantityInput.value);

      quantity = isNaN(quantity) ? 1 : quantity + 1;
      quantityInput.value = quantity;
      itemRow.querySelector('input[type="checkbox"]').checked = true;

      initialObj.items = initialObj.items.filter(i => i.id !== itemRow.getAttribute("data-id"));
      initialObj.items.push(updateItemData(itemRow, quantity));

      updateTotals();
    });
  };

  const handleShippingMethod = (item) => {
    item.addEventListener("change", function () {
      const itemRow = this.closest(".shifting_table_row");
      const shippingCost = parseInt(itemRow.querySelector(".shiftingCost").textContent);
      initialObj.shippingCharge = shippingCost;
      updateTotals();
    });
  };

  const attachEventHandlers = (inputsArray) => {
    inputsArray.forEach(elements => {
      elements.forEach(element => {
        if (element.type === "number") handleInputChange(element);
        if (element.type === "checkbox") handleCheckboxChange(element);
        if (element.type === "radio") handleShippingMethod(element);
        if (element.classList.contains("minus")) handleDecrementButton(element);
        if (element.classList.contains("plus")) handleIncrementButton(element);
      });
    });
  };

  userForm.addEventListener('submit', function (e) {
    e.preventDefault();

    initialObj.shippingInfo.userName = document.getElementById('userName').value;
    initialObj.shippingInfo.userPhone = document.getElementById('userPhone').value;
    initialObj.shippingInfo.userEmail = document.getElementById('userEmail').value;
    initialObj.shippingInfo.userAddress = document.getElementById('userAddress').value;
    initialObj.orderNote = orderNote.value;

    document.getElementById('orderData').value = JSON.stringify(initialObj);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', orderfly_api.ajaxurl, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          alert(response.success ? 'Order saved successfully.' : 'Failed to save order.');
          // if(response.success) {
          //   alert('Order saved successfully.');
          //   console.log('Order saved successfully.');

          //   const pdf_url = response.data.pdf_url;
          //   const order_id = response.data.order_id;
            

          //   viewInvoice.style.display = 'block';
          //   downloadInvoice.setAttribute('href', pdf_url);
          //   downloadInvoice.setAttribute('download', 'invoice_' + order_id + '.pdf');

          //   printInvoice.addEventListener('click', function () {
          //     window.open(pdf_url, '_blank').print();
          //   });
            
          // } else {
          //   alert('Failed to save order.');
          //   console.log('Failed to save order.');
          // }
        } else {
          alert('An error occurred.');
        }
      }
    };

    xhr.send(`action=save_order_data&orderData=${encodeURIComponent(document.getElementById('orderData').value)}`);

  });

  attachEventHandlers([checkBoxes, inputBoxes, minusButtons, plusButtons, radioBoxes]);
});
