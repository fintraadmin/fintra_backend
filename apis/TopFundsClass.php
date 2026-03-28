<?php
require_once 'DataClass.php';

class TopFundsClass extends DataClass {

  public function fetchData($params){
	$client = $this->connectDynamo();

			
	$iterator = $client->getIterator('Scan', array(
    	'TableName'     => 'topFunds',
	));
	$types = array('equity' , 'debt' , 'balanced', 'elss');
	$res =  array();
	foreach ($iterator as $item) {
	  $data  = array();
    	  foreach($item as $key=>$value){
		if(isset($value['S']))
			$data[$key] = $value['S'];
		if(isset($value['N']))
			$data[$key] = $value['N'];
	  }
	  $res[] = $data;
	}
	usort($res, function ($item1, $item2) {
   		 return $item1['position'] <=> $item2['position'];
	});

	$result =  array();
	foreach($res as $r){
		$result[$r['type']][] = $r;
	}
	$final =  array();
	foreach($types as $t){
		$tmp =  array();
		$tmp['id'] = $t;
		$tmp['data'] = $result[$t];
		$final[] = $tmp;
	}
	return $final;
  }
	


}

?>


