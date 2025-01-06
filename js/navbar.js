function initializeDropdown() {
    const accountDropdown = document.querySelector('.account-dropdown');
    if (accountDropdown) {
        accountDropdown.addEventListener('click', function(e) {
            const dropdownContent = this.querySelector('.dropdown-content');
            dropdownContent.classList.toggle('show');
            
            document.addEventListener('click', function(e) {
                if (!accountDropdown.contains(e.target)) {
                    dropdownContent.classList.remove('show');
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    highlightActivePage();
    initializeDropdown();
});
