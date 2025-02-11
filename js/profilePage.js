
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        email: document.getElementById('email').value,
        username: document.getElementById('username').value,
        password: document.getElementById('password').value
    };

    try {
        const response = await fetch('profilePage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        
        const notification = document.getElementById('notification');
        notification.textContent = result.message;
        notification.className = 'notification ' + (result.success ? 'success' : 'error');
        
        if (result.success) {
            setTimeout(() => {
                notification.textContent = '';
                notification.className = 'notification';
            }, 3000);
        }

        if (result.success) {
            document.getElementById('password').value = ''; 
        }
    } catch (error) {
        console.error('Error:', error);
        const notification = document.getElementById('notification');
        notification.textContent = 'An error occurred. Please try again.';
        notification.className = 'notification error';
    }
});
