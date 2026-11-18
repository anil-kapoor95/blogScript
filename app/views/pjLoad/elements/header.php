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
					<input type="hidden" name="category_id" value="<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : NULL;?>" />
					<input type="hidden" name="archive" value="<?php echo isset($_GET['archive']) ? $_GET['archive'] : NULL;?>" />
					
					<input type="text" name="keyword" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : null;?>" class="form-control" id="pjBlSearchField"/>
				</form>
			</div>
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