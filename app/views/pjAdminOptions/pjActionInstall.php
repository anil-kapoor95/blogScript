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
	// Install code: a plain <div> + <script> pair that pastes into any CMS page
	// editor / page builder (no PHP include, no .htaccess). Posts open on the
	// same page as ?post=<url-slug>; the post's SEO tags are written by the
	// embed (see app/web/js/pjBlogEmbed.js).
	$embedCode = '<div id="pjBlogEmbed"></div>' . "\n"
		. '<script src="' . PJ_INSTALL_URL . 'index.php?controller=pjEmbed&action=pjActionLoadJs"></script>';
	?>
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1"><?php __('menuInstall'); ?></a></li>
		</ul>
		<div id="tabs-1">
			<?php
			$blogPage = '';
			foreach ($tpl['o_arr'] as $item)
			{
				if ($item['key'] == 'o_blog_page')
				{
					$blogPage = (string) $item['value'];
					break;
				}
			}
			pjUtil::printNotice(NULL, __('lblEmbedBlogPageInfo', true), false, false);
			?>
			<form id="frmInstall" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form pj-form b20">
				<input type="hidden" name="options_update" value="1" />
				<input type="hidden" name="next_action" value="pjActionInstall" />
				<p>
					<label class="float_left w320 pt5"><?php __('opt_o_blog_page'); ?></label>
					<span class="inline_block">
						<span class="pj-form-field-custom pj-form-field-custom-before float_left">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-url"></abbr></span>
							<input type="text" name="value-string-o_blog_page" class="pj-form-field w250 validateURL" value="<?php echo pjSanitize::html($blogPage); ?>" placeholder="https://yoursite.com/blog" data-msg-validateURL="<?php echo pjSanitize::html(__('lblEnterValidURL', true)); ?>"/>
						</span>
						<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button float_left l5 align_middle" />
					</span>
				</p>
			</form>
			<?php pjUtil::printNotice(NULL, __('lblEmbedInstallInfo', true), false, false); ?>
			<p class="pj-embed-label"><?php __('lblEmbedInstallCode'); ?></p>
			<textarea id="pjEmbedCode" class="pj-form-field w700 textarea_install pj-embed-code" readonly="readonly"><?php echo pjSanitize::html($embedCode); ?></textarea>
			<p class="pj-embed-actions">
				<button type="button" class="pj-button pj-embed-copy" data-target="pjEmbedCode" data-done="<?php echo pjSanitize::html(__('lblCopied', true)); ?>"><?php __('btnCopy'); ?></button>
			</p>
			<?php pjUtil::printNotice(NULL, __('lblEmbedSeoNote', true), false, false); ?>
		</div>
	</div>
	<?php
}
?>