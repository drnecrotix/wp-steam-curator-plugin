(function () {
	'use strict';

	var closeKey = 'bggSteamCuratorClosedUntil';
	var sessionKey = 'bggSteamCuratorShownSession';
	var sevenDays = 7 * 24 * 60 * 60 * 1000;

	function now() {
		return Date.now ? Date.now() : new Date().getTime();
	}

	function storageGet(storage, key) {
		try {
			return storage.getItem(key);
		} catch (error) {
			return null;
		}
	}

	function storageSet(storage, key, value) {
		try {
			storage.setItem(key, value);
		} catch (error) {
			// Storage can be disabled; the widget should still work.
		}
	}

	function trackClick(link) {
		var widget = link.closest('.bgg-steam-curator');
		var placement = link.getAttribute('data-placement') || (widget ? widget.getAttribute('data-placement') : 'shortcode');
		var url = widget ? widget.getAttribute('data-curator-url') : link.href;

		window.dispatchEvent(new CustomEvent('bgGamerSteamCuratorClick', {
			detail: {
				placement: placement,
				url: url
			}
		}));

		if (typeof window.gtag === 'function') {
			window.gtag('event', 'steam_curator_click', {
				placement: placement
			});
		}
	}

	function bindTracking() {
		document.addEventListener('click', function (event) {
			var link = event.target.closest('[data-event="steam_curator_click"]');

			if (!link) {
				return;
			}

			trackClick(link);
		});
	}

	function shouldShowFloating() {
		if (window.matchMedia && window.matchMedia('(max-width: 600px)').matches) {
			return false;
		}

		var closedUntil = parseInt(storageGet(window.localStorage, closeKey), 10);

		if (closedUntil && closedUntil > now()) {
			return false;
		}

		return storageGet(window.sessionStorage, sessionKey) !== '1';
	}

	function scrollProgress() {
		var doc = document.documentElement;
		var max = Math.max(1, doc.scrollHeight - window.innerHeight);

		return window.scrollY / max;
	}

	function bindFloating() {
		var widgets = Array.prototype.slice.call(document.querySelectorAll('.bgg-steam-curator--floating'));

		if (!widgets.length || !shouldShowFloating()) {
			return;
		}

		function showEligibleWidgets() {
			if (scrollProgress() < 0.35) {
				return;
			}

			widgets.forEach(function (widget) {
				widget.classList.add('is-visible');
			});

			storageSet(window.sessionStorage, sessionKey, '1');
			window.removeEventListener('scroll', showEligibleWidgets);
		}

		widgets.forEach(function (widget) {
			var close = widget.querySelector('.bgg-steam-curator__close');

			if (!close) {
				return;
			}

			close.addEventListener('click', function () {
				widget.classList.remove('is-visible');
				storageSet(window.localStorage, closeKey, String(now() + sevenDays));
			});
		});

		window.addEventListener('scroll', showEligibleWidgets, { passive: true });
		showEligibleWidgets();
	}

	function init() {
		bindTracking();
		bindFloating();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
