<?php
require_once('CONSTANTS.php');
header("ShoLiBackendVersion: ".BACKEND_VERSION);

if(!function_exists('hash_equals')) {
	function hash_equals($a, $b) {
		$ret = strlen($a) ^ strlen($b);
		$ret |= array_sum(unpack("C*", $a^$b));
		return !$ret;
	}
}   

$itemName = array_key_exists('item', $_POST) ? $_POST['item'] : null;
$itemCount = array_key_exists('count', $_POST) ? $_POST['count'] : null;
$itemChecked = array_key_exists('checked', $_POST) ? $_POST['checked'] : null;
$jsonData = array_key_exists('jsonArray', $_POST) ? $_POST['jsonArray'] : null;
$function = array_key_exists('function', $_POST) ? $_POST['function'] : null;
$auth = array_key_exists('auth', $_POST) ? $_POST['auth'] : null;

include('config.php');

if($authKey == ''){
	if ($_SERVER['HTTP_USER_AGENT'] != "ShoLiApp"){
		header("Location: INSTALL.php");
		exit();
	} else {
		die (json_encode(array('type' => API_ERROR_NOT_CONFIGURED, 'content' => 'Backend has not been configured yet!')));
	}
}

switch($dataBase){
	case 'SQLite':
		$dbConfig = $SQLiteConfig;
		break;
	case 'MySQL':
		$dbConfig = $MySQLConfig;
		break;
	default:
		$dbConnector = "";
		$dbConfig = "";
		die (json_encode(array('type' => API_ERROR_NO_DATABASE, 'content' => 'no database type specified')));
}


include('db_connector.php');
$db = NEW DataBase($dataBase, $dbConfig);
$db->init(); //TODO: put this to INSTALL.php

session_start();
if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] == 0 && $_SESSION['user_read'] == 1) {
	echo $db->listall();
	exit();
} else if (! isset($_SESSION['user_logged']) || $_SESSION['user_logged'] != 1) {
	if (!hash_equals($authKey, crypt($auth, $authKey))){
		die (json_encode(array('type' => API_ERROR_403, 'content' => 'Authentication failed.')));
	}
}


switch ($function){

	case 'listall':
			echo $db->listall();
		break;

	case 'save':
		if($db->exists($itemName)){
			echo $db->update($itemName, $itemCount, $itemChecked);
			} else {
				echo $db->save($itemName, $itemCount, $itemChecked);
			}
		break;

	case 'saveMultiple':
			echo $db->saveMultiple($jsonData);
		break;

	case 'deleteMultiple':
			echo $db->deleteMultiple($jsonData);
		break;

	case 'update':
			echo $db->update($itemName, $itemCount, $itemChecked);
		break;

	case 'delete':
			echo $db->delete($itemName);
		break;

	case 'clear':
			echo $db->clear();
		break;

	case 'addQRcodeItem':
			/* OUTPAN
			$outpanApiKey='1a74a95c40a331e50d4b2c7fe311328c'; // taken from https://github.com/johncipponeri/outpan-api-java
			$response = file_get_contents('https://api.outpan.com/v2/products/' . $itemName . '/?apikey=' . $outpanApiKey);
			if($response!==false) {
				$name = json_decode($response)->{'name'};
				if ( $name != '' ) {
					$itemName = $name;
					$itemCount = 1;
					$itemChecked = 'false';
					if( $db->exists($itemName) ) {
						echo $db->update($itemName, $itemCount, $itemChecked);
					} else {
						echo $db->save($itemName, $itemCount, $itemChecked);
					}
				} else {
					die (json_encode(array('type' => API_ERROR_QRCODE, 'content' => 'Code not found: ' . $itemName)));
				}
			} else {
					die (json_encode(array('type' => API_ERROR_QRCODE, 'content' => 'Error querying outpan.com')));
			}
			//*/
			$opengtindbApiKey='400000000'; // taken from https://opengtindb.org/api.php
			$response = file_get_contents('https://opengtindb.org/?ean=' . $itemName . '&cmd=query&queryid=' . $opengtindbApiKey);
			if($response!==false) {
				if(strpos($response,'error=0')!==false) {
					$values = parse_ini_string(utf8_encode($response));
					//file_put_contents('/tmp/sholi',print_r($values, true));
					$name = trim($values['name']);
					if ($name == '') $name = trim($values['detailname']);
					if ($name == '') $name = trim($values['name_en']);
					if ($name == '') $name = trim($values['detailname_en']);
					if ($name == '') $name = trim($values['descr']);

					if ( $name != '' ) {
						$itemName = $name;
						$itemCount = 1;
						$itemChecked = 'false';
						if( $db->exists($itemName) ) {
							echo $db->update($itemName, $itemCount, $itemChecked);
						} else {
							echo $db->save($itemName, $itemCount, $itemChecked);
						}
					} else {
						die (json_encode(array('type' => API_ERROR_QRCODE, 'content' => 'Code not found: ' . $itemName)));
					}
				} else {
					die (json_encode(array('type' => API_ERROR_QRCODE, 'content' => 'Code not found: ' . $itemName . ', '.$response)));
				}
			} else {
					die (json_encode(array('type' => API_ERROR_QRCODE, 'content' => 'Error querying opengtindb.org')));
			}
		break;

	default:
		die (json_encode(array('type' => API_ERROR_FUNCTION_NOT_SPECIFIED, 'content' => 'function not specified')));
		
}
?>
