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
	pjUtil::printNotice(__('infoSeoTitle', true, false), __('infoSeoDesc', true, false));
	
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionSeo" method="post" id="frmUpdateSeo" class="form pj-form">
		<input type="hidden" name="seo_update" value="1" />
		
		<div class="clear_both">
			
			<p>
				<label class="title"><?php __('lblMetaTitle'); ?></label>
				<span class="inline_block">
					<input id="o_meta_title" name="o_meta_title" class="pj-form-field w500" value="<?php echo pjSanitize::html($tpl['option_arr']['o_meta_title']); ?>"/>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaKeywords'); ?></label>
				<span class="inline_block">
					<input id="o_meta_keywords" name="o_meta_keywords" class="pj-form-field w500" value="<?php echo pjSanitize::html($tpl['option_arr']['o_meta_keywords']); ?>"/>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaDescription'); ?></label>
				<span class="inline_block">
					<textarea id="o_meta_description" name="o_meta_description" class="pj-form-field w500 150"><?php echo pjSanitize::html($tpl['option_arr']['o_meta_description']); ?></textarea>
				</span>
			</p>
			
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
			</p>
		</div>
	</form>
	<?php
}
?>