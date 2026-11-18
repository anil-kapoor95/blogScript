-- URL Slug (posts and categories) is now read-only: it is always generated
-- from the post title / category name and kept unique (-2, -3, ...).
-- New hint labels (INSERT IGNORE: an existing install keeps its texts) and
-- removal of the two old "leave blank to auto-generate" hints, which no
-- longer apply (only the named rows are deleted).

START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintPostSlugAuto','backend','Hint / Post URL slug (read-only)','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintPostSlugAuto' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Generated automatically from the title','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintCategorySlugAuto','backend','Hint / Category URL slug (read-only)','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintCategorySlugAuto' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Generated automatically from the category name','script');

DELETE FROM `multi_lang` WHERE `model` = 'pjField' AND `foreign_id` IN (
  SELECT `id` FROM `fields` WHERE `key` IN ('hintUrlSlug','hintCategoryUrlSlug')
);
DELETE FROM `fields` WHERE `key` IN ('hintUrlSlug','hintCategoryUrlSlug');

COMMIT;
