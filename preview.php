<?php
/* ==========================================================================
   PHP Blog Script - front-end preview (Admin -> Preview thumbnails).

   Shows the blog through the same <div> + <script> install code as
   Admin -> Install, so the preview behaves exactly like the blog on the
   client's website: posts open as preview.php?post=<url-slug>, categories
   as ?blog_category=<url-slug>, and the post / category SEO tags are
   written into this page's <head> (check Inspect -> Elements).

   &local=1 keeps every link on this preview page even when a Blog page URL
   is set in Admin -> Install. ?theme=themeN (from the Preview thumbnails)
   previews that colour theme. The page is marked noindex so search engines
   never index the preview copy of the blog.
   ========================================================================== */

// Language of the preview. Admin > Install > Language > Preview sends ?locale=<language ID>: it is kept
// in the session and the address is cleaned (preview.php?post=<url-slug>) - the language is never part of
// the visible URL. An empty value (default language) or a theme link (?theme=...) resets it.
// Each time a preview is opened from the admin panel (?locale= or a theme thumbnail) it gets a fresh
// preview ID. The blog script keeps the visitor's own language switch under that ID, so a language chosen
// inside one preview never carries over to the next preview.
@session_start();
if (array_key_exists('locale', $_GET))
{
	$_SESSION['pjBlogPreviewId'] = md5(uniqid((string) mt_rand(), true));
	$loc = (isset($_GET['locale']) && is_string($_GET['locale']) && ctype_digit($_GET['locale'])) ? $_GET['locale'] : '';
	if ($loc !== '') { $_SESSION['pjBlogPreviewLocale'] = $loc; } else { unset($_SESSION['pjBlogPreviewLocale']); }
	$rest = $_GET;
	unset($rest['locale']);
	session_write_close();
	header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . (empty($rest) ? '' : '?' . http_build_query($rest)));
	exit;
}
if (isset($_GET['theme']) && !isset($_GET['post']) && isset($_GET['new']))
{
	// A theme thumbnail was clicked in the admin panel (preview.php?theme=themeN&new=1): a new preview in the
	// default language. The address is cleaned; moving around inside that preview (language menu, links)
	// keeps ?theme=themeN without &new, so it stays the same preview.
	unset($_SESSION['pjBlogPreviewLocale']);
	$_SESSION['pjBlogPreviewId'] = md5(uniqid((string) mt_rand(), true));
	$rest = $_GET;
	unset($rest['new']);
	session_write_close();
	header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($rest));
	exit;
}
if (empty($_SESSION['pjBlogPreviewId'])) { $_SESSION['pjBlogPreviewId'] = md5(uniqid((string) mt_rand(), true)); }
$previewId = (string) $_SESSION['pjBlogPreviewId'];
$previewLocale = isset($_SESSION['pjBlogPreviewLocale']) ? (string) $_SESSION['pjBlogPreviewLocale'] : '';
session_write_close();

// This file sits in the blog script folder, so the script URL is this folder.
$scriptUrl = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http')
	. '://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$src = $scriptUrl . 'index.php?controller=pjEmbed&action=pjActionLoadJs&local=1&pv=' . $previewId;
if (isset($_GET['theme']) && preg_match('/^theme\d{1,2}$/', (string) $_GET['theme']))
{
	$src .= '&theme=' . $_GET['theme'];
}
if ($previewLocale !== '')
{
	// the install code with that language (&locale=<language ID>)
	$src .= '&locale=' . $previewLocale;
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title></title>
</head>
<body>
	<div id="pjBlogEmbed"></div>
	<script src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
