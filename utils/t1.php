<?php
function highlight($value , $key){
                $value = '<b>'. $value . '</b>';
                $highlighted = $value;
                if (strpos($value,$key) !== FALSE){
                        $start_pos = strpos($value,$key);
                        $len = strlen($key);
			echo "$value , $start_pos  , $len \n";
                        $str1 = substr($value , 0 , $start_pos );
                        $str2 = substr($value , $start_pos , $len);
                        $str3 = substr($value , $start_pos + $len );

                        $highlighted = $str1 . '</b>' . $str2  . '<b>' . $str3;
                }
                return $highlighted;
}

$value = 'how to save money';
$key = 'how';
  
print_r(highlight($value , $key));
?>
