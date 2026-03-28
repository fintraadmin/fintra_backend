<?php
include_once 'apis/dao/PromptDAO.php';
include_once 'apis/CompleteClass.php';
include_once 'apis/LoggingClass.php';

class ChatGPTConnector {


    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1/chat/completions';
    //private $model =  'gpt-3.5-turbo';
    private $model =  'gpt-3.5-turbo-16k-0613';
    //private $model =  'gpt-4';
    private $system_prompt = 'You are a tax consultant. If not country is provided use India as default and INR as currency default. Make assumptions for deductions unless provided by user.';

    public function __construct() {
        // Load API key from environment variable
        $this->apiKey = getenv('OPENAI_API_KEY');
        if (empty($this->apiKey)) {
            throw new Exception('OPENAI_API_KEY environment variable is not set');
        }
    }


   public function getGPTResponse($data){
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
	curl_setopt($ch, CURLOPT_POST, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
	    error_log("GPT Curl Error " . curl_errno($ch));
	    return array();
        }
        
        curl_close($ch);
	return $response;
   }

   public function getSessionID(){
	$http_headers = apache_request_headers(); 
	$original_ip = $http_headers["X-Forwarded-For"]; 
	$session_id=$_COOKIE['PHPSESSID'];

	return $session_id;
    }
   
    public function fetchPrompts($id, $prompt , $role, $set=true){
	$session_id = $this->getSessionID();

	$memcache_key = 'gpt-' .$id . '-'.  $session_id;
	$user_data = unserialize(MemcacheUtil::getItem($memcache_key));
	$user_prompt['role'] = $role;
	$user_prompt['content'] = $prompt;
	if(empty($user_data))
		$user_data = array();
	$user_data[] = $user_prompt;

	if($set)
    		MemcacheUtil::setItem($memcache_key , serialize($user_data));

	return $user_data;

    }

    public function getSessionUUID(){
	$session_id = $this->getSessionID();
	$memcache_key = 'gpt-uuid-'.  $session_id;
	$data = MemcacheUtil::getItem($memcache_key);

	return $data;

    }

    public function setSessionUUID($uuid){
	$session_id = $this->getSessionID();
	$memcache_key = 'gpt-uuid-'.  $session_id;

	if(!empty($uuid)){	
    		MemcacheUtil::setItem($memcache_key , $uuid);
		return $uuid;
	}
    }

    public function generateSuggestions($prompt , $options = array()){
	$promptObj = new PromptDAO();
	$prompt_data = $promptObj->getByID('suggestion_prompt' , $options['ln']);
	
	$messages= array();
	$system['role']= 'system'; 
	$system['content'] = $prompt_data['system_prompt'];
	$id=$prompt_data['id'];
	$temperature = $prompt_data['temperature'];
	$top_p = $prompt_data['top_p'];
	$max_tokens = $prompt_data['max_tokens'];
	
	$messages[] = $system;
	$user_data = $this->fetchPrompts($id , $prompt , 'user', false);
	foreach($user_data as $a=>$b){
		$messages[] =$b;
	}

        $data = array(
            'model' => $this->model,
	    'messages' => $messages,
	    'temperature' => $temperature,
	    'max_tokens'=> $max_tokens,
	    'top_p' => $top_p
        );
        
        $response = $this->getGPTResponse($data);
	$v = json_decode($response, true)['choices'][0]['message']['content'];
	return $v; 

    }
 
    public function generateResponse($prompt, $options = array()) {
	$promptObj = new PromptDAO();
	$prompt_text = $prompt['prompt'];
	$prompt_uuid = $prompt['uuid'];

	//Generate uuid from session
	$uuid = $this->getSessionUUID();
	$session_uuid = $uuid;
	$top_prompt=null;
	$top_score = 1;
	if(empty($prompt_uuid)){
		//Try to get nearest prompt in case of empty uuid
		$p = array();
		$p['q'] = $prompt_text;
		$p['internal'] = $prompt_text;
		$class =  new CompleteClass();
		$pList = $class->getSuggestions($p);
		//choose for closest match
		foreach($pList as $l){
			if($l['dist'] < 0.3){
				$top_prompt= $l['uuid'];
				$top_score = $l['dist'];
				break; // pick the topmost promot
			}
		}
	}

	if($prompt_uuid == 'system')
		$prompt_uuid = 'default_prompt';
	elseif(empty($prompt_uuid) && !is_null($top_prompt))
		$prompt_uuid = $top_prompt;
	elseif(empty($prompt_uuid))
		$prompt_uuid = 'default_prompt';

	if(empty($uuid) || ($uuid != $prompt_uuid  && $top_score < 0.25))
		$uuid = $prompt_uuid;


	$prompt_data = $promptObj->getByID($uuid , $options['ln']);
        $mergedOptions = array_merge($defaultOptions, $options);

	//set Memcache uuid
	$this->setSessionUUID($uuid);     
 
	//Get Memcache data
	$messages= array();
	$system['role']= 'system'; 
	$system['content'] = $prompt_data['system_prompt'];
	$id=$prompt_data['id'];
	$temperature = $prompt_data['temperature'];
	$top_p = $prompt_data['top_p'];
	$max_tokens = 4000;


	$messages[] = $system;
	if(!empty($prompt_text)) // save prompt id for session
		$user_data = $this->fetchPrompts($id , $prompt_text , 'user');

	foreach($user_data as $a=>$b){
		$messages[] =$b;
	}
        $data = array(
            'model' => $this->model,
	    'messages' => $messages,
	    'temperature' => $temperature,
	    'max_tokens'=> $max_tokens,
	    'top_p' => $top_p
        );
        $response = $this->getGPTResponse($data);
	$v = json_decode($response, true)['choices'][0]['message']['content'];
	// Add system prompt to chat
	if(!empty($v))
		$this->fetchPrompts($id, $v , 'assistant');
	$response =  json_decode($response , true);
	$response['chat_uuid'] = $uuid;
	$response['session_id']= $this->getSessionID();
	$response['session_uuid'] = $session_uuid;
	return $response; 
    }
}

class GPTService{

	public function formatResponse($response){
		$text = $response['choices'][0]['message']['content'];
		if(empty($text))
			$text= 'Oops!! Please try again';
		$v = str_replace("\\n" , "<br />", $text);
		$v = str_replace("\n" , "<br />", $v);
		$v = str_replace("https://fintra.co.in/english/leads?ref=16" , '<a href="https://fintra.co.in/english/leads?ref=gpt" target="_blank"> click here</a> or via <a href="https://wa.me/919619392341" target="_blank">whatsapp</a>', $v);
		return $v; 
	}

	public function logResponse($data){
		$logclass =  new LoggingClass();
		$logclass->logData($data);
	}

	public function createLogPayload($id , $params, $response, $suggestions){
		$data = array();
		$data['chatid'] = $id;
		$data['service'] = 'fintragpt';
		$data['session_id'] = $response['session_id'];
		$data['user_prompt'] = $params['prompt'];
		$data['user_uuid'] = $params['uuid'];
		$data['chat_uuid'] = $response['chat_uuid'];
		$data['session_uuid'] = $response['session_uuid'];
		$data['prompt_tokens'] = $response['usage']['prompt_tokens'];
		$data['completion_tokens'] = $response['usage']['completion_tokens'];
		$data['suggestion_count'] = count($suggestions['suggestions']);
		return $data;
	}


  	public function chatID(){
		$session_id = $_COOKIE['PHPSESSID'];
		$uid = uniqid();
		return $session_id.$uid;
  	}

 	public function getData($args){
		$start = microtime(true);
		$cid = $this->chatID();
		$connector = new ChatGPTConnector();
		$prompt_data['prompt'] =  $args['message'];
		$prompt_data['uuid'] =  $args['uuid'];
		if(empty($prompt_data['prompt']))
			return array();
		$ln = $args['ln'];
		if(empty($ln))
			$ln = 'en';
		$options = array(
			'temperature' =>0, 
    			'max_tokens' => 400,
			'ln' => $ln
		);
		$response = $connector->generateResponse($prompt_data, $options);
		$response_text = $this->formatResponse($response);
		#$suggestions = $connector->generateSuggestions($prompt_data['prompt'], $options);
		#$suggestions = json_decode($suggestions , true);
		$end = microtime(true);
		$latency  = round($end - $start);
		$logData = $this->createLogPayload($cid , $prompt_data , $response , $suggestions);
		$logData['latency'] = $latency;
		$this->logResponse($logData);
		$r= array();
		$r['ai_response'] = $response_text;
		$r['suggestions'] = $suggestions['suggestions'];
		$r['tracking_id'] = $cid;
		return $r;


	} 
}


?>
		

