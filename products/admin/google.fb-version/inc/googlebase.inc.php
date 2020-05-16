<?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); 




if(!function_exists("createGoogleProductFeed")) {
function createGoogleProductFeed($categoryID = 0, $manufacturerID = 0, $options = false, $excelcompat = false) {
	// $options - separate feed out into the options if these each have unique identifiers
	global $database_aquiescedb, $aquiescedb, $regionID, $thisRegion;
	
	mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".intval($regionID);
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);


	
	$where = "";
	if($categoryID>0) {
		$where .= " AND product.productcategoryID = ".intval($categoryID)." ";
	}
	if($manufacturerID>0) {
		$where .= " AND product.manufacturerID = ".intval($manufacturerID)." ";
	}
	
	$where .= " AND (parentcategory.statusID IS NULL OR parentcategory.statusID=1)  AND productcategory.statusID  = 1 ";
	
	$where .=  ($row_rsProductPrefs['googlemerchantoutofstock']==0) ? " AND product.instock > 0 " : "";
	
	$where .= ($row_rsProductPrefs['googlemerchantpreorder']==0) ? " AND (product.availabledate IS NULL OR product.availabledate <=CURDATE()) " : ""; 
	
	$groupby = $options ?  "" : " GROUP BY product.ID ";
	$where .= $options ? " AND (product.upc IS NOT NULL || productoptions.upc IS NOT NULL || product.mpn IS NOT NULL || productoptions.mpn IS NOT NULL) " :  " AND (product.upc IS NOT NULL || product.mpn IS NOT NULL ) ";

$maxRows_rsProducts = 10000;
$pageNum_rsProducts = 0;
if (isset($_GET['pageNum_rsProducts'])) {
  $pageNum_rsProducts = $_GET['pageNum_rsProducts'];
}
$startRow_rsProducts = $pageNum_rsProducts * $maxRows_rsProducts;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProducts = "SELECT product.title, product.ID, product.productcategoryID, productcategory.longID AS categorylongID, product.longID,  product.sku, product.`description`, 'New' as `condition`,  product.price, product.instock,product.imageURL, NULL AS shipping, NULL AS shippingweight, product.upc, productmanufacturer.manufacturername, product.mpn, productcategory.gbasecat, productcategory.title AS category, NULL AS imageURL2, NULL AS color, NULL AS size, NULL AS gender, NULL as agegroup, NULL as groupID, NULL AS saleprice, NULL AS saledate , product.vattype, productcategory.vatdefault, productcategory.vatincluded , product.showareaprice, product.area, product.availabledate,
productoptions.optionname,  productoptions.mpn AS optionmpn, productoptions.upc AS optionupc, productoptions.price AS optionprice FROM product LEFT JOIN productoptions ON (productoptions.productID = product.ID) LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) LEFT JOIN productcategory AS parentcategory ON (productcategory.subcatofID = parentcategory.ID)  LEFT JOIN productmanufacturer ON (product.manufacturerID = productmanufacturer.ID) LEFT JOIN productinregion ON (productinregion.productID = product.ID) WHERE product.custompageURL IS NULL AND productcategory.gbasecat IS NOT NULL AND productmanufacturer.manufacturername IS NOT NULL AND product.statusID = 1 AND product.price >0 AND ((productinregion.regionID IS NULL AND ".intval($regionID)." = 1) OR productinregion.regionID = ".intval($regionID).")".$where ;

$query_limit_rsProducts = sprintf("%s LIMIT %d, %d", $query_rsProducts, $startRow_rsProducts, $maxRows_rsProducts);
$rsProducts = mysql_query($query_limit_rsProducts, $aquiescedb) or die(mysql_error().": ".$query_rsProducts);


if (isset($_GET['totalRows_rsProducts'])) {
  $totalRows_rsProducts = $_GET['totalRows_rsProducts'];
} else {
  $all_rsProducts = mysql_query($query_rsProducts);
  $totalRows_rsProducts = mysql_num_rows($all_rsProducts);
}
$totalPages_rsProducts = ceil($totalRows_rsProducts/$maxRows_rsProducts)-1;

$queryString_rsProducts = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsProducts") == false && 
        stristr($param, "totalRows_rsProducts") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsProducts = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsProducts = sprintf("&totalRows_rsProducts=%d%s", $totalRows_rsProducts, $queryString_rsProducts);




$text = "title\tlink\tid\tdescription\tcondition\tprice\tavailability\timage link\tshipping\tshipping weight\tgtin\tbrand\tmpn\tgoogle product category\tproduct type\tadditional image link\tcolor\tsize\tgender\tage group\titem group id\tsale price\tsale price effective date\r\n";
if($totalRows_rsProducts>0) {
	$unique_identifier  = "";
	
	while($row = mysql_fetch_assoc($rsProducts)) {
			$gtin = ($options && isset($row['optionupc'])) ? $row['optionupc'] : $row['upc'];
			$mpn = ($options && isset($row['optionmpn'])) ? $row['optionmpn'] : $row['mpn'];
			$new_ui = (isset($gtin) && $gtin!="") ? $gtin :$mpn;
			if($new_ui!= "" && $new_ui!=$unique_identifier) {
				// make has UI and sure not duplicate of previous (e.g. from options)
				$unique_identifier = $new_ui;
		
			
		
			
			if(isset($thisRegion['hostdomain']) && trim($thisRegion['hostdomain'])!="") {
				$host = (!isset($thisRegion['www']) || $thisRegion['www']==1) ? "www.".$thisRegion['hostdomain'] : $thisRegion['hostdomain'];
			} else {
				$host =  $_SERVER['HTTP_HOST'];
			}
						$protocol = getProtocol()."://";
			
			$link  = $protocol.$host.productLink($row['ID'], $row['longID'], $row['productcategoryID'],$row['categorylongID']);
			if(isset($row['imageURL']) && $row['imageURL'] !="") {
				$imageURL  = is_readable(SITE_ROOT."/Uploads/".$row['imageURL']) ? $protocol.$host."/Uploads/".$row['imageURL'] : $protocol.$host.getImageURL($row['imageURL'], "large");
			} else {
				$imageURL = "";
			}
			
			$vatinc = ($row['vatdefault']==0) ? $row['vatincluded'] : $row_rsProductPrefs['vatincluded'];
			$vatrate = ($row['vattype']>0) ? $thisRegion['vatrate'] : 0;
			$id = isset($row['sku']) ? $row['sku'] : "ID-".$row['ID'];
			
			

			
			$title = ($options && isset($row['optionname'])) ? $row['optionname'] : $row['title'];
			$text.= (formatCSV($title,"striptags","","\t", $excelcompat, false))."\t";	
			$text.= $link."\t";	
			$text.= (formatCSV($id,"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($row['description'],"striptags","\t", $excelcompat, true))."\t";	
			$text.= (formatCSV($row['condition'],"","\t", $excelcompat, false))."\t";	
			//$text.= (formatCSV(number_format($row['price'],2),"","\t", true, false))."\t";
			$price = ($options && isset($row['optionprice'])) ? $row['optionprice']: $row['price'];
			$price = ($row['area']>0 && ((isset($row_rsProductPrefs['useareaquantity']) && $row_rsProductPrefs['useareaquantity']==1) || (isset($row['showareaprice']) && $row['showareaprice']==1))) ? number_format(($price/$row['area']),2,".",",") : $price;
			$prices = vatPrices($price, $vatinc, $vatrate); 
			if($row_rsProductPrefs['gbaseVAT']==1) {
				$text.= (formatCSV(number_format($prices['gross'],2),"","\t", $excelcompat, false))."  GBP\t";	
			} else if($row_rsProductPrefs['gbaseVAT']==2) {
				$text.= (formatCSV(number_format($prices['net'],2),"","\t", $excelcompat, false))."  GBP\t";	
			} else {
				$text.= (formatCSV(number_format($price,2),"","\t", $excelcompat, false))." GBP\t";
			}
			
				
			
			//$text.= (formatCSV(number_format($vatrate,2),"","\t", $excelcompat, false))."\t";	
			$availibility = ($row['instock']<1 || (isset($row['availabledate']) && $row['availabledate']>date('Y-m-d'))) ? "out of stock" : "in stock";
			$text.= (formatCSV($availibility,"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($imageURL,"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($row['shipping'],"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($row['shippingweight'],"","\t", $excelcompat, false))."\t";	
			
			
			$text.= (formatCSV($gtin,"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($row['manufacturername'],"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($mpn,"","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($row['gbasecat'],"exact","\t", $excelcompat, false))."\t";	
			$text.= (formatCSV($row['category'],"","\t", $excelcompat, false))."\t";	
			$text.= "\t\t\t\t\t\t\t\r\n";					
						
					
					
				
			}
	}
	}
	return $text;
		
}
}

if(!function_exists("writeGoogleFeedFile")) {

function writeGoogleFeedFile() {
	global $regionID;
	$text = createGoogleProductFeed();
	saveFile(UPLOAD_ROOT."feeds/merchantcenter".$regionID.".txt",$text);
}
}


?>
