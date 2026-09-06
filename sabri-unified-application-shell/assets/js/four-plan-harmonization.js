(function () {
    'use strict';

    var resizeTimer = 0;

    function rebalanceNavigation() {
        var nav = document.querySelector('.sabri-shell-primary-nav');
        if (!nav || window.matchMedia('(max-width: 1023px)').matches) {
            return;
        }
        var list = nav.querySelector('ul');
        if (!list) {
            return;
        }
        var more = Array.prototype.filter.call(list.children, function (child) {
            return child.classList && child.classList.contains('sabri-shell-nav-more');
        })[0];
        if (!more) {
            return;
        }
        var menu = more.querySelector('.sabri-shell-nav-more-menu');
        if (!menu) {
            return;
        }

        var direct = Array.prototype.filter.call(list.children, function (child) {
            return child !== more;
        });
        while (list.scrollWidth > list.clientWidth + 1 && direct.length > 4) {
            var item = direct.pop();
            item.setAttribute('data-sabri-nav-overflow-moved', '1');
            menu.insertBefore(item, menu.firstChild);
        }
    }

    function ready() {
        rebalanceNavigation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready, { once: true });
    } else {
        ready();
    }

    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(rebalanceNavigation, 80);
    }, { passive: true });
}());
