(function () {
	'use strict';

	var STACK_KEY = 'sabriShellContextNavigationStack';
	var ARRIVAL_KEY = 'sabriShellContextManagedBackArrival';
	var MAX_STACK_SIZE = 20;
	var arrivedByManagedBack = false;

	function safeSameOriginUrl(value) {
		if (!value) {
			return null;
		}

		try {
			var url = new URL(value, window.location.href);
			if (
				(url.protocol !== 'http:' && url.protocol !== 'https:') ||
				url.origin !== window.location.origin
			) {
				return null;
			}
			return url.origin + url.pathname;
		} catch (error) {
			return null;
		}
	}

	function readStack() {
		try {
			var parsed = JSON.parse(window.sessionStorage.getItem(STACK_KEY) || '[]');
			if (!Array.isArray(parsed)) {
				return [];
			}
			return parsed.map(safeSameOriginUrl).filter(Boolean).slice(-MAX_STACK_SIZE);
		} catch (error) {
			return [];
		}
	}

	function writeStack(stack) {
		try {
			window.sessionStorage.setItem(STACK_KEY, JSON.stringify(stack.slice(-MAX_STACK_SIZE)));
		} catch (error) {
			// Storage can be unavailable; referrer and server fallback remain usable.
		}
	}

	function rememberCurrentPage() {
		var current = safeSameOriginUrl(window.location.href) || (window.location.origin + window.location.pathname);
		var stack = readStack();

		try {
			var arrival = safeSameOriginUrl(window.sessionStorage.getItem(ARRIVAL_KEY));
			arrivedByManagedBack = arrival === current;
			window.sessionStorage.removeItem(ARRIVAL_KEY);
		} catch (error) {
			arrivedByManagedBack = false;
		}

		if (stack.length && stack[stack.length - 1] === current) {
			return;
		}

		if (stack.length > 1 && stack[stack.length - 2] === current) {
			stack.pop();
		} else {
			stack.push(current);
		}
		writeStack(stack);
	}

	function previousStackUrl() {
		var current = safeSameOriginUrl(window.location.href) || (window.location.origin + window.location.pathname);
		var stack = readStack();

		while (stack.length && stack[stack.length - 1] === current) {
			stack.pop();
		}

		var previous = stack.length ? safeSameOriginUrl(stack[stack.length - 1]) : null;
		writeStack(stack);
		return previous;
	}

	function managedNavigate(target) {
		try {
			window.sessionStorage.setItem(ARRIVAL_KEY, target);
		} catch (error) {
			// Navigation remains safe without storage.
		}
		window.location.assign(target);
	}

	function navigateBack(link) {
		var current = safeSameOriginUrl(window.location.href) || (window.location.origin + window.location.pathname);
		var previous = previousStackUrl();
		if (previous && previous !== current) {
			managedNavigate(previous);
			return;
		}

		var referrer = arrivedByManagedBack ? null : safeSameOriginUrl(document.referrer);
		if (referrer && referrer !== current) {
			managedNavigate(referrer);
			return;
		}

		var fallback = safeSameOriginUrl(link.getAttribute('data-fallback-url'));
		var home = safeSameOriginUrl(link.getAttribute('data-home-url'));
		managedNavigate(fallback || home || window.location.origin + '/');
	}

	// Real same-origin browser history only. Never infer Forward from history.length,
	// referrer or the synthetic, same-origin Back convenience stack.
	function trustedForwardController() {
		var nav = window.navigation;
		if (!nav || nav.canGoForward !== true || typeof nav.forward !== 'function' ||
			typeof nav.entries !== 'function' || !nav.currentEntry) {
			return null;
		}
		try {
			var entries = nav.entries();
			if (!Array.isArray(entries)) {
				return null;
			}
			for (var i = 0; i < entries.length - 1; i++) {
				if (entries[i] && entries[i].key === nav.currentEntry.key) {
					var next = entries[i + 1];
					return next && safeSameOriginUrl(next.url) ? nav : null;
				}
			}
		} catch (error) {
			return null;
		}
		return null;
	}

	function updateForwardControl() {
		var button = document.querySelector('[data-sabri-context-forward]');
		if (!button) {
			return;
		}
		var available = !!trustedForwardController();
		button.hidden = !available;
		button.disabled = !available;
	}

	function registerForwardControl() {
		var button = document.querySelector('[data-sabri-context-forward]');
		if (!button) {
			return;
		}
		button.addEventListener('click', function () {
			var nav = trustedForwardController();
			if (!nav) {
				updateForwardControl();
				return;
			}
			button.disabled = true;
			try {
				var transition = nav.forward();
				if (transition && transition.finished && typeof transition.finished.catch === 'function') {
					transition.finished.catch(updateForwardControl);
				}
			} catch (error) {
				updateForwardControl();
			}
		});
		if (window.navigation && typeof window.navigation.addEventListener === 'function') {
			window.navigation.addEventListener('currententrychange', updateForwardControl);
		}
		updateForwardControl();
	}

	function initialize() {
		rememberCurrentPage();
		registerForwardControl();

		var links = document.querySelectorAll('[data-sabri-context-back]');
		links.forEach(function (link) {
			link.addEventListener('click', function (event) {
				event.preventDefault();
				navigateBack(link);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize, { once: true });
	} else {
		initialize();
	}

	window.addEventListener('pageshow', function () {
		rememberCurrentPage();
		updateForwardControl();
	});
})();
