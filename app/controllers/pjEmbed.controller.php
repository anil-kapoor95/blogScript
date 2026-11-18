<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
/**
 * JS embed for the blog (install code = <div id="pjBlogEmbed"></div> + <script>).
 * This is the only front-end install method of the script.
 *
 *   index.php?controller=pjEmbed&action=pjActionLoadJs
 *       Install-code script. Streams the runtime (app/web/js/pjBlogEmbed.js)
 *       and calls PJBLOG.boot(), which reads the HOST page URL and loads the
 *       content script below with the matching parameters.
 *         &post=<url-slug>   single-post install code (that post by default)
 *         &theme=themeN      Admin > Preview colour theme
 *         &local=1           Admin > Preview: keep links on the preview page
 *
 *   index.php?controller=pjEmbed&action=pjActionContentJs
 *       Renders the normal front views (pjLoad/pjActionIndex.php or
 *       pjLoad/pjActionView.php) and streams them as PJBLOG.render(CFG).
 *       Host-page parameters (no .htaccess needed):
 *         post=<url-slug>                  single post (detail)
 *         blog_post_id=<id>                single post by id (fallback)
 *         blog_category=<url-slug|id>      category listing
 *         blog_page, blog_search,
 *         blog_archive                     listing paging / filters
 *         host=<page URL>                  the page the code sits on
 *         err=<code>                       comment result message
 *       Links and canonical URLs use the Blog page URL (Admin > Install,
 *       option o_blog_page) when it is set, else the host page.
 *       SEO meta (written by the runtime in FORCE mode):
 *         post      post SEO, else global SEO (Options > SEO), else page tag
 *         category  category SEO, else page tag
 *         list / search   none - the page's own tags stay
 *
 *   index.php?controller=pjEmbed&action=pjActionComment   (POST, AJAX)
 *       Runs the existing pjLoad comment logic and answers JSON {code}
 *       instead of redirecting.
 */
class pjEmbed extends pjLoad
{
	/** The page the install code sits on (from the runtime), or ''. */
	protected $embedHost = '';

	/** Base URL for every blog link / canonical: Blog page URL, else host. */
	protected $linkBase = '';

	/** Active categories: id => public slug (lazy). */
	private $categorySlugs = null;

	/** Install-code request that also carries the content (see pjPrefetchParams). */
	private $prefetch = null;

	/** &post=<slug> of a single-post install code (before pjPrefetchParams). */
	private $fixedPost = '';

	public function beforeFilter()
	{
		// Map the host-page parameters onto the names the existing pjLoad /
		// pjFront code reads, BEFORE the parent builds the archive/category
		// sidebars from $_GET.
		$action = isset($_GET['action']) ? $_GET['action'] : '';
		if ($action == 'pjActionLoadJs')
		{
			$fixed = isset($_GET['post']) ? trim((string) $_GET['post']) : '';
			$this->fixedPost = preg_match('/^[\p{L}\p{N}_-]{1,255}$/u', $fixed) ? $fixed : '';
			$this->prefetch = $this->pjPrefetchParams();
		}
		if ($action == 'pjActionContentJs' || $this->prefetch !== null)
		{
			$this->pjMapParams();
		}

		parent::beforeFilter();

		$this->embedHost = pjUtil::cleanPageUrl(isset($_GET['host']) ? $_GET['host'] : '');
		$blogPage = pjUtil::cleanPageUrl(isset($this->option_arr['o_blog_page']) ? $this->option_arr['o_blog_page'] : '');
		$local = isset($_GET['local']) && (int) $_GET['local'] === 1;
		$this->linkBase = ($blogPage !== '' && !$local) ? $blogPage : $this->embedHost;
	}

	/**
	 * Install-code script: runtime + boot config.
	 */
	public function pjActionLoadJs()
	{
		$this->pjJsHeaders();
		$this->set('contentUrl', PJ_INSTALL_URL . 'index.php?controller=pjEmbed&action=pjActionContentJs');
		$this->set('fixedPost', $this->fixedPost);
		$theme = isset($_GET['theme']) ? (string) $_GET['theme'] : '';
		$this->set('previewTheme', preg_match('/^theme\d{1,2}$/', $theme) ? $theme : '');
		$this->set('local', isset($_GET['local']) && (int) $_GET['local'] === 1);
		if ($this->prefetch !== null)
		{
			// The blog content comes in this same response, so the page does
			// not wait for a second request (the runtime checks it matches).
			$this->pjActionContentJs();
			$this->set('prefetch', $this->prefetch);
		}
		$this->setLayout('pjActionEmpty');
	}

	/**
	 * The install-code script request carries the page it sits on in the
	 * Referer header (full URL for a page on the same website as the script).
	 * When it does, the blog parameters of that page are known here and the
	 * content can be sent with the runtime - one request instead of two, so
	 * the blog shows sooner. Returns the parameters (+ host) used, or null
	 * (other website / no Referer): the runtime then loads the content itself.
	 */
	private function pjPrefetchParams()
	{
		$ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
		$refHost = strtolower((string) parse_url($ref, PHP_URL_HOST));
		if ($ref === '' || $refHost === '' || $refHost !== strtolower((string) parse_url(PJ_INSTALL_URL, PHP_URL_HOST)))
		{
			return null;
		}
		$host = pjUtil::cleanPageUrl($ref);
		if ($host === '')
		{
			return null;
		}
		$query = array();
		parse_str((string) parse_url($ref, PHP_URL_QUERY), $query);
		$params = array();
		foreach (array('post', 'blog_post_id', 'blog_category', 'blog_page', 'blog_search', 'blog_archive') as $k)
		{
			if (isset($query[$k]) && is_string($query[$k]) && $query[$k] !== '')
			{
				$params[$k] = $query[$k];
			}
		}
		$single = false;
		if (empty($params) && $this->fixedPost !== '')
		{
			$params = array('post' => $this->fixedPost);
			$single = true;
		}
		// Hand them to pjMapParams() / pjActionContentJs() like the content request.
		foreach (array('post', 'blog_post_id', 'blog_category', 'blog_page', 'blog_search', 'blog_archive', 'single', 'err') as $k)
		{
			unset($_GET[$k]);
		}
		foreach ($params as $k => $v)
		{
			$_GET[$k] = $v;
		}
		if ($single)
		{
			$_GET['single'] = 1;
		}
		$_GET['host'] = $host;
		return array('params' => $params, 'host' => $host, 'single' => $single);
	}

	/**
	 * Listing or single post, rendered with the existing front views.
	 */
	public function pjActionContentJs()
	{
		$this->pjJsHeaders();
		$this->set('embed', true);
		$this->set('page_url', '');
		$this->set('linkBase', $this->linkBase);

		$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
		if ($postId > 0)
		{
			parent::pjActionView();
			$found = isset($this->tpl['arr']) && !empty($this->tpl['arr']);
			$this->set('embedView', 'detail');
			$this->set('found', $found);
			// single=1: the post comes from a single-post install code, so the
			// page it sits on IS the post's URL (canonical = that page).
			$single = isset($_GET['single']) && (int) $_GET['single'] === 1;
			$this->set('single', $single);
			if ($found)
			{
				$arr = $this->tpl['arr'];
				$url = ($single && $this->embedHost !== '') ? $this->embedHost : $this->postDetailUrl($arr);
				$this->set('page_url', $url);
				$this->set('embedMeta', $this->pjDetailMeta($arr, $url));
			}
		} elseif (isset($_GET['pj_post_missing'])) {
			$this->set('embedView', 'detail');
			$this->set('found', false);
		} else {
			parent::pjActionIndex();
			$this->set('embedView', 'list');
			$this->set('found', true);
			// Category listing: that category's SEO, else the page's own tags.
			// Search results and the plain list write no tags.
			if (isset($_GET['category_id']) && (int) $_GET['category_id'] > 0 && empty($_GET['dosearch']))
			{
				$cat = pjCategoryModel::factory()->where('status', 'T')->find((int) $_GET['category_id'])->getData();
				if (!empty($cat))
				{
					$this->set('embedMeta', $this->pjCategoryMeta($cat));
				}
			}
		}
		$this->setLayout('pjActionEmpty');
	}

	/**
	 * Comment submit from the embed (AJAX). Same validation / insert / e-mail
	 * as pjLoad::pjActionView(), answered as JSON instead of a redirect.
	 */
	public function pjActionComment()
	{
		$this->setAjax(true);
		if (!isset($_POST['post_comment']) || !isset($_POST['post_id']))
		{
			pjAppController::jsonResponse(array('code' => 0));
		}
		$this->pjJsonResult = true;
		parent::pjActionView();
		pjAppController::jsonResponse(array('code' => 0));
	}

	/**
	 * Post link: <blog page>?post=<url-slug>.
	 */
	public function postDetailUrl($arr, $htmlEncode = false)
	{
		if ($this->linkBase === '')
		{
			return parent::postDetailUrl($arr, $htmlEncode);
		}
		return $this->pjLink(array('post' => $this->pjPostSlug($arr)), $htmlEncode);
	}

	/**
	 * Category link: <blog page>?blog_category=<url-slug>.
	 */
	public function categoryUrl($id, $htmlEncode = false)
	{
		$slugs = $this->pjCategorySlugs();
		if ($this->linkBase === '' || !isset($slugs[(int) $id]))
		{
			return parent::categoryUrl($id, $htmlEncode);
		}
		return $this->pjLink(array('blog_category' => $slugs[(int) $id]), $htmlEncode);
	}

	/**
	 * <link base> + the given blog parameters (keeps any query the base
	 * page already has, e.g. ?page_id=12).
	 */
	private function pjLink($params, $htmlEncode = false)
	{
		$q = array();
		foreach ($params as $k => $v)
		{
			$q[] = rawurlencode($k) . '=' . rawurlencode((string) $v);
		}
		$url = $this->linkBase . (strpos($this->linkBase, '?') === false ? '?' : '&') . implode('&', $q);
		return $htmlEncode ? htmlspecialchars($url, ENT_QUOTES, 'UTF-8') : $url;
	}

	/**
	 * A post's public slug: stored url_slug, else friendlyURL(title).
	 */
	private function pjPostSlug($arr)
	{
		$slug = isset($arr['url_slug']) ? trim((string) $arr['url_slug']) : '';
		if ($slug === '')
		{
			$slug = $this->friendlyURL(stripslashes((string) $arr['title']));
		}
		return $slug !== '' ? $slug : ('post-' . (int) $arr['id']);
	}

	/**
	 * Active categories, id => public slug (stored url_slug, else
	 * friendlyURL(name), else category-<id>).
	 */
	private function pjCategorySlugs()
	{
		if ($this->categorySlugs === null)
		{
			$this->categorySlugs = array();
			$rows = pjCategoryModel::factory()->select('t1.id, t1.category, t1.url_slug')->where('t1.status', 'T')->findAll()->getData();
			foreach ($rows as $r)
			{
				$slug = trim((string) $r['url_slug']);
				if ($slug === '')
				{
					$slug = $this->friendlyURL(stripslashes((string) $r['category']));
				}
				$this->categorySlugs[(int) $r['id']] = $slug !== '' ? $slug : ('category-' . (int) $r['id']);
			}
		}
		return $this->categorySlugs;
	}

	/**
	 * ?blog_category value (url-slug or numeric id) -> category id, or 0.
	 */
	private function pjResolveCategory($value)
	{
		$value = trim((string) $value);
		if ($value === '')
		{
			return 0;
		}
		$slugs = $this->pjCategorySlugs();
		if (ctype_digit($value))
		{
			return isset($slugs[(int) $value]) ? (int) $value : 0;
		}
		$id = array_search($value, $slugs, true);
		if ($id !== false)
		{
			return (int) $id;
		}
		return preg_match('/^category-(\d+)$/', $value, $m) && isset($slugs[(int) $m[1]]) ? (int) $m[1] : 0;
	}

	/**
	 * Translate host-page parameters into the internal ones.
	 */
	private function pjMapParams()
	{
		$q = array('controller' => 'pjLoad', 'action' => 'pjActionIndex');
		unset($_GET['id'], $_GET['category_id'], $_GET['pjPage'], $_GET['keyword'], $_GET['dosearch'], $_GET['archive']);

		$postId = 0;
		if (isset($_GET['post']) && trim((string) $_GET['post']) !== '')
		{
			$postId = $this->pjResolveSlug((string) $_GET['post']);
			if ($postId === 0)
			{
				$_GET['pj_post_missing'] = 1;
			}
		} elseif (isset($_GET['blog_post_id']) && (int) $_GET['blog_post_id'] > 0) {
			$postId = (int) $_GET['blog_post_id'];
		}

		if ($postId > 0)
		{
			$_GET['id'] = $postId;
			$q['action'] = 'pjActionView';
			$q['id'] = $postId;
		} else {
			if (isset($_GET['blog_category']))
			{
				$catId = $this->pjResolveCategory($_GET['blog_category']);
				if ($catId > 0)
				{
					$_GET['category_id'] = $catId;
					$slugs = $this->pjCategorySlugs();
					$q['blog_category'] = $slugs[$catId];
				}
			}
			if (isset($_GET['blog_archive']) && preg_match('/^\d{4}(-\d{2})?$/', (string) $_GET['blog_archive']))
			{
				$_GET['archive'] = $q['archive'] = (string) $_GET['blog_archive'];
			}
			if (isset($_GET['blog_search']) && trim((string) $_GET['blog_search']) !== '')
			{
				$_GET['keyword'] = $q['keyword'] = (string) $_GET['blog_search'];
				$_GET['dosearch'] = $q['dosearch'] = 1;
			}
			if (isset($_GET['blog_page']) && (int) $_GET['blog_page'] > 1)
			{
				$_GET['pjPage'] = (int) $_GET['blog_page'];
			}
		}

		if (isset($_GET['err']))
		{
			$_GET['err'] = (int) $_GET['err'];
		}

		// The paginator builds its links from QUERY_STRING; give it the
		// internal form so the runtime can map them back to host params.
		$_SERVER['QUERY_STRING'] = http_build_query($q);
	}

	/**
	 * Slug -> post id (published posts only): stored url_slug first (newest
	 * wins if old data holds duplicates), then friendlyURL(title) for posts
	 * saved before url_slug existed, then post-<id>.
	 */
	private function pjResolveSlug($slug)
	{
		$slug = trim($slug);
		if ($slug === '')
		{
			return 0;
		}
		$pjPostModel = pjPostModel::factory();
		$row = $pjPostModel
			->where('t1.url_slug', $slug)
			->where('t1.status', 'T')
			->orderBy('t1.published_date DESC, t1.id DESC')
			->limit(1)
			->findAll()
			->getData();
		if (!empty($row))
		{
			return (int) $row[0]['id'];
		}
		$rows = $pjPostModel->reset()
			->select('t1.id, t1.title')
			->where("(t1.url_slug IS NULL OR t1.url_slug = '')")
			->where('t1.status', 'T')
			->findAll()
			->getData();
		foreach ($rows as $r)
		{
			if ($this->friendlyURL(stripslashes((string) $r['title'])) === $slug)
			{
				return (int) $r['id'];
			}
		}
		if (preg_match('/^post-(\d+)$/', $slug, $m))
		{
			return (int) $m[1];
		}
		return 0;
	}

	/**
	 * The post's SEO meta. Per field: the post's SEO tab, else the global SEO
	 * (Options > SEO); if both are empty the value stays '' and the runtime
	 * keeps the website page's own tag.
	 */
	private function pjDetailMeta($arr, $url)
	{
		$title = $this->pjSeoValue($arr, 'meta_title', 'o_meta_title');
		$description = trim(preg_replace('/\s+/', ' ', strip_tags($this->pjSeoValue($arr, 'meta_description', 'o_meta_description'))));
		$keywords = $this->pjSeoValue($arr, 'meta_keywords', 'o_meta_keywords');
		$published = !empty($arr['published_date']) ? date('c', strtotime($arr['published_date'])) : '';
		$modified = !empty($arr['modified']) ? date('c', strtotime($arr['modified'])) : $published;

		return array(
			'title'          => $title,
			'description'    => $description,
			'keywords'       => $keywords,
			'canonical'      => $url,
			'ogType'         => 'article',
			'ogImage'        => !empty($arr['medium_path']) ? PJ_INSTALL_URL . $arr['medium_path'] : '',
			'siteName'       => isset($this->option_arr['o_meta_title']) ? trim(stripslashes((string) $this->option_arr['o_meta_title'])) : '',
			'publishedTime'  => $published,
			'modifiedTime'   => $modified,
			'author'         => !empty($arr['name']) ? stripslashes((string) $arr['name']) : '',
			'section'        => !empty($arr['category']) ? stripslashes((string) $arr['category']) : '',
		);
	}

	/**
	 * A category listing's SEO meta: the category's own SEO fields only (no
	 * global fallback); an empty value stays '' and the runtime keeps the
	 * page's own tag. Canonical = <blog page>?blog_category=<url-slug>
	 * (+ &blog_page=<n> past page 1).
	 */
	private function pjCategoryMeta($cat)
	{
		$canonical = '';
		if ($this->linkBase !== '')
		{
			$params = array('blog_category' => $this->categoryUrlSlug($cat));
			if (isset($_GET['pjPage']) && (int) $_GET['pjPage'] > 1)
			{
				$params['blog_page'] = (int) $_GET['pjPage'];
			}
			$canonical = $this->pjLink($params);
		}
		return array(
			'title'       => trim(stripslashes((string) $cat['meta_title'])),
			'description' => trim(preg_replace('/\s+/', ' ', strip_tags(stripslashes((string) $cat['meta_description'])))),
			'keywords'    => trim(stripslashes((string) $cat['meta_keywords'])),
			'canonical'   => $canonical,
			'ogType'      => 'website',
			'ogImage'     => '',
			'siteName'    => isset($this->option_arr['o_meta_title']) ? trim(stripslashes((string) $this->option_arr['o_meta_title'])) : '',
		);
	}

	private function categoryUrlSlug($cat)
	{
		$slugs = $this->pjCategorySlugs();
		return isset($slugs[(int) $cat['id']]) ? $slugs[(int) $cat['id']] : ('category-' . (int) $cat['id']);
	}

	/**
	 * One SEO field: the post's own value, else the global option, else ''.
	 */
	private function pjSeoValue($arr, $postField, $optionKey)
	{
		$v = isset($arr[$postField]) ? trim(stripslashes((string) $arr[$postField])) : '';
		if ($v === '' && isset($this->option_arr[$optionKey]))
		{
			$v = trim(stripslashes((string) $this->option_arr[$optionKey]));
		}
		return $v;
	}

	/**
	 * A stylesheet of the blog as text, for the embed to put into the page
	 * together with the blog HTML (one response: no moment where the blog is
	 * shown before its CSS has arrived). Relative url(...) references
	 * (images, fonts) are made absolute, since the text is no longer read from
	 * the file's own folder. '' when the file cannot be read.
	 *
	 * @param string $relPath Path below the script folder, e.g. app/web/css/style.css
	 * @return string
	 */
	public function pjInlineCss($relPath)
	{
		$file = PJ_INSTALL_PATH . $relPath;
		$css = is_file($file) ? @file_get_contents($file) : false;
		if ($css === false)
		{
			return '';
		}
		$dir = PJ_INSTALL_URL . (dirname($relPath) !== '.' ? dirname($relPath) . '/' : '');
		return preg_replace_callback('/url\(\s*([\'"]?)([^\'")]+)\1\s*\)/i', function ($m) use ($dir) {
			$u = trim($m[2]);
			if (preg_match('~^(data:|https?:|//|/|#)~i', $u))
			{
				return $m[0];
			}
			return 'url(' . $m[1] . $dir . $u . $m[1] . ')';
		}, $css);
	}

	/**
	 * The responses bundle code + per-request data: never cache them.
	 */
	private function pjJsHeaders()
	{
		header("Content-Type: text/javascript; charset=utf-8");
		header("Access-Control-Allow-Origin: *");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");
		// The response carries the blog CSS + HTML (~240 KB): send it gzipped
		// (~35 KB) so the blog shows as soon as the page loads. ob_gzhandler
		// sends plain output to browsers that do not accept gzip. Skipped when
		// the server already compresses PHP output.
		static $gzip = false;
		if (!$gzip && extension_loaded('zlib') && !ini_get('zlib.output_compression') && !headers_sent())
		{
			$gzip = true;
			ob_start('ob_gzhandler');
		}
	}
}
?>