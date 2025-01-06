
function showDeleteConfirm(messageId, authorName) {
    document.getElementById('messageAuthor').textContent = authorName;
    document.getElementById('deleteConfirmDialog').classList.add('active');
    
    document.getElementById('confirmDeleteBtn').onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="message_id" value="${messageId}">
            <input type="hidden" name="delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    };
}

function hideDeleteConfirm() {
    document.getElementById('deleteConfirmDialog').classList.remove('active');
}

document.getElementById('deleteConfirmDialog').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteConfirm();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('deleteConfirmDialog').classList.contains('active')) {
        hideDeleteConfirm();
    }
});
