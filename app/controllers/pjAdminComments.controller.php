<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminComments extends pjAdmin
{
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminComments.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionGetComment()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjCommentModel = pjCommentModel::factory();
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjCommentModel->where('t1.name LIKE', "%$q%");
				$pjCommentModel->orWhere('t1.email LIKE', "%$q%");
				$pjCommentModel->orWhere('t1.comment LIKE', "%$q%");
			}

			if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F', 'W')))
			{
				$pjCommentModel->where('t1.status', $_GET['status']);
			}
			if (isset($_GET['post_id']) && !empty($_GET['post_id']))
			{
				$pjCommentModel->where('t1.post_id', $_GET['post_id']);
			}
			if ($this->isAuthor()&& $this->isAllComments($this->getUserId()) == false){
				$pjCommentModel->where("(t1.post_id IN(SELECT (TP.id) FROM `".pjPostModel::factory()->getTable()."` TP WHERE TP.author_id = ".$this->getUserId()."))");
			}
				
			$column = 'created';
			$direction = 'DESC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjCommentModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = array();
			
			$arr = $pjCommentModel->select('t1.*, t2.title')
				->join('pjPost', "t1.post_id=t2.id", 'left')
				->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();

			foreach($arr as $k => $v){
				$v['created'] = pjUtil::formatDate(date('Y-m-d', strtotime($v['created'])), 'Y-m-d', $this->option_arr['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($v['created'])), 'H:i:s', $this->option_arr['o_time_format']);
				$data[$k] = $v;
			}	
				
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{				
			if (isset($_POST['comment_update']))
			{
				// A comment made blank (or just whitespace) in this form
				// isn't a real edit - reject it instead of silently saving
				// an empty comment. name/email already carry a "required"
				// class the client-side validator picks up, but that check
				// alone doesn't stop whitespace-only input, and neither
				// field had a server-side check at all before this.
				$name = isset($_POST['name']) ? trim($_POST['name']) : '';
				$email = isset($_POST['email']) ? trim($_POST['email']) : '';
				$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
				if ($name === '' || $email === '' || $comment === '')
				{
					pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminComments&action=pjActionIndex&err=AC11");
				} else {
					$_POST['name'] = $name;
					$_POST['email'] = $email;
					$_POST['comment'] = $comment;
					$data = array();
					$data['modified'] = date('Y-m-d H:i:s');
					pjCommentModel::factory()->where('id', $_POST['id'])->limit(1)->modifyAll(array_merge($_POST, $data));
					
					pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminComments&action=pjActionIndex&err=AC01");
				}
			} else {
				$arr = pjCommentModel::factory()
					->select('t1.*, t2.title')
					->join('pjPost', "t1.post_id=t2.id", 'left')
					->find($_GET['id'])
					->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminComments&action=pjActionIndex&err=AC08");
				}
				$this->set('arr', $arr);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminComments.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionDelete()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{				
			if (isset($_GET['id']))
			{
				$arr = pjCommentModel::factory()->find($_GET['id'])->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminComments&action=pjActionIndex&err=AC08");
				}
				
				if (pjCommentModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminComments&action=pjActionIndex&err=AC09");
				} else {
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminComments&action=pjActionIndex&err=AC10");
				}
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminComments.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionStatusComment()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0 && in_array($_GET['status'], array('T', 'F', 'W')))
			{
				pjCommentModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
					'status' => $_GET['status']
				));
			}
		}
		exit;
	}
	
	public function pjActionSaveComment()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if($_POST['value'] != '')
			{
				pjCommentModel::factory()->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $_POST['value']));
			}
		}
		exit;
	}
	
	public function pjActionExportComment()
	{
		$this->checkLogin();
		
		if (isset($_POST['record']) && is_array($_POST['record']))
		{
			$arr = pjCommentModel::factory()->whereIn('id', $_POST['record'])->findAll()->getData();
			$csv = new pjCSV();
			$csv
				->setHeader(true)
				->setName("Comments-".time().".csv")
				->process($arr)
				->download();
		}
		exit;
	}
	
	public function pjActionDeleteComment()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$response = array();
			if (pjCommentModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
			{
				$response['code'] = 200;
			} else {
				$response['code'] = 100;
			}
			pjAppController::jsonResponse($response);
		}
		exit;
	}
	
	public function pjActionDeleteCommentBulk()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0)
			{
				pjCommentModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
			}
		}
		exit;
	}
}
?>