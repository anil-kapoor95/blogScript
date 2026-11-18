<header class="panel-heading pjBlHeader">
	<div class="pjBlHeaderInner">
		<div class="btn-group pjBlNav">
			<?php
			$home_url =  $_SERVER['SCRIPT_NAME'] . '?controller=pjLoad&amp;action=pjActionIndex' . (isset($_GET['iframe']) ? '&amp;iframe' : NULL);
			?>
			<a href="<?php echo $home_url;?>" type="button" class="btn btn-default">
				<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
			</a>
			<?php
			if($tpl['option_arr']['o_show_category'] == 'Yes')
			{ 
				?>
				<button type="button" class="btn btn-default pjBlFiltersTrigger" data-pjBlFilter="#pjBlFilterCategories"><?php __('front_categories');?></button>
				<?php
			} 
			?>
			<button type="button" class="btn btn-default pjBlFiltersTrigger" data-pjBlFilter="#pjBlFilterArchive"><?php __('front_archive');?></button>
		
			<div class="btn btn-default pjBlSearch">
				<form id="pjSbSearchForm_<?php echo $index;?>" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
					<label for="pjBlSearchField">
						<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
					</label>
					<input type="hidden" name="controller" value="pjLoad" />
					<input type="hidden" name="action" value="pjActionIndex" />
					<input type="hidden" name="dosearch" value="1" />
					<?php if ($controller->localeQuery() !== '') { ?><input type="hidden" name="blog_lang" value="<?php echo pjSanitize::html($controller->localeIso($controller->getLocaleId())); ?>" /><?php } ?>
					<input type="hidden" name="category_id" value="<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : NULL;?>" />
					<input type="hidden" name="archive" value="<?php echo isset($_GET['archive']) ? $_GET['archive'] : NULL;?>" />
					
					<input type="text" name="keyword" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : null;?>" class="form-control" id="pjBlSearchField"/>
				</form>
			</div>
			<?php
			// Language switcher (only when more than one language has been added)
			if ($controller->isMultiLang())
			{
				?>
				<?php
				$lang_current = NULL;
				foreach ($controller->getLocales() as $locale)
				{
					if ((int) $locale['id'] === (int) $controller->getLocaleId()) { $lang_current = $locale; break; }
				}
				if (!$lang_current) { $locale_list = $controller->getLocales(); $lang_current = reset($locale_list); }
				$lang_code = function ($iso) {
					$parts = preg_split('/[-_]/', (string) $iso);
					return strtoupper(end($parts));
				};
				?>
				<details class="pjBlLang" aria-label="<?php __('front_language'); ?>">
					<summary class="btn btn-default pjBlLangCurrent">
						<img src="<?php echo pjSanitize::html($lang_current['flag_url']); ?>" alt="" />
						<span class="pjBlLangCode"><?php echo pjSanitize::html($lang_code($lang_current['language_iso'])); ?></span>
						<span class="pjBlLangCaret" aria-hidden="true"></span>
					</summary>
					<ul class="pjBlLangMenu list-unstyled">
						<?php
						foreach ($controller->getLocales() as $locale)
						{
							$is_current = (int) $locale['id'] === (int) $lang_current['id'];
							?>
							<li<?php echo $is_current ? ' class="active"' : NULL; ?>><a href="<?php echo $controller->switchLocaleUrl($locale['id'], true); ?>" class="pjBlLangItem<?php echo $is_current ? ' active' : NULL; ?>" hreflang="<?php echo pjSanitize::html($locale['language_iso']); ?>" lang="<?php echo pjSanitize::html($locale['language_iso']); ?>"<?php echo $is_current ? ' aria-current="true"' : NULL; ?>><img src="<?php echo pjSanitize::html($locale['flag_url']); ?>" alt="" /><span><?php echo pjSanitize::html($locale['name']); ?></span></a></li>
							<?php
						}
						?>
					</ul>
				</details><!-- /.pjBlLang -->
				<?php
			}
			?>
		</div><!-- /.btn-group pjBlNav -->
	</div><!-- /.pjBlHeaderInner -->
	
	<div class="pjBlFiltersDropdown">
		<button type="button" class="btn pjBlFiltersRemove">
			<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
		</button>
		<?php
		if($tpl['option_arr']['o_show_category'] == 'Yes')
		{ 
			?>
			<div class="pjBlFilter" id="pjBlFilterCategories">
				<p class="pjBlFilterTitle"><?php __('front_categories');?></p><!-- /.pjBlFilterTitle -->
	
				<ul class="list-unstyled">
					<li<?php echo !isset($_GET['category_id']) ? ' class="active"' : NULL;?>><a href="<?php echo $_SERVER['SCRIPT_NAME'] . '?controller=pjLoad&amp;action=pjActionIndex' . (isset($_GET['iframe']) ? '&amp;iframe' : NULL);?>"><?php __('front_all_posts');?></a></li>					
					<?php
					foreach($tpl['category_arr'] as $v)
					{ 
						if($v['cnt_posts'] > 0)
						{
							$category_url = $controller->categoryUrl($v['id'], true);
							?>
							<li<?php echo isset($_GET['category_id']) ? ($_GET['category_id'] == $v['id'] ? ' class="active"' : NULL) : NULL;?>><a class="pjSbCategoryItem" href="<?php echo $category_url;?>" data-id="<?php echo $v['id'];?>"><?php echo pjSanitize::html($v['category']);?></a></li>
							<?php
						}
					} 
					?>
				</ul><!-- /.list-unstyled -->
			</div><!-- /#pjBlFilterCategories.pjBlFilter -->
			<?php
		}
		?>
	
		<div class="pjBlFilter" id="pjBlFilterArchive">
			<p class="pjBlFilterTitle"><?php __('front_archive');?></p><!-- /.pjBlFilterTitle -->
	
			<ul class="list-unstyled">
				<li<?php echo isset($_GET['archive']) ? ($_GET['archive'] == '' ? ' class="active"' : NULL) : ' class="active"';?>><a class="pjSbArchiveItem" href="#" data-archive=""><?php __('front_all_posts');?></a></li>
				<?php
				$months = __('months', true);
				foreach($tpl['current_year_arr'] as $k => $v)
				{
					$archive = $v['pub_year'] . '-'. str_pad($v['pub_month'], 2, '0', STR_PAD_LEFT); 
					?>
					<li<?php echo isset($_GET['archive']) ? ($_GET['archive'] == $archive ? ' class="active"' : NULL) : NULL;?>><a class="pjSbArchiveItem" href="#" data-archive="<?php echo $archive?>"><?php echo $months[$v['pub_month']]; ?>, <?php echo $v['pub_year'];?> (<?php echo $v['cnt_posts'];?>)</a></li>
					<?php
				}
				foreach($tpl['past_year_arr'] as $k => $v)
				{
					$archive = $v['pub_year'];
					?>
					<li<?php echo isset($_GET['archive']) ? ($_GET['archive'] == $archive ? ' class="active"' : NULL) : NULL;?>><a class="pjSbArchiveItem" href="#" data-archive="<?php echo $archive?>"><?php echo $v['pub_year'];?> (<?php echo $v['cnt_posts'];?>)</a></li>
					<?php
				} 
				?>
			</ul><!-- /.list-unstyled -->
		</div><!-- /#pjBlFilterArchive.pjBlFilter -->
	</div><!-- /.pjBlFiltersDropdown -->
	</header><!-- /.panel-heading pjBlHeader -->