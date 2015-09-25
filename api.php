 <?php
    include "CONSTANTS.php";
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
	
	if (!hash_equals($authKey, crypt($auth, $authKey))){
		die (json_encode(array('type' => API_ERROR_403, 'content' => 'Authentication failed.')));
	}
	
	$db = NEW DataBase($dataBase, $dbConfig);
	$db->init(); //TODO: put this to INSTALL.php
	switch ($function){
		case 'listall':
			echo $db->listall();
		break;
		case 'save':
			if($db->exists($itemName)){
				echo $db->update($itemName, $itemCount);
			} else {
				echo $db->save($itemName, $itemCount);
			}
		break;
		case 'saveMultiple':
			echo $db->saveMultiple($jsonData);
		break;
		case 'deleteMultiple':
			echo $db->deleteMultiple($jsonData);
		break;
		case 'update':
			echo $db->update($itemName, $itemCount);
		break;
		case 'delete':
			echo $db->delete($itemName);
		break;
		case 'clear':
			echo $db->clear();
		break;
		default:
		die (json_encode(array('type' => API_ERROR_FUNCTION_NOT_SPECIFIED, 'content' => 'function not specified')));
		
	}


?> 
