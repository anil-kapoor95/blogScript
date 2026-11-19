<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminPosts extends pjAdmin
{
	/**
	 * jQuery validate "remote" rule of the "URL Slug" field: is ?url_slug=
	 * free (?id= = the post being edited)? See pjAdmin::pjSlugResponse().
	 */
	public function pjActionCheckSlug()
	{
		$this->checkLogin();
		$this->pjSlugResponse('pjPostModel');
	}

	/**
	 * Language the add/edit form works in: ?locale=ID / the hidden "locale" field, else the default language.
	 */
	private function getContentLocale()
	{
		$requested = isset($_POST['locale']) ? $_POST['locale'] : (isset($_GET['locale']) ? $_GET['locale'] : 0);
		return $this->isMultiLang() ? $this->validLocaleId($requested) : $this->getDefaultLocaleId();
	}
	
	/**
	 * Languages for the add/edit form and its language bar.
	 */
	private function setLocaleForm($content_locale)
	{
		$this->set('lp_arr', $this->getLocales());
		$this->set('content_locale', $content_locale);
		$this->set('default_locale_id', $this->getDefaultLocaleId());
	}
	
	/**
	 * Values of the translatable fields of the submitted form.
	 */
	private function getSubmittedTranslation()
	{
		$values = array();
		foreach (pjAppController::$translatable['pjPost'] as $field)
		{
			if (isset($_POST[$field]))
			{
				$values[$field] = $_POST[$field];
			}
		}
		return $values;
	}
	
	/**
	 * Add Post: text the author wrote in the other languages of the form before saving (the form
	 * keeps one language per page, so the other languages come in as "tr[<language ID>][<field>]").
	 * Each is saved as that language's own translation; the default language also goes to the
	 * original columns. Nothing typed in a language is dropped, and nothing is copied across languages.
	 */
	private function saveOtherLanguageDrafts($id, $content_locale)
	{
		if ($id <= 0 || !$this->isMultiLang() || empty($_POST['tr']) || !is_array($_POST['tr']))
		{
			return;
		}
		$default_locale = (int) $this->getDefaultLocaleId();
		foreach ($_POST['tr'] as $locale => $vals)
		{
			$locale = (int) $locale;
			if ($locale <= 0 || $locale === (int) $content_locale || $this->validLocaleId($locale) !== $locale || !is_array($vals))
			{
				continue;
			}
			$values = array();
			foreach (pjAppController::$translatable['pjPost'] as $field)
			{
				if (isset($vals[$field]) && is_string($vals[$field]))
				{
					$values[$field] = $vals[$field];
				}
			}
			if (empty($values))
			{
				continue;
			}
			if ($locale === $default_locale)
			{
				$columns = $values;
				if (array_key_exists('url_slug', $columns))
				{
					list($columns['url_slug']) = $this->pjSlugFromForm('pjPostModel', $id, $columns['url_slug'], isset($columns['title']) ? $columns['title'] : '');
				}
				pjPostModel::factory()->reset()->where('id', $id)->limit(1)->modifyAll($columns);
			} elseif (array_key_exists('url_slug', $values) || !empty($values['title']))
			{
				// a language written without a slug gets one from its own title
				$values['url_slug'] = $this->translatedSlug('pjPost', $id, $locale, isset($values['url_slug']) ? $values['url_slug'] : '', isset($values['title']) ? $values['title'] : '');
			}
			$this->saveTranslation('pjPost', $id, $locale, $values);
		}
	}

	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			$this->set('author_arr', pjUserModel::factory()->orderBy('t1.name ASC')->findAll()->getData());
			
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminPosts.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionGetPost()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjPostModel = pjPostModel::factory();
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjPostModel->where('t1.title LIKE', "%$q%");
				$pjPostModel->orWhere('t1.body LIKE', "%$q%");
				if ($this->isMultiLang() && $this->getLocaleId())
				{
					$pjPostModel->orWhere("t1.id IN (SELECT TL.foreign_id FROM `".pjMultiLangModel::factory()->getTable()."` AS TL WHERE TL.model='pjPost' AND TL.locale='".(int) $this->getLocaleId()."' AND TL.field IN ('title','body') AND TL.content LIKE '%$q%')");
				}
			}
			if (isset($_GET['title']) && !empty($_GET['title']))
			{
				$title = pjObject::escapeString($_GET['title']);
				$pjPostModel->where('t1.title LIKE', "%$title%");
			}
			if (isset($_GET['blog_text']) && !empty($_GET['blog_text']))
			{
				$blog_text = pjObject::escapeString($_GET['blog_text']);
				$pjPostModel->where('t1.body LIKE', "%$blog_text%");
			}
			if ($this->isAuthor()){
				$pjPostModel->where('t1.author_id', $this->getUserId());
			}
			if (isset($_GET['author_id']) && (int) $_GET['author_id'] > 0)
			{
				$pjPostModel->where('t1.author_id', $_GET['author_id']);
			}
			if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
			{
				$pjPostModel->where('t1.status', $_GET['status']);
			}
			if (isset($_GET['date_from']) && !empty($_GET['date_from']) && isset($_GET['date_to']) && !empty($_GET['date_to']))
			{
				$df = pjUtil::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
				$dt = pjUtil::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
				$pjPostModel->where("(t1.published_date BETWEEN '$df' AND '$dt')");
			
			} else {
				if (isset($_GET['date_from']) && !empty($_GET['date_from']))
				{
					$df = pjUtil::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
					$pjPostModel->where("(t1.published_date >= '$df')");
				} elseif (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
					$dt = pjUtil::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
					$pjPostModel->where("(t1.published_date <= '$dt')");
				}
			}
				
			$column = 'published_date';
			$direction = 'DESC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjPostModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = array();
			$arr = $pjPostModel->select('t1.*, (SELECT COUNT(TC.post_id) FROM `'.pjCommentModel::factory()->getTable().'` AS TC WHERE t1.id = TC.post_id) AS `cnt_comments`')
				->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
			$arr = $this->translateRows($arr, 'pjPost');

			foreach($arr as $k => $v){
				$v['published_date'] = pjUtil::formatDate(date('Y-m-d', strtotime($v['published_date'])), 'Y-m-d', $this->option_arr['o_date_format']);
				$data[$k] = $v;
			}	
				
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			$post_max_size = pjUtil::getPostMaxSize();
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size)
			{
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionIndex&err=AP05");
			}
			if (isset($_POST['post_create']))
			{
				$pjPostModel = pjPostModel::factory();
				$data = array();
				if($this->isAuthor()){
					$data['author_id'] = $this->getUserId();
				}
				// URL slug from the form (filled in from the title, editable); a
				// slug already used by another post is not saved (AP12).
				list($_POST['url_slug'], $slugTaken) = $this->pjSlugFromForm('pjPostModel', 0, isset($_POST['url_slug']) ? $_POST['url_slug'] : '', isset($_POST['title']) ? $_POST['title'] : '');
				// The text is also kept in the original columns (the fallback shown when a
				// language has no translation); the chosen language gets its own copy.
				$content_locale = $this->getContentLocale();
				$id = $pjPostModel->setAttributes(array_merge($data, $_POST))->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
					if ($content_locale)
					{
						$this->saveTranslation('pjPost', $id, $content_locale, $this->getSubmittedTranslation());
					}
					$this->saveOtherLanguageDrafts((int) $id, (int) $content_locale);
					$img_err = '';
					if (isset($_FILES['image']))
					{
						if($_FILES['image']['error'] == 0)
						{
							$data = array();

							$Image = new pjImage();
							if ($Image->getErrorCode() !== 200)
							{
								$Image->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
								if ($Image->load($_FILES['image']))
								{
									$resp = $Image->isConvertPossible();
									if ($resp['status'] === true)
									{
										$hash = md5(uniqid(rand(), true));
										$source_path = PJ_UPLOAD_PATH . 'source/' . $id . '_' . $hash . '.' . $Image->getExtension();
										$thumb_path = PJ_UPLOAD_PATH . 'thumb/' . $id . '_' . $hash . '.' . $Image->getExtension();
										$medium_path = PJ_UPLOAD_PATH . 'medium/' . $id . '_' . $hash . '.' . $Image->getExtension();
										if ($Image->save($source_path))
										{
											$small_size = explode(",", preg_replace('/\s+/', '', PJ_IMAGE_SMALL));
											$medium_size = explode(",", preg_replace('/\s+/', '', PJ_IMAGE_MEDIUM));

											$Image->loadImage($source_path);
											$Image->resizeSmart($small_size[0], $small_size[1]);
											$Image->saveImage($thumb_path);

											$Image->loadImage($source_path);
											$Image->resizeSmart($medium_size[0], $medium_size[1]);
											$Image->saveImage($medium_path);

											$data['source_path'] = $source_path;
											$data['medium_path'] = $medium_path;
											$data['thumb_path'] = $thumb_path;
											$data['image_name'] = $_FILES['image']['name'];
											$pjPostModel->reset()->where('id', $id)->limit(1)->modifyAll($data);
										} else {
											$img_err = $Image->getError();
										}
									} else {
										$img_err = 'The image could not be processed (not enough memory available to resize it). Please try a smaller image.';
									}
								} else {
									$img_err = $Image->getError();
								}
							} else {
								$img_err = 'The server is missing the PHP GD extension needed to process images.';
							}
						}else if($_FILES['image']['error'] != 4){
							pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminPosts&action=pjActionUpdate&id=$id&err=AP09");
						}
						$err = 'AP03';
					}else{
						$err = 'AP03';
					}
				} else {
					$err = 'AP04';
					$img_err = '';
				}
				if (!empty($img_err))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminPosts&action=pjActionUpdate&id=$id&err=AP11&img_err=" . urlencode($img_err));
				}
				if ($slugTaken && isset($id) && (int) $id > 0)
				{
					// Saved with a slug from the title: open the post so the
					// user can enter a different slug.
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminPosts&action=pjActionUpdate&id=$id&tab_id=tabs-2&err=AP12");
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminPosts&action=pjActionIndex&err=$err");
			} else {

				// A brand new post has no existing category assignment to
				// protect, so a deactivated category simply shouldn't be
				// offered here (see pjActionUpdate() below for the edit-form
				// version of this, which has to also keep whatever category
				// the post already has).
				$content_locale = $this->getContentLocale();
				$this->setLocaleForm($content_locale);
				$this->set('category_arr', $this->translateRows(pjCategoryModel::factory()->where('t1.status', 'T')->orderBy('t1.category ASC')->findAll()->getData(), 'pjCategory', $content_locale));
				$this->set('author_arr', pjUserModel::factory()->orderBy('t1.name ASC')->findAll()->getData());

				$this->appendJs('tinymce.min.js', PJ_THIRD_PARTY_PATH . 'tinymce/');
				
				$this->appendJs('chosen.jquery.js', PJ_THIRD_PARTY_PATH . 'chosen/');
				$this->appendCss('chosen.css', PJ_THIRD_PARTY_PATH . 'chosen/');

				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminPosts.js');
				$this->appendJs('pjAdminSlug.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			$post_max_size = pjUtil::getPostMaxSize();
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size)
			{
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionIndex&err=AP06");
			}
			if (isset($_POST['post_update']))
			{
				$err = 'AP01';
				$img_err = '';

				$pjPostModel = pjPostModel::factory();
				$data = array();
				$data['modified'] = date('Y-m-d H:i:s');
				$data['published_date'] = pjUtil::formatDate($_POST['published_date'], $this->option_arr['o_date_format']);

				// URL slug from the form (editable). A slug already used by
				// another post is not saved - everything else is, and the same
				// page shows AP12.
				$slugTaken = false;
				$edit_locale = $this->getContentLocale();
				if (isset($_POST['url_slug']) && $edit_locale && $edit_locale !== (int) $this->getDefaultLocaleId())
				{
					// another language: the slug is that language's own (saved with its translation)
					$_POST['url_slug'] = $this->translatedSlug('pjPost', isset($_POST['id']) ? (int) $_POST['id'] : 0, $edit_locale, $_POST['url_slug'], isset($_POST['title']) ? $_POST['title'] : '');
				} elseif (isset($_POST['url_slug']))
				{
					list($_POST['url_slug'], $slugTaken) = $this->pjSlugFromForm('pjPostModel', isset($_POST['id']) ? (int) $_POST['id'] : 0, $_POST['url_slug'], isset($_POST['title']) ? $_POST['title'] : '');
				}

				if (isset($_FILES['image']))
				{
					if($_FILES['image']['error'] == 0)
					{
						$arr = $pjPostModel->find($_POST['id'])->getData();
						if(!empty($arr['source_path'])){
							$source_path = PJ_INSTALL_PATH . $arr['source_path'];
							$medium_path = PJ_INSTALL_PATH . $arr['medium_path'];
							$thumb_path = PJ_INSTALL_PATH . $arr['thumb_path'];
							@unlink($source_path);
							@unlink($medium_path);
							@unlink($thumb_path);
						}

						$Image = new pjImage();
						if ($Image->getErrorCode() !== 200)
						{
							$Image->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
							if ($Image->load($_FILES['image']))
							{
								$resp = $Image->isConvertPossible();
								if ($resp['status'] === true)
								{
									$hash = md5(uniqid(rand(), true));
									$source_path = PJ_UPLOAD_PATH . 'source/' . $_POST['id'] . '_' . $hash . '.' . $Image->getExtension();
									$medium_path = PJ_UPLOAD_PATH . 'medium/' . $_POST['id'] . '_' . $hash . '.' . $Image->getExtension();
									$thumb_path = PJ_UPLOAD_PATH . 'thumb/' . $_POST['id'] . '_' . $hash . '.' . $Image->getExtension();
									if ($Image->save($source_path))
									{
										$small_size = explode(",", preg_replace('/\s+/', '', PJ_IMAGE_SMALL));
										$medium_size = explode(",", preg_replace('/\s+/', '', PJ_IMAGE_MEDIUM));

										$Image->loadImage($source_path);
										$Image->resizeSmart($small_size[0], $small_size[1]);
										$Image->saveImage($thumb_path);

										$Image->loadImage($source_path);
										$Image->resizeSmart($medium_size[0], $medium_size[1]);
										$Image->saveImage($medium_path);

										$data['source_path'] = $source_path;
										$data['medium_path'] = $medium_path;
										$data['thumb_path'] = $thumb_path;
										$data['image_name'] = $_FILES['image']['name'];
									} else {
										$img_err = $Image->getError();
									}
								} else {
									$img_err = 'The image could not be processed (not enough memory available to resize it). Please try a smaller image.';
								}
							} else {
								$img_err = $Image->getError();
							}
						} else {
							$img_err = 'The server is missing the PHP GD extension needed to process images.';
						}
					}else if($_FILES['image']['error'] != 4){
						pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionUpdate&id=".$_POST['id'].($this->getContentLocale() ? '&locale=' . $this->getContentLocale() : '')."&tab_id=" . $_POST['tab_id']."&err=AP10");
					}
				}
				// The default language is also written to the original columns (the fallback);
				// another language only changes its own translation.
				$content_locale = $this->getContentLocale();
				$save_data = array_merge($_POST, $data);
				if ($content_locale && $content_locale !== (int) $this->getDefaultLocaleId())
				{
					foreach (pjAppController::$translatable['pjPost'] as $field)
					{
						unset($save_data[$field]);
					}
				}
				$pjPostModel->reset()->where('id', $_POST['id'])->limit(1)->modifyAll($save_data);
				if ($content_locale)
				{
					$this->saveTranslation('pjPost', (int) $_POST['id'], $content_locale, $this->getSubmittedTranslation());
				}
				$locale_param = $content_locale ? '&locale=' . $content_locale : '';

				if (!empty($img_err))
				{
					pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionUpdate&id=".$_POST['id'].$locale_param."&tab_id=" . $_POST['tab_id']."&err=AP11&img_err=" . urlencode($img_err));
				}
				if ($slugTaken)
				{
					$err = 'AP12';
				}
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionUpdate&id=".$_POST['id'].$locale_param."&tab_id=" . $_POST['tab_id']."&err=$err");
			} else {
				$vote_table = pjVoteModel::factory()->getTable();
				$arr = pjPostModel::factory()
					->select("t1.*, (SELECT COUNT(TV1.id) FROM `$vote_table` AS TV1 WHERE `TV1`.`post_id` = `t1`.`id` LIMIT 1) AS `cnt`,
									(SELECT SUM(TV2.vote_rate)/COUNT(TV2.id) FROM `$vote_table` AS TV2 WHERE `TV2`.`post_id` = `t1`.`id` LIMIT 1) AS `avg_rate`,
									(SELECT COUNT(TC.post_id) FROM `".pjCommentModel::factory()->getTable()."` AS TC WHERE t1.id = TC.post_id) AS `cnt_comments`
							")
					->find($_GET['id'])
					->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminPosts&action=pjActionIndex&err=AP08");
				}
				
				$can_update = true;
				if($this->isAuthor() && $arr['author_id'] != $this->getUserId())
				{
					$can_update = false;
				}
				if($can_update == true)
				{
					// Show the text of the language being edited. The default language falls back to
					// the original columns; another language shows only its own translation (empty
					// fields = not translated yet, the website then shows the default-language text).
					$content_locale = $this->getContentLocale();
					$i18n = $this->getTranslations('pjPost', $arr['id']);
					$translation_missing = false;
					if ($this->isMultiLang() && $content_locale !== (int) $this->getDefaultLocaleId())
					{
						foreach (pjAppController::$translatable['pjPost'] as $field)
						{
							$arr[$field] = isset($i18n[$content_locale][$field]) ? $i18n[$content_locale][$field] : '';
						}
						$translation_missing = empty($i18n[$content_locale]['title']) && empty($i18n[$content_locale]['body']);
					} elseif (isset($i18n[$content_locale])) {
						foreach (pjAppController::$translatable['pjPost'] as $field)
						{
							if ($field !== 'url_slug' && isset($i18n[$content_locale][$field]))
							{
								$arr[$field] = $i18n[$content_locale][$field];
							}
						}
					}
					$this->setLocaleForm($content_locale);
					$this->set('translation_missing', $translation_missing);
					$this->set('arr', $arr);

					// Only offer active categories here too, but keep this
					// post's own category in the list even if it's since
					// been deactivated - otherwise the dropdown would land
					// on no selection at all ("-- Choose--") when you open
					// this post to edit, and re-saving would silently clear
					// its category.
					$this->set('category_arr', $this->translateRows(pjCategoryModel::factory()->where('t1.status', 'T')->orWhere('t1.id', $arr['category_id'])->orderBy('t1.category ASC')->findAll()->getData(), 'pjCategory', $content_locale));
					$this->set('author_arr', pjUserModel::factory()->orderBy('t1.name ASC')->findAll()->getData());
					
					$this->appendJs('tinymce.min.js', PJ_THIRD_PARTY_PATH . 'tinymce/');
					
					$this->appendJs('chosen.jquery.js', PJ_THIRD_PARTY_PATH . 'chosen/');
					$this->appendCss('chosen.css', PJ_THIRD_PARTY_PATH . 'chosen/');
					$this->appendJs('jquery.ui.stars.min.js', PJ_THIRD_PARTY_PATH . 'jstarring/');

					$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
					$this->appendJs('pjAdminPosts.js');
					$this->appendJs('pjAdminSlug.js');
				}else{
					$this->set('status', 2);
				}
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	/**
	 * AJAX: generate a draft title + body for a post from a short topic,
	 * using the AI provider/model/key already saved in AI Settings. Never
	 * trusts a provider/model/key from the request - only the saved ones.
	 */
	public function pjActionGenerateAi()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyInvalidReq', true)));
		}
		if (!($this->isAdmin() || $this->isAuthor()))
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyForbidden', true)));
		}

		$enabled = isset($this->option_arr['o_ai_enabled']) && $this->option_arr['o_ai_enabled'] === 'T';
		if (!$enabled)
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiMsgDisabled', true)));
		}

		$provider = isset($this->option_arr['o_ai_provider']) ? $this->option_arr['o_ai_provider'] : '';
		$model    = isset($this->option_arr['o_ai_model'])    ? $this->option_arr['o_ai_model']    : '';
		$apiKey   = isset($this->option_arr['o_ai_api_key'])  ? $this->option_arr['o_ai_api_key']  : '';
		$topic    = isset($_POST['topic']) ? trim((string) $_POST['topic']) : '';
		// The draft is written in the language of the post form it goes to (the default language)
		$language = $this->getLocaleLabel($this->getDefaultLocaleId());

		// Release the session's exclusive file lock before the slow, blocking
		// provider call below. PHP's default session handler holds that lock
		// for the whole request, so without this a concurrent request in the
		// same admin session (e.g. the AI Writer wizard's separate "generate
		// an image" request, fired alongside this one) would queue behind it
		// and could itself time out. Nothing after this point reads or writes
		// $_SESSION.
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			session_write_close();
		}

		$result = pjAi::generatePost($provider, $model, $apiKey, $topic, $language);

		pjAppController::jsonResponse($result);
	}

	/**
	 * Separate "AI Writer" page (reached from the "+ Add Post" button on the
	 * posts list). Step 1 lets the author choose manual vs. AI; step 2 takes a
	 * topic/tone/length; step 3 shows a few generated drafts (title, body, SEO
	 * title, SEO description) to pick from. Picking one hands the draft off to
	 * the normal Add Post screen. All content on this page is view logic only -
	 * the actual generation happens via pjActionGenerateAiOptions().
	 */
	public function pjActionAiWriter()
	{
		$this->checkLogin();

		if ($this->isAdmin() || $this->isAuthor())
		{
			// The AI writes in the default language and the draft opens in that language's form
			$this->set('ai_locale_id', (int) $this->getDefaultLocaleId());
			$this->set('ai_language', $this->getLocaleLabel($this->getDefaultLocaleId()));
			$this->appendJs('pjAdminPosts.js');
		} else {
			$this->set('status', 2);
		}
	}

	/**
	 * AJAX: generate a few (title/body/SEO title/SEO description) draft
	 * options for a post from a topic (plus optional tone/length), using the
	 * AI provider/model/key already saved in AI Settings. Never trusts a
	 * provider/model/key from the request - only the saved ones. Backs the
	 * "AI Writer" wizard page (pjActionAiWriter).
	 */
	public function pjActionGenerateAiOptions()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyInvalidReq', true)));
		}
		if (!($this->isAdmin() || $this->isAuthor()))
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyForbidden', true)));
		}

		$enabled = isset($this->option_arr['o_ai_enabled']) && $this->option_arr['o_ai_enabled'] === 'T';
		if (!$enabled)
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiMsgDisabled', true)));
		}

		$provider = isset($this->option_arr['o_ai_provider']) ? $this->option_arr['o_ai_provider'] : '';
		$model    = isset($this->option_arr['o_ai_model'])    ? $this->option_arr['o_ai_model']    : '';
		$apiKey   = isset($this->option_arr['o_ai_api_key'])  ? $this->option_arr['o_ai_api_key']  : '';
		$topic    = isset($_POST['topic'])  ? trim((string) $_POST['topic'])  : '';
		$tone     = isset($_POST['tone'])   ? trim((string) $_POST['tone'])   : '';
		$length   = isset($_POST['length']) ? trim((string) $_POST['length']) : '';
		// The draft is written in the language of the post form it goes to (the default language)
		$language = $this->getLocaleLabel($this->getDefaultLocaleId());

		// See the matching comment in pjActionGenerateAi() above - this
		// releases the session lock before the slow provider call so it
		// doesn't serialize behind (or block) the AI Writer wizard's
		// concurrent "generate an image" request.
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			session_write_close();
		}

		$result = pjAi::generatePostOptions($provider, $model, $apiKey, $topic, $tone, $length, 3, $language);

		pjAppController::jsonResponse($result);
	}

	/**
	 * AJAX: generate a single optional featured image for the post being
	 * drafted, from the same topic used for pjActionGenerateAiOptions(). Never
	 * trusts a provider/key from the request - only the saved ones. Backs the
	 * "Also generate a featured image" checkbox on the "AI Writer" wizard page
	 * (pjActionAiWriter). Fired as its own request alongside
	 * pjActionGenerateAiOptions() rather than folded into it, so a failed or
	 * slow image never blocks the text drafts from coming back.
	 */
	public function pjActionGenerateAiImage()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyInvalidReq', true)));
		}
		if (!($this->isAdmin() || $this->isAuthor()))
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiVerifyForbidden', true)));
		}

		$enabled = isset($this->option_arr['o_ai_enabled']) && $this->option_arr['o_ai_enabled'] === 'T';
		if (!$enabled)
		{
			pjAppController::jsonResponse(array('ok' => false, 'text' => __('aiMsgDisabled', true)));
		}

		$provider = isset($this->option_arr['o_ai_provider']) ? $this->option_arr['o_ai_provider'] : '';
		$apiKey   = isset($this->option_arr['o_ai_api_key'])  ? $this->option_arr['o_ai_api_key']  : '';
		$topic    = isset($_POST['topic']) ? trim((string) $_POST['topic']) : '';

		$prompt = 'A high-quality, professional featured image for a blog post about: ' . $topic
				. '. Photographic or tasteful editorial illustration style, wide/landscape composition, no text, no watermarks, no logos.';

		// See the matching comment in pjActionGenerateAi() above - this is
		// the slower of the two concurrent requests (image generation can
		// take significantly longer than the text drafts), so it's the one
		// most likely to have been blocking pjActionGenerateAiOptions()
		// behind its session lock rather than the two genuinely running in
		// parallel.
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			session_write_close();
		}

		$result = pjAi::generateImage($provider, $apiKey, $prompt);

		pjAppController::jsonResponse($result);
	}

	public function pjActionResetVote()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			pjVoteModel::factory()->where('post_id', $_GET['id'])->eraseAll();
			pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminPosts&action=pjActionUpdate&id=" . $_GET['id']);
		} else {
			$this->set('status', 2);
		}
		exit;
	}
	
	public function pjActionStatusPost()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0)
			{
				pjPostModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
					'status' => ":IF(`status`='F','T','F')"
				));
			}
		}
		exit;
	}
	
	public function pjActionSavePost()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if($_POST['value'] != ''){
				$data = array($_POST['column'] => $_POST['value']);
				$locale_id = $this->isMultiLang() ? (int) $this->getLocaleId() : 0;
				$is_translatable = in_array($_POST['column'], pjAppController::$translatable['pjPost']);
				if ($locale_id > 0 && $is_translatable && $locale_id !== (int) $this->getDefaultLocaleId())
				{
					// another language: only its own translation changes
					$this->saveTranslation('pjPost', (int) $_GET['id'], $locale_id, array($_POST['column'] => $_POST['value']));
				} else {
					// Inline title edit: the URL slug stays; a post without one gets
					// one from the new title.
					if ($_POST['column'] == 'title')
					{
						$slug = $this->pjSlugForRename('pjPostModel', (int) $_GET['id'], $_POST['value']);
						if ($slug !== null)
						{
							$data['url_slug'] = $slug;
						}
					}
					pjPostModel::factory()->where('id', $_GET['id'])->limit(1)->modifyAll($data);
					if ($locale_id > 0 && $is_translatable)
					{
						$this->saveTranslation('pjPost', (int) $_GET['id'], $locale_id, array($_POST['column'] => $_POST['value']));
					}
				}
			}
		}
		exit;
	}
	
	public function pjActionExportPost()
	{
		$this->checkLogin();
		
		if (isset($_POST['record']) && is_array($_POST['record']))
		{
			$arr = pjPostModel::factory()->whereIn('id', $_POST['record'])->findAll()->getData();
			$csv = new pjCSV();
			$csv
				->setHeader(true)
				->setName("Posts-".time().".csv")
				->process($arr)
				->download();
		}
		exit;
	}
	
	public function pjActionDeletePost()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$response = array();
			$pjPostModel = pjPostModel::factory();
			$arr = $pjPostModel->find($_GET['id'])->getData();
			if ($pjPostModel->reset()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
			{
				if(!empty($arr['source_path'])){
					$source_path = PJ_INSTALL_PATH . $arr['source_path'];
					$medium_path = PJ_INSTALL_PATH . $arr['medium_path'];
					$thumb_path = PJ_INSTALL_PATH . $arr['thumb_path'];
					@unlink($source_path);
					@unlink($medium_path);
					@unlink($thumb_path);
				}
				pjCommentModel::factory()->where('post_id', $_GET['id'])->eraseAll();
				pjVoteModel::factory()->where('post_id', $_GET['id'])->eraseAll();
				$this->eraseTranslations('pjPost', $_GET['id']);
				$response['code'] = 200;
			} else {
				$response['code'] = 100;
			}
			pjAppController::jsonResponse($response);
		}
		exit;
	}
	
	public function pjActionDeletePostBulk()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0)
			{
				$pjPostModel = pjPostModel::factory();
				$arr = $pjPostModel->whereIn('id', $_POST['record'])->getData();
				foreach($arr as $v){
					if(!empty($v['source_path'])){
						$source_path = PJ_INSTALL_PATH . $v['source_path'];
						$medium_path = PJ_INSTALL_PATH . $v['medium_path'];
						$thumb_path = PJ_INSTALL_PATH . $v['thumb_path'];
						@unlink($source_path);
						@unlink($medium_path);
						@unlink($thumb_path);
					}
				}
				$pjPostModel->reset()->whereIn('id', $_POST['record'])->eraseAll();
				pjCommentModel::factory()->whereIn('post_id', $_POST['record'])->eraseAll();
				pjVoteModel::factory()->whereIn('post_id', $_POST['record'])->eraseAll();
				$this->eraseTranslations('pjPost', $_POST['record']);
			}
		}
		exit;
	}
	
	public function pjActionClone()
	{
		$this->checkLogin();

		if ($this->isAdmin() || $this->isAuthor())
		{
			$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
			if ($id > 0)
			{
				$arr = pjPostModel::factory()->find($id)->getData();
				if (!empty($arr))
				{
					// Authors may only duplicate their own posts (mirrors update rules).
					if (!$this->isAuthor() || (isset($arr['author_id']) && $arr['author_id'] == $this->getUserId()))
					{
						$item = $arr;
						unset($item['id']);
						$item['title']          = $arr['title'] . ' (copy)';
						$item['modified']       = date('Y-m-d H:i:s');
						// A clone is a new post being made today, not a copy of
						// whenever the original was published - give it today's
						// date instead of carrying over the source post's date.
						$item['published_date'] = date('Y-m-d');
						// Start the copy without image references; the files are
						// duplicated below so the copy is fully independent.
						$item['source_path'] = '';
						$item['medium_path'] = '';
						$item['thumb_path']  = '';
						$item['image_name']  = '';

						$item['url_slug'] = $this->pjUniqueSlug('pjPostModel', $item['title'], 0);

						$newId = pjPostModel::factory($item)->insert()->getInsertId();
						if ($newId !== false && (int) $newId > 0)
						{
							// the copy keeps the text of every language (title marked as a copy)
							foreach ($this->getTranslations('pjPost', $id) as $clone_locale => $clone_values)
							{
								if (!empty($clone_values['title']))
								{
									$clone_values['title'] .= ' (copy)';
								}
								$this->saveTranslation('pjPost', $newId, $clone_locale, $clone_values);
							}
						}

						if ($newId !== false && (int) $newId > 0 && !empty($arr['source_path']))
						{
							$ext  = pathinfo($arr['source_path'], PATHINFO_EXTENSION);
							$hash = md5(uniqid(rand(), true));
							$map  = array('source_path' => 'source/', 'medium_path' => 'medium/', 'thumb_path' => 'thumb/');
							$imgData = array();
							foreach ($map as $field => $dir)
							{
								if (!empty($arr[$field]) && is_file(PJ_INSTALL_PATH . $arr[$field]))
								{
									$newRel = PJ_UPLOAD_PATH . $dir . $newId . '_' . $hash . '.' . $ext;
									if (@copy(PJ_INSTALL_PATH . $arr[$field], PJ_INSTALL_PATH . $newRel))
									{
										$imgData[$field] = $newRel;
									}
								}
							}
							if (!empty($imgData))
							{
								$imgData['image_name'] = isset($arr['image_name']) ? $arr['image_name'] : '';
								pjPostModel::factory()->reset()->where('id', $newId)->limit(1)->modifyAll($imgData);
							}
						}
					}
				}
			}
			pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionIndex");
		}
	}

	public function pjActionDeleteImage()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$response = array();
			
			$pjPostModel = pjPostModel::factory();
			$arr = $pjPostModel->find($_GET['id'])->getData(); 
			
			if(!empty($arr)){
				if(!empty($arr['source_path'])){
					$source_path = PJ_INSTALL_PATH . $arr['source_path'];
					$medium_path = PJ_INSTALL_PATH . $arr['medium_path'];
					$thumb_path = PJ_INSTALL_PATH . $arr['thumb_path'];
					@unlink($source_path);
					@unlink($medium_path);
					@unlink($thumb_path);
				}
				
				$data = array();
				$data['source_path'] = ':NULL';
				$data['medium_path'] = ':NULL';
				$data['thumb_path'] = ':NULL';
				$data['image_name'] = ':NULL';
				$pjPostModel->reset()->where(array('id' => $_GET['id']))->limit(1)->modifyAll($data);
				
				$response['code'] = 200;
			}else{
				$response['code'] = 100;
			}
			
			pjAppController::jsonResponse($response);
		}
		else
		{
			// Not an AJAX request (e.g. the link was navigated to directly).
			// Do not delete on a plain GET (keeps deletion gated behind the
			// confirmed XHR call); just redirect back to the edit page so the
			// user never lands on a blank page.
			pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminPosts&action=pjActionUpdate&id=" . (int) (isset($_GET['id']) ? $_GET['id'] : 0));
		}
		exit;
	}

	public function pjActionExport()
	{
		$this->checkLogin();
	
		if ($this->isAdmin())
		{
			if(isset($_POST['posts_export']))
			{
				$pjPostModel = pjPostModel::factory()
					->select('t1.*, t2.category AS category_title, t3.name AS author_name')
					->join('pjCategory', 't1.category_id=t2.id', 'left outer')
					->join('pjUser', 't1.author_id=t3.id', 'left outer');
	
				$column = 'published_date';
				$direction = 'ASC';
				$where_str = pjUtil::getMadeWhere($_POST['made_period'], $this->option_arr['o_week_start']);
				if($where_str != '')
				{
					$pjPostModel->where($where_str);
				}
	
				$arr= $pjPostModel
					->orderBy("$column $direction")
					->findAll()
					->getData();
				if($_POST['type'] == 'file')
				{
					$this->setLayout('pjActionEmpty');
	
					$xml = new pjXML();
					$xml
						->setEncoding('UTF-8')
						->setName("Export-".time().".xml")
						->process($arr)
						->download();
					exit;
				}else{
					$pjPasswordModel = pjPasswordModel::factory();
					$password = md5($_POST['password'].PJ_SALT);
					$arr = $pjPasswordModel
						->where("t1.password", $password)
						->limit(1)
						->findAll()
						->getData();
					if (count($arr) != 1)
					{
						$pjPasswordModel->setAttributes(array('password' => $password))->insert();
					}
					$this->set('password', $password);
				}
			}
				
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdminPosts.js');
		} else {
			$this->set('status', 2);
		}
	}
	public function pjActionExportFeed()
	{
		$this->setLayout('pjActionEmpty');
		$access = true;
		if(isset($_GET['p']))
		{
			$pjPasswordModel = pjPasswordModel::factory();
			$arr = $pjPasswordModel
				->where('t1.password', $_GET['p'])
				->limit(1)
				->findAll()
				->getData();
			if (count($arr) != 1)
			{
				$access = false;
			}
		}
		if($access == true)
		{
			$arr = $this->pjGetFeedData($_GET);
				
			if(!empty($arr))
			{
				$xml = new pjXML();
				echo $xml
				->setEncoding('UTF-8')
				->process($arr)
				->getData();
			}
		}else{
			__('lblNoAccessToFeed');
		}
		exit;
	}
	public function pjGetFeedData($get)
	{
		$arr = array();
		$status = true;
		$period = '';
		if(isset($get['period']))
		{
			if(!ctype_digit($get['period']))
			{
				$status = false;
			}else{
				$period = $get['period'];
			}
		}else{
			$status = false;
		}
		if($status == true && $period != '')
		{
			$pjPostModel = pjPostModel::factory()
				->select('t1.*, t2.category AS category_title, t3.name AS author_name')
				->join('pjCategory', 't1.category_id=t2.id', 'left outer')
				->join('pjUser', 't1.author_id=t3.id', 'left outer');
			$column = 'published_date';
			$direction = 'ASC';
			$where_str = pjUtil::getMadeWhere($period, $this->option_arr['o_week_start']);
			if($where_str != '')
			{
				$pjPostModel->where($where_str);
			}
			$arr= $pjPostModel
				->orderBy("$column $direction")
				->findAll()
				->getData();
		}
		return $arr;
	}
}
?>