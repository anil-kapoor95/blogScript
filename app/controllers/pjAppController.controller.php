<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAppController extends pjController
{
	public $models = array();
	
	public $option_arr = array();

	public $defaultLocale = 'admin_locale_id';
	
	public $defaultFields = 'fields';
	
	public $defaultFieldsIndex = 'fields_index';
	
	protected function loadSetFields($force=FALSE, $locale_id=NULL, $fields=NULL)
	{
		if (is_null($locale_id))
		{
			$locale_id = $this->getLocaleId();
		}
		
		if (is_null($fields))
		{
			$fields = $this->defaultFields;
		}
		
		$registry = pjRegistry::getInstance();
		if ($force
				|| !isset($_SESSION[$this->defaultFieldsIndex])
				|| $_SESSION[$this->defaultFieldsIndex] != $this->option_arr['o_fields_index']
				|| !isset($_SESSION[$fields])
				|| empty($_SESSION[$fields])
				|| !isset($_SESSION[$this->defaultFieldsIndex . '_locale'])
				|| $_SESSION[$this->defaultFieldsIndex . '_locale'] != $locale_id)
		{
			pjAppController::setFields($locale_id);
	
			# Update session
			if ($registry->is('fields'))
			{
				$_SESSION[$fields] = $registry->get('fields');
			}
			$_SESSION[$this->defaultFieldsIndex] = $this->option_arr['o_fields_index'];
			# Remember which language the cached labels belong to, so switching language reloads them
			$_SESSION[$this->defaultFieldsIndex . '_locale'] = $locale_id;
		}
	
		if (isset($_SESSION[$fields]) && !empty($_SESSION[$fields]))
		{
			# Load fields from session
			$registry->set('fields', $_SESSION[$fields]);
		}
		
		return TRUE;
	}
	
	public function isOneAdminReady()
	{
		return $this->isAdmin();
	}
	
	public static function setTimezone($timezone="UTC")
    {
    	if (in_array(version_compare(phpversion(), '5.1.0'), array(0,1)))
		{
			date_default_timezone_set($timezone);
		} else {
			$safe_mode = ini_get('safe_mode');
			if ($safe_mode)
			{
				putenv("TZ=".$timezone);
			}
		}
    }

	public static function setMySQLServerTime($offset="-0:00")
    {
		pjAppModel::factory()->prepare("SET SESSION time_zone = :offset;")->exec(compact('offset'));
    }
    
	public function setTime()
	{
		if (isset($this->option_arr['o_timezone']))
		{
			$offset = $this->option_arr['o_timezone'] / 3600;
			if ($offset > 0)
			{
				$offset = "-".$offset;
			} elseif ($offset < 0) {
				$offset = "+".abs($offset);
			} elseif ($offset === 0) {
				$offset = "+0";
			}
	
			pjAppController::setTimezone('Etc/GMT' . $offset);
			if (strpos($offset, '-') !== false)
			{
				$offset = str_replace('-', '+', $offset);
			} elseif (strpos($offset, '+') !== false) {
				$offset = str_replace('+', '-', $offset);
			}
			pjAppController::setMySQLServerTime($offset . ":00");
		}
	}
    
    public function beforeFilter()
    {
    	$this->appendJs('jquery.min.js', PJ_THIRD_PARTY_PATH . 'jquery/');
		$baseDir = defined("PJ_INSTALL_PATH") ? PJ_INSTALL_PATH : null;
		$dm = new pjDependencyManager($baseDir, PJ_THIRD_PARTY_PATH);
		$dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();
		$this->appendJs('jquery-migrate.min.js', $dm->getPath('jquery_migrate'), FALSE, FALSE);
		$this->appendJs('pjAdminCore.js');
		$this->appendCss('reset.css');

		$this->appendJs('js/jquery-ui.custom.min.js', PJ_THIRD_PARTY_PATH . 'jquery_ui/');
		$this->appendCss('css/smoothness/jquery-ui.min.css', PJ_THIRD_PARTY_PATH . 'jquery_ui/');
				
		$this->appendCss('pj-all.css', PJ_FRAMEWORK_LIBS_PATH . 'pj/css/');
		$this->appendCss('admin.css');
		
    	if ($_GET['controller'] != 'pjInstaller')
		{
			$this->pjCheckCsrf();
			$this->models['Option'] = pjOptionModel::factory();
			$this->option_arr = $this->models['Option']->getPairs($this->getForeignId());
			$this->set('option_arr', $this->option_arr);
			$this->setTime();
			
			if (!isset($_SESSION[$this->defaultLocale]))
			{				
				$locale_arr = pjLocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->getData();
				if (count($locale_arr) === 1)
				{
					$this->setLocaleId($locale_arr[0]['id']);
				}
			}
			$this->loadSetFields();
		}
    }

	public function isAuthor()
    {
    	return $this->getRoleId() == 2;
    }

    /**
     * Returns the per-session CSRF token, generating one if needed, and
     * exposes it to the browser via a JS-readable cookie (admin panel).
     */
    public function pjGetCsrfToken()
    {
    	if (empty($_SESSION['pj_csrf_token']))
    	{
    		if (function_exists('random_bytes'))
    		{
    			$_SESSION['pj_csrf_token'] = bin2hex(random_bytes(32));
    		}
    		else
    		{
    			$_SESSION['pj_csrf_token'] = md5(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
    		}
    	}
    	if (!headers_sent())
    	{
    		setcookie('pj_csrf_token', $_SESSION['pj_csrf_token'], 0, '/');
    	}
    	return $_SESSION['pj_csrf_token'];
    }

    /**
     * Constant-time comparison of a submitted token against the session token.
     */
    public function pjValidateCsrf($token)
    {
    	if (empty($token) || empty($_SESSION['pj_csrf_token']))
    	{
    		return false;
    	}
    	if (function_exists('hash_equals'))
    	{
    		return hash_equals($_SESSION['pj_csrf_token'], (string) $token);
    	}
    	return $_SESSION['pj_csrf_token'] === (string) $token;
    }

    /**
     * CSRF guard for the admin panel. Validates a token on every state-changing
     * admin request (all POSTs plus state-changing GET actions). Authentication
     * endpoints are exempt to avoid lock-out. The public site (pjFront / pjLoad)
     * and the installer are not handled here. The token is read from a request
     * header (AJAX) or a hidden POST field (forms) - never from the URL.
     */
    public function pjCheckCsrf()
    {
    	$controller = isset($_GET['controller']) ? $_GET['controller'] : '';
    	if ($controller === 'pjFront' || $controller === 'pjLoad' || $controller === 'pjInstaller')
    	{
    		return;
    	}

    	// Make sure a token exists and is available to the browser.
    	$this->pjGetCsrfToken();

    	$action = isset($_GET['action']) ? $_GET['action'] : '';
    	$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

    	// Login / forgot / logout require no token (avoids admin lock-out).
    	if (in_array($action, array('pjActionLogin', 'pjActionForgot', 'pjActionLogout')))
    	{
    		return;
    	}

    	$needsCheck = false;
    	if ($method === 'POST')
    	{
    		$needsCheck = true;
    	}
    	elseif (preg_match('/^pjAction(Delete|Clone|Status|SetActive|Sort|Duplicate|Remove)/i', $action))
    	{
    		$needsCheck = true;
    	}

    	if (!$needsCheck)
    	{
    		return;
    	}

    	// Read the token from the POST body (csrf_token field) or, for AJAX, from
    	// the X-CSRF-Token header. Never from the URL / query string.
    	$submitted = '';
    	if (isset($_POST['csrf_token']))
    	{
    		$submitted = $_POST['csrf_token'];
    	}
    	elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN']))
    	{
    		$submitted = $_SERVER['HTTP_X_CSRF_TOKEN'];
    	}

    	if (!$this->pjValidateCsrf($submitted))
    	{
    		if ($this->isXHR())
    		{
    			header('HTTP/1.1 403 Forbidden');
    			echo '403';
    			exit;
    		}
    		pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminPosts&action=pjActionIndex&err=AG01");
    	}
    }

    public function getForeignId()
    {
    	return 1;
    }
    
    public static function setFields($locale)
    {
    	if(isset($_SESSION['lang_show_id']) && (int) $_SESSION['lang_show_id'] == 1)
		{
			$fields = pjMultiLangModel::factory()
				->select('CONCAT(t1.content, CONCAT(":", t2.id, ":")) AS content, t2.key')
				->join('pjField', "t2.id=t1.foreign_id", 'inner')
				->where('t1.locale', $locale)
				->where('t1.model', 'pjField')
				->where('t1.field', 'title')
				->findAll()
				->getDataPair('key', 'content');
		}else{
			$fields = pjMultiLangModel::factory()
				->select('t1.content, t2.key')
				->join('pjField', "t2.id=t1.foreign_id", 'inner')
				->where('t1.locale', $locale)
				->where('t1.model', 'pjField')
				->where('t1.field', 'title')
				->findAll()
				->getDataPair('key', 'content');
		}
		$registry = pjRegistry::getInstance();
		$tmp = array();
		if ($registry->is('fields'))
		{
			$tmp = $registry->get('fields');
		}
		$arrays = array();
		foreach ($fields as $key => $value)
		{
			if (strpos($key, '_ARRAY_') !== false)
			{
				list($prefix, $suffix) = explode("_ARRAY_", $key);
				if (!isset($arrays[$prefix]))
				{
					$arrays[$prefix] = array();
				}
				$arrays[$prefix][$suffix] = $value;
			}
		}
		require PJ_CONFIG_PATH . 'settings.inc.php';
		$fields = array_merge($tmp, $fields, $settings, $arrays);
		$registry->set('fields', $fields);
    }

    public static function jsonDecode($str)
	{
		$Services_JSON = new pjServices_JSON();
		return $Services_JSON->decode($str);
	}
	
	public static function jsonEncode($arr)
	{
		$Services_JSON = new pjServices_JSON();
		return $Services_JSON->encode($arr);
	}
	
	public static function jsonResponse($arr)
	{
		header("Content-Type: application/json; charset=utf-8");
		echo pjAppController::jsonEncode($arr);
		exit;
	}

	public function getLocaleId()
	{
		return isset($_SESSION[$this->defaultLocale]) && (int) $_SESSION[$this->defaultLocale] > 0 ? (int) $_SESSION[$this->defaultLocale] : false;
	}
	
	public function setLocaleId($locale_id)
	{
		$_SESSION[$this->defaultLocale] = (int) $locale_id;
	}
	public function pjActionCheckInstall()
	{
		$this->setLayout('pjActionEmpty');
		
		$result = array('status' => 'OK', 'code' => 200, 'text' => 'Operation succeeded', 'info' => array());
		$folders = array(
							'app/web/upload', 
							'app/web/upload/medium', 
							'app/web/upload/source', 
							'app/web/upload/thumb',
							'app/web/upload/tinymce-source', 
							'app/web/upload/tinymce-thumbs'
						);
		foreach ($folders as $dir)
		{
			if (!is_writable($dir))
			{
				$result['status'] = 'ERR';
				$result['code'] = 101;
				$result['text'] = 'Permission requirement';
				$result['info'][] = sprintf('Folder \'<span class="bold">%1$s</span>\' is not writable. You need to set write permissions (chmod 777) to directory located at \'<span class="bold">%1$s</span>\'', $dir);
			}
		}
		
		return $result;
	}
	public function friendlyURL($str, $divider='-')
	{
		$str = mb_strtolower($str, mb_detect_encoding($str));
		$str = trim($str);
		$str = str_replace(array('Ä','ä','É','é','Ö','ö','Ü','ü'), array('A','a','E','e','O','o','U','u'), $str);
    $str = preg_replace('/[^\w\s\-]+/u', '', $str);
		$str = preg_replace('/[_|\s]+/', $divider, $str);
		$str = preg_replace('/[-]+/', $divider, $str);
		$str = preg_replace('/^-+|-+$/', '', $str);
		return $str;
	}

	/* ------------------------------------------------------------------
	 * Multi-language content (categories and posts)
	 *
	 * Translations live in the `multi_lang` table (one row per model, record,
	 * language and field - the same storage the Locale plugin and the other
	 * PHPJabbers scripts use). The original columns of `categories` and
	 * `posts` stay as the default-language copy and as the fallback shown
	 * when a translation is missing or empty.
	 * ------------------------------------------------------------------ */

	/**
	 * Translatable fields per model.
	 *
	 * @var array
	 */
	public static $translatable = array(
		'pjCategory' => array('category', 'meta_title', 'meta_keywords', 'meta_description', 'url_slug'),
		'pjPost' => array('title', 'body', 'meta_title', 'meta_keywords', 'meta_description', 'url_slug'),
		// site-wide SEO defaults (Options > SEO), stored for the options record with ID 1
		'pjOption' => array('o_meta_title', 'o_meta_keywords', 'o_meta_description')
	);

	/**
	 * All languages (sorted), each with the flag image file, or an empty array when the
	 * Languages feature is not available. Cached per request.
	 *
	 * @access public
	 * @return array
	 */
	public function getLocales()
	{
		static $locales = NULL;
		if ($locales === NULL)
		{
			$locales = array();
			if (class_exists('pjLocaleModel'))
			{
				$locales = pjLocaleModel::factory()
					->select('t1.*, t2.file')
					->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
					->where('t2.file IS NOT NULL')
					->orderBy('t1.sort ASC, t1.id ASC')
					->findAll()
					->getData();
				foreach ($locales as $k => $locale)
				{
					$locales[$k]['flag_url'] = !empty($locale['flag']) && is_file(PJ_INSTALL_PATH . $locale['flag'])
						? PJ_INSTALL_URL . $locale['flag']
						: PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'];
				}
			}
		}
		return $locales;
	}

	/**
	 * Is the multi-language mode on and is there more than one language?
	 *
	 * @access public
	 * @return boolean
	 */
	public function isMultiLang()
	{
		return isset($this->option_arr['o_multi_lang']) && (int) $this->option_arr['o_multi_lang'] === 1 && count($this->getLocales()) > 1;
	}

	/**
	 * Name of a language for the AI prompt, e.g. "Deutsch (Deutschland) [de-DE]".
	 *
	 * @param integer $locale_id Language ID (the default language when empty)
	 * @access public
	 * @return string
	 */
	public function getLocaleLabel($locale_id = 0)
	{
		$locale_id = (int) $locale_id > 0 ? (int) $locale_id : (int) $this->getDefaultLocaleId();
		foreach ($this->getLocales() as $locale)
		{
			if ((int) $locale['id'] === $locale_id)
			{
				return trim((!empty($locale['name']) ? $locale['name'] : '') . ' [' . $locale['language_iso'] . ']');
			}
		}
		return '';
	}

	/**
	 * @access public
	 * @return integer|boolean Default language ID or FALSE
	 */
	public function getDefaultLocaleId()
	{
		foreach ($this->getLocales() as $locale)
		{
			if ((int) $locale['is_default'] === 1)
			{
				return (int) $locale['id'];
			}
		}
		$locales = $this->getLocales();
		return empty($locales) ? FALSE : (int) $locales[0]['id'];
	}

	/**
	 * Return the given language ID when it is a known language, otherwise the default one.
	 *
	 * @param mixed $locale_id
	 * @access public
	 * @return integer|boolean
	 */
	public function validLocaleId($locale_id)
	{
		foreach ($this->getLocales() as $locale)
		{
			if ((int) $locale['id'] === (int) $locale_id)
			{
				return (int) $locale['id'];
			}
		}
		return $this->getDefaultLocaleId();
	}

	/**
	 * Put the translated text over the rows' own (default-language) text. A missing or empty
	 * translation leaves the original value untouched, so the default language is the fallback.
	 *
	 * @param array $rows Rows with an 'id' key, changed in place. Pass a single row via translateRow().
	 * @param string $model pjCategory|pjPost
	 * @param integer|NULL $locale_id Defaults to the current language
	 * @access public
	 * @return array
	 */
	public function translateRows($rows, $model, $locale_id=NULL)
	{
		if (empty($rows) || !isset(self::$translatable[$model]) || !$this->isMultiLang())
		{
			return $rows;
		}
		if (is_null($locale_id))
		{
			$locale_id = $this->getLocaleId();
		}
		if (empty($locale_id))
		{
			return $rows;
		}
		$ids = array();
		foreach ($rows as $row)
		{
			if (isset($row['id']))
			{
				$ids[] = (int) $row['id'];
			}
		}
		if (empty($ids))
		{
			return $rows;
		}
		$tr = array();
		$data = pjMultiLangModel::factory()
			->where('t1.model', $model)
			->where('t1.locale', (int) $locale_id)
			->whereIn('t1.foreign_id', $ids)
			->whereIn('t1.field', self::$translatable[$model])
			->findAll()
			->getData();
		$is_default_locale = (int) $locale_id === (int) $this->getDefaultLocaleId();
		foreach ($data as $item)
		{
			if ($item['field'] === 'url_slug' && $is_default_locale)
			{
				continue;
			}
			if ($item['content'] !== NULL && trim($item['content']) !== '')
			{
				$tr[(int) $item['foreign_id']][$item['field']] = $item['content'];
			}
		}
		foreach ($rows as $k => $row)
		{
			if (isset($row['id']) && isset($tr[(int) $row['id']]))
			{
				foreach ($tr[(int) $row['id']] as $field => $content)
				{
					$rows[$k][$field] = $content;
				}
			}
		}
		return $rows;
	}

	/**
	 * Put the site-wide SEO defaults (title, keywords, description) of the language being browsed over
	 * the values saved in the options. An empty translation keeps the default-language value.
	 *
	 * @access public
	 * @return void
	 */
	public function translateOptions()
	{
		if (!$this->isMultiLang() || !$this->getLocaleId())
		{
			return;
		}
		$data = pjMultiLangModel::factory()
			->where('t1.model', 'pjOption')
			->where('t1.foreign_id', $this->getForeignId())
			->where('t1.locale', (int) $this->getLocaleId())
			->findAll()
			->getData();
		foreach ($data as $item)
		{
			if (in_array($item['field'], self::$translatable['pjOption']) && $item['content'] !== NULL && trim($item['content']) !== '')
			{
				$this->option_arr[$item['field']] = $item['content'];
			}
		}
		$this->set('option_arr', $this->option_arr);
	}

	/**
	 * Same as translateRows() for one row.
	 *
	 * @access public
	 * @return array
	 */
	public function translateRow($row, $model, $locale_id=NULL)
	{
		if (empty($row) || !isset($row['id']))
		{
			return $row;
		}
		$rows = $this->translateRows(array($row), $model, $locale_id);
		return $rows[0];
	}

	/**
	 * Translations of one record, grouped as array(locale_id => array(field => content)).
	 *
	 * @access public
	 * @return array
	 */
	public function getTranslations($model, $id)
	{
		return pjMultiLangModel::factory()->getMultiLang((int) $id, $model);
	}

	/**
	 * Save the text of one language of a record into `multi_lang`.
	 *
	 * @param string $model pjCategory|pjPost
	 * @param integer $id Record ID
	 * @param integer $locale_id Language ID
	 * @param array $values array(field => content); fields that are not translatable are ignored
	 * @access public
	 * @return void
	 */
	public function saveTranslation($model, $id, $locale_id, $values)
	{
		if (!isset(self::$translatable[$model]) || empty($locale_id) || (int) $id <= 0)
		{
			return;
		}
		$data = array();
		foreach (self::$translatable[$model] as $field)
		{
			// the URL slug of the default language lives in the original column only
			if ($field === 'url_slug' && (int) $locale_id === (int) $this->getDefaultLocaleId())
			{
				continue;
			}
			if (array_key_exists($field, $values))
			{
				$data[$field] = is_null($values[$field]) ? '' : $values[$field];
			}
		}
		if (!empty($data))
		{
			pjMultiLangModel::factory()->updateMultiLang(array((int) $locale_id => $data), (int) $id, $model, 'data');
		}
	}

	/**
	 * Remove all the translations of a record (used when the record is deleted).
	 *
	 * @access public
	 * @return void
	 */
	public function eraseTranslations($model, $ids)
	{
		$ids = array_filter(array_map('intval', (array) $ids));
		if (!empty($ids))
		{
			pjMultiLangModel::factory()->where('model', $model)->whereIn('foreign_id', $ids)->eraseAll();
		}
	}

	/**
	 * Language ID for a "blog_lang" value: a language code (e.g. fr-FR) or a language ID. Empty or unknown
	 * values give the default language.
	 *
	 * @param mixed $value
	 * @access public
	 * @return integer|boolean
	 */
	public function localeFromParam($value)
	{
		$value = is_string($value) ? trim($value) : '';
		if ($value !== '')
		{
			foreach ($this->getLocales() as $locale)
			{
				if (strcasecmp($locale['language_iso'], $value) === 0 || (ctype_digit($value) && (int) $locale['id'] === (int) $value))
				{
					return (int) $locale['id'];
				}
			}
		}
		return $this->getDefaultLocaleId();
	}

	/**
	 * Language code of a language ID (e.g. fr-FR), or ''.
	 *
	 * @access public
	 * @return string
	 */
	public function localeIso($locale_id)
	{
		foreach ($this->getLocales() as $locale)
		{
			if ((int) $locale['id'] === (int) $locale_id)
			{
				return $locale['language_iso'];
			}
		}
		return '';
	}

	/**
	 * The URL slug to save for a translation: cleaned like every slug, and made unique among the slugs
	 * of the same language (-2, -3 ...). An empty slug is made from $text; if that is empty too the
	 * translation has no slug of its own and the default-language slug is used.
	 *
	 * @access public
	 * @return string
	 */
	public function translatedSlug($model, $id, $locale_id, $posted, $text='')
	{
		$base = trim($this->friendlyURL(stripslashes(trim((string) $posted))), '-');
		if ($base === '')
		{
			$base = trim($this->friendlyURL(stripslashes(trim((string) $text))), '-');
		}
		$base = function_exists('mb_substr') ? mb_substr($base, 0, 200) : substr($base, 0, 200);
		if ($base === '')
		{
			return '';
		}
		$slug = $base;
		for ($n = 2; $n < 200; $n++)
		{
			$count = pjMultiLangModel::factory()
				->where('t1.model', $model)
				->where('t1.field', 'url_slug')
				->where('t1.locale', (int) $locale_id)
				->where('t1.content', $slug)
				->where('t1.foreign_id <> ' . (int) $id)
				->findCount()
				->getData();
			if ((int) $count === 0)
			{
				return $slug;
			}
			$slug = $base . '-' . $n;
		}
		return $base . '-' . uniqid();
	}

	/**
	 * Language part of a front-end URL: empty for the default language (so the existing URLs
	 * do not change), "locale=ID" for every other language.
	 *
	 * @param integer|NULL $locale_id Defaults to the current language
	 * @access public
	 * @return string
	 */
	public function localeQuery($locale_id=NULL)
	{
		if (!$this->isMultiLang())
		{
			return '';
		}
		if (is_null($locale_id))
		{
			$locale_id = $this->getLocaleId();
		}
		if (empty($locale_id) || (int) $locale_id === (int) $this->getDefaultLocaleId())
		{
			return '';
		}
		return 'blog_lang=' . rawurlencode($this->localeIso($locale_id));
	}

	/**
	 * Append the language part to a URL.
	 *
	 * @param string $url
	 * @param boolean $htmlEncode Use '&amp;'
	 * @param integer|NULL $locale_id
	 * @access public
	 * @return string
	 */
	public function addLocaleToUrl($url, $htmlEncode=false, $locale_id=NULL)
	{
		$q = $this->localeQuery($locale_id);
		if ($q === '')
		{
			return $url;
		}
		$amp = $htmlEncode ? '&amp;' : '&';
		return $url . (strpos($url, '?') === false ? '?' : $amp) . $q;
	}

	/**
	 * Absolute URL of the page being shown in another language: the same address with the
	 * "locale" parameter set (and dropped for the default language).
	 *
	 * @param integer $locale_id
	 * @param boolean $htmlEncode
	 * @access public
	 * @return string
	 */
	public function switchLocaleUrl($locale_id, $htmlEncode=false)
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'];
		$parts = explode('?', $uri, 2);
		$params = array();
		if (isset($parts[1]) && $parts[1] !== '')
		{
			foreach (explode('&', $parts[1]) as $pair)
			{
				$k = urldecode(current(explode('=', $pair, 2)));
				if ($pair !== '' && $k !== 'blog_lang')
				{
					$params[] = $pair;
				}
			}
		}
		if ((int) $locale_id !== (int) $this->getDefaultLocaleId())
		{
			$params[] = 'blog_lang=' . rawurlencode($this->localeIso($locale_id));
		}
		$url = $parts[0] . (empty($params) ? '' : '?' . implode('&', $params));
		return $htmlEncode ? str_replace('&', '&amp;', $url) : $url;
	}

	/**
	 * Translate post rows and, when they carry the category name (the 'category' key from the
	 * joined category), the category name too.
	 *
	 * @access public
	 * @return array
	 */
	public function translatePostRows($rows, $locale_id=NULL)
	{
		if (empty($rows) || !$this->isMultiLang())
		{
			return $rows;
		}
		$rows = $this->translateRows($rows, 'pjPost', $locale_id);
		$cat_ids = array();
		foreach ($rows as $row)
		{
			if (!empty($row['category_id']) && array_key_exists('category', $row))
			{
				$cat_ids[(int) $row['category_id']] = (int) $row['category_id'];
			}
		}
		if (!empty($cat_ids))
		{
			$cats = array();
			foreach ($this->translateRows(pjCategoryModel::factory()->whereIn('t1.id', array_values($cat_ids))->findAll()->getData(), 'pjCategory', $locale_id) as $cat)
			{
				$cats[(int) $cat['id']] = $cat['category'];
			}
			foreach ($rows as $k => $row)
			{
				if (!empty($row['category_id']) && isset($cats[(int) $row['category_id']]))
				{
					$rows[$k]['category'] = $cats[(int) $row['category_id']];
				}
			}
		}
		return $rows;
	}

	/**
	 * Run a callback as if the visitor browsed in another language (the language is put back
	 * afterwards). Used to build the links of the other language versions of a page.
	 *
	 * @access public
	 * @return mixed
	 */
	public function runInLocale($locale_id, $callback)
	{
		$had = isset($_SESSION[$this->defaultLocale]);
		$prev = $had ? $_SESSION[$this->defaultLocale] : NULL;
		$this->setLocaleId($locale_id);
		$result = call_user_func($callback, $locale_id);
		if ($had)
		{
			$_SESSION[$this->defaultLocale] = $prev;
		} else {
			unset($_SESSION[$this->defaultLocale]);
		}
		return $result;
	}

	/**
	 * Percent-encode the non-ASCII characters of a URL (a slug in another language), leaving the rest as is.
	 *
	 * @access public
	 * @return string
	 */
	public function encodeUrl($url)
	{
		return preg_replace_callback('/[^\x00-\x7F]+/', function ($m) { return rawurlencode($m[0]); }, $url);
	}

	/**
	 * hreflang alternates of a page: array(array('hreflang' => 'en-GB', 'url' => 'http://...'), ...),
	 * plus an "x-default" entry for the default language. Empty when there is only one language.
	 *
	 * @param callable $builder function($locale_id) returning the relative URL of the page in that language, or NULL to skip it
	 * @access public
	 * @return array
	 */
	public function buildAlternates($builder)
	{
		if (!$this->isMultiLang())
		{
			return array();
		}
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
		$default_id = $this->getDefaultLocaleId();
		$alternates = array();
		$default_url = NULL;
		foreach ($this->getLocales() as $locale)
		{
			$url = $this->runInLocale($locale['id'], $builder);
			if ($url === NULL || $url === '')
			{
				continue;
			}
			$url = $scheme . $_SERVER['HTTP_HOST'] . $this->encodeUrl($url);
			$alternates[] = array('hreflang' => $locale['language_iso'], 'url' => $url);
			if ((int) $locale['id'] === (int) $default_id)
			{
				$default_url = $url;
			}
		}
		if ($default_url !== NULL)
		{
			$alternates[] = array('hreflang' => 'x-default', 'url' => $default_url);
		}
		return $alternates;
	}

	/**
	 * Language code of the language being browsed, for og:locale (e.g. en_GB), or '' when
	 * the multi-language mode is off.
	 *
	 * @access public
	 * @return string
	 */
	public function currentHtmlLocale()
	{
		if (!$this->isMultiLang())
		{
			return '';
		}
		foreach ($this->getLocales() as $locale)
		{
			if ((int) $locale['id'] === (int) $this->getLocaleId())
			{
				return str_replace('-', '_', $locale['language_iso']);
			}
		}
		return '';
	}

	/**
	 * Link to a single post. The JS embed (pjEmbed) overrides this with the
	 * blog page URL + ?post=<url-slug>; this script-URL form is only used when
	 * the front views are opened directly.
	 *
	 * @param array $arr Post row - must include 'id'
	 * @param boolean $htmlEncode Use '&amp;' instead of '&' (for use inside an HTML attribute)
	 * @param integer|NULL $locale_id Language of the link (current language by default). Pass a post row already translated to that language.
	 * @access public
	 * @return string
	 */
	public function postDetailUrl($arr, $htmlEncode = false, $locale_id = NULL)
	{
		$amp = $htmlEncode ? '&amp;' : '&';
		$url = $_SERVER['SCRIPT_NAME'] . '?controller=pjLoad' . $amp . 'action=pjActionView' . $amp . 'id=' . (int) $arr['id'];
		if (isset($_GET['iframe']))
		{
			$url .= $amp . 'iframe';
		}
		return $this->addLocaleToUrl($url, $htmlEncode, $locale_id);
	}

	/**
	 * Link to a category listing. The JS embed (pjEmbed) overrides this with
	 * the blog page URL + ?blog_category=<url-slug>; this script-URL form is
	 * only used when the front views are opened directly.
	 *
	 * @param int $id Category id
	 * @param boolean $htmlEncode Use '&amp;' instead of '&' (for use inside an HTML attribute)
	 * @access public
	 * @return string
	 */
	public function categoryUrl($id, $htmlEncode = false, $locale_id = NULL)
	{
		$amp = $htmlEncode ? '&amp;' : '&';
		$url = $_SERVER['SCRIPT_NAME'] . '?controller=pjLoad' . $amp . 'action=pjActionIndex' . $amp . 'category_id=' . (int) $id;
		if (isset($_GET['iframe']))
		{
			$url .= $amp . 'iframe';
		}
		return $this->addLocaleToUrl($url, $htmlEncode, $locale_id);
	}

	public function getFromEmail()
	{
		if (isset($this->option_arr['o_from_email']) && !empty($this->option_arr['o_from_email']))
		{
			return $this->option_arr['o_from_email'];
		}
		$arr = pjUserModel::factory()->find(1)->getData();
		$email = $arr['email'];
		return $email;
	}
	
	public function isAllComments($id)
	{
		$is_all = false;
		$arr = pjUserModel::factory()->find($id)->getData();
		if(!empty($arr))
		{
			if($arr['receive_notify'] == 'A')
			{
				$is_all = true;
			}
		}
		return $is_all;
	}
}
?>