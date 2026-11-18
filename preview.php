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

// This file sits in the blog script folder, so the script URL is this folder.
$scriptUrl = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http')
	. '://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$src = $scriptUrl . 'index.php?controller=pjEmbed&action=pjActionLoadJs&local=1';
if (isset($_GET['theme']) && preg_match('/^theme\d{1,2}$/', (string) $_GET['theme']))
{
	$src .= '&theme=' . $_GET['theme'];
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
