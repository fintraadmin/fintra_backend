<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include 'utils/utils.php';


define('ENCRYPTION_KEY', 's@ltk3yF1ntr@' );
$string = "22";

$OpensslEncryption = new Utils();
$encrypted = base64_encode($OpensslEncryption->encrypt($string, ENCRYPTION_KEY));
$decrypted = $OpensslEncryption->decrypt($encrypted, ENCRYPTION_KEY);

echo "encrypted  $encrypted \n";
echo "decrypted  $decrypted \n";

?> 

