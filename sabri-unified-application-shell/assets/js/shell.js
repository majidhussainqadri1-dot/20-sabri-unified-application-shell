(function () {
	'use strict';

	var state = {
		openDrawer: null,
		lastFocus: null,
		inerted: []
	};

	function ready(callback) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', callback, { once: true });
			return;
		}
		callback();
	}

	function measureChrome() {
		var header = document.querySelector('.sabri-shell-header');
		var nav = document.querySelector('.sabri-shell-primary-nav');
		var headerHeight = header ? Math.ceil(header.getBoundingClientRect().height) : 0;
		var navHeight = nav ? Math.ceil(nav.getBoundingClientRect().height) : 0;
		document.body.style.setProperty('--sabri-shell-header-height', headerHeight + 'px');
		document.body.style.setProperty('--sabri-shell-chrome-height', (headerHeight + navHeight) + 'px');
	}

	function focusable(container) {
		return Array.prototype.slice.call(container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), summary, [tabindex]:not([tabindex="-1"])'))
			.filter(function (element) {
				return element.offsetParent !== null || element === document.activeElement;
			});
	}

	function setTriggerState(id, expanded) {
		document.querySelectorAll('[data-sabri-drawer-trigger="' + id + '"]').forEach(function (trigger) {
			var openLabel = trigger.getAttribute('data-sabri-open-label') || (window.SabriShell && window.SabriShell.openLabel) || 'Open menu';
			var closeLabel = trigger.getAttribute('data-sabri-close-label') || (window.SabriShell && window.SabriShell.closeLabel) || 'Close menu';
			trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
			trigger.setAttribute('aria-label', expanded ? closeLabel : openLabel);
		});
	}

	function setBackgroundInert(drawer, inert) {
		if (!('inert' in HTMLElement.prototype)) {
			return;
		}
		if (!inert) {
			state.inerted.forEach(function (element) {
				element.inert = false;
			});
			state.inerted = [];
			return;
		}
		state.inerted = [];
		document.querySelectorAll('.sabri-shell-header, .sabri-shell-primary-nav, .sabri-shell-content-column, .sabri-shell-left-sidebar:not(.sabri-shell-left-sidebar-drawer), .sabri-shell-right-sidebar:not(.sabri-shell-right-sidebar-drawer), .sabri-shell-bottom-nav').forEach(function (element) {
			if (element !== drawer && !drawer.contains(element)) {
				element.inert = true;
				state.inerted.push(element);
			}
		});
	}

	function openDrawer(id, trigger) {
		var drawer = document.getElementById(id);
		var overlay = document.querySelector('[data-sabri-drawer-overlay]');
		if (!drawer) {
			return;
		}
		closeDrawer();
		state.openDrawer = drawer;
		state.lastFocus = trigger || document.activeElement;
		drawer.removeAttribute('hidden');
		drawer.removeAttribute('inert');
		drawer.setAttribute('aria-hidden', 'false');
		drawer.classList.add('is-open');
		if (overlay) {
			overlay.hidden = false;
		}
		document.body.classList.add('sabri-shell-drawer-open');
		setBackgroundInert(drawer, true);
		setTriggerState(id, true);
		var items = focusable(drawer);
		if (items.length) {
			items[0].focus();
		}
	}

	function closeDrawer() {
		if (!state.openDrawer) {
			return;
		}
		var drawer = state.openDrawer;
		var overlay = document.querySelector('[data-sabri-drawer-overlay]');
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		drawer.setAttribute('inert', '');
		if (overlay) {
			overlay.hidden = true;
		}
		document.body.classList.remove('sabri-shell-drawer-open');
		setBackgroundInert(drawer, false);
		setTriggerState(drawer.id, false);
		state.openDrawer = null;
		if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
			state.lastFocus.focus();
		}
	}

	function onKeydown(event) {
		if (event.key === 'Escape') {
			closeDrawer();
			return;
		}
		if (event.key !== 'Tab' || !state.openDrawer) {
			return;
		}
		var items = focusable(state.openDrawer);
		if (!items.length) {
			event.preventDefault();
			return;
		}
		var first = items[0];
		var last = items[items.length - 1];
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	function bindDrawers() {
		document.addEventListener('click', function (event) {
			var trigger = event.target.closest('[data-sabri-drawer-trigger]');
			if (trigger) {
				event.preventDefault();
				openDrawer(trigger.getAttribute('data-sabri-drawer-trigger'), trigger);
				return;
			}
			if (event.target.closest('[data-sabri-drawer-close]') || event.target.matches('[data-sabri-drawer-overlay]')) {
				event.preventDefault();
				closeDrawer();
				return;
			}
			if (state.openDrawer && event.target.closest('.sabri-shell-drawer a[href]')) {
				closeDrawer();
			}
		});
		document.addEventListener('keydown', onKeydown);

		var navMedia = window.matchMedia('(min-width: 1024px)');
		var contextMedia = window.matchMedia('(min-width: 1200px)');
		var onChange = function () {
			if (!state.openDrawer) {
				return;
			}
			if (state.openDrawer.id === 'sabri-shell-drawer-nav' && navMedia.matches) {
				closeDrawer();
			}
			if (state.openDrawer && state.openDrawer.id === 'sabri-shell-drawer-context' && contextMedia.matches) {
				closeDrawer();
			}
		};
		[navMedia, contextMedia].forEach(function (media) {
			if (typeof media.addEventListener === 'function') {
				media.addEventListener('change', onChange);
			} else if (typeof media.addListener === 'function') {
				media.addListener(onChange);
			}
		});
	}

	function configuredCandidates() {
		var candidates = [];
		if (window.SabriShell && window.SabriShell.contentSelector) {
			candidates.push(window.SabriShell.contentSelector);
		}
		if (window.SabriShell && Array.isArray(window.SabriShell.contentCandidates)) {
			window.SabriShell.contentCandidates.forEach(function (selector) {
				if (selector && candidates.indexOf(selector) === -1) {
					candidates.push(selector);
				}
			});
		}
		return candidates;
	}

	function validContentTarget(target) {
		if (!target || target === document.body || target === document.documentElement) {
			return false;
		}
		if (target.matches('.wp-site-blocks, #page, .site') || target.closest('[data-sabri-shell-component]') || target.matches('[data-sabri-shell-component]')) {
			return false;
		}
		return true;
	}

	function resolveContentTarget() {
		var candidates = configuredCandidates();
		for (var i = 0; i < candidates.length; i += 1) {
			try {
				var target = document.querySelector(candidates[i]);
				if (validContentTarget(target)) {
					document.body.setAttribute('data-sabri-shell-content-target', candidates[i]);
					return target;
				}
			} catch (error) {
				// Invalid selectors are rejected server-side; this is an additional runtime guard.
			}
		}
		return null;
	}

	function rememberSidebarScroll() {
		var sidebar = document.querySelector('.sabri-shell-left-sidebar:not(.sabri-shell-left-sidebar-drawer)');
		if (!sidebar || !window.sessionStorage) {
			return;
		}
		var key = 'sabriShellLeftScroll:' + window.location.pathname;
		var saved = parseInt(window.sessionStorage.getItem(key) || '0', 10);
		if (saved > 0) {
			sidebar.scrollTop = saved;
		}
		sidebar.addEventListener('scroll', function () {
			window.sessionStorage.setItem(key, String(sidebar.scrollTop));
		}, { passive: true });
	}

	function annotateStructuralLayout() {
		var left = document.querySelector('.sabri-shell-left-sidebar:not(.sabri-shell-left-sidebar-drawer)');
		var right = document.querySelector('.sabri-shell-right-sidebar:not(.sabri-shell-right-sidebar-drawer)');
		var target = resolveContentTarget();
		if (!target) {
			document.body.classList.add('sabri-shell-layout-unresolved');
			return;
		}
		target.classList.add('sabri-shell-content-column');
		target.setAttribute('data-sabri-shell-content-column', 'true');
		document.body.classList.toggle('sabri-shell-has-left-sidebar', Boolean(left));
		document.body.classList.toggle('sabri-shell-no-left-sidebar', !left);
		document.body.classList.toggle('sabri-shell-has-right-sidebar', Boolean(right));
		document.body.classList.toggle('sabri-shell-no-right-sidebar', !right);
		document.body.classList.add('sabri-shell-layout-ready');
	}

	ready(function () {
		measureChrome();
		annotateStructuralLayout();
		bindDrawers();
		rememberSidebarScroll();
		window.addEventListener('resize', measureChrome, { passive: true });
		window.addEventListener('load', measureChrome, { once: true });
		if (document.fonts && typeof document.fonts.ready.then === 'function') {
			document.fonts.ready.then(measureChrome);
		}
	});
}());
