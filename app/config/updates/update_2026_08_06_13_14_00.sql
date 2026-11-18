-- Stiva Blog: Email Settings (SMTP) + Google reCAPTCHA migration
-- Run once in phpMyAdmin against the blog database. Idempotent (INSERT IGNORE / conditional UPDATE).

START TRANSACTION;

-- 1) New option rows -------------------------------------------------------
INSERT IGNORE INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
(1, 'o_smtp_secure', 1, '|ssl|tls::', 'None|SSL|TLS', 'enum', 25, 0, NULL),
(1, 'o_smtp_auth', 1, 'LOGIN|PLAIN|CRAM-MD5|XOAUTH2::LOGIN', 'LOGIN|PLAIN|CRAM-MD5|XOAUTH2', 'enum', 26, 0, NULL),
(1, 'o_from_email', 1, NULL, NULL, 'string', 27, 0, NULL),
(1, 'o_from_name', 1, NULL, NULL, 'string', 28, 0, NULL),
(1, 'o_captcha_provider', 1, 'phpjabbers|recaptcha::phpjabbers', 'PHPJabbers Image Captcha|Google reCAPTCHA', 'enum', 29, 1, NULL),
(1, 'o_recaptcha_site_key', 1, NULL, NULL, 'string', 30, 1, NULL),
(1, 'o_recaptcha_secret_key', 1, NULL, NULL, 'string', 31, 1, NULL);

-- 2) Move the email-transport options onto the dedicated Email Settings page
UPDATE `options` SET `is_visible` = 0
WHERE `foreign_id` = 1 AND `key` IN ('o_send_email','o_smtp_host','o_smtp_port','o_smtp_user','o_smtp_pass','o_from_email','o_from_name');

-- 3) Interface labels (English seeded for locales 1,2,3; translate later in Locales) 
INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('menuEmailSettings','backend','menuEmailSettings','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='menuEmailSettings' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Email Settings','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblEmailSettings','backend','lblEmailSettings','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblEmailSettings' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Email Settings','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('infoEmailSettingsSaved','backend','infoEmailSettingsSaved','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='infoEmailSettingsSaved' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Your email settings have been saved.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_smtp_secure','backend','opt_o_smtp_secure','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_smtp_secure' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','SMTP security','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_smtp_auth','backend','opt_o_smtp_auth','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_smtp_auth' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','SMTP authentication','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_from_email','backend','opt_o_from_email','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_from_email' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Email account for notifications','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_from_name','backend','opt_o_from_name','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_from_name' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Sender name','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_captcha_provider','backend','opt_o_captcha_provider','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_captcha_provider' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Captcha type','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_recaptcha_site_key','backend','opt_o_recaptcha_site_key','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_recaptcha_site_key' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','reCAPTCHA site key','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('opt_o_recaptcha_secret_key','backend','opt_o_recaptcha_secret_key','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='opt_o_recaptcha_secret_key' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','reCAPTCHA secret key','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblNone','backend','lblNone','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblNone' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','None','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnTestConnection','backend','btnTestConnection','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnTestConnection' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Test connection','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnSendTestEmail','backend','btnSendTestEmail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnSendTestEmail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Send test email','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnSendEmail','backend','btnSendEmail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnSendEmail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Send Email','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('btnCancel','backend','btnCancel','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='btnCancel' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Cancel','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgEnterEmail','backend','emailMsgEnterEmail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgEnterEmail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please enter an email address.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgEnterValidEmail','backend','emailMsgEnterValidEmail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgEnterValidEmail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please enter a valid email address.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgEnterHostPort','backend','emailMsgEnterHostPort','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgEnterHostPort' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please enter the SMTP host and port first.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgTesting','backend','emailMsgTesting','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgTesting' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Testing SMTP connection, please wait...','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgSending','backend','emailMsgSending','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgSending' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Sending test email, please wait...','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgConnOk','backend','emailMsgConnOk','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgConnOk' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Connection successful - your SMTP settings are correct.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgConnFail','backend','emailMsgConnFail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgConnFail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Connection failed. Please check your SMTP settings.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgSentOk','backend','emailMsgSentOk','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgSentOk' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','A test email has been sent to','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgCheckInbox','backend','emailMsgCheckInbox','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgCheckInbox' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please check the inbox (and the spam folder).','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgSendFail','backend','emailMsgSendFail','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgSendFail' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','The test email could not be sent. Please verify your settings.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgUnexpected','backend','emailMsgUnexpected','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgUnexpected' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','An unexpected error occurred.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailMsgPrompt','backend','emailMsgPrompt','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailMsgPrompt' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Enter the email address to send the test email to:','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailTestModalTitle','backend','emailTestModalTitle','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailTestModalTitle' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Send Test Email','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('emailTestModalDesc','backend','emailTestModalDesc','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='emailTestModalDesc' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Enter your email address and a test message will be sent to it to verify that the system is able to send emails.','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('lblEmailAddress','backend','lblEmailAddress','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='lblEmailAddress' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Email Address:','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_titles_ARRAY_AO10','arrays','error_titles_ARRAY_AO10','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_titles_ARRAY_AO10' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Missing reCAPTCHA keys','script');

INSERT IGNORE INTO `fields` (`key`,`type`,`label`,`source`) VALUES ('error_bodies_ARRAY_AO10','arrays','error_bodies_ARRAY_AO10','script');
SET @id := (SELECT `id` FROM `fields` WHERE `key`='error_bodies_ARRAY_AO10' LIMIT 1);
INSERT IGNORE INTO `multi_lang` (`foreign_id`,`model`,`locale`,`field`,`content`,`source`) VALUES
  (@id,'pjField','::LOCALE::','title','Please enter both the reCAPTCHA site key and secret key when Google reCAPTCHA is selected.','script');

COMMIT;
