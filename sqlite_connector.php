<?php

    class DataBase{
        
        var $db;
        
        function __construct($args){
            $dbfile = $args['file'];
            try{
                $this->db = new SQLite3($dbfile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            }catch(Exception $e){
                die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $e->getMessage())));
            }
            $resultQuery = $this->db->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table' AND name='itemlist'");
            $row = $resultQuery->fetchArray();
            if($row['count'] == 0){
                $this->db->exec("CREATE TABLE itemlist(ITEM TEXT PRIMARY KEY NOT NULL, COUNT INT NOT NULL);");
            }
        }
        
        function __destructor(){
            $this->db->close();
        }
         
        function listall(){
            $resultQuery = $this->db->query("SELECT ITEM, COUNT FROM itemlist ORDER BY ITEM ASC;");
            $stack = array();
            if(!$resultQuery){
                return json_encode(array('type' => API_SUCCESS_LIST_EMPTY));
            }
            while($item = $resultQuery->fetchArray()){
                $itemData = array(
                    'itemTitle' => $item['ITEM'],
                    'itemCount' => $item['COUNT'],
					'checked' => false
                );
                array_push($stack, $itemData);
            }
            if(count($stack) == 0){
                return json_encode(array('type' => API_SUCCESS_LIST_EMPTY));
            }else{
                return json_encode(array('type' => API_SUCCESS_LIST, 'items' => $stack));
            }
        }
         
        function exists($item){
            $resultQuery = $this->db->query("SELECT COUNT(*) as count FROM itemlist WHERE ITEM = '".$item."';");
            $row = $resultQuery->fetchArray();
            if($row['count'] > 0){
                return True;
            }else{
                return False;
            }
        }
         
        function save($item, $count){
            $resultQuery = $this->db->query("INSERT INTO itemlist (ITEM, COUNT) VALUES('".$item."', ".$count.");");
            if($resultQuery){
                $result = json_encode(array('type' => API_SUCCESS_SAVE, 'content' => $item.' saved.'));
            }else{
                $result = json_encode(array('type' => API_ERROR_SAVE, 'content' => 'Saving failed'));
            }
            return $result;
        }
		
		function saveMultiple($jsonData){
			if(empty($jsonData)) {
					die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing for saveMultiple')));
				}
			//iterate over all items in json array
			$array = json_decode( $jsonData, true );
			foreach($array as $item)
			{
				$resultQuery = $this->db->query("INSERT INTO itemlist (ITEM, COUNT) VALUES('".$item['itemTitle']."', ".$item['itemCount'].");");
			}
            if($resultQuery){
                $result = json_encode(array('type' => API_SUCCESS_SAVE, 'content' => 'Multiple items saved'));
            }else{
                $result = json_encode(array('type' => API_ERROR_SAVE, 'content' => 'Saving failed'));
            }
            return $result;
        }
         
        function update($item, $count){
            $resultQuery = $this->db->query("UPDATE itemlist SET COUNT = ".$count." WHERE ITEM = '".$item."';");
            if($resultQuery){
                $result = json_encode(array('type' => API_SUCCESS_UPDATE, 'content' => $item.' updated.'));
            }else{
                $result = json_encode(array('type' => API_ERROR_UPDATE_, 'content' => 'Updating failed'));
            }
            return $result;
        }
         
		function deleteMultiple($jsonData){
			if(empty($jsonData)) {
					die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing for deleteMultiple')));
			}
			//iterate over all items in json array
			$array = json_decode( $jsonData, true );
			foreach($array as $item)
			{
				$resultQuery = $this->db->query("DELETE FROM itemlist WHERE ITEM = '".$item['itemTitle']."';");
			}	
            if($resultQuery){
                $result = json_encode(array('type' => API_SUCCESS_DELETE, 'content' => 'Multiple items deleted'));
            }else{
                $result = json_encode(array('type' => API_ERROR_DELETE, 'content' => 'Deleting failed'));
            }
            return $result;
        } 
		 
        function delete($item){
            $resultQuery = $this->db->query("DELETE FROM itemlist WHERE ITEM = '".$item."';");
            if($resultQuery){
                $result = json_encode(array('type' => API_SUCCESS_DELETE, 'content' => $item.' deleted.'));
            }else{
                $result = json_encode(array('type' => API_ERROR_DELETE, 'content' => 'Deleting failed'));
            }
            return $result;
        }
         
        function clear(){
            $resultQuery = $this->db->query("DELETE FROM itemlist;");
            $this->db->exec("VACUUM;");
            if($resultQuery){
                $result = json_encode(array('type' => API_SUCCESS_CLEAR, 'content' => 'List cleared'));
            }else{
                $result = json_encode(array('type' => API_ERROR_CLEAR, 'content' => 'Clearing failed'));
            }
            return $result;
        }
         
         
    }
    
?>
