<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	$category = explode("\n",$_POST['categories']);
	foreach($category as $uniquecat) {
$uniquecat = trim($uniquecat);
if($uniquecat != "") { // cat exists
  $insertSQL = sprintf("INSERT INTO directorycategory (subcatofID, `description`, createdbyID, createddatetime, statusID, regionID) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['subCatOf'], "int"),
                       GetSQLValueString($uniquecat, "text"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
 $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

} // cat exits
} // end for each
//die("test");

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
} // end insert

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsActiveParentCategories = "SELECT directorycategory.ID, directorycategory.description, directorycategory.statusID FROM directorycategory WHERE directorycategory.statusID = 1 AND directorycategory.subCatOfID=0 ORDER BY directorycategory.description";
$rsActiveParentCategories = mysql_query($query_rsActiveParentCategories, $aquiescedb) or die(mysql_error());
$row_rsActiveParentCategories = mysql_fetch_assoc($rsActiveParentCategories);
$totalRows_rsActiveParentCategories = mysql_num_rows($rsActiveParentCategories);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add multiple categories"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
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
        <div class="page directory">
    <h1><i class="glyphicon glyphicon-book"></i> Add Multiple Categories</h1>
   
    <form action="" method="post" name="form1" id="form1"> <table border="0" cellpadding="2" cellspacing="0" class="form-table">
      <tr>
        <td align="right">Parent Category:</td>
        <td class="region"><label for="subCatOf"></label>
          <select name="subCatOf" id="subCatOf">
            <option value="0">None</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsActiveParentCategories['ID']?>"><?php echo $row_rsActiveParentCategories['description']?></option>
            <?php
} while ($row_rsActiveParentCategories = mysql_fetch_assoc($rsActiveParentCategories));
  $rows = mysql_num_rows($rsActiveParentCategories);
  if($rows > 0) {
      mysql_data_seek($rsActiveParentCategories, 0);
	  $row_rsActiveParentCategories = mysql_fetch_assoc($rsActiveParentCategories);
  }
?>
          </select></td>
      </tr>
      <tr>
        <td align="right">Site:</td>
        <td class="region"><label for="regionID"></label>
          <select name="regionID" id="regionID">
            <option value="0">All versions</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsRegions['ID']?>"><?php echo $row_rsRegions['title']?></option>
            <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
          </select></td>
      </tr>
      <tr>
        <td align="right">Type (or paste) the categories into the box. Each category must appear on a new line:</td>
        <td class="region"><textarea name="categories" id="categories" cols="45" rows="10"></textarea></td>
      </tr>
      <tr>
        <td align="right"><input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input type="hidden" name="statusID" value="1" />
          <input type="hidden" name="MM_insert" value="form1" />
          <input type="hidden" />
          <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" /></td>
        <td class="region"><input type="submit" class="button" value="Add Multiple" /></td>
      </tr>
    </table>
     
    </form>
  </div>
    
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsActiveParentCategories);

mysql_free_result($rsRegions);

mysql_free_result($rsLoggedIn);
?>
