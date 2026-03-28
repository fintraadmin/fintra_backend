<?php
require_once 'apis/CMSClass.php';
require_once 'apis/dao/CreditCardDAO.php';
require_once 'apis/TrackingClass.php';

function callCallbackUrl($url, $postData) {
    // Initialize cURL session
    $ch = curl_init($url);


    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response instead of printing it
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Set POST data

    $headers = ['x-client-code: fintra', 'Content-Type: application/json',];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if($response === false) {
        // If there's an error, you can handle it here
        echo 'cURL error: ' . curl_error($ch);
    }

    $info = curl_getinfo($ch);

    error_log("====== response $response");
    error_log("======= info ".  json_encode($info , true));	
    // Close cURL session
    curl_close($ch);

    // Return the response
    return $response;
}


error_log("input url" . json_encode($_GET, true));
$data['agentId'] = $_GET['aid'];
$data['productid'] = $_GET['pid'];
$data['publisher'] = $_GET['cid'];
$data['customer']['name'] = $_GET['name'];
$data['customer']['mobileNo'] = $_GET['phone'];
$data['customer']['emailId']= $_GET['email'];
$data['customer']['pincode']= $_GET['pincode'];

#get product details
if($_GET['pid'] != 'indusind-saving'){
 $dao = new CreditCardDAO();
 $card = $dao->getByID($data['productid']);
 $affliate_link = $card['findipaylink'];
}
else{
 $card=  array();
 $card['id'] = 'indusind_saving';
 $card['title'] = 'IndusInd Saving Account'; 
 $affliate_link = 'https://secure.traqkarr.com/click?pid=298&offer_id=1339&sub1=IMSASBICB298&sub2={clickid}&sub3={agentid}';
}
$prodcut= array();
$product['id'] = $card['id'];
$product['name'] = $card['title'];
$product['description'] = $card['title'];
error_log("  car " . json_encode($card, true));
$cmsDAO=new CMSClass();
$lead_data = $cmsDAO->createRecord($data);
$clickid=$lead_data['clickid'];

error_log("======= findipaylink $affliate_link");
#replace agenid and clickid in url

$redirect_url = str_replace('{agentid}' , $data['agentId'], $affliate_link);
$redirect_url =str_replace('{clickid}', $clickid,$redirect_url);

//Trackier
/*
$tracker = new TrackierTrackingService(
    'https://fintra.gotrackier.io/click?campaign_id=2&pub_id=2',  // Redirect URL
    'https://fintra.trackier.co/acquisition?security_token=630542d888e41e52814f',  // Postback URL
    '630542d888e41e52814f'
);

$params = new TrackingParams(
    $data['agentId'],
    $card['id'],
    'credit-card',
    $clickid,
    $data['customer']['name'],
    $data['customer']['mobileNo']
);
$redirectUrl = $tracker->getRedirectUrl('https://example.com', $params->toArray());
$clickId = $tracker->extractClickId($redirectUrl);
$tracker->trackStep($clickId, 'initiated', $params->toArray());
error_log("===== trackier  $clickId");
//
*/

// Get redirect URL
error_log("===== redirecting to $redirect_url");
#$url = 'https://secure.traqkarr.com/click?pid=298&offer_id=1251&sub1=publisherid&sub2=clickid&sub3=sub-publisherid';
##post the data 
$data['status'] = 'Pending';
$data['product'] = $product;
$data['lead_created_date'] = $lead_data['created'];
$data['leadId'] =intval($clickid);
$callback_url = 'https://findipay.in:4443/api/Fintra/FintraWebhook';
$postData = $data;
error_log("====== postdata " . json_encode($postData, true));
$response = callCallbackUrl($callback_url, $postData);
header("Location: $redirect_url");


?>
