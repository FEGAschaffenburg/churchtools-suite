/**
 * Sticky Header Shrink on Scroll
 *
 * Feedback-Loop-Prevention:
 * Wenn der Header ein-/ausklappt, ändert sich seine Höhe im DOM-Flow.
 * Dadurch springt scrollY, was ohne Lock sofort einen weiteren Toggle auslöst.
 * Lösung: nach jedem Toggle für die Dauer der CSS-Transition (350ms) keine
 * weiteren Scroll-Events verarbeiten.
 */
(function () {
	var wrap = document.querySelector('.sticky-header-wrap');
	if (!wrap) return;

	var shrinkAt    = 80;   // px – einklappen beim Runterscrollen
	var expandAtTop = 2;    // px – ausklappen nur ganz oben
	var lockMs      = 350;  // ms – muss >= CSS transition-Dauer sein

	var locked  = false;
	var ticking = false;
	var lockTimer = null;

	function lock() {
		locked = true;
		clearTimeout(lockTimer);
		lockTimer = setTimeout(function () { locked = false; }, lockMs);
	}

	function updateState() {
		ticking = false;
		if (locked) return;

		var y         = window.scrollY || window.pageYOffset || 0;
		var isScrolled = wrap.classList.contains('scrolled');

		if (!isScrolled && y > shrinkAt) {
			wrap.classList.add('scrolled');
			lock();
		} else if (isScrolled && y <= expandAtTop) {
			wrap.classList.remove('scrolled');
			lock();
		}
	}

	function onScroll() {
		if (locked || ticking) return;
		ticking = true;
		window.requestAnimationFrame(updateState);
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	updateState(); // initial state
})();
