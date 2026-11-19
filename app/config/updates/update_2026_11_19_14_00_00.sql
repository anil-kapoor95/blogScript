START TRANSACTION;

-- Admin > Install: "Display Language Selector on Frontend" (default No)
INSERT IGNORE INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
(1, 'o_show_lang_selector', 99, 'Yes|No::No', NULL, 'enum', NULL, 0, NULL);

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('opt_o_show_lang_selector', 'backend', 'Options / Display language selector on frontend', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'opt_o_show_lang_selector' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Display Language Selector on Frontend', 'script');

-- The admin keeps the labels in the login session and reloads them when this value changes
UPDATE `options` SET `value` = MD5(UUID()) WHERE `foreign_id` = 1 AND `key` = 'o_fields_index';

COMMIT;
