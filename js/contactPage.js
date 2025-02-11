document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const submitButton = form.querySelector('.submit-btn');
    const contactContainer = document.querySelector('.contact-container');
    const originalFormHTML = contactContainer.innerHTML;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const errorMessages = form.querySelectorAll('.error-message');
        errorMessages.forEach(msg => msg.style.display = 'none');
        
        let isValid = true;
        const name = form.querySelector('#name');
        const email = form.querySelector('#email');
        const message = form.querySelector('#message');
        
        if (!name.value.trim()) {
            name.nextElementSibling.style.display = 'block';
            isValid = false;
        }
        
        if (!email.value.trim() || !email.value.includes('@')) {
            email.nextElementSibling.style.display = 'block';
            isValid = false;
        }
        
        if (!message.value.trim()) {
            message.nextElementSibling.style.display = 'block';
            isValid = false;
        }
        
        if (!isValid) return;

        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';

        try {
            const response = await fetch('contact_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: name.value.trim(),
                    email: email.value.trim(),
                    message: message.value.trim()
                })
            });

            const data = await response.json();
            
            if (data.success) {
                contactContainer.innerHTML = `
                    <div class="notification-container">
                        <div class="success-message">
                            Thank you! Your message has been sent successfully.
                        </div>
                    </div>
                `;
                
                setTimeout(() => {
                    contactContainer.innerHTML = originalFormHTML;
                    const newForm = contactContainer.querySelector('#contact-form');
                    const newSubmitButton = newForm.querySelector('.submit-btn');
                    newSubmitButton.disabled = false;
                    newSubmitButton.textContent = 'Send Message';
                    setupFormListener();
                }, 3000);
            } else {
                throw new Error(data.error || 'Something went wrong');
            }
        } catch (error) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-alert';
            errorDiv.textContent = error.message;
            form.insertBefore(errorDiv, form.firstChild);
            
            submitButton.disabled = false;
            submitButton.textContent = 'Send Message';
        }
    });

    function setupFormListener() {
        const newForm = document.getElementById('contact-form');
        if (newForm) {
            newForm.addEventListener('submit', form.onsubmit);
        }
    }
});