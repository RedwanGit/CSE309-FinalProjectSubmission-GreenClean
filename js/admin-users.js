document.addEventListener('DOMContentLoaded', function() {
    initializePopupMessages();
    initializeEditableFields();
    initializeDeleteButtons();
});

function initializePopupMessages() {
    const popupMessage = document.getElementById('popupMessage');
    if (popupMessage) {
        popupMessage.style.display = 'block';
        setTimeout(() => {
            popupMessage.style.opacity = '0';
            setTimeout(() => {
                popupMessage.style.display = 'none';
                popupMessage.style.opacity = '1';
            }, 300);
        }, 5000);
    }
}

function initializeEditableFields() {
    const editables = document.querySelectorAll('.editable');
    editables.forEach(editable => {
        editable.addEventListener('click', handleEditableClick);
    });
}

function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmDialog = document.getElementById('confirmDialog');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.userId;
            confirmDialog.classList.add('active');

            confirmYes.onclick = function() {
                window.location.href = `?delete=${userId}`;
            };

            confirmNo.onclick = function() {
                confirmDialog.classList.remove('active');
            };
        });
    });

    // Close dialog when clicking outside
    confirmDialog.addEventListener('click', function(e) {
        if (e.target === confirmDialog) {
            confirmDialog.classList.remove('active');
        }
    });
}

function handleEditableClick(event) {
    if (this.classList.contains('editing')) return;

    const field = this.dataset.field;
    const originalText = this.innerText;
    const input = createInputElement(field, originalText);

    this.innerHTML = '';
    this.appendChild(input);
    this.classList.add('editing');
    input.focus();

    input.addEventListener('blur', () => handleInputBlur(this, input, originalText));
}

function createInputElement(field, originalText) {
    if (field === 'is_admin') {
        const select = document.createElement('select');
        select.innerHTML = `
            <option value="0" ${originalText === 'User' ? 'selected' : ''}>User</option>
            <option value="1" ${originalText === 'Admin' ? 'selected' : ''}>Admin</option>
        `;
        return select;
    }

    const input = document.createElement('input');
    input.value = originalText;
    input.type = field === 'password' ? 'text' : 'text';
    return input;
}

function handleInputBlur(element, input, originalText) {
    const displayValue = element.dataset.field === 'is_admin' ? 
        (input.value === '1' ? 'Admin' : 'User') : 
        input.value;

    element.innerHTML = displayValue;
    element.classList.remove('editing');

    if (displayValue !== originalText) {
        updateField(element, input.value, originalText);
    }
}

function updateField(element, newValue, originalText) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', element.dataset.id);
    formData.append('field', element.dataset.field);
    formData.append('value', newValue);

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            showNotification(data.message, 'error');
            element.innerHTML = originalText;
        } else {
            showNotification('Updated successfully', 'success');
        }
    })
    .catch(() => {
        showNotification('Error updating field', 'error');
        element.innerHTML = originalText;
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `popup-message ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}