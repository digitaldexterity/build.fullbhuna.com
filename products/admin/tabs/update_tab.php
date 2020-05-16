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

if(isset($_GET['deleteID'])) { //delete
  $delete = "DELETE FROM productdetails WHERE ID = ".intval($_GET['deleteID']);
  mysql_query($delete, $aquiescedb) or die(mysql_error());
  header("location:  /products/admin/products/modify_product.php?productID=".intval($_GET['productID'])."&defaultTab=2"); exit;
} // end delete

if(isset($_POST['defaulttab'])) { // if default tab, remove any previous
$update = "UPDATE productdetails SET defaulttab = 0 WHERE productID = ".GetSQLValueString($_POST['productID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
 mysql_query($update, $aquiescedb) or die(mysql_error());

} // end default tab

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productdetails SET regionID=%s, defaulttab=%s, tabtitle=%s, headHTML=%s, tabtext=%s, footHTML=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString(isset($_POST['defaulttab']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['tabtitle'], "text"),
                       GetSQLValueString($_POST['headHTML'], "text"),
                       GetSQLValueString($_POST['tabtext'], "text"),
                       GetSQLValueString($_POST['footHTML'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "/products/admin/products/modify_product.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTabNextID = "SELECT MAX(ID)+1 AS maxID FROM productdetails";
$rsTabNextID = mysql_query($query_rsTabNextID, $aquiescedb) or die(mysql_error());
$row_rsTabNextID = mysql_fetch_assoc($rsTabNextID);
$totalRows_rsTabNextID = mysql_num_rows($rsTabNextID);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$colname_rsThisProduct = "-1";
if (isset($_GET['productID'])) {
  $colname_rsThisProduct = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisProduct = sprintf("SELECT title FROM product WHERE ID = %s", GetSQLValueString($colname_rsThisProduct, "int"));
$rsThisProduct = mysql_query($query_rsThisProduct, $aquiescedb) or die(mysql_error());
$row_rsThisProduct = mysql_fetch_assoc($rsThisProduct);
$totalRows_rsThisProduct = mysql_num_rows($rsThisProduct);

$varUsername_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT users.ID, users.usertypeID, users.regionID FROM users WHERE users.username = %s", GetSQLValueString($varUsername_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsProductTab = "-1";
if (isset($_GET['tabID'])) {
  $colname_rsProductTab = $_GET['tabID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductTab = sprintf("SELECT * FROM productdetails WHERE ID = %s", GetSQLValueString($colname_rsProductTab, "int"));
$rsProductTab = mysql_query($query_rsProductTab, $aquiescedb) or die(mysql_error());
$row_rsProductTab = mysql_fetch_assoc($rsProductTab);
$totalRows_rsProductTab = mysql_num_rows($rsProductTab);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT useregions FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update Product Details"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php require_once('../../../core/tinymce/tinymce.inc.php'); 
if(@$row_rsPreferences['useregions']!=1) { echo "<style> .region { display:none; } </style>"; } ?>

<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<?php if(isset($body_class)) $body_class .= " products ";  ?>
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
    <h1><i class="glyphicon glyphicon-shopping-cart"></i> Update Details for <?php echo $row_rsThisProduct['title']; ?></h1>
   
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
      <table class="form-table"> <tr>
          <td class="text-nowrap"><select name="regionID" class="form-control">
            <option value="0" <?php if (!(strcmp(0, $row_rsProductTab['regionID']))) {echo "selected=\"selected\"";} ?>>Choose site...</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsProductTab['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
            <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
          </select></td>
        </tr> <tr>
          <td class="text-nowrap form-inline"><input name="tabtitle" type="text"  value="<?php echo $row_rsProductTab['tabtitle']; ?>" size="20" maxlength="50" class="form-control" />
            <input <?php if (!(strcmp($row_rsProductTab['defaulttab'],1))) {echo "checked=\"checked\"";} ?> name="defaulttab" type="checkbox" id="defaulttab" value="1" />
Default tab</td>
        </tr> <tr>
          <td class="text-nowrap text-right top"><textarea name="tabtext"  id="tabtext"  cols="50" rows="10" class="tinymce form-control"><?php echo htmlentities($row_rsProductTab['tabtext'], ENT_COMPAT,"UTF-8"); ?></textarea></td>
        </tr> <tr>
          <td class="text-nowrap"><button type="submit" class="btn btn-primary">Save changes</button>
          <button type="button" name="delete" id="delete" class="btn btn-default btn-secondary" onclick="if(confirm('Are you sure you want to delete these product details?')) { window.location = 'update_tab.php?productID=<?php echo intval($_GET['productID']); ?>&amp;deleteID=<?php echo $row_rsProductTab['ID']; ?>'; } else return false;" >Delete</button>
          <input <?php if (!(strcmp($row_rsProductTab['statusID'],1))) {echo "checked=\"checked\"";} ?> name="statusID" type="checkbox" id="statusID" value="1" />
Active</td>
        </tr>
      </table>
      <input type="hidden" name="headHTML" value="" />
      <input type="hidden" name="footHTML" value="" />
      <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
      <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsProductTab['ID']; ?>" />
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    <p>&nbsp;</p>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegions);

mysql_free_result($rsThisProduct);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsTabNextID);

mysql_free_result($rsProductTab);

mysql_free_result($rsPreferences);
?>
