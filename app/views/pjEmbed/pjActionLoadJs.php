<?php
if (!defined('ROOT_PATH')) { header('HTTP/1.1 403 Forbidden'); exit; }
/**
 * Install-code script: streams the embed runtime and boots it. The runtime
 * reads the host page URL (?post=<slug>, ?blog_page=, ...) and loads the
 * matching content script (pjEmbed::pjActionContentJs).
 */
$bootCfg = array(
	'host'    => 'pjBlogEmbed',
	'content' => $tpl['contentUrl'],
	'post'    => isset($tpl['fixedPost']) ? $tpl['fixedPost'] : '',
	'theme'   => isset($tpl['previewTheme']) ? $tpl['previewTheme'] : '',
	'local'   => !empty($tpl['local']),
);
readfile(PJ_INSTALL_PATH . PJ_JS_PATH . 'pjBlogEmbed.js');
// Page on the same website: the content for its URL comes right here (see
// pjEmbed::pjPrefetchParams), before boot() so boot can use it.
if (!empty($tpl['prefetch']))
{
	include dirname(__FILE__) . '/pjActionContentJs.php';
}
echo "\n;window.PJBLOG && PJBLOG.boot(" . json_encode($bootCfg) . ");\n";
?>