<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10,2,7,6,5,4,3";
$MM_donotCheckaccess = "true";

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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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

$colname_rsInFolder = "-1";
if (isset($_GET['categoryID'])) {
  $colname_rsInFolder = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsInFolder = sprintf("SELECT * FROM documentcategory WHERE ID = %s", GetSQLValueString($colname_rsInFolder, "int"));
$rsInFolder = mysql_query($query_rsInFolder, $aquiescedb) or die(mysql_error());
$row_rsInFolder = mysql_fetch_assoc($rsInFolder);
$totalRows_rsInFolder = mysql_num_rows($rsInFolder);

$varUserGroup_rsFolders = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsFolders = $_SESSION['MM_UserGroup'];
}
$varUserID_rsFolders = "-1";
if (isset($userID)) {
  $varUserID_rsFolders = $userID;
}
$varCategoryID_rsFolders = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsFolders = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFolders = sprintf("SELECT documentcategory.ID, documentcategory.categoryname, subcat.ID AS subcat FROM documentcategory LEFT JOIN documentcategory AS subcat ON (subcat.subcatofID = documentcategory.ID) WHERE documentcategory.active = 1 AND (documentcategory.writeaccess <= %s OR (documentcategory.addedbyID = %s AND documentcategory.writeaccess = 99)) AND documentcategory.ID != %s AND (subcat.ID IS NULL OR subcat.subcatofID != %s) GROUP BY documentcategory.ID ORDER BY categoryname ASC ", GetSQLValueString($varUserGroup_rsFolders, "int"),GetSQLValueString($varUserID_rsFolders, "int"),GetSQLValueString($varCategoryID_rsFolders, "int"),GetSQLValueString($varCategoryID_rsFolders, "int"));
$rsFolders = mysql_query($query_rsFolders, $aquiescedb) or die(mysql_error());
$row_rsFolders = mysql_fetch_assoc($rsFolders);
$totalRows_rsFolders = mysql_num_rows($rsFolders);

// security - check if user has permission to add within this folder select statements must go above

$pageaccess = true;
if(!($row_rsLoggedIn['usertypeID']>=$row_rsInFolder['writeaccess'] || ($row_rsInFolder['writeaccess']==99 && $row_rsInFolder['addedbyID'] == $row_rsLoggedIn['ID']))) {
	// not authorised to add 
	if(isset($_POST["MM_insert"])) {
		unset($_POST["MM_insert"]);
	}
	$submit_error = "You are not authorised to add anything within the folder: ".$row_rsInFolder['categoryname'];
	$pageaccess = false;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO documentshortcut (categoryID, shortcuttoID, createdbyID, createddatetime) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['categoryID'], "int"),
                       GetSQLValueString($_POST['shortcuttoID'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "../index.php?categoryID=" . $row_rsInFolder['ID'] . "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");

?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add shortcut"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<script src="../../SpryAssets/SpryValidationSelect.js"></script>
<script>
addListener("load", init);
function init() {
	addListener("change", checkWriteAccess, document.getElementById('accessID'));
	addListener("change", checkReadAccess, document.getElementById('writeaccess'));
}

function checkWriteAccess() {
	if(document.getElementById('accessID').value > document.getElementById('writeaccess').value) {
		setSelectListToValue(document.getElementById('accessID').value, 'writeaccess');	}
}

function checkReadAccess() {
	if(document.getElementById('accessID').value > document.getElementById('writeaccess').value) {
		setSelectListToValue(document.getElementById('writeaccess').value, 'accessID');
	}
}
</script>
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> 
         <div class="container pageBody documents">
     
      
      
       <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="index.php"><span itemprop="name">Documents</span></a>
       <meta itemprop="position" content="2" />
      </li>
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="index.php?categoryID=<?php echo intval($_GET['categoryID']); ?>"><span itemprop="name">
	  <?php if (isset($row_rsInFolder['categoryname']))  echo $row_rsInFolder['categoryname'];  else echo "Home folder"; ?></span></a> <meta itemprop="position" content="3" /></li>
      
       <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  Add Shortcut</span></a> <meta itemprop="position" content="4" /></li>
      
      </ol></div></div>
      <h1 class="folderheader">Add Shortcut </h1> 
      <h2>(inside
        <?php if (isset($row_rsInFolder['categoryname']))  echo $row_rsInFolder['categoryname'];  else echo "Home folder"; ?>)</h2>
      
    <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } if($pageaccess) { ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">

      <table border="0" cellpadding="2" cellspacing="0" class="form-table">
        <tr>
          <td align="right"><label for="shortcuttoID">Add shortcut to:</label></td>
          <td><span id="spryselect1">
            <select name="shortcuttoID" id="shortcuttoID" class="form-control">
              <option value="">Choose folder...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsFolders['ID']?>"><?php echo $row_rsFolders['categoryname']?></option>
              <?php
} while ($row_rsFolders = mysql_fetch_assoc($rsFolders));
  $rows = mysql_num_rows($rsFolders);
  if($rows > 0) {
      mysql_data_seek($rsFolders, 0);
	  $row_rsFolders = mysql_fetch_assoc($rsFolders);
  }
?>
            </select>
          <span class="selectRequiredMsg">Please select a folder.</span></span></td>
        </tr>
        <tr>
          <td align="right"><input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
            <input name="categoryID" type="hidden" id="categoryID" value="<?php echo intval($_GET['categoryID']); ?>" />
            <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />          </td>
          <td><button name="Submit" type="submit" class="btn btn-primary" >Add shortcut</button></td>
        </tr>
      </table>
      <input type="hidden" name="MM_insert" value="form1" />
</form>
   
    
     <?php } ?>
    <script>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
     </script></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsInFolder);

mysql_free_result($rsFolders);
?>
