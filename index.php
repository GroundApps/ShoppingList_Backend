<?php
/****************** 
	Shopping list php web frontend
	Reference 	: https://github.com/GroundApps/ShoppingList_Backend
	Licence 	: http://www.gnu.org/licenses/agpl-3.0.fr.html
*******************/
/******************* Status : ALPHA
- PASSWORD CHECK NOT WORKING AND BYPASSED
- Uses listall only, so read only from DB point of view
********************/

	require_once("CONSTANTS.php");
	include('config.php');
	header("ShoLiBackendVersion: ".BACKEND_VERSION);
	
	session_start();
	$password_fail="";
	if ( isset ($_POST['password']) ) {
		 if(!function_exists('hash_equals')) {
			function hash_equals($a, $b) {
				$ret = strlen($a) ^ strlen($b);
				$ret |= array_sum(unpack("C*", $a^$b));
				return !$ret;
			}
		 }
		if (hash_equals($authKey, crypt($auth, $authKey))){
			$_SESSION['user_logged']=1;
		} else {
			$password_fail.="<p>Wrong password</p>";
			//$_SESSION['user_logged']=0;
			// Password check always fails for now
// ************************ TODO : REMOVE THIS WHEN PASSWORD CHECK IS OK ! ***************
// ************************ NOT FOR PRODUCTION ENVIRONNEMENT  ****************************
			$_SESSION['user_logged']=1;
		}
	}
	if (! isset($_SESSION['user_logged']) || $_SESSION['user_logged'] != 1) {
?>
<html><head></head><body>
	<h3>Shopping List</h3><br>
	<form action="index.php" method="post">
		<?php echo $password_fail; ?>
		Password : <input type="text" name="password"><br>
		<input type="submit" value="Submit">
	</form>
</body></html>
<?php
	exit (0);
	}
?>
<html>
<head>
  <meta charset="utf-8">
  <title>Shopping List</title>
  <script src="lib/web/jquery/jquery-1.11.3.min.js"></script>
  <script src="lib/web/jquery/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="lib/web/jquery/jquery-ui.min.css">
  <script src="lib/web/js/sholi.js"></script>
  <link rel="stylesheet" type="text/css" href="lib/web/js/sholi.css">
</head>
<body>
<h1>Shopping List</h1> 
<button id="refresh">Refresh data</button> 
Add a category (well, will soon): <input type="text" size="15" id="addCategoryName" value=""/>
<div id="shopcategory">
  <h3 id="title_0">Test page : hit refresh data button</h3>
  <div id="cat_0">
    <p>
	Qty : <input type="text" size="6" id="addItemQty" value="1"/> 
 	Name : <input type="text" size="15" id="addItemName" value=""/>	
	<button id="addItemButton">Add</button>
    </p>
    <ul id="shopItems">
	<li id="shopItemEntry">
	   <table>
	   <tr>
	    <td class="itemCheck itemUncheckedTD" > </td>
		<td class="itemQty">
			<input type="text" size="6" id="itemQtyValue" value="2"/>
		</td>
		<td class="itemName">
			testitem
		</td>
		<td class="itemDelete">
			<button>X</button>
		</td>
	   </tr>
 	   </table>
	</li>
    </ul>
  </div>
  <h3 id="title_1">Test page 2: hit refresh data button</h3>
  <div id="cat_1">
    <p>
	Qty : <input type="text" size="6" id="addItemQty" value="1"/> 
 	Name : <input type="text" size="15" id="addItemName" value=""/>	
	<button id="addItemButton">Add</button>
    </p>
    <ul id="shopItems">
		<li id="shopItemEntry">
		   <table>
			   <tr>
					<td class="itemCheck itemUncheckedTD" > </td>
					<td class="itemQty">
						<input type="text" size="6" id="itemQtyValue" value="2"/>
					</td>
					<td class="itemName">
						testitem
					</td>
					<td class="itemDelete">
						<button>X</button>
					</td>
			   </tr>
		   </table>
		</li>
    </ul>
  </div>  
</div>
 
 
</body>
</html>
