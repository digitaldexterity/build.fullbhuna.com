<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO notification (notification, userID, rankID, groupID, statusID, createdbyID, createddatetime, regionID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['notification'], "text"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['rankID'], "int"),
                       GetSQLValueString($_POST['groupID'], "int"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['regionID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

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
$query_rsRanks = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsRanks = mysql_query($query_rsRanks, $aquiescedb) or die(mysql_error());
$row_rsRanks = mysql_fetch_assoc($rsRanks);
$totalRows_rsRanks = mysql_num_rows($rsRanks);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup WHERE regionID = $regionID ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$colname_rsThisUSer = "-1";
if (isset($_GET['userID'])) {
  $colname_rsThisUSer = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisUSer = sprintf("SELECT firstname, surname FROM users WHERE ID = %s", GetSQLValueString($colname_rsThisUSer, "int"));
$rsThisUSer = mysql_query($query_rsThisUSer, $aquiescedb) or die(mysql_error());
$row_rsThisUSer = mysql_fetch_assoc($rsThisUSer);
$totalRows_rsThisUSer = mysql_num_rows($rsThisUSer);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Notifications"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page class">
       <h1><i class="glyphicon glyphicon-exclamation-sign"></i> Add Notification</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <ul class="nav navbar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php" ><i class="glyphicon glyphicon-chevron-left"></i> Back</a></li>
          </ul>
        </div>
      </nav>
      <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
        <table class="form-table">
        
        <tr>
            <td nowrap align="right" valign="top">To:</td>
            <td><input type="hidden" name="userID" value="<?php isset($_GET['userID']) ? intval($_GET['userID']) : 0; ?>"><?php echo  isset($_GET['userID']) ?  $row_rsThisUSer['firstname']." ".$row_rsThisUSer['surname'] : "Everyone (send notifiction to a user from their profile page)"; ?></td>
          </tr>
          
        
        
          <tr>
            <td nowrap align="right" valign="top">Notification:</td>
            <td><textarea name="notification" cols="50" rows="5" class="form-control"></textarea></td>
          </tr>
          <tr valign="baseline">
            <td nowrap align="right">Rank:</td>
            <td><select name="rankID"  class="form-control">
              <option value="0">Everyone</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsRanks['ID']?>"><?php echo $row_rsRanks['name']?></option>
              <?php
} while ($row_rsRanks = mysql_fetch_assoc($rsRanks));
  $rows = mysql_num_rows($rsRanks);
  if($rows > 0) {
      mysql_data_seek($rsRanks, 0);
	  $row_rsRanks = mysql_fetch_assoc($rsRanks);
  }
?>
            </select></td>
          <tr>
          <tr valign="baseline">
            <td nowrap align="right">Group:</td>
            <td><select name="groupID"  class="form-control">
              <option value="0">All groups</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsGroups['ID']?>"><?php echo $row_rsGroups['groupname']?></option>
              <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
            </select></td>
          <tr>
          <tr valign="baseline">
            <td nowrap align="right">Live:</td>
            <td><input type="checkbox" name="statusID" value="" checked></td>
          </tr>
          <tr valign="baseline">
            <td nowrap align="right">&nbsp;</td>
            <td><button type="submit" >Add Notification</button></td>
          </tr>
        </table>
        
        <input type="hidden" name="createdbyID" value="">
        <input type="hidden" name="createddatetime" value="">
        <input type="hidden" name="regionID" value="0">
        <input type="hidden" name="MM_insert" value="form1">
      </form>
     
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsRanks);

mysql_free_result($rsGroups);

mysql_free_result($rsThisUSer);
?>
