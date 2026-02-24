<?php
// sitemap.php
require_once 'config.php';

// Generate dynamic sitemap XML
header("Content-Type: application/xml; charset=utf-8");

$baseUrl = BASE_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Array of static pages
$pages = [
    '/' => '1.0',
    '/index.php' => '1.0',
    '/blog.php' => '0.8',
    '/login.php' => '0.5'
];

foreach ($pages as $url => $priority) {
    echo "  <url>\n";
    echo "    <loc>{$baseUrl}{$url}</loc>\n";
    echo "    <changefreq>daily</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

// Fetch published blog posts
$res = $conn->query("SELECT slug, updated_at FROM blog_posts WHERE status = 'published' ORDER BY updated_at DESC");

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $postUrl = $baseUrl . "/blog_detail.php?slug=" . urlencode($row['slug']);
        $date = date('Y-m-d', strtotime($row['updated_at']));
        echo "  <url>\n";
        echo "    <loc>{$postUrl}</loc>\n";
        echo "    <lastmod>{$date}</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";
    }
}

echo '</urlset>';
