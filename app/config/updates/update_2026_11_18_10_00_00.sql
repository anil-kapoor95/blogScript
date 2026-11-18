START TRANSACTION;

-- ---------------------------------------------------------------------
-- Multi-language blog content ("Add Language")
--
-- 1. Switch the multi-language feature on (the Languages tab of the Options
--    menu appears; the front end gets its language switcher).
-- 2. Copy the CURRENT category / post text into `multi_lang` for the default
--    language. The original columns of `categories` and `posts` are NOT
--    touched - they stay as the default-language copy and as the fallback
--    shown when a translation is missing, so nothing is lost or overwritten.
-- 3. Add the new text labels.
--
-- The script is safe to run more than once (INSERT IGNORE on the unique key
-- foreign_id + model + locale + field, and existing rows are never updated).
-- ---------------------------------------------------------------------

UPDATE `options` SET `value` = '1|0::1' WHERE `foreign_id` = 1 AND `key` = 'o_multi_lang' AND `value` = '1|0::0';

SET @default_locale := (SELECT `id` FROM `plugin_locale` WHERE `is_default` = 1 ORDER BY `id` LIMIT 1);
SET @default_locale := IFNULL(@default_locale, (SELECT MIN(`id`) FROM `plugin_locale`));

-- Categories
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjCategory', @default_locale, 'category', `category`, 'data' FROM `categories` WHERE @default_locale IS NOT NULL AND `category` IS NOT NULL AND `category` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjCategory', @default_locale, 'meta_title', `meta_title`, 'data' FROM `categories` WHERE @default_locale IS NOT NULL AND `meta_title` IS NOT NULL AND `meta_title` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjCategory', @default_locale, 'meta_keywords', `meta_keywords`, 'data' FROM `categories` WHERE @default_locale IS NOT NULL AND `meta_keywords` IS NOT NULL AND `meta_keywords` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjCategory', @default_locale, 'meta_description', `meta_description`, 'data' FROM `categories` WHERE @default_locale IS NOT NULL AND `meta_description` IS NOT NULL AND `meta_description` <> '';

-- Posts
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjPost', @default_locale, 'title', `title`, 'data' FROM `posts` WHERE @default_locale IS NOT NULL AND `title` IS NOT NULL AND `title` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjPost', @default_locale, 'body', `body`, 'data' FROM `posts` WHERE @default_locale IS NOT NULL AND `body` IS NOT NULL AND `body` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjPost', @default_locale, 'meta_title', `meta_title`, 'data' FROM `posts` WHERE @default_locale IS NOT NULL AND `meta_title` IS NOT NULL AND `meta_title` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjPost', @default_locale, 'meta_keywords', `meta_keywords`, 'data' FROM `posts` WHERE @default_locale IS NOT NULL AND `meta_keywords` IS NOT NULL AND `meta_keywords` <> '';

INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `id`, 'pjPost', @default_locale, 'meta_description', `meta_description`, 'data' FROM `posts` WHERE @default_locale IS NOT NULL AND `meta_description` IS NOT NULL AND `meta_description` <> '';

-- (URL slugs: the original `url_slug` column stays the default-language slug; only the other
-- languages keep their own slug in multi_lang.)

-- Site-wide SEO defaults (Options > SEO)
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT `foreign_id`, 'pjOption', @default_locale, `key`, `value`, 'data' FROM `options` WHERE @default_locale IS NOT NULL AND `foreign_id` = 1 AND `key` IN ('o_meta_title', 'o_meta_keywords', 'o_meta_description') AND `value` IS NOT NULL AND `value` <> '';

-- Text labels
INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblContentLanguage', 'backend', 'Label / Content language', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblContentLanguage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Content language', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblContentLanguageHint', 'backend', 'Label / Content language hint', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblContentLanguageHint' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Title, content and SEO fields are saved separately for each language. Category, author, date, status and image are shared by all languages. Leave a translation empty to show the default-language text instead.', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblNoTranslationYet', 'backend', 'Label / No translation yet', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblNoTranslationYet' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'This language has no translation yet. The default-language text is shown on the website until you save one.', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblLeaveUnsaved', 'backend', 'Label / Leave with unsaved changes', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblLeaveUnsaved' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'You have unsaved changes. Switching language will discard them. Continue?', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('lblDefaultLanguage', 'backend', 'Label / Default language', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'lblDefaultLanguage' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'default', 'script');

INSERT IGNORE INTO `fields` (`key`, `type`, `label`, `source`) VALUES ('front_language', 'frontend', 'Front / Language', 'script');
SET @id := (SELECT `id` FROM `fields` WHERE `key` = 'front_language' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`) VALUES
  (@id, 'pjField', '::LOCALE::', 'title', 'Language', 'script');

COMMIT;
