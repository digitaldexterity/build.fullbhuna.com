<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('product_functions.inc.php'); ?>
<?php
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
?>
<?php $userID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
//mail("paul@digdex.co.uk",$_GET['productID'],$version.":". $userID);
if(isset($_GET['productID']) && intval($_GET['productID'])>0) {
	$version = html_entity_decode($_GET['versionname']);if(isset($_GET['type']) && $_GET['type'] == "version") {
		
		if(isset($_GET['checked']) && $_GET['checked'] == "true") {
			
			addVersionToProduct(intval($_GET['productID']), $version, $userID);
	
		} else {
			removeVersionFromProduct(intval($_GET['productID']), $version);
		}
	} else if(isset($_GET['type']) && $_GET['type'] == "finish") {
		if(isset($_GET['checked']) && $_GET['checked'] == "true") {
			
			addFinishToProduct(intval($_GET['productID']), $version, $userID);
	
		} else {
			removeFinishFromProduct(intval($_GET['productID']), $version);
		}
	}
}

?>