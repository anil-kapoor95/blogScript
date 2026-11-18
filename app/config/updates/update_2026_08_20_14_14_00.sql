START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiWriter','backend','lblAiWriter','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiWriter' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','AI Writer','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiWriterHint','backend','aiWriterHint','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiWriterHint' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','Generate a draft title and post body from a topic using your configured AI provider. Always review before publishing.','script'),
  (@id,'pjField',3,'title','Generate a draft title and post body from a topic using your configured AI provider. Always review before publishing.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiTopic','backend','lblAiTopic','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiTopic' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','Topic or working title','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnGenerateAi','backend','btnGenerateAi','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnGenerateAi' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','Generate with AI','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgEnterTopic','backend','aiMsgEnterTopic','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgEnterTopic' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','Please enter a topic first.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgGenerating','backend','aiMsgGenerating','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgGenerating' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','Generating your draft...','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgGenerateFailed','backend','aiMsgGenerateFailed','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgGenerateFailed' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','The AI provider did not return any content. Please try again.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgNoKey','backend','aiMsgNoKey','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgNoKey' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','No API key is configured. Add one in AI Settings first.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgDisabled','backend','aiMsgDisabled','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgDisabled' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','AI Writer is disabled. Enable it in AI Settings first.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgGenerated','backend','aiMsgGenerated','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgGenerated' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','Draft generated. Review and edit before publishing.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgOverwriteConfirm','backend','aiMsgOverwriteConfirm','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgOverwriteConfirm' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',"::LOCALE::",'title','This will replace the current title and body. Continue?','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiWriterChooseTitle','backend','infoAiWriterChooseTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiWriterChooseTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','AI Writer','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiWriterChooseBody','backend','infoAiWriterChooseBody','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiWriterChooseBody' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Choose how you would like to start this post.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblWriteManually','backend','lblWriteManually','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblWriteManually' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Write manually','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiWriteManuallyHint','backend','aiWriteManuallyHint','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiWriteManuallyHint' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Start with a blank title and body.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblWriteWithAi','backend','lblWriteWithAi','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblWriteWithAi' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Write with AI','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiWizardHint','backend','aiWizardHint','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiWizardHint' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Describe the post and let AI draft a few options to choose from.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiWriterPromptTitle','backend','infoAiWriterPromptTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiWriterPromptTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Describe the post','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiWriterPromptBody','backend','infoAiWriterPromptBody','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiWriterPromptBody' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Give it a topic, and optionally a tone and length. AI will draft a few options to choose from.','script'),
  (@id,'pjField',3,'title','Give it a topic, and optionally a tone and length. AI will draft a few options to choose from.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiTone','backend','lblAiTone','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiTone' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Tone','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiToneProfessional','backend','aiToneProfessional','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiToneProfessional' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Professional','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiToneCasual','backend','aiToneCasual','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiToneCasual' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Casual','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiToneFriendly','backend','aiToneFriendly','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiToneFriendly' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Friendly','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiLength','backend','lblAiLength','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiLength' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Length','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiLengthShort','backend','aiLengthShort','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiLengthShort' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Short','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiLengthMedium','backend','aiLengthMedium','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiLengthMedium' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Medium','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiLengthLong','backend','aiLengthLong','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiLengthLong' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Long','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnGenerateAiOptions','backend','btnGenerateAiOptions','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnGenerateAiOptions' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Generate options','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiWriterReviewTitle','backend','infoAiWriterReviewTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiWriterReviewTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Choose a draft','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoAiWriterReviewBody','backend','infoAiWriterReviewBody','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoAiWriterReviewBody' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Pick the option you like best, then continue to the editor to review and publish.','script'),
  (@id,'pjField',3,'title','Pick the option you like best, then continue to the editor to review and publish.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiOption','backend','lblAiOption','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiOption' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Option','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiSeoTitle','backend','lblAiSeoTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiSeoTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','SEO title','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblAiSeoDescription','backend','lblAiSeoDescription','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblAiSeoDescription' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','SEO description','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnUseDraft','backend','btnUseDraft','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnUseDraft' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Use this draft','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnRegenerateOptions','backend','btnRegenerateOptions','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnRegenerateOptions' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Regenerate all','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnContinueToEditor','backend','btnContinueToEditor','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnContinueToEditor' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Continue to editor','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblSelected','backend','lblSelected','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblSelected' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Selected','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgOptionsFailed','backend','aiMsgOptionsFailed','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgOptionsFailed' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','The AI provider did not return any usable drafts. Please try again.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiMsgDraftLoaded','backend','aiMsgDraftLoaded','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiMsgDraftLoaded' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Draft loaded from AI Writer. Review before saving.','script');

COMMIT;