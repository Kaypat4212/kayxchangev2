// Enhanced Mobile Navigation JavaScript

class MobileNavigation {
    constructor() {
        this.init();
    }

    init() {
        this.setupElements();
        this.bindEvents();
        this.setupAccessibility();
        this.handleResize();
    }

    setupElements() {
        this.mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        this.navbar = document.querySelector('#navbar');
        this.body = document.body;
        this.header = document.querySelector('#header');
        
        // Create mobile nav panel if it doesn't exist
        this.createMobileNavPanel();
    }

    createMobileNavPanel() {
        if (!this.navbar) return;

        // Check if mobile nav panel already exists
        let existingPanel = document.querySelector('.mobile-nav-panel');
        if (existingPanel) return;

        // Create the mobile navigation structure
        const navbarMobile = document.createElement('div');
        navbarMobile.className = 'navbar-mobile';
        navbarMobile.setAttribute('role', 'dialog');
        navbarMobile.setAttribute('aria-label', 'Mobile Navigation');
        navbarMobile.setAttribute('aria-hidden', 'true');

        const panel = document.createElement('div');
        panel.className = 'mobile-nav-panel';

        // Create header
        const header = this.createMobileNavHeader();
        panel.appendChild(header);

        // Clone the existing navigation menu
        const existingNav = this.navbar.querySelector('ul');
        if (existingNav) {
            const mobileNav = existingNav.cloneNode(true);
            this.processMobileNavItems(mobileNav);
            panel.appendChild(mobileNav);
        }

        // Create footer
        const footer = this.createMobileNavFooter();
        panel.appendChild(footer);

        navbarMobile.appendChild(panel);
        document.body.appendChild(navbarMobile);

        this.navbarMobile = navbarMobile;
        this.mobileNavPanel = panel;
    }

    createMobileNavHeader() {
        const header = document.createElement('div');
        header.className = 'mobile-nav-header';

        // Get logo info from main header
        const mainLogo = document.querySelector('.logo');
        let logoSrc = '/Assests/favicon.png';
        let logoText = 'KayXchange';
        let logoSubtext = 'Crypto Exchange';

        if (mainLogo) {
            const img = mainLogo.querySelector('img');
            const span = mainLogo.querySelector('span');
            
            if (img) logoSrc = img.src;
            if (span) {
                logoText = span.textContent;
                logoSubtext = span.textContent.includes('Admin') ? 'Admin Panel' : 'Crypto Exchange';
            }
        }

        header.innerHTML = `
            <i class="bi bi-x mobile-nav-toggle" aria-label="Close navigation"></i>
            <div class="mobile-nav-logo">
                <img src="${logoSrc}" alt="${logoText}" width="40" height="40">
                <div>
                    <h4 class="mobile-nav-title">${logoText}</h4>
                    <p class="mobile-nav-subtitle">${logoSubtext}</p>
                </div>
            </div>
        `;

        return header;
    }

    createMobileNavFooter() {
        const footer = document.createElement('div');
        footer.className = 'mobile-nav-footer';
        footer.innerHTML = `
            <p>&copy; ${new Date().getFullYear()} KayXchange. All rights reserved.</p>
        `;
        return footer;
    }

    processMobileNavItems(nav) {
        const items = nav.querySelectorAll('li');
        
        items.forEach(item => {
            const link = item.querySelector('a');
            if (!link) return;

            // Add icons to navigation items
            this.addNavigationIcon(link);

            // Handle forms (like logout button)
            const form = item.querySelector('form');
            if (form) {
                const button = form.querySelector('button');
                if (button) {
                    button.className = 'getstarted';
                    button.type = 'submit';
                }
            }

            // Handle dropdown menus
            const dropdown = item.querySelector('.dropdown');
            if (dropdown) {
                this.setupMobileDropdown(item);
            }
        });
    }

    addNavigationIcon(link) {
        const href = link.getAttribute('href') || '';
        const text = link.textContent.toLowerCase();
        let icon = 'fas fa-circle';

        // Map common navigation items to icons
        const iconMap = {
            'dashboard': 'fas fa-tachometer-alt',
            'deposit': 'fas fa-plus-circle',
            'deposits': 'fas fa-history',
            'rate': 'fas fa-chart-line',
            'rates': 'fas fa-chart-line',
            'buy': 'fas fa-shopping-cart',
            'sell': 'fas fa-money-bill-wave',
            'settings': 'fas fa-cog',
            'referrals': 'fas fa-users',
            'users': 'fas fa-users',
            'trades': 'fas fa-exchange-alt',
            'crypto': 'fab fa-bitcoin',
            'blog': 'fas fa-blog',
            'faqs': 'fas fa-question-circle',
            'about': 'fas fa-info-circle',
            'home': 'fas fa-home',
            'login': 'fas fa-sign-in-alt',
            'register': 'fas fa-user-plus',
            'logout': 'fas fa-sign-out-alt'
        };

        // Find matching icon
        for (const [key, iconClass] of Object.entries(iconMap)) {
            if (text.includes(key) || href.includes(key)) {
                icon = iconClass;
                break;
            }
        }

        // Add icon to link
        if (!link.querySelector('i')) {
            const iconElement = document.createElement('i');
            iconElement.className = icon;
            link.insertBefore(iconElement, link.firstChild);
        }
    }

    setupMobileDropdown(item) {
        const link = item.querySelector('a');
        const submenu = item.querySelector('ul');
        
        if (link && submenu) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                item.classList.toggle('dropdown-active');
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
            });
        }
    }

    bindEvents() {
        // Toggle button click
        if (this.mobileNavToggle) {
            this.mobileNavToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMobileNav();
            });
        }

        // Close button in mobile nav
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('mobile-nav-toggle') && 
                e.target.classList.contains('bi-x')) {
                this.closeMobileNav();
            }
        });

        // Close on overlay click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('navbar-mobile')) {
                this.closeMobileNav();
            }
        });

        // Close on navigation link click
        document.addEventListener('click', (e) => {
            if (e.target.matches('.navbar-mobile a:not(.dropdown > a)')) {
                // Don't close for dropdown toggles
                if (!e.target.parentElement.classList.contains('dropdown')) {
                    this.closeMobileNav();
                }
            }
        });

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobileNavOpen()) {
                this.closeMobileNav();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Handle orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleResize();
            }, 100);
        });
    }

    setupAccessibility() {
        if (this.mobileNavToggle) {
            this.mobileNavToggle.setAttribute('aria-label', 'Toggle mobile navigation');
            this.mobileNavToggle.setAttribute('aria-expanded', 'false');
        }
    }

    toggleMobileNav() {
        if (this.isMobileNavOpen()) {
            this.closeMobileNav();
        } else {
            this.openMobileNav();
        }
    }

    openMobileNav() {
        if (!this.navbarMobile) return;

        this.navbarMobile.classList.add('active');
        this.navbarMobile.setAttribute('aria-hidden', 'false');
        this.body.classList.add('mobile-nav-open');
        
        if (this.mobileNavToggle) {
            this.mobileNavToggle.classList.remove('bi-list');
            this.mobileNavToggle.classList.add('bi-x');
            this.mobileNavToggle.setAttribute('aria-expanded', 'true');
        }

        // Focus management
        setTimeout(() => {
            const firstLink = this.navbarMobile.querySelector('a, button');
            if (firstLink) firstLink.focus();
        }, 300);

        // Trap focus within mobile nav
        this.trapFocus();
    }

    closeMobileNav() {
        if (!this.navbarMobile) return;

        this.navbarMobile.classList.remove('active');
        this.navbarMobile.setAttribute('aria-hidden', 'true');
        this.body.classList.remove('mobile-nav-open');
        
        if (this.mobileNavToggle) {
            this.mobileNavToggle.classList.remove('bi-x');
            this.mobileNavToggle.classList.add('bi-list');
            this.mobileNavToggle.setAttribute('aria-expanded', 'false');
            this.mobileNavToggle.focus();
        }
    }

    isMobileNavOpen() {
        return this.navbarMobile && this.navbarMobile.classList.contains('active');
    }

    handleResize() {
        // Close mobile nav if window becomes wide enough
        if (window.innerWidth > 991 && this.isMobileNavOpen()) {
            this.closeMobileNav();
        }

        // Recreate mobile nav panel if structure changed
        if (window.innerWidth <= 991) {
            this.createMobileNavPanel();
        }
    }

    trapFocus() {
        if (!this.isMobileNavOpen()) return;

        const focusableElements = this.navbarMobile.querySelectorAll(
            'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const handleTabKey = (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        };

        document.addEventListener('keydown', handleTabKey);
        
        // Remove event listener when mobile nav is closed
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class' && 
                    !this.navbarMobile.classList.contains('active')) {
                    document.removeEventListener('keydown', handleTabKey);
                    observer.disconnect();
                }
            });
        });

        observer.observe(this.navbarMobile, { attributes: true });
    }
}

// Smooth scroll for anchor links
function smoothScrollTo(target) {
    const element = document.querySelector(target);
    if (element) {
        const headerHeight = document.querySelector('#header')?.offsetHeight || 0;
        const targetPosition = element.offsetTop - headerHeight - 20;
        
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
}

// Enhanced scroll behavior for hash links
document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (link) {
        const href = link.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            smoothScrollTo(href);
        }
    }
});

// Initialize mobile navigation when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MobileNavigation();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileNavigation;
}