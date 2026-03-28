<?php
require_once 'utils/solrutils.php';
header('Access-Control-Allow-Origin: *'); 

class CompleteClass{

	public function getSuggestions($params){
		$q = $params['q'];
	
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'ec2-13-126-139-41.ap-south-1.compute.amazonaws.com:5000/getSuggestions');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"query\" : \"$q\"}");
		
		$headers = array();
		$headers[] = 'Content-Type: text/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		$r = json_decode($result, true);
		$v= $r['documents'][0];
		$ids = $r['ids'][0];
		$dist = $r['distances'][0];
		$response = array();
		$i=0;
		foreach($v as $a){
			$response[] = array('uuid' => $ids[$i] , 'title' => $a, 'dist' => $dist[$i]); 
		 $i++;
		}
		return array_slice($response,0,3);
	}




}
