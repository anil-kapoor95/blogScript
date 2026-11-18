@ -1,32 +1,38 @@
<!doctype html>
<html>
	<head>
		<title></title>
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
		?>
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
	<?php
	require $content_tpl;
	foreach ($controller->getJs() as $js)
	{
		echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].$js['file'].$assetVer($js).'"></script>';
	}
	?>
	</body>
</html>