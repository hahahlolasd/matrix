;(function (global, $) {
	'use strict';
	
	const DEFAULT_ID = 'loadingLine';
	let $container = null, $bar = null, disabledEls = [];
	
	function ensureDom(selector) {
		$container = $(selector);
		if (!$container.length) {
			// auto-create if missing
			$container = $(
				`<div id="${DEFAULT_ID}" class="progress position-fixed top-0 start-0 w-100 d-none" style="height:4px; z-index:1050">
				<div class="progress-bar" role="progressbar" style="width:0%"></div>
				</div>`
			).appendTo(document.body);
		}
		$bar = $container.find('.progress-bar');
		if (!$bar.length) $bar = $('<div class="progress-bar" role="progressbar" style="width:0%"></div>').appendTo($container);
	}
	
	function clamp(n, min, max){ return Math.max(min, Math.min(max, n)); }
	
	function setWidth(pct, duration) {
		pct = clamp(pct, 0, 100);
		if (duration && $.fn && $.fn.animate) {
			$bar.stop(true).animate({ width: pct + '%' }, duration);
			} else {
			$bar.stop(true);
			$bar.css('width', pct + '%');
		}
	}
	
	function setDisabled(state) {
		disabledEls.forEach($el => $el && $el.prop && $el.prop('disabled', state));
	}
	
	const api = {
		/**
			* Initialize (optional). Auto-runs on DOM ready with default selector.
			* @param {string} selector  CSS selector or element id (default '#loadingLine')
		*/
		init(selector = '#'+DEFAULT_ID) {
			ensureDom(selector);
			return api;
		},
		
		/**
			* Start the loading line.
			* @param {Object} o
			*  - to: target percent (number) or [min,max] range (array). Default random 40–70.
			*  - duration: ms to animate to target (default 400)
			*  - disable: jQuery collection or DOM element(s) to disable during load (optional)
		*/
		start(o = {}) {
			if (!$container) api.init();
			const hasRange = Array.isArray(o.to) && o.to.length === 2;
			const target = hasRange
			? clamp(Math.floor(Math.random()*(o.to[1]-o.to[0]+1))+o.to[0], 0, 100)
			: (typeof o.to === 'number' ? clamp(o.to, 0, 100) : Math.floor(Math.random()*31)+40); // 40–70
			const duration = Number.isFinite(o.duration) ? o.duration : 400;
			
			// collect disable targets
			disabledEls = [];
			if (o.disable) {
				const $d = $(o.disable);
				if ($d && $d.length) disabledEls = [$d];
			}
			setDisabled(true);
			
			$container.removeClass('d-none');
			setWidth(0, 0);
			setWidth(target, duration);
			return api;
		},
		
		/**
			* Manually set progress (0–100).
			* @param {number} pct
			* @param {number} duration ms
		*/
		set(pct, duration = 150) {
			if (!$container) api.init();
			$container.removeClass('d-none');
			setWidth(pct, duration);
			return api;
		},
		
		/**
			* Finish: fill to 100%, hold, hide, reset.
			* @param {Object} o
			*  - duration: ms to animate to 100 (default 150)
			*  - hold: ms to hold at 100 before hiding (default 800)
		*/
		finish(o = {}) {
			if (!$container) api.init();
			const duration = Number.isFinite(o.duration) ? o.duration : 150;
			const hold = Number.isFinite(o.hold) ? o.hold : 800;
			
			setWidth(100, duration);
			setTimeout(() => {
				$container.addClass('d-none');
				setWidth(0, 0);
				setDisabled(false);
				disabledEls = [];
			}, hold);
			return api;
		},
		
		/**
			* Fail: flash red, then hide.
			* @param {Object} o
			*  - hold: ms visible after 100% (default 1200)
		*/
		fail(o = {}) {
			if (!$container) api.init();
			const hold = Number.isFinite(o.hold) ? o.hold : 1200;
			
			$bar.addClass('bg-danger');
			setWidth(100, 200);
			setTimeout(() => {
				$bar.removeClass('bg-danger');
				$container.addClass('d-none');
				setWidth(0, 0);
				setDisabled(false);
				disabledEls = [];
			}, hold);
			return api;
		},
		
		/** Is the line visible? */
		isVisible() {
			if (!$container) return false;
			return !$container.hasClass('d-none');
		}
	};
	
	// auto-init on DOM ready with the default selector
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => api.init());
		} else {
		api.init();
	}
	
	global.LoadingLine = api;
})(window, window.jQuery);
