<?php

require_once(dirname(__FILE__) . "/../config/globals.php");
require_once(dirname(__FILE__) . "/storage_module.php");

define ('ID_LENGTH', 8);


function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        //$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));//Original code - not working (openssl_random...) :(
        $rnd = hexdec(bin2hex(random_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    global $codeAlphabet;
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}

function get_unique_id()
{
	$id = getToken(ID_LENGTH);
	while (get_file_path_by_id($id) != FALSE){
		//Prevent colisions
		//Colisions are rare ( < 36 per milion )
		$id = getToken(ID_LENGTH);
	}
	return $id;
}

function is_valid_id($id){
	if (!is_string($id)){
		return FALSE;
	}
	if (strlen($id) != ID_LENGTH){
		return FALSE;
	}
	for($i = 0; $i < ID_LENGTH; ++$i){
		$chr = $id[$i];
		if ( ( ('0' > $chr) || ('9' < $chr) ) && ( ('a' > $chr) || ('z' < $chr) ) ){
			return FALSE;
		}
	}
	return TRUE;
}
?>
