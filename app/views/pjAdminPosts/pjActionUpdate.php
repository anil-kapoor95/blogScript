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

		$title_text = @$titles[$_GET['err']];
		$body_raw   = @$bodies[$_GET['err']];

		// The default AP10 entry ("image not uploaded") ships with only a vague
		// title and no body, so the admin never learns *why* the image was
		// skipped. Provide a clear, specific message that includes the real
		// upload limit ({SIZE} is replaced with upload_max_filesize below).
		if (isset($_GET['err']) && $_GET['err'] === 'AP10')
		{
			$title_text = 'Image not uploaded';
			if (empty($body_raw))
			{
				$body_raw = 'Your post was saved, but the image was not uploaded because its file size is larger than the maximum size your server allows ({SIZE}). Please upload a smaller image (under {SIZE}) and try again.';
			}
		}

		// AP11: the post was saved, but the image itself could not be processed
		// (bad/corrupt file, unreadable upload, out of memory to resize it, or
		// the file could not be moved into place). $_GET['img_err'] carries the
		// specific reason from the controller so the admin sees exactly why,
		// instead of a generic "saved successfully" message.
		if (isset($_GET['err']) && $_GET['err'] === 'AP11')
		{
			$title_text = 'Image not uploaded';
			$reason = isset($_GET['img_err']) && $_GET['img_err'] !== '' ? $_GET['img_err'] : 'an unknown error occurred while processing it';
			$body_raw = 'Your post was saved, but the image could not be uploaded: ' . $reason . '. Please try a different image.';
		}

		$bodies_text = str_replace("{SIZE}", ini_get('upload_max_filesize'), $body_raw);
		pjUtil::printNotice($title_text, $bodies_text);
	}
		
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionUpdate" method="post" id="frmUpdatePost" class="form pj-form" enctype="multipart/form-data">
		<input type="hidden" name="post_update" value="1" />
		<input type="hidden" name="locale" value="<?php echo (int) $tpl['content_locale']; ?>" />
		<?php
		// Text fields of a language that is not the default one may stay empty (the default text is shown instead)
		$is_default_content = (int) $tpl['content_locale'] === (int) $tpl['default_locale_id'];
		include PJ_VIEWS_PATH . 'pjLayouts/elements/langbar.php';
		?>
		<input type="hidden" name="id" value="<?php echo (int) $tpl['arr']['id']; ?>" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && !empty($_GET['tab_id']) ? $_GET['tab_id'] : 'tabs-1'; ?>" />
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('lblPost'); ?></a></li>
				<li><a href="#tabs-2"><?php __('lblSeo'); ?></a></li>
			</ul>
		
			<div id="tabs-1">
				<?php
				pjUtil::printNotice(__('infoUpdatePostTitle', true, false), __('infoUpdatePostBody', true, false)); 
				?>
				<div class="float_left w500">
					<p>
						<label class="title110"><?php __('lblCategory'); ?></label>
						<span class="inline_block">
							<?php
							if(!empty($tpl['category_arr']))
							{ 
								?>
								<select name="category_id" id="category_id" class="pj-form-field w300">
									<option value="0">-- <?php __('lblChoose'); ?>--</option>
									<?php
									foreach ($tpl['category_arr'] as $v)
									{
										// This post's own category is kept in the list even if it's
										// since been deactivated (see pjActionUpdate() in the
										// controller) so the field doesn't land on "-- Choose--" -
										// label it "(inactive)" so that's obvious at a glance instead
										// of looking like a normal, still-usable category.
										$categoryLabel = pjSanitize::html($v['category']);
										if (isset($v['status']) && $v['status'] !== 'T')
										{
											$categoryLabel .= ' (' . __('lblInActive', true) . ')';
										}
										?><option value="<?php echo $v['id']; ?>" <?php echo $v['id'] == $tpl['arr']['category_id'] ? 'selected="selected"' : null; ?>><?php echo $categoryLabel; ?></option><?php
									}
									?>
								</select>
								<?php
							}else{
								?><label class="block t5"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminCategories&amp;action=pjActionCreate"><?php __('lblAddCategory', false, true);?></a></label><?php
							} 
							?>
						</span>
					</p>
				</div>
				<div class="float_right">
					<p>
						<?php
						if((int) $tpl['arr']['cnt_comments'] > 0)
						{ 
							?>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminComments&amp;action=pjActionIndex&amp;post_id=<?php echo (int) $tpl['arr']['id']; ?>"><?php echo $tpl['arr']['cnt_comments'];?> <?php $tpl['arr']['cnt_comments'] != 1 ? __('lblPluralComments') : __('lblSingularComment');?></a>
							<?php
						} 
						?>
					</p>
				</div>
				<div class="clear_both"></div>
				<p>
					<label class="title110"><?php __('lblDatePublished'); ?></label>
					<span class="pj-form-field-custom pj-form-field-custom-after">
						<input type="text" name="published_date" id="published_date" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['arr']['published_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" />
						<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
					</span>
				</p>
				<?php
				if($controller->isAdmin())
				{ 
					?>
					<p>
						<label class="title110"><?php __('lblAuthor'); ?></label>
						<span class="inline_block">
							<select name="author_id" id="author_id" class="pj-form-field w300 required">
								<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
								foreach ($tpl['author_arr'] as $v)
								{
									?><option value="<?php echo $v['id']; ?>" <?php echo $v['id'] == $tpl['arr']['author_id'] ? 'selected="selected"' : null; ?>><?php echo pjSanitize::html($v['name']); ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<?php
				} 
				?>
				<p>
					<label class="title110"><?php __('lblTitle', false, true); ?></label>
					<span class="inline_block">
						<input type="text" name="title" id="title" class="pj-form-field<?php echo $is_default_content ? ' required' : NULL; ?> w400 float_left r10" maxlength="200" value="<?php echo pjSanitize::html($tpl['arr']['title']);?>" /><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input float_left r10"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
						<span class="post-rating">
							<select name="rate">
								<?php
								$avg = floor($tpl['arr']['avg_rate']);
								for ($j=1; $j<=5; $j++) {
									?>
									<option value="<?php echo $j;?>" <?php echo ($avg == $j) ? 'selected="selected"' : '';?>><?php echo $j;?></option>
									<?php
								}
								?>
							</select>
							<span id="star_message_<?php echo intval($tpl['arr']['id']);?>"><?php echo round((float)$tpl['arr']['avg_rate'], 2);?> <?php __('lblOf', false, true);?> <?php echo intval($tpl['arr']['cnt']);?> <?php __('lblVotes', false, true);?></span>
							<span class="clear"></span>
						</span>
						<a id="rating_reset" class="rating-reset" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionResetVote&amp;id=<?php echo $tpl['arr']['id']; ?>"><?php __('lblReset', false, true);?></a>
					</span>
				</p>
				<p>
					<label class="title110"><?php __('lblBody'); ?></label>
					<span class="inline_block">
						<textarea id="body" name="body" class="mceEditor" style="width: 600px; height: 250px"><?php echo !empty($tpl['arr']['body']) ? stripslashes($tpl['arr']['body']) : '';?></textarea><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title110"><?php __('lblImage', false, true); ?></label>
					<span class="inline_block">
						<span class="pj-image-uploader" id="image_uploader">
							<span class="pj-image-dropzone" id="image_dropzone" tabindex="0">
								<span class="pj-image-dropzone-empty" id="image_dropzone_empty">
									<span class="pj-image-dropzone-icon">&#8679;</span>
									<span class="pj-image-dropzone-text"><?php __('hintDropImage', false, true); ?></span>
								</span>
								<span class="pj-image-dropzone-preview" id="image_preview_wrap" style="display: none;">
									<img id="image_preview" src="" alt="" />
								</span>
							</span>
							<span class="pj-image-dropzone-error" id="image_dropzone_error" style="display: none;"></span>
							<input type="file" name="image" id="image" class="pj-image-file-input" accept="image/png,image/gif,image/jpeg" />
							<span class="pj-image-crop-actions" id="image_crop_actions" style="display: none;">
								<input type="button" value="<?php __('btnCropImage', false, true); ?>" class="pj-button" id="btnCropImage" />
								<input type="button" value="<?php __('btnRemoveImage', false, true); ?>" class="pj-button" id="btnRemoveImage" />
							</span>
						</span>
					</span>
				</p>
				<?php
				if(!empty($tpl['arr']['source_path'])){
					$thumb_url = PJ_INSTALL_URL . $tpl['arr']['thumb_path'];
					?>
					<p id="image_container">
						<label class="title110">&nbsp;</label>
						<span class="inline_block">
							<img src="<?php echo $thumb_url; ?>" />
							<a class="delete-image" href="javascript:void(0);" data-href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionDeleteImage&id=<?php echo $tpl['arr']['id'];?>"><?php __('lblDelete', false, true); ?></a>
						</span>
					</p>
					<?php
				} 
				?>
				<p>
					<label class="title110"><?php __('lblStatus'); ?></label>
					<span class="inline_block">
						<select name="status" id="status" class="pj-form-field required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach (__('u_statarr', true) as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['status'] ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title110">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminPosts&action=pjActionIndex';" />
				</p>
			</div>
			<div id="tabs-2">
				<?php
				pjUtil::printNotice(__('infoUpdateSEOTitle', true, false), __('infoUpdateSEOBody', true, false)); 
				?>
				<p>
					<label class="title"><?php __('lblMetaTitle'); ?></label>
					<span class="inline_block">
						<input type="text" id="meta_title" name="meta_title" class="pj-form-field w500" value="<?php echo pjSanitize::html($tpl['arr']['meta_title']); ?>"/><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblMetaKeywords'); ?></label>
					<span class="inline_block">
						<input type="text" id="meta_keywords" name="meta_keywords" class="pj-form-field w500" value="<?php echo pjSanitize::html($tpl['arr']['meta_keywords']); ?>"/><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblMetaDescription'); ?></label>
					<span class="inline_block">
						<textarea id="meta_description" name="meta_description" class="pj-form-field w500 h150"><?php echo pjSanitize::html($tpl['arr']['meta_description']); ?></textarea><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblUrlSlug'); ?></label>
					<span class="inline_block">
						<input type="text" id="url_slug" name="url_slug" class="pj-form-field w500" maxlength="200" value="<?php echo pjSanitize::html($tpl['arr']['url_slug']); ?>" data-slug-source="#title" <?php if ($is_default_content) : ?>data-slug-url="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminPosts&amp;action=pjActionCheckSlug"<?php endif; ?> data-slug-id="<?php echo (int) $tpl['arr']['id']; ?>" data-slug-autofill="<?php echo trim((string) @$tpl['arr']['url_slug']) === '' ? '1' : '0'; ?>" data-msg-remote="<?php __('lblPostSlugTaken', false, true); ?>" placeholder="<?php __('hintPostSlugEditable', false, true); ?>" /><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminPosts&action=pjActionIndex';" />
				</p>
			</div>
		</div>
	</form>
	<div id="dialogReset" style="display: none" title="<?php __('lblResetVotes');?>"><?php __('lblResetConfirmation');?></div>
	<div id="dialogDeleteImage" style="display: none" title="<?php __('lblDeleteImage');?>"><?php __('lblDeleteConfirmation');?></div>
	<div id="delete_url" style="display: none"></div>
	<div id="imageCropDialog" style="display: none;" title="<?php __('lblCropImage', false, true); ?>">
		<div class="pj-image-crop-stage">
			<img id="imageCropTarget" src="" alt="" />
		</div>
	</div>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('lblFieldRequired', false, true); ?>";
	myLabel.image_invalid_type = "<?php __('imageInvalidType', false, true); ?>";
	myLabel.btn_crop_confirm = "<?php __('btnCropImage', false, true); ?>";
	myLabel.btn_crop_cancel = "<?php __('btnCancel', false, true); ?>";
	</script>
	<?php
	if (isset($_GET['tab_id']) && !empty($_GET['tab_id']))
	{
		$tab_id = explode("-", $_GET['tab_id']);
		$tab_id = (int) $tab_id[1] - 1;
		$tab_id = $tab_id < 0 ? 0 : $tab_id;
		?>
		<script type="text/javascript">
		(function ($) {
			$(function () {
				$("#tabs").tabs("option", "selected", <?php echo $tab_id; ?>);
			});
		})(jQuery_1_8_2);
		</script>
		<?php
	}
}
?>