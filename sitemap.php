<?php
/**
 * XML sitemap (/sitemap.xml via .htaccess) - every published post and every
 * active category that has posts, at the URLs the JS embed uses:
 *     <Blog page URL>?post=<url-slug>
 *     <Blog page URL>?blog_category=<url-slug>
 * The Blog page URL is set in Admin > Install (option o_blog_page). When it
 * is empty the posts have no single fixed URL, so the sitemap is empty.
 *
 * Deliberately standalone (does not bootstrap the app's controller/session
 * framework) so it stays fast and side-effect free for bots - it only needs
 * the DB credentials from config.inc.php. The slug rules mirror
 * pjEmbed::pjPostSlug() / pjEmbed::pjCategorySlugs() and
 * pjAppController::friendlyURL() - keep them in step.
 */
require dirname(__FILE__) . '/app/config/config.inc.php';

header('Content-Type: application/xml; charset=utf-8');

$link = mysqli_connect(PJ_HOST, PJ_USER, PJ_PASS, PJ_DB);
if (!$link)
{
	http_response_code(500);
	exit;
}
mysqli_set_charset($link, 'utf8');

$prefix = (defined('PJ_PREFIX') ? PJ_PREFIX : '') . 'blog_';

function pj_sitemap_friendly_url($str, $divider = '-')
{
	$str = mb_strtolower($str, mb_detect_encoding($str));
	$str = trim($str);
	$str = str_replace(array('Ä', 'ä', 'É', 'é', 'Ö', 'ö', 'Ü', 'ü'), array('A', 'a', 'E', 'e', 'O', 'o', 'U', 'u'), $str);
	$str = preg_replace('/[^\w\s\-]+/u', '', $str);
	$str = preg_replace('/[_|\s]+/', $divider, $str);
	$str = preg_replace('/[-]+/', $divider, $str);
	$str = preg_replace('/^-+|-+$/', '', $str);
	return $str;
}

// <Blog page URL> + one parameter (keeps any query the page already has).
function pj_sitemap_url($base, $key, $value)
{
	return $base . (strpos($base, '?') === false ? '?' : '&') . rawurlencode($key) . '=' . rawurlencode($value);
}

$res = mysqli_query($link, "SELECT `value` FROM `{$prefix}options` WHERE `foreign_id` = 1 AND `key` = 'o_blog_page' LIMIT 1");
$row = $res ? mysqli_fetch_assoc($res) : null;
$base = $row ? trim((string) $row['value']) : '';
$base = preg_replace('/#.*$/', '', $base);
if ($base !== '' && !preg_match('~^https?://[^\s"\'<>`\\\\]+$~i', $base))
{
	$base = '';
}

$urls = array();
if ($base !== '')
{
	$urls[] = array('loc' => $base, 'lastmod' => null);

	$cat_res = mysqli_query($link, "SELECT c.`id`, c.`category`, c.`url_slug` FROM `{$prefix}categories` AS c
		WHERE c.`status` = 'T' AND EXISTS (SELECT 1 FROM `{$prefix}posts` AS p WHERE p.`category_id` = c.`id` AND p.`status` = 'T')");
	if ($cat_res)
	{
		while ($cat = mysqli_fetch_assoc($cat_res))
		{
			$slug = trim((string) $cat['url_slug']);
			if ($slug === '')
			{
				$slug = pj_sitemap_friendly_url(stripslashes((string) $cat['category']));
			}
			if ($slug === '')
			{
				$slug = 'category-' . (int) $cat['id'];
			}
			$urls[] = array('loc' => pj_sitemap_url($base, 'blog_category', $slug), 'lastmod' => null);
		}
	}

	// Same visibility rule as the blog list: published, and either no
	// category or an active one.
	$post_res = mysqli_query($link, "SELECT p.`id`, p.`title`, p.`url_slug`, p.`published_date`, p.`modified` FROM `{$prefix}posts` AS p
		WHERE p.`status` = 'T' AND (p.`category_id` = 0 OR p.`category_id` IN (SELECT c.`id` FROM `{$prefix}categories` AS c WHERE c.`status` = 'T'))
		ORDER BY p.`published_date` DESC");
	if ($post_res)
	{
		while ($post = mysqli_fetch_assoc($post_res))
		{
			$slug = trim((string) $post['url_slug']);
			if ($slug === '')
			{
				$slug = pj_sitemap_friendly_url(stripslashes((string) $post['title']));
			}
			if ($slug === '')
			{
				$slug = 'post-' . (int) $post['id'];
			}
			$lastmod_raw = !empty($post['modified']) ? $post['modified'] : $post['published_date'];
			$urls[] = array('loc' => pj_sitemap_url($base, 'post', $slug), 'lastmod' => !empty($lastmod_raw) ? date('c', strtotime($lastmod_raw)) : null);
		}
	}
}

mysqli_close($link);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u)
{
	echo "\t<url>\n";
	echo "\t\t<loc>" . htmlspecialchars($u['loc'], ENT_QUOTES, 'UTF-8') . "</loc>\n";
	if (!empty($u['lastmod']))
	{
		echo "\t\t<lastmod>" . htmlspecialchars($u['lastmod'], ENT_QUOTES, 'UTF-8') . "</lastmod>\n";
	}
	echo "\t</url>\n";
}
echo '</urlset>' . "\n";
?>