 <?php
	include_once('CONSTANTS.php');
	class DataBase
        {
        var $server, $username, $password, $database;

        function __construct($mysql_config)
            {
            $this->server = $mysql_config['host'];
			$this->database = $mysql_config['db'];
			$this->username = $mysql_config['user'];
			$this->password = $mysql_config['password'];
            }

        function save($itemName, $itemCount)
            {
				if(empty($itemName)||empty($itemCount)) {
					die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing')));
				}
            //connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			
			//prepare query
			$stmt = $handler->prepare("INSERT into ShoppingList(item,count) VALUES(?,?)");
			$stmt->bind_param('ss', $itemName, $itemCount);

			//execute query and check if successful
			if ($stmt->execute()){
				$result = json_encode(array('type' => API_SUCCESS_SAVE, 'content' => $itemName.' saved'));
			} else {
				$result = json_encode(array('type' => API_ERROR_SAVE, 'content' => $stmt->error));
			}
		
			//close connection
			$stmt->close();
			
			//return result
			return $result;
            }
			
		function saveMultiple($jsonData)
            {
				if(empty($jsonData)) {
					die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing for saveMultiple')));
				}
            //connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			//iterate over all items in json array
			$array = json_decode( $jsonData, true );
			foreach($array as $item)
			{
				//prepare query
				$stmt = $handler->prepare("INSERT into ShoppingList(item,count) VALUES(?,?)");
				$stmt->bind_param('ss', $item['itemTitle'], $item['itemCount']);

				//execute query and check if successful
				if ($stmt->execute()){
					$result = json_encode(array('type' => API_SUCCESS_SAVE, 'content' => ' Multiple items saved'));
				} else {
					$result = json_encode(array('type' => API_ERROR_SAVE, 'content' => $stmt->error));
				}
			}
				
			//close connection
			$stmt->close();
			
			//return result
			return $result;
            }
			
		function deleteMultiple($jsonData) {
			if(empty($jsonData)) 
				die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing for deleteMultiple')));
			//iterate over all items in json array
			$array = json_decode( $jsonData, true );
			if(count($array) ==0)
				die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing for deleteMultiple')));

            //connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}

			//prepare query
			$stmt = $handler->prepare("DELETE from ShoppingList WHERE item = ?");
			$stmt->bind_param('s', $item['itemTitle']);
			foreach($array as $item)
			{
				//execute query and check if successful
				if (!$stmt->execute()){
					$result = json_encode(array('type' => API_SUCCESS_DELETE, 'content' => ' Multiple items deleted'));
				} else {
					$result = json_encode(array('type' => API_ERROR_DELETE, 'content' => $stmt->error));
				}
			}
				
			//close connection
			$stmt->close();
			
			//return result
			return $result;
            }

        function update($itemName, $itemCount)
            {
			if(empty($itemName)||empty($itemCount)){
				die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing')));
				}
            //connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			
			//prepare query
			$stmt = $handler->prepare("UPDATE ShoppingList SET count = ? WHERE item = ?");
			$stmt->bind_param('ss', $itemCount, $itemName);

			//execute query and check if successful
			if ($stmt->execute()){
				$result = json_encode(array('type' => API_SUCCESS_UPDATE, 'content' => $itemName.' updated'));
			} else {
				$result = json_encode(array('type' => API_ERROR_UPDATE_, 'content' => $stmt->error));
			}
		
			//close connection
			$stmt->close();
						
			//return result
			return $result;
            }
			
        function delete($itemName)
            {
			if(empty($itemName)){
				die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing')));
			}
			//connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			
			//prepare query
			$stmt = $handler->prepare("DELETE FROM ShoppingList WHERE item = ?");
			$stmt->bind_param('s', $itemName);

			//execute query and check if successful
			if ($stmt->execute()){
				$result = json_encode(array('type' => API_SUCCESS_DELETE, 'content' => $itemName.' deleted'));
			} else {
				$result = json_encode(array('type' => API_ERROR_DELETE, 'content' => $stmt->error));
			}
		
			//close connection
			$stmt->close();
						
			//return result
			return $result;
            }
			
		function exists($itemName)
            {
			if(empty($itemName)){
				die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing')));
				}
			//connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			
			//prepare query
			$stmt = $handler->prepare("SELECT item FROM ShoppingList WHERE item = ?");
			$stmt->bind_param('s', $itemName);
			//execute query
			$stmt->execute();
			
			//bind the result
			$stmt->store_result();
			if ($stmt->num_rows > 0){
				$itemExists = true;
			} else {
				$itemExists = false;
			}
			
			//close connection
			$stmt->close();
			return $itemExists;
            
        }
			
		function listall()
            {
			//connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			
			//prepare query
			$stmt = $handler->prepare("SELECT item, count FROM ShoppingList ORDER BY item ASC");
			//execute query
			$stmt->execute();
			$stmt->store_result();
			//bind the result
			$stmt->bind_result($item_name, $item_count);
			
			//create array
			$stack = array();
			
			if($stmt->num_rows > 0) {
				//put all rows into array
				while ($stmt->fetch()) {
					$listdata = array('itemTitle' => $item_name, 'itemCount' => $item_count, 'checked' => false);
					array_push($stack, $listdata);
				}
				return json_encode(array('type' => API_SUCCESS_LIST, 'items' => $stack));
			} else {
				return json_encode(array('type' => API_SUCCESS_LIST_EMPTY));
			}
			
			//close connection
			$stmt->close();
            }
        
		function clear()
            {
			//connect to db
            $handler = new mysqli($this->server, $this->username, $this->password, $this->database);
			
			//check if connection successful
			if ($handler->connect_error) {
				die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $handler->connect_error)));
			}
			
			//prepare query
			$stmt = $handler->prepare("TRUNCATE ShoppingList");
			$stmt->bind_param('s', $itemName);

			//execute query and check if successful
			if ($stmt->execute()){
				$result = json_encode(array('type' => API_SUCCESS_CLEAR, 'content' => 'list cleared'));
			} else {
				$result = json_encode(array('type' => API_ERROR_CLEAR, 'content' => $stmt->error));
			}
		
			//close connection
			$stmt->close();
						
			//return result
			return $result;
            }
		
		}

?> 
