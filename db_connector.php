<?php

    include('CONSTANTS.php');
    
    class DataBase{
        
        var $db;
        var $type;
        var $table = "shoppinglist";
        
        function __construct($dbtype, $dbargs){
            $this->type = $dbtype;
            switch($dbtype){
                case 'SQLite':
                    $db_pdo="sqlite:".$dbargs['file'];
                    try{
                        $this->db = new PDO($db_pdo);
                    }catch(PDOException $e){
                        die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $e->getMessage())));
                    }
                    break;
                case 'MySQL':
                    $db_pdo="mysql:host=".$dbargs['host'].";dbname=".$dbargs['db'];
                    try{
                        $this->db = new PDO($db_pdo, $dbargs['user'], $dbargs['password']);
                    }catch(PDOException $e){
                        die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $e->getMessage())));
                    }
                    break;
                default:
                    die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => "Missing database parameters.")));
            }
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        function init(){
            $sql = "CREATE table $this->table(
                item STRING PRIMARY KEY,
                count INT NOT NULL,
                checked INT NOT NULL,
                category STRING);";
            try{
                $this->db->exec($sql);
            }catch(PDOException $e){
                //die(json_encode(array('type' => API_ERROR_UNKNOWN, 'content' => $e->getMessage()))); //uncomment after init() has been put to INSTALL.php
            }
        }
        
        function listall(){
            try{
                $sql = "SELECT * FROM $this->table;";
                $val = $this->db->query($sql);
                $stack = array();
                foreach($val as $row){
                    array_push($stack, array(
                        'itemTitle' => $row['item'],
                        'itemCount' => $row['count'],
                        'checked' => (bool)$row['checked'],
                        'itemCategory' => $row['category']));
                }
                if(count($stack) == 0){
                    return json_encode(array('type' => API_SUCCESS_LIST_EMPTY));
                }else{
                    return json_encode(array('type' => API_SUCCESS_LIST, 'items' => $stack));
                }
            }catch(PDOException $e){
                die(json_encode(array('type' => API_ERROR_LIST, 'content' => $e->getMessage())));
            }
        }
        
        function exists($item){
            $stmt = $this->db->prepare("SELECT * from $this->table WHERE item=:item;");
            $stmt->bindParam(':item', $item, PDO::PARAM_STR);
            $stmt->execute();
            return (bool)count($stmt->fetchAll());
        }
        
        function save($item, $count){
            try{
                $checked = (int)false;
                $stmt = $this->db->prepare("INSERT INTO $this->table (item, count, checked) VALUES (:item, :count, :checked);");
                $stmt->bindParam(':item', $item, PDO::PARAM_STR);
                $stmt->bindParam(':count', $count, PDO::PARAM_INT);
                $stmt->bindParam(':checked', $checked, PDO::PARAM_INT);
                $stmt->execute();
                return json_encode(array('type' => API_SUCCESS_SAVE, 'content' => $item.' saved.'));
            }catch(PDOException $e){
                return json_encode(array('type' => API_ERROR_SAVE, 'content' => $e->getMessage()));
            }
        }
        
        function saveMultiple($jsonData){
            if(empty($jsonData)){
                die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'parameter missing for saveMultiple')));
            }
            echo $jsonData."\n";
            var_dump(json_decode($jsonData));
            $itemList = json_decode($jsonData, true);
            try{
                $stmt = $this->db->prepare("INSERT INTO $this->table (item, count, checked) VALUES (:item, :count, :checked);");
                $checked = (int)false;
                $stmt->bindParam(':checked', $checked, PDO::PARAM_INT);
                foreach($itemList as $item){
                    $stmt->bindParam(':item', $item['itemTitle'], PDO::PARAM_STR);
                    $stmt->bindParam(':count', $item['itemCount'], PDO::PARAM_INT);
                    $stmt->execute();
                }
                return json_encode(array('type' => API_SUCCESS_SAVE, 'content' => 'Multiple items saved.'));
            }catch(PDOException $e){
                return json_encode(array('type' => API_ERROR_SAVE, 'content' => $e->getMessage()));
            }
        }
        
        function update($item, $count){
            try{
                $stmt = $this->db->prepare("UPDATE $this->table SET count=:count WHERE item=:item;");
                $stmt->bindParam(':item', $item, PDO::PARAM_STR);
                $stmt->bindParam(':count', $count, PDO::PARAM_INT);
                $stmt->execute();
                return json_encode(array('type' => API_SUCCESS_UPDATE, 'content' => 'Update successfull.'));
            }catch(PDOException $e){
                return json_encode(array('type' => API_ERROR_UPDATE_, 'content' => $e->getMessage()));
            }
        }
        
        function deleteMultiple($jsonData){
            if(empty($jsonData)) {
                die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => 'Parameter missing for deleteMultiple.')));
            }
            $itemList = json_decode($jsonData, true);
            try{
                $stmt = $this->db->prepare("DELETE FROM $this->table WHERE item=:item;");
                foreach($itemList as $item){
                    $stmt->bindParam(':item', $item['itemTitle'], PDO::PARAM_STR);
                    $stmt->execute();
                }
                return json_encode(array('type' => API_SUCCESS_DELETE, 'content' => 'Multiple items deleted.'));
            }catch(PDOException $e){
                return json_encode(array('type' => API_ERROR_DELETE, 'content' => $e->getMessage()));
            }
        }
        
        function delete($item){
            try{
                $stmt = $this->db->prepare("DELETE FROM $this->table WHERE item=:item");
                $stmt->bindParam(':item', $item, PDO::PARAM_STR);
                $stmt->execute();
                return json_encode(array('type' => API_SUCCESS_DELETE, 'content' => 'Item deleted.'));
            }catch(PDOException $e){
                return json_encode(array('type' => API_ERROR_DELETE, 'content' => $e->getMessage()));
            }
        }
        
        function clear(){
            try{
                $stmt = $this->db->exec("TRUNCATE TABLE $this->table;");
                return json_encode(array('type' => API_SUCCESS_CLEAR, 'content' => 'Database cleared.'));
            }catch(PDOException $e){
                return json_encode(array('type' => API_ERROR_CLEAR, 'content' => $e->getMessage()));
            }
        }
                
    }
