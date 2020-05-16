<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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
  $updateSQL = sprintf("UPDATE locationcategory SET categoryname=%s, statusID=%s, subcatofID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categoryname'], "text"),
                       GetSQLValueString(isset($_POST['statusID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['subcatofID'], "int"),
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

$colname_rslocationcategory = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rslocationcategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rslocationcategory = sprintf("SELECT * FROM locationcategory WHERE ID = %s", GetSQLValueString($colname_rslocationcategory, "int"));
$rslocationcategory = mysql_query($query_rslocationcategory, $aquiescedb) or die(mysql_error());
$row_rslocationcategory = mysql_fetch_assoc($rslocationcategory);
$totalRows_rslocationcategory = mysql_num_rows($rslocationcategory);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationPrefs = "SELECT * FROM locationprefs";
$rsLocationPrefs = mysql_query($query_rsLocationPrefs, $aquiescedb) or die(mysql_error());
$row_rsLocationPrefs = mysql_fetch_assoc($rsLocationPrefs);
$totalRows_rsLocationPrefs = mysql_num_rows($rsLocationPrefs);

$varRegionID_rsLocationCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsLocationCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLocationCategories = sprintf("SELECT ID, categoryname FROM locationcategory WHERE statusID = 1 AND  (regionID = 0 OR regionID = %s)  ORDER BY categoryname ASC", GetSQLValueString($varRegionID_rsLocationCategories, "int"));
$rsLocationCategories = mysql_query($query_rsLocationCategories, $aquiescedb) or die(mysql_error());
$row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
$totalRows_rsLocationCategories = mysql_num_rows($rsLocationCategories);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update ". ucwords($row_rsLocationPrefs['locationdescriptor'])." Category"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style>
<?php if($totalRows_rsLocationCategories==0) {
	echo ".parentcategory { display: none; } ";
} ?><!--
--></style><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page location">
<h1><i class="glyphicon glyphicon-flag"></i> Update <?php echo ucwords($row_rsLocationPrefs['locationdescriptor']); ?> Category  </h1>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
     <table class="form-table"> 
       <tr class="parentcategory">
         <td class="text-nowrap text-right">Parent category:</td>
         <td><label for="subcatofID"></label>
           <select name="subcatofID" id="subcatofID">
             <option value="0">None</option>
             <?php
do {  $row_rslocationcategory['subcatofID']
?>
             <option value="<?php echo $row_rsLocationCategories['ID']; ?>" <?php if($row_rsLocationCategories['ID']==$row_rslocationcategory['subcatofID']) echo "selected"; ?>><?php echo $row_rsLocationCategories['categoryname']; ?></option>
             <?php
} while ($row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories));
  $rows = mysql_num_rows($rsLocationCategories);
  if($rows > 0) {
      mysql_data_seek($rsLocationCategories, 0);
	  $row_rsLocationCategories = mysql_fetch_assoc($rsLocationCategories);
  }
?>
           </select></td>
       </tr>
       <tr>
         <td class="text-nowrap text-right">Name:</td>
         <td><input name="categoryname" type="text"  value="<?php echo $row_rslocationcategory['categoryname']; ?>" size="50" maxlength="50" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Active:</td>
         <td><input <?php if (!(strcmp($row_rslocationcategory['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
       </tr>
     </table>
     <input name="ID" type="hidden" id="ID" value="<?php echo $row_rslocationcategory['ID']; ?>" />
     <input type="hidden" name="MM_update" value="form1" />
   </form>
   </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rslocationcategory);

mysql_free_result($rsLocationPrefs);

mysql_free_result($rsLocationCategories);
?>


