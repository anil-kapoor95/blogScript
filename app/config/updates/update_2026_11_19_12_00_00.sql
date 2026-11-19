START TRANSACTION;

-- AI Writer: shows the language the draft is written in
INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblAiWriteLanguage', 'backend', 'AI Writer / Language the AI writes in', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblAiWriteLanguage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Write in', 'script');

-- The admin keeps the labels in the login session and reloads them when this value changes
UPDATE `options` SET `value` = MD5(UUID()) WHERE `foreign_id` = 1 AND `key` = 'o_fields_index';

COMMIT;
