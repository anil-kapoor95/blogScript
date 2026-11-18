<!doctype html>
<html>
	<head>
		<title>Php Blog Script by PHPJabbers.com</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<?php
		//Local CSS/JS get "?v=<file modified time>" appended so a browser
		// that cached an older copy re-fetches it as soon as the file changes
		// (otherwise admin.css/pjAdminPosts.js updates only showed up after a
		// hard refresh). Remote files and missing files are left untouched.
		$assetVer = function ($asset) {
			if (isset($asset['remote']) && $asset['remote'])
			{
				return '';
			}
			// PJ_ASSET_NO_CACHE (app/config/options.inc.php): stamp every file with the
			// current time in milliseconds so it is re-downloaded on every page view.
			if (defined('PJ_ASSET_NO_CACHE') && PJ_ASSET_NO_CACHE)
			{
				return '?v=' . round(microtime(true) * 1000);
			}
			$file = ROOT_PATH . $asset['path'] . $asset['file'];
			return is_file($file) ? '?v=' . filemtime($file) : '';
		};
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : PJ_INSTALL_URL).$css['path'].htmlspecialchars($css['file']).$assetVer($css).'" />';
		}
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].htmlspecialchars($js['file']).$assetVer($css).'"></script>';
		}
		?>
		<!--[if gte IE 9]>
  		<style type="text/css">.gradient {filter: none}</style>
		<![endif]-->
	</head>
	<body>
		<div id="container">
    		<div id="header">
    			<div id="logo">
    				<a href="https://www.phpjabbers.com/stiva-blog-script/" target="_blank" rel="nofollow">PHP Blog Script</a>
					<span>v<?php echo PJ_SCRIPT_VERSION;?></span>
    			</div>
			</div>
			
			<div id="middle">
				<div id="leftmenu">
					<?php require PJ_VIEWS_PATH . 'pjLayouts/elements/leftmenu.php'; ?>
				</div>
				<div id="right">
					<div class="content-top"></div>
					<div class="content-middle" id="content">
					<?php require $content_tpl; ?>
					</div>
					<div class="content-bottom"></div>
				</div> <!-- content -->
				<div class="clear_both"></div>
			</div> <!-- middle -->
		
		</div> <!-- container -->
		<div id="footer-wrap">
			<div id="footer">
			   	<p>Copyright &copy; <?php echo date("Y"); ?> <a href="https://www.PHPJabbers.com" target="_blank">PHPJabbers.com</a></p>
	        </div>
        </div>
	</body>
</html>