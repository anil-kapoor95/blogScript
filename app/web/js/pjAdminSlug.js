/*
 * Admin "URL Slug" field for posts and categories. The slug is filled in
 * from the title / name once - while the record has no saved slug and the
 * user has not typed in the slug field - and the user can change it at any
 * time. The value is shown the way the server saves it (same rules as
 * pjAppController::friendlyURL). A slug already used by another post /
 * category is reported by the form's jQuery validate "remote" rule
 * (pjActionCheckSlug) before the form is sent, so nothing typed is lost.
 *
 * Markup contract (data-* on the slug <input>):
 *   data-slug-source    selector of the title / name field (e.g. #title)
 *   data-slug-autofill  "1" = fill in from the title / name (no saved slug yet)
 */
(function () {
	function toSlug(str) {
		return String(str).toLowerCase().replace(/^\s+|\s+$/g, '')
			.replace(/[_|\s]+/g, '-')
			.replace(/[^a-zЀ-ӿ0-9-]+/g, '')
			.replace(/-+/g, '-')
			.replace(/^-+|-+$/g, '');
	}

	function init(input) {
		var source = document.querySelector(input.getAttribute('data-slug-source'));
		if (!source) { return; }

		var auto = input.getAttribute('data-slug-autofill') === '1';
		var lastSource = null, timer = null;

		// Run the form's validation of the slug shortly after it changes, so
		// a "slug already used" message shows (and clears) while typing -
		// not only on Save.
		function validateSoon() {
			var jq = window.jQuery_1_8_2;
			if (!jq || !jq.fn.valid || !input.form || !jq.data(input.form, 'validator')) { return; }
			clearTimeout(timer);
			timer = setTimeout(function () { jq(input).valid(); }, 500);
		}

		function fromSource() {
			if (!auto || source.value === lastSource) { return; }
			lastSource = source.value;
			input.value = toSlug(source.value);
			validateSoon();
		}

		source.addEventListener('input', fromSource, false);
		source.addEventListener('change', fromSource, false);
		// Titles filled by script (e.g. the AI writer) fire no input event.
		setInterval(fromSource, 700);

		// Typed by hand: stop filling it in; show it the way it will be saved.
		input.addEventListener('input', function () { auto = false; validateSoon(); }, false);
		input.addEventListener('change', function () { input.value = toSlug(input.value); }, false);

		if (auto && source.value !== '' && input.value === '') { fromSource(); }
	}

	function start() {
		var list = document.querySelectorAll('input[data-slug-source]');
		for (var i = 0; i < list.length; i++) { init(list[i]); }
	}

	if (document.readyState !== 'loading') { start(); }
	else { document.addEventListener('DOMContentLoaded', start, false); }
})();
