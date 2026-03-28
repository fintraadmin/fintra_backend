<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://staging.findipay.in:4443/api/Fintra/FintraWebhook');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-client-code: fintra',
    'Content-type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"agentId\": \"TSI001\",\n    \"product\": {\n        \"id\": 1,\n        \"name\": \"CC\",\n        \"description\": \"CC 1\"\n    },\n    \"lead_created_date\": \"2024-05-11 13:18:31.590\",\n    \"status\": \"Pending\",\n    \"leadId\": 1,\n    \"customer\": {\n        \"name\": \"ram\",\n        \"mobileNo\": \"8588970553\",\n        \"emailId\": \"ram@abc.com\"\n    }\n}");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);

curl_close($ch);

echo $response;
