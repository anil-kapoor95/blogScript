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
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionCreate" method="post" id="frmCreatePost" class="form pj-form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="post_create" value="1" />
		<input type="hidden" name="locale" value="<?php echo (int) $tpl['content_locale']; ?>" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && in_array($_GET['tab_id'], array('tabs-1', 'tabs-2')) ? $_GET['tab_id'] : 'tabs-1'; ?>" />
		<?php
		// Text fields of a language that is not the default one may stay empty (the default text is shown instead)
		$is_default_content = (int) $tpl['content_locale'] === (int) $tpl['default_locale_id'];
		?>
		<div id="aiDraftLoadedNotice" style="display: none; margin-bottom: 12px; padding: 8px 10px; border-radius: 3px; background: #e6f4ea; color: #1e6b34; border: 1px solid #b7dfc4;"><?php __('aiMsgDraftLoaded'); ?></div>
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('lblPost'); ?></a></li>
				<li><a href="#tabs-2"><?php __('lblSeo'); ?></a></li>
			</ul>

			<div id="tabs-1">
				<?php
				pjUtil::printNotice(__('infoAddPostTitle', true, false), __('infoAddPostBody', true, false));
				?>
				<?php $langbar_tab = 'tabs-1'; include PJ_VIEWS_PATH . 'pjLayouts/elements/langbar.php'; ?>
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
									?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['category']); ?></option><?php
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
									?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['name']); ?></option><?php
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
						<input type="text" name="title" id="title" class="pj-form-field<?php echo $is_default_content ? ' required' : NULL; ?> w400" maxlength="200"/><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title110"><?php __('lblBody'); ?></label>
					<span class="inline_block">
						<span class="pj-body-wrap"><textarea id="body" name="body" class="mceEditor" style="width: 570px; height: 250px"></textarea><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?></span>
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
				<p>
					<label class="title110"><?php __('lblStatus'); ?></label>
					<span class="inline_block">
						<select name="status" id="status" class="pj-form-field required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach (__('u_statarr', true) as $k => $v)
							{
								?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
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
				<?php $langbar_tab = 'tabs-2'; include PJ_VIEWS_PATH . 'pjLayouts/elements/langbar.php'; ?>
				<p>
					<label class="title"><?php __('lblMetaTitle'); ?></label>
					<span class="inline_block">
						<input type="text" name="meta_title" id="meta_title" class="pj-form-field w500" maxlength="200" /><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblMetaKeywords'); ?></label>
					<span class="inline_block">
						<input type="text" name="meta_keywords" id="meta_keywords" class="pj-form-field w500" /><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblMetaDescription'); ?></label>
					<span class="inline_block">
						<textarea name="meta_description" id="meta_description" class="pj-form-field w500 h150"></textarea><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblUrlSlug'); ?></label>
					<span class="inline_block">
						<input type="text" name="url_slug" id="url_slug" class="pj-form-field w500" maxlength="200" data-slug-source="#title" <?php if ($is_default_content) : ?>data-slug-url="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminPosts&amp;action=pjActionCheckSlug"<?php endif; ?> data-slug-id="0" data-slug-autofill="1" data-msg-remote="<?php __('lblPostSlugTaken', false, true); ?>" placeholder="<?php __('hintPostSlugEditable', false, true); ?>" /><?php if (!empty($content_flag)) : ?><span class="pj-multilang-input"><img src="<?php echo pjSanitize::html($content_flag); ?>" alt="" /></span><?php endif; ?>
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

	/* Pick up a draft handed off from the AI Writer wizard (pjActionAiWriter),
	   if the author arrived here via "Continue to editor".
	   Deferred to document-ready (via the same jQuery_1_8_2 alias
	   pjAdminPosts.js sets up) rather than running immediately: this inline
	   script runs synchronously as the page parses, which is BEFORE
	   pjAdminPosts.js's own $(function(){...}) block has fired and wired up
	   the "Modern image uploader"'s change listener on #image. Dispatching
	   the synthetic change event before that listener exists meant the
	   event fired into nothing - the file was technically assigned to the
	   input, but no preview/crop ever appeared. Running after ready (and
	   after pjAdminPosts.js's own ready handler, since that one was
	   registered first and jQuery fires them in registration order)
	   guarantees the uploader is already listening. */
	jQuery_1_8_2(function ($) {
		if (!window.sessionStorage) {
			return;
		}
		var raw = sessionStorage.getItem("pjAiDraft");
		if (!raw) {
			return;
		}
		sessionStorage.removeItem("pjAiDraft");

		var draft;
		try {
			draft = JSON.parse(raw);
		} catch (e) {
			return;
		}
		if (!draft) {
			return;
		}

		var titleEl = document.getElementById("title"),
			bodyEl = document.getElementById("body"),
			metaTitleEl = document.getElementById("meta_title"),
			metaDescEl = document.getElementById("meta_description"),
			notice = document.getElementById("aiDraftLoadedNotice");

		if (titleEl && draft.title) { titleEl.value = draft.title; }
		if (bodyEl && draft.body) { bodyEl.value = draft.body; }
		if (metaTitleEl && draft.meta_title) { metaTitleEl.value = draft.meta_title; }
		if (metaDescEl && draft.meta_description) { metaDescEl.value = draft.meta_description; }
		if (notice) { notice.style.display = "block"; }

		// The textarea's value above is enough if TinyMCE hasn't initialized
		// yet (it reads the textarea on init). If it already has, push the
		// content into the live editor instance too.
		if (draft.body && window.tinymce) {
			var applyToEditor = function () {
				var ed = tinymce.get("body");
				if (ed) { ed.setContent(draft.body); }
			};
			if (tinymce.get("body")) {
				applyToEditor();
			} else if (tinymce.on) {
				tinymce.on("AddEditor", function (e) {
					if (e.editor && e.editor.id === "body") {
						e.editor.on("init", applyToEditor);
					}
				});
			}
		}

		// Hand the AI-generated image (if any) to the existing image
		// uploader (in pjAdminPosts.js) as though the author had picked or
		// dropped it themselves, so the normal upload/crop/resize/save
		// pipeline handles it completely unchanged - nothing below knows or
		// cares that the file didn't come from disk.
		if (draft.image_base64 && window.DataTransfer && window.atob) {
			try {
				var mime = draft.image_mime || "image/png",
					ext = (mime.indexOf("png") !== -1) ? "png" : "jpg",
					binary = window.atob(draft.image_base64),
					len = binary.length,
					bytes = new Uint8Array(len);
				for (var bi = 0; bi < len; bi++) { bytes[bi] = binary.charCodeAt(bi); }

				var aiBlob = new Blob([bytes], { type: mime }),
					aiFile;
				try {
					aiFile = new File([aiBlob], "ai-generated." + ext, { type: mime });
				} catch (fileCtorErr) {
					/* Older browsers without a File constructor - the uploader
					   only reads .type/.name off it, a named Blob works too. */
					aiFile = aiBlob;
					aiFile.name = "ai-generated." + ext;
				}

				var imageInput = document.getElementById("image");
				if (imageInput) {
					var dt = new DataTransfer();
					dt.items.add(aiFile);
					imageInput.files = dt.files;

					// The uploader listens for "change" on #image to run its
					// own preview/crop handling - dispatch one instead of
					// duplicating that logic here.
					var changeEvt;
					if (typeof Event === "function") {
						changeEvt = new Event("change", { bubbles: true });
					} else {
						changeEvt = document.createEvent("Event");
						changeEvt.initEvent("change", true, true);
					}
					imageInput.dispatchEvent(changeEvt);
				}
			} catch (imgErr) {
				/* Corrupt/oversized base64, or the browser is missing one of
				   the APIs above - fall through silently. The text draft
				   above is already applied either way, and the author can
				   still upload an image manually. */
			}
		}
	});
	</script>
	<?php
}
?>
