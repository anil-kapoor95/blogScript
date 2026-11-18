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

// Languages: when the multi-language mode is on, every page is listed once per language
// (with <xhtml:link> alternates) - the default language at its usual address, the others with
// ?blog_lang=<language code> and that language's own slug.
$locales = array();
$res = mysqli_query($link, "SELECT `id`, `language_iso`, `is_default` FROM `{$prefix}plugin_locale` ORDER BY `sort` ASC, `id` ASC");
while ($res && ($l = mysqli_fetch_assoc($res)))
{
	$locales[(int) $l['id']] = $l;
}
$res = mysqli_query($link, "SELECT `value` FROM `{$prefix}options` WHERE `foreign_id` = 1 AND `key` = 'o_multi_lang' LIMIT 1");
$row = $res ? mysqli_fetch_assoc($res) : null;
$multi = $row && substr((string) $row['value'], -3) === '::1' && count($locales) > 1;
$default_lid = 0;
foreach ($locales as $lid => $l)
{
	if ((int) $l['is_default'] === 1) { $default_lid = $lid; }
}
if (!$default_lid && !empty($locales)) { reset($locales); $default_lid = key($locales); }
$tr = array(); // $tr[model][foreign_id][locale][field] = content
if ($multi)
{
	$res = mysqli_query($link, "SELECT `model`, `foreign_id`, `locale`, `field`, `content` FROM `{$prefix}multi_lang`
		WHERE `model` IN ('pjCategory', 'pjPost') AND `field` IN ('url_slug', 'category', 'title') AND `content` IS NOT NULL AND `content` <> ''");
	while ($res && ($t = mysqli_fetch_assoc($res)))
	{
		$tr[$t['model']][(int) $t['foreign_id']][(int) $t['locale']][$t['field']] = $t['content'];
	}
}

// <Blog page URL> + parameter(s) for a language (nothing extra for the default language).
function pj_sitemap_loc($base, $key, $value, $lid, $multi, $default_lid, $locales)
{
	$url = $key === '' ? $base : pj_sitemap_url($base, $key, $value);
	if ($multi && $lid !== $default_lid)
	{
		$url .= (strpos($url, '?') === false ? '?' : '&') . 'blog_lang=' . rawurlencode($locales[$lid]['language_iso']);
	}
	return $url;
}

// Entries: array(array(locale id => array('loc' =>, 'lastmod' =>)))
$entries = array();
function pj_sitemap_add(&$entries, $base, $key, $slugs, $lastmod, $multi, $default_lid, $locales)
{
	$entry = array();
	foreach ($multi ? array_keys($locales) : array($default_lid) as $lid)
	{
		$slug = isset($slugs[$lid]) && $slugs[$lid] !== '' ? $slugs[$lid] : $slugs[$default_lid];
		$entry[$lid] = array('loc' => pj_sitemap_loc($base, $key, $slug, $lid, $multi, $default_lid, $locales), 'lastmod' => $lastmod);
	}
	$entries[] = $entry;
}

if ($base !== '')
{
	$entry = array();
	foreach ($multi ? array_keys($locales) : array(0) as $lid)
	{
		$entry[$lid] = array('loc' => pj_sitemap_loc($base, '', '', $lid, $multi, $default_lid, $locales), 'lastmod' => null);
	}
	$entries[] = $entry;

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
			$slugs = array($default_lid => $slug);
			if ($multi)
			{
				foreach ($locales as $lid => $l)
				{
					$t = isset($tr['pjCategory'][(int) $cat['id']][$lid]) ? $tr['pjCategory'][(int) $cat['id']][$lid] : array();
					$s = isset($t['url_slug']) ? trim($t['url_slug']) : '';
					if ($s === '' && isset($t['category']))
					{
						$s = pj_sitemap_friendly_url(stripslashes($t['category']));
					}
					if ($lid !== $default_lid) { $slugs[$lid] = $s; }
				}
			}
			pj_sitemap_add($entries, $base, 'blog_category', $slugs, null, $multi, $default_lid, $locales);
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
			$slugs = array($default_lid => $slug);
			if ($multi)
			{
				foreach ($locales as $lid => $l)
				{
					$t = isset($tr['pjPost'][(int) $post['id']][$lid]) ? $tr['pjPost'][(int) $post['id']][$lid] : array();
					$s = isset($t['url_slug']) ? trim($t['url_slug']) : '';
					if ($s === '' && isset($t['title']))
					{
						$s = pj_sitemap_friendly_url(stripslashes($t['title']));
					}
					if ($lid !== $default_lid) { $slugs[$lid] = $s; }
				}
			}
			pj_sitemap_add($entries, $base, 'post', $slugs, !empty($lastmod_raw) ? date('c', strtotime($lastmod_raw)) : null, $multi, $default_lid, $locales);
		}
	}
}

mysqli_close($link);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . ($multi ? ' xmlns:xhtml="http://www.w3.org/1999/xhtml"' : '') . '>' . "\n";
foreach ($entries as $entry)
{
	foreach ($entry as $lid => $u)
	{
		echo "\t<url>\n";
		echo "\t\t<loc>" . htmlspecialchars($u['loc'], ENT_QUOTES, 'UTF-8') . "</loc>\n";
		if (!empty($u['lastmod']))
		{
			echo "\t\t<lastmod>" . htmlspecialchars($u['lastmod'], ENT_QUOTES, 'UTF-8') . "</lastmod>\n";
		}
		if ($multi)
		{
			foreach ($entry as $other_lid => $other)
			{
				echo "\t\t" . '<xhtml:link rel="alternate" hreflang="' . htmlspecialchars($locales[$other_lid]['language_iso'], ENT_QUOTES, 'UTF-8') . '" href="' . htmlspecialchars($other['loc'], ENT_QUOTES, 'UTF-8') . '" />' . "\n";
			}
			echo "\t\t" . '<xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($entry[$default_lid]['loc'], ENT_QUOTES, 'UTF-8') . '" />' . "\n";
		}
		echo "\t</url>\n";
	}
}
echo '</urlset>' . "\n";
?>
