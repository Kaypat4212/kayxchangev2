    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    
    <!-- Main Template JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- Bootstrap Navigation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Disable old mobile nav functions that conflict with Bootstrap
            if (window.main) {
                // Remove old mobile nav event listeners
                const oldToggles = document.querySelectorAll('.mobile-nav-toggle');
                oldToggles.forEach(toggle => {
                    const newToggle = toggle.cloneNode(true);
                    toggle.parentNode.replaceChild(newToggle, toggle);
                });
            }

            // Header scroll effect (safe version)
            const header = document.querySelector('header, .header, #header');
            if (header) {
                function toggleScrolled() {
                    if (window.scrollY > 100) {
                        header.classList.add('header-scrolled');
                    } else {
                        header.classList.remove('header-scrolled');
                    }
                }
                
                window.addEventListener('scroll', toggleScrolled);
                toggleScrolled(); // Call once on load
            }

            // Only close navbar when clicking nav links (not the toggler)
            document.addEventListener('click', function(e) {
                // Don't interfere with Bootstrap's toggle functionality
                if (e.target.closest('.navbar-toggler')) {
                    return;
                }
                
                // Close navbar when clicking nav links (for better UX on mobile)
                if (e.target.matches('.navbar-nav .nav-link:not(.dropdown-toggle)')) {
                    const navbar = document.querySelector('.navbar-collapse.show');
                    if (navbar) {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbar);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                    }
                }
            });
        });
    </script>