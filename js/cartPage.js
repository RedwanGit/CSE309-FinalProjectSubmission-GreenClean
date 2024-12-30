function updateQuantity(productId, action) {
    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
    const input = row.querySelector('.quantity-input');
    const stock = parseInt(row.querySelector('.stock-amount').textContent);
    
    let newQuantity = parseInt(input.value);
    if (action === 'increase' && newQuantity < stock) {
        newQuantity++;
    } else if (action === 'decrease' && newQuantity > 1) {
        newQuantity--;
    }
    
    if (newQuantity !== parseInt(input.value)) {
        fetch('cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${newQuantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error updating cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating cart');
        });
    }
}

    function removeItem(productId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cartPage.php';
        
        const fields = {
            'product_id': productId,
            'action': 'remove'
        };
        
        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }

    async function proceedToCheckout() {
        try {
            const response = await fetch('checkout_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                window.location.href = 'checkoutPage.php?order_id=' + data.orderId;
            } else {
                alert(data.message || 'An error occurred during checkout');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred during checkout');
        }
    }
    