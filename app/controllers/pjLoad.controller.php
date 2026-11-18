<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjLoad extends pjFront
{
	private $isoDatePattern = '/\d{4}-\d{2}-\d{2}/';

	/**
	 * Set by the JS embed (pjEmbed::pjActionComment): answer a comment
	 * submit with JSON {code} instead of redirecting to the post page.
	 */
	protected $pjJsonResult = false;

	/**
	 * pjValidation::pjActionNotEmpty() only recognises plain ASCII
	 * whitespace, so a comment/name made up of e.g. non-breaking spaces
	 * (U+00A0) or a zero-width space passes it while still looking blank.
	 * This normalises those invisible characters to a regular space first,
	 * then trims, so a "blank-looking" submission is treated as blank.
	 */
	private function pjIsBlank($str)
	{
		$normalized = str_replace(array("\xC2\xA0", "\xE2\x80\x8B", "\xEF\xBB\xBF"), ' ', (string) $str);
		return trim($normalized) === '';
	}
		
	public function pjActionIndex()
	{
		// SEO meta for the listing / category page is written by the JS embed
		// (pjEmbed), not here.
		$canonical_category = null;

		$pjCategoryModel = pjCategoryModel::factory();
		$pjPostModel = pjPostModel::factory();


		$pjPostModel = pjPostModel::factory();
		if(isset($_GET['dosearch'])){
			if (isset($_GET['keyword']) && !empty($_GET['keyword'])){
				$q = pjObject::escapeString($_GET['keyword']);
				$pjPostModel->where("(t1.title LIKE '%$q%' OR t1.body LIKE '%$q%')");
			}
		}
		if(isset($_GET['category_id']) && (int) $_GET['category_id'] > 0){
			$category_id = pjObject::escapeString($_GET['category_id']);
			$_arr = $pjCategoryModel->reset()->where('status', 'T')->find($category_id)->getData();
			if(!empty($_arr))
			{
				$pjPostModel->where('t1.category_id', $category_id);
				$canonical_category = $_arr;
			}
		}
		if(isset($_GET['archive']) && !empty($_GET['archive'])){
			$pair = explode("-", $_GET['archive']);
			if(count($pair) == 1)
			{
				if((int) $pair[0] > 0)
				{
					$year = (int) $pair[0];
					$pjPostModel->where("(YEAR(t1.published_date) = $year)");
				}
			}else{
				if((int) $pair[0] > 0 && in_array($pair[1], array('01','02','03','04','05','06','07','08','09','10','11','12')))
				{
					$pjPostModel->where("(DATE_FORMAT(published_date, '%Y-%m') = '".$_GET['archive']."')");
				}
			}
		}
		$pjPostModel->where('t1.status', 'T');
		$pjPostModel->where("((t1.category_id = 0) OR (t1.category_id > 0 AND t1.category_id IN(SELECT TC.id FROM `".$pjCategoryModel->getTable()."` AS TC WHERE TC.status='T')))");
		
		$column = 'published_date';
		$direction = 'DESC';
		if($this->option_arr['o_post_order'] == 'bottom'){
			$direction = 'ASC';
		}

		$total = $pjPostModel->findCount()->getData();
		$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : $this->option_arr['o_posts_per_page'];
		$pages = ceil($total / $rowCount);
		$page = isset($_GET['pjPage']) && (int) $_GET['pjPage'] > 0 ? intval($_GET['pjPage']) : 1;
		$offset = ((int) $page - 1) * $rowCount;
		if ($page > $pages)
		{
			$page = $pages;
		}
		$vote_table = pjVoteModel::factory()->getTable();
		$arr = $pjPostModel->select("t1.*, t2.name, t3.category, (SELECT COUNT(TC.post_id) FROM `".pjCommentModel::factory()->getTable()."` AS TC WHERE t1.id = TC.post_id AND TC.status='T') AS `cnt_comments`,
													(SELECT COUNT(TV1.id) FROM `$vote_table` AS TV1 WHERE `TV1`.`post_id` = `t1`.`id` LIMIT 1) AS `cnt`,
													(SELECT SUM(TV2.vote_rate)/COUNT(TV2.id) FROM `$vote_table` AS TV2 WHERE `TV2`.`post_id` = `t1`.`id` LIMIT 1) AS `avg_rate`")
				->join('pjUser', 't1.author_id=t2.id', 'left')
				->join('pjCategory', 't1.category_id=t3.id', 'left')
				->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();

		// Real, single H1 for the listing page: the category name when
		// filtering by category, otherwise the blog's own name (Site
		// Settings -> Meta Title). Individual post titles in the list are
		// H2s (see pjActionIndex.php) - the SEO review flagged the article
		// page for having no H1 at all, and the listing page had the same
		// gap. No placeholder text - if neither value is set, no heading
		// renders (pjActionIndex.php only prints it when non-empty).
		$page_heading = !empty($canonical_category) ? stripslashes($canonical_category['category']) : $this->option_arr['o_meta_title'];
		$this->set('page_heading', $page_heading);

		$this->set('arr', $arr);
		$this->set('paginator', compact('total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		
	}
	
	public function pjActionView()
	{
		if(isset($_POST['post_comment'])){
			set_time_limit(0);

			$useRecaptcha = isset($this->option_arr['o_captcha_provider']) && $this->option_arr['o_captcha_provider'] == 'recaptcha';

			if (!isset($_POST['name'])){
				$err = 9901;
			}
			if (!isset($_POST['email'])){
				$err = 9902;
			}
			if (!isset($_POST['comment'])){
				$err = 9903;
			}
			if (!$useRecaptcha && !isset($_POST['captcha'])){
				$err = 9904;
			}
			if (isset($_POST['name']) && (!pjValidation::pjActionNotEmpty($_POST['name']) || $this->pjIsBlank($_POST['name'])))
			{
				$err = 9905;
			}
			if (isset($_POST['email']) && (!pjValidation::pjActionNotEmpty($_POST['email']) || $this->pjIsBlank($_POST['email'])))
			{
				$err = 9906;
			}
			if (isset($_POST['comment']) && (!pjValidation::pjActionNotEmpty($_POST['comment']) || $this->pjIsBlank($_POST['comment'])))
			{
				$err = 9907;
			}
			if (!$useRecaptcha && isset($_POST['captcha']) && !pjValidation::pjActionNotEmpty($_POST['captcha']))
			{
				$err = 9908;
			}
			if (isset($_POST['email']) && !pjValidation::pjActionEmail($_POST['email']))
			{
				$err = 9909;
			}
			if (!$useRecaptcha && isset($_POST['captcha']) && isset($_SESSION[$this->defaultCaptcha]) && strtoupper($_POST['captcha']) != $_SESSION[$this->defaultCaptcha])
			{
				$err = 9910;
			}
			if ($useRecaptcha)
			{
				$recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
				if (!$this->pjVerifyRecaptcha($recaptcha_response))
				{
					$err = 9910;
				}
			}
			if (isset($_POST['comment']) && pjValidation::pjActionNotEmpty($_POST['comment']) && $this->pjCheckBannedWords($_POST['comment']) == false)
			{
				$err = 9911;
			}
			if (isset($_POST['name']) && pjValidation::pjActionNotEmpty($_POST['name']) && $this->pjCheckUrls($_POST['name']) == false)
			{
				$err = 9912;
			}
			if (isset($_POST['comment']) && pjValidation::pjActionNotEmpty($_POST['comment']) && $this->pjCheckUrls($_POST['comment']) == false)
			{
				$err = 9913;
			}
			
			$post = pjPostModel::factory()->find($_POST['post_id'])->getData(); 
			
			if (isset($err))
			{
				if (isset($_SESSION[$this->defaultCaptcha]))
				{
					$_SESSION[$this->defaultCaptcha] = NULL;
					unset($_SESSION[$this->defaultCaptcha]);
				}
				if ($this->pjJsonResult)
				{
					pjAppController::jsonResponse(array('code' => (int) $err));
				}
				$detail_url = $this->postDetailUrl($post);
				$_SESSION[$this->defaultStatus] = $err;
				pjUtil::redirect($detail_url);
			}
			if (isset($_SESSION[$this->defaultCaptcha]))
			{
				$_SESSION[$this->defaultCaptcha] = NULL;
				unset($_SESSION[$this->defaultCaptcha]);
			}
			// Trim now that the blank checks above have passed, so a
			// submission like "  Nice post!  " is stored as "Nice post!"
			// rather than keeping the incidental leading/trailing padding.
			if (isset($_POST['name'])) { $_POST['name'] = trim($_POST['name']); }
			if (isset($_POST['email'])) { $_POST['email'] = trim($_POST['email']); }
			if (isset($_POST['comment'])) { $_POST['comment'] = trim($_POST['comment']); }

			$data = array();
			$data['status'] = 'T';
			if($this->option_arr['o_comment_approval'] == 'Yes'){
				$data['status'] = 'W';
			}
			$id = pjCommentModel::factory()->setAttributes(array_merge($_POST, $data))->insert()->getInsertId();
			if ($id !== false && (int) $id > 0)
			{
				$arr = pjPostModel::factory()->find($_POST['post_id'])->getData();
				
				$pjEmail = new pjEmail();
				if ($this->option_arr['o_send_email'] == 'smtp')
				{
					$pjEmail
						->setTransport('smtp')
						->setSmtpHost($this->option_arr['o_smtp_host'])
						->setSmtpPort($this->option_arr['o_smtp_port'])
						->setSmtpSecure((isset($this->option_arr['o_smtp_secure']) && in_array($this->option_arr['o_smtp_secure'], array('ssl', 'tls'))) ? $this->option_arr['o_smtp_secure'] : '')
						->setSmtpUser($this->option_arr['o_smtp_user'])
						->setSmtpPass($this->option_arr['o_smtp_pass'])
						->setSmtpAuthType((isset($this->option_arr['o_smtp_auth']) && in_array($this->option_arr['o_smtp_auth'], array('CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'))) ? $this->option_arr['o_smtp_auth'] : 'LOGIN');
				}
				$pjEmail->setContentType('text/html');
				$from = $this->getFromEmail();

				$subject = $this->option_arr['o_notify_subject'];
				$message = $this->option_arr['o_notify_message'];
				
				$edit_url = PJ_INSTALL_URL . "index.php?controller=pjAdminComments&action=pjActionUpdate&id=".$id;
				$edit_url = '<a href="'.$edit_url.'">'.$edit_url.'</a>';
				$delete_url = PJ_INSTALL_URL . "index.php?controller=pjAdminComments&action=pjActionDelete&id=".$id;
				$delete_url = '<a href="'.$delete_url.'">'.$delete_url.'</a>';
				
				$search = array('{PostTitle}', '{Name}', '{Email}', '{Comment}', '{EditComment}', '{DeleteComment}');
				$replace = array($arr['title'], $_POST['name'], $_POST['email'], $_POST['comment'], $edit_url, $delete_url);
					
				$message = str_replace($search, $replace, $message);
				
				$pjUserModel = pjUserModel::factory();
				if(!empty($arr['author_id']) && (int) $arr['author_id'] > 0)
				{
					$pjUserModel->where("(t1.id = '".$arr['author_id']."' AND t1.receive_notify <> 'D') OR t1.receive_notify = 'A'");
				}else{
					$pjUserModel->where("t1.receive_notify", 'A');
				}
				$email_arr = $pjUserModel->findAll()->getDataPair('id', 'email');
				
				foreach($email_arr as $email)
				{
					$pjEmail
						->setFrom($from)
						->setTo($email)
						->setSubject($subject)
						->send(pjUtil::textToHtml($message));
				}
				
				if($this->option_arr['o_comment_approval'] == 'No'){
					$err = 9999;
				}else{
					$err = 9998;
				}
				if ($this->pjJsonResult)
				{
					pjAppController::jsonResponse(array('code' => (int) $err));
				}

				$detail_url = $this->postDetailUrl($post);
				$_SESSION[$this->defaultStatus] = $err;
				pjUtil::redirect($detail_url);
				exit;
			}
		}else{
			
			$id = pjObject::escapeString($_GET['id']);
			if(is_numeric($id))
			{
				$pjPostModel = pjPostModel::factory();
				$vote_table = pjVoteModel::factory()->getTable();
				$arr = $pjPostModel
					->select("t1.*, t2.name, t3.category, (SELECT COUNT(TC.post_id) FROM `".pjCommentModel::factory()->getTable()."` AS TC WHERE t1.id = TC.post_id AND TC.status='T') AS `cnt_comments`,
														  (SELECT COUNT(TV1.id) FROM `$vote_table` AS TV1 WHERE `TV1`.`post_id` = `t1`.`id` LIMIT 1) AS `cnt`,
														  (SELECT SUM(TV2.vote_rate)/COUNT(TV2.id) FROM `$vote_table` AS TV2 WHERE `TV2`.`post_id` = `t1`.`id` LIMIT 1) AS `avg_rate`")
					->join('pjUser', 't1.author_id=t2.id', 'left')
					->join('pjCategory', 't1.category_id=t3.id', 'left')
					->find($_GET['id'])->getData();
					
				$category = pjCategoryModel::factory()->find($arr['category_id'])->getData();
					
				if($arr['status'] == 'T' && ( ($arr['category_id'] > 0 && $category['status'] == 'T') || $arr['category_id'] == 0 ))
				{
					$column = 'created';
					$direction = 'DESC';
					if($this->option_arr['o_comment_order'] == 'bottom'){
						$direction = 'ASC';
					}
					$comment_arr = pjCommentModel::factory()
						->where('t1.post_id', $_GET['id'])
						->where('t1.status', 'T')
						->orderBy("$column $direction")
						->findAll()
						->getData();
					
					if(!empty($arr['category_id']))
					{
						$number_of_related_posts = isset($this->option_arr['o_related_posts_shown']) ? (!empty($this->option_arr['o_related_posts_shown']) ? $this->option_arr['o_related_posts_shown'] : 0) : 0 ;
						if($number_of_related_posts > 0)
						{
							$related_arr = $pjPostModel
								->reset()
								->join('pjUser', 't1.author_id=t2.id', 'left')
								->select("t1.*, t2.name,(SELECT COUNT(TC.post_id) FROM `".pjCommentModel::factory()->getTable()."` AS TC WHERE t1.id = TC.post_id AND TC.status='T') AS `cnt_comments`,
														(SELECT COUNT(TV1.id) FROM `$vote_table` AS TV1 WHERE `TV1`.`post_id` = `t1`.`id` LIMIT 1) AS `cnt`,
													 	(SELECT SUM(TV2.vote_rate)/COUNT(TV2.id) FROM `$vote_table` AS TV2 WHERE `TV2`.`post_id` = `t1`.`id` LIMIT 1) AS `avg_rate`")
								->where("t1.id <> " . $_GET['id'])
								->where('t1.category_id', $arr['category_id'])
								->orderBy("RAND()")
								->limit($number_of_related_posts)
								->findAll()
								->getData();
						}else{
							$related_arr = array();
						}
					}else{
						$related_arr = array();
					}
					
					// The post's SEO meta is written by the JS embed (pjEmbed).
					$this->set('arr', $arr);
					$this->set('comment_arr', $comment_arr);
					$this->set('related_arr', $related_arr);
				}
			}
			
			if(isset($_SESSION[$this->defaultStatus]))
			{
				$_GET['err'] = $_SESSION[$this->defaultStatus];
				unset($_SESSION[$this->defaultStatus]);
			}
		}
	}
	
	private function pjCheckBannedWords($string)
	{
		if($this->option_arr['o_banned_words'] == ''){
			return true;
		}else{
			$banned_words = trim($this->option_arr['o_banned_words']);
			$banned_arr = explode(",", $banned_words);
			foreach($banned_arr as $k => $v){
				$banned_arr[$k] = trim($v);
			}
			$matches = array();
			$matchFound = preg_match_all("/\b(" . implode($banned_arr,"|") . ")\b/i", $string, $matches);
			if ($matchFound) {
				return false;
			}else{
				return true;
			}
		}
	}
	
	private function pjCheckUrls($string)
	{
		$result = true;
		if($this->option_arr['o_reject_links'] == 'Yes'){
			$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			if(preg_match($reg_exUrl, $string, $url)) {
				$result = false;
			}
		}
		return $result;
	}
	
	public function pjActionSend(){
		$this->setAjax(true);

		$json_arr = array();

		// Verify the anti-spam challenge before sending anything.
		$useRecaptcha = isset($this->option_arr['o_captcha_provider']) && $this->option_arr['o_captcha_provider'] == 'recaptcha';
		if ($useRecaptcha)
		{
			$recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
			if (!$this->pjVerifyRecaptcha($recaptcha_response))
			{
				$json_arr['code'] = '201';
				pjAppController::jsonResponse($json_arr);
				exit;
			}
		}
		else
		{
			if (!isset($_POST['captcha']) || !isset($_SESSION[$this->defaultSendCaptcha]) || strtoupper($_POST['captcha']) != $_SESSION[$this->defaultSendCaptcha])
			{
				$json_arr['code'] = '201';
				pjAppController::jsonResponse($json_arr);
				exit;
			}
		}

		$pjEmail = new pjEmail();
		if ($this->option_arr['o_send_email'] == 'smtp')
		{
			$pjEmail
				->setTransport('smtp')
				->setSmtpHost($this->option_arr['o_smtp_host'])
				->setSmtpPort($this->option_arr['o_smtp_port'])
				->setSmtpSecure((isset($this->option_arr['o_smtp_secure']) && in_array($this->option_arr['o_smtp_secure'], array('ssl', 'tls'))) ? $this->option_arr['o_smtp_secure'] : '')
				->setSmtpUser($this->option_arr['o_smtp_user'])
				->setSmtpPass($this->option_arr['o_smtp_pass'])
				->setSmtpAuthType((isset($this->option_arr['o_smtp_auth']) && in_array($this->option_arr['o_smtp_auth'], array('CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'))) ? $this->option_arr['o_smtp_auth'] : 'LOGIN');
		}
		$pjEmail->setContentType('text/html');
		$from = $this->getFromEmail();
		if(isset($this->option_arr['o_email_address']) && $this->option_arr['o_email_address'] != '' ){
			$from = $this->option_arr['o_email_address'];
		}
		$to = $_POST['send_to'];
		$subject = stripslashes($_POST['subject']);
		$message = nl2br(stripslashes($_POST['message']));
		$pjEmail->setFrom($from)
					->setTo($to)
					->setSubject($subject)
					->send($message);
		$json_arr['code'] = '102';
		pjAppController::jsonResponse($json_arr);
		exit;
	}
	
	public function pjActionRating()
	{
		$this->setAjax(true);
		
		$pjPostModel = pjPostModel::factory();
		$pjVoteModel = pjVoteModel::factory();
		
		$access_ip = $_SERVER["REMOTE_ADDR"];
		$is_voted = array();
		$json_arr = array();
		
		$post_id = intval($_POST['id']);
		$rate = intval($_POST['rate']);
		$is_voted = false;
		$limit_vote = $this->option_arr['o_limit_vote'];
		
		if($limit_vote == 'IP'){
			$pjVoteModel->where("t1.post_id = $post_id AND t1.ip = '$access_ip' AND (t1.vote_date + INTERVAL 1 DAY) > NOW()");
			if($pjVoteModel->findCount()->getData() > 0){
				$is_voted = true;
			}
		}else if($limit_vote == 'Cookie'){
			if(isset($_COOKIE["SBlog_Vote_" + $post_id])){
				$is_voted = true;
			}
		}
		if ($is_voted) {
			$json_arr['code'] = 101;
		}else{
			$arr = $pjPostModel->find($post_id)->getData();
			$data = array();
			$data['ip'] = $access_ip;
			$data['post_id'] = $post_id;
			$data['category_id'] = $arr['category_id'];
			$data['vote_rate'] = $rate;
			$pjVoteModel->reset()->setAttributes($data)->insert();
			
			if($limit_vote == 'Cookie'){
				$expire=time() + 60*60*24;
				setcookie("SBlog_Vote_" + $post_id, 1, $expire);
			}
			$json_arr['code'] = 102;
		}
		pjAppController::jsonResponse($json_arr);
	}
}
?>