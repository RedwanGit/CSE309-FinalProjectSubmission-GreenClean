
document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
    const requiredFields = ['address', 'city', 'state', 'zip'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });

    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return;
    }

    // Validate ZIP code format
    const zip = document.getElementById('zip');
    if (!/^\d{5}$/.test(zip.value)) {
        e.preventDefault();
        zip.classList.add('error');
        alert('Please enter a valid 5-digit ZIP code.');
    }
});
