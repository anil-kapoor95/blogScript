<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdmin extends pjAppController
{
	public $defaultUser = 'admin_user';
	
	public $requireLogin = true;

	/**
	 * Unique URL slug for a post or category (the JS embed opens them by slug
	 * alone, e.g. ?post=<slug> / ?blog_category=<slug>). The title / name
	 * goes through friendlyURL(); if another record
	 * already uses it, the first free -2, -3, ... is appended.
	 *
	 * @param string $modelClass 'pjPostModel' or 'pjCategoryModel'
	 * @param string $text The title / name
	 * @param int $excludeId The record being edited (0 on create)
	 * @return string '' when the text has no usable characters
	 */
	protected function pjUniqueSlug($modelClass, $text, $excludeId = 0)
	{
		$base = $this->friendlyURL(stripslashes((string) $text));
		if ($base === '')
		{
			return '';
		}
		$slug = $base;
		for ($n = 2; $n < 1000; $n++)
		{
			$model = call_user_func(array($modelClass, 'factory'));
			$model->where('t1.url_slug', $slug);
			if ((int) $excludeId > 0)
			{
				$model->where('t1.id <> ' . (int) $excludeId);
			}
			if ((int) $model->findCount()->getData() === 0)
			{
				return $slug;
			}
			$slug = $base . '-' . $n;
		}
		return $base . '-' . uniqid();
	}

	/**
	 * Is the URL slug used by another post / category?
	 *
	 * @param string $modelClass 'pjPostModel' or 'pjCategoryModel'
	 * @param string $slug
	 * @param int $excludeId The record being edited (0 on create)
	 * @return bool
	 */
	protected function pjSlugTaken($modelClass, $slug, $excludeId = 0)
	{
		$model = call_user_func(array($modelClass, 'factory'));
		$model->where('t1.url_slug', $slug);
		if ((int) $excludeId > 0)
		{
			$model->where('t1.id <> ' . (int) $excludeId);
		}
		return (int) $model->findCount()->getData() > 0;
	}

	/**
	 * The URL slug to save for a post / category from the form. The admin
	 * fills the slug in from the title / name once and the user may change
	 * it. The posted slug goes through friendlyURL(); an empty slug is built
	 * from the title / name (unique, -2, -3, ...). A slug already used by
	 * another record is NOT saved: an existing record keeps its saved slug
	 * (a new one gets a unique slug from the title / name) and the caller
	 * shows the "slug already used" message on the same page.
	 *
	 * @param string $modelClass 'pjPostModel' or 'pjCategoryModel'
	 * @param int $id Record id (0 on create)
	 * @param string $posted The slug from the form
	 * @param string $text The title / name
	 * @return array array(slug to save, true when the posted slug was already used)
	 */
	protected function pjSlugFromForm($modelClass, $id, $posted, $text)
	{
		$id = (int) $id;
		$slug = $this->friendlyURL(stripslashes(trim((string) $posted)));
		$slug = function_exists('mb_substr') ? mb_substr($slug, 0, 200) : substr($slug, 0, 200);
		$slug = trim($slug, '-');
		if ($slug === '')
		{
			return array($this->pjUniqueSlug($modelClass, $text, $id), false);
		}
		if (!$this->pjSlugTaken($modelClass, $slug, $id))
		{
			return array($slug, false);
		}
		if ($id > 0)
		{
			$row = call_user_func(array($modelClass, 'factory'))->find($id)->getData();
			if (!empty($row) && trim((string) $row['url_slug']) !== '')
			{
				return array($row['url_slug'], true);
			}
		}
		return array($this->pjUniqueSlug($modelClass, $text, $id), true);
	}

	/**
	 * Inline title / name edit (list grid): the URL slug is not changed;
	 * a record that has no slug yet gets one from the new title / name.
	 *
	 * @return string|null The slug to save, or null to leave it as it is
	 */
	protected function pjSlugForRename($modelClass, $id, $text)
	{
		$row = call_user_func(array($modelClass, 'factory'))->find((int) $id)->getData();
		if (!empty($row) && trim((string) $row['url_slug']) !== '')
		{
			return null;
		}
		return $this->pjUniqueSlug($modelClass, $text, (int) $id);
	}

	/**
	 * jQuery validate "remote" rule of the admin "URL Slug" field: 'true' when
	 * ?url_slug= (normalised the same way it is saved) is free for record
	 * ?id=, else 'false'.
	 */
	protected function pjSlugResponse($modelClass)
	{
		$this->setAjax(true);
		if (!$this->isXHR() || !($this->isAdmin() || $this->isAuthor()))
		{
			exit;
		}
		$slug = trim($this->friendlyURL(stripslashes(isset($_GET['url_slug']) ? (string) $_GET['url_slug'] : '')), '-');
		$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
		echo ($slug === '' || !$this->pjSlugTaken($modelClass, $slug, $id)) ? 'true' : 'false';
		exit;
	}

	public function __construct($requireLogin=null)
	{
		$this->setLayout('pjActionAdmin');
		
		if (!is_null($requireLogin) && is_bool($requireLogin))
		{
			$this->requireLogin = $requireLogin;
		}
		
		if ($this->requireLogin)
		{
			if (!$this->isLoged() && !in_array(@$_GET['action'], array('pjActionLogin', 'pjActionForgot','pjActionExportFeed')))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin");
			}
		}
	}
	
	public function beforeRender()
	{
		
	}
		
	public function pjActionIndex()
	{
		$this->checkLogin();
		if ($this->isAdmin() || $this->isAuthor())
		{
			$pjPostModel = pjPostModel::factory();
			$pjCommentModel = pjCommentModel::factory();
			$pjUserModel = pjUserModel::factory();
			
			if($this->isAdmin())
			{
				$cnt_posts = $pjPostModel->findCount()->getData();
				$cnt_comments = $pjCommentModel->findCount()->getData();
				$cnt_waiting_comments = $pjCommentModel->reset()->where('t1.status', 'W')->findCount()->getData();
			}else{
				$cnt_posts = $pjPostModel->where('t1.author_id', $this->getUserId())->findCount()->getData();
				$cnt_comments = $pjCommentModel->where("(t1.post_id IN(SELECT (TP.id) FROM `".$pjPostModel->getTable()."` TP WHERE TP.author_id = ".$this->getUserId()."))")->findCount()->getData();
				$cnt_waiting_comments = $pjCommentModel->reset()->where("(t1.post_id IN(SELECT (TP.id) FROM `".$pjPostModel->getTable()."` TP WHERE TP.author_id = ".$this->getUserId()."))")->where('t1.status', 'W')->findCount()->getData();
			}
		
			$cnt_authors = $pjUserModel->findCount()->getData();
			
			if($this->isAdmin()){
				$post_arr = $pjPostModel->reset()->select('t1.*, t2.name')->limit(4)
									->join('pjUser', 't1.author_id = t2.id', 'left')
									->orderBy('t1.published_date DESC')
									->findAll()->getData();
									
				$comment_arr = $pjCommentModel->reset()->limit(5)
									->orderBy('t1.created DESC')
									->findAll()->getData();
			}else{
				$post_arr = $pjPostModel->reset()
									->select('t1.*, t2.name')
									->join('pjUser', 't1.author_id = t2.id', 'left')
									->where('author_id', $this->getUserId())
									->limit(3)
									->orderBy('t1.published_date DESC')
									->findAll()->getData();
									
				$comment_arr = $pjCommentModel->reset()->where("(t1.post_id IN(SELECT (TP.id) FROM `".$pjPostModel->getTable()."` TP WHERE TP.author_id = ".$this->getUserId()."))")
									->limit(5)
									->orderBy('t1.created DESC')
									->findAll()->getData();
			}
								
			$author_arr = $pjUserModel->reset()->limit(5)
								->orderBy('t1.last_login DESC')
								->findAll()->getData();
								
			$this->set('cnt_posts', $cnt_posts);
			$this->set('cnt_comments', $cnt_comments);
			$this->set('cnt_waiting_comments', $cnt_waiting_comments);
			$this->set('cnt_authors', $cnt_authors);

			$this->set('post_arr', $post_arr);
			$this->set('comment_arr', $comment_arr);
			$this->set('author_arr', $author_arr);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionForgot()
	{
		$this->setLayout('pjActionAdminLogin');
		
		if (isset($_POST['forgot_user']))
		{
			if (!isset($_POST['forgot_email']) || !pjValidation::pjActionNotEmpty($_POST['forgot_email']) || !pjValidation::pjActionEmail($_POST['forgot_email']))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=AA10");
			}
			$pjUserModel = pjUserModel::factory();
			$user = $pjUserModel
				->where('t1.email', $_POST['forgot_email'])
				->limit(1)
				->findAll()
				->getData();
				
			if (count($user) != 1)
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=AA10");
			} else {
				$user = $user[0];
				
				$Email = new pjEmail();
				$Email
					->setTo($user['email'])
					->setFrom($user['email'])
					->setSubject(__('emailForgotSubject', true));
				
				if ($this->option_arr['o_send_email'] == 'smtp')
				{
					$Email
						->setTransport('smtp')
						->setSmtpHost($this->option_arr['o_smtp_host'])
						->setSmtpPort($this->option_arr['o_smtp_port'])
						->setSmtpSecure((isset($this->option_arr['o_smtp_secure']) && in_array($this->option_arr['o_smtp_secure'], array('ssl', 'tls'))) ? $this->option_arr['o_smtp_secure'] : '')
						->setSmtpUser($this->option_arr['o_smtp_user'])
						->setSmtpPass($this->option_arr['o_smtp_pass'])
						->setSmtpAuthType((isset($this->option_arr['o_smtp_auth']) && in_array($this->option_arr['o_smtp_auth'], array('CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'))) ? $this->option_arr['o_smtp_auth'] : 'LOGIN')
					;
				}
				
				$body = str_replace(
					array('{Name}', '{Password}'),
					array($user['name'], $user['password']),
					__('emailForgotBody', true)
				);

				if ($Email->send($body))
				{
					$err = "AA11";
				} else {
					$err = "AA12";
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=$err");
			}
		} else {
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdmin.js');
		}
	}
	
	public function pjActionMessages()
	{
		$this->setAjax(true);
		header("Content-Type: text/javascript; charset=utf-8");
	}
	
	public function pjActionLogin()
	{
		$this->setLayout('pjActionAdminLogin');
		
		if (isset($_POST['login_user']))
		{
			if (!isset($_POST['login_email']) || !isset($_POST['login_password']) ||
				!pjValidation::pjActionNotEmpty($_POST['login_email']) ||
				!pjValidation::pjActionNotEmpty($_POST['login_password']) ||
				!pjValidation::pjActionEmail($_POST['login_email']))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=4");
			}
			$pjUserModel = pjUserModel::factory();

			$user = $pjUserModel
				->where('t1.email', $_POST['login_email'])
				->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($_POST['login_password']), PJ_SALT))
				->limit(1)
				->findAll()
				->getData();

			if (count($user) != 1)
			{
				# Login failed
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=1");
			} else {
				$user = $user[0];
				unset($user['password']);
															
				if (!in_array($user['role_id'], array(1,2,3)))
				{
					# Login denied
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=2");
				}
				
				if ($user['role_id'] == 3 && $user['is_active'] == 'F')
				{
					# Login denied
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=2");
				}
				
				if ($user['status'] != 'T')
				{
					# Login forbidden
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=3");
				}
				
				# Login succeed
				$last_login = date("Y-m-d H:i:s");
				if($user['last_login'] == $user['created'])
				{
					$user['last_login'] = date("Y-m-d H:i:s");
				}
    			$_SESSION[$this->defaultUser] = $user;
    			
    			# Update
    			$data = array();
    			$data['last_login'] = $last_login;
    			$pjUserModel->reset()->setAttributes(array('id' => $user['id']))->modify($data);

    			if ($this->isAdmin() || $this->isAuthor())
    			{
	    			pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionIndex");
    			}
			}
		} else {
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdmin.js');
		}
	}
	
	public function pjActionLogout()
	{
		if ($this->isLoged())
        {
        	unset($_SESSION[$this->defaultUser]);
        }
       	pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin");
	}
	
	public function pjActionProfile()
	{
		$this->checkLogin();
		
		if (!$this->isAdmin())
		{
			if (isset($_POST['profile_update']))
			{
				$pjUserModel = pjUserModel::factory();
				$arr = $pjUserModel->find($this->getUserId())->getData();
				$data = array();
				$data['role_id'] = $arr['role_id'];
				$data['status'] = $arr['status'];
				$post = array_merge($_POST, $data);
				if (!$pjUserModel->validates($post))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionProfile&err=AA14");
				}
				$pjUserModel->set('id', $this->getUserId())->modify($post);
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionProfile&err=AA13");
			} else {
				$this->set('arr', pjUserModel::factory()->find($this->getUserId())->getData());
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdmin.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
}
?>