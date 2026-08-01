(function () {
	'use strict';

	var repaired = false;
	var observer = null;
	var selectors = 'main.site-main, main#main, .site-main, #primary, .content-area, main, #content, .site-content';

	function desktopSidebar(selector) {
		return document.querySelector(selector + ':not(.sabri-shell-left-sidebar-drawer):not(.sabri-shell-right-sidebar-drawer)');
	}

	function safeTarget(element) {
		if (!element || element === document.body || element === document.documentElement) {
			return false;
		}
		if (
			element.matches('.wp-site-blocks, #page, .site, [data-sabri-shell-component]') ||
			element.closest('[data-sabri-shell-component]') ||
			element.querySelector('[data-sabri-shell-component]')
		) {
			return false;
		}
		return true;
	}

	function findManagedTarget() {
		if (!document.body.classList.contains('sabri-hnf-managed-single')) {
			return null;
		}
		var managed = document.querySelector('.sabri-hnf-single-content');
		if (!managed) {
			return null;
		}
		var node = managed.parentElement;
		while (node && node !== document.body && node !== document.documentElement) {
			if (node.matches(selectors) && safeTarget(node)) {
				return node;
			}
			node = node.parentElement;
		}
		return null;
	}

	function applyTarget(target) {
		if (!target || repaired) {
			return false;
		}

		// A managed single publication must have exactly one File 20 content
		// column. Remove only File 20's annotation from stale candidates; never
		// move, replace, or delete theme/companion nodes.
		document.querySelectorAll('.sabri-shell-content-column[data-sabri-shell-content-column="true"]').forEach(function (candidate) {
			if (candidate !== target) {
				candidate.classList.remove('sabri-shell-content-column');
				candidate.removeAttribute('data-sabri-shell-content-column');
			}
		});

		target.classList.add('sabri-shell-content-column');
		target.setAttribute('data-sabri-shell-content-column', 'true');
		document.body.classList.remove('sabri-shell-layout-unresolved');
		document.body.classList.toggle('sabri-shell-has-left-sidebar', Boolean(desktopSidebar('.sabri-shell-left-sidebar')));
		document.body.classList.toggle('sabri-shell-has-right-sidebar', Boolean(desktopSidebar('.sabri-shell-right-sidebar')));
		document.body.classList.add('sabri-shell-layout-ready', 'sabri-shell-managed-single-layout-repaired');
		repaired = true;
		window.dispatchEvent(new Event('resize'));
		if (observer) {
			observer.disconnect();
			observer = null;
		}
		return true;
	}

	function attemptRepair() {
		return applyTarget(findManagedTarget());
	}

	function start() {
		if (!document.body.classList.contains('sabri-hnf-managed-single')) {
			return;
		}
		if (attemptRepair()) {
			return;
		}
		if ('MutationObserver' in window) {
			observer = new MutationObserver(function () {
				attemptRepair();
			});
			observer.observe(document.body, { childList: true, subtree: true });
			window.setTimeout(function () {
				if (observer) {
					observer.disconnect();
					observer = null;
				}
			}, 3000);
		}
		[100, 350, 900, 1800].forEach(function (delay) {
			window.setTimeout(attemptRepair, delay);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start, { once: true });
	} else {
		start();
	}
}());
