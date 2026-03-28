<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/LoanApplicationDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('link_gen.twig.html');


$cid=$_REQUEST['cid'];
$template_array['cid']= $cid;
$links  = array();
//Credit Card Link
$link = array();
$link['link'] = 'https://fintra.co.in/english/landing?cid=findipay&product=cc';
$link['label'] = 'Credit Cards';
$links[] = $link;
//Personal Loan
$link = array();
$link['link'] = 'https://fintra.co.in/english/landing_loan?cid=findipay&product=personal-loan';
$link['label'] = 'Personal Loans';
$links[] = $link;

//Business Loan 
$link = array();
$link['link'] = 'https://fintra.co.in/english/landing_loan?cid=findipay&product=business-loan';
$link['label'] = 'Business Loans';
$links[] = $link;

$template_array['links'] = $links;
echo $template->render($template_array);

?>

