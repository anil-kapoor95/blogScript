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
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionIndex"><?php __('tabPosts'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionExport"><?php __('tabExport'); ?></a></li>
		</ul>
	</div>
	
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	pjUtil::printNotice(@$titles['AR21'], @$bodies['AR21']);
	
	$export_types = __('export_types', true, false);
	$made_arr = __('made_arr', true, false);
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionExport" method="post" id="frmExportPosts" class="form pj-form">
		<input type="hidden" name="posts_export" value="1" />
		<p>
			<label class="title"><?php __('lblType'); ?></label>
			<span class="inline_block t5">
				<span class="block float_left r10">
					<input type="radio" name="type" id="file" value="file"<?php echo isset($_POST['type']) ? ($_POST['type'] == 'file' ? ' checked="checked"' : null) : ' checked="checked"'; ?> class="float_left r3"/>
					<span class="inline_block">
						<label for="file"><?php echo $export_types['file'];?></label>
					</span>
				</span>
				<span class="block float_left">
					<input type="radio" name="type" id="feed" value="feed"<?php echo isset($_POST['type']) ? ($_POST['type'] == 'feed' ? ' checked="checked"' : null) : null; ?> class="float_left r3"/>
					<span class="inline_block">
						<label for="feed"><?php echo $export_types['feed'];?></label>
					</span>
				</span>
			</span>
		</p>
		<p class="tsPassowrdContainer" style="display:<?php echo isset($_POST['type']) ? ($_POST['type'] == 'file' ? ' none' : ' block' ) : ' none'; ?>">
			<label class="title"><?php __('lblEnterPassword');?></label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="text" id="feed_password" name="password" class="pj-form-field w200" value="<?php echo isset($_POST['password']) ? $_POST['password'] : null; ?>"/>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblPublishedPosts'); ?></label>
			<span class="inline_block">
				<select name="made_period" id="made_period" class="pj-form-field w150">
					<?php
					foreach($made_arr as $k => $v)
					{
						?><option value="<?php echo $k;?>"<?php echo isset($_POST['made_period']) ? ($_POST['made_period'] == $k ? ' selected="selected"' : null) : null; ?>><?php echo $v;?></option><?php 
					} 
					?>
				</select>
			</span>
		</p>
		
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" id="tsSubmitButton" value="<?php isset($_POST['type']) ? ($_POST['type'] == 'file' ? __('btnExport') : __('btnGetFeedURL') ) :  __('btnExport'); ?>" class="pj-button" />
		</p>
		<?php
		if(isset($_POST['type']) && $_POST['type'] == 'feed') 
		{
			?>
			<div class="tsFeedContainer">
				<br/>
				<?php pjUtil::printNotice(__('infoPostsFeedTitle', true), __('infoPostsFeedDesc', true)); ?>
				<span class="inline_block">
					<textarea id="posts_feed" name="posts_feed" class="pj-form-field h80" style="width: 726px;"><?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminPosts&amp;action=pjActionExportFeed&amp;period=<?php echo $_POST['made_period']; ?>&amp;p=<?php echo isset($tpl['password']) ? $tpl['password'] : null;?></textarea>
				</span>
			</div>
			<?php
		} 
		?>
	</form>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.btn_export = "<?php __('btnExport'); ?>";
	myLabel.btn_get_url = "<?php __('btnGetFeedURL'); ?>";
	</script>
	<?php
}
?>