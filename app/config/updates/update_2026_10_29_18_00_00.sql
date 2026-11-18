-- URL Slug (posts and categories) is editable again: it is filled in from
-- the title / category name once and the user can change it. A slug already
-- used by another post / category is shown as an error on the same page
-- (nothing typed is lost) and is not saved.
-- New labels use INSERT IGNORE (an existing install keeps its texts). The two
-- read-only hints ("Generated automatically ...") no longer apply and are
-- removed (only the named rows are deleted).

START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintPostSlugEditable','backend','Hint / Post URL slug (editable)','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintPostSlugEditable' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Filled in from the title - you can change it','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintCategorySlugEditable','backend','Hint / Category URL slug (editable)','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintCategorySlugEditable' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Filled in from the category name - you can change it','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintPostSlugExists','backend','Hint / Post URL slug already used','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintPostSlugExists' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','This URL slug is already used by another post. Please enter a different one.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintCategorySlugExists','backend','Hint / Category URL slug already used','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintCategorySlugExists' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','This URL slug is already used by another category. Please enter a different one.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_titles_ARRAY_AP12','arrays','error_titles_ARRAY_AP12','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_titles_ARRAY_AP12' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','URL slug not saved','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_bodies_ARRAY_AP12','arrays','error_bodies_ARRAY_AP12','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_bodies_ARRAY_AP12' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','The URL slug you entered is already used by another post, so it was not saved. All your other changes were saved. Please enter a different slug.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_titles_ARRAY_ACT10','arrays','error_titles_ARRAY_ACT10','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_titles_ARRAY_ACT10' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','URL slug not saved','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_bodies_ARRAY_ACT10','arrays','error_bodies_ARRAY_ACT10','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_bodies_ARRAY_ACT10' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','The URL slug you entered is already used by another category, so it was not saved. All your other changes were saved. Please enter a different slug.','script');

DELETE FROM `multi_lang` WHERE `model` = 'pjField' AND `foreign_id` IN (
  SELECT `id` FROM `fields` WHERE `key` IN ('hintPostSlugAuto','hintCategorySlugAuto')
);
DELETE FROM `fields` WHERE `key` IN ('hintPostSlugAuto','hintCategorySlugAuto');

COMMIT;
