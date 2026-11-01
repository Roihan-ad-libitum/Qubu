document.addEventListener('DOMContentLoaded', () => {
    const navbars = document.querySelectorAll('.dash-navbar, .admin-panel-nav, .home-nav');
    if (navbars.length > 0 && !document.body.dataset.stickyNavBound) {
        document.body.dataset.stickyNavBound = '1';
        const syncScrollClass = () => {
            const scrollTop = window.scrollY
                || window.pageYOffset
                || document.documentElement.scrollTop
                || document.body.scrollTop
                || 0;
            const scrolled = scrollTop > 8;
            navbars.forEach((nav) => {
                nav.classList.toggle('is-scrolled', scrolled);
            });
        };
        syncScrollClass();
        window.addEventListener('scroll', syncScrollClass, { passive: true });
        document.addEventListener('scroll', syncScrollClass, { passive: true, capture: true });
    }

    const navbar = document.querySelector('.dash-navbar');
    const navMenu = document.getElementById('dash-nav-menu');
    const mobileToggle = document.getElementById('dash-mobile-toggle');
    const avatarBtn = document.getElementById('dash-avatar-btn');
    const avatarWrapper = document.getElementById('dash-avatar-wrapper');

    const closeAvatarMenu = () => {
        if (!avatarWrapper || !avatarBtn) return;
        avatarWrapper.classList.remove('is-open');
        avatarBtn.setAttribute('aria-expanded', 'false');
    };

    const closeMobileMenu = () => {
        if (!navbar || !mobileToggle) return;
        navbar.classList.remove('is-menu-open');
        if (navMenu) {
            navMenu.classList.remove('is-open');
        }
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileToggle.setAttribute('aria-label', 'Buka Menu Navigasi');
    };

    if (mobileToggle && navbar && mobileToggle.dataset.navBound !== '1') {
        mobileToggle.dataset.navBound = '1';

        mobileToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            closeAvatarMenu();
            const isOpen = navbar.classList.toggle('is-menu-open');
            if (navMenu) {
                navMenu.classList.toggle('is-open', isOpen);
            }
            mobileToggle.setAttribute('aria-expanded', String(isOpen));
            mobileToggle.setAttribute('aria-label', isOpen ? 'Tutup Menu Navigasi' : 'Buka Menu Navigasi');
        });

        if (navMenu) {
            navMenu.querySelectorAll('.dash-nav-link').forEach((link) => {
                link.addEventListener('click', () => {
                    closeMobileMenu();
                });
            });
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && navbar.classList.contains('is-menu-open')) {
                closeMobileMenu();
            }
        });
    }

    if (avatarBtn && avatarWrapper && avatarBtn.dataset.navBound !== '1') {
        avatarBtn.dataset.navBound = '1';

        avatarBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            closeMobileMenu();
            const isOpen = avatarWrapper.classList.toggle('is-open');
            avatarBtn.setAttribute('aria-expanded', String(isOpen));
        });
    }

    if (!document.body.dataset.navDismissBound) {
        document.body.dataset.navDismissBound = '1';

        document.addEventListener('click', (event) => {
            if (avatarWrapper && !avatarWrapper.contains(event.target)) {
                closeAvatarMenu();
            }
            if (navbar && !navbar.contains(event.target)) {
                closeMobileMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                if (avatarWrapper && avatarWrapper.classList.contains('is-open')) {
                    closeAvatarMenu();
                    if (avatarBtn) avatarBtn.focus();
                }
                if (navbar && navbar.classList.contains('is-menu-open')) {
                    closeMobileMenu();
                    if (mobileToggle) mobileToggle.focus();
                }
            }
        });
    }
});
