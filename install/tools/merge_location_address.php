<?php require_once('../../Connections/aquiescedb.php'); ?><?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAddresses = "SELECT * FROM deliveryaddress";
$rsAddresses = mysql_query($query_rsAddresses, $aquiescedb) or die(mysql_error());
$row_rsAddresses = mysql_fetch_assoc($rsAddresses);
$totalRows_rsAddresses = mysql_num_rows($rsAddresses);
?>
<?php if (isset($_GET['mergeaddresses'])) { //update
  mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT ID, address, telephone FROM location";
$result = mysql_query($select, $aquiescedb);
if($result) { // old table, so update
$row = mysql_fetch_assoc($result);
if(mysql_num_rows($result)>0) {//rows to update
do {
$address = explode("<br />",str_replace("\r\n","<br />",$row['address']));
$update = "UPDATE location SET address1 = ".GetSQLValueString($address[0],"text").", address2 = ".GetSQLValueString($address[1],"text").", address3 = ".GetSQLValueString($address[2],"text").", address4 = ".GetSQLValueString($address[3],"text").", address5 = ".GetSQLValueString($address[4],"text").", telephone1 = ".GetSQLValueString($row['telephone'],"text")." WHERE ID = ".GetSQLValueString($row['ID'],"int");
$update_result = mysql_query($update, $aquiescedb);
echo $update."<br>"; 
} while ($row = mysql_fetch_assoc($result));
} // end rows to update;
$alter = "ALTER TABLE location DROP COLUMN address, DROP COLUMN telephone";
 // $result = mysql_query($alter, $aquiescedb)  or die(mysql_error());
} // end table requires update

if ($totalRows_rsAddresses>0) { // addresses to move

$alter = "ALTER TABLE users ADD COLUMN temp TINYINT(4) DEFAULT 0";
  $result = mysql_query($alter, $aquiescedb)  or die(mysql_error());
do { 
  $insert = "INSERT INTO location (userID, locationname, address1, address2, address3, address4, address5, postcode, countryID, telephone1, telephone2, active) VALUES (".GetSQLValueString($row_rsAddresses['userID'],"int").",".GetSQLValueString($row_rsAddresses['fullname'],"text").",".GetSQLValueString($row_rsAddresses['address1'],"text").",".GetSQLValueString($row_rsAddresses['address2'],"text").",".GetSQLValueString($row_rsAddresses['address3'],"text").",".GetSQLValueString($row_rsAddresses['address4'],"text").",".GetSQLValueString($row_rsAddresses['address5'],"text").",".GetSQLValueString($row_rsAddresses['postcode'],"text").",".GetSQLValueString($row_rsAddresses['countryID'],"int").",".GetSQLValueString($row_rsAddresses['telephone1'],"text").",".GetSQLValueString($row_rsAddresses['telephone2'],"text").",".GetSQLValueString($row_rsAddresses['statusID'],"int").")";
  $result = mysql_query($insert, $aquiescedb)  or die(mysql_error());

  echo $insert."<br>";
  
  $update = "UPDATE users SET temp = 1, defaultaddressID = ".mysql_insert_id()." WHERE temp = 0 AND defaultaddressID = ".$row_rsAddresses['ID'];
    $result = mysql_query($update, $aquiescedb)  or die(mysql_error());

  echo $update."<br>";
    } while ($row_rsAddresses = mysql_fetch_assoc($rsAddresses));
	$alter = "ALTER TABLE users DROP COLUMN temp";
  $result = mysql_query($alter, $aquiescedb)  or die(mysql_error());
  $drop = "DROP TABLE deliveryaddress";
  //$result = mysql_query($drop, $aquiescedb)  or die(mysql_error());
exit;
} // end addresses to move
} // end update ?>
  
  <?php  ?>

<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Merge Location Addresses"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
   <p>copy all location address to address1, etc</p>
   <p>delete address</p>
   <p>copy all location telephone to telephone1 etc</p>
   <p>delete telephone</p>
   <p>copy addresses to location</p>
   <p>&nbsp;</p>
   <p>change user tables</p>
   <p><a href="merge_location_address.php?mergeaddresses=true">Merge Now</a></p>
  <!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsAddresses);
?>


