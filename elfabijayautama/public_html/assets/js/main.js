// Form validation and notifications
function showNotification(message, type = 'error') {
    // Remove any existing notifications
    const existingNotification = document.querySelector('.form-notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `form-notification ${type}`;
    notification.textContent = message;
    
    // Add to form
    const form = document.querySelector('.contact-form');
    if (form) {
        form.insertBefore(notification, form.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const nav = document.querySelector('.nav');
    const navToggle = document.querySelector('.nav-toggle');
    const navList = document.getElementById('primary-navigation');
    const navOverlay = document.getElementById('nav-overlay');
    const header = document.querySelector('.site-header');
    const html = document.documentElement;
    let isMenuOpen = false;
    let lastScroll = 0;
    
    // Initialize menu state
    if (navList) {
        navList.setAttribute('data-visible', 'false');
        // Add animation classes to menu items
        const menuItems = navList.querySelectorAll('li');
        menuItems.forEach((item, index) => {
            item.style.animationDelay = `${0.1 * index}s`;
            item.classList.add('menu-item-animate');
        });
    }
    
    if (navOverlay) {
        navOverlay.setAttribute('data-visible', 'false');
    }
    
    // Header scroll effect
    function handleScroll() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            header.classList.remove('scrolled-up');
            return;
        }
        
        if (currentScroll > lastScroll && !header.classList.contains('scrolled-down')) {
            // Scroll down
            header.classList.remove('scrolled-up');
            header.classList.add('scrolled-down');
        } else if (currentScroll < lastScroll && header.classList.contains('scrolled-down')) {
            // Scroll up
            header.classList.remove('scrolled-down');
            header.classList.add('scrolled-up');
        }
        
        lastScroll = currentScroll;
        
        // Add scrolled class when not at top
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const headerHeight = document.querySelector('.site-header').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (isMenuOpen) {
                    toggleMenu();
                }
            }
        });
    });
    
    // Initialize scroll events
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll(); // Run once on load

    // Function to toggle menu
    function toggleMenu() {
        isMenuOpen = !isMenuOpen;
        
        // Toggle aria attributes
        if (navToggle) {
            navToggle.setAttribute('aria-expanded', isMenuOpen);
        }
        if (navList) {
            navList.setAttribute('data-visible', isMenuOpen);
            navList.style.display = isMenuOpen ? 'flex' : 'none';
        }
        if (navOverlay) {
            navOverlay.setAttribute('data-visible', isMenuOpen);
            navOverlay.style.display = isMenuOpen ? 'block' : 'none';
        }
        
        // Toggle body scroll
        document.body.style.overflow = isMenuOpen ? 'hidden' : '';
        
        // Add/remove no-scroll class to body
        if (isMenuOpen) {
            document.body.classList.add('menu-open');
            // Focus management
            document.addEventListener('keydown', handleEscape);
            if (navList) {
                const firstNavItem = navList.querySelector('a');
                if (firstNavItem) firstNavItem.focus();
            }
        } else {
            document.body.classList.remove('menu-open');
            document.removeEventListener('keydown', handleEscape);
            if (navToggle) navToggle.focus();
        }
    }

    // Close menu when clicking outside
    function handleClickOutside(e) {
        if (isMenuOpen && !nav.contains(e.target) && e.target !== navToggle) {
            toggleMenu();
        }
    }

    // Close menu on escape key
    function handleEscape(e) {
        if (e.key === 'Escape' && isMenuOpen) {
            toggleMenu();
        }
    }

    // Close menu when clicking on overlay
    function closeOnOverlayClick(e) {
        if (e.target === navOverlay) {
            toggleMenu();
        }
    }

    // Close menu when clicking on a nav link
    function closeOnNavLinkClick(e) {
        if (e.target.matches('.nav-list a')) {
            toggleMenu();
        }
    }

    // Initialize menu functionality
    if (navToggle && navList) {
        // Toggle menu on button click
        navToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
            
            // Toggle body scroll lock
            if (isMenuOpen) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
        
        // Close when clicking outside
        document.addEventListener('click', handleClickOutside);
        
        // Close when clicking overlay
        if (navOverlay) {
            navOverlay.addEventListener('click', closeOnOverlayClick);
        }
        
        // Close when clicking a nav link
        navList.addEventListener('click', function(e) {
            if (e.target.closest('a')) {
                closeOnNavLinkClick(e);
                // Close menu after a short delay to allow click to register
                setTimeout(() => {
                    if (isMenuOpen) {
                        toggleMenu();
                    }
                }, 100);
            }
        });
        
        // Handle keyboard navigation
        navList.addEventListener('keydown', function(e) {
            if (!isMenuOpen) return;
            
            const menuItems = Array.from(navList.querySelectorAll('a'));
            const currentIndex = menuItems.indexOf(document.activeElement);
            
            switch(e.key) {
                case 'ArrowDown':
                case 'ArrowRight':
                    e.preventDefault();
                    const nextIndex = (currentIndex + 1) % menuItems.length;
                    menuItems[nextIndex].focus();
                    break;
                case 'ArrowUp':
                case 'ArrowLeft':
                    e.preventDefault();
                    const prevIndex = (currentIndex - 1 + menuItems.length) % menuItems.length;
                    menuItems[prevIndex].focus();
                    break;
                case 'Escape':
                    toggleMenu();
                    navToggle.focus();
                    break;
                case 'Tab':
                    if (currentIndex === menuItems.length - 1 && !e.shiftKey) {
                        e.preventDefault();
                        menuItems[0].focus();
                    } else if (currentIndex === 0 && e.shiftKey) {
                        e.preventDefault();
                        menuItems[menuItems.length - 1].focus();
                    }
                    break;
            }
        });
    }
});

// Portfolio filtering
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioGrid = document.getElementById('portfolioGrid');
    if (filterButtons.length && portfolioGrid) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.getAttribute('data-filter');
                
                // Add smooth transition for filtering
                portfolioGrid.style.opacity = '0.5';
                portfolioGrid.style.transition = 'opacity 0.3s ease';
                
                setTimeout(() => {
                    [...portfolioGrid.children].forEach(card => {
                        const cat = card.getAttribute('data-category');
                        card.style.display = (filter === 'all' || filter === cat) ? '' : 'none';
                    });
                    
                    // Trigger reflow for smooth animation
                    void portfolioGrid.offsetHeight;
                    portfolioGrid.style.opacity = '1';
                }, 150);
            });
        });
    }
});

// Lightbox functionality
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxClient = document.getElementById('lightboxClient');
    const lightboxDate = document.getElementById('lightboxDate');
    const lightboxServices = document.getElementById('lightboxServices');
    const lightboxClose = document.getElementById('lightboxClose');
    const portfolioGrid = document.getElementById('portfolioGrid');
    
    if (!lightbox) return;
    
    // Function to open lightbox
    function openLightbox(card) {
        lightboxImg.src = card.dataset.image || 'https://picsum.photos/1200/800';
        lightboxTitle.textContent = card.dataset.title || '';
        lightboxCaption.textContent = card.dataset.desc || '';
        lightboxClient.textContent = card.dataset.client || 'N/A';
        lightboxDate.textContent = card.dataset.date || 'N/A';
        lightboxServices.textContent = card.dataset.services || 'N/A';
        
        // Add animation class
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus trap for accessibility
        lightbox.setAttribute('aria-hidden', 'false');
        lightboxClose.focus();
    }
    
    // Function to close lightbox
    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        lightbox.setAttribute('aria-hidden', 'true');
    }
    
    // Open lightbox when clicking on portfolio card
    if (portfolioGrid) {
        portfolioGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.portfolio-card');
            if (card) {
                e.preventDefault();
                openLightbox(card);
            }
        });
    }
    
    // Close lightbox when clicking close button or overlay
    if (lightboxClose) {
        lightboxClose.addEventListener('click', (e) => {
            e.stopPropagation();
            closeLightbox();
        });
        
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.getAttribute('aria-hidden') === 'false') {
                closeLightbox();
            }
        });
    }
});
