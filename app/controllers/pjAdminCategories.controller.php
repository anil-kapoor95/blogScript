<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminCategories extends pjAdmin
{
	/**
	 * jQuery validate "remote" rule of the "URL Slug" field: is ?url_slug=
	 * free (?id= = the category being edited)? See pjAdmin::pjSlugResponse().
	 */
	public function pjActionCheckSlug()
	{
		$this->checkLogin();
		$this->pjSlugResponse('pjCategoryModel');
	}

	public function pjActionCheckCategory()
	{
		$this->setAjax(true);
		
		if ($this->isXHR())
		{
			if (!isset($_GET['category']) || trim($_GET['category']) === '')
			{
				echo 'false';
				exit;
			}
			$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
			$locale_id = isset($_GET['locale']) ? (int) $_GET['locale'] : 0;
			if ($locale_id > 0 && $this->isMultiLang() && $locale_id !== (int) $this->getDefaultLocaleId())
			{
				// another language: the name has to be unique inside that language
				$pjMultiLangModel = pjMultiLangModel::factory()
					->where('t1.model', 'pjCategory')
					->where('t1.field', 'category')
					->where('t1.locale', $locale_id)
					->where('t1.content', $_GET['category']);
				if ($id > 0)
				{
					$pjMultiLangModel->where('t1.foreign_id !=', $id);
				}
				echo $pjMultiLangModel->findCount()->getData() == 0 ? 'true' : 'false';
				exit;
			}
			$pjCategoryModel = pjCategoryModel::factory()->where('t1.category', $_GET['category']);
			if ($id > 0)
			{
				$pjCategoryModel->where('t1.id !=', $id);
			}
			echo $pjCategoryModel->findCount()->getData() == 0 ? 'true' : 'false';
		}
		exit;
	}
	
	/**
	 * Language-aware list of the languages of the add/edit forms.
	 */
	private function setLocaleForm()
	{
		$lp_arr = array();
		$locale_str = array();
		foreach ($this->getLocales() as $item)
		{
			$lp_arr[] = $item;
			$locale_str[$item['id'] . '_'] = $item['file'];
		}
		$this->set('lp_arr', $lp_arr);
		$this->set('locale_str', pjAppController::jsonEncode($locale_str));
		$this->set('default_locale_id', $this->getDefaultLocaleId());
	}
	
	/**
	 * Read the translations of the submitted form (i18n[locale][field]) and save them.
	 * The default language also goes to the original columns, which stay the fallback.
	 *
	 * @return array The default-language values (for the original columns), or NULL when the form has no language fields
	 */
	private function saveCategoryI18n($id, $i18n)
	{
		$default_id = (int) $this->getDefaultLocaleId();
		$default_values = NULL;
		foreach ($this->getLocales() as $locale)
		{
			$lid = (int) $locale['id'];
			if (!isset($i18n[$lid]) || !is_array($i18n[$lid]))
			{
				continue;
			}
			$values = array();
			foreach (pjAppController::$translatable['pjCategory'] as $field)
			{
				if (isset($i18n[$lid][$field]))
				{
					$values[$field] = $field === 'category' ? trim($i18n[$lid][$field]) : $i18n[$lid][$field];
				}
			}
			if ($lid === $default_id)
			{
				$default_values = $values;
				// the default-language URL slug lives in the original column only
				unset($values['url_slug']);
			} elseif (isset($values['url_slug']))
			{
				$values['url_slug'] = $this->translatedSlug('pjCategory', $id, $lid, $values['url_slug'], isset($values['category']) ? $values['category'] : '');
			}
			$this->saveTranslation('pjCategory', $id, $lid, $values);
		}
		return $default_values;
	}
	
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			if (isset($_POST['category_create']))
			{
				$default_id = (int) $this->getDefaultLocaleId();
				$i18n = isset($_POST['i18n']) && is_array($_POST['i18n']) ? $_POST['i18n'] : NULL;
				if (!is_null($i18n))
				{
					// the default language fills the original columns (the fallback)
					$default_values = isset($i18n[$default_id]) && is_array($i18n[$default_id]) ? $i18n[$default_id] : array();
					foreach (pjAppController::$translatable['pjCategory'] as $field)
					{
						$_POST[$field] = isset($default_values[$field]) ? $default_values[$field] : '';
					}
				}
				// A category name of only whitespace (or empty) isn't a real
				// name - reject it instead of silently saving blank-looking
				// rows (see pjActionUpdate() and pjActionSaveCategory() below
				// for the matching checks on edit/inline-rename).
				$category = isset($_POST['category']) ? trim($_POST['category']) : '';
				if ($category === '')
				{
					$err = 'ACT09';
				} else {
					$_POST['category'] = $category;
					// URL slug from the form (filled in from the name, editable);
					// a slug already used by another category is not saved.
					list($_POST['url_slug'], $slugTaken) = $this->pjSlugFromForm('pjCategoryModel', 0, isset($_POST['url_slug']) ? $_POST['url_slug'] : '', $category);
					$id = pjCategoryModel::factory($_POST)->insert()->getInsertId();
					if ($id !== false && (int) $id > 0)
					{
						if (!is_null($i18n))
						{
							$this->saveCategoryI18n($id, $i18n);
						}
						if ($slugTaken)
						{
							// Saved with a slug from the name: open it so the
							// user can enter a different slug.
							pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminCategories&action=pjActionUpdate&id=" . (int) $id . "&err=ACT10");
						}
						$err = 'ACT03';
					} else {
						$err = 'ACT04';
					}
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminCategories&action=pjActionIndex&err=$err");
			} else {
				$this->setLocaleForm();
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('pjAdminCategories.js');
				$this->appendJs('pjAdminSlug.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
		
	public function pjActionDeleteCategory()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			$response = array();
			$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
			// A category that still has posts in it shouldn't be deletable -
			// this used to delete the category AND every post (plus their
			// comments/votes) in it, which is a lot to lose to one click.
			// Block the delete and say why instead.
			if ($id > 0 && pjPostModel::factory()->where('category_id', $id)->findCount()->getData() > 0)
			{
				$response['code'] = 409;
				$response['message'] = __('admin_category_in_use', true);
			}
			else if (pjCategoryModel::factory()->setAttributes(array('id' => $id))->erase()->getAffectedRows() == 1)
			{
				// No posts reference this category (checked above), so these
				// are just a safety net, not an expected cleanup anymore.
				pjPostModel::factory()->where('category_id', $id)->eraseAll();
				pjCommentModel::factory()->where('category_id', $id)->eraseAll();
				pjVoteModel::factory()->where('category_id', $id)->eraseAll();
				$this->eraseTranslations('pjCategory', $id);
				$response['code'] = 200;
			} else {
				$response['code'] = 100;
			}
			pjAppController::jsonResponse($response);
		}
		exit;
	}

	public function pjActionDeleteCategoryBulk()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0)
			{
				// Same rule as the single-record delete above - don't remove
				// a category that still has posts in it. Categories still in
				// use are quietly left out of the bulk delete rather than
				// failing the whole batch (there's no per-row feedback
				// channel on this bulk action to explain a partial failure).
				$inUseRows = pjPostModel::factory()->whereIn('category_id', $_POST['record'])->select('category_id')->findAll()->getData();
				$inUseIds = array_map('strval', array_column($inUseRows, 'category_id'));
				$deletable = array_diff($_POST['record'], $inUseIds);
				if (count($deletable) > 0)
				{
					pjCategoryModel::factory()->whereIn('id', $deletable)->eraseAll();
					pjCommentModel::factory()->whereIn('category_id', $deletable)->eraseAll();
					pjVoteModel::factory()->whereIn('category_id', $deletable)->eraseAll();
					$this->eraseTranslations('pjCategory', $deletable);
				}
			}
		}
		exit;
	}
	
	public function pjActionGetCategory()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjCategoryModel = pjCategoryModel::factory();
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				if ($this->isMultiLang() && $this->getLocaleId())
				{
					$pjCategoryModel->where("(t1.category LIKE '%$q%' OR t1.id IN (SELECT TL.foreign_id FROM `".pjMultiLangModel::factory()->getTable()."` AS TL WHERE TL.model='pjCategory' AND TL.field='category' AND TL.locale='".(int) $this->getLocaleId()."' AND TL.content LIKE '%$q%'))");
				} else {
					$pjCategoryModel->where('t1.category LIKE', "%$q%");
				}
			}

			$column = 'category';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjCategoryModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}
			
			$data = $pjCategoryModel->select('t1.*')
				->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
			$data = $this->translateRows($data, 'pjCategory');
				
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
		
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isAuthor())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminCategories.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSaveCategory()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
			$locale_id = $this->isMultiLang() ? (int) $this->getLocaleId() : 0;
			$is_default = $locale_id === 0 || $locale_id === (int) $this->getDefaultLocaleId();
			if($_POST['column'] == 'category')
			{
				// Trim first so "   " (or "  Generation  ") can't be saved
				// as-is - a whitespace-only value is treated the same as an
				// empty one (silently ignored, matching this endpoint's
				// existing behaviour for a truly empty $_POST['value']).
				$value = trim($_POST['value']);
				if($value != '')
				{
					if ($is_default)
					{
						// Inline rename: the URL slug stays; a category without
						// one gets one from the new name.
						$data = array($_POST['column'] => $value);
						$slug = $this->pjSlugForRename('pjCategoryModel', $id, $value);
						if ($slug !== null)
						{
							$data['url_slug'] = $slug;
						}
						pjCategoryModel::factory()->where('id', $id)->limit(1)->modifyAll($data);
						if ($locale_id > 0)
						{
							$this->saveTranslation('pjCategory', $id, $locale_id, array('category' => $value));
						}
					} else {
						$this->saveTranslation('pjCategory', $id, $locale_id, array('category' => $value));
					}
				}
			}else{
				if (in_array($_POST['column'], pjAppController::$translatable['pjCategory']) && !$is_default)
				{
					$this->saveTranslation('pjCategory', $id, $locale_id, array($_POST['column'] => $_POST['value']));
				} else {
					pjCategoryModel::factory()->where('id', $id)->limit(1)->modifyAll(array($_POST['column'] => $_POST['value']));
					if ($locale_id > 0 && in_array($_POST['column'], pjAppController::$translatable['pjCategory']))
					{
						$this->saveTranslation('pjCategory', $id, $locale_id, array($_POST['column'] => $_POST['value']));
					}
				}
			}
		}
		exit;
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();

		if ($this->isAdmin() || $this->isAuthor())
		{
			if (isset($_POST['category_update']))
			{
				$default_id = (int) $this->getDefaultLocaleId();
				$i18n = isset($_POST['i18n']) && is_array($_POST['i18n']) ? $_POST['i18n'] : NULL;
				if (!is_null($i18n))
				{
					// the default language fills the original columns (the fallback)
					$default_values = isset($i18n[$default_id]) && is_array($i18n[$default_id]) ? $i18n[$default_id] : array();
					foreach (pjAppController::$translatable['pjCategory'] as $field)
					{
						$_POST[$field] = isset($default_values[$field]) ? $default_values[$field] : '';
					}
				}
				// Same blank-name guard as pjActionCreate() - don't let an
				// edit silently blank out (or whitespace-pad) a category's
				// name.
				$category = isset($_POST['category']) ? trim($_POST['category']) : null;
				if ($category !== null && $category === '')
				{
					pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminCategories&action=pjActionIndex&err=ACT09");
				} else {
					if ($category !== null)
					{
						$_POST['category'] = $category;
					}
					// URL slug from the form (editable). A slug already used by
					// another category is not saved - everything else is, and
					// the same page shows ACT10.
					$slugTaken = false;
					if (isset($_POST['url_slug']))
					{
						if ($category === null)
						{
							$row = pjCategoryModel::factory()->find((int) $_POST['id'])->getData();
							$category = !empty($row) ? $row['category'] : '';
						}
						list($_POST['url_slug'], $slugTaken) = $this->pjSlugFromForm('pjCategoryModel', (int) $_POST['id'], $_POST['url_slug'], $category);
					}
					pjCategoryModel::factory()->where('id', $_POST['id'])->limit(1)->modifyAll($_POST);
					if (!is_null($i18n))
					{
						$this->saveCategoryI18n((int) $_POST['id'], $i18n);
					}
					if ($slugTaken)
					{
						pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminCategories&action=pjActionUpdate&id=" . (int) $_POST['id'] . "&err=ACT10");
					}
					pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminCategories&action=pjActionIndex&err=ACT01");
				}
			} else {
				$arr = pjCategoryModel::factory()->find($_GET['id'])->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminCategories&action=pjActionIndex&err=ACT08");
				}
				
				$arr['i18n'] = $this->getTranslations('pjCategory', $arr['id']);
				$this->set('arr', $arr);
				$this->setLocaleForm();
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('pjAdminCategories.js');
				$this->appendJs('pjAdminSlug.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
}
?>