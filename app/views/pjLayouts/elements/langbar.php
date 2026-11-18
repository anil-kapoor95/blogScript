<?php
/**
 * Language bar of the edit forms that keep one language per page load (blog posts, SEO settings):
 * one flag per language, each a link to this same page with &locale=<ID>.
 *
 * Expects $tpl['lp_arr'] (languages with 'flag_url') and $tpl['content_locale'] (the language being edited).
 * Does nothing when there is only one language. Include it inside each tab panel (below the info notice) and
 * set $langbar_tab to that panel's id so the page reopens on the same tab after the language switch.
 */
if (isset($tpl['lp_arr']) && count($tpl['lp_arr']) > 1 && isset($tpl['option_arr']['o_multi_lang']) && (int) $tpl['option_arr']['o_multi_lang'] === 1)
{
	$content_flag = '';
	$bar_params = $_GET;
	unset($bar_params['locale'], $bar_params['err'], $bar_params['img_err']);
	if (!empty($langbar_tab)) { $bar_params['tab_id'] = $langbar_tab; }
	?>
	<div class="pj-content-langbar pj-form-langbar b10">
		<a href="#" class="pj-form-langbar-tip" title="<?php echo pjSanitize::html(__('lblContentLanguageHint', true)); ?>" onclick="return false;"></a>
		<?php
		foreach ($tpl['lp_arr'] as $v)
		{
			$bar_params['locale'] = (int) $v['id'];
			$is_active = (int) $v['id'] === (int) $tpl['content_locale'];
			if ($is_active) { $content_flag = $v['flag_url']; }
			?>
			<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . pjSanitize::html(http_build_query($bar_params)); ?>" class="pj-form-langbar-item<?php echo $is_active ? ' pj-form-langbar-item-active' : NULL; ?>" data-index="<?php echo (int) $v['id']; ?>" hreflang="<?php echo pjSanitize::html($v['language_iso']); ?>" title="<?php echo pjSanitize::html($v['name'] . ((int) $v['is_default'] === 1 ? ' - ' . __('lblDefaultLanguage', true) : '')); ?>"><abbr style="background-image: url(<?php echo pjSanitize::html($v['flag_url']); ?>)"></abbr></a>
			<?php
		}
		?>
	</div>
	<div class="clear_both"></div>
	<?php
	if (!empty($tpl['translation_missing']))
	{
		?><p class="pj-langbar-notice"><?php __('lblNoTranslationYet'); ?></p><?php
	}
	?>
	<?php if (empty($GLOBALS['pj_langbar_js'])) : $GLOBALS['pj_langbar_js'] = true; ?>
	<script type="text/javascript">
	(function () {
		// Switching language reloads the page: warn first when the form has unsaved changes.
		var started = false, initial = '';
		function snapshot() {
			var form = document.querySelector('form.pj-form');
			if (!form) { return ''; }
			if (window.tinymce && tinymce.triggerSave) { try { tinymce.triggerSave(); } catch (e) {} }
			var parts = [], els = form.elements, i;
			for (i = 0; i < els.length; i++) {
				if (els[i].name && els[i].type !== 'file' && els[i].type !== 'submit' && els[i].type !== 'button') {
					parts.push(els[i].name + '=' + ((els[i].type === 'checkbox' || els[i].type === 'radio') ? (els[i].checked ? els[i].value : '') : els[i].value));
				}
			}
			return parts.join('&');
		}
		window.addEventListener('load', function () { setTimeout(function () { initial = snapshot(); started = true; }, 1500); });
		document.addEventListener('click', function (e) {
			var a = e.target.closest ? e.target.closest('.pj-content-langbar a.pj-form-langbar-item') : null;
			if (a && started && snapshot() !== initial && !window.confirm(<?php echo json_encode(__('lblLeaveUnsaved', true)); ?>)) {
				e.preventDefault();
			}
		});
	})();
	</script>
	<?php endif; ?>
	<?php
}
?>
