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
		// ACT10: saved, but the URL slug entered is used by another category.
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']], true, true, false, 'error');
	}
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminCategories&amp;action=pjActionUpdate" method="post" id="frmUpdateCategory" class="form pj-form">
		<input type="hidden" name="category_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<div class="clear_both">
			
			<p>
				<label class="title"><?php __('lblCategory'); ?>:</label>
				<span class="inline_block">
					<input type="text" id="category" name="category" class="pj-form-field w300 required" required="required" lang="<?php echo $v['id']; ?>" value="<?php echo pjSanitize::html($tpl['arr']['category']); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaTitle'); ?></label>
				<span class="inline_block">
					<input id="meta_title" name="meta_title" class="pj-form-field w500" value="<?php echo pjSanitize::html($tpl['arr']['meta_title']); ?>"/>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaKeywords'); ?></label>
				<span class="inline_block">
					<input id="meta_keywords" name="meta_keywords" class="pj-form-field w500" value="<?php echo pjSanitize::html($tpl['arr']['meta_keywords']); ?>"/>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaDescription'); ?></label>
				<span class="inline_block">
					<textarea id="meta_description" name="meta_description" class="pj-form-field w500 h100"><?php echo pjSanitize::html($tpl['arr']['meta_description']); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblUrlSlug'); ?></label>
				<span class="inline_block">
					<input type="text" id="url_slug" name="url_slug" class="pj-form-field w500" maxlength="200" value="<?php echo pjSanitize::html(isset($tpl['arr']['url_slug']) ? $tpl['arr']['url_slug'] : ''); ?>" data-slug-source="#category" data-slug-url="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminCategories&amp;action=pjActionCheckSlug" data-slug-id="<?php echo (int) $tpl['arr']['id']; ?>" data-slug-autofill="<?php echo trim((string) @$tpl['arr']['url_slug']) === '' ? '1' : '0'; ?>" data-msg-remote="<?php __('lblCategorySlugTaken', false, true); ?>" placeholder="<?php __('hintCategorySlugEditable', false, true); ?>" />
				</span>
			</p>	
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
				<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_FOLDER; ?>index.php?controller=pjAdminCategories&action=pjActionIndex';" />
			</p>
		</div>
	</form>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('lblFieldRequired', false, true); ?>";
	myLabel.same_category = "<?php __('lblSameCategory', false, true); ?>";
	</script>
	<?php
}
?>