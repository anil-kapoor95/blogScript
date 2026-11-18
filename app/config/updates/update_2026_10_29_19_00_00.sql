-- Clear "URL slug already used" message under the URL Slug field (posts and
-- categories), shown on the same page while typing / on Save.
-- 1) New labels with a plain, actionable text (INSERT IGNORE: an existing
--    install keeps its texts). They replace hintPostSlugExists /
--    hintCategorySlugExists, which are removed (only the named rows).
-- 2) o_fields_index gets a new value: the admin keeps the labels in the login
--    session and reloads them only when this value changes - without it the
--    new labels stay empty for a logged-in user and the field shows the
--    generic "Please fix this field." instead of the message.

START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblPostSlugTaken','backend','Label / Post URL slug already used by another post','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblPostSlugTaken' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Duplicate URL slug: another post already uses this slug. Each post needs its own slug - please change it (for example add a word or a number) and save again.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblCategorySlugTaken','backend','Label / Category URL slug already used by another category','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblCategorySlugTaken' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Duplicate URL slug: another category already uses this slug. Each category needs its own slug - please change it (for example add a word or a number) and save again.','script');

DELETE FROM `multi_lang` WHERE `model` = 'pjField' AND `foreign_id` IN (
  SELECT `id` FROM `fields` WHERE `key` IN ('hintPostSlugExists','hintCategorySlugExists')
);
DELETE FROM `fields` WHERE `key` IN ('hintPostSlugExists','hintCategorySlugExists');

UPDATE `options` SET `value` = MD5(UUID()) WHERE `foreign_id` = 1 AND `key` = 'o_fields_index';

COMMIT;
