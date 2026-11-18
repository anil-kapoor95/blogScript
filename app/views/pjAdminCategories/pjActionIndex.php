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
		
		// ACT01/ACT03 are success confirmations (category updated/added) -
		// everything else on this screen (not found, failed to add, blank
		// name) is a real error, so show it in red instead of the default
		// blue info style.
		$successCodes = array('ACT01', 'ACT03');
		$noticeType = in_array($_GET['err'], $successCodes) ? 'info' : 'error';
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']], true, true, false, $noticeType);
	}
	?>
	<?php 
	pjUtil::printNotice(__('infoCategoriesTitle', true, false), __('infoCategoriesBody', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<input type="hidden" name="controller" value="pjAdminCategories" />
		<input type="hidden" name="action" value="pjActionCreate" />
		<input type="submit" class="pj-button" value="<?php __('btnAdd'); ?>" />
		<p>&nbsp;</p>
	</form>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.category = "<?php __('lblCategory'); ?>";
	myLabel.status = "<?php __('lblStatus'); ?>";
	myLabel.active = "<?php __('lblActive'); ?>";
	myLabel.inactive = "<?php __('lblInActive'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation'); ?>";
	</script>
	<?php
}
?>