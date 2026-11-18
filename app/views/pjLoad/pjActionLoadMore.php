<?php
switch ($tpl['option_arr']['o_layout']) {
	case 'layout_1':
		include_once PJ_VIEWS_PATH . 'pjLoad/elements/layout_1/load_more.php';
		break;
	case 'layout_2':
		include_once PJ_VIEWS_PATH . 'pjLoad/elements/layout_2/load_more.php';
		break;
	default:
		include_once PJ_VIEWS_PATH . 'pjLoad/elements/layout_1/load_more.php';
		break;
}
?>