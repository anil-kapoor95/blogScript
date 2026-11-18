-- JS embed install code (Admin > Install): a plain <div> + <script> that works
-- in any CMS page editor / page builder, with no PHP include and no .htaccess.
-- Posts open on the same page as ?post=<url-slug> and the post's SEO tags are
-- written into that page.
-- INSERT IGNORE only: an existing install keeps its current texts/translations.
-- No tables are altered (posts.meta_keywords already exists).

START TRANSACTION;

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblEmbedInstallInfo','backend','Install / Embed info','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblEmbedInstallInfo' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Paste the code below into the page of your website where the blog should appear - any CMS page editor, page builder or plain HTML page. No PHP code and no .htaccess are needed. Use a page on the same website (domain) as the blog script, so comments, captcha and ratings work. When a visitor opens a post, it opens on that same page as ?post=post-url-slug, and the post''s SEO tags (title, description, keywords, canonical, Open Graph, Twitter and Article schema, from the post''s SEO tab) are written into that page, replacing the page''s own tags. The post list itself does not change the page''s SEO tags.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblEmbedInstallCode','backend','Install / Embed code heading','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblEmbedInstallCode' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Install code - paste it where the blog should appear','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblEmbedSeoNote','backend','Install / Embed SEO note','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblEmbedSeoNote' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','The post SEO tags are written by JavaScript, so check them in your browser''s Inspect > Elements (not View source / Ctrl+U). Google does read tags set by JavaScript, but they are not indexed as reliably as tags that are already in the page HTML.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnCopy','backend','Button / Copy','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnCopy' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Copy','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblCopied','backend','Label / Copied','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblCopied' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Copied','script');

COMMIT;
