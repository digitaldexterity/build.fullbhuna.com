<?php 
if(!isset($aquiescedb)) die();

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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1; 

global $row_rsPreferences;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

global $row_rsProductPrefs;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT productprefs.*, article.body  FROM productprefs  LEFT JOIN article ON (productprefs.productpagetemplateID = article.ID) WHERE productprefs.ID=".$regionID;
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);


if (!($row_rsProductPrefs['shopstatus']==1 || @$_SESSION['MM_UserGroup']>=8)) { 
	header("location: /products/closed.php"); exit; 
}

global $row_rsThisRegion;

$colname_rsThisRegion = "1";
if (isset($regionID)) {
  $colname_rsThisRegion = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisRegion = sprintf("SELECT region.*, countries.ID, countries.iso2  FROM region LEFT JOIN countries  ON (region.ID = countries.regionID OR countries.regionID=0)  WHERE region.ID =  %s", GetSQLValueString($colname_rsThisRegion, "int"));
$rsThisRegion = mysql_query($query_rsThisRegion, $aquiescedb) or die(mysql_error());
$row_rsThisRegion = mysql_fetch_assoc($rsThisRegion);
$totalRows_rsThisRegion = mysql_num_rows($rsThisRegion);


$varUsername_rsPurchaseAccount = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsPurchaseAccount = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPurchaseAccount = sprintf("SELECT productaccount.* FROM productaccount LEFT JOIN usergroupmember ON (productaccount.groupID = usergroupmember.groupID) LEFT JOIN users ON (users.ID = usergroupmember.userID) WHERE productaccount.statusID =1 AND (productaccount.regionID = 0 OR productaccount.regionID = ".$regionID.") AND  users.username = %s LIMIT 1", GetSQLValueString($varUsername_rsPurchaseAccount, "text"));
$rsPurchaseAccount = mysql_query($query_rsPurchaseAccount, $aquiescedb) or die(mysql_error());
$row_rsPurchaseAccount = mysql_fetch_assoc($rsPurchaseAccount);
$totalRows_rsPurchaseAccount = mysql_num_rows($rsPurchaseAccount);


switch($row_rsThisRegion['currencycode']) {
				case "GBP" : $currency = "&pound;"; break;
				case "EUR" : $currency = "&euro;"; break;
				case "USD" :  $currency = "$"; break;
				default : $currency = $row_rsThisRegion['currencycode']." "; 
				}
				
// manage auctions
if($row_rsProductPrefs['auctions']==1) {
	$select = "SELECT product.ID, product.title, product.auctionsellafter, productbid.ID AS productbidID, productbid.amount AS highestbid, users.ID AS userID, users.firstname, users.email FROM product LEFT JOIN productbid ON (product.ID = productbid.productID) LEFT JOIN users ON (productbid.createdbyID = users.ID)  WHERE auction >= 1 AND instock>0 AND statusID = 1 AND auctionenddatetime <= '".date('Y-m-d H:i:s')."' ORDER BY productbid.amount DESC  LIMIT 1";
	$products = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($products)>0) { // bids ended for product
		while($product = mysql_fetch_assoc($products)) {
			$update = "UPDATE product SET auction = 0 ";
			$update .= ($product['auctionsellafter'] !=1 || $product['auction']==2) ? ", statusID = 0 " : "";
			$update .= isset($product['userID']) ? ", price = ".floatval($product['highestbid'])." " : "";
			$update .=  " WHERE ID = ".$product['ID'];
			mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
			// is there a winner?
			if(isset($product['userID'])) {
				// email winner if is one
				$update = "UPDATE productbid SET winning = 1 WHERE ID = ".$product['productbidID'];
				mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
				$to = $product['email'];
				$subject = "You are the winning bidder for ".$product['title'];
				$message = "Dear ".$product['firstname'].",\n\n";
				$message .= "Congratulations, you are the winning bidder for ".$product['title']."\n\n";
				$message .= "Your final bid was: ".$currency.number_format($product['highestbid'],2, ".", ",")."\n\n";
				$message .= "Please use the link below to pay for your item:\n\n";
				$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/products/members/bids.php";
				
				if(!function_exists("sendMail")) {
					require_once(SITE_ROOT."mail/includes/sendmail.inc.php");
				}
				
				sendMail($to, $subject, $message);
			}
			
		}
	}
			 
}
				
 ?>