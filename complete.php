<?php

require_once 'vendor/autoload.php';
require_once 'apis/services/TaxonomyService.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/PromptDAO.php';


session_start();
//session_reset();
session_regenerate_id();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = $_REQUEST['ln'];

$lang_key = Utils::$language_keys[$ln];
$params['language'] = $lang_key;


?>

