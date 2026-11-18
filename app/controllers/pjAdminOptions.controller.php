<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminOptions extends pjAdmin
{
	public function pjActionIndex()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$this->set('arr', $arr);
			
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('additional-methods.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	/**
	 * Email Settings screen (mail transport, SMTP configuration,
	 * test connection & send test email).
	 */
	public function pjActionEmailSettings()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionAiSettings()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			// Option values are available to the view via $tpl['option_arr'].
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionVerifyAiKey()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('valid' => false, 'text' => __('aiVerifyInvalidReq', true)));
		}
		if (!$this->isAdmin())
		{
			pjAppController::jsonResponse(array('valid' => false, 'text' => __('aiVerifyForbidden', true)));
		}

		$provider = isset($_POST['provider']) ? trim($_POST['provider']) : '';
		$key      = isset($_POST['api_key'])  ? trim($_POST['api_key'])  : '';

		if ($key === '')
		{
			pjAppController::jsonResponse(array('valid' => false, 'text' => __('aiMsgEnterKey', true)));
		}
		if (!function_exists('curl_init'))
		{
			pjAppController::jsonResponse(array('valid' => false, 'text' => __('aiVerifyNoCurl', true)));
		}

		// Provider request/response handling lives in pjAi so pjActionGenerateAi
		// (AI Writer, in pjAdminPosts) can reuse the exact same cURL logic.
		$result = pjAi::verify($provider, $key);

		pjAppController::jsonResponse($result);
	}

	/**
	 * AJAX: persist a custom model ID typed into the "Custom..." field next
	 * to the Model dropdown on the AI Settings tab (pjActionAiSettings), so
	 * it shows up there as a normal selectable option from then on - for any
	 * admin, not just this browser - instead of having to be retyped every
	 * time. Stored per-provider as a small JSON array in the
	 * o_ai_custom_models option; this only ever appends a new id, it never
	 * removes or reorders what's already saved.
	 */
	public function pjActionAiAddModel()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyInvalidReq', true)));
		}
		if (!$this->isAdmin())
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyForbidden', true)));
		}

		$provider = isset($_POST['provider']) ? trim((string) $_POST['provider']) : '';
		$model    = isset($_POST['model'])    ? trim((string) $_POST['model'])    : '';

		if ($provider === '' || $model === '')
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiMsgEnterModel', true)));
		}
		// A model id is effectively a free-form identifier, not user-facing
		// prose - keep it to characters providers actually use (letters,
		// digits, dot/colon/slash/underscore/hyphen) so it can't smuggle in
		// anything unexpected via this field.
		if (mb_strlen($model) > 150 || !preg_match('/^[A-Za-z0-9_.:\/-]+$/', $model))
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiMsgAddModelFailed', true)));
		}

		$raw = isset($this->option_arr['o_ai_custom_models']) ? $this->option_arr['o_ai_custom_models'] : '{}';
		$map = json_decode((string) $raw, true);
		if (!is_array($map))
		{
			$map = array();
		}
		if (!isset($map[$provider]) || !is_array($map[$provider]))
		{
			$map[$provider] = array();
		}
		if (!in_array($model, $map[$provider], true))
		{
			$map[$provider][] = $model;
		}

		pjOptionModel::factory()
			->reset()
			->where('foreign_id', $this->getForeignId())
			->where('`key`', 'o_ai_custom_models')
			->limit(1)
			->modifyAll(array('value' => json_encode($map)));

		pjAppController::jsonResponse(array('ok' => true, 'models' => $map[$provider]));
	}

	public function pjActionAddOns()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();

			$this->set('arr', $arr);
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionProtection()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$this->set('arr', $arr);
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionNotification()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			if (isset($_POST['notification_update']))
			{
				$pjOptionModel = pjOptionModel::factory();
				
				$pjOptionModel
					->reset()
					->where('foreign_id', $this->getForeignId())
					->where('`key`', 'o_notify_subject')
					->limit(1)
					->modifyAll(array('value' => $_POST['o_notifiy_subject']));
				$pjOptionModel
					->reset()
					->where('foreign_id', $this->getForeignId())
					->where('`key`', 'o_notify_message')
					->limit(1)
					->modifyAll(array('value' => $_POST['o_notify_message']));
				
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminOptions&action=pjActionNotification&err=AN01");
			}else{
				$arr = pjOptionModel::factory()
					->where('t1.foreign_id', $this->getForeignId())
					->orderBy('t1.order ASC')
					->findAll()
					->getData();
				
				$this->set('arr', $arr);
				
				$this->appendJs('jquery.multiselect.min.js', PJ_THIRD_PARTY_PATH . 'multiselect/');
				$this->appendCss('jquery.multiselect.css', PJ_THIRD_PARTY_PATH . 'multiselect/');
				$this->appendJs('pjAdminOptions.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSeo()
	{
		$this->checkLogin();
	
		if ($this->isAdmin())
		{
			$requested = isset($_POST['locale']) ? $_POST['locale'] : (isset($_GET['locale']) ? $_GET['locale'] : 0);
			$content_locale = $this->isMultiLang() ? $this->validLocaleId($requested) : $this->getDefaultLocaleId();
			
			if (isset($_POST['seo_update']))
			{
				$pjOptionModel = pjOptionModel::factory();
				$seo_keys = pjAppController::$translatable['pjOption'];
				$is_default = !$content_locale || (int) $content_locale === (int) $this->getDefaultLocaleId();
				
				// The default language is also kept in the options (the fallback); another
				// language only changes its own translation.
				if ($is_default)
				{
					foreach ($seo_keys as $key)
					{
						$pjOptionModel
							->reset()
							->where('foreign_id', $this->getForeignId())
							->where('`key`', $key)
							->limit(1)
							->modifyAll(array('value' => $_POST[$key]));
					}
				}
				if ($content_locale)
				{
					$values = array();
					foreach ($seo_keys as $key)
					{
						$values[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
					}
					pjMultiLangModel::factory()->updateMultiLang(array((int) $content_locale => $values), $this->getForeignId(), 'pjOption', 'data');
				}
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminOptions&action=pjActionSeo" . ($content_locale ? "&locale=" . (int) $content_locale : "") . "&err=AS01");
			}else{
				$arr = pjOptionModel::factory()
					->where('t1.foreign_id', $this->getForeignId())
					->orderBy('t1.order ASC')
					->findAll()
					->getData();
	
				$this->set('arr', $arr);
				
				// Values shown in the form: the chosen language's own text (another language
				// starts empty), or the saved options for the default language.
				$i18n = pjMultiLangModel::factory()->getMultiLang($this->getForeignId(), 'pjOption');
				$seo = array();
				$is_default = !$content_locale || (int) $content_locale === (int) $this->getDefaultLocaleId();
				foreach (pjAppController::$translatable['pjOption'] as $key)
				{
					if (isset($i18n[$content_locale][$key]))
					{
						$seo[$key] = $i18n[$content_locale][$key];
					} else {
						$seo[$key] = $is_default && isset($this->option_arr[$key]) ? $this->option_arr[$key] : '';
					}
				}
				$this->set('seo', $seo);
				$this->set('lp_arr', $this->getLocales());
				$this->set('content_locale', $content_locale);
				$this->set('translation_missing', !$is_default && !isset($i18n[$content_locale]));
	
				$this->appendJs('pjAdminOptions.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			if (isset($_POST['options_update']))
			{
				// When Google reCAPTCHA is selected, both keys are required.
				if (isset($_POST['value-enum-o_captcha_provider']))
				{
					$provider_parts = explode('::', $_POST['value-enum-o_captcha_provider']);
					$provider_val = isset($provider_parts[1]) ? $provider_parts[1] : '';
					if ($provider_val === 'recaptcha')
					{
						$site_key = isset($_POST['value-string-o_recaptcha_site_key']) ? trim($_POST['value-string-o_recaptcha_site_key']) : '';
						$secret_key = isset($_POST['value-string-o_recaptcha_secret_key']) ? trim($_POST['value-string-o_recaptcha_secret_key']) : '';
						if ($site_key === '' || $secret_key === '')
						{
							pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminOptions&action=" . @$_POST['next_action'] . "&err=AO10");
							return;
						}
					}
				}

				// Blog page URL (Install): store a clean absolute URL ('' = not set).
				if (isset($_POST['value-string-o_blog_page']))
				{
					$blogPage = trim($_POST['value-string-o_blog_page']);
					if ($blogPage !== '' && !preg_match('~^https?://~i', $blogPage))
					{
						$blogPage = 'https://' . ltrim($blogPage, '/');
					}
					$_POST['value-string-o_blog_page'] = pjUtil::cleanPageUrl($blogPage);
				}

				$OptionModel = new pjOptionModel();

				foreach ($_POST as $key => $value)
				{
					if (preg_match('/value-(string|text|int|float|enum|bool|color)-(.*)/', $key) === 1)
					{
						list(, $type, $k) = explode("-", $key);
						if (!empty($k))
						{
							$OptionModel
								->reset()
								->where('foreign_id', $this->getForeignId())
								->where('`key`', $k)
								->limit(1)
								->modifyAll(array('value' => $value));
						}
					}
				}
				
				if (isset($_POST['next_action']) && $_POST['next_action'] === 'pjActionEmailSettings')
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminOptions&action=pjActionEmailSettings&saved=1");
					return;
				}

				if (isset($_POST['next_action']) && $_POST['next_action'] === 'pjActionAiSettings')
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminOptions&action=pjActionAiSettings&saved=1");
					return;
				}

				$err = '';
				if (isset($_POST['next_action']))
				{
					switch ($_POST['next_action'])
					{
						case 'pjActionIndex':
							$err = 'AO01';
							break;
						case 'pjActionAddOns':
							$err = 'AO02';
							break;
						case 'pjActionProtection':
							$err = 'AO03';
							break;
						case 'pjActionInstall':
							$err = 'AO04';
							break;
					}
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminOptions&action=" . @$_POST['next_action'] . "&err=$err");
			}
		} else {
			$this->set('status', 2);
		}
	}

	/**
	 * AJAX: test an SMTP connection using the values currently entered
	 * in the Email Settings form (nothing is saved).
	 */
	public function pjActionAjaxSmtp()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Invalid request.'));
		}

		if (!(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'))
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 101, 'text' => 'Invalid request method.'));
		}

		if (!$this->isAdmin())
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Forbidden.'));
		}

		$host   = isset($_POST['smtp_host'])   ? trim($_POST['smtp_host'])   : '';
		$port   = isset($_POST['smtp_port'])   ? trim($_POST['smtp_port'])   : '';
		$user   = isset($_POST['smtp_user'])   ? trim($_POST['smtp_user'])   : '';
		$pass   = isset($_POST['smtp_pass'])   ? (string) $_POST['smtp_pass'] : '';
		$secure = isset($_POST['smtp_secure']) ? trim($_POST['smtp_secure']) : '';
		$auth   = isset($_POST['smtp_auth'])   ? trim($_POST['smtp_auth'])   : 'LOGIN';

		if (empty($host) || empty($port))
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 102, 'text' => __('emailMsgEnterHostPort', true)));
		}

		$message = '';
		$result  = false;

		try {
			$mail = new pjPHPMailer(true);
			$mail->isSMTP();
			$mail->Timeout = 10;
			$mail->Host = str_ireplace(array('ssl://', 'tls://'), array('', ''), $host);
			$mail->Port = (int) $port;
			if (in_array($secure, array('ssl', 'tls')))
			{
				$mail->SMTPSecure = $secure;
			}
			if (!empty($user))
			{
				$mail->SMTPAuth = true;
				$mail->AuthType = in_array($auth, array('CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2')) ? $auth : 'LOGIN';
				$mail->Username = $user;
				$mail->Password = $pass;
			}
			$result = $mail->smtpConnect();
			$mail->smtpClose();
		} catch (Exception $e) {
			$result  = false;
			$message = $e->getMessage();
		}

		if ($result)
		{
			pjAppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => __('emailMsgConnOk', true)));
		}

		pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 104, 'text' => __('emailMsgConnFail', true) . (!empty($message) ? ' (' . $message . ')' : '')));
	}

	/**
	 * AJAX: send a test email using the values currently entered
	 * in the Email Settings form (nothing is saved).
	 */
	public function pjActionAjaxSend()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Invalid request.'));
		}

		if (!(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'))
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 101, 'text' => 'Invalid request method.'));
		}

		if (!$this->isAdmin())
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Forbidden.'));
		}

		$email = isset($_POST['email']) ? trim($_POST['email']) : '';
		if (empty($email) || !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i', $email))
		{
			pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 102, 'text' => __('emailMsgEnterValidEmail', true)));
		}

		$method     = isset($_POST['send_email'])  ? trim($_POST['send_email'])  : 'mail';
		$host       = isset($_POST['smtp_host'])   ? trim($_POST['smtp_host'])   : '';
		$port       = isset($_POST['smtp_port'])   ? trim($_POST['smtp_port'])   : '';
		$user       = isset($_POST['smtp_user'])   ? trim($_POST['smtp_user'])   : '';
		$pass       = isset($_POST['smtp_pass'])   ? (string) $_POST['smtp_pass'] : '';
		$secure     = isset($_POST['smtp_secure']) ? trim($_POST['smtp_secure']) : '';
		$auth       = isset($_POST['smtp_auth'])   ? trim($_POST['smtp_auth'])   : 'LOGIN';
		$from_name  = isset($_POST['from_name'])   ? trim($_POST['from_name'])   : '';
		$from_email = isset($_POST['from_email']) && !empty($_POST['from_email'])
			? trim($_POST['from_email'])
			: $this->getFromEmail();

		$pjEmail = new pjEmail();
		if ($method === 'smtp')
		{
			$pjEmail
				->setTransport('smtp')
				->setSmtpHost($host)
				->setSmtpPort($port)
				->setSmtpSecure(in_array($secure, array('ssl', 'tls')) ? $secure : '')
				->setSmtpUser($user)
				->setSmtpPass($pass)
				->setSmtpAuthType(in_array($auth, array('CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2')) ? $auth : 'LOGIN')
				->setSender(!empty($user) ? $user : $from_email);
		}

		$subject = 'Test email from php Blog Script';
		$body    = 'Congratulations! This is a test message sent from the Email Settings page of your php Blog Script. '
				 . 'If you have received this email, your email settings are working correctly.';

		$pjEmail->setContentType('text/html');

		$ok = $pjEmail
			->setFrom($from_email, $from_name)
			->setTo($email)
			->setSubject($subject)
			->send($body);

		if ($ok)
		{
			pjAppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => __('emailMsgSentOk', true) . ' ' . $email . '. ' . __('emailMsgCheckInbox', true)));
		}

		$err = $pjEmail->getErrorMessage();
		pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 104, 'text' => __('emailMsgSendFail', true) . (!empty($err) ? ' (' . $err . ')' : '')));
	}
	
	public function pjActionInstall()
	{
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			$this->set('o_arr', $this->models['Option']
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.key ASC')
				->findAll()
				->getData()
			);
			
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('additional-methods.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdminOptions.js');
			$this->appendJs('pjAdminInstall.js');
			$this->appendCss('pjAdminInstall.css');
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionPreview()
	{
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionUpdateTheme()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			pjOptionModel::factory()
				->where('foreign_id', $this->getForeignId())
				->where('`key`', 'o_theme')
				->limit(1)
				->modifyAll(array('value' => 'theme1|theme2|theme3|theme4|theme5|theme6|theme7|theme8|theme9|theme10|theme11|theme12::theme' . $_GET['theme']));
	
		}
	}
}
?>