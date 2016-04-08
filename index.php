<?php
/****************** 
	Shopping list php web frontend
	Reference 	: https://github.com/GroundApps/ShoppingList_Backend
	Licence 	: http://www.gnu.org/licenses/agpl-3.0.fr.html
*******************/
/******************* Status : BETA
- PASSWORD CHECK IS PERFORMED VIA SESSION COOKIE
********************/

	require_once("CONSTANTS.php");
	include('config.php');
	header("ShoLiBackendVersion: ".BACKEND_VERSION);
	
	session_start();
	$login_error='';
	if ( isset ($_POST['password']) ) {
		$_SESSION['user_logged']=0;
		if(!function_exists('hash_equals')) {
			function hash_equals($a, $b) {
				$ret = strlen($a) ^ strlen($b);
				$ret |= array_sum(unpack("C*", $a^$b));
				return !$ret;
			}
		}
		if (hash_equals($authKey, crypt($_POST['password'], $authKey))){
			$_SESSION['user_logged']=1;
		} else {
			$login_error.="<p>Invalid API Key</p>";
		}
	}
?>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Shopping List</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script src="https://johnny.github.io/jquery-sortable/js/jquery-sortable-min.js"></script>
  <script src="js/main.js"></script>
  <link rel="stylesheet" type="text/css" href="css/main.css">
</head>
<body>
<section id="main">
<div class="row">
<div class="col-md-4 col-md-offset-4">
<h2>Shopping List</h2> 
<?
	if (! isset($_SESSION['user_logged']) || $_SESSION['user_logged'] != 1) {
?>
	<form class="form-inline" action="<? echo $_SERVER["REQUEST_URI"]; ?>" method="post">
		<?php echo $login_error; ?>
		<h2><small>API Key</small></h2>
		<div class="form-group">
			<div class="input-group">
         <div class="input-group-addon"><i class="fa fa-key"></i></div>
           <input type="password" class="form-control" name="password" placeholder="API Key" size="35">
         </div>
      </div>
		<br />
		<br />
		<button type="submit" class="btn btn-primary icon fa-sign-in" name="submit"> Login </button>
	</form>
<?
	} else {
?>
<button id="refresh" class="btn btn-primary icon fa-refresh">Refresh data</button> 
<? /* Add a category (well, will soon): <input type="text" size="15" id="addCategoryName" value=""/> */ ?>
<button id="checked" class="btn btn-primary icon fa-check">Remove checked</button> 
<div id="shopcategory"></div>
<br />
<?
	}
?>
 
</div>
</div>
</section>
</body>
</html>
