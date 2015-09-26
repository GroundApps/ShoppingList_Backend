<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<title>Install ShoppingList</title>
</head>
<body>
<div class="row">
<div class="col-md-4 col-md-offset-5">
<?php

if (version_compare(phpversion(), '5.3.3', '>=') AND version_compare(phpversion(), '5.5', '<'))
    require_once('func.inc.php');

//Start on submit form
if(isset($_POST['createConfig'])) {
    function generateRandomPWD($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function get_qrcode($_apikey) {
        include('lib/phpqrcode-git/lib/full/qrlib.php');
        $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'];
        $uri = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
        $api_url = $url . $uri . "api.php";
        $ssl = (($_SERVER['SERVER_PORT'] == 443) ? "true" : "false");

        $qr_code = "{ \"url\": \"$api_url\", \"apikey\": \"$_apikey\", \"ssl\": $ssl}";

        ob_start();
        QRCode::png($qr_code);
        $image_string = base64_encode(ob_get_clean());
        header('Content-Type: text/html');

        return $image_string;
    }

    //set vars
    //hash api key with bcrypt
    $apikey = password_hash($_POST['apikey'], PASSWORD_BCRYPT);
    $dbtype = $_POST['type'];
    $dbhost = $_POST['hostname'];
    $dbname =  (isset($_POST['database']) ? $_POST['database'] : "");
    $dbuser = $_POST['dbuser'];
    $dbpassword = $_POST['dbpassword'];
    $createDBUser = (isset($_POST['createDBUser']) ? $_POST['createDBUser'] : "");
    $dbrandom_pwd = generateRandomPWD();

    $filename = 'shoppinglist.sqlite';
    //set up .htaccess rules for sqlite database if sqlite is used

    $htaccess = "Options ALL -Indexes\nDirectoryIndex api.php";
    if($dbtype === 'SQLite') {
        $htaccess .= "\n<files ".$filename.">\norder allow,deny\ndeny from all\n</files>\n";
    }

    file_put_contents('.htaccess', $htaccess);
    //create config file value

    if($createDBUser == "true") {
        $config_access = '
            <?php
            $authKey = \''.$apikey.'\';

        $dataBase = "'.$dbtype.'";
        //only for SQLite
        $SQLiteConfig = array(
                \'file\' => "'.$filename.'",
                );
        //only for MySQL
        $MySQLConfig = array(
                \'host\' => "'.$dbhost.'",
                \'db\' => "shopping",
                \'user\' => "ShoppingListUser",
                \'password\' => "'.$dbrandom_pwd.'",
                );
        ?>';

        //mysql dump for root access
        $dbdump_access = "CREATE USER 'ShoppingListUser'@'".$dbhost."' IDENTIFIED BY '".$dbrandom_pwd."';";
        $dbdump_access2 = "CREATE DATABASE shopping;";
        $dbdump_access3 = "GRANT ALL PRIVILEGES ON shopping.ShoppingList TO 'ShoppingListUser'@'".$dbhost."';";

    } else {

        $config = '
            <?php
            $authKey = \''.$apikey.'\';

        $dataBase = "'.$dbtype.'";
        //only for SQLite
        $SQLiteConfig = array(
                \'file\' => "'.$filename.'",
                );
        //only for MySQL
        $MySQLConfig = array(
                \'host\' => "'.$dbhost.'",
                \'db\' => "'.$dbname.'",
                \'user\' => "'.$dbuser.'",
                \'password\' => "'.$dbpassword.'",
                );
        ?>';
    }

    //mysql dump
    $dbdump = "
        CREATE TABLE ShoppingList (
                item VARCHAR(255),
                count VARCHAR(255),
                RID int(11) NOT NULL auto_increment,
                primary KEY (RID))
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;";

    //try to open/create config.php file
    //success: write config value
    //error: message and get out config.php value
    if($handler = fopen('config.php', 'w')) {
        if($createDBUser == "true")
            fwrite($handler, $config_access);
        else
            fwrite($handler, $config);

        fclose($handler);
    } else {
        echo <<<EOCONFIG
            <h2>Config.php</h2>
            <div class="alert alert-danger" role="alert">It was not possible to create the config.php file. Please copy the code and paste it in the file.</div>
            <div class="form-group">
            <label for="msg_config">config.php</label>
            <textarea class="form-control" rows="15" min-height="300px" id="msg_config">
EOCONFIG;
        if($createDBUser == "true")
            echo $config_access;
        else
            echo $config;

        echo '</textarea>';
    }

    //when DB Type MySQL create table
    if($dbtype == "MySQL") {
        if($createDBUser == "true")
            $handler = new mysqli($dbhost, $dbuser, $dbpassword);
        else
            $handler = new mysqli($dbhost, $dbuser, $dbpassword, $dbname);

        //check if connection successful
        if ($handler->connect_error)
            die('<div class="alert alert-danger" role="alert">No Connection to your Database. Please correct your Informations!</div>');

        //prepare query
        if($createDBUser == "true") {
            $stmt1 = $handler->prepare($dbdump_access);
            $stmt2 = $handler->prepare($dbdump_access2);
            $stmt3 = $handler->prepare($dbdump_access3);
        }
        else
            $stmt = $handler->prepare($dbdump);

        //execute query and check if successful
        if($createDBUser == "true") {
            if($stmt1->execute() && $stmt2->execute() && $stmt3->execute()) {
                $mysql_error = false;
                $handler = new mysqli($dbhost, $dbuser, $dbpassword, 'shopping');
                $stmt4 = $handler->prepare($dbdump);
                if($stmt4->execute())
                    $mysql_error = false;
                else
                    $mysql_error = true;
            }
            else {
                $mysql_error = true;
            }
            //close connection
            $stmt1->close();
            $stmt2->close();
            $stmt3->close();
            $stmt4->close();
        } else {
            if($stmt->execute())
                $mysql_error = false;
            else
                $mysql_error = true;

            //close connection
            $stmt->close();
        }
        if (!$mysql_error) {
            echo '<div class="alert alert-success" role="alert">All done! Please delete the INSTALL.php file!</div>';

            echo '<img src="data:image/png;base64,' . get_qrcode($_POST['apikey']) . '"/><br />
                <p>Open your app and scan code for automatically configuration</p>';

        } else {
            echo '<div class="alert alert-danger" role="alert">
                There was an Error in the MySQL Statment!
                </div>';
        }
    } else {
        echo '<div class="alert alert-success" role="alert">All done! Please delete the INSTALL.php file!</div>';

        echo '<img src="data:image/png;base64,' . get_qrcode($_POST['apikey']) . '"/><br />
            <p>Open your app and scan code for automatically configuration</p>';
    }
} else {
?>
    <script>
    jQuery(document).ready(function() {
        jQuery('#type').change(function(){
            if(jQuery('#type').val() == 'SQLite') {
                jQuery('#MySQL').hide();
            } else {
                jQuery('#MySQL').show();
            }
        });
        jQuery('#createDBUser').change(function() {
            if(this.checked) {
                $('#createDBMessage').show();
                $('#dbname').hide();
            }
            else {
                $('#createDBMessage').hide();
                $('#dbname').show();
            }
        });


    });
    </script>
    <h2>Install ShoppingList Database</h2>
    <h2><small>API Key</small></h2>
    <form class="form-inline" action="INSTALL.php" method="post">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-key"></i></div>
                <input type="password" class="form-control" name="apikey" placeholder="API Key" size="35">
            </div>
        </div>

        <h2><small>Database Setup</small></h2>
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-database"></i></div>
                <select class="form-control" id="type" name="type">
                    <option value="MySQL" selected>MySQL</option>
                    <option value="SQLite">SQLite</option>
                </select>
            </div>
        </div><br style="font-size:5px"/>
        <div id="MySQL">
            <h3><small><input type="checkbox" name="createDBUser" id="createDBUser" value="true"/>&nbsp;Do you want to create a user and database for that App? </small></h3>
            <div class="alert alert-info" role="alert" id="createDBMessage" style="display:none;">Please fill the form with your root MySQL data. After this installation this data will delete and the app will take the new created user for the operations.</div>
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-globe"></i></div>
                        <input type="text" class="form-control" name="hostname" placeholder="Host" size="35">
                    </div>
                </div>
                <br /><br style="font-size:5px"/>
                <div class="form-group" id="dbname">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-database"></i></div>
                        <input type="text" class="form-control" name="database" placeholder="Database Name" size="35">
                    </div>
                    <br /><br style="font-size:5px"/>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-user"></i></div>
                        <input type="text" class="form-control" name="dbuser" placeholder="Database User" size="35">
                    </div>
            </div>
            <br /><br style="font-size:5px"/>
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-key"></i></div>
                    <input type="password" class="form-control" name="dbpassword" placeholder="Database Password" size="35">
                </div>
            </div>
        </div>
        <br /><br style="font-size:5px"/>
        <small>Click Create to write the necessary configuration to config.php and in case of MySQL to create a table for the list storage in the given database.</small><br ><br >
        <button type="submit" class="btn btn-primary" name="createConfig"> Create </button></div>
    </form>
</div>
</div>
<?php
}
?>
</body>
</html>
