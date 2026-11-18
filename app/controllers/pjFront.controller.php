<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFront extends pjAppController
{
	public $defaultCaptcha = 'PHPJabbersCaptcha';
	public $defaultSendCaptcha = 'PHPJabbersSendCaptcha';
	public $defaultLocale = 'front_locale_id';
	public $defaultStatus = 'front_view_status';
	public $defaultTheme = 'front_theme_id';
	
	public function __construct()
	{
		$this->setLayout('pjActionFront');
	}

	public function afterFilter()
	{		
		$dm = new pjDependencyManager(PJ_INSTALL_PATH, PJ_THIRD_PARTY_PATH);
		$dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();
		
		$theme = $this->option_arr['o_theme'];
		if(isset($_SESSION[$this->defaultTheme]))
		{
			$theme = $_SESSION[$this->defaultTheme];
		}
		$this->appendCss('pj.bootstrap.min.css', PJ_FRAMEWORK_LIBS_PATH . 'pj/css/');
		$this->appendCss('style.css', PJ_CSS_PATH);
		$this->appendCss($theme . '.css', PJ_CSS_PATH . 'fonts/');
		$this->appendCss($theme . '.css', PJ_CSS_PATH . 'themes/');
	}
	
    // 	public function beforeFilter()
    // 	{
    // 		$OptionModel = pjOptionModel::factory();
    // 		$this->option_arr = $OptionModel->getPairs($this->getForeignId());
    // 		$this->set('option_arr', $this->option_arr);
    // 		$this->setTime();
    
    // 		if (!isset($_SESSION[$this->defaultLocale]))
    // 		{
    // 			$locale_arr = pjLocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->getData();
    // 			if (count($locale_arr) === 1)
    // 			{
    // 				$this->setLocaleId($locale_arr[0]['id']);
    // 			}
    // 		}
    // 		if(isset($_GET['theme']))
    // 		{
    // 			$theme = pjObject::escapeString($_GET['theme']);
    // 			if(!empty($theme))
    // 			{
    // 				$_SESSION[$this->defaultTheme] = $theme;
    // 			}
    // 		}
    // 		$this->loadSetFields();
    		
    // 		$pjPostModel = pjPostModel::factory();
    // 		if(isset($_GET['category_id']) && (int) $_GET['category_id'] > 0)
    // 		{
    // 			$pjPostModel->where('t1.category_id', (int) $_GET['category_id']);
    // 		}
    		
    // 		$current_year_arr = $pjPostModel
    // 		->reset()
    // 		->where('t1.status', 'T')
    // 		->where("YEAR(published_date) = YEAR(CURDATE())")
    // 		->orderBy("published_date DESC")
    // 		->findAll()
    // 		->getData();
    		
    // 		$tmp_arr = array();
    // 		foreach($current_year_arr as $v)
    // 		{
    // 		    $year = date('Y', strtotime($v['published_date']));
    // 		    $month = date('n', strtotime($v['published_date']));
    // 		    if(empty($tmp_arr))
    // 		    {
    // 		        $tmp_arr[] = array('pub_year' => $year, 'pub_month' => $month, 'cnt_posts' => 1);
    // 		    }else{
    // 		        foreach($tmp_arr as $k => $item)
    // 		        {
    // 		            if($item['pub_year'] == $year && $item['pub_month'] == $month)
    // 		            {
    // 		                $tmp_arr[$k]['cnt_posts'] += 1;
    // 		            }else{
    // 		                $tmp_arr[] = array('pub_year' => $year, 'pub_month' => $month, 'cnt_posts' => 1);
    // 		            }
    // 		        }
    // 		    }
    // 		}
    // 		$current_year_arr = $tmp_arr;
    		
    // 		$pjPostModel->reset();
    // 		if(isset($_GET['category_id']) && (int) $_GET['category_id'] > 0)
    // 		{
    // 			$pjPostModel->where('t1.category_id', (int) $_GET['category_id']);
    // 		}
    // 		$past_year_arr = $pjPostModel
    // 		->where('t1.status', 'T')
    // 		->where("YEAR(published_date) < YEAR(CURDATE())")
    // 		->orderBy("published_date DESC")
    // 		->findAll()
    // 		->getData();
    		
    // 		$tmp_arr = array();
    // 		foreach($past_year_arr as $v)
    // 		{
    // 		    $year = date('Y', strtotime($v['published_date']));
    // 		    if(empty($tmp_arr))
    // 		    {
    // 		        $tmp_arr[] = array('pub_year' => $year, 'cnt_posts' => 1);
    // 		    }else{
    // 		        foreach($tmp_arr as $k => $item)
    // 		        {
    // 		            if($item['pub_year'] == $year)
    // 		            {
    // 		                $tmp_arr[$k]['cnt_posts'] += 1;
    // 		            }else{
    // 		                $tmp_arr[] = array('pub_year' => $year, 'cnt_posts' => 1);
    // 		            }
    // 		        }
    // 		    }
    // 		}
    // 		$past_year_arr = $tmp_arr;
    		
    // 		$category_arr = pjCategoryModel::factory()
    // 			->select("t1.*, (SELECT COUNT(`TP`.id) FROM `".$pjPostModel->getTable()."` AS `TP` WHERE `TP`.category_id=t1.id AND `TP`.status='T') AS cnt_posts")
    // 			->where('status', 'T')
    // 			->findAll()
    // 			->getData();
    		
    // 		$this->set('current_year_arr', $current_year_arr);
    // 		$this->set('past_year_arr', $past_year_arr);
    // 		$this->set('category_arr', $category_arr);
    // 	}

    public function beforeFilter()
	{
		$OptionModel = pjOptionModel::factory();
		$this->option_arr = $OptionModel->getPairs($this->getForeignId());
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
		if(isset($_GET['theme']))
		{
			$theme = pjObject::escapeString($_GET['theme']);
			if(!empty($theme))
			{
				$_SESSION[$this->defaultTheme] = $theme;
			}
		}
		// Language of this request: ?blog_lang=<language code> (the default language has no
		// parameter, so the existing URLs do not change). It is read on every request - nothing is
		// kept in the session - because the blog may be embedded in a page on another website.
		$lang = isset($_GET['blog_lang']) ? $_GET['blog_lang'] : (isset($_GET['locale']) ? $_GET['locale'] : '');
		if (count($this->getLocales()) > 0)
		{
			$this->setLocaleId($this->localeFromParam(is_scalar($lang) ? (string) $lang : ''));
		}
		$this->loadSetFields();
		$this->translateOptions();
		
		$pjPostModel = pjPostModel::factory();
		if(isset($_GET['category_id']) && (int) $_GET['category_id'] > 0)
		{
			$pjPostModel->where('t1.category_id', (int) $_GET['category_id']);
		}
		
		// Archive menu: published posts grouped by month (current year onwards)
		// and by year (earlier years). Uses the same visibility rules as the post
		// list in pjLoad::pjActionIndex() - published, in an active category (or
		// none), and inside the category currently being browsed - so the counts
		// shown here always match what you get after clicking an entry.
		$archive_model = pjPostModel::factory()
			->select('t1.published_date')
			->where('t1.status', 'T')
			->where("((t1.category_id = 0) OR (t1.category_id > 0 AND t1.category_id IN(SELECT TC.id FROM `".pjCategoryModel::factory()->getTable()."` AS TC WHERE TC.status='T')))");
		if(isset($_GET['category_id']) && (int) $_GET['category_id'] > 0)
		{
			$archive_model->where('t1.category_id', (int) $_GET['category_id']);
		}
		$archive_rows = $archive_model
			->orderBy("published_date DESC")
			->findAll()
			->getData();

		$this_year = (int) date('Y');
		$current_year_arr = array();
		$past_year_arr = array();
		foreach($archive_rows as $v)
		{
			$ts = strtotime($v['published_date']);
			if($ts === false)
			{
				continue;
			}
			$year = (int) date('Y', $ts);
			if($year >= $this_year)
			{
				$month = (int) date('n', $ts);
				$key = $year . '-' . $month;
				if(!isset($current_year_arr[$key]))
				{
					$current_year_arr[$key] = array('pub_year' => $year, 'pub_month' => $month, 'cnt_posts' => 0);
				}
				$current_year_arr[$key]['cnt_posts']++;
			}else{
				if(!isset($past_year_arr[$year]))
				{
					$past_year_arr[$year] = array('pub_year' => $year, 'cnt_posts' => 0);
				}
				$past_year_arr[$year]['cnt_posts']++;
			}
		}
		$current_year_arr = array_values($current_year_arr);
		$past_year_arr = array_values($past_year_arr);
		
		$category_arr = pjCategoryModel::factory()
			->select("t1.*, (SELECT COUNT(`TP`.id) FROM `".$pjPostModel->getTable()."` AS `TP` WHERE `TP`.category_id=t1.id AND `TP`.status='T') AS cnt_posts")
			->where('status', 'T')
			->findAll()
			->getData();
		$category_arr = $this->translateRows($category_arr, 'pjCategory');
		
		$this->set('current_year_arr', $current_year_arr);
		$this->set('past_year_arr', $past_year_arr);
		$this->set('category_arr', $category_arr);
	}
	
	public function beforeRender()
	{
		if (isset($_GET['iframe']))
		{
			$this->setLayout('pjActionIframe');
		}
	}
	public function pjActionCaptcha()
	{
		$this->setAjax(true);
		
		$Captcha = new pjCaptcha('app/web/obj/Anorexia.ttf', $this->defaultCaptcha, 6);
		$Captcha->setImage('app/web/img/button.png')->init(isset($_GET['rand']) ? $_GET['rand'] : null);
	}
	public function pjActionCaptchaSend()
	{
		$this->setAjax(true);
		
		$Captcha = new pjCaptcha('app/web/obj/Anorexia.ttf', $this->defaultSendCaptcha, 6);
		$Captcha->setImage('app/web/img/button.png')->init(isset($_GET['rand']) ? $_GET['rand'] : null);
	}

	public function pjActionCheckCaptcha()
	{
		$this->setAjax(true);
				
		if (!isset($_GET['captcha']) || empty($_GET['captcha']) || strtoupper($_GET['captcha']) != $_SESSION[$this->defaultCaptcha]){
			echo 'false';
		}else{
			echo 'true';
		}
	}
	public function pjActionCheckSendCaptcha()
	{
		$this->setAjax(true);
				
		if (!isset($_GET['captcha']) || empty($_GET['captcha']) || strtoupper($_GET['captcha']) != $_SESSION[$this->defaultSendCaptcha]){
			echo 'false';
		}else{
			echo 'true';
		}
	}
	public function pjActionSetLocale()
	{
		$this->setLocaleId(@$_GET['locale']);
		pjUtil::redirect($_SERVER['HTTP_REFERER']);
	}

	/**
	 * Verify a Google reCAPTCHA response against Google's siteverify endpoint
	 * using the configured secret key. Returns true only on a confirmed success.
	 */
	protected function pjVerifyRecaptcha($response)
	{
		$secret = isset($this->option_arr['o_recaptcha_secret_key']) ? trim($this->option_arr['o_recaptcha_secret_key']) : '';
		if ($secret == '' || $response == '')
		{
			return false;
		}
		$data = array(
			'secret'   => $secret,
			'response' => $response,
			'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
		);
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$result = false;
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			$result = curl_exec($ch);
			curl_close($ch);
		}
		// Fall back to the stream wrapper if cURL is unavailable or failed.
		if ($result === false || $result === '' || $result === null)
		{
			$context = stream_context_create(array(
				'http' => array(
					'method'  => 'POST',
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'content' => http_build_query($data),
					'timeout' => 20
				)
			));
			$result = @file_get_contents($url, false, $context);
		}
		if ($result === false || $result === '')
		{
			return false;
		}
		$json = json_decode($result, true);
		return (isset($json['success']) && $json['success'] == true);
	}
}
?>