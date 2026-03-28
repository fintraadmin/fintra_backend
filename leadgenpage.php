<?php
require_once 'vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('leadgen.twig.html');
$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
$pid=$_REQUEST['pid'];

$template_array['redirectURL'] = 'https://fintra.co.in/redir?aid='. $aid. '&cid=' . $cid. '&pid='. $pid;

echo $template->render($template_array);

?>

