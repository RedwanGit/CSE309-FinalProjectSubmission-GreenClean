
function filterCategory(category) {
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

function showNotification(message, type) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.classList.add('show');

    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

function showCartStatus(productId) {
    const status = document.getElementById(`status-${productId}`);
    status.classList.add('show');
    setTimeout(() => {
        status.classList.remove('show');
    }, 2000);
}

async function addToCart(productId) {
const button = document.getElementById(`btn-${productId}`);
button.classList.add('loading');
button.disabled = true;

const formData = new FormData();
formData.append('ajax_add_to_cart', '1');
formData.append('product_id', productId);
formData.append('quantity', '1');

try {
    // First check stock
    const stockCheck = await fetch(`check_stock.php?product_id=${productId}&quantity=1`);
    const stockData = await stockCheck.json();
    
    if (!stockData.available) {
        showNotification(stockData.message, 'error');
        return;
    }

    const response = await fetch('productPage.php', {
        method: 'POST',
        body: formData
    });

    const data = await response.json();
    
    if (data.success) {
        showNotification(data.message, 'success');
        showCartStatus(productId);
    } else {
        if (data.message === 'Please login first') {
            window.location.href = 'authpage.php';
            return;
        }
        showNotification(data.message, 'error');
    }
} catch (error) {
    showNotification('An error occurred', 'error');
} finally {
    button.classList.remove('loading');
    button.disabled = false;
}
}
