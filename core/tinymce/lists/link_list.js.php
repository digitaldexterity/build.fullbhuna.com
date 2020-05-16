<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php
if(is_readable(SITE_ROOT.'products/includes/functions.inc.php')) {
	 require_once(SITE_ROOT.'products/includes/functions.inc.php');
}?>
<?php $regionID = isset($regionID) ? intval($regionID) : 1;


$protocol = 'http://';

		if ((isset($_SERVER['HTTPS']) && ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ))
				|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
		{
			$protocol = 'https://';
		}

$host = $protocol . $_SERVER['HTTP_HOST'];
		
		
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

echo "[\n";

if(is_readable(SITE_ROOT.'articles/includes/functions.inc.php')) {
	require_once(SITE_ROOT.'articles/includes/functions.inc.php'); 


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLinks = sprintf("SELECT article.ID, article.longID, article.title, article.sectionID, articlesection.longID AS sectionlongID, articlesection.description FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.statusID = 1 AND article.versionofID IS NULL AND article.regionID = %s ORDER BY articlesection.ordernum, article.ordernum", GetSQLValueString($regionID, "int"));
$rsLinks = mysql_query($query_rsLinks, $aquiescedb) or die(mysql_error());
$row_rsLinks = mysql_fetch_assoc($rsLinks);
$totalRows_rsLinks = mysql_num_rows($rsLinks);
 
// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There flash movies will be displayed as a dropdown in all media dialog if the "media_external_list_url"
  // option is defined in TinyMCE init.





	echo "{\"title\": \"Home Page\", \"value\": \"/\"},\n";
	echo "{\"title\":\"\", \"value\":\"/\"}";
	// mind the array commas!
	do { 
	echo ",\n{\"title\":";
	echo isset($row_rsLinks['description']) ? json_encode($row_rsLinks['description']." : ".$row_rsLinks['title']) : json_encode($row_rsLinks['title']);
	echo ", \"value\": "; 
	echo  json_encode($host.articleLink($row_rsLinks['ID'],$row_rsLinks['longID'], $row_rsLinks['sectionID'], $row_rsLinks['sectionlongID']));
	echo "}";
	} while ($row_rsLinks = mysql_fetch_assoc($rsLinks));
	
	mysql_free_result($rsLinks);
	
}
	
	if($row_rsArticlePrefs['productlinks']==1 && function_exists("productLink")) {
	
	$select = "SELECT productcategory.ID, productcategory.longID, productcategory.title FROM  productcategory  WHERE productcategory.statusID = 1 AND (productcategory.regionID = 0 OR productcategory.regionID = ".GetSQLValueString($regionID, "int").") ORDER BY productcategory.title";
$productcategory = mysql_query($select, $aquiescedb) or die(mysql_error());

	if(mysql_num_rows($productcategory)>0) {echo ",\n{\"\", \"\"}";
		while($row_productcategory = mysql_fetch_assoc($productcategory)) {
			
			echo ",\n{\"title\": ".json_encode($row_productcategory['title']).", \"value\": "; 
			echo  json_encode($host.productLink("","", $row_productcategory['ID'], $row_productcategory['longID']));
			echo "}";
		}
	}
	
	$select = "SELECT product.ID, product.longID, product.title, product.productcategoryID, productcategory.longID AS catlongID FROM product LEFT JOIN productcategory ON (product.productcategoryID = productcategory.ID) WHERE product.statusID = 1 AND (product.regionID = 0 OR product.regionID = ".GetSQLValueString($regionID, "int").") ORDER BY product.title";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());

	
	if(mysql_num_rows($result)>0) {echo ",\n{\"title\":\"\", value:\"/\"}";
		while($row = mysql_fetch_assoc($result)) {
			
			echo ",\n{\"title\": ".json_encode($row['title']).", \"value\":"; 
			echo  json_encode($host.productLink($row['ID'],$row['longID'], $row['productcategoryID'], $row['catlongID']));
			echo "}";
		}
	}
	}
	
	if($row_rsArticlePrefs['documentlinks']==1) {
	$select = "SELECT documents.ID, documents.documentname, documentcategory.categoryname FROM documents LEFT JOIN documentcategory ON (documents.documentcategoryID = documentcategory.ID) WHERE documents.active = 1 AND documentcategory.active = 1 AND documentcategory.accessID = 0 AND documentcategory.regionID = ".$regionID." ORDER BY documentcategory.categoryname, documents.documentname";
$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);

	
	if(mysql_num_rows($result)>0) {echo ",\n{\"title\":\"\", \"value\":\"/\"}";
		while($row = mysql_fetch_assoc($result)) {
			
			echo ",\n{\"title\": ".json_encode($row['categoryname']." : ".$row['documentname']).", \"value\":"; 
			echo  json_encode($host."/documents/view.php?documentID=".$row['ID']);
			echo "}";
		}
	}
	}
	

echo "\n]";

mysql_free_result($result);
?>
