/****************** 
	Shopping list javascript for web frontend
	Reference 	: https://github.com/GroundApps/ShoppingList_Backend
	Licence 	: http://www.gnu.org/licenses/agpl-3.0.fr.html
	
*******************/

/*******************/

var apiurl="api.php";

/*******************/
var BACKEND_VERSION=1.0;
	
var API_SUCCESS_LIST=1000;
var API_SUCCESS_LIST_EMPTY=1001;
var API_SUCCESS_UPDATE=1002;
var API_SUCCESS_FAVORITE=1003;
var API_SUCCESS_DELETE=1004;
var API_SUCCESS_SAVE=1005;
var API_SUCCESS_CLEAR=1006;
	
var API_ERROR_SERVER=5000;
var API_ERROR_404=5001;
var API_ERROR_403=5002;
var API_ERROR_MISSING_FUNCTION=5003;
var API_ERROR_NO_DATABASE=5004;
var API_ERROR_CONFIG=5005;
var API_ERROR_UNKNOWN=5006;
var API_ERROR_DATABASE_CONNECT=5012;
var API_ERROR_MISSING_PARAMETER=5013;
var API_ERROR_FUNCTION_NOT_SPECIFIED=5014;
var API_ERROR_NOT_CONFIGURED=5015;
	
var API_ERROR_UPDATE_=6001;
var API_ERROR_FAVORITE=6002;
var API_ERROR_DELETE=6003;
var API_ERROR_SAVE=6004;
var API_ERROR_CLEAR=6005;
var API_ERROR_LIST=6006;
/*******************/

/* web page structure
  
<html>
	<head>
	</head>
	<body>
		<div id="shopcategory"> # accordeon
			<h3><CATEGORY NAME></h3>
			<div id="cat_<CATEGORY ID NUMBER>">
				<p> (Item add inputs and buttons) </p>
				<ul id="shopItems">
					<li id="shopItemEntry">
						<table>
							<tr> 
								<td class="itemCheck itemUncheckedTD" > </td>
								<td class="itemQty">
									<input type="text" size="6" id="itemQtyValue" value="1"/>
								</td>
								<td class="itemName"><NAME> </td>
								<td class="itemDelete"> <button>X</button> 	</td>
							</tr>
						</table>
					</li>
					<MORE items...>
				</ul>
			</div>
			<MORE categories....>
		</div>	
	</body>
</html>
  
  */
  
  var categoryList = new Array ("Uncategorized"); // TODO : make an object here
  
  $.ajaxSetup({
		url: apiurl,
		async: true,
		dataType: "json",
		type: "POST"
  });
  
  // add a category ( id: category ref in categoryList, name: display name )
  function addCategory(id,name) {
		var content= '<h2 id="title_'+id+'"><small>'+name+'</small></h2> <div id="cat_'+id+'"> <div style="display: table; width: 100%"><div style="display: table-cell;"><div class="input-group" style="float: left"><div class="input-group-addon"><i class="fa fa-shopping-cart"></i></div><input class="form-control" type="text" placeholder="Qty" name="addItemQty" id="addItemQty" value="1" style="width: 3.5em"><input id="addItemName" class="form-control" type="text" placeholder="Name" name="addItemName" value="" style="border-left: 0px; width: 70%"></div></div> <div style="display: table-cell;"><button id="addItemButton" class="btn btn-default" style="float: right"><i class="fa fa-cart-plus"></i></button></div></div> <br /> <ul id="shopItems"> </ul> </div> ';
		$("#shopcategory").append(content);
		var addButtonOBJ=$('#cat_'+id);
		addButtonOBJ.find( "#addItemButton" ).button().click(addItemClick);
		addButtonOBJ.find( "#shopItems" ).sortable({
			items: "li"	
		});
		//XXX $("#title_"+id).droppable({drop: function (event,ui) { categoryMove(event, ui); } });
		//XXX $("#title_"+id).droppable({drop: function (event,ui) { categoryMove(event, ui, $("#title_"+id)); }	});
	
		return addButtonOBJ;
  }
  // Returns category ref in categoryList, or -1 if not found
  function categoryID(name) {
		for (index = 0; index < categoryList.length; index++) {
			if (name == categoryList[index]) {
				return index;
			}
		}
		return -1;
  }
  /* Returns jquery object of a category identified by display name. 
	 Create categoryList entry and/or object if not found.*/
  function GetCategoryOBJ(name) {
		var index=categoryID(name);
		if (index ==-1) {
			index=categoryList.length;
			categoryList[index]=name;
		}
		var catObject=$('#cat_'+index);
		if (catObject.length ) {
			return catObject
		}
		return addCategory(index,name)
  }
  // delete item in list. Only used by the delete button
  function deleteItem(){
		var itemNameOBJ=$(this).parent().find(".itemName");
		var itemName=itemNameOBJ.html();
		//$(this).closest("#shopItemEntry").remove();
		var itemOBJ = $(this).closest("#shopItemEntry");

		$.ajax({
			data: {
				auth: "none",
				function: "delete",
				item: itemName,
			},
		  success: function (data) {
				if (data.type == API_SUCCESS_DELETE) {
					itemOBJ.remove();
				}
			}
		});
  }
  // Add an item in the current category. Only used by "add" button of category
  function addItemClick(){
		// Get the input values
		var addedItemObj=$(this).parent().parent().find("#addItemName");
		var addedQtyObj=$(this).parent().parent().find("#addItemQty");
		var addedItem=addedItemObj.val();
		var addedQty=addedQtyObj.val();
		// Get category pointer and add item
		var category=$(this).closest("div[id*='cat_']");

		if(addedQty>0) {
		}
		else {
			addedQty = 1;
			addedQtyObj.val(addedQty);
		}

		if(addedItem.length>0) {

			$.ajax({
				data: {
					auth: "none",
					function: "save",
					item: addedItem,
					count: addedQty
				},
			  success: function (data) {
					if (data.type == API_SUCCESS_SAVE) {
						addItem(category,addedItem,addedQty,false);
						// reset values
						addedItemObj.val("");
						addedQtyObj.val("1");
					}
					else if (data.type == API_SUCCESS_UPDATE) {
						// reset values
						addedItemObj.val("");
						addedQtyObj.val("1");
						// easier than finding out on which item to update the QTY
						refresh();
					}
				}
			});

			$(this).blur();
		}
		else addedItemObj.focus();
  }
  /* Add an item (name : display name, amount : amount of items, checked : bool )
	 to the category object categoryOBJ */
  function addItem(categoryOBJ, name, amount, checked) {
		var isChecked,isCheckedName;
		if (checked) { isChecked="itemCheckedTD"; isCheckedName=" itemCheckedName";} 
		else { isChecked="itemUncheckedTD";isCheckedName=""; }
		var itemVal=$('<li id="shopItemEntry" class="panel panel-default"> <table class="panel-body" width="100%"> <tr> ' + 
			'<td class="itemCheck '+ isChecked +'" > <i class="fa fa-2x"></i>' + 
			'</td> <td class="itemQty"> <input type="text" id="itemQtyValue" value="'+amount+'"/> ' +
			'</td> <td class="itemName'+isCheckedName+'">'+name+'</td> '+
			'<td class="itemDelete" align="right"> <button class="btn btn-default"><i class="fa fa-trash"></i></button> </td> '+
			'</tr> </table> </li>');
		$(itemVal).appendTo(categoryOBJ.find("#shopItems"));
		// set events
		$(itemVal).find( ".itemDelete" ).click(deleteItem);
		$(itemVal).find( ".itemCheck" ).click(checkItemToggle);
		$(itemVal).find( "#itemQtyValue" ).change(updateItem);
  }

  // Toggle check/uncheck of an item. Only used by "add" button of category
  function checkItemToggle() {
		var itemNameOBJ=$(this).parent().find(".itemName");
		if ($(this).hasClass("itemUncheckedTD") ) 
		{
			$(this).removeClass("itemUncheckedTD");	
			$(this).addClass("itemCheckedTD");
			itemNameOBJ.addClass("itemCheckedName");
		} else 
		{
			$(this).removeClass("itemCheckedTD");
			$(this).addClass("itemUncheckedTD");
			itemNameOBJ.removeClass("itemCheckedName");	
		}
  }
  
  // Update item linked by itemObject
  function updateItem()
  {
		var itemOBJ = $(this).closest("#shopItemEntry");
		var itemName=itemOBJ.find(".itemName").html();
		var itemQty=itemOBJ.find("#itemQtyValue").val();

		if(itemQty>0) {
		}
		else {
			itemQty = 1;
			itemOBJ.find("#itemQtyValue").val(itemQty);
		}

		$.ajax({
			data: {
				auth: "none",
				function: "save",
				item: itemName,
				count: itemQty
			},
		  success: function (data) {
				if (data.type != API_SUCCESS_UPDATE) {
					refresh();
				}
			}
		});

  }
  
  // Delete all and refresh from DB
  function refresh(){
		var addedItem="Error";
		var addedQty="Error"; 
		var	addedchecked="Error";
		var addedcategory="Error";

		//Remove all
		$("#shopcategory").empty();
		categoryList=[];
		var defcategory=GetCategoryOBJ("Uncategorized");
		
		$.ajax({
			data: {
				auth: "none",
				function: "listall"
			},
		  success: function (data) {

		    //alert(data.type);
				if (data.type == API_SUCCESS_LIST) {
					for (var x = 0; x < data.items.length; x++) {
						addedItem = data.items[x].itemTitle;
						addedQty = data.items[x].itemCount;
						checked = data.items[x].checked;
						addedcategory = data.items[x].itemCategory;
						if (addedcategory == null) {
							addItem(defcategory, addedItem, addedQty, checked);
						} else {
							var categoryOBJ=GetCategoryOBJ(addedcategory);
							addItem(categoryOBJ, addedItem, addedQty, checked);
						}
					}
					//XXX $( "#shopcategory" ).accordion("refresh");
				} else
				if (data.type == API_SUCCESS_LIST_EMPTY) {
					alert ("Empty list");
					//TODO
				} else {
					alert ("Error : "+data.content);
				}
			} //success

		}); //ajax

		if($(this).blur) $(this).blur();
  }
  
  function removechecked(){
		var checked = $(".itemCheckedTD");
		if (checked.length>0) {

			var data = '[';
			for (index = 0; index < checked.length; index++) {
				data = data + '{"itemTitle":"'+$(checked[index]).parent().find(".itemName").html()+'"},';
			}
			data = data.substr(0,data.length-1) + ']';

			$.ajax({
				data: {
					auth: "none",
					function: "deleteMultiple",
					jsonArray: data
				},
			  success: function (data) {
					if (data.type == API_SUCCESS_DELETE) {
						refresh();
					}
				}
			});

		}
		$(this).blur();
  }
  
  function getItemValues(itemOBJ) {
		var retVal = new Array();
		retVal['checked']=itemOBJ.find(".itemCheck").hasClass("itemCheckedTD");
		retVal['amount']=itemOBJ.find("#itemQtyValue").val();
		retVal['name']=itemOBJ.find(".itemName").html();
		retVal['category']=itemOBJ.closest("div[id*='cat_']").attr("id");
		retVal['category'] = retVal['category'].substr(4);
		return retVal;
  }
  
  // Item is dropped in another category.Only used by drop event of category
  function categoryMove(event, ui, category) {
		//alert("Dropped");
		var itemValues = new Array ();
		itemValues = getItemValues(ui.draggable);
		var catID = category.attr("id").substr(6);  // Remove this
		var newCategory = $("#cat_"+catID); 
		ui.draggable.remove();
		addItem(newCategory,itemValues['name'],itemValues['amount'],itemValues['checked']);
  }
  
  // Doc ready functions
  $(function() {

		// for test cat
		$( ".itemDelete" ).click(deleteItem);
		$( "#shopItems" ).sortable({ items: "li" });
		$( "#addItemButton" ).button().click(addItemClick);
		$( ".itemCheck" ).click(checkItemToggle());

		// Main page  
		$( "#refresh" ).button().click(refresh);
		$( "#checked" ).button().click(removechecked);
		$( "#itemQtyValue" ).change(updateItem);

		/* XXX
		// Categories accordeon
		$( "#shopcategory" ).accordion({
			heightStyle: content,collapsible: true
		});
		*/

		if ($("#shopcategory").length) refresh();	
  });

