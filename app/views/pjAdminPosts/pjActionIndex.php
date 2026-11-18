<?php
if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			pjUtil::printNotice(NULL, $status[2]);
			break;
	}
} else {
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$bodies[$_GET['err']]);
		pjUtil::printNotice(@$titles[$_GET['err']], $bodies_text);
	}
	$u_statarr = __('u_statarr', true);
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionIndex"><?php __('tabPosts'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionExport"><?php __('tabExport'); ?></a></li>
		</ul>
	</div>
	<?php
	pjUtil::printNotice(__('infoPostsTitle', true, false), __('infoPostsBody', true, false));
	?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left pj-form r10">
			<input type="hidden" name="controller" value="pjAdminPosts" />
			<input type="hidden" name="action" value="pjActionAiWriter" />
			<input type="submit" class="pj-button" value="<?php __('btnAddPost'); ?>" />
		</form>
		<form action="" method="get" class="float_left pj-form frm-filter">
			<input type="text" name="q" class="pj-form-field pj-form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
			<button type="button" class="pj-button pj-button-detailed"><span class="pj-button-detailed-arrow"></span></button>
		</form>
		<?php
		$filter = __('filter', true);
		?>
		<div class="float_right t5">
			<a href="#" class="pj-button btn-all"><?php __('lblAll', false, true); ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="T"><?php echo $filter['active']; ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="F"><?php echo $filter['inactive']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div class="pj-form-filter-advanced" style="display: none">
		<span class="pj-menu-list-arrow"></span>
		<form action="" method="get" class="form pj-form pj-form-search frm-filter-advanced">
			<div class="float_left w300">
				<p>
					<label class="title80"><?php __('lblTitle'); ?></label>
					<input type="text" name="title" id="title" class="pj-form-field w150" />
				</p>
				<p>
					<label class="title80"><?php __('lblBlogText'); ?></label>
					<input type="text" name="blog_text" id="blog_text" class="pj-form-field w150" />
				</p>
			</div>
			<div class="float_right w400">
				
				<p>
					<label class="title100"><?php __('lblAuthor'); ?></label>
					<select name="author_id" id="author_id" class="pj-form-field w200">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($tpl['author_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>"<?php echo isset($_GET['author_id']) && (int) $_GET['author_id'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['name']); ?></option><?php
						}
						?>
					</select>
				</p>
				<p>
					<label class="title100"><?php __('lblPostedBetween'); ?></label>
					<span class="block float_left r10">
						<span class="pj-form-field-custom pj-form-field-custom-after">
							<input type="text" name="date_from" id="date_from" class="pj-form-field pointer w80 datepicker" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['arr']['published_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" />
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
					</span>
					<span class="block float_left r10 t5"><?php __('lblAnd');?></span>
					<span class="block float_left">
						<span class="pj-form-field-custom pj-form-field-custom-after">
							<input type="text" name="date_to" id="date_to" class="pj-form-field pointer w80 datepicker" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['arr']['published_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" />
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
					</span>
				</p>
			</div>
			<br class="clear_both" />
			<p>
				<label class="title80">&nbsp;</label>
				<input type="submit" value="<?php __('btnSearch'); ?>" class="pj-button" />
				<input type="reset" value="<?php __('btnCancel'); ?>" class="pj-button" />
			</p>
		</form>
	</div>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var pjGrid = pjGrid || {};
	pjGrid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
	pjGrid.queryString = "";
	<?php
	if (isset($_GET['author_id']) && (int) $_GET['author_id'] > 0)
	{
		?>pjGrid.queryString += "&author_id=<?php echo (int) $_GET['author_id']; ?>";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.date_published = "<?php __('lblDatePublished', false, true); ?>";
	myLabel.title = "<?php __('lblTitle', false, true); ?>";
	myLabel.comments = "<?php __('lblComments', false, true); ?>";
	myLabel.revert_status = "<?php __('revert_status', false, true); ?>";
	myLabel.exported = "<?php __('lblExport', false, true); ?>";
	myLabel.active = "<?php __('lblActive', false, true); ?>";
	myLabel.inactive = "<?php __('lblInActive', false, true); ?>";	
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	myLabel.status = "<?php __('lblStatus', false, true); ?>";
	</script>
	<?php
}
?>