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
    
if(isset($_POST['item']))
	$itemName = $_POST['item'];

if(isset($_POST['count']))
	$itemCount = $_POST['count'];

if(isset($_POST['jsonArray']))
	$jsonData = $_POST['jsonArray'];

if(!isset($_POST['function']))
	die("No function");

if(!isset($_POST['auth']))
	die("No auth");

$auth = $_POST['auth'];
$function = $_POST['function'];

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
	/* WHAT?
	if (!hash_equals($authKey, crypt($auth, $authKey))){
		die (json_encode(array('type' => API_ERROR_403, 'content' => 'Authentication failed.')));
	}
	*/
	if($auth!==$authKey)
		die (json_encode(array('type' => API_ERROR_403, 'content' => 'Authentication failed.')));
	
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
