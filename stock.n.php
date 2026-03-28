<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/StockDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
global $ln;
$id = $_REQUEST['id'];
$ln = $_REQUEST['ln'];
$langs= array('english' => 'en' , 'hindi' => 'hi', 'punjabi' => 'pu');
$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

$template_file = 'stock.twig.html';
//$template_file = 'stock.en.twig.html';
if($ln == 'hindi')
	$template_file = 'stock.hi.twig.html';
if($ln == 'punjabi')
	$template_file = 'stock.pu.twig.html';

$template = $twig->load($template_file);
$template_array  = array();

$url_pattern =   'stock/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

try {
 $file_path = 'mocks/stocks/' . $id . '.json';
 if(!file_exists($file_path))
	throw new Exception("Stock Not found");	
 $json_data = json_decode(file_get_contents($file_path), true);
 $template_array = tranform($json_data);
 $template_array['language']= $ln;
 $template_array['relative_url'] = '/stock/'. $id;
 $seo_data_vals = $seoDAO->substitute($template_array , $seo_data);
 $template_array['seo'] = $seo_data_vals;
 $template_array['footer'] = $footer;
 $template_array['navigation'] = $menu;  
 $template_array['id'] = $id;
 addRecos($template_array);
 addCompare($template_array);
 echo $template->render($template_array);
}
catch(Exception $e){
        error_log('ERROR:' . print_r($e , true));
        http_response_code(404);
        $template_404 = $twig->load('404.twig.html');
        echo $template_404->render(array(
                'navigation' => $menu ,
                'footer' => $footer
        ));
}

function addRecos(&$template_array){
 #Recos
 global $ln;
 $c =  new StockRecoService($ln);
 $r = $c->getReco($template_array['industry'] , 'high');
 $template_array['similar'] = $r['recos'];

}

function addCompare(&$data){
	global $ln;
	$sims = json_decode(file_get_contents('mocks/stock_recos.json'), true);
	$cat1 = $data['industry'];

	$ids1 = $sims[$cat1];

	$id = $data['id'];
	$dao =  new StockDAO();
	$similar = array();
	foreach($ids1 as $idx){
        	if($id == $idx)
                	continue;
        	$sf = $dao->getByID($idx ,$langs[$ln]);
        	$sf['compare_url'] = '/'  . $ln . '/stocks-compare/'. $id . '-vs-' . $idx;
        	$similar[] = $sf;
	}
	$data['compare'] = $similar;
}


function tranform($json){
	global $ln;
	setlocale(LC_MONETARY, 'en_IN');
	$data = array();
	$data['company_title'] = $json['info']['companyName'];
	$data['company_name'] = $json['info']['companyName'];
	if (!empty($json['language'])){
		$data['company_name'] = $json['language']['shortName'];

		if($ln == 'hindi'){
			$data['company_title'] = $json['language']['nameHindi'];
			$data['company_name'] = $json['language']['shortNameHindi'];
		}
		if($ln == 'punjabi'){
			$data['company_title'] = $json['language']['namePunjabi'] . '(' .  $data['company_title']  .')';
			$data['company_name'] = $json['language']['shortNamePunjabi'] . '(' .  $data['company_name']  .')';
		}
	}
	$data['industry'] = $json['info']['industry'];
	$data['last_updated'] = $json['metadata']['lastUpdateTime'];
	$date=date_create($data['last_updated']);
	$data['peratio'] = $json['metadata']['pdSymbolPe'];
	$data['peratiosector'] = $json['metadata']['pdSectorPe'];
	$data['sectorindex'] = $json['metadata']['pdSectorInd'];
	$data['ltp'] = $json['priceInfo']['lastPrice'];
	$data['open_price'] = $json['priceInfo']['open'];
	$data['previousClose'] = $json['priceInfo']['previousClose'];
	$data['pChange'] = round($json['priceInfo']['pChange'] , 2);
	$data['priceChange'] = $json['priceInfo']['change'];
	$data['intradayHigh'] = $json['priceInfo']['intraDayHighLow']['max'];
	$data['intradayLow'] = $json['priceInfo']['intraDayHighLow']['min'];
	$data['weekHigh'] = $json['priceInfo']['weekHighLow']['max'];
	$data['weekLow'] = $json['priceInfo']['weekHighLow']['min'];
	$data['weekHighDate'] = $json['priceInfo']['weekHighLow']['maxDate'];
	$data['weekLowDate'] = $json['priceInfo']['weekHighLow']['minDate'];
	$data['volume'] = money_format('%!.0n',  $json['marketDeptOrderBook']['tradeInfo']['totalTradedVolume']);
	$data['dropped'] = 1;
	$data['title'] = $data['company_name'] . ' share price NSE latest';
	if($ln == 'hindi'){
		$data['title'] = $data['company_name'] . '  शेयर की एनएसई पर कीमत';
	}
	if($data['pChange'] < 0){
		$data['dropped'] = -1;
	}
	return $data;
}
?>
