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
    
$itemName = isset ($_POST['item']) ? $_POST['item'] : NULL;
$itemCount = isset ($_POST['count']) ? $_POST['count'] : NULL;
$jsonData = isset ($_POST['jsonArray'])? $_POST['jsonArray'] : NULL;
$function = isset ($_POST['function']) ? $_POST['function'] : NULL;
$auth = isset ($_POST['auth']) ? $_POST['auth'] : NULL;

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
		$dbConnector = "sqlite_connector.php";
		$dbConfig = $SQLiteConfig;
		break;
	case 'MySQL':
		$dbConnector = "mysql_connector.php";
		$dbConfig = $MySQLConfig;
		break;
	default:
		$dbConnector = "";
		$dbConfig = "";
		die (json_encode(array('type' => API_ERROR_NO_DATABASE, 'content' => 'no database type specified')));
}


include $dbConnector;
	
	if (!hash_equals($authKey, crypt($auth, $authKey))){
		die (json_encode(array('type' => API_ERROR_403, 'content' => 'Authentication failed.')));
	}
	
	$db = NEW DataBase($dbConfig);
	
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
