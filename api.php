 <?php
	include 'mysql_connector.php';
	require 'config.php';
	
    $itemName = $_POST['item'];
	$itemCount = $_POST['count'];
	$function = $_POST['function'];
	$auth = $_POST['auth'];

	if ($auth != $authKey){
			header("HTTP/1.1 403 Forbidden");
			die (json_encode(array('code' => 'error', 'comment' => 'auth failed with authKey: '. $auth)));
		}
		
		$db = NEW sql($sqlHost, $sqlDatabase, $sqlUser, $sqlPassword);
		
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
			die (json_encode(array('code' => 'error', 'comment' => 'function not specified')));
			
		}


?> 
