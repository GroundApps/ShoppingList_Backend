<?php
    
    //hashed password used for app
    //to get a hash of your password, run 'php config.php <password>' on the command line.
    //It is important to put the hashed password into single quotes ''!
	$authKey = '';
	
	$dataBase = "SQLite";
	//only for SQLite
	$SQLiteConfig = [
        'file' => "shoppinglist.sqlite",
	];
	//only for MySQL
	$MySQLConfig = [
        'host' => "host",
        'db' => "db",
        'user' => "user",
        'password' => "password",
    ];
    
    if($argc > 1){
		echo password_hash($argv[1], PASSWORD_BCRYPT);
	}
?>

