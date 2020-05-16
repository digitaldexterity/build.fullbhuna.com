<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
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
    if (($strUsers == "") && true) { 
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



$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varDirectoryID_rsDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectory = sprintf("SELECT ID, name, directory.address1, directory.address2, directory.address3, directory.address4, directory.address5, directory.postcode, directory.telephone, directory.fax, directory.mobile FROM directory WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
$rsDirectory = mysql_query($query_rsDirectory, $aquiescedb) or die(mysql_error());
$row_rsDirectory = mysql_fetch_assoc($rsDirectory);
$totalRows_rsDirectory = mysql_num_rows($rsDirectory);

$varDirectoryID_rsDirectoryUser = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsDirectoryUser = $_GET['directoryID'];
}
$varUsername_rsDirectoryUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsDirectoryUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryUser = sprintf("SELECT directoryuser.ID FROM directoryuser LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE users.username = %s AND directoryuser.directoryID = %s", GetSQLValueString($varUsername_rsDirectoryUser, "text"),GetSQLValueString($varDirectoryID_rsDirectoryUser, "int"));
$rsDirectoryUser = mysql_query($query_rsDirectoryUser, $aquiescedb) or die(mysql_error());
$row_rsDirectoryUser = mysql_fetch_assoc($rsDirectoryUser);
$totalRows_rsDirectoryUser = mysql_num_rows($rsDirectoryUser);

$varUsername_rsIsAuthorised = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsIsAuthorised = $_SESSION['MM_Username'];
}
$varDirectoryID_rsIsAuthorised = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsIsAuthorised = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsIsAuthorised = sprintf("SELECT DISTINCT(directory.ID), name FROM directory LEFT JOIN users AS creator ON (directory.createdbyID = creator.ID) LEFT JOIN directoryuser ON (directory.ID = directoryuser.directoryID) LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE (creator.username = %s OR users.username = %s) AND directory.ID= %s", GetSQLValueString($varUsername_rsIsAuthorised, "text"),GetSQLValueString($varUsername_rsIsAuthorised, "text"),GetSQLValueString($varDirectoryID_rsIsAuthorised, "int"));
$rsIsAuthorised = mysql_query($query_rsIsAuthorised, $aquiescedb) or die(mysql_error());
$row_rsIsAuthorised = mysql_fetch_assoc($rsIsAuthorised);
$totalRows_rsIsAuthorised = mysql_num_rows($rsIsAuthorised);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLocations = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsLocations = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocations = sprintf("SELECT locationID, location.locationname, location.address1, location.address2, location.address3, location.address4, location.address5, location.postcode, location.telephone1, location.telephone2, location.telephone3, location.active FROM directorylocation LEFT JOIN location ON (directorylocation.locationID = location.ID) WHERE directoryID = %s ORDER BY location.createddatetime ASC", GetSQLValueString($colname_rsLocations, "int"));
$rsLocations = mysql_query($query_rsLocations, $aquiescedb) or die(mysql_error());
$row_rsLocations = mysql_fetch_assoc($rsLocations);
$totalRows_rsLocations = mysql_num_rows($rsLocations);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Update ".$row_rsDirectory['name']." locations"; echo " - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
<?php if (!isset($allowlocalwebpage) || $allowlocalwebpage == false) {
echo "#linkWebPage { display:none; } ";
}
?>
<?php if ($row_rsLoggedIn['usertypeID']<2) {
echo "#linkAddContact { display:none; } ";
}
?>
-->
</style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1 class="directoryheader"><?php echo $row_rsDirectory['name']; ?> Locations</h1>
    <?php if ($totalRows_rsIsAuthorised >0 || $row_rsLoggedIn['usertypeID'] >=8) { //authorsied to access ?>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item" id="linkAddContact"> <a href="../../../location/members/add_location.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add location</a></li>
       <li class="nav-item"> <a href="../index.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> My Entries</a></li>
      
     
    </ul></div></nav><p><strong>Main Address:</strong><br /><?php echo nl2br(trim($row_rsDirectory['address1'].",\n".$row_rsDirectory['address2'].",\n".$row_rsDirectory['address3'].",\n".$row_rsDirectory['address4'].",\n".$row_rsDirectory['address5'].",\n","\n, ")); 
	echo isset($row_rsDirectory['postcode']) ? "<br />".$row_rsDirectory['postcode'] : "";
	echo isset($row_rsDirectory['telephone']) ? "<br />Telephone: ".$row_rsDirectory['telephone'] : "";
	echo isset($row_rsDirectory['fax']) ? "<br />Fax: ".$row_rsDirectory['fax'] : "";
	echo isset($row_rsDirectory['mobile']) ? "<br />Mobile: ".$row_rsDirectory['mobile'] : ""; ?></p>
    <?php if ($totalRows_rsLocations == 0) { // Show if recordset empty ?>
      <p>There are no other locations for this entry.</p>
      <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsLocations > 0) { // Show if recordset not empty ?>
  <table class="table table-hover"><thead>
    <tr>
      <th>Address</th>
      <th>Telephone</th>
      <th>Update</th>
    </tr></thead><tbody><?php do { ?>
      
      <tr>
        <td><?php echo $row_rsLocations['locationname'].",\n".$row_rsLocations['address1'].",\n".$row_rsLocations['address2'].",\n".$row_rsLocations['address3'].",\n".$row_rsLocations['address4'].",\n".$row_rsLocations['address5'];
		  echo isset($row_rsLocations['postcode']) ? "<br />".$row_rsLocations['postcode'] : "";
		  ?></td>
        <td><?php  echo isset($row_rsLocations['telephone1']) ? "Telephone: ".$row_rsLocations['telephone1']."<br />" : "";
		  echo isset($row_rsLocations['telephone2']) ? "Fax: ".$row_rsLocations['telephone2']."<br />" : "";
		  echo isset($row_rsLocations['telephone3']) ? "Mobile: ".$row_rsLocations['telephone3']."<br />" : ""; ?></td>
        
        <td><a href="../../../location/members/update_location.php?locationID=<?php echo $row_rsLocations['locationID']; ?>&amp;returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_edit icon_only">Update</a></td>
      </tr>
      <?php } while ($row_rsLocations = mysql_fetch_assoc($rsLocations)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
<?php } //end authorised
	  else { ?>
    <p class="alert alert-danger" role="alert">You are not authorised to edit this entry.</p>
    <?php } ?>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDirectory);

mysql_free_result($rsDirectoryUser);

mysql_free_result($rsIsAuthorised);

mysql_free_result($rsPreferences);

mysql_free_result($rsLocations);
?>
