-- JS embed, part 2:
--  1) Category URL slug: categories open on the blog page as
--     ?blog_category=<url-slug> (readable URL); admin gets a "URL Slug" field.
--  2) Blog page URL (Admin > Install): the existing o_blog_page option is
--     reused - when set, post / category links, canonical URLs and the
--     sitemap use that page. New info label only.
--  3) The PHP include install method and the .htaccess ".html" SEO URLs are
--     removed from the script: drop the o_seo_url option and the labels that
--     only that old method used.
-- New labels use INSERT IGNORE: an existing install keeps its texts.
-- The DELETEs below remove only the named, no-longer-used rows.

START TRANSACTION;

-- 1) Category URL slug ------------------------------------------------------
ALTER TABLE `categories` ADD COLUMN `url_slug` VARCHAR(255) NULL DEFAULT NULL AFTER `meta_description`;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('hintCategoryUrlSlug','backend','Hint / Category URL slug','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='hintCategoryUrlSlug' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Leave blank to auto-generate from the category name, e.g. travel-tips','script');

-- 2) Blog page URL (Admin > Install) -----------------------------------------
INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblEmbedBlogPageInfo','backend','Install / Blog page URL info','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblEmbedBlogPageInfo' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Optional. Enter the full URL of the page on your website where you paste the install code below, for example https://yoursite.com/blog. Every post and category link then opens on that page (https://yoursite.com/blog?post=post-url-slug), its canonical and Open Graph URL point there, and the sitemap (blog folder /sitemap.xml) lists your posts and categories with these URLs - so each post keeps one URL even if the install code also sits on other pages. Leave it blank to open posts on whichever page the code is placed (the sitemap then stays empty).','script');

-- 3) Remove the PHP include / .html SEO URL leftovers ------------------------
DELETE FROM `options` WHERE `foreign_id` = 1 AND `key` = 'o_seo_url';

DELETE FROM `multi_lang` WHERE `model` = 'pjField' AND `foreign_id` IN (
  SELECT `id` FROM `fields` WHERE `key` IN ('opt_o_seo_url','tabSEOSettings','lblInstallPhp1Title','lblInstallPhp1_1','lblInstallPhp1_2','lblInstallPhp1_2a','lblInstallPhp1_3','lblInstallPhp1_4','lblInstallPhp1_5','lblInstallPhp2Title')
);
DELETE FROM `fields` WHERE `key` IN ('opt_o_seo_url','tabSEOSettings','lblInstallPhp1Title','lblInstallPhp1_1','lblInstallPhp1_2','lblInstallPhp1_2a','lblInstallPhp1_3','lblInstallPhp1_4','lblInstallPhp1_5','lblInstallPhp2Title');

COMMIT;
