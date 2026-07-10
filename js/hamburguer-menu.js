document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-btn');
    const sidebar = document.getElementById('sidebar');
    const closeBtn = document.getElementById('close-btn');
    const overlay = document.getElementById('sidebar-overlay');

    const setMenuState = (isOpen) => {
        if (!sidebar) {
            return;
        }

        sidebar.classList.toggle('active', isOpen);
        overlay?.classList.toggle('active', isOpen);
        document.body.classList.toggle('menu-open', isOpen);

        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', String(isOpen));
        }
    };

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            setMenuState(true);
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            setMenuState(false);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            setMenuState(false);
        });
    }

    document.addEventListener('click', (event) => {
        if (!sidebar || !sidebar.classList.contains('active')) {
            return;
        }

        const clickedToggle = toggleBtn && toggleBtn.contains(event.target);
        if (!sidebar.contains(event.target) && !clickedToggle) {
            setMenuState(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuState(false);
        }
    });
});