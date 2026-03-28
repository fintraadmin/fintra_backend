<?php
require_once '/var/www/html/conf/db.conf';

$site = "https://fintra.co.in";

$str = '<?xml version="1.0" encoding="UTF-8"?>';
$str .= "\n";
$str .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
$str .= "\n";

$host = DBHOST;
$username = DBUSER;
$password = DBPASS;

$pdo = new PDO('mysql:host='. $host .';dbname=fintracms;charset=utf8', $username, $password );
$query = "select url from blogs where draft='no' order by created";
$stmt = $pdo->prepare($query);
$stmt->execute();
$rows =  $stmt->fetchAll(PDO::FETCH_ASSOC);
$blogs = array();
foreach($rows as $row){
	$blogs[] = "https://fintra.co.in/blog/". $row['url'];
}

$str .= "<url>";
$str .= "\n";
$str .="<loc>" . $site . '/blog' . "</loc>";
$str .= "\n";
$str .= "</url>";

foreach($blogs as $blog){

	$str .= "\n";
	$str .= "<url>";
	$str .= "\n";
	$str .="<loc>" . $blog . "</loc>";
	$str .= "\n";
	$str .= "</url>";
}
$str .= "\n";
$str .= "</urlset>";

echo $str;
?>
