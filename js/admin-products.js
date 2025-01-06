document.addEventListener('DOMContentLoaded', function() {
    initializeEditableFields();
    initializeNotifications();
});

function initializeEditableFields() {
    const editables = document.querySelectorAll('.editable');
    
    editables.forEach(editable => {
        editable.addEventListener('click', function() {
            if (this.classList.contains('editing')) return;
            
            const value = this.textContent;
            const field = this.dataset.field;
            const type = this.dataset.type;
            
            this.classList.add('editing');
            
            if (type === 'select') {
                const select = document.createElement('select');
                select.className = 'edit-input';
                select.innerHTML = `
                    <option value="household" ${value === 'household' ? 'selected' : ''}>Household</option>
                    <option value="personal" ${value === 'personal' ? 'selected' : ''}>Personal Care</option>
                `;
                this.textContent = '';
                this.appendChild(select);
                select.focus();
                
                select.addEventListener('change', function() {
                    updateField(this.parentElement, this.value);
                });
                
                select.addEventListener('blur', function() {
                    this.parentElement.classList.remove('editing');
                });
            } else {
                const input = document.createElement('input');
                input.type = field === 'price' ? 'number' : 'text';
                if (field === 'price') {
                    input.step = '0.01';
                    input.value = parseFloat(value.replace('$', ''));
                } else {
                    input.value = value;
                }
                input.className = 'edit-input';
                
                this.textContent = '';
                this.appendChild(input);
                input.focus();
                
                input.addEventListener('blur', function() {
                    updateField(this.parentElement, this.value);
                });
                
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            }
        });
    });
}

function updateField(element, value) {
    const productId = element.closest('tr').dataset.id;
    const field = element.dataset.field;
    
    fetch('products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `update_product=1&id=${productId}&field=${field}&value=${value}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            element.classList.remove('editing');
            if (field === 'price') {
                element.textContent = '$' + parseFloat(value).toFixed(2);
            } else {
                element.textContent = value;
            }
            showNotification('Updated successfully', 'success');
        } else {
            showNotification('Error updating field: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating field: ' + error, 'error');
    });
}

function deleteProduct(id) {
    const confirmDialog = document.getElementById('confirmDialog');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');

    confirmDialog.classList.add('active');

    confirmYes.onclick = function() {
        fetch('products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `delete_product=1&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.remove();
                }
                showNotification('Product deleted successfully', 'success');
            } else {
                showNotification('Error deleting product: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showNotification('Error deleting product: ' + error, 'error');
        })
        .finally(() => {
            confirmDialog.classList.remove('active');
        });
    };

    confirmNo.onclick = function() {
        confirmDialog.classList.remove('active');
    };

    confirmDialog.addEventListener('click', function(e) {
        if (e.target === confirmDialog) {
            confirmDialog.classList.remove('active');
        }
    });
}

function initializeNotifications() {
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}