<?php
// Cache-busting for the scripts/styles loaded dynamically by this file and by pjLoad.js
// (they bypass the layout, so the layout's version stamp does not reach them).
// PJ_ASSET_NO_CACHE (app/config/options.inc.php): millisecond stamp on every load,
// otherwise a token made from file modified time + size.
$assetStamp = round(microtime(true) * 1000);
$assetVer = function ($file) use ($assetStamp) {
	if (defined('PJ_ASSET_NO_CACHE') && PJ_ASSET_NO_CACHE)
	{
		return '?v=' . $assetStamp;
	}
	return is_file($file) ? '?v=' . filemtime($file) . '-' . filesize($file) : '';
};
?>
<script type="text/javascript">
var pjQ = pjQ || {},
	Blog;
(function () {
	"use strict";
	var loadRemote = function(url, type, callback) {
		var _element, _type, _attr, scr, s, element;
		
		switch (type) {
		case 'css':
			_element = "link";
			_type = "text/css";
			_attr = "href";
			break;
		case 'js':
			_element = "script";
			_type = "text/javascript";
			_attr = "src";
			break;
		}
		
		scr = document.getElementsByTagName(_element);
		s = scr[scr.length - 1];
		element = document.createElement(_element);
		element.type = _type;
		if (type == "css") {
			element.rel = "stylesheet";
		}
		if (element.readyState) {
			element.onreadystatechange = function () {
				if (element.readyState == "loaded" || element.readyState == "complete") {
					element.onreadystatechange = null;
					callback();
				}
			};
		} else {
			element.onload = function () {
				callback();
			};
		}
		element[_attr] = url;
		s.parentNode.insertBefore(element, s.nextSibling);
	},
	loadScript = function (url, callback) {
		loadRemote(url, "js", callback);
	},
	loadCss = function (url, callback) {
		loadRemote(url, "css", callback);
	};

	var STBObj = {
		server: "<?php echo PJ_INSTALL_URL; ?>",
		folder: "<?php echo PJ_INSTALL_FOLDER; ?>",
		index: <?php echo $index; ?>,
		transitions_ver: "<?php echo $assetVer(PJ_INSTALL_PATH . 'app/web/css/transitions.css'); ?>",
				
		already_voted: "<?php __('label_already_voted', false, true);?>",
		thank_vote: "<?php __('label_thank_vote', false, true);?>",
		required_field: "<?php __('label_required_field', false, true); ?>",
		invalid_email: "<?php __('label_invalid_email', false, true); ?>",
		captcha_incorrect: "<?php __('label_captcha_incorrect', false, true); ?>",
		banned_msg: "<?php __('label_banned_msg', false, true); ?>",
		send_msg: "<?php __('label_send_msg', false, true); ?>",
		send_ok: "<?php __('label_send_ok', false, true); ?>",

		show_category: "<?php echo $tpl['option_arr']['o_show_category'];?>",
		reject_links: "<?php echo $tpl['option_arr']['o_reject_links'];?>",
		
		show_message: <?php echo isset($_GET['err']) ? 'true' : 'false'; ?>,
		banned_words: "<?php echo !empty($tpl['option_arr']['o_banned_words']) ? trim($tpl['option_arr']['o_banned_words']) : ''; ?>",
		reject_msg: "<?php __('label_reject_msg', false, true); ?>",
		embed: <?php echo !empty($tpl['embed']) ? 'true' : 'false'; ?>
	};
	<?php
	$dm = new pjDependencyManager(PJ_INSTALL_PATH, PJ_THIRD_PARTY_PATH);
	$dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();
	?>
	loadScript("<?php echo PJ_INSTALL_URL . preg_replace('|^' . PJ_INSTALL_PATH . '|', '', $dm->getPath('pj_jquery')); ?>pjQuery.min.js<?php echo $assetVer($dm->getPath('pj_jquery') . 'pjQuery.min.js'); ?>", function () {
		loadScript("<?php echo PJ_INSTALL_URL . preg_replace('|^' . PJ_INSTALL_PATH . '|', '', $dm->getPath('pj_validate')); ?>pjQuery.validate.min.js<?php echo $assetVer($dm->getPath('pj_validate') . 'pjQuery.validate.min.js'); ?>", function () {
			loadScript("<?php echo PJ_INSTALL_URL . preg_replace('|^' . PJ_INSTALL_PATH . '|', '', $dm->getPath('pj_validate')); ?>pjQuery.additional-methods.min.js<?php echo $assetVer($dm->getPath('pj_validate') . 'pjQuery.additional-methods.min.js'); ?>", function () {
				loadScript("<?php echo PJ_INSTALL_URL . preg_replace('|^' . PJ_INSTALL_PATH . '|', '', $dm->getPath('pj_bootstrap')); ?>pjQuery.bootstrap.min.js<?php echo $assetVer($dm->getPath('pj_bootstrap') . 'pjQuery.bootstrap.min.js'); ?>", function () {
					loadScript("<?php echo PJ_INSTALL_URL . PJ_JS_PATH; ?>pjLoad.js<?php echo $assetVer(PJ_INSTALL_PATH . PJ_JS_PATH . 'pjLoad.js'); ?>", function () {
						Blog = new Blog(STBObj);
					});
				});	
			});
		});
	});
})();
</script>