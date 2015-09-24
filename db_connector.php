<?php

    include('CONSTANTS.php');
    
    class DataBase{
        
        var $db;
        var $type;
        
        function __construct($dbtype, $dbargs){
            $type = $dbtype;
            switch($dbtype){
                case 'SQLite':
                    $db_pdo="sqlite:".$dbargs['file'];
                    try{
                        $db = new PDO($db_pdo);
                    }catch(PDOException $e){
                        die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $e->getMessage())));
                    }
                    break;
                case 'MySQL':
                    $db_pdo="mysql:host=".$dbargs['host'].";dbname=".$dbargs['db'];
                    try{
                        $db = new PDO($db_pdo, $dbargs['user'], $dbargs['password']);
                    }catch(PDOException $e){
                        die(json_encode(array('type' => API_ERROR_DATABASE_CONNECT, 'content' => $e->getMessage())));
                    }
                    break;
                default:
                    die(json_encode(array('type' => API_ERROR_MISSING_PARAMETER, 'content' => "Missing database parameters.")));
            }
        }
        
    }
