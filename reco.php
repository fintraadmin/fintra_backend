<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
global $ln;
$id = $_REQUEST['id'];
$ln = $_REQUEST['ln'];

$template_file = 'recomodule.html';


$template = $twig->load($template_file);
$template_array  = array();
$data = array();
$data['title'] = 'Test Recos';

$c =  new StockRecoService($ln);
$r = $c->getReco('PRINTING & STATIONERY' , 'high');
$recos = $c->output($r);
$template_array['recos'] = $recos;

echo $template->render($template_array);
