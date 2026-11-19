<?php
if (!defined('ROOT_PATH')) { header('HTTP/1.1 403 Forbidden'); exit; }
/**
 * Embed content script: renders the normal front view (listing or single
 * post) and hands it to the runtime as PJBLOG.render(CFG). Only the detail
 * view carries SEO meta (written into the host <head>, post tags win).
 */
$isDetail = ($tpl['embedView'] == 'detail');

// pjActionView.php shows its back link only for the pjLoad view route.
if ($isDetail)
{
	$_GET['controller'] = 'pjLoad';
	$_GET['action'] = 'pjActionView';
}

ob_start();
if ($isDetail && empty($tpl['found']))
{
	?>
<div id="pjWrapper">
	<div class="container-fluid pjBlContainer">
		<div class="panel panel-default pjBlMain">
			<div class="panel-body pjBlBody">
				<p><a href="#" class="pjBlBackLink"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span><?php __('label_back'); ?></a></p>
				<p class="pjBlNotFound"><?php __('label_post_not_found'); ?></p>
			</div>
		</div>
	</div>
</div>
	<?php
} elseif ($isDetail) {
	include PJ_VIEWS_PATH . 'pjLoad/pjActionView.php';
} else {
	include PJ_VIEWS_PATH . 'pjLoad/pjActionIndex.php';
}
$html = ob_get_clean();

// The blog's CSS goes into the page with the blog HTML, as text (styles),
// so the blog never shows unstyled while stylesheets download - and there is
// nothing extra to wait for. Remote stylesheets stay links (sheets).
// &nocss=1: the page already has these styles (e.g. after a comment).
$styles = array();
$sheets = array();
$cssFiles = array();
foreach ($controller->getCss() as $css)
{
	if (isset($css['remote']) && $css['remote'])
	{
		$sheets[] = $css['path'] . $css['file'];
	} else {
		$cssFiles[] = $css['path'] . $css['file'];
	}
}
// pjLoad.js adds this on window "load", which has already fired by the time
// the embed renders - include it up front instead.
$cssFiles[] = PJ_CSS_PATH . 'transitions.css';
if (empty($_GET['nocss']))
{
	foreach ($cssFiles as $f)
	{
		$styles[] = array('id' => $f, 'css' => $controller->pjInlineCss($f));
	}
}

$cfg = array(
	'html'       => $html,
	'sheets'     => $sheets,
	'styles'     => $styles,
	'view'       => $isDetail ? 'detail' : 'list',
	// Language shown ('' = the default one; null = one-language blog) and whether links name it
	// (only with a Blog page URL; otherwise the address stays clean and the runtime remembers it).
	'lang'       => $controller->isMultiLang() ? ($controller->localeQuery() !== '' ? $controller->localeIso($controller->getLocaleId()) : '') : null,
	'langInUrl'  => $controller->langInUrl(),
	'found'      => !empty($tpl['found']),
	'single'     => !empty($tpl['single']),
	// Base URL for blog links (Blog page URL, else the page the code sits on).
	'base'       => isset($tpl['linkBase']) ? $tpl['linkBase'] : '',
	// Post detail, or a category listing; never the plain list / search.
	'meta'       => (!empty($tpl['found']) && isset($tpl['embedMeta'])) ? $tpl['embedMeta'] : null,
	'commentUrl' => PJ_INSTALL_URL . 'index.php?controller=pjEmbed&action=pjActionComment',
);

// Keep the response valid even if a post body holds broken UTF-8.
$jsonFlags = defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : (defined('JSON_PARTIAL_OUTPUT_ON_ERROR') ? JSON_PARTIAL_OUTPUT_ON_ERROR : 0);
if (!empty($tpl['prefetch']))
{
	// Sent with the install-code script (pjActionLoadJs): the runtime shows it
	// only if it matches the page's parameters, else loads the content itself.
	echo "\n;window.PJBLOG && PJBLOG.prefetch(" . json_encode(array_merge($tpl['prefetch'], array('cfg' => $cfg)), $jsonFlags) . ");\n";
} else {
	echo "\n;window.PJBLOG && PJBLOG.render(" . json_encode($cfg, $jsonFlags) . ");\n";
}
?>