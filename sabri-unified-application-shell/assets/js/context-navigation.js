(function () {
	'use strict';

	var CURRENT_KEY = 'sabriShellContextCurrentUrl';
	var PREVIOUS_KEY = 'sabriShellContextPreviousUrl';

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
			return url.href;
		} catch (error) {
			return null;
		}
	}

	function rememberCurrentPage() {
		try {
			var current = window.location.href;
			var storedCurrent = safeSameOriginUrl(window.sessionStorage.getItem(CURRENT_KEY));

			if (storedCurrent && storedCurrent !== current) {
				window.sessionStorage.setItem(PREVIOUS_KEY, storedCurrent);
			}
			window.sessionStorage.setItem(CURRENT_KEY, current);
		} catch (error) {
			// Storage can be unavailable; referrer and server fallback remain usable.
		}
	}

	function storedPreviousUrl() {
		try {
			return safeSameOriginUrl(window.sessionStorage.getItem(PREVIOUS_KEY));
		} catch (error) {
			return null;
		}
	}

	function navigateBack(button) {
		var current = window.location.href;
		var referrer = safeSameOriginUrl(document.referrer);

		if (referrer && referrer !== current && window.history.length > 1) {
			window.history.back();
			return;
		}

		var previous = storedPreviousUrl();
		if (previous && previous !== current) {
			window.location.assign(previous);
			return;
		}

		var fallback = safeSameOriginUrl(button.getAttribute('data-fallback-url'));
		window.location.assign(fallback || window.location.origin + '/');
	}

	function initialize() {
		rememberCurrentPage();

		var buttons = document.querySelectorAll('[data-sabri-context-back]');
		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				navigateBack(button);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize, { once: true });
	} else {
		initialize();
	}
})();
