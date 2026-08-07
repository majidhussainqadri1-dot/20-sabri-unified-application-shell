(function () {
	'use strict';

	var config = window.SabriShellFourPlan || {};
	var welcome = config.welcome || {};
	var storageKey = typeof welcome.storageKey === 'string' ? welcome.storageKey : 'sabriShellWelcomeDismissedAt';
	var interval = Number(welcome.intervalSeconds || (30 * 24 * 60 * 60));

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

	function rememberLocally() {
		try {
			window.localStorage.setItem(storageKey, String(nowSeconds()));
		} catch (error) {
			/* Storage failure must never block the site. */
		}
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

	function suppressDuplicateIntro() {
		if (!storageRecentlyDismissed()) {
			return;
		}
		document.querySelectorAll('[data-sabri-welcome-intro]').forEach(function (node) {
			node.hidden = true;
			node.setAttribute('aria-hidden', 'true');
		});
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

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', suppressDuplicateIntro, { once: true });
	} else {
		suppressDuplicateIntro();
	}
}());
