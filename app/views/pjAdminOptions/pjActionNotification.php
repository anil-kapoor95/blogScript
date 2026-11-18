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
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/optmenu.php'; 
	pjUtil::printNotice(__('infoNotificationsTitle', true, false), __('infoNotificationsDesc', true, false));
	
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionNotification" method="post" id="frmUpdateNotification" class="form pj-form">
		<input type="hidden" name="notification_update" value="1" />
		
		<div class="clear_both">
			
			<p>
				<label class="title"><?php __('lblSubject'); ?></label>
				<span class="inline_block">
					<input id="o_notifiy_subject" name="o_notifiy_subject" class="pj-form-field required w300" value="<?php echo pjSanitize::html($tpl['option_arr']['o_notify_subject']); ?>"/>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMessage'); ?></label>
				<span class="inline_block">
					<textarea id="o_notify_message" name="o_notify_message" class="pj-form-field required w500 h300"><?php echo pjSanitize::html($tpl['option_arr']['o_notify_message']); ?></textarea>
				</span>
			</p>
			
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
			</p>
		</div>
	</form>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.choose = "-- <?php __('lblChoose'); ?> --";
	</script>
	<?php
}
?>