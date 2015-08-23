<?php

    class DataBase{
        
        var $db;
        
        function checkaccess($filename){
			$htaccess = file_get_contents('.htaccess');
			if(strpos($htaccess, $filename) == false){
				$denystring = "\n<files $filename>\norder allow,deny\ndeny from all\n</files>\n";
				file_put_contents('.htaccess', $denystring, FILE_APPEND);
			}
		}
				
        
        function __construct($args){
            $dbfile = $args['file'];
            $this->checkaccess($dbfile);
            try{
                $this->db = new SQLite3($dbfile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            }catch(Exception $e){
                die(json_encode(array('code' => 'error', 'comment' => $e->getMessage())));
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
            $stack = [];
            if(!$resultQuery){
                $dummy = [
                    'item' => "",
                    'count' => 0,
                ];
                array_push($stack, $dummy);
                return $stack;
            }
            while($item = $resultQuery->fetchArray()){
                $itemData = [
                    'item' => $item['ITEM'],
                    'count' => $item['COUNT'],
                ];
                array_push($stack, $itemData);
            }
            return json_encode($stack);
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
                $result = json_encode(array('code' => 'success', 'comment' => $item.' saved.'));
            }else{
                $result = json_encode(array('code' => 'error', 'comment' => 'Saving failed'));
            }
            return $result;
        }
         
        function update($item, $count){
            $resultQuery = $this->db->query("UPDATE itemlist SET COUNT = ".$count." WHERE ITEM = '".$item."';");
            if($resultQuery){
                $result = json_encode(array('code' => 'success', 'comment' => $item.' updated.'));
            }else{
                $result = json_encode(array('code' => 'error', 'comment' => 'Updating failed'));
            }
            return $result;
        }
         
        function delete($item){
            $resultQuery = $this->db->query("DELETE FROM itemlist WHERE ITEM = '".$item."';");
            if($resultQuery){
                $result = json_encode(array('code' => 'success', 'comment' => $item.' deleted.'));
            }else{
                $result = json_encode(array('code' => 'error', 'comment' => 'Deleting failed'));
            }
            return $result;
        }
         
        function clear(){
            $resultQuery = $this->db->query("DELETE FROM itemlist;");
            $this->db->exec("VACUUM;");
            if($resultQuery){
                $result = json_encode(array('code' => 'success', 'comment' => 'List cleared'));
            }else{
                $result = json_encode(array('code' => 'error', 'comment' => 'Clearing failed'));
            }
            return $result;
        }
         
         
    }
    
?>
