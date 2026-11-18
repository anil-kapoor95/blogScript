START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_titles_ARRAY_AG01','arrays','error_titles_ARRAY_AG01','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_titles_ARRAY_AG01' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Security check failed','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_bodies_ARRAY_AG01','arrays','error_bodies_ARRAY_AG01','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_bodies_ARRAY_AG01' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Your security token was missing or invalid. Please try again.','script');

  -- ==========================================================================
-- AI Writer - Step 1: AI Settings (option rows + backend label translations)
-- Follows the same style as blog_csrf_migration.sql.
-- Run once in phpMyAdmin against the BlogDev database. Idempotent.
-- ==========================================================================

-- 1) Configuration rows (hidden options read/written by the AI Settings tab)
INSERT INTO `options`(`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
	(1, 'o_ai_enabled',  55, 'F',      NULL, 'string', 210, 0, NULL),
	(1, 'o_ai_provider', 55, 'openai', NULL, 'string', 211, 0, NULL),
	(1, 'o_ai_model',    55, 'gpt-4o', NULL, 'string', 212, 0, NULL),
	(1, 'o_ai_api_key',  55, '',       NULL, 'string', 213, 0, NULL);

-- 2) Backend labels (translations for locales 1, 2, 3)
INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoEmailSettingsTitle','backend','infoEmailSettingsTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoEmailSettingsTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Email Settings','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoEmailSettingsBody','backend','infoEmailSettingsBody','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoEmailSettingsBody' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Below are the email settings you can configure to control how your blog sends outgoing email.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiSettingsTitle','backend','infoAiSettingsTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiSettingsTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI Settings','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiSettingsBody','backend','infoAiSettingsBody','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiSettingsBody' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',2,'title','Below are the AI Writer settings — choose the AI provider and model version, add your API key, and verify it before saving.','script'),
  (@id,'pjField','::LOCALE::','title','Below are the AI Writer settings — choose the AI provider and model version, add your API key, and verify it before saving.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('menuAiSettings','backend','menuAiSettings','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='menuAiSettings' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI Settings','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiSettings','backend','lblAiSettings','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiSettings' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI Settings','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiSettingsSaved','backend','infoAiSettingsSaved','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiSettingsSaved' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Your AI settings have been saved.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_ai_enabled','backend','opt_o_ai_enabled','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_ai_enabled' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI Writer','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_ai_provider','backend','opt_o_ai_provider','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_ai_provider' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI model','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_ai_model','backend','opt_o_ai_model','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_ai_model' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI version','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_ai_api_key','backend','opt_o_ai_api_key','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_ai_api_key' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','API key','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiEnabled','backend','lblAiEnabled','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiEnabled' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Enabled','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiDisabled','backend','lblAiDisabled','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiDisabled' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Disabled','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnVerifyAiKey','backend','btnVerifyAiKey','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnVerifyAiKey' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Verify key','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgEnterKey','backend','aiMsgEnterKey','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgEnterKey' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please enter an API key first.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgVerifying','backend','aiMsgVerifying','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgVerifying' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Verifying...','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgServerError','backend','aiMsgServerError','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgServerError' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Could not reach the server to verify the key.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgUnexpected','backend','aiMsgUnexpected','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgUnexpected' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Unexpected response.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyValid','backend','aiVerifyValid','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyValid' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','API key is valid and working.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyRejected','backend','aiVerifyRejected','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyRejected' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','API key was rejected by the provider. Please check the key.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyRateLimited','backend','aiVerifyRateLimited','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyRateLimited' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','The key is recognised but rate-limited or out of quota.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyConnFail','backend','aiVerifyConnFail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyConnFail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Could not connect to the provider.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyUnexpected','backend','aiVerifyUnexpected','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyUnexpected' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Unexpected response from the provider.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyNoProvider','backend','aiVerifyNoProvider','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyNoProvider' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please choose an AI provider.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyNoCurl','backend','aiVerifyNoCurl','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyNoCurl' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Cannot verify: PHP cURL is not enabled on this server.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyForbidden','backend','aiVerifyForbidden','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyForbidden' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Forbidden.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiVerifyInvalidReq','backend','aiVerifyInvalidReq','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiVerifyInvalidReq' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Invalid request.','script');

-- 3) Dynamic dropdown lists: providers ( __('ai_providers') ) and
--    per-provider models ( __('ai_models_openai') etc. ), built from *_ARRAY_* keys.

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_providers_ARRAY_openai','arrays','ai_providers_ARRAY_openai','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_providers_ARRAY_openai' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','OpenAI (ChatGPT)','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_providers_ARRAY_anthropic','arrays','ai_providers_ARRAY_anthropic','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_providers_ARRAY_anthropic' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Anthropic (Claude)','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_providers_ARRAY_google','arrays','ai_providers_ARRAY_google','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_providers_ARRAY_google' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Google (Gemini)','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_gpt-4o','arrays','ai_models_openai_ARRAY_gpt-4o','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_gpt-4o' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gpt-4o','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_gpt-4o-mini','arrays','ai_models_openai_ARRAY_gpt-4o-mini','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_gpt-4o-mini' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gpt-4o-mini','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_gpt-4.1','arrays','ai_models_openai_ARRAY_gpt-4.1','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_gpt-4.1' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gpt-4.1','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_gpt-4.1-mini','arrays','ai_models_openai_ARRAY_gpt-4.1-mini','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_gpt-4.1-mini' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gpt-4.1-mini','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_gpt-4-turbo','arrays','ai_models_openai_ARRAY_gpt-4-turbo','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_gpt-4-turbo' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gpt-4-turbo','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_o3-mini','arrays','ai_models_openai_ARRAY_o3-mini','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_o3-mini' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','o3-mini','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_openai_ARRAY_gpt-3.5-turbo','arrays','ai_models_openai_ARRAY_gpt-3.5-turbo','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_openai_ARRAY_gpt-3.5-turbo' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gpt-3.5-turbo','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_anthropic_ARRAY_claude-3-7-sonnet-20250219','arrays','ai_models_anthropic_ARRAY_claude-3-7-sonnet-20250219','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_anthropic_ARRAY_claude-3-7-sonnet-20250219' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','claude-3-7-sonnet-20250219','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_anthropic_ARRAY_claude-3-5-sonnet-20241022','arrays','ai_models_anthropic_ARRAY_claude-3-5-sonnet-20241022','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_anthropic_ARRAY_claude-3-5-sonnet-20241022' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','claude-3-5-sonnet-20241022','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_anthropic_ARRAY_claude-3-5-haiku-20241022','arrays','ai_models_anthropic_ARRAY_claude-3-5-haiku-20241022','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_anthropic_ARRAY_claude-3-5-haiku-20241022' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','claude-3-5-haiku-20241022','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_anthropic_ARRAY_claude-3-opus-20240229','arrays','ai_models_anthropic_ARRAY_claude-3-opus-20240229','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_anthropic_ARRAY_claude-3-opus-20240229' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','claude-3-opus-20240229','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_anthropic_ARRAY_claude-3-sonnet-20240229','arrays','ai_models_anthropic_ARRAY_claude-3-sonnet-20240229','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_anthropic_ARRAY_claude-3-sonnet-20240229' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','claude-3-sonnet-20240229','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_anthropic_ARRAY_claude-3-haiku-20240307','arrays','ai_models_anthropic_ARRAY_claude-3-haiku-20240307','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_anthropic_ARRAY_claude-3-haiku-20240307' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','claude-3-haiku-20240307','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-2.0-flash','arrays','ai_models_google_ARRAY_gemini-2.0-flash','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-2.0-flash' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-2.0-flash','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-1.5-pro','arrays','ai_models_google_ARRAY_gemini-1.5-pro','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-1.5-pro' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-1.5-pro','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-1.5-flash','arrays','ai_models_google_ARRAY_gemini-1.5-flash','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-1.5-flash' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-1.5-flash','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-1.5-flash-8b','arrays','ai_models_google_ARRAY_gemini-1.5-flash-8b','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-1.5-flash-8b' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-1.5-flash-8b','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-3.5-flash','arrays','ai_models_google_ARRAY_gemini-3.5-flash','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-3.5-flash' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-3.5-flash','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-2.5-flash','arrays','ai_models_google_ARRAY_gemini-2.5-flash','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-2.5-flash' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-2.5-flash','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('ai_models_google_ARRAY_gemini-3.5-flash','arrays','ai_models_google_ARRAY_gemini-3.5-flash','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='ai_models_google_ARRAY_gemini-3.5-flash' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','gemini-3.5-flash','script');

COMMIT;
