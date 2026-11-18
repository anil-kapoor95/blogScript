<!doctype html>
<html>
	<head>
		<title>Php Blog Script by PHPJabbers.com</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<?php
		// Cache-bust local CSS/JS by file modified time + size (size covers deploys that normalise mtimes) (see pjActionAdmin.php).
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
			return is_file($file) ? '?v=' . filemtime($file) . '-' . filesize($file) : '';
		};
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : PJ_INSTALL_URL).$css['path'].$css['file'].$assetVer($css).'" />';
		}
		
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].$js['file'].$assetVer($js).'"></script>';
		}
		?>
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
				<div id="login-content">
				<?php require $content_tpl; ?>
				</div>
			</div> <!-- middle -->
		</div> <!-- container -->
		<div id="footer-wrap">
			<div id="footer">
			   	<p>Copyright &copy; <?php echo date("Y"); ?> <a href="https://www.PHPJabbers.com" target="_blank">PHPJabbers.com</a></p>
	        </div>
        </div>
	</body>
</html>