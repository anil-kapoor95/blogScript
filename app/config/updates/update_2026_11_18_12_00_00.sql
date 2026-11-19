START TRANSACTION;

-- Admin > Install: "Install config" with a language choice (the install code then opens the blog in it)
INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblInstallConfig', 'backend', 'Install / Install config', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblInstallConfig' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Install config', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblInstallConfigLocale', 'backend', 'Install / Language', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblInstallConfigLocale' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Language', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblInstallConfigLang', 'backend', 'Install / Language / Default option', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblInstallConfigLang' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', '-- Default language --', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblInstallConfigLangInfo', 'backend', 'Install / Language / Info', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblInstallConfigLangInfo' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'The language the blog opens in on the page where you paste the code. Visitors can still switch to any other language with the language menu.', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('btnPreviewInstall', 'backend', 'Install / Preview button', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'btnPreviewInstall' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Preview', 'script');

-- The admin keeps the labels in the login session and reloads them when this value changes
UPDATE `options` SET `value` = MD5(UUID()) WHERE `foreign_id` = 1 AND `key` = 'o_fields_index';

COMMIT;
