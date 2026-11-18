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

	$ai_enabled = isset($tpl['option_arr']['o_ai_enabled']) && $tpl['option_arr']['o_ai_enabled'] === 'T';
	?>
	<p class="b10">
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionIndex">&laquo; <?php __('tabPosts'); ?></a>
	</p>

	<div id="aiw-step1">
		<?php pjUtil::printNotice(__('infoAiWriterChooseTitle', true, false), __('infoAiWriterChooseBody', true, false)); ?>
		<div class="pj-ai-wizard-choices">
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionCreate" class="pj-ai-wizard-choice">
				<span class="pj-ai-wizard-choice-title"><?php __('lblWriteManually'); ?></span>
				<span class="pj-ai-wizard-choice-hint"><?php __('aiWriteManuallyHint'); ?></span>
			</a>
			<a href="#" id="aiw-pick-ai" class="pj-ai-wizard-choice pj-ai-wizard-choice-ai<?php echo $ai_enabled ? '' : ' pj-ai-wizard-choice-disabled'; ?>">
				<span class="pj-ai-wizard-choice-title">&#10024; <?php __('lblWriteWithAi'); ?></span>
				<span class="pj-ai-wizard-choice-hint"><?php echo $ai_enabled ? __('aiWizardHint', true, false) : __('aiMsgDisabled', true, false); ?></span>
			</a>
		</div>
	</div>

	<div id="aiw-step2" style="display: none;">
		<?php pjUtil::printNotice(__('infoAiWriterPromptTitle', true, false), __('infoAiWriterPromptBody', true, false)); ?>
		<form class="form pj-form" onsubmit="return false;">
			<p>
				<label class="title110"><?php __('lblAiTopic'); ?></label>
				<span class="inline_block">
					<textarea id="aiw_topic" class="pj-form-field w400" rows="2" maxlength="300"></textarea>
				</span>
			</p>
			<p>
				<label class="title110"><?php __('lblAiTone'); ?></label>
				<span class="inline_block">
					<select id="aiw_tone" class="pj-form-field w300">
						<option value="professional"><?php __('aiToneProfessional'); ?></option>
						<option value="casual" selected="selected"><?php __('aiToneCasual'); ?></option>
						<option value="friendly"><?php __('aiToneFriendly'); ?></option>
					</select>
				</span>
			</p>
			<p>
				<label class="title110"><?php __('lblAiLength'); ?></label>
				<span class="inline_block">
					<select id="aiw_length" class="pj-form-field w300">
						<option value="short"><?php __('aiLengthShort'); ?></option>
						<option value="medium" selected="selected"><?php __('aiLengthMedium'); ?></option>
						<option value="long"><?php __('aiLengthLong'); ?></option>
					</select>
					<span id="aiw_length_hint" class="pj-ai-wizard-length-hint"></span>
				</span>
			</p>
			<p>
				<label class="title110">&nbsp;</label>
				<span class="inline_block">
					<label class="pj-ai-wizard-image-toggle">
						<input type="checkbox" id="aiw_generate_image" />
						<?php __('lblAiGenerateImage'); ?>
					</label>
				</span>
			</p>
			<p>
				<label class="title110">&nbsp;</label>
				<input type="button" id="aiw-back-1" value="<?php __('btnBack', false, true); ?>" class="pj-button pj-button-secondary" />
				<input type="button" id="aiw-generate" value="<?php __('btnGenerateAiOptions', false, true); ?>" class="pj-button" />
			</p>
		</form>
	</div>

	<div id="aiw-step3" style="display: none;">
		<?php pjUtil::printNotice(__('infoAiWriterReviewTitle', true, false), __('infoAiWriterReviewBody', true, false)); ?>
		<div id="aiw-image-wrap" class="pj-ai-wizard-image-wrap" style="display: none;">
			<img id="aiw-image-preview" class="pj-ai-wizard-image-preview" src="" alt="<?php __('lblAiGenerateImage', false, true); ?>" style="display: none;" />
			<div id="aiw-image-status" class="pj-ai-wizard-image-status" style="display: none;"></div>
			<input type="button" id="aiw-regenerate-image" value="<?php __('btnAiRegenerateImage', false, true); ?>" class="pj-button pj-button-secondary pj-ai-wizard-image-regenerate-btn" />
		</div>
		<div id="aiw-options" class="pj-ai-wizard-options"></div>
		<p class="t10">
			<input type="button" id="aiw-back-2" value="<?php __('btnBack', false, true); ?>" class="pj-button pj-button-secondary" />
			<input type="button" id="aiw-regenerate" value="<?php __('btnRegenerateOptions', false, true); ?>" class="pj-button pj-button-secondary" />
			<input type="button" id="aiw-continue" value="<?php __('btnContinueToEditor', false, true); ?>" class="pj-button" disabled="disabled" />
		</p>
	</div>

	<!-- #aiw-loading/#aiw-error live outside steps 2 and 3 (rather than nested
	     inside #aiw-step2) so they're visible no matter which step is
	     currently shown - "Regenerate all" on step 3 used to call the same
	     loading/error code as step 2's "Generate", but step 2 (and these two
	     divs along with it) was hidden at that point, so the wizard just
	     went blank with no feedback while a new set of options was pending
	     or if generation failed. -->
	<div id="aiw-loading" style="display: none;" class="t5"><?php __('aiMsgGenerating'); ?></div>
	<div id="aiw-error" style="display: none;" class="t5 pj-ai-wizard-error"></div>

	<style type="text/css">
	.pj-ai-wizard-choices { display: flex; gap: 14px; flex-wrap: wrap; }
	.pj-ai-wizard-choice { display: block; width: 260px; padding: 16px; border: 1px solid #ddd; border-radius: 4px; background: #fff; text-decoration: none; color: inherit; }
	.pj-ai-wizard-choice:hover { border-color: #bbb; }
	.pj-ai-wizard-choice-title { display: block; font-weight: bold; margin-bottom: 6px; }
	.pj-ai-wizard-choice-hint { display: block; color: #777; font-size: 12px; }
	.pj-ai-wizard-choice-ai { border-color: #b7d3ec; background: #f5f9fd; }
	.pj-ai-wizard-choice-disabled { opacity: .55; cursor: default; pointer-events: none; }
	.pj-ai-wizard-error { color: #b3261e; background: #fdecea; border: 1px solid #f5c2bd; padding: 8px 10px; border-radius: 3px; max-width: 640px; }
	.pj-ai-wizard-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
	.pj-ai-wizard-option { border: 1px solid #ddd; border-radius: 4px; padding: 12px 14px; background: #fff; }
	.pj-ai-wizard-option.aiw-selected { border-color: #4a9d5f; box-shadow: 0 0 0 2px rgba(74,157,95,.25); }
	.pj-ai-wizard-option.aiw-dimmed { opacity: .5; }
	.pj-ai-wizard-option-label { display: block; font-size: 11px; text-transform: uppercase; color: #888; margin-bottom: 4px; }
	.pj-ai-wizard-option-title { font-weight: bold; margin-bottom: 6px; }
	.pj-ai-wizard-option-body { color: #555; font-size: 12px; max-height: 90px; overflow: hidden; margin-bottom: 8px; }
	.pj-ai-wizard-length-hint { display: inline-block; margin-left: 10px; font-size: 12px; color: #888; }
	.pj-ai-wizard-image-toggle { font-weight: normal; cursor: pointer; }
	.pj-ai-wizard-image-toggle input { margin-right: 6px; vertical-align: middle; }
	.pj-ai-wizard-image-wrap { margin-bottom: 12px; }
	.pj-ai-wizard-image-preview { max-width: 100%; max-height: 260px; display: block; border: 1px solid #ddd; border-radius: 4px; }
	.pj-ai-wizard-image-status { font-size: 12px; color: #888; padding: 8px 0; }
	.pj-ai-wizard-image-regenerate-btn { margin-top: 6px; }
	.pj-ai-wizard-option-charcount { font-size: 11px; color: #999; font-style: italic; margin-bottom: 8px; }
	.pj-ai-wizard-option-seo { font-size: 11px; color: #888; border-top: 1px dashed #ddd; padding-top: 6px; margin-bottom: 8px; }
	.pj-ai-wizard-option-selected-badge { display: none; font-size: 11px; font-weight: bold; color: #2e7d43; }
	.pj-ai-wizard-option.aiw-selected .pj-ai-wizard-option-selected-badge { display: inline; }
	.pj-ai-wizard-option.aiw-selected .pj-ai-wizard-option-pick { display: none; }
	</style>

	<script type="text/javascript">
	var pjAiWizardLabels = {
		enterTopic:     <?php echo pjAppController::jsonEncode(__('aiMsgEnterTopic', true)); ?>,
		generating:     <?php echo pjAppController::jsonEncode(__('aiMsgGenerating', true)); ?>,
		serverError:    <?php echo pjAppController::jsonEncode(__('aiMsgServerError', true)); ?>,
		unexpected:     <?php echo pjAppController::jsonEncode(__('aiMsgUnexpected', true)); ?>,
		optionsFailed:  <?php echo pjAppController::jsonEncode(__('aiMsgOptionsFailed', true)); ?>,
		option:         <?php echo pjAppController::jsonEncode(__('lblAiOption', true)); ?>,
		seoTitle:       <?php echo pjAppController::jsonEncode(__('lblAiSeoTitle', true)); ?>,
		seoDescription: <?php echo pjAppController::jsonEncode(__('lblAiSeoDescription', true)); ?>,
		useThisDraft:   <?php echo pjAppController::jsonEncode(__('btnUseDraft', true)); ?>,
		selected:       <?php echo pjAppController::jsonEncode(__('lblSelected', true)); ?>,
		lengthHint:     <?php echo pjAppController::jsonEncode(__('aiLengthHint', true)); ?>,
		optionChars:    <?php echo pjAppController::jsonEncode(__('aiOptionChars', true)); ?>,
		generatingImage: <?php echo pjAppController::jsonEncode(__('aiMsgGeneratingImage', true)); ?>,
		imageFailed:    <?php echo pjAppController::jsonEncode(__('aiMsgImageFailed', true)); ?>
	};
	</script>
	<?php
}
?>
