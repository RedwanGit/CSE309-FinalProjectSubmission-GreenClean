
document.addEventListener('DOMContentLoaded', () => {
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    });
});

function toggleForms() {
    const [loginForm, registerForm] = ['loginForm', 'registerForm'].map(id => document.getElementById(id));
    const notifications = document.querySelectorAll('.notification');
    
    notifications.forEach(notification => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });

    loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
    registerForm.style.display = registerForm.style.display === 'none' ? 'block' : 'none';
}
