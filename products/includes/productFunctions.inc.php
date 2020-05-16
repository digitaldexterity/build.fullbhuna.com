<?php 
if(!function_exists("productLink")) {
	function productLink($productID=0, $productlongID="", $sectionID=0, $sectionlongID="", $manufacturerID = 0, $manufacturerlongID = "", $page=0, $query="", $force_rewrite = false) 
	{ 
		$shopurl = defined("SHOP_FRIENDLY_URL") ? "/".SHOP_FRIENDLY_URL."/" : "/shop/";	
		// page -1 = show all		
		if(($force_rewrite || defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && ($productID == 0 || $productlongID !="") && ($manufacturerID==0 || $manufacturerlongID != "") && ($sectionID==0 ||$sectionlongID !="")) { // use mod rewrite	
			if($sectionlongID =="")  $sectionlongID = "0";
			$url = $shopurl.$sectionlongID;
			$url .= ($manufacturerlongID!="") ? "/by/".$manufacturerlongID."/" : "";	
			$url .= ($productlongID!="") ? "/".$productlongID : "";	
			$url .= ($page!=0) ? "/page".$page : "";	
		} else {			
			$url = ($productID >0) ? "/products/product.php?categoryID=".$sectionID."&productID=".$productID : "/products/index.php?categoryID=".$sectionID;
			$url .= ($manufacturerID >0) ? "&manufacturerID=".$manufacturerID : "";
			$url .= ($page!=0) ? "&pageNum_rsProduct=".$page : "";
		}
		if($query!="") {			
			$params = explode("&", $query);
			$newParams = array();
			foreach ($params as $param) {
				if (stristr($param, "pageNum_rsProduct") == false && 
				  stristr($param, "totalRows_rsProduct") == false && 
				  stristr($param, "productID") == false && 
				  stristr($param, "categoryID") == false && 
				  stristr($param, "sectionID") == false && 
				  stristr($param, "redirectarticleID") == false && 
				  stristr($param, "manufacturerID") == false) {
				  array_push($newParams, $param);
				}
			}
			if (count($newParams) != 0) {
			  $query = htmlentities(implode("&", $newParams));	
			  $url.= strpos($url,"?") ? "&" : "?";
			  $url .= $query;
			}
		}
		return $url;	  
	} // end func
}

if(!function_exists("productMenu")) {
function productMenu($categoryID=0, $depth = 0 , $class="", $deluxe = false, $includeproducts = false) {
	global $aquiescedb, $site_name, 	$regionID;
	$html = "";
	$class= ($class!="") ? " class = \"".$class."\" " : "";
	$regionID = isset($regionID) ? $regionID : 1;
	$accesslevel = isset($_SESSION['MM_UserGroup']) ? $_SESSION['MM_UserGroup'] : 0;
	$select = "(SELECT 1 AS iscategory, productcategory.title, NULL AS productID, NULL AS productlongID, productcategory.ID AS categoryID, productcategory.longID AS categorylongID FROM productcategory WHERE productcategory.subcatofID = ".$categoryID." AND productcategory.statusID = 1 AND productcategory.showinmenu = 1 AND productcategory.accesslevel <= ".$accesslevel. " AND productcategory.regionID = ".$regionID." ORDER BY productcategory.ordernum)";		
	$select .= ($includeproducts) ? " UNION (SELECT -1 AS iscategory,  product.title, product.ID AS productID, product.longID AS productlongID, productcategory.ID AS categoryID, productcategory.longID AS categorylongID FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE product.productcategoryID = ".$categoryID." AND product.statusID =1  AND productcategory.statusID = 1 AND productcategory.accesslevel <= ".$accesslevel. " AND productcategory.regionID = ".$regionID." ORDER BY productcategory.ordernum)" : "";
	$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
	if (mysql_num_rows($result)>0 || mysql_num_rows($result)>0) {
		$html .= ($html == "") ? "<ul".$class." itemscope itemtype=\"http://www.schema.org/SiteNavigationElement\">\r\n" : "<ul".$class.">\r\n"; 
		
		while ($row = mysql_fetch_assoc($result)) { // loop items			
			$html .= "<li itemprop=\"name\" class=\"category".$row['categoryID'];
			$html .= isset($row['productID']) ? " product product".$row['productID'] : "";
			$html .= (@$_GET['categoryID'] == $row['categoryID'] || @$_GET['categoryID'] == $row['categorylongID']) ? " selected" : "";
			$html .= "\"><a itemprop=\"url\" href=\"".productLink($row['productID'], $row['productlongID'], $row['categoryID'], $row['categorylongID'])."\" title=\"".$row['title']."\">" .$row['title']."</a>";
			// look for sub cats if below max depth
			if($depth>=1 && $row['iscategory']==1) {	// is category and depth remaining
				if($deluxe) {
					$html .= productSubMenu($row['categoryID'], $row['categorylongID']);
				} else {
					$html .= productMenu($row['categoryID'],$depth-1,"",$deluxe,$includeproducts); // here is the recursion 
				}
			}
			$html .= "</li>\r\n";			
		} // end loop items
		$html .= "<li class=\"viewall\"><a href=\"".productLink("", "", $categoryID)."\" title=\"View all\">View all</a></li>";
		$html .= "</ul>\r\n";
	}
	return $html;
}
}



if(!function_exists("productSubMenu")) {
	function productSubMenu($categoryID, $categorylongID) {
	global $aquiescedb, $site_name, $row_rsProductPrefs, $regionID;
	$html = "<ul class=\"deluxe\">\r\n<li>\r\n<div class=\"columns\">";
	
	$select = "SELECT  productversion.ID, productversion.versionname FROM product 
LEFT JOIN productwithversion ON (product.ID = productwithversion.productID) 
LEFT JOIN productversion ON (productwithversion.versionID = productversion.ID) 
LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
WHERE product.statusID = 1 AND productversion.ID>0 AND (".intval($categoryID)." = parentcategory.ID OR productcategory.ID = ".intval($categoryID).") GROUP BY productversion.versionname ORDER BY productversion.ordernum";
$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin	

$versions = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
if(mysql_num_rows($versions)>=1) { // is versions

$html .= "<div class=\"column size\"><h3>";
$html .= isset($row_rsProductPrefs['versiontitle']) ? $row_rsProductPrefs['versiontitle'] : "Thickness";
$html .= "</h3><ul>\r\n";
		while($version = mysql_fetch_assoc($versions)) {
			$link = productLink(0, "", $categoryID, $categorylongID);
			$link .= strpos($link,"?")===false ? "?" : "&";
			//$link .= "versionID=".$version['ID'];
			$link .= "version=".urlencode(str_replace(" ","_",$version['versionname'])); 
			$html .= "<li itemprop=\"name\"><a itemprop=\"url\" href=\"".$link."\" title =\"".htmlentities($version['versionname'], ENT_COMPAT, "UTF-8")."\" >".htmlentities($version['versionname'], ENT_COMPAT, "UTF-8")."</a></li>\r\n";
		}
		$html .= "</ul></div>\r\n";
   }  // end is versions
	
	

	
$select = "SELECT  productfinish.ID, productfinish.finishname FROM product 
LEFT JOIN productwithfinish ON (product.ID = productwithfinish.productID) 
LEFT JOIN productfinish ON (productwithfinish.finishID = productfinish.ID) 
LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
LEFT JOIN productinregion ON (productinregion.productID = product.ID)
WHERE product.statusID = 1 AND productfinish.ID>0 AND (".intval($categoryID)." = parentcategory.ID OR productcategory.ID = ".intval($categoryID).") AND  ((productinregion.regionID IS NULL AND ".intval($regionID)." = 1) OR productinregion.regionID = ".intval($regionID).") GROUP BY productfinish.finishname ORDER BY productfinish.ordernum";
$errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin	
$finishes = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
if(mysql_num_rows($finishes)>=1) { // is finishes

$html .= "<div class=\"column colour\"><h3>";
$html .= isset($row_rsProductPrefs['finishtitle']) ? $row_rsProductPrefs['finishtitle'] : "Colour";
$html .= "</h3><ul>\r\n";
		while($finish = mysql_fetch_assoc($finishes)) {
			$link = productLink(0, "", $categoryID, $categorylongID);
			$link .= strpos($link,"?")===false ? "?" : "&";
			//$link .= "finishID=".$finish['ID'];
			$link .= "finish=".urlencode(str_replace(" ","_",$finish['finishname']));
			$html .= "<li itemprop=\"name\"><a itemprop=\"url\" href=\"".$link."\" title =\"".htmlentities($finish['finishname'], ENT_COMPAT, "UTF-8")."\" >".htmlentities($finish['finishname'], ENT_COMPAT, "UTF-8")."</a></li>\r\n";
		}
		$html .= "</ul></div>\r\n";
   }  // end is finishes
   
   $collections = productMenu($categoryID,1);
	if($collections!="") {
	$html .= "<div class=\"column category\"><h3>Category</h3>".$collections."</div>";
	}
	
	$where = (isset($row_rsProductPrefs['manufacturershowsubs'])  && $row_rsProductPrefs['manufacturershowsubs']==1) ? "" : " AND mf.subsidiaryofID IS NULL ";
	$select = "SELECT manufacturerID, name, ordernum, manufacturerlongID FROM (SELECT  mf.ID AS manufacturerID, mf.longID AS manufacturerlongID, mf.manufacturername AS name, mf.ordernum AS ordernum FROM product 
LEFT JOIN productinregion ON (productinregion.productID = product.ID) 
LEFT JOIN productmanufacturer AS mf ON (mf.ID = product.manufacturerID) 
LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
WHERE (productinregion.regionID = 0 OR productinregion.regionID = ".$regionID.") AND (".intval($categoryID)." = parentcategory.ID OR productcategory.ID = ".intval($categoryID).") AND product.statusID =1".$where."
UNION ALL
SELECT  mf.ID  AS manufacturerID, mf.longID AS manufacturerlongID, mf.manufacturername AS name, mf.ordernum FROM product 
LEFT JOIN productinregion ON (productinregion.productID = product.ID) 
LEFT JOIN productmanufacturer ON (productmanufacturer.ID = product.manufacturerID) 
LEFT JOIN productmanufacturer AS mf ON (productmanufacturer.subsidiaryofID = mf.ID)
LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID)
LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID  )  
WHERE (productinregion.regionID = 0 OR productinregion.regionID = ".$regionID.") AND (".intval($categoryID)." = parentcategory.ID OR productcategory.ID = ".intval($categoryID).")  AND product.statusID =1".$where.") t 
 GROUP BY manufacturerID ORDER BY ordernum, name ASC";
 $errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$select : ""; // only have full select statement if webadmin	
	$manufacturers = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql );

	
	if(mysql_num_rows($manufacturers)>0) {// is manufacturers
		$html .= "<div class=\"column manufacturer\"><h3>";
$html .= isset($row_rsProductPrefs['manufacturertitle']) ? $row_rsProductPrefs['manufacturertitle'] : "Brand";
$html .= "</h3><ul>\r\n";
		while($manufacturer = mysql_fetch_assoc($manufacturers)) {
			//if($_SESSION['MM_UserGroup']==10) echo $manufacturer['manufacturerID'].":". $manufacturer['manufacturerlongID'];
			$link = productLink(0, "", $categoryID, $categorylongID,$manufacturer['manufacturerID'], $manufacturer['manufacturerlongID']);
			
			$html .= "<li itemprop=\"name\" class=\"manufacturer".$manufacturer['manufacturerID']."\"><a itemprop=\"url\" href=\"".$link."\" title =\"".htmlentities($manufacturer['name'], ENT_COMPAT, "UTF-8")."\"  >".htmlentities($manufacturer['name'], ENT_COMPAT, "UTF-8")."</a></li>\r\n";
		}
		$html .= "</ul></div>\r\n";
		//if($_SESSION['MM_UserGroup']==10) die();
	}// is manufacturers	
	
	/** TAGS **/
	
	
	
	$select = "SELECT producttag.ID, producttag.tagname, producttag.taggroupID, producttaggroup.taggroupname FROM producttag LEFT JOIN producttaggroup ON (producttaggroup.ID = producttag.taggroupID) LEFT JOIN producttagged ON (producttag.ID = producttagged.tagID) LEFT JOIN product ON (producttagged.productID = product.ID) LEFT JOIN productincategory ON (product.ID = productincategory.productID) WHERE  product.statusID = 1 AND  (".intval($categoryID)." = 0 OR productincategory.categoryID = ".intval($categoryID)." OR product.productcategoryID = ".intval($categoryID)." )  AND (producttaggroup.regionID = 0 OR producttaggroup.regionID = ".$regionID.") GROUP BY producttag.ID ORDER BY producttaggroup.ordernum, producttag.ordernum";
	
	$tagresult = mysql_query($select, $aquiescedb) or die(mysql_error().$errorsql);
	
	if(mysql_num_rows($tagresult)>0) { // tags exist
		$taggroupID = 0;	
		
		$html .= "<div class=\"column taggroup taggroup_".$taggroupID."\">\r\n";
	
		while($tagnames = mysql_fetch_assoc($tagresult)) { 			
			if($taggroupID != $tagnames['taggroupID']) { // new tag group 
				
				if($taggroupID != 0) $html .= "</ul></div><div class=\"column taggroup taggroup_".$taggroupID."\">";// previous existed
				$taggroupID = $tagnames['taggroupID'];	
				$html .= "<h3>".$tagnames['taggroupname']."</h3><ul>\r\n";		}	
				$link = productLink(0, "", $categoryID, $categorylongID);
				$link .= strpos($link, "?")>0 ? "&" : "?";
				$link .= "tagID=".$tagnames['ID'];
			
			$html .= "<li itemprop=\"name\"><a itemprop=\"url\" href=\"".$link."\" title =\"".htmlentities($tagnames['tagname'], ENT_COMPAT, "UTF-8")."\"  >".htmlentities($tagnames['tagname'], ENT_COMPAT, "UTF-8")."</a></li>\r\n";		
		}
		$html .= "</ul></div>\r\n";
	} // tags exist	
	$html .= "</div>\r\n</li>\r\n</ul>";
	return $html;
}
}

if(!function_exists("saveProductMenu")) {
	function saveProductMenu($regionID=1) {
	// save hard copy of deluxe menu for use if required
	$html = productMenu(0,4,"sf-menu product-menu", true);
	saveFile(UPLOAD_ROOT."menu/productMenu".$regionID.".inc.php",$html);
}
}



if(!function_exists("productTitle")) {
	function productTitle($productRecord) {
	global $row_rsProductPrefs;
	$productTitle = isset($row_rsProductPrefs['producttitle']) ? 	$row_rsProductPrefs['producttitle'] : $productRecord['title'];
	
	preg_match_all("/{+(.*?)}/",$productTitle,$matches);
	foreach($matches[1] as $key=> $value) {
		
		$productTitle = str_replace("{".$value."}",$productRecord[$value],$productTitle);
		
	}
	return $productTitle;
}
}

if(!function_exists("addDays")) {
	// function to add week days (and miss out bank holidays etc.)
	function addDays($timestamp, $days, $endofday = "23:59:59", $skipdays = array("Saturday", "Sunday"), $skipdates = NULL) {
        // $skipdays: array (Monday-Sunday) eg. array("Saturday","Sunday")
        // $skipdates: array (YYYY-mm-dd) eg. array("2012-05-02","2015-08-01");
       //timestamp is strtotime of ur $startDate
        $i = 1;
				
		if(date("H:i:s",$timestamp)>$endofday) {
			$timestamp = strtotime("+1 day", $timestamp);
		}
		if ( (is_array($skipdays) && ( in_array(date("l", $timestamp), $skipdays))) || (is_array($skipdates) && (in_array(date("Y-m-d", $timestamp), $skipdates))) )
            {
                $days++;
            }
		

        while ($days >= $i) {
            $timestamp = strtotime("+1 day", $timestamp);
            if ((is_array($skipdays) && (in_array(date("l", $timestamp), $skipdays))) || (is_array($skipdates)  && (in_array(date("Y-m-d", $timestamp), $skipdates))))
            {
                $days++;
            }
			
            $i++;
        }

        return $timestamp;
    }
}

if(!function_exists("getDeliveryTimes")) {
	function getDeliveryTimes($minperiod=0,$maxperiod=0,$periodincrement=0, $availablefrom="", $endofday="23:59:59") {
		$dates = array();
		$fromdate = ($availablefrom !="" && $availablefrom>date('Y-m-d H:i:s')) ? $availablefrom : date('Y-m-d H:i:s');
		if(isset($minperiod) && $minperiod>0 && isset($maxperiod) && $maxperiod>0 ) { 
			
				if($minperiod>0) {
					if($periodincrement==24) { // days
						$mintimestamp = addDays(strtotime($fromdate), $minperiod, $endofday); //$returntext .= "*".$fromdate."*";
					} else if($periodincrement==168) { // weeks
						$mintimestamp = strtotime($fromdate ." + ".$minperiod." WEEKS");
					} else { //hours
						$mintimestamp = strtotime($fromdate ." + ".$minperiod." HOURS");
					}
					$dates['fromdatetime'] = date('Y-m-d H:i:s', $mintimestamp);					
				}
				if($maxperiod>0) {
					if($periodincrement==24) { // days
						$maxtimestamp = addDays(strtotime($fromdate), $maxperiod, $endofday);
					} else if($periodincrement==168) { // weeks
						$maxtimestamp = strtotime($fromdate ." + ".$maxperiod." WEEKS");
					} else { //hours
						$maxtimestamp = strtotime($fromdate ." + ".$maxperiod." HOURS");
					}
					$dates['todatetime'] = date('Y-m-d H:i:s', $maxtimestamp);					
				}		
  		} 
		return $dates; 
	}
}

if(!function_exists("showDeliveryTime")) {
	function showDeliveryTime($minperiod=0,$maxperiod=0,$periodincrement=0, $availablefrom="", $endofday="23:59:59") {
		$returntext = "";
		$dates = getDeliveryTimes($minperiod,$maxperiod,$periodincrement, $availablefrom, $endofday);
		if(isset($dates['fromdatetime'])) {
			$returntext .= " Delivered ";
			$returntext .= date('l jS F', strtotime($dates['fromdatetime']));
			if($periodincrement<24) {
				$returntext .= ", ".date('g:ia', strtotime($dates['fromdatetime']));
			}
		}
		if(isset($dates['todatetime']) && $dates['todatetime']>$dates['fromdatetime']) {
			$returntext .= " to ". date('l jS F', strtotime($dates['todatetime']));	
			if($periodincrement<24) {
				$returntext .= ", ".date('g:ia', strtotime($dates['fromdatetime']));
			} 
		}
		return $returntext; 
	}
}


if(!function_exists("getPeriodSales")) {
	function getPeriodSales($startday="", $endday="") {
		$startday = ($startday =="") ? "CURDATE()" : "'".date('Y-m-d', strtotime($startday))."'";
		
		if($endday=="") {
			$period = " DATE(createddatetime) = ".$startday;
		} else {
			$period = " DATE(createddatetime) <= ".$startday." AND DATE(createddatetime) >= '".date('Y-m-d', strtotime($endday))."'";
		}
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$query_rsShopOrders = "SELECT SUM(productorders.Amount) AS total_amount, COUNT(productorders.VendorTxCode) AS num_orders FROM productorders WHERE ".$period." AND  (Status LIKE 'APPROVED%' OR Status LIKE 'AUTHORISED%' OR Status LIKE 'COMPLETED%')";
		$rsShopOrders = mysql_query($query_rsShopOrders, $aquiescedb) or die(mysql_error().": ".$query_rsShopOrders);
		$row_rsShopOrders = mysql_fetch_assoc($rsShopOrders);
		if(mysql_num_rows($rsShopOrders)>0) {
			return $row_rsShopOrders;
		} else {
			return false;
		}		
	}
}
 ?>