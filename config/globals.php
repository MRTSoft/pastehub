<?php
$default_language = 'en';
//WARNING: DO not use relative paths! Use full paths instead
//$primary_storage_location = 'F:/Apache24/htdocs/proiect/storage/';
$primary_storage_location = dirname(__FILE__).'/../storage/';
//Set mirror to null for no mirroring of data
//$mirror_storage_location = 'C:/storage/';
$mirror_storage_location = null;
$private_config_values_location = dirname(__FILE__) . '/../restricted/';

$codeAlphabet = "abcdefghijklmnopqrstuvwxyz0123456789";
$default_expire_time_days = 7; //1 week

$use_db_storage = TRUE;
?>
