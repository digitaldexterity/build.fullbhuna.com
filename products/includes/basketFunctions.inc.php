<?php /******************************

REQUIRES 
/core/includes/framework.inc.php
/products/includes/productHeader.inc.php which includes rsThisRegion AND rsProductPrefs

**************************************/

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
mysql_select_db($database_aquiescedb, $aquiescedb);




if (!function_exists("getBasket")) {
function getBasket() { // get contents of basket into array
	$items = array();
	$item = 0;
	global $aquiescedb, $row_rsThisRegion;
	if(isset($_SESSION['basket'])) { // is basket
		foreach($_SESSION['basket'] as $productID => $product) { // for each product
			foreach($product as $option =>$details) { // for each product option
				if($details['quantity']>0) { // has quantity
					$item++;
					$items[$item]['productID'] = $productID;
					
						
				 	if($productID >0) { // is product
					
						$select = "SELECT product.*,product.longID AS productlongID,productcategory.longID AS categorylongID,productcategory.vatdefault,productcategory.vatincluded,productcategory.excludepromotions, productvatrate.ratepercent, productmanufacturer.manufacturershipping FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productvatrate ON (product.vattype = productvatrate.ID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID)  WHERE product.ID = ".GetSQLValueString($productID,"int")." LIMIT 1";
						$rsProduct = mysql_query($select, $aquiescedb) or die(mysql_error());
						$row_rsProduct = mysql_fetch_assoc($rsProduct);
						$items[$item]['option'] = base64_encode($option);	// used as unique identifier for basket item (deleting etc)	
						$options = explode(" @@ ",$option);
						$items[$item]['optionID'] = @$options[0];
						$items[$item]['optiontext'] = @$options[1];
				
						$select = "SELECT optionname, weight, price, productfinish.finishname, productversion.versionname, productoptions.photoID, photos.imageURL AS optionimageURL, availabledate  FROM productoptions   LEFT JOIN productfinish ON (productoptions.finishID = productfinish.ID) LEFT JOIN productversion ON (productoptions.versionID = productversion.ID) LEFT JOIN photos ON (productoptions.photoID = photos.ID) WHERE productoptions.ID = ".intval($items[$item]['optionID'])." LIMIT 1";
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
						$rowOption = mysql_fetch_assoc($result);
						
						
				
						
						$items[$item]['productlongID'] = $row_rsProduct['productlongID'];
						$items[$item]['productcategoryID'] = $row_rsProduct['productcategoryID'];
						$items[$item]['categorylongID'] = $row_rsProduct['categorylongID'];
						$items[$item]['availabledate'] = isset($rowOption['availabledate']) && $rowOption['availabledate'] !="" && $rowOption['availabledate'] > date('Y-m-d') ? $rowOption['availabledate'] : $row_rsProduct['availabledate'];
						$items[$item]['deliveryperiod'] = $row_rsProduct['deliveryperiod'];
						$items[$item]['mindeliverytime'] = $row_rsProduct['mindeliverytime'];
						$items[$item]['maxdeliverytime'] = $row_rsProduct['maxdeliverytime'];
						$items[$item]['title'] = $row_rsProduct['title'];
						$items[$item]['sku'] = isset($row_rsProduct['sku']) ? $row_rsProduct['sku'] : "ID-".$productID; 
						$items[$item]['vattype'] = $row_rsProduct['vattype'];
						$items[$item]['vatdefault'] = isset($row_rsProduct['vatdefault']) ? $row_rsProduct['vatdefault'] : 1; 
						$items[$item]['vatincluded'] = $row_rsProduct['vatincluded'];  
						$items[$item]['shippingexempt'] = $row_rsProduct['shippingexempt'];
						$items[$item]['shippingrateID'] = $row_rsProduct['shippingrateID'];
						$items[$item]['noshipinternational'] = $row_rsProduct['noshipinternational'];
						$items[$item]['manufacturershipping'] = $row_rsProduct['manufacturershipping'];
						$items[$item]['area'] = $row_rsProduct['area']; 
						$items[$item]['hazardous'] = $row_rsProduct['hazardous']; 
						$items[$item]['title'] .= isset($rowOption['optionname']) ? " [".$rowOption['optionname']."]" : ""; 
						$items[$item]['title'] .= (isset($rowOption['finishname']) && $rowOption['finishname'] != $rowOption['optionname']) ? " [".$rowOption['finishname']."]" : ""; 
						$items[$item]['title'] .= (isset($rowOption['versionname']) && $rowOption['versionname'] != $rowOption['optionname'])  ? " [".$rowOption['versionname']."]" : ""; 
						$items[$item]['title'] .= (@$items[$item]['optiontext'] !="") ? " [".$items[$item]['optiontext']."]" : "";
						$items[$item]['explanation'] = (@$details['explanation'] !="") ? $details['explanation'] : "";
						$items[$item]['excludepromotions'] =  $row_rsProduct['excludepromotions'];			
						$items[$item]['price'] = isset($rowOption['price']) ? $rowOption['price'] : $row_rsProduct['price'];
						$items[$item]['listprice'] = $row_rsProduct['listprice'];
						$items[$item]['weight'] = isset($rowOption['weight']) ? $rowOption['weight'] : $row_rsProduct['weight'];
						$items[$item]['quantity'] = $details['quantity'];
						$items[$item]['instock'] = $row_rsProduct['instock']; 
						$items[$item]['ratepercent'] = $row_rsProduct['vattype']>0 ? (($row_rsProduct['vattype']>1) ? $row_rsProduct['ratepercent'] : $row_rsThisRegion['vatrate']) : 0; 
						if(isset($_SESSION['basket'][$productID][$option]['thumbnail'])) {
							$items[$item]['imageURL'] = $_SESSION['basket'][$productID][$option]['thumbnail'];
						} 
						else if(isset($rowOption['optionimageURL'])) { 
							$items[$item]['imageURL'] = $rowOption['optionimageURL']; 
						}
						else if(isset($row_rsProduct['imageURL'])) { 
							$items[$item]['imageURL'] = $row_rsProduct['imageURL']; 
						}
						
						if(isset($details['productforuserID']) && intval($details['productforuserID'])>0) {
							$select = "SELECT firstname, surname FROM users WHERE ID = ".intval($details['productforuserID'])." LIMIT 1";
							$result = mysql_query($select, $aquiescedb) or die(mysql_error());
							$rowUser = mysql_fetch_assoc($result);
							$items[$item]['productforuserID'] = $details['productforuserID'];
							$items[$item]['explanation'].="; For ".$rowUser['firstname']." ".$rowUser['surname'];
						}
					} // is product
					else { // is one off
						$items[$item]['price'] = $details['amount'];
						$items[$item]['title'] = isset($details['explanation']) ? $details['explanation'] : "One-off payment";
						$items[$item]['quantity'] = 1;
						$items[$item]['vatincluded'] = 1;
						$items[$item]['ratepercent'] = $row_rsThisRegion['vatrate']; 
						 
					}
				} // has quantity
				
			} // for each option
		} // for each item
	} // is basket
	return $items;
}
}


function getPromotions() {
	global $aquiescedb, $nettotal, $totalvat, $freeshipping, $totalitems, $totalarea, $excludenetprice, $excludearea, $regionID, $console;
	$standalone = 0; // can be 0 or 1
	$progressivediscountgroup = array(); // each array can only be set once. Only one promo per discount group can be valid
	$multiple = 1;
	$item = 0; 
	$discount = array();
	$regionID = isset($regionID) ? intval($regionID) : 0;
	$username = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : " ";
	$query_rsPromotions = "SELECT productpromo.*, productpromocode.promocode AS uniquecode, users.username FROM productpromo LEFT JOIN productpromocode ON (productpromo.ID = productpromocode.promoID AND productpromocode.promocode = ".GetSQLValueString($_SESSION['promocode'],"text").")  LEFT JOIN usergroupmember ON (productpromo.usergroupID = usergroupmember.groupID) LEFT JOIN users ON (usergroupmember.userID = users.ID AND users.username = ".GetSQLValueString($username,"text").") WHERE (productpromo.regionID = ".$regionID." OR  productpromo.regionID = 0) AND (productpromo.startdatetime <= '".date('Y-m-d H:i:s')."' OR  productpromo.startdatetime IS NULL) AND (productpromo.enddatetime >= '".date('Y-m-d H:i:s')."' OR productpromo.enddatetime IS NULL) AND productpromo.statusID = 1 ORDER BY productpromo.ordernum ASC, productpromo.createddatetime DESC";
	$rsPromotions = mysql_query($query_rsPromotions, $aquiescedb) or die(mysql_error());
	
	
	// promos - there are automatic promos and code promos 
	// select statement takes active promos
	// check each against validity
	// only one code allowed - this is enforced by single session variable
	while($row_rsPromotions = mysql_fetch_assoc($rsPromotions)) { 
		$action = false;  
		$item ++;
		// initialse dicount group for this promo is not already
		$progressivediscountgroup[$row_rsPromotions['progressivediscountgroup']] = isset($progressivediscountgroup[$row_rsPromotions['progressivediscountgroup']]) ? $progressivediscountgroup[$row_rsPromotions['progressivediscountgroup']] : 0;
		$console .="CHECK PROMOTION ID ".$row_rsPromotions['ID']." - ".$row_rsPromotions['promotitle']." (type ".$row_rsPromotions['promocodetype'].") ";
		
		// first check if can use
		// i.e. not a promo code or a matching one and total standalones would not be greater than 1
		if(($row_rsPromotions['promocodetype']==0 || ($row_rsPromotions['promocodetype']==3 && isset($row_rsPromotions['username'])) ||  (isset($_SESSION['promocode']) && ($row_rsPromotions['promocode'] == $_SESSION['promocode'] || isset($row_rsPromotions['uniquecode'])))) && ($row_rsPromotions['standalone']==0 || $standalone ==0) && $progressivediscountgroup[$row_rsPromotions['progressivediscountgroup']]==0) { // valid...	
			$console .= "[valid] ";
			if($row_rsPromotions['actiontypeID']==0) {
				$action = true;
				$console .= "(no action required):\n";
				
				
			} else if($row_rsPromotions['actiontypeID']==1) { 		
			// buys sepcified amount of product
			 $console .= "(buys sepcified amount of product):\n";
			   	if($row_rsPromotions['actionproductID'] == 0  && $totalitems >= $row_rsPromotions['actionamount']) { 
					$multiple = floor($totalitems/$row_rsPromotions['actionamount']);
					$action = true;  
				} else if ($row_rsPromotions['actionproductID'] != 0) { // is specified product
					if(isset($_SESSION['basket'][$row_rsPromotions['actionproductID']]) && is_array($_SESSION['basket'][$row_rsPromotions['actionproductID']])) {// product in basket
							$totalpurchased=0;
							foreach($_SESSION['basket'][$row_rsPromotions['actionproductID']] as $key => $value) { 
							// for each option
								$totalpurchased += $value['quantity'];  
							} // end for each
							if($totalpurchased >=$row_rsPromotions['actionamount']) { 
								$multiple = floor($totalpurchased/$row_rsPromotions['actionamount']);
								$action = true; 
							} 
						}// end specified product in basket
					}// end is specified product
			   } // end buys specified amount of product
			   
			   
			   
			   
			   
			   else if ($row_rsPromotions['actiontypeID']==2 ) { 			   
			   // spends minimum amount on product
			   $console .= "(spends minimum amount on product):\n";
			   		if($row_rsPromotions['actionproductID'] == 0  && ($nettotal-$excludenetprice) >= $row_rsPromotions['actionamount']) { $action = true;  }
					else { // is specified product
					if(isset($_SESSION['basket'][$row_rsPromotions['actionproductID']]) && is_array($_SESSION['basket'][$row_rsPromotions['actionproductID']])) {// product in basket
					//get price
					$select = "SELECT price FROM product WHERE ID = ".$row_rsPromotions['actionproductID'];
					$result = mysql_query($select, $aquiescedb) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					$totalpurchased=0;
					foreach($_SESSION['basket'][$row_rsPromotions['actionproductID']] as $key => $value) { // for each option
						
							$totalpurchased += $value['quantity']*$row['price'];
						
								} // end for each
								if($totalpurchased >=$row_rsPromotions['actionamount']) { $action = true; } 
						}// end specified product in basket
					}// end is specified product
			   } // end spends specified amount
			   
			   
			   
			   
			   
			   
			   
			   
			   
			    else if ($row_rsPromotions['actiontypeID']==3 ) { 				
				// spends minimum amount on category
				$console .= "(spends minimum amount on category):\n";
					$totalspend = 0;
			   		if($row_rsPromotions['actioncategoryID'] == 0  && ($nettotal-$excludenetprice) >= $row_rsPromotions['actionamount']) { $action = true;  }
					else { // is specified category
					
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							foreach($_SESSION['basket'] as $productID => $product) {
								$select = "SELECT price, productcategoryID, productcategory.excludepromotions FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID)  LEFT JOIN productcategory AS parentcategory ON (productincategory.categoryID = parentcategory.ID)  LEFT JOIN productmanufacturer ON (productmanufacturer.ID = product.manufacturerID) WHERE product.ID = ".$productID." AND (productcategoryID= ".$row_rsPromotions['actioncategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['actioncategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['actioncategoryID']." OR parentcategory.subcatofID = ".$row_rsPromotions['actioncategoryID'].") AND (productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0)";
										
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								
								if(mysql_num_rows($result)>0  && $row['excludepromotions'] !=1) {// category match
								
									$row = mysql_fetch_assoc($result);
									foreach($_SESSION['basket'][$productID] as $key=>$value) {
										$totalspend += $value['quantity']*$row['price'];
									}// end for each								
								} // category match						
							} // end for each
							if($totalspend>=$row_rsPromotions['actionamount']) { $action = true;  }
						} // end products in basket		
					} // end specifed category
			   } // end spends specified amount on category
			   
			   
			   
			   
			   
			   
			   
			   
			   else if ($row_rsPromotions['actiontypeID']==4 ) { 				
				// spends minimum amount on manufacturer
				$console .= "(spends minimum amount on manufacturer):\n";
					$totalspend = 0;			   		
					if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
						foreach($_SESSION['basket'] as $productID => $product) {
							$select = "SELECT price, manufacturerID, productcategory.excludepromotions FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID) LEFT JOIN productmanufacturer AS parent ON (parent.ID = productmanufacturer.subsidiaryofID) LEFT JOIN productincategory ON (product.ID = productincategory.productID)   WHERE product.ID = ".$productID." AND (".$row_rsPromotions['actionmanufacturerID']." = 0 OR manufacturerID= ".$row_rsPromotions['actionmanufacturerID']." OR parent.ID = ".$row_rsPromotions['actionmanufacturerID'].") AND ((productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND (parent.exclpromos IS NULL OR parent.exclpromos = 0))";
									
							$result = mysql_query($select, $aquiescedb) or die(mysql_error());
							
							if(mysql_num_rows($result)>0  && $row['excludepromotions'] !=1) {// category match
								$row = mysql_fetch_assoc($result);
								foreach($_SESSION['basket'][$productID] as $key=>$value) {
									$totalspend += $value['quantity']*$row['price'];
								}// end for each								
							} // manufacturer match						
						} // end for each
						if($totalspend>=$row_rsPromotions['actionamount']) { $action = true; }
					} // end products in basket						
			   } // end spends specified amount on manufacturer
			   
			   
			   
			   
			   else if ($row_rsPromotions['actiontypeID']==6 ) { 
			   //  buys specified sqm category
			   		$console .= "(Buys specified sqm category):\n";
					//  minimum SQM amount on category
						$calctotalarea = 0;			   		
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							foreach($_SESSION['basket'] as $productID => $product) {
								$select = "SELECT area, productcategoryID, productcategory.excludepromotions FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID)  LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) LEFT JOIN productmanufacturer ON (productmanufacturer.ID = product.manufacturerID) WHERE product.ID = ".$productID." AND (".$row_rsPromotions['actioncategoryID']." = 0 OR productcategoryID= ".$row_rsPromotions['actioncategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['actioncategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['actioncategoryID']." OR parentcategory.subcatofID = ".$row_rsPromotions['actioncategoryID'].")  AND  (productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0)";
										
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								$console .= $select;
								if(mysql_num_rows($result)>0 && $row['excludepromotions'] !=1) {// category match
									$row = mysql_fetch_assoc($result);
									foreach($_SESSION['basket'][$productID] as $key=>$value) {
										$calctotalarea += $value['quantity']*$row['area'];
									}// end for each								
								} // manufacturer match						
							} // end for each basket item
							$console .= $calctotalarea .">=".$row_rsPromotions['actionamount']."\n";
							if($calctotalarea>=$row_rsPromotions['actionamount']) { $action = true; }
						} // end products in basket							
			   } // end buys specified sqm
			   
			   
			   
			   
			   
			   
			   else if ($row_rsPromotions['actiontypeID']==5 ) { 
			   //  buys specified sqm manufacturer			   
			  			$console .= "(Buys specified sqm manufacturer):\n";		
				
						$calctotalarea = 0;			   		
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							foreach($_SESSION['basket'] as $productID => $product) {
								$select = "SELECT  product.ID, area, manufacturerID FROM product LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID) LEFT JOIN productmanufacturer AS parent ON (parent.ID = productmanufacturer.subsidiaryofID)  LEFT JOIN productincategory ON (product.ID = productincategory.productID)  WHERE product.ID = ".$productID." AND (".$row_rsPromotions['actionmanufacturerID'] ." = 0 OR manufacturerID= ".$row_rsPromotions['actionmanufacturerID']." OR parent.ID = ".$row_rsPromotions['actionmanufacturerID'].")   AND ((productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND (parent.exclpromos IS NULL OR parent.exclpromos = 0))";
										
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								
								if(mysql_num_rows($result)>0 && $row['excludepromotions'] !=1) {// manufacturer match
									$row = mysql_fetch_assoc($result);									
									foreach($_SESSION['basket'][$productID] as $key=>$value) {
										$calctotalarea += $value['quantity']*$row['area'];
										
									}// end for each								
								// manufacturer match						
							} // end for each
							if($calctotalarea>=$row_rsPromotions['actionamount']) { $action = true; }
						} // end products in basket						
				 		
					}// end specific manufacturer
			   } // end buys specified sqm
			   
			   
			   
			   
			   
			   else if ($row_rsPromotions['actiontypeID']==7 ) { 			
				$totalpurchased =0;
				// buys minimum number in category
					$console .= "(buys minimum number in category):\n";
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							foreach($_SESSION['basket'] as $productID => $product) {
								$select = "SELECT price, productcategoryID, productcategory.excludepromotions FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID)  LEFT JOIN productcategory AS parentcategory ON (productincategory.categoryID = parentcategory.ID)  LEFT JOIN productmanufacturer ON (productmanufacturer.ID = product.manufacturerID) WHERE product.ID = ".$productID." AND (".$row_rsPromotions['actioncategoryID']." = 0 OR productcategoryID= ".$row_rsPromotions['actioncategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['actioncategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['actioncategoryID']." OR parentcategory.subcatofID = ".$row_rsPromotions['actioncategoryID'].") AND (productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND productcategory.excludepromotions !=1";
										
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								
								if(mysql_num_rows($result)>0 ) {// category match
								
									$row = mysql_fetch_assoc($result);
									foreach($_SESSION['basket'][$productID] as $key=>$value) {
										 $totalpurchased += $value['quantity'];  
									}// end for each								
								} // category match						
							} // end for each
							if($totalpurchased >=$row_rsPromotions['actionamount']) { $action = true; } 
						} // end products in basket		
					
			   } // end spends specified amount on category
			   
			   
			   
			   
			   
			   
			    else if($row_rsPromotions['actiontypeID']==8) {  
				// buys MAXIMUM amount of product		
			$console .= "(buys MAXIMUM amount of product):\n";
			   	if($row_rsPromotions['actionproductID'] == 0  && $totalitems <= $row_rsPromotions['actionamount']) { $action = true;  } else if ($row_rsPromotions['actionproductID'] != 0) { // is specified product
					if(isset($_SESSION['basket'][$row_rsPromotions['actionproductID']]) && is_array($_SESSION['basket'][$row_rsPromotions['actionproductID']])) {// product in basket
							$totalpurchased=0;
							foreach($_SESSION['basket'][$row_rsPromotions['actionproductID']] as $key => $value) { 
							// for each option
								$totalpurchased += $value['quantity'];  
							} // end for each
							if($totalpurchased <=$row_rsPromotions['actionamount']) { 
								
							$action = true; } 
						}// end specified product in basket
					}// end is specified product
			   } // end buys specified amount of product
			   
			   
			   
			   
			   
			   
			   
			else if ($row_rsPromotions['actiontypeID']==9 ) { 			
				$totalpurchased =0;
				// buys MAXIMUM number in category
				$console .= "(buys MAXIMUM number in category):\n";	 	
					
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							foreach($_SESSION['basket'] as $productID => $product) {
								$select = "SELECT price, productcategoryID, productcategory.excludepromotions FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) LEFT JOIN productmanufacturer ON (productmanufacturer.ID = product.manufacturerID) LEFT JOIN productcategory AS incat ON (productincategory.categoryID = incat.ID) WHERE product.ID = ".$productID." AND (".$row_rsPromotions['actioncategoryID']." = 0 OR productcategoryID= ".$row_rsPromotions['actioncategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['actioncategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['actioncategoryID']." OR parentcategory.subcatofID = ".$row_rsPromotions['actioncategoryID']." OR  incat.subcatofID = ".$row_rsPromotions['actioncategoryID'].")  AND (productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND productcategory.excludepromotions !=1";
										
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								
								if(mysql_num_rows($result)>0 ) {// category match
								
									$row = mysql_fetch_assoc($result);
									foreach($_SESSION['basket'][$productID] as $key=>$value) {
										 $totalpurchased += $value['quantity'];  
									}// end for each								
								} // category match						
							} // end for each
							if($totalpurchased>0 && $totalpurchased <=$row_rsPromotions['actionamount']) { $action = true; } 
						} // end products in basket		
					
			   } // end spends specified amount on category   
			   
			   
			   /***************************
			   
			   
			   RESULTS 
			   
			   
			   ******************************/
			   
			
			   
			   
			   if($action == true) { // if we have an action go on to test for possible result
			   		$console .="*** MATCH *** (ACTION ".$row_rsPromotions['actiontypeID']." = RESULT ".$row_rsPromotions['resulttypeID'].")\n";
			 
			   		
			   		$discount[$item]['addbasket'] =	$row_rsPromotions['addbasket'];	
			   		$discount[$item]['type'] =	$row_rsPromotions['promocodetype'];		   		
			   		if($row_rsPromotions['resulttypeID']==0) { // no effect
						$console .="** RESULT 0 NO EFFECT\n";
						$discount[$item]['ID'] = $row_rsPromotions['ID'];
						$discount[$item]['name'] = $row_rsPromotions['promotitle'];
						$discount[$item]['amount'] = 0;
			   		
			   		} 
					
					
					
					
					
					
					
					else if($row_rsPromotions['resulttypeID']==1) { // per cent discount
						
						$console .="** RESULT 1 [% DISCOUNT] \n";
						
				 		if($row_rsPromotions['resultproduct']==0) { // all products							
							if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
								foreach($_SESSION['basket'] as $productID => $product) {
									foreach($product as $key => $option) {
							
							//get price
							$selectadd ="";$joinadd = "";$whereadd=""; $keys = explode("@@",$key);
							if($keys[0]>0) { // is option
								$selectadd =" , productoptions.price AS optionprice ";
								$joinadd = " LEFT JOIN productoptions ON (productoptions.productID = product.ID) ";
								$whereadd = " AND productoptions.ID = ".$keys[0]." ";
							}
									$select = "SELECT product.price, productcategoryID ".$selectadd." FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID)  LEFT JOIN productmanufacturer AS parent ON (parent.ID = productmanufacturer.subsidiaryofID) ".$joinadd ." WHERE product.ID = ".$productID ."  AND ((productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND (parent.exclpromos IS NULL OR parent.exclpromos = 0))  AND productcategory.excludepromotions = 0".$whereadd;
									
									$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
									//echo $select;
									$totalpurchased = 0;
									if(mysql_num_rows($result)>0) {// category match
										$row = mysql_fetch_assoc($result);
										foreach($_SESSION['basket'][$productID] as $key=>$value) {
											$price = isset($row['optionprice']) ? $row['optionprice'] : $row['price'];
											$totalpurchased += $value['quantity']*$price;
											 
										}// end for each basket
										
										$discount[$item]['ID'] = $row_rsPromotions['ID'];
										$discount[$item]['name'] = $row_rsPromotions['promotitle'];
										$discount[$item]['amount'] = number_format(($totalpurchased * $row_rsPromotions['resultamount'] / 100),2,".","");
										$console .= "Bought: ".$totalpurchased.", discount: ".$row_rsPromotions['resultamount']."\n";
									} // category match
									} // each option
								} // end for ech
					 		} // end products in basket							
						} // end all products
						else { // specific product
					 		if(is_array($_SESSION['basket'][$row_rsPromotions['resultproduct']])) { // product in basket
					 	//get price
						$totalpurchased=0;
								$select = "SELECT price FROM product WHERE ID = ".$row_rsPromotions['resultproduct'];
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								$row = mysql_fetch_assoc($result);
					 			foreach($_SESSION['basket'][$row_rsPromotions['resultproduct']] as $key=>$value) {
						 			$totalpurchased += $value['quantity']*$row['price'];
					 			}// end for each
					 			
								$discount[$item]['ID'] = $row_rsPromotions['ID'];
								$discount[$item]['name'] = $row_rsPromotions['promotitle'];
								$discount[$item]['amount'] = number_format(($totalpurchased * $row_rsPromotions['resultamount'] / 100),2,".","");
								$console .=" Total: ".$totalpurchased ." Discount:".$row_rsPromotions['resultamount'];
					 		} // end product in basket
						} // end specific product
				 	} // end per cent discount
				 	else if($row_rsPromotions['resulttypeID']==2) { // customer gets free product
						$console .="** RESULT 2 [FREE PRODUCT] \n";
				 		$select = "SELECT price,title FROM product WHERE ID = ".$row_rsPromotions['resultproduct'];
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
						$row = mysql_fetch_assoc($result);
						
						$discount[$item]['ID'] = $row_rsPromotions['ID'];
				 		if(isset($_SESSION['basket'][$row_rsPromotions['resultproduct']]) && is_array($_SESSION['basket'][$row_rsPromotions['resultproduct']])) { // in basket
				//get price
							
							$discount[$item]['name'] = $row_rsPromotions['promotitle'];
							
							
							$amount = $_SESSION['basket'][$row_rsPromotions['resultproduct']]['0']['quantity'] <= $row_rsPromotions['resultamount'] ? $_SESSION['basket'][$row_rsPromotions['resultproduct']]['0']['quantity'] : $row_rsPromotions['resultamount'];
							$discount[$item]['amount'] = $row['price']*$amount*$multiple; // multiple of number of times promo can be counted
							
						} else if($row_rsPromotions['resultproduct']==0) { // any product - pick cheapest
					if(is_array($_SESSION['basket']) && $totalitems>1) { // more than 1 product in basket
								foreach($_SESSION['basket'] as $productID => $product) {
									
							//get price
						$select = "SELECT price, title FROM product  WHERE product.ID = ".$productID;
									
									
						$result = mysql_query($select, $aquiescedb) or die(mysql_error());
									
									if(mysql_num_rows($result)>0) {//  found price
										$row = mysql_fetch_assoc($result);
										foreach($_SESSION['basket'][$productID] as $key=>$value) {
											if(!isset($minprice) || $row['price'] <= $minprice) {
										$minprice = $row['price'];
										$title = $row['title'];
											} // found min price
										}// end for each
										
										$discount[$item]['ID'] = $row_rsPromotions['ID'];
										$discount[$item]['name'] = $row_rsPromotions['promotitle']." (".$title.")";
										$discount[$item]['amount'] = number_format($minprice,2,".",""); 
									} // found price
									
								} // end for ech
					 		} // end products in basket
							
							
							
							
							
						} else { // not in basket
							
							$discount[$item]['name'] = "<strong>Promotion:</strong> you qualify for a free <em>".$row['title']."</em>.<br />&raquo;&nbsp;<a href=\"/products/basket/index.php?addtobasket=true&amp;productID=".$row_rsPromotions['resultproduct']."&amp;optionID=0\">Click here</a> add to basket if you require it."; 
							$discount[$item]['amount'] = 0;
						} // end not in basket
					} else if($row_rsPromotions['resulttypeID']==3) { 
					
						if($row_rsPromotions['actiontypeID']==0 || ($row_rsPromotions['actiontypeID']==3 && $row_rsPromotions['actioncategoryID']==0)) {
							// no action required OR whole order so add 1000 to cover all items
							$freeshipping += 1000;
						} else {
							$freeshipping ++;
						}
					  $console .="** RESULT 3 [FREE SHIPPING] - amount ".$freeshipping."\n";
						
				 	} else if($row_rsPromotions['resulttypeID']==4) { 
					
						$console .="** RESULT 4 [% Discount on Category]\n";
						
						$totalpurchased = 0;
						$alreadydiscounted = 0;						
					// per cent discount on category
				 		if($row_rsPromotions['resultcategoryID']==0) { // all categories
							$console .="(all categories)\n\n";
							$where = "";							
							 // calc discount after  any previous discounts removed
							foreach($discount as $itemkey=> $value) {
								$alreadydiscounted += $discount[$itemkey]['amount'];
								$console .= "Exitsing discount: ".$discount[$itemkey]['name'].", discount: ".$discount[$itemkey]['amount']."\n";
							}								
						} // end all categories
						else { // specific category
						 	$console .= "Specific category:\n";
							$wherecat = " AND (productcategoryID= ".$row_rsPromotions['resultcategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['resultcategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['resultcategoryID']." OR parentcategory.subcatofID = ".$row_rsPromotions['resultcategoryID'].") ";
						} // end specific product
							
					 	if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							$console .= "(Products in basket)\n";										
							foreach($_SESSION['basket'] as $productID => $product) {
								foreach($product as $key => $option) {							
								//get price
									$selectadd ="";$joinadd = "";$whereadd= ""; $keys = explode("@@",$key);
									if($keys[0]>0) { // is option
										$selectadd .= " , productoptions.price AS optionprice ";
										$joinadd .= " LEFT JOIN productoptions ON (productoptions.productID = product.ID) ";
										$whereadd .= " AND productoptions.ID = ".$keys[0]." ";
									}
									$select = "SELECT product.price, productcategoryID ".$selectadd ." FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID)  LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID)  LEFT JOIN productmanufacturer AS parent ON (parent.ID = productmanufacturer.subsidiaryofID)  ".$joinadd." WHERE product.ID = ".$productID. $wherecat ."  AND ((productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND (parent.exclpromos IS NULL OR parent.exclpromos = 0)) AND productcategory.excludepromotions = 0".$whereadd;
									
									$result = mysql_query($select, $aquiescedb) or die(mysql_error());
									$console .= $select."=".mysql_num_rows($result)."\n";	
									if(mysql_num_rows($result)>0) {// category match
										
										$row = mysql_fetch_assoc($result);
										$console .= "CATEGORY MATCH: ".$product['name']."\n";
										
										
										foreach($_SESSION['basket'][$productID] as $key=>$value) {
											$price = isset($row['optionprice']) ? $row['optionprice'] : $row['price'];
											$totalpurchased += $value['quantity']*$price;
										}// end for each					
										
									} // category match
								} // each option
							} // end for each product
							$discount[$item]['ID'] = $row_rsPromotions['ID'];
							$discount[$item]['name'] = $row_rsPromotions['promotitle'];
							$discount[$item]['amount'] = number_format((($totalpurchased-$alreadydiscounted) * $row_rsPromotions['resultamount'] / 100),2,".","");
							$console .= "Bought: ".$totalpurchased.", discount: ".$row_rsPromotions['amount']."\n";
					 	} // end products in basket
							
							
							
							
							
				 	} else if($row_rsPromotions['resulttypeID']==5) { //  monetary discount on whole order
						$console .="** RESULT 5 [% Discount on whole order]\n";
						$discount[$item]['ID'] = $row_rsPromotions['ID'];
						$discount[$item]['name'] = $row_rsPromotions['promotitle'];
						$discount[$item]['amount'] = number_format($row_rsPromotions['resultamount'],2,".","");
						
						
						
						
						
						
					} else if($row_rsPromotions['resulttypeID']==6) { //  monetary discount on cat
						$console .="** RESULT 6 [% Discount on category]\n";
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
								foreach($_SESSION['basket'] as $productID => $product) {
							//get price
							$selectadd ="";$joinadd = "";
							if($product>0) {
								$selectadd =" , productoptions.price AS optionprice ";
								$joinadd = " LEFT JOIN productoptions ON (productoptions.productID = product.ID) ";
							}
									$select = "SELECT product.price, productcategoryID ".$selectadd."FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID)  LEFT JOIN productmanufacturer AS parent ON (parent.ID = productmanufacturer.subsidiaryofID) ".$joinadd." WHERE product.ID = ".$productID." AND (productcategoryID= ".$row_rsPromotions['resultcategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['resultcategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['resultcategoryID'].") AND ((productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND (parent.exclpromos IS NULL OR parent.exclpromos = 0))";
									
								
									
									$result = mysql_query($select, $aquiescedb) or die(mysql_error());
									
									if(mysql_num_rows($result)>0) {// category match
										$row = mysql_fetch_assoc($result);
										foreach($_SESSION['basket'][$productID] as $key=>$value) {
											$price = isset($row['optionprice']) ? $row['optionprice'] : $row['price'];
											$totalpurchased += $value['quantity']*$price;
										}// end for each
										
										$discount[$item]['ID'] = $row_rsPromotions['ID'];
										$discount[$item]['name'] = $row_rsPromotions['promotitle'];
										$discount[$item]['amount'] = $totalpurchased<$row_rsPromotions['resultamount'] ? number_format($totalpurchased,2,".","") :  number_format($row_rsPromotions['resultamount'],2,".","");
									} // category match
								} // end for ech
					 		} // end products in basket
					// end monetary discount on cat
					
					
					
					
					} else if($row_rsPromotions['resulttypeID']==7) { // free products in category
						$totalpurchased = 0;
						$discountamount = 0;
				 		$console .="** RESULT 7 [free products in category]\n";
						if(is_array($_SESSION['basket']) && !empty($_SESSION['basket'])) { // products in basket
							foreach($_SESSION['basket'] as $productID => $product) {
						//get price
								$select = "SELECT price, productcategoryID FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID)  LEFT JOIN productmanufacturer AS parent ON (parent.ID = productmanufacturer.subsidiaryofID)   WHERE product.ID = ".$productID." AND (".$row_rsPromotions['resultcategoryID']." = 0 OR productcategoryID= ".$row_rsPromotions['resultcategoryID']." OR productincategory.categoryID = ".$row_rsPromotions['resultcategoryID']." OR productcategory.subcatofID = ".$row_rsPromotions['resultcategoryID'].") AND ((productmanufacturer.exclpromos IS NULL OR productmanufacturer.exclpromos = 0) AND (parent.exclpromos IS NULL OR parent.exclpromos = 0))";
								
								$result = mysql_query($select, $aquiescedb) or die(mysql_error());
								
								if(mysql_num_rows($result)>0) {// category match
									$row = mysql_fetch_assoc($result);
									foreach($_SESSION['basket'][$productID] as $key=>$value) {
										$totalthisitem = ($value['quantity']+$totalpurchased) <= $row_rsPromotions['resultamount'] ? $value['quantity'] :  ($row_rsPromotions['resultamount']-$totalpurchased);
										$discountamount += $totalthisitem*$row['price'];
										$totalpurchased +=$totalthisitem ;
									}// end for each
									
									$discount[$item]['ID'] = $row_rsPromotions['ID'];
									$discount[$item]['name'] = $row_rsPromotions['promotitle'];
									$discount[$item]['amount'] = number_format($discountamount,2,".","");
								} // category match
							} // end for each
						} // end products in basket					
					} // free products in category						
			   } // end possible result 	
			   
			   
			   		   
			   if(isset($discount[$item]['name']) && $discount[$item]['name'] !="") { // we now have a promo to add...
			   		$standalone += $row_rsPromotions['standalone'];
					if($row_rsPromotions['progressivediscountgroup']>0) {
						$progressivediscountgroup[$row_rsPromotions['progressivediscountgroup']] ++;
					}
					$console .= $discount[$item]['name']." = STANDALONE: ".$standalone." PROGRESSIVE[".$row_rsPromotions['progressivediscountgroup']."] = ".$progressivediscountgroup[$row_rsPromotions['progressivediscountgroup']]."\n";
			   		$discount[$item]['actiontypeID'] = $row_rsPromotions['actiontypeID'];
			   		$discount[$item]['resulttypeID'] = $row_rsPromotions['resulttypeID'];
			   		$discount[$item]['amount'] = ($discount[$item]['amount'] > 0) ? $discount[$item]['amount'] : 0;
			   } // end add promotion				
		} // end promo valid
		else {
			$console .= "[not valid] \n";
		}
	} // end while
	mysql_free_result($rsPromotions); 
	
	return $discount;
}

function appendSagePay($name,$amount,$quantity=1,$vattype=1) {
	global $sagedescriptionlines, $vatinc,$vatrate;
	$sagestring = ":".str_replace(":","-",$name).":".$quantity.":";
	if($vattype==1) { // taxable
 if($vatinc==1) { // tax included
  $sagestring .= number_format($amount*(1-$vatrate/100),2,".","").":".number_format($amount*($vatrate/100),2,".","").":".number_format($amount,2,".","").":".number_format($amount,2,".","");
 } else { // tax not included
 $sagestring .= number_format($amount,2,".","").":".number_format($amount*(1+$vatrate/100),2,".","").":".number_format($amount*(1+$vatrate/100),2,".","").":".number_format($amount*(1+$vatrate/100),2,".","");
 }
	} else {
		$sagestring .= number_format($amount,2,".","").":0.00:0.00:0.00";
	}
   $sagedescriptionlines ++;
	return $sagestring;
}

function getShipping($items) {
	global $aquiescedb, $freeshipping, $totalshippingitems, $regionID, $console;
	$console .= "**********SHIPPING**********\n\n";

	$productgroup = array();
	$shipping = array();
	$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;
	
	$select = "SELECT shippingcalctype, shippingcalcbeforeaddress FROM productprefs WHERE ID = ".$regionID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result); 
	if(isset($row['shippingcalctype']) && $row['shippingcalctype']>0  && (!isset($_SESSION['bIsDeliverySame']) || $_SESSION['bIsDeliverySame'] <2)) {  // shipping to be added 
	
		$console .= "SHIPPING TO BE ADDED\n\n";
	  if($row['shippingcalcbeforeaddress'] == 1 || (isset($_SESSION['strDeliveryPostCode']) && isset($_SESSION['strDeliveryCountry']) && isset($_SESSION['shippingoptionID']))) { // data needed // do we have enough info?
	  $console .= "SUFFICIENT DATA \n\n";
	   //We need option (e.g. express) and distance (e.g. local, international)
		  $express = isset($_SESSION['shippingoptionID']) ? ($_SESSION['shippingoptionID']-1) : 0;
		  $console .= "EXPRESS = ".$express." \n\n";
		  if(($row['shippingcalctype']==1 || $row['shippingcalctype']==5) && $totalshippingitems>0) { // flat rate based only on distance and speed by basket or multiplied by number ofitems
		  	$console .= "**FLAT RATE / PER ITEM \n\n";
$weight = is_array($items['weight']) ? array_sum($items['weight']) : 0;			  $bestrate = findShippingRate($express,$weight); 
			  $console .= "RATE = ".$bestrate['shippingname']."\n\n";
			  $shipping[0]['name'] = $bestrate['shippingname'];
			  $shipping[0]['amount'] =  ($freeshipping >0 && $bestrate['promotion']==1) ? 0 : $bestrate['totalshipping'];	
			  $shipping[0]['amount'] = ($row['shippingcalctype']==5	) ? ($totalshippingitems*$shipping[0]['amount']) : $shipping[0]['amount'];	
		  }
		  
		  if($row['shippingcalctype']==2) { // per product (sum)
			  $console .= "**PER PRODUCT \n\n";
			  $shipping[0]['name'] = "Individual rates per item";
			  $shipping[0]['amount'] = 0;
			  foreach($items as $key => $value) {
				  if($freeshipping <1 && $items[$key]['shippingexempt'] !=1 && isset($items[$key]['shippingrateID'])) {
				  $select = "SELECT shippingrate FROM productshipping WHERE ID = ".$items[$key]['shippingrateID'];
				  $errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
				  $shippingrates = mysql_query($select, $aquiescedb) or die(mysql_error(). $errorsql);
				$shippingrate = mysql_fetch_assoc($shippingrates); 
				  $shipping[0]['amount'] += $shippingrate['shippingrate'];
				  }
			  }
			  
		  } // end per item sum
		  
		  
		  if($row['shippingcalctype']==4) { // per product (max)
			  $console .= "**PER PRODUCT MAX \n\n";
			  $shipping[0]['name'] = "No shipping";
			  $shipping[0]['amount'] = 0;
			  foreach($items as $key => $value) {
				  if($freeshipping <1 && $items[$key]['shippingexempt'] !=1 && isset($items[$key]['shippingrateID'])) {
					  $select = "SELECT shippingrate FROM productshipping WHERE ID = ".$items[$key]['shippingrateID'];
					  $errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
					  $shippingrates = mysql_query($select, $aquiescedb) or die(mysql_error(). $errorsql);
					  $shippingrate = mysql_fetch_assoc($shippingrates); 
					  if($shippingrate['shippingrate']>$shipping[0]['amount']) {
						   $shipping[0]['amount'] = $shippingrate['shippingrate'];
						   $shipping[0]['name'] = $shippingrate['shippingname'];
					  }
				  }
			  }
			  
		  } // end per item max
		  
		  if($row['shippingcalctype']==3) { // by weight 
		  $console .= "**BY WEIGHT \n\n";
			  $productgroup['hazardous'] = array();
			  $productgroup['nonhazardous'] = array();
			  $productgroup['both'] = array();
			  
			  foreach($items as $key => $item) {
				  if($item['shippingexempt']!=1) { // not exempt
					  if($item['hazardous'] ==1) {
						  $productgroup['hazardous'][$key] = $item['quantity']*$item['weight'];
					  } else {
						  $productgroup['nonhazardous'][$key] = $item['quantity']*$item['weight'];
					  }
				  $productgroup['both'][$key] = $item['quantity']*$item['weight'];
				  } // end not exempt
			  } // end for each item
			  if(array_sum($productgroup['hazardous'])>0) { // is hazardous products
			   $console .= "HAZARDOUS \n";
				  $weight = array_sum($productgroup['hazardous']);
				  $bestrate = findShippingRate($express,$weight,1);	$console .= "RATE = ".$bestrate['shippingname']."\n\n";
				  $rate['hazardous'] = $bestrate['totalshipping'] ? $bestrate['totalshipping'] : -1;
				  $rate['hazpromotion']  = $bestrate['promotion'];				  
			  } // end is hazardous
			  
			  if(array_sum($productgroup['nonhazardous'])>0) { // is other
			    $console .= "NON-HAZARDOUS \n";	
				  $weight = array_sum($productgroup['nonhazardous']);
				  $bestrate = findShippingRate($express,$weight,0);	
				  $rate['nonhazardous'] = $bestrate['totalshipping'] ? $bestrate['totalshipping'] : -1;	
				   $rate['nonhazpromotion']  = $bestrate['promotion'];
				   		
			  } // end is non hazardous
			  
			  if(array_sum($productgroup['nonhazardous'])>0 && array_sum($productgroup['hazardous'])>0) { // is other
				  $weight = array_sum($productgroup['both']) ;
				  $bestrate = findShippingRate($express,$weight,1);
				  $rate['both']  = $bestrate['totalshipping'] ? $bestrate['totalshipping'] : -1;
				  $rate['bothpromotion']  = $bestrate['promotion'];	
				   $console .= "BOTH \n";		
			  } // end is non hazardous
			  
			  if(isset($rate['both']) && $rate['both']> 0 && isset($rate['nonhazardous']) && $rate['nonhazardous']>0 && isset($rate['hazardous'])  && $rate['hazardous']>0 && $rate['both']<$rate['nonhazardous']+$rate['hazardous']) { // cheaper to do both together
				  $shipping[0]['name'] = "Hazardous/non-hazardous goods shipping";
				  $shipping[0]['amount'] =  ($freeshipping >0 && $rate['bothpromotion']==1) ? 0 : $rate['both'];	
			  } else {
				  if(isset($rate['hazardous']) && $rate['hazardous']>0) {
				  $shipping[0]['name'] = "Hazardous goods shipping";				 
				  $shipping[0]['amount'] =  ($freeshipping >0 && $rate['hazpromotion']==1) ? 0 : $rate['hazardous'];	
				  }
				  if(isset($rate['nonhazardous']) && $rate['nonhazardous']>0) {
				  $shipping[1]['name'] = "Standard goods shipping";
				  $shipping[1]['amount'] =  ($freeshipping >0 && $rate['nonhazpromotion']==1) ? 0 : $rate['nonhazardous'];
				
				  }
			  }
			  
		  } // end by weight
	  }// end data needed
	} // end shipping to be added 
		$console .= "**SHIPPING AMOUNT: ".$shipping[0]['amount']."\n";
	return $shipping;
}

function findShippingRate($express=0,$weight=0, $hazardous=0) {
	global $aquiescedb, $regionID, $console;
	$weight= floatval($weight);
	$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;
	if(isset($_SESSION['shippingrateID']) && $_SESSION['shippingrateID']>0) { // rate already set by customer choice
		$console .= "CUSTOMER CHOSEN\n";
		$select = "SELECT productshipping.ID, shippingname, shippingrate, ratemultiple, ratemultipleamount, promotion FROM productshipping  WHERE ID = ".intval($_SESSION['shippingrateID']);	
	} else { // work out best rate based on criteria
		$console .= "AUTO CHOSEN\n";
		$console .= "WEIGHT = ".$weight."\n";
		$postcode = isset($_SESSION['strDeliveryPostCode']) ? $_SESSION['strDeliveryPostCode'] : "";
		$countryID = isset($_SESSION['strDeliveryCountry']) ? $_SESSION['strDeliveryCountry'] : 240;
		$console .= "ZONE - POSTCODE ".$postcode." COUNTRY ID ".$countryID."\n\n";
		$select = "SELECT productshipping.ID, shippingname, shippingrate, ratemultiple, ratemultipleamount, productshippingzone.type, productshippingzone.bypostcode, promotion
		FROM productshipping 
		LEFT JOIN productshippingzone ON (productshipping.shippingzoneID = productshippingzone.ID AND productshippingzone.statusID = 1 ) 
		WHERE productshipping.statusID = 1 
		AND (productshipping.shippingzoneID = 0 OR (productshippingzone.type = ".getZoneType($postcode,$countryID)." AND (bypostcode IS NULL OR INSTR(bypostcode,':".getPostCodeArea($postcode).":')>0))) 
		AND  hazardous = ".$hazardous." 
		AND (".$weight." = 0 OR minweight IS NULL OR ".$weight." >= minweight) 
		AND (".$weight." = 0 OR maxweight IS NULL OR ".$weight."<= maxweight) 
		AND express = ".$express." 
		AND productshipping.regionID = ".$regionID." 
		ORDER BY shippingrate ASC LIMIT 1"; // give best rate 
		
		$console .= "SQL = ".$select."\n\n";
	}
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$console .= "MATCHING RESULTS = ".mysql_num_rows($result)."\n\n";
	if(mysql_num_rows($result)>0) {
		
		$row = mysql_fetch_assoc($result);
		$console .= "RATE: ".$row['shippingrate']."\n";
		//$postcodes = explode(",",$row['bypostcode']);	// to do
		// put full amount in totalshipping - still to add by item
		if($row['ratemultiple'] == 2) {
			$row['totalshipping'] = $row['shippingrate']*ceil($weight/$row['ratemultipleamount']);
		} else {
			$row['totalshipping'] = $row['shippingrate'];
		}
		$console .= "TOTAL: ".$row['totalshipping']."\n\n";
		return $row;
	} else {
		return false;
	}
}


function getZoneType($postcode,$countryID) {
	global $aquiescedb, $regionID, $console;
	$regionID = isset($regionID) ? $regionID : 1;
	$select = "SELECT countries.ID FROM countries LEFT JOIN region ON (region.ID = countries.regionID) WHERE (countries.regionID = 0 OR countries.regionID = ".$regionID.") AND countries.ID = ".intval($countryID);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$console .= "ZONE TYPE SQL = ".$select."\n\n";
	if(mysql_num_rows($result)>0) { // is country of delivery same as dispatch
		return 1; // national
	} else {
		return 2; // international
	}
}

function getPostCodeDistrict($postcode) {
	$postcode = preg_replace("/[^0-9A-Z]/","",strtoupper($postcode));
	// last part of (if any) Postcode always 3 digits so remove them
	$length = strlen($postcode);
	$postcode = $length > 4 ? substr($postcode,0,strlen($postcode)-3) : $postcode;
	return $postcode;	
}

function getPostCodeArea($postcode) {
	$postcode = getPostCodeDistrict($postcode);
	$postcode = preg_replace("/[0-9]/","",$postcode);
	return $postcode;
}

function addProductsTobasket($productID, $quantity, $options = array(), $area = false, $explanation = array(), $amount = array()) { // arrays but for backward compatibility will work without
// productID = 0 - one off payment
// price only with productID = 0 otherwise uses database price
	global $database_aquiescedb, $aquiescedb;
	$_SESSION['basket_total_items'] = isset($_SESSION['basket_total_items']) ? $_SESSION['basket_total_items'] : 0;
	$_SESSION['basket_grand_total'] = isset($_SESSION['basket_grand_total']) ? $_SESSION['basket_grand_total'] : 0;
	
	if(!is_array($productID)) {
		
		$productID = array($productID);
		$quantity = array($quantity);
		$options = array($options);
		$explanation = array($explanation);
		$amount = array($amount);
	}
	$optionID = array();
	$uploaded = getUploads(); 
	foreach($productID as $key=>$value) {
		$optiontext = "";		
		if(isset($uploaded['filename']) && is_array($uploaded['filename']) && isset($uploaded['filename'][$productID[$key]]['newname']) && strlen($uploaded['filename'][$productID[$key]]['newname'])>4 && !isset($uploaded['filename'][$productID[$key]]['error'])) { // file succcessfully uploaded 
			$filename = explode("_",$uploaded['filename'][$productID[$key]]['newname'],2);		
			$optiontext .= "File: ".$filename[1]." ";
			$uploadimage = true;			
		}	
		//print_r($options[$key]);
		$optionID[$key] = (isset($options[$key]['optionID']) && intval($options[$key]['optionID'])>0) ? $options[$key]['optionID'] : 0; // will be "none" if nothing chosen
		
		$optiontext .= (isset($options[$key]['version']) && $options[$key]['version']!="") ? $options[$key]['version']."; " : ""; // append if version
		$optiontext .= (isset($options[$key]['finish']) && $options[$key]['finish']!="") ? $options[$key]['finish']."; " : ""; // append if finish  
		
		$optiontext .= (isset($options[$key]['optiontext']) && $options[$key]['optiontext']!="") ? $options[$key]['optiontext']."; " : ""; // append if written option		
		$optiontext .= (isset($options[$key]['predictedamount']) && $options[$key]['predictedamount']!="") ? $options[$key]['predictedamount']." required; " : ""; // append if predictedamount
		$optionID[$key] .= ($optiontext !="") ? " @@ ".(stripslashes($optiontext)) : "";
		//die($optionID[$key]);
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['quantity'] = isset($_SESSION['basket'][$productID[$key]][$optionID[$key]]['quantity']) ? intval($_SESSION['basket'][$productID[$key]][$optionID[$key]]['quantity']) : 0; // if none in basket create variable
		
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['quantity'] += intval($quantity[$key]); // add number of products
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['explanation'] = $explanation[$key];
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['predictedamount'] =  (isset($options[$key]['predictedamount']) && $options[$key]['predictedamount']!="") ? $options[$key]['predictedamount']  : "";
		
		
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['productforuserID'] =  (isset($options[$key]['productforuserID']) && $options[$key]['productforuserID']!="") ? $options[$key]['productforuserID']  : "";
		
		
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['sampleofID'] =  (isset($options[$key]['sampleofID']) && $options[$key]['sampleofID']!="") ? $options[$key]['sampleofID']  : "";
		// amount (price) only for one-off payments (productID = 0)
		$_SESSION['basket'][$productID[$key]][$optionID[$key]]['amount'] =  ($productID[$key] ==0 && isset($amount[$key]) && $amount[$key]>0) ? floatval($amount[$key])  : "";
		if(isset($uploadimage)) {
			$_SESSION['basket'][$productID[$key]][$optionID[$key]]['thumbnail'] = $uploaded['filename'][$productID[$key]]['newname'];
			$_SESSION['basket'][$productID[$key]][$optionID[$key]]['uploadID'] = $uploaded['filename'][$productID[$key]]['uploadID'];
		}	
		mysql_query("UPDATE product SET popularity=popularity+1 WHERE ID=".intval($productID[$key]), $aquiescedb);
		$_SESSION['basket_total_items'] += $quantity[$key];
		$_SESSION['basket_grand_total'] += floatval($amount[$key]);
	} // end for each
	
}


function removeProductsFromBasket($productID, $optionID = "", $quantity = 1) { // 0 = remove all


	$optionID = ($optionID =="") ? 0: base64_decode($optionID);
	
	$_SESSION['basket'][$productID][$optionID]['quantity'] = isset($_SESSION['basket'][$productID][$optionID]['quantity']) ? $_SESSION['basket'][$productID][$optionID]['quantity'] : 0; // if none in basket create variable
	
	if($quantity == 0) {
		$_SESSION['basket'][$productID][$optionID]['quantity'] = 0;
	} else {
		$_SESSION['basket'][$productID][$optionID]['quantity'] -= $quantity; // remove from basket
	}
	if($_SESSION['basket'][$productID][$optionID]['quantity']<1) { // no more - or negative
		if(count($_SESSION['basket'][$productID])<=1) { // if only one product option left remove product
			unset($_SESSION['basket'][$productID]);
		} else { // remove product option
			unset($_SESSION['basket'][$productID][$optionID]);
		}
	}
	
}

function emptyBasket() {
	if(isset($_SESSION['basket'])) unset($_SESSION['basket']);
		if(isset($_SESSION['promocode'])) unset($_SESSION['promocode']);
		if(isset($_SESSION['basket_total_items'])) unset($_SESSION['basket_total_items']);
	if(isset($_SESSION['basket_grand_total'])) unset($_SESSION['basket_grand_total']);
}

function vatPrices($setprice = 0, $includesvat = 1, $vatrate = 0) {
	
	if($includesvat == 1) {
		$price['gross'] = $setprice ;
		$price['net'] = $vatrate>0 ? number_format($price['gross']/(1+($vatrate/100)),2) : $price['gross'];
		$price['vat'] = $price['gross'] - $price['net'];
		
	} else {
		$price['net'] = $setprice;
		$price['vat']= $vatrate>0 ? number_format(($price['net']*$vatrate/100),2) : 0;
		$price['gross']	 = 	$price['net'] + $price['vat'];
		
	}
	
	return $price;
}


function checkStock() {
	global $aquiescedb;
	$msg = "";
	if(isset($_SESSION['basket'])) {
		foreach($_SESSION['basket'] as $key => $product) { // for each product
			foreach($product as $option =>$details) { // for each product option
				if($details['quantity']>0 && $key >0) { // is items and not promo
					
					$select = "SELECT instock, title FROM product WHERE product.ID = ".GetSQLValueString($key,"int")." LIMIT 1";
					$rsProduct = mysql_query($select, $aquiescedb) or die(mysql_error());
					$row_rsProduct = mysql_fetch_assoc($rsProduct);
					if($details['quantity']>$row_rsProduct['instock']) {
						$_SESSION['basket'][$key][$option]['quantity'] = $row_rsProduct['instock'];
						$msg .= "Sorry, we currently only have ".$row_rsProduct['instock']." of ".$row_rsProduct['title']." in stock. Your basket has been updated to reflect this. Please contact us to find out when more will become available.\n";
					}
				}
			}
		}
	}
	return $msg;
}
	
	
	
	

?>