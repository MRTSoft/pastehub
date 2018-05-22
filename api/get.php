<?php

if(!isset($_SESSION)) 
{ 
	session_start(); 
}

require_once(dirname(__FILE__) . '/id_module.php');
require_once(dirname(__FILE__) . '/storage_module.php');

function return_not_found(){
	http_response_code(404);
	include(dirname(__FILE__) . "/../404.php");
	die();
}

function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_numeric($key) ){
            $key = 'item'.$key; //dealing with <0/>..<n/> issues
        }
        if( is_object($value)) {
        	//is_array($value) ) {
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}

if (isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
	if (!is_valid_id($id)){
		die();
	}
	$path = FALSE;
	$uid = NULL;
	if (isset($_SESSION['uid'])){
		$uid = $_SESSION['uid'];
	}
	global $use_db_storage;
	if (isset($use_db_storage) and $use_db_storage){
		$path = db_get_file_path_by_id($id, $uid); 
	} else {
		$path = get_file_path_by_id($id);
	}
	//DEBUG only
	//TODO Delete this
	//$path = get_file_path_by_id($id);
	
	$results = array();
	if ($path != FALSE){
		try{
			$results['meta_data'] = get_file_metadata_by_id($id);
			$results['content'] = get_file_content_by_id($id);
		} catch (Exception $e) {
			return_not_found();
		}
		if (!isset($_REQUEST['type']) || $_REQUEST['type'] == 'text'){
			header("Content-Type: text/plain; charset=utf-8");
			//echo htmlspecialchars($results->content);
			echo $results['content'];
			die();
		}

		if ($_REQUEST['type'] == 'json'){
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($results);
		}
		if ($_REQUEST['type'] == 'xml')
		{
			header("Content-Type: text/xml; charset=utf-8");
			$xml_data = new SimpleXMLElement('<?xml version="1.0"?><file></file>');
			array_to_xml($results, $xml_data);
			echo $xml_data->asXML();
		}
	} else {
		//Path is FALSE
		return_not_found();
	}
}
?>
