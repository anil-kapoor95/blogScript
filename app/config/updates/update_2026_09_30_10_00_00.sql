START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('menuRequestCustomization','backend','Menu / Request customization','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='menuRequestCustomization' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Request customization','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiGenerateImage','backend','lblAiGenerateImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiGenerateImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Also generate a featured image','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgGeneratingImage','backend','aiMsgGeneratingImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgGeneratingImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Generating image…','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgImageFailed','backend','aiMsgImageFailed','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgImageFailed' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Couldn''t generate an image - you can still continue without one, or add one manually after.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgImageNoProvider','backend','aiMsgImageNoProvider','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgImageNoProvider' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Image generation isn''t available for the selected AI provider.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiCustomModel','backend','lblAiCustomModel','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiCustomModel' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Custom…','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintAiCustomModel','backend','hintAiCustomModel','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintAiCustomModel' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Enter the exact model ID, e.g. gpt-4o-2024-11-20','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintAiModelList','backend','hintAiModelList','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintAiModelList' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Don''t see a model you want to use? Choose "Custom…" and type its exact model ID.','script');

INSERT IGNORE INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
  (1, 'o_ai_custom_models', 55, '{}', NULL, 'string', 214, 0, NULL);

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnAiAddModel','backend','btnAiAddModel','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnAiAddModel' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Add','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgEnterModel','backend','aiMsgEnterModel','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgEnterModel' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Enter a model ID first.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgAddingModel','backend','aiMsgAddingModel','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgAddingModel' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Adding model…','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgModelAdded','backend','aiMsgModelAdded','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgModelAdded' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Model added and selected.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgAddModelFailed','backend','aiMsgAddModelFailed','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgAddModelFailed' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Couldn''t add that model - please try again.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnAiRegenerateImage','backend','btnAiRegenerateImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnAiRegenerateImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Regenerate image','script');

COMMIT;
