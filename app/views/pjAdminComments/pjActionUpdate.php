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
	
	pjUtil::printNotice(__('infoUpdateCommentTitle', true, false), __('infoUpdateCommentBody', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminComments&amp;action=pjActionUpdate" method="post" id="frmUpdateComment" class="form pj-form">
		<input type="hidden" name="comment_update" value="1" />
		<input type="hidden" name="id" value="<?php echo (int) $tpl['arr']['id']; ?>" />
		<p>
			<label class="title"><?php __('lblPost'); ?></label>
			<span class="inline_block">
				<label class="content"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionUpdate&amp;id=<?php echo $tpl['arr']['post_id'];?>"><?php echo pjSanitize::html($tpl['arr']['title']);?></a></label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblDateTimePosted'); ?></label>
			<span class="inline_block">
				<label class="content"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($tpl['arr']['created'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($tpl['arr']['created'])), 'H:i:s', $tpl['option_arr']['o_time_format']);?></label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblName', false, true); ?></label>
			<span class="inline_block">
				<input type="text" name="name" id="name" class="pj-form-field required w300" value="<?php echo pjSanitize::html($tpl['arr']['name']);?>"/>
			</span>
		</p>
		<p>
			<label class="title"><?php __('email'); ?></label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-email"></abbr></span>
				<input type="text" name="email" id="email" class="pj-form-field required email w200" value="<?php echo pjSanitize::html($tpl['arr']['email']); ?>" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblComment', false, true); ?></label>
			<span class="inline_block">
				<textarea id="comment" name="comment" class="pj-form-field w500 h200 required" required="required"><?php echo pjSanitize::clean($tpl['arr']['comment']); ?></textarea>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="status" id="status" class="pj-form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('comment_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['status'] ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminComments&action=pjActionIndex';" />
		</p>
	</form>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('lblFieldRequired', false, true); ?>";
	myLabel.invalid_email = "<?php __('lblInvalidEmail', false, true); ?>";
	</script>
	<?php
}
?>