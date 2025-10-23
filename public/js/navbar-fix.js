// Clean Bootstrap Navbar Fix - Simple and targeted

document.addEventListener('DOMContentLoaded', function() {
    
    // Remove any old mobile nav toggles that might conflict
    const oldToggles = document.querySelectorAll('.mobile-nav-toggle');
    oldToggles.forEach(toggle => toggle.remove());

    // Remove navbar-mobile classes that might interfere
    document.querySelectorAll('.navbar-mobile').forEach(el => {
        el.classList.remove('navbar-mobile');
    });

    // Ensure Bootstrap navbar toggler works properly
    const navbarTogglers = document.querySelectorAll('.navbar-toggler[data-bs-toggle="collapse"]');
    
    navbarTogglers.forEach(toggler => {
        // Make sure it has the correct Bootstrap behavior
        toggler.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const targetSelector = this.getAttribute('data-bs-target');
            const target = document.querySelector(targetSelector);
            
            if (target) {
                // Let Bootstrap handle the toggle
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(target);
                bsCollapse.toggle();
            }
        });
    });

    // Auto-close navbar when clicking nav links (for mobile)
    document.addEventListener('click', function(e) {
        if (e.target.matches('.nav-link') && !e.target.closest('.navbar-toggler')) {
            const openNavbar = document.querySelector('.navbar-collapse.show');
            if (openNavbar && window.innerWidth < 992) {
                const bsCollapse = bootstrap.Collapse.getInstance(openNavbar);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
        }
    });

    console.log('Clean Bootstrap navbar fix applied');
});