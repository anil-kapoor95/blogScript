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
	if (isset($_GET['saved']))
	{
		pjUtil::printNotice(__('lblAiSettings', true), __('infoAiSettingsSaved', true));
	}

	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/optmenu.php';

	pjUtil::printNotice(__('infoAiSettingsTitle', true), __('infoAiSettingsBody', true));

	$o = isset($tpl['option_arr']) && is_array($tpl['option_arr']) ? $tpl['option_arr'] : array();

	$ai_enabled  = isset($o['o_ai_enabled'])  && $o['o_ai_enabled'] === 'T' ? 'T' : 'F';
	$ai_provider = isset($o['o_ai_provider']) ? $o['o_ai_provider'] : 'openai';
	$ai_model    = isset($o['o_ai_model'])    ? $o['o_ai_model']   : '';
	$ai_api_key  = isset($o['o_ai_api_key'])  ? $o['o_ai_api_key'] : '';

	// --- Provider list (dynamic: __('ai_providers') built from ai_providers_ARRAY_* keys) ---
	$providers = __('ai_providers', true);
	if (!is_array($providers) || empty($providers))
	{
		$providers = array(
			'openai'    => 'OpenAI (ChatGPT)',
			'anthropic' => 'Anthropic (Claude)',
			'google'    => 'Google (Gemini)',
		);
	}
	if (!isset($providers[$ai_provider]))
	{
		$ai_provider = key($providers);
	}

	// --- Model lists per provider (dynamic: __('ai_models_openai') etc.) ---
	$modelFallback = array(
		'openai'    => array('gpt-4o', 'gpt-4o-mini', 'gpt-4.1', 'gpt-4.1-mini', 'gpt-4-turbo', 'o3-mini', 'gpt-3.5-turbo'),
		'anthropic' => array('claude-3-7-sonnet-20250219', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-opus-20240229', 'claude-3-sonnet-20240229', 'claude-3-haiku-20240307'),
		'google'    => array('gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-1.5-flash-8b'),
	);
	$models = array();
	foreach (array_keys($providers) as $pv)
	{
		$list = __('ai_models_' . $pv, true);
		if (is_array($list) && !empty($list))
		{
			$models[$pv] = $list;
		}
		else if (isset($modelFallback[$pv]))
		{
			$tmp = array();
			foreach ($modelFallback[$pv] as $mid) { $tmp[$mid] = $mid; }
			$models[$pv] = $tmp;
		}
		else
		{
			$models[$pv] = array();
		}
	}

	// --- Merge in custom model ids the admin has added via the "Add" button
	// below (persisted server-side in o_ai_custom_models), so they show up
	// as normal dropdown entries for every admin from now on, not just
	// whoever originally typed them in. ---
	$customModelsRaw = isset($o['o_ai_custom_models']) ? $o['o_ai_custom_models'] : '{}';
	$customModels = json_decode((string) $customModelsRaw, true);
	if (is_array($customModels))
	{
		foreach ($customModels as $pv => $ids)
		{
			if (!is_array($ids)) { continue; }
			if (!isset($models[$pv])) { $models[$pv] = array(); }
			foreach ($ids as $mid)
			{
				$mid = (string) $mid;
				if ($mid !== '' && !isset($models[$pv][$mid]))
				{
					$models[$pv][$mid] = $mid;
				}
			}
		}
	}
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form pj-form" id="frmAiSettings">
		<input type="hidden" name="options_update" value="1" />
		<input type="hidden" name="next_action" value="pjActionAiSettings" />

		<table class="pj-table" cellpadding="0" cellspacing="0" style="width: 100%">
			<thead>
				<tr>
					<th style="width: 260px;"><?php __('lblOption'); ?></th>
					<th><?php __('lblValue'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="pj-table-row-odd">
					<td><?php __('opt_o_ai_enabled'); ?></td>
					<td>
						<select name="value-string-o_ai_enabled" id="o_ai_enabled" class="pj-form-field">
							<option value="F"<?php echo $ai_enabled === 'F' ? ' selected="selected"' : ''; ?>><?php __('lblAiDisabled'); ?></option>
							<option value="T"<?php echo $ai_enabled === 'T' ? ' selected="selected"' : ''; ?>><?php __('lblAiEnabled'); ?></option>
						</select>
					</td>
				</tr>

				<tr class="pj-table-row-odd">
					<td><?php __('opt_o_ai_provider'); ?></td>
					<td>
						<select name="value-string-o_ai_provider" id="o_ai_provider" class="pj-form-field">
							<?php foreach ($providers as $pv => $plabel) { ?>
								<option value="<?php echo pjSanitize::html($pv); ?>"<?php echo $ai_provider === $pv ? ' selected="selected"' : ''; ?>><?php echo pjSanitize::html($plabel); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>

				<tr class="pj-table-row-odd">
					<td><?php __('opt_o_ai_model'); ?></td>
					<td>
						<select id="o_ai_model" class="pj-form-field">
							<?php if ($ai_model !== '') { ?>
								<option value="<?php echo pjSanitize::html($ai_model); ?>" selected="selected"><?php echo pjSanitize::html($ai_model); ?></option>
							<?php } ?>
						</select>
						<div id="o_ai_model_custom_wrap" style="display: none; margin-top: 6px;">
							<input type="text" id="o_ai_model_custom" class="pj-form-field w300" style="display: inline-block; width: 260px; vertical-align: middle;" maxlength="150" placeholder="<?php __('hintAiCustomModel', false, true); ?>" spellcheck="false" />
							<input type="button" id="o_ai_model_add_btn" value="<?php __('btnAiAddModel', false, true); ?>" class="pj-button" style="margin-left: 6px; vertical-align: middle;" />
							<span id="o_ai_model_add_status" style="display: block; margin-top: 4px; font-size: 12px;"></span>
						</div>
						<input type="hidden" name="value-string-o_ai_model" id="o_ai_model_value" value="<?php echo pjSanitize::html($ai_model); ?>" />
						<span style="display: block; margin-top: 6px; font-size: 12px; color: #888;"><?php __('hintAiModelList', false, true); ?></span>
					</td>
				</tr>

				<tr class="pj-table-row-odd">
					<td><?php __('opt_o_ai_api_key'); ?></td>
					<td>
						<input type="password" name="value-string-o_ai_api_key" id="o_ai_api_key" class="pj-form-field w300" value="<?php echo pjSanitize::html($ai_api_key); ?>" autocomplete="new-password" spellcheck="false" style="width: 380px;" />
					</td>
				</tr>
			</tbody>
		</table>

		<p style="margin-top: 12px;">
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
			<input type="button" id="btnVerifyAiKey" value="<?php __('btnVerifyAiKey', false, true); ?>" class="pj-button" />
		</p>

		<div id="aiVerifyResult" style="display: none; margin-top: 10px; padding: 10px 12px; border-radius: 3px; max-width: 640px;"></div>
	</form>

	<script type="text/javascript">
	var PJ_AI_MODELS = <?php echo pjAppController::jsonEncode($models); ?>;
	var pjAiLabels = {
		enterKey:       <?php echo pjAppController::jsonEncode(__('aiMsgEnterKey', true)); ?>,
		verifying:      <?php echo pjAppController::jsonEncode(__('aiMsgVerifying', true)); ?>,
		serverError:    <?php echo pjAppController::jsonEncode(__('aiMsgServerError', true)); ?>,
		unexpected:     <?php echo pjAppController::jsonEncode(__('aiMsgUnexpected', true)); ?>,
		customOption:   <?php echo pjAppController::jsonEncode(__('lblAiCustomModel', true)); ?>,
		enterModel:     <?php echo pjAppController::jsonEncode(__('aiMsgEnterModel', true)); ?>,
		addingModel:    <?php echo pjAppController::jsonEncode(__('aiMsgAddingModel', true)); ?>,
		modelAdded:     <?php echo pjAppController::jsonEncode(__('aiMsgModelAdded', true)); ?>,
		addModelFailed: <?php echo pjAppController::jsonEncode(__('aiMsgAddModelFailed', true)); ?>
	};

	(function () {
		"use strict";

		var CUSTOM_VALUE = "__custom__";

		var MODELS       = PJ_AI_MODELS || {};
		var providerEl   = document.getElementById("o_ai_provider");
		var modelEl      = document.getElementById("o_ai_model");
		var modelCustomEl = document.getElementById("o_ai_model_custom");
		var modelCustomWrap = document.getElementById("o_ai_model_custom_wrap");
		var modelAddBtn  = document.getElementById("o_ai_model_add_btn");
		var modelAddStatus = document.getElementById("o_ai_model_add_status");
		var modelValueEl = document.getElementById("o_ai_model_value");
		var keyEl        = document.getElementById("o_ai_api_key");
		var btnVerify    = document.getElementById("btnVerifyAiKey");
		var resultEl     = document.getElementById("aiVerifyResult");

		function pjReadCookie(name) {
			var m = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, "\\$1") + "=([^;]*)"));
			return m ? decodeURIComponent(m[1]) : "";
		}

		var savedModel = <?php echo json_encode($ai_model); ?>;

		function setAddStatus(text, ok) {
			if (!modelAddStatus) { return; }
			modelAddStatus.textContent = text || "";
			modelAddStatus.style.color = ok === false ? "#b3261e" : (ok === true ? "#1e6b34" : "#888");
		}

		function showCustomModelInput(prefill) {
			modelEl.value = CUSTOM_VALUE;
			modelCustomWrap.style.display = "block";
			if (prefill !== undefined) {
				modelCustomEl.value = prefill;
			}
			modelValueEl.value = modelCustomEl.value;
			setAddStatus("");
		}

		function hideCustomModelInput() {
			modelCustomWrap.style.display = "none";
			modelValueEl.value = modelEl.value;
		}

		/**
		 * Rebuilds the preset dropdown for the currently selected provider and
		 * appends a "Custom..." entry so the admin isn't limited to the
		 * hardcoded/known model list above (e.g. a newer model id this script
		 * doesn't know about yet, an org-specific fine-tune, etc.) - anything
		 * typed there is saved as-is, the server accepts any string here.
		 *
		 * keepValue=true (a provider switch): try to carry over whatever's
		 * currently active (preset or custom text) if it still applies.
		 * keepValue=false (initial load): fall back to the model saved in AI
		 * Settings. Either way, if that value isn't one of the known preset
		 * ids for this provider, it's treated as a custom model rather than
		 * silently dropped - previously an unrecognized saved value (typed in
		 * before this provider's preset list existed/changed) just fell
		 * through to the browser's default first-option selection with no
		 * indication anything had changed.
		 */
		function populateModels(keepValue) {
			var provider = providerEl.value;
			var map = MODELS[provider] || {};
			var ids = [];
			for (var id in map) { if (map.hasOwnProperty(id)) { ids.push(id); } }

			var wasCustom = modelEl.value === CUSTOM_VALUE;
			var current = keepValue ? (wasCustom ? modelCustomEl.value : modelEl.value) : "";

			modelEl.innerHTML = "";
			for (var i = 0; i < ids.length; i++) {
				var opt = document.createElement("option");
				opt.value = ids[i];
				opt.textContent = map[ids[i]];
				modelEl.appendChild(opt);
			}
			var customOpt = document.createElement("option");
			customOpt.value = CUSTOM_VALUE;
			customOpt.textContent = pjAiLabels.customOption || "Custom…";
			modelEl.appendChild(customOpt);

			var want = current || savedModel;
			if (want && ids.indexOf(want) !== -1) {
				modelEl.value = want;
				hideCustomModelInput();
			} else if (want) {
				showCustomModelInput(want);
			} else {
				modelCustomWrap.style.display = "none";
				modelCustomEl.value = "";
				if (ids.length > 0) {
					modelEl.value = ids[0];
				}
				modelValueEl.value = modelEl.value;
			}
			resetResult();
		}

		function resetResult() {
			resultEl.style.display = "none";
			resultEl.innerHTML = "";
		}

		function showResult(ok, text) {
			resultEl.style.display = "block";
			resultEl.style.background = ok ? "#e6f4ea" : "#fdecea";
			resultEl.style.color = ok ? "#1e6b34" : "#b3261e";
			resultEl.style.border = "1px solid " + (ok ? "#b7dfc4" : "#f5c2bd");
			resultEl.textContent = text;
		}

		function showPending(text) {
			resultEl.style.display = "block";
			resultEl.style.background = "#f4f4f4";
			resultEl.style.color = "#555";
			resultEl.style.border = "1px solid #ddd";
			resultEl.textContent = text;
		}

		providerEl.addEventListener("change", function () { populateModels(false); });

		modelEl.addEventListener("change", function () {
			if (modelEl.value === CUSTOM_VALUE) {
				showCustomModelInput(modelCustomEl.value);
				modelCustomEl.focus();
			} else {
				hideCustomModelInput();
			}
		});

		modelCustomEl.addEventListener("input", function () {
			modelValueEl.value = modelCustomEl.value;
		});

		/**
		 * Persists the typed model id server-side (pjActionAiAddModel) so it
		 * becomes a normal selectable option - for every admin, from now on -
		 * instead of a value that only lives in this one field until Save.
		 * Disabled for the duration of the request so it can't be double-clicked.
		 */
		modelAddBtn.addEventListener("click", function () {
			var val = (modelCustomEl.value || "").replace(/^\s+|\s+$/g, "");
			if (val === "") {
				setAddStatus(pjAiLabels.enterModel, false);
				modelCustomEl.focus();
				return;
			}

			modelAddBtn.disabled = true;
			setAddStatus(pjAiLabels.addingModel);

			var csrfToken = pjReadCookie("pj_csrf_token");
			var body = "provider=" + encodeURIComponent(providerEl.value) +
					   "&model=" + encodeURIComponent(val) +
					   "&csrf_token=" + encodeURIComponent(csrfToken);

			fetch("index.php?controller=pjAdminOptions&action=pjActionAiAddModel", {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
					"X-Requested-With": "XMLHttpRequest",
					"X-CSRF-Token": csrfToken
				},
				credentials: "same-origin",
				body: body
			})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				modelAddBtn.disabled = false;
				if (res && res.ok) {
					var provider = providerEl.value;
					MODELS[provider] = MODELS[provider] || {};
					MODELS[provider][val] = val;

					var opt = document.createElement("option");
					opt.value = val;
					opt.textContent = val;
					modelEl.insertBefore(opt, modelEl.lastChild);
					modelEl.value = val;
					hideCustomModelInput();
					setAddStatus(pjAiLabels.modelAdded, true);
				} else {
					setAddStatus((res && res.text) ? res.text : pjAiLabels.addModelFailed, false);
				}
			})
			.catch(function () {
				modelAddBtn.disabled = false;
				setAddStatus(pjAiLabels.serverError, false);
			});
		});

		/**
		 * Disabled for the duration of the request (re-enabled in both the
		 * success and failure paths below) so a fast double-click can't fire
		 * two overlapping verify requests against the provider's API.
		 */
		btnVerify.addEventListener("click", function () {
			var key = (keyEl.value || "").replace(/^\s+|\s+$/g, "");
			if (key === "") {
				showResult(false, pjAiLabels.enterKey);
				return;
			}
			showPending(pjAiLabels.verifying);
			btnVerify.disabled = true;

			var csrfToken = pjReadCookie("pj_csrf_token");

			var body = "provider=" + encodeURIComponent(providerEl.value) +
					   "&model=" + encodeURIComponent(modelValueEl.value) +
					   "&api_key=" + encodeURIComponent(key) +
					   "&csrf_token=" + encodeURIComponent(csrfToken);

			fetch("index.php?controller=pjAdminOptions&action=pjActionVerifyAiKey", {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
					"X-Requested-With": "XMLHttpRequest",
					"X-CSRF-Token": csrfToken
				},
				credentials: "same-origin",
				body: body
			})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				showResult(res && res.valid === true, (res && res.text) ? res.text : pjAiLabels.unexpected);
			})
			.catch(function () {
				showResult(false, pjAiLabels.serverError);
			})
			.then(function () {
				btnVerify.disabled = false;
			});
		});

		populateModels(false);
	})();
	</script>
	<?php
}
?>
