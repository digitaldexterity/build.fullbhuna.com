<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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
$query_rsDirectoryToDirectoryUser = "SELECT directory.ID, directory.userID, directory.name, directory.createdbyID, users.firstname, users.surname, creator.firstname AS creatorfirstname, creator.surname AS creatorsurname FROM directory LEFT JOIN users ON (directory.userID = users.ID) LEFT JOIN users AS creator ON (directory.createdbyID = creator.ID)";
$rsDirectoryToDirectoryUser = mysql_query($query_rsDirectoryToDirectoryUser, $aquiescedb) or die(mysql_error());
$row_rsDirectoryToDirectoryUser = mysql_fetch_assoc($rsDirectoryToDirectoryUser);
$totalRows_rsDirectoryToDirectoryUser = mysql_num_rows($rsDirectoryToDirectoryUser);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryAddresses = "SELECT * FROM directory";
$rsDirectoryAddresses = mysql_query($query_rsDirectoryAddresses, $aquiescedb) or die(mysql_error());
$row_rsDirectoryAddresses = mysql_fetch_assoc($rsDirectoryAddresses);
$totalRows_rsDirectoryAddresses = mysql_num_rows($rsDirectoryAddresses);


?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Directory Tables"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page directory">
   <h1><i class="glyphicon glyphicon-book"></i> Update Directory Tables</h1>
   <h2>Directory associations...</h2>
   
   <?php if ($totalRows_rsDirectoryToDirectoryUser == 0) { // Show if recordset empty ?>
     <p>No relationships from organisation table.</p>
     <?php } // Show if recordset empty ?>
<table border="0" cellpadding="0" cellspacing="0" class="form-table">
     <?php if ($totalRows_rsDirectoryToDirectoryUser > 0) { // Show if recordset not empty ?>
       <?php do { ?>
       <?php $userID = isset($row_rsDirectoryToDirectoryUser['userID']) ? $row_rsDirectoryToDirectoryUser['userID'] : $row_rsDirectoryToDirectoryUser['createdbyID']; 
		 // avoid duplicates and check if already exists first...
$result = mysql_query("SELECT ID FROM directoryuser WHERE userID = ".intval($userID)." AND directoryID = ".intval($row_rsDirectoryToDirectoryUser['ID']), $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)<1) {  ?>
         <tr>
           <td><?php echo $row_rsDirectoryToDirectoryUser['name']; ?> - user: </td>
           <td><?php  echo isset($row_rsDirectoryToDirectoryUser['userID']) ? $row_rsDirectoryToDirectoryUser['firstname']." ".$row_rsDirectoryToDirectoryUser['surname'] : $row_rsDirectoryToDirectoryUser['creatorfirstname']." ".$row_rsDirectoryToDirectoryUser['creatorsurname'] ; ?>
               <?php $insert = "INSERT INTO directoryuser (userID, directoryID, relationshiptype, createdbyID, createddatetime) VALUES ('".$userID."','".$row_rsDirectoryToDirectoryUser['ID']."',1,'".$row_rsLoggedIn['ID']."',NOW())";
		 $result = mysql_query($insert, $aquiescedb) or die(mysql_error());  ?></td>
         </tr>
         <?php } // end avoid duplicate ?>
         <?php } while ($row_rsDirectoryToDirectoryUser = mysql_fetch_assoc($rsDirectoryToDirectoryUser)); ?>
       <?php } // Show if recordset not empty ?>

    </table>
   <h2>User associations...</h2>
   <?php 
$query = "SELECT ID, firstname, surname, companyID FROM users WHERE users.companyID IS NOT NULL";

$result = mysql_query($query, $aquiescedb);
if($result) {
$row = mysql_fetch_assoc($result);
$totalRows = mysql_num_rows($result);
do { 
// avoid duplicates and check if already exists first...
$select = mysql_query("SELECT ID FROM directoryuser WHERE userID = ".intval($row['ID'])." AND directoryID = ".intval($row['companyID']), $aquiescedb) or die(mysql_error());
if(mysql_num_rows($select)<1) {
$insert = "INSERT INTO directoryuser (userID, directoryID, relationshiptype, createdbyID, createddatetime) VALUES (".GetSQLValueString($row['ID'],"int").",".GetSQLValueString($row['companyID'],"int").",1,".GetSQLValueString($row_rsLoggedIn['ID'],"int").",NOW())";
echo $insert."<br>";
$result2 = mysql_query($insert, $aquiescedb) or die(mysql_error()); } // end check for duplicate
} while ($row = mysql_fetch_assoc($result));
} else { ?><p>No relationships from users table.</p>
   <p>
     <?php } ?>
   </p>
   <h2>New Address Schema</h2>
   <p>&nbsp;</p>
   <table border="0" cellpadding="0" cellspacing="0" class="form-table">
     <tr>
       <td>&nbsp;</td>
     </tr>
     <?php do { ?>
       <tr>
         <td><?php if (isset($row_rsDirectoryAddresses['address']) && $row_rsDirectoryAddresses['address'] !="" && $row_rsDirectoryAddresses['address1']=="") { // old address exists, new doesn't
		 echo nl2br($row_rsDirectoryAddresses['address']); 
		
			 
         $address = explode("\n",str_replace("\r","",$row_rsDirectoryAddresses['address']));
          $update = "UPDATE directory SET address = '', address1 = ".GetSQLValueString(@$address[0], "text").", address2 = ".GetSQLValueString(@$address[1], "text").", address3 = ".GetSQLValueString(@$address[2], "text").", address4 = ".GetSQLValueString(@$address[3], "text").", address5 = ".GetSQLValueString(@$address[4], "text")." WHERE ID =".$row_rsDirectoryAddresses['ID']; 
		  $result3 = mysql_query($update, $aquiescedb) or die(mysql_error());
		  
		  } // address exists?> </td>
       </tr>
       <?php } while ($row_rsDirectoryAddresses = mysql_fetch_assoc($rsDirectoryAddresses)); ?>
   </table>
<p>Done!</p>
 </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDirectoryToDirectoryUser);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsDirectoryAddresses);

mysql_free_result($rsUserToDirectoryUser);
?>


