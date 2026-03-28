<?php
require 'lib/vendor/autoload.php';
require 'lib/vendor/conf.ini';

use Aws\S3\S3Client;
use Aws\Common\Credentials\Credentials;
use Aws\Exception\AwsException;

function uploadToS3($bucketName, $filePath, $keyName)
{
  $credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
    // Instantiate the S3 client with your AWS credentials
    $s3 = S3Client::factory([
        'version' => 'latest',
        'region'  => 'us-east-1', // Change this to your desired region
        'credentials' => $credentials
    ]);
try {
        // Upload the file
        $result = $s3->putObject([
            'Bucket' => $bucketName,
            'Key'    => $keyName,
            'SourceFile' => $filePath,
            'ACL'    => 'private-read', // Optional: Adjust ACL as needed
        ]);

        echo "File uploaded successfully. File URL: " . $result['ObjectURL'] . "\n";
    } catch (AwsException $e) {
        echo "Error uploading file: " . $e->getMessage() . "\n";
    }
}

$file = 'test.txt';
$bucketName = 'fintrafiles';
$keyNme ='test';

uploadToS3($bucketName , $file , $keyNme);


