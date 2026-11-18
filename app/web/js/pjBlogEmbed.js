/*
 * PHP Blog Script - embed runtime (install code = <div> + <script>, works in
 * any CMS page editor, page builder or plain HTML page; no PHP, no .htaccess).
 *
 * Streamed by the two embed endpoints, which call:
 *   PJBLOG.boot(CFG)    install-code script. Reads THIS page's URL and loads the
 *                       matching content script. A single-post install code
 *                       (&post=<slug> on the script URL) shows that post when
 *                       the page URL has none of the parameters below, with
 *                       this page itself as the post's canonical URL.
 *                       Page URL parameters:
 *                         ?post=<slug>             single post (detail)
 *                         ?blog_category=<slug>    category listing (an id works too)
 *                         ?blog_page=<n>           listing page n
 *                         ?blog_search=<text>      listing search
 *                         ?blog_archive=<Y[-m]>    listing archive
 *                       Other parameters of the page (e.g. ?page_id=12) are kept.
 *   PJBLOG.render(CFG)  content script. Puts the blog into the host <div>,
 *                       re-runs its scripts, points every blog link at the
 *                       Blog page URL (Admin > Install) or, when that is not
 *                       set, at THIS page, and - on a single post or a category listing -
 *                       writes the SEO tags into the page <head> in FORCE mode
 *                       (replace the page's tag if present, else add). Post:
 *                       post SEO, else global SEO, else the page's own tag.
 *                       Category: category SEO, else the page's own tag.
 *                       The plain list and search results write no tags.
 *
 * NOTE on SEO: tags written by JavaScript are read by Google, but are not
 * guaranteed to be indexed as reliably as tags already in the page HTML.
 */
(function () {
	if (window.PJBLOG && window.PJBLOG.__v) { return; }
	var PJBLOG = (window.PJBLOG = window.PJBLOG || {});
	PJBLOG.__v = 1;

	var head = document.head || document.getElementsByTagName('head')[0];
	var KEYS = ['post', 'blog_post_id', 'blog_category', 'blog_page', 'blog_search', 'blog_archive'];
	var state = { boot: null, host: null, params: {}, render: null };

	/* ---- helpers ------------------------------------------------------- */
	function ready(fn) {
		if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', fn, false); }
		else { fn(); }
	}

	// This page without the blog's own parameters and hash (its other query
	// parameters, e.g. ?page_id=12, are kept).
	function pageBase() {
		var keep = [], parts = (location.search || '').replace(/^\?/, '').split('&');
		for (var i = 0; i < parts.length; i++) {
			if (!parts[i]) { continue; }
			var k = parts[i].split('=')[0];
			try { k = decodeURIComponent(k); } catch (e) {}
			if (KEYS.indexOf(k) === -1 && k !== 'err') { keep.push(parts[i]); }
		}
		return location.protocol + '//' + location.host + location.pathname + (keep.length ? '?' + keep.join('&') : '');
	}

	// Where blog links go: the Blog page URL when set (sent by the server),
	// else this page.
	function linkBase() {
		return (state.render && state.render.base) ? state.render.base : pageBase();
	}

	function parseQuery(search) {
		var out = {}, s = (search || '').replace(/^\?/, '');
		if (!s) { return out; }
		var parts = s.split('&');
		for (var i = 0; i < parts.length; i++) {
			if (!parts[i]) { continue; }
			var kv = parts[i].split('='), k, v;
			try {
				k = decodeURIComponent(kv[0].replace(/\+/g, ' '));
				v = kv.length > 1 ? decodeURIComponent(kv.slice(1).join('=').replace(/\+/g, ' ')) : '';
			} catch (e) { continue; }
			out[k] = v;
		}
		return out;
	}

	function buildQuery(obj) {
		var a = [];
		for (var i = 0; i < KEYS.length; i++) {
			var k = KEYS[i], v = obj[k];
			if (v === undefined || v === null || String(v) === '') { continue; }
			if (k === 'blog_page' && parseInt(v, 10) <= 1) { continue; }
			a.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
		}
		return a.join('&');
	}

	function hostUrl(params) {
		var q = buildQuery(params || {}), base = linkBase();
		return base + (q ? (base.indexOf('?') === -1 ? '?' : '&') + q : '');
	}

	function pageParams() {
		var all = parseQuery(location.search), out = {};
		for (var i = 0; i < KEYS.length; i++) { if (all[KEYS[i]] !== undefined) { out[KEYS[i]] = all[KEYS[i]]; } }
		return out;
	}

	function addCss(href) {
		if (!href) { return; }
		// Same file already on the page (the ?v= cache-bust stamp may differ).
		var bare = function (u) { return String(u || '').replace(/\?v=[^&]*$/, ''); };
		var links = document.getElementsByTagName('link');
		for (var i = 0; i < links.length; i++) { if (bare(links[i].getAttribute('href')) === bare(href)) { return; } }
		var l = document.createElement('link');
		l.rel = 'stylesheet'; l.type = 'text/css'; l.href = href;
		head.appendChild(l);
	}

	// The blog's CSS sent as text with the HTML: one <style> per stylesheet,
	// added once (marked with data-pjbl-css).
	function addStyles(list) {
		for (var i = 0; list && i < list.length; i++) {
			if (!list[i].css || document.querySelector('style[data-pjbl-css="' + list[i].id + '"]')) { continue; }
			var st = document.createElement('style');
			st.setAttribute('data-pjbl-css', list[i].id);
			st.appendChild(document.createTextNode(list[i].css));
			head.appendChild(st);
		}
		if (list && list.length) { state.cssDone = true; }
	}

	// The host element by id, else a div dropped in where the install <script> sits.
	function resolveHost(id, cs) {
		var el = document.getElementById(id);
		if (el) { return el; }
		if (!cs) { var ss = document.getElementsByTagName('script'); cs = ss[ss.length - 1]; }
		el = document.createElement('div');
		el.id = id;
		if (cs && cs.parentNode) { cs.parentNode.insertBefore(el, cs.nextSibling); }
		else { (document.body || document.documentElement).appendChild(el); }
		return el;
	}

	function closest(el, sel) {
		while (el && el.nodeType === 1) {
			if (el.matches ? el.matches(sel) : (el.msMatchesSelector && el.msMatchesSelector(sel))) { return el; }
			el = el.parentNode;
		}
		return null;
	}

	// Scripts added through innerHTML never run: re-create them, in order.
	function runScripts(scope) {
		var list = scope.getElementsByTagName('script'), arr = [], i, j;
		for (i = 0; i < list.length; i++) { arr.push(list[i]); }
		for (i = 0; i < arr.length; i++) {
			var old = arr[i], s = document.createElement('script');
			for (j = 0; j < old.attributes.length; j++) { s.setAttribute(old.attributes[j].name, old.attributes[j].value); }
			if (!old.src) { s.text = old.text || old.textContent || ''; }
			old.parentNode.replaceChild(s, old);
		}
	}

	// reCAPTCHA renders itself when api.js first loads; on a re-render it is
	// already loaded, so render the new (empty) widgets explicitly.
	function renderCaptchas(scope) {
		if (!window.grecaptcha || !window.grecaptcha.render) { return; }
		var w = scope.querySelectorAll('.g-recaptcha');
		for (var i = 0; i < w.length; i++) {
			if (w[i].children.length) { continue; }
			try { window.grecaptcha.render(w[i]); } catch (e) {}
		}
	}

	/* ---- SEO meta (FORCE: replace the page's tag if present, else add) ---
	 * Matching scans the WHOLE document: the first match is updated and moved
	 * into <head>, every other copy is removed - the page always ends with
	 * exactly one of each tag.
	 */
	function collect(tag, attr, val) {
		var out = [], list = document.getElementsByTagName(tag);
		for (var i = 0; i < list.length; i++) {
			var a = list[i].getAttribute(attr);
			if (attr === 'rel') { a = (a || '').toLowerCase(); }
			if (a === val) { out.push(list[i]); }
		}
		return out;
	}
	function placeOne(found, apply, create) {
		if (found.length) {
			apply(found[0]);
			if (found[0].parentNode !== head) { head.appendChild(found[0]); }
			for (var j = 1; j < found.length; j++) { if (found[j].parentNode) { found[j].parentNode.removeChild(found[j]); } }
			return;
		}
		head.appendChild(create());
	}
	function setTitle(title) {
		if (!title) { return; }
		document.title = title;
		var ts = document.getElementsByTagName('title');
		for (var i = ts.length - 1; i >= 1; i--) { if (ts[i].parentNode) { ts[i].parentNode.removeChild(ts[i]); } }
	}
	function setMeta(attr, key, content) {
		if (content === undefined || content === null || content === '') { return; }
		placeOne(collect('meta', attr, key),
			function (el) { el.setAttribute('content', content); },
			function () { var m = document.createElement('meta'); m.setAttribute(attr, key); m.setAttribute('content', content); return m; });
	}
	function setCanonical(href) {
		if (!href) { return; }
		placeOne(collect('link', 'rel', 'canonical'),
			function (el) { el.setAttribute('href', href); },
			function () { var l = document.createElement('link'); l.setAttribute('rel', 'canonical'); l.setAttribute('href', href); return l; });
	}
	function setSchema(m) {
		var old = document.querySelectorAll('script[data-pjbl-schema]');
		for (var i = 0; i < old.length; i++) { if (old[i].parentNode) { old[i].parentNode.removeChild(old[i]); } }
		var schema = { '@context': 'https://schema.org', '@type': 'Article', headline: m.title };
		if (m.description) { schema.description = m.description; }
		if (m.ogImage) { schema.image = [m.ogImage]; }
		if (m.publishedTime) { schema.datePublished = m.publishedTime; }
		if (m.modifiedTime) { schema.dateModified = m.modifiedTime; }
		if (m.author) { schema.author = { '@type': 'Person', name: m.author }; }
		if (m.siteName) { schema.publisher = { '@type': 'Organization', name: m.siteName }; }
		if (m.section) { schema.articleSection = m.section; }
		if (m.canonical) { schema.mainEntityOfPage = { '@type': 'WebPage', '@id': m.canonical }; }
		var s = document.createElement('script');
		s.type = 'application/ld+json';
		s.setAttribute('data-pjbl-schema', '1');
		s.text = JSON.stringify(schema);
		head.appendChild(s);
	}
	function applyMeta(m) {
		if (!m) { return; }
		// title / description / keywords: post SEO, else global SEO (decided on
		// the server); when both are empty the value is '' and the website
		// page's own tag stays.
		setTitle(m.title);
		setMeta('name', 'description', m.description);
		setMeta('name', 'keywords', m.keywords);
		setCanonical(m.canonical);
		// Open Graph / Twitter / schema mirror the title & description the page
		// actually ends up with, so og:title always matches <title>.
		var dm = document.querySelector('meta[name="description"]');
		m.title = (document.title || '').trim() || m.title;
		m.description = (dm && dm.getAttribute('content')) ? dm.getAttribute('content') : m.description;
		setMeta('property', 'og:type', m.ogType || 'article');
		setMeta('property', 'og:title', m.title);
		setMeta('property', 'og:description', m.description);
		setMeta('property', 'og:url', m.canonical);
		setMeta('property', 'og:image', m.ogImage);
		setMeta('property', 'og:site_name', m.siteName);
		setMeta('property', 'article:published_time', m.publishedTime);
		setMeta('property', 'article:modified_time', m.modifiedTime);
		setMeta('property', 'article:author', m.author);
		setMeta('property', 'article:section', m.section);
		setMeta('name', 'twitter:card', m.ogImage ? 'summary_large_image' : 'summary');
		setMeta('name', 'twitter:title', m.title);
		setMeta('name', 'twitter:description', m.description);
		setMeta('name', 'twitter:image', m.ogImage);
		// Article schema: single post only (not a category listing).
		if ((m.ogType || 'article') === 'article') { setSchema(m); }
	}

	/* ---- links: blog script URLs -> this page --------------------------- */
	function mapHref(href) {
		if (!href || href.indexOf('controller=pjLoad') === -1) { return null; }
		var i = href.indexOf('?');
		var q = parseQuery(i === -1 ? '' : href.substring(i + 1).replace(/#.*$/, '').replace(/&amp;/g, '&'));
		if (q.action === 'pjActionView' && q.id) { return hostUrl({ blog_post_id: q.id }); }
		return hostUrl({
			blog_category: q.blog_category || q.category_id,
			blog_page: q.pjPage,
			blog_search: q.keyword,
			blog_archive: q.archive
		});
	}

	function wire(host) {
		var a = host.getElementsByTagName('a');
		for (var i = 0; i < a.length; i++) {
			var to = mapHref(a[i].getAttribute('href'));
			if (to) { a[i].setAttribute('href', to); }
		}
		var back = host.querySelectorAll('.pjBlBackLink');
		for (var j = 0; j < back.length; j++) {
			// Single-post page: there is no list on this page to go back to.
			if (state.single) { if (back[j].parentNode) { back[j].parentNode.removeChild(back[j]); } continue; }
			back[j].setAttribute('href', linkBase());
		}
	}

	function searchParams(form, archive) {
		var f = form.elements;
		return {
			blog_search: f.keyword ? f.keyword.value : '',
			blog_category: f.category_id ? f.category_id.value : '',
			blog_archive: archive !== undefined ? archive : (f.archive ? f.archive.value : '')
		};
	}

	function loadContent(params, extra) {
		var url = state.boot.content + (state.boot.content.indexOf('?') === -1 ? '?' : '&');
		var q = buildQuery(params);
		url += (q ? q + '&' : '') + 'host=' + encodeURIComponent(pageBase()) + (state.single ? '&single=1' : '') + (extra || '');
		// Admin > Preview (&theme=themeN / &local=1 on the install script URL).
		if (state.boot.theme) { url += '&theme=' + encodeURIComponent(state.boot.theme); }
		if (state.boot.local) { url += '&local=1'; }
		// The blog's CSS is on the page already: do not send it again.
		if (state.cssDone) { url += '&nocss=1'; }
		var s = document.createElement('script');
		s.src = url;
		head.appendChild(s);
	}

	/* ---- public API ---------------------------------------------------- */
	// Content sent with the install-code script (page on the same website):
	// kept until boot() checks it was made for this page's URL.
	PJBLOG.prefetch = function (P) {
		state.prefetch = P;
	};

	PJBLOG.boot = function (CFG) {
		var selfScript = document.currentScript;
		state.boot = CFG;
		// The host <div> normally comes before the install <script>: start now
		// instead of waiting for the rest of the page to load.
		var start = document.getElementById(CFG.host || 'pjBlogEmbed') ? function (fn) { fn(); } : ready;
		start(function () {
			state.host = resolveHost(CFG.host || 'pjBlogEmbed', selfScript);
			state.params = pageParams();
			// Single-post install code (&post=<slug> on the script URL): show that
			// post unless this page URL asks for something else.
			state.single = false;
			if (CFG.post && buildQuery(state.params) === '') {
				state.params = { post: CFG.post };
				state.single = true;
			}
			// Search box (Enter): open the results on this page.
			state.host.addEventListener('submit', function (e) {
				var form = e.target;
				if (form && form.id && form.id.indexOf('pjSbSearchForm_') === 0) {
					e.preventDefault();
					location.href = hostUrl(searchParams(form));
				}
			}, false);
			var P = state.prefetch;
			state.prefetch = null;
			if (P && P.cfg && P.host === pageBase() && !!P.single === state.single && buildQuery(P.params || {}) === buildQuery(state.params)) {
				PJBLOG.render(P.cfg);
			} else {
				loadContent(state.params);
			}
		});
	};

	PJBLOG.render = function (CFG) {
		if (!state.host) { return; }
		state.render = CFG;
		// CSS first (same response as the HTML), then the blog.
		addStyles(CFG.styles);
		for (var i = 0; i < CFG.sheets.length; i++) { addCss(CFG.sheets[i]); }
		state.host.innerHTML = CFG.html;
		wire(state.host);
		runScripts(state.host);
		renderCaptchas(state.host);
		// Post detail or category listing only (the server sends no meta for
		// the plain list or search): these tags win over the page's own.
		if (CFG.found && CFG.meta) { applyMeta(CFG.meta); }
	};

	// Archive filter (called by pjLoad.js in embed mode).
	PJBLOG.archive = function (form, archive) {
		location.href = hostUrl(searchParams(form, archive));
	};

	// Comment form (called by pjLoad.js after validation, in embed mode):
	// post it to the script, then re-render the post with the result message.
	PJBLOG.comment = function (form) {
		if (!state.render || !window.FormData) { return; }
		var btns = form.querySelectorAll('button, input[type="submit"]'), k;
		for (k = 0; k < btns.length; k++) { btns[k].disabled = true; }
		var xhr = new XMLHttpRequest();
		xhr.open('POST', state.render.commentUrl, true);
		xhr.withCredentials = true;
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.onreadystatechange = function () {
			if (xhr.readyState !== 4) { return; }
			var code = 0;
			try { code = parseInt(JSON.parse(xhr.responseText).code, 10) || 0; } catch (e) {}
			if (!code) {
				for (k = 0; k < btns.length; k++) { btns[k].disabled = false; }
				return;
			}
			loadContent(state.params, '&err=' + code);
		};
		xhr.send(new FormData(form));
	};
})();
