<?php


function callCallbackUrl($url, $postData) {
    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
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

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);


    // Close cURL session
    curl_close($ch);
    echo $response;
    #print_r($response,true); 

    // Return the response
    return $response;
}

$callback_url = 'https://staging.findipay.in:4443/api/fintra/fintraWebhook';

$arrayVar = [
    "agentId" => "TSI002",
    "product" => ["id" => 1, "name" => "CC", "description" => "CC 1"],
    "lead_created_date" => "2024-05-11 13:18:31.590",
    "status" => "Pending",
    "leadId" => 1,
    "customer" => [
        "name" => "ram",
        "mobileNo" => "8588970553",
        "emailId" => "ram@abc.com",
    ],
];

callCallbackUrl($callback_url  , $arrayVar);
?>
