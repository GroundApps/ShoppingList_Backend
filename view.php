<?php
/****************** 
	Shopping list php web frontend
	Reference 	: https://github.com/GroundApps/ShoppingList_Backend
	Licence 	: http://www.gnu.org/licenses/agpl-3.0.fr.html
*******************/
/******************* Status : BETA
********************/

	require_once("CONSTANTS.php");
	include('config.php');
	header("ShoLiBackendVersion: ".BACKEND_VERSION);
	
	session_start();
	$_SESSION['user_logged']=0;
?>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Shopping List View</title>
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
<h2>Shopping List View</h2> 
<?
	if ( isset ($_GET['key']) && $_GET['key'] == substr(crypt($authKey, 'share'), -16) )
	{
		$_SESSION['user_read']=1;
?>
<button id="refresh" class="btn btn-primary icon fa-refresh">Refresh data</button> 
<div id="shopcategory" data="view"></div>
<br />
<?
	}
?>
 
</div>
</div>
</section>
</body>
</html>
