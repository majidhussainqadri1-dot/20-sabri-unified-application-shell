(function () {
    'use strict';

    var config = window.SabriShellFourPlan || {};
    var welcome = config.welcome || {};
    var storageKey = typeof welcome.storageKey === 'string' ? welcome.storageKey : 'sabriShellWelcomeDismissedAt';
    var sessionKey = typeof welcome.sessionKey === 'string' ? welcome.sessionKey : 'sabriShellWelcomeSeenSession';
    var interval = Number(welcome.intervalSeconds || (30 * 24 * 60 * 60));
    var resizeTimer = 0;

    function nowSeconds() {
        return Math.floor(Date.now() / 1000);
    }

    function storageRecentlyDismissed() {
        try {
            var value = Number(window.localStorage.getItem(storageKey) || 0);
            return value > 0 && (nowSeconds() - value) < interval;
        } catch (error) {
            return false;
        }
    }

    function sessionSeen() {
        try {
            return window.sessionStorage.getItem(sessionKey) === '1';
        } catch (error) {
            return false;
        }
    }

    function markSessionSeen() {
        try {
            window.sessionStorage.setItem(sessionKey, '1');
        } catch (error) {
            /* Storage failure must never block the site. */
        }
    }

    function rememberLocally() {
        try {
            window.localStorage.setItem(storageKey, String(nowSeconds()));
        } catch (error) {
            /* Storage failure must never block the site. */
        }
        markSessionSeen();
    }

    function notifyServer() {
        if (!welcome.ajaxUrl || !welcome.action || !welcome.nonce || typeof window.fetch !== 'function') {
            return;
        }
        var body = new URLSearchParams();
        body.set('action', welcome.action);
        body.set('nonce', welcome.nonce);
        window.fetch(welcome.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).catch(function () {
            /* Server persistence failure is non-blocking; local fallback remains. */
        });
    }

    function dismiss() {
        rememberLocally();
        notifyServer();
    }

    function reconcileWelcome() {
        var nodes = document.querySelectorAll('[data-sabri-welcome-intro]');
        if (!nodes.length) {
            return;
        }
        if (storageRecentlyDismissed() || sessionSeen()) {
            nodes.forEach(function (node) {
                node.hidden = true;
                node.setAttribute('aria-hidden', 'true');
            });
            return;
        }
        markSessionSeen();
    }

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

    document.addEventListener('click', function (event) {
        var trigger = event.target && event.target.closest ? event.target.closest('[data-sabri-welcome-dismiss]') : null;
        if (trigger) {
            dismiss();
        }
    }, true);

    document.addEventListener('sabri:welcome-dismissed', dismiss);
    document.addEventListener('sabri:welcome-continued', dismiss);
    document.addEventListener('sabri:welcome-skipped', dismiss);

    function ready() {
        reconcileWelcome();
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
