<?php
if(!function_exists("addProduct")) {
function addUpdateProduct($id=0,$mode=0,$createdbyID=0, $matchtitle = 0, $title="", $description="", $price = 0, $rrp = 0, $sku = "", $imageURL = "", $length = "", $width = "", $height = "", $weight = "", $volume = "", $int_length = "", $int_width="", $int_height="", $area = "", $regionID = 1, $mpn = "", $upc = "", $condition = "",  $longID="", $instock="", $availabledate="", $testmode=0) { 
	global $database_aquiescedb, $aquiescedb;
	$returnvalue = array();
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	$select = "SELECT defaultcondition FROM productprefs WHERE ID = ".$regionID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error()." - ".$select);
	$prefs= mysql_fetch_assoc($result);
	
	
	// if match title = 1 the system will see if product with sam title exits then add other
	$price = floatval(preg_replace("/[^0-9\.]/", "", $price)); // remove currency symbols, commas, etc.
	$sku_cleaned = preg_replace("/[^a-zA-Z0-9_\-]/", "", $sku);
	
	$imageURL = fixImageURL($imageURL);	
	$availabledate = $availabledate !="" ? date('Y-m-d H:i:s',strtotime($availabledate)) : "";
	$longID = ($longID=="" && $title!="") ? createURLname("", $title, "-",  "product") : $longID;
	$condition=  ($condition !="") ? $condition : intval($prefs['defaultcondition']);	
	$select = "SELECT product.ID, product.metakeywords FROM product ";
	if($id != 0) { 
		$select .= " WHERE ID = ".intval($id);
	} else {
		$select .= " LEFT JOIN productinregion ON (product.ID = productinregion.productID) WHERE productinregion.regionID = ".$regionID." AND (false";
		$select .= ($sku != "") ? " OR product.sku = ".GetSQLValueString($sku, "text") : "";	
		$select .= ($matchtitle == 1) ? " OR product.title = ".GetSQLValueString($title, "text") : "";
		$select .= ") LIMIT 1";
	}
	$result = mysql_query($select, $aquiescedb) or die(mysql_error()." - ".$select);
	 // if unique product code OR ID is the same as existing product, just update fields that have values
	if(mysql_num_rows($result)==0 && $_POST['import_mode']!=1 && $sku !="") { // check product options for an SKU match
		$select = "SELECT productoptions.ID, productoptions.productID FROM productoptions WHERE stockcode = ".GetSQLValueString($sku, "text");
		$optionresult = mysql_query($select, $aquiescedb) or die(mysql_error()." - ".$select);
	}
	
	if(mysql_num_rows($result)>0 && $_POST['import_mode']!=1) {
		$row = mysql_fetch_assoc($result);
		$update = "UPDATE product SET ";
		$update .= ($title !="") ? "title = ".GetSQLValueString($title, "text").", " : ""; 
		$update .= ($title !="" && $row['metakeywords']=="") ? "metakeywords = ".GetSQLValueString($title, "text").", " : ""; 
		$update .= ($longID !="") ? "longID = ".GetSQLValueString($longID, "text").", " : ""; 
		$update .= ($description !="") ? "`description` = ".GetSQLValueString($description, "text")."," : "";
		$update .= ($description !="") ? "`metadescription` = ".GetSQLValueString($description, "text")."," : "";
		$update .= ($price >0) ? "price = ".GetSQLValueString($price, "double").", " : ""; 
		$update .= ($rrp >0) ? "listprice = ".GetSQLValueString($rrp, "double").", " : "";
		$update .= ($length >0) ? "box_length = ".GetSQLValueString($length, "double").", " : "";
		$update .= ($width >0) ? "box_width = ".GetSQLValueString($width, "double").", " : "";
		$update .= ($height >0) ? "box_height = ".GetSQLValueString($height, "height").", " : "";
		$update .= ($int_length >0) ? "int_length = ".GetSQLValueString($int_length, "double").", " : "";
		$update .= ($int_width >0) ? "int_height = ".GetSQLValueString($int_width, "double").", " : "";
		$update .= ($int_width >0) ? "int_width = ".GetSQLValueString($int_width, "double").", " : "";
		$update .= ($area >0) ? "area = ".GetSQLValueString($area, "double").", " : "";
		$update .= ($weight >0) ? "weight = ".GetSQLValueString($weight, "double").", " : "";
		$update .= ($volume >0) ? "capacity = ".GetSQLValueString($volume, "double").", " : "";   
		$update .= ($imageURL !="") ? "imageURL = ".GetSQLValueString($imageURL, "text").", " : "";
		$update .= ($mpn !="") ? "mpn = ".GetSQLValueString($mpn, "text").", " : ""; 
		$update .= ($upc !="") ? "upc = ".GetSQLValueString($upc, "text").", " : ""; 
		$update .= ($sku !="") ? "sku = ".GetSQLValueString($sku, "text").", " : ""; 
		$update .= ($availabledate !="") ? "availabledate = ".GetSQLValueString($availabledate, "date").", " : ""; 
		$update .= ($instock !="") ? "instock = ".GetSQLValueString($instock, "int").", " : ""; 
		$update .= "modifieddatetime = '".date('Y-m-d H:i:s')."', ";
		$update .= "modifiedbyID = ".GetSQLValueString($createdbyID, "int");
		$update .= " WHERE ID = ".$row['ID'];
		if($testmode==0) {
			mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
			$returnvalue['productID'] = $row['ID'];			
		} else {
			$returnvalue['result'] = $update;
		}
		return $returnvalue;
	} else if(isset($optionresult) && mysql_num_rows($optionresult)>0) { // OPTION MATCH
		$option = mysql_fetch_assoc($optionresult);
		$update = "UPDATE productoptions SET ";
		$update .= ($title !="") ? "optionname = ".GetSQLValueString($title, "text").", " : ""; 
		$update .= ($price>0) ? "price = ".GetSQLValueString($price, "double").", " : ""; 
		$update .= ($weight>0) ? "`weight` = ".GetSQLValueString($weight, "double")."," : "";
		$update .= ($instock !="") ? "`quantity` = ".GetSQLValueString($instock, "int")."," : "";
		$update .= ($availabledate!="") ? "availabledate = ".GetSQLValueString($availabledate, "date").", " : ""; 
		$update .= "modifieddatetime = '".date('Y-m-d H:i:s')."', ";
		$update .= "modifiedbyID = ".GetSQLValueString($createdbyID, "int");
		$update .= " WHERE ID = ".$option['ID'];	
		if($testmode==0) {
			mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
			$returnvalue['productoptionID'] = $option['ID'];			
		} else {
			$returnvalue['result'] = $update;
		}
		return $returnvalue;
	} else if($_POST['import_mode']!=2) { // IMPORT
		$instock = ($instock=="") ? 1 : $instock ;
		$title = ($title !="") ? $title : "Untitled";
		$insert = "INSERT INTO product (sku, title, `description`,  metadescription, metakeywords, longID, price, listprice,   imageURL, box_height, box_width, box_length, int_height, int_width, int_length, area, weight, capacity, mpn, upc, `condition`, instock,availabledate, datetimecreated, createdbyID) VALUES (".
		GetSQLValueString($sku, "text").",".
		GetSQLValueString($title, "text").",".
		GetSQLValueString($description, "text").",".
		GetSQLValueString($description, "text").",".
		GetSQLValueString($title, "text").",".
		GetSQLValueString($longID, "text").",".
		GetSQLValueString($price, "double").",".
		GetSQLValueString($rrp, "double").",".
		GetSQLValueString($imageURL, "text").",".
		GetSQLValueString($height, "double").",".
		GetSQLValueString($width, "double").",".
		GetSQLValueString($length, "double").",".
		GetSQLValueString($int_height, "double").",".
		GetSQLValueString($int_width, "double").",".
		GetSQLValueString($int_length, "double").",".
		GetSQLValueString($area, "double").",".
		GetSQLValueString($weight, "double").",".
		GetSQLValueString($volume, "double").",".
		GetSQLValueString($mpn, "text").",".
		GetSQLValueString($upc, "text").",".
		GetSQLValueString($condition, "int").",".
		GetSQLValueString($instock, "int").",".
		GetSQLValueString($availabledate, "date").",
		'".date('Y-m-d H:i:s')."',".GetSQLValueString($createdbyID, "int").")";
		if($testmode==0) {
			mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			$productID = mysql_insert_id();
			addProductToRegion($productID, $regionID, $createdbyID=0);
			$update = "UPDATE product SET ordernum = ".$productID." WHERE ID = ".$productID;
			mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
			$returnvalue['productID'] =  $productID;
		} else {
			$returnvalue['result'] = $insert;
		}
		return $returnvalue;
	} else {
		return false;
	}
	
}
}


if(!function_exists("addCategory")) {
function addCategory($categorytitle = "", $subcatofID = 0, $createdbyID=0) {
	if($categorytitle != "") {
		global $database_aquiescedb, $aquiescedb, $regionID;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$longID = createURLname("", $categorytitle, "-",  "productcategory");
		$select = "SELECT ID FROM productcategory WHERE title = ".GetSQLValueString($categorytitle, "text")." AND subcatofID = ".intval($subcatofID)." AND regionID =  ".intval($regionID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			return $row['ID'];
		} else {
			$insert = "INSERT INTO productcategory (longID, title, subcatofID, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($longID, "text").",".GetSQLValueString($categorytitle, "text").",".GetSQLValueString($subcatofID, "int").",".GetSQLValueString($regionID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			return mysql_insert_id();
		}
	
	} else {
		return 0;
	}
}
}




if(!function_exists("addProductToCategory")) {
function addProductToCategory($productID = 0, $categoryID = 0, $default = 0, $createdbyID=0) {
	if($productID>0 && $categoryID>0) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		// first check if already in category
		$select = "SELECT product.ID FROM product LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE product.ID = ".intval($productID)." AND (product.productcategoryID = ".intval($categoryID)." OR productincategory.categoryID = ".intval($categoryID).") LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)==0) { // doesn't exist 	
			$insert="INSERT INTO productincategory (categoryID, productID, createdbyID, createddatetime) VALUES (".GetSQLValueString($categoryID, "int").",".GetSQLValueString($productID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			$select = "SELECT product.productcategoryID FROM product  WHERE product.ID = ".intval($productID). " LIMIT 1";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$row = mysql_fetch_assoc($result);
			$select = "SELECT forcemaincategory FROM productcategory WHERE ID = ".intval($categoryID);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$category = mysql_fetch_assoc($result);
			if($default != -1 && ($default == 1 || is_null($row['productcategoryID']) || $category['forcemaincategory']==1)) { // default or doesn't exist or force main
				makeMainCategory($categoryID, $productID);
				
			}
			return mysql_insert_id();
		}
		return 0;
	}
}
}

if(!function_exists("addManufacturer")) {
function addManufacturer($manufacturer="", $subsidiaryofID = 0, $createdbyID=0) {
	if($manufacturer !="") {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT ID FROM productmanufacturer WHERE manufacturername = ".GetSQLValueString($manufacturer, "text")."";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			return $row['ID'];
		} else {
			$insert = "INSERT INTO productmanufacturer (manufacturername, subsidiaryofID, createdbyID, createddatetime) VALUES (".GetSQLValueString($manufacturer, "text").",".GetSQLValueString($subsidiaryofID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			return mysql_insert_id();
		}
	}
}
}

if(!function_exists("setProductToManufacturer")) {
function setProductToManufacturer($productID = 0, $manufacturerID = 0, $createdbyID=0) {
	if($productID>0 && $manufacturerID>0) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
			
			$update="UPDATE product SET manufacturerID = ".GetSQLValueString($manufacturerID, "int")." WHERE ID = ".GetSQLValueString($productID, "int");
			$result = mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
		
	}
}
}


if(!function_exists("addProductOption")) {
function addProductOption($productID, $productoption="", $stockcode = "", $price = 0, $weight = "", $size="", $quantity = "", $createdbyID=0) {
	if($productID> 0 && $productoption !="") {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
	
		$insert = "INSERT INTO productoptions (productID, optionname, stockcode, price, weight, size, quantity, createdbyID, createddatetime) VALUES (".GetSQLValueString($productID, "int").",".GetSQLValueString($productoption, "text").",".GetSQLValueString($stockcode, "text").",".GetSQLValueString($price, "double").",".GetSQLValueString($weight, "double").",".GetSQLValueString($size, "text").",".GetSQLValueString($quantity, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
		$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
		return mysql_insert_id();
	
	}
}
}

if(!function_exists("addProductToRegion")) {
function addProductToRegion($productID, $regionID, $createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = "INSERT INTO productinregion (productID, regionID, createdbyID, createddatetime) VALUES(".$productID.",".$regionID.",".$createdbyID.", '".date('Y-m-d H:i:s')."') ";
	mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
}
}


if(!function_exists("deleteProduct")) {
function deleteProduct($productID, $substituteID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	// we need to delete any linked items first
	$delete = "DELETE FROM productincategory WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productdetails WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productgallery WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productoptions WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productrelated WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productbid WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productinregion WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM producttagged WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productwithfinish WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	$delete = "DELETE FROM productwithversion WHERE productID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);
	
	
	
	if($substituteID !=0) {
		$update = "UPDATE productnotify SET productID = ".intval($substituteID);
		mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
		$update = "UPDATE productorderproducts SET productID = ".intval($substituteID);
		mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
	}
	$delete = "DELETE FROM product WHERE ID = ".intval($productID);
	mysql_query($delete, $aquiescedb) or die(mysql_error()." - ".$delete);		
}
}


if(!function_exists("addTag")) {
function addTag($tagname = "", $taggroupID = 0, $createdbyID=0, $taggroupname = "") {
	if(trim($tagname) != "") {
		global $database_aquiescedb, $aquiescedb, $regionID;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		if($taggroupname !="") {
			$taggroupID = addTagGroup($taggroupname, $createdbyID);
		}
		$select = "SELECT ID FROM producttag WHERE tagname LIKE ".GetSQLValueString($tagname, "text")." AND regionID =".$regionID;
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			return $row['ID'];
		} else {
			$insert = "INSERT INTO producttag (tagname, taggroupID, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($tagname, "text").",".GetSQLValueString($taggroupID, "int").",".GetSQLValueString($regionID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			return mysql_insert_id();
		}
	
	} else {
		return 0;
	}
}
}

if(!function_exists("addTagGroup")) {
function addTagGroup($taggroupname, $createdbyID=0) {
	$taggroupID = 0;
	if(trim($taggroupname) != "") {
		global $database_aquiescedb, $aquiescedb, $regionID;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT ID FROM producttaggroup WHERE taggroupname LIKE ".GetSQLValueString($taggroupname, "text")." AND regionID = ".intval($regionID)." LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)==0) {
			$insert = "INSERT INTO producttaggroup (taggroupname,  regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($taggroupname, "text").",".GetSQLValueString($regionID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			$taggroupID = mysql_insert_id();
		} else {
			$row = mysql_fetch_assoc($result);
			$taggroupID = $row['ID'];
		}
	}
	return $taggroupID;
}
}
	
if(!function_exists("tagProduct")) {
function tagProduct($productID = 0, $tagID = 0, $createdbyID=0) {
	if($productID>0 && $tagID>0 ) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		// first check if already in category
		$select = "SELECT productID FROM producttagged WHERE productID = ".intval($productID)." AND tagID = ".intval($tagID)." LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)==0) { // doesn't exist 	
			$insert="INSERT INTO producttagged (tagID, productID, createdbyID, createddatetime) VALUES (".GetSQLValueString($tagID, "int").",".GetSQLValueString($productID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$insert);
			return mysql_insert_id();
		}
		return 0;
	}
}
}

if(!function_exists("getChildren")) {
function getChildren($parentID) { // categories
	global $database_aquiescedb, $aquiescedb, $regionID;
	
	$select = "SELECT productcategory.ID AS catID, productcategory.ordernum, productcategory.title, productcategory.statusID, productcategory.gbasecat, productcategory.subcatofID, categorysale, (SELECT COUNT(DISTINCT(product.ID)) FROM product LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE (product.productcategoryID = productcategory.ID OR productincategory.categoryID = productcategory.ID)  AND (product.statusID IS NULL OR product.statusID <2)) AS numproducts FROM productcategory   WHERE productcategory.regionID = ".$regionID." AND subcatofID = ".$parentID." ORDER BY productcategory.ordernum";
	$rsChildren = mysql_query($select, $aquiescedb) or die(mysql_error());
	$rowChildren = mysql_fetch_assoc($rsChildren);
	$html = "";
	if($rowChildren) { // is children
	$html .= "<ul class=\"sortable\">"; $i=0;
	do { // each row
		$i++; if($rowChildren['ordernum']==0 && $i>1) {
			$update = "UPDATE productcategory SET ordernum = ".intval($i)." WHERE ID = ".$rowChildren['catID'];
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		$html .= "<li class=\"status".$rowChildren['statusID']."\"  id=\"listItem_".$rowChildren['catID']."\">
		<div class=\"fltlft handle\">&nbsp;</div>
		<div class=\"fltlft\"><a href=\"update_category.php?categoryID=".$rowChildren['catID']."\">".$rowChildren['title']."</a>&nbsp;(".$rowChildren['numproducts'].")&nbsp;";
		$html .= ($rowChildren['categorysale']==1) ? "<img src=\"/core/images/icons/flag_red.png\" alt=\"Sale item\" width=\"16\" height=\"16\" style=\"vertical-align:
middle;\" />" : "";
		$html .= isset($rowChildren['gbasecat']) ? "<span class=\"gbase\"><img src=\"/core/images/icons/google_favicon.png\" alt=\"Google Shopping compatible\" width=\"16\" height=\"16\" style=\"vertical-align:
middle;\" />&nbsp;".$rowChildren['gbasecat']."</span>&nbsp;</div>" : "</div>";
		
		$html .= "<span class=\"hover\"><a href=\"".productLink(0, "", $rowChildren['catID'], $rowChildren['longID'])."\" class=\"link_view fltlft\" target=\"_blank\">View</a>&nbsp;";
		$html .= "<a href=\"update_category.php?categoryID=".$rowChildren['catID']."\" class=\"link_edit fltlft\">Edit</a>&nbsp;";
		$html .= "<a href=\"add_category.php?categoryID=".$rowChildren['catID']."\" title=\"Add sub category to this category\" class=\"link_add icon_only fltlft\">Add</a>\n";
		$childHTML = getChildren($rowChildren['catID']);
		
		$html .= ($childHTML == "" && $rowChildren['numproducts'] == 0) ? "<a href=\"index.php?deleteID=".$rowChildren['catID']."\" onclick=\"return confirm('Are you sure you want to delete this category?');\"" : "<a href=\"javascript:alert('You cannot delete a category that contains products. Please move products within to a new category first.')\"";
		$html .=" class=\"link_delete fltlft\">Delete</a></span>";
		$html .= $childHTML;
		$html .= "</li>";
	} while ($rowChildren = mysql_fetch_assoc($rsChildren)); // end each row
	$html .= "</ul>\n";
	} // is children
	return $html;
} // end func	
}


if(!function_exists("addFinishToProduct")) {
function addFinishToProduct($productID=0, $finishname="", $createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	$optionID = 0;
	if($productID>0 && trim($finishname)!="") {
		// does finish exist yet?
		$select = "SELECT ID FROM productfinish WHERE finishname = ".GetSQLValueString($finishname, "text");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
		if(mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			$finishID = $row['ID'];
			// check if option exists wth product
			$select = "SELECT ID FROM productwithfinish WHERE productID = ".GetSQLValueString($productID, "int")." AND finishID = ".GetSQLValueString($finishID, "int");
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
			if(mysql_num_rows($result)>0) {
				$row = mysql_fetch_assoc($result);
				$optionID = $row['ID'];
			}
			
		} else { // doesn't exist - create new
			$insert = "INSERT INTO productfinish (finishname, createdbyID, createddatetime) VALUES (".GetSQLValueString($finishname, "text").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());	
			$finishID = mysql_insert_id();
		}
		if($optionID == 0) { // add option	
			$insert = "INSERT INTO productwithfinish (productID, finishID, createdbyID, createddatetime) VALUES (".GetSQLValueString($productID, "int").",".GetSQLValueString($finishID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());	
			$optionID = mysql_insert_id();
		}
	}
	return $optionID;
}
}

if(!function_exists("addVersionToProduct")) {
function addVersionToProduct($productID=0, $versionname="", $createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	$optionID = 0;
	if($productID>0 && trim($versionname)!="") {
		
		// does finish exist yet?
		$select = "SELECT ID FROM productversion WHERE versionname = ".GetSQLValueString($versionname, "text");
		
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
		if(mysql_num_rows($result)>0) { 
			$row = mysql_fetch_assoc($result);
			$versionID = $row['ID'];
			// check if option exists wth product
			$select = "SELECT ID FROM productwithversion WHERE productID = ".GetSQLValueString($productID, "int")." AND versionID = ".GetSQLValueString($versionID, "int");
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
			if(mysql_num_rows($result)>0) {
				$row = mysql_fetch_assoc($result);
				$optionID = $row['ID'];
			}
			
		} else { // doesn't exist - create new
			$insert = "INSERT INTO productversion (versionname, createdbyID, createddatetime) VALUES (".GetSQLValueString($versionname, "text").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());	
			$versionID = mysql_insert_id();
		}
		if($optionID == 0) { // add option	
			$insert = "INSERT INTO productwithversion (productID, versionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($productID, "int").",".GetSQLValueString($versionID, "int").",".GetSQLValueString($createdbyID, "int").",'".date('Y-m-d H:i:s')."')";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());	
			$optionID = mysql_insert_id();
		}
	}
	return $optionID;
}
}

if(!function_exists("removeFinishFromProduct")) {
function removeFinishFromProduct($productID=0, $finishname="") {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID FROM productfinish WHERE finishname = ".GetSQLValueString($finishname, "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
	if(mysql_num_rows($result)>0) {
		$row = mysql_fetch_assoc($result);
		$delete = "DELETE FROM productwithfinish  WHERE productID = ".intval($productID)." AND finishID = ".$row['ID'];
		$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	}
}
}

if(!function_exists("removeVersionFromProduct")) {
function removeVersionFromProduct($productID=0, $versionname="") {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID FROM productversion WHERE versionname = ".GetSQLValueString($versionname, "text");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
	if(mysql_num_rows($result)>0) {
		$row = mysql_fetch_assoc($result);
		$delete = "DELETE FROM productwithversion  WHERE productID = ".intval($productID)." AND versionID = ".$row['ID'];
		$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
	}
}
}

if(!function_exists("addProductGalleryPhoto")) {
function addProductGalleryPhoto($productID, $mediaURL, $createdbyID = 0) { 
	if(strlen(trim($mediaURL))>4) { // genuine media file
		if(strpos($mediaURL,"youtube.com")) {  // is youtube
			$videoURL = $mediaURL;
			preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $mediaURL, $matches);
    		$youtubeID = $matches[1];
			$imageURL = "https://img.youtube.com/vi/".$youtubeID."/mqdefault.jpg";
		} else if(strpos($imageURL,".mp4")>0) {  // is raw video
			$videoURL = $mediaURL;
			$imageURL = "";
		} else {
			$videoURL = "";
			$imageURL = fixImageURL($mediaURL);
		}
		
		global $database_aquiescedb, $aquiescedb, $regionID;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT  title FROM product WHERE ID = ".intval($productID). " LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$product = mysql_fetch_assoc($result);
		$select = "SELECT galleryID FROM productgallery WHERE productID = ".intval($productID). " LIMIT 1";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			$galleryID = $row['galleryID'];
		} else {
			// create gallery
			$galleryname = isset($product['title']) ? $product['title'] : "Product ".$productID;
			$insert = "INSERT INTO photocategories (categoryname, accesslevel, active, regionID, addedbyID,createddatetime) VALUES (".GetSQLValueString($product['title'], "text").", 0, 1, ".intval($regionID).", ".intval($createdbyID).",'".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
			$galleryID = mysql_insert_id();
			$insert = "INSERT INTO productgallery (productID, galleryID) VALUES (".intval($productID).",".$galleryID .")";
			mysql_query($insert, $aquiescedb) or die(mysql_error());		
		}
		$insert = "INSERT INTO photos (active, categoryID, title, description, imageURL, videoURL, userID, createddatetime) VALUES (1, ".$galleryID.", ".GetSQLValueString($product['title'], "text").", ".GetSQLValueString($product['title'], "text").", ".GetSQLValueString($imageURL, "text").", ".GetSQLValueString($videoURL, "text").", ".intval($createdbyID).", '".date('Y-m-d H:i:s')."')";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$photoID = mysql_insert_id();
		return $photoID;
	} else {
		return false;
	}
}
}

if(!function_exists("copyProductsToRegion")) {
function copyProductsToRegion($newRegion = 0, $fromRegion = 1, $categoryID = 0, $regioncategoryID = 0, $productID = 0) {
	/* INPUT - just regions - copies all products
	- region and categoryID - copies just that category to new one
	- region and categoryID  and regioncategoryID - copies just that category to exitisng one
	- productID, $categoryID and regioncategoryID - copies product to specified category in new region
	 */
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	// REQUIRES framework.inc
	if($newRegion>0 && $fromRegion>0 && $newRegion !=$fromRegion) { // have regions
		// need region numbers but possibilty to use 0 later
		// copy product prefs
		$oldnewcategories = array();
		$oldnewproducts = array();
		if($productID>0 && ($categoryID==0 || $regioncategoryID==0)) die("Incorrect function data");
		
		if($categoryID>0  && $regioncategoryID>0) { // just one product or just one category
			$oldnewcategories[intval($categoryID)] = $regioncategoryID;
			
		} else {		
			// copy all product categories across and put in array
			$select = "SELECT * FROM productcategory WHERE regionID = ".intval($fromRegion);
			$select .= $categoryID >0 ? " AND ID = ".intval($categoryID) : "";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)>0) {
				while($row = mysql_fetch_assoc($result)) {
					$newcatID = duplicateMySQLRecord ("productcategory", $row['ID'], "ID");
					//echo "CAT".$newID."<br>"; //********
					$oldnewcategories[$row['ID']] = $newcatID;	
					$update = "UPDATE productcategory SET createdbyID= 0, createddatetime = '".date('Y-m-d H:i:s')."', regionID = ".$newRegion." WHERE ID = ".$newcatID;
					mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);								
				}
				// update sub cat of ID
				foreach($oldnewcategories as $key=> $value) {				
					$update = "UPDATE productcategory SET subcatofID = ".$value." WHERE subcatofID = ".$key." AND regionID = ".$newRegion;
					mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);								
				}
			}
		} // end not product		
		
		
		// copy products
		$select = "SELECT product.* FROM product LEFT JOIN productinregion ON (product.ID = productinregion.productID) LEFT JOIN productcategory ON (productcategory.ID = product.productcategoryID) WHERE (".intval($productID)." = 0 OR product.ID = ".intval($productID).") AND (".intval($categoryID)." = 0 OR product.productcategoryID = ".intval($categoryID).") AND product.statusID = 1 AND (productinregion.regionID = ".intval($fromRegion)." OR productcategory.regionID = ".intval($fromRegion).") GROUP BY product.ID";
		
		$product_result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($product_result)>0) { // is result
		
			while($product = mysql_fetch_assoc($product_result)) { // for each product
				copyProductToRegion( $product['ID'],$oldnewcategories[$product['productcategoryID']],$newRegion);			
			} // end for each product
		} // is result		
	} // have regions	
} // end function
}


if(!function_exists("copyProductToRegion")) {
function copyProductToRegion($productID, $newcategoryID, $newRegion) {
	global $database_aquiescedb, $aquiescedb, $oldnewcategories, $rsLoggedIn;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$newproductID = duplicateMySQLRecord ("product", $productID, "ID");
	$createdbyID = (isset($rsLoggedIn['ID']) && $rsLoggedIn['ID']>0) ? $rsLoggedIn['ID'] : 0;
				//echo "PRO".$newproductID."<br>"; //********
				
	$update = "UPDATE product SET createdbyID = ".$createdbyID.", datetimecreated = '".date('Y-m-d H:i:s')."', productcategoryID = ".intval($newcategoryID)." WHERE ID = ".$newproductID;
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	// add to new region
	addProductToRegion($newproductID, $newRegion);
	
	
	// add any product in categories
	if(isset($oldnewcategories) && is_array($oldnewcategories)) {
		$select2 = "SELECT * FROM productincategory WHERE productID = ".$productID;
		$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result2)>0) {
			while($row2 = mysql_fetch_assoc($result2)) {
				if(isset($oldnewcategories[$row2['categoryID']])) { // we have a match to copy into
					$insert = "INSERT INTO productincategory (productID, categoryID, createdbyID, createddatetime) VALUES(".$newproductID.",".$oldnewcategories[$row2['categoryID']].",".$createdbyID.", '".date('Y-m-d H:i:s')."')";
					mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
				}				
			}
		}
	} 
	// add any product tagged
	$select2 = "SELECT * FROM producttagged WHERE productID = ".$productID;
	$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result2)>0) {
		while($row2 = mysql_fetch_assoc($result2)) {
			$insert = "INSERT INTO producttagged (productID, tagID, createdbyID, createddatetime) VALUES(".$newproductID.",".$row2['tagID'].",".$createdbyID.", '".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		}
	}	
	
		
	// add any product gallery
	$select2 = "SELECT * FROM productgallery WHERE productID = ".$productID;
	$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result2)>0) {
		while($row2 = mysql_fetch_assoc($result2)) {
			$insert = "INSERT INTO productgallery (productID, galleryID, createdbyID, createddatetime) VALUES(".$newproductID.",".$row2['galleryID'].",".$createdbyID.", '".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		}
	}	
	
	// add any product with  finishes
	$select2 = "SELECT * FROM productwithfinish WHERE productID = ".$productID;
	$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result2)>0) {
		while($row2 = mysql_fetch_assoc($result2)) {
			$insert = "INSERT INTO productwithfinish (productID, finishID, createdbyID, createddatetime) VALUES(".$newproductID.",".$row2['finishID'].",".$createdbyID.", '".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		}
	}
	
	// add any product with  versions
	$select2 = "SELECT * FROM productwithversion WHERE productID = ".$productID;
	$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result2)>0) {
		while($row2 = mysql_fetch_assoc($result2)) {
			$insert = "INSERT INTO productwithversion (productID, versionID, createdbyID, createddatetime) VALUES(".$newproductID.",".$row2['versionID'].",".$createdbyID.", '".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		}
	}
	
	// copy product details (tabs)
	$select = "SELECT * FROM productdetails WHERE productID = ".intval($productID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			$newdetailsID = duplicateMySQLRecord ("productdetails", $row['ID']);
			$update = "UPDATE productdetails SET createdbyID= ".$createdbyID.", createddatetime = '".date('Y-m-d H:i:s')."', productID = ".$newproductID.", regionID = ".$newRegion." WHERE ID = ".$newdetailsID;
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);			
		}	
	}
		
	// copy product options 
	$select = "SELECT * FROM productoptions  WHERE productID = ".intval($productID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			$newoptionsID = duplicateMySQLRecord ("productoptions", $row['ID']);
			$update = "UPDATE productoptions SET createdbyID= ".$createdbyID.", createddatetime = '".date('Y-m-d H:i:s')."', productID = ".$newproductID.", regionID = ".$newRegion." WHERE ID = ".$newoptionsID;
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
			
		}
	}		
}
}

if(!function_exists("fixImageURL")) {
function fixImageURL($imageURL) { //when importing from a spreadheet - often names are not properly entered.
	if(trim($imageURL !="")) {
		//$imageURL = str_replace(" ","_",$imageURL);
		$imageURL .= (strpos($imageURL,".")===false) ? ".jpg" : "";
		return $imageURL;
	} else {
		return "";
	}
}
}
	
if(!function_exists("makeMainCategory")) {
function makeMainCategory($categoryID = 0, $productID = 0) {
		global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT product.ID AS productID, productcategoryID,productincategory.categoryID  FROM product LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE  productincategory.categoryID = ".intval($categoryID)." AND productcategoryID != ".intval($productID)." AND (".intval($productID)." = 0 OR product.ID = ".intval($productID).")";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($result)>0) { // is match(es)
			while($row = mysql_fetch_assoc($result)) {
				$update = "UPDATE product SET productcategoryID = ".intval($categoryID)." WHERE product.ID = ".intval($row['productID']);
				//echo $update."**".$row['productcategoryID']."<br>";
				mysql_query($update, $aquiescedb) or die(mysql_error()." - ".$update);
				addProductToCategory($row['productID'], $row['productcategoryID'], -1);
			} // repeat
		} // match(es)
	}
}
	
if(!function_exists("addUniqueCode")) {
	function addUniqueCodes($promoID, $promocode="", $validfrom="", $validuntil="", $quantity =1) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		if(intval($promoID)>0) {
			$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			for($i=1; $i<=$quantity; $i++) {
				if($promocode=="") {				
					   
					for ($x = 0; $x < 8; $x++) {
						$promocode .= $characters[rand(0, 25)];
					}   
				}
				
			
				$insert = "INSERT INTO productpromocode (`promocode`,`promoID`,`validfrom`,`validuntil`,`createdbyID`,`createddatetime`) VALUES (".GetSQLValueString($promocode, "text").",".GetSQLValueString($promoID, "int").",".GetSQLValueString($validfrom, "date").",".GetSQLValueString($validuntil, "date").",0,NOW())";	
				mysql_query($insert, $aquiescedb) or die(mysql_error()." - ".$update);
				$lastcode = $promocode;
				$promocode = "";
			}
			return $lastcode;		
		}
		return false;
	}
}

if(!function_exists("addRelatedProduct")) {
	function addRelatedProduct($productID=0, $relatedproductID=0,$createdbyID = 0) {
		if(intval($productID)>0 &&intval($relatedproductID)>0  && intval($productID)!=intval($relatedproductID)) {
			global $database_aquiescedb, $aquiescedb;
			mysql_select_db($database_aquiescedb, $aquiescedb);
			// first check if relationship already exists...
			$select = "SELECT ID FROM productrelated WHERE (productID = ".GetSQLValueString($productID, "int")." AND relatedtoID = ".GetSQLValueString($relatedproductID, "int").") OR (relatedtoID = ".GetSQLValueString($productID, "int")." AND productID = ".GetSQLValueString($relatedproductID, "int").") LIMIT 1";
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)==0) { // doesn't exist 
				$insert="INSERT INTO productrelated (productID, relatedtoID, relationshiptypeID, createdbyID, createddatetime) VALUES (".GetSQLValueString($productID,"int").",".GetSQLValueString($relatedproductID,"int").",1,".intval($createdbyID).",'".date('Y-m-d H:i:s')."')";
				$result = mysql_query($insert, $aquiescedb) or die(mysql_error());			  
			}// end doesn't already exist
		}
	}
}

if(!function_exists("removeRelatedProducts")) {
	function removeRelatedProducts($productID=0) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$delete = "DELETE FROM productrelated WHERE productID = ".GetSQLValueString($productID, "int")." OR relatedtoID = ".GetSQLValueString($productID, "int");
		mysql_query($delete, $aquiescedb) or die(mysql_error());
	}
}



?>
