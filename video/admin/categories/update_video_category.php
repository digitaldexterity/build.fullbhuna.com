<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE videocategory SET categoryname=%s, categorydescription=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s, regionID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categoryname'], "text"),
                       GetSQLValueString($_POST['categorydescription'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsVideoCategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsVideoCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVideoCategory = sprintf("SELECT * FROM videocategory WHERE ID = %s", GetSQLValueString($colname_rsVideoCategory, "int"));
$rsVideoCategory = mysql_query($query_rsVideoCategory, $aquiescedb) or die(mysql_error());
$row_rsVideoCategory = mysql_fetch_assoc($rsVideoCategory);
$totalRows_rsVideoCategory = mysql_num_rows($rsVideoCategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevel = "SELECT usertype.ID, CONCAT(usertype.name,'s') AS usertype FROM usertype WHERE usertype.ID >0 ORDER BY usertype.ID ASC";
$rsAccessLevel = mysql_query($query_rsAccessLevel, $aquiescedb) or die(mysql_error());
$row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
$totalRows_rsAccessLevel = mysql_num_rows($rsAccessLevel);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Video Category"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page video">
   <h1><i class="glyphicon glyphicon-film"></i> Update Video Category </h1>
   
   
      <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Category name:</td>
         <td><input name="categoryname" type="text"  value="<?php echo htmlentities($row_rsVideoCategory['categoryname'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Access Level:</td>
         <td><label>
           <select name="accesslevel"  id="accesslevel">
             <option value="0" <?php if (!(strcmp(0, $row_rsVideoCategory['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
             <?php
do {  
?>
             <option value="<?php echo $row_rsAccessLevel['ID']?>"<?php if (!(strcmp($row_rsAccessLevel['ID'], $row_rsVideoCategory['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevel['usertype']?></option>
             <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
           </select>
         </label></td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Description:</td>
         <td><textarea name="categorydescription" cols="50" rows="5"><?php echo htmlentities($row_rsVideoCategory['categorydescription'], ENT_COMPAT, 'UTF-8'); ?></textarea>         </td>
       </tr> <tr>
         <td class="text-nowrap text-right">Status :</td>
         <td><select name="statusID" >
             <?php 
do {  
?>
             <option value="<?php echo $row_rsStatus['ID']?>" <?php if (!(strcmp($row_rsStatus['ID'], htmlentities($row_rsVideoCategory['statusID'], ENT_COMPAT, 'UTF-8')))) {echo "SELECTED";} ?>><?php echo $row_rsStatus['description']?></option>
             <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
?>
           </select>         </td>
       </tr>
       <tr> </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
       </tr>
     </table>
     <input type="hidden" name="ID" value="<?php echo $row_rsVideoCategory['ID']; ?>" />
     <input type="hidden" name="modifiedbyID" value="<?php echo htmlentities($row_rsLoggedIn['ID']); ?>" />
     <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input type="hidden" name="MM_update" value="form1" />
     <input type="hidden" name="ID" value="<?php echo $row_rsVideoCategory['ID']; ?>" /><input type="hidden" name="regionID" value="<?php echo $regionID; ?>" />
   </form>
  </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);

mysql_free_result($rsVideoCategory);

mysql_free_result($rsAccessLevel);
?>


