<?php


$cmd = "aws sns publish --region ap-southeast-1 --message 'hello rahul, your OTP is 5678' --phone-number +919167071530";
exec($cmd);

?>
