<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjUtil extends pjToolkit
{
	static public function getPageURL()
	{
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != "80")
		{
		    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else
		{
		    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	static public function html2txt($document){
	    if (empty($document)) {
	        return $document;
	    }
		$search = array('@<script[^>]*?>.*?</script>@si', 
		               '@<[\/\!]*?[^<>]*?>@si',           
		               '@<style[^>]*?>.*?</style>@siU',   
		               '@<![\s\S]*?--[ \t\n\r]*>@'    
		);
		$text = preg_replace($search, '', $document);
		return $text;
	}
	static public function textToHtml($content)
	{
		$content = preg_replace('/\r\n|\n/', '<br />', $content);
		return '<html><head><title></title></head><body>'.$content.'</body></html>';
	}	
	static public function truncateDescription($string, $limit, $break=".", $pad=""){
		if(strlen($string) <= $limit) 
			return $string;  
		if(false !== ($breakpoint = strpos($string, $break, $limit))) 
		{ 
			if($breakpoint < strlen($string) - 1) 
			{ 
				$string = substr($string, 0, $breakpoint) . $pad; 
			} 
		} 
		return $string;
	}
	static public function tokenTruncate($string, $limit) {
	    if (empty($string)) {
	        return $string;
	    }
		$parts = preg_split('/([\s\n\r]+)/u', $string, null, PREG_SPLIT_DELIM_CAPTURE);
		
		$result_arr = array();
		$length = 0;
		foreach($parts as $k => $str)
		{
			if($str != '')
			{
				$length++;
				if ($length > $limit) { break; }
			}
			$result_arr[] = $str;
		}
		return implode("", $result_arr);
	}
	static public function getPostMaxSize()
	{
		$post_max_size = ini_get('post_max_size');
		switch (substr($post_max_size, -1))
		{
			case 'G':
				$post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
				break;
			case 'M':
				$post_max_size = (int) $post_max_size * 1024 * 1024;
				break;
			case 'K':
				$post_max_size = (int) $post_max_size * 1024;
				break;
		}
		return $post_max_size;
	}
	static public function getUploadMaxSize()
	{
		$max_upload = ini_get('upload_max_filesize');
		switch (substr($max_upload, -1))
		{
			case 'G':
				$max_upload = (int) $max_upload * 1024 * 1024 * 1024;
				break;
			case 'M':
				$max_upload = (int) $max_upload * 1024 * 1024;
				break;
			case 'K':
				$max_upload = (int) $max_upload * 1024;
				break;
		}
		return $max_upload;
	}
	
	static public function getWeekRange($date, $week_start)
	{
		$week_arr = array(
				0=>'sunday',
				1=>'monday',
				2=>'tuesday',
				3=>'wednesday',
				4=>'thursday',
				5=>'friday',
				6=>'saturday');
			
		$ts = strtotime($date);
		$start = (date('w', $ts) == 0) ? $ts : strtotime('last ' . $week_arr[$week_start], $ts);
		$week_start = ($week_start == 0 ? 6 : $week_start -1);
		return array(date('Y-m-d', $start), date('Y-m-d', strtotime('next ' . $week_arr[$week_start], $start)));
	}
	
	static public function getMadeWhere($period, $week_start)
	{
		$where_str = '';
		switch ($period) {
			case 1:
				$where_str = "(DATE(t1.published_date) = CURDATE())";
				break;
				;
			case 2:
				$where_str = "(DATE(t1.published_date) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)))";
				break;
				;
			case 3:
				list($start_week, $end_week) = pjUtil::getWeekRange(date('Y-m-d'), $week_start);
				$where_str = "(DATE(t1.published_date) BETWEEN '$start_week' AND '$end_week')";
				break;
				;
			case 4:
				list($start_week, $end_week) = pjUtil::getWeekRange(date('Y-m-d', strtotime("-7 days")), $week_start);
				$where_str = "(DATE(t1.published_date) BETWEEN '$start_week' AND '$end_week')";
				break;
				;
			case 5:
				$start_month = date('Y-m-01',strtotime('this month'));
				$end_month = date('Y-m-t',strtotime('this month'));
				$where_str = "(DATE(t1.published_date) BETWEEN '$start_month' AND '$end_month')";
				break;
				;
			case 6:
				$start_month = date("Y-m-d", mktime(0, 0, 0, date("m")-1, 1, date("Y")));
				$end_month = date("Y-m-d", mktime(0, 0, 0, date("m"), 0, date("Y")));
				$where_str = "(DATE(t1.published_date) BETWEEN '$start_month' AND '$end_month')";
				break;
				;
		}
		return $where_str;
	}

	/**
	 * An absolute http(s) page URL with no hash and no characters that could
	 * break out of an attribute; its own query (e.g. ?page_id=12) is kept,
	 * minus the blog's own parameters. Returns '' when invalid.
	 * Used for the Blog page URL (Admin > Install) and the embed host page.
	 */
	static public function cleanPageUrl($u)
	{
		$u = trim((string) $u);
		$u = preg_replace('/#.*$/', '', $u);
		if ($u === '' || !preg_match('~^https?://[^\s"\'<>`\\\\]+$~i', $u))
		{
			return '';
		}
		$parts = explode('?', $u, 2);
		if (isset($parts[1]))
		{
			$keep = array();
			foreach (explode('&', $parts[1]) as $pair)
			{
				$k = urldecode((string) strtok($pair, '='));
				if ($pair === '' || in_array($k, array('post', 'blog_post_id', 'blog_category', 'blog_page', 'blog_search', 'blog_archive'), true))
				{
					continue;
				}
				$keep[] = $pair;
			}
			$u = $parts[0] . (!empty($keep) ? '?' . implode('&', $keep) : '');
		}
		return $u;
	}

}
?>