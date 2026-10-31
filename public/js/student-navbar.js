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

    const avatarBtn = document.getElementById('dash-avatar-btn');
    const avatarWrapper = document.getElementById('dash-avatar-wrapper');

    if (!avatarBtn || !avatarWrapper) {
        return;
    }

    if (avatarBtn.dataset.navBound === '1') {
        return;
    }
    avatarBtn.dataset.navBound = '1';

    const closeMenu = () => {
        avatarWrapper.classList.remove('is-open');
        avatarBtn.setAttribute('aria-expanded', 'false');
    };

    avatarBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = avatarWrapper.classList.toggle('is-open');
        avatarBtn.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (event) => {
        if (!avatarWrapper.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && avatarWrapper.classList.contains('is-open')) {
            closeMenu();
            avatarBtn.focus();
        }
    });
});
