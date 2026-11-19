START TRANSACTION;

-- Label translations stored as only "?" characters (e.g. Yes/No) get the English (language 1) text.
UPDATE `multi_lang` m JOIN (SELECT `foreign_id`, `model`, `field`, `content` FROM `multi_lang` WHERE `locale` = 1 AND `content` NOT REGEXP '^[?]+$') e ON e.`foreign_id` = m.`foreign_id` AND e.`model` = m.`model` AND e.`field` = m.`field` SET m.`content` = e.`content` WHERE m.`model` = 'pjField' AND m.`field` = 'title' AND m.`locale` <> 1 AND m.`content` REGEXP '^[?]+$';

UPDATE `options` SET `value` = MD5(UUID()) WHERE `key` = 'o_fields_index';

COMMIT;
