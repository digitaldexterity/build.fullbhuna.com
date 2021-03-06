<?php if(!isset($aquiescedb)) { // if called by ajax
	require_once('../../Connections/aquiescedb.php'); 
 	require_once('../../core/includes/framework.inc.php');
	require_once('productHeader.inc.php'); 
}

$console = isset($console) ? $console : "";

if(!function_exists("showPrice")) {
function showPrice($row_rsProduct) {
	global $row_rsProductPrefs, $row_rsThisCategory, $row_rsThisRegion;
	switch($row_rsThisRegion['currencycode']) {
		case "GBP" :  $currency = "&pound;"; break;
		case "EUR" :  $currency = "&euro;"; break;
		case "USD" :  $currency = "$"; break;
		default :  $currency = $row_rsThisRegion['currencycode']." "; 		
	}
	$html = "<div class=\"productprice\">";
	$vatdefault = isset($row_rsThisCategory['vatdefault']) ? $row_rsThisCategory['vatdefault'] : 0;
	$vatincluded = ($vatdefault ==0 && isset($row_rsThisCategory['vatincluded'])) ? $row_rsThisCategory['vatincluded'] : (isset($row_rsProductPrefs['vatincluded']) ? $row_rsProductPrefs['vatincluded'] : 1);
	$vatprice = ($vatdefault ==0 && isset($row_rsThisCategory['vatprice'])) ? $row_rsThisCategory['vatprice'] : (isset($row_rsProductPrefs['vatprice']) ? $row_rsProductPrefs['vatprice'] : 0);
	$vattext = ($vatdefault ==0 && isset($row_rsThisCategory['vattext'])) ? $row_rsThisCategory['vattext'] : (isset($row_rsProductPrefs['vattext']) ? $row_rsProductPrefs['vattext'] : 0); 

	if (@$row_rsProduct['saleitem']==1) { 
		$html .= "<div class=\"saleitem\">Offer</div>";
	}
	if (isset($row_rsProduct['listprice']) && $row_rsProduct['listprice']>0) { 
		$html .= "<span class=\"rrp\"> <span class=\"pricetext label\">Was:&nbsp;</span> ";
		$html .= "<span class=\"pricecurrency\">";  
		$html .= $currency;
		$html .="</span><span class=\"priceamount\">";
		$html .= ($row_rsProduct['area']>0 && ((isset($row_rsProductPrefs['showareaprice']) && $row_rsProductPrefs['showareaprice']==1) || (isset($row_rsProduct['showareaprice']) && $row_rsProduct['showareaprice']==1))) ? number_format(($row_rsProduct['listprice']/$row_rsProduct['area']),2,".",",")."<span class=\"area\">/m&sup2;</span>": number_format($row_rsProduct['listprice'],2,".",","); 
		$html .="</span></span>";
	} 
	
	
	$html .="<span class=\"sellingprice\" ><span class=\"pricetext label\">Price:&nbsp;</span>";
        
	if(@$row_rsProduct['addfrom'] ==1) {
		  $html .="<span class=\"pricefrom\">From&nbsp;</span>";
		   } 
		    if(isset($row_rsProduct['price']) && $row_rsProduct['price']>0) { 
				$html .="<span class=\"pricecurrency\">";  				
				$html .= $currency;
				$html .="</span>";
            } 
			$html .="<span class=\"priceamount\">";
			if(@$row_rsProduct['price']>0) {
				$html .= 	(isset($row_rsProductPrefs['showareaprice']) && $row_rsProductPrefs['showareaprice']==1 && $row_rsProduct['area']>0) ? number_format(($row_rsProduct['price']/$row_rsProduct['area']),2,".",",")."<span class=\"area\">/m&sup2;</span>": number_format($row_rsProduct['price'],2,".",",");
			} else { 
			$html .=  $row_rsProductPrefs['nopricetext']; } 
			$html .="</span>";
                
             $html .=   "<span class=\"pricetype\">";
              switch($row_rsProduct['pricetype']) {
					case 1 : break;
					case 2 : $html .= " per kg"; break;
					case 3 : $html .= " per hour"; break;
					case 4 : $html .= " per day"; break;
					case 0 : $html .= " ".@$row_rsProduct['priceper']; break;
				}           
                
                $html .="</span>";
                
               if($vattext==1) { 
                $html .="<span class=\"vattext\">";
                $html .=($vatincluded==0) ? "<span class=\"excluding\"> ex. VAT</span>" : "<span class=\"including\"> inc. VAT</span>";
				$html .= "</span>";
                } 
                
				 $html .="</span>"; // selling price
				 
               if($vatprice==1 && @$row_rsProduct['price']>0 && $row_rsProduct['vattype']>0) { 
			   	$vatrate = ($row_rsProduct['vattype']>1) ? $row_rsProduct['ratepercent'] : $row_rsThisRegion['vatrate'];
				$vatprice = ($vatincluded==1) ? ($row_rsProduct['price']/(1+$vatrate/100)) : ($row_rsProduct['price']*(100+$vatrate)/100); 
				
                $html .="<span class=\"incvatprice\">";
				$html .= "(".$currency.number_format($vatprice,2,".",",");
				$html .= ($vatincluded==1) ? " ex. VAT)" : " inc. VAT)"; 
                $html .= "</span>";
				} 
				 if($row_rsProduct['shippingexempt']==1) { 
				 $html .= "<span class=\"shippingexempt\">Includes Delivery</span>";
                 } $html .="</div>";
				 
	return $html;			
} // end function get price
}

if(!function_exists("getProducts")) {
function getProducts($regionID = 1, $sortby = "", $categoryID =-1, $manufacturerID = array(), $count = 0, $page = 1,  $shownav = false,$relatedtoID = 0, $search = "", $tags = array(), $versions = array(), $finishes = array(), $prices = array(), $showproducts="", $productclass="") {
	global $database_aquiescedb, $aquiescedb, $row_rsProductPrefs, $totalRows_rsSubCategories, $totalRows_rsIndexManufacturers, $row_rsThisCategory, $row_rsThisManufacturer, $console;
	
	
	$categoryID = intval($categoryID);
	$select = "";
	$join = "";
	$filter = false;
	$where = " WHERE product.statusID = 1 AND  ((productinregion.regionID IS NULL AND ".intval($regionID)." = 1) OR productinregion.regionID = ".intval($regionID).")";

	$relatedorderby="";
	$having = "";
	
	if($showproducts!="") { // show specigi products from comma delimited list
		$products = explode(",", $showproducts);
		
		$subwhere = "";
		foreach ($products as $productID) {
			$subwhere .= ($subwhere=="") ? "" : " OR ";
			$subwhere .= " product.ID = ".intval($productID);
		}
		$where .= " AND (".$subwhere.") ";
		
	}
	
	if(!is_array($manufacturerID)) $manufacturerID = array($manufacturerID); // convert select  to checkbox post array

	if(count($manufacturerID)>0) {
		$filter = true;		
		$sub_where = "";
		foreach($manufacturerID as $key => $value) {
			if($value!="" && $value !='0') {				
				$sub_where .= ($sub_where =="") ? "" : " OR ";
				$sub_where .= " product.manufacturerID = ".intval($value)." OR productmanufacturer.longID = ".GetSQLValueString($value, "text");	}
		}
		$where .=  ($sub_where!="")	? " AND (".$sub_where.") " : "";
	}

	$sortby = ($sortby !="") ? $sortby : (isset($row_rsProductPrefs['defaultsort']) ? $row_rsProductPrefs['defaultsort']   : "ordernum");


	switch($sortby) {
		case "createddatetime_desc" : $orderby = "datetimecreated DESC"; break; 
		 case "price_asc" : $orderby = "CASE WHEN area >= 0 THEN (product.price/product.area) ELSE product.price END ASC"; break; 
		case "price_desc" : $orderby = "CASE WHEN area >= 0 THEN (product.price/product.area) ELSE product.price END DESC"; break; 
		case "title_asc" : $orderby = "title ASC"; break; 
		case "popularity_desc" : $orderby = "product.popularity DESC"; break;
		default: $orderby = "featuredproduct DESC,  listorder ASC"; // ordernum
	}
	
	if(!is_array($prices)) $prices = array($prices); 
		if(count($prices)>0) {
		$filter = true;
		$min = 0; $max = 99999999;
		foreach($prices as $key => $value) {
			if(strpos($value,"-")!==false) {
				$range = explode("-",$value);
				$min = $range[0]<$min ? $range[0] : $min;
				$max = $range[1]>$max ? $range[1] : $max;
			}
		}
		$where .= " AND product.price >= ".intval($min)." AND product.price < ".intval($max);
	}

// tags sent either thourgh tagID - select menu, or tag[x] - array from checkboxes

	if(!is_array($tags)) $tags = array($tags); // convert select  to checkbox post array
	
	$tagsearchtype = isset($_GET['tagsearchtype']) ? $_GET['tagsearchtype'] : $row_rsProductPrefs['tagsearchtype']; // 1 = any , 2= all
	
	if(count($tags)>0) { //tags
		$filter = true;
		$tagcount = 0; 
		$sub_where = "";
		foreach($tags as $key => $value) {
			if(intval($value)>0) {
				$tagcount ++;
				$sub_where .= ($sub_where =="") ? "" : " OR ";
				$sub_where .= " producttagged.tagID = ".intval($value)." ";
			}
		}
		if($tagcount>0) {
			$select .= " ,(SELECT COUNT(ID) FROM producttagged WHERE productID = product.ID AND (";
			$select .= $sub_where.")) AS tagged";
			$having .= ($having != "") ? " AND " : " HAVING ";
			$having .= ($tagsearchtype ==1) ? " tagged > 0" : " tagged = ".$tagcount;
			$relatedorderby .= " tagged DESC,";
		}	
	}
	
	// version sent either thourgh versionID - select menu, or version[x] - array from checkboxes
	
	if(!is_array($versions)) $versions = array($versions); // convert select  to checkbox post array
	if(count($versions)>0) { //versions
		$filter = true;
		$versioncount = 0; 
		$sub_where = "";
		foreach($versions as $key => $value) {
			if($value!="" && $value!='0') {// for text and numeric based ids
				$versioncount ++;
				$value = str_replace("_"," ", $value); // for text based ids
				$sub_where .= ($sub_where =="") ? "" : " OR ";
				$sub_where .= " productwithversion.versionID LIKE ".GetSQLValueString($value, "text")." OR productversion.versionname = ".GetSQLValueString($value, "text");
			}
		}
		if($versioncount>0) {
			$select .= " ,(SELECT COUNT(productwithversion.ID) FROM productwithversion LEFT JOIN productversion ON (productwithversion.versionID = productversion.ID) WHERE productID = product.ID AND (";
			$select .= $sub_where.")) AS hasversion";
			$having .= ($having != "") ? " AND " : " HAVING ";
			$having .= ($tagsearchtype ==1) ? " hasversion > 0" : " hasversion = ".$versioncount;
			$relatedorderby .= " hasversion DESC,";
		}
	}
	
	
	// version sent either thourgh versionID - select menu, or version[x] - array from checkboxes
	
	if(!is_array($finishes))  $finishes = array($finishes); // convert select  to checkbox post array
	if(count($finishes)>0) { //versions
		$finishcount = 0;
		$filter = true; 
		$sub_where = "";	
		foreach($finishes as $key => $value) {
			if($value!="" && $value!='0') { // for text and numeric based ids
				$finishcount ++;
				$value = str_replace("_"," ", $value); // for text based ids
				$sub_where .= ($sub_where =="") ? "" : " OR ";
				$sub_where .= " productwithfinish.finishID LIKE ".GetSQLValueString($value, "text")." OR productfinish.finishname = ".GetSQLValueString($value, "text");
			}
		}
		if($finishcount>0) {		
			$select .= " ,(SELECT COUNT(productwithfinish.ID) FROM productwithfinish LEFT JOIN productfinish ON (productwithfinish.finishID = productfinish.ID) WHERE productID = product.ID  AND (";
			$select .= $sub_where.")) AS hasfinish";
			$having .= ($having != "") ? " AND " : " HAVING ";
			$having .= ($tagsearchtype ==1) ? " hasfinish > 0" : " hasfinish = ".$finishcount;
			$relatedorderby .= " hasfinish DESC,";
		}
	}
	
	
	if(!is_array($categoryID))  $categoryID = array($categoryID); // convert select  to checkbox post array
	
	if ($categoryID[0]!=-1) { // not 'any' category
		if(count($categoryID)>0) { //versions
			$categorysql = "";
			foreach($categoryID as $key => $thisCategoryID) {
				if($thisCategoryID!="" && $thisCategoryID!='0') { // for text and numeric based ids		
					$categorysql .= ($categorysql == "") ? "" : " OR ";
					$categorysql .=  " product.productcategoryID = ".$thisCategoryID." OR productincategory.categoryID = ".$thisCategoryID;
					// if we have any filter then we search sub categories too (although just for main category just now)
					$categorysql  .= (isset($row_rsProductPrefs['searchsubcats']) && (($filter==true && $row_rsProductPrefs['searchsubcats']==1) || $row_rsProductPrefs['searchsubcats']==2)) ? " OR parentcategory.ID = ".$thisCategoryID." OR suppcat.subcatofID= ".$thisCategoryID." " :"";				
				} // has value				
			} // end for each
			$categorysql = ($categorysql !="") ? " AND (".$categorysql.") " : "";
		} // is category filter
	} // not 'any' category
	$categoryID = $categoryID[0]; // for later main cat stuff
	
	if($relatedtoID>0) {
	// we will assume we are on product page or basket and only main category is chosen here 				
		  $select .= ", productrelated.ID AS relatedID, product.relatedall AS relatedtoall";
		  $join .= " LEFT JOIN productrelated ON  ((product.ID = productrelated.productID AND productrelated.relatedtoID = ".intval($relatedtoID).") OR (product.ID = productrelated.relatedtoID AND productrelated.productID = ".intval($relatedtoID)."))";
		  $where .= " AND product.ID != ".intval($relatedtoID);
		  
		  if($categoryID >0) {
			  $having = " HAVING (".$categoryID." = -1 OR product.productcategoryID = ".$categoryID."   OR incatID  = ".$categoryID." OR parentID = ".$categoryID."  OR  ((relatedID>0 OR product.relatedall = 1) AND ".$categoryID." >0))";
		  }
		  $orderby = $relatedorderby." relatedID DESC, relatedtoall DESC,  RAND() DESC";
		  $categorysql = ""; // clear category WHERE as this is now dealt with in HAVING
	  } 
	
	
	if($page  <= 0 ) { // negative or zero page means show all
		$page  = 1;
		$maxproducts =  10000;
	} else { 
		$maxproducts =  $count>0 ? intval($count) : (defined("DEFAULT_PRODUCTS_COUNT") ? DEFAULT_PRODUCTS_COUNT : 7);
	}
	
	
	$maxRows_rsProduct = $maxproducts;
	$startRow_rsProduct = ($page-1) * $maxRows_rsProduct;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$query_rsProduct = "SELECT  product.*, (product.productcategoryID = ".$categoryID.") AS maincategory, productcategory.longID AS categorylongID,  product.ID AS prodID, productcategory.freesamplesku, (SELECT pc.categorysale FROM productincategory AS pic LEFT JOIN productcategory AS pc ON (pic.categoryID = pc.ID) WHERE pic.productID = product.ID AND pc.categorysale = 1 LIMIT 1) AS catinsale, productcategory.categorysale, productcategory.imageURL3, parentcategory.categorysale AS parentsale, productcategory.title AS categoryname, productmanufacturer.manufacturersale,  productmanufacturer.manufacturername, product.ordernum AS listorder,  product.featured AS featuredproduct, parentcategory.ID AS parentID, productincategory.categoryID AS incatID, productvatrate.ratepercent, AVG(forumcomment.rating) AS avgrating, COUNT(forumcomment.rating) AS ratingCount".$select." FROM product  LEFT JOIN productoptions ON (productoptions.productID = product.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID)  LEFT JOIN productcategory AS suppcat ON (suppcat.ID = productincategory.categoryID)  LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID) LEFT JOIN productvatrate ON (product.vattype = productvatrate.ID) LEFT JOIN forumtopic ON (forumtopic.productID = product.ID AND  forumtopic.statusID=1) LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID AND forumcomment.statusID=1)".$join.$where.$categorysql."  GROUP BY product.ID  ".$having." ORDER BY ".$orderby." ";
	$query_limit_rsProduct = sprintf("%s LIMIT %d, %d", $query_rsProduct, $startRow_rsProduct, $maxRows_rsProduct);
	$console .= $query_limit_rsProduct."\n";
	$rsProduct = mysql_query($query_limit_rsProduct, $aquiescedb) or die(mysql_error());
	
	$sql = $query_rsProduct; // for debug
	if (isset($_GET['totalRows_rsProduct'])) {
	  $totalRows_rsProduct = $_GET['totalRows_rsProduct'];
	} else {
	  $all_rsProduct = mysql_query($query_rsProduct);
	  $totalRows_rsProduct = mysql_num_rows($all_rsProduct);
	}
	$totalPages_rsProduct = ceil($totalRows_rsProduct/$maxRows_rsProduct)-1;
	
	
	$queryString_rsProduct = "";
	if (!empty($_SERVER['QUERY_STRING'])) {
	  $params = explode("&", $_SERVER['QUERY_STRING']);
	  $newParams = array();
	  foreach ($params as $param) {
		if (stristr($param, "pageNum_rsProduct") == false && 
			stristr($param, "totalRows_rsProduct") == false) {
		  array_push($newParams, $param);
		}
	  }
	  if (count($newParams) != 0) {
		$queryString_rsProduct = "&" . htmlentities(implode("&", $newParams));
	  }
	}
	$queryString_rsProduct = sprintf("&totalRows_rsProduct=%d%s", $totalRows_rsProduct, $queryString_rsProduct);
	
	$html = ""; $navhtml = "";
	$query = http_build_query(array_merge($_POST,$_GET));  
	
	if($shownav) { 
		// changed from POST to GET here to stop "form resubmit" dialogue - not sure if 100% works everywhere
		$navhtml .= "<form method=\"get\" action=\"".productLink(0,"", $categoryID,$row_rsThisCategory['longID'])."\">";
		// populate with all request values to re-post (except tags>)
		foreach(array_merge($_POST,$_GET) as $name=>$value) {
		  if($name != "pageNum_rsProduct" && substr($name,0,3) != "tag") {
			if(is_array($value)) {
				foreach($value as $key => $arrayvalue) {
					$navhtml .= "<input type=\"hidden\" name =\"".htmlentities($name,ENT_COMPAT, "UTF-8")."[".htmlentities($key,ENT_COMPAT, "UTF-8")."]\" value = \"".htmlentities($arrayvalue,ENT_COMPAT, "UTF-8")."\">\n";
				}
			} else {
			$navhtml .= "<input type=\"hidden\" name =\"".htmlentities($name,ENT_COMPAT, "UTF-8")."\" value = \"".htmlentities($value,ENT_COMPAT, "UTF-8")."\">\n";
			}
		  }
	}
	
	$navhtml .= "<span class=\"productNavigation\"><span class=\"productSort\">Sort by: <select name=\"sortby\"  onchange=\"this.form.submit()\">";
	$navhtml .= "<option value=\"ordernum\"";
	if($sortby=="ordernum" ) $navhtml .=  "selected=\"selected\""; 
	$navhtml .= ">Relevance</option>";
	$navhtml .= "<option value=\"createddatetime_desc\"";
	if($sortby=="createddatetime_desc" ) $navhtml .=  "selected=\"selected\"";  
	$navhtml .= ">Recently added</option>";
	$navhtml .= "<option value=\"price_asc\"";
	if($sortby=="price_asc" ) $navhtml .=  "selected=\"selected\""; 
	$navhtml .= ">Price (low-high)</option>";
	$navhtml .= "<option value=\"price_desc\"";
	if($sortby=="price_desc" )  $navhtml .= "selected=\"selected\""; 
	$navhtml .= ">Price (high-low)</option>";
	$navhtml .= "<option value=\"title_asc\"";
	if($sortby=="title_asc" )  $navhtml .= "selected=\"selected\"";  
	$navhtml .= ">Alphabetically</option>";
	$navhtml .= "<option value=\"bestselling_desc\"";
	if($sortby=="bestselling_desc" ) { $navhtml .= "selected=\"selected\""; } 
	$navhtml .= ">Best selling</option></select>";
	$navhtml .= "&nbsp;&nbsp;</span>";
	if(($totalPages_rsProduct+1)>1) {
		$navhtml .= "<span class=\"productItemCount\">Items ". ($startRow_rsProduct + 1)." to ".min($startRow_rsProduct + $maxRows_rsProduct, $totalRows_rsProduct)." of ". $totalRows_rsProduct."&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		if ($page>1) { // Show if not first page 
			$link = productLink(0,"", $categoryID,$row_rsThisCategory['longID'],$row_rsThisManufacturer['ID'],$row_rsThisManufacturer['longID'], max(0, $page - 2), $query);
			$navhtml .= "<a href=\"".$link."\">&laquo;&nbsp;Previous</a>&nbsp;&nbsp;";
		} // Show if not first page 	
		for($pagelink=1; $pagelink<=$totalPages_rsProduct+1; $pagelink++) {
			$link = productLink(0,"", $categoryID,$row_rsThisCategory['longID'],$row_rsThisManufacturer['ID'],$row_rsThisManufacturer['longID'], $pagelink-1, $query);
			$navhtml .=  ($page != $pagelink) ? "<a href=\"".$link."\">".$pagelink."</a>&nbsp;&nbsp;" : $pagelink."&nbsp;&nbsp;";
		}  // end for
	}// end pagination
			  
	if ($page-1 < $totalPages_rsProduct) { // Show if not last page
			  
		$link = productLink(0,"", $categoryID,$row_rsThisCategory['longID'],$row_rsThisManufacturer['ID'],$row_rsThisManufacturer['longID'], min($totalPages_rsProduct, $page), $query); 
	  $navhtml .= "<a href=\"".$link."\">Next&nbsp;&raquo;</a>";
	} // Show if not last page
		   
	$link = productLink(0,"", $categoryID,$row_rsThisCategory['longID'],$row_rsThisManufacturer['ID'],$row_rsThisManufacturer['longID'], -1, $query);
		  
	if($totalPages_rsProduct>0) { 
		$navhtml .= "&nbsp;&nbsp;<a href=\"".$link."\">Show all</a>";  
	} 
	
	/** PRODUCT FILTERS **/
	$navhtml .= "</span><span class=\"productfilters\">";
	/** TAGS **/
	$select = "SELECT producttag.ID, producttag.tagname, producttag.taggroupID, producttaggroup.taggroupname FROM producttag LEFT JOIN producttaggroup ON (producttaggroup.ID = producttag.taggroupID) LEFT JOIN producttagged ON (producttag.ID = producttagged.tagID) LEFT JOIN product ON (producttagged.productID = product.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE  product.statusID = 1 AND  (".intval($_GET['categoryID'])." = 0 OR productincategory.categoryID = ".intval($_GET['categoryID'])." OR product.productcategoryID = ".intval($_GET['categoryID'])." ) GROUP BY producttag.ID ORDER BY producttaggroup.ordernum, producttag.ordernum";
	$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
	$tagresult = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
	
	if(mysql_num_rows($tagresult)>0) { // tags exist
		$taggroupID = 0;	
		while($tagnames = mysql_fetch_assoc($tagresult)) { 
			if($taggroupID != $tagnames['taggroupID']) { // new tag group 
				if($taggroupID != 0) $navhtml .= "</select>"; // previous existed
				$taggroupID = $tagnames['taggroupID'];	
				$tagID = isset($_REQUEST['tagID'][$taggroupID]) ? 		$_REQUEST['tagID'][$taggroupID] : (isset($_REQUEST['tagID']) ? $_REQUEST['tagID'] : 0);
				$navhtml .= "<select name=\"tagID[".$taggroupID."]\" class=\"tagselect\"  onchange=\"this.form.submit()\">";
				$navhtml .= "<option value=\"0\">Filter by ".$tagnames['taggroupname']."...</option>";
				$navhtml .= "<option value=\"0\" ";
					if($tagID==0) $navhtml .=  "selected=\"selected\"";
				 $navhtml .= ">Any ".$tagnames['taggroupname']."</option>";
			}	
			$navhtml .= "<option value=\"". $tagnames['ID']."\""; 
				if($tagnames['ID']==$tagID) $navhtml .=  "selected=\"selected\""; 
			$navhtml .= ">".$tagnames['tagname']."</option>";
		}
		$navhtml .= "</select>"; 
	} // tags exist
	
	if(isset($row_rsProductPrefs['versionfilter']) && $row_rsProductPrefs['versionfilter'] ==1) {
		$select = "SELECT  productversion.ID, productversion.versionname FROM product 
		LEFT JOIN productwithversion ON (product.ID = productwithversion.productID) 
		LEFT JOIN productversion ON (productwithversion.versionID = productversion.ID) 
		LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
		LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
		WHERE product.statusID = 1 AND productversion.ID>0 AND (".intval($_GET['categoryID'])." = parentcategory.ID OR productcategory.ID = ".intval($_GET['categoryID']).") GROUP BY productversion.versionname ORDER BY productversion.ordernum";
		$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
		$versionresult = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
		if(mysql_num_rows($versionresult)>0) {
			$navhtml .= "<select name=\"version\" id=\"versionselect\"   onchange=\"this.form.submit()\">";
			$navhtml .= "<option value=\"0\">Filter by ". $row_rsProductPrefs['versiontitle']."...</option>";
			$navhtml .= "<option value=\"0\">Any ". $row_rsProductPrefs['versiontitle']."</option>";
			while($versionnames = mysql_fetch_assoc($versionresult)) { 
				$navhtml .= "<option value=\"".$versionnames['ID']."\"";
				if(isset($_REQUEST['version']) && ($versionnames['ID']==$_REQUEST['version'] || $versionnames['versionname']==$_REQUEST['version']))  $navhtml .=  "selected=\"selected\""; 
				$navhtml .= ">".$versionnames['versionname']."</option>";
			} 
		$navhtml .= "</select>"; 
		} 
	} 
	  
	if(isset($row_rsProductPrefs['finishfilter']) && $row_rsProductPrefs['finishfilter'] ==1) {	  
		$select = "SELECT  productfinish.ID, productfinish.finishname FROM product 
		LEFT JOIN productwithfinish ON (product.ID = productwithfinish.productID) 
		LEFT JOIN productfinish ON (productwithfinish.finishID = productfinish.ID) 
		LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
		LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
		WHERE product.statusID = 1 AND productfinish.ID>0 AND (".intval($_GET['categoryID'])." = parentcategory.ID OR productcategory.ID = ".intval($_GET['categoryID']).") GROUP BY productfinish.finishname ORDER BY productfinish.ordernum";
		$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
		$finishresult = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
		if(mysql_num_rows($finishresult)>0) { 
			$navhtml .= "<select name=\"finish\" id=\"finishselect\"   onchange=\"this.form.submit()\">";
			$navhtml .= "<option value=\"0\">Filter by ". $row_rsProductPrefs['finishtitle']."...</option>";
			$navhtml .= "<option value=\"0\">Any ". $row_rsProductPrefs['finishtitle']."</option>";
			while($finishnames = mysql_fetch_assoc($finishresult)) { 
				$navhtml .= "<option value=\"".$finishnames['ID']."\"";
				if(isset($_REQUEST['finish']) && ($finishnames['ID']==$_REQUEST['finish'] || $finishnames['finishname']==$_REQUEST['finish'])) { $navhtml .=  "selected=\"selected\""; } 
				$navhtml .= ">".$finishnames['finishname']."</option>";
			} 
			$navhtml .= "</select>"; 
		} 
	}
	
	$thismanufacturerID = isset($row_rsThisManufacturer['ID']) ? $row_rsThisManufacturer['ID'] : (isset($_REQUEST['manufacturerID']) ? intval($_REQUEST['manufacturerID']) : -1);
	$select = "SELECT productmanufacturer.ID, productmanufacturer.manufacturername FROM productmanufacturer LEFT JOIN  product ON (productmanufacturer.ID = product.manufacturerID) LEFT JOIN productincategory ON (product.productcategoryID = productincategory.categoryID) WHERE (productmanufacturer.ID = ".$thismanufacturerID." OR ".intval($_GET['categoryID'])." = 0 OR productincategory.categoryID = ".intval($_GET['categoryID'])." OR product.productcategoryID = ".intval($_GET['categoryID']).") GROUP BY productmanufacturer.ID ORDER BY productmanufacturer.manufacturername ASC";
	$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin
	$manufacturers = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
	if(isset($row_rsProductPrefs['manufacturerfilter']) && $row_rsProductPrefs['manufacturerfilter'] ==1 && mysql_num_rows($manufacturers)>1) {
		$navhtml .= "<select name=\"manufacturerID\" id=\"manufacturerselect\"   onchange=\"this.form.submit()\">";
		$navhtml .= "<option value=\"\">Filter by ". $row_rsProductPrefs['manufacturertitle']."...</option>";
		$navhtml .= "<option value=\"\">Any ". $row_rsProductPrefs['manufacturertitle']."</option>";
		while($manufacturer = mysql_fetch_assoc($manufacturers)) {
			$navhtml .= "<option value=\"".$manufacturer['ID']."\"";
			if($thismanufacturerID==$manufacturer['ID']) { $navhtml .=  "selected=\"selected\""; } 
			$navhtml .= ">".$manufacturer['manufacturername']."</option>";
		} 
		$navhtml .= "</select>"; 
	} 
	
	$navhtml .= "</span></form>";
	
	$html .="<div class=\"pageNavigation top\">".$navhtml."</div>";
	} // end show nav 
	if($totalRows_rsProduct==0 && (!isset($totalRows_rsSubCategories) || $totalRows_rsSubCategories==0) && (!isset($totalRows_rsIndexManufacturers) || $totalRows_rsIndexManufacturers==0)) { 
		$msg= "Sorry, there are currently no items available matching your selection. Please try another search.";
		$html .= "<p class=\"message\">".$msg."</p>";
	} 
	if(($totalRows_rsProduct > 0)){ 

		$html.= displayProducts($rsProduct, $categoryID, $query);
	} // Show if recordset not empty 
	
	mysql_free_result($rsProduct); 
	if($shownav && $totalRows_rsProduct > 0) {
			$html .= "<div class=\"pageNavigation bottom\">".$navhtml."</div>";
		} 				
	return $html;
} // end function getProducts()
}

if(!function_exists("displayProducts")) {
function displayProducts($products, $categoryID="", $query="", $productclass="", $newwindow=false, $host = "") {
	//$newwindow and  $host if displaying and linking toanother site's products
	//$host MUST be full https://www.domain.com
	
	// categoryID to optionally get actual links for chosen category rather than product main category
	global $row_rsProductPrefs;
	$productclass = ($productclass != "") ? $productclass : (defined("PRODUCT_CLASS") ? PRODUCT_CLASS : "col-md-3 col-sm-4");
	
		$html = "<div class=\"row products\">";
		// REPLICATE ON SEARCH FOR NOW
		$item = 0; while($row_rsProduct = mysql_fetch_assoc($products)) { $item ++; 
			$thiscategoryID = (is_numeric($categoryID) && $categoryID>0) ? $categoryID : $row_rsProduct['productcategoryID'];
			$categorylongID = (is_string($categoryID)) ? $categoryID : $row_rsProduct['categorylongID'];
			$productLink = productLink($row_rsProduct['ID'], $row_rsProduct['longID'], $thiscategoryID,$categorylongID,"","",0,$query);
			if($host!="") {
				$productLink = $host.$productLink;				
			}
			$html .= "<!--  shopItem --><div class=\"product".$row_rsProduct['ID']." category".$thiscategoryID." ".$productclass." shopItem item". $item;
			$html .=  ($row_rsProduct['saleitem']==1) ? " sale productsale" : ""; 
			$html .=  (isset($row_rsProduct['catinsale']) || $row_rsProduct['parentsale']==1 || $row_rsProduct['categorysale']==1) ? " sale categorysale" : ""; 
			$html .=  ($row_rsProduct['manufacturersale']==1) ? " sale manufacturersale" : ""; 
			$html .= "\" ><div itemscope itemtype=\"http://schema.org/Product\" >"; // extra div for styling wrapper and schema
			$html .= "<a class=\"productimage\" href=\"".$productLink."\"  title=\"". str_replace("&lt;br&gt;","<br>",htmlentities(productTitle($row_rsProduct), ENT_COMPAT, "UTF-8"))."\"";
			$html .= $newwindow ? " target=\"_blank\" " : "";
			$html .="><img src=\"";
			$html .= isset($row_rsProduct['imageURL']) ? getImageURL($row_rsProduct['imageURL'],$row_rsProductPrefs['imagesize_index']) : getImageURL($row_rsProductPrefs['defaultImageURL'],$row_rsProductPrefs['imagesize_index']);
		  
			$html .= "\" alt=\"". htmlentities(strip_tags($row_rsProduct['title']),ENT_COMPAT, "UTF-8")."\" class=\"".$row_rsProductPrefs['imagesize_index']."\" /><span class=\"productImageOverlay\">";
			$overlayimageURL = isset($row_rsProduct['imageURL3']) ? $row_rsProduct['imageURL3'] : (isset($row_rsProductPrefs['imageOverlayURL']) ? $row_rsProductPrefs['imageOverlayURL'] : "");
			if(trim($overlayimageURL)!="") { 
				$html .= "<img src = \"/Uploads/". $overlayimageURL."\" alt=\"Overlay image\"  />";
			} 
			$html .= "</span></a>";
			$html .= "<div class=\"producttext\">";
			$html .= "<h3 itemprop=\"name\"><a href=\"".$productLink."\"  title=\"". str_replace("&lt;br&gt;","<br>",htmlentities(productTitle($row_rsProduct), ENT_COMPAT, "UTF-8"))."\">".str_replace("&lt;br&gt;","<br>",htmlentities($row_rsProduct['title'], ENT_COMPAT, "UTF-8"))."</a></h3>";
			$html .= "<div class=\"productdescription\">". strip_tags(truncate($row_rsProduct['description'], "..."))."</div>";
			$html .= "<!-- product rating --><div  itemprop=\"aggregateRating\" itemscope itemtype=\"http://schema.org/AggregateRating\"  class=\"rating starrating rating".intval($row_rsProduct['avgrating'])."\">Rating: <span itemprop=\"ratingValue\">".intval($row_rsProduct['avgrating'])."</span>
			<meta itemprop=\"worstRating\" content=\"0\" />
			<meta itemprop=\"bestRating\" content=\"10\" />
			<meta itemprop=\"ratingCount\" content=\"".intval($row_rsProduct['ratingCount'])."\" /></div>";
			$html .= "<!-- product buy --><div class = \"buyproduct\"> ";
			$html .= showPrice($row_rsProduct); 
			$html .= "<form class=\"formMoreInfo\" method = \"post\" action=\"".$productLink."\">";
			$html .= "<input type=\"submit\" value=\"".$row_rsProductPrefs['moreinfotext']."\" class=\"moreinfo button\"  />
			<input type=\"hidden\" name =\"addtobasket\" value=\"true\" />
			<input type=\"hidden\" name =\"productID\" value=\"".$row_rsProduct['ID']."\" />";
			 if(($row_rsProduct['price']>0 || $row_rsProductPrefs['nopricebuy']==1) && $row_rsProduct['instock']>0) { 
				$html .= "<input type=\"submit\" value=\"".$row_rsProductPrefs['addtobasket']."\" class=\"addtobasket button\" onclick=\"this.form.action='/products/basket/'; this.form.submit(); return false;\"  />";
				if(isset($row_rsProduct['freesamplesku']) && $_SESSION['MM_UserGroup']==10) { 
				$html .= " <input type=\"hidden\" name=\"freesamplesku\"  value=\"".$row_rsProduct['freesamplesku']."\">";				
				$html .= "<input type=\"hidden\" name=\"sampleofID\"  value=\"".$row_rsProduct['ID']."\"><input type=\"button\" name=\"sample\" value=\"Order sample\" class=\"button shopButton freesample ajax\"   >";
				}
				
			 }
			 $html .= "</form>";
			 $html .= "</div><!-- end product buy --></div> <!-- end product text --> ";
			 $html .= "</div></div><!-- end shopItem -->";
		  }// end while loop
		$html .= "</div><!-- end products -->";	
		
	
	return $html;
}
}
?>