const ThemeManager = {
    getCurrentTheme() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            return savedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: light)').matches ? 'dark' : 'light';
    },
  
    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
      
        const themeIcon = document.querySelector('.theme-toggle .icon');
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.style.left = 'calc(100% - 18px)';
            } else {
                themeIcon.style.left = '4px';
            }
            
            setTimeout(() => {
                themeIcon.className = theme === 'dark' ? 'fas fa-sun icon' : 'fas fa-moon icon';
            }, 150);
        }
    },
  
    toggleTheme() {
        const currentTheme = this.getCurrentTheme();
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.classList.add('theme-transitioning');
            
            setTimeout(() => {
                themeToggle.classList.remove('theme-transitioning');
            }, 300);
        }
        
        this.setTheme(newTheme);
    },
  
    init() {
        const initialTheme = this.getCurrentTheme();
        document.documentElement.setAttribute('data-theme', initialTheme);
        localStorage.setItem('theme', initialTheme);
        
        const themeIcon = document.querySelector('.theme-toggle .icon');
        if (themeIcon) {
            themeIcon.style.transition = 'none';
            if (initialTheme === 'dark') {
                themeIcon.style.left = 'calc(100% - 18px)';
                themeIcon.className = 'fas fa-sun icon';
            } else {
                themeIcon.style.left = '4px';
                themeIcon.className = 'fas fa-moon icon';
            }
            setTimeout(() => {
                themeIcon.style.transition = 'all 0.3s ease';
            }, 100);
        }
  
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
  
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
};
  
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
});