(function () {
	'use strict';

	var config = window.SabriShellPublishingDashboard || {};
	var url = typeof config.url === 'string' ? config.url : '';
	var label = typeof config.label === 'string' ? config.label : 'Publishing Dashboard';
	var sectionLabel = typeof config.sectionLabel === 'string' ? config.sectionLabel : 'Publishing';
	var observer = null;
	var stopped = false;

	if (!url) {
		return;
	}

	function createLink(location) {
		var link = document.createElement('a');
		link.href = url;
		link.textContent = label;
		link.setAttribute('data-sabri-publishing-dashboard-entry', location);
		link.className = 'sabri-shell-publishing-dashboard-link';
		try {
			var target = new URL(url, window.location.href);
			var current = new URL(window.location.href);
			if (target.origin === current.origin && target.pathname.replace(/\/$/, '') === current.pathname.replace(/\/$/, '')) {
				link.setAttribute('aria-current', 'page');
			}
		} catch (error) {
			// Server-side same-site validation is authoritative. A parsing failure
			// merely omits the cosmetic active state.
		}
		return link;
	}

	function mountAccountMenu() {
		var profile = document.querySelector('.sabri-shell-profile');
		if (!profile || profile.querySelector('[data-sabri-publishing-dashboard-entry="account-menu"]')) {
			return false;
		}
		var link = createLink('account-menu');
		var logout = profile.querySelector('a[href*="action=logout"], a[href*="wp-login.php?action=logout"]');
		if (logout && logout.parentNode === profile) {
			profile.insertBefore(link, logout);
		} else {
			profile.appendChild(link);
		}
		return true;
	}

	function mountSidebar() {
		var mounted = false;
		document.querySelectorAll('.sabri-shell-left-sidebar').forEach(function (sidebar) {
			if (sidebar.querySelector('[data-sabri-publishing-dashboard-entry="sidebar"]')) {
				return;
			}
			var userCard = sidebar.querySelector('.sabri-shell-user-card');
			if (!userCard) {
				return;
			}
			var nav = document.createElement('nav');
			nav.className = 'sabri-shell-account-tools';
			nav.setAttribute('aria-label', sectionLabel);
			var heading = document.createElement('span');
			heading.className = 'sabri-shell-account-tools__label';
			heading.textContent = sectionLabel;
			var link = createLink('sidebar');
			nav.appendChild(heading);
			nav.appendChild(link);
			userCard.insertAdjacentElement('afterend', nav);
			mounted = true;
		});
		return mounted;
	}

	function sync() {
		if (stopped) {
			return;
		}
		var accountReady = mountAccountMenu();
		var sidebarReady = mountSidebar();
		if ((accountReady || document.querySelector('[data-sabri-publishing-dashboard-entry="account-menu"]'))
			&& (sidebarReady || document.querySelector('[data-sabri-publishing-dashboard-entry="sidebar"]'))
		) {
			stopObserver();
		}
	}

	function stopObserver() {
		stopped = true;
		if (observer) {
			observer.disconnect();
			observer = null;
		}
	}

	function start() {
		sync();
		if (stopped || typeof MutationObserver === 'undefined') {
			return;
		}
		observer = new MutationObserver(sync);
		observer.observe(document.documentElement, { childList: true, subtree: true });
		window.setTimeout(stopObserver, 5000);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start, { once: true });
	} else {
		start();
	}
	window.addEventListener('pageshow', function () {
		stopped = false;
		start();
	});
}());
