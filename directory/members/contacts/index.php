<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "2,3,4,5,6,7,8,9,10";
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
$query_rsDirectory = sprintf("SELECT ID, name FROM directory WHERE directory.ID = %s", GetSQLValueString($varDirectoryID_rsDirectory, "int"));
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

$colname_rsContacts = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsContacts = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsContacts = sprintf("SELECT userID, users.firstname, users.surname, users.jobtitle, users.addedbyID, users.email, users.telephone, users.usertypeID FROM directoryuser LEFT JOIN users ON (directoryuser.userID = users.ID) WHERE directoryID = %s ORDER BY createddatetime ASC", GetSQLValueString($colname_rsContacts, "int"));
$rsContacts = mysql_query($query_rsContacts, $aquiescedb) or die(mysql_error());
$row_rsContacts = mysql_fetch_assoc($rsContacts);
$totalRows_rsContacts = mysql_num_rows($rsContacts);


if(isset($_GET['removeuserID']) && intval($_GET['removeuserID'])>0 && isset($_GET['directoryID']) && intval($_GET['directoryID'])>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT usertypeID FROM users WHERE ID = ".intval($_GET['removeuserID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$user = mysql_fetch_assoc($result);
	if($row_rsLoggedIn['usertypeID'] > $user['usertypeID']) { //can only delete if higher rank
		$delete = "DELETE FROM directorylocation WHERE directoryID = ".intval($_GET['directoryID'])." AND locationID = ".intval($_GET['removelocationID']);
		mysql_query($delete, $aquiescedb) or die(mysql_error());
		header("location: index.php?directoryID=".intval($_GET['directoryID'])); exit;	
	}
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Update ".$row_rsDirectory['name']." contacts"; echo " - ".$pageTitle; ?></title>
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
    <h1 class="directoryheader"><?php echo $row_rsDirectory['name']; ?> Contacts</h1>
    <?php if ($totalRows_rsIsAuthorised >0 || $row_rsLoggedIn['usertypeID'] >=8) { //authorsied to access ?>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li id="linkAddContact"> <a href="add_directory_user.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Add contact</a></li>
      <li id="linkInviteContact"> <a href="invite_directory_user.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Invite contacts</a></li>
       <li> <a href="../index.php?directoryID=<?php echo $row_rsDirectory['ID']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> My Entries</a></li>
      
     
    </ul></div></nav>
    <?php if ($totalRows_rsContacts == 0) { // Show if recordset empty ?>
      <p>There are no contacts for this entry.</p>
      <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsContacts > 0) { // Show if recordset not empty ?>
  <table  class="table table-hover">
  <thead>
    <tr>
      <th>Name</th>
      <th>Role</th>
      <th>Telephone</th>
      <th>email</th>
      <th colspan="2">Actions</th>
    </tr></thead><tbody><?php do { ?>
      
      <tr>
        <td><?php $class = ($_SESSION['MM_UserGroup'] >=9 || $row_rsLoggedIn['usertypeID'] > $row_rsContacts['usertypeID'] || $row_rsContacts['addedbyID'] == $row_rsLoggedIn['usertypeID']) ? "show" : "hide"; echo $row_rsContacts['firstname']; ?> <?php echo $row_rsContacts['surname']; ?></td>
        <td><?php echo $row_rsContacts['jobtitle']; ?></td>
        <td><?php echo $row_rsContacts['telephone']; ?></td>
        <td><?php echo $row_rsContacts['email']; ?></td>
        <td><span class="<?php echo $class; ?>"><a href="index.php?directoryID=<?php echo intval($_GET['directoryID']); ?>&amp;removeuserID=<?php echo $row_rsContacts['userID']; ?>" class="link_delete"  onclick="return confirm('Are you sure you want to remove this contact?');"><i class="glyphicon glyphicon-trash"></i> Remove</a></span></td>
        <td><span class="<?php echo $class; ?>"><a href="update_directory_user.php?userID=<?php echo $row_rsContacts['userID']; ?>&amp;directoryID=<?php echo intval($_GET['directoryID']); ?>" class="link_edit icon_only">Update</a></span></td>
      </tr>
      <?php } while ($row_rsContacts = mysql_fetch_assoc($rsContacts)); ?></tbody>
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

mysql_free_result($rsContacts);
?>
