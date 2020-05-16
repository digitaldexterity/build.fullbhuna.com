<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/documentfunctions.inc.php'); ?>
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

// security - check if user has permission to add within this folder select statements must go above

$pageaccess = true;
if(!($row_rsLoggedIn['usertypeID']>=$row_rsInFolder['writeaccess'] || ($row_rsInFolder['writeaccess']==99 && $row_rsInFolder['addedbyID'] == $row_rsLoggedIn['ID']))) {
	// not authorised to add 
	if(isset($_POST["MM_insert"])) {
		unset($_POST["MM_insert"]);
	}
	$submit_error = "You are not authorised to add a folder within the folder: ".$row_rsInFolder['categoryname'];
	$pageaccess = false;
}



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  
  
  addfolder($_POST['categoryname'], $_POST['description'], $_POST['subcatofID'], $_POST['addedbyID'], $_POST['accessID'], $_POST['writeaccess'], $_POST['groupreadID'], $_POST['groupwriteID'], $_POST['regionID']) ;
}
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = "/documents/index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}



$varUsername_rsAccessLevels = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsAccessLevels = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevels = sprintf("SELECT usertype.ID, usertype.name AS accesslevel FROM usertype, users WHERE usertype.ID > 0 AND usertype.iD <= users.usertypeID AND users.username = %s ORDER BY usertype.ID ASC", GetSQLValueString($varUsername_rsAccessLevels, "text"));
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");

?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Add folder"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<script src="../../SpryAssets/SpryValidationTextField.js"></script>

<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
</script><style><!--
<?php if($totalRows_rsGroups==0) { echo ".groups { display: 'none' }"; } 
 echo ".justmeshow { display: none; }";
if($row_rsInFolder['writeaccess']==99) {
	echo ".justmehide { display: none; } .justmeshow { display: block; }";
	
}

?>
--></style>
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
	  Add Folder</span></a> <meta itemprop="position" content="4" /></li>
      
      </ol></div></div>
      
      
      <h1 class="folderheader">Add Folder </h1> 
      <h2>(inside
        <?php if (isset($row_rsInFolder['categoryname']))  echo $row_rsInFolder['categoryname'];  else echo "Home folder"; ?>)</h2>
      
    <?php require_once('../../core/includes/alert.inc.php'); ?><?php if($pageaccess) { ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">

      <table class="form-table">
        <tr>
          <td align="right">Folder name: </td>
          <td><span id="sprytextfield1">
            <input name="categoryname" type="text"  id="categoryname" size="50" maxlength="50" class="form-control"/>
          <span class="textfieldRequiredMsg">A name is required.</span></span></td>
        </tr>
        <tr>
          <td align="right" valign="top">Description:<br /></td>
          <td><span id="sprytextarea1">
            <textarea name="description" id="description" cols="45" rows="5" placeholder="Optional - describe what this folder is intended to contain" class="form-control"></textarea>
</span></td>
        </tr>
        <tr>
          <td align="right"><label for="accessID">Can view:</label></td>
          <td class="form-inline"><select name="accessID" id="accessID" class="justmehide form-control">
            <option value="99">Just me</option>
            <?php if($row_rsInFolder['writeaccess']!=99) { ?>
            <option value="0" selected="selected">Everyone</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsAccessLevels['ID']?>"><?php echo $row_rsAccessLevels['accesslevel']?></option>
            <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
          </select>
           <?php } ?>
            <select name="groupreadID" id="groupreadID" class="group justmehide form-control" onchange="if(this.value!=0) alert('Remember, you will need to be in the chosen group if you wish to view or edit this folder in future');">
              <option value="0">Any group</option>
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
            </select> <div class="justmeshow">Only you can see this folder as it is within a folder that is set to be viewed by just you</div></td>
        </tr>
        <tr>
          <td align="right"><label for="writeaccess">Can add to:</label></td>
          <td class="form-inline"><select name="writeaccess" id="writeaccess" class="justmehide form-control">
            <option value="99" >Just me</option>
            <option value="100" >Nobody</option>
            <option value="0">Everyone</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsAccessLevels['ID']; ?>" <?php if($row_rsAccessLevels['ID']==7) { echo "selected=\"selected\""; } ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
            <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
          </select>
            <select name="groupwriteID" id="groupwriteID" class="group justmehide form-control">
            <option value="0">Any group</option>
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
          </select> <div class="justmeshow">Only you can add to this folder</div></td>
        </tr>
        <tr>
          <td align="right"><input type="hidden" name="MM_insert" value="form1" />
          <input name="regionID" type="hidden" id="regionID" value="<?php echo isset($regionID) ?  $regionID : 1; ?>" />
            <input name="subcatofID" type="hidden" id="subcatofID" value="<?php echo intval($_GET['categoryID']); ?>" />
            <input name="addedbyID" type="hidden" id="addedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
            <input name="referer" type="hidden" id="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
            <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" /></td>
          <td><button type="submit" class="btn btn-primary" onclick="if(document.getElementById('accessID').value==0) return confirm('This folder is set to be accessible by anyone. Do you wish to continue?');">Add Folder</button></td>
        </tr>
      </table>
    </form>
    <p>Folders on the web site work just the same as on your computer. You can put documents into them or another folder within it. You can restrict access to the folder to just yourself, everyone who visits the site or a membership rank. Higher ranked members can always view or add to folders with lower access levels.</p>
<script>
<!--

var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
      </script><?php } ?></div>
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

mysql_free_result($rsAccessLevels);

mysql_free_result($rsGroups);
?>
