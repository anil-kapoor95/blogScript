START TRANSACTION;

ALTER TABLE `posts` ADD COLUMN `url_slug` VARCHAR(255) NULL DEFAULT NULL AFTER `meta_description`;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblUrlSlug','backend','lblUrlSlug','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblUrlSlug' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','URL Slug','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintUrlSlug','backend','hintUrlSlug','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintUrlSlug' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField',::LOCALE::,'title','Leave blank to auto-generate from the title, e.g. my-post-title','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintDropImage','backend','hintDropImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintDropImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Drag and drop an image here, or click to browse','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnCropImage','backend','btnCropImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnCropImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Crop','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnRemoveImage','backend','btnRemoveImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnRemoveImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Remove','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblCropImage','backend','lblCropImage','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblCropImage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Crop Image','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('imageInvalidType','backend','imageInvalidType','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='imageInvalidType' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please choose a PNG, GIF or JPEG image.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiLengthHint','backend','aiLengthHint','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiLengthHint' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Approximately {N} characters','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('aiOptionChars','backend','aiOptionChars','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='aiOptionChars' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','{N} characters','script');

INSERT INTO `fields` VALUES (NULL, 'option_themes_ARRAY_11', 'arrays', 'option_themes_ARRAY_11', 'script', NULL);
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Theme 11', 'script');

INSERT INTO `fields` VALUES (NULL, 'option_themes_ARRAY_12', 'arrays', 'option_themes_ARRAY_12', 'script', NULL);
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Theme 12', 'script');

UPDATE `options`
SET `value` = 'Yes|No::Yes'
WHERE `key` = 'o_seo_url';

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('front_post_not_found','frontend','front_post_not_found','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='front_post_not_found' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Post not found.','script');
  
  INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_titles_ARRAY_ACT09','arrays','error_titles_ARRAY_ACT09','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_titles_ARRAY_ACT09' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Category name required','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_bodies_ARRAY_ACT09','arrays','error_bodies_ARRAY_ACT09','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_bodies_ARRAY_ACT09' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please enter a name for the category - it cannot be empty or just spaces.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_titles_ARRAY_AC11','arrays','error_titles_ARRAY_AC11','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_titles_ARRAY_AC11' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Comment cannot be blank','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_bodies_ARRAY_AC11','arrays','error_bodies_ARRAY_AC11','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_bodies_ARRAY_AC11' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Name, email, and comment cannot be empty or just spaces.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('admin_author_in_use','backend','admin_author_in_use','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='admin_author_in_use' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','This author still has posts assigned to them. Reassign or delete those posts first, then try again.','script');

  INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('admin_category_in_use','backend','admin_category_in_use','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='admin_category_in_use' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','This category still has posts assigned to it. Move or delete those posts first, then try again.','script');

COMMIT;