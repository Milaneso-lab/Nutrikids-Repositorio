(function () {
  'use strict';

  function initPublicNav() {
    var toggle = document.getElementById('publicNavToggle');
    var menu = document.getElementById('publicMainNav');
    if (!toggle || !menu) {
      return;
    }

    function closeMenu() {
      menu.classList.remove('menu--open');
      toggle.setAttribute('aria-expanded', 'false');
    }

    function openMenu() {
      menu.classList.add('menu--open');
      toggle.setAttribute('aria-expanded', 'true');
    }

    toggle.addEventListener('click', function () {
      if (menu.classList.contains('menu--open')) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 992px)').matches) {
          closeMenu();
        }
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeMenu();
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 992) {
        closeMenu();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPublicNav);
  } else {
    initPublicNav();
  }
})();
