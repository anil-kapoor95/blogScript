/*
 * Admin > Install: "Copy" button for the embed install code.
 * Button markup: .pj-embed-copy[data-target=<textarea id>][data-done=<label>]
 */
(function () {
	function flash(btn) {
		var orig = btn.textContent, done = btn.getAttribute('data-done') || orig;
		btn.textContent = done;
		setTimeout(function () { btn.textContent = orig; }, 1500);
	}

	function copy(btn) {
		var el = document.getElementById(btn.getAttribute('data-target'));
		if (!el) { return; }
		var text = el.value !== undefined ? el.value : (el.textContent || '');
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(function () { flash(btn); }, function () { legacy(el, btn); });
		} else {
			legacy(el, btn);
		}
	}

	function legacy(el, btn) {
		try {
			el.focus();
			el.select();
			if (document.execCommand('copy')) { flash(btn); }
		} catch (e) {}
	}

	function init() {
		var b = document.querySelectorAll('.pj-embed-copy');
		for (var i = 0; i < b.length; i++) {
			b[i].addEventListener('click', function () { copy(this); }, false);
		}
	}

	if (document.readyState !== 'loading') { init(); }
	else { document.addEventListener('DOMContentLoaded', init, false); }
})();
