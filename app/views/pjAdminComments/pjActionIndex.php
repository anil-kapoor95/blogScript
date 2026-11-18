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
		
		// AC01/AC03/AC09 are success confirmations (comment updated/added/
		// deleted) - everything else on this screen (not found, failed to
		// add/delete, blank name/email/comment) is a real error, so show
		// it in red instead of the default blue info style.
		$successCodes = array('AC01', 'AC03', 'AC09');
		$noticeType = in_array($_GET['err'], $successCodes) ? 'info' : 'error';
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']], true, true, false, $noticeType);
	}
	
	pjUtil::printNotice(__('infoCommentsTitle', true, false), __('infoCommentsBody', true, false)); 
	?>
	<div class="b10">
		<form action="" method="get" class="float_left pj-form frm-filter">
			<input type="text" name="q" class="pj-form-field pj-form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
		</form>
		<?php
		$comment_statarr = __('comment_statarr', true);
		?>
		<div class="float_right t5">
			<a href="#" class="pj-button btn-all"><?php __('lblAll', false, true); ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="W"><?php echo $comment_statarr['W']; ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="T"><?php echo $comment_statarr['T']; ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="F"><?php echo $comment_statarr['F']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div class="comment-grid"><div id="grid"></div></div>
	<script type="text/javascript">
	var pjGrid = pjGrid || {};
	pjGrid.queryString = "";
	pjGrid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
	<?php
	if (isset($_GET['post_id']) && (int) $_GET['post_id'] > 0)
	{
		?>pjGrid.queryString += "&post_id=<?php echo (int) $_GET['post_id']; ?>";<?php
	}
	if (isset($_GET['status']) && in_array($_GET['status'], array('T', 'F', 'W')))
	{
		?>pjGrid.queryString += "&status=<?php echo $_GET['status']; ?>";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.date_published = "<?php __('lblDatePublished', false, true); ?>";
	myLabel.name = "<?php __('lblName', false, true); ?>";
	myLabel.under_post = "<?php __('lblUnderPost', false, true); ?>";
	myLabel.revert_status = "<?php __('revert_status', false, true); ?>";
	myLabel.status_to_approved = "<?php __('lblSetStatusToApproved', false, true); ?>";
	myLabel.status_to_not_approved = "<?php __('lblSetStatusToNotApproved', false, true); ?>";
	myLabel.exported = "<?php __('lblExportToCSV', false, true); ?>";
	myLabel.active = "<?php echo $comment_statarr['T']; ?>";
	myLabel.inactive = "<?php echo $comment_statarr['F']; ?>";
	myLabel.waiting = "<?php echo $comment_statarr['W']; ?>";	
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	myLabel.status = "<?php __('lblStatus', false, true); ?>";
	</script>
	<?php
}
?>