<?php

$dsn = 'mysql:host=localhost;dbname=fintracms';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->query("SELECT lead_id FROM loan_applications");
    $users = $stmt->fetchAll();

    $apiUrl = "https://fintra.co.in/api/loan/update-cibil";

    foreach ($users as $user) {
        $postData = json_encode([
            'lead_id' => $user['lead_id'],
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	echo "processing ". $user['lead_id'] . "\n";
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            error_log("Failed to send data for user ID {$user['lead_id']}: " . curl_error($ch));
        }
	else{
	   echo "Success for " . $user['lead_id'] . "\n";
	}

        curl_close($ch);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

?>

