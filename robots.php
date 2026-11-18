<?php
/**
 * Dynamic robots.txt - served for /robots.txt through the rewrite rule in
 * .htaccess, so the Sitemap URL (and the Disallow paths) always match the
 * domain and folder the script is actually running on. No manual edit is
 * needed when the site moves from localhost to its real domain.
 *
 * Standalone on purpose (no framework bootstrap, no DB): it only needs
 * PJ_INSTALL_URL from config.inc.php, like sitemap.php.
 *
 * Note: search engines only read robots.txt from the domain root
 * (https://example.com/robots.txt). If the script is installed in a
 * sub-folder, copy the output of this file to the root robots.txt.
 */
require dirname(__FILE__) . '/app/config/config.inc.php';

header('Content-Type: text/plain; charset=utf-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$install_path = rtrim((string) parse_url(PJ_INSTALL_URL, PHP_URL_PATH), '/');
$base = $scheme . $_SERVER['HTTP_HOST'] . $install_path;

echo "User-agent: *\n";
echo "Disallow: " . $install_path . "/app/config/\n";
echo "Disallow: " . $install_path . "/app/controllers/\n";
// core/ is NOT disallowed: the blog's JavaScript/CSS must stay crawlable,
// Google renders the embed with them.
echo "Disallow: " . $install_path . "/_to_delete/\n";
echo "Disallow: " . $install_path . "/index.php?controller=pjAdmin\n";
echo "Allow: " . $install_path . "/\n";
echo "\n";
echo "Sitemap: " . $base . "/sitemap.xml\n";
