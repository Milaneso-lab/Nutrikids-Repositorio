(function () {
    'use strict';

    function initUserMenu() {
        var menu = document.getElementById('parentUserMenu');
        if (!menu) return;

        var trigger = document.getElementById('parentUserMenuTrigger');
        var panel = document.getElementById('parentUserMenuPanel');
        if (!trigger || !panel) return;

        function closeMenu() {
            menu.classList.remove('user-menu--open');
            trigger.setAttribute('aria-expanded', 'false');
            panel.setAttribute('hidden', '');
        }

        function openMenu() {
            menu.classList.add('user-menu--open');
            trigger.setAttribute('aria-expanded', 'true');
            panel.removeAttribute('hidden');
        }

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            if (menu.classList.contains('user-menu--open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        panel.querySelectorAll('a, button').forEach(function (item) {
            item.addEventListener('click', function () {
                closeMenu();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserMenu);
    } else {
        initUserMenu();
    }
})();
