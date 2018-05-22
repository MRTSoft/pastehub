<?php

require_once(dirname(__FILE__) . "/../config/globals.php");
require_once(dirname(__FILE__) . "/user_module.php");
require_once(dirname(__FILE__) . "/db_module.php");

//This doesn't check if the file actually exist
function get_path_of_id($id, $get_mirror_location = FALSE){
	//WARNING: aux, nul, prn, con, COM1 to COM9 and LPT1 to LPT9 are reserver names
	//under Windows and you can't create a folder with that name.
	//This is why we use 2 chars for subfolders and 2 levels
	// storage
	// \- ab
	//    \- 2d
	//       \ab2d1234 - The actual file (JSON format)
	global $primary_storage_location;
	global $mirror_storage_location;
	$path = "";
	if (!$get_mirror_location){
		$dir = $primary_storage_location;
		$dir = $dir . strtolower(substr($id, 0, 2)) . '/' . strtolower(substr($id, 2, 2));
		$path = $dir . '/' . $id;
	} else {
		if (!is_null($mirror_storage_location)){
			$dir = $mirror_storage_location;
			$dir = $dir . strtolower(substr($id, 0, 2)) . '/' . strtolower(substr($id, 2, 2));
			$path = $dir . '/' . $id;
		} else {
			$path = FALSE;
		}
	}
	return $path;
}

//Returns a path of the file or FALSE if no such file exists
function get_file_path_by_id($id, $get_mirror_location = FALSE){
	$path = get_path_of_id($id, $get_mirror_location);
	if (!is_file($path)){
		$path = FALSE;
	}
	return $path;
}

function get_file_metadata_by_id($id, $use_mirror = FALSE) {
	$path = get_file_path_by_id($id, $use_mirror);
	$path .= '.meta';
	$contents = file_get_contents($path);
	$contents = utf8_encode($contents);
	$results = json_decode($contents);
	return $results;
}


//This returns the PHP object with the file
function get_file_content_by_id($id, $use_mirror = FALSE){
	$path = get_file_path_by_id($id, $use_mirror);
	$contents = file_get_contents($path);
	$results = utf8_encode($contents);
	//$contents = utf8_encode($contents);
	//$results = json_decode($contents);
	return $results;
}

function create_storage_dirs($root){
	//O(n^4) complexity !
	//TODO this could be a python script
	$seconds = 60 * 15; // 15 min!!!
	set_time_limit($seconds);
	//NOTE: this must be the same code that getToken uses!
	global $codeAlphabet;
	$len = strlen($codeAlphabet);
	if ($root[$len-1] != '/'){
		$root = $root . '/';
	}
	for ($i1=0; $i1 < $len; $i1++) {
		for ($i2=0; $i2 < $len; $i2++) { 
			$l1 = $root . '/' . $codeAlphabet[$i1] . $codeAlphabet [$i2];
			if (!is_dir($l1)){
    			mkdir($l1, 0755, true);
			}
			for ($j1=0; $j1 < $len; $j1++) { 
				for ($j2=0; $j2 < $len; $j2++) { 
    				$l2 = $l1 . '/' . $codeAlphabet[$j1] . $codeAlphabet [$j2];
    				if (!is_dir($l2)){
	    				mkdir($l2, 0755, true);
    				}
				}
			}
		}
	}
}


function _save_raw_file($content, $path){
	if (!$path){
		//This should NOT happen
		throw new Exception("Unable to save to specified path", 1);
	}
	if (!is_dir(dirname($path))){
		mkdir(dirname($path), 0755, TRUE);
	}
	
	$f = fopen($path, 'w');
	if (!$f){
		throw new Exception("Unable to open file $path", 2);
	}
	fwrite($f, $content);
	fclose($f);
}

//metadata should contain user and expire time
//Save content and meta-data separate

// in $id
// in $meta_data
// in $content
// in $uid
function save_file($id, $meta_data, $content, $uid = null){
	
	
	$path = get_path_of_id($id);
	$meta = json_encode($meta_data);
	_save_raw_file($content, $path);
	_save_raw_file($meta, $path . '.meta');
	global $mirror_storage_location;
	if (!is_null($mirror_storage_location)){
		$use_mirror = TRUE;
		$path = get_path_of_id($path, $use_mirror);
		_save_raw_file($content, $path);
		_save_raw_file($meta, $path.'.meta');
	}
	//Add the file to auto-delete list (if necessary)
	if (isset($meta_data['expire_date'])){
		auto_clean_file($id, $meta_data);
	}

	if (!is_null($meta_data['owner'])){
		add_file_to_user($meta_data['owner'], $id);
	}
	
	global $use_db_storage;
	if (isset($use_db_storage) and $use_db_storage == TRUE){
		db_add_file($id, $path, $meta_data['valability'], $meta_data, $uid);
	}
	
	return TRUE;
}


function _get_autoclean_location($id, $meta = null, $use_mirror = FALSE) {
	if (is_null($meta)){
		//1. Read the metadata
		$data = get_file_content_by_id($id);
		$meta = $data->meta;
		$expire_date = $meta->expire_date;
	} else {
		$expire_date = $meta['expire_date'];
	}

	$path = get_path_of_id($id, $use_mirror);
	$path = dirname($path, 3);
	$doomsday = new DateTime();
	$doomsday->setTimestamp($expire_date);
	$doom_week = $doomsday->format('W');
	//for safty add a week
	$doom_week = (int)$doom_week;
	$doom_week = $doom_week+1;
	$doom_week = $doom_week % 54;
	$place = "W" . (string)$doom_week;
	$path .= "/meta_data/" . $place . "/";
	return $path;
}

function auto_clean_file($id, $meta_data){
	$path = _get_autoclean_location($id, $meta_data);
	if (!is_dir($path)){
		mkdir($path, 0755, true);
	}
	$content = $id;
	$path .= $id;
	_save_raw_file($content, $path);
	global $mirror_storage_location;
	if (!is_null($mirror_storage_location)){
		$path = _get_autoclean_location($id, $meta_data, TRUE);
		if (!is_dir($path)){
			mkdir($path, 0755, true);
		}
		$path .= $id;
		_save_raw_file($content, $path);
	}
}

function remove_file($id){
	//TODO delete from mirror
	$path = _get_autoclean_location($id);
	$file = $path . '/' . $id;
	if (is_file($file))
		unlink($file);
	$path = get_file_path_by_id($id);
	if (($path != FALSE) && is_file($path)){
		unlink($path);
		unlink($path . '.meta');
	}

	global $mirror_storage_location;
	if (!is_null($mirror_storage_location)){
		$path = _get_autoclean_location($id, TRUE);
		$file = $path . '/' . $id;
		if (is_file($file))
			unlink($file);
		$path = get_file_path_by_id($id, TRUE);
		if (($path != FALSE) && is_file($path)){
			unlink($path);
			unlink($path . '.meta');
		}
	}
}

function get_current_week_number(){
	$now = new DateTime();
	return (int)$now->format('W');
}

function check_should_clean_files(){
	//return TRUE or FALSE if we should perform server maintenance
	$thisWeek = get_current_week_number();
	//throw new Exception("This is week $thisWeek", 1);
	
	global $primary_storage_location;
	global $mirror_storage_location;
	$path = $primary_storage_location . "/meta_data/W$thisWeek/";
	return is_dir($path);
}

function perform_maintenance(){
	//do nothing for now	
	global $primary_storage_location;
	global $mirror_storage_location;
	$thisWeek = get_current_week_number();

	$weeklyEraseListPath = $primary_storage_location . "/meta_data/W$thisWeek/";
	$mirrorPath = null;
	if (!is_null($mirror_storage_location)){
		$mirrorPath = $mirror_storage_location . "/meta_data/W$thisWeek/";
	}
	$fi = new FileSystemIterator($weeklyEraseListPath, FileSystemIterator::SKIP_DOTS);

	foreach ($fi as $fileHandle) {
		$file_id = $fileHandle->getFilename();
		$meta = get_file_metadata_by_id($file_id);
		//1 Remove file from user
		$owner = $meta->owner;
		if (!is_null($owner)){
			remove_file_from_user($owner, $file_id);
		}

		//2 Delete file + auto-clean entry
		remove_file($file_id); //NOTE this also deletes the autoclean location
		//3 Delete mirror location File
	}
	//6 Remove auto-clean week directory
	rmdir($weeklyEraseListPath);

	//Repeat for all weeks since last clean (optional)
}
?>
