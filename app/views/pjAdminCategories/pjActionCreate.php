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
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminCategories&amp;action=pjActionCreate" method="post" id="frmCreateCategory" class="pj-form form">
		<input type="hidden" name="category_create" value="1" />
		<div class="clear_both">

			<?php
			$multi = (int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1;
			if ($multi)
			{
				?><div class="multilang b10"></div><p class="b10"><?php __('lblContentLanguageHint'); ?></p><?php
			}
			foreach ($tpl['lp_arr'] as $v)
			{
				$is_def = (int) $v['is_default'] === 1;
				?>
				<div class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo $is_def ? NULL : 'none'; ?>">
					<p>
						<label class="title"><?php __('lblCategory'); ?>:</label>
						<span class="inline_block">
							<input type="text" id="i18n_category_<?php echo $v['id']; ?>" name="i18n[<?php echo $v['id']; ?>][category]" class="pj-form-field w300<?php echo $is_def ? ' required' : NULL; ?>"<?php echo $is_def ? ' required="required"' : NULL; ?> lang="<?php echo $v['id']; ?>" value="" />
							<?php if ($multi) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($v['flag_url']); ?>" alt="" /></span><?php endif; ?>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblMetaTitle'); ?></label>
						<span class="inline_block">
							<input id="i18n_meta_title_<?php echo $v['id']; ?>" name="i18n[<?php echo $v['id']; ?>][meta_title]" class="pj-form-field w500" value=""/>
							<?php if ($multi) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($v['flag_url']); ?>" alt="" /></span><?php endif; ?>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblMetaKeywords'); ?></label>
						<span class="inline_block">
							<input id="i18n_meta_keywords_<?php echo $v['id']; ?>" name="i18n[<?php echo $v['id']; ?>][meta_keywords]" class="pj-form-field w500" value=""/>
							<?php if ($multi) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($v['flag_url']); ?>" alt="" /></span><?php endif; ?>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblMetaDescription'); ?></label>
						<span class="inline_block">
							<textarea id="i18n_meta_description_<?php echo $v['id']; ?>" name="i18n[<?php echo $v['id']; ?>][meta_description]" class="pj-form-field w500 h100"></textarea>
							<?php if ($multi) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($v['flag_url']); ?>" alt="" /></span><?php endif; ?>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblUrlSlug'); ?></label>
						<span class="inline_block">
							<?php if ($is_def) : ?>
							<input type="text" id="i18n_url_slug_<?php echo $v['id']; ?>" name="i18n[<?php echo $v['id']; ?>][url_slug]" class="pj-form-field w500" maxlength="200" data-slug-source="#i18n_category_<?php echo $v['id']; ?>" data-slug-url="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminCategories&amp;action=pjActionCheckSlug" data-slug-id="<?php echo 0; ?>" data-slug-autofill="1" data-msg-remote="<?php __('lblCategorySlugTaken', false, true); ?>" placeholder="<?php __('hintCategorySlugEditable', false, true); ?>" />
							<?php else : ?>
							<input type="text" id="i18n_url_slug_<?php echo $v['id']; ?>" name="i18n[<?php echo $v['id']; ?>][url_slug]" class="pj-form-field w500" maxlength="200" data-slug-source="#i18n_category_<?php echo $v['id']; ?>" data-slug-autofill="1" placeholder="<?php __('hintCategorySlugEditable', false, true); ?>" />
							<?php endif; ?>
							<?php if ($multi) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($v['flag_url']); ?>" alt="" /></span><?php endif; ?>
						</span>
					</p>
				</div>
				<?php
			}
			?>

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
	myLabel.default_locale = <?php echo (int) $tpl['default_locale_id']; ?>;
	(function ($) {
		$(function() {
			if ($(".multilang").length > 0 && $.fn.multilang !== undefined) {
				$(".multilang").multilang({
					langs: <?php echo $tpl['locale_str']; ?>,
					flagPath: "<?php echo PJ_FRAMEWORK_LIBS_PATH; ?>pj/img/flags/",
					select: function (event, ui) {}
				});
			}
		});
	})(jQuery_1_8_2);
	</script>
	<?php
}
?>