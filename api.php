 <?php
	include 'mysql_connector.php';
	
    $itemName = $_POST['item'];
	$itemCount = $_POST['count'];
	$function = $_POST['function'];
	$auth = $_POST['auth'];
	$authKey = "";
	
		if ($auth != $authKey){
			header("HTTP/1.1 403 Forbidden");
			die (json_encode(array('code' => 'error', 'comment' => 'auth failed with authkey: '. $auth)));
		}
		
		$db = NEW sql('host','db','table','user','password');
		
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